<?php
# Lifter002: TODO
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// edit_about.inc.php
// administration of personal home page, helper functions
//
// Copyright (C) 2008 Till Glöggler <tgloeggl@uos.de>
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
// $Id: edit_about.php 9361 2008-03-19 11:00:35Z tgloeggl $

require_once('lib/messaging.inc.php');

function parse_datafields($user_id) {
	global $datafield_id, $datafield_type, $datafield_content;
	global $my_about;

	if (is_array($datafield_id)) {
		$ffCount = 0; // number of processed form fields
		foreach ($datafield_id as $i=>$id) {
			$struct = new DataFieldStructure($zw = array("datafield_id"=>$id, 'type'=>$datafield_type[$i]));
			$entry  = DataFieldEntry::createDataFieldEntry($struct, $user_id);
			$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
			if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
				$entry->setValue('');
				$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
			}
			elseif ($numFields == 1)
				$entry->setValue($datafield_content[$ffCount]);
			else
				$entry->setValue(array_slice($datafield_content, $ffCount, $numFields));
			$ffCount += $numFields;

			$entry->structure->load();
			if ($entry->isValid()) {
				$entry->store();			
			}	else {
				$invalidEntries[$struct->getID()] = $entry;
			}
		}
		/*// change visibility of role data
			foreach ($group_id as $groupID)
			setOptionsOfStGroup($groupID, $u_id, ($visible[$groupID] == '0') ? '0' : '1');*/
		$my_about->msg .= 'msg§'. _("Die Daten wurden gespeichert!").'§';
		if (is_array($invalidEntries)) {
			foreach ($invalidEntries as $field) {
				$name = $field->structure->getName();
				$my_about->msg .= 'error§'. sprintf(_("Fehlerhafte Eingabe im Datenfeld %s (wurde nicht gespeichert)!"), "<b>$name</b>") .'§';
			}
		}
	}

	return $invalidEntries;
}


// class definition
class about extends messaging {

	var $db;     //unsere Datenbankverbindung
	var $auth_user = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle auth_user_md5
	var $user_info = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle user_info
	var $user_inst = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle user_inst
	var $user_studiengang = array(); // assoziatives Array, enthält die Userdaten aus der Tabelle user_studiengang
	var $user_userdomains = array(); // assoziatives Array, enthält die Userdaten aus der Tabelle user_userdomains
	var $check = "";    //Hilfsvariable für den Rechtecheck
	var $special_user = FALSE;  // Hilfsvariable für bes. Institutsfunktionen
	var $msg = ""; //enthält evtl Fehlermeldungen
	var $max_file_size = 100; //max Größe der Bilddatei in KB
	var $logout_user = FALSE; //Hilfsvariable, zeigt an, ob der User ausgeloggt werden muß
	var $priv_msg = "";  //Änderungsnachricht bei Adminzugriff
	var $default_url = "http://www"; //default fuer private URL


	function about($username,$msg) {  // Konstruktor, prüft die Rechte
		global $user,$perm,$auth;

		$this->db = new DB_Seminar;
		$this->get_auth_user($username);
		$this->dataFieldEntries = DataFieldEntry::getDataFieldEntries($this->auth_user["user_id"]);
		$this->msg = $msg; //Meldungen restaurieren

		// der user selbst natürlich auch
		if ($auth->auth["uname"] == $username AND $perm->have_perm("autor"))
			$this->check="user";
		//bei admins schauen wir mal
		elseif ($auth->auth["perm"]=="admin") {
			$this->db->query("SELECT a.user_id FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.inst_perms='admin' AND b.user_id='$user->id') AND (a.user_id='".$this->auth_user["user_id"]."' AND a.inst_perms IN ('dozent','tutor','autor'))");
			if ($this->db->num_rows())
				$this->check="admin";

			if ($perm->is_fak_admin()){
				$this->db->query("SELECT c.user_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)  LEFT JOIN user_inst c ON(b.Institut_id=c.Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND c.user_id='".$this->auth_user["user_id"]."'");
				if ($this->db->next_record())
					$this->check="admin";
			}
		}
		//root darf mal wieder alles
		elseif ($auth->auth["perm"]=="root")
			$this->check="admin";
		else
			$this->check="";
		//hier ist wohl was falschgelaufen...
		if ($this->auth_user["username"]=="")
			$this->check="";

		return;
	}


