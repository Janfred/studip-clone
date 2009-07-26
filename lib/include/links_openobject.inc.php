<?php
# Lifter001: TEST
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
// vim: noexpandtab
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

global
	$auth,
	$AUTO_INSERT_SEM,
	$ELEARNING_INTERFACE_ENABLE,
	$folder_system_data,
	$forum,
	$i_page,
	$perm,
	$rechte,
	$RELATIVE_PATH_ELEARNING_INTERFACE,
	$RELATIVE_PATH_LEARNINGMODULES,
	$RELATIVE_PATH_RESOURCES,
	$RESOURCES_ENABLE,
	$SEM_CLASS,
	$SEM_TYPE,
	$SemUserStatus,
	$SessSemName,
	$_show_scm,
	$type,
	$user,
	$view,
    $studygroup_mode;

//only if there's an open object

if (isset($SessSemName) && $SessSemName[0] != "") {

require_once ('lib/visual.inc.php');
require_once ('lib/include/reiter.inc.php');
require_once 'lib/functions.php';
require_once ('lib/classes/Modules.class.php');
require_once ('lib/classes/StudipScmEntry.class.php');
require_once ('lib/classes/LockRules.class.php');

$db=new DB_Seminar;
$reiter=new reiter;
$Modules=new Modules;


//load list of used modules
$modules = $Modules->getLocalModules($SessSemName[1]);
$studygroup_mode=$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["studygroup_mode"];

if ($modules["scm"]){
	$scms = array_values(StudipScmEntry::GetSCMEntriesForRange($SessSemName[1]));
}

if ($ELEARNING_INTERFACE_ENABLE) {
	include_once ("$RELATIVE_PATH_ELEARNING_INTERFACE/ObjectConnections.class.php");
}

//Topkats
$structure = array();
if ($SessSemName["class"]=="inst") {
	$structure["institut_main"]=array ('topKat' => '', 'name'=>_("Übersicht"), 'link' => URLHelper::getLink("institut_main.php"), 'active' => FALSE);
	if ($modules["forum"])
		$structure["forum"]=array ('topKat' => '', 'name' => _("Forum"), 'link' => URLHelper::getLink("forum.php?view=reset"), 'active' => FALSE);
	if ($modules["personal"] && $user->id != "nobody")
		$structure["personal"]=array ('topKat' => '', 'name' => _("Personal"), 'link' => URLHelper::getLink("institut_members.php"), 'active' => FALSE);
	if ($modules["documents"])
		$structure["folder"]=array ('topKat' => '', 'name' => _("Dateien"), 'link' => URLHelper::getLink("folder.php?cmd=tree"), 'active' => FALSE);
	if ($modules["scm"])
		$structure["scm"]=array ('topKat' => '', 'name' => ($scms[0]['tab_name'] ? $scms[0]['tab_name'] : _("Informationen")), 'link' => URLHelper::getLink("scm.php"), 'active' => FALSE);
	if ($modules["literature"])
		$structure["literatur"]=array ('topKat' => '', 'name' => _("Literatur zur Einrichtung"), 'link' => URLHelper::getLink("literatur.php"), 'active' => FALSE);
	if ($modules["wiki"]){
	  	$structure["wiki"]=array ('topKat' => '', 'name' => _("Wiki"), 'link' => URLHelper::getLink("wiki.php?view=show"), 'active' => FALSE);
	}

	//topkats for resources management, if module is activated
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvailableResources ($SessSemName[1]))
			$structure["resources"]=array ('topKat' => '', 'name' => _("Ressourcen"), 'link' => URLHelper::getLink('resources.php?view=openobject_main&view_mode=oobj'), 'active' => FALSE);
	}
} else {
	$structure["seminar_main"]=array ('topKat' => '', 'name' => _("Übersicht"), 'link' => URLHelper::getLink("seminar_main.php"), 'active' => FALSE);
	if ($studygroup_mode && $rechte && $perm->have_studip_perm('dozent',$SessSemName[1])) {
		$structure["studygroup_admin"]=array ('topKat' => '', 'name' => _("Admin"), 'link' => URLHelper::getLink("dispatch.php/course/studygroup/edit/".$SessSemName[1]), 'active' => FALSE);
		$structure["_studygroup_admin"]=array ('topKat' => 'studygroup_admin', 'name' => _("Admin"), 'link' => URLHelper::getLink("dispatch.php/course/studygroup/edit/".$SessSemName[1]), 'active' => FALSE);
	}
	if ($modules["forum"])
		$structure["forum"]=array ('topKat' => '', 'name' => _("Forum"), 'link' => URLHelper::getLink("forum.php?view=reset"), 'active' => FALSE);
	// studygroup (TT)
	if ($modules["participants"] && $studygroup_mode) {
		$structure["studygroup_teilnehmer"]=array ('topKat' => '', 'name' => _("TeilnehmerInnen"), 'link' => URLHelper::getLink("dispatch.php/course/studygroup/members/".$SessSemName[1]), 'active' => FALSE);
	} else if ((!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM) || $rechte) && $modules["participants"] && $user->id != "nobody") {
		$structure["teilnehmer"]=array ('topKat' => '', 'name' => _("TeilnehmerInnen"), 'link' => URLHelper::getLink("teilnehmer.php"), 'active' => FALSE);
	}
	if ($modules["documents"])
		$structure["folder"]=array ('topKat' => '', 'name' => _("Dateien"), 'link' => URLHelper::getLink("folder.php?cmd=tree"), 'active' => FALSE);
	if ($modules["schedule"] && $user->id != "nobody")
		$structure["dates"]=array ('topKat' => '', 'name' => _("Ablaufplan"), 'link' => URLHelper::getLink("dates.php?cmd=setType&type=all"), 'active' => FALSE);
	if ($modules["scm"]) {
		$structure["scm"]=array ('topKat' => '', 'name' => ($scms[0]['tab_name'] ? $scms[0]['tab_name'] : _("Informationen")), 'link' => URLHelper::getLink("scm.php"), 'active' => FALSE);
	}
	if ($modules["literature"])
		$structure["literatur"]=array ('topKat' => '', 'name' => _("Literatur"), 'link' => URLHelper::getLink("literatur.php"), 'active' => FALSE);
	if ($modules["wiki"]){
	  	$structure["wiki"]=array ('topKat' => '', 'name' => _("Wiki"), 'link' => URLHelper::getLink("wiki.php?view=show"), 'active' => FALSE);
	}

	//topkats for resources management, if module is activated
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvailableResources ($SessSemName[1]))
			$structure["resources"]=array ('topKat' => '', 'name' => _("Ressourcen"), 'link' => URLHelper::getLink('resources.php?view=openobject_main&view_mode=oobj'), 'active' => FALSE);
	}
}

