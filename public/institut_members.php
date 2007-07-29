<?php
/*
institut_members.php - Liste der Mitarbeiter eines Instituts
Copyright (C) 2002 Peter Thienel <pthienel@web.de>

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
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include('lib/seminar_open.php'); //hier werden die sessions initialisiert
require_once('lib/language.inc.php');
require_once('config.inc.php');
require_once('lib/visual.inc.php');
require_once('lib/include/html_head.inc.php');
require_once('lib/statusgruppe.inc.php');
require_once 'lib/functions.php';
if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
}
$css_switcher = new CssClassSwitcher();
echo $css_switcher->GetHoverJSFunction();

$HELP_KEYWORD="Basis.EinrichtungenPersonal";
$CURRENT_PAGE = $SessSemName["header_line"]. " - " ._("Personal");

require('lib/include/header.php');   //hier wird der "Kopf" nachgeladen

$db_institut_members = new DB_Seminar();

$auswahl = $SessSemName[1];

// initialize session variable and store data given by URL
if(!isset($institut_members_data))
	$sess->register("institut_members_data");

if (isset($sortby))
	$institut_members_data["sortby"] = $sortby;
if (isset($direction))
	$institut_members_data["direction"] = $direction;
if (isset($show))
	$institut_members_data["show"] = $show;
if (isset($extend))
	$institut_members_data["extend"] = $extend;

// The script remembers the users settings for the hole duration of the session,
// remove the comments if you don't like this behavior.
//if($i_query[0] == "" && sizeof($_POST) == 0) {
//	$sess->unregister($institut_members_data);
//	unset($institut_members_data);
//}

// check the given parameters or initialize them
if ($perm->have_perm("admin"))
	$accepted_columns = array("Nachname", "inst_perms");
else
	$accepted_columns = array("Nachname");

if(!in_array($institut_members_data["sortby"], $accepted_columns))
	$institut_members_data["sortby"] = "Nachname";

if($institut_members_data["direction"] == "ASC")
	$new_direction = "DESC";
else if($institut_members_data["direction"] == "DESC")
	$new_direction = "ASC";
else{
	$institut_members_data["direction"] = "ASC";
	$new_direction = "DESC";
}

checkObject();
checkObjectModule("personal");

require('lib/include/links_openobject.inc.php');

// group by function as preset
switch ($institut_members_data["show"]) {
	case status :
		if ($perm->have_perm("admin"))
			break;
	case liste :
		break;
	default :
		$institut_members_data["show"] = "funktion";
}

// this array contains the structure of the table for the different views
if ($institut_members_data["extend"] == "yes") {
	switch ($institut_members_data["show"]) {
		case liste :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "10"),
												"raum" => array("name" => _("Raum"),
														"width" => "10%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "10%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "10%"),
												"email" => array("name" => _("E-Mail"),
														"width" => "10%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "10%"),
												"raum" => array("name" => _("Raum"),
														"width" => "10%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "10%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "10%"),
												"email" => array("name" => _("E-Mail"),
														"width" => "10%"),
												"home" => array("name" => _("externe Homepage"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
			break;
		case status :
			$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%"),
												"raum" => array("name" => _("Raum"),
														"width" => "10%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "15%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "10%"),
												"email" => array("name" => _("E-Mail"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			break;
		default :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "10"),
												"raum" => array("name" => _("Raum"),
														"width" => "15%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "15%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "15%"),
												"email" => array("name" => _("E-Mail"),
														"width" => "10%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"raum" => array("name" => _("Raum"),
														"width" => "15%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "10%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "15%"),
												"email" => array("name" => _("E-Mail"),
														"width" => "10%"),
												"home" => array("name" => _("externe Homepage"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
	} // switch
}
else {
	switch ($institut_members_data["show"]) {
		case liste :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "35%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "10"),
												"raum" => array("name" => _("Raum"),
														"width" => "20%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%"),
												"raum" => array("name" => _("Raum"),
														"width" => "15%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "20%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
			break;
		case status :
			$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "40%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "20%"),
												"raum" => array("name" => _("Raum"),
														"width" => "20%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			break;
		default :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "40%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "15"),
												"raum" => array("name" => _("Raum"),
														"width" => "20%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "20%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "40%"),
												"raum" => array("name" => _("Raum"),
														"width" => "20%"),
												"sprechzeiten" => array("name" => _("Sprechzeiten"),
														"width" => "20%"),
												"telefon" => array("name" => _("Telefon"),
														"width" => "15%"),
												"nachricht" => array("name" => _("Nachricht") . "&nbsp;",
														"width" => "5%")
												);
			}
	} // switch
}

$colspan = sizeof($table_structure);

if ($perm->have_perm("admin")) {
	$query = "SELECT COUNT(*) AS count FROM user_inst WHERE
						Institut_id = '$auswahl' AND inst_perms != 'user'";
	$db_institut_members->query($query);
	$db_institut_members->next_record();
	$count = $db_institut_members->f("count");
}
else
	$count = CountMembersStatusgruppen($auswahl);

echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";

if ($sms_msg) {
	echo "<tr><td class=\"blank\">";
	echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"5\"></td></tr>\n";
	parse_msg($sms_msg, "§", "blank", 1, FALSE);
	$sms_msg = '';
	$sess->unregister('sms_msg');
}

echo "\n<tr><td class=\"blank\"><br />&nbsp;<font size=\"-1\">\n";

if ($count > 0)
	printf("%s <b>%s</b>", _("Alle MitarbeiterInnen der Einrichtung"), htmlReady($SessSemName[0]));
else {
	printf(_("Der Einrichtung <b>%s</b> wurden noch keine MitarbeiterInnen zugeordnet!"), htmlReady($SessSemName[0]));
	echo "\n<br /><br /></font>\n";
	echo "</td></tr></table\n";
	include ('lib/include/html_end.inc.php');
	page_close();
	die;
}

echo "\n\n";
echo "</td></tr>\n<tr><td class=\"blank\">";
echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"5\"></td></tr>\n";
echo "<tr><td class=\"blank\">\n";

if ($perm->have_perm("admin")) {
	echo "<form action=\"./institut_members.php\" method=\"post\">\n";
}

echo "<table border=\"0\" width=\"99%\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr>\n";
echo "<td class=\"steel1\" width=\"60%\">\n";

// Admins can choose between different grouping functions
if ($perm->have_perm("admin")) {
	printf("<font size=\"-1\"><b>%s&nbsp;</b></font>\n", _("Gruppierung:"));
	printf("<select name=\"show\" style=vertical-align:middle><option %svalue=\"funktion\">%s</option>\n",
		($institut_members_data["show"] == "funktion" ? "selected " : ""), _("Funktion"));
	printf("<option %svalue=\"status\">%s</option>\n",
		($institut_members_data["show"] == "status" ? "selected " : ""), _("Status"));
	printf("<option %svalue=\"liste\">%s</option>\n",
		($institut_members_data["show"] == "liste" ? "selected " : ""), _("keine"));
	echo "</select>\n";
	echo "<input type=\"image\" border=\"0\" " . makeButton("uebernehmen", "src") . "style=vertical-align:middle />";
}
else {
	if ($institut_members_data["show"] == "funktion") {
		echo "&nbsp; &nbsp; &nbsp; <a href=\"./institut_members.php?show=liste\">";
		printf("<font size=\"-1\"><b>%s</b></font></a>\n", _("Alphabetische Liste anzeigen"));
	}
	else {
		echo "&nbsp; &nbsp; &nbsp; <a href=\"./institut_members.php?show=funktion\">";
		printf("<font size=\"-1\"><b>%s</b></font></a>\n", _("Nach Funktion gruppiert anzeigen"));
	}
}

echo "</td><td class=\"steel1\" width=\"30%\">\n";
printf("<font size=\"-1\">" . _("<b>%s</b> MitarbeiterInnen gefunden") . "</font>", $count);
echo "</td><td class=\"steel1\" width=\"10%\">\n";

if ($institut_members_data["extend"] == "yes") {
	echo "<a href=\"./institut_members.php?extend=no\">";
	echo makeButton("normaleansicht", "img");
}
else {
	echo "<a href=\"./institut_members.php?extend=yes\">";
	echo makeButton("erweiterteansicht", "img");
}

echo "</a>\n";
echo "</td></tr></table>\n";

if ($perm->have_perm("admin")) {
	echo "\n</form>\n";
}
echo "<table border=\"0\" width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";

table_head($table_structure, $css_switcher);

// if you have the right question you will get the right answer ;-)
if ($institut_members_data["show"] == "funktion") {
	$all_statusgruppen = GetAllStatusgruppen($auswahl);
	if ($all_statusgruppen) {
		foreach ($all_statusgruppen as $statusgruppe_id => $statusgruppe_name) {
			if ($institut_members_data["extend"] == "yes")
				$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.inst_perms, ui.raum,
								ui.sprechzeiten, ui.Telefon, ui.inst_perms, aum.Email, aum.user_id,
								aum.username, info.Home, statusgruppe_id
								FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) LEFT JOIN
								user_info info USING(user_id) LEFT JOIN user_inst ui USING(user_id)
								WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
								AND statusgruppe_id = '%s' ORDER BY %s %s", $auswahl, $statusgruppe_id,
								$institut_members_data["sortby"], $institut_members_data["direction"]);
			else
				$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, user_inst.raum, user_inst.sprechzeiten, user_inst.Telefon, inst_perms,
								Email, auth_user_md5.user_id, username, statusgruppe_id
								FROM statusgruppe_user  LEFT JOIN	auth_user_md5 USING(user_id)
								LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst USING(user_id)
								WHERE Institut_id = '%s' AND statusgruppe_id = '%s'
								AND inst_perms != 'user' ORDER BY %s %s", $auswahl, $statusgruppe_id,
								$institut_members_data["sortby"], $institut_members_data["direction"]);

			$db_institut_members->query($query);
			if ($db_institut_members->num_rows() > 0) {
				echo "<tr><td class=\"steelkante\" colspan=\"$colspan\" height=\"20\">";
				echo "<font size=\"-1\"><b>&nbsp;";
				echo htmlReady($statusgruppe_name);
				echo "<b></font></td></tr>\n";
				table_body($db_institut_members, $auswahl, $table_structure, $css_switcher);
			}
		}
	}
	if ($perm->have_perm("admin")) {
		$assigned = implode("','", GetAllSelected($auswahl));
		$db_residual = new DB_Seminar();
		if ($institut_members_data["extend"] == "yes")
			$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.inst_perms, ui.raum,
								ui.sprechzeiten, ui.Telefon, aum.Email, aum.user_id,
								aum.username
								FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id) LEFT JOIN user_info USING(user_id)
								WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
								AND ui.user_id NOT IN('%s') ORDER BY %s %s",
								$auswahl, $assigned, $institut_members_data["sortby"],
								$institut_members_data["direction"]);
		else
			$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.inst_perms, ui.raum,
								ui.Telefon, aum.user_id, aum.username
								FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id) LEFT JOIN user_info USING(user_id)
								WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
								AND ui.user_id NOT IN('%s')ORDER BY %s %s", $auswahl,
								$assigned,
								$institut_members_data["sortby"], $institut_members_data["direction"]);

		$db_residual->query($query);
		if ($db_residual->num_rows() > 0) {
			echo "<tr><td class=\"steelkante\" colspan=\"$colspan\" height=\"20\">";
			echo "<font size=\"-1\"><b>&nbsp;";
			echo _("keiner Funktion zugeordnet") . "<b></font></td></tr>\n";
			table_body($db_residual, $auswahl, $table_structure, $css_switcher);
		}
	}
}
elseif ($institut_members_data["show"] == "status") {
	$inst_permissions = array("admin" => _("Admin"), "dozent" => _("DozentIn"), "tutor" => _("TutorIn"),
														"autor" => _("AutorIn"), "user" => _("User"));
	foreach ($inst_permissions as $key => $permission) {
		$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.raum, ui.sprechzeiten, ui.Telefon,
											inst_perms, Email, auth_user_md5.user_id,
											username FROM user_inst ui LEFT JOIN	auth_user_md5 USING(user_id)
											LEFT JOIN user_info USING(user_id)
											WHERE ui.Institut_id = '%s' AND inst_perms = '%s'
											ORDER BY %s %s", $auswahl, $key,
											$institut_members_data["sortby"], $institut_members_data["direction"]);
		$db_institut_members->query($query);
		if ($db_institut_members->num_rows() > 0) {
			echo "<tr><td class=\"steelkante\" colspan=\"$colspan\" height=\"20\">";
			echo "<font size=\"-1\"><b>&nbsp;$permission<b></font></td></tr>\n";
			table_body($db_institut_members, $auswahl, $table_structure, $css_switcher);
		}
	}
}
else {
	if ($institut_members_data["extend"] == "yes") {
		if($perm->have_perm("admin"))
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon, ui.inst_perms,
							aum.user_id, info.Home, ". $_fullname_sql['full_rev'] ." AS fullname,aum.Email, aum.username
							FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id)
							LEFT JOIN user_info info USING(user_id)
							WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
							ORDER BY %s %s", $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
		else
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
							aum.user_id, info.Home,
							". $_fullname_sql['full_rev'] ." AS fullname, aum.Email, aum.username, Institut_id
							FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id)
							LEFT JOIN user_inst ui USING(user_id) LEFT JOIN auth_user_md5 aum USING(user_id)
							LEFT JOIN user_info info USING(user_id)
							WHERE range_id = '%s' AND Institut_id = '%s' GROUP BY user_id
							ORDER BY %s %s", $auswahl, $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
	}
	else {
		if($perm->have_perm("admin"))
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon, ". $_fullname_sql['full_rev'] ." AS fullname,
							inst_perms, username, ui.user_id
							FROM user_inst ui LEFT JOIN	auth_user_md5 USING(user_id)
							LEFT JOIN user_info USING(user_id)
							WHERE ui.Institut_id = '%s' AND inst_perms != 'user'
							ORDER BY %s %s", $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
		else
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
							aum.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, aum.username, Institut_id
							FROM statusgruppen LEFT JOIN statusgruppe_user su USING(statusgruppe_id)
							LEFT JOIN user_inst ui USING(user_id) LEFT JOIN auth_user_md5 aum USING(user_id)
							LEFT JOIN user_info USING(user_id)
							WHERE range_id = '%s' AND Institut_id = '%s' GROUP BY user_id
							ORDER BY %s %s", $auswahl, $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
	}
	$db_institut_members->query($query);
	if ($db_institut_members->num_rows() != 0)
		table_body($db_institut_members, $auswahl, $table_structure, $css_switcher);
}

if (($EXPORT_ENABLE) AND ($db_institut_members->num_rows() > 0) AND ($perm->have_perm("tutor")))
{
	include_once($PATH_EXPORT . "/export_linking_func.inc.php");
	echo "<tr><td colspan=$colspan><br>" . export_form($auswahl, "person", $SessSemName[0]) . "</td></tr>";
}
echo "<tr><td class=\"blank\" colspan=\"$colspan\">&nbsp;</td></tr>\n";
echo "</table></td></tr></table>\n";
include ('lib/include/html_end.inc.php');

function table_head ($structure, $css_switcher) {
	echo "<colgroup>\n";
	foreach ($structure as $field)
		printf("<col width=\"%s\">", $field["width"]);
	echo "\n</colgroup>\n";

	echo "<tr>\n";

	$begin = TRUE;
	foreach ($structure as $field) {
		if ($begin) {
			printf ("<td class=\"%s\" width=\"%s\" valign=\"baseline\">",
				$css_switcher->getHeaderClass(), $field["width"]);
			echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"25\" align=\"bottom\">&nbsp;";
			$begin = FALSE;
		}
		else
			printf ("<td class=\"%s\" width=\"%s\" align=\"center\" valign=\"bottom\">",
				$css_switcher->getHeaderClass(), $field["width"]);

		if ($field["link"]) {
			printf("<a href=\"%s\">", $field["link"]);
			printf("<font size=\"-1\"><b>%s</b></font>\n", $field["name"]);
			echo "</a>\n";
		}
		else
			printf("<font size=\"-1\"><b>%s</b></font>\n", $field["name"]);
		echo "</td>\n";
	}
	echo "</tr>\n";
}



function table_body ($db, $range_id, $structure, $css_switcher) {
	$css_switcher->enableHover();

	while ($db->next_record()) {

		$css_switcher->switchClass();
		printf("<tr%s>\n", $css_switcher->getHover());
		if($db->f("fullname")) {
			printf("<td%s>", $css_switcher->getFullClass());
			echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"2\" height=\"1\">";
			printf("<a href=\"about.php?username=%s\"><font size=\"-1\">%s</font></a></td>\n",
				$db->f("username"), htmlReady($db->f("fullname")));
		}
		else
			printf("<td%s>&nbsp;</td>", $css_switcher->getFullClass());

		if ($structure["statusgruppe"]) {
			$statusgruppen = GetStatusgruppen($range_id, $db->f("user_id"));
			if ($statusgruppen)
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady(implode(", ", $statusgruppen)));
			else
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), _("keine"));
		}

		if ($structure["status"]) {
			if ($db->f("inst_perms"))
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady($db->f("inst_perms")));
			else // It is actually impossible !
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}

		if ($structure["raum"]) {
			if ($db->f("raum"))
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady($db->f("raum")));
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}

		if ($structure["sprechzeiten"]) {
			if ($db->f("sprechzeiten"))
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady($db->f("sprechzeiten")));
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}

		if ($structure["telefon"]) {
			if ($db->f("Telefon"))
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady($db->f("Telefon")));
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}

		if ($structure["email"]) {
			if ($db->f("Email"))
				printf("<td%salign=\"center\"><font size=\"-1\"><a href=\"mailto:%s\">%s</a></font></td>\n",
					$css_switcher->getFullClass(), $db->f("Email"), $db->f("Email"));
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}

		if ($structure["home"]) {
			if ($db->f("Home")) {
				$home = mila($db->f("Home"), 20);
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), FixLinks(htmlReady($db->f("Home")), FALSE));
			}
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}

		if ($structure["nachricht"]) {
			printf("<td%salign=\"center\">\n",$css_switcher->getFullClass());
			if ($GLOBALS['CHAT_ENABLE']){
				echo chat_get_online_icon($db->f("user_id"),$db->f("username"),$GLOBALS['SessSemName'][1]) . "&nbsp;";
			}
			printf("<a href=\"sms_send.php?sms_source_page=institut_members.php&rec_uname=%s\">",
				$db->f("username"));
			printf("<img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" alt=\"%s\" ", _("Nachricht an User verschicken"));
			printf("title=\"%s\" border=\"0\" valign=\"baseline\"></a>", _("Nachricht an User verschicken"));
			echo "\n</td>\n";
		}

		echo "</tr>\n";
	}
}

page_close();

?>
