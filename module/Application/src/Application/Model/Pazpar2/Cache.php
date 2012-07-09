<?php

namespace Application\Model\Pazpar2;


/**
 * Pazpar2 Cache
 * Extends Xerxes\Utility\Cache with unset function
 * @author David Walker
 * @author Graham Seaman
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Cache extends \Xerxes\Utility\Cache
{
    public function clear( $id )
    {
        // clear within scope of this request
        unset ( $this->_data[$id] );
        $arrParams = array();
       
        // remove from db
        $arrParams[":id"] = $id;

        $strSQL = "DELETE FROM xerxes_cache WHERE id = :id";
        $this->delete( $strSQL, $arrParams );
    }

}
