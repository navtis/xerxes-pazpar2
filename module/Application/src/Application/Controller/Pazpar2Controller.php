<?php

namespace Application\Controller;

use Application\Model\Pazpar2\Engine,
    Application\Model\Pazpar2\Targets,
    Application\Model\Pazpar2\Affiliations,
    Application\Model\Pazpar2\Subjects,
    Application\Model\Pazpar2\Libraries,
    Application\Model\Pazpar2\UserOptions,
    Application\Model\DataMap\SavedRecords,
    Application\View\Helper\Pazpar2 as SearchHelper,
    Zend\Mvc\MvcEvent,
    Zend\View\Model\ViewModel,
    Zend\Debug,
    Zend\Mvc\Controller\Plugin\FlashMessenger;

class Pazpar2Controller extends SearchController
{
    protected $id = "pazpar2";

    protected function init(MvcEvent $e)
    {
        parent::init($e);
        $this->helper = new SearchHelper($e, $this->id, $this->engine);
        $this->flashMessenger = new FlashMessenger();
    }

    protected function getEngine()
    {
        return new Engine();
    }

    /* index is currently just used to hold the search box and as a place
     * to redirect on errors */
    public function indexAction()
    {
        // if sent back here by exception, display any message
        $flashMessenger = $this->flashMessenger();
        if ($flashMessenger->hasMessages()) {
            foreach( $flashMessenger->getMessages() as $msg )
            {
                // message category separated from message by pipe symbol
                // categories: Error, Warning, Info, Success
                $comm = explode('|', $msg);
                $this->data[$comm[0]] = $comm[1];
            }
            $this->flashMessenger->clearMessages();
        }
        return( $this->data );
    }

    /* Informational page about a single institution and its constituent libraries.
     */
    public function libraryAction()
    {

        if ( ($target = $this->request->getParam('target') ) != null )
        {
            $uo = new UserOptions($this->request);
            $type = $uo->getSessionData('source_type');
            $t = new Targets($type);
            $institution = $t->getIndividualTargets($target);
            $this->data['institution'] = $institution;
            $libs = new Libraries( $target );
            $this->data['libraries'] = $libs;
            $this->data['useroptions'] = $uo;
            if ( $uo->existsInSessionData( 'affiliation' ) )
            {
                $affil = $uo->getSessionData('affiliation');
                $a = new Affiliations();
                $this->data['entitlements'] = $a->getEntitlementsAtInstitution($target, $affil);
            }        
        }
        return($this->data);
    }

    /* default options */
    public function optionsAction()
    {
        if ( $this->request->getParam('records') == 'Submit' )
        {
            // resetting max-records user option
            $max_records = $this->request->getParam('max_records');
            $uo = new UserOptions($this->request);
            $uo->setSessionData('max_records', $max_records);
        }
        else if ( $this->request->getParam('submit-affiliation') == 'Submit' )
        {
            // resetting user academic affiliation
            $affiliation = $this->request->getParam('affiliation');
            $role = $this->request->getParam('role');
            $affiliation = $role . '@' . $affiliation;
            $uo = new UserOptions($this->request);
            $uo->setSessionData('affiliation', $affiliation);
            $uo->setSessionData('readable_affiliation', $this->request->getParam('readable_affiliation'));
            $uo->setSessionData('readable_role', $this->request->getParam('readable_role'));
        }
        return($this->nameoptionsAction());
    }

    /* Select targets by name */
    public function nameoptionsAction()
    {
        // populate the target data for xsl lookups
        $uo = new UserOptions($this->request);
        $type = $uo->getSessionData('source_type');
        $pzt = new Targets($type);
        $this->data['all-targets'] = $pzt->toArray();
        $pzt = new Affiliations();
        $this->data['all-institutions'] = $pzt->getAllInstitutions();
        // fetch the selected data
        $this->data['useroptions'] = $uo;
        return($this->data);
    }

