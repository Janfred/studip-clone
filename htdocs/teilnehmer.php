<?php
/*
teilnehmer.php - Anzeige der Teilnehmer eines Seminares
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

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
?>

<html>
<head>
<?IF (!isset($SessSemName[0]) || $SessSemName[0] == "") {
    echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
    echo "</head></html>";
    die;
}
?>

<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<body bgcolor=white>

<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren --

	require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
	require_once ("$ABSOLUTE_PATH_STUDIP/admission.inc.php");	//Funktionen der Teilnehmerbegrenzung	
    $cssSw=new cssClassSwitcher;
	include "header.php";   //hier wird der "Kopf" nachgeladen
	include "links1.php";

if ($sms_msg)
	$msg=rawurldecode($sms_msg);

IF ($SessSemName[1] =="")
	{
	parse_window ("error§Sie haben keine Veranstaltung gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher eine Veranstaltung gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Keine Veranstaltung gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}

// Aenderungen nur in dem Seminar, in dem ich gerade bin...
	$id=$SessSemName[1];

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;

echo "<table cellspacing=0 border=0 width=\"100%\">";
echo "<tr><td class=blank colspan=2>&nbsp;</td></tr>";

// Hier will jemand die Karriereleiter rauf...

if ($cmd=="pleasure") {
	//erst mal sehen, ob er hier wirklich Dozent ist... Tutoren d&uuml;rfen andere nicht zu Tutoren befoerdern!
	if ($rechte AND $SemUserStatus!="tutor")  {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username' AND perms!='user' AND perms!='autor'");
		if ($db->next_record()) {
			$userchange=$db->f("user_id");
			$db->query("UPDATE seminar_user SET status='tutor' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
			$msg = "msg§Bef&ouml;rderung von ".$db->f("Vorname")." ". $db->f("Nachname")." durchgef&uuml;hrt§";
		}
		else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

// jemand ist der anspruchsvollen Aufgabe eines Tutors nicht gerecht geworden...

if ($cmd=="pain") {
	//erst mal sehen, ob er hier wirklich Dozent ist... Tutoren d&uuml;rfen andere Tutoren nicht rauskicken!
	if ($rechte AND $SemUserStatus!="tutor") {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
		$msg = "msg§Der Tutor ".$db->f("Vorname")." ". $db->f("Nachname")." wurde entlassen und auf Autor zur&uuml;ckgestuft.§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

// jemand ist zu bl&ouml;de, sein Seminar selbst zu abbonieren...

if ($cmd=="schreiben") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username' AND perms != 'user'");
		if ($db->next_record()) {
			$userchange=$db->f("user_id");
			$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
			$msg = "msg§Der User ".$db->f("Vorname")." ". $db->f("Nachname")." wurde als Autor in die Veranstaltung aufgenommen.§";
		}
		else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

// jemand sollte erst mal das Maul halten...

if ($cmd=="lesen") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$db->query("UPDATE seminar_user SET status='user' WHERE Seminar_id = '$id' AND user_id = '$userchange'");
		$msg = "msg§Der Autor ".$db->f("Vorname")." ". $db->f("Nachname")." wurde auf Leser zur&uuml;ckgestuft.§";
		$msg.= "info§Um jemanden permanent am Schreiben zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Schreiben nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.<br>\n"
				."Dann k&ouml;nnen sich weitere Benutzer nur noch mit Kenntnis des Veranstaltungs-Passworts als Autor anmelden.§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

// und tschuess...

if ($cmd=="raus") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$db->query("DELETE FROM seminar_user WHERE Seminar_id = '$id' AND user_id = '$userchange'");
		
		//Pruefen, ob es Nachruecker gibt
		update_admission($auswahl);

		$msg = "msg§Der Leser ".$db->f("Vorname")." ". $db->f("Nachname")." wurde aus der Veranstaltung entfernt.§";
		$msg.= "info§Um jemanden permanent am Lesen zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Lesen nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.<br>\n"
				."Dann k&ouml;nnen sich weitere Benutzer nur noch mit Kenntnis des Veranstaltungs-Passworts als Autor anmelden.§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

//aus der Anmelde- oder Warteliste entfernen
if ($cmd=="admission_raus") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$db->query("DELETE FROM admission_seminar_user WHERE seminar_id = '$id' AND user_id = '$userchange'");
		
		$msg = "msg§Der Leser ".$db->f("Vorname")." ". $db->f("Nachname")." wurde aus der Anmelde bzw. Warteliste entfernt.§";
		$msg.= "info§Um jemanden permanent am Lesen zu hindern, m&uuml;ssen Sie die Veranstaltung auf \"Lesen nur mit Passwort\" setzen und ein Veranstaltungs-Passwort vergeben.<br>\n"
				."Dann k&ouml;nnen sich weitere Benutzer nur noch mit Kenntnis des Veranstaltungs-Passworts als Autor anmelden.§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

//aus der Anmelde- oder Warteliste in die Veranstaltung hochstufen
if ($cmd=="admission_rein") {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte) {
		$db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
		$db->next_record();
		$userchange=$db->f("user_id");
		$db->query("INSERT INTO seminar_user SET Seminar_id = '$id', user_id = '$userchange', status= 'autor' ");
		if ($db->affected_rows())
			$db->query("DELETE FROM admission_seminar_user WHERE seminar_id = '$id' AND user_id = '$userchange'");
		
		$msg = "msg§Der Leser ".$db->f("Vorname")." ". $db->f("Nachname")." wurde aus der Anmelde bzw. Warteliste in die Veranstaltung hochgestuft.§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}


// so bin auch ich berufen?

if (isset($berufen)) {
	//erst mal sehen, ob er hier wirklich Dozent ist...
	if ($rechte AND $SemUserStatus!="tutor") {
				// nur wenn wer ausgewaehlt wurde
		if ($u_id != "0") {
			$db->query("SELECT Vorname, Nachname FROM user_inst NATURAL LEFT JOIN auth_user_md5 WHERE Institut_id = '$SessSemName[5]' AND user_inst.user_id = '$u_id' AND (inst_perms = 'tutor' OR inst_perms = 'dozent')");
				// wer versucht denn da wen nicht zugelassenen zu berufen?
			if ($db->next_record()) {
				// so, Berufung ist zulaessig
				$db2->query("SELECT status FROM seminar_user WHERE Seminar_id = '$id' AND user_id = '$u_id'");
				if ($db2->next_record()) {
					// der Dozent hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Seminar. Na, auch egal...
					if ($db2->f("status") == "autor" || $db2->f("status") == "user") {
						// gehen wir ihn halt hier hochstufen
						$db2->query("UPDATE seminar_user SET status='tutor' WHERE Seminar_id = '$id' AND user_id = '$u_id'");
						$msg = "msg§".$db->f("Vorname")." ". $db->f("Nachname")." wurde zum Tutor bef&ouml;rdert..§";
					} else {
						;	// na, das ist ja voellig witzlos, da tun wir einfach nix.
							// Nicht das sich noch ein Dozent auf die Art und Weise selber degradiert!
					}
				} else {  // ok, einfach aufnehmen.
					$db3->query("SELECT start_time FROM seminare WHERE Seminar_id = '$id' ");
					$db->next_record();
					$group=select_group ($db3->f("start_time"), $u_id);
					$db2->query("INSERT into seminar_user (Seminar_id, user_id, status, gruppe) values ('$id', '$u_id', 'tutor','$group' )");
					$msg = sprintf ("msg§%s wurde als Tutor in die Veranstaltung aufgenommen.</b>", get_fullname($u_id));
				}
			}
			else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
		}
		else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
	}
	else $msg ="error§Netter Versuch! vielleicht beim n&auml;chsten Mal!§";
}

//Alle fuer das Losen anstehenden Veranstaltungen bearbeiten (wenn keine anstehen wird hier nahezu keine Performance verbraten!)
check_admission();
 

$gruppe = array ("dozent" => "Dozenten",
		  "tutor" => "Tutoren",
		  "autor" => "Autoren",
		  "user" => "Leser");
?>

<tr>
        <td class="topic" colspan=2><b>&nbsp;<? echo $SessSemName["art"],": ",htmlReady($SessSemName[0]); ?> - Teilnehmer</b></td>
</tr>
	<tr>
		<td class="blank" width="100%" colspan=2>&nbsp;
			<?
			if ($msg) parse_msg($msg);
			?>
		</td>
	</tr>
<tr>
	<td class="blank" colspan=2>
	
	<table width="99%" border="0"  cellpadding="2" cellspacing="0" align="center">

<?
//Veranstaltungsdaten holen
$db3->query ("SELECT admission_type, admission_selection_take_place FROM seminare WHERE Seminar_id = '$SessionSeminar'");
$db3->next_record();

while (list ($key, $val) = each ($gruppe)) {

if (!isset($sortby) || $sortby=="") 
	$sortby = "doll DESC";

$db->query ("SELECT seminar_user.user_id, Vorname, Nachname, username, status, count(topic_id) AS doll,  studiengaenge.name, admission_studiengang_id AS studiengang_id FROM seminar_user LEFT JOIN px_topics USING (user_id,Seminar_id) LEFT JOIN auth_user_md5 ON (seminar_user.user_id=auth_user_md5.user_id) LEFT JOIN studiengaenge ON (seminar_user.admission_studiengang_id = studiengaenge.studiengang_id) WHERE seminar_user.Seminar_id = '$SessionSeminar' AND status = '$key'  GROUP by seminar_user.user_id ORDER BY $sortby");

if ($db->num_rows()) { //Only if Users were found...
	// die eigentliche Teil-Tabelle
	echo "<tr height=28>";
	printf ("<td class=\"steel\" width=\"30%%\" align=\"left\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b><a href=%s?sortby=Nachname>%s</a></b></font></td>", $PHP_SELF, $val);
	printf ("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=-1><b><a href=%s>Postings</a></b></font></td>", $PHP_SELF);
	echo "<td class=\"steel\" width=\"10%\" align=\"center\"><font size=-1><b>Dokumente</b></font></td>";
	echo "<td class=\"steel\" width=\"10%\" align=\"center\"><font size=-1><b>Nachricht</b></font></td>";
	//echo "<td class=\"steel\" width=\"10%\"><b>Literatur</b></td>";

	if ($rechte) {

		if ($db3->f("admission_type"))
			$width=15;
		else
			$width=20;
						
 		if ($key == "dozent") {
			echo"<td class=\"steel\" width=\"$width%\" align=center><b>&nbsp;</b></td>";
			echo"<td class=\"steel\" width=\"$width%\" align=center><b>&nbsp;</b></td>";
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=center><b>&nbsp;</b></td>";
		}

		if ($key == "tutor") {
			echo"<td class=\"steel\" width=\"$width%\" align=center><font size=-1><b>&nbsp;</b></font></td>";
			echo"<td class=\"steel\" width=\"$width%\" align=center><font size=-1><b>Tutor entlassen</b></font></td>";
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=center><b>&nbsp;</b></td>";
		}
		
		if ($key == "autor") {
			echo"<td class=\"steel\" width=\"$width%\" align=center><font size=-1><b>als Tutor eintragen</b></font></td>";
			echo"<td class=\"steel\" width=\"$width%\" align=center><font size=-1><b>Schreibrecht entziehen</b></font></td>";
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=center><font size=-1><b>Kontingent</b></font></td>";
		}

		if ($key == "user") {
			echo"<td class=\"steel\" width=\"$width%\" align=center><font size=-1><b>Schreibrecht erteilen</b></font></td>";
			echo"<td class=\"steel\" width=\"$width%\" align=center><font size=-1><b>Benutzer entfernen</b></font></td>";
			if ($db3->f("admission_type"))
				echo"<td class=\"steel\" width=\"10%\" align=center><b>&nbsp;</b></td>";
		}		
	}
	
	echo "</tr>";

	$c=1;
	while ($db->next_record()) {
  	if ($c % 2)
  		$class="steel1";
	else
		$class="steelgraulight"; 
	$c++;

	print("<tr><td class=\"$class\">");
	print( "<font size=-1><a href = about.php?username=" . $db->f("username") . ">");
	print(htmlReady($db->f("Vorname")) ." ". htmlReady($db->f("Nachname")) ."</a>");
	print("</font></td><td class=\"$class\" align=center><font size=-1>");
	print( $db->f("doll"));
	print("</font></td><td class=\"$class\" align=center><font size=-1>");

	$Dokumente = 0;
	$UID = $db->f("user_id");
	$db2->query ("SELECT count(dokument_id) AS doll FROM dokumente WHERE seminar_id = '$SessionSeminar' AND user_id = '$UID' GROUP by seminar_id");
	while ($db2->next_record()) {
		$Dokumente = $db2->f("doll");
	}
	print $Dokumente;
	print("</font></td>");
	
	printf ("<td class=\"$class\" align=center>");
	printf ("<a href=\"sms.php?sms_source_page=teilnehmer.php&cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\"></a>", $db->f("username")); 
	printf ("</td>");

// Befoerderungen und Degradierungen
	$username=$db->f("username");
	if ($rechte) {

		// Tutor entlassen	
		if ($key == "tutor" AND $SemUserStatus!="tutor") {
			echo "<td class=\"$class\">&nbsp</td>";
			echo "<td class=\"$class\" align=center>";
			echo "<a href=\"$PHP_SELF?cmd=pain&username=$username\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		}

		elseif ($key == "autor") {
			// zum Tutor befördern
			if ($SemUserStatus!="tutor") {
				$db2->query ("SELECT inst_perms, user_id, Institut_id FROM user_inst WHERE user_id = '$UID' AND Institut_id = '$SessSemName[5]' AND inst_perms!='user' AND inst_perms!='autor'");		
				if ($db2->next_record()) {
					echo "<td class=\"$class\" align=center>";
					echo "<a href=\"$PHP_SELF?cmd=pleasure&username=$username\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>";
				} else echo "<td class=\"$class\" >&nbsp;</td>";
			} else echo "<td class=\"$class\">&nbsp;</td>";
			// Schreibrecht entziehen
			echo "<td class=\"$class\" align=center>";
			echo "<a href=\"$PHP_SELF?cmd=lesen&username=$username\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		}

		// Schreibrecht erteilen
		elseif ($key == "user") {
			$db2->query ("SELECT perms, user_id FROM auth_user_md5 WHERE user_id = '$UID' AND perms != 'user'");		
			if ($db2->next_record()) { // Leute, die sich nicht zurueckgemeldet haben duerfen auch nicht schreiben!
				echo "<td class=\"$class\" align=center>";
				echo "<a href=\"$PHP_SELF?cmd=schreiben&username=$username\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>";
			} else echo "<td class=\"$class\">&nbsp;</td>";
			// aus dem Seminar werfen
			echo "<td class=\"$class\" align=center>";
			echo "<a href=\"$PHP_SELF?cmd=raus&username=$username\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></td>";
		} 
		
		else { // hier sind wir bei den Dozenten
			echo "<td class=\"$class\" >&nbsp;</td>";
			echo "<td class=\"$class\">&nbsp;</td>";
		}
		
		if ($db3->f("admission_type")) {
			if ($key== "autor" || $key== "user")
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td>", $class, ($db->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db->f("name"));
			else
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\">&nbsp;</td>", $class);
		}
			
	} // Ende der Dozenten/Tutorenspalten


	print("</tr>\n");
} // eine Zeile zuende

if ($rechte) {
	if ($db3->f("admission_type"))
		$colspan=7;
	else
		$colspan=6;
} else
	$colspan=4;

	echo "<tr><td class=blank colspan=\"$colspan\">&nbsp;</td></tr>";

} // eine Gruppe zuende
}
echo "</table>\n";

echo "</td></tr>\n";  // Auflistung zuende

// Warteliste
if ($rechte) {
	$db->query ("SELECT admission_seminar_user.user_id, Vorname, Nachname, username, studiengaenge.name, position, admission_seminar_user.studiengang_id, status FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN studiengaenge ON (admission_seminar_user.studiengang_id=studiengaenge.studiengang_id)  WHERE admission_seminar_user.seminar_id = '$SessionSeminar' ORDER BY position, name");
	if ($db->num_rows()) { //Only if Users were found...

		// die eigentliche Teil-Tabelle
	 	echo "<tr><td class=\"blank\" colspan=2>";
		echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
		echo "<tr height=28>";
		printf ("<td class=\"steel\" width=\"%s%%\" align=\"left\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>%s</b></font></td>", ($db3->f("admission_type") == 1 && $db3->f("admission_selection_take_place") !=1) ? "40" : "30",  ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1) ? "Warteliste" : "Anmeldeliste");
		if ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1)
			printf ("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=-1><b>Position</b></font></td>");
		printf ("<td class=\"steel\" width=\"10%%\" align=\"center\">&nbsp; </td>");
		printf ("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=-1><b>Nachricht</b></font></td>");
		printf ("<td class=\"steel\" width=\"15%%\" align=\"center\"><font size=-1><b>eintragen</b></font></td>");
		printf ("<td class=\"steel\" width=\"15%%\" align=\"center\"><font size=-1><b>entfernen</b></font></td>");
		printf ("<td class=\"steel\" width=\"10%%\" align=\"center\"><font size=-1><b>Kontingent</b></font></td></tr>");
		

		WHILE ($db->next_record()) {
			IF ($db->f("status") == "claiming") { // wir sind in einer Anmeldeliste und brauchen Prozentangaben
				$db2=new DB_Seminar;
				$admission_studiengang_id = $db->f("studiengang_id");
				$admission_seminar_id = $db->f("seminar_id");
				$plaetze = round ($db->f("admission_turnout") * ($db->f("quota") / 100));  // Anzahl der Plaetze in dem Studiengang in den ich will
				$db2->query("SELECT count(*) AS wartende FROM admission_seminar_user WHERE seminar_id = '$admission_seminar_id' AND studiengang_id = '$admission_studiengang_id'");
				IF ($db2->next_record())
					$wartende = ($db2->f("wartende"));   // Anzahl der Personen die auch in diesem Studiengang auf einen Platz lauern
	        	         IF ($plaetze >= $wartende) 
        		         	$admission_chance = 100;   // ich komm auf jeden Fall rein
				ELSE 
					$admission_chance = round (($plaetze / $wartende) * 100); // mehr Bewerber als Plaetze
			}
		
			$cssSw->switchClass(); 
			printf ("<tr><td width=\"%s%%\" class=\"%s\" align=left><font size=-1><a href=\"about.php?username=%s\">%s&nbsp;%s</a></font></td>",  ($db3->f("admission_type") == 1 && $db3->f("admission_selection_take_place") !=1) ? "40" : "30", $cssSw->getClass(), $db->f("username"), $db->f("Vorname"), $db->f("Nachname"));
			if ($db3->f("admission_type") == 2 || $db3->f("admission_selection_take_place")==1)
				printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td>", $cssSw->getClass(), $db->f("position"));
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\">&nbsp; </td>", $cssSw->getClass());
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><a href=\"sms.php?sms_source_page=teilnehmer.php&cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\"></a></td>",$cssSw->getClass(), $db->f("username")); 
			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_rein&username=%s\"><img border=\"0\" src=\"pictures/up.gif\" width=\"21\" height=\"16\"></a></td>", $cssSw->getClass(), $db->f("username"));
			printf ("<td width=\"15%%\" align=\"center\" class=\"%s\"><a href=\"$PHP_SELF?cmd=admission_raus&username=%s\"><img border=\"0\" src=\"pictures/down.gif\" width=\"21\" height=\"16\"></a></tr>", $cssSw->getClass(), $db->f("username"));
			printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td></tr>", $cssSw->getClass(), ($db->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db->f("name"));
		}
		print "</table>";
	}
}





// Der Dozent braucht mehr Unterstuetzung, also Tutor aus dem Institut berufen...
if ($rechte AND $SemUserStatus!="tutor") {
?>

	<tr>
		<td class=blank colspan=2>&nbsp; 
		</td>
	</tr>
	<tr><td class=blank colspan=2>

	<table width="99%" border="0" cellpadding="2" cellspacing="0" border=0 align="center">
	<form action="<? echo $PHP_SELF ?>" method="POST">
	<tr>
		<td class="steel1" width="30%" align="left">&nbsp; <font size=-1><b>Mitarbeiter der Enrichtung</b></font></td>
		<td class="steel1" width="40%" align="center"><SELECT Name="u_id" size="1">
		<?
		$db->query("SELECT auth_user_md5.user_id, username, Vorname, Nachname, inst_perms FROM user_inst NATURAL LEFT JOIN auth_user_md5 WHERE Institut_id = '$SessSemName[5]' AND (inst_perms = 'tutor' OR inst_perms = 'dozent') ORDER BY Nachname");
		printf ("<option value=\"0\">- -  bitte ausw&auml;hlen - -\n");
		while ($db->next_record())
			printf ("<option value=\"%s\">%s - %s\n", $db->f("user_id"), my_substr($db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username"),0,40).")", $db->f("inst_perms"));
		?>
		</select></td>
		<td class="steel1" width="30%" align=center><font size=-1>als Tutor</font><br />
		<input type="IMAGE" name="add_tutor" src="./pictures/buttons/eintragen-button.gif" border=0 value=" Als Tutor berufen "></td>
	</tr></form></table>
<?

} // Ende der Berufung

echo "</td></tr></table>";

// Save data back to database.
page_close()
?>
</body>
</html>