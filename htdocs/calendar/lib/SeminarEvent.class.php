<?

/*
SeminarEvent.class.php
Klassen fuer Persoenlichen Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthienel@web.de>

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

class SeminarEvent extends Event {

	var $sem_id = "";
	var $sem_write_perm = FALSE;
	var $color;
	
	function SeminarEvent ($id = "", $properties = NULL, $sem_id = "") {
	
		if ($id && !$properties) {
			$this->id = $id;
			// get event out of database...
			$this->restore();
		}
		elseif ($properties) {
			parent::Event($properties);
			$this->id = $id;
			$this->sem_id = $sem_id;
		}
	}
	
	
	/**
	* Returns the name of the category.
	*
	* @access public
	* @return String the name of the category
	*/
	function getCategoryName () {
	
		return $this->getProperty("CATEGORIES");
	}
	
	function getColor () {
		
		return $this->color;
	}
	
	function getTitle () {
		return $this->getProperty("SUMMARY");
	}
	
	// public
	function getSeminarId () {
		return $this->sem_id;
	}
	
	// public
	function getRepeat ($index = "") {
		$ts = mktime(12, 0, 0, date('n', $this->getStart()), date('j', $this->getStart()),
				date('Y', $this->getStart()), 0);
		$rep = array('ts' => $this->date, 'linterval' => 0, 'sinterval' => 0, 'wdays' => '',
				'month' => 0, 'day' => 0, 'rtype' => 'SINGLE', 'duration' => 1);
		return $index ? $rep[$index] : $rep;
	}
	
	// public
	function setSeminarId ($id) {
		$this->sem_id = $id;
	}
	
	function restore ($id = '') {
		global $user;
		
		if ($id == "")
			$id = $this->id;
		else
			$this->id = $id;
		$db =& new DB_Seminar();
		$query = "SELECT t.*, su.*, s.Seminar_id, s.Name "
						.	"FROM termine t LEFT JOIN seminar_user su ON (t.range_id=su.Seminar_id) "
						. "LEFT JOIN seminare s USING(Seminar_id) WHERE t.termin_id='{$this->id}' "
						. "AND su.user_id='{$user->id}'";
		$db->query($query);
		if ($db->num_rows() == 1 && $db->next_record()) {
			$this->setProperty('SUMMARY',       $db->f('content'));
			$this->setProperty('DTSTART',       $db->f('date'));
			$this->setProperty('DTEND',         $db->f('end_time'));
			$this->setProperty('CATEGORIES',    $TERMIN_TYP[$db->f('date_typ')]['name']);
			$this->setProperty('LOCATION',      $db->f('raum'));
			$this->setProperty('DESCRIPTION',   $db->f('description'));
			$this->setProperty('CLASS',         'PRIVATE');
			$this->setProperty('SEMNAME',       $db->f('Name'));
			$this->setProperty('UID',           $this->getUid($this->id));
			$this->setProperty('DTSTAMP',       $db->f('mkdate'));
			$this->setProperty('LAST-MODIFIED', $db->f('chdate'));
			$this->setProperty('RRULE',         $this->getRepeat());
			$this->sem_id   = $db->f('Seminar_id');
			$this->color    = $this->setcolor($db->f('gruppe'));
			if ($db->f('status') == 'tutor' || $db->f('status') == 'dozent')
				$this->setWritePermission(TRUE);
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	function getSemName () {
		return $this->properties["SEMNAME"];
	}
	
	function setSemName ($name) {
		$this->properties["SEMNAME"] = $name;
	}
	
	function getType () {
		return 1;
	}
	
	function setWritePermission ($perm) {
		$this->sem_write_perm = $perm;
	}
	
	function haveWritePermission () {
		return $this->sem_write_perm;
	}
	
	function getUid ($id) {
	
		return "Stud.IP-SEM$id@{$_SERVER['SERVER_NAME']}";
	}
	
	function setColor ($group) {
		
		// see style.css gruppe
		$color = array('#000000', '#FF0000', '#FF9933', '#FFCC66', '#99FF99', '#66CC66',
				'#6699CC', '#666699');
		
		return $color[$group] ? $color[$group] : '#000000';
	}
	
} // class SeminarEvent
