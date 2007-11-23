<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipSemSearch.class.php
// Class to build search formular and execute search
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

require_once('lib/classes/StudipSemTree.class.php');
require_once('lib/classes/StudipRangeTree.class.php');
require_once('lib/classes/SemesterData.class.php');
require_once('lib/visual.inc.php');
require_once 'lib/functions.php';


/**
* Class to build search formular and execute search
*
* 
*
* @access	public	
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	DBTools
**/
class StudipSemSearch {
	
	var $view;
	
	var $search_result;
	
	var $form_name;
	
	var $num_sem;
	
	var $sem_tree;
	
	var $range_tree;
	
	var $search_done = false;
	
	var $found_rows = false;
	
	var $search_button_clicked = false;
	
	var $new_search_button_clicked = false;
	
	var $sem_change_button_clicked = false;
	
	var $override_sem = false;
	
	var $attributes_default = array('style' => 'width:100%;');
	
	var $search_fields = array('title' => array('type' => 'text'),
								'sub_title' => array('type' => 'text'),
								'number' => array('type' => 'text'),
								'comment' => array('type' => 'text'),
								'lecturer' => array('type' => 'text'),
								'scope' => array('type' => 'text'),
								'quick_search' => array('type' => 'text'),
								'type' => array('type' => 'select', 'default_value' => 'all', 'class' => 'all',size => 50),
								'sem' => array('type' => 'select', 'default_value' => 'all'),
								'category' => array('type' => 'select', 'default_value' => 'all', size => 50),
								'combination' => array('type' => 'select', 'default_value' => 'AND'),
								'scope_choose' => array('type' => 'select', 'default_value' => 'root', size => 45),
								'range_choose' => array('type' => 'select', 'default_value' => 'root', size => 45),
								'qs_choose' => array('type' => 'select', 'default_value' => 'all', 'content' => array()));
	
	var $search_scopes = array();
	var $search_ranges = array();
	
	var $visible_only = false;
	
	function StudipSemSearch($form_name = "search_sem", $auto_search = true, $visible_only = false){
		$this->view = new DbView();
		$this->form_name = $form_name;
		$this->sem_dates = SemesterData::GetSemesterArray();
		$this->visible_only = $visible_only;
		if(isset($_REQUEST[$form_name . "_do_search_x"]) || (isset($_REQUEST[$form_name . "_send"]) && !$_REQUEST[$form_name . "_sem_change_x"])){
			$this->search_button_clicked = true;
			if ($auto_search){
				$this->doSearch();
				$this->search_done = true;
			}
		}
		
		if(isset($_REQUEST[$form_name . "_new_search_x"])){
			$this->new_search_button_clicked = true;
		}
		if(isset($_REQUEST[$form_name . "_sem_change_x"])){
			$this->sem_change_button_clicked = true;
		}
		
	}
	
	function getSearchField($name,$attributes = false,$default = false){
		if (!$attributes){
			$attributes = $this->attributes_default;
		}
		if (!$default && $this->search_button_clicked){
			$default = stripslashes($_REQUEST[$this->form_name . "_" . $name]);
		}
		if($this->search_fields[$name]['type']){
			$method = "getSearchField" . $this->search_fields[$name]['type'];
			return $this->$method($name,$attributes,$default);
		}
	}
	
	function getSearchFieldtext($name,$attributes, $default){
		$ret = "\n<input type=\"text\" name=\"{$this->form_name}_{$name}\" " . (($default) ? "value=\"".htmlReady($default)."\" " : "");
		foreach($attributes as $key => $value){
			$ret .= " $key=\"$value\"";
		}
		$ret .= ">";
		return $ret;
	}
	
