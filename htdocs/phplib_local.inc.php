<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// phplib_local.inc.php
// This file contains several phplib classes extended for use with Stud.IP
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de> 
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

//Compatibility for PHP Version < 4.1.0
// 3/18/2002 - Tim Gallagher<timg@sunflowerroad.com>
// if $_REQUEST isn't set, we will set it based on $HTTP_GET_VARS AND $HTTP_POST_VARS
// however, we should still global these variables in the functions to keep backward
// compatability from breaking.

if ( (! isset($_REQUEST)) && (! isset($_GET)) ) {
	// swap the foreach loops to change the order of variable registration
	// in other words you can change GET then POST to POST then GET
	// where the second set of variables overrides the first.

	foreach ($HTTP_GET_VARS as $key => $value) {
		$_GET[$key] = $value;
		$_REQUEST[$key] =& $_GET[$key];
	} // end foreach loop

	foreach ($HTTP_POST_VARS as $key => $value) {
		$_POST[$key] = $value;
		$_REQUEST[$key] =& $_POST[$key];
	} // end foreach loop
} // end if

//bugfix ?
reset($HTTP_POST_VARS);
reset($HTTP_GET_VARS);

if (strstr( PHP_OS,"WIN")) 									//DON'T TOUCH: disable the chat for windows installations
	$CHAT_ENABLE=FALSE;

	
/*classes for database access
----------------------------------------------------------------
please note: Stud.IP uses the class DB_Seminar*/

// default Stud.IP database class
class DB_Seminar extends DB_Sql {
	function DB_Seminar($query = false){
		$this->Host = $GLOBALS['DB_STUDIP_HOST'];
		$this->Database = $GLOBALS['DB_STUDIP_DATABASE'];
		$this->User = $GLOBALS['DB_STUDIP_USER'];
		$this->Password = $GLOBALS['DB_STUDIP_PASSWORD'];
		if ($query){
			$this->query($query);
		}
	}
}

//additional class, for your own purpose!
class DB_Institut extends DB_Sql {
	function DB_Institut($query = false){
		$this->Host = $GLOBALS['DB_INSTITUT_HOST'];
		$this->Database = $GLOBALS['DB_INSTITUT_DATABASE'];
		$this->User = $GLOBALS['DB_INSTITUT_USER'];
		$this->Password = $GLOBALS['DB_INSTITUT_PASSWORD'];
		if ($query){
			$this->query($query);
		}
	}
}

// Vollzugriff auf eine ILIAS-Installation
class DB_Ilias extends DB_Sql {
	function DB_Ilias($query = false){
		$this->Host = $GLOBALS['DB_ILIAS_HOST'];
		$this->Database = $GLOBALS['DB_ILIAS_DATABASE'];
		$this->User = $GLOBALS['DB_ILIAS_USER'];
		$this->Password = $GLOBALS['DB_ILIAS_PASSWORD'];
		if ($query){
			$this->query($query);
		}
	}
}


/*mail settings
----------------------------------------------------------------*/

class studip_smtp_class extends smtp_class {

	var $from = "";
	var $env_from = "";
	var $abuse = "";
	
	function studip_smtp_class() {
		$this->localhost = ($GLOBALS['MAIL_LOCALHOST'] == "") ? getenv("SERVER_NAME") : $GLOBALS['MAIL_LOCALHOST']; // name of the mail sending machine (the web server)
		$this->host_name = ($GLOBALS['MAIL_HOST_NAME'] == "") ? getenv("SERVER_NAME") : $GLOBALS['MAIL_HOST_NAME']; // which mailserver should we use? (must allow mail-relaying from this->localhost)
		$this->from="\"Stud.IP\" <wwwrun@".$this->localhost.">"; // From: Mailheader
		$this->env_from="wwwrun@".$this->localhost; // Envelope-From:
		$this->abuse="abuse@".$this->localhost; // Reply-To: Mailheader
	}
}


class Seminar_CT_Sql extends CT_Sql {
	var $database_class = "DB_Seminar";	  // Which database to connect...
	var $database_table = "active_sessions"; // and find our session data in this table.
}


class Seminar_Session extends Session {
	var $classname = "Seminar_Session";
	
	var $cookiename     = "Seminar_Session"; // defaults to classname
	var $magic	  = "sdfghjdfdf";      // ID seed
	var $mode	   = "cookie";	  // We propagate session IDs with cookies
	//var $fallback_mode  = "get";
	var $lifetime       = 0;		 // 0 = do session cookies, else minutes
	var $that_class     = "Seminar_CT_Sql"; // name of data storage container
	var $gc_probability = 5;
	var $allowcache = "no";
	
	
	//modifizierte function put_headers(),ermöglicht den Verzicht auf Headers seitens der PHPLib
	function put_headers(){
		if ($GLOBALS["dont_put_headers"]) return;
		//put_headers der SuperKlasse aufrufen
		Session::put_headers();
	}
	
