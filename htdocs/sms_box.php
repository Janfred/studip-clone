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
require_once ("$ABSOLUTE_PATH_STUDIP/sms_functions.inc.php");
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

// sortierung festlegen
if ($cmd_sort) { 
	$sms_show['sort'] = $cmd_sort;
} else if (empty($sms_show['sort'])) {
	$sms_show['sort'] = "no";
}

// set timefilter
if (empty($my_messaging_settings["timefilter"])) { 
	$my_messaging_settings["timefilter"] ="all";
}

// delete selected messages
if ($delete_selected_button_x || $cmd == "delete_selected") {
	$l = 0;
	if (is_array($sel_sms)) {
		foreach ($sel_sms as $a) {
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

// open festlegen
if ($mclose) {
	$sms_data["open"] = '';
} else if ($mopen) {
	$sms_data["open"] = $mopen;
}

// view festlegen
if ($sms_inout) {
	$sms_data["view"] = $sms_inout;
} else if ($sms_data["view"] == "") {
	$sms_data["view"] = "in";
}

if ($cmd == "mark_allsmsreaded") {
	$msging->set_read_all_messages();
	$msg = "msg§".sprintf(_("Es wurden alle ungelesenen Nachrichten als gelesen gespeichert."), $l);
}

// folder festlegen
if (!$sms_show['folder'][$sms_data['view']]) {
	$sms_show['folder'][$sms_data['view']] = $my_messaging_settings["folder"]['active'][$sms_data['view']];
}
if ($show_folder == "close") {
	$sms_show['folder'][$sms_data['view']] = "close";
} else if ($show_folder != "") {
	$sms_show['folder'][$sms_data['view']] = $show_folder;
	$my_messaging_settings["folder"]['active'][$sms_data['view']] = $sms_show['folder'][$sms_data['view']];
}

// neuen folder anlegen
if ($new_folder[0] != "" && $new_folder_button_x) {
	$my_messaging_settings["folder"][$sms_data["view"]] = array_add_value($new_folder, $my_messaging_settings["folder"][$sms_data["view"]]);
	sort($my_messaging_settings["folder"][$sms_data["view"]]);
	$msg = "msg§".sprintf(_("Der Ordner %s wurde angelegt."), $new_folder[0]);
}

// folder loeschen
if ($delete_folder && $delete_folder_button_x) {
	if ($sms_data["view"] == "in") {
		$tmp_sndrec = "rec";
	} else {
		$tmp_sndrec = "snd";
	}
	$query = "UPDATE message_user SET folder='' WHERE folder='".$delete_folder."' AND snd_rec='".$tmp_sndrec."'";
	$db->query($query);
	$my_messaging_settings["folder"][$sms_data["view"]] = array_delete_value($my_messaging_settings["folder"][$sms_data["view"]], $delete_folder);
	sort($my_messaging_settings["folder"][$sms_data["view"]]);
	$msg = "msg§".sprintf(_("Der Ordner %s wurde gelöscht."), $delete_folder);
}

// folder umbennen
if ($ren_folder_button_x) {
	if ($sms_data["view"] == "in") {
		$tmp_sndrec = "rec";
	} else {
		$tmp_sndrec = "snd";
	}
	$query = "UPDATE message_user SET folder='".$new_foldername."' WHERE folder='".$orig_folder_name."' AND snd_rec='".$tmp_sndrec."'";
	$db->query($query);
	$my_messaging_settings["folder"][$sms_data["view"]] = array_delete_value($my_messaging_settings["folder"][$sms_data["view"]], $orig_folder_name);
	$tmp[] = $new_foldername;
	$my_messaging_settings["folder"][$sms_data["view"]] = array_add_value($tmp, $my_messaging_settings["folder"][$sms_data["view"]]);
	unset($tmp);
	sort($my_messaging_settings["folder"][$sms_data["view"]]);
	$msg = "msg§".sprintf(_("Der Ordner %s wurde in %s umbenannt."), $orig_folder_name, $new_foldername);
}

// openall festlegen
if (empty($my_messaging_settings["openall"])) { 
	$my_messaging_settings["openall"] = "2";
}

// zeitfilter festlegen
if ($sms_time) { 
	$sms_data["time"] = $sms_time;
} else if ($sms_data["time"] == "" && empty($my_messaging_settings["timefilter"])) {
	$sms_data["time"] = "all";
	$my_messaging_settings["timefilter"] = "all";
} else if ($sms_data["time"] == "" && !empty($my_messaging_settings["timefilter"])) {
	$sms_data["time"] = $my_messaging_settings["timefilter"];
}

// folder festlegen
if ($sms_show['folder'][$sms_data['view']]) { 
	if ($sms_show['folder'][$sms_data['view']] != "all") {
		if ($sms_show['folder'][$sms_data['view']] == "free") {
			$query_showfolder = "AND message_user.folder=''";
			$infotext_folder = "&nbsp;("._("Ordner").":&nbsp;"._("Unzugeordnet").")";
		} else {
			$query_showfolder = "AND message_user.folder='".$sms_show['folder'][$sms_data['view']]."'";
			$infotext_folder = "&nbsp;("._("Ordner").":&nbsp;".$sms_show['folder'][$sms_data['view']].")";
		}
	} else {
		$infotext_folder = "&nbsp;("._("Ordner").":&nbsp;"._("Alle Nachrichten").")";
	}
} else {
	$infotext_folder = "&nbsp;("._("Ordner").":&nbsp;"._("Alle Nachrichten").")";
	$sms_show['folder'][$sms_data['view']] = "all";
}

// texte definieren
if ($sms_data['view'] == "in") {
	$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("empfangene systeminterne Nachrichten anzeigen")."</b>";
	$no_message_text_box = _("im Posteingang");
	$tmp_snd_rec = "rec";
} else if ($sms_data['view'] == "out") {
	$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("gesendete systeminterne Nachrichten anzeigen")."</b>";
	$no_message_text_box = _("im Postausgang");
	$tmp_snd_rec = "snd";
}

// nachricht loesch-schuetzen bzw. diesen aufheben
if ($sel_lock) { 
	if ($cmd == "safe_selected") { // close lock message delete
		$tmp_dont_delete = "1";
		$msg = "msg§"._("Der Lösch-Schutz wurde für die gewählte Nachricht aktiviert.");
	} else if ($cmd == "open_selected") { // open lock message delete
		$tmp_dont_delete = "0";
		$msg = "msg§"._("Der Lösch-Schutz wurde für die gewählte Nachricht aufgehoben.");
	}	
	$db->query("UPDATE message_user SET dont_delete='".$tmp_dont_delete."' WHERE user_id='".$user->id."' AND message_id='".$sel_lock."' AND snd_rec='".$tmp_snd_rec."'");
	$tmp_dont_delete = "";
	$tmp_snd_rec = "";
}

// wenn nachrichten zum verschieben ausgewaehlt
if (is_array($move_to_folder)) {
	$sms_data['tmp']['move_to_folder'] = $move_to_folder;
}

// wenn mehrere verschieben-button gedrueckt
if ($move_selected_button_x && !empty($sel_sms)) {
	$sms_data['tmp']['move_to_folder'] = $sel_sms;
}

// action: nachricht in ordner verschieben
if ($move_folder) { 
	$user_id = $user->id;
	if ($move_folder == "free") {
		$move_folder = "";
	}
	$l = 0;
	if (is_array($sms_data['tmp']['move_to_folder'])) {
		foreach ($sms_data['tmp']['move_to_folder'] as $a) {
			if ($db->query("UPDATE message_user SET folder='".$move_folder."' WHERE message_id='".$a."' AND user_id='".$user_id."' AND snd_rec='".$tmp_snd_rec."'")) {
				$l = $l+1;
			}
		}
	}
	if ($l) {
		if ($l == "1") {
			$msg = "msg§"._("Es wurde eine Nachricht verschoben.");
		} else {
			$msg = "msg§".sprintf(_("Es wurden %s Nachrichten verschoben."), $l);
		}
	} else {
		$msg = "error§"._("Es konnten keine Nachrichten verschoben werden.");
	}
	unset($sms_data['tmp']['move_to_folder']);
	$move_folder = "";
	$tmp_snd_rec = "";
} 

// query wenn nachrichten verschieben
if ($sms_data['tmp']['move_to_folder']) {
	if (sizeof($sms_data['tmp']['move_to_folder']) == "1") {
		if ($sms_data['tmp']['move_to_folder'][1] == "") {
			$tmp_partquery = $sms_data['tmp']['move_to_folder'][0];
		} else {
			$tmp_partquery = $sms_data['tmp']['move_to_folder'][1];
		}
		$query_movetofolder = "AND message.message_id='".$tmp_partquery."'"; // es wird nur diese nachricht angezeigt	
	} else {
		$query_movetofolder = "AND (message.message_id='".$sms_data['tmp']['move_to_folder'][0]."'";
		for($x=1;$x<sizeof($sms_data['tmp']['move_to_folder']);$x++) {
			$query_movetofolder .= " OR message.message_id='".$sms_data['tmp']['move_to_folder'][$x]."'";		
		}
		$query_movetofolder .= ")";
	}
}

if ($sms_data["time"] == "all") {
	$query_time = " ORDER BY message.mkdate DESC";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten%s %s vor."), $infotext_folder, $no_message_text_box);
} else if ($sms_data["time"] == "new") {
	if ($sms_data["view"] == "in") {
		$query_time = " AND message.mkdate > '".$LastLogin."' ORDER BY message.mkdate DESC";
		$query_time_sort = " AND message.mkdate > '".$LastLogin."'";
	} else {
		$query_time = " AND message.mkdate > '".$CurrentLogin."' ORDER BY message.mkdate DESC";
	}
	$no_message_text = sprintf(_("Es liegen keine neuen systeminternen Nachrichten%s %s vor."), $infotext_folder, $no_message_text_box);
} else if ($sms_data["time"] == "24h") {
	$query_time = " AND message.mkdate > '".(date("U")-86400)."' ORDER BY message.mkdate DESC";
	$query_time_sort = " AND message.mkdate > '".(date("U")-86400)."'";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten%s aus den letzten 24 Stunden %s vor."), $infotext_folder, $no_message_text_box);
} else if ($sms_data["time"] == "7d") {
	$query_time = " AND message.mkdate > '".(date("U")-(7*86400))."' ORDER BY message.mkdate DESC";
	$query_time_sort = " AND message.mkdate > '".(date("U")-(7*86400))."'";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten%s aus den letzten 7 Tagen %s vor."), $infotext_folder, $no_message_text_box);
} else if ($sms_data["time"] == "30d") {
	$query_time = " AND message.mkdate > '".(date("U")-(30*86400))."' ORDER BY message.mkdate DESC";
	$query_time_sort = " AND message.mkdate > '".(date("U")-(30*86400))."'";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten%s aus den letzten 30 Tagen %s vor."), $infotext_folder, $no_message_text_box);
} else if ($sms_data["time"] == "older") {
	$query_time = " AND message.mkdate < '".(date("U")-(30*86400))."' ORDER BY message.mkdate DESC";
	$query_time_sort = " AND message.mkdate < '".(date("U")-(30*86400))."'";
	$no_message_text = sprintf(_("Es liegen keine systeminternen Nachrichten%s %s vor, die &auml;lter als 30 Tage sind."), $infotext_folder, $no_message_text_box);
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_sms.inc.php");

if ($auth->auth["jscript"]) { // JS an und erwuenscht?
	echo "<script language=\"JavaScript\">var ol_textfont = \"Arial\"</script>";
	echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
	echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"overlib.js\"></SCRIPT>";
}

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</td></tr></table>";
	page_close();
	die;
} 

?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr><td class="topic" colspan="2"><?=$info_text_001?></td></tr>
<tr><td class="blank" colspan="2">&nbsp;</td></tr>
<tr>	
	<td class="blank" valign="top"> <? 
		if ($msg) { // if info ($msg) for user
			print ("<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" width=\"99%\"><tr><td valign=\"top\">");
			parse_msg($msg); 
			print ("</td></tr></table>");
		} ?>
		<table cellpadding="3" cellspacing="0" border="0" width="100%">
			<tr>
				<td class="blank" align="left" valign="bottom">&nbsp; <? 
					if ($cmd != "admin_folder") {
						echo "<a href=\"".$PHP_SELF."?cmd=admin_folder&cmd_2=new\">".makeButton("neuerordner", "img")."</a>";
					} else if ($cmd == "admin_folder") {
						echo "<a href=\"".$PHP_SELF."?cmd=\">".makeButton("zurueck", "img")."</a>";
					} ?>
				</td>
			</tr>
		</table> <?
		// ordner verwaltung 
		if ($cmd == "admin_folder") { 
			if ($cmd_2 == "new") {
				$tmp[0] = "new_folder[]";
				$tmp[1] = _("einen neuen Ordner anlegen");
				$tmp[2] = "new_folder_button";
				$tmp[3] = "";
				$tmp[4] = "";
			}
			if ($ren_folder) {
				$tmp[0] = "new_foldername";
				$tmp[1] = _("einen bestehenden Ordner umbennen");
				$tmp[2] = "ren_folder_button";
				$tmp[3] = " value=\"".$ren_folder."\"";
				$tmp[4] = "<input type=\"hidden\" name=\"orig_folder_name\" value=\"".$ren_folder."\">";
			}
			$titel = "	<input type=\"text\" name=\"".$tmp[0]."\"".$tmp[3].">";
			echo "\n<form action=\"".$PHP_SELF."\" method=\"post\"><table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
			printhead(0, 0, FALSE, "open", FALSE, "<img src=\"pictures/cont_folder.gif\" border=0>", $titel, FALSE);
			echo "</tr></table>	";
			$content_content = $tmp[1]."<div align=\"center\">".$tmp[4]."
			<input type=\"image\" name=\"".$tmp[2]."\" border=\"0\" ".makeButton("uebernehmen", "src")." value=\"a\" align=\"absmiddle\">
			<input type=\"image\" name=\"a\" border=\"0\" ".makeButton("abbrechen", "src")." value=\"a\" align=\"absmiddle\"><div>";
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
			printcontent("99%",0, $content_content, FALSE);
			echo "</form></tr></table>";
		}

		if (!$sms_data['tmp']['move_to_folder']) { // wenn nicht verschieben
			// neue-nachrichten-ordner
			if ($sms_data['view'] == "in") { // zeige neue-nachrichten-ordner wenn im eingang ...
				$count = count_messages_from_user($sms_data['view'], "AND deleted='0' AND readed='0'"); // neue nachrichten zaehlen
				if ($count >= "1") { // nur zeigen, wenn auch neue nachrichten ...
					$link = folder_makelink("new");
					$titel = "<a href=\"".$link."\" class=\"tree\" >"._("Ungelesene Nachrichten")."</a>";
					$zusatz = $count."&nbsp;"._("Nachrichten");
					echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
					printhead(0, 0, $link, folder_openclose($sms_show['folder'][$sms_data['view']], "new"), FALSE, "<a href=\"".$link."&cmd_show=openall\"><img src=\"pictures/".showfoldericon("new", $count)."\" border=0></a>", $titel, $zusatz);
					echo "</tr></table>";
					if (folder_openclose($sms_show['folder'][$sms_data['view']], "new") == "open") print_new_messages();
				}
			}
			// alle-nachrichten-ordner
			$link = folder_makelink("all");
			$count = count_messages_from_user($sms_data['view'], "AND deleted='0'");
			$count_timefilter = count_x_messages_from_user($sms_data['view'], "all", $query_time_sort);
			$titel = "<a href=\"".$link."\" class=\"tree\" >"._("Alle Nachrichten")."</a>";
			$symbol = "<a href=\"".$link."&cmd_show=openall\"><img src=\"pictures/".showfoldericon("all", $count)."\" border=0></a>";
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
			$zusatz = show_nachrichtencount($count, $count_timefilter);
			printhead(0, 0, $link, folder_openclose($sms_show['folder'][$sms_data['view']], "all"), FALSE, $symbol, $titel, $zusatz);
			echo "</tr></table>";
			$content_content = _("Dieser Ordner zeigt alle Nachrichten.")."<br><br>";
			$content_content .= "<div align=\"center\">
				<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
				<input type=\"hidden\" name=\"cmd\" value=\"select_all\">
				<input type=\"image\" name=\"select\" border=\"0\" ".makeButton("alleauswaehlen", "src")." value=\"loeschen\">
				</form>
				<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
				<input type=\"hidden\" name=\"cmd\" value=\"delete_selected\">
				<input type=\"image\" name=\"kill\" border=\"0\" ".makeButton("markierteloeschen", "src")." value=\"loeschen\"><br></div>";
			if (folder_openclose($sms_show['folder'][$sms_data['view']], "all") == "open") {
				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
				if ($count_timefilter != "0") {
					echo "<td class=\"blank\" background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/blank.gif\" height=\"100%\" width=\"10px\"></td>";
				}
				printcontent("99%",0, $content_content, FALSE);
				echo "</tr></table>	";		
			}
			if (folder_openclose($sms_show['folder'][$sms_data['view']], "all") == "open") print_messages();
		}
		if (!empty($my_messaging_settings["folder"][$sms_data['view']])) {
			// unzugeordnete-nachrichten-ordner
			$count = count_messages_from_user($sms_data['view'], "AND deleted='0' AND folder=''");
			$count_timefilter = count_x_messages_from_user($sms_data['view'], "", $query_time_sort);
			$open = folder_openclose($sms_show['folder'][$sms_data['view']], "free");
			if ($sms_data['tmp']['move_to_folder'] && $open == "close") { // wenn in diesen ordner verschiebbar
				$picture = "move.gif";
				$link = $PHP_SELF."?move_folder=free";
			} else {
				$picture = showfoldericon("", $count);
			}
			if (empty($sms_data['tmp']['move_to_folder'])) $link = folder_makelink("free");
			$symbol = "<a href=\"".$link."&cmd_show=openall\"><img src=\"pictures/".$picture."\" border=0></a>";
			$titel = "<a href=\"".$link."\" class=\"tree\" >"._("Unzugeordnet")."</a>";
			$zusatz = show_nachrichtencount($count, $count_timefilter);
			echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
			printhead(0, 0, $link, $open, FALSE, $symbol, $titel, $zusatz);
			echo "</tr></table>	";	
			$content_content = "<div align=\"center\">"._("markierte Nachrichten:")."
				<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
					<input type=\"hidden\" name=\"cmd\" value=\"select_all\">
					<input type=\"image\" name=\"select\" border=\"0\" ".makeButton("alleauswaehlen", "src")." value=\"loeschen\" align=\"absmiddle\">
				</form>
				<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
					<input type=\"image\" name=\"delete_selected_button\" border=\"0\" ".makeButton("loeschen", "src")." value=\"delete_selected\" align=\"absmiddle\">
					<input type=\"image\" name=\"move_selected_button\" border=\"0\" ".makeButton("verschieben", "src")." value=\"move\" align=\"absmiddle\"><br></div>";
			if ($open == "open") {
				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
				if ($count_timefilter != "0") {
					echo "<td class=\"blank\" background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/blank.gif\" height=\"100%\" width=\"10px\"></td>";
				}
				printcontent("99%",0, $content_content, $edit);
				echo "</tr></table>	";		
			}
			if (folder_openclose($sms_show['folder'][$sms_data['view']], "free") == "open") print_messages();
			// persoenliche ordner
			for($x="0";$x<sizeof($my_messaging_settings["folder"][$sms_data['view']]);$x++) {
				$count = count_messages_from_user($sms_data['view'], "AND deleted='0' AND folder='".$my_messaging_settings["folder"][$sms_data['view']][$x]."'");
				$count_timefilter = count_x_messages_from_user($sms_data['view'], $my_messaging_settings["folder"][$sms_data['view']][$x], $query_time_sort);
				$open = folder_openclose($sms_show['folder'][$sms_data['view']], $my_messaging_settings["folder"][$sms_data['view']][$x]);
				if ($sms_data['tmp']['move_to_folder'] && $open == "close") {
					$picture = "move.gif";
					$link = $PHP_SELF."?move_folder=".$my_messaging_settings["folder"][$sms_data['view']][$x];
				} else {
					$picture = showfoldericon($my_messaging_settings["folder"][$sms_data['view']][$x], $count);
				}
				if (!$sms_data['tmp']['move_to_folder']) {
					$link = folder_makelink($my_messaging_settings["folder"][$sms_data['view']][$x]);
					$link_add = "&cmd_show=openall";
				}
				$titel = "<a href=\"".$link."\" class=\"tree\" >".$my_messaging_settings["folder"][$sms_data['view']][$x]."</a>";
				$zusatz = show_nachrichtencount($count, $count_timefilter);
				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
				printhead(0, 0, $link, $open, FALSE, "<a href=\"".$link.$link_add."\"><img src=\"pictures/".$picture."\" border=0></a>", $titel, $zusatz);
				echo "</tr></table>	";
				$content_content = _("Ordner:")."&nbsp;".$sms_show['folder'][$sms_data['view']]."<br>";
				if ($open == "open") {
					$content_content = "<div align=\"center\">"._("Ordneroptionen:")."
						<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
							<input type=\"hidden\" name=\"delete_folder\" value=\"".$my_messaging_settings["folder"][$sms_data['view']][$x]."\">
							<input type=\"image\" name=\"delete_folder_button\" border=\"0\" ".makeButton("loeschen", "src")." value=\"a\" align=\"absmiddle\">
						</form>
						<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
							<input type=\"hidden\" name=\"cmd\" value=\"admin_folder\">
							<input type=\"hidden\" name=\"ren_folder\" value=\"".$my_messaging_settings["folder"][$sms_data['view']][$x]."\">
							<input type=\"image\" name=\"x\" border=\"0\" ".makeButton("unregelmaessig", "src")." value=\"a\" align=\"absmiddle\">
						</form>
						<br><img src=\"pictures/blank.gif\" height=\"5\"><br>"._("markierte Nachrichten:")."
						<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
							<input type=\"hidden\" name=\"cmd\" value=\"select_all\">
							<input type=\"image\" name=\"select\" border=\"0\" ".makeButton("alleauswaehlen", "src")." value=\"loeschen\" align=\"absmiddle\">
							</form>
							<form action=\"".$PHP_SELF."\" method=\"post\" style=\"display: inline\">
							<input type=\"image\" name=\"delete_selected_button\" border=\"0\" ".makeButton("loeschen", "src")." value=\"delete_selected\" align=\"absmiddle\">
							<input type=\"image\" name=\"move_selected_button\" border=\"0\" ".makeButton("verschieben", "src")." value=\"move\" align=\"absmiddle\"><br></div>";
					echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\">\n\t<tr>";
					if ($count_timefilter != "0") {
						echo "\n\t<td class=\"blank\" background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/blank.gif\" height=\"100%\" width=\"10px\"></td>\n";
					}
					printcontent("99%",0, $content_content, FALSE);
					echo "</tr></table>	";		
				}
				if (folder_openclose($sms_show['folder'][$sms_data['view']], $my_messaging_settings["folder"][$sms_data['view']][$x]) == "open") print_messages();
			}	
		} 
		print("</form>");

 ?>
	</td>
	<td class="blank" width="270" align="right" valign="top"> <?
	
		// start infobox //
		$time_by_links = ""; // build infobox_content > viewfilter
		#$time_by_links .= _("Sie k&ouml;nnen die Anzeige der Nachrichten zeitlich eingrenzen.")."<br>";
		$time_by_links .= "<a href=\"".$PHP_SELF."?sms_time=new\"><img src=\"pictures/".show_icon($sms_data["time"], "new")."\" width=\"8\" border=\"0\">&nbsp;"._("neue Nachrichten")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br>";
		$time_by_links .= "<a href=\"".$PHP_SELF."?sms_time=all\"><img src=\"pictures/".show_icon($sms_data["time"], "all")."\" width=\"8\" border=\"0\">&nbsp;"._("alle Nachrichten")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br>";
		$time_by_links .= "<a href=\"".$PHP_SELF."?sms_time=24h\"><img src=\"pictures/".show_icon($sms_data["time"], "24h")."\" width=\"8\" border=\"0\">&nbsp;"._("letzte 24 Stunden")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br>";
		$time_by_links .= "<a href=\"".$PHP_SELF."?sms_time=7d\"><img src=\"pictures/".show_icon($sms_data["time"], "7d")."\" width=\"8\" border=\"0\">&nbsp;"._("letzte 7 Tage")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br>";
		$time_by_links .= "<a href=\"".$PHP_SELF."?sms_time=30d\"><img src=\"pictures/".show_icon($sms_data["time"], "30d")."\" width=\"8\" border=\"0\">&nbsp;"._("letzte 30 Tage")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br>";
		$time_by_links .= "<a href=\"".$PHP_SELF."?sms_time=older\"><img src=\"pictures/".show_icon($sms_data["time"], "older")."\" width=\"8\" border=\"0\">&nbsp;"._("&auml;lter als 30 Tage")."</a>";

		if ($SessSemName[0] && $SessSemName["class"] == "inst") {
			$tmp_array_1 = array("kategorie" => _("Zur&uuml;ck:"),"eintrag" => array(array("icon" => "pictures/ausruf_small.gif", "text" => "<a href=\"institut_main.php\">"._("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung")."</a>")));
		} else if ($SessSemName[0]) {
			$tmp_array_1 = array("kategorie" => _("Zur&uuml;ck:"),"eintrag" => array(array("icon" => "pictures/ausruf_small.gif", "text" => "<a href=\"seminar_main.php\">"._("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung")."</a>")));
		}

		$infobox = array($tmp_array_1,
			array("kategorie" => _("Information:"),"eintrag" => array(
				array("icon" => "pictures/ausruf_small.gif", "text" => sprintf(_("Sie haben %s empfangene und %s gesendete Nachrichten."), count_rec_messages_from_user($user->id), count_snd_messages_from_user($user->id))))),
			array("kategorie" => _("Nachrichten filtern:"),"eintrag" => array(
				array("icon" => "pictures/suchen.gif", "text" => $time_by_links))),
			array("kategorie" => _("Optionen:"),"eintrag" => array(
				array("icon" => "pictures/blank.gif", "text" => sprintf("<a href=\"%s?cmd_show=openall\">"._("Alle Nachrichten aufklappen")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br><a href=\"%s?cmd=mark_allsmsreaded\">"._("alle als gelesen speichern")."</a>", $PHP_SELF, $PHP_SELF, $PHP_SELF))))		
		);
		print_infobox($infobox,"pictures/sms3.jpg"); ?>
	</td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;</td>
</tr>
</table><?

// Save data back to database.
page_close() ?>

</body>
</html>
