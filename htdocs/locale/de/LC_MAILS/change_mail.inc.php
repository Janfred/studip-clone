<?
		$subject="Account-Änderung Stud.IP-System";
		
		$mailbody="Dies ist eine Informationsmail des Stud.IP-Systems\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Ihr Account wurde um $Zeit von einer Administratorin oder einem\n"
		."Administrator verändert.\n"
		."Die aktuellen Angaben lauten:\n\n"
		."Benutzername: $username\n"
		."Status: $permlist\n"
		."Vorname: $Vorname\n"
		."Nachname: $Nachname\n"
		."E-Mail-Adresse: $Email\n\n"
		."Ihr Passwort hat sich nicht verändert.\n\n"
		."Diese Mail wurde Ihnen zugesandt, um Sie über die Änderungen zu informieren.\n\n"
		."Wenn Sie Einwände gegen die Änderungen haben, wenden Sie sich bitte an\n"
		."$smtp->abuse. Sie können einfach auf diese Mail antworten.\n\n"
		."Hier kommen Sie direkt ins System:\n"
		."$url\n\n";

?>
