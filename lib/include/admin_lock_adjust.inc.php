<?
require_once("lib/classes/DataFieldStructure.class.php");
require_once("lib/classes/LockRules.class.php");
require_once("lib/classes/ZebraTable.class.php");

function show_lock_rules() {
	$lock_rules = new LockRules;
	$all_lock_data = $lock_rules->getAllLockRules();
	return $all_lock_data;
}

function check_empty_lock_rule($lock_id) {
	$db = new DB_Seminar;
	$sql = "SELECT lock_rule FROM seminare WHERE lock_rule='".$lock_id."' ";
	if (!$db->query($sql)) {
		return 0;
	}
	return $db->num_rows();
}

function delete_lock_rule($lock_id) {
	$lock_rules = new LockRules;
	return $lock_rules->deleteLockRule($lock_id);
}

function show_content() {
	$data = "<br><a href=\"".$GLOBALS['PHP_SELF']."?action=new\">"._("<b>Neue Sperrebene anlegen</b>")."</a>";
	$zt = new ZebraTable(array("width"=>"100%", "padding"=>"5"));
	$data .= $zt->openHeaderRow();
	$data .= $zt->cell("<b>"._("Name")."</b>",array("width"=>"25%"));
	$data .= $zt->cell("<b>"._("Beschreibung")."</b>",array("width"=>"55%"));
	$data .= $zt->cell("<b>".""."</b>",array("width"=>"10%"));
	$data .= $zt->cell("<b>".""."</b>",array("width"=>"10%"));
	$data .= $zt->closeRow();
	$all_lock_data = show_lock_rules();
	if (is_array($all_lock_data)) {
		for ($i=0;$i<count($all_lock_data);$i++) {
			$data .= $zt->row(array($all_lock_data[$i]["name"], $all_lock_data[$i]["description"], "<a href=".$GLOBALS['PHP_SELF']."?action=edit&lock_id=".$all_lock_data[$i]["lock_id"]."><img ".makeButton("bearbeiten","src")." border=0></a>", "<a href=".$GLOBALS['PHP_SELF']."?action=confirm_delete&lock_id=".$all_lock_data[$i]["lock_id"]."><img ".makeButton("loeschen","src")." border=0></a>"));
		}
	}
	$data .= $zt->close();
	return $data;
}

