<?
/**
* evaluate_values.php
* 
* handles all values, which are sent from the resources-management
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup	resources
* @module		ScheduleWeek.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ScheduleWeek.class.php
// Auswerten der Werte aus der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>
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


//a temp session-variable...
$sess->register("new_assign_object");

/*****************************************************************************
Functions...
/*****************************************************************************/

//a small helper function to close all the kids
function closeStructure ($resource_id) {
	global $resources_data;
	$db = new DB_Seminar;
	
	unset($resources_data["structure_opens"][$resource_id]);
	$query = sprintf ("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' ", $resource_id);
	$db->query($query);
	while ($db->next_record()) {
		closeStructure ($db->f("resource_id"));
	}
}

//a small helper function to update some data of the tree-structure (after move something)
function updateStructure ($resource_id, $root_id, $level) {
	$db = new DB_Seminar;
	$query = sprintf ("UPDATE resources_objects SET root_id = '%s', level='%s' WHERE resource_id = '%s' ", $root_id, $level, $resource_id);
	$db->query($query);
	
	$query = sprintf ("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' ", $resource_id);
	$db->query($query);
	while ($db->next_record()) {
		closeStructure ($db->f("resource_id"), $root_id, $level+1);
	}
}

/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

//got a fresh session?
if ((sizeof ($_REQUEST) == 1) && (!$view)) {
	$resources_data='';
	$resources_data["view"]="resources";
	$resources_data["view_mode"]=FALSE;
}

//a dirty trick to prevent sometimes chaos ;-)
if ((sizeof ($_REQUEST) == 2) && ($view == "view_schedule")) {
	$resources_data["view_mode"]=FALSE;
}

//get views/view_modes
if ($view)
	 $resources_data["view"]=$view;
if ($view_mode)	
	$resources_data["view_mode"]=$view_mode;
if (strpos($view, "openobject") !== FALSE)
	$resources_data["view_mode"]="oobj";

//Open a level/resource
if ($structure_open) {
	$resources_data["structure_opens"][$structure_open] =TRUE;
	$resources_data["actual_object"]=$structure_open;
}

if ($edit_object)
	$resources_data["actual_object"]=$edit_object;


//Select an object to work with
if ($actual_object) {
	$resources_data["actual_object"]=$actual_object;
}

//Close a level/resource
if ($structure_close)
	closeStructure ($structure_close);

//switch to move mode
if ($pre_move_object) {
	$resources_data["move_object"]=$pre_move_object;
}

//Listenstartpunkt festlegen
if ($open_list) {
	$resources_data["list_open"]=$open_list;
	$resources_data["view"]="_lists";
	}

if ($recurse_list) 
	$resources_data["list_recurse"]=TRUE;

if ($nrecurse_list) 
	$resources_data["list_recurse"]=FALSE;

