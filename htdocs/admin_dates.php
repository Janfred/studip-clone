<?php
/*
admin_dates.php - Terminverwaltung von Stud.IP
Copyright (C) 2000 André Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("tutor");
	
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); 
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/forum.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$RELATIVE_PATH_CALENDAR/calendar_func.inc.php");


$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;	
$db4=new DB_Seminar;	

//Defaults, die fuer DAUS (<admin) gesetzt werden
$default_description="Bitte geben Sie hier nur optionale Angaben (genauere Terminbeschreibung, Referatsthemen usw.) ein.";
$default_titel="Kurztitel, bitte ausfüllen";
if ((!$perm->have_perm ("admin")) && (!$perm->have_perm ("root"))) {
	$temp_default[1]="tt";
	$temp_default[2]="mm";
	$temp_default[3]="jjjj";
	$temp_default[4]="hh";
	$temp_default[5]="mm";
	$temp_default[6]="hh";
	$temp_default[7]="mm";
}

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

$sess->register("term_data");
$sess->register("admin_dates_data");

if ($RESOURCES_ENABLE) {
	require_once ($RELATIVE_PATH_RESOURCES."/resourcesClass.inc.php");
	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	require_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	$resList = new ResourcesUserRoomsList($user_id);
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");


if ($SessSemName[1])
	$admin_dates_data["range_id"]=$SessSemName[1]; 
elseif ($range_id)
	$admin_dates_data["range_id"]=$range_id; 

if (!$admin_dates_data["range_id"]) {
	echo "</tr></td></table>";
	die;
}

//Einpflegen neu angekommender Daten/Schalter
if ($assi) 
	$admin_dates_data["assi"]=$assi;
if ($show_id) 
	$admin_dates_data["show_id"]=$show_id;
if ($show_all) 
	$admin_dates_data["show_all"]=TRUE;
if ($show_nall) 
	$admin_dates_data["show_all"]=FALSE;
	
if (($edit_x) && (!$admin_dates_data["show_all"]))
	$admin_dates_data["show_id"]='';

//Content of the Infobox
$infobox = array(
		array  ("kategorie"  => "Information:", 
			"eintrag" => array (
					array ("icon" => "pictures/ausruf_small.gif", 	
						"text"  => ($admin_dates_data["assi"]) ? _("Sie k&ouml;nnen nun den Ablaufplan und weitere Termine f&uuml;r die neu angelegte Veranstaltung eingeben.") : _("Sie k&ouml;nnen hier den Ablaufplan und weitere Termine der Veranstaltung ver&auml;ndern.")))),
		array  ("kategorie" => "Aktionen:", 
				"eintrag" => array (
					array	("icon" => "pictures/meinetermine.gif",
						"text"  => sprintf(_("Um die allgemeinen Zeiten der Veranstaltung zu &auml;ndern, nutzen Sie bitte den Men&uuml;punkt %s Zeiten %s"), "<a href=\"admin_metadates.php?seminar_id=".$admin_dates_data["range_id"]."\">", "</a>")))));



if ($insert_new) {
	$hash_secret = "blubbelsupp";
	$t_id=md5(uniqid($hash_secret));   //termin_id erzeugen
	//Insert Modus AN
	$admin_dates_data["insert_id"]=$t_id;
	}
else
	$admin_dates_data["insert_id"]=FALSE;

//maximale spaltenzahl berechnen
if ($auth->auth["jscript"]) 
	$max_col = round($auth->auth["xres"] / 10 );
else 
	$max_col =  64 ; //default für 640x480
	
if ($admin_dates_data["range_id"] && !$perm->have_perm("root"))
	{
	//Sicherheitscheck
	$range_perm=get_perm($admin_dates_data["range_id"]);
	if ($range_perm!="admin" && $range_perm!="dozent" && $range_perm!="tutor") die;
	}

//Bevor einen Termin loeschen uebernehmen wir Aenderungen, dann ist gleichzeitiges Aendern und Loeschen moeglich
if ($kill_x)
	$edit_x=TRUE;

//Assistent zum automatischen generieren eines Ablaufplans
if ($make_dates_x) {
	//Initialisierungen
	$hash_secret = "blubbelsupp";
	$date_typ=1; //hier setzen wir den Typ, den automatisch generierte Termine haben sollen. "1" steht fuer Sitzungstermine.
	$orig_stunde=date("G",$sstamp);
	$tmp = $auth->auth["uname"];
	$db2->query("SELECT start_time, duration_time FROM seminare WHERE Seminar_id = '".$admin_dates_data["range_id"]."'");
	$db2->next_record();
	$author = get_fullname();

	//Semesterbeginn herausfinden
	foreach ($SEMESTER as $sem)
		if (($db2->f("start_time") >= $sem["beginn"]) AND ($db2->f("start_time") <= $sem["ende"]))
			{
			$vorles_beginn=$sem["vorles_beginn"];
			$vorles_ende=$sem["vorles_ende"];
			}

	//andere Endzeit bei Veranstaltungen ueber mehrere Semester
	if ($full)
		foreach ($SEMESTER as $sem)
			if  ((($db2->f("start_time") + $db2->f("duration_time")) >= $sem["beginn"]) AND (($db2->f("start_time") + $db2->f("duration_time")) <= $sem["ende"]))
				$vorles_ende=$sem["vorles_ende"];

	if ($term_data["start_woche"]>=0)
		$semester_woche=$term_data["start_woche"];
	$made_dates=0;
	
	do
		{	
		for ($i=0; $i<sizeof($term_data["turnus_data"]); $i++) {
			//check auf diverse HOLIDAY, gesetzt in der config.inc.php. Sollte man aber nur noch fuer Uni-Ferien benutzen.
			for ($k=0; $k<=sizeof($HOLIDAY); $k++)
				if (($HOLIDAY[$k]["beginn"] <= $insert_termin_start[$i]) && ($insert_termin_start[$i]<=$HOLIDAY[$k]["ende"]))
					$insert_termin_start[$i]=FALSE;
			
			//check auf diverse Feiertage 2.0 (nutzt Function des Terminkalenders und muss nicht in der config.inc.php angepasst werden)
			if ($insert_termin_start[$i]) {
				$holy_type=holiday($insert_termin_start[$i]);
				if ($holy_type==3)
					$insert_termin_start[$i]=FALSE;
				}
			
			//check auf Semesterferien (bei Anlegen ueber mehrere Semester)
			if ($full)
				for ($k=0; $k<=sizeof($HOLIDAY); $k++)
					if (($SEMESTER[$k]["vorles_ende"] <= $insert_termin_start[$i]) && ($insert_termin_start[$i]<=$SEMESTER[$k+1]["vorles_beginn"]))
					$insert_termin_start[$i]=FALSE;
			
			//check auf anderen Starttermin (wenn gesetzt blenden wir alles unnoetige weg)
			if ($term_data["start_termin"]>0)
				if ($term_data["start_termin"] >$insert_termin_start[$i]) {
					$insert_termin_start[$i]=FALSE;
					$supress_turnus=TRUE;
					}
			
			//Sommer oder Winterzeit abfangen
			if ($insert_termin_start[$i])
				if (date("G",$insert_termin_start[$i]) != $term_data["turnus_data"][$i]["start_stunde"]) 
					$insert_termin_start[$i]=mktime($term_data["turnus_data"][$i]["start_stunde"],date("i",$insert_termin_start[$i]),0,date("n",$insert_termin_start[$i]),date("j",$insert_termin_start[$i]),date("Y",$insert_termin_start[$i]));
			if ($insert_termin_end[$i])
				if (date("G",$insert_termin_end[$i]) != $term_data["turnus_data"][$i]["end_stunde"]) 
					$insert_termin_end[$i]=mktime($term_data["turnus_data"][$i]["end_stunde"],date("i",$insert_termin_end[$i]),0,date("n",$insert_termin_end[$i]),date("j",$insert_termin_end[$i]),date("Y",$insert_termin_end[$i]));
				
			//Alles moegliche schreiben
			if ($insert_termin_start[$i]) {
				$t_id=md5(uniqid($hash_secret));   //termin_id erzeugen
				$f_id=md5(uniqid($hash_secret)); 	//folder_id erzeiugen
				$aktuell=time();
				
				//if we have a resource_id, we take the room name from resource_id
				if (($term_data["turnus_data"][$i]["resource_id"]) && ($RESOURCES_ENABLE))
					$raum=getResourceObjectName($term_data["turnus_data"][$i]["resource_id"]);
				
				if ($pfad)  //Forumseintrag erzeugen
					$topic_id=CreateTopic($TERMIN_TYP[$date_typ]["name"]." am ".date("d.m.Y", $insert_termin_start[$i]), $author, "Hier kann zu diesem Termin diskutiert werden", 0, 0, $admin_dates_data["range_id"]);
				if ($folder) { //Dateiordner erzeugen
					//Titel basteln
					$titel_f=$TERMIN_TYP[1]["name"].": Kein Titel";
					$titel_f.=" am ".date("d.m.Y ", $insert_termin_start[$i]);
					$description="Ablage für Ordner und Dokumente zu diesem Termin";
					$db3->query("INSERT INTO folder SET folder_id='$f_id', range_id='$t_id', description='$description', user_id='$user->id', name='$titel_f', mkdate='$aktuell', chdate='$aktuell'");
					}
				else
					$f_id='';
				$db4->query("INSERT INTO termine SET termin_id='$t_id', range_id='".$admin_dates_data["range_id"]."', autor_id='$user->id', content='Kein Titel', date='$insert_termin_start[$i]', mkdate='$aktuell', chdate='$aktuell', date_typ='$date_typ', topic_id='$topic_id', end_time='$insert_termin_end[$i]', raum='$raum' ");
				if ($db4->affected_rows()) {
					//insert a entry for the linked resource, if resource management activ
					if ($RESOURCES_ENABLE) {
						$insertAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
						$insertAssign->dont_check = TRUE;
						$insertAssign->insertDateAssign($t_id, $term_data["turnus_data"][$i]["resource_id"]);
					}
					$made_dates++;
				}
			}
		}

		//frische Daten erzeugen
		for ($i=0; $i<sizeof($term_data["turnus_data"]); $i++) {
			$insert_termin_start[$i]=$vorles_beginn+(($term_data["turnus_data"][$i]["day"]-1)*24*60*60)+($term_data["turnus_data"][$i]["start_stunde"]*60*60)+($term_data["turnus_data"][$i]["start_minute"]*60) + ($semester_woche * 7 * 24 * 60 *60);
			$insert_termin_end[$i]=$vorles_beginn+(($term_data["turnus_data"][$i]["day"]-1)*24*60*60)+($term_data["turnus_data"][$i]["end_stunde"]*60*60)+($term_data["turnus_data"][$i]["end_minute"]*60) + ($semester_woche * 7 * 24 * 60 *60);
		}
		
		//Woche erhoehen
		if (($term_data["turnus"]==1) && (!$supress_turnus))
			$semester_woche=$semester_woche+2;
		else
			$semester_woche++;
		
		unset ($supress_turnus);
		
		}
		
	while ($insert_termin_start[0] <=$vorles_ende);

	//make an update, this will kill old metadate entries in the resources
	if ($RESOURCES_ENABLE)
		$resources_result=$insertAssign->updateAssign();

	$result="msg§Der Ablaufplan wurde erstellt. Es wurden ".$made_dates." Termine erstellt.§";
	}

if ($new)
	{
	$do=TRUE;
	if (!checkdate($monat,$tag,$jahr))
		{
		$do=FALSE;
		$result="error§Bitte geben Sie ein g&uuml;ltiges Datum ein!§";
		}

	if ($do)		
		if ((!$stunde) && (!end_stunde))
			{
			$do=FALSE;	
			$result.="error§Bitte geben Sie eine g&uuml;eltige Start- und Endzeit an!§";
			}
		
	$start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
	$end_time = mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr);
	
	if ($do)		
		if ($start_time > $end_time)
			{
			$do=FALSE;	
			$result.="error§Der Endzeitpunkt muss nach dem Startzeitpunkt liegen!§";
			}
	
	//Check auf Konsistenz mt Metadaten, Semestercheck
	if (($do) && ($art==1) && (is_array($term_data ["turnus_data"]))) {
		
		foreach ($SEMESTER as $a) {
			if (($term_data["start_time"] >= $a["beginn"]) && ($term_data["start_time"] <= $a["ende"]))  {
				$sem_beginn=$a["beginn"];
				$sem_ende=$a["ende"];
				}
			if (($term_data["duration_time"] > 0) && ((($term_data["start_time"] + $term_data["duration_time"]) >= $a["beginn"]) && (($term_data["start_time"] + $term_data["duration_time"]) < $a["ende"])))
				$sem_ende=$a["ende"];
			}
			
		if (($start_time < $sem_beginn) || ($start_time > $sem_ende))
			$result.="info§Der eingegebene Termine liegt ausserhalb des Semesters, in dem die Veranstaltung stattfindet. Es wird empfohlen, den Termin anzupassen.§";
		
		//Und dann noch auf regelmaessige Termine checken, wenn dieser Typ gewaehlt ist
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
				$result.="info§Der eingegebene Termin findet nicht zu allgemeinen Veranstaltungszeiten statt. Es wird empfohlen, Sitzungstermine von regelm&auml;&szlig;igen Veranstaltungen nur zu den allgemeinen Zeiten stattfinden zu lassen.§";
			}
		}
	
	if ($do)		
		{
		$hash_secret = "blubbelsupp";
		$t_id=$termin_id;
		$f_id=md5(uniqid($hash_secret));   //folder_id erzeugen		
		$aktuell=time();

		$tmp = $auth->auth["uname"];
		$author=get_fullname();

		if ($titel==$default_titel)
			$tmp_titel="Kein Titel";
		else
			$tmp_titel=$titel;
		
		if ($description==$default_description)
			$description='';
			
		//if we have a resource_id, we take the room name from resource_id
		if (($resource_id) && ($RESOURCES_ENABLE))
			$raum=getResourceObjectName($resource_id);

		if ($topic)  //Forumseintrag erzeugen
			$topic_id=CreateTopic($TERMIN_TYP[$art]["name"].": ".$tmp_titel." am ".date("d.m.Y ", $start_time), $author, "Hier kann zu diesem Termin diskutiert werden", 0, 0, $admin_dates_data["range_id"]);
		if ($folder) { //Dateiordner erzeugen
			$titel_f=$TERMIN_TYP[$art]["name"].": $tmp_titel";
			$titel_f.=" am ".date("d.m.Y ", $start_time);
			$titel_f=$titel_f;
			$description_f="Ablage für Ordner und Dokumente zu diesem Termin";		
			$db3->query("INSERT INTO folder SET folder_id='$f_id', range_id='$t_id', description='$description_f', user_id='$user->id', name='$titel_f', mkdate='$aktuell', chdate='$aktuell'");
			}
		else
			$f_id='';
		$tmp_titel=$tmp_titel;
		$description=$description;
		$db->query("INSERT INTO termine SET termin_id='$t_id', range_id='".$admin_dates_data["range_id"]."', autor_id='$user->id', content='$tmp_titel', date='$start_time', mkdate='$aktuell', chdate='$aktuell', date_typ='$art', topic_id='$topic_id', end_time='$end_time', raum='$raum', description='$description'");

		if ($db->affected_rows()) {
			//insert a entry for the linked resource, if resource management activ
			if ($RESOURCES_ENABLE) {
				$insertAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
				$resources_result = $insertAssign->insertDateAssign($t_id, $resource_id);
				$insertAssign->updateAssign($t_id, $resource_id);
			}
		
			$result.="msg§Ihr Termin wurde eingef&uuml;gt!§";
			$admin_dates_data["termin_id"]=FALSE;
			}
		}
	}

if (($edit_x) && (!$admin_dates_data["termin_id"])) {
	if (is_array($termin_id)) {
		for ($i=0; $i < sizeof($termin_id); $i++) {
		 	$t_id=$termin_id[$i];
			$f_id=md5(uniqid($hash_secret));
			
			if ($resource_id[$i] == "FALSE")
				$resource_id[$i] = FALSE;
				
			$tmp_result=edit_dates($stunde[$i],$minute[$i],$monat[$i], $tag[$i], $jahr[$i], $end_stunde[$i], $end_minute[$i], $t_id, $art[$i], $titel[$i],$description[$i], $topic_id[$i],$raum[$i], $resource_id[$i], $admin_dates_data["range_id"]);
		 	$result.=$tmp_result["msg"];

		 	$resources_result = array_merge ($resources_result, $tmp_result["resources_result"]);

			$aktuell=time();

			$tmp = $auth->auth["uname"];
			$author=get_fullname();
			
			if ($tag[$i]<10)
				$tag[$i]="0".$tag[$i];
			if ($monat[$i]<10)
				$monat[$i]="0".$monat[$i];
			$tmp_datum=$tag[$i].".".$monat[$i].".".$jahr[$i];

			
 			if ($titel[$i]==$default_titel)
				$tmp_titel="Kein Titel";
			else
				$tmp_titel=$titel[$i];

		 	//nachtraegliches Anlegen von Ordner vornehmen
		 	if ($insert_topic[$i])  {
				$tmp_topic_id=CreateTopic($TERMIN_TYP[$art[$i]]["name"].": ".$tmp_titel." am $tmp_datum", $author, "Hier kann zu diesem Termin diskutiert werden", 0, 0, $admin_dates_data["range_id"]);
				$db3->query ("UPDATE termine SET topic_id = '$tmp_topic_id' WHERE termin_id = '$t_id'");
				}
			else $tmp_topic_id='';
			if ($insert_folder[$i]) { 
				$titel_f=$TERMIN_TYP[$art[$i]]["name"].": $tmp_titel";
				$titel_f.=" am $tmp_datum";
				$titel_f=$titel_f;
				$description_f="Ablage für Ordner und Dokumente zu diesem Termin";		
				$db3->query("INSERT INTO folder SET folder_id='$f_id', range_id='$t_id', description='$description_f', user_id='$user->id', name='$titel_f', mkdate='$aktuell', chdate='$aktuell'");
				}
		 	
		 	if (!$add_result) //Hinweisnachrichten nur einmal anzeigen
		 		$add_result=$tmp_result["add_msg"];
		 	}
		 $result.=$add_result;
		}

		//after every change, we have to do this check (and we create the msgs...)
		if ($RESOURCES_ENABLE) {
			$updateAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
			$updateAssign->updateAssign();
		}
	}

if (($kill_x) && ($admin_dates_data["range_id"])) {
	if (is_array($kill_termin)) {
		for ($i=0; $i < count($kill_termin); $i++) {
			$teile = explode("&",$kill_termin[$i]);
	 		$del_count=$del_count+delete_date($teile[0], $teile[1], TRUE, $admin_dates_data["range_id"]);
		}
		$del_count=count($kill_termin);
	}

	//after every change, we have to do this check (and we create the msgs...)
	if ($RESOURCES_ENABLE) {
		$updateAssign = new VeranstaltungResourcesAssign($admin_dates_data["range_id"]);
		$updateAssign->updateAssign();
	}
	
	if ($del_count)
		if ($del_count ==1)
			$result="msg§$del_count Termin wurde gel&ouml;scht!";
		else
			$result="msg§$del_count Termine wurden gel&ouml;scht!";
	$beschreibung='';
	}

	//If resource-management activ, update the assigned reources and do the overlap checks.... not so easy!
	if (($RESOURCES_ENABLE) && ($resources_result)) {
		$overlaps_detected=FALSE;
		foreach ($resources_result as $key=>$val)
			if ($val["overlap_assigns"] == TRUE)
				$overlaps_detected[$val["resource_id"]] = $val["overlap_assigns"];

		//create bad msg
		if ($overlaps_detected) {
			$result.="error§"._("Folgende gew&uuml;nschte Raumbelegungen &uuml;berschneiden sich mit bereits vorhandenen Belegungen. Bitte &auml;ndern Sie die R&auml;ume oder Zeiten!");
			$i=0;
			foreach ($overlaps_detected as $key=>$val) {
				$result.="<br /><font size=\"-1\" color=\"black\">".htmlReady(getResourceObjectName($key)).": ";
				//show the first overlap
				list(, $val2) = each($val);
				$result.=date("d.m, H:i",$val2["begin"])." - ".date("H:i",$val2["end"]);
				if (sizeof($val) >1)
					$result.=", ... ("._("und weitere").")";
				$result.=sprintf (", <a target=\"new\" href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav&start_time=%s\">"._("Raumplan anzeigen")."</a> ",$key, $val2["begin"]);
				$i++;
			}
			$result.="</font>§";
		}
		//create good msg
		$i=0;
		foreach ($resources_result as $key=>$val)
			if (!is_array($val["overlap_assigns"]))
				$rooms_id[$val["resource_id"]]=TRUE;
			
		if (is_array($rooms_id))
			foreach ($rooms_id as $key=>$val) {
				if ($i)
					$rooms_booked.=", ";
				$rooms_booked.=sprintf ("<a target=\"new\" href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav\">%s</a>", $key, htmlReady(getResourceObjectName($key)));
				$i++;
			}
		
		if ($i == 1)
			$result.= sprintf ("msg§"._("Die Belegung des Raums %s wurde in die Ressourcenverwaltung eingetragen.")."§", $rooms_booked);
		elseif ($i)
			$result.= sprintf ("msg§"._("Die Belegung der R&auml;ume %s wurden in die Ressourcenverwaltung eingetragen.")."§", $rooms_booked);
	  }
	

//Ab hier Ausgaben....

	//Bereich wurde ausgewaehlt (aus linksadmin) oder wir kommen aus dem Seminar Assistenten
	$db->query("SELECT metadata_dates, Name, start_time, duration_time, status, Seminar_id FROM seminare WHERE Seminar_id = '".$admin_dates_data["range_id"]."'");
	$db->next_record();
	if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME) 	
		$tmp_typ = "Veranstaltung"; 
	else
		$tmp_typ = $SEM_TYPE[$db->f("status")]["name"];
	
	  
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic">&nbsp; 
		<b>
	 <?
	 if ($admin_dates_data["assi"]) {
	  	echo "Schritt 7: Ablaufplan und Termine der Veranstaltung: ",htmlReady(substr($db->f("Name"), 0, 40));
		if (strlen($db->f("Name")) > 40)
			echo "... ";
	} else
		echo  getHeaderLine($db->f("Seminar_id"))." - Ablaufplan und Termine";
	?>
		</b>
		</td>
	</tr>
	<tr>
		<td class="blank">
		&nbsp; 
 		</td>
 	</tr>
 	<tr>
		<td class="blank">
			<table width="100%" border=0 cellpadding=0 cellspacing=0>
				<tr>
					<td valign="top">
						<table width="100%" border=0 cellpadding=0 cellspacing=0>
							<?
							if ($result) {
								parse_msg($result);
								print "<a href=\"anchor\"></a>";
							}
							?>
							<tr>
								<td>
									<blockquote>
	<?
	

	//Anzeige, wenn wir aus dem Seminarassistenten kommen
	$term_data=unserialize($db->f("metadata_dates"));
	$term_data["start_time"]=$db->f("start_time");
	$term_data["duration_time"]=$db->f("duration_time");

	if ($term_data["art"] ==1) {
		?>
		<font size=-1><b>Typ: </b>unregelm&auml;&szlig;ige Veranstaltung
		<?
		if (get_semester($admin_dates_data["range_id"])) {
			echo "<br /><b>Semester:</b> ", get_semester($admin_dates_data["range_id"]);
		}
		?>
		<br>
		<?
	} else {
		?>
		<font size=-1><b>Typ: </b>regelm&auml;&szlig;ige Veranstaltung
		<?
		if (view_turnus($admin_dates_data["range_id"]))
			echo " (", trim(view_turnus($admin_dates_data["range_id"])),")";			
		if (veranstaltung_beginn($admin_dates_data["range_id"]))
			echo "<br><b>Erster Termin:</b> ", veranstaltung_beginn($admin_dates_data["range_id"]);
		if (get_semester($admin_dates_data["range_id"]))
			echo "<br /><b>Semester:</b> ", get_semester($admin_dates_data["range_id"]);
		?>
		</font><br /><br />
		<?
	}
	if ($admin_dates_data["assi"]) {
		?>
		<font size="-1">Sie haben jederzeit die M&ouml;glichkeit, diesen Schritt des Veranstaltungs-Assistenten sp&auml;ter nachzuholen.</font><br>
		<?
	}
		?>
		<form method="POST" action="<? echo $PHP_SELF?>">
		<font size=-1>Einen neuen Termin <a href="admin_dates.php?insert_new=TRUE#anchor"><img <?=makeButton("anlegen", "src")?> align="absmiddle" border=0 valign="middle" alt="Neuen Termin anlegen"></a><br><?

		$db2->query("SELECT count(*) AS anzahl FROM termine WHERE range_id='".$admin_dates_data["range_id"]."' AND date_typ IN $typ_clause");
		$db2->next_record();

	//Fenster zum Starten des Terminassistenten einblenden
	if ((!$term_data["art"]) && (!$db2->f("anzahl"))) {
		if (sizeof($term_data["turnus_data"])) { //Ablaufplanassistent nur wenn allgemeine Zeiten vorhanden moeglich
		?>
		<br />
		<table border="0" cellpadding="6" cellspacing="0" width="80%">
		<tr>
			<td class="rahmen_steel">
				<font size="-1"><b>Ablaufplanassistent</b><br /><br /></font>
				<font size="-1">generieren Sie automatisch Sitzungstermine mit folgenden Einstellungen:<br /></font>
				&nbsp; &nbsp; <font size=-1><input type="checkbox" name="pfad"> Zu jedem Termin einen Themenordner im Forum der Veranstaltung anlegen.</font><br>
				&nbsp; &nbsp; <font size=-1><input type="checkbox" name="folder"> Zu jedem Termin einen Dateiordner zum Upload anlegen. </font>
				<? if ($db->f("duration_time") >0) {
					?>
					<br />&nbsp; &nbsp; <font size=-1><input type="checkbox" name="full"> Ablaufplan f&uuml;r alle Semester anlegen (wenn nicht gesetzt nur f&uuml;r das erste Semester) </font>
					<?
					}
					?>
					<img  src="./pictures/info.gif" 
						onClick="alert('Der Ablaufplanassistent erstellt automatisch alle Termine des ersten oder aller Semester, je nach Auswahl. Dabei werden  - soweit wie möglich  - Feiertage und Ferienzeiten übersprungen. Anschliessend können Sie jedem Termin einen Titel und eine Beschreibung geben.');" 
						alt="Der Ablaufplanassistent erstellt automatisch alle Termine des ersten oder aller Semester, je nach Auswahl. Dabei werden soweit wir m&ouml;glich Feiertage und Ferienzeiten &uuml;bersprungen. Anschliessend k&ouml;nnen Sie jedem Termin einen Titel und eine Beschreibung geben.">
					<br /><br />
					<font size=-1>Assistent <input type="IMAGE" align ="absmiddle" name="make_dates" <?=makeButton("starten", "src")?> border="0" value="Ablaufplanassistenten ausf&uuml;hren >> "></font><br>
				</td>
			</tr>
		</table>
		<?
		} else {
		?>
		<br /><br />
		<font size="-1">Sie haben bislang noch keine Sitzungstermine eingegeben.  Sie k&ouml;nnen an dieser Stelle den Ablaufplanassisten benutzen, wenn Sie f&uuml;r die Veranstaltung einen regelm&auml;&szlig;igen Turnus festlegen.
		<?
		}
	} ?>
									</form>
									</blockquote>
									</td>
								</tr>
							</td>
						</tr>
					</table>
				</td>
			<?
			if ($infobox) {
			?>
				<td class="blank" width="270" align="right" valign="top">
				<? print_infobox ($infobox, ($admin_dates_data["assi"]) ? "pictures/hands07.jpg" : "pictures/schedules.jpg"); ?>
				<br />
			</td>
			<?
			}
			?>
		</tr>
	</table>
	<?
	
	 //Vorhandene Termine holen und anzeigen und nach Bedarf bearbeiten
	 
	 $db->query("SELECT * FROM termine WHERE range_id='".$admin_dates_data["range_id"]."' ORDER BY date");
	 if ($db->num_rows() || $admin_dates_data["insert_id"])
		{
		?>
		<table border="0" cellpadding="2" cellspacing="0" width="99%" align="center">
		<tr align="left" height="22">
			<td width="82%" class="steelgraulight" align="center">
			<?
			if (!$show_all)
				{
				?>
				<a href="<? echo $PHP_SELF, "?range_id=".$admin_dates_data["range_id"]."&show_all=TRUE"; ?>"><img src="pictures/forumgraurunt.gif" alt="Alle Termine aufklappen" border=0></a>
				<?
				}
			else
				{
				?>
				<a href="<? echo $PHP_SELF, "?range_id=".$admin_dates_data["range_id"]."&show_nall=TRUE"; ?>"><img src="pictures/forumgraurauf.gif" alt="Alle Termine zuklappen"border=0></a>
				<?
				}
				?>
			</td>
		<form method="POST" action="<? echo $PHP_SELF; ?>#anchor">

			<?
		if (!$admin_dates_data["insert_id"]) {
			?>
		<td class="steelgraulight" align="right" nowrap>
			<?
				if (!$show_all) {
				?>
				<input type="IMAGE" name="mark_all" border=0 src="pictures/buttons/alleauswaehlen-button.gif" value="löschen">&nbsp;
				<input type="IMAGE" name="kill" border=0 src="pictures/buttons/loeschen-button.gif" value="löschen">&nbsp; 
				<?
				}
			}
		if ($show_all) {
			?>
			<input type="IMAGE" name="edit" border=0 src="pictures/buttons/termineaendern-button.gif" value="verändern">&nbsp; &nbsp; 
			<?
			}
			?>
			<input type="HIDDEN" name="show_id" value="<? echo $db->f("termin_id");?>">
			<input type="HIDDEN" name="show_id" value="<? echo $show_id ?>">
			<input type="HIDDEN" name="show_all" value="<? echo $show_all ?>">
		</td>
		</tr>
	</table>
	<table border="0" cellpadding="0" cellspacing="2" width="99%" align="center">
	<?	
	
	//Wenn insert gesetzt, neuen Anlegen...
	if ($admin_dates_data["insert_id"]) {
				
		//Titel erstellen
		$titel='';
		$titel.="&nbsp;<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"tag\" maxlength=2 size=2 value=\"".$temp_default[1]."\"><font size=-1>.</font>";
		$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"monat\" maxlength=2 size=2 value=\"".$temp_default[2]."\"><font size=-1>.</font>";
		$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"jahr\" maxlength=4 size=4  value=\"".$temp_default[3]."\"><font size=-1>&nbsp;von&nbsp;</font>";
		$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"stunde\" maxlength=2 size=2 value=\"".$temp_default[4]."\"><font size=-1> :</font>";
		$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"minute\" maxlength=2 size=2 value=\"".$temp_default[5]."\"><font size=-1>&nbsp;bis&nbsp;</font>";
		$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"end_stunde\" maxlength=2 size=2 value=\"".$temp_default[6]."\"><font size=-1> :</font>";
		$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"end_minute\" maxlength=2 size=2 value=\"".$temp_default[7]."\"><font size=-1> Uhr.</font>";
		$titel.="<input type=\"HIDDEN\" name=\"termin_id\" value=\"".$admin_dates_data["insert_id"]."\">";
		
		$icon="&nbsp;<img src=\"./pictures/termin-icon.gif\" border=0>";
		$link=$PHP_SELF."?cancel=TRUE";

		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		printhead(0, 0, $link, "open", TRUE, $icon, $titel, $zusatz);
		echo "</tr></table>	";
		
		//Contentbereich
		$content='';
		
		$content.="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"left\" width=\"100%\"><tr>\n";
		$content.="<td class=\"steel1\" width=\"80%\" valign=\"top\">\n";
	    	$content.="<input type=\"HIDDEN\" name=\"new\" value=\"TRUE\">";		
		$content.="<font size=-1>Titel:</font><br /><input type=\"TEXT\" name=\"titel\" maxlength=255 size=".round($max_col*0.45)." value=\"";
		if (!$perm->have_perm ("admin"))
			$titel.=$default_titel;
	    	$content.="\"><br />";
		$content.="<font size=-1>Beschreibung:<br></font><textarea style=\"width:98%\" cols=\"". round($max_col*0.45)."\" rows=4 name=\"description\"  wrap=\"virtual\">";
		if (!$perm->have_perm ("admin"))
			$content.=$default_description;
		$content.="</textarea>\n</div>";
		$content.="</td>\n";
		$content.="<td class=\"steel1\" width=\"20%\">\n";
		$content.="<font size=-1>&nbsp;Raum:</font>";
		if ($RESOURCES_ENABLE) {
			$resList -> reset();
			if ($resList->numberOfEvents()) {
				$content.= "<br /><font size=-1>&nbsp;<select name=\"resource_id\"></font>";
				$content.= ("<option value=\"FALSE\">[eingeben oder aus Liste]</option>");												
				while ($resObject = $resList->nextEvent())
					$content.= sprintf("<option value=\"%s\">%s</option>", $resObject->getId(), htmlReady($resObject->getName()));
				$content.= "</select></font>";
			}
		}
		$content.="<br />&nbsp;<input type=\"TEXT\" name=\"raum\" maxlength=255 size=20 value=\"".htmlReady($default_room)."\"><br>\n";
		$content.="&nbsp;<font size=-1>Art:</font><br>&nbsp;<select name=\"art\">\n";
		
		for ($i=1; $i<=sizeof($TERMIN_TYP); $i++)
			if ($db->f("date_typ") == $i)
				$content.= "<option value=$i selected>".$TERMIN_TYP[$i]["name"]."</option>";
			else
				$content.= "<option value=$i>".$TERMIN_TYP[$i]["name"]."</option>";
		$content.="</select><br><br>\n";

		$content.="<input type=\"CHECKBOX\" name=\"topic\"/><font size=-1>Thema im Forum anlegen</font><br />\n";
		$content.="<input type=\"CHECKBOX\" name=\"folder\"/><font size=-1>Dateiordner anlegen</font>\n";
				
		$content.="</tr></td></table></td></tr>\n<tr><td class=\"steel1\" align=\"center\" colspan=2>";
		$content.="<input type=\"IMAGE\" name=\"send\" border=0 src=\"pictures/buttons/terminspeichern-button.gif\" value=\"speichern\">&nbsp;";
		$content.="<a href=\"$PHP_SELF?cancel=TRUE\"><img  border=0 src=\"pictures/buttons/abbrechen-button.gif\"><br /><br />";
	 	$content.= "<a name=\"anchor\"></a>";
		

		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		printcontent(0,1, $content, '');
		echo "</tr></table>	";

		?>
		</form>
		<?	
	}
	
	//..und alte Bearbeiten
	$c=0;
	while ($db->next_record()) {

		//Ermitteln, ob Ordner an diesem Termin haengt
		$c++;
		$db2->query("SELECT folder_id FROM folder WHERE range_id='".$db->f("termin_id")."'");
		if ($db2->num_rows())
			$folder=TRUE;
		else
			$folder=FALSE;
			
		if (($show_id  == $db->f("termin_id")) || ($show_all)) {
			print "<a name=\"#anchor\"></a>";
			$edit=TRUE;
		} else	
			$edit=FALSE;
		
		//Zusatz erstellen
		if ((!$admin_dates_data["insert_id"]) && ($show_id  != $db->f("termin_id")) && (!$show_all))
			$zusatz="<input type=\"CHECKBOX\" ".(($mark_all_x) ? "checked" : "")." name=\"kill_termin[]\" value=\"". $db->f("termin_id")."&". $db->f("topic_id")."\"><img src=\"pictures/trash.gif\" border=0 />";
		else
			$zusatz='';
		
		//Titel erstellen
		$titel='';
		if ($edit) {
			$titel.="&nbsp;<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"tag[]\" maxlength=2 size=2 value=\"".date ("j", $db->f("date"))."\"><font size=-1>.</font>";
			$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"monat[]\" maxlength=2 size=2 value=\"".date ("n", $db->f("date")) ."\"><font size=-1>.</font>";
			$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"jahr[]\" maxlength=4 size=4  value=\"".date ("Y", $db->f("date"))."\"><font size=-1>&nbsp;von&nbsp;</font>";
			$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"stunde[]\" maxlength=2 size=2 value=\"".date ("G", $db->f("date"))."\"><font size=-1> :</font>";
			$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"minute[]\" maxlength=2 size=2 value=\"".date ("i", $db->f("date")) ."\"><font size=-1>&nbsp;bis&nbsp;</font>";
			$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"end_stunde[]\" maxlength=2 size=2 value=\"".date ("G", $db->f("end_time")) ."\"><font size=-1> :</font>";
			$titel.="<input type=\"TEXT\" style=\"font-size:8 pt;\" name=\"end_minute[]\" maxlength=2 size=2 value=\"".date ("i", $db->f("end_time")) ."\"><font size=-1> Uhr.</font>";
		    	$titel.="<input type=\"HIDDEN\" name=\"termin_id[]\" value=\"".$db->f("termin_id")."\">";
			$titel.="<input type=\"HIDDEN\" name=\"topic_id[]\" value=\"".$db->f("topic_id")."\">";
			}
		else {
			$titel .= substr(strftime("%a",$db->f("date")),0,2);		
			$titel.= date (". d.m.Y, H:i", $db->f("date"));
			if ($db->f("date") <$db->f("end_time"))
				$titel.= " - ".date ("H:i", $db->f("end_time"));
			if ($db->f("content")) {
				$tmp_titel=htmlReady(mila($db->f("content"))); //Beschneiden des Titels			
				$titel.=", ".$tmp_titel;
				}
			}
		 if (($show_id  == $db->f("termin_id")) && (!$result))
		 	$titel.= "<a name=\"anchor\"></a>";
		
		//Link erstellen
		if (($show_id  == $db->f("termin_id")) || ($show_all))
			$link=$PHP_SELF."?range_id=".$admin_dates_data["range_id"]."&show_id=";			
		else
			$link=$PHP_SELF."?range_id=".$admin_dates_data["range_id"]."&show_id=".$db->f("termin_id")."#anchor";
			
		//Icon erstellen
		$icon="&nbsp;<img src=\"./pictures/termin-icon.gif\" border=0>";
		
		if ($db->f("chdate") > $loginfilelast[$SessSemName[1]])
			$neuer_termin=TRUE;
		else
			$neuer_termin=FALSE;


		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		
		if (($show_id  == $db->f("termin_id")) || ($show_all))
			printhead(0, 0, $link, "open", $neuer_termin, $icon, $titel, $zusatz, $db->f("mkdate"));
		else
			printhead(0, 0, $link, "close", $neuer_termin, $icon, $titel, $zusatz, $db->f("mkdate"));

		echo "</tr></table>	";
		
		//Contentbereich
		if (($show_id  == $db->f("termin_id")) || ($show_all)) {
			$content='';		
			if ($edit) {
				$content.="<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"left\"width=\"100%\"><tr>\n";
				$content.="<td class=\"steel1\" width=\"80%\" valign=\"top\">\n";
				
				if (!$show_all) {
					$content.="<input type=\"HIDDEN\" name=\"show_id\" value=\"". $db->f("termin_id")."\">";
				}
				
				$content.="<font size=-1>Titel:</font><br /><input type=\"TEXT\" name=\"titel[]\" maxlength=255 size=".round($max_col*0.45)." value=\"".htmlReady($db->f("content"))."\"><br />";
				$content.="<font size=-1>Beschreibung:<br></font><textarea style=\"width:98%\" cols=\"". round($max_col*0.45)."\" rows=4 name=\"description[]\"  wrap=\"virtual\">".$db->f("description")."</textarea>\n</div>";
				$content.="</td>\n";
				$content.="<td class=\"steel1\" width=\"20%\">\n";
				$content.="<font size=-1>&nbsp;Raum:</font>";
				if ($RESOURCES_ENABLE) {
					$assigned_resource_id = getDateAssigenedRoom($db->f("termin_id"));
						$resList -> reset();
						if ($resList->numberOfEvents()) {
						$content.= "<br /><font size=-1>&nbsp;<select name=\"resource_id[]\"></font>";
						$content.= sprintf("<option %s value=\"FALSE\">[eingeben oder aus Liste]</option>", (!$assigned_resource_id) ? "selected" : "");												
						while ($resObject = $resList->nextEvent())
							$content.= sprintf("<option %s value=\"%s\">%s</option>", ($assigned_resource_id) == $resObject->getId() ? "selected" :"", $resObject->getId(), htmlReady($resObject->getName()));
						$content.= "</select></font>";
					}
				}
				$content.="<br />&nbsp;<input type=\"TEXT\"  name=\"raum[]\" maxlength=255 size=20 value=\"". htmlReady($db->f("raum"))."\"><br>\n";
				$content.="&nbsp;<font size=-1>Art:</font><br>&nbsp;<select name=\"art[]\">\n";
				for ($i=1; $i<=sizeof($TERMIN_TYP); $i++)
					if ($db->f("date_typ") == $i)
						$content.= "<option value=$i selected>".$TERMIN_TYP[$i]["name"]."</option>";
					else
						$content.= "<option value=$i>".$TERMIN_TYP[$i]["name"]."</option>";
				$content.="</select><br><br>\n";

				if ($db->f("topic_id")) 
					$content.= "<font size=-1>&nbsp; Forenthema vorhanden</font><br>";
				else
					$content.="<font size=-1>&nbsp; <input type=\"CHECKBOX\" name=\"insert_topic[]\"/>Thema im Forum anlegen</font><br />\n";

				if ($folder)
					$content.= "<font size=-1>&nbsp; Dateiordner vorhanden</font>";
				else
					$content.="<font size=-1>&nbsp; <input type=\"CHECKBOX\" name=\"insert_folder[]\"/>Dateiordner anlegen</font>\n";
				
				$content.="</tr></td></table></td></tr>\n<tr><td class=\"steel1\" align=\"center\" colspan=2>";

				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
				
				if (!$show_all)
					$content.="<input type=\"IMAGE\" name=\"edit\" border=0 src=\"pictures/buttons/terminaendern-button.gif\" value=\"verändern\"><br /><br />";
				printcontent(0,1, $content, '');
				}
		
		echo "</td></tr></table>";
		}
	}
	?>
		<tr>
			<td class="blank" >	&nbsp; 
			</td>
		</tr>
	<?	
	if ((!$admin_dates_data["insert_id"]) && (($show_all) || ($c>10))) {
		?>
		<tr align="left" height="22">
			<td class="steelgraulight" align="right" nowrap>
			<?
			if (!$show_all) {
				?>
				<input type="IMAGE" name="mark_all" border=0 src="pictures/buttons/alleauswaehlen-button.gif" value="löschen">&nbsp;
				<input type="IMAGE" name="send" border=0 src="pictures/buttons/loeschen-button.gif" value="löschen">&nbsp; 
				<?
			}
	}
	if ($show_all) {
		?>
		<input type="IMAGE" name="send" border=0 src="pictures/buttons/termineaendern-button.gif" value="verändern">&nbsp; &nbsp; 
		<?
	}
}
?>
						</tr>
						<tr>
								<td class="blank" >	&nbsp; 
								</td>
						</tr>
						</td>
					</table>
					</form>
				
			</tr>
		</table>
		</tr>
	</td>
	</table>

<?	
page_close();
 ?>
</td></tr></table>
</body>
</html>