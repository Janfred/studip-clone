<?
/*
resourcesClass.php - 0.8
Klassen fuer Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>

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
AssignObject, zentrale Klasse der Objecte der Belegung
/*****************************************************************************/
class AssignObject {
	var $db;					//Datenbankanbindung;
	var $id;					//Id des Belegungs-Objects
	var $resource_id;			//resource_id des verknuepten Objects;
	var $assign_user_id;		//id des verknuepten Benutzers der Ressource
	var $user_free_name;		//freier Name fuer Belegung
	var $begin_ts;				//Timestamp der Startzeit
	var $end_ts;				//Timestamp der Endzeit
	var $repeat_end_ts;		//Timestamp der Endzeit der Belegung (expire)
	var $repeat_quantity;		//Anzahl der Wiederholungen
	var $repeat_interval;		//Intervall der Wiederholungen
	var $repeat_month_of_year ;	//Wiederholungen an bestimmten Monat des Jahres
	var $repeat_day_of_month;	//Wiederholungen an bestimmten Tag des Monats
	var $repeat_month;			//janaja...
	var $repeat_week_of_month;	//Wiederholungen immer in dieser Woxche des Monats
	var $repeat_day_of_week;	//Wiederholungen immer an diesem Wochentag
	var $repeat_week;			//najan

	//Konstruktor
	function AssignObject($id='', $resource_id='', $assign_user_id='', $user_free_name='', $begin_ts='', $end_ts='', 
						$repeat_end_ts='', $repeat_quantity='', $repeat_interval='', $repeat_month_of_year='', $repeat_day_of_month='', 
						$repeat_month='', $repeat_week_of_month='', $repeat_day_of_week='', $repeat_week='') {
		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;

		if(func_num_args() == 1) {
			$this->id = func_get_arg(0);
			$this->restore($this->id);
		} elseif(func_num_args() == 15) {
			$this->id=func_get_arg(0);
			$this->resource_id = func_get_arg(1);
			$this->assign_user_id = func_get_arg(2);
			$this->user_free_name = func_get_arg(3);
			$this->begin_ts = func_get_arg(4);
			$this->end_ts = func_get_arg(5);
			$this->repeat_end_ts = func_get_arg(6);
			$this->repeat_quantity = func_get_arg(7);
			$this->repeat_interval = func_get_arg(8);
			$this->repeat_month_of_year  = func_get_arg(9);
			$this->repeat_day_of_month = func_get_arg(10);
			$this->repeat_month = func_get_arg(11);
			$this->repeat_week_of_month = func_get_arg(12);
			$this->repeat_day_of_week = func_get_arg(13);
			$this->repeat_week = func_get_arg(14);
			if (!$this->id)
				$this->id=$this->createId();
		} 	
	}

	function createId() {
		return md5(uniqid("BartSimpson"));
	}
	
	function create() {
		return $this->store(TRUE);
	}

	function getId() {
		return $this->id;
	}
	
	function getAssignUserId() {
		return $this->assign_user_id;
	}

	function getUsername($use_free_name=TRUE) {
		if ($this->assign_user_id) 
			return resourceObject::getOwnerName(TRUE, $this->assign_user_id);
		elseif ($use_free_name)
			return $this->getUserFreeName();
		else 
			return FALSE;
	}

	function getResourceId() {
		return $this->resource_id;
	}

	function getUserFreeName() {
		return $this->user_free_name;
	}

	function getBegin() {
		if (!$this->begin_ts)
			return time();
		else
			return $this->begin_ts;
	}

	function getEnd() {
		if (!$this->end_ts)
			return time()+3600;
		else
			return $this->end_ts;
	}

	function getRepeatEnd() {
		if (!$this->repeat_end_ts)
			return time();
		else
			return $this->repeat_end_ts;
	}

	function getRepeatQuantity() {
		return $this->repeat_quantity;
	}

	function getRepeatInterval() {
		return $this->repeat_interval;
	}

