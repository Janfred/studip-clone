<?
/*
calendar.inc.php 0.8-20020628
Persoenlicher Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthien@gmx.de>

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


require_once("config.inc.php"); //Daten laden
require_once("visual.inc.php");
require_once("functions.php");
require($RELATIVE_PATH_CALENDAR . "/calendar_func.inc.php");
require($RELATIVE_PATH_CALENDAR . "/calendar_visual.inc.php");

// -- hier muessen Seiten-Initialisierungen passieren --
// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

// bei Einsprung ohne $cmd wird im Header eine Erlaeuterung ausgegeben
if(!$cmd && !$atime)
	$intro = TRUE;

// Wird kein timestamp an das Skript uebergeben, benutze aktuellen
if(!$atime && !$termin_id)
	$atime = time();
	
if(isset($mod_s_x)) $mod = "SINGLE";
if(isset($mod_d_x)) $mod = "DAYLY";
if(isset($mod_w_x)) $mod = "WEEKLY";
if(isset($mod_m_x)) $mod = "MONTHLY";
if(isset($mod_y_x)) $mod = "YEARLY";

if($mod)
	$cmd = "edit";
	
// Zeitbereich eingrenzen
if(isset($atime) && ($atime < 0 || $atime > 2114377200))
	$atime = time();

// Datum fuer "Gehe-zu-Funktion" checken
if(check_date($jmp_m, $jmp_d, $jmp_y))
	$atime = mktime(12,0,0,$jmp_m,$jmp_d,$jmp_y);
else{
	$jmp_d = date("j", $atime);
	$jmp_m = date("n", $atime);
	$jmp_y = date("Y", $atime);
}

// Benutzereinstellungen uebernehmen
if($cmd_cal == "chng_cal_settings"){
	$calendar_user_control_data = array(
		"view"             => $cal_view,
		"start"            => $cal_start,
		"end"              => $cal_end,
		"step_day"         => $cal_step_day,
		"step_week"        => $cal_step_week,
		"type_week"        => $cal_type_week,
		"holidays"         => $cal_holidays,
		"sem_data"         => $cal_sem_data,
		"link_edit"        => $cal_link_edit,
		"bind_seminare"    => $calendar_user_control_data["bind_seminare"],
		"ts_bind_seminare" => $calendar_user_control_data["ts_bind_seminare"],
		"number_of_events" => $calendar_user_control_data["number_of_events"],
		"delete"           => $cal_delete
	);
}

$db_check =& new DB_Seminar;

if(!isset($calendar_user_control_data["number_of_events"])){
	$db_check->query("SELECT COUNT(*) cnt FROM termine WHERE autor_id='$user->id' AND autor_id=range_id GROUP BY autor_id");
	$db_check->next_record();
	$calendar_user_control_data["number_of_events"] = $db_check->f("cnt");
	$calendar_user_control_data["delete"] = 6;
}

$db_check->query("SELECT Seminar_id, mkdate FROM seminar_user WHERE user_id='$user->id' ORDER BY mkdate DESC");
while ($db_check->next_record()
		&& ($db_check->f("mkdate") > $calendar_user_control_data["ts_bind_seminare"]
		|| $db_check->f("mkdate") == 0)) {
	$calendar_user_control_data["bind_seminare"][$db_check->f("Seminar_id")] = "TRUE";
}
$calendar_user_control_data["ts_bind_seminare"] = time();

// Wenn "Einbinden-Formular" abgeschickt wurde, dann ...["bind_seminare"] erneuern
if($sem)
	$calendar_user_control_data["bind_seminare"] = $sem;
if(is_array($calendar_user_control_data["bind_seminare"]))
	$bind_seminare = array_keys($calendar_user_control_data["bind_seminare"], "TRUE");
else
	$bind_seminare = "";

// Wenn Termin-Anlegen oder -Bearbeiten beendet ist, vergiss die Formulardaten
if(isset($calendar_sess_forms_data) && $cmd != "edit"){
	$sess->unregister("calendar_sess_forms_data");
	unset($calendar_sess_forms_data);
}

if($cmd == ""){
	if($termin_id)
		// wird eine termin_id uebergeben immer in den Bearbeiten-Modus
		$cmd = "edit";
	else
		$cmd = $calendar_user_control_data["view"];
}

if(!$calendar_sess_control_data)
	$sess->register("calendar_sess_control_data");

if($cmd == "add" || $cmd == "edit"){
	if(!isset($calendar_sess_forms_data))
		$sess->register("calendar_sess_forms_data");
	if(!empty($HTTP_POST_VARS)){
	/*	if($calendar_sess_control_data["mod"])
			$mod_prv = $calendar_sess_control_data["mod"];
		else
			$mod_prv = "keine";
			
		if($mod)
			$calendar_sess_control_data["mod"] = $mod; */
			
		// Formulardaten uebernehmen
		$accepted_vars = array("start_m", "start_h", "start_day", "start_month", "start_year", "end_m",
													"end_h",	"end_day", "end_month", "end_year",	"exp_day", "exp_month",
													"exp_year", "cat", "priority", "txt", "content", "loc", "lintervall_d",
													"lintervall_w", "wdays", "type_m", "lintervall_m2", "sintervall_m",
													"lintervall_m1", "wday_m", "day_m", "type_y", "sintervall_y", "wday_y",
													"day_y", "month_y1", "month_y2", "atime", "termin_id", "exp_c", "mod"
													);
		reset($HTTP_POST_VARS);
		while(list($key, $value) = each($HTTP_POST_VARS)){
			if(in_array($key, $accepted_vars))
				$calendar_sess_forms_data[$key] = $value;
		}
		extract($calendar_sess_forms_data, EXTR_OVERWRITE);
	}
	else
		$calendar_sess_control_data["mod"] = "";
	
}

