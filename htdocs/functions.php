<?
function select_group($sem_start_time, $user_id='') {
	global $SEMESTER;
	
	$db=new DB_Seminar;
	
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

//////////////////////////////////////////////////////////////////////////

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

//////////////////////////////////////////////////////////////////////////

function get_fullname($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT Vorname , Nachname , user_id FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("Vorname")." " . $db->f("Nachname");
 if ($author=="") $author="unbekannt";

 return $author;
 }
 
 /////////////////////////////////////////////////////////////////////////

function get_fullname_from_uname($uname="")
{
 global $user;
 $db=new DB_Seminar;
 $db->query ("SELECT Vorname , Nachname , user_id FROM auth_user_md5 WHERE username = '$uname'");
				 while ($db->next_record())
					 $author=$db->f("Vorname")." " . $db->f("Nachname");
 if ($author=="") $author="unbekannt";

 return $author;
 }
 
 
 //////////////////////////////////////////////////////////////////////////

function get_nachname($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT Vorname , Nachname , user_id FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("Nachname");
 if ($author=="") $author="unbekannt";

 return $author;
 }
 
 //////////////////////////////////////////////////////////////////////////
 
 function get_username($user_id="")
{
 global $user;
 if (!($user_id)) $user_id=$user->id;
 $db=new DB_Seminar;
 $db->query ("SELECT username , user_id FROM auth_user_md5 WHERE user_id = '$user_id'");
				 while ($db->next_record())
					 $author=$db->f("username");

 return $author;
 }

 //////////////////////////////////////////////////////////////////////////
 
 function get_userid($username="")
{
 global $user;
 $db=new DB_Seminar;
 $db->query ("SELECT user_id  FROM auth_user_md5 WHERE username = '$username'");
				 while ($db->next_record())
					 $author=$db->f("user_id");

 return $author;
 }
 
 //////////////////////////////////////////////////////////////////////////
 
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
	IF ($score>65536) $titel =	"Papst";
	IF ($score>131072) $titel =	"Gott";

	RETURN $titel;
}

//////////////////////////////////////////////////////

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

///////////////////////////////////////////////////////////////
?>
