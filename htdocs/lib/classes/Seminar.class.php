<?
/**
* Seminar.class.php
* 
* the seminar main-class
* 
*
* @author		Stefan Suchi <suchi@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		UserManagement.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Seminar.class.php
// zentrale Veranstaltungsklasse
// Copyright (C) 2004 Cornelis Kater <kater@data-quest>, data-quest GmbH <info@data-quest.de>
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

require_once ($ABSOLUTE_PATH_STUDIP."functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."admission.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Modules.class.php");
require_once ($ABSOLUTE_PATH_STUDIP."/dates.inc.php");


class Seminar {
	var $db;     //unsere Datenbankverbindung
	var $db2;     //unsere Datenbankverbindung
	
	/**
	* Constructor
	*
	* Pass nothing to create a seminar, or the seminar_id from an existing seminar to change or delete
	* @access	public
	* @param	string	$seminar_id	the seminar which should be retrieved
	*/
	function Seminar($id = FALSE) {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		if ($id) {
			$this->id = $id;
			$this->restore();
		}
		if (!$this->id) {
			$this->id=$this->createId();
			$this->is_new = TRUE;
		}
		
	}
	
	/**
	*
	* creates an new id for this object
	* @access	private
	* @return	string	the unique id
	*/
	function createId() {
		return md5(uniqid("SeminarSissi",1));
	}
	
	/**
	* unserialize the term metadata-array
	*
	*/
	function unserializeMetadata() {
		$this->meta_times = array();
		$this->metadata = unserialize($this->serialized_metadata);
		$this->metadate_type = $this->metadata["art"];
		$this->start_date = $this->metadata["start_termin"];
		$this->start_week = $this->metadata["start_woche"];
		$this->cycle = $this->metadata["turnus"]+1;
		
		if (($this->metadate_type == 0) && (is_array($this->metadata["turnus_data"]))) {
			foreach ($this->metadata["turnus_data"] as $val)
				$this->meta_times[] = array(
					"day" => $val["day"],
					"start_hour" => $val["start_stunde"],
					"start_minute" => $val["start_minute"],
					"end_hour" => $val["end_stunde"],
					"end_minute" => $val["end_minute"],
					"room_description" => $val["room"],
					"resource_id" =>$val["resource_id"]
					);
		}
		
	}

	function getId() {
		return $this->id;
	}
	
	function getName() {
		return $this->name;
	}

	function getInstitutId() {
		return $this->institut_id;
	}

	function getSemesterStartTime() {
		return $this->semester_start_time;
	}

	function getSemesterDurationTime() {
		return $this->semester_duration_time;
	}
	
	function getCycle() {
		return $this->cycle;
	}

	function getMetaDateType () {
		return $this->metadata["art"];
	}

	function getSerializedMetadata() {
		$this->serializeMetadata();
		return $this->serializedMetadata;
	}
	
	function getFirstDate() {
		return veranstaltung_beginn ($this->form, $this->semester_start_time, $this->start_week, $this->start_date, $this->metadata["turnus_data"], "int");
	}

	function getFormattedTurnus($short = FALSE) {
		return view_turnus($this->seminar_id, $short, $this->serialized_metadata, $this->start_time);
	}
	
	function getFormattedTurnusDates($short = FALSE) {
		if (is_array($this->meta_times)) {
			foreach ($this->meta_times as $key=>$val) {
				if ($short)
					switch ($val["day"]) {
						case "1": $return_string[$key]= _("Mo."); break;
						case "2": $return_string[$key]= _("Di."); break;
						case "3": $return_string[$key]= _("Mi."); break;
						case "4": $return_string[$key]= _("Do."); break;
						case "5": $return_string[$key]= _("Fr."); break;
						case "6": $return_string[$key]= _("Sa."); break;
						case "7": $return_string[$key]= _("So."); break;
					} 
				else
					switch ($val["day"]) {
						case "1": $return_string[$key]= _("Montag"); break;
						case "2": $return_string[$key]= _("Dienstag"); break;
						case "3": $return_string[$key]= _("Mittwoch"); break;
						case "4": $return_string[$key]= _("Donnerstag"); break;
						case "5": $return_string[$key]= _("Freitag"); break;
						case "6": $return_string[$key]= _("Samstag"); break;
						case "7": $return_string[$key]= _("Sonntag"); break;			
					}
				$return_string[$key].=" ".$val["start_hour"].":";
				if (!$val["start_minute"])
					$return_string[$key].="00";
				elseif (($val["start_minute"] <10) && ($val["start_minute"] >0))
					$return_string[$key].="0".$val["start_minute"];
				else
					$return_string[$key].=$val["start_minute"];
				if (!(($val["end_hour"] == $val["start_hour"]) && ($val["end_minute"] == $val["start_minute"]))) {
					$return_string[$key].=" - ".$val["end_hour"].":";
					if (!$val["end_minute"])
						$return_string[$key].="00";
					elseif (($val["end_minute"] <10) && ($val["end_minute"] >0))
						$return_string[$key].="0".$val["end_minute"];
					else
						$return_string[$key].=$val["end_minute"];
				}
			}
			return $return_string;
		} else
			return FALSE;
	}

	function getMetaDateCount() {
		return sizeof($this->meta_times);
	}

	function getMetaDates() {
		return $this->meta_times;
	}

	function getMetaDateValue($key, $value_name) {
		return $this->meta_times[$key][$value_name];
	}

	function setMetaDateValue($key, $value_name, $value) {
		$this->meta_times[$key][$value_name] = $value;
	}
	
	/**
	* serialize the term metadata-array
	*
	*/
	function serializeMetadata() {
		if ($this->metadata["art"] == -1) 
			$this->serializedMetadata = '';
		else {
			$this->metadata["art"] = $this->metadate_type;
			$this->metadata["start_termin"] = $this->start_date;
			$this->metadata["start_woche"] = $this->start_week;
			$this->metadata["turnus"] = $this->cycle-1;
			if ($this->metadate_type == 0 && is_array($this->meta_times)) {
				$this->metadata["turnus_data"]='';
				foreach ($this->meta_times as $val) {
					$this->metadata["turnus_data"][] = array(
						"idx"=> $val["day"].(($val["start_hour"] <10) ?  "0" : "").$val["start_hour"].(($val["start_minute"]< 10) ?  "0" : "").$val["start_minute"], 
						"day" => $val["day"],
						"start_stunde" => $val["start_hour"],
						"start_minute" => $val["start_minute"],
						"end_stunde" => $val["end_hour"],
						"end_minute" => $val["end_minute"],
						"room" => $val["room_description"],
						"resource_id" =>$val["resource_id"]
						);
					}
			}
			//sort
			if (is_array($this->metadata["turnus_data"])) {
				sort ($this->metadata["turnus_data"]);
			}
			
			//serialize
			$this->serializedMetadata = serialize ($this->metadata);
		}
	}

	/**
	* restore the data
	*
	* the complete data of the object will be loaded from the db
	* @access	publihc
	* @return	booelan	succesful restore?
	*/
	function restore() {
	
		$query = sprintf("SELECT * FROM seminare WHERE Seminar_id='%s' ",$this->id);
		$this->db->query($query);

		if ($this->db->next_record()) {
			$this->seminar_number = $this->db->f("VeranstaltungsNummer");
			$this->institut_id = $this->db->f("Institut_id");
			$this->name = $this->db->f("Name");
			$this->subtitle = $this->db->f("Untertitel");
			$this->status = $this->db->f("status");
			$this->description = $this->db->f("Beschreibung");
			$this->location = $this->db->f("Ort");
			$this->misc = $this->db->f("Sonstiges");
			$this->password = $this->db->f("Passwort");
			$this->read_level = $this->db->f("Lesezugriff");
			$this->write_level = $this->db->f("Schreibzugriff");
			$this->semester_start_time = $this->db->f("start_time");
			$this->semester_duration_time = $this->db->f("duration_time");
			$this->form = $this->db->f("art");
			$this->participants = $this->db->f("teilnehmer");
			$this->requirements = $this->db->f("vorrausetzungen");
			$this->orga = $this->db->f("lernorga");
			$this->leistungsnachweis = $this->db->f("leistungsnachweis");
			$this->serialized_metadata = $this->db->f("metadata_dates");
			$this->unserializeMetadata();			
			$this->mkdate = $this->db->f("mkdate");
			$this->chdate = $this->db->f("chdate");
			$this->ects = $this->db->f("ects");
			$this->admission_endtime = $this->db->f("admission_endtime");
			$this->admission_turnout = $this->db->f("admission_turnout");
			$this->admission_binding = $this->db->f("admission_binding");
			$this->admission_type = $this->db->f("admission_type");
			$this->admission_selection_take_place = $this->db->f("admission_selection_take_place");
			$this->admission_group = $this->db->f("admission_group");
			$this->admission_prelim = $this->db->f("admission_prelim");
			$this->admission_prelim_txt = $this->db->f("admission_prelim_txt");
			$this->admission_starttime = $this->db->f("admission_starttime");
			$this->admission_endtime_sem = $this->db->f("admission_endtime_sem");
			$this->visible = $this->db->f("visible");
			$this->showscore = $this->db->f("showscore");
			$this->modules = $this->db->f("modules");
			return TRUE;
			$this->is_new = false;
		}
		return FALSE;
	}
	
	function store() {
    		//check for securiry cinsistency
		if ($this->read_level < $this->write_level) // hier wusste ein Dozent nicht, was er tat
			$this->write_level = $this->read_level;
		if ($this->is_new) {
			$query = "INSERT INTO seminare SET
				Seminar_id = '".			$this->id."', 
				VeranstaltungsNummer = '".		mysql_escape_string($this->seminar_number)."', 
				Institut_id = '".			$this->institut_id."', 
				Name = '".				mysql_escape_string($this->name)."', 
				Untertitel = '".			mysql_escape_string($this->subtitle)."',
				status = '".				$this->status."', 
				Beschreibung = '".			mysql_escape_string($this->description)."', 
				Ort = '".				mysql_escape_string($this->location)."', 
				Sonstiges = '".				mysql_escape_string($this->misc)."', 
				Passwort= '".				$this->password."', 
				Lesezugriff = '".			$this->read_level."', 
				Schreibzugriff = '".			$this->write_level."', 
				start_time = '".			$this->semester_start_time."', 
				duration_time = '".			$this->semester_duration_time."', 
				art = '".				mysql_escape_string($this->form)."', 
				teilnehmer = '".			mysql_escape_string($this->participants)."', 
				vorrausetzungen = '".			mysql_escape_string($this->requirements)."', 
				lernorga = '".				mysql_escape_string($this->orga)."', 
				leistungsnachweis = '".			mysql_escape_string($this->leistungsnachweis)."', 
				metadata_dates= '".			mysql_escape_string($this->getSerializedMetadata())."', 
				mkdate = '".				time()."', 
				chdate = '".				time()."', 
				ects = '".				mysql_escape_string($this->ects)."', 
				admission_endtime = '".			$this->admission_endtime."', 
				admission_turnout = '".			$this->admission_turnout."', 
				admission_binding = 			NULL , 	
				admission_type = '".			$this->admission_type."', 
				admission_selection_take_place = 	'0', 
				admission_group = 			NULL , 				
				admission_prelim = '".			$this->admission_prelim."', 
				admission_prelim_txt = '".		mysql_escape_string($this->admission_prelim_txt)."', 
				admission_starttime = '".		$this->admission_starttime."',  
				admission_endtime_sem = '".		$this->admission_endtime_sem."', 
				visible =  				'1', 
				showscore =				'0', 
				modules = 				NULL";

			//write the default module-config
			$Modules = new Modules;
			$Modules->writeDefaultStatus($sem_create_data["sem_id"]);
		} else {
			$query = "UPDATE seminare SET
				VeranstaltungsNummer = '".		mysql_escape_string($this->seminar_number)."', 
				Institut_id = '".			$this->institut_id."', 
				Name = '".				mysql_escape_string($this->name)."', 
				Untertitel = '".			mysql_escape_string($this->subtitle)."',
				status = '".				$this->status."', 
				Beschreibung = '".			mysql_escape_string($this->description)."', 
				Ort = '".				mysql_escape_string($this->location)."', 
				Sonstiges = '".				mysql_escape_string($this->misc)."', 
				Passwort= '".				$this->password."', 
				Lesezugriff = '".			$this->read_level."', 
				Schreibzugriff = '".			$this->write_level."', 
				start_time = '".			$this->semester_start_time."', 
				duration_time = '".			$this->semester_duration_time."', 
				art = '".				mysql_escape_string($this->form)."', 
				teilnehmer = '".			mysql_escape_string($this->participants)."', 
				vorrausetzungen = '".			mysql_escape_string($this->requirements)."', 
				lernorga = '".				mysql_escape_string($this->orga)."', 
				leistungsnachweis = '".			mysql_escape_string($this->leistungsnachweis)."', 
				metadata_dates= '".			$this->getSerializedMetadata()."', 
				chdate = '".				time()."', 
				ects = '".				mysql_escape_string($this->ects)."', 
				admission_endtime = '".			$this->admission_endtime."', 
				admission_turnout = '".			$this->admission_turnout."', 
				admission_binding = '".			$this->admission_binding."', 	
				admission_type = '".			$this->admission_type."', 
				admission_selection_take_place ='". 	$this->admission_selection_take_place."', 
				admission_group = '".			$this->admission_group."' , 				
				admission_prelim = '".			$this->admission_prelim."', 
				admission_prelim_txt = '".		mysql_escape_string($this->admission_prelim_txt)."', 
				admission_starttime = '".		$this->admission_starttime."',  
				admission_endtime_sem = '".		$this->admission_endtime_sem."', 
				visible = '". 				$this->visible."', 
				showscore ='".				$this->showscore."', 
				modules = '".				$this->modules."' 
				WHERE Seminar_id = '".			$this->id."'";
		}
		$this->db->query($query);
		
		if ($this->db->affected_rows()) {
			$query = sprintf("UPDATE seminare SET chdate='%s' WHERE Seminar_id='%s' ", time(), $this->id);
			$this->db->query($query);
			return TRUE;
		} else
			return FALSE;
	}
}
