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

function MessageIcon ($message_hovericon) {
	global $my_messaging_settings, $PHP_SELF, $auth;
	#if ($my_messaging_settings["hover"]==1 AND $auth->auth["jscript"] AND $message["content"]!="" && $message["openclose"]=="close") {
	if ($auth->auth["jscript"] AND $message_hovericon["content"]!="" && $message_hovericon["openclose"]=="close" && $my_messaging_settings["hover"] == "1") {
		$hovericon = "<a href=\"".$message_hovericon['link']."\" "
			."onMouseOver=\"return overlib('"
			.JSReady(quotes_decode($message_hovericon["content"]))
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
	$query = "SELECT DISTINCT message_id 
		FROM message_user 
			WHERE snd_rec = 'rec'
			AND user_id = '".$user_id."' 
			AND deleted = '0'
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

function print_snd_message($mkdate, $message_id, $message, $sms_data_open, $sms_data_view, $dont_delete, $folder) {
	global $n, $LastLogin, $my_messaging_settings, $cmd, $db7, $PHP_SELF, $msging, $cmd_show;	

	// open?!
	if ($sms_data_open == $message_id) {
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($cmd_show == "openall" || $my_messaging_settings["openall"] == "1") {
		$open = "open";
		$link = $PHP_SELF."?mopen=".$message_id."#".$message_id;
	} else {
		$open = "close";
		$link = $PHP_SELF."?mopen=".$message_id."#".$message_id;
	}

	// make message_header
	$x = "0"; // how many receivers are there?
	$query = "SELECT * FROM message_user WHERE message_id = '".$message_id."' AND snd_rec = 'rec'";
	$db7->query($query);
	while ($db7->next_record()) {
		$x = $x+1;
	}
	if ($dont_delete == "1") { // disable the checkbox if message is locked
		$disable = "disabled";
		$tmp_cmd = "open_selected";
		$tmp_picture = "closelock2";
		$tmp_tooltip = tooltip(_("Löschschutz deaktivieren."));
	} else {
		$tmp_cmd = "safe_selected";
		$tmp_picture = "openlock2";
		$tmp_tooltip = tooltip(_("Diese Nachricht nicht löschen."));
	}

	$zusatz = "<font size=-1>";
	if ($x == "1") { // if only one receiver
		$query = "SELECT message_user.* FROM message_user LEFT JOIN auth_user_md5 USING(user_id) WHERE message_user.message_id = '".$message_id."' AND message_user.snd_rec = 'rec'";
		$db7->query($query);
		while ($db7->next_record()) {
			$rec_userid = $db7->f("user_id");
		}
		$zusatz .= sprintf(_("an %s, %s %s"), "</font><a href=\"about.php?username=".get_username($rec_userid)."\"><font size=-1 color=\"#333399\">".get_fullname($rec_userid)."</font></a><font size=-1>", date("d.m.y, H:i",$mkdate), "<a href=\"".$PHP_SELF."?move_to_folder=".$message_id."\"><img src=\"./pictures/cont_folder_sms.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a><a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$message_id."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$message_id."\" ".CheckChecked($cmd, "select_all").">");
	} else if ($x >= "2") { // if more than one receiver
		$zusatz .= sprintf(_("an %s Empf&auml;nger, %s %s"), $x, date("d.m.y, H:i",$mkdate), "<a href=\"".$PHP_SELF."?move_to_folder=".$message_id."\"><img src=\"./pictures/cont_folder_sms.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a><a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$message_id."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$message_id."\" ".CheckChecked($cmd, "select_all").">");
	}
	$zusatz .= "</font>";
	
	if ($open == "open" || $my_messaging_settings["hover"] == "1") {
		// tread content
		if (strpos($message,$msging->sig_string)) {
			$titel = mila(kill_format(substr($message, 0, strpos($message,$msging->sig_string))));
		} else {
			$titel = mila(kill_format($message));
		}
		$content = quotes_decode(formatReady($message));
		if ($x >= "2") { // if more than one receiver add appendix
			$content .= "<br><br>--<br>"._("gesendet an:")."<br>";
			$query = "SELECT message_user.* FROM message_user LEFT JOIN auth_user_md5 USING(user_id) WHERE message_user.message_id = '".$message_id."' AND message_user.snd_rec = 'rec'";
			$db7->query($query);
			while ($db7->next_record()) {
				$content .= "<a href=\"about.php?username=".get_username($db7->f("user_id"))."\"><font size=-1 color=\"#333399\">".get_fullname($db7->f("user_id"))."</font></a>,&nbsp;";
			}
		}

		// buttons
		$edit = "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$message_id."\" ".tooltip(_("Diese Nachricht löschen.")).">".makeButton("loeschen", "img")."</a>&nbsp;";
		$edit .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder=".$message_id."\" ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben.")).">".makeButton("verschieben", "img")."</a><br><br>";
	}

	// mk titel
	if (strlen($titel) >= "50") {
		$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".substr($titel, 0, 30)." ...</a></a>";
	} else {
		$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".$titel."</a></a>";
	}	
	// (hover) icon 
	$message_hovericon['openclose'] = $open;
	$message_hovericon['content'] = $content;
	$message_hovericon['id'] = $message_id;
	$message_hovericon['titel'] = $titel;
	$message_hovericon['link'] = $link;
	$message_hovericon["picture"] = "cont_nachricht.gif";
	$icon = MessageIcon($message_hovericon);
	// print message_header		
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	printhead(0, 0, $link, $open, FALSE, $icon, $titel, $zusatz, $mkdate);
	echo "</tr></table>	";
	// print content
	if (($open == "open") || ($sms_data_open == $message_id)) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";		
	}
	return $n++;
}

