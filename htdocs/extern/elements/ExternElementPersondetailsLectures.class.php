<?
/**
* ExternElementPersondetailsLectures.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementPersondetailsLectures
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementPersondetailsLectures.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternElement.class.php");

class ExternElementPersondetailsLectures extends ExternElement {

	var $attributes = array("semrange", "aliaswise", "aliassose", "aslist");
	
	/**
	* Constructor
	*
	* @param array config
	*/
	function ExternElementPersondetailsLectures ($config = "") {
		if ($config)
			$this->config = $config;
		
		$this->name = "PersondetailsLectures";
		$this->real_name = _("Lehrveranstaltungen");
		$this->description = _("Angaben zur Ausgabe von Lehrveranstaltungen.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		
		$config = array(
			"semrange" => "three",
			"aliaswise" => _("Wintersemester"),
			"aliassose" => _("Sommersemester"),
			"aslist" => "1"
		);
		
		return $config;
	}
	
	/**
	* 
	*/
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
		
		$out = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE, $anker);
		
		$headline = $edit_form->editHeadline(_("Allgemeine Angaben"));
		
		$title = _("Semesterumfang:");
		$info = _("Geben Sie an, aus welchen Semestern Lehrveranstaltungen angezeigt werden sollen.");
		$names = array(_("nur aktuelles"), _("vorheriges, aktuelles, n&auml;chstes"), _("alle"));
		$values = array("current", "three", "all");
		$table = $edit_form->editRadioGeneric("semrange", $title, $info, $values, $names);
		
		$title = _("Bezeichnung Sommersemester:");
		$info = _("Alternative Bezeichnung für den Begriff \"Sommersemester\".");
		$table .= $edit_form->editTextfieldGeneric("aliassose", $title, $info, 40, 80);
		
		$title = _("Bezeichnung Wintersemester:");
		$info = _("Alternative Bezeichnung für den Begriff \"Wintersemester\".");
		$table .= $edit_form->editTextfieldGeneric("aliaswise", $title, $info, 40, 80);
		
		$title = _("Darstellungsart:");
		$info = _("Wählen Sie zwischen Listendarstellung und reiner Textdarstellung.");
		$names = array(_("Liste"), _("nur Text"));
		$values = array("1", "0");
		$table .= $edit_form->editRadioGeneric("aslist", $title, $info, $values, $names);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return $element_headline . $out;
	}
	
}

?>
