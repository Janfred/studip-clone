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

require_once($ABSOLUTE_PATH_STUDIP . "config.inc.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/Event.class.php");

class CalendarEvent extends Event {

	var $rep;         // Wiederholungsanweisung des Termins (String).
	var $exp = 2114377200;         // Wann verliert Termin Gueltigkeit? Unix-Timestamp (int)
	var $col = "";    // Farbe (STRING)
	var $dev = FALSE; // TRUE wenn Tagestermin (boolean)
	var $ts;          // der "genormte" Timestamp
	var $prio;        // Prioritaet (int)
	var $user_id;     // User-ID aus PphLib (String)
	var $type = -2;   // Termintyp (int) -2 privat, -1 öffentlich,
	                  // 1 Veranstaltungstermin (privater Termin als Standard)
	var $sem_id = ""; // Veranstaltungs-ID, wenn es sich um einen Veranstaltungstermin handelt (String)
	var $chdate;
	var $mkdate;
	
	// Konstruktor
	function CalendarEvent ($start = "", $end = "", $title = "", $repeat = "", $expire = "", $category = "",
												 $priority = 1, $location = "", $id = "", $type = -2) {
		global $user, $PERS_TERMIN_KAT, $TERMIN_TYP;
		$this->user_id = $user->id;
		if(func_num_args() == 10){
			$this->id = $id;
			$this->start = $start;
			$this->end = $end;
			$this->txt = $title;
			$this->rep = $repeat;
			$this->exp = $expire;
			$this->cat = $category;
			$this->prio = $priority;
			$this->loc = $location;
			$this->type = $type;
			$this->chng_flag = FALSE;
		}
		else if(func_num_args() != 1){
			if(empty($id))
				$id = md5(uniqid("Studip_Calendar"));
			$this->id = $id;
			$this->start = $start;
			$this->setTitle($title);
			$this->setEnd($end);
			$this->setExpire($expire);
			$this->setRepeat("SINGLE");
			$this->prio = $priority;
			$this->setLocation($location);
			if(empty($type))
				// privater Termin als Standard
				$type = -2; 
			$this->type = $type;
			
			// handelt es sich um einen Veranstaltungs-Termin ist die Kategorie gleich dem Typ
			if($this->type == -1 || $this->type == -2)
				$this->cat = $category;
			else if($TERMIN_TYP[$this->type])
				$this->cat = $this->type;
				
			$this->chng_flag = TRUE;
		}
		// nur persoenliche Termin haben per default eine Farbe
		// fuer Veranstaltungstermine muss eine Farbe explizit mit setColor() gesetzt werden
		if($this->type == -1 || $this->type == -2)
			$this->col = $PERS_TERMIN_KAT[$this->cat]["color"];
			
		$this->mkdate = time();
		$this->chndate = $this->mkdate;
	}
	
	// public
	function getExpire () {
		return $this->exp;
	}
	
	/**
	* Returns the name of the category.
	*
	* @access public
	* @return String the name of the category
	*/
	function getCategoryName () {
		global $PERS_TERMIN_KAT;
			return _($PERS_TERMIN_KAT[$this->cat]["name"]);
	}
	
	function isDayEvent () {
		return $this->dev;
	}
	
	function setDayEvent ($is_dev) {
		$this->dev = $is_dev;
	}
	
	// public
	function getTs () {
		$repeat_data = explode(",", $this->rep);
		return $repeat_data[0];
	}
	