//Neue Hierachieebene oder Unterebene anlegen
if ($resources_data["view"]=="create_hierarchie" || $create_hierachie_level) {
	if ($resources_data["view"]=="create_hierarchie") {
		$newHiearchie=new ResourceObject("Neue Hierachie", "Dieses Objekt kennzeichnet eine Hierachie und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', '', "0", '', $user->id);
	} elseif ($create_hierachie_level) {
		$parent_Object=new ResourceObject($create_hierachie_level);
		$newHiearchie=new ResourceObject("Neue Hierachieebene", "Dieses Objekt kennzeichnet eine neue Hierachieebene und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', $parent_Object->getRootId(), $create_hierachie_level, '', $user->id);
	}
	$newHiearchie->create();
	$edit_structure_object=$newHiearchie->id;
	$resources_data["structure_opens"][$newHiearchie->id] =TRUE;
	$resources_data["actual_object"]=$newHiearchie->getId();	
	$resources_data["view"]="resources";
	}

//Neues Objekt anlegen
if ($create_object) {
	$parent_Object=new ResourceObject($create_object);
	$new_Object=new ResourceObject("Neues Objekt", "Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen."
					, FALSE, FALSE, $parent_Object->getRootId(), $create_object, "0", $user->id);
	$new_Object->create();
	$resources_data["view"]="edit_object_properties";
	$resources_data["actual_object"]=$new_Object->getId();
	}

//Object loeschen
if ($kill_object) {
	$ObjectPerms = new ResourcesObjectPerms($kill_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$killObject=new ResourceObject($kill_object);
		if ($killObject->delete())
		 	$msg -> addMsg(7);
		$resources_data["view"]="resources";
	} else {
		$msg->displayMsg(1);
		die;
	}
}

//move an object
if ($target_object) {
	$ObjectPerms = new ResourcesObjectPerms($kill_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		if ($target_object != $resources_data["move_object"]) {
			//we want to move an object, so we have first to check if we want to move a object in a subordinated object
			$db->query ("SELECT parent_id FROM resources_objects WHERE resource_id = '$target_object'");
			while ($db->next_record()) {
				if ($db->f("parent_id") == $resources_data["move_object"])
					$target_is_child=TRUE;
				$db->query ("SELECT parent_id FROM resources_objects WHERE resource_id = '".$db->f("parent_id")."' ");
			}
			if (!$target_is_child) {
				$db->query ("UPDATE resources_objects SET parent_id='$target_object' WHERE resource_id = '".$resources_data["move_object"]."' ");
				$db->query ("SELECT root_id, level FROM resources_objects WHERE resource_id = '$target_object'");
				$db->next_record();
				//set the correct root_id's and levels
				updateStructure($resources_data["move_object"], $db->f("root_id"), $db->f("level")+1);
				$resources_data["structure_opens"][$resources_data["move_object"]] =TRUE;
				$resources_data["structure_opens"][$target_object] =TRUE;				
				if ($db->nf()) {
					$msg -> addMsg(9);
				}
			}
		}
		unset($resources_data["move_object"]);
	} else {
		$msg->displayMsg(1);
		die;
	}	
}

//Name und Beschreibung aendern
if ($change_structure_object) {
	$ObjectPerms = new ResourcesObjectPerms($change_structure_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject=new ResourceObject($change_structure_object);
		$changeObject->setName($change_name);
		$changeObject->setDescription($change_description);
		if ($changeObject->store())
			$msg -> addMsg(6);
	} else {
		$msg->displayMsg(1);
		die;
	}
	$resources_data["view"]="resources";
	$resources_data["actual_object"]=$change_structure_object;
}

//Objektbelegung erstellen/aendern
if ($change_object_schedules) {
	//load the perms
	$ObjectPerms = new ResourcesObjectPerms($change_schedule_resource_id);
	if (($ObjectPerms->getUserPerm () != "admin") && ($change_object_schedules != "NEW") || ($change_schedule_assign_user_id))
		$ObjectPerms = new AssignObjectPerms($change_object_schedules);
	
	if ($ObjectPerms->getUserPerm () == "autor" || $ObjectPerms->getUserPerm () == "admin") {
		if ($kill_assign_x) {
			$killAssign=new AssignObject($change_object_schedules);
			$killAssign->delete();
		} else {
			if ($change_object_schedules == "NEW")
				$change_schedule_id=FALSE;
			else
				$change_schedule_id=$change_object_schedules;
			
			if ($reset_search_user_x)
				$search_string_search_user=FALSE;

			if (($send_search_user_x) && ($submit_search_user !="FALSE") && (!$reset_search_user_x)) {
				//Check if this user is able to reach the resource (and this assign), to provide, that the owner of the resources foists assigns to others
				$ForeignObjectPerms = new ResourcesObjectPerms($change_schedule_resource_id, $submit_search_user);
				//echo 
				if (($ForeignObjectPerms->getUserPerm() == "autor") || ($ForeignObjectPerms-> getUserPerm() == "admin"))
					$change_schedule_assign_user_id=$submit_search_user;
				else
					$msg -> addMsg(2);
			}

			//the user send infinity repeat (until date) as empty field, but it's -1 in the db
			if (($change_schedule_repeat_quantity_infinity) && (!$change_schedule_repeat_quantity))
				$change_schedule_repeat_quantity=-1;

			//create timestamps
			if ($change_schedule_year) {
				$change_schedule_begin=mktime($change_schedule_start_hour, $change_schedule_start_minute, 0, $change_schedule_month, $change_schedule_day, $change_schedule_year);
				$change_schedule_end=mktime($change_schedule_end_hour, $change_schedule_end_minute, 0, $change_schedule_month, $change_schedule_day, $change_schedule_year);
			}
	
			if ($change_schedule_repeat_end_year)
				$change_schedule_repeat_end=mktime(23, 59, 59, $change_schedule_repeat_end_month, $change_schedule_repeat_end_day, $change_schedule_repeat_end_year);

			if ($change_schedule_repeat_sem_end)
				foreach ($SEMESTER as $a)	
					if (($change_schedule_begin >= $a["beginn"]) &&($change_schedule_begin <= $a["ende"]))
						$change_schedule_repeat_end=$a["vorles_ende"];

			//create repeatdata

			//repeat = none
			if ($change_schedule_repeat_none_x) {
				$change_schedule_repeat_month_of_year='';
				$change_schedule_repeat_day_of_month='';
				$change_schedule_repeat_week_of_month='';
				$change_schedule_repeat_day_of_week='';
				$change_schedule_repeat_quantity='';
				$change_schedule_repeat_interval='';	
			}

			//repeat = year
			if ($change_schedule_repeat_year_x) {
				$change_schedule_repeat_month_of_year=date("n", $change_schedule_begin);
				$change_schedule_repeat_day_of_month=date("j", $change_schedule_begin);
				$change_schedule_repeat_week_of_month='';
				$change_schedule_repeat_day_of_week='';
				if (!$change_schedule_repeat_quantity	)
					$change_schedule_repeat_quantity=-1;
				if (!$change_schedule_repeat_interval)
					$change_schedule_repeat_interval=1;
			}

			//repeat = month
			if ($change_schedule_repeat_month_x)
				if (!$change_schedule_repeat_week_of_month) {
					$change_schedule_repeat_month_of_year='';
					$change_schedule_repeat_day_of_month=date("j", $change_schedule_begin);
					$change_schedule_repeat_week_of_month='';
					$change_schedule_repeat_day_of_week='';
					if (!$change_schedule_repeat_quantity	)
						$change_schedule_repeat_quantity=-1;
					if (!$change_schedule_repeat_interval)
						$change_schedule_repeat_interval=1;
				}

			//repeat = week
			if ($change_schedule_repeat_week_x) {
				$change_schedule_repeat_month_of_year='';
				$change_schedule_repeat_day_of_month='';
				$change_schedule_repeat_week_of_month='';
				$change_schedule_repeat_quantity='';
				if (!$change_schedule_repeat_day_of_week)
					$change_schedule_repeat_day_of_week=1;
				if (!$change_schedule_repeat_quantity	)
					$change_schedule_repeat_quantity=-1;
				if (!$change_schedule_repeat_interval)
					$change_schedule_repeat_interval=1;
			}

			//repeat = day
			if ($change_schedule_repeat_day_x) {
				$change_schedule_repeat_month_of_year='';
				$change_schedule_repeat_day_of_month='';
				$change_schedule_repeat_week_of_month='';
				$change_schedule_repeat_quantity='';
				$change_schedule_repeat_day_of_week='';
				if (!$change_schedule_repeat_quantity	)
					$change_schedule_repeat_quantity=-1;
				if (!$change_schedule_repeat_interval)
					$change_schedule_repeat_interval=1;
			}

			//repeat days, only if week°
			if ($change_schedule_repeat_day1_x)
				$change_schedule_repeat_day_of_week=1;
			if ($change_schedule_repeat_day2_x)
				$change_schedule_repeat_day_of_week=2;
			if ($change_schedule_repeat_day3_x)
				$change_schedule_repeat_day_of_week=3;
			if ($change_schedule_repeat_day4_x)
				$change_schedule_repeat_day_of_week=4;
			if ($change_schedule_repeat_day5_x)
				$change_schedule_repeat_day_of_week=5;
			if ($change_schedule_repeat_day6_x)
				$change_schedule_repeat_day_of_week=6;
			if ($change_schedule_repeat_day7_x)
				$change_schedule_repeat_day_of_week=7;

			//give data to the assignobject
			$changeAssign=new AssignObject(
				$change_schedule_id,
				$change_schedule_resource_id,
				$change_schedule_assign_user_id,
				$change_schedule_user_free_name,
				$change_schedule_begin,
				$change_schedule_end,
				$change_schedule_repeat_end,
				$change_schedule_repeat_quantity,
				$change_schedule_repeat_interval,
				$change_schedule_repeat_month_of_year,
				$change_schedule_repeat_day_of_month, 
				$change_schedule_repeat_month,
				$change_schedule_repeat_week_of_month,
				$change_schedule_repeat_day_of_week,
				$change_schedule_repeat_week);

			if (($change_object_schedules == "NEW") || ($new_assign_object)) {
				if (($change_schedule_assign_user_id) || ($change_schedule_user_free_name))
					$overlaps = $changeAssign->checkOverlap();
				if (!$overlaps) {
					if ($changeAssign->create()) {
						$assign_id=$changeAssign->getId();
						$msg->addMsg(3);
						$new_assign_object='';
					} else {
						if ((!$do_search_user_x) && (!$reset_search_user_x))
							$msg->addMsg(10);					
						$new_assign_object=serialize($changeAssign);
					}
				} else
					$msg->addMsg(11);
			} else {
				if (($change_schedule_assign_user_id) || ($change_schedule_user_free_name))
					$overlaps = $changeAssign->checkOverlap();
				if (!$overlaps) {
					$changeAssign->chng_flag=TRUE;
					if ($changeAssign->store()) {
						$msg->addMsg(4);
						}
					$assign_id=$changeAssign->getId();
				} else
					$msg->addMsg(11);
			}
		}
	} else {
		$msg->displayMsg(1);
		die;
	}
}

//Objekteigenschaften aendern
if ($change_object_properties) {
	$ObjectPerms = new ResourcesObjectPerms($change_object_properties);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject=new ResourceObject($change_object_properties);
		$changeObject->setName($change_name);
		$changeObject->setDescription($change_description);
		$changeObject->setCategoryId($change_category_id);
		$changeObject->setParentBind($change_parent_bind);
	
		//Properties loeschen
		$changeObject->flushProperties();
	
		//Eigenschaften neu schreiben
		$props_changed=FALSE;
		if (is_array($change_property_val))
			foreach ($change_property_val as $key=>$val) {
				if ((substr($val, 0, 4) == "_id_") && (substr($change_property_val[$key+1], 0, 4) != "_id_"))
					if ($changeObject->storeProperty(substr($val, 4, strlen($val)), $change_property_val[$key+1]))
						$props_changed=TRUE;
			}
	
		//Object speichern
		if (($changeObject->store()) || ($props_changed))
		 	$msg -> addMsg(6);
	} else {
		$msg->displayMsg(1);
		die;
	}
	
	$resources_data["view"]="edit_object_properties";
}

//Objektberechtigungen aendern
if ($change_object_perms) {
	$ObjectPerms = new ResourcesObjectPerms($change_object_perms);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject=new ResourceObject($change_object_perms);
	
		if (is_array($change_user_id))
			foreach ($change_user_id as $key=>$val) {
				if ($changeObject->storePerms($val, $change_user_perms[$key]))
					$perms_changed=TRUE;
			}

		if ($delete_user_perms)
			if ($changeObject->deletePerms($delete_user_perms))
				$perms_changed=TRUE;
	
		if ($reset_search_owner_x)
			$search_string_search_owner=FALSE;

		if ($reset_search_perm_user_x)
			$search_string_search_perm_user=FALSE;
	
		if (($send_search_owner_x) && ($submit_search_owner !="FALSE") && (!$reset_search_owner_x)) 
 			$changeObject->setOwnerId($submit_search_owner);
	
		if (($send_search_perm_user_x) && ($submit_search_perm_user !="FALSE") && (!$reset_search_perm_user_x))
			if ($changeObject->storePerms($submit_search_perm_user))
				$perms_changed=TRUE;
	
		//Object speichern
		if (($changeObject->store()) || ($perms_changed))
			$msg->addMsg(8);
	} else {
		$msg->displayMsg(1);
		die;
	}
	$resources_data["view"]="edit_object_perms";
}

