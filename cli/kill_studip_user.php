#!/usr/bin/php -q
<?php
/**
* kill_studip_user.php
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
// kill_studip_user.php
// 
// Copyright (C) 2006 André Noack <noack@data-quest.de>,
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
define('SEND_MAIL_ON_DELETE', 1);
define('KILL_ADMINS' , 0);

function CliErrorHandler($errno, $errstr, $errfile, $errline) {
	if ($errno & ~E_NOTICE && error_reporting()){
		fwrite(STDERR,"$errstr \n$errfile line $errline\n");
		exit(1);
	}
}
set_error_handler('CliErrorHandler');

require_once dirname(__FILE__) . "/prepend4.php"; //for use with old style phplib change this to prepend.php!!!
require_once "language.inc.php";
require_once 'lib/functions.php';
require_once "lib/classes/UserManagement.class.php";

class FakePerm {
	function have_perm($foo){return true;}
}

function parse_msg_to_clean_text($long_msg,$separator="§") {
	$msg = explode ($separator,$long_msg);
	$ret = array();
	for ($i=0; $i < count($msg); $i=$i+2) {
		if ($msg[$i+1]) $ret[] = trim(decodeHTML(preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", $msg[$i+1])));
	}
	return join("\n", $ret);
}
if (!($MAIL_LOCALHOST && $MAIL_HOST_NAME && $ABSOLUTE_URI_STUDIP)){
	trigger_error('To use this script you MUST set correct values for $MAIL_LOCALHOST, $MAIL_HOST_NAME and $ABSOLUTE_URI_STUDIP in local.inc!', E_USER_ERROR);
}

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

if (!$argv[1]){
	fwrite(STDOUT,'Usage: ' . basename(__FILE__) . ' [file][-] (use - to read from STDIN)' .chr(10));
	exit(0);
}
if ($argv[1] == '-'){
	$fo = STDIN;
} elseif (is_file($argv[1])){
	$fo = fopen($argv[1],'r');
} else {
	trigger_error("File not found: {$argv[1]}", E_USER_ERROR);
}

$list = '';
while (!feof($fo)) {
  $list .= fgets($fo, 1024);
}

$kill_list = preg_split("/[\s,;]+/", $list, -1, PREG_SPLIT_NO_EMPTY);
$kill_list = array_map('mysql_escape_string', array_keys(array_flip($kill_list)));
$db = new DB_Seminar();
$db->query("SELECT * FROM auth_user_md5 WHERE username IN ('".join("','", $kill_list)."')");
while($db->next_record()){
	$kill_user[$db->f('username')] = $db->Record;
}
if (!is_array($kill_user)) {
	fwrite(STDOUT, 'No user from list found in database.' . chr(10));
	exit(0);
}
$umanager = new UserManagement();
$perm = new FakePerm();
$user = new Seminar_User('xxx');
foreach($kill_user as $uname => $udetail){
	if (!KILL_ADMINS && ($udetail['perms'] == 'admin' || $udetail['perms'] == 'root')){
		fwrite(STDOUT, "user: $uname is '{$udetail['perms']}', NOT deleted". chr(10));
	} else {
		$umanager->user_data = array();
		$umanager->msg = '';
		$umanager->getFromDatabase($udetail['user_id']);
		//wenn keine Email gewünscht, Adresse aus den Daten löschen
		if (!SEND_MAIL_ON_DELETE) $umanager->user_data['auth_user_md5.Email'] = '';
		if ($umanager->deleteUser()){
			fwrite(STDOUT, "user: $uname successfully deleted:". chr(10)
			. parse_msg_to_clean_text($umanager->msg)
			. chr(10));
		} else {
			fwrite(STDOUT, "user: $uname NOT deleted:". chr(10)
			. parse_msg_to_clean_text($umanager->msg)
			. chr(10));
		}
	}
}
?>
