<?php
/*
header.php - Kopfzeile von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
//Daten fuer Onlinefunktion einbinden
if (!$perm->have_perm("user"))
	$my_messaging_settings["active_time"]=5;

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatShmServer.class.php";
require_once ($ABSOLUTE_PATH_STUDIP . "visual.inc.php");

//nur sinnvoll wenn chat eingeschaltet
if ($CHAT_ENABLE) {
	$chatServer=new ChatShmServer;
	$chatServer->caching = TRUE;
	echo "\t\t<script type=\"text/javascript\">\n";
	echo "\t\tfunction open_chat() {\n";
	if ($chatServer->isActiveUser($user->id,"studip"))
		echo "alert('Sie sind bereits im Chat angemeldet!');\n";
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
					&nbsp;<a href="index.php" target="_top"><img border="0" src="pictures/home.gif" <?=tooltip("zurück zur Startseite")?>></a>
					&nbsp;<a href="./help/index.php<?echo $help_query?>" target="_new"><img border="0" src="pictures/hilfe.gif" <?=tooltip("Hilfe")?> width="24" height="21"></a>
					&nbsp;<a href="freie.php"><img border="0" src="pictures/meinesem.gif" <?=tooltip("Freie Veranstaltungen")?> width="24" height="21"></a></td>
				<td class="angemeldet" width="20%" nowrap bgcolor="#C0C0C0" align="center" valign="middle" background="pictures/kaverl1b.jpg">
					<font color="#000080">Sie sind nicht angemeldet</font></td>
				<td class="header" width="33%" nowrap valign="bottom" align="right" background="pictures/fill1.gif">
					&nbsp;&nbsp;<a href="impressum.php"><img border="0" src="pictures/logo2.gif" <?=tooltip("Impressum")?>></a>
					&nbsp;&nbsp;<a href="index.php?again=yes"><img border="0" src="pictures/login.gif" <?=tooltip("Am System anmelden")?>></a>&nbsp;</td>
			</tr>
		</table>

<?php
  }
	else {   // Benutzer angemeldet

		$db=new DB_Seminar;

		// wer ist ausser mir online
		$now = time(); // nach eingestellter Zeit (default = 5 Minuten ohne Aktion) zaehlt man als offline
		$query = "SELECT CONCAT(Vorname,' ',Nachname) AS full_name,($now-UNIX_TIMESTAMP(changed)) AS lastaction,username,user_id FROM active_sessions LEFT JOIN auth_user_md5 ON user_id=sid WHERE changed > '".date("YmdHis",$now - ($my_messaging_settings["active_time"] * 60))."' AND sid != 'nobody' AND sid != '".$auth->auth["uid"]."' AND active_sessions.name = 'Seminar_User' ORDER BY changed DESC";
		$db->query($query);
		while ($db->next_record()){
      		$online[$db->f("username")] = array("name"=>$db->f("full_name"),"last_action"=>$db->f("lastaction"),"userid"=>$db->f("user_id"));      
      	}
		
		//Chatnachrichten zaehlen (wenn Sender Online)
		$myuname=$auth->auth["uname"];
		$db->query("SELECT *  FROM globalmessages WHERE user_id_rec LIKE '$myuname'");
		$i=0;
		$chatm=false;
		while ($db->next_record())
		{
			if (preg_match("/chat_with_me/i", $db->f("message")))  {
				if ($online[$db->f("user_id_snd")]) {
				    $chatm=true;
					}
			}
			elseif ($my_messaging_settings["last_visit"] < $db->f("mkdate"))
				$neum++; // das ist eine neue Nachricht.
			else
				$altm++;
		}

		//Nachrichten auf Wunsch anzeigen
		?>

		<table class="header" border="0" width="100%" background="pictures/fill1.gif" cellspacing="0" cellpadding="0" bordercolor="#999999" height="25">
			<tr>
				<td class="header" width="33%" valign="bottom" background="pictures/fill1.gif">
					&nbsp;<a href="index.php" target="_top"><img border="0" src="pictures/home.gif" <?=tooltip("zurück zur Startseite")?> width="24" height="21"></a>
					&nbsp;<a href="./help/index.php<?echo $help_query?>" target="_new"><img border="0" src="pictures/hilfe.gif" <?=tooltip("Hilfe")?> width="24" height="21"></a>
					&nbsp;<a href="meine_seminare.php"><img border="0" src="pictures/meinesem.gif" <?=tooltip("Meine Veranstaltungen & Einrichtungen")?> width="24" height="21"></a>
				<?
				if (!($perm->have_perm("admin") || $perm->have_perm("root"))) {
					echo "&nbsp;<a href=\"./calendar.php\">";
					echo "<img border=\"0\" src=\"pictures/meinetermine.gif\" ";
					echo tooltip("Meine Terminverwaltung") . "width=\"24\" height=\"21\"></a>";
				}
				?>
					&nbsp;&nbsp;&nbsp;


<?	if ((($altm) && (!$neum)) || ((($altm+$neum) >0) && ($i_page == "sms.php")))
		if ($altm > 1)
			echo "&nbsp;<a href=\"sms.php\" ><img border='0' src='pictures/nachricht1.gif' ".tooltip("Sie haben $altm alte Nachrichten!")."></a>&nbsp;&nbsp;&nbsp;";
		else
			echo "&nbsp;<a href=\"sms.php\" ><img border='0' src='pictures/nachricht1.gif' ".tooltip("Sie haben eine alte Nachricht!")."></a>&nbsp;&nbsp;&nbsp;";
	elseif (($neum) && ($i_page != "sms.php"))
		if ($neum > 1)
			echo "&nbsp;<a href=\"sms.php\" ><img border='0' src='pictures/nachricht2.gif' ".tooltip("Sie haben $neum neue Nachrichten!")."></a>&nbsp;&nbsp;&nbsp;";
		else
			echo "&nbsp;<a href=\"sms.php\" ><img border='0' src='pictures/nachricht2.gif' ".tooltip("Sie haben eine neue Nachricht!")."></a>&nbsp;&nbsp;&nbsp;";
	else
		echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

		// wurde ich zum Chat eingeladen? Wenn nicht, nachsehen ob wer im Chat ist
          //Version für neuen Chat (vorläufig)
  if ($CHAT_ENABLE) {

          if (($chatm) && ($i_page != "sms.php") && (!$chatServer->isActiveUser($user->id,"studip"))) {
			echo "<a href=\"javascript:open_chat();\"><img border='0' src='pictures/chateinladung.gif' ".tooltip("Sie wurden zum Chatten eingeladen!")."></a>\n";
		} else {

               $chatter=$chatServer->getActiveUsers("studip");
   		     if ($chatter == 1)
   		     		if ($chatServer->isActiveUser($user->id,"studip"))	
			       		printf ("<a href=\"javascript:open_chat();\"><img border='0' src='pictures/chat3.gif' ".tooltip("Nur Sie sind im Chat")."></a>");
			       	else
			       		printf ("<a href=\"javascript:open_chat();\"><img border='0' src='pictures/chat2.gif' ".tooltip("Es ist eine Person im Chat")."></a>");
			elseif ($chatter > 1)
			       print ("<a href=\"javascript:open_chat();\"><img border='0' src='pictures/chat2.gif' ".tooltip("Es sind $chatter Personen im Chat")."></a>");
               else
                    echo "<a href=\"javascript:open_chat();\"><img border='0' src='pictures/chat1.gif' ".tooltip("Es ist niemand im Chat")."></a>";
          }
     }
     else echo "&nbsp;&nbsp;";



?>

&nbsp;

<?	// Ist sonst noch wer da?
		if (!count($online)) print "<a href=\"online.php\"><img src='pictures/nutzer.gif' ".tooltip("Nur Sie sind online")." border='0'></a>";
		else {
			if (count($online)==1) print "<a href=\"online.php\"><img src='pictures/nutzeronline.gif' ".tooltip("Ausser Ihnen ist eine Person online")." border='0'></a>";
			else {
				?>
				<a href="online.php"><img src="pictures/nutzeronline.gif" <?=tooltip("Es sind ausser Ihnen ".count($online)." Personen online")?> border='0'></a>
				<?
			}
		}

?>
		</td>

		<td class="angemeldet" width="20%" nowrap bgcolor="#C0C0C0" valign="middle" align="center" background="pictures/kaverl1b.jpg">
			<font color="#000080">angemeldet als	<? printf ("%s", $auth->auth["uname"]);?>

			<img border="0" src="pictures/info.gif"
			<? //JavaScript Infofenster aufbauen
				ob_end_flush();
				ob_start();
				print "Sie sind angemeldet als ";
				printf ("%s", $auth->auth["uname"]);
				print " mit der Berechtigung ";
				printf ("%s.", $auth->auth["perm"]);
				print " Beginn der Session: ";
				print date ("d. M Y, H:i:s", $SessionStart);
				print ", Letztes Login: ";
				print date ("d. M Y, H:i:s", $LastLogin);
				if ($auth->auth["jscript"]) print " JavaScript eingeschaltet, ";
				if ($auth->auth["xres"]) print "Auflösung :".$auth->auth["xres"]."x".$auth->auth["yres"];
				$infotext=ob_get_contents();
				ob_end_clean();
				ob_start();	
				if ($auth->auth["jscript"])
					{
					echo " onClick=\"alert('$infotext');\" ";
					}
			echo tooltip($infotext);
			?>
			>
			</font>
		</td>

		<td class="header" width="33%" nowrap valign="bottom" align="right" background="pictures/fill1.gif">

<?
		IF ($perm->have_perm("autor"))
		{
			echo"&nbsp;<a href=\"about.php\"><img border='0' src='pictures/einst.gif' ".tooltip("Zu Ihrer Einstellungsseite")."></a>\n";
			echo"&nbsp;<a href=\"auswahl_suche.php\"><img border='0' src='pictures/suchen.gif' ".tooltip("Im System suchen")."></a>\n";
		}

		IF ($perm->have_perm("tutor"))
		{
			echo"&nbsp;<a href=\"adminarea_start.php?list=TRUE\"><img border='0' src='pictures/admin.gif' ".tooltip("Zu Ihrer Administrationsseite")."></a>\n";
		}
?>

			&nbsp;&nbsp;<a href="impressum.php"><img border="0" src="pictures/logo2.gif" <?=tooltip("Impressum")?>></a>
			&nbsp;&nbsp;<a href="logout.php"><img border="0" src="pictures/logout.gif" <?=tooltip("Aus dem System abmelden")?>></a>&nbsp;

		</td>
	</tr>
</table>
<?
	}

	echo"<body><br>\n";
	ob_end_flush();

	include "check_sem_entry.inc.php"; //hier wird der Zugang zum Seminar ueberprueft
?>
<!-- $Id$ -->