//Typen bearbeiten
if (($add_type) || ($delete_type) || ($delete_type_property_id) || ($change_categories)) {
	//if ($ObjectPerms->getUserPerm () == "admin") { --> da muss der Ressourcen Root check hin °
		if ($delete_type) {
			$db->query("DELETE FROM resources_categories WHERE category_id ='$delete_type'");
		}
	
		if (($add_type) && ($_add_type_x)) {
			$id=md5(uniqid("Sommer2002"));
			$db->query("INSERT INTO resources_categories SET category_id='$id', name='$add_type', description='$insert_type_description' ");
			if ($db->affected_rows())
				$created_category_id=$id;
		}
	
		if ($delete_type_property_id) {
			$db->query("DELETE FROM resources_categories_properties WHERE category_id='$delete_type_category_id' AND property_id='$delete_type_property_id' ");
		}

		if (is_array($change_category_name)) foreach ($change_category_name as $key=>$val) {
			$query = sprintf ("UPDATE  resources_categories SET name='%s', iconnr='%s' WHERE category_id = '%s'", $change_category_name[$key], $change_category_iconnr[$key], $key);
			$db->query($query);

			if (${"change_category_add_property".$key."_x"}) {
				$db->query("INSERT INTO resources_categories_properties SET category_id='$key', property_id='$add_type_property_id[$key]' ");
			}
		}
	/*} else {
		$msg->displayMsg(1);
		die;
	}*/
}

