<?
/*
admin_seminare_assi.php - Seminar-Assisten von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>

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
  $perm->check("dozent");

// Set this to something, just something different...
$hash_secret = "nirhtak";

include "$ABSOLUTE_PATH_STUDIP/seminar_open.php"; 	//hier werden die sessions initialisiert

require_once "$ABSOLUTE_PATH_STUDIP/msg.inc.php"; 		//Funktionen fuer Nachrichtenmeldungen
require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php"; 		//wir brauchen die Seminar-Typen
require_once "$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php";  //Bereitstellung weiterer Daten
require_once "$ABSOLUTE_PATH_STUDIP/functions.php";	//noch mehr Stuff
require_once "$ABSOLUTE_PATH_STUDIP/forum.inc.php";		//damit wir Themen anlegen koennen
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";		//Aufbereitungsfunktionen
require_once "$ABSOLUTE_PATH_STUDIP/dates.inc.php";		//Terminfunktionen

// Get a database connection and Stuff
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$cssSw = new cssClassSwitcher;
$user_id = $auth->auth["uid"];
$errormsg='';

//Registrieren der Sessionvariablen
$sess->register("sem_create_data");
$sess->register("links_admin_data");

//Assi-Modus an (Kopfzeile ignoiert evtl. gesetze Seminare un verwaltet nur angelegtes Seminar (ab erfolgreichem Schritt 5)
$links_admin_data["assi"]=TRUE;

if (((time() - $sem_create_data["timestamp"]) >$auth->lifetime*60) || ($new_session))
	{
	$sem_create_data='';
	$links_admin_data='';
	$sem_create_data["sem_start_termin"]=-1;
	$sem_create_data["sem_vor_termin"]=-1;
	$sem_create_data["sem_vor_end_termin"]=-1;
	$sem_create_data["sem_admission_date"]=-1;
	$sem_create_data["sem_admission_ratios_changed"]=FALSE;
		
	$sem_create_data["timestamp"]=time();
	}
else 
	$sem_create_data["timestamp"]=time();
	
//wenn das Seminar bereits geschrieben wurde und wir trotzdem frisch reinkommen, soll die Variable geloescht werden
if (($sem_create_data["sem_entry"]) && (!$form)) 
	{
	$sem_create_data='';
	$sem_create_data["sem_start_termin"]=-1;
	$sem_create_data["sem_vor_termin"]=-1;
	$sem_create_data["sem_vor_end_termin"]=-1;	
	}	

//empfangene Variablen aus diversen Formularen auswerten
if ($form==1)
	{
	$sem_create_data["sem_name"]=$sem_name;
	$sem_create_data["sem_untert"]=$sem_untert;
	$sem_create_data["sem_nummer"]=$sem_nummer;
	$sem_create_data["sem_ects"]=$sem_ects;	
	$sem_create_data["sem_ort"]=$sem_ort;
	$sem_create_data["sem_desc"]=$sem_desc;
	$sem_create_data["sem_inst_id"]=$sem_inst_id;
	$sem_create_data["term_art"]=$term_art;	
	$sem_create_data["sem_turnout"]=$sem_turnout;	
	//Anmeldeverfahren festlegen
	$sem_create_data["sem_admission"]=$sem_admission;
	
	if ($sem_bet_inst)
		{
		foreach ($sem_bet_inst as $tmp_array)
				$tmp_create_data_bet_inst[]=$tmp_array;
		$sem_create_data["sem_bet_inst"]=$tmp_create_data_bet_inst;
		}
	$i=0;
	foreach ($SEM_CLASS as $a) {
		$i++;
		if ($sem_class==$a["name"])
			$sem_create_data["sem_class"]=$i;
		}
	}

if ($form==2)
	{
	$i=0;
	for ($i; $i<sizeof($sem_doz); $i++)
		$tmp_create_data_doz[]=$sem_doz[$i];
	unset ($sem_create_data["sem_doz"]);
	$sem_create_data["sem_doz"]=$tmp_create_data_doz;
	$i=0;
	for ($i; $i<sizeof($sem_tut); $i++)
		$tmp_create_data_tut[]=$sem_tut[$i];
	unset ($sem_create_data["sem_tut"]);
	$sem_create_data["sem_tut"]=$tmp_create_data_tut;
	$i=0;
	for ($i; $i<sizeof($sem_bereich); $i++)
		$tmp_create_data_bereich[]=$sem_bereich[$i];
	unset ($sem_create_data["sem_bereich"]);
	$sem_create_data["sem_bereich"]=$tmp_create_data_bereich;
	if (!$sem_create_data["sem_admission"]) {
		$sem_create_data["sem_sec_lese"]=$sem_sec_lese;
		$sem_create_data["sem_sec_schreib"]=$sem_sec_schreib;
	} else {
		$sem_create_data["sem_sec_lese"]=3;
		$sem_create_data["sem_sec_schreib"]=3;
	}
	$sem_create_data["sem_status"]=$sem_status;
	$sem_create_data["sem_art"]=$sem_art;
	}

if ($form==3)
	{
	$sem_create_data["term_start_woche"]=$term_start_woche;
	$sem_create_data["term_turnus"]=$term_turnus;	
	$sem_create_data["sem_start_time"]=$sem_start_time;
	$sem_create_data["sem_vor_raum"]=$vor_raum;

	if (($sem_duration_time == 0) || ($sem_duration_time == -1))
		$sem_create_data["sem_duration_time"]=$sem_duration_time;
	else
		$sem_create_data["sem_duration_time"]=$sem_duration_time - $sem_start_time;

	if ($sem_create_data["term_art"]==0)
		{
		//Arrays fuer Turnus loeschen
		$sem_create_data["term_turnus_date"]='';
		$sem_create_data["term_turnus_start_stunde"]='';
		$sem_create_data["term_turnus_start_minute"]='';
		$sem_create_data["term_turnus_end_stunde"]='';
		$sem_create_data["term_turnus_end_minute"]='';
	
		//Alle eingegebenen Turnus-Daten in Sessionvariable uebernehmen
		for ($i=0; $i<$sem_create_data["turnus_count"]; $i++) {
			$sem_create_data["term_turnus_date"][$i]=$term_turnus_date[$i]; 
			$sem_create_data["term_turnus_start_stunde"][$i]=$term_turnus_start_stunde[$i];
			$sem_create_data["term_turnus_start_minute"][$i]=$term_turnus_start_minute[$i]; 
			$sem_create_data["term_turnus_end_stunde"][$i]=$term_turnus_end_stunde[$i]; 
			$sem_create_data["term_turnus_end_minute"][$i]=$term_turnus_end_minute[$i]; 

			//diese Umwandlung muessen hier passieren, damit Werte mit fuehrender Null nicht als String abgelegt werden und so spaeter Verwirrung stiften
			settype($sem_create_data["term_turnus_start_stunde"][$i], "integer");
			settype($sem_create_data["term_turnus_start_minute"][$i], "integer");
			settype($sem_create_data["term_turnus_end_stunde"][$i], "integer");
			settype($sem_create_data["term_turnus_end_minute"][$i], "integer");
		}
		
		//Turnus-Metadaten-Array erzeugen
		$sem_create_data["metadata_termin"]='';
		$sem_create_data["metadata_termin"]["art"]=$sem_create_data["term_art"];
		$sem_create_data["metadata_termin"]["start_termin"]=$sem_create_data["sem_start_termin"];
		$sem_create_data["metadata_termin"]["start_woche"]=$sem_create_data["term_start_woche"];
		$sem_create_data["metadata_termin"]["turnus"]=$sem_create_data["term_turnus"];
		
		//indizierte (=sortierbares Temporaeres Array erzeugen)
		if ($sem_create_data["term_art"] == 0)
			{
			for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
				if (($sem_create_data["term_turnus_start_stunde"][$i])  && ($sem_create_data["term_turnus_end_stunde"][$i])) {
					//Index erzeugen
					$tmp_idx=$sem_create_data["term_turnus_date"][$i];
					if ($sem_create_data["term_turnus_start_stunde"][$i] < 10)
						$tmp_idx.="0";
					$tmp_idx.=$sem_create_data["term_turnus_start_stunde"][$i];
					if ($sem_create_data["term_turnus_start_minute"][$i] < 10)
						$tmp_idx.="0";
					$tmp_idx.=$sem_create_data["term_turnus_start_minute"][$i];						
						
					$tmp_metadata_termin["turnus_data"][]=array("idx"=>$tmp_idx, "day" => $sem_create_data["term_turnus_date"][$i], "start_stunde" => $sem_create_data["term_turnus_start_stunde"][$i], "start_minute" => $sem_create_data["term_turnus_start_minute"][$i], "end_stunde" => $sem_create_data["term_turnus_end_stunde"][$i], "end_minute" => $sem_create_data["term_turnus_end_minute"][$i]);
				}	
			if (is_array($tmp_metadata_termin["turnus_data"])) {

				//sortieren
				sort ($tmp_metadata_termin["turnus_data"]);
			
				foreach ($tmp_metadata_termin["turnus_data"] as $tmp_array)
					{
					$sem_create_data["metadata_termin"]["turnus_data"][]=$tmp_array;
					}
				}
			}
		}
	else
		{
		//Arrays fuer Termine loeschen
		$sem_create_data["term_tag"]='';
		$sem_create_data["term_monat"]='';
		$sem_create_data["term_jahr"]='';
		$sem_create_data["term_start_stunde"]='';
		$sem_create_data["term_start_minute"]='';
		$sem_create_data["term_end_stunde"]='';
		$sem_create_data["term_end_minute"]='';
		$sem_create_data["term_first_date"]='';
		
	
		//Alle eingegebenen Termin-Daten in Sessionvariable uebernehmen
		for ($i=0; $i<$sem_create_data["term_count"]; $i++) {
			$sem_create_data["term_tag"][$i]=$term_tag[$i];
			$sem_create_data["term_monat"][$i]=$term_monat[$i];
			$sem_create_data["term_jahr"][$i]=$term_jahr[$i];
			$sem_create_data["term_start_stunde"][$i]=$term_start_stunde[$i];
			$sem_create_data["term_start_minute"][$i]=$term_start_minute[$i];
			$sem_create_data["term_end_stunde"][$i]=$term_end_stunde[$i]; 
			$sem_create_data["term_end_minute"][$i]=$term_end_minute[$i]; 

			//diese Umwandlung muss hier sein, da fuehrende 0 bei Minutenangaben sonst nur Verwirrung stiftet...
			settype($sem_create_data["term_start_stunde"][$i], "integer");
			settype($sem_create_data["term_start_minute"][$i], "integer");
			settype($sem_create_data["term_end_stunde"][$i], "integer");
			settype($sem_create_data["term_end_minute"][$i], "integer");
		
			//erster Termin wird gepeichert, wird fuer spaetere Checks benoetigt
			if ((($sem_create_data["term_first_date"] == 0) 
				|| ($sem_create_data["term_first_date"] >mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]))) 
				&& (mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]) > 0)) {
				$sem_create_data["term_first_date"]=mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
			}
		}
	}

	//Datum fuer Vobesprechung umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
	if (($vor_jahr>0) && ($vor_jahr<100))
		 $vor_jahr=$vor_jahr+2000;

	if ($vor_monat == "mm") $vor_monat=0;
	if ($vor_tag == "tt") $vor_tag=0;
	if ($vor_jahr == "jjjj") $vor_jahr=0;	
	if ($vor_stunde == "hh") $vor_stunde=0;
	if ($vor_minute == "mm") $vor_minute=0;
	if ($vor_end_stunde == "hh") $vor_end_stunde=0;
	if ($vor_end_minute == "mm") $vor_end_minute=0;
	
	if (($vor_monat) && ($vor_tag) && ($vor_jahr))
		if (($vor_stunde=="hh") && ($vor_end_stunde=="hh")) {
			$errormsg=$errormsg."error§Bitte geben Sie g&uuml;ltige Werte f&uuml;r Start- und Endzeit der Vorbesprechung ein!§"; 
			$check=FALSE;
		} else
			$check=TRUE;

	settype($vor_stunde, "integer");
	settype($vor_minute, "integer");
	settype($vor_end_stunde, "integer");
	settype($vor_end_minute, "integer");

	if ((!checkdate($vor_monat, $vor_tag, $vor_jahr) && ($vor_monat) && ($vor_tag) && ($vor_jahr)) && ($check)) {
		$errormsg=$errormsg."error§Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r die Vorbesprechung ein!§";
		$check=FALSE;			
	} else
		$check=TRUE;

	if ((($vor_stunde > 24) || ($vor_end_stunde > 24) || ($vor_minute > 59) || ($vor_end_minute > 60)) && ($check)) {
		$errormsg=$errormsg."error§Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r die Vorbesprechung ein!§";
		$check=FALSE;			
	} else
		$check=TRUE;

	if ($check) {
	 	$sem_create_data["sem_vor_termin"] = mktime($vor_stunde,$vor_minute,0,$vor_monat,$vor_tag,$vor_jahr);
		$sem_create_data["sem_vor_end_termin"] = mktime($vor_end_stunde,$vor_end_minute,0,$vor_monat,$vor_tag,$vor_jahr);
	} else {
		$sem_create_data["sem_vor_termin"] = -1;
		$sem_create_data["sem_vor_end_termin"] = -1;
	}
}

if ($form==4)
	{
	$sem_create_data["sem_teiln"]=$sem_teiln;
	$sem_create_data["sem_voraus"]=$sem_voraus;
	$sem_create_data["sem_orga"]=$sem_orga;
	$sem_create_data["sem_leistnw"]=$sem_leistnw;
	$sem_create_data["sem_sonst"]=$sem_sonst;
	
	//Hat der User an den automatischen Werte rumgefuscht? Dann denkt er sich wohl was :) (und wir benutzen die Automatik spaeter nicht!)
	if ($sem_all_ratio_old != $sem_all_ratio) {
		$sem_create_data["sem_admission_ratios_changed"]=TRUE;
		$sem_create_data["sem_all_ratio"]=$sem_all_ratio;
	}
	
	//Studienbereiche entgegennehmen
	if (is_array($sem_studg_id)) {
		foreach ($sem_studg_id as $key=>$val)
			if ($sem_studg_ratio_old[$key] != $sem_studg_ratio[$key])
				$sem_create_data["sem_admission_ratios_changed"]=TRUE;
		if ($sem_create_data["sem_admission_ratios_changed"]) {
			$sem_create_data["sem_studg"]='';
			foreach ($sem_studg_id as $key=>$val)
				$sem_create_data["sem_studg"][$val]=array("name"=>$sem_studg_name[$key], "ratio"=>$sem_studg_ratio[$key]);
		}
	}	
	
	//Datum fuer Ende der Anmeldung umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
	if ($sem_create_data["sem_admission"] == 1)
		$end_date_name="Losdatum";
	else
		$end_date_name="Enddatum der Kontingentierung";		
	
	if (($adm_jahr>0) && ($adm_jahr<100))
		 $adm_jahr=$adm_jahr+2000;
	
	if ($adm_monat == "mm") $adm_monat=0;
	if ($adm_tag == "tt") $adm_tag=0;
	if ($adm_jahr == "jjjj") $adm_jahr=0;	
	if ($adm_stunde == "hh") $adm_stunde=0;
	if ($adm_minute == "mm") $adm_minute=0;
	

	if (($adm_monat) && ($adm_tag) && ($adm_jahr))
		if ($adm_stunde=="hh")
			{
			$errormsg=$errormsg."error§Bitte geben Sie g&uuml;ltige Werte f&uuml;r das $end_date_name ein!§"; 
			$check=FALSE;
			}
		else
			$check=TRUE;

	settype($adm_stunde, "integer");
	settype($adm_minute, "integer");

	if ((!checkdate($adm_monat, $adm_tag, $adm_jahr) && ($adm_monat) && ($adm_tag) && ($adm_jahr)) && ($check)) {
		$errormsg=$errormsg."error§Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r das $end_date_name ein!§";
		$check=FALSE;			
	} else
		$check=TRUE;

	if ((($adm_stunde > 24) || ($adm_minute > 59)) && ($check)) {
		$errormsg=$errormsg."error§Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das $end_date_name ein!§";
		$check=FALSE;			
	} else
		$check=TRUE;

	if ($check)
		{
 		$sem_create_data["sem_admission_date"] = mktime($adm_stunde,$adm_minute,59,$adm_monat,$adm_tag,$adm_jahr);
		}
	else
		{
		$sem_create_data["sem_admission_date"] = -1;
		}
	}
		
	//Datum fuer ersten Termin umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
	if (($jahr>0) && ($jahr<100))
		 $jahr=$jahr+2000;

	if ($monat == "mm") $monat=0;
	if ($tag == "tt") $tag=0;
	if ($jahr == "jjjj") $jahr=0;	

	if (!checkdate($monat, $tag, $jahr) && ($monat) && ($tag) && ($jahr))
		{
		$errormsg=$errormsg."error§Bitte geben Sie ein g&uuml;ltiges Datum ein!§";
		$check=FALSE;			
		}
	else
		$check=TRUE;

	if ($check)
	 	$sem_create_data["sem_start_termin"] = mktime($stunde,$minute,0,$monat,$tag,$jahr);
	else
		$sem_create_data["sem_start_termin"] = -1;

if ($form==7)
	{
	$sem_create_data["sem_literat"]=$sem_literat;
	$sem_create_data["sem_links"]=$sem_links;
	}

//Check auf korrekte Eingabe und Sprung in naechste Level, hier auf Schritt 2
if ($cmd_b_x)
	{
	if (strlen($sem_create_data["sem_name"]) <3)
		{
		$level=1; //wir bleiben auf der ersten Seite
		$errormsg=$errormsg."error§Bitte geben Sie einen g&uuml;ltigen Namen f&uuml;r die Veranstaltung ein!§";
		}
	if (!$sem_create_data["sem_inst_id"])
		{
		$level=1;
		$errormsg=$errormsg."error§Da Ihr Account keiner Einrichtung zugeordnet ist, k&ouml;nnen Sie leider noch keine Veranstaltung anlegen. Bitte wenden Sie sich an den zust&auml;ndigen Administrator der Einrichtung oder einen der <a href=\"impressum.php\">Entwickler</a>!§";
		}
	if (($sem_create_data["sem_turnout"] < 1) && ($sem_create_data["sem_admission"]))
 		{
		$level=1;
		$errormsg=$errormsg."error§Wenn Sie sie die Teilnahmebeschr&auml;nkung benutzen wollen, m&uuml;ssen sie wenigstens einen Teilnehmer zulassen.§";
		$sem_create_data["sem_turnout"] =1;
		}
	
	if (!$errormsg)
		$level=2;
	}


//wenn alles stimmt, Sprung auf Schritt 3
if ($cmd_c_x)
	{
	if (is_array($sem_create_data["sem_doz"]))
		foreach ($sem_create_data["sem_doz"] as $a)
			$temp_sem_create_check_dozent[$a]=TRUE;
	if (is_array($sem_create_data["sem_tut"]))
		foreach ($sem_create_data["sem_tut"] as $a)
			if ($temp_sem_create_check_dozent[$a])
				$badly_dozent_is_tutor=TRUE;
	if ($badly_dozent_is_tutor) {
		$level=2; //wir bleiben auf der zweiten Seite
		$errormsg=$errormsg."error§Sie d&uuml;rfen die selben DozentInnen nicht gleichzeitig als TutorInnen eintragen!!§";
	}
	
 	if (sizeof($sem_create_data["sem_doz"])==0)
		{
		$level=2; //wir bleiben auf der zweiten Seite
		$errormsg=$errormsg."error§Bitte geben Sie mindestens einen Dozent oder eine Dozentin f&uuml;r die Veranstaltung an!§";
		}
	elseif ((!$perm->have_perm("root")) && (!$perm->have_perm("admin")))
		{
		$self_in=false;
		foreach ($sem_create_data["sem_doz"] as $tmp_array)
			if ($tmp_array == $user_id)
				$self_in=true;
		if (!$self_in)
			{
			$level=2;
			$errormsg=$errormsg."error§Sie m&uuml;ssen wenigstens sich selbst als DozentIn f&uuml;r diese Veranstaltung angeben! Der Eintrag wird automatisch gesetzt.§";
			}
		}
	if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"]) {
		if (sizeof($sem_create_data["sem_bereich"])==0)
			{
			$level=2;
			$errormsg=$errormsg."error§Bitte geben Sie mindestens einen Studienbereich f&uuml;r die Veranstaltung an!§";
			}
		else
			{
			$false_mark=false;
			foreach ($sem_create_data["sem_bereich"] as $tmp_array)
				if ($tmp_array == "nix")
					$false_mark=true;
			if ($false_mark)
				{
				$level=2;
				$errormsg=$errormsg."error§Sie haben eine oder mehrere Fach&uuml;berschriften (rot bzw. innerhalb der Linien dargestellt) ausgew&auml;hlt. Diese dienen nur der Orientierung und k&ouml;nnen nicht ausgew&auml;hlt werden!§";
				}
			}
		}
	if (($sem_create_data["sem_sec_schreib"]) <($sem_create_data["sem_sec_lese"]))
		{
		$level=2; //wir bleiben auf der zweiten Seite
		$errormsg=$errormsg."error§Es macht keinen Sinn, die Sicherheitsstufe f&uuml;r den Lesezugriff h&ouml;her zu setzen als f&uuml;r den Schreibzugriff!§";
		}
	if (!$errormsg)
		$level=3;
	else
		$level=2;
	}

//Felder fuer Standardtermine oder Blocktermine, Studiengaenge hinzufuegen/loeschen
if ($add_turnus_field_x)
	{
	$sem_create_data["turnus_count"]++;
	$level=3;
	}
if ($add_term_field_x)
	{
	$sem_create_data["term_count"]++;
	$level=3;
	}	
if ($delete_turnus_field)
	{
	for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
		if ($i != ($delete_turnus_field-1))
			{
			$temp_term_turnus_date[]=$sem_create_data["term_turnus_date"][$i];
			$tmp_term_turnus_start_stunde[]=$sem_create_data["term_turnus_start_stunde"][$i];
			$tmp_term_turnus_start_minute[]=$sem_create_data["term_turnus_start_minute"][$i]; 
			$tmp_term_turnus_end_stunde[]=$sem_create_data["term_turnus_end_stunde"][$i]; 
			$tmp_term_turnus_end_minute[]=$sem_create_data["term_turnus_end_minute"][$i]; 
			}
	$sem_create_data["term_turnus_date"]=$temp_term_turnus_date;
	$sem_create_data["term_turnus_start_stunde"]=$tmp_term_turnus_start_stunde;
	$sem_create_data["term_turnus_start_minute"]=$tmp_term_turnus_start_minute;
	$sem_create_data["term_turnus_end_stunde"]=$tmp_term_turnus_end_stunde;
	$sem_create_data["term_turnus_end_minute"]=$tmp_term_turnus_end_minute;
	
	$sem_create_data["turnus_count"]--;
	$level=3;
	}
if ($delete_term_field)
	{
	for ($i=0; $i<$sem_create_data["term_count"]; $i++)
		if ($i != ($delete_term_field-1))
			{
			$tmp_term_tag[]=$sem_create_data["term_tag"][$i];
			$tmp_term_monat[]=$sem_create_data["term_monat"][$i];
			$tmp_term_jahr[]=$sem_create_data["term_jahr"][$i];
			$tmp_term_start_stunde[]=$sem_create_data["term_start_stunde"][$i];
			$tmp_term_start_minute[]=$sem_create_data["term_start_minute"][$i]; 
			$tmp_term_end_stunde[]=$sem_create_data["term_end_stunde"][$i]; 
			$tmp_term_end_minute[]=$sem_create_data["term_end_minute"][$i]; 
			}
	$sem_create_data["term_tag"]=$tmp_term_tag;
	$sem_create_data["term_monat"]=$tmp_term_monat;
	$sem_create_data["term_jahr"]=$tmp_term_jahr;
	$sem_create_data["term_start_stunde"]=$tmp_term_start_stunde;
	$sem_create_data["term_start_minute"]=$tmp_term_start_minute;
	$sem_create_data["term_end_stunde"]=$tmp_term_end_stunde;
	$sem_create_data["term_end_minute"]=$tmp_term_end_minute;
	
	$sem_create_data["term_count"]--;
	$level=3;
	}


//Termin-Metaddaten-Check, wenn alles stimmt, Sprung auf Schritt 4
if ($cmd_d_x)
	{
	if (($sem_create_data["sem_duration_time"]<0) && ($sem_create_data["sem_duration_time"] != -1))
		{ 
		$level=3;
		$errormsg=$errormsg."error§Das Endsemester darf nicht vor dem Startsemester liegen, bitte &auml;ndern Sie die entsprechenden Einstellungen!§";
		}

	if ($sem_create_data["term_art"]==0)
		{
		for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
			if ((($sem_create_data["term_turnus_start_stunde"][$i]) || ($sem_create_data["term_turnus_end_stunde"][$i])))
				{
				if ((($sem_create_data["term_turnus_start_stunde"][$i]) && (!$sem_create_data["term_turnus_end_stunde"][$i])) || ((!$sem_create_data["term_turnus_start_stunde"][$i]) && ($sem_create_data["term_turnus_end_stunde"][$i])))
						{
						if (!$just_informed)
							$errormsg=$errormsg."error§Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der regul&auml;ren Termine aus!§";	
						$just_informed=TRUE;
						}
				if ((($sem_create_data["term_turnus_start_stunde"][$i]>23) || ($sem_create_data["term_turnus_start_stunde"][$i]<0))  ||  (($sem_create_data["term_turnus_start_minute"][$i]>59) || ($sem_create_data["term_turnus_start_minute"][$i]<0))  ||  (($sem_create_data["term_turnus_end_stunde"][$i]>23) ||($sem_create_data["term_turnus_end_stunde"][$i]<0))  || (($sem_create_data["term_turnus_end_minute"][$i]>59) || ($sem_create_data["term_turnus_end_minute"][$i]<0)))
						{
						if (!$just_informed3)
							$errormsg=$errormsg."error§Sie haben eine ung&uuml;ltige Zeit eingegeben, bitte korrigieren sie dies!$";	
						$just_informed3=TRUE;
						}
				if (mktime($sem_create_data["term_turnus_start_stunde"][$i], $sem_create_data["term_turnus_start_minute"][$i], 0, 1, 1, 2001) > mktime($sem_create_data["term_turnus_end_stunde"][$i], $sem_create_data["term_turnus_end_minute"][$i], 0, 1, 1, 2001)) 
					if ((!$just_informed5) && (!$just_informed)) {
						$errormsg=$errormsg."error§Die jeweilige Endzeitpunkt der regul&auml;ren Termine muss nach dem jeweiligen Startzeitpunkt liegen!§";
						$just_informed5=TRUE;				
					}
				}
				elseif(!$just_informed4) 
					if ((!$sem_create_data["term_turnus_start_stunde"][$i]) && (!$sem_create_data["term_turnus_start_minute"][$i]) && (!$sem_create_data["term_turnus_end_stunde"][$i]) && (!$sem_create_data["term_turnus_end_minute"][$i]))
						$empty_fields++;
					else
						{
						$errormsg=$errormsg."error§Sie haben nicht alle Felder der regul&auml;ren Termine ausgef&uuml;llt, bitte korrigieren sie dies!§";
						$just_informed4=TRUE;
						}
		}
	else {
		for ($i=0; $i<$sem_create_data["term_count"]; $i++)
			if ((($sem_create_data["term_start_stunde"][$i]) || ($sem_create_data["term_end_stunde"][$i])) && (($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_jahr"][$i])))
				{
				if ((($sem_create_data["term_start_stunde"][$i]) && (!$sem_create_data["term_end_stunde"][$i])) || ((!$sem_create_data["term_start_stunde"][$i]) && ($sem_create_data["term_end_stunde"][$i])))
						{
						if (!$just_informed)
							$errormsg=$errormsg."error§Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der jeweiligen Termine aus!§";	
						$just_informed=TRUE;
						}
				if (!checkdate ($sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]))
						{
						if (!$just_informed2)
							$errormsg=$errormsg."error§Sie haben ein ung&uuml;ltiges Datum eingegeben, bitte korrigieren sie dies!§";
						$just_informed2=TRUE;
						}
				if ((($sem_create_data["term_start_stunde"][$i]>23) || ($sem_create_data["term_start_stunde"][$i]<0))  ||  (($sem_create_data["term_start_minute"][$i]>59) || ($sem_create_data["term_start_minute"][$i]<0))  ||  (($sem_create_data["term_end_stunde"][$i]>23) ||($sem_create_data["term_end_stunde"][$i]<0))  || (($sem_create_data["term_end_minute"][$i]>59) || ($sem_create_data["term_end_minute"][$i]<0)))
						{
						if (!$just_informed3)
							$errormsg=$errormsg."error§Sie haben eine ung&uuml;ltige Zeit eingegeben, bitte korrigieren sie dies!§";	
						$just_informed3=TRUE;
						}
				if (mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, 1, 1, 2001) > mktime($sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i], 0, 1, 1, 2001)) 
					if ((!$just_informed5) && (!$just_informed)) {
						$errormsg=$errormsg."error§Die jeweilige Endzeitpunkt der Termine muss nach dem jeweiligen Startzeitpunkt liegen!§";
						$just_informed5=TRUE;				
					}
				}
				elseif(!$just_informed4) 
					if ((!$sem_create_data["term_tag"][$i]) && (!$sem_create_data["term_monat"][$i]) && (!$sem_create_data["term_jahr"][$i]) && (!$sem_create_data["term_start_stunde"][$i]) && (!$sem_create_data["term_start_minute"][$i]) && (!$sem_create_data["term_end_stunde"][$i]) && (!$sem_create_data["term_end_minute"][$i]))
						$empty_fields++;
					else
						{
						$errormsg=$errormsg."error§Sie haben nicht alle Felder bei der Termineingabe ausgef&uuml;llt, bitte korrigieren sie dies!§";
						$just_informed4=TRUE;
						}

	} 
	if ($sem_create_data["sem_vor_termin"] == -1);
	else
		if ((($vor_stunde) && (!$vor_end_stunde)) || ((!$vor_stunde) && ($vor_end_stunde)))
			$errormsg=$errormsg."error§Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der Vorbesprechung aus!§";	
	
	if (!$errormsg)
		$level=4;
	else
		$level=3;
	}

//Neuen Studiengang zur Begrenzung aufnehmen
if ($add_studg_x) {
	if ($sem_add_studg) {
		$db->query("SELECT name FROM studiengaenge WHERE studiengang_id='".$sem_add_studg."' ");
		$db->next_record();
		$sem_create_data["sem_studg"][$sem_add_studg]=array("name"=>$db->f("name"), "ratio"=>$sem_add_ratio);
	}
	$level=4;
}

//Studiengang zur Begrenzung loeschen
if ($sem_delete_studg) {
	unset($sem_create_data["sem_studg"][$sem_delete_studg]);
	$level=4;
	}

//Prozentangabe checken/berechnen wenn neueer Studiengang, einer geloescht oder Seite abgeschickt
if (($cmd_e_x) || ($add_studg_x) || ($sem_delete_studg)) {
	if ($sem_create_data["sem_admission"]) {
		if ((!$sem_create_data["sem_admission_ratios_changed"]) && (!$sem_add_ratio) && (!cmd_c_x) && (!cmd_e_x)) {//User hat nichts veraendert oder neuen Studiengang mit Wert geschickt, wir koennen automatisch rechnen
			if (is_array($sem_create_data["sem_studg"]))
				foreach ($sem_create_data["sem_studg"] as $key=>$val)
					$sem_create_data["sem_studg"][$key]["ratio"]=round(100 / (sizeof ($sem_create_data["sem_studg"]) + 1));
			$sem_create_data["sem_all_ratio"]=100 - (sizeof ($sem_create_data["sem_studg"])) * round(100 / (sizeof ($sem_create_data["sem_studg"]) + 1));
		} else {
			$cnt=0;
			if (is_array($sem_create_data["sem_studg"]))
				foreach ($sem_create_data["sem_studg"] as $val)
					$cnt+=$val["ratio"];
			if (($cnt + $sem_create_data["sem_all_ratio"]) < 100)
				$sem_create_data["sem_all_ratio"]=100 - $cnt;
			if (($cnt + $sem_create_data["sem_all_ratio"]) > 100)
				if ($cnt <= 100)
					$sem_create_data["sem_all_ratio"]=(100 - $cnt);
				else {
					$errormsg.=sprintf ("error§Die Werte der einzelnen Kontigente &uuml;bersteigen 100%%. Bitte &auml;ndern Sie die Kontigente!§");	
					$level=4;
				}
		}
	}
}

//wenn alles stimmt, Sprung auf Schritt 5 (Anlegen)
if ($cmd_e_x)
	{
	if (($sem_create_data["sem_sec_lese"] ==2) ||  ($sem_create_data["sem_sec_schreib"] ==2))
		{
          	//Password bei Bedarf dann doch noch verschlusseln
		if (empty($hashpass)) // javascript disabled
			{											
     	   		if (!$password)
          			$sem_create_data["sem_pw"] = "";
     			elseif($password != "*******")
     				{
    				$sem_create_data["sem_pw"] = md5($password);
	     			if($password2 != "*******")
    					$check_pw = md5($password2);
    				}
     			}
		elseif ($hashpass != md5("*******")) // javascript enabled
	       		{
       			$sem_create_data["sem_pw"]= $hashpass;
       			$check_pw = $hashpass2;
       			}

		if ($sem_create_data["sem_pw"]=="")
	          	{
        	  	$errormsg=$errormsg."error§Sie haben kein Passwort eingegeben! Bitte geben Sie ein Passwort ein!§";
          		$level=4;
	          	}
        	  elseif (isset($check_pw) AND $sem_create_data["sem_pw"] != $check_pw)
     			{
			$errormsg=$errormsg."error§Das eingegebene Passwort und das Wiederholungspasswort stimmen nicht &uuml;berein!§";
     			$sem_create_data["sem_pw"] = "";
     			$level=4;
	          	}
	}
	
	//Ende der Anmeldung checken
	if ($sem_create_data["sem_admission"]) {
		if ($sem_create_data["sem_admission"] == 1)
			$end_date_name="Losdatum";
		else
			$end_date_name="Enddatum der Kontingentierung";		
		if ($sem_create_data["sem_admission_date"] == -1) 
			$errormsg.="error§Bitte geben Sie einen Termin f&uuml;r das $end_date_name an!§";	
		elseif ($sem_create_data["term_art"]==0) {
			$tmp_first_date=veranstaltung_beginn ($sem_create_data["term_art"], $sem_create_data["sem_start_time"], $sem_create_data["term_start_woche"], $sem_create_data["sem_start_termin"], $sem_create_data["metadata_termin"]["turnus_data"], "int");
			if ($sem_create_data["sem_admission_date"] > $tmp_first_date) {
				if ($tmp_first_date > 0)
					$errormsg.= sprintf ("error§Das $end_date_name liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern sie das $end_date_name!§", date ("d.m.Y", $tmp_first_date));
				$level=4;
			}
		} elseif ($sem_create_data["sem_admission_date"] > $sem_create_data["term_first_date"]) {
				$errormsg.=sprintf ("error§Das $end_date_name liegt nach dem eingetragenen Veranstaltungsbeginn am %s. Bitte &auml;ndern sie das $end_date_name!§", date ("d.m.Y", $sem_create_data["term_first_date"]));	
				$level=4;
		} 
		if (($sem_create_data["sem_admission_date"] < time()) && ($sem_create_data["sem_admission_date"] != -1)) {
				$errormsg.=sprintf ("error§Das $end_date_name liegt in der Vergangenheit. Bitte &auml;ndern sie das $end_date_name!§");	
				$level=4;
		} elseif (($sem_create_data["sem_admission_date"] < (time() + (24 * 60 *60))) && ($sem_create_data["sem_admission_date"] != -1)) {
				$errormsg.=sprintf ("error§Das $end_date_name liegt zu nah am aktuellen Datum. Bitte &auml;ndern sie das $end_date_name!§");	
				$level=4;
		}
	}

	//Erster Termin wenn angegeben werden soll muss er auch da sein
	if (($sem_create_data["sem_start_termin"] == -1) && ($sem_create_data["term_start_woche"] ==-1))
		$errormsg=$errormsg."error§Bitte geben Sie einen ersten Termin an!§";	
	else
		if ((($stunde) && (!$end_stunde)) || ((!$stunde) && ($end_stunde)))
			$errormsg=$errormsg."error§Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit des ersten Termins aus!§";	

	if (!$errormsg)
		$level=5;
	else
		$level=4;
	}
	
//OK, nun wird es ernst, wir legen das Seminar an.
if ($cmd_f_x)
	{
	$run = TRUE;

	//Rechte ueberpruefen
	if (!$perm->have_perm("dozent"))
		{
		$errormsg .= "error§Sie haben keine Berechtigung Veranstaltungen anzulegen Um eine Veranstaltung anlegen zu k&ouml;nnen, ben&ouml;tigen Sie mindestens den globalen Status \"Dozent\". Bitte kontaktieren Sie den f&uuml;r Sie zust&auml;ndigen Administrator.§";
		$run = FALSE;
		}
	if (!$perm->have_perm("root"))
		{
		$db3->query("SELECT * FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (user_inst.Institut_id = '".$sem_create_data["sem_inst_id"]."' AND user_id = '$user_id') AND (inst_perms = 'dozent' OR inst_perms = 'admin')");
		if (!$db3->next_record())
			{
      			$errormsg .= "error§Sie haben keine Berechtigung f&uuml;r diese Einrichtung Veranstaltungen anzulegen.§";
      			$run = FALSE;
			}
    		}

	//Nochmal Checken, ob wirklich alle Daten vorliegen. Kann zwar eigentlich hier nicht mehr vorkommen, aber sicher ist sicher.
	if (empty($sem_create_data["sem_name"]))
		{
		$errormsg  .= "error§Bitte geben Sie einen Namen f&uuml;r die Veranstaltung ein!§";
		$run = FALSE;
    		}

	if (empty($sem_create_data["sem_inst_id"]))
		{
		$errormsg .= "error§Bitte geben Sie ein Heimat-Einrichtung f&uuml;r die Veranstaltung an!§";
		$run = FALSE;
		}
	if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])  {
		if (empty($sem_create_data["sem_bereich"]))
			{
			$errormsg .= "error§Bitte geben Sie wenigstens einen Studienbereich f&uuml;r die Veranstaltung an!§";
			$run = FALSE;
			}
		else
			{
			$dochnoch = FALSE;    // Test ob ausser Murks auch etwas Sinnvolles angeklickt wurde
			while (list($key,$val) = each($sem_create_data["sem_bereich"]))
				if ($val != "nix") $dochnoch = TRUE;
				if (!$dochnoch)
					{
					$errormsg .= "error§Sie haben nur einen ung&uuml;ltigen Studienbereich ausgew&auml;hlt. Bitte geben Sie wenigstens einen Studienbereich an!§";
	    	  			$run = FALSE;
					}
				reset ($sem_create_data["sem_bereich"]);
			}
		}

    	if ($perm->have_perm("admin") && empty($sem_create_data["sem_doz"]))
    		{
    		$errormsg .= "error§Bitte geben Sie wenigstens einen Dozenten f&uuml;r die Veranstaltung an!§";
      		$run = FALSE;
      		}

      	if ($run)
		{
		//Seminar_id erzeugen und Seminar eintrag
		$sem_create_data["sem_id"]=md5(uniqid($hash_secret));
    		
		//Termin-Metadaten-Array zusammenmatschen zum besseren speichern in der Datenbank
		$serialized_metadata=serialize($sem_create_data["metadata_termin"]);

		//for admission it have to always 3
		if ($sem_create_data["sem_admission"]) {
			$sem_create_data["sem_sec_lese"]=3;
			$sem_create_data["sem_sec_schreib"]=3;
		}

		if ($Schreibzugriff < $Lesezugriff) // hier wusste ein Dozent nicht, was er tat
			$Schreibzugriff = $Lesezugriff;
		
						
		$query = "insert into seminare values('".
				$sem_create_data["sem_id"]."', '".				//Feld Seminar_id 
				$sem_create_data["sem_nummer"]."', '".		//Feld VeranstaltungsNummer
				$sem_create_data["sem_inst_id"]."', '".			//Feld Institut_id 
				$sem_create_data["sem_name"]."', '".			//Feld Name 
				$sem_create_data["sem_untert"]."', '".			//Feld Untertitel 
				$sem_create_data["sem_status"]."', '".			//Feld status 
				$sem_create_data["sem_desc"]."', '".			//Feld Beschreibung 
				$sem_create_data["sem_ort"]."', '".			//Feld Ort 
				$sem_create_data["sem_sonst"]."', '".			//Feld Sonstiges
				$sem_create_data["sem_pw"]."', '".			//Feld Passwort 
				$sem_create_data["sem_sec_lese"]."', '".		//Feld Lesezugriff 
				$sem_create_data["sem_sec_schreib"]."', '".		//Feld Schreibzugriff
				$sem_create_data["sem_start_time"]."', '".		//Feld start_time 
				$sem_create_data["sem_duration_time"]."', '".	//Feld duration_time 
				$sem_create_data["sem_art"]."', '".			//Feld art 
				$sem_create_data["sem_teiln"]."', '".			//Feld teilnehmer 
				$sem_create_data["sem_voraus"]."', '".			//Feld vorrausetzungen 
				$sem_create_data["sem_orga"]."', '".			//Feld lernorga 
				$sem_create_data["sem_leistnw"]."', '".			//Feld leistungsnachweis 
				$serialized_metadata."', '".					//Feld metadata_dates 
				time()."', '".								//Feld mkdate 
				time()."', '".								//Feld chdate 
				$sem_create_data["sem_ects"]."', '".			//Feld ects
				$sem_create_data["sem_admission_date"]."', '".	//Feld admission_endtime 
				$sem_create_data["sem_turnout"]."', '".			//Feld admission_turnout 
				"', '".										//Feld admission_binding
				$sem_create_data["sem_admission"]."', '".		//Feld admission_type 
				"' )";										//Feld admission_selection_take_place

		//und jetzt wirklich eintragen
		if (!$sem_create_data["sem_entry"])
			{
			$db->query($query);
			if ($db->affected_rows() == 0)
				{
				$errormsg .= "error§<b>Fehler:</b> $query §";
				$successful_entry=0;
				$sem_create_data["sem_entry"]=FALSE;
				break;
    				}
	    		else
    				{
    				$successful_entry=1;
				$sem_create_data["sem_entry"]=TRUE;
				$links_admin_data["sem_id"]=$sem_create_data["sem_id"];
				}
			}
		else
			{
			$errormsg .= "error§<b>Fehler:</b> Die Veranstaltung wurde schon eingetragen!§";
    			$successful_entry=2;			
			}

		if (is_array($sem_create_data["sem_doz"]))  // alle ausgewählten Dozenten durchlaufen
			{
			$self_included = FALSE;
			$count_doz=0;
			foreach ($sem_create_data["sem_doz"] as $tmp_array)
				{
				$group=select_group($temp_array, $sem_create_data["sem_start_time"]);
				
				if ($tmp_array == $user_id)
					$self_included=TRUE;
				$query = "insert into seminar_user  values('".
					$sem_create_data["sem_id"]."', '".
					$tmp_array."', 'dozent', '$group', '', '".time()."')";
				$db3->query($query);// Dozenten eintragen
				if ($db3->affected_rows() >=1)
					$count_doz++;
				}
			}

		if (!$perm->have_perm("admin") && !$self_included) // wenn nicht admin, aktuellen Dozenten eintragen
			{
			$group=select_group($user_id, $sem_create_data["sem_start_time"]);

			$query = "insert into seminar_user  values('".
				$sem_create_data["sem_id"]."', '".
				$user_id."', 'dozent', '$group', '', '".time()."')";
			$db3->query($query);
			if ($db3->affected_rows() >=1)
				$count_doz++;
			}

		if (is_array($sem_create_data["sem_tut"]))  // alle ausgewählten Tutoren durchlaufen
			{
			$count_tut=0;
			foreach ($sem_create_data["sem_tut"] as $tmp_array)
				{
				$group=select_group($temp_array, $sem_create_data["sem_start_time"]);
				
				$query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '".
					$sem_create_data["sem_id"]."' AND user_id ='$tmp_array'";
				$db4->query($query);
				if ($db4->next_record())	// User schon da, kann beim Anlegen nur als Dozent sein, also ignorieren
					;
				else // User noch nicht da
					{
					$query = "insert into seminar_user  values('".
						$sem_create_data["sem_id"]."', '".
						$tmp_array."', 'tutor', '$group', '', '".time()."')";
					$db3->query($query);			     // Tutor eintragen
						if ($db3->affected_rows() >= 1)
							$count_tut++;
					}
				}
			}

		//Eintrag der Studienbereiche
		if (is_array($sem_create_data["sem_bereich"]))
			{
			$count_bereich=0;
			foreach ($sem_create_data["sem_bereich"] as $tmp_array)  // alle ausgewählten Bereiche durchlaufen
				{
				if ($tmp_array != "nix")
					{
					$query = "INSERT IGNORE INTO seminar_bereich VALUES('".$sem_create_data["sem_id"]."', '$tmp_array')";
					$db3->query($query);// Bereich eintragen
					if ($db3->affected_rows() >= 1)
						$count_bereich++;
					}
				}
			}
			
		//Eintrag der zugelassen Studienbereiche
		if ($sem_create_data["sem_admission"]) {
			if (is_array($sem_create_data["sem_studg"]))
				foreach($sem_create_data["sem_studg"] as $key=>$val)
					if ($val["ratio"]) {
						$query = "INSERT INTO admission_seminar_studiengang VALUES('".$sem_create_data["sem_id"]."', '$key', '".$val["ratio"]."' )";
						$db3->query($query);// Studiengang eintragen
					}
			if ($sem_create_data["sem_all_ratio"]) {
				$query = "INSERT INTO admission_seminar_studiengang VALUES('".$sem_create_data["sem_id"]."', 'all', '".$sem_create_data["sem_all_ratio"]."' )";
				$db3->query($query);// Studiengang eintragen
			}
		}

		//Eintrag der beteiligten Institute
		if (is_array($sem_create_data["sem_bet_inst"])>0)
			{
			$count_bet_inst=0;
			foreach ($sem_create_data["sem_bet_inst"] as $tmp_array) //Alle beteiligten Institute durchlaufen
				{
				$query = "INSERT INTO seminar_inst VALUES('".$sem_create_data["sem_id"]."', '$tmp_array')";
				$db3->query($query);// Institut eintragen
				if ($db3->affected_rows() >= 1)
					$count_bet_inst++;
				}
			}

		//Heimat-Institut ebenfalls eintragen, wenn noch nicht da
		$query = "INSERT IGNORE INTO seminar_inst values('".$sem_create_data["sem_id"]."', '".$sem_create_data["sem_inst_id"]."')";
		$db3->query($query);

		//Standard Thema im Forum anlegen, damit Studis auch ohne Zutun des Dozenten diskutieren koennen
		$db->query("SELECT Vorname, Nachname FROM auth_user_md5 WHERE user_id = '$user_id'");
		$db->next_record();
		CreateTopic('Allgemeine Diskussionen', $db->f("Vorname")." ".$db->f("Nachname"), 'Hier ist Raum für allgemeine Diskussionen', 0, 0, $sem_create_data["sem_id"]);
		
		//Standard Ordner im Foldersystem anlegen, damit Studis auch ohne Zutun des Dozenten Uploaden k&ouml;nnen
		$db3->query("INSERT INTO folder SET folder_id='".md5(uniqid(rand()))."', range_id='".$sem_create_data["sem_id"]."', user_id='".$user_id."', name='Allgemeiner Dateiordner', description='Ablage für allgemeine Ordner und Dokumente der Veranstaltung', mkdate='".time()."', chdate='".time()."'");
		
		//Vorbesprechung, falls vorhanden, in Termintabelle eintragen
		if ($sem_create_data["sem_vor_termin"] <>-1)
			{
			$termin_id=md5(uniqid($hash_secret));
			$mkdate=time();		
			$db->query("INSERT INTO termine SET termin_id = '$termin_id', range_id='".$sem_create_data["sem_id"]."', autor_id='$user_id', content ='Vorbesprechung', date='".$sem_create_data["sem_vor_termin"]."', mkdate='$mkdate', chdate='$mkdate', date_typ='2', topic_id=0, end_time='".$sem_create_data["sem_vor_end_termin"]."', raum='".$sem_create_data["sem_vor_raum"]."'");
			}
		
		//Wenn der Veranstaltungs-Termintyp Blockseminar ist, dann tragen wir diese Termine auch schon mal ein
		if ($sem_create_data["term_art"] ==1)
			{
			for ($i=0; $i<$sem_create_data["term_count"]; $i++)
				if (($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_jahr"][$i]) && ($sem_create_data["term_start_stunde"][$i]) && ($sem_create_data["term_end_stunde"][$i]))
					{
					$termin_id=md5(uniqid($hash_secret));
					$mkdate=time();
					$date=mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
					$end_time=mktime($sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
					$db->query("INSERT INTO termine SET termin_id = '$termin_id', range_id='".$sem_create_data["sem_id"]."', autor_id='$user_id', content ='".($i+1).". Seminartermin (ohne Titel)', date='$date', mkdate='$mkdate', chdate='$mkdate', date_typ='1', topic_id=0, end_time='$end_time', raum=''");
					}
			}
		}

	$level=6;
	}

//Nur der Form halber... es geht weiter zur Literaturliste
if ($cmd_g_x)
   	{
	$level=7;
   	}

//Eintragen der Literatur und Links
if ($cmd_h_x)
	{
	if ($sem_create_data["lit_entry"])
		$db->query("UPDATE literatur SET literatur='".$sem_create_data["sem_literat"]."', links='".$sem_create_data["sem_links"]."', chdate='".time()."' WHERE literatur_id='".$sem_create_data["sem_lit_id"]."'");
	else
		{
		$sem_create_data["sem_lit_id"]=md5(uniqid($hash_secret));
		$db->query("INSERT INTO literatur SET literatur_id='".$sem_create_data["sem_lit_id"]."', range_id='".$sem_create_data["sem_id"]."', user_id='$user_id', literatur='".$sem_create_data["sem_literat"]."', links='".$sem_create_data["sem_links"]."', mkdate='".time()."', chdate='".time()."' ");
		}
	if ($db->affected_rows()) {
		$sem_create_data["lit_entry"]=TRUE;
		header ("Location: admin_dates.php?assi=yes&ebene=sem&range_id=".$sem_create_data["sem_id"]);
		break;
		}
	else
		{
		$errormsg .= "error§Fehler! Der Eintrag konnte nicht erfolgreich vorgenommen werden!";
		$level=7;
		}
	}

//Gibt den aktuellen View an, brauchen wir in der Hilfe
$sem_create_data["level"]=$level;

//ab hier folgen nun die eigentlichen Ausgaben der Seite
 ?>
<html>
	<head>
		<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
		<title>Stud.IP</title>
		<link rel="stylesheet" href="style.css" type="text/css">

		<script language="javascript" src="md5.js"></script>

		<script language="javascript">
		<!--
     		function doCrypt() {
				document.form_4.hashpass.value = MD5(document.form_4.password.value);
				document.form_4.hashpass2.value = MD5(document.form_4.password2.value);
				document.form_4.password.value = "";
				document.form_4.password2.value = "";
				return true;
				}
		
		// -->
		</script>
	 </head>
 <body>
 <?

//Includes...

include "$ABSOLUTE_PATH_STUDIP/header.php";   		//hier wird der "Kopf" nachgeladen
include "$ABSOLUTE_PATH_STUDIP/links_admin.inc.php";  		//Linkleiste fuer admins

//Level 1: Hier werden die Grunddaten abgefragt.
if ((!$level) || ($level==1))
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;Veranstaltungs-Assistent - Schritt 1: Grunddaten</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<tr>
			<td class="blank" valign="top">
				<blockquote>
				Willkommen beim Veranstaltungs-Assistenten. Der Veranstaltungs-Assistent wird Sie nun Schritt f&uuml;r Schritt durch die notwendigen Schritte zum Anlegen einer neuen Veranstaltung in Stud.IP leiten.<br><br>
				<b>Schritt 1: Grunddaten der Veranstaltung angeben</b><br><br />
				<font size=-1>Alle mit einem Sternchen&nbsp;</font><font color="red" size=+1><b>*</b></font><font size=-1>&nbsp;markierten Felder sind zwingend notwendig, um eine Veranstaltung anlegen zu k&ouml;nnen.</font><br><br>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="pictures/hands01.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
			<form method="POST" action="<? echo $PHP_SELF ?>">
			<input type="HIDDEN" name="form" value=1>
				<table cellspacing=0 cellpadding=2 border=0 width="99%" align="center">
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_b">
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Name der Veranstaltung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <input type="text" name="sem_name" size=58 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_name"])) ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte geben Sie hie einen aussagekräftigen aber möglichst knappen Titel für die Veranstaltung ein. Dieser Eintrag erscheint innerhalb des Systems durchgehend zur Identifikation der Veranstaltung.", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Untertitel:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <input type="text" name="sem_untert" size=58 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_untert"]))?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Der Untertitel ermöglicht eine genauere Beschreibung der Veranstaltung.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Kategorie:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; 
							<?
								$i=0;
								foreach ($SEM_CLASS as $a) {
									$i++;
									if ((($i==1) && (!$sem_create_data["sem_class"])) || ($sem_create_data["sem_class"] == $i))
										echo "<input type=\"RADIO\" name=\"sem_class\" checked value=\"".$a["name"]."\">", $a["name"], "&nbsp; ";
									else
										echo "<input type=\"RADIO\" name=\"sem_class\"  value=\"".$a["name"]."\">", $a["name"], "&nbsp; ";
									}
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Hier legen Sie fest, ob Sie eine Lehrveranstaltung anlegen möchten, oder ob die Veranstaltungen einer anderen Kategorie zugeordnet wird. Im Normalfall sollten sie eine Lehrveranstaltung anlegen.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Raum:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=1>
							&nbsp; <input type="text" name="sem_ort" size=20 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_ort"]))  ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Der Raum, in dem die Veranstaltung stattfindet", TRUE, TRUE) ?>
							>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Veranstaltungsnummer:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="60%">
							&nbsp; <input type="int" name="sem_nummer" size=6 maxlength=6 value="<? echo  htmlReady(stripslashes($sem_create_data["sem_nummer"])) ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Die von der Universität vergebene Veranstaltungsnummer.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Turnus:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=1>
							&nbsp; <select  name="term_art">
							<?
							if ($sem_create_data["term_art"] == 0) 
								echo "<option selected value=0>regelm&auml;&szlig;ig</option>";
							else
								echo "<option value=0>regelm&auml;&szlig;ig</option>>";
							if ($sem_create_data["term_art"] == 1) 
								echo "<option selected value=1>unregelm&auml;&szlig;ig oder Blockveranstaltung</option>";
							else
								echo "<option value=1>unregelm&auml;&szlig;ig oder Blockveranstaltung</option>";
											
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte wählen Sie hier aus, ob die Veranstaltung regelmässig stattfindet, oder ob die Sitzungen nur an bestimmten Terminen stattfinden (etwa bei einem Blockseminar)", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>									
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							ECTS-Punkte:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="60%">
							&nbsp; <input type="int" name="sem_ects" size=6 maxlength=32 value="<? echo  htmlReady(stripslashes($sem_create_data["sem_ects"])) ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip("ECTS-Kreditpunkte, die in dieser Veranstaltung vergeben werden.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Teilnehmer- begrenzung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=1>
 							&nbsp; <input type="RADIO" name="sem_admission" value=0 <? if (!$sem_create_data["sem_admission"]) echo checked?>>
 							keine &nbsp; <br />
							&nbsp; <input type="RADIO" name="sem_admission" value=2 <? if ($sem_create_data["sem_admission"]=="2") echo checked?>>
 							nach Anmeldereihenfolge <br />
 							&nbsp; <input type="RADIO" name="sem_admission" value=1 <? if ($sem_create_data["sem_admission"]=="1") echo checked?>>
 							per Losverfahren&nbsp; 
 							<img  src="./pictures/info.gif" 
 								<? echo tooltip("Sie können die Teilnhmezahl in der Reihenfolgen der Anmeldung chronologisch vornehmen oder das Losverfahren benutzen. Sie können später Angaben über zugelassene Teilnehmer machen.", TRUE, TRUE) ?>
							>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							maximale Teilnehmeranzahl:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="60%">
							&nbsp; <input type="int" name="sem_turnout" size=6 maxlength=5 value="<? echo $sem_create_data["sem_turnout"] ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip("'Hier geben Sie die erwartete Teilnehmerzahl an. Stud.IP kann auf Wunsch für Sie ein Anmeldungsverfahren starten, wenn sie  »Anmeldebeschränkung benutzen«.", TRUE, TRUE) ?>
							>
						</td>
						
					</tr>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Beschreibung/ Kommentar:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <textarea name="sem_desc" cols=58 rows=6><? echo htmlReady(stripslashes($sem_create_data["sem_desc"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Hier geben Sie bitte den eigentlichen Kommentartext der Veranstaltung ein.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Heimat-Einrichtung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp;
							<?
								if (!$perm->have_perm("admin")) //Alles unter Admin bekommt Institut, in denen er Dozent ist
									$db->query("SELECT * FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'dozent') GROUP BY Institute.institut_id ORDER BY Name");
								else if (!$perm->have_perm("root")) //Alles unter root (=Admin) bekommt alles wo er Admin isr
									$db->query("SELECT * FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'admin') GROUP BY Institute.institut_id ORDER BY Name");
								else //als Root alles
									$db->query("SELECT * FROM Institute ORDER BY Name");
								if ($db->affected_rows())
									{
									echo "<select name=\"sem_inst_id\">";
									while ($db->next_record()) {
										printf ("<option %s value=%s> %s</option>", $db->f("Institut_id") == $sem_create_data["sem_inst_id"] ? "selected" : "", $db->f("Institut_id"), my_substr($db->f("Name"),0,60));
										}
									echo "</select>";
									}
								else
									echo "Ihr Account wurde noch keiner Einrichtung als Dozent zugeordnet. Bitte wenden Sie sich an Ihren Administrator der Einrichtung oder einen der <a href=\"impressum.php\">Entwickler</a>";
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Die Heimateinrichtung ist die Einrichtung, die offiziell f&uuml;r die Veranstaltung zuständig ist.", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							beteiligte Einrichtungen:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <select  name="sem_bet_inst[]" MULTIPLE SIZE=7>
							<?
								$db->query("SELECT * FROM Institute  WHERE Name NOT LIKE '%- - -%' ORDER BY Name");
								while ($db->next_record()) {
									$tempInstitut_id = $db->f("Institut_id");
									$selected=FALSE;
									$i=0;
									for ($i; $i<sizeof($sem_create_data["sem_bet_inst"]); $i++)
										if ($sem_create_data["sem_bet_inst"][$i] == $tempInstitut_id) $selected=TRUE;
									if ($selected) {
										printf ("<option selected value=%s> %s</option>", $tempInstitut_id, my_substr($db->f("Name"),0,60));
									} else {
										printf ("<option value=%s> %s</option>", $tempInstitut_id, my_substr($db->f("Name"),0,60));
										}
									}
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte markieren Sie hier alle Einrichtungen, an denen die Veranstaltung ebenfalls angeboten wird. Bitte beachten Sie: Sie können später nur DozentInnen aus den Einrichtungen auswählen, die entweder als Heimt- oder beteiligte Einrichtung markiert worden sind. Sie können mehrere Einträge markieren, indem sie die STRG bzw. APPLE Taste gedrückt halten.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_b">
						</td>
					</tr>
				</table>
			</form>
			</td>
		</tr>
	</table>
	<?
	}

//Level 2: Hier werden weitere Einzelheiten (Personendaten und Zeiten) abgefragt
if ($level==2)
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr >
			<td class="topic" colspan=2><b>
			<?
				if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])
					echo "&nbsp;Veranstaltungs-Assistent - Schritt 2: Personendaten, Typ, Sicherheit und Bereiche</b>";
				else
					echo "&nbsp;Veranstaltungs-Assistent - Schritt 2: Personendaten, Typ und Sicherheit</b>";
			?>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<tr>
			<td class="blank" valign="top">
				<blockquote>
				<?
				if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])
					echo "<b>Schritt 2: Personendaten, Studienbereiche und weitere Angaben der Veranstaltung</b><br><br>";
				else
					echo "<b>Schritt 2: Personendaten und weitere Angaben der Veranstaltung </b><br><br>";
				?>
				<font size=-1>Alle mit einem Sternchen&nbsp;</font><font color="red" size=+1><b>*</b></font><font size=-1>&nbsp;markierten Felder sind zwingend notwendig, um eine Veranstaltung anlegen zu k&ouml;nnen.</font><br><br>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="pictures/hands02.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
			<form method="POST" action="<? echo $PHP_SELF ?>">
			<input type="HIDDEN" name="form" value=2>
				<table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_a">&nbsp;<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_c">
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							DozentInnen der Veranstaltung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <select name="sem_doz[]" MULTIPLE SIZE=7>
							<?
								$clause='';
								$clause=$clause."institut_id = '".$sem_create_data["sem_inst_id"]."' OR auth_user_md5.user_id = '".$user_id."'";
								if ($sem_create_data["sem_bet_inst"])
									foreach ($sem_create_data["sem_bet_inst"] as $tmp_array)
										{
										$clause=$clause." OR institut_id = '".$tmp_array."'";
										}
								$db->query("SELECT DISTINCT auth_user_md5.user_id, Nachname, username, auth_user_md5.Vorname, perms FROM auth_user_md5 LEFT JOIN user_inst USING (user_id) WHERE user_inst.inst_perms = 'dozent' AND ($clause) ORDER BY Nachname");
								while ($db->next_record()) {
									$tempDozent_id = $db->f("user_id");
									$selected=FALSE;
									$i=0;
									for ($i; $i<sizeof($sem_create_data["sem_doz"]); $i++)
										if ($sem_create_data["sem_doz"][$i] == $tempDozent_id)
											$selected=TRUE;
										if (($db->f("user_id") == $user_id) && ($perm->have_perm ("dozent")))
											$selected=TRUE;
									if ($selected)
										{
										printf ("<option value=\"%s\" selected> %s, %s (%s) - %s</option>", $db->f("user_id"), $db->f("Nachname"), $db->f("Vorname"), $db->f("username"), $db->f("perms"));
										}
									else
										{
										printf ("<option value=\"%s\"> %s, %s (%s) - %s</option>", $db->f("user_id"), $db->f("Nachname"), $db->f("Vorname"), $db->f("username"), $db->f("perms"));
										}
								}
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Die Namen der DozentInnen, die die Veranstaltung leiten. Sie können mehrere Einträge markieren, indem sie die STRG bzw. APPLE Taste gedrückt halten.", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							TutorInnen der Veranstaltung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <select name="sem_tut[]" MULTIPLE SIZE=7>
							<?
								$clause='';
								$clause=$clause."institut_id = '".$sem_create_data["sem_inst_id"]."'";
								if ($sem_create_data["sem_bet_inst"])
									foreach ($sem_create_data["sem_bet_inst"] as $tmp_array)
										{
										$clause=$clause." OR institut_id = '".$tmp_array."'";
										}
								$db->query("SELECT DISTINCT auth_user_md5.user_id, Nachname, username, auth_user_md5.Vorname, perms FROM auth_user_md5 LEFT JOIN user_inst USING (user_id) WHERE (user_inst.inst_perms = 'tutor' OR user_inst.inst_perms = 'dozent') AND ($clause) ORDER BY Nachname");
								while ($db->next_record())
									{
									$tempTutor_id = $db->f("user_id");
									$selected=FALSE;
									$i=0;
									for ($i; $i<sizeof($sem_create_data["sem_tut"]); $i++)
										if ($sem_create_data["sem_tut"][$i] == $tempTutor_id) $selected=TRUE;
									if ($selected)
										{
										printf ("<option value=\"%s\" selected> %s, %s (%s) - %s</option>", $db->f("user_id"), $db->f("Nachname"), $db->f("Vorname"), $db->f("username"), $db->f("perms"));
										}
									else
										{
										printf ("<option value=\"%s\"> %s, %s (%s) - %s</option>", $db->f("user_id"), $db->f("Nachname"), $db->f("Vorname"), $db->f("username"), $db->f("perms"));
										}
									}
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Die Tutoren der Veranstaltung. Diese haben eingeschränkte Zugriffsrechte. Sie können mehrere Einträge markieren, indem sie die STRG bzw. APPLE Taste gedrückt halten.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Typ der Veranstaltung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <select name="sem_status">
							<?
								for ($i=1; $i <= sizeof($SEM_TYPE); $i++) {
									if ($SEM_TYPE[$i]["class"] == $sem_create_data["sem_class"])
										printf ("<option %s value=%s>%s</option>", $sem_create_data["sem_status"] == $i ? "selected" : "", $i, $SEM_TYPE[$i]["name"]);
								}
							?>
							</select> in der Kategorie <b><? echo $SEM_CLASS[$sem_create_data["sem_class"]]["name"] ?></b>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Über den Typ der Veranstaltung werden die Veranstaltungen innerhalb von Listen gruppiert.", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>	
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Art der Veranstaltung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <input type="text" name="sem_art" size=30 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_art"])) ?>">
							<font size=-1>(eigene Beschreibung)</font>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Hier können Sie eine frei wählbare Bezeichnung für die Art der Veranstaltung wählen.", TRUE, TRUE) ?>
							>								
						</td>
					</tr>
									
					<?
					if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])
					{
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Studienbereiche:<br>
							<font color="red" size=-1>
								(Studien<i>f&auml;cher</i> sind rot bzw. innerhalb der Linien dargestellt und k&ouml;nnen nicht ausgew&auml;hlt werden!)
							</font>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <select MULTIPLE name="sem_bereich[]" SIZE=10>
							<?
								$fachtmp="0";
								$clause='';
								$clause=$clause."user_inst.institut_id = '".$sem_create_data["sem_inst_id"]."'";
								if ($sem_create_data["sem_bet_inst"])
									foreach ($sem_create_data["sem_bet_inst"] as $tmp_array)
										{
										$clause=$clause." OR user_inst.institut_id = '".$tmp_array."'";
										}
								// Anzeige der eigenen Faecher
								$db3->query("SELECT DISTINCT bereiche.bereich_id, bereiche.name, bereich_fach.fach_id FROM bereiche LEFT JOIN bereich_fach USING(bereich_id) LEFT JOIN fach_inst USING (fach_id) LEFT JOIN user_inst USING (institut_id) WHERE ($clause)  AND user_inst.inst_perms !='user' AND user_inst.inst_perms !='autor' AND user_inst.inst_perms !='tutor' ORDER BY bereich_fach.fach_id, bereiche.name");
								while ($db3->next_record())
									{
									IF ($fachtmp != $db3->f("fach_id"))
										{
										// Hier werden die Faecherueberschriften ausgegeben
										$fachtmp = $db3->f("fach_id");
										$db4->query("SELECT name from faecher WHERE fach_id = '$fachtmp'");
										while ($db4->next_record())
											{
											//echo "</optgroup>"; ## Optgroups funktionieren nur ab Browser v. 6 Koennen auf Wunsch statt der naechsten drei Zeilen verwendet werden
											echo "<option style=\"color:red;\" value = \"nix\">------------------------------------------------------------</option>";
											echo "<option style=\"color:red;\" value = \"nix\">".my_substr($db4->f("name"),0,60)."</option>";
											echo "<option style=\"color:red;\" value = \"nix\">------------------------------------------------------------</option>";
											//echo "<optgroup style=\"color:red;\" label=\"".my_substr($db4->f("name"),0,60)."\">";
											}
										}
										// Anzeige ob Selected oder nicht
										$bereichtmp =	 $db3->f("bereich_id");
										$selected=FALSE;
										$i=0;
										for ($i; $i<sizeof($sem_create_data["sem_bereich"]); $i++)
											if ($sem_create_data["sem_bereich"][$i] == $bereichtmp) $selected=TRUE;
										if ($selected)
											printf ("<option selected VALUE=\"%s\"> %s</option>", $db3->f("bereich_id"), "&nbsp;".my_substr($db3->f("name"),0,60));
										else
											printf ("<option VALUE=\"%s\"> %s</option>", $db3->f("bereich_id"), "&nbsp;".my_substr($db3->f("name"),0,60));
										$fachtmp = $db3->f("fach_id");
									}
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Hier k&ouml;nnen Sie die Studienbereiche, an denen die Veranstaltung angeboten wird, markieren. Sie können mehrere Einträge markieren, indem sie die STRG bzw. APPLE Taste gedrückt halten.", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>
					<?
					}
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Lesezugriff:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<?					
							if (!$sem_create_data["sem_admission"]) {
								if (!isset($sem_create_data["sem_sec_lese"]) || $sem_create_data["sem_sec_lese"]==3)
									$sem_create_data["sem_sec_lese"] = "1";	//Vorgabe: nur angemeldet oder es war Teilnahmebegrenzung gesetzt
							?>
								<input type="radio" name="sem_sec_lese" value="0" <?php print $sem_create_data["sem_sec_lese"] == 0 ? "checked" : ""?>> freier Zugriff &nbsp;
								<input type="radio" name="sem_sec_lese" value="1" <?php print $sem_create_data["sem_sec_lese"] == 1 ? "checked" : ""?>> in Stud.IP angemeldet &nbsp;
								<input type="radio" name="sem_sec_lese" value="2" <?php print $sem_create_data["sem_sec_lese"] == 2 ? "checked" : ""?>> nur mit Passwort &nbsp;
								<img  src="./pictures/info.gif" 
									<? echo tooltip("Hier geben Sie an, ob der Lesezugriff auf das Veranstaltung frei (jeder), normal beschränkt (nur angemdeldet) oder nur mit einem speziellen Passwort möglich ist.", TRUE, TRUE) ?>
								>								
							<?
							} else
								print "&nbsp; <font size=-1>Leseberechtigung nach erfolgreichem Anmeldeprozess</font>"
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Schreibzugriff:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<?
							if (!$sem_create_data["sem_admission"]) {
								if (!isset($sem_create_data["sem_sec_schreib"]) || $sem_create_data["sem_sec_schreib"]==3)
									$sem_create_data["sem_sec_schreib"] = "1";	//Vorgabe: nur angemeldet
								if ($SEM_CLASS[$sem_create_data["sem_class"]]["write_access_nobody"]) {
									?>
								<input type="radio" name="sem_sec_schreib" value="0" <?php print $sem_create_data["sem_sec_schreib"] == 0 ? "checked" : ""?>> freier Zugriff &nbsp;
									<?
									}
								else {
									?>
								<font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  freier Zugriff &nbsp;</font>
									<?
									}
							?>
								<input type="radio" name="sem_sec_schreib" value="1" <?php print $sem_create_data["sem_sec_schreib"] == 1 ? "checked" : ""?>> in Stud.IP angemeldet &nbsp;
								<input type="radio" name="sem_sec_schreib" value="2" <?php print $sem_create_data["sem_sec_schreib"] == 2 ? "checked" : ""?>> nur mit Passwort &nbsp;
								<img  src="./pictures/info.gif" 
									<? echo tooltip("Hier geben Sie an, ob der Schreibzugriff auf das Veranstaltung frei (jeder), normal beschränkt (nur angemdeldet) oder nur mit einem speziellen Passwort möglich ist.", TRUE, TRUE) ?>
							>
							<?
							} else
								print "&nbsp; <font size=-1>Schreibberechtigung nach erfolgreichem Anmeldeprozess</font>"
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_a">&nbsp;<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_c">
						</td>
					</tr>
				</table>
			</form>
			</td>
		</tr>
	</table>
	<?
	}

//Level 3: Metadaten &uuml;ber Terminstruktur
if ($level==3)
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;Veranstaltungs-Assistent - Schritt 3:  Termine</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<tr>
			<td class="blank" valign="top">
				<blockquote>
				<b>Schritt 3: Daten &uuml;ber die Termine</b><br><br>
				<? if ($sem_create_data["term_art"] ==0) 
					{?>
					Bitte geben Sie hier ein, an welchen Tagen das Seminar stattfindet. Wenn Sie nur einen Wochentag wissen, brauchen Sie nur diesen angeben.<br>Sie haben sp&auml;ter die M&ouml;glichkeit, weitere Einzelheiten zu diesen Terminen anzugeben.<br><br>
					<?
					}
				else
					{
					?>
					Bitte geben Sie hier die eizelnen Termine, an denen die Veranstaltung stattfinden, an. <br> Sie haben sp&auml;ter die M&ouml;glichkeit, weitere Einzelheiten zu diesen Terminen anzugeben.<br><br>
					<?
					}
				?>
				<font size=-1>Alle mit einem Sternchen&nbsp;</font><font color="red" size=+1><b>*</b></font><font size=-1>&nbsp;markierten Felder sind zwingend notwendig, um eine Veranstaltung anlegen zu k&ouml;nnen.</font><br><br>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="pictures/hands03.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
			<form method="POST" name="form_3" action="<? echo $PHP_SELF ?>">
			<input type="HIDDEN" name="form" value=3>
				<table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_b">&nbsp;<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_d">
						</td>
					</tr>
					<?
						if ($sem_create_data["term_art"] ==0)
							{
							?>
							<tr <? $cssSw->switchClass() ?>>
								<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
									&nbsp; Daten &uuml;ber die Termine:
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
									&nbsp; <b><font size=-1>Regelm&auml;&szlig;ige Veranstaltung</font></b><br><br>
									&nbsp;  <font size=-1>Wenn Sie den Typ der Veranstaltung &auml;ndern m&ouml;chten, gehen Sie bitte auf die erste Seite zur&uuml;ck.</font><br><br>
									&nbsp; Turnus: &nbsp; 
									<select name="term_turnus">
									<?
									if ($sem_create_data["term_turnus"]==0)
										echo "<option selected value=0>w&ouml;chentlich</option>";
									else
										echo "<option value=0>w&ouml;chentlich</option>";
									if ($sem_create_data["term_turnus"]==1)
										echo "<option selected value=1>zweiw&ouml;chentlich</option>";
									else
										echo "<option value=1>zweiw&ouml;chentlich</option>";
									?>
									</select>&nbsp; erster Termin in der 
									<select name="term_start_woche">
									<?
									if ($sem_create_data["term_start_woche"]==0)
										echo "<option selected value=0>1. Semesterwoche</option>";
									else
										echo "<option value=0>1. Semesterwoche</option>";
									if ($sem_create_data["term_start_woche"]==1)
										echo "<option selected value=1>2. Semesterwoche</option>";
									else
										echo "<option value=1>2. Semesterwoche</option>";
									if ($sem_create_data["term_start_woche"]==-1)
										echo "<option selected value=-1>anderer Startzeitpunkt</option>";
									else
										echo "<option value=-1>anderer Startzeitpunkt</option>";
										
									?>
									</select>
									<br><br>&nbsp; Die Veranstaltung findet immer zu diesen Zeiten statt:<br><br>
									<?
									if (empty($sem_create_data["turnus_count"])) 
										$sem_create_data["turnus_count"]=2;
									for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
										{
										if ($i>0) echo "<br>";
										?>&nbsp; <select name="term_turnus_date[<?echo $i?>]">
										<?
										if (($sem_create_data["term_turnus_date"][$i]==1) || (empty($sem_create_data["term_turnus_date"][$i])))
											echo "<option selected value=1>Montag</option>";
										else
											echo "<option value=1>Montag</option>";
										if ($sem_create_data["term_turnus_date"][$i]==2)
											echo "<option selected value=2>Dienstag</option>";
										else
											echo "<option value=2>Dienstag</option>";
										if ($sem_create_data["term_turnus_date"][$i]==3)
											echo "<option selected value=3>Mittwoch</option>";
										else
											echo "<option value=3>Mittwoch</option>";
										if ($sem_create_data["term_turnus_date"][$i]==4)
											echo "<option selected value=4>Donnerstag</option>";
										else
											echo "<option value=4>Donnerstag</option>";
										if ($sem_create_data["term_turnus_date"][$i]==5)
											echo "<option selected value=5>Freitag</option>";
										else
											echo "<option value=5>Freitag</option>";
										if ($sem_create_data["term_turnus_date"][$i]==6)
											echo "<option selected value=6>Samstag</option>";
										else
											echo "<option value=6>Samstag</option>";
										if ($sem_create_data["term_turnus_date"][$i]==7)
											echo "<option selected value=7>Sonntag</option>";
										else
											echo "<option value=7>Sonntag</option>";
										echo "</select>\n";
										?>
										<font size=-1>&nbsp; <input type="text" name="term_turnus_start_stunde[<?echo $i?>]" size=2 maxlength=2 value="<? if ($sem_create_data["term_turnus_start_stunde"][$i]) echo $sem_create_data["term_turnus_start_stunde"][$i] ?>"> :
										<input type="text" name="term_turnus_start_minute[<?echo $i?>]" size=2 maxlength=2 value="<? if (($sem_create_data["term_turnus_start_minute"][$i]) && ($sem_create_data["term_turnus_start_minute"][$i] >0)) { if ($sem_create_data["term_turnus_start_minute"][$i] < 10) echo "0", $sem_create_data["term_turnus_start_minute"][$i]; else echo $sem_create_data["term_turnus_start_minute"][$i];  } elseif ($sem_create_data["term_turnus_start_stunde"][$i]) echo "00"; ?>">&nbsp;Uhr bis
										&nbsp; <input type="text" name="term_turnus_end_stunde[<?echo $i?>]" size=2 maxlength=2 value="<? if ($sem_create_data["term_turnus_end_stunde"][$i]) echo $sem_create_data["term_turnus_end_stunde"][$i] ?>"> :
										<input type="text" name="term_turnus_end_minute[<?echo $i?>]" size=2 maxlength=2 value="<? if (($sem_create_data["term_turnus_end_minute"][$i]) && ($sem_create_data["term_turnus_end_minute"][$i] >0)) { if ($sem_create_data["term_turnus_end_minute"][$i] < 10) echo "0", $sem_create_data["term_turnus_end_minute"][$i]; else echo $sem_create_data["term_turnus_end_minute"][$i];  } elseif ($sem_create_data["term_turnus_end_stunde"][$i]) echo "00"; ?>">&nbsp;Uhr</font>
										<? if ($sem_create_data["turnus_count"]>1) 
											{
											?>
											&nbsp; <a href="<? echo $PHP_SELF?>?delete_turnus_field=<?echo $i+1?>"><img border=0 src="./pictures/trash.gif" <? echo tooltip("Dieses Feld aus der Auswahl löschen", TRUE) ?> ></a>
											<?
											}
										}
										?>
										&nbsp; &nbsp; <input type="IMAGE" name="add_turnus_field" src="./pictures/buttons/feldhinzufuegen-button.gif" border=0 value="Feld hinzuf&uuml;gen">&nbsp; 
										<img  src="./pictures/info.gif" 
											<? echo tooltip("Wenn es sich um eine zyklische Veranstaltung handelt, so können Sie hier genau angeben, an welchen Tagen und zu welchen Zeiten die Veranstaltung stattfindet. Wenn Sie noch keine Zeiten wissen, dann klicken Sie auf »keine Zeiten speichern«.", TRUE, TRUE) ?>
										>
										<font color="red" size=+2>*</font>											
										<br>
								</td>
							</tr>
						<?
						}
					else
						{
						?>
							<tr <? $cssSw->switchClass() ?>>
								<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
									&nbsp; Daten &uuml;ber die Termine:
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
									&nbsp; <b><font size=-1>Veranstaltung an unregelm&auml;&szlig;igen Terminen</font></b><br><br>
									&nbsp;  <font size=-1>Wenn Sie den Typ der Veranstaltung &auml;ndern m&ouml;chten, gehen Sie bitte auf die erste Seite zur&uuml;ck.</font><br><br>
									&nbsp; Die Veranstaltung findet an diesen Terminen statt:<br><br>
									<?
									if (empty($sem_create_data["term_count"])) 
										$sem_create_data["term_count"]=2;
									for ($i=0; $i<$sem_create_data["term_count"]; $i++)
										{
										if ($i>0) echo "<br>";
										?>
										<font size=-1>&nbsp; Datum: <input type="text" name="term_tag[<?echo $i?>]" size=2 maxlength=2 value="<? if ($sem_create_data["term_tag"][$i]) echo $sem_create_data["term_tag"][$i] ?>">.
										<input type="text" name="term_monat[<?echo $i?>]" size=2 maxlength=2 value="<? if ($sem_create_data["term_monat"][$i]) echo $sem_create_data["term_monat"][$i] ?>">.
										<input type="text" name="term_jahr[<?echo $i?>]" size=4 maxlength=4 value="<? if ($sem_create_data["term_jahr"][$i]) echo $sem_create_data["term_jahr"][$i] ?>">
										&nbsp;um <input type="text" name="term_start_stunde[<?echo $i?>]" size=2 maxlength=2 value="<? if ($sem_create_data["term_start_stunde"][$i]) echo $sem_create_data["term_start_stunde"][$i] ?>"> :
										<input type="text" name="term_start_minute[<?echo $i?>]" size=2 maxlength=2 value="<? if (($sem_create_data["term_start_minute"][$i]) && ($sem_create_data["term_start_minute"][$i] >0)) { if ($sem_create_data["term_start_minute"][$i] < 10) echo "0", $sem_create_data["term_start_minute"][$i]; else echo $sem_create_data["term_start_minute"][$i];  } elseif ($sem_create_data["term_start_stunde"][$i]) echo "00"; ?>">&nbsp;Uhr bis
										<input type="text" name="term_end_stunde[<?echo $i?>]" size=2 maxlength=2 value="<? if ($sem_create_data["term_end_stunde"][$i]) echo $sem_create_data["term_end_stunde"][$i] ?>"> :
										<input type="text" name="term_end_minute[<?echo $i?>]" size=2 maxlength=2 value="<? if (($sem_create_data["term_end_minute"][$i]) && ($sem_create_data["term_end_minute"][$i] >0)) { if ($sem_create_data["term_end_minute"][$i] < 10) echo "0", $sem_create_data["term_end_minute"][$i]; else echo $sem_create_data["term_end_minute"][$i];  } elseif ($sem_create_data["term_end_stunde"][$i]) echo "00"; ?>">&nbsp;Uhr</font>
										<? if ($sem_create_data["term_count"]>1) 
											{
											?>
											&nbsp; <a href="<? echo $PHP_SELF?>?delete_term_field=<?echo $i+1?>"><img border=0 src="./pictures/trash.gif" <? echo tooltip("Dieses Feld aus der Auswahl löschen", TRUE) ?> ></a>
											<?
											}
										}
										?>
										&nbsp; &nbsp; <input type="IMAGE" name="add_term_field" src="./pictures/buttons/feldhinzufuegen-button.gif" border=0 value="Feld hinzuf&uuml;gen">&nbsp; 
										<img  src="./pictures/info.gif" 
											<? echo tooltip("In diesem Feldern können Sie aller Termine ein, an denen die Veranstaltung stattfindet. Wenn Sie noch keine Termine wissen, dann klicken Sie auf »keine Termine speichern.'", TRUE, TRUE) ?>
										>
										<font color="red" size=+2>*</font>
										<br>
								</td>
							</tr>
						<?
						}
						?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Semester:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="20%">
							&nbsp;
							<?
							echo "<select name=\"sem_start_time\">";
							for ($i=1; $i<=sizeof($SEMESTER); $i++)
								if ((!$SEMESTER[$i]["past"]) && ($SEMESTER[$i]["vorles_ende"] > time()))
									{
									if ($sem_create_data["sem_start_time"] ==$SEMESTER[$i]["beginn"])
										echo "<option value=".$SEMESTER[$i]["beginn"]." selected>", $SEMESTER[$i]["name"], "</option>";
									else
										echo "<option value=".$SEMESTER[$i]["beginn"].">", $SEMESTER[$i]["name"], "</option>";
									}
							echo "</select>";
							?>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte geben Sie hier ein, welchem Semester die Veranstaltung zugeordnet werden soll.", TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Dauer:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="60%">
							&nbsp; <select name="sem_duration_time">
							<?
								if ($sem_create_data["sem_duration_time"] == 0)
									echo "<option value=0 selected>1 Semester</option>";
								else
									echo "<option value=0>1 Semester</option>";
								$i=1;
								for ($i; $i<=sizeof($SEMESTER); $i++)
									if ((!$SEMESTER[$i]["past"]) && ($SEMESTER[$i]["name"] != $SEM_NAME) && (($SEMESTER[$i]["vorles_ende"] > time())))
										{
										if (($sem_create_data["sem_start_time"] + $sem_create_data["sem_duration_time"]) == $SEMESTER[$i]["beginn"])
											{
											if (!$sem_create_data["sem_duration_time"] == 0)
												echo "<option value=",$SEMESTER[$i]["beginn"], " selected>bis ", $SEMESTER[$i]["name"], "</option>";
											else
												echo "<option value=",$SEMESTER[$i]["beginn"], ">bis ", $SEMESTER[$i]["name"], "</option>";
											}
										else
											echo "<option value=",$SEMESTER[$i]["beginn"], ">bis ", $SEMESTER[$i]["name"], "</option>";
										}
								if ($sem_create_data["sem_duration_time"] == -1)
									echo "<option value=-1 selected>unbegrenzt</option>";
								else
									echo "<option value=-1>unbegrenzt</option>";
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Falls die Veranstaltung mehrere Semester läuft, können Sie hier das Endsemester wählen. Dauerveranstaltung können über die entsprechende Einstellung markiert werden.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Vorbesprechung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<font size=-1>&nbsp; <font size=-1>Wenn es eine Vorbesprechung gibt, tragen Sie diese bitte hier ein:</font><br><br>&nbsp; Datum:</font>
							<font size=-1><input type="text" name="vor_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("d",$sem_create_data["sem_vor_termin"]); else echo"tt" ?>">.
							<input type="text" name="vor_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("m",$sem_create_data["sem_vor_termin"]); else echo"mm" ?>">.
							<input type="text" name="vor_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("Y",$sem_create_data["sem_vor_termin"]); else echo"jjjj" ?>">&nbsp;
							&nbsp;um <input type="text" name="vor_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("H",$sem_create_data["sem_vor_termin"]); else echo"hh" ?>"> :
							<input type="text" name="vor_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("i",$sem_create_data["sem_vor_termin"]); else echo"mm" ?>">&nbsp;Uhr bis
							<input type="text" name="vor_end_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_end_termin"]<>-1) echo date("H",$sem_create_data["sem_vor_end_termin"]); else echo"mm" ?>"> :
							<input type="text" name="vor_end_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_end_termin"]<>-1) echo date("i",$sem_create_data["sem_vor_end_termin"]); else echo"hh" ?>">&nbsp;Uhr
							&nbsp;Raum: <input type="text" name="vor_raum" size=10 maxlength=255 value="<? if ($sem_create_data["sem_vor_raum"]) echo  htmlReady(stripslashes($sem_create_data["sem_vor_raum"])); ?>"></font>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Dieses Feld müssen Sie nur ausfüllen, wenn es eine verbindliche Vorbesprechung zu der Veranstaltung gibt.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_b">&nbsp;<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_d">
						</td>
					</tr>
				</table>
			</form>
			</td>
		</tr>
	</table>
	<?
	}


//Level 4: Hier wird der Rest abgefragt
if ($level==4)
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr >
			<td class="topic" colspan=2><b>&nbsp;Veranstaltungs-Assistent - Schritt 4: Sonstige Daten</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2 >&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<tr>
			<td class="blank" valign="top">
				<blockquote>
				<b>Schritt 4: Sonstige Daten zu der Veranstaltung</b><br><br>
				<font size=-1>Alle mit einem Sternchen&nbsp;</font><font color="red" size=+1><b>*</b></font><font size=-1>&nbsp;markierten Felder sind zwingend notwendig, um eine Veranstaltung anlegen zu k&ouml;nnen.</font><br><br>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="pictures/hands04.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
			<form method="POST" name="form_4" action="<? echo $PHP_SELF ?>"
	               <? if (($sem_create_data["sem_sec_lese"] ==2) ||  ($sem_create_data["sem_sec_schreib"] ==2)) echo " onSubmit=\"return doCrypt();\" "; ?>
               		>
			<input type="HIDDEN" name="form" value=4>
			<input type="HIDDEN" name="hashpass" value="">
			<input type="HIDDEN" name="hashpass2" value="">				
				<table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_c">&nbsp;<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_e">
						</td>
					</tr>
					<?
					if ($sem_create_data["sem_admission"]) {
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Anmeldeverfahren:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							<font size=-1>&nbsp;Sie haben vorher das Stud.IP Anmeldeverfahren <? if ($sem_create_data["sem_admission"] == 1) echo "nach dem Losverfahren"; else echo "in Reihenfolge der Anmeldung";?> aktiviert. <br />
							&nbsp;Bitte geben Sie hier an, welche Studieng&auml;nge mit welchen Kontingenten zugelassen sind und wann das <? if ($sem_create_data["sem_admission"] == 1) echo "Losdatum"; else echo "Enddatum der Kontingentierung";?> ist:<br /><br />
								<table border=0 cellpadding=2 cellspacing=0>
									<tr>
										<td class="<? echo $cssSw->getClass() ?>" valign="bottom" width="25%">
										<font size=-1>&nbsp;
										<?
										printf ("%s", ($sem_create_data["sem_studg"]) ? "Alle anderen Studieng&auml;nge" : "Alle Studieng&auml;nge");
										?>
										</font>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" valign="bottom"  nowrap width="5%">
										<?
										printf ("<input type=\"HIDDEN\" name=\"sem_all_ratio_old\" value=\"%s\" />", ($sem_create_data["sem_studg"]) ? $sem_create_data["sem_all_ratio"] : "100");
										printf ("<input type=\"TEXT\" name=\"sem_all_ratio\" size=5 maxlength=5 value=\"%s\" /> <font size=-1> %%</font>", ($sem_create_data["sem_studg"]) ? $sem_create_data["sem_all_ratio"] : "100");
										?>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" valign="top" align="right" width="25%">
											<font size=-1><? if ($sem_create_data["sem_admission"] == 1) echo "Losdatum"; else echo "Enddatum der Kontingentierung";?>:</font>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" valign="top" width="45%">
											<font size=-1>&nbsp; <input type="text" name="adm_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("d",$sem_create_data["sem_admission_date"]); else echo"tt" ?>">.
											<input type="text" name="adm_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("m",$sem_create_data["sem_admission_date"]); else echo"mm" ?>">.
											<input type="text" name="adm_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("Y",$sem_create_data["sem_admission_date"]); else echo"jjjj" ?>">um&nbsp;</font><br />
											<font size=-1>&nbsp; <input type="text" name="adm_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("H",$sem_create_data["sem_admission_date"]); else echo"23" ?>">:
											<input type="text" name="adm_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("i",$sem_create_data["sem_admission_date"]); else echo"59" ?>">&nbsp;Uhr</font>&nbsp; 
											<? 
											if ($sem_create_data["sem_admission"] == 1) {
											?>
											<img  src="./pictures/info.gif" 
												<? echo tooltip("Bitte geben Sie hier ein, wann die Anwärter auf der Anmeldeliste in die Veranstaltung gelost werden. Freigebliebene Plätze werden nach diesem Termin per Warteliste an andere interessierte Personen vergeben.", TRUE, TRUE) ?>
											>
											<? 
											} else {
											?>
											<img  src="./pictures/info.gif" 
												<? echo tooltip("Bitte geben Sie hier ein, wann das Anmelderverfahren die Kontingentierung aufheben soll. Ab diesem Zeitpunkt werden freie Plätze an interessierten Personen aus der Warteliste vergeben.", TRUE, TRUE) ?>
											>
											<?
											}
											?>
										</td>
									</tr>
									<?
									if ($sem_create_data["sem_studg"]) {
										foreach ($sem_create_data["sem_studg"] as $key=>$val) {
									?>
									<tr>
										<td class="<? echo $cssSw->getClass() ?>" width="25%">
										<font size=-1>&nbsp;
										<?
										echo (htmlReady(my_substr($val["name"], 0, 40)));
										?>
										</font>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" nowrap width="5%">
										<input type="HIDDEN" name="sem_studg_id[]" value="<? echo $key ?>" />
										<input type="HIDDEN" name="sem_studg_name[]" value="<? echo $val["name"] ?>" />
										<?
										printf ("<input type=\"HIDDEN\" name=\"sem_studg_ratio_old[]\" value=\"%s\" />", $val["ratio"]);
										printf ("<input type=\"TEXT\" name=\"sem_studg_ratio[]\" size=5 maxlength=5 value=\"%s\" /><font size=-1> %%</font>", $val["ratio"]);
										printf ("&nbsp; <a href=\"%s?sem_delete_studg=%s\"><img border=0 src=\"./pictures/trash.gif\" ".tooltip("Den Studiengang aus der Liste löschen", TRUE)." />", $PHP_SELF, $key);
										?>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" width="70%" colspan=2>&nbsp; 
										</td>
									</tr>
									<?
										}
									}
									$db->query("SELECT * FROM studiengaenge");
									if ($db->num_rows() != sizeof($sem_create_data["sem_studg"])) {
									?>
									<tr>
										<td class="<? echo $cssSw->getClass() ?>" width="25%">
										<font size=-1>&nbsp;
										<select name="sem_add_studg">
										<option value="">-- bitte ausw&auml;hlen --</option>
									<?
									while ($db->next_record()) {
										if (is_array($sem_create_data["sem_studg"])) {
											if (!$sem_create_data["sem_studg"][$db->f("studiengang_id")])
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
										<input type="TEXT" name="sem_add_ratio" size=5 maxlength=5 /><font size=-1> %</font>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" width="25%">
											<input type="IMAGE" src="./pictures/buttons/hinzufuegen-button.gif" name="add_studg" border=0 />&nbsp;
											<img  src="./pictures/info.gif" 
												<? echo tooltip("Bitte geben Sie hier ein, für welche Studiengänge die Veranstaltung mit welchen Kontingenten beschränkt sein soll und bis wann eine Anmeldung über das Stud.IP Anmeldeverfahren möglich ist.", TRUE, TRUE) ?>
											>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" width="40%">&nbsp; 
										</td>
									</tr>
									<?
									} 
									?>
								</table>
						</td>
					</tr>
					<tr>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							&nbsp; 
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							<font size=-1 color="red">&nbsp; Achtung: </font><font size=-1>Wenn Sie ein Anmeldeverfahren starten, so kann dieser Schritt sp&auml;ter nicht r&uuml;ckg&auml;ngig gemacht werden. <br />&nbsp; Sie k&ouml;nnen jedoch die Anzahl der Teilnehmer jederzeit anpassen.</font>
						</td>
					</tr>
					<?
					}
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Teilnehmer- beschreibung:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_teiln" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_teiln"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte geben Sie hier ein, für welchen Teilnehmerkreis die Veranstaltung geeignet ist.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Voraussetzungen:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_voraus" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_voraus"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte geben Sie hier ein, welche Vorausetzungen für die Veranstaltung nötig sind.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Lernorganisation:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_orga" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_orga"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte geben Sie hier ein, mit welcher Lernorganisation die Veranstaltung durchgeführt wird.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Leistungsnachweis:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <textarea name="sem_leistnw" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_leistnw"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Bitte geben Sie hier ein, welche Leistungsnachweise erbracht werden müssen.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Sonstiges:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <textarea name="sem_sonst" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_sonst"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("Hier ist Platz für alle sonstigen Informationen der Veranstaltung.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<?
					if (($sem_create_data["sem_sec_lese"] ==2) || ($sem_create_data["sem_sec_schreib"] ==2))
						{
						?>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
								Passwort f&uuml;r Freischaltung:
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>&nbsp;
								<?
									if ($sem_create_data["sem_pw"]!="")
										echo "<input type=\"password\" name=\"password\" size=12 maxlength=31 value=\"*******\">&nbsp; Passwort-Wiederholung:&nbsp; <input type=\"password\" name=\"password2\" size=12 maxlength=31 value=\"*******\">";
									else	
										echo "<input type=\"password\" name=\"password\" size=12 maxlength=31> &nbsp; Passwort-Wiederholung:&nbsp; <input type=\"password\" name=\"password2\" size=12 maxlength=31>";
								?>
								<img  src="./pictures/info.gif" 
									<? echo tooltip("Bitte geben Sie hier ein Passwort sowie ein einmaliges Wiederholungspasswort für die Veranstaltung ein. Dieses wird von den Teilnehmer benötigt, um die Veranstaltung abonnieren zu können.", TRUE, TRUE) ?>
								>
							</td>
						</tr>
						<?
						}
					if (($sem_create_data["term_start_woche"]==-1) && ($sem_create_data["term_art"] == 0))
						{
						?>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
								erster Termin:
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
								<font size=-1>&nbsp; <font size=-1>Sie haben angegeben, an einem anderen Zeitpunkt mit der Veranstaltung zu beginnen. Bitte geben Sie hier den ersten Termin ein.</font><br><br>&nbsp; Datum: </font>
								<font size=-1><input type="text" name="tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("d",$sem_create_data["sem_start_termin"]); else echo"tt" ?>">.
								<input type="text" name="monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("m",$sem_create_data["sem_start_termin"]); else echo"mm" ?>">.
								<input type="text" name="jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("Y",$sem_create_data["sem_start_termin"]); else echo"jjjj" ?>">&nbsp; </font>
								<img  src="./pictures/info.gif" 
									<? echo tooltip("Bitte geben Sie hier ein, wann der erste Termin der Veranstaltung stattfindet.", TRUE, TRUE) ?>
								>
							</td>
						</tr>
						<?
						}
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_c">&nbsp;<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_e">
						</td>
					</tr>
				</table>
			</form>
			</td>
		</tr>
	</table>
	<?
	}

//Level 5: Seminar anlegen
if ($level==5)
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;Veranstaltungs-Assistent - Schritt 5: Anlegen der Veranstaltung</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<tr>
			<td class="blank" valign="top">
				<blockquote>
				<b>Schritt 5: Bereit zum Anlegen der Veranstaltung</b><br><br>
				Sie haben nun alle n&ouml;tigen Daten zum Anlegen der Veranstaltung eingegeben. Wenn Sie auf "Fertig stellen" klicken, wird die Veranstaltung in das System &uuml;bernommen. Wenn Sie sich sich nicht sicher sind, ob alle Daten korrekt sind, &uuml;berpr&uuml;fen Sie noch einmal die Eingaben auf den vorhergehenden Seiten.<br><br>
				<form method="POST" action="<? echo $PHP_SELF ?>">
					<input type="HIDDEN" name="form" value=5>
					<input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_d">&nbsp;<input type="IMAGE" src="./pictures/buttons/fertigstellen-button.gif" border=0 value="Weiter >>" name="cmd_f">
				</form>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="pictures/hands05.jpg" border="0">
			</td>
		</tr>
	</table>
	<?
	}

//Level 6:Statusmeldungen nach dem Anlegen und weiter zum den Einzelheiten
if ($level==6)
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;Veranstaltungs-Assistent</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<?
		if (!$successful_entry)
			{ ?>
			<tr>
				<td class="blank">
					<blockquote>
					<b>Die Veranstaltung konnte nicht angelegt werden.</b><br><br>
					Bitte korrigieren Sie die Daten.
					<form method="POST" action="<? echo $PHP_SELF ?>">
						<input type="HIDDEN" name="form" value=6>
						<input type="IMAGE" src="./pictures/buttons/zurueck-button.gif" border=0 value="Weiter >>" name="cmd_a">
					</form>
					</blockqoute>
				</td>
				<td class="blank" align="right">
					<img src="pictures/hands05.jpg" border="0">
				</td>
			</tr> <?
			}
		elseif ($successful_entry==2)
			{ ?>
			<tr>
				<td class="blank">
					<blockquote>
					Sie haben die Veranstaltung bereits angelegt und k&ouml;nnen nun mit der Literatur- und Linkverwaltung und dem Termin-Assistenten fortfahren oder an diesem Punkt abbrechen.<br><br><br>
					<form method="POST" action="<? echo $PHP_SELF ?>">
						<input type="HIDDEN" name="form" value=6>
						<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_f">
					</form>
					</blockqoute>
				</td>
				<td class="blank" align="right">
					<img src="pictures/hands05.jpg" border="0">
				</td>
			</tr> <?
			}
			
		else
			{ ?>
			<tr>
				<td class="blank" valign="top">
					<blockquote>
					<b>Die Daten der Veranstaltung wurden in das System &uuml;bernommen</b><br><br>
					Die Veranstaltung ist damit eingerichtet. Wenn Sie nun auf "weiter >>" klicken, k&ouml;nnen Sie weitere optionale Daten f&uuml;r die Veranstaltung eintragen. Sie haben die M&ouml;glichkeit, Literatur- und Linklisten einzugeben und k&ouml;nnen mit Hilfe des Termin-Assisten einen Ablaufplan erstellen.<br><br>
					<font size=-1>Sie haben jederzeit die M&ouml;glichkeit, die bereits erfassten Daten zu &auml;ndern und die n&auml;chsten Schritte sp&auml;ter nachzuholen.</font><br><br>
					<form method="POST" action="<? echo $PHP_SELF ?>">
						<input type="HIDDEN" name="form" value=6>
						<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_g">
					</form>
					</blockqoute>
				</td>
				<td class="blank" align="right" valign="top">
					<img src="pictures/hands05.jpg" border="0">
				</td>
			</tr>
			<tr>
				<td class="blank" colspan=2>
				<br>
				<form method="POST" action="<? echo $PHP_SELF ?>">
					<table width ="60%" cellspacing=1 cellpadding=1>
						<tr>
							<td width="10%" class="blank">&nbsp; </td>
							<td width="90%" class="steel1">
							<?
							echo "<br><br><ul><li>Veranstaltung \"<b>".htmlReady(stripslashes($sem_create_data["sem_name"]))."</b>\"erfolgreich angelgt.<br><br>";
							if ($count_bet_inst==1)
								echo "<li>Veranstaltung f&uuml;r <b>1</b> beteiligte Einrichtung angelegt.<br><br>";
							elseif ($count_bet_inst>1)
								echo "<li>Veranstaltung f&uuml;r <b>$count_bet_inst</b> beteiligte Einrichtungen angelegt.<br><br>";
							if ($count_doz==1)
								echo "<li><b>1</b> DozentIn f&uuml;r die Veranstaltung eingetragen.<br><br>";
							else
								echo "<li><b>$count_doz</b> DozentInnen f&uuml;r die Veranstaltung eingetragen.<br><br>";
							if ($count_tut==1)
								echo "<li><b>1</b> TutorIn f&uuml;r die Veranstaltung eingetragen.<br><br>";
							elseif ($count_tut>1)
								echo "<li><b>$count_tut</b> TutorInnen f&uuml;r die Veranstaltung eingetragen.<br><br>";
							if ($count_bereich==1)
								echo "<li><b>1</b> Bereich f&uuml;r die Veranstaltung eingetragen.<br><br>";
							else
								echo "<li><b>$count_bereich</b> Bereiche f&uuml;r die Veranstaltung eingetragen.<br><br>";
							echo "</ul>";
							?>
							</td>
						</tr>
					</table>
					<br>
					<br>
				</form>
				</td>
			</tr>
			<?
			}
			?>
	</table>
	<?
	}

//Level 7: Erstellen der Literatur- und Linkliste
if ($level==7)
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;Veranstaltungs-Assistent - Schritt 6: Literatur- und Linkliste</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>&nbsp;
				<?
				if ($errormsg) parse_msg($errormsg);
				?>
			</td>
		</tr>
		<tr>
			<td class="blank" valign="top">
				<blockquote>
				<b>Schritt 6: Eingeben der Literatur- und Linkliste</b><br><br>
				Sie k&ouml;nnen nun Literatur und Links f&uuml;r die eben angelegte Veranstaltung "<? echo $sem_create_data["sem_name"] ?>" eingeben. Wenn Sie auf "Fertig stellen" klicken, bekommen Sie noch die M&ouml;glichkeit, mit dem Termin-Assisten den Ablaufplan f&uuml;r die Veranstaltung anzulegen.<br><br>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="pictures/hands06.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
			<form method="POST" name="form_4" action="<? echo $PHP_SELF ?>">
			<input type="HIDDEN" name="form" value=7>
				<table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
					<tr<? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_h">
						</td>
					</tr>
						<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Literaturliste:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_literat" cols=58 rows=10><? echo $sem_create_data["sem_literat"] ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("In diesen Feld können Sie eine komplette Literaturliste einfügen.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							Linkliste:
						</td>
						<td class="<? echo $cssSw->getClass() ?>" class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_links" cols=58 rows=10><? echo $sem_create_data["sem_links"] ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip("In diesen Feld können Sie eine komplette Linkliste einfügen. Alle Links werden später automatisch als Hyperlinks angezeigt.", TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr<? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							<input type="IMAGE" src="./pictures/buttons/weiter-button.gif" border=0 value="Weiter >>" name="cmd_h">
						</td>
					</tr>
				</table>
		</tr>
	</table>
	<?
	}

page_close();
?>
</body>
</html>