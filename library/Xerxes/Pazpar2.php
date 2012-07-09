<?php

namespace Xerxes;

use Zend\Http\Client,
    Xerxes\Utility\Parser;

/**
 * Pazpar2 Client
 * 
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license 
 * @version 
 * @package Xerxes
 */

class Pazpar2
{ 
    public $baseurl = "";	        // pazpar2 server address
    public $per_session_dbs = false;
    public $service = null;
    public  $sid = null;                // pazpar2 session id
    public $finished = false;           // flag indicating pz2 is done searching
    private $client;                    // http client
	
    /**
     * Create a new Pazpar2 Client
     * Throws exception if fails to get session
     *
     * @param string $baseurl	        the pazpar2 address url
     * @param boolean $per_session_dbs	[optional]
     * @param string $service           [optional]
     * @param Client $client	        [optional] subclass of Zend\Client (defaults to  Http)
    */
	
    public function __construct( $baseurl, $per_session_dbs = false, $service = null, Client $client = null )
    {						
        $this->baseurl = $baseurl;
        $this->per_session_dbs = $per_session_dbs;
        $this->service = $service;

        if (! is_null($client) )
        {
            $this->client = $client;
	}
        else
	{
            $this->client = new Client();
        }

        // if this fails throws an exception
        $this->sid = $this->pz2_init($this->per_session_dbs, $this->service);
    }

    /**
     * Session id is used as a key to access the Client in a Zend session
     *
     * return string $sid
     */
    public function getSessionId()
    {
        return $this->sid;
    }
	
    public function __sleep()
    {
        return Parser::removeProperties(get_object_vars($this), array('client'));
    }
                    
    public function __wakeup()
    {
        $this->client = new Client();
        if (! $this->pz2_ping( $this->sid ) )
        {
            throw new \Exception( 'Session died - data lost' );
        }
    }

    /**
     * Initiates search request
     *
     * @param string $query		pz2 formatted query 
     * @param array|string $targets	selected targets, array if multiple
     * @param array $facets
     * @param bool $wait                [optional] whether to wait until results are availble (default false)
     * @return mixed                    if wait = false, returns group number as string; else search progress as DOMDocument
    */

    public function search( $query, $targets=null, $facets=null, $maxrecs, $wait = false) 
    {
        $this->pz2_search( $query, $targets, $facets, null, $maxrecs );
		
        if ($wait)  // blocking
        {
            while ( ! $this->isFinished())
            {
                sleep(1);
            }
            // AIM25 uses the totals only
            return ( $this->pz2_bytarget() );
            //return ( $this->pz2_show( 0, 500) );
        }
        else
        {
	    $this->status = $this->pz2_stat( );
            return($this->status);
            // subsequent calls to pz2_stat come from javascript
            // assuming the search has not finished immediately on this
            // first call
        }
    }

    /**
     * Get progress fraction
     *
     * @return float$maxrecs<= $progress <= 1.0
    */ 

    public function getProgress()
    {
        $status = $this->pz2_stat();
        return $status["progress"];
    }
	
    /**
    * Get status
    *
    * @return array $status
    */ 

    public function getStatus( $sid )
    {
        return $this->pz2_stat();
    }
	

    /**
    * User is terminating a search early
    */
    public function setFinished()
    {
        $this->finished = true;
    }

    /**
    * Check if pazpar2 is done searching
    * Once a search has finished once, it stays finished
    * regardless of status of targets (eg for facets, sorting)
    * Use to stop hits page autorefreshing
    * @return bool      true if finished, false if not
    */ 

    public function isFinished( )
    {
        if ( $this->finished == true )
        {
            return true;
        }
        else
        {
            $status = $this->pz2_stat();
            if ( $status["activeclients"] == 0 )
            {
                $this->finished = true;
                return true;
            }
            if ( $status["progress"] == "1.0" )
            {
                $this->finished = true;
                return true;
            }
        }
	return false; // by default, still running 
    }
	

