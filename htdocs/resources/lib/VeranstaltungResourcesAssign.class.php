<?
/**
* VeranstaltungResourcesAssign.class.php
* 
* creates a grafical schedule view for different purposes, ie. a personal timetable
* or a timetable for a ressource like a room, a device or a building
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
	
	function deleteAssign($resource_id='') {
		if ($resource_id)
			$query = sprintf("DELETE FROM resources_assign WHERE assign_user_id = '%s' AND resource_id ='%s' ", $this->seminar_id, $resource_id);
		else
			$query = sprintf("DELETE FROM resources_assign WHERE assign_user_id = '%s' ", $this->seminar_id);		
		$this->db->query($query);
		if ($this->db->affected_rows())
			return TRUE;
	}
	
	function updateAssign() {
		$query = sprintf("SELECT DISTINCT resource_id, user_free_name FROM resources_assign WHERE assign_user_id = '%s' ", $this->seminar_id);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$this->changeAssign($this->db->f("resource_id"), $this->db->f("user_free_name"));
		}
	}

	function changeAssign($resource_id, $user_free_name) {
		global $SEMESTER;
		//first, we kill all entries to create new ones
		$this->deleteAssign($resource_id);
		//load dates-type of the Veranstaltung
		$query = sprintf("SELECT start_time, duration_time, metadata_dates FROM seminare WHERE Seminar_id = '%s'", $this->seminar_id);
		$this->db->query($query);
		$this->db->next_record();
		
		$term_data = unserialize ($this->db->f("metadata_dates"));
		
		//regulary dates, create assigns from metadates
		if ($term_data["art"] == 0) {
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
			foreach ($term_data["turnus_data"] as $val) {
				$start_time = mktime ($val["start_stunde"], $val["start_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1), date("Y", $sem_begin));
				$end_time = mktime ($val["end_stunde"], $val["end_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1), date("Y", $sem_begin));
				
				$day_of_week = date("w", $start_time);
				if ($day_of_week == 0)
					$day_of_week = 7;
			
				$createAssign=new AssignObject(FALSE, $resource_id, $this->seminar_id, $user_free_name, 
											$start_time, $end_time, $sem_end,
											-1, $interval, 0, 0, 0, 
											0, $day_of_week, 0);
				$createAssign->create();
				$created_ids[] = $createAssign->getId();
			}			
		//non regulary dates, create assigns from the singel dates
		} else {
			$query = sprintf("SELECT date, end_time FROM termine WHERE range_id = '%s'", $this->seminar_id);
			$this->db->query($query);
			while ($this->db->next_record()) {
				$createAssign=new AssignObject(FALSE, $resource_id, $this->seminar_id, $user_free_name, 
											$this->db->f("date"), $this->db->f("end_time"), $this->db->f("end_time"),
											0, 0, 0, 0, 0, 0, 0, 0);
				$createAssign->create();
				$created_ids[] = $createAssign->getId();
			}
		}
	
	if ($created_ids)
		return $created_ids;
	else
		return FALSE;
	}
}
?>