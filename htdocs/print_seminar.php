<?php
/*
print_seminar.php - Druckanzeige eines laufenden Seminares
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

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
<?IF (!isset($SessSemName[1]) || $SessSemName[1] == "") {
    echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
    echo "</head></html>";
    die;
}
?>

	<title>Stud.IP</title>
	<link rel="stylesheet" href="style_print.css" type="text/css">
</head>
<body bgcolor=white>

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert
	require_once("archiv.inc.php");
	
print (dump_sem($SessSemName[1]));

echo "<table width=100% border=0 cellpadding=2 cellspacing=0>";
echo "<tr><td><i><font size=-1>Stand: ".date("d.m.y",time()).", ".date("G:i", time())." Uhr.</font></i></td><td align=\"right\"><font size=-2><img src=\"pictures/logo2b.gif\"><br />&copy; ".date("Y", time())." v.$SOFTWARE_VERSION&nbsp; &nbsp; </font></td></tr>";
echo "</table>\n";


// Save data back to database.
page_close()
?>
</body>
</html>