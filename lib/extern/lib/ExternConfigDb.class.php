<?
# Lifter002: TODO
/**
* ExternConfigDb.class.php
* 
* This class is a wrapper class for configuration files.
* 
*
* @author		Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id: ExternConfigDb.class.php 6706 2006-07-21 12:15:16Z tthelen $
* @access		public
* @modulegroup	extern
* @module		ExternConfig
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternConfigDb.class.php
// This is a wrapper class for configuration data stored in the database.
// Copyright (C) 2007 Peter Thienel <pthienel@web.de>,
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
require_once($GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternConfig.class.php");
require_once($GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternModule.class.php");


class ExternConfigDb extends ExternConfig {

	var $db;

	/**
	*
	*/
	function ExternConfigDb ($range_id, $module_name, $config_id = '') {
		$this->db =& new DB_Seminar();
		parent::ExternConfig ($range_id, $module_name, $config_id);
	}

	/**
	*
	*/
	function store () {
		$serialized_config = addslashes(serialize($this->config));
		if (sizeof($serialized_config)) {
			$query = "UPDATE extern_config SET config = '$serialized_config' "
				. "WHERE config_id = '{$this->id}' AND range_id = '{$this->range_id}'";
			$this->db->query($query);
			return($this->updateConfiguration());
		} else {
			ExternModule::printError();
			return FALSE;
		}
		
	}
	
	/**
	*
	*/
	function parse () {
		$query = "SELECT * FROM extern_config WHERE config_id = '{$this->id}'";
		if ($this->db->query($query) && $this->db->next_record()) {
			$this->config = unserialize(stripslashes($this->db->f('config')));
		} else {
			ExternModule::printError();
		}
	}
	
	function insertConfiguration () {
		$db =& new DB_Seminar();
		$query = "SELECT COUNT(config_id) AS count FROM extern_config WHERE ";
		$query .= "range_id='{$this->range_id}' AND config_type={$this->module_type}";
		$db->query($query);

		if ($db->next_record() && $db->f('count') > $GLOBALS['EXTERN_MAX_CONFIGURATIONS']) {
			return FALSE;
		}
	
		$serialized_config = serialize($config_obj->config);
		$time = time();
		$query = "INSERT INTO extern_config VALUES (";
		$query .= "'{$this->id}', '{$this->range_id}', {$this->module_type}, ";
		$query .= "'{$this->config_name}', 0, '$serialized_config', $time, $time)";
		$db->query($query);
	
		if ($db->affected_rows() != 1) {
			return FALSE;
		}
	
		return TRUE;
	}
	
}

?>
