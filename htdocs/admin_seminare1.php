<?
/*
admin_seminare1.php - Seminar-Verwaltung von Stud.IP.
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

  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
	$auth->login_if($auth->auth["uid"] == "nobody");
  $perm->check("tutor");

## Set this to something, just something different...
  $hash_secret = "dslkjjhetbjs";
  
	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
?>

 <SCRIPT language="JavaScript">
<!--

function checkname(){
 var checked = true;
 if (document.details.Name.value.length<3) {
    alert("Bitte geben Sie einen Namen für die Veranstaltung ein!");
 		document.details.Name.focus();
    checked = false;
    }
 return checked;
}

function checkbereich(){
 var checked = true;
 if (document.details["bereich[]"].selectedIndex < 0) {
    alert("Bitte geben Sie mindestens einen Studienbereich für die Veranstaltung ein!");
 		document.details["bereich[]"].focus();
    checked = false;
 } else {
		if (document.details["bereich[]"].options[document.details["bereich[]"].selectedIndex].value == "nix") {
			alert("Die Zeilen die mit '--' beginnen sind keine gültigen Bereiche, sie dienen nur der Orientierung.\nBitte geben Sie einen gültigen Bereich ein!");
 			document.details["bereich[]"].focus();
    	checked = false;
		}
 }
 return checked;
}

function checkdozent(){
 var checked = true;
 if (document.details["Dozenten[]"].selectedIndex < 0) {
    alert("Bitte geben Sie mindestens einen Dozenten für die Veranstaltung ein!");
 		document.details["Dozenten[]"].focus();
    checked = false;
 }
 return checked;
}

function checkdata(command){
 var checked = true;
 if (!checkname())
 	checked = false;
 if (!checkbereich())
 	checked = false;

<? if ($perm->have_perm("admin"))  // bei Dozenten muessen wir das nicht testen, da sie automatisch selber eingetragen werden
	echo "if (!checkdozent()) checked = false;";
?>

 if (checked) {
   document.details.method = "post";
   document.details.action = "<?php echo $PHP_SELF ?>";
   document.details.s_command.value = command;
   document.details.submit();
 }
 return checked;
}
 	

function checkdata_without_bereich(command){
 var checked = true;
 if (!checkname())
 	checked = false;

<? if ($perm->have_perm("admin"))  // bei Dozenten muessen wir das nicht testen, da sie automatisch selber eingetragen werden
	echo "if (!checkdozent()) checked = false;";
?>

 if (checked) {
   document.details.method = "post";
   document.details.action = "<?php echo $PHP_SELF ?>";
   document.details.s_command.value = command;
   document.details.submit();
 }
 return checked;
}

//-->
</SCRIPT>

<?
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); // Funktionen zum Loeschen von Terminen
require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php"); // Funktionen zum Loeschen von Dokumenten
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/admission.inc.php");
	
## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$cssSw = new cssClassSwitcher;

$user_id = $auth->auth["uid"];
$msg = "";

## Change Seminar parameters
if (($s_command=="edit") && ($s_send)) {
	$run = TRUE;
	## Do we have permission to do so?
	$db2->query("select * from seminar_user where Seminar_id = '$s_id' AND user_id = '$user_id'");
	$db2->next_record();
	$my_perms=$db2->f("status");

	if ((!$perm->have_perm("admin")) && ($db2->f("status") != "dozent") && ($db2->f("status") != "tutor")) {
		$msg .= "error§Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.§";
		$run = FALSE;
	}
			
	if ($perm->have_perm("admin") && !$perm->have_perm("root")) {
		$db2->query("select inst_perms from seminare LEFT JOIN user_inst USING(Institut_id) where Seminar_id = '$s_id' AND user_id = '$user_id'");
			if (!$db2->next_record() || $db2->f("inst_perms") != "admin") {
      				$msg .= "error§Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.§";
      				$run = FALSE;
			}
    	}
    	
    //Load necessary data from the saved lecture
   $db->query("SELECT * FROM seminare WHERE Seminar_id = '$s_id' ");
   $db->next_record();

    ## Do we have all necessary data?
    if (empty($Name)) {
      $msg .= "error§Bitte geben Sie den <B>Namen der Veranstaltung</B> ein!§";
      $run = FALSE;
    }

    if ((empty($Institut)) && (!$my_perms== "tutor")) {
      $msg .= "error§Bitte geben Sie eine <B>Heimat-Einrichtung</B> an!§";
      $run = FALSE;
    }

    if ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["bereiche"]) {
    if (empty($bereich)) {
      $msg .= "error§Bitte geben Sie wenigstens einen <B>Studienbereich</B> an!§";
      $run = FALSE;
		} else {
			$dochnoch = "nein";    // Test ob ausser Murks auch etwas Sinnvolles angeklickt wurde
			while (list($key,$val) = each($bereich)) 
				if ($val != "nix") $dochnoch = "ja";
			if ($dochnoch != "ja") {
				$msg .= "error§Sie haben nur einen ung&uuml;ltigen Studienbereich ausgew&auml;hlt. Bitte geben Sie wenigstens einen <B>Studienbereich</B> an!§";
    	  $run = FALSE;
			}
			reset ($bereich);
		}
	}
    
    if ($perm->have_perm("admin") && empty($Dozenten)) {
      $msg .= "error§Bitte geben Sie wenigstens einen <B>Dozenten</B> an!§";
      $run = FALSE;
      }
     
    //Checks for admission turnout (only important if an admission is set)
    if ($db->f("admission_type")) {
    	if ($turnout < 1) {
		$msg .= "error§Diese Veranstaltung ist teilnahmebeschr&auml;nkt. Daher m&uuml;ssen Sie wenigstens einen Teilnehmer zulassen!§";
		$run=FALSE;
	}
    	if (($run) &&($turnout < $db->f("admission_turnout")))
		$msg .= "info§Diese Veranstaltung ist teilnahmebeschr&auml;nkt. Wenn Sie die Teilnehmerzahl verringern, m&uuml;ssen Sie evtl. Nutzer, die bereits einen Platz in der Veranstaltung erhalten haben, manuell entfernen!§";

	if ($turnout > $db->f("admission_turnout"))
		$do_update_admission=TRUE;
    	
    }

    if ($run) { // alle Angaben ok
    ## Create timestamps
    $start_time = mktime($stunde,$minute,0,$monat,$tag,$jahr);
    $duration=mktime($end_stunde,$end_minute,0,$monat,$tag,$jahr)-$start_time;

    if ($Schreibzugriff < $Lesezugriff)
	$Schreibzugriff = $Lesezugriff;			// hier wusste ein Dozent nicht, was er tat
	
    ## Update Seminar information.
    $query = "UPDATE seminare SET Veranstaltungsnummer='$VeranstaltungsNummer',";
    if ($my_perms != "tutor")
	$query .="Institut_id='$Institut', ";
    $query .= "Name='$Name', Untertitel='$Untertitel',
			status='$Status', Beschreibung='$Beschreibung',  Ort='$Ort',
			Sonstiges='$Sonstiges', Lesezugriff='$Lesezugriff', Schreibzugriff='$Schreibzugriff',
			art='$art', teilnehmer='$teilnehmer', vorrausetzungen='$vorrausetzungen', lernorga='$lernorga',
			leistungsnachweis='$leistungsnachweis', ects='$ects', admission_turnout='$turnout'
			WHERE Seminar_id='$s_id'";
    $db->query($query);
    
    if ($do_update_admission)
    	update_admission($s_id);

    
    if ($db->affected_rows()) {
	$msg .= "msg§Die Grund-Daten der Veranstaltung wurden ver&auml;ndert.§";
	$db->query("UPDATE seminare SET chdate='".time()."' WHERE Seminar_id='$s_id'");
	}
	
	
	if ($my_perms !="tutor") {
		$query = "UPDATE seminar_user SET status = \"autor\" WHERE Seminar_id = '$s_id' AND status = \"dozent\"";
		$db3->query($query);			       // alte Dozenten zu Autor
		$query = "UPDATE seminar_user SET status = \"autor\" WHERE Seminar_id = '$s_id' AND status = \"tutor\"";
		$db3->query($query);			       // alte Tutoren zu Autor
		
		//Starttime des Seminar ermitteln
		$query = "SELECT start_time FROM seminare WHERE Seminar_id = '$s_id' ";
		$db->query($query);
		$db->next_record();
		$temp_admin_seminare_start_time=$db->f("start_time");

		if (isset($Dozenten)) {				// alle ausgewählten Dozenten durchlaufen
			$self_included = 0;
			while (list($key,$val) = each($Dozenten)) {
				$start = strrpos($val,'(') + 1;
				$length = strrpos($val,')') - $start;
				$tempDozent = substr($val,$start,$length);
				$query = "SELECT user_id FROM auth_user_md5 WHERE username = '$tempDozent'";
				$db3->query($query);			       // Dozenten_id suchen
				if ($db3->next_record()) {
					$tempDozent_id = $db3->f("user_id");
					$group=select_group($temp_admin_seminare_start_time, $tempDozent_id);					
					$query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id'";
					$db4->query($query);
					if ($db4->next_record())	{		  // User schon da
						$query = "UPDATE seminar_user SET status = \"dozent\" WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id'";
						}						
					else					     // User noch nicht da
						$query = "insert into seminar_user values('$s_id','$tempDozent_id',\"dozent\",'$group', '', '".time()."')";
					$db3->query($query);			     // Dozent eintragen
				}
			}
		}

		if (isset($Tutoren)) {				 // alle ausgewählten Tutoren durchlaufen
			while (list($key,$val) = each($Tutoren)) {
				$start = strrpos($val,'(') + 1;
				$length = strrpos($val,')') - $start;
				$tempTutor = substr($val,$start,$length);
				$query = "SELECT user_id FROM auth_user_md5 WHERE username = '$tempTutor'";
				$db3->query($query);			       // Tutor_id suchen
				if ($db3->next_record()) {
					$tempTutor_id = $db3->f("user_id");
					$group=select_group($temp_admin_seminare_start_time, $tempTutor_id);
					$query = "SELECT user_id, status FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$tempTutor_id'";
					$db4->query($query);
					if ($db4->next_record()) {
						if ($db4->f("status") == "dozent")				// User schon da aber Dozent, also nix tun! (Selbstgedradierung ist zwar schoen, wollen wir aber nicht, sonst ist der Dozent futsch)
							;
						else									//User schon da aber was anderes (unterhalb Tutor), also Hochstufen.
						$query = "UPDATE seminar_user SET status = \"tutor\" WHERE Seminar_id = '$s_id' AND user_id = '$tempTutor_id'";
					} else										// User noch nicht da
						$query = "insert into seminar_user values('$s_id','$tempTutor_id',\"tutor\",'$group', '', '".time()."')";
					$db3->query($query);							// Tutor eintragen
					$query = "DELETE FROM admission_seminar_user WHERE seminar_id = '$s_id' AND user_id = '$tempTutor_id' ";
					$db3->query($query);							//delete possible entrys in wainting list
					if ($db3->affected_rows())
						renumber_admission($s_id);
				}
			}
		}

		if (!$perm->have_perm("admin")) { // wenn nicht admin, aktuellen Dozenten eintragen
			$tempDozent_id = $auth->auth["uid"];
			
			$query = "SELECT username FROM auth_user_md5 WHERE user_id = '$tempDozent_id'";
			$db3->query($query);
			if ($db3->next_record())			   // Namen besorgen
				$tempDozent_name = $db3->f("username");
			$group=select_group($temp_admin_seminare_start_time, $tempDozent_id);
			$query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id'";
			$db4->query($query);
			if ($db4->next_record())			  // User schon da
				$query = "UPDATE seminar_user SET status = \"dozent\" WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id'";
			else					     // User noch nicht da
				$query = "insert into seminar_user values('$s_id','$tempDozent_id',\"dozent\",'$group', '', '".time()."')";
			$db3->query($query);
		}
	}
	
		if (isset($b_institute)) 
			{
			## Alle alten beteiligten Institute rauswerfen, dann noch mal sauber neu eintragen
			    $query = "DELETE from seminar_inst where Seminar_id='$s_id'";
			    $db3->query($query);
			while (list($key,$val) = each($b_institute)) {       // alle ausgewählten beteiligten Institute durchlaufen
				$query = "INSERT INTO seminar_inst values('$s_id','$val')";
				$db3->query($query);			     // Institut eintragen
			}
		}
		
		//Bereiche aendern
		if ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["bereiche"]) {
		if (isset($bereich)) 
			{
			## Alle alten Eintraege aus seminar_bereich rauswerfen, dann noch mal sauber neu eintragen
			    $query = "DELETE from seminar_bereich where Seminar_id='$s_id'";
			    $db3->query($query);

			while (list($key,$val) = each($bereich)) 
				{ 
				IF ($val != "nix")
					{      
					$query = "INSERT IGNORE INTO seminar_bereich VALUES('$s_id','$val')";
					$db3->query($query);			     // Bereich eintragen
					}
				}
			}
		} else {
			## nur alte Eintraege rauswerfen, falls voher Kategorie mit Bereichen gewaehlt war
			$query = "DELETE from seminar_bereich where Seminar_id='$s_id'";
			$db3->query($query);
		}
			
		
	     // Heimat-Institut ebenfalls eintragen, wenn noch nicht da		
		if (!$my_perms == "tutor") {
			$query = "INSERT IGNORE INTO seminar_inst values('$s_id','$Institut')";
			$db3->query($query);
			}
	}
}

## Details-Formular
include ("links_admin.inc.php");

if ($s_command) {

  $db->query("SELECT x.*, y.Name AS Institut FROM seminare x LEFT JOIN Institute y USING (institut_id) WHERE x.Seminar_id = '$s_id'");
  $db->next_record();
  $user_id = $auth->auth["uid"];
  $db2->query("select * from seminar_user where Seminar_id = '$s_id' and user_id = '$user_id'");
  $db2->next_record();
  $my_perms=$db2->f("status");
  if ($SEM_TYPE[$db->f("status")]["name"] == $SEM_TYPE_MISC_NAME) 	
	$tmp_typ = "Veranstaltung"; 
  else
	$tmp_typ = $SEM_TYPE[$db->f("status")]["name"];

  
	?>

	<table border=0 align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr valign=top align=middle>
		<?
		echo "<td class=\"topic\"colspan=2 align=\"left\"><b>&nbsp;", $tmp_typ, ": ",htmlReady(substr($db->f("Name"), 0, 60));
		if (strlen($db->f("Name")) > 60)
			echo "... ";
		echo " - ";
		if ($s_command=="edit")
			echo "Grunddaten</b></td>";
		?>
	</tr>
	<tr><td class="blank" colspan=2><br>
	<?
	if ($s_command=="edit") {
		$msg.="info§<font size=-1>Achtung: alle <b>FETT</b> gedruckten Kategorien M&Uuml;SSEN ausgef&uuml;llt werden!<br>Wenn Sie unsicher bei den m&ouml;glichen Eingaben sind, finden Sie in der Kopfzeile auf dem Symbol <img src='pictures/hilfe.gif' border=0> Hilfe</font>§";
		parse_msg($msg);
		echo "</td></tr><tr><td class=\"blank\" colspan=2>";
	}
	
	?>
	<table border=0 align="center" cellspacing=0 cellpadding=2 width=99%>

<?

	// ab hier Anzeigeroutinen ///////////////////////////////////////////////


//	if ($auth->auth["jscript"]) echo "<form name=\"details\" onsubmit=\"return checkdata()\">";
//	else echo "<form name=\"details\" method=\"post\" action=\"",$sess->pself_url(),"\">";
		echo "<form name=\"details\" method=\"post\" action=\"",$PHP_SELF,"\">";
	
?>
			<input type="hidden" name="s_id"   value="<?php $db->p("Seminar_id") ?>">
			<tr>   
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<?
					if ($s_id != "" && $s_command=="edit" &&
							($perm->have_perm("admin") ||
							($my_perms == "dozent" || $my_perms == "tutor"))):
						?>
						<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" src="pictures/buttons/uebernehmen-button.gif" border=0 name="s_edit" value=" Ver&auml;ndern ">
						<?php
					else:
						?>
						&nbsp;
						<?php
					endif;
					?>
				<input type="hidden" name="s_command" value="<? echo $s_command ?>">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" align=right><b>Name der Veranstaltung</b></td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <input type="text" name="Name" onchange="checkname()" size=58 maxlength=254 value="<?php echo htmlReady($db->f("Name")) ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Untertitel der Veranstaltung</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <input type="text" name="Untertitel" size=58 maxlength=254 value="<?php echo htmlReady($db->f("Untertitel")) ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><b>Typ der Veranstaltung</b></td>
				<td class="<? echo $cssSw->getClass() ?>"  align=left colspan=2>&nbsp; <select name="Status">
				<?
				if (!$perm->have_perm("admin")) {
					$i=0;
					for ($i=1; $i <= sizeof($SEM_TYPE); $i++) {
						if ($SEM_TYPE[$i]["class"] == $SEM_TYPE[$db->f("status")]["class"])
							printf ("<option %s value=%s>%s</option>", $db->f("status")== $i ? "selected" : "", $i, htmlReady($SEM_TYPE[$i]["name"]));
					}
					?>
				</select><?echo "&nbsp;in der Kategorie <b>".htmlReady($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"])."</b>";?></td>
				<?
				} else {
					$i=0;
					foreach ($SEM_TYPE as $a) {
						$i++;
						if (($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) || (!$SEM_CLASS[$a["class"]]["bereiche"]))
							printf ("<option %s value=%s>%s (%s)</option>", $db->f("status")== $i ? "selected" : "", $i, htmlReady($a["name"]), htmlReady($SEM_CLASS[$a["class"]]["name"]));
					}
					printf ("</select></td>");
				}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Art der Veranstaltung</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <input type="text" name="art" size=30 maxlength=254 value="<?php echo htmlReady($db->f("art")) ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Veranstaltungs-Nummer</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <input type="int" name="VeranstaltungsNummer" size=6 maxlength=6 value="<?php echo htmlReady($db->f("VeranstaltungsNummer")) ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>ECTS-Punkte</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <input type="int" name="ects" size=6 maxlength=32 value="<?php echo htmlReady($db->f("ects")) ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><? printf ("%smax. Teilnehmeranzahl%s", ($db->f("admission_type")) ? "<b>" : "",  ($db->f("admission_type")) ? "</b>" : ""); ?></td>
				<td class="<? echo $cssSw->getClass() ?>"  align=left colspan=2>&nbsp; <input type="int" name="turnout" size=6 maxlength=4 value="<?php echo $db->f("admission_turnout") ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Raum &nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <input type="text" name="Ort" size=20 maxlength=254 value="<?php echo htmlReady($db->f("Ort")) ?>"></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Beschreibung &nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="Beschreibung" cols=58 rows=6><?php echo htmlReady($db->f("Beschreibung")) ?></textarea></td>
			</tr>			
			<tr>
				<?
					if ($my_perms != "tutor") {
						echo "<td class=\"".$cssSw->getClass()."\" align=right><b>Heimat-Einrichtung</b></td>";
						echo "<td class=\"".$cssSw->getClass()."\" align=left colspan=2>&nbsp; ";
						echo "<select name=\"Institut\">";
						if (!$perm->have_perm("admin"))
							$db3->query("SELECT * FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (user_id = '$user_id' AND (inst_perms = 'dozent' OR inst_perms = 'tutor')) GROUP BY Institute.institut_id ORDER BY Name");
						else if (!$perm->have_perm("root"))
							$db3->query("SELECT * FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'admin') GROUP BY Institute.institut_id ORDER BY Name");
						else
							$db3->query("SELECT * FROM Institute ORDER BY Name");
						while ($db3->next_record()) {
							printf ("<option %s value=%s> %s</option>", $db3->f("Institut_id") == $db->f("Institut_id") ? "selected" : "", $db3->f("Institut_id"), htmlReady(my_substr($db3->f("Name"),0,60)));
							}
						}
					else {
						echo "<td class=\"".$cssSw->getClass()."\" align=right>Heimat-Einrichtung</td>";
						echo "<td class=\"".$cssSw->getClass()."\" align=left colspan=2>&nbsp; ";
						echo "<b>".htmlReady($db->f("Institut"))."</b>";
						}
					echo "</select>";
				?>
				</td>
			</tr>				
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>beteiligte Einrichtungen</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <select  name="b_institute[]" MULTIPLE SIZE=8>
					<?php
					$db3->query("SELECT * FROM Institute ORDER BY Name");
					while ($db3->next_record()) {
						$tempInstitut_id = $db3->f("Institut_id");
						$db4->query("SELECT * FROM seminar_inst WHERE Seminar_id = '$s_id' AND institut_id = '$tempInstitut_id'");
						if ($db4->next_record()) {
							printf ("<option selected value=%s> %s</option>", $tempInstitut_id, htmlReady(my_substr($db3->f("Name"),0,60)));
						} else {
							printf ("<option value=%s> %s</option>", $tempInstitut_id, htmlReady(my_substr($db3->f("Name"),0,60)));
						}
					}
					?>
				</select></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>   
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<?
					if ($s_id != "" && $s_command=="edit" &&
							($perm->have_perm("admin") ||
							($my_perms == "dozent" || $my_perms == "tutor"))):
						?>
						<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" src="pictures/buttons/uebernehmen-button.gif" border=0 name="s_edit" value=" Ver&auml;ndern ">
						<?php
					else:
						?>
						&nbsp;
						<?php
					endif;
					?>
				<input type="hidden" name="s_command" value="<? echo $s_command ?>">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>     <!-- Dozenten und Tutoren -->				
			<?
			//Fuer Tutoren eine Sonderregelung, da sie nicht alle Daten aendern duerfen
			if ($my_perms == "tutor") {
				?>
				<td class="<? echo $cssSw->getClass() ?>" align=right>DozentInnen
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; 
				<?
				$db3->query("SELECT Vorname,Nachname FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE status = 'dozent' AND Seminar_id='$s_id' ORDER BY Nachname");
				$i=0;
				while ($db3->next_record()) {
					//$tempDozent_id = $db3->f("user_id");
					//$db4->query("SELECT * FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id' AND Status = 'dozent'");
					//if ($db4->next_record()) {
						if ($i)
							echo ", ";
						echo "<b>", htmlReady($db3->f("Vorname")), " ", htmlReady($db3->f("Nachname")), "</b>";
						$i++;						
					//	}
					}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>TutorInnen
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; 
				<?
				$db3->query("SELECT Vorname,Nachname FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE status = 'tutor' AND Seminar_id='$s_id' ORDER BY Nachname");
				$i=0;
				while ($db3->next_record()) {
					//$tempTutor_id = $db3->f("user_id");
					//$db4->query("SELECT * FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$tempTutor_id' AND Status = 'tutor'");
					//if ($db4->next_record()) {
						if ($i)
							echo ", ";
						echo "<b>", htmlReady($db3->f("Vorname")), " ", htmlReady($db3->f("Nachname")), "</b>";
						$i++;						
					//	}
					}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <font color="#FF0000">Die Personendaten k&ouml;nnen Sie mit Ihrem Status (Tutor) nicht bearbeiten!</font></td>
				<?
				}
			else
				{
				if ($perm->have_perm("admin"))
					echo "<td class=\"".$cssSw->getClass()."\" align=right><b>DozentInnen</b></td>";
				else
					echo "<td class=\"".$cssSw->getClass()."\" align=right>DozentInnen</td>";
?>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <select name="Dozenten[]" MULTIPLE SIZE=10>
					<?php
					unset($tempDozent_id);
					$db4->query("SELECT seminar_user.user_id,status FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE Seminar_id = '$s_id' AND Status IN('dozent','tutor')");
					while ($db4->next_record()){
						$tempDozent_id[$db4->f("user_id")] = $db4->f("status");
					}
					$db3->query("SELECT * FROM auth_user_md5 WHERE perms = 'dozent' ORDER BY Nachname");
					while ($db3->next_record()) {
						if ($tempDozent_id[$db3->f("user_id")]=="dozent") {
							printf ("<option selected> %s, %s (%s) - %s</option>", htmlReady($db3->f("Nachname")), htmlReady($db3->f("Vorname")), $db3->f("username"), $db3->f("perms"));
						} else {
							printf ("<option> %s, %s (%s) - %s</option>", htmlReady($db3->f("Nachname")), htmlReady($db3->f("Vorname")), $db3->f("username"), $db3->f("perms"));
						}
					}
					?>
				</select></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>TutorInnen</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <select name="Tutoren[]" MULTIPLE SIZE=10>
					<?php
					$db3->query("SELECT * FROM auth_user_md5 WHERE perms = 'tutor' OR perms = 'dozent' ORDER BY Nachname");
					while ($db3->next_record()) {
						if ($tempDozent_id[$db3->f("user_id")]=="tutor") {
							printf ("<option selected> %s, %s (%s) - %s</option>", htmlReady($db3->f("Nachname")), htmlReady($db3->f("Vorname")), $db3->f("username"), $db3->f("perms"));
						} else {
							printf ("<option> %s, %s (%s) - %s</option>", htmlReady($db3->f("Nachname")), htmlReady($db3->f("Vorname")), $db3->f("username"), $db3->f("perms"));
						}
					}
					?>
				</select></td>
				<?
				}
				?>
			</tr>
			<?
			//Bereichsauswahl
			if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) {
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><b>Studienbereich(e)</b></td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <select MULTIPLE name="bereich[]" onchange="checkbereich()" SIZE=12>
					<?php
					$fachtmp="0";
					//Anzeige der eigenen Faecher
					if (!$perm->have_perm("root"))
					$db3->query("SELECT DISTINCT bereiche.bereich_id, bereiche.name, bereich_fach.fach_id FROM bereiche LEFT JOIN bereich_fach USING(bereich_id) LEFT OUTER JOIN fach_inst USING(fach_id) LEFT OUTER JOIN user_inst USING(institut_id) WHERE user_id ='$user_id' AND user_inst.inst_perms !='user' AND user_inst.inst_perms !='autor' AND user_inst.inst_perms !='tutor' ORDER BY bereich_fach.fach_id");
					while ($db3->next_record()) 
						{
						IF ($fachtmp != $db3->f("fach_id"))
							{
					//Hier werden die Faecherueberschriften ausgegeben 
							$fachtmp = $db3->f("fach_id");
							$db4->query("SELECT name from faecher WHERE fach_id = '$fachtmp'");	
							while ($db4->next_record()) 
								{
								ECHO "<option value = nix>==========================</option>";
								ECHO "<option value = nix>==&nbsp;".htmlReady(my_substr($db4->f("name"),0,58))."</option>";
								ECHO "<option value = nix>==========================</option>";
								}
							}
						$bereichtmp =	 $db3->f("bereich_id");
						$db4->query("SELECT bereich_id FROM seminar_bereich WHERE seminar_id = '$s_id' AND bereich_id = '$bereichtmp'");
					//Anzeige ob Selected oder nicht
						IF ($db4->next_record()) 
							printf ("<option selected VALUE=%s>&nbsp;%s</option>", $db3->f("bereich_id"), htmlReady(my_substr($db3->f("name"),0,60)));
						ELSE
							printf ("<option VALUE=%s>&nbsp;%s</option>", $db3->f("bereich_id"), htmlReady(my_substr($db3->f("name"),0,60)));
						$fachtmp = $db3->f("fach_id");
						}		
						
					// Anzeige der anderen Faecher
					$query = "SELECT bereiche.bereich_id, bereiche.name, bereich_fach.fach_id FROM bereiche LEFT JOIN bereich_fach USING(bereich_id)";
					$db3->query("SELECT fach_id FROM fach_inst LEFT JOIN user_inst USING(institut_id) WHERE user_id ='$user_id' AND user_inst.inst_perms !='user' AND user_inst.inst_perms !='autor' AND user_inst.inst_perms !='tutor' GROUP BY fach_id");
					while ($db3->next_record()) 
						$filter .= " fach_id != '".$db3->f("fach_id")."' AND";
					$filter = substr($filter, 1, -3);
					IF (strlen($filter) > 4) $query .= " WHERE " .$filter;
					$query .= " ORDER BY bereich_fach.fach_id";
					$fachtmp="0";
					$db3->query($query);
					while ($db3->next_record()) 
						{
						IF ($fachtmp != $db3->f("fach_id"))
							{
							//Hier werden die Faecherueberschriften ausgegeben 
							$fachtmp = $db3->f("fach_id");
							$db4->query("SELECT name from faecher WHERE fach_id = '$fachtmp'");	
							while ($db4->next_record()) 
								{
								ECHO "<option value = nix>------------------------------------------------------------</option>";
								ECHO "<option value = nix>--&nbsp;".htmlReady(my_substr($db4->f("name"),0,60))."</option>";
								ECHO "<option value = nix>------------------------------------------------------------</option>";
								}
							}
						$bereichtmp =	 $db3->f("bereich_id");
						$db4->query("SELECT bereich_id FROM seminar_bereich WHERE seminar_id = '$s_id' AND bereich_id = '$bereichtmp'");
						//Anzeige ob Selected oder nicht
						IF ($db4->next_record()) 
							printf ("<option selected VALUE=%s>&nbsp;%s</option>", $db3->f("bereich_id"), htmlReady(my_substr($db3->f("name"),0,60)));
						ELSE
							printf ("<option VALUE=%s>&nbsp;%s</option>", $db3->f("bereich_id"), htmlReady(my_substr($db3->f("name"),0,60)));
						$fachtmp = $db3->f("fach_id");
						}					
				?>
				</select></td>
			<?
			}
			?>
			<tr <?$cssSw->switchClass() ?>>   
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<?
					if ($s_id != "" && $s_command=="edit" &&
							($perm->have_perm("admin") ||
							($my_perms == "dozent" || $my_perms == "tutor"))):
						?>
						<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" src="pictures/buttons/uebernehmen-button.gif" border=0 name="s_edit" value=" Ver&auml;ndern ">
						<?php
					else:
						?>
						&nbsp;
						<?php
					endif;
					?>
				<input type="hidden" name="s_command" value="<? echo $s_command ?>">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Zeit</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <b><? echo htmlReady(view_turnus ($s_id)) ?></b>&nbsp; </td>
			</td>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Semester</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <b><? echo get_semester ($s_id) ?></b>&nbsp; </td>
			</td>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Erster Termin</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <b><? echo veranstaltung_beginn ($s_id) ?></b>&nbsp; </td>
			</td>
			</tr>
			<?
			if (vorbesprechung ($s_id)) {
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Vorbesprechung</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <b><? echo vorbesprechung ($s_id) ?></b>&nbsp; </td>
			</td>
			<?
			}
			?>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>">&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <font color="#FF0000">Bitte nutzen Sie den Menupunkt <? echo "<a href=\"admin_metadates.php?seminar_id=$s_id\"><b>Zeiten</b></a>" ?>, um diese Angaben zu ver&auml;ndern!</font></td>
			<tr <?$cssSw->switchClass() ?>>   
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<?
					if ($s_id != "" && $s_command=="edit" &&
							($perm->have_perm("admin") ||
							($my_perms == "dozent" || $my_perms == "tutor"))):
						?>
						<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" src="pictures/buttons/uebernehmen-button.gif" border=0 name="s_edit" value=" Ver&auml;ndern ">
						<?php
					else:
						?>
						&nbsp;
						<?php
					endif;
					?>
				<input type="hidden" name="s_command" value="<? echo $s_command ?>">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Teilnehmer</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="teilnehmer" cols=58 rows=2><?php echo htmlReady($db->f("teilnehmer")) ?></textarea></td>
			</tr>

			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Voraussetzungen</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="vorrausetzungen" cols=58 rows=2><?php echo htmlReady($db->f("vorrausetzungen")) ?></textarea></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Lernorganisation</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="lernorga" cols=58 rows=2><?php echo htmlReady($db->f("lernorga")) ?></textarea></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Leistungsnachweis</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="leistungsnachweis" cols=58 rows=2><?php echo htmlReady($db->f("leistungsnachweis")) ?></textarea></td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right>Sonstiges</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="Sonstiges" cols=58 rows=4><?php echo htmlReady($db->f("Sonstiges")) ?></textarea></td>
			</tr>
			<?
			$mkstring=date ("d.m.Y, G:i", $db->f("mkdate"));
			if (!$db->f("mkdate"))
				$mkstring="unbekannt";
			$chstring=date ("d.m.Y, G:i", $db->f("chdate"));
			if (!$db->f("chdate"))
				$chstring="unbekannt";
			?>	
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" colspan=3 align="right">
					<?
					echo "<font size=-1><i>Veranstaltung angelegt am <b>$mkstring</b>, letzte &Auml;nderung der Veranstaltungsdaten am <b>$chstring</b></font>&nbsp; <br />&nbsp; ";
					?>
				</td>
			</tr>
			<tr>   
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<?
					if ($s_id != "" && $s_command=="edit" &&
							($perm->have_perm("admin") ||
							($my_perms == "dozent" || $my_perms == "tutor"))):
						?>
						<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" src="pictures/buttons/uebernehmen-button.gif" border=0 name="s_edit" value=" Ver&auml;ndern ">
						<?php
					else:
						?>
						&nbsp;
						<?php
					endif;
					?>
				<input type="hidden" name="s_command" value="<? echo $s_command ?>">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
		</form>
	</table>
	</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<?php
}

?>
</table>
<?
page_close();
?>
</body>
</html>
<!-- $Id$ -->
