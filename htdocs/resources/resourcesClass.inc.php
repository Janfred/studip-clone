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
	var $begin;				//Timestamp der Startzeit
	var $end;					//Timestamp der Endzeit
	var $repeat_end;			//Timestamp der Endzeit der Belegung (expire)
	var $repeat_quantity;		//Anzahl der Wiederholungen
	var $repeat_interval;		//Intervall der Wiederholungen
	var $repeat_month_of_year ;	//Wiederholungen an bestimmten Monat des Jahres
	var $repeat_day_of_month;	//Wiederholungen an bestimmten Tag des Monats
	var $repeat_month;			//janaja...
	var $repeat_week_of_month;	//Wiederholungen immer in dieser Woxche des Monats
	var $repeat_day_of_week;	//Wiederholungen immer an diesem Wochentag
	var $repeat_week;			//najan

	//Konstruktor
	function AssignObject($id='', $resource_id='', $assign_user_id='', $user_free_name='', $begin='', $end='', 
						$repeat_end='', $repeat_quantity='', $repeat_interval='', $repeat_month_of_year='', $repeat_day_of_month='', 
						$repeat_month='', $repeat_week_of_month='', $repeat_day_of_week='', $repeat_week='') {
		global $RELATIVE_PATH_RESOURCES;
		
	 	require_once ($RELATIVE_PATH_RESOURCES."/lib/list_assign.inc.php");
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");

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
			$this->begin = func_get_arg(4);
			$this->end = func_get_arg(5);
			$this->repeat_end = func_get_arg(6);
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
			$this->isNewObject =TRUE;
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

	function getOwnerName($explain=FALSE, $id='') {
		global $TERMIN_TYP;

		if (!$id)
			$id=$this->owner_id;

		switch (ResourceObject::getOwnerType($id)) {
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
						return $this->db->f("Name")." (Einrichtung)";
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
			case "date":
				$query = sprintf("SELECT Name, content, date_typ FROM termine LEFT JOIN seminare ON (seminar_id = range_id) WHERE termin_id='%s' ",$id);
				$this->db->query($query);
				if ($this->db->next_record())
					if (!$explain)
						return $this->db->f("Name");
					else
						return $this->db->f("Name")." (".$TERMIN_TYP[$this->db->f("date_typ")]["name"].")";	
			break;
			case "global":
			default:
				return "unbekannt";
			break;
		}
	}
	

	function getUsername($use_free_name=TRUE) {
		if ($this->assign_user_id) 
			return $this->getOwnerName(TRUE, $this->assign_user_id);
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
		if (!$this->begin)
			return time();
		else
			return $this->begin;
	}

	function getEnd() {
		if (!$this->end)
			return time()+3600;
		else
			return $this->end;
	}

	function getRepeatEnd() {
		if (!$this->repeat_end)
			return $this->end;
		else
			return $this->repeat_end;
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

	function getRepeatMonth() {
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
			if (($this->begin >= $a["beginn"]) &&($this->begin <= $a["ende"]))
				if ($this->repeat_end==$a["vorles_ende"])
					return true;
		return false;
	}
	
	function checkOverlap() {
		//we check overlaps always for a whole day
		$start = mktime (0,0,0, date("n", $this->begin), date("j", $this->begin), date("Y", $this->begin));
		if ($this->repeat_end)
			$end = mktime (23,59,59, date("n", $this->repeat_end), date("j", $this->repeat_end), date("Y", $this->repeat_end));
		else
			$end = mktime (23,59,59, date("n", $this->end), date("j", $this->end), date("Y", $this->end));
			
		list_restore_assign($this, $this->resource_id, $start, $end);
		
		if ($this->isNewObject)
			create_assigns($this, $this);
		
		if (is_array($this->events))
			$keys=array_keys($this->events);

		//ok, a very heavy algorhytmus do detect the overlaps...
		for ($i1=0; $i1<count($this->events); $i1++) {
			$val = $this->events[$keys[$i1]];
			for ($i2=0; $i2<count($this->events); $i2++) {
				$val2 = $this->events[$keys[$i2]];
				if ($val2->getId() != $val->getId())
					if ((($val->getEnd() >= $val2->getBegin()) &&($val->getEnd() <= $val2->getEnd()))
					|| (($val->getBegin() >= $val2->getBegin()) &&($val->getBegin() <= $val2->getEnd()))
					|| (($val2->getEnd() >= $val->getBegin()) &&($val2->getEnd() <= $val->getEnd()))
					|| (($val2->getBegin() >= $val->getBegin()) &&($val2->getBegin() <= $val->getEnd()))) {
						if (($val2->getAssignId()	 != $this->getId()) &&($val->getAssignId() == $this->getId()))
							$overlaps[$val2->getAssignId()] =$val2->getAssignId();
				}
			}
		}
		return $overlaps;
	}

	function setResourceId($value) {
		$this->resource_id=$value;
		$this->chng_flag=TRUE;
	}

	function setUserFreeName($value) {
		$this->user_free_name=$value;
		$this->chng_flag=TRUE;
	}
	
	function setAssignUserId($value) {
		$this->assign_user_id=$value;
		$this->chng_flag=TRUE;
	}

	function setBegin($value) {
		$this->begin=$value;
		$this->chng_flag=TRUE;
	}

	function setEnd($value) {
		$this->end=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatEnd() {
		$this->repeat_end=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatQuantity($value) {
		$this->repeat_quantity=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatInterval($value) {
		$this->repeat_interval=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatMonthOfYear($value) {
		$this->repeat_month_of_year=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatDayOfMonth($value) {
		$this->repeat_day_of_month=$value;
		$this->chng_flag=TRUE;
	}

	function setRepeatWeekOfMonth($value) {
		$this->repeat_week_of_month=$value;
		$this->chng_flag=TRUE;
	}
	
	function setRepeatDayOfWeek($value) {
		$this->repeat_day_of_week=$value;
		$this->chng_flag=TRUE;
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
			$this->begin =$this->db->f("begin");
			$this->end = $this->db->f("end");
			$this->repeat_end = $this->db->f("repeat_end");
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
		if (($this->chng_flag) || ($create)) {
			$chdate = time();
			$mkdate = time();
			if($create) {
				$query = sprintf("INSERT INTO resources_assign SET assign_id='%s', resource_id='%s', " 
					."assign_user_id='%s', user_free_name='%s', begin='%s', end='%s', repeat_end='%s', "
					."repeat_quantity='%s', repeat_interval='%s', repeat_month_of_year='%s', repeat_day_of_month='%s',  "
					."repeat_month='%s', repeat_week_of_month='%s', repeat_day_of_week='%s', repeat_week='%s', "
					."mkdate='%s' "
							 , $this->id, $this->resource_id, $this->assign_user_id, $this->user_free_name, $this->begin
							 , $this->end, $this->repeat_end, $this->repeat_quantity, $this->repeat_interval
							 , $this->repeat_month_of_year, $this->repeat_day_of_month, $this->repeat_month
							 , $this->repeat_week_of_month, $this->repeat_day_of_week, $this->repeat_week
							 , $mkdate);
			} else {
				$query = sprintf("UPDATE resources_assign SET resource_id='%s', " 
					."assign_user_id='%s', user_free_name='%s', begin='%s', end='%s', repeat_end='%s', "
					."repeat_quantity='%s', repeat_interval='%s', repeat_month_of_year='%s', repeat_day_of_month='%s',  "
					."repeat_month='%s', repeat_week_of_month='%s', repeat_day_of_week='%s', repeat_week='%s' "
					." WHERE assign_id='%s' "
							 , $this->resource_id, $this->assign_user_id, $this->user_free_name, $this->begin 
							 , $this->end, $this->repeat_end, $this->repeat_quantity, $this->repeat_interval
							 , $this->repeat_month_of_year, $this->repeat_day_of_month, $this->repeat_month
							 , $this->repeat_week_of_month, $this->repeat_day_of_week, $this->repeat_week
							 , $this->id);
			}
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE resources_assign SET chdate='%s' WHERE assign_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				return TRUE;
			} else
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
	var $begin;				//begin timestamp
	var $end;					//end timestamp

	//Konstruktor
	function AssignEvent($assign_id, $begin, $end, $resource_id, $assign_user_id, $user_free_name='') {
		global $user;
		$this->user_id = $user->id;
		$this->db=new DB_Seminar;

		$this->assign_id=$assign_id;
		$this->begin=$begin;
		$this->end=$end;
		$this->resource_id=$resource_id;
		$this->assign_user_id=$assign_user_id;
		$this->user_free_name=$user_free_name;
		$this->id = md5(uniqid("jasony"));
	}

	function getId() {
		return $this->id;
	}

	function getAssignId() {
		return $this->assign_id;
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
			return assignObject::getOwnerName(TRUE, $this->assign_user_id);
		elseif ($use_free_name)
			return $this->getUserFreeName();
		else 
			return FALSE;
	}
	
	function getName() {
		return $this->getUsername();
	}

	function getBegin() {
		if (!$this->begin)
			return time();
		else
			return $this->begin;
	}

	function getEnd() {
		if (!$this->end)
			return time()+3600;
		else
			return $this->end;
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

	var $begin;	// starttime as unix-timestamp
	var $end;		// endtime as unix-timestamp
	var $assign;	// ressources-assignements (Object[])
	var $range_id;		// range_id (String)
	var $user_id;    // userId from PhpLib (String)
	
	// Konstruktor
	// if activated without timestamps, we take the current semester
	function AssignEventList($begin = 0, $end = 0, $resource_id='', $range_id='', $user_id='', $sort = TRUE){
	 	global $RELATIVE_PATH_RESOURCES, $SEMESTER, $SEM_ID, $user;
	 	
	 	require_once ($RELATIVE_PATH_RESOURCES."/lib/list_assign.inc.php");
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	 	
	 	
		if (!$begin)
			$begin = $SEMESTER[$SEM_ID]["beginn"];
		if (!$end )
			$end = $SEMESTER[$SEM_ID]["ende"];
		
		
		$this->begin = $begin;
		$this->end = $end;
		$this->resource_id = $resource_id;
		$this->range_id = $range_id;
		$this->user_id = $user_id;
		$this->restore();
		if($sort)
			$this->sort();
	}
	
	// public
	function getBegin(){
		return $this->begin;
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
		list_restore_assign($this, $this->resource_id,  $this->begin, $this->end);
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
	
} 

/*****************************************************************************
ResourcesInstituteList, creates a list for all resources for one user
/*****************************************************************************/

class ResourcesUserRoomsList {
	var $user_id;    	// userId from PhpLib (String)
	var $resources;	// the results
	
	// Konstruktor
	function ResourcesUserRoomsList ($user_id ='', $sort= TRUE) {
	 	global $RELATIVE_PATH_RESOURCES, $user;
	 	
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");

		$this->user_id = $user_id;
		if (!$this->user_id)
			$this->user_id = $user->id;
		
		$this->category_id = $category_id;	
		$this->restore();
		if($sort)
			$this->sort();
	}
	
	function walkThread ($resource_id) {
		$db=new DB_Seminar;	
		$db2=new DB_Seminar;
		
		$query = sprintf ("SELECT resource_id FROM resources_categories LEFT JOIN resources_objects USING (category_id) WHERE resources_categories.name = 'Raum' AND resources_objects.resource_id = '%s' ", $resource_id);
		$db->query($query);
		if ($db->nf()) {
			$resource_object = new ResourceObject ($resource_id);
			$this->resources[$resource_id] = $resource_object;
		}

		//subcurse
		$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$resource_id."' ");
		while ($db2->next_record())
			$this->walkThread($db2->f("resource_id"));
	}
	
	
	// private
	function restore() {
		global $perm;
		
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		//if perm is root, load all rooms
		if ($perm->have_perm ("root")) {
			$query = sprintf ("SELECT resource_id FROM resources_categoriesres LEFT JOIN resources_objects USING (category_id) WHERE resources_categories.name = 'Raum' ");
			$db->query($query);
			while ($db->next_record()) {
				$resource_object = new ResourceObject ($db->f("resource_id"));
				$this->resources[$db->f("resource_id")] = $resource_object;
			}
		//if tutor, dozent or admin, load all the rooms of all the Einrichtungen he is member of
		} elseif  ($perm->have_perm ("tutor")) {
			$query = sprintf ("SELECT Institut_id FROM user_inst  WHERE inst_perms IN ('tutor', 'dozent', 'admin') AND user_id = '%s' ", $this->user_id);
			$db->query($query);
			while ($db->next_record()) {
				$query2 = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $db->f("Institut_id"));
				$db2->query($query2);
				while ($db2->next_record()) {
					$this->walkThread($db2->f("resource_id"));
				}
				$query2 = sprintf ("SELECT resource_id FROM resources_user_resources WHERE user_id = '%s' ", $db->f("Institut_id"));
				$db2->query($query2);
				while ($db2->next_record()) {
					$this->walkThread($db2->f("resource_id"));
				}
			}
		}
		
		if (!$perm->have_perm("admin")) {
			$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $this->user_id);
			$db->query($query);

			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
			$query = sprintf ("SELECT resource_id FROM resources_user_resources WHERE user_id = '%s' ", $this->user_id);
			$db->query($query2);
			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
		}
	}
	
	// public
	function numberOfEvents() {
		return sizeof($this->resources);
	}
	
	function existEvent() {
		return sizeof($this->resources) > 0 ? TRUE : FALSE;
	}
	
	// public
	function nextEvent() {
		if (is_array($this->resources))
			if(list(,$ret) = each($this->resources));
				return $ret;
		return FALSE;
	}
	
	function sort(){
		if ($this->resources) 
			usort($this->resources,"cmp_resources");
	}
} 


/*****************************************************************************
resourceObjeckt, zentrale Klasse der Ressourcen Objecte
/*****************************************************************************/
class ResourceObject {
	var $id;					//resource_id des Objects;
	var $db;					//Datenbankanbindung;
	var $name;				//Name des Objects
	var $description;			//Beschreibung des Objects;
	var $owner_id;			//Owner_id;
	var $category_id;			//Die Kategorie des Objects
	var $invetar_num;			//Die Inventarnummer des Objects;
	var $parent_bind=FALSE;	//Verkn&uuml;pfung mit Parent?

	
	//Konstruktor
	function ResourceObject($name='', $description='', $inventar_num='', $parent_bind='', $root_id='', $parent_id='', $category_id='', $owner_id='', $resource_id='') {
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
		$old_value = $this->owner_id;
		$this->owner_id=$owner_id;
		$this->chng_flag = TRUE;
		if ($old_value != $owner_id)
			return TRUE;
		else
			return FALSE;
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

	function getCategoryName() {
		$query = sprintf("SELECT name FROM resources_categories WHERE category_id='%s' ",$this->category_id);
		$this->db->query($query);
		if ($this->db->next_record())
			return $this->db->f("name");
		else
			return FALSE;
	}

	function getCategoryId() {
		return $this->category_id;
	}

	function getDescription() {
		return $this->description;
	}

	function getOwnerId() {
		return $this->owner_id;
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

		//Is it a entry for "everyone"?
		if ($id == "all")
			return "all";
		
		//Ist es eine Veranstaltung?
		$query = sprintf("SELECT Seminar_id FROM seminare WHERE Seminar_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "sem";

		//Ist es ein Nutzer?
		$query = sprintf("SELECT user_id FROM auth_user_md5 WHERE user_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "user";
		
		//Ist es ein Termin?
		$query = sprintf("SELECT termin_id FROM termine WHERE termin_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "date";

		//Ist es ein Institut?
		$query = sprintf("SELECT Institut_id FROM Institute WHERE Institut_id='%s' ",$id);
		$this->db->query($query);
		if ($this->db->next_record())
			return "inst";

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

		switch (ResourceObject::getOwnerType($id)) {
			case "all":
				if (!$explain)
					return "Jeder";
				else
					return "Jeder (alle Nutzer)";
			break;
			case "global":
				if (!$explain)
					return "Global";
				else
					return "Global (zentral verwaltet)";
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
						return $this->db->f("Name")." (Einrichtung)";
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
		global $PHP_SELF;
		
		if (!$id)
			$id=$this->owner_id;
		switch ($this->getOwnerType($id)) {
			case "global":
				return $PHP_SELF;
			case "all":
				return $PHP_SELF;
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
		// Natuerlich nur Speichern, wenn sich was gaendert hat oder das Object neu angelegt wird
		if(($this->chng_flag) || ($create)) {
			$chdate = time();
			$mkdate = time();
			
			if($create) {
				//create level value
				if (!$this->parent_id)
					$level=0;
				else {
					$query = sprintf("SELECT level FROM resources_objects WHERE resource_id = '%s'", $this->parent_id);
					$this->db->query($query);
					$this->db->next_record();
					$level = $this->db->f("level") +1;
				}

				$query = sprintf("INSERT INTO resources_objects SET resource_id='%s', root_id='%s', " 
					."parent_id='%s', category_id='%s', owner_id='%s', level='%s', name='%s', description='%s', "
					."inventar_num='%s', parent_bind='%s', mkdate='%s', chdate='%s' "
							 , $this->id, $this->root_id, $this->parent_id, $this->category_id, $this->owner_id, $level
							 , $this->name, $this->description, $this->inventar_num, $this->parent_bind
							 , $mkdate, $chdate);
			} else
				$query = sprintf("UPDATE resources_objects SET root_id='%s'," 
					."parent_id='%s', category_id='%s', owner_id='%s', name='%s', description='%s', "
					."inventar_num='%s', parent_bind='%s' WHERE resource_id='%s' "
							 , $this->root_id, $this->parent_id, $this->category_id, $this->owner_id
							 , $this->name, $this->description, $this->inventar_num, $this->parent_bind
							 , $this->id);
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$query = sprintf("UPDATE resources_objects SET chdate='%s' WHERE resource_id='%s' ", $chdate, $this->id);
				$this->db->query($query);
				return TRUE;
			} else
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
AssignObjectPerms, stellt Perms zum Ressourcen Object zur 
Verfuegung
/*****************************************************************************/

class AssignObjectPerms extends ResourcesPerms {
	var $user_id;
	var $db;
	var $db2;
	var $assign_id;
	
	function AssignObjectPerms ($assign_id, $user_id='') {
		global $user, $perm;
		
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		if ($user_id)
			$this->$user_id=$user_id;
		else
			$this->user_id=$user->id;
		
		$this->assign_id=$assign_id;
		
		//check if user is root
		if ($perm->have_perm("root")) {
			$this->perm="admin";
		} else //check if resources admin
			if ($this->getGlobalPerms() == "admin")
				$this->perm="admin";

		//check if the user assigns the assign 
		if ($this->perm != "admin") {
			$this->db->query("SELECT assign_user_id FROM resources_assign WHERE assign_user_id='$this->user_id' AND assign_id = '$this->assign_id' ");
			if ($this->db->next_record()) {
				$this->owner=TRUE;
				$this->perm="admin";
			} else {
				$this->owner=FALSE;
			}
		}
		
		//else check if the user is admin of the assigned resource
		if ($this->perm != "admin") {
			$this->db->query("SELECT resource_id FROM resources_assign WHERE assign_id = '$this->assign_id' ");
			if ($this->db->next_record()) {		
				$ObjectPerms = new ResourcesObjectPerms($this->db->f("resource_id"));
				if ($ObjectPerms->getUserPerm () == "admin")
					$this->perm="admin";
			}
		}
	}
	
	function getUserPerm () {
		return $this->perm;
	}
	
	function getUserIsOwner () {
		return $this->owner;
	}
	
	function getId () {
		return $this->assign_id;	
	}

	function getUserId () {
		return $this->user_id;	
	}
}

/*****************************************************************************
ResourcesObjectPerms, stellt Perms zum Ressourcen Object zur 
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
		if ($this->perm != "admin") {			
			$this->db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$this->user_id' AND resource_id = '$this->resource_id' ");
			if ($this->db->next_record()) {
				$this->owner=TRUE;
				$this->perm="admin";
			} else {
				$this->owner=FALSE;
			}
		}
		
		//else check all the other possibilities
		if ($this->perm != "admin") {
			$my_objects=search_administrable_objects();
			//echo serialize ($my_objects);
			//check if one of my administrable (system) objects owner of the resourcen object, so that I am too...
			foreach ($my_objects as $key=>$val) {
				$this->db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$key' AND resource_id = '$this->resource_id' ");
				if ($this->db->next_record())
					if ($val["perms"] == "admin")
						$this->perm="admin";
					else
						$this->perm="user";
				
				if ($this->perm=="admin")
					break;
				
				//also check the additional perms...
				$this->db->query("SELECT perms FROM resources_user_resources  WHERE user_id='$key' AND resource_id = '$this->resource_id' ");
				if ($this->db->next_record())
					$this->perm=$this->db->f("perms");

				if ($this->perm=="admin")
					break;
			}
		}

		//if all the check don't work, we have to take a look to the superordinated objects
		if ($this->perm != "admin") {
			foreach ($my_objects as $key=>$val) {
				$query = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $this->resource_id);
				$this->db->query($query);	
				$this->db->next_record();
	
				$superordinated_id=$this->db->f("parent_id");
				$top=FALSE;

				while ((!$top) && ($k<10000) && ($superordinated_id)) {
					$this->db2->query("SELECT owner_id, resource_id FROM resources_objects WHERE owner_id='$key' AND resource_id = '$superordinated_id' ");
					if ($this->db2->next_record()) {
						if ($val["perms"] == "admin")
							$this->perm="admin";
						else
							$this->perm="user";
					}
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
	
	function havePerm ($perm) {
		if ($perm == "admin") {
			if ($this->getUserPerm () == "admin")
				return TRUE;
		} elseif ($perm == "user") {
			if (($this->getUserPerm () == "admin") || ($this->getUserPerm () == "user"))
				return TRUE;
		} else
			return FALSE;
	}

	function getUserPerm () {
		return $this->perm;
	}
	
	function getUserIsOwner () {
		return $this->owner;
	}

	function getId () {
		return $this->resource_id;	
	}

	function getUserId () {
		return $this->user_id;	
	}
	
}

/*****************************************************************************
ResourcesUserRoots, stellt Stamm-Ressourcen zur Verfuegung
/*****************************************************************************/

class ResourcesRootThreads {
	var $user_global_perm;			//Globaler Status des Users, fuer den Klasse initiert wird
	var $range_id;					//the id of the User (could be a Person, Einrichtung oder Veranstaltung)
	var $my_roots;					//Alle meine Ressourcen-Staemme
	
	//Konstruktor
	function ResourcesRootThreads($range_id='') {
		global $user, $perm, $auth;
		
		if (!$this->range_id)
			$this->range_id=$user->id;

		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$db3=new DB_Seminar;
		$resPerms= new ResourcesPerms($this->range_id);
		
		if(func_num_args() == 1){
			$this->range_id = func_get_arg(0);
		}
		
		if (get_object_type($this->range_id) == "user") {
			//load the global perms in the resources-system (check if the user ist resources-root)
			$this->resources_global_perm=$resPerms->getGlobalPerms();
			//load the global studip perms (check, if user id root)
			$this->user_global_perm=get_global_perm($this->range_id);
		
			if ($this->resources_global_perm == "admin")
				$global_perm="root";
			else
				$global_perm=$this->user_global_perm;
		}
		
		//root or resoures root are able to see all resources (roots in tree)
		if ($global_perm == "root") {
			$db->query("SELECT resource_id FROM resources_objects WHERE parent_id='0'");
			while ($db->next_record())
				$this->my_roots[$db->f("resource_id")]=$db->f("resource_id");
		} else {
			$my_objects=search_administrable_objects();
			$my_objects[$user->id]=TRUE;
			
			//create the clause with all my id's
			$i=0;
			$clause = " (";
			foreach ($my_objects as $key=>$val) {
				if ($i)
					$clause .= ", ";
				$clause .= "'$key'";
				$i++;
			}
			$clause .= ") ";
			
			//all objects where i'am having owner perms...
			$query = sprintf ("SELECT resource_id, parent_id, root_id, level FROM resources_objects WHERE owner_id IN %s ORDER BY level DESC", $clause);
			$db->query($query);
			while ($db->next_record()) {
				$my_resources[$db->f("resource_id")]=array("root_id" =>$db->f("root_id"), "parent_id" =>$db->f("parent_id"), "level" =>$db->f("level"));
				$roots[$db->f("root_id")][]=$db->f("resource_id");
			}
			
			//...and all objects where i'am having add perms...
			$query = sprintf ("SELECT resources_objects.resource_id, parent_id, root_id, level FROM resources_user_resources LEFT JOIN resources_objects USING (resource_id) WHERE user_id IN %s ORDER BY level DESC", $clause);
			$db->query($query);
			while ($db->next_record()) {
				$my_resources[$db->f("resource_id")]=array("root_id" =>$db->f("root_id"), "parent_id" =>$db->f("parent_id"), "level" =>$db->f("level"));
				$roots[$db->f("root_id")][]=$db->f("resource_id");
			}

			foreach ($my_resources as $key => $val) {
				if (!$this->my_roots[$key]) {
				if (sizeof($roots[$val["root_id"]]) == 1)
					$this->my_roots[$key] = $key;
				//there are more than 2 resources in one thread...
				else {
					$query = sprintf ("SELECT resource_id, parent_id, name FROM resources_objects WHERE resource_id = '%s' ", $key);
					$db->query($query);
					$db->next_record();
					$superordinated_id=$db->f("parent_id");
					$top=FALSE;
					$last_found=$key;
					while ((!$top) && ($superordinated_id)) {
						$query = sprintf ("SELECT resource_id, parent_id, name FROM resources_objects WHERE resource_id = '%s' ", $db->f("parent_id"));
						$db->query($query);
						$db->next_record();

						if ($my_resources[$db->f("resource_id")]) {
							if ($last_found)
								unset ($my_resources[$last_found]);
							$last_found= $db->f("resource_id");
						}

						$superordinated_id=$db->f("parent_id");
						if ($db->f("parent_id") == "0")
							$top = TRUE;
						
					}

					$this->my_roots[$last_found] = $last_found;
				}
				}
			}
		}
	
	}
	
	//public
	function getRoots() {
		return $this->my_roots;
	}
}

/*****************************************************************************
ResourcesMsg, class for all the msg stuff
/*****************************************************************************/

class ResourcesMsg {
	var $msg;
	var $codes;
	
	//Konstruktor
	function ResourcesMsg() {
		global $RELATIVE_PATH_RESOURCES;
		
	 	include ($RELATIVE_PATH_RESOURCES."/views/msgs_resources.inc.php");
	}
				
	function addMsg($msg_code) {
		$this->codes[]=$msg_code;
	}
	
	function checkMsgs() {
		if ($this->codes)
			return TRUE;
		else 
			return FALSE;
	}
	
	function displayAllMsg($view_mode = "window") {
		if (is_array($this->codes)) {
			foreach ($this->codes as $val)
				$collected_msg.=($this->msg[$val]["mode"]."§".$this->msg[$val]["msg"]."§");
			if ($view_mode == "window")
				parse_window($collected_msg, "§", $this->msg[$this->codes[0]]["titel"], "<a href=\"resources.php\">"._("zur&uuml;ck")."</a>");
			else
				parse_msg($collected_msg, "§", "blank", 1, FALSE);
		}
	}
	
	function displayMsg($msg_code, $view_mode = "window") {
		if ($view_mode == "window")
			parse_window($this->msg[$msg_code]["mode"]."§".$this->msg[$msg_code]["msg"], "§", $this->msg[$msg_code]["titel"], "<a href=\"resources.php\">"._("zur&uuml;ck")."</a>");
		else
			parse_msg($this->msg[$msg_code]["mode"]."§".$this->msg[$msg_code]["msg"], "§", "blank", 1, FALSE);
	}
}
	
	//Konstruktor
?>