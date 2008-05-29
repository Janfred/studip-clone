<?php
# Lifter002: TODO
/**
* ILIAS-User authentification.
* 
* This file opens an ILIAS-Session and redirects to an ILIAS-page in order to view or edit a learning-module.
* It must be copied to the ILIAS-directory!
* 
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		studip2ilias
* @package		ELearning
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// studip2ilias.php
//
// Copyright (c) 2003 Arne Schroeder <schroeder@data-quest.de> 
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
//$Id$

// Includes fuer Datenbankverbindung
	$__virtus_inst = 1;
	$USER_ENV = array(
			"id"         	=> $u_id);

include_once("./include/ilias_db.inc");
include_once("./include/db_session_handler.inc");
include_once("./include/errors.inc");
include_once("./include/layout.inc");
include_once("./include/gruppe.inc");
include_once("./include/util.inc");
include_once("./include/user.inc");
include_once("./include/rechte.inc");
include_once("./include/class.mysql.inc");
include_once("./include/mail.inc");/**/

// Verbindung mit der ILIAS-Datenbank fuer Session-Handling
$db           = new DB_Sql;
$db->Host     = $__virtus_dbhost;
$db->Database = $__virtus_dbname;
$db->User     = $__virtus_dbuser;
$db->Password = $__virtus_dbpasswd;
$dbh =	mysql_pconnect($__virtus_dbhost,$__virtus_dbuser,$__virtus_dbpasswd)
			or die("Error: unable to connect to SQL server.");
mysql_select_db($__virtus_dbname)
			or die("Error: database could not be opened.");
if(!((boolean) ini_get("safe_mode")))
	ini_set("session.save_handler", "user");
if(ini_get("session.save_handler") == "user")
	{
		session_set_save_handler(
			"db_session_open",
			"db_session_close",
			"db_session_read",
			"db_session_write",
			"db_session_destroy",
			"db_session_gc");
	}

// Session anlegen
session_save_path("/tmp"); // location for session data
ini_alter(session.gc_maxlifetime,"3600");
session_cache_limiter("");
session_name("SID");
@session_start();
if (strlen(session_id()) != 32)
	{
		mt_srand ((double)microtime()*1000000);
		session_id(md5(uniqid(mt_rand())));
	}

// Passwort Ueberpruefen:
if (md5(get_user_password($u_id)) != $u_pw)
{	
	die("BenutzerInnenname oder Passwort ist nicht korrekt!");
}

// Setzen der User-Variable
$db->query("SELECT vorname, nachname, anrede, email, zustimmung, inst, lang, last_login FROM benutzer WHERE id='$u_id'");
if ($db->next_record())
{
	$__virtus_inst = $db->f("inst");
	$USER_ENV = array(
			"id"         	=> $u_id,
			"benutzername"  => $acct_name,
			"vorname"    	=> $db->f("vorname"),
			"nachname"   	=> $db->f("nachname"),
			"anrede"     	=> $db->f("anrede"),
			"email"      	=> $db->f("email"),
			"zustimmung" 	=> $db->f("zustimmung"),
			"inst"       		=> $db->f("inst"),
			"lang"       	=> $set_lang,
			"ibo_kat"    	=> "",
			"last_login"	=> "",
			"navLocations" => "",
			"navLocLevel" => "-1",
			"system" => rgt_get_sys_grp($u_id) 
			);/**/
}
session_register("USER_ENV");

// Welche ILIAS-Seite ist das Ziel?
$error = false;
switch($rdmode)
{
	case "use": 
		$rd_string = "./le_uebersicht.php?act=auto_enter&le_id=$co_id&le_inst=$co_inst";
	break;
	case "edit": 
		$rd_string = "./ed_gliederung.php?le=$le&le_inst=$le_inst";
	break;
	case "new": 
		$rd_string = "./ed_le.php?cmd=nl";
	break;
	case "delete": 
		$rd_string = "./editor.php?cmd=d&id=$le";
	break;
	default:
		$error = true;
}
// Umleitung auf eine ILIAS-Seite
if (!$error)
{	
	if ($rd_string[0] != "/")
		{	
			$rd_string = substr($_SERVER["SCRIPT_NAME"],
							   0,
							   strrpos($_SERVER["SCRIPT_NAME"],"/")+1
							   )
						.$rd_string;
		}
	$port	 = !preg_match( "/^(80|443)$/",
							$_SERVER["SERVER_PORT"],
							$portMatch)
			   ? ":".$_SERVER["SERVER_PORT"]
			   : "";
		$server = $_SERVER["SERVER_NAME"];
	header("Location: "
		   .(($portMatch[1] == 443) ? "https://" : "http://")
		   .$server.$port.$rd_string);
	exit;
}
else
	echo "Ung&uuml;ltiger Seitenaufruf!";
?>