if($source_page && ($cmd == "edit" || $cmd == "add" || $cmd == "delete")){
	$calendar_sess_control_data["source"] = rawurldecode($source_page);
}

// Seitensteuerung
switch($cmd){
	case "showday":
		$calendar_sess_control_data["view_prv"] = $cmd;
		$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
		break;
	case "add":
		switch($calendar_sess_control_data["view_prv"]){
			case "showday":
				$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
				break;
			case "showweek":
				$title = _("Mein pers&ouml;nlicher Terminkalender - Wochenansicht");
				break;
			case "showmonth":
				$title = _("Mein pers&ouml;nlicher Terminkalender - Monatsansicht");
				break;
			case "showyear":
				$title = _("Mein pers&ouml;nlicher Terminkalender - Jahresansicht");
		}
		break;
	case "del":
		require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
		$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
		$atermin =& new DbCalendarEvent($termin_id);
		$atermin->delete();
		
		if($calendar_sess_control_data["source"]){
			$destination = $calendar_sess_control_data["source"];
			$calendar_sess_control_data["source"] = "";
			header("Location: $destination");
			die;
		}
		
		if(!empty($calendar_sess_control_data["view_prv"]))
			$cmd = $calendar_sess_control_data["view_prv"];
		else
			$cmd = "showday";
		break;
	case "showweek":
		$title = _("Mein pers&ouml;nlicher Terminkalender - Wochenansicht");
		$calendar_sess_control_data["view_prv"] = $cmd;
		break;
	case "showmonth":
		$title = _("Mein pers&ouml;nlicher Terminkalender - Monatsansicht");
		$calendar_sess_control_data["view_prv"] = $cmd;
		break;
	case "showyear":
		$title = _("Mein pers&ouml;nlicher Terminkalender - Jahresansicht");
		$calendar_sess_control_data["view_prv"] = $cmd;
		break;
	case "bind":
		$title = _("Mein pers&ouml;nlicher Terminkalender - Veranstaltungstermine einbinden");
		break;
/*		case "import":
		$title = "Mein pers&ouml;nlicher Terminkalender - Termine importieren";
		break; */
	case "edit":
		if($termin_id && !$mod){
			// in the near future there will be a factory method to get the
			// right type of event ;-)
			if($sem_id){
				$db_check_event =& new DBSeminar();
				$db_check_event->query("SELECT termin_id FROM termine WHERE termin_id='$termin_id' "
						. "AND range_id='$sem_id'");
				if($db_check_event->next_record()){
					require_once($RELATIVE_PATH_CALENDAR . "/lib/SeminarEvent.class.php");
					$atermin =& new SeminarEvent($termin_id);
				}
				else{
				// there is something wrong... its better to go back to the last view
					header("Location: " . $PHP_SELF	. "?cmd="
							. $calendar_sess_control_data["view_prv"] . "&atime=$atime");
					exit;
				}
			}
			else{	
				require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
				$atermin =& new DbCalendarEvent($termin_id);
				$repeat = $atermin->getRepeat();
		//	$translate = array("SINGLE"=>"keine", "DAYLY"=>"taeglich", "WEEKLY"=>"woechentlich",
			//	                 "MONTHLY"=>"monatlich", "YEARLY"=>"jaehrlich");
				$mod = $repeat["type"];
		//	if(empty($HTTP_POST_VARS))
			//	$calendar_sess_control_data["mod"] = $mod;
			}
		}
		if($termin_id)
			$title = _("Mein pers&ouml;nlicher Terminkalender - Termin bearbeiten");
		else
			$title = _("Mein pers&ouml;nlicher Terminkalender - Neuer Termin");
			
		switch($mod){
			case "SINGLE":
				break;
			case "DAYLY":
				if($type == "wdayly")
					$lintervall_d = "";
				break;
			case "WEEKLY":
			case "MONTHLY":
			case "YEARLY":
				break;
		}
		break;
	default:
		
}

