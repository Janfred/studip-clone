<?php 
/*
register2.php - Benutzerregistrierung in Stud.IP, Part II
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Oliver Brakel <obrakel@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Register_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

if ($auth->auth["uid"] == "nobody") {
	$auth->logout();
	header("Location: register2.php");
	page_close();
	die;
}

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

?>
<table width ="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="topic"><b>&nbsp;Herzlich Willkommen</b>
	</td>
</tr>

<tr>
	<td class="blank">&nbsp;
		<blockquote>
		Ihre Registrierung wurde erfolgreich vorgenommen.<br><br>
		Das System wird Ihnen zur Best&auml;tigung eine Email zusenden.<br>
		Bitte rufen Sie die Email ab und folgen Sie den Anweisungen, um Schreibrechte im System zu bekommen.<br>
		<br>
		<a href="index.php">Hier</a> geht es wieder zur Startseite.<br>
		<br>
		</blockquote>
	</td>
</tr>	
</table>

<?php page_close() ?>
</body>
</html>
<!-- $Id$ -->
