<?
		$subject="Passwort-Änderung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Ihr Passwort wurde um $Zeit von einem der Administrierenden neu gesetzt.\n"
		."Die aktuellen Angaben lauten:\n\n"
		."Benutzername: $username\n"
		."Passwort: $password\n"
		."Status: $permlist\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."E-Mail-Adresse: $Email\n\n"
		."Das Passwort ist nur Ihnen bekannt. Bitte geben Sie es an niemanden\n"
		."weiter (auch nicht an eine Administratorin oder einen Administrator),\n"
		."damit nicht Dritte in Ihrem Namen Nachrichten\n"
		."in das System einstellen können!\n\n"
		."Hier kommen Sie direkt ins System:\n"
		."$url\n\n";

?>
