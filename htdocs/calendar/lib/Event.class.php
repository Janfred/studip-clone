<?

/*
Event.class.php - 0.8.20020409a
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

class Event {

	var $id;    	    // termin_id (String)
	var $txt;         // Terminkurzbeschreibung (String)
	var $start = "";  // Anfangszeit des Termins als Unix-Timestamp (int)
	var $end = "";    // Endzeit des des Termins als Unix-Timestamp (int)
	var $cat;         // Kategorie (int)
	var $loc;         // Ort (String)
	var $desc = null;         // Terminbeschreibung (String)
	var $chng_flag = FALSE;   // Termin geaendert ? (boolean)
	var $chdate;
	var $mkdate;

	function Event ($start, $end, $title, $category = 1, $description = null,
									$location = "") {
		$this->start = $start;
		if($this->start >= $end){
			unset($this);
			return FALSE;
		}
		$this->end = $end;
		$this->txt = $title;
		if(!$this->setCategory($category)){
			unset($this);
			return FALSE;
		}
		$this->desc = $description;
		$this->loc = $location;
		$this->chng_flag = FALSE;
		$this->mkdate = time();
		$this->chdate = $this->mkdate;
		$chng_flag = FALSE;
		return TRUE;
	}
	
	/**
	* Returns the title of this event.
	*
	* @access public
	* @return String the title of this event
	*/
	function getTitle () {
		if ($this->txt == "")
			return _("Keine Titel");
		
		return $this->txt;
	}
	
	/**
	* Returns the starttime of this event.
	*
	* @access public
	* @return int the starttime of this event as a unix timestamp
	*/
	function getStart () {
		return $this->start;
	}
	
	/**
	* Returns the endtime of this event.
	*
	* @access public
	* @return int the endtime of this event as a unix timestamp
	*/
	function getEnd () {
		return $this->end;
	}
	
		// public
	function setId ($id) {
		$this->id = $id;
		$this->chng_flag = TRUE;
	}
	
	// public
	function getId () {
		return $this->id;
	}
	
	/**
	* Returns the integer representation of the category.
	*
	* @access public
	* @return int the integer representation of a category
	*/
	function getCategory () {
		return $this->cat;
	}
	
	/**
	* Returns the description.
	*
	* If the description is not set it returns FALSE.
	*
	* @access public
	* @return String the description
	*/
	function getDescription () {
		if($this->desc == null)
			return FALSE;
		return $this->desc;
	}
	
	/**
	* Returns the unix timestamp of the last change
	*
	* @access public
	*/
	function getChangeDate () {
		return $this->chdate;
	}
	
	/**
	* Returns the duration of this event in seconds
	*
	* @access public
	* @return int the duration of this event in seconds
	*/
	function getDuration () {
		return $this->end - $this->start -
			((date("I", $this->start) - date("I", $this->end)) * 3600);
	}
	
	/**
	* Returns the location. If the location is not set, it returns FALSE.
	*
	* @access public
	* @return String the location
	*/
	function getLocation () {
		if($this->loc == "")
			return FALSE;
		return $this->loc;
	}
	
	/**
	* Returns the unix timestamp of creating
	*
	* @access public
	*/
	function getMakeDate () {
		return $this->mkdate;
	}
	
	/**
	* Returns TRUE if this event has been modified after creation
	*
	* @access public
	* @return boolean
	*/
	function isModified () {
		return $this->chng_flag;
	}
	
	/**
	* Returns this object in a serialized form (String)
	*
	* To unserialize use the PHP function unserialize().
	*
	* @access public
	* @return String this event Object in a serialized form
	*/
	function serialize () {
		return serialize($this);
	}
	
	/**
	* Changes the description.
	*
	* After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param String $description the description
	*/
	function setDescription ($description) {
		$this->desc = $description;
		$this->chng_flag = TRUE;
	}
	
	/**
	* Changes the location.
	*
	*	After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param String $location the location
	*/
	function setLocation ($location) {
		$this->loc = $location;
		$this->chng_flag = TRUE;
	}
	
	/**
	* Changes the starttime of this event.
	*
	*	After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param int $start a valid unix timestamp
	*/ 
	function setStart ($start) {
		if($this->end != "" && $this->end < $start)
			return FALSE;
		$this->start = $start;
		$this->chng_flag = TRUE;
		return TRUE;
	}
	
	/**
	* Changes the endtime of this event.
	*
	*	After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param int $end a valid unix timestamp
	*/
	function setEnd ($end) {
		if($this->start != "" && $this->start > $end)
			return FALSE;
		$this->end = $end;
		$this->chng_flag = TRUE;
		return TRUE;
	}
	
	/**
	* Changes the category of this event.
	*
	* See config.inc.php for further information and possible values.<br>
	* <br>
	* After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param int $category a valid integer representation of a category (see 
	* config.inc.php)
	* @return boolean TRUE if the value of $category is valid, otherwise FALSE
	*/
	function setCategory ($category) {
		global $PERS_TERMIN_KAT;
		if(is_array($PERS_TERMIN_KAT[$category])){
			$this->cat = $category;
			$this->chng_flag = TRUE;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	* Changes the title of this event.
	*
	* If no title is set it returns "Kein Titel".<br>
	* <br>
	* After calling this method, the method isModified() returns TRUE.
	*
	* @access public
	* @param String $title title of this event
	*/
	function setTitle ($title = "") {
		$this->txt = $title;
		$this->chng_flag = TRUE;
	}
	
	function isDayEvent () {
		return FALSE;
	}
	
}
	
