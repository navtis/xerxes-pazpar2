<?php

namespace Application\Model\Pazpar2;

/**
 * Library access mapper for pazpar2
 * Each Libraries object represents a single institution
 * Class which can be extended to allow configuration,
 * database or API control of libraries (this class is a wrapper for its children)
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Libraries
{
    protected $libraries = array(); // array of Libraries indexed by id
    private $manager; // subclass to delegate calls to
    protected $centroid;

    /**
     * Constructor
     * Populate with specified libraries for a target, or all for that target if none
     * specified
     */	
    public function __construct($pz2_key, $libraries = null)
    {
	// the 'real' class is given by the configuration file
	$config = Config::getInstance();
        $class = $config->getConfig("datasource") . 'Libraries';
        $class = 'Application\Model\Pazpar2\\' .  $class;
	$this->manager = new $class($pz2_key);

        $objects = $this->manager->getIndividualLibraries($libraries);
        foreach($objects as $object)
        {
            $this->libraries[$object->library_id] = $object;
        }
    }

    protected function calculateCentroid()
    {
        $maxlat = $maxlong = null;
        $minlat = $minlong = null;
        foreach($this->libraries as $lib)
        {
            if ( is_null( $maxlat ) )
                $maxlat = $lib->location['latitude'];
            if ($lib->location['latitude'] > $maxlat)
                $maxlat = $lib->location['latitude'];
            if ( is_null( $minlat ) )
                $minlat = $lib->location['latitude'];
            if ($lib->location['latitude'] < $minlat)
                $minlat = $lib->location['latitude'];

            if ( is_null( $maxlong ) )
                $maxlong = $lib->location['longitude'];
            if ($lib->location['longitude'] > $maxlong)
                $maxlong = $lib->location['longitude'];
            if ( is_null( $minlong ) )
                $minlong = $lib->location['longitude'];
            if ($lib->location['longitude'] < $minlong)
                $minlong = $lib->location['longitude'];
        }
        $lat = ( $maxlat + $minlat ) / 2; 
        $long = ( $maxlong + $minlong ) / 2; 

        return array('latitude' => $lat, 'longitude' => $long);
    }

    public function getCentroid()
    {
        return $this->centroid;
    }

    /**
     * Get one or a set of libraries: must be implemented by subclasses 
     * If this Libraries object is populated, universe is the libraries within
     * the object; otherwise all libraries for this institution
     *
     * @param mixed $keys		[optional] null returns all libraries, array returns a list of libraries by id, string id returns single id
     * @return array			array of Library objects
     */
	
    public function getIndividualLibraries($keys = null)
    {
        if ( ! empty($this->libraries) )
        {
            if ($keys == null)
            {
                return $this->libraries;
            }
            else if( is_array($keys) )
            {
                $ls = array();
                foreach( $keys as $key )
                {
                    if ( isset( $this->libraries[$key] ) )
                    {
                        $ls[] = $this->libraries[$key]; 
                    }
                }
                return $ls;
            }
            else
            {
                if ( isset( $this->libraries[$keys] ) )
                {
                    return $this->libraries[$keys];
                }
                else
                {
                    return null;
                }
            }
        }
        else
        {
            return $this->manager->getIndividualLibraries($keys);
        }
    }

    public function getLibraryKeys()
    {
        return (array_keys($this->libraries));
    }

    public function getLibraryLocations()
    {
        $locations = array();
        foreach($this->libraries as $l)
        {
           $locations[$l->getKey()] = $l->getLocation();
        }
        return $locations;
    }

    /* used by Parser::addToXml() to convert this object's contents to XML
    */
    public function toArray()
    {
        return $this->libraries;
    }
}
