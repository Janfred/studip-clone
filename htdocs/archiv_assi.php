<?
/*
archiv_Assi.php - Archivierungs-Assistent von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, data-quest <info@data-quest.de>

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
$perm->check("admin");

include "$ABSOLUTE_PATH_STUDIP/seminar_open.php"; // hier werden die sessions initialisiert
require_once("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); // Funktionen zum Loeschen von Terminen
require_once("$ABSOLUTE_PATH_STUDIP/datei.inc.php"); // Funktionen zum Loeschen von Dokumenten
require_once("$ABSOLUTE_PATH_STUDIP/archiv.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

## Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;

$sess->register("archiv_assi_data");
$cssSw=new cssClassSwitcher;	

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
<?

include ("header.php");   // hier wird der "Kopf" nachgeladen
include ("links_admin.inc.php");

//Handlings....

//Kill current list and stuff
if ($new_session)
	$archiv_assi_data='';

//A list was sent
if (is_array($archiv_sem)) {
	unset($archiv_assi_data["sems"]);
	unset($archiv_assi_data["sem_check"]);
	$archiv_assi_data["pos"]=0;
	foreach($archiv_sem as $key=>$val) {
		if ((substr($val, 0, 4) == "_id_") && (substr($$archiv_sem[$key+1], 0, 4) != "_id_"))
			if ($archiv_sem[$key+1]=="on") {
				$archiv_assi_data["sems"][]=array("id"=>substr($val, 4, strlen($val)), "succesful_archived"=>FALSE);
				$archiv_assi_data["sem_check"][substr($val, 4, strlen($val))]=TRUE;
			}
	}
}

//inc if we have lectures left in the upper
if ($inc)
	if ($archiv_assi_data["pos"] < sizeof($archiv_assi_data["sems"])-1) {
		$i=1;
		while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$i]["id"]]) && ($archiv_assi_data["pos"]+$i <sizeof($archiv_assi_data["sems"])-1)) 
			$i++;
		if ((sizeof($archiv_assi_data["sem_check"]) >1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$i]["id"]]))
			$archiv_assi_data["pos"]=$archiv_assi_data["pos"]+$i;
	}

//inc if we have lectures left in the lower	
if ($dec)
	if ($archiv_assi_data["pos"] >0) {
		$d=-1;
		while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$d]["id"]]) && ($archiv_assi_data["pos"]+$d > 0)) 
			$d--;
		if ((sizeof($archiv_assi_data["sem_check"]) >1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$d]["id"]]))
			$archiv_assi_data["pos"]=$archiv_assi_data["pos"]+$d;
	}

//Delete (and archive) the lecture
if ($kill) {
   $run = TRUE;
   $s_id=$archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"];
    ## Do we have permission to do so?

   //Admin sollte man schon sein
   if (!$perm->have_perm("admin")) {
    	$msg .= "error§Sie haben keine Berechtigung Veranstaltungen zu archivieren.§";
    	$run = FALSE;
    	}

   //Trotzdem nochmal nachsehen
    if (!$perm->have_perm("root")) {
	$db2->query("SELECT inst_perms FROM seminare LEFT JOIN user_inst USING(Institut_id) where Seminar_id = '$s_id' AND user_id = '$user->id'");
		if (!$db2->next_record() || $db2->f("inst_perms") != "admin") {
		      $msg .= "error§Sie haben keine Berechtigung diese Veranstaltung zu archivieren.§";
		      $run = FALSE;
		}
	}
	
	//Soll die Veranstaltung in weiteren (kommenden Semestern auftauchen?
	$db2->query ("SELECT start_time, duration_time, Name FROM seminare WHERE Seminar_id = '$s_id'");
	$db2->next_record();
	$tmp_name = $db2->f("Name");
	if ($db2->f("duration_time") == -1) {
		      $msg .= "error§Das Archivieren der Veranstaltung ist nicht m&ouml;glich, da diese Veranstaltung eine dauerhafte Veranstaltung ist. <br>Wenn Sie sie wirklich archivieren wollen, dann &auml;ndern Sie bitte die Semesterzurordnung &uuml;ber den Menupunkt <a href=\"admin_metadates.php?seminar_id=$s_id\"><b>Zeiten</b></a>.§";
		      $run = FALSE;
		}
	elseif (time() < ($db2->f("start_time") + $db2->f("duration_time"))) {
		      $msg .= "error§Das Archivieren der Veranstaltung ist nicht m&ouml;glich, da diese Veranstaltung &uuml;ber mehrere Semester l&auml;uft und noch nicht abgeschlossen ist. <br>Wenn sie Sie wirklich archivieren wollen, dann &auml;ndern Sie bitte die Semesterzurordnung &uuml;ber den Menupunkt <a href=\"admin_metadates.php?seminar_id=$s_id\"><b>Zeiten</b></a>.§";
		      $run = FALSE;
		}

	if ($run) {
    ## Bevor es wirklich weg ist. kommt das Seminar doch noch schnell ins Archiv
    in_archiv($s_id);
    
    ## Delete that Seminar.
		## Alle Benutzer aus dem Seminar rauswerfen.
    $query = "DELETE from seminar_user where Seminar_id='$s_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $liste .= "<li>$db_ar Veranstaltungsteilnehmer, Dozenten oder Tutoren archiviert.</li>";
    }
		## Alle Benutzer aus Wartelisten rauswerfen
    $query = "DELETE from admission_seminar_user where seminar_id='$s_id'";
    $db->query($query);

		## Alle beteiligten Institute rauswerfen
	  $query = "DELETE FROM seminar_inst where Seminar_id='$s_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $liste .= "<li>$db_ar Zuordnungen zu Einrichtungen archiviert.</li>";
    }
		## Alle Eintraege in der seminar_bereich rauswerfen
	  $query = "DELETE FROM seminar_bereich where Seminar_id='$s_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $liste .= "<li>$db_ar Zuordnungen zu Bereichen archiviert.</li>";
    }
		## Alle Termine mit allem was dranhaengt zu diesem Seminar loeschen.
    if (($db_ar = delete_range_of_dates($s_id, TRUE)) > 0) {
      $liste .= "<li>$db_ar Veranstaltungstermine archiviert.</li>";
    }
		## Alle weiteren Postings zu diesem Seminar loeschen.
    $query = "DELETE from px_topics where Seminar_id='$s_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $liste .= "<li>$db_ar Postings archiviert.</li>";
    }
		## Alle Dokumente im allgemeinen Ordner zu diesem Seminar loeschen.
    if (($db_ar = recursiv_folder_delete($s_id)) > 0) {
      $liste .= "<li>$db_ar Dokumente und Ordner archiviert.</li>";
    }
		## Literatur zu diesem Seminar löschen
	  $query = "DELETE FROM literatur where range_id='$s_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $liste .= "<li>Literatur und Links der Veranstaltung archiviert.</li>";
    }
		## Alle News-Verweise auf dieses Seminar löschen
	  $query = "DELETE FROM news_range where range_id='$s_id'";
    $db->query($query);
    if (($db_ar = $db->affected_rows()) > 0) {
      $tmp_news_deleted=$db_ar;
    }
		## Die News durchsehen, ob es da jetzt verweiste Einträge gibt...
	  $query = "SELECT news.news_id FROM news LEFT OUTER JOIN news_range USING (news_id) where range_id IS NULL";
    $db->query($query);
		While ($db->next_record()) {			  // Diese News hängen an nix mehr...
			$tempNews_id = $db->f("news_id");
		   $query = "DELETE FROM news where news_id = '$tempNews_id'";
	    $db2->query($query);
		}
    if (($db_ar = $db->num_rows()) > 0) {
      $tmp_news_deleted=$tmp_news_deleted+$db_ar;
    }
    if ($tmp_news_deleted)
          $liste .= "<li>$tmp_news_deleted News gel&ouml;scht.</li>";

    if ($liste)
	    $msg .= "info§<font size=-1>$liste</font>§";
    
		## und das Seminar loeschen.
    $query = "DELETE FROM seminare where Seminar_id= '$s_id'";
    $db->query($query);
    if ($db->affected_rows() == 0) {
      $msg .= "error§<b>Fehler beim L&ouml;schen der Veranstaltung §";
      break;
    }
    
    //Successful archived, if we are here
    $msg .= "msg§Die Veranstaltung <b>".htmlReady(stripslashes($tmp_name))."</b> wurde erfolgreich archiviert und aus den aktiven Veranstaltungen gel&ouml;scht. Sie steht nun im Archiv zur Verf&uuml;gung§";

    //unset the checker, lecture is now killed!
    unset($archiv_assi_data["sem_check"][$s_id]);
    
    //if there are lectures left....
    if (is_array($archiv_assi_data["sem_check"]))
   	if ($archiv_assi_data["pos"] < sizeof($archiv_assi_data["sems"])-1) { //...inc the counter if possible..
		$i=1;
		while ((! $archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$i]["id"]]) && ($archiv_assi_data["pos"]+$i <sizeof($archiv_assi_data["sems"])-1))
			$i++;
		$archiv_assi_data["pos"]=$archiv_assi_data["pos"]+$i;
		
   } else { //...else dec the counter to find a unarchived lecture
	if ($archiv_assi_data["pos"] >0) 
		$d=-1;
		while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$d]["id"]]) && ($archiv_assi_data["pos"]+$d > 0))
			$d--;
		$archiv_assi_data["pos"]=$archiv_assi_data["pos"]+$d;
   }
  }
}


//Outputs...
if (($archiv_assi_data["sems"]) && (sizeof($archiv_assi_data["sem_check"])>0)){
	$msg.="info§<font color=\"red\">Sie sind im Begriff, die untenstehende  Veranstaltung zu archivieren. Dieser Schritt kann nicht r&uuml;ckg&auml;ngig gemacht werden!";
?>
<body>

	<?
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '".$archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]."' ");
	$db->next_record();
	?>

<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><b>&nbsp;
		<?
		echo $SEM_TYPE[$db->f("status")]["name"], ": ", htmlReady(substr($db->f("Name"), 0, 60));
		if (strlen($db->f("Name")) > 60)
			echo "... ";
		echo " -  Archivieren der Veranstaltung";
		?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2><b>&nbsp;
		<table align="center" width="99%" border=0 cellpadding=2 cellspacing=0>
			<?
			parse_msg($msg, "§", "blank", 3);
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" colspan=3 valign="top" width="96%">
				<?
				//Grunddaten des Seminars
				printf ("<font size=-1><b>Titel:</b></font><br /><font size=-1>%s</font>",htmlReady($db->f("Name")));
				?>
				</td>
			</tr>
			<? if ($db->f("Untertitel") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" colspan=2 valign="top" width="96%">
				<?
				//Grunddaten des Seminars
				printf ("<font size=-1><b>Untertitel:</b></font><br /><font size=-1>%s</font>",htmlReady($db->f("Untertitel")));
				?>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>Zeit:</b></font><br /><font size=-1>%s</font>",view_turnus($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"], FALSE));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>Semester:</b></font><br /><font size=-1>%s</font>",get_semester($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]));
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>Erster Termin:</b></font><br /><font size=-1>%s</font>",veranstaltung_beginn($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="48%">
				<?
				printf ("<font size=-1><b>Vorbesprechung:</b></font><br /><font size=-1>%s</font>", (vorbesprechung($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"])) ? vorbesprechung($archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]) : "keine");
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				printf ("<font size=-1><b>Veranstaltungsort:</b></font><br /><font size=-1>%s</font>",($db->f("Ort")) ? $db->f("Ort") : "nicht angegeben");
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				if ($db->f("VeranstaltungsNummer"))
					printf ("<font size=-1><b>Veranstaltungsnummer:</b></font><br /><font size=-1>%s</font>",$db->f("VeranstaltungsNummer"));
				else
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?		
				//wer macht den Dozenten?
				$db2->query ("SELECT Vorname, Nachname, seminar_user.user_id, username, status FROM auth_user_md5 LEFT JOIN seminar_user USING (user_id) WHERE seminar_user.Seminar_id = '".$archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]."' AND status = 'dozent' ORDER BY Nachname");
				if ($db2->num_rows() > 1)
					printf ("<font size=-1><b>DozentInnen:</b></font><br />");
				else
					printf ("<font size=-1><b>DozentIn:</b></font><br />");
				while ($db2->next_record()) {
					if ($db2->num_rows() > 1)
						print "<li>";
					printf( "<font size=-1><a href = about.php?username=%s>%s</a></font>",$db2->f("username"), htmlReady($db2->f("Vorname"))." ".htmlReady($db2->f("Nachname")) );
					if ($db2->num_rows() > 1)
						print "</li>";
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?		
				//und wer ist Tutor?
				$db2->query ("SELECT seminar_user.user_id, Vorname, Nachname, username, status FROM auth_user_md5 LEFT JOIN seminar_user USING (user_id) WHERE seminar_user.Seminar_id = '".$archiv_assi_data["sems"][$archiv_assi_data["pos"]]["id"]."' AND status = 'tutor' ORDER BY Nachname");
				if ($db2->num_rows() > 1)
					printf ("<font size=-1><b>TutorInnen:</b></font><br />");
				elseif ($db2->num_rows() == 0)
					printf ("<font size=-1><b>TutorIn:</b></font><br /><font size=-1>keine</font>");
				else
					printf ("<font size=-1><b>TutorIn:</b></font><br />");
				while ($db2->next_record()) {
					if ($db2->num_rows() > 1)
						print "<li>";
					printf( "<font size=-1><a href = about.php?username=%s>%s</a></font>",$db2->f("username"), htmlReady($db2->f("Vorname"))." ".htmlReady($db2->f("Nachname")) );
					if ($db2->num_rows() > 1)
						print "</li>";
				}
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				printf ("<font size=-1><b>Veranstaltungstyp:</b></font><br /><font size=-1>%s in der Kategorie %s</font>",$SEM_TYPE[$db->f("status")]["name"], $SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["name"]);
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				if ($db->f("art"))
					printf ("<font size=-1><b>Art/Form:</b></font><br /><font size=-1>%s</font>",$db->f("art"));
				else
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<? if ($db->f("Beschreibung") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="96%" valign="top">
				<?
				printf ("<font size=-1><b>Kommentar/Beschreibung:</b></font><br /><font size=-1>%s</font>",htmlReady($db->f("Beschreibung"), TRUE, TRUE));
				?>
				</td>
			</tr>	
			<?
			}
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="96%" valign="top" align="center">
					<?
					//can we inc?
					if ($archiv_assi_data["pos"] >0) {
						$d=-1;
						while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$d]["id"]]) && ($archiv_assi_data["pos"]+$d > 0)) 
							$d--;
						if ((sizeof($archiv_assi_data["sem_check"]) >1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$d]["id"]]))
							$inc_possible=TRUE;
					}
					if ($inc_possible)
						{ ?>&nbsp;<a href="<? echo $PHP_SELF ?>?dec=TRUE"><img src="./pictures/buttons/vorherige-button.gif" border=0></a> <? } ?>
					&nbsp;<a href="<? echo $PHP_SELF ?>?list=TRUE&new_session=TRUE"><img src="./pictures/buttons/abbrechen-button.gif" border=0></a>
					&nbsp;<a href="<? echo $PHP_SELF ?>?kill=TRUE"><img src="./pictures/buttons/archivieren-button.gif" border=0></a>
					<?
					//can we dec?
					if ($archiv_assi_data["pos"] < sizeof($archiv_assi_data["sems"])-1) {
						$i=1;
						while ((!$archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$i]["id"]]) && ($archiv_assi_data["pos"]+$i <sizeof($archiv_assi_data["sems"])-1)) 
							$i++;
						if ((sizeof($archiv_assi_data["sem_check"]) >1) && ($archiv_assi_data["sem_check"][$archiv_assi_data["sems"][$archiv_assi_data["pos"]+$i]["id"]]))
							$dec_possible=TRUE;
					}
					if ($dec_possible)
						{ ?>&nbsp;<a href="<? echo $PHP_SELF ?>?inc=TRUE"><img src="./pictures/buttons/naechster-button.gif" border=0></a><? } ?>
				</td>
			</tr>
		</table>
	</td>
	</tr>
	</table>
	<?
}
elseif (($archiv_assi_data["sems"]) && (sizeof($archiv_assi_data["sem_check"])==0)) {
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><b>Keine Veranstaltung zum Archivieren gew&auml;hlt</b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2><b>&nbsp;
		<?
		parse_msg($msg."info§Sie haben alle ausgew&auml;hlten Veranstaltungen archiviert!");
		?>
		</td>
	</tr>
	</table>
	<?
	}
elseif (!$list) {
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2><b>Keine Veranstaltung zum Archivieren gew&auml;hlt</b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2><b>&nbsp;
		<?
		parse_msg("info§Sie haben keine Veranstaltung zum Archivieren gew&auml;hlt.");
		?>
		</td>
	</tr>
	</table>
	<?
	}
	page_close();
?>
</body>
</html>