<?
/**
* CalendarDriver.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	calendar_modules
* @module		calendar_sync
* @package	Calendar
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CalendarDriver.class.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
// Suchi & Berg GmbH <info@data-quest.de>
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

global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_CALENDAR;

require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/driver/MySQL/MysqlDriver.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/CalendarEvent.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/SeminarEvent.class.php");

class CalendarDriver extends MysqlDriver {
	
	var $db_sem;
	var $_sem_events;
	var $_create_sem_object;
	
	function CalendarDriver () {
		
		parent::MysqlDriver();
		$this->db_sem = NULL;
		$this->$_bind_sem_events;
		$this->_create_sem_object = FALSE;
	}
	
	function bindSeminarEvents () {
		
		
		$this->$_sem_events = TRUE;
	}
	
	function openDatabaseForReading ($range_id, $start, $end, $event_types,
			$except = NULL, $sem_id = "") {
		global $user;
				
		if ($event_types == 'ALL_EVENTS' || $event_types == 'CALENDAR_EVENTS') {
			$this->initialize('db');
			
			$query = "SELECT * FROM calendar_events WHERE range_id = '$range_id' "
					. "AND start BETWEEN $start AND $end";
			if ($exept !== NULL) {
				$except = implode("','", $except);
				$query .= " AND NOT IN '$except'";
			}
			$this->db->query($query);
		}
		if ($event_types == 'ALL_EVENTS' || $event_types == 'SEMINAR_EVENTS') {
			$this->initialize('db_sem');
			
			if ($sem_id == "")
				$query = "SELECT t.*, s.Name "
							 . "FROM termine t LEFT JOIN seminar_user su ON su.Seminar_id=t.range_id "
							 . "LEFT JOIN seminare s USING(Seminar_id) WHERE "
		      		 . "user_id = '{$user->id}' AND date_typ!=-1 AND date_typ!=-2 "
							 . "AND date BETWEEN $start AND $end";
			else if ($sem_id != "") {
				if (is_array($sem_id))
					$sem_id = implode("','", $sem_id);
				$query = "SELECT t.*, s.Name "
							 . "FROM termine t LEFT JOIN seminar_user su ON su.Seminar_id=t.range_id "
							 . "LEFT JOIN seminare s USING(Seminar_id) WHERE "
		       		 . "user_id = '%s' AND range_id IN ('$sem_id') AND date_typ!=-1 "
					 		 . "AND date_typ!=-2 AND date BETWEEN $start AND $end";
			}
			$this->db_sem->query($query);
		//	echo $query;
		}
	}
	
	function nextProperties () {

		if (is_object($this->db) && $this->db->next_record()) {
			$properties = array(
					'DTSTART'         => $this->db->f('start'),
					'DTEND'           => $this->db->f('end'),
					'SUMMARY'         => $this->db->f('summary'),
					'DESCRIPTION'     => $this->db->f('description'),
					'UID'             => $this->db->f('uid'),
					'CLASS'           => $this->db->f('class'),
					'CATEGORIES'      => $this->db->f('categories'),
					'STUDIP_CATEGORY' => $this->db->f('category_intern'),
					'PRIORITY'        => $this->db->f('priority'),
					'LOCATION'        => $this->db->f('location'),
					'RRULE'           => array(
							'rtype'       => $this->db->f('rtype'),
							'linterval'   => $this->db->f('linterval'),
							'sinterval'   => $this->db->f('sinterval'),
							'wdays'       => $this->db->f('wdays'),
							'month'       => $this->db->f('month'),
							'day'         => $this->db->f('day'),
							'expire'      => $this->db->f('expire')),
					'CREATED'         => $this->db->f('mkdate'),
					'LAST-MODIFIED'   => $this->db->f('chdate'),
					'DTSTAMP'         => time());
			
			$this->count();
			return $properties;
		}
		elseif (is_object($this->db_sem) && $this->db_sem->next_record()) {
			$this->_create_sem_object = TRUE;
			$properties = array(
					'DTSTART'         => $this->db_sem->f('date'),
					'DTEND'           => $this->db_sem->f('end_time'),
					'SUMMARY'         => $this->db_sem->f('content'),
					'DESCRIPTION'     => $this->db_sem->f('description'),
					'LOCATION'        => $this->db_sem->f('raum'),
					'STUDIP_CATEGORY' => $this->db_sem->f('date_typ'),
					'CREATED'         => $this->db_sem->f('mkdate'),
					'LAST-MODIFIED'     => $this->db_sem->f('chdate'),
					'DTSTAMP'       => time());
			
			$this->count();
			return $properties;
		}
		else
			$this->_create_sem_object = FALSE;
			
		return FALSE;
	}
	
	function &nextObject () {
		
		if ($properties = $this->nextProperties()) {
			if ($this->_create_sem_object) {
				$event =& new SeminarEvent($this->db_sem->f('termin_id'), $properties, $this->db_sem->f('range_id'));
			}
			else {
				$event =& new CalendarEvent($properties, $this->db->f('event_id'));
				$event->user_id = $this->db->f('range_id');
			}
			
			$this->count();
			return $event;
		}
			
		return FALSE;
	}
	
	function writeIntoDatabase ($properties, $mode = 'REPLACE') {
		global $user;
		
		if (!sizeof($properties))
			return FALSE;
			
		if ($mode == 'INSERT_IGNORE')
			$query = "INSERT IGNORE INTO";
		elseif ($mode ==  'INSERT')
			$query = "INSERT INTO";
		elseif ($mode == 'REPLACE')
			$query = "REPLACE";
		
		$query .= " calendar_events VALUES ";
		
		$this->initialize('db');
		
		$mult = FALSE;
		foreach ($properties as $property_set) {
		
			if ($property_set['ID'] == '')
				$id = CalendarEvent::createUniqueId();
			else
				$id = $property_set['ID'];
			
			if ($mult)
				$query .= ",\n";
			else
				$mult = TRUE;
			$query .= sprintf("('%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s',%s,%s,'%s',%s,%s,%s,
					'%s',%s,%s,'%s',%s,%s,'%s',%s,%s)",
					$id, $user->id, $user->id,
					$property_set['UID'],
					$property_set['DTSTART'],
					$property_set['DTEND'],
					addslashes($property_set['SUMMARY']),
					addslashes($property_set['DESCRIPTION']),
					$property_set['CLASS'],
					addslashes($property_set['CATEGORIES']),
					(int) $property_set['STUDIP_CATEGORY'],
					(int) $property_set['PRIORITY'],
					addslashes($property_set['LOCATION']),
					$property_set['RRULE']['ts'],
					(int) $property_set['RRULE']['linterval'],
					(int) $property_set['RRULE']['sinterval'],
					$property_set['RRULE']['wdays'],
					(int) $property_set['RRULE']['month'],
					(int) $property_set['RRULE']['day'],
					$property_set['RRULE']['rtype'],
					$property_set['RRULE']['duration'],
					$property_set['RRULE']['expire'],
					$property_set['EXCEPTIONS'],
					$property_set['CREATED'],
					$property_set['LAST-MODIFIED']);
			
			$this->count();
		}
	
	//	echo "<br>$query<br>";
		
		$this->db->query($query);
	}
	
	function writeObjectsIntoDatabase ($objects, $mode = 'REPLACE') {
		global $user;
		
		if (!sizeof($objects))
			return FALSE;
		
		if ($mode == 'INSERT_IGNORE')
			$query = "INSERT IGNORE INTO";
		elseif ($mode ==  'INSERT')
			$query = "INSERT INTO";
		elseif ($mode == 'REPLACE')
			$query = "REPLACE";
		
		$query .= " calendar_events VALUES ";/*(event_id,range_id,autor_id,uid,start,end,summary,description,"
		        . "class,categories,priority,location,ts,linterval,sinterval,wdays,"
						. "month,day,rtype,duration,expire,exceptions,mkdate,chdate) VALUES ";*/
		
		$this->initialize('db');
		
		$mult = FALSE;
		foreach ($objects as $object) {
			if ($mult)
				$query .= ",\n";
			else
				$mult = TRUE;
			$query .= sprintf("('%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s',%s,%s,'%s',%s,%s,%s,
					'%s',%s,%s,'%s',%s,%s,'%s',%s,%s)",
					$object->getId(), $user->id, $user->id,
					$object->properties['UID'],
					$object->properties['DTSTART'],
					$object->properties['DTEND'],
					addslashes($object->properties['SUMMARY']),
					addslashes($object->properties['DESCRIPTION']),
					$object->properties['CLASS'],
					addslashes($object->properties['CATEGORIES']),
					(int) $object->properties['STUDIP_CATEGORY'],
					(int) $object->properties['PRIORITY'],
					addslashes($object->properties['LOCATION']),
					$object->properties['RRULE']['ts'],
					(int) $object->properties['RRULE']['linterval'],
					(int) $object->properties['RRULE']['sinterval'],
					$object->properties['RRULE']['wdays'],
					(int) $object->properties['RRULE']['month'],
					(int) $object->properties['RRULE']['day'],
					$object->properties['RRULE']['rtype'],
					$object->properties['RRULE']['duration'],
					$object->properties['RRULE']['expire'],
					$object->properties['EXCEPTIONS'],
					$object->properties['CREATED'],
					$object->properties['LAST-MODIFIED']);
			
			$this->count();
		}
	
//		echo "<br>$query<br>";
		
		$this->db->query($query);
	}
	
}

?>
