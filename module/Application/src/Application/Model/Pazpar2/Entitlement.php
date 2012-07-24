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

class Entitlement extends DataValue  
{
    public $rule_id;
    public $name;
    public $scheme;
    public $scheme_url;
    public $charges;
    public $requirements;

    private $xml; // simplexml
    private $vars;
    private $position;
    public $data; // string version of $xml
	
	/**
	 * Load data from entitlement results array
	 *
	 * @param array $arr
	 */
	
	public function __Construct( $arr )
	{
            $this->vars = $arr; // keep for use later
            $this->rule_id = $this->vars['access_rule_id'];
	    $this->name = $this->vars['entitlement_name'];
	    $this->scheme = $this->vars['scheme_name'];
	    $this->scheme_url = $this->vars['scheme_url'];
	    $this->charges = $this->vars['charges'];
	    $this->requirement = $this->vars['requirements'];
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
	    if ( $this->rule_id == "" )
	    {
		throw new \Exception("Cannot access data, it has not been loaded");
	    }
	    $xml = new \DOMDocument();
	    $xml->loadXML("<entitlement />");
	    $xml->documentElement->setAttribute("rule_id", $this->rule_id);
	    $xml->documentElement->setAttribute("position", $this->position);
	    foreach( $this->vars as $k => $v )
            {
	        $node = $xml->createElement($k, $v);
		$xml->documentElement->appendChild($node);
            }
	    return $xml;
	}
}
