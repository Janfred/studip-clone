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
// evaluate_values.php
// Auswerten der Werte aus der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObject.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/AssignObjectPerms.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."/dates.inc.php");

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
handle the view-logic
/*****************************************************************************/

//got a fresh session?
if ((sizeof ($_REQUEST) == 1) && (!$view) && (!$quick_view)) {
	$resources_data='';
	$resources_data["view"]="resources";
	$resources_data["view_mode"]=FALSE;
	closeObject();
}

//get views/view_modes
if ($view)
	$resources_data["view"]=$view;
if ($view_mode)	
	$resources_data["view_mode"]=$view_mode;
if (strpos($view, "openobject") !== FALSE)
	$resources_data["view_mode"]="oobj";

//if quick_view, we take this view (only one page long, until the next view is given!)
if ($quick_view)
	$view = $quick_view;
//or we take back the persitant view from $resources_data
else
	$view = $resources_data["view"];
	
//we do so for the view_mode too
if ($quick_view_mode)
	$view_mode = $quick_view_mode;
else
	$quick_view_mode = $resources_data["view_mode"];

//reset edit the assign
if ((sizeof ($_REQUEST) == 2) && (($view == "edit_object_assign") || ($view == "openobject_assign"))) {
	$new_assign_object=FALSE;
}
if ((sizeof ($_REQUEST) == 3) && ($edit_assign_object) && (($view == "edit_object_assign") || ($view == "openobject_assign"))) {
	$new_assign_object=FALSE;
}
if ($cancel_edit_assign) {
	$new_assign_object=FALSE;
	$resources_data["actual_assign"]=FALSE;
}

//send the user to index, if he want to use studip-object based modul but has no object set!
if (($view=="openobject_main") || ($view=="openobject_details") || ($view=="openobject_assign") || ($view=="openobject_schedule"))
	if (!$SessSemName[1]) {
		checkObject();
		die;	
	}
	
//we take a search as long with us, as no other overview modul is used
if (($view=="openobject_main") || ($view=="_lists") || ($view=="lists") || ($view=="resources") || ($view=="_resources"))
	$resources_data["search_array"]='';



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

//cancel move mode
if ($cancel_move) {
	$resources_data["move_object"]='';
}

//Listenstartpunkt festlegen
if ($open_list) {
	$resources_data["list_open"]=$open_list;
	$resources_data["view"]="_lists";
	$view = $resources_data["view"];
	}

if ($recurse_list) 
	$resources_data["list_recurse"]=TRUE;

if ($nrecurse_list) 
	$resources_data["list_recurse"]=FALSE;
	
//Create ClipBoard-Class, if needed
if (($view == "search") || ($view == "edit_request")) {
	require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/ClipBoard.class.php");

	$clipObj = & ClipBoard::GetInstance("search");
	$clipFormObj =& $clipObj->getFormObject();
	
	if ($view == "edit_request") {
		$clipFormObj->form_fields['clip_cmd']['options'][] = array('name' => _("In aktueller Anfrage mit ber&uuml;cksichtigen"), 'value' => 'add');
		
		if ($clipFormObj->getFormFieldValue("clip_cmd") == "add") {
			$marked_clip_ids = $clipFormObj->getFormFieldValue("clip_content");
			$msg->addMsg(32);
		}
	}
	
	if ($clip_in)
		$clipObj->insertElement($clip_in, "res");
	if ($clip_out)
		$clipObj->deleteElement($clip_out);
	$clipObj->doClipCmd();
}


//Neue Hierachieebene oder Unterebene anlegen
if ($view == "create_hierarchie" || $create_hierachie_level) {
	if ($view == "create_hierarchie") {
		$newHiearchie =& ResourceObject::Factory("Neue Hierachie", "Dieses Objekt kennzeichnet eine Hierachie und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', '', '', $user->id);
	} elseif ($create_hierachie_level) {
		$parent_Object =& ResourceObject::Factory($create_hierachie_level);
		$newHiearchie =& ResourceObject::Factory("Neue Hierachieebene", "Dieses Objekt kennzeichnet eine neue Hierachieebene und kann jederzeit in eine Ressource umgewandelt werden"
						, '', $parent_Object->getRootId(), $create_hierachie_level, '', $user->id);
	}
	$newHiearchie->create();
	$edit_structure_object=$newHiearchie->id;
	$resources_data["structure_opens"][$newHiearchie->id] =TRUE;
	$resources_data["actual_object"]=$newHiearchie->getId();	
	$resources_data["view"]="resources";
	$view = $resources_data["view"];
	}

//Neues Objekt anlegen
if ($create_object) {
	$parent_Object =& ResourceObject::Factory($create_object);
	$new_Object=& ResourceObject::Factory("Neues Objekt", "Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen."
					, FALSE, $parent_Object->getRootId(), $create_object, "0", $user->id);
	$new_Object->create();
	$resources_data["view"]="edit_object_properties";
	$view = $resources_data["view"];
	$resources_data["actual_object"]=$new_Object->getId();
	}


//Object loeschen
if ($kill_object) {
	$ObjectPerms =& ResourceObjectPerms::Factory($kill_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$killObject =& ResourceObject::Factory($kill_object);
		if ($killObject->delete())
		 	$msg -> addMsg(7);
		$resources_data["view"]="resources";
		$view = $resources_data["view"];
	} else {
		$msg->addMsg(1);
	}
}

//cancel a just created object
if ($cancel_edit) {
	$ObjectPerms =& ResourceObjectPerms::Factory($cancel_edit);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$cancel_edit =& ResourceObject::Factory($cancel_edit);
		$cancel_edit->delete();
		$resources_data["view"]="resources";
		$view = $resources_data["view"];
	} else {
		$msg->addMsg(1);
	}
}


//move an object
if ($target_object) {
	$ObjectPerms =& ResourceObjectPerms::Factory($target_object);
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
		$msg->addMsg(1);
	}	
}

//Name und Beschreibung aendern
if ($change_structure_object) {
	$ObjectPerms =& ResourceObjectPerms::Factory($change_structure_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject =& ResourceObject::Factory($change_structure_object);
		$changeObject->setName($change_name);
		$changeObject->setDescription($change_description);
		if ($changeObject->store())
			$msg -> addMsg(6);
	} else {
		$msg->addMsg(1);
	}
	$resources_data["view"]="resources";
	$view = $resources_data["view"];
	$resources_data["actual_object"]=$change_structure_object;
}

/*****************************************************************************
edit/add assigns
/*****************************************************************************/

