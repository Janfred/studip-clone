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

$hash_secret = "dslkjjhetbjs";
  
include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); // Funktionen zum Loeschen von Terminen
require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php"); // Funktionen zum Loeschen von Dokumenten
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/admission.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");	//Funktionen der Statusgruppen
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemTreeSearch.class.php");


// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");
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
 if (document.details["details_chooser[]"].selectedIndex < 0) {
    alert("Bitte geben Sie mindestens einen Studienbereich für die Veranstaltung ein!");
 		document.details["details_chooser[]"].focus();
    checked = false;
 } else {
		if (document.details["details_chooser[]"].options[document.details["details_chooser[]"].selectedIndex].value == 0) {
			alert("Die Zeilen, die unterstrichen sind, dienen nur der Orientierung.\nBitte geben Sie einen gültigen Bereich ein!");
 			document.details["details_chooser[]"].focus();
    	checked = false;
		}
 }
 return checked;
}

function checkdata(command){
 var checked = true;
 if (!checkname())
 	checked = false;
 if (!checkbereich())
 	checked = false;
 if (checked) {
   document.details.method = "post";
   document.details.action = "<?php echo $PHP_SELF ?>";
   document.details.submit();
 }
 return checked;
}
 	

function checkdata_without_bereich(command){
 var checked = true;
 if (!checkname())
 	checked = false;
 if (checked) {
   document.details.method = "post";
   document.details.action = "<?php echo $PHP_SELF ?>";
   document.details.submit();
 }
 return checked;
}

//-->
</SCRIPT>

<?
	
## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$cssSw = new cssClassSwitcher;

$user_id = $auth->auth["uid"];
$msg = "";

$st_search = new StudipSemTreeSearch($s_id,"details");


function auth_check() {
	global $perm,$s_id;
	return $perm->have_studip_perm("tutor",$s_id);
}


//delete Tutoren/Dozenten
if ($delete_doz) {
	if (auth_check()) {
		$db2->query ("SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND status = 'dozent' ");
		if (($auth->auth["perm"] == "dozent") && ($delete_doz == get_username($user_id)))
			$msg .= "error§Sie d&uuml;rfen sich nicht selbst aus der Veranstaltung austragen.§";
		elseif ($db2->nf() <2)
			$msg .= sprintf ("error§Die Veranstaltung muss wenigstens <b>einen</b> %s eingetragen haben! Tragen Sie zun&auml;chst einen anderen ein, um diesen zu l&ouml;schen.§", ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["workgroup_mode"]) ? "Leiter" : "Dozenten");
		else {
			$db2->query ("DELETE FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id ='".get_userid($delete_doz)."' ");
			if ($db2->affected_rows()) {
				$msg .= "msg§Der Nutzer <b>".htmlReady(get_fullname_from_uname($delete_doz))."</b> wurde aus der Veranstaltung gel&ouml;scht.§";
				$user_deleted=TRUE;
				RemovePersonStatusgruppeComplete ($delete_doz, $s_id);
			}
		}
	} else
		$msg .= "error§Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.§";
}

if ($delete_tut) {
	if (auth_check()) {
		$db2->query ("DELETE FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id ='".get_userid($delete_tut)."' ");
		if ($db2->affected_rows()) {
			$msg .= "msg§Der Nutzer <b>".htmlReady(get_fullname_from_uname($delete_tut))."</b> wurde aus der Veranstaltung gel&ouml;scht.§";
			$user_deleted=TRUE;
			RemovePersonStatusgruppeComplete ($delete_tut, $s_id);
		}

		$db2->query ("SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND status = 'dozent' ");
		if ($db2->nf() == 0)
			$msg .= sprintf ("error§Btte geben Sie wenigstens <b>einen</b> %s an.§", ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["workgroup_mode"]) ? "Leiter" : "Dozenten");
	} else
		$msg .= "error§Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.§";
}

