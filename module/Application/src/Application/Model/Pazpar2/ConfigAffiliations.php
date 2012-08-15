<?php

namespace Application\Model\Pazpar2;

use Zend\Debug,
    Xerxes\Utility\Factory;

/**
 * An implementation of the abstract Affiliations class,
 * selected in configuration
 * FIXME just a stub
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class ConfigAffiliations extends Affiliations
{
    public function __construct() 
    { 
        $config = Config::getInstance();
    }

    public function getAllInstitutions()
    {
        return array();
    }

    public function getRolesByAffiliation($domain)
    {
        return array();
    }

    /**
     * Lists institutions accessible given an entitlement
     * @param string $type          Kind of library to filter on
     * @param array $affiliation    EduPerson style role+domain
     * @param string $entitlement   array of entitlement ids
     * @return array                pz2_keys => institution names
     */
    public function getTargetsByEntitlement($type, $entitlements, $affiliation)
    {
        return array();
    }


    public function getEntitlementsAtInstitution($institution, $affiliation)
    {
        return array();
    }
}       
