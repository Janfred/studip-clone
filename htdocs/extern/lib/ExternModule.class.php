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

	var $type = NULL;
	var $name;
	var $config;
	var $registered_elements = array();
	var $elements = array();
	var $field_names = array();
	var $data_fields = array();
	var $args = array();
	
	
	/**
	*
	*/
	function &GetInstance ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
		$module_name = ucfirst($module_name);
		
		if ($module_name != '') {
			$class_name = "ExternModule" . $module_name;
			// Vorläufiger Bugfix (Modul-Skript wird schon in extern.inc.php eingebunden)
		//	require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/modules/$class_name.class.php");
			$module =& new $class_name($range_id, $module_name, $config_id, $set_config, $global_id);
			
			return $module;
		}
		
		return NULL;
	}
	
	/**
	* The constructor of a child class has to call this parent constructor!
	*/
	function ExternModule ($range_id, $module_name, $config_id = NULL, $set_config = NULL, $global_id = NULL) {
		/*$module_name = ucfirst($module_name);
		
		if ($module_name != "") {
			$class_name = "ExternModule" . $module_name;
			// Vorläufiger Bugfix (Modul-Skript wird schon in extern.inc.php eingebunden)
		//	require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/modules/$class_name.class.php");
			$this = new $class_name();
		}
		*/
		// the module is called via extern.php (not via the admin area) and there is
		// no config_id so it's necessary to check the range_id
		if (!$config_id && !$this->checkRangeId($range_id))
			$this->printError();
		
		foreach ($GLOBALS["EXTERN_MODULE_TYPES"] as $type => $module) {
			if ($module["module"] == $module_name) {
				$this->type = $type;
				break;
			}
		}
		if ($this->type === NULL)
			$this->printError();
		
		$this->name = $module_name;
		
		if ($config_id)
			$this->config =& new ExternConfig($range_id, $module_name, $config_id);
		else 
			$this->config =& new ExternConfig($range_id, $module_name);
		
		// the "Main"-element is included in every module and needs information
		// about the data this module handles with
		$this->elements["Main"] =& ExternElementMain::GetInstance($module_name,
				$this->data_fields, $this->field_names, $this->config);
		
		// instantiate the registered elements
		foreach ($this->registered_elements as $name => $registered_element) {
			if (is_int($name) || !$name)
				$this->elements[$registered_element] =& ExternElement::GetInstance(&$this->config, $registered_element);
			else {
				$this->elements[$name] =& ExternElement::GetInstance(&$this->config, $registered_element);
				$this->elements[$name]->name = $name;
			}
		}
				
		if ($set_config != "" && $config_id == "") {
			$config = $this->getDefaultConfig();
			$this->config->setConfiguration($set_config, $config);
		}
		
		// overwrite modules configuration with global configuration
		if ($global_id) {
			$this->config->setGlobalConfig(new ExternConfig($range_id, $module_name, $global_id),
					$this->registered_elements);
		}
		
		$this->setup();
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
		
		foreach ($this->elements as $element) {
			if ($element->isEditable())
				$default_config[$element->getName()] = $element->getDefaultConfig();
		}
		
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
	
	function getArgs () {
		
		return $this->args;
	}
		
	/**
	*
	*/
	function toString ($start, $end) {}
	
	/**
	*
	*/
	function toStringEdit ($open_elements = "", $post_vars = "",
			$faulty_values = "", $anker = "") {
		
		require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "extern/views/ExternEditModule.class.php");
		$edit_form =& new ExternEditModule($this->config, $post_vars, $faulty_values, $anker);
		
		$out = $edit_form->editHeader();
		
		foreach ($this->elements as $element) {
			if ($element->isEditable()) {
				if ($open_elements[$element->getName()])
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
				if ($faulty = $element->checkFormValues())
					$faulty_values = $faulty_values + $faulty;
			}
		}
		else {
			if ($faulty_values = $this->elements[$element_name]->checkFormValues()) {
					
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
	function store ($element_name = '', $values = '') {
		$this->config->restore(&$this, $element_name, $values);
		$this->config->store();
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
	function executeCommand ($element, $command, $value) {
		if ($element == "Main" || in_array($element, $this->registered_elements))
			return $this->elements[$element]->executeCommand($command, $value);
	}
	
	/**
	*
	*/
	function checkRangeId () {}
	
	/**
	*
	*/
	function printError () {
		
		exit;
	}
	
	/**
	*
	*/
	function getModuleLink ($module, $config, $sri_link) {
		if ($this->config->config["Main"]["incdata"])
			$link = $sri_link;
		else {
			if ($sri_link) {
				$link = $GLOBALS['EXTERN_SERVER_NAME'] . "extern.php?page_url=$sri_link";
			}
			else {
				$link = $GLOBALS['EXTERN_SERVER_NAME'] . "extern.php?module=$module";
				if ($config)
					$link .= "&config_id=$config";
				$link .= "&range_id={$this->config->range_id}";
			}
		}
		if ($this->config->global_id)
			$link .= "&global_id=" . $this->config->global_id;
		
		return $link;
	}
	
	/**
	*
	*/
	function setup () {}
	
}
?>
