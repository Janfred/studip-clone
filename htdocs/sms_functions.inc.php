<?

/*
sms_functions.inc.php -
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

function MessageIcon ($message_hovericon) {
	global $my_messaging_settings, $PHP_SELF, $auth;
	if ($auth->auth["jscript"] AND $message_hovericon["content"]!="" && $message_hovericon["openclose"]=="close" && $my_messaging_settings["hover"] == "1") {
		/*
		$hovericon = "<a href=\"".$message_hovericon['link']."\" "
			."onMouseOver=\"return overlib('"
			.JSReady($message_hovericon["content"])
			."', CAPTION, '&nbsp;"
			.JSReady($message_hovericon["titel"])
			."', NOCLOSE, CSSOFF)\" "
			." onMouseOut=\"nd();\"><img src=\"pictures/".$message_hovericon["picture"]."\" border=0></a>"; */
		$hovericon = "<a href=\"javascript:void(0);\" "
			."onMouseOver=\"return overlib('"
			.JSReady($message_hovericon["content"], "forum")
			."', CAPTION, '&nbsp;"
			.JSReady($message_hovericon["titel"])
			."', NOCLOSE, CSSOFF)\" "
			." onMouseOut=\"nd();\"><img src=\"pictures/".$message_hovericon["picture"]."\" border=0></a>";
	} else {
		$hovericon = "<a href=\"".$message_hovericon['link']."\"><img src=\"pictures/cont_nachricht.gif\" border=0></a>";	
	}
	return $hovericon;
}

// functions
function count_snd_messages_from_user($user_id, $where="") {
	global $db;
	$x = "0";
	$query = "SELECT DISTINCT message_id 
		FROM message_user 
			WHERE snd_rec = 'snd'
			AND user_id = '".$user_id."' 
			AND deleted = '0' ".$where;
	$db->query($query);
	while ($db->next_record()) {
		$x = $x+1;
	}
	return $x;
}

function count_rec_messages_from_user($user_id, $where="") {
	global $db;
	$x = "0";
	if (!$time) {
		$query = "SELECT DISTINCT message_id 
			FROM message_user 
				WHERE snd_rec = 'rec'
				AND user_id = '".$user_id."' 
				".$where;	
	}
	$db->query($query);
	while ($db->next_record()) {
		$x = $x+1;
	}
	return $x;
}

function count_x_messages_from_user($snd_rec, $folder, $where="") {
	global $db, $user;
	if ($snd_rec == "in" || $snd_rec == "out") {
		if ($snd_rec == "in") {
			$tmp_snd_rec = "rec";
		} else {
			$tmp_snd_rec = "snd";
		}
	} else {
		$tmp_snd_rec = $snd_rec;
	}
	if (!$uer_id) {
		$user_id = $user->id;
	}
	$x = "0";
	if ($folder == "all") {
		$folder_query = "";
	} else {
		$folder_query = " AND message_user.folder = '".$folder."'";
	}
	$query = "SELECT message_user.message_id, message.mkdate
		FROM message_user 
			LEFT JOIN message using(message_id)
		WHERE message_user.snd_rec = '".$tmp_snd_rec."'
			AND message_user.user_id = '".$user_id."' 
			AND message_user.deleted = '0'
			".$folder_query."
			".$where;
	$db->query($query);
	while ($db->next_record()) {
		$x = $x+1;
	}
	return $x;
}

