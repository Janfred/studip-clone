<?php
/*
seminar_main.php - Die Eingangs- und Uebersichtsseite fuer ein Seminar
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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); //Funktionen zur Anzeige der Terminstruktur
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");


if (isset($auswahl) && $auswahl!="") {
	//just opened Veranstaltung... here follows the init
	openSem($auswahl);
} else {
	$auswahl=$SessSemName[1];
}


// gibt es eine Anweisung zur Umleitung?
if(isset($redirect_to) && $redirect_to != "") {
	$take_it = 0;

	for ($i = 0; $i < count($i_query); $i++) { // alle Parameter durchwandern
		$parts = explode('=',$i_query[$i]);
		if ($parts[0] == "redirect_to") {
			// aha, wir haben die erste interessante Angabe gefunden
			$new_query = $parts[1];
			$take_it ++;
		}	elseif ($take_it) {
			// alle weiteren Parameter mit einsammeln
			if ($take_it == 1) { // hier kommt der erste
				$new_query .= '?';
			} else { // hier kommen alle weiteren
				$new_query .= '&';
			}
			$new_query .= $i_query[$i];
			$take_it ++;
		}
	}
	header("Location: $new_query");
	unset($redirect_to);
	page_close();
	die;
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

if ($SessSemName[1] =="") {
	parse_window ("error§Sie haben kein Objekt gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt (Veranstaltung oder Einrichtung) gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Kein Objekt gew&auml;hlt",
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
} else {
	include "links1.php";
	include "show_news.php";
	include "show_dates.inc.php";
	$sess->register("smain_data");
	//Auf und Zuklappen Termine
	if ($dopen)
		$smain_data["dopen"]=$dopen;

	if ($dclose)
		$smain_data["dopen"]='';

	//Auf und Zuklappen News
	if ($nopen)
		$smain_data["nopen"]=$nopen;

	if ($nclose)
		$smain_data["nopen"]='';

	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr><td class="topic" colspan=2><b>&nbsp;<? echo $SessSemName["art"],": ",htmlReady($SessSemName[0]), " - Kurzinfo"; ?>
	</b></td></tr>
	<tr><td class="blank" valign="top"><blockquote>
	<?

	if ($SessSemName[3]!="") {
		echo "<br /><b>Untertitel: </b>"; echo htmlReady($SessSemName[3]); echo"<br>";
	}

	echo "<br><b>Zeit: </b>", (view_turnus($SessionSeminar, FALSE));

	if ($SessSemName[2]!="") {
		echo "<br><b>Ort: </b>"; echo htmlReady($SessSemName[2]);
	}

	$db=new DB_Seminar;
	$db->query ("SELECT seminar_user.user_id, Vorname, Nachname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_user.Seminar_id = '$SessionSeminar' AND status = 'dozent' ORDER BY Nachname");
	if ($db->affected_rows() > 1)
		printf ("<br><b>%s: </b>", ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) ? "LeiterInnen" : "DozentInnen");
	else
		printf ("<br><b>%s: </b>", ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) ? "LeiterIn" : "DozentIn");

	$i=0;
	while ($db->next_record()) {
		if ($i)
			print( ", <a href = about.php?username=" . $db->f("username") . ">");
		else
			print( "<a href = about.php?username=" . $db->f("username") . ">");
		print(htmlReady($db->f("Vorname")) ." ". htmlReady($db->f("Nachname")) ."</a>");
		$i++;
	}
	?>
	</blockquote><br><br>
	</td>

	<td class="blank" align = right><img src="pictures/board2.jpg" border="0"></td>
	</tr></table><br>


	<?php

	// Anzeige von News


	($rechte) ? $show_admin=TRUE : $show_admin=FALSE;
	if (show_news($auswahl,$show_admin, 0, $smain_data["nopen"], "100%", $loginfilelast[$SessSemName[1]]))
		echo"<br>";
	// Anzeige von Terminen
	$start_zeit=time();
	$end_zeit=$start_zeit+1210000;
	$name = rawurlencode($SessSemName[0]);
	($rechte) ? $show_admin="admin_dates.php?range_id=$SessSemName[1]&ebene=sem&new_sem=TRUE" : $show_admin=FALSE;
	if (show_dates($auswahl, $start_zeit, $end_zeit, 0, 0, $show_admin, $smain_data["dopen"]))
		echo"<br>";
}
?>
</body>
</html>
<?php
  // Save data back to database.
  page_close();
 ?>
<!-- $Id$ -->