//Objektbelegung erstellen/aendern
if ($change_object_schedules) {
	require_once ($ABSOLUTE_PATH_STUDIP."calendar_functions.inc.php"); //needed for extended checkdate
	require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/SemesterData.class.php");
	
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	//load the object perms
	$ObjectPerms =& ResourceObjectPerms::Factory($change_schedule_resource_id);
	
	//in some case, we load the perms from the assign object, if it has an owner
	if (($ObjectPerms->getUserPerm() != "admin") && ($change_object_schedules != "NEW") && (!$new_assign_object)) {
		//load the assign-object perms of a saved object
		$SavedStateAssignObject = new AssignObject($change_object_schedules);
		if ($SavedStateAssignObject->getAssignUserId())
			$ObjectPerms = new AssignObjectPerms($change_object_schedules);
	}
	
	if (($ObjectPerms->havePerm("admin")) && ($change_meta_to_single_assigns_x)) {
		$assObj = new AssignObject($change_object_schedules);	
		$semResAssign = new VeranstaltungResourcesAssign($assObj->getAssignUserId());
		$semResAssign->deleteAssignedRooms();
		
		$resultAssi = dateAssi ($assObj->getAssignUserId(), "insert", FALSE, FALSE, FALSE, FALSE);
		if ($resultAssi["changed"]) {
			$return_schedule = TRUE;
			header (sprintf("Location:resources.php?quick_view=%s&quick_view_mode=%s&show_msg=37", ($view_mode == "oobj") ? "openobject_schedule" : "view_schedule", $view_mode));
		}
	}

	if (($ObjectPerms->havePerm("admin")) && ($send_change_resource_x)) {
		$ChangeObjectPerms =& ResourceObjectPerms::Factory($select_change_resource);
		if ($ChangeObjectPerms->havePerm("tutor")) {
			$changeAssign=new AssignObject($change_object_schedules);
			$changeAssign->setResourceId($select_change_resource);
			$overlaps = $changeAssign->checkOverlap();
			if ($overlaps) {
				$msg->addMsg(11);
			} else {
				$changeAssign->store();
				$return_schedule = TRUE;
				header (sprintf("Location:resources.php?quick_view=%s&quick_view_mode=%s&show_msg=38&msg_resource_id=%s", ($view_mode == "oobj") ? "openobject_schedule" : "view_schedule", $view_mode, $select_change_resource));
			}
		} else
			$msg->addMsg(2);
	}

	if ($ObjectPerms->havePerm("autor")) {
		if ($kill_assign_x) {
			$killAssign=new AssignObject($change_object_schedules);
			$killAssign->delete();
			$new_assign_object='';
			$msg ->addMsg(5);
		} elseif (!$return_schedule) {
			if ($change_object_schedules == "NEW")
				$change_schedule_id=FALSE;
			else
				$change_schedule_id=$change_object_schedules;
			
			if ($reset_search_user_x)
				$search_string_search_user=FALSE;

			if (($send_search_user_x) && ($submit_search_user !="FALSE") && (!$reset_search_user_x)) {
				//Check if this user is able to reach the resource (and this assign), to provide, that the owner of the resources foists assigns to others
				$ForeignObjectPerms =& ResourceObjectPerms::Factory($change_schedule_resource_id, $submit_search_user); 
				if ($ForeignObjectPerms->havePerm("autor"))
					$change_schedule_assign_user_id=$submit_search_user;
				else
					$msg->addMsg(2);
			}

			//the user send infinity repeat (until date) as empty field, but it's -1 in the db
			if (($change_schedule_repeat_quantity_infinity) && (!$change_schedule_repeat_quantity))
				$change_schedule_repeat_quantity=-1;
				
			//check dates
			$illegal_dates=FALSE;
			if ((!check_date($change_schedule_month, $change_schedule_day, $change_schedule_year, $change_schedule_start_hour, $change_schedule_start_minute)) || 
				(!check_date($change_schedule_month, $change_schedule_day, $change_schedule_year, $change_schedule_end_hour, $change_schedule_end_minute))) {
				$illegal_dates=TRUE;
				$msg -> addMsg(17);				
			}

			//create timestamps
			if (!$illegal_dates) {
				$change_schedule_begin=mktime($change_schedule_start_hour, $change_schedule_start_minute, 0, $change_schedule_month, $change_schedule_day, $change_schedule_year);
				$change_schedule_end=mktime($change_schedule_end_hour, $change_schedule_end_minute, 0, $change_schedule_month, $change_schedule_day, $change_schedule_year);
				if ($change_schedule_begin > $change_schedule_end) {
					$illegal_dates=TRUE;
					$msg -> addMsg(20);				
				}
			}
	
			if (check_date($change_schedule_repeat_end_month, $change_schedule_repeat_end_day, $change_schedule_repeat_end_year))
				if ($change_schedule_repeat_mode == "sd")
					$change_schedule_repeat_end=mktime(date("G", $change_schedule_end), date("i", $change_schedule_end), 0, $change_schedule_repeat_end_month, $change_schedule_repeat_end_day, $change_schedule_repeat_end_year);
				else
					$change_schedule_repeat_end=mktime(23, 59, 59, $change_schedule_repeat_end_month, $change_schedule_repeat_end_day, $change_schedule_repeat_end_year);

			if ($change_schedule_repeat_sem_end)
				foreach ($all_semester as $a)	
					if (($change_schedule_begin >= $a["beginn"]) && ($change_schedule_begin <= $a["ende"]))
						$change_schedule_repeat_end=$a["vorles_ende"];
			
			//create repeatdata

			//repeat = none
			if ($change_schedule_repeat_none_x) {
				$change_schedule_repeat_end='';
				$change_schedule_repeat_month_of_year='';
				$change_schedule_repeat_day_of_month='';
				$change_schedule_repeat_week_of_month='';
				$change_schedule_repeat_day_of_week='';
				$change_schedule_repeat_quantity='';
				$change_schedule_repeat_interval='';	
			}


			//repeat = several days
			if ($change_schedule_repeat_severaldays_x) {
				$change_schedule_repeat_end = mktime(date("G", $change_schedule_end), date("i", $change_schedule_end), 0, date("n", $change_schedule_begin), date("j", $change_schedule_begin)+1, date("Y", $change_schedule_begin));
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
				$change_schedule_repeat_week_of_month,
				$change_schedule_repeat_day_of_week);
			
			//if isset quantity, we calculate the correct end date
			if ($changeAssign->getRepeatQuantity() >0)
				$changeAssign->setRepeatEnd($changeAssign->getRepeatEndByQuantity());
				
			//check repeat_end
			if (($changeAssign->getRepeatMode() != "na") && ($change_schedule_repeat_end_month) && ($change_schedule_repeat_end_day) && ($change_schedule_repeat_end_year)){
				if (!check_date($change_schedule_repeat_end_month, $change_schedule_repeat_end_day, $change_schedule_repeat_end_year)) {
					$illegal_dates=TRUE;
					$msg -> addMsg(18);
				}
				//repeat end schould not be bevor the begin
				if (!$illegal_dates) {
					if ($changeAssign->getEnd() > $changeAssign->getRepeatEnd()) {
						$changeAssign->setRepeatEnd($changeAssign->getBegin());
					}
				}
				//limit recurrences
				if (!$illegal_dates) {
					switch ($changeAssign->getRepeatMode()) {
						case "y" : if ((date("Y",$changeAssign->getRepeatEnd()) - date("Y", $changeAssign->getBegin())) > 10) {
									$illegal_dates=TRUE;
									$msg -> addMsg(21);
								}
						break;
						case "m" : if ((date("Y",$changeAssign->getRepeatEnd()) - date("Y", $changeAssign->getBegin())) > 10) {
									$illegal_dates=TRUE;
									$msg -> addMsg(22);
								}
						break;
						case "w" : if ((($changeAssign->getRepeatEnd() - $changeAssign->getBegin()) / (60 * 60 * 24 *7) / $changeAssign->getRepeatInterval()) > 50) {
									$illegal_dates=TRUE;
									$msg -> addMsg(23);
								}
						break;
						case "d" : if ((int)(($changeAssign->getRepeatEnd() - $changeAssign->getBegin()) / (60 * 60 * 24) / $changeAssign->getRepeatInterval()) > 100) {
									$illegal_dates=TRUE;
									$msg -> addMsg(24);
								}
						break;
					}
				}
			}

			if ($illegal_dates) {
				$new_assign_object=serialize($changeAssign);
			} elseif (($change_object_schedules == "NEW") || ($new_assign_object)) {
				if (($change_schedule_assign_user_id) || ($change_schedule_user_free_name))
					$overlaps = $changeAssign->checkOverlap();
				if (!$overlaps) {
					if ($changeAssign->create()) {
						$resources_data["actual_assign"]=$changeAssign->getId();
						$msg->addMsg(3);
						$new_assign_object='';
					} else {
						if ((!$do_search_user_x) && (!$reset_search_user_x))
							if ((!$change_schedule_assign_user_id) && ($change_schedule_user_free_name))
								$msg->addMsg(10);					
						$new_assign_object=serialize($changeAssign);
					}
				} else {
					$msg->addMsg(11);
					$new_assign_object=serialize($changeAssign);
				}
			} else {
				if (($change_schedule_assign_user_id) || ($change_schedule_user_free_name))
					$overlaps = $changeAssign->checkOverlap();
				if (!$overlaps) {
					$changeAssign->chng_flag=TRUE;
					if ($changeAssign->store()) {
						$msg->addMsg(4);
						$new_assign_object='';						
						}
					$resources_data["actual_assign"]=$changeAssign->getId();
				} else
					$msg->addMsg(11);
					
			}
		}
	} else {
		$msg->addMsg(1);
	}
}

