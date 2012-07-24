<?php

namespace Application\Model\Pazpar2;

use Xerxes\Utility\Request,
    Xerxes\Utility\Parser;
use Zend\Debug;

/**
 * Manage pazpar2 user options using ZF2 Session Containers
 * held in Xerxes Request
 * 
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 */

class UserOptions
{
    private $container;
    // target selection options
    public $valid_keys = array(
        'selectedby' => 'string',
        'names' => 'array',
        'subjects' => 'array',
        'types' => 'array',
        'distances' => 'array',
        'entitlements' => 'array',
        // calculated targets to use
        'targets' => 'array',
        // user affiliation
        'affiliation' => 'string', // in domain form
        'readable_affiliation' => 'string', // in name form
        // user role
        'role' => 'string', // from set list
        'readable_role' => 'string', // in name form
        // maximum records to fetch from search
        'max_records' => 'string', 
        // pazpar2 session id
        'pz2session' => 'string'
    );
    public $target_fields = array('names', 'subjects', 'types', 'distances', 'entitlements');
    public $role_fields = array('affiliation', 'readable_affiliation', 'role', 'readable_role');
    private $targets; // Targets object containing currently selected targets

    /**
     * Create UserOptions object saving Request with session data
     * @param Request $request
     */
        
    public function __construct(Request $request)
    {
        $this->container = $request->getContainer('pazpar2options');

        // if this is a submit, change the session values as required
        if( $request->getParam('changetargets') != '' )
        {
            $selectedby = $request->getParam('selectedby');
            if ($selectedby == 'names')
            {
                $targets = $request->getParam('target', null, true);
                $this->setSessionData('names', $targets);
                $this->setSessionData('selectedby', $selectedby);
            }
            else if ($selectedby == 'subjects')
            {
                $subjects = $request->getParam('subject', null, true);
                $this->setSessionData('subjects', $subjects);
                $this->setSessionData('selectedby', $selectedby);
            }
            else if ($selectedby == 'entitlements')
            {
                $entitlements = $request->getParam('entitlement', null, true);
                $this->setSessionData('entitlements', $entitlements);
                $this->setSessionData('selectedby', $selectedby);
            }
        }
        
        // retrieve the session values

        // set the actual targets to use
        if ( $this->existsInSessionData( 'selectedby' ) )
        {
            $key = $this->getSessionData( 'selectedby' );
        }
        else // set default: all targets
        {
            $this->setSessionData( 'selectedby', 'names' );
            $this->setSessionData( 'names', array( 'all' ) );
            $key = 'names';
        }
        $this->calculateTargets($key, $this->getSessionData( $key ) );
    }

    /**
     * Generate the actual targets needed depending on the 
     * selection method chosen
     */
    protected function calculateTargets($key, $val)
    {
        switch( $key )
        {
            case 'names':
                if ($val[0] == 'all')
                {
                    $targets = new Targets();
                    $targets = $targets->getTargetKeys();
                }
                else
                    $targets = $val; 
                break;
            case 'types':
                break;
            case 'subjects':
                $subj_ids = $this->getSessionData( 'subjects' );
                $ts = new Subjects();
                $ts = $ts->getTargetsBySubject( $subj_ids );
                $targets = $ts->getTargetKeys();
                break;
            case 'distances':
                break;
            case 'entitlements':
                $entitlement_ids = $this->getSessionData( 'entitlements' );
                $ts = new Affiliations();
                $affiliation = $this->getSessionData( 'affiliation' );
                $ts = $ts->getTargetsByEntitlement( $entitlement_ids, $affiliation );
                $targets = array_keys($ts);
                //var_dump($targets); echo('<br />');
                break;
        }
        $this->setSessionData('targets', $targets);
        return $targets;
    }

    /**
     * Add value to Session ($value may be an array)
     * 
     * @param string $key
     * @param mixed $value
     */
        
    public function setSessionData($key, $value)
    {
        if ( ! ( gettype( $value ) == $this->valid_keys[$key] ) )
        {
            throw new \Exception("Setting invalid pazpar2 session data $key: $value");
        }
        // if we set one of the target fields the others need to be empty
        if (in_array( $key, $this->target_fields ) )
        {
            foreach( $this->target_fields as $field )
            {
                $this->unsetSessionData($field);
            }
        }
        $this->container->offsetSet($key, $value);
    }
        
    /**
     * Unset a value in Session
     *
     * @param string $key
     */
        
    public function unsetSessionData($key)
    {
        if ( ! ( array_key_exists( $key, $this->valid_keys ) ) )
        {
            throw new Exception("Unsetting invalid pazpar2 session data $key");
        }
        $this->container->offsetUnset($key);
    }   
        
    /**
     * Check if a key is set in Session
     *
     * @param string $key
     */
    
    public function existsInSessionData($key)
    {
        if ( ! ( array_key_exists( $key, $this->valid_keys ) ) )
        {
            throw new \Exception("Checking invalid pazpar2 session data $key");
        }
        return $this->container->offsetExists($key);
    }       
     
    /**
     * Get session value
     * 
     * @param string $key
     * @return mixed                value, if key exists, otherwise null
     */
        
    public function getSessionData($key)
    {       
        if ( ! ( array_key_exists( $key, $this->valid_keys ) ) )
        {
            throw new Exception("Getting invalid pazpar2 session data $key");
        }
        return $this->container->offsetGet($key);
    }
        
    /**
     * Get all session values
     * 
     * @return array
     */
       
    public function getAllSessionData()
    {
        return $this->container->getIterator()->getArrayCopy();
    }
        
    /**
     * Serialize to xml
     * 
     * @return DOMDocument
     */
     
    public function toXML()
    {
        $xml = new \DOMDocument( );
        $xml->loadXML( "<pazpar2options />" );
               
        Parser::addToXml( $xml, 'user-options', $this->getAllSessionData() );
             
        return $xml;
    }
      
}