	//erweiterter Garbage Collector
	function gc(){
		srand(time());
		if ((rand()%100) < $this->gc_probability){
			//Alte News, oder News ohne range_id löschen
			$db=new DB_Seminar("SELECT news.news_id FROM news where (date+expire)<UNIX_TIMESTAMP() ");
			while($db->next_record()) {
				$result[$db->Record[0]] = true;
			}
			$db->query("SELECT news_range.news_id FROM news_range LEFT JOIN news using(news_id) WHERE ISNULL(news.news_id)");
			while($db->next_record()) {
				$result[$db->Record[0]] = true;
			}
			if (is_array($result)) {
				$kill_news = "('".join("','",array_keys($result))."')";
				$db->query("DELETE FROM news WHERE news_id IN $kill_news");
				$db->query("DELETE FROM news_range WHERE news_id IN $kill_news");
			}
			
		}
		
		//weiter mit gc() in der Super Klasse
		Session::gc();
	}
}

class Seminar_User extends User {
	var $classname = "Seminar_User";
	
	var $magic	  = "dsfgakdfld";     // ID seed
	var $that_class     = "Seminar_CT_Sql"; // data storage container
}


//
// Seminar_Challenge_Crypt_Auth: Keep passwords in md5 hashes rather
//			   than cleartext in database
// Author: Jim Zajkowski <jim@jimz.com>

class Seminar_Auth extends Auth {
	var $classname      = "Seminar_Auth";
	
	var $lifetime       =  60;
	
	var $magic	  = "Fdfglkdfsg";  // Challenge seed
	var $database_class = "DB_Seminar";
	var $database_table = "auth_user_md5";
	var $error_msg = "";
	
	
	function auth_preauth() {
		global $auto_user,$auto_response,$auto_id,$resolution;
		
		
		if (!$auto_user OR !$auto_response OR !$auto_id) return false;
		
		$aktuell=time();
		$folder=dir("/tmp");
		while ($entry=$folder->read())
		{
			if (!strncmp($entry,"auto_key",8))
			{
				if ($aktuell-filemtime("/tmp/$entry") > 30) unlink("/tmp/$entry");
			}
		}
		$folder->close;
		
		if (file_exists("/tmp/auto_key_$auto_id"))
		{
			$fp=@fopen("/tmp/auto_key_$auto_id","r");
			$auto_challenge=fgets($fp,100);
			fclose($fp);
			unlink("/tmp/auto_key_$auto_id");
		}
		else
		{
			$this->error_msg="Fehler beim Auto-Login!<br>";
			return false;
		}
		$this->auth["uname"]=$auto_user;  // This provides access for "loginform.ihtml"
		
		$this->db->query(sprintf("select user_id,username,perms,password ".
		"from %s where username = '%s'",
		$this->database_table,
		addslashes($auto_user)));
		if (!$this->db->num_rows())
		{
			$this->error_msg="Dieser Username existiert nicht!<br>";
			return false;
		}
		
		while($this->db->next_record()) {
			
			if ($this->db->f("username") != $auto_user) {
				$this->error_msg="Bitte achten Sie auf korrekte Gro&szlig;-Kleinschreibung beim Username!<br>";
				return false;
			}
			
			$uid   = $this->db->f("user_id");
			$perm  = $this->db->f("perms");
			$pass  = $this->db->f("password");   // Password is stored as a md5 hash
		}
		
		if ($perm=="root" || $perm=="admin")
		{
			$this->error_msg="Autologin ist mit dem Status: $perm nicht möglich!";
			return false;
		}
		
		$expected_response="";
		for ($i=0;$i < strlen($auto_response)/2;$i++)
		{
			$s=(256-(ord(substr($auto_challenge,$i,1))-hexdec(substr($auto_response,$i*2,2)))) % 256;
			$expected_response.=chr($s);
		}
		$expected_response = md5($expected_response);
		
		//echo "$auto_user<br>$auto_response<br>$auto_challenge<br>$pass<br>$expected_response<br>";
		//die;
		
		if ($pass != $expected_response) {
			$this->error_msg="Das Passwort ist falsch!<br>";
			return false;
		}
		else {
			$this->auth["perm"] = $perm;
			$this->auth["jscript"] = TRUE;
			$this->auth_set_user_settings($uid);
			return $uid;
		}
		
		
	}
	