	function getSearchFieldselect($name, $attributes, $default){
		$ret = "\n<select name=\"{$this->form_name}_{$name}\" ";
		foreach($attributes as $key => $value){
			$ret .= " $key=\"$value\"";
		}
		$ret .= ">";
		if ($default === false){
			$default = $this->search_fields[$name]['default_value'];
		}
		if ($name == "combination"){
			$options = array(array('name' =>_("UND"),'value' => 'AND'),array('name' => _("ODER"), 'value' => 'OR'));
		} elseif ($name == "sem"){
			$options = array(array('name' =>_("alle"),'value' => 'all'));
			for ($i = count($this->sem_dates) -1 ; $i >= 0; --$i){
				$options[] = array('name' => $this->sem_dates[$i]['name'], 'value' => $i);
			}
		} elseif ($name == "type"){
			$options = array(array('name' =>_("alle"),'value' => 'all'));
			foreach($GLOBALS['SEM_TYPE'] as $type_key => $type_value){
				if($this->search_fields['type']['class'] == 'all' || $type_value['class'] == $this->search_fields['type']['class']){
					$options[] = array('name' => htmlReady(my_substr($type_value['name'] . " (". $GLOBALS['SEM_CLASS'][$type_value['class']]['name'] .")",0,$this->search_fields['type']['size'])),
										'value' => $type_key);
				}
			}
		} elseif ($name == "category"){
			$options = array(array('name' =>_("alle"),'value' => 'all'));
			foreach($GLOBALS['SEM_CLASS'] as $class_key => $class_value){
				$options[] = array('name' => htmlReady(my_substr($class_value['name'],0,$this->search_fields['category']['size'])),
										'value' => $class_key);
				}
		} elseif ($name == "scope_choose"){
			if(!is_object($this->sem_tree)){
				$this->sem_tree =& TreeAbstract::GetInstance("StudipSemTree", false);
			}
			$options = array(array('name' => htmlReady(my_substr($this->sem_tree->root_name,0,$this->search_fields['scope_choose']['size'])), 'value' => 'root'));
			for($i = 0; $i < count($this->search_scopes); ++$i){
				$options[] = array('name' => htmlReady(my_substr($this->sem_tree->tree_data[$this->search_scopes[$i]]['name'],0,$this->search_fields['scope_choose']['size'])), 'value' => $this->search_scopes[$i]);
			}
		} elseif ($name == "range_choose"){
			if(!is_object($this->range_tree)){
				$this->range_tree =& TreeAbstract::GetInstance("StudipRangeTree", false);
			}
			$options = array(array('name' => htmlReady(my_substr($this->range_tree->root_name,0,$this->search_fields['range_choose']['size'])), 'value' => 'root'));
			for($i = 0; $i < count($this->search_ranges); ++$i){
				$options[] = array('name' => htmlReady(my_substr($this->range_tree->tree_data[$this->search_ranges[$i]]['name'],0,$this->search_fields['scope_choose']['size'])), 'value' => $this->search_ranges[$i]);
			}
		} elseif ($name == "qs_choose"){
			$options = array(array('name' =>_("alles"),'value' => 'all'));
			foreach ($this->search_fields['qs_choose']['content'] as $key => $value){
				$options[] = array('name' => htmlReady($value), 'value' => $key);
			}
		}
		
		for ($i = 0; $i < count($options); ++$i){
			$ret .= "\n<option value=\"{$options[$i]['value']}\" " . (($default == "" . $options[$i]['value']) ? " selected " : "");
			$ret .= ">{$options[$i]['name']}</option>";
		}
		$ret .= "\n</select>";
		return $ret;
	}
	
	function getFormStart($action = ""){
		if (!$action){
			$action = $GLOBALS['PHP_SELF'];
		}
		$ret = "\n<form action=\"$action\" method=\"post\" name=\"{$this->form_name}\">";
		return $ret;
	}
	
	function getFormEnd(){
		if ($this->search_fields['type']['class'] != 'all'){
			$ret = "\n<input type=\"hidden\" name=\"{$this->form_name}_category\" value=\"{$this->search_fields['type']['class']}\">";
		}
		return $ret . "\n<input type=\"hidden\" name=\"{$this->form_name}_send\" value=\"1\"></form>";
	}
	
	function getHiddenField($name, $value = false){
		if (!$value && $this->search_fields[$name]){
			$value = $this->search_fields[$name]['default_value'];
		}
		return "\n<input type=\"hidden\" name=\"{$this->form_name}_{$name}\" value=\"".htmlReady($value)."\"";
	}
	
	function getSearchButton($attributes = false, $tooltip = false){
		if (!$tooltip){
			$tooltip = _("Suche starten");
		}
		$ret = "\n<input type=\"image\" name=\"{$this->form_name}_do_search\" " . makeButton("suchestarten","src") . tooltip($tooltip);
		if ($attributes){
			foreach($attributes as $key => $value){
				$ret .= " $key=\"$value\"";
			}
		}
		$ret .= " border=\"0\">";
		return $ret;
	}
	
	function getNewSearchButton($attributes = false, $tooltip = false){
		if (!$tooltip){
			$tooltip = _("Neue Suche starten");
		}
		$ret = "\n<input type=\"image\" name=\"{$this->form_name}_new_search\" " . makeButton("neuesuche","src") . tooltip($tooltip);
		if ($attributes){
			foreach($attributes as $key => $value){
				$ret .= " $key=\"$value\"";
			}
		}
		$ret .= " border=\"0\">";
		return $ret;
	}
	