//topkats for contentmodules, if elearning-interface is activated
if ($ELEARNING_INTERFACE_ENABLE && $modules["elearning_interface"] && $user->id != "nobody") {
	if (ObjectConnections::isConnected($SessSemName[1]))
		$structure["elearning_interface"]=array ('topKat' => '', 'name' => _("Lernmodule"), 'link' => URLHelper::getLink("elearning_interface.php?view=show&seminar_id=".$SessSemName[1]), 'active' => FALSE);
	elseif  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["elearning_interface"]=array ('topKat' => '', 'name' => _("Lernmodule"), 'link' => URLHelper::getLink("elearning_interface.php?view=edit&seminar_id=".$SessSemName[1]), 'active' => FALSE);
}

// last topkats, insert new topkats in front of this statement
// create the structure array for activated plugins
if ($GLOBALS['PLUGINS_ENABLE']){
	// list all activated plugins
	$plugins = PluginEngine::getPlugins('StandardPlugin', $SessSemName[1]);

	foreach ($plugins as $plugin){
		if ($plugin_struct = $reiter->getStructureForPlugin($plugin)){
			$structure = array_merge($structure, $plugin_struct['structure']);
			if($plugin_struct['reiter_view']) $reiter_view = $plugin_struct['reiter_view'];
		}
	}
}

