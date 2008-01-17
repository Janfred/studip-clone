<?php
/* vim: noexpandtab */
/*
teilnehmer.php - Anzeige der Teilnehmer eines Seminares
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check('user');

include 'lib/seminar_open.php'; //hier werden die sessions initialisiert

require_once ('lib/msg.inc.php');
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('lib/admission.inc.php');	//Funktionen der Teilnehmerbegrenzung
require_once ('lib/statusgruppe.inc.php');	//Funktionen der Statusgruppen
require_once ('lib/messaging.inc.php');	//Funktionen des Nachrichtensystems
require_once ('config.inc.php');	//We need the config for some parameters of the class of the Veranstaltung
require_once('lib/user_visible.inc.php');
require_once('lib/export/export_studipdata_func.inc.php');
require_once('lib/classes/UserPic.class.php');

if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
}
$db = new DB_Seminar;
$db2 = new DB_Seminar;

$show_user_picture = false;
/*
* set the user_visibility of all unkowns to their global visibility
*/

$db->query("SELECT user_id FROM admission_seminar_user WHERE visible = 'unknown' AND seminar_id = '".$SessSemName[1]."'");
while ($db->next_record()) {
	$visible = (get_visibility_by_id($db->f("user_id"))) ? "yes" : "no";
	$db2->query("UPDATE admission_seminar_user SET visible = '$visible' WHERE user_id = '".$db->f("user_id")."' AND seminar_id = '".$SessSemName[1]."'");
}

$db->query("SELECT user_id FROM seminar_user WHERE visible = 'unknown' AND Seminar_id = '".$SessSemName[1]."'");
while ($db->next_record()) {
	$visible = (get_visibility_by_id($db->f("user_id"))) ? "yes" : "no";
	$db2->query("UPDATE seminar_user SET visible = '$visible' WHERE user_id = '".$db->f("user_id")."' AND Seminar_id = '".$SessSemName[1]."'");
}

/* ---------------------------------- */

if ($cmd == "make_me_visible" && !$perm->have_studip_perm('tutor',$SessSemName[1])) {
	if ($mode == "participant") {
		$db->query("UPDATE seminar_user SET visible = 'yes' WHERE user_id = '".$auth->auth['uid']."' AND Seminar_id = '".$SessSemName[1]."'");
	} elseif ($mode == "awaiting") {
		$db->query("UPDATE admission_seminar_user SET visible = 'yes' WHERE user_id = '".$auth->auth['uid']."' AND seminar_id = '".$SessSemName[1]."'");
	}
}

if ($cmd == "make_me_invisible" && !$perm->have_studip_perm('tutor',$SessSemName[1])) {
	if ($mode == "participant") {
		$db->query("UPDATE seminar_user SET visible = 'no' WHERE user_id = '".$auth->auth['uid']."' AND Seminar_id = '".$SessSemName[1]."'");
	} else {
		$db->query("UPDATE admission_seminar_user SET visible = 'no' WHERE user_id = '".$auth->auth['uid']."' AND seminar_id = '".$SessSemName[1]."'");
	}
}

if ($rechte) {
	$HELP_KEYWORD="Basis.VeranstaltungenVerwaltenTeilnehmer";
} else {
	$HELP_KEYWORD="Basis.InVeranstaltungTeilnehmer";
}
$CURRENT_PAGE = $SessSemName["header_line"]. " - " . _("TeilnehmerInnen");
if ($cmd != "send_sms_to_all" && $cmd != "send_sms_to_waiting") {
	// Start  of Output
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen

	checkObject();
	checkObjectModule("participants");

} else {
	if ($cmd == "send_sms_to_all" && $who != "accepted") {
		$sess->register("sms_data");
		$db->query("SELECT b.username FROM seminar_user a, auth_user_md5 b WHERE a.Seminar_id = '".$SessSemName[1]."' AND a.user_id = b.user_id AND a.status = '$who'");
		$sms_data = "";
		$sms_data['tmpsavesnd'] = 1;
		while ($db->next_record()) {
			$data[] = $db->f("username");
		}
		$sms_data['p_rec'] = $data;
		page_close(NULL);
		header("Location: sms_send.php");
		die;
	} else if ($cmd == "send_sms_to_waiting" || $who == "accepted") {
		$sess->register("sms_data");
		if (!$who) $who = "awaiting";
		$db->query("SELECT b.username FROM admission_seminar_user a, auth_user_md5 b WHERE a.seminar_id = '".$SessSemName[1]."' AND a.user_id = b.user_id AND status = '$who'");
		$sms_data = "";
		$sms_data['tmpsavesnd'] = 1;
		while ($db->next_record()) {
			$data[] = $db->f("username");
		}
		$sms_data['p_rec'] = $data;
		page_close(NULL);
		header("Location: sms_send.php");
		die;
	}
}

include ('lib/include/links_openobject.inc.php');

$messaging=new messaging;
$cssSw=new cssClassSwitcher;

if ($sms_msg) {
	$msg = $sms_msg;
	$sms_msg = '';
	$sess->unregister('sms_msg');
}

// Aenderungen nur in dem Seminar, in dem ich gerade bin...
	$id=$SessSemName[1];

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$db4=new DB_Seminar;
$db5=new DB_Seminar;

$csv_not_found = array();

/*
 * This function checks if a the given user has to be shown (is in the array
 * of downpulled users)
 *
 * @param  user_id integer
 *
 * returns boolean
 *
 */
function is_opened($user_id) {
	global $open_users;

	if (!isset($open_users)) return FALSE;
	if (array_search($user_id, $open_users) === FALSE) {
		return FALSE;
	} else {
		return TRUE;
	}
}

if (($cmd == "change_view") && (isset($view_order))) {
	if (!isset($indikator)) {
		$sess->register("indikator");
	}
	$indikator = $view_order;
}

// get user_id if somebody wants more infos about an user
if (($cmd == "allinfos") && ($rechte)) {
	if (isset($$area)) {
		unset($$area);
		$sess->unregister($area);
	} else {
		$sess->register($area);
		$$area = TRUE;
	}
}

if ((($cmd == "moreinfos") || ($cmd == "lessinfos")) && ($rechte)) {
	$db->query("SELECT user_id FROM auth_user_md5 WHERE username = '$username'");
	$db->next_record();
	$user_id = $db->f("user_id");
	if (!isset($open_users)) {
		$sess->register("open_users");
		$open_users = array();
	}
	if (($z = array_search($user_id, $open_users)) === FALSE) {
		$open_users[] = $user_id;
	} else {
		unset($open_users[$z]);
	}
	sort ($open_users);
}

// Aktivitaetsanzeige an_aus

if ($cmd=="showscore") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("UPDATE seminare SET showscore = '1' WHERE Seminar_id = '$id'");
		$msg = "msg§" . _("Die Aktivit&auml;tsanzeige wurde aktiviert.") . "§";
	}
}

if ($cmd=="hidescore") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("UPDATE seminare SET showscore = '0' WHERE Seminar_id = '$id'");
		$msg = "msg§" . _("Die Aktivit&auml;tsanzeige wurde deaktiviert.") . "§";
	}
}