function count_messages_from_user($snd_rec, $where="") {
	global $db, $user;
	if ($snd_rec == "in" || $snd_rec == "out") {
		if ($snd_rec == "in") {
			$tmp_snd_rec = "rec";
		} else {
			$tmp_snd_rec = "snd";
		}
	} else {
		$tmp_snd_rec = $snd_rec;
	}
	if (!$uer_id) {
		$user_id = $user->id;
	}
	$x = "0";
	$query = "SELECT DISTINCT message_id
		FROM message_user 
		WHERE snd_rec = '".$tmp_snd_rec."'
			AND user_id = '".$user_id."' 
			AND deleted = '0'
			".$where;
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

function show_icon($sms_show, $value) {
	if ($sms_show == $value) {
		$x = "forum_indikator_gelb2.gif";
	} else {
		$x = "blank.gif";
	}
	return $x;
}

function showfoldericon($tmp, $count) {
	global $sms_show, $sms_data;
	if ($count == "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "close") {
		$picture = "cont_folder2.gif";
	} else if ($count == "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "open") {
		$picture = "cont_folder4.gif";
	} else if ($count != "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "close") {
		$picture = "cont_folder.gif";
	} else if ($count != "0" && folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "open") {
		$picture = "cont_folder3.gif";
	}
	return $picture;
}

function folder_makelink($tmp) {
	global $sms_show, $sms_data;
	if (folder_openclose($sms_show['folder'][$sms_data['view']], $tmp) == "open") {
		$link = $PHP_SELF."?show_folder=close";
	} else {
		$link = $PHP_SELF."?show_folder=".$tmp;
	}
	return $link;
}

function folder_openclose($folder, $x) {
	if ($folder == $x) {
		$tmp = "open";
	} else {
		$tmp = "close";
	}
	return $tmp;
}

// print_snd_message
function print_snd_message($psm) {	
	global $n, $LastLogin, $my_messaging_settings, $cmd, $db7, $PHP_SELF, $msging, $cmd_show, $sms_data;	

	// open?!
	if ($sms_data["open"] == $psm['message_id']) {
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($cmd_show == "openall" || $my_messaging_settings["openall"] == "1") {
		$open = "open";
		$link = $PHP_SELF."?mopen=".$psm['message_id']."#".$psm['message_id'];
	} else {
		$open = "close";
		$link = $PHP_SELF."?mopen=".$psm['message_id']."#".$psm['message_id'];
	}

	// make message_header
	$x = "0"; // how many receivers are there?
	$query = "SELECT * FROM message_user WHERE message_id = '".$psm['message_id']."' AND snd_rec = 'rec'";
	$db7->query($query);
	while ($db7->next_record()) {
		$x = $x+1;
	}
	if ($psm['dont_delete'] == "1") { // disable the checkbox if message is locked
		$disable = "disabled";
		$tmp_cmd = "open_selected";
		$tmp_picture = "closelock2";
		$tmp_tooltip = tooltip(_("Löschschutz deaktivieren."));
		$trash = "<img src=\"./pictures/trash2no.gif\" border=0 ".tooltip(_("Diese Nachricht kann momentan nicht gelöscht werden.")).">";
	} else {
		$tmp_cmd = "safe_selected";
		$tmp_picture = "openlock2";
		$tmp_tooltip = tooltip(_("Diese Nachricht nicht löschen."));
		$trash = "<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$psm['message_id']."\"><img src=\"./pictures/trash2.gif\" border=0 ".tooltip(_("Diese Nachricht löschen."))."></a>";
	}

	$zusatz = "<font size=-1>";
	if ($x == "1") { // if only one receiver
		$query = "SELECT message_user.*, auth_user_md5.vorname, auth_user_md5.nachname, auth_user_md5.username FROM message_user LEFT JOIN auth_user_md5 USING(user_id) WHERE message_user.message_id = '".$psm['message_id']."' AND message_user.snd_rec = 'rec'";
		$db7->query($query);
		while ($db7->next_record()) {
			$tmp['rec_userid'] = $db7->f("user_id");
			$tmp['username'] = $db7->f("username");
			$tmp['vorname'] = $db7->f("vorname");
			$tmp['nachname'] = $db7->f("nachname");
		}
		$zusatz .= sprintf(_("an %s, %s"), "</font><a href=\"about.php?username=".$tmp['username']."\"><font size=-1 color=\"#333399\">".$tmp['vorname']."&nbsp;".$tmp['nachname']."</font></a><font size=-1>", date("d.m.y, H:i",$psm['mkdate']));
		$zusatz .= "&nbsp;";
		if ($psm['folder'] != "all") {
			$zusatz .= "<a href=\"".$PHP_SELF."?move_to_folder=".$psm['message_id']."\"><img src=\"./pictures/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a>";
		}
		$zusatz .= "<a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$psm['message_id']."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"./pictures/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$psm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
	} else if ($x >= "2") { // if more than one receiver
		$zusatz .= sprintf(_("an %s Empf&auml;nger, %s"), $x, date("d.m.y, H:i",$psm['mkdate']));
		$zusatz .= "&nbsp;";
		if ($psm['folder'] != "all") {
			$zusatz .= "<a href=\"".$PHP_SELF."?move_to_folder=".$psm['message_id']."\"><img src=\"./pictures/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a>";
		}
		
		$zusatz .= "<a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$psm['message_id']."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"./pictures/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$psm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
	}
	$zusatz .= "</font>";
	
	// tread content
	if (strpos($psm['message'],$msging->sig_string)) {
		$titel = mila(kill_format(substr($psm['message'], 0, strpos($psm['message'],$msging->sig_string))));
	} else {
		$titel = mila(kill_format($psm['message']));
	}
	$content = quotes_decode(formatReady($psm['message']));
	if ($x >= "2") { // if more than one receiver add appendix
		$content .= "<br><br>--<br>"._("gesendet an:")."<br>";
		$query = "SELECT message_user.*, auth_user_md5.username FROM message_user LEFT JOIN auth_user_md5 USING(user_id) WHERE message_user.message_id = '".$psm['message_id']."' AND message_user.snd_rec = 'rec'";
		$db7->query($query);
		while ($db7->next_record()) {
			$content .= "<a href=\"about.php?username=".$db7->f("username")."\"><font size=-1 color=\"#333399\">".get_fullname($db7->f("user_id"))."</font></a>,&nbsp;";
		}
	}

	// buttons
	$edit = "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$psm['message_id']."\" ".tooltip(_("Diese Nachricht löschen.")).">".makeButton("loeschen", "img")."</a>&nbsp;";
	if ($psm['folder'] != "all") {
		$edit .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder=".$psm['message_id']."\" ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben.")).">".makeButton("verschieben", "img")."</a><br><br>";
	}

	$message_hovericon['titel'] = $titel;

	// mk titel
	if (strlen($titel) >= "50") {
		$titel = "<a name=".$psm['message_id']."><a href=\"$link\" class=\"tree\" >".substr($titel, 0, 30)." ...</a></a>";
	} else {
		$titel = "<a name=".$psm['message_id']."><a href=\"$link\" class=\"tree\" >".$titel."</a></a>";
	}	
	// (hover) icon 
	$message_hovericon['openclose'] = $open;
	$message_hovericon['content'] = $psm['message'];
	$message_hovericon['id'] = $psm['message_id'];
	$message_hovericon["picture"] = "cont_nachricht.gif";
	$icon = MessageIcon($message_hovericon);
	// print message_header		
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	echo "<td class=\"blank\"><img src=\"./pictures/";
	if ($psm['count'] == "0") {
		echo "forumstrich2.gif";
	} else {
		echo "forumstrich3.gif";
	}
	echo "\"></td>";	
	printhead(0, 0, $link, $open, FALSE, $icon, $titel, $zusatz, $psm['mkdate']);
	echo "</tr></table>	";
	// print content
	if (($open == "open") || ($psm['sms_data_open'] == $psm['message_id'])) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		echo "<td class=\"blank\"><img src=\"./pictures/forumstrich.gif\" height=\"100%\" width=\"10\"></td>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";		
	}
	return $n++;
}

