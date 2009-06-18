<?php
# Lifter002: TEST
# Lifter005: TEST
# Lifter007: TODO
# Lifter003: TODO
/*
studipim.php - Instant Messenger for Studip
Copyright (C) 2001 André Noack <andre.noack@gmx.net>

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

//Instant messenger 2.0:
//Main improvements:
// - ajax-control for smoother feeling
// - you have all the time in the world to read the message
// - receive messages while you are writing some
// - avatars in online-list
// - additional link in writer_form for more settings
// - messages-cache so no message will ever be lost by clicking on a wrong button

// $Id: studipim.php 12072 2009-03-27 21:19:53Z tthelen $

// Known bugs:
// * Umlaute (only if a message with Umlauts is about to be cited)

/**
* Close the actual window if PHPLib shows login screen
* @const CLOSE_ON_LOGIN_SCREEN
*/
define("CLOSE_ON_LOGIN_SCREEN",true);
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once ('lib/seminar_open.php');
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('config.inc.php');
require_once ('lib/messaging.inc.php');
require_once ('lib/sms_functions.inc.php');

//Time in seconds to reload the online-list and incoming messages.
$refresh = 12;

//ajax-commands which will give only small response-messages and not the whole messenger
if ($auth->auth["uid"] != "nobody"){
	$db = DBManager::get();
	$sms= new messaging;
	
	$online = get_users_online($my_messaging_settings["active_time"],'no_title');
		
	//If message should be SENT (via ajax), 
	//this script does this and returns nothing or an exception-message. 
	if ($cmd=="send_msg" AND $msg_rec) {
		if (!$nu_msg)
			exit;
		else
			$nu_msg = utf8_decode($nu_msg);
		$nu_msg=trim($nu_msg);
		if (!$msg_subject) 
			$msg_subject = _("Ohne Betreff");
		else
			$msg_subject = utf8_decode($msg_subject);
		if ($sms->insert_message ($nu_msg, $msg_rec, FALSE, FALSE, FALSE, FALSE, FALSE, $msg_subject))
			print '';
		else
			_("Ihre Nachricht konnte nicht verschickt werden!");
		page_close();
		exit; //just ajax-response
	}
	
		
	//Preparations for reading and receiving messages
	$old_msg = count_messages_from_user('in', " AND message_user.readed = 1 ");
	$new_msg = count_messages_from_user('in', " AND message_user.readed = 0 ");
	$new_msgs = array();
			
	if ($new_msg) {
		//load the data from new messages
		$query =  "SELECT message.message_id, message.mkdate, autor_id, message, subject
		FROM message_user LEFT JOIN message USING (message_id)
		WHERE deleted = 0 AND message_user.readed = 0 AND snd_rec = 'rec' AND message_user.user_id ='".$user->id."'
		ORDER BY message.mkdate";
		$result = $db->query($query)->fetchAll();
		foreach ($result as $row) {
			if ($cmd=="read" && $msg_id==$row["message_id"]) {
				// "open" the message (display it in the messenger)
				$msg_text = $row["message"];
				if (strpos($row["message"],$sms->sig_string))
					$sms_reply_text = utf8_encode(quotes_encode(substr($row["message"], 0, strpos($row["message"],$sms->sig_string),get_fullname($msg_autor_id))))."\n";
				else
					$sms_reply_text = utf8_encode(quotes_encode($row["message"],get_fullname($msg_autor_id)))."\n";
				$msg_snd = get_username($row["autor_id"]);
				$msg_autor_id = $row["autor_id"];
				$msg_subject = $row["subject"];
			}
			if ($row["autor_id"] == "____%system%____") {
				$new_msgs[]=date("H:i",$row["mkdate"]) . sprintf(_(" <b>Systemnachricht</b> %s[lesen]%s"),"<a href='Javascript: studipim.read(\"".$row["message_id"]."\")'>","</a>");
			} else {
				$new_msgs[]=date("H:i",$row["mkdate"]). sprintf(_(" von <b>%s</b> %s[lesen]%s"),get_fullname($row["autor_id"],'full',true),"<a href='Javascript: studipim.read(\"".$row["message_id"]."\")'>","</a>");
			}
		}
	}
	
		
	//Ask for ONLINE-LIST
	if ($cmd == "receive_onlinelist") {
		$template = $GLOBALS['template_factory']->open('studipim_onlinelist');
		$template->set_attribute('GLOBALS', $GLOBALS);
		$template->set_attribute('online', $online);
		$template->set_attribute('my_messaging_settings', $my_messaging_settings);
		$template->set_attribute('new_msg', $new_msg);
		$template->set_attribute('old_msg', $old_msg);
		$template->set_attribute('new_msgs', $new_msgs);
		echo $template->render();
		page_close();
		exit;
	}
	
	
	//READ a message
	if (($cmd == "read") && ($msg_id)) {
		
		if ($msg_text) {
			$template = $GLOBALS['template_factory']->open('studipim_read_it');
			$template->set_attribute('msg_text', $msg_text);
			$template->set_attribute('msg_autor_id', $msg_autor_id);
			$template->set_attribute('msg_snd', $msg_snd);
			$template->set_attribute('msg_subject', $msg_subject);
			$template->set_attribute('sms_reply_text', $sms_reply_text);
			echo $template->render();
		}
		
		$query = sprintf ("UPDATE message_user SET readed = 1 WHERE message_id = '%s' AND user_id ='%s'", $msg_id, $user->id);
		$db->query($query);
		
		page_close();
		exit;
	}
		
}


