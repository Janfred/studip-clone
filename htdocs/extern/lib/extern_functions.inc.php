<?
/**
* extern_functions.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern_functions
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_functions.inc.php
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


require_once($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_EXTERN."/extern_config.inc.php");

/**
* Returns all statusgruppen for the given range.
*
* If there is no statusgruppe for the given range, it returns FALSE.
*
* @access	public
* @param	string	$range_id
* @return	array	(structure statusgruppe_id => name)
*/
function get_all_statusgruppen ($range_id) {
	$ret = "";
	$db = new DB_Institut();
	$db->query("SELECT statusgruppe_id, name FROM statusgruppen
							WHERE range_id='$range_id' ORDER BY position ASC");
	while ($db->next_record()) {
		$ret[$db->f("statusgruppe_id")] = $db->f("name");
	}
	return (is_array($ret)) ? $ret : FALSE;
}

/**
* Returns an array containing the ids as key and the name as value
* for every given name of statusgruppe.
*
* If there is no known statusgruppe for the given range and name, 
* it returns FALSE.
*
* @access	public
* @param	string	$range_id
* @param	string	$names comma separated list of names for 
* statusgruppe valid in the given range (syntax: 'name1','name2',...)
* @param	boolean	$hidden TRUE if you don't want to get the specified groups,
* but all others in the given range. Default FALSE.
* @return	array		(structure statusgruppe_id => name)
*/
function get_statusgruppen_by_name ($range_id, $names, $hidden = FALSE) {
	$ret = "";
	if ($hidden)
		$not = " NOT";
	$db =& new DB_Institut();
	$db->query("SELECT statusgruppe_id, name FROM statusgruppen
							WHERE range_id='$range_id' AND name$not IN ($names)
							ORDER BY position ASC");
	while ($db->next_record()) {
		$ret[$db->f("statusgruppe_id")] = $db->f("name");
	}
	return (is_array($ret)) ? $ret : FALSE;
}

/**
* Returns an array containing the ids as key and the name as value
* for every given name of statusgruppe.
*
* If there is no known statusgruppe in the given range and name, 
* it returns FALSE.
*
* @access	public
* @param	string	$range_id
* @param	string	$names comma separated list of statusgruppe_id for 
* statusgruppe valid for the given range (syntax: 'id1','id2',...)
* @param	boolean	$hidden TRUE if you don't want to get the specified groups,
* but all others in the given range. Default FALSE.
* @return	array		(structure statusgruppe_id => name)
*/
function get_statusgruppen_by_id ($range_id, $ids, $hidden = FALSE) {
	$ret = "";
	if (is_array($ids))
		$ids = "'" . implode("','", $ids) . "'";
	if ($hidden)
		$not = " NOT";
	$db =& new DB_Institut();
	$db->query("SELECT statusgruppe_id, name FROM statusgruppen
							WHERE range_id='$range_id' AND statusgruppe_id$not IN ($ids)
							ORDER BY position ASC");
	while ($db->next_record()) {
		$ret[$db->f("statusgruppe_id")] = $db->f("name");
	}
	return (is_array($ret)) ? $ret : FALSE;
}

function get_all_configurations ($range_id, $type = "") {
	$db =& new DB_Seminar();
	$query = "SELECT * FROM extern_config WHERE range_id='$range_id' ";
	
	if ($type)
		$query .= "AND config_type=$type ";
		
	$query .= "ORDER BY name ASC";
	
	$db->query($query);
	
	if ($db->num_rows() == 0)
		return FALSE;
	
	while ($db->next_record()) {
		// return registered modules only!
		$module = $GLOBALS["EXTERN_MODULE_TYPES"][$db->f("config_type")]["module"];
		if ($module)
			$all_configs[$module][$db->f("config_id")] = array("name" => $db->f("name"),
					"id" => $db->f("config_id"), "is_default" => $db->f("is_standard"));
	}

	return $all_configs;
}

function get_configuration ($range_id, $config_id) {
	$db =& new DB_Seminar();
	$query = "SELECT * FROM extern_config WHERE config_id='$config_id' ";
	$query .= "AND range_id='$range_id'";
	
	$db->query($query);
	
	if ($db->next_record()) {
		$module_name = $GLOBALS["EXTERN_MODULE_TYPES"][$db->f("config_type")]["module"];
		if ($module_name)
			$config = array("name" => $db->f("name"), "module_name" => $module_name,
					"id" => $db->f("config_id"), "is_default" => $db->f("is_standard"),
					"type" => $db->f("config_type"));
	}
	else
		return FALSE;
	
	return $config;
}

function exist_configuration ($range_id, $config_id) {
	$db =& new DB_Seminar();
	$query = "SELECT config_id FROM extern_config WHERE config_id='$config_id' ";
	$query .= "AND range_id='$range_id'";
	
	$db->query($query);
	
	if ($db->num_rows == 1)
		return TRUE;
		
	return FALSE;
}

function set_default_config ($range_id, $config_id) {
	$db =& new DB_Seminar();
	$query = "SELECT config_type, is_standard FROM extern_config WHERE config_id='$config_id' ";
	$query .= " AND range_id='$range_id'";
	$db->query($query);
	
	if ($db->next_record()) {
		if ($db->f("is_standard") == 0) {
			$query = "SELECT config_id FROM extern_config WHERE range_id='$range_id' ";
			$query .= "AND is_standard=1 AND config_type=" . $db->f("config_type");
	
			$db->query($query);
	
			if ($db->next_record()) {
				$query = "UPDATE extern_config SET is_standard=0 WHERE config_id='";
				$query .= $db->f("config_id") . "'";
		
				$db->query($query);
		
				if ($db->affected_rows() != 1)
					return FALSE;
			}
		}
		else {
			$query = "UPDATE extern_config SET is_standard=0 WHERE config_id='$config_id'";
		
			$db->query($query);
		
			if ($db->affected_rows() != 1)
				return FALSE;
			
			return TRUE;
		}
		
		$query = "UPDATE extern_config SET is_standard=1 WHERE config_id='";
		$query .= $config_id . "'";
		
		$db->query($query);
		
		if ($db->affected_rows() != 1)
			return FALSE;
		
	}
	else
		return FALSE;
		
	return TRUE;
}

function insert_config (&$config_obj) {
	$db =& new DB_Seminar();
	$query = "SELECT COUNT(config_id) AS count FROM extern_config WHERE ";
	$query .= "range_id='{$config_obj->range_id}' AND config_type={$config_obj->module_type}";
	
	$db->query($query);

	if ($db->next_record() && $db->f("count") > $GLOBALS["EXTERN_MAX_CONFIGURATIONS"])
		return FALSE;
	
	$time = time();
	$query = "INSERT INTO extern_config VALUES (";
	$query .= "'{$config_obj->id}', '{$config_obj->range_id}', {$config_obj->module_type}, ";
	$query .= "'{$config_obj->config_name}', 0, '$time', '$time')";
	
	$db->query($query);
	
	if ($db->affected_rows() != 1)
		return FALSE;
	
	return TRUE;
}

function delete_config ($range_id, $config_id) {
	$db =& new DB_Seminar();
	$query = "SELECT config_id FROM extern_config WHERE config_id='$config_id' ";
	$query .= "AND range_id='$range_id'";
	
	$db->query($query);
	
	if ($db->num_rows() == 1) {
		$file_name = $GLOBALS["EXTERN_CONFIG_FILE_PATH"] . $config_id . ".cfg";
		if (!@unlink($file_name))
			return FALSE;
	}
	else
		return FALSE;
	
	$query = "DELETE FROM extern_config WHERE config_id='$config_id' ";
	$query .= "AND range_id='$range_id'";
	
	$db->query($query);
	
	if ($db->affected_rows() != 1)
		return FALSE;
	
	return TRUE;
}

function delete_all_configs ($range_id) {
	$db =& new DB_Seminar();
	$query = "SELECT config_id FROM extern_config WHERE range_id='$range_id'";
	$db->query($query);
	$count_records = $db->num_rows();
	$count_files = 0;
	$config_ids = array();
	while ($db->next_record()) {
		if (@unlink($GLOBALS["EXTERN_CONFIG_FILE_PATH"] . $db->f("config_id") . ".cfg")) {
			$count_files++;
			$config_ids[] = $db->f("config_id");
		}
	}
	if ($count_records) {
		$query = "DELETE FROM extern_config WHERE range_id='$range_id'";
		$db->query($query);
	}
	
	return array("records" => $count_records, "files" => $count_files);
}

function get_config_info ($range_id, $config_id) {
	$db =& new DB_Seminar();
	$query = "SELECT * FROM extern_config WHERE config_id='$config_id' ";
	$query .= " AND range_id='$range_id'";
	
	
	$db->query($query);
	
	if ($db->next_record()) {
		$module_type = $db->f("config_type");
		$module = $GLOBALS["EXTERN_MODULE_TYPES"][$db->f("config_type")]["module"];
		$level = $GLOBALS["EXTERN_MODULE_TYPES"][$db->f("config_type")]["level"];
		$make = strftime("%x", $db->f("mkdate"));
		$change = strftime("%x", $db->f("chdate"));
		$sri = "&lt;studip_remote_include&gt;\n\t&lt;module name=\"$module\" /&gt;";
		$sri .= "\n\t&lt;config id=\"$config_id\" /&gt;\n\t&lt;range id=\"$range_id\" /&gt;";
		$sri .= "\n&lt;/studip_remote_include&gt;";
		$link_sri = "http://" . $GLOBALS["EXTERN_SERVER_NAME"];
		$link_sri .= "extern.php?page_url=" . _("URL_DER_INCLUDE_SEITE");
		
		if ($level) {
			$link = "http://" . $GLOBALS["EXTERN_SERVER_NAME"];
			$link .= "extern.php?module=$module&config_id=$config_id&range_id=$range_id";
			$link_structure = $link . "&view=tree";
			$sri_structure = "&lt;studip_remote_include&gt;\n\tmodule = $module\n\t";
			$sri_structure = "config_id = $config_id\n\trange_id=$range_id";
			$sri_structure .= "\n\tview = tree\n&lt;/studip_remote_include&gt;";
			$link_br = "http://" . $GLOBALS["EXTERN_SERVER_NAME"];
			$link_br .= "extern.php?module=$module<br>&config_id=$config_id<br>&range_id=$range_id";
			
			$info = array("module_type" => $module_type, "module_name" => $module,
				"name" => $db->f("name"), "make_date" => $make,
				"change_date" => $change, "link" => $link, "link_stucture" => $link_structure,
				"sri" => $sri, "sri_structure" => $sri_structure, "link_sri" => $link_sri,
				"level" => $level, "link_br" => $link_br);
		}
		else
			$info = array("module_type" => $module_type, "module_name" => $module_name,
				"name" => $db->f("name"), "make_date" => $make,
				"change_date" => $change,	"sri" => $sri, "link_sri" => $link_sri,
				"level" => $level);
		
		return $info;
	}
	
	return FALSE;	
}

function change_config_name ($range_id, $module_type, $config_id, $old_name, $new_name) {
	$db =& new DB_Seminar();
	$query = "SELECT name FROM extern_config WHERE range_id='$range_id' AND ";
	$query .= "config_type=$module_type AND name='$new_name'";
	
	$db->query($query);
	
	if ($db->num_rows())
		return FALSE;
	
	$changed = time();
	$query = "UPDATE extern_config SET name='$new_name', chdate=$changed ";
	$query .= "WHERE config_id='$config_id' AND range_id='$range_id'";
	$db->query($query);
	
	if ($db->affected_rows() != 1)
		return FALSE;
		
	return TRUE;
}

function get_config_by_name ($range_id, $module_type, $name) {
	$db =& new DB_Seminar();
	$query = "SELECT config_id FROM extern_config WHERE range_id='$range_id' AND ";
	$query .= "config_type=$module_type AND name='$name'";
	
	$db->query($query);
	
	if ($db->next_record())
		return $db->f("config_id");
	
	return FALSE;
}

function update_config ($range_id, $config_id) {
	$db =& new DB_Seminar();
	
	$changed = time();
	$query = "UPDATE extern_config SET chdate=$changed ";
	$query .= "WHERE config_id='$config_id' AND range_id='$range_id'";
	$db->query($query);
	
	if ($db->affected_rows() != 1)
		return FALSE;
		
	return TRUE;
}

function print_footer () {
	echo "\n</td></tr></table>";
	echo "\n</td></tr>\n<tr><td class=\"blank\" colspan=\"2\" width=\"100%\">&nbsp;";
	echo "</td></tr>\n</table>\n</body>\n</html>";
	page_close();
}

function get_standard_config ($range_id, $type) {
	$db =& new DB_Seminar();
	$query = "SELECT config_id FROM extern_config WHERE range_id='$range_id' AND ";
	$query .= "config_type=$type AND is_standard=1";
	
	$db->query($query);
	
	if ($db->next_record())
		return $db->f("config_id");
	
	return FALSE;
}

function mila_extern ($string, $length) {
	if ($length > 0 && strlen($string) > $length) 
		$string = substr($string, 0, $length) . "... ";
	
	return $string;
}

function get_start_item_id ($object_id) {
	$db =& new DB_Seminar();
	$query = "SELECT item_id FROM range_tree WHERE studip_object_id='$object_id'";
	
	$db->query($query);
	
	if ($db->next_record())
		return $db->f("item_id");
	
	return FALSE;
}

?>
