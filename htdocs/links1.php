<?php
//diese Datei enthält die links der Kopfzeile, besser nur anzeigen wenn ein Objekt ausgewählt wurde
if (isset($SessSemName) && $SessSemName[0] != "") {
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."reiter.inc.php");

$db=new DB_Seminar;
$reiter=new reiter;
		
//Welche Art liegt hier vor?
$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id = '$SessSemName[1]' ");
if ($db->next_record())
	$entry_level="sem";

if (!$entry_level) {
	$db->query("SELECT Institut_id FROM Institute WHERE Institut_id = '$SessSemName[1]' ");
	if ($db->next_record())
		$entry_level="inst";
}

if (!$entry_level) {
	$db->query("SELECT Fakultaets_id FROM fakultaeten WHERE Fakultaets_id = '$SessSemName[1]' ");
	if ($db->next_record())
		$entry_level="fak";
}

//Reitersytem erzeugen

//Topkats
if ($entry_level=="inst") {
	$structure["institut_main"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"institut_main.php", active=>FALSE);
	$structure["forum"]=array (topKat=>"", name=>"Forum", link=>"forum.php", active=>FALSE);
	$structure["folder"]=array (topKat=>"", name=>"Dateien", link=>"folder.php?cmd=tree", active=>FALSE);
	$structure["literatur"]=array (topKat=>"", name=>"Literatur zur Einrichtung", link=>"literatur.php?", active=>FALSE);
} else {
	$structure["seminar_main"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"seminar_main.php", active=>FALSE);
	$structure["forum"]=array (topKat=>"", name=>"Forum", link=>"forum.php", active=>FALSE);
	$structure["folder"]=array (topKat=>"", name=>"Dateien", link=>"folder.php?cmd=tree", active=>FALSE);
	$structure["dates"]=array (topKat=>"", name=>"Ablaufplan", link=>"dates.php", active=>FALSE);
	$structure["literatur"]=array (topKat=>"", name=>"Literatur", link=>"literatur.php?", active=>FALSE);
}

//Bottomkats
if ($entry_level=="inst") {
	$structure["_institut_main"]=array (topKat=>"institut_main", name=>"Info", link=>"institut_main.php", active=>FALSE);
	$structure["institut_mitarbeiter"]=array (topKat=>"institut_main", name=>"MitarbeiterInnen", link=>"institut_mitarbeiter.php", active=>FALSE);
	$structure["institut_veranstaltungen"]=array (topKat=>"institut_main", name=>"Veranstaltungen", link=>"show_bereich.php?level=s&id=$SessSemName[1]", active=>FALSE);
	$structure["timetable"]=array (topKat=>"institut_main", name=>"Veranstaltungs-Timetable", link=>"mein_stundenplan.php?inst_id=$SessSemName[1]", active=>FALSE);
	$structure["druckansicht_i"]=array (topKat=>"institut_main", name=>"Druckansicht", link=>"print_institut.php", target=>"_new", active=>FALSE);
	if ($rechte)
		$structure["administration_e"]=array (topKat=>"institut_main", name=>"Administration der Einrichtung", link=>"admin_institut.php?new_inst=TRUE&view=inst", active=>FALSE);
} else {
//
	$structure["_seminar_main"]=array (topKat=>"seminar_main", name=>"Kurzinfo", link=>"seminar_main.php", active=>FALSE);
	$structure["details"]=array (topKat=>"seminar_main", name=>"Details", link=>"details.php", active=>FALSE);
	$structure["teilnehmer"]=array (topKat=>"seminar_main", name=>"TeilnehmerInnen", link=>"teilnehmer.php", active=>FALSE);
	$structure["druckansicht_s"]=array (topKat=>"seminar_main", name=>"Druckansicht", link=>"print_seminar.php", target=>"_new", active=>FALSE);
	if ($rechte)
		$structure["administration_v"]=array (topKat=>"seminar_main", name=>"Administration dieser Veranstaltung", link=>"admin_seminare1.php?new_sem=TRUE".$SessSemName[1], active=>FALSE);
}
//
$structure["_forum"]=array (topKat=>"forum", name=>"Themen", link=>"forum.php", active=>FALSE);
$structure["neue"]=array (topKat=>"forum", name=>"neue Beitr&auml;ge", link=>"forum.php?view=neue", active=>FALSE);
$structure["letzte"]=array (topKat=>"forum", name=>"letzte 5 Beitr&auml;ge", link=>"forum.php?view=letzte&mehr=1", active=>FALSE);
$structure["suchen"]=array (topKat=>"forum", name=>"Suchen", link=>"suchen.php", active=>FALSE);
$structure["forum_export"]=array (topKat=>"forum", name=>"Druckansicht", link=>"forum_export.php", target=>"_new", active=>FALSE);
if ($rechte)
	$structure["neues_thema"]=array (topKat=>"forum", name=>"neues Thema", link=>"forum.php?neuesthema=TRUE#anker", active=>FALSE);
