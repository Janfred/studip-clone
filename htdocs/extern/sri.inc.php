<?
/**
* sri.inc.php
* 
* The Stud.IP-remote-include interface to extern modules.
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sri.inc.php
// Stud.IP-remote-include interface to extern modules.
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


// this script is included in extern.inc.php

$sri_page = implode("", file($page_url));

$sri_pattern = "'^(.*)<studip_remote_include>(.*)</studip_remote_include>(.*)$'i";

if (!preg_match($sri_pattern, $sri_page, $sri_matches)) {
	echo $EXTERN_ERROR_MESSAGE;
	exit;
}

// get data out of sri-block
// 1. range_id
if (preg_match("'range_id\s*\=\s*([a-f0-9]{32})\s?\n'", $sri_matches[2], $matches))
	$config_id = $matches[1];
else {
	echo $EXTERN_ERROR_MESSAGE;
	exit;
}

// 2. module
if (preg_match("'module\s*\=\s*([a-z]{5,20})\s?\n'i", $sri_matches[2], $matches))
	$module = ucfirst(strtolower($matches[1]));
else {
	echo $EXTERN_ERROR_MESSAGE;
	exit;
}

// 3. config_id / config_name
if (preg_match("'config_id\s*\=\s*([a-f0-9]{32})\s?\n'", $sri_matches[2], $matches))
	$config_id = $matches[1];
else (preg_match("'config_name\s*\=\s*([a-z0-9-_ ]{1,40})\s?\n'i", $sri_matches[2], $matches))
	$config_name = $matches[1];

// 4. sem (which semester?)
if (preg_match("'sem\s*\=\s*([\+\-]1)\s?\n'", $sri_matches[2], $matches))
	$sem = $matches[1];


// check given data
// Is it a valid module name?
reset($EXTERN_MODULE_TYPES);
foreach ($EXTERN_MODULE_TYPES as $module_type => $module_data) {
	if ($module_data["module"] == $module) {
		$type = $module_type;
		break;
	}
}
// Wrong module name!
if (!$type) {
	echo $EXTERN_ERROR_MESSAGE;
	exit;
}

if ($config_name) {
	// check for valid configuration name and convert it into a config_id
	if (!$config_id = get_config_by_name($range_id, $type, $config_name)) {
		echo $EXTERN_ERROR_MESSAGE;
		exit;
	}
}
elseif (!$config_id) {
	// check for standard configuration
	if ($id = get_standard_config($range_id, $type))
		$config_id = $id;
	else {
		// use default configuraion
		$default = "DEFAULT";
		$config_id = "";
	}
}

// sem == -1: show data from last semester
// sem == +1: show data from next semester
// other values: show data from current semester
$now = time();
foreach ($SEMESTER as $key => $sem_record) {
	if ($now >= $sem_record["beginn"] && $now <= $sem_record["ende"]) {
		$current = $key;
		break;
	}
}
if ($sem == "-1") {
	$start = $SEMESTER[$key - 1]["beginn"];
	$end = $SEMESTER[$key - 1]["ende"];
}
elseif ($sem == "+1") {
	$start = $SEMESTER[$key + 1]["beginn"];
	$end = $SEMESTER[$key + 1]["ende"];
}
else {
	$start = $SEMESTER[$key]["beginn"];
	$end = $SEMESTER[$key]["ende"];
}

// all parameters ok, instantiate module and print data
foreach ($EXTERN_MODULE_TYPES as $type) {
	if ($type["module"] == $module)
		$module_obj =& new ExternModule($range_id, $module, $config_id, $default);
}

$module_obj->printout($start, $end);

?>






