<?
/**
* ExternModuleLecturedetails.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleLecturedetails
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleLecturedetails.class.php
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

class ExternModuleLecturedetails extends ExternModule {

	var $field_names = array();
	var $data_fields = array("subtitle", "lecturer", "art", "status", "description", "location",
			"time", "teilnehmer", "requirements", "lernorga", "leistung", "range_path", "misc");
	var $registered_elements = array(
			"Body",
			"TableHeader",
			"SemName" => "TableParagraphText",
			"Headline" => "TableParagraphText",
			"Content" => "TableParagraphText",
			"LinkInternSimple",
			"StudipInfo",
			"StudipLink");
	var $args = array("seminar_id");

	/**
	*
	*/
	function ExternModuleLecturedetails () {
		$this->field_names = array(
				_("Untertitel:"),
				_("DozentIn:"),
				_("Veranstaltungsart:"),
				_("Veranstaltungstyp:"),
				_("Beschreibung:"),
				_("Ort:"),
				_("Zeiten:"),
				_("Teilnehmer:"),
				_("Voraussetzungen:"),
				_("Lernorganisation:"),
				_("Leistungsnachweis:"),
				_("Bereichseinordnung:"),
				_("Sonstiges:"));
	}
	
	function setup () {
		$this->elements["SemName"]->real_name = _("Name der Veranstaltung");
		$this->elements["Headline"]->real_name = _("&Uuml;berschriften");
		$this->elements["Content"]->real_name = _("Abs&auml;tze");
		$this->elements["LinkInternSimple"]->link_module_type = 6;
		$this->elements["LinkInternSimple"]->real_name = _("Link zum Modul Mitarbeiterdetails");
	}
	
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
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/lecturedetails.inc.php");
		
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
		
		include($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
				. "/modules/views/lecturedetails_preview.inc.php");
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
}
?>
