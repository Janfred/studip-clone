<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RangeTreeObjectFak.class.php
// Class to handle items in the "range tree"
// 
// Copyright (c) 2002 André Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+
require_once($ABSOLUTE_PATH_STUDIP . "/RangeTreeObject.class.php");

/**
* class for items in the "range tree"
*
* This class is used for items in the tree which are "Fakultäten"
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class RangeTreeObjectFak extends RangeTreeObject {
	
	/**
	* Constructor
	*
	* Do not use directly, call factory method in base class instead
	* @access private
	* @param	string	$item_id
	*/
	function RangeTreeObjectFak($item_id) {
		$base_class = get_parent_class($this);
		//parent::$base_class($item_id); //calling the baseclass constructor 
		$this->$base_class($item_id); //calling the baseclass constructor PHP < 4.1.0
		$this->initItemDetail();
	}
}

?>
