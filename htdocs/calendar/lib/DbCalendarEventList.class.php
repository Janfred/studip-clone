<?

/*
DbCalendarEventList.class.php - 0.8.20020708
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
require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/SeminarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/calendar_misc_func.inc.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/driver/$CALENDAR_DRIVER/list_driver.inc.php");

class AppList{

	var $start;      // Startzeit als Unix-Timestamp (int)
	var $end;        // Endzeit als Unix-Timestamp (int)
	var $ts;         // der "genormte" Timestamp s.o. (int)
	var $apps;       // Termine (Object[])
	var $r_id;       // range_id (String)
	var $show_pr;    // Private Termine anzeigen ? (boolean)
	var $user_id;    // User-ID aus PhpLib (String)
	
	// Konstruktor
	// bei Aufruf ohne Parameter: Termine von jetzt bis jetzt + 14 Tage
	function AppList($range_id, $show_private = FALSE, $start = -1, $end = -1, $sort = TRUE){
		global $user;
		if($start == -1)
			$start = time();
		if($end == -1)
			$end = mktime(23,59,59,date("n", $start),date("j", $start) + 7,date("Y", $start));
		
		$this->start = $start;
		$this->end = $end;
		$this->ts = mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y", $this->start),0);
		$this->r_id = $range_id;
		$this->show_pr = $show_private;
		$this->user_id = $user->id;
		$this->restore();
		if($sort)
			$this->sort();
	}
	
	// public
	function getStart(){
		return $this->start;
	}
	
	// public
	function getEnd(){
		return $this->end;
	}
	
	// Persönliche Termine und Seminartermine werden gemischt. Es muss also
	// nicht mehr nachtraeglich sortiert werden.
	// private
	function restore(){
		list_restore($this);
	}
	
	// public
	function numberOfEvents(){
		return sizeof($this->apps);
	}
	
	function existEvent(){
		return sizeof($this->apps) > 0 ? TRUE : FALSE;
	}
	
	// public
	function nextEvent(){
		if(list(,$ret) = each($this->apps));
			return $ret;
		return FALSE;
	}
	
	// public
	function bindSeminarEvents($sem_ids = ""){
		if ($sem_ids == "")
			$query = "SELECT t.*, su.status, s.Name FROM seminar_user su "
						 . "LEFT JOIN seminare s USING(Seminar_id) LEFT JOIN termine t ON "
						 . "s.Seminar_id=range_id WHERE user_id = '" . $this->user_id
						 . "' AND ((date BETWEEN " . $this->getStart() . " AND " . $this->getEnd()
						 . ") OR (end_time BETWEEN " . $this->getStart() . " AND " . $this->getEnd()
						 . "))";
		else {
			if (is_array($sem_ids))
				$sem_ids = implode("','", $sem_ids);
			$query = "SELECT t.*, su.status , s.Name FROM seminar_user su "
						 . "LEFT JOIN seminare s USING(Seminar_id) LEFT JOIN termine t ON "
						 . "s.Seminar_id=range_id WHERE user_id = '" . $this->user_id
						 . "' AND range_id IN ('$sem_ids') AND "
						 . "((date BETWEEN " . $this->getStart() . " AND " . $this->getEnd()
						 . ") OR (end_time BETWEEN " . $this->getStart() . " AND " . $this->getEnd()
						 . "))";
		}
			
		$db = new DB_Seminar;	
		$db->query($query);
		
		if($db->num_rows() != 0){
			while($db->next_record()){
				$app =& new SeminarEvent($db->f("date"), $db->f("end_time"), $db->f("content"),
				              $db->f("date_typ"), $db->f("raum"), $db->f("termin_id"), $db->f("range_id"),
											$db->f("mkdate"), $db->f("chdate"));
				$app->setDescription($db->f("description"));
				$app->setWritePermission($db->f("status") == "tutor" || $db->f("status") == "dozent");
				$app->setSemName($db->f("Name"));
				$this->apps[] = $app;
			}
			$this->sort();
			return TRUE;
		}
		return FALSE;
	}
	
	function sort(){
		if($this->apps)
			usort($this->apps,"cmp_list");
	}
	
} // class DbCalendarEventList 

?>
