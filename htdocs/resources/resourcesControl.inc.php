<?
/*
resourcesControl.php - 0.8
Steuerung fuer Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*****************************************************************************
Requires & Registers
/*****************************************************************************/

require_once ($ABSOLUTE_PATH_STUDIP."reiter.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."msg.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."config.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."functions.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesClass.inc.php");
require_once ($RELATIVE_PATH_RESOURCES."/resourcesVisual.inc.php");

$sess->register("resources_data");
$my_perms= new ResourcesPerms;
$error = new ResourcesError;
$db=new DB_Seminar;


//temporaer der Kopf...
include "html_head.inc.php";
include "header.php";


/*****************************************************************************
empfangene Werte auswerten und Befehle ausfuehren
/*****************************************************************************/

//View uebernehmen
if ($view)
	 $resources_data["view"]=$view;

//Beitrag aufklappen
if ($structure_open)
	$resources_data["structure_open"]=$structure_open;

//Beitrag schliessen
if ($structure_close)
	$resources_data["structure_open"]=FALSE;

//Listenstartpunkt festlegen
if ($open_list) {
	$resources_data["list_open"]=$open_list;
	$resources_data["view"]="_lists";
	}

//Neue Hierachieebene oder Unterebene anlegen
if ($resources_data["view"]=="create_hierarchie" || $create_hierachie_level) {
	if ($resources_data["view"]=="create_hierarchie") {
		$newHiearchie=new resourceObject("Neue Hierachie", "Dieses Objekt kennzeichnet eine Hierachie und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', '', "0", '', $user->id);
	} elseif ($create_hierachie_level) {
		$parent_Object=new resourceObject($create_hierachie_level);
		$newHiearchie=new resourceObject("Neue Hierachieebene", "Dieses Objekt kennzeichnet eine neue Hierachieebene und kann jederzeit in eine Ressource umgewandelt werden"
						, '', '', $parent_Object->getRootId(), $create_hierachie_level, '', $user->id);
	}
	$newHiearchie->create();
	$edit_structure_object=$newHiearchie->id;
	$resources_data["view"]="resources";
	}

//Neues Objekt anlegen
if ($create_object) {
	$parent_Object=new resourceObject($create_object);
	$new_Object=new resourceObject("Neues Objekt", "Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen."
					, FALSE, FALSE, $parent_Object->getRootId(), $create_object, "0", $user->id);
	$new_Object->create();
	$resources_data["view"]="edit_object_properties";
	$resources_data["structure_open"]=$new_Object->getId();
	}

//Object bearbeiten
if ($edit_object) {
	$resources_data["view"]="edit_object_properties";
	$resources_data["structure_open"]=$edit_object;
	}
	
//Object loeschen
if ($kill_object) {
	$ObjectPerms = new ResourcesObjectPerms($kill_object);
	if ($ObjectPerms->getUserPerm () == "admin") {
		$killObject=new resourceObject($kill_object);
		if ($killObject->delete())
		;
		$resources_data["view"]="resources";
	} else {
		$error->displayMsg(1);
		die;
	}
}

//Name und Beschreibung aendern
if ($change_structure_object) {
	//hier muss noch ein Rechtecheck passieren!
	$changeObject=new resourceObject($change_structure_object);
	$changeObject->setName($change_name);
	$changeObject->setDescription($change_description);
	if ($changeObject->store())
	;
	$resources_data["view"]="resources";
	$resources_data["structure_open"]=$change_structure_object;
}

//Objektbelegung erstellen/aendern
if ($change_object_schedules) {
	//hier muss noch ein Rechtecheck passieren!
	if ($change_object_schedules == "NEW")
		$change_schedule_id=FALSE;
	else
		$change_schedule_id=$change_object_schedules;

	if ($reset_search_user)
		$search_string_search_user=FALSE;

	if (($submit_search_user) && ($submit_search_user !="FALSE") && (!$reset_search_user))
		$change_schedule_assign_user_id=$submit_search_user;
	
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
			$change_schedule_repeat_quantity=1;
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
				$change_schedule_repeat_quantity=1;
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
			$change_schedule_repeat_quantity=1;
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
			$change_schedule_repeat_quantity=1;
		if (!$change_schedule_repeat_interval)
			$change_schedule_repeat_interval=1;
	}
	
	//repeat days, only if week
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
		
	if ($change_object_schedules == "NEW")
		$changeAssign->create();
	else {
		$changeAssign->chng_flag=TRUE;
		$changeAssign->store();
	}
	$assign_id=$changeAssign->getId();
	$resources_data["view"]="edit_object_schedules";
}

//Objekteigenschaften aendern
if ($change_object_properties) {
	//hier muss noch ein Rechtecheck passieren!
	$changeObject=new resourceObject($change_object_properties);
	$changeObject->setName($change_name);
	$changeObject->setDescription($change_description);
	$changeObject->setCategoryId($change_category_id);
	$changeObject->setParentBind($change_parent_bind);
	
	//Properties loeschen
	$changeObject->flushProperties();
	
	//Eigenschaften neu schreiben
	if (is_array($change_property_val))
		foreach ($change_property_val as $key=>$val) {
			if ((substr($val, 0, 4) == "_id_") && (substr($change_property_val[$key+1], 0, 4) != "_id_"))
				$changeObject->storeProperty(substr($val, 4, strlen($val)), $change_property_val[$key+1]);
		}
	
	//Object speichern
	if ($changeObject->store())
	;
	$resources_data["view"]="edit_object_properties";
}

//Objektberechtigungen aendern
if ($change_object_perms) {
	//hier muss noch ein Rechtecheck passieren!
	$changeObject=new resourceObject($change_object_perms);
	
	if (is_array($change_user_id))
		foreach ($change_user_id as $key=>$val) {
			$changeObject->storePerms($val, $change_user_perms[$key]);
		}

	if ($delete_user_perms)
		$changeObject->deletePerms($delete_user_perms);
	
	if ($reset_search_owner)
		$search_string_search_owner=FALSE;

	if ($reset_search_perm_user)
		$search_string_search_perm_user=FALSE;
	
	if (($submit_search_owner) && ($submit_search_owner !="FALSE") && (!$reset_search_owner)) 
 		$changeObject->setOwnerId($submit_search_owner);
	
	if (($submit_search_perm_user) && ($submit_search_perm_user !="FALSE") && (!$reset_search_perm_user))
		$changeObject->storePerms($submit_search_perm_user);
	
	//Object speichern
	if ($changeObject->store())
	;
	$resources_data["view"]="edit_object_perms";
}

//Typen bearbeiten
if (($add_type) || ($delete_type) || ($add_type_property_id) || ($delete_type_property_id)) {
	//hier muss noch ein Rechtecheck passieren!
	
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
}
	
//Eigenschaften bearbeiten
if (($add_property) || ($delete_property) || ($send_property_type_id)) {
	//hier muss noch ein Rechtecheck passieren!
	
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
}

//Globale Perms bearbeiten
if (($add_root_user) || ($delete_root_user_id)){
	//hier muss noch ein Rechtecheck passieren!
	if ($reset_search_root_user)
		$search_string_search_root_user=FALSE;

	if (($submit_search_root_user) && ($submit_search_root_user !="FALSE") && (!$reset_search_root_user))
		$db->query("INSERT resources_user_resources SET user_id='$submit_search_root_user', resource_id='all', perms='user' ");

	if ($delete_root_user_id)		
		$db->query("DELETE FROM resources_user_resources WHERE user_id='$delete_root_user_id' AND resource_id='all' ");
	
	if (is_array ($change_root_user_id))
		foreach ($change_root_user_id as $key => $val) {
			$db->query("UPDATE resources_user_resources SET perms='".$change_root_user_perms[$key]."' WHERE user_id='$val' ");
		}
}

//evaluate the command from schedule navigator
if ($navigate_to) {
	$schedule_start_time=mktime (0,0,0,$schedule_begin_month, $schedule_begin_day, $schedule_begin_year);
	if ($start_list_x) {
		if ($schedule_start_time < 1)
			$schedule_start_time = time();
		switch ($schedule_length_unit) {
			case "y" :
				$schedule_end_time=mktime(23,59,59,date("n",$schedule_start_time), date("j", $schedule_start_time), date("Y",$schedule_start_time)+$schedule_length_factor);
			break;
			case "m" :
				$schedule_end_time=mktime(23,59,59,date("n",$schedule_start_time)+$schedule_length_factor, date("j", $schedule_start_time), date("Y",$schedule_start_time));
			break;
			case "w" :
				$schedule_end_time=mktime(23,59,59,date("n",$schedule_start_time), date("j", $schedule_start_time)+($schedule_length_factor * 7), date("Y",$schedule_start_time));
			break;
			case "d" :
				$schedule_end_time=mktime(23,59,59,date("n",$schedule_start_time), date("j", $schedule_start_time)+$schedule_length_factor, date("Y",$schedule_start_time));
			break;
		}
		if ($schedule_end_time < 1)
			$schedule_end_time = time()+ (24 * 60 * 60);
	} else
		$schedule_end_time = $schedule_start_time + (7 * 24 * 60 * 60);
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
 
/*****************************************************************************
Kopf der Seite
/*****************************************************************************/

//soll spaeter nur hier hjn

/*****************************************************************************
Reitersystem 
/*****************************************************************************/

$reiter=new reiter;

//Reitersystem erstellen

//oberen Reiter 
$structure["resources"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"resources.php?view=resources", active=>FALSE);
$structure["lists"]=array (topKat=>"", name=>"Listen", link=>"resources.php?view=lists", active=>FALSE);
$structure["objects"]=array (topKat=>"", name=>"Objekt", link=>"resources.php?view=objects", active=>FALSE);
$structure["settings"]=array (topKat=>"", name=>"Anpassen", link=>"resources.php?view=settings", active=>FALSE);

//Reiter "Uebersicht"
$structure["_resources"]=array (topKat=>"resources", name=>"Struktur", link=>"resources.php?view=_resources", active=>FALSE);
$structure["search"]=array (topKat=>"resources", name=>"Suchen", link=>"resources.php?view=search&new_search=TRUE", active=>FALSE);
$structure["create_hierarchie"]=array (topKat=>"resources", name=>"Neue Hierarchieebene erzeugen", link=>"resources.php?view=create_hierarchie#a", active=>FALSE);

//Reiter "Listen"
$structure["_lists"]=array (topKat=>"lists", name=>"Listenausgabe", link=>"resources.php?view=_lists", active=>FALSE);
$structure["search_lists"]=array (topKat=>"lists", name=>"Suchen", link=>"resources.php?view=search_lists", active=>FALSE);
$structure["export_lists"]=array (topKat=>"lists", name=>"Listen exportieren", link=>"resources.php?view=export_lists", active=>FALSE);

//Reiter "Objekt"
$structure["view_schedule"]=array (topKat=>"objects", name=>"Belegung ausgeben", link=>"resources.php?view=view_schedule", active=>FALSE);
$structure["edit_object_schedules"]=array (topKat=>"objects", name=>"Belegung bearbeiten", link=>"resources.php?view=edit_object_schedules", active=>FALSE);
$structure["edit_object_properties"]=array (topKat=>"objects", name=>"Eigenschaften bearbeiten", link=>"resources.php?view=edit_object_properties", active=>FALSE);
$structure["edit_object_perms"]=array (topKat=>"objects", name=>"Rechte bearbeiten", link=>"resources.php?view=edit_object_perms", active=>FALSE);

//Reiter "Anpassen"
if (($my_perms->getGlobalPerms() == "admin") || ($perm->have_perm("root"))){ //Grundlegende Einstellungen fuer alle Ressourcen Admins
	$structure["edit_types"]=array (topKat=>"settings", name=>"Typen verwalten", link=>"resources.php?view=edit_types", active=>FALSE);
	$structure["edit_properties"]=array (topKat=>"settings", name=>"Eigenschaften verwalten", link=>"resources.php?view=edit_properties", active=>FALSE);
}
if ($perm->have_perm("root")) { //Rechtezuweisungen nur fuer Root
	$structure["edit_perms"]=array (topKat=>"settings", name=>"globale Rechte verwalten", link=>"resources.php?view=edit_perms", active=>FALSE);
}
$structure["edit_personal_settings"]=array (topKat=>"settings", name=>"pers&ouml;nliche Einstellungen", link=>"resources.php?view=edit_personal_settings", active=>FALSE);
//

/*****************************************************************************
Switcher
/*****************************************************************************/

$currentObject=new resourceObject($resources_data["structure_open"]);
$currentObjectTitelAdd=$currentObject->getCategory();
if ($currentObjectTitelAdd)
	$currentObjectTitelAdd=": ";
$currentObjectTitelAdd=$currentObject->getName()."&nbsp;<font size=-1>(".$currentObject->getOwnerName().")</font>";

switch ($resources_data["view"]) {
	//Reiter "Uebersicht"
	case "resources":
	case "_resources":
	case "create_hierachie":
	case "search":
		$page_intro="Auf dieser Seite k&ouml;nnen Sie Ressourcen, auf die Sie Zugriff haben, Ebenen zuordnen. ";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	
	//Reiter "Listen"
	case "lists":
	case "_lists":
	case "export_lists":
	case "search_list":
		$page_intro="Hier k&ouml;nnen Sie Listen verwalten. Angezeigt wird jeweils die Liste einer ausgew&auml;hlten Ebene oder alle Ressourcen, auf die sie Zugriff haben.";
		$title="Bearbeiten und Ausgeben von Listen";
	break;

	//Reiter "Objekt"
	case "objects":
	case "edit_object_perms":
	case "edit_object_properties":
	case "edit_object_schedules":
	case "view_schedule":
	case "search_object":
		$page_intro="Hier k&ouml;nnen Sie einzelen Objekte verwalten. Sie k&ouml;nnen Eigenschaften, Berechtigungen und Belegung verwalten.";
		$title="Objekt bearbeiten: ".$currentObjectTitelAdd;
	break;
	case "view_schedule":
		$page_intro="Hier k&ouml;nnen Sie sich den Belegungsplan des Objektes ausgeben lassen. Bitte w&auml;hlen Sie daf&uuml;r den Zeitraum aus.";
		$title="Belegung ausgeben - Objekt: ".$currentObjectTitelAdd;
	break;
	
	//Reiter "Anpassen"	
	case "settings":
	case "edit_types":
	case "edit_properties":
	case "edit_perms":
		$page_intro="Hier k&ouml;nnen Sie grundlegen Einstellungen der Ressourcenverwaltung vornehmen.";
		$title="Einstellungen bearbeiten";
	break;
	
	//default
	default:
		$resources_data["view"]="resources";
		$page_intro="Sie befinden sich in der Ressurcenverwaltung von Stud.IP. Sie k&ouml;nnen hier R&auml;ume, Geb&auml;ude, Ger&auml;te und andere Ressourcen verwalten.";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	}

$reiter->create($structure, $resources_data["view"]);

/*****************************************************************************
Kopf der Ausgabe
/*****************************************************************************/
?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="topic">&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt="Ressourcen"><b>&nbsp;<? echo $title; ?></b></td>
	</tr>
	<tr>
		<td class="blank">&nbsp;
			<blockquote>
			<? echo $page_intro ?>
			</blockquote>
		</td>
	</tr>	
	<tr>
		<td class="blank">

<?	

/*****************************************************************************
Treeview, die Strukturdarstellung, views: resources, _resources, make_hierarchie
/*****************************************************************************/
if ($resources_data["view"]=="resources" || $resources_data["view"]=="_resources") {

	$resUser=new ResourcesUserRoots();
	$thread=new getThread();
	

	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}

	$roots=$resUser->getRoots();
	if (is_array($roots)) {
		foreach ($roots as $a) {
			$thread->createThread($a);
		}
		echo "<br />&nbsp;";			
	} else {
		echo "</td></tr>";
		parse_msg ("infoºEs sind keine Objekte beziehungsweise Ebene angelegt, auf die Sie Zugriff haben. <br />Sie k&ouml;nnen eine neue Ebene erzeugen, wenn sie \"Neue Hierarchie erzeugen\" anw&auml;hlen.");
	}

	if ($edit_structure_object) {
		echo "</form>";
	}
	
}

/*****************************************************************************
Listview, die Listendarstellung, views: resources, _resources, make_hierarchie
/*****************************************************************************/
if ($resources_data["view"]=="lists" || $resources_data["view"]=="_lists") {

	$list=new getList();
	$list->setRecurseLevels(-1);
	
	if ($edit_structure_object) {
		echo"<form method=\"POST\" action=\"$PHP_SELF\">";
	}
	
	if (!$list->createList($resources_data["list_open"])) {
		echo "</td></tr>";
		parse_msg ("infoºSie haben keine Ebene ausgew&auml;hlt. Daher kann keine Liste erzeugt werden. <br />Benutzen Sie die Suchfunktion oder w&auml;hlen Sie unter \"&Uuml;bersicht\" einen Startpunkt in der Hierachie aus.");
	}

	if ($edit_structure_object) {
		echo "</form>";
	}
}

/*****************************************************************************
Objecteigenschaften bearbeiten, views: edit_object_properties
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_properties" || $resources_data["view"]=="objects") {

	if ($resources_data["structure_open"]) {
		$editObject=new editObject($resources_data["structure_open"]);
		$editObject->create_propertie_forms();
	} else {
		echo "</td></tr>";
		parse_msg ("infoºSie haben kein Objekt zum Bearbeiten ausgew&auml;hlt. <br />Benutzen Sie die Suchfunktion oder w&auml;hlen Sie in der \"&Uuml;bersicht\"  oder einer ge&ouml;ffnete Liste ein Objekt aus.");
	}
}

/*****************************************************************************
Objectberechtigungen bearbeiten, views: edit_object_perms
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_perms") {

	if ($resources_data["structure_open"]) {
		$editObject=new editObject($resources_data["structure_open"]);
		$editObject->create_perm_forms();
	} else {
		echo "</td></tr>";
		parse_msg ("infoºSie haben kein Objekt zum Bearbeiten ausgew&auml;hlt. <br />Benutzen Sie die Suchfunktion oder w&auml;hlen Sie in der \"&Uuml;bersicht\"  oder einer ge&ouml;ffnete Liste ein Objekt aus.");
	}
}

/*****************************************************************************
Objectbelegung bearbeiten, views: edit_object_schedules
/*****************************************************************************/
if ($resources_data["view"]=="edit_object_schedules") {

	if ($resources_data["structure_open"]) {
		$editObject=new editObject($resources_data["structure_open"]);
		if ($edit_assign_object)
			$assign_id=$edit_assign_object;
		$editObject->create_schedule_forms($assign_id);
	} else {
		echo "</td></tr>";
		parse_msg ("infoºSie haben kein Objekt zum Bearbeiten ausgew&auml;hlt. <br />Benutzen Sie die Suchfunktion oder w&auml;hlen Sie in der \"&Uuml;bersicht\"  oder einer ge&ouml;ffnete Liste ein Objekt aus.");
	}
}

/*****************************************************************************
Typen verwalten, views: edit_types
/*****************************************************************************/
if (($resources_data["view"]=="edit_types") || ($resources_data["view"]=="settings")) {
	
	$editSettings=new editSettings;
	$editSettings->create_types_forms();
}

/*****************************************************************************
Eigenschaften verwalten, views: edit_properties
/*****************************************************************************/
if ($resources_data["view"]=="edit_properties") {
	
	$editSettings=new editSettings;
	$editSettings->create_properties_forms();
}

/*****************************************************************************
Eigenschaften verwalten, views: edit_perms
/*****************************************************************************/
if ($resources_data["view"]=="edit_perms") {
	
	$editSettings=new editSettings;
	$editSettings->create_perms_forms();
}

/*****************************************************************************
Belegungen ausgeben, views: view_schedule
/*****************************************************************************/
if ($resources_data["view"]=="view_schedule") {
	
	$ViewSchedules=new ViewSchedules($resources_data["structure_open"]);
	$ViewSchedules->navigator();
	if (($schedule_start_time) && ($schedule_end_time))
		if ($start_list_x) //view List
			$ViewSchedules->create_schedule_list($schedule_start_time, $schedule_end_time);
		else
			$ViewSchedules->create_schedule_graphical($schedule_start_time, $schedule_end_time);
}

/*****************************************************************************
persoenliche Einstellungen verwalten, views: edit_personal_settings
/*****************************************************************************/
if ($resources_data["view"]=="edit_personal_settings") {
	
	$editSettings=new editPersonalSettings;
	$editSettings->create_personal_settings_forms();
}

/*****************************************************************************
Search 'n' browse
/*****************************************************************************/
if ($resources_data["view"]=="search") {
	
	$search=new ResourcesBrowse;
	$search->setStartLevel('');
	$search->setMode($resources_data["search_mode"]);
	$search->setSearchArray($resources_data["search_array"]);
	if ($resources_data["browse_open_level"])
		$search->setOpenLevel($resources_data["browse_open_level"]);
	$search->createSearch();
}



/*****************************************************************************
Seite abschliessen
/*****************************************************************************/
		?></td>
	</tr>
</table>
<?
page_close();
?>