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
			  elseif ($SemSecLevelWrite==1){//Hat sich der globale Status in der zwischenzeit ge&auml;ndert? Dann hochstufen
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
						parse_msg("info§Sie sind schon mit der Berechtigung <b>$SemUserStatus</b> f&uuml;r die Veranstaltung <b>$SeminarName</b> freigeschaltet. Wenn sie auf die Registrierungsmail antworten, bekommen sie in dieser Veranstaltung Schreibrechte.");
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
				die;
			}
		} else {//User ist noch nicht eingetragen in seminar_user
			if ($perm->have_perm("autor")) {
				if (($SemSecLevelWrite==2)&&($SemSecLevelRead==2)) {//Paswort auf jeden Fall erforderlich, also her damit
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
				elseif ($SemSecLevelWrite==2) {//nur passwort f&uuml;r Schreiben, User k&ouml;nnte ohne Passwort als 'User' in das Seminar
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
				if ($SemSecLevelRead>0) {//Lesen d&uuml;rfen nur autoren, also wech hier
					parse_msg ("info§Um an der Veranstaltung <b>$SeminarName</b> teilnehmen zu k&ouml;nnen, m&uuml;ssen sie zumindest auf die Registrierungsmail geantwortet haben!");
					echo "<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
					if ($send_from_search)
					    	echo "&nbsp; |&nbsp;<a href=\"$send_from_search_page\">zur&uuml;ck zur letzten Auswahl</a>";
					echo "<br><br></td></tr></table>";
					page_close();
					die;
				}	else {//Lesen mit Berechtigung 'User' geht
					if ($SemSecLevelWrite==0) {//Wenn Schreiben auch mit Berechtigung 'user' geht, darf es sogar als 'autor' rein (auch wenn es gegen das Grundprizip verstoesst (keine hoeheren Rechte als globale Rechte)
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