if (Seminar_Session::check_ticket($studipticket)){
	// edit special seminar_info of an user
	if ($cmd == "change_userinfo") {
		//first we have to check if he is really "Dozent" of this seminar
		if ($rechte) {
			$db->query("UPDATE admission_seminar_user SET comment = '$userinfo' WHERE seminar_id = '$id' AND user_id = '$user_id'");
			$db->query("UPDATE seminar_user SET comment = '$userinfo' WHERE Seminar_id = '$id' AND user_id = '$user_id'");
			$msg = "msg§" . _("Die Zusatzinformationen wurden ge&auml;ndert.") . "§";
		}
		$cmd = "moreinfos";
	}

	// Hier will jemand die Karriereleiter rauf...

	if ( ($cmd == "pleasure" && $username) || (isset($_REQUEST['do_autor_to_tutor_x']) && is_array($_REQUEST['autor_to_tutor'])) ){
		//erst mal sehen, ob er hier wirklich Dozent ist... Tutoren d&uuml;rfen andere nicht zu Tutoren befoerdern!
		if ($rechte AND $SemUserStatus != "tutor")  {
			$msgs = array();
			if ($cmd == "pleasure"){
				$pleasure = array($username);
			} else {
				$pleasure = (is_array($_REQUEST['autor_to_tutor']) ? array_keys($_REQUEST['autor_to_tutor']) : array());
			}
			foreach($pleasure as $username){
				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username' AND perms!='user' AND perms!='autor'");
				if ($db->next_record()) {
					$userchange = $db->f("user_id");
					$fullname = $db->f("fullname");
					$next_pos = get_next_position("tutor",$id);
					$db->query("UPDATE seminar_user SET status='tutor', position='$next_pos', visible='yes' WHERE Seminar_id = '$id' AND user_id = '$userchange' AND status='autor'");
					if($db->affected_rows()) $msgs[] = $fullname;
				}
			}
			$msg = "msg§" . sprintf(_("Bef&ouml;rderung von %s durchgef&uuml;hrt"), htmlReady(join(', ',$msgs))) . "§";
		}
		else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
	}

	// jemand ist der anspruchsvollen Aufgabe eines Tutors nicht gerecht geworden...

	if ( ($cmd == "pain" && $username) || (isset($_REQUEST['do_tutor_to_autor_x']) && is_array($_REQUEST['tutor_to_autor'])) ){
		//erst mal sehen, ob er hier wirklich Dozent ist... Tutoren d&uuml;rfen andere Tutoren nicht rauskicken!
		if ($rechte AND $SemUserStatus != "tutor") {
			$msgs = array();
			if ($cmd == "pain"){
				$pain = array($username);
			} else {
				$pain = (is_array($_REQUEST['tutor_to_autor']) ? array_keys($_REQUEST['tutor_to_autor']) : array());
			}
			foreach($pain as $username){
				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
				$db->next_record();
				$userchange = $db->f("user_id");
				$fullname = $db->f("fullname");

        		$db->query("SELECT position FROM seminar_user WHERE user_id = '$userchange'");
         		$db->next_record();
         		$pos = $db->f("position");

				$db->query("UPDATE seminar_user SET status='autor', position=0 WHERE Seminar_id = '$id' AND user_id = '$userchange' AND status='tutor'");

         		re_sort_tutoren($id, $pos);

				if($db->affected_rows()) $msgs[] = $fullname;
			}
			if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
				$msg = "msg§" . sprintf (_("Das Mitglied %s wurde entlassen und auf den Status 'Autor' zur&uuml;ckgestuft."), htmlReady(join(', ',$msgs))) . "§";
			} else {
				$msg = "msg§" . sprintf (_("Der/die TutorIn %s wurde entlassen und auf den Status 'Autor' zur&uuml;ckgestuft."), htmlReady(join(', ',$msgs))) . "§";
			}
		}
		else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
	}

	// jemand ist zu bloede, sein Seminar selbst zu abbonieren...

	if ( ($cmd == "schreiben" && $username) || (isset($_REQUEST['do_user_to_autor_x']) && is_array($_REQUEST['user_to_autor'])) ){
		//erst mal sehen, ob er hier wirklich Dozent ist...
		if ($rechte) {
			$msgs = array();
			if ($cmd == "schreiben"){
				$schreiben = array($username);
			} else {
				$schreiben = (is_array($_REQUEST['user_to_autor']) ? array_keys($_REQUEST['user_to_autor']) : array());
			}
			foreach($schreiben as $username){
				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username' AND perms != 'user'");
				if ($db->next_record()) {
					$userchange = $db->f("user_id");
					$fullname = $db->f("fullname");
					$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$userchange' AND status='user'");
					if($db->affected_rows()) $msgs[] = $fullname;
				}
			}
			$msg = "msg§" . sprintf(_("User %s wurde als Autor in die Veranstaltung aufgenommen."), htmlReady(join(', ',$msgs))) . "§";
		}
		else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
	}

	// jemand sollte erst mal das Maul halten...

	if ( ($cmd == "lesen" && $username) || (isset($_REQUEST['do_autor_to_user_x']) && is_array($_REQUEST['autor_to_user'])) ){
		//erst mal sehen, ob er hier wirklich Dozent ist...
		if ($rechte) {
			$msgs = array();
			if ($cmd == "lesen"){
				$lesen = array($username);
			} else {
				$lesen = (is_array($_REQUEST['autor_to_user']) ? array_keys($_REQUEST['autor_to_user']) : array());
			}
			foreach($lesen as $username){
				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
				$db->next_record();
				$userchange = $db->f("user_id");
				$fullname = $db->f("fullname");
				$db->query("UPDATE seminar_user SET status='user' WHERE Seminar_id = '$id' AND user_id = '$userchange' AND status='autor'");
				if($db->affected_rows()) $msgs[] = $fullname;
			}
			$msg = "msg§" . sprintf(_("Der/die AutorIn %s wurde auf den Status 'Leser' zur&uuml;ckgestuft."), htmlReady(join(', ',$msgs))) . "§";
			$msg.= "info§" . _("Um jemanden permanent am Schreiben zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Schreiben nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.") . "<br>\n"
					. _("Dann k&ouml;nnen sich weitere BenutzerInnen nur noch mit Kenntnis des Veranstaltungs-Passworts als 'Autor' anmelden.") . "§";
		}
		else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
	}

	// und tschuess...

	if ( ($cmd == "raus" && $username) || (isset($_REQUEST['do_user_to_null_x']) && is_array($_REQUEST['user_to_null'])) ){
		//erst mal sehen, ob er hier wirklich Dozent ist...
		if ($rechte) {
			$msgs = array();
			if ($cmd == "raus"){
				$raus = array($username);
			} else {
				$raus = (is_array($_REQUEST['user_to_null']) ? array_keys($_REQUEST['user_to_null']) : array());
			}
			foreach($raus as $username){
				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
				$db->next_record();
				$userchange = $db->f("user_id");
				$fullname = $db->f("fullname");
				$db->query("DELETE FROM seminar_user WHERE Seminar_id = '$id' AND user_id = '$userchange' AND status='user'");
				if($db->affected_rows()){
					setTempLanguage($userchange);
					if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
						$message = sprintf(_("Ihr Abonnement der Veranstaltung **%s** wurde von einem/r LeiterIn oder AdministratorIn aufgehoben."), $SessSemName[0]);
					} else {
						$message= sprintf(_("Ihr Abonnement der Veranstaltung **%s** wurde von einem/r DozentIn oder AdministratorIn aufgehoben."), $SessSemName[0]);
					}
					restoreLanguage();
					$messaging->insert_message(mysql_escape_string($message), $username, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Abonnement aufgehoben"), TRUE);
					// raus aus allen Statusgruppen
					RemovePersonStatusgruppeComplete ($username, $id);
					$msgs[] = $fullname;
				}
			}
			//Pruefen, ob es Nachruecker gibt
			update_admission($id);

			$msg = "msg§" . sprintf(_("LeserIn %s wurde aus der Veranstaltung entfernt."), htmlReady(join(', ',$msgs))) . "§";
			$msg.= "info§" . _("Um jemanden permanent am Lesen zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Lesen nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.") . "<br>\n"
					. _("Dann k&ouml;nnen sich weitere BenutzerInnen nur noch mit Kenntnis des Veranstaltungs-Passworts anmelden.") . "§";
		}
		else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
	}

	//aus der Anmelde- oder Warteliste entfernen
	if ( ($cmd == "admission_raus" && $username)  || (isset($_REQUEST['do_admission_delete_x']) && is_array($_REQUEST['admission_delete']) ) ) {
		//erst mal sehen, ob er hier wirklich Dozent ist...
		if ($rechte) {
			$msgs = array();
			if ($cmd == "admission_raus"){
				$adm_delete[] = $username;
			} else {
				$adm_delete = (is_array($_REQUEST['admission_delete']) ? array_keys($_REQUEST['admission_delete']) : array());
			}
			foreach($adm_delete as $username){
				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
				$db->next_record();
				$userchange=$db->f("user_id");
				$fullname = $db->f("fullname");
				$db->query("DELETE FROM admission_seminar_user WHERE seminar_id = '$id' AND user_id = '$userchange'");
				if($db->affected_rows()){
					setTempLanguage($userchange);
					if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
						if (!$accepted) {
							$message= sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn von der Warteliste der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
						} else {
							$message= sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn aus der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
						}
					} else {
						if (!$accepted) {
							$message= sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn von der Warteliste der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
						} else {
							$message= sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn aus der Veranstaltung **%s** gestrichen und sind damit __nicht__ zugelassen worden."), $SessSemName[0]);
						}
					}
					restoreLanguage();

					$messaging->insert_message(mysql_escape_string($message), $username, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("nicht zugelassen in Veranstaltung"), TRUE);

					$msgs[] = $fullname;
				}
			}
			//Warteliste neu sortieren
			renumber_admission($id);
			if ($accepted) update_admission($id);
			$msg = "msg§". sprintf(_("LeserIn %s wurde aus der Anmelde bzw. Warteliste entfernt."), htmlReady(join(', ', $msgs))) . '§';
		} else {
			$msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
		}
	}
	if(isset($_REQUEST['admission_rein_x'])){
		$cmd = 'admission_rein';
		$username = $_REQUEST['admission_rein'];
	}
	//aus der Anmelde- oder Warteliste in die Veranstaltung hochstufen / aus der freien Suche als Tutoren oder Autoren eintragen
	if ((isset($_REQUEST['do_admission_insert_x']) && is_array($_REQUEST['admission_insert'])) || (($cmd == "admission_rein" || $cmd == "add_user") && $username)){
		//erst mal sehen, ob er hier wirklich Dozent ist...
		if ($rechte) {
			$msgs = array();
			if ($cmd == "admission_rein" || $cmd == "add_user"){
				$user_add[] = $username;
			} else {
				$user_add = (is_array($_REQUEST['admission_insert']) ? array_keys($_REQUEST['admission_insert']) : array());
			}
			foreach($user_add as $username){

				$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, a.* FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username = '$username'");
				$db->next_record();
				$userchange = $db->f("user_id");
				$fullname = $db->f("fullname");

				if ($cmd == "add_user" && !$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"] && (($db->f("perms") == "tutor" || $db->f("perms") == "dozent")) && ($perm->have_studip_perm("dozent", $id))){
					$status = 'tutor';
				} else {
					$status = 'autor';
				}

				$admission_user = insert_seminar_user($id, $userchange, $status, ($accepted || $_REQUEST['consider_contingent'] ? TRUE : FALSE), $_REQUEST['consider_contingent']);
				//Only if user was on the waiting list
				if ($admission_user == 2) {
					setTempLanguage($userchange);
					if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
						if (!$accepted) {
							$message = sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn aus der Warteliste in die Veranstaltung **%s** aufgenommen und sind damit zugelassen."), $SessSemName[0]);
						} else {
							$message = sprintf(_("Sie wurden von einem/r LeiterIn oder AdministratorIn zum/r TeilnehmerIn der Veranstaltung **%s** hochgestuft und sind damit zugelassen."), $SessSemName[0]);
						}
					} else {
						if (!$accepted) {
							$message = sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn aus der Warteliste in die Veranstaltung **%s** aufgenommen und sind damit zugelassen."), $SessSemName[0]);
						} else {
							$message = sprintf(_("Sie wurden von einem/r DozentIn oder AdministratorIn vom Status **vorläufig akzeptiert** zum/r TeilnehmerIn der Veranstaltung **%s** hochgestuft und sind damit zugelassen."), $SessSemName[0]);
						}
					}
					restoreLanguage();
					$messaging->insert_message(mysql_escape_string($message), $username, "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Eintragung in Veranstaltung"), TRUE);
				}
				$msgs[] = $fullname;
			}

			//Warteliste neu sortieren
			renumber_admission($id);

			if($admission_user){
				if ($cmd=="add_user") {
					$msg = "msg§" . sprintf(_("NutzerIn %s wurde in die Veranstaltung mit dem Status <b>%s</b> eingetragen."), htmlReady($fullname), $status) . "§";
				} else {
					if (!$accepted) {
						$msg = "msg§" . sprintf(_("NutzerIn %s wurde aus der Anmelde bzw. Warteliste mit dem Status <b>%s</b> in die Veranstaltung eingetragen."), htmlReady(join(', ', $msgs)), $status) . "§";
					} else {
						$msg = "msg§" . sprintf(_("NutzerIn %s wurde mit dem Status <b>%s</b> endgültig akzeptiert und damit in die Veranstaltung aufgenommen."), htmlReady(join(', ', $msgs)), $status) . "§";
					}
				}
			} else if($_REQUEST['consider_contingent']){
				$msg = "error§" . _("Es stehen keine weiteren Plätze mehr im Teilnehmerkontingent zur Verfügung.") . "§";
			}
		} else {
			$msg = "error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
		}
	}

	// import users from a csv-list
	if ($_REQUEST['cmd'] == 'csv' && $rechte) {
		$csv_mult_founds = array();
		$csv_count_insert = 0;
		$csv_count_multiple = 0;
		if ($_REQUEST['csv_import']) {
			$csv_lines = preg_split('/(\n\r|\r\n|\n|\r)/', trim($_REQUEST['csv_import']));
			foreach ($csv_lines as $csv_line) {
				$csv_name = preg_split('/[,\t]/', substr($csv_line, 0, 100),-1,PREG_SPLIT_NO_EMPTY);
				$csv_nachname = trim($csv_name[0]);
				$csv_vorname = trim($csv_name[1]);
				if ($csv_nachname){
					$db->query("SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, perms FROM auth_user_md5 a ".
					"LEFT JOIN user_info USING(user_id) LEFT JOIN seminar_user b ON (b.user_id=a.user_id AND b.seminar_id='$SessSemName[1]')  ".
					"WHERE perms IN ('autor','tutor','dozent') AND ISNULL(b.seminar_id) AND ".
					"(Nachname LIKE '" . $csv_nachname . "'"
					. ($csv_vorname ? " AND Vorname LIKE '" . $csv_vorname . "'" : '')
					. ") ORDER BY Nachname");
					if ($db->num_rows() > 1) {
						while ($db->next_record()) {
							$csv_mult_founds[$csv_line][] = $db->Record;
						}
						$csv_count_multiple++;
					} else if ($db->num_rows() > 0) {
						$db->next_record();
						if(insert_seminar_user($id, $db->f('user_id'), 'autor', isset($_REQUEST['consider_contingent']), $_REQUEST['consider_contingent'])){
							$csv_count_insert++;
						} elseif (isset($_REQUEST['consider_contingent'])){
							$csv_count_contingent_full++;
						}
					} else {
						// not found
						$csv_not_found[] = stripslashes($csv_nachname) . ($csv_vorname ? ', ' . stripslashes($csv_vorname) : '');
					}
				}
			}
		}
		if (sizeof($_REQUEST['selected_users'])) {
			foreach ($_REQUEST['selected_users'] as $selected_user) {
				if ($selected_user) {
					if(insert_seminar_user($id, get_userid($selected_user), 'autor', isset($_REQUEST['consider_contingent']), $_REQUEST['consider_contingent'])){
						$csv_count_insert++;
					} elseif (isset($_REQUEST['consider_contingent'])){
						$csv_count_contingent_full++;
					}
				}
			}
		}
		$msg = '';
		if (!$csv_count_multiple) {
			$_REQUEST['cmd'] = '';
		}
		if (!sizeof($csv_lines) && !sizeof($_REQUEST['selected_users'])) {
			$msg = 'error§' . _("Keine NutzerIn gefunden!") . '§';
			$_REQUEST['cmd'] = '';
		} else {
			if ($csv_count_insert) {
				$msg .=  'msg§' . sprintf(_("%s NutzerInnen als AutorIn in die Veranstaltung eingetragen!"),
						$csv_count_insert) . '§';
			}
			if ($csv_count_multiple) {
				$msg .= 'info§' . sprintf(_("%s NutzerInnen konnten <b>nicht eindeutig</b> zugeordnet werden! Nehmen Sie die Zuordnung am Ende dieser Seite manuell vor."),
						$csv_count_multiple) . '§';
			}
			if (sizeof($csv_not_found)) {
				$msg .= 'error§' . sprintf(_("%s NutzerInnen konnten <b>nicht</b> zugeordnet werden! Am Ende dieser Seite finden Sie die Namen, die nicht zugeordnet werden konnten."),
						sizeof($csv_not_found)) . '§';
			}
			if($csv_count_contingent_full){
				$msg .= 'error§' . sprintf(_("%s NutzerInnen konnten <b>nicht</b> zugeordnet werden, da das ausgewählte Kontingent keine freien Plätze hat."),
						$csv_count_contingent_full) . '§';
			}
		}
	}

	// so bin auch ich berufen?

	if (isset($add_tutor_x)) {
		//erst mal sehen, ob er hier wirklich Dozent ist...
		if ($rechte AND $SemUserStatus!="tutor") {
					// nur wenn wer ausgewaehlt wurde
			if ($u_id != "0") {
				$query = "SELECT DISTINCT b.user_id, username, Vorname, Nachname, inst_perms, perms FROM seminar_inst d LEFT JOIN user_inst a USING(Institut_id) ".
				"LEFT JOIN auth_user_md5  b USING(user_id) ".
				"LEFT JOIN seminar_user c ON (c.user_id=a.user_id AND c.seminar_id='$SessSemName[1]')  ".
				"WHERE d.seminar_id = '$SessSemName[1]' AND a.inst_perms IN ('tutor','dozent') AND ISNULL(c.seminar_id) ORDER BY Nachname";
				$db->query($query);
					// wer versucht denn da wen nicht zugelassenen zu berufen?
				if ($db->next_record()) {
					// so, Berufung ist zulaessig
					$db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$id' AND user_id = '$u_id'");
					if ($db2->next_record()) {
						// der Dozent hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Seminar. Na, auch egal...
						if ($db2->f("status") == "autor" || $db2->f("status") == "user") {
							// gehen wir ihn halt hier hochstufen
                     $next_pos = get_next_position("tutor",$id);
							$db2->query("UPDATE seminar_user SET status='tutor', position='$next_pos' WHERE Seminar_id = '$id' AND user_id = '$u_id'");
							if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
								$msg = "msg§" . sprintf (_("%s wurde zum Mitglied bef&ouml;rdert."), get_fullname($u_id,'full',1)) . "§";
							} else {
								$msg = "msg§" . sprintf (_("%s wurde auf den Status 'Tutor' bef&ouml;rdert."), get_fullname($u_id,'full',1)) . "§";
							}
							//kill from waiting user
							$db2->query("DELETE FROM admission_seminar_user WHERE seminar_id = '$id' AND user_id = '$u_id'");
							//reordner waiting list
							renumber_admission($id);
						} else {
							;	// na, das ist ja voellig witzlos, da tun wir einfach nix.
								// Nicht das sich noch ein Dozent auf die Art und Weise selber degradiert!
						}
					} else {  // ok, einfach aufnehmen.
						insert_seminar_user($id, $u_id, "tutor", FALSE);

						if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
							$msg = "msg§" . sprintf (_("%s wurde als Mitglied in die Veranstaltung aufgenommen."), get_fullname($u_id,'full',1));
						} else {
							$msg = "msg§" . sprintf (_("%s wurde als Tutor in die Veranstaltung aufgenommen."), get_fullname($u_id,'full',1));
						}

						setTempLanguage($userchange);
						if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
							$message= sprintf(_("Sie wurden vom einem/r LeiterIn oder AdministratorIn in die Veranstaltung **%s** aufgenommen."), $SessSemName[0]);
						} else {
							$message= sprintf(_("Sie wurden vom einem/r DozentIn oder AdministratorIn in die Veranstaltung **%s** aufgenommen."), $SessSemName[0]);
						}
						restoreLanguage();
						$messaging->insert_message(mysql_escape_string($message), get_username($u_id), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Eintragung in Veranstaltung"), TRUE);
					}
				}
				else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
			}
			else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
		}
		else $msg ="error§" . _("Sie haben leider nicht die notwendige Berechtigung für diese Aktion.") . "§";
	}
}
//Alle fuer das Losen anstehenden Veranstaltungen bearbeiten (wenn keine anstehen wird hier nahezu keine Performance verbraten!)
check_admission();


