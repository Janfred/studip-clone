<?php
# Lifter002: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_test_fak.php
// 
// 
// Copyright (c) 2003 André Noack <noack@data-quest>
// Suchi & Berg GmbH <info@data-quest.de>
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
// $Id$

$_test_institut_id = "7c7a4238e65b0662092d059b4514fa86";

$_test_id_prefix = "t_";

$_test_user_default['uname_prefix'] = "test_";
$_test_user_default['Vorname'] = "Hotte";
$_test_user_default['Nachname'] = "Testfreund";
$_test_user_default['Email'] = "hotte@testfreund.de";
$_test_user_default['password'] = md5("hochgeheim");

$_test_course_default['Name'] = "Testveranstaltung";
$_test_course_default['status'] = 8;
$_test_course_default['Lesezugriff'] = 2;
$_test_course_default['Schreibzugriff'] = 2;
$_test_course_default['start_time'] = time();
$_test_course_default['duration_time'] = -1;
$_test_course_default['art'] = "virtuelle Testveranstaltung";

function username_exists($uname){
	$db = new Db_Seminar("SELECT username FROM auth_user_md5 WHERE username='$uname'");
	return $db->next_record();
}

function init_values(){
	global $_test_institut_id,$_test_institut_name,$_test_users,$_test_courses,$_test_user_default,$_test_id_prefix;
	$db = new DB_Seminar();
	$db->query("SELECT Name FROM Institute WHERE Institut_id='$_test_institut_id'");
	$db->next_record();
	$_test_institut_name = $db->f("Name");
	$db->query("SELECT a.inst_perms,b.*,count(c.user_id) AS anzahl,IF(LEFT(a.user_id,2)='$_test_id_prefix',SUBSTRING_INDEX(username,'_',-1),0) AS usernumber, IF(LEFT(a.user_id,2)='$_test_id_prefix',1,0) AS is_testuser FROM user_inst a LEFT JOIN auth_user_md5 b USING(user_id) 
	 LEFT JOIN seminar_user c USING(user_id) 
	WHERE a.Institut_id='$_test_institut_id' AND a.inst_perms!='admin' GROUP BY a.user_id ORDER BY a.inst_perms,username");
	$_test_users = new DbSnapshot($db);
	$db->query("SELECT a.*,count(b.user_id) as anzahl,IF(LEFT(a.Seminar_id,2)='$_test_id_prefix',1,0) AS is_testsem FROM seminare a LEFT JOIN seminar_user b USING(Seminar_id)
	WHERE a.Institut_id='$_test_institut_id' GROUP BY a.Seminar_id ORDER BY Name");
	$_test_courses = new DbSnapshot($db);
}

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
if (!$perm->have_studip_perm("admin",$_test_institut_id)){
	$perm->perm_invalid(0,0);
	page_close();
	die;
}


include ('lib/seminar_open.php'); // initialise Stud.IP-Session

require_once 'lib/dates.inc.php';
require_once 'lib/classes/DbSnapshot.class.php';
require_once 'lib/forum.inc.php';
require_once('lib/admission.inc.php');	 //Enthaelt Funktionen zum Updaten der Wartelisten
require_once('lib/statusgruppe.inc.php');	 //Enthaelt Funktionen fuer Statusgruppen
require_once('lib/contact.inc.php');	 //Enthaelt Funktionen fuer Adressbuchverwaltung

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/DeleteResourcesUser.class.php");
}
if ($ILIAS_CONNECT_ENABLE) {
	include_once ("$RELATIVE_PATH_LEARNINGMODULES/lernmodul_db_functions.inc.php");
	include_once ("$RELATIVE_PATH_LEARNINGMODULES/lernmodul_user_functions.inc.php");
}

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
?>
<script type="text/javascript">
function invert_selection(formname){
	my_elements = document.forms[formname].elements['kill_items[]'];
	if(!my_elements.length){
		if(my_elements.checked)
			my_elements.checked = false;
		else
			my_elements.checked = true;
	} else {
		for(i = 0; i < my_elements.length; ++i){
			if(my_elements[i].checked)
				my_elements[i].checked = false;
			else
				my_elements[i].checked = true;
		}
	}
	return false;
}
</script>
<?

init_values();

if ($_REQUEST['cmd'] == 'user_form_send'){
	if (isset($_REQUEST['user_add_x'])){
		$num_add_user = $_REQUEST['num_add_user'];
		$perm_add_user = $_REQUEST['perm_add_user'];
		if (is_numeric($num_add_user) && $num_add_user < 50){
			if ($_test_users->numRows){
				$_test_users->sortRows("usernumber", "DESC", SORT_NUMERIC);
				$_test_users->nextRow();
				$offset = $_test_users->getField("usernumber");
				$_test_users->sortRows("inst_perms");
			} else {
				$offset = 0;
			}
			for ($i = 0; $i < $num_add_user; ++$i){
				do {
					++$offset;
					$uname = $_test_user_default['uname_prefix'] . $perm_add_user . "_" . $offset;
				} while (username_exists($uname));
				$uid = $_test_id_prefix . md5(uniqid("tolle_test_user",1));
				$db->query("INSERT INTO auth_user_md5 (username,user_id,perms,Vorname,Nachname,Email,password) VALUES 
				('$uname', '$uid', '$perm_add_user', '" . $_test_user_default['Vorname'] . "', '" . $_test_user_default['Nachname'] ." " . $offset
				. "', '" . $_test_user_default['Email'] . "', '" . $_test_user_default['password'] ."')");
				if ($db->affected_rows()){
					$db->query("INSERT INTO user_info (user_id) VALUES ('$uid')");
					$db->query("INSERT INTO user_inst (user_id,Institut_id,inst_perms) VALUES ('$uid','$_test_institut_id','" . (($perm_add_user == "autor") ? "user" : $perm_add_user) ."')");
				}
				if ($db->affected_rows()){
					++$num_added;
				}
			}
			$_msg = "msg§" . sprintf(_("Es wurden %s NutzerInnen mit der Berechtigung '%s' erzeugt."), $num_added,$perm_add_user) ."§";
		}
	} elseif (isset($_REQUEST['user_kill_x'])){
		$users_to_kill = $_REQUEST['kill_items'];
		if (is_array($users_to_kill) && count($users_to_kill)){
			$kill_list = "'" . join("','",$users_to_kill) . "'";
			$db->query("DELETE FROM user_inst WHERE user_id IN($kill_list)");
			$db->query("DELETE FROM user_info WHERE user_id IN($kill_list)");
			$db->query("DELETE FROM seminar_user WHERE user_id IN($kill_list)");
			$db2 = new DB_Seminar("SELECT seminar_id FROM admission_seminar_user where user_id IN($kill_list)");
			$db->query("DELETE FROM admission_seminar_user WHERE user_id IN($kill_list)");
			while ($db2->next_record()) {
				update_admission($db2->f("seminar_id"));
			}
			$db->query("DELETE FROM user_studiengang WHERE user_id IN($kill_list)");
			$db->query("SELECT dokument_id FROM dokumente WHERE user_id IN($kill_list)");
			while ($db->next_record()) {
				delete_document($db->f("dokument_id"));
			}
			$db->query("DELETE FROM archiv_user WHERE user_id IN($kill_list)");
			$db->query("DELETE FROM kategorien WHERE range_id IN($kill_list)");
			$db->query("DELETE FROM active_sessions WHERE sid IN($kill_list)");
			$db2->query("SELECT user_id,username FROM auth_user_md5 WHERE user_id IN ($kill_list)");
			while ($db2->next_record()){
				if(file_exists("./user/" . $db2->f("user_id") . ".jpg")) {
					unlink("./user/". $db2->f("user_id") .".jpg");
				}
				RemoveUserFromBuddys($db2->f("user_id"));
				if ($RESOURCES_ENABLE) {
					$killAssign = new DeleteResourcesUser($db2->f("user_id"));
					$killAssign->delete();
				}
				if ($ILIAS_CONNECT_ENABLE) {
					$this_ilias_id = get_connected_user_id($db2->f("user_id"));
					if (($this_ilias_id != false) AND (is_created_user($db2->f("user_id")) == 1))
						delete_ilias_user($this_ilias_id);
				}
				RemovePersonFromAllStatusgruppen($db2->f("username"));
				delete_range_of_dates($db2->f("user_id"), FALSE);
			}
			$db->query("DELETE FROM auth_user_md5 WHERE user_id IN($kill_list)");
			$_msg = "msg§" . sprintf(_("Es wurden %s NutzerInnen gelöscht."), $db->affected_rows()) ."§";
		}
	}
}

if ($_REQUEST['cmd'] == 'course_form_send'){
	if (isset($_REQUEST['course_add_x'])){
		$num_add_course = $_REQUEST['num_add_course'];
		for ($i = 0; $i < $num_add_course; ++$i){
			$seminar_id = $_test_id_prefix . md5(uniqid("tolle_test_veranstaltung",1));
			$db->query("INSERT INTO seminare (Seminar_id,Institut_id,Name,status,Lesezugriff,Schreibzugriff,start_time,duration_time,art,mkdate,chdate) VALUES 
			('$seminar_id','$_test_institut_id','" . $_test_course_default['Name'] . "', '" . $_test_course_default['status']
			. "', '" . $_test_course_default['Lesezugriff'] . "', '" . $_test_course_default['Schreibzugriff']
			. "', '" . $_test_course_default['start_time'] . "', '" . $_test_course_default['duration_time']
			. "', '" . $_test_course_default['art'] . "', UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
			if ($db->affected_rows()){
				CreateTopic('Allgemeine Diskussionen', " ", 'Hier ist Raum für allgemeine Diskussionen', 0, 0, $seminar_id, 0);
				$db->query("INSERT INTO folder SET folder_id='".md5(uniqid("tolle_test_ordner",1))."', range_id='" . $seminar_id . "',
				name='Allgemeiner Dateiordner', description='Ablage für allgemeine Ordner und Dokumente der Veranstaltung',
				mkdate='".time()."', chdate='".time()."'");
				$db->query("INSERT INTO seminar_inst (seminar_id,institut_id) VALUES ('$seminar_id','$_test_institut_id')");
			}
			if ($db->affected_rows()){
				++$num_added;
			}
		}
		$_msg = "msg§" . sprintf(_("Es wurden %s Veranstaltungen erzeugt."), $num_added) ."§";
	} elseif (isset($_REQUEST['course_kill_x'])){
		$courses_to_kill = $_REQUEST['kill_items'];
		if (is_array($courses_to_kill) && count($courses_to_kill)){
			$kill_list = "'" . join("','",$courses_to_kill) . "'";
			$db->query("DELETE FROM seminar_user WHERE Seminar_id IN ($kill_list)");
			$db->query("DELETE FROM admission_seminar_user WHERE seminar_id IN ($kill_list)");
			$db->query("DELETE FROM seminar_inst WHERE Seminar_id IN ($kill_list)");
			$db->query("DELETE FROM px_topics WHERE Seminar_id IN ($kill_list)");
			$db->query("DELETE FROM literatur WHERE range_id IN ($kill_list)");
			StudipSemTree::DeleteSemEntries(null, $courses_to_kill);
			for ($i = 0; $i < count($courses_to_kill); ++$i){
				$s_id = $courses_to_kill[$i];
				if ($RESOURCES_ENABLE) {
					$killAssign = new DeleteResourcesUser($s_id);
					$killAssign->delete();
				}
				recursiv_folder_delete($s_id);
				delete_range_of_dates($s_id, TRUE);
				DeleteAllStatusgruppen($s_id);
			}
			$db->query("DELETE FROM seminare WHERE Seminar_id IN ($kill_list)");
			$_msg = "msg§" . sprintf(_("Es wurden %s Veranstaltungen gelöscht."), $db->affected_rows()) ."§";
		}
	}
}

if ($_REQUEST['cmd'] == 'user_course_send'){
	$user_id = $_REQUEST['user_id'];
	if (isset($_REQUEST['user_course_add_x'])){
		$db->query("SELECT perms FROM auth_user_md5 WHERE user_id='$user_id'");
		$db->next_record();
		if (($seminar_id = $_REQUEST['user_course_add_course']) && ($user_perm = $db->f(0))){
			$db->query("INSERT INTO seminar_user (user_id,Seminar_id,status,mkdate) VALUES ('$user_id','$seminar_id','" . $user_perm ."',UNIX_TIMESTAMP())");
			if ($db->affected_rows()){
				$_msg = "msg§" . _("NutzerIn wurde zugeordnet.") ."§";
			}
		}
	} elseif (isset($_REQUEST['user_course_kill_x'])){
		$user_courses_to_kill = $_REQUEST['kill_items'];
		if (is_array($user_courses_to_kill) && count($user_courses_to_kill)){
			$kill_list = "'" . join("','",$user_courses_to_kill) . "'";
			$db->query("DELETE FROM seminar_user WHERE Seminar_id IN ($kill_list) AND user_id='$user_id'");
			if ($db->affected_rows()){
				$_msg = "msg§" . sprintf(_("%s Zuordnungen wurden aufgehoben."),$db->affected_rows()) ."§";
			}
		}
	}
	$_REQUEST['cmd'] = "user_course_change";
}
	
init_values();

?>
<body>
<table width="100%" border="0" cellpadding="2" cellspacing="0">
	<tr>
		<td class="topic"><b>&nbsp;<?=_("Administration der Testumgebung: ") . $_test_institut_name?></b></td>
	</tr>
	<tr>
	<td class="blank" width="100%" align="left" valign="top">
	<?
if ($_msg)	{
	echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
	parse_msg ($_msg,"§","blank",1,false);
	echo "\n</table>";
}

if ($_REQUEST['cmd'] == "user_course_change"){
	$user_id = $_REQUEST['user_id'];
	echo "\n<form name=\"user_course_form\" action=\"$PHP_SELF?cmd=user_course_send&user_id=$user_id\" method=\"post\"><br>";
	echo "\n<div style=\"margin-left:10px;\"><b>" . _("Veranstaltungen des Nutzenden") . "&nbsp;" . get_fullname($user_id) ."</b>";
	$db->query("SELECT a.Seminar_id,a.status,b.Name FROM seminar_user a LEFT JOIN seminare b USING (Seminar_id) WHERE a.user_id='$user_id' AND b.Institut_id='$_test_institut_id' ORDER BY Name");
	$_user_course = new DbSnapshot($db);
	if (!$_user_course->numRows){
			echo "\n<br>" . _("Keine Veranstaltungen gefunden.") ."<br>";
	} else {
		echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\" style=\"font-size:10pt;\">";
		echo "\n<tr><th>" . _("Name der Veranstaltung") . "</th><th>" . _("Status in der Veranstaltung") . "</th><th><a href=\"#\" onClick=\"return invert_selection('user_course_form');\" title=\"Auswahl umkehren\">" . _("Entfernen") . "</a></th></tr>";
		while ($_user_course->nextRow()){
			echo "\n<tr><td class=\"steel1\"><a href=\"seminar_main.php?auswahl=" . $_user_course->getField('Seminar_id') ."\">" 
			. htmlReady($_user_course->getField('Name'))
			. "</a></td>";
			echo "\n<td class=\"steel1\" align=\"center\">" . $_user_course->getField('status') ."</td>";
			echo "\n<td class=\"steel1\" align=\"center\"><input name=\"kill_items[]\" type=\"checkbox\" value=\"" . $_user_course->getField('Seminar_id') . "\"></td>";
			echo "\n</tr>";
		}
		echo "\n<tr><td class=\"steel1\" align=\"right\" colspan=\"4\"><input name=\"user_course_kill\" type=\"image\" align=\"absmiddle\" " 
		. makeButton("loeschen","src") . "></td></tr>";
		echo "\n</table>";
	}
	$clause = "";
	if ($_user_course->numRows){
		$is_in_course = array_flip($_user_course->getRows("Seminar_id"));
	}
	echo "<select name=\"user_course_add_course\" style=\"vertical-align:middle\">";
	while ($_test_courses->nextRow()){
		if (!isset($is_in_course[$_test_courses->getField("Seminar_id")])){
			echo "\n<option value=\"". $_test_courses->getField("Seminar_id") ."\">" . htmlReady($_test_courses->getField('Name') ." - " . strftime("%d.%m.%y",$_test_courses->getField('mkdate'))) ."</option>";
		}
	}
	echo "</select>&nbsp;"
	. _("NutzerIn eintragen") . "&nbsp;<input name=\"user_course_add\" type=\"image\" align=\"absmiddle\" " 
	. makeButton("ok","src") . "></form></div><div align=\"center\"><a href=\"$PHP_SELF\">" . makeButton("zurueck") . "</a></div>";
	
	
	
} else {
	echo "\n<div style=\"margin-left:10px;margin-top:10px;font-size:10pt;\"><a href=\"admin_institut.php?admin_inst_id=$_test_institut_id\"><img " . makeButton("weiter","src")." hspace=\"5\" align=\"absmiddle\" border=\"0\"></a>" . _("zur Verwaltung der Einrichtung") . "</div>";
	echo "\n<form name=\"user_form\" action=\"$PHP_SELF?cmd=user_form_send\" method=\"post\"><br>";
	echo "\n<div style=\"margin-left:10px;\"><b>" . _("NutzerIn") . "&nbsp({$_test_users->numRows})</b>";
	if (!$_test_users->numRows){
		echo "\n<br>" . _("Keine NutzerInnen gefunden.") ."<br>";
	} else {
		echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\" style=\"font-size:10pt;\">";
		echo "\n<tr><th>" . _("Name") . "</th><th>" . _("Status in der Einrichtung") . "</th><th>" . _("Anzahl Veranstaltungen") . "</th><th><a href=\"#\" onClick=\"return invert_selection('user_form');\" title=\"Auswahl umkehren\">" . _("L&ouml;schen") . "</a></th></tr>";
		while ($_test_users->nextRow()){
			echo "\n<tr><td class=\"steel1\"><a href=\"new_user_md5.php?details=" . $_test_users->getField('username') ."\">" 
			. htmlReady($_test_users->getField('Vorname') . " " . $_test_users->getField('Nachname') . " (" . $_test_users->getField('username') .")")
			. "</a></td>";
			echo "\n<td class=\"steel1\" align=\"center\">" . $_test_users->getField('inst_perms') ."</td>";
			echo "\n<td class=\"steel1\" align=\"center\">" . $_test_users->getField('anzahl') ."&nbsp;<a href=\"$PHP_SELF?cmd=user_course_change&user_id=" .$_test_users->getField('user_id')."\">[zuordnen]</a></td>";
			echo "\n<td class=\"steel1\" align=\"center\">";
			echo ($_test_users->getField("is_testuser")) ? "<input name=\"kill_items[]\" type=\"checkbox\" value=\"" . $_test_users->getField('user_id') . "\">" : "&nbsp;";
			echo "</td>";
			echo "\n</tr>";
		}
		echo "\n<tr><td class=\"steel1\" align=\"right\" colspan=\"4\"><input name=\"user_kill\" type=\"image\" align=\"absmiddle\" " 
		. makeButton("loeschen","src") . "></td></tr>";
		echo "\n</table>";
	}
	echo "<input type=\"text\" size=\"2\" value=\"1\" name=\"num_add_user\" style=\"vertical-align:middle\">&nbsp;"
	."<select name=\"perm_add_user\" style=\"vertical-align:middle\"><option>autor</option><option>tutor</option><option>dozent</option></select>&nbsp;"
	. "<span style=\"font-size:10pt;\">" . _("NutzerIn neu anlegen") . "</span>&nbsp;<input name=\"user_add\" type=\"image\" align=\"absmiddle\" " 
	. makeButton("anlegen","src") . "></form></div>"; 
	
	echo "\n<form name=\"course_form\" action=\"$PHP_SELF?cmd=course_form_send\" method=\"post\"><br>";
	echo "\n<div style=\"margin-left:10px;\"><b>" . _("Veranstaltungen") . "&nbsp({$_test_courses->numRows})</b>";
	if (!$_test_courses->numRows){
		echo "\n<br>" . _("Keine Veranstaltungen gefunden.") . "<br>";
	} else {
		echo "\n<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\" style=\"font-size:10pt;\">";
		echo "\n<tr><th>" . _("Name") . "</th><th>" . _("angelegt am") . "</th><th>" . _("Anzahl Nutzende") . "</th><th><a href=\"#\" onClick=\"return invert_selection('course_form');\" title=\"Auswahl umkehren\">" . _("L&ouml;schen") . "</a></th></tr>";
		while ($_test_courses->nextRow()){
			echo "\n<tr><td class=\"steel1\"><a href=\"seminar_main.php?auswahl=" . $_test_courses->getField('Seminar_id') ."\">" 
			. htmlReady($_test_courses->getField('Name'))
			. "</a></td>";
			echo "\n<td class=\"steel1\" align=\"center\">" . strftime("%d.%m.%y",$_test_courses->getField('mkdate')) ."</td>";
			echo "\n<td class=\"steel1\" align=\"center\">" . $_test_courses->getField('anzahl') ."</td>";
			echo "\n<td class=\"steel1\" align=\"center\">";
			echo ($_test_courses->getField('is_testsem')) ? "<input name=\"kill_items[]\" type=\"checkbox\" value=\"" . $_test_courses->getField('Seminar_id') . "\">" : "&nbsp;";
			echo "</td>";
			echo "\n</tr>";
		}
		echo "\n<tr><td class=\"steel1\" align=\"right\" colspan=\"4\"><input name=\"course_kill\" type=\"image\" align=\"absmiddle\" " 
		. makeButton("loeschen","src") . "></td></tr>";
		echo "\n</table>";
	}
	echo "<input type=\"text\" size=\"2\" value=\"1\" name=\"num_add_course\" style=\"vertical-align:middle\">&nbsp;"
	."&nbsp;<span style=\"font-size:10pt;\">" . _("Veranstaltung(en) neu anlegen") . "</span>&nbsp;<input name=\"course_add\" type=\"image\" align=\"absmiddle\" " 
	. makeButton("anlegen","src") . "></form></div>"; 
}
?>
</table>
<?php
include ('lib/include/html_end.inc.php');
page_close();
?>