//Objekteigenschaften aendern
if ($change_object_properties) {
	$ObjectPerms =& ResourceObjectPerms::Factory($change_object_properties);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject =& ResourceObject::Factory($change_object_properties);
		$changeObject->setName($change_name);
		$changeObject->setDescription($change_description);
		$changeObject->setCategoryId($change_category_id);
		$changeObject->setParentBind($change_parent_bind);
		$changeObject->setInstitutId($change_institut_id);
		
		if (getGlobalPerms($user->id) == "admin") {
			$changeObject->setMultipleAssign($change_multiple_assign);
		}

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
		$msg->addMsg(1);
	}
	
	$resources_data["view"]="edit_object_properties";
	$view = $resources_data["view"];
}

//Objektberechtigungen aendern
if ($change_object_perms) {
	$ObjectPerms =& ResourceObjectPerms::Factory($change_object_perms);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$changeObject =& ResourceObject::Factory($change_object_perms);
	
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
		
		if ((getGlobalPerms($user->id) == "admin") && ($changeObject->isRoom())) {
			if ($changeObject->isParent()) {
				if (($change_lockable) && (!$changeObject->isLockable()))
					$msg->addMsg(29, array($PHP_SELF, $changeObject->getId(), $PHP_SELF));
				elseif ((!$change_lockable) && ($changeObject->isLockable()))
					$msg->addMsg(30, array($PHP_SELF, $changeObject->getId(), $PHP_SELF));
			}
			$changeObject->setLockable($change_lockable);
		}
	
		//Object speichern
		if (($changeObject->store()) || ($perms_changed))
			$msg->addMsg(8);
	} else {
		$msg->addMsg(1);
	}
	$resources_data["view"]="edit_object_perms";
	$view = $resources_data["view"];
}

//set/unset lockable for a comlete hierarchy
if (($set_lockable_recursiv) || ($unset_lockable_recursiv)) {
	if (getGlobalPerms($user->id) == "admin") {
		changeLockableRecursiv($lock_resource_id, ($set_lockable_recursiv) ? TRUE : FALSE);
	} else {
		$msg->addMsg(1);
	}
	$resources_data["view"]="edit_object_perms";
	$view = $resources_data["view"];
}

