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
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php"; 
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$admin_chats = $chatServer->getAdminChats($auth->auth['uid']);
}
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
	$count_deleted_sms = $msging->delete_all_sms ($user->id, ($delete_unread) ? $GLOBALS['LastLogin'] : false);
	if ($count_deleted_sms)
		if ($count_deleted_sms==1)
			$msg="msg§" . _("Es wurde eine Nachricht gel&ouml;scht.");
		else
			$msg="msg§" . sprintf(_("Es wurden %s Nachrichten gel&ouml;scht."), $count_deleted_sms);
	else
		$msg="error§" . _("Es liegen keine Nachrichten zum L&ouml;schen vor.");
	}
	
//Nachricht loeschen
if ($cmd=="delete") {
	$l=0;
	if (is_array($delete_msg)) {
		foreach ($delete_msg as $a) {
			$count_deleted_sms=$msging->delete_sms ($a);
			$l=$i+$count_deleted_sms;
			}
	if ($l)
		if ($l==1)
			$msg="msg§" . _("Es wurde eine Nachricht gel&ouml;scht.");
		else
			$msg="msg§" . sprintf(_("Es wurden %s Nachrichten gel&ouml;scht."), $l);
		}
	else
		$msg="error§" . _("Es konnten keine Nachrichten gel&ouml;scht werden.");
	}
 
//Geschriebene Nachricht einfuegen
if ($cmd=="insert") {
	if ($send_all_buddies) {
		$buddy_count+=$msging->circular_sms($message, "buddy");
				
		if (!CheckBuddy($rec_uname))
			$count=$msging->insert_sms($rec_uname, $message);
	} elseif($group_id) {
		$group_count+=$msging->circular_sms($message, "group", $group_id);
	} else
		$count=$msging->insert_sms($rec_uname, $message);
	
	if (($count) || ($buddy_count) || ($group_count)) {
		$msg="msg§";
		if ($count > 0)	
			$msg.= sprintf(_("Ihre Nachricht an %s wurde verschickt!"), get_fullname_from_uname($rec_uname)) . "<br />";
		if ($buddy_count > 0)	
			$msg.= sprintf(_("Die Nachricht wurde an alle %s Buddies verschickt!"), $buddy_count);
		if ($group_count > 0)	
			$msg.= sprintf(_("Die Nachricht wurde an %s Gruppenmitglieder verschickt!"), $group_count);
	}
	if ($count < 0)
		$msg="error§" . _("Ihre Nachricht konnte nicht gesendet werden. Die Nachricht enth&auml;lt keinen Text.");
	elseif ((!$count) && (!$buddy_count) && (!$group_count))
		$msg="error§" . _("Ihre Nachricht konnte nicht gesendet werden.");
		
	$sms_msg=rawurlencode ($msg);

	if ($sms_source_page) {
		header ("Location: $sms_source_page?username=$username&sms_msg=$sms_msg");
		die;
		}
	}

//Chateinladung absetzen
if ($cmd == "chatinsert" && $GLOBALS['CHAT_ENABLE']) {
	if ($admin_chats[$_REQUEST['chat_id']]){
		if ($send_all_buddies) {
			$buddy_count = $msging->buddy_chatinv($message,$_REQUEST['chat_id']);
			if (!CheckBuddy($rec_uname)){
				$count = $msging->insert_chatinv ($rec_uname, $_REQUEST['chat_id'],$message);
			}
		} else {
			$count = $msging->insert_chatinv ($rec_uname, $_REQUEST['chat_id'],$message);
		}
		if ($count)
			$msg="msg§" . sprintf(_("Ihre Einladung zum Chatten an %s in den Chatraum %s wurde verschickt."), htmlReady(get_fullname_from_uname($rec_uname)),htmlReady($admin_chats[$_REQUEST['chat_id']])). "§";
		elseif (!$send_all_buddies)
			$msg="error§" . sprintf(_("Ihre Einladung zum Chatten an %s konnte nicht verschickt werden"), htmlReady(get_fullname_from_uname($rec_uname))) ."§";
		if ($buddy_count){
			$msg .=  "msg§" . sprintf(_("Ihre Einladung zum Chatten wurde an %s Buddies verschickt!"), $buddy_count) ."§";
		} elseif ($send_all_buddies) {
			$msg .= "error§" . _("Ihre Einladung zum Chatten konnte nicht an Ihre Buddies verschickt werden!") ."§";
		}
	} else {
		$msg="error§" . sprintf(_("Ihre Einladung zum Chatten an %s konnte nicht verschickt werden"), htmlReady(get_fullname_from_uname($rec_uname)));
	}
	
	$sms_msg = rawurlencode ($msg);
	
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
	echo "</td></tr></table>";
	page_close();
	die;
	}

