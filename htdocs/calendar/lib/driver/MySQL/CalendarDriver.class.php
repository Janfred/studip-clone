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

class CalendarDriver extends MysqlDriver {
	
	var $db_seminar;
	var $_sem_events;
	
	function CalendarDriver () {
		
		parent::MysqlDriver();
		$this->db_seminar = NULL;
		$this->$_bind_sem_events;
	}
	
	function bindSeminarEvents () {
		
		
		$this->$_sem_events = TRUE;
	}
	
	function openDatabaseForReading ($range_id, $start, $end, $event_types, $except = NULL) {
	
		if (!$this->isInitialized()) {
			if ($event_types == 'ALL_EVENTS' || $event_types == 'CALENDAR_EVENTS') {
				$query = "SELECT * FROM calendar_events WHERE range_id = '$range_id' "
						. "AND start BETWEEN $start AND $end";
				if ($exept !== NULL) {
					$except = implode("','", $except);
					$query .= " AND NOT IN '$except'";
				}
				$this->db->query($query);
			}
	//		if ($event_types == 'ALL_EVENTS' || $event_types == 'SEMINAR_EVENTS') {
				
	//		}
			$this->initialize();
		}
	}
	
	function nextProperties () {

		if ($this->db->next_record()) {
			$properties = array(
					'DTSTART'       => $this->db->f('start'),
					'DTEND'         => $this->db->f('end'),
					'SUMMARY'       => $this->db->f('summary'),
					'DESCRIPTION'   => $this->db->f('description'),
					'UID'           => $this->db->f('uid'),
					'CLASS'         => $this->db->f('class'),
					'CATEGORIES'    => $this->db->f('categories'),
					'PRIORITY'      => $this->db->f('priority'),
					'LOCATION'      => $this->db->f('location'),
					'RRULE'         => array(
							'rtype'     => $this->db->f('rtype'),
							'linterval' => $this->db->f('linterval'),
							'sinterval' => $this->db->f('sinterval'),
							'wdays'     => $this->db->f('wdays'),
							'month'     => $this->db->f('month'),
							'day'       => $this->db->f('day'),
							'expire'    => $this->db->f('expire')),
					'DTSTAMP'       => $this->db->f('mkdate'),
					'LAST-MODIFIED' => $this->db->f('chdate'));
			
			if ($properties['RRULE']['rtype'] == 'SINGLE')
				unset($properties['RRULE']);
				
			return $properties;
		}
	/*	elseif ($this->_bind_sem_events) {
			if ($this->db_seminar->next_record()) {
				$properties = array(
						'DTSTART'       => $db_seminar->f('start'),
						'DTEND'         => $db_seminar->f('end'),
						'SUMMARY'       => $db_seminar->f('summary'),
						'DESCRIPTION'   => $db_seminar->f('description'),
						'LOCATION'      => $db_seminar->f('location'),
						'DTSTAMP'       => $db_seminar->f('mkdate'),
						'LAST-MODIFIED' => $db_seminar->f('chdate'));
			}
		}*/
				
		$this->initialize();
		return FALSE;
	}
	
	function &nextObject () {
		
		if ($properties = $this->nextProperties()) {
			$event =& new CalendarEvent($properties);
			$event->id = $this->db->f('event_id');
			$event->user_id = $this->db->f('range_id');
			
			return $event;
		}
			
		return FALSE;
	}
	
	function writeIntoDatabase ($properties, $mode = 'REPLACE') {
		global $user;
		
		if (!sizeof($properties))
			return FALSE;
		
		if ($properies['ID'] == '')
			$id = CalendarEvent::createUniqueId();
		else
			$id = $properies['ID'];
			
		if ($mode == 'INSERT_IGNORE')
			$query = "INSERT IGNORE INTO";
		elseif ($mode ==  'INSERT')
			$query = "INSERT INTO";
		elseif ($mode == 'REPLACE')
			$query = "REPLACE";
		
		$query .= " calendar_events VALUES ";
		
		$mult = FALSE;
		foreach ($properties as $property_set) {
			if ($mult)
				$query .= ",\n";
			else
				$mult = TRUE;
			$query .= sprintf("('%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s',%s,'%s',%s,%s,%s,
					'%s',%s,%s,'%s',%s,%s,'%s',%s,%s)",
					$id, $user->id, $user->id,
					$property_set['UID'],
					$property_set['DTSTART'],
					$property_set['DTEND'],
					addslashes($property_set['SUMMARY']),
					addslashes($property_set['DESCRIPTION']),
					$property_set['CLASS'],
					addslashes($property_set['CATEGORIES']),
					$property_set['PRIORITY'],
					addslashes($property_set['LOCATION']),
					$property_set['RRULE']['ts'],
					$property_set['RRULE']['linterval'],
					$property_set['RRULE']['sinterval'],
					$property_set['RRULE']['wdays'],
					$property_set['RRULE']['month'],
					$property_set['RRULE']['day'],
					$property_set['RRULE']['rtype'],
					$property_set['RRULE']['duration'],
					$property_set['RRULE']['expire'],
					$property_set['EXCEPTIONS'],
					$property_set['DTSTAMP'],
					$property_set['LAST-MODIFIED']);
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
		
		$mult = FALSE;
		foreach ($objects as $object) {
			if ($mult)
				$query .= ",\n";
			else
				$mult = TRUE;
			$query .= sprintf("('%s','%s','%s','%s',%s,%s,'%s','%s','%s','%s',%s,'%s',%s,%s,%s,
					'%s',%s,%s,'%s',%s,%s,'%s',%s,%s)",
					$object->getId(), $user->id, $user->id,
					$object->properties['UID'],
					$object->properties['DTSTART'],
					$object->properties['DTEND'],
					addslashes($object->properties['SUMMARY']),
					addslashes($object->properties['DESCRIPTION']),
					$object->properties['CLASS'],
					addslashes($object->properties['CATEGORIES']),
					$object->properties['PRIORITY'],
					addslashes($object->properties['LOCATION']),
					$object->properties['RRULE']['ts'],
					$object->properties['RRULE']['linterval'],
					$object->properties['RRULE']['sinterval'],
					$object->properties['RRULE']['wdays'],
					$object->properties['RRULE']['month'],
					$object->properties['RRULE']['day'],
					$object->properties['RRULE']['rtype'],
					$object->properties['RRULE']['duration'],
					$object->properties['RRULE']['expire'],
					$object->properties['EXCEPTIONS'],
					$object->properties['DTSTAMP'],
					$object->properties['LAST-MODIFIED']);
		}
	
//		echo "<br>$query<br>";
		
		$this->db->query($query);
	}
	
}

?>
