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

## Set this to something, just something different...
	$hash_secret = "jdfiuwenxclka";

## generate_password($length):
##
## Erzeugt ein Passwort mit $length Zeichen [a-z0-9]
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

?>
<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
	<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
 </head>

<body>

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert
	require_once("msg.inc.php"); // Funktionen fuer Nachrichtenmeldungen
	require_once('dates.inc.php'); //Wir brauchen die Funktionen zum Loeschen von Terminen...
	require_once("config.inc.php"); // Wir brauchen den Namen der Uni
	require_once("datei.inc.php"); // Wir brauchen die Funktionen zum Loeschen der folder
	require_once("visual.inc.php");
	require_once("admission.inc.php");	 //Enthaelt Funktionen zum Updaten der Wartelisten
	$cssSw=new cssClassSwitcher;

//-- hier muessen Seiten-Initialisierungen passieren --

	include "header.php";	 //hier wird der "Kopf" nachgeladen 
?>
<body>

<?php
	include "links_admin.inc.php";	//Linkleiste fuer admins


## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$validator=new email_validation_class;	## Klasse zum Ueberpruefen der Eingaben
$validator->timeout=10;									## Wie lange warten wir auf eine Antwort des Mailservers?
$smtp=new smtp_class;									 ## Einstellungen fuer das Verschicken der Mails
$smtp->host_name=getenv("SERVER_NAME");
$smtp->localhost="localhost";
$Zeit=date("H:i:s, d.m.Y",time());


