<?
/**
* ExternModule.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModule
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


require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/extern_config.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/extern_functions.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/ExternConfig.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/ExternElement.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/ExternElementMain.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/views/ExternEditModule.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");


class ExternModule {

	var $type;
	var $name;
	var $config;
	var $registered_elements;
	var $elements;
	var $field_names;
	var $data_fields;

	/**
	*
	*/
	function ExternModule ($range_id, $module_name, $config_id = "", $set_config = "") {
		$module_name = ucfirst($module_name);
		
		if ($module_name != "") {
			$class_name = "ExternModule" . $module_name;
			
			require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/modules/$class_name.class.php");
			$this = new $class_name($range_id, $module_name);
		}
		
		// the module is called via extern.php (not via the admin area) and there is
		// no config_id so it's necessary to check the range_id
		if (!$config_id && !$this->checkRangeId($range_id)) {
		echo "<BR>SJHFLKSJHFLSjksdhfskjhfsfhsfhsdfls";
			$this->printError();
		}
		
		foreach ($GLOBALS["EXTERN_MODULE_TYPES"] as $type => $module) {
			if ($module["module"] == $module_name) {
				$this->type = $type;
				break;
			}
		}
		if ($this->type == "")
			$this->printError();
		
		$this->name = $module_name;
		
		if ($config_id)
			$this->config =& new ExternConfig($range_id, $module_name, $config_id);
		else 
			$this->config =& new ExternConfig($range_id, $module_name);
		
		// the "Main"-element is included in every module and needs information
		// about the data this module handles with
		$this->elements["Main"] =& new ExternElementMain($module_name, $this->data_fields,
				$this->field_names, $this->config);
		
		// instantiate the registered elements
		foreach ($this->registered_elements as $registered_element)
			$this->elements[$registered_element] =& new ExternElement($this->config, $registered_element);
		
		if ($set_config != "" && $config_id == "") {
			$config = $this->getDefaultConfig();
			$this->config->setConfiguration($set_config, $config);
		}
		
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
	function &getConfig () {
		return $this->config;
	}
	
	/**
	*
	*/
	function getDefaultConfig () {
		$default_config = array();
		
		reset($this->elements);
		foreach ($this->elements as $element)
			$default_config[$element->getName()] = $element->getDefaultConfig();
		
		return $default_config;
	}
	
	/**
	*
	*/
	function getAllElements () {
		return $this->elements;
	}
	
	/**
	*
	*/
	function getValue ($attribute) {
		return $this->config->getValue($this->name, $attribute);
	}
	
	/**
	*
	*/
	function setValue ($attribute, $value) {
		$this->config->setValue($this->name, $attribute, $value);
	}
	
	/**
	*
	*/
	function getAttributes ($element_name, $tag_name) {
		return $this->config->getAttributes($element_name, $tag_name);
	}
	
	/**
	*
	*/
	function toString ($start, $end) {
	}
	
	/**
	*
	*/
	function toStringEdit ($open_elements = "", $post_vars = "",
			$faulty_values = "", $anker = "") {
		
		require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/views/ExternEditModule.class.php");
		$edit_form =& new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
		
		$out = $edit_form->editHeader();
		
		reset($this->elements);
		foreach ($this->elements as $element) {
			if ($element->isEditable()) {
				if ($open_elements[$element->getName()] == TRUE)
					$out .= $element->toStringEdit($post_vars, $faulty_values, $edit_form, $anker);
				else {
					$edit_form->setElementName($element->getName());
					$out .= $edit_form->editElementHeadline($element->getRealName(),
							$this->getName(), $this->config->getId(), FALSE, $anker);
				}
			}
		}
		
		$out .= $edit_form->editFooter();
		
		return $out;
	}

	/**
	*
	*/
	function printout ($start, $end) {
		echo $this->toString($start, $end);
	}

	/**
	*
	*/
	function printoutEdit ($element_name = "", $post_vars = "",
			$faulty_values = "", $anker = "") {
			
		echo $this->toStringEdit($element_name, $post_vars, $faulty_values, $anker);
	}
	
	/**
	*
	*/
	function checkFormValues ($element_name = "") {
		$faulty_values = array();
		
		if ($element_name == "") {
			foreach ($this->elements as $element) {
				if ($faulty = $this->config->checkFormValues($element->getName(), $element->getAttributes()))
					$faulty_values = $faulty_values + $faulty;
			}
		}
		else {
			if ($faulty_values = $this->config->checkFormValues($element_name,
					$this->elements[$element_name]->getAttributes())) {
					
				return $faulty_values;
			}
		}
			
		if (sizeof($faulty_values))
			return $faulty_values;
		
		return FALSE;
	}
	
	/**
	*
	*/
	function store ($element_name = "", $values = "") {
		$this->config->store(&$this, $element_name, $values);
	}
	
	/**
	*
	*/
	function getDescription () {
		return $GLOBALS["EXTERN_ELEMENT_TYPES"][$this->type]["description"];
	}
	
	/**
	*
	*/
	function mainCommand ($command, $value) {
		return $this->elements["Main"]->mainCommand($command, $value);
	}
	
	/**
	*
	*/
	function checkRangeId () {}
	
	/**
	*
	*/
	function printError () {
		global $user, $perm;
		
		if ($user->name)
			$perm->check("GOTT");
		else
			echo $GLOBALS["EXTERN_ERROR_MESSAGE"];
		
		exit;
	}
	
}
?>
