	<?
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
if ($perm->have_perm("tutor")){	// Navigationsleiste ab status "Tutor"

require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/dates.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/msg.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/reiter.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/functions.php";

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$db4=new DB_Seminar;
$cssSw=new cssClassSwitcher;

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
	$archiv_assi_data='';
	$term_metadata='';
	$news_range_id='';
	$news_range_name='';

	$links_admin_data["select_old"]=TRUE;
	// $links_admin_data["select_inactive"]=TRUE;
}


//a Veranstaltung was selected in the admin-search kann viellecht weg
if (($i_page== "adminarea_start.php") && ($select_sem_id)) {
	reset_all_data();
	closeObject();
	openSem($select_sem_id);
//a Veranstaltung which was already open should be administrated
} elseif (($SessSemName[1]) && ($new_sem))  {
	reset_all_data();
	$links_admin_data["referred_from"]="sem";
}

//a Einrichtung was selected in the admin-search
if (($admin_inst_id) && ($admin_inst_id != "NULL")){
	reset_all_data();
	closeObject();
	openInst($admin_inst_id);
//a Einrichtung which was already open should be administrated
} elseif (($SessSemName[1]) && ($new_inst))  {
	reset_all_data();
	$links_admin_data["referred_from"]="inst";
}

//Veranstaltung was selected but it is on his way to hell.... we close t at ths point 
if (($archive_kill) && ($SessSemName[1] == $archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])) {
	//reset_all_data();
	closeObject();
}

//a new session in the adminarea...
if (($i_page== "adminarea_start.php") && ($list)) {
	reset_all_data();
	closeObject();
} elseif ($i_page== "adminarea_start.php")
	$list=TRUE;


if ($sortby) {
	$links_admin_data["sortby"]=$sortby;
	$list=TRUE;
} else
	$links_admin_data["sortby"]="Name";

if ($view)
	$links_admin_data["view"]=$view;

if ($srch_send) {
	$links_admin_data["srch_sem"]=$srch_sem;
	$links_admin_data["srch_doz"]=$srch_doz;
	$links_admin_data["srch_inst"]=$srch_inst;
	$links_admin_data["srch_fak"]=$srch_fak;	
	$links_admin_data["srch_exp"]=$srch_exp;
	$links_admin_data["select_old"]=$select_old;
	$links_admin_data["select_inactive"]=$select_inactive;
	$links_admin_data["srch_on"]=TRUE;
	$list=TRUE;
	}

//if the user selected the information field at Einrichtung-selection....
if ($admin_inst_id == "NULL")
	$list=TRUE;

//user wants to create a new Einrichtung
if ($i_view=="new")
	$links_admin_data='';

//here are all the pages/views listed, which require the search form for  Einrichtungen
if ($i_page == "admin_institut.php"
		OR ($i_page == "admin_statusgruppe.php" AND $links_admin_data["view"]=="statusgruppe_inst")
		OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="literatur_inst")
		OR $i_page == "inst_admin.php"
		OR ($i_page == "admin_news.php" AND $links_admin_data["view"]=="news_inst")
		OR ($i_page == "admin_extern.php" AND $links_admin_data["view"] == "extern_inst")) {
		
	$view_mode="inst";
}

//here are all the pages/views listed, which require the search form for Veranstaltungen
if ($i_page == "admin_seminare1.php"
		OR $i_page == "admin_dates.php"
		OR $i_page == "admin_metadates.php"
		OR $i_page == "admin_admission.php"
		OR ($i_page == "admin_statusgruppe.php" AND $links_admin_data["view"]=="statusgruppe_sem")
		OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="literatur_sem")
		OR $i_page == "archiv_assi.php"
		OR $i_page == "adminarea_start.php"
		OR ($i_page == "admin_news.php" AND $links_admin_data["view"]=="news_sem")) {
	
	$view_mode="sem";
}

//remember the open topkat
if ($view_mode=="sem")
	$links_admin_data["topkat"]="sem";
elseif ($view_mode=="inst")
	$links_admin_data["topkat"]="inst";
else
	$links_admin_data["topkat"]="global";

/*//Wenn nur ein Institut verwaltet werden kann, immer dieses waehlen (Auswahl unterdruecken)
if ((!$SessSemName[1]) && ($list) && ($view_mode=="inst")) {
	if ($perm->have_perm("root"))
		$db->query("SELECT Institut_id  FROM Institute ORDER BY Name");
	else
		$db->query("SELECT Institute.Institut_id FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms IN ('admin', 'dozent', 'tutor') ORDER BY Name");

	if ($db->nf() ==1) {
		$db->next_record();
		reset_all_data;
		openInst($db->f("Institut_id"));
	}
}
*/

