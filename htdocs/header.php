<?php
/**
* header
* 
* head line of Stud.IP
* 
*
* @author		Stefan Suchi <suchi@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	visual
* @module		header.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// header.php
// head line of Stud.IP
// Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

ob_start();
//Daten fuer Onlinefunktion einbinden
if (!$perm->have_perm("user"))
	$my_messaging_settings["active_time"]=5;

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatShmServer.class.php";
require_once ($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP . "functions.php");

function MakeToolbar($icon,$URL,$text,$tooltip,$size,$target="_top",$align="center",$toolwindow="FALSE")
{
	if ($toolwindow == "FALSE") {
		$tool = tooltip($tooltip);
	} else {
		$tool = tooltip($tooltip,TRUE,TRUE);
	}
	$toolbar = "<td class=\"toolbar\" align=\"$align\">";

	$toolbar .= "<img border=\"0\" src=\"pictures/blank.gif\" height=\"1\" width=\"30\"><br>"
			  ."<a class=\"toolbar\" href=\"$URL\" target=\"$target\"><img border=\"0\" src=\"$icon\" ".$tool."><br>"
			  ."<img border=\"0\" src=\"pictures/blank.gif\" height=\"4\" width=\"30\"><br>"
			  ."<b>$text</b></a><br>"
			  ."<img border=\"0\" src=\"pictures/blank.gif\" height=\"7\" width=\"30\">";
	$toolbar .= "</td>\n";
	return $toolbar;
}

//nur sinnvoll wenn chat eingeschaltet
if ($CHAT_ENABLE) {
	$chatServer=new ChatShmServer;
	$chatServer->caching = TRUE;
	echo "\t\t<script type=\"text/javascript\">\n";
	echo "\t\tfunction open_chat() {\n";
	if ($chatServer->isActiveUser($user->id,"studip"))
		printf ("alert('%s');\n", _("Sie sind bereits im Chat angemeldet!"));
	else
		echo "\t\t\tfenster=window.open(\"$RELATIVE_PATH_CHAT/chat_login.php?chatid=studip\",\"chat_studip_".$auth->auth["uid"]."\",\"scrollbars=no,width=640,height=480,resizable=yes\");\n";
	echo "\t\t}\n";
	echo "\t\t</script>\n";
}

// Initialisierung der Hilfe
$help_query = "?referrer_page=" . $i_page;
if (isset($i_query[0]) && $i_query[0] != "") {
	for ($i = 0; $i < count($i_query); $i++) { // alle Parameter durchwandern
		$help_query .= '&';
		$help_query .= $i_query[$i];
	}
}

if ($auth->auth["uid"] == "nobody") { ?>

		<table class="header" border="0" width="100%" background="pictures/fill1.gif" cellspacing="0" cellpadding="0" bordercolor="#999999" height="25">
			<tr>
				<td class="header" width="33%" valign="bottom" align="left" background="pictures/fill1.gif">
					&nbsp;<a href="index.php" target="_top"><img border="0" src="pictures/home.gif" <?=tooltip(_("Zurück zur Startseite"))?>></a>
					&nbsp;<a href="./help/index.php<?echo $help_query?>" target="_new"><img border="0" src="pictures/hilfe.gif" <?=tooltip(_("Hilfe"))?> width="24" height="21"></a>
					&nbsp;<a href="freie.php"><img border="0" src="pictures/meinesem.gif" <?=tooltip(_("Freie Veranstaltungen"))?> width="24" height="21"></a></td>
				<td class="angemeldet" width="20%" nowrap bgcolor="#C0C0C0" align="center" valign="middle" background="pictures/kaverl1b.jpg">
					<font color="#000080"><? echo _("Sie sind nicht angemeldet") ?></font></td>
				<td class="header" width="33%" nowrap valign="bottom" align="right" background="pictures/fill1.gif">
					&nbsp;&nbsp;<a href="impressum.php"><img border="0" src="pictures/logo2.gif" <?=tooltip(_("Impressum"))?>></a>
					&nbsp;&nbsp;<a href="index.php?again=yes"><img border="0" src="pictures/login.gif" <?=tooltip(_("Am System anmelden"))?>></a>&nbsp;</td>
			</tr>
		</table>

<?php
} else {   // Benutzer angemeldet

		$db=new DB_Seminar;

		// wer ist ausser mir online
		$now = time(); // nach eingestellter Zeit (default = 5 Minuten ohne Aktion) zaehlt man als offline
		$query = "SELECT " . $_fullname_sql['full'] . " AS full_name,($now-UNIX_TIMESTAMP(changed)) AS lastaction,a.username,a.user_id FROM active_sessions LEFT JOIN auth_user_md5 a ON (a.user_id=sid) LEFT JOIN user_info USING(user_id) WHERE changed > '".date("YmdHis",$now - ($my_messaging_settings["active_time"] * 60))."' AND sid != 'nobody' AND sid != '".$auth->auth["uid"]."' AND active_sessions.name = 'Seminar_User' ORDER BY changed DESC";
		$db->query($query);
		while ($db->next_record()) {
			$online[$db->f("username")] = array("name"=>$db->f("full_name"),"last_action"=>$db->f("lastaction"),"userid"=>$db->f("user_id"));      
		}
		
		//Chatnachrichten zaehlen (wenn Sender Online)
		$myuname=$auth->auth["uname"];
		$db->query("SELECT *  FROM globalmessages WHERE user_id_rec LIKE '$myuname'");
		$i=0;
		$chatm=false;
		while ($db->next_record()) {
			if (preg_match("/chat_with_me/i", $db->f("message"))) {
				if ($online[$db->f("user_id_snd")]) {
					$chatm=true;
				}
			}
			elseif ($my_messaging_settings["last_visit"] < $db->f("mkdate"))
				$neum++; // das ist eine neue Nachricht.
			else
				$altm++;
		}


		?>
		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
		<td width="40%">
			<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
			<tr>
<?
				echo MakeToolbar("pictures/home.gif","index.php",_("Start"),_("Zurück zur Startseite"),40,"_top");
				echo MakeToolbar("pictures/meinesem.gif","meine_seminare.php",_("Veranstaltungen"),_("Meine Veranstaltungen & Einrichtungen"),40, "_top","left");


				if (!($perm->have_perm("admin") || $perm->have_perm("root"))) {
					echo MakeToolbar("pictures/meinetermine.gif","./calendar.php",_("Planer"),_("Meine Termine und Kontakte"),40, "_top");
				}
//Nachrichten anzeigen

	if ((($altm) && (!$neum)) || ((($altm+$neum) >0) && ($i_page == "sms.php"))) {
		$icon = "pictures/nachricht1.gif";
		$text = _("Post");
		if ($altm > 1) {
			$tip = sprintf(_("Sie haben %s alte Nachrichten!"), $altm);
		} else {
			$tip = _("Sie haben eine alte Nachricht!");
		}
	} elseif (($neum) && ($i_page != "sms.php")) {
		$icon = "pictures/nachricht2.gif";		
		$text = "Nachrichten";
		if ($neum > 1) {
			$tip = sprintf(_("Sie haben %s neue Nachrichten!"), $neum);
		} else {
			$tip = _("Sie haben eine neue Nachricht!");
		}
	} else {
		$icon = "pictures/blank.gif";
		$tip = "";
		$text = "";
	}
	
		echo MakeToolbar($icon,"sms.php",$text,$tip,40, "_top");

		// wurde ich zum Chat eingeladen? Wenn nicht, nachsehen ob wer im Chat ist
          //Version für neuen Chat (vorläufig)
  	if ($CHAT_ENABLE) {
    	if (($chatm) && ($i_page != "sms.php") && (!$chatServer->isActiveUser($user->id,"studip"))) {
				echo MakeToolbar("pictures/chateinladung.gif","javascript:open_chat();",_("Chat"),_("Sie wurden zum Chatten eingeladen!"),40,"_top");
			} else {
      	$chatter=$chatServer->getActiveUsers("studip");
   			if ($chatter == 1)
   		  	if ($chatServer->isActiveUser($user->id,"studip"))	
						echo MakeToolbar("pictures/chat3.gif","javascript:open_chat();",_("Chat"),_("Sie sind alleine im Chat"),40,"_top");
					else
						echo MakeToolbar("pictures/chat2.gif","javascript:open_chat();",_("Chat"),_("Es ist eine Person im Chat"),40,"_top");
				elseif ($chatter > 1)
					echo MakeToolbar("pictures/chat2.gif","javascript:open_chat();",_("Chat"),sprintf(_("Es sind %s Personen im Chat"), $chatter),40,"_top");
      	else
					echo MakeToolbar("pictures/chat1.gif","javascript:open_chat();",_("Chat"),_("Es ist niemand im Chat"),40,"_top");
			}
		} else {
//			echo MakeToolbar("pictures/blank.gif","","","",40,"_top");
		}

		// Ist sonst noch wer da?
		if (!count($online))
			echo MakeToolbar("pictures/nutzer.gif","online.php",_("Online"),_("Nur Sie sind online"),40, "_top");
		else {
			if (count($online)==1) {
				echo MakeToolbar("pictures/nutzeronline.gif","online.php",_("Online"),_("Außer Ihnen ist eine Person online"),40, "_top");
			} else {
				echo MakeToolbar("pictures/nutzeronline.gif","online.php",_("Online"),sprintf(_("Es sind außer Ihnen %s Personen online"), count($online)),40, "_top");
			}
		}

?>
	</tr>
	</table>
	</td>
	<td width="20%" align="center">
		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr align="center">
<? //create (javascript) info tooltip/window
				$infotext = sprintf (_("Sie sind angemeldet als: %s mit der Berechtigung: %s. Beginn der Session: %s,  letztes Login: %s, %s,  Auflösung: %sx%s, eingestellte Sprache: %s"),
								$auth->auth["uname"], $auth->auth["perm"], date ("d. M Y, H:i:s", $SessionStart), date ("d. M Y, H:i:s", $LastLogin),
								($auth->auth["jscript"]) ? _("JavaScript eingeschaltet") : _("JavaScript ausgeschaltet"), $auth->auth["xres"], $auth->auth["yres"], $INSTALLED_LANGUAGES[$_language]["name"]);
				echo MakeToolbar("pictures/logo2.gif","Impressum.php",_("Impressum"),_("Informationen zu dieser Installation"), "_top");
?>
	</tr>
	</table>
	</td>
	<td width="40%" align="right">
		<table class="toolbar" border="0" width="100%" cellspacing="0" cellpadding="0">
		<tr>
<?

		if ($perm->have_perm("autor")) {
			echo MakeToolbar("pictures/einst.gif","about.php",_("Homepage"),_("Zu Ihrer Einstellungsseite"),40, "_top","right");
			echo MakeToolbar("pictures/suchen.gif","auswahl_suche.php",_("Suche"),_("Im System suchen"),40, "_top");
		}

		if ($perm->have_perm("tutor")) {
			echo MakeToolbar("pictures/admin.gif","adminarea_start.php?list=TRUE",_("Admin"),_("Zu Ihrer Administrationsseite"),40, "_top");
		}
		echo MakeToolbar("pictures/info.gif","",$auth->auth["uname"],$infotext,40, "","left","TRUE");
		echo MakeToolbar("pictures/hilfe.gif","./help/index.php$help_query",_("Hilfestellung"),_("Hilfe zu dieser Seite"),40, "_new","right");
		echo MakeToolbar("pictures/logout.gif","logout.php",_("Logout"),_("Aus dem System abmelden"),40, "_top");

?>
	</tr>
</table>
</td>
</tr>
</table>

<?

	}
	
	if ($auth->auth["uid"] == "nobody") { 
		echo "<br><br>";
	}
	echo"<body>\n";
	ob_end_flush();

	include "check_sem_entry.inc.php"; //hier wird der Zugang zum Seminar ueberprueft
	include "tracking.inc.php"; //teomporaer. hier wird der User getrackt. 
?>
<!-- $Id$ -->