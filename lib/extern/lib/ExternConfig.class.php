<?
/**
* ExternConfig.class.php
* 
* This class is a wrapper class for configuration files.
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternConfig
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternElement.class.php
// This is a wrapper class for configuration files.
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

require_once($GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/extern_functions.inc.php");


class ExternConfig {

	var $id;
	var $config = array();
	var $global_id = NULL;
	var $module_type;
	var $module_name;
	var $config_name;
	var $range_id;
	var $file_name;

	/**
	*
	*/
	function ExternConfig ($range_id, $module_name, $config_id = "") {
		
		if ($config_id != "") {
			if ($configuration = get_configuration($range_id, $config_id)) {
				$this->id = $config_id;
				$this->module_type = $configuration["type"];
				$this->module_name = $configuration["module_name"];
				$this->config_name = $configuration["name"];
				$this->range_id = $range_id;
				$this->file_name = $config_id . ".cfg";
				$this->parse();
			}
			else
				ExternModule::printError();
		}
		else {
			$module_name = ucfirst(strtolower($module_name));
			foreach ($GLOBALS["EXTERN_MODULE_TYPES"] as $type => $module) {
				if ($module["module"] == $module_name) {
					$this->module_name = $module_name;
					$this->module_type = $type;
					$this->range_id = $range_id;
					break;
				}
			}
		}
		
	}

	/**
	*
	*/
	function getName () {
		return $this->module_name;
	}
	
	/**
	*
	*/
	function getConfigName () {
		return $this->config_name;
	}

	/**
	*
	*/
	function getType () {
		global $EXTERN_MODULE_TYPES;
		foreach ($EXTERN_MODULE_TYPES as $key => $known_module) {
			if ($known_module["name"] == $this->module_type)
				return $key;
		}
		
		return FALSE;
	}

	/**
	*
	*/
	function getTypeName () {
		return $this->module_type;
	}

	/**
	*
	*/
	function &getConfiguration () {
		return $this->config;
	}
	
	function setConfiguration ($type, $config) {
		if ($type == "NEW") {
			$this->id = $this->makeId();
			$this->file_name = $this->id . ".cfg";
			$this->config_name = $this->createConfigName($this->range_id);
			
			// take the new configuration, write the name in the configuration
			// insert it into the database and write it to the specified path with
			// id.cfg as file name
			$this->config = $config;
			$this->setValue("Main", "name", $this->config_name);
			if (insert_config($this))
				$this->store();
			else
				ExternModule::printError();
		}
		elseif ($type == "DEFAULT") {
			$this->config = $config;
		}
		else
			ExternModule::printError();
	}
	
	/**
	*
	*/
	function getParameterNames () {}

	/**
	*
	*/
	
	function getAllParameterNames () {}

	/**
	*
	*/
	function getValue ($element_name, $attribute) {
	
		// if the first character is a pipe symbol (|) convert it
		// into an array
		$value = $this->config[$element_name][$attribute];
		
		if ($value{0} == "|")
			return explode("|", substr($this->config[$element_name][$attribute], 1));
		
		return $value;
	}

	/**
	*
	*/
	function setValue ($element_name, $attribute, $value) {
		
		// if $value is an array convert it into a string, each value is separated
		// by the pipe symbol (|) the first character of this string is |
		if (is_array($value)) {
			ksort($value, SORT_NUMERIC);
			$value = "|" . implode("|", $value);
		}
		
		$this->config[$element_name][$attribute] = $value;
	}
	
	/**
	*
	*/
	function getAttributes ($element_name, $tag, $second_set = FALSE) {
		if (!is_array($this->config[$element_name]))
			return "";
			
		$attributes = "";
		
		reset($this->config);
		if ($second_set) {
			foreach ($this->config[$element_name] as $tag_attribute_name => $value) {
				if ($value != "") {
					$tag_attribute = explode("_", $tag_attribute_name);
					if ($tag_attribute[0] == $tag && !isset($tag_attribute[2])) {
						if ($this->config[$element_name]["{$tag_attribute_name}2_"] == "")
							$attributes .= " {$tag_attribute[1]}=\"$value\"";
						else {
							$attributes .= " {$tag_attribute[1]}=\""
									. $this->config[$element_name]["{$tag_attribute_name}2_"] . "\"";
						}
					}
				}
			}
		}
		else {
			foreach ($this->config[$element_name] as $tag_attribute_name => $value) {
				if ($value != "") {
					$tag_attribute = explode("_", $tag_attribute_name);
					if ($tag_attribute[0] == $tag && !isset($tag_attribute[2]))
						$attributes .= " {$tag_attribute[1]}=\"$value\"";
				}
			}
		}
		
		return $attributes;
	}
	
	// Returns a complete HTML-tag with attributes
	function getTag ($element_name, $tag, $second_set = FALSE) {
		return "<$tag" . $this->getAttributes($element_name, $tag, $second_set) . ">";
	}
	
	/**
	* Restores a configuration with all registered elements and their attributes.
	* The restored configuration contains only the attributes of the current
	* registered elements.
	*
	* @access		public
	* @param		object	 $module		The module whose configuration will be restored
	* @param		string[] $values		These values overwrites the values in current configuration
	*/
	function restore (&$module, $element_name = '', $values = '') {
		// store the own configuration if the function is called without parameters
		if ($values != "" && $module) {
			if ($element_name)
				$module_elements[$element_name] = $module->elements[$element_name];
			else
				$module_elements = $module->elements;
		
			foreach ($module_elements as $element_name => $element_obj) {
			
				if ($element_obj->isEditable()) {
			
					$attributes = $element_obj->getAttributes();
				
					foreach ($attributes as $attribute) {
						$form_name = $element_name . "_" . $attribute;
					
						if (isset($values[$form_name])) {
							if (is_array($values[$form_name])) {
								ksort($values[$form_name], SORT_NUMERIC); 
								$form_value = "|" . implode("|", $values[$form_name]);
								$config_tmp[$attribute] = stripslashes($form_value);
							}
							else {
								$config_tmp[$attribute] = stripslashes($values[$form_name]);
							}
						}
						else
							$config_tmp[$attribute] = $this->config[$element_name][$attribute];
					}
				}
				$this->config[$element_name] = $config_tmp;
			}
		}
	}
	
	/**
	*
	*/
	function store () {
		$file_content = "; Configuration file for the extern module"
				. " $this->module_name in Stud.IP\n"
				. "; (range_id: $this->range_id)\n"
				. "; DO NOT EDIT !!!\n";
		
		foreach ($this->config as $element => $attributes) {
			$file_content .= "\n[" . $element . "]\n";
			
			reset($attributes);
			foreach ($attributes as $attribute => $value)
				$file_content .= $attribute . " = \"" . $value . "\"\n";
		}

		if ($file = @fopen($GLOBALS["EXTERN_CONFIG_FILE_PATH"] . $this->file_name, 'w')) {
			fputs($file, $file_content);
			fclose($file);
			update_config($this->range_id, $this->id);
		}
		else
			ExternModule::printError();
		
	}
	
	/**
	*
	*/
	function parse () {
		$file_name = $GLOBALS["EXTERN_CONFIG_FILE_PATH"] . $this->file_name;
		
		if (file_exists($file_name))
			$this->config = parse_ini_file($file_name, TRUE);
		else {
			// error handling
		}
	}
	
	/**
	*
	*/
	function makeId () {
		mt_srand((double) microtime() * 1000000);
		
		return md5(uniqid(mt_rand(), 1));
	}
	
	/**
	*
	*/
	function getId () {
		return $this->id;
	}
	
	/**
	*
	*/
	function createConfigName ($range_id) {
		$configurations = get_all_configurations($range_id, $this->module_type);
		
		$config_name_prefix = _("Konfiguration") . " ";
		$config_name_suffix = 1;
		$config_name = $config_name_prefix . $config_name_suffix;
		$all_config_names = "";
		
		if ($configurations) {
			foreach ($configurations[$this->module_name] as $configuration)
				$all_config_names .= $configuration["name"];
		}
		
		while(stristr($all_config_names, $config_name)) {
			$config_name = $config_name_prefix . $config_name_suffix;
			$config_name_suffix++;
		}
		
		return $config_name;
	}
	
	/**
	*
	*/
	function setGlobalConfig ($global_config, $registered_elements) {
		$this->global_id = $global_config->getId();
		
		// the name of the global configuration has to be overwritten by the
		// the name of the main configuration
		$global_config->config["Main"]["name"] = $this->config["Main"]["name"];
		
		// The Main-element is not a registered element, because it is part of every
		// module. So register it now.
		$registered_elements[] = "Main";
		
		foreach ($registered_elements as $name => $element) {
			if ((is_int($name) || !$name) && $this->config[$element]) {
				foreach ($this->config[$element] as $attribute => $value) {
					if ($value === "")
						$this->config[$element][$attribute] = $global_config->config[$element][$attribute];
				}
			}
			else if ($this->config[$name]) {
				foreach ($this->config[$name] as $attribute => $value) {
					if ($value === "")
						$this->config[$name][$attribute] = $global_config->config[$name][$attribute];
				}
			}
		}
	}
	
}

?>
