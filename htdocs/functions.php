<?
/**
* functions.php
* 
* The Stud.IP-Core functions. Look to the descriptions to get further details
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @package		studip_core
* @modulegroup	library
* @module		functions.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>, 
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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
* This function returns an array of all my administrable objects (sem, inst)
*
* All the Objects of the type Seminar and Einrichtung, which the 
* specified user has the status 'admin'. The array contains the id as key
* and the type as value (inst, sem). The function include NOT the root-perm-level
* (because all objects and levels are administrable by root...)
* 
* @param		string	user_id the seminar_id of the seminar to calculate
* @return		array		returns an array of my object_ids
*
*/

function get_my_administrable_objects($user_id='') {
	global $user, $perm;
	
	if (!$user_id)
		$user_id=$user->id;
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	
	if ($perm->have_perm("admin")) //check all the Seminare from all my Einrichtungen
		$db->query("SELECT Seminar_id FROM user_inst LEFT JOIN seminar_inst USING (Institut_id)  WHERE user_id='$user_id' AND inst_perms = 'admin' ");
	else //check all my Seminare
		$db->query("SELECT Seminar_id FROM seminar_user WHERE user_id='$user_id' AND status IN ('dozent', 'tutor') ");
			
	while ($db->next_record()) {
		$my_objects[$db->f("Seminar_id")]="sem";
	}
	
	//check all my Einrichtungen
	$db->query("SELECT Institut_id FROM user_inst WHERE user_id='$user_id' AND inst_perms IN ('admin', 'dozent', 'tutor') ");
	
	while ($db->next_record()) {
		$my_objects[$db->f("Institut_id")]="inst";
	}
		
	return $my_objects;
}


/**
* This function determines, which from which type an id is from.
*
* The function recognizes the following types at this moment:
* Einrichtungen, Veranstaltungen and Fakultaeten
* 
* @param		string	seminar_id	the seminar_id of the seminar to calculate
* @return		string	return "ins" (Institut), "sem" (Veranstaltung), "fak" (Fakultaeten)
*
*/

function get_object_type($seminar_id) {
	 $db=new DB_Seminar;
	
	$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id = '$seminar_id' ");
	if ($db->next_record())
		return "sem";

	if (!$entry_level) {
		$db->query("SELECT Institut_id FROM Institute WHERE Institut_id = '$seminar_id' ");
		if ($db->next_record())
			return "inst";
	}

	if (!$entry_level) {
		$db->query("SELECT Fakultaets_id FROM Fakultaeten WHERE Fakultaets_id = '$seminar_id' ");
		if ($db->next_record())
			return "fak";
	}
	return FALSE;
}

// functions.php - This file contains various functions
// functions.php - author: studip crew
// functions.php - version: $Id$

////
// !dunno
// A longer, more complete description would go here
// We might include details of what the function parameters do and what the 
// result should be, etc.
function select_group($sem_start_time, $user_id='') {
	global $SEMESTER;
	//Farben Algorhytmus, erzeugt eindeutige Farbe fuer jedes Semester. Funktioniert ab 2001 die naechsten 1000 Jahre.....
	$year_of_millenium=date ("Y", $sem_start_time) % 1000;
	$index=$year_of_millenium * 2;
	if (date ("n", $sem_start_time) > 6)
		$index++;
	$group=($index % 7) + 1;

	return $group;
}

//////////////////////////////////////////////////////////////////////////

function my_substr($what, $start, $end) {
	$length=$end-$start;
	if (strlen($what) > $length) {
		$what=substr($what, $start, (($length / 3) * 2))."[...]".substr($what, strlen($what) -($length / 3), strlen($what)); 
		}
	return $what;
}


//////////////////////////////////////////////////////////////////////////

