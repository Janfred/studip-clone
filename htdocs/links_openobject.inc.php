<?
/**
* links_openobject.inc.php
* 
* links for the Stud.IP objects (institutes and Veranstaltungen)
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		views
* @module		links_openobject.inc.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// links_openobject.inc.php
// Links fuer Stud.IP Objekte
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

//only if there's an open object

if (isset($SessSemName) && $SessSemName[0] != "") {

require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."reiter.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Modules.class.php");

$db=new DB_Seminar;
$reiter=new reiter;
$Modules=new Modules;

//load list of used modules
$modules = $Modules->getLocalModules($SessSemName[1]);

//Reitersytem erzeugen

if ($ILIAS_CONNECT_ENABLE) {
	include_once ("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_LEARNINGMODULES/lernmodul_db_functions.inc.php");
	include_once ("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_LEARNINGMODULES/lernmodul_user_functions.inc.php");
}

//Topkats
if ($SessSemName["class"]=="inst") {
	$structure["institut_main"]=array (topKat=>"", name=>_("&Uuml;bersicht"), link=>"institut_main.php", active=>FALSE);
	if ($modules["forum"])
		$structure["forum"]=array (topKat=>"", name=>_("Forum"), link=>"forum.php", active=>FALSE);
	if ($modules["personal"])
		$structure["personal"]=array (topKat=>"", name=>_("Personal"), link=>"institut_members.php", active=>FALSE);
	if ($modules["documents"])
		$structure["folder"]=array (topKat=>"", name=>_("Dateien"), link=>"folder.php?cmd=tree", active=>FALSE);
	if ($modules["literature"])
		$structure["literatur"]=array (topKat=>"", name=>_("Literatur zur Einrichtung"), link=>"literatur.php", active=>FALSE);

	//topkats for resources management, if module is activated
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvaiableResources ($SessSemName[1]))
			$structure["resources"]=array (topKat=>"", name=>_("Ressourcen"), link=>"resources.php?view=openobject_main&view_mode=no_nav", active=>FALSE);
	}
} else {
	$structure["seminar_main"]=array (topKat=>"", name=>_("&Uuml;bersicht"), link=>"seminar_main.php", active=>FALSE);
	if ($modules["forum"])
		$structure["forum"]=array (topKat=>"", name=>_("Forum"), link=>"forum.php", active=>FALSE);
	if ((!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM) || $rechte) && ($modules["participants"])){
		$structure["teilnehmer"]=array (topKat=>"", name=>_("TeilnehmerInnen"), link=>"teilnehmer.php", active=>FALSE);
	}
	if ($modules["documents"])
		$structure["folder"]=array (topKat=>"", name=>_("Dateien"), link=>"folder.php?cmd=tree", active=>FALSE);
	if ($modules["schedule"])
		$structure["dates"]=array (topKat=>"", name=>_("Ablaufplan"), link=>"dates.php", active=>FALSE);
	if ($modules["literature"])
		$structure["literatur"]=array (topKat=>"", name=>_("Literatur"), link=>"literatur.php", active=>FALSE);

	//topkats for resources management, if module is activated
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvaiableResources ($SessSemName[1]))
			$structure["resources"]=array (topKat=>"", name=>_("Ressourcen"), link=>"resources.php?view=openobject_main&view_mode=no_nav", active=>FALSE);
	}
}

//topkats for Ilias-learningmodules, if module is activated
if (($ILIAS_CONNECT_ENABLE) && ($modules["ilias_connect"])) {
	if (get_seminar_modules($SessSemName[1]) != false)
		$structure["lernmodule"]=array (topKat=>"", name=>_("Lernmodule"), link=>"seminar_lernmodule.php?seminar_id=".$SessSemName[1], active=>FALSE);
	elseif  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["lernmodule"]=array (topKat=>"", name=>_("Lernmodule"), link=>"seminar_lernmodule.php?view=edit&seminar_id=".$SessSemName[1], active=>FALSE);
/*		else $nolink = true;
		if (($nolink != true) AND (get_connected_user_id($auth->auth["uid"]) == false))
			$structure["lernmodule"]=array (topKat=>"", name=>_("Lernmodule"), link=>"migration2studip.php", active=>FALSE);/**/
}

