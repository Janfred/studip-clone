<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemTreeViewSimple.class.php
// Class to print out the seminar tree
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
require_once($ABSOLUTE_PATH_STUDIP . "StudipSemTree.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "TreeView.class.php");

/**
* class to print out the seminar tree
*
* This class prints out a html representation a part of the tree
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipSemTreeViewSimple {

	
	var $tree;
	
	/**
	* constructor
	*
	* @access public
	*/
	function StudipSemTreeViewSimple($start_item_id = "root", $sem_number = false){
		$this->start_item_id = ($start_item_id) ? $start_item_id : "root";
		$this->root_content = "Eine gaaaaaanz tolle Uni.";
		$args = null;
		if ($sem_number !== false){
			$args = array('sem_number' => $sem_number);
		}
		$this->tree =& TreeAbstract::GetInstance("StudipSemTree",$args);
		$this->tree->enable_lonely_sem = false;
	}
	
	function showSemTree(){
		echo "\n<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo "\n<tr><td class=\"steelgraulight\" align=\"left\" valign=\"top\" style=\"font-size:10pt;\">"
			. "<div style=\"margin-left:10px\"><b>" . _("Studienbereiche:"). "</b><br>". $this->getSemPath();
		echo "</div></td></tr>";
		echo "\n<tr><td class=\"steel1\"  align=\"center\" valign=\"center\">";
		$this->showKids($this->start_item_id);
		echo "\n</td></tr><tr><td class=\"steelgraulight\"  align=\"left\" valign=\"center\">";
		$this->showContent($this->start_item_id);
		echo "\n</td></tr></table>";
	}
	
	function showKids($item_id){
		$num_kids = $this->tree->getNumKids($item_id);
		$kids = $this->tree->getKids($item_id);
		echo "\n<table width=\"95%\" border=\"0\" cellpadding=\"0\" cellspacing=\"10\"><tr>\n<td class=\"steel1\" width=\"50%\" align=\"left\" valign=\"top\">";
		for ($i = 0; $i < $num_kids; ++$i){
			$num_entries = $this->tree->getNumEntries($kids[$i],true);
			echo "<b><a " . tooltip(sprintf(_("%s Einträge in allen Unterebenen vorhanden"), $num_entries)) . " href=\"" .$this->getSelf("start_item_id={$kids[$i]}", false) . "\">";
			echo "<span style=\"font-size:10pt;\">" . htmlReady($this->tree->tree_data[$kids[$i]]['name']);
			echo "&nbsp;($num_entries)</span>";
			echo "</a></b>";
			if ($i == ceil($num_kids / 2)-1){
				echo "</td>\n<td class=\"steel1\" align=\"left\" valign=\"top\">";
			} else {
				echo "<br>";
			}
		}
		if (!$num_kids){
			echo "<p style=\"font-size:10pt\"><b>";
			echo _("Auf dieser Ebene existieren keine weiteren Unterebenen.");
			echo "</b></p>";
		}
		if ($item_id != "root"){
			echo "\n<div align=\"right\" style=\"font-size:10pt;\"><a href=\"" .$this->getSelf("start_item_id={$this->tree->tree_data[$item_id]['parent_id']}", false) . "\">
			<img src=\"pictures/move_left.gif\" border=\"0\" style=\"vertical-align:bottom;margin-right:3px;\">" . _("eine Ebene zur&uuml;ck") . "</a></div>";
		}
		echo "</td></tr></table>";
	}
	
	function showContent($item_id){
		echo "\n<div style=\"margin-left:10px\">";
		if ($item_id == "root"){
			echo "\n<p style=\"font-size:10pt\"><b><u>" . htmlReady($this->tree->root_name) ." </u></b></p>";
			echo "\n<p style=\"font-size:10pt\">" . formatReady($this->root_content) ." </p>";
		} else {
			echo "\n<p style=\"font-size:10pt\"><b><u>" . htmlReady($this->tree->tree_data[$item_id]['name']) ." </u></b></p>";
			echo "\n<p style=\"font-size:10pt\">" . formatReady($this->tree->tree_data[$item_id]['info']) ." </p>";
			echo "\n<p style=\"font-size:10pt;\">";
			if ($num_entries = $this->tree->getNumEntries($item_id)){
				echo "<a " . tooltip(_("alle Einträge auf dieser Ebene anzeigen")) ." href=\"" . $this->getSelf("cmd=show_sem_range&item_id=$item_id") ."\">";
				printf(_("<b>%s</b> Eintr&auml;ge auf dieser Ebene.&nbsp;"),$num_entries);
				echo "</a>";
			} else {
				echo _("Keine Eintr&auml;ge auf dieser Ebene vorhanden!");
			}
			if ($this->tree->hasKids($item_id) && ($num_entries = $this->tree->getNumEntries($this->start_item_id,true))){
				echo "<br><br>";
				echo "<a " . tooltip(_("alle Einträge in allen Unterebenen anzeigen")) ." href=\"" . $this->getSelf("cmd=show_sem_range&item_id={$this->start_item_id}_withkids") ."\">";
				printf(_("<b>%s</b> Eintr&auml;ge in allen Unterebenen vorhanden"), $num_entries);
				echo "</a>";
		}
			echo "</p>";
		}
		echo "\n</div>";
	}
	function getSemPath(){
		
		if ($parents = $this->tree->getParents($this->start_item_id)){
			for($i = count($parents)-1; $i >= 0; --$i){
				$ret .= "&nbsp;&gt;&nbsp;<a href=\"" . $this->getSelf("start_item_id={$parents[$i]}",false) 
					. "\">" .htmlReady($this->tree->tree_data[$parents[$i]]["name"]) . "</a>";
			}
		}
		if ($this->start_item_id == "root") {
			$ret = "&nbsp;&gt;&nbsp;<a href=\"" . $this->getSelf("start_item_id=root",false) . "\">" .htmlReady($this->tree->root_name) . "</a>";
		} else {
			$ret .= "&nbsp;&gt;&nbsp;<a href=\"" . $this->getSelf("start_item_id={$this->start_item_id}",false) . "\">" . htmlReady($this->tree->tree_data[$this->start_item_id]["name"]) . "</a>";
		
		}
		return $ret;
	}
			
	
	
	function getSelf($param = "", $with_start_item = true){
		if ($param)
			$url = $GLOBALS['PHP_SELF'] . (($with_start_item) ? "?start_item_id=" . $this->start_item_id . "&" : "?") . $param ;
		else
			$url = $GLOBALS['PHP_SELF'] . (($with_start_item) ? "?start_item_id=" . $this->start_item_id : "") ;
		return $url;
	}
}
//test
//page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
//include "html_head.inc.php";
//include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
//$test = new StudipSemTreeViewSimple($start_item_id);
//$test->showSemTree();
//page_close();
?>
