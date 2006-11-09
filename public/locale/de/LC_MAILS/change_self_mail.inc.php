<?
		$subject="Passwort-Änderung Stud.IP";

		$mailbody="Dies ist eine Informationsmail des Systems Stud.IP\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Ihr Passwort wurde um $Zeit neu gesetzt,\n"
		."da Sie Ihre E-Mail-Addresse verändert haben!\n"
		."Die aktuellen Angaben lauten:\n\n"
		."Benutzername: $new_username\n"
		."Passwort: $newpass\n"
		."Status: ".$this->auth_user["perms"]."\n"
		."Vorname: $vorname\n"
		."Nachname: $nachname\n"
		."E-Mail-Adresse: $email\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an eine/n AdministratorIn), damit Dritte nicht in Ihrem\n"
		."Namen Nachrichten in das System einstellen können!\n\n"
		."Hier kommen Sie direkt ins System:\n"
		."$url\n\n";

?>
