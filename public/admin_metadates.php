<?php
/**
* admin_metadates.php
*
* edit the settings for generic dates from a Veranstaltung
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		admin_metadates.php
* @modulegroup	admin
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_metadates.php
// Terminmetadatenverwaltung von Stud.IP
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("tutor");

include ("seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$HELP_KEYWORD="Basis.VeranstaltungenVerwaltenZeiten";

// Start of Output
include ('include/html_head.inc.php'); // Output of html head
include ('include/header.php');   // Output of Stud.IP head
include ('include/links_admin.inc.php');

require_once("msg.inc.php");//ja auch die...
require_once("config.inc.php");//ja,ja auch die...
require_once 'lib/functions.php';//ja,ja,ja auch die...
require_once("visual.inc.php");//ja,ja,ja,ja auch die...
require_once("dates.inc.php");//ja,ja,ja,ja,ja auch die...
require_once("lib/classes/Seminar.class.php");//ja,ja,ja,ja,ja,ja auch die...

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObjectPerms.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/ResourcesUserRoomsList.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/RoomRequest.class.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	$resList = new ResourcesUserRoomsList($user->id, TRUE, FALSE, TRUE);
}

$db=new DB_Seminar;
$db2=new DB_Seminar;
$cssSw=new cssClassSwitcher;
$semester = new SemesterData;
$sess->register ("term_metadata");

/**
* This function creates a snapshot for all the values the admin_metadates script uses
*
* The function serializes all the data which is used on this page. So you can
* compare an old and a new state of the whole set. It is used to inform the user,
* that the data isn't saved yet.
*
* @param		string	all the data in serilized form
*
*/
function get_snapshot() {
	global $term_metadata;
	return	serialize($term_metadata["turnus_data"]).
			serialize($term_metadata["sem_start_time"]).
			serialize($term_metadata["sem_duration_time"]).
			serialize($term_metadata["sem_start_termin"]).
			serialize($term_metadata["sem_end_termin"]).
			serialize($term_metadata["sem_vor_termin"]).
			serialize($term_metadata["sem_vor_end_termin"]).
			serialize($term_metadata["start_woche"]).
			serialize($term_metadata["art"]);
}

//get ID
if ($SessSemName[1])
	$seminar_id=$SessSemName[1];

