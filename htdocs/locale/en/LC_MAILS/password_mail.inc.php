<?
		$subject="Password modification in the Stud.IP-System";
		
		$mailbody="This is a Stud.IP system information mail\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Your password was changed by an administrator at $Zeit.\n"
		."The current information is:\n\n"
		."Username: $username\n"
		."Password: $password\n"
		."Status: $permlist\n"
		."Forename: $Vorname\n"
		."Surname: $Nachname\n"
		."E-mail address: $Email\n\n"
		."The password is only known to you. Please do not pass it onto anyone\n"
		."else(not even an administrator). This is to stop\n"
		."third parties from posting messages\n"
		."in the system under your name!\n\n"
		."Here takes you directly into the system:\n"
		."$url\n\n";

?>
