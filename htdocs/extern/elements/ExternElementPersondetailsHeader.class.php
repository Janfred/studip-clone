<?
/**
* ExternElementPersondetailsHeader.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementTableHeader
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementPersondetailsHeader.class.php
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

class ExternElementPersondetailsHeader extends ExternElement {

	var $attributes = array("table_width", "table_align", "table_border", "table_bgcolor",
				"table_bordercolor", "table_cellpadding", "table_cellspacing", "table_class",
				"table_style", "tr_height", "tr_class", "tr_style", "headlinetd_align",
				"headlinetd_valign", "headlinetd_bgcolor", "headlinetd_class", "headlinetd_style",
				"picturetd_width", "picturetd_align", "picturetd_valign", "picturetd_bgcolor",
				"picturetd_class", "picturetd_style", "contacttd_width", "contacttd_align",
				"contacttd_valign", "contacttd_bgcolor", "contacttd_class", "contacttd_style",
				"font_face", "font_size", "font_color", "font_class", "font_style");
	
	/**
	* Constructor
	*
	* @param array config
	*/
	function ExternElementPersondetailsHeader ($config = "") {
		if ($config)
			$this->config = $config;
		
		$this->name = "PersondetailsHeader";
		$this->real_name = _("Seitenkopf/Bild");
		$this->description = _("Angaben zur Gestaltung des Seitenkopfes.");
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
		
		$attributes = array("table_width", "table_align",
				"table_border", "table_bgcolor", "table_bordercolor", "table_cellpadding",
				"table_cellspacing", "table_class", "table_style");
		$content_table .= $edit_form->getEditFormContent($attributes);
		$content_table .= $edit_form->editBlankContent();
		
		$attributes = array("tr_height", "tr_class", "tr_style", "font_face", "font_size",
				"font_color", "font_class", "font_style", "headlinetd_align",
				"headlinetd_valign", "headlinetd_bgcolor", "headlinetd_class", "headlinetd_style",
				"picturetd_width", "picturetd_align", "picturetd_valign", "picturetd_bgcolor",
				"picturetd_class", "picturetd_style", "contacttd_width", "contacttd_align",
				"contacttd_valign", "contacttd_bgcolor", "contacttd_class", "contacttd_style",);
		$headlines = array("tr" => "Tabellenzeile Name", "font" => _("Schriftformatierung Name"),
				"headlinetd" => _("Tabellenzelle Name"), "picturetd" => _("Tabellenzelle Bild"),
				"contacttd" => _("Tabellenzelle Kontakt"));
		$content_table .= $edit_form->getEditFormContent($attributes, $headlines);
		$content_table .= $edit_form->editBlankContent();
		
		$headline = $edit_form->editHeadline(_("Bild"));
		
		$title = _("Ausrichtung:");
		$info = _("Ausrichtung des Bildes.");
		$names = array(_("zentriert"), _("linksb&uuml;ndig"), _("rechtsb&uuml;ndig"),
				_("obenb&uuml;ndig"), _("untenb&uuml;ndig"));
		$values = array("center", "left", "right", "top", "bottom");
		$table = $edit_form->editOptionGeneric("img_align", $title, $info, $values, $names);
		
		$title = _("Rahmenbreite:");
		$info = _("Breite des Bildrahmens.");
		$table .= $edit_form->editTextfieldGeneric("img_border", $title, $info, 3, 3);
		
		$title = _("Breite:");
		$info = _("Breite des Bildes.");
		$table .= $edit_form->editTextfieldGeneric("img_width", $title, $info, 3, 3);
		
		$title = _("Höhe:");
		$info = _("Breite des Bildes.");
		$table .= $edit_form->editTextfieldGeneric("img_height", $title, $info, 3, 3);
		
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