// print_rec_message

function print_rec_message($user_id_snd, $mkdate, $message_id, $message, $fullname, $sms_data_open, $read, $dont_delete, $folder) {
	global $n, $LastLogin, $my_messaging_settings, $cmd, $PHP_SELF, $msging, $cmd_show, $sms_show;	
	$uname_snd = get_username($user_id_snd);
	// build
	if ($read != "1" && ($my_messaging_settings["opennew"] == "1" || $sms_show['sort'] != "no")) { // open if unread
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($sms_data_open == $message_id) {
		$open = "open";
		$link = $PHP_SELF."?mclose=TRUE";
	} else if ($cmd_show == "openall" || $my_messaging_settings["openall"] == "1") {
		$open = "open";
		$link = $PHP_SELF."?mopen=".$message_id."#".$message_id;
	} else {
		$open = "close";
		$link = $PHP_SELF."?mopen=".$message_id."#".$message_id;
	}
	if ($read == "1") { // unread=new ... is message new? if new and opened=set readed
		$red = FALSE;
		$picture = "cont_nachricht.gif";
	} else {
		$red = TRUE;
		$picture = "cont_nachricht_rot.gif";
		if ($open == "open") {
			set_read($message_id);		
		}
	}	
	if ($dont_delete == "1") { // disable the checkbox if message is locked
		$disable = "disabled";
		$tmp_cmd = "open_selected";
		$tmp_picture = "closelock2";
		$tmp_tooltip = tooltip(_("Löschschutz deaktivieren."));
	} else {
		$tmp_cmd = "safe_selected";
		$tmp_picture = "openlock";	
		$tmp_tooltip = tooltip(_("Diese Nachricht nicht löschen."));
	}

	if ($user_id_snd == "____%system%____") { // if message from system	
		$zusatz = "<font size=-1>";
		if ($sms_show['sort'] != "snd_rec") {
			$zusatz .= _("automatische Systemnachricht, ");
		}
		$zusatz .= sprintf(_("am %s"), date("d.m.y, H:i", $mkdate));
		$zusatz .= "<a href=\"".$PHP_SELF."?move_to_folder=".$message_id."\"><img src=\"./pictures/cont_folder_sms.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a><a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$message_id."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$message_id."\" ".CheckChecked($cmd, "select_all").">";
		$zusatz .= "</font>";
	} else { // if message from user
		$zusatz = "<font size=-1>";
		if ($sms_show['sort'] != "snd_rec") {
			$zusatz .= sprintf(_("von %s, "), "</font><a href=\"about.php?username=".$uname_snd."\"><font size=-1 color=\"#333399\">".$fullname."</font></a><font size=-1>");
		}
		$zusatz .= sprintf(_("am %s"), date("d.m.y, H:i", $mkdate));
		$zusatz .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder=".$message_id."\"><img src=\"./pictures/cont_folder_sms.gif\" border=0 ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben."))."></a><a href=\"".$PHP_SELF."?cmd=".$tmp_cmd."&sel_lock=".$message_id."\"><img src=\"./pictures/".$tmp_picture.".gif\" border=0 ".$tmp_tooltip."></a><input type=\"checkbox\" name=\"sel_delsms[]\" ".$disable." value=\"".$message_id."\" ".CheckChecked($cmd, "select_all").">";
		$zusatz .= "</font>";			
	}
	if ($open == "open" || $my_messaging_settings["hover"] == "1") {
		// tread message_header and content
		if (strpos($message,$msging->sig_string)) {
			$titel = mila(kill_format(substr($message, 0, strpos($message,$msging->sig_string))));
		} else {
			$titel = mila(kill_format($message));
		}
		if (strlen($titel) >= "50") {
			$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".substr($titel, 0, 30)." ...</a></a>";
		} else {
			$titel = "<a name=".$message_id."><a href=\"$link\" class=\"tree\" >".$titel."</a></a>";
		}	
		$content = quotes_decode(formatReady($message));
		// mk buttons
		$edit = "";
		if ($user_id_snd != "____%system%____") {
			$edit .= "<a href=\"sms_send.php?cmd=write&rec_uname=".$uname_snd."\">".makeButton("antworten", "img")."</a>";
			$edit .= "&nbsp;<a href=\"sms_send.php?cmd=write&quote=".$message_id."&rec_uname=".$uname_snd."\">".makeButton("zitieren", "img")."</a>";
		}
		$edit.= "&nbsp;<a href=\"".$PHP_SELF."?cmd=delete_selected&sel_delsms[1]=".$message_id."\">".makeButton("loeschen", "img")."</a>";
		$edit .= "&nbsp;<a href=\"".$PHP_SELF."?move_to_folder=".$message_id."\" ".tooltip(_("Diese Nachricht in einen frei wählbaren Ordner verschieben.")).">".makeButton("verschieben", "img")."</a><br><br>";
	}
	// (hover) icon 
	$message_hovericon['openclose'] = $open;
	$message_hovericon['content'] = $message;
	$message_hovericon['id'] = $message_id;
	$message_hovericon['titel'] = $titel;
	$message_hovericon['link'] = $link;
	$message_hovericon["picture"] = $picture;
	$icon = MessageIcon($message_hovericon);

	// print message_header
	echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\" class=\"steel1\"><tr>";
	printhead(0, 0, $link, $open, $red, $icon, $titel, $zusatz, $mkdate);
	echo "</tr></table>	";
	// print message content
	if (($open == "open") || ($sms_data_open == $message_id)) {
		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\"><tr>";
		printcontent("99%",0, $content, $edit);
		echo "</tr></table>	";		
	}
	return $n++;
}