// print_rec_message
function print_rec_message($prm) {
	global $n, $LastLogin, $my_messaging_settings, $cmd, $PHP_SELF, $msging, $cmd_show, $sms_show, $sms_data;	
	// build
	if ($prm['readed'] != "1" && $my_messaging_settings["opennew"] == "1") { // open if unread
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($sms_data["open"] == $prm['message_id']) {
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($cmd_show == "openall" || $my_messaging_settings["openall"] == "1") {
		$open = "open";
		$link = $PHP_SELF."?mopen=".$prm['message_id']."#".$prm['message_id'];
	} else {
		$open = "close";
		$link = $PHP_SELF."?mopen=".$prm['message_id']."#".$prm['message_id'];
	}
	if ($prm['readed'] == "1") { // unread=new ... is message new? if new and opened=set readed
		$red = FALSE;
		$picture = "cont_nachricht.gif";
	} else {
		$red = TRUE;
		$picture = "cont_nachricht_rot.gif";
		if ($open == "open") {
			set_read($prm['message_id']);		
		}
	}	
	if ($prm['dont_delete'] == "1") { // disable the checkbox if message is locked
		$disable = "disabled";
		$tmp_cmd = "open_selected";
		$tmp_picture = "closelock2";
		$tmp_tooltip = tooltip(_("Löschschutz deaktivieren."));
		$trash = "<img src=\"./pictures/trash2no.gif\" border=0 ".tooltip(_("Diese Nachricht kann momentan nicht gelöscht werden.")).">";
	} else {
		$tmp_cmd = "safe_selected";
		$tmp_picture = "openlock";	
		$tmp_tooltip = tooltip(_("Diese Nachricht nicht löschen."));
		$trash = "<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$prm['message_id']."\"><img src=\"./pictures/trash2.gif\" border=0 ".tooltip(_("Diese Nachricht löschen."))."></a>";
	}

	if ($prm['user_id_snd'] == "____%system%____") { // if message from system	
		$zusatz = "<font size=-1>";
		if ($sms_show['sort'] != "snd_rec") {
			$zusatz .= _("automatische Systemnachricht, ");
		}
		$zusatz .= sprintf(_("am %s"), date("d.m.y, H:i", $prm['mkdate']));
		$zusatz .= "<a href=\"".$PHP_SELF."?move_to_folder=".$prm['message_id']."\"><img src=\"./pictures/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a><a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$prm['message_id']."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"./pictures/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$prm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
		$zusatz .= "</font>";
	} else { // if message from user
		$zusatz = "<font size=-1>";
		if ($sms_show['sort'] != "snd_rec") {
			$zusatz .= sprintf(_("von %s, "), "</font><a href=\"about.php?username=".$prm['uname_snd']."\"><font size=-1 color=\"#333399\">".$prm['vorname']."&nbsp;".$prm['nachname']."</font></a><font size=-1>");
		}
		$zusatz .= sprintf(_("am %s"), date("d.m.y, H:i", $prm['mkdate']));
		$zusatz .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder=".$prm['message_id']."\"><img src=\"./pictures/cont_folder_sms_move.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a><a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$prm['message_id']."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><img src=\"./pictures/blank.gif\" width=\"2\">".$trash."<input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$prm['message_id']."\" ".CheckChecked($cmd, "select_all").">";
		$zusatz .= "</font>";			
	}
	// tread message_header and content
	if (strpos($prm['message'],$msging->sig_string)) {
		$titel = mila(trim(kill_format(substr($prm['message'], 0, strpos($prm['message'],$msging->sig_string)))));
	} else {
		$titel = mila(trim(kill_format($prm['message'])));
	}
	$message_hovericon['titel'] = $titel;
	if (strlen($titel) >= "50") {
		$titel = "<a name=".$prm['message_id']."><a href=\"$link\" class=\"tree\" >".substr($titel, 0, 30)." ...</a></a>";
	} else {
		$titel = "<a name=".$prm['message_id']."><a href=\"$link\" class=\"tree\" >".$titel."</a></a>";
	}	
	$content = quotes_decode(formatReady($prm['message']));
	// mk buttons
	$edit = "";
	if ($prm['user_id_snd'] != "____%system%____") {
		$edit .= "<a href=\"sms_send.php?cmd=write&rec_uname=".$prm['uname_snd']."\">".makeButton("antworten", "img")."</a>";
		$edit .= "&nbsp;<a href=\"sms_send.php?cmd=write&quote=".$prm['message_id']."&rec_uname=".$prm['uname_snd']."\">".makeButton("zitieren", "img")."</a>";
	}
	$edit.= "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$prm['message_id']."\">".makeButton("loeschen", "img")."</a>";
	if ($prm['folder'] != "all") {
		$edit .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder=".$prm['message_id']."\" ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben.")).">".makeButton("verschieben", "img")."</a><br><br>";
	}
	// (hover) icon 
	$message_hovericon['openclose'] = $open;
	$message_hovericon['content'] = $prm['message'];
	$message_hovericon['id'] = $prm['message_id'];
	$message_hovericon['link'] = $link;
	$message_hovericon["picture"] = $picture;
	$icon = MessageIcon($message_hovericon);

	// print message_header
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	echo "<td class=\"blank\"><img src=\"./pictures/";
	if ($prm['count'] <= "0") {
		echo "forumstrich2.gif";
	} else {
		echo "forumstrich3.gif";
	}
	echo "\"></td>";
	printhead(0, 0, $link, $open, $red, $icon, $titel, $zusatz, $prm['mkdate']);
	echo "</tr></table>	";
	// print message content
	if (($open == "open") || ($sms_data["open"] == $prm['message_id'])) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
		echo "<td class=\"blank\"><img src=\"./pictures/forumstrich.gif\" height=\"100%\" width=\"10\"></td>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";		
	}
	return $n++;
}