//Typen bearbeiten
if (($add_type) || ($delete_type) || ($delete_type_property_id) || ($change_categories)) {
	if (getGlobalPerms ($user->id) == "admin") { //check for resources root or global root
		if ($delete_type) {
			$db->query("DELETE FROM resources_categories WHERE category_id ='$delete_type'");
		}
	
		if (($add_type) && ($_add_type_x)) {
			$id=md5(uniqid("Sommer2002",1));
			if ($resource_is_room)
				$resource_is_room = 1;
			$db->query("INSERT INTO resources_categories SET category_id='$id', name='$add_type', description='$insert_type_description', is_room='$resource_is_room' ");
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
		
		if (is_array($requestable)) {
			foreach ($requestable as $key=>$val) {
				if ((strpos($requestable[$key-1], "id1_")) &&  (strpos($requestable[$key], "id2_"))) {
					if ($requestable[$key+1] == "on")
						$req_num = 1;
					else
						$req_num = 0;
					$query = sprintf ("UPDATE resources_categories_properties SET requestable ='%s' WHERE category_id = '%s' AND property_id = '%s' ", $req_num, substr($requestable[$key-1], 5, strlen($requestable[$key-1])), substr($requestable[$key], 5, strlen($requestable[$key])));			
					$db->query($query);
				}
			}
		}
	} else {
		$msg->addMsg(25);
	}
}

//Eigenschaften bearbeiten
if (($add_property) || ($delete_property) || ($change_properties)) {
	if ($globalPerm == "admin") { //check for resources root or global root
		if ($delete_property) {
			$db->query("DELETE FROM resources_properties WHERE property_id ='$delete_property' ");
		}
	
		if ($add_property) {
			if ($add_property_type=="bool")
				$options="vorhanden";
			if ($add_property_type=="select")
				$options="Option 1;Option 2;Option 3";
			$id=md5(uniqid("Regen2002",1));
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
			
			$db->query("UPDATE resources_properties SET name='$change_property_name[$key]', options='$options', type='$send_property_type[$key]' WHERE property_id='$key' ");
		}
	} else {
		$msg->addMsg(25);
	}
}

//Globale Perms bearbeiten
if (($add_root_user) || ($delete_root_user_id)){
	if ($globalPerm == "admin") { //check for resources root or global root
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
	} else {
		$msg->addMsg(25);
	}
}

/*****************************************************************************
change settings
/*****************************************************************************/

//change settings
if ($change_global_settings) {
	if ($globalPerm == "admin") { //check for resources root or global root
		write_config("RESOURCES_LOCKING_ACTIVE", $locking_active);
		write_config("RESOURCES_ALLOW_ROOM_REQUESTS", $allow_requests);
		write_config("RESOURCES_ALLOW_CREATE_ROOMS", $allow_create_resources);
		write_config("RESOURCES_INHERITANCE_PERMS_ROOMS", $inheritance_rooms);
		write_config("RESOURCES_INHERITANCE_PERMS", $inheritance);
		write_config("RESOURCES_ENABLE_ORGA_CLASSIFY", $enable_orga_classify);
		write_config("RESOURCES_ENABLE_ORGA_ADMIN_NOTICE", $enable_orga_admin_notice);
		write_config("RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE", $allow_single_assign_percentage);
		write_config("RESOURCES_ALLOW_SINGLE_DATE_GROUPING", $allow_single_date_grouping);
	} else {
		$msg->addMsg(25);
	}
}	

//create a lock
if ($create_lock) {
	if ($globalPerm == "admin") { //check for resources root or global root
		$id = md5(uniqid("locks",1));
		$query = sprintf("INSERT INTO resources_locks SET lock_begin = '%s', lock_end = '%s', lock_id = '%s' ", 0, 0, $id);
		$db->query($query);
	
		$resources_data["lock_edits"][$id] = TRUE;
	} else {
		$msg->addMsg(25);
	}
}	

//edit a lock
if ($edit_lock) {
	if ($globalPerm == "admin") { //check for resources root or global root
		$resources_data["lock_edits"][$edit_lock] = TRUE;
	} else {
		$msg->addMsg(25);
	}
}

//edit locks
if (($lock_sent_x)) {
	if ($globalPerm == "admin") { //check for resources root or global root
		require_once ($ABSOLUTE_PATH_STUDIP."calendar_functions.inc.php"); //needed for extended checkdate
		
		foreach ($lock_id as $key=>$id) {
			$illegal_begin = FALSE;
			$illegal_end = FALSE;
	
			//checkdates
			if (!check_date($lock_begin_month[$key], $lock_begin_day[$key], $lock_begin_year[$key], $lock_begin_hour[$key], $lock_begin_min[$key])) {
				//$msg->addMsg(2);
				$illegal_begin=TRUE;
			} else
				$lock_begin = mktime($lock_begin_hour[$key],$lock_begin_min[$key],0,$lock_begin_month[$key], $lock_begin_day[$key], $lock_begin_year[$key]);
	
			if (!check_date($lock_end_month[$key], $lock_end_day[$key], $lock_end_year[$key], $lock_end_hour[$key], $lock_end_min[$key])) {
				//$msg -> addMsg(3);
				$illegal_end=TRUE;						
			} else
				$lock_end = mktime($lock_end_hour[$key],$lock_end_min[$key],0,$lock_end_month[$key], $lock_end_day[$key], $lock_end_year[$key]);
			
			if ((!$illegal_begin) && (!$illegal_end) && ($lock_begin < $lock_end)) {
				$query = sprintf("UPDATE resources_locks SET lock_begin = '%s', lock_end = '%s' WHERE lock_id = '%s' ", $lock_begin, $lock_end, $id);
				$db->query($query);
				
				if ($db->affected_rows()) {
					$msg->addMsg(27);
					unset($resources_data["lock_edits"][$id]);			
				}
			} else
				$msg->addMsg(26);
		}
	} else {
		$msg->addMsg(25);
	}
}

//kill a lock-time
if (($kill_lock)) {
	if ($globalPerm == "admin") { //check for resources root or global root
		$query = sprintf("DELETE FROM resources_locks WHERE lock_id = '%s' ", $kill_lock);
		$db->query($query);	
		if ($db->affected_rows()) {
			$msg->addMsg(28);
			unset($resources_data["lock_edits"][$kill_lock]);			
		}
	} else {
		$msg->addMsg(25);
	}
}

/*****************************************************************************
evaluate the commands from schedule navigator
/*****************************************************************************/
if ($view == "view_schedule" || $view == "openobject_schedule") {
	if ($next_week)
		$resources_data["schedule_week_offset"]++;
	if ($previous_week)
		$resources_data["schedule_week_offset"]--;
	if ($start_time) {
		$resources_data["schedule_start_time"] = $start_time;
		$resources_data["schedule_end_time"] = $resources_data["schedule_start_time"] + (7 * 24 * 60 * 60) + 59;		
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
					$resources_data["schedule_end_time"] =mktime(23,59,59,date("n",$resources_data["schedule_start_time"]), date("j", $resources_data["schedule_start_time"])+$resources_data["schedule_length_factor"]-1, date("Y",$resources_data["schedule_start_time"]));
				break;
			}
			if ($resources_data["schedule_end_time"]  < 1)
				$resources_data["schedule_end_time"] = mktime (23, 59, 59, date("n", time()), date("j", time())+1, date("Y", time()));
		} elseif (($start_graphical_x) || (!$resources_data["schedule_mode"]) || (($jump_x) && ($resources_data["schedule_mode"] == "graphical"))) {
			$resources_data["schedule_end_time"] = $resources_data["schedule_start_time"] + (7 * 24 * 60 * 60) + 59;
			$resources_data["schedule_mode"] = "graphical";			
		}
	} else {
		if (!$resources_data["schedule_start_time"])
			$resources_data["schedule_start_time"] = mktime (0, 0, 0, date("n", time()), date("j", time()), date("Y", time()));
		if (!$resources_data["schedule_end_time"])
			$resources_data["schedule_end_time"] = mktime (23, 59, 59, date("n", time()), date("j", time())+7, date("Y", time()));
		if (!$resources_data["schedule_mode"])
			$resources_data["schedule_mode"] = "graphical";		
	}
}

if (($show_repeat_mode) && ($send_schedule_repeat_mode_x)) {
	$resources_data["show_repeat_mode"] = $show_repeat_mode;
}

if ($time_range) {
	if ($time_range == "FALSE")
		$resources_data["schedule_time_range"] = '';
	else
		$resources_data["schedule_time_range"] = $time_range;
}
	
