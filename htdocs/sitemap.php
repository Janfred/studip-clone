<?php
/*
sitemap.php - Anzeige der Grundfunktionen von Stud.IP und Anpassung der Startseite
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

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head


// Struktur der sidemap, wandert spaeter in die config.inc

// status loest sich wie folgt auf: 	0 -> keine Anzeige
//							1 -> Anzeige
//							2 -> Anzeige und Default auf Startseite

//level 0 topkats
$sitemap ["veranstaltungen"]=array ("topkat"=>'', "name"=>"Veranstaltungen", "info"=>"&Uuml;bersicht &uuml;ber alle Ihre Veranstaltungen", "url"=>"meine_seminare.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["einrichtungen"]=array ("topkat"=>'', "name"=>"Einrichtungen", "info"=>"&Uuml;bersicht &uuml;ber alle Ihre Einrichtungen", "url"=>"meine_einrichtungen.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>2, "root"=>0);
$sitemap ["nutzer"]=array ("topkat"=>'', "name"=>"Nutzer", "info"=>"pers&ouml;nliche Nutzerdaten", "url"=>"about.php", "user"=>0, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["termine"]=array ("topkat"=>'', "name"=>"Termine", "info"=>"Ihr pers&ouml;nlicher Terminkalender", "url"=>"calendar.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>0, "root"=>0);
$sitemap ["kommunikation"]=array ("topkat"=>'', "name"=>"Kommunikation", "info"=>"Treten Sie mir anderen Nutzern in Kontakt", "url"=>"online.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>2);
$sitemap ["suche"]=array ("topkat"=>'', "name"=>"Suche", "info"=>"Suchen Sie was Sie wollen", "url"=>"suchen.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["hilfe"]=array ("topkat"=>'', "name"=>"Hilfe", "info"=>"Das Stud.IP Hilfesystem", "url"=>"/help/index.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["administration"]=array ("topkat"=>'', "name"=>"Administration", "info"=>"Verwalten Sie das System", "url"=>"aminarea_start.php?list=TRUE", "user"=>0, "autor"=>0, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>2);
$sitemap ["sonstiges"]=array ("topkat"=>'', "name"=>"Sonstiges", "info"=>"was sonst noch so von Interesse ist", "url"=>"impressum.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
//level 1
$sitemap ["veranstaltungen_uebersicht"]=array ("topkat"=>"veranstaltungen", "name"=>"Veranstaltungs&uuml;bersicht", "info"=>"&Uuml;bersicht &uuml;ber alle Ihre Veranstaltungen", "url"=>"meine_seminare.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["veranstaltungen_suche"]=array ("topkat"=>"veranstaltungen", "name"=>"Veranstaltungssuche", "info"=>"Finden Sie Veranstaltungen, die Sie interessieren", "url"=>"sem_portal.php?rest_all=TRUE", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["einrichtungen_uebersicht"]=array ("topkat"=>"einrichtungen", "name"=>"Einrichtungs&uuml;bersicht", "info"=>"&Uuml;bersicht &uuml;ber alle Ihre Einrichtungen", "url"=>"meine_einrichtungen.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>1);
$sitemap ["einrichtungen_suche"]=array ("topkat"=>"einrichtungen", "name"=>"Einrichtungssuche", "info"=>"Finden Sie ihre Einrichtungen", "url"=>"institut_browse.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["homepage"]=array ("topkat"=>"nutzer", "name"=>"Pers&ouml;nliche Homepage", "info"=>"Ihre Homepage", "url"=>"about.php", "user"=>0, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["nutzerdaten"]=array ("topkat"=>"nutzer", "name"=>"Nutzerdaten", "info"=>"pers&ouml;nliche Einstellungen zum Account in Stud.IP", "url"=>"edit_about.php?view=Benutzerdaten", "user"=>0, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["unidaten"]=array ("topkat"=>"nutzer", "name"=>"universit&auml;re Daten", "info"=>"Was treiben Sie an der Uni?", "url"=>"edit_about.php?view=Karriere", "user"=>0, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["nutzer_suche"]=array ("topkat"=>"nutzer", "name"=>"Benutzersuche", "info"=>"Finden Sie andere Benutzer", "url"=>"browse.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["nutzer_news"]=array ("topkat"=>"nutzer", "name"=>"News", "info"=>"erstellen Sie pers&ouml;nliche News auf ihrer Homepage", "url"=>"admin_news.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>2, "root"=>2);
$sitemap ["terminkalender"]=array ("topkat"=>"termine", "name"=>"Terminkalender", "info"=>"alle Ihre Termine m Griff", "url"=>"calendar.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["stundenplan"]=array ("topkat"=>"termine", "name"=>"Stundenplan", "info"=>"ihr Stundenplan", "url"=>"mein_stundenplan.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>0, "root"=>0);
$sitemap ["online"]=array ("topkat"=>"kommunikation", "name"=>"Wer ist online", "info"=>"wer ist ausser Ihnen online?", "url"=>"online.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["sms"]=array ("topkat"=>"kommunikation", "name"=>"Systeminterne Nachrchten", "info"=>"versenden Sie Nachrichten an andere Nutzer", "url"=>"sms.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["chat"]=array ("topkat"=>"kommunikation", "name"=>"Chat", "info"=>"der Stud.IP Chat", "url"=>"chat.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["buddies"]=array ("topkat"=>"kommunikation", "name"=>"Buddies verwalten", "info"=>"Ihre Freunde im System", "url"=>"edit_about.php?view=messenger", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["sms"]=array ("topkat"=>"kommunikation", "name"=>"Systeminterne Nachrchten", "info"=>"versenden Sie Nachrichten an andere Nutzer", "url"=>"sms.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["suche_nutzer"]=array ("topkat"=>"suche", "name"=>"Benutzersuche", "info"=>"Finden Sie andere Benutzer", "url"=>"browse.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["suche_veranstaltungen"]=array ("topkat"=>"suche", "name"=>"Veranstaltungssuche", "info"=>"Finden Sie Veranstaltungen, die Sie interessieren", "url"=>"browse.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["suche_einrichtungen"]=array ("topkat"=>"suche", "name"=>"Einrichtungssuche", "info"=>"Finden Sie ihre Einrichtungen", "url"=>"institut_browse.php", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["suche_archiv"]=array ("topkat"=>"suche", "name"=>"Archivsuche", "info"=>"St&ouml;bern Sie im Archiv", "url"=>"archiv.php", "user"=>1, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["hilfe_inhalt"]=array ("topkat"=>"hilfe", "name"=>"Inhaltsverzeichnis", "info"=>"der gesamte Inhalt", "url"=>"/help/index.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>1, "root"=>1);
$sitemap ["hilfe_print"]=array ("topkat"=>"hilfe", "name"=>"Druckversion", "info"=>"die Hilfe zum Ausdrucken", "url"=>"/help/index.php?druck=1", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["hilfe_quick"]=array ("topkat"=>"hilfe", "name"=>"Quick Tour", "info"=>"auf die Schnelle Stud.IP lernen", "url"=>"/help/index.php?help_page=schnelleinstieg.htm", "user"=>2, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["hilfe_faq"]=array ("topkat"=>"hilfe", "name"=>"h&auml;ugig gestellte Fragen", "info"=>"die am meisten auftrenden Probleme", "url"=>"/help/index.php?help_page=faq.htm", "user"=>2, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["admin_veranstaltungen"]=array ("topkat"=>"admin", "name"=>"Verantaltungsverwaltung", "info"=>"Veranstaltungseigenschaften verwalten", "url"=>"adminarea_start.php?list=TRUE", "user"=>0, "autor"=>0, "tutor"=>1,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["admin_einrichtungen"]=array ("topkat"=>"admin", "name"=>"Einrchtungsverwaltung", "info"=>"Einrichtungen verwalten", "url"=>"admin_literatur.php?list=TRUE", "user"=>0, "autor"=>0, "tutor"=>1,"dozent"=>1, "admin"=>2, "root"=>2);
$sitemap ["admin_global"]=array ("topkat"=>"admin", "name"=>"globale Einstellungen", "info"=>"Chefsache", "url"=>"new_user_md5.php", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>2, "root"=>2);
$sitemap ["impressum"]=array ("topkat"=>"sonstiges", "name"=>"Impressum", "info"=>"&uuml;ber das System...", "url"=>"impressum.php", "user"=>2, "autor"=>2, "tutor"=>2,"dozent"=>2, "admin"=>2, "root"=>2);
$sitemap ["ansprechpartner"]=array ("topkat"=>"sonstiges", "name"=>"Anpsrechpartner", "info"=>"Wer ist hier zust&auml;ndig?", "url"=>"impressum.php?view=ansprechpartner", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["entwickler"]=array ("topkat"=>"sonstiges", "name"=>"Entwickler", "info"=>"Wer bastelt was?", "url"=>"impressum.php?view=entwickler", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["statistik"]=array ("topkat"=>"sonstiges", "name"=>"Statistik", "info"=>"f&uuml;r Zahlenliebhaber", "url"=>"impressum.php?view=statistik", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>1, "root"=>1);
$sitemap ["history"]=array ("topkat"=>"sonstiges", "name"=>"History", "info"=>"was hat sich am System getan?", "url"=>"impressum.php?view=history", "user"=>1, "autor"=>1, "tutor"=>1,"dozent"=>1, "admin"=>2, "root"=>2);
//level 2
$sitemap ["veranstaltung_uebersicht"]=array ("topkat"=>"veranstaltungen_uebersicht", "name"=>"&Uuml;bersicht &uuml;ber eine Veranstaltung", "info"=>"wichtige Veranstaltungsdaten", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_forum"]=array ("topkat"=>"veranstaltungen_uebersicht", "name"=>"Forum", "info"=>"hier wird diskutiert", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_teilnehmer"]=array ("topkat"=>"veranstaltungen_uebersicht", "name"=>"Teilnehmer", "info"=>"wer nimmt alles Teil?", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_dateien"]=array ("topkat"=>"veranstaltungen_uebersicht", "name"=>"Dateien", "info"=>"Materialien in der Veranstaltung", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_literatur"]=array ("topkat"=>"veranstaltungen_uebersicht", "name"=>"Literatur", "info"=>"Literatur und Linklisten zu einer Veranstaltung", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
//level3
$sitemap ["veranstaltung_uebersicht_kurzinfo"]=array ("topkat"=>"veranstaltung_uebersicht", "name"=>"Kurzinfo", "info"=>"Alles auf einem Blick", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_uebersicht_details"]=array ("topkat"=>"veranstaltung_uebersicht", "name"=>"Details", "info"=>"die kompletten Kommentardaten", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_uebersicht_druckansicht"]=array ("topkat"=>"veranstaltung_uebersicht", "name"=>"Druckansicht", "info"=>"zum Ausdrucken", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_forum_themen"]=array ("topkat"=>"veranstaltung_forum", "name"=>"Themen", "info"=>"Themen im Forum", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_forum_neue"]=array ("topkat"=>"veranstaltung_forum", "name"=>"neue Beitr&auml;ge", "info"=>"Alle neuen Beitr&auml;ge seit dem letzten Login", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_forum_letzte5"]=array ("topkat"=>"veranstaltung_forum", "name"=>"letzte 5 Beitr&auml;ge", "info"=>"Die neuesten Beitr&auml;ge", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);
$sitemap ["veranstaltung_forum_suche"]=array ("topkat"=>"veranstaltung_forum", "name"=>"Suche", "info"=>"Suchen im Forum der Veranstaltung", "url"=>"", "user"=>0, "autor"=>0, "tutor"=>0,"dozent"=>0, "admin"=>0, "root"=>0);




function create_sitemap($sitemap, $depth=4, $id='', $level=0) {
	global $PHP_SELF, $auth;
	
	foreach ($sitemap as $key=>$val)  {
		if ($sitemap[$key]["topkat"] == $id) {
			print "<tr>";
			for ($i=0; $i<$level; $i++) {
				?>
				<td class="blank" width="<? echo (int)(80 / $depth) ?>%">
					&nbsp; 
				</td>
				<?
			}
			?>
				<td class="blank" width="<? echo (int)(80 / $depth) ?>%">
					<? 
					if ($val[$auth->auth["perm"]])
						printf ("<font size=-1><a href=\"%s%s\">%s%s%s</a></font>", $PHP_SELF, $val["link"], (!$level) ? "<b>" : "", $val["name"], (!$level) ? "</b>" : "");
					else
						printf ("<font size=-1>%s%s%s<font>", (!$level) ? "<b>" : "", $val["name"], (!$level) ? "</b>" : "");					
					?> 
				</td>
			<?
			for ($i=0; $i<=$depth-$level-1; $i++) {
				?>
				<td class="blank" width="<? echo (int)(80 / $depth) ?>%">
					&nbsp; 
				</td>
				<?
			}
			?>
				<td class="blank" width="20%" align="center">
					<?
					if ($val[$auth->auth["perm"]])
						printf ("<input type=\"CHECKBOX\" name=\"on_index[]\" %s />", ($val[$auth->auth["perm"]] == 2) ? "checked" : "");
					printf ("<input type=\"HIDDEN\" name=\"on_index[]\" value=\"_id_%s\" />", $key);
					?>
				</td>
			<?
			print "</tr>";
			create_sitemap ($sitemap, $depth, $key, $level+1);
		}
	}
}

$preview = "";
foreach ($sitemap as $val) {
	if ($val[$auth->auth["perm"]] == 2 && !$val["topkat"])
		$preview .= sprintf ("<img src=\"./pictures/forumrot.gif\">&nbsp; <a href=\"%s%s\"><font size=\"-1\">%s</font></a><br>", $PHP_SELF, $val["link"], $val["name"]);
}


// die Infobox der Seite

$infobox = array	(			
	array  ("kategorie"  => "Vorschau Ihrer Startseite:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/blank.gif",
								"text"  => $preview
								)
		)
	),
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
	)
);

// Anzeigeteil

?>
	 <table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="topic" colspan="3">
			<img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b>Stud.IP Sitemap</b>
		</td>
	</tr>
	 <tr>
	 	<td class="blank" colspan="3">&nbsp; 
	 	</td>
	 </tr>
	 <tr>
		 <td valign="top" class="blank" width="90%" align="center">
		 	<form action="<? echo $PHP_SELF?>?cmd=change_index" method="POST">
				<table align="center" width="95%"border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td class="blank" width="80%" colspan="4">
						<b><font size=-1>Struktur</font></b>
					</td>
					<td class="blank" width="80%" colspan="4">
						<b><font size=-1>erscheint auf Startseite</font></b>					
					</td>
				</tr>
		 		<?
				create_sitemap($sitemap);
				?>
				</table>
			<br>&nbsp; 
			<br>&nbsp; 		
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