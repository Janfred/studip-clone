<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipLitSearch.class.php
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

require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/StudipForm.class.php");

$_lit_search_plugins[] = array('name' => "Studip", 'link' => '');

$_lit_search_plugins[] = array('name' => "SUBGoeOpac", 'link' => 'http://goopc4.sub.uni-goettingen.de:8080/DB=1/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');
$_lit_search_plugins[] = array('name' => "Rkgoe", 'link' => 'http://gso.gbv.de/DB=2.90/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');
$_lit_search_plugins[] = array('name' => "Gvk", 'link' => 'http://gso.gbv.de/DB=2.1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');
$_lit_search_plugins[] = array('name' => "WisoFak", 'link' => 'http://goopc4.sub.uni-goettingen.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');
//$_lit_search_plugins[] = array('name' => "FHHIOpac", 'link' => 'http://hidbs2.bib.uni-hildesheim.de:8080/DB=2/SET=1/TTL=1/CMD?ACT=SRCHA&IKT=12&SRT=YOP&TRM={accession_number}');

/**
*
*
* 
*
* @access	public	
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
**/
class StudipLitSearch {
	
	var $start_result;
	var $form_template;
	var $inner_form;
	var $outer_form;
	var $term_count;
	var $search_plugin;
	
	
	function StudipLitSearch(){
		global $sess, $_lit_search_plugins;
		
		if (!$sess->is_registered("_start_result")){
			$sess->register("_start_result");
		}
		$this->start_result =& $GLOBALS['_start_result'];
		
		$this->form_template = array('search_term'	=> 	array('type' => 'text', 'caption' => _("Suchbegriff"), 'info' => _("Ein Suchbegriff")),
									'search_field'	=> 	array('type' => 'select', 'caption' => _("Suchfeld"), 'info' => _("Mögliche Suchfelder"),
															'options_callback' => array(&$this,"getSearchFields")),
									'search_truncate'=>	array('type' => 'select', 'caption' => _("Trunkieren"), 
															'options' => array(array('name' => _("Nein"), "value" => 'none'),
																				array('name' => _("Rechts trunkieren"), "value" => 'right'),
																				array('name' => _("Links trunkieren"), "value" => 'left'))),
									'search_operator'=> array('type' => 'radio', 'options' => array(array('name' =>_("UND"),'value' => 'AND'),
																									array('name' =>_("ODER"),'value' => 'OR'),
																									array('name' =>_("NICHT"),'value' => 'NOT')), 
															'caption' => _("Verknüpfung") ,'separator' => "&nbsp;", 'default_value' => "AND")
									);
		$search_plugins = $this->getAvailablePlugins();
		$preferred_plugin = $this->getPreferredPlugin();
		$i = 0;
		if ($preferred_plugin && in_array($preferred_plugin, $search_plugins)){
			$search_plugin_options[] = array('name' => $preferred_plugin, 'value' => $i++);
		}
		foreach ($search_plugins as $plugin_name){
			if ($preferred_plugin != $plugin_name){
				$search_plugin_options[] = array('name' => $plugin_name, 'value' => $i++);
			} else {
				array_splice($search_plugins, $i-1,1);
				array_unshift($search_plugins,$plugin_name);
			}
		}
		$outer_form_fields = array('search_plugin' => array('type' => 'select', 'caption' => _("Welchen Katalog durchsuchen ?"),
															'options' => $search_plugin_options, 'default_value' => 0),
									'search_term_count' => array('type' => 'hidden', 'default_value' => 1)
									);
		$outer_form_buttons = array('search' => array('type' => 'suchen', 'info' => _("Suche starten")),
									'reset' => array('type' => 'zuruecksetzen', 'info' => _("Suche zurücksetzen")),
									'change' => array('type' => 'auswaehlen', 'info' => _("Anderen Katalog auswählen")),
									'search_add' => array('type' => 'hinzufuegen', 'info' => _("Suchfeld hinzufügen")),
									'search_sub' => array('type' => 'entfernen', 'info' => _("Suchfeld entfernen")));
		
		$this->outer_form =& new StudipForm($outer_form_fields,$outer_form_buttons,"lit_search");
		
		if ($this->outer_form->isClicked("search_add")){
			$this->outer_form->form_values['search_term_count'] = $this->outer_form->getFormFieldValue('search_term_count') + 1;
		}
		if ($this->outer_form->isClicked("search_sub") && $this->outer_form->getFormFieldValue('search_term_count') > 1){
			$this->outer_form->form_values['search_term_count']--;
		}
		$plugin_number = false;
		if ($this->outer_form->isClicked("reset") ||  $this->outer_form->isChanged("search_plugin")){
			$plugin_number = $this->outer_form->getFormFieldValue("search_plugin");
			$this->outer_form->doFormReset();
			$this->outer_form->form_values["search_plugin"] = $plugin_number;
		}
		
		$this->term_count = $this->outer_form->getFormFieldValue('search_term_count');
		for ($i = 0 ; $i < $this->term_count; ++$i){
			foreach($this->form_template as $name => $value){
				$inner_form_fields[$name . "_" . $i] = $value;
			}
		}
		$this->inner_form =& new StudipForm($inner_form_fields, null, "lit_search");
		if ($plugin_number !== false){
			$this->inner_form->doFormReset();
			$this->outer_form->form_values["search_plugin"] = $plugin_number;
		}
		if ( ($plugin_name = $search_plugins[$this->outer_form->getFormFieldValue("search_plugin")]) ){
			$plugin_name = "StudipLitSearchPlugin" . $plugin_name;
			include_once $GLOBALS['ABSOLUTE_PATH_STUDIP']. "lib/classes/lit_search_plugins/" . $plugin_name .".class.php";
			$this->search_plugin =& new $plugin_name();
		}
		if ($plugin_number !== false){
			$this->search_plugin->doResetSearch();
			$this->start_result = 1;
		}
		
		$this->outer_form->form_fields['search_plugin']['info'] = $this->search_plugin->description;
	}
	
