<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTreeViewAdmin.class.php
// Class to print out the "range tree"
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
require_once($ABSOLUTE_PATH_STUDIP . "StudipRangeTreeView.class.php");
/**
* class to print out the admin view of the "range tree"
*
* This class prints out a html representation of the whole or part of the tree, <br>
* it also contains all functions for administrative tasks on the tree
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipRangeTreeViewAdmin extends StudipRangeTreeView{

	
	var $tree_status;
	
	var $mode;
	
	/**
	* constructor
	*
	* only calls the base class constructor
	* @access public
	*/
	function StudipRangeTreeViewAdmin(){
		$base_class = get_parent_class($this);
		//parent::$base_class($item_id); //calling the baseclass constructor 
		$this->$base_class(); //calling the baseclass constructor PHP < 4.1.0
		$this->initTreeStatus();
		$this->parseCommand();
	}

	function initTreeStatus(){
		$view = new DbView();
		$user_id = $GLOBALS['auth']->auth['uid'];
		$user_perm = $GLOBALS['auth']->auth['perm'];
		$studip_object_status = null;
		if (is_array($this->open_items)){
			foreach ($this->open_items as $key => $value){
				if ($key != 'root'){
					$studip_object_status[$this->tree->getAdminRange($key)] = ($user_perm == "root") ? 1 : -1;
				}
			}
		}
		if (is_array($this->open_ranges)){
			foreach ($this->open_ranges as $key => $value){
				if ($key != 'root'){
					$studip_object_status[$this->tree->getAdminRange($key)] = ($user_perm == "root") ? 1 : -1;
					$studip_object_status[$this->tree->getAdminRange($this->tree->tree_data[$key]['parent_id'])] = ($user_perm == "root") ? 1 : -1;
				}
			}
		}
		if (is_array($studip_object_status) && $user_perm != 'root'){
			$rs = $view->get_query("view:TREE_INST_STATUS:{".join(",",array_keys($studip_object_status))."},$user_id");
			while ($rs->next_record()){
				$studip_object_status[$rs->f("Institut_id")] = 1;
			}
			$rs = $view->get_query("view:TREE_FAK_STATUS:{".join(",",array_keys($studip_object_status))."},$user_id");
			while ($rs->next_record()){
				$studip_object_status[$rs->f("Fakultaets_id")] = 1;
				if ($rs->f("Institut_id") && isset($studip_object_status[$rs->f("Institut_id")])){
					$studip_object_status[$rs->f("Institut_id")] = 1;
				}
			}
		}
		$studip_object_status['root'] = ($user_perm == "root") ? 1 : -1;
	$this->tree_status = $studip_object_status;
	}
	
	function parseCommand(){
		global $_REQUEST;
		if ($_REQUEST['mode'])
			$this->mode = $_REQUEST['mode'];
		if ($_REQUEST['cmd']){
			$exec_func = "execCommand" . $_REQUEST['cmd'];
			if (method_exists($this,$exec_func))
				if ($this->$exec_func())
					$this->tree->init();
		}
	}
	
	function execCommandOrderItem(){
		global $_REQUEST;
		$direction = $_REQUEST['direction'];
		$item_id = $_REQUEST['item_id'];
		$items_to_order = $this->tree->getKids($this->tree->tree_data[$item_id]['parent_id']);
		if (!$this->isParentAdmin($item_id) || !$items_to_order)
			return false;
		for ($i = 0; $i < count($items_to_order); ++$i){
			if ($item_id == $items_to_order[$i])
				break;
		}
		if ($direction == "up" && isset($items_to_order[$i-1])){
			$items_to_order[$i] = $items_to_order[$i-1];
			$items_to_order[$i-1] = $item_id;
		} elseif (isset($items_to_order[$i+1])){
			$items_to_order[$i] = $items_to_order[$i+1];
			$items_to_order[$i+1] = $item_id;
		}
		$view = new DbView();
		for ($i = 0; $i < count($items_to_order); ++$i){
			$rs = $view->get_query("view:TREE_UPD_PRIO:$i,$items_to_order[$i]");
		}
		return true;
	}
		
		
	function isItemAdmin($item_id){
		return ($this->tree_status[$this->tree->getAdminRange($item_id)] == 1) ? true :false;
	}
	
	function isParentAdmin($item_id){
			return ($this->tree_status[$this->tree->getAdminRange($this->tree->tree_data[$item_id]['parent_id'])] == 1) ? true : false;
	}
	
	function getItemHead($item_id){
		$head = "";
		$head .= "<b>" . htmlReady($this->tree->tree_data[$item_id]['name']) . "</b>";
		if ($item_id != $this->start_item_id && $this->isParentAdmin($item_id)){
			$head .= "</td><td align=\"rigth\" valign=\"bottom\" class=\"printhead\">";
			if (!$this->tree->isFirstKid($item_id)){
				$head .= "<a href=\"". $this->getSelf("cmd=OrderItem&direction=up&item_id=$item_id") .
				"\"><img src=\"pictures/move_up.gif\" hspace=\"4\" width=\"13\" height=\"11\" border=\"0\" " . 
				tooltip(_("Element nach oben")) ."></a>";
			}
			if (!$this->tree->isLastKid($item_id)){
				$head .= "<a href=\"". $this->getSelf("cmd=OrderItem&direction=down&item_id=$item_id") . 
				"\"><img src=\"pictures/move_down.gif\" hspace=\"4\" width=\"13\" height=\"11\" border=\"0\" " . 
				tooltip(_("Element nach unten")) . "></a>";
			}
			$head .= "&nbsp;";
		}
		return $head;
	}
	
}
//test
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
include "html_head.inc.php";
$test = new StudipRangeTreeViewAdmin();
$test->showTree();
echo "</table>";
page_close();
?>
