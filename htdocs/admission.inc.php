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
Die Funktion veranstaltung_beginn errechnet den ersten Seminartermin aus dem Turnus Daten.
Zurueckgegeben wird ein String oder Timestamp. je nach return_mode (TRUE = Timestamp)
Evtl. Ergaenzungen werden im Stringmodus mit ausgegeben.
Die Funktion kann mit einer Seminar_id aufgerufen werden, dann werden saemtliche gespeicherten Daten 
beruecksichtigt. Im 'ad hoc' Modus koennen der Funktion auch die eizelnen Variabeln des Metadaten-Arrays
uebergeben werden. Dann werden konkrete Termine nur mit berruecksichtigt, sofern sie schon angelegt wurden.
*/

function check_admission ($send_message=TRUE) {
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$messaging=new messaging;
	
	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time FROM seminare WHERE admission_endtime > '".time()."' AND admission_selection_take_place = '0' AND admission_type = '1' ");
	while ($db->next_record()) {
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
				$db4->query("INSERT INTO seminar_user SET user_id = '".$db3->f("user_id")."', Seminar_id = '".$db->f("Seminar_id")."', status= 'autor', group = '$group', admission_studiengang_id = '".$db3->f("studiengang_id")."', mkdate = '".time()."' ");
				if ($db4->num_rows())
					$db5->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
					//User benachrichten
					if (($db5->num_rows()) && ($send_message)) {
						$message="Sie wurden als Teilnehmer der Veranstaltung <b>".$db->f("Name")."</b> ausgelost. Ab sofort finden Sie die Veranstaltungen in der &Uuml;bersicht ihrer Veranstaltungen. Damit sind sie auch als Teilnehmer der Pr&auml;senzveranstaltung zugelassen.";
						$messaging->insert_sms ($db3->f("username"), $message);
					}
			}
			//Alle anderen Teilnehmer in der Warteliste losen
			$db3->query("SELECT user_id, username FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' ORDER BY RAND() ");
			//Warteposition ablegen
			$position=1;
			while ($db3->next_record()) {
				$db4->query("UPDATE admission_seminar_user SET position = '$position', status = 'awaiting' ");
				//User benachrichten
				if (($db4->num_rows()) && ($send_message)) {
					$message="Sie wurden leider im Losverfahren der Veranstaltung <b>".$db->f("Name")."</b> nicht ausgelost. Sie wurden jedoch auf Position $position auf die Warteliste gesetzt. Sie werden automatisch eingetragen, sobald ein Platz f&uuml;r Sie frei wird.";
					$messaging->insert_sms ($db3->f("username"), $message);
				}
				$position++;
			}
		}
		//Veranstaltung lock aufheben und erfolgreichen Losvorgang einragen
		$db2->query("UPDATE seminare SET admission_selection_take_place ='1' WHERE Seminar_id = '".$db->f("Seminar_id")."' ");
	}
}


function update_admission ($seminar_id, $send_message=TRUE) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$db6=new DB_Seminar;
	$messaging=new messaging;
	
	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time FROM seminare WHERE seminar_id = '$seminar_id' AND admission_type != 0");
	if ($db->next_record()) {
		//Alle zugelassenen Studiengaenge auswaehlen
		$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '$seminar_id' ");
		while ($db2->next_record()) {
			//Wiieviel Teilnehmer sind eingetragen und wieviele koennten eingetragen werden?
			$db3->query("SELECT count (user_id) AS count FROM seminar_user WHERE Seminar_id = '".$db->f("Seminar_id")."' AND admission_studiengang_id = '".$db2->f("studiengang_id")."' ");
			$db3->next_record();
			if ($db3->f("count") < round($db->f("admission_turnout") * ($db2->f("quota") / 100))) {
				//Studis asuwaehlen, die jetzt aufsteigen koennen
				$db4->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id =  '".$db->f("Seminar_id")."'  AND studiengang_id = ".$db2->f("studiengang_id")."' ORDER BY position LIMIT ".($db3->f("count") - round($db->f("admission_turnout") * ($db2->f("quota") / 100))));
				while ($db4->next_record()) {
					$group = select_group ($db->f("start_time"), $db4->f("user_id"));			
					$db5->query("INSERT INTO seminar_user SET user_id = '".$db4->f("user_id")."', Seminar_id = '".$db->f("Seminar_id")."', status= 'autor', group = '$group', admission_studiengang_id = '".$db2->f("studiengang_id")."', mkdate = '".time()."' ");
					if ($db6->num_rows())
						$db6->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db4->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
						//User benachrichten
						if (($db6->num_rows()) && ($send_message)) {
							$message="Sie als Teilnehmer der Veranstaltung <b>".$db->f("Name")."</b> eingetragen worden, da f&uuml;r Sie ein Platz frei geworden ist. Ab sofort finden Sie die Veranstaltungen in der &Uuml;bersicht ihrer Veranstaltungen. Damit sind sie auch als Teilnehmer der Pr&auml;senzveranstaltung zugelassen.";
							$messaging->insert_sms ($db3->f("username"), $message);
						}
				}
				//Warteposition der restlichen User neu eintragen
				$db4->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id =  '".$db->f("Seminar_id")."' ORDER BY position ");
				$position=1;
				while ($db4->next_record()) {
					$db4->query("UPDATE admission_seminar_user SET position = '$position'  ");
					$position++;
				}
			}
		}
	}
}