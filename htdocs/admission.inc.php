<?
/**
* admission.inc.php
*
* the basic library for the admisson system
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		admission
* @module		admission.inc.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admission.inc.php
// Funktionen die zur Teilnehmerbeschraenkung benoetigt werden
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


require_once ("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/language.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
//set handling for script execution
ignore_user_abort(TRUE);
set_time_limit(0);

/**
* This function calculate the remaining places for the "alle"-allocation
*
* The function calculate the remaining places for the "alle"-allocation. It considers
* the places in the other allocations to avoid rounding errors
*
* @param		string	seminar_id	the seminar_id of the seminar to calculate
* @return		integer
*
*/

function get_all_quota($seminar_id) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	//Daten holen
	$db->query("SELECT Seminar_id, Name, admission_turnout FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();

	//Alle zugelassenen Studiengaenge auswaehlen um die genaue Platzzahl zu ermitteln
	$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '$seminar_id' AND studiengang_id !='all' ");
	$count=0;
	while ($db2->next_record())
		$count=$count+ round($db->f("admission_turnout") * ($db2->f("quota") / 100));

	$all_quota=$db->f("admission_turnout")-$count;
	if ($all_quota <0)
		$all_quota = 0;

	return $all_quota;
}


/**
* This function calculate the remaining places for the complete seminar
*
* This function calculate the remaining places for the complete seminar. It considers all the allocations
* and it avoids rounding errors
*
* @param		string	seminar_id	the seminar_id of the seminar to calculate
* @return		integer
*
*/

function get_free_admission ($seminar_id) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;

	//Daten holen
	$db->query("SELECT Seminar_id, Name, admission_turnout FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();

	//Alle zugelassenen Studiengaenge auswaehlen um die genaue Platzzahl zu ermitteln
	$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '$seminar_id' ");
	$count=0;
	while ($db2->next_record())
		if ($db2->f("studiengang_id") == "all")
			$count=$count+get_all_quota($db->f("Seminar_id"));
		else
			$count=$count+round ($db->f("admission_turnout") * ($db2->f("quota") / 100));

	//Wiieviel Teilnehmer koennen noch eingetragen werden?
	$db3->query("SELECT user_id FROM seminar_user WHERE Seminar_id = '".$db->f("Seminar_id")."' AND status= 'autor' AND admission_studiengang_id != ''");

	// this query is for "temporarily accepted", status "accepted"
	$db4->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id = '".$db->f("Seminar_id")."' AND status = 'accepted' AND studiengang_id != ''");
	if (($count - $db3->num_rows() - $db4->num_rows()) > 0)
		$count = ($count - $db3->num_rows() - $db4->num_rows());
	else
		$count = 0;

	return $count;
}

/**
* This function numbers a waiting list
*
* Use this functions, if a person was moved from the waiting list or there were other changes
* to the waiting list. The User gets a message, if the parameter is set and the position
* on the waiting  list has changed.
*
* @param		string	seminar_id		the seminar_id of the seminar to calculate
* @param		boolean	send_message		should a system-message be send?
*
*/

function renumber_admission ($seminar_id, $send_message=TRUE) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$messaging=new messaging;

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
				//Usernamen auslesen
				$db4->query("SELECT username FROM auth_user_md5 WHERE user_id = '".$db2->f("user_id")."' ");
				$db4->next_record();
				setTempLanguage($db2->f("user_id"));
				$message = sprintf(_("Sie sind in der Warteliste der Veranstaltung **%s (%s)** hochgestuft worden. Sie stehen zur Zeit auf Position %s."), $db->f("Name"), htmlReady(view_turnus($db->f("Seminar_id"))), $position);
				restoreLanguage();
				$messaging->insert_message($message, $db4->f("username"), "____%system%____", FALSE, FALSE, "1");
			}
			$position++;
		}
	}
}