	function auth_loginform() {
		global $sess;
		global $challenge;
		global $ABSOLUTE_PATH_STUDIP;
		
		
		$challenge = md5(uniqid($this->magic));
		$sess->register("challenge");
		
		include("$ABSOLUTE_PATH_STUDIP/crcloginform.ihtml");
	}
	
	function auth_validatelogin() {
		global $username, $password, $challenge, $response, $resolution;
		
		$this->auth["uname"]=$username;	// This provides access for "loginform.ihtml"
		
		$this->db->query(sprintf("select user_id,username,perms,password ".
		"from %s where username = '%s'",
		$this->database_table,
		addslashes($username)));
		if (!$this->db->num_rows())
		{
			$this->error_msg="Dieser Username existiert nicht!<br>";
			return false;
		}
		
		while($this->db->next_record()) {
			
			if ($this->db->f("username") != $username) {
				$this->error_msg="Bitte achten Sie auf korrekte Gro&szlig;-Kleinschreibung beim Username!<br>";
				return false;
			}
			
			$uid   = $this->db->f("user_id");
			$perm  = $this->db->f("perms");
			$pass  = $this->db->f("password");   // Password is stored as a md5 hash
		}
		$exspected_response = md5("$username:$pass:$challenge");
		
		// True when JS is disabled
		if ($response == "") {
			if (md5($password) != $pass) {       // md5 hash for non-JavaScript browsers
				$this->error_msg="Das Paßwort ist falsch!<br>";
				return false;
			} else {
				$this->auth["perm"] = $perm;
				$this->auth["jscript"] = FALSE;
				$this->auth_set_user_settings($uid);
				return $uid;
			}
		}
		
		// Response is set, JS is enabled
		if ($exspected_response != $response) {
			$this->error_msg="Das Paßwort ist falsch!<br>";
			return false;
		} else {
			$this->auth["perm"] = $perm;
			$this->auth["jscript"] = TRUE;
			$this->auth_set_user_settings($uid);
			return $uid;
		}
	}
	
	function auth_set_user_settings($uid){
		global $resolution, $_language;
		$divided = explode("x",$resolution);
		$this->auth["xres"] = ($divided[0]) ? $divided[0] : 800; //default
		$this->auth["yres"] = ($divided[1]) ? $divided[1] : 600; //default
		// Change X-Resulotion on Multi-Screen Systems (as Matrox Graphic-Adapters are)
		if (($this ->auth["xres"] / $this ->auth["yres"]) > 1.5){
			$this->auth["xres"] = $this->auth["xres"] /2;
		}
		//restore user-specific language preference
		$db = new DB_Seminar("SELECT preferred_language FROM user_info WHERE user_id='$uid'");
		if ($db->next_record()) {
			if ($db->f("preferred_language")) {
				// we found a stored setting for preferred language
				$_language = $db->f("preferred_language");
			}
		}
	}
	
}

class Seminar_Default_Auth extends Seminar_Auth {
	var $classname = "Seminar_Default_Auth";
	
	var $nobody    = true;
}


class Seminar_Register_Auth extends Seminar_Auth {
	var $classname = "Seminar_Register_Auth";
	var $magic     = "dsdfjhgretha";  // Challenge seed
	
	var $mode      = "reg";
	var $error_msg = ""; // Was läuft falsch bei der Registrierung ?
	
	function auth_registerform() {
		global $sess;
		global $challenge,$ABSOLUTE_PATH_STUDIP;
		
		$challenge = md5(uniqid($this->magic));
		$sess->register("challenge");
		
		include("$ABSOLUTE_PATH_STUDIP/crcregister.ihtml");
	}
	
