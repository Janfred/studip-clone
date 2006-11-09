<?php
/**
* dates.php
*
* basic script for viewing dates (module schedule)
*
*
* @author		André Noack <noack@data-quest.de>, Cornelis Kater <kater@data-quest.de>, Stefan Suchi <suchi@data-quest.de>, data-quest GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		views
* @module		dates.php
* @package		studip_core
*/

//object.inc.php - Verwaltung von Objektoperationen
//Copyright (C) 2004 André Noack <noack@data-quest.de>, Cornelis Kater <kater@data-quest.de>, Stefan Suchi <suchi@data-quest.de>, data-quest GmbH <info@data-quest.de>
// This file is part of Stud.IP
// object.inc.php
// Script zur Anzeige des Ablaufplans einer Veranstaltung
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

$HELP_KEYWORD="Basis.InVeranstaltungAblauf";

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/show_dates.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

checkObject();
checkObjectModule("schedule");
object_set_visit_module("schedule");

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");

$sess->register("dates_data");

if ($dopen)
	$dates_data["open"]=$dopen;

if ($dclose)
	$dates_data["open"]='';

?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="topic" >&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/icon-uhr.gif" border="0" align="absmiddle" alt="<?_("Ablaufplan")?>"><b>&nbsp;<? echo $SessSemName["header_line"]; ?> - <?=_("Ablaufplan")?></b></td>
	</tr>
	<tr>
		<td class="blank">&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" valign ="top">
			<table width="100%" cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td valign ="top">
						<table width="100%" cellspacing="0" cellpadding="0" border="0">
							<tr>
								<td class="blank" valign ="top">
									<?
									$name = rawurlencode($SessSemName[0]);
									if ($rechte)
										$show_admin=TRUE;
									else
										$show_admin=FALSE;
									if (show_dates(0, 0, $dates_data["open"], $SessSemName[1], $show_not, TRUE, $show_admin, TRUE, FALSE))
										echo"<br>";
									?>
								</td>
							</tr>
						</table>
					</td>
					<td class="blank" width="270" align="center" valign="top">
						<?
						//Build an infobox
						$infobox[0]["kategorie"] = _("Informationen:");
						$infobox[0]["eintrag"][] = array ('icon' => "ausruf_small.gif",
							"text"  =>_("Hier finden Sie alle Termine der Veranstaltung."));
						if ($rechte) {
							$infobox[1]["kategorie"] = _("Aktionen:");
							$infobox[1]["eintrag"][] = array ('icon' => "link_intern.gif",
								"text"  =>"<a href=\"admin_dates.php?insert_new=TRUE#anchor\">"._("Einen neuen Termin anlegen")."</a>");
							$infobox[1]["eintrag"][] = array ('icon' => "link_intern.gif",
								"text"  =>"<a href=\"admin_dates.php\">"._("Zur Ablaufplanverwaltung")."</a>");
						}
						print_infobox ($infobox, "schedules.jpg");
						?>
						<br />
					</td>
				</tr>
			</table>
		</td>
	</tr>
</body>
</html>

<?
//Save data back to database.
page_close()
?>
