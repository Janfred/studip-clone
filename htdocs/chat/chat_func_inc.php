<?
/**
* Chat Functions
* 
*
* @author		André Noack <noack@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	chat_modules
* @module		chat_func_inc
* @package		Chat
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_func_inc.php
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de>
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

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php";
//Studip includes
require_once $ABSOLUTE_PATH_STUDIP."visual.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."messaging.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."functions.php";
require_once $ABSOLUTE_PATH_STUDIP."contact.inc.php";


function chat_get_chat_icon($chatter,$chatinv,$is_active,$as_icon = false){
	if ($GLOBALS['CHAT_ENABLE']){
			$pic_prefix = ($as_icon) ? "icon-" : "";
			$pic_path = $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . "/pictures/";
			$image = "<img border=\"0\" src=\"" . $pic_path . $pic_prefix;
			if (!$chatter){
				$image .= "chat1.gif\"" . tooltip(_("Dieser Chatraum ist leer"));
			} elseif ($chatinv){
				$image .= "chateinladung.gif\"" . tooltip(_("Sie haben eine gültige Einladung für diesen Chatraum") 
					. " " . (($chatter == 1) ? _("Es ist eine Person in diesem Chatraum.") : sprintf(_("Es sind %s Personen in diesem Chatraum"),$chatter)));
			} elseif ($chatter == 1 && $is_active) {
				$image .= "chat3.gif\"" . tooltip(_("Sie sind alleine in diesem Chatraum"));
			} else {
				$image .= "chat2.gif\"" . tooltip(($chatter == 1) ? _("Es ist eine Person in diesem Chatraum.") : sprintf(_("Es sind %s Personen in diesem Chatraum"),$chatter));
			}
			$image .= "align=\"texttop\">";
			return $image;
	} else {
		return false;
	}
}

function chat_get_entry_level($chatid){
	global $perm,$user,$auth;
	$object_type = get_object_type($chatid);
	$chat_entry_level = false;
	if (!$perm->have_perm("root")){;
		switch($object_type){
			case "user":
			if ($chatid == $user->id){
				$chat_entry_level = "admin";
			} elseif (CheckBuddy($auth->auth['uname'], $chatid)){
				$chat_entry_level = "user";
			}
			break;
			
			case "sem" :
			if ($perm->have_studip_perm("tutor",$chatid)){
				$chat_entry_level = "admin";
			} elseif ($perm->have_studip_perm("user",$chatid)){
				$chat_entry_level = "user";
			}
			break;
			
			case "inst" :
			case "fak" :
			if ($perm->have_studip_perm("admin",$chatid)){
				$chat_entry_level = "admin";
			} elseif ($perm->have_studip_perm("autor",$chatid)){
				$chat_entry_level = "user";
			}
			break;
			
			default:
			if ($chatid == "studip"){
				$chat_entry_level = "user";
			}
		}
	} else {
		$chat_entry_level = "admin";
	}
	return $chat_entry_level;
}

function chat_get_name($chatid){
	$db = new DB_Seminar();
	if ($chatid != "studip"){
		$db->query("SELECT Name from seminare WHERE Seminar_id='$chatid'");
		if (!$db->next_record()){
			$db->query("SELECT Name from Institute WHERE Institut_id='$chatid'");
			if (!$db->next_record()){
				$db->query("SELECT " . $GLOBALS['_fullname_sql']['full'] ." AS Name FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE a.user_id='$chatid'");
				if (!$db->next_record()){
					return false;
				}
			}
		}
		return $db->f("Name");
	} else {
		return "Stud.IP Global Chat";
	}
}

function chat_show_info($chatid){
	global $auth;
		if ($GLOBALS['CHAT_ENABLE']){
			$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
			$sms = new messaging();
			$chatter = $chatServer->isActiveChat($chatid);
			$pic_path = $GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . "/pictures/";
			$chatinv = $sms->check_chatinv($chatid);
			$is_active = $chatServer->isActiveUser($auth->auth['uid'],$chatid);
			$chatname = ($chatter) ? $chatServer->chatDetail[$chatid]['name'] : chat_get_name($chatid);
			//Ausgabe der Kopfzeile
			echo "\n<table border=\"0\" bgcolor=\"#FFFFFF\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" width=\"100%\" >";
			echo "\n<tr><td class=\"topic\" colspan=\"2\" width=\"100%\">";
			echo "\n" . chat_get_chat_icon($chatter,$chatinv,$is_active);
			echo "\n<b>&nbsp;" . _("Chatraum:") . "&nbsp;" . htmlReady($chatname) . "</b></td></tr>";
			echo "\n<tr><td class=\"steel1\" colspan=\"2\" width=\"100%\">&nbsp;</td></tr>";
			echo "\n<tr><td class=\"steel1\" width=\"50%\"><blockquote><font size=\"-1\">";
			if (chat_get_entry_level($chatid) || $chatinv){
				if (!$is_active){
					echo "<a href=\"#\" onClick=\"javascript:return open_chat('$chatid');\">";
					echo "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/chat1.gif\" " . tooltip(_("Diesen Chatraum betreten")) ." ></a>&nbsp;&nbsp;";
					echo _("Sie k&ouml;nnen diesen Chatraum betreten.");
					if ($chatinv){
						echo "&nbsp;" . _("(Sie wurden eingeladen.)");
					}
				} else {
					echo "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/chat1.gif\" " . tooltip(_("Sie haben diesen Chatraum bereits betreten.")) ." >&nbsp;&nbsp;";
					echo _("Sie haben diesen Chatraum bereits betreten.");
				}
				if ($chatServer->chatDetail[$chatid]['password']){
					echo "<br><img border=\"0\" align=\"absmiddle\" src=\"$pic_path/closelock.gif\" >&nbsp;&nbsp;";
					echo _("Dieser Chatraum ist mit einem Passwort gesichert.");
				}
			} else {
				echo "<img border=\"0\" align=\"absmiddle\" src=\"$pic_path/nochat.gif\" >&nbsp;&nbsp;";
				echo _("Um diesen Chatraum zu betreten, brauchen sie eine g&uuml;ltige Einladung.");
			}
			echo "\n</font></blockquote></td><td class=\"steel1\" width=\"50%\"><blockquote><font size=\"-1\">";
			if (!$chatter){
				echo _("Dieser Chatraum ist leer.");
			} else {
				echo ($chatter == 1) ? _("Es ist eine Person in diesem Chatraum.") : sprintf(_("Es sind %s Personen in diesem Chatraum"),$chatter);
				echo "<br>(";
				$chat_user = $chatServer->getUsers($chatid);
				$c = 0;
				foreach ($chat_user as $chat_user_id => $detail){
					echo "<a href=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}/about.php?username={$detail['nick']}\">"
						. htmlReady($detail['fullname']) . "</a>";
					if (++$c != $chatter){
						echo ", ";
					}
				}
				echo ")";
			}
				
			echo "</font></blockquote></td></tr>";
			echo "\n<tr><td class=\"steel1\" colspan=\"2\" width=\"100%\">&nbsp;</td></tr>";
			echo "\n</table>";
			return true;
		} else {
			return false;
		}
}
?>
