<?
		$subject="Stud.IP system account modification";
		
		$mailbody="This is a Stud.IP system information mail\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Your account was modified by an administrator at $Zeit\n"
		."The current information is:\n\n"
		."Username: $username\n"
		."Status: $permlist\n"
		."Forename: $Vorname\n"
		."Surname: $Nachname\n"
		."E-mail-address: $Email\n\n"
		."Your password has not been changed.\n\n"
		."This mail has been sent to you, to inform you of the changes.\n\n"
		."If you have objections against these changes, please contact\n"
		."$smtp->abuse. You can simply reply to this mail.\n\n"
		."Here takes you directly into the system:\n"
		."$url\n\n";

?>
