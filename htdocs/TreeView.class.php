<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// TreeView.class.php
// Class to print out html represantation of a tree object based on TreeAbstract.class.php
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
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
/**
* Class to print out html represantation of a tree object based on TreeAbstract.class.php
*
* Class to print out html represantation of a tree object based on TreeAbstract.class.php
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class TreeView {

	/**
	* Reference to the tree structure
	* 
	* @access	private
	* @var	object StudipRangeTree $tree
	*/
	var $tree;
	/**
	* name of used tree class
	* 
	* @access	public
	* @var	string $tree_class_name
	*/
	var $tree_class_name;
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
	* the content of the root element
	*
	* @access	public
	* @var	string	$root_content
	*/
	var $root_content;
	/**
	* constructor
	*
	* registers two session variables, session feature of PHPLib must be available!
	* @access public
	* @param	string	$tree_class_name	name of used tree class
	* @param	mixed	$args				argument passed to the tree class
	*/
	function TreeView($tree_class_name,$args = null){
		global $sess,$_open_ranges,$_open_items;
		$this->tree_class_name = $tree_class_name;
		$this->tree =& TreeAbstract::GetInstance($tree_class_name,$args);
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
				$num_kidskids = count($kidskids);
				for ($i = 0; $i < $num_kidskids; ++$i){
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
			$kidskids = $this->tree->getKidsKids($_REQUEST['open_range']);
			$kidskids[] = $_REQUEST['open_range'];
			$num_kidskids = count($kidskids);
			for ($i = 0; $i < $num_kidskids; ++$i){
				if (!$this->open_ranges[$kidskids[$i]]){
					$this->open_ranges[$kidskids[$i]] = true;
				}
			}
		$this->anchor = $_REQUEST['open_range'];
		}
		
		if ($_REQUEST['close_item'] || $_REQUEST['open_item']){
			$toggle_item = ($_REQUEST['close_item']) ? $_REQUEST['close_item'] : $_REQUEST['open_item'];
			if (!$this->open_items[$toggle_item]){
				$this->open_items[$toggle_item] = true;
				$this->open_ranges[$toggle_item] = true;
			} else {
				unset($this->open_items[$toggle_item]);
			}
		$this->anchor = $toggle_item;
		}
		if ($_REQUEST['item_id'])
			$this->anchor = $_REQUEST['item_id'];
	}
	
	function openItem($item_id){
		$this->open_items[$item_id] = true;
		$this->openRange($this->tree->tree_data[$item_id]['parent_id']);
	}
	
	function openRange($item_id){
		$this->open_ranges[$item_id] = true;
		$parents = $this->tree->getParents($item_id);
		$num_parents = count($parents);
		for ($i = 0; $i < $num_parents; ++$i){
			$this->open_ranges[$parents[$i]] = true;
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
	$num_items = count($items);
	for ($j = 0; $j < $num_items; ++$j){
		ob_start();
		$this->printLevelOutput($items[$j]);
		$this->printItemOutput($items[$j]);
		ob_end_flush();
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
		$level_output = "";
		if ($item_id != $this->start_item_id){
			if ($this->tree->isLastKid($item_id)) 
				$level_output = "<td class=\"blank\" valign=\"top\"  heigth=\"21\" nowrap><img src=\"pictures/forumstrich2.gif\"  border=\"0\" ></td>"; //last
			else 
				$level_output = "<td class=\"blank\" valign=\"top\"  heigth=\"21\" nowrap><img src=\"pictures/forumstrich3.gif\"  border=\"0\" ></td>"; //crossing
			$parent_id = $item_id;
			while($this->tree->tree_data[$parent_id]['parent_id'] != $this->start_item_id){
				$parent_id = $this->tree->tree_data[$parent_id]['parent_id'];
				if ($this->tree->isLastKid($parent_id))
					$level_output = "<td class=\"blank\" valign=\"top\" width=\"10\" heigth=\"21\" nowrap><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output; //nothing
				else
					$level_output = "<td class=\"blank\" valign=\"top\"  heigth=\"21\" nowrap><img src=\"pictures/forumstrich.gif\"  border=\"0\" ></td>" . $level_output; //vertical line
			}
		}
		$level_output = "<td class=\"blank\" valign=\"top\" width=\"20\" heigth=\"21\" nowrap><img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" ></td>" . $level_output;
		echo "\n<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\"><tr>$level_output";
		return;
	}
	
	/**
	* prints out one item
	*
	* @access	private
	* @param	string	$item_id
	*/
	function printItemOutput($item_id){
		echo "\n<td  class=\"printhead\" nowrap  align=\"left\" valign=\"bottom\">";
		echo $this->getItemHeadPics($item_id);
		echo "\n</td><td class=\"printhead\" nowrap width=\"1\" valign=\"middle\">";
		if ($this->anchor == $item_id)
			echo "<a name=\"anchor\">";
		echo "<img src=\"pictures/forumleer.gif\"  border=\"0\" height=\"20\" width=\"1\">";
		if ($this->anchor == $item_id)
			echo "</a>";
		echo "\n</td><td class=\"printhead\" align=\"left\" width=\"99%\" nowrap valign=\"bottom\">";
		echo $this->getItemHead($item_id);
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
		if (!$this->tree->hasKids($item_id) || !$this->open_ranges[$item_id] || $item_id == $this->start_item_id) 
			$level_output = "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output;
		else 
			$level_output = "<td class=\"blank\" background=\"pictures/forumstrich.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output;
		
		if ($this->tree->isLastKid($item_id) || (!$this->open_ranges[$item_id] && $item_id == $this->start_item_id)) 
			$level_output = "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output;
		else 
			$level_output = "<td class=\"blank\" background=\"pictures/forumstrich.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output;
		if ($item_id != $this->start_item_id){			
			$parent_id = $item_id;
			while($this->tree->tree_data[$parent_id]['parent_id'] != $this->start_item_id){
				$parent_id = $this->tree->tree_data[$parent_id]['parent_id'];
				if ($this->tree->isLastKid($parent_id))
					$level_output = "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output; //nothing
				else
					$level_output = "<td class=\"blank\" background=\"pictures/forumstrich.gif\" ><img src=\"pictures/forumleer.gif\" width=\"10\" height=\"20\" border=\"0\" ></td>" . $level_output; //vertical line
			}
		}
		$level_output = "<td class=\"blank\" background=\"pictures/forumleer.gif\" ><img src=\"pictures/forumleer.gif\" width=\"20\" height=\"20\" border=\"0\" ></td>" . $level_output;
	
		echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\"><tr>$level_output";
		echo "<td class=\"printcontent\" width=\"100%\"><br>";
		echo $this->getItemContent($item_id);
		echo "<br></td></tr></table>";
		return;
	}
	
	/**
	* returns html for the icons in front of the name of the item 
	*
	* @access	private
	* @param	string	$item_id
	* @return	string
	*/
	function getItemHeadPics($item_id){
		$head = "";
		$head .= "<a href=\"";
		$head .= ($this->open_items[$item_id])? $this->getSelf("close_item={$item_id}") . "\"" . tooltip(_("Dieses Element schließen"),true) . ">"
											: $this->getSelf("open_item={$item_id}") . "\"" . tooltip(_("Dieses Element öffnen"),true) . ">";
		$head .= "<img src=\"pictures/";
		$head .= ($this->open_items[$item_id]) ? "forumrotrunt.gif" : "forumgrau.gif";
		$head .= "\" border=\"0\" align=\"baseline\" hspace=\"2\">";
		$head .= (!$this->open_items[$item_id]) ? "<img  src=\"pictures\forumleer.gif\" width=\"5\" border=\"0\">" : ""; 
		$head .= "</a>";
		if ($this->tree->hasKids($item_id)){
			$head .= "<a href=\"";
			$head .= ($this->open_ranges[$item_id]) ? $this->getSelf("close_range={$item_id}") : $this->getSelf("open_range={$item_id}"); 
			$head .= "\"><img border=\"0\"  src=\"pictures/";
			$head .= ($this->open_ranges[$item_id]) ? "cont_folder3.gif" : "cont_folder.gif";
			$head .= "\" ";
			$head .= (!$this->open_ranges[$item_id])? tooltip($this->tree->getNumKidsKids($item_id) . " " . _("Unterelement(e) öffnen")) : tooltip(_("Alle Unterelemente schliessen"));
			$head .= "></a>";
		} else { 
			$head .= "<img src=\"pictures/";
			$head .= ($this->open_items[$item_id]) ? "cont_folder4.gif" : "cont_folder2.gif";
			$head .= "\" " . tooltip(_("Dieses Element hat keine Unterelemente")) . "border=\"0\">";
		}
	return $head;
	}
	
	/**
	* returns html for the name of the item
	*
	* @access	private
	* @param	string	$item_id
	* @return	string
	*/
	function getItemHead($item_id){
		$head = "";
		$head .= "&nbsp;<a class=\"tree\" href=\"";
		$head .= ($this->open_items[$item_id])? $this->getSelf("close_item={$item_id}") . "\"" . tooltip(_("Dieses Element schließen"),true) . "><b>"
											: $this->getSelf("open_item={$item_id}") . "\"" . tooltip(_("Dieses Element öffnen"),true) . ">";
		$head .= htmlReady($this->tree->tree_data[$item_id]['name']);
		$head .= ($this->open_items[$item_id]) ? "</b></a>" : "</a>";
		return $head;
	}
	
	/**
	* returns html for the content body of the item
	*
	* @access	private
	* @param	string	$item_id
	* @return	string
	*/
	function getItemContent($item_id){
		$content = "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
		if ($item_id == "root"){
			$content .= "\n<tr><td class=\"topic\" align=\"left\">" . htmlReady($this->tree->root_name) ." </td></tr>";
			$content .= "\n<tr><td class=\"blank\" align=\"left\">" . $this->root_content ." </td></tr>";
			$content .= "\n</table>";
			return $content;
		}
		$content .= "\n<tr><td class=\"blank\" align=\"left\">Inhalt für Element <b>{$this->tree->tree_data[$item_id]['name']} ($item_id)</b><br>".formatReady($this->tree->tree_data[$item_id]['description'])."</td></tr>";
		$content .= "</table>";
		return $content;
	}
	
	/**
	* returns script name
	*
	* @access	private
	* @param	string	$param	
	* @return	string
	*/
	function getSelf($param){
		$url = $GLOBALS['PHP_SELF'];
		if ($param)
			$url .= "?" . $param . "#anchor";
		return $url;
	}
}
//test
//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
//include "ForumTree.class.php";
//include "html_head.inc.php";
//$test = new TreeView("ForumTree",array('seminar_id' => '3f43debe372cfd7d4da6afa4ca40616f'));
//$test->showTree();
//page_close();
?>
