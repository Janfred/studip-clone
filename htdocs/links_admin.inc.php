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

if ($perm->have_perm("tutor")):	// Navigationsleiste ab status "Tutor"

require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/dates.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/msg.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$db4=new DB_Seminar;
$cssSw=new cssClassSwitcher;

$sess->register("links_admin_data");
$sess->register("sem_create_data");
$sess->register("admin_dates_data");

//neue Admin-Seminar-Sitzung, ich komme mit einer seminar_id rein
if (($i_page== "adminarea_start.php") && ($admin_sem_id)) {
	$links_admin_data='';
	$links_admin_data["sem_id"]=$admin_sem_id;
	$links_admin_data["select_old"]=TRUE;
	$links_admin_data["select_inactive"]=TRUE;
	$sem_create_data='';
	$admin_dates_data='';
	$admin_admission_data='';
	$archiv_assi_data='';
	$term_metadata='';
	$admin_dates_data='';
	$SessSemName[0] = "";
	$SessSemName[1] = "";
	}
//neue Admin-Institut-Sitzung, ich komme mit einer seminar_id rein
elseif ($admin_inst_id) {
	$links_admin_data='';
	$links_admin_data["inst_id"]=$admin_inst_id;
	$links_admin_data["select_old"]=TRUE;
	$links_admin_data["select_inactive"]=TRUE;
	$sem_create_data='';
	$admin_dates_data='';
	$admin_admission_data='';
	$archiv_assi_data='';
	$term_metadata='';
	$admin_dates_data='';
	$SessSemName[0] = "";
	$SessSemName[1] = "";
	}
//neue Admin-Sitzung, ich komme frisch rein ($list==TRUE)
elseif (($i_page== "adminarea_start.php") && ($list)) {
	$links_admin_data='';
	$links_admin_data["select_old"]=TRUE;
	$links_admin_data["select_inactive"]=TRUE;
	$sem_create_data='';
	$admin_dates_data='';
	$admin_admission_data='';	
	$archiv_assi_data='';
	$term_metadata='';	
	$admin_dates_data='';	
	$SessSemName[0] = "";
	$SessSemName[1] = "";
	}
//neue Admin-Seminar-Sitzung, ich komme aus einem Seminar rein ($new_sem==TRUE) 
elseif ((($SessSemName[1]) && ($new_sem)) || ($SessSemName[1] && !$links_admin_data["sem_id"]  && $new_sem)) {
	$links_admin_data='';
	$links_admin_data["select_old"]=TRUE;
	$links_admin_data["select_inactive"]=TRUE;
	$links_admin_data["sem_id"]=$SessSemName[1];
	$sem_create_data='';
	$admin_dates_data='';
	$admin_admission_data='';	
	$archiv_assi_data='';
	$term_metadata='';	
	}
//neue Admin-Institut-Sitzung, ich komme aus einem Institut rein ($new_inst==TRUE) 
elseif ((($SessSemName[1]) && ($new_inst)) || ($SessSemName[1] && !$links_admin_data["inst_id"]  && $new_inst)) {
	$links_admin_data='';
	$links_admin_data["inst_id"]=$SessSemName[1];
	}
elseif ($i_page== "adminarea_start.php")
	$list=TRUE;


if ($sortby) {
	$links_admin_data["sortby"]=$sortby;
	$list=TRUE;
	}
else
	$links_admin_data["sortby"]="Name";

if ($select_sem_id)
	$links_admin_data["sem_id"]=$select_sem_id;

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