// Change Seminar parameters
if ($s_send) {
    $run = TRUE;
    if (!auth_check()) {
	   $msg .= "error§Sie haben keine Berechtigung diese Veranstaltung zu ver&auml;ndern.§";
	   $run = FALSE;
    }
    	
    //Load necessary data from the saved lecture
   $db->query("SELECT * FROM seminare WHERE Seminar_id = '$s_id' ");
   $db->next_record();

    // Do we have all necessary data?
    if (empty($Name)) {
      $msg .= "error§Bitte geben Sie den <B>Namen der Veranstaltung</B> ein!§";
      $run = FALSE;
    }

    if ((empty($Institut)) && (!$my_perms== "tutor")) {
      $msg .= "error§Bitte geben Sie eine <B>Heimat-Einrichtung</B> an!§";
      $run = FALSE;
    }

    if ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["bereiche"]) {
    if (empty($_REQUEST['details_chooser'])) {
      $msg .= "error§Bitte geben Sie wenigstens einen <B>Studienbereich</B> an!§";
      $run = FALSE;
		} else {
			for ($i = 0; $i < count($_REQUEST['details_chooser']);++$i){ 
				if ($_REQUEST['details_chooser'][$i] != '0') $dochnoch = "ja";
			}
			if ($dochnoch != "ja") {
				$msg .= "error§Sie haben nur einen ung&uuml;ltigen Studienbereich ausgew&auml;hlt. Bitte geben Sie wenigstens einen <B>Studienbereich</B> an!§";
				$run = FALSE;
			}
		}
	}
    
    //we have to select at least one Dozent!
    if (($perm->have_perm("admin")) && (!$add_doz)) {
	$db2->query ("SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND status = 'dozent' ");
	if ($db2->nf() == 0) {
		$msg .= sprintf ("error§Btte geben Sie wenigstens <b>einen</b> %s an.§", ($SEM_CLASS[$SEM_TYPE[$Status]["class"]]["workgroup_mode"]) ? "Leiter" : "Dozenten");
   		$run = FALSE;
      }
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
			status='$Status', Beschreibung='$Beschreibung', 
			Sonstiges='$Sonstiges', art='$art', teilnehmer='$teilnehmer', 
			vorrausetzungen='$vorrausetzungen', lernorga='$lernorga',
			leistungsnachweis='$leistungsnachweis', ects='$ects', admission_turnout='$turnout'
			WHERE Seminar_id='$s_id'";
    $db->query($query);
    
    if ($do_update_admission)
    	update_admission($s_id);

    
    if ($db->affected_rows()) {
	$msg .= "msg§Die Grund-Daten der Veranstaltung wurden ver&auml;ndert.§";
	$db->query("UPDATE seminare SET chdate='".time()."' WHERE Seminar_id='$s_id'");
	}
	
	//Starttime des Seminar ermitteln
	$query = "SELECT start_time FROM seminare WHERE Seminar_id = '$s_id' ";
	$db->query($query);
	$db->next_record();
	$temp_admin_seminare_start_time=$db->f("start_time");
	
	//a Dozent was added
	if ($add_doz_x) {
		$add_doz_id=get_userid($add_doz);
		$group=select_group($temp_admin_seminare_start_time);
		$query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$add_doz_id'";
		$db2->query($query);
		if ($db2->next_record())					//User schon da
			$query = "UPDATE seminar_user SET status = \"dozent\" WHERE Seminar_id = '$s_id' AND user_id = '$add_doz_id'";
		else								//User noch nicht da
			$query = "INSERT INTO seminar_user values('$s_id','$add_doz_id',\"dozent\",'$group', '', '".time()."')";
		$db3->query($query);					//Dozent eintragen
		$user_added=TRUE;		
	}
	
	//a Tutor was added
	if ($add_tut_x) {
		$add_tut_id=get_userid($add_tut);
		$group=select_group($temp_admin_seminare_start_time);
		$query = "SELECT user_id, status FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$add_tut_id'";
		$db2->query($query);
		if ($db2->next_record()) {
			if ($db2->f("status") == "dozent")		// User schon da aber Dozent, also nix tun! (Selbstgedradierung ist zwar schoen, wollen wir aber nicht, sonst ist der Dozent futsch)
				$query = '';
			else							//User schon da aber was anderes (unterhalb Tutor), also Hochstufen.
				$query = "UPDATE seminar_user SET status = \"tutor\" WHERE Seminar_id = '$s_id' AND user_id = '$add_tut_id'";
		} else								//User noch nicht da
			$query = "INSERT INTO seminar_user values('$s_id','$add_tut_id',\"tutor\",'$group', '', '".time()."')";
		if ($query) {
			$db3->query($query);				//Tutor eintragen
			$user_added=TRUE;
			$query = "DELETE FROM admission_seminar_user WHERE seminar_id = '$s_id' AND user_id = '$add_tut_id' ";
			$db3->query($query);				//delete possible entrys in wainting list
			if ($db3->affected_rows())
				renumber_admission($s_id);
		}
	}

	if (!$perm->have_perm("admin")) { // wenn nicht admin, aktuellen Dozenten eintragen
		$tempDozent_id = $auth->auth["uid"];
		$group=select_group($temp_admin_seminare_start_time);
		$query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id'";
		$db4->query($query);
		if ($db4->next_record())			  		// User schon da
			$query = "UPDATE seminar_user SET status = \"dozent\" WHERE Seminar_id = '$s_id' AND user_id = '$tempDozent_id'";
		else					     			// User noch nicht da
			$query = "INSERT INTO seminar_user values('$s_id','$tempDozent_id',\"dozent\",'$group', '', '".time()."')";
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
			if (isset($_REQUEST['details_chooser'])) {
				$st_search->insertSelectedRanges();
				if ($st_search->num_inserted){
					$msg .= "msg§" . sprintf(_("%s Studienbereiche hinzugefügt."),$st_search->num_inserted) ."§";
				}
				if ($st_search->num_deleted){
					$msg .= "msg§" . sprintf(_("%s Studienbereiche gel&ouml;scht."),$st_search->num_deleted) ."§";
				}
				if ($st_search->num_deleted || $st_search->num_inserted){
					$st_search->init();
				}
			}
		} else {
			// nur alte Eintraege rauswerfen, falls voher Kategorie mit Bereichen gewaehlt war
			$query = "DELETE from seminar_sem_tree where seminar_id='$s_id'";
			$db3->query($query);
		}
		
		
	     // Heimat-Institut ebenfalls eintragen, wenn noch nicht da		
		if (!$my_perms == "tutor") {
			$query = "INSERT IGNORE INTO seminar_inst values('$s_id','$Institut')";
			$db3->query($query);
			}
}

