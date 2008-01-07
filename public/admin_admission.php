<?
/**
* admin_admission.php
*
* edit the settings for the admission system
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		admin_admission.php
* @modulegroup	admin
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_admission.php
// Zugangsberechtigungen fuer Veranstaltungen verwalten
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

$perm->check("tutor");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

?>
	<script type="text/javascript" language="javascript" src="<?= $GLOBALS['ASSETS_URL'] ?>javascripts/md5.js"></script>

	<script type="text/javascript" language="javascript">
	<!--
	function doCrypt() {
		document.admission.hashpass.value = MD5(document.admission.password.value);
		document.admission.hashpass2.value = MD5(document.admission.password2.value);
		document.admission.password.value = "";
		document.admission.password2.value = "";
		return true;
	}

	function checkpassword(){
		var checked = true;
		if ((document.admission.password.value.length<4) && (document.admission.password.value.length != 0)) {
			alert("<?= _("Das Passwort ist zu kurz. Es sollte mindestens 4 Zeichen lang sein.") ?>");
			document.admission.password.focus();
			checked = false;
		}
		return checked;
	}

	function checkpassword2(){
	var checked = true;
	if (document.admission.password.value != document.admission.password2.value) {
		alert("<?=_("Das Passwort stimmt nicht mit dem Wiederholungspasswort überein!") ?>");
		document.admission.password2.focus();
		checked = false;
		}
		return checked;
	}
	// -->
	</script>

<?

require_once('lib/msg.inc.php');	//Ausgaben
require_once('config.inc.php');	//Settings....
require_once 'lib/functions.php';	//basale Funktionen
require_once('lib/visual.inc.php');	//Darstellungsfunktionen
require_once('lib/messaging.inc.php');	//Nachrichtenfunktionen
require_once('lib/admission.inc.php');	//load functions from admission system
require_once('lib/classes/StudipAdmissionGroup.class.php'); //htmlReady

$HELP_KEYWORD="Basis.VeranstaltungenVerwaltenZugangsberechtigungen";
	
//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Verwaltung von Zugangsberechtigungen");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();
//get ID from a open Seminar
if ($SessSemName[1])
	$header_object_id = $SessSemName[1];
else
	$header_object_id = $seminar_id;

//Change header_line if open object
$header_line = getHeaderLine($header_object_id);
if ($header_line)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;

$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$db6 = new DB_Seminar;
$cssSw = new cssClassSwitcher;
$sess->register("admin_admission_data");
$messaging = new messaging;

/**
* This function creates a snapshor for all the values the admin_admission script uses
*
* The function serializes all the data which is used on this page. So you can
* compare an old and a new state of the whole set. It is used to inform the user,
* that the data isn't saved yet.
*
* @param		string	all the data in serialized form
*
*/
function get_snapshot() {
	global $admin_admission_data;
	return	md5($admin_admission_data["admission_turnout"].
		$admin_admission_data["admission_type"].
		$admin_admission_data["admission_endtime"].
		$admin_admission_data["admission_binding"].
		$admin_admission_data["passwort"].
		$admin_admission_data["read_level"].
		$admin_admission_data["write_level"].
		serialize($admin_admission_data["studg"]).
		$admin_admission_data["all_ratio"].
		$admin_admission_data["admission_prelim"].
		$admin_admission_data["admission_prelim_txt"].
		$admin_admission_data["sem_admission_start_date"].
		$admin_admission_data["sem_admission_end_date"].
		$admin_admission_data["admission_disable_waitlist"]);
}

$errormsg = '';
if ($SessSemName[1]) $seminar_id = $SessSemName[1];

//check, if seminar is grouped
$group_obj = StudipAdmissionGroup::GetAdmissionGroupBySeminarId($seminar_id);
if (is_object($group_obj)) { //if so, do not allow to change admission_type
	$is_grouped = TRUE;
} else {
	$is_grouped = FALSE;
}
	