//Eigenschaften bearbeiten
if (($add_property) || ($delete_property) || ($change_properties)) {
	//if ($ObjectPerms->getUserPerm () == "admin") { { --> da muss der Ressourcen Root check hin °
		if ($delete_property) {
			$db->query("DELETE FROM resources_properties WHERE property_id ='$delete_property' ");
		}
	
		if ($add_property) {
			if ($add_property_type=="bool")
				$options="vorhanden";
			if ($add_property_type=="select")
				$options="Option 1;Option 2;Option 3";
			$id=md5(uniqid("Regen2002"));
			$db->query("INSERT INTO resources_properties SET options='$options', property_id='$id', name='$add_property', description='$insert_property_description', type='$add_property_type' ");
		}
	
		if (is_array($change_property_name)) foreach ($change_property_name as $key=>$val) {
			if ($send_property_type[$key] == "select") {
				$tmp_options=explode (";",$send_property_select_opt[$key]);
				$options='';
				$i=0;
				if (is_array($tmp_options))
					foreach ($tmp_options as $a) {
						if ($i)
							$options.=";";
						$options.=trim($a);						
						$i++;
					}
			} elseif ($send_property_type[$key] == "bool") {
				$options=$send_property_bool_desc[$key];
			}
			else
				$options='';
			
			if (!$options)
				if ($send_property_type[$key] == "bool")
					$options="vorhanden";
				elseif ($send_property_type[$key] == "select")
					$options="Option 1;Option 2;Option 3";	
				
			$db->query("UPDATE resources_properties SET name='$change_property_name[$key]', options='$options', type='$send_property_type[$key]' WHERE property_id='$key' ");
		}
	/*} else {
		$msg->displayMsg(1);
		die;
	}*/
}

