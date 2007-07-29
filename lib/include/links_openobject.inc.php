<?php
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

require_once ('lib/visual.inc.php');
require_once ('lib/include/reiter.inc.php');
require_once 'lib/functions.php';
require_once ('lib/classes/Modules.class.php');
require_once ('lib/classes/StudipScmEntry.class.php');

$db=new DB_Seminar;
$reiter=new reiter;
$Modules=new Modules;


//load list of used modules
$modules = $Modules->getLocalModules($SessSemName[1]);

if ($modules["scm"]){
	$scms = array_values(StudipScmEntry::GetSCMEntriesForRange($SessSemName[1]));
}
//Reitersytem erzeugen

if ($ILIAS_CONNECT_ENABLE) {
	include_once ("$RELATIVE_PATH_LEARNINGMODULES/lernmodul_db_functions.inc.php");
	include_once ("$RELATIVE_PATH_LEARNINGMODULES/lernmodul_user_functions.inc.php");
}

if ($ELEARNING_INTERFACE_ENABLE) {
	include_once ("$RELATIVE_PATH_ELEARNING_INTERFACE/ObjectConnections.class.php");
}

//Topkats
if ($SessSemName["class"]=="inst") {
	$structure["institut_main"]=array ('topKat' => '', 'name'=>_("&Uuml;bersicht"), 'link'=>"institut_main.php", 'active'=>FALSE);
	if ($modules["forum"])
		$structure["forum"]=array ('topKat' => '', 'name' => _("Forum"), 'link' => "forum.php", 'active' => FALSE);
	if ($modules["personal"])
		$structure["personal"]=array ('topKat' => '', 'name' => _("Personal"), 'link' => "institut_members.php", 'active' => FALSE);
	if ($modules["documents"])
		$structure["folder"]=array ('topKat' => '', 'name' => _("Dateien"), 'link' => "folder.php?cmd=tree", 'active' => FALSE);
	if ($modules["scm"])
		$structure["scm"]=array ('topKat' => '', 'name' => ($scms[0]['tab_name'] ? $scms[0]['tab_name'] : _("Informationen")), 'link' => "scm.php", 'active' => FALSE);
	if ($modules["literature"])
		$structure["literatur"]=array ('topKat' => '', 'name' => _("Literatur zur Einrichtung"), 'link' => "literatur.php", 'active' => FALSE);
	if ($modules["wiki"]){
	  	$structure["wiki"]=array ('topKat' => '', 'name' => _("Wiki"), 'link' => "wiki.php", 'active' => FALSE);
	}

	//topkats for resources management, if module is activated
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvailableResources ($SessSemName[1]))
			$structure["resources"]=array ('topKat' => '', 'name' => _("Ressourcen"), 'link' => "resources.php?view=openobject_main&view_mode=oobj", 'active' => FALSE);
	}
} else {
	$structure["seminar_main"]=array ('topKat' => '', 'name' => _("&Uuml;bersicht"), 'link' => "seminar_main.php", 'active' => FALSE);
	if ($modules["forum"])
		$structure["forum"]=array ('topKat' => '', 'name' => _("Forum"), 'link' => "forum.php?view=reset", 'active' => FALSE);
	if ((!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM) || $rechte) && ($modules["participants"])){
		$structure["teilnehmer"]=array ('topKat' => '', 'name' => _("TeilnehmerInnen"), 'link' => "teilnehmer.php", 'active' => FALSE);
	}
	if ($modules["documents"])
		$structure["folder"]=array ('topKat' => '', 'name' => _("Dateien"), 'link' => "folder.php?cmd=tree", 'active' => FALSE);
	if ($modules["schedule"])
		$structure["dates"]=array ('topKat' => '', 'name' => _("Ablaufplan"), 'link' => "dates.php?cmd=setType&type=all", 'active' => FALSE);
	if ($modules["scm"]) {
		$structure["scm"]=array ('topKat' => '', 'name' => ($scms[0]['tab_name'] ? $scms[0]['tab_name'] : _("Informationen")), 'link' => "scm.php", 'active' => FALSE);
	}
	if ($modules["literature"])
		$structure["literatur"]=array ('topKat' => '', 'name' => _("Literatur"), 'link' => "literatur.php", 'active' => FALSE);
	if ($modules["wiki"]){
	  	$structure["wiki"]=array ('topKat' => '', 'name' => _("Wiki"), 'link' => "wiki.php", 'active' => FALSE);
	}

	//topkats for resources management, if module is activated
	if ($RESOURCES_ENABLE) {
		require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
		if (checkAvailableResources ($SessSemName[1]))
			$structure["resources"]=array ('topKat' => '', 'name' => _("Ressourcen"), 'link' => "resources.php?view=openobject_main&view_mode=oobj", 'active' => FALSE);
	}
}

