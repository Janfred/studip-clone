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


/*
Die Funktion veranstaltung_beginn errechnet den ersten Seminartermin aus dem Turnus Daten.
Zurueckgegeben wird ein String oder Timestamp. je nach return_mode (TRUE = Timestamp)
Evtl. Ergaenzungen werden im Stringmodus mit ausgegeben.
Die Funktion kann mit einer Seminar_id aufgerufen werden, dann werden saemtliche gespeicherten Daten 
beruecksichtigt. Im 'ad hoc' Modus koennen der Funktion auch die eizelnen Variabeln des Metadaten-Arrays
uebergeben werden. Dann werden konkrete Termine nur mit berruecksichtigt, sofern sie schon angelegt wurden.
*/

function veranstaltung_beginn ($seminar_id='', $art='', $semester_start_time='', $start_woche='', $start_termin='', $turnus_data='', $return_mode='')
	{
	global $SEMESTER;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;	
	
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
	if ($term_data["art"]==0)
		{
		if (($term_data["start_woche"] ==0) || ($term_data["start_woche"] ==1)) // Startzeitpunkt 1. oder 2. Semesterwoche
			if (sizeof($term_data["turnus_data"])) {
				foreach ($SEMESTER as $sem)
					if (($term_data["start_time"] >= $sem["beginn"]) AND ($term_data["start_time"] <= $sem["ende"]))
						$vorles_beginn=$sem["vorles_beginn"];
				$start_termin=$vorles_beginn+(($term_data["turnus_data"][0]["day"]-1)*24*60*60)+($term_data["turnus_data"][0]["start_stunde"]*60*60)+($term_data["turnus_data"][0]["start_minute"]*60) + ($term_data["start_woche"] * 7 * 24 * 60 *60);
				$end_termin=$vorles_beginn+(($term_data["turnus_data"][0]["day"]-1)*24*60*60)+($term_data["turnus_data"][0]["end_stunde"]*60*60)+($term_data["turnus_data"][0]["end_minute"]*60) + ($term_data["start_woche"] * 7 * 24 * 60 *60);;
				$return_string=date ("d.m.Y, G:i", $start_termin);
				$return_int=$start_termin;
				if ($start_termin != $end_termin) 
					$return_string.=" - ".date ("G:i", $end_termin);
				}
			else {
				if ($term_data["start_woche"]==0) {
					$return_string="1. Semesterwoche";
					$return_int=$vorles_beginn;
				} else {
					$return_string="2. Semesterwoche";
					$return_int=$vorles_beginn+604800;
				}
			}
		else //anderer Startzeitpunkt gewaehlt
			{
			if ($term_data["start_termin"]<1) {
				$return_string.="nicht bekannt";
				$return_int=-1;
			}	
			else {
				$return_string.=date ("d.m.Y", $term_data["start_termin"]);
				$return_int=$term_data["start_termin"];
			}
			if ($term_data["turnus_data"][0]["start_stunde"]) {
				$return_string.=", ". $term_data["turnus_data"][0]["start_stunde"]. ":"; 
				if (($term_data["turnus_data"][0]["start_minute"] > 0)  &&  ($term_data["turnus_data"][0]["start_minute"] < 10))
					$return_string.="0". $term_data["turnus_data"][0]["start_minute"];
				elseif ($term_data["turnus_data"][0]["start_minute"] > 10)
					$return_string.=$term_data["turnus_data"][0]["start_minute"];
				if (!$term_data["turnus_data"][0]["start_minute"])
					$return_string.="00";
				if (($term_data["turnus_data"][0]["end_stunde"] != $term_data["turnus_data"][0]["start_stunde"]) && ($term_data["turnus_data"][0]["end_minute"] !=$term_data["turnus_data"][0]["start_minute"])) {
					$return_string.= " - ". $term_data["turnus_data"][0]["end_stunde"]. ":";
					if (($term_data["turnus_data"][0]["end_minute"] > 0)  &&  ($term_data["turnus_data"][0]["end_minute"] < 10))
						$return_string.="0".$term_data["turnus_data"][0]["end_minute"];
					elseif ($term_data["turnus_data"][0]["end_minute"] > 10)
						$return_string.=$term_data["turnus_data"][0]["end_minute"];
					if (!$term_data["turnus_data"][0]["end_minute"])
						$return_string.="00";
					}
				}
			}
		}
	//Unregelmaessige Termine, also konkrete Termine aus Termintabelle
	else
		{
		$db2->query("SELECT date, end_time FROM termine WHERE date_typ='1' AND range_id='$seminar_id' ORDER BY date");
		$db2->next_record();
		if ($db->affected_rows())
			$return_string=date ("d.m.Y, G:i", $db2->f("date"))." - ".date ("G:i",  $db2->f("end_time"));
			$return_int=$db2->f("date");
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

function view_turnus ($seminar_id, $short = FALSE)
	{
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	
	setlocale ("LC_ALL", "de_DE");
	
	$db->query("SELECT metadata_dates FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();
	
	$term_data=unserialize($db->f("metadata_dates"));
	
	if ($term_data["art"] == 1)
		{
		$db2->query("SELECT * FROM termine WHERE range_id='$seminar_id' AND date_typ='1' ORDER BY date");
		if ($db2->affected_rows() == 0)
			{
			if ($short)
				$return_string="Termin: n. A.";
			else
				$return_string="unregelmässige Veranstaltung oder Blockveranstaltung. Die Termine stehen noch nicht fest ";
			}
		else
			if ($short)
				$return_string="Termine am ";
			else
				$return_string="unregelmässige Veranstaltung oder Blockveranstaltung am ";

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
						case "1": $return_string.="Mo."; break;
						case "2": $return_string.="Di."; break;
						case "3": $return_string.="Mi."; break;
						case "4": $return_string.="Do."; break;
						case "5": $return_string.="Fr."; break;
						case "6": $return_string.="Sa."; break;
						case "7": $return_string.="So."; break;
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
				$return_string="Zeiten: n. A.";
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
						case "1": $return_string.="Montag"; break;
						case "2": $return_string.="Dienstag"; break;
						case "3": $return_string.="Mittwoch"; break;
						case "4": $return_string.="Donnerstag"; break;
						case "5": $return_string.="Freitag"; break;
						case "6": $return_string.="Samstag"; break;
						case "7": $return_string.="Sonntag"; break;
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
				$return_string="Die Zeiten der Veranstaltung stehen noch nicht fest";
				}			
			if ($term_data["turnus"] == 1)
				$return_string.=" (zweiwöchentlich)";
		}
	return $return_string;
	}

/*
Die Funktion Seminartermin ueberpueft, ob der erste Veranstaltungstermin bereits vergangen ist 
und gibt entweder Datum und Zeit des ersten Termins oder Wochentage und Uhrzeiten als String zurueck.
*/

function seminartermin($seminar_id, $short = TRUE, $br = TRUE)
{
global $SEMESTER;
	$return_string = "";
	$db=new DB_Seminar;
	$db2=new DB_Seminar;	
	
	$db->query("SELECT metadata_dates, start_time, duration_time FROM seminare WHERE seminar_id='$seminar_id'");
	$db->next_record();
	$term_data=unserialize($db->f("metadata_dates"));

	if ($term_data["art"]==0)
		{
		if (($term_data["start_woche"] ==0) || ($term_data["start_woche"] ==1))
			if (sizeof($term_data["turnus_data"])) {
				foreach ($SEMESTER as $sem)
					if (($db->f("start_time") >= $sem["beginn"]) AND ($db->f("start_time") <= $sem["ende"]))
						$vorles_beginn=$sem["vorles_beginn"];
				$start_termin=$vorles_beginn+(($term_data["turnus_data"][0]["day"]-1)*24*60*60)+($term_data["turnus_data"][0]["start_stunde"]*60*60)+($term_data["turnus_data"][0]["start_minute"]*60) + ($term_data["start_woche"] * 7 * 24 * 60 *60);
				$end_termin=$vorles_beginn+(($term_data["turnus_data"][0]["day"]-1)*24*60*60)+($term_data["turnus_data"][0]["end_stunde"]*60*60)+($term_data["turnus_data"][0]["end_minute"]*60) + ($term_data["start_woche"] * 7 * 24 * 60 *60);;
				if (time() <$end_termin)
					$return_string=date ("d.m.Y, G:i", $start_termin)." - ".date ("G:i", $end_termin);
				}
			else {
				if ($term_data["start_woche"]==0)
					$return_string="1. Semesterwoche";
				else
					$return_string="2. Semesterwoche";
				}
		else
			{
			if (time() < ($term_data["start_termin"]+($term_data["turnus_data"][0]["start_stunde"]*60*60)+($term_data["turnus_data"][0]["start_minute"]*60)))
				{
				$return_string.=date ("d.m.Y", $term_data["start_termin"]). ", ". $term_data["turnus_data"][0]["start_stunde"]. ":";
				if (($term_data["turnus_data"][0]["start_minute"] > 0)  &&  ($term_data["turnus_data"][0]["start_minute"] < 10))
					$return_string.="0". $term_data["turnus_data"][0]["start_minute"];
				elseif ($term_data["turnus_data"][0]["start_minute"] > 10)
					$return_string.=$term_data["turnus_data"][0]["start_minute"];
				if (!$term_data["turnus_data"][0]["start_minute"])
					$return_string.="00";
				$return_string.= " - ". $term_data["turnus_data"][0]["end_stunde"]. ":";
				if (($term_data["turnus_data"][0]["end_minute"] > 0)  &&  ($term_data["turnus_data"][0]["end_minute"] < 10))
					$return_string.="0".$term_data["turnus_data"][0]["end_minute"];
				elseif ($term_data["turnus_data"][0]["end_minute"] > 10)
					$return_string.=$term_data["turnus_data"][0]["end_minute"];
				if (!$term_data["turnus_data"][0]["end_minute"])
					$return_string.="00";
				}
			}
		}
	else
		{
		$db2->query("SELECT date, end_time FROM termine WHERE date_typ='1' AND range_id='$seminar_id' ORDER BY date");
		$db2->next_record();
		if ($db->affected_rows())
			{
			if (time() < $db2->f("end_time"))
				$return_string=date ("d.m.Y, G:i", $db2->f("date"))." - ".date ("G:i",  $db2->f("end_time"));
			}
		}

	if ($return_string <> "") 
		 return $return_string;


	//setlocale( ("LC_ALL", "de_DE");
	if ($term_data["art"] == 1)
		{
		$db2->query("SELECT * FROM termine WHERE range_id='$seminar_id' AND date_typ='1' ORDER BY date");
		if ($db2->affected_rows() == 0)
			{
			if ($short)
				$return_string="Termin: n. A.";
			else
				$return_string="unregelmässige Veranstaltung oder Blockveranstaltung. Die Termine stehen noch nicht fest ";
			}
		else
			if ($short)
				$return_string="Termine am ";
			else
				$return_string="unregelmässige Veranstaltung oder Blockveranstaltung am ";

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
					if($br)
						$return_string .= "<br>";
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
						case "1": $return_string.="Mo."; break;
						case "2": $return_string.="Di."; break;
						case "3": $return_string.="Mi."; break;
						case "4": $return_string.="Do."; break;
						case "5": $return_string.="Fr."; break;
						case "6": $return_string.="Sa."; break;
						case "7": $return_string.="So."; break;
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
					if($br)
						$return_string .= "<br>";
					}
				}
			else {
				$return_string="Zeiten: n. A.";
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
						case "1": $return_string.="Montag"; break;
						case "2": $return_string.="Dienstag"; break;
						case "3": $return_string.="Mittwoch"; break;
						case "4": $return_string.="Donnerstag"; break;
						case "5": $return_string.="Freitag"; break;
						case "6": $return_string.="Samstag"; break;
						case "7": $return_string.="Sonntag"; break;
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
				$return_string="Die Zeiten der Veranstaltung stehen noch nicht fest";
				}			
			if ($term_data["turnus"] == 1)
				$return_string.=" (zweiwöchentlich)";
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
		$return_string.=", Ort: ".$db->f("raum");
	return $return_string;		
	}

/*
Die Funktion next_date errechnet den naechsten Seminartermin. Dabei wird zuerst geprueft, ob es spezielle
Termine in der Termintabelle gibt. Wenn nicht wird der naechste Seminartermin aus dem Turnusdaten ermittelt.
*/

function next_date ($seminar_id)
	{
	}
	
/*
Die Funktion get_sem_name gibt den Namen eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/


function get_sem_name ($time) {
	global $SEMESTER;
	foreach ($SEMESTER as $key=>$val)
		if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
			return $val["name"];

}

/*
Die Funktion get_sem_num gibt die Nummer eines Semester, in dem ein uebergebener Timestamp liegt, zurueck
*/

function get_sem_num ($time) {
	global $SEMESTER;
	foreach ($SEMESTER as $key=>$val)
		if (($time >= $val["beginn"]) AND ($time <= $val["ende"]))
			return $key;

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
			$return_string.=" bis unbegrenzt";
		}
	return $return_string;		
	}