	function getSemChangeButton($attributes = false, $tooltip = false){
		if (!$tooltip){
			$tooltip = _("anderes Semester auswählen");
		}
		$ret = "\n<input type=\"image\" name=\"{$this->form_name}_sem_change\" " . makeButton("auswaehlen","src") . tooltip($tooltip);
		if ($attributes){
			foreach($attributes as $key => $value){
				$ret .= " $key=\"$value\"";
			}
		}
		$ret .= " border=\"0\">";
	return $ret;
	}
		
	function doSearch(){
		$clause = "";
		$and_clause = "";
		$this->search_result = new DbSnapshot();
		$combination = ($_REQUEST[$this->form_name . "_combination"]) ? $_REQUEST[$this->form_name . "_combination"] : "AND";
		
		if (isset($_REQUEST[$this->form_name . "_quick_search"]) && isset($_REQUEST[$this->form_name . "_qs_choose"])){
			if (strlen($_REQUEST[$this->form_name . "_quick_search"]) < 2){
				return false;
			}
			if ($_REQUEST[$this->form_name . "_qs_choose"] == 'all'){
				foreach ($this->search_fields['qs_choose']['content'] as $key => $value){
					$_REQUEST[$this->form_name . "_" . $key] = trim($_REQUEST[$this->form_name . "_quick_search"]);
				}
				$combination = "OR";
			} else {
				$_REQUEST[$this->form_name . "_" . $_REQUEST[$this->form_name . "_qs_choose"]] = trim($_REQUEST[$this->form_name . "_quick_search"]);
			}
			if (is_array($this->override_sem)){
				$clause = " HAVING sem_number IN(" . join(",",$this->override_sem) . ") ";
			}
		
		}
		
		if (isset($_REQUEST[$this->form_name . "_sem"]) && $_REQUEST[$this->form_name . "_sem"] != 'all'){
			$sem_number = $_REQUEST[$this->form_name . "_sem"];
			$clause = " HAVING (sem_number <= $sem_number AND (sem_number_end >= $sem_number OR sem_number_end = -1)) ";
		}
		if (isset($_REQUEST[$this->form_name . "_category"]) && $_REQUEST[$this->form_name . "_category"] != 'all'){
			foreach($GLOBALS['SEM_TYPE'] as $type_key => $type_value){
				if($type_value['class'] == $_REQUEST[$this->form_name . "_category"])
					$sem_types[] = $type_key;
			}
		}
		
		if (isset($_REQUEST[$this->form_name . "_type"]) && $_REQUEST[$this->form_name . "_type"] != 'all'){
			unset($sem_types);
			$sem_types[0] = $_REQUEST[$this->form_name . "_type"];
		}
		if (is_array($sem_types)){
			$clause = " AND c.status IN('" . join("','",$sem_types) . "') " . $clause;
		}
		
		if (isset($_REQUEST[$this->form_name . "_scope_choose"]) && $_REQUEST[$this->form_name . "_scope_choose"] != 'root'){
			if(!is_object($this->sem_tree)){
				$this->sem_tree =& TreeAbstract::GetInstance("StudipSemTree", false);
			}
			$this->view->params[0] = (is_array($sem_types) ? $sem_types : $this->sem_tree->sem_status);
			$this->view->params[1] = $this->visible_only ? "c.visible=1" : "1";

			$this->view->params[2] = $this->sem_tree->getKidsKids($_REQUEST[$this->form_name . "_scope_choose"]);
			$this->view->params[2][] = $_REQUEST[$this->form_name . "_scope_choose"];
			$this->view->params[3] = $clause;
			$snap = new DbSnapshot($this->view->get_query("view:SEM_TREE_GET_SEMIDS"));
			if ($snap->numRows){
				$clause = " AND c.seminar_id IN('" . join("','",$snap->getRows("seminar_id")) ."')" . $clause;
			} else {
				return true;
			}
			unset($snap);
		}
		
		if (isset($_REQUEST[$this->form_name . "_range_choose"]) && $_REQUEST[$this->form_name . "_range_choose"] != 'root'){
			$range_object =& RangeTreeObject::GetInstance($_REQUEST[$this->form_name . "_range_choose"]);
			$this->view->params[0] = $range_object->getAllObjectKids();
			$this->view->params[0][] = $range_object->item_data['studip_object_id'];
			$this->view->params[1] = ($this->visible_only ? " AND c.visible=1 " : "");
			$this->view->params[2] = $clause;
			$snap = new DbSnapshot($this->view->get_query("view:SEM_INST_GET_SEM"));
			if ($snap->numRows){
				$clause = " AND c.seminar_id IN('" . join("','",$snap->getRows("Seminar_id")) ."')" . $clause;
			} else {
				return true;
			}
			unset($snap);
		}
		
		
		if (isset($_REQUEST[$this->form_name . "_lecturer"]) && strlen($_REQUEST[$this->form_name . "_lecturer"]) > 2){
			$this->view->params[0] = $this->visible_only ? "c.visible=1" : "1";
			$this->view->params[1] = "%".trim($_REQUEST[$this->form_name . "_lecturer"])."%";
			$this->view->params[2] = "%".trim($_REQUEST[$this->form_name . "_lecturer"])."%";
			$this->view->params[3] = "%".trim($_REQUEST[$this->form_name . "_lecturer"])."%";
			$this->view->params[4] = $clause;
			$snap = new DbSnapshot($this->view->get_query("view:SEM_SEARCH_LECTURER"));
			$this->search_result = $snap;
			$this->found_rows = $this->search_result->numRows;
		}

		
		if ($combination == "AND" && $this->search_result->numRows){
			$and_clause = " AND c.seminar_id IN('" . join("','",$this->search_result->getRows("seminar_id")) ."')";
		}
		
		if ((isset($_REQUEST[$this->form_name . "_title"]) && strlen($_REQUEST[$this->form_name . "_title"]) > 2) ||
			(isset($_REQUEST[$this->form_name . "_sub_title"]) && strlen($_REQUEST[$this->form_name . "_sub_title"]) > 2) ||
			(isset($_REQUEST[$this->form_name . "_number"]) && strlen($_REQUEST[$this->form_name . "_number"]) > 2) ||
			(isset($_REQUEST[$this->form_name . "_comment"]) && strlen($_REQUEST[$this->form_name . "_comment"]) > 2)){
			$this->view->params[0] .= ($_REQUEST[$this->form_name . "_title"]) ? " Name LIKE '%".trim($_REQUEST[$this->form_name . "_title"])."%' " : " ";
			$this->view->params[0] .= ($_REQUEST[$this->form_name . "_title"] && $_REQUEST[$this->form_name . "_sub_title"]) ? $combination : " ";
			$this->view->params[0] .= ($_REQUEST[$this->form_name . "_sub_title"]) ? " Untertitel LIKE '%".trim($_REQUEST[$this->form_name . "_sub_title"])."%' " : " ";
			$this->view->params[0] .= (($_REQUEST[$this->form_name . "_title"] || $_REQUEST[$this->form_name . "_sub_title"]) && $_REQUEST[$this->form_name . "_comment"]) ? $combination : " ";
			$this->view->params[0] .= ($_REQUEST[$this->form_name . "_comment"]) ? " Beschreibung LIKE '%".trim($_REQUEST[$this->form_name . "_comment"])."%' " : " ";
			$this->view->params[0] .= (($_REQUEST[$this->form_name . "_title"] || $_REQUEST[$this->form_name . "_sub_title"] || $_REQUEST[$this->form_name . "_comment"]) && $_REQUEST[$this->form_name . "_number"]) ? $combination : " ";
			$this->view->params[0] .= ($_REQUEST[$this->form_name . "_number"]) ? " VeranstaltungsNummer LIKE '%".trim($_REQUEST[$this->form_name . "_number"])."%' " : " ";
			$this->view->params[0] = ($this->visible_only ? " c.visible=1 AND " : "") . "(" . $this->view->params[0] .")";
			$this->view->params[1] =  $and_clause . $clause;
			$snap = new DbSnapshot($this->view->get_query("view:SEM_SEARCH_SEM"));
			if ($this->found_rows === false){
				$this->search_result = $snap;
			} else {
				$this->search_result->mergeSnapshot($snap,"seminar_id",$combination);
			}
			$this->found_rows = $this->search_result->numRows;
		}
		
		if ($combination == "AND" && $this->search_result->numRows){
			$and_clause = " AND c.seminar_id IN('" . join("','",$this->search_result->getRows("seminar_id")) ."')";
		}
		
		if (isset($_REQUEST[$this->form_name . "_scope"]) && strlen($_REQUEST[$this->form_name . "_scope"]) > 2){
			$this->view->params[0] = $this->visible_only ? "c.visible=1" : "1";
			$this->view->params[1] = "%".trim($_REQUEST[$this->form_name . "_scope"])."%";
			$this->view->params[2] = $and_clause . $clause;
			$snap = new DbSnapshot($this->view->get_query("view:SEM_TREE_SEARCH_SEM"));
			if ($this->found_rows === false){
				$this->search_result = $snap;
			} else {
				$this->search_result->mergeSnapshot($snap,"seminar_id",$combination);
			}
			$this->found_rows = $this->search_result->numRows;
		}
		return;
	}
}
?>