	function getRepeatMonthOfYear() {
		return $this->repeat_month_of_year ;
	}

	function getRepeatDayOfMonth() {
		return $this->repeat_day_of_month;
	}

	function repeatMonth() {
		return $this->repeat_month;
	}
	function getRepeatWeekOfMonth() {
		return $this->repeat_week_of_month;
	}
	function getRepeatDayOfWeek() {
		return $this->repeat_day_of_week;
	}
	
	function getRepeatWeek() {
		return $this->repeat_week;
	}
	
	function getRepeatMode() {
		if ((!$this->repeat_month_of_year) && (!$this->repeat_week_of_moth) && (!$this->repeat_day_of_month) && (!$this->repeat_day_of_week) && (!$this->repeat_quantity))
			return "na";
		elseif ($this->repeat_month_of_year)
			return "y";
		elseif ($this->repeat_week_of_moth || $this->repeat_day_of_month)
			return "m";
		elseif ($this->repeat_day_of_week)
			return "w";
		else
			return "d";
	}
	
	function isRepeatEndSemEnd() {
		global $SEMESTER;

		foreach ($SEMESTER as $a)	
			if (($this->begin_ts >= $a["beginn"]) &&($this->begin_ts <= $a["ende"]))
				if ($this->repeat_end_ts==$a["vorles_ende"])
					return true;
		return false;
	}

	function restore($id='') {
		if(func_num_args() == 1)
			$query = sprintf("SELECT * FROM resources_assign WHERE assign_id='%s' ",$id);
		else 
			$query = sprintf("SELECT * FROM resources_assign WHERE assign_id='%s' ",$this->id);
		$this->db->query($query);
		
		if($this->db->next_record()) {
			$this->id = $id;
			$this->resource_id = $this->db->f("resource_id");
			$this->assign_user_id = $this->db->f("assign_user_id");
			$this->user_free_name = $this->db->f("user_free_name");
			$this->begin_ts =$this->db->f("begin");
			$this->end_ts = $this->db->f("end");
			$this->repeat_end_ts = $this->db->f("repeat_end");
			$this->repeat_quantity = $this->db->f("repeat_quantity");
			$this->repeat_interval = $this->db->f("repeat_interval");
			$this->repeat_month_of_year  =$this->db->f("repeat_month_of_year");
			$this->repeat_day_of_month =$this->db->f("repeat_day_of_month");
			$this->repeat_month = $this->db->f("repeat_month");
			$this->repeat_week_of_month = $this->db->f("repeat_week_of_month");
			$this->repeat_day_of_week = $this->db->f("repeat_day_of_week");
			$this->repeat_week = $this->db->f("repeat_week");
			return TRUE;
		}
		return FALSE;
	}

	function store($create=''){
		// Natuerlich nur Speichern, wenn sich was geaendert hat oder das Object neu angelegt wird
		if(($this->chng_flag) || ($create)) {
			$chdate = time();
			$mkdate = time();
			if($create)
				$query = sprintf("INSERT INTO resources_assign SET assign_id='%s', resource_id='%s', " 
					."assign_user_id='%s', user_free_name='%s', begin='%s', end='%s', repeat_end='%s', "
					."repeat_quantity='%s', repeat_interval='%s', repeat_month_of_year='%s', repeat_day_of_month='%s',  "
					."repeat_month='%s', repeat_week_of_month='%s', repeat_day_of_week='%s', repeat_week='%s', "
					."mkdate='%s', chdate='%s' "
							 , $this->id, $this->resource_id, $this->assign_user_id, $this->user_free_name, $this->begin_ts
							 , $this->end_ts, $this->repeat_end_ts, $this->repeat_quantity, $this->repeat_interval
							 , $this->repeat_month_of_year, $this->repeat_day_of_month, $this->repeat_month
							 , $this->repeat_week_of_month, $this->repeat_day_of_week, $this->repeat_week
							 , $mkdate, $chdate);
			else
				$query = sprintf("UPDATE resources_assign SET resource_id='%s', " 
					."assign_user_id='%s', user_free_name='%s', begin='%s', end='%s', repeat_end='%s', "
					."repeat_quantity='%s', repeat_interval='%s', repeat_month_of_year='%s', repeat_day_of_month='%s',  "
					."repeat_month='%s', repeat_week_of_month='%s', repeat_day_of_week='%s', repeat_week='%s', "
					."chdate='%s' WHERE assign_id='%s'"
							 , $this->resource_id, $this->assign_user_id, $this->user_free_name, $this->begin_ts 
							 , $this->end_ts, $this->repeat_end_ts, $this->repeat_quantity, $this->repeat_interval
							 , $this->repeat_month_of_year, $this->repeat_day_of_month, $this->repeat_month
							 , $this->repeat_week_of_month, $this->repeat_day_of_week, $this->repeat_week
							 , $chdate, $this->id);
			if($this->db->query($query))
				return TRUE;
			return FALSE;
		}
		return FALSE;
	}

