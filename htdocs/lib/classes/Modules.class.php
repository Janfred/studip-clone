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
		"forum" => array("id" => 0, "const" => ""),
		"documents" => array("id" => 1, "const" => ""),
		"schedule" => array("id" => 2, "const" => ""),
		"participants" => array("id" => 3, "const" => ""),
		"literature" => array("id" => 4, "const" => ""),
		"ilias_connect" => array("id" => 5, "const" => "ILIAS_CONNECT_ENABLE"),
		"chat" => array("id" => 6, "const" => "CHAT_ENABLE"),
		"support" => array("id" => 7, "const" => "SUPPORT_ENABLE"),
		"wikki" => array("id" => 8, "const" => "WIKKI_ENABLE")
	);
	var $db;
	
	function Modules() {
		$this->db = new DB_Seminar;
	}

	function getStatus($modul, $range_id, $range_type='') {
		if ($this->isBit($this->getBin($range_id, $range_type),$this->registered_modules[$modul]["id"]))
			return TRUE;
		else
			return FALSE;
	}

	function getLocalModules($range_id, $range_type = '') {
		foreach ($this->registered_modules as $key => $val) {
			if ($this->isBit($this->getBin($range_id, $range_type),$val["id"]))
				$modules_list[$key]= TRUE;
			else
				$modules_list[$key]= FALSE;
		}

		return $modules_list;
	}
	
	function getDefaultBinValue($range_id, $range_type = '') {
		global $SEM_TYPE, $SEM_CLASS, $INST_MODULES;

		$db = new DB_Seminar;
		
		if (!$range_type)
			$range_type = get_object_type($range_id);
		
		if ($range_type == "sem") {
			$query = sprintf ("SELECT status AS type FROM seminare WHERE Seminar_id ='%s'", $range_id);
		} else {
			$query = sprintf ("SELECT type FROM Institute WHERE Institut_id ='%s'", $range_id);
		}

		$db->query($query);
		$db->next_record();
		
		if ($range_type == "sem") {
			foreach ($this->registered_modules as $key=>$val) {
				if (($SEM_CLASS[$SEM_TYPE[$db->f("type")]["class"]][$key]) && (($GLOBALS[$val["const"]]) || (!$val["const"]))) {
					$this->setBit($bitmask, $val["id"]);
				} else
					$this->clearBit($bitmask, $val["id"]);
			}
		} else {
			foreach ($this->registered_modules as $key=>$val) {
				if (($INST_MODULES[($INST_MODULES[$db->f("type")]) ? $type : "default"][$key]) && (($GLOBALS[$val["const"]]) || (!$val["const"])))
					$this->setBit($bitmask, $val["id"]);
				else
					$this->clearBit($bitmask, $val["id"]);
			}
		}

		return $bitmask;
	}
	
	function getBin ($range_id, $range_type='') {
		$db = new DB_Seminar;
		
		if (!$range_type)
			$range_type = get_object_type($range_id);
		
		if ($range_type == "sem") {
			$query = sprintf ("SELECT status AS type, modules FROM seminare WHERE Seminar_id ='%s'", $range_id);
		} else {
			$query = sprintf ("SELECT type, modules FROM Institute WHERE Institut_id ='%s' ", $range_id);
		}

		$db->query($query);
		$db->next_record();

		if ($db->nf()) {
			if ($db->f("modules"))
				$bitmask = $db->f("modules");
			else
				$bitmask = $this->getDefaultBinValue($range_id, $range_type);
		}

		return $bitmask;
	}
	
	function writeBin ($range_id, $bitmask, $range_type='') {
		$db = new DB_Seminar;

		if (!$range_type)
			$range_type = get_object_type($range_id);
		
		if ($range_type == "sem") {
			$query = sprintf ("UPDATE seminare SET modules = '%s' WHERE Seminar_id ='%s'", $bitmask, $range_id);
		} else {
			$query = sprintf ("UPDATE Institute SET modules = '%s' WHERE Institut_id ='%s'", $bitmask, $range_id);
		}

		$db->query($query);
		$db->next_record();

		if ($db->affected_rows)
			return TRUE;
		else
			return FALSE;
	}
	
	
	function writeDefaultStatus($range_id, $range_type='') {
		if (!$range_type)
			$range_type = get_object_type($range_id);
		
		if ($range_type == "sem") {
			$query = sprintf ("SELECT status AS type FROM seminare WHERE Seminar_id ='%s'", $range_id);
		} else {
			$query = sprintf ("SELECT type FROM Institute WHERE Institut_id ='%s'", $range_id);
		}

		$this->db->query($query);
		$this->db->next_record();

		if ($range_type == "sem") {
			$query = sprintf ("UPDATE seminare SET modules = '%s' WHERE Seminar_id ='%s'", $this->getDefaultBinValue($range_id, $range_type), $range_id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;
		} else {
			$query = sprintf ("UPDATE Institute SET modules = '%s' WHERE Institut_id ='%s'", $this->getDefaultBinValue($range_id, $range_type), $range_id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;
		}
	}

	function writeStatus($modul, $range_id, $value, $range_type='') {
		global $SEM_TYPE, $SEM_CLASS, $INST_MODULES;
		
		$db = new DB_Seminar;
		
		if (!$range_type)
			$range_type = get_object_type($range_id);
		
		if ($range_type == "sem") {
			$query = sprintf ("SELECT status AS type FROM seminare WHERE Seminar_id ='%s'", $range_id);
		} else {
			$query = sprintf ("SELECT type FROM Institute WHERE Institut_id ='%s'", $range_id);
		}
		
		$db->query($query);
		$db->next_record();
		
		$bitmask = $this->getBin($range_id, $range_type);
		
		if ($value)
			$this->setBit($bitmask, $this->registered_modules[$modul]["id"]);
		else
			$this->clearBit($bitmask, $this->registered_modules[$modul]["id"]);
			
		if ($range_type == "sem") {
			if ($this->checkGlobal($modul)) {
				$query = sprintf ("UPDATE seminare SET modules = '%s' WHERE Seminar_id ='%s'", $bitmask, $range_id);
				$db->query($query);
				if ($db->affected_rows())
					return TRUE;
				else 
					return FALSE;
			} else 
				return FALSE;
		} else {
			if ($this->checkGlobal($modul)) {
				$query = sprintf ("UPDATE Institute SET modules = '%s' WHERE Institut_id ='%s'", $bitmask, $range_id);
				$db->query($query);
				if ($db->affected_rows())
					return TRUE;
				else 
					return FALSE;
			} else 
				return FALSE;
		}
	}

	function checkGlobal($modul) {
		if ($this->registered_modules[$modul]["const"]) {
			if ($GLOBALS[$this->registered_modules[$modul]["const"]])
				return TRUE;
			else
				return FALSE;
		} else
			return TRUE;
	}
	
	function checkLocal($modul, $range_id, $range_type='') {
		if ($this->getStatus($modul, $range_id, $range_type))
			return TRUE;
		else
			return FALSE;
	}
	
	function isEnableable ($modul, $range_id, $range_type='') {
		global $SEM_TYPE, $SEM_CLASS, $INST_MODULES;

		if (!$range_type)
			$range_type = get_object_type($range_id);
		
		if ($range_type == "sem") {
			$query = sprintf ("SELECT status AS type FROM seminare WHERE Seminar_id ='%s'", $range_id);
		} else {
			$query = sprintf ("SELECT type FROM Institute WHERE Institut_id ='%s'", $range_id);
		}
		
		$this->db->query($query);
		$this->db->next_record();
		
		if ($range_type == "sem") {
			if ($this->checkGlobal($modul))
				return TRUE;
			else
				return FALSE;
		} else {
			if ($this->checkGlobal($modul))
				return TRUE;
			else
				return FALSE;
		}
	}
	
	function setBit(&$bitField,$n) { 
		// Ueberprueft, ob der Wert zwischen 0-31 liegt 
		// $n ist hier der Wert der aktivierten Checkbox, z.B. 15 
		// Somit waere hier die 15. Checkbox aktiviert 
		if(($n < 0) or ($n > 31)) return false;  
       
       
		// Bit Shifting 
		// Hier wird nun der Binaerwert fuer die aktuelle Checkbox gesetzt. 
		// In unserem Beispiel wird hier nun die 15. Stelle von rechts auf 1 gesetzt 
		// 100000000000000 <-- Dieses entspricht der Zahl 16384 
		// | ist nicht das logische ODER sondern das BIT-oder 
		$bitField |= (0x01 << ($n)); 
		return true; 
	} 

	function clearBit(&$bitField,$n) { 
		// Loescht ein Bit oder ein Bitfeld 
		// & ist nicht das logische UND sondern das BIT-and 
		$bitField &= ~(0x01 << ($n)); 
		return true; 
	} 

	function isBit($bitField,$n) { 
		// Ist die x-te Stelle eine 1? 
		return (($bitField & (0x01 << ($n)))); 
	} 	
}