function have_sem_write_perm () {

global $SemSecLevelWrite, $SemUserStatus, $perm, $rechte;

$error_msg="";
if (!($perm->have_perm("root"))) {
       if (!($rechte || ($SemUserStatus=="autor") || ($SemUserStatus=="tutor") || ($SemUserStatus=="dozent"))) // hier wohl eher kein Semikolon
	   {
		//Auch eigentlich uberfluessig...
		//$error_msg = "<br><b>Sie haben nicht die Berechtigung in dieser Veranstaltung zu schreiben!</b><br><br>";
		switch ($SemSecLevelWrite) {
			case 2 : 
				$error_msg=$error_msg."error§In dieser Veranstaltung ist ein Passwort f&uuml;r den Schreibzugriff n&ouml;tig.<br>Zur <a href=\"sem_verify.php\">Passworteingabe</a>§";
				break;
			case 1 :
				if ($perm->have_perm("autor"))
					$error_msg=$error_msg."info§Sie müssen sich erneut für diese Veranstaltung anmelden, um schreiben zu können!<br>Hie kommen sie zur <a href=\"sem_verify.php\">Freischaltung</a> der Veranstaltung.§";
				elseif ($perm->have_perm("user"))
					$error_msg=$error_msg."info§Bitte folgen Sie den Anweisungen in der Registrierungsmail.§";
				else
					$error_msg=$error_msg."info§Bitte melden Sie sich an.<br>Hier geht es zur <a href=\"register1.php\">Registrierung</a> wenn Sie noch keinen Account im System haben.§";
				break;
			default :
				//Wenn Schreiben fuer Nobody jemals wieder komplett verboten werden soll, diesen Teil bitte wieder einkommentieren (man wei&szlig; ja nie...)
				//$error_msg=$error_msg."Bitte melden Sie sich an.<br><br><a href=\"register1.php\"><b>Registrierung</b></a> wenn Sie noch keinen Account im System haben.<br><a href=\"index.php?again=yes\"><b>Login</b></a> f&uuml;r registrierte Benutzer.<br><br>";
				break; 
			}
		$error_msg=$error_msg."info§Dieser Fehler kann auch aufteten, wenn Sie zu lange inaktiv gewesen sind. <br />Wenn sie l&auml;nger als $AUTH_LIFETIME Minuten keine Aktion mehr ausgef&uuml;hrt haben, m&uuml;ssen sie sich neu anmelden.§";
		}
	}
return $error_msg;
}
//////////////////////////////////////////////////////////////////////////
////
// !Returns global perm
// deprecated	use $auth->auth["perm"]
function get_global_perm($user_id="") {
	 global $user;

	 if (!($user_id)) $user_id=$user->id;
	
	 $db=new DB_Seminar;
	 $db->query("SELECT perms FROM auth_user_md5 WHERE user_id='$user_id'");
	 if ($db->next_record())
	 	return $db->f("perms");
	 else
	 	return ("Fehler");
}
// TABLES: auth_user_md5

//////////////////////////////////////////////////////////////////////////

////
// !Returns permission for given range_id and user_id
// Function works for seminare,institute, fakultaeten
// admins get status 'admin' if range_id is a seminar
// param:	string 	$range_id 	an id from a seminar,institut, fakultaet
// param:	string 	$user_id 	if omitted,current user_id is used
// return:	string 	
function get_perm($range_id,$user_id="")
{
 global $user,$auth;
 $status="";
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query("SELECT status FROM seminar_user WHERE user_id='$user_id' AND Seminar_id='$range_id'");
 if ($db->num_rows())
 	{
	$db->next_record();
	$status=$db->f("status");
	}
 else
	{
	$db->query("SELECT inst_perms FROM user_inst WHERE user_id='$user_id' AND Institut_id='$range_id'");
	if ($db->num_rows())
		{
		$db->next_record();
		$status=$db->f("inst_perms");
		}
	else
		{
		$db->query("SELECT status FROM fakultaet_user WHERE user_id='$user_id' AND Fakultaets_id='$range_id'");
		if ($db->num_rows())
			{
			$db->next_record();
			$status=$db->f("status");
			}
	  }
	}
 if ($auth->auth["perm"]=="admin")   // Institutsadmins sind automagisch admins in Seminaren des Institus
	{
	$db->query("SELECT user_inst.Institut_id, seminare.Seminar_id FROM user_inst LEFT JOIN seminare ON (user_inst.Institut_id=seminare.Institut_id AND seminare.Seminar_id='$range_id') WHERE inst_perms='admin' AND user_id='$user_id'");
	if ($db->num_rows())
		{
		// Eintrag gefunden, also ein zum Instadmin gehöriges Seminar
		$status="admin";
		}
	}

 if (!($status)) $status="fehler!";

 return $status;
}
// TABLES: seminar_user,seminare,user_inst,fakultaet_user
//////////////////////////////////////////////////////////////////////////

////
// !Retrieves fullname for a given user_id
// uses global $online array if user is online
// param:	string 	$user_id 	if omitted,current user_id is used
// return:	string 	
function get_fullname($user_id="")
{
 global $user,$online;
 $author="";
 if (!($user_id)) $user_id=$user->id;
 if(count($online)) {
 	foreach($online as $key=>$value){
		if ($value["userid"]==$user_id) {
		    $author=$value["name"];
		    break;
			}
		}
	}
if (!$author) {
     $db=new DB_Seminar;
     $db->query ("SELECT CONCAT(Vorname ,' ', Nachname) AS fullname FROM auth_user_md5 WHERE user_id = '$user_id'");
    				 while ($db->next_record())
    					 $author=$db->f("fullname");
 }
 if ($author=="") $author="unbekannt";
 return $author;
 }
// TABLES: auth_user_md5 
 /////////////////////////////////////////////////////////////////////////

////
// !Retrieves fullname for a given username
// uses global $online array if user is online
// param:	string 	$uname 	if omitted,current username is used
// return:	string 	
function get_fullname_from_uname($uname="")
{
 global $auth,$online;
 $author="";
 if (!$uname) $uname=$auth->auth["uname"];
 if(count($online)) {
 	if ($online[$uname]) {
		    $author=$online["name"];
	}
}
if (!$author) {
 $db=new DB_Seminar;
 $db->query ("SELECT CONCAT(Vorname ,' ', Nachname) AS fullname FROM auth_user_md5 WHERE username = '$uname'");
				 while ($db->next_record())
					 $author=$db->f("fullname");
 }
 if ($author=="") $author="unbekannt";

 return $author;
 }
 // TABLES: auth_user_md5
 
 //////////////////////////////////////////////////////////////////////////

