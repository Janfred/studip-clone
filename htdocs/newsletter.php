<?php
/*
newsletter.php - Seite f&uuml;r nobody zum ein- austragen von Newsletter-Abbos
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$magic     = "ddvedvgngda";  ## Challenge seed

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/newsletter.inc.php");

?>
<br>
<table class="blank" width="70%" border=0 cellpadding=0 cellspacing=0 align="center">
	<tr>
		<td class="topic" colspan="2"><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;Newsletter Verwaltung</b>
		</td>
	</tr>
	<tr>
		<td>
<?
	$hash = md5("$username:$magic");
	// hier wird noch mal berechnet, welches secret in der Bestaetigungsmail uebergeben wurde

	if (!isset($secret) || $secret == "" || !isset($username) || $username== "" || !isset($newsletter_id)) {   // Volltrottel (oder abuse)
		echo "<table class=\"blank\">";
		my_error("<b>Sie m&uuml;ssen den vollst&auml;ndigen Link aus aus einem der Newsletter<br>\nin die Zeile \"Adresse\", \"Location\" oder \"URL\" Ihres Browsers kopieren.</b>\n");
		print "<tr><td class=\"blank\" colspan=2><b>&nbsp;Versuchen Sie es noch einmal!</b><br><br>\n";
		echo "</td></tr></table>";
	} elseif ($secret != $hash) {   // wahrscheinlich URL-Hacking oder unvollstaendige URL
		echo "<table class=\"blank\">";
		my_error("<b>Der &uuml;bergebene \"Secret-Code\" ist nicht korrekt.</b>\n");
		my_info("Sie m&uuml;ssen den vollst&auml;ndigen Link aus einem der Newsletter<br>\nin die Zeile \"Adresse\", \"Location\" oder \"URL\" Ihres Browsers kopieren.\n");
		print "<tr><td class=\"blank\" colspan=2 width=\"100%\"><b>&nbsp;Versuchen Sie es noch einmal!</b><br><br>\n";
		echo "</td></tr></table>";
	} else { // stimmt alles

		echo "<table class=\"blank\">";

		// haben wir ein Kommando?
	
		if ($cmd =="add") {  // soll rein
			$msg = AddPersonNewsletter ($username, $newsletter_id);
		} elseif ($cmd == "remove") {
			$msg = RemovePersonNewsletter ($username, $newsletter_id);
		}
	
		// Ausgabe Info
		if ($msg) parse_msg($msg);
		$status = CheckStatusPersonNewsletter ($username, $newsletter_id);
		echo "	<tr>";
		echo "		<td class=\"blank\">";
		printf ("Ihr aktueller Status im Newsletter \"%s\":<br><br><b>%s</b><br><br>",$newsletter[$newsletter_id]["name"], $status);

		// wieder anders:
	
		if ($status == "Eingetragen") {
			printf ("Um sich aus dem Newsletter wieder auszutragen,<br>klicken Sie bitte %s hier</a>.","<a href= \"$PHP_SELF?username=$username&newsletter_id=$newsletter_id&cmd=remove&secret=$secret\">");
		} else {
			printf ("Um sich in den Newsletter wieder einzutragen,<br>klicken Sie bitte %s hier</a>.","<a href= \"$PHP_SELF?username=$username&newsletter_id=$newsletter_id&cmd=add&secret=$secret\">");
		}
		echo "</td></tr></table>";
	}
	
  page_close();
?>
	</td>
	<td class="blank" align = right valign="top"><img src="pictures/brief.jpg" border="0">
	</td>
    </tr>
</table>
</body>
</html>
<!-- $Id$ -->