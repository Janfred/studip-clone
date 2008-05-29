<?
# Lifter002: TODO
/**
* User-Functions for ILIAS-Connection.
* 
* In this file there are functions to edit, create and delete ILIAS-Useraccounts 
* and to connect them with Stud.IP-Accounts.
* 
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		lernmodul_user_functions
* @package		ELearning
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// lernmodul_user_functions.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

/**
* Gets ILIAS-Inst-ID
*
* This function gets the ILIAS-Installation-ID from the ILIAS-db.
*
* @access	public        
* @return		integer	returns Inst_id or false
*/
function get_ilias_inst_id()
{
	$ilias_db = New DB_Ilias;
	$ilias_db->query("SELECT inst_id FROM cust");
	
	if ($ilias_db->next_record())
		return $ilias_db->f("inst_id");
	else
		return false;
}

/**
* Gets MD5-Hash of the Stud.IP-password of the current user
*
* This function gets the Stud.IP-password of the current user and creates the md5-Hash of it.
*
* @access	public        
* @return		string	returns MD5 or false
*/
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

/**
* Gets the Stud.IP-username connected to the given ILIAS-User-ID
*
* This function gets the Stud.IP-username connected to the given ILIAS-User-ID.
*
* @access	public        
* @param		integer	$ilias_id	Ilias User ID
* @return		string	returns username or false
*/
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

/**
* Checks if user was automatically created
*
* This function checks if the ILIAS-user linked to the given Stud.IP-user-ID was automatically created.
*
* @access	public        
* @param		string	StudIP User ID
* @return		boolean
*/
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

/**
* Gets connected User-ID
*
* This function gets the ILIAS-user-ID linked to the given Stud.IP-user-ID.
*
* @access	public        
* @param		string	$studip_id	StudIP User ID
* @return		string	returns ID or false
*/
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

/**
* Gets ILIAS-User-ID
*
* This function gets the ILIAS-user-ID that belongs to the given ILIAS-Username.
*
* @access	public        
* @param		string	$benutzername	Ilias Username
* @return		integer	returns ID or false
*/
function get_ilias_user_id($benutzername)
{
	$ilias_db = New DB_Ilias;
	$ilias_db->query("SELECT id FROM benutzer WHERE benutzername='" . mysql_escape_string($benutzername)."'");
	
	if ($ilias_db->next_record())
		return $ilias_db->f("id");
	else
		return false;
}

/**
* Gets ILIAS-Login-data for user authentification
*
* This function gets the ILIAS-Login-data that belongs to the current Stud.IP-User. It returns an string that can be added to the studip2ilias.php-Call.
*
* @access	public        
* @return		string	returns string or false
*/
function get_ilias_logindata()
{
	global $auth, $username_prefix;
	$db = New DB_Seminar;
	$db->query("SELECT preferred_language FROM user_info WHERE user_id='" . $auth->auth["uid"] . "'");
	if ($db->next_record()) 
		$preferred_language = $db->f("preferred_language");
	if ($preferred_language != "")
	{
		$language = explode("_", $preferred_language);
		$language = $language[0];
	}
	else
		$language = "de";
	$ilias_db = New DB_Ilias;
	$ilias_db->query("SELECT * FROM benutzer WHERE id='" . mysql_escape_string(get_connected_user_id($auth->auth["uid"]))."'");
	if ($ilias_db->next_record())
		return "&acct_name=" . $ilias_db->f("benutzername") . "&u_id=" . $ilias_db->f("id") . "&u_pw=" . md5($ilias_db->f("passwort")) . "&set_lang=" . $language;
	else
		return false;
}

/**
* Creates new ILIAS-user-account with the given values
*
* This function creates new ILIAS-user-account with the given values in case it doesn't exist already.
*
* @access	public
* @param		string	$benutzername	Ilias Username
* @param		string	$passwort	Ilias password
* @param		integer	$geschlecht	sex
* @param		string	$vorname		first name
* @param		string	$nachname	name
* @param		string	$title_front	title
* @param		string	$institution	institution
* @param		string	$telefon		phone number
* @param		string	$email		email
* @param		string	$status		status
* @param		string	$preferred_language	language
* @return		string	returns error-string or true
*/
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
		if ($preferred_language == "")
			$u_lang = "de";
		if ($geschlecht == 0)
			$anrede = "Herr";
		else	
			$anrede = "Frau";
		$passwort = (crypt($passwort,substr($passwort,0,2)));
		$atitel = $title_front;
		$u_status = $ilias_status[$status];

	 	$inst_id = get_ilias_inst_id();
	 
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
			.mysql_escape_string($inst_id)."','"
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
			."VALUES ('". $ilias_systemgroup[$status]."', 'grp', '" . $inst_id . "', '" . $u_id . "','user', '" . $inst_id . "', 132, '0000-00-00', '0000-00-00', '0000-00-00 00:00:00')";
		$ilias_db->query($query_string);