// Termin hinzufuegen *********************************************************

if($cmd == "add"){
	// Ueberpruefung der Formulareingaben
	$err = "";
	if(!check_date($start_month, $start_day, $start_year))
		$err["start_time"] = TRUE;
	if(!check_date($end_month, $end_day, $end_year))
		$err["end_time"] = TRUE;
	
	if(!$err["start_time"] && !$err["end_time"]){
		$start = mktime($start_h,$start_m,0,$start_month,$start_day,$start_year);
		$end = mktime($end_h,$end_m,0,$end_month,$end_day,$end_year);
		if($start > $end)
			$err["end_time"] = TRUE;
	}
	/*
	if(ceil((mktime(12,0,0,$end_month,$end_day,$end_year) - mktime(12,0,0,$start_month,$start_day,$start_year)) / 86400) > 2){
		$err["end_time"] = TRUE;
		$err_message = "<br>Ein Termin darf sich &uuml;ber max. zwei Tage erstrecken!";
	}*/
	
	if(!preg_match('/^.*\S+.*$/', $txt))
		$err["titel"] = TRUE;
	
	switch($mod_prv){
		case "DAYLY":
			if(!preg_match("/^\d{1,3}$/", $lintervall_d))
				$err["lintervall_d"] = TRUE;
			break;
		case "WEEKLY":
			if(!preg_match("/^\d{1,3}$/", $lintervall_w))
				$err["lintervall_w"] = TRUE;
			break;
		case "MONTHLY":
			if($type_m == "day"){
				if(!preg_match("/^\d{1,2}$/", $day_m) && $day_m < 32)
					$err["sintervall_m"] = TRUE;
				if(!preg_match("/^\d{1,3}$/", $lintervall_m1))
					$err["lintervall_m1"] = TRUE;
			}
			else
				if(!preg_match("/^\d{1,3}$/", $lintervall_m2))
					$err["lintervall_m2"] = TRUE;
			break;
		case "YEARLY":
			// Jahr 2000 als Schaltjahr
			if(!check_date($month_y1, $day_y, 2000))
				$err["day_y"] = TRUE;
	}
	
	if($exp_c == "date")
		if(!check_date($exp_month, $exp_day, $exp_year))
			$err["exp_time"] = TRUE;
		else{
			$exp = mktime(23,59,59,$exp_month,$exp_day,$exp_year);
			if(!$err["end_time"] && $exp < $end)
				$err["exp_time"] = TRUE;
		}
	else
		$exp = "";
	
	// wenn alle Daten OK, dann Termin anlegen		
	if(empty($err)){
		include_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
		$atermin =& new DbCalendarEvent($start, $end, $txt, "SINGLE", $exp, $cat, $priority, $loc);		
		$atermin->setDescription($content);
		if($via == "public")
			$atermin->setType(-1);
		else
			$atermin->setType(-2);
			
		switch($mod_prv){
			case "SINGLE":
				$atermin->setRepeat("SINGLE");
				break;
			case "DAYLY":
				if($type_d == "dayly")
					$atermin->setRepeat("DAYLY", $lintervall_d);
				else if($type_d == "wdayly")
					$atermin->setRepeat("WEEKLY", 1, "12345");
				break;
			case "WEEKLY":
				if(empty($wdays))
					$atermin->setRepeat("WEEKLY", $lintervall_w);
				else{
					$weekdays = implode("", $wdays);
					$atermin->setRepeat("WEEKLY", $lintervall_w, $weekdays);
				}
				break;
			case "MONTHLY":
				if($type_m == "day")
					$atermin->setRepeat("MONTHLY", $lintervall_m1, $day_m);
				else
					$atermin->setRepeat("MONTHLY", $lintervall_m2, $sintervall_m, $wday_m);
				break;
			case "YEARLY":
				if($type_y == "day")
					$atermin->setRepeat("YEARLY", $month_y1, $day_y);
				else
					$atermin->setRepeat("YEARLY", $sintervall_y, $wday_y, $month_y2);
				break;
		}
		// wird eine termin_id uebergeben, werden nur die Daten des Termins geaendert
		if($termin_id)
			$atermin->setId($termin_id);
		$atermin->save();
		
		if($calendar_sess_control_data["source"]){
			$destination = $calendar_sess_control_data["source"];
			$calendar_sess_control_data["source"] = "";
			header("Location: $destination");
			die;
		}
		
		if(!empty($calendar_sess_control_data["view_prv"]))
			$cmd = $calendar_sess_control_data["view_prv"];
		else
			$cmd = "showday";
			
	}
	else{
		$cmd = "edit";
		if(!$mod_err)
			$mod_err = $mod_prv;
		$mod = $mod_err;
	}
}

