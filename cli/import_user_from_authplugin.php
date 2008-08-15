#!/usr/bin/php -q
<?php
/**
* import_user_from_authplugin.php
* 
* 
* 
*
* @author		André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		
* @access		public
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// import_user_from_authplugin.php
// 
// Copyright (C) 2008 André Noack <noack@data-quest.de>,
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

require_once dirname(__FILE__) . '/studip_cli_env.inc.php';
require_once 'lib/language.inc.php';
require_once 'lib/functions.php';
require_once 'lib/classes/UserManagement.class.php';

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

if (!$argv[1] || !$argv[2]){
	fwrite(STDOUT,'Usage: ' . basename(__FILE__) . ' [StudipAuthPlugin] [file][-] (use - to read from STDIN)' .chr(10));
	exit(0);
}

$USED_AUTH_PLUGIN = $argv[1];
if (!in_array($USED_AUTH_PLUGIN, $STUDIP_AUTH_PLUGIN)){
	trigger_error(sprintf('To use this script you MUST activate AuthPlugin: %s!', $USED_AUTH_PLUGIN) , E_USER_ERROR);
}

if ($argv[2] == '-'){
	$fo = STDIN;
} elseif (is_file($argv[2])){
	$fo = fopen($argv[2],'r');
} else {
	trigger_error("File not found: {$argv[2]}", E_USER_ERROR);
}

$list = '';
while (!feof($fo)) {
  $list .= fgets($fo, 1024);
}

$user_list = preg_split("/[\s,;]+/", $list, -1, PREG_SPLIT_NO_EMPTY);
$user_list = array_map('trim', array_keys(array_flip($user_list)));
if (!count($user_list)) {
	fwrite(STDOUT, sprintf('No usernames found in file: ', $argv[1]) . chr(10));
	exit(0);
}

$_language = $DEFAULT_LANGUAGE;
$auth_plugin = StudipAuthAbstract::GetInstance($USED_AUTH_PLUGIN);

foreach($user_list as $uname){
	$uid = $auth_plugin->updateUser($uname);
	if($uid){
		$action_taken = ($auth_plugin->is_new_user ? 'created' : 'updated');
		$uname = get_username($uid);
		fwrite(STDOUT, "user: $uname with id: $uid successfully $action_taken" . chr(10));
	} else {
		fwrite(STDOUT, "user: $uname could not be created/updated. Reason:" . chr(10)
			. strip_tags(str_replace('<br>', chr(10), $auth_plugin->error_msg))
			. chr(10));
	}
}
exit(1);
?>
