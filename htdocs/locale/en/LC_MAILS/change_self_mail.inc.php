<?
		$subject="Change of password in Stud.IP";

		$mailbody="This is a Stud.IP system information mail\n"
		."(Studienbegleitender Internetsupport von Präsenzlehre)\n"
		."- $UNI_NAME_CLEAN -\n\n"
		."Your password was changed at $Zeit,\n"
		."because you changed your E-mail address!\n"
		."The current information is:\n\n"
		."Username: $new_username\n"
		."Password: $newpass\n"
		."Status: ".$this->auth_user["perms"]."\n"
		."Forename: $vorname\n"
		."Surname: $nachname\n"
		."E-mail address: $email\n\n"
		."The password is only known to you. Please do not pass it onto\n"
		."anyone else(not even an administrator). This is to stop third parties\n"
		."from posting messages in the system under your name!\n\n"
		."Here takes you directly into the system:\n"
		."$url\n\n";

?>
