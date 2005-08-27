<?php
/*
help/structure.inc.php - die Struktur der Hilfeseiten von Stud.IP
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

modifiziert 2002 von:
Cornelis Kater <ckater@gwdg.de>,
Marco Bohnsack <Silencer@www.funcity.de>


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

$pages = array	(	
array	(	"name" => _("Allgemeines"),
				"text" => _("Einige generelle Informationen zu Stud.IP"),
				"perm" =>	"",
				"kategorien" => array	(	
																array	(	"name" => _("Einleitung"),
																				"text" => _("Das Ziel von Stud.IP"),
																				"page" => "help1.html"
																			),
																array	(	"name" => _("Hilfe zur Hilfe"),
																				"text" => _("Was Sie über diese Hilfefunktion wissen sollten"),
																				"page" => "help_help.html"
																			),
																			
																array	(	"name" => _("Spracheinstellungen"),
																				"text" => _("Deutsch oder Englisch?"),
																				"page" => "iii_homepagef1.htm"
																			),
																			
																array	(	"name" => _("Nutzungsbedingungen"),
																				"text" => _("Die rechtlichen Grundlagen"),
																				"page" => "nutzung.html"
																			),
																array	(	"name" => _("neue Funktionen in Stud.IP"),
																				"text" => _("neue Funktionen in jeder Version"),
																				"page" => "whatsnew.htm"
																			)
															)
			),
			
array	(	"name" => _("Die Anmeldung"),
				"text" => _("Alles, was Sie über die Anmeldung wissen müssen"),
				"perm" =>	"",
				"kategorien" => array	(	
																array	(	"name" => _("Zugang zum System"),
																				"text" => _("Wie komme ich in Stud.IP?"),
																				"page" => "ii_zugang.htm"
																			),
																array	(	"name" => _("Vorteile der Anmeldung"),
																				"text" => _("Warum soll ich mich anmelden?"),
																				"page" => "ii_vorteile_anmeldung.htm"
																			),
																array	(	"name" => _("Die Registrierung"),
																				"text" => _("Was muss ich tun um mich anzumelden?"),
																				"page" => "ii_anmeldeformular.htm"
																			),
																array	(	"name" => _("Die Bestätigungsmail"),
																				"text" => _("nur noch ein kleiner Schritt..."),
																				"page" => "ii_bestaetigungsmail.htm"
																			),
																array	(	"name" => _("Die Login-Seite"),
																				"text" => _("Der erste Login"),
																				"page" => "ii_login.htm"
																			),
																array	(	"name" => _("Passwort vergessen?"),
																				"text" => _("Nur keine Panik..."),
																				"page" => "ii_passwort.htm"
																			)
															)
			),



array	(	"name" => _("Erste Schritte"),
				"text" => _("Eine Kurzeinweisung speziell für Neulinge"),
				"perm" =>	"",
				"kategorien" => array	(	
																array	(	"name" => _("Die Startseite"),
																				"text" => _("Ihre Übersichtsseite nach jedem Login"),
																				"page" => "startseite.htm"
																			),
																
																array	(	"name" => _("Schnelleinstieg"),
																				"text" => _("Das Wichtigste in Kürze"),
																				"page" => "schnelleinstieg.htm"
																			),
																array	(	"name" => _("Die eigene Homepage"),
																				"text" => _("Erzählen Sie der Welt von sich..."),
																				"page" => "iii_homepage.htm"
																			)
																
															)
			),


array	(	"name" => _("Die eigene Homepage"),
				"text" => _("Ihre private Ecke in Stud.IP"),
				"perm" =>	"autor",
				"kategorien" => array	(	
																array	(	"name" => _("Persönliche Homepage"),
																				"text" => _("In 5 Minuten eingerichtet!"),
																				"page" => "iii_homepage.htm"
																			),
																array	(	"name" => _("Eigenes Bild"),
																				"text" => _("Bleiben Sie nicht im Dunkeln"),
																				"page" => "iii_homepagea.htm"
																			),
																array	(	"name" => _("Persönlichen Daten"),
																				"text" => _("Was muß, was kann?"),
																				"page" => "iii_homepageb.htm"
																			),
																array	(	"name" => _("Universitäre Daten"),
																				"text" => _("Was tun Sie so an der Uni?"),
																				"page" => "iii_homepagec.htm"
																			),
																array	(	"name" => _("Lebenslauf"),
																				"text" => _("Und was machen Sie sonst noch?"),
																				"page" => "iii_homepaged.htm"
																			),
																array	(	"name" => _("Sonstiges"),
																				"text" => _("Eigene Kategorien anlegen"),
																				"page" => "iii_homepagee.htm"
																			),
																			array	(	"name" => _("Tools"),
																				"text" => _("Umfragen, Literaturlisten und News"),
																				"page" => "iii_homepageg.htm"
																			),
																			array	(	"name" => _("MyStudip"),
																				"text" => _("Stud.IP anpassen"),
																				"page" => "iii_homepagef1.htm"
																			),
															)
			),
			
			

array	(	"name" => _("Interaktion"),
				"text" => _("Wie Sie mit anderen Nutzenden des Systems interagieren können"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Die Homepages der anderen"),
																				"text" => _("Wie Sie die Homepages anderen Nutzenden finden können"),
																				"page" => "iv_interaktion.htm"
																			),
																array	(	"name" => _("Wer ist online?"),
																				"text" => _("Wie Sie herausfinden, wer ausser Ihnen gerade im System ist"),
																				"page" => "iv_online.htm"
																			),
																array	(	"name" => _("Systeminterne Nachrichten"),
																				"text" => _("Wie Sie Nachrichten an andere Nutzenden schicken können"),
																				"page" => "iv_sms.htm"
																			),
																array	(	"name" => _("Der Chatbereich"),
																				"text" => _("Wo und wie Sie in Stud.IP chatten können"),
																				"page" => "iv_chat.htm"
																			)
															)
			),
			
	array	(	"name" => _("Meine Einrichtungen"),
				"text" => _("Fakultäten, Institute, Seminare an denen Sie studieren oder arbeiten"),
				"perm" =>	"user",
				"kategorien" => array	(						array	(	"name" => _("Informationen über Einrichtungen "),
																				"text" => _("Adressen, Mitarbeiterlisten und mehr"),
																				"page" => "institut_main.htm"
																			),
																array	(	"name" => _("Die Einrichtungssuche"),
																				"text" => _("Einrichtungen in Stud.IP finden"),
																				"page" => "xii_suche_einr.htm"
																			),
																
																
																array	(	"name" => _("Zuordnung zu Einrichtungen"),
																				"text" => _("Wie Sie sich Einrichtungen zuordnen können"),
																				"page" => "iii_homepagec.htm"
																			)
																
											)
			),
			
			
array	(	"name" => _("Meine Veranstaltungen"),
				"text" => _("Meine Veranstaltungen - hinzufügen, löschen, verwalten"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Veranstaltungen abonnieren"),
																				"text" => _("Wie Sie Veranstaltungen zu 'Meine Veranstaltungen' hinzufügen"),
																				"page" => "v_abonnieren.htm"
																			),
																array	(	"name" => _("Der Veranstaltungs-Browser"),
																				"text" => _("Die Veranstaltungssuchmaschine"),
																				"page" => "v_sembrowse.htm"
																			),
																array	(	"name" => _("Was ist neu?"),
																				"text" => _("Alle Neuigkeiten im Blick"),
																				"page" => "v_neu.htm"
																			),
																array	(	"name" => _("Veranstaltungen ordnen"),
																				"text" => _("Ordnung in die Veranstaltungsübersicht bringen"),
																				"page" => "v_ordnen.htm"
																			),
																array	(	"name" => _("Abonnements kündigen"),
																				"text" => _("Wie Sie Veranstaltungen aus 'Meine Veranstaltungen' entfernen"),
																				"page" => "v_kuendigen.htm"
																			)																		
															)
			),
			
array	(	"name" => _("In der Veranstaltung: grundlegende Funktionen"),
				"text" => _("Wie Sie sich im Veranstaltungsbereich zurechtfinden"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Navigation"),
																				"text" => _("Die Bedienlogik des Veranstaltungsbereichs"),
																				"page" => "vi_navi.htm"
																			),
																array	(	"name" => _("Kurzinfo"),
																				"text" => _("Die Startseite im Veranstaltungsbereich"),
																				"page" => "vi_kurz.htm"
																			),
																array	(	"name" => _("Detailansicht"),
																				"text" => _("Erweiterte Informationen"),
																				"page" => "vi_detail.htm"
																			),
																array	(	"name" => _("Druckansicht"),
																				"text" => _("alles auf einen Blick"),
																				"page" => "vi_druckansicht.htm"
																			),
																array	(	"name" => _("Teilnehmer"),
																				"text" => _("Personen in der Veranstaltung"),
																				"page" => "vi_teilnehmer.htm"
																			),
																array	(	"name" => _("Funktionen / Gruppen"),
																				"text" => _("Nutzer in Gruppen organisieren"),
																				"page" => "vi_statusgruppen_show.htm"
																			),

																array	(	"name" => _("Ablaufplan"),
																				"text" => _("Termine finden"),
																				"page" => "vi_ablauf.htm"
																			),
																array	(	"name" => _("Literatur & Links"),
																				"text" => _("Materialien für die Veranstaltung"),
																				"page" => "vi_literatur.htm"
																			)																		
															)
			),
			
			
			
array	(	"name" => _("In der Veranstaltung: das Forum"),
				"text" => _("Diskutieren & streiten"),
				"perm" =>	"user",
				"kategorien" => array	(	
																
																			
																array	(	"name" => _("Funktionen des Forums"),
																				"text" => _("Bedienlogik, Ansichten, Postings verfassen"),
																				"page" => "ix_forum1.htm"
																			),
																array	(	"name" => _("Neue Funktionen in der 0.9.5"),
																				"text" => _("Erweiterungen zu den Grundfunktionen"),
																				"page" => "ix_forumneu.htm"
																			),
																array	(	"name" => _("Einstellungen des Forums"),
																				"text" => _("Schonen Sie Ihr Modem"),
																				"page" => "iii_homepagef2.htm"
																			),
																array	(	"name" => _("Neue Beiträge"),
																				"text" => _("Was gibt´s Neues?"),
																				"page" => "ix_forum2.htm"
																			),
																array	(	"name" => _("Letzte 5 Beiträge"),
																				"text" => _("Was als letztes los war"),
																				"page" => "ix_forum3.htm"
																			),
																array	(	"name" => _("Suchen"),
																				"text" => _("Finden eines bestimmten Postings"),
																				"page" => "ix_forum4.htm"
																			)
																
																			
																			
																			
																			
															)
			),
array	(	"name" => _("In der Veranstaltung: die Dateiverwaltung"),
				"text" => _("Wie Sie Dateien in das System einstellen und herunterladen"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Dateien herunterladen"),
																				"text" => _("Die Dateiverwaltung und wie Sie Dateien herunterladen"),
																				"page" => "vii_download.htm"
																			),
																array	(	"name" => _("Dateien einstellen"),
																				"text" => _("Wie Sie neue Dateien ins System einstellen"),
																				"page" => "vii_upload.htm"
																			),
																array	(	"name" => _("Dateien bearbeiten"),
																				"text" => _("Verschieben, löschen oder die Beschreibung ändern"),
																				"page" => "vii_move.htm"
																			)
																
																)
			));
			
			

//show help for WikiWikiWeb, if available
if ($GLOBALS["WIKI_ENABLE"]) {
	$pages[] = array("name" => _("In der Veranstaltung: das Wiki"),
			"text" => _("Wie Sie gemeinsam Texte verfassen"),
			"perm" =>	"user",
			"kategorien" => array	(	
				array(	"name" => _("Das WikiWikiWeb"),
					"text" => _("Die kollaborative Schreibumgebung"),
					"page" => "wiki_all.htm"
					),
				)
			);
}
			
			
	$pages[]=array	(	"name" => _("Der Veranstaltungs-Assistent"),
				"text" => _("Veranstaltungen anlegen -ganz einfach!"),
				"perm" =>	"dozent",
				"kategorien" => array	(	
																array	(	"name" => _("Grunddaten"),
																				"text" => _("Name, Beschreibung, Raum u.a."),
																				"page" => "va_assi1.htm"
																			),
																array	(	"name" => _("Personendaten, Typ und Sicherheit"),
																				"text" => _("DozentInnen, TutorInnen und Passwörter"),
																				"page" => "va_assi2.htm"
																			),
																array	(	"name" => _("Termindaten"),
																				"text" => _("Wann finden Sitzungen statt?"),
																				"page" => "va_assi3.htm"
																			),
																array	(	"name" => _("Sonstiges"),
																				"text" => _("Voraussetzungen, Lernorganisation, Leistungsnachweis, Sonstiges"),
																				"page" => "va_assi4.htm"
																			),
																array	(	"name" => _("Bereit zum Anlegen"),
																				"text" => _("Fast fertig!"),
																				"page" => "va_assi5.htm"
																			), 
																array	(	"name" => _("Literatur- und Linkliste"),
																				"text" => _("Bücher und Webquellen anlegen"),
																				"page" => "va_assi6.htm"
																			), 
																array	(	"name" => _("Ablaufplan und Termine"),
																				"text" => _("Manuell oder automatisch anlegen"),
																				"page" => "va_assi7.htm"
																			) 
															)
			);
			
$pages[]=array	(	"name" => _("Veranstaltungen verwalten"),
				"text" => _("Wie Sie Ihre Veranstaltungen anlegen und aktuell halten"),
				"perm" =>	"tutor",
				"kategorien" => array	(	
																array	(	"name" => _("Die Administrierungsseite"),
																				"text" => _("Zugang zur Veranstaltungsverwaltung"),
																				"page" => "x_adminarea.htm"
																			),
																
																array	(	"name" => _("Basisdaten ändern"),
																				"text" => _("Wie Sie die Basisdaten einer Veranstaltung ändern können"),
																				"page" => "x_aendern.htm"
																			),
																array	(	"name" => _("Literatur / Links"),
																				"text" => _("Wie Sie die Literatur und Linklisten Ihrer Veranstaltungen anpassen"),
																				"page" => "x_literatur.htm"
																			),
																array	(	"name" => _("Ablaufplan eingeben / ändern"),
																				"text" => _("Wie Sie den Ablaufplan einer Veranstaltung verwalten können"),
																				"page" => "x_ablauf.htm"
																			),
																array	(	"name" => _("Zeiten ändern"),
																				"text" => _("Wie Sie die Veranstaltungszeiten ändern können"),
																				"page" => "x_metadates.htm"
																			),
																array	(	"name" => _("Zugangsberechtigungen ändern"),
																				"text" => _("Wie Sie die Zugangsberechtigungen für Veranstaltungen ändern können"),
																				"page" => "x_admission.htm"
																			),
																array	(	"name" => _("Themen anlegen"),
																				"text" => _("Wie Sie Debattenthemen für das Forum vorgeben können"),
																				"page" => "x_themen.htm"
																			), 
																array	(	"name" => _("Teilnehmer verwalten"),
																				"text" => _("Wie Sie Teilnehmer verwalten und Tutoren ernennen können"),
																				"page" => "x_teil.htm"
																			), 
																array	(	"name" => _("Funktionen / Gruppen verwalten"),
																				"text" => _("Wie Sie Teilnehmer mit Funktionen oder Gruppen organisieren"),
																				"page" => "x_statusgruppen_admin.htm"
																			), 

																array	(	"name" => _("Dateiordner verwalten"),
																				"text" => _("Wie Sie Dateiordner verwalten können"),
																				"page" => "x_datei.htm"
																			), 
																array	(	"name" => _("News anlegen und verwalten"),
																				"text" => _("Wie Sie die neuesten Neuigkeiten unters Volk bringen"),
																				"page" => "x_admin_news.htm"
																			) 
															)
			);
			
$pages[]=array	(	"name" => _("Terminkalender und Stundenplan"),
			"text" => _("Ihr Timeplaner im Netz"),
			"perm" =>	"user",
			"kategorien" => array	(	
															array	(	"name" => _("Der Stundenplan"),
																				"text" => _("Praktisch und einfach"),
																				"page" => "stupla.htm"
																			),
																array	(	"name" => _("Der Terminkalender"),
																				"text" => _("Bedienung und Ansichten"),
																				"page" => "termin1.htm"
																			),
																array	(	"name" => _("Termine bearbeiten"),
																				"text" => _("Anlegen und Ändern von Terminen"),
																				"page" => "termin2.htm"
																			),
																array	(	"name" => _("Termine einbinden"),
																				"text" => _("Veranstaltungstermine im Terminkalender anzeigen"),
																				"page" => "termin3.htm"
																			),
																array	(	"name" => _("Ansicht anpassen"),
																				"text" => _("Optionen des Terminkalenders"),
																				"page" => "iii_homepagef3.htm"
																			)
																
															)
			);
			
$pages[] = array	(	"name" => _("MyStud.IP"),
				"text" => _("Stud.IP anpassen"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Allgemeines"),
																				"text" => _("Sprach- und Geschwindigkeitseinstellungen"),
																				"page" => "iii_homepagef1.htm"
																			),
																array	(	"name" => _("Einstellungen des Forums"),
																				"text" => _("Schonen Sie Ihr Modem"),
																				"page" => "iii_homepagef2.htm"
																			),
																array	(	"name" => _("Einstellungen des Terminkalenders"),
																				"text" => _("Die Zeit läuft -aber wie schnell?"),
																				"page" => "iii_homepagef3.htm"
																			),
																array	(	"name" => _("Einstellungen des Stundenplans"),
																				"text" => _("Haben Sie heute frei?"),
																				"page" => "iii_homepagef4.htm"
																			),
																array	(	"name" => _("Einstellungen des Messaging"),
																				"text" => _("So bleiben Sie in Kontakt"),
																				"page" => "iii_homepagef5.htm"
																			),
																array	(	"name" => _("Auto-LogIn"),
																				"text" => _("Wenn es bequem sein soll"),
																				"page" => "iii_homepageh.htm"
																			)
															)
			);
			
$pages[] = array	(	"name" => _("Suchen"),
				"text" => _("Was möchten Sie finden?"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Die Suchauswahl"),
																				"text" => _("Was genau suchen Sie?"),
																				"page" => "xii_suchen1.htm"
																			),
																array	(	"name" => _("Suchen nach Personen"),
																				"text" => _("DozentInnen und KommilitonInnen"),
																				"page" => "personensuche.htm"
																			),
																array	(	"name" => _("Suchen nachVeranstaltungen"),
																				"text" => _("Veranstaltungen des aktuellen und kommender Semester"),
																				"page" => "v_abonnieren.htm"
																			),
																			array	(	"name" => _("Suchen nach Einrichtungen"),
																				"text" => _("Fakultäten, Institute, ..."),
																				"page" => "xii_suche_einr.htm"
																			),
/*																array	(	"name" => _("Suchen nach Ressourcen"),
																				"text" => _("Suche nach Räumen, Ausstattung, etc."),
																				"page" => "xii_suche_res.htm"
																			),*/
																array	(	"name" => _("Suchen im Archiv"),
																				"text" => _("Veranstaltungen vergangener Semester"),
																				"page" => "xii_suchen3.htm"
																			)
															)
			);

