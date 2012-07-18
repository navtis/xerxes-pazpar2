<?php

namespace Application\Model\Pazpar2;

/**
 * Target access mapper for pazpar2
 * Class which can be extended to allow configuration,
 * database or API control of targets (this class is a wrapper for its children)
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Targets
{
    protected $targets = array(); // array of Targets indexed by id
    private $manager; // subclass to delegate calls to

    /**
     * Constructor
     * Populate with specified targets or all targets if none specified
     */	
    public function __construct($targets = null)
    {
	// the 'real' class is given by the configuration file
	$config = Config::getInstance();
        $targetClass = $config->getConfig("datasource") . 'Targets';
        $targetClass = 'Application\Model\Pazpar2\\' .  $targetClass;
	$this->manager = new $targetClass();

        $ts = $this->manager->getIndividualTargets($targets);
        foreach($ts as $t)
        {
            $this->targets[$t->pz2_key] = $t;
        }
    }

    /**
     * Get one or a set of targets: must be implemented by subclasses 
     * If this Targets object is populated, universe is the targets within
     * the object; otherwise all configured targets
     *
     * @param mixed $keys		[optional] null returns all targets, array returns a list of targets by id, string id returns single id
     * @return array			array of Target objects
     */
	
    public function getIndividualTargets($keys = null)
    {
        if ( ! empty($this->targets) )
        {
            if ($keys == null)
            {
                return $this->targets;
            }
            else if( is_array($keys) )
            {
                $ts = array();
                foreach( $keys as $key )
                {
                    if ( isset( $this->targets[$key] ) )
                    {
                        $ts[] = $this->targets[$key]; 
                    }
                }
                return $ts;
            }
            else
            {
                if ( isset( $this->targets[$keys] ) )
                {
                    return $this->targets[$keys];
                }
                else
                {
                    return null;
                }
            }
        }
        else
        {
            return $this->manager->getIndividualTargets($key);
        }
    }

    public function getTargetKeys()
    {
        return (array_keys($this->targets));
    }

    public function getTargetNames()
    {
        $names = array();
        foreach($this->targets as $t)
        {
           $names[$t->getKey()] = $t->getName();
        }
        return $names;
    }

    public function getTargetLocations()
    {
        $locations = array();
        foreach($this->targets as $t)
        {
           $locations[$t->getKey()] = $t->getLocation();
        }
        return $locations;
    }

    /* used by Parser::addToXml() to convert this object's contents to XML
    */
    public function toArray()
    {
        return $this->targets;
    }
}