//Wenn Seminar_id gesetzt ist oder vorgewaehlt wurde, werden die spaeteren Seiten mit entsprechend gesetzten Werten aufgerufen
if ($SessSemName["class"]=="sem") {
	switch ($i_page) {
		case "admin_admission.php": 
			$seminar_id=$SessSemName[1];
			break;
		case "admin_dates.php": 
			$range_id=$SessSemName[1];
			break;
		case "admin_metadates.php": 
			$seminar_id=$SessSemName[1];
			break;
		case "admin_literatur.php":
			if ($links_admin_data["view"]=="literatur_sem") {
				$range_id=$SessSemName[1];
				$ebene="sem";
			}
			break;
		case "admin_statusgruppe.php":
			if ($links_admin_data["view"]=="statusgruppe_sem") {
				$range_id=$SessSemName[1];
				$ebene="sem";
			}
			break;
		case "archiv_assi.php": 
			$archiv_sem[]="_id_".$SessSemName[1];
			$archiv_sem[]="on";
			break;
		case "admin_seminare1.php": 
			$s_id=$SessSemName[1];
			if (!$s_command)
				$s_command="edit";
			break;
	}
}

//Wenn Institut_id gesetzt ist oder vorgewaehlt wurde, werden die spaeteren Seiten mit entsprechend gesetzten Werten aufgerufen
if ($SessSemName["class"]=="inst") {
	switch ($i_page) {
		case "admin_institut.php": 
			$i_view=$SessSemName[1];
			break;
		case "inst_admin.php": 
			$inst_id=$SessSemName[1];
			break;
		case "admin_literatur.php": 
			if ($links_admin_data["view"]=="literatur_inst") {
				$range_id=$SessSemName[1];
				$ebene="inst";
				}
			break;
		case "admin_statusgruppe.php": 
			if ($links_admin_data["view"]=="statusgruppe_inst") {
				$range_id=$SessSemName[1];
				$ebene="inst";
				}
			break;
		case "admin_extern.php":
			$range_id = $SessSemName[1];
			break;
	}
}


//Reitersytem erzeugen
$reiter=new reiter;

//Ruecksprung-Reiter vorbereiten
if ($SessSemName["class"] == "inst") {
	if ($links_admin_data["referred_from"] == "inst")
		$back_jump= "zur&uuml;ck zur ausgew&auml;hlten Einrichtung";
	else
		$back_jump= "zur ausgew&auml;hlten Einrichtung";
}
if ($SessSemName["class"] == "sem") {
	if (($links_admin_data["referred_from"] == "sem") && (!$archive_kill) && (!$links_admin_data["assi"]))
		$back_jump= "zur&uuml;ck zur ausgew&auml;hlten Veranstaltung";
	elseif (($links_admin_data["referred_from"] == "assi") && (!$archive_kill))
		$back_jump= "zur neu angelegten Veranstaltung";
	elseif (!$links_admin_data["assi"])
		$back_jump= "zur ausgew&auml;hlten Veranstaltung";
}

//Topkats
if ($perm->have_perm("tutor")) {
	if (($SessSemName["class"] == "sem") && (!$archive_kill))
		$structure["veranstaltungen"]=array (topKat=>"", name=>"Veranstaltungen", link=>"admin_seminare1.php", active=>FALSE);
	else
		$structure["veranstaltungen"]=array (topKat=>"", name=>"Veranstaltungen", link=>"adminarea_start.php?list=TRUE", active=>FALSE);	
	$structure["einrichtungen"]=array (topKat=>"", name=>"Einrichtungen", link=>"admin_literatur.php?list=TRUE&view=literatur_inst", active=>FALSE);
}

$structure["modules"]=array (topKat=>"", name=>"Tools", link=>"export.php", active=>FALSE);

if ($perm->have_perm("admin")) {
	$structure["einrichtungen"]=array (topKat=>"", name=>"Einrichtungen", link=>"admin_institut.php?list=TRUE", active=>FALSE);
	$structure["global"]=array (topKat=>"", name=>"globale Einstellungen", link=>"new_user_md5.php", active=>FALSE);
}

if ($SessSemName["class"] == "inst")
	$structure["back_jump"]=array (topKat=>"", name=>$back_jump, link=>"institut_main.php?auswahl=".$SessSemName[1], active=>FALSE);
