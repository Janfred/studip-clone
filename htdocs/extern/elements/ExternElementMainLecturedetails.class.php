<?
/**
* ExternElementMainLecturedetails.class.php
* 
*  
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementMainDownload
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementMainLecturedetails.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternElementMain.class.php");

class ExternElementMainLecturedetails extends ExternElementMain {

	var $attributes = array("name", "order", "visible", "aliases", "range", "studipinfo",
			"studiplink", "wholesite", "nameformat", "urlcss", "title", "language");
	var $edit_function = "editMainSettings";
	
	/**
	* Constructor
	*
	*/
	function ExternElementMainLecturedetails () {
		$this->real_name = _("Grundeinstellungen");
		$this->description = _("In den Grundeinstellungen k&ouml;nnen Sie allgemeine Daten des Moduls &auml;ndern.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		$config = array(
			"name" => "",
			"order" => "|0|1|2|3|4|5|6|7|8|9|10|11|12",
			"visible" => "|1|1|1|1|1|1|1|1|1|1|1|1|1",
			"aliases" => "|"._("Untertitel:")."|"._("DozentIn:")."|"._("Veranstaltungsart:")
				."|"._("Veranstaltungstyp:")."|"._("Beschreibung:")."|"._("Ort:")."|"._("Zeiten:")
				."|"._("Teilnehmer:")."|"._("Voraussetzungen:")."|"._("Lernorganisation:")
				."|"._("Leistungsnachweis:")."|"._("Bereichseinordnung:")."|"._("Sonstiges:"),
			"range" => "long",
			"studipinfo" => "1",
			"studiplink" => "1",
			"wholesite" => "",
			"nameformat" => "no_title",
			"urlcss" => "",
			"title" => "",
			"language" => "de_DE"
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
		
		if ($faulty_values = "")
			$faulty_values = array();
		
		$headline = $edit_form->editHeadline(_("Name der Konfiguration"));
		$table = $edit_form->editName("name");
		$content_table = $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));
		
		$edit_function = $this->edit_function;
		$table = $edit_form->$edit_function($this->field_names, "", array("sort", "width", "widthpp"));
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Weitere Angaben"));
		
		$title = _("Bereichseinordnung:");
		$info = _("Wählen Sie, ob die Bereichseinordnung mit übergeordneten Bereichen angezeigt werden soll. Die Option \"kurz\" gibt nur den untersten Bereich aus.");
		$values = array("long", "short");
		$names = array(_("vollst&auml;ndig"), _("kurz"));
		$table = $edit_form->editRadioGeneric("range", $title, $info, $values, $names);
		
		$title = _("Stud.IP-Info:");
		$info = _("Diese Option zeigt weitere Informationen aus der Stud.IP-Datenbank an (Anzahl Teilnehmer, Posting, Dokumente usw.).");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("studipinfo", $title, $info, $values, $names);
		
		$title = _("Stud.IP-Link:");
		$info = _("Anwählen, wenn ein Link angezeigt werden soll, der direkt zum Stud.IP-Administrationsbereich verweisen soll.");
		$value = "1";
		$table .= $edit_form->editCheckboxGeneric("studiplink", $title, $info, $value, "");
		
		$title = _("HTML-Header/Footer:");
		$info = _("Anwählen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
		$values = "1";
		$names = "";
		$table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $values, $names);
		
		$title = _("Namensformat:");
		$info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
		$values = array("no_title_short", "no_title", "no_title_rev", "full", "full_rev");
		$names = array(_("Meyer, P."), _("Peter Meyer"), _("Meyer Peter"),
				_("Dr. Peter Meyer"), _("Meyer, Peter, Dr."));
		$table .= $edit_form->editOptionGeneric("nameformat", $title, $info, $values, $names);
		
		$title = _("Sprache");
		$info = _("Wählen Sie eine Sprache für die Datumsangaben aus.");
		$values = array("de_DE", "en_GB");
		$names = array(_("Deutsch"), _("Englisch"));
		$table .= $edit_form->editOptionGeneric("language", $title, $info, $values, $names);
		
		$title = _("Stylesheet-Datei:");
		$info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
		$table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);
		
		$title = _("Seitentitel:");
		$info = _("Geben Sie hier den Titel der Seite ein. Der Titel wird bei der Anzeige im Web-Browser in der Titelzeile des Anzeigefensters angezeigt.");
		$table .= $edit_form->editTextfieldGeneric("title", $title, $info, 50, 200);
		
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