//topkats for Ilias-learningmodules, if module is activated
if (($ILIAS_CONNECT_ENABLE) && ($modules["ilias_connect"])) {
	if (get_seminar_modules($SessSemName[1]) != false)
		$structure["lernmodule"]=array ('topKat' => '', 'name' => _("Lernmodule"), 'link' => "seminar_lernmodule.php?seminar_id=".$SessSemName[1], 'active' => FALSE);
	elseif  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["lernmodule"]=array ('topKat' => '', 'name' => _("Lernmodule"), 'link' => "seminar_lernmodule.php?view=edit&seminar_id=".$SessSemName[1], 'active' => FALSE);
/*		else $nolink = true;
		if (($nolink != true) AND (get_connected_user_id($auth->auth["uid"]) == false))
			$structure["lernmodule"]=array ('topKat' =>"", 'name' => _("Lernmodule"), 'link' => "migration2studip.php", 'active' => FALSE);/**/
}

//topkats for contentmodules, if elearning-interface is activated
if (($ELEARNING_INTERFACE_ENABLE) && ($modules["elearning_interface"])) {
	if (ObjectConnections::isConnected($SessSemName[1]))
		$structure["elearning_interface"]=array ('topKat' => '', 'name' => _("Lernmodule"), 'link' => "elearning_interface.php?view=show&seminar_id=".$SessSemName[1], 'active' => FALSE);
	elseif  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["elearning_interface"]=array ('topKat' => '', 'name' => _("Lernmodule"), 'link' => "elearning_interface.php?view=edit&seminar_id=".$SessSemName[1], 'active' => FALSE);
}

//topkats for SupportDB, if module is activated
if (($SUPPORT_ENABLE) && ($modules["support"])) {
	$structure["support"]=array ('topKat' => '', 'name' => _("SupportDB"), 'link' => "support.php", 'active' => FALSE);
}

// last topkats, insert new topkats in front of this statement
// create the structure array for activated plugins
if ($PLUGINS_ENABLE){
	// list all activated plugins
	
	$plugins = $Modules->pluginengine->getAllActivatedPlugins();
	
	if (is_array($plugins)){
		foreach ($plugins as $plugin){
			if ($plugin->hasNavigation()){
				$pluginnavi = $plugin->getNavigation();
				$structure["plugin_" . $plugin->getPluginId()] = array('topKat' => '', 'name' => $plugin->getDisplaytitle(),'link' => PluginEngine::getLink($plugin),'active' => false);
				
				$pluginsubmenu["_plugin_" . $plugin->getPluginId()] = array('topKat' => "plugin_" . $plugin->getPluginId(), 'name' => $pluginnavi->getDisplayname(), 'link' => PluginEngine::getLink($plugin), 'active' => false);
				$submenu = $pluginnavi->getSubMenu();
				// create bottomkats for activated plugins
				foreach ($submenu as $submenuitem){
					// create entries in a temporary structure and add it to structure later
					$pluginsubmenu["plugin_" . $plugin->getPluginId() . "_" . $submenuitem->getDisplayname()] = array ('topKat' => "plugin_" . $plugin->getPluginId(), 'name' => $submenuitem->getDisplayname(), 'link' => PluginEngine::getLink($plugin,$submenuitem->getLinkParams()), 'active' => false); 
				}
			}
			else {
				// there's no navigation, show nothing
			}
		}
		// now insert the bottomkats
		$structure = array_merge((array)$structure,(array)$pluginsubmenu);
	}
}

