<?

/*
sms_box.php - Verwaltung von systeminternen Kurznachrichten - Eingang/ Ausgang
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Nils K. Windisch <info@nkwindisch.de>

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

// start functions

function count_snd_messages_from_user($user_id) {
	global $db;
	$x = "0";
	$query = "SELECT DISTINCT message_id 
		FROM message_user 
			WHERE snd_rec = 'snd'
			AND user_id = '".$user_id."' 
			AND deleted = '0'";
	$db->query($query);
	while ($db->next_record()) {
		$x = $x+1;
	}
	return $x;
}

function count_rec_messages_from_user($user_id) {
	global $db;
	$x = "0";
	$query = "SELECT DISTINCT message_id 
		FROM message_user 
			WHERE snd_rec = 'rec'
			AND user_id = '".$user_id."' 
			AND deleted = '0'";
	$db->query($query);
	while ($db->next_record()) {
		$x = $x+1;
	}
	return $x;
}

function set_read($message_id) {
	global $db7, $user;
	$user_id = $user->id;
	$query = "UPDATE IGNORE message_user SET readed=1 WHERE user_id = '$user_id' AND message_id = '$message_id'";
	$db7->query($query);
}

// print_snd_message

function print_snd_message($mkdate, $message_id, $message, $sms_data_open, $sms_data_view) {
	global $n, $LastLogin, $my_messaging_settings, $cmd, $db7;	
	//Kopfzeile erstellen
	$icon = "&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
	if ($cmd == "select_all") {
		$checked = "checked";
	} else {
		$checked = "";
	}
	$x = "0";
	$query = "SELECT * FROM message_user WHERE message_id = '".$message_id."' AND snd_rec = 'rec'";
	$db7->query($query);
	while ($db7->next_record()) {
		$x = $x+1;
	}

	$zusatz = "<font size=-1>";
	if ($x == "1") {
		$query = "SELECT message_user.* 
		FROM message_user 
			LEFT JOIN auth_user_md5 USING(user_id) 
			WHERE message_user.message_id = '".$message_id."' 
			AND message_user.snd_rec = 'rec'";
		$db7->query($query);
		while ($db7->next_record()) {
			$rec_userid = $db7->f("user_id");
		}
		$zusatz .= sprintf(_("gesendet an %s am %s %s"), "</font><a href=\"about.php?username=".get_username($rec_userid)."\"><font size=-1 color=\"#333399\">".get_fullname($rec_userid)."</font></a><font size=-1>", date("d.m.Y, H:i",$mkdate), "<input type=\"checkbox\" name=\"sel_delsms[]\" value=\"".$message_id."\" ".$checked.">");
	} else if ($x >= "2") {
		$zusatz .= sprintf(_("gesendet an %s Empf&auml;nger am %s %s"), $x, date("d.m.Y, H:i",$mkdate), "<input type=\"checkbox\" name=\"sel_delsms[]\" value=\"".$message_id."\" ".$checked.">");
	}
	$zusatz .= "</font>";	
	
	if (strpos($message,$msging->sig_string)) {
		$titel = mila(kill_format(substr($message, 0, strpos($message,$msging->sig_string))));
	} else {
		$titel = mila(kill_format($message));
	}
	
	$content = quotes_decode(formatReady($message));
	if ($x >= "2") {
		$content .= "<br><br>"._("gesendet an:")."<br>";
		$query = "SELECT message_user.* 
		FROM message_user 
			LEFT JOIN auth_user_md5 USING(user_id) 
			WHERE message_user.message_id = '".$message_id."' 
			AND message_user.snd_rec = 'rec'";
		$db7->query($query);
		while ($db7->next_record()) {
			$content .= "<a href=\"about.php?username=".get_username($db7->f("user_id"))."\"><font size=-1 color=\"#333399\">".get_fullname($db7->f("user_id"))."</font></a> / ";
		}
	}

	$edit = "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$message_id."\">".makeButton("loeschen", "img")."</a><br><br>";
	
	if ($sms_data_open == $message_id) {
		$open = "open";
		#$link = $PHP_SELF."?mclose=TRUE";
		$link = "sms_box.php?mclose=TRUE";
	} else {
		$open = "close";
		#$link = $PHP_SELF."?mopen=".$message_id."#".$message_id;
		$link = "sms_box.php?mopen=".$message_id."#".$message_id;
	}
	
	if (strlen($titel) >= "50") {
		$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".substr($titel, 0, 30)." ...</a></a>";
	} else {
		$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".$titel."</a></a>";
	}

	// ausgabe der ueberschrift		
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	printhead(0, 0, $link, $open, FALSE, $icon, $titel, $zusatz, $mkdate);
	echo "</tr></table>	";
	// ausgabe des content
	if (($open == "open") || ($sms_data_open == $message_id)) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";		
	}
	return $n++;
}

// print_rec_message

function print_rec_message($user_id_snd, $mkdate, $message_id, $message, $fullname, $sms_data_open, $read) {
	global $n, $LastLogin, $my_messaging_settings, $cmd;	

	$uname_snd = get_username($user_id_snd);

	if ($cmd == "select_all") {
		$checked = "checked";
	} else {
		$checked = "";
	}

	// open if unread
	if ($read != "1") {
		$open = "open";
		$link = "sms_box.php?mclose=TRUE";
		#$link = $PHP_SELF."?mclose=TRUE";
	} else if ($sms_data_open == $message_id) {
		$open = "open";
		$link = "sms_box.php?mclose=TRUE";
		#$link = $PHP_SELF."?mclose=TRUE";
	} else {
		$open = "close";
		$link = "sms_box.php?mopen=".$message_id;
		#$link = $PHP_SELF."?mopen=".$message_id;
	}

	if ($read == "1") {
		$red = FALSE;
		$icon = "&nbsp;<img src=\"pictures/cont_nachricht.gif\">";
	} else {
		$red = TRUE;
		$icon = "&nbsp;<img src=\"pictures/cont_nachricht_rot.gif\">";
		if ($open == "open") {
			set_read($message_id);		
		}
	}

	if ($user_id_snd == "____%system%____") {
		$zusatz = "<font size=-1>";
		$zusatz .= sprintf(_("automatische Systemnachricht, gesendet am %s %s"), date("d.m.Y, H:i", $mkdate), "<input type=\"checkbox\" name=\"sel_delsms[]\" value=\"".$message_id."\" ".$checked.">");
		$zusatz .= "</font>";
	} else {
		if ($cmd == "select_all") {
			$checked = "checked";
		} else {
			$checked = "";
		}
		$zusatz = "<font size=-1>";
		$zusatz .= sprintf(_("gesendet von %s am %s %s"), "</font><a href=\"about.php?username=".$uname_snd."\"><font size=-1 color=\"#333399\">".$fullname."</font></a><font size=-1>", date("d.m.Y, H:i", $mkdate), "<input type=\"checkbox\" name=\"sel_delsms[]\" value=\"".$message_id."\" ".$checked.">");
		$zusatz .= "</font>";			
	}
	
	if (strpos($message, $msging->sig_string)) {
		$titel = mila(kill_format(substr($message, 0, strpos($message, $msging->sig_string))));
	} else {
		$titel = mila(kill_format($message));
	}
	
	$content = quotes_decode(formatReady($message));

	$edit = "";
	if ($user_id_snd != "____%system%____") {
		$edit .= "<a href=\"sms_send.php?cmd=write&rec_uname=".$uname_snd."\">".makeButton("antworten", "img")."</a>";
		$edit .= "&nbsp;<a href=\"sms_send.php?cmd=write&quote=".$message_id."&rec_uname=".$uname_snd."\">".makeButton("zitieren", "img")."</a>";
	}
	$edit.= "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$message_id."\">".makeButton("loeschen", "img")."</a><br><br>";

	if (strlen($titel) >= "50") {
		$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".substr($titel, 0, 30)." ...</a></a>";
	} else {
		$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".$titel."</a></a>";
	}

	// ausgabe der ueberschrift		
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	printhead(0, 0, $link, $open, $red, $icon, $titel, $zusatz, $mkdate);
	echo "</tr></table>	";
	// ausgabe des content
	if (($open == "open") || ($sms_data_open == $message_id)) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";		
	}
	return $n++;
}

function show_icon($sms_show, $value) {
	if ($sms_show == $value) {
		$x = "forumgrau.gif";
	} else {
		$x = "blank.gif";
	}
	return $x;
}

// end of functions

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
require_once ("$ABSOLUTE_PATH_STUDIP/reiter.inc.php");
if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php"; 
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$admin_chats = $chatServer->getAdminChats($auth->auth['uid']);
}
$sess->register("sms_data");
$sess->register("sms_show");
$msging = new messaging;

$db = new DB_Seminar;
$db6 = new DB_Seminar;
$db7 = new DB_Seminar;

// delete selected messages
if ($cmd == "delete_selected") {
	$l = 0;
	if (is_array($sel_delsms)) {
		foreach ($sel_delsms as $a) {
			$count_deleted_sms = $msging->delete_message($a);
			$l = $l+$count_deleted_sms;
		}
	}
	if ($l) {
		if ($l == "1") {
			$msg = "msg§"._("Es wurde eine Nachricht gel&ouml;scht.");
		} else {
			$msg = "msg§".sprintf(_("Es wurden %s Nachrichten gel&ouml;scht."), $l);
		}
	} else {
		$msg = "error§"._("Es konnten keine Nachrichten gel&ouml;scht werden.");
	}
}

if ($mclose) {
	$sms_data["open"] = '';
}

if ($mopen) {
	$sms_data["open"] = $mopen;
}

if ($sms_inout) {
	$sms_data["view"] = $sms_inout;
} else if ($sms_data["view"] == "") {
	$sms_data["view"] = "in";
}

if ($sms_time) {
	$sms_data["time"] = $sms_time;
} else if ($sms_data["time"] == "") {
	$sms_data["time"] = "all";
}

if ($sms_data['view'] == "in") {
	$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("empfangene systeminterne Nachrichten anzeigen")."</b>";
	$info_text_002 = _("Alle systeminternen Nachrichten, die an Sie gesendet wurden werden hier angezeigt.<br>");
	$no_message_text_box = _("im Posteingang");
} else if ($sms_data['view'] == "out") {
	$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("gesendete systeminterne Nachrichten anzeigen")."</b>";
	$info_text_002 = _("Alle systeminternen Nachrichten, die Sie gesendet haben werden hier angezeigt.<br>");
	$no_message_text_box = _("im Postausgang");
}

if ($sms_data["time"] == "all") {
	$info_text_003 = _("Sie sehen alle Ihre Nachrichten.");
	$query_time = " ORDER BY message.mkdate DESC";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten %s vor."), $no_message_text_box);
}
if ($sms_data["time"] == "new") {
	$info_text_003 = _("Sie sehen nur neue Nachrichten.");
	if ($sms_data["view"] == "in") {
		$query_time = " AND message.mkdate > '".$LastLogin."' ORDER BY message.mkdate DESC";
	} else {
		$query_time = " AND message.mkdate > '".$CurrentLogin."' ORDER BY message.mkdate DESC";
	}
	$no_message_text = sprintf(_("Es liegen keine neuen systeminternen Nachrichten %s vor."), $no_message_text_box);
}
if ($sms_data["time"] == "24h") {
	$info_text_003 = _("Sie sehen nur Nachrichten der letzten 24 Stunden.");
	$query_time = " AND message.mkdate > '".(date("U")-86400)."' ORDER BY message.mkdate DESC";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten aus den letzten 24 Stunden %s vor."), $no_message_text_box);
}
if ($sms_data["time"] == "7d") {
	$info_text_003 = _("Sie sehen alle Nachrichten der letzten 7 Tage.");
	$query_time = " AND message.mkdate > '".(date("U")-(7*86400))."' ORDER BY message.mkdate DESC";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten aus den letzten 7 Tagen %s vor."), $no_message_text_box);
}
if ($sms_data["time"] == "30d") {
	$info_text_003 = _("Sie sehen alle Nachrichten der letzten 30 Tage.");
	$query_time = " AND message.mkdate > '".(date("U")-(30*86400))."' ORDER BY message.mkdate DESC";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten aus den letzten 30 Tagen %s vor."), $no_message_text_box);
}
if ($sms_data["time"] == "older") {
	$info_text_003 = _("Sie sehen nur Nachrichten, die &auml;lter als 30 Tage sind.");
	$query_time = " AND message.mkdate < '".(date("U")-(30*86400))."' ORDER BY message.mkdate DESC";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten %s vor, die &auml;lter als 30 Tage sind."), $no_message_text_box);
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_sms.inc.php");

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</td></tr></table>";
	page_close();
	die;
}

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
	<td class="topic" colspan="2"><?
		echo $info_text_001;
	?></td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
<tr>	
	<td class="blank" valign="top"> <? 
	if ($msg) {
		print ("<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"99%\"><tr><td valign=\"top\">");
		parse_msg($msg); 
		print ("</td></tr></table>");
	}
	print("<table cellpadding=\"5\" border=\"0\" width=\"100%\"><tr><td colspan=\"2\" valign=\"top\">");
	print("<font size=-1>".$info_text_002.$info_text_003);
	if ($SessSemName[0] && $SessSemName["class"] == "inst") {
		echo "<br><br><a href=\"institut_main.php\">"._("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung")."</a>";
	} elseif ($SessSemName[0]) {
		echo "<br><br><a href=\"seminar_main.php\">"._("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung")."</a>";
	}
	print("</font></td><td class=\"blank\" align=\"right\" valign=\"bottom\">");
	print("<a href=\"".$PHP_SELF."?cmd=select_all\">".makeButton("alleauswaehlen", "img")."</a><br><br>");
	print("&nbsp;&nbsp;");
	print("<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">");
	print("<input type=\"hidden\" name=\"cmd\" value=\"delete_selected\">");
	print("<input type=\"image\" name=\"kill\" border=\"0\" ".makeButton("markierteloeschen", "src")." value=\"loeschen\">");			
	print("</td></tr>\n");
	print("</table>");

	$n=0;
	$query = "";
	if ($sms_data['view'] == "in") {
		$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("empfangene systeminterne Nachrichten anzeigen")."</b>";
		$info_text_002 = _("Alle systeminternen Nachrichten, die an Sie gesendet wurden werden hier angezeigt.<br>");
		$query .= "SELECT message.*, message_user.*, ".$_fullname_sql['full']." AS fullname FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) LEFT JOIN user_info USING(user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_time;		
		$db->query($query);		
		while ($db->next_record()) {
			print_rec_message($db->f("autor_id"), $db->f("mkdate"), $db->f("message_id"), $db->f("message"), get_fullname($db->f("autor_id")), $sms_data["open"], $db->f("readed"));	
		}	
	} else if ($sms_data['view'] == "out") {
		$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("gesendete systeminterne Nachrichten anzeigen")."</b>";
		$info_text_002 = _("Alle systeminternen Nachrichten, die Sie gesendet haben werden hier angezeigt.<br>");
		$query .= "SELECT message.*, message_user.* FROM message_user LEFT JOIN message USING (message_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'snd' AND message_user.deleted = '0' ".$query_time;
		$db->query($query);
		while ($db->next_record()) {
			print_snd_message($db->f("mkdate"), $db->f("message_id"), $db->f("message"), $sms_data["open"], $sms_data["view"]);		
		}	
	}

	echo "</form>"; // close form "delete selected messages"

	// wenn keine nachrichten zum anzeigen
	if (!$n) {
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\">";
		$srch_result = "info§<font size=-1><b>".$no_message_text."</b></font>";
		parse_msg ($srch_result, "§", "steel1", 2, FALSE);
		echo "</td></tr></table><br />";
	}

	print "</td><td class=\"blank\" width=\"270\" align=\"right\" valign=\"top\">";

	$time_by_links = ""; // build links to narrow down the messages
	$time_by_links .= "Sie können die Anzeige der Nachrichten zeitlich eingrenzen"."<br>";
	$time_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?sms_time=new\"><img src=\"pictures/".show_icon($sms_data["time"], "new")."\" width=\"10\" height=\"20\" border=\"0\">&nbsp;"._("neue Nachrichten")."</a><br>";
	$time_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?sms_time=all\"><img src=\"pictures/".show_icon($sms_data["time"], "all")."\" width=\"10\" height=\"20\" border=\"0\">&nbsp;"._("alle Nachrichten")."</a><br>";
	$time_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?sms_time=24h\"><img src=\"pictures/".show_icon($sms_data["time"], "24h")."\" width=\"10\" height=\"20\" border=\"0\">&nbsp;"._("letzte 24 Stunden")."</a><br>";
	$time_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?sms_time=7d\"><img src=\"pictures/".show_icon($sms_data["time"], "7d")."\" width=\"10\" height=\"20\" border=\"0\">&nbsp;"._("letzte 7 Tage")."</a><br>";
	$time_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?sms_time=30d\"><img src=\"pictures/".show_icon($sms_data["time"], "30d")."\" width=\"10\" height=\"20\" border=\"0\">&nbsp;"._("letzte 30 Tage")."</a><br>";
	$time_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?sms_time=older\"><img src=\"pictures/".show_icon($sms_data["time"], "older")."\" width=\"10\" height=\"20\" border=\"0\">&nbsp;"._("&auml;lter als 30 Tage")."</a>";

	$infobox = array(
		array("kategorie" => _("Information:"),"eintrag" => array(
			array("icon" => "pictures/ausruf_small.gif", "text" => sprintf(_("Sie haben %s empfangene und %s gesendete Nachrichten."), count_rec_messages_from_user($user->id), count_snd_messages_from_user($user->id)))
		)),

		array("kategorie" => _("Anzeigezeitraum ausw&auml;hlen:"),"eintrag" => array(
			array("icon" => "pictures/suchen.gif", "text" => $time_by_links)
		))
	);
	print_infobox($infobox,"pictures/sms3.jpg");

?></td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
</table><?

// Save data back to database.
page_close() ?>

</body>
</html>