/* Helper-Function for grouped admissions */
function group_update_admission($seminar_id, $send_message=TRUE, $user_id, $username) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$messaging=new messaging;

	//check if seminar ist grouped
	$db->query("SELECT start_time, duration_time, admission_group, Name FROM seminare WHERE Seminar_id='$seminar_id'");
	$db->next_record();
	if ($db->f("admission_group")) {
		//check if already in another seminar
		$db2->query("SELECT seminare.Seminar_id, seminare.Name FROM seminare, seminar_user WHERE seminare.admission_group='".$db->f("admission_group")."' AND seminare.Seminar_id = seminar_user.Seminar_id AND seminar_user.user_id='$user_id'");
		if ($db2->next_record()) {
			//remove from other seminar
			$db3->query("DELETE FROM seminar_user WHERE Seminar_id='".$db2->f("Seminar_id")."' AND user_id='$user_id'");
			//send a message if necessary
			if ($send_message) {
				setTempLanguage($user_id);
				$message = sprintf (_("Sie wurden aus der Veranstaltung **%s (%s)** gelöscht, da Sie in einer anderen Veranstaltung dieser Gruppe von der Warteliste in die Teilnehmerliste aufgestiegen sind."), $db2->f("Name"), view_turnus($db2->f("Seminar_id")));
				restoreLanguage();
				$messaging->insert_message(addslashes($message), $username, "____%system%____", FALSE, FALSE, "1");
			}
		//there was a change on the list of participants if the other seminar, so we have to update this seminar, maybe we can someone new put into the seminar
		update_admission($db2->f("Seminar_id"), $send_message);
		}
	}

}

/**
* This function updates an admission procedure
*
* The function checks, if user could be insert to the seminar.
* The User gets a message, if he is inserted to the seminar
*
* @param		string	seminar_id		the seminar_id of the seminar to calculate
* @param		boolean	send_message		should a system-message be send?
*
*/
function update_admission ($seminar_id, $send_message=TRUE) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$db6=new DB_Seminar;
	$messaging=new messaging;

	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time, admission_selection_take_place FROM seminare WHERE Seminar_id = '$seminar_id' ");
	$db->next_record();

	$db2->query("SELECT Name,admission_prelim FROM seminare WHERE Seminar_id='".$db->f("Seminar_id")."'");
	$db2->next_record();
	if ($db2->f("admission_prelim") == 1)
		$sem_preliminary = TRUE;
	else
		$sem_preliminary = FALSE;

	//Veranstaltung einfach auffuellen (nach Lostermin und Ende der Kontingentierung)
	if ($db->f("admission_selection_take_place")) {
		//anzahl der freien Plaetze holen
		$count=get_free_admission($seminar_id);

		//Studis auswaehlen, die jetzt aufsteigen koennen
		$db3->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id =  '".$db->f("Seminar_id")."' AND status != 'accepted' ORDER BY position LIMIT $count");
		while ($db3->next_record()) {
			group_update_admission($seminar_id,$send_message,$db3->f("user_id"),$db3->f("username")); //controll grouped
			$group = select_group ($db->f("start_time"), $db3->f("user_id")); //ok, here ist the "colored-group" meant (for grouping on meine_seminare), not the grouped seminars as above!
			if (!$sem_preliminary) {
				$db4->query("INSERT INTO seminar_user SET user_id = '".$db3->f("user_id")."', Seminar_id = '".$db->f("Seminar_id")."', status= 'autor', gruppe = '$group', admission_studiengang_id = '".$db3->f("studiengang_id")."', mkdate = '".time()."' ");
			} else {
				$db4->query("UPDATE admission_seminar_user SET status = 'accepted' WHERE user_id='".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."'");
			}
			if ($db4->affected_rows()) {
				if (!$sem_preliminary)
					$db5->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
				//User benachrichtigen
				if (($sem_preliminary || $db5->affected_rows()) && ($send_message)) {
					setTempLanguage($db3->f("user_id"));
					if (!$sem_preliminary) {
						$message = sprintf (_("Sie sind als TeilnehmerIn der Veranstaltung **%s (%s)** eingetragen worden, da für Sie ein Platz frei geworden ist. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen. Damit sind Sie auch als TeilnehmerIn der Präsenzveranstaltung zugelassen."), $db->f("Name"), view_turnus($db->f("Seminar_id")));
					} else {
						$message = sprintf (_("Sie haben den Status vorläufig akzeptiert in der Veranstaltung **%s (%s)** erhalten, da für Sie ein Platz freigeworden ist."), $db->f("Name"), view_turnus($db->f("Seminar_id")));
					}
					restoreLanguage();
					$messaging->insert_message(addslashes($message), $db3->f("username"), "____%system%____", FALSE, FALSE, "1");
				}
			}
		}

		//Warteposition der restlichen User neu eintragen
		renumber_admission($seminar_id, FALSE);

	//Nachruecken in einzelnen Kontingenten veranlassen (nur bei chronologischer Anmeldung)
	} elseif ($db->f("admission_type") == 2) {
		//Alle zugelassenen Studiengaenge einzeln bearbeiten
		$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '".$db->f("Seminar_id")."' ");
		while ($db2->next_record()) {
			//Wenn Kontingent "alle" bearbeitet wird, wird die Teilnehmerzahl aus den anderen Kontingenten gebildet
			if ($db2->f("studiengang_id") == "all")
				$tmp_admission_quota=get_all_quota($db->f("Seminar_id"));
			else
				$tmp_admission_quota=round ($db->f("admission_turnout") * ($db2->f("quota") / 100));
			//belegte Plaetze zaehlen
			$db3->query("SELECT user_id FROM seminar_user WHERE Seminar_id =  '".$db->f("Seminar_id")."' AND admission_studiengang_id ='".$db2->f("studiengang_id")."' ");
			$db6->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id = '".$db->f("Seminar_id")."' AND status = 'accepted' AND studiengang_id = '".$db2->f("studiengang_id")."'");
			$free_quota=$tmp_admission_quota - $db3->num_rows() - $db6->num_rows();
			//Studis auswaehlen, die jetzt aufsteigen koennen
			$db4->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id =  '".$db->f("Seminar_id")."' AND studiengang_id = '".$db2->f("studiengang_id")."' AND status != 'accepted' ORDER BY position LIMIT $free_quota");
			while ($db4->next_record()) {
				group_update_admission($seminar_id,$send_message,$db4->f("user_id"),$db4->f("username")); //controll grouped
				$group = select_group ($db->f("start_time"), $db4->f("user_id")); //ok, here ist the "colored-group" meant (for grouping on meine_seminare), not the grouped seminars as above!
				if (!$sem_preliminary) {
					$db5->query("INSERT INTO seminar_user SET user_id = '".$db4->f("user_id")."', Seminar_id = '".$db->f("Seminar_id")."', status= 'autor', gruppe = '$group', admission_studiengang_id = '".$db2->f("studiengang_id")."', mkdate = '".time()."' ");
				} else {
					$db5->query("UPDATE admission_seminar_user SET status = 'accepted' WHERE user_id = '".$db4->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."'");
				}
				if ($db5->affected_rows()) {
					if (!$sem_preliminary)
						$db6->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db4->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
					//User benachrichtigen
					if (($sem_preliminary || $db6->affected_rows()) && ($send_message)) {
						setTempLanguage($db4->f("user_id"));
						if (!$sem_preliminary) {
							$message = sprintf (_("Sie sind als TeilnehmerIn der Veranstaltung **%s** eingetragen worden, da für Sie ein Platz frei geworden ist. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen. Damit sind Sie auch als TeilnehmerIn der Präsenzveranstaltung zugelassen."), $db->f("Name"));
						} else {
							 $message = sprintf (_("Sie haben den Status vorläufig akzeptiert in der Veranstaltung **%s (%s)** erhalten,  da für Sie ein Platz freigeworden ist."), $db->f("Name"), view_turnus($db->f("Seminar_id")));
						}
						restoreLanguage();
						$messaging->insert_message(addslashes($message), $db4->f("username"), "____%system%____", FALSE, FALSE, "1");
					}
				}
			}
		}
		//Warteposition der restlichen User neu eintragen
		renumber_admission($seminar_id, $send_message);
	}
}


