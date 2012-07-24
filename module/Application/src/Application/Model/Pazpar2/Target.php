<?php

namespace Application\Model\Pazpar2;

use Application\Model\Authentication\User,
    Xerxes\Utility\DataValue,
    Xerxes\Utility\Parser,
    Zend\Debug;

/**
 * target
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Target extends DataValue  
{
    public $target_id; 
    public $pz2_key;
    public $target_pz2_zurl;
    public $linkback_url;
    public $library_url;
    public $title_short;
    public $title_long;
    public $position; // display order
    private $xml; // simplexml
    private $vars;
    public $data; // string version of $xml
	
	/**
	 * Load data from target results array
	 *
	 * @param array $arr
	 */
	
	public function load( $arr )
	{
	    $this->vars = $arr; // keep for use later
	    $this->target_id = $this->vars['pz2_key'];
	    $this->pz2_key = $this->vars['pz2_key'];
	    $this->pz2_location = $this->vars['z3950_location'];
	    $this->library_url = $this->vars['library_url'];
	    $this->linkback_url = $this->vars['linkback_url'];
	    $this->title_short = $this->vars['short_name'];
            $this->title_long = $this->vars['display_name'];
            if ( isset($this->vars['domain']) )
            {
                $this->domain = $this->vars['domain'];
            }
            if ( isset($this->vars['sort_name']) )
            {
                $this->sort_name = $this->vars['sort_name'];
            }
	    parent::load($arr);
	}

        public function getKey()
        {
            return $this->pz2_key;
        }

        public function getName()
        {
            return $this->title_short;
        }

        public function getLocation()
        {
            return $this->pz2_location;
        }

	public function setPosition($i)
	{
	    $this->position = $i;
	}

	/**
	 * Serialize to XML
	 *
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
	    if ( $this->target_id == "" )
	    {
		throw new \Exception("Cannot access data, it has not been loaded");
	    }
	    $xml = new \DOMDocument();
	    $xml->loadXML("<target />");
	    $xml->documentElement->setAttribute("target_id", $this->target_id);
	    $xml->documentElement->setAttribute("position", $this->position);
	    if (isset( $this->textValue ) )
	    {
            // check checkbox
		$xml->documentElement->setAttribute("textValue", $this->textValue);
	    }
	    foreach( $this->vars as $k => $v )
            {
	        $node = $xml->createElement($k, $v);
		$xml->documentElement->appendChild($node);
            }
	    return $xml;
	}
}