	function delete() {
		$query = sprintf("DELETE FROM resources_assign WHERE assign_id='%s'", $this->id);
		if($this->db->query($query))
			return TRUE;
		return FALSE;
	}

}

/*****************************************************************************
AssignEvent, the assigned events 
/*****************************************************************************/
class AssignEvent {
	var $db;					//Database
	var $id;					//Id from mother AssignObject
	var $resource_id;			//resource_id from mother AssignObject
	var $assign_user_id;		//user_id of mother AssignObject
	var $user_free_name;		//free owner-name of mother AssignObject
	var $begin_ts;				//begin timestamp
	var $end_ts;				//end timestamp

	//Konstruktor
	function AssignEvent($assign_id, $begin_ts, $end_ts, $resource_id, $assign_user_id, $user_free_name='') {
		global $user;
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;

		$this->id=$assign_id;
		$this->begin_ts=$begin_ts;
		$this->end_ts=$end_ts;
		$this->resource_id=$resource_id;
		$this->assign_user_id=$assign_user_id;
		$this->user_free_name=$user_free_name;
	}

	function getAssignId() {
		return $this->id;
	}
	
	function getAssignUserId() {
		return $this->assign_user_id;
	}

	function getResourceId() {
		return $this->resource_id;
	}

	function getUserFreeName() {
		return $this->user_free_name;
	}
	
	function getUsername($use_free_name=TRUE) {
		if ($this->assign_user_id) 
			return resourceObject::getOwnerName(TRUE, $this->assign_user_id);
		elseif ($use_free_name)
			return $this->getUserFreeName();
		else 
			return FALSE;
	}
	
	function getName() {
		return $this->getUsername();
	}

	function getBegin() {
		if (!$this->begin_ts)
			return time();
		else
			return $this->begin_ts;
	}

	function getEnd() {
		if (!$this->end_ts)
			return time()+3600;
		else
			return $this->end_ts;
	}

	function store($create='') {
		// Noch fraglich, ob diese Methose existieren soll. Wenn ja muesste sie eine Splittung vornehmen
	}

	function delete() {
		// Noch fraglich, ob diese Methose existieren soll. Wenn ja muesste sie eine Splittung vornehmen
	}

}

/*****************************************************************************
AssignEventList, creates a event-list for an assignobject
/*****************************************************************************/

class AssignEventList{

	var $start;	// starttime as unix-timestamp
	var $end;		// endtime as unix-timestamp
	var $assign;	// ressources-assignements (Object[])
	var $range_id;		// range_id (String)
	var $user_id;    // userId from PhpLib (String)
	
