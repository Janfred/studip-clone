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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); 	//hier werden die sessions initialisiert

require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php"); 		//Funktionen fuer Nachrichtenmeldungen
require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php"); 		//wir brauchen die Seminar-Typen
require_once ("$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php");  //Bereitstellung weiterer Daten
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");		//noch mehr Stuff
require_once ("$ABSOLUTE_PATH_STUDIP/forum.inc.php");		//damit wir Themen anlegen koennen
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");		//Aufbereitungsfunktionen
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");		//Terminfunktionen
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/StudipSemTreeSearch.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/Modules.class.php");

if ($RESOURCES_ENABLE) {
	include_once ($RELATIVE_PATH_RESOURCES."/resourcesClass.inc.php");
	include_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");
	include_once ($RELATIVE_PATH_RESOURCES."/lib/VeranstaltungResourcesAssign.class.php");
	$resAssign = new VeranstaltungResourcesAssign();
}

//cancel
if ($cancel_x) {
	header ("Location: admin_seminare1.php?");
}

// Get a database connection and Stuff
$db = new DB_Seminar;
$db2 = new DB_Seminar;
$db3 = new DB_Seminar;
$db4 = new DB_Seminar;
$cssSw = new cssClassSwitcher;
$st_search = new StudipSemTreeSearch("dummy","sem_bereich",false);
if (is_array($sem_create_data["sem_bereich"])){
		for ($i = 0; $i < count($sem_create_data["sem_bereich"]); $i++){
			$st_search->selected[$sem_create_data["sem_bereich"][$i]] = true;
			$st_search->sem_tree_ranges[$st_search->tree->tree_data[$sem_create_data["sem_bereich"][$i]]['parent_id']][] = $sem_create_data["sem_bereich"][$i];
			$st_search->sem_tree_ids[] = $sem_create_data["sem_bereich"][$i];
		}
	}
$st_search->institut_id = $sem_create_data["sem_inst_id"];
$st_search->doSearch();
$user_id = $auth->auth["uid"];
$errormsg='';

//Registrieren der Sessionvariablen
$sess->register("sem_create_data");
$sess->register("links_admin_data");

//Assi-Modus an und gesetztes Object loeschen solange keine Veranstaltung angelegt
if (!$sem_create_data["sem_entry"]) {
	$links_admin_data["assi"]=TRUE;
	closeObject();
} else
	$links_admin_data["assi"]=FALSE;

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
if ($start_level) { //create defaults
	$Modules = new Modules;
	
	$sem_create_data["sem_class"]=$class;
	
	foreach ($SEM_TYPE as $key => $val) {
		if ($val["class"] == $class) {
			$sem_create_data["modules_list"] = $Modules->getLocalModules("", "sem", false, $key);
			break;
		}
	}

	if ($SEM_CLASS[$class]["turnus_default"]) 
		$sem_create_data["term_art"] = $SEM_CLASS[$class]["turnus_default"];

	if ($SEM_CLASS[$class]["default_read_level"]) 
		$sem_create_data["sem_sec_lese"] = $SEM_CLASS[$class]["default_read_level"];

	if ($SEM_CLASS[$class]["default_write_level"]) 
		$sem_create_data["sem_sec_schreib"] = $SEM_CLASS[$class]["default_write_level"];
		
	if ($auth->auth["perm"] == "dozent")
		$sem_create_data["sem_doz"][$user->id]=TRUE;
}

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
	$sem_create_data["sem_start_time"]=$sem_start_time;
	
	if (($sem_duration_time == 0) || ($sem_duration_time == -1))
		$sem_create_data["sem_duration_time"]=$sem_duration_time;
	else
		$sem_create_data["sem_duration_time"]=$sem_duration_time - $sem_start_time;

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
	$st_search->institut_id = $sem_create_data["sem_inst_id"];
	
	}

if ($form==2)
	{
	if(isset($sem_bereich_chooser) && !$st_search->search_done){
		$st_search->sem_tree_ranges = array();
		$st_search->sem_tree_ids = array();
		for ($i = 0; $i < count($sem_bereich_chooser); $i++){
			if($sem_bereich_chooser[$i] != '0'){
				$st_search->selected[$sem_bereich_chooser[$i]] = true;
				$st_search->sem_tree_ranges[$st_search->tree->tree_data[$sem_bereich_chooser[$i]]['parent_id']][] = $sem_bereich_chooser[$i];
				$st_search->sem_tree_ids[] = $sem_bereich_chooser[$i];
			} else {
				$false_mark = true;
			}
		}
		$sem_create_data["sem_bereich"] = $st_search->sem_tree_ids;
	}
	
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

	//The room for the prelimary discussion
	$sem_create_data["sem_vor_raum"]=$vor_raum; 
	$sem_create_data["sem_vor_resource_id"]=($vor_resource_id == "FALSE") ? FALSE : $vor_resource_id; 
	if ($RESOURCES_ENABLE && $sem_create_data["sem_vor_resource_id"]) {
		$resObject=new ResourceObject($sem_create_data["sem_vor_resource_id"]);
		$sem_create_data["sem_vor_raum"]=$resObject->getName();
	}

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
			$sem_create_data["term_turnus_room"][$i]=$term_turnus_room[$i]; 
			$sem_create_data["term_turnus_resource_id"][$i]=($term_turnus_resource_id[$i] == "FALSE") ? FALSE : $term_turnus_resource_id[$i];
			if ($RESOURCES_ENABLE && $sem_create_data["term_turnus_resource_id"][$i]) {
				$resObject=new ResourceObject($sem_create_data["term_turnus_resource_id"][$i]);
				$sem_create_data["term_turnus_room"][$i]=$resObject->getName();
			}

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
					$tmp_metadata_termin["turnus_data"][]=array("idx"=>$tmp_idx, "day" => $sem_create_data["term_turnus_date"][$i], "start_stunde" => $sem_create_data["term_turnus_start_stunde"][$i], "start_minute" => $sem_create_data["term_turnus_start_minute"][$i], "end_stunde" => $sem_create_data["term_turnus_end_stunde"][$i], "end_minute" => $sem_create_data["term_turnus_end_minute"][$i], "room"=>$sem_create_data["term_turnus_room"][$i], "resource_id"=>$sem_create_data["term_turnus_resource_id"][$i],);
				}
				
			if (is_array($tmp_metadata_termin["turnus_data"])) {
				//check for dublettes
				$tmp_array_assi = $tmp_metadata_termin["turnus_data"];
				foreach ($tmp_array_assi as $key1=>$val1)  {
					foreach ($tmp_metadata_termin["turnus_data"] as $key2=>$val2) {
						if (($val1["day"] == $val2["day"]) &&
							($val1["start_stunde"] == $val2["start_stunde"]) &&
							($val1["start_minute"] == $val2["start_minute"]) &&
							($val1["end_stunde"] == $val2["end_stunde"]) &&
							($val1["end_minute"] == $val2["end_minute"]) &&
							($val1["room"] == $val2["room"]) &&
							($val1["ressource_id"] == $val2["ressource_id"]) &&
							($key1 != $key2))
							unset ($tmp_metadata_termin["turnus_data"][$key1]);
					}
				}
			
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
			$sem_create_data["term_room"][$i]=$term_room[$i]; 
			$sem_create_data["term_resource_id"][$i]=($term_resource_id[$i] == "FALSE") ? FALSE : $term_resource_id[$i];
			if ($RESOURCES_ENABLE && $sem_create_data["term_resource_id"][$i]) {
				$resObject=new ResourceObject($sem_create_data["term_resource_id"][$i]);
				$sem_create_data["term_room"][$i]=$resObject->getName();
			}

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

	if ($vor_monat == _("mm")) $vor_monat=0;
	if ($vor_tag == _("tt")) $vor_tag=0;
	if ($vor_jahr == _("jjjj")) $vor_jahr=0;	
	if ($vor_stunde == _("hh")) $vor_stunde=0;
	if ($vor_minute == _("mm")) $vor_minute=0;
	if ($vor_end_stunde == _("hh")) $vor_end_stunde=0;
	if ($vor_end_minute == _("mm")) $vor_end_minute=0;
	
	if (($vor_monat) && ($vor_tag) && ($vor_jahr))
		if (($vor_stunde== _("hh")) && ($vor_end_stunde== _("hh"))) {
			$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r Start- und Endzeit der Vorbesprechung ein!")."§"; 
			$check=FALSE;
		} else
			$check=TRUE;

	settype($vor_stunde, "integer");
	settype($vor_minute, "integer");
	settype($vor_end_stunde, "integer");
	settype($vor_end_minute, "integer");

	if ((!checkdate($vor_monat, $vor_tag, $vor_jahr) && ($vor_monat) && ($vor_tag) && ($vor_jahr)) && ($check)) {
		$errormsg=$errormsg."error§"._("Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r die Vorbesprechung ein!")."§";
		$check=FALSE;			
	} else
		$check=TRUE;

	if ((($vor_stunde > 24) || ($vor_end_stunde > 24) || ($vor_minute > 59) || ($vor_end_minute > 60)) && ($check)) {
		$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r die Vorbesprechung ein!")."§";
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
	if (($adm_jahr>0) && ($adm_jahr<100))
		 $adm_jahr=$adm_jahr+2000;
	
	if ($adm_monat == _("mm")) $adm_monat=0;
	if ($adm_tag == _("tt")) $adm_tag=0;
	if ($adm_jahr == _("jjjj")) $adm_jahr=0;	
	if ($adm_stunde == _("hh")) $adm_stunde=0;
	if ($adm_minute == _("mm")) $adm_minute=0;
	

	if (($adm_monat) && ($adm_tag) && ($adm_jahr))
		if ($adm_stunde== _("hh")) {
			if ($sem_create_data["sem_admission"] == 1)
				$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Werte f&uuml;r das Losdatum ein!")."§"; 
			else
				$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Werte f&uuml;r das Enddatum der Kontingentierung ein!")."§"; 
			$check=FALSE;
		} else
			$check=TRUE;

	settype($adm_stunde, "integer");
	settype($adm_minute, "integer");

	if ((!checkdate($adm_monat, $adm_tag, $adm_jahr) && ($adm_monat) && ($adm_tag) && ($adm_jahr)) && ($check)) {
		if ($sem_create_data["sem_admission"] == 1)
			$errormsg=$errormsg."error§"._("Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r das Losdatum ein!")."§";
		else
			$errormsg=$errormsg."error§"._("Bitte geben Sie ein g&uuml;ltiges Datum f&uuml;r das Enddatum der Kontingentierung ein!")."§";		
		$check=FALSE;			
	} else
		$check=TRUE;

	if ((($adm_stunde > 24) || ($adm_minute > 59)) && ($check)) {
		if ($sem_create_data["sem_admission"] == 1)	
			$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das Losdatum ein!")."§";
		else
			$errormsg=$errormsg."error§"._("Bitte geben Sie g&uuml;ltige Zeiten f&uuml;r das Enddatum der Kontingentierung ein!")."§";		
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
		
	//Datum fuer ersten Termin umwandeln. Checken muessen wir es auch leider direkt hier, da wir es sonst nicht umwandeln duerfen
	if (($jahr>0) && ($jahr<100))
		 $jahr=$jahr+2000;

	if ($monat == _("mm")) $monat=0;
	if ($tag == _("tt")) $tag=0;
	if ($jahr == _("jjjj")) $jahr=0;	

	if ((!checkdate($monat, $tag, $jahr)) && ($monat) && ($tag) && ($jahr))
		{
		$errormsg=$errormsg."error§"._("Bitte geben Sie ein g&uuml;ltiges Datum ein!")."§";
		$sem_create_data["sem_start_termin"] = -1;
		}
	else {
	 	$sem_create_data["sem_start_termin"] = mktime($stunde,$minute,0,$monat,$tag,$jahr);
		$sem_create_data["metadata_termin"]["start_termin"] = $sem_create_data["sem_start_termin"];
		//check overlaps...
		if ($RESOURCES_ENABLE) {
			$checkResult = $resAssign->changeMetaAssigns($sem_create_data["metadata_termin"], $sem_create_data["sem_start_time"], $sem_create_data["sem_duration_time"],TRUE);
		}
	 }
	}	 


if ($form==7)
	{
	$sem_create_data["sem_literat"]=$sem_literat;
	$sem_create_data["sem_links"]=$sem_links;
	}

//Check auf korrekte Eingabe und Sprung in naechste Level, hier auf Schritt 2
if ($cmd_b_x)
	{
	if (($sem_create_data["sem_duration_time"]<0) && ($sem_create_data["sem_duration_time"] != -1))
		{ 
		$level=3;
		$errormsg=$errormsg."error§"._("Das Endsemester darf nicht vor dem Startsemester liegen. Bitte &auml;ndern Sie die entsprechenden Einstellungen!")."§";
		}
	if (strlen($sem_create_data["sem_name"]) <3)
		{
		$level=1; //wir bleiben auf der ersten Seite
		$errormsg=$errormsg."error§"._("Bitte geben Sie einen g&uuml;ltigen Namen f&uuml;r die Veranstaltung ein!")."§";
		}
	if (!$sem_create_data["sem_inst_id"])
		{
		$level=1;
		$errormsg=$errormsg.sprintf ("error§"._("Da Ihr Account keiner Einrichtung zugeordnet ist, k&ouml;nnen Sie leider noch keine Veranstaltung anlegen. Bitte wenden Sie sich an den/die zust&auml;ndigeN AdministratorIn der Einrichtung oder einen der %sAdministratoren%s des Systems!")."§", "<a href=\"impressum.php?view=ansprechpartner\">", "</a>");
		}
	if (($sem_create_data["sem_turnout"] < 1) && ($sem_create_data["sem_admission"]))
 		{
		$level=1;
		$errormsg=$errormsg."error§"._("Wenn Sie die Teilnahmebeschr&auml;nkung benutzen wollen, m&uuml;ssen Sie wenigstens einen Teilnehmer zulassen.§")."";
		$sem_create_data["sem_turnout"] =1;
		}
	
	if (!$errormsg)
		$level=2;
	}

//delete Tutoren/Dozenten
if ($delete_doz) {
	unset($sem_create_data["sem_doz"][get_userid($delete_doz)]);
	$level=2;	
}

if ($delete_tut) {
	unset($sem_create_data["sem_tut"][get_userid($delete_tut)]);
	$level=2;	
}

if (($send_doz_x) && (!$reset_search_x)) {
	$sem_create_data["sem_doz"][get_userid($add_doz)]=TRUE;
	$level=2;	
}

if (($send_tut_x) && (!$reset_search_x)) {
	$sem_create_data["sem_tut"][get_userid($add_tut)]=TRUE;
	$level=2;	
}

if (($search_doz_x) || ($search_tut_x) || ($reset_search_x) || $sem_bereich_do_search_x) {
	$level=2;
}

//wenn alles stimmt, Sprung auf Schritt 3
if ($cmd_c_x)
	{
	if (is_array($sem_create_data["sem_tut"]))
		foreach ($sem_create_data["sem_tut"] as $key=>$val)
			if ($sem_create_data["sem_doz"][$key])
				$badly_dozent_is_tutor=TRUE;
	if ($badly_dozent_is_tutor) {
		$level=2; //wir bleiben auf der zweiten Seite
		$errormsg=$errormsg."error§"._("Sie d&uuml;rfen dieselben DozentInnen nicht gleichzeitig als TutorInnen eintragen!")."§";
	}
	
 	if (sizeof($sem_create_data["sem_doz"])==0)
		{
		$level=2; //wir bleiben auf der zweiten Seite
		$errormsg=$errormsg."error§"._("Bitte geben Sie mindestens einen Dozent oder eine Dozentin f&uuml;r die Veranstaltung an!")."§";
		}
	elseif ((!$perm->have_perm("root")) && (!$perm->have_perm("admin")))
		{
		if (!$sem_create_data["sem_doz"][$user_id]) {
			$level=2;
			$errormsg=$errormsg."error§"._("Sie m&uuml;ssen wenigstens sich selbst als DozentIn f&uuml;r diese Veranstaltung angeben! Der Eintrag wird automatisch gesetzt.")."§";
			$sem_create_data["sem_doz"][$user_id]=TRUE;
			}
		}
	if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"]) {
		if (sizeof($sem_create_data["sem_bereich"])==0)
			{
			$level=2;
			$errormsg=$errormsg."error§"._("Bitte geben Sie mindestens einen Studienbereich f&uuml;r die Veranstaltung an!")."§";
			}
		else
			{
			if ($false_mark)
				{
				$level=2;
				$errormsg=$errormsg."error§"._("Sie haben eine oder mehrere Fach&uuml;berschriften (unterstrichen) ausgew&auml;hlt. Diese dienen nur der Orientierung und k&ouml;nnen nicht ausgew&auml;hlt werden!")."§";
				}
			}
		}
	if (($sem_create_data["sem_sec_schreib"]) <($sem_create_data["sem_sec_lese"]))
		{
		$level=2; //wir bleiben auf der zweiten Seite
		$errormsg=$errormsg."error§"._("Es macht keinen Sinn, die Sicherheitsstufe f&uuml;r den Lesezugriff h&ouml;her zu setzen als f&uuml;r den Schreibzugriff!")."§";
		}
	if (!$errormsg) {
		if ($sem_create_data["term_art"]== -1)
			$level=4;
		else
			$level=3;
	} else
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
			$tmp_term_turnus_date[]=$sem_create_data["term_turnus_date"][$i];
			$tmp_term_turnus_start_stunde[]=$sem_create_data["term_turnus_start_stunde"][$i];
			$tmp_term_turnus_start_minute[]=$sem_create_data["term_turnus_start_minute"][$i]; 
			$tmp_term_turnus_end_stunde[]=$sem_create_data["term_turnus_end_stunde"][$i]; 
			$tmp_term_turnus_end_minute[]=$sem_create_data["term_turnus_end_minute"][$i]; 
			$tmp_term_turnus_resource_id[]=$sem_create_data["term_turnus_resource_id"][$i]; 
			$tmp_term_turnus_room[]=$sem_create_data["term_turnus_room"][$i]; 
			}
	$sem_create_data["term_turnus_date"]=$tmp_term_turnus_date;
	$sem_create_data["term_turnus_start_stunde"]=$tmp_term_turnus_start_stunde;
	$sem_create_data["term_turnus_start_minute"]=$tmp_term_turnus_start_minute;
	$sem_create_data["term_turnus_end_stunde"]=$tmp_term_turnus_end_stunde;
	$sem_create_data["term_turnus_end_minute"]=$tmp_term_turnus_end_minute;
	$sem_create_data["term_turnus_resource_id"]=$tmp_term_turnus_resource_id;
	$sem_create_data["term_turnus_room"]=$tmp_term_turnus_room;
	
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
			$tmp_term_resource_id[]=$sem_create_data["term_resource_id"][$i]; 
			$tmp_term_room[]=$sem_create_data["term_room"][$i]; 
			}
	$sem_create_data["term_tag"]=$tmp_term_tag;
	$sem_create_data["term_monat"]=$tmp_term_monat;
	$sem_create_data["term_jahr"]=$tmp_term_jahr;
	$sem_create_data["term_start_stunde"]=$tmp_term_start_stunde;
	$sem_create_data["term_start_minute"]=$tmp_term_start_minute;
	$sem_create_data["term_end_stunde"]=$tmp_term_end_stunde;
	$sem_create_data["term_end_minute"]=$tmp_term_end_minute;
	$sem_create_data["term_resource_id"]=$tmp_term_resource_id;
	$sem_create_data["term_room"]=$tmp_term_room;
	
	$sem_create_data["term_count"]--;
	$level=3;
	}


