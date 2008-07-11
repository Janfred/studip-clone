<?
# Lifter002: TODO
/*
links_admin.inc.php - Navigation fuer die Verwaltungsseiten von Stud.IP.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de

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

# necessary if you want to include links_admin.inc.php in function/method scope
global  $BANNER_ADS_ENABLE,
        $ELEARNING_INTERFACE_ENABLE,
        $EXPORT_ENABLE,
        $EXTERN_ENABLE,
        $ILIAS_CONNECT_ENABLE,
        $LOG_ENABLE,
        $RESOURCES_ALLOW_ROOM_REQUESTS,
        $RESOURCES_ENABLE,
        $SEM_BEGINN_NEXT,
        $SEM_CLASS,
        $SEM_TYPE,
        $SMILEYADMIN_ENABLE,
        $VOTE_ENABLE;

global  $auth, $perm, $sess, $user;

global  $admin_admission_data,
        $adminarea_sortby,
        $admin_dates_data,
        $admin_inst_id,
        $archiv_assi_data,
        $archive_kill,
        $back_jump,
        $conditions,
        $_fullname_sql,
        $i_page,
        $i_view,
        $links_admin_data,
        $list,
        $msg,
        $my_inst,
        $new_inst,
        $new_sem,
        $news_range_id,
        $news_range_name,
        $quit,
        $select_all,
        $select_inactive,
        $select_none,
        $select_old,
        $select_sem_id,
        $sem_create_data,
        $SessSemName,
        $srch_doz,
        $srch_exp,
        $srch_fak,
        $srch_inst,
        $srch_result,
        $srch_sem,
        $srch_send,
        $structure,
        $term_metadata,
        $view,
        $view_mode;


if ($perm->have_perm("tutor")) {	// Navigationsleiste ab status "Tutor"

	require_once 'config.inc.php';
	require_once 'lib/admin_semester.inc.php';
	require_once 'lib/dates.inc.php';
	require_once 'lib/msg.inc.php';
	require_once 'lib/visual.inc.php';
	require_once 'lib/include/reiter.inc.php';
	require_once 'lib/functions.php';
	require_once 'lib/classes/Modules.class.php';
	require_once 'lib/classes/SemesterData.class.php';
	require_once "lib/classes/LockRules.class.php";
	require_once "lib/classes/AuxLockRules.class.php";

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$cssSw=new cssClassSwitcher;
	$Modules=new Modules;
	$semester=new SemesterData;
	$lock_rules=new LockRules;
	$all_lock_rules=$lock_rules->getAllLockRules();
	$aux_rules=new AuxLockRules();
	$all_aux_rules=$aux_rules->getAllLockRules();

	$sess->register("links_admin_data");
	$sess->register("sem_create_data");
	$sess->register("admin_dates_data");
	/**
	* We use this helper-function, to reset all the data in the adminarea
	*
	* There are much pages with an own temporary set of data. Please use
	* only this function to add defaults or clear data.
	*/
	function reset_all_data() {
		global $links_admin_data, $sem_create_data, $admin_dates_data, $admin_admission_data, $archiv_assi_data,
			$term_metadata, $news_range_id, $news_range_name;

		$links_admin_data='';
		$sem_create_data='';
		$admin_dates_data='';
		$admin_admission_data='';
		$admin_rooms_data='';
		$archiv_assi_data='';
		$term_metadata='';
		$links_admin_data["select_old"]=TRUE;
		$links_admin_data['srch_sem'] =& $GLOBALS['_default_sem'];
	}


	//a Veranstaltung was selected in the admin-search kann viellecht weg
	if (isset($select_sem_id)) {
		reset_all_data();
		closeObject();
		openSem($select_sem_id);
	//a Veranstaltung which was already open should be administrated
	} elseif (($SessSemName[1]) && ($new_sem)) {
		reset_all_data();
		$links_admin_data["referred_from"]="sem";
	}

	//a Einrichtung was selected in the admin-search
	if (($admin_inst_id) && ($admin_inst_id != "NULL")) {
		reset_all_data();
		closeObject();
		openInst($admin_inst_id);
	//a Einrichtung which was already open should be administrated
	} elseif (($SessSemName[1]) && ($new_inst)) {
		reset_all_data();
		$links_admin_data["referred_from"]="inst";
	}

	//Veranstaltung was selected but it is on his way to hell.... we close it at this point
	if (($archive_kill) && ($SessSemName[1] == $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])) {
		//reset_all_data();
		closeObject();
	}

	//a new session in the adminarea...
	if ((($i_page == "adminarea_start.php") && ($list)) || ($quit)) {
		reset_all_data();
		closeObject();
	} elseif ($i_page== "adminarea_start.php")
		$list=TRUE;


	if ($adminarea_sortby) {
		$links_admin_data["sortby"]=$adminarea_sortby;
		$list=TRUE;
	} else
		$links_admin_data["sortby"]="Name";

	if ($view)
		$links_admin_data["view"]=$view;

	if ($srch_send) {
		$links_admin_data["srch_sem"]= trim($srch_sem);
		$links_admin_data["srch_doz"]= trim($srch_doz);
		$links_admin_data["srch_inst"]= trim($srch_inst);
		$links_admin_data["srch_fak"]= trim($srch_fak);
		$links_admin_data["srch_exp"]= trim($srch_exp);
		$links_admin_data["select_old"]=$select_old;
		$links_admin_data["select_inactive"]=$select_inactive;
		$links_admin_data["srch_on"]=TRUE;
		$list=TRUE;
	}
	if ($SessSemName[1])
		$modules = $Modules->getLocalModules($SessSemName[1]);

	//if the user selected the information field at Einrichtung-selection....
	if ($admin_inst_id == "NULL")
		$list=TRUE;

	//user wants to create a new Einrichtung
	if ($i_view=="new")
		$links_admin_data='';

	//here are all the pages/views listed, which require the search form for Einrichtungen
	if ($i_page == "admin_institut.php"
			OR ($i_page == "admin_roles.php" AND $links_admin_data["view"] == "statusgruppe_inst")
			OR ($i_page == "admin_lit_list.php" AND $links_admin_data["view"] == "literatur_inst")
			OR $i_page == "inst_admin.php"
			OR ($i_page == "admin_news.php" AND $links_admin_data["view"] == "news_inst")
			OR ($i_page == "admin_modules.php" AND $links_admin_data["view"] == "modules_inst")
			OR ($i_page == "admin_extern.php" AND $links_admin_data["view"] == "extern_inst")
			OR ($i_page == "admin_vote.php" AND $links_admin_data["view"] == "vote_inst")
			OR ($i_page == "admin_evaluation.php" AND $links_admin_data["view"] == "eval_inst")
			) {

		$links_admin_data["topkat"]="inst";
	}

	//here are all the pages/views listed, which require the search form for Veranstaltungen
	if ($i_page == "admin_seminare1.php"
			OR $i_page == "themen.php"
			OR $i_page == "raumzeit.php"
			OR $i_page == "admin_admission.php"
			OR $i_page == "admin_room_requests.php"
			OR ($i_page == "admin_statusgruppe.php" AND $links_admin_data["view"]=="statusgruppe_sem")
			OR ($i_page == "admin_lit_list.php" AND $links_admin_data["view"]=="literatur_sem")
			OR $i_page == "archiv_assi.php"
			OR $i_page == "admin_visibility.php"
			OR $i_page == "admin_aux.php"
			OR $i_page == "admin_lock.php"
			OR $i_page == "copy_assi.php"
			OR $i_page == "adminarea_start.php"
			OR ($i_page == "admin_modules.php" AND $links_admin_data["view"] == "modules_sem")
			OR ($i_page == "admin_news.php" AND $links_admin_data["view"]=="news_sem")
			OR ($i_page == "admin_vote.php" AND $links_admin_data["view"]=="vote_sem")
			OR ($i_page == "admin_evaluation.php" AND $links_admin_data["view"]=="eval_sem")
			) {

		$links_admin_data["topkat"]="sem";
	}

	//remember the open topkat
	if ($view_mode=="sem")
		$links_admin_data["topkat"]="sem";
	elseif ($view_mode=="inst")
		$links_admin_data["topkat"]="inst";
	if (!$links_admin_data["topkat"])
		$links_admin_data["topkat"]="global";
	$view_mode = $links_admin_data["topkat"];

	//Wenn nur ein Institut verwaltet werden kann, immer dieses waehlen (Auswahl unterdruecken)
	if ((!$SessSemName[1]) && ($list) && ($view_mode=="inst")) {
		if (!$perm->have_perm("root") && !$perm->is_fak_admin($auth->auth["uid"])) {
			$db->query("SELECT Institute.Institut_id FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms IN ('admin', 'dozent', 'tutor') ORDER BY Name");

			if ($db->nf() ==1) {
				$db->next_record();
				reset_all_data();
				openInst($db->f("Institut_id"));
			}
		}
	}

	//Reitersytem erzeugen
	$reiter=new reiter;

	//Ruecksprung-Reiter vorbereiten
	if ($SessSemName["class"] == "inst") {
		if ($links_admin_data["referred_from"] == "inst")
			$back_jump= _("zur&uuml;ck zur ausgew&auml;hlten Einrichtung");
		else
			$back_jump= _("zur ausgew&auml;hlten Einrichtung");
	}
	if ($SessSemName["class"] == "sem") {
		if (($links_admin_data["referred_from"] == "sem") && (!$archive_kill) && (!$links_admin_data["assi"]))
			$back_jump= _("zur&uuml;ck zur ausgew&auml;hlten Veranstaltung");
		elseif (($links_admin_data["referred_from"] == "assi") && (!$archive_kill))
			$back_jump= _("zur neu angelegten Veranstaltung");
		elseif (!$links_admin_data["assi"])
			$back_jump= _("zur ausgew&auml;hlten Veranstaltung");
	}

	//Topkats
	if ($perm->have_perm("tutor")) {
		if (($SessSemName["class"] == "sem") && (!$archive_kill))
			$structure["veranstaltungen"]=array ('topKat'=>"", 'name'=>_("Veranstaltungen"), 'link'=>"admin_seminare1.php", 'active'=>FALSE);
		else
			$structure["veranstaltungen"]=array ('topKat'=>"", 'name'=>_("Veranstaltungen"), 'link'=>"adminarea_start.php?list=TRUE", 'active'=>FALSE);
		$structure["einrichtungen"]=array ('topKat'=>"", 'name'=>_("Einrichtungen"), 'link'=>"admin_lit_list.php?list=TRUE&view=literatur_inst", 'active'=>FALSE);
	}

	if ($perm->have_perm("admin")) {
		$structure["einrichtungen"]=array ('topKat'=>"", 'name'=>_("Einrichtungen"), 'link'=>"admin_institut.php?list=TRUE", 'active'=>FALSE);
		$structure["global"]=array ('topKat'=>"", 'name'=>_("globale Einstellungen"), 'link'=>"new_user_md5.php", 'active'=>FALSE);
	}

	// "Log" tab for log view and administration (Root only)
	if ($perm->have_perm("root") && $LOG_ENABLE) {
		$structure["log"]=array ('topKat'=>"", 'name'=>_("Log"), 'link'=>"show_log.php", 'active'=>FALSE);
	}

	$structure["modules"]=array ('topKat'=>"", 'name'=>_("Tools"), 'link'=>"export.php", 'active'=>FALSE);

	if ($SessSemName["class"] == "inst")
		$structure["back_jump"]=array ('topKat'=>"", 'name'=>$back_jump, 'link'=>"institut_main.php?auswahl=".$SessSemName[1], 'active'=>FALSE);
	elseif (($SessSemName["class"] == "sem") && (!$archive_kill) && (!$links_admin_data["assi"]))
		$structure["back_jump"]=array ('topKat'=>"", 'name'=>$back_jump, 'link'=>"seminar_main.php?auswahl=".$SessSemName[1], 'active'=>FALSE);

	if ($perm->have_perm("root") && $GLOBALS["PLUGINS_ENABLE"]) {
		$structure["plugins"]=array ('topKat'=>"", 'name'=>_("Administrations-Plugins"), 'link'=>PluginEngine::getLinkToAdministrationPlugin(), 'active'=>FALSE);
	}

	//Bottomkats
	$structure["grunddaten_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Grunddaten"), 'link'=>"admin_seminare1.php?list=TRUE", 'active'=>FALSE);
	$structure["zeiten"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Zeiten / Räume"), 'link'=>"raumzeit.php?list=TRUE", 'active'=>FALSE, 'isolator'=>TRUE);
	if (($modules["schedule"]) || (!$SessSemName[1]))
		$structure["ablaufplan"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Ablaufplan"), 'link'=>"themen.php?list=TRUE", 'active'=>FALSE);
	$structure["news_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("News"), 'link'=>"admin_news.php?list=TRUE&view=news_sem", 'active'=>FALSE, 'isolator'=>TRUE);
	if (($modules["literature"]) || (!$SessSemName[1]))
		$structure["literatur_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Literatur"), 'link'=>"admin_lit_list.php?list=TRUE&view=literatur_sem", 'active'=>FALSE);
	if ($VOTE_ENABLE)
		$structure["vote_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Votings und Tests"), 'link'=>"admin_vote.php?view=vote_sem", 'active'=>FALSE);
	if ($VOTE_ENABLE)
		$structure["eval_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Evaluationen"), 'link'=>"admin_evaluation.php?view=eval_sem", 'active'=>FALSE);

	$structure["zugang"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Zugangsberechtigungen"), 'link'=>"admin_admission.php?list=TRUE", 'active'=>FALSE, 'isolator'=>TRUE);
	if (($modules["participants"]) || (!$SessSemName[1]))
		$structure["statusgruppe_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Gruppen&nbsp;/&nbsp;Funktionen"), 'link'=>"admin_statusgruppe.php?list=TRUE", active=>FALSE);
	$structure["modules_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Module/Plugins"), 'link'=>"admin_modules.php?list=TRUE&view=modules_sem", 'active'=>FALSE);
	$sem_create_perm = (in_array(get_config('SEM_CREATE_PERM'), array('root','admin','dozent')) ? get_config('SEM_CREATE_PERM') : 'dozent');
	if ($perm->have_perm($sem_create_perm)) {
		$structure["copysem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Veranstaltung&nbsp;kopieren"), 'link'=>"copy_assi.php?list=TRUE&new_session=TRUE", 'active'=>FALSE, 'isolator'=>TRUE);
		$structure["new_sem"]=array ('topKat'=>"veranstaltungen", 'name'=>_("neue&nbsp;Veranstaltung&nbsp;anlegen"), 'link'=>"admin_seminare_assi.php?new_session=TRUE", 'active'=>FALSE);
		if (get_config('ALLOW_DOZENT_ARCHIV') || $perm->have_perm("admin")){
			$structure["archiv"]=array ('topKat'=>"veranstaltungen", 'name'=>_("archivieren"), 'link'=>"archiv_assi.php?list=TRUE&new_session=TRUE", 'active'=>FALSE);
		}
		if (get_config('ALLOW_DOZENT_VISIBILITY') || $perm->have_perm("admin")){
			$structure["visibility"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Sichtbarkeit"), 'link'=>"admin_visibility.php?list=TRUE&new_session=TRUE", 'active'=>FALSE, 'newline'=>TRUE);

		}
	}

	if ($SEMINAR_LOCK_ENABLE && $perm->have_perm("admin"))
		$structure["lock"]=array ('topKat'=>"veranstaltungen", 'name'=>_("Sperren"), 'link'=>"admin_lock.php?list=TRUE&new_session=TRUE", 'active'=>FALSE);

	$structure["aux"]=array (topKat=>"veranstaltungen", name=>_("Zusatzangaben"), link=>"admin_aux.php?list=TRUE&new_session=TRUE", active=>FALSE);

	//
	if ($perm->have_perm("admin")) {
		$structure["grunddaten_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("Grunddaten"), 'link'=>"admin_institut.php?list=TRUE", 'active'=>FALSE);
		$structure["mitarbeiter"]=array ('topKat'=>"einrichtungen", 'name'=>_("Mitarbeiter"), 'link'=>"inst_admin.php?list=TRUE", 'active'=>FALSE);
		$structure["statusgruppe_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("Gruppen&nbsp;/&nbsp;Funktionen"), 'link'=>"admin_roles.php?list=TRUE", 'active'=>FALSE);
	}

	$structure["literatur_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("Literatur"), 'link'=>"admin_lit_list.php?list=TRUE&view=literatur_inst", 'active'=>FALSE);
	$structure["news_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("News"), 'link'=>"admin_news.php?list=TRUE&view=news_inst", 'active'=>FALSE);

	if ($VOTE_ENABLE)
		$structure["vote_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("Votes"), 'link'=>"admin_vote.php?view=vote_inst", 'active'=>FALSE);

	if ($VOTE_ENABLE)
		$structure["eval_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("Evaluationen"), 'link'=>"admin_evaluation.php?view=eval_inst", 'active'=>FALSE);

	if ($perm->have_perm("admin"))
		$structure["modules_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("Module"), 'link'=>"admin_modules.php?list=TRUE&view=modules_inst", 'active'=>FALSE);

	if ($EXTERN_ENABLE && $perm->have_perm("admin"))
		$structure["extern_inst"] = array("topKat" => "einrichtungen", "name" => _("externe&nbsp;Seiten"), "link" => "admin_extern.php?list=TRUE&view=extern_inst", "active" => FALSE);
	if ($perm->is_fak_admin())
		$structure["new_inst"]=array ('topKat'=>"einrichtungen", 'name'=>_("neue&nbsp;Einrichtung"), 'link'=>"admin_institut.php?i_view=new", 'active'=>FALSE);
	//
	if ($EXPORT_ENABLE)
		$structure["export"]=array ('topKat'=>"modules", 'name'=>_("Export"), 'link'=>"export.php", 'active'=>FALSE);
	if ($ILIAS_CONNECT_ENABLE)
		$structure["lernmodule"]=array ('topKat'=>"modules", 'name'=>_("Lernmodule"), 'link'=>"admin_lernmodule.php", 'active'=>FALSE);
	if ($RESOURCES_ENABLE)
		$structure["resources"]=array ('topKat'=>"modules", 'name'=>_("Ressourcenverwaltung"), 'link'=>"resources.php", 'active'=>FALSE);
	if ($perm->have_perm("admin")){
		$structure["show_admission"]=array ('topKat'=>"modules", 'name'=>_("laufende&nbsp;Anmeldeverfahren"), 'link'=>"show_admission.php", 'active'=>FALSE);
		$structure["lit_overview"]=array ('topKat'=>"modules", 'name'=>_("Literatur&uuml;bersicht"), 'link'=>"admin_literatur_overview.php", 'active'=>FALSE);
	}
	if ($perm->have_perm("admin")) {
		$structure["new_user"]=array ('topKat'=>"global", 'name'=>_("Benutzer"), 'link'=>"new_user_md5.php", 'active'=>FALSE);
		$structure["range_tree"]=array ('topKat'=>"global", 'name'=>_("Einrichtungshierarchie"), 'link'=>"admin_range_tree.php", 'active'=>FALSE);
		if ($perm->is_fak_admin()) {
			$structure["sem_tree"]=array ('topKat'=>"global", 'name'=>_("Veranstaltungshierarchie"), 'link'=>"admin_sem_tree.php", 'active'=>FALSE);
		}
		$structure["aux_adjust"]=array (topKat=>"global", name=>("Zusatzangaben definieren"), link=>"admin_aux_adjust.php", active=>FALSE);
		if ($SEMINAR_LOCK_ENABLE)
		$structure["lock_adjust"]=array (topKat=>"global", name=>("Sperrebenen anpassen"), link=>"admin_lock_adjust.php", active=>FALSE);
	}

	if($perm->have_perm('dozent') && $GLOBALS['STM_ENABLE']){
		$structure["stm_instance_assi"]=array ('topKat'=>"modules", 'name'=>_("Konkrete Studienmodule"), 'link'=>"stm_instance_assi.php", 'active'=>FALSE);
	}
	if ($perm->have_perm("root")) {
		if($GLOBALS['STM_ENABLE']) $structure["stm_abstract_assi"]=array ('topKat'=>"modules", 'name'=>_("Allgemeine Studienmodule"), 'link'=>"stm_abstract_assi.php", 'active'=>FALSE);
		if ($ELEARNING_INTERFACE_ENABLE){
			$structure["elearning_interface"]=array ('topKat'=>"modules", 'name'=>_("Lernmodul-Schnittstelle"), 'link'=>"admin_elearning_interface.php", 'active'=>FALSE);
		}
		$structure["studiengang"]=array ('topKat'=>"global", 'name'=>_("Studieng&auml;nge"), 'link'=>"admin_studiengang.php", 'active'=>FALSE);
		$structure["datafields"]=array ('topKat'=>"global", 'name'=>_("Datenfelder"), 'link'=>"admin_datafields.php", 'active'=>FALSE);
		$structure["config"]=array ('topKat'=>"global", 'name'=>_("Konfiguration"), 'link'=>"admin_config.php", 'active'=>FALSE);
		if('active_sessions' == PHPLIB_SESSIONDATA_TABLE){
			$structure["sessions"]=array ('topKat'=>"modules", 'name'=>_("Sessions"), 'link'=>"view_sessions.php", 'active'=>FALSE);
		}
		$structure["integrity"]=array ('topKat'=>"modules", 'name'=>_("DB&nbsp;Integrit&auml;t"), 'link'=>"admin_db_integrity.php", 'active'=>FALSE);
		if ($BANNER_ADS_ENABLE)  {
			$structure["bannerads"]=array ('topKat'=>"global", 'name'=>_("Werbebanner"), 'link'=>"admin_banner_ads.php", 'active'=>FALSE);
		}
		if ($SMILEYADMIN_ENABLE) {
			$structure["smileyadmin"]=array ('topKat'=>"global", 'name'=>_("Smileys"), 'link'=>"admin_smileys.php", 'active'=>FALSE);
		}

		$structure["semester"]=array ('topKat'=>"global", 'name'=>_("Semester"), 'link'=>"admin_semester.php", 'active'=>FALSE);
    	$structure["admin_teilnehmer_view"]=array (topKat=>"global", name=>_("Teilnehmeransicht"), link=>"admin_teilnehmer_view.php", active=>FALSE);

		if ($LOG_ENABLE) {
			$structure["show_log"]=array ('topKat'=>"log", 'name'=>_("Log"), 'link'=>"show_log.php", 'active'=>FALSE);
			$structure["admin_log"]=array ('topKat'=>"log", 'name'=>_("Einstellungen"), 'link'=>"admin_log.php", 'active'=>FALSE);
		}
	}
	// create sublinks for administration plugins
	if ($GLOBALS["PLUGINS_ENABLE"] && $perm->have_perm("root")){
		$persist = PluginEngine::getPluginPersistence("Administration");
		$plugins = $persist->getAllActivatedPlugins();
		if (is_array($plugins)){
			foreach ($plugins as $adminplugin) {
				if ($adminplugin->hasNavigation()){
					$nav = $adminplugin->getNavigation();
					$structure["plugins_" . $adminplugin->getPluginid()]=array ('topKat'=>"plugins", 'name'=>$nav->getDisplayname(), 'link'=> $nav->getLink(), 'active'=>FALSE);
				}
			}
		}

	}
	//Reitersystem Ende

	//View festlegen
	switch ($i_page) {
		case "admin_room_requests.php" :
			$reiter_view="zeiten";
		break;
		case "admin_admission.php" :
			$reiter_view="zugang";
		break;
		case "admin_bereich.php" :
			$reiter_view="bereich";
		break;
		case "themen.php" :
			$reiter_view="ablaufplan";
		break;
		case "admin_db_integrity.php" :
			$reiter_view = "integrity";
		break;
		case "admin_fach.php" :
			$reiter_view="fach";
		break;
		case "admin_semester.php":
			$reiter_view ="semester";
		break;
    	case "admin_teilnehmer_view.php";
    		$reiter_view = "admin_teilnehmer_view";
    	break;
		case "admin_institut.php" :
			$reiter_view="grunddaten_inst";
		break;
		case "admin_lit_list.php":
		case "lit_search.php":
		case "admin_lit_element.php":
			if ($links_admin_data["topkat"] == "sem")
				$reiter_view="literatur_sem";
			else
				$reiter_view="literatur_inst";
		break;
		case "raumzeit.php" :
			$reiter_view="zeiten";
		break;
		case "admin_news.php":
			if ($links_admin_data["topkat"] == "sem")
				$reiter_view="news_sem";
			elseif ($links_admin_data["topkat"] == "inst")
				$reiter_view="news_inst";
		break;
		case "admin_vote.php":
			if ($links_admin_data["topkat"] == "sem")
				$reiter_view="vote_sem";
			elseif ($links_admin_data["topkat"] == "inst")
				$reiter_view="vote_inst";
		break;
		case "admin_evaluation.php":
			if ($links_admin_data["topkat"] == "sem")
				$reiter_view="eval_sem";
			elseif ($links_admin_data["topkat"] == "inst")
				$reiter_view="eval_inst";
		break;
		case "admin_seminare1.php":
			$reiter_view="grunddaten_sem";
		break;
		case "admin_seminare_assi.php":
			$reiter_view="new_sem";
		break;
		case "admin_statusgruppe.php":
			$reiter_view="statusgruppe_sem";
		break;
		case "admin_roles.php":
			$reiter_view="statusgruppe_inst";
		break;
		case "admin_modules.php":
			if ($links_admin_data["topkat"] == "sem")
				$reiter_view="modules_sem";
			else
				$reiter_view="modules_inst";
		break;
		case "admin_aux_adjust.php":
			$reiter_view="aux_adjust";
                break;
	        case "admin_lock_adjust.php":
		        $reiter_view="lock_adjust";
		break;
		case "admin_studiengang.php":
			$reiter_view="studiengang";
		break;
		case "adminarea_start.php" :
			$reiter_view="(veranstaltungen)";
		break;
		case "archiv_assi.php":
			$reiter_view="archiv";
		break;
		case "admin_visibility.php":
			$reiter_view="visibility";
		break;
		case "admin_aux.php":
			$reiter_view="aux";
		break;
		case "admin_lock.php":
			$reiter_view="lock";
		break;
		case "copy_assi.php":
			$reiter_view="copysem";
		break;
		case "new_user_md5.php":
			$reiter_view="new_user";
		break;
		case "view_sessions.php":
			$reiter_view="sessions";
		break;
		case "inst_admin.php":
			$reiter_view="mitarbeiter";
		break;
		case "show_admission.php":
			$reiter_view="show_admission";
		break;
		case "admin_literatur_overview.php":
			$reiter_view="lit_overview";
		break;
		case "export.php":
			$reiter_view="export";
		break;
		case "admin_elearning_interface.php":
			$reiter_view="elearning_interface";
		break;
		case "admin_lernmodule.php":
			$reiter_view="lernmodule";
		break;
		case "admin_range_tree.php":
			$reiter_view="range_tree";
		break;
		case "admin_sem_tree.php":
			$reiter_view="sem_tree";
		break;
		case "admin_datafields.php":
			$reiter_view="datafields";
		break;
		case "admin_extern.php":
			$reiter_view = "extern_inst";
		break;
		case "admin_banner_ads.php":
			$reiter_view = "bannerads";
		break;
		case "admin_smileys.php":
			$reiter_view = "smileyadmin";
		break;
		case "admin_config.php":
			$reiter_view = "config";
			break;
		case "show_log.php":
			$reiter_view = "show_log";
			break;
		case "admin_log.php":
			$reiter_view = "admin_log";
			break;
		case "plugins.php":
			// check if view is delegated to a bottomkat
			$pid = PluginEngine::getCurrentPluginId();
			$reiter_view = "plugins_" . $pid;
			$ppersist = PluginEngine::getPluginPersistence();
			$viewplugin = $ppersist->getPlugin($pid);
			if (PluginEngine::getTypeOfPlugin($viewplugin) <> "Administration"){
				$reiter_view = "plugins";
			}
		break;
		default:
		$reiter_view = substr($i_page,0, strpos($i_page,'.php'));

	}


	//Einheitliches Auswahlmenu fuer Einrichtungen
	if (((!$SessSemName[1]) || ($SessSemName["class"] == "sem")) && ($list) && ($view_mode == "inst")) {
		//Save data back to database and start a connection  - so we avoid some problems with large search results and data is writing back to db too late
		page_close();

		if(!is_object($header_controller)) include ('lib/include/header.php');   // Output of Stud.IP head
		$reiter->create($structure, $reiter_view);

		?>
		<table width="100%" cellspacing=0 cellpadding=0 border=0>
		<?
		if ($msg) {
			echo "<tr> <td class=\"blank\" colspan=2><br />";
			parse_msg ($msg);
			echo "</td></tr>";
		}
		?>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<form name="links_admin_search" action="<? echo $GLOBALS['PHP_SELF'],"?", "view=$view"?>" method="POST">
				<table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
					<tr>
						<td class="steel1">
							<font size=-1><br /><b><?=_("Bitte w&auml;hlen Sie die Einrichtung aus, die Sie bearbeiten wollen:")?></b><br/>&nbsp; </font>
						</td>
					</tr>
					<tr>
						<td class="steel1">
						<font size=-1><select name="admin_inst_id" size="1" style="vertical-align:middle">
						<?
						if ($auth->auth['perm'] == "root"){
							$db->query("SELECT Institut_id, Name, 1 AS is_fak  FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
						} elseif ($auth->auth['perm'] == "admin") {
							$db->query("SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
										WHERE a.user_id='$user->id' AND a.inst_perms='admin' ORDER BY is_fak,Name");
						} else {
							$db->query("SELECT a.Institut_id,Name FROM user_inst a LEFT JOIN Institute b USING (Institut_id) WHERE inst_perms IN('tutor','dozent') AND user_id='$user->id' ORDER BY Name");
						}

						printf ("<option value=\"NULL\">%s</option>\n", _("-- bitte Einrichtung ausw&auml;hlen --"));
						while ($db->next_record()){
							printf ("<option value=\"%s\" style=\"%s\">%s </option>\n", $db->f("Institut_id"),($db->f("is_fak") ? "font-weight:bold;" : ""), htmlReady(substr($db->f("Name"), 0, 70)));
							if ($db->f("is_fak")){
								$db2->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name");
								while ($db2->next_record()){
									printf("<option value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s </option>\n", $db2->f("Institut_id"), htmlReady(substr($db2->f("Name"), 0, 70)));
								}
							}
						}
						?>
					</select></font>&nbsp;
					<input type="IMAGE" <?=makeButton("auswaehlen", "src")?> border=0 align="absmiddle" value="bearbeiten">
					</td>
				</tr>
				<tr>
					<td class="steel1">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td class="blank">
						&nbsp;
					</td>
				</tr>
			</table>
			</form>
		</tr>
		</td>
		</table>
		<?
		page_close();
		die;
	}

	//Einheitliches Seminarauswahlmenu, wenn kein Seminar gewaehlt ist
	if (((!$SessSemName[1]) || ($SessSemName["class"] == "inst")) && ($list) && ($view_mode == "sem")) {
		//Save data back to database and start a connection  - so we avoid some problems with large search results and data is writing back to db too late
		page_close();
		if(!is_object($header_controller)) include ('lib/include/header.php');   // Output of Stud.IP head
		$reiter->create($structure, $reiter_view);
		?>
		<table width="100%" cellspacing=0 cellpadding=0 border=0>
		<?
		if ($msg)
			parse_msg ($msg);
		?>
		<tr>
			<td class="blank" colspan=2>&nbsp;
		<?
		//Umfangreiches Auswahlmenu nur ab Admin, alles darunter sollte eine uberschaubare Anzahl von Seminaren haben
		if ($perm->have_perm("admin")) {
		?>
			<form name="links_admin_search" action="<? echo $GLOBALS['PHP_SELF'] ?>" method="POST">
				<table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
					<tr>
						<td class="steel1" colspan=5>
								<font size=-1><br /><b><?=_("Sie k&ouml;nnen die Auswahl der Veranstaltungen eingrenzen:")?></b><br/>&nbsp; </font>
						</td>
					</tr>
					<tr>
						<td class="steel1">
							<font size=-1><?=_("Semester:")?></font><br />
							<?=SemesterData::GetSemesterSelector(array('name'=>'srch_sem'), $links_admin_data["srch_sem"])?>
						</td>

						<td class="steel1">
						<?
						if ($perm->have_perm("root")) {
							$db->query("SELECT Institut_id, Name FROM Institute WHERE Institut_id!=fakultaets_id ORDER BY Name");
						} else {
							$db->query("SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
								WHERE a.user_id='$user->id' AND a.inst_perms='admin' ORDER BY is_fak,Name");
						}
						?>
						<font size=-1><?=_("Einrichtung:")?></font><br />
						<select name="srch_inst">
							<option value=0><?=_("alle")?></option>
							<?
							while ($db->next_record()) {
								$my_inst[]=$db->f("Institut_id");
								if ($links_admin_data["srch_inst"] == $db->f("Institut_id"))
									echo"<option selected value=".$db->f("Institut_id").">".substr($db->f("Name"), 0, 30)."</option>";
								else
									echo"<option value=".$db->f("Institut_id").">".substr($db->f("Name"), 0, 30)."</option>";
								if ($db->f("is_fak")) {
									$db2->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND institut_id!='" .$db->f("Institut_id") . "'");
									while ($db2->next_record()) {
										if ($links_admin_data["srch_inst"] == $db2->f("Institut_id"))
											echo"<option selected value=".$db2->f("Institut_id").">&nbsp;&nbsp;&nbsp;".substr($db2->f("Name"), 0, 30)."</option>";
										else
											echo"<option value=".$db2->f("Institut_id").">&nbsp;&nbsp;&nbsp;".substr($db2->f("Name"), 0, 30)."</option>";
										$my_inst[]=$db2->f("Institut_id");
									}
								}
							}
							?>
						</select>
						</td>
						<td class="steel1">
						<?
						if (($perm->have_perm("admin")) && (!$perm->have_perm("root"))) {
							?>
							<font size=-1><?=_("DozentIn:")?></font><br />
							<select name="srch_doz">
							<option value=0><?=_("alle")?></option>
							<?
							if (is_array($my_inst)) {
								$inst_id_query = "'";
								$inst_id_query.= join ("', '",$my_inst);
								$inst_id_query.= "'";

								$query="SELECT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] ." AS fullname, Institut_id FROM user_inst  LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms='dozent' AND institut_id IN ($inst_id_query) GROUP BY auth_user_md5.user_id ORDER BY Nachname ";
								$db->query($query);
								if ($db->num_rows()) {
									while ($db->next_record()) {
										if ($links_admin_data["srch_doz"] == $db->f("user_id"))
											echo"<option selected value=".$db->f("user_id").">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
										else
											echo"<option value=".$db->f("user_id").">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
									}
								}
							}
							?>
							</select>
							<?
						}

						if ($perm->have_perm("root")) {
							$db->query("SELECT Institut_id,Name FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
							?>
							<font size=-1><?=_("Fakult&auml;t:")?></font><br />
							<select name="srch_fak">
								<option value=0><?=_("alle")?></option>
								<?
								while ($db->next_record()) {
									if ($links_admin_data["srch_fak"] == $db->f("Institut_id"))
										echo"<option selected value=".$db->f("Institut_id").">".substr($db->f("Name"), 0, 30)."</option>";
									else
										echo"<option value=".$db->f("Institut_id").">".substr($db->f("Name"), 0, 30)."</option>";
								}
								?>
							</select>
							<?
						}
						?>&nbsp;
						</td>
						<td class="steel1">
							<font size=-1><?=_("freie Suche:")?></font><br /><input type="TEXT" name="srch_exp" maxlength=255 size=20 value="<? echo $links_admin_data["srch_exp"] ?>" />
							<input type="HIDDEN" name="srch_send" value="TRUE" />
						</td>
						<td class="steel1" valign="bottom">
							&nbsp; <br/>
							<input type="IMAGE" <?=makeButton("anzeigen", "src")?> border=0 name="anzeigen" value="<?=_("Anzeigen")?>" />
							<input type="HIDDEN" name="view" value="<? echo $links_admin_data["view"]?>" />
						</td>
					</tr>
					<?
					//more Options for archiving
					if ($i_page == "archiv_assi.php") {
						?>
						<tr>
							<td class="steel1" colspan=6>
								<br />&nbsp;<font size=-1><input type="CHECKBOX" name="select_old" <? if ($links_admin_data["select_old"]) echo ' checked' ?> />&nbsp;<?=_("keine zuk&uuml;nftigen Veranstaltungen anzeigen - Beginn des (letzten) Veranstaltungssemesters ist verstrichen")?> </font><br />
								<!-- &nbsp;<font size=-1><input type="CHECKBOX" name="select_inactive" <? if ($links_admin_data["select_inactive"]) echo ' checked' ?> />&nbsp;<?=_("nur inaktive Veranstaltungen ausw&auml;hlen (letzte Aktion vor mehr als sechs Monaten)")?> </font> -->
							</td>
						</tr>
						<?
					} else {
						?>
						<input type="HIDDEN" name="select_old" value="<? if ($links_admin_data["select_old"]) echo "TRUE" ?> " />
						<input type="HIDDEN" name="select_inactive" value="<? if ($links_admin_data["select_inactive"]) echo "TRUE" ?>" />
						<?
					}
					?>
					<tr>
						<td class="steel1" colspan=5>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td class="blank" colspan=5>
							&nbsp;
						</td>
					</tr>
					<? if (! empty($message)) : ?>
					<tr>
						<td class="blank" colspan=5>
							<? parse_msg($message); ?>
						</td>
					</tr>
					<? endif; ?>
				</table>
			</form>
			<?
		}

	// display Seminar-List
	if ($links_admin_data["srch_on"] || $auth->auth["perm"] =="tutor" || $auth->auth["perm"] == "dozent") {

		// Creation of Seminar-Query
		if ($links_admin_data["srch_on"]) {
			$query="SELECT DISTINCT seminare.*, Institute.Name AS Institut,
					sd1.name AS startsem,IF(duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem
					FROM seminar_user LEFT JOIN seminare USING (seminar_id)
					LEFT JOIN Institute USING (institut_id)
					LEFT JOIN auth_user_md5 ON (seminar_user.user_id = auth_user_md5.user_id)
					LEFT JOIN semester_data sd1 ON ( start_time BETWEEN sd1.beginn AND sd1.ende)
					LEFT JOIN semester_data sd2 ON ((start_time + duration_time) BETWEEN sd2.beginn AND sd2.ende)
					WHERE seminar_user.status = 'dozent' ";
			$conditions=0;

			if ($links_admin_data["srch_sem"]) {
				$one_semester = $semester->getSemesterData($links_admin_data["srch_sem"]);
				$query.="AND seminare.start_time <=".$one_semester["beginn"]." AND (".$one_semester["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
				$conditions++;
			}

			if (is_array($my_inst) && $auth->auth["perm"] != "root") {
				$query.="AND Institute.Institut_id IN ('".join("','",$my_inst)."') ";
			}

			if ($links_admin_data["srch_inst"]) {
				$query.="AND Institute.Institut_id ='".$links_admin_data["srch_inst"]."' ";
			}


			if ($links_admin_data["srch_fak"]) {
				$query.="AND fakultaets_id ='".$links_admin_data["srch_fak"]."' ";
			}


			if ($links_admin_data["srch_doz"]) {
				$query.="AND seminar_user.user_id ='".$links_admin_data["srch_doz"]."' ";
			}

			if ($links_admin_data["srch_exp"]) {
				$query.="AND (seminare.Name LIKE '%".$links_admin_data["srch_exp"]."%' OR seminare.VeranstaltungsNummer LIKE '%".$links_admin_data["srch_exp"]."%' OR seminare.Untertitel LIKE '%".$links_admin_data["srch_exp"]."%' OR seminare.Beschreibung LIKE '%".$links_admin_data["srch_exp"]."%' OR auth_user_md5.Nachname LIKE '%".$links_admin_data["srch_exp"]."%') ";
				$conditions++;
			}

			//Extension to the query, if we want to show lectures which are archiveable
			if (($i_page== "archiv_assi.php") && ($links_admin_data["select_old"]) && ($SEM_BEGINN_NEXT)) {
				$query.="AND ((seminare.start_time + seminare.duration_time < ".$SEM_BEGINN_NEXT.") AND seminare.duration_time != '-1') ";
				$conditions++;
			}

			// tutors and dozents only have a plain list
			} elseif (($auth->auth["perm"] =="tutor") || ($auth->auth["perm"] == "dozent")) {
					$query="SELECT  seminare.*, Institute.Name AS Institut ,
							sd1.name AS startsem,IF(duration_time=-1, '"._("unbegrenzt")."', sd2.name) AS endsem
						FROM seminar_user LEFT JOIN seminare USING (Seminar_id)
						LEFT JOIN Institute USING (institut_id)
						LEFT JOIN semester_data sd1 ON ( start_time BETWEEN sd1.beginn AND sd1.ende)
						LEFT JOIN semester_data sd2 ON ((start_time + duration_time) BETWEEN sd2.beginn AND sd2.ende)
						WHERE seminar_user.status IN ('dozent'"
						.(($i_page != 'archiv_assi.php' && $i_page != 'admin_visibility.php') ? ",'tutor'" : "")
						. ") AND seminar_user.user_id='$user->id' ";

			// should never be reached
			} else {
				$query = FALSE;
			}

			$query.=" ORDER BY  ".$links_admin_data["sortby"];
			if ($links_admin_data["sortby"] == 'start_time') $query .= ' DESC';
			$db->query($query);

		?>
		<form name="links_admin_action" action="<? echo $GLOBALS['PHP_SELF'] ?>" method="POST">
		<table border=0  cellspacing=0 cellpadding=2 align=center width="99%">
		<?

		// only show table header in case of hits
		if ($db->num_rows()) {
			?>
			<tr height=28>
				<td width="%10" class="steel" valign=bottom>
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width=1 height=20>
					&nbsp;<a href="<? echo $GLOBALS['PHP_SELF'] ?>?adminarea_sortby=start_time"><b><?=_("Semester")?></b></a>
				</td>
				<td width="5%" class="steel" valign=bottom>
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width=1 height=20>
					&nbsp; <a href="<? echo $GLOBALS['PHP_SELF'] ?>?adminarea_sortby=VeranstaltungsNummer"><b><?=_("Nr.")?></b></a>
				</td>
				<td width="45%" class="steel" valign=bottom>
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width=1 height=20>
					&nbsp; <a href="<? echo $GLOBALS['PHP_SELF'] ?>?adminarea_sortby=Name"><b><?=_("Name")?></b></a>
				</td>
				<td width="15%" align="center" class="steel" valign=bottom>
					<b><?=_("DozentIn")?></b>
				</td>
				<td width="25%"align="center" class="steel" valign=bottom>
					<a href="<? echo $GLOBALS['PHP_SELF'] ?>?adminarea_sortby=status"><b><?=_("Status")?></b></a>
				</td>
				<td width="10%" align="center" class="steel" valign=bottom>
					<b><?
						if ($i_page=="archiv_assi.php") {
							echo _("Archivieren");
						} elseif ($i_page=="admin_visibility.php") {
							echo _("Sichtbarkeit");
						} elseif ($i_page=="admin_lock.php") {
						echo _("Sperrebene");
						} else {
							echo _("Aktion");
						}
					?></b>
				</td>
			</tr>
			<?
			//more Options for archiving
			if ($i_page == "archiv_assi.php") {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=3>
						&nbsp; <font size=-1><?=_("Alle ausgew&auml;hlten Veranstaltungen")?>&nbsp;<input type="IMAGE" <?=makeButton("archivieren", "src")?> border=0 align="absmiddle" /></font><br />
						&nbsp; <font size=-1 color="red"><?=_("Achtung: Das Archivieren ist ein Schritt, der <b>nicht</b> r&uuml;ckg&auml;ngig gemacht werden kann!")?></font>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" colspan=3 align="right">
					<?
					if ($auth->auth["jscript"]) {
						printf("<font size=-1><a href=\"%s?select_all=TRUE&list=TRUE\">%s</a></font>", $GLOBALS['PHP_SELF'], makeButton("alleauswaehlen"));
					}
					?>&nbsp;
					</td>
				</tr>
				<?
			}
			//more Options for visibility changing
			if ($i_page == "admin_visibility.php") {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=3>
						&nbsp; <font size=-1><?=_("Sichtbarkeit der angezeigten Veranstaltungen")?>&nbsp;<input type="IMAGE" <?=makeButton("zuweisen", "src")?> border=0 align="absmiddle" /></font><br />
					</td>
					<td class="<? echo $cssSw->getClass() ?>" colspan=3 align="right">
					<input type="HIDDEN" name="change_visible" value="1">
					<?
					if ($auth->auth["jscript"]) {
						printf("<font size=-1><a href=\"%s?select_all=TRUE&list=TRUE\">%s</a></font>", $GLOBALS['PHP_SELF'], makeButton("alleauswaehlen"));
						// echo "&nbsp;<br>";
						// printf("<font size=-1><a href=\"%s?select_none=TRUE&list=TRUE\">%s</a></font>", $GLOBALS['PHP_SELF'], makeButton("alleauswaehlen"));
					}
					?>&nbsp;
					</td>
				</tr>
				<?
			}
		//more Options for lock changing
		if ($i_page == "admin_lock.php") {
			?>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" colspan="3">
					&nbsp; <font size=-1><?=_("Gewählte Sperrebenen den angezeigten Veranstaltungen ")?>&nbsp;<input type="IMAGE" <?=makeButton("zuweisen", "src")?> border=0 align="absmiddle" /></font><br />
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan="4" align="right">
				<?
				if ($auth->auth["jscript"]) {
					printf("<select name=\"lock_all\" size=1>");
					printf("<option value='-1'>"._("Bitte w&auml;hlen")."</option>");
					printf("<option value='none' %s>--"._("keine Sperrebene")."--</option>", $lock_all == 'none' ? 'selected=selected' : '' );
					for ($i=0;$i<count($all_lock_rules);$i++) {
						printf("<option value=\"".$all_lock_rules[$i]["lock_id"]."\" ");
						if (isset($lock_all) && $lock_all==$all_lock_rules[$i]["lock_id"]) {
							printf(" selected=selected ");
						}
						printf(">".$all_lock_rules[$i]["name"]."</option>");
					}
					// ab hier die verschiedenen Sperrlevel für alle Veranstaltungen
					printf("</select>");
					printf("<font size='-1'> als Vorauswahl </font>");
					printf("<input type=\"IMAGE\" ".makeButton("auswaehlen","src")." border=0 align=\"absmiddle\" name=\"general_lock\">");
				}
				?>&nbsp;
				</td>
			</tr>
			<?
		}

		//more Options for lock changing
			if ($i_page == "admin_aux.php") {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan="3" nowrap>
						&nbsp; <font size=-1><?=_("Zusatzangaben-Template den angezeigten Veranstaltungen")?>&nbsp;<input type="IMAGE" <?=makeButton("zuweisen", "src")?> border=0 align="absmiddle" /></font><br />
					</td>
					<td class="<? echo $cssSw->getClass() ?>" colspan="4" align="right">
					<?
					if ($auth->auth["jscript"]) {
						echo '<select name="aux_all" size="1">';
						echo '<option value="-1">'. _("Bitte ausw&auml;hlen"). '</option>';
						echo '<option value="null" ' . ($aux_all == 'null' ? 'selected=selected' : '') . '>-- '. _("keine Zusatzangaben") .' --</option>';
						foreach ((array)$all_aux_rules as $lock_id => $data) {
							echo '<option value="'.$lock_id.'"';
							if (isset($aux_all) && $aux_all==$lock_id) {
								echo ' selected=selected ';
							}
							echo '>'.$data['name'].'</option>';
						}
						// ab hier die verschiedenen Sperrlevel für alle Veranstaltungen
						echo '</select>';
						echo '<input type="image" '.makeButton("uebernehmen","aux_rule").' border=0 align="absmiddle" name="aux_rule">';
					}
					?>&nbsp;
					</td>
				</tr>
				<?
			}


		}

		while ($db->next_record()) {
			$seminar_id = $db->f("Seminar_id");
			$user_id = $auth->auth["uid"];

			$cssSw->switchClass();
			echo "<tr>";
			echo "<td align=\"center\" class=\"".$cssSw->getClass()."\"><font size=\"-1\">".htmlReady($db->f('startsem'));
			if ($db->f('startsem') != $db->f('endsem')) echo '<br>( - '.htmlReady($db->f('endsem')).')';
			echo "</font></td>";
			echo "<td class=\"".$cssSw->getClass()."\"><font size=\"-1\">".htmlReady($db->f("VeranstaltungsNummer"))."</font></td>";
			echo "<td class=\"".$cssSw->getClass()."\"><font size=\"-1\">".htmlReady(substr($db->f("Name"),0,100));
			if (strlen ($db->f("Name")) > 100)
				echo "(...)";
			if ($db->f("visible")==0) {
				echo "&nbsp;". _("(versteckt)");
			}
			echo "</font></td>";
			echo "<td align=\"center\" class=\"".$cssSw->getClass()."\"><font size=-1>";
			$db4->query("SELECT ". $_fullname_sql['full'] ." AS fullname, username, position FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) where Seminar_id = '$seminar_id' and status = 'dozent' ORDER BY position ");
			$k=0;
			if (!$db4->num_rows())
				echo "&nbsp; ";
			while ($db4->next_record()) {
				if ($k)
					echo ", ";
				echo "<a href=\"about.php?username=".$db4->f("username")."\">".htmlReady($db4->f("fullname"))."</a>";
				$k++;
			}
			echo "</font></td>";
			echo "<td class=\"".$cssSw->getClass()."\" align=\"center\"><font size=-1>".$SEM_TYPE[$db->f("status")]["name"]."<br />" . _("Kategorie:") . " <b>".$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"]."</b><font></td>";
			echo "<td class=\"".$cssSw->getClass()."\" nowrap align=\"center\">";

			//Kommandos fuer die jeweilgen Seiten
			switch ($i_page) {
				case "adminarea_start.php":
					printf("<font size=-1>" . _("Veranstaltung") . "<br /><a href=\"adminarea_start.php?select_sem_id=%s\">%s</a></font>", $seminar_id, makeButton("auswaehlen"));
					break;
				case "themen.php":
					printf("<font size=-1>" . _("Ablaufplan") . "<br /><a href=\"themen.php?seminar_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "raumzeit.php":
					printf("<font size=-1>" . _("Zeiten / Räume") . "<br /><a href=\"raumzeit.php?seminar_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_admission.php":
					printf("<font size=-1>" . _("Zugangsberechtigungen") . "<br /><a href=\"admin_admission.php?seminar_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_room_requests.php":
					printf("<font size=-1>" . _("Raumw&uuml;nsche") . "<br /><a href=\"admin_room_requests.php?seminar_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_lit_list.php":
					printf("<font size=-1>" . _("Literatur") . "<br /><a href=\"admin_lit_list.php?_range_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_statusgruppe.php":
					printf("<font size=-1>" . _("Funktionen / Gruppen") . "<br /><a href=\"admin_statusgruppe.php?range_id=%s&ebene=sem\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_roles.php":
					printf("<font size=-1>" . _("Funktionen / Gruppen") . "<br /><a href=\"admin_roles.php?range_id=%s&ebene=sem\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_seminare1.php":
					printf("<font size=-1>" . _("Veranstaltung") . "<br /><a href=\"admin_seminare1.php?s_id=%s&s_command=edit\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_modules.php":
					printf("<font size=-1>" . _("Module") . "<br /><a href=\"admin_modules.php?range_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "admin_news.php":
					printf("<font size=-1>" . _("News") . "<br /><a href=\"admin_news.php?range_id=%s\">%s</a></font>", $seminar_id, makeButton("bearbeiten"));
					break;
				case "copy_assi.php":
					printf("<font size=-1>" . _("Veranstaltung") . "<br /><a href=\"admin_seminare_assi.php?cmd=do_copy&cp_id=%s&start_level=TRUE&class=1\">%s</a></font>", $seminar_id, makeButton("kopieren"));
					break;
				case "admin_lock.php":
					$db5 = new Db_Seminar;
					$db5->query("SELECT lock_rule from seminare WHERE Seminar_id='".$seminar_id."'");
					$db5->next_record();
					if ($perm->have_perm("admin")) {
						?>
						<input type="hidden" name="make_lock" value=1>
						<select name=lock_sem[<? echo $seminar_id ?>]>
						<option value="none">-- <?= _("keine Sperrebene") ?> --</option>
						<?
							for ($i=0;$i<count($all_lock_rules);$i++) {
								echo "<option value=".$all_lock_rules[$i]["lock_id"]."";
								if (isset($lock_all) && $lock_all==$all_lock_rules[$i]["lock_id"]) {
									echo " selected ";
								} elseif (!isset($lock_all) && ($all_lock_rules[$i]["lock_id"]==$db5->f("lock_rule"))) {
									echo " selected ";
								}
								echo ">".htmlReady($all_lock_rules[$i]["name"])."</option>";
							}
						?>
						</select>
	
					<?
					}
				break;
				case "admin_aux.php":
					$db5 = new Db_Seminar;
					$db5->query("SELECT aux_lock_rule from seminare WHERE Seminar_id='$seminar_id'");
					$db5->next_record();
					if ($perm->have_perm("admin")) {
						?>
						<input type="hidden" name="make_aux" value="1">
						<select name=aux_sem[<? echo $seminar_id ?>]>
						<option value="null">-- <?=_("keine Zusatzangaben")?> --</option>
						<?
							foreach ((array)$all_aux_rules as $lock_id => $data) {
								echo '<option value="'.$lock_id.'"';
								if (isset($aux_all) && $aux_all==$lock_id) {
									echo ' selected ';
								} elseif (!isset($aux_all) && ($lock_id == $db5->f("aux_lock_rule"))) {
									echo ' selected ';
								}
								echo '>'.htmlReady($data['name']).'</option>';
							}
						?>
						</select>
					<?
					}
				break;

				case "admin_visibility.php":
					if ($perm->have_perm("admin") || (get_config('ALLOW_DOZENT_VISIBILITY') && $perm->have_perm('dozent'))) {
					?>
					<input type="HIDDEN" name="all_sem[]" value="<? echo $seminar_id ?>" />
					<input type="CHECKBOX" name="visibility_sem[<? echo $seminar_id ?>]" <? if (!$select_none && ($select_all || $db->f("visible"))) echo ' checked'; ?> />
					<?
					}
					break;
				case "archiv_assi.php":
					if ($perm->have_perm("admin") || (get_config('ALLOW_DOZENT_ARCHIV') && $perm->have_perm('dozent'))) {
					?>
					<input type="HIDDEN" name="archiv_sem[]" value="_id_<? echo $seminar_id ?>" />
					<input type="CHECKBOX" name="archiv_sem[]" <? if ($select_all) echo ' checked'; ?> />
					<?
					}
					break;
			}
			echo "</tr>";
		}

		//Traurige Meldung wenn nichts gefunden wurde oder sonst irgendwie nichts da ist
		if ($query && !$db->num_rows()) {
			if ($conditions)
				$srch_result="info§<font size=-1><b>" . _("Leider wurden keine Veranstaltungen entsprechend Ihren Suchkriterien gefunden!") . "</b></font>§";
			else
				$srch_result="info§<font size=-1><b>" . _("Leider wurden keine Veranstaltungen gefunden!") . "</b></font>§";
			parse_msg ($srch_result, "§", "steel1", 2, FALSE);
		}
		?>
			<tr>
				<td class="blank" colspan=1>
					&nbsp;
				</td>
			</tr>
		</table>
		</form>
		<?
	}
	?>
	</td>
	</tr>
	</table>
	<?
		page_close();
		die;
	}
}
if ($SessSemName["class"] == "sem" && $SessSemName[1] && !$perm->have_studip_perm('tutor', $SessSemName[1])){
	if(!is_object($header_controller)) include ('lib/include/header.php');   // Output of Stud.IP head
	parse_window('error§' . _("Sie haben keine ausreichende Zugriffsberechtigung!"), '§', _("Zugriff verweigert"));
	include ('lib/include/html_end.inc.php');
	page_close();
	die();
}
$reiter->create($structure, $reiter_view);