elseif (($SessSemName["class"] == "sem") && (!$archive_kill) && (!$links_admin_data["assi"]))
	$structure["back_jump"]=array (topKat=>"", name=>$back_jump, link=>"seminar_main.php?auswahl=".$SessSemName[1], active=>FALSE);

//Bottomkats
$structure["grunddaten_sem"]=array (topKat=>"veranstaltungen", name=>"Grunddaten", link=>"admin_seminare1.php?list=TRUE", active=>FALSE);
$structure["zeiten"]=array (topKat=>"veranstaltungen", name=>"Zeiten", link=>"admin_metadates.php?list=TRUE", active=>FALSE);
$structure["ablaufplan"]=array (topKat=>"veranstaltungen", name=>"Ablaufplan", link=>"admin_dates.php?list=TRUE", active=>FALSE);
$structure["literatur_sem"]=array (topKat=>"veranstaltungen", name=>"Literatur", link=>"admin_literatur.php?list=TRUE&view=literatur_sem", active=>FALSE);
$structure["zugang"]=array (topKat=>"veranstaltungen", name=>"Zugangsberechtigungen", link=>"admin_admission.php?list=TRUE", active=>FALSE);
$structure["statusgruppe_sem"]=array (topKat=>"veranstaltungen", name=>"Gruppen&nbsp;/&nbsp;Funktionen", link=>"admin_statusgruppe.php?list=TRUE&view=statusgruppe_sem", active=>FALSE);
$structure["news_sem"]=array (topKat=>"veranstaltungen", name=>"News", link=>"admin_news.php?view=news_sem", active=>FALSE);
if ($perm->have_perm("admin")) 
	$structure["archiv"]=array (topKat=>"veranstaltungen", name=>"archivieren", link=>"archiv_assi.php?list=TRUE&new_session=TRUE", active=>FALSE);
if ($perm->have_perm("dozent")) 
	$structure["new_sem"]=array (topKat=>"veranstaltungen", name=>"neue&nbsp;Veranstaltung", link=>"admin_seminare_assi.php?new_session=TRUE", active=>FALSE, newline=>FALSE);
//
if ($perm->have_perm("admin")) {
	$structure["grunddaten_inst"]=array (topKat=>"einrichtungen", name=>"Grunddaten", link=>"admin_institut.php?list=TRUE", active=>FALSE);
	$structure["mitarbeiter"]=array (topKat=>"einrichtungen", name=>"Mitarbeiter", link=>"inst_admin.php?list=TRUE", active=>FALSE);
	$structure["statusgruppe_inst"]=array (topKat=>"einrichtungen", name=>"Gruppen&nbsp;/&nbsp;Funktionen", link=>"admin_statusgruppe.php?list=TRUE&view=statusgruppe_inst", active=>FALSE);
}	
$structure["literatur_inst"]=array (topKat=>"einrichtungen", name=>"Literatur", link=>"admin_literatur.php?list=TRUE&view=literatur_inst", active=>FALSE);
$structure["news_inst"]=array (topKat=>"einrichtungen", name=>"News", link=>"admin_news.php?view=news_inst", active=>FALSE);

if ($EXTERN_ENABLE && $perm->have_perm("admin"))
	$structure["extern_inst"] = array("topKat" => "einrichtungen", "name" => "externe Seiten", "link" => "admin_extern.php?list=TRUE&view=extern_inst", "active" => FALSE);
if ($perm->is_fak_admin())
	$structure["new_inst"]=array (topKat=>"einrichtungen", name=>"neue&nbsp;Einrichtung", link=>"admin_institut.php?i_view=new", active=>FALSE);
//
if ($EXPORT_ENABLE)
	$structure["export"]=array (topKat=>"modules", name=>"Export", link=>"export.php", active=>FALSE);
if ($ILIAS_CONNECT_ENABLE)
	$structure["lernmodule"]=array (topKat=>"modules", name=>"Lernmodule", link=>"admin_lernmodule.php", active=>FALSE);
if ($RESOURCES_ENABLE)
	$structure["resources"]=array (topKat=>"modules", name=>"Ressourcenverwaltung", link=>"resources.php", active=>FALSE);
if ($perm->have_perm("admin"))
	$structure["show_admission"]=array (topKat=>"modules", name=>"laufende&nbsp;Anmeldeverfahren", link=>"show_admission.php", active=>FALSE);