// including the calendar header (it includes the studip main header)
require($RELATIVE_PATH_CALENDAR . "/views/header.inc.php");
require($RELATIVE_PATH_CALENDAR . "/views/navigation.inc.php");

// Tagesuebersicht anzeigen ***************************************************

if($cmd == "showday"){
	
	$d_start = $calendar_user_control_data["start"];
	$d_end = $calendar_user_control_data["end"];

	$at = date("G", $atime);
	if($at >=  $d_start && $at <= $d_end || !$atime){
		$st = $d_start;
		$et = $d_end;
	}
	elseif($at < $d_start){
		$st = 0;
		$et = $d_start + 2;
	}
	else{
		$st = $d_end - 2;
		$et = 23;
	}
	
	include_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarDay.class.php");
	$aday =& new DbCalendarDay($atime);
	$aday->bindSeminarEvents($bind_seminare);
	$tab = createDayTable($aday, $st, $et, $calendar_user_control_data["step_day"],
							TRUE, TRUE, FALSE, 70, 20, 3, 1);
	
	include($RELATIVE_PATH_CALENDAR . "/views/day.inc.php");

}

// Wochenuebersicht anzeigen **************************************************

if($cmd == "showweek"){

	$w_start = $calendar_user_control_data["start"];
	$w_end = $calendar_user_control_data["end"];
	
	if(isset($wtime))
		$at = (int) $wtime;
	if(!($at > 0 && $at < 24))
		$at = $w_start;
	if($at >=  $w_start && $at <= $w_end){
		$st = $w_start;
		$et = $w_end;
	}
	else if($at < $w_start){
		$st = 0;
		$et = $w_start + 2;
	}
	else{
		$st = $w_end - 2;
		$et = 23;
	}

	include_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarWeek.class.php");
	$aweek =& new DbCalendarWeek($atime, $calendar_user_control_data["type_week"]);
	$aweek->bindSeminarEvents($bind_seminare);
	$tab = createWeekTable($aweek, $st, $et, $calendar_user_control_data["step_week"],
												FALSE, $calendar_user_control_data["link_edit"]);
	$rowspan = ceil(3600 / $calendar_user_control_data["step_week"]);
	$height = ' height="20"';
	if($aweek->getType() == 5)
		$width = "98";
	else
		$width = "99";
	if($rowspan > 1){
		$colspan_1 = ' colspan="2"';
		$colspan_2 = $tab["max_columns"] + 4;
	}
	else{
		$colspan_1 = "";
		$colspan_2 = $tab["max_columns"] + 2;
	}
	
	include($RELATIVE_PATH_CALENDAR . "/views/week.inc.php");

}

