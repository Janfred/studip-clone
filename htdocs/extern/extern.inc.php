<?
/**
* extern.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern
* @package	extern.inc.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern.inc.php
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


require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/ExternModule.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/extern_functions.inc.php");

$default = "";

// there is a page_url, switch to the sri-interface
if ($page_url) {
	require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/sri.inc.php");
	exit;
}

// range_id and module are always necessary
if ($range_id && $module) {
	$module = ucfirst(strtolower($module));
	
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
	
	// the module itself validates the rest
}
else {
	// without a range_id and a module-name there's no chance to printout data
	// except an error message
	echo $EXTERN_ERROR_MESSAGE;
	exit;
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

$args = $module_obj->getArgs();
for ($i = 0; $i < sizeof($args); $i++)
	$arguments[$args[$i]] = $$args[$i];

if ($preview)
	$module_obj->printoutPreview();
else
	$module_obj->printout($arguments);

?>
