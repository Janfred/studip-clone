<?php
/*
newsletter.inc.php - Funktionen fuer den Newsletter
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

function CheckPersonInNewsletter ($username, $newsletter_id)    // Ist jemand aufgrund der SQL-Clause im Versand?
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT * ".
		"FROM auth_user_md5 ".
		$newsletter[$newsletter_id]["SQL"].
		" AND user_id = '$user_id'";
	$db->query($query); 
	if ($db->next_record()) {
		$status = "letter";
	} else {
		$status = FALSE;
	}
	return $status;
}

function CheckPersonNewsletter ($username, $newsletter_id)    // Ist jemand in der Ausnahmeliste?
{ global $newsletter;
	$user_id = get_userid($username);
	$db=new DB_Seminar;
	$query = "SELECT status FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'";
	$db->query($query); 
	if ($db->next_record()) {
		if ($db->f("status") == 1) {
			$status = "added";
		} else {
			 $status = "removed";
		}
	} else {
		$status = FALSE;
	}
	return $status;
}

function AddPersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Positivliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "removed") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg§Der Nutzer $username wurde wieder in den Newsletter aufgenommen.§";
	} elseif (CheckPersonInNewsletter($username, $newsletter_id) != "letter" AND $status != "added")  {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '1', newsletter_id = '$newsletter_id'");
		$msg = "msg§Der Nutzer $username wurde in den Newsletter aufgenommen.§";
	}
	return $msg;
}

function RemovePersonNewsletter ($username, $newsletter_id)    // Funktion, mit der man Personen auf die Negativliste setzt
{ global $newsletter;
	$db=new DB_Seminar;
	$user_id = get_userid($username);
	$status = CheckPersonNewsletter ($username, $newsletter_id);
	if ($status == "added") {
		$db->query("DELETE FROM newsletter WHERE user_id = '$user_id' AND newsletter_id = '$newsletter_id'"); 
		$msg = "msg§Der Nutzer $username wurde wieder aus dem Newsletter gel&ouml;scht.§";
	} elseif ($status != "removed") {
		$db->query("INSERT INTO newsletter SET user_id = '$user_id', status = '0', newsletter_id = '$newsletter_id'");
		$msg = "msg§Der Nutzer $username wurde aus dem Newsletter gel&ouml;scht.§";
	}
	return $msg;
}

// Newsletter arrays

	// Standard

	$newsletter[0]["name"] = "Stud.IP Newsletter";
	$newsletter[0]["SQL"] = "WHERE username = 'rstockm'";
//		$newsletter[0]["SQL"] = "WHERE perms != 'user' AND perms != 'autor'";
	$newsletter[0]["text"] = "Hallo dies ist ein Text";

	// weitere

	$newsletter[1]["name"] = "Stud.IP Admin-Newsletter";
	$newsletter[1]["SQL"] = "WHERE perms = 'admin'";
	$newsletter[1]["text"] = "Hallo dies ist ein noch ein Text";



?>