/*****************************************************************************
handle commands from the search 'n' browse module
/*****************************************************************************/
if ($view == "search") {
	if ($open_level)
		 $resources_data["browse_open_level"]=$open_level;

	if ($mode == "properties")
		$resources_data["search_mode"]="properties";
	
	if ($mode == "browse")
		$resources_data["search_mode"]="browse";
	
	if ((isset($start_search_x)) || ($search_send)) {
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

/*****************************************************************************
the room-planning module
/*****************************************************************************/

switch ($skip_closed_requests) {
	case "FALSE" : $resources_data["skip_closed_requests"] = FALSE; break;
	case "TRUE" : $resources_data["skip_closed_requests"] = TRUE; break;
}

//cancel an edit request session
if ($cancel_edit_request_x) {
	if (sizeof($resources_data["requests_open"]) < sizeof ($resources_data["requests_working_on"])) {
		$msg->addMsg(40, array($PHP_SELF, $PHP_SELF));
		//$resources_data["requests_working_on"] = FALSE;
		$save_state_x = FALSE;
	}
	$resources_data["view"] = "requests_start";
	$view = "requests_start";
}

//we start a new room-planning-session
if (($start_multiple_mode_x) || ($single_request)) {
	unset($resources_data["requests_working_on"]);
	unset($resources_data["requests_open"]);
	
	$requests = getMyRoomRequests();
	
	$resources_data["requests_working_pos"] = 0;
	$resources_data["skip_closed_requests"] = TRUE;
	
	//filter the requests
	foreach($requests as $key => $val) {
		if (!$val["closed"]) {
			if ($resolve_requests_mode == "sem") {
				if ($val["my_sem"])
					$selected_requests[$key] = TRUE;
			} elseif ($resolve_requests_mode == "res") {
				if ($val["my_res"])
					$selected_requests[$key] = TRUE;
			} else {
				$selected_requests[$key] = TRUE;
			}
		}
	}
	
	if ($single_request) {
		if ($selected_requests[$single_request]) {
			$resources_data["requests_working_on"][] = array("request_id" => $single_request, "closed" => FALSE);
			$resources_data["requests_open"][$single_request] = TRUE;
		}
	} else {
		//order requests
		$in =  "('".join("','",array_keys($selected_requests))."')";
		if ($resolve_requests_order = "complex")
			$order = "seats DESC, complexity DESC";
		if ($resolve_requests_order = "newest")
			$order = "a.mkdate DESC";
		if ($resolve_requests_order = "oldest")
			$order = "a.mkdate ASC";
	
		$query = sprintf ("SELECT a.request_id, a.resource_id, COUNT(b.property_id) AS complexity, MAX(d.state) AS seats
				FROM resources_requests a 
				LEFT JOIN resources_requests_properties b USING (request_id)
				LEFT JOIN resources_properties c ON (b.property_id = c.property_id AND c.system = 2)
				LEFT JOIN resources_requests_properties d ON (c.property_id = d.property_id AND a.request_id = d.request_id)
				WHERE a.request_id IN %s
				GROUP BY a.request_id
				ORDER BY %s", $in, $order);
		
		$db->query($query);
		
		while($db->next_record()) {
			$resources_data["requests_working_on"][] = array("request_id" => $db->f("request_id"), "closed" => FALSE);
			$resources_data["requests_open"][$db->f("request_id")] = TRUE;
		} 
	}
	
	$resources_data["view"] = "edit_request";
	$view = $resources_data["view"];
	$new_session_started = TRUE;
}

if (is_array($selected_resource_id)) {
	foreach ($selected_resource_id as $key=>$val) {
		$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$key] = $val;
	}
}

// save the assigments in db
if ($save_state_x) {
	// RECHTECHECK NICHT VERGESSEN!
	require_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
	require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Seminar.class.php");
	
	$reqObj = new RoomRequest($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["request_id"]);
	$semObj = new Seminar($reqObj->getSeminarId());
	$semResAssign = new VeranstaltungResourcesAssign($semObj->getId());

	//single date mode - just create one assign-object
	if ($reqObj->getTerminId())
		$assignObjects[] = $semResAssign->getDateAssignObject($reqObj->getTerminId());
	//multiple assign_objects (every date one assign-object or every metadate one assign-object)
	elseif (is_array ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"])) {
		$i=0;
		//check, if one assignment should assigned to a room, which is only particularly free - so we have treat every single date
		if (($semObj->getMetaDateType == 0) && (!isSchedule($semObj->getId(), FALSE))) {
			foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $key=>$val) {
				if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$i]) {
					$overlap_events_count = $val["overlap_events_count"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$i]];
					if (($overlap_events_count > 0) && ($overlap_events_count < round($val["events_count"] * ($GLOBALS['RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE'] / 100)))) {
						$particular_free = TRUE;
						$close_request = TRUE;
					}
				}
				$i++;
			}
		}
	
		if (($semObj->getMetaDateType() == 1) || (isSchedule($semObj->getId(), FALSE))) {
			$assignObjects = $semResAssign->getDateAssignObjects(TRUE);
		} else {
			$assignObjects = $semResAssign->getMetaAssignObjects();
		}
	}
	
	//get the selected resources, save this informations and create the right msgs
	if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"])) {
		//check all selected resources for perms
		foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"] as $key=>$val) {
			$resPerms =& ResourceObjectPerms::Factory($val);
			if (!$resPerms->havePerm("tutor"))
				$no_perm = TRUE;
			$resPerms ='';
		}
		
		if ($no_perm)
			$msg->addMsg(25);
		else {
			//grouped multiple date mode
			if ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"]) {
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"] as $key=>$val) {
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["resource_id"] = $val;
					foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["termin_ids"] as $key2 => $val2) {
						$result = array_merge($result, $semResAssign->changeDateAssign($key2, $resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$key]));
						$result_termin_id[] = $key2;
					}
					if ($semObj->getMetaDateType() == 0) {
						$semObj->setMetaDateValue($key, "resource_id", $val);
					}
					
				}
				$close_request = TRUE;
				$semObj->store();
				
			//normal metadate mode
			} elseif (($semObj->getMetaDateType() == 0) && (!isSchedule($semObj->getId(), FALSE))) {
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"] as $key=>$val) {
					$assignObjects[$key]->setResourceId($val);
					$semResAssign->deleteAssignedRooms();
					if (!$particular_free) {
						$result = $semResAssign->changeMetaAssigns($assignObjects);
					}
				}
	
			//single date mode
			} elseif ($reqObj->getTerminId()) {
				$result = $semResAssign->changeDateAssign($reqObj->getTerminId(), $resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][0]);
			
			//multiple dates mode
			} elseif (($semObj->getMetaDateType() == 1) || (isSchedule($semObj->getId(), FALSE))) {
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"] as $key=>$val){
					$result = array_merge($result, $semResAssign->changeDateAssign($key, $resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$key]));
					$result_termin_id[] = $key;
				}
			}
			
			//---------------------------------------------- second part, msgs and some other operations
			
			$succesful_assigned = 0;
			//create msgs, single date mode
			if ($reqObj->getTerminId()) {
				$assign_ids = array_keys($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]);
				$resObj =& ResourceObject::Factory($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][0]);
				foreach ($result as $key=>$val) {
					if (!$val["overlap_assigns"]) {
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[0]]["resource_id"] = $resObj->getId();			
						$good_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[0]->getFormattedShortInfo());
						$succesful_assigned++;
					} else {
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[0]]["resource_id"] = FALSE;			
						$bad_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[0]->getFormattedShortInfo());
					}
				}
		
			//create msgs, grouped multi date mode
			} elseif ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"]) {
				$i=0;
				foreach ($result as $key=>$val) {
					$resObj =& ResourceObject::Factory($val["resource_id"]);
					if (!$val["overlap_assigns"]) {
						$good_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$result_termin_id[$i]]->getFormattedShortInfo());
					} else {
						$req_added_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$result_termin_id[$i]]->getFormattedShortInfo());
						$copyReqObj = $reqObj;
						$copyReqObj->copy();
						$copyReqObj->setTerminId($val["termin_id"]);
						$copyReqObj->store();				
					}
					$i++;
				}
			
			//create msgs, normal metadate mode, and update the matadata (we save the resource there too), if no overlaps detected and create msg's
			} elseif (($semObj->getMetaDateType() == 0) && (!$particular_free) && (!isSchedule($semObj->getId(), FALSE))) {
				$i=0;
				$assign_ids = array_keys($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]);
				foreach ($result as $key=>$val) {
					$resObj =& ResourceObject::Factory($val["resource_id"]);
					if (!$val["overlap_assigns"]) {
						$semObj->setMetaDateValue($i, "resource_id", $resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"][$i]);
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[$i]]["resource_id"] = $resObj->getId();
						$good_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$i]->getFormattedShortInfo());
						$succesful_assigned++;
					} else {
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[$i]]["resource_id"] = FALSE;				
						$bad_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$i]->getFormattedShortInfo());
					}
					$i++;
				}
	
			//create msgs, metadate mode, but create individual dates (a complete schedule) in case of trouble with the suggested room (it isn't free at all, but for most dates ok)
			} elseif ($semObj->getMetaDateType() == 0) {
				$i=0;
				$assign_ids = array_keys($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]);
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["selected_resources"] as $key=>$val) {
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[$i]]["resource_id"] = $val;
					$semObj->setMetaDateValue($i, "resource_id", $val);
					$i++;
				}
				$semObj->store();
				
				//use the assi to create all the dates (and do the checks again)
				$assi_result = dateAssi ($semObj->getId(), "update", FALSE, FALSE, FALSE, FALSE, FALSE);
	
				//reload the assignObject - now we are dealing with indivual dates instead of a regularly assign
				$assignObjects = $semResAssign->getDateAssignObjects();
				
				foreach ($assi_result["resources_result"] as $key=>$val) {
					$resObj =& ResourceObject::Factory($val["resource_id"]);
					if (!$val["overlap_assigns"]) {
						$good_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$val["termin_id"]]->getFormattedShortInfo());
						$succesful_assigned++;;
					} else {
						$req_added_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$val["termin_id"]]->getFormattedShortInfo());
						$copyReqObj = $reqObj;
						$copyReqObj->copy();
						$copyReqObj->setTerminId($val["termin_id"]);
						$copyReqObj->store();
					}
				}
	
			//create msgs, multiple	date mode
			} else {
				$i=0;
				$assign_ids = array_keys($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]);
				foreach ($result as $key=>$val) {
					$resObj =& ResourceObject::Factory($val["resource_id"]);
					if (!$val["overlap_assigns"]) {
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[$i]]["resource_id"] = $resObj->getId();							
						$good_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$result_termin_id[$i]]->getFormattedShortInfo());
					} else {
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assign_ids[$i]]["resource_id"] = FALSE;							
						$bad_msg.="<br>".sprintf(_("%s, Belegungszeit: %s"), "<a href=\"".$resObj->getLink()."\" target=\"_new\">".$resObj->getName()."</a>", $assignObjects[$result_termin_id[$i]]->getFormattedShortInfo());
					}
					$i++;
				}
			}
			
			//update seminar-date (save the new resource_ids)
			if ($succesful_assigned) {
				if (($semObj->getMetaType == 0) && (!isSchedule($semObj->getId(), FALSE)))
					$semObj->store();
			}
			
			//set reload flag for this request (the next time the user skips to the request, we reload all data)
			$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["reload"] = TRUE;
				
			
			//create msg's
			if ($good_msg)
				$msg->addMsg(33, array($good_msg));
			if ($bad_msg)
				$msg->addMsg(34, array($bad_msg));
			if ($req_added_msg)
				$msg->addMsg(35, array($req_added_msg));
		}
	}
	
	//set request to closed, if we have a room for every assign_object
	$assigned_resources = 0;
	foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"] as $val) {
		if ($val["resource_id"])
			$assigned_resources++;
	}
	
	if (($assigned_resources == sizeof($assignObjects)) || ($close_request)) {
		$reqObj->setClosed(1);
		$reqObj->store();
		unset($resources_data["requests_open"][$reqObj->getId()]);
		if (sizeof($resources_data["requests_open"]) == 0) {
			$resources_data["view"] = "requests_start";
			$view = "requests_start";
			$msg->addMsg(36, array($PHP_SELF, $PHP_SELF));
			//$resources_data["requests_working_on"] = FALSE;
			$save_state_x = FALSE;
		} else  {
			if ($resources_data["requests_working_pos"] == sizeof($resources_data["requests_working_on"])-1) {
				$auto_dec = TRUE;
			} else {
				$auto_inc = TRUE;
			}
		}
	}
}