## Check if there was a submission
while ( is_array($HTTP_POST_VARS) 
		 && list($key, $val) = each($HTTP_POST_VARS)) {
	switch ($key) {

	## Create a new user
	case "create":
		$run = TRUE;
		## Do we have permission to do so?
		if (!$perm->have_perm("root") && addslashes(implode($perms,",")) == "admin") {
			$msg .= "error§Sie haben keine Berechtigung <b>admins</b> anzulegen.§";
			$run = FALSE;
		}
		if (!$perm->have_perm("root") && addslashes(implode($perms,",")) == "root") {
			$msg .= "error§Sie haben keine Berechtigung <b>roots</b> anzulegen.§";
			$run = FALSE;
		}
		
		$username = trim($username);
		$Vorname = trim($Vorname);
		$Nachname = trim($Nachname);
		$Email = trim($Email);
		
		## Do we have all necessary data?
		if (empty($username) || empty($perms) || empty ($Email)) {
			$msg .= "error§Bitte geben Sie <B>Username</B>, <B>Status</B> und <B>Email</B> an!§";
			$run = FALSE;
		}

		## Does the user already exist?
		## NOTE: This should be a transaction, but it isn't...
		$db->query("select * from auth_user_md5 where username='$username'");
		if ($db->nf()>0) {
			$msg .= "error§Benutzer <B>$username</B> ist schon vorhanden!§";
			$run = FALSE;
		}

		## E-Mail erreichbar?
		if (!$validator->ValidateEmailHost($Email)) {		 ## Mailserver nicht erreichbar, ablehnen
			$msg .= "error§Mailserver ist nicht erreichbar!§";
			$run = FALSE;
		} else {																					## Server ereichbar
			if (!$validator->ValidateEmailBox($Email)) {		## aber user unbekannt, ablehnen
				$msg .= "error§E-Mail an <B>$Email</B> ist nicht zustellbar!§";
				$run = FALSE;
			}
		}
		
		if ($run) { // alle Angaben ok
			## Create a uid and insert the user...
			$u_id=md5(uniqid($hash_secret));
			$permlist = addslashes(implode($perms,","));
			$password = generate_password(6);
			$hashpass = md5($password);
			$query = "INSERT INTO auth_user_md5 (user_id, username, password, perms, Vorname, Nachname, Email) values('$u_id','$username','$hashpass','$permlist','$Vorname','$Nachname','$Email')";
			$query2 = "INSERT INTO user_info SET user_id='$u_id', mkdate='".time()."', chdate='".time()."' ";			
			$db->query($query);
			$db2->query($query2);

			if (($db->affected_rows() == 0) && ($db2->affected_rows() == 0)) {
				$msg .= "error§Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden§";
				$run = FALSE;
				}
		}
		
		if ($run) { // Benutzer angelegt
			$msg .= "msg§Benutzer \"$username\" angelegt.§";

			## Mail abschicken...
			$from="\"Stud.IP\" <wwwrun@".$smtp->host_name.">";
			$env_from="wwwrun@".$smtp->host_name;
			$abuse="abuse@".$smtp->host_name;
			$to=$Email;
			$url = "http://" . $smtp->host_name . $CANONICAL_RELATIVE_PATH_STUDIP;
			$mailbody="Dies ist eine Informationsmail des Systems\n"
			."\"Studentischer Internetsupport Präsenzlehre\"\n"
			."- $UNI_NAME_CLEAN -\n\n"
			."Sie wurden um $Zeit mit folgenden Angaben von einem\n"
			."der Administratoren ins System eingetragen:\n\n"
			."Benutzername: $username\n"
			."Passwort: $password\n"
			."Status: $permlist\n"
			."Vorname: $Vorname\n"
			."Nachname: $Nachname\n"
			."Email-Adresse: $Email\n\n"
			."Diese Mail wurde Ihnen zugesandt um Ihnen den Benutzernamen\n"
			."und das Passwort mitzuteilen, mit dem Sie sich am System anmelden.\n\n"
			."Sie finden die Startseite des Systems unter folgender URL:\n\n"
			."$url\n\n"
			."Möglicherweise unterstützt ihr Mail-Programm ein einfaches Anklicken des Links.\n"
			."Ansonsten müssen sie Ihren Browser öffnen und den Link komplett in die Zeile\n"
			."\"Location\" oder \"URL\" kopieren.\n\n"
			."Um Zugang auf die nichtöffentlichen Bereiche des Systems zu bekommen\n"
			."müssen Sie sich unter \"Login\" oben rechts auf der Seite anmelden.\n"
			."Geben Sie bitte unter Benutzername \"$username\" und unter\n"
			."Passwort \"$password\" ein.\n\n"
			."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
			."weiter (auch nicht an einen Administrator), damit nicht Dritte in ihrem\n"
			."Namen Nachrichten in das System einstellen können!\n\n";
			$smtp->SendMessage(
			$env_from, array($to),
			array("From: $from", "Reply-To: $abuse", "To: $to", "Subject: Anmeldung Stud.IP"),
			$mailbody);
		}

	break;

	## Change user parameters
	case "u_edit":
		$run = TRUE;
		## Do we have permission to do so?
		if (!$perm->have_perm("root") && addslashes(implode($perms,",")) == "admin") {
			$msg .= "error§Sie haben keine Berechtigung, <b>Administratoren</b> anzulegen.§";
			$run = FALSE;
		}
		if (!$perm->have_perm("root") && addslashes(implode($perms,",")) == "root") {
			$msg .= "error§Sie haben keine Berechtigung, <b>Roots</b> anzulegen.§";
			$run = FALSE;
		}
		if (!$perm->have_perm("root")) {
			$db->query("select * from auth_user_md5 where user_id='$u_id'");
			$db->next_record();
			if ($db->f("perms") == "admin") {
				$msg .= "error§Sie haben keine Berechtigung <b>admins</b> zu ver&auml;ndern.§";
				$run = FALSE;
			}
			if ($db->f("perms") == "root") {
				$msg .= "error§Sie haben keine Berechtigung <b>roots</b> zu ver&auml;ndern.§";
				$run = FALSE;
			}
		}
		// aktiver Dozent?
		$db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '$u_id' AND status = 'dozent' GROUP BY user_id");
		$db->next_record();
		if ($db->f("count") && addslashes(implode($perms,",")) != "dozent") {
			$msg .= sprintf("error§Der Benutzer <b>$username</b> ist Dozent in %s aktiven Veranstaltungen und kann daher nicht in einen anderen Status versetzt werden.§", $db->f("count"));
			$run = FALSE;
		}
			
		
		$username = trim($username);
		$Vorname = trim($Vorname);
		$Nachname = trim($Nachname);
		$Email = trim($Email);
		
		## Do we have all necessary data?
		if (empty($username) || empty($perms) || empty ($Email)) {
			$msg .= "error§Bitte geben Sie <B>Username</B>, <B>Status</B> und <B>Email</B> an!§";
			$run = FALSE;
		}
		
		if ($run) { // alle Rechte und Angaben ok
			## E-Mail erreichbar?
			if (!$validator->ValidateEmailHost($Email)) {		 ## Mailserver nicht erreichbar, ablehnen
				$msg .= "error§Mailserver ist nicht erreichbar!§";
				$run = FALSE;
			} else {																					## Server ereichbar
				if (!$validator->ValidateEmailBox($Email)) {		## aber user unbekannt, ablehnen
					$msg .= "error§E-Mail an <B>$Email</B> ist nicht zustellbar!§";
					$run = FALSE;
				}
			}
		}
		
		if ($run) { // E-Mail erreichbar
			## Update user information.
			$permlist = addslashes(implode($perms,","));
			$query = "UPDATE auth_user_md5 set username='$username', perms='$permlist', Vorname='$Vorname', Nachname='$Nachname', Email='$Email' where user_id='$u_id'";
			$query2 = "UPDATE user_info SET chdate='".time()."' WHERE user_id = '$u_id' ";			
			$db->query($query);
			$db2->query($query2);

			if (($db->affected_rows() == 0) && ($db2->affected_rows() == 0)) {
				$msg .= "error§Die &Auml;nderung konnte nicht in die Datenbank geschrieben werden§";
				$run = FALSE;
				}
		}
		
		if ($run) { // Aenderung erfolgt
			$msg .= "msg§User \"$username\" ver&auml;ndert.§";

			## Mail abschicken...
			$from="\"Stud.IP\" <wwwrun@".$smtp->host_name.">";
			$env_from="wwwrun@".$smtp->host_name;
			$abuse="abuse@".$smtp->host_name;
			$to=$Email;
			$url = "http://" . $smtp->host_name . $CANONICAL_RELATIVE_PATH_STUDIP;
			$mailbody="Dies ist eine Informationsmail des Systems\n"
			."\"Studentischer Internetsupport Präsenzlehre\"\n"
			."- $UNI_NAME_CLEAN -\n\n"
			."Ihr Account wurde um $Zeit von einem der Administratoren verändert.\n"
			."Die aktuellen Angaben lauten:\n\n"
			."Benutzername: $username\n"
			."Status: $permlist\n"
			."Vorname: $Vorname\n"
			."Nachname: $Nachname\n"
			."Email-Adresse: $Email\n\n"
			."Ihr Passwort hat sich nicht verändert.\n\n"
			."Diese Mail wurde Ihnen zugesandt um Sie über die Änderungen zu informieren.\n\n"
			."Wenn Sie Einwände gegen die Änderungen haben, wenden Sie sich bitte an\n"
			."$abuse. Sie können einfach auf diese Mail antworten.\n\n"
			."Hier kommen Sie direkt ins System:\n"
			."$url\n\n"
;
			$smtp->SendMessage(
			$env_from, array($to),
			array("From: $from", "Reply-To: $abuse", "To: $to", "Subject: Account-Änderung Stud.IP"),
			$mailbody);

			// Hochstufung auf admin oder root?
			if (addslashes(implode($perms,",")) == "admin" || addslashes(implode($perms,",")) == "root") {
				//Eintraege aus Veranstaltungen loeschen
				$query = "delete from seminar_user where user_id='$u_id'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§$db_ar Eintr&auml;ge aus Veranstaltungen gel&ouml;scht.§";
				}
				//Eintraege aus Wartelisten loeschen
				$query2 = "SELECT seminar_id FROM admission_seminar_user where user_id='$u_id'";
				$query = "delete from admission_seminar_user where user_id='$u_id'";
				$db->query($query);
				$db2->query($query2);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§$db_ar Eintr&auml;ge aus Wartelisten gel&ouml;scht.§";
				while ($db2->next_record()) 
					update_admission($db2->f("seminar_id"));
				}
			}
			if (addslashes(implode($perms,",")) == "admin") {
				$query = "delete from user_inst where user_id='$u_id' AND inst_perms != 'admin'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§$db_ar Eintr&auml;ge aus Mitarbeiterlisten gel&ouml;scht.§";
				}
			}
			if (addslashes(implode($perms,",")) == "root") {
				$query = "delete from user_inst where user_id='$u_id'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§$db_ar Eintr&auml;ge aus Mitarbeiterlisten gel&ouml;scht.§";
				}
				$query = "delete from fakultaet_user where user_id='$u_id'";
				$db->query($query);
				if (($db_ar = $db->affected_rows()) > 0) {
					$msg .= "info§$db_ar Eintr&auml;ge aus den Fakult&auml;tsangeh&ouml;rigen gel&ouml;scht.§";
				}
			}
		}

	break;

	## Change user password
	case "u_pass":
		$run = TRUE;
		## Do we have permission to do so?
		if (!$perm->have_perm("root")) {
			$db->query("select * from auth_user_md5 where user_id='$u_id'");
			$db->next_record();
			if ($db->f("perms") == "admin") {
				$msg .= "error§Sie haben keine Berechtigung <b>admins</b> zu ver&auml;ndern.§";
      	$run = FALSE;
			}
			if ($db->f("perms") == "root") {
				$msg .= "error§Sie haben keine Berechtigung <b>roots</b> zu ver&auml;ndern.§";
      	$run = FALSE;
			}
		}
		
		if ($run) { // Rechte ok
			## E-Mail erreichbar?
			if (!$validator->ValidateEmailHost($Email)) {		 ## Mailserver nicht erreichbar, ablehnen
				$msg .= "error§Mailserver ist nicht erreichbar!§";
      	$run = FALSE;
			} else {																					## Server ereichbar
				if (!$validator->ValidateEmailBox($Email)) {		## aber user unbekannt, ablehnen
					$msg .= "error§E-Mail an <B>$Email</B> ist nicht zustellbar!§";
      		$run = FALSE;
				}
			}
		}
		
		if ($run) { // E-Mail erreichbar
			## Update user password.
			$permlist = addslashes(implode($perms,","));
			$password = generate_password(6);
			$hashpass = md5($password);
			$query = "update auth_user_md5 set password='$hashpass' where user_id='$u_id'";
			$db->query($query);
			if ($db->affected_rows() == 0) {
				$msg .= "error§<b>Fehlgeschlagen:</b> $query§";
				break;
			}
		}
		
		if ($run) { // Aenderung durchgefuehrt
			$msg .= "msg§Passwort von User \"$username\" neu gesetzt.§";

			## Mail abschicken...
			$from="\"Stud.IP\" <wwwrun@".$smtp->host_name.">";
			$env_from="wwwrun@".$smtp->host_name;
			$abuse="abuse@".$smtp->host_name;
			$to=$Email;
			$url = "http://" . $smtp->host_name . $CANONICAL_RELATIVE_PATH_STUDIP;
			$mailbody="Dies ist eine Informationsmail des Systems\n"
			."\"Studentischer Internetsupport Präsenzlehre\"\n"
			."- $UNI_NAME_CLEAN -\n\n"
			."Ihr Passwort wurde um $Zeit von einem der Administratoren neu gesetzt.\n"
			."Die aktuellen Angaben lauten:\n\n"
			."Benutzername: $username\n"
			."Passwort: $password\n"
			."Status: $permlist\n"
			."Vorname: $Vorname\n"
			."Nachname: $Nachname\n"
			."Email-Adresse: $Email\n\n"
			."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
			."weiter (auch nicht an einen Administrator), damit nicht Dritte in ihrem\n"
			."Namen Nachrichten in das System einstellen können!\n"
			."Hier kommen Sie direkt ins System:\n"
			."$url\n\n"
;
			$smtp->SendMessage(
			$env_from, array($to),
			array("From: $from", "Reply-To: $abuse", "To: $to", "Subject: Passwort-Änderung Stud.IP"),
			$mailbody);
		}

	break;

	## Delete the user
	case "u_kill":
		$run = TRUE;
		## Do we have permission to do so?
		if (!$perm->have_perm("root")) {
			$db->query("select * from auth_user_md5 where user_id='$u_id'");
			$db->next_record();
			if ($db->f("perms") == "admin") {
				$msg .= "error§Sie haben keine Berechtigung <b>admins</b> zu l&ouml;schen.§";
      	$run = FALSE;
			}
			if ($db->f("perms") == "root") {
				$msg .= "error§Sie haben keine Berechtigung <b>roots</b> zu l&ouml;schen.§";
      	$run = FALSE;
			}
		}
		
		// aktiver Dozent?
		$db->query("SELECT count(*) AS count FROM seminar_user WHERE user_id = '$u_id' AND status = 'dozent' GROUP BY user_id");
		$db->next_record();
		if ($db->f("count")) {
			$msg .= sprintf("error§Der Benutzer <b>$username</b> ist Dozent in %s aktiven Veranstaltungen und kann daher nicht gel&ouml;scht werden.§", $db->f("count"));
			$run = FALSE;
		}

		if ($run) { // Rechte ok
			## Delete that user.
			## user aus den Seminaren rauswerfen (seine Postings bleiben aber stehen)
			$query = "delete from seminar_user where user_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§$db_ar Eintr&auml;ge aus Veranstaltungen gel&ouml;scht.§";
			}
			## user aus den Wartelisten rauswerfen
			$query2 = "SELECT seminar_id FROM admission_seminar_user where user_id='$u_id'";
			$query = "delete from admission_seminar_user where user_id='$u_id'";
			$db->query($query);
			$db2->query($query2);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§$db_ar Eintr&auml;ge aus Wartelisten gel&ouml;scht.§";
			while ($db2->next_record()) 
				update_admission($db2->f("seminar_id"));
			}
			## Dokumente des users loeschen
			$temp_count = 0;
			$query = "SELECT dokument_id FROM dokumente WHERE user_id='$u_id'";
			$db->query($query);
			while ($db->next_record()) {
				if (delete_document($db->f("dokument_id")))
					$temp_count ++;
			}
			if ($temp_count) {
				$msg .= "info§$temp_count Dokumente gel&ouml;scht.§";
			}
			## Leere folder des users loeschen
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
				$msg .= "info§$temp_count leere Ordner gel&ouml;scht.§";
			}
			## noch folder ueber?
			$query = "SELECT count(*) AS count FROM folder WHERE user_id='$u_id'";
			$db->query($query);
	 		$db->next_record();
			if ($db->f("count")) {
				$msg .= sprintf("info§%s Ordner konnten nicht gel&ouml;scht werden, da sie noch Dokumente anderer Benutzer enthalten.§", $db->f("count"));
			}
			## user aus den Instituten rauswerfen
			$query = "delete from user_inst where user_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§$db_ar Eintr&auml;ge aus Mitarbeirerlisten gel&ouml;scht.§";
			}
			## user aus den Fakultaeten rauswerfen
			$query = "delete from fakultaet_user where user_id='$u_id'";
 			$db->query($query);
		 	if (($db_ar = $db->affected_rows()) > 0) {
			 	$msg .= "info§$db_ar Eintr&auml;ge der Fakult&auml;tsangeh&ouml;rigen gel&ouml;scht.§";
	 		}
			## Alle persoenlichen Termine dieses users löschen
		 	if ($db_ar = delete_range_of_dates($u_id, FALSE) > 0) {
				$msg .= "info§$db_ar Eintr&auml;ge aus den Terminen gel&ouml;scht.§";
			}
			## Alle persoenlichen News-Verweise auf diesen user löschen
		 	$query = "DELETE FROM news_range where range_id='$u_id'";
			$db->query($query);
			if (($db_ar = $db->affected_rows()) > 0) {
				$msg .= "info§$db_ar Eintr&auml;ge aus  den News gel&ouml;scht.§";
			}
			## Die News durchsehen, ob es da jetzt verweiste Einträge gibt...
		 	$query = "SELECT news.news_id FROM news LEFT OUTER JOIN news_range USING (news_id) where range_id IS NULL";
			$db->query($query);
			while ($db->next_record()) {	// Diese News hängen an nix mehr...
				$tempNews_id = $db->f("news_id");
			 	$query = "DELETE FROM news where news_id = '$tempNews_id'";
				$db2->query($query);
			}
			if (($db_ar = $db->num_rows()) > 0) {
				$msg .= "info§$db_ar Eintr&auml;ge aus den News gel&ouml;scht.§";
			}
			## user aus dem Archiv werfen
			$query = "delete from archiv_user where user_id='$u_id'";
 			$db->query($query);
		 	if (($db_ar = $db->affected_rows()) > 0) {
			 	$msg .= "info§$db_ar Eintr&auml;ge aus dem Zugriffsberechtigungen f&uuml;r das Archiv gel&ouml;scht.§";
	 		}
			## alle Daten des lusers löschen
			$query = "delete from kategorien where range_id = '$u_id'";
			$db->query($query);
			$query = "delete from active_sessions where sid = '$u_id'";
			$db->query($query);
			$query = "delete from globalmessages where user_id_rec = '$u_id'";
			$db->query($query);
			$query = "delete from globalmessages where user_id_snd = '$u_id'";
			$db->query($query);
			$query = "delete from user_info where user_id= '$u_id'";
			$db->query($query);
			if(file_exists("./user/".$u_id.".jpg")) {
				if (unlink("./user/".$u_id.".jpg"))
					$msg .= "info§Bild gel&ouml;scht.§";
				else
					$msg .= "error§Bild konnte nicht gel&ouml;scht werden.§";
			}
			$query = "delete from auth_user_md5 where user_id='$u_id' and username='$username'";
			$db->query($query);
			if ($db->affected_rows() == 0) {
				$msg .= "error§<b>Fehlgeschlagen:</b> $query§";
      	$run = FALSE;
			}
		}
		
		if ($run) { // User geloescht
			$msg .= "msg§User \"$username\" gel&ouml;scht.§";

			## E-Mail erreichbar?
			if (!$validator->ValidateEmailHost($Email)) {		 ## Mailserver nicht erreichbar, ablehnen
				$msg .= "error§Mailserver ist nicht erreichbar!§";
			} else {																					## Server ereichbar
				if (!$validator->ValidateEmailBox($Email)) {		## aber user unbekannt, ablehnen
					$msg .= "error§E-Mail an <B>$Email</B> ist nicht zustellbar!§";
				} else {
					## Mail abschicken...
					$permlist = addslashes(implode($perms,","));
					$from="\"Stud.IP\" <wwwrun@".$smtp->host_name.">";
					$env_from="wwwrun@".$smtp->host_name;
					$abuse="abuse@".$smtp->host_name;
					$to=$Email;
					$mailbody="Dies ist eine Informationsmail des Systems\n"
					."\"Studentischer Internetsupport Präsenzlehre\"\n"
					."- $UNI_NAME_CLEAN -\n\n"
					."Ihr Account\n\n"
					."Benutzername: $username\n"
					."Status: $permlist\n"
					."Vorname: $Vorname\n"
					."Nachname: $Nachname\n"
					."Email-Adresse: $Email\n\n"
					."wurde um $Zeit von einem der Administratoren gelöscht.\n";
					$smtp->SendMessage(
					$env_from, array($to),
					array("From: $from", "Reply-To: $abuse", "To: $to", "Subject: Account-Löschung Stud.IP"),
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
			<td class="topic" colspan=2 align="left"><b>&nbsp;Eingabe eines neuen Benutzers</b></td>
		</tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2>

			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?php echo $PHP_SELF ?>">
				<tr>
					<td><b>&nbsp;Benutzername:</b></td>
					<td>&nbsp;<input type="text" name="username" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td><b>&nbsp;globaler Status:&nbsp;</b></td>
					<td><? print $perm->perm_sel("perms", $db->f("perms")) ?></td>
				</tr>
				<tr>
					<td><b>&nbsp;Vorname:</b></td>
					<td>&nbsp;<input type="text" name="Vorname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td><b>&nbsp;Nachname:</b></td>
					<td>&nbsp;<input type="text" name="Nachname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td><b>&nbsp;E-Mail:</b></td>
					<td>&nbsp;<input type="text" name="Email" size=48 maxlength=63 value="">&nbsp;</td>
				</tr>
				<td colspan=2 align=center>&nbsp;
				<input type="submit" name="create" value=" Benutzer anlegen ">&nbsp;
				<input type="submit" name="nothing" value=" Abbrechen ">
				&nbsp;</td></tr>
			</form></table>
			
		</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		</table>
		<?

	} else { // alten Benutzer bearbeiten
	
		$db->query("SELECT auth_user_md5.*, changed, mkdate FROM auth_user_md5 LEFT JOIN active_sessions ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) WHERE username ='$details'");
		while ($db->next_record()) {
			if ($db->f("changed") != "") {
				$stamp = mktime(substr($db->f("changed"),8,2),substr($db->f("changed"),10,2),substr($db->f("changed"),12,2),substr($db->f("changed"),4,2),substr($db->f("changed"),6,2),substr($db->f("changed"),0,4));
				$inactive = floor((time() - $stamp) / 3600 / 24)	." Tagen";
			} else {
				$inactive = "nie benutzt";
			}
			?>
			
			<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>
			<tr valign=top align=middl e>
				<td class="topic" colspan=2 align="left"><b>&nbsp;Ver&auml;ndern eines bestehenden Benutzers</b></td>
			</tr>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>
			<tr><td class="blank" colspan=2>
			
			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?php echo $PHP_SELF ?>">
				<tr>
					<td class="steel1"><b>&nbsp;Benutzername:</b></td>
					<td class="steel1">&nbsp;<input type="text" name="username" size=24 maxlength=63 value="<?php $db->p("username") ?>"></td>
				</tr>
				<tr>
					<td class="steel1"><b>&nbsp;globaler Status:&nbsp;</b></td>
					<td class="steel1"><? print $perm->perm_sel("perms", $db->f("perms")) ?></td>
				</tr>
				<tr>
					<td class="steel1"><b>&nbsp;Vorname:</b></td>
					<td class="steel1">&nbsp;<input type="text" name="Vorname" size=24 maxlength=63 value="<?php $db->p("Vorname") ?>"></td>
				</tr>
				<tr>
					<td class="steel1"><b>&nbsp;Nachname:</b></td>
					<td class="steel1">&nbsp;<input type="text" name="Nachname" size=24 maxlength=63 value="<?php $db->p("Nachname") ?>"></td>
				</tr>
				<tr>
					<td class="steel1"><b>&nbsp;E-Mail:</b></td>
					<td class="steel1">&nbsp;<input type="text" name="Email" size=48 maxlength=63 value="<?php $db->p("Email") ?>">&nbsp;</td>
				</tr>
				<tr>
					<td class="steel1"><b>&nbsp;inaktiv seit:</b></td>
					<td class="steel1">&nbsp;<? echo $inactive ?></td>
				</tr>
				<tr>
					<td class="steel1"><b>&nbsp;registriert seit</b></td>
					<td class="steel1">&nbsp;<? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo "unbekannt"; ?></td>
				</tr>
				
				<td class="steel1" colspan=2 align=center>&nbsp;
				<input type="hidden" name="u_id"	 value="<?php $db->p("user_id") ?>">
				<?
				if ($perm->have_perm("root") || 
					($db->f("perms") != "admin" && $db->f("perms") != "root")) {
					?>
					<input type="submit" name="u_edit" value=" Ver&auml;ndern ">&nbsp;
					<input type="submit" name="u_pass" value=" Passwort neu setzen ">&nbsp;
					<input type="submit" name="u_kill" value=" L&ouml;schen ">&nbsp;
					<?
		 		}
				?>
				<input type="submit" name="nothing" value=" Abbrechen ">
				&nbsp;</td></tr>
			</form>
			
			<tr><td colspan=2 class="blank">&nbsp;</td></tr>
			
			<? // links to everywhere
			print "<tr><td class=\"steelgraulight\" colspan=2 align=\"center\">";
				printf("&nbsp;pers&ouml;nliche Homepage <a href=\"about.php?username=%s\"><img src=\"pictures/einst.gif\" border=0 alt=\"Zur pers&ouml;nlichen Homepage des Benutzers\" align=\"texttop\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp", $db->f("username"));
				printf("&nbsp;Nachricht an Benutzer <a href=\"sms.php?cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an den Benutzer verschicken\" border=0 align=\"texttop\"></a>", $db->f("username"));
			print "</td></tr>";
			
			$temp_user_id = $db->f("user_id");
			if ($perm->have_perm("root"))
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_id ='$temp_user_id' AND inst_perms != 'user'");
			else
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst AS x LEFT JOIN user_inst AS y USING (Institut_id) LEFT JOIN Institute USING (Institut_id) WHERE x.user_id ='$temp_user_id' AND x.inst_perms != 'user' AND y.user_id = '$user->id' AND y.inst_perms = 'admin'");
			if ($db2->num_rows()) {
				print "<tr><td class=\"steel2\" colspan=2 align=\"center\">";
				print "<b>&nbsp;Link zur Mitarbeiter-Verwaltung&nbsp;</b>";
				print "</td></tr>\n";
			}
			while ($db2->next_record()) {
				print "<tr><td class=\"steel2\" colspan=2 align=\"center\">";
				printf ("&nbsp;%s <a href=\"inst_admin.php?details=%s&inst=%s\"><img src=\"pictures/admin.gif\" border=0 align=\"texttop\" alt=\"&Auml;ndern der Eintr&auml;ge des Benutzers im jeweiligen Institut\"></a>&nbsp;", htmlReady($db2->f("Name")), $db->f("username"), $db2->f("Institut_id"));
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
		<td class="topic" colspan=2 align="left"><b>&nbsp;Verwaltung aller Benutzer des Systems</b></td>
	</tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>

	<?
	parse_msg($msg);
	?>

	<tr><td class="blank" colspan=2>
	
	<p><b><a href="<? echo $PHP_SELF . "?details="?>">&nbsp;Neuen Benutzer anlegen</a></b></p>

	<?
	include ("pers_browse.inc.php");
	print "<br>\n";
	

	if (isset($pers_browse_search_string)) { // Es wurde eine Suche initiert

		## nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
		if (!isset($sortby) || $sortby=="") {
			if (!isset($new_user_md5_sortby) || $new_user_md5_sortby == "") {
				$new_user_md5_sortby = "username";
			}
		} else {
			$new_user_md5_sortby = $sortby;
			$sess->register("new_user_md5_sortby");
		}

		## Traverse the result set
		$db->query("SELECT auth_user_md5.*, changed, mkdate FROM auth_user_md5 LEFT JOIN active_sessions ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) $pers_browse_search_string ORDER BY $new_user_md5_sortby");

		if ($db->num_rows() == 0) { // kein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 cellpadding=2 width=\"80%\">";
			print "<tr valign=\"top\" align=\"middle\">";
			print "<td>Es wurden keine Benutzer gefunden, auf die die obigen Kriterien zutreffen.</td>";
			print "</tr><tr><td class=\"blank\">&nbsp;</td></tr></table>";

		} else { // wir haben ein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 class=blank cellpadding=2 width=\"100%\">";
			print "<tr valign=\"top\" align=\"middle\">";
				if ($db->num_rows() == 1)
			 		print "<td colspan=7>Suchergebnis: Es wurde <b>";
				else
			 		print "<td colspan=7>Suchergebnis: Es wurden <b>";
				print $db->num_rows();
				print "</b> Benutzer gefunden.</td></tr>\n";
			?>
			 <tr valign="top" align="middle">
				<th align="left"><a href="new_user_md5.php?sortby=username">Benutzername</a></th>
				<th align="left"><a href="new_user_md5.php?sortby=perms">Status</a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Vorname">Vorname</a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Nachname">Nachname</a></th>
				<th align="left"><a href="new_user_md5.php?sortby=Email">E-Mail</a></th>
				<th><a href="new_user_md5.php?sortby=changed">inaktiv</a></th>
				<th><a href="new_user_md5.php?sortby=mkdate">registriert seit</a></th>				
			 </tr>
			<?	

			while ($db->next_record()):
				if ($db->f("changed") != "") {
					$stamp = mktime(substr($db->f("changed"),8,2),substr($db->f("changed"),10,2),substr($db->f("changed"),12,2),substr($db->f("changed"),4,2),substr($db->f("changed"),6,2),substr($db->f("changed"),0,4));
					$inactive = floor((time() - $stamp) / 3600 / 24);
				} else {
					$inactive = "nie benutzt";
				}
				?>
				<tr valign=middle align=left>
					<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><a href="<?php echo $PHP_SELF . "?details=" . $db->f("username") ?>"><?php $db->p("username") ?></a></td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("perms") ?></td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("Vorname") ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("Nachname") ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?php $db->p("Email")?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><?php echo $inactive ?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo "unbekannt"; ?></td>
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
<!-- $Id$ -->