	// Konstruktor
	// if activated without timestamps, we take the current semester
	function AssignEventList($start = -1, $end = -1, $resource_id='', $range_id='', $user_id='', $sort = TRUE){
	 	global $SEMESTER, $SEM_ID, $user;
	 	
	 	require_once ($RELATIVE_PATH_RESOURCES."/lib/list_assign.inc.php");
	 	
		if(!$start)
			$start = $SEMESTER[$SEM_ID]["beginn"];
		if(!$end )
			$end = $SEMESTER[$SEM_ID]["ende"];
		
		
		$this->start = $start;
		$this->end = $end;
		$this->resource_id = $resource_id;
		$this->range_id = $range_id;
		$this->user_id = $user_id;
		$this->restore();
		if($sort)
			$this->sort();
	}
	
	// public
	function getStart(){
		return $this->start;
	}
	
	// public
	function getEnd(){
		return $this->end;
	}

	// public
	function getResourceId(){
		return $this->resource_id;
	}

	// public
	function getRangeId(){
		return $this->range_id;
	}

	// public
	function getUserId(){
		return $this->$user_id;
	}
	
	// private
	function restore(){
		list_restore_assign($this);
	}
	
	// public
	function numberOfEvents(){
		return sizeof($this->events);
	}
	
	function existEvent(){
		return sizeof($this->events) > 0 ? TRUE : FALSE;
	}
	
	// public
	function nextEvent(){
		if (is_array($this->events))
			if(list(,$ret) = each($this->events));
				return $ret;
		return FALSE;
	}
	
	function sort(){
		if($this->events)
			usort($this->events,"cmp_assign_events");
	}
	
} // class AppList 


/*****************************************************************************
resourceObjeckt, zentrale Klasse der Ressourcen Objecte
/*****************************************************************************/
class resourceObject {
	var $id;					//resource_id des Objects;
	var $db;					//Datenbankanbindung;
	var $name;				//Name des Objects
	var $description;			//Beschreibung des Objects;
	var $owner_id;			//Owner_id;
	var $category_id;			//Die Kategorie des Objects
	var $invetar_num;			//Die Inventarnummer des Objects;
	var $parent_bind=FALSE;	//Verkn&uuml;pfung mit Parent?

	
	//Konstruktor
	function resourceObject($name='', $description='', $inventar_num='', $parent_bind='', $root_id='', $parent_id='', $category_id='', $owner_id='', $resource_id='') {
		global $user;
		
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;
		
		if(func_num_args() == 1) {
			$id = func_get_arg(0);
			$this->restore($id);
		} elseif(func_num_args() == 8) {
			$this->name = func_get_arg(0);
			$this->description = func_get_arg(1);
			$this->inventar_num = func_get_arg(2);
			$this->parent_bind = func_get_arg(3);
			$this->root_id = func_get_arg(4);
			$this->parent_id = func_get_arg(5);
			$this->category_id = func_get_arg(6);
			$this->owner_id = func_get_arg(7);
			$this->id=$this->createId();
			if (!$this->root_id)
			$this->root_id = $this->id;
			$this->changeFlg=FALSE;
		}
	}

	function createId() {
		return md5(uniqid("DuschDas"));
	}

	function create() {
		return $this->store(TRUE);
	}
	
	function setName($name){
		$this->name= $name;
		$this->chng_flag = TRUE;
	}

	function setDescription($description){
		$this->description= $description;
		$this->chng_flag = TRUE;
	}

	function setCategoryId($category_id){
		$this->category_id=$category_id;
		$this->chng_flag = TRUE;
	}

	function setInventarNum($inventar_num){
		$this->inventar_num= $inventar_num;
		$this->chng_flag = TRUE;
	}

	function setParentBind($parent_bind){
		if ($parent_bind==on)
			$this->parent_bind=TRUE;
		else
			$this->parent_bind=FALSE;
		$this->chng_flag = TRUE;
	}

	function setOwnerId($owner_id){
		$this->owner_id=$owner_id;
		$this->chng_flag = TRUE;
	}
	

	function getId() {
		return $this->id;
	}

	function getRootId() {
		return $this->root_id;
	}

	function getParentId() {
		return $this->parent_id;
	}

