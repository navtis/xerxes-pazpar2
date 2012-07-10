<?php

namespace Application\Model\Pazpar2;

use Xerxes\Utility\Parser,
    Application\Model\Search;


/**
 * Search Item
 * Extends Search\Item to restore accessors
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

class Item extends Search\Item
{
	/**
	 * Set a property for this item
	 * 
	 * @param string $name		property name
	 * @param mixed $value		the value
	 */
    
	public function setProperty($name, $value)
	{
		if ( property_exists($this, $name) )
		{
			$this->$name = $value;
		}
	}

	/**
	 * Get a property from this item
	 * 
	 * @param string $name		property name
	 * @return mixed the value
	 */
	
	public function getProperty($name)
	{
		if ( property_exists($this, $name) )
		{
			return $this->$name;
		}
		else
		{
			throw new \Exception("trying to access propety '$name', which does not exist");
		}
	}
	
}
