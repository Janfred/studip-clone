<?
		$subject="Passwort-Änderung Stud.IP";

		$mailbody="Dies ist eine Informationsmail des Systems\n"
		."\"Studienbegleitender Internetsupport Präsenzlehre\"\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Ihr Passwort wurde um $Zeit neu gesetzt,\n"
		."da Sie Ihre Email Addresse verändert haben!\n"
		."Die aktuellen Angaben lauten:\n\n"
		."Benutzername: $new_username\n"
		."Passwort: $newpass\n"
		."Status: ".$this->auth_user["perms"]."\n"
		."Vorname: $vorname\n"
		."Nachname: $nachname\n"
		."Email-Adresse: $email\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an einen Administrator), damit nicht Dritte in Ihrem\n"
		."Namen Nachrichten in das System einstellen können!\n\n"
		."Hier kommen Sie direkt ins System:\n"
		."$url\n\n";

?>
