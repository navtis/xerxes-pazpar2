<?php

namespace Application\Model\Pazpar2;

/**
 * Subject access mapper for pazpar2
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

class Subjects
{
    protected $subjectnames = null; // subjects listed in current query
    private $manager; // subclass to delegate calls to

    /**
     * Constructor
     * 
     */ 
    public function __construct($subjectnames = null)
    {
        // the 'real' class is given by the configuration file
        $config = Config::getInstance();
        $subjectClass = $config->getConfig("datasource") . 'Subjects';
        $subjectClass = 'Application\Model\Pazpar2\\' .  $subjectClass;
        $this->manager = new $subjectClass();
        if ( $subjectnames != null )
        {
            $this->subjectnames = $subjectnames;
        }
    }

    public function getSubject($id)
    {
        $arrResults = $this->getSubjects( $id );
                
        if ( count( $arrResults ) > 0 )
        {
            return array_pop( $arrResults );
        } 
        else
        {
            return null;
        }
    }
        
    /**
     * Get one or a set of subjects: must be implemented by subclasses 
     *
     * @param mixed $key                [optional] null returns all subjects, array returns a list of subjects by id, string id returns single id
     * @return array                    array of Subject objects
     */
        
    public function getSubjects($key = null)
    {
        return $this->manager->getSubjects($key);
    }

    /**
     * Get all subjects for a library: must be implemented by subclasses 
     *
     * @param string pz2_key            Identifier for institution to look in
     * @param string $key               Identifier for library within institution
     * @return array                    array of Subject objects
     */
    public function getSubjectsByLibrary($pz2_key, $key)
    {
        return $this->manager->getSubjectsByLibrary($pz2_key, $key);
    }

    public function getSubjectKeys()
    {
        $keys = array();
        $subjects = $this->getSubjects();
        foreach($subjects as $s)
        {
            $keys[] = $s->id;
        }
        return $keys;
    }

    /**
     * Get all the pazpar2 targets which cover a particular subject
     * @param string/array $subject_ids     Single subject_id or array of subject_ids
     * @returns Targets object  
     */
    public function getTargetsBySubject($subject_ids)
    {
        return $this->manager->getTargetsBySubject($subject_ids);
    }

    /**
     * Fetch all subjects relative to a target specified
     * by its pazpar2 key (from the pazpar2 configuration files 
     *
     * @param string $pz2_key   Identifier for a pazpar2 z39.50 target
     * @returns array of Subjects
     */
    public function getSubjectsByTarget($target_ids)
    {
        return $this->manager->getTargetsBySubject($target_ids);
    }

}
