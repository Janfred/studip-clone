<?
/**
* UserManagement.class.php
* 
* Management for the Stud.IP global users
* 
*
* @author		Stefan Suchi <suchi@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		UserManagement.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// UserManagement.class.php
// Management for the Stud.IP global users
// Copyright (C) 2003 Stefan Suchi <suchi@data-quest>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once $ABSOLUTE_PATH_STUDIP.("functions.php");
require_once $ABSOLUTE_PATH_STUDIP.("language.inc.php");
require_once $ABSOLUTE_PATH_STUDIP.("lib/classes/auth_plugins/StudipAuthAbstract.class.php");
if ($ILIAS_CONNECT_ENABLE) {
	include_once ("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_LEARNINGMODULES/lernmodul_db_functions.inc.php");
	include_once ("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_LEARNINGMODULES/lernmodul_user_functions.inc.php");
}


class UserManagement {
	var $auth_user = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle auth_user_md5 und user_info
	var $msg = ""; //enthält evtl Fehlermeldungen
	var $db;     //unsere Datenbankverbindung
	var $db2;     //unsere Datenbankverbindung
	var $validator;	// Klasse zum Ueberpruefen der Eingaben
	var $smtp;		// Klasse fuer das Verschicken der Mails
	var $hash_secret = "jdfiuwenxclka";  // Set this to something, just something different...
	
	/**
	* Constructor
	*
	* Pass nothing to create a new user, or the user_id from an existing user to change or delete
	* @access	public
	* @param	string	$user_id	the user which should be retrieved
	*/
	function UserManagement($user_id = FALSE) {

		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		$this->validator = new email_validation_class;	// Klasse zum Ueberpruefen der Eingaben
		$this->validator->timeout = 10;									// Wie lange warten wir auf eine Antwort des Mailservers?
		$this->smtp = new studip_smtp_class;						// Einstellungen fuer das Verschicken der Mails
		if ($user_id) {
			$this->getFromDatabase($user_id);
		}
	}
	

	/**
	* load user data from database into internal array
	*
	* @access	private
	* @param	string	$user_id	the user which should be retrieved
	*/
	function getFromDatabase($user_id) {

		$this->db->query("SELECT * FROM auth_user_md5 WHERE user_id = '$user_id'");  //ein paar userdaten brauchen wir schon mal
		if ($this->db->next_record()) {
			$fields = $this->db->metadata();
			for ($i=0; $i<count($fields); $i++) {
				$field_name = $fields[$i]["name"];
				$this->auth_user["auth_user_md5.".$field_name] = $this->db->f("$field_name");
			}
		}

		$this->db->query("SELECT * FROM user_info WHERE user_id = '".$this->auth_user["auth_user_md5.user_id"]."'");
		if ($this->db->next_record()) {
			$fields = $this->db->metadata();
			for ($i=0; $i<count($fields); $i++) {
				$field_name = $fields[$i]["name"];
				$this->auth_user["user_info.".$field_name] = $this->db->f("$field_name");
			}
		}
	}
	

	/**
	* store user data from internal array into database
	*
	* @access	private
	*/
	function storeToDatabase() {
	
		if (!$this->auth_user['auth_user_md5.user_id']) {
			$this->auth_user['auth_user_md5.user_id'] = md5(uniqid($this->hash_secret));
			$this->db->query("INSERT INTO auth_user_md5 SET user_id = '".$this->auth_user['auth_user_md5.user_id']."', username = '".$this->auth_user['auth_user_md5.username']."', password = 'dummy'");
			if ($this->db->affected_rows() == 0) {
				return FALSE;
			}
			$this->db->query("INSERT INTO user_info SET user_id = '".$this->auth_user['auth_user_md5.user_id']."', mkdate='".time()."'");
			if ($this->db->affected_rows() == 0) {
				return FALSE;
			}
		}
		
		foreach($this->auth_user as $key => $value) {
			$split = explode(".",$key);
			$table = $split[0];
			$field = $split[1];
			$value = mysql_escape_string($value);
			$this->db->query("UPDATE $table SET $field = '$value' WHERE user_id = '".$this->auth_user['auth_user_md5.user_id']."'");
		}
		$this->db->query("UPDATE user_info SET chdate='".time()."' WHERE user_id = '".$this->auth_user['auth_user_md5.user_id']."'");
		if ($this->db->affected_rows() == 0) {
			return FALSE;
		}
		return TRUE;
	}
	

	/**
	* generate a secure password of $length characters [a-z0-9]
	*
	* @access	private
	* @param	integer	$length	number of characters
	*/
	function generate_password($length) {
		mt_srand((double)microtime()*1000000);
		for ($i=1;$i<=$length;$i++) {
			$temp = mt_rand() % 36;
			if ($temp < 10)
				$temp += 48;	 // 0 = chr(48), 9 = chr(57)
			else
				$temp += 87;	 // a = chr(97), z = chr(122)
			$pass .= chr($temp);
		}
		return $pass;
	}


	function checkMail($Email) {
		// Adresse korrekt?
		if (!$this->validator->ValidateEmailAddress($Email)) {
			$this->msg .= "error§" . _("E-Mail-Adresse syntaktisch falsch!") . "§";
			return FALSE;
		}
		// E-Mail erreichbar?
		if (!$this->validator->ValidateEmailHost($Email)) {		 // Mailserver nicht erreichbar, ablehnen
			$this->msg .= "error§" . _("Mailserver ist nicht erreichbar!") . "§";
			return FALSE;
		} 
		if (!$this->validator->ValidateEmailBox($Email)) {		// aber user unbekannt, ablehnen
			$this->msg .= "error§" . sprintf(_("E-Mail an <b>%s</b> ist nicht zustellbar!"), $Email) . "§";
			return FALSE;
		}
		return TRUE;
	}
	
	
	//do auto inserts, if we created an autor, tutor or dozent
	function autoInsertSem($old_status = FALSE) {
		global $AUTO_INSERT_SEM, $auth;
		
		if (($old_status != "autor") && ($old_status != "tutor") && ($old_status != "dozent")) {
			if (($this->auth_user['auth_user_md5.perms'] == "autor") || ($this->auth_user['auth_user_md5.perms'] == "tutor") || ($this->auth_user['auth_user_md5.perms'] == "dozent")) {
				if (is_array($AUTO_INSERT_SEM)){
					foreach ($AUTO_INSERT_SEM as $sem) {
						$this->db->query("SELECT Name, start_time, Schreibzugriff FROM seminare WHERE Seminar_id = '$sem'");
						if ($this->db->num_rows()) {
							$this->db->next_record();							
							if ($this->db->f("Schreibzugriff") < 2) { // es gibt das Seminar und es ist kein Passwort gesetzt
								$this->db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$sem' AND user_id='".$this->auth_user['auth_user_md5.user_id']."'");
								if ($this->db2->num_rows()) { // Benutzer ist schon eingetragen
									$this->db2->next_record();
									if ($this->db2->f("status") == "user") { // wir können ihn hochstufen
										$this->db2->query("UPDATE seminar_user SET status = 'autor' WHERE Seminar_id = '$sem' AND user_id='".$this->auth_user['auth_user_md5.user_id']."'");	
										if ($this->auth_user['auth_user_md5.user_id'] == $auth->auth["uid"]) {
											$this->msg .= sprintf("msg§" . _("Ihnen wurden Schreibrechte in der Veranstaltung <b>%s</b> erteilt.") . "§", $this->db->f("Name"));
										} else {
											$this->msg .= sprintf("msg§" . _("Der Person wurden Schreibrechte in der Veranstaltung <b>%s</b> erteilt.") . "§", $this->db->f("Name"));
										}
									}
								} else {  // Benutzer ist noch nicht eingetragen
									$group = select_group ($this->db->f("start_time"));
									$this->db2->query("INSERT into seminar_user (Seminar_id, user_id, status, gruppe) values ('$sem', '".$this->auth_user['auth_user_md5.user_id']."', 'autor', '$group')");
									if ($this->auth_user['auth_user_md5.user_id'] == $auth->auth["uid"]) {
										$this->msg .= sprintf("msg§" . _("Sie wurden automatisch in die Veranstaltung <b>%s</b> eingetragen.") . "§", $this->db->f("Name"));
									} else {
										$this->msg .= sprintf("msg§" . _("Die Person wurde automatisch in die Veranstaltung <b>%s</b> eingetragen.") . "§", $this->db->f("Name"));
									}
								}
							}
						}
					}
				}
			}
		}
	}
	

	function createNewUser($newuser) {
		global $perm, $ABSOLUTE_PATH_STUDIP;
		
		// Do we have permission to do so?
		if (!$perm->is_fak_admin() && $newuser['auth_user_md5.perms'] == "admin") {
			$this->msg .= "error§" . _("Sie haben keine Berechtigung <b>Admin-Accounts</b> anzulegen.") . "§";
			return FALSE;
		}
		if (!$perm->have_perm("root") && $newuser['auth_user_md5.perms'] == "root") {
			$this->msg .= "error§" . _("Sie haben keine Berechtigung <b>Root-Accounts</b> anzulegen.") . "§";
			return FALSE;
		}
			
		// Do we have all necessary data?
		if (empty($newuser['auth_user_md5.username']) || empty($newuser['auth_user_md5.perms']) || empty ($newuser['auth_user_md5.Email'])) {
			$this->msg .= "error§" . _("Bitte geben Sie <b>Username</b>, <b>Status</b> und <b>E-Mail</b> an!") . "§";
			return FALSE;
		}

		// Is the username correct?
		if (!$this->validator->ValidateUsername($newuser['auth_user_md5.username'])) {
			$this->msg .= "error§" .  _("Der gewählte Username ist zu kurz oder enthält unzulässige Zeichen!") . "§";
			return FALSE;
		}														

		// Can we reach the email?
		if (!$this->checkMail($newuser['auth_user_md5.Email'])) {
			return FALSE;
		}
		
		// Store new values in internal array
		foreach ($newuser as $key => $value) {
			$this->auth_user[$key] = $value;
		}
			
		$password = $this->generate_password(6);
		$this->auth_user['auth_user_md5.password'] = md5($password);

		// Does the user already exist?
		// NOTE: This should be a transaction, but it is not...
		$this->db->query("select * from auth_user_md5 where username='{$newuser['auth_user_md5.username']}'");
		if ($this->db->nf()>0) {
			$this->msg .= "error§" . sprintf(_("BenutzerIn <b>%s</b> ist schon vorhanden!"), $newuser['auth_user_md5.username']) . "§";
			return FALSE;
		}
		
		if (!$this->storeToDatabase()) {
			$this->msg .= "error§" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "§";
			return FALSE;
		}
		
		$this->autoInsertSem();
		$this->msg .= "msg§" . sprintf(_("BenutzerIn \"%s\" angelegt."), $newuser['auth_user_md5.username']) . "§";

		// include language-specific subject and mailbody
		$user_language = getUserLanguagePath($this->auth_user['auth_user_md5.user_id']); // user has been just created, so we will get $DEFAULT_LANGUAGE
		include_once("$ABSOLUTE_PATH_STUDIP"."locale/$user_language/LC_MAILS/create_mail.inc.php");

		// send mail
		$this->smtp->SendMessage(
				$this->smtp->env_from,
				array($this->auth_user['auth_user_md5.Email']),
				array("From: " . $this->smtp->from,
						"Reply-To:" . $this->smtp->abuse,
						"To: " . $this->auth_user['auth_user_md5.Email'],
						"Subject: " . $subject),
				$mailbody);
		
		return TRUE;
	}


	function changeUser($newuser) {
		global $perm, $auth;
	
		// Do we have permission to do so?
		if (!$perm->is_fak_admin() && $newuser['auth_user_md5.perms'] == "admin") {
			$this->msg .= "error§" . _("Sie haben keine Berechtigung, <b>Admin-Accounts</b> anzulegen.") . "§";
			return FALSE;
		}
		if (!$perm->have_perm("root") && $newuser['auth_user_md5.perms'] == "root") {
			$this->msg .= "error§" . _("Sie haben keine Berechtigung, <b>Root-Accounts</b> anzulegen.") . "§";
			return FALSE;
		}
		if (!$perm->have_perm("root")) {
			if (!$perm->is_fak_admin() && $this->auth_user['auth_user_md5.perms'] == "admin") {
				$this->msg .= "error§" . _("Sie haben keine Berechtigung <b>Admin-Accounts</b> zu ver&auml;ndern.") . "§";
				return FALSE;
			}
			if ($this->auth_user['auth_user_md5.perms'] == "root") {
				$this->msg .= "error§" . _("Sie haben keine Berechtigung <b>Root-Accounts</b> zu ver&auml;ndern.") . "§";
				return FALSE;
			}
			if ($perm->is_fak_admin() && $this->auth_user['auth_user_md5.perms'] == "admin") {
				$this->db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a 
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id) 
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '" . $auth->auth["uid"] . "' AND c.inst_perms='admin') 
							WHERE a.user_id ='" . $this->auth_user['auth_user_md5.user_id'] . "' AND a.inst_perms = 'admin'");
				$this->db->next_record();
				if (!$this->db->f("admin_ok")) {
					$this->msg .= "error§" . _("Sie haben keine Berechtigung diesen Admin-Account zu ver&auml;ndern.") . "§";
					return FALSE;
				}
			}
		}

		// aktiver Dozent?
		$this->db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '" . $this->auth_user['auth_user_md5.user_id'] . "' AND status = 'dozent' GROUP BY user_id");
		$this->db->next_record();
		if ($this->db->f("count") && $newuser['auth_user_md5.perms'] != "dozent") {
			$this->msg .= sprintf("error§" . "Der Benutzer <b>%s</b> ist Dozent in %s aktiven Veranstaltungen und kann daher nicht in einen anderen Status versetzt werden." . "§", $this->auth_user['auth_user_md5.username'], $this->db->f("count"));
			return FALSE;
		}
			
		// Is the username correct?
		if (isset($newuser['auth_user_md5.username'])) {
			if (!$this->validator->ValidateUsername($newuser['auth_user_md5.username'])) {
				$this->msg .= "error§" .  _("Der gewählte Username ist zu kurz oder enthält unzulässige Zeichen!") . "§";
				return FALSE;
			}
		}														

		// Can we reach the email?
		if (isset($newuser['auth_user_md5.Email'])) {
			if (!$this->checkMail($newuser['auth_user_md5.Email'])) {
				return FALSE;
			}
		}
		
		// Store changed values in internal array if allowed
		$old_perms = $this->auth_user['auth_user_md5.perms'];
		foreach ($newuser as $key => $value) {
			if (!StudipAuthAbstract::CheckField($key, $this->auth_user['auth_user_md5.auth_plugin'])) {
				$this->auth_user[$key] = $value;
			} else {
				$this->msg .= "error§" .  sprintf(_("Das Feld <b>%s</b> können Sie nicht ändern!"), $key) . "§";
				return FALSE;
			}
		}
			
		if (!$this->storeToDatabase()) {
			$this->msg .= "error§" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "§";
			return FALSE;
		}
		
		if ($ILIAS_CONNECT_ENABLE) {
			$this_ilias_id = get_connected_user_id($this->auth_user['auth_user_md5.user_id']);
			if ($this_ilias_id) 
				edit_ilias_user($this_ilias_id, $this->auth_user['auth_user_md5.username'], $this->auth_user['user_info.geschlecht'], $this->auth_user['auth_user_md5.Vorname'], $this->auth_user['auth_user_md5.Nachname'], $this->auth_user['user_info.title_front'], "Stud.IP", $this->auth_user['auth_user_md5.Email'], $this->auth_user['auth_user_md5.perms'], $this->auth_user['user_info.preferred_language']);
		}
		
		$this->autoInsertSem($old_perms);
		$this->msg .= "msg§" . sprintf(_("User \"%s\" ver&auml;ndert."), $this->auth_user['auth_user_md5.username']) . "§";
		



		return TRUE;
	}

}