//topkats for SupportDB, if module is activated
if (($SUPPORT_ENABLE) && ($modules["support"])) {
	$structure["support"]=array (topKat=>"", name=>_("SupportDB"), link=>"support.php", active=>FALSE);
}


//Bottomkats
if ($SessSemName["class"]=="inst") {
	$structure["_institut_main"]=array (topKat=>"institut_main", name=>_("Info"), link=>"institut_main.php", active=>FALSE);
	if ($modules["personal"])	
		$structure["institut_members"]=array (topKat=>"personal", name=>_("MitarbeiterInnen"), link=>"institut_members.php", active=>FALSE);
	$structure["institut_veranstaltungen"]=array (topKat=>"institut_main", name=>_("Veranstaltungen"), link=>"show_bereich.php?level=s&id=$SessSemName[1]", active=>FALSE);
	$structure["timetable"]=array (topKat=>"institut_main", name=>_("Veranstaltungs-Timetable"), link=>"mein_stundenplan.php?inst_id=$SessSemName[1]", active=>FALSE);
	// $structure["druckansicht_i"]=array (topKat=>"institut_main", name=>"Druckansicht", link=>"print_institut.php", target=>"_new", active=>FALSE);
	if ($rechte)
		if ($perm->have_perm("admin"))
			$structure["administration_e"]=array (topKat=>"institut_main", name=>_("Administration der Einrichtung"), link=>"admin_institut.php?new_inst=TRUE", active=>FALSE);
		else
			$structure["administration_e"]=array (topKat=>"institut_main", name=>_("Administration der Einrichtung"), link=>"admin_literatur.php?new_inst=TRUE&view=literatur_inst", active=>FALSE);		
} else {
//
	$structure["_seminar_main"]=array (topKat=>"seminar_main", name=>_("Kurzinfo"), link=>"seminar_main.php", active=>FALSE);
	$structure["details"]=array (topKat=>"seminar_main", name=>_("Details"), link=>"details.php", active=>FALSE);
	$structure["druckansicht_s"]=array (topKat=>"seminar_main", name=>_("Druckansicht"), link=>"print_seminar.php", target=>"_new", active=>FALSE);
	if ($rechte)
		$structure["administration_v"]=array (topKat=>"seminar_main", name=>_("Administration dieser Veranstaltung"), link=>"admin_seminare1.php?new_sem=TRUE", active=>FALSE);
}
//

