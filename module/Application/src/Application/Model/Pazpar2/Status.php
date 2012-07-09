<?php

namespace Application\Model\Pazpar2;

use Zend\Debug;
/**
 * Pazpar2 Search Status
 *
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2012 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Status
{
    protected $stats = array(); // individual target statuses
    protected $timestamp = 0; // timestamp of status check
    protected $finished = false; // whether search is complete
    protected $progress = 0; // integer 0 - 10
    protected $result_set;
    protected $xml;

        public function __construct()
        {
            $this->timestamp = time();
        }

        /**
         * Add class to allow CSS colouring of target name
         * depending on status
         * @param string $stat
         */
        public function addTargetStatus( $stat )
        {
            // generate CSS class
            $stat['class'] = $this->classmap($stat['state']);
            $this->stats[] = $stat;
        }

        /**
         * Return status of target given pz2_key
         * @param string $tid   pazpar2 target key
         */
        public function getTargetStatus($tid)
        {
            return $this->stats[$tid];
        }
        
    /**
     * Merge target display info with status results
     * and progress info
     * @param Targets $targets
     * @return mixed $news
     */
    public function getTargetStatuses($targets_obj=null)
    {
        $news = $this->stats;
        // drop xml from pazpar2, and let xerxes create new xml 
        unset($news['xml']);
        // add in title and class
        foreach($news as $t => $arr)
        {
            $arr['class'] = $this->classmap($arr['state']);
            $target = $targets_obj->getIndividualTargets($arr['name']);
            $arr['title_short'] = $target->title_short;
            $news[$t] = $arr;
        }
        $news['progress'] = $this->progress;
        // ensure finished always has a value in the xml
        $news['finished'] = $this->finished?1:0;
        return $news;
    }

    protected function classmap($cs)
    {
        $map = array(
                'Client_Idle' => 'succeeded',
                'Client_Working' => 'working',
                'Client_Connected' => 'working',
                'Client_Connecting' => 'working',
                'Client_Searching' => 'working',
                'Client_Presenting' => 'working',
                'Client_Disconnected' => 'failed',
                'Client_Stopped' => 'failed',
                'Client_Error' => 'failed',
                'Client_Failed' => 'failed'
              );
         return $map[$cs];
    }

        public function SetResultSet( $rs )
        {
            $this->result_set = $rs;
        }
        
        public function getResultSet()
        {
            return $this->result_set;
        }
        
        
        public function setFinished($finished)
        {
            $this->finished = (bool) $finished;
        }
        
        public function isFinished()
        {
            return $this->finished;
        }

        /**
         * Convert progress from fraction to percent for progress bar
         * then save it
         * @param float $p
         */
        public function setProgress($p)
        {
        // 0.0 <= $p <= 1.0
            $this->progress = intval( $p * 100 );
        }

        public function getProgress()
        {
            return $this->progress;
        }

        /**
        * @param DomDocument $xml
        */
        public function setXml($xml)
        {
            $this->stats['xml'] = $xml;
        }

        public function toXml()
        {
            return $this->stats['xml'];
        }    
}   
