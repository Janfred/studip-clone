<?
/**
* CalendarParserICalendar.class.php
* 
* Based on the iCalendar parser from The Horde Project
* www.horde.org
* horde/lib/iCalendar.php,v 1.19
* Copyright 2003 Mike Cochrane <mike@graftonhall.co.nz>
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
// CalendarParserICalender.class.php
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

require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/driver/$CALENDAR_DRIVER/CalendarDriver.class.php");

class CalendarSynchronizer {
	
	var $_import;
	var $_export;
	
	function CalendarSynchronizer ($import, $export) {
		
		$this->_import = $import;
		$this->_export = $export;
	}
	
	function synchronize ($compare_fields = NULL) {
		global $user;
		
		// export to extern CUA
		$ext = array();
		// events to replace in Stud.IP
		$int = array();
		
		$this->_import->importIntoObjects();
		$events = $this->_import->getObjects();
	
		// get events from database
		$db =& new CalendarDriver();
		$db->openDatabaseForReading($user->id, $start = 0, $end = 2114377200,
			'CALENDAR_EVENTS');
		
		$sentinel = '#';
		$in_to_ext = TRUE;
		array_unshift($events, $sentinel);
		
		// synchronize!
		while ($in = $db->nextObject()) {
		
			while ($ex = array_pop($events)) {
				// end of queue, return to start
				if ($ex == $sentinel) {
					if ($in_to_ext)
						$ext[] = $in;
					array_unshift($events, $sentinel);
					continue 2;
				}
				
				// no chance to do the job because there's no LAST-MODIFIED...
				if (!$ex->properties['LAST-MODIFIED']) {
					return FALSE; //throw (fatal-)error object
				}
				
				// we are lucky, because we have the same UID and LAST-MODIFIED, easy...
				if ($ex->properties['UID'] == $in->properties['UID']) {
					if ($ex->properties['LAST-MODIFIED'] < $in->properties['LAST-MODIFIED']) {
						$ext[] = $in;
					}
					if ($ex->properties['LAST-MODIFIED'] > $in->properties['LAST-MODIFIED']) {
						$ex->id = $in->id;
						$int[] = $ex;
					}
					$in_to_ext = FALSE;
				}
				// difficult and unsave, if we have no UID...
				elseif ($ex->properties['DTSTAMP'] == $in->properties['DTSTAMP']) {
					if (trim($ex->properties['SUMMARY']) == trim($in->properties['SUMMARY'])) {
						if ($ex->properties['LAST-MODIFIED'] < $in->properties['LAST-MODIFIED']) {
							$ext[] = $in;
						}
						if ($ex->properties['LAST-MODIFIED'] > $in->properties['LAST-MODIFIED']) {
							$ex->id = $in->id;
							$int[] = $ex;
						}
					}
					$in_to_ext = FALSE;
				}
				else {
					array_unshift($events, $ex);
				}
				
			}
				$in_to_ext = TRUE;
		}
		
		// delete sentinel
		array_shift($events);
		// every event left over in $events is not in Stud.IP, so import the rest
		$int = array_merge($int, $events);
		
/*		echo "<br>importieren MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n";
		echo "<pre>";
		print_r($int);
		echo "exportieren MMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMMM\n";
		print_r($ext);
		echo "</pre>";
		echo "<br><br>Durchl&auml;ufe: $d -------- ";
*/		
		// OK, work is done, import and export the events
		$db->writeObjectsIntoDatabase($int, 'REPLACE');
		$this->_export->exportFromObjects($int);
	}
	
}

?>
