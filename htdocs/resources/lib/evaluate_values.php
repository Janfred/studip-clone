<?
/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

//get view
if ($view)
	 $resources_data["view"]=$view;

//If we start the admin mode, kill open objects
if ($resources_data["view"] == "resources")
	closeObject();

//Open a level/resource
if ($structure_open) {
	$resources_data["structure_opens"][$structure_open] =TRUE;
	$resources_data["actual_object"]=$structure_open;
}

//Close a level/resource
if ($structure_close) {
	unset($resources_data["structure_opens"][$structure_close]);
}

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

//Object bearbeiten
if ($edit_object) {
	$resources_data["view"]="edit_object_properties";
	$resources_data["actual_object"]=$edit_object;
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
	
	if ($ObjectPerms->getUserPerm () == "user" || $ObjectPerms->getUserPerm () == "admin") {
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
				if (($ForeignObjectPerms->getUserPerm() == "user") || ($ForeignObjectPerms-> getUserPerm() == "admin"))
					$change_schedule_assign_user_id=$submit_search_user;
				else
					$msg -> addMsg(2);
			}

			//check, if the owner of the assign object is a Veranstaltung, which has own dates to insert
			if (get_object_type($change_schedule_assign_user_id) == "sem") {
			 	require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
		 		$veranstAssign = new VeranstaltungResourcesAssign($change_schedule_assign_user_id);
				$created_ids = $veranstAssign->changeAssign($change_schedule_resource_id, $change_schedule_user_free_name);

				//after a succesful insert show the first (maybe the only) assign-object
				if (is_array($created_ids))
					$assign_id=$created_ids[0];

			//create the "normal" assign object
			} else {
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

				if ($change_object_schedules == "NEW") {
					if ($changeAssign->create()) {
						$assign_id=$changeAssign->getId();
						$msg->addMsg(3);
					}
				} else {
					$changeAssign->chng_flag=TRUE;
					if ($changeAssign->store()) {
						$assign_id=$changeAssign->getId();
						$msg->addMsg(4);
						}
				}
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
if (($add_type) || ($delete_type) || ($add_type_property_id) || ($delete_type_property_id)) {
	//if ($ObjectPerms->getUserPerm () == "admin") { --> da muss der Ressourcen Root check hin °
		if ($delete_type) {
			$db->query("DELETE FROM resources_categories WHERE category_id ='$delete_type'");
		}
	
		if (($add_type) && ($_add_type_x)) {
			$id=md5(uniqid("Sommer2002"));	
			$db->query("INSERT INTO resources_categories SET category_id='$id', name='$add_type', description='$insert_type_description' ");
		}
	
		if (($add_type_property_id) && ($add_type_category_id)) {	
			$db->query("INSERT INTO resources_categories_properties SET category_id='$add_type_category_id', property_id='$add_type_property_id' ");
		}
	
		if ($delete_type_property_id) {
			$db->query("DELETE FROM resources_categories_properties WHERE category_id='$delete_type_category_id' AND property_id='$delete_type_property_id' ");
		}
	/*} else {
		$msg->displayMsg(1);
		die;
	}*/
}

//Eigenschaften bearbeiten
if (($add_property) || ($delete_property) || ($send_property_type_id)) {
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
	
		if ($send_property_type_id) {
			if ($send_property_type == "select") {
				$tmp_options=explode (";",$send_property_select_opt);
				$options='';
				$i=0;
				if (is_array($tmp_options))
					foreach ($tmp_options as $a) {
						if ($i)
							$options.=";";
						$options.=trim($a);						
						$i++;
					}
			} elseif ($send_property_type == "bool") {
				$options=$send_property_bool_desc;
			}
			else
				$options='';
			
			if (!$options)
				if ($send_property_type == "bool")
					$options="vorhanden";
				elseif ($send_property_type == "select")
					$options="Option 1;Option 2;Option 3";	
				
			$db->query("UPDATE resources_properties SET options='$options', type='$send_property_type' WHERE property_id='$send_property_type_id' ");
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
			$db->query("INSERT resources_user_resources SET user_id='$submit_search_root_user', resource_id='all', perms='user' ");

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
	if ($navigate) {
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
		$resources_data["schedule_start_time"] = mktime (0, 0, 0, date("n", time()), date("j", time()), date("Y", time()));
		$resources_data["schedule_end_time"] = mktime (23, 59, 0, date("n", time()), date("j", time())+7, date("Y", time()));
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
} else
	$ActualObjectPerms = new ResourcesObjectPerms($resources_data["actual_object"]);

?>