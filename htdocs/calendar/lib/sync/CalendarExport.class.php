<?
/**
* CalendarExport.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	calendar_modules
* @module		calendar_import
* @package	Calendar
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CalendarExport.class.php
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

global $ABSOLUTE_PATH_STUDIP, $RELATIVE_PATH_CALENDAR, $CALENDAR_DRIVER;

require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/driver/$CALENDAR_DRIVER/CalendarDriver.class.php");
 
class CalendarExport {
	
	var $_writer;
	var $export;

	function CalendarExport (&$writer) {
		
		$this->_writer = $writer;
	}
	
	function exportFromDatabase ($range_id, $start = 0, $end = 2114377200,
			$event_types = 'CALENDAR_EVENTS', $except = NULL) {
		
		$export_driver =& new CalendarDriver();
		$export_driver->openDatabaseForReading($range_id, $start, $end, $event_types, $except);
		
		$this->_export($this->_writer->writeHeader());
		
		while ($properties = $export_driver->nextProperties()) {
			$this->_export($this->_writer->write($properties));
		}
		
		$this->_export($this->_writer->writeFooter());
	}
	
	function exportFromObjects ($events) {
		
		$this->_export($this->_writer->writeHeader());
		
		foreach ($events as $event) {
			$properties = $event->getProperty();
			
			if ($properties["RRULE"]["rtype"] != "SINGLE")
				$properties["RRULE"]["expire"] = $properties["EXPIRE"];
			else
				unset($properties["RRULE"]);
			unset($properties["EXPIRE"]);
			
			$properties["DTSTAMP"] = $event->getMakeDate();
			$properties["LAST-MODIFIED"] = $event->getChangeDate();
			$properties["UID"] = CalendarEvent::getUid($event->getId());
			
			$this->_export($this->_writer->write($properties));
		}
		
		$this->_export($this->_writer->writeFooter());
	}
	
	function getExport () {
		
		return $this->export;
	}
	
	function _export ($string) {
		
		$this->export .= $string;
	}
	
}

///////////////////////////////////////////////////////////////////////////////////////
// debugging
///////////////////////////////////////////////////////////////////////////////////////
/*
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarWriterICalendar.class.php");
global $user;
$export = new CalendarExport(new CalendarWriterICalendar());
$export->exportFromDatabase($user->id);
echo "<pre>" . $export->export . "</pre>";
page_close();
*/
?>
