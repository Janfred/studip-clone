<?
function get_password_md5()
{
	global $auth;
	$db = New DB_Seminar;
	$query_string = "SELECT password FROM auth_user_md5 WHERE username = '" . $auth->auth["uname"] . "'";
	$db->query($query_string);
	if ($db->next_record())
		return md5($db->f("password"));
	else
		return false;
}

function get_studip_user($ilias_id)
{
	global $auth, $username_prefix;
	$db = New DB_Seminar;
//	$query_string = "SELECT studip_user_id FROM studip_ilias WHERE ilias_user_id = '$ilias_id'";
	$query_string = "SELECT auth_user_md5.username FROM studip_ilias, auth_user_md5 WHERE ilias_user_id = '$ilias_id' AND user_id = studip_user_id";
	$db->query($query_string);
	if ($db->next_record())
		return $db->f("username");
	else
		return false;
}

function is_created_user($studip_id)
{
	global $auth, $username_prefix;
	$db = New DB_Seminar;
	$query_string = "SELECT is_created FROM studip_ilias WHERE studip_user_id = '$studip_id'";
	$db->query($query_string);
	if ($db->next_record())
		return $db->f("is_created");
	else
		return false;
}

function get_connected_user_id($studip_id)
{
	global $auth, $username_prefix;
	$db = New DB_Seminar;
	$query_string = "SELECT ilias_user_id FROM studip_ilias WHERE studip_user_id = '$studip_id'";
	$db->query($query_string);
	if ($db->next_record())
		return $db->f("ilias_user_id");
	else
		return false;
}

function get_ilias_user_id($benutzername)
{
	$ilias_db = New DB_Ilias;
	$ilias_db->query("SELECT id FROM benutzer WHERE benutzername='" . mysql_escape_string($benutzername)."'");
	
	if ($ilias_db->next_record())
		return $ilias_db->f("id");
	else
		return false;
}

function get_ilias_logindata()
{
	global $auth, $username_prefix;
	$ilias_db = New DB_Ilias;
	$ilias_db->query("SELECT * FROM benutzer WHERE id='" . mysql_escape_string(get_connected_user_id($auth->auth["uid"]))."'");
	if ($ilias_db->next_record())
		return "&acct_name=" . $ilias_db->f("benutzername") . "&u_id=" . $ilias_db->f("id") . "&u_pw=" . md5($ilias_db->f("passwort")) . "&set_lang=en";
	else
		return false;
}

function new_ilias_user($benutzername, $passwort, $geschlecht, $vorname, $nachname, $title_front, $institution, $telefon, $email, $status, $preferred_language)
{
	global $ilias_status, $ilias_systemgroup, $username_prefix;
// Check, ob username schon vorhanden
	$ilias_db = New DB_Ilias;
	$ilias_db->query("SELECT benutzername FROM benutzer WHERE benutzername='". $username_prefix . mysql_escape_string($benutzername) ."'");
	if ($ilias_db->next_record())
	{
		return sprintf(_("Der ILIAS-User '%s' existiert bereits!<br>"), $ilias_db->f("benutzername"));
	}
	else
	{	
// Konvertierung der Daten
		$strasse = "-";
		$plz = "-";
		$ort = "-";
		$lang_arr = explode("_", $preferred_language);
		$u_lang = $lang_arr[0];
		$land = $lang_arr[1];
		if ($inst == "")
			$inst = "1";
		if ($geschlecht == 0)
			$anrede = "Herr";
		else	
			$anrede = "Frau";
		$passwort = (crypt($passwort,substr($passwort,0,2)));
		$atitel = $title_front;
		$u_status = $ilias_status[$status];
	 
// Datenbankzugriff: BENUTZER
		$query_string = "INSERT INTO benutzer (ctime,benutzername,passwort,anrede,vorname,nachname,atitel,institution,strasse, plz, ort, land,telefon,email,inst,status,zustimmung,ibo_kat,lang) ".
			"VALUES (now(),'". $username_prefix . mysql_escape_string($benutzername)."','"
			.mysql_escape_string($passwort)."','"
			.mysql_escape_string($anrede)."','"
			.mysql_escape_string($vorname)."','"
			.mysql_escape_string($nachname)."','"
			.mysql_escape_string($atitel)."','"
			.mysql_escape_string($institution)."','"
			.mysql_escape_string($strasse)."','"
			.mysql_escape_string($plz)."','"
			.mysql_escape_string($ort)."','"
			.mysql_escape_string($land)."','"
			.mysql_escape_string($telefon)."','"
			.mysql_escape_string($email)."','"
			.mysql_escape_string($inst)."','"
			.mysql_escape_string($u_status)."','"
			."J',"
			."0,'"
			.mysql_escape_string($u_lang) . "')";
//			."',$__virtus_inst,'"
//			.mysql_escape_string($ref_person)."')";

		$ilias_db->query($query_string);
//		echo $query_string . "<br>";
	
		$query_string = "SELECT id FROM benutzer WHERE benutzername = '" . $username_prefix . $benutzername . "' AND vorname = '$vorname' AND nachname = '$nachname' AND email = '$email'";
		$ilias_db->query($query_string);
		if ($ilias_db->next_record())
			$u_id = $ilias_db->f("id");
		else
			die(_("Datenbankoperation konnte nicht ausgeführt werden!"));

// Datenbankzugriff: OBJECT2
		$query_string = "INSERT INTO object2 (own_id, own_typ, own_inst,vri_id,vri_typ,vri_inst,recht,start,end,deleted) "
			."VALUES ('". $ilias_systemgroup[$status]."', 'grp', 1, '" . $u_id . "','user', '1', 132, '0000-00-00', '0000-00-00', '0000-00-00 00:00:00')";
		$ilias_db->query($query_string);
//		echo $query_string . "<br>";
	}
	return true;
}

