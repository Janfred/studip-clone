<?php 
/*
logout.php - Ausloggen aus Stud.IP und aufräumen
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, André Noack <andre.noack@gmx.net>,
Cornelis Kater <ckater@gwdg.de>

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

ob_start();

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatShmServer.class.php";

if ($auth->auth["uid"]!="nobody") {   //nur wenn wir angemeldet sind sollten wir dies tun!

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	//User aus allen Chatraeumen entfernen
	if ($CHAT_ENABLE) {
		$chatServer=new ChatShmserver();
		$chatServer->logoutUser($user->id);
	}
	
	//Wenn Option dafuer gewaehlt, vorliegende SMS loeschen
	if ($my_messaging_settings["delete_messages_after_logout"]) {
		$db->query ("SELECT username FROM auth_user_md5 WHERE user_id = '".$user->id."' ");
		$db->next_record();

		$db2->query("DELETE FROM globalmessages WHERE user_id_rec = '".$db->f("username")."' AND mkdate <'".$my_messaging_settings["last_visit"]."' ");
	}
 
	$logout_user=$user->id;            
	//Logout aus dem Sessionmanagement
	$auth->logout();
	$sess->delete();
	//evtl verbleibende Session Variablen löschen
	foreach($sess->pt as $key => $value){
		$$key = null;
	}
	
	page_close();

	//Session changed zuruecksetzen
	$timeout=(time()-(15 * 60));
	$sqldate = date("YmdHis", $timeout);
	$query = "UPDATE active_sessions SET changed = '$sqldate' WHERE sid = '$logout_user'";
	$db->query($query); 
	
	header("Location:$PHP_SELF?logout"); //Seite neu aufrufen um eine nobody Session zu erzeugen

} else {        //wir sind nobody, also wahrscheinlich gerade ausgeloggt

	header("Pragma: no-cache");
	header("Expires: 0");
	
	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

	// -- here you have to put initialisations for the current page
	require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
	require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");

	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	?>
	<table width="80%" align="center" border=0 cellpadding=0 cellspacing=0>
		<tr><td class="topic" valign="absmiddle"><img src="pictures/logout.gif" border="0"><b>&nbsp;Stud.IP - Logout</b></td></tr>
		<tr><td class="blank">&nbsp;</td></tr>
		<?
			parse_msg ("info§Sie sind nun aus dem System abgemeldet", "§", "blank", 1)
		?>
		<tr><td class="blank"><font size=-1><a href="index.php"><b>&nbsp;Hier</b></a> geht es wieder zur Startseite.<br />
		<? if ($UNI_LOGOUT_ADD) {
			echo "<tr><td class=\"blank\"><font size=-1>&nbsp;$UNI_LOGOUT_ADD</font></td></tr><tr><td class=\"blank\">&nbsp;</td></tr>";
			}
		?>
		</font></td></tr>
	</table>
	</body>
</html>
<?
page_close();
}
ob_end_flush();
?>
<!-- $Id$ -->
