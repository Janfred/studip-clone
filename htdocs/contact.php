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


	if ($forum["jshover"]==1 AND $auth->auth["jscript"]) { // JS an und erwuenscht?
		echo "<script language=\"JavaScript\">";
		echo "var ol_textfont = \"Arial\"";
		echo "</script>";
		echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
		echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"overlib.js\"></SCRIPT>";
	}


include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/contact.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
$cssSw->enableHover();

include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head


echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
$cssSw->switchClass();

// include "links_openobject.inc.php";

$sess->register("contact");

if (!$contact["filter"])
	$contact["filter"]="all";
if ($view) {
	$contact["view"]=$view;
}
if (!$contact["view"])
	$contact["view"]="alpha";

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

include ("$ABSOLUTE_PATH_STUDIP/calendar/calendar_links.inc.php");   // Output of Stud.IP head

// Aktionen //////////////////////////////////////

// adding a contact via search

if ($Freesearch) {
	$open = AddNewContact(get_userid($Freesearch));
}

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



?>
<table width = "100%" cellspacing="0" cellpadding="0"><tr>
	<td class="topic" colspan="2" width = "100%"><img src="pictures/nutzer.gif" border="0" align="texttop"><b>&nbsp; Mein Addressbuch <font size="2">(<?echo $size_of_book = GetSizeofBook();?> Eintr&auml;ge)</size></b>
	</td>
</tr><tr><td class="blank" align="left" valign="absmiddle">

	<form action="<? echo $PHP_SELF ?>?cmd=search" method="POST"><?
echo "&nbsp; <a href=\"$PHP_SELF?open=all&filter=$filter\"><img src=\"pictures/forumgraurunt.gif\" border=\"0\">&nbsp; <font size=\"2\">Alle aufklappen</font></a></td>";
echo "<td class=\"blank\" align=\"right\">";

if ($search_exp) {
	printf ("<input type=\"IMAGE\" name=\"search\" src= \"./pictures/buttons/eintragen-button.gif\" border=\"0\" value=\" In Addressbuch eintragen\" %s>&nbsp;  ", tooltip("In Addressbuch eintragen"));
	SearchResults($search_exp);
	printf ("<input type=\"IMAGE\" name=\"search\" src= \"./pictures/rewind.gif\" border=\"0\" value=\" Personen suchen\" %s>&nbsp;  ", tooltip("neue Suche"));
} else {
	echo "&nbsp; <input type=\"text\" name=\"search_exp\" value=\"\">";
	printf ("<input type=\"IMAGE\" name=\"search\" src= \"./pictures/suchen.gif\" border=\"0\" value=\" Personen suchen\" %s>&nbsp;  ", tooltip("Person suchen"));
} 
echo "</form>";

?>

</td></tr></table>
<?


echo "<table align=\"center\" class=\"blank\" width=\"100%\" cellpadding=\"1\"><tr><td align=\"middle\" class=\"steelgraulight\">";

/*




*/

// echo "<a href=\"$PHP_SELF?open=all&filter=$filter\"><img src=\"pictures/meinesem.gif\" border=\"0\" valign=\"bottom\">&nbsp; <font size=\"2\">in Addressbuch aufnehmen</font></a><hr>";

// Buchstabenleiste

// echo $filter;


if (($contact["view"])=="alpha") {
	echo "<table align=\"center\" width=\"70%\"><tr>";
	if (!$filter) {
		$cssSw->switchClass();
	}
	echo "<td width=\"5%\" align=\"center\" valign=\"center\" ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\" "
		. tooltip(($size_of_book == 1) ? _("1 Eintrag") : sprintf(_("%s Einträge"),$size_of_book),false)
		."><a href=\"$PHP_SELF?filter=all\">a-z</a>"
		."</td>";
	if (!$filter) {
		$cssSw->switchClass();
	}
	$size_of_book_by_letter = GetSizeOfBookByLetter();
	for ($i=97;$i<123;$i++) {
		if ($filter==chr($i)) {
			$cssSw->switchClass();
		}
		echo "<td width=\"3%\"  align=\"center\" valign=\"center\" ".$cssSw->getHover()." class=\"".$cssSw->getClass()."\""
		. tooltip(($size_of_book_by_letter[chr($i)] == 1) ? ("1 Eintrag") : sprintf(_("%s Einträge"),($size_of_book_by_letter[chr($i)]) ? $size_of_book_by_letter[chr($i)] : "keine"),false)
		."><a href=\"$PHP_SELF?filter=".chr($i)."\" "
		. ">".chr($i)."</a>"
		."</td>";
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