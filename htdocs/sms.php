<?php
/*
sms.php - Verwaltung von systeminternen Kurznachrichten
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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
$perm->check("user");
	
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/messagingSettings.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
	
$sess->register("sms_data");
$msging=new messaging;

$db=new DB_Seminar;
$db2=new DB_Seminar;


if ($mclose)
	$sms_data["open"]='';

if ($mopen)
	$sms_data["open"]=$mopen;
	
//alle Nachrichten loeschen
if ($cmd=="delete_all") { 
	$count=0;
	$count_deleted_sms=$msging->delete_all_sms ($user->id, $delete_unread);
	if ($count_deleted_sms)
		if ($count_deleted_sms==1)
			$msg="msg§Es wurde eine Nachricht gel&ouml;scht.";
		else
			$msg="msg§Es wurden ".$$count_deleted_sms." Nachrichten gel&ouml;scht.";
	else
		$msg="error§Es liegen keine Nachrichten vor, die gel&ouml;scht werden konnten.";
	}
	
//Nachricht loeschen
if ($cmd=="delete") {
	$l=0;; 
	if (is_array($delete_msg)) {
		foreach ($delete_msg as $a) {
			$count_deleted_sms=$msging->delete_sms ($a);
			$l=$i+$count_deleted_sms;
			}
	if ($l)
		if ($l==1)
			$msg="msg§Es wurde eine Nachricht gel&ouml;scht.";
		else
			$msg="msg§Es wurden $l Nachrichten gel&ouml;scht.";
		}
	else
		$msg="error§Es konnten keine Nachrichten gel&ouml;scht werden.";
	}
 
//Geschriebene Nachricht einfuegen
if ($cmd=="insert") {
	if ($send_all_buddies) {
		if ($my_buddies)
			foreach ($my_buddies as $a)
				$buddy_count+=$msging->insert_sms($a["username"], $message);
				
	if (!$my_buddies[$rec_uname])
		$count=$msging->insert_sms($rec_uname, $message);
		
	} else				
		$count=$msging->insert_sms($rec_uname, $message);
		
	if (($count > 0) || ($buddy_count >0)) {
		$msg="msg§";
		if ($count > 0)	
			$msg.="Ihre Nachricht an ".get_fullname_from_uname($rec_uname)." wurde verschickt! <br />";
		if ($buddy_count > 0)	
			$msg.="Die Nachricht wurde an alle $buddy_count Buddies verschickt!";
	}
	if ($count < 0)
		$msg="error§Ihre Nachricht konnte nicht gesendet werden, die Nachricht ist leer.";
	elseif ((!$count) && (!$buddy_count))
		$msg="error§Ihre Nachricht konnte nicht gesendet werden.";
		
	$sms_msg=rawurlencode ($msg);

	if ($sms_source_page) {
		header ("Location: $sms_source_page?username=$username&sms_msg=$sms_msg");
		die;
		}
	}

//Chateinladung absetzen
if ($cmd=="chatinsert") {
	$count=$msging->insert_chatinv ($rec_uname, $message);
	if ($count)
		$msg="msg§Ihre Einladung zum Chatten an ".get_fullname_from_uname($rec_uname)." wurde verschickt.";
	else
		$msg="error§Ihre Einladung zum Chatten an ".get_fullname_from_uname($rec_uname)." konnte nicht verschickt werden";

	$sms_msg=rawurlencode ($msg);

	if ($sms_source_page) {
		header ("Location: $sms_source_page?username=$username&sms_msg=$sms_msg");
		die;
	}
}
	
// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</tr></td></table>";
	page_close();
	die;
	}

//Neue Nachricht schreiben
if ($cmd=="write") {
	 ?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;Systeminterne Nachricht schreiben</b></td>
	</tr>
	<tr>
		<td class="blank">
		<blockquote>Schreiben Sie hier eine Nachricht an einen anderen Benutzer:
		<?
		if ($SessSemName[0] && $SessSemName["class"] == "inst")
			echo "<br /><br /><a href=\"institut_main.php\">Zur&uuml;ck zur ausgew&auml;hlten Einrichtung</a>";
		elseif ($SessSemName[0])
			echo "<br /><br /><a href=\"seminar_main.php\">Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung</a>";
		?>
		</blockquote>
		</td>
		<td class="blank" align = right><img src="pictures/brief.jpg" border="0"></td>
	</tr>
	<tr>
		<td class="blank" colspan="2">
	<?
	$db->query ("SELECT Vorname, Nachname FROM auth_user_md5 WHERE username = '$rec_uname' ");
	$db->next_record();

	if ($quote) {
		$db2->query ("SELECT message FROM globalmessages WHERE message_id = '$quote' ");
		$db2->next_record();
		if (strpos($db2->f("message"),$msging->sig_string))
			$tmp_sms_content=substr($db2->f("message"), 0, strpos($db2->f("message"),$msging->sig_string));
		else
			$tmp_sms_content=$db2->f("message");
		}
	
	$icon="&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
	$titel="</b>Nachricht schreiben an: <a href=\"about.php?username=$rec_uname\"><font size=-1 color=\"#333399\">".$db->f("Vorname")." ".$db->f("Nachname")."</font></a><b>";				
	$content="<textarea  name=\"message\" style=\"width: 90%\" cols=80 rows=4 wrap=\"virtual\">";
	if ($quote)
		$content.=quotes_encode($tmp_sms_content, $db->f("Vorname")." ".$db->f("Nachname"));
	$content.="</textarea><br />\n";
	if ($my_buddies)
		$content.="<font size=-1><input type=\"CHECKBOX\" name=\"send_all_buddies\" />&nbsp; Diese Nachricht an alle meine Buddies versenden</font><br />\n";
	$edit="<input type=\"IMAGE\" src=\"pictures/buttons/abschicken-button.gif\" border=0>";
	$edit.="&nbsp; <a href=\"$PHP_SELF\"><img src=\"pictures/buttons/abbrechen-button.gif\" border=0></a>";	
	
	echo"<form action=\"$PHP_SELF\" method=\"POST\">\n";
	echo"<input type=\"HIDDEN\" name=\"rec_uname\" value=\"$rec_uname\">\n";
	echo"<input type=\"HIDDEN\" name=\"cmd\" value=\"insert\">\n";
	echo"<input type=\"HIDDEN\" name=\"username\" value=\"$username\">\n";
	echo"<input type=\"HIDDEN\" name=\"sms_source_page\" value=\"$sms_source_page\">\n";
			
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
	printhead(0, 0, $link, TRUE,TRUE, $icon, $titel, $zusatz);
	echo "</tr></table>	";

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
	printcontent("99%",0, $content, $edit);
	echo "</tr></table>	";
	
	echo"</form>\n";
	}

//Ausgabe von vorhandenen Nachrichten
else {
	$db->query("SELECT user_id_snd, user_id_rec, mkdate, message, message_id, Vorname, Nachname  FROM globalmessages LEFT JOIN auth_user_md5 ON (username = user_id_snd) WHERE globalmessages.user_id_rec LIKE '".get_username($user->id)."' ORDER BY globalmessages.mkdate DESC");

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic"><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;Systeminterne Nachrichten anzeigen</b></td>
	<td nowrap class="topic" align="right">Einstellungen &auml;ndern&nbsp; <a href="<? echo $PHP_SELF ?>?change_view=TRUE"><img src="pictures/pfeillink.gif" border=0></a>
	</td>
	
</tr>
<?
if ($msg)	{
	echo"<td class=\"blank\"colspan=2><br>";
	parse_msg ($msg);
	echo"</td></tr>";
	}
?>
<tr>
	<td class="blank">
	<blockquote><br />Sie sehen hier alle systeminternen Nachrichten (SMS), die an Sie verschickt wurden.<br />
	Alle Nachrichten seit dem letzten Login erscheinen aufgeklappt.
	<?
	if ($SessSemName[0] && $SessSemName["class"] == "inst")
		echo "<br /><br /><a href=\"institut_main.php\">Zur&uuml;ck zur ausgew&auml;hlten Einrichtung</a>";
	elseif ($SessSemName[0])
		echo "<br /><br /><a href=\"seminar_main.php\">Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung</a>";

	if ($db->affected_rows())
		{?><form action="<? echo $PHP_SELF ?>"> <input type="hidden" name="cmd" value="delete_all" /> <input type="IMAGE"  align ="absmiddle" src="pictures/buttons/alleloeschen-button.gif" border=0 />&nbsp; <br /><br /><input type="CHECKBOX" name="delete_unread" checked /><font size=-1>Nachrichten seit letztem Login nicht l&ouml;schen</font></form><?}
	?>
	</blockquote>
	</td>
	<td class="blank" align = right><img src="pictures/brief.jpg" border="0">
	</td>
</tr>
<tr>
	<td class="blank" colspan=2>
	<?
	$n=0;
	while ($db->next_record()) {
		//Chateinladung
		if (ereg(("chat_with_me"), $db->f("message")))  {
			if (count($online)) {
				reset($online);
					while (list($index)=each($online)) {
						list(,,,$temp_sms_uname)=$online[$index];
						if ($db->f("user_id_snd")==$temp_sms_uname) { //Nachricht liegt vor und kann angezegt werden (Absender ist im Chat)
							$open="open";
							$neu=TRUE;
							$icon="&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
							$zusatz="<font size=-1>gesendet von </font><a href=\"about.php?username=".$db->f("user_id_snd")."\"><font size=-1 color=\"#333399\">".$db->f("Vorname")." ".$db->f("Nachname")."</font></a><font size=-1> am ".date("d.m.Y, H:i",$db->f("mkdate"))."<font size=-1>&nbsp;"."</font>";				
							$titel="Einladung zum Chat";
							$content=$db->f("Vorname")." ".$db->f("Nachname")." hat Sie am ".date("d.m.Y",$db->f("mkdate"))." um ".date("H:i",$db->f("mkdate"))." Uhr in den Chat eingeladen.\n";
							$content.="Wenn Sie mit ihm Chatten wollen, betreten Sie den Chat über das Symbol in der Kopfzeile.";

							echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
							printhead(0, 0, $link, $open, $neu, $icon, htmlReady($titel), $zusatz);
							echo "</tr></table>	";
			
							echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
							printcontent("99%",0, $content, $edit);
							echo "</tr></table>	";
							
							$n++;
							
							// nach der Anzeige wird die Message geloescht
							$db2->query("DELETE FROM globalmessages WHERE message_id LIKE '".$db->f("message_id")."' ");
							}
 						}
			}
		}
		//Keine Chateinladung
		else {
			//Kopfzeile erstellen
			$icon="&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
			if ($db->f("user_id_snd") == "____%system%____")
				$zusatz="<font size=-1>automatische Systemnachricht, gesendet";
			else
				$zusatz="<font size=-1>gesendet von </font><a href=\"about.php?username=".$db->f("user_id_snd")."\"><font size=-1 color=\"#333399\">".$db->f("Vorname")." ".$db->f("Nachname")."</font></a><font size=-1>";
			$zusatz.=" am ".date("d.m.Y, H:i",$db->f("mkdate"))."<font size=-1>&nbsp;"."</font>";
			if (strpos($db->f("message"),$msging->sig_string))
				$titel=mila(kill_format(substr($db->f("message"), 0, strpos($db->f("message"),$msging->sig_string))));
			else
				$titel=mila(kill_format($db->f("message")));
			
			$content=quotes_decode(formatReady($db->f("message")));

			$edit='';
			if ($db->f("user_id_snd") != "____%system%____") {
				$edit="<a href=\"$PHP_SELF?cmd=write&rec_uname=".$db->f("user_id_snd")."\"><img src=\"pictures/buttons/antworten-button.gif\" border=0></a>";
				$edit.="&nbsp;<a href=\"$PHP_SELF?cmd=write&quote=".$db->f("message_id")."&rec_uname=".$db->f("user_id_snd")."\"><img src=\"pictures/buttons/zitieren-button.gif\" border=0></a>";
			}
			$edit.="&nbsp;<a href=\"$PHP_SELF?cmd=delete&delete_msg[1]=".$db->f("message_id")."\"><img src=\"pictures/buttons/loeschen-button.gif\" border=0></a>";

			//Alle seit letztem Login (=aufgeklappt)
			if ($db->f("mkdate") > $LastLogin) 
				$open="open";
			elseif ($sms_data["open"]==$db->f("message_id"))
				$open="open";
			else
				$open="close";

			//Alle neue seit letztem Betreten (=roter Pfeil)
			if ($db->f("mkdate") < $my_messaging_settings["last_visit"])
				$red=FALSE;
			else {
				$red=TRUE;
				$open="open";
			}
				
		
			if (!$red) {
				if ($sms_data["open"]==$db->f("message_id")) {
					$link=$PHP_SELF."?mclose=TRUE";
					}
				else {
					$link=$PHP_SELF."?mopen=".$db->f("message_id");
					}
				}
		
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
			printhead(0, 0, $link, $open, $red, $icon, htmlReady($titel), $zusatz);
			echo "</tr></table>	";
			
			if (($open=="open") || ($sms_data["open"]==$db->f("message_id"))) {
				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
				printcontent("99%",0, $content, $edit);
				echo "</tr></table>	";		
				}
			
			$n++;
			}
		}
	if (!$n) {
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\">";
		$srch_result="info§<font size=-1><b>Im Augenblick liegen keine systeminternen Nachrichten f&uuml;r Sie vor. ";
		parse_msg ($srch_result, "§", "steel1", 2, FALSE);
		echo "</td></tr></table><br />";
		}
		
	//letzte Besuchszeit ablegen
	$my_messaging_settings["last_visit"]=time();
	}

?>
</tr></table></td></tr></table>
<?

// Save data back to database.
page_close()
	?>
</body>
</html>
