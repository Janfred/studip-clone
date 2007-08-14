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


require_once ('lib/messaging.inc.php');
require_once 'lib/functions.php';
require_once ('lib/language.inc.php');
require_once ('lib/dates.inc.php');
require_once('lib/classes/StudipAdmissionGroup.class.php');

//set handling for script execution
ignore_user_abort(TRUE);
if( !ini_get('safe_mode')) set_time_limit(0);

/**
* This function inserts an user into the seminar_user and does consitency checks with admission_seminar_user
*
* Please use this functions always to insert user to a seminar. Returns true, if user was on the admission_seminar_user
*
* @param		string seminar_id		the seminar_id of the seminar to calculate
* @param		string user_id			the user_id
* @param		string status			the perms the user should archive
* @param		boolean copy_studycourse	should the entry for studycourse from admission_seminar_user into seminar user be copied?
* @return		boolean
*
*/
function insert_seminar_user($seminar_id, $user_id, $status, $copy_studycourse = false, $consider_contingent = false) {
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
		
	$query = sprintf("SELECT comment, studiengang_id FROM admission_seminar_user WHERE user_id = '%s' AND seminar_id ='%s' ", $user_id, $seminar_id);
	$db->query($query);
	if ($db->next_record()) {
		$admission_entry = TRUE;
		$comment = $db->f("comment");
		if ($copy_studycourse)
			$studiengang_id = $db->f("studiengang_id");
		else
			$studiengang_id = '';
	}
	if(strlen($consider_contingent) > 1) $studiengang_id = $consider_contingent;
	
	$query = sprintf("SELECT * FROM seminare WHERE Seminar_id ='%s' ", $seminar_id);
	$db->query($query);
	$db->next_record();
	
	if($copy_studycourse && $consider_contingent && ($db->f('admission_type') == 1 || $db->f('admission_type') == 2)){
		if(!$db->f('admission_selection_take_place')){
			$admission_info = get_admission_quota_info($seminar_id);
			if(!$admission_info[$studiengang_id]['num_available']) return false;
		} else {
			if(!get_free_admission($seminar_id)) return false;
		}
	}

	$group = select_group($db->f("start_time"), $user_id); //ok, here ist the "colored-group" meant (for grouping on meine_seminare), not the grouped seminars as above!
	
	$query = sprintf("INSERT INTO seminar_user SET Seminar_id = '%s', user_id = '%s', status= '%s', admission_studiengang_id ='%s', comment ='%s', gruppe='%s', mkdate = '%s' ", $seminar_id, $user_id, $status, $studiengang_id, mysql_escape_string($comment), $group, time());
	$db->query($query);
	
	if ($ret = $db->affected_rows()) {
		$query2 = sprintf("DELETE FROM admission_seminar_user WHERE user_id = '%s' AND seminar_id ='%s'", $user_id, $seminar_id);
		$db2->query($query2);
	}
	
	if ($db2->affected_rows()) {
		//renumber the waiting list, if a user was deleted from it
		renumber_admission($seminar_id);
		return 2;
	}
	return $ret;
}


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
	/*$db=new DB_Seminar;
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
	*/
	$info = get_admission_quota_info($seminar_id);
	return $info['all']['num_total'];
}

function get_admission_quota_info($seminar_id) {
	$db = new DB_Seminar();
	$ret = array();
	$count = 0;
	$ret['all']['name'] = _("alle Studiengänge");
	//Daten holen
	$db->query("SELECT admission_turnout FROM seminare WHERE Seminar_id = '$seminar_id'");
	$db->next_record();
	$admission_turnout = $db->f('admission_turnout');
	$db->query("SELECT quota, name FROM admission_seminar_studiengang ass LEFT JOIN studiengaenge st USING(studiengang_id) WHERE seminar_id = '$seminar_id' AND ass.studiengang_id !='all'");
	while($db->next_record()){
		$ret[$db->f('studiengang_id')]['name'] = $db->f('name');
		$ret[$db->f('studiengang_id')]['num_total'] = round($admission_turnout * ($db->f("quota") / 100));
		$count += $ret[$db->f('studiengang_id')]['num_total'];
	}
	$ret['all']['num_total'] = $admission_turnout - $count;
	if($ret['all']['num_total'] < 0) $ret['all']['num_total'] = 0;
	foreach($ret as $studiengang_id => $data){
		if($data['num_total']){
			$ret[$studiengang_id]['num_available'] = $data['num_total'];
			$db->query("SELECT COUNT(user_id) FROM seminar_user WHERE seminar_id = '$seminar_id' AND admission_studiengang_id='$studiengang_id'");
			$db->next_record();
			$ret[$studiengang_id]['num_available'] -= $db->f(0);
			$db->query("SELECT COUNT(user_id) FROM admission_seminar_user WHERE seminar_id = '$seminar_id' AND status = 'accepted' AND studiengang_id='$studiengang_id'");
			$db->next_record();
			$ret[$studiengang_id]['num_available'] -= $db->f(0);
			if($ret[$studiengang_id]['num_available'] < 0) $ret[$studiengang_id]['num_available'] = 0;
		} else {
			$ret[$studiengang_id]['num_available'] = 0;
		}
	}
	return $ret;
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
	/*
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
	*/
	$count = 0;
	foreach(get_admission_quota_info($seminar_id) as $info){
		$count += $info['num_available'];
	}
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
				$message = sprintf(_("Sie sind in der Warteliste der Veranstaltung **%s (%s)** hochgestuft worden. Sie stehen zur Zeit auf Position %s."), $db->f("Name"), view_turnus($db->f("Seminar_id")), $position);
				restoreLanguage();
				$messaging->insert_message(addslashes($message), $db4->f("username"), "____%system%____", FALSE, FALSE, "1");
			}
			$position++;
		}
	}
}



