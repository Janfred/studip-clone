<?php
/*
auswahl_suche.php - Uebersicht ueber die Suchfunktion von Stud.IP
Copyright (C) 2001 Cornelis Kater <ckater@gwdg.de>

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

include ("seminar_open.php");

?>
<html>
<head>

<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
<body bgcolor=white>

<?php
	
	include "header.php";   //hier wird der "Kopf" nachgeladen 

?>
<table width="70%" border=0 cellpadding=0 cellspacing=0 align="center">

<tr>
	<td class="topic" colspan=2><img src="pictures/suchen.gif" border="0" align="texttop"><b>&nbsp;Suchen in Stud.IP</b></td>
</tr>
<tr>
<td class="blank" width=100%">
<blockquote>
	<br><a href="browse.php"><b>Suchen nach Personen</b></a></br>
	<font size=-1>Hier k&ouml;nnen sie nach ihren in Stud.IP angemeldeten Kommiltonen und Dozenten suchen.</font>
	<br>
	<br><a href="sem_portal.php?view=Alle&reset_all=TRUE"><b>Suchen nach Veranstaltungen</b></a></br>
	<font size=-1>Hier finden sie alle Veranstaltungen in Stud.IP.</font>
	<br>
	<br><a href="institut_browse.php"><b>Suchen nach Einrichtungen</b></a></br>
	<font size=-1>Hier finden sie alle Einrichtungen und Institute  in Stud.IP.</font>
	<br>
	<br><a href="archiv.php"><b>Suchen im Archiv</b></a></br>
	<font size=-1>Hier finden sie alle Veranstaltungen vergangener Semester.</font>
	<br>
	<br>
</td>
<td class="blank" align="right" valign="top"><img src="pictures/suche.jpg" border="0"></td>
</tr>

<?  // Save data back to database.
  page_close()
 ?>
</table>
</body>
</html>