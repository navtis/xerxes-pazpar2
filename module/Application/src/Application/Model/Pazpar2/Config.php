<?php

namespace Application\Model\Pazpar2;

use 
    Xerxes\Record\Format,
    Application\Model\Search;

/**
 * Pazpar2 Config
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Config extends Search\Config
{
        protected $config_file = "config/pazpar2";
        private static $instance; // singleton pattern
        
        public static function getInstance()
        {
                if ( empty( self::$instance ) )
                {
                        self::$instance = new Config();
                        $object = self::$instance;
                        $object->init();                        
                }
                return self::$instance;
        }

        /**
         * Get the defined public name of a facet value (overrides parent function)
         * 
         * @param string $internal_group                group internal id
         * @param string $internal_field                internal field id
         * 
         * @return string                                               public name, or the internal field name supplied if not found
         */     
        
        public function getValuePublicName($internal_group, $internal_field)
        {
                if ( strstr($internal_field, "'") || strstr($internal_field, " ") )
                {
                        return $internal_field;
                }
                
                $query = "//config[@name='facet_fields']/facet[@internal='$internal_group']/value[@internal='$internal_field']";
                
                $values = $this->xml->xpath($query);
                
                if ( count($values) > 0 )
                {
                        return (string) $values[0]["public"];
                }
                else
                {
                        return $internal_field;
                }
        }


    public function getMediumMap($group, $keys)
    {
        $map = array();
        $format = new Format();
        foreach($keys as $key)
        {
            $map[$key] = $format->getConstNameForValue($key);
        }
        return $map; 
    }

    /**
     * Get the defined public names of a set of facet values for media
     * This function name is defined in configuration file pazpar2.xml
     * 
     * @param Search\FacetGroup $group            
     * @param string $internal_group            group internal id
     * @param array $keys                   pz2 facet key:count array
     * 
     * @return Search\FacetGroup                    Populated facetgroup object                         
     */ 
    public function getFacetObjects($group, $internal_group, $keys)
    {
        // populate the name map if required
        $query = "//config[@name='facet_fields']/facet[@internal='$internal_group']/@name_filter";
        $do_filter = (bool)$this->xml->xpath($query);
        if ($do_filter)
        {
            $function = 'get' . ucfirst($internal_group) . 'Map';
            $params[0] = $group;
            $params[1] = array_keys($keys);
            $map = call_user_func_array(array($this, $function), $params );
        }

        foreach ( $keys as $key => $value ) 
        { 
            $facet = new Search\Facet(); 
            if ($do_filter)
            {
                $facet->key = $key; 
                $facet->name = $map[$key]; 
            }
            else
            {
                $facet->name = $key; 
            }
            $facet->count = $value;
            $group->addFacet($facet); 
        }
        return $group;
    }

    // let Config* classes access xml
    public function getXml()
    {
        return $this->xml;
    }
}