//wenn wir frisch reinkommen, werden benoetigte Daten eingelesen
if (($seminar_id) && (!$uebernehmen_x) &&(!$adm_null_x) &&(!$adm_los_x) &&(!$adm_chrono_x) && (!$add_studg_x) && (!$delete_studg) && (!$adm_gesperrt_x)) {
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '$seminar_id' ");
	$db->next_record();
	$admin_admission_data='';
	$admin_admission_data["metadata_dates"]=unserialize($db->f("metadata_dates"));
	$admin_admission_data["admission_turnout"]=$db->f("admission_turnout");
	$admin_admission_data["admission_turnout_org"]=$db->f("admission_turnout");
	$admin_admission_data["admission_type"]=$db->f("admission_type");
	$admin_admission_data["admission_type_org"]=$db->f("admission_type");
	$admin_admission_data["admission_selection_take_place"]=$db->f("admission_selection_take_place");
	$admin_admission_data["admission_endtime"]=$db->f("admission_endtime");
	$admin_admission_data["admission_binding"]=$db->f("admission_binding");
	$admin_admission_data["sem_id"]=$seminar_id;
	settype($admin_admission_data["admission_binding"], integer);
	$admin_admission_data["heimat_inst_id"]=$db->f("Institut_id");
	$admin_admission_data["passwort"]=$db->f("Passwort");
	$admin_admission_data["name"]=$db->f("Name");
	$admin_admission_data["status"]=$db->f("status");
	$admin_admission_data["start_time"]=$db->f("start_time");
	$admin_admission_data["read_level"]=$db->f("Lesezugriff");
	$admin_admission_data["write_level"]=$db->f("Schreibzugriff");
	$admin_admission_data["admission_prelim"]=$db->f("admission_prelim");
	$admin_admission_data["admission_prelim_txt"]=$db->f("admission_prelim_txt");
	$admin_admission_data["sem_admission_start_date"]=$db->f("admission_starttime");
	$admin_admission_data["sem_admission_end_date"]=$db->f("admission_endtime_sem");
	$admin_admission_data["admission_disable_waitlist"] = $db->f("admission_disable_waitlist");
	$admin_admission_data["admission_disable_waitlist_org"] = $db->f("admission_disable_waitlist");
	if ($admin_admission_data["admission_endtime"] <= 0){
		$admin_admission_data["admission_endtime"] = veranstaltung_beginn($seminar_id, 'int');
		if(!$admin_admission_data["admission_endtime"]) $admin_admission_data["admission_endtime"] = -1;
	}
	$db->query("SELECT admission_seminar_studiengang.studiengang_id, name, quota FROM admission_seminar_studiengang LEFT JOIN studiengaenge USING (studiengang_id)  WHERE seminar_id = '$seminar_id'");
	while ($db->next_record()) {
		if ($db->f("studiengang_id") == "all")
			$admin_admission_data["all_ratio"] = $db->f("quota");
		else
			$admin_admission_data["studg"][$db->f("studiengang_id")] = array("name"=>$db->f("name"), "ratio"=>$db->f("quota"));
	}
	$admin_admission_data["original"]=get_snapshot();
	if($reset_admission_time_x){
		$admin_admission_data["sem_admission_end_date"]=-1;
		$admin_admission_data["sem_admission_start_date"]=-1;
	}
//nur wenn wir schon Daten haben kann was zurueckkommen
} else {
	//Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.
	if (!$admin_admission_data["sem_id"]) {
		echo "</tr></td></table>";
		die;
	}

	//check start / enddate
	if (!check_and_set_date($adm_s_tag, $adm_s_monat, $adm_s_jahr, $adm_s_stunde, $adm_s_minute, $admin_admission_data, "sem_admission_start_date")) {
		$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das Startdatum ein!")."§";
	}
	if (!check_and_set_date($adm_e_tag, $adm_e_monat, $adm_e_jahr, $adm_e_stunde, $adm_e_minute, $admin_admission_data, "sem_admission_end_date")) {
		$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das Enddatum ein!")."§";
	}
	if ($admin_admission_data["sem_admission_end_date"] != "-1") {
		if ($admin_admission_data["sem_admission_end_date"] < time()) {
			$errormsg=$errormsg."error§"._("Das Enddatum liegt in der Vergangenheit. Bitte geben Sie ein g&uuml;ltiges Enddatum ein!")."§";
		}
		if ($admin_admission_data["sem_admission_end_date"] <= $admin_admission_data["sem_admission_start_date"]) {
			$errormsg=$errormsg."error§"._("Das Startdatum muss vor dem Enddatum liegen!")."§";
		}
	}

	//Umschalter zwischen den Typen
	if ($adm_null_x || $adm_los_x || $adm_chrono_x || $adm_gesperrt_x){
		if ($is_grouped){
			$errormsg = $errormsg."error§"._("Gruppierte Veranstaltungen m&uuml;ssen ein einheitliches Anmeldeverfahren haben! Bei gruppierten Veranstaltungen können Sie das Anmeldeverfahren an dieser Stelle nicht mehr ändern.")."§";
		} else {
			if($adm_null_x){
				$admin_admission_data["admission_type"]=0;
				$admin_admission_data["sem_admission_end_date"]=-1;
				$admin_admission_data["sem_admission_start_date"]=-1;
				$admin_admission_data["admission_endtime"]=-1;
				$admin_admission_data["admission_selection_take_place"] = 0;
			}
			if ($adm_los_x){
				$admin_admission_data["admission_type"]=1;
			}
			if ($adm_chrono_x){
				$admin_admission_data["admission_type"]=2;
			}
			if ($adm_gesperrt_x){
                $admin_admission_data["admission_type"] = 3;
			}
		}
	}

	//Aenderungen ubernehmen
	$admin_admission_data["admission_binding"]=$admission_binding;
	if ($admin_admission_data["admission_binding"])
		$admin_admission_data["admission_binding"]=TRUE;
	settype($admin_admission_data["admission_binding"], integer);

	if(isset($admission_turnout)) $admin_admission_data["admission_turnout"]=$admission_turnout;

	$admin_admission_data["admission_prelim_txt"]=$admission_prelim_txt;

	if(isset($_REQUEST['uebernehmen_x']) && isset($_REQUEST["admission_waitlist"])) $admin_admission_data["admission_disable_waitlist"] = (int)(!$_REQUEST["admission_waitlist"]);
	
	if (!$admin_admission_data["admission_type"]) {
		$admin_admission_data["read_level"]=$read_level;
		$admin_admission_data["write_level"]=$write_level;

	//Alles was mit der Anmeldung zu tun hat ab hier
	} elseif (!$delete_studg) {

		//Hat der User an den automatischen Werte rumgefuscht? Dann denkt er sich wohl was :) (und wir benutzen die Automatik spaeter nicht!)
		if ($all_ratio_old != $all_ratio) {
			$admin_admission_data["admission_ratios_changed"]=TRUE;
			$admin_admission_data["all_ratio"]=$all_ratio;
		}

		//Studienbereiche entgegennehmen
		if (is_array($studg_id)) {
			foreach ($studg_id as $key=>$val)
				if ($studg_ratio_old[$key] != $studg_ratio[$key])
					$admin_admission_data["admission_ratios_changed"]=TRUE;
			if ($admin_admission_data["admission_ratios_changed"]) {
				$admin_admission_data["studg"]='';
				foreach ($studg_id as $key=>$val)
					$admin_admission_data["studg"][$val]=array("name"=>$studg_name[$key], "ratio"=>$studg_ratio[$key]);
			}
		}

		//Datum fuer Ende der Anmeldung umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
		if (!$commit_no_admission_data) { //wenn Ansicht gesperrt ist (Dozentenview) hier keine Ubernahmen

			if (!check_and_set_date($adm_tag, $adm_monat, $adm_jahr, $adm_stunde, $adm_minute, $admin_admission_data, "admission_endtime")) {
				if ($admin_admission_data["admission_type"] == 1) {
					$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das Losdatum ein!")."§";
				} else {
					$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das Enddatum der Kontingentierung ein!")."§";
				}
			}
		}

	}

	//Studiengang hinzufuegen
	if ($add_studg_x) {
		if ($add_studg) {
			$db->query("SELECT name FROM studiengaenge WHERE studiengang_id='".$add_studg."' ");
			$db->next_record();
			$admin_admission_data["studg"][$add_studg]=array("name"=>$db->f("name"), "ratio"=>$add_ratio);
		}
	}

	//Studiengang loeschen
	if ($delete_studg)
		unset($admin_admission_data["studg"][$delete_studg]);

	//Checks performen
	if (!$admin_admission_data["admission_type"]) {
		if (($admin_admission_data["write_level"]) <($admin_admission_data["read_level"]))
			$errormsg=$errormsg."error§"._("Es macht keinen Sinn, die Sicherheitsstufe f&uuml;r den Lesezugriff h&ouml;her zu setzen als f&uuml;r den Schreibzugriff!")."§";

		if (($admin_admission_data["read_level"] ==2) ||  ($admin_admission_data["write_level"] ==2)) {
       			//Password bei Bedarf dann doch noch verschlusseln
			if (empty($hashpass)) { // javascript disabled
   				if (!$password)
       					$admin_admission_data["passwort"] = "";
				elseif($password != "*******") {
					$admin_admission_data["passwort"] = md5($password);
	     					if($password2 != "*******")
    							$check_pw = md5($password2);
	    			}
    			} elseif ($hashpass != md5("*******")) { // javascript enabled
				$admin_admission_data["passwort"]= $hashpass;
				$check_pw = $hashpass2;
			}

			if ($admin_admission_data["passwort"]=="")
       			  	$errormsg=$errormsg."error§"._("Sie haben kein Passwort eingegeben! Bitte geben Sie ein Passwort ein!")."§";
		      	elseif (isset($check_pw) AND $admin_admission_data["passwort"] != $check_pw) {
					$errormsg=$errormsg."error§"._("Das eingegebene Passwort und das Wiederholungspasswort stimmen nicht &uuml;berein!")."§";
     					$admin_admission_data["passwort"] = "";
			}
		}

	//Checks bei Anmeldeverfahren
	} elseif ((!$adm_chrono_x) && (!$adm_los_x) && (!$adm_gesperrt_x))  {
		//max. Teilnehmerzahl checken
		if (($uebernehmen_x) && ($admin_admission_data["admission_type"] > 0) && ($admin_admission_data["admission_type"] < 3)) {
			if ($admin_admission_data["admission_turnout"] < 1) {
				$errormsg=$errormsg."error§"._("Wenn Sie die Teilnahmebeschr&auml;nkung benutzen wollen, m&uuml;ssen Sie wenigstens einen Teilnehmer zulassen.")."§";
				$admin_admission_data["admission_turnout"] =1;
			}

			//we have to perform some checks more, if we change the turnout-parameter from an already saved admission
			if ($admin_admission_data["admission_type_org"]) {
				if ($admin_admission_data["admission_turnout"] < $admin_admission_data["admission_turnout_org"])
					$infomsg.= "info§" . _("Diese Veranstaltung ist teilnahmebeschr&auml;nkt. Wenn Sie die Anzahl der Teilnehmenden verringern, m&uuml;ssen Sie evtl. NutzerInnen, die bereits einen Platz in der Veranstaltung erhalten haben, manuell entfernen!") . "§";

				if ($admin_admission_data["admission_turnout"] > $admin_admission_data["admission_turnout_org"])
					$do_update_admission=TRUE;
			}
		}

		//Prozentangabe checken/berechnen wenn neueer Studiengang, einer geloescht oder Seite abgeschickt
		if (($add_studg_x) || ($delete_studg) || ($uebernehmen_x)) {
			if ($admin_admission_data["admission_type"]) {
				if ((!$admin_admission_data["admission_ratios_changed"]) && (!$add_ratio) && (!$admin_admission_data["admission_type_org"])) {//User hat nichts veraendert oder neuen Studiengang mit Wert geschickt, wir koennen automatisch rechnen
					if (is_array($admin_admission_data["studg"]))
						foreach ($admin_admission_data["studg"] as $key=>$val)
							$admin_admission_data["studg"][$key]["ratio"]=round(100 / (sizeof ($admin_admission_data["studg"]) + 1));
					$admin_admission_data["all_ratio"]=100 - (sizeof ($admin_admission_data["studg"])) * round(100 / (sizeof ($admin_admission_data["studg"]) + 1));
				} else {
					$cnt=0;
					if (is_array($admin_admission_data["studg"]))
						foreach ($admin_admission_data["studg"] as $val)
							$cnt+=$val["ratio"];
					if (($cnt + $admin_admission_data["all_ratio"]) < 100)
						$admin_admission_data["all_ratio"]=100 - $cnt;
					if (($cnt + $admin_admission_data["all_ratio"]) > 100)
						if ($cnt <= 100)
							$admin_admission_data["all_ratio"]=(100 - $cnt);
						else
							$errormsg.= "error§". _("Die Werte der einzelnen Kontigente &uuml;bersteigen 100%. Bitte &auml;ndern Sie die Kontigente!") . "§";
				}
			}
		}

		//Ende der Anmeldung checken
		if ($uebernehmen_x)
			if (($admin_admission_data["admission_type"]) && ($admin_admission_data["admission_endtime"]) && ($admin_admission_data["admission_type"]!=3)) {
				if ($admin_admission_data["admission_type"] == 1)
					$end_date_name="Losdatum";
				else
					$end_date_name="Enddatum der Kontingentierung";
				if ($admin_admission_data["admission_endtime"] == -1)
					$errormsg.="error§Bitte geben Sie einen Termin f&uuml;r das $end_date_name an!§";
				$tmp_first_date = veranstaltung_beginn($admin_admission_data['sem_id'], 'int');
				if($admin_admission_data["admission_type"] == 1){
					if ($admin_admission_data["admission_endtime"] > $tmp_first_date)
						if ($tmp_first_date > 0) {
							if ($admin_admission_data["admission_type"] == 1)
								$errormsg.= sprintf ("error§"._("Das Losdatum liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern Sie das Losdatum!")."§", date ("d.m.Y", $tmp_first_date));
							else
								$errormsg.= sprintf ("error§"._("Das Enddatum der Kontingentierung liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern Sie das Enddatum!")."§", date ("d.m.Y", $tmp_first_date));
						}
					if (!$admin_admission_data["admission_selection_take_place"]) {
						if (($admin_admission_data["admission_endtime"] < time()) && ($admin_admission_data["admission_endtime"] != -1)) {
							if ($admin_admission_data["admission_type"] == 1)
								$errormsg.=sprintf ("error§"._("Das Losdatum liegt in der Vergangenheit. Bitte &auml;ndern Sie das Losdatum!")."§");
							else
								$errormsg.=sprintf ("error§"._("Das Enddatum der Kontingentierung liegt in der Vergangenheit. Bitte &auml;ndern Sie das Datum!")."§");
						} elseif (($admin_admission_data["admission_endtime"] < (time() + (24 * 60 *60))) && ($admin_admission_data["admission_endtime"] != -1)) {
							if ($admin_admission_data["admission_type"] == 1)
								$errormsg.=sprintf ("error§"._("Das Losdatum liegt zu nah am aktuellen Datum. Bitte &auml;ndern Sie das Losdatum!")."§");
							else
								$errormsg.=sprintf ("error§"._("Das Enddatum der Kontingentierung liegt zu nah am aktuellen Datum. Bitte &auml;ndern Sie das Enddatum!")."§");
						}
					}
				} else {
					if($admin_admission_data["admission_endtime"] > time() && $admin_admission_data["admission_selection_take_place"]){
						$admin_admission_data["admission_selection_take_place"] = 0;
					}
				}
			}
	}

	//Meldung beim Wechseln des Modis
	if (($adm_type_old != $admin_admission_data["admission_type"]) && (!$commit_no_admission_data))
		if ($admin_admission_data["admission_type"] > 0)
			$infomsg.=sprintf ("info§"._("Sie haben ein Anmeldeverfahren vorgesehen. Beachten Sie bitte, dass nach dem &Uuml;bernehmen dieser Einstellung alle bereits eingetragenen Nutzerinnen und Nutzer aus der Veranstaltung entfernt werden und das Anmeldeverfahren anschließend nicht mehr abgeschaltet werden kann!")."§");

	
	//Daten speichern
	if (($uebernehmen_x) && (!$errormsg)) {
		
		//Warteliste aktivieren / deaktivieren
		if($admin_admission_data["admission_disable_waitlist"] != $admin_admission_data["admission_disable_waitlist_org"]){
			if($admin_admission_data["admission_disable_waitlist_org"] == 0){ //Warteliste war eingeschaltet
				$db3->query("SELECT admission_seminar_user.user_id ,auth_user_md5.username FROM admission_seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE seminar_id = '".$admin_admission_data["sem_id"]."' AND status='awaiting'");
				while ($db3->next_record()) {
					$db4->query("DELETE FROM admission_seminar_user WHERE user_id='".$db3->f("user_id")."' AND seminar_id='".$admin_admission_data["sem_id"]."' AND status='awaiting'");
					if ($db4->affected_rows()){
						setTempLanguage($db3->f("user_id"));
						$message= sprintf(_("Die Warteliste der Veranstaltung **%s** wurde von einem/r DozentIn oder AdministratorIn deaktiviert, sie sind damit __nicht__ zugelassen worden."), $admin_admission_data["name"]);
						$messaging->insert_message(addslashes($message), $db3->f('username'), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("nicht zugelassen in Veranstaltung"), TRUE);
						restoreLanguage();
					}
				}
			}
		}
		
		//for admission it have to be always 3
		if ($admission_prelim == 1) {
			if ($admin_admission_data["admission_prelim"] == 0) { //we have to move the students to status "temporaly accepted", if put on
				$db3->query("SELECT *,auth_user_md5.username FROM seminar_user,auth_user_md5 WHERE seminar_user.Seminar_id = '".$admin_admission_data["sem_id"]."' AND seminar_user.status='autor' AND seminar_user.user_id = auth_user_md5.user_id");
				while ($db3->next_record()) {
					$db4->query("INSERT INTO admission_seminar_user SET user_id ='".$db3->f("user_id")."', seminar_id = '".$db3->f("Seminar_id")."', studiengang_id ='".$db3->f("admission_studiengang_id")."', mkdate='".$db3->f("mkdate")."', status ='accepted'");
					$db4->query("DELETE FROM seminar_user WHERE user_id='".$db3->f("user_id")."' AND Seminar_id='".$db3->f("Seminar_id")."'");
					$message=sprintf(_("Sie wurden in der Veranstaltung **%s** in den Status **vorläufig akzeptiert** befördert, da das Anmeldeverfahren geändert wurde."), $admin_admission_data["name"]);
					$messaging->insert_message(addslashes($message), $db3->f("username"), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("vorläufig akzeptiert"), TRUE);
				}
				$db3->query("SELECT *, auth_user_md5.username FROM seminar_user, auth_user_md5 WHERE seminar_user.Seminar_id = '".$admin_admission_data["sem_id"]."' AND seminar_user.status='user' AND seminar_user.user_id = auth_user_md5.user_id");
				$db4->query("DELETE FROM seminar_user WHERE Seminar_id = '".$admin_admission_data["sem_id"]."' AND status='user'");
				if ($db4->affected_rows()) {
					while ($db3->next_record()) {
						$message=sprintf(_("Ihr Abonnement der Veranstaltung **%s** wurde aufgehoben, da die Veranstaltung mit einem teilnahmebeschränkten Anmeldeverfahren versehen wurde. \nWenn Sie einen Platz in der Veranstaltung bekommen wollen, melden Sie sich bitte erneut an."), $admin_admission_data["name"]);
						$messaging->insert_message(addslashes($message), $db3->f("username"), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Abonnement aufgehoben"), TRUE);
					}
				}
				$db4->query("UPDATE seminare SET admission_prelim = 1 WHERE Seminar_id = '".$admin_admission_data["sem_id"]."'");
				$admin_admission_data["admission_prelim"] = 1;
			}
		} elseif (!$commit_no_admission_data && $admission_prelim == 0) {
			if ($admin_admission_data["admission_prelim"] == 1) { //we have to move the students again
				if (!$perm->have_perm("admin")) {
					$errormsg.=sprintf ("error§"._("Sie dürfen den Anmeldemodus nicht mehr verändern! Wenden Sie sich ggf. an den zuständigen Admin.")."§");
				} else {
					$db3->query("SELECT *, auth_user_md5.username FROM admission_seminar_user, auth_user_md5 WHERE admission_seminar_user.seminar_id = '".$admin_admission_data["sem_id"]."' AND admission_seminar_user.status='accepted' AND admission_seminar_user.user_id = auth_user_md5.user_id");
					while ($db3->next_record()) {
						$db4->query("INSERT INTO seminar_user SET user_id ='".$db3->f("user_id")."', Seminar_id = '".$db3->f("seminar_id")."', admission_studiengang_id ='".$db3->f("studiengang_id")."', mkdate='".$db3->f("mkdate")."', status='autor'");
						$db4->query("DELETE FROM admission_seminar_user WHERE user_id='".$db3->f("user_id")."' AND seminar_id='".$db3->f("seminar_id")."'");
						$message=sprintf(_("Sie wurden in der Veranstaltung **%s** in den Status **Autor** versetzt, da das Anmeldeverfahren geändert wurde."), $admin_admission_data["name"]);
						$messaging->insert_message(addslashes($message), $db3->f("username"), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Statusänderung"), TRUE);
					}
					$db4->query("UPDATE seminare SET admission_prelim = 0 WHERE Seminar_id = '".$admin_admission_data["sem_id"]."'");
					$admin_admission_data["admission_prelim"] = 0;
				}
			}
		}

		if ($admin_admission_data["admission_type"]) {
			$admin_admission_data["read_level"]=3;
			$admin_admission_data["write_level"]=3;
		}

		$db->query ("UPDATE seminare SET
				admission_turnout = '".$admin_admission_data["admission_turnout"]."' ,
				admission_type = '".$admin_admission_data["admission_type"]."',
				admission_endtime= '".$admin_admission_data["admission_endtime"]."',
				admission_binding = '".$admin_admission_data["admission_binding"]."',
				admission_starttime = '".$admin_admission_data["sem_admission_start_date"]."',
				admission_endtime_sem = '".$admin_admission_data["sem_admission_end_date"]."',
				admission_prelim_txt = '".$admin_admission_data["admission_prelim_txt"]."',
				Passwort = '".$admin_admission_data["passwort"]."',
				Lesezugriff = '".$admin_admission_data["read_level"]."',
				Schreibzugriff  = '".$admin_admission_data["write_level"]."',
				admission_disable_waitlist = '".$admin_admission_data['admission_disable_waitlist']."',
				admission_selection_take_place = '".$admin_admission_data["admission_selection_take_place"]."'
				WHERE seminar_id = '".$admin_admission_data["sem_id"]."' ");

		//check, if we need to update the admission data after saving new settings
		if ($do_update_admission)
    			update_admission($admin_admission_data["sem_id"]);

		if ($db->affected_rows()) {
			$errormsg.="msg§"._("Die Berechtigungseinstellungen f&uuml;r die Veranstaltung wurden aktualisiert")."§";
			$db->query ("UPDATE seminare SET chdate='".time()."' WHERE Seminar_id ='".$admin_admission_data["sem_id"]."'");
			}

		//Variante nachtraeglich Anmeldeverfahren starten, alle alten Teilnehmer muessen raus
		if (($admin_admission_data["admission_type"] >$admin_admission_data["admission_type_org"]) && ($admin_admission_data["admission_type_org"]==0) && $admin_admission_data["admission_type"]!=3) {
			$db->query("SELECT seminar_user.user_id, username FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE Seminar_id ='".$admin_admission_data["sem_id"]."' AND status IN ('autor', 'user') ");
			$db2->query("DELETE FROM seminar_user WHERE Seminar_id ='".$admin_admission_data["sem_id"]."' AND status IN ('autor', 'user') ");
			if ($db2->affected_rows()) {
				while ($db->next_record()) {
					$message="Ihr Abonnement der Veranstaltung **".$admin_admission_data["name"]."** wurde aufgehoben, da die Veranstaltung mit einem teilnahmebeschränkten Anmeldeverfahren versehen wurde. \nWenn Sie einen Platz in der Veranstaltung bekommen wollen, melden Sie sich bitte erneut an.";
					$messaging->insert_message (addslashes($message), $db->f("username"), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Abonnement aufgehoben"), TRUE);
				}
			}

			//Kill old data
			$db2->query ("DELETE FROM admission_seminar_studiengang WHERE seminar_id= '".$admin_admission_data["sem_id"]."' ");
			$admin_admission_data["write_level"]='';
			$admin_admission_data["read_level"]='';
			$admin_admission_data["passwort"]='';
		}

		//Variante nachtraeglich Anmeldeverfahren beenden, alle aus Warteliste kommen in die Veranstaltung
		if (($admin_admission_data["admission_type"] == 0) && ($admin_admission_data["admission_type_org"] > 0)) {
			$db->query("SELECT admission_seminar_user.user_id, username  FROM admission_seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE seminar_id ='".$admin_admission_data["sem_id"]."' ");
			while ($db->next_record()) {
				$group=select_group ($admin_admission_data["start_time"], $db->f("user_id"));
				$db2->query("INSERT INTO seminar_user SET user_id = '".$db->f("user_id")."', Seminar_id = '".$admin_admission_data["sem_id"]."', status='autor', gruppe='$group', mkdate ='".time()."' ");
				$message="Sie wurden in die Veranstaltung **".$admin_admission_data["name"]."** eingetragen, da das Anmeldeverfahren aufgehoben wurde. Damit sind Sie als Teilnehmer der Präsenzveranstaltung zugelassen.";
				$messaging->insert_message(addslashes($message), $db->f("username"), "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("Eintragung in Veranstaltung"), TRUE);
			}
			if ($db->num_rows())
				$db2->query("DELETE FROM admission_seminar_user  WHERE seminar_id ='".$admin_admission_data["sem_id"]."' ");

			//Kill old Studiengang entries and data
			$db2->query ("DELETE FROM admission_seminar_studiengang WHERE seminar_id= '".$admin_admission_data["sem_id"]."' ");
			$admin_admission_data["studg"]='';
			$admin_admission_data["all_ratio"]='';
			$admin_admission_data["admission_ratios_changed"]='';
			$admin_admission_data["admission_endtime"]='';
		}

		//Eintrag der zugelassen Studienbereiche
		if ($admin_admission_data["admission_type"]) {
			$query = "DELETE FROM admission_seminar_studiengang WHERE seminar_id= '".$admin_admission_data["sem_id"]."' ";
			$db->query($query); // Alle Eintraege rauswerfen

			if (is_array($admin_admission_data["studg"]))
				foreach($admin_admission_data["studg"] as $key=>$val)
					if ($val["ratio"]) {
						$query = "INSERT INTO admission_seminar_studiengang (seminar_id,studiengang_id,quota) VALUES('".$admin_admission_data["sem_id"]."', '$key', '".$val["ratio"]."' )";
						$db->query($query);// Studiengang eintragen
					}

			if ($admin_admission_data["all_ratio"]) {
				$query = "INSERT INTO admission_seminar_studiengang (seminar_id,studiengang_id,quota) VALUES('".$admin_admission_data["sem_id"]."', 'all', '".$admin_admission_data["all_ratio"]."' )";
				$db->query($query);// Studiengang eintragen

			//Save the current state as snapshot to compare with current data
			$admin_admission_data["original"]=get_snapshot();
			}
		}

		//Save the current state as snapshot to compare with current data
		$admin_admission_data["original"] = get_snapshot();
		$admin_admission_data["admission_turnout_org"] = $admin_admission_data["admission_turnout"];
		$admin_admission_data["admission_type_org"] = $admin_admission_data["admission_type"];
	}
}

//Beim Umschalten keine Fehlermeldung
 if (($errormsg) && ((!$uebernehmen_x) &&(!$adm_null_x) &&(!$adm_los_x) &&(!$adm_chrono_x) && (!$adm_gesperrt_x) && (!$add_studg_x) && (!$delete_studg)))
 	$errormsg='';

//check, ob Warteliste gefüllt.
$db->query("SELECT count(*) FROM admission_seminar_user WHERE seminar_id = '$seminar_id' AND status='awaiting'");
$db->next_record();
$num_waitlist = $db->f(0);

$num_all = $admin_admission_data["admission_turnout"];

if (is_array($admin_admission_data["studg"]) && $admin_admission_data["admission_turnout"]){
	foreach ($admin_admission_data["studg"] as $key => $val){
		if ($val["ratio"]) {
			$num_stg[$key] = round($admin_admission_data["admission_turnout"] * $val["ratio"] / 100);
			$num_all -= $num_stg[$key];
		}
	}
	if ($num_all < 0) $num_all = 0;
}
?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<?
	$errormsg.=$infomsg;
	if (isset($errormsg)) {
	?>
	<tr>
		<td class="blank" colspan=2><br />
		<?parse_msg($errormsg);?>
		</td>
	</tr>
	<? } ?>
 	<tr>
		<td class="blank" valign="top">
			<br />
			<blockquote>
			<b><?=_("Zugangsberechtigungen der Veranstaltung bearbeiten") ?></b><br /><br />
			<?=_("Sie k&ouml;nnen hier die Zugangsberechtigungen bearbeiten.")?> <br />
			<?=_("Sie haben auf dieser Seite ebenfalls die M&ouml;glichkeit, ein Anmeldeverfahren f&uuml;r die Veranstaltung festzulegen.")?><br />
			</blockqoute>
		</td>
		<td class="blank" align="right">
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/board2.jpg" border="0">
		</td>
	</tr>
	<tr>
	<td class="blank" colspan=2>
	<form method="POST" name="Formular" action="<? echo $PHP_SELF ?>"
	<? if (!$admin_admission_data["admission_type"]) echo " onSubmit=\"return doCrypt();\" "; ?>
	>
		<table width="99%" border=0 cellpadding=2 cellspacing=0 align="center">
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
				<input type="IMAGE" name="uebernehmen" <?=makeButton("uebernehmen", "src")?> border=0 value="uebernehmen">
				<?if ($admin_admission_data["original"] != get_snapshot()) {
					?> <br /><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><?=_("Diese Daten sind noch nicht gespeichert.")?></font><br /> <?
					}
				?>
			</td>
		</tr>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align="left">
				<font size=-1><b><?=_("Anmeldeverfahren:")?></b><br /></font>
				<? if (($admin_admission_data["admission_type_org"] && $admin_admission_data["admission_type_org"] != 3) && (!$perm->have_perm("admin"))) {
					$db->query("SELECT username, ". $_fullname_sql['full'] . "  as fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE institut_id ='".$admin_admission_data["heimat_inst_id"]."' AND perms = 'admin'");
					if  (!$db->num_rows())
						printf ("<font size=-1>"._("Sie haben ein Anmeldeverfahren aktiviert. Dieser Schritt kann %s nicht %s r&uuml;ckg&auml;ngig gemacht werden! Bei Problemen wenden sie sich bitte an eine Administratorin oder einen Administrator.")."<br /></font>", "</font><font size=-1 color=\"red\"><b>", "</b></font><font size=-1>");
					else
						printf ("<font size=-1>"._("Sie haben ein Anmeldeverfahren aktiviert. Dieser Schritt kann %s nicht %s r&uuml;ckg&auml;ngig gemacht werden! Bei Problemen wenden sie sich bitte an eineN der hier aufgef&uuml;hrten AdministratorInnen.")."<br /></font>", "</font><font size=-1 color=\"red\"><b>", "</b></font><font size=-1>");
					printf ("<input type=\"HIDDEN\" name=\"commit_no_admission_data\" value=\"TRUE\" />");
					while ($db->next_record()) {
						printf ("<li><font size=-1><a href=\"about.php?username=%s\">%s</a></font></li>", $db->f("username"), htmlReady($db->f("fullname")));
					}
				} else {
					if(is_object($group_obj)){
						?>
						<font size="-1">
						<?=_("Diese Veranstaltung ist Mitglied einer Gruppe. Die Art des Anmeldeverfahrens können sie nur für die Gruppe insgesamt ändern.")?>
						<br>
						<a href="show_admission.php?group_sem_x=1&group_id=<?=$group_obj->getId()?>">
						<img src="<?=$GLOBALS['ASSETS_URL']?>images/link_intern.gif" align="absmiddle" border="0">&nbsp;&nbsp;<?=_("Gruppenverwaltung")?></a>
						<div style="margin-top:5px;">
						<?=_("Veranstaltungsgruppe:")?>&nbsp;<?=htmlReady($group_obj->getValue('name'))?>
						<ol>
						<?foreach($group_obj->getMemberIds() as $m_id){?>
							<li><a href="admin_admission.php?select_sem_id=<?=$m_id?>"><?=htmlReady($group_obj->members[$m_id]->getName())?></a></li>
						<?}?>
						</ol>
						</div>
						<?
					} else {
				?><font size=-1><?=_("Sie k&ouml;nnen hier eine Teilnahmebeschr&auml;nkung per Anmeldeverfahren festlegen. Sie k&ouml;nnen per Losverfahren beschr&auml;nken oder Anmeldungen in der Reihenfolge ihres Eintreffens (chronologische Anmeldung) zulassen. Wenn Sie eine Veranstaltung sperren, kann sich niemand zu dieser Veranstaltung anmelden. Bestehende Teilnehmer- und Wartelisteneintr&auml;ge bleiben bei einem Wechsel von <B>keins</B> auf <B>gesperrt</B> unber&uuml;hrt.")?><br /></font>
				<br /><input type="IMAGE" name="adm_null" <? if  ($admin_admission_data["admission_type"] == 0) print makeButton ("keins2", "src");  else print makeButton ("keins", "src") ?> border=0 value="keins">&nbsp;
				<input type="IMAGE" name="adm_los" <? if  ($admin_admission_data["admission_type"] == 1) print makeButton ("los2", "src");  else print makeButton ("los", "src") ?> border=0 value="los">&nbsp;
				<input type="IMAGE" name="adm_chrono" <? if  ($admin_admission_data["admission_type"] == 2) print makeButton ("chronolog2", "src");  else print makeButton ("chronolog", "src") ?>border=0 value="chronolog">
				<input type="IMAGE" name="adm_gesperrt" <? if  ($admin_admission_data["admission_type"] == 3) print makeButton ("gesperrt2", "src");  else print makeButton ("gesperrt", "src") ?>border=0 value="gesperrt">
				<? }
				}?>
				<input type="HIDDEN" name="adm_type_old" value="<? echo $admin_admission_data["admission_type"] ?>" /><br />
			</td>
		</tr>

		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right" rowspan=2>
					&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align="left">
					<font size=-1><b>&nbsp;<?= _("Start- und Endzeit:") ?></b><br /></font>
					<font size=-1>&nbsp;<?= _("Sie k&ouml;nnen hier angeben, in welchem Zeitraum eine Anmeldung f&uuml;r die Veranstaltung m&ouml;glich ist.") ?><br /></font>
			</td>
		</tr>

		<tr>
			<td class="<? echo $cssSw->getClass() ?>" colspan="2">
				<table border=0 cellpadding=2 cellspacing=0 align="center" width="100%">
				<tr>
					<td class="<? echo $cssSw->getClass() ?>" valign="top" align="right" width="10%">
						<font size=-1><? echo _("Startdatum f&uuml;r Anmeldungen");?>:</font>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" valign="top" width="30%">
						<font size=-1>&nbsp; <input type="text" name="adm_s_tag" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_start_date"]<>-1) echo date("d",$admin_admission_data["sem_admission_start_date"]); else echo _("tt") ?>">.
						<input type="text" name="adm_s_monat" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_start_date"]<>-1) echo date("m",$admin_admission_data["sem_admission_start_date"]); else echo _("mm") ?>">.
						<input type="text" name="adm_s_jahr" size=4 maxlength=4 value="<? if ($admin_admission_data["sem_admission_start_date"]<>-1) echo date("Y",$admin_admission_data["sem_admission_start_date"]); else echo _("jjjj") ?>"><?=_("um");?>&nbsp;</font><br />
						<font size=-1>&nbsp; <input type="text" name="adm_s_stunde" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_start_date"]<>-1) echo date("H",$admin_admission_data["sem_admission_start_date"]); else echo "00" ?>">:
						<input type="text" name="adm_s_minute" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_start_date"]<>-1) echo date("i",$admin_admission_data["sem_admission_start_date"]); else  echo "00" ?>">&nbsp;<?=_("Uhr");?></font>
					<?=Termin_Eingabe_javascript(20,0,($admin_admission_data["sem_admission_start_date"] != -1 ? $admin_admission_data["sem_admission_start_date"] : 0));?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" valign="top" align="right" width="10%">
						<font size=-1><? echo _("Enddatum f&uuml;r Anmeldungen");?>:</font>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" valign="top" width="30%">
						<font size=-1>&nbsp; <input type="text" name="adm_e_tag" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_end_date"]<>-1) echo date("d",$admin_admission_data["sem_admission_end_date"]); else echo _("tt") ?>">.
						<input type="text" name="adm_e_monat" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_end_date"]<>-1) echo date("m",$admin_admission_data["sem_admission_end_date"]); else echo _("mm") ?>">.
						<input type="text" name="adm_e_jahr" size=4 maxlength=4 value="<? if ($admin_admission_data["sem_admission_end_date"]<>-1) echo date("Y",$admin_admission_data["sem_admission_end_date"]); else echo _("jjjj") ?>"><?=_("um");?>&nbsp;</font><br />
						<font size=-1>&nbsp; <input type="text" name="adm_e_stunde" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_end_date"]<>-1) echo date("H",$admin_admission_data["sem_admission_end_date"]); else echo "23" ?>">:
						<input type="text" name="adm_e_minute" size=2 maxlength=2 value="<? if ($admin_admission_data["sem_admission_end_date"]<>-1) echo date("i",$admin_admission_data["sem_admission_end_date"]); else echo "59" ?>">&nbsp;<?=_("Uhr");?></font>
					<?=Termin_Eingabe_javascript(21,0,($admin_admission_data["sem_admission_end_date"] != -1 ? $admin_admission_data["sem_admission_end_date"] : 0));?>
					</td>
					<td class="<? echo $cssSw->getClass() ?>" >
						<?=makeButton('loeschen', 'input' , _("Start- und Enddatum zurücksetzen"), 'reset_admission_time')?>
					</td>
				</tr>
				</table>
			</td>
		</tr>

		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align="left">
				<font size=-1>
					<?
					if ((!$perm->have_perm("admin")) && ($admin_admission_data["admission_prelim"] == 1)) {
						$db->query("SELECT username, ". $_fullname_sql['full'] . "  as fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE institut_id ='".$admin_admission_data["heimat_inst_id"]."' AND perms = 'admin'");
						echo "<b>"._("Anmeldemodus:")."</b><br />";
						echo _("Sie haben den Anmeldemodus \"Vorl&auml;ufiger Eintrag\" aktiviert. ");
						printf ("<font size=-1>"._("Dieser Schritt kann %s nicht %s r&uuml;ckg&auml;ngig gemacht werden! ")."</font>", "</font><font size=-1 color=\"red\"><b>", "</b></font><font size=-1>");
						if  (!$db->num_rows())
							echo _("Bei Problemen wenden sie sich bitte an eine Administratorin oder einen Administrator.");
						else
							echo _("Bei Problemen wenden sie sich bitte an eineN der hier aufgef&uuml;hrten AdministratorInnen.");
						printf ("<input type=\"HIDDEN\" name=\"commit_no_admission_data\" value=\"TRUE\" />");
						while ($db->next_record()) {
							printf ("<li><font size=-1><a href=\"about.php?username=%s\">%s</a></font></li>", $db->f("username"), htmlReady($db->f("fullname")));
						}
					} else { ?>
						<b><?=_("Anmeldemodus:")?></b><br />
						<? echo _("Bitte wählen Sie sie hier einen Anmeldemodus aus:"); ?><br/>
						<input type="RADIO"  name="admission_prelim" value="0" <? if ($admin_admission_data["admission_prelim"] == 0) echo "checked"; ?> /><?=_("Direkter Eintrag")?>&nbsp;
						<input type="RADIO"  name="admission_prelim" value="1" <? if ($admin_admission_data["admission_prelim"] == 1) echo "checked"; ?> /><?=_("Vorl&auml;ufiger Eintrag")?>
					<? } ?>
				</font>
			</td>
		</tr>
		<? if ($admin_admission_data["admission_prelim"] == 1) { ?>
			<tr>
			<td class="<? echo $cssSw->getClass() ?>" align=right>&nbsp;</td>
			<td class="<? echo $cssSw->getClass() ?>" align=left colspan=2><font size=-1><? echo _("Hinweistext bei vorl&auml;ufigen Eintragungen:"); ?></font><br><textarea name="admission_prelim_txt" cols=58 rows=4><?php echo htmlReady($admin_admission_data["admission_prelim_txt"]) ?></textarea></td>
			</tr>
		<?
		}
		if (!$admin_admission_data["admission_type"]  || $admin_admission_data["admission_type"] == 3) {
		?>
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>" colspan=2 align="left">
				<font size=-1><b><?=_("Berechtigungen:")?></b><br /></font>
				<font size=-1><?=_("Legen Sie hier fest, welche Teilnehmer Zugriff auf die Veranstaltung haben.")?><br /></font>
				<input type="HIDDEN" name="admisision_turnout" value="<? echo $admin_admission_data["admission_turnout"] ?>">
			</td>
		</tr>
		<tr>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="20%" align="left">
			<?
			if (!isset($admin_admission_data["read_level"]) || $admin_admission_data["read_level"]==3)
				$admin_admission_data["read_level"]= "1";	//Vorgabe: nur angemeldet oder es war Teilnahmebegrenzung gesetzt
				?>
				<font size=-1><u><?=("Lesezugriff:")?></u> </font><br />
				<font size=-1>
				<?if (get_config('ENABLE_FREE_ACCESS')) {?>
					<input type="radio" name="read_level" value="0" <?php print $admin_admission_data["read_level"] == 0 ? "checked" : ""?>> <?=_("freier Zugriff")?> &nbsp;<br />
				<?} else {?>
					<font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  <?=_("freier Zugriff")?> &nbsp;</font><br />
				<?}?>
				<input type="radio" name="read_level" value="1" <?php print $admin_admission_data["read_level"] == 1 ? "checked" : ""?>> <?=_("in Stud.IP angemeldet")?> &nbsp;<br />
				<input type="radio" name="read_level" value="2" <?php print $admin_admission_data["read_level"] == 2 ? "checked" : ""?>> <?=_("nur mit Passwort")?> &nbsp;<br />
				</font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="76%" align="left">
					&nbsp;<font size=-1><u><?=_("Schreibzugriff:")?></u> </font><br />
					<font size=-1>
			<?
			if (!isset($admin_admission_data["write_level"]) || $admin_admission_data["write_level"]==3)
				$admin_admission_data["write_level"] = "1";	//Vorgabe: nur angemeldet
				if (get_config('ENABLE_FREE_ACCESS') && $SEM_CLASS[$SEM_TYPE[$admin_admission_data["status"]]["class"]]["write_access_nobody"]) {
				?>
				<input type="radio" name="write_level" value="0" <?php print $admin_admission_data["write_level"] == 0 ? "checked" : ""?>> <?=_("freier Zugriff")?> &nbsp;<br />
				<?
				} else {
				?>
				<font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  <?=_("freier Zugriff")?> &nbsp;</font><br />
				<?
				}
				?>
				<input type="radio" name="write_level" value="1" <?php print $admin_admission_data["write_level"] == 1 ? "checked" : ""?>> <?=_("in Stud.IP angemeldet")?> &nbsp;<br />
				<input type="radio" name="write_level" value="2" <?php print $admin_admission_data["write_level"] == 2 ? "checked" : ""?>> <?=_("nur mit Passwort")?> &nbsp;<br />
				</font>
			</td>
			</tr>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b><?=_("Passwort:")?> </b></font><br />
					<font size=-1><?=_("Bitte geben Sie hier ein Passwort ein, wenn sie <b>Zugriff nur mit Passwort</b> gew&auml;hlt haben.")?></font><br /><br />
					<?
					if ($admin_admission_data["passwort"]!="") {
						echo "<font size=-1><input type=\"password\" ";
						echo "name=\"password\"  onchange=\"checkpassword()\" size=12 maxlength=31 value=\"*******\">&nbsp; "._("Passwort-Wiederholung:")."&nbsp; <input type=\"password\" ";
						echo "name=\"password2\" onchange=\"checkpassword2()\" size=12 maxlength=31 value=\"*******\"></font>";
					}
					else {
						echo "<font size=-1><input type=\"password\" name=\"password\" ";
						echo "onchange=\"checkpassword()\" size=12 maxlength=31> &nbsp; "._("Passwort-Wiederholung:")."&nbsp; <input type=\"password\" name=\"password2\" ";
						echo "onchange=\"checkpassword2()\" size=12 maxlength=31></font>";
					}
					?>
					<input type="HIDDEN" name="hashpass" value="">
					<input type="HIDDEN" name="hashpass2" value="">
				</td>
			</tr>
		<?
		} else {
		?>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b><?=_("maximale Teilnehmeranzahl:")?> </b></font><br />
					<font size=-1><?=_("Diese Teilnehmeranzahl dient als Grundlage zur Berechnung der Pl&auml;tze pro Kontingent.")?></font><br /><br />
					<font size=-1><input type="TEXT" name="admission_turnout" size=2 maxlength=5 value="<? echo $admin_admission_data["admission_turnout"]; ?>" /> <?=_("Teilnehmende")?></font>
				</td>
			</tr>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<table border=0 cellpadding=2 cellspacing=0>
						<tr>
							<font size=-1><b><?=_("zugelassenene Studieng&auml;nge:")?> </b></font><br />
							<td class="<? echo $cssSw->getClass() ?>" colspan=3 width="100%">
								<font size=-1><?=_("Bitte geben Sie hier ein, welche Studieng&auml;nge im Anmeldeverfahren zugelassen sind.")."</font>"?>
							</td>
						</tr>
						<tr>
							<td class="<? echo $cssSw->getClass() ?>" valign="bottom" width="25%">
									<font size=-1>
									<?
									printf ("%s", ($admin_admission_data["studg"]) ? _("Alle anderen Studieng&auml;nge") : _("Alle Studieng&auml;nge"));
									?>
									</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" valign="bottom"  colspan=2 nowrap width="75%">
								<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
									printf ("&nbsp; &nbsp; <font size=-1>%s %% (%s Teilnehmer)</font>", $admin_admission_data["all_ratio"], $num_all);
								} else {
									printf ("<input type=\"HIDDEN\" name=\"all_ratio_old\" value=\"%s\" />", ($admin_admission_data["studg"]) ? $admin_admission_data["all_ratio"] : "100");
									printf ("<input type=\"TEXT\" name=\"all_ratio\" size=5 maxlength=5 value=\"%s\" /> <font size=-1> %% (%s Teilnehmer)</font>", ($admin_admission_data["studg"]) ? $admin_admission_data["all_ratio"] : "100", $num_all);
									} ?>
								</td>
							</tr>
							<?
							if ($admin_admission_data["studg"]) {
								foreach ($admin_admission_data["studg"] as $key=>$val) {
							?>
							<tr>
								<td class="<? echo $cssSw->getClass() ?>" width="25%">
								<font size=-1>
								<?
								echo (htmlReady(my_substr($val["name"], 0, 40)));
								?>
								</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" nowrap colspan=2 width="75%">
								<input type="HIDDEN" name="studg_id[]" value="<? echo $key ?>" />
								<input type="HIDDEN" name="studg_name[]" value="<? echo $val["name"] ?>" />
								<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
									printf ("&nbsp; &nbsp; <font size=-1>%s %% (%s Teilnehmer)</font>", $val["ratio"], $num_stg[$key]);
								} else {
									printf ("<input type=\"HIDDEN\" name=\"studg_ratio_old[]\" value=\"%s\" />", $val["ratio"]);
									printf ("<input type=\"TEXT\" name=\"studg_ratio[]\" size=5 maxlength=5 value=\"%s\" /><font size=-1> %% (%s Teilnehmer)</font>", $val["ratio"], $num_stg[$key]);
									printf ("&nbsp; <a href=\"%s?delete_studg=%s\"><img border=0 src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("Den Studiengang aus der Liste löschen"))." />", $PHP_SELF, $key, $val["name"]);
								}
								?>
								</td>
							</tr>
							<?
								}
							}
							$db->query("SELECT * FROM studiengaenge");
							if ($db->num_rows() != sizeof($admin_admission_data["studg"])) {
								if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
									;
									} else {
								?>
							<tr>
								<td class="<? echo $cssSw->getClass() ?>" width="25%">
								<font size=-1>
								<select name="add_studg">
								<option value="">-- <?=_("bitte ausw&auml;hlen")?> --</option>
								<?
								while ($db->next_record()) {
									if (is_array($admin_admission_data["studg"])) {
										if (!$admin_admission_data["studg"][$db->f("studiengang_id")])
											printf ("<option value=%s>%s</option>", $db->f("studiengang_id"), htmlReady(my_substr($db->f("name"), 0, 40)));
										}
									else
										printf ("<option value=%s>%s</option>", $db->f("studiengang_id"), htmlReady(my_substr($db->f("name"), 0, 40)));
								}
								?>
								</select>
								</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" nowrap width="5%">
								<input type="TEXT" name="add_ratio" size=5 maxlength=5 /><font size=-1> %</font>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="25%">
									&nbsp;<input type="IMAGE" <?=makeButton("hinzufuegen", "src")?> name="add_studg" border=0 />&nbsp;
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="40%">&nbsp;
								</td>
							</tr>
								<?
								}
							}
							?>
					</table>
				</td>
			</tr>
			<tr  <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b><? if ($admin_admission_data["admission_type"] == 1) echo _("Losdatum"); else echo _("Enddatum der Kontingentierung");?>:</b></font><br />
					<?
					if ($admin_admission_data["admission_type"] == 1) {
						?>
						<font size=-1><?=_("Bitte geben Sie hier ein, wann die Wartenden auf der Anmeldeliste in die Veranstaltung gelost werden sollen.")?></font><br /><br />
						<?
					} else {
						?>
						<font size=-1><?=_("Bitte geben Sie hier ein, wann das Anmeldeverfahren die Kontingentierung aufheben soll.")?> </font><br /><br />
						<?
					}
					?>
					<? if (($admin_admission_data["admission_type_org"]) && (!$perm->have_perm("admin"))) {
						printf ("<font size=-1>%s um %s Uhr </font>", date("d.m.Y",$admin_admission_data["admission_endtime"]), date("H:i",$admin_admission_data["admission_endtime"]));
					} else { ?>
					<font size=-1><input type="text" name="adm_tag" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("d",$admin_admission_data["admission_endtime"]); else echo _("tt") ?>">.
					<input type="text" name="adm_monat" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("m",$admin_admission_data["admission_endtime"]); else echo"mm" ?>">.
					<input type="text" name="adm_jahr" size=4 maxlength=4 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("Y",$admin_admission_data["admission_endtime"]); else echo _("jjjj") ?>"><?=_("um")?>&nbsp;
					<font size=-1><input type="text" name="adm_stunde" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("H",$admin_admission_data["admission_endtime"]); else echo"23" ?>">:
					<input type="text" name="adm_minute" size=2 maxlength=2 value="<? if ($admin_admission_data["admission_endtime"]<>-1) echo date("i",$admin_admission_data["admission_endtime"]); else echo"59" ?>">&nbsp;<?=_("Uhr")?></font>&nbsp;
					<?=Termin_Eingabe_javascript(22,0,($admin_admission_data["admission_endtime"] != -1 ? $admin_admission_data["admission_endtime"] : 0));?>
					<? } ?>
					</td>
			</tr>
			<?if (get_config('ADMISSION_ALLOW_DISABLE_WAITLIST')) {?>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b><?=_("Warteliste:")?> </b></font><br />
					<font size=-1><?=_("Bitte aktivieren Sie diese Einstellung, wenn eine Warteliste erstellt werden soll falls die Anmeldungen die maximale Teilnehmeranzahl überschreiten:")?></font><br />
					<?if ($num_waitlist && !$admin_admission_data["admission_disable_waitlist"]){
						?>
						<font size=-1 color="red"><b><?=_("Achtung:")?></b></font>&nbsp;
						<font size=-1>
						<?=sprintf(_("Es existiert eine Warteliste mit %s Einträgen. Wenn Sie die Warteliste ausschalten, werden diese Einträge gelöscht."), $num_waitlist)?>
						</font><br /><br />
					<?}?>
					<input type="hidden" name="admission_waitlist" value="0">
					<font size=-1><input type="CHECKBOX" name="admission_waitlist" value="1" <? if (!$admin_admission_data["admission_disable_waitlist"]) echo "checked"; ?> /><?=_("Warteliste aktivieren")?></font>
				</td>
			</tr>
			<?}?>
			<tr <? $cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" width="4%">
					&nbsp;
				</td>
				<td class="<? echo $cssSw->getClass() ?>" width="96%" colspan=2>
					<font size=-1><b><?=_("verbindliche Anmeldung:")?> </b></font><br />
					<font size=-1><?=_("Bitte aktivieren Sie diese Einstellung, wenn die Anmeldung f&uuml;r Veranstaltungen verbindlich erfolgen soll:")?></font><br />
					<font size=-1 color="red"><b><?=_("Achtung:")?></b></font>&nbsp;<font size=-1><?=_("Verwenden Sie diese Option nur bei entsprechenden Bedarf, etwa nach erfolgter Teilnehmerauswahl durch Losen!")?></font><br /><br />
					<font size=-1><input type="CHECKBOX" name="admission_binding" <? if ($admin_admission_data["admission_binding"]) echo "checked"; ?> /><?=_("Anmeldung ist <u>verbindlich</u>. (Teilnehmer k&ouml;nnen sich nicht austragen.)")?></font>
				</td>
			</tr>
		<?
		}
		?>
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan=3>
				<input type="IMAGE" name="uebernehmen" <?=makeButton("uebernehmen", "src")?> border=0 value="uebernehmen">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=3>&nbsp;
			</td>
		</tr>
		<?
page_close();
?>
	</table>
</td>
</tr>
</table>

<?php
include ('lib/include/html_end.inc.php');
page_close();
?>