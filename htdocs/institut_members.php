<?php
/*
institut_mitarbeiter.php - Liste der Mitarbeiter eines Instituts
Copyright (C) 2000 Peter Thienel <pthienel@web.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include($ABSOLUTE_PATH_STUDIP."seminar_open.php"); //hier werden die sessions initialisiert
require_once($ABSOLUTE_PATH_STUDIP."config.inc.php");
require_once($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP."html_head.inc.php");

$css_switcher = new CssClassSwitcher();
echo $css_switcher->GetHoverJSFunction();

require($ABSOLUTE_PATH_STUDIP."header.php");   //hier wird der "Kopf" nachgeladen

$db_institut_members = new DB_Seminar();

// hier muessen Seiten-Initialisierungen passieren
if(isset($auswahl) && $auswahl != ""){
	// dieses Institut wurde gerade eben betreten
	$SessionSeminar = $auswahl;
	$db_institut_members->query("SELECT Name, Institut_id, type FROM Institute WHERE Institut_id='$auswahl'");
	while($db->next_record()){
		$SessSemName[0] = $db_institut_members->f("Name");
		$SessSemName[1] = $db_institut_members->f("Institut_id");
		$SessSemName["art_generic"] = "Einrichtung";
		$SessSemName["art"] = $INST_TYPE[$db_institut_members->f("type")]["name"];
		if(!$SessSemName["art"])
			$SessSemName["art"] = $SessSemName["art_generic"];
		$SessSemName["class"] = "inst";
		$nr = $db_institut_members->f("Institut_id");
		$loginfilelast["$nr"] = $loginfilenow["$nr"];
		$loginfilenow["$nr"] = time();
	}
}
else 
	$auswahl = $SessSemName[1];

// initialize session variable and store data given by URL
if(!isset($institut_members_data))
	$sess->register("institut_members_data");
if(!empty($i_query)){
		
	$accepted_vars = array("sortby", "direction", "show", "extend");
	
	reset($i_query);
	foreach($i_query as $key_value){
		list($key, $value) = explode("=", $key_value);
		if(in_array($key, $accepted_vars))
			$institut_members_data[$key] = $value;
	}
}
else
	$institut_members_data = "";

// check the given parameters or initialize them
$accepted_columns = array("Nachname", "Funktion", "Raum");
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

$accepted_functions = implode(",", $INST_FUNKTION_ORDER);

if($SessSemName[1] == ""){
	parse_window ("error§Sie haben kein Objekt gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Kein Objekt gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
}

require($ABSOLUTE_PATH_STUDIP."links1.php");

if ($institut_members_data["show"] != "list")
	$institut_members_data["show"] = "group";

// this array contains the data and structure of the table head
if ($institut_members_data["extend"] == "yes") {
	$table_structure = array(
											"name" => array("name" => "Name",
													"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
													"width" => "30%"),
											"raum" => array("name" => "Raum",
													"width" => "15%"),
											"sprechzeiten" => array("name" => "Sprechzeiten",
													"width" => "10%"),
											"telefon" => array("name" => "Telefon",
													"width" => "15%"),
											"email" => array("name" => "Email",
													"width" => "10%"),
											"home" => array("name" => "externe Homepage",
													"width" => "15%"),
											"nachricht" => array("name" => "Nachricht&nbsp;",
													"width" => "5%")
										);
	$colspan = 7;
}
else {
	$table_structure = array(
											"name" => array("name" => "Name",
													"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
													"width" => "35%"),
											"raum" => array("name" => "Raum",
													"width" => "20%"),
											"sprechzeiten" => array("name" => "Sprechzeiten",
													"width" => "20%"),
											"telefon" => array("name" => "Telefon",
													"width" => "15%"),
											"nachricht" => array("name" => "Nachricht&nbsp;",
													"width" => "10%")
											);
	$colspan = 5;
}

$query = "SELECT COUNT(*) AS count FROM user_inst WHERE Institut_id = '$auswahl'
					AND Funktion IN ($accepted_functions)";
$db_institut_members->query($query);
$db_institut_members->next_record();

echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
printf("\n<tr><td class=\"topic\" colspan=\"2\"><b>&nbsp; %s</b></td></tr>",
	"Mitarbeiter der Einrichtung");
	
if ($sms_msg) {
	echo "<tr><td class=\"blank\">";
	echo "<img src=\"pictures/blank.gif\" width=\"1\" height=\"5\"></td></tr>\n";
	parse_msg($sms_msg, "§", "blank", 1, FALSE);
}
	
echo "\n<tr><td class=\"blank\"><br /><blockquote>\n";

if ($db_institut_members->f("count") > 0)
	printf("Alle Mitarbeiter der Einrichtung <b>%s</b>", $SessSemName[0]);
else {
	printf("Der Einrichtung <b>%s</b> wurden noch keine Mitarbeiter zugeordnet!", $SessSemName[0]);
	echo "\n<br /><br /></blockquote>\n";
	echo "</td></tr></table\n";
	echo "</body></html>";
	page_close();
	die;
}
	
echo "\n</blockquote>\n";
echo "</td></tr>\n<tr><td class=\"blank\">";
echo "<img src=\"pictures/blank.gif\" width=\"1\" height=\"5\"></td></tr>\n";
echo "<tr><td class=\"blank\">\n";

echo "<table border=\"0\" width=\"99%\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr>\n";
echo "<td class=\"steel1\" width=\"60%\">\n";

if ($institut_members_data["show"] == "group") {
	echo "&nbsp; &nbsp; &nbsp; <a href=\"./institut_members.php?show=list\">";
	echo "<font size=\"-1\"><b>Alphabetische Liste anzeigen</b></font></a>\n";
}
else {
	echo "&nbsp; &nbsp; &nbsp; <a href=\"./institut_members.php?show=group\">";
	echo "<font size=\"-1\"><b>Nach Status gruppiert anzeigen</b></font></a>\n";
}
	
echo "</td><td class=\"steel1\" width=\"30%\">\n";
printf("<font size=\"-1\"><b>%s</b> %s</font>",
	$db_institut_members->f("count"), "Mitarbeiter gefunden");
echo "</td><td class=\"steel1\" width=\"10%\">\n";

if ($institut_members_data["extend"] == "yes") {
	echo "<a href=\"./institut_members.php?extend=no\">";
	echo "<img src=\"pictures/buttons/normaleansicht-button.gif\" border=\"0\">";
}
else {
	echo "<a href=\"./institut_members.php?extend=yes\">";
	echo "<img src=\"pictures/buttons/erweiterteansicht-button.gif\" border=\"0\">";
}

echo "</a>\n";
echo "</td></tr></table>\n";

echo "<table border=\"0\" width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";

table_head($table_structure, $css_switcher);

if ($institut_members_data["show"] == "group") {
	foreach ($INST_FUNKTION_ORDER as $key => $function) {
		if ($function != 0) {
			$query = sprintf("SELECT Nachname, Vorname, raum, sprechzeiten, Telefon, Email,
												auth_user_md5.user_id,
												username FROM user_inst LEFT JOIN	auth_user_md5 USING(user_id)
												WHERE user_inst.Institut_id = '%s' AND Funktion = '%s'
												ORDER BY %s %s", $auswahl, $function,
												$institut_members_data["sortby"], $institut_members_data["direction"]);
			$db_institut_members->query($query);
			if ($db_institut_members->num_rows() != 0) {
				printf("<tr><td class=\"steelgroup1\" colspan=\"%s\" height=\"20\">", $colspan);
				printf("<font size=\"-1\"><b>&nbsp;%s<b></font></td></tr>\n", $INST_FUNKTION[$function]["name"]);
				table_boddy($db_institut_members,$table_structure, $css_switcher);
			}
		}
	}
}
else {
	if ($institut_members_data["extend"] == "yes")
		$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
											ui.Funktion, aum.user_id, info.Home, 
											aum.Nachname, aum.Vorname,aum.Email, aum.username
											FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id)
											LEFT JOIN user_info info USING(user_id)
											WHERE ui.Institut_id = '%s' AND Funktion IN (%s)
											ORDER BY %s %s", $auswahl, $accepted_functions,
											$institut_members_data["sortby"], $institut_members_data["direction"]);
	else
		$query = sprintf("SELECT Nachname, Vorname, raum, sprechzeiten, Telefon, Funktion,
											Email, auth_user_md5.user_id,
											username FROM user_inst LEFT JOIN	auth_user_md5 USING(user_id)
											WHERE user_inst.Institut_id = '%s' AND Funktion IN (%s)
											ORDER BY %s %s", $auswahl, $accepted_functions,
											$institut_members_data["sortby"], $institut_members_data["direction"]);
	$db_institut_members->query($query);
	if ($db_institut_members->num_rows() != 0)
		table_boddy($db_institut_members,$table_structure, $css_switcher);
}

echo "<tr><td class=\"blank\" colspan=\"$colspan\">&nbsp;</td></tr>\n";
echo "</table></td></tr></table>\n";
echo "</body></html>";

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
			echo "<img src=\"pictures/blank.gif\" width=\"1\" height=\"25\" align=\"bottom\">&nbsp;";
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

		

function table_boddy ($db, $structure, $css_switcher) {
	global $INST_FUNKTION;
	$css_switcher->enableHover();
	
	while ($db->next_record()) {
	
		printf("<tr%s>\n", $css_switcher->getHover());
		if($db->f("Vorname") && $db->f("Nachname")) {
			printf("<td%s>", $css_switcher->getFullClass());
			echo "<img src=\"pictures/blank.gif\" width=\"2\" height=\"1\">";
			printf("<a href=\"about.php?username=%s\"><font size=\"-1\">%s</font></a></td>\n",
				$db->f("username"), htmlReady($db->f("Nachname") . ", " . $db->f("Vorname")));
		}
	
		if ($structure["funktion"]) {
			if ($db->f("Funktion") != 0)
				printf("<td%salign=\"center\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady($INST_FUNKTION[$db->f("Funktion")]["name"]));
			else
				printf("<td align=\"center\"><font size=\"-1\">%s</font></td>\n", "keine");
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
				printf("<td%salign=\"center\"><a href=\"mailto:%s\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), $db->f("Email"), $db->f("Email"));
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}
		
		if ($structure["home"]) {
			if ($db->f("Home")) {
				$home = mila($db->f("Home"), 20);
				printf("<td%salign=\"center\"><a href=\"%s\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), $db->f("Home"), $home);
			}
			else
				printf("<td%salign=\"center\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
		}
		
		if ($structure["nachricht"]) {
			printf("<td%salign=\"center\">\n",$css_switcher->getFullClass());
			printf("<a href=\"sms.php?sms_source_page=institut_members.php&cmd=write&rec_uname=%s\">",
				$db->f("username"));
			echo "<img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\" valign=\"baseline\"></a>";
			echo "\n</td>\n";
		}
		
		echo "</tr>\n";
		$css_switcher->switchClass();
	}
}

page_close();

?>