    /* Select targets by subject covered */
    public function subjectoptionsAction()
    {
        // fetch all the target data for xsl lookups
        $uo = new UserOptions($this->request);
        $type = $uo->getSessionData('source_type');
        $pzt = new Targets($type);
        $this->data['all-targets'] = $pzt->toArray();
        $pzt = new Affiliations();
        $this->data['all-institutions'] = $pzt->getAllInstitutions();
        // we need all subject data for lookups
        $s = new Subjects();
        $this->data['all-subjects'] = $s->getSubjects();
        // fetch the selected data
        $this->data['useroptions'] = $uo;
        return($this->data);
    }

    /* Select targets by user entitlements */
    public function accessoptionsAction()
    {
        // fetch all the target data for xsl lookups
        $uo = new UserOptions($this->request);
        $type = $uo->getSessionData('source_type');
        $pzt = new Targets($type);
        $this->data['all-targets'] = $pzt->toArray();
        $pzt = new Affiliations();
        $this->data['all-institutions'] = $pzt->getAllInstitutions();

        $this->data['useroptions'] = $uo;
        return($this->data);
    }

    /* SearchAction kicks off a new search with a new 
     * sessionid (if Submit is set to GO) 
     * or reprocesses an existing search (eg to sort or limit by facet) 
     * with and existing sessionid. After initialisation, redirects to
     * statusAction to wait for the search to complete
     */
    public function searchAction()
    {
        $uo = new UserOptions($this->request);
        if ( $this->request->getParam('Submit') == 'GO' )
        {
            // initialise the session for a new search
            $sid = $this->engine->initializePazpar2Client();
            $uo->setSessionData('pz2session', $sid);
        }
        else
        {
            $sid = $uo->getSessionData('pz2session');
        }
        // kick the search off
        $this->query->sid = $sid;
        $max_records = $uo->getSessionData('max_records');
        try
        {
            $this->engine->search($this->query, $max_records); // non-blocking call
        }
        catch( \Exception $e)
        {
            $this->flashMessenger->addMessage('Error|Session timeout: ' . $e->getMessage());
            // assume the session died - cab't initialise a new one
            // as might be infinite loop
            // Need to generate an error message
            // and routing back to search start page 
            $this->engine->clearPazpar2Client( $sid );
            $params = $this->query->getAllSearchParams();
            $params['lang'] = $this->request->getParam('lang');
            $params['controller'] = $this->request->getParam('controller');
            $params['action'] = 'index';
            $url = $this->request->url_for($params);
            return $this->redirect()->toUrl($url);
        }
        // set the url params for where we are going to redirect,
        // usually to the results action, but can be overriden
        $base = $this->helper->searchRedirectParams();
        $params = $this->query->getAllSearchParams();
        $params = array_merge($base, $params);
        $params['lang'] = $this->request->getParam('lang');
        $params['action'] = 'status';
        $params['Submit'] = '';
        // check spelling
        $this->checkSpelling();
        // construct the actual url and redirect
        $url = $this->request->url_for($params);
        return $this->redirect()->toUrl($url);
    }

    /* Redirected to from searchAction once the search has been 
     * initiated and called again by meta-refresh on the page 
     * until the search completes
     */
    public function statusAction()
    {
        $uo = new UserOptions($this->request);
        $this->data['useroptions'] = $uo;
        $sid = $uo->getSessionData('pz2session');
        $status = $this->engine->getSearchStatus($sid);
        // if status is finished, redirect to results
        if ($status->isFinished() == 1)
        {
            $params = $this->helper->searchRedirectParams();
            $url = $this->request->url_for($params);
            return $this->redirect()->toUrl($url);
        }
        
        $targets = $uo->getSessionData('targets');
        $type = $uo->getSessionData('source_type');
        $targets = new Targets($type, $targets);
        $this->data['pz2results'] = $status->getTargetStatuses($targets);
        // keep the session number in the output HTML for AJAX
        $this->request->setSessionData('pz2session', $sid);

        return $this->data;
    }