	function get_auth_user($username) {
		//ein paar userdaten brauchen wir schon mal
		$this->db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
		$fields = $this->db->metadata();
		if ($this->db->next_record()) {
			for ($i=0; $i<count($fields); $i++) {
				$field_name = $fields[$i]["name"];
				$this->auth_user[$field_name] = $this->db->f("$field_name");
			}
		}
		if (!$this->auth_user['auth_plugin']){
			$this->auth_user['auth_plugin'] = "standard";
		}
	}

	// füllt die arrays  mit Daten
	function get_user_details() {
		$this->db->query("SELECT * FROM user_info WHERE user_id = '".$this->auth_user["user_id"]."'");
		$fields = $this->db->metadata();
		if ($this->db->next_record()) {
			for ($i=0; $i<count($fields); $i++) {
				$field_name = $fields[$i]["name"];
				$this->user_info[$field_name] = $this->db->f("$field_name");
				if (!$this->user_info["Home"])
					$this->user_info["Home"]=$this->default_url;
			}
		}

		$this->db->query("SELECT user_studiengang.*,studiengaenge.name FROM user_studiengang LEFT JOIN studiengaenge USING (studiengang_id) WHERE user_id = '".$this->auth_user["user_id"]."' ORDER BY name");
		while ($this->db->next_record()) {
			$this->user_studiengang[$this->db->f("studiengang_id")] = array("name" => $this->db->f("name"));
		}


		$this->user_userdomains = UserDomain::getUserDomainsForUser($this->auth_user['user_id']);

		$this->db->query("SELECT user_inst.*,Institute.Name FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_id = '".$this->auth_user["user_id"]."' ORDER BY priority ASC, Institut_id ASC");
		while ($this->db->next_record()) {
			$this->user_inst[$this->db->f("Institut_id")] =
				array("inst_perms" => $this->db->f("inst_perms"),
						"sprechzeiten" => $this->db->f("sprechzeiten"),
						"raum" => $this->db->f("raum"),
						"Telefon" => $this->db->f("Telefon"),
						"Fax" => $this->db->f("Fax"),
						"Name" => $this->db->f("Name"),
						"externdefault" => $this->db->f("externdefault"),
						"priority" => $this->db->f("priority"),
						"visible" => $this->db->f("visible"));
			if ($this->db->f("inst_perms")!="user")
				$this->special_user=TRUE;
		}

		return;
	}

	function studiengang_edit($studiengang_delete,$new_studiengang) {
		if (is_array($studiengang_delete)) {
			for ($i=0; $i < count($studiengang_delete); $i++) {
				$this->db->query("DELETE FROM user_studiengang WHERE user_id='".$this->auth_user["user_id"]."' AND studiengang_id='$studiengang_delete[$i]'");
				if (!$this->db->affected_rows())
					$this->msg = $this->msg."error§" . sprintf(_("Fehler beim L&ouml;schen in user_studiengang bei ID=%s"), $studiengang_delete[$i]) . "§";
			}
		}

		if ($new_studiengang) {
			$this->db->query("INSERT IGNORE INTO user_studiengang (user_id,studiengang_id) VALUES ('".$this->auth_user["user_id"]."','$new_studiengang')");
			if (!$this->db->affected_rows())
				$this->msg = $this->msg."error§" . sprintf(_("Fehler beim Einf&uuml;gen in user_studiengang bei ID=%s"), $new_studiengang) . "§";
		}

		if ( ($studiengang_delete || $new_studiengang) && !$this->msg) {
			$this->msg = "msg§" . _("Die Zuordnung zu Studiengängen wurde ge&auml;ndert.");
			setTempLanguage($this->auth_user["user_id"]);
			$this->priv_msg= _("Die Zuordnung zu Studiengängen wurde geändert!\n");
			restoreLanguage();
		}

		return;
	}



