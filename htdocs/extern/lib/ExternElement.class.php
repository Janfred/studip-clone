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
		require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]
				. $GLOBALS["RELATIVE_PATH_EXTERN"] . "/elements/$class_name.class.php");
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
		
		if ($faulty_values = "")
			$faulty_values = array();	
		$out = "";
		$tag_headline = "";
		$table = "";
		if ($edit_form == "")
			$edit_form =& new ExternEditHtml($this->config, $post_vars, $faulty_values, $anker);
		
		$edit_form->setElementName($this->getName());
		$element_headline = $this->getEditFormHeadline($edit_form);
		
		$out = $edit_form->getEditFormContent($this->attributes);
		
		$submit = $edit_form->editSubmit($this->config->getName(),
				$this->config->getId(), $this->getName());
		$out = $edit_form->editContent($out, $submit);
		$out .= $edit_form->editBlank();
		
		return  $element_headline . $out;
	}
	
	function getEditFormHeadline (&$edit_form) {
		$headline = $edit_form->editElementHeadline($this->real_name,
				$this->config->getName(), $this->config->getId(), TRUE);
		
		return $headline;
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
	
	/**
	* 
	*/
	function executeCommand ($command, $value = "") {
		switch ($command) {
			case "show" :
				$visible = $this->config->getValue($this->name, "visible");
				if ($value >= 0 || $value < sizeof($visible)) {
					$visible[$value] = "1";
					$this->config->setValue($this->name, "visible", $visible);
					$GLOBALS["HTTP_POST_VARS"]["{$this->name}_visible"] = $visible;
				}
				break;
				
			case "hide" :
				$visible = $this->config->getValue($this->name, "visible");
				
				if ($value >= 0 || $value < sizeof($visible)) {
					$visible[$value] = "0";
					$this->config->setValue($this->name, "visible", $visible);
					$GLOBALS["HTTP_POST_VARS"]["{$this->name}_visible"] = $visible;
				}
				break;
				
			case "move_left" :
				$order = $this->config->getValue($this->name, "order");
				if ($value >= 0 || $value < sizeof($order)) {
					$a = $order[$value];
					if (($value - 1) < 0) {
						$b = array_pop($order);
						array_push($order, $a);
						$order[0] = $b;
					}
					else {
						$b = $order[$value - 1];
						$order[$value - 1] = $a;
						$order[$value] = $b;
					}
					$this->config->setValue($this->name, "order", $order);
					$GLOBALS["HTTP_POST_VARS"]["{$this->name}_order"] = $order;
				}
				break;
				
			case "move_right" :
				$order = $this->config->getValue($this->name, "order");
				if ($value >= 0 || $value < sizeof($order)) {
					$a = $order[$value];
					if (($value + 1) >= sizeof($order)) {
						$b = $order[0];
						$order[0] = $a;
						$order[$value] = $b;
					}
					else {
						$b = $order[$value + 1];
						$order[$value + 1] = $a;
						$order[$value] = $b;
					}
					$this->config->setValue($this->name, "order", $order);
					$GLOBALS["HTTP_POST_VARS"]["{$this->name}_order"] = $order;
				}
				break;
			
			case "show_group" :
				$visible = $this->config->getValue($this->name, "groupsvisible");
				$groups = $this->config->getValue($this->name, "groups");
				// initialize groupsvisible if it isn't set in the config file
				// all groups are visible (1)
				if (!$visible && !$groups) {
					if($groups = get_all_statusgruppen($this->config->range_id))
						$groups = array_keys($groups);
					else
						break;
					global $HTTP_POST_VARS;
					$visible = $HTTP_POST_VARS["{$this->name}_groups"];
				}
				if (!$groups) {
					if($groups = get_all_statusgruppen($this->config->range_id))
						$groups = array_keys($groups);
					else
						break;
				}
				
				if (in_array($value, $groups)) {
					$visible[] = $value;
					$visible = array_unique($visible);
					$this->config->setValue($this->name, "groupsvisible", $visible);
					$GLOBALS["HTTP_POST_VARS"]["{$this->name}_groupsvisible"] = $visible;
				}
				
				break;
			
			case "hide_group" :
				$visible = $this->config->getValue($this->name, "groupsvisible");
				// initialize groupsvisible if it isn't set in the config file
				// all groups are visible (1)
				if (!$visible) {
					global $HTTP_POST_VARS;
					$visible = $HTTP_POST_VARS["{$this->name}_groups"];
				}
				$visible = array_diff($visible, array($value));
				$this->config->setValue($this->name, "groupsvisible", $visible);
				$GLOBALS["HTTP_POST_VARS"]["{$this->name}_groupsvisible"] = $visible;
				break;
			
			default :
				return FALSE;
		}
		
		return TRUE;
	}
	
}
