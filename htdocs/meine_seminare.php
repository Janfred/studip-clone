<?php
/*
meine_seminare.php - Anzeige der eigenen Seminare (anhaengig vom Status)
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
$perm->check("user");

ob_start(); //Outputbuffering für maximal Performance

function get_my_sem_values(&$my_sem) {
	 global $user,$loginfilenow;
	 $db2 = new DB_seminar;
	 $my_semids="('".implode("','",array_keys($my_sem))."')";
// Postings
	 $db2->query ("SELECT Seminar_id,count(*) as count FROM px_topics WHERE Seminar_id IN ".$my_semids." GROUP BY Seminar_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("Seminar_id")]["postings"]=$db2->f("count");
	 }
	 $db2->query ("SELECT a.Seminar_id,count(*) as count FROM px_topics a LEFT JOIN loginfilenow_".$user->id." b USING (Seminar_id) WHERE a.Seminar_id IN ".$my_semids." AND chdate > b.loginfilenow AND user_id !='$user->id' GROUP BY a.Seminar_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("Seminar_id")]["neuepostings"]=$db2->f("count");
	 }

//dokumente
	 $db2->query ("SELECT seminar_id , count(*) as count FROM dokumente WHERE seminar_id IN ".$my_semids." GROUP BY seminar_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("seminar_id")]["dokumente"]=$db2->f("count");
	 }
	 $db2->query ("SELECT a.seminar_id , count(*) as count  FROM dokumente a LEFT JOIN loginfilenow_".$user->id." b USING (seminar_id) WHERE a.seminar_id IN ".$my_semids." AND chdate > b.loginfilenow AND user_id !='$user->id' GROUP BY a.seminar_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("seminar_id")]["neuedokumente"]=$db2->f("count");
	 }

//News
	 $db2->query ("SELECT range_id,count(*) as count  FROM news_range  LEFT JOIN news USING(news_id) WHERE range_id IN ".$my_semids." GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["news"]=$db2->f("count");
	 }
	 $db2->query ("SELECT range_id,count(*) as count  FROM news_range LEFT JOIN news  USING(news_id)  LEFT JOIN loginfilenow_".$user->id." b ON (b.Seminar_id=range_id) WHERE range_id IN ".$my_semids." AND date > b.loginfilenow AND user_id !='$user->id' GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["neuenews"]=$db2->f("count");
	 }
// Literatur?
	 $db2->query ("SELECT range_id,chdate,user_id FROM literatur WHERE range_id IN ".$my_semids);
	while($db2->next_record()) {
	  if ($db2->f("chdate")>$loginfilenow[$db2->f("range_id")] AND $db2->f("user_id")!=$user->id){
		$my_sem[$db2->f("range_id")]["neueliteratur"]=TRUE;
		$my_sem[$db2->f("range_id")]["literatur"]=TRUE;
		}
	 else $my_sem[$db2->f("range_id")]["literatur"]=TRUE;
	 }
	 $db2->query ("SELECT range_id,count(*) as count FROM termine WHERE range_id IN ".$my_semids." GROUP BY range_id");
	 while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["termine"]=$db2->f("count");
	 }
	 $db2->query ("SELECT range_id,count(*) as count  FROM termine LEFT JOIN loginfilenow_".$user->id." b ON (b.Seminar_id=range_id) WHERE range_id IN ".$my_semids." AND chdate > b.loginfilenow AND autor_id !='$user->id' GROUP BY range_id");
	  while($db2->next_record()) {
	 $my_sem[$db2->f("range_id")]["neuetermine"]=$db2->f("count");
	 }

	 return;
}


function print_seminar_content($semid,$my_sem_values) {
  // Postings
  IF ($my_sem_values["neuepostings"])  ECHO "<a href=\"seminar_main.php?auswahl=$semid&redirect_to=forum.php&view=neue\">&nbsp; <img src='pictures/icon-posting2.gif' border=0 ".tooltip($my_sem_values["postings"]." Postings, ".$my_sem_values["neuepostings"]." Neue")."></a>";
  ELSEIF ($my_sem_values["postings"]) ECHO "<a href=\"seminar_main.php?auswahl=$semid&redirect_to=forum.php\">&nbsp; <img src='pictures/icon-posting.gif' border=0 ".tooltip($my_sem_values["postings"]." Postings")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
  //Dokumente
  IF ($my_sem_values["neuedokumente"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=folder.php&cmd=all\"><img src='pictures/icon-disc2.gif' border=0 ".tooltip($my_sem_values["dokumente"]." Dokumente, ".$my_sem_values["neuedokumente"]." neue")."></a>";
  ELSEIF ($my_sem_values["dokumente"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=folder.php&cmd=tree\"><img src='pictures/icon-disc.gif' border=0 ".tooltip($my_sem_values["dokumente"]." Dokumente")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //News
  IF ($my_sem_values["neuenews"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid\"><img src='pictures/icon-news2.gif' border=0 ".tooltip($my_sem_values["news"]." News, ".$my_sem_values["neuenews"]." neue")."></a>";
  ELSEIF ($my_sem_values["news"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid\"><img src='pictures/icon-news.gif' border=0 ".tooltip($my_sem_values["news"]." News")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //Literatur
  IF ($my_sem_values["literatur"]) {
	ECHO "<a href=\"seminar_main.php?auswahl=$semid&redirect_to=literatur.php\">";
	if ($my_sem_values["neueliteratur"])
	  ECHO "&nbsp; <img src=\"pictures/icon-lit2.gif\" border=0 ".tooltip("Zur Literatur und Linkliste (geändert)")."></a>";
		else
		  ECHO "&nbsp; <img src=\"pictures/icon-lit.gif\" border=0 ".tooltip("Zur Literatur und Linkliste")."></a>";
  }
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  // Termine
  IF ($my_sem_values["neuetermine"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=dates.php\"><img src='pictures/icon-uhr2.gif' border=0 ".tooltip($my_sem_values["termine"]." Termine, ".$my_sem_values["neuetermine"]." neue")."></a>";
  ELSEIF ($my_sem_values["termine"]) ECHO "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=dates.php\"><img src='pictures/icon-uhr.gif' border=0 ".tooltip($my_sem_values["termine"]." Termine")."></a>";
  ELSE ECHO "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  echo "&nbsp;";

} // Ende function print_seminar_content

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php");			 //hier werden die sessions initialisiert

require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");			 // Klarnamen fuer den Veranstaltungsstatus
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");			 // htmlReady fuer die Veranstaltungsnamen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");			 // Semester-Namen fuer Admins
require_once ("$ABSOLUTE_PATH_STUDIP/admission.inc.php");		//Funktionen der Teilnehmerbegrenzung

$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
$cssSw->enableHover();
$db = new DB_Seminar;

// we are defintely not in an lexture or institute
$SessSemName[0] = "";
$SessSemName[1] = "";
$links_admin_data ='';	 //Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

?>

<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
 </head>
<body>

<? echo "\n" . $cssSw->GetHoverJSFunction() . "\n";

include ("$ABSOLUTE_PATH_STUDIP/header.php");				   //hier wird der "Kopf" nachgeladen

if (!$perm->have_perm("root"))
	include ("$ABSOLUTE_PATH_STUDIP/links_seminare.inc.php");	   //hier wird die Navigation nachgeladen

//Ausgabe bei bindenden Veranstaltungen, loeschen nicht moeglich!
if ($cmd == "no_kill") {
	$db->query("SELECT Name, admission_type FROM seminare WHERE Seminar_id = '$auswahl'");
	$db->next_record();
	$meldung = "info§Die Veranstaltung <b>" . htmlReady($db->f("Name")) . "</b> ist als <b>bindend</b> angelegt. Wenn Sie sich austragen wollen, m&uuml;ssen Sie sich an den Dozenten der Veranstaltung wenden.<br />";
}

//Sicherheitsabfrage fuer abonnierte Veranstaltungen
if ($cmd == "suppose_to_kill") {
	$db->query("SELECT Name, admission_type FROM seminare WHERE Seminar_id = '$auswahl'");
	$db->next_record();
	if ($db->f("admission_type")) {
		$meldung="info§Wollen Sie das Abonnement der teilnahmebeschr&auml;nkten Veranstaltung <b>".htmlReady($db->f("Name"))."</b> wirklich aufheben? Sie verlieren damit die Berechtigung f&uuml;r die Veranstaltung und m&uuml;ssen sich neu anmelden! <br />";
		$meldung.="<a href=\"$PHP_SELF?cmd=kill&auswahl=$auswahl\"><img src=\"pictures/buttons/ja2-button.gif\" border=0 /></a>&nbsp; \n";
		$meldung.="<a href=\"$PHP_SELF\"><img src=\"pictures/buttons/nein-button.gif\" border=0 /></a>\n";
	} else {
		$cmd="kill";
	}
}

//Sicherheitsabfrage fuer Wartelisteneintraege
if ($cmd=="suppose_to_kill_admission") {
	$db->query("SELECT Name FROM seminare WHERE Seminar_id = '$auswahl'");
	$db->next_record();
	$meldung="info§Wollen Sie den Eintrag auf der Warteliste der Veranstaltung <b>".htmlReady($db->f("Name"))."</b> wirklich aufheben? Sie verlieren damit die bereits erreichte Position und m&uuml;ssen sich neu anmelden! <br />";
	$meldung.="<a href=\"$PHP_SELF?cmd=kill_admission&auswahl=$auswahl\"><img src=\"pictures/buttons/ja2-button.gif\" border=0 /></a>&nbsp; \n";
	$meldung.="<a href=\"$PHP_SELF\"><img src=\"pictures/buttons/nein-button.gif\" border=0 /></a>\n";
}

//bei Bedarf aus seminar_user austragen
if ($cmd=="kill") {
	$db->query("DELETE FROM seminar_user WHERE user_id='$user->id' AND Seminar_id='$auswahl'");
	if ($db->affected_rows() == 0)  $meldung="error§Datenbankfehler!";
	else {
	  //Pruefen, ob es Nachruecker gibt
	  update_admission($auswahl);
	  
	  $db->query("SELECT Name FROM seminare WHERE Seminar_id = '$auswahl'");
	  $db->next_record();
	  $meldung="msg§Das Abonnement der Veranstaltung <b>".$db->f("Name")."</b> wurde aufgehoben. Sie sind nun nicht mehr als Teilnehmer dieser Veranstaltung im System registriert.";
	}
}

//bei Bedarf aus admission_seminar_user austragen
if ($cmd=="kill_admission") {
	$db->query("DELETE FROM admission_seminar_user WHERE user_id='$user->id' AND seminar_id='$auswahl'");
	if ($db->affected_rows() == 0)  $meldung="error§Datenbankfehler!";
	else {
	  //Warteliste neu sortieren
	  renumber_admission($auswahl);
	  
	  $db->query("SELECT Name FROM seminare WHERE Seminar_id = '$auswahl'");
	  $db->next_record();
	  $meldung="msg§Der Eintrag in der Anmelde- bzw. Wartelistet der Veranstaltung <b>".$db->f("Name")."</b> wurde aufgehoben. Wenn Sie an der Veranstaltung teilnehmen wollen, m&uuml;ssen sie sich erneut bewerben.";
	}
}

// Update der Gruppen

	  if ($gruppesent=="1")
	  {for ($gruppe; $key = key($gruppe); next($gruppe))
			$db->query ("UPDATE seminar_user SET gruppe = '$gruppe[$key]' WHERE Seminar_id = '$key' AND user_id = '$user->id'");
	}


//Anzeigemodul fuer eigene Seminare (nur wenn man angemeldet und nicht root oder admin ist!)
IF ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("admin")){

	 //Alle fuer das Losen anstehenden Veranstaltungen bearbeiten (wenn keine anstehen wird hier nahezu keine Performance verbraten!)
	 check_admission();
	 
	 if (!isset($sortby)) $sortby="gruppe, Name";
	 if ($sortby == "count")
	 $sortby = "count DESC";
	$db->query ("SELECT seminare.Name, seminare.Seminar_id, seminar_user.status, seminar_user.gruppe, seminare.chdate, admission_binding FROM seminar_user LEFT JOIN seminare  USING (Seminar_id) WHERE seminar_user.user_id = '$user->id' GROUP BY Seminar_id ORDER BY $sortby");
	$num_my_sem=$db->num_rows();
	 if (!$num_my_sem) $meldung="info§Sie haben zur Zeit keine Veranstaltungen abonniert, in denen Sie teilnehmen k&ouml;nnen. Bitte nutzen Sie <a href=\"sem_portal.php?view=Alle&reset_all=TRUE\"><b>Veranstaltung suchen / hinzuf&uuml;gen</b></a> um neue Veranstaltungen aufzunehmen.§".$meldung;

	 ?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan="3>
				<img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b>Meine Veranstaltungen</>
			</td>
		</tr>
	<?
	if ($num_my_sem){
	?>
		 <tr>
			 <td valign="top" class="blank">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
					<? if ($meldung) {
						echo "<tr><td><br />";
						parse_msg($meldung);
						echo "</td></tr>"; 
					}
					?>
					<tr align="center">
						<td align="center">
							<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" class="blank">
								<tr>
									<td class="blank" colspan="2">&nbsp;
									</td>
								</tr>
								<tr align="center">
									<th width="2%" colspan=2 nowrap align="center">&nbsp;<a href="gruppe.php"><img src="pictures/gruppe.gif" ".tooltip("Gruppe ändern")." border="0"></a></th>
									<th width="85%" align="left"><a href="<? echo $PHP_SELF ?>?sortby=Name">Name</a></th>
									<th width="10%"><b>Inhalt</b></th>
									<? 
									if ($view=="ext") { ?>
										<th width="10%"><b>&nbsp;besucht&nbsp;</b></th>
										<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=status">&nbsp;Status&nbsp;</a></th>
										<th width="10%"><img src="pictures/nutzer.gif" alt="TeilnehmerInnen der Veranstaltung"></th>
									<?	}?>
									<th width="3%"><b>X&nbsp; </b></th>
								</tr>
	<?
	ob_end_flush(); //Buffer leeren, damit der Header zu sehen ist
	ob_start();
	 while ($db->next_record())   {
	  	$my_sem[$db->f("Seminar_id")]=array(name=>$db->f("Name"),status=>$db->f("status"),gruppe=>$db->f("gruppe"),chdate=>$db->f("chdate"), binding=>$db->f("admission_binding"));
		$value_list.="('".$db->f("Seminar_id")."',0".$loginfilenow[$db->f("Seminar_id")]."),";
	 }
	 $value_list=substr($value_list,0,-1);
	 $db->query("CREATE  TEMPORARY TABLE IF NOT EXISTS loginfilenow_".$user->id." ( Seminar_id varchar(32) NOT NULL PRIMARY KEY, loginfilenow int(11) NOT NULL DEFAULT 0, INDEX(loginfilenow) ) TYPE=HEAP");
	 $ins_query="REPLACE INTO loginfilenow_".$user->id." (Seminar_id,loginfilenow) VALUES ".$value_list;
	 $db->query($ins_query);
	 get_my_sem_values($my_sem);
	 $db->query("DROP TABLE loginfilenow_".$user->id);

  foreach ($my_sem as $semid=>$values){

		$cssSw->switchClass();
		$lastVisit = $loginfilenow[$semid];
		ECHO "<tr ".$cssSw->getHover()."><td class=gruppe";
		ECHO $values["gruppe"];
		ECHO "><a href='gruppe.php'><img src='pictures/blank.gif' ".tooltip("Gruppe ändern")." border=0 width=7 height=12></a></td>";
		ECHO "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
// Name-field		
		ECHO "<td class=\"".$cssSw->getClass()."\" ><a href=\"seminar_main.php?auswahl=$semid\">";
		if ($lastVisit <= $values["chdate"])
			print ("<font color=\"red\">");    // red color for new metadates
		ECHO "<font size=-1>".htmlReady($values["name"])."</font>";
		if ($lastVisit <= $values["chdate"])
			print ("</font>");
		print ("</a></td>");
// Content-field
		echo "<td class=\"".$cssSw->getClass()."\" align=\"left\" nowrap>";
		print_seminar_content($semid, $values);
		echo "</td>";


// Extendet views:

	// last visited-field
		IF ($view=="ext") {
			IF ($loginfilenow[$semid]==0) {
				echo "<td class=".$cssSw->getClass()."  align=\"center\" nowrap><font size=-1>n.b.</font></td>";
			}
			ELSE {
				 echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>", date("d.m.", $loginfilenow[$semid]),"</font></td>";
			}
	// Status-field
			echo "<td class=\"".$cssSw->getClass()."\"  align=\"center\" nowrap><font size=-1>". $values["status"]."&nbsp;</font></td>";
	// Teilnehmer
			$db2=new DB_Seminar;
			$db2->query ("SELECT count(*) as teilnehmer FROM seminar_user WHERE Seminar_id ='$semid'");
			 while($db2->next_record()) 
				 echo "<td class=\"".$cssSw->getClass()."\"  nowrap align=\"right\"><font size=-1>". $db2->f("teilnehmer")."&nbsp;</font></td>";
		}


// delete Entry from List:

		if (($values["status"]=="dozent") || ($values["status"]=="tutor")) 
			echo "<td class=\"".$cssSw->getClass()."\"  align=center>&nbsp;</td>";
		elseif ($values["binding"]) //anderer Link und andere Tonne wenn Veranstaltungszuordnung bindend ist.
			printf("<td class=\"".$cssSw->getClass()."\"  align=center nowrap><a href=\"$PHP_SELF?auswahl=%s&cmd=no_kill\"><img src=\"pictures/lighttrash.gif\" ".tooltip("Das Abonnement ist bindend. Bitte wenden sie sich an den Dozenten der Veranstaltung, um sich austragen zu lassen.")." border=\"0\"></a>&nbsp; </td>", $semid);
		else
			printf("<td class=\"".$cssSw->getClass()."\"  align=center nowrap><a href=\"$PHP_SELF?auswahl=%s&cmd=suppose_to_kill\"><img src=\"pictures/trash.gif\" ".tooltip("aus der Veranstaltung abmelden")." border=\"0\"></a>&nbsp; </td>", $semid);			
		 echo "</tr>\n";
		}
	 echo "</table></td></tr>";
	 } else {
	 ?>
	 <tr>
		 <td valign="top" class="blank">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
				<?
				if ($meldung)	{
					echo "<tr><td><br />";
					parse_msg($meldung);
					echo "</td></tr>"; 
				}
	}
	?>
	<tr>
		<td class="blank" colspan=2>&nbsp;
		</td>
	</tr>
	<?

// Anzeige der Wartelisten

	  $db->query("SELECT admission_seminar_user.*, seminare.Name, seminare.admission_endtime, seminare.admission_turnout, quota FROM admission_seminar_user LEFT JOIN seminare USING(seminar_id) LEFT JOIN admission_seminar_studiengang ON (admission_seminar_user.studiengang_id = admission_seminar_studiengang.studiengang_id AND seminare.seminar_id = admission_seminar_studiengang.seminar_id) WHERE user_id = '$user->id' ORDER BY admission_type, name");
	  IF ($db->num_rows()) {
		?>
	<tr>
		<td class="blank">
			<b><br>&nbsp; Anmelde- und Wartelisteneintr&auml;ge:</b><br />&nbsp; 
		</td>
	</tr>
	<tr>
		<td valign="top" class="blank" align="center">
		<?
		ECHO "<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"98%\" align=\"center\" class=\"blank\">";
		ECHO "<tr>";
			ECHO "<th width=\"2%\" nowrap colspan=2>&nbsp</th>";
			ECHO "<th width=\"65%\" align=\"left\"><b>Name</b></th>";
			ECHO "<th width=\"10%\"><b>Datum</b></th>";
			ECHO "<th width=\"10%\" nowrap><b>Position/Chance</b></th>";
			ECHO "<th width=\"10%\"><b>Art</b></th>";
			ECHO "<th width=\"3%\">X&nbsp; </tr></th>";

	WHILE ($db->next_record()) {
		IF ($db->f("status") == "claiming") { // wir sind in einer Anmeldeliste und brauchen Prozentangaben
				 $db2=new DB_Seminar;
				 $admission_studiengang_id = $db->f("studiengang_id");
				 $admission_seminar_id = $db->f("seminar_id");
				 $plaetze = round ($db->f("admission_turnout") * ($db->f("quota") / 100));  // Anzahl der Plaetze in dem Studiengang in den ich will
				 $db2->query("SELECT count(*) AS wartende FROM admission_seminar_user WHERE seminar_id = '$admission_seminar_id' AND studiengang_id = '$admission_studiengang_id'");
				 IF ($db2->next_record()) {
					$wartende = ($db2->f("wartende"));   // Anzahl der Personen die auch in diesem Studiengang auf einen Platz lauern
				 }
				 IF ($plaetze >= $wartende) $admission_chance = 100;   // ich komm auf jeden Fall rein
				 ELSE $admission_chance = round (($plaetze / $wartende) * 100); // mehr Bewerber als Plaetze
				 $chance_color = dechex(255-(200-($admission_chance*2)));  // Gruen der Farbe nimmt mit Wahrscheinlichkeit ab
		}
		ELSE {  // wir sind in einer Warteliste
			IF ($db->f("position") >= 30) $chance_color = 44; // das wird wohl nix mehr mit nachrücken
			ELSE $chance_color = dechex(255-($db->f("position")*6)); // da gibts vielleicht noch Hoffnung, also grün
		}
	
	$cssSw->disableHover();
	$cssSw->switchClass();
	printf ("<tr><td width=\"1%%\" bgcolor=\"#44%s44\"><img src='pictures/blank.gif' alt='Position oder Wahrscheinlichkeit' border=0 width=7 height=12></td>",$chance_color);
	printf ("<td width=\"1%%\" class=\"%s\">&nbsp;</td>",$cssSw->getClass());
	printf ("<td width=\"55%%\" class=\"%s\">",$cssSw->getClass());
	print "<a href=details.php?sem_id=".$db->f("seminar_id")."><font size=-1>".$db->f("Name")."</font></a></td>";
	printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td>", $cssSw->getClass(), ($db->f("status") == "claiming") ? date("d.m.", $db->f("admission_endtime")) : "-");
	printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s %s</font></td>",$cssSw->getClass(), ($db->f("status") == "claiming") ? $admission_chance : $db->f("position"), ($db->f("status") == "claiming") ? "%" : "");
	printf ("<td width=\"10%%\" align=\"center\" class=\"%s\"><font size=-1>%s</font></td>", $cssSw->getClass(),  ($db->f("status") == "claiming") ? "Los" : "chronolg.");
	printf("<td width=\"3%%\" class=\"%s\" align=\"center\"><a href=\"$PHP_SELF?auswahl=%s&cmd=%skill_admission\"><img src=\"pictures/trash.gif\" ".tooltip("aus der Veranstaltung abmelden")." border=\"0\"></a>&nbsp; </td></tr>", $cssSw->getClass(), $db->f("seminar_id"), ($db->f("status") == "awaiting") ? "suppose_to_" : "");
	}
	print "</table></td>";
?>
		<td class="blank">
			&nbsp;&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>&nbsp;
		</td>
	</tr>
<?	
}	
 // Ende Wartelisten
 

//Info-field on the right side
?>
		</table>
			</td>
			<td class="blank">
				&nbsp;&nbsp;
			</td>
			<td class="blank" width="240" valign="top">
				<table "center" width="100%" border=0 cellpadding=0 cellspacing=0>
					<tr>
						<td class="blank" width="100%" align="right" colspan=2>
							<img src="pictures/seminare.jpg">
						</td>
					</tr>
					<tr>
						<td class="angemeldet" width="100%" colspan=2>
							<table "center" width="99%" border=0 cellpadding=4 cellspacing=0>
								<tr>
									<td class="blank" width="100%" colspan=2>
										<font size=-1><b><? print "Information" ?>:</b></font>
										<br>
									</td>
								</tr>
								<tr>
									<td width="1%" valign="top">
										<img src="./pictures/ausruf_small.gif">
									</td>
									<td class="blank" width="100%">
										 <? $db->query("SELECT count(*) as count  FROM seminare");
										$db->next_record(); ?>
										<font size=-1>Es sind noch <? echo ($db->f("count")-$num_my_sem) ?> weitere Veranstaltungen vorhanden.</font><br>
									</td>
								</tr>
								<tr>
									<td class="blank" width="100%" colspan=2>
										<font size=-1><b><? print "Aktionen" ?>:</b></font>
										<br>
									</td>
								</tr>
								<tr>
									<td width="1%" valign="top">
										<img src="./pictures/suchen.gif">
									</td>
									<td class="blank" width="100%">
										<font size=-1>Um weitere Veranstaltungen in Ihre pers&ouml;nliche Auswahl aufzunehmen, nutzen Sie bitte die <a href="sem_portal.php?view=Alle&reset_all=TRUE">Suchfunktion.</a></font>
									</td>
								</tr>
							<?  if ($perm->have_perm("dozent")) {  ?>
								<tr>
									<td width="1%" valign="top">
										<img src="./pictures/admin.gif">
									</td>
									<td class="blank" width="100%">
										<? echo "<font size=-1>Um Veranstaltungen anzulegen, nutzen Sie bitte den <a href=\"admin_seminare_assi.php?new_session=TRUE\">Veranstaltungs-Assistenten</a>.</font><br><br>";?>
									</td>
								</tr>  
							 <? }  ?>
							</table>
						</td>
					</tr>
				</table>
				<br />
			</td>
		</tr>
	  </table>
     </td>
    </tr>


<?
}


ELSEIF ($auth->auth["perm"]=="admin"){

	   if (!isset($sortby)) $sortby="Institut, start_time, Name";
	   if ($sortby == "teilnehmer")
	   $sortby = "teilnehmer DESC";
	   $db->query("SELECT Institute.Name AS Institut, seminare.*, COUNT(seminar_user.user_id) AS teilnehmer FROM user_inst LEFT JOIN Institute USING (Institut_id) LEFT JOIN seminare USING(Institut_id) LEFT OUTER JOIN seminar_user USING(Seminar_id) WHERE user_inst.inst_perms='admin' AND user_inst.user_id='$user->id' AND seminare.Institut_id is not NULL GROUP BY seminare.Seminar_id ORDER BY $sortby");
	   $num_my_sem=$db->num_rows();
	   if (!$num_my_sem) $meldung="msg§Sie haben keine Veranstaltungen!§".$meldung;
	 ?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b>Veranstaltungen an meinen Einrichtungen</></td>
	</tr>
	<tr>
		<td class="blank" width="100%" colspan=2>&nbsp;
			<?
			if ($meldung) parse_msg($meldung);
			?>
		</td>
	</tr>
	 <?
	 if ($num_my_sem) {
	 ?>
	<tr>
		<td class="blank" colspan=2>
			<table border="0" cellpadding="0" cellspacing="0" width="99%" align="center" class=blank>
				<tr valign"top" align="center">
					<th width="50%" colspan=2><a href="<? echo $PHP_SELF ?>?sortby=Name">Name</a></th>
					<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=status">Status</a></th>
					<th width="15%"><b>Dozent</b></th>
					<th width="10%"><b>Inhalt</b></th>
					<th width="10%"><a href="<? echo $PHP_SELF ?>?sortby=teilnehmer">Teilnehmer</a></th>
					<th width="5%"><b>&nbsp; </b></th>
				</tr>
	<?
  $db2=new DB_Seminar;

	while ($db->next_record()){
	$my_sem[$db->f("Seminar_id")]=array(institut=>$db->f("Institut"),teilnehmer=>$db->f("teilnehmer"),name=>$db->f("Name"),status=>$db->f("status"),chdate=>$db->f("chdate"),start_time=>$db->f("start_time"), binding=>$db->f("admission_binding"));
		$value_list.="('".$db->f("Seminar_id")."',0".$loginfilenow[$db->f("Seminar_id")]."),";
	}
	$value_list=substr($value_list,0,-1);
	 $db->query("CREATE TEMPORARY TABLE IF NOT EXISTS  loginfilenow_".$user->id." ( Seminar_id varchar(32) NOT NULL PRIMARY KEY, loginfilenow int(11) NOT NULL DEFAULT 0 ) TYPE=HEAP");
	 $ins_query="REPLACE INTO loginfilenow_".$user->id." (Seminar_id,loginfilenow) VALUES ".$value_list;
	$db->query($ins_query);
	get_my_sem_values(&$my_sem);
	$db->query("DROP TABLE loginfilenow_".$user->id);
	$cssSw->enableHover();
	foreach ($my_sem as $semid=>$values){
		$cssSw->switchClass();
		$class = $cssSw->getClass();
		
		$lastVisit = $loginfilenow[$semid];
		
		echo "<tr ".$cssSw->getHover()."><td class=\"$class\">&nbsp;&nbsp;</td>";
		ECHO "<td class=\"$class\"><a href=\"seminar_main.php?auswahl=$semid\">";
		if ($lastVisit <= $values["chdate"])
			print ("<font color=\"red\">");
		ECHO htmlReady($values["name"]);
		echo " (" . get_sem_name($values["start_time"]) .")";
		if ($lastVisit <= $values["chdate"])
			print ("</font>");
		print ("</a></td>");

		ECHO "<td class=\"$class\" align=\"center\">&nbsp;" . $SEM_TYPE[$values["status"]]["name"] . "&nbsp;</td>";
// Dozenten
		$db2->query ("SELECT Vorname, Nachname, username FROM  seminar_user LEFT JOIN auth_user_md5  USING (user_id) WHERE Seminar_id='$semid' AND status='dozent'");
		$temp = "";
		while ($db2->next_record()) {
			$temp .= "<a href=\"about.php?username=" . $db2->f("username") . "\">" . $db2->f("Nachname") . "</a>, ";
		}
		$temp = substr($temp, 0, -2);
		print ("<td class=\"$class\" align=\"center\">&nbsp;$temp</td>");

// Inhalt
		echo "<td class=\"$class\" align=\"left\" nowrap>";
		print_seminar_content($semid, $values);
		echo "</td>";

		echo "<td class=\"$class\" align=\"center\" nowrap>". $values["teilnehmer"]."&nbsp;</td>";
		printf("<td class=\"$class\" align=center align=center><a href=\"seminar_main.php?auswahl=$semid&redirect_to=adminarea_start.php&new_sem=TRUE\"><img src=\"pictures/admin.gif\" ".tooltip("Veranstaltungsdaten bearbeiten")." border=\"0\"></a></td>", $semid);
		 echo "</tr>\n";
		}
	echo "		</table>
			</td>
		</tr>";

	 }

?>
	<tr>
	<td class="blank" colspan=2>&nbsp;</td>
	</tr>
<?
}

ELSEIF ($perm->have_perm("root")){


//Anzeigemodul fuer alle Seminare für root
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&Uuml;bersicht &uuml;ber Veranstaltungen</></td>
		</tr>
		<tr>
			<td class="blank" align = left colspan=2><br /><blockquote>
				Um eine Veranstaltung zu bearbeiten, w&auml;hlen Sie sie &uuml;ber die Suchfunktion aus.
			</blockquote>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
	<?
		$root_mode=TRUE;
		$target_url="seminar_main.php";	//teilt der nachfolgenden Include mit, wo sie die Leute hinschicken soll
		$target_id="auswahl";			 //teilt der nachfolgenden Include mit, wie die id, die uebergeben wird, bezeichnet werden soll

		include "sem_browse.inc.php";		 //der zentrale Seminarbrowser wird hier eingef&uuml;gt.

	?>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
				<blockquote>Um eine neue Veranstaltung anzulegen, benutzen Sie bitte den&nbsp; <a href="admin_seminare_assi.php?new_session=TRUE">Veranstaltungs-Assistenten</a><br>
				</blockquote>
			</td>
		</tr>
	</table>
<?
}
?>
</table>
</body>
</html>
<?
  // Save data back to database.
ob_end_flush(); //Outputbuffering beenden
page_close();
  ?>
<!-- $Id$ -->