//Bottomkats
if ($SessSemName["class"]=="inst") {
	$structure["_institut_main"]=array ('topKat' => "institut_main", 'name' => _("Info"), 'link' => URLHelper::getLink("institut_main.php"), 'active' => FALSE);
	if ($modules["personal"])
		$structure["institut_members"]=array ( 'topKat' => "personal", 'name' => _("MitarbeiterInnen"), 'link' => URLHelper::getLink("institut_members.php"), 'active' => FALSE);
	$structure["institut_veranstaltungen"]=array ('topKat' => "institut_main", 'name' => _("Veranstaltungen"), 'link' => URLHelper::getLink("show_bereich.php?level=s&id=$SessSemName[1]"), 'active' => FALSE);
	$structure["timetable"]=array ('topKat' => "institut_main", 'name' => _("Veranstaltungs-Timetable"), 'link' => URLHelper::getLink("mein_stundenplan.php?inst_id=$SessSemName[1]"), 'active' => FALSE);
	// $structure["druckansicht_i"]=array ('topKat' => "institut_main", 'name' => "Druckansicht", 'link' => URLHelper::getLink("print_institut.php"), 'target' =>"_blank", 'active' => FALSE);
	if ($rechte)
		if ($perm->have_perm("admin"))
			$structure["administration_e"]=array ('topKat' => "institut_main", 'name' => _("Administration der Einrichtung"), 'link' => URLHelper::getLink("admin_institut.php?new_inst=TRUE"), 'active' => FALSE);
		else
			$structure["administration_e"]=array ('topKat' => "institut_main", 'name' => _("Administration der Einrichtung"), 'link' => URLHelper::getLink("admin_lit_list.php?new_inst=TRUE&view=literatur_inst"), 'active' => FALSE);
} else {
//
	$structure["_seminar_main"]=array ('topKat' => "seminar_main", 'name' => _("Kurzinfo"), 'link' => URLHelper::getLink("seminar_main.php"), 'active' => FALSE);
	if (!$studygroup_mode) {
		$structure["details"]=array ('topKat' => "seminar_main", 'name' => _("Details"), 'link' => URLHelper::getLink("details.php"), 'active' => FALSE);
		$structure["druckansicht_s"]=array ('topKat' => "seminar_main", 'name' => _("Druckansicht"), 'link' => URLHelper::getLink("print_seminar.php"), 'target' => "_blank", 'active' => FALSE);
	}
	if ($rechte && !$studygroup_mode)
		$structure["administration_v"]=array ('topKat' => "seminar_main", 'name' => _("Administration dieser Veranstaltung"), 'link' => URLHelper::getLink("admin_seminare1.php?new_sem=TRUE"), 'active' => FALSE);

	$db->query("SELECT admission_binding FROM seminare WHERE seminar_id = '$SessSemName[1]'");
	$db->next_record();
	if (!$db->f("admission_binding") && !$perm->have_studip_perm("tutor",$SessSemName[1]) && $user->id != "nobody")
		$structure["delete_abo"]=array ('topKat' => "seminar_main", 'name' => _("Austragen aus der Veranstaltung"), 'link' => URLHelper::getLink("meine_seminare.php?auswahl=$SessSemName[1]&cmd=suppose_to_kill"), 'isolator' => TRUE);
}
//

