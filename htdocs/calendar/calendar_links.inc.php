<?
/*
calendar_links.inc.php 0.8.15-20021012
Reiternavigation im Bereich 'Mein Terminkalender'
und 'Mein Stundenplan' in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthien@gmx.de>

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

echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>\n";
	
	if ($i_page == "calendar.php") {
		echo '<td class="links1b" align="right" nowrap>';
		echo '<a class="links1b" href="calendar.php">';
		echo "&nbsp; &nbsp; " . _("Terminkalender") . "&nbsp; &nbsp; ";
		echo '</a><img src="pictures/reiter2.jpg" align="absmiddle"></td>';
	}
	else {
		echo '<td class="links1" align="right" nowrap>';
		echo '<a class="links1" href="calendar.php">';
		echo "&nbsp; &nbsp; " . _("Terminkalender") . "&nbsp; &nbsp; ";
		echo '</a><img src="pictures/reiter1.jpg" align="absmiddle"></td>';
	}
	
	if ($i_page == "contact.php" || $i_page == "contact_statusgruppen.php") {
		echo '<td class="links1b" align="right" nowrap>';
		echo '<a class="links1b" href="contact.php">';
		echo "&nbsp; &nbsp; " . _("Addressbuch") . "&nbsp; &nbsp; ";
		echo '</a><img src="pictures/reiter2.jpg" align="absmiddle"></td>';
	}
	else {
		echo '<td class="links1" align="right" nowrap>';
		echo '<a class="links1" href="contact.php">';
		echo "&nbsp; &nbsp; " . _("Addressbuch") . "&nbsp; &nbsp; ";
		echo '</a><img src="pictures/reiter1.jpg" align="absmiddle"></td>';
	}
	
	if ($i_page == "mein_stundenplan.php") {
		echo '<td class="links1b" align="right" nowrap>';
		echo '<a class="links1b" href="mein_stundenplan.php">';
		echo "&nbsp; &nbsp; " . _("Stundenplan") . "&nbsp; &nbsp; ";
		echo '</a><img src="pictures/reiter4.jpg" align="absmiddle"></td>';
	}
	else {
		echo '<td class="links1" align="right" nowrap>';
		echo '<a class="links1" href="mein_stundenplan.php">';
		echo "&nbsp; &nbsp; " . _("Stundenplan") . "&nbsp; &nbsp; ";
		echo '</a><img src="pictures/reiter4.jpg" align="absmiddle"></td>';
	}

echo "</tr>\n</table>\n";
echo "<table cellspacing=\"0\" cellpadding=\"4\" border=\"0\" width=\"100%\">\n";

if ($i_page == "calendar.php") {

	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";
		
	if ($cmd == "showday" || $cmd == "add" || $cmd == "del") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showday';
		if ($atime)
			echo "&atime=$atime";
		echo '">' . _("Tag") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showday';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Tag") . '&nbsp; &nbsp; </a>';
	}
	
	if (($i_page == "calendar.php" && $cmd == "") || $cmd == "showweek"){
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showweek';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Woche") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showweek';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Woche") . '&nbsp; &nbsp; </a>';
	}

	if ($cmd == "showmonth") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showmonth';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Monat") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showmonth';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Monat") . '&nbsp; &nbsp; </a>';
	}

	if ($cmd == "showyear") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showyear';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Jahr") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=showyear';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Jahr") . '&nbsp; &nbsp; </a>';
	}
	
	if ($cmd == "edit") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=edit';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Bearbeiten") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=edit';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Bearbeiten") . '&nbsp; &nbsp; </a>';
	}
	
	if ($cmd == "bind") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=bind';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Veranstaltungstermine") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=bind';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Veranstaltungstermine") . '&nbsp; &nbsp; </a>';
	}
	
/*	if ($cmd == "import") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=import';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Importieren") . '&nbsp;&nbsp;</a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=import';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Importieren") . '&nbsp;&nbsp;</a>';
	} */
	
	if ($cmd == "changeview") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=changeview';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Ansicht anpassen") . '&nbsp; &nbsp; </a>';
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="calendar.php?cmd=changeview';
		if($atime)
			echo "&atime=$atime";
		echo '">' . _("Ansicht anpassen") . '&nbsp; &nbsp; </a>';
	}
	
	echo"<br />";
}

elseif ($i_page == "contact.php" || $i_page == "contact_statusgruppen.php") {
	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";	

	if ($i_page == "contact.php" && $contact["view"]=="alpha") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="contact.php?view=alpha">';
		echo _("Alphabetisch") . "&nbsp; &nbsp; </a>";
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="contact.php?view=alpha">';
		echo _("Alphabetisch") . "&nbsp; &nbsp; </a>";
	}

	if ($i_page == "contact.php" && $contact["view"]=="gruppen") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="contact.php?view=gruppen">';
		echo _("Gruppenansicht") . "&nbsp; &nbsp; </a>";
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="contact.php?view=gruppen">';
		echo _("Gruppenansicht") . "&nbsp; &nbsp; </a>";
	}
	
	if ($i_page == "contact_statusgruppen.php") {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="contact_statusgruppen.php">';
		echo _("Gruppenverwaltung") . "&nbsp; &nbsp; </a>";
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="contact_statusgruppen.php">';
		echo _("Gruppenverwaltung") . "&nbsp; &nbsp; </a>";
	}


	echo"<br />";
}


elseif ($i_page == "mein_stundenplan.php") {
	echo "<tr><td class=\"steel1\">&nbsp; &nbsp; ";	

	if (!$change_view) {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="mein_stundenplan.php">';
		echo _("Stundenplan") . "&nbsp; &nbsp; </a>";
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="mein_stundenplan.php">';
		echo _("Stundenplan") . "&nbsp; &nbsp; </a>";
	}

	echo '<img src="pictures/forumgrau.gif" border="0">';
	echo '<a class="links1"  target="_new" href="mein_stundenplan.php?print_view=TRUE">';
	echo _("Druckansicht") . "&nbsp; &nbsp; </a>";

	if ($change_view) {
		echo '<img src="pictures/forumrot.gif" border="0">';
		echo '<a class="links1"  href="mein_stundenplan.php?change_view=TRUE">';
		echo _("Ansicht anpassen") . "&nbsp; &nbsp; </a>";
	}
	else {
		echo '<img src="pictures/forumgrau.gif" border="0">';
		echo '<a class="links1"  href="mein_stundenplan.php?change_view=TRUE">';
		echo _("Ansicht anpassen") . "&nbsp; &nbsp; </a>";
	}

	echo"<br />";
}

else echo"<tr><td class=\"steel1\">&nbsp;";

echo"</td></tr><tr><td class=\"reiterunten\">&nbsp; </td></tr></table>";

?>