function print_messages() {
	global $user, $my_messaging_settings, $PHP_SELF,$sms_data, $sms_show, $db, $query_showfolder, $query_time_sort, $query_movetofolder, $query_time, $_fullname_sql, $srch_result, $no_message_text, $n, $count, $count_timefilter;
	if ($query_time) {
		$count = $count_timefilter;
	}
	$n = 0;
	$user_id = $user->id;
	if ($sms_data['view'] == "in") { // postbox in
		if ($sms_show['sort'] == "snd_rec") { // wenn nach absender sortieren
			$query = "SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user_id."' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time_sort." GROUP BY message.autor_id ORDER BY auth_user_md5.Nachname ASC";
			$db->query($query);		
			while ($db->next_record()) { // die verschiednen absender heraussuchen
				$tmp_rec_snd[] = $db->f("autor_id");	
			}
			for ($x=0; $x < sizeof($tmp_rec_snd); $x++) { // die gefundenen absender durchgehen ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr><td width="1px" class="blank"></td><td width="99%" class="printhead"> 
				<img src="./pictures/nutzer2.gif"><img src="./pictures/blank.gif" height="18" width="1"><font size="-1"><b><?
				if ($tmp_rec_snd[$x] == "____%system%____") {
					echo _("Stud.IP - Systemnachricht");
				} else {
					echo get_fullname($tmp_rec_snd[$x]);
				}
				echo "</b></font></td><td width=\"1px\" class=\"blank\"></td></tr>";
				echo "</table>	";		
				$db->query("SELECT message.*, message_user.*, auth_user_md5.*  FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user_id."' AND message_user.snd_rec = 'rec' AND message.autor_id = '".$tmp_rec_snd[$x]."' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time);		
				while ($db->next_record()) { // die messages des jeweiligen absenders anzeigen
					$tmp_x = "1";
					$count = ($count-1);
					$prm['count'] = $count;	
					$prm['folder'] = $my_messaging_settings['folder']['active']['in'];	
					$prm['user_id_snd'] = $db->f("autor_id");
					$prm['mkdate'] = $db->f("mkdate");
					$prm['message_id'] = $db->f("message_id");
					$prm['message'] = $db->f("message");
					$prm['readed'] = $db->f("readed");
					$prm['dont_delete'] = $db->f("dont_delete");
					$prm['uname_snd'] = $db->f("username");	
					print_rec_message($prm);
				}
			}
		} else { // nicht nach absender sortieren	
			$db->query("SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) LEFT JOIN user_info USING(user_id) WHERE message_user.user_id = '".$user_id."' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time);		
			while ($db->next_record()) {
				$count = ($count-1);
				$prm['count'] = $count;
				$prm['user_id_snd'] = $db->f("autor_id");
				$prm['folder'] = $my_messaging_settings['folder']['active']['in'];	
				$prm['mkdate'] = $db->f("mkdate");
				$prm['message_id'] = $db->f("message_id");
				$prm['message'] = $db->f("message");
				$prm['vorname'] = $db->f("Vorname");
				$prm['nachname'] = $db->f("Nachname");
				$prm['readed'] = $db->f("readed");
				$prm['dont_delete'] = $db->f("dont_delete");
				$prm['uname_snd'] = $db->f("username");
				print_rec_message($prm);
			}
		}
	} else if ($sms_data['view'] == "out") { // postbox out
		$db->query("SELECT message.*, message_user.* FROM message_user LEFT JOIN message USING (message_id) WHERE message_user.user_id = '".$user_id."' AND message_user.snd_rec = 'snd' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time);
		while ($db->next_record()) {
			$count = ($count-1);
			$psm['count'] = $count;
			$psm['mkdate'] = $db->f("mkdate");
			$psm['folder'] = $my_messaging_settings['folder']['active']['out'];	
			$psm['message_id'] = $db->f("message_id");
			$psm['message'] = $db->f("message");
			$psm['dont_delete'] = $db->f("dont_delete");
			print_snd_message($psm);	
		}	
	}	
	if (!$n) { // wenn keine nachrichten zum anzeigen
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\">";
		$srch_result = "info§<font size=-1><b>".$no_message_text."</b></font>";
		parse_msg ($srch_result, "§", "steel1", 2, FALSE);
		echo "</td></tr></table>";
	}
}

function print_new_messages() {
	global $user, $my_messaging_settings, $PHP_SELF, $sms_data, $sms_show, $db, $query_showfolder, $query_time_sort, $query_movetofolder, $query_time, $_fullname_sql, $srch_result, $no_message_text, $n, $count;
		if ($sms_show['sort'] == "snd_rec") { // wenn nach absender sortieren
			$query = "SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.readed = '0' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' GROUP BY message.autor_id ORDER BY auth_user_md5.Nachname ASC";
			$db->query($query);		
			while ($db->next_record()) { // die verschiednen absender heraussuchen
				$tmp_rec_snd[] = $db->f("autor_id");	
			}
			for ($x=0; $x < sizeof($tmp_rec_snd); $x++) { // die gefundenen absender durchgehen ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr><td width="1px" class="blank"></td><td width="99%" class="printhead"> 
				<img src="./pictures/nutzer2.gif"><img src="./pictures/blank.gif" height="23" width="1"><font size="-1"> <?
				if ($tmp_rec_snd[$x] == "____%system%____") {
					echo _("Systemnachricht");
				} else {
					echo get_fullname($tmp_rec_snd[$x]);
				}
				echo "</font></td><td width=\"1px\" class=\"blank\"></td></tr>";
				echo "</table>	";		
				$db->query("SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'rec' AND message_user.readed = '0' AND message.autor_id = '".$tmp_rec_snd[$x]."' AND message_user.deleted = '0'");		
				while ($db->next_record()) { // die messages des jeweiligen absenders anzeigen
					$tmp_x = "1";	
					$count = ($count-1);
					$prm['count'] = $count;					
					$prm['user_id_snd'] = $db->f("autor_id");
					$prm['mkdate'] = $db->f("mkdate");
					$prm['message_id'] = $db->f("message_id");
					$prm['message'] = $db->f("message");
					$prm['readed'] = $db->f("readed");
					$prm['dont_delete'] = $db->f("dont_delete");
					$prm['uname_snd'] = $db->f("username");
					print_rec_message($prm);
				}
			}
		} else { // nicht nach absender sortieren	
			$db->query("SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) LEFT JOIN user_info USING(user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.readed = '0' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0'");		
			while ($db->next_record()) {
				$count = ($count-1);
				if ($count == "-1") {
					$prm['count'] == "";
				} else {
					$prm['count'] = $count;	
				}	
				$prm['user_id_snd'] = $db->f("autor_id");
				$prm['mkdate'] = $db->f("mkdate");
				$prm['message_id'] = $db->f("message_id");
				$prm['message'] = $db->f("message");
				$prm['vorname'] = $db->f("Vorname");
				$prm['nachname'] = $db->f("Nachname");
				$prm['readed'] = $db->f("readed");
				$prm['dont_delete'] = $db->f("dont_delete");
				$prm['uname_snd'] = $db->f("username");				
				print_rec_message($prm);
			}
		}
}

function folder_verwaltung($in_out) { 
	global $PHP_SELF, $my_messaging_settings, $sms_data; 
	if ($in_out == "in") $border = "style=\"border-right:1px dotted black;\""; ?>
	<td class="steel1" <?=$border?> valign="top"><font size="-1">
		<form action="<?=$PHP_SELF?>" method="post"> 
		<input type="hidden" name="in_out" value="<?=$in_out?>">
		<img src="./pictures/blank.gif" border="0" height="4"><br>&nbsp;
		<?=_("Neuen Ordner anlegen:")?><br>&nbsp;
		<input type="text" name="new_folder[]" value="" size="30" maxlength="255">
		<input type="image" name="new_folder_button" <?=tooltip(_("Erstellt einen neuen Ordner."))?> src="./pictures/cont_folder_add.gif" border="0"><br><?
		if (!empty($my_messaging_settings['folder'][$in_out])) { ?>
			<img src="./pictures/blank.gif" border="0" height="4"><br>&nbsp;&nbsp;<?=_("Ordner entfernen:") ?><br>&nbsp;
			<select name="delete_folder" style="width:200px">
			<? for($x="0";$x<sizeof($my_messaging_settings['folder'][$in_out]);$x++) {
				printf("<option>".$my_messaging_settings['folder'][$in_out][$x]."</option>");
			} ?>
			</select>&nbsp;
			<input type="image" name="delete_folder_button" <?=tooltip(_("Entfernt den Ordner. Nachrichten aus diesem Ordner werden in \"Unzugeordnet\" verschoben."))?> src="./pictures/trash.gif" border="0">&nbsp;
			<br><img src="./pictures/blank.gif" border="0" height="4"><br>&nbsp;&nbsp;<?=_("Ordner umbennen:") ?><br>&nbsp;
			<select name="ren_folder" style="width:200px">
			<? for($x="0";$x<sizeof($my_messaging_settings['folder'][$in_out]);$x++) {
				printf("<option>".$my_messaging_settings['folder'][$in_out][$x]."</option>");
			} ?>
			</select>
			<input type="image" name="ren_folder_button" <?=tooltip(_("Der ausgewählte Ordner kann umbenannt werden."))?> src="./pictures/rewind3.gif" border="0">
			<? } ?>
		</form> 
	</td><?
}

?>