$db5->query("SELECT * FROM teilnehmer_view WHERE seminar_id = '$id'");

if ($perm->have_perm("dozent")) {
	$sem_type = $SessSemName["art_num"];

	$sem_view_rights = array();

	$db5->query("SELECT * FROM teilnehmer_view WHERE seminar_id = '$sem_type'");

	while ($db5->next_record()) {
	    $sem_view_rights[$db5->f("datafield_id")] = TRUE;
	}

	if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"])
		$gruppe = array ("dozent" => _("DozentInnen"),
					  "tutor" => _("TutorInnen"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"),
					  "accepted" => _("Vorl&auml;ufig akzeptierte TeilnehmerInnen"));
	else
		$gruppe = array ("dozent" => _("LeiterInnen"),
					  "tutor" => _("Mitglieder"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"),
					  "accepted" => _("Vorl&auml;ufig akzeptierte TeilnehmerInnen"));
} else {
	if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"])
		$gruppe = array ("dozent" => _("DozentInnen"),
					  "tutor" => _("TutorInnen"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"));
	else
		$gruppe = array ("dozent" => _("LeiterInnen"),
					  "tutor" => _("Mitglieder"),
					  "autor" => _("AutorInnen"),
					  "user" => _("LeserInnen"));
}

$multiaction['tutor'] = array('insert' => null, 'delete' => array('tutor_to_autor', (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"] ? _("Ausgewählte Tutoren entlassen") : _("Ausgewählte Mitglieder entlassen"))));
$multiaction['autor'] = array('insert' => array('autor_to_tutor',(!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"] ? _("Ausgewählte Benutzer als Tutor eintragen") : _("Ausgewählte Benutzer als Mitglied eintragen"))), 'delete' => array('autor_to_user', _("Ausgewählten Benutzern das Schreibrecht entziehen")));
$multiaction['user'] = array('insert' => array('user_to_autor',_("Ausgewählten Benutzern das Schreibrecht erteilen")), 'delete' => array('user_to_null', _("Ausgewählte Benutzer aus der Veranstaltung entfernen")));
$multiaction['accepted'] = array('insert' => array('admission_insert',_("Ausgewählte Benutzer akzeptieren")), 'delete' => array('admission_delete', _("Ausgewählte Benutzer aus der Veranstaltung entfernen")));

$db->query("SELECT COUNT(user_id) as teilnehmer, COUNT(IF(admission_studiengang_id <> '',1,NULL)) as teilnehmer_kontingent FROM seminar_user WHERE seminar_id='".$SessSemName[1]."' AND status IN('autor','user')");
$db->next_record();
$anzahl_teilnehmer = $db->f('teilnehmer');
$anzahl_teilnehmer_kontingent = $db->f('teilnehmer_kontingent');
?>

		<script type="text/javascript">
			function invert_selection(prefix, theform){
				my_elements = document.forms[theform].elements;
				for(i = 0; i < my_elements.length; ++i){
					if(my_elements[i].type == 'checkbox' && my_elements[i].name.substr(0, prefix.length) == prefix){
					if(my_elements[i].checked)
						my_elements[i].checked = false;
					else
						my_elements[i].checked = true;
					}
				}
			return false;
			}

		</script>
		<table cellspacing="0" border="0" width="100%">
		<tr>
		<td colspan="2" class="blank">
			<?
			$db3->query("SELECT status, visible FROM seminar_user WHERE user_id = '".$auth->auth['uid']."' AND Seminar_id = '$SessionSeminar'");
			$visible_mode = "false";

			if ($db3->num_rows() > 0) {
				$db3->next_record();
				if ($db3->f("visible") == "yes") {
					$iam_visible = true;
				} else {
					$iam_visible = false;
				}
				if ($db3->f("status") == "user" || $db3->f("status")=="autor") {
					$visible_mode = "participant";
				} else {
					$iam_visible = true;
					$visible_mode = false;
				}
			}

			$db3->query("SELECT status, visible FROM admission_seminar_user WHERE user_id = '".$auth->auth['uid']."' AND seminar_id = '$SessionSeminar'");
			if ($db3->num_rows() > 0) {
				if ($db3->f("visible") == "yes") {
					$iam_visible = true;
				} else {
					$iam_visible = false;
				}
				$visible_mode = "awaiting";
			}
		if (!$perm->have_studip_perm('tutor',$SessSemName[1])) {
			if ($iam_visible) {
		?>
		<br/>
			<b><?=	_("Sie erscheinen für andere TeilnehmerInnen sichtbar auf der Teilnehmerliste."); ?></b><br/>
			<a href="<?=$PHP_SELF?>?cmd=make_me_invisible&mode=<?=$visible_mode?>">
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/vote-icon-invisible.gif" border="0">
			<?= _("Klicken Sie hier, um unsichtbar zu werden.") ?>
			</a>
		<br/>
		<?
			} else {
		?>
		<br/>
			<b><?=	_("Sie erscheinen nicht auf der Teilnehmerliste."); ?></b><br/>
			<a href="<?=$PHP_SELF?>?cmd=make_me_visible&mode=<?=$visible_mode?>">
			<img src="<?=$GLOBALS['ASSETS_URL']?>images/vote-icon-visible.gif" border="0">
			<?= _("Klicken Sie hier, um sichtbar zu werden.") ?>
			</a>
		<br/>
		<?
			}
		}
			?>
		</td>
	</tr>

	<? if ($rechte) { ?>

	<tr>
		<td class="blank" colspan="2" align="left">
    		<table class="blank" border=0 cellpadding=0 cellspacing=0>
					<tr>
						<td class="blank">&nbsp;</td>
					</tr>
					<tr>
      			<td class="steelkante2" valign="middle">
							<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="22" width="5">
						</td>
      			<td class="steelkante2" valign="middle">
							<font size="-1"><?=_("Sortierung:")?>&nbsp;</font>
						<? if (isset($indikator) && ($indikator == "abc")) { ?>
     				</td>
						<td nowrap class="steelgraulight_shadow" valign="middle">
							&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/forumrot_indikator.gif" align="absmiddle">
							<font size="-1"><?=_("Alphabetisch")?></font>&nbsp;
						<? } else { ?>
						</td>
						<td nowrap class="steelkante2" valign="middle">
							&nbsp;
							<a href="<?=$PHP_SELF?>?view_order=abc&cmd=change_view">
								<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/forum_indikator_grau.gif" border="0" align="absmiddle">
								<font size="-1" color="#555555"><?=_("Alphabetisch")?></font>
							</a>
							&nbsp;
						<? } ?>
						<? if (isset($indikator) && ($indikator == "date")) { ?>
     				</td>
						<td nowrap class="steelgraulight_shadow" valign="middle">
							&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/forumrot_indikator.gif" align="absmiddle">
							<font size="-1"><?=_("Anmeldedatum")?></font>&nbsp;
						<? } else { ?>
						</td>
						<td nowrap class="steelkante2" valign="middle">
							&nbsp;
							<a href="<?=$PHP_SELF?>?view_order=date&cmd=change_view">
								<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/forum_indikator_grau.gif" border="0" align="absmiddle">
								<font size="-1" color="#555555"><?=_("Anmeldedatum")?></font>
							</a>
							&nbsp;
						<? } ?>
						<? if (isset($indikator) && ($indikator == "active")) { ?>
     				</td>
						<td nowrap class="steelgraulight_shadow" valign="middle">
							&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/forumrot_indikator.gif" align="absmiddle">
							<font size="-1"><?=_("Aktivität")?></font>&nbsp;
						<? } else { ?>
						</td>
						<td nowrap class="steelkante2" valign="middle">
							&nbsp;
							<a href="<?=$PHP_SELF?>?view_order=active&cmd=change_view">
								<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/forum_indikator_grau.gif" border="0" align="absmiddle">
								<font size="-1" color="#555555"><?=_("Aktivität")?></font>
							</a>
							&nbsp;
						<? } ?>
						</td>

							<td nowrap align="right" class="steelkante2" valign="middle"> <?

			$db3->query ("SELECT showscore  FROM seminare WHERE Seminar_id = '$SessionSeminar'");
			while ($db3->next_record()) {
				if ($db3->f("showscore") == 1) {
					if ($rechte) {
						printf ("<a href=\"$PHP_SELF?cmd=hidescore\"><img src=\"".$GLOBALS['ASSETS_URL']."images/showscore1.gif\" border=\"0\" %s>&nbsp; &nbsp; </a>", tooltip(_("Aktivitätsanzeige eingeschaltet. Klicken zum Ausschalten.")));
					} else {
						echo "&nbsp; ";
					}
					$showscore = TRUE;
				} else {
					if ($rechte) {
						printf ("<a href=\"$PHP_SELF?cmd=showscore\"><img src=\"".$GLOBALS['ASSETS_URL']."images/showscore0.gif\" border=\"0\" %s>&nbsp; &nbsp; </a>", tooltip(_("Aktivitätsanzeige ausgeschaltet. Klicken zum Einschalten.")));
					} else {
						echo "&nbsp; ";
					}
					$showscore = FALSE;
				}
			}
		?>
		</td>

<td><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/balken.jpg"></td>
					<tr>
				</table>
		</td>
	</tr>
	<tr>
		<td class="blank" width="100%" colspan="2">
		<a href="sms_send.php?sms_source_page=teilnehmer.php&course_id=<?=$SessSemName[1]?>&emailrequest=1&subject=<?=rawurlencode($SessSemName[0])?>&filter=all">
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/mailnachricht.gif" border="0" vspace="3" hspace="3" align="absmiddle">
		<span style="font-size:80%">
		<?=_("Systemnachricht mit Emailweiterleitung an alle Teilnehmer verschicken")?>
		</span></a>
		</td>
	</tr>
	<? } ?>

	<tr>
		<td class="blank" width="100%" colspan="2">&nbsp;
			<?
			if ($msg) parse_msg($msg);
			?>
		</td>
	</tr>
<tr>
	<td class="blank" colspan="2">

	<table width="99%" border="0"  cellpadding="2" cellspacing="0" align="center">

<?
$studipticket = Seminar_Session::get_ticket();

//Index berechnen
$db3->query ("SELECT count(dokument_id) AS count_doc FROM dokumente WHERE seminar_id = '$SessionSeminar'");
if ($db3->next_record()) {
	$aktivity_index_seminar = $db3->f("count_doc") * 5;
}
$db3->query ("SELECT count(topic_id) AS count_post FROM px_topics WHERE Seminar_id = '$SessionSeminar'");
if ($db3->next_record()) {
	$aktivity_index_seminar += $db3->f("count_post");
}
$db3->query ("SELECT count(user_id) AS count_pers FROM seminar_user WHERE Seminar_id = '$SessionSeminar'");
if ($db3->next_record() && $db3->f("count_pers")) {
	$aktivity_index_seminar /= $db3->f("count_pers");
}

//Veranstaltungsdaten holen
$db3->query ("SELECT admission_type, admission_selection_take_place, admission_turnout FROM seminare WHERE Seminar_id = '$SessionSeminar'");
$db3->next_record();
if ($rechte) {
	if ($db3->f("admission_type") == 1 || $db3->f("admission_type") == 2)
		$colspan=10;
	else
		$colspan=9;
} else
	$colspan=7;
if ($showscore==TRUE) $colspan++;

$accepted_columns = array('Nachname', 'mkdate', 'doll DESC');
if (!isset($sortby)  || !in_array($sortby, $accepted_columns))  $sortby = '';

while (list ($key, $val) = each ($gruppe)) {

	if (!isset($sortby) || $sortby == "") {
		switch($indikator) {
			case "date":
				$sortby .= "mkdate";
				break;

			case "active":
				$sortby = "doll DESC";
				break;

			default:
				$sortby .= "Nachname";
				break;
		}
	}

	if (isset($sortby)) {
		$sort = "ORDER BY $sortby";
	} else {
		$sort = "";
	}

	$counter=1;

	if ($key == "accepted") {  // modify query if user is in admission_seminar_user and not in seminar_user
		$tbl = "admission_seminar_user";
		$tbl2 = "";
		$tbl3 = "s";
	} else {
		$tbl = "seminar_user";
		$tbl2 = "admission_";
		$tbl3 = "S";
	}

	$db->query ("SELECT $tbl.visible, $tbl.mkdate, comment, $tbl.user_id, ". $_fullname_sql['full'] ." AS fullname,
				username, status, count(topic_id) AS doll,  studiengaenge.name, ".$tbl.".".$tbl2."studiengang_id
				AS studiengang_id
				FROM $tbl LEFT JOIN px_topics USING (user_id,".$tbl3."eminar_id)
				LEFT JOIN auth_user_md5 ON (".$tbl.".user_id=auth_user_md5.user_id)
				LEFT JOIN user_info ON (auth_user_md5.user_id=user_info.user_id)
				LEFT JOIN studiengaenge ON (".$tbl.".".$tbl2."studiengang_id = studiengaenge.studiengang_id)
				WHERE ".$tbl.".".$tbl3."eminar_id = '$SessionSeminar'
				AND status = '$key'$visio GROUP by ".$tbl.".user_id $sort");

	if ($db->num_rows()) { //Only if Users were found...
		$info_is_open = false;
		$tutor_count = 0;
	// die eigentliche Teil-Tabelle
	if($key != 'dozent') echo "<form name=\"$key\" action=\"$PHP_SELF?studipticket=$studipticket\" method=\"post\">";
	if($rechte && $key == 'autor' 	&& (($db3->f("admission_type") == 1 || $db3->f("admission_type") == 2))){
		echo '<tr><td class="blank" colspan="'.$colspan.'" align="right"><font size="-1">';
		printf(_("<b>Teilnahmebeschränkte Veranstaltung</b> -  Teilnehmerkontingent: %s, davon belegt: %s, zusätzlich belegt: %s"),
			$db3->f('admission_turnout'), $anzahl_teilnehmer_kontingent, $anzahl_teilnehmer - $anzahl_teilnehmer_kontingent);
		echo '</font></td></tr>';
	}
	echo "<tr height=28>";
	if ($showscore==TRUE)
		echo "<td class=\"steel\" width=\"1%\">&nbsp; </td>";
	print "<td class=\"steel\" width=\"1%\" align=\"center\" valign=\"middle\">";
	if ($rechte) {
		$show_area = "show_".$key;
		if (isset($$show_area)) {
			$image = "forumgraurunt.gif";
			$tooltiptxt = _("Informationsfelder wieder hochklappen");
		} else {
			$image = "forumgrau.gif";
			$tooltiptxt = _("Alle Informationsfelder aufklappen");
		}
		print "<a href=\"$PHP_SELF?cmd=allinfos&area=show_$key\">";
		print "<img src=\"".$GLOBALS['ASSETS_URL']."images/$image\" border=\"0\" ".tooltip($tooltiptxt)."></a>";
	} else {
		print "&nbsp; ";
	}

	print "</td>";

	echo '<td class="steel" width="19%" align="left">'.
	       '<img src="'.$GLOBALS['ASSETS_URL'].'images/blank.gif" width="1" height="20">'.
	       '<font size="-1"><b>' . $val . '</b></font>'.
	     '</td>';

	// mail button einfügen
	if ($rechte) {
		echo '<td class="steel" width="10%">';
		// hier kann ne flag setzen um mail extern zu nutzen
		if ($ENABLE_EMAIL_TO_STATUSGROUP) {
			$db_mail = new DB_Seminar();
			$db_mail->query("SELECT Email FROM seminar_user su ".
			                "LEFT JOIN auth_user_md5 au ON (su.user_id = au.user_id) ".
			                "WHERE su.seminar_id = '".$SessSemName[1]."' ".
			                "AND status = '$key'");
			$users = array();
			while ($db_mail->next_record()) {
				$users[] = $db_mail->f("Email");
			}
			$all_user = implode(',', $users);

			echo '<a href="mailto:'.$all_user.'"><img src="'.$GLOBALS['ASSETS_URL'].'images/mailnachricht.gif" title="'._("E-Mail an alle Gruppenmitglieder verschicken").'" alt="'.("E-Mail an alle Gruppenmitglieder verschicken").'" border="0" align="absmiddle"/></a>';
		}

		echo '<a href="teilnehmer.php?cmd=send_sms_to_all&amp;who='.$key.'"><img src="'.$GLOBALS['ASSETS_URL'].'images/nachricht1.gif" title="'.sprintf(_("Nachricht an alle %s schicken"), $val).'" alt="'.sprintf(_("Nachricht an alle %s schicken"), $val).'" border="0" align="absmiddle"></a>';
		echo '</td>';
	} else {
		echo '<td class="steel">&nbsp;</td>';
	}

	echo "</b></font></td>";

	if ($key != "dozent" && $rechte) {
		printf("<td class=\"steel\" width=\"1%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Anmeldedatum"));
	} else if ($key == "dozent" && $rechte) {
		printf("<td class=\"steel\" width=\"9%%\" align=\"center\" valign=\"bottom\">&nbsp;</td>");
	}
	printf("<td class=\"steel\" width=\"10%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Postings"));
	printf("<td class=\"steel\" width=\"10%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Dokumente"));
	printf("<td class=\"steel\" width=\"9%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Nachricht"));


	if ($rechte) {
		$tooltip = tooltip(_("Klicken, um Auswahl umzukehren"),false);
		if ($db3->f("admission_type"))
			$width=15;
		else
			$width=20;

		if ($key == "dozent") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\"><b>&nbsp;</b></td>", $width);
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\"><b>&nbsp;</b></td>", $width);
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=\"center\" colspan=\"2\"><b>&nbsp;</b></td>";
		}

		if ($key == "tutor") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\"><font size=\"-1\"><b>&nbsp;</b></font></td>", $width);
			if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"tutor_to_autor\" onClick=\"return invert_selection('tutor_to_autor','%s');\" %s><b>%s</b></a></font></td>", $width, $key, $tooltip, _("Mitglied entlassen"));
			} else {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"tutor_to_autor\" onClick=\"return invert_selection('tutor_to_autor','%s');\" %s><b>%s</b></a></font></td>", $width, $key, $tooltip, _("TutorIn entlassen"));
			}
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=\"center\"><b>&nbsp;</b></td>";
		}

		if ($key == "autor") {
			if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"autor_to_tutor\" onClick=\"return invert_selection('autor_to_tutor','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip, _("als Mitglied eintragen"));
			} else {
				printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"autor_to_tutor\" onClick=\"return invert_selection('autor_to_tutor','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip, _("als TutorIn eintragen"));
			}
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"autor_to_user\" onClick=\"return invert_selection('autor_to_user','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip, _("Schreibrecht entziehen"));
			if ($db3->f("admission_type"))
				printf("<td class=\"steel\" width=\"10%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><b>%s</b></font></td>", _("Kontingent"));
		}

		if ($key == "user") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"user_to_autor\" onClick=\"return invert_selection('user_to_autor','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip, _("Schreibrecht erteilen"));
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"user_to_null\" onClick=\"return invert_selection('user_to_null','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip, _("BenutzerIn entfernen"));
			if ($db3->f("admission_type"))
				print"<td class=\"steel\" width=\"10%\" align=\"center\"><b>&nbsp;</b></td>";
		}

		if ($key == "accepted") {
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"admission_insert\" onClick=\"return invert_selection('admission_insert','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip,  _("Akzeptieren"));
			printf ("<td class=\"steel\" width=\"%s%%\" align=\"center\" valign=\"bottom\"><font size=\"-1\"><a name=\"admission_delete\" onClick=\"return invert_selection('admission_delete','%s');\" %s><b>%s</b></a></font></td>",  $width, $key, $tooltip, _("BenutzerIn entfernen"));
			if ($db3->f("admission_type"))
				print"<td class=\"steel\" width=\"10%\" align=\"center\"><b>&nbsp;</b></td>";

		}
	}

	echo "</tr>";
	$c=1;
	$i_see_everybody = $perm->have_studip_perm('tutor', $SessSemName[1]);

	while ($db->next_record()) {
		if (($db->Record['user_id'] == $user->id) && ($db->f('visible') != 'yes')) {
			$db->Record['fullname'] .= ' ('._("unsichtbar").')';
		}

	if ($c % 2) {   // switcher fuer die Klassen
		$class="steel1";
		$class2="colorline";
	} else {
		$class="steelgraulight";
		$class2="colorline2";
	}

//  Elemente holen

	$Dokumente = 0;
	$UID = $db->f("user_id");
	$db2->query ("SELECT count(dokument_id) AS doll FROM dokumente WHERE seminar_id = '$SessionSeminar' AND user_id = '$UID' GROUP by seminar_id");
	while ($db2->next_record()) {
		$Dokumente = $db2->f("doll");
	}
	$postings_user = $db->f("doll");

// Aktivitaet berechnen

	if ($showscore == TRUE) {
		if ($aktivity_index_seminar == 0){
	            $aktivity_index_user = 0; // to avoid div by zero
                } else {
		    $aktivity_index_user =  (($postings_user + (5 * $Dokumente)) / $aktivity_index_seminar) * 100;
		}
		if ($aktivity_index_user > 100) {
			$offset = $aktivity_index_user / 4;
			if ($offset < 0) {
				$offset = 0;
			} elseif ($offset > 200) {
				$offset = 200;
			}
			$red = dechex(200-$offset) ;
			$green = dechex(200);
			$blue = dechex(200-$offset) ;
			if ($offset > 184)  {
				$red = "0".$red;
				$blue = "0".$blue;
			}
		} else {
			$red = dechex(200);
			$green = dechex($aktivity_index_user * 2) ;
			$blue = dechex($aktivity_index_user * 2) ;
			if ($aktivity_index_user < 8)  {
				$green = "0".$green;
				$blue = "0".$blue;
			}
		}
	}


// Anzeige der eigentlichen Namenzeilen

	echo "<tr>";
	if ($showscore == TRUE) {
		printf("<td bgcolor=\"#%s%s%s\" class=\"%s\">", $red, $green,$blue, $class2);
		printf("<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" %s width=\"10\" heigth=\"10\"></td>", tooltip(_("Aktivität: ").round($aktivity_index_user)."%"));
	}

	if ($rechte) {
		if (is_opened($db->f("user_id"))) {
			$link = $PHP_SELF."?cmd=lessinfos&username=".$db->f("username")."#".$db->f("username");
			$img = "forumgraurunt.gif";
		} else {
			$link = $PHP_SELF."?cmd=moreinfos&username=".$db->f("username")."#".$db->f("username");
			$img = "forumgrau.gif";
		}
	}
	if ($i_see_everybody) {
		$anker = "<A name=\"".$db->f("username")."\">";
	} else {
		$anker = '';
	}
	printf ("<td class=\"%s\" nowrap>%s<font size=\"-1\">&nbsp;%s.</td>", $class, $anker, $c);
	printf ("<td colspan=2 class=\"%s\">", $class);
	if ($rechte) {
		printf ("<A href=\"%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/%s\" border=\"0\"", $link, $img);
		echo tooltip(sprintf(_("Weitere Informationen über %s"), $db->f("username")));
		echo ">&nbsp;</A>";
	}

	if ($db->f('visible') == 'yes'
	    || $i_see_everybody
	    || $db->f('user_id') == $user->id) {
		?>
		<font size="-1">
			<a href="about.php?username=<?= $db->f("username") ?>">
				<?
				$user_pic = new UserPic($db->f("user_id"));
				echo $user_pic->getImageTag(UserPic::SMALL);
				?>
				<?= htmlReady($db->f("fullname")) ?>
			</a>
		</font>
		</td>
	<? } else { ?>
		<font size="-1" color="#666666">
			<?= _("(unsichtbareR NutzerIn)") ?>
		</font>
	<? }

	if ($key != "dozent" && $rechte) {
		if ($db->f("mkdate")) {
			echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">".date("d.m.y,",$db->f("mkdate"))."&nbsp;".date("H:i:s",$db->f("mkdate"))."</font></td>";
		} else {
			echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">"._("unbekannt")."</font></td>";
		}
	} else if ($key == "dozent" && $rechte) {
		echo "<td class=\"$class\" align=\"center\">&nbsp;</td>";
	}
	echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">".$db->f("doll")."</font></td>";
	echo "<td class=\"$class\" align=\"center\"><font size=\"-1\">".$Dokumente."</font></td>";

	echo "<td class=\"$class\" align=\"center\">";
	if ($db->f('visible') == 'yes' || $i_see_everybody) {
		if ($GLOBALS['CHAT_ENABLE']){
			echo chat_get_online_icon($db->f("user_id"),$db->f("username"),$SessSemName[1]) . "&nbsp;";
		}

		printf ("<a href=\"sms_send.php?sms_source_page=teilnehmer.php&rec_uname=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" %s border=\"0\"></a>", $db->f("username"), tooltip(_("Nachricht an User verschicken")));
	}

	echo "</td>";

	// Befoerderungen und Degradierungen
	$username=$db->f("username");
	if ($rechte) {

		// Tutor entlassen
		if ($key == "tutor" AND $SemUserStatus!="tutor") {
			echo "<td class=\"$class\">&nbsp</td>";
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=pain&username=$username&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/down.gif\" width=\"21\" height=\"16\"></a>";
			echo "<input type=\"checkbox\" name=\"tutor_to_autor[$username]\" value=\"1\">";
			echo "</td>";
		}

		elseif ($key == "autor") {
			// zum Tutor befördern
			if ($SemUserStatus!="tutor") {
				if ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"])
					$db2->query ("SELECT DISTINCT user_id FROM seminar_inst LEFT JOIN user_inst USING(Institut_id) WHERE user_id = '$UID' AND seminar_id ='$SessSemName[1]' AND inst_perms!='user' AND inst_perms!='autor'");
				else
					$db2->query ("SELECT user_id FROM auth_user_md5  WHERE perms IN ('tutor', 'dozent') AND user_id = '$UID' ");
				if ($db2->next_record()) {
					++$tutor_count;
					echo "<td class=\"$class\" align=\"center\">";
					echo "<a href=\"$PHP_SELF?cmd=pleasure&username=$username&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/up.gif\" width=\"21\" height=\"16\"></a>";
					echo "<input type=\"checkbox\" name=\"autor_to_tutor[$username]\" value=\"1\">";
					echo "</td>";
				} else echo "<td class=\"$class\" >&nbsp;</td>";
			} else echo "<td class=\"$class\">&nbsp;</td>";
			// Schreibrecht entziehen
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=lesen&username=$username&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/down.gif\" width=\"21\" height=\"16\"></a>";
			echo "<input type=\"checkbox\" name=\"autor_to_user[$username]\" value=\"1\">";
			echo "</td>";
		}

		// Schreibrecht erteilen
		elseif ($key == "user") {
			$db2->query ("SELECT perms, user_id FROM auth_user_md5 WHERE user_id = '$UID' AND perms != 'user'");
			if ($db2->next_record()) { // Leute, die sich nicht zurueckgemeldet haben duerfen auch nicht schreiben!
				echo "<td class=\"$class\" align=\"center\">";
				echo "<a href=\"$PHP_SELF?cmd=schreiben&username=$username&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/up.gif\" width=\"21\" height=\"16\"></a>";
				echo "<input type=\"checkbox\" name=\"user_to_autor[$username]\" value=\"1\">";
				echo "</td>";
			} else echo "<td class=\"$class\">&nbsp;</td>";
			// aus dem Seminar werfen
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=raus&username=$username&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/down.gif\" width=\"21\" height=\"16\"></a>";
			echo "<input type=\"checkbox\" name=\"user_to_null[$username]\" value=\"1\">";
			echo "</td>";
		}

		elseif ($key == "accepted") { // temporarily accepted students
			// forward to autor
			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_rein&username=%s&accepted=1&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/up.gif\" width=\"21\" height=\"16\"></a><input type=\"checkbox\" name=\"admission_insert[%s]\" value=\"1\"></td>", $class, $username, $username);
			// kick
			echo "<td class=\"$class\" align=\"center\">";
			echo "<a href=\"$PHP_SELF?cmd=admission_raus&username=$username&accepted=1&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/down.gif\" width=\"21\" height=\"16\"></a><input type=\"checkbox\" name=\"admission_delete[$username]\" value=\"1\"></td>";
		}

		else { // hier sind wir bei den Dozenten
			echo "<td colspan=\"2\" class=\"$class\" >&nbsp;</td>";
		}

		if ($db3->f("admission_type") == 1 || $db3->f("admission_type") == 2) {
			if ($key == "autor" || $key == "user")
				printf ("<td width=\"80%%\" align=\"center\" class=\"%s\"><font size=-1>%s%s</font></td>", $class, ($db->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db->f("name"), (!$db->f("name") && !$db->f("studiengang_id") == "all") ?  "&nbsp; ": "");
			else
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\">&nbsp;</td>", $class);
		}

		// info-field for users
		$show_area = "show_".$key;
		if ((is_opened($db->f("user_id")) || isset($$show_area)) && $rechte) { // show further userinfosi
			$info_is_open = true;

			//get data for user, if dozent or higher
			if ($perm->have_perm("dozent")) {
				/* remark: if you change something in the data-acquisition engine
				 * please do not forget to change it also in "export/export_studipdata_func.inc.php"
				 * in the function export_teilis(...)
				 */

        $additional_data = get_additional_data($db->f('user_id'), $id);

				$user_data = array();

        foreach($additional_data as $key => $val)
        {
          if ($val['content'] && $val['display'])
          {
            if (is_array($val['content']))
            {
              $zw = implode(', ', $val['content']);

              $user_data [] = array('name' => $val['name'], 'content' => $zw);
            } else
            {
              if ($val['name'] == 'user_picture')
              {
                $show_user_picture = true;
              } else
              {
                $user_data [] = $val;
              }
            }
          }
        }
			}

		?>
			<tr class="<?= $class ?>">

				<? if ($showscore) : ?>
					<td colspan="2">&nbsp;</td>
				<? else : ?>
					<td>&nbsp;</td>
				<? endif ?>

				<td valign="top">
					<font size="-1">
						<dl style="margin-left:2em;">
							<? foreach ($user_data as $val) : ?>
								<? if ($val["content"] == "") continue; ?>
								<dt><?= $val["name"] ?> :</dt>
								<dd><?= $val["content"] ?></dd>
								<!--
								<font size="-1">
									<?= $val["name"] ?>: <?= $val["content"] ?>
								</font>
								<br/>
								-->
							<? endforeach ?>
						</dl>
					</font>
				</td>

				<? if ($show_user_picture) : ?>
					<td>
						<img src="<?= get_user_pic_url($db->f('user_id')) ?>" border="0" width="80">
					</td>
				<? endif ?>

				<td colspan="<?= $colspan - 2 - ($show_user_picture ? 1 : 0) - ($showscore ? 1 : 0)?>">
					<form action="#<?= $db->f("username") ?>" method="POST">
						<font size="-1"><?=_("Bemerkungen:")?></font><br/>
						<textarea name="userinfo" rows="3" cols="50"><?= $db->f("comment") ?></textarea>
						<br>
						<font size="-1"><?= _("&Auml;nderungen") ?></font>
						<input type="image" <?= makeButton("uebernehmen", "src") ?>>
						<input type="hidden" name="user_id" value="<?=$db->f("user_id")?>">
						<input type="hidden" name="cmd" value="change_userinfo">
						<input type="hidden" name="username" value="<?= $db->f("username") ?>">
						<input type="hidden" name="studipticket" value="<?= $studipticket ?>">
					</form>
				</td>
			</tr>
		<?
		}
	} // Ende der Dozenten/Tutorenspalten


	print("</tr>\n");
	$c++;
} // eine Zeile zuende

