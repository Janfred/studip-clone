<?php
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

/**
* Close the actual window if PHPLib shows login screen
* @const CLOSE_ON_LOGIN_SCREEN
*/
define("CLOSE_ON_LOGIN_SCREEN",true);
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

require_once ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");

$db = new DB_Seminar;
$sess->register("sidebar_data");
$sms= new messaging;

//Initial time, all msg's from this time will later be counted as new
if (!$sidebar_data["messenger_start"])
	if ($my_messaging_settings["last_visit"] < time())
		$sidebar_data["messenger_start"] = $my_messaging_settings["last_visit"];
	else
		$sidebar_data["messenger_start"] = time();

if ($auth->auth["uid"] !="nobody") {
        ($cmd=="write") ? $refresh=0 : $refresh=30;
        
        $now = time(); // nach eingestellter Zeit (default = 5 Minuten ohne Aktion) zaehlt man als offline

	$query = "SELECT " . $_fullname_sql['full'] . " AS full_name,($now-UNIX_TIMESTAMP(changed)) AS lastaction,a.username,a.user_id,contact_id 
		FROM active_sessions LEFT JOIN auth_user_md5 a ON (a.user_id=sid) LEFT JOIN user_info USING(user_id) 
		LEFT JOIN contact ON (owner_id='".$auth->auth["uid"]."' AND contact.user_id=a.user_id AND buddy=1)
		WHERE changed > '".date("YmdHis",$now - ($my_messaging_settings["active_time"] * 60))."' 
		AND sid != 'nobody' AND sid != '".$auth->auth["uid"]."' 
		AND active_sessions.name = 'Seminar_User' ORDER BY changed DESC";
        
        $db->query($query);
        
        while ($db->next_record()) {
                $online[$db->f("username")] = array("name"=>$db->f("full_name"),"last_action"=>$db->f("lastaction"),"userid"=>$db->f("user_id"),"is_buddy" => $db->f("contact_id"));      
        }
    
        $query =  "SELECT message_id, mkdate, user_id_snd, message, user_id_snd FROM globalmessages WHERE user_id_rec='".$auth->auth["uname"]."'";
        $db->query($query);

        $old_msg = 0;
        $new_msgs = FALSE;
        
        while ($db->next_record()) {
                if ($cmd == "read" AND $msg_nr == $db->f("message_id")) {
                        $msg_text=$db->f("message");
                        $msg_snd=$db->f("user_id_snd");
                        $sidebar_data["read_new_msgs"][$db->f("message_id")]=TRUE;
                }
                
                if (($db->f("mkdate") > $sidebar_data["messenger_start"]) && (!$sidebar_data["read_new_msgs"][$db->f("message_id")])) {
                        //this is a new msg, will be shown as new msg until the user wants to see it
                         if (preg_match("/chat_with_me/i", $db->f("message")) && $online[$db->f("user_id_snd")]) {
                               $new_msgs[] = date("H:i",$db->f("mkdate")). sprintf(_("&nbsp;Chateinladung von <b>%s</b>"),htmlReady(get_fullname_from_uname($db->f("user_id_snd"))));
                        } else {
                                if ($db->f("user_id_snd") == "____%system%____"){
                                        $new_msgs[] = date("H:i",$db->f("mkdate")) . sprintf(_("&nbsp;<b>Systemnachricht</b> %s[lesen]%s"),"<a href='$PHP_SELF?cmd=read&msg_nr=".$db->f("message_id")."'>","</a>");
                                } else {
                                        $new_msgs[] = date("H:i",$db->f("mkdate")). sprintf(_("&nbsp;Nachricht von <br /><b>%s</b> %s[lesen]%s"),htmlReady(get_fullname_from_uname($db->f("user_id_snd"))),"<a href='$PHP_SELF?cmd=read&msg_nr=".$db->f("message_id")."'>","</a>");
                                }
                        }
                        $refresh+=10;
                } elseif (!preg_match("/chat_with_me/i", $db->f("message")))
                	$old_msg++;
        }
}

// Start of Output
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<?
		if ($cmd !="write")
			print "<meta http-equiv=\"REFRESH\" CONTENT=\"$refresh\">";
		?>
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
		<meta name="copyright" content="Stud.IP-Crew (crew@studip.de)">
		<title>Stud.IP Sidebar</title>
		<link rel="stylesheet" href="imstyle.css" type="text/css">
	</head>

	<body background="bathtile.jpg">

<?php
//$_html_head_title = "Stud.IP IM (" . $auth->auth["uname"] . ")";
//include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
?>


<table width="100%" border=0 cellpadding=2 cellspacing=0>
<tr>
        <td class="topic" colspan=2><font size="-1"><b>&nbsp;Stud.IP-Sidebar <br />&nbsp;(<?=$auth->auth["uname"]?>)</b></font></td>
</tr>
<tr>
	<td class="blank" width="100%" ><table width="100%" border=0 cellpadding=1 cellspacing=0 valign="top">
<?php

$c=0;
$owner_id = $user->id;

if (is_array ($online)) { // wenn jemand online ist
	foreach($online as $username=>$value) { //ale durchgehen die online sind
		$user_id = get_userid($username);
		$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id' AND buddy = '1'");	
		if ($db->next_record()) { // er ist auf jeden Fall als Buddy eingetragen
			$buddies[]=array($online[$username]["name"],$online[$username]["last_action"],$username);
		}
	}
}

