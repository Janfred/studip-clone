<?
/**
* ExternModulePersons.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModulePersons
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModulePersons.class.php
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
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "/lib/classes/DataFields.class.php");

class ExternModulePersons extends ExternModule {

	var $field_names = array();
	var $data_fields = array("Nachname", "Telefon", "raum", "Email", "sprechzeiten");
	var $registered_elements = array("Body", "TableHeader", "TableHeadrow", "TableGroup",
																	 "TableRow", "Link", "LinkIntern", "TableFooter");

	/**
	*
	*/
	function ExternModulePersons () {
		$this->field_names = array
		(
				_("Name"),
				_("Telefon"),
				_("Raum"),
				_("Email"),
				_("Sprechzeiten")
		);
	}
	
	function setup () {
		// extend $data_fields if generic datafields are set
		$config_datafields = $this->config->getValue("Main", "genericdatafields");
		$this->data_fields = array_merge($this->data_fields, $config_datafields);
		
		// setup module properties
		$this->elements["LinkIntern"]->link_module_type = 2;
		$this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
		$this->elements["Link"]->real_name = _("Email-Link");
	}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"),
					$this->config->getValue("Main", "copyright"),
					$this->config->getValue("Main", "author"));
		}
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/persons.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"),
					$this->config->getValue("Main", "copyright"),
					$this->config->getValue("Main", "author"));
		}
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/persons_preview.inc.php");
				
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?> 