if($key != 'dozent' && $rechte && !$info_is_open) {
	echo '<tr><td class="blank" colspan="'.($showscore ? 8 : 7).'">&nbsp;</td>';
	if (isset($multiaction[$key]['insert'][0]) && !($key == 'autor' && !$tutor_count)) echo '<td class="blank" align="center">' . makeButton('eintragen','input', $multiaction[$key]['insert'][1],'do_' . $multiaction[$key]['insert'][0]) . '</td>';
	else echo '<td class="blank">&nbsp;</td>';
	echo '<td class="blank" align="center">' . makeButton('entfernen','input', $multiaction[$key]['delete'][1],'do_' . $multiaction[$key]['delete'][0]) . '</td>';
	if ($db3->f("admission_type")) echo '<td class="blank">&nbsp;</td>';
	echo "</tr></form>";
}
echo "<tr><td class=\"blank\" colspan=\"$colspan\">&nbsp;</td></tr>";
} // eine Gruppe zuende
}
echo "</table>\n";

echo "</td></tr>\n";  // Auflistung zuende

// Warteliste
$awaiting = false;
if ($rechte) {
	$db->query ("SELECT admission_seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname , username, studiengaenge.name, position, admission_seminar_user.studiengang_id, status FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) LEFT JOIN studiengaenge ON (admission_seminar_user.studiengang_id=studiengaenge.studiengang_id)  WHERE admission_seminar_user.seminar_id = '$SessionSeminar' AND admission_seminar_user.status != 'accepted' ORDER BY position, name");
	if ($db->num_rows()) { //Only if Users were found...
		$awaiting = true;
		?>
		<tr>
		<td class="blank" width="100%" colspan="2">
		<a href="sms_send.php?sms_source_page=teilnehmer.php&course_id=<?=$SessSemName[1]?>&emailrequest=1&subject=<?=rawurlencode($SessSemName[0])?>&filter=waiting">
		<img src="<?=$GLOBALS['ASSETS_URL']?>images/mailnachricht.gif" border="0" vspace="3" hspace="3" align="absmiddle">
		<span style="font-size:80%">
		<?=_("Systemnachricht mit Emailweiterleitung an alle Wartenden verschicken")?>
		</span></a>
		</td>
		</tr>
		<?
		// die eigentliche Teil-Tabelle
		echo '<form name="waitlist" action="'.$PHP_SELF.'?studipticket='.$studipticket.'" method="post">';
		echo "<tr><td class=\"blank\" colspan=\"2\">";
		echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
		echo "<tr height=\"28\">";
		printf ("<td class=\"steel\" width=\"%s%%\" align=\"left\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"20\"><font size=\"-1\"><b>%s</b></font></td>", ($db3->f("admission_type") == 1 && $db3->f("admission_selection_take_place") !=1) ? "40" : "30",  ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1) ? _("Warteliste") : _("Anmeldeliste"));
		if ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1)
			printf("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td>", _("Position"));
		printf("<td class=\"steel\" width=\"10%%\" align=\"center\">&nbsp; </td>");
		printf("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td>", _("Nachricht"));
		printf("<td class=\"steel\" width=\"15%%\" align=\"center\"><font size=\"-1\"><a name=\"blubb\" onClick=\"return invert_selection('admission_insert','waitlist');\" %s><b>%s</b></a></font></td>", tooltip(_("Klicken, um Auswahl umzukehren"),false), _("eintragen"));
		printf("<td class=\"steel\" width=\"15%%\" align=\"center\"><font size=\"-1\"><a name=\"bla\" onClick=\"return invert_selection('admission_delete','waitlist');\" %s><b>%s</b></a></font></td>", tooltip(_("Klicken, um Auswahl umzukehren"),false), _("entfernen"));
		printf("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=\"-1\"><b>%s</b></font></td></tr>\n", _("Kontingent"));


		while ($db->next_record()) {
			if ($db->f("status") == "claiming") { // wir sind in einer Anmeldeliste und brauchen Prozentangaben
				$db2=new DB_Seminar;
				$admission_studiengang_id = $db->f("studiengang_id");
				$admission_seminar_id = $db->f("seminar_id");
				$plaetze = round ($db->f("admission_turnout") * ($db->f("quota") / 100));  // Anzahl der Plaetze in dem Studiengang in den ich will
				$db2->query("SELECT count(*) AS wartende FROM admission_seminar_user WHERE seminar_id = '$admission_seminar_id' AND studiengang_id = '$admission_studiengang_id'");
				if ($db2->next_record())
					$wartende = ($db2->f("wartende"));   // Anzahl der Personen die auch in diesem Studiengang auf einen Platz lauern
						 if ($plaetze >= $wartende)
							$admission_chance = 100;   // ich komm auf jeden Fall rein
				else
					$admission_chance = round (($plaetze / $wartende) * 100); // mehr Bewerber als Plaetze
			}

			$cssSw->switchClass();
			printf ("<tr><td width=\"%s%%\" class=\"%s\" align=\"left\"><font size=\"-1\"><a name=\"%s\" href=\"about.php?username=%s\">%s</a></font></td>",  ($db3->f("admission_type") == 1 && $db3->f("admission_selection_take_place") !=1) ? "40" : "30", $cssSw->getClass(), $db->f("username"), $db->f("username"), htmlReady($db->f("fullname")));
			if ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1)
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=\"-1\">%s</font></td>", $cssSw->getClass(), $db->f("position"));
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\">&nbsp; </td>", $cssSw->getClass());

			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><a href=\"sms_send.php?sms_source_page=teilnehmer.php&rec_uname=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" %s border=\"0\"></a></td>",$cssSw->getClass(), $db->f("username"), tooltip(_("Nachricht an User verschicken")));

			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><input type=\"image\" name=\"admission_rein\" value=\"".$db->f("username")."\" border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/up.gif\" width=\"21\" height=\"16\">
					<input type=\"checkbox\" name=\"admission_insert[%s]\" value=\"1\"></td>", $cssSw->getClass(), $db->f("username"), $db->f("username"));
			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_raus&username=%s&studipticket=$studipticket\"><img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/down.gif\" width=\"21\" height=\"16\"></a>
					<input type=\"checkbox\" name=\"admission_delete[%s]\" value=\"1\"></td>", $cssSw->getClass(), $db->f("username"), $db->f("username"));
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=\"-1\">%s</font></td></tr>\n", $cssSw->getClass(), ($db->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db->f("name"));
		}
		echo '<tr><td class="blank" colspan="4" align="right"><font size="-1">';
		echo '<img src="'.$GLOBALS['ASSETS_URL'].'images/info.gif" align="absmiddle" hspace="3" border="0" '.tooltip(_("Mit dieser Einstellung beeinflussen Sie, ob Teilnehmer die Sie hinzufügen auf die Kontingentplätze angerechnet werden."),1,1).' >';
		echo '<label for="kontingent">'._("Kontingent berücksichtigen:");
		echo '<input id="kontingent" type="checkbox" checked name="consider_contingent" value="1" style="vertical-align:middle"></label>';
		echo '&nbsp;</font></td>';
		echo '<td class="blank" align="center">' . makeButton('eintragen','input',_("Ausgewählte Nutzer aus der Warteliste in die Veranstaltung eintragen"),'do_admission_insert') . '</td>';
		echo '<td class="blank" align="center">' . makeButton('entfernen','input',_("Ausgewählte Nutzer aus der Warteliste entfernen"),'do_admission_delete') . '</td>';
		echo '<td class="blank">&nbsp;</td></tr>';
		echo '</table>';
		echo '</td></tr></form>';
	}
}

