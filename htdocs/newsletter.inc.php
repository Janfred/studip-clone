<?php
/*
newsletter.inc.php - Funktionen fuer den Newsletter
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

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

function CheckPersonInNewsletter ($username, $newsletter_id)    // Ist jemand aufgrund der SQL-Clause im Versand?
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT * ".
		"FROM auth_user_md5 ".
		$newsletter[$newsletter_id]["SQL"].
		" AND user_id = '$user_id'";
	$db->query($query); 
	if ($db->next_record()) {
		$status = "letter";
	} else {
		$status = FALSE;
	}
	return $status;
}


function CheckPersonNewsletter ($username, $newsletter_id)    // Ist jemand in der Ausnahmeliste?
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT status FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'";
	$db->query($query); 
	if ($db->next_record()) {
		if ($db->f("status") == 1) {
			$status = "added";
		} else {
			 $status = "removed";
		}
	} else {
		$status = FALSE;
	}
	return $status;
}

function CheckStatusPersonNewsletter ($username, $newsletter_id)   // was ist nun der Gesamtstatus?
{ global $newsletter;
	if (CheckPersonInNewsletter ($username, $newsletter_id)=="letter" AND CheckPersonNewsletter ($username, $newsletter_id) !="removed") {
		$status = "Eingetragen";
	} elseif (CheckPersonNewsletter ($username, $newsletter_id) =="added") {		
		$status = "Eingetragen";
	} else {
		$status = "Ausgetragen";
	}
	return $status;
}  

function AddPersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Positivliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "removed") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg§Der Nutzer $username wurde wieder in den Newsletter aufgenommen.§";
	} elseif (CheckPersonInNewsletter($username, $newsletter_id) != "letter" AND $status != "added")  {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '1', newsletter_id = '$newsletter_id'");
		$msg = "msg§Der Nutzer $username wurde in den Newsletter aufgenommen.§";
	}
	return $msg;
}

function RemovePersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Negativliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "added") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg§Der Nutzer $username wurde wieder aus dem Newsletter gel&ouml;scht.§";
	} elseif ($status != "removed") {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '0', newsletter_id = '$newsletter_id'");
		$msg = "msg§Der Nutzer $username wurde aus dem Newsletter gel&ouml;scht.§";
	}
	return $msg;
}

// Newsletter arrays

	// Standard

	$newsletter[0]["name"] = "Stud.IP Newsletter";
	$newsletter[0]["SQL"] = "WHERE username = 'rstockm'";
//	$newsletter[0]["SQL"] = "WHERE perms != 'user' AND perms != 'autor'";
	$newsletter[0]["return"] = "crew@studip.de";
	$newsletter[0]["text"] = 
"Stud.IP-Newsletter  #01  / 19.10.2002
----------------------------------------------------------------------

Inhalt

0. Vorwort
1. Release der 0.8.15 auf dem Göttinger Hauptserver
2. Informationsveranstaltungen und Schulungen zum Semesterstart 
3. Neue Funktionen der letzten beiden Releases
4. Archivieren von Veranstaltungen des SS02
5. Impressum



**********************************************************************
** 0. Vorwort
**********************************************************************

Sehr geehrte Nutzer und Nutzerinnen von Stud.IP,

seit unser System im Rahmen der 'Notebook University' gefördert wird,
haben sich die Entwicklungszyklen beschleunigt. Viele neue
Funktionalitäten sind dazugekommen, mit denen wir auf die vielfältigen
Anregungen der User eingehen.

Damit Sie als Nutzer nicht den Überblick verlieren und immer auf dem
aktuellen Stand bleiben können, habe wir diesen Newsletter entwickelt.
Zu jeder neuen Release informieren wir über alle Änderungen, die
Sie in Ihrer täglichen Arbeit mit dem System betreffen könnten.

Adressaten dieses Newsletters sind alle im System registrierten
Nutzer ab dem Status 'Tutor'.

Wenn Sie sich aus dem Newsletter abmelden wollen, scrollen Sie an
das untere Ende und folgen Sie dort dem Link.

Eine informative Lektüre wünscht

Ihre Stud.IP Crew



**********************************************************************
** 1. Release der 0.8.15 auf dem Göttinger Hauptserver
**********************************************************************

Am Sonntag den 13.10.2002 haben wir um 15 Uhr den Göttinger Stud.IP
Server auf die neueste Version geupdatet (Version 0.8.15). Die neuen
Funktionalitäten stehen allen angeschlossenen Instituten ab sofort
zur Verfügung (siehe 3.).



**********************************************************************
** 2. Informationsveranstaltungen und Schulungen zum Semesterstart
**********************************************************************

Nicht nur durch die NBU-Verastaltungen begrüßen wir immer mehr
Studierende im System. Die aktuelle Anzahl liegt bei 2740.
Die meisten Studierenden haben mit den selbsterklärenden Funktionen
von Stud.IP wenig Probleme, aber die 'Medienkompetenz' ist bekanntlich
doch unterschiedlich ausgeprägt.

Auf vielfachen Wunsch bieten wir daher zu mehreren Terminen im Semester
öffentliche Schulungsveranstaltungen an, in denen Studierenden auf
einfache und unterhaltende Weise die Grundfunktionen des Systems 
erklärt werden.

Zielgruppe sind also ausschließlich Studierende, die ersten beiden
Termine sind:


Dienstag  29.10. 14.00 Uhr ZHG 010
Mittwoch, 30.10. 16.00 Uhr ZHG 010


Bitte teilen Sie diese Termine Ihren Studierenden mit, an beiden 
Terminen werden dieselben Grundlagen vermittelt.

Auch für TutorInnen und DozentInnen der beteiligten Einrichtungen
bieten wir im Semester gezielte Schulungen an. Diese werden über
die Zentrale Einrichtung Medien (ZEM) organisiert, ein Vertreter
macht die Einführungen direkt bei Ihnen vor Ort.

Ansprechpartner für diese Schulungen ist:

Dirk Pfuhl
----------------------------------
Telefon: 0551-39 8351 
Email: dpfuhl@uni-goettingen.de 
Homepage: http://www.dirkpfuhl.de 
----------------------------------

In den nächsten Wochen werden des weiteren Informationsflyer für
die verschiedenen Zielgruppen 'Autoren', 'Tutoren/Dozenten' und
'Admins' verfügbar sein mit entsprechenden F.A.Q., Nutzungshinweisen
etc.



**********************************************************************
** 3. Neue Funktionen der letzten beiden Releases
**********************************************************************

Hinweis: zu allen neuen Funktionen finden Sie ausführliche Hilfeseiten
direkt im System, wie gewohnt über das Fragezeichensymbol '?' zu
erreichen!


3.a. Funktionen- und Gruppenverwaltung

In vielen Situationen kann es hilfreich sein, 
die Teilnehmer einer Veranstaltung oder einer Einrichtung nach 
Funktionen oder Gruppen zu ordnen. 
In einer Veranstaltung könnten dies beispielsweise sein: 

- Unterteilung der Studierenden nach der Art des Scheinerwerbs 
  (kein Schein / Teilnahmeschein / Leistungsschein). 
  Sie sehen dann etwa auf einen Blick, welche Studierende 
  besondere Leistungen erbringen müssen... 

- Unterteilung der Studierenden nach Aufgabengebieten. 
  In einer Praxisveranstaltung etwa soll Teamarbeit gefördert werden, 
  dazu wird der Kurs aufgeteilt in 'Designer' und 'Programmierer'.

- Die Hausarbeiten der Veranstaltung werden in Gruppen angefertigt. 
  Diese können Sie so einfach verwalten und behalten den Überblick...


Für jede Einrichtung sind Einteilungen fast unverzichtbar: 

- Um die Hierarchien einer Einrichtung Abzubilden 
  (Hochschullehrer, Mittelbau, Sekretariat etc.)
- Um Kompetenzbereiche oder Lehrstühle abzubilden 
  (Ein Professor und sein 'Hofstaat')
- Um Themenschwerpunkte abzugrenzen etc.

Die entsprechenden Funktionen sind für Veranstaltungen am dem Status
'Tutor' verfügbar, für Institute nehmen 'Admins' die entsprechenden
Einstellungen vor.


3.b. Meine aktuellen Termine auf der Startseite

Auf der persönlichen Startseite werden jetzt die Termine der nächsten
sieben Tage angezeigt. Es werden genau die Termine angezeigt, die auch
im persönlichen Terminkalender eingebunden sind. Dort haben Sie wie
bisher auch die Möglichkeit, eigene veranstaltungsunabhängige Termine
anzulegen.


3.c. Verbessertes Arbeitsgruppenmanagement

Für Forschungs- und Arbeitsgruppen gibt es eine Reihe Vereinfachungen
beim Anlegen.


3.d. Einrichtungen als Objekte im System

Einrichtungen wie Institute sind nun als eigenständige Objekte im System
verankert. Sie können also für jedes Institut das Forensystem nutzen
(etwa für eine Studienberatung), den Dateibereich (Prüfungsordnungen etc.)
und dergleichen mehr.
Sie erreichen den Einrichtungsbereich über die Veranstaltungsübersichtsseite
mit dem neuen Reiter 'meine Einrichtungen'.


3.e. Zulassungsverfahren für teilnehmerbeschränkte Veranstaltungen

Für teilnehmerbeschränkte Veranstaltungen stehen nun mächtige 
Verwaltungsfunktionen zur Verfügung: beim Anlegen können Sie die
maximale Teilnehmerzahl angeben. Es gibt bisher zwei Verfahrensweisen:

- Anmeldung nach Reihenfolge (wer zuerst kommt, malt zuerst):
  Die Studierenden können sich in die Veranstaltung eintragen bis diese 'voll'
  ist. Danach landet man auf einer Warteliste. Eingetragene Studierende 
  können ihren Platz aufgeben, es wird dann automatisch 'nachgerückt'.

- Zuordnung durch Losverfahren:
  Alle Bewerber landen in einer Warteliste. An einem einstellbaren Termin
  werden die Teilnehmer ausgelost. Alle nicht gelosten landen in einer 
  Warteliste. Man hat zusätzlich die Möglichkeit, Kontingente nach
  Studiengängen einzurichten, also etwa 50% Hauptfächler, 40% Nebenfächler,
  10% Sonstige etc.

Ab dem Status Tutor sehen Sie auf Ihrer TeilnehmerInnenseite den aktuellen
Stand der Zuweisungen. 
 


**********************************************************************
** 4. Archivieren von Veranstaltungen des SS02
**********************************************************************


Da das Wintersemester 2002 offiziell begonnen hat, empfiehlt es sich
alle abgelaufenen Veranstaltungen des vergangenen Sommersemesters zu
archivieren. Solange dies nicht geschieht, verbleiben die 
Veranstaltungen auf den Übersichtsseiten im System.

Archiviert werden können Veranstaltungen ausschließlich von den
Administratoren der Institute. Als Administrator sollten Sie daher
unter Ihren Lehrenden in Erfahrung bringen, welche Veranstaltungen
archiviert werden können. Das Archivieren geschieht sehr einfach über
den Bereich 'Verwaltung von Veranstaltungen'.

Sollten Veranstaltungen noch weiterlaufen, sollten diese entsprechend
nicht archiviert werden.

Auf alle archivierten Veranstaltungen haben Sie nach wie vor Zugriff
über die Option 'Suchen' auf der Startseite. Zugriff auf die
Veranstaltungen haben alle TeilnehmerInnen der Veranstaltungen.
Als Admin oder DozentIn können Sie auch nachträglich Nutzern Zugriff
erlauben.



**********************************************************************
** 5. Impressum
**********************************************************************

Redaktion des Newsletter:

  Stud.IP Entwicklercrew

Kontakt:

  Dipl.- Sozw. Ralf Stockmann
  ZiM - Humboldtallee 23
  +49 551 39 92 48
  rstockm@uni-goettingen.de
  www.studip.de
  www.zim.uni-goettingen.de";

	// weitere

	$newsletter[1]["name"] = "Stud.IP Admin-Newsletter";
	$newsletter[1]["SQL"] = "WHERE perms = 'admin'";
	$newsletter[1]["text"] = "Hallo dies ist ein noch ein Text";



?>