<?php
/*
sidemap.php - Anzeige der Grundfunktionen von Stud.IP und Anpassung der Startseite
Copyright (C) 2002 	Ralf Stockmann <rstockm@gwdg.de>

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
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 		// Klarnamen fuer den Veranstaltungsstatus
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php"); 		// htmlReady fuer die Veranstaltungsnamen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); 		// Semester-Namen fuer Admins

$db=new DB_Seminar;

// we are defintely not in an lexture or institute$SessSemName[0] = "";
$SessSemName[0] = "";
$SessSemName[1] = "";
$links_admin_data =''; 	//Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head


// Struktur der sidemap, wandert spaeter in die config.inc

// status loest sich wie folgt auf: 	0 -> keine Anzeige
//							1 -> Anzeige
//							2 -> Anzeige und Default auf Startseite

$sidemap = array	(			
	array  ("kategorie"  => "Veranstaltungen",
		"eintrag" => array	(	
						array (	"name" => "Veranstaltungs&uuml;bersicht",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "meine_seminare.php",
								"user"  => "1",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "2",
								"root"  => "1"								
								),
						array (	"name" => "Veranstaltungssuche",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "sem_portal.php?view=Alle&reset_all=TRUE",
								"user"  => "1",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "2",
								"root"  => "1"								
								),
						array (	"name" => "neue Veranstaltung anlegen",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "admin_seminare_assi.php?new_session=TRUE",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "2",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Veranstaltungsverwaltung",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "adminarea_start.php?list=TRUE",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "1",
								"admin"  => "2",
								"root"  => "1"								
								),
						array (	"name" => "Statusgruppen in Veranstaltungen verwalten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "admin_statusgruppe.php?list=TRUE&view=statusgruppe_sem",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								)								
		)
	),
	array  ("kategorie" => "Einrichtungen",
	       "eintrag" => array	(	
						array (	"name" => "Einrichtungs&uuml;bersicht",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "meine_einrichtungen.php",
								"user"  => "1",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "2",
								"root"  => "1"								
								),
						array (	"name" => "Einrichtungssuche",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "institut_browse.php",
								"user"  => "1",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "2",
								"root"  => "1"								
								),
						array (	"name" => "Einrichtungsverwaltung",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "admin_institut.php?list=TRUE",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "0",
								"admin"  => "2",
								"root"  => "2"								
								),
						array (	"name" => "neue Einrichtung anlegen",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "admin_institut.php?i_view=new",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "0",
								"admin"  => "0",
								"root"  => "1"								
								),
						array (	"name" => "Mitarbeiter verwalten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "inst_admin.php?list=TRUE",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "0",
								"admin"  => "2",
								"root"  => "1"								
								),
						array (	"name" => "Statusgruppen in Einrichtungen verwalten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "admin_statusgruppe.php?list=TRUE&view=statusgruppe_inst",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "0",
								"admin"  => "1",
								"root"  => "1"								
								)
		)
	),
	array  ("kategorie" => "Nutzer",
	       "eintrag" => array	(	
						array (	"name" => "Eigene Homepage",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "about.php",
								"user"  => "2",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Nutzerdaten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "edit_about.php?view=Daten",
								"user"  => "2",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Nutzersuche",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "browse.php",
								"user"  => "2",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "2",
								"root"  => "2"								
								),
						array (	"name" => "Nutzerverwaltung",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "new_user_md5.php",
								"user"  => "0",
								"autor"  => "0",
								"tutor"  => "0",
								"dozent"  => "0",
								"admin"  => "2",
								"root"  => "2"								
								),
						array (	"name" => "Stud.IP Score",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "inst_admin.php?list=TRUE",
								"user"  => "2",
								"autor"  => "2",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								)
		)
	),
	array  ("kategorie" => "Termine",
	       "eintrag" => array	(	
						array (	"name" => "Terminkalender",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "calendar.php",
								"user"  => "0",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "0",
								"root"  => "0"								
								),
						array (	"name" => "neuer Termin",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "calendar.php?cmd=edit",
								"user"  => "0",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "0",
								"root"  => "0"								
								),
						array (	"name" => "Stundenlan",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "mein_stundenplan.php",
								"user"  => "0",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "0",
								"root"  => "0"								
								)
		)
	),
	array  ("kategorie" => "Kommunikation",
	       "eintrag" => array	(	
						array (	"name" => "Wer ist online...",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "online.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Buddies verwalten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "online.php?change_view=TRUE#buddy_anker",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Chat",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "chat.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Kurznachrichten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "sms.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "News verwalten",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "admin_news.php?view=news_sem",
								"user"  => "0",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "2",
								"root"  => "1"								
								)
		)
	),
	array  ("kategorie" => "Suche",
	       "eintrag" => array	(	
						array (	"name" => "Nutzersuche",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "browse.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Veranstaltungssuche",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "sem_portal.php?view=Alle&reset_all=TRUE",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Einrichtungssuche",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "institut_browse.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Archivierte Veranstaltungen",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "archiv.php",
								"user"  => "1",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "1",
								"root"  => "1"								
								)
		)
	),
	array  ("kategorie" => "Hilfe",
	       "eintrag" => array	(	
						array (	"name" => "Hilfe - Inhaltsverzeichnis",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "./help/index.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Druckdokumentation",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "./help/index.php?druck=1",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Quick - Tour",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "./help/index.php?help_page=schnelleinstieg.htm",
								"user"  => "2",
								"autor"  => "2",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Glossar",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "./help/index.php?help_page=glossar.htm",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "F.A.Q.",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "./help/index.php?help_page=faq.htm",
								"user"  => "1",
								"autor"  => "2",
								"tutor"  => "2",
								"dozent"  => "2",
								"admin"  => "1",
								"root"  => "1"								
								)
		)
	),
	array  ("kategorie" => "Sonstiges",
	       "eintrag" => array	(	
						array (	"name" => "Impressum",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "impressum.php",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Ansprechpartner",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "impressum.php?view=ansprechpartner",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Statistik",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "impressum.php?view=statistik",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "2",
								"root"  => "2"								
								),
						array (	"name" => "Versions-History",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "impressum.php?view=history",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								),
						array (	"name" => "Portalseite",
								"info"  => "Auf dieser Seite sehen sie...",
								"url" => "http://www.studip.de",
								"user"  => "1",
								"autor"  => "1",
								"tutor"  => "1",
								"dozent"  => "1",
								"admin"  => "1",
								"root"  => "1"								
								)
		)
	)

);






function print_sidemap ($content) {
global $PHP_SELF, $auth;
$print = "<table align=\"left\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";

// die Rubriken
	
for ($i = 0; $i < count($content); $i++) { $print .= "
			<tr>
				<td class=\"blank\">
					<font size=\"-1\"><b>".$content[$i]["kategorie"]."</b></font>
				</td>";
	if ($i ==0) {
		$print .= "<td valign=\"top\" align=\"right\">
					<img src = \"pictures/home.gif\" ".tooltip("Auf Ihrer Startseite verankert").">
				</td>";
	} else {
		$print .= "<td>
					&nbsp; 
				</td>";
	}	
	$print .= "</tr>";
	
	// hier die einzelnen Links
	
	for ($j = 0; $j < count($content[$i]["eintrag"]); $j++) { 
		if ($content[$i]["eintrag"][$j][$auth->auth["perm"]] > 0) {    // bekomme ich den Link zu sehen
			if ($content[$i]["eintrag"][$j][$auth->auth["perm"]] > 1) {   // ist der Link ein Preset?
				$checked="checked";
			} else {
				$checked="";
			}
			$print .= "		<tr>
								<td class=\"blank\" nowrap>
									<img src = \"pictures/blank.gif\" width=\"10\">
									<a href=\"".$content[$i]["eintrag"][$j]["url"]."\" ".tooltip($content[$i]["eintrag"][$j]["info"])."><font size=\"-1\">".$content[$i]["eintrag"][$j]["name"]."</font></a><br>
								</td>
								<td valign=\"top\">
									&nbsp; &nbsp; &nbsp; <input type=checkbox name='on_index[]' $checked value='1'>
								</td>
							</tr>";
		}
	}
	$print .= "<tr><td>&nbsp; </td></tr>";
}
$print .= "<tr><td colspan = \"2\" align=\"right\">	<br>	<input type=\"IMAGE\" name=\"submit\" src=\"./pictures/buttons/uebernehmen-button.gif\" border=\"0\"></td></tr>";
$print .= "</table>";
echo $print;
}



$content = $sidemap;
$preview = "";
for ($i = 0; $i < count($content); $i++)  {
	for ($j = 0; $j < count($content[$i]["eintrag"]); $j++) { 
		if ($content[$i]["eintrag"][$j][$auth->auth["perm"]] > 1) {    // bekomme ich den Link zu sehen
			$preview .= "<img src=\"./pictures/forumrot.gif\">&nbsp; <a href=\"".$content[$i]["eintrag"][$j]["url"]."\"><font size=\"-1\">".$content[$i]["eintrag"][$j]["name"]."</font></a><br>";
		}
	}
}

// die Infobox der Seite

$infobox = array	(			
	array  ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => "Auf dieser Sidemap sehen Sie die grundlegenden Funktionsseiten des Stud.IP Systems."
								)
		)
	),
	array  ("kategorie" => "Aktionen:",
	       "eintrag" => array	(	
						array	 (	"icon" => "pictures/suchen.gif",
								"text"  => "Klicken Sie auf den Namen, um die entsprechende Seite zu besuchen. "
								),
						array	 (	"icon" => "pictures/home.gif",
								"text"  => "Um die entsprechende Seite auf Ihrer Startseite zu verankern, w&auml;hlen Sie die entsprechende Box an!"
								)
		)
	),
	array  ("kategorie"  => "Vorschau Ihrer Startseite:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/blank.gif",
								"text"  => $preview
								)
		)
	)
);






// Anzeigeteil

?>
	 <table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="topic" colspan="3">
			<img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b>Stud.IP Sidemap</b>
		</td>
	</tr>
	 <tr>
	 	<td class="blank" colspan="3">&nbsp; 
	 	</td>
	 </tr>
	 <tr>
		 <td valign="top" class="blank" width="90%" align="center">
		 	<form action="<? echo $PHP_SELF?>?cmd=change_index" method="POST">
		 	<blockquote>
			<?
			print_sidemap($sidemap);
			?>
			</blockquote>
			<br>

			</form>
		</td>
		<td class="blank" width="290" align="right" valign="top">
			<?
			print_infobox ($infobox,"pictures/seminare.jpg");
			?>				
			<br />
		</td>
		 <td valign="top" class="blank" width="1%" align="center">
			<img src="pictures/blank.gif" border="0" width="15">
		</td>
	</tr>
<?
// Save data back to database.
ob_end_flush(); //Outputbuffering beenden
page_close();
 ?>
</table>
<!-- $Id$ -->