// studygroup
if ($modules['participants'] && $studygroup_mode) {
	$structure["_studygroup_teilnehmer"]=array ('topKat' => 'studygroup_teilnehmer', 'name' => _("TeilnehmerInnen"), 'link' => URLHelper::getLink("dispatch.php/course/studygroup/members/".$SessSemName[1]), 'active' => FALSE);
} else if ((!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM)  || $rechte) && ($modules["participants"])){
	$structure["_teilnehmer"]=array ('topKat' => "teilnehmer", 'name' => _("TeilnehmerInnen"), 'link' => URLHelper::getLink("teilnehmer.php"), 'active' => FALSE);
}
if ($modules["forum"]) {
	$structure["_forum"]=array ('topKat' => "forum", 'name' => _("Themenansicht"), 'link' => URLHelper::getLink("forum.php?view=".$forum["themeview"]), 'active' => FALSE);
	if ($user->id != "nobody") {
		$structure["neue"]=array ('topKat' => "forum", 'name' => _("neue Beiträge"), 'link' => URLHelper::getLink("forum.php?view=neue&sort=age"), 'active' => FALSE);
		$structure["flat"]=array ('topKat' => "forum", 'name' => _("letzte Beiträge"), 'link' => URLHelper::getLink("forum.php?view=flat&sort=age"), 'active' => FALSE);
		$structure["search"]=array ('topKat' => "forum", 'name' => _("Suchen"), 'link' => URLHelper::getLink("forum.php?view=search&reset=1"), 'active' => FALSE);
	}
	$structure["forum_export"]=array ('topKat' => "forum", 'name' => _("Druckansicht"), 'link' => URLHelper::getLink("forum_export.php"), 'target' => "_blank", 'active' => FALSE);
	if (($rechte) || ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["topic_create_autor"]))
		$structure["neues_thema"]=array ('topKat' => "forum", 'name' => _("neues Thema anlegen"), 'link' => URLHelper::getLink("forum.php?view=".$forum["themeview"]."&neuesthema=TRUE#anker"), 'active' => FALSE);
		$structure["admin"]=array ('topKat' => "forum", 'name' => _("Forum anpassen"), 'link' => URLHelper::getLink("forum.php?forumsend=anpassen&view=$view"), 'active' => FALSE);

}
//
if (($SessSemName["class"]=="sem") && ($modules["schedule"])){
	$structure["_dates"]=array ('topKat' => "dates", 'name' => _("alle Termine"), 'link' => URLHelper::getLink("dates.php?cmd=setType&type=all"), 'active' => FALSE);
	$structure["sitzung"]=array ('topKat' => "dates", 'name' => _("Sitzungstermine"), 'link' => URLHelper::getLink("dates.php?cmd=setType&type=1"), 'active' => FALSE);
	$structure["andere_t"]=array ('topKat' => "dates", 'name' => _("andere Termine"), 'link' => URLHelper::getLink("dates.php?cmd=setType&type=other"), 'active' => FALSE);
	if ($rechte)
		$structure["themen"]=array ('topKat' => "dates", 'name' => _("Ablaufplan bearbeiten"), 'link' => URLHelper::getLink("themen.php?seminar_id=".$SessSemName[1]), 'active' => FALSE);
}
//
if ($modules["documents"]) {
	$structure["_folder"]=array ('topKat' => "folder", 'name' => _("Ordneransicht"), 'link' => URLHelper::getLink("folder.php?cmd=tree"), 'active' => FALSE);
	$structure["alle_dateien"]=array ('topKat' => "folder", 'name' => _("Alle Dateien"), 'link' => URLHelper::getLink("folder.php?cmd=all"), 'active' => FALSE);
}
//
if ($modules["scm"]) {
	foreach($scms as $scm){
		$structure["_scm_" . $scm['scm_id']]=array ('topKat' => "scm", 'name' => $scm['tab_name'] , 'link' => URLHelper::getLink("scm.php?show_scm=" . $scm['scm_id']), 'active' => FALSE);
	}
	if ($perm->have_studip_perm('tutor', $SessSemName[1])){
		$structure["_scm_new_entry"]=array ('topKat' => "scm", 'name' => _("neuen Eintrag anlegen") , 'link' => URLHelper::getLink("scm.php?show_scm=new_entry&i_view=edit"), 'active' => FALSE);
	}
}
//
if ($modules["literature"]) {
	if ($SessSemName["class"]=="sem"){
		$structure["_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur"), 'link' => URLHelper::getLink("literatur.php?view=literatur_sem"), 'active' => FALSE);
		$structure["_literatur_print"]=array ('topKat' => "literatur", 'name' => _("Druckansicht"), 'link' => URLHelper::getLink("lit_print_view.php?_range_id=" . $SessSemName[1]), 'target' => "_blank", 'active' => FALSE);
	}else{
		$structure["_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur"), 'link' => URLHelper::getLink("literatur.php?view=literatur_inst"), 'active' => FALSE);
		$structure["_literatur_print"]=array ('topKat' => "literatur", 'name' => _("Druckansicht"), 'link' => URLHelper::getLink("lit_print_view.php?_range_id=" . $SessSemName[1]), 'target' => "_blank", 'active' => FALSE);
	}
}