// Start of Output
$_html_head_title = "Stud.IP IM (" . $auth->auth["uname"] . ")";
$_SESSION['messenger_started'] = true; //html_head should NOT try to open us again!
include ('lib/include/html_head.inc.php'); // Output of html head
?>
<script language="JavaScript">
<!--

<?if ($auth->auth["uid"]=="nobody") echo "close();"; //als nobody ergibt der IM keinen Sinn ?>

var messages_cache_content = new Array();
var messages_cache_to = new Array();
var messages_cache_subject = new Array();

if (typeof studipim == "undefined" || !studipim) {
	var studipim = {};
}
	
studipim.getxmlHttp = function() {
	var xmlHttp;
	try {  // Firefox, Opera 8.0+, Safari
		xmlHttp=new XMLHttpRequest();
	}
	catch (e) {  // Internet Explorer
		try {
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (e) {
			try {
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e) {
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
	return xmlHttp;
}

//opens the writer, saves a formally written message from the writer to the messages_cache
//and loads a probably previously saved message in the writer so the user can keep on writing the old message
studipim.write_to = function(msg_rec, msg_rec_name, msg_subject, reply_text) {
	//Opens the messenger_writer
	studipim.close_writer(true);
	document.getElementById("adressat").innerHTML = msg_rec_name;
	document.forms.eingabe.msg_rec.value = msg_rec;
	document.forms.eingabe.msg_subject.value = msg_subject;
	//If the array messages_cache has already a message saved to the user, it will be loaded
	var i;
	if (reply_text != '') {
		document.eingabe.nu_msg.value = reply_text;
	} else {
		document.eingabe.nu_msg.value = '';
	}
	for (i=0; i < messages_cache_to.length; i++) {
		if (messages_cache_to[i] == msg_rec) {
			document.eingabe.nu_msg.value += messages_cache_content[i];
			if (document.eingabe.msg_subject.value == '') 
				document.eingabe.msg_subject.value = messages_cache_subject[i];
			messages_cache_content[i] = messages_cache_content[messages_cache_content.length-1];
			messages_cache_content.pop();
			messages_cache_to[i] = messages_cache_to[messages_cache_to.length-1];
			messages_cache_to.pop();
			messages_cache_subject[i] = messages_cache_subject[messages_cache_subject.length-1];
			messages_cache_subject.pop();
		}
	}
	//Now show the writer
	document.getElementById("messenger_writer").style.visibility = "visible";
	document.eingabe.nu_msg.focus();
}

studipim.close_writer = function(leaveviewer) {
	//Deletes the actual messenger_writer content and closes it if message_cache is empty.
	//Otherwise this will open a message from the cache, so the user can continue writing
	document.getElementById("messenger_writer").style.visibility = "collapse";
	//If messenger_writer has already had content in it, it will be saved in the messages_cache.
	if (document.eingabe.nu_msg.value != '') {
	messages_cache_content.push(document.eingabe.nu_msg.value);
		messages_cache_to.push(document.eingabe.msg_rec.value);
		messages_cache_subject.push(document.eingabe.msg_subject.value);
	}
	document.eingabe.nu_msg.value = '';
	if (!leaveviewer)
	  studipim.cleanup();	
}

//loads a message from the server and shows it to the user 
studipim.read = function(msg_id) {
	var xmlHttp = studipim.getxmlHttp();
	xmlHttp.onreadystatechange=function() {
		if(xmlHttp.readyState==4) {
			document.getElementById("readthenews_cell").innerHTML = xmlHttp.responseText;
			//document.getElementById("readthenews").style.visibility = "visible";
		}
	}
	url = 'studipim2.php?cmd=read&msg_id='+msg_id;
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}

//closes the viewer_window
studipim.cleanup = function() {
	document.getElementById("readthenews_cell").innerHTML = '';
	//document.getElementById("readthenews").style.visibility = "collapse";
}

//sends a written message to the receiver
studipim.send = function() {
	//Sends the content of the writer_window
	var xmlHttp = studipim.getxmlHttp();
	var nu_msg = encodeURIComponent(window.document.forms.eingabe.nu_msg.value);
	var msg_rec = window.document.forms.eingabe.msg_rec.value;
	var msg_subject = encodeURIComponent(window.document.forms.eingabe.msg_subject.value);
	xmlHttp.onreadystatechange=function() {
		if(xmlHttp.readyState==4) {
			if (xmlHttp.responseText != '') {
				alert(xmlHttp.responseText);
			} else {
				//Everything worked fine
				document.eingabe.nu_msg.value = '';
				studipim.close_writer(false);
			}
		}
	}
	var params = 'cmd=send_msg&nu_msg='+nu_msg+'&msg_rec='+msg_rec+'&msg_subject='+msg_subject;
	xmlHttp.open("POST",'<?= $PHP_SELF ?>',true);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlHttp.setRequestHeader("Content-length", params.length);
	xmlHttp.setRequestHeader("Connection", "close");
	xmlHttp.send(params);
}

//refreshes the online-list and the incoming messages
studipim.again_and_again = function() {
	var xmlHttp = studipim.getxmlHttp();
	xmlHttp.onreadystatechange=function() {
		if(xmlHttp.readyState==4) {
			document.getElementById("online_list").innerHTML = xmlHttp.responseText;
		}
	}
	url = 'studipim2.php?cmd=receive_onlinelist';
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
	setTimeout('studipim.again_and_again();',<? print($refresh*1000);?>);
}

studipim.coming_home = function(url) {
	if (opener) {
		opener.location.href = url;
		opener.focus();
	} else {
		top.open(url,'');
	}
}

studipim.settings = function() {
	if (opener) {
		document.eingabe.action = opener;
	} else {
		document.eingabe.action = "_blank";
	}
	//document.eingabe.submit();
	var nu_msg = encodeURIComponent(window.document.forms.eingabe.nu_msg.value);
	var msg_rec = window.document.forms.eingabe.msg_rec.value;
	var msg_subject = encodeURIComponent(window.document.forms.eingabe.msg_subject.value);
	var url = 'sms_send.php?cmd=send_msg&add_receiver_button_x=1&add_receiver_button=1&add_receiver[]='+msg_rec+'&messagesubject='+msg_subject+'&message='+nu_msg;
	studipim.coming_home(url);
}


setTimeout('studipim.again_and_again();',<? print($refresh*1000);?>);
<?
if ($new_msgs[0] OR $cmd)  print ("self.focus();\n");
?>
//-->
</script>

<?php
//Ansprechen des Templates und dadurch Ausgabe des Fensters
if ($auth->auth["uid"] != "nobody") {
	$template = $GLOBALS['template_factory']->open('studipim');
	$template->set_attribute('GLOBALS', $GLOBALS);
	$template->set_attribute('online', $online);
	$template->set_attribute('my_messaging_settings', $my_messaging_settings);
	$template->set_attribute('new_msg', $new_msg);
	$template->set_attribute('new_msgs', $new_msgs);
	$template->set_attribute('old_msg', $old_msg);
	$template->set_attribute('username', $auth->auth["uname"]);
	echo $template->render();
}

include ('lib/include/html_end.inc.php');
// Save data back to database.
page_close();
?>