function show_lock_rule_form($lockdata="",$edit=0) {
	if ($edit) {
		$form =	"<h3>"._($lockdata["name"]."&nbsp;&auml;ndern")."</h3>"; 
	} else {
		$form =	"<h3>"._("Neue Sperrebene eingeben")."</h3>"; 
	}
	$zt2 = new ZebraTable(array("width"=>"100%","padding"=>"5"));
	//$form .= $zt;
	$form .= "<form action=\"admin_lock_adjust.php\" METHOD=\"POST\">";
	$form .= "<input type=\"hidden\" name=\"lockdata[lock_id]\" value=\"".$lockdata["lock_id"]."\">";
	$form .= $zt2->openRow();
	$form .= $zt2->cell(_("Name"),array("width"=>"80%"));
	$form .= $zt2->cell("<input type=\"text\" name=\"lockdata[name]\" value=\"".$lockdata["name"]."\">",array("width"=>"20%","colspan"=>"2"));
	//$form .= $zt2->row(array(_("Name"),"<input type=\"text\" name=\"lockdata[name]\" value=\"".$lockdata["name"]."\">",""));
	$form .= $zt2->row(array(_("Beschreibung"),"<textarea name=\"lockdata[description]\" rows=5 cols=30>".$lockdata["description"]."</textarea>",""));
	$form .= $zt2->close();
	$form .= "<br>";
	$zt = new ZebraTable(array("width"=>"100%","padding"=>"5"));
	$form .= $zt->openHeaderRow();
	$form .= $zt->cell("<font size=4><b>"._("Attribute")."</b></font>",array("width"=>"73%"));
	$form .= $zt->cell("<b>".""."</b>",array("width"=>"14%","align"=>"left"));
	$form .= $zt->cell("<b>".""."</b>",array("width"=>"13%","align"=>"left"));
	//$form .= $zt->closeRow();
	//$form .= $zt->row(array("<font size=4><b>"._("Attribut")."</b></font>","",""));
	$form .= $zt->closeRow();
	$form .= $zt->headerRow(array("&nbsp;<B>"._("Grunddaten")."</B>", "<B>"._("gesperrt")."</B>", "<B>"._("nicht gesperrt")."</B>"));
	$form .= $zt->closeRow();
	$form .= $zt->openRow();
	if ($lockdata["attributes"]["VeranstaltungsNummer"]) {
		$form .= $zt->row(array(_("Veranstaltungsnummer"),"<input type=\"radio\" name=\"lockdata[attributes][VeranstaltungsNummer]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][VeranstaltungsNummer]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Veranstaltungsnummer"),"<input type=\"radio\" name=\"lockdata[attributes][VeranstaltungsNummer]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][VeranstaltungsNummer]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Institut_id"]) {
		$form .= $zt->row(array(_("beteiligte Einrichtung"),"<input type=\"radio\" name=\"lockdata[attributes][Institut_id]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Institut_id]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("beteiligte Einrichtung"),"<input type=\"radio\" name=\"lockdata[attributes][Institut_id]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Institut_id]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Name"]) {
		$form .= $zt->row(array(_("Name"),"<input type=\"radio\" name=\"lockdata[attributes][Name]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Name]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Name"),"<input type=\"radio\" name=\"lockdata[attributes][Name]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Name]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Untertitel"]) {
		$form .= $zt->row(array(_("Untertitel"),"<input type=\"radio\" name=\"lockdata[attributes][Untertitel]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Untertitel]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Untertitel"),"<input type=\"radio\" name=\"lockdata[attributes][Untertitel]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Untertitel]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["status"]) {
		$form .= $zt->row(array(_("Status"),"<input type=\"radio\" name=\"lockdata[attributes][status]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][status]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Status"),"<input type=\"radio\" name=\"lockdata[attributes][status]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][status]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Beschreibung"]) {
		$form .= $zt->row(array(_("Beschreibung"),"<input type=\"radio\" name=\"lockdata[attributes][Beschreibung]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Beschreibung]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Beschreibung"),"<input type=\"radio\" name=\"lockdata[attributes][Beschreibung]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Beschreibung]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Ort"]) {
		$form .= $zt->row(array(_("Ort"),"<input type=\"radio\" name=\"lockdata[attributes][Ort]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Ort]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Ort"),"<input type=\"radio\" name=\"lockdata[attributes][Ort]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Ort]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["art"]) {
		$form .= $zt->row(array(_("Veranstaltungstyp"),"<input type=\"radio\" name=\"lockdata[attributes][art]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][art]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Veranstaltungstyp"),"<input type=\"radio\" name=\"lockdata[attributes][art]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][art]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["ects"]) {
		$form .= $zt->row(array(_("ECTS-Punkte"),"<input type=\"radio\" name=\"lockdata[attributes][ects]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][ects]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("ECTS-Punkte"),"<input type=\"radio\" name=\"lockdata[attributes][ects]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][ects]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_turnout"]) {
		$form .= $zt->row(array(_("Teilnehmerzahl"),"<input type=\"radio\" name=\"lockdata[attributes][admission_turnout]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_turnout]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Teilnehmerzahl"),"<input type=\"radio\" name=\"lockdata[attributes][admission_turnout]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_turnout]\" value=0 checked>"));	
	}
	if ($edit) {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"confirm_edit\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("uebernehmen", "src").">",array("colspan"=>"3","align"=>"center"));
	} else {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"insert\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("anlegen", "src").">",array("colspan"=>"3","align"=>"center"));
	}
	$form .= $zt->closeRow();
	$form .= $zt->headerRow(array("&nbsp;<B>"._("Personen und Einordnung")."</B>", "<B>"._("gesperrt")."</B>", "<B>"._("nicht gesperrt")."</B>"));
	$form .= $zt->closeRow();
	$form .= $zt->openRow();
	if ($lockdata["attributes"]["dozent"]) {
		$form .= $zt->row(array(_("DozentInnen"),"<input type=\"radio\" name=\"lockdata[attributes][dozent]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][dozent]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("DozentInnen"),"<input type=\"radio\" name=\"lockdata[attributes][dozent]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][dozent]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["tutor"]) {
		$form .= $zt->row(array(_("TutorInnen"),"<input type=\"radio\" name=\"lockdata[attributes][tutor]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][tutor]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("TutorInnen"),"<input type=\"radio\" name=\"lockdata[attributes][tutor]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][tutor]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["seminar_inst"]) {
		$form .= $zt->row(array(_("Heimateinrichtung"),"<input type=\"radio\" name=\"lockdata[attributes][seminar_inst]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][seminar_inst]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Heimateinrichtung"),"<input type=\"radio\" name=\"lockdata[attributes][seminar_inst]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][seminar_inst]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["sem_tree"]) {
		$form .= $zt->row(array(_("Studienbereiche"),"<input type=\"radio\" name=\"lockdata[attributes][sem_tree]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][sem_tree]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Studienbereiche"),"<input type=\"radio\" name=\"lockdata[attributes][sem_tree]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][sem_tree]\" value=0 checked>"));	
	}
	if ($edit) {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"confirm_edit\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("uebernehmen", "src").">",array("colspan"=>"3","align"=>"center"));
	} else {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"insert\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("anlegen", "src").">",array("colspan"=>"3","align"=>"center"));
	}
	$form .= $zt->closeRow();
	$form .= $zt->headerRow(array("&nbsp;<B>"._("weitere Daten")."</B>", "<B>"._("gesperrt")."</B>", "<B>"._("nicht gesperrt")."</B>"));
	$form .= $zt->closeRow();
	$form .= $zt->openRow();
	if ($lockdata["attributes"]["Sonstiges"]) {
		$form .= $zt->row(array(_("Sonstiges"),"<input type=\"radio\" name=\"lockdata[attributes][Sonstiges]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Sonstiges]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Sonstiges"),"<input type=\"radio\" name=\"lockdata[attributes][Sonstiges]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Sonstiges]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["teilnehmer"]) {
		$form .= $zt->row(array(_("Beschreibung des Teilnehmerkreises"),"<input type=\"radio\" name=\"lockdata[attributes][teilnehmer]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][teilnehmer]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Beschreibung des Teilnehmerkreises"),"<input type=\"radio\" name=\"lockdata[attributes][teilnehmer]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][teilnehmer]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["voraussetzungen"]) {
		$form .= $zt->row(array(_("Teilnahmevoraussetzungen"),"<input type=\"radio\" name=\"lockdata[attributes][voraussetzungen]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][voraussetzungen]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Teilnahmevoraussetzungen"),"<input type=\"radio\" name=\"lockdata[attributes][voraussetzungen]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][voraussetzungen]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["lernorga"]) {
		$form .= $zt->row(array(_("Lernorganisation"),"<input type=\"radio\" name=\"lockdata[attributes][lernorga]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][lernorga]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Lernorganisation"),"<input type=\"radio\" name=\"lockdata[attributes][lernorga]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][lernorga]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["leistungsnachweis"]) {
		$form .= $zt->row(array(_("Leistungsnachweis"),"<input type=\"radio\" name=\"lockdata[attributes][leistungsnachweis]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][leistungsnachweis]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Leistungsnachweis"),"<input type=\"radio\" name=\"lockdata[attributes][leistungsnachweis]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][leistungsnachweis]\" value=0 checked>"));	
	}
	$datafields = get_all_seminars_generic_datafields();
	
  $datafields_list = DataFieldStructure::getDataFieldStructures("sem");

	foreach ($datafields_list as $key=>$val) {
		if ($lockdata["attributes"][$key]) {
			$form .= $zt->row(array($val->data["name"],"<input type=\"radio\" name=\"lockdata[attributes][".$val->data["datafield_id"]."]\" value=\"1\" checked>","<input type=\"radio\" name=\"lockdata[attributes][".$val->data["datafield_id"]."]\" value=\"0\">"));
		} else {
			$form .= $zt->row(array($val->data["name"],"<input type=\"radio\" name=\"lockdata[attributes][".$val->data["datafield_id"]."]\" value=\"1\">","<input type=\"radio\" name=\"lockdata[attributes][".$val->data["datafield_id"]."]\" value=\"0\" checked>"));
		}
	}
	if ($edit) {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"confirm_edit\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("uebernehmen", "src").">",array("colspan"=>"3","align"=>"center"));
	} else {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"insert\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("anlegen", "src").">",array("colspan"=>"3","align"=>"center"));
	}
	$form .= $zt->closeRow();
	$form .= $zt->headerRow(array("&nbsp;<B>"._("Zeiten/Räume")."</B>", "<B>"._("gesperrt")."</B>", "<B>"._("nicht gesperrt")."</B>"));
	$form .= $zt->closeRow();
	$form .= $zt->openRow();
	if ($lockdata["attributes"]["room_time"]) {
		$form .= $zt->row(array(_("Zeiten/Räume"),"<input type=\"radio\" name=\"lockdata[attributes][room_time]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][room_time]\" value=0>"));	
    } else {
		$form .= $zt->row(array(_("Zeiten/Räume"),"<input type=\"radio\" name=\"lockdata[attributes][room_time]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][room_time]\" value=0 checked>"));	
    }
    /*if ($lockdata["attributes"]["start_time"]) {
		$form .= $zt->row(array(_("Semester"),"<input type=\"radio\" name=\"lockdata[attributes][start_time]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][start_time]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Semester"),"<input type=\"radio\" name=\"lockdata[attributes][start_time]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][start_time]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["duration_time"]) {
		$form .= $zt->row(array(_("Dauer (in Semestern)"),"<input type=\"radio\" name=\"lockdata[attributes][duration_time]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][duration_time]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Dauer (in Semestern)"),"<input type=\"radio\" name=\"lockdata[attributes][duration_time]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][duration_time]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["metadata_dates"]) {
		$form .= $zt->row(array(_("Termine"),"<input type=\"radio\" name=\"lockdata[attributes][metadata_dates]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][metadata_dates]\" value=0>"));
	} else {
		$form .= $zt->row(array(_("Termine"),"<input type=\"radio\" name=\"lockdata[attributes][metadata_dates]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][metadata_dates]\" value=0 checked>"));
	}
	if ($lockdata["attributes"]["further_time_dates"]) {
		$form .= $zt->row(array(_("Veranstaltungsbeginn"),"<input type=\"radio\" name=\"lockdata[attributes][further_time_dates]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][further_time_dates]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Veranstaltungsbeginn"),"<input type=\"radio\" name=\"lockdata[attributes][further_time_dates]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][further_time_dates]\" value=0 checked>"));	
	}*/
	if ($edit) {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"confirm_edit\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("uebernehmen", "src").">",array("colspan"=>"3","align"=>"center"));
	} else {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"insert\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("&nbsp;",array("colspan" => "1"));
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("anlegen", "src").">",array("colspan"=>"3","align"=>"center"));
	}
	$form .= $zt->closeRow();
	$form .= $zt->headerRow(array("&nbsp;<B>"._("Zugangsberechtigungen")."</B>", "<B>"._("gesperrt")."</B>", "<B>"._("nicht gesperrt")."</B>"));
	$form .= $zt->closeRow();
	$form .= $zt->openRow();
	if ($lockdata["attributes"]["admission_endtime"]) {
		$form .= $zt->row(array(_("Aufhebung der Kontigentierung"),"<input type=\"radio\" name=\"lockdata[attributes][admission_endtime]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_endtime]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Aufhebung der Kontingentierung"),"<input type=\"radio\" name=\"lockdata[attributes][admission_endtime]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_endtime]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_waitlist"]) {
		$form .= $zt->row(array(_("Aktivieren/Deaktivieren der Warteliste"),"<input type=\"radio\" name=\"lockdata[attributes][admission_waitlist]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_waitlist]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Aktivieren/Deaktivieren der Warteliste"),"<input type=\"radio\" name=\"lockdata[attributes][admission_waitlist]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_waitlist]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_binding"]) {
		$form .= $zt->row(array(_("Verbindlichkeit der Anmeldung"),"<input type=\"radio\" name=\"lockdata[attributes][admission_binding]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_binding]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Verbindlichkeit der Anmeldung"),"<input type=\"radio\" name=\"lockdata[attributes][admission_binding]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_binding]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_type"]) {
		$form .= $zt->row(array(_("Typ des Anmeldeverfahrens"),"<input type=\"radio\" name=\"lockdata[attributes][admission_type]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_type]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Typ des Anmeldeverfahrens"),"<input type=\"radio\" name=\"lockdata[attributes][admission_type]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_type]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_selection_take_place"]) {
		$form .= $zt->row(array(_("Zeit/Datum des Losverfahrens"),"<input type=\"radio\" name=\"lockdata[attributes][admission_selection_take_place]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_selection_take_place]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Zeit/Datum des Losverfahrens"),"<input type=\"radio\" name=\"lockdata[attributes][admission_selection_take_place]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_selection_take_place]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_prelim"]) {
		$form .= $zt->row(array(_("Vorl&auml;ufigkeit der Anmeldungen"),"<input type=\"radio\" name=\"lockdata[attributes][admission_prelim]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_prelim]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Vorl&auml;ufigkeit der Anmeldungen"),"<input type=\"radio\" name=\"lockdata[attributes][admission_prelim]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_prelim]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_prelim_txt"]) {
		$form .= $zt->row(array(_("Hinweistext bei Anmeldungen"),"<input type=\"radio\" name=\"lockdata[attributes][admission_prelim_txt]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_prelim_txt]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Hinweistext bei Anmeldungen"),"<input type=\"radio\" name=\"lockdata[attributes][admission_prelim_txt]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_prelim_txt]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_starttime"]) {
		$form .= $zt->row(array(_("Startzeitpunkt der Anmeldem&ouml;glichkeit"),"<input type=\"radio\" name=\"lockdata[attributes][admission_starttime]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_starttime]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Startzeitpunkt der Anmeldem&ouml;glichkeit"),"<input type=\"radio\" name=\"lockdata[attributes][admission_starttime]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_starttime]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["admission_endtime_sem"]) {
		$form .= $zt->row(array(_("Endzeitpunkt der Anmeldem&ouml;glichkeit"),"<input type=\"radio\" name=\"lockdata[attributes][admission_endtime_sem]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][admission_endtime_sem]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Endzeitpunkt der Anmeldem&ouml;glichkeit"),"<input type=\"radio\" name=\"lockdata[attributes][admission_endtime_sem]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][admission_endtime_sem]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Lesezugriff"]) {
		$form .= $zt->row(array(_("Lesezugriff"),"<input type=\"radio\" name=\"lockdata[attributes][Lesezugriff]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Lesezugriff]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Lesezugriff"),"<input type=\"radio\" name=\"lockdata[attributes][Lesezugriff]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Lesezugriff]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Schreibzugriff"]) {
		$form .= $zt->row(array(_("Schreibzugriff"),"<input type=\"radio\" name=\"lockdata[attributes][Schreibzugriff]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Schreibzugriff]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Schreibzugriff"),"<input type=\"radio\" name=\"lockdata[attributes][Schreibzugriff]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Schreibzugriff]\" value=0 checked>"));	
	}
	if ($lockdata["attributes"]["Passwort"]) {
		$form .= $zt->row(array(_("Passwort"),"<input type=\"radio\" name=\"lockdata[attributes][Passwort]\" value=1 checked>","<input type=\"radio\" name=\"lockdata[attributes][Passwort]\" value=0>"));	
	} else {
		$form .= $zt->row(array(_("Passwort"),"<input type=\"radio\" name=\"lockdata[attributes][Passwort]\" value=1>","<input type=\"radio\" name=\"lockdata[attributes][Passwort]\" value=0 checked>"));	
	}
	if ($edit) {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"confirm_edit\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("uebernehmen", "src").">&nbsp;<a href=\"{$GLOBALS['PHP_SELF']}\"><img ".makeButton("abbrechen","src")." border=0></a>",array("colspan"=>"3","align"=>"center"));
	} else {
		$form .= "<input type=\"hidden\" name=\"action\" value=\"insert\">";
		$form .= $zt->openRow();
		$form .= $zt->cell("<input type=\"IMAGE\" ".makeButton("anlegen", "src").">&nbsp;<a href=\"{$GLOBALS['PHP_SELF']}\"><img ".makeButton("abbrechen","src")." border=0></a>",array("colspan"=>"3","align"=>"center"));
	}
	$form .= "</form>";
	$form .= $zt->close();
	return $form;
}

function update_existing_rule($updatedata) {
	$lock_rules = new LockRules;
	$success = $lock_rules->updateExistingLockRule($updatedata);
	return $success;
}

function parse_lockdata($lockdata) {
	$insertdata = array();
	$insertdata["name"] = $lockdata["name"];
	$insertdata["lock_id"] = $lockdata["lock_id"];
	$insertdata["description"] = $lockdata["description"];
	while (list($key,$val)=each($lockdata["attributes"])) {
		if ($val==1) {
			$insertdata["attributes"][$key] = $val;
		} 
	}
	return $insertdata;
}

function insert_lock_rule($insertdata) {
	$lock_rule = new LockRules;
	return $lock_rule->insertNewLockRule($insertdata);
}

function get_all_seminars_generic_datafields() {
	$i++;
	$sql = "SELECT * FROM datafields WHERE object_class=1 ORDER BY priority";
	$db = new DB_Seminar;
	if (!$db->query($sql)) {
		echo "error! DB-Query";
		return 0;
	}
	if ($db->num_rows()==0) {
		return 0;
	}
	while ($db->next_record()) {
		$datafields[$i]["name"] = $db->f("name");
		$datafields[$i]["id"]	= $db->f("datafield_id");
		$i++;
	}
	return $datafields;
}

function get_lock_rule($lock_id) {
	$lock_rules = new LockRules;
	$lockdata = $lock_rules->getLockRule($lock_id);
	return $lockdata;
}

function get_lock_rule_by_name($name) {
	$lock_rule = new LockRules;
	return $lock_rule->getLockRuleByName($name);
}

?>
