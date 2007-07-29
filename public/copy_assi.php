<?php

/*
copy_assi.php - Dummy zum Einstieg in Veranstaltungskopieren
Copyright (C) 2004 Tobias Thelen <tthelen@uni-osnabrueck.de>

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

// -- here you have to put initialisations for the current page

$CURRENT_PAGE=getHeaderLine($SessSemName[1])." - "._("Kopieren der Veranstaltung");

// Start of Output
include 'lib/include/html_head.inc.php'; // Output of html head
include 'lib/include/header.php';   // Output of Stud.IP head
include 'lib/include/links_admin.inc.php'; //Output the nav

require_once 'lib/visual.inc.php';

if ($SessSemName[1]) {
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<tr><td class="blank" colspan=2>
	<blockquote>
	<? 
	printf(_("Die Veranstaltung wurde zum Kopieren ausgewählt."). " ");
	printf(_("Um die vorgewählte Veranstaltung zu kopieren klicken sie %shier%s."),'<a href="admin_seminare_assi.php?cmd=do_copy&cp_id='.$SessSemName[1].'&start_level=TRUE&class=1">','</a>'); ?>
	</blockquote>
	<br />
	</td></tr>
	</table>
<?php
}
include ('lib/include/html_end.inc.php');
page_close();
?>