<?php

namespace Application\Model\Pazpar2;

use Zend\Debug,
    Xerxes\Utility\Factory;

/**
 * An implementation of the abstract Libraries class,
 * selected in configuration
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ConfigLibraries extends Libraries
{
    protected $libraries = array();
    protected $client;

    // Helper function 
    function alphasort( $a, $b )
    {
        return strcmp( $a->name, $b->name );
    }

    /**
     * Constructor
     * 
     */
        
    public function __construct($pz2_key)
    {
        // full set of Libraries for this institution from configuration file
        $this->config = Config::getInstance();
        $xml = $this->config->getXml();

        $query = "//config[@name='targets']/target[@pz2_key='$pz2_key']/library";
        $libraries = $xml->xpath($query);

        $lib_arr = array();

        foreach($libraries as $config_library)
        {
            $arr = array();
            $description = (string)$config_library;
            $arr['description'] = $description;
            foreach($config_library->attributes() as $k => $v)
            {
                $arr[$k] = (string)$v;
            }
            $library = new Library();
            $arr['pz2_key'] = $pz2_key;
            $library->load($arr);
            $lib_arr[] = $library;
        }

        usort( $lib_arr, array($this, 'alphasort') );
        $this->libraries = $lib_arr;
    }


    /**
     * Get one or a set of libraries 
     *
     * @param mixed $keys               [optional] null returns all libraries, array returns a list of libraries by id, string id returns single id
     * @return array                    array of Library objects
     */
        
    public function getLibraries($keys = null)
    {
        $arr = array ( );
        if ( $keys == null )
        {
            // all libraries
            $arr = $this->libraries;
        }
        else
        {
            if (! is_array( $keys ) )
            {
                // convert single id to array
                $keys = array( $keys );
            }
            // list of libraries or single library
            foreach( $keys as $key )
            {
                if ( in_array( $key, $this->libraries ) )
                {
                    $arr[$key] = $this->libraries[$key];
                }
            }
        }               
        
        return $arr;
    }
        
}