	function auth_doregister() {
		global $username, $password, $challenge, $response, $Vorname, $Nachname, $geschlecht, $Email,$title_front,$title_front_chooser,$title_rear,$title_rear_chooser,$ABSOLUTE_PATH_STUDIP, $CANONICAL_RELATIVE_PATH_STUDIP, $UNI_NAME_CLEAN;
		
		require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
		
		$this->auth["uname"]=$username;					// This provides access for "crcregister.ihtml"
		
		$validator=new email_validation_class;	// Klasse zum Ueberpruefen der Eingaben
		$validator->timeout=10;									// Wie lange warten wir auf eine Antwort des Mailservers?
		
		
		$username = trim($username);
		$Vorname = trim($Vorname);
		$Nachname = trim($Nachname);
		$Email = trim($Email);
		
		if (!$validator->ValidateUsername($username))
		{
			$this->error_msg=$this->error_msg."Der gewählte Username ist zu kurz!<br>";
			return false;
		}														// username syntaktisch falsch oder zu kurz
		// auf doppelte Vergabe wird weiter unten getestet.
		
		if (!isset($response) || $response=="")	{	// wir haben kein verschluesseltes Passwort
			if (!$validator->ValidatePassword($password))
			{
				$this->error_msg=$this->error_msg."Das Paßwort ist zu kurz!<br>";
				return false;
			}													// also können wir das unverschluesselte Passwort testen
		}
		
		if (!$validator->ValidateName($Vorname))
		{
			$this->error_msg=$this->error_msg."Der Vorname fehlt, oder ist unsinnig!<br>";
			return false;
		}			   // Vorname nicht korrekt oder fehlend
		if (!$validator->ValidateName($Nachname))
		{
			$this->error_msg=$this->error_msg."Der Nachname fehlt, oder ist unsinnig!<br>";
			return false;			   // Nachname nicht korrekt oder fehlend
		}
		if (!$validator->ValidateEmailAddress($Email))
		{
			$this->error_msg=$this->error_msg."Die E-Mail Addresse fehlt, oder ist falsch geschrieben!<br>";
			return false;
		}			   // E-Mail syntaktisch nicht korrekt oder fehlend
		
		$smtp=new studip_smtp_class;		     // Einstellungen fuer das Verschicken der Mails
		$REMOTE_ADDR=getenv("REMOTE_ADDR");
		$Zeit=date("H:i:s, d.m.Y",time());
		
		if (!$validator->ValidateEmailHost($Email)) {     // Mailserver nicht erreichbar, ablehnen
			$this->error_msg=$this->error_msg."Der Mailserver ist nicht erreichbar, bitte überprüfen Sie, ob Sie E-Mails mit der angegebenen Addresse verschicken können!<br>";
			return false;
		} else {					  // Server ereichbar
			if (!$validator->ValidateEmailBox($Email)) {    // aber user unbekannt. Mail an abuse@puk!
				$from="wwwrun@".$smtp->localhost;
				$to="abuse@".$smtp->localhost;
				$smtp->SendMessage(
				$from, array($to),
				array("From: $from", "To: $to", "Subject: Register"),
				"Emailbox unbekannt\n\nUser: $username\nEmail: $Email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
				$this->error_msg=$this->error_msg."Die angegebene E-Mail Addresse ist nicht erreichbar, bitte überprüfen Sie Ihre Angaben!<br>";
				return false;
			} else {
				;					     // Alles paletti, jetzt kommen die Checks gegen die Datenbank...
			}
		}
		
		$this->db->query(sprintf("select user_id ".
		"from %s where username = '%s'",
		$this->database_table,
		addslashes($username)));
		
		while($this->db->next_record()) {
			//   error_log("username schon vorhanden", 0);
			$this->error_msg=$this->error_msg."Der gewählte Username ist bereits vorhanden!<br>";
			return false;				   // username schon vorhanden
		}
		
		$this->db->query(sprintf("select user_id ".
		"from %s where Email = '%s'",
		$this->database_table,
		addslashes($Email)));
		
		while($this->db->next_record()) {
			//error_log("E-Mail schon vorhanden", 0);
			$this->error_msg=$this->error_msg."Die angegebene E-Mail Addresse wird bereits von einem anderen User verwendet. Sie müssen eine andere E-Mail Addresse angeben!<br>";
			return false;				   // Email schon vorhanden
		}
		
		// alle Checks ok, Benutzer registrieren...
		// True when JS is disabled
		if ($response == "") {
			$newpass = md5($password);
		}
		// Response is set, JS is enabled
		else {
			$newpass = $response;
		}
		$uid = md5(uniqid($this->magic));
		$perm = "user";
		$this->db->query(sprintf("insert into %s (user_id, username, perms, password, Vorname, Nachname, Email) ".
		"values ('%s', '%s', '%s', '%s', '%s', '%s', '%s')",
		$this->database_table, $uid, addslashes($username), $perm, $newpass,
		addslashes($Vorname), addslashes($Nachname), addslashes($Email)));
		$this->auth["perm"] = $perm;
		
		if($title_front == "")
			$title_front = $title_front_chooser;
		
		if($title_rear == "")
			$title_rear = $title_rear_chooser;
		
		// Anlegen eines korespondierenden Eintrags in der user_info
		$this->db->query("INSERT INTO user_info SET user_id='$uid', mkdate='".time()."', geschlecht='$geschlecht', title_front='$title_front', title_rear='$title_rear'");
		
		// Abschicken der Bestaetigungsmail
		$to=$Email;
		$secret= md5("$uid:$this->magic");
		$url = "http://" . $smtp->localhost . $CANONICAL_RELATIVE_PATH_STUDIP . "email_validation.php?secret=" . $secret;
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
		$smtp->SendMessage(
		$smtp->env_from, array($to),
		array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: Bestätigungsmail des Stud.IP-Systems"),
		$mailbody);
		
		return $uid;
	}
}



class Seminar_Perm extends Perm {
	var $classname = "Seminar_Perm";
	
