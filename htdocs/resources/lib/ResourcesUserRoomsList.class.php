<?
/**
* ResourcesUserRoomsList.class.php
* 
* container for a list of rooms a user has rights for
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		AssignEvent.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourcesUserRoomsList.class.php
// Containerklasse, die eine Liste der Raeume, auf die ein User Zugriff hat, enthaelt
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

require_once ($RELATIVE_PATH_RESOURCES."/lib/ResourceObject.class.php");


/*****************************************************************************
ResourcesUserRoomsList, creates a list for all resources for one user
/*****************************************************************************/

class ResourcesUserRoomsList {
	var $user_id;    	// userId from PhpLib (String)
	var $resources;		// the results
	var $return_objects;	// should the complete objects be returned?
	var $only_rooms;	// we can do this stuff for rooms ar for all resources
	
	// Konstruktor
	function ResourcesUserRoomsList ($user_id ='', $sort= TRUE, $return_objects = TRUE, $only_rooms = TRUE) {
	 	global $RELATIVE_PATH_RESOURCES, $user;
	 	require_once ($RELATIVE_PATH_RESOURCES."/resourcesFunc.inc.php");

		$this->user_id = $user_id;
		if (!$this->user_id)
			$this->user_id = $user->id;
		
		$this->return_objects = $return_objects;
		$this->only_rooms = $only_rooms;
		$this->category_id = $category_id;
		$this->restore();
		if($sort)
			$this->sort();
	}
	
	//public
	function setReturnObjects ($value) {
		$this->return_objects = $value;
	}
	
	//private
	function walkThread ($resource_id) {
		global $user;
		
		$db=new DB_Seminar;	
		$db2=new DB_Seminar;
		
		if ($this->only_rooms)
			$query = sprintf ("SELECT resource_id, lockable, resources_objects.name FROM resources_categories LEFT JOIN resources_objects USING (category_id) WHERE resources_categories.is_room = '1' AND resources_objects.resource_id = '%s' ", $resource_id);
		else
			$query = sprintf ("SELECT resource_id, lockable, resources_objects.name FROM resources_objects WHERE resources_objects.resource_id = '%s' ", $resource_id);		
		$db->query($query);
		$db->next_record();
		$db->f("count");
		
		if  (($db->f("resource_id")) && (((isLockPeriod(time())) && (!$db->f("lockable"))) || (!isLockPeriod(time()) || (getGlobalPerms($user->id) == "admin")))) {
			if ($this->return_objects) {
				$resource_object = new ResourceObject ($resource_id);
				$this->resources[$resource_id] = $resource_object;
			} else {
				$this->resources[$resource_id] = array("name"=>$db->f("name"), "resource_id" =>$db->f("resource_id"));
			}
		}

		//subcurse
		$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$resource_id."' ");
		while ($db2->next_record())
			$this->walkThread($db2->f("resource_id"));
	}
	
	
	// private
	function restore() {
		global $perm, $user;
		$db = new DB_Seminar;
		$db2 = new DB_Seminar;
		
		//if perm is root or resources admin, load all rooms/objects
		if (($perm->have_perm ("root")) || (getGlobalPerms($user->id) == "admin")) { //hier muss auch admin rein!! {
			if ($this->only_rooms)
				$query = sprintf ("SELECT resource_id, resources_objects.name FROM resources_categories LEFT JOIN resources_objects USING (category_id) WHERE resources_categories.is_room = '1' ");
			else
				$query = sprintf ("SELECT resource_id, resources_objects.name FROM resources_objects ");			
			$db->query($query);
			while ($db->next_record()) {
				if ($this->return_objects) {
					$resource_object = new ResourceObject ($db->f("resource_id"));
					$this->resources[$db->f("resource_id")] = $resource_object;
				} else {
					$this->resources[$db->f("resource_id")] = array("name"=>$db->f("name"), "resource_id" =>$db->f("resource_id"));
				}
			}
		//if tutor, dozent or admin, load all the rooms of all his administrable objects
		} elseif  ($perm->have_perm ("tutor")) {
			$my_objects=search_administrable_objects();
			$my_objects[$this->user_id]=TRUE;
			$my_objects["all"]=TRUE;
			$i=0;
			$clause="(";
			//load my objects
			foreach ($my_objects as $key=>$val) {
				if ($i)
					$clause.=", ";
				$clause.="'$key'";
				$i++;
			}
			$clause.=")";
			$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id IN %s ",$clause);
			$db->query($query);
			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
			$query = sprintf ("SELECT resource_id FROM resources_user_resources WHERE user_id IN %s ", $clause);
			$db->query($query);
			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
		}
		
		if (!$perm->have_perm("admin")) {
			$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $this->user_id);
			$db->query($query);

			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
			$query = sprintf ("SELECT resource_id FROM resources_user_resources WHERE user_id = '%s' ", $this->user_id);
			$db->query($query2);
			while ($db->next_record()) {
				$this->walkThread($db->f("resource_id"));
			}
		}
	}
	
	function getRooms() {
		return ($this->resources);
	}
	
	//public
	function numberOfRooms() {
		return sizeof($this->resources);
	}
	
	//public
	function roomsExist() {
		return sizeof($this->resources) > 0 ? TRUE : FALSE;
	}
	
	//public
	function next() {
		if (is_array($this->resources))
			if(list(,$ret) = each($this->resources));
				return $ret;
		return FALSE;
	}

	//public
	function reset() {
		if (is_array($this->resources))
			reset($this->resources);
	}
	
	
	function sort(){
		if ($this->resources) 
			if ($return_objects)
				usort($this->resources,"cmp_resources");
			else
				sort ($this->resources);
	}
} 