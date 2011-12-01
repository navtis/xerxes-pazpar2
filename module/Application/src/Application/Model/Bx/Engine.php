<?php

namespace Application\Model\Bx;

use Xerxes\Utility\Parser,
	Xerxes\Record,
	Zend\Http\Client;

/**
 * Search Engine
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version $Id: Engine.php 2020 2011-11-17 14:39:51Z dwalker.calstate@gmail.com $
 * @package Xerxes
 */

class Engine
{
	protected $token;
	protected $client;
	
	public function __construct($token, $sid, $url = null)
	{
		$this->token = $token;
		$this->sid = $sid;
		
		if ( $url == null )
		{
			$this->url = "http://recommender.service.exlibrisgroup.com/service";
		}
		else
		{
			$this->url = $url;
		}
	}
	
	public function setClient(Client $client)
	{
		$this->client = $client;
	}
	
	
	public function getClient()
	{
		if ( ! $this->client instanceof Client )
		{
			$this->client = new Client();
		}
	
		return $this->client;
	}	
	
	
	public function getRecommendations(Record $xerxes_record, $min_relevance = 0, $max_records = 10)
	{
		$bx_records = array();
		
		// now get the open url
		
		$open_url = $xerxes_record->getOpenURL(null, $this->sid);
		$open_url = str_replace('genre=unknown', 'genre=article', $open_url);
		
		// send it to bx service
		
		$url = $this->url . "/recommender/openurl?token=" . $this->token . "&$open_url" .
			"&res_dat=source=global&threshold=$min_relevance&maxRecords=$max_records";
		
		try 
		{
			$client = $this->getClient();
			$client->setUri($url);
			$client->setConfig(array('timeout' => 4));
			
			$xml = $client->send()->getBody();
			
			if ( $xml == "" )
			{
				throw new \Exception("No response from bx service");
			}
		}
		catch ( \Exception $e )
		{
			// just issue the exception as a warning
			
			trigger_error("Could not get result from bX service: " . $e->getTraceAsString(), E_USER_WARNING);
			return $bx_records;
		}
		
		// header("Content-type: text/xml"); echo $xml; exit;
		
		$doc = new \DOMDocument();
		$doc->recover = true;
		$doc->loadXML($xml);
		
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace("ctx", "info:ofi/fmt:xml:xsd:ctx");
		
		$records = $xpath->query("//ctx:context-object");
		
		foreach ( $records as $record )
		{
			$bx_record = new Record();
			$bx_record->loadXML($record);
			array_push($bx_records, $bx_record);
		}
		
		if ( count($bx_records) > 0 ) // and only if there are any records
		{
			// first one is the record we want to find recommendations for
			// so skip it; any others are actual recommendations
			
			array_shift($bx_records);
		}
			
		return $bx_records;
	}		
}