//Neue Nachricht schreiben
if ($cmd=="write") {
	 ?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Systeminterne Nachricht schreiben")?></b></td>
	</tr>
	<tr>
		<td class="blank">
		<blockquote>
		<?
		echo _("Schreiben Sie hier eine Nachricht an eine(n) anderen Benutzer(in):");
		if ($SessSemName[0] && $SessSemName["class"] == "inst")
			echo "<br /><br /><a href=\"institut_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung") . "</a>";
		elseif ($SessSemName[0])
			echo "<br /><br /><a href=\"seminar_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung") . "</a>";
		?>
		</blockquote>
		</td>
		<td class="blank" align = right><img src="pictures/brief.jpg" border="0"></td>
	</tr>
	<tr>
		<td class="blank" colspan="2">
	<?
	$fullname = get_fullname_from_uname($rec_uname);

	if ($quote) {
		$db2->query ("SELECT message FROM globalmessages WHERE message_id = '$quote' ");
		$db2->next_record();
		if (strpos($db2->f("message"),$msging->sig_string))
			$tmp_sms_content=substr($db2->f("message"), 0, strpos($db2->f("message"),$msging->sig_string));
		else
			$tmp_sms_content=$db2->f("message");
		}
	
	$icon="&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
	$titel="</b>" . _("Nachricht schreiben an") . " ";
	if ($group_id)
		$titel.= sprintf(_("alle Mitglieder der Gruppe: %s (%s Person(en))"), htmlReady(GetStatusgruppeName($group_id)), CountMembersPerStatusgruppe($group_id));
	else
		$titel.="<a href=\"about.php?username=$rec_uname\"><font size=-1 color=\"#333399\">".$fullname."</font></a><b>";				
	$content="<textarea  name=\"message\" style=\"width: 90%\" cols=80 rows=4 wrap=\"virtual\">";
	if ($quote)
		$content.=quotes_encode($tmp_sms_content, $fullname);
	$content.="</textarea><br />\n";
	if ((GetNumberOfBuddies()) && (!$group_id))
		$content.="<font size=-1><input type=\"CHECKBOX\" name=\"send_all_buddies\" />&nbsp; " . _("Diese Nachricht (auch) an alle meine Buddies versenden") . "</font><br />\n";
	$edit="<input type=\"IMAGE\" " . makeButton("abschicken", "src") . " border=0 align=\"absmiddle\">";
	$edit.="&nbsp; <a href=\"$PHP_SELF\">" . makeButton("abbrechen", "img") . "</a><br>&nbsp;";	
	
	echo"<form action=\"$PHP_SELF\" method=\"POST\">\n";
	echo"<input type=\"HIDDEN\" name=\"rec_uname\" value=\"$rec_uname\">\n";
	echo"<input type=\"HIDDEN\" name=\"cmd\" value=\"insert\">\n";
	echo"<input type=\"HIDDEN\" name=\"username\" value=\"$username\">\n";
	echo"<input type=\"HIDDEN\" name=\"sms_source_page\" value=\"$sms_source_page\">\n";
	if ($group_id)
		echo"<input type=\"HIDDEN\" name=\"group_id\" value=\"$group_id\">\n";

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
	printhead(0, 0, $link, TRUE,TRUE, $icon, $titel, $zusatz);
	echo "</tr></table>	";

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
	printcontent("99%",0, $content, $edit);
	echo "</tr></table>";
	
	echo"</form>\n";
}
//chateinladung schreiben
elseif ($cmd == "write_chatinv" && $GLOBALS['CHAT_ENABLE']) {
	 ?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/chat1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Chateinladung verschicken")?></b></td>
	</tr>
	<tr>
		<td class="blank">
		<blockquote>
		<?
		echo _("Schreiben Sie hier eine Chateinladung an eine(n) anderen Benutzer(in), der Nachrichtentext ist optional:");
		if ($SessSemName[0] && $SessSemName["class"] == "inst")
			echo "<br /><br /><a href=\"institut_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung") . "</a>";
		elseif ($SessSemName[0])
			echo "<br /><br /><a href=\"seminar_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung") . "</a>";
		?>
		</blockquote>
		</td>
		<td class="blank" align = right><img src="pictures/brief.jpg" border="0"></td>
	</tr>
	<tr>
		<td class="blank" colspan="2">
	<?
	$fullname = get_fullname_from_uname($rec_uname);

	$icon="&nbsp;<img src=\"pictures/icon-chat1.gif\">";
	$titel="</b>" . _("Chateinladung verschicken an") . " ";
	$titel.="<a href=\"about.php?username=$rec_uname\"><font size=-1 color=\"#333399\">".$fullname."</font></a><b>";				
	if (is_array($admin_chats)){
		$content = _("Chatraum ausw&auml;hlen:") . "&nbsp;";
		$content .= "<select name=\"chat_id\" style=\"vertical-align:middle;font-size:9pt;\">";
		foreach($admin_chats as $chat_id => $chat_name){
			$content .= "<option value=\"$chat_id\"";
			if ($_REQUEST['selected_chat_id'] == $chat_id){
				$content .= " selected ";
			}
			$content .= ">" . htmlReady($chat_name) . "</option>";
		}
		$content .= "</select><br><br>";
		$content .="<textarea  name=\"message\" style=\"width: 90%\" cols=80 rows=4 wrap=\"virtual\">";
		$content.="</textarea><br />\n";
		if (GetNumberOfBuddies()){
			$content.="<font size=-1><input style=\"vertical-align:middle;\" type=\"CHECKBOX\" name=\"send_all_buddies\" />&nbsp; " . _("Diese Einladung (auch) an alle meine Buddies verschicken") . "</font><br />\n";
		}
		$edit="<input type=\"IMAGE\" " . makeButton("abschicken", "src") . " border=0 align=\"absmiddle\">";
		$edit.="&nbsp; <a href=\"$PHP_SELF\">" . makeButton("abbrechen", "img") . "</a><br>&nbsp;";	
	} else {
		$content = _("Sie haben in keinem aktiven Chatraum die Berechtigung andere NutzerInnen einzuladen!");
		$edit = "<a href=\"$PHP_SELF\">" . makeButton("abbrechen", "img") . "</a><br>&nbsp;";	
	}
	echo"<form action=\"$PHP_SELF\" method=\"POST\">\n";
	echo"<input type=\"HIDDEN\" name=\"rec_uname\" value=\"$rec_uname\">\n";
	echo"<input type=\"HIDDEN\" name=\"cmd\" value=\"chatinsert\">\n";
	echo"<input type=\"HIDDEN\" name=\"username\" value=\"$username\">\n";
	echo"<input type=\"HIDDEN\" name=\"sms_source_page\" value=\"$sms_source_page\">\n";

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
	printhead(0, 0, $link, TRUE,TRUE, $icon, $titel, $zusatz);
	echo "</tr></table>	";

	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
	printcontent("99%",0, $content, $edit);
	echo "</tr></table>";
	
	echo"</form>\n";
}
//Ausgabe von vorhandenen Nachrichten
else {
	$db->query("SELECT globalmessages.*, ". $_fullname_sql['full'] ." AS fullname  FROM globalmessages LEFT JOIN auth_user_md5 ON (username = user_id_snd) LEFT JOIN user_info USING(user_id) WHERE globalmessages.user_id_rec LIKE '".get_username($user->id)."' ORDER BY globalmessages.mkdate DESC");

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic"><img src="pictures/nachricht1.gif" border="0" align="texttop"><b>&nbsp;<?=_("Systeminterne Nachrichten anzeigen")?></b></td>
	<td nowrap class="topic" align="right"><?=_("Einstellungen &auml;ndern")?>&nbsp; <a href="<? echo $PHP_SELF ?>?change_view=TRUE"><img src="pictures/pfeillink.gif" border=0></a>
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
	<blockquote><br />
	<?
	echo _("Sie sehen hier alle systeminternen Nachrichten (SMS), die an Sie verschickt wurden.") . "<br />";
	echo _("Alle Nachrichten seit dem letzten Login erscheinen aufgeklappt.") . "<br>";

	if ($SessSemName[0] && $SessSemName["class"] == "inst")
		echo "<br /><a href=\"institut_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung") . "</a><br>";
	elseif ($SessSemName[0])
		echo "<br /><a href=\"seminar_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung") . "</a><br>";

	if ($db->affected_rows()) {
		echo "<br><form action=\"$PHP_SELF\"> <input type=\"hidden\" name=\"cmd\" value=\"delete_all\" />";
		echo "<input type=\"IMAGE\"  align =\"absmiddle\" " . makeButton("alleloeschen", "src") . " border=0 />&nbsp; <br /><br />";
		echo "<input type=\"CHECKBOX\" name=\"delete_unread\" checked /><font size=-1>";
		echo _("Nachrichten seit letztem Login nicht l&ouml;schen") . "</font></form>";
	}
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
		if (preg_match("/chat_with_me/i", $db->f("message")))  {
			if (count($online)) {
				if ($online[$db->f("user_id_snd")]) { //Nachricht liegt vor und kann angezegt werden (Absender ist online)
							$open="open";
							$neu=TRUE;
							$icon="&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
							$zusatz= sprintf("<font size=-1>" . "gesendet von </font><a href=\"about.php?username=".$db->f("user_id_snd")."\"><font size=-1 color=\"#333399\">".$db->f("fullname")."</font></a><font size=-1> am ".date("d.m.Y, H:i",$db->f("mkdate"))."<font size=-1>&nbsp;"."</font>");				
							$titel=_("Einladung zum Chat");
							$content=$db->f("fullname")." hat Sie am ".date("d.m.Y",$db->f("mkdate"))." um ".date("H:i",$db->f("mkdate"))." Uhr in den Chat eingeladen.\n";
							$content.=_("Wenn Sie mit ihm Chatten wollen, betreten Sie den Chat über das Symbol in der Kopfzeile.");
							
							if ($link)
								$titel = "<a href=\"$link\" class=\"tree\" >".$titel."</a>";

							echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
							printhead(0, 0, $link, $open, $neu, $icon, $titel, $zusatz);
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
		//Keine Chateinladung
		else {
			//Kopfzeile erstellen
			$icon="&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
			if ($db->f("user_id_snd") == "____%system%____") {
				$zusatz="<font size=-1>";
				$zusatz.= sprintf(_("automatische Systemnachricht, gesendet am %s"), date("d.m.Y, H:i",$db->f("mkdate")));
				$zusatz.="</font>";
			} else {
				$zusatz="<font size=-1>";
				$zusatz.= sprintf(_("gesendet von %s am %s"), "</font><a href=\"about.php?username=".$db->f("user_id_snd")."\"><font size=-1 color=\"#333399\">".$db->f("fullname")."</font></a><font size=-1>", date("d.m.Y, H:i",$db->f("mkdate")));
				$zusatz.="</font>";
			}
			if (strpos($db->f("message"),$msging->sig_string))
				$titel=htmlReady(mila(kill_format(substr($db->f("message"), 0, strpos($db->f("message"),$msging->sig_string)))));
			else
				$titel=htmlReady(mila(kill_format($db->f("message"))));
			
			$content=quotes_decode(formatReady($db->f("message")));

			$edit='';
			if ($db->f("user_id_snd") != "____%system%____") {
				$edit="<a href=\"$PHP_SELF?cmd=write&rec_uname=".$db->f("user_id_snd")."\">" . makeButton("antworten", "img") . "</a>";
				$edit.="&nbsp;<a href=\"$PHP_SELF?cmd=write&quote=".$db->f("message_id")."&rec_uname=".$db->f("user_id_snd")."\">" . makeButton("zitieren", "img") . "</a>";
			}
			$edit.="&nbsp;<a href=\"$PHP_SELF?cmd=delete&delete_msg[1]=".$db->f("message_id")."\">" . makeButton("loeschen", "img") . "</a>";

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
				
			if ($link)
				$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
		
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\" align=\"center\"><tr>";
			printhead(0, 0, $link, $open, $red, $icon, $titel, $zusatz);
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
		$srch_result="info§<font size=-1><b>" . _("Im Augenblick liegen keine systeminternen Nachrichten f&uuml;r Sie vor.") . "</b></font>";
		parse_msg ($srch_result, "§", "steel1", 2, FALSE);
		echo "</td></tr></table><br />";
		}
		
	//letzte Besuchszeit ablegen
	$my_messaging_settings["last_visit"]=time();
	}

?>
</td></tr></table>
<?

// Save data back to database.
page_close()
	?>
</body>
</html>
