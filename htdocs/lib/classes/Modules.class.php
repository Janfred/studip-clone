<?
/**
* Modules.class.php
* 
* check for modules (global and local for institutes and Veranstaltungen), read and write
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		Modules.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Modules.class.php
// Checks fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen), Schreib-/Lesezugriff
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once $ABSOLUTE_PATH_STUDIP.("functions.php");
require_once $ABSOLUTE_PATH_STUDIP.("config.inc.php");

class Modules {
	var $registered_modules = array (
		"forum" => array("id" => 1, "const" => ""),
		"documents" => array("id" => 2, "const" => ""),
		"ilias_connect" => array("id" => 3, "const" => "ILIAS_CONNECT_ENABLE"),
		"chat" => array("id" => 4, "const" => "CHAT_ENABLE"),
		"support" => array("id" => 5, "const" => "SUPPORT_ENABLE")
	);
	var $db;
	
	function Modules() {
		$this->db = new DB_Seminar;
	}

	function getStatus($modul, $range_id) {
		if (get_object_type($range_id) == "sem") {
			$query = sprintf ("SELECT status AS type, modules FROM seminare WHERE Seminar_id ='%s' AND (modules IS NULL OR modules > 0)", $range_id);
		} elseif (get_object_type($range_id) == "inst") {
			$query = sprintf ("SELECT type, modules FROM Institute WHERE Institut_id ='%s' AND (modules IS NULL OR modules > 0)", $range_id);
		} else
			return FALSE;
			
		$this->db->query($query);
		$this->db->next_record();

		if ($this->db->nf()) {
			if ($this->db->f("modules"))
				$modules = decbin($this->db->f("modules"));
			else
				$modules = $this->getDefaultBinValue($this->db->f("type"), $range_id);
		}
		
		if ($modules{$this->registered_modules[$modul]["id"]})
			return TRUE;
		else
			return FALSE;
	}
	
	function getLocalModules($range_id) {
		if (get_object_type($range_id) == "sem") {
			$query = sprintf ("SELECT status AS type, modules FROM seminare WHERE Seminar_id ='%s' AND (modules IS NULL OR modules > 0)", $range_id);
		} elseif (get_object_type($range_id) == "inst") {
			$query = sprintf ("SELECT type, modules FROM Institute WHERE Institut_id ='%s' AND (modules IS NULL OR modules > 0)", $range_id);
		} else
			return FALSE;

		$this->db->query($query);
		$this->db->next_record();

		if ($this->db->nf()) {
			if ($this->db->f("modules"))
				$modules = decbin($this->db->f("modules"));
			else
				$modules = $this->getDefaultBinValue($this->db->f("type"), $range_id);
		}
			
		reset ($this->registered_modules);
		
		for ($i = 1; $i <= strlen($modules); $i++) {
			if (list($module_name, $tmp_module) = each($this->registered_modules)) {
				if ($modules{$tmp_module["id"]})
					$modules_list[$module_name]= TRUE;
				else
					$modules_list[$module_name]= FALSE;
			}
		}
		return $modules_list;
	}
	
	function getDefaultBinValue($type, $range_id) {
		global $GLOBALS, $SEM_TYPE, $SEM_CLASS, $INST_MODULES;
		
		$modules = "1";
		
		if (get_object_type($range_id) == "sem") {
			foreach ($this->registered_modules as $key=>$val) {
				if (($SEM_CLASS[$SEM_TYPE[$type]["class"]][$key]) && (($GLOBALS[$val["const"]]) || (!$val["const"])))
					$modules .= "1";
				else
					$modules .= "0";
			}
		} elseif (get_object_type($range_id) == "inst") {
			foreach ($this->registered_modules as $key=>$val) {
				if (($INST_MODULES[($INST_MODULES[$type]) ? $type : "default"][$key]) && (($GLOBALS[$val["const"]]) || (!$val["const"])))
					$modules .= "1";
				else
					$modules .= "0";
			}
		}
		return $modules;
	}
	
	function getDefaultDecValue($type, $range_id) {
		return bindec($this->getDefaultBinValue($type, $range_id));
	}
	
	function writeDefaultStatus($range_id) {
		if (get_object_type($range_id) == "sem") {
			$query = sprintf ("SELECT status AS type FROM seminare WHERE Seminar_id ='%s'", $range_id);
		} else {
			$query = sprintf ("SELECT type FROM Institute WHERE Institut_id ='%s'", $range_id);
		}
		$this->db->query($query);
		$this->db->next_record();
		
		if (get_object_type($range_id) == "sem") {
			$query = sprintf ("UPDATE seminare SET modules = '%s' WHERE Seminar_id ='%s'", $this->getDefaultDecValue($this->db->f("type"), $range_id), $range_id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;
		} elseif (get_object_type($range_id) == "inst") {
			$query = sprintf ("UPDATE Institute SET modules = '%s' WHERE Institut_id ='%s'", $thi->getDefaultDecValue($this->db->f("type"), $range_id), $range_id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;
		}
	}

	function writeStatus($modul, $range_id, $value) {
		global $SEM_TYPE, $SEM_CLASS, $INST_MODULES;
		
		if (get_object_type($range_id) == "sem") {
			$query = sprintf ("SELECT status AS type, modules FROM seminare WHERE Seminar_id ='%s' AND (modules IS NULL OR modules > 0)", $range_id);
		} else {
			$query = sprintf ("SELECT type, modules FROM Institute WHERE Institut_id ='%s' AND (modules IS NULL OR modules > 0)", $range_id);
		}
		$this->db->query($query);
		$this->db->next_record();
		if ($this->db->nf()) {
			if ($this->db->f("modules"))
				$changed_modules = decbin($this->db->f("modules"));
			else
				$changed_modules = $this->getDefaultBinValue($this->db->f("type"), $range_id);
		}
		
		$changed_modules = $this->db->f("modules");
		$changed_modules{$this->registered_modules[$modul]["id"]} = $value;

		if (get_object_type($range_id) == "sem") {
			if (($SEM_CLASS[$SEM_TYPE[$db->f("type")]["class"]][$modul]) && ($this->checkGlobal($modul))) {
				$query = sprintf ("INSERT INTO seminare SET modules = '%s' WHERE Seminar_id ='%s'", bindec($changed_modules), $range_id);
				$this->db->query($query);
				if ($this->db->affected_rows())
					return TRUE;
				else 
					return FALSE;
			} else 
				return FALSE;
		} elseif (get_object_type($range_id) == "inst") {
			if (($INST_MODULES[($INST_MODULES[$db->f("type")]) ? $db->f("type") : "default"][$module]) && ($this->checkGlobal($modul))) {
				$query = sprintf ("INSERT INTO Institute SET modules = '%s' WHERE Institut_id ='%s'", bindec($changed_modules), $range_id);
				$this->db->query($query);
				if ($this->db->affected_rows())
					return TRUE;
				else 
					return FALSE;
			} else 
				return FALSE;
		}
	}

	function checkGlobal($modul) {
		global $GLOBALS;
		
		if ($this->registered_modules[$modul]["const"]) {
			if ($GLOBALS[$this->registered_modules[$modul]["const"]])
				return TRUE;
			else
				return FALSE;
		} else
			return TRUE;
	}
	
	function checkLocal($modul, $range_id) {
		if ($this->getStatus($modul, $range_id))
			return TRUE;
		else
			return FALSE;
	}
}
