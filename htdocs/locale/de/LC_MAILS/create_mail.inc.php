<?
		$subject="Anmeldung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Sie wurden um $Zeit mit folgenden Angaben von einem\n"
		."der Administratoren ins System eingetragen:\n\n"
		."Benutzername: $username\n"
		."Passwort: $password\n"
		."Status: $permlist\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."E-Mail-Adresse: $Email\n\n"
		."Diese Mail wurde Ihnen zugesandt um Ihnen den Benutzernamen\n"
		."und das Passwort mitzuteilen, mit dem Sie sich am System anmelden.\n\n"
		."Sie finden die Startseite des Systems unter folgender URL:\n\n"
		."$url\n\n"
		."Wahrscheinlich unterstützt ihr Mail-Programm ein einfaches Anklicken des Links.\n"
		."Ansonsten müssen sie Ihren Browser öffnen und den Link komplett in die Zeile\n"
		."\"Location\" oder \"URL\" kopieren.\n\n"
		."Um Zugang auf die nichtöffentlichen Bereiche des Systems zu bekommen\n"
		."müssen Sie sich unter \"Login\" oben rechts auf der Seite anmelden.\n"
		."Geben Sie bitte unter Benutzername \"$username\" und unter\n"
		."Passwort \"$password\" ein.\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an einen Administrator), damit nicht Dritte in ihrem\n"
		."Namen Nachrichten in das System einstellen können!\n\n";

?>