if ((!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM)) && ($modules["participants"])){
	$structure["_teilnehmer"]=array (topKat=>"teilnehmer", name=>_("TeilnehmerInnen"), link=>"teilnehmer.php", active=>FALSE);
}
if ($modules["forum"]) {
	$structure["_forum"]=array (topKat=>"forum", name=>_("Themen"), link=>"forum.php", active=>FALSE);
	$structure["neue"]=array (topKat=>"forum", name=>_("neue Beitr&auml;ge"), link=>"forum.php?view=neue", active=>FALSE);
	$structure["letzte"]=array (topKat=>"forum", name=>_("letzte 5 Beitr&auml;ge"), link=>"forum.php?view=letzte&mehr=1", active=>FALSE);
	$structure["suchen"]=array (topKat=>"forum", name=>_("Suchen"), link=>"suchen.php", active=>FALSE);
	$structure["forum_export"]=array (topKat=>"forum", name=>_("Druckansicht"), link=>"forum_export.php", target=>"_new", active=>FALSE);
	if (($rechte) || ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["topic_create_autor"]))
		$structure["neues_thema"]=array (topKat=>"forum", name=>_("neues Thema"), link=>"forum.php?neuesthema=TRUE#anker", active=>FALSE);
}
//
if (($SessSemName["class"]=="sem") && ($modules["schedule"])){
	$structure["_dates"]=array (topKat=>"dates", name=>_("alle Termine"), link=>"dates.php", active=>FALSE);
	$structure["sitzung"]=array (topKat=>"dates", name=>_("Sitzungstermine"), link=>"dates.php?show_not=sem", active=>FALSE);
	$structure["andere_t"]=array (topKat=>"dates", name=>_("andere Termine"), link=>"dates.php?show_not=other", active=>FALSE);
	if ($rechte)
		$structure["admin_dates"]=array (topKat=>"dates", name=>_("Ablaufplan bearbeiten"), link=>"admin_dates.php?new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
}
//
if ($modules["documents"]) {
	$structure["_folder"]=array (topKat=>"folder", name=>_("Ordneransicht"), link=>"folder.php?cmd=tree", active=>FALSE);
	$structure["alle_dateien"]=array (topKat=>"folder", name=>_("Alle Dateien"), link=>"folder.php?cmd=all", active=>FALSE);
}
//
if ($modules["literature"]) {
	if ($SessSemName["class"]=="sem")
		$structure["_literatur"]=array (topKat=>"literatur", name=>_("Literatur und Links"), link=>"literatur.php?view=literatur_sem", active=>FALSE);
	else
		$structure["_literatur"]=array (topKat=>"literatur", name=>_("Literatur und Links"), link=>"literatur.php?view=literatur_inst", active=>FALSE);
}

if ($SessSemName["class"]=="sem" && $modules["participants"] && (!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM)))
	$structure["statusgruppen"]=array (topKat=>"teilnehmer", name=>_("Funktionen / Gruppen"), link=>"statusgruppen.php?view=statusgruppe_sem", active=>FALSE);


