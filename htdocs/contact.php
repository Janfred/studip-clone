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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
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
include ("$ABSOLUTE_PATH_STUDIP/calendar/calendar_links.inc.php");   // Output of Stud.IP head

echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
$cssSw->switchClass();

include "links_openobject.inc.php";

$sess->register("contact");
if (!$contact["filter"])
	$contact["filter"]="";
if ($view) {
	$contact["view"]=$view;
}
if ($filter) {
	$contact["filter"]=$filter;
}
$filter = $contact["filter"];
if ($filter == "all")
	$filter="";
if ($contact["view"]=="alpha" && strlen($filter) > 3)
	$filter="";
if ($contact["view"]=="gruppen" && strlen($filter) < 4)
	$filter="";

?>
<table width = "100%" cellspacing="0"><tr>
	<td class="topic" width = "100%"><img src="pictures/nutzer.gif" border="0" align="texttop"><b>&nbsp; Mein Addressbuch</b>
	</td>
</tr><tr><td class="blank">&nbsp;</td></tr></table>
<?

// Aktionen //////////////////////////////////////

// deletel a contact

if ($cmd == "delete") {
	DeleteContact ($contact_id);
}

// remove from buddylist

if ($cmd == "changebuddy") {
	changebuddy($contact_id);
	$open = $contact_id;
}

// delete a single userinfo

if ($deluserinfo) {
	DeleteUserinfo ($deluserinfo);
}

if ($move) {
	MoveUserinfo ($move);
}

// add a new userinfo

if ($owninfolabel AND ($owninfocontent[0]!="Inhalt")){
	AddNewUserinfo ($edit_id, $owninfolabel[0], $owninfocontent[0]);
}

if ($existingowninfolabel) {
	for ($i=0; $i<sizeof($existingowninfolabel); $i++) {
		UpdateUserinfo ($existingowninfolabel[$i], $existingowninfocontent[$i], $userinfo_id[$i]);
	}
}

echo "<table align=\"center\" class=\"blank\" width=\"100%\" cellpadding=\"10\"><tr><td class=\"steelgraulight\">";
echo "<a href=\"$PHP_SELF?open=all&filter=$filter\">Alle aufklappen</a>";

// Buchstabenleiste

// echo $filter;


if (($contact["view"])=="alpha") {
	echo "<table align=\"center\" ><tr>";
	if (!$filter) {
		$cssSw->switchClass();
	}
	echo "<td ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\">&nbsp; "
		."<a href=\"$PHP_SELF?filter=all\">a-z</a>"
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
}

if (($contact["view"])=="gruppen") {
	echo "<table align=\"center\" ><tr>";
	if (!$filter) {
		$cssSw->switchClass();
	}
	echo "<td ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\">&nbsp; "
		."<a href=\"$PHP_SELF?filter=all\"><font size=\"2\">Alle Gruppen</font></a>"
		."&nbsp; </td>";
	if (!$filter) {
		$cssSw->switchClass();
	}
	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db->query ("SELECT name, statusgruppe_id FROM statusgruppen WHERE range_id = '$owner_id'");	
	while ($db->next_record()) {
		if ($filter==$db->f("statusgruppe_id")) {
			$cssSw->switchClass();
		}
		echo "<td ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\">&nbsp; "
		."<a href=\"$PHP_SELF?filter=".$db->f("statusgruppe_id")."\"><font size=\"2\">".$db->f("name")."</font></a>"
		."&nbsp; </td>";
		if ($filter==$db->f("statusgruppe_id")) {
			$cssSw->switchClass();
		}
	}
	echo "</tr></table>";
}



// Anzeige Treffer
if ($edit_id) {
	PrintEditContact($edit_id);
} else {
	PrintAllContact($filter);
}

echo "</td></tr></table>";

page_close()

 ?>
</body>
</html>