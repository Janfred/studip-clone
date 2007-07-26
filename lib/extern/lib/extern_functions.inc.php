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


require_once($GLOBALS["RELATIVE_PATH_EXTERN"]."/extern_config.inc.php");
require_once("lib/classes/DataFieldEntry.class.php");

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
	$db =& new DB_Seminar();
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
	$db =& new DB_Seminar();
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
* @param	string	$ids comma separated list of statusgruppe_id for 
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
	$db =& new DB_Seminar();
	$db->query("SELECT statusgruppe_id, name FROM statusgruppen
							WHERE range_id='$range_id' AND statusgruppe_id$not IN ($ids)
							ORDER BY position ASC");
	while ($db->next_record()) {
		$ret[$db->f("statusgruppe_id")] = $db->f("name");
	}
	return (is_array($ret)) ? $ret : FALSE;
}

function print_footer () {
	echo "\n</td></tr></table>";
	echo "\n</td></tr>\n<tr><td class=\"blank\" colspan=\"2\" width=\"100%\">&nbsp;";
	echo "</td></tr>\n</table>\n</body>\n</html>";
	page_close();
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

function get_generic_datafields ($object_type) {
//	$datafields_obj = new DataFields();
	$fieldStructs = DataFieldStructure::getDataFieldStructures($object_type);
//	$generic_datafields = $datafields_obj->getFields($object_type);
	
	if (sizeof($fieldStructs)) {
		foreach ($fieldStructs as $struct) {
			$datafields["ids"][] = $struct->getID();
			$datafields["names"][] = $struct->getName();
			$datafields["ids_names"][$struct->getID()] = $struct->getName();
		}		
		
		return $datafields;
	}
	
	return FALSE;
}

function array_condense ($array) {
	foreach ($array as $value)
		$array_ret[] = $value;
	
	return $array_ret;
}

function update_generic_datafields (&$config, &$data_fields, &$field_names, $object_type) {
	// setup the generic data fields if they exist or if there are any changes
	if ($generic_datafields = get_generic_datafields($object_type)) {
		$config_datafields = $config->getValue("Main", "genericdatafields");
		if (!is_array($config_datafields))
			$config_datafields = array();
		
		$visible = (array) $config->getValue("Main", "visible");
		$order = (array) $config->getValue("Main", "order");
		$aliases = (array) $config->getValue("Main", "aliases");
		$store = FALSE;
		
		// data fields deleted
		if ($diff_generic_datafields = array_diff($config_datafields,
				$generic_datafields["ids"])) {
			$swapped_datafields = array_flip($config_datafields);
			$swapped_order = array_flip($order);
			$offset = sizeof($data_fields) - sizeof($config_datafields);
			$deleted = array();
			foreach ($diff_generic_datafields as $datafield) {
				$deleted[] = $offset + $swapped_datafields[$datafield];
				unset($visible[$offset + $swapped_datafields[$datafield]]);
				unset($swapped_order[$offset + $swapped_datafields[$datafield]]);
				unset($aliases[$offset + $swapped_datafields[$datafield]]);
			}
			$visible = array_condense($visible);
			$order = array_condense(array_flip($swapped_order));
			$aliases = array_condense($aliases);
			
			$config_generic_datafields = array_diff($config_datafields,
					$diff_generic_datafields);
			for ($i = 0; $i < sizeof($order); $i++) {
				foreach ($deleted as $position) {
					if ($order[$i] >= $position)
						$order[$i]--;
				}
			}
			$store = TRUE;
		}
		
		if (!$config_generic_datafields)
			$config_generic_datafields = $config_datafields;
		// data fields added
		if ($diff_generic_datafields = array_diff($generic_datafields["ids"],
				$config_generic_datafields)) {
			$config_generic_datafields = array_merge((array)$config_generic_datafields,
					(array)$diff_generic_datafields);
			foreach ($diff_generic_datafields as $datafield) {
				$visible[] = "0";
				$order[] = sizeof($order);
				$aliases[] = $generic_datafields["ids_names"][$datafield];
			}
			$store = TRUE;
		}

		if ($store) {
			$config->setValue("Main", "visible", $visible);
			$config->setValue("Main", "order", $order);
			$config->setValue("Main", "aliases", $aliases);
			$config->setValue("Main", "genericdatafields", $config_generic_datafields);
			$config->store();
		}

		$config_generic_datafields = $config->getValue("Main", "genericdatafields");
		foreach ($config_generic_datafields as $datafield) {
			$field_names[] = $generic_datafields["ids_names"][$datafield];
		}

		if ($store)
			return TRUE;

		return FALSE;
	}

	return FALSE;
}

function get_default_generic_datafields (&$default_config, $object_type) {
	// extend $default_config if generic data fields exist
	if ($generic_datafields = get_generic_datafields($object_type)) {
		foreach ($generic_datafields["ids"] as $datafield) {
			$default_config["genericdatafields"] .= "|" . $datafield;
			$default_config["visible"] .= "|0";
			$default_config["order"] .= "|" . substr_count($default_config["order"], "|");
			$default_config["aliases"] .= "|" . $generic_datafields["ids_names"][$datafield];
		}

		return TRUE;
	}

	return FALSE;
}

function enable_sri ($i_id, $enable) {
	$db =& new DB_Seminar();
	if ($enable) {
		$query = "UPDATE Institute SET srienabled = 1 WHERE Institut_id = '$i_id'";
		$db->query($query);
	} else {
		$query = "UPDATE Institute SET srienabled = 0 WHERE Institut_id = '$i_id'";
		$db->query($query);
	}
}

function sri_is_enabled ($i_id) {
	if ($GLOBALS['EXTERN_SRI_ENABLE']) {
		if (!$GLOBALS['EXTERN_SRI_ENABLE_BY_ROOT']) {
			return 1;
		}
		$db =& new DB_Seminar();
		$query = "SELECT srienabled FROM Institute WHERE Institut_id = '$i_id' AND srienabled = 1";
		$db->query($query);
		if ($db->next_record()) {
			return 1;
		}
	}
	return 0;
}

?>