function print_messages() {
	global $user, $my_messaging_settings, $PHP_SELF,$sms_data, $sms_show, $db, $query_showfolder, $query_time_sort, $query_movetofolder, $query_time, $_fullname_sql, $srch_result, $no_message_text, $n;
	$n = 0;
	if ($sms_data['view'] == "in") { // postbox in
		if ($sms_show['sort'] == "snd_rec") { // wenn nach absender sortieren
			$query = "SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time_sort." GROUP BY message.autor_id ORDER BY auth_user_md5.Nachname ASC";
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
				$db->query("SELECT message.*, message_user.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'rec' AND message.autor_id = '".$tmp_rec_snd[$x]."' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time);		
				while ($db->next_record()) { // die messages des jeweiligen absenders anzeigen
					$tmp_x = "1";	
					print_rec_message($db->f("autor_id"), $db->f("mkdate"), $db->f("message_id"), $db->f("message"), get_fullname($db->f("autor_id")), $sms_data["open"], $db->f("readed"), $db->f("dont_delete"), $db->f("folder"));	
				}
			}
		} else { // nicht nach absender sortieren	
			$db->query("SELECT message.*, message_user.*, ".$_fullname_sql['full']." AS fullname FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) LEFT JOIN user_info USING(user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time);		
			while ($db->next_record()) {
				print_rec_message($db->f("autor_id"), $db->f("mkdate"), $db->f("message_id"), $db->f("message"), get_fullname($db->f("autor_id")), $sms_data["open"], $db->f("readed"), $db->f("dont_delete"), $db->f("folder"));	
			}
		}
	} else if ($sms_data['view'] == "out") { // postbox out
		$db->query("SELECT message.*, message_user.* FROM message_user LEFT JOIN message USING (message_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'snd' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_showfolder." ".$query_time);
		while ($db->next_record()) {
			print_snd_message($db->f("mkdate"), $db->f("message_id"), $db->f("message"), $sms_data["open"], $sms_data["view"], $db->f("dont_delete"), $db->f("folder"));		
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
	global $user, $my_messaging_settings, $PHP_SELF, $sms_data, $sms_show, $db, $query_showfolder, $query_time_sort, $query_movetofolder, $query_time, $_fullname_sql, $srch_result, $no_message_text, $n;
		if ($sms_show['sort'] == "snd_rec") { // wenn nach absender sortieren
			$query = "SELECT message.*, message_user.*, auth_user_md5.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.readed = '0' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_time_sort." GROUP BY message.autor_id ORDER BY auth_user_md5.Nachname ASC";
			$db->query($query);		
			while ($db->next_record()) { // die verschiednen absender heraussuchen
				$tmp_rec_snd[] = $db->f("autor_id");	
			}
			for ($x=0; $x < sizeof($tmp_rec_snd); $x++) { // die gefundenen absender durchgehen ?>
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr><td width="1px" class="blank"></td><td width="99%" class="printhead"> 
				<img src="./pictures/nutzer2.gif"><img src="./pictures/blank.gif" height="23" width="1"><font size="-1"> <?
				if ($tmp_rec_snd[$x] == "____%system%____") {
					echo _("Stud.IP - Systemnachricht");
				} else {
					echo get_fullname($tmp_rec_snd[$x]);
				}
				echo "</font></td><td width=\"1px\" class=\"blank\"></td></tr>";
				echo "</table>	";		
				$db->query("SELECT message.*, message_user.* FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.snd_rec = 'rec' AND message_user.readed = '0' AND message.autor_id = '".$tmp_rec_snd[$x]."' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_time);		
				while ($db->next_record()) { // die messages des jeweiligen absenders anzeigen
					$tmp_x = "1";	
					print_rec_message($db->f("autor_id"), $db->f("mkdate"), $db->f("message_id"), $db->f("message"), get_fullname($db->f("autor_id")), $sms_data["open"], $db->f("readed"), $db->f("dont_delete"), $db->f("folder"));	
				}
			}
		} else { // nicht nach absender sortieren	
			$db->query("SELECT message.*, message_user.*, ".$_fullname_sql['full']." AS fullname FROM message_user LEFT JOIN message USING (message_id) LEFT JOIN auth_user_md5 ON (autor_id=auth_user_md5.user_id) LEFT JOIN user_info USING(user_id) WHERE message_user.user_id = '".$user->id."' AND message_user.readed = '0' AND message_user.snd_rec = 'rec' AND message_user.deleted = '0' ".$query_movetofolder." ".$query_time);		
			while ($db->next_record()) {
				print_rec_message($db->f("autor_id"), $db->f("mkdate"), $db->f("message_id"), $db->f("message"), get_fullname($db->f("autor_id")), $sms_data["open"], $db->f("readed"), $db->f("dont_delete"), $db->f("folder"));	
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

if ($cmd_sort) { // sortierung
	$sms_show['sort'] = $cmd_sort;
} else if (empty($sms_show['sort'])) {
	$sms_show['sort'] = "no";
}

if (empty($my_messaging_settings["timefilter"])) { // set timefilter
	$my_messaging_settings["timefilter"] ="all";
}

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
} else if ($mopen) {
	$sms_data["open"] = $mopen;
}

if ($sms_inout) {
	$sms_data["view"] = $sms_inout;
} else if ($sms_data["view"] == "") {
	$sms_data["view"] = "in";
}

if ($show_folder) { // set choosen folder
	if ($show_folder == "close") {
		$sms_show['folder'][$sms_data['view']] = "close";
	} else {
		$sms_show['folder'][$sms_data['view']] = $show_folder;
	}
}

if (!empty($new_folder) && $new_folder_button_x) {
	$my_messaging_settings["folder"][$in_out] = array_add_value($new_folder, $my_messaging_settings["folder"][$in_out]);
}


if (!empty($delete_folder) && $delete_folder_button_x) {
	echo "was";
	if ($in_out == "in") {
		$tmp_sndrec = "rec";
	} else {
		$tmp_sndrec = "snd";
	}
	$query = "UPDATE message_user SET folder='' WHERE folder='".$delete_folder."' AND snd_rec='".$tmp_sndrec."'";
	echo $query;
	$db->query($query);
	$my_messaging_settings["folder"][$in_out] = array_delete_value($my_messaging_settings["folder"][$in_out], $delete_folder);
}

if (empty($my_messaging_settings["openall"])) { // openall festlegen
	$my_messaging_settings["openall"] = "2";
}

if ($sms_time) { // zeitfilter festlegen
	$sms_data["time"] = $sms_time;
} else if ($sms_data["time"] == "" && empty($my_messaging_settings["timefilter"])) {
	$sms_data["time"] = "all";
	$my_messaging_settings["timefilter"] = "all";
} else if ($sms_data["time"] == "" && !empty($my_messaging_settings["timefilter"])) {
	$sms_data["time"] = $my_messaging_settings["timefilter"];
}

if ($sms_show['folder'][$sms_data['view']]) { // folder festlegen
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

if ($sms_data['view'] == "in") {
	$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("empfangene systeminterne Nachrichten anzeigen")."</b>";
	$no_message_text_box = _("im Posteingang");
	$tmp_snd_rec = "rec";
} else if ($sms_data['view'] == "out") {
	$info_text_001 = "<img src=\"pictures/nachricht1.gif\" border=\"0\" align=\"texttop\"><b>&nbsp;"._("gesendete systeminterne Nachrichten anzeigen")."</b>";
	$no_message_text_box = _("im Postausgang");
	$tmp_snd_rec = "snd";
}

if ($sel_lock) { // nachricht loesch-schuetzen bzw. diesen aufheben
	if ($cmd == "safe_selected") { // close lock message delete
		$tmp_dont_delete = "1";
	} else if ($cmd == "open_selected") { // open lock message delete
		$tmp_dont_delete = "0";
	}	
	$db->query("UPDATE message_user SET dont_delete='".$tmp_dont_delete."' WHERE user_id='".$user->id."' AND message_id='".$sel_lock."' AND snd_rec='".$tmp_snd_rec."'");
	$tmp_dont_delete = "";
	$tmp_snd_rec = "";
}

if ($move_folder) { // action: nachricht in ordner verschieben
	if ($move_folder == "free") {
		$move_folder = "";
	}
	$query = "UPDATE message_user SET folder='".$move_folder."' WHERE message_id='".$move_to_folder."' AND user_id='".$user->id."' AND snd_rec='".$tmp_snd_rec."'";
	$db->query($query);
	$move_folder = "";
	$move_to_folder = "";
	$tmp_snd_rec = "";
} 

if ($move_to_folder) {
	$sms_data["open"] = $move_to_folder; // nachricht wird geoeffnet
	$query_movetofolder = "AND message.message_id='".$move_to_folder."'"; // es wird nur diese nachricht angezeigt
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
				<td class="blank" align="left" valign="bottom">&nbsp;
					<? if ($cmd != "admin_folder") { ?>
						<form action="<?=$PHP_SELF?>" method="post" style="display: inline">
						<input type="hidden" name="cmd" value="admin_folder">
						<input type="image" name="select" border="0" <?=makeButton("neuerordner", "src")?> value="loeschen">
						</form> <?
					} else if ($cmd == "admin_folder") { ?>
						<form action="<?=$PHP_SELF?>" method="post" style="display: inline">
						<input type="hidden" name="cmd" value="">
						<input type="image" name="select" border="0" <?=makeButton("zurueck", "src")?> value="loeschen">
						</form> <?
					}
						if ($cmd != "admin_folder") {?>
							<form action="<?=$PHP_SELF?>" method="post" style="display: inline">
							<input type="hidden" name="cmd" value="select_all">
							<input type="image" name="select" border="0" <?=makeButton("alleauswaehlen", "src")?> value="loeschen">
							</form>
							<form action="<?=$PHP_SELF?>" method="post" style="display: inline">
							<input type="hidden" name="cmd" value="delete_selected">
							<input type="image" name="kill" border="0" <?=makeButton("markierteloeschen", "src")?> value="loeschen">
					<? } ?>
				</td>
			</tr>
		</table><?
		if ($cmd != "admin_folder") {
			if (!$move_to_folder) { // wenn nicht verschieben
				// neue-nachrichten-ordner
				if ($sms_data['view'] == "in") { // zeige neue-nachrichten-ordner wenn im eingang ...
					$count = count_messages_from_user($sms_data['view'], "AND deleted='0' AND readed='0'"); // neue nahrichten zaehlen
					if ($count >= "1") { // nur zeigen, wenn auch neue nachrichten ...
						$link = folder_makelink("new");
						$titel = "<a href=\"".$link."\" class=\"tree\" >"._("Ungelesene Nachrichten")."&nbsp;(".$count.")</a>";
						echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
						printhead(0, 0, $link, folder_openclose($sms_show['folder'][$sms_data['view']], "new"), FALSE, "<a href=\"".$link."&cmd_show=openall\"><img src=\"pictures/".showfoldericon("new", $count)."\" border=0></a>", $titel, $zusatz);
						echo "</tr></table>";
						if (folder_openclose($sms_show['folder'][$sms_data['view']], "new") == "open") print_new_messages();
					}
				}
				// alle-nachrichten-ordner
				$link = folder_makelink("all");
				$count = count_messages_from_user($sms_data['view'], "AND deleted='0'");
				$titel = "<a href=\"".$link."\" class=\"tree\" >"._("Alle Nachrichten")."&nbsp;(".$count.")</a>";
				$symbol = "<a href=\"".$link."&cmd_show=openall\"><img src=\"pictures/".showfoldericon("all", $count)."\" border=0></a>";
				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
				printhead(0, 0, $link, folder_openclose($sms_show['folder'][$sms_data['view']], "all"), FALSE, $symbol, $titel, $zusatz);
				echo "</tr></table>";
				if (folder_openclose($sms_show['folder'][$sms_data['view']], "all") == "open") print_messages();
			}
			// persoenliche ordner
			if (!empty($my_messaging_settings["folder"][$sms_data['view']])) {
				// unzugeordnete-nachrichten-ordner
				$count = count_messages_from_user($sms_data['view'], "AND deleted='0' AND folder=''");
				$open = folder_openclose($sms_show['folder'][$sms_data['view']], "free");
				if ($move_to_folder && $open == "close") { // wenn in diesen ordner verschiebbar
					$picture = "move.gif";
					$link = $PHP_SELF."?move_folder=free&move_to_folder=".$move_to_folder;
				} else {
					$picture = showfoldericon("free", $count);
				}
				if (!$move_to_folder) {
					$link = folder_makelink("free");
				}
				$symbol = "<a href=\"".$link."&cmd_show=openall\"><img src=\"pictures/".$picture."\" border=0></a>";
				$titel = "<a href=\"".$link."\" class=\"tree\" >"._("Unzugeordnet")."&nbsp;(".$count.")</a>";
				echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
				printhead(0, 0, $link, $open, FALSE, $symbol, $titel, FALSE);
				echo "</tr></table>	";
				if (folder_openclose($sms_show['folder'][$sms_data['view']], "free") == "open") print_messages();
				// persoenliche ordner
				for($x="0";$x<sizeof($my_messaging_settings["folder"][$sms_data['view']]);$x++) {
					$count = count_messages_from_user($sms_data['view'], "AND deleted='0' AND folder='".$my_messaging_settings["folder"][$sms_data['view']][$x]."'");
					$open = folder_openclose($sms_show['folder'][$sms_data['view']], $my_messaging_settings["folder"][$sms_data['view']][$x]);
					if ($move_to_folder && $open == "close") {
						$picture = "move.gif";
						$link = $PHP_SELF."?move_folder=".$my_messaging_settings["folder"][$sms_data['view']][$x]."&move_to_folder=".$move_to_folder;
					} else {
						$picture = showfoldericon($my_messaging_settings["folder"][$sms_data['view']][$x], $count);
					}
					if (!$move_to_folder) {
						$zusatz = "<a href=\"".$PHP_SELF."?delete_folder=".$my_messaging_settings["folder"][$sms_data['view']][$x]."\"><img src=\"pictures/trash2.gif\" border=0></a>";
						$link = folder_makelink($my_messaging_settings["folder"][$sms_data['view']][$x]);
						$link_add = "&cmd_show=openall";
					}
					$titel = "<a href=\"".$link."\" class=\"tree\" >".$my_messaging_settings["folder"][$sms_data['view']][$x]."&nbsp;(".$count.")</a>";
					echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"99%\" align=\"center\"><tr>";
					printhead(0, 0, $link, $open, FALSE, "<a href=\"".$link.$link_add."\"><img src=\"pictures/".$picture."\" border=0></a>", $titel, $zusatz);
					echo "</tr></table>	";
					if (folder_openclose($sms_show['folder'][$sms_data['view']], $my_messaging_settings["folder"][$sms_data['view']][$x]) == "open") print_messages();
				}	
			} 
			print("</form>");
		} else if ($cmd == "admin_folder") { // ordner verwaltung ?>
			<table border="0" cellpadding="0" cellspacing="0" width="99%" align="center">
				<tr>
					<td class="steelgraudunkel" width="50%"><img src="./pictures/blank.gif" border="0" height="5"><br>&nbsp;&nbsp;<img src="./pictures/cont_folder.gif">&nbsp;<font style="color:#FFFFFF"><b><?=_("Ordnerverwaltung")?></b></font><br><img src="./pictures/blank.gif" border="0" height="5"></td>
					<td class="steelgraudunkel">&nbsp;</td>
				</tr><tr>
					<td class="steel1" width="50%" colspan="2"><img src="./pictures/blank.gif" border="0" height="5"><br>&nbsp;&nbsp;<font size="-1"><?=_("Verwalten Sie hier die persönlichen Ordner für empfangene und gesendete Nachrichten.")?></font><br><img src="./pictures/blank.gif" border="0" height="5"></td>
					
				</tr><tr>
					<td class="steelgraulight" style="border-right:1px dotted black;"><img src="./pictures/blank.gif" border="0" height="3"><br>&nbsp;&nbsp;<font size="-1"><b><?=_("Posteingang")?></b></font><br><img src="./pictures/blank.gif" border="0" height="3"></td>
					<td class="steelgraulight"><img src="./pictures/blank.gif" border="0" height="3"><br>&nbsp;&nbsp;<font size="-1"><b><?=_("Postausgang")?></b></font><br><img src="./pictures/blank.gif" border="0" height="3"></td>
				</tr><tr>
					<?=folder_verwaltung("in");?>
					<?=folder_verwaltung("out");?>
				</tr>
			</table> 
		<?	} ?>
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

		if ($sms_data['view'] == "in") {
			$sort_by_links = ""; // build infobox_content > viewsort
			#$sort_by_links .= _("Sie können die Nachrichten nach Absender sortieren.")."<br>";
			$sort_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?cmd_sort=no\"><img src=\"pictures/".show_icon($sms_show['sort'], "no")."\" width=\"8\" border=\"0\">&nbsp;"._("nur Anzeigefilter")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br>";
			$sort_by_links .= "&nbsp;<a href=\"".$PHP_SELF."?cmd_sort=snd_rec\"><img src=\"pictures/".show_icon($sms_show['sort'], "snd_rec")."\" width=\"8\" border=\"0\">&nbsp;"._("nach Absender sortieren")."</a>";	
		} else {
			$sort_by_links = _("Keine Sortierung im Postausgang möglich.");
		}

		if ($SessSemName[0] && $SessSemName["class"] == "inst") {
			$tmp_array_1 = array("kategorie" => _("Zur&uuml;ck:"),"eintrag" => array(array("icon" => "pictures/ausruf_small.gif", "text" => "<a href=\"institut_main.php\">"._("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung")."</a>")));
		} else if ($SessSemName[0]) {
			$tmp_array_1 = array("kategorie" => _("Zur&uuml;ck:"),"eintrag" => array(array("icon" => "pictures/ausruf_small.gif", "text" => "<a href=\"seminar_main.php\">"._("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung")."</a>")));
		}

		$infobox = array($tmp_array_1,
			array("kategorie" => _("Information:"),"eintrag" => array(
				array("icon" => "pictures/ausruf_small.gif", "text" => sprintf(_("Sie haben %s empfangene und %s gesendete Nachrichten."), count_rec_messages_from_user($user->id), count_snd_messages_from_user($user->id))))),
			array("kategorie" => _("Anzeigesortierung:"),"eintrag" => array(
				array("icon" => "pictures/suchen.gif", "text" => $sort_by_links))),
			array("kategorie" => _("Nachrichten filtern:"),"eintrag" => array(
				array("icon" => "pictures/suchen.gif", "text" => $time_by_links))),
			array("kategorie" => _("Optionen:"),"eintrag" => array(
				array("icon" => "pictures/blank.gif", "text" => sprintf("<a href=\"%s?cmd_show=openall\">"._("Alle Nachrichten aufklappen")."</a><br><img src=\"./pictures/blank.gif\" border=\"0\" height=\"2\"><br><a href=\"%s?cmd=admin_folder\">"._("Ordnerverwaltung")."</a>", $PHP_SELF, $PHP_SELF))))		
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
