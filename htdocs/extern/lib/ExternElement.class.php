<?
/**
* ExternElement.class.php
* 
* This is an abstract class that define an interface to every so called HTML-element
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternElement
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElement.class.php
// This is an abstract class that define an interface to every so called HTML-element
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


class ExternElement {

	var $config;
	var $name;
	var $attributes;
	var $real_name;
	var $description;

	/**
	* Constructor
	*
	* @param array config
	* @param string element_name
	*/
	function ExternElement (&$config, $element_name) {
		$class_name = "ExternElement" . $element_name;
		require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/elements/$class_name.class.php");
		$this = new $class_name();
		$this->config = $config;
	}

	/**
	*
	*/
	function getType () {
		return $this->type;
	}
	
	/**
	*
	*/
	function getName () {
		return $this->name;
	}
	
	/**
	*
	*/
	function getRealname () {
		return $this->real_name;
	}
	
	/**
	*
	*/
	function getDefaultConfig () {
		$config = array();
		
		reset($this->attributes);
		foreach ($this->attributes as $attribute)
			$config[$attribute] = "";
		
		return $config;
	}
			
	/**
	*
	*/
	function isEditable () {
		if (sizeof($this->attributes))
			return TRUE;
		
		return FALSE;
	}
	
	/**
	*
	*/
	function getValue ($attribute) {
		return $this->config->getValue($this->type, $attribute);
	}

	/**
	* 
	*/
	function toString ($data = "") {
		$template_function = $this->name;
		
		if ($data == "")
			return $template_function(&$this);
			
		return $template_function(&$this, $data);
	}

	/**
	* 
	*/
	function printout () {
		echo $this->toString();
	}

	/**
	* 
	*/
	function toStringEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
			
		$out = "";
		$tag_headline = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE, $anker);
		
		if ($faulty_values = "")
			$faulty_values = array();
		
		$previous_tag = "";
		
		reset($this->attributes);
		foreach ($this->attributes as $attribute) {
			$attribute_part = explode("_", $attribute);
			if (!$attribute_part[2]) {
				$edit_function = "edit" . $attribute_part[1];
			
				if ($attribute_part[0] != $previous_tag) {
					if ($previous_tag != "") {
						$out .= $edit_form->editContentTable($tag_headline, $table);
						$out .= $edit_form->editBlankContent();
						$tag_headline = $edit_form->editTagHeadline($attribute_part[0]);
						$table = "";
					}
					else
						$tag_headline = $edit_form->editTagHeadline($attribute_part[0]);
					
					$previous_tag = $attribute_part[0];
				}
				$table .= $edit_form->$edit_function($attribute);
			}
		}

		$out .= $edit_form->editContentTable($tag_headline, $table);
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($out, $submit);
		$out .= $edit_form->editBlank();
		return $element_headline . $out;
	}

	/**
	* 
	*/
	function printoutEdit ($post_vars = "", $faulty_values = "",
			$edit_form = "", $anker = "") {
			
		echo $this->toStringEdit($post_vars, $faulty_values, $edit_form, $anker);
	}
	
	/**
	* 
	*/
	function getAttributes ($short = TRUE) {
		if ($short)
			return $this->attributes;
		
		reset($this->attributes);
		foreach($this->attributes as $attribute)
			$attributes_long[] = $this->name . "_" . $attribute;
		
		return $attributes_long;
	}
	
	/**
	* 
	*/
	function getDescription () {
		return $this->description;
	}

}
