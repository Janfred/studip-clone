<?
/**
* LockSeminars.class.php
* 
* 
*
* @author		Mark Sievers <msievers@uos.de> 
* @version		$Id$
* @access		public
* @modulegroup	core
* @module			
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// LockSeminars.class.php
// Klasse für SemesterVerwaltung
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



class LockRules {
	var $db;


	function LockRules() {
		$this->db = new DB_Seminar;
	}

	function getAllLockRules() {
		$i=0;
		$sql = "SELECT * FROM lock_rules";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		while ($this->db->next_record()) {
			$lockdata[$i] = $this->wrapLockRules();
			$i++;
		}		
		return $lockdata;
	
	}

	function getLockRule($lock_id) {
		$sql = "SELECT * FROM lock_rules WHERE lock_id = '".$lock_id."'";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		$this->db->next_record();
		return $this->wrapLockRules();
	}
	
	function wrapLockRules() {
		$lockdata = array();
		$lockdata["lock_id"]		= $this->db->f("lock_id");
		$lockdata["name"] 			= $this->db->f("name");
		$lockdata["description"]	= $this->db->f("description");
		$lockdata["attributes"]		= unserialize($this->db->f("attributes"));
		return $lockdata;
	}

	function insertNewLockRule($lockdata) {
		$lock_id = md5(uniqid("Legolas"));
		$sql = "INSERT INTO lock_rules (lock_id, name, description, attributes) VALUES ('".$lock_id."', '".$lockdata["name"]."', '".$lockdata["description"]."', '".serialize($lockdata["attributes"])."')";
		if (!$this->db->query($sql)) {
			echo "Error! insert_query not succeeded";
			return 0;
		}
		return $lock_id;
	}
// update!!!	
	function updateExistingLockRule($lockdata) {
   		if (!$this->db->query("UPDATE lock_rules SET ".
    	            "name='".$lockdata["name"]."', ".
					"description='".$lockdata["description"]."', ".
					"attributes='".serialize($lockdata["attributes"])."' ".
                    "WHERE lock_id='".$lockdata["lock_id"]."'")) {
                        echo "Fehler! Einf&uuml;gen in die DB!";
                        return 0;
                    }
    	else return 1;
	}

	function getLockRuleByName($name) {
		$sql = "SELECT lock_id FROM lock_rules WHERE name='".$name."'";
		if  (!$this->db->query($sql)) {
			echo "Error! query not succeeded";
			return 0;
		}
		if ($this->db->num_rows()==0) {
			return 0;
		}
		$this->db->next_record();
		return $this->db->f("lock_id");;
	}

	function deleteLockRule($lock_id) {
		$sql = "DELETE FROM lock_rules WHERE lock_id='".$lock_id."'";
		if (!$this->db->query($sql)) {
			echo "Error! Query not succeeded";
			return 0;
		}
		return 1;
	}

}


?>
