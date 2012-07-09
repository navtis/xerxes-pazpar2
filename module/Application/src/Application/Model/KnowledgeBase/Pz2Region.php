<?php

namespace Application\Model\KnowledgeBase;

use Xerxes\Utility\DataValue,
	Xerxes\Utility\Parser;

/**
 * Region
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Pz2Region extends DataValue
{
	public $id;
	public $name;
	public $region_key;
	public $lang;
	public $subregions = array();
	public $targets = array();
	public $sidebar = array();
	
	/**
	 * Converts a string to a normalized (no-spaces, non-letters) string
	 *
	 * @param string $subject	original string
	 * @return string			normalized string
	 */
	
	public static function normalize($region)
	{
		// this is influenced by the setlocale() call with category LC_CTYPE; see PopulateDatabases.php
		
		$normalized = iconv( 'UTF-8', 'ASCII//TRANSLIT', $region ); 
		$normalized = Parser::strtolower( $normalized );
		
		$normalized = str_replace( "&amp;", "", $normalized );
		$normalized = str_replace( "'", "", $normalized );
		$normalized = str_replace( "+", "-", $normalized );
		$normalized = str_replace( " ", "-", $normalized );
		
		$normalized = Parser::preg_replace( '/\W/', "-", $normalized );
		
		while ( strstr( $normalized, "--" ) )
		{
			$normalized = str_replace( "--", "-", $normalized );
		}
		
		return $normalized;
	}

	public function toXML()
	{
		$xml = new \DOMDocument();
		$xml->loadXML("<region />");
		$xml->documentElement->setAttribute("name", $this->name);
		$xml->documentElement->setAttribute("region_key", $this->region_key);
		
		foreach ( $this->subregions as $subregion )
		{
			$import = $xml->importNode($subregion->toXML()->documentElement, true);
			$xml->documentElement->appendChild($import);
		}
		foreach ( $this->targets as $target )
		{
			$import = $xml->importNode($target->toXML()->documentElement, true);
			$xml->documentElement->appendChild($import);
		}
		
		return $xml;
	}
}
