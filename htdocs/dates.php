<?php
/*
dates.php - Script zur Anzeige des Ablaufplans einer Veranstaltung
Copyright (C) 2000 André Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>,
Stefan Suchi <suchi@gmx.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$sess->register("dates_data");
	
// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	
require_once("$ABSOLUTE_PATH_STUDIP/show_dates.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

checkObject();
checkObjectModule("schedule");
object_set_visit($SessSemName[1], "schedule", "last", object_get_visit($SessSemName[1], "sem"));


include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
	
if ($dopen)
	$dates_data["open"]=$dopen;
	
if ($dclose)
	$dates_data["open"]='';

?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;<img src="pictures/icon-uhr.gif" align="absmiddle">&nbsp; <? echo $SessSemName["header_line"]; ?> - <?=_("Ablaufplan")?></b>
			</td>
		</tr>
		<tr>
			<td class="blank" width="100%" valign="top"><br /><blockquote><?=_("Hier finden Sie alle Termine der Veranstaltung."). "<br><br>" . _("Klicken Sie auf ein Text-Icon, um zu den hochgeladenen Dateien des jeweiligen Termins zu gelangen.")?>
			</td>
			<td class="blank" align="right"><img src="pictures/termine.jpg" border="0">
			</td>
		</tr>
	</table>
	<?
	$show_docs=TRUE;
	$name = rawurlencode($SessSemName[0]);
	($rechte) ? $show_admin="admin_dates.php?range_id=$SessSemName[1]" : $show_admin=FALSE;
	if (show_dates(0, 0, $dates_data["open"], $SessSemName[1], $show_not, $show_docs, $show_admin))
		echo"<br>";

?>
</body>
</html>
<?php
  // Save data back to database.
  page_close()
 ?>
<!-- $Id$ -->