/* 
 * Helper-Functions for grouped admissions
 * Grouped seminars MUST HAVE chronologically admission-procedure activated!
 */
function check_group($user_id, $username, $grouped_sems, $cur_name, $cur_id) {
	global $send_message;

	$db = new DB_Seminar;
	$db2 = new DB_Seminar;
	$db3 = new DB_Seminar;
	$messaging = new messaging;

	//crunch array into sql_statement
	$sql = "";
	while ($elem = array_pop($grouped_sems)) {
		if ($sql != "") $sql .= " OR ";
		$sql .= "(Seminar_id = '$elem')";
	}
	$db->query("SELECT * FROM seminar_user WHERE user_id = '$user_id' AND ($sql);");
	if ($db->num_rows() != 0) {
		$db->next_record();
		$db2->query("DELETE FROM seminar_user WHERE user_id = '$user_id' AND Seminar_id = '".$db->f("Seminar_id")."';");
		if ($db2->affected_rows()) {
			$db3->query("SELECT Name FROM seminare WHERE Seminar_id = '".$db->f("Seminar_id")."';");
			$db3->next_record();
			setTempLanguage($db->f("user_id"));
			$message = sprintf (_("Ihr Abonnement der Veranstaltung **%s (%s)** wurde aufgehoben, da Sie in der Veranstaltung **%s (%s)** von der Warteliste nachgerückt sind. Bei diesen Veranstaltungen handelt sich um gruppierte Veranstaltungen, der Wartelisteneintrag wurde somit bevorzugt behandelt."),$db3->f("Name"), view_turnus($db->f("Seminar_id")),$cur_name, view_turnus($cur_id));
			restoreLanguage();
			$messaging->insert_message(addslashes($message), $username, "____%system%____", FALSE, FALSE, "1");
			update_admission($db->f("Seminar_id"), $send_message);
		}
	}
}

function group_update_admission($seminar_id, $send_message = TRUE) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$messaging=new messaging;

	//get date / check if there is any admission
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '$seminar_id' ");
	$db->next_record();

	//Groups exist only for chronological admissions
	if ($db->f("admission_type") != 2) return;
	
	//check if seminar ist grouped
	if ($db->f("admission_group")) {
		$db2->query("SELECT * FROM seminare WHERE admission_group = '".$db->f("admission_group")."' AND Seminar_id <> '".$db->f("Seminar_id")."';");
		$grouped_seminars = array();
		while ($db2->next_record()) {
			$grouped_seminars[] = $db2->f("Seminar_id");
		}
		//if no more contingents, just fill up
		if ($db->f("admission_selection_take_place")) {
			//anzahl der freien Plaetze holen
			$count=get_free_admission($seminar_id);

			//Studis auswaehlen, die jetzt aufsteigen koennen
			$db3->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id =  '".$db->f("Seminar_id")."' AND status != 'accepted' ORDER BY position LIMIT $count");
			while ($db3->next_record()) {
				//First we check to parse the grouped seminars
				check_group($db3->f("user_id"),$db3->f("username"),$grouped_seminars,$db->f("Name"),$db->f("Seminar_id"));
			}	
	} else {
		//Alle zugelassenen Studiengaenge einzeln bearbeiten
		$db2->query("SELECT studiengang_id, quota FROM admission_seminar_studiengang WHERE seminar_id = '".$db->f("Seminar_id")."' ");
		while ($db2->next_record()) {
			//Wenn Kontingent "alle" bearbeitet wird, wird die Teilnehmerzahl aus den anderen Kontingenten gebildet
			if ($db2->f("studiengang_id") == "all") {
				$tmp_admission_quota=get_all_quota($db->f("Seminar_id"));
			} else {
				$tmp_admission_quota=round ($db->f("admission_turnout") * ($db2->f("quota") / 100));
			}
			//belegte Plaetze zaehlen
			$db3->query("SELECT user_id FROM seminar_user WHERE Seminar_id =  '".$db->f("Seminar_id")."' AND admission_studiengang_id ='".$db2->f("studiengang_id")."' ");
			$db5->query("SELECT user_id FROM admission_seminar_user WHERE seminar_id = '".$db->f("Seminar_id")."' AND status = 'accepted' AND studiengang_id = '".$db2->f("studiengang_id")."'");
			$free_quota=$tmp_admission_quota - $db3->num_rows() - $db5->num_rows();
			if ($free_quota < 0) $free_quota = 0;
				//Studis auswaehlen, die jetzt aufsteigen koennen
				$db4->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id =  '".$db->f("Seminar_id")."' AND studiengang_id = '".$db2->f("studiengang_id")."' AND status != 'accepted' ORDER BY position LIMIT $free_quota");
				while ($db4->next_record()) {
					check_group($db4->f("user_id"),$db4->f("username"),$grouped_seminars,$db->f("Name"),$db->f("Seminar_id"));
				}
			}
		}
	}
}

