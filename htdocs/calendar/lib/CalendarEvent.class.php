<?

/*
calendarEvent.class.php - 0.8.20020409a
Klassen fuer Persoenlichen Terminkalender in Stud.IP.
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

//****************************************************************************

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "config.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_CALENDAR"]
		. "/lib/Event.class.php");

class CalendarEvent extends Event {
	
	var $ts;          // der "genormte" Timestamp
	var $user_id;
	var $dev = FALSE; // TRUE wenn Tagestermin (boolean)	
	
	function CalendarEvent ($properties, $id = "") {
		global $user, $PERS_TERMIN_KAT, $TERMIN_TYP;
		$this->user_id = $user->id;
		
		parent::Event($properties);
		
		if (!$id)
			$id = $this->createUniqueId();
		
		$this->id = $id;
		if (!$this->properties['UID'])
			$this->properties['UID'] = $this->getUid($this->id);
		// privater Termin als Standard
		if($this->properties['CLASS'] === '')
			$this->properties['CLASS'] = 'PRIVATE';
	}
	
	// public
	function getExpire () {
		return $this->properties['RRULE']['expire'];
	}
	
	/**
	* Returns the name of the category.
	*
	* @access public
	* @return String the name of the category
	*/
	function getCategoryName () {
		global $PERS_TERMIN_KAT;
		return ($PERS_TERMIN_KAT[$this->properties['CATEGORIES']]['name']);
	}
	
	function isDayEvent () {
		return $this->dev;
	}
	
	function setDayEvent ($is_dev) {
		$this->dev = $is_dev;
	}
	
	// public
	function getTs () {
		
		return $this->properties['RRULE']['ts'];
	}
	
	function getUserId () {
		return $this->user_id ? $this->user_id : FALSE;
	}
	
	// public
	function getRepeat ($index = '') {
		if (is_array($this->properties['RRULE']))
			return $index ? $this->properties['RRULE'][$index] : $this->properties['RRULE'];
		
		return FALSE;
	}
	
	// public
	function getType () {
		return $this->properties['CLASS'];
	}
	
	// public
	function setType ($type) {
		$this->properties['CLASS'] = $type;
		$this->chng_flag = TRUE;
	}
	
	// public
	function getPriority () {
		return $this->properties['PRIORITY'];
	}
	
	function setPriority ($priority) {
			$this->properties['PRIORITY'] = $priority;
			$this->chng_flag = TRUE;
	}
	
	function setRepeat ($r_rule) {
	
		$this->properties['RRULE'] = $this->createRepeat($r_rule,
				$this->properties['DTSTART'], $this->properties['DTEND']);
		$this->ts = $this->properties['RRULE']['ts'];
		$this->chng_flag = TRUE;
	}
	
	/**
	* Sets the recurrence rule of this event
	*
	* The possible repetitions are:
	* SINGLE, DAILY, WEEKLY, MONTHLY, YEARLY
	*
	*/
	function createRepeat ($r_rule, $start, $end) {
		$duration = (int) ((mktime(12, 0, 0, date('n', $end), date('j', $end), date('Y', $end), 0)
									- mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start), 0))
									/ 86400) + 1;
		
		// Hier wird auch der 'genormte Timestamp' (immer 12.00 Uhr, ohne Sommerzeit) ts berechnet.
		switch ($r_rule['rtype']) {
		
			// ts ist hier der Tag des Termins 12:00:00 Uhr
			case 'SINGLE':
				$ts = mktime(12, 0, 0, date('n', $start), date('j', $start),date('Y', $start), 0);
				$rrule = array($ts, 0, 0, '', 0, 0, 'SINGLE', $duration);
				break;
				
			case 'DAILY':
				// ts ist hier der Tag des ersten Wiederholungstermins 12:00:00 Uhr
				$ts = mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start), 0);
				if ($r_rule['count']) {
					$r_rule['expire'] = mktime(23, 59, 59, date('n', $start), date('j', $start)
							+ $r_rule['count'] * $r_rule['linterval'] + 1, date('Y', $start), 0);
				}
				if (!$r_rule['linterval'])
					$rrule = array($ts, 1, 0, '', 0, 0, 'DAILY', $duration);
				else
					$rrule = array($ts, $r_rule['linterval'], 0, '', 0, 0, 'DAILY', $duration);
				break;
				
			case 'WEEKLY':
				// ts ist hier der Montag der ersten Wiederholungswoche 12:00:00 Uhr
				$ts_start = mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start), 0);
				if (!$r_rule['linterval'] && !$r_rule['wdays']) {
					$ts = $ts_start + (604800 - (strftime('%u', $start) - 1) * 86400);
					if ($r_rule['count']) {
						$r_rule['expire'] = $ts_start  + 604800 * ($r_rule['count'] - 1);
						$r_rule['expire'] = mktime(23, 59, 59, date('n', $r_rule['expire']),
								date('j', $r_rule['expire']), date('Y', $r_rule['expire']));
					}
					$rrule = array($ts, 1, 0, strftime('%u', $start), 0, 0, 'WEEKLY', $duration);
				}
				else if (!$r_rule['wdays']) {
					$ts = $ts_start + ($r_rule['linterval'] * 604800 - (strftime('%u', $start) - 1) * 86400);
					if ($r_rule['count']) {
						$r_rule['expire'] = $ts_start  + 604800 * ($r_rule['count'] - 1) * $r_rule['linterval'];
						$r_rule['expire'] = mktime(23, 59, 59, date('n', $r_rule['expire']),
								date('j', $r_rule['expire']), date('Y', $r_rule['expire']));
					}
					$rrule = array($ts, $r_rule['linterval'], 0, strftime('%u', $start), 0, 0, 'WEEKLY', $duration);
				}
				else {
					$r_rule['count'] = 4;
					$ts = $ts_start + ($r_rule['linterval'] * 604800 - (strftime('%u', $start) - 1) * 86400);
					if ($r_rule['count']) {
						$diff = 0;
						// last week day of the recurrence set
						for ($i = 0; $i < strlen($r_rule['wdays']); $i++) {
							$wdays[] = $r_rule['wdays']{$i};
							if (intval($r_rule['wdays']{$i}) > strftime("%u", $start))
								$diff++;
						}
						
						sort($wdays, SORT_NUMERIC);
						$last_wday = $wdays[sizeof($wdays) - ($r_rule['count'] % sizeof($wdays)) - 1];
						$w = (round(($r_rule['count'] - $diff - 1) / sizeof($wdays))) * $r_rule['linterval'];
						$r_rule['expire'] = $ts_start + 604800 * $w
								+ ($last_wday - 1) * 86400;
						$r_rule['expire'] = mktime(23, 59, 59, date('n', $r_rule['expire']),
								date('j', $r_rule['expire']), date('Y', $r_rule['expire']));
					}
					$rrule = array($ts, $r_rule['linterval'], 0, $r_rule['wdays'], 0, 0, 'WEEKLY', $duration);
				}
				break;
				
			case 'MONTHLY':
				if ($r_rule['month'])
					return FALSE;
				if (!$r_rule['linterval'] && !$r_rule['day'] && !$r_rule['sinterval'] && !$r_rule['wdays']) {
					$ts = mktime(12, 0, 0, date('n', $start) + 1, date('j', $start), date('Y', $start), 0);
					$rrule = array($ts, 1, 0, '', 0, date('j', $start), 'MONTHLY', $duration);
				}
				else if (!$r_rule['day'] && !$r_rule['sinterval'] && !$r_rule['wdays']) {
					$amonth = date('n', $start) + $r_rule['linterval'];
					$ts = mktime(12, 0, 0, $amonth, date('j', $start), date('Y', $start), 0);
					$rrule = array($ts, $r_rule['linterval'], 0, '', 0, date('j', $start), 'MONTHLY', $duration);
				}
				else if (!$r_rule['sinterval'] && !$r_rule['wdays']) {
					// Ist erste Wiederholung schon im gleichen Monat?
					if($r_rule['day'] < date('j', $start))
						$amonth = date('n', $start) + $r_rule['linterval'];
					else
						$amonth = date('n', $start);
					$ts = mktime(12, 0, 0, $amonth, $r_rule['day'], date('Y', $start), 0);
					$rrule = array($ts, $r_rule['linterval'], 0, '', 0, $r_rule['day'], 'MONTHLY', $duration);
				}
				else if (!$r_rule['day']) {
					// hier ist ts der erste Wiederholungstermin
					$amonth = date('n', $start) + $r_rule['linterval'];
					$adate = mktime(12, 0, 0, $amonth, 1, date('Y', $start), 0) + ($r_rule['sinterval'] - 1) * 604800;
					$awday = strftime('%u', $adate);
					$adate -= ($awday - $r_rule['wdays']) * 86400;
					if($r_rule['sinterval'] == 5){
						if(date('j', $adate) < 10)
							$adate -= 604800;
						if(date('n', $adate) == date('n', $adate + 604800))
								$adate += 604800;
					}
					else if ($awday > $r_rule['wdays'])
						$adate += 604800;
					// Ist erste Wiederholung schon im gleichen Monat?
					if (date('j', $adate) > date('j', $start)) {
						//Dann muss hier die Berechnung ohne interval wiederholt werden
						$amonth = date('n', $start);
						$adate = mktime(12, 0, 0, $amonth, 1, date('Y', $start), 0) + ($r_rule['sinterval'] - 1) * 604800;
						$awday = strftime('%u', $adate);
						$adate -= ($awday - $r_rule['wdays']) * 86400;
						if ($r_rule['sinterval'] == 5) {
							if (date('j', $adate) < 10)
								$adate -= 604800;
							if (date('n', $adate) == date('n', $adate + 604800))
								$adate += 604800;
						}
						else if ($awday > $r_rule['wdays'])
							$adate += 604800;
					}
					$ts = $adate;
					$rrule = array($ts, $r_rule['linterval'], $r_rule['sinterval'], $r_rule['wdays'], 0, 0, 'MONTHLY',$duration);
				}
				break;
				
			case 'YEARLY':
				// ts ist hier der erste Wiederholungstermin 12:00:00 Uhr
				if (!$r_rule['month'] && !$r_rule['day'] && !$r_rule['sinterval'] && !$r_rule['wdays']) {
					$ts = mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start) + 1, 0);
					$rrule = array($ts, 1, 0, '', date('n', $start), date('j', $start), 'YEARLY', $duration);
				}
				else if (!$r_rule['sinterval'] && !$r_rule['wdays']) {
					if (!$r_rule['day'])
						$r_rule['day'] = date('j', $start);
					$ts = mktime(12, 0, 0, $r_rule['month'], $r_rule['day'], date('Y', $start), 0);
					if ($ts < mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start), 0))
						$ts = mktime(12, 0, 0, $r_rule['month'], $r_rule['day'], date('Y', $start) + 1, 0);
					$rrule = array($ts, 1, 0, '', $r_rule['month'], $r_rule['day'], 'YEARLY', $duration);
				}
				else if (!$r_rule['day']) {
					$ayear = date('Y', $start);
					if ($r_rule['month'] < date('n', $start))
						$ayear++;
					$adate = mktime(12, 0, 0, $r_rule['month'], 1, $ayear, 0) + ($r_rule['sinterval'] - 1) * 604800;
					$aday = strftime('%u', $adate);
					$adate -= ($aday - $r_rule['wdays']) * 86400;
					if ($r_rule['sinterval'] == 5) {
						if (date('j', $adate) < 10)
							$adate -= 604800;
						if (date('n', $adate) == date('n', $adate + 604800))
							$adate += 604800;
					}
					else if ($aday > $r_rule['wdays'])
						$adate += 604800;
					$ts = $adate;
					if ($ts < mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start), 0))
						$ts = mktime(12, 0, 0, $r_rule['month'], $aday, date('Y', $start) + 1, 0);
					$rrule = array($ts, 1, $r_rule['sinterval'], $r_rule['wdays'], $r_rule['month'], 0, 'YEARLY', $duration);
				}
				break;
				
			default :
				$ts = mktime(12, 0, 0, date('n', $start), date('j', $start), date('Y', $start), 0);
				$rrule = array($ts, 0, 0, '', 0, 0, 'SINGLE', $duration);
		}
		
		if (!$r_rule['rtype'] == 'SINGLE')
			$r_rule['expire'] = 0;
		elseif (!$r_rule['expire'])
			$r_rule['expire'] = 2114377200;
		
		return array(
				'ts' 				=> $rrule[0],
				'linterval' => $rrule[1],
				'sinterval' => $rrule[2],
				'wdays' 		=> $rrule[3],
				'month' 		=> $rrule[4],
				'day' 			=> $rrule[5],
				'rtype' 		=> $rrule[6],
				'duration' 	=> $rrule[7],
				'expire'    => $r_rule['expire']);
	}		
	
	function getUid ($id) {
	
		return "Stud.IP-$id@{$_SERVER['SERVER_NAME']}";
	}
	
	function getColor () {
		return "#000000";
	}
	
	function createUniqueId () {
	
		return md5(uniqid(rand() . "Stud.IP Calendar"));
	}
	
} // class Termin
