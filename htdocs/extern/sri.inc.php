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

$semester = new SemesterData;
$all_semester = $semester->getAllSemesterData();

if ($sri_file = @file($page_url))
	$sri_page = implode("", $sri_file);
else {
	echo $EXTERN_ERROR_MESSAGE;
	exit;
}

//echo $sri_page;
$sri_pattern = "'(.*)(\<studip_remote_include\>.*\<\/studip_remote_include\>)(.*)'is";

if (!preg_match($sri_pattern, $sri_page, $sri_matches)) {
	echo $EXTERN_ERROR_MESSAGE;
	echo $sri_page;
	exit;
}

$parser = xml_parser_create();
xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
xml_parse_into_struct($parser, $sri_matches[2], $xml_values, $xml_tags);

$allowed_xml_tags = array("module", "range", "config", "sem", "global");

foreach ($allowed_xml_tags as $xml_tag) {
	if ($xml_tags[$xml_tag]) {
		$attributes = $xml_values[$xml_tags[$xml_tag][0]]["attributes"];
		foreach ($attributes as $attribute => $value) {
			$parameter_name = $xml_tag . "_" . $attribute;
			$$parameter_name = $value;
		}
	}
}

// check given data
// no range_id? sorry...
if (!$range_id) {
	echo $sri_matches[1];
	echo $EXTERN_ERROR_MESSAGE;
	echo $sri_matches[3];
	exit;
}

// Is it a valid module name?
reset($EXTERN_MODULE_TYPES);
foreach ($EXTERN_MODULE_TYPES as $module_type => $module_data) {
	if ($module_data["module"] == $module_name) {
		$type = $module_type;
		break;
	}
}
// Wrong module name!
if (!$type) {
	echo $sri_matches[1];
	echo $EXTERN_ERROR_MESSAGE;
	echo $sri_matches[3];
	exit;
}

// if there is no config_id or config_name, take the DEFAULT configuration
if ($config_name) {
	// check for valid configuration name and convert it into a config_id
	if (!$config_id = get_config_by_name($range_id, $type, $config_name)) {
		echo $sri_matches[1];
		echo $EXTERN_ERROR_MESSAGE;
		echo $sri_matches[3];
		exit;
	}
}
elseif (!$config_id) {
	// check for standard configuration
	if ($id = get_standard_config($range_id, $type))
		$config_id = $id;
	else {
		// use default configuration
		$default = "DEFAULT";
		$config_id = "";
	}
}

// if there is no global_id or global_name, take the DEFAULT global configuration
if ($global_name) {
	// check for valid configuration name and convert it into a config_id
	if (!$global_id = get_config_by_name($range_id, $type, $config_name)) {
		echo $sri_matches[1];
		echo $EXTERN_ERROR_MESSAGE;
		echo $sri_matches[3];
		exit;
	}
}
elseif (!$global_id) {
	// check for standard configuration
	if ($id = get_global_config($range_id))
		$global_id = $id;
	else {
		// use no global configuration
		$global_id = NULL;
	}
}

// sem == -1: show data from last semester
// sem == +1: show data from next semester
// other values: show data from current semester
$now = time();
foreach ($all_semester as $key => $sem_record) {
	if ($now >= $sem_record["beginn"] && $now <= $sem_record["ende"]) {
		$current = $key;
		break;
	}
}
if ($sem_offset == "-1") {
	$start = $all_semester[$current - 1]["beginn"];
	$end = $all_semester[$current - 1]["ende"];
}
elseif ($sem_offset == "+1") {
	$start = $all_semester[$current + 1]["beginn"];
	$end = $all_semester[$current + 1]["ende"];
}
else {
	$start = $all_semester[$current]["beginn"];
	$end = $all_semester[$current]["ende"];
}

// all parameters ok, instantiate module and print data
foreach ($EXTERN_MODULE_TYPES as $type) {
	if ($type["module"] == $module_name) {
		// Vorläufiger Bugfix
		$class_name = "ExternModule" . $module_name;
		require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/modules/$class_name.class.php");
		$module_obj =& new ExternModule($range_id, $module_name, $config_id, $default, $global_id);
}

$args = $module_obj->getArgs();
for ($i = 0; $i < sizeof($args); $i++)
	$arguments[$args[$i]] = $$args[$i];

echo $sri_matches[1];
$module_obj->printout($arguments);
echo $sri_matches[3];

?>
