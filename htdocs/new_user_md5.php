<?php
/*
new_user_md5.php - die globale Benutzerverwaltung von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("admin");

// Set this to something, just something different...
$hash_secret = "jdfiuwenxclka";

// generate_password($length):
//
// Erzeugt ein Passwort mit $length Zeichen [a-z0-9]
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


include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); // Funktionen fuer Nachrichtenmeldungen
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); //Wir brauchen die Funktionen zum Loeschen von Terminen...
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php"); // Wir brauchen den Namen der Uni
require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php"); // Wir brauchen die Funktionen zum Loeschen der folder
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/admission.inc.php");	 //Enthaelt Funktionen zum Updaten der Wartelisten
require_once("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");	 //Enthaelt Funktionen fuer Statusgruppen
require_once("$ABSOLUTE_PATH_STUDIP/contact.inc.php");	 //Enthaelt Funktionen fuer Adressbuchverwaltung

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/lib/DeleteResourcesUser.class.php");
}
if ($ILIAS_CONNECT_ENABLE) {
	include_once ("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_LEARNINGMODULES/lernmodul_db_functions.inc.php");
	include_once ("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_LEARNINGMODULES/lernmodul_user_functions.inc.php");
}

$cssSw=new cssClassSwitcher;

//-- hier muessen Seiten-Initialisierungen passieren --

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");	 //hier wird der "Kopf" nachgeladen 

include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");	//Linkleiste fuer admins


// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$validator=new email_validation_class;	// Klasse zum Ueberpruefen der Eingaben
$validator->timeout=10;									// Wie lange warten wir auf eine Antwort des Mailservers?
$smtp=new studip_smtp_class;									 // Einstellungen fuer das Verschicken der Mails
$Zeit=date("H:i:s, d.m.Y",time());


// Check if there was a submission
while ( is_array($HTTP_POST_VARS) 
		 && list($key, $val) = each($HTTP_POST_VARS)) {
	switch ($key) {

	// Create a new user
	case "create_x":

		$run = TRUE;
		// Do we have permission to do so?
		if (!$perm->is_fak_admin() && addslashes(implode($perms,",")) == "admin") {
			$msg .= "error§" . _("Sie haben keine Berechtigung <b>admins</b> anzulegen.") . "§";
			$run = FALSE;
		}
		if (!$perm->have_perm("root") && addslashes(implode($perms,",")) == "root") {
			$msg .= "error§" . _("Sie haben keine Berechtigung <b>roots</b> anzulegen.") . "§";
			$run = FALSE;
		}
		
		$username = trim($username);
		$Vorname = trim($Vorname);
		$Nachname = trim($Nachname);
		$Email = trim($Email);
		
		// Do we have all necessary data?
		if (empty($username) || empty($perms) || empty ($Email)) {
			$msg .= "error§" . _("Bitte geben Sie <b>Username</b>, <b>Status</b> und <b>Email</b> an!") . "§";
			$run = FALSE;
		}

		// Does the user already exist?
		// NOTE: This should be a transaction, but it is not...
		$db->query("select * from auth_user_md5 where username='$username'");
		if ($db->nf()>0) {
			$msg .= "error§" . sprintf(_("Benutzer <b>%s</b> ist schon vorhanden!"), $username) . "§";
			$run = FALSE;
		}

		// E-Mail erreichbar?
		if (!$validator->ValidateEmailHost($Email)) {		 // Mailserver nicht erreichbar, ablehnen
			$msg .= "error§" . _("Mailserver ist nicht erreichbar!") . "§";
			$run = FALSE;
		} else {																					// Server ereichbar
			if (!$validator->ValidateEmailBox($Email)) {		// aber user unbekannt, ablehnen
				$msg .= "error§" . sprintf(_("E-Mail an <b>%s</b> ist nicht zustellbar!"), $Email) . "§";
				$run = FALSE;
			}
		}
		
		if ($run) { // alle Angaben ok
			// Create a uid and insert the user...
			$u_id=md5(uniqid($hash_secret));
			$permlist = addslashes(implode($perms,","));
			$password = generate_password(6);
			$hashpass = md5($password);
			if (!$title_front)
				$title_front = $title_front_chooser;
			if (!$title_rear)
				$title_rear = $title_rear_chooser;
				
			$query = "INSERT INTO auth_user_md5 (user_id, username, password, perms, Vorname, Nachname, Email) values('$u_id','$username','$hashpass','$permlist','$Vorname','$Nachname','$Email')";
			$query2 = "INSERT INTO user_info SET user_id='$u_id', geschlecht='$geschlecht', title_front='$title_front',title_rear='$title_rear', mkdate='".time()."', chdate='".time()."' ";			
			$db->query($query);
			$db2->query($query2);

			if (($db->affected_rows() == 0) && ($db2->affected_rows() == 0)) {
				$msg .= "error§" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "§";
				$run = FALSE;
				}
		}
		
		//do auto inserts, if we created an autor
		if (($run) && (($permlist == "autor") || ($permlist == "tutor") || ($permlist == "dozent"))) {
			if (is_array($AUTO_INSERT_SEM)){
				foreach ($AUTO_INSERT_SEM as $a) {
					$db->query("SELECT Name, start_time FROM seminare WHERE Seminar_id = '$a'");
					$db->next_record();							
					$group=select_group ($db->f("start_time"),$u_id);							
					$db2->query("INSERT into seminar_user (Seminar_id, user_id, status, gruppe) values ('$a', '$u_id', 'autor', '$group')");
					$msg .= sprintf("msg§" . _("Der Nutzer wurde automatisch in die Veranstaltung <b>%s</b> eingetragen.") . "§", $db->f("Name"));
				}
			}
		}
		
		if ($run) { // Benutzer angelegt
			$msg .= "msg§" . sprintf(_("Benutzer \"%s\" angelegt."), $username) . "§";

			// Mail abschicken...
			$to=$Email;
			$url = "http://" . $smtp->localhost . $CANONICAL_RELATIVE_PATH_STUDIP;

			// include language-specific subject and mailbody
			$user_language = getUserLanguagePath($u_id); // user has been just created, so we will get $DEFAULT_LANGUAGE
			include_once("$ABSOLUTE_PATH_STUDIP"."locale/$user_language/LC_MAILS/create_mail.inc.php");

			$smtp->SendMessage(
			$smtp->env_from, array($to),
			array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: $subject"),
			$mailbody);
		}

		break;

	// Change user parameters
	case "u_edit_x":

		$run = TRUE;
		// Do we have permission to do so?
		if (!$perm->is_fak_admin() && addslashes(implode($perms,",")) == "admin") {
			$msg .= "error§" . _("Sie haben keine Berechtigung, <b>Administratoren</b> anzulegen.") . "§";
			$run = FALSE;
		}
		if (!$perm->have_perm("root") && addslashes(implode($perms,",")) == "root") {
			$msg .= "error§" . _("Sie haben keine Berechtigung, <b>Roots</b> anzulegen.") . "§";
			$run = FALSE;
		}
		if (!$perm->have_perm("root")) {
			$db->query("select * from auth_user_md5 where user_id='$u_id'");
			$db->next_record();
			if (!$perm->is_fak_admin() && $db->f("perms") == "admin") {
				$msg .= "error§" . _("Sie haben keine Berechtigung <b>Administratoren</b> zu ver&auml;ndern.") . "§";
				$run = FALSE;
			}
			if ($db->f("perms") == "root") {
				$msg .= "error§" . _("Sie haben keine Berechtigung <b>Roots</b> zu ver&auml;ndern.") . "§";
				$run = FALSE;
			}
			if ($perm->is_fak_admin() && $db->f("perms") == "admin"){
				$db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a 
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id) 
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '$user->id' AND c.inst_perms='admin') 
							WHERE a.user_id ='$u_id' AND a.inst_perms = 'admin'");
				$db->next_record();
				$run = $db->f("admin_ok");
				if (!$run){
					$msg .= "error§" . _("Sie haben keine Berechtigung diesen Admin zu ver&auml;ndern.") . "§";
				}
			}
		}
		
		// aktiver Dozent?
		$db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '$u_id' AND status = 'dozent' GROUP BY user_id");
		$db->next_record();
		if ($db->f("count") && addslashes(implode($perms,",")) != "dozent") {
			$msg .= sprintf("error§" . "Der Benutzer <b>%s</b> ist Dozent in %s aktiven Veranstaltungen und kann daher nicht in einen anderen Status versetzt werden." . "§", $username, $db->f("count"));
			$run = FALSE;
		}
			
		
		$username = trim($username);
		$Vorname = trim($Vorname);
		$Nachname = trim($Nachname);
		$Email = trim($Email);
		
		// Do we have all necessary data?
		if (empty($username) || empty($perms) || empty ($Email)) {
			$msg .= "error§" . _("Bitte geben Sie <b>Username</b>, <b>Status</b> und <b>Email</b> an!") . "§";
			$run = FALSE;
		}
		
		if ($run) { // alle Rechte und Angaben ok
			// E-Mail erreichbar?
			if (!$validator->ValidateEmailHost($Email)) {		 // Mailserver nicht erreichbar, ablehnen
				$msg .= "error§" . _("Mailserver ist nicht erreichbar!") . "§";
				$run = FALSE;
			} else {																					// Server ereichbar
				if (!$validator->ValidateEmailBox($Email)) {		// aber user unbekannt, ablehnen
					$msg .= "error§" . sprintf(_("E-Mail an <b>%s</b> ist nicht zustellbar!"), $Email) . "§";
					$run = FALSE;
				}
			}
		}
		
		if ($run) { // E-Mail erreichbar
			// Update user information.
			$permlist = addslashes(implode($perms,","));
			if (!$title_front)
				$title_front = $title_front_chooser;
			if (!$title_rear)
				$title_rear = $title_rear_chooser;
			$query = "UPDATE auth_user_md5 set username='$username', perms='$permlist', Vorname='$Vorname', Nachname='$Nachname', Email='$Email' where user_id='$u_id'";
			$query2 = "UPDATE user_info SET geschlecht='$geschlecht', title_front='$title_front',title_rear='$title_rear',chdate='".time()."' WHERE user_id = '$u_id' ";			
			$db->query($query);
			$db2->query($query2);

			if (($db->affected_rows() == 0) && ($db2->affected_rows() == 0)) {
				$msg .= "error§" . _("Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden.") . "§";
				$run = FALSE;
			}
			if ($ILIAS_CONNECT_ENABLE) {
				$db->query("SELECT preferred_language FROM user_info WHERE user_id='$u_id'");
				if ($db->next_record()) 
					$preferred_language = $db->f("preferred_language");
				$this_ilias_id = get_connected_user_id($u_id);
				if ($this_ilias_id != false) 
					edit_ilias_user($this_ilias_id, $username, $geschlecht, $Vorname, $Nachname, $title_front, "Stud.IP", $Email, $permlist, $preferred_language);
			}
		}
		
		if ($run) { // Aenderung erfolgt
			$msg .= "msg§" . sprintf(_("User \"%s\" ver&auml;ndert."), $username) . "§";

			// Mail abschicken...
			$to=$Email;
			$url = "http://" . $smtp->localhost . $CANONICAL_RELATIVE_PATH_STUDIP;

			// include language-specific subject and mailbody
			$user_language = getUserLanguagePath($u_id);
			include_once("$ABSOLUTE_PATH_STUDIP"."locale/$user_language/LC_MAILS/change_mail.inc.php");

			$smtp->SendMessage(
			$smtp->env_from, array($to),
			array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: $subject"),
			$mailbody);
			
			//do auto inserts, if we changed to autor or higher
			if (($permlist == "autor") || ($permlist == "tutor") || ($permlist == "dozent")) {
				if (is_array($AUTO_INSERT_SEM)){
					foreach ($AUTO_INSERT_SEM as $a) {
						$db->query("SELECT Name, start_time, Schreibzugriff FROM seminare WHERE Seminar_id = '$a'");
						if ($db->num_rows()) {
							$db->next_record();
							if ($db->f("Schreibzugriff") < 2) { // es gibt das Seminar und es ist kein Passwort gesetzt
								$db2 = new DB_Seminar;
								$db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$a' AND user_id='$u_id'");
								if ($db2->num_rows()) { // Benutzer ist schon eingetragen
									$db2->next_record();
									if ($db2->f("status") == "user") { // wir können ihn hochstufen
										$db2->query("UPDATE seminar_user SET status = 'autor' WHERE Seminar_id = '$a' AND user_id='$user->id'");	
										$msg .= sprintf("msg§" . _("Dem Nutzer wurden wurden Schreibrechte in der Veranstaltung <b>%s</b> erteilt.") . "§", $db->f("Name"));
									}
								} else {  // Benutzer ist noch nicht eingetragen
									$group=select_group ($db->f("start_time"),$u_id);							
									$db2->query("INSERT into seminar_user (Seminar_id, user_id, status, gruppe) values ('$a', '$u_id', 'autor', '$group')");
									$msg .= sprintf("msg§" . _("Der Nutzer wurde automatisch in die Veranstaltung <b>%s</b> eingetragen.") . "§", $db->f("Name"));
								}
							}
						}
					}
				}
			}
				

			// Hochstufung auf admin oder root?
			if (addslashes(implode($perms,",")) == "admin" || addslashes(implode($perms,",")) == "root") {
				//Eintraege aus Veranstaltungen loeschen
				$query = "delete from seminar_user where user_id='$u_id'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Veranstaltungen gel&ouml;scht."), $db_ar) . "§";
				}
				//Eintraege aus Wartelisten loeschen
				$query2 = "SELECT seminar_id FROM admission_seminar_user where user_id='$u_id'";
				$query = "delete from admission_seminar_user where user_id='$u_id'";
				$db->query($query);
				$db2->query($query2);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Wartelisten gel&ouml;scht."), $db_ar) . "§";
				while ($db2->next_record()) 
					update_admission($db2->f("seminar_id"));
				}
			}
			if (addslashes(implode($perms,",")) == "admin") {
				$query = "delete from user_inst where user_id='$u_id' AND inst_perms != 'admin'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Mitarbeiterlisten gel&ouml;scht."), $db_ar) . "§";
				}
			}
			if (addslashes(implode($perms,",")) == "root") {
				$query = "delete from user_inst where user_id='$u_id'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Mitarbeiterlisten gel&ouml;scht."), $db_ar) . "§";
				}
			}
		}

		break;

	// Change user password
	case "u_pass_x":
	
		$run = TRUE;
		// Do we have permission to do so?
		if (!$perm->have_perm("root")) {
			$db->query("select * from auth_user_md5 where user_id='$u_id'");
			$db->next_record();
			if ($db->f("perms") == "root") {
				$msg .= "error§" . _("Sie haben keine Berechtigung <b>Roots</b> zu ver&auml;ndern.") . "§";
				$run = FALSE;
			}
			if ($perm->is_fak_admin() && $db->f("perms") == "admin"){
				$db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a 
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id) 
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '$user->id' AND c.inst_perms='admin') 
							WHERE a.user_id ='$u_id' AND a.inst_perms = 'admin'");
				$db->next_record();
				$run = $db->f("admin_ok");
				if (!$run){
					$msg .= "error§" . _("Sie haben keine Berechtigung diesen Admin zu ver&auml;ndern.") . "§";
				}
			}
			
		}
		
		if ($run) { // Rechte ok
			// E-Mail erreichbar?
			if (!$validator->ValidateEmailHost($Email)) {		 // Mailserver nicht erreichbar, ablehnen
				$msg .= "error§" . _("Mailserver ist nicht erreichbar!") . "§";
      	$run = FALSE;
			} else {																					// Server ereichbar
				if (!$validator->ValidateEmailBox($Email)) {		// aber user unbekannt, ablehnen
					$msg .= "error§" . sprintf(_("E-Mail an <b>%s</b> ist nicht zustellbar!"), $Email) . "§";
      		$run = FALSE;
				}
			}
		}
		
		if ($run) { // E-Mail erreichbar
			// Update user password.
			$permlist = addslashes(implode($perms,","));
			$password = generate_password(6);
			$hashpass = md5($password);
			$query = "update auth_user_md5 set password='$hashpass' where user_id='$u_id'";
			$db->query($query);
			if ($db->affected_rows() == 0) {
				$msg .= "error§<b>" . _("Fehlgeschlagen:") . "</b> " . $query . "§";
				break;
			}
		}
		
		if ($run) { // Aenderung durchgefuehrt
			$msg .= "msg§" . sprintf(_("Passwort von User \"%s\" neu gesetzt."), $username) . "§";

			// Mail abschicken...
			$to=$Email;
			$url = "http://" . $smtp->localhost . $CANONICAL_RELATIVE_PATH_STUDIP;

			// include language-specific subject and mailbody
			$user_language = getUserLanguagePath($u_id);
			include_once("$ABSOLUTE_PATH_STUDIP"."locale/$user_language/LC_MAILS/password_mail.inc.php");

			$smtp->SendMessage(
			$smtp->env_from, array($to),
			array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: $subject"),
			$mailbody);
		}

		break;

	// Delete the user
	case "u_kill_x":
	
		$run = TRUE;
		// Do we have permission to do so?
		if (!$perm->have_perm("root")) {
			$db->query("select * from auth_user_md5 where user_id='$u_id'");
			$db->next_record();
			if ($db->f("perms") == "root") {
				$msg .= "error§" . _("Sie haben keine Berechtigung <b>Roots</b> zu l&ouml;schen.") . "§";
				$run = FALSE;
			}
			if ($perm->is_fak_admin() && $db->f("perms") == "admin"){
				$db->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a 
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id) 
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '$user->id' AND c.inst_perms='admin') 
							WHERE a.user_id ='$u_id' AND a.inst_perms = 'admin'");
				$db->next_record();
				$run = $db->f("admin_ok");
				if (!$run){
					$msg .= "error§" . _("Sie haben keine Berechtigung diesen Admin zu l&ouml;schen.") . "§";
				}
			}
		}
		
		// aktiver Dozent?
		$db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '$u_id' AND status = 'dozent' GROUP BY user_id");
		$db->next_record();
		if ($db->f("count")) {
			$msg .= sprintf("error§" . _("Der Benutzer <b>%s</b> ist Dozent in %s aktiven Veranstaltungen und kann daher nicht gel&ouml;scht werden.") . "§", $username, $db->f("count"));
			$run = FALSE;
		}

		if ($run) { // Rechte ok
			// Delete that user.
			
			// store user preferred language for sending mail
			$user_language = getUserLanguagePath($u_id);
			
			// delete user from seminars (postings will be preserved)
			$query = "delete from seminar_user where user_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Veranstaltungen gel&ouml;scht."), $db_ar) . "§";
			}
			// delete user from waiting lists
			$query2 = "SELECT seminar_id FROM admission_seminar_user where user_id='$u_id'";
			$query = "delete from admission_seminar_user where user_id='$u_id'";
			$db->query($query);
			$db2->query($query2);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Wartelisten gel&ouml;scht."), $db_ar) . "§";
			while ($db2->next_record()) 
				update_admission($db2->f("seminar_id"));
			}
			// delete 'Studiengaenge'
			$query = "delete from user_studiengang where user_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0)
				$msg .= "info§" . sprintf(_("%s Zuordnungen zu Studieng&auml;ngen gel&ouml;scht."), $db_ar) . "§";
			// Dokumente des users loeschen
			$temp_count = 0;
			$query = "SELECT dokument_id FROM dokumente WHERE user_id='$u_id'";
			$db->query($query);
			while ($db->next_record()) {
				if (delete_document($db->f("dokument_id")))
					$temp_count ++;
			}
			if ($temp_count) {
				$msg .= "info§" . sprintf(_("%s Dokumente gel&ouml;scht."), $temp_count) . "§";
			}
			// delete empty folders of this user
			$temp_count = 0;
			$query = "SELECT folder_id FROM folder WHERE user_id='$u_id' ORDER BY mkdate DESC";
			$db->query($query);
			while ($db->next_record()) {
				$query = "SELECT count(*) AS count FROM folder WHERE range_id = '".$db->f("folder_id")."'";
				$db2->query($query);
	 			$db2->next_record();
				if (!$db2->f("count") && !doc_count($db->f("folder_id"))) {
					$query = "DELETE FROM folder WHERE folder_id ='".$db->f("folder_id")."'";
					$db2->query($query);
					$temp_count += $db2->affected_rows();
				}
			}
			if ($temp_count) {
				$msg .= "info§" . sprintf(_(" leere Ordner gel&ouml;scht."), $temp_count) . "§";
			}
			// folder left?
			$query = "SELECT count(*) AS count FROM folder WHERE user_id='$u_id'";
			$db->query($query);
	 		$db->next_record();
			if ($db->f("count")) {
				$msg .= sprintf("info§" . _("%s Ordner konnten nicht gel&ouml;scht werden, da sie noch Dokumente anderer Benutzer enthalten.") . "§", $db->f("count"));
			}
			// delete user from instituts
			$query = "delete from user_inst where user_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Mitarbeiterlisten gel&ouml;scht."), $db_ar) . "§";
			}
			// user aus den Statusgruppen rauswerfen
			if ($db_ar = RemovePersonFromAllStatusgruppen(get_username($u_id))  > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Funktionen / Gruppen gel&ouml;scht."), $db_ar) . "§";
			}
			// Alle persoenlichen Termine dieses users löschen
		 	if ($db_ar = delete_range_of_dates($u_id, FALSE) > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus den Terminen gel&ouml;scht."), $db_ar) . "§";
			}
			// Alle persoenlichen News-Verweise auf diesen user löschen
		 	$query = "DELETE FROM news_range where range_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§" . sprintf(_("%s Verweise auf News gel&ouml;scht."), $db_ar) . "§";
			}
			// Die News durchsehen, ob es da jetzt verweiste Einträge gibt...
		 	$query = "SELECT news.news_id FROM news LEFT OUTER JOIN news_range USING (news_id) where range_id IS NULL";
			$db->query($query);
			while ($db->next_record()) {	// Diese News hängen an nix mehr...
				$tempNews_id = $db->f("news_id");
			 	$query = "DELETE FROM news where news_id = '$tempNews_id'";
				$db2->query($query);
			}
			if (($db_ar = $db->num_rows()) > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus den News gel&ouml;scht."), $db_ar) . "§";
			}
			// user aus dem Archiv werfen
			$query = "delete from archiv_user where user_id='$u_id'";
 			$db->query($query);
		 	if (($db_ar = $db->affected_rows()) > 0) {
			 	$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus dem Zugriffsberechtigungen f&uuml;r das Archiv gel&ouml;scht."), $db_ar) . "§";
	 		}
			// alle Daten des users löschen
			$query = "delete from kategorien where range_id = '$u_id'";
			$db->query($query);
			$query = "delete from active_sessions where sid = '$u_id'";
			$db->query($query);
			$query = "delete from globalmessages where user_id_rec = '$username'";
			$db->query($query);
			$query = "delete from globalmessages where user_id_snd = '$username'";
			$db->query($query);
			$query = "delete from user_info where user_id= '$u_id'";
			$db->query($query);
			if(file_exists("./user/".$u_id.".jpg")) {
				if (unlink("./user/".$u_id.".jpg"))
					$msg .= "info§" . _("Bild gel&ouml;scht.") . "§";
				else
					$msg .= "error§" . _("Bild konnte nicht gel&ouml;scht werden.") . "§";
			}
			//kill all the ressources that are assigned to the Veranstaltung (and all the linked or subordinated stuff!)
			if ($RESOURCES_ENABLE) {
				$killAssign = new DeleteResourcesUser($u_id);
				$killAssign->delete();
			}
			//kill ILIAS-Account if it was automatically generated)
			if ($ILIAS_CONNECT_ENABLE) {
				$this_ilias_id = get_connected_user_id($u_id);
				if (($this_ilias_id != false) AND (is_created_user($u_id) == 1))
					delete_ilias_user($this_ilias_id);
			}
			
			// Aus allen Adressbüchern und persönlcihen Einträgen raus...
			$buddykills = RemoveUserFromBuddys($u_id);
			if ($buddykills > 0) {
				$msg .= "info§" . sprintf(_("%s Eintr&auml;ge aus Adressb&uuml;chern gel&ouml;scht."), $buddykills) . "§";
			}

			$query = "delete from auth_user_md5 where user_id='$u_id' and username='$username'";
			$db->query($query);
			if ($db->affected_rows() == 0) {
				$msg .= "error§<b>" . _("Fehlgeschlagen:") . "</b> " . $query . "§";
      			$run = FALSE;
			}
		}
		
		if ($run) { // User geloescht
			$msg .= "msg§" . sprintf(_("User \"%s\" gel&ouml;scht."), $username) . "§";

			// E-Mail erreichbar?
			if (!$validator->ValidateEmailHost($Email)) {		 // Mailserver nicht erreichbar, ablehnen
				$msg .= "error§" . _("Mailserver ist nicht erreichbar!") . "§";
			} else {																					// Server ereichbar
				if (!$validator->ValidateEmailBox($Email)) {		// aber user unbekannt, ablehnen
					$msg .= "error§" . sprintf(_("E-Mail an <b>%s</b> ist nicht zustellbar!"), $Email) . "§";
				} else {
					// Mail abschicken...
					$permlist = addslashes(implode($perms,","));
					$to=$Email;

					// include language-specific subject and mailbody
					include_once("$ABSOLUTE_PATH_STUDIP"."locale/$user_language/LC_MAILS/delete_mail.inc.php");

					$smtp->SendMessage(
					$smtp->env_from, array($to),
					array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: $subject"),
					$mailbody);
				}
			}
		}
		break;
	
	default:
		break;
 	}
}

	// einzelnen Benutzer anzeigen
if (isset($details)) {
	if ($details=="") { // neuen Benutzer anlegen
		?>
		<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
		<tr valign=top align=middle>
			<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Eingabe eines neuen Benutzers")?></b></td>
		</tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2>

			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?php echo $PHP_SELF ?>">
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Benutzername:")?></b></td>
					<td>&nbsp;<input type="text" name="username" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("globaler Status:")?>&nbsp;</b></td>
					<td>&nbsp;<? print $perm->perm_sel("perms", $db->f("perms")) ?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Vorname:")?></b></td>
					<td>&nbsp;<input type="text" name="Vorname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Nachname:")?></b></td>
					<td>&nbsp;<input type="text" name="Nachname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
				<td><b>&nbsp;<?=_("Titel:")?></b>
				</td><td align="right"><select name="title_front_chooser" onChange="document.edit.title_front.value=document.edit.title_front_chooser.options[document.edit.title_front_chooser.selectedIndex].text;">
				<?
				for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i){
					echo "\n<option>$TITLE_FRONT_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td>&nbsp;<input type="text" name="title_front" value="" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td><b>&nbsp;<?=_("Titel nachgest.:")?></b>
				</td><td align="right"><select name="title_rear_chooser" onChange="document.edit.title_rear.value=document.edit.title_rear_chooser.options[document.edit.title_rear_chooser.selectedIndex].text;">
				<?
				for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i){
					echo "\n<option>$TITLE_REAR_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td>&nbsp;<input type="text" name="title_rear" value="" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td colspan="2"><b>&nbsp;<?=_("Geschlecht:")?></b></td>
				<td>&nbsp;<input type="RADIO" checked name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp;
				<input type="RADIO" name="geschlecht" value="1"><?=_("weiblich")?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("E-Mail:")?></b></td>
					<td>&nbsp;<input type="text" name="Email" size=48 maxlength=63 value="">&nbsp;</td>
				</tr>
				<td colspan=3 align=center>&nbsp;
				<input type="IMAGE" name="create" <?=makeButton("anlegen", "src")?> value=" <?=_("Benutzer anlegen")?> ">&nbsp;
				<input type="IMAGE" name="nothing" <?=makeButton("abbrechen", "src")?> value=" <?=_("Abbrechen")?> ">
				&nbsp;</td></tr>
			</form></table>
			
		</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		</table>
		<?

	} else { // alten Benutzer bearbeiten
	
		$db->query("SELECT auth_user_md5.*, changed, mkdate, title_rear, title_front, geschlecht FROM auth_user_md5 LEFT JOIN active_sessions ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) WHERE username ='$details'");
		while ($db->next_record()) {
			if ($db->f("changed") != "") {
				$stamp = mktime(substr($db->f("changed"),8,2),substr($db->f("changed"),10,2),substr($db->f("changed"),12,2),substr($db->f("changed"),4,2),substr($db->f("changed"),6,2),substr($db->f("changed"),0,4));
				$inactive = floor((time() - $stamp) / 3600 / 24)	." " . _("Tagen");
			} else {
				$inactive = _("nie benutzt");
			}
			?>
			
			<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
			<tr valign=top align=middle>
				<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Ver&auml;ndern eines bestehenden Benutzers")?></b></td>
			</tr>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>
			<tr><td class="blank" colspan=2>
			
			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?php echo $PHP_SELF ?>">
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Benutzername:")?></b></td>
					<td class="steel1">&nbsp;<input type="text" name="username" size=24 maxlength=63 value="<?php $db->p("username") ?>"></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("globaler Status:")?>&nbsp;</b></td>
					<td class="steel1">&nbsp;<? print $perm->perm_sel("perms", $db->f("perms")) ?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Vorname:")?></b></td>
					<td class="steel1">&nbsp;<input type="text" name="Vorname" size=24 maxlength=63 value="<?php $db->p("Vorname") ?>"></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Nachname:")?></b></td>
					<td class="steel1">&nbsp;<input type="text" name="Nachname" size=24 maxlength=63 value="<?php $db->p("Nachname") ?>"></td>
				</tr>
				<td class="steel1"><b>&nbsp;<?=_("Titel:")?></b>
				</td><td class="steel1" align="right"><select name="title_front_chooser" onChange="document.edit.title_front.value=document.edit.title_front_chooser.options[document.edit.title_front_chooser.selectedIndex].text;">
				<?
				 for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i){
					 echo "\n<option";
					 if($TITLE_FRONT_TEMPLATE[$i] == $db->f("title_front"))
					 	echo " selected ";
					 echo ">$TITLE_FRONT_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td class="steel1">&nbsp;<input type="text" name="title_front" value="<?=$db->f("title_front")?>" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td class="steel1"><b>&nbsp;<?=_("Titel nachgest.:")?></b>
				</td><td class="steel1" align="right"><select name="title_rear_chooser" onChange="document.edit.title_rear.value=document.edit.title_rear_chooser.options[document.edit.title_rear_chooser.selectedIndex].text;">
				<?
				 for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i){
					 echo "\n<option";
					 if($TITLE_REAR_TEMPLATE[$i] == $db->f("title_rear"))
					 	echo " selected ";
					 echo ">$TITLE_REAR_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td class="steel1">&nbsp;<input type="text" name="title_rear" value="<?=$db->f("title_rear")?>" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td colspan="2" class="steel1"><b>&nbsp;<?=_("Geschlecht:")?></b></td>
				<td class="steel1">&nbsp;<input type="RADIO" <? if (!$db->f("geschlecht")) echo "checked";?> name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp;
				<input type="RADIO" <? if ($db->f("geschlecht")) echo "checked";?> name="geschlecht" value="1"><?=_("weiblich")?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("E-Mail:")?></b></td>
					<td class="steel1">&nbsp;<input type="text" name="Email" size=48 maxlength=63 value="<?php $db->p("Email") ?>">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("inaktiv seit:")?></b></td>
					<td class="steel1">&nbsp;<? echo $inactive ?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("registriert seit:")?></b></td>
					<td class="steel1">&nbsp;<? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo _("unbekannt"); ?></td>
				</tr>
				
				<td class="steel1" colspan=3 align=center>&nbsp;
				<input type="hidden" name="u_id"	 value="<?= $db->f("user_id") ?>">
				<?
				if ($perm->is_fak_admin() && $db->f("perms") == "admin"){
					$db2->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a 
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id) 
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '$user->id' AND c.inst_perms='admin') 
							WHERE a.user_id ='".$db->f("user_id")."' AND a.inst_perms = 'admin'");
					$db2->next_record();
				}
			
				if ($perm->have_perm("root") || 
					($db->f("perms") != "admin" && $db->f("perms") != "root") ||
					$db2->f("admin_ok") ) {
					?>
					<input type="IMAGE" name="u_edit" <?=makeButton("uebernehmen", "src")?> value=" <?=_("Ver&auml;ndern")?> ">&nbsp;
					<input type="IMAGE" name="u_pass" <?=makeButton("neuespasswort", "src")?> value=" <?=_("Passwort neu setzen")?> ">&nbsp;
					<input type="IMAGE" name="u_kill" <?=makeButton("loeschen", "src")?> value=" <?=_("L&ouml;schen")?> ">&nbsp;
					<?
		 		}
				?>
				<input type="IMAGE" name="nothing" <?=makeButton("abbrechen", "src")?> value=" <?=_("Abbrechen")?> ">
				&nbsp;</td></tr>
			</form>
			
			<tr><td colspan=3 class="blank">&nbsp;</td></tr>
			
			<? // links to everywhere
			print "<tr><td class=\"steelgraulight\" colspan=3 align=\"center\">";
				printf("&nbsp;" . _("pers&ouml;nliche Homepage") . " <a href=\"about.php?username=%s\"><img src=\"pictures/einst.gif\" border=0 alt=\"Zur pers&ouml;nlichen Homepage des Benutzers\" align=\"texttop\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp", $db->f("username"));
				printf("&nbsp;" . _("Nachricht an Benutzer") . " <a href=\"sms.php?cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an den Benutzer verschicken\" border=0 align=\"texttop\"></a>", $db->f("username"));
			print "</td></tr>";
			
			$temp_user_id = $db->f("user_id");
			if ($perm->have_perm("root"))
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_id ='$temp_user_id' AND inst_perms != 'user'");
			elseif ($perm->is_fak_admin())
				$db2->query("SELECT a.Institut_id,b.Name FROM user_inst AS a 
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id) 
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id ) 
							WHERE a.user_id ='".$db->f("user_id")."' AND a.inst_perms = 'admin' AND c.user_id = '$user->id' AND c.inst_perms='admin'");
			else	
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst AS x LEFT JOIN user_inst AS y USING (Institut_id) LEFT JOIN Institute USING (Institut_id) WHERE x.user_id ='$temp_user_id' AND x.inst_perms != 'user' AND y.user_id = '$user->id' AND y.inst_perms = 'admin'");
			if ($db2->num_rows()) {
				print "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				print "<b>&nbsp;" . _("Link zur Mitarbeiter-Verwaltung") . "&nbsp;</b>";
				print "</td></tr>\n";
			}
			while ($db2->next_record()) {
				print "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				printf ("&nbsp;%s <a href=\"inst_admin.php?details=%s&admin_inst_id=%s\"><img src=\"pictures/admin.gif\" border=0 align=\"texttop\" alt=\"&Auml;ndern der Eintr&auml;ge des Benutzers in der jeweiligen Einrichtung\"></a>&nbsp;", htmlReady($db2->f("Name")), $db->f("username"), $db2->f("Institut_id"));
				print "</td></tr>\n";
			}	
			?>
			
			</table>

			</td></tr>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>

			</table>
			<?
		}
	}

} else {
	
	// Gesamtliste anzeigen

	?>

	<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr valign=top align=middle>
		<td class="topic" colspan=2 align="left"><b>&nbsp;<?=_("Verwaltung aller Benutzer des Systems")?></b></td>
	</tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>

	<?
	parse_msg($msg);
	?>

	<tr><td class="blank" colspan=2>
	
	<p><b><a href="<? echo $PHP_SELF . "?details="?>">&nbsp;<?=_("Neuen Benutzer anlegen")?></a></b></p>

	<?
	unset($msg);
	include ("pers_browse.inc.php");
	print "<br>\n";
	parse_msg($msg);
	

	if (isset($pers_browse_search_string)) { // Es wurde eine Suche initiert

		// nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
		if (!isset($sortby) || $sortby=="") {
			if (!isset($new_user_md5_sortby) || $new_user_md5_sortby == "") {
				$new_user_md5_sortby = "username";
			}
		} else {
			$new_user_md5_sortby = $sortby;
			$sess->register("new_user_md5_sortby");
		}

		// Traverse the result set
		$db->query("SELECT auth_user_md5.*, changed, mkdate FROM auth_user_md5 LEFT JOIN active_sessions ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) $pers_browse_search_string ORDER BY $new_user_md5_sortby");

		if ($db->num_rows() == 0) { // kein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 cellpadding=2 width=\"80%\">";
			print "<tr valign=\"top\" align=\"middle\">";
			print "<td>" . _("Es wurden keine Benutzer gefunden, auf die die obigen Kriterien zutreffen.") . "</td>";
			print "</tr><tr><td class=\"blank\">&nbsp;</td></tr></table>";

		} else { // wir haben ein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 class=blank cellpadding=2 width=\"100%\">";
			print "<tr valign=\"top\" align=\"middle\">";
				if ($db->num_rows() == 1)
			 		print("<td colspan=7>" . _("Suchergebnis: Es wurde <b>1</b> Benutzer gefunden.") . "</td></tr>\n");
				else
			 		printf("<td colspan=7>" . _("Suchergebnis: Es wurden <b>%s</b> Benutzer gefunden.") . "</td></tr>\n", $db->num_rows());
			?>
			 <tr valign="top" align="middle">
				<th align="left"><a href="new_user_md5.php?sortby=username"><?=_("Benutzername")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=perms"><?=_("Status")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Vorname"><?=_("Vorname")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Nachname"><?=_("Nachname")?></a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Email"><?=_("E-Mail")?></a></th>
				<th><a href="new_user_md5.php?sortby=changed"><?=_("inaktiv")?></a></th>
				<th><a href="new_user_md5.php?sortby=mkdate"><?=_("registriert seit")?></a></th>				
			 </tr>
			<?	

			while ($db->next_record()):
				if ($db->f("changed") != "") {
					$stamp = mktime(substr($db->f("changed"),8,2),substr($db->f("changed"),10,2),substr($db->f("changed"),12,2),substr($db->f("changed"),4,2),substr($db->f("changed"),6,2),substr($db->f("changed"),0,4));
					$inactive = floor((time() - $stamp) / 3600 / 24);
				} else {
					$inactive = _("nie benutzt");
				}
				?>
				<tr valign=middle align=left>
					<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><a href="<?php echo $PHP_SELF . "?details=" . $db->f("username") ?>"><?php $db->p("username") ?></a></td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("perms") ?></td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("Vorname") ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("Nachname") ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("Email")?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><?php echo $inactive ?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo _("unbekannt"); ?></td>
				</tr>
				<?
			endwhile;
			print ("</table>");
		}
	}
	print ("</td></tr></table>");
	

}

page_close();
?>
</body>
</html>
