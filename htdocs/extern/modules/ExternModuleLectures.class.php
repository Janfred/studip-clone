<?
/**
* ExternModuleLectures.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleLectures
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLectures.class.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternModule.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/views/extern_html_templates.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/modules/views/ExternSemBrowse.class.php");

class ExternModuleLectures extends ExternModule {

	var $field_names = array();
	var $data_fields = array();
	var $registered_elements = array("Body", "TableHeader", "TableGroup", "TableFooter");
	var $args = array();

	/**
	*
	*/
	function ExternModuleLectures () {}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {
		global $ABSOLUTE_PATH_STUDIP;
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		}
		
		$start_item_id = get_start_item_id($this->config->range_id);
		$sem_browse_data = array("start_item_id" => $start_item_id, "level" => "ev",
		"cmd" => "qs", "show_class" => "all", "group_by" => 1, "default_sem" => "all",
		"search_result" => Array(), "show_entries" => "level", "sem_status" => "", "sset" => "");
		$browser =& new ExternSemBrowse($this->config, $sem_browse_data);
	//	$browser->do_output();
		$browser->print_result();
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview ($args) {
		global $ABSOLUTE_PATH_STUDIP;
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		}
		
		$sem_browse_data = array("start_item_id" => $this->config->range_id, "level" => "ev",
		"cmd" => "qs", "show_class" => "all", "group_by" => 0, "default_sem" => "all",
		"search_result" => Array(), "show_entries" => "level", "sem_status" => "", "sset" => "");
		$browser =& new ExternSemBrowse($this->config, $sem_browse_data);
		$browser->print_result();
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?>
