<?
/**
* ExternElementContact.class.php
* 
*  
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementContact
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementContact.class.php
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

class ExternElementContact extends ExternElement {

	var $attributes = array("order", "visible", "aliases", "headline", "adradd", "table_width",
				"table_align", "table_border", "table_bgcolor", "table_bordercolor", "table_cellpadding",
				"table_cellspacing", "table_class", "table_style", "tr_height", "tr_class",
				"tr_style", "td_align", "td_valign", "td_bgcolor", "td_class", "td_style",
				"fonttitle_face", "fonttitle_size", "fonttitle_color", "fonttitle_class",
				"fonttitle_style", "fontcontent_face", "fontcontent_size", "fontcontent_color",
				"fontcontent_class", "fontcontent_style");
	
	/**
	* Constructor
	*
	*/
	function ExternElementContact () {
		$this->name = "Contact";
		$this->real_name = _("Name, Anschrift, Kontakt");
		$this->description = _("Allgemeine Angaben zum und Formatierung des Kontaktfeldes (Anschrift, Email, Homepage usw.).");
	}
	
	function getDefaultConfig () {
		
		$config = array(
			"order" => "|0|1|2|3|4|5",
			"visible" => "|1|1|1|1|1|1",
			"aliases" => "|"._("Raum:")."|"._("Telefon:")."|"._("Fax:")."|"._("Email:")."|"
					._("Homepage:")."|"._("Sprechzeiten:"),
			"headline" => _("Kontakt:"),
			"adrradd" => _("der Georg-August-Universit&auml;t G&ouml;ttingen")
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
		
		$headline = $edit_form->editHeadline(_("Aufbau der Adress- und Kontakt-Tabelle"));
		$field_names = array(_("Raum"), _("Telefon"), _("Fax"), _("Email"), _("Homepage"), _("Sprechzeiten"));
		$table = $edit_form->editMainSettings($field_names, "", array("width", "sort", "widthpp"));
		
		$title = _("&Uuml;berschrift");
		$info = _("Überschrift der Kontakt-Daten");
		$table .= $edit_form->editTextfieldGeneric("headline", $title, $info, 35, 100);
		
		$title = _("Adresszusatz:");
		$info = _("Zusatz zur Adresse der Einrichtung, z.B. Universitätsname.");
		$table .= $edit_form->editTextfieldGeneric("adradd", $title, $info, 35, 100);
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$attributes = array("table_width", "table_align",
				"table_border", "table_bgcolor", "table_bordercolor", "table_cellpadding",
				"table_cellspacing", "table_class", "table_style", "tr_height", "tr_class",
				"tr_style", "td_align", "td_valign", "td_bgcolor", "td_class", "td_style");
		$content_table .= $edit_form->getEditFormContent($attributes);
		$content_table .= $edit_form->editBlankContent();
		
		$attributes = array("fonttitle_face", "fonttitle_size", "fonttitle_color", "fonttitle_class",
				"fonttitle_style", "fontcontent_face", "fontcontent_size", "fontcontent_color",
				"fontcontent_class", "fontcontent_style");
		$headlines = array("fonttitle" => _("Schriftformatierung Spaltentitel"),
				"fontcontent" => _("Schriftformatierung Spalteninhalt"));
		$content_table .= $edit_form->getEditFormContent($attributes, $headlines);
		$content_table .= $edit_form->editBlankContent();
				
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return $element_headline . $out;
	}
	
}

?>