$pages[] = array	(	"name" => _("Verschiedenes"),
				"text" => _("Textformatierungen, FAQ u.a."),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Formatierungen von Text"),
																				"text" => _("Fett, kursiv, Aufzählungen und mehr"),
																				"page" => "ix_forum6.htm"
																			),
																array	(	"name" => _("Smilies"),
																				"text" => _("Zeigen Sie Gefühl"),
																				"page" => "ix_forum7.htm"
																			),
																array	(	"name" => _("Score-Liste"),
																				"text" => _("Die Stud.IP-Rangliste"),
																				"page" => "score.htm"
																			),
																array	(	"name" => _("WAP"),
																				"text" => _("Der Handy-Zugang zu Stud.IP"),
																				"page" => "wap_help.htm"
																			),
																array	(	"name" => _("Glossar"),
																				"text" => _("Kurze Erklärungen"),
																				"page" => "glossar.htm"
																			),
																array	(	"name" => _("FAQ"),
																				"text" => _("Oft gestellte Fragen"),
																				"page" => "faq.htm"
																			)
															)
			);

//show help for resources management, if available
if ($GLOBALS["RESOURCES_ENABLE"]) {
	$pages[] = array	(	"name" => _("Ressourcenverwaltung"),
				"text" => _("Verwaltung unterschiedlicher Ressourcen in Stud.IP"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Einführung"),
																				"text" => _("Was fange ich mit der Ressourcenverwaltung an?"),
																				"page" => "resources_intro.htm"
																			)
															)
			);
}

//show help for export functions, if available
if ($GLOBALS["EXPORT_ENABLE"]) {
	$pages[] = array	(	"name" => _("Export von Daten"),
				"text" => _("Exportieren von Daten aus Stud.IP in verschiedenen Formaten"),
				"perm" =>	"tutor",
				"kategorien" => array	(	
																array	(	"name" => _("Einführung"),
																				"text" => _("Was ist das Export-Modul?"),
																				"page" => "export_intro.htm"
																			)
															)
			);

}
//show help for ILIAS-interface, if available
if ($GLOBALS["ILIAS_CONNECT_ENABLE"]) {
	$pages[] = array	(	"name" => _("ILIAS Lernmodule in Stud.IP"),
				"text" => _("Einrichten und Nutzen von Lernmodulen aus ILIAS Open Source"),
				"perm" =>	"user",
				"kategorien" => array	(	
																array	(	"name" => _("Was ist ILIAS"),
																				"text" => _("Was ist ILIAS und was sind ILIAS Lernmodule"),
																				"page" => "what_is_ilias.php"
																			)
															)
			);
}
?>