if ($rechte)
	if (($SessSemName["class"]=="sem") && ($modules["participants"]))
		$structure["Statusgruppen verwalten"]=array (topKat=>"teilnehmer", name=>_("Funktionen / Gruppen verwalten"), link=>"admin_statusgruppe.php?view=statusgruppe_sem&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
	elseif (($perm->have_perm("admin")) && ($modules["personal"]))
			$structure["Statusgruppen verwalten"]=array (topKat=>"personal", name=>_("Funktionen / Gruppen verwalten"), link=>"admin_statusgruppe.php?view=statusgruppe_inst&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);


if (($rechte) && ($modules["literature"]))
	if ($SessSemName["class"]=="sem")
		$structure["admin_literatur"]=array (topKat=>"literatur", name=>_("Literatur und Links bearbeiten"), link=>"admin_literatur.php?view=literatur_sem&new_sem=TRUE&range_id=".$SessSemName[1], active=>FALSE);
	else
		$structure["admin_literatur"]=array (topKat=>"literatur", name=>_("Literatur und Links bearbeiten"), link=>"admin_literatur.php?view=literatur_inst&new_inst=TRUE&range_id=".$SessSemName[1], active=>FALSE);

//bottomkats for resources-management, if modul is activated
if ($RESOURCES_ENABLE) {
	$structure["resources_overview"]=array (topKat=>"resources", name=>_("&Uuml;bersicht"), link=>"resources.php?view=openobject_main", active=>FALSE);
	$structure["resources_details"]=array (topKat=>"resources", name=>_("Details"), link=>"resources.php?view=openobject_details", active=>FALSE);
	$structure["resources_schedule"]=array (topKat=>"resources", name=>_("Belegung"), link=>"resources.php?view=openobject_schedule", active=>FALSE);
	$structure["resources_assign"]=array (topKat=>"resources", name=>_("Belegungen bearbeiten"), link=>"resources.php?view=openobject_assign", active=>FALSE);
	if ($rechte)
		$structure["resources_admin"]=array (topKat=>"resources", name=>_("Ressourcen verwalten"), link=>"resources.php", active=>FALSE);
}

//bottomkats for Ilias-connect, if modul is activated
if (($ILIAS_CONNECT_ENABLE) && ($modules["ilias_connect"])){
	if (get_seminar_modules($SessSemName[1]) != false)
	{
		if ($SessSemName["class"]=="inst") 
			$structure["lernmodule_show"]=array (topKat=>"lernmodule", name=>_("Lernmodule dieser Einrichtung"), link=>"seminar_lernmodule.php?view=show&seminar_id=" . $SessSemName[1], active=>FALSE);
		else		
			$structure["lernmodule_show"]=array (topKat=>"lernmodule", name=>_("Lernmodule dieser Veranstaltung"), link=>"seminar_lernmodule.php?view=show&seminar_id=" . $SessSemName[1], active=>FALSE);
	}
	if  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["lernmodule_edit"]=array (topKat=>"lernmodule", name=>_("Lernmodule hinzuf&uuml;gen / entfernen"), link=>"seminar_lernmodule.php?view=edit&seminar_id=" . $SessSemName[1], active=>FALSE);
}

//bottomkats for SupportDB, if modul is activated
if (($SUPPORT_ENABLE) && ($modules["support"])){
	$structure["support_overview"]=array (topKat=>"support", name=>_("&Uuml;bersicht"), link=>"support.php?view=overview", active=>FALSE);
	$structure["support_requests"]=array (topKat=>"support", name=>_("Anfragen"), link=>"support.php?view=requests", active=>FALSE);
	if ($rechte)
		$structure["support_events"]=array (topKat=>"resources", name=>_("Supportleistungen bearbeiten"), link=>"support.php?view=edit_events", active=>FALSE);
}


//Infofenstereintraege erzeugen
if ($SessSemName["class"]=="inst") {
	$tooltip = sprintf(_("Sie befinden sich in der Einrichtung: %s, letzter Besuch: %s, Ihr Status in dieser Einrichtung: %s"), $SessSemName[0], date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]), $SemUserStatus);
} else {
	$tooltip = sprintf(_("Sie befinden sich in der Veranstaltung: %s, letzter Besuch: %s, Ihr Status in dieser Veranstaltung: %s"), $SessSemName[0], date("d.m.Y - H:i:s", $loginfilelast[$SessSemName[1]]), $SemUserStatus);
}

//View festlegen
switch ($i_page) {
	case "show_bereich.php" : 
		$reiter_view="institut_veranstaltungen"; 
	break;
	case "institut_main.php" : 
		$reiter_view="institut_main"; 
	break;
	case "institut_members.php" :
		$reiter_view = "institut_members";
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
	case "statusgruppen.php" : 
		$reiter_view="statusgruppen"; 
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
		switch ($folder_system_data["cmd"]) {
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
	case "migration2studip.php": 
		$reiter_view="lernmodule_user";
	break;
	case "seminar_lernmodule.php": 
		switch ($view) {
			case "edit":
				$reiter_view="lernmodule_edit";
			break;
			default :
				$reiter_view="lernmodule_show";
			break;
		}
	break;
	case "resources.php": 
		switch ($view) {
			case "openobject_main":
				$reiter_view="resources";
			break;
			case "openobject_details":
				$reiter_view="resources_details";
			break;
			case "openobject_schedule":
				$reiter_view="resources_schedule";
			break;
			case "openobject_assign":
				$reiter_view="resources_assign";
			break;
			default :
				$reiter_view="resources";
			break;
		}
	break;
	case "support.php": 
		switch ($supportdb_data["view"]) {
			case "overview":
				$reiter_view="support_overview";
			break;
			case "requests":
				$reiter_view="support_requests";
			break;
			case "edit_events":
				$reiter_view="support_events";
			break;
		}
	break;
	default :
		if ($SessSemName["class"]=="inst")
			$reiter_view="institut_main";
		else
			$reiter_view="seminar_main";
	break;
}

$reiter->create($structure, $reiter_view, $tooltip);
}
?>