	var $permissions = array(
	"user"       => 1,
	"autor"      => 3,
	"tutor"	     => 7,
	"dozent"     => 15,
	"admin"      => 31,
	"root"       => 63
	);
	var $studip_perms = array();
	var $fak_admins = array();
	
	function perm_invalid($does_have, $must_have) {
		global $perm, $auth, $sess;
		global $ABSOLUTE_PATH_STUDIP,$RELATIVE_PATH_CHAT;
		include($ABSOLUTE_PATH_STUDIP . "perminvalid.ihtml");
	}
	
	function get_studip_perm($range_id) {
		global $auth;
		if (!$range_id){
			return false;
		}
		$db=new DB_Seminar;
		$status = false;
		$user_id = $auth->auth["uid"];
		$user_perm = $auth->auth["perm"];
		if ($user_perm == "root") {
			return "root";
		} elseif (isset($this->studip_perms[$range_id])) {
			return $this->studip_perms[$range_id];
		} elseif ($user_perm == "admin") {
			$db->query("SELECT seminare.Seminar_id FROM user_inst 
						LEFT JOIN seminare USING (Institut_id)
						WHERE inst_perms='admin' AND user_id='$user_id' AND seminare.Seminar_id='$range_id'");
			if ($db->num_rows()) {
				$status = "admin";
			} else {
				$db->query("SELECT Seminar_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id) 
							LEFT JOIN Institute c ON (b.Institut_id=c.fakultaets_id) LEFT JOIN seminare d USING(Institut_id) WHERE a.user_id='$user_id' AND a.inst_perms='admin' AND d.Seminar_id='$range_id'");
				if ($db->num_rows()) {
					$status = "admin";
				} else {
					$db->query("SELECT a.Institut_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id) WHERE user_id='$user_id' AND a.inst_perms='admin'
								AND b.Institut_id='$range_id'");
					if ($db->num_rows()) {
						$status = "admin";
					}
				}
			}
		}
		
		if ($status) {
			$this->studip_perms[$range_id] = $status;
			return $status;
		}
		
		$db->query("SELECT status FROM seminar_user WHERE user_id='$user_id' AND Seminar_id='$range_id'");
		if ($db->next_record()){
			$status=$db->f("status");
		} else {
			$db->query("SELECT inst_perms FROM user_inst WHERE user_id='$user_id' AND Institut_id='$range_id'");
			if ($db->next_record()){
				$status=$db->f("inst_perms");
			}
		}
		$this->studip_perms[$range_id] = $status;
		return $status;
	}
	
	function have_studip_perm($perm,$range_id) {
		
		if (!$perm || !$range_id){
			return false;
		}
		$pageperm = split(",", $perm);
		$userperm = split(",", $this->get_studip_perm($range_id));
		
		list ($ok0, $pagebits) = $this->permsum($pageperm);
		list ($ok1, $userbits) = $this->permsum($userperm);
		
		$has_all = (($userbits & $pagebits) == $pagebits);
		
		if (!($has_all && $ok0 && $ok1) ) {
			return false;
		} else {
			return true;
		}
	}
	
	function is_fak_admin($user_id = ""){
		global $auth;
		$user_id = $auth->auth["uid"];
		$user_perm = $auth->auth["perm"];
		if ($user_perm == "root") {
			return true;
		}
		if (isset($this->fak_admins[$user_id])){
			return $this->fak_admins[$user_id];
		} else {
			$db = new DB_Seminar("SELECT a.Institut_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.Institut_id AND b.Institut_id=b.fakultaets_id)  
									WHERE a.user_id='$user_id' AND a.inst_perms='admin' AND NOT ISNULL(b.Institut_id)");
			if ($db->next_record()){
				$this->fak_admins[$user_id] = true;
				return true;
			} else {
				$this->fak_admins[$user_id] = false;
				return false;
			}
		}
	}
}
?>