    /**
    * Execute a single pazpar2 command throwing exception on common problems
    *
    * @param string $url	    The service request
    * @param string command    Name of the comman used
    * @returns DomDocument     pazpar2 response or false if session expired
    */
    protected function getRawResponse( $url, $command )
    {
	$doc = new \DOMDocument();
	// fetch the data
	$this->client->setUri($url);
	$response = $this->client->send();
        $statusCode = $response->getStatusCode();
  
	if ( ($response->isClientError() && $statusCode != 417)  || $response->getBody() == "")
	{
            throw new \Exception( "Cannot process search at this time." );
	}

	// load into xml
		
	$doc->loadXML($response->getBody());
		
	// no response?
		
	if ( is_null( $doc->documentElement ) )
	{
	    throw new \Exception("cannot connect to pazpar2 server");
	}

        $xpath = new \DOMXPath($doc);

        if ( $response->getStatusCode() == '417' )
        {
            $code = $xpath->query("//error")->item(0)->getAttribute("code");
            $msg = $xpath->query("//error")->item(0)->getAttribute("msg");
            if ($code == '1' && $command == 'ping')  // session died
            {
                return false;
            }
            else 
            {
                throw new \Exception("Cannot execute command $command for session $this->sid: $msg");
            }
        }
		
		
	if ( $xpath->query("//status")->length > 0 )
	{
            $status = $xpath->query("//status")->item(0)->nodeValue;
	    if ( $status != "OK" ) 
            {
                throw new \Exception("Cannot execute command $command: $status");
            }
        }
	return $doc;
    }

    /**
     * Initialize pazpar2 session
     *
     * Uses per_session_db and service object variables
     * @returns string session_id
     */
    protected function pz2_init()
    {
        $url = $this->baseurl . '?command=init';

        if ($this->per_session_dbs == true)
        {
            $url .= "&clear=1";
        }

        if ( !is_null( $this->service ) )
        {
            $url .= "&service=$this->service";
        }

        $response = $this->getRawResponse( $url, "init" );
        return ($response->getElementsByTagname("session")->item(0)->nodeValue);
    }

    /**
     * Check pazpar2 session status
     *
     * Uses per_session_db and service object variables
     * @returns bool true if session exists
     */
    public function pz2_ping( )
    {
        $url = $this->baseurl . "?command=ping&session=$this->sid";
        $response = $this->getRawResponse( $url, "ping" );

        if ($response == false)
            return false;
        else
            return true;
    }        

    /**
     * Set pazpar2 target settings
     *
     * @param array $settings   array of pazpar2 settings
     * @returns null
    */
    protected function pz2_settings( $settings )
    {
        $url = $this->baseurl . "?command=settings&session=$this->sid";
        // FIXME escape settings here?
        foreach ($settings as $setting) 
        {
           $url .= '&' . $setting;
        }
        $response = $this->getRawResponse( $url, "settings" );
    }

    
    /**
     * Initialize a pazpar2 search
     *
     * @param string $query     user search query
     * @param array $targets    list of targets to use (null = all targets)
     * @param array $facets     array of limiting values for search
     * @param int $startrecs    starting record to receive per target (default 0)
     * @param int $maxrecs      Maxmimum records per target (default 100)
     * @returns null
     */
    
    protected function pz2_search( $query, $targets=null, $facets=null, $startrecs=null, $maxrecs=null )
    {
        $url = $this->baseurl . "?command=search&session=$this->sid";
        // should already be urlescaped
        $url .= "&query=$query";

        if ( !is_null( $targets ) )
        {
            $alltargs = implode('|', $targets);
            $alltargs = 'pz:id=' . $alltargs;
            $alltargs = urlencode($alltargs);
            $url .= "&filter=$alltargs";
        }
        if ( !is_null( $facets ) )
        {
            // escape any commas inside limits
            foreach( $facets as &$f )
            {
                $f = urldecode( $f );
                $f = preg_replace( '/\,/', '\\\,', $f );
                $f = urlencode( $f );
            }
            $allfacets = implode( ',', $facets );
            $url .= "&limit=$allfacets";
        }
        if ( !is_null( $startrecs ) )
            $url .= "&startrecs=$startrecs";

        if ( !is_null( $maxrecs ) )
            $url .= "&maxrecs=$maxrecs";
echo($url); 
        $response = $this->getRawResponse( $url, "search" );
        
        // this just kicks off the search; nothing to return, yet
    }

