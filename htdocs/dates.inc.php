<?php

/*
dates.inc.php - basale Routinen zur Terminveraltung.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, André Noack <anoack@mcis.de>

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

require_once $ABSOLUTE_PATH_STUDIP."datei.inc.php";  // benötigt zum Löschen von Dokumenten
require_once $ABSOLUTE_PATH_STUDIP."config.inc.php";  //Daten 
require_once $ABSOLUTE_PATH_STUDIP."functions.php";  //Daten 
require_once $ABSOLUTE_PATH_STUDIP."/lib/classes/SemesterData.class.php";  //Daten 
require_once $ABSOLUTE_PATH_STUDIP."/lib/classes/Seminar.class.php";  //Daten 
require_once ($ABSOLUTE_PATH_STUDIP."calendar_functions.inc.php");

/**
* This function creates the assigned room name for range_id
*
* @param		string	the id of the Veranstaltung or date
* @return		string	the name of the room
*
*/

function getRoom ($range_id, $link=TRUE, $start_time = 0, $range_typ = false) {
	global $RESOURCES_ENABLE, $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_RESOURCES, $TERMIN_TYP;
	
	if ($RESOURCES_ENABLE) {
	 	include_once ($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
	 	include_once ($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	 }
	
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	if (!$range_typ){
		$range_typ = get_object_type($range_id);
	}
	switch ($range_typ) {
		case ("sem"):
			$query = sprintf ("SELECT metadata_dates, Ort FROM seminare WHERE Seminar_id = '%s'", $range_id);
			$db->query($query);
			if ($db->next_record()) {
				//get the metatdata array
				$term_data=unserialize($db->f("metadata_dates"));
				$i=0;
				if (is_array($term_data["turnus_data"])) {
					foreach ($term_data["turnus_data"] as $data) {
						if ($i)
							$ret.=", ";
						if (sizeof($term_data["turnus_data"]) > 1)
							switch ($data["day"]) {
								case "1": $ret .=_("Mo.: "); break;
								case "2": $ret .=_("Di.: "); break;
								case "3": $ret .=_("Mi.: "); break;
								case "4": $ret .=_("Do.: "); break;
								case "5": $ret .=_("Fr.: "); break;
								case "6": $ret .=_("Sa.: "); break;
								case "7": $ret .=_("So.: "); break;
							}
						if (($RESOURCES_ENABLE) && ($data["resource_id"])) {
							$resObj =& ResourceObject::Factory($data["resource_id"]);
							if ($resObj->getName())
								if ($link)
									$ret .= $resObj->getFormattedLink();
								else
									$ret .= htmlReady($resObj->getName());
							else
								$ret .= htmlReady($data["room"]);
						}
						elseif ((!$data["room"]) && (sizeof($term_data["turnus_data"]) >1))
							$ret .=_("n. A.");
						else
							$ret .= htmlReady($data["room"]);
						$i++;
					}
					if ($ret)
						return $ret;
					elseif ($db->f("Ort"))
						return htmlReady($db->f("Ort"));
					else
						return _("nicht angegeben");
				} else {
					//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
					$i=0;
					$typ_clause = "(";
					foreach ($TERMIN_TYP as $key=>$val) {
						if ($val["sitzung"]) {
							if ($i)
								$typ_clause .= ", ";
							$typ_clause .= "'".$key."' ";
							$i++;
						}
					}
					$typ_clause .= ")";	
					
					$query = sprintf ("SELECT termin_id, date, raum FROM termine WHERE date_typ IN $typ_clause AND range_id='%s' AND date >= $start_time ORDER BY date", $range_id);
					$db2->query($query);
					$i=0;
					while ($db2->next_record()) {
						$tmp_room='';
						if ($RESOURCES_ENABLE) {
							$assigned_room = getDateAssigenedRoom($db2->f("termin_id"));
							if ($assigned_room) {
								$resObj =& ResourceObject::Factory($assigned_room);
								if ($link)
									$tmp_room .= $resObj->getFormattedLink();
								else
									$tmp_room .= htmlReady($resObj->getName());
							}
						}
						if (($tmp_room) || ($db2->f("raum"))) {
							if ($i)
								$ret .= ", ";
							$i++;
						}
						if ($tmp_room)
							$ret .= date ("d.m", $db2->f("date")).": ".$tmp_room;
						elseif ($db2->f("raum"))
							$ret .= date ("d.m", $db2->f("date")).": ".htmlReady($db2->f("raum"));
					}
					if ($ret)
						return $ret;
					elseif ($db->f("Ort"))
						return htmlReady($db->f("Ort"));
					else
						return _("nicht angegeben");
				}
			} else
				return FALSE;
		break;
		case ("date");
			$query = sprintf ("SELECT termin_id, date, raum FROM termine WHERE termin_id='%s' ", $range_id);
			$db->query($query);
			$db->next_record();

			if ($RESOURCES_ENABLE) {
				$assigned_room = getDateAssigenedRoom($range_id);
				if ($assigned_room) {
					$resObj =& ResourceObject::Factory($assigned_room);
					if ($link)
						$tmp_room .= $resObj->getFormattedLink($db->f("date"));
					else
						$tmp_room .= htmlReady($resObj->getName());	
				}
			}
			if ($tmp_room)
				$ret .= $tmp_room;
			else
				$ret .= htmlReady($db->f("raum"));
			return $ret;
		break;
	}
}


/*
Die Funktion veranstaltung_beginn errechnet den ersten Seminartermin aus dem Turnus Daten.
Zurueckgegeben wird ein String oder Timestamp. je nach return_mode (TRUE = Timestamp)
Evtl. Ergaenzungen werden im Stringmodus mit ausgegeben.
Die Funktion kann mit einer Seminar_id aufgerufen werden, dann werden saemtliche gespeicherten Daten 
beruecksichtigt. Im 'ad hoc' Modus koennen der Funktion auch die eizelnen Variabeln des Metadaten-Arrays
uebergeben werden. Dann werden konkrete Termine nur mit berruecksichtigt, sofern sie schon angelegt wurden.
*/

function veranstaltung_beginn ($seminar_id='', $art='', $semester_start_time='', $start_woche='', $start_termin='', $turnus_data='', $return_mode='') {
	global $TERMIN_TYP;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;	
	$semester = new SemesterData;
	$holiday = new HolidayData;
	$all_semester = $semester->getAllSemesterData();
	
	if ((func_num_args()==1) || (func_num_args()==2)){
		$seminar_id=func_get_arg(0);
		if (func_num_args()==2)
			$return_mode=func_get_arg(1);
		$db->query("SELECT metadata_dates, start_time, duration_time FROM seminare WHERE seminar_id='$seminar_id'");
		$db->next_record();
		$term_data=unserialize($db->f("metadata_dates"));
		$term_data["start_time"]=$db->f("start_time");
	} else {
		$term_data["art"]=func_get_arg(0);
		$term_data["start_time"]=func_get_arg(1);
		$term_data["start_woche"]=func_get_arg(2);
		$term_data["start_termin"]=func_get_arg(3);
		$term_data["turnus_data"]=func_get_arg(4);
		if (func_num_args()==6)
			$return_mode=func_get_arg(1);
	}
	//Regelmaessige Termine. also Turnus aus Metadaten
	if ($term_data["art"]==0) {
		if (($term_data["start_woche"] ==0) || ($term_data["start_woche"] ==1)) // Startzeitpunkt 1. oder 2. Semesterwoche
			if (sizeof($term_data["turnus_data"])) {
				//first, determine the correct the start for $vorles_bginn
				foreach ($all_semester as $sem)
					if (($term_data["start_time"] >= $sem["beginn"]) AND ($term_data["start_time"] <= $sem["ende"]))

				$vorles_beginn=$sem["vorles_beginn"];
					
				//correct the vorles_beginn to match monday, if necessary
				$dow = date("w", $vorles_beginn);

				if ($dow <= 5)
					$corr = ($dow -1) * -1;
				elseif ($dow == 6)
					$corr = 2;
				elseif ($dow == 0)
					$corr = 1;
				else
					$corr = 0;

				if ($corr) {
					$vorles_beginn_uncorrected = $vorles_beginn;
					$vorles_beginn = mktime(date("G",$vorles_beginn), date("i",$vorles_beginn), 0, date("n",$vorles_beginn), date("j",$vorles_beginn)+$corr,  date("Y",$vorles_beginn));
				}
				
				//now create possible start dates and do checks for holidays or calculatable off-days			
				$cycle=0;				
				do {
					foreach ($term_data["turnus_data"] as $turnus_arr) {
						$date_ok = TRUE;

						$start_termin=$vorles_beginn+(($turnus_arr["day"]-1)*24*60*60)+($turnus_arr["start_stunde"]*60*60)+($turnus_arr["start_minute"]*60) + (($term_data["start_woche"] + $cycle) * 7 * 24 * 60 *60);
						$end_termin=$vorles_beginn+(($turnus_arr["day"]-1)*24*60*60)+($turnus_arr["end_stunde"]*60*60)+($turnus_arr["end_minute"]*60) + (($term_data["start_woche"] + $cycle) * 7 * 24 * 60 *60);
		
						//correct the start_termin if the start_termin is ealier than $vorles_beginn_uncorrected
						$corr = 0;
						if ($vorles_beginn_uncorrected)
							if ($start_termin < $vorles_beginn_uncorrected) {
								if ($term_data["turnus"])
									$corr = $corr + 7;
							}
		
						if ($corr) {
							$start_termin = mktime(date("G",$start_termin), date("i",$start_termin), 0, date("n",$start_termin), date("j",$start_termin)+$corr,  date("Y",$start_termin));
							$end_termin = mktime(date("G",$end_termin), date("i",$end_termin), 0, date("n",$end_termin), date("j",$end_termin)+$corr,  date("Y",$end_termin));
						}
						
						//and, correct the start_termin, if de dayligt saving time plays a trick with us
						if (date("G", $start_termin) != $term_data["turnus_data"][0]["start_stunde"])
							$start_termin = mktime($term_data["turnus_data"][0]["start_stunde"], date("i",$start_termin), 0, date("n",$start_termin), date("j",$start_termin),  date("Y",$start_termin));
						if (date("G", $end_termin) != $term_data["turnus_data"][0]["end_stunde"])
							$end_termin = mktime($term_data["turnus_data"][0]["end_stunde"], date("i",$end_termin), 0, date("n",$end_termin), date("j",$end_termin),  date("Y",$end_termin));
						
						//check for holidays. You should use it only for special holidays
						$all_holiday = $holiday->getAllHolidays(); // fetch all Holidays
						// get all holidays from db
						foreach ($all_holiday as $val)
							if (($val["beginn"] <= $start_termin) && ($start_termin <=$val["ende"]))
								$date_ok = FALSE;	
						
						//check for calculatable holidays
						$holy_type = holiday($start_termin);
						if ($holy_type["col"] == 3) {
							$date_ok = FALSE;
						}
						
						//cancel running the foreach-loop, if one of my matadates (not the last) already is fine
						if ($date_ok)
							break;
					}
					$cycle ++;
				} while ((!$date_ok) || ($cycle>50));
				
				$return_string=date ("d.m.Y, G:i", $start_termin);
				$return_int=$start_termin;
				if ($start_termin != $end_termin) 
					$return_string.=" - ".date ("G:i", $end_termin);
				}
			else {
				$return_string=_("nicht angegeben");
				$return_int=-1; 
			}
		//anderer Startzeitpunkt gewaehlt
		else {
			//no $start_termin given
			if ($term_data["start_termin"]<1) {
				$return_string.= _("nicht angegeben");
				$return_int=-1;
			//$start_termin given, this is the first date
			} else {
				$return_string.=date ("d.m.Y", $term_data["start_termin"]);
				$return_int=$term_data["start_termin"];
			}
			//just an algorhytmus to fit the given start_termin (only a date without hour an minute!) to given meta_dates, if they are on the same day
			if (is_array($term_data["turnus_data"]))
				foreach ($term_data["turnus_data"] as $val) {
					$dow = $val["day"];
					if ($dow == 7)
						$dow=0;
					if ($dow == date("w", $term_data["start_termin"])) {
						if ($val["start_stunde"]) {
							$return_string.=", ". $val["start_stunde"]. ":"; 
							if (($val["start_minute"] > 0)  &&  ($val["start_minute"] < 10))
								$return_string.="0". $val["start_minute"];
							elseif ($val["start_minute"] > 10)
								$return_string.=$val["start_minute"];
							if (!$val["start_minute"])
								$return_string.="00";
							if (!(($val["end_stunde"] != $val["start_stunde"]) && ($val["end_minute"] !=$val["start_minute"]))) {
								$return_string.= " - ". $val["end_stunde"]. ":";
								if (($val["end_minute"] > 0)  &&  ($val["end_minute"] < 10))
									$return_string.="0".$val["end_minute"];
								elseif ($val["end_minute"] > 10)
									$return_string.=$val["end_minute"];
								if (!$val["end_minute"])
									$return_string.="00";
								}	
							}
						break;
						}
					}
				}
		}
	//Unregelmaessige Termine, also konkrete Termine aus Termintabelle
	else {
	
		//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
		$i=0;
		$typ_clause = "(";
		foreach ($TERMIN_TYP as $key=>$val) {
			if ($val["sitzung"]) {
				if ($i)
					$typ_clause .= ", ";
				$typ_clause .= "'".$key."' ";
				$i++;
			}
		}
		$typ_clause .= ")";	
		
		$db2->query("SELECT date, end_time FROM termine WHERE date_typ IN $typ_clause AND range_id='$seminar_id' ORDER BY date");
		$db2->next_record();
		if ($db->affected_rows()) {
			$return_string=date ("d.m.Y, G:i", $db2->f("date"))." - ".date ("G:i",  $db2->f("end_time"));
			$return_int=$db2->f("date");
		} else {
			$return_string.= _("nicht angegeben");
			$return_int=-1;
		
		}
	}

	if ($return_mode)
		return $return_int;	
	else
		return $return_string;	
	}

/*
Die Funktion view_turnus zeigt in einer kompakten Ansicht den Turnus eines Seminars an.
Angezeigt werden bei unregelmaessigen Veranstaltungen gruppierte Termine,
wenn mehrere gleiche Termine an aufeinanderfolgenden Tagen liegen.
Der Parameter short verkuerzt die Ansicht nochmals (fuer besonders platzsparende Ausgabe).
Bei regelmaessigen Veranstaltungen werden die einzelen Zeiten ausgegeben, bei zweiwoechentlichem
Turnus mit dem enstprechenden Zusatz. Short verkuerzt die Ansicht nochmals.
*/

function view_turnus ($seminar_id, $short = FALSE, $meta_data = false, $start_time = false) {
	
	static $turnus_cache;
	
	global $TERMIN_TYP;
	
	if ($turnus_cache[$seminar_id][$short]){
		return $turnus_cache[$seminar_id][$short];
	}
	
	if (!$start_time){
		$start_time = 0;
	}
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	
	if ($meta_data === false){
		$db->query("SELECT metadata_dates FROM seminare WHERE Seminar_id = '$seminar_id'");
		$db->next_record();
		$term_data=unserialize($db->f("metadata_dates"));
	} else {
		$term_data = unserialize($meta_data);
	}
	
	if ($term_data["art"] == 1)
		{
		//Load all TERMIN_TYPs that are "Sitzungstermine" and build query-clause
		$i=0;
		$typ_clause = "(";
		foreach ($TERMIN_TYP as $key=>$val) {
			if ($val["sitzung"]) {
				if ($i)
					$typ_clause .= ", ";
				$typ_clause .= "'".$key."' ";
				$i++;
			}
		}
		$typ_clause .= ")";
		
		$db2->query("SELECT * FROM termine WHERE range_id='$seminar_id' AND date_typ IN $typ_clause AND date >= $start_time ORDER BY date");
		if ($db2->affected_rows() == 0)
			{
			if ($short)
				$return_string= _("Termin: n. A.");
			elseif (!$start_time)
				$return_string= _("unregelmäßige Veranstaltung oder Blockveranstaltung. Die Termine stehen nicht fest.") . " ";
			else
				$return_string= _("unregelmäßige Veranstaltung oder Blockveranstaltung. Keine aktuellen oder zukünftigen Termine.") . " ";
			}
		else
			if ($short)
				$return_string= _("Termine am") . " ";
			else
				$return_string= _("unregelmäßige Veranstaltung oder Blockveranstaltung am") . " ";

			while ($db2->next_record())
				$dates[]=array("start_time"=>$db2->f("date"), "end_time"=>$db2->f("end_time"), "conjuncted"=>FALSE, "time_match"=>FALSE);
			
			for ($i=1; $i<sizeof($dates); $i++)
				{
				if (((date("G", $dates[$i-1]["start_time"])) == date("G", $dates[$i]["start_time"])) && ((date("i", $dates[$i-1]["start_time"])) == date("i", $dates[$i]["start_time"])) && ((date("G", $dates[$i-1]["end_time"])) == date("G", $dates[$i]["end_time"])) && ((date("i", $dates[$i-1]["end_time"])) == date("i", $dates[$i]["end_time"])))
					$dates[$i]["time_match"]=TRUE;
					
				if (((date ("z", $dates[$i]["start_time"])-1) == date ("z", $dates[$i-1]["start_time"])) || ((date ("z", $dates[$i]["start_time"]) == 0) && (date ("j", $dates[$i-1]["start_time"]) == 0)))
					if ($dates[$i]["time_match"])
						$dates[$i]["conjuncted"]=TRUE;
				}
			
			for ($i=0; $i<sizeof($dates); $i++)
				{
				if (!$dates[$i]["conjuncted"])
					$conjuncted=FALSE;				
					
				if ((!$dates[$i]["conjuncted"]) || (!$dates[$i+1]["conjuncted"]))
					$return_string.=date (" j.n.", $dates[$i]["start_time"]);
				
				if ((!$conjuncted) && ($dates[$i+1]["conjuncted"]))
					{
					$return_string.=" -";	
					$conjuncted=TRUE;
					}
				elseif ((!$dates[$i+1]["conjuncted"]) && ($dates[$i+1]["time_match"]))
					$return_string.=",";
					
				if (!$dates[$i+1]["time_match"])
					{
					$return_string.=" ".date("G:i", $dates[$i]["start_time"]);
					if (date("G:i", $dates[$i]["start_time"]) != date("G:i", $dates[$i]["end_time"])) 
						$return_string.=" - ".date("G:i", $dates[$i]["end_time"]);
					if ($i+1 != sizeof ($dates))
						$return_string.=",";
					}
				}
		}
	else
		{
		if ($short)
			if (sizeof($term_data["turnus_data"])) {
				$k=0;
				foreach ($term_data["turnus_data"] as $data)
					{
					if ($k) 
						$return_string.=", ";
					$k++;
					switch ($data["day"])
						{
						case "1": $return_string.= _("Mo."); break;
						case "2": $return_string.= _("Di."); break;
						case "3": $return_string.= _("Mi."); break;
						case "4": $return_string.= _("Do."); break;
						case "5": $return_string.= _("Fr."); break;
						case "6": $return_string.= _("Sa."); break;
						case "7": $return_string.= _("So."); break;
						}
					$return_string.=" ".$data["start_stunde"].":";
					if (!$data["start_minute"])
						$return_string.="00";
					elseif (($data["start_minute"] <10) && ($data["start_minute"] >0))
						$return_string.="0".$data["start_minute"];
					else
						$return_string.=$data["start_minute"];
					if (!(($data["end_stunde"] == $data["start_stunde"]) && ($data["end_minute"] == $data["start_minute"])))
						{
						$return_string.=" - ".$data["end_stunde"].":";
						if (!$data["end_minute"])
							$return_string.="00";
						elseif (($data["end_minute"] <10) && ($data["end_minute"] >0))
							$return_string.="0".$data["end_minute"];
						else
							$return_string.=$data["end_minute"];
						}
					else
						$return_string.=" ";
					}
				}
			else {
				$return_string= _("Zeiten: n. A.");
				}
		else
			if (sizeof($term_data["turnus_data"])) {
				$k=0;
				foreach ($term_data["turnus_data"] as $data)
					{
					if ($k) 
						$return_string.=", ";
					$k++;
					switch ($data["day"])
						{
						case "1": $return_string.= _("Montag"); break;
						case "2": $return_string.= _("Dienstag"); break;
						case "3": $return_string.= _("Mittwoch"); break;
						case "4": $return_string.= _("Donnerstag"); break;
						case "5": $return_string.= _("Freitag"); break;
						case "6": $return_string.= _("Samstag"); break;
						case "7": $return_string.= _("Sonntag"); break;
						}
					$return_string.=" ".$data["start_stunde"].":";
					if (!$data["start_minute"])
						$return_string.="00";
					elseif (($data["start_minute"] <10) && ($data["start_minute"] >0))
						$return_string.="0".$data["start_minute"];
					else
						$return_string.=$data["start_minute"];
					if (!(($data["end_stunde"] == $data["start_stunde"]) && ($data["end_minute"] == $data["start_minute"])))
						{
						$return_string.=" - ".$data["end_stunde"].":";
						if (!$data["end_minute"])
							$return_string.="00";
						elseif (($data["end_minute"] <10) && ($data["end_minute"] >0))
							$return_string.="0".$data["end_minute"];
						else
							$return_string.=$data["end_minute"];
						}
					else
						$return_string.=" ";
					}
				}
			else {
				$return_string= _("Die Zeiten der Veranstaltung stehen nicht fest.");
				}			
			if ($term_data["turnus"] == 1)
				$return_string.= " " . _("(zweiwöchentlich)");
		$turnus_cache[$seminar_id][$short] = $return_string;
		}
	return $return_string;
	}


/*
Die Funktion Vorbesprechung ueberpueft, ob es eine Vorbesprechung gibt und gibt in diesem
Falle den entsprechenden Timestamp zurueck. Ansonsten wird FALSE zurueckgegeben.
*/

function vorbesprechung ($seminar_id)
	{
	$db=new DB_Seminar;
	$db->query("SELECT * FROM termine WHERE range_id='$seminar_id' AND date_typ='2' ORDER by date");
	if ($db->next_record())
		$return_string=date ("d.m.Y, G:i", $db->f("date"))." - ".date ("G:i", $db->f("end_time"));
	if ($db->f("raum"))
		$return_string .= ", " . sprintf(_("Ort: %s"), $db->f("raum"));
	return $return_string;		
	}


/*
Die Funktion get_sem_name gibt den Namen eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/

function get_sem_name ($time) {
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	foreach ($all_semester as $key=>$val)
		if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
			return $val["name"];

}

/*
Die Funktion get_sem_num gibt die Nummer eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/

function get_sem_num ($time) {
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	foreach ($all_semester as $key=>$val)
		if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
			return $key;

}

function get_sem_num_sem_browse () {
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	$time = time();
	$ret = false;
	foreach ($all_semester as $key=>$val){
		if ($ret && ($val["vorles_ende"] >= $time)){
			$ret = $key;
			break;
		}
		if ($time >= $val["vorles_ende"]){
			$ret = true;
		}
	}
	return $ret;
}

/*
Die Funktion get_semester gibt den oder die Semester einer speziellen Veranstaltung aus.
*/

function get_semester($seminar_id, $start_sem_only=FALSE)
	{
	$db=new DB_Seminar;
	$db->query("SELECT metadata_dates, start_time, duration_time FROM seminare WHERE seminar_id='$seminar_id'");
	$db->next_record();
	
	$return_string=get_sem_name($db->f("start_time"));
	if (!$start_sem_only) {
		if ($db->f("duration_time")>0)
			$return_string.=" - ".get_sem_name($db->f("start_time") + $db->f("duration_time"));
		if ($db->f("duration_time")==-1)				
			$return_string.= " " . _("bis unbegrenzt");
		}
	return $return_string;		
	}


function getCorrectedSemesterVorlesBegin ($semester_num) {
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	
	$vorles_beginn=$all_semester[$semester_num]["vorles_beginn"];

	//correct the vorles_beginn to match monday, if necessary
	$dow = date("w", $vorles_beginn);

	if ($dow <= 5)
		$corr = ($dow -1) * -1;
	elseif ($dow == 6)
		$corr = 2;
	elseif ($dow == 0)
		$corr = 1;
	else
		$corr = 0;

	if ($corr) {
		$vorles_beginn_uncorrected = $vorles_beginn;
		$vorles_beginn = mktime(date("G",$vorles_beginn), date("i",$vorles_beginn), 0, date("n",$vorles_beginn), date("j",$vorles_beginn)+$corr,  date("Y",$vorles_beginn));
	}
	
	return $vorles_beginn;
}			


/*
Die Funktion edit_dates veraendert den zu der Uebergebenen termin_id passenden Termin.
Dazu wird die Beschreibung des Ordners angepasst, falls es einen gibt.
Dabei werden die Beschriftungen der Ordner im Forensystem und im Dateisystem aktualisiert.
*/


function edit_dates($stunde,$minute,$monat,$tag,$jahr,$end_stunde, $end_minute, $termin_id,$art,$titel,$description,$topic_id,$raum,$resource_id,$range_id,$save_changes_with_request = FALSE) {
	global $user,$auth, $TERMIN_TYP, $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES, $PHP_SELF;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$semester = new SemesterData;
	$semObj = new Seminar($range_id);

	if ($RESOURCES_ENABLE) {
		include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
		include_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
		include_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	}
	
	$do=TRUE;
	if (!checkdate($monat,$tag,$jahr)) {
		$do=FALSE;
		$result= "error§" . _("Bitte geben Sie ein g&uuml;ltiges Datum ein!"). "§";
	}

	if ($do)		
		if ((!$stunde) && (!end_stunde)) {
			$do=FALSE;	
			$result .= "error§" . _("Bitte geben Sie eine g&uuml;ltige Start- und Endzeit an!"). "§";
		}
	
	$start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
	$end_time = mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr);
	
	if ($do)		
		if ($start_time > $end_time) {
			$do=FALSE;	
			$result .= "error§" . _("Der Endzeitpunkt muss nach dem Startzeitpunkt liegen!"). "§";
		}
		
	//check, if a single date should be created when it is forbidden (no single dates corresponding to metadates are allowed when using resources, only a whole schedule creating with date-assi is fine...!)
	if ($GLOBALS["RESOURCES_ENABLE"]) {
		if ((isMetadateCorrespondingDate($termin_id, $start_time, $end_time, $range_id)) && (!$semObj->getMetaDateType()) && (!isSchedule($range_id))) {
			$do = FALSE;
				if ($TERMIN_TYP[$art]["sitzung"])
					$add_result .= "info§" . sprintf(_("Sie wollen einen oder mehrere Sitzungstermine auf die regelm&auml;&szlig;igen Veranstaltungszeiten &auml;ndern. Bitte verwenden Sie f&uuml;r diese Termine den Ablaufplanassistenten, um die entsprechenden Termine f&uuml;r den gesamten Veranstaltungszeitraum anzulegen.")) . "§";
				else
					$add_result .= "info§" . sprintf(_("Sie wollen einen oder mehrere Sondertermine auf die regelm&auml;&szlig;igen Veranstaltungszeiten &auml;ndern. Bitte verwenden Sie f&uuml;r diese Termine den Ablaufplanassistenten und &auml;ndern dann die Terminart f&uuml;r den gew&uuml;nschten Termin.")). "§";

		} elseif ($GLOBALS["RESOURCES_ALLOW_ROOM_REQUESTS"]) {
			if (($resource_id) && ($resource_id != "FALSE") && ($resource_id != "NULL")){
				$check_resource_id = $resource_id;
			} elseif ($resource_id == "NULL") {
				$check_resource_id = getDateAssigenedRoom($termin_id);
			} elseif ($resource_id == "FALSE") {
				$check_resource_id = FALSE;
				$resource_id = FALSE;
			}
						
			if ($check_resource_id) {	
				$resObjPrm =& ResourceObjectPerms::Factory($check_resource_id);
				if (!$resObjPrm->havePerm("autor")) {
					//load the saved state to check for changes to date and time
					$query = sprintf("SELECT date, end_time FROM termine WHERE termin_id = '%s' ", $termin_id);
					$db->query($query);
					if ($db->next_record()) {
						if (($db->f("date") != $start_time) || ($db->f("end_time") != $end_time)) {
							if ($save_changes_with_request) {
								$create_update_request = TRUE;
							} else {
								$do = FALSE;
								$add_result .= "info§" . sprintf(_("Sie wollen die Zeiten eines oder mehrerer Termine &auml;ndern, f&uuml;r die bereits ein Raum durch den Raumadministrator zugewiesen wurde. Wenn Sie die Zeiten dieser Termine &auml;ndern, verlieren Sie diese Buchung und es mu&szlig; eine neue Anfrage an den Raumadministrator gestellt werden. <br /> Wollen Sie diese Termin dennoch &auml;ndern und daf&uuml;r jeweils neue Anfragen erstellen?"));
								$add_result .= "<br /><a href=\"$PHP_SELF?save_changes_with_request=1\">".makeButton("ja2")."</a>&nbsp;<a href=\"$PHP_SELF?reset_edit=1\">".makeButton("nein")."</a>§";
							}
						}
					}
				}
			}
		}
	}		
	
	//create a request or reopen a given one
	if ($create_update_request) {
		if ($request_id = getDateRoomRequest($termin_id)) {
			$reqObj = new RoomRequest ($request_id);
			$reqObj->setClosed(0);
			if (!$reqObj->getResourceId())
				$reqObj->setResourceId($resObjPrm->getId());
			$reqObj->store();
			$create_req = TRUE;
		} elseif ($request_id = getSeminarRoomRequest($range_id)) {
			$reqObj = new RoomRequest ($request_id);
			$reqObj->copy();
			$reqObj->setTerminId($termin_id);
			if (!$reqObj->getResourceId())
				$reqObj->setResourceId($resObjPrm->getId());
			$reqObj->store();
			$create_req = TRUE;
		} else {
			$add_result .= "info§" . sprintf(_("Sie haben die Zeiten eines oder mehrerer Termine ge&auml;ndert und damit die Raumbuchung verloren. Bitte stellen Sie f&uuml;r diese Termine Raumanfragen, um einen Raum durch den Raumadministrator zugewiesen zu bekommen."). "§");
		}
		if ($create_req)
			$add_result .= "info§" . sprintf(_("Sie haben die Zeiten eines oder mehrerer Termine ge&auml;ndert und damit die Raumbuchung verloren. Eine entsprechende Raumanfrage wurde erstellt. Sie k&ouml;nnen diese Raumanfrage jederzeit bearbeiten, in dem Sie auf \"Raumanfrage bearbeiten\" klicken.")."§");
	}
				
	//Check auf Konsistenz mit Metadaten, Semestercheck bei allen Sitzungsterminen
	$all_semester = $semester->getAllSemesterData();
	if (($do) && ($TERMIN_TYP[$art]["sitzung"]==1) && (is_array($term_data ["turnus_data"]))) {
		foreach ($all_semester as $a) {
			if (($term_data["start_time"] >= $a["beginn"]) && ($term_data["start_time"] <= $a["ende"]))  {
				$sem_beginn=$a["beginn"];
				$sem_ende=$a["ende"];
			}
			if (($term_data["duration_time"] > 0) && ((($term_data["start_time"] + $term_data["duration_time"]) >= $a["beginn"]) && (($term_data["start_time"] + $term_data["duration_time"]) < $a["ende"])))
				$sem_ende=$a["ende"];
			}
			
		if (($start_time < $sem_beginn) || ($start_time > $sem_ende))
			$add_result .= "info§" . _("Sie haben einen oder mehrere Termine eingegeben, die ausserhalb des Semesters liegen, in dem die Veranstaltung stattfindet. Es wird empfohlen, diese Termine anzupassen.") . "§";
		
		//Und dann noch auf regelmaessige Termine checken, wenn dieser Typ gewaehlt ist
		if ((!$term_data["art"]) && (!isMetadateCorrespondingDate($termin_id, $start_time, $end_time, $range_id))) {
			$add_result .= "info§" . _("Sie haben einen oder mehrere Termine eingegeben, der nicht zu den allgemeinen Veranstaltungszeiten stattfindet. Es wird empfohlen, Sitzungstermine von regelm&auml;&szlig;igen Veranstaltungen nur zu den allgemeinen Zeiten stattfinden zu lassen.") . "§";
		}
	}
	
	if ($do) {
		$author = get_fullname();

		$titel=$titel;
		$description=$description; 

		//if we have a resource_id, we take the room name from resource_id
		if (($resource_id) && ($resource_id != "NULL") && ($resource_id != "FALSE"))
			$raum=getResourceObjectName($resource_id);
		
		$db->query("UPDATE  termine SET autor_id='$user->id', content='$titel', date= '$start_time', end_time='$end_time', date_typ='$art', raum='$raum', description='$description'  WHERE termin_id='$termin_id'");
		if ($db->affected_rows()) {
			$db->query ("UPDATE termine SET chdate='".time()."' WHERE termin_id='$termin_id'"); //Nur wenn Daten geaendert wurden, schreiben wir auch ein chdate
			$result.= sprintf ("msg§" ._("Der Termin <b>%s</b> wurde ge&auml;ndert!")."§", htmlReady($titel));
			$date_changed = TRUE;
		}
			
		//Workaround fuer Forenbug. Dies ist keine Loesung, sondern Schadensvermeidung!!
		$db3->query("SELECT Seminar_id FROM px_topics WHERE topic_id ='$topic_id'");
		$db3->next_record();
			
		$db4->query("SELECT range_id FROM termine WHERE termin_id ='$termin_id'");
		$db4->next_record();
			
		if ($db3->f("Seminar_id") == $db4->f("range_id")) {
		//WA Ende
			
			if ($topic_id) 
				$db->query("UPDATE px_topics SET name='".$TERMIN_TYP[$art]["name"].": $titel am ".date("d.m.Y ", $start_time)."', author='$author', user_id='".$user->id."' WHERE topic_id='$topic_id'");		
			if ($db->affected_rows())
				$db2->query("UPDATE px_topics SET chdate='".time()."' WHERE topic_id='$topic_id'");
			
		//WA Teil zwei, wo gefunden, so korrigieren
		} else {
			if ($topic_id) 
				$db->query("UPDATE termine SET topic_id=''  WHERE termin_id='$termin_id'");		
			}
		//WA Ende

		//Aendern des Titels des zugehoerigen Ordners
		$titel_f=$TERMIN_TYP[$art]["name"].": $titel";
		$titel_f .= " " . _("am") . " " . date("d.m.Y ", $start_time);
		
		$db->query("SELECT folder_id FROM folder WHERE range_id ='$termin_id'");
		if ($db->num_rows() == 1) {
			$db->next_record();
			$db2->query ("UPDATE folder SET name='$titel_f' WHERE folder_id = '".$db->f("folder_id")."'");
			if ($db2->affected_rows())
				$db3->query("UPDATE folder SET chdate='".time()."' WHERE folder_id = '".$db->f("folder_id")."'");
		}

		//update assigned resources, if resource manangement activ
		if ($RESOURCES_ENABLE) {
			$updateAssign = new VeranstaltungResourcesAssign($range_id);
			if (($resource_id) && ($resource_id != "NULL"))
				$resources_result=$updateAssign->changeDateAssign($termin_id, $resource_id);
			if (($create_update_request) || (!$resource_id))
				$resources_result=$updateAssign->killDateAssign($termin_id);
		}
		
	} else
		$result.= sprintf ("error§" ._("Der Termin <b>%s</b> wurde <u>nicht</u> ge&auml;ndert!")."§", htmlReady($titel));

	$result_a["changed"]=$date_changed;
	$result_a["msg"]=$result;
	$result_a["add_msg"]=$add_result;
	$result_a["resources_result"]=$resources_result;

	return ($result_a);
}

/*
Die Funktion delete_topic löscht rekursiv alle Postings ab der übergebenen topic_id, der zweite Parameter
muss(!) eine Variable sein, diese wird für jedes gelöschte Posting um eins erhöht
*/

function delete_topic($topic_id, &$deleted)  //rekursives löschen von topics VORSICHT!
{

	$db=new DB_Seminar;
	$db->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
	if ($db->num_rows()) {
		while ($db->next_record()) {
			$next_topic=$db->f("topic_id");
			delete_topic($next_topic,$deleted);
		}
	}
 	$db->query("DELETE FROM px_topics WHERE topic_id='$topic_id'");
 	$deleted++;
	
	// gehoerte dieses Posting zu einem Termin?
	// dann Verknuepfung loesen...
	$db->query("UPDATE termine SET topic_id = '' WHERE topic_id = '$topic_id'");
	
 	return;
}

/*
Die function delete_date löscht einen Termin und verschiebt daran haegende
Ordner in den allgemeinen Ordner.
Der erste Parameter ist die termin_id des zu löschenden Termins.
Der zweite Parameter topic_id gibt an, ob auch die zu diesem Termin gehoerenden
Postings im Forensystem geloescht werden sollen.
0 bzw. FALSE : keine Topics loeschen
> 0 : rekursives loeschen von topic_id
Der dritte Parameter gibt analog an, ob auch die zu diesem Terminen gehoerenden
Folder im Ordnersystem geloescht werden sollen.
Der Rückgabewert der Funktion ist die Anzahl der insgesamt gelöschten Items.
-1 bedeutet einen Fehler beim Loeschen des Termins.
Ausgabe wird keine produziert.
Es erfolgt keine Überprüfung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_date ($termin_id, $topic_delete = TRUE, $folder_move = TRUE, $sem_id=0) {
	global $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES;
	
	if ($RESOURCES_ENABLE) {
		include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	}
	
	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;	

	## Eventuell rekursiv Postings loeschen
	if ($topic_delete) {
		$db->query("SELECT topic_id FROM termine WHERE termin_id ='$termin_id'");
		if ($db->next_record()) {
			delete_topic($db->f("topic_id"),$count);
		}
	}
	
	if (!$folder_move) {
		## Dateiordner muessen weg!
		recursiv_folder_delete ($termin_id);
		}
	else {
		## Dateiordner werden verschoben, wenn Ordner nicht leer, ansonsten auch weg
		if (!doc_count($termin_id, 0))
			recursiv_folder_delete ($termin_id);		
		else {
			$db->query("SELECT folder_id FROM folder WHERE range_id ='$sem_id'");
			$db->next_record();
			$db2->query("SELECT folder_id FROM folder WHERE range_id = '$termin_id'");
			while ($db2->next_record()) {
				move_item ($db2->f("folder_id"), $db->f("folder_id"));
				$db3->query ("UPDATE folder SET name='" . _("Dateiordner zu gelöschtem Termin") . "', description='" . _("Dieser Ordner enthält Dokumente und Termine eines gelöschten Termins") . "' WHERE folder_id='".$db2->f("folder_id")."'");
				}
			}
		}

	## Und den Termin selbst loeschen
	$query = "DELETE FROM termine WHERE termin_id='$termin_id'";
	$db->query($query);
	if ($db->affected_rows() && $RESOURCES_ENABLE) {
		$insertAssign = new VeranstaltungResourcesAssign($sem_id);
		$insertAssign->killDateAssign($termin_id);
	}
}

/*
Die function delete_range_of_dates löscht Termine mit allen daran haengenden Items.
Der erste Parameter ist die range_id der zu löschenden Termine.
Es koennen also mit einem Aufruf alle Termine eines Seminares,
eines Institutes oder persoenliche Termine eines Benutzers aus der Datenbank entfernt werden.
Dokumente und Literatur an diesen Terminen werden auf jeden Fall gelöscht.
Der zweite Parameter topics gibt an, ob auch die zu diesen Terminen gehoerenden
Postings im Forensystem geloescht werden sollen.
0 bzw. FALSE : keine Topics loeschen
1 bzw. TURE : rekursives Loeschen der Postings
Der Rückgabewert der Funktion ist die Anzahl der gelöschten Termine.
Ausgabe wird keine produziert.
Es erfolgt keine Überprüfung der Berechtigung innerhalb der Funktion,
dies muss das aufrufende Script sicherstellen.
*/

function delete_range_of_dates ($range_id, $topics = FALSE) {

	$db = new DB_Seminar;
	$count = 0;

	## Termine finden...
	$query = "SELECT termin_id, topic_id FROM termine WHERE range_id='$range_id'";
	$db->query($query);
	while ($db->next_record()) {       // ...und nacheinander...
		if ($topics)
			delete_date($db->f("termin_id"), $db->f("topic_id"));  // ...mit topics loeschen
		else
			delete_date($db->f("termin_id"), FALSE);  // ...ohne topics loeschen
		$count ++;
	}

	return $count;
}


//Erstellt automatisch einen Ablaufplan oder aktualisiert ihn
function dateAssi ($sem_id, $mode="update", $topic=FALSE, $folder=FALSE, $full = FALSE, $old_turnus = FALSE, $dont_check_overlaps = TRUE, $update_resources = TRUE, $presence_dates_only = TRUE) {
	global $RESOURCES_ENABLE, $RELATIVE_PATH_RESOURCES, $TERMIN_TYP, $user;
	
	if ($RESOURCES_ENABLE)	{
	 	include_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		$insertAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
	}

	$hash_secret = "blubbersuppe";
	$date_typ=1; //type to use for new dates
	$author = get_fullname();

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$semester = new SemesterData;
	$holiday = new HolidayData;

	//load data of the Veranstaltung
	$query = sprintf("SELECT start_time, duration_time, metadata_dates FROM seminare WHERE Seminar_id = '%s'", $sem_id);
	$db->query($query);
	$db->next_record();

	$term_data = unserialize ($db->f("metadata_dates"));
	$veranstaltung_start_time = $db->f("start_time");
	$veranstaltung_duration_time = $db->f("duration_time");
	
	if (($mode == "update") && (!$old_turnus))
		$old_turnus = $term_data;
	
	//load the ids from already created dates
	if ($mode == "update") {

		//first, we load all dates that exists
		$query = sprintf("SELECT termin_id, date, end_time FROM termine WHERE range_id='%s' %s ORDER BY date", $sem_id, ($presence_dates_only) ? "AND date_typ IN".getPresenceTypeClause() : "");
		$db->query($query);

		//than we check, which ones matches to our metadates
		while ($db->next_record()) {
			foreach ($old_turnus as $val) {
				//compense php sunday = 0 bullshit
				if ($val["day"] == 7)
					$t_day = 0;
				else
					$t_day = $val["day"];
				
				if ((date("w", $db->f("date")) == $t_day) &&
					(date("G", $db->f("date")) == $val["start_stunde"]) &&
					(date("i", $db->f("date")) == $val["start_minute"]) &&
					(date("G", $db->f("end_time")) == $val["end_stunde"]) &&
					(date("i", $db->f("end_time")) == $val["end_minute"]))
					$saved_dates[] = $db->f("termin_id");
			}
		}
	}
	
	//determine first day of the start-week as sem_begin
	$all_semester = $semester->getAllSemesterData();
	if ($term_data["start_woche"] >= 0) {
		foreach ($all_semester as $val)
			if (($veranstaltung_start_time >= $val["beginn"]) AND ($veranstaltung_start_time <= $val["ende"])) {
				$sem_begin = mktime(0, 0, 0, date("n",$val["vorles_beginn"]), date("j",$val["vorles_beginn"])+($term_data["start_woche"] * 7),  date("Y",$val["vorles_beginn"]));
			}
	} else
		$sem_begin = $term_data["start_termin"];
		
	$dow = date("w", $sem_begin);

	if ($dow <= 5)
		$corr = ($dow -1) * -1;
	elseif ($dow == 6)
		$corr = 2;
	elseif ($dow == 0)
		$corr = 1;
	else
		$corr = 0;
	
	if ($corr)
		$sem_begin_uncorrected = $sem_begin;
		
	$sem_begin = mktime(0, 0, 0, date("n",$sem_begin), date("j",$sem_begin)+$corr,  date("Y",$sem_begin));
	
	foreach ($all_semester as $val)
		if (($veranstaltung_start_time >= $val["beginn"]) AND ($veranstaltung_start_time <= $val["ende"])) {
			$sem_end = $val["vorles_ende"];
		}

	//determine the last day as sem_end when $full (Veranstaltung uses multiple Semesters)
	if ($full)
		if ($veranstaltung_duration_time == -1) {
			$last_sem = array_pop($all_semester);
			$sem_end=$last_sem["vorles_ende"];
		} else
			foreach ($all_semester as $val)
				if  ((($veranstaltung_start_time + $veranstaltung_duration_time + 1) >= $val["beginn"]) AND (($veranstaltung_start_time + $veranstaltung_duration_time +1) <= $val["ende"]))
					$sem_end=$val["vorles_ende"];
			
	
	$interval = $term_data["turnus"] + 1;
	
	//create the dates
	$affected_dates=0;
	if (is_array($term_data["turnus_data"]))
		do {
			foreach ($term_data["turnus_data"] as $val) {
				$do = TRUE;

				//create new dates
				$start_time = mktime ($val["start_stunde"], $val["start_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1) + ($week * 7), date("Y", $sem_begin));
				$end_time = mktime ($val["end_stunde"], $val["end_minute"], 0, date("n", $sem_begin), date("j", $sem_begin) + ($val["day"] -1) + ($week * 7), date("Y", $sem_begin));

				$all_holiday = $holiday->getAllHolidays(); // fetch all Holidays
				// get all holidays from db
				foreach ($all_holiday as $val2)
					if (($val2["beginn"] <= $start_time) && ($start_time <=$val2["ende"]))
						$do = FALSE;
		
				//check for calculatable holidays
				if ($do) {
					$holy_type = holiday($start_time);
					if ($holy_type["col"] == 3)
						$do = FALSE;
				}
				
				//check if corrected $sem_begin
				if (($do) && ($sem_begin_uncorrected))
					if ($start_time < $sem_begin_uncorrected) {
						$do = FALSE;
						if ($term_data["turnus"])
							$cor_interval = -1;
					}

				if (($do) && ($end_time <$sem_end)){
					//ids
					$date_id=md5(uniqid("lisa"));
					$folder_id=md5(uniqid("alexandra"));
					$aktuell=time();

					//if we have a resource_id, we take the room name from resource_id
					if (($val["resource_id"]) && ($RESOURCES_ENABLE))	
						$room = getResourceObjectName($val["resource_id"]);
					else
						$room = $val["room"];
						
					//create topic
					if (($topic) && (!$saved_dates[$affected_dates]))
						$topic_id=CreateTopic($TERMIN_TYP[$date_typ]["name"]." " . _("am") . " ".date("d.m.Y", $start_time), $author, _("Hier kann zu diesem Termin diskutiert werden"), 0, 0, $sem_id);
		
					//create folder
					if (($folder) && (!$saved_dates[$affected_dates])) {
						$titel = sprintf (_("%s am %s"), $TERMIN_TYP[$date_typ]["name"], date("d.m.Y", $start_time));
						$description= _("Ablage für Ordner und Dokumente zu diesem Termin");
						$db2->query("INSERT INTO folder SET folder_id='$folder_id', range_id='$date_id', description='$description', user_id='$user->id', name='$titel', mkdate='$aktuell', chdate='$aktuell'");
					} else
						$folder_id='';
					
					//insert/update dates
					if ($saved_dates[$affected_dates]) 
						$query2 = "UPDATE termine SET date='$start_time', chdate='$aktuell', end_time='$end_time', raum='$room' WHERE termin_id = '".$saved_dates[$affected_dates]."' ";
					else
						$query2 = "INSERT INTO termine SET termin_id='$date_id', range_id='$sem_id', autor_id='$user->id', content='" . _("Kein Titel") . "', date='$start_time', mkdate='$aktuell', chdate='$aktuell', date_typ='$date_typ', topic_id='$topic_id', end_time='$end_time', raum='$room' ";
					$db2->query($query2);
					if ($db2->affected_rows()) {
						//insert an entry for the linked resource, if resource management activ
						if ($RESOURCES_ENABLE) {
							$insertAssign->dont_check = $dont_check_overlaps;
							//only if we get a resource_id, we update assigns...
							if (($val["resource_id"]) && ($update_resources)){
								if ($saved_dates[$affected_dates]) {
									$resources_result = array_merge($resources_result, $insertAssign->changeDateAssign($saved_dates[$affected_dates], $val["resource_id"]));
								} else {
									$resources_result = array_merge($resources_result, $insertAssign->insertDateAssign($date_id, $val["resource_id"]));
								}
							//...if no resource_id (but assign, if ressource was set but is no more), kill assign
							} elseif ($saved_dates[$affected_dates]) {
								$insertAssign->killDateAssign($saved_dates[$affected_dates]);
							}
						}
						$affected_dates++;
					}
					
					//update topic & folder
					if ($saved_dates[$affected_dates-1]) {
						//load topic- and folder_id
						$db->query("SELECT topic_id, content FROM termine WHERE termin_id = '".$saved_dates[$affected_dates-1]."' ");
						$db->next_record();
						
						//change topic
						$db2->query("UPDATE px_topics SET name='".$TERMIN_TYP[$date_typ]["name"].": ".$db->f("content")." " . _("am") . " ".date("d.m.Y ", $start_time)."', chdate='$aktuell' WHERE topic_id='".$db->f("topic_id")." '");
					
						//change folder
						$titel = sprintf (_("%s: %s am %s"), $TERMIN_TYP[$date_typ]["name"], $db->f("content"), date("d.m.Y", $start_time));
						$db2->query("UPDATE folder SET name='$titel', chdate='$aktuell' WHERE range_id = '".$saved_dates[$affected_dates-1]."' ");
					}
				}
			}
			//inc the week
			$week = $week + $interval + $cor_interval;

			if ($cor_interval)
				unset($cor_interval);

		} while ($end_time <$sem_end);

		//kill dates
		if (sizeof($saved_dates) >$affected_dates)
			for ($i=$affected_dates; $i<sizeof($saved_dates); $i++) {
				$query2 = "SELECT topic_id FROM termine WHERE termin_id = '".$saved_dates[$i]."' ";
				$db2->query($query2);
				$db2->next_record();
				delete_date ($saved_dates[$i], $db2->f("topic_id"), TRUE, $range_id);
			}

	$result_a["changed"]=$affected_dates;
	$result_a["resources_result"]=$resources_result;

	return ($result_a);
}

//Checkt, ob Ablaufplantermine zu gespeicherten Metadaten vorliegen
function isSchedule ($sem_id, $presence_dates_only = TRUE, $clearcache = FALSE) {
	static $cache;
	
	if ($clearcache)
		$cache = '';

	if (isset($cache[$sem_id]))
		return $cache[$sem_id];

	$db = new DB_Seminar;
	$query = sprintf ("SELECT metadata_dates FROM seminare WHERE Seminar_id = '%s'", $sem_id);
	
	$db->query($query);
	$db->next_record();
	
	$term_metadata=unserialize($db->f("metadata_dates"));

	//first, we load all dates that exists
	$query = sprintf("SELECT termin_id, date, end_time FROM termine WHERE range_id='%s' %s ORDER BY date", $sem_id, ($presence_dates_only) ? "AND date_typ IN".getPresenceTypeClause() : "");
	$db->query($query);
	
	if ($term_metadata["art"] == 1) {
		$cache[$sem_id] = $db->nf();
		return $cache[$sem_id];
		
	} else {

		//than we check, which ones matches to our metadates
		while ($db->next_record()) {
			if (is_array($term_metadata["turnus_data"]))
			foreach ($term_metadata["turnus_data"] as $val) {
				//compense php sunday = 0 bullshit
				if ($val["day"] == 7)
					$t_day = 0;
				else
					$t_day = $val["day"];
				
				if ((date("w", $db->f("date")) == $t_day) &&
					(date("G", $db->f("date")) == $val["start_stunde"]) &&
					(date("i", $db->f("date")) == $val["start_minute"]) &&
					(date("G", $db->f("end_time")) == $val["end_stunde"]) &&
					(date("i", $db->f("end_time")) == $val["end_minute"]))
					$matched_dates[$db->f("termin_id")] = TRUE;
			}
		}
	
		if (isset($matched_dates)) {
			$cache[$sem_id] = sizeof($matched_dates);
			return $cache[$sem_id];
		} else {
			$cache[$sem_id] = FALSE;
			return FALSE;
		}
	}
}

//Checkt, ob bereits angelegte Termine ueber mehrere Semester laufen
function isDatesMultiSem ($sem_id) {
	$db = new DB_Seminar;

	//we load the first date
	$query = sprintf("SELECT date FROM termine WHERE range_id='%s' ORDER BY date LIMIT 1", $sem_id);
	$db->query($query);
	$db->next_record();
	$first = $db->f("date");

	//we load the last date
	$query = sprintf("SELECT date FROM termine WHERE range_id='%s' ORDER BY date DESC LIMIT 1", $sem_id);
	$db->query($query);
	$db->next_record();
	$last = $db->f("date");

	//than we check, if they are in the same semester
	if (get_sem_name ($first) != get_sem_name ($last))
		return TRUE;
	else
		return FALSE;
}

/**
* this functions extracts all the dates, which are corresponding to a metadate
*
* @param		string	seminar_id
* @return		array	["metadate_numer"]["termin_id"]
*				"metadate_number" the numerber of the corresponding metadate. first metadate (in chronological order) is always 0
*				"termin_id" the termin_id that are corresponding to the given metdat_number
*
*/
function getMetadateCorrespondingDates ($sem_id, $presence_dates_only) {
	$semObj = new Seminar($sem_id);
	$db = new DB_Seminar;
	
	//first, we load all dates that exists
	$query = sprintf("SELECT termin_id, date, end_time FROM termine WHERE range_id='%s' %s ORDER BY date", $sem_id, ($presence_dates_only) ? "AND date_typ IN".getPresenceTypeClause() : "");
	$db->query($query);

	//than we check, which ones matches to our metadates
	while ($db->next_record()) {

		foreach ($semObj->getMetaDates() as $key=>$val) {
			//compense php sunday = 0 bullshit
			if ($val["day"] == 7)
				$t_day = 0;
			else
				$t_day = $val["day"];
			
			if ((date("w", $db->f("date")) == $t_day) &&
				(date("G", $db->f("date")) == $val["start_hour"]) &&
				(date("i", $db->f("date")) == $val["start_minute"]) &&
				(date("G", $db->f("end_time")) == $val["end_hour"]) &&
				(date("i", $db->f("end_time")) == $val["end_minute"]))
				$result[$key][$db->f("termin_id")] = TRUE;
		}
	}

	if (is_array($result))
		return $result;
	else
		return FALSE;
}

/**
* this functions checks, if a date corresponds with a metadate
*
* @param		string	termin_id
* @return		boolean	TRUE, if the date corresponds to a metadate
*
*/
function isMetadateCorrespondingDate ($termin_id, $begin = '', $end = '', $seminar_id='') {
	$db = new DB_Seminar;
	
	//first, we the date
	$query = sprintf("SELECT termin_id, date, end_time, range_id FROM termine WHERE termin_id ='%s' ", $termin_id);
	$db->query($query);
	if (!$db->next_record()){
		return false;
	}

	if ((!$begin) && (!$end) && (!$seminar_id)) {
		$begin = $db->f("date");
		$end = $db->f("end_time");
		$seminar_id = $db->f("range_id");
	}
	$semObj = new Seminar($seminar_id);

	//than we check, if the date matches a metadate
	if (is_array($semObj->getMetaDates())) {
		foreach ($semObj->getMetaDates() as $key=>$val) {
			//compense php sunday = 0 bullshit
			if ($val["day"] == 7)
				$t_day = 0;
			else
				$t_day = $val["day"];
			
			if ((date("w", $begin) == $t_day) &&
				(date("G", $begin) == $val["start_hour"]) &&
				(date("i", $begin) == $val["start_minute"]) &&
				(date("G", $end) == $val["end_hour"]) &&
				(date("i", $db->f("end_time")) == $val["end_minute"]))
				$result = TRUE;
		}
	}
	return $result;
}

/**
* a small helper funktion to get the type query for "Sitzungstermine"
* (this dates are important to get he regularly, presence dates
* for a seminar
*
* @return		string	the SQL-clause to select only the "Sitzungstermine"
*
*/
function getPresenceTypeClause() {
	global $TERMIN_TYP;
	
	$i=0;
	$typ_clause = "(";
	foreach ($TERMIN_TYP as $key=>$val) {
		if ($val["sitzung"]) {
			if ($i)
				$typ_clause .= ", ";
			$typ_clause .= "'".$key."' ";
			$i++;
		}
	}
	$typ_clause .= ")";

	return $typ_clause;
}


/**
* Javascript für TerminEingabeHilfe
*
* Beim ersten Aufruf wird die benötigte js-Funktionen
* und die HTML-TerminZeile zurückgegeben. Bei allen weiteren Aufrufen enthält
* der Rückgabewert nur noch die HTML-TerminZeile.
*
* @param	int	Werte von 1 bis 6, bestimmt welche Formularfeldnamen verwendet werden
* @param	int	counter wenn mehrere TerminZeilen auf einer Seite
* @param	string	ursprüngliche StartStunde (Wert für ESC Taste)
* @param	string	ursprüngliche StartMinute (Wert für ESC Taste)
* @param	string	ursprüngliche EndStunde (Wert für ESC Taste)
* @param	string	ursprüngliche EndMinute (Wert für ESC Taste)
* @return	string	JavaScriptCode und HTML-TerminZeile
*
*/
function Termin_Eingabe_javascript ($t = 0, $n = 0, $ss = '', $sm = '', $es = '', $em = '') {
	global $auth, $CANONICAL_RELATIVE_PATH_STUDIP, $RELATIVE_PATH_CALENDAR;

	if (!$auth->auth["jscript"]) return '';
	$km = ($auth->auth["xres"] > 650)? 8 : 6;
	$kx = ($auth->auth["xres"] > 650)? 780 : 600;

	$txt = '&nbsp;';

	$q = ($ss !== '')? "&ss={$ss}&sm={$sm}&es={$es}&em={$em}":'';
	$txt .= "<a href=\"javascript:window.open('".$CANONICAL_RELATIVE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR. "/views/insert_date_popup.php?mcount={$km}&element_switch={$t}&c={$n}{$q}', 'kalender', 'dependent=yes, width=$kx, height=480');void(0);";
	$txt .= '"><img src="pictures/edit_transparent.gif" border="0" align="middle" ';
	$txt .= tooltip(_('Für Eingabehilfe zur einfacheren Terminwahl bitte hier klicken.'),TRUE,FALSE);
	$txt .= '></a>';

	return  $txt;
}

?>