	// public
	function getRepeat ($index = "") {
		if($this->rep != ""){
			list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
			     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $this->rep);
			if($rep["duration"] == "#")
				$rep["duration"] = 1;
			return $index ? $rep[$index] : $rep;
		}
		return FALSE;
	}
	
	// public
	function getColor () {
		if($this->col == "")
			return FALSE;
		return $this->col;
	}
	
	// public
	function getType () {
		return $this->type;
	}
	
	// public
	function getSeminarId () {
		if($this->type == 1)
			return $this->sem_id;
		return FALSE;
	}
	
	// public
	function setSeminarId ($id) {
		if($this->type == 1){
			$this->sem_id = $id;
			return TRUE;
		}
		return FALSE;
	}
	
	// public
	function setType ($type) {
		// nur fuer private Termine
		if($type == -2 || $type == -1){
			$this->type = $type;
			$this->chng_flag = TRUE;
		}
	}
	
	// public
	function getPriority () {
		return $this->prio;
	}
	
	function setPriority ($priority) {
		if($priority < 6 && $priority > 0){
			$this->prio = $priority;
			$this->chng_flag = TRUE;
		}
	}
	
	/**
	* Sets the repetition of this event
	*
	* This is a very flexible function ;-) For every kind of repetition there is
	* a different number of parameters.<br>The possible repetitions are:<br><br>
	* SINGLE, DAYLY, WEEKLY, MONTHLY, YEARLY<br><br>
	*
	*/
	function setRepeat () {
		$num = func_num_args();
		$type = func_get_arg(0);
		$duration = (int) ((mktime(12,0,0,date("n",$this->end),date("j",$this->end),date("Y",$this->end),0)
									- mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0))
									/ 86400) + 1;
		if($duration == 1)
			$duration = "#";
		// Hier wird auch der "genormte Timestamp" ts berechnet.
		switch($type){
			// ts ist hier der Tag des Termins 12:00:00 Uhr
			case "SINGLE":
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0);
				$this->rep = sprintf("%s,,,,,,SINGLE,%s", $this->ts, $duration);
				break;
			case "DAYLY":
				// ts ist hier der Tag des ersten Wiederholungstermins 12:00:00 Uhr
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0);
				if($num == 1)
					$this->rep = sprintf("%s,1,,,,,DAYLY,%s", $this->ts, $duration);
				elseif($num == 2)
					$this->rep = $this->ts.",".func_get_arg(1).",,,,,DAYLY,$duration";
				break;
			case "WEEKLY":
				// ts ist hier der Montag der ersten Wiederholungswoche 12:00:00 Uhr
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j", $this->start),date("Y",$this->start),0);
				switch($num){
					case 1:
						$this->ts += 604800 - (strftime("%u", $this->start) - 1) * 86400;
						$this->rep = sprintf("%s,1,,%s,,,WEEKLY,%s", $this->ts, strftime("%u", $this->start), $duration);
						break;
					case 2:
						$this->ts += func_get_arg(1) * 604800 - (strftime("%u", $this->start) - 1) * 86400;
						$this->rep = $this->ts.",".func_get_arg(1).",,".strftime("%u", $this->start).",,,WEEKLY,$duration";
						break;
					case 3:
						$this->ts += func_get_arg(1) * 604800 - (strftime("%u", $this->start) - 1) * 86400;
						$this->rep = $this->ts.",".func_get_arg(1).",,".func_get_arg(2).",,,WEEKLY,$duration";
						break;
				}
				break;
			case "MONTHLY":
				switch($num){
					case 1:
						$this->ts = mktime(12,0,0,date("n",$this->start) + 1,date("j",$this->start),date("Y",$this->start),0);
						$this->rep = sprintf("%s,1,,,,%s,MONTHLY,%s", $this->ts, date("j", $this->start), $duration);
						break;
					case 2:
						$amonth = date("n",$this->start) + func_get_arg(1);
						$this->ts = mktime(12,0,0,$amonth,date("j",$this->start),date("Y",$this->start),0);
						$this->rep = $this->ts.",".func_get_arg(1).",,,,".date("j", $this->start).",MONTHLY,$duration";
						break;
					case 3:
						$aday = func_get_arg(2);
						// Ist erste Wiederholung schon im gleichen Monat?
						if($aday < date("j", $this->start))
							$amonth = date("n",$this->start) + func_get_arg(1);
						else
							$amonth = date("n",$this->start);
						$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start),0);
						$this->rep = $this->ts.",".func_get_arg(1).",,,,".func_get_arg(2).",MONTHLY,$duration";
						break;
					case 4:
						// hier ist ts der erste Wiederholungstermin
						$amonth = date("n",$this->start) + func_get_arg(1);
						$adate = mktime(12,0,0,$amonth,1,date("Y",$this->start),0) + (func_get_arg(2) - 1) * 604800;
						$awday = strftime("%u",$adate);
						$adate -= ($awday - func_get_arg(3)) * 86400;
						if(func_get_arg(2) == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
						}
						else
							if($awday > func_get_arg(3))
								$adate += 604800;
						// Ist erste Wiederholung schon im gleichen Monat?
						if(date("j", $adate) > date("j", $this->start)){
							//Dann muss hier die Berechnung ohne Intervall wiederholt werden
							$amonth = date("n",$this->start);
							$adate = mktime(12,0,0,$amonth,1,date("Y",$this->start),0) + (func_get_arg(2) - 1) * 604800;
							$awday = strftime("%u",$adate);
							$adate -= ($awday - func_get_arg(3)) * 86400;
							if(func_get_arg(2) == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else
								if($awday > func_get_arg(3))
									$adate += 604800;
						}
						$this->ts = $adate;
						$this->rep = $this->ts.",".func_get_arg(1).",".func_get_arg(2).",".func_get_arg(3).",,,MONTHLY,$duration";
						break;
				}
				break;
			case "YEARLY":
				// ts ist hier der erste Wiederholungstermin 12:00:00 Uhr
				switch($num){
					case 1:
						$this->ts = mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y",$this->start) + 1,0);
						$this->rep = sprintf("%s,1,,,%s,%s,YEARLY,%s", $this->ts, date("n", $this->start), date("j", $this->start), $duration);
						break;
					case 3:
						$amonth = func_get_arg(1);
						$aday = func_get_arg(2);
						$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start),0);
						if($this->ts < mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y",$this->start),0))
							$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start) + 1,0);
						$this->rep = $this->ts.",1,,,".func_get_arg(1).",".func_get_arg(2).",YEARLY,$duration";
						break;
					case 4:
						$amonth = func_get_arg(3);
						$ayear = date("Y", $this->start);
						if($amonth < date("n", $this->start))
							$ayear++;
						$adate = mktime(12,0,0,$amonth,1,$ayear,0) + (func_get_arg(1) - 1) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - func_get_arg(2)) * 86400;
						if(func_get_arg(1) == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
								$adate += 604800;
						}
						else
							if($aday > func_get_arg(2))
								$adate += 604800;
						$this->ts = $adate;
						if($this->ts < mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y",$this->start),0))
							$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start) + 1,0);
						$this->rep = $this->ts.",1,".func_get_arg(1).",".func_get_arg(2).",".func_get_arg(3).",,YEARLY,$duration";
						break;
				}
				break;
			default :
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0);
				$this->rep = sprintf("%s,,,,,,SINGLE,%s", $this->ts, $duration);
		}
		$this->chng_flag = TRUE;
	}
	
	/**
	* Sets the date of expiry
	*
	* Without parameter the date of expiry is 01/01/2037 00:00:00
	*
	* @access public
	* @param int $exp a valid unix timestamp
	*/
	function setExpire ($exp = "") {
		if($exp == ""){
			$this->exp = 2114377200; //01.01.2037 00:00:00 Uhr
			$this->chng_flag = TRUE;
		}
		else if($exp < $this->end)
			return FALSE;
		else{
			$this->exp = $exp;
			$this->chng_flag = TRUE;
			return TRUE;
		}
	}
		
	// public
	function setColor ($col) {
		$this->col = $col;
		$this->chng_flag = TRUE;
	}
	
	/**
	* Creates and returns a copy of this object.
	*
	* @access public
	*/
	function clone () {
		$cloned = new CalendarEvent($this->start, $this->end, $this->txt, $this->rep,
		                     $this->exp, $this->cat, $this->prio, $this->loc, $this->id, $this->type);
		if(!is_int($this->descr))
			$cloned->setDescription($this->desc);
		$cloned->setColor($this->col);
		return $cloned;
	}
	
	/**
	* Sets the unix timestamp of the last change
	*
	* Access to this method is useful only from the container classes
	* DbCalendarEventList, DbCalendarDay, DbCalendarMonth. Normally every
	* modification of this object sets this value automatically.
	* Nevertheless it is a public function.
	*
	* @access public
	* @param int $timestamp a valid unix timestamp
	*/
	function setChangeDate ($timestamp = "") {
		if($timestamp == "")
			$this->chdate = time();
		else
			$this->chdate = $timestamp;
		if($this->mkdate > $this->chdate)
			$this->chdate = $this->mkdate;
	}
	
	/**
	* Sets the unix timestamp of the creation date
	*
	* Access to this method is useful only from the container classes
	* DbCalendarEventList, DbCalendarDay, DbCalendarMonth. Normally the
	* constructor sets this timestamp.
	*
	* @access public
	* @param int $timestamp a valid unix timestamp
	*/
	function setMakeDate ($timestamp = "") {
		if($timestamp == "")
			$this->mkdate = time();
		else
			$this->mkdate = $timestamp;
		if($this->mkdate > $this->chdate)
			$this->chdate = $this->mkdate;
	}
	
	
} // class Termin