//Bottomkats
if ($SessSemName["class"]=="inst") {
	$structure["_institut_main"]=array ('topKat' => "institut_main", 'name' => _("Info"), 'link' => "institut_main.php", 'active' => FALSE);
	if ($modules["personal"])	
		$structure["institut_members"]=array ( 'topKat' => "personal", 'name' => _("MitarbeiterInnen"), 'link' => "institut_members.php", 'active' => FALSE);
	$structure["institut_veranstaltungen"]=array ('topKat' => "institut_main", 'name' => _("Veranstaltungen"), 'link' => "show_bereich.php?level=s&id=$SessSemName[1]", 'active' => FALSE);
	$structure["timetable"]=array ('topKat' => "institut_main", 'name' => _("Veranstaltungs-Timetable"), 'link' => "mein_stundenplan.php?inst_id=$SessSemName[1]", 'active' => FALSE);
	// $structure["druckansicht_i"]=array ('topKat' => "institut_main", 'name' => "Druckansicht", 'link' => "print_institut.php", 'target' =>"_new", 'active' => FALSE);
	if ($rechte)
		if ($perm->have_perm("admin"))
			$structure["administration_e"]=array ('topKat' => "institut_main", 'name' => _("Administration der Einrichtung"), 'link' => "admin_institut.php?new_inst=TRUE", 'active' => FALSE);
		else
			$structure["administration_e"]=array ('topKat' => "institut_main", 'name' => _("Administration der Einrichtung"), 'link' => "admin_lit_list.php?new_inst=TRUE&view=literatur_inst", 'active' => FALSE);		
} else {
//
	$structure["_seminar_main"]=array ('topKat' => "seminar_main", 'name' => _("Kurzinfo"), 'link' => "seminar_main.php", 'active' => FALSE);
	$structure["details"]=array ('topKat' => "seminar_main", 'name' => _("Details"), 'link' => "details.php", 'active' => FALSE);
	$structure["druckansicht_s"]=array ('topKat' => "seminar_main", 'name' => _("Druckansicht"), 'link' => "print_seminar.php", 'target' => "_new", 'active' => FALSE);
	if ($rechte)
		$structure["administration_v"]=array ('topKat' => "seminar_main", 'name' => _("Administration dieser Veranstaltung"), 'link' => "admin_seminare1.php?new_sem=TRUE", 'active' => FALSE);

	$db->query("SELECT admission_binding FROM seminare WHERE seminar_id = '$SessSemName[1]'");
	$db->next_record();
	if (!$db->f("admission_binding") && !$perm->have_studip_perm("tutor",$SessSemName[1]) && $user->id != "nobody")
		$structure["delete_abo"]=array ('topKat' => "seminar_main", 'name' => _("Austragen aus der Veranstaltung"), 'link' => "meine_seminare.php?auswahl=$SessSemName[1]&cmd=suppose_to_kill", 'isolator' => TRUE);
}
//

