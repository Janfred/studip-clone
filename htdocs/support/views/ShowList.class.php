<?
/**
* EditResourceData.class.php
* 
* shows the forms to edit the object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		EditResourceData.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// EditResourceData.class.php
// stellt die forms zur Bearbeitung eines Ressourcen-Objekts zur Verfuegung
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

/*****************************************************************************
ShowList, stellt Liste mit Hilfe von printThread dar
/*****************************************************************************/
require_once ($RELATIVE_PATH_SUPPORT."/views/ShowTreeRow.class.php");

class ShowList extends ShowTreeRow{
	var $db;
	var $db2;
	var $admin_buttons;			//show admin buttons or not

	function ShowList() {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
	}

	function setAdminButtons($value) {
		$this->admin_buttons=$value;
	}
	
	
	//private
	function showListObject ($resource_id, $admin_buttons=FALSE) {
		global $resources_data, $edit_structure_object, $RELATIVE_PATH_RESOURCES, $PHP_SELF, $ActualObjectPerms, $SessSemName;
	
		//Object erstellen
		$resObject=new ResourceObject($resource_id);

		//Daten vorbereiten
		if (!$resObject->getCategoryIconnr())
			$icon="<img src=\"pictures/cont_folder2.gif\" />";
		else
			$icon="<img src=\"$RELATIVE_PATH_RESOURCES/pictures/cont_res".$resObject->getCategoryIconnr().".gif\" />";

		if ($resources_data["structure_opens"][$resObject->id]) {			
			$link=$PHP_SELF."?structure_close=".$resObject->id."#a";
			$open="open";
			if ($resources_data["actual_object"] == $resObject->id)
				echo "<a name=\"a\"></a>";
		} else {
			$link=$PHP_SELF."?structure_open=".$resObject->id."#a";
			$open="close";
		}

		$titel='';
		if ($resObject->getCategoryName())
			$titel=$resObject->getCategoryName().": ";
		if ($edit_structure_object==$resObject->id) {
			echo "<a name=\"a\"></a>";
			$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
		} else {
			$titel.=htmlReady($resObject->getName());
		}

		//create a link on the titel, too
		if (($link) && ($edit_structure_object != $resObject->id))
			$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
		
		if ($resObject->getOwnerLink())
			$zusatz=sprintf (_("verantwortlich:")." <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), $resObject->getOwnerName());
		else			
			$zusatz=sprintf (_("verantwortlich:")." %s", $resObject->getOwnerName());
		$new=TRUE;
		if ($open=="open") {
			//load the perms
			if (($ActualObjectPerms) && ($ActualObjectPerms->getId() == $resObject->getId()))
				$perms = $ActualObjectPerms->getUserPerm();
			else {
				$ThisObjectPerms = new ResourcesObjectPerms($resObject->getId());
				$perms = $ThisObjectPerms->getUserPerm();
			}
			if ($edit_structure_object==$resObject->id) {
				$content= "<br /><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br />";
				$content.= "<input type=\"image\" name=\"send\" align=\"absmiddle\" ".makeButton("uebernehmen", "src")." border=0 value=\""._("&Auml;nderungen speichern")."\" />";
				$content.= "&nbsp;<a href=\"$PHP_SELF?cancel_edit=$resObject->id\">".makeButton("abbrechen", "img")."</a>";						
				$content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\" />";
				$open="open";
			} else {
				$content=htmlReady($resObject->getDescription());
			}
			if (($admin_buttons) && ($perms == "admin")) {
				if ($resObject->isDeletable()) {
					$edit= "<a href=\"$PHP_SELF?kill_object=$resObject->id\">".makeButton("loeschen")."</a>";
				} 
				$edit.= "&nbsp;<a href=\"$PHP_SELF?create_object=$resObject->id\">".makeButton("neuesobjekt")."</a>";
				$edit.= "&nbsp;&nbsp;&nbsp;&nbsp;";
			} 
			if ($SessSemName[1]) {
				if (($perms == "autor") || ($perms == "admin")) 
					if ($resObject->getCategoryId())
						$edit.= "<a href=\"$PHP_SELF?show_object=$resObject->id&view=openobject_schedule\">".makeButton("belegung")."</a>&nbsp;";
				$edit.= "<a href=\"$PHP_SELF?show_object=$resObject->id&view=openobject_details\">".makeButton("eigenschaften")."</a>";
			} else {
				if (($perms == "autor") || ($perms == "admin"))
					if ($resObject->getCategoryId())
						$edit.= "<a href=\"$PHP_SELF?show_object=$resObject->id&view=view_schedule\">".makeButton("belegung")."</a>&nbsp;";
				$edit.= "<a href=\"$PHP_SELF?show_object=$resObject->id&view=view_details\">".makeButton("eigenschaften")."</a>";
			}
		}

		//Daten an Ausgabemodul senden (aus resourcesVisual)
		$this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
	}
	
	function showListObjects ($start_id='', $level=0, $result_count=0) {

		$db=new DB_Seminar;	
		$db2=new DB_Seminar;
		
		//Let's start and load all the threads
		$query = sprintf ("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' %s", $start_id, ($this->supress_hierachy_levels) ? "AND category_id != ''" : "");
		$db->query($query);
		
		//if we have an empty result
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;
			
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"), $this->admin_buttons);
			//in weitere Ebene abtauchen
			if (($this->recurse_levels == -1) || ($level + 1 < $this->recurse_levels)) {
				//Untergeordnete Objekte laden
				$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$db->f("resource_id")."' ");
				
				while ($db2->next_record())
					$this->showListObjects($db2->f("resource_id"), $level+1, $result_count);
			}
			$result_count++;
		}
	return $result_count;
	}
	
	function showRangeList($range_id) {
		$db=new DB_Seminar;	

		//create the query for all objects owned by the range
		$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}

		//create the query for all additionale perms by the range to an object 
		$query = sprintf ("SELECT resource_id FROM  resources_user_resources WHERE user_id = '%s' ", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}
		
	return $result_count;		
	}
	
	function showSearchList($search_array) {
		$db=new DB_Seminar;	

		//create the query
		if (($search_array["search_exp"]) && (!$search_array["search_properties"]))
			$query = sprintf ("SELECT resource_id FROM resources_objects WHERE name LIKE '%%%s%%' ORDER BY name", $search_array["search_exp"]);

		if ($search_array["properties"]) {
			$query = sprintf ("SELECT DISTINCT resources_objects_properties.resource_id FROM resources_objects_properties %s WHERE ", ($search_array["search_exp"]) ? "LEFT JOIN resources_objects USING (resource_id)" : "");
			
			$i=0;
			foreach ($search_array["properties"] as $key => $val) {
				if ($val == "on")
					$val = 1;
				
				//let's create some possible wildcards
				if (ereg("<", $val)) {
					$val = trim(substr($val, strpos($val, "<")+1, strlen($val)));
					$linking = "<";
				} elseif (ereg(">", $val)) {
					$val = trim(substr($val, strpos($val, "<")+1, strlen($val)));
					$linking = ">";
				} elseif (ereg("<=", $val)) {
					$val = trim(substr($val, strpos($val, "<")+2, strlen($val)));
					$linking = "<=";
				} elseif (ereg(">=", $val)) {
					$val = trim(substr($val, strpos($val, "<")+2, strlen($val)));
					$linking = ">=";
				} else $linking = "=";
				
				$query.= sprintf(" %s (property_id = '%s' AND state %s %s%s%s) ", ($i) ? "AND" : "", $key, $linking,  (!is_numeric($val)) ? "'" : "", $val, (!is_numeric($val)) ? "'" : "");
				$i++;
			}
			
			if ($search_array["search_exp"]) 
				$query.= sprintf(" AND (name LIKE '%%%s%%' OR description LIKE '%%%s%%') ", $search_array["search_exp"], $search_array["search_exp"]);
		}
		
		$db->query($query);

		//if we have an empty result
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;

		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}
	return $result_count;
	}
}