    /**
     * ResultsAction is called by statusAction once the search has completed
     * Uses parent (SearchController) resultsAction
     */
        public function resultsAction()
        {
            //var_dump($this->request); exit;
            $uo = new UserOptions($this->request); 
            $sid = $uo->getSessionData('pz2session');
            try
            {
                // force restoration of client from cache
                $status = $this->engine->getSearchStatus($sid);
                // fetch the search results
                $result = parent::resultsAction(); 
            }
            catch( \Exception $e )
            {
                // Exception probably a session timeout; go back to front page
                $this->flashMessenger->addMessage('Error|Session timeout: ' . $e->getMessage());
                $params = $this->query->getAllSearchParams();
                $params['controller'] = 'pazpar2';
                $params['action'] = 'index';
                $params['Submit'] = '';
                $url = $this->request->url_for($params);
                return $this->redirect()->toUrl($url);
            }
            // keep the session number for the AJAX code in the output HTML
            $this->request->setSessionData('pz2session', $sid);
            $targets = $uo->getSessionData('targets');
            $type = $uo->getSessionData('source_type');
            $targets = new Targets($type, $targets);
            $result['useroptions'] = $uo;
            $result['targets'] = $targets->getTargetNames(); // needed for the facets
            //$result['status'] = $status->getTargetStatuses($this->query->getTargets());
            //$result['externalLinks'] = $this->helper->addExternalLinks($this->config);
            return $result;
        }

    /*
     * recordAction displays a single record returned by a search.
     * The sessionId used in the search must still be live
     */
    public function recordAction()
    {
        $uo = new UserOptions($this->request);
        $sid = $uo->getSessionData('pz2session');
        $targets = new Targets($uo->getSessionData('source_type'), $uo->getSessionData('targets'));
        $id = $this->request->getParam('id'); 
        $offset = $this->request->getParam('offset', null, true); 
        try
        {
            // get the record 
            $results = $this->engine->getRawRecord($sid, $id, $offset, $targets); 
        }
        catch( \Exception $e )
        {
            // Exception probably a session timeout; go back to front page
            $fm = new FlashMessenger();
            $fm->addMessage('Session timeout: ' . $e->getMessage());
            $params = $this->query->getAllSearchParams();
            $params['lang'] = $this->request->getParam('lang');
            $params['controller'] = $this->request->getParam('controller');
            $params['action'] = 'index';
            $url = $this->request->url_for($params);
            return $this->redirect()->toUrl($url);
        }
        // set links 
        $this->helper->addRecordLinks($results); 
        $this->helper->addExternalRecordLinks($results, $this->config); 
        // add to response 
        $this->data["results"] = $results;
        return $this->data; 
    }

    /**
     *  Called by AJAX from results page to keep session alive 
     */
        public function ajaxpingAction()
        {
            $uo = new UserOptions($this->request);
            $sid = $uo->getSessionData('pz2session');
            $arr['live'] = $this->engine->ping($sid);
            $response = $this->getResponse(); 
            $response->headers()->addHeaderLine("Content-type", "application/json");
            $response->setContent(json_encode($arr)); 
            return $response;
        }

        /**
        * Terminate a search early when the user has lost patience
        */
        public function ajaxterminateAction()
        {
            $sid = $this->request->getParam("session");
            $this->engine->setFinished($sid);
            $response = $this->getResponse();
            $response->headers()->addHeaderLine("Content-type", "application/json");
            $arr['sid'] = $sid;
            $response->setContent(json_encode($arr));
            return $response;
        }

        public function ajaxgetrolesAction()
        {
            $affil = $this->request->getParam("affiliation");
            $arr['affiliation'] = $affil;
            $pzt = new Affiliations();
            $arr['roles'] = $pzt->getRolesByAffiliation($affil);
            //$arr['roles'] = array('std' => 'student', 'lec' => 'lecturer');
            $response = $this->getResponse(); 
            $response->headers()->addHeaderLine("Content-type", "application/json");
            $response->setContent(json_encode($arr)); 
            return $response;
        }
}