if ((!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM)  || $rechte) && ($modules["participants"])){
	$structure["_teilnehmer"]=array ('topKat' => "teilnehmer", 'name' => _("TeilnehmerInnen"), 'link' => "teilnehmer.php", 'active' => FALSE);
}
if ($modules["forum"]) {
	$structure["_forum"]=array ('topKat' => "forum", 'name' => _("Themenansicht"), 'link' => "forum.php?view=".$forum["themeview"], 'active' => FALSE);
	if ($user->id != "nobody") {
		$structure["neue"]=array ('topKat' => "forum", 'name' => _("neue Beiträge"), 'link' => "forum.php?view=neue&sort=age", 'active' => FALSE);
		$structure["flat"]=array ('topKat' => "forum", 'name' => _("letzte Beiträge"), 'link' => "forum.php?view=flat&sort=age", 'active' => FALSE);
		$structure["search"]=array ('topKat' => "forum", 'name' => _("Suchen"), 'link' => "forum.php?view=search&reset=1", 'active' => FALSE);
	}
	$structure["forum_export"]=array ('topKat' => "forum", 'name' => _("Druckansicht"), 'link' => "forum_export.php", 'target' => "_new", 'active' => FALSE);
	if (($rechte) || ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["topic_create_autor"]))
		$structure["neues_thema"]=array ('topKat' => "forum", 'name' => _("neues Thema anlegen"), 'link' => "forum.php?view=".$forum["themeview"]."&neuesthema=TRUE#anker", 'active' => FALSE);
		$structure["admin"]=array ('topKat' => "forum", 'name' => _("Forum anpassen"), 'link' => "forum.php?forumsend=anpassen&view=$view", 'active' => FALSE);
		
}
//
if (($SessSemName["class"]=="sem") && ($modules["schedule"])){
	$structure["_dates"]=array ('topKat' => "dates", 'name' => _("alle Termine"), 'link' => "dates.php?cmd=setType&type=all", 'active' => FALSE);
	$structure["sitzung"]=array ('topKat' => "dates", 'name' => _("Sitzungstermine"), 'link' => "dates.php?cmd=setType&type=1", 'active' => FALSE);
	$structure["andere_t"]=array ('topKat' => "dates", 'name' => _("andere Termine"), 'link' => "dates.php?cmd=setType&type=other", 'active' => FALSE);
	if ($rechte)
		$structure["themen"]=array ('topKat' => "dates", 'name' => _("Ablaufplan bearbeiten"), 'link' => "themen.php?seminar_id=".$SessSemName[1], 'active' => FALSE);
}
//
if ($modules["documents"]) {
	$structure["_folder"]=array ('topKat' => "folder", 'name' => _("Ordneransicht"), 'link' => "folder.php?cmd=tree", 'active' => FALSE);
	$structure["alle_dateien"]=array ('topKat' => "folder", 'name' => _("Alle Dateien"), 'link' => "folder.php?cmd=all", 'active' => FALSE);
}
//
if ($modules["scm"]) {
	foreach($scms as $scm){
		$structure["_scm_" . $scm['scm_id']]=array ('topKat' => "scm", 'name' => $scm['tab_name'] , 'link' => "scm.php?show_scm=" . $scm['scm_id'], 'active' => FALSE);
	}
	if ($perm->have_studip_perm('tutor', $SessSemName[1])){
		$structure["_scm_new_entry"]=array ('topKat' => "scm", 'name' => _("neuen Eintrag anlegen") , 'link' => "scm.php?show_scm=new_entry&i_view=edit", 'active' => FALSE);
	}
}
//
if ($modules["literature"]) {
	if ($SessSemName["class"]=="sem"){
		$structure["_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur"), 'link' => "literatur.php?view=literatur_sem", 'active' => FALSE);
		$structure["_literatur_print"]=array ('topKat' => "literatur", 'name' => _("Druckansicht"), 'link' => "lit_print_view.php?_range_id=" . $SessSemName[1], 'target' => "_blank", 'active' => FALSE);
	}else{
		$structure["_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur"), 'link' => "literatur.php?view=literatur_inst", 'active' => FALSE);
		$structure["_literatur_print"]=array ('topKat' => "literatur", 'name' => _("Druckansicht"), 'link' => "lit_print_view.php?_range_id=" . $SessSemName[1], 'target' => "_blank", 'active' => FALSE);
	}
}

if ($SessSemName["class"]=="sem" && $modules["participants"] && (!is_array($AUTO_INSERT_SEM) || !in_array($SessSemName[1], $AUTO_INSERT_SEM)  || $rechte))
	$structure["statusgruppen"]=array ('topKat' => "teilnehmer", 'name' => _("Funktionen / Gruppen"), 'link' => "statusgruppen.php?view=statusgruppe_sem", 'active' => FALSE);