/**
* This function checks, if an admission procedure has to start
*
* The function will start a fortune procedure and ends the allocations. It will check ALL
* seminars in the admission system, but it do not much if there are no seminars to handle.
*
* @param		boolean	send_message		should a system-message be send?
*
*/
function check_admission ($send_message=TRUE) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$messaging=new messaging;

	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time FROM seminare WHERE admission_endtime <= '".time()."' AND admission_type > 0 AND (admission_selection_take_place = '0' OR admission_selection_take_place IS NULL) ");
	while ($db->next_record()) {

		$db2->query("SELECT Name,admission_prelim FROM seminare WHERE Seminar_id='".$db->f("Seminar_id")."'");
		$db2->next_record();
		if ($db2->f("admission_prelim") == 1)
			$sem_preliminary = TRUE;
		else
			$sem_preliminary = FALSE;

		if ($db->f("admission_type") == '1') { //nur Losveranstaltungen losen
			//Check, if locked
			$db2->query("SELECT admission_selection_take_place FROM seminare WHERE Seminar_id = '".$db->f("Seminar_id")."' ");
			$db2->next_record();
			if (($db2->f("admission_selection_take_place") == '-1') ||  ($db2->f("admission_selection_take_place") == '1'))
				break; //Someone has locked or checked the Veranstaltung in the meanwhile

			//Veranstaltung locken
			$db2->query("UPDATE seminare SET admission_selection_take_place ='-1' WHERE Seminar_id = '".$db->f("Seminar_id")."' ");

			//Alle zugelassenen Studiengaenge einzeln auslosen
			$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '".$db->f("Seminar_id")."' ");
			while ($db2->next_record()) {
				//Wenn Kontingent "alle" bearbeitet wird, wird die Teilnehmerzahl aus den anderen Kontingenten gebildet
				if ($db2->f("studiengang_id") == "all")
					$tmp_admission_quota=get_all_quota($db->f("Seminar_id"));
				else
					$tmp_admission_quota=round ($db->f("admission_turnout") * ($db2->f("quota") / 100));
				//Losfunktion
				$db3->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' AND studiengang_id = '".$db2->f("studiengang_id")."' AND status != 'accepted' ORDER BY RAND() LIMIT ".$tmp_admission_quota);
				//User aus admission_Seminar_user in seminar_user verschieben oder in Status "vorläufig akzeptiert" setzen
				while ($db3->next_record())   {
					if ($sem_preliminary) {
						// Bei Seminaren mit vorläufiger Akzeptierung wird nicht in die Teilnehmerliste
						// gelost, sondern der Status wird auf "accepted" gesetzt
						$db4->query("UPDATE seminar_user SET status='accepted' WHERE Seminar_id = '".$db->f("Seminar_id")."' AND user_id = '".$db3->f("user_id")."'");
						if ($send_message) {
							setTempLanguage($db3->f("user_id"));
							$message = sprintf (_("Sie wurden als TeilnehmerIn der Veranstaltung **%s** ausgelost. Die endgültige Zulassung zu der Veranstaltung ist noch von weiteren Bedingungen abhängig, die Sie bitte der Veranstaltungsbeschreibung entnehmen."), $db->f("Name"));
							restoreLanguage();
							$messaging->insert_message(addslashes($message), $db3->f("username"), "____%system%____", FALSE, FALSE, "1");
						}
					} else {
						$group = select_group ($db->f("start_time"), $db3->f("user_id"));
						$db4->query("INSERT INTO seminar_user SET Seminar_id = '".$db->f("Seminar_id")."', user_id = '".$db3->f("user_id")."', status= 'autor', gruppe = '$group', admission_studiengang_id = '".$db3->f("studiengang_id")."', mkdate = '".time()."' ");
						if ($db4->affected_rows()) {
							$db5->query("DELETE FROM admission_seminar_user WHERE user_id ='".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
							//User benachrichten
							if (($db5->affected_rows()) && ($send_message)) {
								setTempLanguage($db3->f("user_id"));
								$message = sprintf (_("Sie wurden als TeilnehmerIn der Veranstaltung **%s** ausgelost. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen. Damit sind Sie auch als TeilnehmerIn der Präsenzveranstaltung zugelassen."), $db->f("Name"));
								restoreLanguage();
								$messaging->insert_message(addslashes($message), $db3->f("username"), "____%system%____", FALSE, FALSE, "1");
							}
						}
					}
				}
			}

			//Alle anderen Teilnehmer in der Warteliste losen
			$db3->query("SELECT admission_seminar_user.user_id, username FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' AND status != 'accepted' ORDER BY RAND() ");
			//Warteposition ablegen
			$position=1;
			while ($db3->next_record()) {
				$db4->query("UPDATE admission_seminar_user SET position = '$position', status = 'awaiting' WHERE user_id = '".$db3->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
				$position ++;
			}
		}

		//Veranstaltung lock aufheben und erfolgreichen Losvorgang eintragen bzw. verstreichen der Kontingentierungsfrist notieren
		$db2->query("UPDATE seminare SET admission_selection_take_place ='1' WHERE Seminar_id = '".$db->f("Seminar_id")."' ");

		//evtl. verbliebene Plaetze auffuellen
		update_admission($db->f("Seminar_id"), $send_message);

		//User benachrichten (nur bei Losverfahren, da Warteliste erst waehrend des Losens generiert wurde).
		if (($send_message) && ($db->f("admission_type") == '1')) {
			$db2->query("SELECT admission_seminar_user.user_id, username, position FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' ORDER BY position ");
			while ($db2->next_record()) {
				setTempLanguage($db2->f("user_id"));
				$message = sprintf(_("Sie wurden leider im Losverfahren der Veranstaltung **%s** __nicht__ ausgelost. Sie wurden jedoch auf Position %s auf die Warteliste gesetzt. Das System wird Sie automatisch eintragen und benachrichtigen, sobald ein Platz für Sie frei wird."), $db->f("Name"), $db2->f("position"));
				restoreLanguage();
				$messaging->insert_message(addslashes($message), $db2->f("username"), "____%system%____", FALSE, FALSE, "1");
			}
		}
	}
}