// Monatsuebersicht anzeigen **************************************************

if($cmd == "showmonth"){

	include_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarMonth.class.php");
	
	$amonth =& new DbCalendarMonth($atime);
	$calendar_sess_forms_data["bind_seminare"] = "";
	$amonth->bindSeminarEvents($bind_seminare);
	$amonth->sort();
	
	if($mod == "compact" || $mod == "nokw"){
		$hday["name"] = "";
		$hday["col"] = "";
		$width = "20";
		$height = "20";
	}
	else{
		$width = "90";
		$height = "80";
	}
	
	include($RELATIVE_PATH_CALENDAR . "/views/month.inc.php");
	
}

// Jahresuebersicht ***********************************************************

if($cmd == "showyear"){

	include_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarYear.class.php");
	
	$ayear =& new DbCalendarYear($atime);
	$ayear->bindSeminarEvents($bind_seminare);
	
	include($RELATIVE_PATH_CALENDAR . "/views/year.inc.php");

}

// edit an event *********************************************************

// ist $termin_id an das Skript uebergeben worden, dann bearbeite diesen Termin
// ist $atime an das Skript uebergeben worden, dann erzeuge neuen Termin (s.o.)
if($cmd == "edit"){

	// call from dayview for new event
	if($atime && !$termin_id/* && !$mod*/){
		$start_h = date("G", $atime);
		$start_m = date("i", $atime);
		$start_day = date("j", $atime);
		$start_month = date("n", $atime);
		$start_year = date("Y", $atime);
		$end_h = $start_h + 1;
		$end_m = 0;
		$end_day = $start_day;
		$end_month = $start_month;
		$end_year = $start_year;
		$expire = 2114377200;
		$cat = 1;
		$via = "private";
		$edit_mode_out = "<b>";
		$edit_mode_out .= sprintf(_("Termin erstellen f&uuml;r %s"), ldate($atime));
		$edit_mode_out .= "</b></td></tr>\n";
	}
	// call from different views to edit an event
	else if($atermin && !$mod_prv){
		$start_h = date("G", $atermin->getStart());
		$start_m = date("i", $atermin->getStart());
		$start_day = date("j", $atermin->getStart());
		$start_month = date("n", $atermin->getStart());
		$start_year = date("Y", $atermin->getStart());
		$end_h = date("G", $atermin->getEnd());
		$end_m = date("i", $atermin->getEnd());
		$end_day = date("j", $atermin->getEnd());
		$end_month = date("n", $atermin->getEnd());
		$end_year = date("Y", $atermin->getEnd());
		$expire = $atermin->getExpire();
		if($expire == mktime(0,0,0,1,1,2037))
			$exp_c = "never";
		else
			$exp_c = "date";
		$exp_day = date("j", $expire);
		$exp_month = date("n", $expire);
		$exp_year = date("Y", $expire);
		if($atermin->getType() == -1)
			$via = "public";
		else
			$via = "private";
		$cat = $atermin->getCategory();
		$priority = $atermin->getPriority();
		$txt = htmlReady($atermin->getTitle());
		$content = htmlReady($atermin->getDescription());
		$loc = htmlReady($atermin->getLocation());
		switch($repeat["type"]){
			case "SINGLE":
				break;
			case "DAYLY":
				$lintervall_d = $repeat["lintervall"];
				break;
			case "WEEKLY":
				$lintervall_w = $repeat["lintervall"];
				for($i = 0;$i < strlen($repeat["wdays"]);$i++)
					$wdays[$repeat["wdays"][$i]] = $repeat["wdays"][$i];
				break;
			case "MONTHLY":
				if($repeat["day"] == ""){
					$type_m = "wday";
					$lintervall_m2 = $repeat["lintervall"];
					$sintervall_m = $repeat["sintervall"];
					for($i = 0;$i < strlen($repeat["wdays"]);$i++)
						$wday_m = $repeat["wdays"];
				}
				else{
					$type_m = "day";
					$lintervall_m1 = $repeat["lintervall"];
					$day_m = $repeat["day"];
				}
				break;
			case "YEARLY":
				if($repeat["day"] == ""){
					$type_y = "wday";
					$sintervall_y = $repeat["sintervall"];
					$wday_y = $repeat["wdays"];
					$month_y2 = $repeat["month"];
				}
				else{
					$type_y = "day";
					$day_y = $repeat["day"];
					$month_y1 = $repeat["month"];
				}
		}
		$edit_mode_out = "<b>";
		if($atermin->getSeminarId())
			$edit_mode_out .= sprintf(_("Termin am %s"), ldate($atermin->getStart()));
		else
			$edit_mode_out .= sprintf(_("Termin am %s bearbeiten"), ldate($atermin->getStart()));
		$edit_mode_out .= "</b>\n";
	}
	else if($mod_prv && $termin_id){
		$edit_mode_out = "<b>";
		$edit_mode_out .= sprintf(_("Termin am %s bearbeiten"), ldate($atime));
		$edit_mode_out .= "</b>\n";
	}
	else if($mod && $atime)
		if(check_date($start_month, $start_day, $start_year)) {
			$edit_mode_out = "<b>";
			$edit_mode_out .= sprintf(_("Termin erstellen am %s"),
				ldate(mktime(0,0,0,$start_month,$start_day,$start_year)));
			$edit_mode_out .= "</b>\n";
		}
	else{
		page_close();
		die;
	}
	
	if(!$mod)
		$mod = "SINGLE";
	
	// transfer form->form
	if($mod_prv){
		$txt = htmlentities(stripslashes($txt), ENT_QUOTES);
		$content = htmlentities(stripslashes($content), ENT_QUOTES);
		$loc = htmlentities(stripslashes($loc), ENT_QUOTES);
	}
	
	// start and end time in 5 minute steps	
	$start_m = $start_m - ($start_m % 5);
	$end_m = $end_m - ($end_m % 5);
	
	include($RELATIVE_PATH_CALENDAR . "/views/edit.inc.php");
}

