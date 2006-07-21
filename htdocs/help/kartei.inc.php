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

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

if (!isset($druck)) {  // Kopfzeile anzeigen
	?>

	<table class="header" border="0" width="100%" cellspacing="0" cellpadding="0" bordercolor="#999999" height="25">
		<tr>
			<td class="header" width="33%" align="center">
				<a href="index.php" target="_top"><font color="#FFFFFF" size="4"><b><?=_("Inhaltsverzeichnis")?></b></font></a>
			</td>
			<td class="header" width="33%" align="center">
				<a href="index.php?druck=1<?if(isset($help_page)) echo "&help_page=".$help_page?>" target="_top"><font color="#FFFFFF" size="4"><b><?=_("Druckansicht")?></b></font></a>
			</td>
			<td class="header" width="33%" align="center" valign="middle">
				<a href="../impressum.php"><img border="0" src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="1" width="45"><br><img border="0" src="<?= $GLOBALS['ASSETS_URL'] ?>images/logo2.gif" alt="<?=_("Impressum")?>" valign="middle"></a>
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
	$tooltip = "Dieser Abschnitt der Hilfe erklärt: " . $pages[$topkat]["text"];
	printf("<td class=\"links1b\">&nbsp; <img align=\"absmiddle\" src=\"".$GLOBALS['ASSETS_URL']."images/info.gif\" %s ", tooltip($tooltip, TRUE, $auth->auth["jscript"]));
	printf("border=\"0\"></b>&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/reiter1.jpg\" align=\"absmiddle\"></td>");

	printf("<td class=\"links1b\" align=\"right\" nowrap><a class=\"links1b\" href=\"$PHP_SELF?help_page=%s\">&nbsp; &nbsp; %s&nbsp; &nbsp; </a><img src=\"".$GLOBALS['ASSETS_URL']."images/reiter4.jpg\" align=absmiddle></td>\n",
			$pages[$topkat]["kategorien"][0]["page"],
			htmlReady($pages[$topkat]["name"]));

	print("</tr></table>");

	print("\n<table cellspacing=0 cellpadding=4 border=0 width=\"100%\"><tr><td class=\"links1b\">");

	// die entsprechenden Unterkategorien durchlaufen
	for ($i = 0; $i < count($pages[$topkat]["kategorien"]); $i++) {

		if ($pages[$topkat]["kategorien"][$i]["page"] == $help_page)
			$picture = $GLOBALS['ASSETS_URL']."images/forumrot.gif";
		else
			$picture = $GLOBALS['ASSETS_URL']."images/forumgrau.gif";


		// Unterpunkte ausgeben, wenn ich die Rechte habe oder auf der Seite stehe
		if ($pages[$topkat]["perm"] == "" || $perm->have_perm($pages[$topkat]["perm"]) || $pages[$topkat]["kategorien"][$i]["page"] == $help_page) {
			printf("<img src=\"%s\" border=\"0\"><a class=\"links1b\" href=\"$PHP_SELF?help_page=%s\">%s&nbsp; &nbsp; </a>\n",
					$picture,
					$pages[$topkat]["kategorien"][$i]["page"],
					htmlReady($pages[$topkat]["kategorien"][$i]["name"]));
		}

	}
	print("<br></td></tr><tr><td background=\"".$GLOBALS['ASSETS_URL']."images/reiter3.jpg\">&nbsp;</td></tr></table>");

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

	printf("\n<font size=\"+2\"><b>%s</b></font><br>\n", htmlReady($pages[$topkat]["name"]));
	printf("\n<i>%s</i><br><br>\n", htmlReady($pages[$topkat]["text"]));
	printf("\n<font size=\"+1\"><b>%s</b></font><br>\n", htmlReady($pages[$topkat]["kategorien"][$secondkat]["name"]));
	printf("\n<i>%s</i><br><br>\n", htmlReady($pages[$topkat]["kategorien"][$secondkat]["text"]));

}  // Ende Titel


?>
