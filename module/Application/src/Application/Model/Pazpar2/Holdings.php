<?php

namespace Application\Model\Pazpar2;

use Application\Model\Search\Holdings as SearchHoldings,
    Application\Model\Search\Item,
    Application\Model\Search\Holding;

/**
 * Result Holdings
 *
 * Based on Search\Holdings with addition of target name/title and links array
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Holdings extends SearchHoldings
{
    public $target_name;
    public $target_title;
    public $links = array();

        public function setTargetName($name)
        {
                $this->target_name = $name;
        }

        public function setTargetTitle($title)
        {
                $this->target_title = $title;
        }

    public function hasMembers()
    {
        return count($this->holdings) + count($this->items) + count($this->links) > 0?true:false;
    }

    public function hasLinks()
    {
        return count($this->links) > 0?true:false;
    }

    public function hasCirculationData()
    {
        return count($this->items) > 0?true:false;
    }

    public function addLink(\Xerxes\Record\Link $link)
    {
        $this->links[] = $link;
    }
}
