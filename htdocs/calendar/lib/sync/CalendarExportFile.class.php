<?
/**
* CalendarExportFile.class.php
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
// CalendarExportFile.class.php
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
 
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarExport.class.php");

class CalendarExportFile extends CalendarExport {
	
	var $writer;
	var $file_name = "studip";
	var $tmp_file_name;
	var $path;
	var $file;
	
	function CalendarExportFile (&$writer, $path = "", $file_name = "") {
		global $TMP_PATH;
		
		if ($file_name == "") {
			$this->tmp_file_name = $this->makeUniqueFilename();
			$this->file_name .= "." . $writer->getDefaultFileNameSuffix();
		}
		else {
			$this->file_name = $file_name;
			$this->tmp_file_name = $file_name;
		}
		
		if ($path == "")
			$this->path = "$TMP_PATH/export/";
		
		$this->_writer = $writer;
	}
	
	function exportFromDatabase ($range_id, $start = 0, $end = 2114377200,
			$event_types = "ALL", $except = NULL) {
			
		$this->_createFile();
		parent::exportFromDatabase($range_id, $start, $end, $event_types, $except);
		$this->_closeFile();
	}
	
	function exportFromObjects (&$events) {
		
		$this->_createFile();
		parent::exportFromObjects($events);
		$this->_closeFile();
	}
	
	function sendFile () {
		global $CANONICAL_RELATIVE_PATH_STUDIP;
		
		if (file_exists($this->path . $this->tmp_file_name)) {
			header("Location: {$CANONICAL_RELATIVE_PATH_STUDIP}sendfile.php"
				. "?type=2&file_id={$this->tmp_file_name}&file_name={$this->file_name}&force_download=1");
		}
		else {
			
		}
	}
	
	function makeUniqueFileName () {
	
		return md5(uniqid(rand() . "Stud.IP Calendar"));
	}
	
	function getExport () {
		// Datei als String zurueckgeben
	}
	
	function getFileName () {
	
		return $this->file_name;
	}
	
	function getTempFileName () {
	
		return $this->tmp_file_name;
	}
	
	function _createFile () {
		if (!(is_dir($this->path))) {
			mkdir($this->path);
			chmod ($this->path, 0777);
		}
		if (file_exists($this->path . $this->tmp_file_name)) {
			unlink($this->path . $this->tmp_file_name);
		}
		$this->file = fopen($this->path . $this->tmp_file_name, "w");
	}
	
	function _export ($string) {
	
		fwrite($this->file, $string);
	}
	
	function _closeFile () {
	
		fclose($this->file);
	}
	
}
?>
