<?
/**
* ResourcesAssign.class.php
* 
* updates the to the object (range_id)  linked resources
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>
* @version		$Id$
* @access		public
* @package		resources
* @modulegroup	resources_modules
* @module		VeranstaltungResourcesAssign.class.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesAssign.class.php
// Klasse zum Bearbeiten aller verknuepften Ressourcen mit einem Objekt
// (Nutzer, Veranstaltung oder Einrichtung)
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/DeleteResources.class.php");

class ResourcesAssign extends DeleteResources {
	var $db;
	var $db2;
	var $range_id;
	
	//Konstruktor
	function ResourcesAssign ($range_id, $recurse = TRUE) {
		global $RELATIVE_PATH_RESOURCES;

		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		$this->range_id = $range_id;
		
		//execute constructor from DeleteResources
		$this->DeleteResources($recurse);
	}
	
	//private
	function deleteForeignAssigns() {
		$query = sprintf("DELETE FROM resources_assign WHERE assign_user_id = '%s' ", $this->range_id);
		$this->db->query($query);			
	}

	//private
	function deleteForeignPerms() {
		$query = sprintf("DELETE FROM resources_user_resources WHERE user_id = '%s' ", $this->range_id);
		$this->db->query($query);			
	}

	//private
	function deleteOwnerResources() {
		$query = sprintf("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $this->range_id);
		while ($this->db->next_record()) {
			$this->deleteResource($this->db->f("resource_id"));
		}
	}
	
	function delete() {
		$this->deleteForeignAssigns();
		$this->deleteForeignPerms();
		$this->deleteOwnerResources();		
	}
}
?>