// Der Dozent braucht mehr Unterstuetzung, also Tutor aus der(n) Einrichtung(en) berufen...
//Note the option "only_inst_user" from the config.inc. If it is NOT setted, this Option is disabled (the functionality will do in this case do seachform below)
if ($rechte
		&& $SemUserStatus!="tutor"
		&& $SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"]
		&& $_REQUEST['cmd'] != 'csv') {
	$query = "SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, inst_perms, perms FROM seminar_inst d LEFT JOIN user_inst a USING(Institut_id) ".
	"LEFT JOIN auth_user_md5  b USING(user_id) LEFT JOIN user_info USING(user_id) ".
	"LEFT JOIN seminar_user c ON (c.user_id=a.user_id AND c.seminar_id='$SessSemName[1]')  ".
	"WHERE d.seminar_id = '$SessSemName[1]' AND a.inst_perms IN ('tutor','dozent') AND ISNULL(c.seminar_id) GROUP BY a.user_id ORDER BY Nachname";

	$db->query($query); // ergibt alle berufbaren Personen
	?>

	<tr>
		<td class=blank colspan=2>&nbsp;
		</td>
	</tr>
	<tr><td class=blank colspan=2>

	<table width="99%" border="0" cellpadding="2" cellspacing="0" border="0" align="center">
	<form action="<? echo $PHP_SELF ?>" method="POST">
	<INPUT type="hidden" name="studipticket" value="<?=$studipticket?>">
	<tr>
		<td class="steel1" width="40%" align="left">&nbsp; <font size="-1"><b><?=_("MitarbeiterInnen der Einrichtung(en)")?></b></font></td>
		<td class="steel1" width="40%" align="left"><select name="u_id" size="1">
		<?
		printf("<option value=\"0\">- -  %s - -\n", _("bitte ausw&auml;hlen"));
		while ($db->next_record())
			printf("<option value=\"%s\">%s - %s\n", $db->f("user_id"), htmlReady(my_substr($db->f("fullname")." (".$db->f("username"),0,35)).")", $db->f("inst_perms"));
		?>
		</select></td>
		<td class="steel1" width="20%" align="center"><font size=-1><? if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) print _("als TutorIn"); else print _("als Mitglied") ?></font><br />
		<input type="IMAGE" name="add_tutor" <?=makeButton("eintragen", "src")?> border="0" value=" <?=_("Als TutorIn berufen")?> "></td>
	</tr></form></table>