////
// !Retrieves vorname for a given user_id
// param:	string 	$user_id 	if omitted,current user_id is used
// return:	string 	
function get_vorname($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT Vorname FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("Vorname");
 if ($author=="") $author="unbekannt";

 return $author;
 }
// TABLES: auth_user_md5
 
 //////////////////////////////////////////////////////////////////////////

////
// !Retrieves nachname for a given user_id
// param:	string 	$user_id 	if omitted,current user_id is used
// return:	string 	
function get_nachname($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT Nachname FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("Nachname");
 if ($author=="") $author="unbekannt";

 return $author;
 }
 // TABLES: auth_user_md5
 //////////////////////////////////////////////////////////////////////////

 ////
// !Retrieves username for a given user_id
// uses global $online array if user is online
// param:	string 	$user_id 	if omitted,current user_id is used
// return:	string 	
function get_username($user_id="")
{
  global $auth,$online;
 $author="";
 if (!($user_id)) return $auth->auth["uname"];
 if(count($online)) {
 	foreach($online as $key=>$value){
		if ($value["userid"]==$user_id) {
		    $author=$key;
		    break;
			}
		}
	}
if (!$author) {
 $db=new DB_Seminar;
 $db->query ("SELECT username , user_id FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("username");
}
 return $author;
}
// TABLES: auth_user_md5
 //////////////////////////////////////////////////////////////////////////

////
// !Retrieves user_id for a given username
// uses global $online array if user is online
// param:	string 	$username 	if omitted,current username is used
// return:	string 	
function get_userid($username="")
{
 global $user,$online;
 $author="";
 if (!$username) return $user->id;
 if(count($online)) {
 	$author=$online[$username]["userid"];
	}
if (!$author) {
$db=new DB_Seminar;
 $db->query ("SELECT user_id  FROM auth_user_md5 WHERE username = '$username'");
				 while ($db->next_record())
					 $author=$db->f("user_id");
}
 return $author;
}
//TABLES auth_user_md5 
 //////////////////////////////////////////////////////////////////////////

////
// !Retrieves titel for a given studip score
// param:	integer	$score
// return:	string 	
FUNCTION gettitel($score)

{
	IF ($score==0) $titel =		"Unbeschriebenes Blatt";
	IF ($score>0) $titel =		"Neuling";
	IF ($score>16) $titel =		"Greenhorn";
	IF ($score>32) $titel =		"Anf&auml;nger";
	IF ($score>64) $titel =		"Einsteiger";
	IF ($score>128) $titel =		"Beginner";
	IF ($score>256) $titel =		"Novize";
	IF ($score>512) $titel =		"Fortgeschrittener";
	IF ($score>1024) $titel =	"Kenner";
	IF ($score>2048) $titel =	"K&ouml;nner";
	IF ($score>4096) $titel =	"Experte";
	IF ($score>8192) $titel =	"Meister";
	IF ($score>16384) $titel =	"Gro&szlig;meister";
	IF ($score>32768) $titel =	"Guru";
	IF ($score>65536) $titel =	"Lichtgestalt";
	IF ($score>131072) $titel =	"Gott";

	RETURN $titel;
}

//////////////////////////////////////////////////////

////
// !calculate score for current user
// score is also stored in DB
// return:	integer
function getscore()

{ global $user,$auth;

$user_id=$user->id; //damit keiner schummelt...

///// Werte holen...

$db=new DB_Seminar;
$db->query("SELECT count(*) as postings FROM px_topics WHERE user_id = '$user_id' ");
$db->next_record();
$postings=$db->f("postings");

$db->query("SELECT count(*) as dokumente FROM dokumente WHERE user_id = '$user_id' ");
$db->next_record();
$dokumente=$db->f("dokumente");

$db->query("SELECT count(*) as seminare FROM seminar_user WHERE user_id = '$user_id' ");
$db->next_record();
$seminare=$db->f("seminare");

$db->query("SELECT count(*) as archiv FROM archiv_user WHERE user_id = '$user_id' ");
$db->next_record();
$archiv=$db->f("archiv");

$db->query("SELECT count(*) as institut FROM user_inst WHERE user_id = '$user_id' ");
$db->next_record();
$institut=$db->f("institut");

$db->query("SELECT count(*) as news FROM news WHERE user_id = '$user_id' ");
$db->next_record();
$news=$db->f("news");


///////////////////////// Die HOCHGEHEIME Formel:

$score = (5*$postings) + (5*$news) + (20*$dokumente) + (5*$institut) + (5*($archiv+$seminare));
if(file_exists("./user/".$user_id.".jpg")) $score *=10;

/// Schreiben wenn hoeher

	
$query = "UPDATE user_info "
	." SET score = '$score'"
	." WHERE user_id = '$user_id' AND score > 0";
$db->query($query);
	
RETURN $score;
}
// TABLES: user_info,news,user_inst,archiv_user,seminar_user,dokumente,px_topics
///////////////////////////////////////////////////////////////
?>