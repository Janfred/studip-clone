<?
/**
* ExternElementMainDownload.class.php
* 
* This class defines 
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
// ExternElementMainDownload.class.php
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

class ExternElementMainPersons extends ExternElementMain {

	var $attributes = array("name", "order", "visible", "aliases", "width",
			"width_pp", "sort", "groups", "groupsalias", "groupsvisible", "wholesite",
			"nametitle", "urlcss", "title", "bodystyle", "bodyclass", "nodatatext");
	var $edit_function = "editMainSettings";
	
	/**
	* Constructor
	*
	*/
	function ExternElementMainPersons () {
		$this->real_name = _("Grundeinstellungen");
		$this->description = _("In den Grundeinstellungen k&ouml;nnen Sie allgemeine Daten des Moduls &auml;ndern.");
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		if ($groups = get_all_statusgruppen($this->config->range_id))
			$groups = "|" . implode("|", array_keys($groups));
		else
			$groups = "";
		
		$config = array(
			"name" => "",
			"order" => "|0|1|2|3|4",
			"visible" => "|1|1|1|1|1",
			"aliases" => "|Name|Telefon|Raum|Email|Sprechzeiten",
			"width" => "|1%|20%|25%|15%|15%",
			"widthpp" => "",
			"sort" => "|0|0|0|1|0",
			"groups" => $groups,
			"groupsalias" => "",
			"groupsvisible" => $groups,
			"wholesite" => "",
			"nametitle" => "no_title",
			"urlcss" => "",
			"title" => "Personal",
			"bodystyle" => "",
			"bodyclass" => "",
			"nodatatext" => ""
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
		
		$headline = $edit_form->editHeadline(_("Name der Konfiguration"));
		$table = $edit_form->editName("name");
		
		$content_table = $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Allgemeine Angaben zum Tabellenaufbau"));
		
		$edit_function = $this->edit_function;
		$table = $edit_form->$edit_function($this->field_names, array());
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$table = $edit_form->editGroups();
		// show nothing if there are no groups
		if ($table) {
			$headline = $edit_form->editHeadline(_("Anzeige von Gruppen"));
		
			$content_table .= $edit_form->editContentTable($headline, $table);
			$content_table .= $edit_form->editBlankContent();
		}
		
		$headline = $edit_form->editHeadline(_("Weitere Angaben"));
		
		$title = _("Personennamen:");
		$info = _("Wählen Sie, wie Personennamen formatiert werden sollen.");
		$nametitle_values = array("no_title", "no_title_rev", "full", "full_rev");
		$nametitle_names = array(_("Vorname Nachname"), _("Nachname Vorname"),
				_("Titel Vorname Nachname"), _("Nachname Vorname Titel"));
		$table = $edit_form->editOptionGeneric("nametitle", $title, $info, $nametitle_values, $nametitle_names);
		
		$title = _("HTML-Header/Footer:");
		$info = _("Anwählen, wenn die Seite als komplette HTML-Seite ausgegeben werden soll, z.B. bei direkter Verlinkung oder in einem Frameset.");
		$wholesite_values = "1";
		$wholesite_names = "";
		$table .= $edit_form->editCheckboxGeneric("wholesite", $title, $info, $wholesite_values, $wholesite_names);
		
		$title = _("Stylesheet-Datei:");
		$info = _("Geben Sie hier die URL Ihrer Stylesheet-Datei an.");
		$table .= $edit_form->editTextfieldGeneric("urlcss", $title, $info, 50, 200);
		
		$title = _("Seitentitel:");
		$info = _("Geben Sie hier den Titel der Seite ein. Der Titel wird bei der Anzeige im Web-Browser in der Titelzeile des Anzeigefensters angezeigt.");
		$table .= $edit_form->editTextfieldGeneric("title", $title, $info, 50, 200);
		
		$title = _("Style f&uuml;r &lt;body&gt;:");
		$info = _("Sie können hier eine Stylesheet-Definition für das <body>-Tag eingeben.");
		$table .= $edit_form->editTextfieldGeneric("bodystyle", $title, $info, 50, 250);
		
		$title = _("Class f&uuml;r &lt;body&gt;:");
		$info = _("Sie können hier eine Stylesheet-Klasse für das <body>-Tag eingeben.");
		$table .= $edit_form->editTextfieldGeneric("bodyclass", $title, $info, 30, 128);
		
		$title = _("Keine Dateien:");
		$info = _("Dieser Text wird an Stelle der Tabelle ausgegeben, wenn keine Dateien zum Download verfügbar sind.");
		$table .= $edit_form->editTextareaGeneric("nodatatext", $title, $info, 3, 50);
		
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