## Details-Formular
if (($s_id) && (auth_check())) {
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
		echo "Grunddaten</b></td>";
		?>
	</tr>
	<tr><td class="blank" colspan=2><br>
	<?
	parse_msg($msg);
	?>
	</td></tr><tr><td class="blank" colspan=2>
	<table border=0 align="center" cellspacing=0 cellpadding=2 width=99%>	
	<?

	// ab hier Anzeigeroutinen ///////////////////////////////////////////////
	echo "<form name=\"details\" method=\"post\" action=\"$PHP_SELF#anker\">";
	?>
			<input type="hidden" name="s_id"   value="<?php $db->p("Seminar_id") ?>">
			<tr>   
				<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
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
				<td class="<? echo $cssSw->getClass() ?>" align=right>Beschreibung</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <textarea name="Beschreibung" cols=58 rows=6><?php echo htmlReady($db->f("Beschreibung")) ?></textarea></td>
			</tr>			
			<tr>
				<?
					if ($my_perms != "tutor") {
						echo "<td class=\"".$cssSw->getClass()."\" align=right><b>Heimat-Einrichtung</b></td>";
						echo "<td class=\"".$cssSw->getClass()."\" align=left colspan=2>&nbsp; ";
						echo "<select name=\"Institut\">";
						if (!$perm->have_perm("admin"))
							$db3->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND (inst_perms = 'dozent' OR inst_perms = 'tutor')) ORDER BY is_fak,Name");
						else if (!$perm->have_perm("root"))
							$db3->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'admin') ORDER BY is_fak,Name");
						else
							$db3->query("SELECT Name,Institut_id,1 AS is_fak,'admin' AS inst_perms FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
						while ($db3->next_record()) {
							printf ("<option %s style=\"%s\" value=\"%s\"> %s</option>", $db3->f("Institut_id") == $db->f("Institut_id") ? "selected" : "",
								($db3->f("is_fak")) ? "font-weight:bold;" : "", $db3->f("Institut_id"), htmlReady(my_substr($db3->f("Name"),0,60)));
							if ($db3->f("is_fak") && $db3->f("inst_perms") == "admin"){
								$db2->query("SELECT a.Institut_id, a.Name FROM Institute a 
											 WHERE fakultaets_id='" . $db3->f("Institut_id") . "' AND a.Institut_id!='" .$db3->f("Institut_id") . "' ORDER BY Name");
								while($db2->next_record()){
									printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $db2->f("Institut_id") == $db->f("Institut_id") ? "selected" : "",
										$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
								}
							}
						}
					} else {
						echo "<td class=\"".$cssSw->getClass()."\" align=right>Heimat-Einrichtung</td>";
						echo "<td class=\"".$cssSw->getClass()."\" align=left colspan=2>&nbsp; ";
						echo "<input type=\"HIDDEN\" name=\"Institut\" value=\"".$db->f("Institut_id")."\" />";
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
					$db3->query("SELECT Name,a.Institut_id,b.Institut_id as beteiligt FROM Institute a LEFT JOIN seminar_inst b ON(a.Institut_id=b.Institut_id AND Seminar_id='$s_id') WHERE a.Institut_id=a.fakultaets_id ORDER BY Name");
					while ($db3->next_record()) {
						printf ("<option %s style=\"font-weight:bold;\" value=\"%s\"> %s</option>", $db3->f("beteiligt") ? "selected" : "",
								$db3->f("Institut_id"), htmlReady(my_substr($db3->f("Name"),0,60)));
						$db2->query("SELECT a.Institut_id, a.Name,b.Institut_id as beteiligt FROM Institute a LEFT JOIN seminar_inst b ON(a.Institut_id=b.Institut_id AND Seminar_id='$s_id')
						WHERE fakultaets_id='" . $db3->f("Institut_id") . "' AND a.Institut_id!='" .$db3->f("Institut_id") . "' ORDER BY Name" );
						while($db2->next_record()){
							printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $db2->f("beteiligt") ? "selected" : "",
								$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
						}
					}
					?>
				</select></td>
			</tr>
			<tr>   
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				<?
				if (($user_added) || ($user_deleted) || ($reset_search_x) || ($search_exp_tut) || ($search_exp_doz))
					print "<a name=\"anker\"></a>";
				?>
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>     <!-- Dozenten und Tutoren -->				
			<?
			//Fuer Tutoren eine Sonderregelung, da sie nicht alle Daten aendern duerfen
			if ($my_perms == "tutor") {
				?>
				<td class="<? echo $cssSw->getClass() ?>" align="right"><? if (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) echo  "DozentInnen"; else echo "LeiterInnen";?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left" colspan="2">&nbsp; 
				<?
				$db3->query("SELECT ". $_fullname_sql['full'] ." FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE status = 'dozent' AND Seminar_id='$s_id' ORDER BY Nachname");
				$i=0;
				while ($db3->next_record()) {
					if ($i)
						echo ", ";
					echo "<b>" . htmlReady($db3->f(0)) . "</b>";
					$i++;						
				}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align=right><? if (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) echo "TutorInnen"; else echo "Mitglieder";?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; 
				<?
				$db3->query("SELECT ". $_fullname_sql['full'] ." FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE status = 'tutor' AND Seminar_id='$s_id' ORDER BY Nachname");
				$i=0;
				while ($db3->next_record()) {
					if ($i)
						echo ", ";
					echo "<b>" . htmlReady($db3->f(0)) . "</b>";
					$i++;						
				}
				?>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <font color="#FF0000">Die Personendaten k&ouml;nnen Sie mit Ihrem Status nicht bearbeiten!</font></td>
				<?
				}
			else
				{
				if ($perm->have_perm("admin"))
					printf ("<td %s align=right><b>%s</b></td>", $cssSw->getFullClass(), (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? "DozentInnen" : "LeiterInnen");
				else
					printf ("<td %s align=right>%s</td>", $cssSw->getFullClass(), (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? "DozentInnen" : "LeiterInnen");
				?>
				<td class="<? echo $cssSw->getClass() ?>" align="left">
					<?
					$db4->query("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, seminar_user.user_id,status,username FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id) WHERE Seminar_id = '$s_id' AND Status = 'dozent' ORDER BY Nachname");
					if ($db4->nf()) {
						while ($db4->next_record()) {
							printf ("&nbsp; <a href=\"%s?delete_doz=%s&s_id=%s#anker\"><img src=\"./pictures/trash.gif\" border=\"0\"></a>&nbsp; <font size=\"-1\"><b>%s (%s)&nbsp; &nbsp; <br />", $PHP_SELF, $db4->f("username"), $s_id, htmlReady($db4->f("fullname")), $db4->f("username"));
						}
					} else {
						printf ("<font size=\"-1\">&nbsp;  Keine %s gew&auml;hlt.</font><br >", ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? "LeiterIn" : "DozentIn");
					}
					?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left" valign="top">
					<?
					$no_doz_found=TRUE;
					if (($search_exp_doz) && ($search_doz_x)) {
						if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["only_inst_user"]) {
							$query3 = sprintf("SELECT institut_id FROM seminar_inst WHERE seminar_id = '%s'", $s_id);
							$db3->query($query3);
							$clause="AND Institut_id IN (";
							$i=0;
							while ($db3->next_record()) {
								if ($i)
									$clause.=", ";
								$clause.=" '".$db3->f("institut_id")."' ";
								$i++;
							}
							$clause.=")";
							$db4->query ("SELECT DISTINCT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms = 'dozent' $clause AND (username LIKE '%$search_exp_doz%' OR Vorname LIKE '%$search_exp_doz%' OR Nachname LIKE '%$search_exp_doz%') ORDER BY Nachname");
						} else
							$db4->query ("SELECT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id)  WHERE perms = 'dozent' AND (username LIKE '%$search_exp_doz%' OR Vorname LIKE '%$search_exp_doz%' OR Nachname LIKE '%$search_exp_doz%') ORDER BY Nachname");								
						if ($db4->num_rows()) {
							$no_doz_found=FALSE;
							printf ("<font size=-1><b>%s</b> Nutzer gefunden:<br />", $db4->num_rows());
							print "<input type=\"IMAGE\" src=\"./pictures/move_left.gif\" ".tooltip("Den Benutzer hinzufügen")." border=\"0\" name=\"add_doz\" />";
							print "&nbsp; <select name=\"add_doz\">";
							while ($db4->next_record()) {
								printf ("<option value=\"%s\">%s </option>", $db4->f("username"), htmlReady(my_substr($db4->f("fullname") ." (" . $db4->f("username"). ")",  0, 30)));
							}
							print "</select></font>";
							print "<input type=\"IMAGE\" src=\"./pictures/rewind.gif\" ".tooltip("Neue Suche starten")." border=\"0\" name=\"reset_search\" />";									
						}
					}
					if ($no_doz_found) {
						?>
						<font size=-1>
						<? printf ("%s %s", (($search_exp_doz) && ($no_doz_found)) ? "Keinen Nutzer gefunden. <a name=\"anker\"></a>" : "",   (!$search_exp_doz) ? (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? "DozentIn hinzuf&uuml;gen" : "LeiterIn hinzuf&uuml;gen"  : "");?>
						</font><br />
						<input type="TEXT" size="30" maxlength="255" name="search_exp_doz" />&nbsp; 
						<input type="IMAGE" src="./pictures/suchen.gif" <? echo tooltip("Suche starten") ?> border="0" name="search_doz" /><br />
						<font size=-1>Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.</font>
						<?
					}
					?>
				</td>			
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>
					<hr width="99%" align="right">
				<td>
			</tr>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" align="right"><? if (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) echo "TutorInnen"; else echo "Mitglieder";?></td>
				<td class="<? echo $cssSw->getClass() ?>" align="left">
					<?
					$db4->query("SELECT ". $_fullname_sql['full_rev'] ." AS fullname,seminar_user.user_id,status,username FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id) WHERE Seminar_id = '$s_id' AND Status = 'tutor' ORDER BY Nachname");
					if ($db4->nf()) {
						while ($db4->next_record()) {
							printf ("&nbsp; <a href=\"%s?delete_tut=%s&s_id=%s#anker\"><img src=\"./pictures/trash.gif\" border=\"0\"></a>&nbsp; <font size=\"-1\"><b>%s (%s)&nbsp; &nbsp; <br />", $PHP_SELF, $db4->f("username"), $s_id, htmlReady($db4->f("fullname")), $db4->f("username"));
						}
					} else {
						printf ("<font size=\"-1\">&nbsp;  %s gew&auml;hlt.</font><br >", ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? "Kein Mitarbeiter" : "Keine TutorIn");
					}
					?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" align="left" valign="top">
					<?
					$no_tut_found=TRUE;	
					if (($search_exp_tut) && ($search_tut_x)) {
						if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["only_inst_user"]) {
							$query3 = sprintf("SELECT institut_id FROM seminar_inst WHERE seminar_id = '%s'", $s_id);
							$db3->query($query3);
							$clause="AND Institut_id IN (";
							$i=0;
							while ($db3->next_record()) {
								if ($i)
									$clause.=", ";
								$clause.="'".$db3->f("institut_id")."'";
								$i++;
							}
							$clause.=")";
							$db4->query ("SELECT DISTINCT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms IN ('tutor', 'dozent') $clause AND (username LIKE '%$search_exp_tut%' OR Vorname LIKE '%$search_exp_tut%' OR Nachname LIKE '%$search_exp_tut%') ORDER BY Nachname");
						} else
							$db4->query ("SELECT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE perms IN ('tutor', 'dozent') AND (username LIKE '%$search_exp_tut%' OR Vorname LIKE '%$search_exp_tut%' OR Nachname LIKE '%$search_exp_tut%') ORDER BY Nachname");
						if ($db4->num_rows()) {
							$no_tut_found=FALSE;
							printf ("<font size=-1><b>%s</b> Nutzer gefunden:<br />", $db4->num_rows());
							print "<input type=\"IMAGE\" src=\"./pictures/move_left.gif\" ".tooltip("Den Benutzer hinzufügen")." border=\"0\" name=\"add_tut\" />";
							print "&nbsp; <select name=\"add_tut\">";
							while ($db4->next_record()) {
								printf ("<option value=\"%s\">%s </option>", $db4->f("username"), htmlReady(my_substr($db4->f("fullname")." (".$db4->f("username").")", 0, 30)));
							}
							print "</select></font>";
							print "<input type=\"IMAGE\" src=\"./pictures/rewind.gif\" ".tooltip("neue Suche starten")." border=\"0\" name=\"reset_search\" />";									
						}
					}
					if ($no_tut_found) {
						?>
						<font size=-1>
						<? printf ("%s %s", (($search_exp_tut) && ($no_tut_found)) ? "Keinen Nutzer gefunden.<a name=\"anker\"></a>" : "",   (!$search_exp_tut) ? (!$SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["workgroup_mode"]) ? "TutorIn hinzuf&uuml;gen" : "Mitglied hinzuf&uuml;gen"  : "");?>
						</font><br />
						<input type="TEXT" size="30" maxlength="255" name="search_exp_tut" />&nbsp; 
						<input type="IMAGE" src="./pictures/suchen.gif" <? echo tooltip("Suche starten") ?> border="0" name="search_tut" /><br />
						<font size=-1>Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.</font>
						<?
					}
					?>
				</td>
				</td>
			<tr>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;</td>
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>
					<hr width="99%" align="right">
				<td>
				
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
				<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2>&nbsp; <br />&nbsp; 
					<?
					echo "\n<div align=\"left\">&nbsp;";
					echo $st_search->getSearchField(array('style' => 'vertical-align:middle;','size'=>30));
					echo "&nbsp;";
					echo $st_search->getSearchButton(array('style' => 'vertical-align:middle;'));
					echo "<br>&nbsp;&nbsp;<span style=\"font-size:10pt;\">" . _("Geben Sie zur Suche den Namen des Studienbereiches ein.");
					if ($st_search->num_search_result !== false){
						echo "<br><a name=\"anker\">&nbsp;&nbsp;</a><b>" . sprintf(_("Ihre Suche ergab %s Treffer."),$st_search->num_search_result) . (($st_search->num_search_result) ? _(" (Suchergebnisse werden blau angezeigt)") : "") . "</b>";
					}
					echo "</span><br>&nbsp;";
					echo $st_search->getChooserField(array('size' => 12, 'onChange' => 'checkbereich()'),70);
					echo "</div>";
				?>
				</td>
			<?
			}
			?>
			<tr>   
				<td class="<? $cssSw->switchClass();  echo $cssSw->getClass() ?>" align="center" colspan=3>
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				</td>

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
					<input <? if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) echo "onClick=\"checkdata('edit'); return false;\" "; ?> type="image" <? echo makeButton ("uebernehmen", "src") ?> border=0 name="s_edit" value=" Ver&auml;ndern ">
				<input type="hidden" name="s_send" value="TRUE">
				</td>
			</tr>
		</form>
	</table>
	</td></tr>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>
	<?
}
?>
</table>
<?
page_close();
?>
</body>
</html>
<!-- $Id$ -->