	function userdomain_edit ($userdomain_delete, $new_userdomain) {
		if (is_array($userdomain_delete)) {
			for ($i=0; $i < count($userdomain_delete); $i++) {
				$domain = new UserDomain($userdomain_delete[$i]);
				$domain->removeUser($this->auth_user['user_id']);
			}
		}

		if ($new_userdomain) {
			$domain = new UserDomain($new_userdomain);
			$domain->addUser($this->auth_user['user_id']);
		}

		if (($userdomain_delete || $new_userdomain) && !$this->msg) {
			$this->msg = "msg§" . _("Die Zuordnung zu Nutzerdomänen wurde ge&auml;ndert.");
			setTempLanguage($this->auth_user["user_id"]);
			$this->priv_msg= _("Die Zuordnung zu Nutzerdomänen wurde geändert!\n");
			restoreLanguage();
		}
	}



	function inst_edit($inst_delete,$new_inst) {
		if (is_array($inst_delete)) {
			for ($i=0; $i < count($inst_delete); $i++) {
				$this->db->query("DELETE FROM user_inst WHERE user_id='".$this->auth_user["user_id"]."' AND Institut_id='$inst_delete[$i]'");
				if (!$this->db->affected_rows())
					$this->msg = $this->msg . "error§" . sprintf(_("Fehler beim L&ouml;schen in user_inst bei ID=%s"), $inst_delete[$i]) . "§";
			}
		}

		if ($new_inst) {
			$this->db->query("INSERT IGNORE INTO user_inst (user_id,Institut_id,inst_perms) VALUES ('".$this->auth_user["user_id"]."','$new_inst','user')");
			if (!$this->db->affected_rows())
				$this->msg = $this->msg . "error§" . sprintf(_("Fehler beim Einf&uuml;gen in user_inst bei ID=%s"), $new_inst) . "§";
		}

		if ( ($inst_delete || $new_inst) && !$this->msg) {
			$this->msg = "msg§" . _("Die Zuordnung zu Einrichtungen wurde ge&auml;ndert.");
			setTempLanguage($this->auth_user["user_id"]);
			$this->priv_msg= _("Die Zuordnung zu Einrichtungen wurde geändert!\n");
			restoreLanguage();
		}

		return;
	}

