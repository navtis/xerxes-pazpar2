<?php

namespace Application\Model\Pazpar2;

use 
    Application\Model\Search,
    Zend\Debug,
    Xerxes\Pazpar2,
    Xerxes\Utility\Factory,
    Xerxes\Utility\Parser,
    Xerxes\Utility\Xsl,
    Xerxes\Utility\Request;

/**
 * Pazpar2 Search Engine
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Engine extends Search\Engine
{
    protected $client = null; // pazpar2 client/driver
    protected $cache;
    protected $config;

        /**
         * Return the total number of hits for the search
         * Required by abstract parent
         * @return int
         */
        
        public function getHits( Search\Query $search ) {}      


        /**
         * Search and return results
         * Called from SearchController::resultsAction
         * Required by abstract parent
         *
         * @param Query $search         search object
         * @param int $start            [optional] starting record number
         * @param int $max              [optional] max records
         * @param string $sort          [optional] sort order
         *
         * @return Results
         */
        
        public function searchRetrieve( Search\Query $search, $start = 0, $max = 100, $sort = "" )
        {
            $start = $start - 1; // allow for pz2 starting from 0
            $max = $max - 1;
            $results = $this->client->pz2_show($start, $max, $sort);
            $pzt = new Targets(null, array_keys($search->getTargetLocations()));
            $result_set = new MergedResultSet($results, $pzt);

            // fetch facets
            // only if facets should be shown and there are more than facet_min results
            if ( $this->conf()->getConfig('FACET_FIELDS', false) == true && $result_set->total > $this->conf()->getConfig('FACET_MIN', false) )
            {
                $terms = array_keys( $this->conf()->getFacets() );
                $xml = $this->client->pz2_termlist( $terms, $this->conf()->getConfig('FACET_MAX', false) );
                $facets = $this->extractFacets($xml);
                $result_set->setFacets($facets);
            }
                
            return $result_set;
        }   

        /**
         * Return an individual record
         * @param string    pz2 session identifier
         * @param string    record identifier
         * @param array     offset values for each holding
         * @param Targets   Targets object
         * @return Resultset
         */
        public function getRawRecord( $sid, $id, $offset=null, $targets=null )
        {
            $record = $this->client($sid)->pz2_record( $id, $offset ); 

            // need to return a ResultSet, record is a DomDocument
            if ( ! is_null($offset) ) 
            {
                // FIXME MarcRecord and Offset not yet implemented
                $xerxes_record = new MarcRecord(); // convert to xerxes record format first
                $xerxes_record->loadXML( $record );
            } 
            else
            {
                // keep MergedResultSet happy by making it look like single result
                $results = array();
                $results['hits'] = array();
                $results['start'] = 0;
                $results['merged'] = 1; 
                $results['hits'][0] = $record->saveXML();
                $result_set = new MergedResultSet($results, $targets);
            }
      
            return $result_set;
        }

        /**
         * Return an individual record - unused, we use getRawRecord()
         * to avoid the constraint on the number of parameters
         * Required by abstract parent
         *
         * @param string        record identifier
         * @return Resultset
         */

        public function getRecord( $id ){}

        /**
         * Get record to save
         * Required by abstract parent
         *
         * @param string        record identifier
         * @return int          internal saved id
         */
        
         public function getRecordForSave( $id ) {}
        
        /**
         * Return the search engine config
         * Required by abstract parent
         *
         * @return Config
         */
        public function getConfig()
        {
            return $this->conf();
        }

        /**
         * Lazy loader for Config
         */
        public function conf()
        {
        if ( ! $this->config instanceof Config )
        {
            $this->config = Config::getInstance();
        }
        return $this->config;
        }

        /**
        * Return a search query object
        * Replicates parent function to return correct subclass of Query
        * @param Request $request
        * @return Query
        */
        public function getQuery(Request $request )
        {
            if ( $this->query instanceof Query )
            {
                return $this->query;
            }
            else
            {
                return new Query($request, $this->getConfig());
            }
        }

        /* Pazpar2-specific functions */

        /**
         * Given a target key, return a populated Target
         *
         * @param string $target
         * @returns Target
         */
        public function getTarget($target)
        {
            $pzt = new Targets();
            return $pzt->getTarget($target);
        }

        /**
         * Given an array of target keys, return populated Targets
         *
         * @param string $target
         * @returns Target
         */
        public function getTargets($targets)
        {
            $pzt = new Targets();
            return $pzt->getTarget($targets);
        }

        /**
         * Initialize a pazpar2 client, save to cache, and return id for retrieval
         *
         */
        public function initializePazpar2Client()
        {
            $client = new Pazpar2($this->conf()->getConfig('url', true), false, null, $this->client);
            $this->cache()->set($client->getSessionId(), serialize( $client ) );
            $this->client = $client;
            return $this->client->getSessionId();
        }

        /**
         * Remove pazpar2 from the cache
         *
         */
        public function clearPazpar2Client($sid = null)
        {
            if (! is_null($sid) )
            {
                $this->cache()->clear($sid);
            }
            else
            {
                if ( $this->client instanceof Pazpar2 )
                {
                    $this->cache()->clear($this->client->getSessionId());
                }
            }
            $this->client = null;
        }

    /* called on serialization */
        public function __sleep()
        {
        // nothing to do for now
        }
        
    /* called on unserialization */
        public function __wakeup()
        {
                $this->__construct(); // parent constructor
        }
        

    
    /**
     * Initiate the search with pazpar2 for this set of targets
     * @param Query $query  
     * @param integer max_recs      Maximum total records to fetch (optional)
     */
    public function search(Query $query, $max_recs=null) 
    {
        if (isset( $query->limits ) && (sizeof( $query->limits ) > 0 ) )
        {
            $terms = array();
            foreach($query->limits as $limit_term)
            {
                $facets[] = urlencode( preg_replace('/facet\./', '', $limit_term->field) . $limit_term->relation . $limit_term->value );    
            }                                                                    
        }
        else
        {
            $facets = null;
        }

        // calculate records per target to retrieve (a fixed total, divided by
        // number of targets)
        if ($max_recs == null)
        {
            // if max_recs not set by user, use default from configuration
            $max_recs = $this->conf()->getConfig('MAX_RECORDS', false);
        }
        $targets = $query->getTargetLocations();
        $no_recs = floor( $max_recs / count( $targets ) );

        // start the search
        $sid = $query->sid;
        $this->client($sid)->search( $query->toQuery(), $targets, $facets, $no_recs );
                
    }

    /**
     * User has decided to end a search early
     * @param string $sid   Pazpar2 session id
     */
    public function setFinished($sid)
    {
        $this->client($sid)->setFinished();
        // update cached version with finished flag
        $this->cache()->set($sid, serialize( $this->client ) );
    }

    /**
     * Check the status of the search
     *
     * @param string $sid   Pazpar2 session id
     * @return Status
     */
    public function getSearchStatus($sid)
    {
        $status = new Status();

        // test for early termination
        if ( $this->client($sid)->isFinished() )
        {       
            $status->setFinished( true );
        }
        else
        {
            // get latest statuses from pazpar2 as a hash
            $result = $this->client->pz2_bytarget();

            $status->setXml( $result['xml'] );
            unset( $result['xml'] );
                
            foreach($result as $k => $v)
            {
                $status->addTargetStatus($v);
            }
            $status->setProgress($this->client->getProgress());

            // see if search is finished
            if ( $this->client->isFinished() )
            {
                $status->setFinished( true );
                // update cached version with finished flag
                $this->cache()->set($sid, serialize( $this->client ) );
            }
        }
                
        return $status;
    }
        
    /**
     * Get facets
     */
    public function getFacets()
    {
        return $this->cache()->get("facets-" . $this->getId());
    }

    /** called from pingAction 
     * @param string $sid   Pazpar2 session id
     * @return boolean live 
     */
    public function ping($sid)
    {
        return $this->client($sid)->pz2_ping();
    }
        
    /**
    * Lazyload Cache
    */
    protected function cache()
    {
        if ( ! $this->cache instanceof Cache )
        {
            $this->cache = new Cache();
        }
        return $this->cache;
    }

    /**
    * Lazyload cached Pz2 client
    */
    protected function client($sid)
    {
        if ( ! $this->client instanceof Pazpar2 )
        {
            $client = unserialize( $this->cache()->get($sid) );
            if ( is_object( $client ) ) {
                $this->client = $client;
            }
        }
        if (! $this->client instanceof Pazpar2 )
            throw new \Exception("Session $sid lost");

        return $this->client;
    }

    /**
     * Parse facets out of the response
     *
     * @param DOMDocument $dom  pazpar2 XML
     * @return Facets
     */
     protected function extractFacets(\DOMDocument $dom)
     {
        $facets = new Search\Facets();

       //echo $dom->saveXML();

        $groups = $dom->getElementsByTagName("list");

        if ( $groups->length > 0 )
        {
            // we'll pass the facets into an array so we can control both which 
            // ones appear and in what order in the Xerxes config

            $facet_group_array = array();

            foreach ( $groups as $facet_group )
            {
                $facet_values = $facet_group->getElementsByTagName("term");

                // if only one entry, then all the results have this same facet, 
                // so no sense even showing this as a limit 
                if ( $facet_values->length <= 1 ) 
                { 
                    continue;
                } 
                $group_internal_name = $facet_group->getAttribute("name"); 
                $facet_group_array[$group_internal_name] = $facet_values; 
            } 
            // now take the order of the facets as defined in xerxes config 
            foreach ( $this->conf()->getFacets() as $group_internal_name => $facet_values )
            { 
                // we defined it, but it's not in the pazpar2 response 
                if ( ! array_key_exists($group_internal_name, $facet_group_array) ) 
                { 
                    continue; 
                }

                $group = new Search\FacetGroup(); 
                $group->name = $group_internal_name; 
                $group->public = $this->conf()->getFacetPublicName($group_internal_name); 
                // get the actual facets out of the array above 
                $facet_values = $facet_group_array[$group_internal_name]; 
                // and put them in their own array so we can mess with them 
                $facet_array = array(); 
                foreach ( $facet_values as $facet_value ) 
                { 
                    $name = $facet_value->getElementsByTagName("name")->item(0)->nodeValue;
                    // pz2 returns authors with a trailing comma
                    // sometime also get unwanted fullstop
                    $name = trim($name, ",. "); 
                    $counts = $facet_value->getElementsByTagName("frequency");
                    $count = $counts->item(0)->nodeValue;
                    $facet_array[$name] = $count;
                } 

                // sort facets into descending order of frequency
                arsort($facet_array); // assume not date

                $group = $this->conf()->getFacetObjects($group, $group_internal_name, $facet_array);
                $facets->addGroup($group); 
            } 
        }
        return $facets; 
    } 

}