function create_ilias_user($studip_id)
{
	global $auth, $username_prefix;
	$creation_result = false;
	$db = new DB_Seminar;
	$query_string = "SELECT * FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = '". $studip_id . "'";
	$db->query($query_string);
	if ($db->next_record())
	{
		$creation_result = new_ilias_user($db->f("username"), md5($db->f("password")), $db->f("geschlecht"), 
			$db->f("Vorname"), $db->f("Nachname"), $db->f("title_front"), 
			"Stud.IP", $db->f("privatnr"), $db->f("Email"), 
			$db->f("perms"), $db->f("preferred_language"));

		if ($creation_result === true)
		{
			connect_users($studip_id, get_ilias_user_id($username_prefix . $db->f("username")), 1);
			return true;
		}
	}
	return $creation_result;
}
/*
function create_studip_user($benutzername)
{
	global $auth;
	$db = new DB_Ilias;
	$query_string = "SELECT * FROM benutzer WHERE benutzername = '". $benutzername . "'";
	$db->query($query_string);
	if ($db->next_record())
	{
		connect_users($benutzername, $db->f("benutzername"));
		return true;
	}
	return false;
}
/**/
function connect_users($studip_id, $ilias_id, $is_created = 0)
{
	$db = new DB_Seminar;
	$query_string = "SELECT * FROM studip_ilias WHERE studip_user_id = '$studip_id'";
	$db->query($query_string);
	if ($db->next_record())
		$query_string = "UPDATE studip_ilias SET studip_user_id = '$studip_id', ilias_user_id = '$ilias_id', is_created = '$is_created' WHERE studip_user_id = '$studip_id'";
	else
		$query_string = "INSERT INTO studip_ilias (studip_user_id, ilias_user_id, is_created) VALUES ('$studip_id', '$ilias_id', '$is_created')";
	$db->query($query_string);
	return true;
}

function edit_ilias_user ($u_id, $benutzername, $geschlecht, $vorname, $nachname, $title_front, $institution, /*$telefon,*/ $email, $status, $preferred_language)
{
	global $ilias_status, $ilias_systemgroup, $username_prefix;
	$ilias_db = New DB_Ilias;

// Konvertierung der Daten
	$strasse = "-";
	$plz = "-";
	$ort = "-";
	$lang_arr = explode("_", $preferred_language);
	$u_lang = $lang_arr[0];
	$land = $lang_arr[1];
	if ($inst == "")
		$inst = "1";
	if ($geschlecht == 0)
		$anrede = "Herr";
	else	
		$anrede = "Frau";
	$passwort = (crypt($passwort,substr($passwort,0,2)));
	$atitel = $title_front;
	$u_status = $ilias_status[$status];
	if ($preferred_language == "")
		$u_lang = "en";

// Datenbankzugriff: BENUTZER
	$query_string = "UPDATE benutzer ".
			"SET benutzername='" . $benutzername . "',".
//				"passwort='$passwort',".
				"anrede='$anrede',".
				"vorname='$vorname',".
				"nachname='$nachname',".
				"atitel='$atitel',".
				"institution='$institution',".
				"strasse='$strasse',".
				"plz='$plz',".
				"ort='$ort',".
				"land='$land',".
				"telefon='$telefon',".
				"email='$email',".
				"status='$u_status',".
				"lang='$u_lang' ".
			"WHERE id=$u_id";
	$ilias_db->query($query_string);
//	echo $query_string . "<br>";

// Datenbankzugriff: OBJECT2
	$old_own_id = 0;
	$query_string = "SELECT own_id FROM object2 "
		."WHERE vri_id=$u_id AND vri_typ='user' AND vri_inst=1";
	$ilias_db->query($query_string);
	while ($ilias_db->next_record())
		if ($ilias_db->f("own_id") < 5)
			$old_own_id = $ilias_db->f("own_id");
	if ($old_own_id != "0") 
	{
		$query_string = "UPDATE object2 "
			."SET own_id='" . $ilias_systemgroup[$status] . "' "
			."WHERE own_id=$old_own_id vri_id=$u_id AND vri_typ='user' AND vri_inst=1";
		$ilias_db->query($query_string);
	}
	else 
		return false;
//	echo $query_string . "<br>";
}

function delete_ilias_user($ilias_id)
{
	if (get_studip_user($ilias_id) == false)
	{
		echo _("User wurde nicht gefunden.") . "<br>";
		return false;
	}
	else
	{
		$ilias_db = New DB_Ilias;

// Datenbankzugriff: OBJECT2
		$query_string = "UPDATE object2 SET deleted=now(), recht=1 WHERE vri_id=$ilias_id AND vri_typ='user' AND vri_inst=1 AND own_typ='grp'";
		$ilias_db->query($query_string);

// Datenbankzugriff: BENUTZER
		$query_string = "DELETE FROM benutzer WHERE id=$ilias_id";
		$ilias_db->query($query_string);
	}
	return true;
}

?>