<?php
/*
sem_verify.php - Script zum Anmelden zu einem Seminar mit Ueberpruef
 aller Rechte.
Copyright (C) 2002 André Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
	<link rel="stylesheet" href="style.css" type="text/css">
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
	<title>Stud.IP</title>

<script language="javascript" src="md5.js"></script>
<script language="javascript">
  <!--
  function verifySeminar() {
      document.details.hashpass.value = MD5(document.details.pass.value);
      document.details.pass.value = "";
  }
  // -->
</script>

</head>
<body>


<?php
	include "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php
	include "header.php";   //hier wird der "Kopf" nachgeladen
	require_once "msg.inc.php";
	require_once "functions.php";
	
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db4=new DB_Seminar;
	$db5=new DB_Seminar;
	
?>
<body>

	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr><td class="topic" colspan=2>&nbsp;<b>Veranstaltungsfreischaltung</b></td></tr>
	<tr><td class="blank" colspan=2>&nbsp<br></td></tr>
<?
	// admins und roots haben hier nix verloren
	if ($perm->have_perm("admin")) {
	    parse_msg ("info§Sie sind ein <b>Administrator</b> und k&ouml;nnen sich daher nicht f&uuml;r einzelne Veranstaltungen anmelden!");
	    echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
	    if ($send_from_search)
	    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
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
	 
	//nobody darf sogar durch (wird spaeter schon abgefangen)
	if ($perm->have_perm("user")) {

		//Sonderfall, Passwort fuer Schreiben nicht eingegeben, Lesen aber erlaubt
		if ($SemIDtemp<>"") {
			$db->query("SELECT Lesezugriff, Name FROM seminare WHERE Seminar_id LIKE '$SemIDtemp'");
			$db->next_record();
			if ($db->f("Lesezugriff") <= 1 && $perm->have_perm("autor")) {
				$db->query("INSERT INTO seminar_user VALUES ('$SemIDtemp','$user->id','user','$group', '', '".time()."' )");
				parse_msg ("msg§Sie wurden mit dem Status <b> user </b> in die Veranstaltung ".$db->f("Name")." eingetragen. ");
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$SemIDtemp\">&nbsp; &nbsp; Hier </a>kommen sie zu der Veranstaltung";
				if ($send_from_search)
			    		echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur Auswahl</a>";
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
				parse_msg ("msg§Sie wurden die Veranstaltung <b>$SeminarName</b> auf den Status <b> autor </b> hochgestuft.");
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; weiter zu der Veranstaltung</a>";
			    	if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
			elseif ($perm->have_perm("autor")) {
				$db->query("INSERT INTO seminar_user VALUES ('$id','$user->id','autor','$group'', '', '".time()."')");
				parse_msg ("msg§Sie wurden mit dem Status <b> autor </b> in die Veranstaltung <b>$SeminarName</b> eingetragen.");
				echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; weiter zur Veranstaltung</a>";
			  	if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
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
						echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Bitte Passwort f&uuml;r die Veranstaltung <b>$SeminarName</b> eingeben.<br><br></td></tr>";
						?>
						</td></tr>
						<tr><td class="blank" colspan=2>
						<form action="<? echo $sess->pself_url(); ?>" method="POST" >
						&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
						       <input type="HIDDEN" name="id" value="<? echo $id;?>">
						<input type="HIDDEN" name="hashpass" value="">
						<input onSubmit="verifySeminar();return true;" type="SUBMIT" value="abschicken">
						</form>
						<?
						echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; zur&uuml;ck zur Startseite</a>";
					    	if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
						echo "<br><br>";
						?>
						</td></tr></table>
						<?
					} else {
						parse_msg ("info§Um in der Veranstaltung <b>$SeminarName</b> Schreibrechte zu bekommen, m&uuml;ssen sie zumindest auf die Registrierungsmail geantwortet haben!");
	   					echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
  						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
						echo "<br><br></td></tr></table>";
					}
					page_close();
					die;
				}
			  	elseif ($SemSecLevelWrite==1){//Hat sich der globale Status in der Zwischenzeit geaendert? Dann hochstufen
					if ($perm->have_perm("autor")) {
						$db->query("UPDATE seminar_user SET status='autor' WHERE Seminar_id = '$id' AND user_id = '$user->id'");
						parse_msg("info§Sie wurden in der Veranstaltung <b>$SeminarName</b> hochgestuft auf den Status <b> autor </b>.");
						echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; weiter zu der Veranstaltung</a>";
						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
						echo "<br><br></td></tr></table>";
						page_close();
						die;
					} else {//wenn nicht, informieren
						parse_msg("info§Sie sind nur mit der Berechtigung <b>$SemUserStatus</b> f&uuml;r die Veranstaltung <b>$SeminarName</b> freigeschaltet. Wenn sie auf die Registrierungsmail antworten, bekommen sie in dieser Veranstaltung Schreibrechte.");
						echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; <a href=\"seminar_main.php?auswahl=$id\">weiter zu der Veranstaltung</a>";
						if ($send_from_search)
						    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
						echo "<br><br></td></tr></table>";
						page_close();
						die;
					}
				}
			} else { //User ist schon Autor oder hoeher, soll den Quatsch mal lassen und weiter ins Seminar
				parse_msg("info§Sie sind schon mit der Berechtigung <b>$SemUserStatus</b> f&uuml;r die Veranstaltung <b>$SeminarName</b> freigeschaltet.");
				echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; <a href=\"seminar_main.php?auswahl=$id\"> weiter zu der Veranstaltung</a>";
				if ($send_from_search)
				    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
				echo "<br><br></td></tr></table>";
				page_close();
				die;
			}
		} else {//User ist noch nicht eingetragen in seminar_user
			if ($perm->have_perm("autor")) { //User ist global 'Autor'also normaler User
				if (($SemSecLevelWrite==3) && ($SemSecLevelRead==3)) {//Teilnehmerbeschraenkte Veranstaltung, naehere Uberpruefungen erforderlich
					$db2->query("SELECT admission_endtime, admission_turnout, admission_type, admission_selection_take_place FROM seminare WHERE Seminar_id LIKE '$id'"); //Wir brauchen in diesem Fall mehr Daten
					$db2->next_record();
					if (!$sem_verify_suggest_studg) {//Wir wissen noch nicht mit welchem Studiengang der User rein will
						$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id) LEFT JOIN user_studiengang USING (studiengang_id) WHERE seminar_id LIKE '$id' AND (user_id = '$user->id' OR admission_seminar_studiengang.studiengang_id = 'all')"); //Hat der Studi passende Studiengaenge ausgewaehlt?
						if ($db->num_rows() == 1) {//Nur einen passenden gefunden? Dann nehmen wir den
							$db->next_record();
							$sem_verify_suggest_studg=$db->f("studiengang_id");
						} elseif ($db->num_rows() >1) { //Mehrere gefunden, fragen welcher es denn sein soll
							echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Die Veranstaltung <b>$SeminarName</b> ist teilnahmebeschr&auml;nkt.<br><br></td></tr>";
							echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Sie k&ouml;nnen sich f&uuml;r <b>eines</b> der m&ouml;glichen Kontingente anmelden.<br/><br />&nbsp; &nbsp; Bitte w&auml;hlen Sie das f&uuml;r Sie am besten geeignete Kontingent aus: <br><br></td></tr>";
							?>
							</td></tr>
							<tr><td class="blank" colspan=2>
							<form action="<? echo $sess->pself_url(); ?>" method="POST" >
							&nbsp; &nbsp; <select name="sem_verify_suggest_studg">
							       <?
								while ($db->next_record())
									printf ("<option value=\"%s\">Kontingent f&uuml;r %s (%s Pl&auml;tze)</option>", $db->f("studiengang_id"), ($db->f("studiengang_id") == "all") ? "alle Studieng&auml;nge" : $db->f("name"), round ($db2->f("admission_turnout") * ($db->f("quota") / 100)));
							       ?>
							 </select>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/auswaehlen-button.gif" border=0 value="abschicken">
							</for	m>
							<?
							if ($db2->f("admission_type") == 1)
								printf ("<br /><br /><font size=-1>&nbsp; &nbsp; Die Teilnehmerauswahl erfolgt nach dem Losverfahren am %s Uhr. <br /><font size=-1>&nbsp; &nbsp; In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze angegeben.</font><br />&nbsp; ", date("d.m.Y, G:i", $db2->f("admission_endtime")));
							else
								printf ("<br /><br /><font size=-1>&nbsp; &nbsp; Die Teilnehmerauswahl erfolgt in der Reihenfolge der Anmeldung.<br /><font size=-1>&nbsp; &nbsp; In Klammern ist die Anzahl der <b>insgesamt</b> verf&uuml;gbaren Pl&auml;tze angegeben.</font><br />&nbsp; ");
							echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; zur&uuml;ck zur Startseite</a>";
						    	if ($send_from_search)
					    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
							echo "<br><br>";
							?>
							</td></tr></table>				
							<?
							page_close();
							die;
						} else { //Keine passenden Studeingaenge gefunden, abbruch
							$db->query("SELECT studiengang_id FROM user_studiengang WHERE user_id = '$user->id' "); //Hat der Studie ueberhaupt Studiengaenge angegeben?
							if ($db->num_rows() >=1) { //Es waren nur die falschen
								parse_msg ("info§Sie belegen leider keinen passenden Studiengang, um an der teilnahmebeschr&auml;nkten Veranstaltung <b>$SeminarName</b> teilnehmen zu k&ouml;nnen.");
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							} else { //Es sin gar keine vorhanden! Hinweis wie man das eintragen kann
								parse_msg ("info§Die Veranstaltung <b>$SeminarName</b> ist teilnahmebeschr&auml;nkt. Um sich f&uuml;r teilnahmebeschr&auml;nkte Veranstaltungen eintragen zu k&ouml;nnen, m&uuml;ssen sie einmalig ihre Studienkombination angeben! <br> Bitte tragen sie ihre Studeng&auml;nge auf ihrer <a href=\"edit_about.php?view=Karriere#studiengaenge\">pers&ouml;nlichen Homepage</a> ein!");
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							}
						}
					}
					if ($sem_verify_suggest_studg) { //User hat einen Studiengang angegeben oeder wir haben genau einen passenden gefunden, mit dem er jetzt rein will/kann
						$db3->query("SELECT name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id LIKE '$id' AND admission_seminar_studiengang.studiengang_id = '$sem_verify_suggest_studg' "); //Nochmal die Daten des quotas fuer diese Veranstaltung
						if ($db2->f("admission_type") == 1) { //Variante Losverfahren
							if (!$db2->f("admission_selection_take_place")) { //es wurde noch nicht gelost --> auf Warteposition ist admission_seminar_user
								$db5->query("SELECT position FROM admission_seminar_user ORDER BY position DESC");//letzte hoechste Position heruasfinden
								$db5->next_record();
							 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='claiming', mkdate='".time()."', position='' ");
								parse_msg ("info§Sie wurden auf die Anmeldeliste der Veranstaltung <b>$SeminarName</b> gesetzt. <br />Teilnehmer der Veranstaltung <b>$SeminarName</b> werden Sie, falls Sie im Losverfahren am ".date("d.m.Y, G:i", $db2->f("admission_endtime"))." Uhr ausgelost werden. Sollten sie nicht ausgelost werden, werden Sie auf die Warteliste gesetzt und werden vom System automatisch als Teilnehmer eingetragen, sobald ein Platz f&uuml;r Sie frei wird.");
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							} else { //wurde schon gelost --> Verfahren wird wie chronologsich geregelt: Wenn noch Plaetze im Kontingent frei sind, wird sofort eingetragen, sonst Warteliste
								$chrono_after_selection=TRUE;
							}
						}
						if (($db2->f("admission_type") == 2) || ($chrono_after_selection)) { //Variante chronologisches Eintragen oder Losverfahren nach dem Losen
							$db->query("SELECT user_id FROM seminar_user WHERE admission_studiengang_id = '$sem_verify_suggest_studg'"); //Wieviel user sind schon in diesem Kontingent eingetragen
							if ($db->num_rows() <= round ($db2->f("admission_turnout") * ($db->f("quota") / 100))) {//noch Platz in dem Kontingent --> direkt in seminar_user
							 	$db4->query("INSERT INTO seminar_user SET user_id = '$user->id', Seminar_id = '$id', status='autor', gruppe='$group', admission_studiengang_id = '$sem_verify_suggest_studg', mkdate='".time()."' ");
								parse_msg ("msg§Sie wurden mit dem Status <b>autor</b> in die Veranstaltung <b>$SeminarName</b> eingetragen und sind damit zugelassen..");
								echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; Hier kommen Sie zu der Veranstaltung</a>";
								if ($send_from_search)
								    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
								echo "<br><br></td></tr></table>";
								page_close();
								die;
							} else { //kein Platz mehr im Kontingent --> auf Warteposition in admission_seminar_user
								$db5->query("SELECT position FROM admission_seminar_user ORDER BY position DESC");//letzte hoechste Position heruasfinden
								$db5->next_record();
							 	$db4->query("INSERT INTO admission_seminar_user SET user_id = '$user->id', seminar_id = '$id', studiengang_id = '$sem_verify_suggest_studg', status='awaiting', mkdate='".time()."', position='".($db5->f("position")+1)."'  ");
								parse_msg ("info§Es gibt zur Zeit keinen freien Platz in der teilnahmebeschr&auml;nkten Veranstaltung <b>$SeminarName</b>. Sie wurden jedoch auf Platz ".($db5->num_rows()+1)." in die Warteliste eingetragen. <br /> Sie werden autoamtisch eingetragen, sobald ein Platz f&uuml;r sie frei wird.");
								echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
								if ($send_from_search)
						    			echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
								echo "<br	><br></td></tr></table>";
								page_close();
								die;
							}
						}
						echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Die Veranstaltung <b>$SeminarName</b> ist teilnehmerbeschr&auml;nkt.<br>Sowas macht Laune!<br></td></tr>";
					} 
				}
				elseif (($SemSecLevelWrite==2) && ($SemSecLevelRead==2)) {//Paswort auf jeden Fall erforderlich, also her damit
					echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Bitte Passwort f&uuml;r die Veranstaltung <b>$SeminarName</b> eingeben.<br><br></td></tr>";
					?>
					</td></tr>					
					<tr><td class="blank" colspan=2>
					<form action="<? echo $sess->pself_url(); ?>" method="POST" >
					&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
					<input type="HIDDEN" name="id" value="<? echo $id;?>">
					<input type="HIDDEN" name="hashpass" value="">
					<input onSubmit="verifySeminar();return true;" type="SUBMIT" value="abschicken">
					</form>
					<?
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; zur&uuml;ck zur Startseite</a>";
				    	if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
					echo "<br><br>";
					?>
					</td></tr></table>				
					<?
					page_close();
					die;
				}
				elseif ($SemSecLevelWrite==2) {//nur passwort fuer Schreiben, User koennte ohne Passwort als 'User' in das Seminar
					echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Bitte Passwort f&uuml;r die Veranstaltung <b>$SeminarName</b> eingeben.<br><br></td></tr>";
					echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Falls sie das Passwort jetzt noch nicht eingeben m&ouml;chten, k&ouml;nnen sie mit Leseberechtigung an der Veranstaltung teilnehmen.<br><br></td></tr>";
					echo "<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Bitte klicken sie dazu<a href=\"sem_verify.php?SemIDtemp=$id\"> hier</a>!<br><br></td></tr>";
					?>
					</td></tr>					
					<tr><td class="blank" colspan=2>
					<form action="<? echo $sess->pself_url(); ?>" method="POST" >
					&nbsp; &nbsp; <input type="PASSWORD" name="pass" size="12">
					<input type="HIDDEN" name="id" value="<? echo $id;?>">
					<input type="HIDDEN" name="hashpass" value="">
					<input onSubmit="verifySeminar();return true;" type="SUBMIT" value="abschicken">
					</form>
					<?
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp; &nbsp; zur&uuml;ck zur Startseite</a>";
				    	if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
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
					parse_msg ("info§Um an der Veranstaltung <b>$SeminarName</b> teilnehmen zu k&ouml;nnen, m&uuml;ssen sie zumindest auf die Registrierungsmail geantwortet haben!");
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
					if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
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
			$db->query("INSERT INTO seminar_user VALUES ('$id', '$user->id', '$InsertStatus','$group'', '', '".time()."')");
			parse_msg ("msg§Sie wurden mit dem Status <b>$InsertStatus </b>in die Veranstaltung <b>$SeminarName</b> eingetragen.");
			echo"<tr><td class=\"blank\" colspan=2><a href=\"seminar_main.php?auswahl=$id\">&nbsp; &nbsp; Hier kommen Sie zu der Veranstaltung</a>";
			if ($send_from_search)
			    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
			echo "<br><br></td></tr></table>";
			page_close();
			die;
		}
	}
	
  if ($SemSecLevelRead==0) {//nur wenn das Seminar wirklich frei ist geht's hier weiter
	echo"<tr><td class=\"blank\" colspan=2>&nbsp; &nbsp; Um zu der Veranstaltung <b>$SeminarName</b> zu gelangen, klicken sie bitte<a href=\"seminar_main.php?auswahl=$id\"> hier</a>!<br><br></td></tr></table>";
  }	else {//keine Rechte f&uuml;r das Seminar
		parse_msg ("error§Sie habe nicht die erforderlichen Rechte, um an der Veranstaltung <b>$SeminarName</b> teilnehmen zu d&uuml;rfen!");
		echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
		if ($send_from_search)
	    		echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
		echo "<br><br></td></tr></table>";
	}
	
	page_close()
 ?>
</body>
</html>