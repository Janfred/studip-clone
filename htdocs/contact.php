<?
/*
contact.php - 0.8
Bindet Addressbuch ein.
Copyright (C) 2003 Ralf Stockmann <rstockm@uni-goettingen.de>

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

// Default_Auth
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
if ($perm->have_perm("admin")) {
	$perm->perm_invalid($auth->auth["perm"], "");
	exit;
}
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

include "functions.php";
include "statusgruppe.inc.php";
include "contact.inc.php";
include "visual.inc.php";

$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
$cssSw->enableHover();

include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
$cssSw->switchClass();

include "links_openobject.inc.php";

// Aktionen

if ($cmd == "delete") {
	echo DeleteContact ($contact_id);
}

echo "<table align=\"center\" class=\"blank\" width=\"100%\" cellpadding=\"10\"><tr><td class=\"blank\">";
echo "<h3>Addressbuch</h3>";

echo "<a href=\"$PHP_SELF?open=all&filter=$filter\">Alle aufklappen</a>";

// Buchstabenleiste

echo "<table align=\"center\" ><tr>";
if (!$filter) {
	$cssSw->switchClass();
}
echo "<td ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\">&nbsp; "
	."<a href=\"$PHP_SELF\">a-z</a>"
	."&nbsp; </td>";
if (!$filter) {
	$cssSw->switchClass();
}
for ($i=97;$i<123;$i++) {
	if ($filter==chr($i)) {
		$cssSw->switchClass();
	}
	echo "<td ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\">&nbsp; "
	."<a href=\"$PHP_SELF?filter=".chr($i)."\">".chr($i)."</a>"
	."&nbsp; </td>";
	if ($filter==chr($i)) {
		$cssSw->switchClass();
	}
}
echo "</tr></table>";


// Anzeige Treffer

PrintAllContact($filter);

echo "</td></tr></table>";
print("</body></html>");

?>