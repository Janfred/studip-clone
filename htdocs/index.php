<?php
/*
index.php - Startseite von Stud.IP (anhaengig vom Status)
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

// database object
$db=new DB_Seminar;

// evaluate language clicks
// has to be done before seminar_open to get switching back to german (no init of i18n at all))
if (isset($set_language)) {
	$sess->register("forced_language");
	$forced_language = $set_language;
	$_language = $set_language;
}

// store  user-specific language preference
if ($auth->is_authenticated() && $user->id != "nobody") {
	// store last language click
	if (isset($forced_language)) {
		$db->query("UPDATE user_info SET preferred_language = '$forced_language' WHERE user_id='$user->id'");
		$_language = $forced_language;
		$sess->unregister("forced_language");
	} 
}

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
// -- hier muessen Seiten-Initialisierungen passieren --

// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

$sess->register("index_data");
		
//Auf und Zuklappen News
if ($nopen)
	$index_data["nopen"]=$nopen;
       
if ($nclose)
	$index_data["nopen"]='';
	
// Auf- und Zuklappen Termine
if ($dopen)
	$index_data["dopen"]=$dopen;
       
if ($dclose)
	$index_data["dopen"]='';
	
// Start of Output

include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");

// Display banner ad
if ($GLOBALS['BANNER_ADS_ENABLE'] && $auth->is_authenticated() && $user->id != "nobody") {
	require_once("banner_show.inc.php");
	banner_show();
}

//Anzeigemodul fuer studentische Startseite (nur wenn man angemeldet und nicht global dozent oder hoeher ist!)
if ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("dozent")) {
if (!$perm->have_perm("autor")) {  // Warning for Users
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topicwrite" colspan=3><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Bestätigungsgsmail beachten!")?></b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=2>
			<tr><td class="blank" colspan=2>
			<? my_info(_("<font size=-1>Sie haben noch nicht auf Ihre <a href=\"help/index.php?help_page=ii_bestaetigungsmail.htm\" target=\"new\">Bestätigungsmail</a> geantwortet.<br>Bitte holen Sie dies nach, um Stud.IP Funktionen wie das Belegen von Veranstaltungen nutzen zu können.<br>Bei Problemen wenden Sie Sich an: <a href=\"mailto:$UNI_CONTACT\">$UNI_CONTACT	</a>")); ?>
			</td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/sms3.jpg"><img src="pictures/blank.gif" width="235" height="1"></td>
</tr>
</table>
<br><br>
<?
}
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b>&nbsp;<?=_("Ihre pers&ouml;nliche Startseite bei Stud.IP")?></b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Meine Veranstaltungen")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="sem_portal.php"><?=_("Veranstaltung hinzuf&uuml;gen")?></a></td></tr>
		<tr><td class="blank"><a href="calendar.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Mein Planer")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="calendar.php"><?=_("Terminkalender")?></a>&nbsp;/&nbsp;<a href="contact.php"><?=_("Adressbuch")?></a>&nbsp;/&nbsp;<a href="mein_stundenplan.php"><?=_("Stundenplan")?></a></td></tr>
		<tr><td class="blank"><a href="about.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("pers&ouml;nliche Homepage")?></a><br />
			<? if ($perm->have_perm ("autor") ){ ?>
			&nbsp; &nbsp; <font size="-1"><a href="edit_about.php?view=allgemein"><?=_("individuelle Einstellungen")?></a>
			<? } ?>
		</td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Suchen")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php"><?=_("Personensuche")?></a>&nbsp;/&nbsp;<font size="-1"><a href="sem_portal.php"><?=_("Veranstaltungssuche")?></a></td></tr>
		<tr><td class="blank"><a href="help/index.php" target="_new"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Hilfe")?></a></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<?
	include ('show_news.php');
	if (show_news("studip", FALSE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";
	
	include("show_dates.inc.php");
	$start = time();
	$end = $start + 60 * 60 * 24 * 7;
	if ($GLOBALS['CALENDAR_ENABLE']) {
		show_all_dates($start, $end, TRUE, FALSE, $index_data["dopen"]);
	}
	else {
		show_dates($start, $end, $index_data["dopen"], "", 0, TRUE, FALSE, FALSE);
	}

        if ($GLOBALS['VOTE_ENABLE']) {
        	include ("show_vote.php");
	        show_votes ("studip", $auth->auth["uid"], $perm);
	}


} elseif (!$perm->have_perm("dozent")) {

//Anzeigemodul fuer nobody)
?>

<table class="blank" width="600"  border="0" cellpadding="0" cellspacing="0" align="center">
<tr><td colspan=3 class="topic" valign="middle">&nbsp;<b><? echo $UNI_NAME;?></b><img src="pictures/blank.gif" height="16" width="5" border="0"></td></tr>
<tr> 
	<td valign="middle" height="260" colspan=3 background="./pictures/startseite.jpg" alt="Stud.IP - <?=$UNI_NAME?>"">
		<img src="pictures/blank.gif" width="13" height="50" border="0" align="left"><br>
		<table  cellspacing="0" cellpadding="0"border="0">
		<tr>
			<?
			echo "<td class=\"steel1\" width=\"280\" valign=\"middle\"><a class=\"index\" href=\"index.php?again=yes\"><img src=\"./pictures/indexpfeil.gif\" align=left border=\"0\"><font size=\"4\"><b>"._("Login")."</b></font><br><font color=#555555 size=\"1\">"._("f&uuml;r registrierte NutzerInnen")."</font></a>&nbsp; </td>";

			?>
		<td class="shadowver" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr><tr><td class="shadowhor" width="280"><img src="pictures/blank.gif" width="10" height="3" border="0"></td>
		<td class="shadowcor" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr></table><br>
		<?
		if ($GLOBALS['ENABLE_SELF_REGISTRATION']){
		?>
		<img src="pictures/blank.gif" width="13" height="50" border="0" align="left">
		<table  cellspacing="0" cellpadding="0"border="0"><tr>
		
			<?
			echo "<td class=\"steel1\"><a class=\"index\" href=\"register1.php\"><img src=\"./pictures/indexpfeil.gif\" align=left border=\"0\"><font size=\"4\"><b>"._("Registrieren")."</b></font><br><font color=#555555 size=\"1\">"._("um NutzerIn zu werden")."</font></a>&nbsp; </td>";
			?>
		<td class="shadowver" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr><tr><td class="shadowhor" width="280"><img src="pictures/blank.gif" width="10" height="3" border="0"></td>
		<td class="shadowcor" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr></table><br>
		<?
		}
		?>
		<img src="pictures/blank.gif" width="13" height="50" border="0" align="left">
		<table  cellspacing="0" cellpadding="0"border="0"><tr>
			<?
			echo "<td class=\"steel1\"><a class=\"index\" href=\"freie.php\"><img src=\"./pictures/indexpfeil.gif\" align=left border=\"0\"><font size=\"4\"><b>"._("Freier Zugang")."</b></font><br><font color=#555555 size=\"1\">"._("ohne Registrierung")."</font></a>&nbsp; </td>";
			?>
		<td class="shadowver" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr><tr><td class="shadowhor" width="280"><img src="pictures/blank.gif" width="10" height="3" border="0"></td>
		<td class="shadowcor" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr></table><br>
		<img src="pictures/blank.gif" width="13" height="50" border="0" align="left">
		<table  cellspacing="0" cellpadding="0"border="0"><tr>
			<?
			echo "<td class=\"steel1\"><a class=\"index\" href=\"help/index.php\"><img src=\"./pictures/indexpfeil.gif\" align=left border=\"0\"><font size=\"4\"><b>"._("Hilfe")."</b></font><br><font color=#555555 size=\"1\">"._("zu Bedienung und Funktionsumfang")."&nbsp; &nbsp; </font></a>&nbsp; </td>";
			?>
		<td class="shadowver" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr><tr><td class="shadowhor" width="280"><img src="pictures/blank.gif" width="10" height="3" border="0"></td>
		<td class="shadowcor" width="3"><img src="pictures/blank.gif" width="3" border="0"></td>
		</tr></table><br>
	</td>
</tr>
<?
unset($temp_language_key); unset($temp_language);
?>
<tr>
<td class="blank" align="left" valign="middle">
	<img src="pictures/blank.gif" height="85" width="38" border="0">
</td>
<td class="blank" valign="middle" align="left"><a href="http://www.studip.de"><img src="pictures/logoklein.gif" border="0" <?=tooltip(_("Zur Portalseite"))?>></a></td>
<td class="blank" align=right nowrap valign="middle">
<?

//Statistics
$db=new DB_Seminar;
echo "<table cellspacing=\"0\" cellpadding=\"0\">";
$db->query("SELECT count(*) from seminare");
$db->next_record();
$anzahl = $db->f(0);
echo "<tr><td class=\"steel1\"><font size=\"2\" color=\"#555555\">&nbsp; "._("Aktive Veranstaltungen").":</font></td><td class=\"steel1\" align=right><font size=\"2\" color=\"#555555\">&nbsp; $anzahl&nbsp; </font></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>"; 
$db->query("SELECT count(*) from auth_user_md5 WHERE perms <> 'user'");
$db->next_record();
$anzahl = $db->f(0);			
echo "<tr><td class=\"steel1\"><font size=\"2\" color=\"#555555\">&nbsp; "._("Registrierte NutzerInnen").":</font></td><td class=\"steel1\" align=right><font size=\"2\" color=\"#555555\">&nbsp; $anzahl&nbsp; </font></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>"; 
$now = time()-600; 
$db->query("SELECT count(*) FROM active_sessions WHERE changed > '".date("YmdHis",$now)."' AND active_sessions.name = 'Seminar_User' AND sid != 'nobody'");
$db->next_record();
$anzahl = $db->f(0);			
echo "<tr><td class=\"steel1\"><font size=\"2\" color=\"#555555\">&nbsp; "._("Davon online").":</font></td><td class=\"steel1\" align=right><font size=\"2\" color=\"#555555\">&nbsp; $anzahl&nbsp; </font></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>"; 
echo "<tr><td height=\"30\" class=\"blank\" valign=\"middle\">";
// choose language
foreach ($INSTALLED_LANGUAGES as $temp_language_key => $temp_language) {
	printf ("&nbsp;&nbsp;<a href=\"%s?set_language=%s\"><img src=\"pictures/languages/%s\" %s border=\"0\"></a>", $PHP_SELF, $temp_language_key, $temp_language["picture"], tooltip($temp_language["name"]));
}
echo "</td><td align= right valign=\"top\" class=\"blank\"><a href=\"./impressum.php?view=statistik\"><font size=\"2\" color=#888888>"._("mehr")."... </font></a></td><td class=\"blank\">&nbsp; &nbsp; </td></tr>";
echo "</table>";

?>
</table>
<DIV align=center>
<?php
		
} elseif ($auth->auth["perm"]=="dozent") {

//Startseite fuer Dozenten 
?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b>&nbsp;<?=_("Startseite f&uuml;r DozentInnen bei Stud.IP")?></b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Meine Veranstaltungen")?></a></td></tr>
		<tr><td class="blank"><a href="adminarea_start.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Verwaltung von Veranstaltungen")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="admin_seminare_assi.php?new_session=TRUE"><?=_("neue Veranstaltung anlegen")?></a></font></td></tr>
		<tr><td class="blank"><a href="calendar.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Mein Planer")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="calendar.php"><?=_("Terminkalender")?></a>&nbsp;/&nbsp;<a href="contact.php"><?=_("Adressbuch")?></a>&nbsp;/&nbsp;<a href="mein_stundenplan.php"><?=_("Stundenplan")?></a></td></tr>
		<tr><td class="blank"><a href="about.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("pers&ouml;nliche Homepage")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="edit_about.php?view=allgemein"><?=_("individuelle Einstellungen")?></a></font></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Suchen")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php"><?=_("Personensuche")?></a>&nbsp;/&nbsp;<font size="-1"><a href="sem_portal.php"><?=_("Veranstaltungssuche")?></a></font></td></tr>
		<tr><td class="blank"><a href="help/index.php" target="_new"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Hilfe")?></a></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<div align="center">
<?
	include ('show_news.php');
	if (show_news("studip", FALSE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";
	
	include("show_dates.inc.php");
	$start = time();
	$end = $start + 60 * 60 * 24 * 7;
	if ($GLOBALS["CALENDAR_ENABLE"]) {
		show_all_dates($start, $end, TRUE, FALSE, $index_data["dopen"]);
	}
	else {
		show_dates($start, $end, $index_data["dopen"]);
	}

	if ($GLOBALS['VOTE_ENABLE']) {
		include ("show_vote.php");
		show_votes ("studip", $auth->auth["uid"], $perm);
	}

} elseif ($auth->auth["perm"]=="admin") {

//Startseite fuer Inst-Admins

?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0 >
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b>&nbsp;<?=_("Startseite f&uuml;r AdministratorInnen bei Stud.IP")?></b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="meine_seminare.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Veranstaltungen an meinen Einrichtungen")?></a></td></tr>
		<tr><td class="blank"><a href="adminarea_start.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Verwaltung von Veranstaltungen")?></a></td></tr>
		<tr><td class="blank"><a href="admin_institut.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Verwaltung von Einrichtungen")?></a></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Suchen")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php"><?=_("Personensuche")?></a>&nbsp;/&nbsp;<a href="sem_portal.php"><?=_("Veranstaltungssuche")?></a></font></td></tr>
		<tr><td class="blank"><a href="new_user_md5.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("globale Benutzerverwaltung")?></a></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<div align="center">
<?
	include ('show_news.php');
	if (show_news("studip", FALSE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";

        if ($GLOBALS['VOTE_ENABLE']) {
        	include ("show_vote.php");
	        show_votes ("studip", $auth->auth["uid"], $perm);
	}

} elseif ($perm->have_perm("root")) {

//Startseite fuer root

?>
<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic" colspan=3><img src="pictures/home.gif" border="0" align="texttop"><b><b>&nbsp;<?=_("Startseite f&uuml;r Root bei Stud.IP")?></b></b></td></tr>
<tr>
	<td width="5%" class="blank" valign="middle">&nbsp;</td>
	<td width="90%" class="blank" valign="top">
		<table cellpadding=4>
		<tr><td class="blank"><a href="sem_portal.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Veranstaltungs&uuml;bersicht")?></a></td></tr>
		<tr><td class="blank"><a href="adminarea_start.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Verwaltung von Veranstaltungen")?></a></td></tr>
		<tr><td class="blank"><a href="admin_institut.php?list=TRUE"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Verwaltung von Einrichtungen")?></a></td></tr>
		<tr><td class="blank"><a href="new_user_md5.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Verwaltung globaler Einstellungen")?></a></td></tr>
		<tr><td class="blank"><a href="auswahl_suche.php"><img src="pictures/forumrot.gif" border=0>&nbsp;<?=_("Suchen")?></a><br />&nbsp; &nbsp; <font size="-1"><a href="browse.php"><?=_("Personensuche")?></a>&nbsp;/&nbsp;<a href="sem_portal.php"><?=_("Veranstaltungssuche")?></a></font></td></tr>
		</table>
	</td>
	<td class="blank" align="right" valign="top" background="pictures/indexbild.jpg"><img src="pictures/blank.gif" width="235"></td>
</tr>
</table>

<br>
<?
	include ('show_news.php');
	if (show_news("studip", TRUE, 0, $index_data["nopen"], "70%", $LastLogin))
		echo "<br />";

  if ($GLOBALS['VOTE_ENABLE']) {
    include ("show_vote.php");
    show_votes ("studip", $auth->auth["uid"], $perm);
  }
}

?>
</td></tr></table>
</body>
</html>
<!-- $Id$ -->
<?php
  // Save data back to database.
  page_close();
?>