//Globale Perms bearbeiten
if (($add_root_user) || ($delete_root_user_id)){
	//if ($ObjectPerms->getUserPerm () == "admin") { { --> da muss der Ressourcen Root check hin °
		if ($reset_search_root_user_x)
			$search_string_search_root_user=FALSE;

		if (($send_search_root_user_x) && ($submit_search_root_user !="FALSE") && (!$reset_search_root_user_x))
			$db->query("INSERT resources_user_resources SET user_id='$submit_search_root_user', resource_id='all', perms='autor' ");

		if ($delete_root_user_id)		
			$db->query("DELETE FROM resources_user_resources WHERE user_id='$delete_root_user_id' AND resource_id='all' ");
	
		if (is_array ($change_root_user_id))
			foreach ($change_root_user_id as $key => $val) {
				$db->query("UPDATE resources_user_resources SET perms='".$change_root_user_perms[$key]."' WHERE user_id='$val' ");
			}
	/*} else {
		$msg->displayMsg(1);
		die;
	}*/
}

//evaluate the command from schedule navigator
if ($resources_data["view"]=="view_schedule" || $resources_data["view"]=="openobject_schedule") {
	if ($next_week)
		$resources_data["schedule_week_offset"]++;
	if ($previous_week)
		$resources_data["schedule_week_offset"]--;
	if ($start_time) {
		$resources_data["schedule_start_time"] = $start_time;
		$resources_data["schedule_end_time"] = $resources_data["schedule_start_time"] + (7 * 24 * 60 * 60);		
		$resources_data["schedule_mode"] = "graphical";
	}
	elseif ($navigate) {
		$resources_data["schedule_length_factor"] = $schedule_length_factor;
		$resources_data["schedule_length_unit"] = $schedule_length_unit;
		$resources_data["schedule_week_offset"] = 0;
		$resources_data["schedule_start_time"] = mktime (0,0,0,$schedule_begin_month, $schedule_begin_day, $schedule_begin_year);
		if (($start_list_x) || (($jump_x) && ($resources_data["schedule_mode"] == "list"))){
			$resources_data["schedule_mode"] = "list";
			if ($resources_data["schedule_start_time"] < 1)
				$resources_data["schedule_start_time"] = mktime (0, 0, 0, date("n", time()), date("j", time()), date("Y", time()));
			switch ($resources_data["schedule_length_unit"]) {
				case "y" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"]), date("Y",$resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"]);
				break;
				case "m" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"], date("j", $resources_data["schedule_start_time"]), date("Y",$resources_data["schedule_start_time"]));
				break;
				case "w" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"])+($resources_data["schedule_length_factor"] * 7), date("Y",$resources_data["schedule_start_time"]));
				break;
				case "d" :
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"], date("Y",$resources_data["schedule_start_time"]));
				break;
			}
			if ($resources_data["schedule_end_time"]  < 1)
				$resources_data["schedule_end_time"] = mktime (23, 59, 0, date("n", time()), date("j", time())+1, date("Y", time()));
		} elseif (($start_graphical_x) || (!$resources_data["schedule_mode"]) || (($jump_x) && ($resources_data["schedule_mode"] == "graphical"))) {
			$resources_data["schedule_end_time"] = $resources_data["schedule_start_time"] + (7 * 24 * 60 * 60);
			$resources_data["schedule_mode"] = "graphical";			
		}
	} else {
		if (!$resources_data["schedule_start_time"])
			$resources_data["schedule_start_time"] = mktime (0, 0, 0, date("n", time()), date("j", time()), date("Y", time()));
		if (!$resources_data["schedule_end_time"])
			$resources_data["schedule_end_time"] = mktime (23, 59, 0, date("n", time()), date("j", time())+7, date("Y", time()));
		if (!$resources_data["schedule_mode"])
			$resources_data["schedule_mode"] = "graphical";		
	}
}