    /**
     * Check pazpar2 search status
     *
     * @returns array           status values extracted from xml
     */
    protected function pz2_stat( )
    {
        $url = $this->baseurl . "?command=stat&session=$this->sid";
        $response = $this->getRawResponse( $url, "stat" );
        $nodes = $response->getElementsByTagName("stat")->item(0)->childNodes;
        $vals = array();
        foreach($nodes as $node)
        {
            $vals[$node->nodeName] = $node->nodeValue;
        }
        return $vals;
    }

    /**
     * Show records retrieved from search 
     *
     * @param int $start        start record position (default 0)
     * @param int $num          number of records to show (default 20)
     * @param array $sorts      array of sort fields in descending order 
     * @returns array           status values extracted from xml, with an arrays of dom elements for the actual records
     */
    public function pz2_show( $start=null, $num=null, $sorts=null )
    {
        $url = $this->baseurl . "?command=show&session=$this->sid";
        if ( !is_null( $start ) )
            $url .= "&start=$start";
        if ( !is_null( $num ) )
            $url .= "&num=$num";
        if ( !is_null( $sorts ) )
        {
            if ( is_array( $sorts) )
                $url .= '&sort=' . implode(",", $sorts);
            else
                $url .= '&sort=' . $sorts;
        }
        $response = $this->getRawResponse( $url, "show" );

//echo( $response->saveXML() ); //exit;
        $result = array();
        $hits = array();
        $nodes = $response->getElementsByTagName("show")->item(0)->childNodes;
        foreach($nodes as $node)
        {
            if ( $node->nodeName != 'hit' )
            {
                $result[$node->nodeName] = $node->nodeValue;
            }
            else
            {
		$singledoc = new \DOMDocument("1.0", "ISO-8859-15");
                $singledoc->loadXML("<record></record>");
                $node = $singledoc->importNode( $node, true );
                $singledoc->documentElement->appendChild( $node );
                $hits[] = $singledoc->saveXML();
            }
        }
        $result['hits'] = $hits;

        return $result;
    }
    
    /**
     * Show single record retrieved from search 
     *
     * @param string $recordid  id for a single record 
     * @param integer $offset   target number for raw record
     * @param string $syntax    syntax for raw records
     * @param string $esn       element set name for raw records
     * @param string $binary    return application/octet stream. NOT SUPPORTED
     * @param int $num          number of records to show (default 20)
     * @returns DomDocument     single dom record
     */
    public function pz2_record( $recordid, $offset_arr=null, $syntax=null, $esn=null, $binary=null )
    {
        $url = $this->baseurl . "?command=record&session=$this->sid";
        $url .= "&id=$recordid";
        if ( !is_null( $offset_arr ) )
        {
            // FIXME need to loop for each holding?
            // FIXME just taking first one for now
            $offset = array_shift($offset_arr);

            $url .= "&offset=$offset";
            // remaining options depend on knowing the offset
            if ( !is_null( $syntax ) )
            {
                $url .= "&syntax=$syntax";
            }
            if ( !is_null($esn ) )
            {
                $url .= "&esn=$esn";
            }
        }
        $response = $this->getRawResponse( $url, "record" );
        //var_dump($response->saveXML());
        return $response;
    }

    /**
     * Show term list resulting from a search 
     *
     * @param array  $terms     terms to retrieve (default subject)
     * @param integer $num      maximum number of terms to return
     * @returns DomDocument     dom results
     */
    public function pz2_termlist( $terms=null, $num=null )
    {
        $url = $this->baseurl . "?command=termlist&session=$this->sid";
        if ( !is_null( $terms ) )
        {
            $url .= "&name=" . implode( ',', $terms );
        }
        if ( !is_null( $num ) )
        {
            $url .= "&num=$num";
        }
        $response = $this->getRawResponse( $url, "termlist" );
        return $response;
    }

    /**
     * Show target statuses
     *
     * Documentation unclear about 'id' parameter
     * @returns array           status array per active client
     */
    public function pz2_bytarget( )
    {
        $url = $this->baseurl . "?command=bytarget&session=$this->sid";
    
        $response = $this->getRawResponse( $url, "bytarget" );
        $targets = $response->getElementsByTagname("target");
        $results = array();
        foreach( $targets as $target )
        {
            $nodes = $target->childNodes;
            $vals = array();
            foreach($nodes as $node)
            {
                $vals[$node->nodeName] = $node->nodeValue;
            }
            $results[$vals["id"]] = $vals;
        } 
        $results['xml'] = $response;

        return $results;
    }
}
