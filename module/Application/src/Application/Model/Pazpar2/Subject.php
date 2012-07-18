<?php

namespace Application\Model\Pazpar2;

use Application\Model\Authentication\User,
    Xerxes\Utility\DataValue,
    Xerxes\Utility\Parser,
    Zend\Debug,
    Xerxes\Utiltity\Restrict;

/**
 * subject 
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Subject extends DataValue  
{
    public $subject_id; 
    public $name;
    public $url;
    private $xml; // simplexml
    private $vars;
    private $position;
    public $data; // string version of $xml
	
	/**
	 * Load data from subject results array
	 *
	 * @param array $arr
	 */
	
	public function load( $arr )
	{
	    $this->vars = $arr; // keep for use later
	    $this->subject_id = $this->vars['id'];
	    $this->name = $this->vars['name'];
	    $this->url = $this->vars['url'];
	    $this->position = $this->vars['position'];
	    parent::load($arr);
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
	    if ( $this->subject_id == "" )
	    {
		throw new \Exception("Cannot access data, it has not been loaded");
	    }
	    $xml = new \DOMDocument();
	    $xml->loadXML("<subject />");
	    $xml->documentElement->setAttribute("subject_id", $this->subject_id);
	    $xml->documentElement->setAttribute("position", $this->position);
	    $xml->documentElement->setAttribute("url", $this->url);
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