// Ticket #68
require_once('lib/classes/AuxLockRules.class.php');
$rule = AuxLockRules::getLockRuleBySemId($SessSemName[1]);
if (isset($rule)) {
	$show = false;
	foreach ((array)$rule['attributes'] as $val) {
		if ($val == 1) {
			$show = true;
			break;
		}
	}

	if ($show)  {
		$structure["teilnehmer_aux"] = array('topKat' => "teilnehmer", 'name' => _("Zusatzangaben"), 'link' => URLHelper::getLink("teilnehmer_aux.php"), 'active' => FALSE);
	}
}

if ($SessSemName["class"]=="sem" && $modules["participants"] && (!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM)  || $rechte))
	$structure["statusgruppen"]=array ('topKat' => "teilnehmer", 'name' => _("Funktionen / Gruppen"), 'link' => URLHelper::getLink("statusgruppen.php?view=statusgruppe_sem"), 'active' => FALSE);


if ($rechte)
	if ($SessSemName["class"]=="sem" && $modules["participants"] && !LockRules::check($SessSemName[1], 'groups'))
		$structure["Statusgruppen verwalten"]=array ('topKat' => "teilnehmer", 'name' => _("Funktionen / Gruppen verwalten"), 'link' => URLHelper::getLink("admin_statusgruppe.php?new_sem=TRUE&range_id=".$SessSemName[1]), 'active' => FALSE);
	if ($SessSemName["class"] != "sem" && $perm->have_perm("admin") && $modules["personal"])
		$structure["Statusgruppen verwalten"]=array ('topKat' => "personal", 'name' => _("Funktionen / Gruppen verwalten"), 'link' => URLHelper::getLink("admin_roles.php?new_sem=TRUE&range_id=".$SessSemName[1]), 'active' => FALSE);


if (($rechte) && ($modules["literature"]))
	if ($SessSemName["class"]=="sem")
		$structure["admin_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur bearbeiten"), 'link' => URLHelper::getLink("admin_lit_list.php?view=literatur_sem&new_sem=TRUE&_range_id=".$SessSemName[1]), 'active' => FALSE);
	else
		$structure["admin_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur bearbeiten"), 'link' => URLHelper::getLink("admin_lit_list.php?view=literatur_inst&new_inst=TRUE&_range_id=".$SessSemName[1]), 'active' => FALSE);

if ($modules["wiki"]) {
	$structure["_wiki"]=array ('topKat' => "wiki", 'name' => _("WikiWikiWeb"), 'link' => URLHelper::getLink("wiki.php?view=show"), 'active' => FALSE);
	$structure["wiki_listnew"]=array ('topKat' => "wiki", 'name' => _("Neue Seiten"), 'link' => URLHelper::getLink("wiki.php?view=listnew"), 'active' => FALSE);
	$structure["wiki_listall"]=array ('topKat' => "wiki", 'name' => _("Alle Seiten"), 'link' => URLHelper::getLink("wiki.php?view=listall"), 'active' => FALSE);
	$structure["wiki_export"]=array ('topKat' => "wiki", 'name' => _("Export"), 'link' => URLHelper::getLink("wiki.php?view=export"), 'active' => FALSE);
}

