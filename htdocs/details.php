<?php
/*
details.php - Detail-Uebersicht und Statistik fuer ein Seminar
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	
require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php"); //Funktionen zum Anzeigen der Terminstruktur
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php"); // wir brauchen htmlReady
require_once ("$ABSOLUTE_PATH_STUDIP/admission.inc.php"); 
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemTree.class.php");

?>
<body>
<?

//Inits
$cssSw=new cssClassSwitcher;
$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;


//wenn kein Seminar gesetzt und auch kein externer Aufruf raus....
if (($SessSemName[1] =="") && (!isset($sem_id))) {
	parse_window ("error§" . _("Sie haben kein Objekt gew&auml;hlt.") . " <br><br><font size=-1 color=black>"
				. _("Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt (Veranstaltung oder Einrichtung) gew&auml;hlt haben.") . "<br /><br /> "
				. sprintf(_("Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als %s Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen."), $AUTH_LIFETIME) . "</font>", "§",
				_("Kein Objekt gew&auml;hlt"),
				sprintf(_("%sHier%s geht es wieder zur Anmeldungs- bzw. Startseite."), "<a href=\"index.php\"><b>&nbsp;", "</b></a>") . "<br />&nbsp;");
	die;
}
	
//wenn Seminar gesetzt und kein externer Aufruf uebernahme der SessionVariable
if (($SessSemName[1] <>"") && (!isset($sem_id))) {
	include "links_openobject.inc.php";

	$sem_id=$SessSemName[1];
	
}

// nachfragen, ob das Seminar abonniert werden soll
if ($sem_id) {
	if ($perm->have_studip_perm("admin",$sem_id)) {
		$abo_msg=_("direkt zur Veranstaltung");
		$skip_verify=TRUE;
	} elseif ($perm->have_perm("user") && !$perm->have_perm("admin")) { //Add lecture only if logged in	
		$db->query("SELECT status FROM seminar_user WHERE user_id ='$user->id' AND Seminar_id = '$sem_id'");
		$db->next_record();
		if (!$db->num_rows()) {
			$db->query("SELECT status FROM admission_seminar_user WHERE user_id ='$user->id' AND seminar_id = '$sem_id'");
			if (!$db->num_rows())
				$abo_msg=_("Tragen Sie sich hier ein");
		} else {
			if ($db->f("status") == "user") {
				$abo_msg=_("Schreibrechte aktivieren");
			}
		}
	}
}

if ($send_from_search)
    	$back_msg.=_("Zur&uuml;ck zur letzten Auswahl");

//Namen holen
$db2->query("SELECT * FROM seminare WHERE Seminar_id = '$sem_id'");
$db2->next_record();
	
//In dieser Datei nehmen wir die Art direkt, nicht aus Session, da die Datei auch ausserhalb von Seminaren aufgerufen wird
if ($SEM_TYPE[$db2->f("status")]["name"] == $SEM_TYPE_MISC_NAME) //Typ fuer Sonstiges
	$art = _("Veranstaltung"); 
else
	$art = $SEM_TYPE[$db2->f("status")]["name"];

	
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr><td class="topic" colspan=2><b>&nbsp;<? echo getHeaderLine($sem_id)." - " . _("Details"); ?>
	</b></td></tr>
	<?

	if ($msg)
		{
		echo "<tr><td class=\"blank\" colspan=2>&nbsp;</td></tr>";
		parse_msg($msg);
		}
	?>
	<tr><td class="blank">
		&nbsp; <br />
		<table align="center" width="99%" border="0" cellpadding="2" cellspacing="0">
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; <img src="./pictures/blank.gif" width="25" height="10" border="0">
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" colspan=2 valign="top" width="70%">
				<?
				//Titel und Untertitel der Veranstaltung
				printf ("<b>%s</b><br /> ",htmlReady($db2->f("Name")));
				printf ("<font size=-1>%s</font>",htmlReady($db2->f("Untertitel")));
				?>
				</td>
				<td  class="steel1" width="26%" rowspan=7  valign="top">
				
				<? // Infobox 
				

							$user_id = $auth->auth["uid"];
							$db3->query("SELECT status FROM seminar_user WHERE Seminar_id = '$sem_id' AND user_id = '$user_id'");
							if ($db3->next_record() ){
							$mein_status = $db3->f("status");
							} else {
								unset ($mein_status);
							}
							//Status als Wartender ermitteln
							$db3->query("SELECT status FROM admission_seminar_user WHERE seminar_id = '$sem_id' AND user_id = '$user_id'");
							if ($db3->next_record() ){
							$admission_status = $db3->f("status");
							} else {
								unset ($admission_status);
							}

							if (($mein_status) || ($admission_status)) {
								$picture_tmp = "./pictures/haken.gif";
							} else {
								$picture_tmp = "./pictures/x2.gif";
							}
							
							if (($mein_status) || ($admission_status)) {
								if ($mein_status) {
									$tmp_text=_("Sie sind als TeilnehmerIn der Veranstaltung eingetragen");
								} elseif ($admission_status) {
									$tmp_text=sprintf (_("Sie sind in die %s der Veranstaltung eingetragen"), ($admission_status=="claiming")  ? _("Anmeldeliste") : _("Warteliste"));
								} 
							} elseif (!$perm->have_perm("admin")) {
								$tmp_text=_("Sie sind nicht als TeilnehmerIn der Veranstaltung eingetragen.");
							} else {
								$tmp_text=_("Sie sind AdministratorIn und k&ouml;nnen deshalb die Veranstaltung nicht abonnieren.");
							}
							if ((!$mein_status) && (!$admission_status)) {
								$tmp_text = "<font color = red>".$tmp_text."<font>";
							}

	$infobox = array	(			
		array  ("kategorie"  => _("Pers&ouml;nlicher Status:"),
			"eintrag" => array	(	
				array (	"icon" => $picture_tmp,
								"text"  => $tmp_text
				)
			)
		),
		array  ("kategorie" => _("Berechtigungen:"),
	  	"eintrag" => array	(	
				array	 (	"icon" => "pictures/blank.gif",
									"text"  => _("Lesen:") . "&nbsp; ".get_ampel_read($mein_status, $admission_status, $db2->f("Lesezugriff"),FALSE)
				),
				array	 (	"icon" => "pictures/blank.gif",
									"text"  => _("Schreiben:") . "&nbsp; ".get_ampel_write($mein_status, $admission_status, $db2->f("Schreibzugriff"),FALSE)
				)
			)
		)
	);


if ($abo_msg || $back_msg) {
	$infobox[2]["kategorie"] = _("Aktionen:");
	if (($abo_msg) && (!$skip_verify)) {
		$infobox[2]["eintrag"][] = array (	"icon" => "./pictures/meinesem.gif" ,
									"text"  => "<a href=\"sem_verify.php?id=".$sem_id."&send_from_search=$send_from_search&send_from_search_page=$send_from_search_page\">".$abo_msg. "</a>"
								);
	} elseif ($abo_msg) {
		$infobox[2]["eintrag"][] = array (	"icon" => "./pictures/meinesem.gif" ,
									"text"  => "<a href=\"seminar_main.php?auswahl=".$sem_id."\">".$abo_msg. "</a>"
								);
	}	
	if ($back_msg) {
		$infobox[2]["eintrag"][] = array (	"icon" => "./pictures/suchen.gif" ,
									"text"  => "<a href=\"$send_from_search_page\">".$back_msg. "</a>"
								);
	}
}

if ($db2->f("admission_binding")) {
	$infobox[count($infobox)]["kategorie"] = _("Information:");
	$infobox[count($infobox)-1]["eintrag"][] = array (	"icon" => "./pictures/info.gif" ,
								"text"  => _("Das Abonnement dieser Veranstaltung ist <u>bindend</u>!")
							);
}


// print the info_box

print_infobox ($infobox,"pictures/details.jpg");
			
// ende Infobox

?>
				
				
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="45%">
				<?
				printf ("<font size=-1><b>" . _("Zeit:") . "</b></font><br /><font size=-1>%s</font>",htmlReady(view_turnus($sem_id, FALSE)));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="25%">
				<?
				printf ("<font size=-1><b>" . _("Semester:") . "</b></font><br /><font size=-1>%s</font>",get_semester($sem_id));
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="45%">
				<?
				printf ("<font size=-1><b>" . _("Erster Termin:") . "</b></font><br /><font size=-1>%s</font>",veranstaltung_beginn($sem_id));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" valign="top" width="25%">
				<?
				printf ("<font size=-1><b>" . _("Vorbesprechung:") . "</b></font><br /><font size=-1>%s</font>", (vorbesprechung($sem_id)) ? vorbesprechung($sem_id) : "keine");
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="45%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Veranstaltungsort:") . "</b></font><br /><font size=-1>%s</font>", (getRoom ($sem_id)) ? getRoom ($sem_id) : "nicht angegeben.");
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="25%"  align="top">
				<?
				if ($db2->f("VeranstaltungsNummer"))
					printf ("<font size=-1><b>" . _("Veranstaltungsnummer:") . "</b></font><br /><font size=-1>%s</font>",$db2->f("VeranstaltungsNummer"));
				else	
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="45%" valign="top">
				<?		
			//wer macht den Dozenten?
				$db->query ("SELECT " . $_fullname_sql['full'] . " AS fullname, seminar_user.user_id, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'dozent' ORDER BY Nachname");
				if ($db->num_rows() > 1)
					printf ("<font size=-1><b>%s:</b></font><br />", ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) ? _("LeiterInnen") : _("DozentInnen"));
				elseif ($db->num_rows() == 1)
					printf ("<font size=-1><b>%s:</b></font><br />", ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) ? _("LeiterIn") : _("DozentIn"));
				else	
					print "&nbsp; ";
				while ($db->next_record()) {
					if ($db->num_rows() > 1)
						print "<li>";
					printf( "<font size=-1><a href = about.php?username=%s>%s</a></font>",$db->f("username"), htmlReady($db->f("fullname")) );
					if ($db->num_rows() > 1)
						print "</li>";
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>"width="61%" colspan=1 valign="top">
				<?		
				//und wer ist Tutor?
				$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'tutor' ORDER BY Nachname");
				if ($db->num_rows() > 1)
					printf ("<font size=-1><b>%s:</b></font><br />", ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) ? _("Mitglieder") : _("TutorInnen"));
				elseif ($db->num_rows() == 1)
					printf ("<font size=-1><b>%s:</b></font><br />", ($SEM_CLASS[$SEM_TYPE[$SessSemName["art_num"]]["class"]]["workgroup_mode"]) ? _("Mitglied") : _("TutorIn"));
				else	
					print "&nbsp; ";
				while ($db->next_record()) {
					if ($db->num_rows() > 1)
						print "<li>";
					printf( "<font size=-1><a href = about.php?username=%s>%s</a></font>",$db->f("username"), htmlReady($db->f("fullname")) );
					if ($db->num_rows() > 1)
						print "</li>";
				}
				?>
				</td>
			</tr>
		</table>
		<table align="center" width="99%" border=0 cellpadding=2 cellspacing=0>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; <img src="./pictures/blank.gif" width="25" height="10" border="0">
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="51%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Veranstaltungstyp:") . "</b></font><br /><font size=-1>%s in der Kategorie %s</font>",$SEM_TYPE[$db2->f("status")]["name"], $SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["name"]);
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<?
				if ($db2->f("art"))
					printf ("<font size=-1><b>" . _("Art/Form:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("art")));
				else	
					print "&nbsp; ";
				?>
				</td>
			</tr>
			<? if ($db2->f("Beschreibung") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Kommentar/Beschreibung:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("Beschreibung"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("teilnehmer") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Teilnehmende:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("teilnehmer"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("vorrausetzungen") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Voraussetzungen:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("vorrausetzungen"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("lernorga") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Lernorganisation:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("lernorga"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("leistungsnachweis") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Leistungsnachweis:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("leistungsnachweis"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("Sonstiges") !="") {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("Sonstiges:") . "</b></font><br /><font size=-1>%s</font>",FixLinks(htmlReady($db2->f("Sonstiges"), TRUE, TRUE)));
				?>
				</td>
			</tr>
			<? }
			if ($db2->f("ects")) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				printf ("<font size=-1><b>" . _("ECTS-Kreditpunkte:") . "</b></font><br /><font size=-1>%s</font>",htmlReady($db2->f("ects"), TRUE, TRUE));
				?>
				</td>
			</tr>
			<? }
			// Anzeige der Bereiche  
			if ($SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["bereiche"]) {
				$sem_path = get_sem_tree_path($sem_id);
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=4 width="99%" valign="top">
				<?
				if (is_array($sem_path)){
					if (count($sem_path) ==1)
					printf ("<font size=-1><b>" . _("Studienbereich:") . "</b></font><br />");
					else
					printf ("<font size=-1><b>" . _("Studienbereiche:") . "</b></font><br />");
					foreach ($sem_path as $sem_tree_id => $path_name) {
						if (count($sem_path) >= 2)
						print "<li>";
						printf ("<font size=-1><a href=\"show_bereich.php?level=sbb&id=%s\">%s</a></font>",$sem_tree_id,
						htmlReady($path_name));
						if (count($sem_path) >= 2)
						print "</li>";
					}
				}
				?>&nbsp; 
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="51%" valign="top">
				<?
				$db3->query("SELECT Name, url, Institut_id FROM Institute WHERE Institut_id = '".$db2->f("Institut_id")."' ");
				$db3->next_record();
				if ($db3->num_rows()) {
				printf("<font size=-1><b>" . _("Heimat-Einrichtung:") . "</b></font><br /><font size=-1><a href=\"institut_main.php?auswahl=%s\">%s</a></font>", $db3->f("Institut_id"), htmlReady($db3->f("Name")));
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
				<?
				$db3->query("SELECT Name, url, Institute.Institut_id FROM Institute LEFT JOIN seminar_inst USING (institut_id) WHERE seminar_id = '$sem_id' AND Institute.institut_id != '".$db2->f("Institut_id")."'");
				if ($db3->num_rows() ==1)
					printf ("<font size=-1><b>" . _("beteiligte Einrichtung:") . "</b></font><br />");
				elseif ($db3->num_rows() >=2)
					printf ("<font size=-1><b>" . _("beteiligte Einrichtungen:") . "</b></font><br />");
				else	
					print "&nbsp; ";
				while ($db3->next_record()) {
					if ($db3->num_rows() >= 2)
						print "<li>";
					printf("<font size=-1><a href=\"institut_main.php?auswahl=%s\">%s</a></font><br />", $db3->f("Institut_id"), htmlReady($db3->f("Name")));
					if ($db3->num_rows() > 2)
						print "</li>";
				}				
				?>
				</td>
			</tr>
			<?
			if ($db2->f("admission_type")) {
			?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="51%" valign="top">
				<font size=-1><b><?=_("Anmeldeverfahren:")?></b></font><br />				
				<?
				if ($db2->f("admission_selection_take_place") == 1) {
					if ($db2->f("admission_type") == 1)
						printf ("<font size=-1>" . _("Die Auswahl der Teilnehmenden wurde nach dem Losverfahren am %s Uhr festgelegt. Weitere Interessierte k&ouml;nnen per Warteliste einen Platz bekommen.") . "</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
					else
						printf ("<font size=-1>" . _("Die Auswahl der Teilnehmenden erfolgt in der Reihenfolge der Anmeldung. Die Kontingentierung wurde am %s aufgehoben. Weitere Pl&auml;tze k&ouml;nnen noch &uuml;ber Wartelisten vergeben werden.") . "</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
				} else {
					if ($db2->f("admission_type") == 1)
						printf ("<font size=-1>" . _("Die Auswahl der Teilnehmenden erfolgt nach dem Losverfahren am %s Uhr.") . "</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
					else
						printf ("<font size=-1>" . _("Die Auswahl der Teilnehmenden erfolgt in der Reihenfolge der Anmeldung. Die Kontingentierung wird am %s aufgehoben.") . "</font>", date("d.m.Y, G:i", $db2->f("admission_endtime")));
				}
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" colspan=2 width="48%" valign="top">
					<font size=-1><b><?=_("Kontingente:")?></b></font><br />
					<?
					$db3->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$sem_id' "); //Alle  moeglichen Studiengaenge anziegen
					while ($db3->next_record()) {
						if ($db3->f("studiengang_id") == "all")
							$tmp_details_quota=get_all_quota($sem_id);
						else
							$tmp_details_quota=round ($db2->f("admission_turnout") * ($db3->f("quota") / 100));
						printf ("<font size=-1>" . _("Kontingent f&uuml;r %s (%s Pl&auml;tze)") . "</font>",  ($db3->f("studiengang_id") == "all") ? _("alle Studieng&auml;nge") : $db3->f("name"), $tmp_details_quota);
						print "<br />";
					}
				       ?>
				</td>				
			</tr>
			<? } ?>
			<tr>
				<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>" width="1%">&nbsp; 
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="27%" valign="top">
				<?
				//Statistikfunktionen
				$db3->query("SELECT count(*) as anzahl FROM seminar_user WHERE Seminar_id = '$sem_id'");
				$db3->next_record();
				printf ("<font size=-1><b>" . _("Anzahl der Teilnehmenden:") . "&nbsp;</b></font><font size=-1>%s </font>", ($db3->f("anzahl")) ? $db3->f("anzahl") : _("keine"));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="24%" valign="top">
				<?
				if ($db2->f("admission_turnout"))
					printf ("<font size=-1><b>" . _("%s Teilnehmerzahl:") . "&nbsp;</b></font><font size=-1>%s </font>", ($db2->f("admission_type")) ? _("max.") : _("erw."), $db2->f("admission_turnout"));
				else
					print "&nbsp; ";
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="24%" valign="top">
				<?
				$db3->query("SELECT count(*) as anzahl FROM px_topics WHERE Seminar_id = '$sem_id'");
				$db3->next_record();
				printf ("<font size=-1><b>" . _("Postings:") . "&nbsp;</b></font><font size=-1>%s </font>", ($db3->f("anzahl")) ? $db3->f("anzahl") : _("keine"));
				?>
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="48%" valign="top">
				<?
				$db3->query("SELECT count(*) as anzahl FROM dokumente WHERE Seminar_id = '$sem_id'");
				$db3->next_record();
				printf ("<font size=-1><b>" . _("Dokumente:") . "&nbsp;</b></font><font size=-1>%s </font>", ($db3->f("anzahl")) ? $db3->f("anzahl") : _("keine"));
				?>
				</td>
				
			</tr>
		</table>
		<br />&nbsp; 
	</td>
	</tr>
	</table>
</td>
</tr>
</table>
</body>
</html>
<?php
	
// Save data back to database.
page_close();
 ?>
<!-- $Id$ -->