//Termin-Metaddaten-Check, wenn alles stimmt, Sprung auf Schritt 4
if ($cmd_d_x)
	{
	if ($sem_create_data["term_art"]==0)
		{
		for ($i=0; $i<$sem_create_data["turnus_count"]; $i++)
			if ((($sem_create_data["term_turnus_start_stunde"][$i]) || ($sem_create_data["term_turnus_end_stunde"][$i])))
				{
				if ((($sem_create_data["term_turnus_start_stunde"][$i]) && (!$sem_create_data["term_turnus_end_stunde"][$i])) || ((!$sem_create_data["term_turnus_start_stunde"][$i]) && ($sem_create_data["term_turnus_end_stunde"][$i])))
						{
						if (!$just_informed)
							$errormsg=$errormsg."error§"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der regul&auml;ren Termine aus!")."§";	
						$just_informed=TRUE;
						}
				if ((($sem_create_data["term_turnus_start_stunde"][$i]>23) || ($sem_create_data["term_turnus_start_stunde"][$i]<0))  ||  (($sem_create_data["term_turnus_start_minute"][$i]>59) || ($sem_create_data["term_turnus_start_minute"][$i]<0))  ||  (($sem_create_data["term_turnus_end_stunde"][$i]>23) ||($sem_create_data["term_turnus_end_stunde"][$i]<0))  || (($sem_create_data["term_turnus_end_minute"][$i]>59) || ($sem_create_data["term_turnus_end_minute"][$i]<0)))
						{
						if (!$just_informed3)
							$errormsg=$errormsg."error§"._("Sie haben eine ung&uuml;ltige Zeit eingegeben. Bitte korrigieren sie dies!")."§";	
						$just_informed3=TRUE;
						}
				if (mktime($sem_create_data["term_turnus_start_stunde"][$i], $sem_create_data["term_turnus_start_minute"][$i], 0, 1, 1, 2001) > mktime($sem_create_data["term_turnus_end_stunde"][$i], $sem_create_data["term_turnus_end_minute"][$i], 0, 1, 1, 2001)) 
					if ((!$just_informed5) && (!$just_informed)) {
						$errormsg=$errormsg."error§"._("Der Endzeitpunkt eines regul&auml;ren Termins muss nach dem jeweiligen Startzeitpunkt liegen!")."§";
						$just_informed5=TRUE;				
					}
				}
				elseif(!$just_informed4) 
					if ((!$sem_create_data["term_turnus_start_stunde"][$i]) && (!$sem_create_data["term_turnus_start_minute"][$i]) && (!$sem_create_data["term_turnus_end_stunde"][$i]) && (!$sem_create_data["term_turnus_end_minute"][$i]))
						$empty_fields++;
					else
						{
						$errormsg=$errormsg."error§"._("Sie haben nicht alle Felder der regul&auml;ren Termine ausgef&uuml;llt. Bitte f&uuml;llen Sie alle Felder aus!")."§";
						$just_informed4=TRUE;
						}
					
			//check overlaps...
			if ($RESOURCES_ENABLE) {
				$checkResult = $resAssign->changeMetaAssigns($sem_create_data["metadata_termin"], $sem_create_data["sem_start_time"], $sem_create_data["sem_duration_time"],TRUE);
			}
		}
	else {
		for ($i=0; $i<$sem_create_data["term_count"]; $i++)
			if ((($sem_create_data["term_start_stunde"][$i]) || ($sem_create_data["term_end_stunde"][$i])) && (($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_jahr"][$i]))) {
				if ((($sem_create_data["term_start_stunde"][$i]) && (!$sem_create_data["term_end_stunde"][$i])) || ((!$sem_create_data["term_start_stunde"][$i]) && ($sem_create_data["term_end_stunde"][$i])))
						{
						if (!$just_informed)
							$errormsg=$errormsg."error§"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der jeweiligen Termine aus!")."§";	
						$just_informed=TRUE;
						}
				if (!checkdate ($sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]))
						{
						if (!$just_informed2)
							$errormsg=$errormsg."error§"._("Sie haben ein ung&uuml;ltiges Datum eingegeben. Bitte korrigieren Sie das Datum!")."§";
						$just_informed2=TRUE;
						}
				if ((($sem_create_data["term_start_stunde"][$i]>23) || ($sem_create_data["term_start_stunde"][$i]<0))  ||  (($sem_create_data["term_start_minute"][$i]>59) || ($sem_create_data["term_start_minute"][$i]<0))  ||  (($sem_create_data["term_end_stunde"][$i]>23) ||($sem_create_data["term_end_stunde"][$i]<0))  || (($sem_create_data["term_end_minute"][$i]>59) || ($sem_create_data["term_end_minute"][$i]<0)))
						{
						if (!$just_informed3)
							$errormsg=$errormsg."error§"._("Sie haben eine ung&uuml;ltige Zeit eingegeben, bitte korrigieren sie dies!")."§";	
						$just_informed3=TRUE;
						}
				if (mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, 1, 1, 2001) > mktime($sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i], 0, 1, 1, 2001)) 
					if ((!$just_informed5) && (!$just_informed)) {
						$errormsg=$errormsg."error§"._("Die Endzeitpunkt der Termine muss nach dem jeweiligen Startzeitpunkt liegen!")."§";
						$just_informed5=TRUE;				
					}
				//check overlaps
				if ((!$errormsg) && ($RESOURCES_ENABLE)) {
					$tmp_chk_date=mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
					$tmp_chk_end_time=mktime($sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
					$checkResult = array_merge($checkResult, $resAssign->insertDateAssign(FALSE, $sem_create_data["term_resource_id"][$i], $tmp_chk_date, $tmp_chk_end_time, TRUE));
				}
			}
			elseif(!$just_informed4) 
				if ((!$sem_create_data["term_tag"][$i]) && (!$sem_create_data["term_monat"][$i]) && (!$sem_create_data["term_jahr"][$i]) && (!$sem_create_data["term_start_stunde"][$i]) && (!$sem_create_data["term_start_minute"][$i]) && (!$sem_create_data["term_end_stunde"][$i]) && (!$sem_create_data["term_end_minute"][$i]))
					$empty_fields++;
				else {
					$errormsg=$errormsg."error§"._("Sie haben nicht alle Felder bei der Termineingabe ausgef&uuml;llt. Bitte f&uuml;llen Sie alle Felder aus!")."§";
					$just_informed4=TRUE;
					}
	}

	if ($sem_create_data["sem_vor_termin"] == -1);
	else {
		if ((($vor_stunde) && (!$vor_end_stunde)) || ((!$vor_stunde) && ($vor_end_stunde)))
			$errormsg=$errormsg."error§"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit der Vorbesprechung aus!")."§";	

		//check overlaps...
		if ($RESOURCES_ENABLE) {
			$checkResult = array_merge($checkResult, $resAssign->insertDateAssign(FALSE, $sem_create_data["sem_vor_resource_id"], $sem_create_data["sem_vor_termin"], $sem_create_data["sem_vor_end_termin"],TRUE));
		}
	}
	
	//create overlap array
	if (is_array($checkResult)) {
		$overlaps_detected=FALSE;
		foreach ($checkResult as $key=>$val)
			if ($val["overlap_assigns"] == TRUE)
					$overlaps_detected[] = array("resource_id"=>$val["resource_id"], "overlap_assigns"=>$val["overlap_assigns"]);
	}
	
	//generate bad msg if overlaps exists
	if ($overlaps_detected) {
		$errormsg=$errormsg."error§"._("Folgende gew&uuml;nschte Raumbelegungen &uuml;berschneiden sich mit bereits vorhandenen Belegungen. Bitte &auml;ndern Sie die R&auml;ume oder Zeiten!");
		$i=0;
		foreach ($overlaps_detected as $val) {
			$errormsg.="<br /><font size=\"-1\" color=\"black\">".htmlReady(getResourceObjectName($val["resource_id"])).": ";
			//show the first overlap
			list(, $val2) = each($val["overlap_assigns"]);
			$errormsg.=date("d.m, H:i",$val2["begin"])." - ".date("H:i",$val2["end"]);
			if (sizeof($val) >1)
				$errormsg.=", ... ("._("und weitere").")";
			$errormsg.=sprintf (", <a target=\"new\" href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav&start_time=%s\">"._("Raumplan anzeigen")."</a> ",$val["resource_id"], $val2["begin"]);
			$i++;
		}
		$errormsg.="</font>§";
		unset($overlaps_detected);		
	}
	
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
		if ((!$sem_create_data["sem_admission_ratios_changed"]) && (!$sem_add_ratio) && (!$cmd_c_x) && (!$cmd_e_x)) {//User hat nichts veraendert oder neuen Studiengang mit Wert geschickt, wir koennen automatisch rechnen
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
					$errormsg.= "error§". _("Die Werte der einzelnen Kontigente &uuml;bersteigen 100%. Bitte &auml;ndern Sie die Kontigente!") . "§";	
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

		if (($sem_create_data["sem_pw"]=="") || ($sem_create_data["sem_pw"] == md5("")))
	          	{
        	  	$errormsg=$errormsg."error§"._("Sie haben kein Passwort eingegeben! Bitte geben Sie ein Passwort ein!")."§";
          		$level=4;
	          	}
        	  elseif (isset($check_pw) AND $sem_create_data["sem_pw"] != $check_pw)
     			{
			$errormsg=$errormsg."error§"._("Das eingegebene Passwort und das Passwort zur Best&auml;tigung stimmen nicht &uuml;berein!")."§";
     			$sem_create_data["sem_pw"] = "";
     			$level=4;
	          	}
	}
	
	//Ende der Anmeldung checken
	if ($sem_create_data["sem_admission"]) {
		if ($sem_create_data["sem_admission_date"] == -1) 
			if ($sem_create_data["sem_admission"] == 1)
				$errormsg.= "error§"._("Bitte geben Sie einen Termin f&uuml;r das Losdatum an!")."§";
			else
				$errormsg.= "error§"._("Bitte geben Sie einen Termin f&uuml;r das Enddatum der Kontingentierung an!")."§";
		elseif ($sem_create_data["term_art"]==0){
			$tmp_first_date=veranstaltung_beginn ($sem_create_data["term_art"], $sem_create_data["sem_start_time"], $sem_create_data["term_start_woche"], $sem_create_data["sem_start_termin"], $sem_create_data["metadata_termin"]["turnus_data"], "int");
			if (($sem_create_data["sem_admission_date"] > $tmp_first_date) && ($tmp_first_date >0)){
				if ($tmp_first_date > 0)
					if ($sem_create_data["sem_admission"] == 1)
						$errormsg.= sprintf ("error§"._("Das Losdatum liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern Sie das Losdatum!")."§", date ("d.m.Y", $tmp_first_date));
					else
				$level=4;$errormsg.= sprintf ("error§"._("Das Enddatum der Kontingentierung liegt nach dem ersten Veranstaltungstermin am %s. Bitte &auml;ndern Sie das Enddatum der Kontingentierung!")."§", date ("d.m.Y", $tmp_first_date));
			}
		} elseif (($sem_create_data["sem_admission_date"] > $sem_create_data["term_first_date"]) && ($sem_create_data["term_first_date"])) {
				if ($sem_create_data["sem_admission"] == 1)
					$errormsg.=sprintf ("error§"._("Das Losdatum liegt nach dem eingetragenen Veranstaltungsbeginn am %s. Bitte &auml;ndern Sie das Losdatum!")."§", date ("d.m.Y", $sem_create_data["term_first_date"]));	
				else
					$errormsg.=sprintf ("error§"._("Das Enddatum der Kontingentierung liegt nach dem eingetragenen Veranstaltungsbeginn am %s. Bitte &auml;ndern Sie das Enddatum der Kontingentierung!")."§", date ("d.m.Y", $sem_create_data["term_first_date"]));	
				$level=4;
		} 
		if (($sem_create_data["sem_admission_date"] < time()) && ($sem_create_data["sem_admission_date"] != -1)) {
				if ($sem_create_data["sem_admission"] == 1)
					$errormsg.=sprintf ("error§"._("Das Losdatum liegt in der Vergangenheit. Bitte &auml;ndern sie das Losdatum!")."§");	
				else
					$errormsg.=sprintf ("error§"._("Das Enddatum der Kontingentierung liegt in der Vergangenheit. Bitte &auml;ndern Sie das Enddatum der Kontingentierung!")."§");					
				$level=4;
		} elseif (($sem_create_data["sem_admission_date"] < (time() + (24 * 60 *60))) && ($sem_create_data["sem_admission_date"] != -1)) {
				if ($sem_create_data["sem_admission"] == 1)
					$errormsg.=sprintf ("error§"._("Das Losdatum liegt zu nah am aktuellen Datum. Bitte &auml;ndern Sie das Losdatum!")."§");	
				else
					$errormsg.=sprintf ("error§"._("Das Enddatum der Kontingentierung liegt zu nah am aktuellen Datum. Bitte &auml;ndern sie das Enddatum der Kontingentierung!")."§");					
				$level=4;
		}
	}

	//Erster Termin wenn angegeben werden soll muss er auch da sein
	if (($sem_create_data["sem_start_termin"] == -1) && ($sem_create_data["term_start_woche"] ==-1))
		$errormsg=$errormsg."error§"._("Bitte geben Sie einen ersten Termin an!")."§";	
	else
		if ((($stunde) && (!$end_stunde)) || ((!$stunde) && ($end_stunde)))
			$errormsg=$errormsg."error§"._("Bitte f&uuml;llen Sie beide Felder f&uuml;r Start- und Endzeit des ersten Termins aus!")."§";	

	//create overlap array
	if (is_array($checkResult)) {
		$overlaps_detected=FALSE;
		foreach ($checkResult as $key=>$val)
			if ($val["overlap_assigns"] == TRUE)
					$overlaps_detected[] = array("resource_id"=>$val["resource_id"], "overlap_assigns"=>$val["overlap_assigns"]);
	}
	
	//generate bad msg if overlaps exists
	if ($overlaps_detected) {
		$errormsg=$errormsg."error§"._("Folgende gew&uuml;nschte Raumbelegungen &uuml;berschneiden sich mit bereits vorhandenen Belegungen. Bitte &auml;ndern Sie die R&auml;ume oder Zeiten!");
		$i=0;
		foreach ($overlaps_detected as $val) {
			$errormsg.="<br /><font size=\"-1\" color=\"black\">".htmlReady(getResourceObjectName($val["resource_id"])).": ";
			//show the first overlap
			list(, $val2) = each($val["overlap_assigns"]);
			$errormsg.=date("d.m, H:i",$val2["begin"])." - ".date("H:i",$val2["end"]);
			if (sizeof($val) >1)
				$errormsg.=", ... ("._("und weitere").")";
			$errormsg.=sprintf (", <a target=\"new\" href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav&start_time=%s\">"._("Raumplan anzeigen")."</a> ",$val["resource_id"], $val2["begin"]);
			$i++;
		}
		$errormsg.="</font>§";
		unset($overlaps_detected);		
	}


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
		$errormsg .= "error§"._("Sie haben keine Berechtigung Veranstaltungen anzulegen Um eine Veranstaltung anlegen zu k&ouml;nnen, ben&ouml;tigen Sie mindestens den globalen Status &raquo;Dozent&laquo;. Bitte kontaktieren Sie den/die f&uuml;r Sie zust&auml;ndigeN AdministratorIn.")."§";
		$run = FALSE;
		}
	if (!$perm->have_studip_perm("dozent",$sem_create_data["sem_inst_id"]))
		{
		$errormsg .= "error§"._("Sie haben keine Berechtigung f&uuml;r diese Einrichtung Veranstaltungen anzulegen.")."§";
			$run = FALSE;
		}

	//Nochmal Checken, ob wirklich alle Daten vorliegen. Kann zwar eigentlich hier nicht mehr vorkommen, aber sicher ist sicher.
	if (empty($sem_create_data["sem_name"]))
		{
		$errormsg  .= "error§"._("Bitte geben Sie einen Namen f&uuml;r die Veranstaltung ein!")."§";
		$run = FALSE;
    		}

	if (empty($sem_create_data["sem_inst_id"]))
		{
		$errormsg .= "error§"._("Bitte geben Sie eine Heimat-Einrichtung f&uuml;r die Veranstaltung an!")."§";
		$run = FALSE;
		}
	if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])  {
		if (empty($sem_create_data["sem_bereich"]))
			{
			$errormsg .= "error§"._("Bitte geben Sie wenigstens einen Studienbereich f&uuml;r die Veranstaltung an!")."§";
			$run = FALSE;
			}
		}

    	if ($perm->have_perm("admin") && empty($sem_create_data["sem_doz"]))
    		{
    		$errormsg .= "error§"._("Bitte geben Sie wenigstens eine Dozentin oder einen Dozenten f&uuml;r die Veranstaltung an!")."§";
      		$run = FALSE;
      		}

      	if ($run)
		{
		//Seminar_id erzeugen und Seminar eintrag
		$sem_create_data["sem_id"]=md5(uniqid($hash_secret));
    		
		//Termin-Metadaten-Array zusammenmatschen zum besseren speichern in der Datenbank
		if ($sem_create_data["term_art"] == -1)
			$serialized_metadata='';
		else
			$serialized_metadata=mysql_escape_string(serialize($sem_create_data["metadata_termin"]));

		//for admission it have to always 3
		if ($sem_create_data["sem_admission"]) {
			$sem_create_data["sem_sec_lese"]=3;
			$sem_create_data["sem_sec_schreib"]=3;
		}

		//set temporary entry (for skip dates field) to the right value
		if ($sem_create_data["term_art"]==-1) 
			$sem_create_data["term_art"]=1;

		if ($Schreibzugriff < $Lesezugriff) // hier wusste ein Dozent nicht, was er tat
			$Schreibzugriff = $Lesezugriff;
		
						
		$query = "INSERT INTO seminare values('".
				$sem_create_data["sem_id"]."', '".			//Feld Seminar_id 
				$sem_create_data["sem_nummer"]."', '".			//Feld VeranstaltungsNummer
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
				$sem_create_data["sem_duration_time"]."', '".		//Feld duration_time 
				$sem_create_data["sem_art"]."', '".			//Feld art 
				$sem_create_data["sem_teiln"]."', '".			//Feld teilnehmer 
				$sem_create_data["sem_voraus"]."', '".			//Feld vorrausetzungen 
				$sem_create_data["sem_orga"]."', '".			//Feld lernorga 
				$sem_create_data["sem_leistnw"]."', '".			//Feld leistungsnachweis 
				$serialized_metadata."', '".				//Feld metadata_dates 
				time()."', '".						//Feld mkdate 
				time()."', '".						//Feld chdate 
				$sem_create_data["sem_ects"]."', '".			//Feld ects
				$sem_create_data["sem_admission_date"]."', '".		//Feld admission_endtime 
				$sem_create_data["sem_turnout"]."', '".			//Feld admission_turnout 
				"', '".							//Feld admission_binding
				$sem_create_data["sem_admission"]."', '".		//Feld admission_type 
				"0' ,'".						//Feld admission_selection_take_place
				"0' ,'".						//Feld showscore
				"')";							//Feld modules

		//und jetzt wirklich eintragen
		if (!$sem_create_data["sem_entry"]) {
			$db->query($query);
			if ($db->affected_rows() == 0) {
				$errormsg .= "error§"._("<b>Fehler:</b>")." $query §";
				$successful_entry=0;
				$sem_create_data["sem_entry"]=FALSE;
				die;
    			} else {
    				//completing the internal settings....
    				$successful_entry=1;
				$sem_create_data["sem_entry"]=TRUE;
				openSem($sem_create_data["sem_id"]); //open Veranstaltung to administrate in the admin-area
				$links_admin_data["referred_from"]="assi";
				$links_admin_data["assi"]=FALSE; //protected Assi-mode off
				
				//write the default module-config
				$Modules = new Modules;
				$Modules->writeDefaultStatus($sem_create_data["sem_id"]);

    				//update/insert the assigned roomes
    				if ($RESOURCES_ENABLE) {
    					$updateAssign = new VeranstaltungResourcesAssign($sem_create_data["sem_id"]);
    					$updateResult=$updateAssign->updateAssign();

	    				//are there overlaps, in the meanwhile since the check at step 3? In this case the sem is regular, we have to touch the metadata
					if ((is_array($updateResult)) && ($sem_create_data["term_art"] != -1)) {
						$overlaps_detected=FALSE;
						foreach ($updateResult as $key=>$val)
							if ($val["overlap_assigns"] == TRUE) {
								list($key2, $val2) = each($val["overlap_assigns"]);
								$begin = $val2["begin"];
								$end = $val2["end"];
								$resource_id = $val["resource_id"];
								foreach ($sem_create_data["metadata_termin"]["turnus_data"] as $key3 =>$val3) {
									$day = date("w", $begin);
									if (!$day )
										$day = 7;
									if (($val3["day"] == $day) && ($val3["start_stunde"] == date("G", $begin)) && ($val3["start_minute"] == date("i", $begin)) && ($val3["end_stunde"] == date("G", $end)) && ($val3["end_minute"] == date("i", $end)) && ($val["resource_id"] == $resource_id)) {
										$sem_create_data["metadata_termin"]["turnus_data"][$key3]["resource_id"]='';
										$sem_create_data["metadata_termin"]["turnus_data"][$key3]["room"]='';
										$metadata_changed = TRUE;
									}
								}
							}
						//ok, we have a need to update the metadata again...
						if ($metadata_changed) {
							$serialized_metadata=mysql_escape_string(serialize($sem_create_data["metadata_termin"]));
							$query = sprintf ("UPDATE seminare SET metadata_dates = '%s' WHERE Seminar_id = '%s' ", $serialized_metadata, $sem_create_data["sem_id"]);
							$db->query($query);
						}
					}
    				}
			}
		} else {
			$errormsg .= "error§"._("<b>Fehler:</b> Die Veranstaltung wurde schon eingetragen!")."§";
    			$successful_entry=2;			
		}

		if (is_array($sem_create_data["sem_doz"]))  // alle ausgewählten Dozenten durchlaufen
			{
			$self_included = FALSE;
			$count_doz=0;
			foreach ($sem_create_data["sem_doz"] as $key=>$val)
				{
				$group=select_group($temp_array, $sem_create_data["sem_start_time"]);
				
				if ($key == $user_id)
					$self_included=TRUE;
				$query = "insert into seminar_user  values('".
					$sem_create_data["sem_id"]."', '".
					$key."', 'dozent', '$group', '', '".time()."')";
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
			foreach ($sem_create_data["sem_tut"] as $key=>$val)
				{
				$group=select_group($temp_array, $sem_create_data["sem_start_time"]);
				
				$query = "SELECT user_id FROM seminar_user WHERE Seminar_id = '".
					$sem_create_data["sem_id"]."' AND user_id ='$key'";
				$db4->query($query);
				if ($db4->next_record())	// User schon da, kann beim Anlegen nur als Dozent sein, also ignorieren
					;
				else // User noch nicht da
					{
					$query = "insert into seminar_user  values('".
						$sem_create_data["sem_id"]."', '".
						$key."', 'tutor', '$group', '', '".time()."')";
					$db3->query($query);			     // Tutor eintragen
						if ($db3->affected_rows() >= 1)
							$count_tut++;
					}
				}
			}

		//Eintrag der Studienbereiche
		if (is_array($sem_create_data["sem_bereich"])) {
			$st_search->seminar_id = $sem_create_data["sem_id"];
			$st_search->selected = array();
			$st_search->insertSelectedRanges($sem_create_data["sem_bereich"]);
			$count_bereich = $st_search->num_inserted;
			}
			
		//Eintrag der zugelassen Studiengänge
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
		if ($sem_create_data["modules_list"]["forum"])
			CreateTopic(_("Allgemeine Diskussionen"), get_fullname($user_id), _("Hier ist Raum für allgemeine Diskussionen"), 0, 0, $sem_create_data["sem_id"]);
		
		//Standard Ordner im Foldersystem anlegen, damit Studis auch ohne Zutun des Dozenten Uploaden k&ouml;nnen
		if ($sem_create_data["modules_list"]["documents"])
			$db3->query("INSERT INTO folder SET folder_id='".md5(uniqid("sommervogel"))."', range_id='".$sem_create_data["sem_id"]."', user_id='".$user_id."', name='"._("Allgemeiner Dateiordner")."', description='"._("Ablage für allgemeine Ordner und Dokumente der Veranstaltung")."', mkdate='".time()."', chdate='".time()."'");
		
		//Vorbesprechung, falls vorhanden, in Termintabelle eintragen
		if ($sem_create_data["sem_vor_termin"] <>-1) {
			$termin_id=md5(uniqid($hash_secret));
			$mkdate=time();
			
			//if we have a resource_id, we flush the room name
			if ($sem_create_data["sem_vor_resource_id"])
				$sem_create_data["sem_vor_raum"]='';
	
			$db->query("INSERT INTO termine SET termin_id = '$termin_id', range_id='".$sem_create_data["sem_id"]."', autor_id='$user_id', content ='Vorbesprechung', date='".$sem_create_data["sem_vor_termin"]."', mkdate='$mkdate', chdate='$mkdate', date_typ='2', topic_id=0, end_time='".$sem_create_data["sem_vor_end_termin"]."', raum='".$sem_create_data["sem_vor_raum"]."'");
	
			//update/insert the assigned roomes
			if ($RESOURCES_ENABLE && $db->affected_rows()) {
				$updateAssign = new VeranstaltungResourcesAssign($sem_create_data["sem_id"]);
				$updateResult = array_merge($updateResult, $updateAssign->insertDateAssign($termin_id, $sem_create_data["sem_vor_resource_id"]));
			}
		}
		
		//Wenn der Veranstaltungs-Termintyp Blockseminar ist, dann tragen wir diese Termine auch schon mal ein
		if ($sem_create_data["term_art"] ==1) {
			for ($i=0; $i<$sem_create_data["term_count"]; $i++)
				if (($sem_create_data["term_tag"][$i]) && ($sem_create_data["term_monat"][$i]) && ($sem_create_data["term_jahr"][$i]) && ($sem_create_data["term_start_stunde"][$i]) && ($sem_create_data["term_end_stunde"][$i])) {
					$termin_id=md5(uniqid($hash_secret));
					$mkdate=time();
					$date=mktime($sem_create_data["term_start_stunde"][$i], $sem_create_data["term_start_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
					$end_time=mktime($sem_create_data["term_end_stunde"][$i], $sem_create_data["term_end_minute"][$i], 0, $sem_create_data["term_monat"][$i], $sem_create_data["term_tag"][$i], $sem_create_data["term_jahr"][$i]);
					
					//if we have a resource_id, we flush the room name
					if ($sem_create_data["term_resource_id"][$i])
						$sem_create_data["term_room"][$i]='';

					$db->query("INSERT INTO termine SET termin_id = '$termin_id', range_id='".$sem_create_data["sem_id"]."', autor_id='$user_id', content ='".($i+1).". Seminartermin (ohne Titel)', date='$date', mkdate='$mkdate', chdate='$mkdate', date_typ='1', topic_id=0, end_time='$end_time', raum='".$sem_create_data["term_room"][$i]."' ");
		
					//update/insert the assigned roomes
					if ($RESOURCES_ENABLE && $db->affected_rows()) {
						$updateAssign = new VeranstaltungResourcesAssign($sem_create_data["sem_id"]);
						$updateResult = array_merge($updateResult, $updateAssign->insertDateAssign($termin_id, $sem_create_data["term_resource_id"][$i]));
					}
				}
		}

		}

	$level=6;
	}

//Nur der Form halber... es geht weiter zur Literaturliste
if ($cmd_g_x)
   	{
	if (!$sem_create_data["modules_list"]["literature"]) {
		header ("Location: admin_seminare1.php");
		die;
	}
	$level=7;
   	}

//Eintragen der Literatur und Links
if ($cmd_h_x)
	{
	if ($sem_create_data["lit_entry"]) {
		$db->query("UPDATE literatur SET literatur='".$sem_create_data["sem_literat"]."', links='".$sem_create_data["sem_links"]."', chdate='".time()."' WHERE literatur_id='".$sem_create_data["sem_lit_id"]."'");
	} else {
		$sem_create_data["sem_lit_id"]=md5(uniqid($hash_secret));
		$db->query("INSERT INTO literatur SET literatur_id='".$sem_create_data["sem_lit_id"]."', range_id='".$sem_create_data["sem_id"]."', user_id='$user_id', literatur='".$sem_create_data["sem_literat"]."', links='".$sem_create_data["sem_links"]."', mkdate='".time()."', chdate='".time()."' ");
	}
	if ($db->affected_rows()) {
		$sem_create_data["lit_entry"]=TRUE;
		if ($sem_create_data["modules_list"]["schedule"])
			header ("Location: admin_dates.php?assi=yes&ebene=sem&range_id=".$sem_create_data["sem_id"]);
		else
			header ("Location: admin_seminare1.php");
		die;
		}
	else
		{
		$errormsg .= "error§"._("Fehler! Der Eintrag konnte nicht erfolgreich vorgenommen werden!")."";
		$level=7;
		}
	}

//Gibt den aktuellen View an, brauchen wir in der Hilfe
$sem_create_data["level"]=$level;

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include "$ABSOLUTE_PATH_STUDIP/links_admin.inc.php";  		//Linkleiste fuer admins

?>
	<script type="text/javascript" language="javascript" src="md5.js"></script>

	<script type="text/javascript" language="javascript">
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
<?
//Before we start, let's decide the category (class) of the Veranstaltung
if (!$sem_create_data["sem_class"]) {
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent - Veranstaltungskategorie ausw&auml;hlen"); ?></b>
			</td>
		</tr>
		<?
		if ($errormsg) parse_msg($errormsg);
		?>
		<tr>
			<td class="blank" valign="top">
				<blockquote><br />
				<?=_("Willkommen beim Veranstaltungs-Assistenten. Der Veranstaltungs-Assistent wird Sie Schritt f&uuml;r Schritt durch die notwendigen Schritte zum Anlegen einer neuen Veranstaltung in Stud.IP leiten."); ?><br><br>
				<?=_("Bitte geben Sie zun&auml;chst an, welche Art von Veranstaltung Sie neu anlegen m&ouml;chten:"); ?>
				</blockquote>
			</td>
			<td class="blank" align="right" valign="top" rowspan="2">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/assistent.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan="1">&nbsp;
				<blockquote>
					<table cellpadding=0 cellspacing=2 width="90%" border="0">
					<?
					foreach ($SEM_CLASS as $key=>$val) {
						echo "<tr><td width=\"3%\" class=\"blank\"><a href=\"admin_seminare_assi.php?start_level=TRUE&class=$key\"><img src=\"pictures/forumrot.gif\" border=0 /></a><td>";
						echo "<td width=\"97%\" class=\"blank\"><a href=\"admin_seminare_assi.php?start_level=TRUE&class=$key\">".$val["name"]."</a><td></tr>";
						echo "<tr><td width=\"3%\" class=\"blank\">&nbsp; <td>";
						echo "<td width=\"97%\" class=\"blank\"><font size=-1>".$val["create_description"]."</font><td></tr>";
					}
					?>
					</table>
				</blockquote>
			</td>
		</tr>
	</table>
	<?
}

//Level 1: Hier werden die Grunddaten abgefragt.
elseif ((!$level) || ($level==1))
	{
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent - Schritt 1: Grunddaten"); ?></b>
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
				<?=_("Willkommen beim Veranstaltungs-Assistenten. Der Veranstaltungs-Assistent wird Sie nun Schritt f&uuml;r Schritt durch die notwendigen Schritte zum Anlegen einer neuen Veranstaltung in Stud.IP leiten."); ?><br><br>
				<b><?=_("Schritt 1: Grunddaten der Veranstaltung angeben"); ?></b><br><br />
				<font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands01.jpg" border="0">
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
							&nbsp; <input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_b">
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Name der Veranstaltung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <input type="text" name="sem_name" size=58 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_name"])) ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte geben Sie hier einen aussagekräftigen, aber möglichst knappen Titel für die Veranstaltung ein. Dieser Eintrag erscheint innerhalb Stud.IPs durchgehend zur Identifikation der Veranstaltung."), TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Untertitel:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <input type="text" name="sem_untert" size=58 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_untert"]))?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Der Untertitel ermöglicht eine genauere Beschreibung der Veranstaltung."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<?
					if (!$SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) {
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Veranstaltungsnummer:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="30%">
							&nbsp; <input type="text" name="sem_nummer" size=20 maxlength=32 value="<? echo  htmlReady(stripslashes($sem_create_data["sem_nummer"])) ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Fall Sie eine eindeutige Veranstaltungsnummer für diese Veranstaltung kennen, geben Sie diese bitte hier ein."), TRUE, TRUE) ?>
							>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("ECTS-Punkte:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="60%">
							&nbsp; <input type="text" name="sem_ects" size=6 maxlength=32 value="<? echo  htmlReady(stripslashes($sem_create_data["sem_ects"])) ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("ECTS-Kreditpunkte, die in dieser Veranstaltung erreicht werden können."), TRUE, TRUE) ?>
							>
						</td>
					</tr>						
					<?
					}
					if (!$SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) {
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Teilnahme- beschr&auml;nkung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=1>
 							&nbsp; <input type="RADIO" name="sem_admission" value=0 <? if (!$sem_create_data["sem_admission"]) echo checked?>>
 							<?=_("keine"); ?> &nbsp; <br />
							&nbsp; <input type="RADIO" name="sem_admission" value=2 <? if ($sem_create_data["sem_admission"]=="2") echo checked?>>
 							<?=_("nach Anmeldereihenfolge"); ?> <br />
 							&nbsp; <input type="RADIO" name="sem_admission" value=1 <? if ($sem_create_data["sem_admission"]=="1") echo checked?>>
 							<?=_("per Losverfahren"); ?>&nbsp; 
 							<img  src="./pictures/info.gif" 
 								<? echo tooltip(_("Sie können die Anzahl der Teilnehmenden beschränken. Möglich ist die Zulassung von Interessierten über das Losverfahren oder über die Reihenfolge der Anmeldung. Sie können später Angaben über zugelassene Teilnehmer machen."), TRUE, TRUE) ?>
							>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("maximale Teilnehmeranzahl:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="50%">
							&nbsp; <input type="int" name="sem_turnout" size=6 maxlength=5 value="<? echo $sem_create_data["sem_turnout"] ?>">
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Geben Sie hier die erwartete Teilnehmerzahl an. Stud.IP kann auf Wunsch für Sie ein Anmeldeverfahren starten, wenn Sie »Teilnahmebeschränkung: per Losverfahren / nach Anmeldereihenfolge« benutzen."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<?
					}
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Beschreibung/ Kommentar:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <textarea name="sem_desc" cols=58 rows=6><? echo htmlReady(stripslashes($sem_create_data["sem_desc"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Hier geben Sie bitte den eigentlichen Kommentartext der Veranstaltung (analog zum Vorlesungskommentar) ein."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Semester:"); ?>
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
								<? echo tooltip(_("Bitte geben Sie hier ein, welchem Semester die Veranstaltung zugeordnet werden soll."), TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Dauer:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="60%">
							&nbsp; <select name="sem_duration_time">
							<?
								if ($sem_create_data["sem_duration_time"] == 0)
									echo "<option value=0 selected>"._("1 Semester")."</option>";
								else
									echo "<option value=0>"._("1 Semester")."</option>";
								$i=1;
								for ($i; $i<=sizeof($SEMESTER); $i++)
									if ((!$SEMESTER[$i]["past"]) && ($SEMESTER[$i]["name"] != $SEM_NAME) && (($SEMESTER[$i]["vorles_ende"] > time())))
										{
										if (($sem_create_data["sem_start_time"] + $sem_create_data["sem_duration_time"]) == $SEMESTER[$i]["beginn"])
											{
											if (!$sem_create_data["sem_duration_time"] == 0)
												echo "<option value=",$SEMESTER[$i]["beginn"], " selected>"._("bis")." ", $SEMESTER[$i]["name"], "</option>";
											else
												echo "<option value=",$SEMESTER[$i]["beginn"], ">"._("bis")." ", $SEMESTER[$i]["name"], "</option>";
											}
										else
											echo "<option value=",$SEMESTER[$i]["beginn"], ">"._("bis")." ", $SEMESTER[$i]["name"], "</option>";
										}
								if ($sem_create_data["sem_duration_time"] == -1)
									echo "<option value=-1 selected>"._("unbegrenzt")."</option>";
								else
									echo "<option value=-1>"._("unbegrenzt")."</option>";
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Falls die Veranstaltung mehrere Semester läuft, können Sie hier das Endsemester wählen. Dauerveranstaltungen können über die entsprechende Einstellung markiert werden."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Turnus:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" nowrap width="30%" colspan=3>
							&nbsp; <select  name="term_art">
							<?
							if ($sem_create_data["term_art"] == 0) 
								echo "<option selected value=\"0\">"._("regelm&auml;&szlig;ig")."</option>";
							else
								echo "<option value=\"0\">"._("regelm&auml;&szlig;ig")."</option>>";
							if ($sem_create_data["modules_list"]["schedule"]) {
								if ($sem_create_data["term_art"] == 1) 
									echo "<option selected value=\"1\">"._("unregelm&auml;&szlig;ig oder Blockveranstaltung")."</option>";
								else
									echo "<option value=\"1\">"._("unregelm&auml;&szlig;ig oder Blockveranstaltung")."</option>";
							}
							if ($sem_create_data["term_art"] == -1) 
								echo "<option selected value=\"-1\">"._("keine Termine eingeben")."</option>";
							else
								echo "<option value=\"-1\">"._("keine Termine eingeben")."</option>";
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte wählen Sie hier aus, ob die Veranstaltung regelmäßig stattfindet, oder ob es nur Sitzungen an bestimmten Terminen gibt (etwa bei einem Blockseminar)"), TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>									
						</td>
					</tr>					
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Heimat-Einrichtung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp;
							<?
							if (!$perm->have_perm("admin"))
								$db->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'dozent' ) ORDER BY is_fak,Name");
							else if (!$perm->have_perm("root"))
								$db->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user_id' AND inst_perms = 'admin') ORDER BY is_fak,Name");
							else
								$db->query("SELECT Name,Institut_id,1 AS is_fak,'admin' AS inst_perms FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
							if ($db->affected_rows())
									{
									echo "<select name=\"sem_inst_id\">";
									while ($db->next_record()) {
										printf ("<option %s style=\"%s\" value=%s>%s</option>", $db->f("Institut_id") == $sem_create_data["sem_inst_id"] ? "selected" : "",
											($db->f("is_fak")) ? "font-weight:bold;" : "",$db->f("Institut_id"), my_substr($db->f("Name"),0,60));
										if ($db->f("is_fak") && $db->f("inst_perms") == "admin"){
											$db2->query("SELECT a.Institut_id, a.Name FROM Institute a 
											 WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND a.Institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name");
											while($db2->next_record()){
												printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $db2->f("Institut_id") == $sem_create_data["sem_inst_id"] ? "selected" : "",
													$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
											}
										}
									}
									echo "</select>";
									}
								else
									sprintf ("error§"._("Ihr Account wurde noch keiner Einrichtung zugeordnet. Bitte wenden Sie sich an den/die zust&auml;ndigeN AdministratorIn der Einrichtung oder einen der %sAdministratoren%s des Systems!")."§", "<a href=\"ansprechpartner.php\">", "</a>");
							?>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Die Heimat-Einrichtung ist die Einrichtung, die offiziell für die Veranstaltung zuständig ist."), TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("beteiligte Einrichtungen:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <select  name="sem_bet_inst[]" MULTIPLE SIZE=7>
							<?
								$db->query("SELECT Institut_id,Name FROM Institute WHERE Institut_id = fakultaets_id ORDER BY Name");
								while ($db->next_record()) {
									$selected="";
									if(is_array($sem_create_data["sem_bet_inst"]) && in_array($db->f("Institut_id"),$sem_create_data["sem_bet_inst"])){
										$selected = "selected";
									}
									printf ("<option %s style=\"font-weight:bold;\" value=\"%s\">%s</option>",$selected,$db->f("Institut_id")
										, htmlReady(my_substr($db->f("Name"),0,60)));
									$db2->query("SELECT Institut_id, Name FROM Institute 
										WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND Institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name" );
									while($db2->next_record()){
										$selected="";
										if(is_array($sem_create_data["sem_bet_inst"]) && in_array($db2->f("Institut_id"),$sem_create_data["sem_bet_inst"])){
										$selected = "selected";
										}
										printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $selected,
											$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
									}
								}
							?>
							</select>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte markieren Sie hier alle Einrichtungen, an denen die Veranstaltung ebenfalls angeboten wird. Bitte beachten Sie: Sie können später nur DozentInnen aus den Einrichtungen auswählen, die entweder als Heimat- oder als beteiligte Einrichtung markiert worden sind. Sie können mehrere Einträge markieren, indem sie die STRG bzw. APPLE Taste gedrückt halten und dann auf die Einträge klicken."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_b">
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
					echo "&nbsp;"._("Veranstaltungs-Assistent - Schritt 2: Personendaten, Typ, Sicherheit und Bereiche")."</b>";
				else
					echo "&nbsp;"._("Veranstaltungs-Assistent - Schritt 2: Personendaten, Typ und Sicherheit")."</b>";
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
					echo "<b>"._("Schritt 2: Personendaten, Studienbereiche und weitere Angaben zur Veranstaltung")."</b><br><br>";
				else
					echo "<b>"._("Schritt 2: Personendaten und weitere Angaben zur Veranstaltung")." </b><br><br>";
				?>
				<font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands02.jpg" border="0">
			</td>
		</tr>
		<tr>
			<td class="blank" colspan=2>
			<form method="POST" action="<? echo $PHP_SELF ?>#anker">
			<input type="HIDDEN" name="form" value=2>
				<table width ="99%" cellspacing=0 cellpadding=2 border=0 align="center">
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_a">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_c">
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
						<?
						if (!$SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"])
							echo _("DozentInnen:");
						else
							echo _("LeiterInnen:");
						?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="40%">
							<?
							if (sizeof($sem_create_data["sem_doz"]) >0) {
								foreach($sem_create_data["sem_doz"] as $key=>$val) {
									printf ("&nbsp; <a href=\"%s?delete_doz=%s\"><img src=\"./pictures/trash.gif\" border=\"0\"></a>&nbsp; <font size=\"-1\"><b>%s (%s)&nbsp; &nbsp; <br />", $PHP_SELF, get_username($key), htmlReady(get_fullname($key,"full_rev")), get_username($key));
								}
							} else {
								if ($SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"])
									printf ("<font size=\"-1\">&nbsp;  "._("Keine LeiterIn gew&auml;hlt.")."</font><br >");
								else
									printf ("<font size=\"-1\">&nbsp;  "._("Keine DozentIn gew&auml;hlt.")."</font><br >");								
							}
							?>
							&nbsp; <img  src="./pictures/info.gif" 
								<? 
								if (!$SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"])
									echo tooltip(_("Die Namen der DozentInnen, die die Veranstaltung leiten. Nutzen Sie die Suchfunktion, um weitere Eintragungen vorzunehmen oder das Mülltonnensymbol, um Einträge zu löschen."), TRUE, TRUE);
								else
									echo tooltip(_("Die Namen der LeiterInnen der Veranstaltung. Nutzen Sie die Suchfunktion, um weitere Eintragungen vorzunehmen oder das Mülltonnensymbol, um Einträge zu löschen."), TRUE, TRUE);
								?>
							>
							<font color="red" size=+2>*</font>
						</td>
						<td <? echo $cssSw->getFullClass() ?> width="50%" colspan="2">
							<?
							if (($search_exp_doz) && ($search_doz_x)) {
								if ($SEM_CLASS[$sem_create_data["sem_class"]]["only_inst_user"]) {
									$clause="AND Institut_id IN ('".$sem_create_data["sem_inst_id"]."'";
									if (is_array($sem_create_data["sem_bet_inst"]))
										foreach($sem_create_data["sem_bet_inst"] as $val)
											$clause.=",'$val'";
									$clause.=")";
									$db->query ("SELECT DISTINCT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms = 'dozent' $clause AND (username LIKE '%$search_exp_doz%' OR Vorname LIKE '%$search_exp_doz%' OR Nachname LIKE '%$search_exp_doz%') ORDER BY Nachname");
								} else
									$db->query ("SELECT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id)  WHERE perms = 'dozent' AND (username LIKE '%$search_exp_doz%' OR Vorname LIKE '%$search_exp_doz%' OR Nachname LIKE '%$search_exp_doz%') ORDER BY Nachname");								
								if ($db->num_rows()) {
									print "<a name=\"anker\"></a>";
									printf ("<font size=-1><b>%s</b> "._("NutzerIn gefunden:")."<br />", $db->num_rows());
									print "<input type=\"IMAGE\" src=\"./pictures/move_left.gif\" ".tooltip(_("Den/die BenutzerIn hinzufügen"))." border=\"0\" name=\"send_doz\" />";
									print "&nbsp; <select name=\"add_doz\">";
									while ($db->next_record()) {
										printf ("<option value=\"%s\">%s </option>", $db->f("username"), htmlReady(my_substr($db->f("fullname")." (".$db->f("username").")", 0, 30)));
									}
									print "</select></font>";
									print "<input type=\"IMAGE\" src=\"./pictures/rewind.gif\" ".tooltip(_("Neue Suche starten"))." border=\"0\" name=\"reset_search\" />";									
								}
							}
							if ((!$search_exp_doz) || (($search_exp_doz) && (!$db->num_rows()))) {
								?>
								<font size=-1>
								<? printf ("%s %s", (($search_exp_doz) && (!$db->num_rows())) ? _("KeineN NutzerIn gefunden.")."<a name=\"anker\"></a>" : "",   (!$search_exp_doz) ? (!$SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"]) ? _("DozentIn hinzuf&uuml;gen") : _("LeiterIn hinzuf&uuml;gen")  : "");?>
								</font><br />
								<input type="TEXT" size="30" maxlength="255" name="search_exp_doz" />&nbsp; 
								<input type="IMAGE" src="./pictures/suchen.gif" <? echo tooltip(_("Suche starten")) ?> border="0" name="search_doz" />
								<?
							}
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
						<?
						if (!$SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"])
							echo "TutorInnen:";
						else
							echo "Mitglieder: <br />";
						?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="40%">
							<?
							if (sizeof($sem_create_data["sem_tut"]) >0) {
								foreach($sem_create_data["sem_tut"] as $key=>$val) {
									printf ("&nbsp; <a href=\"%s?delete_tut=%s\"><img src=\"./pictures/trash.gif\" border=\"0\"></a>&nbsp; <font size=\"-1\"><b>%s (%s)&nbsp; &nbsp; <br />", $PHP_SELF, get_username($key), htmlReady(get_fullname($key,"full_rev")), get_username($key));
								}
							} else {
								if ($SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"])
									printf ("<font size=\"-1\">&nbsp;  "._("Kein Mitglied gew&auml;hlt.")."</font><br >");
								else
									printf ("<font size=\"-1\">&nbsp;  "._("Keine TutorIn gew&auml;hlt.")."</font><br >");								
							}
							?>
							&nbsp; <img  src="./pictures/info.gif" 
								<?
								if (!$SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"])
									echo tooltip(_("Die Namen der TutorInnen, die in der Veranstaltung weitergehende Rechte erhalten (meist studentische Hilfskräfte). Nutzen Sie die Suchfunktion (Lupensymbol), um weitere Eintragungen vorzunehmen, oder das Mülltonnensymbol, um Einträge zu löschen."), TRUE, TRUE);
								else
									echo tooltip(_("Die Namen der Mitglieder der Veranstaltung. Nutzen Sie die Suchfunktion (Lupensymbol), um weitere Eintragungen vorzunehmen oder das Mülltonnensymbol, um Einträge zu löschen."), TRUE, TRUE);
								?>
							>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="50%" colspan="2">
							<?
							if (($search_exp_tut) && ($search_tut_x)) {
								if ($SEM_CLASS[$sem_create_data["sem_class"]]["only_inst_user"]) {
									$clause="AND Institut_id IN ('".$sem_create_data["sem_inst_id"]."'";
									if (is_array($sem_create_data["sem_bet_inst"]))
										foreach($sem_create_data["sem_bet_inst"] as $val)
											$clause.=",'$val'";
									$clause.=")";
									$db->query ("SELECT DISTINCT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING(user_id) WHERE inst_perms IN ('tutor', 'dozent') $clause AND (username LIKE '%$search_exp_tut%' OR Vorname LIKE '%$search_exp_tut%' OR Nachname LIKE '%$search_exp_tut%') ORDER BY Nachname");
								} else
									$db->query ("SELECT username, ". $_fullname_sql['full_rev'] ." AS fullname FROM auth_user_md5 LEFT JOIN user_info USING(user_id) WHERE perms IN ('tutor', 'dozent') AND (username LIKE '%$search_exp_tut%' OR Vorname LIKE '%$search_exp_tut%' OR Nachname LIKE '%$search_exp_tut%') ORDER BY Nachname");								
								if ($db->num_rows()) {
									print "<a name=\"anker\"></a>";
									printf ("<font size=-1><b>%s</b> "._("NutzerIn gefunden:")."<br />", $db->num_rows());
									print "<input type=\"IMAGE\" src=\"./pictures/move_left.gif\" ".tooltip(_("Den/die BenutzerIn hinzufügen"))." border=\"0\" name=\"send_tut\" />";
									print "&nbsp; <select name=\"add_tut\">";
									while ($db->next_record()) {
										printf ("<option value=\"%s\">%s </option>", $db->f("username"), htmlReady(my_substr($db->f("fullname")." (".$db->f("username").")", 0, 30)));
									}
									print "</select></font>";
									print "<input type=\"IMAGE\" src=\"./pictures/rewind.gif\" ".tooltip(_("neue Suche starten"))." border=\"0\" name=\"reset_search\" />";									
								}
							}
							if ((!$search_exp_tut) || (($search_exp_tut) && (!$db->num_rows()))) {
								?>
								<font size=-1>
								<? printf ("%s %s", (($search_exp_tut) && (!$db->num_rows())) ? _("KeineN NutzerIn gefunden.")."<a name=\"anker\"></a>" : "",   (!$search_exp_tut) ? (!$SEM_CLASS[$sem_create_data["sem_class"]]["workgroup_mode"]) ? _("TutorIn hinzuf&uuml;gen") : _("Mitglied hinzuf&uuml;gen")  : "");?>
								</font><br />
								<input type="TEXT" size="30" maxlength="255" name="search_exp_tut" />&nbsp; 
								<input type="IMAGE" src="./pictures/suchen.gif" <? echo tooltip(_("Suche starten")) ?> border="0" name="search_tut" /><br />
								<font size=-1><?=_("Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein."); ?></font>
								<?
							}
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Typ der Veranstaltung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <select name="sem_status">
							<?
								for ($i=1; $i <= sizeof($SEM_TYPE); $i++) {
									if ($SEM_TYPE[$i]["class"] == $sem_create_data["sem_class"])
										printf ("<option %s value=%s>%s</option>", $sem_create_data["sem_status"] == $i ? "selected" : "", $i, $SEM_TYPE[$i]["name"]);
								}
							?>
							</select> <br />
							&nbsp; <font size="-1"> <?=_("in der Kategorie"); ?> <b><? echo $SEM_CLASS[$sem_create_data["sem_class"]]["name"] ?></b></font>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Über den Typ der Veranstaltung werden die Veranstaltungen innerhalb von Listen gruppiert."), TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font>
						</td>
					</tr>	
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Art der Veranstaltung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <input type="text" name="sem_art" size=30 maxlength=254 value="<? echo htmlReady(stripslashes($sem_create_data["sem_art"])) ?>">
							<font size=-1><?=_("(eigene Beschreibung)"); ?></font>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Hier können Sie eine frei wählbare Bezeichnung für die Art der Veranstaltung wählen."), TRUE, TRUE) ?>
							>								
						</td>
					</tr>
									
					<?
					if ($SEM_CLASS[$sem_create_data["sem_class"]]["bereiche"])
					{
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Studienbereiche:"); ?><br>
							<font color="red" size=-1>
								<?=_("(&Uuml;berschriften k&ouml;nnen nicht ausgew&auml;hlt werden!)"); ?>
							</font>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<?
							echo "\n<div align=\"left\" >&nbsp;";
							echo $st_search->getSearchField(array('size' => 30 ,'style' => 'vertical-align:middle;'));
							echo "&nbsp;";
							echo $st_search->getSearchButton(array('style' => 'vertical-align:middle;'));
							echo "<br>&nbsp;&nbsp;<span style=\"font-size:10pt;\">" . _("Geben Sie zur Suche den Namen des Studienbereiches ein.");
							if ($st_search->num_search_result !== false){
								echo "<br><a name=\"anker\">&nbsp;&nbsp;</a><b>" . sprintf(_("Ihre Suche ergab %s Treffer."),$st_search->num_search_result) . (($st_search->num_search_result) ? _(" (Suchergebnisse werden blau angezeigt)") : "") . "</b>";
							}
							echo "</span><br>&nbsp;";
							echo $st_search->getChooserField(array('style' => 'width:70%','size' => 10),70);
							?>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Hier können Sie die Studienbereiche, in denen die Veranstaltung angeboten wird, markieren. Sie können mehrere Einträge markieren, indem sie die STRG bzw. APPLE Taste gedrückt halten und dann auf die Einträge klicken."), TRUE, TRUE) ?>
							>
							<font color="red" size=+2>*</font></div>
						</td>
					</tr>
					<?
					}
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Lesezugriff:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<?					
							if (!$sem_create_data["sem_admission"]) {
								if (!isset($sem_create_data["sem_sec_lese"]) || $sem_create_data["sem_sec_lese"]==3)
									$sem_create_data["sem_sec_lese"] = "1";	//Vorgabe: nur angemeldet oder es war Teilnahmebegrenzung gesetzt
							?>
								<input type="radio" name="sem_sec_lese" value="0" <?php print $sem_create_data["sem_sec_lese"] == 0 ? "checked" : ""?>> <?=_("freier Zugriff"); ?> &nbsp;
								<input type="radio" name="sem_sec_lese" value="1" <?php print $sem_create_data["sem_sec_lese"] == 1 ? "checked" : ""?>> <?=_("in Stud.IP angemeldet"); ?> &nbsp;
								<input type="radio" name="sem_sec_lese" value="2" <?php print $sem_create_data["sem_sec_lese"] == 2 ? "checked" : ""?>> <?=_("nur mit Passwort"); ?> &nbsp;
								<img  src="./pictures/info.gif" 
									<? echo tooltip(_("Hier geben Sie an, ob der Lesezugriff auf die Veranstaltung frei (jeder), normal beschränkt (nur registrierte Stud.IP-User) oder nur mit einem speziellen Passwort möglich ist."), TRUE, TRUE) ?>
								>								
							<?
							} else
								print "&nbsp; <font size=-1>"._("Leseberechtigung nach erfolgreichem Anmeldeprozess")."</font>"
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Schreibzugriff:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<?
							if (!$sem_create_data["sem_admission"]) {
								if (!isset($sem_create_data["sem_sec_schreib"]) || $sem_create_data["sem_sec_schreib"]==3)
									$sem_create_data["sem_sec_schreib"] = "1";	//Vorgabe: nur angemeldet
								if ($SEM_CLASS[$sem_create_data["sem_class"]]["write_access_nobody"]) {
									?>
								<input type="radio" name="sem_sec_schreib" value="0" <?php print $sem_create_data["sem_sec_schreib"] == 0 ? "checked" : ""?>> <?=_("freier Zugriff"); ?> &nbsp;
									<?
									}
								else {
									?>
								<font color=#BBBBBB>&nbsp; &nbsp; &nbsp;  <?=_("freier Zugriff")?> &nbsp;</font>
									<?
									}
							?>
								<input type="radio" name="sem_sec_schreib" value="1" <?php print $sem_create_data["sem_sec_schreib"] == 1 ? "checked" : ""?>> <?=_("in Stud.IP angemeldet"); ?> &nbsp;
								<input type="radio" name="sem_sec_schreib" value="2" <?php print $sem_create_data["sem_sec_schreib"] == 2 ? "checked" : ""?>> <?=_("nur mit Passwort"); ?> &nbsp;
								<img  src="./pictures/info.gif" 
									<? echo tooltip(_("Hier geben Sie an, ob der Schreibzugriff auf die Veranstaltung frei (jeder), normal beschränkt (nur registrierte Stud.IP-User) oder nur mit einem speziellen Passwort möglich ist."), TRUE, TRUE) ?>
							>
							<?
							} else
								print "&nbsp; <font size=-1>"._("Schreibberechtigung nach erfolgreichem Anmeldeprozess")."</font>"
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							&nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_a">&nbsp;<input type="IMAGE"  <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_c">
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
if ($level==3) {
	if ($RESOURCES_ENABLE)
		$resList = new ResourcesUserRoomsList($user_id);
	?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
		<tr>
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent - Schritt 3: Termine"); ?></b>
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
				<b><?=_("Schritt 3: Termindaten"); ?></b><br><br>
				<? if ($sem_create_data["term_art"] ==0) 
					print _("Bitte geben Sie hier ein, an welchen Tagen die Veranstaltung stattfindet. Wenn Sie nur einen Wochentag wissen, brauchen Sie nur diesen angeben.<br>Sie haben sp&auml;ter noch die M&ouml;glichkeit, weitere Einzelheiten zu diesen Terminen anzugeben.")."<br><br>";
				else
					print _("Bitte geben Sie hier die einzelnen Termine an, an denen die Veranstaltung stattfindet.<br> Sie haben sp&auml;ter noch die M&ouml;glichkeit, weitere Einzelheiten zu diesen Terminen anzugeben.")."<br><br>";
				?>
				<font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands03.jpg" border="0">
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
							&nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_b">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_d">
						</td>
					</tr>
					<?
						if ($sem_create_data["term_art"] ==0)
							{
							?>
							<tr <? $cssSw->switchClass() ?>>
								<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
									&nbsp; <?=_("Daten &uuml;ber die Termine:"); ?>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
									&nbsp; <b><font size=-1><?=_("Regelm&auml;&szlig;ige Veranstaltung"); ?></font></b><br><br>
									&nbsp;  <font size=-1><?=_("Wenn Sie den Typ der Veranstaltung &auml;ndern m&ouml;chten, gehen Sie bitte auf die erste Seite zur&uuml;ck."); ?></font><br><br>
									&nbsp;  <font size=-1><?=_("Turnus:"); ?> </font>&nbsp; 
									<select name="term_turnus">
									<?
									if ($sem_create_data["term_turnus"]==0)
										echo "<option selected value=0>"._("w&ouml;chentlich")."</option>";
									else
										echo "<option value=0>"._("w&ouml;chentlich")."</option>";
									if ($sem_create_data["term_turnus"]==1)
										echo "<option selected value=1>"._("zweiw&ouml;chentlich")."</option>";
									else
										echo "<option value=1>"._("zweiw&ouml;chentlich")."</option>";
									?>
									</select>&nbsp;  <font size=-1><?=_("erster Termin in der"); ?></font> 
									<select name="term_start_woche">
									<?
									if ($sem_create_data["term_start_woche"]==0)
										echo "<option selected value=0>"._("1. Semesterwoche")."</option>";
									else
										echo "<option value=0>"._("1. Semesterwoche")."</option>";
									if ($sem_create_data["term_start_woche"]==1)
										echo "<option selected value=1>"._("2. Semesterwoche")."</option>";
									else
										echo "<option value=1>"._("2. Semesterwoche")."</option>";
									if ($sem_create_data["term_start_woche"]==-1)
										echo "<option selected value=-1>"._("anderer Startzeitpunkt")."</option>";
									else
										echo "<option value=-1>"._("anderer Startzeitpunkt")."</option>";
										
									?>
									</select>
									<br><br>&nbsp; <font size=-1><?=_("Die Veranstaltung findet immer zu diesen Zeiten statt:"); ?></font><br><br>
									<?
									if (empty($sem_create_data["turnus_count"])) 
										$sem_create_data["turnus_count"]=1;
									for ($i=0; $i<$sem_create_data["turnus_count"]; $i++) {
										if ($i>0) echo "<br>";
										?>&nbsp; <font size=-1><select name="term_turnus_date[<?echo $i?>]">
										<?
										if (($sem_create_data["term_turnus_date"][$i]==1) || (empty($sem_create_data["term_turnus_date"][$i])))
											echo "<option selected value=1>"._("Montag")."</option>";
										else
											echo "<option value=1>"._("Montag")."</option>";
										if ($sem_create_data["term_turnus_date"][$i]==2)
											echo "<option selected value=2>"._("Dienstag")."</option>";
										else
											echo "<option value=2>"._("Dienstag")."</option>";
										if ($sem_create_data["term_turnus_date"][$i]==3)
											echo "<option selected value=3>"._("Mittwoch")."</option>";
										else
											echo "<option value=3>"._("Mittwoch")."</option>";
										if ($sem_create_data["term_turnus_date"][$i]==4)
											echo "<option selected value=4>"._("Donnerstag")."</option>";
										else
											echo "<option value=4>"._("Donnerstag")."</option>";
										if ($sem_create_data["term_turnus_date"][$i]==5)
											echo "<option selected value=5>"._("Freitag")."</option>";
										else
											echo "<option value=5>"._("Freitag")."</option>";
										if ($sem_create_data["term_turnus_date"][$i]==6)
											echo "<option selected value=6>"._("Samstag")."</option>";
										else
											echo "<option value=6>"._("Samstag")."</option>";
										if ($sem_create_data["term_turnus_date"][$i]==7)
											echo "<option selected value=7>"._("Sonntag")."</option>";
										else
											echo "<option value=7>"._("Sonntag")."</option>";
										echo "</select>\n";
										?>
										&nbsp; <input type="text" name="term_turnus_start_stunde[]" size=2 maxlength=2 value="<? if ($sem_create_data["term_turnus_start_stunde"][$i]) echo $sem_create_data["term_turnus_start_stunde"][$i] ?>"> :
										<input type="text" name="term_turnus_start_minute[]" size=2 maxlength=2 value="<? if (($sem_create_data["term_turnus_start_minute"][$i]) && ($sem_create_data["term_turnus_start_minute"][$i] >0)) { if ($sem_create_data["term_turnus_start_minute"][$i] < 10) echo "0", $sem_create_data["term_turnus_start_minute"][$i]; else echo $sem_create_data["term_turnus_start_minute"][$i];  } elseif ($sem_create_data["term_turnus_start_stunde"][$i]) echo "00"; ?>">&nbsp;<?=_("Uhr bis");?>
										&nbsp; <input type="text" name="term_turnus_end_stunde[]" size=2 maxlength=2 value="<? if ($sem_create_data["term_turnus_end_stunde"][$i]) echo $sem_create_data["term_turnus_end_stunde"][$i] ?>"> :
										<input type="text" name="term_turnus_end_minute[]" size=2 maxlength=2 value="<? if (($sem_create_data["term_turnus_end_minute"][$i]) && ($sem_create_data["term_turnus_end_minute"][$i] >0)) { if ($sem_create_data["term_turnus_end_minute"][$i] < 10) echo "0", $sem_create_data["term_turnus_end_minute"][$i]; else echo $sem_create_data["term_turnus_end_minute"][$i];  } elseif ($sem_create_data["term_turnus_end_stunde"][$i]) echo "00"; ?>">&nbsp;<?=_("Uhr");?>
										<?
										if ($sem_create_data["turnus_count"]>1) {
											?>
											&nbsp; <a href="<? echo $PHP_SELF?>?delete_turnus_field=<?echo $i+1?>"><img border=0 src="./pictures/trash.gif" <? echo tooltip(_("Dieses Feld aus der Auswahl löschen"), TRUE) ?> ></a>
											<?
										}
										print "<br />&nbsp; "._("Raum:")."";
										if ($RESOURCES_ENABLE) {
											$resList->reset();
											if ($resList->numberOfEvents()) {
												print " &nbsp;<select name=\"term_turnus_resource_id[]\">";
												printf ("<option %s value=\"FALSE\">["._("wie Eingabe")." -->]</option>", (!$sem_create_data["term_turnus_resource_id"][$i]) ? "selected" : "");												
												while ($resObject = $resList->nextEvent()) {
													printf ("<option %s value=\"%s\">%s</option>", ($sem_create_data["term_turnus_resource_id"][$i]) == $resObject->getId() ? "selected" :"", $resObject->getId(), htmlReady($resObject->getName()));
												}
												print "</select>";
											}
										}
										?>
										&nbsp; <input type="text" name="term_turnus_room[]" size="15" maxlength="255" value="<?= htmlReady($sem_create_data["term_turnus_room"][$i]) ?>"/></font>&nbsp; 
										<?
											print "<br />";
									}
										?>
										<br />&nbsp; <input type="IMAGE" name="add_turnus_field" <?=makeButton("feldhinzufuegen", "src"); ?> border=0 value="Feld hinzuf&uuml;gen">&nbsp; 
										<img  src="./pictures/info.gif" 
											<? echo tooltip(_("Wenn es sich um eine regelmäßige Veranstaltung handelt, so können Sie hier genau angeben, an welchen Tagen, zu welchen Zeiten und in welchem Raum die Veranstaltung stattfindet. Wenn Sie noch keine Zeiten wissen, dann klicken Sie auf »keine Zeiten speichern«."), TRUE, TRUE) ?>
										>
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
									&nbsp; <?=_("Daten &uuml;ber die Termine:"); ?>
								</td>
								<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
									&nbsp; <b><font size=-1><?=_("Veranstaltung an unregelm&auml;&szlig;igen Terminen"); ?></font></b><br><br>
									&nbsp;  <font size=-1><?=_("Wenn Sie den Typ der Veranstaltung &auml;ndern m&ouml;chten, gehen Sie bitte auf die erste Seite zur&uuml;ck."); ?></font><br><br>
									&nbsp; <font size=-1><?=_("Die Veranstaltung findet an diesen Terminen statt:"); ?></font><br><br>
									<?
									if (empty($sem_create_data["term_count"])) 
										$sem_create_data["term_count"]=1;
									for ($i=0; $i<$sem_create_data["term_count"]; $i++)
										{
										if ($i>0) echo "<br>";
										?>
										<font size=-1>&nbsp; <?=_("Datum:");?> <input type="text" name="term_tag[]" size=2 maxlength=2 value="<? if ($sem_create_data["term_tag"][$i]) echo $sem_create_data["term_tag"][$i] ?>">.
										<input type="text" name="term_monat[]" size=2 maxlength=2 value="<? if ($sem_create_data["term_monat"][$i]) echo $sem_create_data["term_monat"][$i] ?>">.
										<input type="text" name="term_jahr[]" size=4 maxlength=4 value="<? if ($sem_create_data["term_jahr"][$i]) echo $sem_create_data["term_jahr"][$i] ?>">
										&nbsp;<?=_("um")?> <input type="text" name="term_start_stunde[]" size=2 maxlength=2 value="<? if ($sem_create_data["term_start_stunde"][$i]) echo $sem_create_data["term_start_stunde"][$i] ?>"> :
										<input type="text" name="term_start_minute[]" size=2 maxlength=2 value="<? if (($sem_create_data["term_start_minute"][$i]) && ($sem_create_data["term_start_minute"][$i] >0)) { if ($sem_create_data["term_start_minute"][$i] < 10) echo "0", $sem_create_data["term_start_minute"][$i]; else echo $sem_create_data["term_start_minute"][$i];  } elseif ($sem_create_data["term_start_stunde"][$i]) echo "00"; ?>">&nbsp;<?=_("Uhr bis");?>
										<input type="text" name="term_end_stunde[]" size=2 maxlength=2 value="<? if ($sem_create_data["term_end_stunde"][$i]) echo $sem_create_data["term_end_stunde"][$i] ?>"> :
										<input type="text" name="term_end_minute[]" size=2 maxlength=2 value="<? if (($sem_create_data["term_end_minute"][$i]) && ($sem_create_data["term_end_minute"][$i] >0)) { if ($sem_create_data["term_end_minute"][$i] < 10) echo "0", $sem_create_data["term_end_minute"][$i]; else echo $sem_create_data["term_end_minute"][$i];  } elseif ($sem_create_data["term_end_stunde"][$i]) echo "00"; ?>">&nbsp;<?=_("Uhr");?></font>
										<?
										if ($sem_create_data["term_count"]>1) 
											{
											?>
											&nbsp; <a href="<? echo $PHP_SELF?>?delete_term_field=<?echo $i+1?>"><img border=0 src="./pictures/trash.gif" <? echo tooltip(_("Dieses Feld aus der Auswahl löschen"), TRUE) ?> ></a>
											<?
											}
										print "<br /><font size=-1>&nbsp; "._("Raum:")." ";
										if ($RESOURCES_ENABLE) {
											$resList->reset();
											if ($resList->numberOfEvents()) {
												print "&nbsp;<select name=\"term_resource_id[]\">";
												printf ("<option %s value=\"FALSE\">["._("wie Eingabe")." -->]</option>", (!$sem_create_data["term_resource_id"][$i]) ? "selected" : "");
												while ($resObject = $resList->nextEvent()) {
													printf ("<option %s value=\"%s\">%s</option>", ($sem_create_data["term_resource_id"][$i]) == $resObject->getId() ? "selected" :"", $resObject->getId(), htmlReady($resObject->getName()));
												}
												print "</select></font>";
											}
										}
										?>
										&nbsp; <font size=-1><input type="text" name="term_room[]" size="15" maxlength="255" value="<?= htmlReady($sem_create_data["term_room"][$i]) ?>"/></font>&nbsp; 
										<?
										print "<br />";
										}
										?>
										&nbsp; <input type="IMAGE" name="add_term_field" <?=makeButton("feldhinzufuegen", "src"); ?> border=0 value="Feld hinzuf&uuml;gen">&nbsp; 
										<img  src="./pictures/info.gif" 
											<? echo tooltip(_("In diesem Feldern können Sie alle Veranstaltungstermine eingeben. Wenn die Termine noch nicht feststehen, lassen Sie die Felder einfach frei."), TRUE, TRUE) ?>
										>
										<br>
								</td>
							</tr>
						<?
						}
					if ($sem_create_data["modules_list"]["schedule"]) {
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Vorbesprechung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							<font size=-1>&nbsp; <font size=-1><?=_("Wenn es eine Vorbesprechung gibt, tragen Sie diese bitte hier ein:"); ?></font><br><br>&nbsp; <?=_("Datum:"); ?></font>
							<font size=-1><input type="text" name="vor_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("d",$sem_create_data["sem_vor_termin"]); ?>">.
							<input type="text" name="vor_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("m",$sem_create_data["sem_vor_termin"]); ?>">.
							<input type="text" name="vor_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("Y",$sem_create_data["sem_vor_termin"]); ?>">&nbsp;
							<?=_("um")?> <input type="text" name="vor_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("H",$sem_create_data["sem_vor_termin"]); ?>"> :
							<input type="text" name="vor_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_termin"]<>-1) echo date("i",$sem_create_data["sem_vor_termin"]); ?>">&nbsp;<?=_("Uhr bis");?>
							<input type="text" name="vor_end_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_end_termin"]<>-1) echo date("H",$sem_create_data["sem_vor_end_termin"]); ?>"> :
							<input type="text" name="vor_end_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_vor_end_termin"]<>-1) echo date("i",$sem_create_data["sem_vor_end_termin"]); ?>">&nbsp;<?=_("Uhr")?><br />
							<?
							print "<font size=-1>&nbsp; "._("Raum:")." ";
							if ($RESOURCES_ENABLE) {
								$resList->reset();
								if ($resList->numberOfEvents()) {
									print "&nbsp;<select name=\"vor_resource_id\">";
									printf ("<option %s value=\"FALSE\">["._("wie Eingabe")." -->]</option>", (!$sem_create_data["sem_vor_resource_id"]) ? "selected" : "");
									while ($resObject = $resList->nextEvent()) {
										printf ("<option %s value=\"%s\">%s</option>", ($sem_create_data["sem_vor_resource_id"]) == $resObject->getId() ? "selected" :"", $resObject->getId(), htmlReady($resObject->getName()));
									}
									print "</select></font>";
								}
							}
							?>
							&nbsp; <input type="text" name="vor_raum" size=15 maxlength=255 value="<? if ($sem_create_data["sem_vor_raum"]) echo  htmlReady(stripslashes($sem_create_data["sem_vor_raum"])); ?>"></font>&nbsp; 
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Dieses Feld müssen Sie nur ausfüllen, wenn es eine verbindliche Vorbesprechung zu der Veranstaltung gibt."), TRUE, TRUE) ?>
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
							&nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_b">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_d">
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
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent - Schritt 4: Sonstige Daten"); ?></b>
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
				<b><?=_("Schritt 4: Sonstige Daten zu der Veranstaltung"); ?></b><br><br>
				<font size=-1><? printf (_("Alle mit einem Sternchen%smarkierten Felder <b>m&uuml;ssen</b> ausgef&uuml;llt werden, um eine Veranstaltung anlegen zu k&ouml;nnen.")."</font><br><br>", "&nbsp;</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>&nbsp;");?>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands04.jpg" border="0">
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
							&nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_<? if ($sem_create_data["term_art"]== -1) echo "b"; else echo "c" ?>">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_e">
						</td>
					</tr>
					<?
					if (($sem_create_data["sem_sec_lese"] ==2) || ($sem_create_data["sem_sec_schreib"] ==2)) {
						?>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
								<?=_("Passwort f&uuml;r Freischaltung:"); ?>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>&nbsp;
								<?
									if (($sem_create_data["sem_pw"]=="") || ($sem_create_data["sem_pw"] == md5("")))
										echo "<input type=\"password\" name=\"password\" size=12 maxlength=31> &nbsp; "._("Passwort-Wiederholung:")."&nbsp; <input type=\"password\" name=\"password2\" size=12 maxlength=31>";
									else	
										echo "<input type=\"password\" name=\"password\" size=12 maxlength=31 value=\"*******\">&nbsp; "._("Passwort-Wiederholung:")."&nbsp; <input type=\"password\" name=\"password2\" size=12 maxlength=31 value=\"*******\">";
								?>
								<img  src="./pictures/info.gif" 
									<? echo tooltip(_("Bitte geben Sie hier ein Passwort für die Veranstaltung sowie dasselbe Passwort nochmal zur Bestätigung ein. Dieses wird später von den Teilnehmenden benötigt, um die Veranstaltung abonnieren zu können."), TRUE, TRUE) ?>
								>
							</td>
						</tr>
						<?
					}
					if ($sem_create_data["sem_admission"]) {
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Anmeldeverfahren:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							<font size=-1>&nbsp;<?
							if ($sem_create_data["sem_admission"] == 1)
								print _("Sie haben vorher das Stud.IP Anmeldeverfahren nach dem Losverfahren aktiviert.");
							else
								print _("Sie haben vorher das Stud.IP Anmeldeverfahren nach der Reihenfolge der Anmeldungen aktiviert.");
							?><br />
							&nbsp;<?
							if ($sem_create_data["sem_admission"] == 1)
								print _("Bitte geben Sie hier an, welche Studieng&auml;nge mit welchen Kontingenten zugelassen sind und wann das Losdatum ist:");
							else
								print _("Bitte geben Sie hier an, welche Studieng&auml;nge mit welchen Kontingenten zugelassen sind und wann das Enddatum der Kontingentierung ist:");
							?><br /><br />
								<table border=0 cellpadding=2 cellspacing=0>
									<tr>
										<td class="<? echo $cssSw->getClass() ?>" valign="bottom" width="25%">
										<font size=-1>&nbsp;
										<?
										printf ("%s", ($sem_create_data["sem_studg"]) ? _("Alle anderen Studieng&auml;nge") : _("Alle Studieng&auml;nge"));
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
											<font size=-1><? if ($sem_create_data["sem_admission"] == 1) echo _("Losdatum"); else echo _("Enddatum der Kontingentierung");?>:</font>
										</td>
										<td class="<? echo $cssSw->getClass() ?>" valign="top" width="45%">
											<font size=-1>&nbsp; <input type="text" name="adm_tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("d",$sem_create_data["sem_admission_date"]); else echo _("tt") ?>">.
											<input type="text" name="adm_monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("m",$sem_create_data["sem_admission_date"]); else echo _("mm") ?>">.
											<input type="text" name="adm_jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("Y",$sem_create_data["sem_admission_date"]); else echo _("jjjj") ?>"><?=_("um");?>&nbsp;</font><br />
											<font size=-1>&nbsp; <input type="text" name="adm_stunde" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("H",$sem_create_data["sem_admission_date"]); else echo"23" ?>">:
											<input type="text" name="adm_minute" size=2 maxlength=2 value="<? if ($sem_create_data["sem_admission_date"]<>-1) echo date("i",$sem_create_data["sem_admission_date"]); else echo"59" ?>">&nbsp;<?=_("Uhr");?></font>&nbsp; 
											<? 
											if ($sem_create_data["sem_admission"] == 1) {
											?>
											<img  src="./pictures/info.gif" 
												<? echo tooltip(_("Bitte geben Sie hier ein, wann die Anwärter auf der Anmeldeliste in die Veranstaltung gelost werden. Freigebliebene Plätze werden nach diesem Termin per Warteliste an andere interessierte Personen vergeben."), TRUE, TRUE) ?>
											>
											<? 
											} else {
											?>
											<img  src="./pictures/info.gif" 
												<? echo tooltip(_("Bitte geben Sie hier ein, wann das Anmeldeverfahren die Kontingentierung aufheben soll. Ab diesem Zeitpunkt werden freie Plätze an interessierten Personen aus der Warteliste vergeben."), TRUE, TRUE) ?>
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
										printf ("&nbsp; <a href=\"%s?sem_delete_studg=%s\"><img border=0 src=\"./pictures/trash.gif\" ".tooltip(_("Den Studiengang aus der Liste löschen"), TRUE)." />", $PHP_SELF, $key);
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
										<option value="">-- <?=_("bitte ausw&auml;hlen"); ?> --</option>
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
											<input type="IMAGE" <?=makeButton("hinzufuegen", "src"); ?> name="add_studg" border=0 />&nbsp;
											<img  src="./pictures/info.gif" 
												<? echo tooltip(_("Bitte geben Sie hier ein, für welche Studiengänge die Veranstaltung mit welchen Kontingenten beschränkt sein soll und bis wann eine Anmeldung über das Stud.IP Anmeldeverfahren möglich ist."), TRUE, TRUE) ?>
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
							<?
							printf (_("%sAchtung: %sWenn Sie ein Anmeldeverfahren starten, so kann dieser Schritt sp&auml;ter nicht r&uuml;ckg&auml;ngig gemacht werden.")." <br />&nbsp; "._("Sie k&ouml;nnen jedoch die Anzahl der Teilnehmer jederzeit unter <i>Grunddaten</i> anpassen.")."</font> ", "<font size=-1 color=\"red\">&nbsp; ", "</font><font size=-1>");
							?>
						</td>
					</tr>
					<?
					}
					if (!$SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) {
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Teilnehmer- beschreibung:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_teiln" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_teiln"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte geben Sie hier ein, für welchen Teilnehmerkreis die Veranstaltung geeignet ist."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Voraussetzungen:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_voraus" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_voraus"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte geben Sie hier ein, welche Voraussetzungen für die Veranstaltung nötig sind."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Lernorganisation:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_orga" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_orga"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte geben Sie hier ein, mit welcher Lernorganisation die Veranstaltung durchgeführt wird."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Leistungsnachweis:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <textarea name="sem_leistnw" cols=58 rows=4><? echo  htmlReady(stripslashes($sem_create_data["sem_leistnw"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Bitte geben Sie hier ein, welche Leistungsnachweise erbracht werden müssen."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<?
					}
					?>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Sonstiges:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
							&nbsp; <textarea name="sem_sonst" cols=58 rows=<? 	if ($SEM_CLASS[$sem_create_data["sem_class"]]["compact_mode"]) echo "10"; else echo "4" ?>><? echo  htmlReady(stripslashes($sem_create_data["sem_sonst"])) ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("Hier ist Platz für alle sonstigen Informationen zur Veranstaltung."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<?
					if (($sem_create_data["term_start_woche"]==-1) && ($sem_create_data["term_art"] == 0))
						{
						?>
						<tr <? $cssSw->switchClass() ?>>
							<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
								<?=_("erster Termin:"); ?>
							</td>
							<td class="<? echo $cssSw->getClass() ?>" width="90%" colspan=3>
								<font size=-1>&nbsp; <font size=-1><?=_("Sie haben angegeben, an einem anderen Zeitpunkt mit der Veranstaltung zu beginnen. Bitte geben Sie hier den ersten Termin ein."); ?></font><br><br>&nbsp; <?=_("Datum:"); ?> </font>
								<font size=-1><input type="text" name="tag" size=2 maxlength=2 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("d",$sem_create_data["sem_start_termin"]); else echo _("tt") ?>">.
								<input type="text" name="monat" size=2 maxlength=2 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("m",$sem_create_data["sem_start_termin"]); else echo _("mm") ?>">.
								<input type="text" name="jahr" size=4 maxlength=4 value="<? if ($sem_create_data["sem_start_termin"]<>-1) echo date("Y",$sem_create_data["sem_start_termin"]); else echo _("jjjj") ?>">&nbsp; </font>
								<img  src="./pictures/info.gif" 
									<? echo tooltip(_("Bitte geben Sie hier ein, wann der erste Termin der Veranstaltung stattfindet."), TRUE, TRUE) ?>
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
							&nbsp; <input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_<? if ($sem_create_data["term_art"]== -1) echo "b"; else echo "c" ?>">&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_e">
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
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent - Schritt 5: Anlegen der Veranstaltung"); ?></b>
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
				<b><?=_("Schritt 5: Bereit zum Anlegen der Veranstaltung"); ?></b><br><br>
				<?=_("Sie haben nun alle n&ouml;tigen Daten zum Anlegen der Veranstaltung eingegeben. Wenn Sie auf &raquo;Fertig stellen&laquo; klicken, wird die Veranstaltung in Stud.IP &uuml;bernommen. Wenn Sie sich nicht sicher sind, ob alle Daten korrekt sind, &uuml;berpr&uuml;fen Sie noch einmal Ihre Eingaben auf den vorhergehenden Seiten."); ?><br><br>
				<form method="POST" action="<? echo $PHP_SELF ?>">
					<input type="HIDDEN" name="form" value=5>
					<input type="IMAGE" <?=makeButton("zurueck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?> >>" name="cmd_d">&nbsp;<input type="IMAGE" <?=makeButton("anlegen", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_f">
				</form>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands05.jpg" border="0">
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
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent");?></b>
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
					<b><?=_("Die Veranstaltung konnte nicht angelegt werden."); ?></b><br><br>
					<?=_("Bitte korrigieren Sie die Daten."); ?>
					<form method="POST" action="<? echo $PHP_SELF ?>">
						<input type="HIDDEN" name="form" value=6>
						<input type="IMAGE" <?=makeButton("zur&uuml;ck", "src"); ?> border=0 value="<?=_("<< zur&uuml;ck");?>" name="cmd_a">
					</form>
					</blockqoute>
				</td>
				<td class="blank" align="right">
					<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands05.jpg" border="0">
				</td>
			</tr> <?
			}
		elseif ($successful_entry==2)
			{ ?>
			<tr>
				<td class="blank" valign="top">
					<blockquote>
					<?
					print _("Sie haben die Veranstaltung bereits angelegt.");
					if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["literature"])) {
						if (($sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["literature"]))
							print " "._("Sie k&ouml;nnen nun mit der Literatur- und Linkverwaltung und dem Termin-Assistenten fortfahren oder an diesem Punkt abbrechen."); 
						if (($sem_create_data["modules_list"]["schedule"]) && (!$sem_create_data["modules_list"]["literature"]))	
							print " "._("Sie k&ouml;nnen nun mit dem Termin-Assistenten fortfahren oder an diesem Punkt abbrechen.");
						if ((!$sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["literature"]))	
							print " "._("Sie k&ouml;nnen nun mit der Literatur- und Linkverwaltung fortfahren oder an diesem Punkt abbrechen."); 						
						print "<br><br><font size=-1>"._("Sie haben jederzeit die M&ouml;glichkeit, die bereits erfassten Daten zu &auml;ndern und diese Schritte sp&auml;ter nachzuholen.")."</font>";
					}
					?>
					<br /><br />
					<form method="POST" action="<? echo $PHP_SELF ?>">
						<input type="HIDDEN" name="form" value=6>
						<input type="IMAGE" <?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("abbrechen");?>" name="cancel_finished">
						<?
						if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["literature"])) {
							?>
							&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_g">
							<?
						}
						?>
					</form>
					</blockqoute>
				</td>
				<td class="blank" align="right">
					<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands05.jpg" border="0">
				</td>
			</tr> <?
			}
			
		else
			{ ?>
			<tr>
				<td class="blank" valign="top">
					<blockquote>
					<b><?=_("Die Daten der Veranstaltung wurden in das System &uuml;bernommen"); ?></b><br><br>
					<?
					print _("Die Veranstaltung ist jetzt eingerichtet.");
					if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["literature"])) {
						print " "._("Wenn Sie nun auf &raquo;weiter >>&laquo; klicken, k&ouml;nnen Sie weitere -optionale- Daten f&uuml;r die Veranstaltung eintragen.");
						if (($sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["literature"]))
							print " "._("Sie haben die M&ouml;glichkeit, Literatur- und Linklisten einzugeben und k&ouml;nnen mit Hilfe des Termin-Assisten einen Ablaufplan erstellen."); 
						if (($sem_create_data["modules_list"]["schedule"]) && (!$sem_create_data["modules_list"]["literature"]))	
							print " "._("Sie haben die M&ouml;glichkeit, mit Hilfe des Termin-Assisten einen Ablaufplan zu erstellen.");
						if ((!$sem_create_data["modules_list"]["schedule"]) && ($sem_create_data["modules_list"]["literature"]))	
							print " "._("Sie haben die M&ouml;glichkeit, Literatur- und Linklisten einzugeben."); 						
						print "<br><br><font size=-1>"._("Sie haben jederzeit die M&ouml;glichkeit, die bereits erfassten Daten zu &auml;ndern und die n&auml;chsten Schritte sp&auml;ter nachzuholen.")."</font>";
					}
					?><br><br>
					<form method="POST" action="<? echo $PHP_SELF ?>">
						<input type="HIDDEN" name="form" value=6>
						<input type="IMAGE" <?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("fertigstellen");?>" name="cancel_finished">						
						<?
						if (($sem_create_data["modules_list"]["schedule"]) || ($sem_create_data["modules_list"]["literature"])) {
							?>
							&nbsp;<input type="IMAGE" <?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_g">
							<?
						}
						?>
					</form>
					</blockqoute>
				</td>
				<td class="blank" align="right" valign="top">
					<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands05.jpg" border="0">
				</td>
			</tr>
			<tr>
				<td class="blank" colspan=2>
				<br>
				<form method="POST" action="<? echo $PHP_SELF ?>">
					<table width ="60%" cellspacing=1 cellpadding=1>
						<tr>
							<td width="10%" class="blank">&nbsp; </td>
							<td width="90%" class="rahmen_steel">
							<?
							printf ("<br><br><ul><li>"._("Veranstaltung <b>%s</b> erfolgreich angelegt.")."<br><br>", htmlReady(stripslashes($sem_create_data["sem_name"])));
							if ($count_bet_inst==1)
								print "<li>"._("Veranstaltung f&uuml;r <b>1</b> beteiligte Einrichtung angelegt.")."<br><br>";
							elseif ($count_bet_inst>1)
								printf ("<li>"._("Veranstaltung f&uuml;r <b>%s</b> beteiligte Einrichtungen angelegt.")."<br><br>", $count_bet_inst);
							if ($count_doz==1)
								print "<li>"._("<b>1</b> DozentIn f&uuml;r die Veranstaltung eingetragen.")."<br><br>";
							else
								printf ("<li>"._("<b>%s</b> DozentInnen f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_doz);
							if ($count_tut==1)
								print "<li>"._("<b>1</b> TutorIn f&uuml;r die Veranstaltung eingetragen.")."<br><br>";
							elseif ($count_tut>1)
								printf ("<li>"._("<b>%s</b> TutorInnen f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_tut);
							if ($count_bereich==1)
								print "<li>"._("<b>1</b> Bereich f&uuml;r die Veranstaltung eingetragen.")."<br><br>";
							else
								printf ("<li>"._("<b>%s</b> Bereiche f&uuml;r die Veranstaltung eingetragen.")."<br><br>", $count_bereich);
							//Show the result from the resources system
							if ($RESOURCES_ENABLE) {
								if (is_array($updateResult))
									foreach ($updateResult as $key=>$val) {
										if ($val["resource_id"]) {
											if ($val["overlap_assigns"] == TRUE)
												$resources_failed[$val["resource_id"]]=TRUE;
											else
												$resources_booked[$val["resource_id"]]=TRUE;
										}
									}
								if ($resources_booked) {
									$i=0;
									$rooms='';
									foreach ($resources_booked as $key=>$val) {
										if ($i)
											$rooms.=", ";
										$rooms.= sprintf ("<a target=\"_blank\" href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav\">%s</a>", $key, htmlReady(getResourceObjectName($key)));
										$i++;
									}
									if (sizeof($resources_booked) == 1)
										printf ("<li>"._("Die Belegung des Raums %s wurde in die Ressourcenverwaltung eingetragen.")."<br /><br />", $rooms);
									else
										printf ("<li>"._("Die Belegung der R&auml;ume %s wurde in die Ressourcenverwaltung eingetragen."). "<br /><br />", $rooms);
								}
								if ($resources_failed) {
									$i=0;
									$rooms='';
									foreach ($resources_failed as $key=>$val) {
										if ($i)
											$rooms.=", ";
										$rooms.= sprintf ("<a target=\"_blank\" href=\"resources.php?actual_object=%s&view=view_schedule&view_mode=no_nav\">%s</a>", $key, htmlReady(getResourceObjectName($key)));
										$i++;
									}
									if (sizeof($resources_failed) == 1)
										printf ("<li><font color=\"red\">"._("Eine oder mehrere Belegungen des Raumes %s konnte wegen &Uuml;berschneidungen nicht in die Ressourcenverwaltung eingetragen werden!")."<br />", $rooms);
									else
										printf ("<li><font color=\"red\">"._("Eine oder mehrere Belegungen der R&auml;ume %s konnten wegen &Uuml;berschneidungen nicht in die Ressourcenverwaltung eingetragen werden!")."<br />", $rooms);
									print _("Bitte &uuml;berpr&uuml;fen Sie manuell die Belegungen!")."</font><br /><br />";
								}
							}
								
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
			<td class="topic" colspan=2><b>&nbsp;<?=_("Veranstaltungs-Assistent - Schritt 6: Literatur- und Linkliste"); ?></b>
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
				<b><?=_("Schritt 6: Eingeben der Literatur- und Linkliste"); ?></b><br><br>
				<? printf (_("Sie k&ouml;nnen nun Literatur und Links f&uuml;r die eben angelegte Veranstaltung <b>%s</b> eingeben."), $sem_create_data["sem_name"]);
				if ($sem_create_data["modules_list"]["schedule"])
					print " "._("Wenn Sie auf &raquo;weiter&laquo; klicken, haben Sie die M&ouml;glichkeit, mit dem Termin-Assistenten einen Ablaufplan f&uuml;r die Veranstaltung anzulegen.")
				?>
				<br><br>
				</blockqoute>
			</td>
			<td class="blank" align="right" valign="top">
				<img src="./locale/<?=$_language_path?>/LC_PICTURES/hands06.jpg" border="0">
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
							<input type="IMAGE"<?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("abbrechen");?>" name="cancel">
							<?
							if ($sem_create_data["modules_list"]["schedule"]) {
								?>
								&nbsp;<input type="IMAGE"<?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_h">
								<?
							} else {
								?>
								&nbsp;<input type="IMAGE"<?=makeButton("uebernehmen", "src"); ?> border=0 value="<?=_("uebernehmen");?>" name="cmd_h">
								<?
							}
							?>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Literaturliste:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_literat" cols=58 rows=10><? echo $sem_create_data["sem_literat"] ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("In dieses Feld können Sie eine komplette Literaturliste einfügen."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr <? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%" align="right">
							<?=_("Linkliste:"); ?>
						</td>
						<td class="<? echo $cssSw->getClass() ?>" class="<? echo $cssSw->getClass() ?>" width="90%"  colspan=3>
							&nbsp; <textarea name="sem_links" cols=58 rows=10><? echo $sem_create_data["sem_links"] ?></textarea>
							<img  src="./pictures/info.gif" 
								<? echo tooltip(_("In dieses Feld können Sie eine komplette Linkliste einfügen. Alle Links werden später automatisch als Hyperlinks angezeigt."), TRUE, TRUE) ?>
							>
						</td>
					</tr>
					<tr<? $cssSw->switchClass() ?>>
						<td class="<? echo $cssSw->getClass() ?>" width="10%">
							&nbsp;
						</td>
						<td class="<? echo $cssSw->getClass() ?>" width="90%" align="center" colspan=3>
							<input type="IMAGE"<?=makeButton("abbrechen", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cancel">
							<?
							if ($sem_create_data["modules_list"]["schedule"]) {
								?>
								&nbsp;<input type="IMAGE"<?=makeButton("weiter", "src"); ?> border=0 value="<?=_("weiter >>");?>" name="cmd_h">
								<?
							} else {
								?>
								&nbsp;<input type="IMAGE"<?=makeButton("uebernehmen", "src"); ?> border=0 value="<?=_("uebernehmen");?>" name="cmd_h">
								<?
							}
							?>
							
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