<?php
/*
suchen.php - Suche im Forensystem, Stud.IP
Copyright (C) 2001 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
?>
<html>
<head>
<?IF (!isset($SessSemName[0]) || $SessSemName[0] == "") {
    echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
    echo "</head></html>";
    die;
}
?>

<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
<body bgcolor="#333366" background="pictures/bathtile.jpg">
<DIV ID="overDiv" STYLE="position:absolute; visibility:hidden; z-index:1000;"></DIV>
<SCRIPT LANGUAGE="JavaScript" SRC="overlib.js"></SCRIPT>

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren --

	include "header.php";   //hier wird der "Kopf" nachgeladen 
	include "links1.php";
	include "links2.php";
	require_once "forum.inc.php";
	require_once "functions.php";
	require_once "visual.inc.php";
	require_once("suchen.inc.php");

IF ($SessSemName[1] =="")
	{
	parse_window ("error§Sie haben keine Veranstaltung gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher eine Veranstaltung gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Keine Veranstaltung gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}


?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr><td class=blank width=100% colspan=2>&nbsp;</td></tr>
<?
suchen($eintrag,$mehr,$suchbegriff,$check_author,$check_name,$check_cont,$seite=$PHP_SELF);
  // Save data back to database.
  page_close()
 ?>
 <tr><td class="blank" colspan=2 width="100%">&nbsp;</td></tr>
</table>
</body>
</html>