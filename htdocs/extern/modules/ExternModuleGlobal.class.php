<?
/**
* ExternModuleGlobal.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleGlobal
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

class ExternModuleGlobal extends ExternModule {

	var $field_names = array();
	var $data_fields = array();
	var $registered_elements = array
			(
				"PageBodyGlobal" => "Body",
				"MainTableHeaderGlobal" => "TableHeader",
				"InnerTableHeaderGlobal" => "TableHeader",
				"MainTableHeadrowGlobal" => "TableHeadrow",
				"TableGrouprowGlobal" => "TableGroup",
				"TableRowGlobal" => "TableRow",
				"TableHeadrowTextGlobal" => "Link",
				"Headline1TextGlobal" => "Link",
				"Headline2TextGlobal" => "Link",
				"TextGlobal" => "Link",
				"LinksGlobal" => "Link"
			);

	/**
	*
	*/
	function ExternModuleGlobal () {}
	
	function setup () {
		$this->elements["PageBodyGlobal"]->real_name = _("Seitenk&ouml;rper");
		$this->elements["MainTableHeaderGlobal"]->real_name = _("Tabellenkopf Gesamttabelle");
		$this->elements["InnerTableHeaderGlobal"]->real_name = _("Tabellenkopf innere Tabelle");
		$this->elements["MainTableHeadrowGlobal"]->real_name = _("Kopfzeile");
		$this->elements["TableGrouprowGlobal"]->real_name = _("Gruppenzeile");
		$this->elements["TableRowGlobal"]->real_name = _("Datenzeile");
		$this->elements["TableHeadrowTextGlobal"]->real_name = _("Text in Tabellenkopf");
		$this->elements["Headline1TextGlobal"]->real_name = _("&Uuml;berschriften erster Ordnung");
		$this->elements["Headline2TextGlobal"]->real_name = _("&Uuml;berschriften zweiter Ordnung");
		$this->elements["TextGlobal"]->real_name = _("Schrift");
		$this->elements["LinksGlobal"]->real_name = _("Links");
		
		$this->elements["MainTableHeadrowGlobal"]->attributes = array("tr_class", "tr_style",
				"th_height", "th_align", "th_valign", "th_bgcolor", "th_bgcolor2_",
				"th_zebrath_", "th_class", "th_style");
		$this->elements["TableGrouprowGlobal"]->attributes = array("tr_class", "tr_style",
				"td_height", "td_align", "td_valign", "td_bgcolor", "td_bgcolor_2", "td_class",
				"td_style");
		$this->elements["TableRowGlobal"]->attributes = array("tr_class", "tr_style",
				"td_height", "td_align", "td_valign", "td_bgcolor", "td_bgcolor2_",
				"td_zebratd_", "td_class", "td_style");
		$this->elements["TableHeadrowTextGlobal"]->attributes = array("font_size", "font_face",
				"font_color", "font_class", "font_style");
		$this->elements["Headline1TextGlobal"]->attributes = array("font_size", "font_face",
				"font_color", "font_class", "font_style");
		$this->elements["Headline2TextGlobal"]->attributes = array("font_size", "font_face",
				"font_color", "font_class", "font_style");
		$this->elements["TextGlobal"]->attributes = array("font_size", "font_face",
				"font_color", "font_class", "font_style");
		
		$this->globalConfigMapping();
	}
	
	function restoreRegisteredElements () {
		$element_names = array
			(
				"Main",
				"PageBodyGlobal",
				"MainTableHeaderGlobal",
				"InnerTableHeaderGlobal",
				"MainTableHeadrowGlobal",
				"TableGrouprowGlobal",
				"TableRowGlobal",
				"TableHeadrowTextGlobal",
				"Headline1TextGlobal",
				"Headline2TextGlobal",
				"TextGlobal",
				"LinksGlobal"
			);
		foreach ($element_names as $element_name) {
			$registered_elements[$element_name] = $this->registered_elements[$element_name];
			$elements[$element_name] = $this->elements[$element_name];
		}
		
		$this->registered_elements = $registered_elements;
		$this->elements = $elements;
	}
	
	/**
	*
	*/
	function toStringEdit ($open_elements = "", $post_vars = "",
			$faulty_values = "", $anker = "") {
		
		$this->restoreRegisteredElements();
		return parent::toStringEdit ($open_elements, $post_vars, $faulty_values, $anker);
	}
		
	
	/**
	*
	*/
	function store ($element_name = "", $values = "") {
		$this->globalConfigMapping($element_name, $values);
		parent::store($element_name, $values);
	}
	
	function globalConfigMapping ($element_name = "", $values = "") {
		$elements_map["Body"][] = $this->elements["PageBodyGlobal"];
		$elements_map["TableHeader"][] = $this->elements["MainTableHeaderGlobal"];
		
		$elements_map["TableHeadrow"][] = $this->elements["MainTableHeadrowGlobal"];
		$elements_map["TableHeadrow"][] = $this->elements["TableHeadrowTextGlobal"];
		
		$elements_map["TableRow"][] = $this->elements["TableRowGlobal"];
		$elements_map["TableRow"][] = $this->elements["TextGlobal"];
		
		$elements_map["Grouping"][] = $this->elements["TableGrouprowGlobal"];
		$elements_map["Grouping"][] = $this->elements["Headline2TextGlobal"];
		
		$elements_map["Link"][] = $this->elements["LinksGlobal"];
		$elements_map["LinkIntern"][] = $this->elements["LinksGlobal"];
		$elements_map["LinkInternSimple"][] = $this->elements["LinksGlobal"];
		$elements_map["LecturerLink"][] = $this->elements["LinksGlobal"];
		$elements_map["SemName"][] = $this->elements["Headline1TextGlobal"];
		$elements_map["Headline"][] = $this->elements["Headline2TextGlobal"];
		$elements_map["Content"][] = $this->elements["TextGlobal"];
		$elements_map["StudipLink"][] = $this->elements["LinksGlobal"];
		$elements_map["SemLink"][] = $this->elements["LinksGlobal"];
		
		$elements_map["Contact"][] = $this->elements["InnerTableHeaderGlobal"];
	/*	$elements_map["Contact"]->attributes["fonttitle_face"] =
				$this->elements["TextGlobal"]->attributes["font_face"];
		$elements_map["Contact"]->attributes["fonttitle_size"] =
				$this->elements["TextGlobal"]->attributes["font_size"];
		$elements_map["Contact"]->attributes["fonttitle_color"] =
				$this->elements["TextGlobal"]->attributes["font_color"];
		$elements_map["Contact"]->attributes["fonttitle_class"] =
				$this->elements["TextGlobal"]->attributes["font_class"];
		$elements_map["Contact"]->attributes["fonttitle_style"] =
				$this->elements["TextGlobal"]->attributes["font_style"];
		$elements_map["Contact"]->attributes["fontcontent_face"] =
				$this->elements["TextGlobal"]->attributes["font_face"];
		$elements_map["Contact"]->attributes["fontcontent_size"] =
				$this->elements["TextGlobal"]->attributes["font_size"];
		$elements_map["Contact"]->attributes["fontcontent_color"] =
				$this->elements["TextGlobal"]->attributes["font_color"];
		$elements_map["Contact"]->attributes["fontcontent_class"] =
				$this->elements["TextGlobal"]->attributes["font_class"];
		$elements_map["Contact"]->attributes["fontcontent_style"] =
				$this->elements["TextGlobal"]->attributes["font_style"];*/
		
		$elements_map["TableParagraph"][] = $this->elements["MainTableHeaderGlobal"];
		
		$elements_map["TableParagraphHeadline"][] = $this->elements["TableRowGlobal"];
		$elements_map["TableParagraphHeadline"][] = $this->elements["Headline2TextGlobal"];
		
		$elements_map["TableParagraphSubHeadline"][] = $this->elements["TableRowGlobal"];
		$elements_map["TableParagraphSubHeadline"][] = $this->elements["TableHeadrowTextGlobal"];
		
		$elements_map["TableParagraphText"][] = $this->elements["TableRowGlobal"];
		$elements_map["TableParagraphText"][] = $this->elements["TextGlobal"];
	
		foreach ($elements_map as $name => $elements) {
			foreach ($elements as $element) {
				foreach ($element->attributes as $attribute) {
					if ($element->name == $element_name)
						$this->config->config[$name][$attribute] = $values[$element->name . "_" . $attribute];
					else
						$this->config->config[$name][$attribute] = $this->config->config[$element->name][$attribute];
					$this->elements[$name]->attribute[] = $attribute;
				}
				$element->name = $name;
				$this->elements[$name] = $element;
				$this->registered_elements[] = $name;
			}
		}
		
	}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {}
	
	function printoutPreview () {}
	
}
?> 
