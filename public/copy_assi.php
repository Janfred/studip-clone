<?php
# Lifter001: TODO
# Lifter002: TODO

/*
copy_assi.php - Dummy zum Einstieg in Veranstaltungskopieren
Copyright (C) 2004 Tobias Thelen <tthelen@uni-osnabrueck.de>

Modified/Extended Version to support an alternative copy mechanism (ACM)
by Dirk Oelkers <d.oelkers@fh-wolfenbuettel.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("dozent");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once 'lib/functions.php';
require_once ('lib/classes/LockRules.class.php');

// -- here you have to put initialisations for the current page

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Kopieren der Veranstaltung");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();

//get ID from a open Institut
if ($SessSemName[1])
	$header_object_id = $SessSemName[1];
else
	$header_object_id = $admin_admission_data["sem_id"];

//Change header_line if open object
$header_line = getHeaderLine($header_object_id);
if ($header_line)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;

require_once 'lib/visual.inc.php';
if ($SessSemName[1]) {
	if(LockRules::Check($SessSemName[1], 'seminar_copy')) {
		$lockRule = new LockRules();
		$lockdata = $lockRule->getSemLockRule($SessSemName[1]);
		$msg = 'error§' . _("Die Veranstaltung kann nicht kopiert werden.").'§';
		if ($lockdata['description']){
			$msg .= "info§" . fixlinks($lockdata['description']).'§';
		}
		?>
		<table border=0 align="center" cellspacing=0 cellpadding=0 width="100%">
		<tr><td class="blank" colspan=2><br>
		<?
		parse_msg($msg);
		?>
		</td></tr>
		</table>
		<?
	} else {

		$cmd = $_GET["cmd"];
		
		if ( !$cmd )
		{
			require_once( "lib/semcopy/entry_visual.inc.php" );
		}
		
		if ( $cmd=="show_copy_form" )
		{
			require_once( "lib/semcopy/copy_form_visual.inc.php" );
		}
		
		$cmd = $_POST["cmd"];
		
		if ( $cmd=="do_copy" )
		{
			require_once( "lib/semcopy/copy_action_visual.inc.php" );
		}
	}
}
include ('lib/include/html_end.inc.php');
page_close();
?>