	function getSearchFields(&$caller, $name){
		return $this->search_plugin->getSearchFields();
	}
	
	function doSearch(){
		return $this->search_plugin->doSearch($this->getSearchValues());
	}
	
	function getNumHits(){
		return $this->search_plugin->getNumHits();
	}
	
	function getSearchResult($num_hit){
		return $this->search_plugin->getSearchResult($num_hit);
	}
	
	function getSearchValues(){
		$search_values = null;
		for ($i = 0 ; $i < $this->term_count; ++$i){
			foreach($this->form_template as $name => $value){
				$search_values[$i][$name] = $this->inner_form->getFormFieldValue($name . "_" . $i);
			}
		}
		return $search_values;
	}
	
	function GetPreferredPlugin(){
		$dbv = new DbView();
		$dbv->params[0] = $GLOBALS['user']->id;
		$rs = $dbv->get_query("view:LIT_GET_FAK_LIT_PLUGIN");
		$rs->next_record();
		return $rs->f('lit_plugin_name');
	}
	
	function GetAvailablePlugins(){
		global $_lit_search_plugins;
		for ($i = 0; $i < count($_lit_search_plugins); ++$i){
			$ret[] = $_lit_search_plugins[$i]['name'];
		}
		return $ret;
	}
	
	function GetExternalLink($plugin_name){
		global $_lit_search_plugins;
		$ret = "";
		for ($i = 0; $i < count($_lit_search_plugins); ++$i){
			if ($_lit_search_plugins[$i]['name'] == $plugin_name){
				$ret = $_lit_search_plugins[$i]['link'];
				break;
			}
		}
		return $ret;
	}
	
	function CheckZ3950($accession_number, $one_plugin_name = false){
		global $_lit_search_plugins, $ABSOLUTE_PATH_STUDIP;
		static $plugin_list;
		if (!is_array($plugin_list)){
			foreach ($_lit_search_plugins as $plugin){
				if ( $plugin['name'] != 'Studip' && ($one_plugin_name === false || $plugin['name'] == $one_plugin_name) ){
					$plugin_name = "StudipLitSearchPlugin" . $plugin['name'];
					include_once("$ABSOLUTE_PATH_STUDIP/lib/classes/lit_search_plugins/{$plugin_name}.class.php");
					$plugin_list[$plugin['name']] =& new $plugin_name();
				}
			}
		}
		foreach($plugin_list as $plugin_name => $plugin_obj){
			$found = $plugin_obj->doCheckAccession($accession_number);
			$error = $plugin_obj->getError();
			$ret[$plugin_name]['error'] = $error;
			$ret[$plugin_name]['found'] = $found;
		}
		return $ret;
	}
}

//test
/*
$_lit_search_plugins = array("Studip", "Gbv");
page_open(array("sess" => "Seminar_Session"));
$_language = $DEFAULT_LANGUAGE;
$_language_path = $INSTALLED_LANGUAGES[$_language]["path"];
$test =& new StudipLitSearch();

echo "<table width='500' border =1><tr><td>";
echo $test->outer_form->getFormStart();
echo $test->outer_form->getFormFieldCaption('search_plugin') . $test->outer_form->getFormField('search_plugin');
echo $test->outer_form->getFormButton('reset');
echo "&nbsp;";
echo $test->outer_form->getFormButton('search');

echo "</td></tr>";
for ($i = 0 ; $i < $test->term_count; ++$i){
	echo "<tr><td>";
	if ($i > 0){
		echo $test->inner_form->getFormFieldCaption("search_operator_" . $i);
		echo $test->inner_form->getFormField("search_operator_" . $i);
		echo "<br>";
	}
	echo $test->inner_form->getFormFieldCaption("search_field_" . $i);
	echo $test->inner_form->getFormField("search_field_" . $i);
	echo "<br>";
	echo $test->inner_form->getFormFieldCaption("search_truncate_" . $i);
	echo $test->inner_form->getFormField("search_truncate_" . $i);
	echo "<br>";
	echo $test->inner_form->getFormFieldCaption("search_term_" . $i);
	echo $test->inner_form->getFormField("search_term_" . $i);
	echo "</td></tr>";
}
echo "<tr><td>";
echo $test->outer_form->getFormButton('search_add');
echo "&nbsp;";
echo $test->outer_form->getFormButton('search_sub');
echo "</td></tr>";
echo "</table>";
echo $test->outer_form->getFormEnd();
echo "<pre>";
if ($test->outer_form->isClicked("search")){
	echo "Suchergebnis: " . $test->doSearch() ."<br>";

	for ($i = 1; $i<=$test->getNumHits();++$i){
		$result = $test->getSearchResult($i);
		print_r($result->fields);
	}
}
page_close();
*/
?>