	function getName() {
		return $this->name;
	}

	function getCategory() {
		$query = sprintf("SELECT name FROM resources_categories WHERE category_id='%s' ",$this->category_id);
		$this->db->query($query);
		if ($this->db->next_record())
			return $this->db->f("name");
		else
			return FALSE;
	}

	function getDescription() {
		return $this->description;
	}

	function getOwnerId() {
		return $this->owner_id;
	}

	function getCategoryId() {
		return $this->category_id;
	}

	function getInventarNum() {
		return $this->inventar_num;
	}

	function getParentBind() {
		return $this->parent_bind;
	}
	
	function getOwnerType($id='') {
		if (!$id)
			$id=$this->owner_id;
			
		//Ist es ein Nutzer?
		$query = sprintf("SELECT user_id FROM auth_user_md5 WHERE user_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "user";
		
		//Ist es ein Institut?
		$query = sprintf("SELECT Institut_id FROM Institute WHERE Institut_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "inst";

		//Ist es eine Verabstaltung?
		$query = sprintf("SELECT Seminar_id FROM seminare WHERE Seminar_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "sem";

		//Ist es eine Fakultaet?
		$query = sprintf("SELECT Fakultaets_id FROM Fakultaeten WHERE Fakultaets_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "fak";
		
		//dann wohl global
		return "global";
	}
	
	function getOwnerName($explain=FALSE, $id='') {
		if (!$id)
			$id=$this->owner_id;

		switch (resourceObject::getOwnerType($id)) {
			case "global":
				return "Global";
			break;
			case "user";
				if (!$explain)
					return get_fullname($id);
				else
					return get_fullname($id)." (Nutzer)";
			break;
			case "inst":
				$query = sprintf("SELECT Name FROM Institute WHERE Institut_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." (Institut)";
			break;
			case "fak":
				$query = sprintf("SELECT Name FROM Fakultaeten WHERE Fakultaets_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." (Fakult&auml;t)";
			break;
			case "sem":
				$query = sprintf("SELECT Name FROM seminare WHERE Seminar_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name"). " (Veranstaltung)";	
			break;
		}
	}
	
	function getOwnerLink($id='') {
		if (!$id)
			$id=$this->owner_id;
		switch ($this->getOwnerType($id)) {
			case "global":
				return FALSE;
			break;
			case "user":
				return  sprintf ("about?username=%s",get_username($id));
			break;
			case "inst":
				return  sprintf ("institut_main?auswahl=%s",$id);
			break;
			case "fak":
				return FALSE;
			break;
			case "sem":
				return  sprintf ("seminar_main?auswahl=%s",$id);
			break;
		}
	}
	
	function flushProperties() {
		$query = sprintf("DELETE FROM resources_objects_properties WHERE resource_id='%s' ",$this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function storeProperty ($property_id, $state) {
		$query = sprintf("INSERT INTO resources_objects_properties SET resource_id='%s', property_id='%s', state='%s' ",$this->id, $property_id, $state);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function deletePerms ($user_id) {
		$query = sprintf("DELETE FROM resources_user_resources WHERE user_id='%s' AND resource_id='%s'",$user_id, $this->id);
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
		else 
			return FALSE;
	}
	
	function storePerms ($user_id, $perms='') {
		$query = sprintf("SELECT user_id FROM resources_user_resources WHERE user_id='%s' AND resource_id='%s'",$user_id, $this->id);
		$this->db->query($query);
		
		//User_id zwingend notwendig
		if (!$user_id)
			return FALSE;
		
		//neuer Eintrag	
		if (!$this->db->num_rows()) {
			if (!$perms)
				$perms="user";
			$query = sprintf("INSERT INTO resources_user_resources SET perms='%s', user_id='%s', resource_id='%s'",$perms, $user_id, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;

		//alter Eintrag wird veraendert
		} elseif ($perms) {
			$query = sprintf("UPDATE resources_user_resources SET perms='%s' WHERE user_id='%s' AND resource_id='%s'",$perms, $user_id, $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows())
				return TRUE;
			else 
				return FALSE;
		} else
			return FALSE;
	}
	
	function restore($id='') {

		if(func_num_args() == 1)
			$query = sprintf("SELECT * FROM resources_objects WHERE resource_id='%s' ",$id);
		else 
			$query = sprintf("SELECT * FROM resources_objects WHERE resource_id='%s' ",$this->id);
		$this->db->query($query);
		
		if($this->db->next_record()) {
			$this->id = $id;
			$this->name = $this->db->f("name");
			$this->description = $this->db->f("description");
			$this->owner_id = $this->db->f("owner_id");
			$this->category_id = $this->db->f("category_id");
			$this->inventar_num = $this->db->f("inventar_num");
			$this->parent_id =$this->db->f("parent_id");
			$this->root_id =$this->db->f("root_id");
			
			if ($this->db->f("parent_bind"))
				$this->parent_bind = TRUE;
			else
				$this->parent_bind = FALSE;
			
			return TRUE;
		}
		return FALSE;
	}

	function store($create=''){
		// Natrlich nur Speichern, wenn sich was ge„ndert hat oder das Object neu angelegt wird
		if(($this->chng_flag) || ($create)) {
			$chdate = time();
			$mkdate = time();
			if($create)
				$query = sprintf("INSERT INTO resources_objects SET resource_id='%s', root_id='%s', " 
					."parent_id='%s', category_id='%s', owner_id='%s', name='%s', description='%s', "
					."inventar_num='%s', parent_bind='%s', mkdate='%s', chdate='%s' "
							 , $this->id, $this->root_id, $this->parent_id, $this->category_id, $this->owner_id
							 , $this->name, $this->description, $this->inventar_num, $this->parent_bind
							 , $mkdate, $chdate);
			else
				$query = sprintf("UPDATE resources_objects SET root_id='%s'," 
					."parent_id='%s', category_id='%s', owner_id='%s', name='%s', description='%s', "
					."inventar_num='%s', parent_bind='%s', chdate='%s' WHERE resource_id='%s' "
							 , $this->root_id, $this->parent_id, $this->category_id, $this->owner_id
							 , $this->name, $this->description, $this->inventar_num, $this->parent_bind
							 , $chdate, $this->id);
			if($this->db->query($query))
				return TRUE;
			return FALSE;
		}
		return FALSE;
	}

	function delete() {
		$query = sprintf("DELETE FROM resources_objects WHERE resource_id='%s'", $this->id);
		if($this->db->query($query))
			return TRUE;
		return FALSE;
	}
}

/*****************************************************************************
ResourcesPerms, stellt globale Perms zur Verfuegung
/*****************************************************************************/

class ResourcesPerms {
	var $user_id;
	var $db;
	var $master_string="all";			//So wird der Ressourcen-Root abgelegt
	
	function ResourcesPerms ($user_id='') {
		global $user;

		$this->db=new DB_Seminar;
		if ($user_id)
			$this->user_id=$user_id;
		else
			$this->user_id=$user->id;
	}
	
	function getGlobalPerms () {
		$this->db->query("SELECT user_id, perms FROM resources_user_resources WHERE user_id='$this->user_id' AND resource_id = '$this->master_string' ");
		if ($this->db->next_record() && $this->db->f("perms")) 
			return $this->db->f("perms");
		else
			return "user";
	}
}

/*****************************************************************************
ResourcesObjectPerms, stellt Perms zum Ressourcen Objectzur 
Verfuegung
/*****************************************************************************/

class ResourcesObjectPerms extends ResourcesPerms {
	var $user_id;
	var $db;
	var $db2;
	var $resource_id;
	
	function ResourcesObjectPerms ($resource_id, $user_id='') {
		global $user, $perm;
		
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		if ($user_id)
			$this->$user_id=$user_id;
		else
			$this->user_id=$user->id;
		
		$this->resource_id=$resource_id;
		
		//check if user is root
		if ($perm->have_perm("root")) {
			$this->perm="admin";
		} else //check if resources admin
			if ($this->getGlobalPerms() == "admin")
				$this->perm="admin";
		
		//check if the user is owner of the object	
		$this->db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$this->user_id' AND resource_id = '$this->resource_id' ");
		if ($this->db->next_record()) {
			$this->owner=TRUE;
			$this->perm="admin";
		} else {
			$this->owner=FALSE;
		}
		
		//else check all the other possibilities
		if ($this->perm != "admin") {
			$my_objects=get_my_administrable_objects();
			
			//add the user_id
			$my_objects[$this->user_id]="user"; //myself is administrable, too...
			//check if one of my administrable (system) objects owner of the resourcen object, so that I am too...
			foreach ($my_objects as $key=>$val) {
				$this->db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$key' AND resource_id = '$this->resource_id' ");
				if ($this->db->next_record())
					$this->perm="admin";
				
				if ($this->perm=="admin")
					break;
				
				//also check the additional perms...
				$this->db->query("SELECT perms FROM resources_user_resources  WHERE user_id='$key' AND resource_id = '$this->resource_id' ");
				if ($this->db->next_record())
					$this->perm=$this->db->f("perms");

				if ($this->perm=="admin")
					break;
			}
			
			//if all the check don't work, we have to take a look to the superordinated objects
			foreach ($my_objects as $key=>$val) {
				$query = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $this->resource_id);
				$this->db->query($query);	
				$this->db->next_record();
	
				$superordinated_id=$this->db->f("parent_id");
				$top=FALSE;
				
				while ((!$top) && ($k<10000) && ($superordinated_id)) {
					$this->db2->query("SELECT owner_id FROM resources_objects WHERE owner_id='$key' AND resource_id = '$superordinated_id' ");
					if ($this->db2->next_record())
						$this->perm="admin";
					$k++;
					if ($this->perm=="admin")
						break;
			
					//also check the additional perms...
					$this->db2->query("SELECT perms FROM resources_user_resources  WHERE user_id='$key' AND resource_id = '$superordinated_id' ");
					if ($this->db2->next_record())
						$this->perm=$this->db2->f("perms");
					if ($this->perm=="admin")
						break;
					
					//select the next superordinated object
					$query = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $superordinated_id);
					$this->db->query($query);						
					$this->db->next_record();
		
					$superordinated_id=$this->db->f("parent_id");
					if ($this->db->f("parent_id") == "0")
						$top = TRUE;
				}
				if ($this->perm=="admin")
					break;
			}
		}
	}
	
	function getUserPerm () {
		return $this->perm;
	}
	
	function getUserIsOwner () {
		return $this->owner;
	}
}

/*****************************************************************************
ResourcesUserRoots, stellt Stamm-Ressourcen zur Verfuegung
/*****************************************************************************/

class ResourcesUserRoots {
	var $user_global_perm;			//Globaler Status des Users, fuer den Klasse initiert wird
	var $user_id;					//User_id des Users;
	var $my_roots;					//Alle meine Ressourcen-Staemme
	
	//Konstruktor
	function ResourcesUserRoots($user_id='') {
		global $user, $perm, $auth;
		
		if (!$this->user_id)
			$this->user_id=$user->id;

		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$db3=new DB_Seminar;
		$resPerms= new ResourcesPerms($this->user_id);
		
		if(func_num_args() == 1){
			$this->user_id = func_get_arg(0);
		}

		//load the global perms in the resources-system (check if the user ist resources-root)
		$this->resources_global_perm=$resPerms->getGlobalPerms();
		//load the global studip perms (check, if user id root)
		$this->user_global_perm=get_global_perm($this->user_id);
		
		if ($this->resources_global_perm == "admin")
			$global_perm="root";
		else
			$global_perm=$this->user_global_perm;
			
		//Bestimmen aller Root Straenge auf die ich Zugriff habe
		switch ($global_perm) {
			case "root": 
				 //Root hat Zugriff auf alles, also alle Stamm-Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
			case "admin": 
				//Alle meine Institute...
				$db->query("SELECT Institut_id, inst_perms FROM user_inst WHERE inst_perms IN ('tutor', 'dozent', 'admin') AND user_inst.user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Institut_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
					if ($db->f("inst_perms") == "admin") {
						//...alle Seminare meiner Institute, in denen ich Admin bin....
						$db2->query("SELECT Seminar_id FROM seminar_inst WHERE institut_id = '".$db->f("Institut_id")."' ");
						while ($db2->next_record()) {
							$db3->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db2->f("Seminar_id")."' AND parent_id='0'");
								while ($db3->next_record())
									$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
						}
						//...alle Mitarbeiter meiner Institute, in denen ich Admin bin....
						$db2->query("SELECT user_id FROM user_inst WHERE Institut_id = '".$db->f("Institut_id")."' AND inst_perms IN ('autor', 'tutor', 'dozent') ");
						while ($db2->next_record()) {
							$db3->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db2->f("user_id")."' AND parent_id='0'");
								while ($db3->next_record())
									$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
						}
						
					}
				}
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");					
			break;
			case "dozent": 
				//Alle meine Institute...
				$db->query("SELECT Institut_id FROM user_inst WHERE inst_perms IN ('tutor', 'dozent') AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Institut_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//..und alle meine Seminare
				$db->query("SELECT Seminar_id FROM seminar_user WHERE status IN ('tutor', 'dozent') AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Seminar_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
			case "tutor": 
				//Alle meine Institute...
				$db->query("SELECT Institut_id FROM user_inst WHERE inst_perms='tutor' AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Institut_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//..und alle meine Seminare
				$db->query("SELECT Seminar_id FROM seminar_user WHERE status='tutor' AND user_id='".$this->user_id."' ");
				while ($db->next_record()) {
					$db2->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$db->f("Seminar_id")."' AND parent_id='0'");
					while ($db2->next_record())
						$this->my_roots[$db->f("resource_id")]=$db2->f("resource_id");
				}
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
			case "autor": 
				//Alle meine Ressourcen
				$db->query("SELECT resource_id FROM resources_objects WHERE owner_id='".$this->user_id."' AND parent_id='0'");
				while ($db->next_record())
					$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
			break;
		}
		//Bestimmen aller weiteren Straenge, die nicht oben schon ausgewaehlt wurden
		$db->query("SELECT resources_objects.resource_id, root_id FROM resources_user_resources LEFT JOIN resources_objects USING (resource_id) WHERE user_id='".$this->user_id."' ");
		while ($db->next_record())
			if (!$this->my_roots[$db->f("root_id")])
				$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
	}
	
	//public
	function getRoots() {
		return $this->my_roots;
	}
}

/*****************************************************************************
ResourcesError, class for all the errormsg stuff
/*****************************************************************************/

class ResourcesError {
	var $msg;
	var $codes;
	
	//Konstruktor
	function ResourcesError() {
		$this->msg[1] = array (
				"mode" => "error",
				"titel" => "Fehlende Berechtigung",
				"msg"=> "Sie haben leider keine Berechtigung, das Objekt zu bearbeiten");
	}
				
	function addMsg($msg_code) {
		$this->codes[]=$msg_code;
	}
	
	function displayAllMsg() {
		parse_window($this->msg[$msg_code]["mode"]."§".$this->msg[$msg_code]["msg"], "§", $this->msg[$msg_code]["titel"], "zZ. NA");
	}
	
	function displayMsg($msg_code) {
		parse_window($this->msg[$msg_code]["mode"]."§".$this->msg[$msg_code]["msg"], "§", $this->msg[$msg_code]["titel"], "zZ. NA");
	}
}
	
	//Konstruktor
?>