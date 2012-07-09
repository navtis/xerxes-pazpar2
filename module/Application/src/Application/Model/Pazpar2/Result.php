<?php

namespace Application\Model\Pazpar2;

use Application\Model\Bx\Engine as BxEngine,
    Application\Model\Search\Holdings,
    Application\Model\Search\Result as SearchResult,
        Xerxes\Record,
        Xerxes\Utility\Cache,
        Xerxes\Utility\Parser,
        Xerxes\Utility\Registry,
        Zend\Http\Client;

/**
 * Search Record
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Result extends SearchResult
{
        public $url_open; // open url
        public $openurl_kev_co;  // just the key-encoded-values of the openurl
        public $xerxes_record; // record
        public $original_record; // original xml
        public $holdings; // NOT USED FOR PZ2
        public $mergedHolding; // container for holdings from LMSs
        public $recommendations = array(); // recommendation objects    
        public $reviews; // reviews
        
        protected $registry; // global config
        protected $config; // local config
        protected $sid; // open url sid
        protected $link_resolver; // link resolver
        
        /**
         * Constructor
         * 
         * @param Xerxes_Record $record         record
         * @param Config $config                        local config
         */
        
        public function __construct(Record $record, Config $config)
        {
                $this->xerxes_record = $record;
                $this->registry = Registry::getInstance();
                $this->config = $config;
                
                // link resolver stuff
                
                $this->link_resolver = $this->config->getConfig("LINK_RESOLVER_ADDRESS", false, $this->registry->getConfig("LINK_RESOLVER_ADDRESS", false));
                $this->sid = $this->registry->getConfig("APPLICATION_SID", false, "calstate.edu:xerxes");
                
                if ( $this->link_resolver != null )
                {
                        $this->url_open = $record->getOpenURL($this->link_resolver, $this->sid);
                }
                
                $this->openurl_kev_co = $record->getOpenURL(null, $this->sid);
                
                // holdings - get data already available from Z Server
                
                $this->mergedHolding = $record->getMergedHolding();
                
        }
        
        /**
         * Enhance record with bx recommendations
         */
        
        public function addRecommendations()
        {
                $configToken = $this->registry->getConfig("BX_TOKEN", false);
                                                
                if ( $configToken != null && $this->link_resolver != null )
                {
                        $configBX = $this->registry->getConfig("BX_SERVICE_URL", false);
                        $configMinRelevance     = (int) $this->registry->getConfig("BX_MIN_RELEVANCE", false, 0);
                        $configMaxRecords = (int) $this->registry->getConfig("BX_MAX_RECORDS", false, 10);
                        
                        $bx_engine = new BxEngine($configToken, $this->sid, $configBX);
                        $bx_records = $bx_engine->getRecommendations($this->xerxes_record, $configMinRelevance, $configMaxRecords);
                        
                        if ( count($bx_records) > 0 ) // only if there are any records
                        {
                                foreach ( $bx_records as $bx_record )
                                {
                                        $result = new Result($bx_record, $this->config);
                                        array_push($this->recommendations, $result);
                                }
                        }
                }
        }

    /**
         * Return merged holdings
         * 
         * @return MergedHolding object
         */
        
        public function getMergedHolding()
        {
                return $this->mergedHolding;
        }


        /**
         * Fetch item and holding records from an ILS for this record
         */
        
        public function fetchHoldings()
        {
                $xerxes_record = $this->getXerxesRecord();
                
        // FIXME this comes from the targets
                //$id = $xerxes_record->getRecordID(); // id from the record
                //$cache_id = $xerxes_record->getSource() . "." . $id; // to identify this in the cache
                //$url = $this->config->getConfig("LOOKUP"); // url to availability server
                
        $mergedHolding = $this->mergedHolding()->getHoldings();

        foreach($mergedHolding as $holdings)
        {
                    // either electronic only or already have ciculation info
                
                    if ( $holdings->hasPhysicalHoldings() == false || $holdings->hasCirculationData() == true ) )
                    {
                            return null;
                    }           

                    // get the data
/* FIXME Can't do this yet              
                    $url .= "?action=status&id=" . urlencode($id);
                
                    // @todo this needs to be gotten from a factory or something
                
                    $client = new Client();
                    $client->setUri($url);
                    $client->setConfig(array('timeout' => 5));
                
                    $data = $client->send()->getBody();
                
                    // echo $url; exit;
                
                    // no data, what's up with that?
                
                    if ( $data == "" )
                    {
                            throw new \Exception("could not connect to availability server");
                    }           
                
                
                    // response is (currently) an array of json objects
                
                    $results = json_decode($data);
                
                    // parse the response
                
                    if ( is_array($results) )
                    {
                            if ( count($results) > 0 )
                            {
                                    // now just slot them into our item object
                                
                                    foreach ( $results as $holding )
                                    {
                                            $is_holding = property_exists($holding, "holding"); 
                                                                                
                                            if ( $is_holding == true )
                                            {
                                                    $item = new Holding();
                                                    $this->holdings->addHolding($item);
                                            }
                                            else
                                            {
                                                    $item = new Item();
                                                    $this->holdings->addItem($item);
                                            }
                                        
                                            foreach ( $holding as $property => $value )
                                            {
                                                    $item->setProperty($property, $value);
                                            }
                                    }
                            }
                    }
                
                    // cache it for the future
                
                    // @todo: zend\cache
                
                    $cache = new Cache();
                
                    $expiry = $this->config->getConfig("HOLDINGS_CACHE_EXPIRY", false, 2 * 60 * 60); // expiry set for two hours
                    $expiry += time(); 
                
                    $cache->set($cache_id, serialize($this->holdings), $expiry);
        }
*/
    return null;
    }

        /**
         * Add reviews from Good Reads
         */
        
        public function addReviews()
        {
                $xerxes_record = $this->getXerxesRecord();
                $isbn = $xerxes_record->getISBN();
                
                $key = $this->registry->getConfig("GOOD_READS_API_KEY", false );
                
                if ( $key != null )
                {
                        $url = "http://www.goodreads.com/book/isbn?isbn=$isbn&key=$key";
                        
                        $data = Parser::request($url, 5);
                        
                        if ( $data != "" )
                        {
                                $xml = new \DOMDocument();
                                $xml->recover = true;
                                $xml->loadXML($data);
                                
                                $this->reviews = $xml;
                        }
                }
        }
        
        /**
         * Get the Xerxes_Record object
         */
        
        public function getXerxesRecord()
        {
                return $this->xerxes_record;
        }
}
