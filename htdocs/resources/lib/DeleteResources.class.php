<?
/**
* DeleteResources.class.php
* 
* deletes a resources with all the linked objects (perms + assigns)
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
// DeleteResources.class.php
// Klasse zum Loeschen einer Ressource und aller Verknuepften Elemente (Belegungen, 
// Berechtigungen und untergeordnete Ressourcen)
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

class DeleteResources {
	var $db;
	var $db2;
	var $resource_id;
	
	//Konstruktor
	function DeleteResources ($recurse = TRUE) {
		global $RELATIVE_PATH_RESOURCES;

		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		$this->resource_id = $resource_id;
		$this->recurse = $recurse;
	}

	function deleteAssigns($id) {
		$query = sprintf("DELETE FROM resources_assign WHERE resource_id = '%s' ", $id);
		$this->db->query($query);			
	}

	function deletePerms($id) {
		$query = sprintf("DELETE FROM resources_user_resources WHERE resource_id = '%s' ", $id);
		$this->db->query($query);			
	}

	function deleteResource($id) {
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		//subcurse to subordinated resource-levels
		if ($this->recurse) {
			$query = sprintf("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' ", $id);
			$db->query($query);
			
			while ($db->next_record()) 
				$this->deleteResource($db->f("resource_id"));
		}

		$this->deleteAssigns($id);
		$this->deletePerms($id);
	
		$query2 = sprintf("DELETE FROM resources_objects WHERE resource_id = '%s' ", $id);
		$db2->query($query2);			
	}
}
?>