if ((is_array($online)) && (is_array ($buddies))) {
	printf ("<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1><b>Buddies:</b></td></tr>");					
	while (list($index)=each($buddies)) {
		list($fullname,$zeit,$tmp_online_uname)=$buddies[$index];
		echo "<tr><td class='blank' width='90%' align='left'><font size=-1><a href=\"javascript:coming_home('about.php?username=$tmp_online_uname');\">".$fullname."</a></font></td>\n";
		echo "<td  class='blank' width='10%' align='middle'><font size=-1><a href='$PHP_SELF?cmd=write&msg_rec=$tmp_online_uname'><img src=\"pictures/nachricht1.gif\" ".tooltip("Nachricht an User verschicken")." border=\"0\" width=\"24\" height=\"21\"></a></font></td></tr>";
		$c++;
	}
 } else {
        echo "<tr><td class='blank' colspan='2' align='left'><font size=-1>" . _("Kein Buddy ist online.") . "</font><br />&nbsp; ";
}

if (!$my_messaging_settings["show_only_buddys"]) {
        if ((sizeof($online)-$c) == 1) {
                echo "<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>" . _("Es ist ein anderer Nutzer online.");
                printf (" <a target=\"_content\" href=\"online.php\"><font size=-1>" . _("Wer?") . "</font></a>");                
        }
        elseif((sizeof($online)-$c) > 1) {
                printf ("<tr><td class=\"blank\" colspan=2 align=\"left\"><font size=-1>" . _("Es sind %s anderere Nutzer online.") , sizeof($online)-$c);
                printf (" <a target=\"_content\" href=\"online.php\"><font size=-1>" . _("Wer?") . "</font></a>");                
        }
}
?>
</td></tr></table></td></tr><tr><td class="blank" width="100%" valign="top"><br><font size=-1>
<?
if ($old_msg) 
	printf(_("%s alte Nachricht(en) %s[lesen]%s"),$old_msg,"<a target=\"_content\" href=\"sms.php\">","</a><br>");
elseif (!is_array($sidebar_data["new_msgs"]))
	print (_("Keine Nachrichten") . "<br>");
else
	print (_("Keine alten Nachrichten") . "<br>");

if (is_array($new_msgs)) {
	print ("<br /><b>"._("neue Nachrichten:") . "</b><br />");
	foreach ($new_msgs as $val)
        	print "<br />".$val;
}

?>
</font></td></tr>
<?

if ($cmd=="send_msg" AND $nu_msg AND $msg_rec) {
        $nu_msg=trim($nu_msg);
        if ($sms->insert_sms ($msg_rec, $nu_msg))
                echo"\n<tr><td class=\"blank\" colspan=\"2\" valign=\"middle\"><br /><font size=\"-1\">"
                        . sprintf(_("Ihre Nachricht an <b>%s</b> wurde verschickt!"),get_fullname_from_uname($msg_rec)) . "</font></td></tr>";
        else 
                echo"\n<tr><td class='blank' colspan='2' valign='middle'><font size=-1 color='red'><b>"
                        . _("Ihre Nachricht konnte nicht verschickt werden!") . "</b></font></td></tr>";
}


if ($cmd=="read" AND $msg_text){
        if ($msg_snd == "____%system%____")
                echo"\n<tr><td class='blank' colspan='2' valign=\"middle\"><br /><font size=-1><b>"
                . _("automatisch erzeugte Systemnachricht:") . " </b><hr>".quotes_decode(formatReady($msg_text))."</font></td></tr>";
        else
                echo"\n<tr><td class='blank' colspan='2' valign=\"middle\"><br /><font size=-1>"
                . sprintf(_("Nachricht von: <b>%s</b>"),htmlReady(get_fullname_from_uname($msg_snd))) ."<hr>".quotes_decode(formatReady($msg_text))."</font></td></tr>";
        if ($msg_snd != "____%system%____")
                echo"\n<tr><td class='blank' colspan='2' valign=\"middle\" align='right'><br /><font size=-1><a href='$PHP_SELF?cmd=write&msg_rec=$msg_snd'>"
                . "<img " . makeButton("antworten","src") . tooltip(_("Diese Nachricht direkt beantworten")) . " border=0></a></td></tr>";
}

if ($cmd=="write" AND $msg_rec){
        echo"\n<tr><td class=\"blank\" colspan='2' valign=\"middle\"><br /><font size=-1>"
                . sprintf(_("Ihre Nachricht an <b>%s:</b>"),htmlReady(get_fullname_from_uname($msg_rec))) . "</font></td></tr>";
        echo"\n<FORM  name=\"eingabe\"' action=\"$PHP_SELF?cmd=send_msg\"' method=\"POST\"><INPUT TYPE=\"HIDDEN\"  name=\"msg_rec\" value=\"$msg_rec\"'>";
        echo"\n<tr><td class=\"blank\"' colspan='2' valign=\"middle\"><TEXTAREA  style=\"width: 100%\" name='nu_msg' rows='4' cols='44' wrap='virtual'></TEXTAREA></font><br>";
        //echo "<font size=-1><a target=\"_new\" href=\"show_smiley.php\">" . _("Smileys</a> k&ouml;nnen verwendet werden") . " </font>\n</td></tr>";
        echo"\n<tr><td class=\"blank\" colspan='2' valign=\"middle\" align=\"right\"><center><font size=-1><INPUT TYPE='IMAGE' name='none' "
                . makeButton("absenden","src") . tooltip(_("Nachricht versenden")) . " border=0 value='senden'><br> <a href=\"$PHP_SELF\"><img "
                . makeButton("abbrechen","src") . tooltip(_("Vorgang abbrechen")) . " border=0 /></a></FORM></font></center></td></tr>";
        echo"\n<script language=\"JavaScript\">\n<!--\ndocument.eingabe.nu_msg.focus();\n//-->\n</script>";
}
        ?>
</table>
<?
// Save data back to database.
page_close();
?>
</body>
</html>