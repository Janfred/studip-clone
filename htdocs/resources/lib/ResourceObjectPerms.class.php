<?
/**
* ResourceObjectPerms.class.php
* 
* perm-class for a resource-object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		ResourceObjectPerms.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ResourceObjectPerms.class.php
// Rechteklasse die Rechte fuer ein Ressourcem-Objekt zur Verfuegung stellt
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
ResourceObjectPerms, stellt Perms zum Ressourcen Object zur 
Verfuegung
/*****************************************************************************/

class ResourceObjectPerms {
	var $user_id;
	var $db;
	var $db2;
	var $resource_id;
	var $perm_weight= array("admin" => 4, "tutor" => 2, "autor" => 1);

	
	function ResourceObjectPerms ($resource_id, $user_id='') {
		global $user, $perm;
		
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
		if ($user_id)
			$this->user_id=$user_id;
		else
			$this->user_id=$user->id;
		
		$this->resource_id=$resource_id;
		
		$resObject= new ResourceObject($this->resource_id);
		$is_room = $resObject->isRoom();
		
		if ($is_room)
			$inheritance = get_config("RESOURCES_INHERITANCE_PERMS_ROOMS");
		else
			$inheritance = get_config("RESOURCES_INHERITANCE_PERMS");
		
		//check if user is root
		if ($perm->have_perm("root")) {
			$this->changePerm("admin");
		} else //check if resources admin
			if (getGlobalPerms($this->user_id) == "admin")
				$this->perm="admin";
		
		//check, if the resource is locked at the moment (only rooms!)
		if (($this->perm != "admin") && ($resObject->isLocked())) {
			$this->perm = FALSE;
			return;
		}
		
		//check if the user is owner of the object
		if ($this->perm != "admin") {			
			$this->db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$this->user_id' AND resource_id = '$this->resource_id' ");
			if ($this->db->next_record()) {
				$this->owner=TRUE;
				$this->changePerm("admin");
			} else {
				$this->owner=FALSE;
			}
		}
		
		//else check all the other possibilities
		if ($this->perm != "admin") {
			$my_administrable_objects=search_administrable_objects();	//the administrative ones....
			$my_objects=search_my_objects();				//...and the other, where the user is autor.
			$my_objects["all"]=TRUE;
			$my_objects = array_merge($my_administrable_objects, $my_objects);
			//check if one of my administrable (system) objects owner of the resourcen object, so that I am too...
			foreach ($my_objects as $key=>$val) {
				$this->db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$key' AND resource_id = '$this->resource_id' ");
				if ($this->db->next_record())
					if ($val["perms"] == "admin")
						$this->changePerm("admin");
					else {
						switch ($inheritance) {
							case "1":
								$this->changePerm($val["perms"]);
								if ($this->perm == "dozent")
									$this->changePerm("tutor");
							break;
							default:
							case "2":
								$this->changePerm("autor");
							break;
						}
					}
				
				if ($this->perm == "admin")
					break;
					
				//also check the additional perms...
				$this->db->query("SELECT perms FROM resources_user_resources  WHERE user_id='$key' AND resource_id = '$this->resource_id' ");
				while ($this->db->next_record())
					$this->changePerm($this->db->f("perms"));
				if ($this->perm == "admin")
					break;

			}
		}

		//if all the checks don't work, we have to take a look to the superordinated objects
		if ($this->perm != "admin") {
			foreach ($my_objects as $key=>$val) {
				$query = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $this->resource_id);
				$this->db->query($query);	
				$this->db->next_record();
	
				$superordinated_id=$this->db->f("parent_id");
				$top=FALSE;

				while ((!$top) && ($k<10000) && ($superordinated_id)) {
					$this->db2->query("SELECT owner_id, resource_id FROM resources_objects WHERE owner_id='$key' AND resource_id = '$superordinated_id' ");
					if ($this->db2->next_record()) {
						if ($val["perms"] == "admin")
							$this->changePerm("admin");
						else {
							switch ($inheritance) {
								case "1":
									$this->changePerm($val["perms"]);
									if ($this->perm == "dozent")
										$this->changePerm("tutor");
								break;
								default:
								case "2":
									$this->changePerm("autor");
								break;
							}
						}
					}
					$k++;
					if ($this->perm == "admin")
						break;

					//also check the additional perms...
					$this->db2->query("SELECT perms FROM resources_user_resources  WHERE user_id='$key' AND resource_id = '$superordinated_id' ");
					while ($this->db2->next_record())
						$this->changePerm($this->db2->f("perms"));
					if ($this->perm == "admin")
						break;

					//select the next superordinated object
					$query = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $superordinated_id);
					$this->db->query($query);						
					$this->db->next_record();
		
					$superordinated_id=$this->db->f("parent_id");
					if ($this->db->f("parent_id") == "0")
						$top = TRUE;
				}

				if ($this->perm == "admin")
					break;
				
			}
		}
	}
	
	//private
	function changePerm($new_perm) {
		if ($this->perm_weight[$new_perm] > $this->perm_weight[$this->perm])
			$this->perm = $new_perm;
	}
	
	function havePerm ($perm) {
		if ($perm == "admin") {
			if ($this->getUserPerm () == "admin")
				return TRUE;
		} elseif ($perm == "autor") {
			if (($this->getUserPerm () == "admin") || ($this->getUserPerm () == "autor") || ($this->getUserPerm () == "tutor"))
				return TRUE;
		} elseif ($perm == "tutor") {
			if (($this->getUserPerm () == "admin") || ($this->getUserPerm () == "tutor"))
				return TRUE;
		} else
			return FALSE;
	}

	function getUserPerm () {
		return $this->perm;
	}
	
	function getUserIsOwner () {
		return $this->owner;
	}

	function getId () {
		return $this->resource_id;	
	}

	function getUserId () {
		return $this->user_id;	
	}
	
}