/*
 * This function is a kind of wrapper, so that no nasty loops between the updaters occur
 *
 **/
function update_admission ($seminar_id, $send_message = TRUE) {
	$group = StudipAdmissionGroup::GetAdmissionGroupBySeminarId($seminar_id);
	if(is_object($group) && $group->getValue('status') == 0){
		group_update_admission($seminar_id, $send_message);
	}
	normal_update_admission($seminar_id, $send_message);
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
function normal_update_admission($seminar_id, $send_message = TRUE) {

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	$db6=new DB_Seminar;
	$messaging=new messaging;

	//Daten holen / Abfrage ob ueberhaupt begrenzt
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time, admission_selection_take_place, admission_prelim FROM seminare WHERE Seminar_id = '$seminar_id' ");
	$db->next_record();

	if ($db->f("admission_prelim") == 1)
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
			if ($free_quota < 0) $free_quota = 0;
			//Studis auswaehlen, die jetzt aufsteigen koennen
			$db4->query("SELECT admission_seminar_user.user_id, username, studiengang_id FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id =  '".$db->f("Seminar_id")."' AND studiengang_id = '".$db2->f("studiengang_id")."' AND status != 'accepted' ORDER BY position LIMIT $free_quota");
			while ($db4->next_record()) {
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
	$db->query("SELECT Seminar_id, Name, admission_endtime, admission_turnout, admission_type, start_time, admission_disable_waitlist FROM seminare WHERE admission_endtime <= '".time()."' AND admission_type > 0 AND (admission_selection_take_place = '0' OR admission_selection_take_place IS NULL) AND visible='1'"); // OK_VISIBLE
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
				if($tmp_admission_quota < 0) $tmp_admission_quota = 0;
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
		normal_update_admission($db->f("Seminar_id"), $send_message);

		//User benachrichten (nur bei Losverfahren, da Warteliste erst waehrend des Losens generiert wurde)
		//verbleibende Warteliste löschen, wenn keine Warteliste vorgesehen
		if (($send_message) && ($db->f("admission_type") == '1')) {
			$db2->query("SELECT admission_seminar_user.user_id, username, position FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id = '".$db->f("Seminar_id")."' AND status != 'accepted' ORDER BY position ");
			while ($db2->next_record()) {
				setTempLanguage($db2->f("user_id"));
				if (!$db->f('admission_disable_waitlist')){
				$message = sprintf(_("Sie wurden leider im Losverfahren der Veranstaltung **%s** __nicht__ ausgelost. Sie wurden jedoch auf Position %s auf die Warteliste gesetzt. Das System wird Sie automatisch eintragen und benachrichtigen, sobald ein Platz für Sie frei wird."), $db->f("Name"), $db2->f("position"));
				} else {
					$message = sprintf(_("Sie wurden leider im Losverfahren der Veranstaltung **%s** __nicht__ ausgelost. Für diese Veranstaltung wurde keine Warteliste vorgesehen."), $db->f("Name"));
					$db3->query("DELETE FROM admission_seminar_user WHERE user_id = '".$db2->f("user_id")."' AND seminar_id = '".$db->f("Seminar_id")."' ");
				}
				$messaging->insert_message(addslashes($message), $db2->f("username"), "____%system%____", FALSE, FALSE, "1");
				restoreLanguage();
			}
		}
	}
}
