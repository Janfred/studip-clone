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

class SeminarEvent extends Event {

	var $sem_id = ""; // Veranstaltungs-ID, wenn es sich um einen Veranstaltungstermin handelt (String)
	var $sem_name = "";
	var $sem_write_perm = FALSE;
	
	function SeminarEvent () {
		switch(func_num_args()){
			// get event out of database...
			case 1:
				$id = func_get_arg(0);
				$this->restore($id);
				break;
			case 9:
				list($start, $end, $txt, $cat, $loc, $id, $sem_id, $mkdate, $chdate) = func_get_args();
				Event::Event($start, $end, $txt, $cat);
				$this->loc = $loc;
				$this->id = $id;
				$this->sem_id = $sem_id;
				$this->mkdate = $mkdate;
				$this->chdate = $chdate;
				break;
			default:
				die("Wrong parameter (".func_num_args().") count for SeminarEvent()!");
		}
	}
	
	/**
	* Returns the name of the category.
	*
	* @access public
	* @return String the name of the category
	*/
	function getCategoryName () {
		global $TERMIN_TYP;
		return $TERMIN_TYP[$this->cat]["name"];
	}
	
	function getColor () {
		global $TERMIN_TYP;
		return $TERMIN_TYP[$this->cat]["color"];
	}
	
	// public
	function getSeminarId () {
		return $this->sem_id;
	}
	
	// public
	function getRepeat ($index = "") {
		$rep = $this->start . ",,,,,,SINGLE,#";
		$rep_arr = array("ts" => $this->date, "lintervall" => "", "sintervall" => "", "wdays" => "",
				"month" => "", "day" => "", "type" => "SINGLE", "duration" => "1");
		return $index ? $rep_arr[$index] : $rep;
	}
	
	// public
	function setSeminarId ($id) {
		$this->sem_id = $id;
	}
	
	function restore ($id = "") {
		global $user;
		if($id = "")
			$id = $this->id;
		$db =& new DB_Seminar();
		$query = "SELECT termine.*, seminar_user.*, seminare.Seminar_id, Name "
						.	"FROM termine LEFT JOIN seminar_user ON (range_id=Seminar_id) "
						. "LEFT JOIN seminare USING(Seminar_id) WHERE (termin_id='$id' "
						. "AND user_id='" . $user->id . "'";
		$db->query($query);
		$db->next_record();
		$this->id = $id;
		$this->txt = $db->f("content");
		$this->start = $db->f("date");
		$this->end = $db->f("end_time");
		$this->cat = $db->f("date_typ");
		$this->loc = $db->f("raum");
		$this->desc = $db->f("description");
		$this->chdate = $db->f("chdate");
		$this->mkdate = $db->f("mkdate");
		$this->sem_id = $db->f("Seminar_id");
		$this->sem_name = $db->f("Name");
	}
	
	function getSemName () {
		return $this->sem_name;
	}
	
	function setSemName ($name) {
		$this->sem_name = $name;
	}
	
	function getType () {
		return 1;
	}
	
	/**
	* Creates and returns a copy of this object.
	*
	* @access public
	* @return Object a copy of this object
	*/
	function clone () {
		$clone =& new SeminarEvent($this->start, $this->end, $this->txt, $this->cat,
				$this->loc, $this->id, $this->sem_id, $this->mkdate, $this->chdate);
		$clone->desc = $this->des;
		$clone->sem_name = $this->sem_name;
		return $clone;
	}
	
	
	function setWritePermission ($perm) {
		$this->sem_write_perm = $perm;
	}
	
	function haveWritePermission () {
		return $this->sem_write_perm;
	}
	
} // class SeminarEvent
