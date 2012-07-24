<?php

namespace Application\Model\Pazpar2;

/**
 * Institution and role access mapper for pazpar2
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

class Affiliations
{
    protected $affiliations = array(); // array of institutions indexed by domain
    private $manager; // subclass to delegate calls to

    /**
     * Constructor
     * Instantiates specified manager object
     */	
    public function __construct()
    {
	// the 'real' class is given by the configuration file
	$config = Config::getInstance();
        $targetClass = $config->getConfig("datasource") . 'Affiliations';
        $targetClass = 'Application\Model\Pazpar2\\' .  $targetClass;
        $this->manager = new $targetClass();
    }

    /**
     * Lazyload full set of institutions
     *
     * @return array of institution_domains => names
     */
    public function getAllInstitutions()
    {
        if (  empty($this->affiliations) )
        {
            $this->affiliations = $this->manager->getAllInstitutions();
        }
        return $this->affiliations;
    }

    /**
    /* Specifies roles available at a single institution
     * @param string $domain    eduPerson style domain for institution
    /* @return array            role_ids => role_names
     */
    public function getRolesByAffiliation($domain)
    {
        return $this->manager->getRolesByAffiliation($domain);
    }

    /**
     * Lists institutions accessible given an entitlement
     * @param mixed $entitlement    (array of) entitlement id(s)
     * @param string affiliation    EduPerson style role+domain
     * @return array                pz2_keys => institution names
     */
    public function getTargetsByEntitlement($entitlement, $affiliation)
    {
        // convert scalar to array
        if (! is_array($entitlement) )
            $entitlement = array($entitlement);
        return $this->manager->getTargetsByEntitlement($entitlement, $affiliation);
    }

    /**
     * Lists entitlements available to a particular user at an institution
     * @param string                m25_code for institution 
     * @param string affiliation    EduPerson style role+domain
     * @return object               Entitlements object
     */
    public function getEntitlementsAtInstitution($institution, $affiliation)
    {
        return $this->manager->getEntitlementsAtInstitution($institution, $affiliation);
    }

    /* used by Parser::addToXml() to convert this object's contents to XML
    */
    public function toArray()
    {
        return $this->affiliations;
    }
}