//Wenn nur ein Institut verwaltet werden kann, immer dieses waehlen (Auswahl unterdruecken)
if ((!$links_admin_data["inst_id"]) && ($list) &&
	(($i_page == "admin_institut.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="inst") OR $i_page == "inst_admin.php"))) {
	if ($perm->have_perm("root"))
		$db->query("SELECT Institut_id  FROM Institute ORDER BY Name");
	else
		$db->query("SELECT Institute.Institut_id FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms = 'admin' ORDER BY Name");

	if (($db->num_rows() ==1) && (!$links_admin_data["inst_id"])) {
		$db->next_record();
		$links_admin_data["inst_id"]=$db->f("Institut_id");
	}
}

//Wenn Seminar_id gesetzt ist oder vorgewaehlt wurde, werden die spaeteren Seiten mit entsprechend gesetzten Werten aufgerufen
if ($links_admin_data["sem_id"]) {
	switch ($i_page) {
		case "admin_admission.php": 
			$seminar_id=$links_admin_data["sem_id"];
		break;
		case "admin_dates.php": 
			$range_id=$links_admin_data["sem_id"];
		break;
		case "admin_metadates.php": 
			$seminar_id=$links_admin_data["sem_id"];
		break;
		case "admin_literatur.php":
			if ($links_admin_data["view"]=="sem") {
				$range_id=$links_admin_data["sem_id"];
				$ebene="sem";
			}
		break;
		case "admin_news.php": 
			$range_id=$links_admin_data["sem_id"];
		break;
		case "archiv_assi.php": 
			$archiv_sem[]="_id_".$links_admin_data["sem_id"];
			$archiv_sem[]="on";
		break;
		case "admin_seminare1.php": 
			$s_id=$links_admin_data["sem_id"];
			if (!$s_command)
				$s_command="edit";
		break;
		}
	}

//Wenn Institut_id gesetzt ist oder vorgewaehlt wurde, werden die spaeteren Seiten mit entsprechend gesetzten Werten aufgerufen
if ($links_admin_data["inst_id"]) {
	switch ($i_page) {
		case "admin_institut.php": 
			$i_view=$links_admin_data["inst_id"];
		break;
		case "inst_admin.php": 
			$inst_id=$links_admin_data["inst_id"];
		break;
		case "admin_literatur.php": 
			if ($links_admin_data["view"]=="inst") {
				$range_id=$links_admin_data["inst_id"];
				$ebene="inst";
				}
		break;
		case "admin_news.php": 
			$range_id=$links_admin_data["inst_id"];
		break;
		}
	}


?>

<!-- obere Leiste -->

<table cellpadding="0" cellspacing="0" border="0">
<tr>
	<td class="steel1" nowrap>&nbsp; <img align="absmiddle" src="pictures/info.gif" 

<? //Reitersystem

if ($links_admin_data["sem_id"]) {
	$db->query ("SELECT Name FROM seminare WHERE Seminar_id = '".$links_admin_data["sem_id"]."' ");
	$db->next_record();
	}
if ($links_admin_data["inst_id"]) {
	$db->query ("SELECT Name FROM Institute WHERE Institut_id = '".$links_admin_data["inst_id"]."' ");
	$db->next_record();
	}
	
	if ($auth->auth["jscript"]) {
		echo "onClick=\"alert('Sie befinden sich im Administrationsbereich von Stud.IP.";
		if ($links_admin_data["sem_id"]) 
			echo " Ausgewählte Veranstaltung: ",JSReady($db->f("Name"),"popup"), " - Um die Auswahl aufzuheben, benutzen Sie bitte das Schlüsselsymbol.');\" ";
		elseif ($links_admin_data["inst_id"]) 
			echo " Ausgewählte Einrichtung: ",JSReady($db->f("Name"),"popup"), " - Um die Auswahl aufzuheben, benutzen Sie bitte das Schlüsselsymbol.');\" ";		
		else
			echo " Keine Veranstaltung oder Einrichtung ausgew&auml;hlt.');\" ";
		}
	?> alt="Sie befinden sich im Administrationsbereich von Stud.IP.<? 
	if ($links_admin_data["sem_id"])
		echo " Ausgew&auml;hlte Veranstaltung: ",htmlReady($db->f("Name")), " - Um die Auswahl aufzuheben, benutzen Sie bitte das Schlüsselsymbol.\" border=0>";
	elseif ($links_admin_data["inst_id"]) 
		echo " Ausgew&auml;hlte Einrichtung: ",htmlReady($db->f("Name")), " - Um die Auswahl aufzuheben, benutzen Sie bitte das Schlüsselsymbol.\" border=0>";
	else
		echo " Keine Veranstaltung ausgew&auml;hlt.\" border=0>&nbsp;";
	
	if ($links_admin_data["sem_id"])
		echo " <a href=\"adminarea_start.php?list=TRUE\"><img alt=\"Auswahl der Veranstaltung ", htmlReady($db->f("Name")), " aufheben\" align=\"absmiddle\" src=\"pictures/admin.gif\" border=0></a>";
	elseif ($links_admin_data["inst_id"]) 
		echo " <a href=\"adminarea_start.php?list=TRUE\"><img alt=\"Auswahl der Einrichtung ", htmlReady($db->f("Name")), " aufheben\" align=\"absmiddle\" src=\"pictures/admin.gif\" border=0></a>";
					
	if ($i_page != "admin_dates.php" AND $i_page != "admin_literatur.php" AND $i_page != "admin_news.php" AND $i_page != "admin_seminare1.php" AND $i_page != "admin_seminare_assi.php" AND $i_page != "admin_metadates.php" AND $i_page != "admin_admission.php" AND $i_page != "adminarea_start.php" AND $i_page != "archiv_assi.php" 
		OR $links_admin_data["sem_id"]
		OR (($i_page == "admin_news.php" AND $links_admin_data["view"]=="inst") OR $i_page == "admin_institut.php" OR $i_page == "inst_admin.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="inst")))
		echo "&nbsp; <img src=\"pictures/reiter2.jpg\" align=absmiddle>";
	else
		echo "&nbsp; <img src=\"pictures/reiter1.jpg\" align=absmiddle>";
	echo "</td>";	

if (($links_admin_data["sem_id"]) || ($links_admin_data["inst_id"])) {
	if ($links_admin_data["inst_id"])
		{?>  <td class="links1" align=right nowrap><a  class="links1" href="institut_main.php?auswahl=<? echo $links_admin_data["inst_id"] ?>"><font color="#000000" size=2><b>&nbsp; &nbsp; <? }
	else
		{?>  <td class="links1" align=right nowrap><a  class="links1" href="seminar_main.php?auswahl=<? echo $links_admin_data["sem_id"] ?>"><font color="#000000" size=2><b>&nbsp; &nbsp; <? }

	if (($SessSemName[0]) && (!$links_admin_data["assi"]))
		if ($links_admin_data["inst_id"])
			echo "zur&uuml;ck zur ausgew&auml;hlten Einrichtung";
		else
			echo "zur&uuml;ck zur ausgew&auml;hlten Veranstaltung";
	elseif ($links_admin_data["assi"])
		echo "zur neu angelegten Veranstaltung";
	elseif ($links_admin_data["inst_id"])
		echo "zur ausgew&auml;hlten Einrichtung";
	else 
		echo "zur ausgew&auml;hlten Veranstaltung";
	 ?>&nbsp; &nbsp; </b></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?
	}

if ($perm->have_perm("tutor")) {
	if (($i_page == "admin_news.php" AND $links_admin_data["view"]=="sem") OR $i_page == "admin_seminare1.php" OR $i_page == "admin_dates.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="sem") OR $i_page == "admin_metadates.php" OR $i_page == "admin_admission.php" OR $i_page == "admin_seminare_assi.php" OR $i_page == "adminarea_start.php" OR $i_page == "archiv_assi.php") {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="<? if ($links_admin_data["sem_id"]) echo "admin_seminare1.php"; else echo "adminarea_start.php?list=TRUE" ?>"><font color="#000000" size=2><b>&nbsp; &nbsp; Veranstaltungen&nbsp; &nbsp; </b></font></a><?
		if ($perm->have_perm("admin")) {?><img src="pictures/reiter2.jpg" align=absmiddle></td><? }
		ELSE {?><img src="pictures/reiter4.jpg" align=absmiddle></td><?} }
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="<? if ($links_admin_data["sem_id"]) echo "admin_seminare1.php"; else echo "adminarea_start.php?list=TRUE" ?>"><font color="#000000" size=2><b>&nbsp; &nbsp; Veranstaltungen&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}
}

if ($perm->have_perm("admin")) {
	if (($i_page == "admin_news.php" AND $links_admin_data["view"]=="inst") OR $i_page == "admin_institut.php" OR $i_page == "inst_admin.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="inst")) {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="admin_institut.php?list=TRUE"><font color="#000000" size=2><b>&nbsp; &nbsp; Einrichtungen&nbsp; &nbsp; </b></font></a><?
		?><img src="pictures/reiter2.jpg" align=absmiddle></td><?
	}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="admin_institut.php?list=TRUE"><font color="#000000" size=2><b>&nbsp; &nbsp; Einrichtungen&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}

	if ($i_page == "admin_fakultaet.php" OR $i_page == "admin_fach.php" OR $i_page == "admin_bereich.php" OR $i_page == "admin_studiengang.php" OR $i_page == "view_sessions.php" OR $i_page == "new_user_md5.php") {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="new_user_md5.php"><font color="#000000" size=2><b>&nbsp; &nbsp; globale Einstellungen&nbsp; &nbsp; </b></font></a><img src="pictures/reiter4.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="new_user_md5.php"><font color="#000000" size=2><b>&nbsp; &nbsp; globale Einstellungen&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}
}

//obere Reihe Ende
?>

</tr>
</table>

<!-- untere Leiste -->

<table cellspacing="0" cellpadding=4 border=0 width="100%">
<tr>
	<td class="steel1">&nbsp; &nbsp; 
<?
if (($i_page == "admin_news.php" AND $links_admin_data["view"]=="sem") OR $i_page == "admin_seminare1.php" OR $i_page == "admin_dates.php" OR $i_page == "admin_metadates.php" OR $i_page == "admin_admission.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="sem") OR $i_page == "admin_seminare_assi.php" OR $i_page == "archiv_assi.php"  OR $i_page == "adminarea_start.php")
{
	IF ($i_page == "admin_seminare1.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_seminare1.php?list=TRUE">Grunddaten&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_seminare1.php?list=TRUE">Grunddaten&nbsp; &nbsp; </a> <?}

	IF ($i_page == "admin_metadates.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_metadates.php?list=TRUE">Zeiten&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_metadates.php?list=TRUE">Zeiten&nbsp; &nbsp; </a> <?}

	IF ($i_page == "admin_dates.php") { ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_dates.php?list=TRUE">Ablaufpl&auml;ne&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_dates.php?list=TRUE">Ablaufpl&auml;ne&nbsp; &nbsp; </a> <?}

	IF ($i_page == "admin_literatur.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_literatur.php?list=TRUE&view=sem">Literatur/Links&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_literatur.php?list=TRUE&view=sem">Literatur/Links&nbsp; &nbsp; </a> <?}

	IF ($i_page == "admin_admission.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_admission.php?list=TRUE">Zugangsberechtigungen&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_admission.php?list=TRUE">Zugangsberechtigungen&nbsp; &nbsp; </a> <?}

	if ($perm->have_perm("admin")) {
		IF ($i_page == "archiv_assi.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="archiv_assi.php?list=TRUE&new_session=TRUE">archivieren&nbsp; &nbsp;  </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="archiv_assi.php?list=TRUE&new_session=TRUE">archivieren&nbsp; &nbsp;  </a> <?}
		}

	if ($perm->have_perm("dozent")) {
		IF ($i_page == "admin_seminare_assi.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_seminare_assi.php?new_session=TRUE">neue Veranstaltung&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_seminare_assi.php?new_session=TRUE">neue Veranstaltung&nbsp; &nbsp; </a> <?}
		}

	IF ($i_page == "admin_news.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_news.php?view=sem">News&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_news.php?view=sem">News&nbsp; &nbsp; </a> <?}

}

if (($i_page == "admin_news.php" AND $links_admin_data["view"]=="inst") OR $i_page == "inst_admin.php" OR $i_page == "admin_institut.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="inst"))
{

	IF (($i_page == "admin_institut.php") && ($i_view!="new")){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_institut.php?list=TRUE">Grunddaten&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_institut.php?list=TRUE">Grunddaten&nbsp; &nbsp; </a> <?}

	IF ($i_page == "inst_admin.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="inst_admin.php?list=TRUE">Mitarbeiter&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="inst_admin.php?list=TRUE">Mitarbeiter&nbsp; &nbsp; </a> <?}

	IF ($i_page == "admin_literatur.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_literatur.php?view=inst&list=TRUE">Literatur/Links&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_literatur.php?view=inst&list=TRUE">Literatur/Links&nbsp; &nbsp; </a> <?}

	IF ($i_page == "admin_news.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_news.php?view=inst&list=TRUE">News&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_news.php?view=inst&list=TRUE">News&nbsp; &nbsp; </a> <?}

	if ($perm->have_perm("root")) {
		IF (($i_page == "admin_institut.php") && ($i_view=="new")) { ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_institut.php?i_view=new"">neue Einrichtung&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_institut.php?i_view=new">neue Einrichtung&nbsp; &nbsp; </a> <?}
	}
}

if ($i_page == "admin_fakultaet.php" OR $i_page == "admin_fach.php" OR $i_page == "admin_bereich.php" OR $i_page == "admin_studiengang.php" OR $i_page == "view_sessions.php" OR $i_page == "new_user_md5.php")
{
	IF ($i_page == "new_user_md5.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="new_user_md5.php">Benutzer&nbsp; &nbsp; </a> <?}
	ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="new_user_md5.php">Benutzer&nbsp; &nbsp; </a> <?}

	if ($perm->have_perm("root")) {
		IF ($i_page == "admin_fakultaet.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_fakultaet.php">Fakult&auml;ten&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_fakultaet.php">Fakult&auml;ten&nbsp; &nbsp; </a> <?}

		IF ($i_page == "admin_fach.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2"  href="admin_fach.php">F&auml;cher&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_fach.php">F&auml;cher&nbsp; &nbsp; </a> <?}

		IF ($i_page == "admin_bereich.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_bereich.php">Bereiche&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_bereich.php">Bereiche&nbsp; &nbsp; </a> <?}

  		IF ($i_page == "admin_studiengang.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="admin_studiengang.php">Studieng&auml;nge&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="admin_studiengang.php">Studieng&auml;nge&nbsp; &nbsp; </a> <?}

		IF ($i_page == "view_sessions.php"){ ?><img src="pictures/forumrot.gif" border="0"><a class="links2" href="view_sessions.php">Sessions&nbsp; &nbsp; </a> <?}
		ELSE{ ?><img src="pictures/forumgrau.gif" border="0"><a class="links2" href="view_sessions.php">Sessions&nbsp; &nbsp; </a> <?}
	}
}
//Reitersystem Ende
?>
<br>
</td></tr>
<tr>
	<td class="reiterunten">&nbsp; </td>
</tr>
</table>
<?
//Einheitliches Auswahlmenu fuer Einrichtungen
if ((!$links_admin_data["inst_id"]) && ($list) &&
	(($i_page == "admin_institut.php" OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="inst") OR $i_page == "inst_admin.php"))) {
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
	<form name="links_admin_search" action="<? echo $PHP_SELF,"?", "view=$view"?>" method="POST">
	<tr>
		<td class="blank" colspan=2>&nbsp;
			<table cellpadding="0" cellspacing="0" border="0" width="99%" align="center">
				<tr>
					<td class="steel1">
							<font size=-1><br /><b>Bitte w&auml;hlen Sie die Einrichtung aus, die Sie bearbeiten wollen:</b><br/>&nbsp; </font>
					</td>
				</tr>
				<tr>
					<td class="steel1">
					<font size=-1><select class="steel1" name="admin_inst_id" size="1">
					<?
					if ($perm->have_perm("root"))
						$db->query("SELECT Institut_id, Name  FROM Institute ORDER BY Name");
					else
						$db->query("SELECT Institute.Institut_id, Name FROM Institute LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$user->id' AND inst_perms = 'admin' ORDER BY Name");
					
					printf ("<option value=\"0\">-- bitte Einrichtung ausw&auml;hlen --</option>\n");
					while ($db->next_record())
						printf ("><option %s value=\"%s\">%s </option></font>\n", $db->f("Institut_id") == $inst_id ? "selected" : "", $db->f("Institut_id"), htmlReady(substr($db->f("Name"), 0, 50)));
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
		<?
		page_close();
		die;
		}
	
//Einheitliches Seminarauswahlmenu, wenn kein Seminar gewaehlt ist
if ((!$links_admin_data["sem_id"]) && ($list) &&
	(( $i_page == "admin_seminare1.php" OR $i_page == "admin_dates.php" OR $i_page == "admin_metadates.php" OR $i_page == "admin_admission.php"  OR ($i_page == "admin_literatur.php" AND $links_admin_data["view"]=="sem") OR $i_page == "archiv_assi.php" OR $i_page == "adminarea_start.php"))) {

	//Umfangreiches Auswahlmenu nur ab Admin, alles darunter sollte eine uberschaubare Anzahl von Seminaren haben
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
							if ($perm->have_perm("root"))
								$db->query("SELECT * FROM Institute ORDER BY Name");
							else
								$db->query("SELECT * FROM Institute LEFT JOIN user_inst USING (Institut_id) WHERE user_id = '".$user->id."' AND inst_perms = 'admin' ORDER BY Name");
							if ($db->num_rows() >1) {
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
									}										
								?>								
							</select>
							<?
								}
							else {
								$db->next_record();
								$my_inst[]=$db->f("Institut_id");
								}
							?>
						</td>
						<td class="steel1">
							<?
							if (($perm->have_perm("admin")) && (!$perm->have_perm("root"))) {
							?>
							<font size=-1>Dozent:</font><br /><select name="srch_doz">
								<option value=0>alle</option>
								<?
								$query="SELECT DISTINCT * FROM auth_user_md5 LEFT JOIN user_inst USING(user_id) WHERE inst_perms='dozent' AND institut_id IN (";
								$i=0;
								foreach ($my_inst as $a) {
									if ($i)
										$query.= ", ";
									$query.="'".$a."'"; 
									$i++;
									}
								$query.=") ORDER BY Nachname";
								$db->query($query);
								if ($db->num_rows() >1) 
									while ($db->next_record()) {
										if ($links_admin_data["srch_doz"] == $db->f("user_id"))
											echo"<option selected value=".$db->f("user_id").">".$db->f("Nachname").", ".$db->f("Vorname")."</option>";
										else
											echo"<option value=".$db->f("user_id").">".$db->f("Nachname").", ".$db->f("Vorname")."</option>";
										}										
								?>								
							</select>
							<?
								}
							else {
								$db->next_record();
								$my_inst[]=$db->f("Institut_id");
								}
							if ($perm->have_perm("root")) {
								$db->query("SELECT * FROM Fakultaeten ORDER BY Name");
							?>
							<font size=-1>Fakult&auml;t:</font><br /><select name="srch_fak">
								<option value=0>alle</option>
								<?
								while ($db->next_record()) {
									if ($links_admin_data["srch_fak"] == $db->f("Fakultaets_id"))
										echo"<option selected value=".$db->f("Fakultaets_id").">".substr($db->f("Name"), 0, 30)."</option>";
									else
										echo"<option value=".$db->f("Fakultaets_id").">".substr($db->f("Name"), 0, 30)."</option>";
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
							<br />&nbsp;<font size=-1><input type="CHECKBOX" name="select_old" <? if ($links_admin_data["select_old"]) echo checked ?> />&nbsp;nur alte Veranstaltungen ausw&auml;hlen (Endsemester verstrichen) </font><br />
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
if (($links_admin_data["srch_on"]) || ($auth->auth["perm"] =="tutor") || ($auth->auth["perm"] == "dozent")){
$query="SELECT DISTINCT seminare.*, Institute.Name AS Institut, Fakultaeten.Name AS Fakultaet FROM seminare LEFT JOIN Institute USING (institut_id) LEFT JOIN Fakultaeten USING (Fakultaets_id) LEFT JOIN seminar_user ON (seminare.Seminar_id=seminar_user.Seminar_id AND seminar_user.status='dozent') LEFT JOIN auth_user_md5 USING (user_id)";
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
	$query.="Fakultaeten.Fakultaets_id ='".$links_admin_data["srch_fak"]."' ";
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
	$query.="((seminare.start_time + seminare.duration_time < ".time().") AND seminare.duration_time != '-1') ";
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
	$db2->query("select * from seminar_user WHERE Seminar_id = '$seminar_id' and user_id = '$user_id' AND (status = 'dozent' OR status = 'tutor')");
  	$db3->query("select * from seminare LEFT JOIN user_inst USING (Institut_id) where Seminar_id = '$seminar_id' and user_id = '$user_id' AND inst_perms = 'admin'");
	
  	 if ($perm->have_perm("root") ||
		($perm->have_perm("admin") && $db3->next_record()) || 
		($db2->next_record()) ) {
		
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
		$db4->query("SELECT Vorname, Nachname, username FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) where Seminar_id = '$seminar_id' and status = 'dozent'");
		$k=0;
		if (!$db4->num_rows())
			echo "&nbsp; ";
		while ($db4->next_record()) {
			if ($k)
				echo ", ";
			echo "<a href=\"about.php?username=".$db4->f("username")."\">".$db4->f("Vorname")." ".$db4->f("Nachname")."</a>";
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
	}
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
endif; // Navigationsleiste ab status "Tutor"
?>