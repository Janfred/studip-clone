<?php

/*
admission.inc.php - Funktionen die zur Teilnehmerbeschraenkung benoetigt werden
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, data-quest <info@data-quest.de>

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

require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");

/*
Die Funktion get_free_admission gibt die Anzahl an insgesamt freien Plaetzen zurueck. Dabei werden
Rundungsfehler gegenueber der absoluten Platzanzahl beruecksichtigt.
*/

function get_free_admission ($seminar_id) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;

	//Daten holen 
	$db->query("SELECT Seminar_id, Name, admission_turnout FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();
	
	//Alle zugelassenen Studiengaenge auswaehlen um die genaue Platzzahl zu ermitteln
	$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '$seminar_id' ");
	$count=0;
	while ($db2->next_record())
		$count=$count+ round($db->f("admission_turnout") * ($db2->f("quota") / 100));

	//Wiieviel Teilnehmer koennen noch eingetragen werden?
	$db3->query("SELECT user_id FROM seminar_user WHERE Seminar_id = '".$db->f("Seminar_id")."' AND status= 'autor' ");
		if ($count - $db3->num_rows() > 0)
			$count = $count - $db3->num_rows();
		else
			$count = 0;
	
	return $count;
}

/*
Die Funktion renumer_admission numeriert eine Warteliste neu. Anzuwenden wenn Personen
von der Warteliste entfertnt wurden.
Die Teilnehmer bekommen eine SMS wie sich die Position veraendert hat, wenn der Parameter
gesetzt ist.
*/

function renumber_admission ($seminar_id, $send_message=TRUE) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$messaging=new messaging;
	echo r, $seminar_id;
	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name FROM seminare WHERE Seminar_id = '$seminar_id' AND ((admission_type = '1'  AND admission_selection_take_place = '1') OR (admission_type = '2'))");
	if ($db->next_record()) {
		//Liste einlesen
		$db2->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id =  '".$db->f("Seminar_id")."' AND status = 'awaiting' ORDER BY position ");
		$position=1;
		//Liste neu numerieren
		while ($db2->next_record()) {
			$db3->query("UPDATE admission_seminar_user SET position = '$position' WHERE user_id = '".$db2->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
			//User benachrichten				
			if (($db3->affected_rows()) && ($send_message)) {
				//Username auslesen
				$db4->query("SELECT username FROM auth_user_md5 WHERE user_id = '".$db2->f("user_id")."' ");
				$db4->next_record();
				$message="Sie sind in der Warteliste der Veranstaltung **".$db->f("Name")."** hochgestuft worde. Sie stehen zur Zeit auf Position $position.";
				$messaging->insert_sms ($db4->f("username"), $message, "____%system%____");
			}
			$position++;
		}
	}
}

/*
Die Funktion update_admission ueberprueft, ob Teilnehmer nachruecken koennen
Die Teilnehmer bekommen eine SMS wenn es geklappt hat, wenn der Parameter
gesetzt ist.
*/

function update_admission ($seminar_id, $send_message=TRUE) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$messaging=new messaging;
	echo u, $seminar_id;
	
	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time FROM seminare WHERE Seminar_id = '$seminar_id' AND admission_selection_take_place = '1' ");
	if ($db->next_record()) {
		//anzahl der freien Plaetze holen
		$count=get_free_admission($seminar_id);
		
		//Studis auswaehlen, die jetzt aufsteigen koennen
		$db3->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id =  '".$db->f("Seminar_id")."' ORDER BY position LIMIT $count");
		while ($db3->next_record()) {
			$group = select_group ($db->f("start_time"), $db3->f("user_id"));			
			$db4->query("INSERT INTO seminar_user SET user_id = '".$db3->f("user_id")."', Seminar_id = '".$db->f("Seminar_id")."', status= 'autor', gruppe = '$group', admission_studiengang_id = '".$db3->f("studiengang_id")."', mkdate = '".time()."' ");
			if ($db4->affected_rows()) {
				$db5->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
				//User benachrichten
				if (($db5->affected_rows()) && ($send_message)) {
					$message="Sie sind als Teilnehmer der Veranstaltung **".$db->f("Name")."** eingetragen worden, da für Sie ein Platz frei geworden ist. Ab sofort finden Sie die Veranstaltung in der Übersicht ihrer Veranstaltungen. Damit sind sie auch als Teilnehmer der Präsenzveranstaltung zugelassen.";
					$messaging->insert_sms ($db3->f("username"), $message, "____%system%____");
				}
			}
		}

		//Warteposition der restlichen User neu eintragen
		renumber_admission($seminar_id. $send_message);
	}
}

