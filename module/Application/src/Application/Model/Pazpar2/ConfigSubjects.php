<?php

namespace Application\Model\Pazpar2;

use Zend\Debug;
/**
 * An implementation of the abstract Subjects class,
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

class ConfigSubjects extends Subjects
{
    // hashes of arrays
    protected $subjects_to_targets = array();
    protected $targets_to_subjects = array();
    // array of objects
    protected $subjects = array();
    // array of names
    protected $subject_names = array();
    protected $config;

    /**
     * Constructor
     * 
     */
        
    public function __construct()
    {
        // full set of Subjects from configuration file 
        $this->config = Config::getInstance();
        $config = $this->config->getConfig("targets");

        if ( $config != null )
        { 
            $subj_assoc = array();
            $key = 'pz2_key';
            foreach ( $config->target as $target_data )
            { 
                $t = (string)$target_data->attributes()->$key;
                if (! array_key_exists( $t, $this->targets_to_subjects ) )
                {
                    $this->targets_to_subjects[$t] = array();
                }
                // now pick up all the subjects for this target
                $subjects = $target_data->children();
                $id = 'id';
                foreach($subjects as $s)
                {
                    $subj = (string)$s;
                    $subj_id = (string)$s->attributes()->$id;
                    $subj_assoc[$subj] = $subj_id;
                    if (! array_key_exists( $subj_id, $this->subjects_to_targets ) )
                    {
                        $this->subjects_to_targets[$subj_id] = array();
                    }
                    $this->targets_to_subjects[$t][] = $subj_id;
                    $this->subjects_to_targets[$subj_id][] = $t;
                }
            }
            $this->subjectnames = array_keys( $subj_assoc );
            sort( $this->subjectnames );
            foreach( $this->subjectnames as $sn)
            {
                $subject = new Subject();
                $subject->load(array('name' => $sn, 'id' => $subj_assoc[$sn])); // useful when has more attributes!
                $this->subjects[] = $subject;
            }
        }
    }

    // Helper function for getSubjects()
    function alphasort( $a, $b )
    {
        return strcmp( $a->name, $b->name );
    }

    /**
     * Get one or a set of subjects 
     * 
     * @param mixed $keys               [optional] null returns all subjects, array returns a list of subjects by id, string id returns single id
     * @param boolean $alpha            [optional] specifies alphabetic order
     * @return array                    array of Subject objects
     */
        
    public function getSubjects($keys = null, $alpha = null)
    {
        $arrSubjects = array ( );
        if ( $keys == null )
        {
            // all subjects
            $arrSubjects = $this->subjects;
        }
        else
        {
            if (! is_array( $keys ) )
            {
                // convert single id to array
                $keys = array( $keys );
            }
            // list of subjects or single subject
            foreach( $this->subjects as $subject )
            {
                if ( in_array( $subject->id, $keys ) )
                {
                    $arrSubjects[] = $subject;
                }
            }
        }               
        
        if ( $alpha != null )
        {
            usort( $arrSubjects, 'alphasort' );
        }
        return $arrSubjects;
    }

    /**
     * Fetch all subjects relative to a target specified
     * by its pazpar2 key (from the pazpar2 configuration files 
     *
     * @param string $pz2_key   Identifier for a pazpar2 z39.50 target
     * @param boolean $alpha    Optionally sort results alphabetically
     * @returns array of Subjects
     */
    public function getSubjectsByTarget($pz2_key, $alpha = null)
    {
        $arrSubjects = array ( );
        $ss = $this->targets_to_subjects[$pz2_key];
        foreach( $this->subjects as $subject )
        {
            if ( in_array( $subject->name, $ss ) )
            {
                $arrSubjects[] = $subject;
            }
        }
        if ( $alpha != null )
        {
            usort( $arrSubjects, 'alphasort' );
        }
        return $arrSubjects;
    }

    /**
     * Get all the pazpar2 targets which cover a particular subject
     * @param string/array $subject_ids     Single subject_id or array of subject_ids
     * @param boolean $alpha    Optionally sort results alphabetically
     * @returns Targets object  
     */
    public function getTargetsBySubject($subject_ids, $alpha = null)
    {
        $ss = array();
        if (! is_array($subject_ids) )
        {
            $sn = array();
            $sn[] = $subject_ids;
            $subject_ids = $sn;
        }
        foreach( $subject_ids as $sn )
        {
            $ss = array_merge($ss, $this->subjects_to_targets[$sn]);
        }
        return new Targets($ss);
    }
}
