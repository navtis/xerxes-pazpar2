<?php

namespace Application\Model\Pazpar2;

use 
    Application\Model\Search,
    Zend\Debug,
    Xerxes\Utility\Request;

/**
 * Search Query
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Query extends Search\Query
{
    protected $date; // timestamp of query
    protected $targets; // Targets

    public function __construct(Request $request = null, Config $config = null )
    {
        parent::__construct($request, $config );

        $uo = new UserOptions($request);

        $tids = $uo->getSessionData('targets');
        $type = $uo->getSessionData('source_type');
        $this->targets = new Targets($type, $tids);
    }

        /**
         * Convert the search terms to Pazpar2 query
         * FIXME needs more work
         * @returns Query 
         */
        
        public function toQuery()
        {
                // construct query
                
                $query = "";
                
                $terms = $this->getQueryTerms();

                // normalize terms
                
                $term = $terms[0];
                // ANDing a long list of terms makes this an invalid Z39.50 query
                //$term->toLower()->andAllTerms();
        
                // remove punctuation
                $phrase = preg_replace('/[\W]+/', ' ', $term->phrase);
                // tidy multiple spaces
                $phrase = preg_replace('/[\s]+/', ' ', $term->phrase);

                $phrase = urlencode( trim ( $phrase ) );        
        
                if ($term->field_internal == 'any') 
                {
                    $query = $phrase;
                }
                else
                {
                        $query = $term->field_internal . "=" . $phrase ;
                }
                return $query;
        }
        
        /**
         * Get Targets object
         * 
         * @return Targets object
         */
        
        public function getTargets()
        {
                return $this->targets;
        }

        /**
         * Get target pz2_names
         * 
         * @return array of strings
         */
        
        public function getTargetIDs()
        {
                return $this->targets->getTargetKeys();
        }

        /**
         * Get all target pazpar2 IDs
         * 
         * @return array of Target Ids
         */
        public function getTargetNames()
        {
            return $this->targets->getTargetNames();
        }
        
        public function getTargetLocations()
        {
            return $this->targets->getTargetLocations();
        }
        
        public function getLanguage()
        {
                return $this->request->getParam('lang');
        }

        public function getSession()
        {
                return $this->request->getParam('session');
        }
}
