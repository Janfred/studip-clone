<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// SemTree.class.php
// Class to handle structure of the "seminar tree"
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de> 
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
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES . "/DbSnapshot.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/sem_tree.view.php");
require_once($ABSOLUTE_PATH_STUDIP . "/TreeAbstract.class.php");

/**
* class to handle the seminar tree
*
* This class provides an interface to the structure of the seminar tree
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipSemTree extends TreeAbstract {
	
	
	/**
	* constructor
	*
	* do not use directly, call &TreeAbstract::GetInstance("StudipRangeTree")
	* @access private
	*/ 
	function StudipSemTree($args) {
		$base_class = get_parent_class($this);
		$this->root_name = $GLOBALS['UNI_NAME_CLEAN'];
		parent::$base_class(); //calling the baseclass constructor 
	}

	/**
	* initializes the tree
	*
	* stores all rows from table sem_tree in array $tree_data
	* @access public
	*/
	function init(){
		parent::init();
		$db = $this->view->get_query("view:SEM_TREE_GET_DATA");
		while ($db->next_record()){
			$this->tree_data[$db->f("sem_tree_id")] = array("info" => $db->f("info"),"studip_object_id" => $db->f("studip_object_id"));
			$name = ($db->f("studip_object_id")) ? $db->f("studip_object_name") : $db->f("name") ;
			$this->storeItem($db->f("sem_tree_id"), $db->f("parent_id"), $name, $db->f("priority"));
		}
	}
	
	function getSemIds($item_id,$ids_from_kids = false){
		if (!$this->tree_data[$item_id])
			return false;
		if ($ids_from_kids){
			$this->view->params = $this->getKidsKids($item_id);
		}
		$this->view->params[] = $item_id;
		$ret = false;
		$rs = $this->view->get_query("view:SEM_TREE_GET_SEMIDS");
		while($rs->next_record()){
			$ret[] = $db->f(0);
		}
		return $ret;
	}
	
	function getSemData($item_id,$sem_data_from_kids = false){
		if (!$this->tree_data[$item_id])
			return false;
		if ($sem_data_from_kids){
			$this->view->params = $this->getKidsKids($item_id);
		}
		$this->view->params[] = $item_id;
		return new DbSnapshot($this->view->get_query("view:SEM_TREE_GET_SEMDATA"));
	}
	
	function getNumSem($item_id, $num_sem_from_kids = false){
		if (!$this->tree_data[$item_id])
			return false;
		if ($num_sem_from_kids){
			$this->view->params = $this->getKidsKids($item_id);
		}
		$this->view->params[] = $item_id;
		$ret = 0;
		$rs = $this->view->get_query("view:SEM_TREE_GET_NUM_SEM");
		if ($rs->next_record()){
			$ret = $db->f(0);
		}
		return $ret;
	}
}
//$test =& TreeAbstract::GetInstance("StudipSemTree");
//echo "<pre>";
//echo get_class($test) . "\n";
//print_r($test->tree_data);
?>
