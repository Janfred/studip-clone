<?
		$subject="Bestätigungsmail des Stud.IP-Systems";
		
		$mailbody="Dies ist eine Bestätigungsmail des Systems\n"
		."\"Studienbegleitender Internetsupport Präsenzlehre\"\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Sie haben sich um $Zeit mit folgenden Angaben angemeldet:\n\n"
		."Benutzername: $username\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."Email-Adresse: $Email\n\n"
		."Diese Mail wurde Ihnen zugesandt um sicherzustellen,\n"
		."daß die angegebene Email-Adresse tatsächlich Ihnen gehört.\n\n"
		."Wenn diese Angaben korrekt sind, dann öffnen Sie bitte den Link\n\n"
		."$url\n\n"
		."in Ihrem Browser.\n"
		."Möglicherweise unterstützt ihr Mail-Programm ein einfaches Anklicken des Links.\n"
		."Ansonsten müssen sie Ihren Browser öffnen und den Link komplett in die Zeile\n"
		."\"Location\" oder \"URL\" kopieren.\n\n"
		."Sie müssen sich auf jeden Fall als Benutzer \"$username\" anmelden,\n"
		."damit die Rückbestätigung funktioniert.\n\n"
		."Falls Sie sich nicht als Benutzer \"$username\" angemeldet haben\n"
		."oder überhaupt nicht wissen, wovon hier die Rede ist,\n"
		."dann hat jemand Ihre Email-Adresse missbraucht!\n\n"
		."Bitte wenden Sie sich in diesem Fall an $smtp->abuse,\n"
		."damit der Eintrag aus der Datenbank gelöscht wird.\n";
?>
