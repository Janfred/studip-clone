<?php
/*
help/kartei.inc.php - die Navigation auf den Hilfeseiten von Stud.IP
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

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

if (!isset($druck)) {  // Kopfzeile anzeigen
	?>
	
	<table class="header" border="0" width="100%" cellspacing="0" cellpadding="0" bordercolor="#999999" height="25">
		<tr>
			<td class="header" width="33%" align="center">
				<a href="index.php" target="_top"><font color="#FFFFFF" size="4"><b>Inhaltsverzeichnis</b></font></a>
			</td>
			<td class="header" width="33%" align="center">
				<a href="index.php?druck=1<?if(isset($help_page)) echo "&help_page=".$help_page?>" target="_top"><font color="#FFFFFF" size="4"><b>Druckansicht</b></font></a>
			</td>
			<td class="header" width="33%" align="center">
				<a href="../impressum.php"><img border="0" src="../pictures/logo2.gif" alt="Impressum"></a>
			</td>
		</tr>
	</table><br>
	<?
}
	


if (isset($help_page) && !isset($druck)) {  // wir zeigen das Kartei-System

	print("\n<table cellpadding=0 cellspacing=0 border=0><tr>");

	// die Hauptkategorien durchlaufen
	for ($i = 0; $i < count($pages); $i++) {

		// in welcher Hauptkategorie sind wir denn gerade?
		for ($j = 0; $j < count($pages[$i]["kategorien"]); $j++) {
			if ($pages[$i]["kategorien"][$j]["page"] == $help_page) {
				$topkat = $i;
			}
		}
	}

	// Reiter ausgeben
	printf("<td class=\"links1b\">&nbsp; <b><img align=\"absmiddle\" src=\"../pictures/info.gif\" alt=\"Dieser Abschnitt der Hilfe erkl&auml;rt: %s\"",
			$pages[$topkat]["text"]);
	if ($auth->auth["jscript"])
	printf(" onClick=\"alert('Dieser Abschnitt der Hilfe erkl&auml;rt: %s');\" ",
			$pages[$topkat]["text"]);
	printf("border=\"0\"></b>&nbsp; <img src=\"../pictures/reiter1.jpg\" align=\"absmiddle\"></td>");
	
	printf("<td class=\"links1b\" align=\"right\" nowrap><a class=\"links1b\" href=\"$PHP_SELF?help_page=%s\"><font color=\"#000000\" size=2><b>&nbsp; &nbsp; %s&nbsp; &nbsp; </b></font></a><img src=\"../pictures/reiter4.jpg\" align=absmiddle></td>\n",
			$pages[$topkat]["kategorien"][0]["page"],
			$pages[$topkat]["name"]);

	print("</tr></table>");

	print("\n<table cellspacing=0 cellpadding=4 border=0 width=\"100%\"><tr><td class=\"links1b\">");

	// die entsprechenden Unterkategorien durchlaufen
	for ($i = 0; $i < count($pages[$topkat]["kategorien"]); $i++) {

		if ($pages[$topkat]["kategorien"][$i]["page"] == $help_page)
			$picture = "../pictures/forumrot.gif";
		else
			$picture = "../pictures/forumgrau.gif";
		

		// Unterpunkte ausgeben, wenn ich die Rechte habe oder auf der Seite stehe
		if ($pages[$topkat]["perm"] == "" || $perm->have_perm($pages[$topkat]["perm"]) || $pages[$topkat]["kategorien"][$i]["page"] == $help_page) {
			printf("<img src=\"%s\" border=\"0\"><a href=\"$PHP_SELF?help_page=%s\"><font color=\"#000000\" size=2><b>%s&nbsp; &nbsp; </font></a>\n",
					$picture,
					$pages[$topkat]["kategorien"][$i]["page"],
					$pages[$topkat]["kategorien"][$i]["name"]);
		}
				
	}
	print("<br></td></tr><tr><td background=\"../pictures/reiter3.jpg\">&nbsp;</td></tr></table>");
	
}  // Ende Kartei-System


if (isset($help_page) && isset($druck)) {  // wir zeigen nur den Titel der Seite

	// die Hauptkategorien durchlaufen
	for ($i = 0; $i < count($pages); $i++) {

		// Wo sind wir denn gerade?
		for ($j = 0; $j < count($pages[$i]["kategorien"]); $j++) {
			if ($pages[$i]["kategorien"][$j]["page"] == $help_page) {
				$topkat = $i;
				$secondkat = $j;
			}
		}
	}

	printf("\n<font size=\"+2\"><b>%s</b></font><br>\n", $pages[$topkat]["name"]);
	printf("\n<i>%s</i><br><br>\n", $pages[$topkat]["text"]);
	printf("\n<font size=\"+1\"><b>%s</b></font><br>\n", $pages[$topkat]["kategorien"][$secondkat]["name"]);
	printf("\n<i>%s</i><br><br>\n", $pages[$topkat]["kategorien"][$secondkat]["text"]);

}  // Ende Titel


?>