	function special_edit ($raum, $sprech, $tel, $fax, $name, $default_inst, $visible, $datafield_content, $datafield_id, $datafield_type, $datafield_sec_range_id, $group_id) {
		if (is_array($raum)) {
			while (list($inst_id, $detail) = each($raum)) {
				if ($default_inst == $inst_id) {
					$this->db->query("UPDATE user_inst SET externdefault = 0 WHERE user_id = '".$this->auth_user['user_id']."'");
				}
								
				$query = "UPDATE user_inst SET raum='$detail', sprechzeiten='$sprech[$inst_id]', ";
				$query .= "Telefon='$tel[$inst_id]', Fax='$fax[$inst_id]', externdefault=";
				$query .= $default_inst == $inst_id ? '1' : '0';
				$query .= ", visible=" . (isset($visible[$inst_id]) ? '0' : '1');
				$query .= " WHERE Institut_id='$inst_id' AND user_id='" . $this->auth_user["user_id"] . "'";
				$this->db->query($query);				
				
				if ($this->db->affected_rows()) {
					$this->msg = $this->msg . "msg§" . sprintf(_("Ihre Daten an der Einrichtung %s wurden ge&auml;ndert"), htmlReady($name[$inst_id])) . "§";
					setTempLanguage($this->auth_user["user_id"]);
					$this->priv_msg = $this->priv_msg . sprintf(_("Ihre Daten an der Einrichtung %s wurden geändert.\n"), htmlReady($name[$inst_id]));
					restoreLanguage();
				}
			}
		}
		
		// process user role datafields
		if (is_array($datafield_id)) {
			$ffCount = 0; // number of processed form fields
			foreach ($datafield_id as $i=>$id) {
				$struct = new DataFieldStructure(array("datafield_id"=>$id, 'type'=>$datafield_type[$i]));
				$entry  = DataFieldEntry::createDataFieldEntry($struct, array($this->auth_user['user_id'], $datafield_sec_range_id[$i]));
				$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
				if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
					$entry->setValue('');
					$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
				}
				elseif ($numFields == 1)
					$entry->setValue($datafield_content[$ffCount]);
				else
					$entry->setValue(array_slice($datafield_content, $ffCount, $numFields));
				$ffCount += $numFields;

				$entry->structure->load();
				if ($entry->isValid())
					$entry->store();
				else
					$invalidEntries[$struct->getID()] = $entry;
			}
			// change visibility of role data
			foreach ($group_id as $groupID)
				setOptionsOfStGroup($groupID, $this->auth_user['user_id'], ($visible[$groupID] == '0') ? '0' : '1');
		}
		return $invalidEntries;
	}


	function edit_leben($lebenslauf,$schwerp,$publi,$view, $datafield_content, $datafield_id, $datafield_type) {
		//Update additional data-fields
		$invalidEntries = array();
		if (is_array($datafield_id)) {
			$ffCount = 0; // number of processed form fields
			foreach ($datafield_id as $i=>$id) {
				$numFields = $this->dataFieldEntries[$id]->numberOfHTMLFields(); // number of form fields used by this datafield
				if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
					$this->dataFieldEntries[$id]->setValue('');
					$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
				}
				elseif ($numFields == 1)
					$this->dataFieldEntries[$id]->setValue($datafield_content[$ffCount]);
				else
					$this->dataFieldEntries[$id]->setValue(array_slice($datafield_content, $ffCount, $numFields));
				$ffCount += $numFields;
				if ($this->dataFieldEntries[$id]->isValid())
					$resultDataFields |= $this->dataFieldEntries[$id]->store();
				else
					$invalidEntries[$id] = $this->dataFieldEntries[$id];
			}
		}

		//check ob die blobs verändert wurden...
		$this->db->query("SELECT  lebenslauf, schwerp, publi FROM user_info WHERE user_id='".$this->auth_user["user_id"]."'");
		$this->db->next_record();
		if ($lebenslauf!=$this->db->f("lebenslauf") || $schwerp!=$this->db->f("schwerp") || $publi!=$this->db->f("publi") || $resultDataFields) {
			$this->db->query("UPDATE user_info SET lebenslauf='$lebenslauf', schwerp='$schwerp', publi='$publi', chdate='".time()."' WHERE user_id='".$this->auth_user["user_id"]."'");
			$this->msg = $this->msg . "msg§" . _("Daten im Lebenslauf u.a. wurden ge&auml;ndert") . "§";
			setTempLanguage($this->auth_user["user_id"]);
			$this->priv_msg = _("Daten im Lebenslauf u.a. wurden geändert.\n");
			restoreLanguage();
		}
		return $invalidEntries;
	}


	function edit_pers($password, $check_pass, $response, $new_username, $vorname, $nachname, $email, $telefon, $cell, $anschrift, $home, $motto, $hobby, $geschlecht, $title_front, $title_front_chooser, $title_rear, $title_rear_chooser, $view) {
		global $UNI_NAME_CLEAN, $_language_path, $auth, $perm;
		global $ALLOW_CHANGE_USERNAME, $ALLOW_CHANGE_EMAIL, $ALLOW_CHANGE_NAME, $ALLOW_CHANGE_TITLE;

		//erstmal die "unwichtigen" Daten
		if ($home == $this->default_url)
			$home='';
		if($title_front == "")
			$title_front = $title_front_chooser;
		if($title_rear == "")
			$title_rear = $title_rear_chooser;
		$query = "";
		if (!StudipAuthAbstract::CheckField("user_info.privatnr", $this->auth_user['auth_plugin'])){
			$query .= "privatnr='$telefon',";
		}

		if (!StudipAuthAbstract::CheckField("user_info.privatcell", $this->auth_user['auth_plugin'])){
			$query .= "privatcell='$cell',";
		}

		if (!StudipAuthAbstract::CheckField("user_info.privadr", $this->auth_user['auth_plugin'])){
			$query .= "privadr='$anschrift',";
		}
		if (!StudipAuthAbstract::CheckField("user_info.Home", $this->auth_user['auth_plugin'])){
			$query .= "Home='$home',";
		}
		if (!StudipAuthAbstract::CheckField("user_info.motto", $this->auth_user['auth_plugin'])){
			$query .= "motto='$motto',";
		}
		if (!StudipAuthAbstract::CheckField("user_info.hobby", $this->auth_user['auth_plugin'])){
			$query .= "hobby='$hobby',";
		}
		if (!StudipAuthAbstract::CheckField("user_info.geschlecht", $this->auth_user['auth_plugin'])){
			$query .= "geschlecht='$geschlecht',";
		}
		if ($ALLOW_CHANGE_TITLE && !StudipAuthAbstract::CheckField("user_info.title_front", $this->auth_user['auth_plugin'])){
			$query .= "title_front='$title_front',";
		}
		if ($ALLOW_CHANGE_TITLE && !StudipAuthAbstract::CheckField("user_info.title_rear", $this->auth_user['auth_plugin'])){
			$query .= "title_rear='$title_rear',";
		}
		if ($query != "") {
			$query = "UPDATE user_info SET " . $query . " chdate='".time()."' WHERE user_id='".$this->auth_user["user_id"]."'";
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$this->msg = $this->msg . "msg§" . _("Ihre pers&ouml;nlichen Daten wurden ge&auml;ndert.") . "§";
				setTempLanguage($this->auth_user["user_id"]);
				$this->priv_msg = _("Ihre persönlichen Daten wurden geändert.\n");
				restoreLanguage();
			}
		}

		$new_username = trim($new_username);
		$vorname = trim($vorname);
		$nachname = trim($nachname);
		$email = trim($email);

		//nur nötig wenn der user selbst seine daten ändert
		if ($this->check == "user") {
			//erstmal die Syntax checken $validator wird in der local.inc.php benutzt, sollte also funzen
			$validator=new email_validation_class; ## Klasse zum Ueberpruefen der Eingaben
				$validator->timeout=10;

			if (!StudipAuthAbstract::CheckField("auth_user_md5.password", $this->auth_user['auth_plugin']) && (($response && $response!=md5("*****")) || $password!="*****")) {      //Passwort verändert ?
				// auf doppelte Vergabe wird weiter unten getestet.
				if (!isset($response) || $response=="") { // wir haben kein verschluesseltes Passwort
					if (!$validator->ValidatePassword($password)) {
						$this->msg=$this->msg . "error§" . _("Das Passwort ist nicht lang genug!") . "§";
						return false;
					}
					if ($check_pass != $password) {
						$this->msg=$this->msg . "error§" . _("Die Wiederholung des Passwortes ist falsch! Bitte geben sie das exakte Passwort ein!") . "§";
						return false;
					}
					$newpass=md5($password);             // also können wir das unverschluesselte Passwort testen
				} else
					$newpass=$response;

				$this->db->query("UPDATE auth_user_md5 SET password='$newpass' WHERE user_id='".$this->auth_user["user_id"]."'");
				$this->msg=$this->msg . "msg§" . _("Ihr Passwort wurde ge&auml;ndert!") . "§";
			}

			if (!StudipAuthAbstract::CheckField('auth_user_md5.Vorname', $this->auth_user['auth_plugin']) && $vorname != $this->auth_user['Vorname']) { //Vornamen verändert ?
				if ($ALLOW_CHANGE_NAME) {
					if (!$validator->ValidateName($vorname)) {
						$this->msg=$this->msg . "error§" . _("Der Vorname fehlt oder ist unsinnig!") . "§";
						return false;
					}   // Vorname nicht korrekt oder fehlend
					$this->db->query("UPDATE auth_user_md5 SET Vorname='$vorname' WHERE user_id='".$this->auth_user["user_id"]."'");
					$this->msg=$this->msg . "msg§" . _("Ihr Vorname wurde ge&auml;ndert!") . "§";
				} else $vorname = $this->auth_user['Vorname'];
			}

			if (!StudipAuthAbstract::CheckField('auth_user_md5.Nachname', $this->auth_user['auth_plugin']) && $nachname != $this->auth_user['Nachname']) { //Namen verändert ?
				if ($ALLOW_CHANGE_NAME) {
					if (!$validator->ValidateName($nachname)) {
						$this->msg=$this->msg . "error§" . _("Der Nachname fehlt oder ist unsinnig!") . "§";
						return false;
					}   // Nachname nicht korrekt oder fehlend
					$this->db->query("UPDATE auth_user_md5 SET Nachname='$nachname' WHERE user_id='".$this->auth_user["user_id"]."'");
					$this->msg=$this->msg . "msg§" . _("Ihr Nachname wurde ge&auml;ndert!") . "§";
				} else $nachname = $this->auth_user['Nachname'];
			}


			if (!StudipAuthAbstract::CheckField('auth_user_md5.username', $this->auth_user['auth_plugin']) && $this->auth_user['username'] != $new_username) {
				if ($ALLOW_CHANGE_USERNAME) {
					if (!$validator->ValidateUsername($new_username)) {
						$this->msg=$this->msg . "error§" . _("Der gewählte Username ist nicht lang genug!") . "§";
						return false;
					}
					$check_uname = StudipAuthAbstract::CheckUsername($new_username);
					if ($check_uname['found']) {
						$this->msg .= "error§" . _("Der Username wird bereits von einem anderen User verwendet. Bitte wählen sie einen anderen Usernamen!") . "§";
						return false;
					} else {
						//$this->msg .= "info§" . $check_uname['error'] ."§";
					}
					$this->db->query("UPDATE auth_user_md5 SET username='$new_username' WHERE user_id='".$this->auth_user["user_id"]."'");
					$this->msg=$this->msg . "msg§" . _("Ihr Username wurde ge&auml;ndert!") . "§";
					$this->logout_user = TRUE;
				} else $new_username = $this->auth_user['username'];
			}


			if (!StudipAuthAbstract::CheckField("auth_user_md5.Email", $this->auth_user['auth_plugin']) && $this->auth_user["Email"] != $email) {  //email wurde geändert!
				if ($ALLOW_CHANGE_EMAIL) {
					$smtp=new studip_smtp_class;       ## Einstellungen fuer das Verschicken der Mails
						$REMOTE_ADDR=$_SERVER["REMOTE_ADDR"];
					$Zeit=date("H:i:s, d.m.Y",time());

					// accept only registered domains if set
					$email_restriction = trim(get_config('EMAIL_DOMAIN_RESTRICTION'));
					if (!$validator->ValidateEmailAddress($email, $email_restriction)) {
						if ($email_restriction) {
							$email_restriction_msg_part = '';
							$email_restriction_parts = explode(',', $email_restriction);
							for ($email_restriction_count = 0; $email_restriction_count < count($email_restriction_parts); $email_restriction_count++) {
								if ($email_restriction_count == count($email_restriction_parts) - 1) {
									$email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . '<br />';
								} else if (($email_restriction_count + 1) % 3) {
									$email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ', ';
								} else {
									$email_restriction_msg_part .= '@' . trim($email_restriction_parts[$email_restriction_count]) . ',<br />';
								}
							}
							$this->msg = $this->msg . 'error§'
								. sprintf(_("Die E-Mail-Adresse fehlt, ist falsch geschrieben oder gehört nicht zu folgenden Domains:%s"), '<br>' . $email_restriction_msg_part);
						} else {
							$this->msg=$this->msg . "error§" . _("Die E-Mail-Adresse fehlt oder ist falsch geschrieben!") . "§";
						}
						return false;        // E-Mail syntaktisch nicht korrekt oder fehlend
					}

					if (!$validator->ValidateEmailHost($email)) {     // Mailserver nicht erreichbar, ablehnen
						$this->msg=$this->msg . "error§" . _("Der Mailserver ist nicht erreichbar. Bitte &uuml;berpr&uuml;fen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken k&ouml;nnen!") . "§";
						return false;
					} else {       // Server ereichbar
						if (!$validator->ValidateEmailBox($email)) {    // aber user unbekannt. Mail an abuse!
							$from = $smtp->env_from;
							$to = $smtp->abuse;
							$smtp->SendMessage(
									$from, array($to),
									array("From: $from", "To: $to", "Subject: edit_about"),
									"Emailbox unbekannt\n\nUser: ".$this->auth_user["username"]."\nEmail: $email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
							$this->msg=$this->msg . "error§" . _("Die angegebene E-Mail-Adresse ist nicht erreichbar. Bitte &uuml;berpr&uuml;fen Sie Ihre Angaben!") . "§";
							return false;
						}
					}

					$this->db->query("SELECT Email,Vorname,Nachname FROM auth_user_md5 WHERE Email='$email'") ;
					if ($this->db->next_record()) {
						$this->msg=$this->msg . "error§" . sprintf(_("Die angegebene E-Mail-Adresse wird bereits von einem anderen User (%s %s) verwendet. Bitte geben Sie eine andere E-Mail-Adresse an."), htmlReady($this->db->f("Vorname")), htmlReady($this->db->f("Nachname"))) . "§";
						return false;
					}

					if (!StudipAuthAbstract::CheckField("auth_user_md5.password", $this->auth_user['auth_plugin'])){
						//email ist ok, user bekommt neues Passwort an diese Addresse, falls Passwort in Stud.IP DB
						$newpass=$this->generate_password(6);
						$hashpass=md5($newpass);
						// Mail abschicken...
						$to=$email;
						$url = $smtp->url;

						// include language-specific subject and mailbody
						include_once("locale/$_language_path/LC_MAILS/change_self_mail.inc.php");

						$smtp->SendMessage(
								$smtp->env_from, array($to),
								array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: $subject"),
								$mailbody);
						$this->logout_user = TRUE;
						$this->msg = $this->msg . "msg§" . _("Ihre E-Mail-Adresse wurde ge&auml;ndert!") . "§info§" . _("ACHTUNG!<br>Aus Sicherheitsgr&uuml;nden wurde auch ihr Passwort ge&auml;ndert. Es wurde an die neue E-Mail-Adresse geschickt!") . "§";
						$this->db->query("UPDATE auth_user_md5 SET Email='$email', password='$hashpass' WHERE user_id='".$this->auth_user["user_id"]."'");
						log_event("USER_NEWPWD",$this->auth_user["user_id"]); // logging
					} else {
						$this->msg = $this->msg . "msg§" . _("Ihre E-Mail-Adresse wurde ge&auml;ndert!") . "§";
						$this->db->query("UPDATE auth_user_md5 SET Email='$email' WHERE user_id='".$this->auth_user["user_id"]."'");
					}
				}
			}
		}
		return;
	}


	function select_studiengang() {  //Hilfsfunktion, erzeugt eine Auswahlbox mit noch auswählbaren Studiengängen

		echo '<select name="new_studiengang" style="width:30ex;"><option selected></option>'."\n";
		$this->db->query("SELECT a.studiengang_id,a.name FROM studiengaenge AS a LEFT JOIN user_studiengang AS b ON (b.user_id='".$this->auth_user["user_id"]."' AND a.studiengang_id=b.studiengang_id) WHERE b.studiengang_id IS NULL ORDER BY a.name");

		while ($this->db->next_record()) {
			echo "<option value=\"".$this->db->f("studiengang_id")."\">".htmlReady(my_substr($this->db->f("name"),0,50))."</option>\n";
		}
		echo "</select>\n";

		return;
	}


	function select_userdomain() {  //Hilfsfunktion, erzeugt eine Auswahlbox mit noch auswählbaren Nutzerdomänen

		echo '<select name="new_userdomain" style="width:30ex;"><option selected></option>'."\n";
		$user_domains = UserDomain::getUserDomainsForUser($this->auth_user['user_id']);
		$domains = UserDomain::getUserDomains();

		foreach (array_diff($domains, $user_domains) as $domain) {
			echo "<option value=\"".$domain->getID()."\">".htmlReady(my_substr($domain->getName(),0,50))."</option>\n";
		}
		echo "</select>\n";
	}


	function select_inst() {  //Hilfsfunktion, erzeugt eine Auswahlbox mit noch auswählbaren Instituten

		echo '<select name="new_inst" style="width:30ex;"><option selected></option>'."\n";
		$this->db->query("SELECT a.Institut_id,a.Name FROM Institute AS a LEFT JOIN user_inst AS b ON (b.user_id='".$this->auth_user["user_id"]."' AND a.Institut_id=b.Institut_id) WHERE b.Institut_id IS NULL ORDER BY a.Name");

		while ($this->db->next_record()) {
			echo "<option value=\"".$this->db->f("Institut_id")."\">".htmlReady(my_substr($this->db->f("Name"),0,50))."</option>\n";
		}
		echo "</select>\n";

		return;
	}


	function generate_password($length) {      //Hilfsfunktion, erzeugt neues Passwort

		mt_srand((double)microtime()*1000000);
		for ($i=1;$i<=$length;$i++) {
			$temp = mt_rand() % 36;
			if ($temp < 10)
				$temp += 48;   // 0 = chr(48), 9 = chr(57)
			else
				$temp += 87;   // a = chr(97), z = chr(122)
			$pass .= chr($temp);
		}
		return $pass;
	}



	//Displays Errosmessages (kritischer Abbruch, Symbol "X")

	function my_error($msg) {
		?>
			<tr>
			<td class="blank" colspan=2>
			<table border="0" align="left" cellspacing="0" cellpadding="2">
			<tr>
			<td class="blank" align="center" width="50"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/x.gif"></td>
			<td class="blank" align="left" width="*"><font color="#FF2020"><?php print $msg ?></font></td>
			</tr>
			</table>
			</td>
			</tr>
			<tr>
			<td class="blank" colspan="2">&nbsp;</td>
			</tr>
			<?
	}


	//Displays  Successmessages (Information &uuml;ber erfolgreiche Aktion, Symbol Haken)

	function my_msg($msg) {
		?>
			<tr>
			<td class="blank" colspan=2>
			<table border="0" align="left" cellspacing="0" cellpadding="2">
			<tr>
			<td class="blank" align="center" width=50><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/ok.gif"></td>
			<td class="blank" align="left" width="*"><font color="#008000"><?php print $msg ?></font></td>
			</tr>
			</table>
			</td>
			</tr>
			<tr>
			<td class="blank" colspan="2">&nbsp;</td>
			</tr>
			<?
	}

	//Displays  Informationmessages  (Hinweisnachrichten, Symbol Ausrufungszeichen)

	function my_info($msg) {
		?>
			<tr>
			<td class="blank" colspan="2">
			<table border="0" align="left" cellspacing="0" cellpadding="2">
			<tr>
			<td class="blank" align="center" width="50"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/ausruf.gif"></td>
			<td class="blank" align="left" width="*"><font color="#008000"><?php print $msg ?></font></td>
			</tr>
			</table>
			</td>
			</tr>
			<tr>
			<td class="blank" colspan="2">&nbsp;</td>
			</tr>
			<?
	}

	function parse_msg($long_msg,$separator="§") {

		$msg = explode ($separator,$long_msg);
		for ($i=0; $i < count($msg); $i=$i+2) {
			switch ($msg[$i]) {
				case "error" : $this->my_error($msg[$i+1]); break;
				case "info" : $this->my_info($msg[$i+1]); break;
				case "msg" : $this->my_msg($msg[$i+1]); break;
			}
		}
		return;
	}

	function move ($inst_id, $direction) {
		if ($this->check == 'user' || $this->check == 'admin') {
			$db = new DB_Seminar();
			$query = "SELECT * FROM user_inst WHERE user_id = '{$this->auth_user['user_id']}' ";
			$query .= "AND inst_perms != 'user' ORDER BY priority ASC";
			$db->query($query);
			$i = 1;
			while ($db->next_record()) {
				$to_order[$i] = $db->f('Institut_id');
				if ($to_order[$i] == $inst_id)
					$pos = $i;
				$i++;
			}
			if ($direction == 'up') {
				$a = $to_order[$pos - 1];
				$to_order[$pos - 1] = $to_order[$pos];
				$to_order[$pos] = $a;
			}
			else {
				$a = $to_order[$pos + 1];
				$to_order[$pos + 1] = $to_order[$pos];
				$to_order[$pos] = $a;
			}
			$i--;
			for (;$i > 0; $i--) {
				$query = "UPDATE user_inst SET priority = $i WHERE user_id = '{$this->auth_user['user_id']}' ";
				$query .= "AND Institut_id = '{$to_order[$i]}'";
				$db->query($query);
			}
		}
	}


} // end class definition
