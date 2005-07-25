<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// edit_about.php
// administration of personal home page
// 
// Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>,
// Niklas Nohlen <nnohlen@gwdg.de>, Miro Freitag <mfreita@goe.net>, André Noack <andre.noack@gmx.net>
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
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if(!$logout && ($auth->auth["uid"] == "nobody"));

if ($usr_name)  $username=$usr_name; //wenn wir von den externen Seiten kommen, nehmen wir den Usernamen aus usr_name, falls dieser gesetzt ist, um die Anmeldeprozedur nicht zu verwirren....

require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/kategorien.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/messaging.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/language.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/DataFields.class.php"); 
require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/UserConfig.class.php"); 

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

if (!isset($ALLOW_CHANGE_NAME)) $ALLOW_CHANGE_NAME = TRUE; //wegen Abwärtskompatibilität, erst ab 1.1 bekannt

// Klassendefinition

class about extends messaging {

var $db;     //unsere Datenbankverbindung
var $auth_user = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle auth_user_md5
var $user_info = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle user_info
var $user_inst = array();        // assoziatives Array, enthält die Userdaten aus der Tabelle user_inst
var $user_studiengang = array(); // assoziatives Array, enthält die Userdaten aus der Tabelle user_studiengang
var $check = "";    //Hilfsvariable für den Rechtecheck
var $special_user = FALSE;  // Hilfsvariable für bes. Institutsfunktionen
var $msg = ""; //enthält evtl Fehlermeldungen
var $max_file_size = 100; //max Größe der Bilddatei in KB
var $img_max_h = 250; // max picture height
var $img_max_w = 200; // max picture width
var $uploaddir = "./user"; //Uploadverzeichnis für Bilder
var $logout_user = FALSE; //Hilfsvariable, zeigt an, ob der User ausgeloggt werden muß
var $priv_msg = "";  //Änderungsnachricht bei Adminzugriff
var $default_url = "http://www"; //default fuer private URL


function about($username,$msg) {  // Konstruktor, prüft die Rechte
	global $user,$perm,$auth;

	$this->db = new DB_Seminar;
	$this->get_auth_user($username);
	$this->DataFields = new DataFields($this->auth_user["user_id"]);	
	$this->msg = rawurldecode($msg); //Meldungen restaurieren
	
	// der user selbst natürlich auch
	if ($auth->auth["uname"] == $username AND $perm->have_perm("autor"))
		$this->check="user";
	//bei admins schauen wir mal
	elseif ($auth->auth["perm"]=="admin") {
		$this->db->query("SELECT a.user_id FROM user_inst AS a LEFT JOIN user_inst AS b USING (Institut_id) WHERE (b.inst_perms='admin' AND b.user_id='$user->id') AND (a.user_id='".$this->auth_user["user_id"]."' AND a.inst_perms IN ('dozent','tutor','autor'))");
		if ($this->db->num_rows()) 
			$this->check="admin";

		if ($perm->is_fak_admin()){
			$this->db->query("SELECT c.user_id FROM user_inst a LEFT JOIN Institute b ON(a.Institut_id=b.fakultaets_id)  LEFT JOIN user_inst c USING(Institut_id) WHERE a.user_id='$user->id' AND a.inst_perms='admin' AND c.user_id='".$this->auth_user["user_id"]."'");
			if ($this->db->next_record()) 
				$this->check="admin";
		}
	}
	//root darf mal wieder alles
	elseif ($auth->auth["perm"]=="root")
		$this->check="admin";
	else
		$this->check="";
	//hier ist wohl was falschgelaufen...
	if ($this->auth_user["username"]=="")
		$this->check="";

	return;
}


function get_auth_user($username) {
	//ein paar userdaten brauchen wir schon mal
	$this->db->query("SELECT * FROM auth_user_md5 WHERE username = '$username'");
	if ($this->db->next_record()) {
		$fields = $this->db->metadata();
		for ($i=0; $i<count($fields); $i++) {
			$field_name = $fields[$i]["name"];
			$this->auth_user[$field_name] = $this->db->f("$field_name");
		}
	}
	if (!$this->auth_user['auth_plugin']){
		$this->auth_user['auth_plugin'] = "standard";
	}
}

// füllt die arrays  mit Daten
function get_user_details() {
	$this->db->query("SELECT * FROM user_info WHERE user_id = '".$this->auth_user["user_id"]."'");
	if ($this->db->next_record()) {
		$fields = $this->db->metadata();
		for ($i=0; $i<count($fields); $i++) {
			$field_name = $fields[$i]["name"];
			$this->user_info[$field_name] = $this->db->f("$field_name");
			if (!$this->user_info["Home"])
				$this->user_info["Home"]=$this->default_url;
		}
	}

	$this->db->query("SELECT user_studiengang.*,studiengaenge.name FROM user_studiengang LEFT JOIN studiengaenge USING (studiengang_id) WHERE user_id = '".$this->auth_user["user_id"]."' ORDER BY studiengang_id");
	while ($this->db->next_record()) {
		$this->user_studiengang[$this->db->f("studiengang_id")] = array("name" => $this->db->f("name"));
	}


	$this->db->query("SELECT user_inst.*,Institute.Name FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_id = '".$this->auth_user["user_id"]."' ORDER BY priority ASC, Institut_id ASC");
	while ($this->db->next_record()) {
		$this->user_inst[$this->db->f("Institut_id")] =
				array("inst_perms" => $this->db->f("inst_perms"),
				"sprechzeiten" => $this->db->f("sprechzeiten"),
				"raum" => $this->db->f("raum"),
				"Telefon" => $this->db->f("Telefon"),
				"Fax" => $this->db->f("Fax"),
				"Name" => $this->db->f("Name"),
				"externdefault" => $this->db->f("externdefault"),
				"priority" => $this->db->f("priority"),
				"visible" => $this->db->f("visible"));
		if ($this->db->f("inst_perms")!="user")
			$this->special_user=TRUE;
	}

	return;
}

function imaging($img,$img_size,$img_name) {
	global $TMP_PATH, $CONVERT_PATH;

	if ($img_size > ($this->max_file_size*1024)) { //Bilddatei ist zu groß
		$this->msg = "error§" . sprintf(_("Die hochgeladene Bilddatei ist %s KB groß.<br>Die maximale Dateigröße beträgt %s KB!"), round($img_size/1024), $this->max_file_size);
		return;
	}
	
	if (!$img_name) { //keine Datei ausgewählt!
		$this->msg = "error§" . _("Sie haben keine Datei zum Hochladen ausgewählt!");
		return;
	}
	
	//Dateiendung bestimmen
	$dot = strrpos($img_name,".");
	if ($dot) {
		$l = strlen($img_name) - $dot;
		$ext = strtolower(substr($img_name,$dot+1,$l));
	}
	//passende Endung ?
	if ($ext != "jpg" && $ext != "gif" && $ext != "png") {
		$this->msg = "error§" . sprintf(_("Der Dateityp der Bilddatei ist falsch (%s).<br>Es sind nur die Dateiendungen .gif, .png und .jpg erlaubt!"), $ext);
		return;
	}
	//na dann kopieren wir mal...
	$newfile = $this->uploaddir . "/".$this->auth_user["user_id"].".jpg";
	if(!@copy($img,$newfile)) {
		$this->msg = "error§" . _("Es ist ein Fehler beim Kopieren der Datei aufgetreten. Das Bild wurde nicht hochgeladen!");
		return;
	} else {
		list($width, $height, $img_type, ) = getimagesize($img);
		if (extension_loaded('gd')){
			switch ($ext) {  //original Bild einlesen
				case 'gif': //GIF
				if (function_exists('ImageCreateFromGIF')){
					$img_org = @ImageCreateFromGIF($img);
				}
				break;
				case 'jpg': //JPG
				if (function_exists('ImageCreateFromJPEG')){
					$img_org = @ImageCreateFromJPEG($img);
				}
				break;
				case 'png': //PNG
				if (function_exists('ImageCreateFromPNG')){
					$img_org = @ImageCreateFromPNG($img);
				}
				break;
				default:
				$img_org = FALSE;
			} // end switch
		}
		if (!$width && $img_org){
			$width = @ImageSX($img_org);
			$height = @ImageSY($img_org);
		}
		// Check picture size
		$hscale = $height / $this->img_max_h;
		$wscale = $width / $this->img_max_w;
		if ($width && ($hscale > 1) || ($wscale > 1)) {
			$scale = ($hscale > $wscale)? $hscale : $wscale;
			$newwidth = floor($width / $scale);
			$newheight= floor($height / $scale);
			$ret_val = false;
			//try ImageMagick...
			if (file_exists($CONVERT_PATH)){
				$out = array();
				$identify_path = dirname($CONVERT_PATH) . '/identify';
				if (file_exists($identify_path)){
					exec($identify_path . ' -format "%n" ' . $newfile, $out, $ret_val);
					if (!$ret_val){
						if (count($out) > 1 || $out[0] > 1){
							$ret_val = true;
						} else {
							exec($CONVERT_PATH . ' -resize ' . $newwidth . 'x' . $newheight . '! ' . $newfile .' ' . $newfile, $out, $ret_val);
						}
					}
				} else {
					$ret_val = true;
				}
			} else {
				$ret_val = true;
			}
			//no luck, lets try GD...
			if ($ret_val){
				if (function_exists('imagecopyresampled')){
					// leeres Bild erzeugen
					$img_res = @ImageCreateTrueColor($newwidth, $newheight);
					if (!$img_org) {
						$ret_val = true;
					} else {
						// resampeln und als jpg speichern
						$ret_val = @ImageCopyResampled ( $img_res, $img_org, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
						$ret_val = @ImageJPEG ( $img_res, $newfile , 70);
						$ret_val = $ret_val ? false : true;
						@ImageDestroy ( $img_res);
						@ImageDestroy ( $img_org);
					}
				} else {
					$ret_val = true; //still no luck, picture can not be resized
				}
			}
		}
		//resizing not possible or dimensions not available
		if ($ret_val || !$width){
			@unlink($newfile);
			$this->msg = "error§" . _("Es ist ein Fehler beim Bearbeiten des Bildes aufgetreten.");
			if (!$width){
				$this->msg .= " ". _("Die Gr&ouml;&szlig;e des Bildes konnte nicht ermittelt werden.");
			} elseif ($ret_val){
				$this->msg .= " ". _("Die Gr&ouml;&szlig;e des Bildes konnte nicht angepasst werden.") . " " . sprintf(_("Die maximale Gr&ouml;&szlig;e betr&auml;gt %s x %s Pixel."),$this->img_max_w,$this->img_max_h);
			}
			return;
		}
		$this->msg = "msg§" . _("Die Bilddatei wurde erfolgreich hochgeladen. Eventuell sehen Sie das neue Bild erst, nachdem Sie diese Seite neu geladen haben (in den meisten Browsern F5 dr&uuml;cken).");
		setTempLanguage($this->auth_user["user_id"]);
		$this->priv_msg = _("Ein neues Bild wurde hochgeladen.\n");
		restoreLanguage();
	}
	return;
}


function studiengang_edit($studiengang_delete,$new_studiengang) {
	if (is_array($studiengang_delete)) {
		for ($i=0; $i < count($studiengang_delete); $i++) {
			$this->db->query("DELETE FROM user_studiengang WHERE user_id='".$this->auth_user["user_id"]."' AND studiengang_id='$studiengang_delete[$i]'");
			if (!$this->db->affected_rows())
				$this->msg = $this->msg."error§" . sprintf(_("Fehler beim L&ouml;schen in user_studiengang bei ID=%s"), $studiengang_delete[$i]) . "§";
		}
	}

	if ($new_studiengang) {
		$this->db->query("INSERT IGNORE INTO user_studiengang (user_id,studiengang_id) VALUES ('".$this->auth_user["user_id"]."','$new_studiengang')");
		if (!$this->db->affected_rows())
			$this->msg = $this->msg."error§" . sprintf(_("Fehler beim Einf&uuml;gen in user_studiengang bei ID=%s"), $new_studiengang) . "§";
	}

	if ( ($studiengang_delete || $new_studiengang) && !$this->msg) {
		$this->msg = "msg§" . _("Die Zuordnung zu Studiengängen wurde ge&auml;ndert.");
		setTempLanguage($this->auth_user["user_id"]);
		$this->priv_msg= _("Die Zuordnung zu Studiengängen wurde geändert!\n");
		restoreLanguage();
	}

	return;
}



function inst_edit($inst_delete,$new_inst) {
	if (is_array($inst_delete)) {
		for ($i=0; $i < count($inst_delete); $i++) {
			$this->db->query("DELETE FROM user_inst WHERE user_id='".$this->auth_user["user_id"]."' AND Institut_id='$inst_delete[$i]'");
			if (!$this->db->affected_rows())
				$this->msg = $this->msg . "error§" . sprintf(_("Fehler beim L&ouml;schen in user_inst bei ID=%s"), $inst_delete[$i]) . "§";
		}
	}

	if ($new_inst) {
		$this->db->query("INSERT IGNORE INTO user_inst (user_id,Institut_id,inst_perms) VALUES ('".$this->auth_user["user_id"]."','$new_inst','user')");
		if (!$this->db->affected_rows())
			$this->msg = $this->msg . "error§" . sprintf(_("Fehler beim Einf&uuml;gen in user_inst bei ID=%s"), $new_inst) . "§";
	}

	if ( ($inst_delete || $new_inst) && !$this->msg) {
		$this->msg = "msg§" . _("Die Zuordnung zu Einrichtungen wurde ge&auml;ndert.");
		setTempLanguage($this->auth_user["user_id"]);
		$this->priv_msg= _("Die Zuordnung zu Einrichtungen wurde geändert!\n");
		restoreLanguage();
	}

	return;
}

function special_edit ($raum, $sprech, $tel, $fax, $name, $default_inst, $visible) {
	if (is_array($raum)) {
		while (list($inst_id, $detail) = each($raum)) {
			$query = "UPDATE user_inst SET raum='$detail', sprechzeiten='$sprech[$inst_id]', ";
			$query .= "Telefon='$tel[$inst_id]', Fax='$fax[$inst_id]', externdefault=";
			$query .= $default_inst == $inst_id ? '1' : '0';
			$query .= ", visible=" . (isset($visible[$inst_id]) ? '0' : '1');
			$query .= " WHERE Institut_id='$inst_id' AND user_id='" . $this->auth_user["user_id"] . "'";
			$this->db->query($query);
			if ($this->db->affected_rows()) {
				$this->msg = $this->msg . "msg§" . sprintf(_("Ihre Daten an der Einrichtung %s wurden ge&auml;ndert"), $name[$inst_id]) . "§";
				setTempLanguage($this->auth_user["user_id"]);
				$this->priv_msg = $this->priv_msg . sprintf(_("Ihre Daten an der Einrichtung %s wurden geändert.\n"), $name[$inst_id]);
				restoreLanguage();
			}
		}
	}
	return;
}

function edit_leben($lebenslauf,$schwerp,$publi) {
	
	//check ob die blobs verändert wurden...
	$this->db->query("SELECT  lebenslauf, schwerp, publi FROM user_info WHERE user_id='".$this->auth_user["user_id"]."'");
	$this->db->next_record();
	if ($lebenslauf!=$this->db->f("lebenslauf") || $schwerp!=$this->db->f("schwerp") || $publi!=$this->db->f("publi") || $resultDataFields) {
		$this->db->query("UPDATE user_info SET lebenslauf='$lebenslauf', schwerp='$schwerp', publi='$publi', chdate='".time()."' WHERE user_id='".$this->auth_user["user_id"]."'");
		$this->msg = $this->msg . "msg§" . _("Daten im Lebenslauf u.a. wurden ge&auml;ndert") . "§";
		setTempLanguage($this->auth_user["user_id"]);
		$this->priv_msg = _("Daten im Lebenslauf u.a. wurden geändert.\n");
		restoreLanguage();
	}
}


function edit_pers($password,$check_pass,$response,$new_username,$vorname,$nachname,$email,$telefon,$anschrift,$home,$hobby,$geschlecht,$title_front,$title_front_chooser,$title_rear,$title_rear_chooser,$view) {
	global $UNI_NAME_CLEAN, $_language_path, $auth, $perm; 
	global $ALLOW_CHANGE_USERNAME, $ALLOW_CHANGE_EMAIL, $ALLOW_CHANGE_NAME;
  
	//erstmal die "unwichtigen" Daten
	if ($home == $this->default_url)
	$home='';
	if($title_front == "")
	$title_front = $title_front_chooser;
	if($title_rear == "")
	$title_rear = $title_rear_chooser;
	$query = "";
	if (!StudipAuthAbstract::CheckField("user_info.privatnr", $this->auth_user['auth_plugin'])){
		$query .= "privatnr='$telefon',";
	}
	if (!StudipAuthAbstract::CheckField("user_info.privadr", $this->auth_user['auth_plugin'])){
		$query .= "privadr='$anschrift',";
	}
	if (!StudipAuthAbstract::CheckField("user_info.Home", $this->auth_user['auth_plugin'])){
		$query .= "Home='$home',";
	}
	if (!StudipAuthAbstract::CheckField("user_info.hobby", $this->auth_user['auth_plugin'])){
		$query .= "hobby='$hobby',";
	}
	if (!StudipAuthAbstract::CheckField("user_info.geschlecht", $this->auth_user['auth_plugin'])){
		$query .= "geschlecht='$geschlecht',";
	}
	if (!StudipAuthAbstract::CheckField("user_info.title_front", $this->auth_user['auth_plugin'])){
		$query .= "title_front='$title_front',";
	}
	if (!StudipAuthAbstract::CheckField("user_info.title_rear", $this->auth_user['auth_plugin'])){
		$query .= "title_rear='$title_rear',";
	}
	if ($query != "") {
		$query = "UPDATE user_info SET " . $query . " chdate='".time()."' WHERE user_id='".$this->auth_user["user_id"]."'";
		$this->db->query($query);
		if ($this->db->affected_rows()) {
			$this->msg = $this->msg . "msg§" . _("Ihre pers&ouml;nlichen Daten wurden ge&auml;ndert.") . "§";
			setTempLanguage($this->auth_user["user_id"]);
			$this->priv_msg = _("Ihre persönlichen Daten wurden geändert.\n");
			restoreLanguage();
		}
	}
	
	$new_username = trim($new_username);
	$vorname = trim($vorname);
	$nachname = trim($nachname);
	$email = trim($email);
	
	//nur nötig wenn der user selbst seine daten ändert
	if ($this->check == "user") {
		//erstmal die Syntax checken $validator wird in der local.inc.php benutzt, sollte also funzen
		$validator=new email_validation_class; ## Klasse zum Ueberpruefen der Eingaben
		$validator->timeout=10;
		
		if (!StudipAuthAbstract::CheckField("auth_user_md5.password", $this->auth_user['auth_plugin']) && (($response && $response!=md5("*****")) || $password!="*****")) {      //Passwort verändert ?
			// auf doppelte Vergabe wird weiter unten getestet.
			if (!isset($response) || $response=="") { // wir haben kein verschluesseltes Passwort
				if (!$validator->ValidatePassword($password)) {
					$this->msg=$this->msg . "error§" . _("Das Passwort ist nicht lang genug!") . "§";
					return false;
				}
				if ($check_pass != $password) {
					$this->msg=$this->msg . "error§" . _("Die Wiederholung des Passwortes ist falsch! Bitte geben sie das exakte Passwort ein!") . "§";
					return false;
				}
				$newpass=md5($password);             // also können wir das unverschluesselte Passwort testen
			} else
			$newpass=$response;
			
			$this->db->query("UPDATE auth_user_md5 SET password='$newpass' WHERE user_id='".$this->auth_user["user_id"]."'");
			$this->msg=$this->msg . "msg§" . _("Ihr Passwort wurde ge&auml;ndert!") . "§";
		}
		
		if (!StudipAuthAbstract::CheckField("auth_user_md5.Vorname", $this->auth_user['auth_plugin']) && $vorname!=$this->auth_user["Vorname"]) { //Vornamen verändert ?
			if ($ALLOW_CHANGE_NAME) {
				if (!$validator->ValidateName($vorname)) {
					$this->msg=$this->msg . "error§" . _("Der Vorname fehlt oder ist unsinnig!") . "§";
					return false;
				}   // Vorname nicht korrekt oder fehlend
				$this->db->query("UPDATE auth_user_md5 SET Vorname='$vorname' WHERE user_id='".$this->auth_user["user_id"]."'");
				$this->msg=$this->msg . "msg§" . _("Ihr Vorname wurde ge&auml;ndert!") . "§";
			}
		}
		
		if (!StudipAuthAbstract::CheckField("auth_user_md5.Nachname", $this->auth_user['auth_plugin']) && $nachname!=$this->auth_user["Nachname"]) { //Namen verändert ?
			if ($ALLOW_CHANGE_NAME) {
				if (!$validator->ValidateName($nachname)) {
					$this->msg=$this->msg . "error§" . _("Der Nachname fehlt oder ist unsinnig!") . "§";
					return false;
				}   // Nachname nicht korrekt oder fehlend
				$this->db->query("UPDATE auth_user_md5 SET Nachname='$nachname' WHERE user_id='".$this->auth_user["user_id"]."'");
				$this->msg=$this->msg . "msg§" . _("Ihr Nachname wurde ge&auml;ndert!") . "§";
			}
		}
		

		if (!StudipAuthAbstract::CheckField("auth_user_md5.username", $this->auth_user['auth_plugin']) && $this->auth_user["username"] != $new_username) {
			if ($ALLOW_CHANGE_USERNAME) {
				if (!$validator->ValidateUsername($new_username)) {
					$this->msg=$this->msg . "error§" . _("Der gewählte Username ist nicht lang genug!") . "§";
					return false;
				}
				$check_uname = StudipAuthAbstract::CheckUsername($new_username);
				if ($check_uname['found']) {
					$this->msg .= "error§" . _("Der Username wird bereits von einem anderen User verwendet. Bitte wählen sie einen anderen Usernamen!") . "§";
					return false;
				} else {
					//$this->msg .= "info§" . $check_uname['error'] ."§";
				}
				$this->db->query("UPDATE auth_user_md5 SET username='$new_username' WHERE user_id='".$this->auth_user["user_id"]."'");
				$this->msg=$this->msg . "msg§" . _("Ihr Username wurde ge&auml;ndert!") . "§";
				$this->logout_user = TRUE;
			}
		}


		if (!StudipAuthAbstract::CheckField("auth_user_md5.Email", $this->auth_user['auth_plugin']) && $this->auth_user["Email"] != $email) {  //email wurde geändert!
			if ($ALLOW_CHANGE_EMAIL) {
				$smtp=new studip_smtp_class;       ## Einstellungen fuer das Verschicken der Mails
				$REMOTE_ADDR=$_SERVER["REMOTE_ADDR"];
				$Zeit=date("H:i:s, d.m.Y",time());
				
				if (!$validator->ValidateEmailAddress($email)) {
					$this->msg=$this->msg . "error§" . _("Die E-Mail-Adresse fehlt oder ist falsch geschrieben!") . "§";
					return false;        // E-Mail syntaktisch nicht korrekt oder fehlend
				}
				
				if (!$validator->ValidateEmailHost($email)) {     // Mailserver nicht erreichbar, ablehnen
					$this->msg=$this->msg . "error§" . _("Der Mailserver ist nicht erreichbar. Bitte &uuml;berpr&uuml;fen Sie, ob Sie E-Mails mit der angegebenen Adresse verschicken k&ouml;nnen!") . "§";
					return false;
				} else {       // Server ereichbar
					if (!$validator->ValidateEmailBox($email)) {    // aber user unbekannt. Mail an abuse!
						$from = $smtp->env_from;
						$to = $smtp->abuse;
						$smtp->SendMessage(
						$from, array($to),
						array("From: $from", "To: $to", "Subject: edit_about"),
						"Emailbox unbekannt\n\nUser: ".$this->auth_user["username"]."\nEmail: $email\n\nIP: $REMOTE_ADDR\nZeit: $Zeit\n");
						$this->msg=$this->msg . "error§" . _("Die angegebene E-Mail-Adresse ist nicht erreichbar. Bitte &uuml;berpr&uuml;fen Sie Ihre Angaben!") . "§";
						return false;
					}
				}
				
				$this->db->query("SELECT Email,Vorname,Nachname FROM auth_user_md5 WHERE Email='$email'") ;
				if ($this->db->next_record()) {
					$this->msg=$this->msg . "error§" . sprintf(_("Die angegebene E-Mail-Adresse wird bereits von einem anderen User (%s %s) verwendet. Bitte geben Sie eine andere E-Mail-Adresse an."), $this->db->f("Vorname"), $this->db->f("Nachname")) . "§";
					return false;
				}
				
				if (!StudipAuthAbstract::CheckField("auth_user_md5.password", $this->auth_user['auth_plugin'])){
						//email ist ok, user bekommt neues Passwort an diese Addresse, falls Passwort in Stud.IP DB
						$newpass=$this->generate_password(6);
						$hashpass=md5($newpass);
						// Mail abschicken...
						$to=$email;
						$url = $smtp->url;
					
						// include language-specific subject and mailbody
						include_once("$ABSOLUTE_PATH_STUDIP"."locale/$_language_path/LC_MAILS/change_self_mail.inc.php");
					
						$smtp->SendMessage(
						$smtp->env_from, array($to),
						array("From: $smtp->from", "Reply-To: $smtp->abuse", "To: $to", "Subject: $subject"),
						$mailbody);
						$this->logout_user = TRUE;
						$this->msg = $this->msg . "msg§" . _("Ihre E-Mail-Adresse wurde ge&auml;ndert!") . "§info§" . _("ACHTUNG!<br>Aus Sicherheitsgr&uuml;nden wurde auch ihr Passwort ge&auml;ndert. Es wurde an die neue E-Mail-Adresse geschickt!") . "§";
						$this->db->query("UPDATE auth_user_md5 SET Email='$email', password='$hashpass' WHERE user_id='".$this->auth_user["user_id"]."'");
					} else {
						$this->msg = $this->msg . "msg§" . _("Ihre E-Mail-Adresse wurde ge&auml;ndert!") . "§";
						$this->db->query("UPDATE auth_user_md5 SET Email='$email' WHERE user_id='".$this->auth_user["user_id"]."'");
					}
			}
		}
	}
	return;
}


function select_studiengang() {  //Hilfsfunktion, erzeugt eine Auswahlbox mit noch auswählbaren Studiengängen

	echo "<select name=\"new_studiengang\" width=30><option selected></option>";
	$this->db->query("SELECT a.studiengang_id,a.name FROM studiengaenge AS a LEFT JOIN user_studiengang AS b ON (b.user_id='".$this->auth_user["user_id"]."' AND a.studiengang_id=b.studiengang_id) WHERE b.studiengang_id IS NULL ORDER BY a.name");

	while ($this->db->next_record()) {
		echo "<option value=\"".$this->db->f("studiengang_id")."\">".htmlReady(my_substr($this->db->f("name"),0,50))."</option>";
	}
	echo "</select>";

	return;
}


function select_inst() {  //Hilfsfunktion, erzeugt eine Auswahlbox mit noch auswählbaren Instituten

	echo "<select name=\"new_inst\" width=30><option selected></option>";
	$this->db->query("SELECT a.Institut_id,a.Name FROM Institute AS a LEFT JOIN user_inst AS b ON (b.user_id='".$this->auth_user["user_id"]."' AND a.Institut_id=b.Institut_id) WHERE b.Institut_id IS NULL ORDER BY a.Name");

	while ($this->db->next_record()) {
		echo "<option value=\"".$this->db->f("Institut_id")."\">".htmlReady(my_substr($this->db->f("Name"),0,50))."</option>";
	}
	echo "</select>";

	return;
}


function generate_password($length) {      //Hilfsfunktion, erzeugt neues Passwort

	mt_srand((double)microtime()*1000000);
	for ($i=1;$i<=$length;$i++) {
		$temp = mt_rand() % 36;
		if ($temp < 10)
			$temp += 48;   // 0 = chr(48), 9 = chr(57)
		else
			$temp += 87;   // a = chr(97), z = chr(122)
		$pass .= chr($temp);
	}
	return $pass;
}



//Displays Errosmessages (kritischer Abbruch, Symbol "X")

function my_error($msg) {
?>
 <tr>
	<td class="blank" colspan=2>
	 <table border=0 align="left" cellspacing=0 cellpadding=2>
	<tr>
	 <td class="blank" align="center" width=50><img src="pictures/x.gif"></td>
	 <td class="blank" align="left" width="*"><font color=#FF2020><?php print $msg ?></font></td>
	</tr>
	 </table>
	</td>
 </tr>
 <tr>
	<td class="blank" colspan=2>&nbsp;</td>
 </tr>
<?
}


//Displays  Successmessages (Information &uuml;ber erfolgreiche Aktion, Symbol Haken)

function my_msg($msg) {
?>
 <tr>
	<td class="blank" colspan=2>
	 <table border=0 align="left" cellspacing=0 cellpadding=2>
	<tr>
	 <td class="blank" align="center" width=50><img src="pictures/ok.gif"></td>
	 <td class="blank" align="left" width="*"><font color=#008000><?php print $msg ?></font></td>
	</tr>
	 </table>
	</td>
 </tr>
 <tr>
	<td class="blank" colspan=2>&nbsp;</td>
 </tr>
<?
}

//Displays  Informationmessages  (Hinweisnachrichten, Symbol Ausrufungszeichen)

function my_info($msg) {
?>
 <tr>
	<td class="blank" colspan=2>
	 <table border=0 align="left" cellspacing=0 cellpadding=2>
	<tr>
	 <td class="blank" align="center" width=50><img src="pictures/ausruf.gif"></td>
	 <td class="blank" align="left" width="*"><font color=#008000><?php print $msg ?></font></td>
	</tr>
	 </table>
	</td>
 </tr>
 <tr>
	<td class="blank" colspan=2>&nbsp;</td>
 </tr>
<?
}

function parse_msg($long_msg,$separator="§") {

	$msg = explode ($separator,$long_msg);
	for ($i=0; $i < count($msg); $i=$i+2) {
		switch ($msg[$i]) {
			case "error" : $this->my_error($msg[$i+1]); break;
			case "info" : $this->my_info($msg[$i+1]); break;
			case "msg" : $this->my_msg($msg[$i+1]); break;
		}
	}
	return;
}

function move ($inst_id, $direction) {
	if ($this->check == 'user' || $this->check == 'admin') {
		$db = new DB_Seminar();
		$query = "SELECT * FROM user_inst WHERE user_id = '{$this->auth_user['user_id']}' ";
		$query .= "AND inst_perms != 'user' ORDER BY priority ASC";
		$db->query($query);
		$i = 1;
		while ($db->next_record()) {
			$to_order[$i] = $db->f('Institut_id');
			if ($to_order[$i] == $inst_id)
				$pos = $i;
			$i++;
		}
		if ($direction == 'up') {
			$a = $to_order[$pos - 1];
			$to_order[$pos - 1] = $to_order[$pos];
			$to_order[$pos] = $a;
		}
		else {
			$a = $to_order[$pos + 1];
			$to_order[$pos + 1] = $to_order[$pos];
			$to_order[$pos] = $a;
		}
		$i--;
		for (;$i > 0; $i--) {
			$query = "UPDATE user_inst SET priority = $i WHERE user_id = '{$this->auth_user['user_id']}' ";
			$query .= "AND Institut_id = '{$to_order[$i]}'";
			$db->query($query);
		}
	}
}
			
		
} // ende Klassendefinition





// hier gehts los
if (!$username) $username = $auth->auth["uname"];

$my_about = new about($username,$msg);
$cssSw = new cssClassSwitcher;
$DataFields = new DataFields($my_about->auth_user["user_id"]);
$ucfg = new UserConfig;

if ($logout)  // wir wurden gerade ausgeloggt...
	{
	
	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr><td class="topic" colspan=2><b>&nbsp;Daten ge&auml;ndert!</b></th></tr>
	<?

	$my_about->parse_msg($my_about->msg);
	$temp_string = "<br><font color=\"black\">"
		. sprintf(_("Um eine korrekte Authentifizierung mit ihren neuen Daten sicherzustellen, wurden sie automatisch ausgeloggt.<br>Wenn sie ihre E-Mail-Adresse ge&auml;ndert haben, m&uuml;ssen sie das Ihnen an diese Adresse zugesandte Passwort verwenden!<br><br>Ihr aktueller Username ist: <b>%s</b><br>"), $username)
		. "---> <a href=\"index.php?again=yes\">" . _("Login") . "</a> <---</font>";
	$my_about->my_info($temp_string);


	echo "</table></html>";
	page_close();
	die;
	}

//No Permission to change userdata
if (!$my_about->check)
 {
	// -- here you have to put initialisations for the current page
	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	parse_window ("error§" . sprintf("Zugriff verweigert.<br /><font size=-1 color=black>Wahrscheinlich ist Ihre Session abgelaufen. Wenn sie sich länger als %s Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen.<br /> <br /> Eine andere Ursache kann der Versuch des Zugriffs auf Userdaten, die Sie nicht bearbeiten d&uuml;rfen, sein. Nutzen Sie den untenstehenden Link, um zurück auf die Startseite zu gelangen.</font>", $AUTH_LIFETIME), "§",
	_("Zugriff auf Userdaten verweigert"), 
	_("<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;"));
	
	?>
	</body>
	</html>  
	<?
	page_close();
	die;
	}
	
if(check_ticket($ticket)){
	//ein Bild wurde hochgeladen
	if ($cmd == "copy")
	 {
		$my_about->imaging($imgfile,$imgfile_size,$imgfile_name);
		}
	
	//Veränderungen an Studiengängen
	if ($cmd == "studiengang_edit" && ($ALLOW_SELFASSIGN_STUDYCOURSE || $perm->have_perm("admin")))
	 {
		$my_about->studiengang_edit($studiengang_delete,$new_studiengang);
		}
	
	//Veränderungen an Instituten für Studies
	if ($cmd == "inst_edit" && ($ALLOW_SELFASSIGN_STUDYCOURSE || $perm->have_perm("admin")))
	 {
		$my_about->inst_edit($inst_delete,$new_inst);
		}
	
	//Veränderungen an Raum, Sprechzeit, etc
	if ($cmd == "special_edit")
	 {
		$my_about->special_edit($raum, $sprech, $tel, $fax, $name, $default_inst, $visible);
		}
	
	// change order of institutes
	if ($cmd == 'move') {
		$my_about->move($move_inst, $direction);
	}
	
	//Veränderungen der pers. Daten
	if ($cmd == "edit_pers") {
		//email und passwort können nicht sinnvoll gleichzeitig geändert werden, da bei Änderung der email automatisch das passwort neu gesetzt wird
		if (($email && $my_about->auth_user["Email"] != $email)
			&& (($response && $response != md5("*****")) || ($password && $password != "*****"))) {
			$my_about->msg = $my_about->msg . "error§" . _("Bitte ändern Sie erst ihre E-Mail-Adresse und dann ihr Passwort!") . "§";
			
		} else {
		$my_about->edit_pers($password,$check_pass,$response,$new_username,$vorname,$nachname,$email,$telefon,$anschrift,$home,$hobby,$geschlecht,$title_front,$title_front_chooser,$title_rear,$title_rear_chooser,$view);
			if (($my_about->auth_user["username"] != $new_username) && $my_about->logout_user == TRUE) $my_about->get_auth_user($new_username);   //username wurde geändert!
			else $my_about->get_auth_user($username);
			$username = $my_about->auth_user["username"];
		}
	}
	
	if ($cmd=="edit_leben")  {
		$my_about->edit_leben($lebenslauf,$schwerp,$publi);
		$DataFields->storeContentFromForm('pers');
	}
	
	// general settings from mystudip: language, jshover, accesskey
	if ($cmd=="change_general") {
		$my_about->db->query("UPDATE user_info SET preferred_language = '$forced_language' WHERE user_id='" . $my_about->auth_user["user_id"] ."'");
		$_language = $forced_language;
		$forum["jshover"]=$jshover; 
		$my_studip_settings["startpage_redirect"] = $personal_startpage;
		$ucfg->setValue($_REQUEST['accesskey_enable'], $user->id, "ACCESSKEY_ENABLE");
	}
	
	if ($my_about->logout_user)
	 {
		$sess->delete();  // User logout vorbereiten
		$auth->logout();
		$timeout=(time()-(15 * 60));
		$user->set_last_action($timeout);
		$msg = rawurlencode($my_about->msg);
		header("Location: $PHP_SELF?username=$username&msg=$msg&logout=1&view=$view"); //Seite neu aufrufen, damit user nobody wird...
		page_close();
		die;
		}
	
	if ($cmd) {
		if (($my_about->check != "user") && ($my_about->priv_msg != "")) {
			$m_id=md5(uniqid("smswahn"));
			setTempLanguage($my_about->auth_user["user_id"]);
			$priv_msg = _("Ihre persönliche Seite wurde von einer Administratorin oder einem Administrator verändert.\n Folgende Veränderungen wurden vorgenommen:\n \n").$my_about->priv_msg;
			restoreLanguage();
			$my_about->insert_message($priv_msg, $my_about->auth_user["username"], "____%system%____", FALSE, FALSE, "1", FALSE, _("Systemnachricht:")." "._("persönliche Homepage verändert"));
		}
		$msg = rawurlencode($my_about->msg);
		header("Location: $PHP_SELF?username=$username&msg=$msg&view=$view");  //Seite neu aufrufen, um Parameter loszuwerden
		page_close();
		die;
	}
	
} else {
	unset($cmd);
}

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head

if ($auth->auth["jscript"]) { // nur wenn JS aktiv
?>
<script type="text/javascript" language="javascript" src="md5.js"></script>

<script type="text/javascript" language="javascript">
<!--

function oeffne()
{
	fenster=window.open('get_auto.php','','scrollbars=no,width=400,height=150','resizable=no');
	fenster.focus();
}
function checkusername(){
 var re_username = /^([a-zA-Z0-9_@-]*)$/;
 var checked = true;
 if (document.pers.new_username.value.length<4) {
	alert("<?=_("Der Benutzername ist zu kurz - er sollte mindestens 4 Zeichen lang sein.")?>");
	 document.pers.new_username.focus();
	checked = false;
	}
 if (re_username.test(document.pers.new_username.value)==false) {
	alert("<?=_("Der Benutzername enthält unzulässige Zeichen - er darf keine Sonderzeichen oder Leerzeichen enthalten.")?>");
	 document.pers.new_username.focus();
	checked = false;
	}
 return checked;
}

function checkpassword(){
 var checked = true;
 if (document.pers.password.value.length<4) {
	alert("<?=_("Das Passwort ist zu kurz - es sollte mindestens 4 Zeichen lang sein.")?>");
	 document.pers.password.focus();
	checked = false;
	}
 if (document.pers.password.value != document.pers.check_pass.value)
	{
	alert("<?=_("Bei der Wiederholung des Paßwortes ist ein Fehler aufgetreten! Bitte geben sie das exakte Paßwort ein!")?>");
	document.pers.check_pass.focus();
	checked = false;
	}

 return checked;
}

function checkvorname(){
 var re_vorname = /^([a-zA-ZÄÖÜ][^0-9"´'`\/\\\(\)\[\]]+)$/;
 var checked = true;
 if (re_vorname.test(document.pers.vorname.value)==false) {
	alert("<?=_("Bitte geben Sie Ihren tatsächlichen Vornamen an.")?>");
	 document.pers.vorname.focus();
	checked = false;
	}
 return checked;
}

function checknachname(){
 var re_nachname = /^([a-zA-ZÄÖÜ][^0-9"´'`\/\\\(\)\[\]]+)$/;
 var checked = true;
 if (re_nachname.test(document.pers.nachname.value)==false) {
	alert("<?=_("Bitte geben Sie Ihren tatsächlichen Nachnamen an.")?>");
	 document.pers.nachname.focus();
	checked = false;
	}
 return checked;
}

function checkemail(){
 var re_email = /^([_a-zA-Z0-9-]+)(\.[_a-zA-Z0-9-]+)*@([_a-zA-Z0-9-]+\.)+([a-zA-Z]{2,4})$/;
 var email = document.pers.email.value;
 var checked = true;
 if ((re_email.test(email))==false || email.length==0) {
	alert("<?=_("Die E-Mail-Adresse ist nicht korrekt!")?>");
	 document.pers.email.focus();
	checked = false;
	}
 return checked;
}

function checkdata(){
 // kompletter Check aller Felder vor dem Abschicken
 var checked = true;
 if (document.pers.new_username && !checkusername())
	checked = false;
 if (document.pers.password && !checkpassword())
	checked = false;
 if (document.pers.vorname && !checkvorname())
	checked = false;
 if (document.pers.nachname && !checknachname())
	checked = false;
 if (document.pers.email && !checkemail())
	checked = false;
 if (checked) {
	 document.pers.method = "post";
	 document.pers.action = "<?php print ("$PHP_SELF?cmd=edit_pers&username=$username&view=$view&ticket=".get_ticket()) ?>";
	 document.pers.response.value = MD5(document.pers.password.value);
	 document.pers.password.value = "*****";
	 document.pers.check_pass.value = "*****";
 }
 return checked;
}
// -->
</SCRIPT>

<?

} // Ende nur wenn JS aktiv

include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head


if (!$cmd)
 {
 // darfst du ändern?? evtl erst ab autor ?
	$perm->check("user");
	$my_about->get_user_details();
	$username = $my_about->auth_user["username"];
	//maximale spaltenzahl berechnen
	 if ($auth->auth["jscript"]) $max_col = round($auth->auth["xres"] / 10 );
	 else $max_col =  64 ; //default für 640x480

// Reitersystem
include ("$ABSOLUTE_PATH_STUDIP/links_about.inc.php");  

//Kopfzeile bei allen eigenen Modulen ausgeben
if ($view!="Forum" AND $view!="calendar" AND $view!="Stundenplan" AND $view!="Messaging" AND $view!= "allgemein") {
	echo "<table class=\"blank\" cellspacing=0 cellpadding=0 border=0 width=\"100%\">";
		
	if ($username!=$auth->auth["uname"]) 
		echo "<tr><td class=\"topicwrite\" colspan=2><img src='pictures/einst.gif' border=0 align=texttop><b>&nbsp;";
	else 
		echo "<tr><td class=\"topic\" colspan=2><img src='pictures/einst.gif' border=0 align=texttop><b>&nbsp;";
	switch ($view) {
		case ("Bild") :
			echo _("Hochladen eines pers&ouml;nlichen Bildes");
		break;
		case ("Daten") :
			echo _("Benutzerdaten bearbeiten");
		break;
		case ("Karriere") :
			if ($perm->have_perm ("tutor"))
				echo _("Studienkarriere und Einrichtungen bearbeiten");
			else
				echo _("Studienkarriere bearbeiten");
		break;
		case ("Lebenslauf") :
			if ($auth->auth['perm'] == "dozent")
				echo _("Lebenslauf, Arbeitsschwerpunkte und Publikationen bearbeiten");
			else
				echo _("Lebenslauf bearbeiten");
		break;
		case ("Sonstiges") :
			echo _("Eigene Kategorien bearbeiten");
		break;
		case ("Login") :
			echo _("Auto-Login einrichten");
		break;
	}
	
	if ($username!=$auth->auth["uname"]) { 
		echo "&nbsp; &nbsp; <font size=-1>";
		printf(_("Daten von: %s %s (%s), Status: %s"), $my_about->auth_user["Vorname"], $my_about->auth_user["Nachname"], $username, $my_about->auth_user["perms"]);
		echo "</font>";
	}

	echo "</b></td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n</table>\n<table class=\"blank\" cellspacing=0 cellpadding=2 border=0 width=\"100%\">";
}

// evtl Fehlermeldung ausgeben
if ($my_about->msg) {
	$my_about->parse_msg($my_about->msg);
}

if ($view=="Bild") {
	// hier wird das Bild ausgegeben
	$cssSw->switchClass();
	echo "<tr><td colspan=2 class=\"blank\"><blockquote><br />" . _("Auf dieser Seite k&ouml;nnen Sie ein pers&ouml;nliches Bild f&uuml;r Ihre Homepage hochladen.") . "<br><br><br></td></tr>";
	echo"<tr><td width=\"30%\" class=\"".$cssSw->getClass()."\" align=\"center\">";
	echo "<font size=-1><b>" . _("Aktuell angezeigtes Bild:") . "<br /><br /></b></font>";
	
	if (!file_exists("./user/".$my_about->auth_user["user_id"].".jpg")) {
		echo "<img src=\"./user/nobody.jpg\" width=\"200\" height=\"250\" alt=\"" . _("Kein pers&ouml;nliches Bild vorhanden") . "\" ><br />&nbsp; ";
	} else {
		echo "<img border=\"1\" src=\"./user/".$my_about->auth_user["user_id"].".jpg\" alt=\"". $my_about->auth_user["Vorname"]." ".$my_about->auth_user["Nachname"]."\"><br />&nbsp; ";
	}
			
	echo "</td><td class=\"".$cssSw->getClass()."\" width=\"70%\" align=\"left\" valign=\"top\"><blockquote>";
	echo "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?cmd=copy&username=$username&view=Bild&ticket=".get_ticket()."\" method=\"POST\">";
	echo "<br />" . _("Hochladen eines Bildes:") . "<br><br>" . _("1. Wählen sie mit <b>Durchsuchen</b> eine Bilddatei von ihrer Festplatte aus.") . "<br><br>";
	echo "&nbsp;&nbsp;<input name=\"imgfile\" type=\"file\" style=\"width: 80%\" cols=".round($max_col*0.7*0.8)."><br><br>";
	echo _("2. Klicken sie auf <b>absenden</b>, um das Bild hochzuladen.") . "<br><br>";
	echo "&nbsp;&nbsp;<input type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=\"" . _("absenden") . "\"><br><br>";
	echo '<b>', _("ACHTUNG!"), '</b><br>';
	printf (_("Die Bilddatei darf max. %s KB groß sein, es sind nur Dateien mit den Endungen %s, %s oder %s erlaubt!"), $my_about->max_file_size, '<b>.jpg</b>', '<b>.png</b>', '<b>.gif</b>');
	echo "</blockquote></td></tr>";
}

if ($view=="Daten") {
	$cssSw->switchClass();
	//persönliche Daten...
	echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>" . _("Hier k&ouml;nnen sie Ihre Benutzerdaten ver&auml;ndern.");
	echo "<br><font size=-1>" . sprintf(_("Alle mit einem Sternchen %s markierten Felder m&uuml;ssen ausgef&uuml;llt werden."), "</font><font color=\"red\" size=+1><b>*</b></font><font size=-1>") . "</font><br><br>";
	if ($my_about->auth_user['auth_plugin'] != "standard"){
		echo "<font size=\"-1\">" . sprintf(_("Ihre Authentifizierung (%s) benutzt nicht die Stud.IP Datenbank, daher k&ouml;nnen sie einige Felder nicht ver&auml;ndern!"),$my_about->auth_user['auth_plugin']) . "</font>";
	}
	echo "<br><br></td></tr>\n<tr><td class=blank><table align=\"center\" width=99% class=blank border=0 cellpadding=2 cellspacing=0>";
	//Keine JavaScript überprüfung bei adminzugriff
	if ($my_about->check=="user" && $auth->auth["jscript"] ) {
		echo "<tr><form action=\"$PHP_SELF?cmd=edit_pers&username=$username&view=$view&ticket=".get_ticket()."\" method=\"POST\" name=\"pers\" onsubmit=\"return checkdata()\">";
	} else {
		echo "<tr><form action=\"$PHP_SELF?cmd=edit_pers&username=$username&view=$view&ticket=".get_ticket()."\" method=\"POST\" name=\"pers\">";
	}
	 
	if ($my_about->check=="user") {
		echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"25%\" align=\"left\"><blockquote><b>" . _("Username:") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 width=\"75%\" align=\"left\">&nbsp;";
		if (($ALLOW_CHANGE_USERNAME && !StudipAuthAbstract::CheckField("auth_user_md5.username",$my_about->auth_user['auth_plugin'])) ) {
			echo "&nbsp;<input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"new_username\" value=\"".$my_about->auth_user["username"]."\">&nbsp; <font color=\"red\" size=+2>*</font>";
		} else {
			echo "&nbsp;<font size=\"-1\">".$my_about->auth_user["username"]."</font>";
		}
	echo "</td></tr>\n";
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Passwort:") . " </td>";
	if (StudipAuthAbstract::CheckField("auth_user_md5.password", $my_about->auth_user['auth_plugin'])) {
	echo "<td class=\"".$cssSw->getClass()."\" colspan=\"2\" align=\"left\">&nbsp; <font size=\"-1\">*****</font>";
	} else {
		echo "<td class=\"".$cssSw->getClass()."\" nowrap width=\"20%\" align=\"left\"><font size=-1>&nbsp; " . _("neues Passwort:") . "</font><br />&nbsp; <input type=\"password\" size=\"".round($max_col*0.25)."\" name=\"password\" value=\"*****\"><input type=\"HIDDEN\" name=\"response\" value=\"\">&nbsp; <font color=\"red\" size=+2>*</font>&nbsp; </td><td class=\"".$cssSw->getClass()."\" width=\"55%\" nowrap align=\"left\"><font size=-1>&nbsp; " . _("Passwort-Wiederholung:") . "</font><br />&nbsp; <input type=\"password\" size=\"".round($max_col*0.25)."\" name=\"check_pass\" value=\"*****\">&nbsp; <font color=\"red\" size=+2>*</font>";
	}
	echo "</td></tr>\n";

	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Name:") . " </td><td class=\"".$cssSw->getClass()."\" nowrap align=\"left\"><font size=-1>&nbsp; " . _("Vorname:") . "</font><br />";
	if ((!$ALLOW_CHANGE_NAME) || StudipAuthAbstract::CheckField("auth_user_md5.Vorname", $my_about->auth_user['auth_plugin'])) {
        	echo "&nbsp; <font size=\"-1\">" . $my_about->auth_user["Vorname"]."</font>";
	} else {
        	echo "&nbsp; <input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"vorname\" value=\"".$my_about->auth_user["Vorname"]."\">&nbsp; <font color=\"red\" size=+2>*</font>";
	}
	echo "</td><td class=\"".$cssSw->getClass()."\" nowrap align=\"left\"><font size=-1>&nbsp; " . _("Nachname:") . "</font><br />";
	if ((!$ALLOW_CHANGE_NAME) || StudipAuthAbstract::CheckField("auth_user_md5.Nachname", $my_about->auth_user['auth_plugin'])) {
		echo "&nbsp; <font size=\"-1\">" . $my_about->auth_user["Nachname"]."</font>";
	} else {
		echo "&nbsp; <input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"nachname\" value=\"".$my_about->auth_user["Nachname"]."\">&nbsp; <font color=\"red\" size=+2>*</font>";
	}
                            
	echo "</td></tr>\n";
      
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("E-Mail:") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 align=\"left\">&nbsp;";
	if (($ALLOW_CHANGE_EMAIL && !(StudipAuthAbstract::CheckField("auth_user_md5.Email", $my_about->auth_user['auth_plugin'])))) {
		echo " <input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"email\" value=\"".$my_about->auth_user["Email"]."\">&nbsp; <font color=\"red\" size=+2>*</font>";
	} else {
		echo "&nbsp; <font size=\"-1\">".$my_about->auth_user["Email"]."</font>";
	}
	echo "</td></tr>\n";
	} else {
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"25%\" align=\"left\"><blockquote><b>" . _("Username:") . " </td><td class=\"".$cssSw->getClass()."\" width=\"25%\" align=\"left\">&nbsp; ".$my_about->auth_user["username"]."</td><td width=\"50%\" rowspan=4 align=\"center\"><b><font color=\"red\">" . _("Adminzugriff hier nicht möglich!") . "</font></b></td></tr>\n";
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Passwort:") . " </td><td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp; *****</td></tr>\n";
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Name:") . " </td><td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp; ".$my_about->auth_user["Vorname"]." ".$my_about->auth_user["Nachname"]."</td></tr>\n";
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("E-Mail:") . " </td><td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp; ".$my_about->auth_user["Email"]."</td></tr>\n";
	}
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Titel:") . " </td>";
	if (StudipAuthAbstract::CheckField("user_info.title_front", $my_about->auth_user['auth_plugin'])) {
		echo "<td class=\"".$cssSw->getClass()."\" colspan=\"2\" align=\"left\">&nbsp;" .  $my_about->user_info['title_front'] . "</td></tr>";
	} else {
		echo "<td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp;";
		echo "\n<select name=\"title_front_chooser\" onChange=\"document.pers.title_front.value=document.pers.title_front_chooser.options[document.pers.title_front_chooser.selectedIndex].text;\">";
		for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i) {
			echo "\n<option";
			if ($TITLE_FRONT_TEMPLATE[$i] == $my_about->user_info['title_front']) {
				echo " selected ";
			}
			echo ">$TITLE_FRONT_TEMPLATE[$i]</option>";
		}	
		echo "</select></td><td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp;&nbsp;";
		echo "<input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"title_front\" value=\"".$my_about->user_info['title_front']."\"></td></tr>\n";
	}
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\" nowrap><blockquote><b>" . _("Titel nachgest.:") . " </td>";
	if (StudipAuthAbstract::CheckField("user_info.title_rear", $my_about->auth_user['auth_plugin'])) {
		echo "<td class=\"".$cssSw->getClass()."\" colspan=\"2\" align=\"left\">&nbsp;" .  $my_about->user_info['title_rear'] . "</td></tr>";
	} else {
		echo "<td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp;";
		echo "\n<select name=\"title_rear_chooser\" onChange=\"document.pers.title_rear.value=document.pers.title_rear_chooser.options[document.pers.title_rear_chooser.selectedIndex].text;\">";
		for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i) {
			echo "\n<option";
			if($TITLE_REAR_TEMPLATE[$i] == $my_about->user_info['title_rear']) {
				echo " selected ";
			}
			echo ">$TITLE_REAR_TEMPLATE[$i]</option>";
		}	
		echo "</select></td><td class=\"".$cssSw->getClass()."\" align=\"left\">&nbsp;&nbsp;";
		echo "<input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"title_rear\" value=\"".$my_about->user_info['title_rear']."\"></td></tr>\n";
	}
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Geschlecht:") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 nowrap align=\"left\"><font size=-1>";
	if (StudipAuthAbstract::CheckField("user_info.geschlecht", $my_about->auth_user['auth_plugin'])) {
		echo "&nbsp;" . (!$my_about->user_info["geschlecht"] ? _("m&auml;nnlich") : _("weiblich"));
	} else {
		echo "&nbsp; " . _("m&auml;nnlich") . "&nbsp; <input type=\"RADIO\" name=\"geschlecht\" value=\"0\" ";
		if (!$my_about->user_info["geschlecht"]) {
			echo "checked";
		}
		echo " />&nbsp; " . _("weiblich") . "&nbsp; <input type=\"RADIO\" name=\"geschlecht\" value=\"1\" ";
		if ($my_about->user_info["geschlecht"]) {
			echo "checked";
		}
		echo " />";
	}
	echo "</font></td></tr>";
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"100%\" colspan=3 align=\"center\"><b>" . _("Freiwillige Angaben") . "</b></td></tr>\n";
	 $cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"25%\" align=\"left\"><blockquote><b>" . _("Telefon (privat):") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 width=\"75%\" align=\"left\">";
	if (StudipAuthAbstract::CheckField("user_info.privatnr", $my_about->auth_user['auth_plugin'])) {
		echo "&nbsp;" . htmlReady($my_about->user_info["privatnr"]);
	} else {
		echo "&nbsp; <input type=\"text\" size=\"".round($max_col*0.25)."\" name=\"telefon\" value=\"".htmlReady($my_about->user_info["privatnr"])."\">";
	}
	echo "</td></tr>\n";
	 $cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Adresse (privat):") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 align=\"left\">";
	if (StudipAuthAbstract::CheckField("user_info.privadr", $my_about->auth_user['auth_plugin'])) {
		echo "&nbsp;" . htmlReady($my_about->user_info["privadr"]);
	} else {
		echo "&nbsp; <input type=\"text\" size=\"".round($max_col*0.5)."\" name=\"anschrift\" value=\"".htmlReady($my_about->user_info["privadr"])."\">";
	}
	echo "</td></tr>\n";
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Homepage:") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 align=\"left\">";
	if (StudipAuthAbstract::CheckField("user_info.Home", $my_about->auth_user['auth_plugin'])) {
		echo "&nbsp;" . htmlReady($my_about->user_info["Home"]);
	} else {
		echo "&nbsp; <input type=\"text\" size=\"".round($max_col*0.5)."\" name=\"home\" value=\"".htmlReady($my_about->user_info["Home"])."\">";
	
	}
	echo "</td></tr>\n";
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" align=\"left\"><blockquote><b>" . _("Hobbies:") . " </td><td class=\"".$cssSw->getClass()."\" colspan=2 align=\"left\">";
	if (StudipAuthAbstract::CheckField("user_info.hobby", $my_about->auth_user['auth_plugin'])) {
		echo "&nbsp;" . htmlReady($my_about->user_info["hobby"]);
	} else {
		echo "&nbsp; <textarea  name=\"hobby\"  style=\"width: 50%\" cols=".round($max_col*0.5)." rows=4 maxlength=250 wrap=virtual >".htmlReady($my_about->user_info["hobby"])."</textarea>";
	}
	echo "</td></tr>\n";
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\">&nbsp; </td><td class=\"".$cssSw->getClass()."\" colspan=2>&nbsp; <input type=\"IMAGE\" " . makeButton("uebernehmen", "src") . " border=0 value=\"" . _("Änderungen übernehmen") . "\"></td></tr>\n</table>\n</td>";
}

	
if ($view=="Karriere") {
	
	if ($perm->have_perm("root") AND $username==$auth->auth["uname"]) {
		echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br><br>" . _("Als Root haben Sie bereits genug Karriere gemacht ;-)") . "<br><br>";
	} else { 
		if (($perm->have_perm("tutor")) && (!$perm->have_perm("dozent"))) {
			echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>" . _("Hier können Sie Angaben &uuml;ber ihre Studienkarriere und Daten an Einrichtungen, an denen Sie arbeiten, machen.");
		} elseif ($perm->have_perm("dozent")) {
			echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>" . _("Hier können Sie Angaben &uuml;ber Daten an Einrichtungen, in den Sie arbeiten, machen.");	
		} else {
				if (!$ALLOW_SELFASSIGN_STUDYCOURSE) {
					$message = "info§" . sprintf(formatReady(_("Sie haben keine Berechtigung diese Daten selbst zu ändern!")));
				parse_msg($message, "§", "blank",3);
			}		
			echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>" . _("Hier können Sie Angaben &uuml;ber ihre Studienkarriere machen.");
		}

		echo "<br />&nbsp; </td></tr>";

		//Verändern von Raum, Sprechzeiten etc
		if ($my_about->special_user) {
	 		reset ($my_about->user_inst);
	 		echo "<a name=\"inst_data\"></a>";   
	 		echo "<tr><td class=\"blank\">";
	 		echo "<b>&nbsp; " . _("Ich arbeite an folgenden Einrichtungen:") . "</b>";
	 		echo "<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" align=\"center\" width=\"99%\" border=\"0\">";
	 		echo "<form action=\"$PHP_SELF?cmd=special_edit&username=$username&view=$view&ticket=".get_ticket()."\" method=\"POST\">";
			
			$i = 1;
	 		while (list($inst_id, $details) = each($my_about->user_inst)) {
				$cssSw->resetClass();
				$cssSw->switchClass();    
				if ($details["inst_perms"] != "user") {
	 				echo "<tr><td class=\"blank\" colspan=\"3\" width=\"100%\">&nbsp; </td></tr>";
	 				echo "<tr><td class=\"" . $cssSw->getClass() . "\" align=\"left\">";
					echo "&nbsp; <b>" . htmlReady($details["Name"]) . "</b></td>";
					echo "<td class=\"" . $cssSw->getClass() . "\" width=\"30%\" align=\"left\" nowrap=\"nowrap\">&nbsp; ";
					echo _("Standard-Adresse:") . "&nbsp;<input type=\"radio\" name=\"default_inst\" ";
					echo "value=\"$inst_id\"";
					echo ($details['externdefault'] ? ' checked="checked"' : '') . ">&nbsp;";
					echo "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}pictures/info.gif\"";
					$info = _("Angaben, die im Adressbuch und auf den externen Seiten als Standard benutzt werden.");
					echo tooltip($info, TRUE, TRUE) . ">&nbsp; &nbsp;";
					echo _("Diese Einrichtung ausblenden:");
					echo "<input type=\"checkbox\" name=\"visible[$inst_id]\" value=\"1\" ";
					echo ($details['visible'] == '1' ? '' : ' checked="checked"') . ">&nbsp;";
					echo "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}pictures/info.gif\"";
					$info = _("Die Angaben zu dieser Einrichtung werden nicht auf Ihrer Homepage und in Adressbüchern ausgegeben.");
					echo tooltip($info, TRUE, TRUE) . ">&nbsp; &nbsp;</td>\n";
					echo "<td class=\"" . $cssSw->getClass() . "\" align=\"left\">";
					if ($i != 1) {
						echo "<a href=\"$PHP_SELF?view=Karriere&username=$username&cmd=move";
						echo "&direction=up&move_inst=$inst_id&ticket=".get_ticket()."\">";
						echo "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}pictures/move_up.gif\" ";
						echo "border=\"0\"" . tooltip(_("nach oben")) . "></a>";
					}
					if ($i != sizeof($my_about->user_inst)) {
						echo "<a href=\"$PHP_SELF?view=Karriere&username=$username&cmd=move";
						echo "&direction=down&move_inst=$inst_id&ticket=".get_ticket()."\">";
						echo "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}pictures/move_down.gif\" ";
						echo "border=\"0\"" . tooltip(_("nach unten")) . "></a>";
					}
					$i++;
					echo "</tr>\n";
					//statusgruppen
					if ($gruppen = GetStatusgruppen($inst_id, $my_about->auth_user["user_id"])) {
						$cssSw->switchClass();
						echo "<tr><td class=\"" . $cssSw->getClass() . "\" width=\"20%\" align=\"left\">";
						echo _("Funktion(en):") . " </td><td class=\"" . $cssSw->getClass() . "\" colspan=\"2\" ";
						echo "width=\"80%\" align=\"left\">&nbsp; " . htmlReady(join(", ", array_values($gruppen)));
						echo "</td></tr>\n";
					}
					echo "<input type=\"HIDDEN\" name=\"name[$inst_id]\" value=\"";
					echo htmlReady($details["Name"]) . "\">";
	 				$cssSw->switchClass();
	 				echo "<tr><td class=\"" . $cssSw->getClass() . "\" width=\"20%\" align=\"left\">";
					echo _("Raum:") . " </td><td class=\"" . $cssSw->getClass() . "\" colspan=\"2\" ";
					echo "width=\"80%\" align=\"left\">&nbsp; <input type=\"text\" style=\"width: 30%\" ";
					echo "size=\"" . round($max_col * 0.25 * 0.6) . "\" name=\"raum[$inst_id]\" ";
					echo "value=\"" . htmlReady($details["raum"]) . "\"></td></tr>";
	 				$cssSw->switchClass();
	 				echo "<td class=\"" . $cssSw->getClass() . "\" width=\"20%\" align=\"left\">";
					echo _("Sprechzeit:") . " </td><td class=\"" . $cssSw->getClass() . "\" colspan=\"2\" ";
					echo "width=\"80%\" align=\"left\">&nbsp; <input type=\"text\" style=\"width: 30%\" ";
					echo "size=\"" . round($max_col * 0.25 * 0.6) . "\" name=\"sprech[$inst_id]\" ";
					echo "value=\"" . htmlReady($details["sprechzeiten"]) . "\"></td></tr>";
	 				$cssSw->switchClass();
	 				echo "<td class=\"" . $cssSw->getClass() . "\" width=\"20%\" align=\"left\">";
					echo _("Telefon:") . " </td><td class=\"" . $cssSw->getClass() . "\" colspan=\"2\" ";
					echo "width=\"80%\" align=\"left\">&nbsp; <input type=\"text\" style=\"width: 30%\" ";
					echo "size=\"" . round($max_col * 0.25 * 0.6) . "\" name=\"tel[$inst_id]\" ";
					echo "value=\"" . htmlReady($details["Telefon"]) . "\"></td></tr>";
	 				$cssSw->switchClass();
	 				echo "<td class=\"" . $cssSw->getClass() . "\" width=\"20%\" align=\"left\">";
					echo _("Fax:") . " </td><td class=\"" . $cssSw->getClass() . "\" colspan=\"2\" ";
					echo "width=\"80%\" align=\"left\">&nbsp; <input type=\"text\" style=\"width: 30%\" ";
					echo "size=\"" . round($max_col * 0.25 * 0.6) . "\"   name=\"fax[$inst_id]\" ";
					echo "value=\"" . htmlReady($details["Fax"]) . "\"></td></tr>";
				}
			}
	 		$cssSw->switchClass();
	 		echo "<tr><td class=\"" . $cssSw->getClass() . "\">&nbsp; </td>";
			echo "<td class=\"" . $cssSw->getClass() . "\" colspan=\"2\">&nbsp; <input type=\"IMAGE\" ";
			echo makeButton("uebernehmen", "src") . " value=\"" . _("Änderungen übernehmen") . "\">";
			echo "</td></table><br />&nbsp; </form></td></tr>";
		}
	}

	//Studiengänge die ich belegt habe
	if (($my_about->auth_user["perms"]=="autor" || $my_about->auth_user["perms"]=="tutor")) { // nur für Autoren und Tutoren
		$cssSw->resetClass();
		$cssSw->switchClass();
		echo "<tr><td class=\"blank\">";
		echo "<b>&nbsp; " . _("Ich bin in folgenden Studieng&auml;ngen eingeschrieben:") . "</b>";
		echo "<table width= \"99%\" align=\"center\" border=0 cellpadding=2 cellspacing=0>\n";
		echo "<form action=\"$PHP_SELF?cmd=studiengang_edit&username=$username&view=$view&ticket=".get_ticket()."#studiengaenge\" method=\"POST\">";
		echo "<tr><td width=\"30%\" valign=\"top\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">";
		reset ($my_about->user_studiengang);
		$flag=FALSE;

		$i=0;
		while (list ($studiengang_id,$details) = each ($my_about->user_studiengang)) {
			if (!$i) {
				echo "<tr><td class=\"steelgraudunkel\" width=\"80%\">" . _("Studiengang") . "</td><td class=\"steelgraudunkel\" width=\"30%\">" . _("austragen") . "</ts></tr>";
			}
			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"80%\">".htmlReady($details["name"])."</td><td class=\"".$cssSw->getClass()."\" width=\"20%\" align=\"center\"><input type=\"CHECKBOX\" name=\"studiengang_delete[]\" value=\"$studiengang_id\"></td><tr>";
			$i++;
			$flag=TRUE;
		}

		if (!$flag) {
			echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=\"2\"><br /><font size=-1><b>" . _("Sie haben sich noch keinem Studiengang zugeordnet.") . "</b><br /><br />" . _("Tragen Sie bitte hier die Angaben aus Ihrem Studierendenausweis ein!") . "</font></td><tr>";
		}
		$cssSw->resetClass();
		$cssSw->switchClass();
		echo "</table></td><td class=\"".$cssSw->getClass()."\" width=\"70%\" align=\"left\" valign=\"top\"><blockquote><br />" . _("Wählen Sie die Studiengänge in Ihrem Studierendenausweis aus der folgenden Liste aus:") . "<br>";
		echo "<br><div align=\"center\">";
		echo "<a name=\"studiengaenge\"></a>";   
		$my_about->select_studiengang();
		echo "</div><br></b>" . _("Wenn Sie einen Studiengang wieder austragen möchten, markieren Sie die entsprechenden Felder in der linken Tabelle.") . "<br>";
		echo _("Mit einem Klick auf <b>&Uuml;bernehmen</b> werden die gewählten Änderungen durchgeführt.") . "<br /><br /> ";
		echo "<input type=\"IMAGE\" " . makeButton("uebernehmen", "src") . " value=\"" . _("Änderungen übernehmen") . "\"></blockquote></td></tr>";
		echo "</form>";
	}
	echo "</td></tr></table>";


	//Institute, an denen studiert wird
	if (($my_about->auth_user["perms"]=="autor" || $my_about->auth_user["perms"]=="tutor")) {
		$cssSw->resetClass();
		$cssSw->switchClass();
		echo "<tr><td class=\"blank\">";
		echo "<br><b>&nbsp; " . _("Ich studiere an folgenden Einrichtungen:") . "</b>";
		echo "<table width= \"99%\" align=\"center\" border=0 cellpadding=2 cellspacing=0>\n";
		echo "<form action=\"$PHP_SELF?cmd=inst_edit&username=$username&view=$view&ticket=".get_ticket()."#einrichtungen\" method=\"POST\">";
		echo "<tr><td width=\"30%\" valign=\"top\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"2\">";
		reset ($my_about->user_inst);
		$flag=FALSE;
		$i=0;
		while (list ($inst_id,$details) = each ($my_about->user_inst)) {
			if ($details["inst_perms"] == "user") {
	 			if (!$i) {
					echo "<tr><td class=\"steelgraudunkel\" width=\"80%\">" . _("Einrichtung") . "</td><td class=\"steelgraudunkel\" width=\"30%\">" . _("austragen") . "</ts></tr>";
				}
				$cssSw->switchClass();    
				echo "<tr><td class=\"".$cssSw->getClass()."\" width=\"80%\">".htmlReady($details["Name"])."</td><td class=\"".$cssSw->getClass()."\" width=\"20%\" align=\"center\"><input type=\"CHECKBOX\" name=\"inst_delete[]\" value=\"$inst_id\"></td><tr>";
	 			$i++;
	 			$flag=TRUE;
	 		}
		}
		if (!$flag) {
			echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=\"2\"><br /><font size=-1><b>" . _("Sie haben sich noch keinen Einrichtungen zugeordnet.") . "</b><br /><br />" . _("Wenn Sie auf ihrer Homepage die Einrichtungen, an denen Sie studieren, auflisten wollen, k&ouml;nnen Sie diese Einrichtungen hier entragen.") . "</font></td><tr>";
		}
		$cssSw->resetClass();
		$cssSw->switchClass();    
		echo "</table></td><td class=\"".$cssSw->getClass()."\" width=\"70%\" align=\"left\" valign=\"top\"><blockquote><br />" . _("Um sich als Student einer Einrichtung zuzuordnen, wählen Sie die entsprechende Einrichtung aus der folgenden Liste aus:") . "<br>";
		echo "<br><div align=\"center\">";
		echo "<a name=\"einrichtungen\"></a>";   
		$my_about->select_inst();
		echo "</div><br></b>" . _("Wenn sie aus Einrichtungen wieder ausgetragen werden möchten, markieren Sie die entsprechenden Felder in der linken Tabelle.") . "<br>";
		echo _("Mit einem Klick auf <b>&Uuml;bernehmen</b> werden die gewählten Änderungen durchgeführt.") . "<br /><br /> ";
		echo "<input type=\"IMAGE\" " . makeButton("uebernehmen", "src") . " value=\"" . _("Änderungen übernehmen") . "\"></blockquote></td></tr>";
		echo "</form>";
	}
	echo "</td></tr></table>";
}   

if ($view=="Lebenslauf") {
	$cssSw->switchClass();
	if ($my_about->auth_user["perms"] == "dozent") {
		echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>" . _("Hier k&ouml;nnen Sie Lebenslauf, Publikationen und Arbeitschwerpunkte bearbeiten.");
	} else {
		echo "<tr><td align=\"left\" valign=\"top\" class=\"blank\"><blockquote><br>" . _("Hier k&ouml;nnen Sie Ihren Lebenslauf bearbeiten.");
	}  
	echo "<br>&nbsp; </td></tr>\n<tr><td class=blank><table align=\"center\" width=\"99%\" align=\"center\" border=0 cellpadding=2 cellspacing=0>";
	echo "<form action=\"$PHP_SELF?cmd=edit_leben&username=$username&view=$view&ticket=".get_ticket()."\" method=\"POST\" name=\"pers\">";
	//add the free adminstrable datafields
	$datafield_form =& $DataFields->getLocalFieldsFormObject('pers');
	$datafield_form->field_attributes_default = array('cols' => round($max_col/1.3), 'style' => 'width:80%;');
	echo $datafield_form->getHiddenField(md5("is_sended"),1);
	foreach ($datafield_form->getFormFieldsByName() as $field_id) {
	$cssSw->switchClass();
	?>
	<tr>
		<td class="<? echo $cssSw->getClass() ?>" colspan="2" align="left" valign="top">
		<b><blockquote>
		<?=$datafield_form->getFormFieldCaption($field_id)?>:
		</b>
		<br>
		<?=$datafield_form->getFormField($field_id);?>
		</td>
	</tr>
	<?
	}
	echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=\"2\" align=\"left\" valign=\"top\"><b><blockquote>" . _("Lebenslauf:") . "</b><br>";
	echo "<textarea  name=\"lebenslauf\" style=\" width: 80%\" cols=".round($max_col/1.3)." rows=7 wrap=virtual>".htmlReady($my_about->user_info["lebenslauf"])."</textarea><a name=\"lebenslauf\"></a></td></tr>\n";
	if ($my_about->auth_user["perms"] == "dozent") {
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=\"2\" align=\"left\" valign=\"top\"><b><blockquote>" . _("Schwerpunkte:") . "</b><br>";
		echo "<textarea  name=\"schwerp\" style=\" width: 80%\" cols=".round($max_col/1.3)." rows=7 wrap=virtual>".htmlReady($my_about->user_info["schwerp"])."</textarea><a name=\"schwerpunkte\"></a></td></tr>\n";
		$cssSw->switchClass();
		echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=\"2\" align=\"left\" valign=\"top\"><b><blockquote>" . _("Publikationen:") . "</b><br>";
		echo "<textarea  name=\"publi\" style=\" width: 80%\" cols=".round($max_col/1.3)." rows=7 wrap=virtual>".htmlReady($my_about->user_info["publi"])."</textarea><a name=\"publikationen\"></a></td></tr>\n";
	}
	
	$cssSw->switchClass();
	echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=2><blockquote><br><input type=\"IMAGE\" " . makeButton("uebernehmen", "src") . " border=\"0\" value=\"" . _("Änderungen übernehmen") . "\"><br></blockquote></td></tr>\n</table>\n</td>";
}

if ($view=="Sonstiges") {
	if ($freie=="create_freie") create_freie();
	if ($freie=="delete_freie") delete_freie($freie_id);
	if ($freie=="update_freie") update_freie();
	if ($freie=="order_freie") order_freie($cat_id,$direction,$username);
	print_freie($username);
}

// Ab hier die Views der MyStudip-Sektion
if($view == "allgemein") {
	require_once("mystudip.inc.php");
	change_general_view();
}

if($view == "Forum") {
	require_once("forumsettings.inc.php");
}

if ($view == "Stundenplan") {
	require_once ("ms_stundenplan.inc.php");
	check_schedule_default();
	change_schedule_view();
}
	
if($view == "calendar" && $CALENDAR_ENABLE) {
	require_once("$RELATIVE_PATH_CALENDAR/calendar_settings.inc.php");
}

if ($view == "Messaging") {
	require_once ("messagingSettings.inc.php");
	check_messaging_default();
	change_messaging_view();
}

if ($view=="Login") {
	if ($my_about->check=="user" && !$perm->have_perm("admin")) {
		echo "<tr><td colspan=2 class=blank><blockquote>";
		echo "<br><br>" . _("Um die automatische Anmeldung zu nutzen, m&uuml;ssen Sie ihre pers&ouml;nliche Login-Datei auf ihren Rechner kopieren. Mit dem folgenden Link &ouml;ffnet sich ein Fenster, indem Sie ihr Passwort eingeben m&uuml;ssen.") . " ";
		echo _("Dann wird die Datei erstellt und zu Ihrem Rechner geschickt.") . "<br><br>";
		echo "<center><b><a href=\"javascript:oeffne();\">" . _("Auto-Login-Datei erzeugen") . "</a></b></center>";
		echo "<br><br>" . _("<b>ACHTUNG!</b> Die automatische Anmeldung stellt eine große Sicherheitslücke dar. Jeder, der Zugriff auf Ihren Rechner hat, kann sich damit unter Ihrem Namen in Stud.IP einloggen!");
		echo "<br><br>";
		echo _("Eine sichere Variante besteht aus folgendem Link:") . "<br>";
		printf ("<center><b><a href=\"%s\">%s</a></b></center><br>", sprintf("index.php?again=yes&shortcut=%s", $auth->auth["uname"]), sprintf(_("Stud.IP - Login (%s)"), $auth->auth["uname"]));
		echo _("Speichern Sie diesen Link als Bookmark oder Favoriten.") . "<br>";
		echo _("Er f&uuml;hrt Sie direkt zum Login-Bildschirm von Stud.IP mit Ihrem schon eingetragenen Benutzernamen. Sie m&uuml;ssen nur noch Ihr Passwort eingeben.");
		echo "</blockquote></td></tr>";
	} else {
		echo "<blockquote><br><br>" . _("Als Administrator d&uuml;rfen Sie dieses Feature nicht nutzen - Sie tragen Verantwortung!");
		echo "</blockquote></td></tr>";
	}
}	

////////////////

	echo "\n</table></td></tr></form>";
	echo "\n</table>";
	echo "</body>";
	echo "</html>";
}

page_close();
?>
