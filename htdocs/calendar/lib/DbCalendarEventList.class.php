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
	function numberOfApps(){
		return sizeof($this->apps);
	}
	
	function existTermin(){
		return sizeof($this->apps) > 0 ? TRUE : FALSE;
	}
	
	// public
	function nextTermin(){
		if(list(,$ret) = each($this->apps));
			return $ret;
		return FALSE;
	}
	
	function sort(){
		if($this->apps)
			usort($this->apps,"cmp_list");
	}
	
} // class AppList 

?>