if ($rechte)
	if (($SessSemName["class"]=="sem") && ($modules["participants"]))
		$structure["Statusgruppen verwalten"]=array ('topKat' => "teilnehmer", 'name' => _("Funktionen / Gruppen verwalten"), 'link' => "admin_statusgruppe.php?view=statusgruppe_sem&new_sem=TRUE&range_id=".$SessSemName[1], 'active' => FALSE);
	elseif (($perm->have_perm("admin")) && ($modules["personal"]))
			$structure["Statusgruppen verwalten"]=array ('topKat' => "personal", 'name' => _("Funktionen / Gruppen verwalten"), 'link' => "admin_statusgruppe.php?view=statusgruppe_inst&new_sem=TRUE&range_id=".$SessSemName[1], 'active' => FALSE);


if (($rechte) && ($modules["literature"]))
	if ($SessSemName["class"]=="sem")
		$structure["admin_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur bearbeiten"), 'link' => "admin_lit_list.php?view=literatur_sem&new_sem=TRUE&_range_id=".$SessSemName[1], 'active' => FALSE);
	else
		$structure["admin_literatur"]=array ('topKat' => "literatur", 'name' => _("Literatur bearbeiten"), 'link' => "admin_lit_list.php?view=literatur_inst&new_inst=TRUE&_range_id=".$SessSemName[1], 'active' => FALSE);

if ($modules["wiki"]) {
	$structure["_wiki"]=array ('topKat' => "wiki", 'name' => _("WikiWikiWeb"), 'link' => "wiki.php", 'active' => FALSE);
	$structure["wiki_listnew"]=array ('topKat' => "wiki", 'name' => _("Neue Seiten"), 'link' => "wiki.php?view=listnew", 'active' => FALSE);
	$structure["wiki_listall"]=array ('topKat' => "wiki", 'name' => _("Alle Seiten"), 'link' => "wiki.php?view=listall", 'active' => FALSE); 
	$structure["wiki_export"]=array ('topKat' => "wiki", 'name' => _("Export"), 'link' => "wiki.php?view=export", 'active' => FALSE); 
}
		
// adding link for configuring the user view user view

if ($rechte && $modules["participants"] && is_array($GLOBALS['TEILNEHMER_VIEW'])) {
	$structure["teilnehmer_view"] = array(topKat => "teilnehmer", name => _("Ansicht konfigurieren"), link => "teilnehmer_view.php", active => FALSE);
}

		

//bottomkats for resources-management, if modul is activated
if ($RESOURCES_ENABLE) {
	$structure["resources_overview"]=array ('topKat' => "resources", 'name' => _("&Uuml;bersicht"), 'link' => "resources.php?view=openobject_main", 'active' => FALSE);
	$structure["resources_details"]=array ('topKat' => "resources", 'name' => _("Details"), 'link' => "resources.php?view=openobject_details", 'active' => FALSE);
	$structure["resources_schedule"]=array ('topKat' => "resources", 'name' => _("Belegung"), 'link' => "resources.php?view=openobject_schedule", 'active' => FALSE);
	$structure["resources_assign"]=array ('topKat' => "resources", 'name' => _("Belegungen bearbeiten"), 'link' => "resources.php?view=openobject_assign", 'active' => FALSE);
	if ($rechte)
		$structure["resources_admin"]=array ('topKat' => "resources", 'name' => _("Ressourcen verwalten"), 'link' => "resources.php", 'active' => FALSE);
}

//bottomkats for Ilias-connect, if modul is activated
if (($ILIAS_CONNECT_ENABLE) && ($modules["ilias_connect"])){
	if (get_seminar_modules($SessSemName[1]) != false)
	{
		if ($SessSemName["class"]=="inst") 
			$structure["lernmodule_show"]=array ('topKat' => "lernmodule", 'name' => _("Lernmodule dieser Einrichtung"), 'link' => "seminar_lernmodule.php?view=show&seminar_id=" . $SessSemName[1], 'active' => FALSE);
		else		
			$structure["lernmodule_show"]=array ('topKat' => "lernmodule", 'name' => _("Lernmodule dieser Veranstaltung"), 'link' => "seminar_lernmodule.php?view=show&seminar_id=" . $SessSemName[1], 'active' => FALSE);
	}
	if  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["lernmodule_edit"]=array ('topKat' => "lernmodule", 'name' => _("Lernmodule hinzuf&uuml;gen / entfernen"), 'link' => "seminar_lernmodule.php?view=edit&seminar_id=" . $SessSemName[1], 'active' => FALSE);
}

