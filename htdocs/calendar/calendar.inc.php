<?
/**
* calendar.inc.php
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version		$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	calendar
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// calendar.inc.php
//
// Copyright (c) 2003 Peter Tienel <pthienel@web.de> 
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


require_once($ABSOLUTE_PATH_STUDIP . "config.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "functions.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_func.inc.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_visual.inc.php");
require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/calendar_misc_func.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/SeminarEvent.class.php");

// -- hier muessen Seiten-Initialisierungen passieren --
// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

// bei Einsprung ohne $cmd wird im Header eine Erlaeuterung ausgegeben
if(!$cmd && !$atime)
	$intro = TRUE;

// Wird kein timestamp an das Skript uebergeben, benutze aktuellen
if(!$atime && !$termin_id)
	$atime = time();
	
if(isset($mod_s_x)) $mod = 'SINGLE';
if(isset($mod_d_x)) $mod = 'DAILY';
if(isset($mod_w_x)) $mod = 'WEEKLY';
if(isset($mod_m_x)) $mod = 'MONTHLY';
if(isset($mod_y_x)) $mod = 'YEARLY';

if($mod)
	$cmd = 'edit';

if ($del_x && $termin_id)
	$cmd = 'del';

if ($back_recur_x)
	unset($set_recur_x);

if ($cancel_x) {
	if($calendar_sess_control_data['source']){
		$destination = $calendar_sess_control_data['source'];
		$calendar_sess_control_data['source'] = '';
		page_close();
		header("Location: $destination");
		exit;
	}
	if ($calendar_sess_control_data['view_prv'])
		$cmd = $calendar_sess_control_data['view_prv'];
	else
		$cmd = $calendar_user_control_data['view'];
}

// Zeitbereich eingrenzen
if(isset($atime) && ($atime < 0 || $atime > 2114377200))
	$atime = time();

// Datum fuer "Gehe-zu-Funktion" checken
if(check_date($jmp_m, $jmp_d, $jmp_y))
	$atime = mktime(12, 0, 0, $jmp_m, $jmp_d, $jmp_y);
else{
	$jmp_d = date('j', $atime);
	$jmp_m = date('n', $atime);
	$jmp_y = date('Y', $atime);
}

// Benutzereinstellungen uebernehmen
if($cmd_cal == 'chng_cal_settings'){
	$calendar_user_control_data = array(
		'view'             => $cal_view,
		'start'            => $cal_start,
		'end'              => $cal_end,
		'step_day'         => $cal_step_day,
		'step_week'        => $cal_step_week,
		'type_week'        => $cal_type_week,
		'holidays'         => $cal_holidays,
		'sem_data'         => $cal_sem_data,
		'link_edit'        => $cal_link_edit,
		'bind_seminare'    => $calendar_user_control_data['bind_seminare'],
		'ts_bind_seminare' => $calendar_user_control_data['ts_bind_seminare'],
		'number_of_events' => $calendar_user_control_data['number_of_events'],
		'delete'           => $cal_delete
	);
}

$db_check =& new DB_Seminar();

if(!isset($calendar_user_control_data['number_of_events'])){
	$db_check->query("SELECT COUNT(*) cnt FROM calendar_events WHERE autor_id='$user->id' AND autor_id=range_id GROUP BY autor_id");
	$db_check->next_record();
	$calendar_user_control_data['number_of_events'] = $db_check->f('cnt');
	$calendar_user_control_data['delete'] = 6;
}

$db_check->query("SELECT Seminar_id, mkdate FROM seminar_user WHERE user_id='$user->id' ORDER BY mkdate DESC");
while ($db_check->next_record()
		&& ($db_check->f('mkdate') > $calendar_user_control_data['ts_bind_seminare']
		|| $db_check->f('mkdate') == 0)) {
	$calendar_user_control_data['bind_seminare'][$db_check->f('Seminar_id')] = 'TRUE';
}
$calendar_user_control_data['ts_bind_seminare'] = time();

// Wenn "Einbinden-Formular" abgeschickt wurde, dann ...["bind_seminare"] erneuern
if($sem)
	$calendar_user_control_data['bind_seminare'] = $sem;
if(is_array($calendar_user_control_data['bind_seminare']))
	$bind_seminare = array_keys($calendar_user_control_data['bind_seminare'], 'TRUE');
else
	$bind_seminare = '';

// Wenn Termin-Anlegen oder -Bearbeiten beendet ist, vergiss die Formulardaten
if(isset($calendar_sess_forms_data) && $cmd != 'add'){
	$sess->unregister('calendar_sess_forms_data');
	unset($calendar_sess_forms_data);
}

if($cmd == ''){
	if($termin_id)
		// wird eine termin_id uebergeben immer in den Bearbeiten-Modus
		$cmd = 'edit';
	else
		$cmd = $calendar_user_control_data['view'];
}

if(!$calendar_sess_control_data)
	$sess->register('calendar_sess_control_data');

$accepted_vars = array('start_m', 'start_h', 'start_day', 'start_month', 'start_year', 'end_m',
											'end_h',	'end_day', 'end_month', 'end_year',	'exp_day', 'exp_month',
											'exp_year', 'cat', 'priority', 'txt', 'content', 'loc', 'linterval_d',
											'linterval_w', 'wdays', 'type_m', 'linterval_m2', 'sinterval_m',
											'linterval_m1', 'wday_m', 'day_m', 'type_y', 'sinterval_y', 'wday_y',
											'day_y', 'month_y1', 'month_y2', 'atime', 'termin_id', 'exp_c', 'mod',
											'via', 'cat_text');

if($cmd == 'add' || $cmd == 'edit'){
	if(!isset($calendar_sess_forms_data))
		$sess->register('calendar_sess_forms_data');
	if(!empty($HTTP_POST_VARS)){
			
		// Formulardaten uebernehmen
		foreach ($HTTP_POST_VARS as $key => $value) {
			if(in_array($key, $accepted_vars))
				$calendar_sess_forms_data[$key] = $value;
		}
		extract($calendar_sess_forms_data, EXTR_OVERWRITE);
	}
	else
		$calendar_sess_control_data['mod'] = '';
	
}

if($source_page && ($cmd == 'edit' || $cmd == 'add' || $cmd == 'delete')){
	$calendar_sess_control_data['source'] = rawurldecode($source_page);
}

// Seitensteuerung
switch($cmd){
	case 'showday':
		$calendar_sess_control_data['view_prv'] = $cmd;
		$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
		break;
	case 'add':
		switch($calendar_sess_control_data['view_prv']){
			case 'showday':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
				break;
			case 'showweek':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Wochenansicht");
				break;
			case 'showmonth':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Monatsansicht");
				break;
			case 'showyear':
				$title = _("Mein pers&ouml;nlicher Terminkalender - Jahresansicht");
		}
		break;
	case 'del':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Tagesansicht");
		$atermin =& new DbCalendarEvent($termin_id);
		$atermin->delete();
		
		if($calendar_sess_control_data['source']){
			$destination = $calendar_sess_control_data['source'];
			$calendar_sess_control_data['source'] = '';
			header("Location: $destination");
			page_close();
			die;
		}
		
		if(!empty($calendar_sess_control_data['view_prv']))
			$cmd = $calendar_sess_control_data['view_prv'];
		else
			$cmd = 'showday';
		break;
		
	case 'showweek':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Wochenansicht");
		$calendar_sess_control_data['view_prv'] = $cmd;
		break;
		
	case 'showmonth':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Monatsansicht");
		$calendar_sess_control_data['view_prv'] = $cmd;
		break;
		
	case 'showyear':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Jahresansicht");
		$calendar_sess_control_data['view_prv'] = $cmd;
		break;
	
	case 'export':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Termindaten importieren, exportieren und synchronisieren");
		break;
		
	case 'bind':
		$title = _("Mein pers&ouml;nlicher Terminkalender - Veranstaltungstermine einbinden");
		break;
		
	case 'edit':
		if ($termin_id && !$mod) {
			//if($sem_id){
			
			if ($evtype == 'sem') {
				$atermin =& new SeminarEvent();
				if (!$atermin->restore($termin_id)) {
					// its something wrong... better to go back to the last view
					page_close();
					header("Location: " . $PHP_SELF	. "?cmd="
							. $calendar_sess_control_data['view_prv'] . "&atime=$atime");
					exit;
				}
			}
			else{	
				$atermin =& new DbCalendarEvent($termin_id);
				$repeat = $atermin->getRepeat();
				$mod = $repeat['rtype'];
		
			}
		}
		if($termin_id) {
			if (get_class($atermin) == 'seminarevent')
				$title = _("Mein pers&ouml;nlicher Terminkalender - Veranstaltungstermin");
			else
				$title = _("Mein pers&ouml;nlicher Terminkalender - Termin bearbeiten");
		}
		else
			$title = _("Mein pers&ouml;nlicher Terminkalender - Neuer Termin");
			
		switch($mod){
			case 'SINGLE':
				break;
			case 'DAILY':
				if($type == 'wdaily')
					$linterval_d = '';
				break;
			case 'WEEKLY':
			case 'MONTHLY':
			case 'YEARLY':
				break;
		}
		break;
		
}

// add an event to database *********************************************************

if ($cmd == 'add') {
	// Ueberpruefung der Formulareingaben
	$err = array();
	if(!check_date($start_month, $start_day, $start_year))
		$err['start_time'] = TRUE;
	if(!check_date($end_month, $end_day, $end_year))
		$err['end_time'] = TRUE;
	
	if(!$err['start_time'] && !$err['end_time']){
		$start = mktime($start_h, $start_m, 0, $start_month, $start_day, $start_year);
		$end = mktime($end_h, $end_m, 0, $end_month, $end_day, $end_year);
		if($start > $end)
			$err['end_time'] = TRUE;
	}
	
	if(!preg_match('/^.*\S+.*$/', $txt))
		$err['titel'] = TRUE;
	
	switch($mod_prv){
		case 'DAILY':
			if (!preg_match("/^\d{1,3}$/", $linterval_d)) {
				$err['linterval_d'] = TRUE;
				$set_recur_x = 1;
			}
			break;
		case 'WEEKLY':
			if (!preg_match("/^\d{1,3}$/", $linterval_w)) {
				$err['linterval_w'] = TRUE;
				$set_recur_x = 1;
			}
			break;
		case 'MONTHLY':
			if ($type_m == 'day') {
				if (!preg_match("/^\d{1,2}$/", $day_m) || $day_m > 31 || $day_m < 1) {
					$err['sinterval_m'] = TRUE;
					$set_recur_x = 1;
				}
				if (!preg_match("/^\d{1,3}$/", $linterval_m1)) {
					$err['linterval_m1'] = TRUE;
					$set_recur_x = 1;
				}
			}
			else {
				if (!preg_match("/^\d{1,3}$/", $linterval_m2)) {
					$err['linterval_m2'] = TRUE;
					$set_recur_x = 1;
				}
			}
			break;
		case 'YEARLY':
			// Jahr 2000 als Schaltjahr
			if (!check_date($month_y1, $day_y, 2000)) {
				$err['day_y'] = TRUE;
				$set_recur_x = 1;
			}
	}
	
	if($exp_c == 'date')
		if (!check_date($exp_month, $exp_day, $exp_year)) {
			$err['exp_time'] = TRUE;
			$set_recur_x = 1;
		}
		else{
			$exp = mktime(23, 59, 59, $exp_month, $exp_day, $exp_year);
			if (!$err['end_time'] && $exp < $end) {
				$err['exp_time'] = TRUE;
				$set_recur_x = 1;
			}
		}
	else
		$exp = '';
	
	// wenn alle Daten OK, dann Termin anlegen oder, wenn termin_id vorhanden,
	// updaten
	if (empty($err)) {
	
		$atermin =& new DbCalendarEvent('', array(
				'DTSTART'         => $start,
				'DTEND'           => $end,
				'SUMMARY'         => $txt,
				'CATEGORIES'      => $cat_text,
				'STUDIP_CATEGORY' => $cat,
				'PRIORITY'        => $priority,
				'LOCATION'        => $loc,
				'DESCRIPTION'     => $content));
		$atermin->setRepeat(array('rtype' => 'SINGLE'));
			$atermin->setDescription($content);
		switch ($via) {
			case 'PUBLIC':
				$atermin->setType('PUBLIC');
				break;
			case 'PRIVATE':
				$atermin->setType('PRIVATE');
				break;
			case 'CONFIDENTIAL':
				$atermin->setType('CONFIDENTIAL');
			default:
				$atermin->setType('PRIVATE');
		}
			
		switch ($mod_prv) {
			case 'SINGLE':
				$atermin->setRepeat(array('rtype' => 'SINGLE'));
				break;
				
			case 'DAILY':
				if ($type_d == 'daily') {
					$atermin->setRepeat(array('rtype' => 'DAILY', 'linterval' => $linterval_d,
							'expire' => $exp));
				}
				elseif ($type_d == 'wdaily') {
					$atermin->setRepeat(array('rtype' => 'WEEKLY', 'linterval' => '1',
							'wdays' => '12345', 'expire' => $exp));
				}
				break;
				
			case 'WEEKLY':
				if (empty($wdays)) {
					$atermin->setRepeat(array('rtype' => 'WEEKLY', 'linterval' => $linterval_w,
							'expire' => $exp));
				}
				else {
					$weekdays = implode('', $wdays);
					$atermin->setRepeat(array('rtype' => 'WEEKLY', 'linterval' => $linterval_w,
							'wdays' => $weekdays, 'expire' => $exp));
				}
				break;
				
			case 'MONTHLY':
				if ($type_m == 'day') {
					$atermin->setRepeat(array('rtype' => 'MONTHLY', 'linterval' => $linterval_m1,
							'day' => $day_m, 'expire' => $exp));
				}
				else {
					$atermin->setRepeat(array('rtype' => 'MONTHLY', 'linterval' => $linterval_m2,
							'sinterval' => $sinterval_m, 'wdays' => $wday_m, 'expire' => $exp));
				}
				break;
				
			case 'YEARLY':
				if ($type_y == 'day') {
					$atermin->setRepeat(array('rtype' => 'YEARLY', 'month' => $month_y1,
							'day' => $day_y, 'expire' => $exp));
				}
				else {
					$atermin->setRepeat(array('rtype' => 'YEARLY', 'sinterval' => $sinterval_y,
							'wdays' => $wday_y, 'month' => $month_y2, 'expire' => $exp));
				}
				break;
		}
			
		if (!$set_recur_x && !$back_recur_x) {
		
			// wird eine termin_id uebergeben, wird ein update durchgefuehrt
			if($termin_id) {
				$termin_old =& new DbCalendarEvent($termin_id);
				$termin_old->update($atermin);
				$termin_old->save();
			}
			else
				$atermin->save();
			
			if($calendar_sess_control_data['source']){
				$destination = $calendar_sess_control_data['source'];
				$calendar_sess_control_data['source'] = '';
				page_close();
				header("Location: $destination");
				exit;
			}
			
			if(!empty($calendar_sess_control_data['view_prv']))
				$cmd = $calendar_sess_control_data['view_prv'];
			else
				$cmd = 'showday';
				
		}
		else {
			$cmd = 'edit';
			$mod = $mod_prv ? $mod_prv : 'SINGLE';
		}
	}
	else {
		$cmd = 'edit';
		$mod = $mod_prv ? $mod_prv : 'SINGLE';
		if ($back_recur_x) {
			$set_recur_x = 1;
			unset($back_recur_x);
		}
		else
			unset($set_recur_x);
	}
}

// Tagesuebersicht anzeigen ***************************************************

if($cmd == 'showday'){
	
	$d_start = $calendar_user_control_data['start'];
	$d_end = $calendar_user_control_data['end'];

	$at = date('G', $atime);
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
	
	include_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarDay.class.php");
	$aday =& new DbCalendarDay($atime);
	$aday->bindSeminarEvents($bind_seminare);
	$tab = createDayTable($aday, $st, $et, $calendar_user_control_data['step_day'],
							TRUE, TRUE, FALSE, 70, 20, 3, 1);
	
	include($RELATIVE_PATH_CALENDAR . "/views/day.inc.php");

}

// Wochenuebersicht anzeigen **************************************************

if($cmd == 'showweek'){

	$w_start = $calendar_user_control_data['start'];
	$w_end = $calendar_user_control_data['end'];
	
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
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/week.inc.php");

}

// Monatsuebersicht anzeigen **************************************************

if($cmd == 'showmonth'){

	include_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarMonth.class.php");
	
	$amonth =& new DbCalendarMonth($atime);
	$calendar_sess_forms_data['bind_seminare'] = '';
	$amonth->bindSeminarEvents($bind_seminare);
	$amonth->sort();
	
	if($mod == 'compact' || $mod == 'nokw'){
		$hday['name'] = '';
		$hday['col'] = '';
		$width = '20';
		$height = '20';
	}
	else{
		$width = '90';
		$height = '80';
	}
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/month.inc.php");
	
}

// Jahresuebersicht ***********************************************************

if($cmd == 'showyear'){

	include_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/lib/DbCalendarYear.class.php");
	
	$ayear =& new DbCalendarYear($atime);
	$ayear->bindSeminarEvents($bind_seminare);
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/year.inc.php");

}

// edit an event *********************************************************

// ist $termin_id an das Skript uebergeben worden, dann bearbeite diesen Termin
// ist $atime an das Skript uebergeben worden, dann erzeuge neuen Termin (s.o.)
if ($cmd == "edit") {

	// call from dayview for new event
	if ($atime && !$termin_id && !$mod) {
		$start_h = date('G', $atime);
		$start_m = date('i', $atime);
		$start_day = date('j', $atime);
		$start_month = date('n', $atime);
		$start_year = date('Y', $atime);
		$end_h = $start_h + 1;
		$end_m = 0;
		$end_day = $start_day;
		$end_month = $start_month;
		$end_year = $start_year;
		$expire = 2114377200;
		$cat = 1;
		$cat_text = '';
		$via = 'PRIVATE';
		$wdays = array(strftime('%u', $atime));
		$edit_mode_out = '<b>';
		$edit_mode_out .= sprintf(_("Termin erstellen am %s"), ldate($atime));
		$edit_mode_out .= "</b></td></tr>\n";
	}
	
	// call from different views to edit an event
	elseif ($atermin && !$mod_prv) {
		$start_h = date('G', $atermin->getStart());
		$start_m = date('i', $atermin->getStart());
		$start_day = date('j', $atermin->getStart());
		$start_month = date('n', $atermin->getStart());
		$start_year = date('Y', $atermin->getStart());
		$end_h = date('G', $atermin->getEnd());
		$end_m = date('i', $atermin->getEnd());
		$end_day = date('j', $atermin->getEnd());
		$end_month = date('n', $atermin->getEnd());
		$end_year = date('Y', $atermin->getEnd());
		
		if (get_class($atermin) != 'seminarevent') {
			$expire = $atermin->getExpire();
			if ($expire == mktime(0, 0, 0, 1, 1, 2037))
				$exp_c = 'never';
			else
				$exp_c = 'date';
			$exp_day = date('j', $expire);
			$exp_month = date('n', $expire);
			$exp_year = date('Y', $expire);
			
			switch ($atermin->getType()) {
				case 'PUBLIC':
					$via = 'PUBLIC';
					break;
				case 'CONFIDENTIAL':
					$via = 'CONFIDENTIAL';
					break;
				default:
					$via = 'PRIVATE';
			}
					
			$priority = $atermin->getPriority();
			
			switch ($repeat['rtype']) {
				case 'SINGLE':
					break;
				case 'DAILY':
					$linterval_d = $repeat['linterval'];
					break;
				case 'WEEKLY':
					$linterval_w = $repeat['linterval'];
					for ($i = 0;$i < strlen($repeat['wdays']);$i++)
						$wdays[$repeat['wdays'][$i]] = $repeat['wdays'][$i];
					break;
				case 'MONTHLY':
					if ($repeat['wdays']) {
						$type_m = 'wday';
						$linterval_m2 = $repeat['linterval'];
						$sinterval_m = $repeat['sinterval'];
						for ($i = 0;$i < strlen($repeat['wdays']);$i++)
							$wday_m = $repeat['wdays'];
					}
					else {
						$type_m = 'day';
						$linterval_m1 = $repeat['linterval'];
						$day_m = $repeat['day'];
					}
					break;
				case 'YEARLY':
					if ($repeat['wdays']) {
						$type_y = 'wday';
						$sinterval_y = $repeat['sinterval'];
						$wday_y = $repeat['wdays'];
						$month_y2 = $repeat['month'];
					}
					else {
						$type_y = 'day';
						$day_y = $repeat['day'];
						$month_y1 = $repeat['month'];
					}
			}
		}
		
		$cat = $atermin->getCategory();
		$txt = htmlReady($atermin->getTitle());
		$content = htmlReady($atermin->getDescription());
		$loc = htmlReady($atermin->getLocation());
		
		// store all form values in session variable
		foreach ($accepted_vars as $var)
			$calendar_sess_forms_data[$var] = $$var;
		
		$edit_mode_out = '<b>';
		if (get_class($atermin) == 'seminarevent')
			$edit_mode_out .= sprintf(_("Termin am %s"), ldate($atermin->getStart()));
		else
			$edit_mode_out .= sprintf(_("Termin am %s bearbeiten"), ldate($atermin->getStart()));
		$edit_mode_out .= "</b>\n";
	}
	
	elseif ($mod_prv && $termin_id) {
		$edit_mode_out = "<b>";
		$edit_mode_out .= sprintf(_("Termin am %s bearbeiten"), ldate($atime));
		$edit_mode_out .= "</b>\n";
	}
	
	elseif($mod && $atime) {
	//	if (check_date($start_month, $start_day, $start_year)) {
			$edit_mode_out = '<b>';
			$edit_mode_out .= sprintf(_("Termin erstellen am %s"),
				ldate(mktime(0, 0, 0, $start_month, $start_day, $start_year)));
			$edit_mode_out .= "</b>\n";
	//	}
	}
	else{
		page_close();
		die;
	}
	
	if(!$mod)
		$mod = 'SINGLE';
	
	// transfer form->form
	if($mod_prv){
		$txt = htmlentities(stripslashes($txt), ENT_QUOTES);
		$content = htmlentities(stripslashes($content), ENT_QUOTES);
		$loc = htmlentities(stripslashes($loc), ENT_QUOTES);
		$cat_text = htmlentities(stripslashes($cat_text), ENT_QUOTES);
	}
	
	// start and end time in 5 minute steps	
	$start_m = $start_m - ($start_m % 5);
	$end_m = $end_m - ($end_m % 5);

	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/edit.inc.php");
}

// Seminartermine einbinden **************************************************

if($cmd == 'bind'){
	
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/bind.inc.php");
	
}

// Termine importieren/exportieren/synchronisieren ***************************
if($cmd == 'export'){
		
	include($RELATIVE_PATH_CALENDAR . "/views/export.inc.php");
	
}
	
// Ansicht anpassen **********************************************************

if($cmd == 'changeview'){

	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/calendar_settings.inc.php");
	
}
	
include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/footer.inc.php");

// Save data back to database.
page_close();

?>
