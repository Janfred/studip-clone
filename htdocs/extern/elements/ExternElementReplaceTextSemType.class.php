<?
/**
* ExternElementReplaceTextSemType.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElementReplaceTextSemType
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElementReplaceTextSemType.class.php
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

class ExternElementReplaceTextSemType extends ExternElement {

	var $attributes = array();

	/**
	* Constructor
	*
	* @param array config
	*/
	function ExternElementReplaceTextSemType ($config = "") {
		if ($config)
			$this->config = $config;
		
		$this->name = "ReplaceTextSemType";
		$this->real_name = _("Textersetzungen f&uuml;r Veranstaltungstypen");
		$this->description = _("Ersetzt die Bezeichnung der Veranstaltungstypen.");
		for ($i = 1; $i <= sizeof($GLOBALS["SEM_CLASS"]); $i++)
			$this->attributes[] = "class_" . $i;
		$this->attributes[] = "order";
	}
	
	/**
	* 
	*/
	function getDefaultConfig () {
		global $SEM_TYPE, $SEM_CLASS;
		$config = array();
		foreach ($SEM_CLASS as $class_index => $class) {
			foreach ($SEM_TYPE as $type_index => $type) {
				if ($type["class"] == $class_index) {
					$config["class_$class_index"] .= "|" . htmlReady($type["name"])
							. " ({$class['name']})";
				}
			}
		}
		
		foreach ($SEM_TYPE as $type_index => $foo)
			$config["order"] .= "|$type_index";
		
		return $config;
	}
	
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
		
		global $SEM_TYPE;
		
		$order = $this->config->getValue($this->name, "order");
		if (!is_array($order) || array_diff(array_keys($SEM_TYPE), $order)) {
			$this->config->setValue($this->name, "order", array_keys($SEM_TYPE));
			$this->config->store();
		}
					
		if ($faulty_values == '')
			$faulty_values = array();	
		$out = '';
		$table = '';
		if ($edit_form == '')
			$edit_form =& new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $this->getEditFormHeadline($edit_form);
		
		$table = $edit_form->editSemTypes();
		
		$content_table .= $edit_form->editContentTable($headline, $table);
		$content_table .= $edit_form->editBlankContent();
		
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($content_table, $submit);
		$out .= $edit_form->editBlank();
		
		return  $element_headline . $out;
	}
	
}

?>