// Seminartermine einbinden **************************************************

if($cmd == "bind"){

	// alle vom user abonnierten Seminare
	$db =& new DB_Seminar;
	if(!isset($sortby))
		$sortby = "seminar_user.gruppe, seminare.Name";
	//	if($sortby == "count")
	//	$sortby = "count DESC";
	if(!isset($order))
		$order = "ASC";
	$query = "SELECT seminare.Name, seminare.Seminar_id, seminar_user.status, seminar_user.gruppe, count(termin_id) as count "
				 . "FROM seminar_user LEFT JOIN seminare USING (Seminar_id) LEFT JOIN termine ON range_id=seminare.Seminar_id WHERE seminar_user.user_id = '"
				 . $user->id."' GROUP BY Seminar_id ORDER BY $sortby $order";
	$db->query($query);
	if($order == "ASC")
		$order = "DESC";
	else
		$order = "ASC";
	
	include($RELATIVE_PATH_CALENDAR . "/views/bind.inc.php");
	
}

// Termine importieren *******************************************************
/*	if($cmd == "import"){
		
	include($RELATIVE_PATH_CALENDAR . "/calendar_import.inc.php");
} */
	
// Ansicht anpassen **********************************************************

if($cmd == "changeview"){

	include($RELATIVE_PATH_CALENDAR . "/calendar_settings.inc.php");
	
}
	
include($RELATIVE_PATH_CALENDAR . "/views/footer.inc.php");
	
// Save data back to database.
page_close();

?>
