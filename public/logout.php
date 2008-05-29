<?php
# Lifter002: 
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

require_once ('lib/messaging.inc.php');
if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT."/ChatServer.class.php"; //wird für Nachrichten im chat benötigt
}
if ($auth->auth["uid"]!="nobody") {   //nur wenn wir angemeldet sind sollten wir dies tun!
	
	$sms = new messaging();
	//User aus allen Chatraeumen entfernen
	if ($CHAT_ENABLE) {
		$chatServer =& ChatServer::GetInstance($CHAT_SERVER_NAME);
		$chatServer->logoutUser($user->id);
	}
	
	//Wenn Option dafuer gewaehlt, vorliegende Nachrichen loeschen
	if ($my_messaging_settings["delete_messages_after_logout"]) {
		$sms->delete_all_messages();
	}
	
	//Wenn Option dafuer gewaehlt, alle ungelsesenen Nachrichten als gelesen speichern
	if ($my_messaging_settings["logout_markreaded"]) {
		$sms->set_read_all_messages();
	}
	
	$logout_user=$user->id;
	$logout_language = $_language;
	
	// TODO this needs to be generalized or removed
	//erweiterung cas
	if ($auth->auth["auth_plugin"] == "cas"){
		$casauth = StudipAuthAbstract::GetInstance('cas');			
		$docaslogout = true;
	}
	//Logout aus dem Sessionmanagement
	$auth->logout();
	$sess->delete();
	
	page_close();
	
	//Session changed zuruecksetzen
	$timeout=(time()-(15 * 60));
	$user->set_last_action($timeout);
	
	//der logout() Aufruf fuer CAS (dadurch wird das Cookie (Ticket) im Browser zerstoert) 
	if ($docaslogout){
		$casauth->logout();
	}
	
	header("Location:$PHP_SELF?_language=$logout_language"); //Seite neu aufrufen um eine nobody Session zu erzeugen
	
} else {        //wir sind nobody, also wahrscheinlich gerade ausgeloggt

	include ('lib/seminar_open.php'); // initialise Stud.IP-Session

	// -- here you have to put initialisations for the current page
	include('config.inc.php');
	require_once('lib/msg.inc.php');

	$HELP_KEYWORD="Basis.Logout";
	$CURRENT_PAGE = _("Logout");
	// Start of Output
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head

	?>
	<table width="800" align="center" border="0" cellpadding="0" cellspacing="0">
		<tr><td colspan="2" class="topic" valign="middle"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/logout.gif" border="0" align="texttop"><b>&nbsp;<? print _("Stud.IP - Logout");?></b></td></tr>
		<tr>
			<td width="99%"  class="blank" valign="middle">
				<table class="blank" width="100%" border="0" cellpadding="0" cellspacing="0">
	<?
					$msg= _("Sie sind nun aus dem System abgemeldet");
					parse_msg ("info§$msg","§","blank", 1);

	?>
					<tr>
					<td class="blank"><blockquote><font size=-1><a href="index.php"><b>&nbsp;<? print _("Hier</b></a> geht es wieder zur Startseite.");?>
					</font></blockquote></td></tr>

				<? if ($UNI_LOGOUT_ADD) {
					echo "<tr><td class=\"blank\"><blockquote><font size=-1>&nbsp;$UNI_LOGOUT_ADD</font></blockquote></td></tr>";
					}
				?>
				</table>
			</td>
			<td class="blank">
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/maus.jpg" align="top"  border="0">
			</td>
		</tr>
	</table>
<?
include ('lib/include/html_end.inc.php');
page_close();
}
ob_end_flush();

// <!-- $Id$ -->
?>