//bottomkats for Ilias-connect, if modul is activated
if (($ELEARNING_INTERFACE_ENABLE) && ($modules["elearning_interface"])){
	if (ObjectConnections::isConnected($SessSemName[1]))
	{
		if ($SessSemName["class"]=="inst") 
			$structure["elearning_interface_show"]=array ('topKat' => "elearning_interface", 'name' => _("Lernmodule dieser Einrichtung"), 'link' => "elearning_interface.php?view=show&seminar_id=" . $SessSemName[1], 'active' => FALSE);
		else		
			$structure["elearning_interface_show"]=array ('topKat' => "elearning_interface", 'name' => _("Lernmodule dieser Veranstaltung"), 'link' => "elearning_interface.php?view=show&seminar_id=" . $SessSemName[1], 'active' => FALSE);
	}
	if  ($perm->have_studip_perm("tutor",$SessSemName[1]))
		$structure["elearning_interface_edit"]=array ('topKat' => "elearning_interface", 'name' => _("Lernmodule hinzuf&uuml;gen / entfernen"), 'link' => "elearning_interface.php?view=edit&seminar_id=" . $SessSemName[1], 'active' => FALSE);
}

//bottomkats for SupportDB, if modul is activated
if (($SUPPORT_ENABLE) && ($modules["support"])){
	$structure["support_overview"]=array ('topKat' => "support", 'name' => _("&Uuml;bersicht"), 'link' => "support.php?view=overview", 'active' => FALSE);
	$structure["support_requests"]=array ('topKat' => "support", 'name'=>_("Anfragen"), 'link' => "support.php?view=requests", 'active' => FALSE);
	if ($rechte)
		$structure["support_events"]=array ('topKat' => "resources", 'name' => _("Supportleistungen bearbeiten"), 'link' => "support.php?view=edit_events", 'active' => FALSE);
}

//Infofenstereintraege erzeugen
if ($SessSemName["class"]=="inst") {
	$tooltip = sprintf(_("Sie befinden sich in der Einrichtung: %s, letzter Besuch: %s, Ihr Status in dieser Einrichtung: %s"), $SessSemName[0], date("d.m.Y - H:i:s", object_get_visit($SessSemName[1], $SessSemName["class"])), $SemUserStatus);
} else {
	$tooltip = sprintf(_("Sie befinden sich in der Veranstaltung: %s, letzter Besuch: %s, Ihr Status in dieser Veranstaltung: %s"), $SessSemName[0], date("d.m.Y - H:i:s", object_get_visit($SessSemName[1], $SessSemName["class"])), $SemUserStatus);
}



// check if view is maintained by a plugin
$found = false;
if ($PLUGINS_ENABLE){
	if (is_array($plugins)){
		$pluginid = $_GET["id"];
		// Namen der aufgerufenen Datei aus der URL herausschneiden
		if (strlen($i_page) <= 0){
			$i_page = basename($PHP_SELF);
		} 
		if ($i_page == "plugins.php"){
			foreach ($plugins as $plugin){
				if ($plugin->hasNavigation() && ($plugin->getPluginId() == $pluginid)){
					// Hauptmenü gefunden
					$reiter_view="plugin_" . $plugin->getPluginId();
					$navi = $plugin->getNavigation();
					$submenu = $navi->getSubMenu();
					
					if ($submenu != null) {
                                                foreach ($submenu as $submenuitem) {
                                                        if ($submenuitem->isActive()) {
                                                               $reiter_view="plugin_" . $plugin->getPluginId() . "_" . $submenuitem->getDisplayname();
                                                        }
                                                }
                                        }
					$found= true;
					break;
				}
			}
		}
	}
}

if (!$found){

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
	
	$reiter->create($structure, $reiter_view, $tooltip);
}
?>