//bottomkats for resources-management, if modul is activated
if ($RESOURCES_ENABLE) {
	$structure["resources_overview"]=array ('topKat' => "resources", 'name' => _("Übersicht"), 'link' => URLHelper::getLink('resources.php?view=openobject_main'), 'active' => FALSE);
	$structure["resources_openobject_group_schedule"]=array ('topKat' => "resources", 'name' => _("Übersicht Belegung"), 'link' => URLHelper::getLink("resources.php?view=openobject_group_schedule"), 'active' => FALSE);
	$structure["resources_details"]=array ('topKat' => "resources", 'name' => _("Details"), 'link' => URLHelper::getLink('resources.php?view=openobject_details'), 'active' => FALSE);
	$structure["resources_schedule"]=array ('topKat' => "resources", 'name' => _("Belegung"), 'link' => URLHelper::getLink('resources.php?view=openobject_schedule'), 'active' => FALSE);
	$structure["resources_assign"]=array ('topKat' => "resources", 'name' => _("Belegungen bearbeiten"), 'link' => URLHelper::getLink('resources.php?view=openobject_assign'), 'active' => FALSE);
	//Hmm, funzt nicht. Aber wo sollte man auch rauskommen?
	//if ($rechte)
	//	$structure["resources_admin"]=array ('topKat' => "resources", 'name' => _("Ressourcen verwalten"), 'link' => //URLHelper::getLink('resources.php'), 'active' => FALSE);
}

//bottomkats for Ilias-connect, if modul is activated
if (($ELEARNING_INTERFACE_ENABLE) && ($modules["elearning_interface"])){
	if (ObjectConnections::isConnected($SessSemName[1]))
	{
		if ($SessSemName["class"]=="inst")
			$structure["elearning_interface_show"]=array ('topKat' => "elearning_interface", 'name' => _("Lernmodule dieser Einrichtung"), 'link' => URLHelper::getLink("elearning_interface.php?view=show&seminar_id=" . $SessSemName[1]), 'active' => FALSE);
		else
			$structure["elearning_interface_show"]=array ('topKat' => "elearning_interface", 'name' => _("Lernmodule dieser Veranstaltung"), 'link' => URLHelper::getLink("elearning_interface.php?view=show&seminar_id=" . $SessSemName[1]), 'active' => FALSE);
	}
	if  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["elearning_interface_edit"]=array ('topKat' => "elearning_interface", 'name' => _("Lernmodule hinzufügen / entfernen"), 'link' => URLHelper::getLink("elearning_interface.php?view=edit&seminar_id=" . $SessSemName[1]), 'active' => FALSE);
}

if (!$reiter_view){

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
		case "teilnehmer_view.php";
			$reiter_view="teilnehmer_view";
		break;
		case "teilnehmer_aux.php";
			$reiter_view="teilnehmer_aux";
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
				case "mixed":
					$reiter_view="forum";
				break;
				case "flatfolder":
					$reiter_view="forum";
				break;
				case "neue":
					$reiter_view="neue";
				break;
				case "flat":
					$reiter_view="flat";
				break;
				case "search":
					$reiter_view="search";
				break;
				default :
					$reiter_view="forum";
				break;
			}
		break;
		case "dates.php":
			switch ($type) {
				case "":
				case "all":
					$reiter_view="dates";
				break;
				case "1":
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
		case "scm.php":
			if ($_show_scm){
				$reiter_view = "_scm_" . $_show_scm;
			} else {
				$reiter_view = "scm";
				$_show_scm = $scms[0]['scm_id'];
			}
		break;
		case "literatur.php":
			$reiter_view="literatur";
		break;
		case "elearning_interface.php":
			switch ($view) {
				case "edit":
					$reiter_view="elearning_interface_edit";
				break;
				default :
					$reiter_view="elearning_interface_show";
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
				case "openobject_group_schedule":
					$reiter_view="resources_openobject_group_schedule";
				break;

				default :
					$reiter_view="resources";
				break;
			}
		break;
		case "wiki.php":
			switch ($view){
				case "listall":
					$reiter_view="wiki_listall";
				break;
				case "listnew":
					$reiter_view="wiki_listnew";
				break;
				case "export":
					$reiter_view="wiki_export";
				break;
				default:
					$reiter_view="wiki";
				break;
			}
		break;
		default:
			if ($SessSemName["class"]=="inst")
				$reiter_view="institut_main";
			else
				$reiter_view="seminar_main";
		break;
	}


	}

	$reiter->create($structure, $reiter_view);
}
