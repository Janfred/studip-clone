<?

/*
DbCalendarMonth.class.php - 0.7.5.20020312
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
require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarYear.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarMonth.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/calendar_misc_func.inc.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/SeminarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/driver/$CALENDAR_DRIVER/month_driver.inc.php");

class DbCalendarMonth extends DbCalendarYear{

	var $month;        // Monat (Object)
	var $apps;         // Object[][]
	var $arr_pntr;     // Array-Pointer (int)
	
	// Konstruktor
	function DbCalendarMonth($tmstamp){
		$this->month = new CalendarMonth($tmstamp);
		$this->apps = array();
		DbCalendarYear::DbCalendarYear($tmstamp);
	}
	
	// public
	function getMonth(){
		return $this->month->getValue();
	}
	
	// public
	function getNameOfMonth(){
		return $this->month->toString();
	}
	
	// public
	function getStart(){
		return $this->month->getStart();
	}
	
	// public
	function getEnd(){
		return $this->month->getEnd();
	}
	
	// public
	function getTs(){
		return $this->month->getTs();
	}
	
	// public
	function sort(){
		while(list($key, $val)=each($this->apps)){
			usort($val,"cmp");
			$this->apps[$key] = $val;
		}
	}
	
	// public
	// ist im Prinzip die gleiche Methode, die auch Jahr benutzt, nur werden hier
	// zusätzlich Terminobjekte erzeugt, so dass in der Monatsansicht auf die
	// Termindaten zugegriffen werden kann
	function restore(){
		month_restore($this);
	}
	
	// public
	function nextEvent($tmstamp){
		$adate = mktime(12,0,0,date("n", $tmstamp), date("j", $tmstamp), date("Y", $tmstamp),0);
		if($this->apps["$adate"]){
			if(!isset($this->arr_pntr["$adate"]))
				$this->arr_pntr["$adate"] = 0;
			if($this->arr_pntr["$adate"] < $this->apdays["$adate"])
				return $this->apps["$adate"][$this->arr_pntr["$adate"]++];
			$this->arr_pntr["$adate"] = 0;
		}
		return FALSE;
	}
	
	// public
	function setPointer($tmstamp, $pos){
		$adate = mktime(12,0,0,date("n", $tmstamp),date("j", $tmstamp),date("Y", $tmstamp),0);
		$this->arr_pntr["$adate"] = $pos;
	}

	function bindSeminarEvents(){
		// 6 Tage zusätzlich (angezeigte Tage des vorigen und des nächsten Monats)
		$end = $this->getEnd() + 518400;
		$start = $this->getStart() - 518400;
		$db = new DB_Seminar;
		
		if(func_num_args() == 0)
			$query = sprintf("SELECT t.*, s.Name "
						 . "FROM termine t LEFT JOIN seminar_user su ON su.Seminar_id=t.range_id "
						 . "LEFT JOIN seminare s USING(Seminar_id) WHERE "
			       . "user_id = '%s' AND date BETWEEN %s AND %s"
						 , $this->user_id, $start, $end);
		else if(func_num_args() == 1 && $seminar_ids = func_get_arg(0)){
			if(is_array($seminar_ids))
				$seminar_ids = implode("','", $seminar_ids);
			$query = sprintf("SELECT t.*, s.Name "
						 . "FROM termine t LEFT JOIN seminar_user su ON su.Seminar_id=t.range_id "
						 . "LEFT JOIN seminare s USING(Seminar_id) WHERE "
			       . "user_id = '%s' AND su.Seminar_id IN ('%s') AND date BETWEEN %s AND %s"
						 , $this->user_id, $seminar_ids, $start, $end);
		}
		else
			return FALSE;
		
		$db->query($query);
		
		while($db->next_record()){
			$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),$this->year,0);
			$this->apdays["$adate"]++;
			$app =& new SeminarEvent($db->f("date"), $db->f("end_time"), $db->f("content"),
				              $db->f("date_typ"), $db->f("raum"), $db->f("termin_id"), $db->f("range_id"),
											$db->f("mkdate"), $db->f("chdate"));
			$app->setDescription($db->f("description"));
			$app->setSemName($db->f("Name"));
			$this->apps["$adate"][] = $app;
		}
	}
	
} // class DB_Month

?>
