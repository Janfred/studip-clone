<?
/**
* Login script for the Chat
* 
* This script checks user permissions and builds a frameset for the chat
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup		chat_modules
* @module		chat_login
* @package		Chat
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_nicklist.php
// Shows the nicklist
// Copyright (c) 2002 André Noack <andre.noack@gmx.net>
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

/**
* Close the actual window if PHPLib shows login screen
* @const CLOSE_ON_LOGIN_SCREEN
*/
define("CLOSE_ON_LOGIN_SCREEN",true);
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

//chat eingeschaltet?
if (!$CHAT_ENABLE) {
	page_close();
	die;
}

require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php";
//Studip includes
require_once $ABSOLUTE_PATH_STUDIP."msg.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."messaging.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."functions.php";
require_once $ABSOLUTE_PATH_STUDIP."visual.inc.php";

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session


$chatServer =& ChatServer::GetInstance($CHAT_SERVER_NAME);
$chatServer->caching = true;
$chatServer->addChat($chatid);
if (!$chatServer->addUser($user->id,$chatid,$auth->auth["uname"],get_fullname(),$perm->have_perm("root"))){
	?><html>
	<head>
	 <title>Stud.IP</title>
	<?php include $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_style.inc.php";?>
	<script type="text/javascript" language="javascript" src="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>/md5.js"></script>
	</head>
	<body>
	<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=70%>
	<tr valign=top align=middle>
		<td class="topic" align="left"><b>&nbsp;Zugriff verweigert</b></td>
		</tr>
		<tr><td class="blank">&nbsp;</td></tr>
		<?
		parse_msg ("error§Chatlogin nicht möglich <br/><font size=-1 color=black>Vermutlich sind sie noch aus einer früheren Chat Session angemeldet. Es dauert ca. 3-5 s bis sie automatisch aus dem Chat entfernt werden. Versuchen sie es etwas später noch einmal.</font>", "§", "blank", 1);
		?>
		<tr><td class="blank"><font size=-1>&nbsp;Fenster <a href="javascript:window.close()"><b>schließen</b></a><br />&nbsp;</font>
		</td></tr>
	</table>
	</body>
	</html>
	<?
	page_close();
	die;
}
//evtl Chateinladungen löschen
$sms=new messaging();
$sms->delete_chatinv($auth->auth["uname"]);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN">
<html>
<head>
	<title>Chat(<?=$auth->auth["uname"]?>) -
	<?=htmlReady($chatServer->chatDetail[$chatid]["name"])?></title>
	<script type="text/javascript" language="javascript" src="<?=$CANONICAL_RELATIVE_PATH_STUDIP?>/md5.js"></script>
<script type="text/javascript">
	/**
	* JavaScript 
	*/
	var chatuniqid = '<?=$chatServer->chatDetail[$chatid]["id"]?>';
	function coming_home(url) {
		if (opener.closed) alert('<?=_("Das Hauptfenster wurde geschlossen,\\ndiese Funktion kann nicht mehr ausgeführt werden!")?>');
		else {
			opener.location.href = url;
			opener.focus();
		}
		return false;
	}
	
	</script>
</head>
<frameset rows="83%,30,*,0" FRAMEBORDER=NO FRAMESPACING=0 FRAMEPADDING=0 border=0>
	<frameset cols="*,25%" FRAMEBORDER=NO FRAMESPACING=0 FRAMEPADDING=0 border=0>
		<frame name="frm_chat" src="chat_client.php?chatid=<?=$chatid?>" marginwidth=1 marginheight=1>
		<frame name="frm_nicklist" src="chat_nicklist.php?chatid=<?=$chatid?>"  marginwidth=1 marginheight=1>
	</frameset>
<frame name="frm_status" src="chat_status.php?chatid=<?=$chatid?>" marginwidth=1 marginheight=2 >
<frame name="frm_input" src="chat_input.php?chatid=<?=$chatid?>" marginwidth=1 marginheight=0 >
<frame name="frm_dummy" src="chat_dummy.php?chatid=<?=$chatid?>" marginwidth=0 marginheight=0 scrolling=no noresize >
</frameset>
</html>
<?
page_close();
?>