if ($decline_request_x) {
	require_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
	
	$reqObj = new RoomRequest($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["request_id"]);

	$reqObj->setClosed(3);
	$reqObj->store();
	unset($resources_data["requests_open"][$reqObj->getId()]);
	if (sizeof($resources_data["requests_open"]) == 0) {
		$resources_data["view"] = "requests_start";
		$view = "requests_start";

	} else  {
		if ($resources_data["requests_working_pos"] == sizeof($resources_data["requests_working_on"])-1) {
			$auto_dec = TRUE;
		} else {
			$auto_inc = TRUE;
		}
	}
}

// inc if we have requests left in the upper
if (($inc_request_x) || ($auto_inc))
	if ($resources_data["requests_working_pos"] < sizeof($resources_data["requests_working_on"])-1) {
		$i = 1;
		if ($resources_data["skip_closed_requests"])
			while ((!$resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $i]["request_id"]]) && ($resources_data["requests_working_pos"] + $i < sizeof($resources_data["requests_open"])-1))
				$i++;
		if ((sizeof($resources_data["requests_open"]) >= 1) && (($resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $i]["request_id"]]) || (!$resources_data["skip_closed_requests"]))){
			$resources_data["requests_working_pos"] = $resources_data["requests_working_pos"] + $i;
		} elseif ($auto_inc)
			$dec_request_x = TRUE; //we cannot inc - so we are at the end and want to find an request below, so try do dec. 
	} 

