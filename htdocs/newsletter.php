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

<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="blank" colspan=2 width=100%">&nbsp;</td></tr>
<tr>
	<td class="topic" colspan=2><b>&nbsp;Best&auml;tigung der Email-Adresse</b></td>
</tr>
<tr><td class="blank" colspan=2 width="100%">&nbsp;</td></tr>

<?

//	So, wer bis hier hin gekommen ist gehoert zur Zielgruppe...

	if (!isset($secret) || $secret == "" || !isset($username) || $username== "" || !isset($newsletter_id)) {   // Volltrottel (oder abuse)
		my_error("<b>Sie m&uuml;ssen den vollst&auml;ndigen Link aus der Best&auml;tigungsmail<br>\nin die Zeile \"Location\" oder \"URL\" Ihres Browsers kopieren.</b>\n");
		print "<tr><td class=\"blank\" colspan=2 width=\"100%\"><b>&nbsp;Versuchen Sie es noch einmal!</b><br><br>\n";
		print "</td></tr></table>";
		page_close();
		die;
	}

	$hash = md5("$username:$magic");
	// hier wird noch mal berechnet, welches secret in der Bestaetigungsmail uebergeben wurde

	if ($secret != $hash) {   // abuse (oder Volltrottel)
		my_error("<b>Der &uuml;bergebene \"Secret-Code\" ist nicht korrekt.</b>\n");
		my_info("Sie m&uuml;ssen unter dem Benutzernamen eingeloggt sein,<br>\nf&uuml;r den Sie die Best&auml;tigungsmail erhalten haben.\n");
		my_info("Und Sie m&uuml;ssen den vollst&auml;ndigen Link aus der Best&auml;tigungsmail<br>\nin die Zeile \"Location\" oder \"URL\" Ihres Browsers kopieren.\n");
		print "<tr><td class=\"blank\" colspan=2 width=\"100%\"><b>&nbsp;Versuchen Sie es noch einmal!</b><br><br>\n";
		print "</td></tr></table>";
    // Mail an abuse
		die;
	}

	if ($secret == $hash) {   // alles paletti, Status ändern
		$db = new DB_Seminar;
	   $query = "update auth_user_md5 set perms='autor' where user_id='$user->id'";
	   $db->query($query);
	   if ($db->affected_rows() == 0) {
	     my_error("<b>Changes failed:</b> $query");
	     break;
	   }

		my_msg("<b>Ihr Status wurde erfolgreich auf \"autor\" gesetzt.<br>\nDamit d&uuml;rfen Sie in den meisten Veranstaltungen schreiben,<br>\nf&uuml;r die Sie sich anmelden.</b>\n");

	} else {
		; // hier sollten wir nie hinkommen
	}

  page_close();
?>
</body>
</html>
<!-- $Id$ -->