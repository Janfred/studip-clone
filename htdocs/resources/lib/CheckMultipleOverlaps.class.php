<?
/**
* CheckMultipleOverlaps.class.php
* 
* checks overlaps for multiple resources, seminars and assign objects
* via the a special table
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup		resources_modules
* @module		CheckMultipleOverlaps.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CheckMultipleOverlaps.class.php
// Klasse zum checken von Ueberschneidungen von mehrere Ressourcen, Veranstaltungen und
// Belegungen
// Copyright (C) 2004 Cornelis Kater <ckater@gwdg.de>, data-quest GmbH <info@data-quest.de>
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

require_once $RELATIVE_PATH_RESOURCES."/lib/AssignEventList.class.php";

class CheckMultipleOverlaps {
	var $begin;
	var $end;
	var $db;			//db object
	var $resource_ids;		//all the resources in the actual check-set
	
	//Kontruktor
	function CheckMultipleOverlaps () {
		$this->db = new DB_Seminar;
		$this->createTable();
	}
	
	function createTable() {
		$query = "CREATE TABLE IF NOT EXISTS `resources_temporary_events` (
				`event_id` varchar(32) NOT NULL default '',
				`resource_id` varchar(32) NOT NULL default '',
				`assign_id` varchar(32) NOT NULL default '',
				`seminar_id` varchar(32) NOT NULL default '',
				`termin_id` varchar(32) NOT NULL default '',
				`begin` int(20) NOT NULL default '0',
				`end` int(20) NOT NULL default '0',
				`mkdate` int(20) NOT NULL default '0',
				PRIMARY KEY  (`event_id`),
				KEY `resource_id` (`resource_id`),
				KEY `assign_object_id` (`assign_id`),
				) TYPE=HEAP";
		$this->db->query($query);
	}
	
	function setTimeRange($begin, $end) {
		$this->begin = $begin;
		$this->end = $end;
	}

	function setAutoTimeRange($assObjs) {
		$end = 0;
		foreach ($assObjs as $obj) {
			if (!$begin)
				$begin = $obj->getBegin();
			if ($obj->getBegin() < $begin)
				$begin = $obj->getBegin();
			if ($obj->getRepeatEnd() > $end)
				$end = $obj->getRepeatEnd();
		}
		$this->setTimeRange($begin, $end);
	}

	
	function addResource($resource_id) {
		$this->resource_ids[] = $resource_id;
		$query = sprintf ("DELETE FROM resources_temporary_events WHERE resource_id = '%s'", $resource_id);
		$this->db->query($query);
		$assEvt = new AssignEventList($this->begin, $this->end, $resource_id, FALSE, FALSE, FALSE);
		while ($event = $assEvt->nextEvent()) {
			$query = sprintf ("INSERT INTO resources_temporary_events SET event_id = '%s', resource_id = '%s', assign_id = '%s', begin = '%s', end = '%s', mkdate = '%s'",
						md5(uniqid("tempo")), $resource_id, $event->getAssignId(), $event->getBegin(), $event->getEnd(), time());
			$this->db->query($query);
		}
	}
	
	function checkOverlap ($assObj, &$result, $resource = array()) {
		$events = $assObj->getEvents();
		
		foreach ($events as $obj) {
			$clauses[] = sprintf ("((begin <= %s AND end > %s) OR (begin <= %s AND end >= %s) OR (begin < %s AND end >= %s))", $obj->getBegin(), $obj->getBegin(), $obj->getBegin(), $obj->getEnd(), $obj->getEnd(), $obj->getEnd());
		}
		
		$clause = join(" OR ",$clauses);
		$in = "('".join("','",$this->resource_ids)."')";
		
		$query = sprintf ("SELECT * FROM resources_temporary_events WHERE 1 AND (%s) AND resource_id IN %s ORDER BY begin", $clause, $in);
		$this->db->query($query);
		while ($this->db->next_record()) {
			$result[$this->db->f("resource_id")][$assObj->getId()][] = array("begin"=>$this->db->f("begin"), "end"=>$this->db->f("end"), "assign_id"=>$this->db->f("assign_id"));
		}
		return;
	}
}
?>