/*
Die Funktion edit_dates veraendert den zu der Uebergebenen termin_id passenden Termin.
Dazu wird die Beschreibung des Ordners angepasst, falls es einen gibt.
Dabei werden die Beschriftungen der Ordner im Forensystem und im Dateisystem aktualisiert.
*/


function edit_dates($stunde,$minute,$monat,$tag,$jahr,$end_stunde, $end_minute, $termin_id,$art,$titel,$description,$topic_id,$raum)
		{
		global $range_id,$user,$auth, $ebene, $term_data, $SEMESTER, $TERMIN_TYP;
		$do=TRUE;
		if (!checkdate($monat,$tag,$jahr))
			{
			$do=FALSE;
			$result="error§Bitte geben Sie ein g&uuml;ltiges Datum ein!";
			}

		if ($do)		
			if ((!$stunde) && (!end_stunde))
				{
				$do=FALSE;	
				$result.="error§Bitte geben Sie eine g&uuml;eltige Start- und Endzeit an!";
				}
	
		$start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
		$end_time = mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr);
	
		if ($do)		
			if ($start_time > $end_time)
				{
				$do=FALSE;	
				$result.="error§Der Endzeitpunkt muss nach dem Startzeitpunkt liegen!";
				}
				
		//Check auf Konsistenz mt Metadaten, Semestercheck
		if (($do) && ($ebene=="sem") && ($art==1) && (is_array($term_data ["turnus_data"]))) {
			foreach ($SEMESTER as $a) {
			if (($term_data["start_time"] >= $a["beginn"]) && ($term_data["start_time"] <= $a["ende"]))  {
				$sem_beginn=$a["beginn"];
				$sem_ende=$a["ende"];
				}
			if (($term_data["duration_time"] > 0) && ((($term_data["start_time"] + $term_data["duration_time"]) >= $a["beginn"]) && (($term_data["start_time"] + $term_data["duration_time"]) < $a["ende"])))
				$sem_ende=$a["ende"];
			}
			
		if (($start_time < $sem_beginn) || ($start_time > $sem_ende))
			$add_result.="info§Sie haben einen oder mehrere Termine eingegeben, der ausserhalb des Semesters, in dem die Veranstaltung stattfindet, liegt. Es wird empfohlen, diese Termin anzupassen.§";
		
		//Und dann noch auf regelmaessige Termine checken, wenn dieser Typ gewsehlt ist
		if (!$term_data["art"]) {
			foreach ($term_data ["turnus_data"] as $a) {
				if ($a["day"] == 7) 
					$tmp_day=0;
				else
					$tmp_day=$a["day"];
				if ($tmp_day == date("w", $start_time)) {
					$tmp_start_time=mktime (date("G", $start_time), date("i", $start_time), 0, 8, 1, 2001);
					$tmp_end_time=mktime (date("G", $end_time), date("i", $end_time), 0, 8, 1, 2001);
					$tmp_turnus_start=mktime ($a["start_stunde"], $a["start_minute"], 0, 8, 1, 2001);
					$tmp_turnus_end=mktime ($a["end_stunde"], $a["end_minute"], 0, 8, 1, 2001);
					if (($tmp_start_time >= $tmp_turnus_start) && ($tmp_end_time <= $tmp_turnus_end))
						$ok=TRUE;
					}
				}
			if (!$ok)
				$add_result.="info§Sie haben einen oder mehrere Termine eingegeben, der nicht zu den allgemeinen Veranstaltungszeiten stattfindet. Es wird empfohlen, Sitzungstermine von regelm&auml;&szlig;igen Veranstaltungen nur zu den allgemeinen Zeiten stattfinden zu lassen.§";
			}
		}

		
		if ($result) 
			$result.="<br> Der Termin <b>\"$titel\"</b> konnte nicht ge&auml;ndert werden.§";
	
		if ($do)
			{
			$db=new DB_Seminar;
			$db2=new DB_Seminar;
			$db3=new DB_Seminar;
			$db4=new DB_Seminar;
			$tmp = $auth->auth["uname"];
			$db->query ("SELECT Vorname , Nachname , username FROM auth_user_md5 WHERE username = '$tmp'");
			$db->next_record();
			$author=$db->f("Vorname")." " . $db->f("Nachname");

			$titel=$titel;
			$description=$description; 
			
			$db->query("UPDATE  termine SET autor_id='$user->id', content='$titel', date= '$start_time', end_time='$end_time', date_typ='$art', raum='$raum', description='$description'  WHERE termin_id='$termin_id'");
			if ($db->affected_rows()) {
				$db->query ("UPDATE termine SET chdate='".time()."' WHERE termin_id='$termin_id'"); //Nur wenn Daten geaendert wurden, schreiben wir auch ein chdate
				$succes=$termin_id;
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
			}
			
			else {
				if ($topic_id) 
					$db->query("UPDATE termine SET topic_id=''  WHERE termin_id='$termin_id'");		
				}
			//WA Ende
			
			//Aendern des Titels des zugehoerigen Ordners
			$titel_f=$TERMIN_TYP[$art]["name"].": $titel";
			$titel_f.=" am ".date("d.m.Y ", $start_time);
			$db->query("SELECT folder_id FROM folder WHERE range_id ='$termin_id'");
			if ($db->num_rows() == 1) {
				$db->next_record();
				$db2->query ("UPDATE folder SET name='$titel_f' WHERE folder_id = '".$db->f("folder_id")."'");
				if ($db2->affected_rows())
					$db3->query("UPDATE folder SET chdate='".time()."' WHERE folder_id = '".$db->f("folder_id")."'");
				}
			
			}
			$result_a["msg"]=$result;
			$result_a["succes"]=$succes;
			$result_a["add_msg"]=$add_result;
		return ($result_a);
		}



/*
Die Funktion delete_topic löscht rekursiv alle Postings ab der übergebenen topic_id, der zweite Parameter
muss(!) eine Variable sein, diese wird für jedes gelöschte Posting um eins erhöht
*/

function delete_topic($topic_id, &$deleted)  //rekursives löschen von topics VORSICHT!
{

	$db=new DB_Seminar;
	// echo "gelöscht $topic_id<br>";
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

function delete_date ($termin_id, $topic_id = FALSE, $folder_move=FALSE, $sem_id=0) {

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;	

	## Eventuell rekursiv Postings loeschen
	if ($topic_id) {
		delete_topic($topic_id,$count);
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
				$db3->query ("UPDATE folder SET name='Dateiordner zu gelöschtem Termin', description='Dieser Ordner enthält Dokumente und Termine eines gelöschten Termins' WHERE folder_id='".$db2->f("folder_id")."'");
				}
			}
		}

	## Und den Termine selbst loeschen
	$query = "DELETE FROM termine WHERE termin_id='$termin_id'";
	$db->query($query);
	$count = $db->affected_rows();

	return $count;
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

?>