<?

} // Ende der Berufung

//insert autors via free search form
if ($rechte) {
	if ($_REQUEST['cmd'] != 'csv') {
	if ($search_exp) {
		$search_exp = trim($search_exp);
		$query = "SELECT a.user_id, username, " . $_fullname_sql['full_rev'] ." AS fullname, perms FROM auth_user_md5 a ".
			"LEFT JOIN user_info USING(user_id) LEFT JOIN seminar_user b ON (b.user_id=a.user_id AND b.seminar_id='$SessSemName[1]')  ".
			"WHERE perms IN ('autor','tutor','dozent') AND ISNULL(b.seminar_id) AND ".
			"(username LIKE '%$search_exp%' OR Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%') ".
			"ORDER BY Nachname";
		$db->query($query); // results all users which are not in the seminar
		?>

	<tr>
		<td class="blank" colspan="2">&nbsp;
		</td>
	</tr>
	<tr><td class="blank" colspan="2">
	<a name="freesearch"></a>
	<table width="99%" border="0" cellpadding="2" cellspacing="0" border=0 align="center">
	<form action="<? echo $PHP_SELF ?>?cmd=add_user" method="POST">
	<INPUT type="hidden" name="studipticket" value="<?=$studipticket?>">
	<tr>
		<td class="steel1" width="40%" align="left">&nbsp; <font size="-1"><b><?=_("Gefundene Nutzer")?></b></font></td>
		<td class="steel1" width="40%" align="left"><select name="username" size="1">
		<?
		printf("<option value=\"0\">- -  %s - -\n", _("bitte ausw&auml;hlen"));
		while ($db->next_record())
			printf("<option value=\"%s\">%s - %s\n", $db->f("username"), htmlReady(my_substr($db->f("fullname")." (".$db->f("username"),0,35)).")", $db->f("perms"));
		?>
		</select>
		<?if($db3->f("admission_type") == 1 || $db3->f("admission_type") == 2){
			echo '<br><br><img src="'.$GLOBALS['ASSETS_URL'].'images/info.gif" align="absmiddle" hspace="3" border="0" '.tooltip(_("Mit dieser Einstellung beeinflussen Sie, ob Teilnehmer die Sie hinzufügen auf die Kontingentplätze angerechnet werden."),1,1).' >';
			echo '<font size="-1"><label for="kontingent2">'._("Kontingent berücksichtigen:");
			echo '&nbsp;<select name="consider_contingent" id="kontingent2">';
			echo '<option value="">'._("Kein Kontingent").'</option>';
			$admission_info = get_admission_quota_info($SessSemName[1]);
			foreach($admission_info as $studiengang => $data){
				echo '<option value="'.$studiengang.'" '.($_REQUEST['consider_contingent'] == $studiengang ? 'selected' : '').'>'.htmlReady($data['name'] . ' ' . '('.$data['num_available'].')').'</option>';
			}
			echo '</select></label></font>';
		}
		?>
		</td>
		<td class="steel1" width="20%" align="center"><font size=-1>
		<?
		if (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["only_inst_user"] && $perm->have_studip_perm("dozent",$SessSemName[1])){
			 echo (!$SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"] ? _("als TutorIn") . " / " . _("als AutorIn") : _("als Mitglied"));
		} else {
			echo _("als AutorIn");
		}
		?></font><br />
		<input type="image" name="add_user" <?=makeButton("eintragen", "src")?> align="absmiddle" border=0 value=" <?=_("Als AutorIn berufen")?> ">&nbsp;<a href="<? echo $PHP_SELF ?>"><?=makeButton("neuesuche")?></a></td>

	</tr></form></table>
		<?
	} else { //create a searchform
		?>
	<tr>
		<td class=blank colspan=2>&nbsp;
		</td>
	</tr>
	<tr><td class=blank colspan=2>
	<table width="99%" border="0" cellpadding="2" cellspacing="0" border=0 align="center">
	<form action="<?=$PHP_SELF?>#freesearch" method="POST">
	<tr>
		<td class="steel1" width="40%" align="left">&nbsp; <font size=-1><b><?=_("Nutzer in die Veranstaltung eintragen")?></b></font>
		<br /><font size=-1>&nbsp; <? printf(_("Bitte geben Sie den Vornamen, Nachnamen %s oder Usernamen zur Suche ein"), "<br />&nbsp;")?> </font></td>
		<td class="steel1" width="40%" align="left">
		<input type="TEXT" name="search_exp" size="40" maxlength="255" />
		<td class="steel1" width="20%" align="center">
		<input type="IMAGE" name="start_search" <?=makeButton("suchestarten", "src")?> border=0 value=" <?=_("Suche starten")?> "></td>
	</tr></form></table></tr>
	<?
}
}
	// import new members (as "autor") from a CSV-list
	echo "<tr>\n<td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=\"2\">\n";
	echo "<form action=\"$PHP_SELF\" method=\"post\">\n";
	echo "<input type=\"hidden\" name=\"studipticket\" value=\"$studipticket\">\n";
	echo "<input type=\"hidden\" name=\"cmd\" value=\"csv\">\n";
	echo "<table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\" ";
	echo "align=\"center\">\n";
	if (!sizeof($csv_mult_founds)) {
		echo "<tr><td width=\"40%\" class=\"steel1\">\n<div style=\"font-size: small; margin-left:6px; width:250px;\">";
		echo '<b>' . _("Teilnehmerliste übernehmen") . '</b><br>';
		echo _("In das nebenstehende Textfeld können Sie eine Liste mit Namen von NutzerInnen eingeben, die in die Veranstaltung aufgenommen werden sollen.");
		echo '<br />' . _("Geben Sie in jede Zeile den Nachnamen und (optional) den Vornamen getrennt durch ein Komma oder ein Tabulatorzeichen ein.");
		echo '<br>' . _("Eingabeformat: <b>Nachname, Vorname &crarr;<b>");
		echo "</div></td>\n";
		echo "<td width=\"40%\" class=\"steel1\">";
		echo "<textarea name=\"csv_import\" rows=\"6\" cols=\"50\">";
		foreach($csv_not_found as $line) echo htmlReady($line) . chr(10);
		echo "</textarea>";
		if($db3->f("admission_type") == 1 || $db3->f("admission_type") == 2){
			echo '<br><br><img src="'.$GLOBALS['ASSETS_URL'].'images/info.gif" align="absmiddle" hspace="3" border="0" '.tooltip(_("Mit dieser Einstellung beeinflussen Sie, ob Teilnehmer die Sie hinzufügen auf die Kontingentplätze angerechnet werden."),1,1).' >';
			echo '<font size="-1"><label for="kontingent2">'._("Kontingent berücksichtigen:");
			echo '&nbsp;<select name="consider_contingent" id="kontingent2">';
			echo '<option value="">'._("Kein Kontingent").'</option>';
			$admission_info = get_admission_quota_info($SessSemName[1]);
			foreach($admission_info as $studiengang => $data){
				echo '<option value="'.$studiengang.'" '.($_REQUEST['consider_contingent'] == $studiengang ? 'selected' : '').'>'.htmlReady($data['name'] . ' ' . '('.$data['num_available'].')').'</option>';
			}
			echo '</select></label></font>';
		}
		echo "</td>\n";
		echo "<td width=\"20%\" class=\"steel1\" align=\"center\"><input type=\"image\" name=\"submit\" ";
		echo makeButton('eintragen', 'src') . " border=\"0\">";
		if (sizeof($csv_not_found)) {
			echo "<br><br><a href=\"$PHP_SELF\">";
			echo "<img border=\"0\" " . makeButton('loeschen', 'src');
			echo "></a>";
		}
		echo "\n</td></tr>\n";
	} else {
	//	if (sizeof($csv_mult_founds)) {
			echo "<tr><td class=\"steel1\" colspan=\"2\">";
			echo "<div style=\"font-size: small; margin-left:8px; width:350px;\">";
			echo '<b>' . _("Manuelle Zuordnung") . '</b><br>';
			echo _("Folgende NutzerInnen konnten <b>nicht eindeutig</b> zugewiesen werden. Bitte wählen Sie aus der jeweiligen Trefferliste:");
			echo "</div></td></tr>\n";
			$cssSw->resetClass();
			foreach ($csv_mult_founds as $csv_key => $csv_mult_found) {
				printf("<tr%s><td%s width=\"40%%\"><div style=\"font-size:small; margin-left:8px;\">%s</div></td>",
						$cssSw->getHover(), $cssSw->getFullClass(),
						htmlReady(mila($csv_key, 50)));
				printf("<td%s width=\"60%%\">", $cssSw->getFullClass());
				echo "<select name=\"selected_users[]\">\n";
				echo '<option value=""> - - ' . _("bitte ausw&auml;hlen") . " - - </option>\n";
				foreach ($csv_mult_found as $csv_found) {
					echo "<option value=\"{$csv_found['username']}\">";
					echo htmlReady(my_substr($csv_found['fullname'], 0, 50)) . " ({$csv_found['username']}) - {$csv_found['perms']}</option>\n";
				}
				echo "</select>\n</td></tr>\n";
				$cssSw->switchClass();
			}
			$cssSw->resetClass();
			$cssSw->switchClass();
			echo "<tr><td class=\"steel1\" colspan=\"2\" align=\"right\" nowrap=\"nowrap\">";
			if($db3->f("admission_type") == 1 || $db3->f("admission_type") == 2){
				echo '<img src="'.$GLOBALS['ASSETS_URL'].'images/info.gif" align="absmiddle" hspace="3" border="0" '.tooltip(_("Mit dieser Einstellung beeinflussen Sie, ob Teilnehmer die Sie hinzufügen auf die Kontingentplätze angerechnet werden."),1,1).' >';
				echo '<font size="-1"><label for="kontingent2">'._("Kontingent berücksichtigen:");
				echo '&nbsp;<select name="consider_contingent" id="kontingent2">';
				echo '<option value="">'._("Kein Kontingent").'</option>';
				$admission_info = get_admission_quota_info($SessSemName[1]);
				foreach($admission_info as $studiengang => $data){
					echo '<option value="'.$studiengang.'" '.($_REQUEST['consider_contingent'] == $studiengang ? 'selected' : '').'>'.htmlReady($data['name'] . ' ' . '('.$data['num_available'].')').'</option>';
				}
				echo '</select></label></font>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ';
			}
			echo makeButton('eintragen', 'input');
			echo '&nbsp; &nbsp; ';
			echo "<a href=\"$PHP_SELF\">";
			echo makeButton('abbrechen', 'img') . '</a>';
			echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; </td></tr>\n";

		if (sizeof($csv_not_found)) {
			echo "<tr><td width=\"40%\" class=\"steel1\">\n<div style=\"font-size: small; margin-left:8px; width:250px;\">";
			echo '<b>' . _("Nicht gefundene NutzerInnen") . '</b><br>';
			echo _("Im nebenstehende Textfeld sehen Sie eine Auflistung der Suchanfragen, zu denen <b>keine</b> NutzerInnen gefunden wurden.");
			echo "</div></td>\n";
			echo "<td width=\"40%\" class=\"steel1\">";
			echo "<textarea name=\"csv_import\" rows=\"6\" cols=\"40\">";
			foreach($csv_not_found as $line) echo htmlReady($line) . chr(10);
			echo "</textarea></td>\n";
			echo "<td width=\"20%\" class=\"steel1\" align=\"center\">&nbsp;";
			echo "\n</td></tr>\n";
		}
	}

	echo "</table>\n</form>";

	if (($EXPORT_ENABLE) AND ($perm->have_studip_perm("tutor", $SessSemName[1]))) {
		include_once($PATH_EXPORT . "/export_linking_func.inc.php");
		echo chr(10) . '<table width="90%" border="0">';
		echo chr(10) . '<tr>';
		echo chr(10) . "<td><b><font size=\"-1\">" . export_link($SessSemName[1], "person", _("TeilnehmerInnen") . ' '. $SessSemName[0], "rtf", "rtf-teiln", "", _("TeilnehmerInnen exportieren als rtf Dokument") . '<img align="bottom" src="'.$GLOBALS['ASSETS_URL'].'images/rtf-icon.gif" border="0">', 'passthrough'). "</font></b></td>";
		echo chr(10) . "<td><b><font size=\"-1\">" . export_link($SessSemName[1], "person", _("TeilnehmerInnen") . ' '. $SessSemName[0], "csv", "csv-teiln", "", _("TeilnehmerInnen exportieren als csv Dokument") . '<img align="bottom" src="'.$GLOBALS['ASSETS_URL'].'images/xls-icon.gif" border="0">', 'passthrough') . "</font></b></td>";
		echo chr(10) . '</tr>';

		if ($awaiting){
			echo chr(10) . '<tr>';
			echo chr(10) . "<td><b><font size=\"-1\">" . export_link($SessSemName[1], "person", _("Warteliste") .' ' . $SessSemName[0], "rtf", "rtf-warteliste","awaiting",_("Warteliste exportieren als rtf Dokument") . '<img align="bottom" src="'.$GLOBALS['ASSETS_URL'].'images/rtf-icon.gif" border="0">', 'passthrough') . "</font></b></td>";
			echo chr(10) . "<td><b><font size=\"-1\">" . export_link($SessSemName[1], "person", _("Warteliste") .' ' . $SessSemName[0], "csv", "csv-warteliste","awaiting",_("Warteliste exportieren csv Dokument") . '<img align="bottom" src="'.$GLOBALS['ASSETS_URL'].'images/xls-icon.gif" border="0">', 'passthrough') . "</font></b></td>";
			echo chr(10) . '</tr>';
		}
		echo chr(10) . '</table>';
	}


	?>
	<tr>
		<td class="blank" colspan="2">&nbsp;
		</td>
	</tr>
	<?
} // end insert autor


echo '</td></tr></table>';
include ('lib/include/html_end.inc.php');
// Save data back to database.
page_close();
?>