//wenn wir frisch reinkommen, werden die alten Metadaten eingelesen
if (($seminar_id) && (!$uebernehmen_x) && (!$add_turnus_field_x) &&(!$delete_turnus_field) && !($open_ureg_x) && !($open_reg_x) && !($enter_start_termin_x) && !($nenter_start_termin_x)) {
	if ($RESOURCES_ENABLE)
		$db->query("SELECT metadata_dates, art, Name, start_time, duration_time, status, request_id  FROM seminare LEFT JOIN resources_requests USING (seminar_id) WHERE seminare.Seminar_id = '$seminar_id'");
	else
		$db->query("SELECT metadata_dates, art, Name, start_time, duration_time, status FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();
	$term_metadata=unserialize($db->f("metadata_dates"));
	$term_metadata["sem_status"]=$db->f("status");
	$term_metadata["sem_name"]=$db->f("Name");
	$term_metadata["sem_start_time"]=$db->f("start_time");
	$term_metadata["sem_duration_time"]=$db->f("duration_time");
	$term_metadata["sem_id"] = $seminar_id;
	$term_metadata["request_id"] = $db->f("request_id");
	if (!$term_metadata["sem_start_termin"]) $term_metadata["sem_start_termin"] =-1;
	if (!$term_metadata["sem_end_termin"]) $term_metadata["sem_end_termin"] =-1;
	if (!$term_metadata["sem_vor_termin"]) $term_metadata["sem_vor_termin"] =-1;
	if (!$term_metadata["sem_vor_end_termin"]) $term_metadata["sem_vor_end_termin"] =-1;
	$term_metadata["original"]=get_snapshot();
	$term_metadata["original_turnus_data"]=$term_metadata["turnus_data"];
	$term_metadata["original_turnus"]=$term_metadata["turnus"];
	$term_metadata["original_art"]=$term_metadata["art"];
	$term_metadata["original_start_time"]=$db->f("start_time");
	$term_metadata["original_duration_time"]=$db->f("duration_time");
	$term_metadata["original_start_woche"]=$term_metadata["start_woche"];
	$term_metadata["original_start_termin"]=$term_metadata["start_termin"];
	$term_metadata["original_resource_id"] = array();
	if (is_array($term_metadata["original_turnus_data"]))
		foreach ($term_metadata["original_turnus_data"] as $val)
			$term_metadata["original_resource_id"][] = $val["resource_id"];
	$term_metadata["update_dates"]=TRUE;
	}
else {

//Sicherheitscheck ob &uuml;berhaupt was zum Bearbeiten gewaehlt ist.
if (!$term_metadata["sem_id"]) {
	echo "</tr></td></table>";
	die;
}

//Umschalter zwischen den Typen
if ($enter_start_termin_x) {
	$term_metadata["start_woche"]=-1;
}

if ($nenter_start_termin_x) {
	$term_metadata["start_woche"]=0;
}

if ($open_reg_x) {
	$term_metadata["art"]=0;
}

if ($open_ureg_x) {
	$term_metadata["art"]=1;
	$term_metadata["start_woche"]=0;
}

if (($turnus_refresh) || ($term_refresh))
	{
	if (($sem_duration_time == 0) || ($sem_duration_time == -1))
		$term_metadata["sem_duration_time"]=$sem_duration_time;
	else
		$term_metadata["sem_duration_time"]=$sem_duration_time - $sem_start_time;
	$term_metadata["sem_start_time"]=$sem_start_time;
	}

if ($turnus_refresh)
	{
	if ($term_metadata["start_woche"] !=-1)
		$term_metadata["start_woche"]=$term_start_woche;
	$term_metadata["turnus"]=$term_turnus;
	$term_metadata["update_dates"]=$update_dates;


	//Arrays fuer Turnus loeschen
	$temp_turnus_data = $term_metadata["turnus_data"];
	$term_metadata["turnus_data"]='';
	//Alle eingegebenen Turnus-Daten in Sessionvariable uebernehmen
	for ($i=0; $i<$term_metadata["turnus_count"]; $i++)
		{
		$term_metadata["turnus_data"][$i]['desc'] = ($turnus_desc[$i] ? $turnus_desc[$i] : $turnus_desc_chooser[$i]);
		$term_metadata["turnus_data"][$i]["day"]=$turnus_day[$i]; 
		$term_metadata["turnus_data"][$i]["start_stunde"] = (strlen($turnus_start_stunde[$i]))? intval($turnus_start_stunde[$i]) : '';
		$term_metadata["turnus_data"][$i]["start_minute"] = (strlen($turnus_start_minute[$i]))? intval($turnus_start_minute[$i]) : ''; 
		$term_metadata["turnus_data"][$i]["end_stunde"] = (strlen($turnus_end_stunde[$i]))? intval($turnus_end_stunde[$i]) : ''; 
		$term_metadata["turnus_data"][$i]["end_minute"] = (strlen($turnus_end_minute[$i]))? intval($turnus_end_minute[$i]) : ''; 
		$term_metadata["turnus_data"][$i]["room"]=$turnus_room[$i]; 
		if (($turnus_resource_id[$i]) && ($turnus_resource_id[$i] != "NULL") && ($turnus_resource_id[$i] != "FALSE")) {
			$term_metadata["turnus_data"][$i]["resource_id"] = $turnus_resource_id[$i];
		} elseif (($turnus_resource_id[$i]) && ($turnus_resource_id[$i] == "FALSE")) {
			$term_metadata["turnus_data"][$i]["resource_id"] = FALSE;
		} else
			$term_metadata["turnus_data"][$i]["resource_id"] = $temp_turnus_data[$i]["resource_id"];


		//if we have a resource_id, we take the room name from resource_id (deprecated at the moment)
		/*if ($RESOURCES_ENABLE && $term_metadata["turnus_data"][$i]["resource_id"]) {
			$resObject =& ResourceObject::Factory($term_metadata["turnus_data"][$i]["resource_id"]);
			$term_metadata["turnus_data"][$i]["room"]=$resObject->getName();
		}*/
		
		}
	}
if (($turnus_refresh) || ($term_metadates["start_woche"] ==-1))
	{
	//Datum fuer ersten Termin umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
	if (($jahr>0) && ($jahr<100))
		 $jahr=$jahr+2000;

	if ($monat == _("mm")) $monat=0;
	if ($tag == _("tt")) $tag=0;
	if ($jahr == _("jjjj")) $jahr=0;

	if (!checkdate((int)$monat, (int)$tag, (int)$jahr) && ($monat) && ($tag) && ($jahr))
		{
		$errormsg=$errormsg."error§"._("Bitte geben Sie ein g&uuml;ltiges Datum ein!")."§";
		$check=FALSE;
		}
	else
		$check=TRUE;
	if ($check)
 		$term_metadata["start_termin"] = mktime(0,0,0,$monat,$tag,$jahr);
	else
		$term_metadata["start_termin"] = -1;
	}

//Felder fuer Standardtermine hinzufuegen/l&ouml;schen
if ($add_turnus_field_x)
	{
	$term_metadata["turnus_count"]++;
	}

if ($delete_turnus_field)
	{
	for ($i=0; $i<$term_metadata["turnus_count"]; $i++)
		if ($i != ($delete_turnus_field-1)) {
			$tmp_term_turnus_data[]=$term_metadata["turnus_data"][$i];
			$tmp_term_turnus_original_resource_id[] = $term_metadata["original_resource_id"][$i];
		}
	$term_metadata["turnus_data"]=$tmp_term_turnus_data;
	$term_metadata["original_resource_id"]=$tmp_term_turnus_original_resource_id;

	$term_metadata["turnus_count"]--;
	}


//Checks performen
if (($term_metadata["sem_duration_time"]<0) && ($term_metadata["sem_duration_time"] != -1))
	{
	$errormsg=$errormsg."error§"._("Das Endsemester darf nicht vor dem Startsemester liegen. Bitte &auml;ndern Sie die entsprechenden Angaben!")."§";
	}

if ($term_metadata["art"]==0)
	{
	for ($i=0; $i<$term_metadata["turnus_count"]; $i++)
		if ((($term_metadata["turnus_data"][$i]["start_stunde"] !== '') || ($term_metadata["turnus_data"][$i]["end_stunde"] !== '')))
			{
			if (($term_metadata['turnus_data'][$i]['start_stunde'] === '') xor ($term_metadata['turnus_data'][$i]['end_stunde'] === ''))
					{
					if (!$just_informed)
						$errormsg=$errormsg."error§"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der regul&auml;ren Veranstaltungstermine aus!")."§";
					$just_informed=TRUE;
					}
			if ((($term_metadata["turnus_data"][$i]["start_stunde"]>23) || ($term_metadata["turnus_data"][$i]["start_stunde"]<0))  ||  (($term_metadata["turnus_data"][$i]["start_minute"]>59) || ($term_metadata["turnus_data"][$i]["start_minute"]<0))  ||  (($term_metadata["turnus_data"][$i]["end_stunde"]>23) ||($term_metadata["turnus_data"][$i]["end_stunde"]<0))  || (($term_metadata["turnus_data"][$i]["end_minute"]>59) || ($term_metadata["turnus_data"][$i]["end_minute"]<0)))
					{
					if (!$just_informed3)
						$errormsg=$errormsg."error§"._("Sie haben eine ung&uuml;ltige Zeit eingegeben. Bitte &auml;ndern Sie die entsprechenden Angaben!")."§";
					$just_informed3=TRUE;
					}
			if (mktime($term_metadata["turnus_data"][$i]["start_stunde"], $term_metadata["turnus_data"][$i]["start_minute"], 0, 1, 1, 2001) >= mktime($term_metadata["turnus_data"][$i]["end_stunde"], $term_metadata["turnus_data"][$i]["end_minute"], 0, 1, 1, 2001)) 
				if ((!$just_informed5) && (!$just_informed)) {
					$errormsg=$errormsg."error§"._("Der Endzeitpunkt der regul&auml;ren Termine muss nach dem jeweiligen Startzeitpunkt liegen!")."§";
					$just_informed5=TRUE;
				}
			}
			elseif(!$just_informed4) 
				if (($term_metadata["turnus_data"][$i]["start_stunde"] === '') && ($term_metadata["turnus_data"][$i]["start_minute"] === '') && ($term_metadata["turnus_data"][$i]["end_stunde"] === '') && ($term_metadata["turnus_data"][$i]["end_minute"] === ''))
					$empty_fields++;
				else
					{
					$errormsg=$errormsg."error§"._("Sie haben nicht alle Felder f&uuml;r regul&auml;ren Termine ausgef&uuml;llt. bitte korrigieren sie dies!")."§";
					$just_informed4=TRUE;
					}
	}

if (($term_metadata["start_termin"] == -1) && ($term_metadata["start_woche"] ==-1))
	$errormsg=$errormsg."error§"._("Bitte geben Sie einen ersten Termin an!")."§";
else
	if ((($stunde) && (!$end_stunde)) || ((!$stunde) && ($end_stunde)))
		$errormsg=$errormsg."error§"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit des ersten Termins aus!")."§";
}


//Daten zum speichern vorbereiten
if (($uebernehmen_x) && (!$errormsg)) {
	//Termin-Metadaten-Array erzeugen
	$metadata_termin["art"]=$term_metadata["art"];
	$metadata_termin["start_termin"]=$term_metadata["start_termin"];
	$metadata_termin["start_woche"]=$term_metadata["start_woche"];
	$metadata_termin["turnus"]=$term_metadata["turnus"];
	$metadates_deleted = $metadates_new = $metadates_unchanged = array();

	//check if change of mode
	if ($RESOURCES_ENABLE){
		//check for changes by deleted entries, changes to semesterdata an start termin
		//$count_meta_changed	= (sizeof($tmp_metadata_termin["turnus_data"]) <  sizeof($term_metadata["original_turnus_data"]));
		$start_termin_changed =	($term_metadata["original_start_woche"] != $term_metadata["start_woche"]) || ($term_metadata["original_start_termin"] != $term_metadata["start_termin"]);
		$sem_changed = ($term_metadata["original_start_time"] != $term_metadata["sem_start_time"]) ||($term_metadata["original_duration_time"] != $term_metadata["sem_duration_time"]);
		$art_changed = ($term_metadata["original_art"] != $term_metadata["art"]);
		$turnus_changed = ($term_metadata["original_turnus"] != $term_metadata["turnus"]);
		//determine, if the resources are owned by the user
		if (is_array($term_metadata["original_turnus_data"])) {
			foreach ($term_metadata["original_turnus_data"] as $key=>$val) {
				if ($term_metadata["original_turnus_data"][$key]["resource_id"]) {
					$resObjPrm =& ResourceObjectPerms::Factory($term_metadata["original_turnus_data"][$key]["resource_id"]);
					if (!$resObjPrm->havePerm("autor"))
						$foreign_resources = TRUE;
				}
			}
		}
	}

	//indiziertes (=sortierbares) temporaeres Array erzeugen
	if ($term_metadata["art"] == 0) {
		for ($i=0; $i<$term_metadata["turnus_count"]; $i++)
			if (($term_metadata["turnus_data"][$i]["start_stunde"] !== '')  && ($term_metadata["turnus_data"][$i]["end_stunde"] !== ''))
				$tmp_metadata_termin["turnus_data"][]=array("idx"=>$term_metadata["turnus_data"][$i]["day"].(($term_metadata["turnus_data"][$i]["start_stunde"] <10) ?  "0" : "").$term_metadata["turnus_data"][$i]["start_stunde"].(($term_metadata["turnus_data"][$i]["start_minute"]< 10) ?  "0" : "").$term_metadata["turnus_data"][$i]["start_minute"],
															"day" => $term_metadata["turnus_data"][$i]["day"],
															"start_stunde" => $term_metadata["turnus_data"][$i]["start_stunde"],
															"start_minute" => $term_metadata["turnus_data"][$i]["start_minute"],
															"end_stunde" => $term_metadata["turnus_data"][$i]["end_stunde"],
															"end_minute" => $term_metadata["turnus_data"][$i]["end_minute"],
															"room" => $term_metadata["turnus_data"][$i]["room"],
															"resource_id" => $term_metadata["turnus_data"][$i]["resource_id"],
															"desc" => $term_metadata["turnus_data"][$i]["desc"]);

		//check for dublettes
		if ($tmp_metadata_termin["turnus_data"]) {
			$tmp_array_assi = $tmp_metadata_termin["turnus_data"];
			foreach ($tmp_array_assi as $key1=>$val1)  {
				foreach ($tmp_metadata_termin["turnus_data"] as $key2=>$val2) {
					if (($val1["day"] == $val2["day"]) &&
						($val1["start_stunde"] == $val2["start_stunde"]) &&
						($val1["start_minute"] == $val2["start_minute"]) &&
						($val1["end_stunde"] == $val2["end_stunde"]) &&
						($val1["end_minute"] == $val2["end_minute"]) &&
						($val1["room"] == $val2["room"]) &&
						($val1["ressource_id"] == $val2["ressource_id"]) &&
						($key1 != $key2))
						unset ($tmp_metadata_termin["turnus_data"][$key1]);
				}
			}
		}

		//check for changes to the old (saved) metadates (for each metadate)
		if (is_array($tmp_metadata_termin["turnus_data"])) {
			$art_changed = FALSE;
			$metadates_changed = FALSE;
			foreach ($tmp_metadata_termin["turnus_data"] as $key => $val) {
				if (is_array($term_metadata["original_turnus_data"])){
					foreach ($term_metadata["original_turnus_data"] as $old_key => $old_val){
						$found = false;
						if (serialize($val) == serialize($old_val)) {
							$metadates_unchanged[] = $val;
							$found = true;
							break;
						}
					}
				}
				if (!$found) $metadates_new[] = $val;
			}
			if (is_array($term_metadata["original_turnus_data"])){
				foreach ($term_metadata["original_turnus_data"] as $old_key => $old_val) {
					foreach ($tmp_metadata_termin["turnus_data"] as $key => $val){
						$found = false;
						if (serialize($val) == serialize($old_val)) {
							$found = true;
							break;
						}
					}
					if (!$found) $metadates_deleted[] = $old_val;
				}
			}
		}
	}
	//check for the rights, the user has on the selected resource-objects
	if ($RESOURCES_ENABLE && (count($metadates_deleted) || count($metadates_new) || $art_changed || $turnus_changed || $sem_changed || $start_termin_changed )) {
		$update_resources = FALSE;
		$update_dates_kill_resources = TRUE;
		//check if change inside regular times
		if (count($metadates_deleted) || count($metadates_new)) {
			$check_metadates = array_merge((array)$metadates_deleted,(array)$metadates_new);
		} else {
			$check_metadates = array_merge((array)$term_metadata['turnus_data'], (array)$term_metadata['original_turnus_data']);
		}
		foreach ($check_metadates as $val) {
			if ($val["resource_id"]){
				$assigned_resources = TRUE;
				$resObjPrm =& ResourceObjectPerms::Factory($val["resource_id"]);
				if (!$resObjPrm->havePerm("autor")) $foreign_resources = TRUE;
			}
		}
		//ok, what do we here? If foreign requests and the ability to create a room request, do this. Else (no requests available or rights on the resource), we try so save the assign.
		if (($foreign_resources) && ($RESOURCES_ALLOW_ROOM_REQUESTS) && (!$change_metadates_open_request)) {
			$update_resources = FALSE;
			$update_dates_kill_resources = TRUE;
			$create_request = TRUE;

			if ($art_changed)
				$errormsg.="info§"._("Sie haben die Art der Veranstaltungszeiten ge&auml;ndert.")." ";
			elseif ($turnus_changed)
				$errormsg.="info§"._("Sie haben den Turnus der Veranstaltung ge&auml;ndert.")." ";
			elseif ($sem_changed)
				$errormsg.="info§"._("Sie haben das Semester ge&auml;ndert.")." ";
			elseif ($start_termin_changed)
				$errormsg.="info§"._("Sie haben den Veranstaltungsbeginn ge&auml;ndert.")." ";
			else
				$errormsg.="info§"._("Sie haben die Belegungszeiten eines zugewiesenen Raums ge&auml;ndert.")." ";

			$errormsg.=_("Dabei verlieren Sie die Buchung des zugewiesenen Raums. Der zust&auml;ndige Raumadministrator mu&szlig; Ihnen den Raum erneut zuweisen, daf&uuml;r wird eine Raumanfrage ben&ouml;tigt.")
					."<br />"._("Wollen Sie die Zeiten dennoch &auml;ndern?")."<br /><a href=\"$PHP_SELF?change_metadates_open_request=1&uebernehmen_x=1&seminar_id=".$term_metadata["sem_id"]."\">".makeButton("ja2")."</a>"
					."&nbsp;<a href=\"$PHP_SELF?seminar_id=".$term_metadata["sem_id"]."\">".makeButton("nein")."</a>§";
		} elseif  ((!$change_metadates_open_request) && ($assigned_resources))
			$update_resources = TRUE;
	}
}

//now, save the data
if (($uebernehmen_x) && (!$errormsg)) {
	//now it's time to sort....
	if (($term_metadata["art"] == 0) && (is_array($tmp_metadata_termin["turnus_data"]))) {
		sort ($tmp_metadata_termin["turnus_data"]);

		foreach ($tmp_metadata_termin["turnus_data"] as $tmp_array) {
			$metadata_termin["turnus_data"][]=$tmp_array;
		}
	}

	//Termin-Metadaten-Array zusammenmatschen zum beseren speichern in der Datenbank
	$serialized_metadata=mysql_escape_string(serialize ($metadata_termin));

	//speichern
	$db->query ("UPDATE seminare SET metadata_dates='$serialized_metadata', start_time='".$term_metadata["sem_start_time"]."', duration_time='".$term_metadata["sem_duration_time"]."' WHERE Seminar_id ='".$term_metadata["sem_id"]."'");
	if ($db->affected_rows()) {
		$errormsg.="msg§"._("Die allgemeinen Termindaten wurden aktualisiert.")."§";
		$db->query ("UPDATE seminare SET chdate='".time()."' WHERE Seminar_id ='".$term_metadata["sem_id"]."'");

		//update the dates in the assi.... (we update if user wants to or the changes to times/rooms/request-system causes killing all the date-assigns)
		if (($term_metadata["update_dates"]) || (($term_metadata["update_dates"]) && ($update_dates_kill_resources))) {
			$multisem = isDatesMultiSem($term_metadata["sem_id"]);
			$result = dateAssi($term_metadata["sem_id"], $mode="update", FALSE, FALSE, $multisem, $term_metadata["original_turnus_data"], FALSE, ($update_resources && !$change_metadates_open_request));
			if ($result["changed"]) {
				$errormsg.= sprintf ("msg§"._("%s Termine des Ablaufplans aktualisiert.")."§", $result["changed"]);
			}
			if ($result['resources_result']){
				$updateResult = $result['resources_result'];
			}
		}

		//If resource-management activ, update the assigned resources and do the overlap checks.... not so easy!
		if (($RESOURCES_ENABLE) && ($update_resources)) {
		 	$veranstAssign = new VeranstaltungResourcesAssign($term_metadata["sem_id"]);
    		$updateResult = array_merge ((array)$updateResult, (array)$veranstAssign->updateAssign());
			//produce the messages from result
			$errormsg.=getFormattedResult($updateResult, "booth");
		}

 		//reopen a request or send user to admin_room_requests, if no request exists
 		if (($RESOURCES_ENABLE) && ($RESOURCES_ALLOW_ROOM_REQUESTS) && ($change_metadates_open_request)) {
 			//kill the assigns
 			$veranstAssign = new VeranstaltungResourcesAssign($term_metadata["sem_id"]);
 			$veranstAssign->deleteAssignedRooms();
 			$veranstAssign->clearTurnusData();
			foreach(getMetadateCorrespondingDates($term_metadata["sem_id"], true) as $meta){
				foreach(array_keys($meta) as $termin_id){
					$veranstAssign->killDateAssign($termin_id);
				}
			}

 			//no request... user should create self an request
 			if (!$term_metadata["request_id"]) {
				$errormsg.= sprintf ("info§"._("Um R&auml;ume f&uuml;r Ihre Veranstaltung zu bekommen, m&uuml;ssen Sie eine %sRaumanfrage%s erstellen.")."§", "<a href=\"admin_room_requests.php?seminar_id=\"".$term_metadata["sem_id"]."\">", "</a>");
 			//update request (set closed 0 (open!))
 			} else {
	 			$resReq = new RoomRequest($term_metadata["request_id"]);
 				$resReq->setClosed(0);
 				$resReq->store();
				$errormsg.= sprintf ("info§"._("Die Raumanfrage der Veranstaltung wurde an den zust&auml;ndigen Raumadministrator gesandt. Um die Anfrage einzusehen oder zu bearbeiten, gehen Sie auf %sRaumanfragen%s.")."§", "<a href=\"admin_room_requests.php?seminar_id=\"".$term_metadata["sem_id"]."\">", "</a>");
 			}
 		}
	}
	$sem_obj =& Seminar::GetInstance($term_metadata["sem_id"], true);
	$term_metadata['turnus_data'] = $sem_obj->metadata['turnus_data'];


	//Save the current state as snapshot to compare with current data and other original data for comparisons
	$term_metadata["original"] = get_snapshot();
	$term_metadata["original_turnus_data"] = $term_metadata["turnus_data"];
	$term_metadata["original_turnus"] = $term_metadata["turnus"];
	$term_metadata["original_art"] = $term_metadata["art"];
	$term_metadata["original_start_time"]=$term_metadata["sem_start_time"];
	$term_metadata["original_duration_time"]=$term_metadata["sem_duration_time"];
	$term_metadata["original_start_woche"]=$term_metadata["start_woche"];
	$term_metadata["original_start_termin"]=$term_metadata["start_termin"];
	$term_metadata["original_resource_id"] = array();
	if (is_array($term_metadata["original_turnus_data"]))
		foreach ($term_metadata["original_turnus_data"] as $val)
			$term_metadata["original_resource_id"][] = $val["resource_id"];

	$metadata_saved=TRUE;
}

 if (($errormsg) && (($open_reg_x) || ($open_ureg_x) || ($enter_start_termin_x) || ($nenter_start_termin_x) || ($add_turnus_field_x) || ($delete_turnus_field)))
 	$errormsg='';

 if ((!$metadata_saved) || (!$term_metadata["source_page"])) {
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp; <b>
		<?
		echo getHeaderLine($term_metadata["sem_id"])." -  "._("allgemeine Zeiten");
		?>
		</td>
	</tr>
	<?
	if (isset($errormsg)) {
	?>
	<tr>
		<td class="blank" colspan=2><br />
		<?parse_msg($errormsg);?>
		</td>
	</tr>
	<? } ?>
 	<tr>
		<td class="blank" valign="top">
			<br />
			<blockquote>
			<?
			print "<b>"._("Zeiten der Veranstaltung bearbeiten")."</b><br /><br />";
			print _("Sie k&ouml;nnen hier die allgemeinen Zeiten bearbeiten.")." <br />";
			if ($modules["schedule"])
				printf (_("Spezifische Termine zur Anzeige im Ablaufplan (z.B. Vorbesprechungstermine) legen Sie unter dem Men&uuml;punkt %s Ablaufplan </a> fest."), "<a href=\"admin_dates.php?ebene=sem&range_id=".$term_metadata["sem_id"]."\">");
			?>
			</blockqoute>
		</td>
		<td class="blank" align="right">
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/board2.jpg" border="0">
		</td>
	</tr>
	<tr>
	<td class="blank" colspan=2>
	<form method="POST" name="Formular" action="<? echo $PHP_SELF ?>">
		<table width="99%" border=0 cellpadding=2 cellspacing=0 align="center">
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
				<input type="IMAGE" name="uebernehmen" <? echo makeButton ("uebernehmen", "src") ?> border=0 value="uebernehmen">
				<? if ($term_metadata["source_page"]) {
					?> &nbsp; <input type="IMAGE" name="abbrechen" <? echo makeButton ("abbrechen", "src") ?> border=0 value="abbrechen"> <?
					}
				?>
				<? if ($term_metadata["original"] != get_snapshot()) {
					?> <br /><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><? print _("Diese Daten sind noch nicht gespeichert.") ?></font><br /> <?
					}
				?>
			</td>
		</tr>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right" rowspan=2>
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align="left">
				<font size=-1><b>&nbsp;<?= _("Allgemeine Zeiten:") ?></b><br /></font>
				<font size=-1>&nbsp;<?= _("Sie k&ouml;nnen hier angeben, ob die Veranstaltung regelm&auml;&szlig;ig stattfindet oder ob die Termine unregelm&auml;&szlig;ig sind (etwa bei einer Blockveranstaltung).");
				?><br /></font>
				<br />&nbsp;<input type="IMAGE" name="open_reg" <? if (!$term_metadata["art"]) print makeButton ("regelmaessig2", "src");  else print makeButton ("regelmaessig", "src") ?> border=0 value="regelmaessig">&nbsp;
				<input type="IMAGE" name="open_ureg"  <? if (!$term_metadata["art"]) print makeButton ("unregelmaessig", "src");  else print makeButton ("unregelmaessig2", "src") ?> border=0 value="unregelmaessig">
			</td>
		</tr>
		<?
		if (!$term_metadata["art"]) {
		?>
					<tr>
						<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
							<br /><font size=-1>&nbsp;<?=("Turnus:") ?> &nbsp; <select name="term_turnus"></font>
							<?
							if ($term_metadata["turnus"]==0)
								echo "<option selected value=0>"._("w&ouml;chentlich")."</option>";
							else
								echo "<option value=0>"._("w&ouml;chentlich")."</option>";
							if ($term_metadata["turnus"]==1)
								echo "<option selected value=1>"._("zweiw&ouml;chentlich")."</option>";
							else
								echo "<option value=1>"._("zweiw&ouml;chentlich")."</option>";
							?>
							</select>
							<br><br><font size=-1>&nbsp;<?=_("Die Veranstaltung findet immer zu diesen Zeiten statt:")?></font><br>
							<?
							if (isSchedule($term_metadata["sem_id"])) {
							?>
							<font size="-1">&nbsp;(<input type="CHECKBOX" name="update_dates" value="1" <?=($term_metadata["update_dates"] ? "checked" : "");?> />&nbsp;<?=_("Ablaufplantermine aktualisieren"); ?>)</font><br>
							<?
							}
							?>
							<br />
							<?
							if (!$term_metadata["turnus_count"])
								{
								if (sizeof($term_metadata["turnus_data"])>0)
									{
									$term_metadata["turnus_count"]=sizeof($term_metadata["turnus_data"]);
									}
								else
									$term_metadata["turnus_count"]=1;
								}

							for ($i=0; $i<$term_metadata["turnus_count"]; $i++)
								{
								if ($i > 0) echo "<br>\n";
								echo '&nbsp;<select name="turnus_day[', $i,']">';
								for($kk = 1; $kk <= 7; $kk++ ){
									echo '<option ', (($kk == $term_metadata['turnus_data'][$i]['day'])? 'selected ':'');
									echo 'value="',$kk,'">';
									switch ($kk){
										case 2: echo _("Dienstag"); break;
										case 3: echo _("Mittwoch"); break;
										case 4: echo _("Donnerstag"); break;
										case 5: echo _("Freitag"); break;
										case 6: echo _("Samstag"); break;
										case 7: echo _("Sonntag"); break;
										case 1:
										default: echo _("Montag");
									}
									echo '</option>';
								}
								echo "</select>\n";
								$ss = (strlen($term_metadata["turnus_data"][$i]["start_stunde"]))? sprintf('%02d',$term_metadata['turnus_data'][$i]['start_stunde']) : '';
								if(strlen($term_metadata['turnus_data'][$i]['start_minute'])) {
									$sm = sprintf('%02d', $term_metadata['turnus_data'][$i]['start_minute'] );
								} elseif (strlen($term_metadata['turnus_data'][$i]['start_stunde'])) {
									$sm = '00';
								} else {
									$sm ='';
								}
								$es = (strlen($term_metadata["turnus_data"][$i]["end_stunde"]))? sprintf('%02d',$term_metadata["turnus_data"][$i]["end_stunde"]) : '';
								if (strlen($term_metadata['turnus_data'][$i]['end_minute'])) {
									$em =  sprintf('%02d', $term_metadata['turnus_data'][$i]['end_minute']);
								} elseif (strlen($term_metadata['turnus_data'][$i]['end_stunde'])) {
									$em = '00';
								} else {
									$em = '';
								}
								echo '&nbsp; <input type="text" name="turnus_start_stunde[',$i,']" size=2 maxlength=2 value="',  $ss, '"> :';
								echo '<input type="text" name="turnus_start_minute[',$i,']" size=2 maxlength=2 value="', $sm, '">', _("Uhr bis");
								echo '&nbsp; <input type="text" name="turnus_end_stunde[',$i,']" size=2 maxlength=2 value="', $es, '"> : ';
								echo '<input type="text" name="turnus_end_minute[',$i,']" size=2 maxlength=2 value="', $em, '">', _("Uhr"), "\n";

								if ($term_metadata["turnus_count"]>1)  {
									echo '&nbsp; <a href="', $PHP_SELF, '?delete_turnus_field=', $i+1,'"><img border=0 src="'.$GLOBALS['ASSETS_URL'].'images/trash.gif" ', tooltip(_("Dieses Feld aus der Auswahl löschen")), '></a>', "\n";
								}
								echo Termin_Eingabe_javascript(3,$i,0,$ss,$sm,$es,$em);
								//Beschreibung
								echo "\n<br>&nbsp;" . _("Beschreibung:") . "&nbsp;";
								echo "\n<select name=\"turnus_desc_chooser[$i]\" ";
								echo "onChange=\"document.Formular.elements['turnus_desc[$i]'].value=document.Formular.elements['turnus_desc_chooser[$i]'].options[Formular.elements['turnus_desc_chooser[$i]'].selectedIndex].value;\" ";
								echo ">";
								echo "\n<option value=\"\">" . _("ausw&auml;hlen oder wie Eingabe") . " --></option>";
								foreach($TERMIN_TYP as $ttyp){
									if ($ttyp['sitzung']) {
										echo "\n<option ";
										if ($term_metadata['turnus_data'][$i]['desc'] == $ttyp['name']) echo "selected";
										echo " value=\"" . htmlReady($ttyp['name']) . "\">" . htmlReady($ttyp['name']) . "</option>";
									}
								}
								echo "\n</select>";
								echo "&nbsp;";
								echo "\n<input type=\"text\" name=\"turnus_desc[$i]\" size=\"30\" value=\"{$term_metadata['turnus_data'][$i]['desc']}\">";

								echo '<br />&nbsp;', _("Raum:"), '&nbsp; ';

								if ($RESOURCES_ENABLE) {
									$resList->reset();
									if ($resList->numberOfRooms()) {
										print "<font size=-1><select name=\"turnus_resource_id[]\"></font>";

										printf ("<option %s value=\"NULL\">[".(($term_metadata["original_resource_id"][$i]) ? _("gebuchter Raum oder ausw&auml;hlen") : _("ausw&auml;hlen oder wie Eingabe")." -->")."]</option>", (!$term_metadata["original_resource_id"][$i]) ? "selected" : "");
										if ($term_metadata["original_resource_id"][$i])
											print "<option value=\"FALSE\">["._("kein gebuchter Raum") ."]</option>";
										while ($res = $resList->next()) {
											printf ("<option value=\"%s\">%s</option>", $res["resource_id"], htmlReady($res["name"]));
										}
										print "</select></font>";
									}
								}

								echo '&nbsp; <font size=-1><input type="text" name="turnus_room[',$i,']" size="30" maxlength="255" value="', htmlReady($term_metadata["turnus_data"][$i]["room"]), '"/></font>&nbsp;', "\n";

								if ($RESOURCES_ENABLE) {
									print "<br />&nbsp;"._("gebuchter Raum:")."&nbsp; ";
									if ($term_metadata["original_resource_id"][$i]) {
										$resObj =& ResourceObject::Factory($term_metadata["original_resource_id"][$i]);
										print "<font size=-1>".$resObj->getFormattedLink(TRUE, TRUE, TRUE)."</font>";
									} else
										print "<font size=-1>"._("kein gebuchter Raum")."</font>";
								}

								print "<br />";
								}
								?>
								<input type="HIDDEN" name="turnus_refresh" value="TRUE">
								<br />
								&nbsp;<input type="IMAGE" name="add_turnus_field" <?=makeButton("feldhinzufuegen", "src") ?> border=0 value="Feld hinzuf&uuml;gen">
								<br />
						</td>
					</tr>
		<?
			}
		else
			{
		?>
					<tr >
						<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
							&nbsp;<font size=-1><?=_("Bitte geben Sie die einzelnen Sitzungstermine unter dem Men&uuml;punkt Ablaufplan ein!")?></font><br><br>
							<input type="HIDDEN" name="term_refresh" value="TRUE">
						</td>
					</tr>
		<?
			}

		if (!$term_metadata["art"])
			{
		?>
					<tr <? $cssSw->switchClass() ?> rowspan=2>
						<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right" rowspan=2>
							&nbsp;
					</td>
						<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align="left">
							&nbsp;<font size=-1><b><?=_("Veranstaltungsbeginn")?></b></font><br /><br />
							<font size=-1>&nbsp;<?=_("Bei einer regelm&auml;&szlig;igen Veranstaltung k&ouml;nnen Sie den ersten Termin entweder selbst eingeben oder automatisch berechnen lassen.") ?></font><br />
							<br />&nbsp;<input type="IMAGE" name="nenter_start_termin" <? if ($term_metadata["start_woche"] != -1) print makeButton ("automatisch2", "src");  else print makeButton ("automatisch", "src") ?> border=0 value="automatisch">&nbsp;
							<input type="IMAGE" name="enter_start_termin" <? if ($term_metadata["start_woche"] != -1) print makeButton ("eingeben", "src");  else print makeButton ("eingeben2", "src") ?> border=0 value="eingeben">
						</td>
					</tr>
		<?
			if ($term_metadata["start_woche"] !=-1)
				{
		?>
					<tr>
						<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
							<br />&nbsp;<font size=-1><?=_("Veranstaltungsbeginn in der")?> <select name="term_start_woche">
						<?
							if ($term_metadata["start_woche"]==0)
								echo "<option selected value=0>"._("1. Semesterwoche")."</option>";
							else
								echo "<option value=0>"._("1. Semesterwoche")."</option>";
							if ($term_metadata["start_woche"]==1)
								echo "<option selected value=1>"._("2. Semesterwoche")."</option>";
							else
								echo "<option value=1>"._("2. Semesterwoche")."</option>";
							?>
							</select></font>
						</td>
					</tr>
		<?
				}
			else
				{
		?>
					<tr>
						<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
							<br /><font size=-1>&nbsp;<?=_("Bitte geben Sie hier das Datum des ersten Termins ein:")?></font><br>
							&nbsp;<input type="text" name="tag" size=2 maxlength=2 value="<? if ($term_metadata["start_termin"]<>-1) echo date("d",$term_metadata["start_termin"]); else echo _("tt") ?>">.
							<input type="text" name="monat" size=2 maxlength=2 value="<? if ($term_metadata["start_termin"]<>-1) echo date("m",$term_metadata["start_termin"]); else echo _("mm") ?>">.
							<input type="text" name="jahr" size=4 maxlength=4 value="<? if ($term_metadata["start_termin"]<>-1) echo date("Y",$term_metadata["start_termin"]); else echo _("jjjj") ?>">&nbsp;
							<? echo Termin_Eingabe_javascript(7); ?>
						</td>
					</tr>
		<?
			}
			}
		?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="4%" rowspan=2>
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
							<b><font size=-1>&nbsp;<?=_("weitere Daten")?></font></b>
						</td>
					</tr>
					<tr>
						<td class="<? echo $cssSw->getClass() ?>" width="4%">
							&nbsp;<font size=-1><?=_("Semester")?></font>
							<?
							echo "&nbsp;<select name=\"sem_start_time\"";
							echo " >";
							$all_semester = $semester->getAllSemesterData();
							for ($i=0; $i<sizeof($all_semester); $i++)
								{
								if ($term_metadata["sem_start_time"] ==$all_semester[$i]["beginn"])
									echo "<option value=".$all_semester[$i]["beginn"]." selected>", $all_semester[$i]["name"], "</option>";
								else
									echo "<option value=".$all_semester[$i]["beginn"].">", $all_semester[$i]["name"], "</option>";
								}
							echo "</select>";
							?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="96%" valign="bottom">
							&nbsp;<font size=-1><?=_("Dauer")?><br /></font>
							&nbsp;<select name="sem_duration_time">
							<?
								if ($term_metadata["sem_duration_time"] == 0)
									echo "<option value=0 selected>"._("1 Semester")."</option>";
								else
									echo "<option value=0>"._("1 Semester")."</option>";
								for ($i=0; $i<sizeof($all_semester); $i++)
									{
									if (($term_metadata["sem_start_time"] + $term_metadata["sem_duration_time"]) == $all_semester[$i]["beginn"])
										{
										if ((!$term_metadata["sem_duration_time"] == 0) && (!$term_metadata["sem_duration_time"] == 0))
											echo "<option value=",$all_semester[$i]["beginn"], " selected>"._("bis")." ", $all_semester[$i]["name"], "</option>";
										else
											echo "<option value=",$all_semester[$i]["beginn"], ">"._("bis")." ", $all_semester[$i]["name"], "</option>";
										}
									else
										echo "<option value=",$all_semester[$i]["beginn"], ">"._("bis")." ", $all_semester[$i]["name"], "</option>";
									}
								if ($term_metadata["sem_duration_time"] == -1)
									echo "<option value=-1 selected>"._("unbegrenzt")."</option>";
								else
									echo "<option value=-1>"._("unbegrenzt")."</option>";
							?>
							</select>
						</td>
					</tr>
		</td>
	</tr>
	<tr <? $cssSw->switchClass() ?>>
		<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
			<input type="IMAGE" name="uebernehmen" <?=makeButton("uebernehmen", "src") ?> border=0 value="uebernehmen">
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=3>
			&nbsp;
		</td>
	</tr>
	</form>
<?php
	}


echo '	</table>
</td>
</tr>
</table>';

include ('include/html_end.inc.php');
page_close();
?>