//
if ($perm->have_perm("admin")) {		
	$structure["new_user"]=array (topKat=>"global", name=>"Benutzer", link=>"new_user_md5.php", active=>FALSE);
	$structure["range_tree"]=array (topKat=>"global", name=>"Einrichtungshierarchie", link=>"admin_range_tree.php", active=>FALSE);
}
if ($perm->have_perm("root")) {
	$structure["sem_tree"]=array (topKat=>"global", name=>"Veranstaltungshierarchie", link=>"admin_sem_tree.php", active=>FALSE);
	$structure["studiengang"]=array (topKat=>"global", name=>"Studieng&auml;nge", link=>"admin_studiengang.php", active=>FALSE);
	$structure["sessions"]=array (topKat=>"global", name=>"Sessions", link=>"view_sessions.php", active=>FALSE);
	$structure["integrity"]=array (topKat=>"global", name=>"DB Integrit&auml;t", link=>"admin_db_integrity.php", active=>FALSE);
}
//Reitersystem Ende


//Tooltip erzeugen
if ($SessSemName["class"] == "sem") {
	$db->query ("SELECT Name FROM seminare WHERE Seminar_id = '".$SessSemName[1]."' ");
	$db->next_record();
	}
if ($SessSemName["class"] == "inst") {
	$db->query ("SELECT Name FROM Institute WHERE Institut_id = '".$SessSemName[1]."' ");
	$db->next_record();
	}

$tooltip="Sie befinden sich im Administrationsbereich von Stud.IP. ";

if (($SessSemName["class"] == "sem") && (!$archive_kill) && (!$links_admin_data["assi"]))
	$tooltip.= "Ausgewählte Veranstaltung: ".$db->f("Name")." - Um die Auswahl aufzuheben, benutzen Sie bitte das Schlüsselsymbol.";
elseif ($SessSemName["class"] == "inst")
	$tooltip.= "Ausgewählte Einrichtung: ".$db->f("Name")." - Um die Auswahl aufzuheben, benutzen Sie bitte das Schlüsselsymbol.";		
else
	$tooltip.= "Keine Veranstaltung oder Einrichtung ausgewählt";

//Additional Text erzeugen
if (($SessSemName["class"] == "sem") && (!$archive_kill) && (!$links_admin_data["assi"]))
	$addText=" <a href=\"adminarea_start.php?list=TRUE\"><img ".tooltip("Auswahl der Veranstaltung ".$db->f("Name")." aufheben")." align=\"absmiddle\" src=\"pictures/admin.gif\" border=0></a>";
elseif ($SessSemName["class"] == "inst")
	$addText=" <a href=\"adminarea_start.php?list=TRUE\"><img ".tooltip("Auswahl der Einrichtung ".$db->f("Name")." aufheben")." align=\"absmiddle\" src=\"pictures/admin.gif\" border=0></a>";

//View festlegen
switch ($i_page) {
	case "admin_admission.php" : 
		$reiter_view="zugang"; 
	break;
	case "admin_bereich.php" : 
		$reiter_view="bereich"; 
	break;
	case "admin_dates.php" : 
		$reiter_view="ablaufplan"; 
	break;
	case "admin_db_integrity.php" :
		$reiter_view = "integrity";
	break;
	case "admin_fach.php" : 
		$reiter_view="fach"; 
	break;

	case "admin_institut.php" : 
		$reiter_view="grunddaten_inst"; 
	break;
	case "admin_literatur.php": 
		if ($links_admin_data["topkat"] == "sem")
			$reiter_view="literatur_sem"; 
		else
			$reiter_view="literatur_inst";
	break;
	case "admin_metadates.php" : 
		$reiter_view="zeiten"; 
	break;
	case "admin_news.php": 
		if ($links_admin_data["topkat"] == "sem")
			$reiter_view="news_sem"; 
		elseif ($links_admin_data["topkat"] == "inst")
			$reiter_view="news_inst";
	break;
	case "admin_seminare1.php": 
		$reiter_view="grunddaten_sem"; 
	break;
	case "admin_seminare_assi.php": 
		$reiter_view="new_sem"; 
	break;
	case "admin_statusgruppe.php": 
		if ($links_admin_data["topkat"] == "sem")
			$reiter_view="statusgruppe_sem"; 
		else
			$reiter_view="statusgruppe_inst";
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
	case "export.php": 
		$reiter_view="export"; 
	break;
	case "admin_lernmodule.php": 
		$reiter_view="lernmodule"; 
	break;
	case "admin_modules_start.php": 
		$reiter_view="modules"; 
	break;
	case "admin_range_tree.php": 
		$reiter_view="range_tree"; 
	break;
	case "admin_sem_tree.php": 
		$reiter_view="sem_tree"; 
	break;
	case "admin_extern.php":
		$reiter_view = "extern_inst";
		break;
}