//handle commands from the search 'n' browse modul
if ($resources_data["view"]=="search") {
	if ($open_level)
		 $resources_data["browse_open_level"]=$open_level;

	if ($mode == "properties")
		$resources_data["search_mode"]="properties";
	
	if ($mode == "browse")
		$resources_data["search_mode"]="browse";
	
	if ($start_search_x) {
		unset($resources_data["search_array"]);
		$resources_data["search_array"]["search_exp"]=$search_exp;
		if (is_array($search_property_val))
			foreach ($search_property_val as $key=>$val) {
				if ((substr($val, 0, 4) == "_id_") && (substr($search_property_val[$key+1], 0, 4) != "_id_") && ($search_property_val[$key+1]))
					$resources_data["search_array"]["properties"][substr($val, 4, strlen($val))]=$search_property_val[$key+1];
		}
	}
	
	if ($reset) {
		unset($resources_data["browse_open_level"]);
		unset($resources_data["search_array"]);
	}
}

//show object, this object will be edited or viewed
if ($show_object)
	$resources_data["actual_object"]=$show_object;

//if ObjectPerms for actual user and actual object are not loaded, load them!
if ($ObjectPerms) {
	if (($ObjectPerms->getId() == $resources_data["actual_object"]) && ($ObjectPerms->getUserId()  == $user->id))
		$ActualObjectPerms = $ObjectPerms;	
	 else
		$ActualObjectPerms = new ResourcesObjectPerms($resources_data["actual_object"]);
} else
	$ActualObjectPerms = new ResourcesObjectPerms($resources_data["actual_object"]);
	
//edit or view object
if ($edit_object) {
	if ($ActualObjectPerms->getUserPerm() == "admin")
		$resources_data["view"]="edit_object_properties";
	else
		$resources_data["view"]="view_details";
}

?>