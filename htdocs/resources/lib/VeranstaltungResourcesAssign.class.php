<?
/**
* VeranstaltungResourcesAssign.class.php
* 
* updates the saved setting from dates and metadates from a Veranstaltung
* ans the linked resources (rooms)
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup	resources_modules
* @module		VeranstaltungResourcesAssign.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// VeranstaltungResourcesAssign.class.php
// Modul zum Verknuepfen von Veranstaltungszeiten mit Resourcenbelegung
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>
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

require_once ("dates.inc.php");
require_once ("config.inc.php");

class VeranstaltungResourcesAssign {
	var $db;
	var $db2;
	var $seminar_id;
	var $assign_id;
	
	//Konstruktor
	function VeranstaltungResourcesAssign ($seminar_id) {
		global $RELATIVE_PATH_RESOURCES;
	 	//make shure to load all the classes from resources, if this class is extern used °change if the classes are storen in own scripts
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesClass.inc.php");
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		$this->seminar_id = $seminar_id;
	}
	
	function deleteAssignedRooms() {
		$query = sprintf("SELECT assign_id FROM resources_assign LEFT JOIN resources_objects USING (resource_id) LEFT JOIN resources_categories USING (category_id) WHERE resources_assign.assign_user_id = '%s' AND resources_categories.name = 'Raum' ", $this->seminar_id);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$query2 = sprintf("DELETE FROM resources_assign WHERE assign_id = '%s'  ", $this->db->f("assign_id"));
			$this->db2->query($query2);			
		}
	}

	function updateAssign() {
		global $TERMIN_TYP;

		$query = sprintf("SELECT termin_id, date_typ FROM termine WHERE range_id = '%s' ", $this->seminar_id);
		$this->db->query($query);
		$course_session=FALSE;
		while ($this->db->next_record()) {
			if ($TERMIN_TYP[$this->db->f("date_typ")]["sitzung"])
				$course_session=TRUE;
			$this->changeDateAssign($this->db->f("termin_id"));
		}
		
		//kill all assigned roomes (only roomes and only resources assigned directly to the Veranstaltung, not to a termin!) to create new ones
		$this->deleteAssignedRooms();
		
		//if no course session date exits, we take the metadates (only in this case! else we take only the concrete dates from the termin table!)
		if (!$course_session)
			$this->changeMetaAssigns();
	}

	function changeMetaAssigns($resource_id='') {
		global $SEMESTER;

		//load data of the Veranstaltung
		$query = sprintf("SELECT start_time, duration_time, metadata_dates FROM seminare WHERE Seminar_id = '%s'", $this->seminar_id);
		$this->db->query($query);
		$this->db->next_record();

		$term_data = unserialize ($this->db->f("metadata_dates"));

		//determine first day of the start-week as sem_begin
		if ($term_data["start_woche"] >= 0) {
			foreach ($SEMESTER as $val)
				if (($this->db->f("start_time") >= $val["beginn"]) AND ($this->db->f("start_time") <= $val["ende"]))
					$sem_begin = mktime(0, 0, 0, date("n",$val["vorles_beginn"]), date("j",$val["vorles_beginn"])+($term_data["start_woche"] * 7),  date("Y",$val["vorles_beginn"]));
					$val["vorles_beginn"];
		} else  {
			$dow = date("w", $term_data["start_termin"]);
			//calculate corrector to get first day of the week
			if ($dow <= 5)
				$corr = ($dow -1) * -1;
			elseif ($dow == 6)
				$corr = 2;
			elseif ($dow == 0)
				$corr = 1;
			else
				$corr = 0;
			
			$sem_begin = mktime(0, 0, 0, date("n",$term_data["start_termin"]), date("j",$term_data["start_termin"])+$corr,  date("Y",$term_data["start_termin"]));
		}

		//determine the last day as sem_end
		foreach ($SEMESTER as $val)
			if  ((($this->db->f("start_time") + $this->db->f("duration_time") + 1) >= $val["beginn"]) AND (($this->db->f("start_time") + $this->db->f("duration_time") +1) <= $val["ende"])) {
				$sem_end=$val["vorles_ende"];
			}
		
		$interval = $term_data["turnus"] + 1;
				
		//create the assigns
		if (is_array($term_data["turnus_data"]))
			foreach ($term_data["turnus_data"] as $val) {
				$start_time = mktime ($val["start_stunde"], $val["start_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1), date("Y", $sem_begin));
				$end_time = mktime ($val["end_stunde"], $val["end_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1), date("Y", $sem_begin));
			
				$day_of_week = date("w", $start_time);
				if ($day_of_week == 0)
					$day_of_week = 7;

				$createAssign=new AssignObject(FALSE, $val["resource_id"], $this->seminar_id, $user_free_name, 
											$start_time, $end_time, $sem_end,
											-1, $interval, 0, 0, 0, 
											0, $day_of_week, 0);
				$createAssign->create();
				$created_ids[] = $createAssign->getId();
			}
	
	if ($created_ids)
		return $created_ids;
	else
		return FALSE;
	}
	
	function changeDateAssign($termin_id, $resource_id='') {
		$query = sprintf("SELECT date, content, end_time, assign_id FROM termine LEFT JOIN resources_assign ON (assign_user_id = termin_id) WHERE termin_id = '%s'", $termin_id);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$changeAssign=new AssignObject($this->db->f("assign_id"));
			if ($resource_id)
				$changeAssign->setResourceId($resource_id);
			
			$changeAssign->setBegin($this->db->f("date"));
			$changeAssign->setEnd($this->db->f("end_time"));
			$changeAssign->setRepeatEnd($this->db->f("end_date"));
			$changeAssign->setRepeatQuantity(0);
			$changeAssign->setRepeatInterval(0);
			$changeAssign->setRepeatMonthOfYear(0);
			$changeAssign->setRepeatDayOfMonth(0);
			$changeAssign->setRepeatWeekOfMonth(0);
			$changeAssign->setRepeatDayOfWeek(0);
			//createAssign->checkIsFree ° should performed here			
			$changeAssign->store();
		}
	}
	
	function insertDateAssign($termin_id, $resource_id) {
		$query = sprintf("SELECT date, content, end_time FROM termine WHERE termin_id = '%s'", $termin_id);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$createAssign=new AssignObject(FALSE, $resource_id, $termin_id, '', 
										$this->db->f("date"), $this->db->f("end_time"), $this->db->f("end_time"),
										0, 0, 0, 0, 0, 0, 0, 0);
			//createAssign->checkIsFree ° should performed here
			$createAssign->create();
			$created_ids[] = $createAssign->getId();
		}
	}

	function killDateAssign($termin_id) {
		$query = sprintf ("SELECT assign_id FROM resources_assign LEFT JOIN resources_objects USING (resource_id) LEFT JOIN resources_categories USING (category_id) WHERE assign_user_id = '%s' AND resources_categories.name = 'Raum' ", $termin_id);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$killAssign=new AssignObject($this->db->f("assign_id"));
			//createAssign->checkIsFree ° should performed here
			$killAssign->delete();
		}
	}
	
}
?>