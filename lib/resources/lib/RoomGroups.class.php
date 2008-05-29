<?
# Lifter002: TODO
/**
* RoomGroups.class.php
* 
* class for a grouping of rooms
* 
*
* @author		André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		 RoomGroups.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// RoomGroups.class.php
// 
// Copyright (C) 2005 André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once $GLOBALS['RELATIVE_PATH_RESOURCES'] . "/lib/ResourceObject.class.php";
require_once $GLOBALS['RELATIVE_PATH_RESOURCES'] . "/lib/ResourcesUserRoomsList.class.php";
require_once "lib/classes/DbSnapshot.class.php";


class RoomGroups {
	
	var $room_groups = array();
	
	function &GetInstance($refresh_cache = false){
		
		static $room_group_object;
		
		if ($refresh_cache){
			$room_group_object = null;
		}
		if (is_object($room_group_object)){
			return $room_group_object;
		} else {
			$room_group_object = new RoomGroups();
			return $room_group_object;
		}
	}
	
	function RoomGroups(){
		$this->createConfigGroups();
		if (get_config('RESOURCES_ENABLE_VIRTUAL_ROOM_GROUPS')){
			$this->createVirtualGroups();
		}
	}
	
	function createConfigGroups(){
		@include "config_room_groups.inc.php";
		if (is_array($room_groups)){
			$room_list = new ResourcesUserRoomsList($GLOBALS['user']->id, false, false, true);
			if ($room_list->numberOfRooms()){
				$my_rooms = array_keys($room_list->getRooms());
				foreach ($room_groups as $key => $value){
					$rooms = array_intersect($value['rooms'], $my_rooms);
					if (count($rooms)){
						$this->room_groups[] = array('name' => $value['name'], 'rooms' => $rooms);
					}
				}
			}
		}
	}
	
	function createVirtualGroups(){
		$room_list = new ResourcesUserRoomsList($GLOBALS['user']->id, false, false, true);
		$res_obj =& ResourceObject::Factory();
		$offset = count($this->room_groups);
		if ($room_list->numberOfRooms()){
			$snap =& new DbSnapshot(new DB_Seminar("SELECT resource_id, parent_id 
													FROM resources_objects 
													WHERE resource_id IN('"
													. join("','", array_keys($room_list->getRooms()))."')"));
			foreach($snap->getGroupedResult('parent_id') as $parent_id => $rooms){
				if (is_array($rooms['resource_id'])){
					$res_obj->restore($parent_id);
					$this->room_groups[$offset]['name'] = $res_obj->getPathToString(true);
					foreach (array_keys($rooms['resource_id']) as $room_id){
						$res_obj->restore($room_id);
						$this->room_groups[$offset]['rooms'][] = $room_id;  
					}
					++$offset;
				}
			}
		}
	}
	
	function getGroupName($id){
		return (isset($this->room_groups[$id]) ? $this->room_groups[$id]['name'] : false);
	}
	
	function getGroupCount($id){
		return (is_array($this->room_groups[$id]['rooms']) ? count($this->room_groups[$id]['rooms']) : 0);
	}
}

//test
/*
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "user" => "Seminar_user" , "perm" => "Seminar_Perm"));
echo "<pre>";
$test = new RoomGroups();
print_r($test->room_groups);
*/
?>