$reiter->create($structure, $reiter_view, $tooltip, $addText);

//Einheitliches Auswahlmenu fuer Einrichtungen
if (((!$SessSemName[1]) || ($SessSemName["class"] == "sem")) && ($list) && ($view_mode == "inst")) {
	?>
	<table width="100%" cellspacing=0 cellpadding=0 border=0>
	<tr valign=top align=middle>
		<td class="topic" colspan=2 align="left"><b>&nbsp;Verwaltung aller Einrichtungen, auf die Sie Zugriff haben:</b>
		</td>
	</tr>
	<?
	if ($msg) {
		echo "<tr> <td class=\"blank\" colspan=2><br />";
		parse_msg ($msg);
		echo "</td></tr>";
	}
	?>
	<tr>
		<td class="blank" colspan=2>&nbsp;
			<form name="links_admin_search" action="<? echo $PHP_SELF,"?", "view=$view"?>" method="POST">
			<table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
				<tr>
					<td class="steel1">
							<font size=-1><br /><b>Bitte w&auml;hlen Sie die Einrichtung aus, die Sie bearbeiten wollen:</b><br/>&nbsp; </font>
					</td>
				</tr>
				<tr>
					<td class="steel1">
					<font size=-1><select name="admin_inst_id" size="1">
					<?
					if ($auth->auth['perm'] == "root"){
						$db->query("SELECT Institut_id, Name, 1 AS is_fak  FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
					} elseif ($auth->auth['perm'] == "admin") {
						$db->query("SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)  
									WHERE a.user_id='$user->id' AND a.inst_perms='admin' ORDER BY is_fak,Name");
					} else {
						$db->query("SELECT a.Institut_id,Name FROM user_inst a LEFT JOIN Institute b USING (Institut_id) WHERE inst_perms IN('tutor','dozent') AND user_id='$user->id'");
					}
						
					printf ("<option value=\"NULL\">-- bitte Einrichtung ausw&auml;hlen --</option>\n");
					while ($db->next_record()){
						printf ("<option value=\"%s\" style=\"%s\">%s </option>\n", $db->f("Institut_id"),($db->f("is_fak") ? "font-weight:bold;" : ""), htmlReady(substr($db->f("Name"), 0, 70)));
						if ($db->f("is_fak")){
							$db2->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND institut_id!='" .$db->f("Institut_id") . "'");
							while ($db2->next_record()){
								printf("<option value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s </option>\n", $db2->f("Institut_id"), htmlReady(substr($db2->f("Name"), 0, 70)));
							}
						}
					}
					?>
				</select></font>&nbsp; 
				<input type="IMAGE" src="./pictures/buttons/auswaehlen-button.gif" border=0 value="bearbeiten">
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
	?>
	<table width="100%" cellspacing=0 cellpadding=0 border=0>
	<tr valign=top align=middle>
		<td class="topic" colspan=2 align="left"><b>&nbsp;Verwaltung aller Veranstaltungen, auf die Sie Zugriff haben:</b>
		</td>
	</tr>
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
		<form name="links_admin_search" action="<? echo $PHP_SELF ?>" method="POST">
			<table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
				<tr>
					<td class="steel1" colspan=5>
							<font size=-1><br /><b>Sie k&ouml;nnen die Auswahl der Veranstaltungen eingrenzen:</b><br/>&nbsp; </font>
					</td>
				</tr>
					<tr>
						<td class="steel1">
							<font size=-1>Semester:</font><br /><select name="srch_sem">
								<option value=0>alle</option>
								<?
								$i=1;
								foreach ($SEMESTER as $a) {
									$i++;
									if ($links_admin_data["srch_sem"]==$a["name"])
										echo "<option selected value=\"".$a["name"]."\">".$a["name"]."</option>";
									else
										echo "<option value=\"".$a["name"]."\">".$a["name"]."</option>";
									}
								?>
							</select>
						</td>
						
						<td class="steel1">
							<?
							if ($perm->have_perm("root")){
								$db->query("SELECT Institut_id, Name FROM Institute WHERE Institut_id!=fakultaets_id ORDER BY Name");
							} else {
								$db->query("SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)  
									WHERE a.user_id='$user->id' AND a.inst_perms='admin' ORDER BY is_fak,Name");
							}
							?>
							<font size=-1>Einrichtung:</font><br /><select name="srch_inst">
								<option value=0>alle</option>
								<?
								while ($db->next_record()) {
									$my_inst[]=$db->f("Institut_id");
									if ($links_admin_data["srch_inst"] == $db->f("Institut_id"))
										echo"<option selected value=".$db->f("Institut_id").">".substr($db->f("Name"), 0, 30)."</option>";
									else
										echo"<option value=".$db->f("Institut_id").">".substr($db->f("Name"), 0, 30)."</option>";
									if ($db->f("is_fak")){
										$db2->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND institut_id!='" .$db->f("Institut_id") . "'");
										while ($db2->next_record()){
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
							<font size=-1>Dozent:</font><br /><select name="srch_doz">
								<option value=0>alle</option>
								<?
								$query="SELECT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] ." AS fullname, Institut_id FROM user_inst  LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms='dozent' AND institut_id IN (";
								$i=0;
								foreach ($my_inst as $a) {
									if ($i)
										$query.= ", ";
									$query.="'".$a."'"; 
									$i++;
									}
								$query.=") GROUP BY auth_user_md5.user_id ORDER BY Nachname ";
								$db->query($query);
								if ($db->num_rows() >1) 
									while ($db->next_record()) {
										if ($links_admin_data["srch_doz"] == $db->f("user_id"))
											echo"<option selected value=".$db->f("user_id").">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
										else
											echo"<option value=".$db->f("user_id").">".htmlReady(my_substr($db->f("fullname"),0,35))."</option>";
										}										
								?>								
							</select>
							<?
								}
							
							if ($perm->have_perm("root")) {
								$db->query("SELECT Institut_id,Name FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
							?>
							<font size=-1>Fakult&auml;t:</font><br /><select name="srch_fak">
								<option value=0>alle</option>
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
							?>
							</td>
							<td class="steel1">
								<font size=-1>freie Suche:</font><br /><input type="TEXT" name="srch_exp" maxlength=255 size=20 value="<? echo $links_admin_data["srch_exp"] ?>" />
								<input type="HIDDEN" name="srch_send" value="TRUE" />
						</td>
						<td class="steel1" valign="bottom">
								&nbsp; <br/>
								<input type="IMAGE" src="./pictures/buttons/anzeigen-button.gif" border=0 name="anzeigen" value="Anzeigen" />
								<input type="HIDDEN" name="view" value="<? echo $links_admin_data["view"]?>" />
						</td>
					</tr>
					<?
					//more Options for archiving
					if ($i_page == "archiv_assi.php") {
					?>
					<tr>
						<td class="steel1" colspan=5>
							<br />&nbsp;<font size=-1><input type="CHECKBOX" name="select_old" <? if ($links_admin_data["select_old"]) echo checked ?> />&nbsp;nur archivierbare Veranstaltungen ausw&auml;hlen -  (letztes) Veranstaltungssemester verstrichen </font><br />
							<!-- &nbsp;<font size=-1><input type="CHECKBOX" name="select_inactive" <? if ($links_admin_data["select_inactive"]) echo checked ?> />&nbsp;nur inaktive Veranstaltungen ausw&auml;hlen (letzte Aktion vor mehr als 6 Monaten) </font> -->
						</td>
					</tr>
					<? } else {?>
					<input type="HIDDEN" name="select_old" value="<? if ($links_admin_data["select_old"]) echo "TRUE" ?> " />
					<input type="HIDDEN" name="select_inactive" value="<? if ($links_admin_data["select_inactive"]) echo "TRUE" ?>" />
					<? } ?>
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
			</table>
			</form>
			<?
		}


//Zusammenbasteln der Seminar-Query
if ($links_admin_data["srch_on"] || $auth->auth["perm"] =="tutor" || $auth->auth["perm"] == "dozent"){
$query="SELECT DISTINCT seminare.*, Institute.Name AS Institut FROM seminare LEFT JOIN Institute USING (institut_id) LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent') LEFT JOIN auth_user_md5 USING (user_id)";
$conditions=0;
if ($links_admin_data["srch_sem"]) {
	$i=0;
	for ($i; $i <=sizeof($SEMESTER); $i++) {
		if ($SEMESTER[$i]["name"] == $links_admin_data["srch_sem"]) {
			$query.="WHERE seminare.start_time <=".$SEMESTER[$i]["beginn"]." AND (".$SEMESTER[$i]["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
			$conditions++;
			}
		}
	}

if (is_array($my_inst) && $auth->auth["perm"] != "root") {
	if ($conditions)
		$query.="AND ";
	else
		$query.="WHERE ";
	$query.="Institute.Institut_id IN ('".join("','",$my_inst)."') ";
	$conditions++;
	}

if ($links_admin_data["srch_inst"]) {
	if ($conditions)
		$query.="AND ";
	else
		$query.="WHERE ";
	$query.="Institute.Institut_id ='".$links_admin_data["srch_inst"]."' ";
	$conditions++;
	}
	

if ($links_admin_data["srch_fak"]) {
	if ($conditions)
		$query.="AND ";
	else
		$query.="WHERE ";
	$query.="fakultaets_id ='".$links_admin_data["srch_fak"]."' ";
	$conditions++;
	}


if ($links_admin_data["srch_doz"]) {
	if ($conditions)
		$query.="AND ";
	else
		$query.="WHERE ";
	$query.="seminar_user.user_id ='".$links_admin_data["srch_doz"]."' ";
	$conditions++;
	}

if ($links_admin_data["srch_exp"]){
	if ($conditions)
		$query.="AND ";
	else
		$query.="WHERE ";
	$query.="(seminare.Name LIKE '%".$links_admin_data["srch_exp"]."%' OR seminare.Untertitel LIKE '%".$links_admin_data["srch_exp"]."%' OR seminare.Beschreibung LIKE '%".$links_admin_data["srch_exp"]."%' OR auth_user_md5.Nachname LIKE '%".$links_admin_data["srch_exp"]."%') ";
	$conditions++;
	}
//arrg Hotfix, bis sich mal jemand erbarmt...

if (($auth->auth["perm"] =="tutor") || ($auth->auth["perm"] == "dozent")){
	$query="SELECT  seminare.*, Institute.Name AS Institut FROM seminar_user LEFT JOIN seminare USING (Seminar_id) 
		LEFT JOIN Institute USING (institut_id) 
		WHERE seminar_user.status IN ('dozent','tutor') 
		AND seminar_user.user_id='$user->id' ";
		$conditions++;
}
// ende Hotfix

//Extension to the query, if we want to show lectures which are archiveable
if (($i_page== "archiv_assi.php") && ($links_admin_data["select_old"])) {
	if ($conditions)
		$query.="AND ";
	else
		$query.="WHERE ";
	$query.="((seminare.start_time + seminare.duration_time < ".$SEM_BEGINN_NEXT.") AND seminare.duration_time != '-1') ";
	$conditions++;
	}

$query.=" ORDER BY  ".$links_admin_data["sortby"];	
		
$db->query($query);

?>
			<form name="links_admin_action" action="<? echo $PHP_SELF ?>" method="POST">
			<table border=0  cellspacing=0 cellpadding=2 align=center width="99%">
<?

$c=-1;
while ($db->next_record()) {
	//weitere Abfragen, falls nur eingeschraenkter Zugriff moeglich
	$seminar_id = $db->f("Seminar_id");
	$user_id = $auth->auth["uid"];
/*
	$db2->query("select * from seminar_user WHERE Seminar_id = '$seminar_id' and user_id = '$user_id' AND (status = 'dozent' OR status = 'tutor')");
	$db3->query("select * from seminare LEFT JOIN user_inst USING (Institut_id) where Seminar_id = '$seminar_id' and user_id = '$user_id' AND inst_perms = 'admin'");
	
	 if ($perm->have_perm("root") ||
		($perm->have_perm("admin") && $db3->next_record()) || 
		($db2->next_record()) ) {
	if ($perm->have_studip_perm("tutor",$seminar_id)){
*/	
		$c++;
		//Titelzeile wenn erste Veranstaltung angezeigt werden soll
		if ($c==0) {
			?>
				<tr height=28>
					<td width="60%" class="steel" valign=bottom>
						<img src="pictures/blank.gif" width=1 height=20>
						&nbsp; <a href="<? echo $PHP_SELF ?>?sortby=Name"><b>Name</b></a>
					</td>
					<td width="15%" align="center" class="steel" valign=bottom>
						<B>Dozent</b></a>
					</td>
					<td width="25%"align="center" class="steel" valign=bottom>
						<a href="<? echo $PHP_SELF ?>?sortby=status"><B>Status</b></a>
					</td>
					<td width="10%" align="center" class="steel" valign=bottom>
						<b><? printf ("%s", ($i_page=="archiv_assi.php") ?  "Archivieren": "Aktion") ?></b> 
					</td>
				 </tr>
				<? 
				//more Options for archiving
				if ($i_page == "archiv_assi.php") {
				?>
				<tr <? $cssSw->switchClass() ?>>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2>
						&nbsp; <font size=-1>Alle ausgew&auml;hlten Veranstaltungen&nbsp;<input type="IMAGE" src="./pictures/buttons/archivieren-button.gif" border=0 /></font><br />
						&nbsp; <font size=-1 color="red">Achtung: Das Archivieren ist ein Schritt, der <b>nicht</b> r&uuml;ckg&auml;ngig gemacht werden kann!</font>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="right">
						<? if ($auth->auth["jscript"]) {?>
						<font size=-1><a href="<? echo $PHP_SELF ?>?select_all=TRUE&list=TRUE"><image src="./pictures/buttons/alleauswaehlen-button.gif" border=0/></a></font>
						<? } ?>&nbsp; 
					</td>
				</tr>
				<? } 
			}
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\">".htmlReady(substr($db->f("Name"),0,100));
		if (strlen ($db->f("Name")) > 100)
			echo "(...)";
		echo "</td>";
		echo "<td align=\"center\" class=\"".$cssSw->getClass()."\"><font size=-1>";
		$db4->query("SELECT ". $_fullname_sql['full'] ." AS fullname, username FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) where Seminar_id = '$seminar_id' and status = 'dozent'");
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
		echo "<td class=\"".$cssSw->getClass()."\" align=\"center\"><font size=-1>".$SEM_TYPE[$db->f("status")]["name"]."<br />Kategorie: <b>".$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"]."</b><font></td>";
		echo "<td class=\"".$cssSw->getClass()."\" nowrap align=\"center\">";
		//Kommandos fuer die jeweilgen Seiten
		switch ($i_page) {
			case "adminarea_start.php":
			?>
			<font size=-1>Veranstaltung<br /><a href="adminarea_start.php?select_sem_id=<? echo $seminar_id ?>"><img src="pictures/buttons/auswaehlen-button.gif" border=0></a></font> 
			<?
			break;
			case "admin_dates.php": 
			?>
			<font size=-1>Ablaufplan<br /><a href="admin_dates.php?range_id=<? echo $seminar_id ?>"><img src="pictures/buttons/bearbeiten-button.gif" border=0></a></font> 
			<?
			break;
			case "admin_metadates.php": 
			?>
				<font size=-1>Zeiten<br /><a href="admin_metadates.php?seminar_id=<? echo $seminar_id ?>"><img src="pictures/buttons/bearbeiten-button.gif" border=0></a></font> 
			<?
			break;
			case "admin_admission.php": 
			?>
				<font size=-1>Zugangsberechtigungen<br /><a href="admin_admission.php?seminar_id=<? echo $seminar_id ?>"><img src="pictures/buttons/bearbeiten-button.gif" border=0></a></font> 
			<?
			break;
			case "admin_literatur.php": 
			?>
				<font size=-1>Literatur/Links<br /><a href="admin_literatur.php?range_id=<? echo $seminar_id ?>&ebene=sem"><img src="pictures/buttons/bearbeiten-button.gif" border=0></a></font> 
			<?
			break;
			case "admin_statusgruppe.php": 
			?>
				<font size=-1>Funktionen / Gruppen<br /><a href="admin_statusgruppe.php?range_id=<? echo $seminar_id ?>&ebene=sem"><img src="pictures/buttons/bearbeiten-button.gif" border=0></a></font> 
			<?
			break;
			case "admin_seminare1.php": 
			?>
				<font size=-1>Veranstaltung<br /><a href="admin_seminare1.php?s_id=<? echo $seminar_id ?>&s_command=edit"><img src="pictures/buttons/bearbeiten-button.gif" border=0></a>
			<?
			break;
			case "archiv_assi.php": 
				if ($perm->have_perm("admin")){
				?>
				<input type="HIDDEN" name="archiv_sem[]" value="_id_<? echo $seminar_id ?>" />
				<input type="CHECKBOX" name="archiv_sem[]" <? if ($select_all) echo checked; ?> />
				<?
				}
			break;
			}
		echo "</tr>";
		}			
	//}
	//Traurige Meldung wenn nichts gefunden wurde oder sonst irgendwie nichts da ist
	if ($c<0) {
		?>
		<tr>
			<?
			$srch_result="info§<font size=-1><b>Leider wurden keine Veranstaltungen ";
			if ($conditions) 
				$srch_result.="entsprechend Ihren Suchkriterien " ;
			$srch_result.="gefunden!</b></font>§";
			parse_msg ($srch_result, "§", "steel1", 2, FALSE);
			?>
		</tr>
		<?
		}		
			?>
				</tr>
				<tr>
					<td class="blank" colspan=1> 
					&nbsp; 
					</td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	</form>
	<?
	page_close();
	die;
	}
	?>
</td>
</tr>
</table>			
<?
}
}
?>