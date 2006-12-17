#!/usr/bin/php -q
<?php
/**
* send_mail_notifications.php
* 
* 
* 
*
* @author		André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// send_mail_notifications.php
// 
// Copyright (C) 2005 André Noack <noack@data-quest.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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
require_once "prepend4.php"; //for use with old style phplib change this to prepend.php!!!
require_once "language.inc.php";
require_once 'lib/functions.php';
require_once "lib/classes/ModulesNotification.class.php";


get_config('MAIL_NOTIFICATION_ENABLE') || die('Mail notifications are disabled in this Stud.IP installation.');
($MAIL_LOCALHOST && $MAIL_HOST_NAME && $ABSOLUTE_URI_STUDIP) || die('To use mail notifications you MUST set correct values for $MAIL_LOCALHOST, $MAIL_HOST_NAME and $ABSOLUTE_URI_STUDIP in local.inc!');

set_time_limit(60*60*2);

class FakeUser {
	var $id;
}

$db = new DB_Seminar();
$notification = new ModulesNotification();
$smtp =& $notification->smtp;
$user = new FakeUser();
$perm = new Seminar_Perm();

$db->query("SELECT aum.user_id,aum.username,{$GLOBALS['_fullname_sql']['full']} as fullname,Email FROM seminar_user su INNER JOIN auth_user_md5 aum USING(user_id) LEFT JOIN user_info ui USING(user_id) WHERE notification != 0 GROUP BY su.user_id");
while($db->next_record()){
	$user->id = $db->f("user_id");
	setTempLanguage($db->f("user_id"));	
	$to = $db->f("Email");				
	$title = "[" . $GLOBALS['UNI_NAME_CLEAN'] . "] " . _("Tägliche Benachrichtigung");
	$reply_to = $smtp->abuse;				
	$mailmessage = $notification->getAllNotifications($db->f('user_id'));
	if ($mailmessage){
		$ok = $smtp->SendMessage($smtp->env_from,
								array($to),
								array(	"From: ".$smtp->QuotedPrintableEncode($smtp->from,1),
										"To: \"".$smtp->QuotedPrintableEncode($db->f('fullname'),1)."\" <$to>",
										"Reply-To: $reply_to",
										"Subject: " . $smtp->QuotedPrintableEncode($title,1)),
								$mailmessage);
		echo date('r') . " " . $db->f('username') . ": " . (int)$ok . "\n";
	}
}
?>