// dec if we have requests left in the lower
if (($dec_request_x) || ($auto_dec))
	if ($resources_data["requests_working_pos"] > 0) {
		$d = -1;
		if ($resources_data["skip_closed_requests"])
			while ((!$resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $d]["request_id"]]) && ($resources_data["requests_working_pos"] + $d > 0))
				$d--;
		if ((sizeof($resources_data["requests_open"]) >= 1) && (($resources_data["requests_open"][$resources_data["requests_working_on"][$resources_data["requests_working_pos"] + $d]["request_id"]]) || (!$resources_data["skip_closed_requests"]))) {
			$resources_data["requests_working_pos"] = $resources_data["requests_working_pos"] + $d;
		}
	}

//create the (overlap)data for all resources that should checked for a request
if (($inc_request_x) || ($dec_request_x) || ($new_session_started) || ($marked_clip_ids) || ($save_state_x)) {
	require_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
	require_once ($RELATIVE_PATH_RESOURCES."/lib/CheckMultipleOverlaps.class.php");
	require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Seminar.class.php");
	require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/SemesterData.class.php");
	
	if ((!is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"])) || ($marked_clip_ids) || ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["reload"])) {
		unset ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["reload"]);
		$semester = new SemesterData;
		$all_semester = $semester->getAllSemesterData();
		
		$reqObj = new RoomRequest($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["request_id"]);
		$semObj = new Seminar($reqObj->getSeminarId());
		$multiOverlaps = new CheckMultipleOverlaps;
		$semResAssign = new VeranstaltungResourcesAssign($semObj->getId());
		
		//add the requested ressource to selection
		if ($reqObj->getResourceId())
			$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"][$reqObj->getResourceId()] = array("type"=>"requested");
	
		//add the matching ressources to selection
		if (getGlobalPerms($user->id) != "admin")
			$resList = new ResourcesUserRoomsList ($user->id, FALSE, FALSE);		
		$machting_resources = $reqObj->searchRooms(FALSE, TRUE, 10, TRUE, (is_object($resList)) ? array_keys($resList->getRooms()) : FALSE);
		foreach ($machting_resources as $key => $val) {
			if (!$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"][$key])
				$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"][$key] = array("type"=>"matching");
		}

		//add resource_ids from clipboard
		if (is_array($marked_clip_ids))
			foreach ($marked_clip_ids as $val)
				if (!$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"][$val])
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"][$val] = array("type"=>"clipped");
		
		//create the assign-objects for the seminar (virtual!)
		$assignObjects = array();
		if ($reqObj->getTerminId()) {
			$assignObjects[] = $semResAssign->getDateAssignObject($reqObj->getTerminId());
		} elseif (($semObj->getMetaDateType() == 1) || (isSchedule($semObj->getId(), FALSE, TRUE)))
			$assignObjects = $semResAssign->getDateAssignObjects(TRUE);
		else
			$assignObjects = $semResAssign->getMetaAssignObjects();
			
		$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"]=array();
		if (is_array($assignObjects)) {
			//set the time range to check;
			if (($semObj->getMetaDateType() == 1) || ($reqObj->getTerminId()) || (isSchedule($semObj->getId(), FALSE))) {
				$multiOverlaps->setAutoTimeRange($assignObjects);
			} else {
				$multiOverlaps->setTimeRange($semObj->getSemesterStartTime(), $all_semester[get_sem_num($semObj->getSemesterStartTime())]["ende"]); //!!!!!
			}
			
			//add the considered resources to the check-set
			if (is_array($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"])) {
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["considered_resources"] as $key => $val) {
					$multiOverlaps->addResource($key);
				}
			}
			//do checks
			$result = array();
			$first_event = FALSE;
			if ((($semObj->getMetaDateType() == 0) && (!isSchedule($semObj->getId(), FALSE)))
				|| (($semObj->getMetaDateType() == 1) && (isSchedule($semObj->getId(), FALSE) < $GLOBALS["RESOURCES_ALLOW_SINGLE_DATE_GROUPING"]))
				|| ($reqObj->getTerminId())) {
				//in this cases, we handle it assin-object based (every column in the tool is one assign-object)
				$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"] = FALSE;
				foreach ($assignObjects as $assObj) {
					$events = array();
					foreach ($assObj->getEvents() as $evtObj) {
						$events[$evtObj->getId()] = $evtObj;
						if (($evtObj->getBegin() < $first_event) || (!$first_event))
							$first_event = $evtObj->getBegin();
					}
					
					$multiOverlaps->checkOverlap($events, &$result, "assign_id");
					
					$tmp_overlap_event_ids = array();
					foreach ($result as $key => $val) {
						foreach ($val as $key2 => $val2) {
							if ($key2 == $assObj->getId()) {
								foreach ($val2 as $val3) {
									$tmp_overlap_event_ids[$key][$val3["event_id"]] = TRUE;
								}
							}
						}
						$overlap_event_ids[$key] = sizeof($tmp_overlap_event_ids[$key]);
					}
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["assign_objects"][$assObj->getId()] = array("termin_id" => ($semObj->getMetaDateType() == 1) ?  $assObj->getAssignUserId() : FALSE, "resource_id" => $assObj->getResourceId(), "events_count"=>sizeof($events), "overlap_events_count" => $overlap_event_ids);
				}
			} else {
				//otherwise, we do grouped objects (we group some dates to one column in the tool)
				$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["grouping"] = TRUE;
				if ($semObj->getMetaDateType() == 1) {
					//group more than all dates (which are presence-dates) to one group
					$query = sprintf ("SELECT termin_id FROM termine WHERE range_id = '%s' AND date_typ IN %s", $semObj->getId(), getPresenceTypeClause());
					$db->query($query);
					while ($db->next_record())
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][0]["termin_ids"][$db->f("termin_id")] = TRUE;
				} else {
					//group all matching dates corresponding to a metadate into one group
					$correspondig_dates = getMetadateCorrespondingDates ($semObj->getId(), TRUE);
					foreach ($correspondig_dates as $key=>$val) {
						$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["termin_ids"] = $val;
					}
				}

				$events = array();
				foreach ($assignObjects as $assObj) {
					foreach ($assObj->getEvents() as $evtObj) {
						$events[$evtObj->getId()] = $evtObj;
						if (($evtObj->getBegin() < $first_event) || (!$first_event))
							$first_event = $evtObj->getBegin();
					}
				}
				$multiOverlaps->checkOverlap($events, &$result, "assign_user_id");

				//build a temporary array for termin_id=>group_number
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"] as $key=>$val) {
					foreach ($val["termin_ids"] as $key2=>$val2) {
						$termin_groups[$key2] = $key; 
					}
					
				}
				
				//build the overlap-result based on termin_ids...
				foreach ($result as $key=>$val) {
					foreach ($val as $key2=>$val2) {
						if (sizeof($val2))
							$tmp_result_termin[$termin_groups[$key2]][$key][$key2] = TRUE;
					}
				}
							
				//count for every group	
				$result_termin = array();
				if (is_array($tmp_result_termin)) {
					foreach ($tmp_result_termin as $key=>$val) {
						foreach ($val as $key2=>$val2) {
							$result_termin[$key][$key2] = sizeof($val2);
						}
					}
				}
					
				
				foreach ($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"] as $key=>$val) {
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["events_count"] = sizeof($resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["termin_ids"]);
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["overlap_events_count"] = $result_termin[$key];
					$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["groups"][$key]["resource_id"] = $semObj->getMetaDateValue($key, "resource_id");
				}
			}
			$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["detected_overlaps"] = $result;
			$resources_data["requests_working_on"][$resources_data["requests_working_pos"]]["first_event"] = $first_event;
		}
	}
}

