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
	
	var $edit_item_id;
	
	var $move_item_id;
	
	var $search_result;

	var $msg;
	
	var $marked_item;
	
	/**
	* constructor
	*
	* only calls the base class constructor
	* @access public
	*/
	function StudipRangeTreeViewAdmin(){
		global $sess,$_marked_item;
		$base_class = get_parent_class($this);
		//parent::$base_class($item_id); //calling the baseclass constructor 
		$this->$base_class(); //calling the baseclass constructor PHP < 4.1.0
		if (is_object($sess)){
			$sess->register("_marked_item");
			$this->marked_item =& $_marked_item;
		}
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
					$tmp = $this->tree->getAdminRange($key);
					for ($i = 0; $i < count($i); ++$i){
						if ($tmp[$i])
							$studip_object_status[$tmp[$i]] = ($user_perm == "root") ? 1 : -1;
					}
				}
			}
		}
		if (is_array($this->open_ranges)){
			foreach ($this->open_ranges as $key => $value){
				if ($key != 'root'){
					$tmp = $this->tree->getAdminRange($key);
					for ($i = 0; $i < count($i); ++$i){
						if ($tmp[$i])
							$studip_object_status[$tmp[$i]] = ($user_perm == "root") ? 1 : -1;
					}
					$tmp = $this->tree->getAdminRange($this->tree->tree_data[$key]['parent_id']);
					for ($i = 0; $i < count($i); ++$i){
						if ($tmp[$i])
							$studip_object_status[$tmp[$i]] = ($user_perm == "root") ? 1 : -1;
					}
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
			if (method_exists($this,$exec_func)){
				if ($this->$exec_func()){
					$this->tree->init();
					$this->initTreeStatus();
				}
			}
		}
		if ($this->mode == "MoveItem")
			$this->move_item_id = $this->marked_item;
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
		$this->mode = "";
		$this->msg = "msg§" . (($direction == "up") ? _("Element wurde eine Position nach oben verschoben.") : _("Element wurde eine Position nach unten verschoben."));
		return true;
	}
	
	function execCommandNewItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		if ($this->isItemAdmin($item_id)){
			$level_items = $this->tree->getKids($item_id);
			$new_item_id = DbView::get_uniqid();
			if (!is_array($level_items)){
				$level_items[0] = $new_item_id;
			} else {
				$level_items[] = $new_item_id;
			}
			$this->tree->tree_childs[$item_id] = $level_items;
			$this->tree->tree_data[$new_item_id] = array('parent_id' => $item_id, 'name' => _("Neues Element"), 'priority' => (count($level_items)-1));
			$this->anchor = $new_item_id;
			$this->edit_item_id = $new_item_id;
			$this->open_ranges[$item_id] = true;
			$this->open_items[$new_item_id] = true;
			if ($this->mode != "NewItem")
				$this->msg = "info§" . _("W&auml;hlen sie einen Namen für dieses Element, oder verlinken sie es mit einer Stud.IP Einrichtung");
			$this->mode = "NewItem";
		}
		return false;
	}
	
	function execCommandSearchStudip(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$parent_id = $_REQUEST['parent_id'];
		$search_str = $_REQUEST['edit_search'];
		$view = new DbView();
		if(strlen($search_str) > 1){
			$rs = $view->get_query("view:TREE_SEARCH_INST:$search_str");
			while ($rs->next_record()){
				$this->search_result[$rs->f("Institut_id")]['name'] = $rs->f("Name");
				$this->search_result[$rs->f("Institut_id")]['studip_object'] = "inst";
			}
			if ($parent_id == "root"){
				$rs = $view->get_query("view:TREE_SEARCH_FAK:$search_str");
				while ($rs->next_record()){
					$this->search_result[$rs->f("Fakultaets_id")]['name'] = $rs->f("Name");
					$this->search_result[$rs->f("Fakultaets_id")]['studip_object'] = "fak";
				}
			}
		$this->msg = "info§" . sprintf(_("Ihre Suche ergab %s Treffer."),count($this->search_result));
		} else {
			$this->msg = "error§" . _("Sie haben keinen Suchbegriff eingegeben.");
		}
		if ($this->mode == "NewItem"){
			$_REQUEST['item_id'] = $parent_id;
			$this->execCommandNewItem();
		} else {
			$this->anchor = $item_id;
			$this->edit_item_id = $item_id;
		}
		return false;
	}
	
	function execCommandEditItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		if ($this->isItemAdmin($item_id) || $this->isParentAdmin($item_id)){
			$this->mode = "EditItem";
			$this->anchor = $item_id;
			$this->edit_item_id = $item_id;
			$this->msg = "info§" . _("W&auml;hlen sie einen Namen für dieses Element, oder verlinken sie es mit einer Stud.IP Einrichtung");
		}
		return false;
	}
	
	function execCommandInsertItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$parent_id = $_REQUEST['parent_id'];
		$item_name = $_REQUEST['edit_name'];
		$tmp = explode(":",$_REQUEST['edit_studip_object']);
		if ($tmp[1] == "fak" || $tmp[1] == "inst"){
			$studip_object = $tmp[1];
			$studip_object_id = $tmp[0];
		} else {
			$studip_object = "";
			$studip_object_id = "";
		}
		$view = new DbView();
		if ($this->mode == "NewItem" && $item_id){
			if ($this->isItemAdmin($parent_id)){
				$priority = count($this->tree->getKids($parent_id));
				$rs = $view->get_query("view:TREE_INS_ITEM:$item_id,$parent_id,$item_name,$priority,$studip_object,$studip_object_id");
				if ($rs->affected_rows()){
					$this->mode = "";
					$this->anchor = $item_id;
					$this->open_items[$item_id] = true;
					$this->msg = "msg§" . _("Dieses Element wurde neu eingef&uuml;gt.");
				}
			}
		}
		if ($this->mode == "EditItem"){
			if ($this->isParentAdmin($item_id)){
				$rs = $view->get_query("view:TREE_UPD_ITEM:$item_name,$studip_object,$studip_object_id,$item_id");
				if ($rs->affected_rows()){
					$this->msg = "msg§" . _("Element wurde ge&auml;ndert.");
				} else {
					$this->msg = "info§" . _("Keine Ver&auml;nderungen vorgenommen.");
				}
				$this->mode = "";
				$this->anchor = $item_id;
				$this->open_items[$item_id] = true;
				
			}
		}
		return true;
	}
	
	function execCommandAssertDeleteItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		if ($this->isParentAdmin($item_id)){
			$this->mode = "AssertDeleteItem";
			$this->msg = "info§" ._("Sie beabsichtigen dieses Element inklusive aller Unterelemente zu l&ouml;schen. ")
						. sprintf(_("Es werden insgesamt %s Elemente gel&ouml;scht !"),count($this->tree->getKidsKids($item_id))+1)
						. "<br>" . _("Wollen sie diese Elemente wirklich l&ouml;schen ?") . "<br>"
						. "<a href=\"" . $this->getSelf("cmd=DeleteItem&item_id=$item_id") . "\">"
						. "<img " .makeButton("ja2","src") . tooltip(_("löschen"))
						. " border=\"0\"></a>&nbsp;"
						. "<a href=\"" . $this->getSelf("cmd=Cancel&item_id=$item_id") . "\">"
						. "<img " .makeButton("nein","src") . tooltip(_("abbrechen"))
						. " border=\"0\"></a>";
		}
		return false;
	}
	
	function execCommandDeleteItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$deleted = 0;
		$item_name = $this->tree->tree_data[$item_id]['name'];
		if ($this->isParentAdmin($item_id) && $this->mode == "AssertDeleteItem"){
			$this->anchor = $this->tree->tree_data[$item_id]['parent_id'];
			$items_to_delete = $this->tree->getKidsKids($item_id);
			$items_to_delete[] = $item_id;
			$view = new DbView();
			$rs = $view->get_query("view:TREE_DEL_ITEM:{" . join(",",$items_to_delete) . "}");
			if ($deleted = $rs->affected_rows()){
				$this->msg = "msg§" . sprintf(_("Das Element <b>%s</b> und alle Unterelemente (insgesamt %s) wurden gel&ouml;scht. "),htmlReady($item_name),$deleted);
			} else {
				$this->msg = "error§" . _("Fehler, es konnten keine Elemente gel&ouml;scht werden !");
			}
			$this->mode = "";
			$this->open_items[$this->anchor] = true;
		}
		return ($deleted) ? true : false;
	}
	
	function execCommandMoveItem(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
	
	}
	
	function execCommandCancel(){
		global $_REQUEST;
		$item_id = $_REQUEST['item_id'];
		$this->mode = "";
		$this->anchor = $item_id;
		return false;
	}
	
	function isItemAdmin($item_id){
		$admin_ranges = $this->tree->getAdminRange($item_id);
		for ($i = 0; $i < count($admin_ranges); ++$i){
			if ($this->tree_status[$admin_ranges[$i]] == 1){
				return true;
			}
		}
		return false;
	}
	
	function isParentAdmin($item_id){
		$admin_ranges = $this->tree->getAdminRange($this->tree->tree_data[$item_id]['parent_id']);
		for ($i = 0; $i < count($admin_ranges); ++$i){
			if ($this->tree_status[$admin_ranges[$i]] == 1){
				return true;
			}
		}
		return false;
	}
	
	function getItemContent($item_id){
		
		if ($item_id == $this->edit_item_id )
			return $this->getEditItemContent();
		if ($item_id == $this->move_item_id){
			$this->msg = "info§" . sprintf(_("Dieses Element wurde zum Verschieben markiert. Bitte w&auml;hlen sie ein Einfügesymbol %s aus, um das Element zu verschieben.")
						, "<img src=\"pictures/move.gif\" border=\"0\" " .tooltip(_("Einfügesymbol")) . ">");
			}
		$content = "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
		if ($item_id == $this->anchor)
			$content .= $this->getItemMessage();
		$content .= "\n<tr><td align=\"center\">";
		if ($this->isItemAdmin($item_id)){
			$content .= "<a href=\"" . $this->getSelf("cmd=NewItem&item_id=$item_id") . "\">"
						. "<img " .makeButton("neuesobjekt","src") . tooltip(_("Innerhalb dieser Ebene ein neues Element einfügen"))
						. " border=\"0\"></a>&nbsp;";
		}
		if ($this->isParentAdmin($item_id) && $item_id !=$this->start_item_id && $item_id != "root"){
				$content .= "<a href=\"" . $this->getSelf("cmd=EditItem&item_id=$item_id") . "\">"
			. "<img " .makeButton("bearbeiten","src") . tooltip(_("Dieses Element bearbeiten"))
			. " border=\"0\"></a>&nbsp;";
		
			$content .= "<a href=\"" . $this->getSelf("cmd=AssertDeleteItem&item_id=$item_id") . "\">"
			. "<img " .makeButton("loeschen","src") . tooltip(_("Dieses Element löschen"))
			. " border=\"0\"></a>&nbsp;";
			$content .= "<a href=\"" . $this->getSelf("cmd=MoveItem&item_id=$item_id") . "\">"
			. "<img " .makeButton("verschieben","src") . tooltip(_("Dieses Element in eine andere Ebene verschieben"))
			. " border=\"0\"></a>&nbsp;";
		}
		$content .= "</td></tr></table>";
		$content .= "\n<table width=\"90%\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
		if ($item_id == "root"){
			$content .= "\n<tr><td class=\"topic\" align=\"left\">" . htmlReady($this->tree->root_name) ." </td></tr>";
			$content .= "\n</table>";
			return $content;
		}
		$range_object =& RangeTreeObject::GetInstance($item_id);
		$name = ($range_object->item_data['type']) ? $range_object->item_data['type'] . ": " : "";
		$name .= $range_object->item_data['name'];
		$content .= "\n<tr><td class=\"topic\" align=\"left\">" . htmlReady($name) ." </td></tr>";
		if (is_array($range_object->item_data_mapping)){
			$content .= "\n<tr><td class=\"blank\" align=\"left\">";
			foreach ($range_object->item_data_mapping as $key => $value){
				$content .= "<b>" . htmlReady($value) . ":</b>&nbsp;";
				$content .= fixLinks(htmlReady($range_object->item_data[$key])) . "&nbsp; ";
			}
			$content .= "</td></tr>";
		} elseif (!$range_object->item_data['studip_object']){
			$content .= "\n<tr><td class=\"blank\" align=\"left\">" .
						_("Dieses Element ist keine Stud.IP Einrichtung, es hat daher keine Grunddaten.") . "</td></tr>";
		} else {
			$content .= "\n<tr><td class=\"blank\" align=\"left\">" . _("Keine Grunddaten vorhanden!") . "</td></tr>";
		}
		$content .= "\n<tr><td>&nbsp;</td></tr>";
		$kategorien =& $range_object->item_data['categories'];
		if ($kategorien->numRows){
			while($kategorien->nextRow()){
				$content .= "\n<tr><td class=\"topic\">" . htmlReady($kategorien->getField("name")) . "</td></tr>";
				$content .= "\n<tr><td class=\"blank\">" . htmlReady($kategorien->getField("content")) . "</td></tr>";
			}
		} else {
			$content .= "\n<tr><td class=\"blank\">" . _("Keine weiteren Daten vorhanden!") . "</td></tr>";
		}
		$content .= "</table>";
		return $content;
	}
	
	function getItemHead($item_id){
		$head = "";
		$head .= "<b>" . htmlReady($this->tree->tree_data[$item_id]['name']) . "</b>";
		if ($item_id != $this->start_item_id && $this->isParentAdmin($item_id) && $item_id != $this->edit_item_id){
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
	
	function getEditItemContent(){
		$content = "\n<form name=\"item_form\" action=\"" . $this->getSelf("cmd=InsertItem&item_id={$this->edit_item_id}") . "\" method=\"POST\">";
		$content .= "\n<input type=\"HIDDEN\" name=\"parent_id\" value=\"{$this->tree->tree_data[$this->edit_item_id]['parent_id']}\">";
		$content .= "\n<table width=\"90%\" border =\"0\" style=\"border-style: solid; border-color: #000000;  border-width: 1px;font-size: 10pt;\" cellpadding=\"2\" cellspacing=\"2\" align=\"center\" style=\"font-size:10pt\">";
		$content .=  $this->getItemMessage(2);
		$content .= "\n<tr><td colspan=\"2\" class=\"steelgraudunkel\" ><b>". _("Element editieren") . "</b></td></tr>";
		$content .= "\n<tr><td class=\"steel1\" width=\"60%\">". _("Name des Elements:") . "&nbsp;"
				. "<input type=\"TEXT\" name=\"edit_name\" size=\"50\" value=\"" . $this->tree->tree_data[$this->edit_item_id]['name']
				. "\"></td><td class=\"steel1\" align=\"left\"><input type=\"image\" "
				. makeButton("absenden","src") . tooltip("Einstellungen übernehmen") . "></td></tr>";
		$content .= "\n<tr><td colspan=\"2\" class=\"steelgraudunkel\"><b>". _("Element mit Stud.IP Einrichtung verlinken") . "</b></td></tr>";
		$content .= "\n<tr><td colspan=\"2\" class=\"steel1\">" . _("Stud.IP Einrichtung:") . "&nbsp;";
		$content .= "\n<select name=\"edit_studip_object\" onChange=\"document.item_form.edit_name.value=document.item_form.edit_studip_object.options[document.item_form.edit_studip_object.selectedIndex].text;\">";
		$content .= "\n<option value=\"none\" ";
		$content .= ($this->tree->tree_data[$this->edit_item_id]['studip_object']) ? ">" : "selected >";
		$content .= _("Kein Link") . "</option>";
		if ($this->tree->tree_data[$this->edit_item_id]['studip_object']){
			$content .= "\n<option selected value=\"". $this->tree->tree_data[$this->edit_item_id]['studip_object_id'] . ":"
					. $this->tree->tree_data[$this->edit_item_id]['studip_object'] ."\">" 
					. $this->tree->tree_data[$this->edit_item_id]['name'] ."</option>";
		}
		if (count($this->search_result)){
			foreach ($this->search_result as $key => $value){
				$content .= "\n<option value=\"" . $key . ":" . $value['studip_object'] . "\">" . $value['name'] . "</option>";
			}
		}
		$content .= "</select></td></tr></form>";
		$content .= "\n<form name=\"link_form\" action=\"" . $this->getSelf("cmd=SearchStudIP&item_id={$this->edit_item_id}") . "\" method=\"POST\"><tr><td class=\"steel1\">" . _("Stud.IP Einrichtung suchen:") . "&nbsp;";
		$content .= "\n<input type=\"HIDDEN\" name=\"parent_id\" value=\"{$this->tree->tree_data[$this->edit_item_id]['parent_id']}\">";
		$content .= "\n<input type=\"TEXT\" name=\"edit_search\" size=\"30\"></td><td class=\"steel1\" align=\"left\"><input type=\"image\" "
				. makeButton("suchen","src") . tooltip("Einrichtung suchen") . "></td></tr>";
		$content .= "\n</table>";
		
		return $content;
	}
	
	function getItemMessage($colspan = 1){
		if ($this->msg){
			$msg = split("§",$this->msg);
			$pics = array('error' => 'x.gif', 'info' => 'ausruf.gif', 'msg' => 'ok.gif');
			$content = "\n<tr><td colspan=\"{$colspan}\"><table border=\"0\" cellspacing=\"0\" cellpadding=\"2\" width=\"100%\" style=\"font-size:10pt\">
						<tr><td class=\"blank\" align=\"center\" width=\"25\"><img width=\"16\" height=\"16\" src=\"pictures/" . $pics[$msg[0]] . "\" ></td>
						<td class=\"blank\" align=\"left\">" . $msg[1] . "</td></tr>
						</table></td></tr><tr>";
		}
		return $content;
	}
		
	function getSelf($param){
		$url = $GLOBALS['PHP_SELF'] . "?" . DbView::get_uniqid();
		if ($this->mode)
			$url .= "&mode=" . $this->mode;
		if ($param)
			$url .= "&" . $param;
		$url .= "#anchor";
	return $url;
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