//
if ($entry_level=="sem") {
	$structure["_dates"]=array (topKat=>"dates", name=>"alle Termine", link=>"dates.php", active=>FALSE);
	$structure["sitzung"]=array (topKat=>"dates", name=>"Sitzungstermine", link=>"dates.php?show_not=sem", active=>FALSE);
	$structure["andere_t"]=array (topKat=>"dates", name=>"andere Termine", link=>"dates.php?show_not=other", active=>FALSE);
	if ($rechte)
		$structure["admin_dates"]=array (topKat=>"dates", name=>"Ablaufplan bearbeiten", link=>"admin_dates.php?new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
}
//
$structure["_folder"]=array (topKat=>"folder", name=>"Ordneransicht", link=>"folder.php?cmd=tree", active=>FALSE);
$structure["alle_dateien"]=array (topKat=>"folder", name=>"Alle Dateien", link=>"folder.php?cmd=all", active=>FALSE);
//
$structure["_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links", link=>"literatur.php?view=sem", active=>FALSE);
	
if ($rechte)
	if ($entry_level=="sem")
		$structure["admin_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links bearbeiten", link=>"admin_literatur.php?view=sem&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
	else
		$structure["admin_literatur"]=array (topKat=>"literatur", name=>"Literatur und Links bearbeiten", link=>"admin_literatur.php?view=inst&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);

//Infofenstereintraege erzeugen
if ($entry_level=="inst") {
	$js="Sie befinden sich in der Einrichtung: ".JSReady($SessSemName[0],"popup").", letzter Besuch: ".date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]).", Ihr Status in dieser Einrichtung: ".$SemUserStatus;
	$alt="Sie befinden sich in der Einrichtung: ".htmlReady($SessSemName[0]).", letzter Besuch: ".date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]).", Ihr Status in dieser Einrichtung: ".$SemUserStatus;
} else {
	$js="Sie befinden sich in der Veranstaltung: ".JSReady($SessSemName[0],"popup").", letzter Besuch: ".date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]).", Ihr Status in dieser Veranstaltung: ".$SemUserStatus;
	$alt="Sie befinden sich in der Veranstaltung: ".htmlReady($SessSemName[0]).", letzter Besuch: ".date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]).", Ihr Status in dieser Veranstaltung: ".$SemUserStatus;
}

//View festlegen
switch ($i_page) {
	case "show_bereich.php" : 
		$reiter_view="institut_veranstaltungen"; 
	break;
	case "institut_main.php" : 
		$reiter_view="institut_main"; 
	break;
	case "seminar_main.php" : 
		$reiter_view="seminar_main"; 
	break;
	case "details.php" : 
		$reiter_view="details"; 
	break;
	case "teilnehmer.php" : 
		$reiter_view="teilnehmer"; 
	break;
	case "institut_details.php": 
		$reiter_view="institut_details"; 
	break;
	case "inst_admin.php": 
		$reiter_view="inst_admin"; 
	break;
	case "admin_institut.php": 
		$reiter_view="admin_institut"; 
	break;
	case "admin_literatur.php": 
		$reiter_view="admin_literatur"; 
	break;
	case "admin_news.php": 
		$reiter_view="admin_news"; 
	break;
	case "forum.php": 
		switch ($view) {
			case "":
				$reiter_view="forum";
			break;
			case "neue":
				$reiter_view="neue";
			break;
			case "letzte":
				$reiter_view="letzte";
			break;
			default :
				$reiter_view="forum";
			break;
		}
	break;
	case "dates.php": 
		switch ($show_not) {
			case "":
				$reiter_view="dates";
			break;
			case "sem":
				$reiter_view="sitzung";
			break;
			case "other":
				$reiter_view="andere_t";
			break;
			default :
				$reiter_view="dates";
			break;
		}
	break;
	case "folder.php": 
		switch ($cmd) {
			case "":
				$reiter_view="folder";
			break;
			case "tree":
				$reiter_view="folder";
			break;
			case "all":
				$reiter_view="alle_dateien";
			break;
			default :
				$reiter_view="folder";
			break;
		}
	break;
	case "suchen.php": 
		$reiter_view="suchen"; 
	break;	
	case "mein_stundenplan.php": 
		$reiter_view="timetable"; 
	break;	
	case "literatur.php": 
		$reiter_view="literatur";
	break;
	default :
		if ($entry_level=="inst")
			$reiter_view="institut_main";
		else
			$reiter_view="seminar_main";
	break;
}

$reiter->create($structure, $reiter_view, $alt, $js);
}
?>