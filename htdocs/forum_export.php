<?
/*
folder.php - Anzeige und Verwaltung des Ordnersystems
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
	page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
?>

<html>
<head>

<?
IF (!isset($SessSemName[0]) || $SessSemName[0] == "") {
	echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
	echo "</head></html>";
	die;
	}

IF ($htmlversion == TRUE) {
	?>
	<title>Stud.IP</title>
		<link rel="stylesheet" href="style.css" type="text/css">
	</head>
	<? 
	}
ELSE {
	?>
	<title>Stud.IP</title>
		<link rel="stylesheet" href="style_print.css" type="text/css">
	</head>
	<? 
	}
	
?>
<body bgcolor=white>
<?
//////////////////////////////////////////////////////////////////////////

include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren

// include "header.php";   //hier wird der "Kopf" nachgeladen
require_once "functions.php";
require_once "visual.inc.php";
require_once "msg.inc.php";
require_once "dates.inc.php"; 
require_once "archiv.inc.php";

IF ($htmlversion==TRUE) echo "<a href=\"$PHP_SELF\">zur Druck-Ansicht</a>";
ELSE echo "<a href=\"$PHP_SELF?htmlversion=true\">zur HTML-Ansicht</a>";
echo "<h1>Forum:&nbsp; ".$SessSemName[0]."</h1>";
echo Export_Topic($SessSemName[1]);

echo "<table width=100% border=0 cellpadding=2 cellspacing=0>";
echo "<tr><td><i><font size=-1>Stand: ".date("d.m.y",time()).", ".date("G:i", time())." Uhr.</font></i></td><td align=\"right\"><font size=-2><img src=\"pictures/logo2b.gif\"><br />&copy; ".date("Y", time())." v.$SOFTWARE_VERSION&nbsp; &nbsp; </font></td></tr>";
echo "</table>\n";

  // Save data back to database.
  page_close()
 ?>
</body></body>
</html>