/*
Die Funktion check_admissionueberprueft, ob Veranstaltungen gelost oder das Kontingent geoefftnet
werden muss. Die Teilnehmer bekommen eine SMS ob es geklappt hat oder nicht, wenn der Parameter
gesetzt ist.
*/

function check_admission ($send_message=TRUE) {
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$messaging=new messaging;
	
	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time FROM seminare WHERE admission_endtime <= '".time()."' AND admission_selection_take_place = '0' ");
	while ($db->next_record()) {
		if ($db->f("admission_type") == '1') { //nur Losveranstaltungen losen 
			//Veranstaltung locken
			$db2->query("UPDATE seminare SET admission_selection_take_place ='-1' WHERE Seminar_id = '".$db->f("Seminar_id")."' ");
		
			//Alle zugelassenen Studiengaenge einzeln auslosen
			$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '".$db->f("Seminar_id")."' ");
			while ($db2->next_record()) {
				//Losfunktion
				$db3->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' AND studiengang_id = '".$db2->f("studiengang_id")."' ORDER BY RAND() LIMIT ".round($db->f("admission_turnout") * ($db2->f("quota") / 100)));
				//User aus admission_Seminar_user in seminar_user verschieben
				while ($db3->next_record())   {
					$group = select_group ($db->f("start_time"), $db3->f("user_id"));			
					$db4->query("INSERT INTO seminar_user SET Seminar_id = '".$db->f("Seminar_id")."', user_id = '".$db3->f("user_id")."', status= 'autor', gruppe = '$group', admission_studiengang_id = '".$db3->f("studiengang_id")."', mkdate = '".time()."' ");
					if ($db4->affected_rows()) {
						$db5->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
						//User benachrichten
						if (($db5->affected_rows()) && ($send_message)) {
							$message="Sie wurden als Teilnehmer der Veranstaltung **".$db->f("Name")."** ausgelost. Ab sofort finden Sie die Veranstaltung in der Übersicht ihrer Veranstaltungen. Damit sind sie auch als Teilnehmer der Präsenzveranstaltung zugelassen.";
							$messaging->insert_sms ($db3->f("username"), $message, "____%system%____");
						}
					}
				}
			}
		
			//Alle anderen Teilnehmer in der Warteliste losen
			$db3->query("SELECT admission_seminar_user.user_id, username FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' ORDER BY RAND() ");
			//Warteposition ablegen
			$position=1;
			while ($db3->next_record()) {
				$db4->query("UPDATE admission_seminar_user SET position = '$position', status = 'awaiting' WHERE user_id = '".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
				$position ++;
			}
		}

		//Veranstaltung lock aufheben und erfolgreichen Losvorgang einragen bzw. vertreichen der Kontingentierungsfrist notieren
		$db2->query("UPDATE seminare SET admission_selection_take_place ='1' WHERE Seminar_id = '".$db->f("Seminar_id")."' ");

		//evtl. verbliebene Plaetze auffuellen
		update_admission($seminar_id, $send_message);

		//User benachrichten (nur bei Losverfahren, da Wartelist erst waehrend des Losens generiert wurde.
		if (($send_message) && ($db->f("admission_type") == '1')) {
			$db2->query("SELECT admission_seminar_user.user_id, username, position FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' ORDER BY position ");
			while ($db2->next_record()) {
				$message="Sie wurden leider im Losverfahren der Veranstaltung **".$db->f("Name")."** __nicht__ ausgelost. Sie wurden jedoch auf Position ".$db2->f("position")." auf die Warteliste gesetzt. Das System wird Sie automatisch eintragen, sobald ein Platz für Sie frei wird.";
				$messaging->insert_sms ($db2->f("username"), $message, "____%system%____");
			}
		}
	}
}