<?
/**
* tracking.inc.php
* 
* a script to track User. Attention: THIS SCRIPT IS NOT PART OF THE STUD.IP DISTRIBUTION!
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	other
* @module		tracking.inc.php
* @package		other
*/

// +---------------------------------------------------------------------------+
// This file is NOT an official part of Stud.IP
// tracking.inc.php
// Script zum Tracking von bestimmten Usern. Nutzt dafuer eigene Tabellen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

/*
$db = new DB_Seminar;

$query = sprintf ("SELECT user_id FROM tracking_user WHERE user_id = '%s'", $user->id);
$db->query($query);

//If User id is found, track him with all the data...
if ($db->next_record()) {
	$query = sprintf ("INSERT INTO tracking_data SET entry_id = '%s', user_id = '%s', page = '%s', params = '%s', timestamp = '%s', open_object = '%s', user_ip = '%s', user_agent = '%s' ", md5(uniqid(rand())), $user->id, $i_page, $QUERY_STRING, time(), $SessSemName[1], $REMOTE_ADDR, $HTTP_USER_AGENT, $referrer_page);
	$db->query($query);
//track him anonymously
} else {
	$hashed_user_id = md5($user->id);
	$query = sprintf ("INSERT INTO tracking_data SET entry_id = '%s', user_id = '%s', page = '%s', timestamp = '%s'", md5(uniqid(rand())), $hashed_user_id, $i_page, time(), $referrer_page);
	$db->query($query);
}
*/
?>