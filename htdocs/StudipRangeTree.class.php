<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTree.class.php
// Class to handle structure of the "range tree"
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
require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES . "/DbSnapshot.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/range_tree.view.php");
require_once($ABSOLUTE_PATH_STUDIP . "/TreeAbstract.class.php");

/**
* class to handle the "range tree"
*
* This class provides an interface to the structure of the "range tree"
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipRangeTree extends TreeAbstract {
	
	
	/**
	* constructor
	*
	* do not use directly, call &TreeAbstract::GetInstance("StudipRangeTree")
	* @access private
	*/ 
	function StudipRangeTree() {
		$this->root_name = $GLOBALS['UNI_NAME_CLEAN'];
		$this->studip_objects['inst'] = array('pk' => 'Institut_id', 'table' => 'Institute');
		$this->studip_objects['fak'] = array('pk' => 'Institut_id', 'table' => 'Institute');
		parent::TreeAbstract(); //calling the baseclass constructor 
	}

	/**
	* initializes the tree
	*
	* stores all rows from table range_tree in array $tree_data
	* @access public
	*/
	function init(){
		parent::init();
		$this->tree_data['root']['studip_object_id'] = 'root';
		$db = $this->view->get_query("view:TREE_GET_DATA");
		while ($db->next_record()){
			$item_name = $db->f("name");
			if ($db->f("studip_object")){
				$item_name = $db->f("studip_object_name");
			}
			$this->tree_data[$db->f("item_id")] = array("studip_object" => $db->f("studip_object"),
													"studip_object_id" => $db->f("studip_object_id"),
													"fakultaets_id" => $db->f("fakultaets_id"));
			$this->storeItem($db->f("item_id"), $db->f("parent_id"), $item_name, $db->f("priority"));
		}
	}
	/**
	* Returns Stud.IP range_id of the next "real" object
	*
	* This function finds the next item wich is a real Stud.IP Object, either an "Einrichtung" or a "Fakultaet"<br>
	* useful for the user rights management
	* @access	public
	* @param	string	$item_id
	* @return	array	of primary keys from table "institute" 
	*/
	function getAdminRange($item_id){
		if (!$this->tree_data[$item_id])
			return false;
		$found = false;
		$ret = false;
		$next_link = $item_id;
		while(($next_link = $this->getNextLink($next_link)) != 'root'){
			if ($this->tree_data[$next_link]['studip_object'] == 'inst'){
				$found[] = $next_link;
			}
			if ($this->tree_data[$next_link]['studip_object'] == 'fak'){
				if (count($found)){
					for($i = 0; $i < count($found); ++$i){
						if ($this->tree_data[$found[$i]]['fakultaets_id'] == $this->tree_data[$next_link]['studip_object_id']){
							$ret[] = $this->tree_data[$found[$i]]['studip_object_id'];
						}
					}
				$ret[] = $this->tree_data[$next_link]['studip_object_id'];
				} else {
					$ret[] = $this->tree_data[$next_link]['studip_object_id'];
				}
				break;
			}
			$next_link = $this->tree_data[$next_link]['parent_id'];
		}
		if (!$ret){
			$ret[] = $next_link;
		}
		return $ret;
	}
	/**
	* returns the next item_id upwards the tree which is a Stud.IP object
	*
	* help function for getAdminRange()
	*
	* @access	private
	* @param	string	$item_id
	* @return	string
	*/
	
	function getNextLink($item_id){
	if (!$this->tree_data[$item_id])
			return false;
		$ret_id = $item_id;
		while (!$this->tree_data[$ret_id]['studip_object_id']){
			$ret_id = $this->tree_data[$ret_id]['parent_id'];
		}
		return $ret_id;
	}
	
}
//$test =& TreeAbstract::GetInstance("StudipRangeTree");
//echo "<pre>";
//echo get_class($test) . "\n";
//print_r($test->tree_data);
//print_r($test->getKidsKids("root"));
?>
