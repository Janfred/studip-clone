<?php
/*
about.php - Anzeige der persoenlichen Userseiten von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>, Niclas Nohlen <nnohlen@gwdg.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- hier muessen Seiten-Initialisierungen passieren --

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");

// Start  of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
?>
<script language="Javascript">
function open_im()
{
fenster=window.open("studipim.php","im_<?=$user->id;?>","scrollbars=yes,width=400,height=300","resizable=no");
}
</script>
<body bgcolor=white>

<?php
include ("$ABSOLUTE_PATH_STUDIP/header.php");
include ("$ABSOLUTE_PATH_STUDIP/show_news.php");
include ("$ABSOLUTE_PATH_STUDIP/show_dates.inc.php");

$sess->register("about_data");
$msging=new messaging;

//Typen zu den Buddies beipacken
if ($cmd=="add_user")
	$msging->add_buddy ($add_uname, 0);
	
//Auf und Zuklappen Termine
if ($dopen)
   $about_data["dopen"]=$dopen;

if ($dclose)
   $about_data["dopen"]='';

//Auf und Zuklappen News
if ($nopen)
    $about_data["nopen"]=$nopen;

if ($nclose)
   $about_data["nopen"]='';

if ($sms_msg)
	$msg=rawurldecode($sms_msg);

//Wenn kein Username uebergeben wurde, wird der eigene genommen:

if (!isset($username) || $username == "")
    $username = $auth->auth["uname"];

 $db = new DB_Seminar;
 $db2 = new DB_Seminar;
 $db3 = new DB_Seminar;

//3 zeilen wegen username statt id zum aufruf... in $user_id steht jetzt die user_id (sic)
 $db->query("SELECT * FROM auth_user_md5  WHERE username ='$username'");
 $db->next_record();
 $user_id=$db->f("user_id");

//Wenn er noch nicht in user_info eingetragen ist, kommt er ohne Werte rein
 $db->query("SELECT * FROM user_info WHERE user_id ='$user_id'");
 if ($db->num_rows()==0) {
  $db->query("INSERT INTO user_info (user_id) VALUES ('$user_id')");
 }

//Bin ich ein Inst_admin, und ist der user in meinem Inst Tutor oder Dozent?
 $db->query("SELECT b.inst_perms FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.user_id = '$user_id') AND (b.inst_perms = 'autor' OR b.inst_perms = 'tutor' OR b.inst_perms = 'dozent') AND (a.user_id = '$user->id') AND (a.inst_perms = 'admin')");
 if ($db->num_rows())
  $admin_darf = TRUE;
 else $admin_darf = FALSE;

//Her mit den Daten...
 $db->query("SELECT user_info.* , auth_user_md5.* FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = '$user_id'");
 $db->next_record();

//daten anzeigen
 IF (($user_id==$user->id AND $perm->have_perm("autor")) OR $perm->have_perm("root") OR $admin_darf == TRUE) { // Es werden die Editreiter angezeigt, wenn ich &auml;ndern darf
	 include ("$ABSOLUTE_PATH_STUDIP/links_about.inc.php");  
 }

?>

   <table align="center" width="100%" border="0" cellpadding="1" cellspacing="0" valign="top">
 <tr><td class="topic" align="right" colspan=2>&nbsp;</td></tr>
<?
if ($msg)
	{
	echo"<tr><td class=\"steel1\"colspan=2><br>";
	parse_msg ($msg, "§", "steel1");
	echo"</td></tr>";
	}
?>
  
   <tr><td class="steel1" align="center" valign="center"><img src="pictures/blank.gif" width=205 height=5><br />
   <?

// hier wird das Bild ausgegeben

	if(!file_exists("./user/".$user_id.".jpg")) {
		echo "&nbsp;<img src=\"./user/nobody.jpg\" width=\"200\" height=\"250\" alt=\"kein pers&ouml;nliches Bild vorhanden\">";
	} else {
		?>&nbsp;<img src="./user/<?echo $user_id; ?>.jpg" border=1 alt="<?echo htmlReady($db->f("Vorname"))." ".htmlReady($db->f("Nachname"));?>"></td><?
	}
    
	// Hier der Teil fuer die Ausgabe der normalen Daten
	?>
    <td class="steel1"  width="99%" valign ="top" rowspan=2><br><blockquote>
    <? echo "<b><font size=7>",htmlReady($db->f("Vorname")), " ", htmlReady($db->f("Nachname")),"</font></b><br><br>";?>
    <? echo "<b>&nbsp;e-mail: </b><a href=\"mailto:", $db->f("Email"),"\">",htmlReady($db->f("Email")),"</a>","<br>";
		IF ($db->f("privatnr")!="") echo "<b>&nbsp;Telefon (privat): </b>", htmlReady($db->f("privatnr")),"<br>";
		IF ($db->f("privadr")!="") echo "<b>&nbsp;Adresse (privat): </b>", htmlReady($db->f("privadr")),"<br>";
		IF ($db->f("Home")!="") {
			$home=$db->f("Home");
			$home=FixLinks($home);
			echo "<b>&nbsp;Homepage: </b>",$home,"<br>";
		}

// Anzeige der Institute an denen (hoffentlich) studiert wird:

    $db3->query("SELECT Institute.* FROM user_inst LEFT JOIN Institute  USING (Institut_id) WHERE user_id = '$user_id' AND inst_perms = 'user'");
    IF ($db3->num_rows()) {
			echo "<br><b>&nbsp;Wo ich studiere:&nbsp;&nbsp;</b><br>";
      while ($db3->next_record()) {
      	echo "&nbsp; &nbsp; &nbsp; &nbsp;<a href=\"institut_main.php?auswahl=".$db3->f("Institut_id")."\">".htmlReady($db3->f("Name"))."</a><br>";
    	}
		}

// Anzeige der Institute an denen gearbeitet wird

		$db3->query("SELECT * FROM user_inst WHERE user_id = '$user_id' AND inst_perms != 'user'");
		IF ($db3->num_rows()) {
			echo "<br><b>&nbsp;Wo ich arbeite:&nbsp;&nbsp;</b><br>";
		}

   //schleife weil evtl. mehrere sprechzeiten und institut nicht gesetzt...

		while ($db3->next_record()) {
			$institut=$db3->f("Institut_id");
			$db2->query("SELECT * FROM Institute WHERE Institut_id = '$institut'");
			$db2->next_record();
		      	echo "&nbsp; &nbsp; &nbsp; &nbsp;<a href=\"institut_main.php?auswahl=".$db2->f("Institut_id")."\">".htmlReady($db2->f("Name"))."</a>";
			//echo "&nbsp; &nbsp; &nbsp;<b><a href=\"".$db2->f("url")."\" target=\"_blank\">".htmlReady($db2->f("Name")),"</a></b>";
			//echo "&nbsp; &nbsp; &nbsp;<b>".formatReady("[".trim($db2->f("Name"))."]".trim($db2->f("url")))."</b>";
			
			IF ($db3->f("Funktion"))
				echo ", ",$INST_FUNKTION[$db3->f("Funktion")]["name"]; 
    	echo "<font size=-1>";
			IF ($db3->f("raum")!="")
				echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Raum: </b>", htmlReady($db3->f("raum"));
			IF ($db3->f("sprechzeiten")!="")
				echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Sprechzeit: </b>", htmlReady($db3->f("sprechzeiten"));
			IF ($db3->f("Telefon")!="")
				echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Telefon: </b>", htmlReady($db3->f("Telefon"));
			IF ($db3->f("Fax")!="")
				echo "<b><br>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fax: </b>", htmlReady($db3->f("Fax"));

			echo "</font><br>";
		}
		echo "</blockquote></td></tr>"
    ?>
    
		</td></tr><tr>
		<td class="steel1" height=99% align="left" valign="top">
		<?

	if ($username==$auth->auth["uname"]) {
		if ($auth->auth["jscript"])
			echo "<br>&nbsp; <a href='javascript:open_im();'>Stud.IP Messenger starten</a>";
		} else {
	        	echo "<br>&nbsp; Nachricht an User: <a href=\"sms.php?sms_source_page=about.php&username=$username&cmd=write&rec_uname=", $db->f("username"),"\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=0 align=texttop></a>";
			if (!$my_buddies[$username])
				echo "<br />&nbsp; <a href=\"$PHP_SELF?cmd=add_user&add_uname=$username&username=$username\">Zu Buddies hinzuf&uuml;gen</a>";
		}
    	

/// Die Anzeige der Stud.Ip-Score

		IF ($username==$auth->auth["uname"])
			echo "<br /><br />&nbsp; <a href='score.php' alt='Zur Highscoreliste'>Ihre Stud.IP-Score: ".getscore()."<br>&nbsp; Ihr Rang: ".gettitel(getscore())."</a>";
		else {
			$db2->query("SELECT score FROM user_info WHERE score > 0  AND user_id = '$user_id'");
			if ($db2->num_rows()) {
				while ($db2->next_record())
					echo "<br /><br />&nbsp; <a href='score.php'>Stud.IP-Score: ".$db->f("score")."<br>&nbsp; Rang: ".gettitel($db->f("score"))."</a>";
			}
		}

    echo "<br>&nbsp; ";
    echo "</td>";

		echo "</tr></table><br>\n";

// News zur person anzeigen!!!

	($perm->have_perm("autor") AND $auth->auth["uid"]==$user_id) ? $show_admin=TRUE : $show_admin=FALSE;
	if (show_news($user_id, $show_admin, 0, $about_data["nopen"]))
		echo "<br>";

// alle persoenlichen Termine anzeigen, aber keine privaten

	$start_zeit=time();
	($perm->have_perm("autor") AND $auth->auth["uid"]==$user_id) ? $show_admin=TRUE : $show_admin=FALSE;
	if (show_personal_dates($user_id, $start_zeit, -1, FALSE, $show_admin, $about_data["dopen"]))
		echo "<br>";

// Hier wird der Lebenslauf ausgegeben:

	if ($db->f("lebenslauf")!="") {
    printf ("<table class='blank' width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;Lebenslauf </b></td></tr><tr><td class='steel1'><br><blockquote>%s</blockquote></td></tr></table><br>\n",formatReady($db->f("lebenslauf")));
	}

// Ausgabe Hobbys

	if ($db->f("hobby")!="") {
		printf ("<table class='blank' width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;Hobbies </b></td></tr><tr><td class='steel1'><br><blockquote>%s</blockquote></td></tr></table><br>\n",formatReady($db->f("hobby")));
	}

//Ausgabe von Publikationen

	if ($db->f("publi")!="") {
		printf ("<table class='blank' width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;Publikationen </b></td></tr><tr><td class='steel1'><br><blockquote>%s</blockquote></td></tr></table><br>\n",formatReady($db->f("publi")));
	}

// Ausgabe von Arbeitsschwerpunkten

	if ($db->f("schwerp")!="") {
		printf ("<table class='blank' width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;Arbeitsschwerpunkte </b></td></tr><tr><td class='steel1'><br><blockquote>%s</blockquote></td></tr></table><br>\n",formatReady($db->f("schwerp")));
	}

// Ausgabe der eigenen Kategorien

	$db2->query("SELECT * FROM kategorien WHERE range_id = '$user_id' ORDER BY chdate DESC");
	while ($db2->next_record())  {
		$head=$db2->f("name");
		$body=$db2->f("content");
		if ($db2->f("hidden")!='1')  // oeffentliche Rubrik
			echo "<table class='blank' width=100% border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;".htmlReady($head)." </b></td></tr><tr><td class='steel1'><br><blockquote>", formatReady($body),"</blockquote></td></tr></table><br>\n";
		elseif ($db->f("user_id")==$user->id)   // nur ich darf sehen
			echo "<table class='blank' width=100% border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;".htmlReady($head)." </b></td></tr><tr><td class='steel1'><br><blockquote>", formatReady($body),"</blockquote></td></tr></table><br>\n";
	}
// Anzeige der Seminare

	$db2->query("SELECT * FROM seminar_user LEFT JOIN seminare USING(Seminar_id) WHERE seminar_user.user_id = '$user_id' AND seminar_user.status = 'dozent' ORDER BY start_time");
	if ($db2->num_rows()) {
		echo "<table class='blank' width=100% border='0' cellpadding='0' cellspacing='0'><tr><td class=\"topic\"><b>&nbsp;Veranstaltungen</b></td></tr><tr><td class='steel1'><blockquote>";
		while ($db2->next_record()) {
			if (($sem_name) <> (get_sem_name ($db2->f("start_time")))) {
				$sem_name=get_sem_name ($db2->f("start_time"));
				echo"<br><font size=\"+1\"><b>$sem_name</b></font><br><br>";
			}
			echo"<b><a href=\"details.php?sem_id=", $db2->f("Seminar_id"), "\">", htmlReady($db2->f("Name")), "</b></a><br>";
		}
		echo "</blockquote></td></tr></table><br>\n";
	}

  // Save data back to database.
  page_close()
 ?>
</body>
</html>
