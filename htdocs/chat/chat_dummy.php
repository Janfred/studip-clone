<?
/**
* chat_dummy
* 
* Shows nothing, only used to send chatlogs
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup	chat_modules
* @module		chat_dummy
* @package		Chat
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// chat_dummy.php
// Shows nothing, only used to send chatlogs
// Copyright (c) 2003 André Noack <noack@data-quest>
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
ob_start();
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");
//chat eingeschaltet?
if (!$CHAT_ENABLE) {
	page_close();
	die;
}
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/ChatServer.class.php";
require_once $ABSOLUTE_PATH_STUDIP."/visual.inc.php";

$chatServer =& ChatServer::GetInstance($CHAT_SERVER_NAME);
$chatServer->caching = true;
if (!is_array($chatServer->chatDetail[$chatid]['users'][$user->id]['log'])){
	echo "chat-dummy";
	page_close();
	die;
}
$log_count = count($chatServer->chatDetail[$chatid]['users'][$user->id]['log']);
$end_time = $chatServer->chatDetail[$chatid]['users'][$user->id]['log'][$log_count-1];
$start_time = $chatServer->chatDetail[$chatid]['users'][$user->id]['log'][$log_count-2];
$output = _("Chat: ") . $chatServer->chatDetail[$chatid]['name'] . "\r\n";
$output .= _("Beginn der Aufzeichnung: ") . strftime("%A, %c",$start_time) . "\r\n";
$output .= _("Ende der Aufzeichnung: ") . strftime("%A, %c",$end_time) . "\r\n";
$output .= _("Aufgezeichnet von: ") . $chatServer->chatDetail[$chatid]['users'][$user->id]['fullname'] . "\r\n";
$output .= str_repeat("-",80) . "\r\n";
for ($i = 0; $i < $log_count-2; ++$i){
	$output .= decodeHTML(preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", $chatServer->chatDetail[$chatid]['users'][$user->id]['log'][$i])) . "\r\n";
}
header("Content-type: text/plain");
header("Content-Disposition: attachment; filename=\"studip_chatlog_".date("d-m-Y_H-i").".log\"");
header("Content-length: ".strlen($output));
header("Expires: Mon, 12 Dec 2001 08:00:00 GMT");
header("Last-Modified: " . gmdate ("D, d M Y H:i:s") . " GMT");
if ($_SERVER['HTTPS'] == "on")
	header("Pragma: public");
else
	header("Pragma: no-cache");
header("Cache-Control: private");
echo $output;
page_close();
?>
