<?
/**
* ExternModuleDownload.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleDownload
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleDownload.class.php
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

class ExternModuleDownload extends ExternModule {

	var $field_names = array();
	var $data_fields = array("icon", "filename", "description", "mkdate", "filesize", "fullname");
	var $registered_elements = array("Body", "TableHeader", "TableHeadrow",
																	 "TableRow", "Link", "LinkIntern", "TableFooter");

	/**
	*
	*/
	function ExternModuleDownload () {
		$this->field_names = array
		(
				_("Icon"),
				_("Dateiname"),
				_("Beschreibung"),
				_("Datum"),
				_("Gr&ouml;&szlig;e"),
				_("Upload durch")
		);
		
	}
	
	function setup () {
		$this->elements["LinkIntern"]->link_module_type = 6;
		$this->elements["LinkIntern"]->real_name = _("Link zum Modul Mitarbeiterdetails");
		$this->elements["Link"]->real_name = _("Link zum Dateidownload");
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
					$this->config->getAttributes("Body", "body"));
		}
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/download.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		if ($this->config->getValue("Main", "wholesite")) {
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"));
		}
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/download_preview.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?> 
