<?php

namespace Application\Model\Pazpar2;

use 
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

class Library extends DataValue  
{
    public $pz2_key;
    public $library_id;
    public $type;
    public $name;
    public $description;
    public $home_url;

    public $address = array(); // address + postcode
    public $contact = array(); // phone + email
    public $web = array(); // opac_url + information_url
    public $location = array(); // longitude, latitude, easting, northing, district

    public $subjects; // Subjects covered by this library

    private $vars;
	
	/**
	 * Load data from source array
	 *
	 * @param array $arr
	 */
	
	public function load( $arr )
	{
            $this->vars = $arr; // keep for use later
            $this->target_id = $arr['pz2_key'];

            foreach($arr as $k => $v)

            $this->pz2_key = $arr['pz2_key'];
            $this->library_id = $arr['library_id'];
            $this->name = $arr['full_name'];
            $this->description = isset($arr['description'])?$arr['description']:'';
            $this->type = isset($arr['type'])?$arr['type']:'';
            $this->web['opac_url'] = isset($arr['opac_url'])?$arr['opac_url']:'';
            $this->web['information_url'] = isset($arr['visitor_information_url'])?$arr['visitor_information_url']:'';
            if ($this->type != 'virtual')
            {
                $this->address['address'] = isset($arr['address'])?$arr['address']:'';
                $this->address['postcode'] = isset($arr['postcode'])?$arr['address']:'';
                $this->contact['phone'] = isset($arr['phone'])?$arr['phone']:'';
                $this->contact['email'] = isset($arr['email'])?$arr['email']:'';
                $this->location['district'] = isset($arr['district'])?$arr['district']:'';
                $this->location['longitude'] = isset($arr['longitude'])?$arr['longitude']:'';
                $this->location['latitude'] = isset($arr['latitude'])?$arr['latitude']:'';
                $this->location['northing'] = isset($arr['northing'])?$arr['northing']:'';
                $this->location['easting'] = isset($arr['easting'])?$arr['easting']:'';
            }
            $subjects = new Subjects();
            $this->subjects = $subjects->getSubjectsByLibrary($this->pz2_key, $this->library_id);
	    parent::load($arr);
	}

        public function getInstitutionKey()
        {
            return $this->pz2_key;
        }

        public function getName()
        {
            return $this->name;
        }

        public function getLocation()
        {
            return $this->location;
        }

        public function getSubjects()
        {
            return $this->subjects;
        }


	/**
	 * Serialize to XML
	 *
	 * @return DOMDocument
	 */
	
	public function toXML()
	{
	    if ( $this->library_id == "" )
	    {
		throw new \Exception("Cannot access data, it has not been loaded");
	    }
	    $xml = new \DOMDocument();
	    $xml->loadXML("<library />");
	    $xml->documentElement->setAttribute("library_id", $this->library_id);
	    $xml->documentElement->setAttribute("pz2_key", $this->pz2_key);
	    foreach( $this->vars as $k => $v )
            {
	        $node = $xml->createElement($k, htmlentities($v));
		$xml->documentElement->appendChild($node);
            }
	    return $xml;
	}
}
