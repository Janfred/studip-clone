<?
/**
* AdminModules.class.php
* 
* administrate modules (global and local for institutes and Veranstaltungen)
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		AdminModules.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// AdminModules.class.php
// Administration fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen)
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once $ABSOLUTE_PATH_STUDIP.("functions.php");
require_once $ABSOLUTE_PATH_STUDIP.("config.inc.php");
require_once $ABSOLUTE_PATH_STUDIP.("lib/classes/Modules.class.php");

class AdminModules extends Modules {
	var $db;
	
	function AdminModule() {
		$this->db = new DB_Seminar;
	}
	
	function getModuleForumExistingItems($range_id) {
		$query = sprintf ("SELECT COUNT(topic_id) as items FROM px_topics WHERE Seminar_id = '%s' ", $range_id);

		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("items");
	}

	function moduleForumDeactivate($range_id) {
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		$query = sprintf ("SELECT topic_ic FROM px_topics WHERE Seminar_id='%s'", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$query2 = sprintf ("DELETE FROM px_topics WHERE topic_id='%s'", $db->f("topic_id"));
			$db2->query($query2);
		
			$query2 = sprintf ("UPDATE termine SET topic_id = NULL WHERE topic_id='%s'", $db->f("topic_id"));
			$db2->query($query2);
		}
	}
	
	function getModuleDocumentsExistingItems($range_id) {
		$query = sprintf ("SELECT COUNT(dokument_id) as items FROM dokumente WHERE seminar_id = '%s' ", $range_id);

		$this->db->query($query);
		$this->db->next_record();
		
		return $this->db->f("items");
	}

	function moduleDocumentsDeactivate($range_id) {
	}
	
}
