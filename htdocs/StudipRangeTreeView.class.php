<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipRangeTreeView.class.php
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
require_once($ABSOLUTE_PATH_STUDIP . "StudipRangeTree.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "RangeTreeObject.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
/**
* class to print out the "range tree"
*
* This class prints out a html representation of the whole or part of the tree
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipRangeTreeView {

	/**
	* Reference to the tree structure
	* 
	* @access	private
	* @var	object StudipRangeTree $tree
	*/
	var $tree;
	/**
	* contains the item with the current html anchor
	* 
	* @access	public
	* @var	string	$anchor
	*/
	var $anchor;
	/**
	* array containing all open items
	*
	* this is a reference to a global session variable, managed by PHPLib
	* @access	public
	* @var	array	$open_items
	*/
	var $open_items;
	/**
	* array containing all open item nodes
	*
	* this is a reference to a global session variable, managed by PHPLib
	* @access	public
	* @var	array	$open_ranges
	*/
	var $open_ranges;
	/**
	* the item to start with
	*
	* @access	private
	* @var	string	$start_item_id
	*/
	var $start_item_id;
	
	/**
	* constructor
	*
	* registers two session variables, session feature of PHPLib must be available!
	* @access public
	*/
	function StudipRangeTreeView(){
		global $sess,$_open_ranges,$_open_items;
		$this->tree =& StudipRangeTree::GetInstance();
		if (is_object($sess)){
			$sess->register("_open_ranges");
			$sess->register("_open_items");
			$this->open_ranges =& $_open_ranges;
			$this->open_items =& $_open_items;
			$this->handleOpenRanges();
		}
		
	}
	
	/**
	* manages the session variables used for the open/close thing
	*
	* @access	private
	*/
	function handleOpenRanges(){
		global $_REQUEST;
		if ($_REQUEST['close_range']){
			if ($_REQUEST['close_range'] == 'root'){
				$this->open_ranges = null;
				$this->open_items = null;
			} else {
				$kidskids = $this->tree->getKidsKids($_REQUEST['close_range']);
				$kidskids[] = $_REQUEST['close_range'];
				for ($i = 0; $i < count($kidskids); ++$i){
					if ($this->open_ranges[$kidskids[$i]]){
						unset($this->open_ranges[$kidskids[$i]]);
					}
					if ($this->open_items[$kidskids[$i]]){
						unset($this->open_items[$kidskids[$i]]);
					}
				}
			}
		$this->anchor = $_REQUEST['close_range'];
		}
		
		if ($_REQUEST['open_range']){
			if (!$this->open_ranges[$_REQUEST['open_range']]){
				$this->open_ranges[$_REQUEST['open_range']] = true;
			}
		$this->anchor = $_REQUEST['open_range'];
		}
		if ($_REQUEST['close_item'] || $_REQUEST['open_item']){
			$toggle_item = ($_REQUEST['close_item']) ? $_REQUEST['close_item'] : $_REQUEST['open_item'];
			if (!$this->open_items[$toggle_item]){
				$this->open_items[$toggle_item] = true;
			} else {
				unset($this->open_items[$toggle_item]);
			}
		$this->anchor = $toggle_item;
		}
	}
	
	/**
	* prints out the tree beginning with a given item
	*
	* @access	public
	* @param	string	$item_id
	*/
	function showTree($item_id = "root"){
	$items = array();
	if (!is_array($item_id)){
		$items[0] = $item_id;
		$this->start_item_id = $item_id;
	} else {
		$items = $item_id;
	}
	for ($j = 0; $j < count($items); ++$j){
		$this->printLevelOutput($items[$j]);
		$this->printItemOutput($items[$j]);
		if ($this->tree->hasKids($items[$j]) && $this->open_ranges[$items[$j]]){
			$this->showTree($this->tree->tree_childs[$items[$j]]);
		}
	}
	return;
}
	/**
	* prints out the lines before an item ("Strichlogik" (c) rstockm)
	*
	* @access	private
	* @param	string	$item_id
	*/
	function printLevelOutput($item_id){
		$level = $this->tree->tree_data[$item_id]['level'] - $this->tree->tree_data[$this->start_item_id]['level'];
		$level_output = "<img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" >";
		for($i = 1; $i < $level; ++$i){
			if ($this->tree->isLastKid($this->tree->tree_data[$item_id]['parent_id']))
				$level_output .= "<img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" >"; //nothing
			else
				$level_output .= "<img src=\"pictures/forumstrich.gif\"  border=\"0\" >"; //vertical line
		}
		if ($item_id != $this->start_item_id){
			if ($this->tree->isLastKid($item_id)) 
				$level_output .= "<img src=\"pictures/forumstrich2.gif\"  border=\"0\" >"; //last
			else 
				$level_output .= "<img src=\"pictures/forumstrich3.gif\"  border=\"0\" >"; //crossing
		}
		echo "\n<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td class=\"blank\" valign=\"top\"  heigth=\"21\" nowrap>$level_output</td>";
		return;
	}
	
	/**
	* prints out one item
	*
	* @access	private
	* @param	string	$item_id
	*/
	function printItemOutput($item_id){
		global $PHP_SELF;
		echo "\n<td  class=\"printhead\" nowrap width=\"20\" align=\"left\" valign=\"top\">";
		if ($this->tree->hasKids($item_id)){
			echo "<a href=\"$PHP_SELF?";
			echo ($this->open_ranges[$item_id]) ? "close_range" : "open_range"; 
			echo "={$item_id}#anchor\"><img border=\"0\" src=\"pictures/cont_folder.gif\"  ></a>";
		} else { 
			echo "<img src=\"pictures/forumleer.gif\"  border=\"0\">";
		}
		echo "\n</td><td class=\"printhead\" nowrap width=\"10\" valign=\"middle\">";
		if ($item_id != 'root'){
			echo "<a href=\"$PHP_SELF?";
			echo ($this->open_items[$item_id])? "close_item" : "open_item";
			echo "={$item_id}#anchor\"><img   border=\"0\" src=\"pictures/";
			echo ($this->open_items[$item_id]) ? "forumrotrunt.gif" : "forumrot.gif";
			echo "\"></a>";
		} else {
			echo "<img src=\"pictures/forumleer.gif\"  border=\"0\" height=\"20\" width=\"10\">";
		}
		echo "\n</td><td class=\"printhead\" align=\"left\" width=\"100%\" nowrap valign=\"bottom\">";
		if ($this->anchor == $item_id)
			echo "<a name=\"anchor\">";
		echo "<b>" . htmlReady($this->tree->tree_data[$item_id]['name']) . "</b>";
		if ($this->anchor == $item_id)
			echo "</a>";
		echo "</td></tr></table>";
		if ($this->open_items[$item_id])
			$this->printItemDetails($item_id);
		return;
	}
	
	/**
	* prints out the details for an item, if item is open
	*
	* @access	private
	* @param	string	$item_id
	*/
	function printItemDetails($item_id){
		$level = $this->tree->tree_data[$item_id]['level'] - $this->tree->tree_data[$this->start_item_id]['level'];
		//$level_output = "<img src=\"pictures/forumleer.gif\" width=\"20\" height=\"100%\" border=\"0\" >";
		$level_output = "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" ></td>";
		for($i = 1; $i < $level; ++$i){
			if ($this->tree->isLastKid($this->tree->tree_data[$item_id]['parent_id']) )
				//$level_output .= "<img src=\"pictures/forumleer.gif\" width=\"20\" height=\"100%\" border=\"1\" >";
				$level_output .= "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" ></td>";
			else
				//$level_output .= "<img src=\"pictures/forumstrich.gif\" width=\"10\" height=\"100%\" border=\"1\" >";
				$level_output .= "<td class=\"blank\" background=\"pictures/forumstrich.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>";
		}
		if ($item_id != $this->start_item_id){
			if ($this->tree->isLastKid($item_id)) 
				//$level_output .= "<img src=\"pictures/forumleer.gif\" width=\"20\" height=\"100%\" border=\"1\" >"; 
				$level_output .= "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" ></td>";
			else 
				//$level_output .= "<img src=\"pictures/forumstrich.gif\"  width=\"10\" height=\"100%\" border=\"1\" >";
				$level_output .= "<td class=\"blank\" background=\"pictures/forumstrich.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>";
			if (!$this->tree->hasKids($item_id) || !$this->open_ranges[$item_id]) 
				//$level_output .= "<img src=\"pictures/forumleer.gif\" width=\"20\" height=\"100%\" border=\"1\" >"; 
				$level_output .= "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>";
			else 
				//$level_output .= "<img src=\"pictures/forumstrich.gif\" width=\"10\" height=\"100%\" border=\"1\" >";
				$level_output .= "<td class=\"blank\" background=\"pictures/forumstrich.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>";
		
			
		}
		
		$range_object =& RangeTreeObject::GetInstance($item_id);
		echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>$level_output";
		echo "<td class=\"printcontent\" width=\"100%\">Untergeordnete Bereiche: ".count($range_object->getAllItemKids());
		echo "<br>Pfad: " . htmlReady($range_object->getItemPath());
		echo "</td></tr></table>";
		
		return;
	}
		
		
}
//test
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
include "html_head.inc.php";
$test = new StudipRangeTreeView();
$test->showTree();
echo "</table>";
page_close();
?>