//		echo $query_string . "<br>";
	}
	return true;
}

/**
* Creates new ILIAS-user-account connected to the given Stud.IP-User-ID
*
* This function creates a new ILIAS-user-account connected to the given Stud.IP-User-ID. User-Data for the account is equal to the date in Stud.IP-Database.
*
* @access	public        
* @param		string	$studip_id	StudIP User ID
* @return		string	returns error-string or true
*/
function create_ilias_user($studip_id)
{
	global $auth, $username_prefix;
	$creation_result = false;
	$db = new DB_Seminar;
	$query_string = "SELECT * FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = '". $studip_id . "'";
	$db->query($query_string);
	if ($db->next_record())
	{
		$creation_result = new_ilias_user($db->f("username"), md5(uniqid("uehvt3td",1)), $db->f("geschlecht"), 
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

/**
* Connects ILIAS-user-account with Stud.IP-User-Account
*
* This function connects an existing ILIAS-user-account with an existing Stud.IP-User-Account. If there isn't already an entry in table 'studip_ilias' the field 'is_created' will be set to 1.
*
* @access	public        
* @param		string	StudIP User ID
* @param		integer	Ilias User ID
* @param		integer	
* @return		string	returns error-string or true
*/
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

/**
* Updates an existing ILIAS-user-account with the given values
*
* This function updates an existing ILIAS-user-account with the given values and sets group rights for it.
*
* @access	public
* @param		string	$u_id		Ilias User ID
* @param		string	$benutzername	Ilias Username
* @param		integer	$geschlecht	sex
* @param		string	$vorname		first name
* @param		string	$nachname	name
* @param		string	$title_front	title
* @param		string	$institution	institution
* @param		string	$email		email
* @param		string	$status		status
* @param		string	$preferred_language	language
* @return		boolean	returns true or false
*/
function edit_ilias_user ($u_id, $benutzername, $geschlecht, $vorname, $nachname, $title_front, $institution, $email, $status, $preferred_language)
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
		$u_lang = "de";

	$inst_id = get_ilias_inst_id();
	 
/*
	$query_string = "SELECT * FROM benutzer ".
			"WHERE benutzername='" . $benutzername . "' AND ".
				"anrede='$anrede' AND ".
				"vorname='$vorname' AND ".
				"nachname='$nachname' AND ".
				"atitel='$atitel' AND ".
				"institution='$institution' AND ".
				"strasse='$strasse' AND ".
				"plz='$plz' AND ".
				"ort='$ort' AND ".
				"land='$land' AND ".
				"telefon='$telefon' AND ".
				"email='$email' AND ".
				"status='$u_status' AND ".
				"lang='$u_lang' AND ".
				"id=$u_id";
	$ilias_db->query($query_string);
	if ($ilias_db->next_record())
	{
		echo "DUBLICATE ENTRY WARNING!!!!!!!!!!!";
		return false;
	}
*/
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
		."WHERE vri_id=$u_id AND vri_typ='user' AND vri_inst=" . $inst_id . " AND deleted='0000-00-00 00:00:00'";
	$ilias_db->query($query_string);
	while ($ilias_db->next_record())
		if ($ilias_db->f("own_id") < 5)
			$old_own_id = $ilias_db->f("own_id");
	if (($old_own_id != "0") AND ($old_own_id != $ilias_systemgroup[$status]) )
	{
		$query_string = "UPDATE object2 "
			."SET own_id='" . $ilias_systemgroup[$status] . "' "
			."WHERE own_id=$old_own_id AND vri_id=$u_id AND vri_typ='user' AND vri_inst=" . $inst_id;
		$ilias_db->query($query_string);
	}
	else 
		return false;
//	echo $query_string . "<br>";
}

/**
* Deletes ILIAS-user-account
*
* This function deletes the ILIAS-user-account connected to the given ILIAS-User-ID.
*
* @access	public        
* @param		string	$ilias_id	Ilias User ID
* @return		boolean	returns true
*/
function delete_ilias_user($ilias_id)
{
/*	if (get_studip_user($ilias_id) == false)
	{
		echo _("User wurde nicht gefunden.") . "<br>";
		return false;
	}
	else/**/
	{

	 	$inst_id = get_ilias_inst_id();
	 
		$ilias_db = New DB_Ilias;

// Datenbankzugriff: OBJECT2
		$query_string = "UPDATE object2 SET deleted=now(), recht=1 WHERE vri_id=$ilias_id AND vri_typ='user' AND vri_inst=" . $inst_id . " AND own_typ='grp'";
		$ilias_db->query($query_string);

// Datenbankzugriff: BENUTZER
		$query_string = "DELETE FROM benutzer WHERE id=$ilias_id";
		$ilias_db->query($query_string);
	}
	return true;
}

?>