//inform the owner of the requests
if ($snd_closed_request_sms) {
	require_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
	require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Seminar.class.php");
	require_once ($ABSOLUTE_PATH_STUDIP."/messaging.inc.php");
	
	$messaging = new messaging;
	
	foreach ($resources_data["requests_working_on"] as $val) {
		$request_ids[] = $val["request_id"];
		$request_data[$val["request_id"]] = $val;
	}
	$in="('".join("','",$request_ids)."')";
	
	$query = sprintf ("SELECT request_id, seminar_id FROM resources_requests WHERE closed = 1 AND request_id IN %s", $in);
	$db->query($query);
	
	while ($db->next_record()) {
		$reqObj = new RoomRequest($db->f("request_id"));
		$semObj = new Seminar($db->f("seminar_id"));
		
		$message = sprintf (_("Ihre Raumanfrage zur Veranstaltung %s wurde bearbeitet.")." \n"._("Für folgende Belegungszeiten wurde der jeweils angegebene Raum gebucht:")."\n\n", $semObj->getName());

		if (!$reqObj->getTerminId()) {
			if ($semObj->getMetaDateType() == 0) {
				if ($metadates = $semObj->getFormattedTurnusDates()) {
					$i=0;
					$tmp_assign_ids = array_keys($request_data[$db->f("request_id")]["assign_objects"]);
					foreach ($metadates as $key=>$val) {
						if ($request_data[$db->f("request_id")]["grouping"])
							$resObj =& ResourceObject::Factory($request_data[$db->f("request_id")]["groups"][$i]["resource_id"]);
						else
							$resObj =& ResourceObject::Factory($request_data[$db->f("request_id")]["assign_objects"][$tmp_assign_ids[$i]]["resource_id"]);
						$message.= $val.": ".$resObj->getName()."\n";
						$i++;
					}
	
					if ($semObj->getCycle() == 1)
						$message.= "\n"._("wöchentlich");
					elseif ($semObj->getCycle() == 2)
						$message.= "\n"._("zweiw&ouml;chentlich");
					$message.= ", "._("ab:")." ".date("d.m.Y", $semObj->getFirstDate());
				}
			} else {
				$query2 = sprintf("SELECT * FROM termine WHERE range_id = '%s' ORDER BY date, content", $reqObj->getSeminarId());
				$db2->query($query2);

				if ($db2->nf()) {
					while ($db2->next_record()) {
						$message.= date("d.m.Y, H:i", $db2->f("date")).", ".(($db2->f("date") != $db2->f("end_time")) ? " - ".date("H:i", $db2->f("end_time")) : "");
						foreach ($request_data[$db->f("request_id")]["assign_objects"] as $key=>$val) {
							if ($val["termin_id"] == $db2->f("termin_id")) {
								$resObj =& ResourceObject::Factory($request_data[$db->f("request_id")]["assign_objects"][$key]["resource_id"]);
								$message.= $resObj->getName()."\n";
							}
						}
					}
				}
			}
		} else {
			$query2 = sprintf("SELECT * FROM termine WHERE range_id = '%s' AND termin_id = '%s' ORDER BY date, content", $reqObj->getSeminarId(), $reqObj->getTerminId());
			$db2->query($query2);

			$tmp_assign_ids = array_keys($request_data[$db->f("request_id")]["assign_objects"]);
			if ($db2->nf()) {
				while ($db2->next_record()) {
					$resObj =& ResourceObject::Factory($request_data[$db->f("request_id")]["assign_objects"][$tmp_assign_ids[0]]["resource_id"]);
					$message.= date("d.m.Y, H:i", $db2->f("date")).", ".(($db2->f("date") != $db2->f("end_time")) ? " - ".date("H:i", $db2->f("end_time")) : "").": ".$resObj->getName()."\n";
				}
			}
		}
		
		//send the message into stud.ip message system
		$messaging->insert_message($message, get_username($reqObj->getUserId()), $user->id);
		
		//set more closed ;-)
		$reqObj->setClosed(2);
		$reqObj->store();
	}
}


/*****************************************************************************
some other stuff ;-)
/*****************************************************************************/

//display perminvalid window
if ((in_array("1", $msg->codes)) || (in_array("25", $msg->codes))) {
	$forbiddenObject =& ResourceObject::Factory($resources_data["actual_object"]);
	if ($forbiddenObject->isLocked()) {
		$lock_ts = getLockPeriod();
		$msg->addMsg(31, array(date("d.m.Y, G:i", $lock_ts[0]), date("d.m.Y, G:i", $lock_ts[1])));
	}
	$msg->displayAllMsg("window");
	die;
}

//show object, this object will be edited or viewed
if ($show_object)
	$resources_data["actual_object"]=$show_object;

if ($show_msg) {
	if ($msg_resource_id)
		$msgResourceObj =& ResourceObject::Factory($msg_resource_id);
	$msg->addMsg($show_msg, ($msg_resource_id) ? array(htmlReady($msgResourceObj->getName())) : FALSE);
}

//if ObjectPerms for actual user and actual object are not loaded, load them!
	
if ($ObjectPerms) {
	if (($ObjectPerms->getId() == $resources_data["actual_object"]) && ($ObjectPerms->getUserId()  == $user->id))
		$ActualObjectPerms = $ObjectPerms;	
	 else
		$ActualObjectPerms =& ResourceObjectPerms::Factory($resources_data["actual_object"]);
} else
	$ActualObjectPerms =& ResourceObjectPerms::Factory($resources_data["actual_object"]);
	
//edit or view object
if ($edit_object) {
	if ($ActualObjectPerms->getUserPerm() == "admin") {
		$resources_data["view"]="edit_object_properties";
		$view = $resources_data["view"];
	} else {
		$resources_data["view"]="view_details";
		$view = $resources_data["view"];
	}
}
?>
