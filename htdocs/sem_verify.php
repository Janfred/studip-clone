<?
/**
* sem_verify.php
* 
* checks the entry to a Veranstaltung an insert user to the seminar_user table
* 
*
* @author		André Noack <noack@data-quest.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		sem_verify.php
* @modulegroup	misc
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sem_verify.php
// Ueberprueft Zutrittsvorausetzungen fuer Veranstaltungen und traegt Nutzer in die Tabelle seminar_user ein
// Copyright (C) 2002 André Noack <noack@data-quest.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$_language_domain = "studip_core";
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
?>
<script type="text/javascript" language="javascript" src="md5.js"></script>
<script type="text/javascript" language="javascript">
  <!--
  function verifySeminar() {
      document.details.hashpass.value = MD5(document.details.pass.value);
      document.details.pass.value = "";
  }
  // -->
</script>

<?php
require_once "msg.inc.php";
require_once "functions.php";
require_once "admission.inc.php";
	
$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$db4=new DB_Seminar;
$db5=new DB_Seminar;
	
?>
<body>

	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr><td class="topic" colspan=2>&nbsp;<b><?=_("Veranstaltungsfreischaltung")?></b></td></tr>
	<tr><td class="blank" colspan=2>&nbsp;<br></td></tr>
<?
	// admins und roots haben hier nix verloren
	if ($perm->have_perm("admin")) {
	    parse_msg ("info§"._("Sie sind ein <b>Administrator</b> und k&ouml;nnen sich daher nicht f&uuml;r einzelne Veranstaltungen anmelden!"));
	    echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
	    if ($send_from_search)
	    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
	    echo "<br><br></td></tr></table>";
	    page_close();
	    die;
	    }
	 
	 //Gruppe auswaehlen, falls wir den User eintragen
	if ($SemIDtemp)
		$t_id=$SemIDtemp;
	else
		$t_id=$id;
	$db->query("SELECT start_time FROM seminare WHERE Seminar_id = '$t_id'");
	$db->next_record();
	$group = select_group ($db->f("start_time"), $user->id);
	
	 //check stuff for admission 
	 check_admission();
	 
	 if ($sem_verify_selection_send && !$sem_verify_suggest_studg)
	 	parse_msg ("error§"._("Bitte w&auml;hlen Sie einen Studiengang zur Anmeldung f&uuml;r diese Veranstaltung aus!"));
	 
	//nobody darf sogar durch (wird spaeter schon abgefangen)
	if ($perm->have_perm("user")) {

		//Sonderfall, Passwort fuer Schreiben nicht eingegeben, Lesen aber erlaubt
		if ($SemIDtemp<>"") {
			$db->query("SELECT Lesezugriff, Name FROM seminare WHERE Seminar_id LIKE '$SemIDtemp'");
			$db->next_record();
			if ($db->f("Lesezugriff") <= 1 && $perm->have_perm("autor")) {
				$db->query("INSERT INTO seminar_user VALUES ('$SemIDtemp','$user->id','user','$group', '', '".time()."' )");
				parse_msg (sprintf("msg§"._("Sie wurden mit dem Status <b> user </b> in die Veranstaltung %s eingetragen."), $db->f("Name")));
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$SemIDtemp\">&nbsp; &nbsp; "._("Hier </a>kommen Sie zu der Veranstaltung");
				if ($send_from_search)
			    		echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
			}
	 		page_close();
			die;
		}

		//wenn eine Sessionvariable gesetzt ist, nehmen wir besser die
		if (!isset($id)) if (isset($SessSemName[1])) 
			$id=$SessSemName[1];
			
		//laden von benoetigten Informationen
		$db=new DB_Seminar;
		$db->query("SELECT Lesezugriff, Schreibzugriff, Passwort, Name FROM seminare WHERE Seminar_id LIKE '$id'");
		while ($db->next_record()) {
			$SemSecLevelRead=$db->f("Lesezugriff");
			$SemSecLevelWrite=$db->f("Schreibzugriff");
			$SemSecPass=$db->f("Passwort");
			$SeminarName=$db->f("Name");
		}
		$db->query("SELECT status FROM seminar_user WHERE Seminar_id LIKE '$id' AND user_id LIKE '$user->id'");
		$db->next_record();
		$SemUserStatus=$db->f("status");

		//Ueberpruefung auf korrektes Passwort
		if ((isset($pass) && $pass!="" && (md5($pass)==$SemSecPass))  ||  (isset($hashpass) && $hashpass!="" && $hashpass==$SemSecPass)) {
			if (($SemUserStatus=="user") && ($perm->have_perm("autor"))){
				$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$user->id'");
				parse_msg (sprintf("msg§"._("Sie wurden die Veranstaltung <b>%s</b> auf den Status <b> autor </b> hochgestuft."), $SeminarName));
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("weiter zu der Veranstaltung")."</a>";
			    	if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
			elseif ($perm->have_perm("autor")) {
				$db->query("INSERT INTO seminar_user VALUES ('$id','$user->id','autor','$group', '', '".time()."')");
				parse_msg (sprintf("msg§"._("Sie wurden mit dem Status <b>autor</b> in die Veranstaltung <b>%s</b> eingetragen."), $SeminarName));
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("weiter zur Veranstaltung")."</a>";
			  	if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
		}
 elseif ((isset($pass) && $pass!="") || (isset($hashpass) && $hashpass!="")) {
		    parse_msg ("error§Ung&uuml;ltiges Passwort eingegeben, bitte nocheinmal versuchen !");
	}

	//Die eigentliche Ueberpruefung verschiedener Rechtesachen
	//User schon in der Seminar_user vorhanden? Und was macht er da eigentlich?
		if ($SemUserStatus) {
			if ($SemUserStatus=="user") { //Nur user? Dann muessen wir noch mal puefen
				if ($SemSecLevelWrite==2) { //Schreiben nur per Passwort, der User darf es eingeben
					if ($perm->have_perm("autor")) { //nur globale Autoren duerfen sich hochstufen!
						printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Bitte Passwort f&uuml;r die Veranstaltung <b>%s</b> eingeben.<br><br></td></tr>"), $SeminarName);
						?>
						</td></tr>
						<tr><td class="blank" colspan=2>
						<form name="details" action="<? echo $sess->pself_url(); ?>" method="POST">
						&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
						<input type="HIDDEN" name="id" value="<? echo $id;?>">
						<input type="HIDDEN" name="hashpass" value="">
						<input type="submit" onClick="verifySeminar();return true;" value="abschicken">
						</form>
						</td></tr>
						<?
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
					    	if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br>";
						?>
						</td></tr></table>
						<?
					} else {
						parse_msg (sprintf("info§"._("Um in der Veranstaltung <b>%s</b> Schreibrechte zu bekommen, m&uuml;ssen Sie zumindest auf die Registrierungsmail reagiert haben!"), $SeminarName));
	   					echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
  						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br></td></tr></table>";
					}
					page_close();
					die;
				}
			  	elseif ($SemSecLevelWrite==1){//Hat sich der globale Status in der Zwischenzeit geaendert? Dann hochstufen
					if ($perm->have_perm("autor")) {
						$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$user->id'");
						parse_msg(sprintf("info§"._("Sie wurden in der Veranstaltung <b>%s</b> hochgestuft auf den Status <b>autor</b>."), $SeminarName));
						echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("weiter zu der Veranstaltung")."</a>";
						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br></td></tr></table>";
						page_close();
						die;
					} else {//wenn nicht, informieren
						parse_msg(sprintf("info§"._("Sie sind nur mit der Berechtigung <b>user</b> f&uuml;r die Veranstaltung <b>%s</b> freigeschaltet. Wenn Sie auf die Registrierungsmail antworten, bekommen Sie in dieser Veranstaltung Schreibrechte."), $SeminarName));
						echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; <a href=\"seminar_main.php?auswahl=$id\">"._("weiter zu der Veranstaltung")."</a>";
						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br><br></td></tr></table>";
						page_close();
						die;
					}
				}
			} else { //User ist schon Autor oder hoeher, soll den Quatsch mal lassen und weiter ins Seminar
				parse_msg(sprintf("info§"._("Sie sind schon mit der Berechtigung <b>%s</b> f&uuml;r die Veranstaltung <b>%s</b> freigeschaltet."), $SemUserStatus, $SeminarName));
				echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; <a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("weiter zu der Veranstaltung")."</a>";
				if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
		} else {//User ist noch nicht eingetragen in seminar_user
			if ($perm->have_perm("autor")) { //User ist global 'Autor'also normaler User
				if (($SemSecLevelWrite==3) && ($SemSecLevelRead==3)) {//Teilnehmerbeschraenkte Veranstaltung, naehere Uberpruefungen erforderlich
					if ($auth->auth["perm"]=="dozent") { //Dozenten duerfen sich nicht fuer Anmeldebeschraenkte Veranstaltungen anmelden
						parse_msg (sprintf("info§"._("Sie d&uuml;rfen sich mit dem Status Dozent nicht f&uuml;r die teilnahmebeschr&auml;nkte Veranstaltung <b>%s</b> anmelden.<br />Wenn Sie dennoch eingetragen werden m&ouml;chten, wenden Se sich bitte direkt an den Dozenten der Veranstaltung."), $SeminarName));
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
						if ($send_from_search)
				    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br	><br></td></tr></table>";
						page_close();
						die;
						}
					$db->query("SELECT studiengang_id FROM user_studiengang WHERE user_id = '$user->id' "); //Hat der Studie ueberhaupt Studiengaenge angegeben?					
					if (!$db->num_rows()) { //Es sin gar keine vorhanden! Hinweis wie man das eintragen kann
						parse_msg (sprintf("info§"._("Die Veranstaltung <b>%s</b> ist teilnahmebeschr&auml;nkt. Um sich f&uuml;r teilnahmebeschr&auml;nkte Veranstaltungen eintragen zu k&ouml;nnen, m&uuml;ssen Sie einmalig ihre Studienkombination angeben! <br> Bitte tragen Sie ihre Studieng&auml;nge auf ihrer <a href=\"edit_about.php?view=Karriere#studiengaenge\">pers&ouml;nlichen Homepage</a> ein!"), $SeminarName));
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
						if ($send_from_search)
				    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br	><br></td></tr></table>";
						page_close();
						die;
						}
					//Wurden wir evtl. schon in die Veranstaltung als Wartender eingetragen?
					$db->query("SELECT user_id FROM admission_seminar_user WHERE user_id = '$user->id' AND seminar_id = '$id' "); //Bin ich eingetragen?
					if ($db->num_rows()) { //Es gibt einen Eintrag, da darf ich also nicht mehr rein
						parse_msg (sprintf("info§"._("Sie stehen schon auf der Anmelde- bzw. Warteliste der Veranstaltung <b>%s</b>. <br />Wenn sich sich neu oder f&uuml;r ein anderes Kontingent eintragen wollen, dann l&ouml;schen Sie bitte vorher die Zuordnug auf der der &Uuml;bersicht ihrer Veranstaltungen."), $SeminarName));
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
						if ($send_from_search)
				    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
						echo "<br	><br></td></tr></table>";
						page_close();
						die;
						}
					//Ok, es gibt also Studiengaenge und wie stehen noch nicht in der admission_seminar_user
					$db2->query("SELECT admission_endtime, admission_turnout, admission_type, admission_selection_take_place FROM seminare WHERE Seminar_id LIKE '$id'"); //Wir brauchen in diesem Fall mehr Daten
					$db2->next_record();
					if (!$sem_verify_suggest_studg) {//Wir wissen noch nicht mit welchem Studiengang der User rein will
						$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id) LEFT JOIN user_studiengang USING (studiengang_id) WHERE seminar_id LIKE '$id' AND (user_id = '$user->id' OR admission_seminar_studiengang.studiengang_id = 'all')"); //Hat der Studi passende Studiengaenge ausgewaehlt?
						if ($db->num_rows() == 1) {//Nur einen passenden gefunden? Dann bieten wir nur den an. Info wird aber trotzdem gegeben
							$db->next_record();
							$sem_verify_possible_studg=$db->f("studiengang_id");
							$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$id' ORDER BY name"); //Alle theorethisch moeglichen Anziegen
							printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Die Veranstaltung <b>%s</b> ist teilnahmebeschr&auml;nkt.")."<br><br></td></tr>", $SeminarName);
							print "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Von den folgenden m&ouml;glichen Kontigenten kommt nur das <b>fett</b>  ausgegebene Kontingent f&uuml;r Sie in Frage.")." <br />&nbsp; &nbsp; "._("Bitte best&auml;tigen Sie, wenn Sie sich in dieses Kontingent eintragen wollen:")." <br><br></td></tr>";
							?>
							<tr><td class="blank" colspan=2>
							<form action="<? echo $sess->pself_url(); ?>" method="POST">
						       <?
							while ($db->next_record()) {
								if ($db->f("studiengang_id") == "all")
									$tmp_sem_verify_quota=get_all_quota($id);
								else
									$tmp_sem_verify_quota=round ($db2->f("admission_turnout") * ($db->f("quota") / 100));
								if ($db->f("studiengang_id") == $sem_verify_possible_studg)
									print "<b>";
								printf ("&nbsp; &nbsp; <font size=-1 color=\"%s\">"._("Kontingent f&uuml;r %s (%s Pl&auml;tze)")."</font>", ($db->f("studiengang_id") == $sem_verify_possible_studg)  ? "black" : "#888888", ($db->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db->f("name"), $tmp_sem_verify_quota);
								if ($db->f("studiengang_id") == $sem_verify_possible_studg) {
									printf ("</b>&nbsp; <input type=\"HIDDEN\" name=\"sem_verify_suggest_studg\" value=\"%s\">", $db->f("studiengang_id"));
								}
								print "<br />";
							}
						       ?>
							<br />
							&nbsp; &nbsp; <input type="IMAGE" <?=makeButton("ok", "src")?> border=0 value="abschicken">
							</form>
							<?
							echo "<tr><td class=\"blank\" colspan=\"2\">";
							if ($db2->f("admission_type") == 1) {
								if ($db2->f("admission_selection_take_place"))
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgte nach dem Losverfahren am %s Uhr.")." "._("Weitere Pl&auml;tze k&ouml;nnen evtl. &uuml;ber die Warteliste vergben werden.")." <br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
								else
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgt nach dem Losverfahren am %s Uhr.")." <br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
							} else {
								if ($db2->f("admission_selection_take_place"))							
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgte in der Reihenfolge der Anmeldung.")." "._(" Weitere Pl&auml;tze k&ouml;nnen evtl. &uuml;ber die Warteliste vergben werden.")."<br />&nbsp; &nbsp;"._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ");
								else
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgt in der Reihenfolge der Anmeldung.")."<br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ");
							}
							echo "</td></tr>";								
							echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
						    	if ($send_from_search)
					    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
							echo "<br><br>";
							?>
							</td></tr></table>				
							<?
							page_close();
							die;
						} elseif ($db->num_rows() >1) { //Mehrere gefunden, fragen welcher es denn sein soll
							printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Die Veranstaltung <b>%s</b> ist teilnahmebeschr&auml;nkt.")."<br><br></td></tr>", $SeminarName);
							print "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Sie k&ouml;nnen sich f&uuml;r <b>eines</b> der m&ouml;glichen Kontingente anmelden.")."<br/><br />&nbsp; &nbsp; "._("Bitte w&auml;hlen Sie das f&uuml;r Sie am besten geeignete Kontingent aus:")." <br><br></td></tr>";
							$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$id' ORDER BY name"); //Alle theoretisch moeglichen auswaehlen
							?>
							<tr><td class="blank" colspan=2>
							<form action="<? echo $sess->pself_url(); ?>" method="POST" >
								<input type="HIDDEN" name="sem_verify_selection_send" value="TRUE" />
							       <?
								while ($db->next_record()) {
									$db3->query("SELECT studiengang_id FROM user_studiengang WHERE studiengang_id = '".$db->f("studiengang_id")."' AND user_id = '$user->id' "); // Darf ich diesen auswaehlen?
									$db3->next_record();
									if ($db3->f("studiengang_id") == "all")
										$tmp_sem_verify_quota=get_all_quota($id);
									else
										$tmp_sem_verify_quota=round ($db2->f("admission_turnout") * ($db->f("quota") / 100));
									if (($db3->num_rows()) || ($db->f("studiengang_id") == "all"))
										printf ("&nbsp; &nbsp; <input type=\"RADIO\" name=\"sem_verify_suggest_studg\" value=\"%s\">&nbsp; <font size=-1><b>"._("Kontingent f&uuml;r %s (%s Pl&auml;tze)")."</b></font><br />", $db->f("studiengang_id"), ($db->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db->f("name"), $tmp_sem_verify_quota);
									else
										printf ("&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<font size=-1 color=\"#888888\">"._("Kontingent f&uuml;r %s (%s Pl&auml;tze)")."</font><br />", ($db->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db->f("name"), $tmp_sem_verify_quota);
									}
							       ?>
							<br />&nbsp; &nbsp; <input type="IMAGE" <?=makeButton("ok", "src")?> border=0 value="abschicken">
							</form>
							</td></tr>							
							<?
							echo "<tr><td class=\"blank\" colspan=\"2\">";
							if ($db2->f("admission_type") == 1) {
								if ($db2->f("admission_selection_take_place"))
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgte nach dem Losverfahren am %s Uhr.")." "._("Weitere Pl&auml;tze k&ouml;nnen evtl. &uuml;ber die Warteliste vergben werden.")." <br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
								else
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgt nach dem Losverfahren am %s Uhr.")." <br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
							} else {
								if ($db2->f("admission_selection_take_place"))							
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgte in der Reihenfolge der Anmeldung.")." "._(" Weitere Pl&auml;tze k&ouml;nnen evtl. &uuml;ber die Warteliste vergben werden.")."<br />&nbsp; &nbsp;"._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ");
								else
									printf ("<font size=-1>&nbsp; &nbsp; "._("Die Teilnehmerauswahl erfolgt in der Reihenfolge der Anmeldung.")."<br />&nbsp; &nbsp; "._("In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze pro Kontingent angegeben.")."</font><br />&nbsp; ");
							}
							echo "</td></tr>";
							echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
						    	if ($send_from_search)
					    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
							echo "<br><br>";
							?>
							</td></tr></table>				
							<?
							page_close();
							die;
						} else { //Keinen passenden Studiengaenge gefunden, abbruch
							$db->query("SELECT studiengang_id FROM user_studiengang WHERE user_id = '$user->id' "); //Hat der Studie ueberhaupt Studiengaenge angegeben?
							if ($db->num_rows() >=1) { //Es waren nur die falschen
								parse_msg (sprintf("info§"._("Sie belegen leider keinen passenden Studiengang, um an der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b> teilnehmen zu k&ouml;nnen."), $SeminarName));
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							}
						}
					}
					if ($sem_verify_suggest_studg) { //User hat einen Studiengang angegeben oeder wir haben genau einen passenden gefunden, mit dem er jetzt rein will/kann
						if ($db2->f("admission_selection_take_place") == 1) { //Variante Eintragen nach Lostermin oder Enddatum der Kontigentierrung. Wenn noch Platz ist fuellen wir einfach auf, ansonsten Warteliste
							if (get_free_admission($id)) { //Wir koennen einfach eintragen, Platz ist noch
							 	$db4->query("INSERT INTO seminar_user SET user_id = '$user->id', Seminar_id = '$id', admission_studiengang_id = '$sem_verify_suggest_studg', status='autor', gruppe='$group', mkdate='".time()."' ");
								parse_msg (sprintf("msg§"._("Sie wurden mit dem Status <b>autor</b> in die Veranstaltung <b>%s</b> eingetragen und sind damit zugelassen."), $SeminarName));
								echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("weiter zu der Veranstaltung")."</a>";
								if ($send_from_search)
					    				echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							} else { //Auf die Warteliste
								$db5->query("SELECT position FROM admission_seminar_user WHERE seminar_id= '$id' ORDER BY position DESC");//letzte hoechste Position herausfinden
								$db5->next_record();
							 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='awaiting', mkdate='".time()."', position='".($db5->f("position")+1)."'  ");
								parse_msg (sprintf("info§"._("Es gibt zur Zeit keinen freien Platz in der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b>. Sie wurden jedoch auf Platz %s auf die Warteliste gesetzt.")." <br /> "._("Sie werden automatisch eingetragen, sobald ein Platz f&uuml;r Sie frei wird."), $SeminarName, ($db5->num_rows()+1)));
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
								if ($send_from_search)
					    				echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							}
						} else { //noch nicht gelost oder Enddatum, also Kontingentierung noch aktiv
							$db3->query("SELECT name, quota, admission_seminar_studiengang.studiengang_id FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id LIKE '$id' AND admission_seminar_studiengang.studiengang_id = '$sem_verify_suggest_studg' "); //Nochmal die Daten des quotas fuer diese Veranstaltung
							$db3->next_record();
							if ($db2->f("admission_type") == 1) { //Variante Losverfahren
								$db5->query("SELECT position FROM admission_seminar_user ORDER BY position DESC");//letzte hoechste Position heruasfinden
								$db5->next_record();
							 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='claiming', mkdate='".time()."', position='' ");
								parse_msg (sprintf("info§"._("Sie wurden auf die Anmeldeliste der Veranstaltung <b>%s</b> gesetzt.")." <br />"._("Teilnehmer der Veranstaltung <b>%s</b> werden Sie, falls Sie im Losverfahren am %s Uhr ausgelost werden. Sollten Sie nicht ausgelost werden, werden Sie auf die Warteliste gesetzt und werden vom System automatisch als Teilnehmer eingetragen, sobald ein Platz f&uuml;r Sie frei wird."), $SeminarName, $SeminarName, date("d.m.Y, G:i", $db2->f("admission_endtime"))));
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							} else { //Variante chronologisches Anmelden
								$db->query("SELECT user_id FROM seminar_user WHERE Seminar_id = '$id' AND admission_studiengang_id = '$sem_verify_suggest_studg'"); //Wieviel user sind schon in diesem Kontingent eingetragen
								if ($db3->f("studiengang_id") == "all")
									$tmp_sem_verify_quota=get_all_quota($id);
								else
									$tmp_sem_verify_quota=round ($db2->f("admission_turnout") * ($db3->f("quota") / 100));
								if ($db->num_rows() < $tmp_sem_verify_quota) {//noch Platz in dem Kontingent --> direkt in seminar_user
								 	$db4->query("INSERT INTO seminar_user SET user_id = '$user->id', Seminar_id = '$id', status='autor', gruppe='$group', admission_studiengang_id = '$sem_verify_suggest_studg', mkdate='".time()."' ");
									parse_msg (sprintf("msg§"._("Sie wurden mit dem Status <b>autor</b> in die Veranstaltung <b>%s</b> eingetragen und sind damit zugelassen."), $SeminarName));
									echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("weiter zu der Veranstaltung")."</a>";
									if ($send_from_search)
									    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
									echo "<br><br></td></tr></table>";
									page_close();
									die;
								} else { //kein Platz mehr im Kontingent --> auf Warteposition in admission_seminar_user
									$db5->query("SELECT position FROM admission_seminar_user WHERE seminar_id= '$id' ORDER BY position DESC");//letzte hoechste Position herausfinden
									$db5->next_record();
								 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='awaiting', mkdate='".time()."', position='".($db5->f("position")+1)."'  ");
									parse_msg (sprintf("info§"._("Es gibt zur Zeit keinen freien Platz in der teilnahmebeschr&auml;nkten Veranstaltung <b>%s</b>. Sie wurden jedoch auf Platz %s auf die Warteliste gesetzt.")." <br /> "._("Sie werden automatisch eingetragen, sobald ein Platz f&uuml;r Sie frei wird."), $SeminarName, $db5->num_rows()+1));
									echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
									if ($send_from_search)
						    				echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
									echo "<br	><br></td></tr></table>";
									page_close();
									die;
								}
							}
						}
					} 
				}
				elseif (($SemSecLevelWrite==2) && ($SemSecLevelRead==2)) {//Paswort auf jeden Fall erforderlich, also her damit
					printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp;"._("Bitte Passwort f&uuml;r die Veranstaltung <b>%s</b> eingeben.")."<br><br></td></tr>", $SeminarName);
					?>
					</td></tr>					
					<tr><td class="blank" colspan=2>
					<form name="details" action="<? echo $sess->pself_url(); ?>" method="POST" >
					&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
					<input type="HIDDEN" name="id" value="<? echo $id;?>">
					<input type="HIDDEN" name="hashpass" value="">
					<input onClick="verifySeminar();return true;" type="SUBMIT" value="abschicken">
					</form>
					</td></tr>					
					<?
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
				    	if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
					echo "<br><br>";
					?>
					</td></tr></table>				
					<?
					page_close();
					die;
				}
				elseif ($SemSecLevelWrite==2) {//nur passwort fuer Schreiben, User koennte ohne Passwort als 'User' in das Seminar
					printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Bitte Passwort f&uuml;r die Veranstaltung <b>%s</b> eingeben.")."<br><br></td></tr>", $SeminarName);
					print "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Falls Sie das Passwort jetzt noch nicht eingeben m&ouml;chten, k&ouml;nnen Sie mit Leseberechtigung an der Veranstaltung teilnehmen.")."<br><br></td></tr>";
					printf ("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Bitte klicken Sie dazu %s hier</a>!")."<br><br></td></tr>", "<a href=\"sem_verify.php?SemIDtemp=$id\">");
					?>
					</td></tr>					
					<tr><td class="blank" colspan=2>
					<form name="details" action="<? echo $sess->pself_url(); ?>" method="POST" >
					&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
					<input type="HIDDEN" name="id" value="<? echo $id;?>">
					<input type="HIDDEN" name="hashpass" value="">
					<input onClick="verifySeminar(); return true;" type="SUBMIT" value="abschicken">
					</form>
					</td></tr>					
					<?
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
				    	if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
					echo "<br><br>";
					?>
					</td></tr></table>					
					<?
					page_close();
					die;
				} else {//kein Passwortschutz, also wird der Kerl auf jeden Fall autor im Seminar
					$InsertStatus="autor";
				}
			} else {//der User ist auch global 'User'
				if ($SemSecLevelRead>0) {//Lesen duerfen nur Autoren, also wech hier
					parse_msg (printf("info§"._("Um an der Veranstaltung <b>%s</b> teilnehmen zu k&ouml;nnen, m&uuml;ssen Sie zumindest auf die Registrierungsmail geantwortet haben!"), $SeminarName));
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
					if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
					echo "<br><br></td></tr></table>";
					page_close();
					die;
				} else {//Lesen mit Berechtigung 'User' geht
					if ($SemSecLevelWrite==0) {//Wenn Schreiben auch mit Berechtigung 'user' geht, darf es sogar als 'autor' rein (auch wenn es gegen das Grundprizip verstoesst (keine hoeheren Rechte als globale Rechte). Das geht nur, wenn in der config.inc Nobody write=TRUE fuer Veranstaltungsklasse ist
						$InsertStatus="autor";
					} else { //sonst bleibt es bei 'user'
						$InsertStatus="user";
					}
				}
			}
		}

		if (isset($InsertStatus)) {//Status reinschreiben
			$db->query("INSERT INTO seminar_user VALUES ('$id', '$user->id', '$InsertStatus','$group', '', '".time()."')");
			parse_msg (sprintf("msg§"._("Sie wurden mit dem Status <b>%s </b>in die Veranstaltung <b>%s</b> eingetragen."), $InsertStatus, $SeminarName));
			echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; "._("Hier kommen Sie zu der Veranstaltung")."</a>";
			if ($send_from_search)
			    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
			echo "<br><br></td></tr></table>";
			page_close();
			die;
		}
	}
	
  if ($SemSecLevelRead==0) {//nur wenn das Seminar wirklich frei ist geht's hier weiter
	printf("<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; "._("Um zu der Veranstaltung <b>%s</b> zu gelangen, klicken Sie bitte%s hier</a>!")."<br><br></td></tr></table>", $SeminarName, "<a href=\"seminar_main.php?auswahl=$id\">");
  }	else {//keine Rechte f&uuml;r das Seminar
		parse_msg (sprintf("error§"._("Sie habe nicht die erforderlichen Rechte, um an der Veranstaltung <b>%s</b> teilnehmen zu d&uuml;rfen!"), $SeminarName));
		echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; "._("zur&uuml;ck zur Startseite")."</a>";
		if ($send_from_search)
	    		echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">"._("zur&uuml;ck zur letzten Auswahl")."</a>";
		echo "<br><br></td></tr></table>";
	}
	page_close();
?>
</body>
</html>
<?