<?php
/**
* DataFields.class.php
* 
* generic data-fields for the Stud.IP objects Veranstaltungen, Einrichtungen and user
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		core
* @module		StartupChecks.class.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// DataFields.class.php
// generische Datenfelder fuer Veranstaltungen, Einrichtungen und Nutzer
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

require_once 'lib/functions.php';
require_once "config.inc.php";
require_once "lib/classes/StudipForm.class.php";

class DataFields {
	var $db;
	var $db2;
	var $perms_mask = array(	//the perm's bitmask for assigned datafields depending from the global perms in field object_class
		"user" => 1, 
		"autor" => 2,
		"tutor" => 4,
		"dozent" => 8,
		"admin" => 16,
		"root" => 32,
		"self" => 64,);
	var $range_id;			//range_id from the stud.ip object
	var $form_obj = array();
	
	function DataFields($range_id = '') {
		$this->range_id = $range_id;
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}
	
	function getReadableUserClass($class) {
		foreach ($this->perms_mask as $key=>$val) {
			if ($class & $val) {
				if ($i)
					$result.=", ";
				$result.=$key;
				$i++;
			}
		}
		return $result;
	}

	function checkPermission($perm, $view_perms, $watcher = "", $user = "") {
		if ($view_perms == "all") return TRUE;	//everybody may see the information
		if ($perm->have_perm($view_perms)) return TRUE;	//permission ist high enough
		if (($watcher != "") && ($user != "")) {	//user may see his own data
			if ($user == $watcher) return TRUE;
		}
		return FALSE;	//nothing matched...
	}

	function getNumberUsedEntries($datafield_id) {
		$db = new DB_Seminar;
		$query = sprintf ("SELECT count(range_id) AS count FROM datafields_entries WHERE datafield_id = '%s' ", $datafield_id);
	
		$db->query($query);
		$db->next_record();
		
		return $db->f("count");
	}

	function getFields($object_type='') {
		$datafields = array();
		
		if ($object_type) {
			$query = sprintf ("SELECT * FROM datafields WHERE object_type = '%s' ORDER BY object_class, priority, name", $object_type);
			
			$this->db->query($query);
				
			while ($this->db->next_record()) {
				$datafields[$this->db->f("datafield_id")] = $this->db->Record;
				$datafields[$this->db->f("datafield_id")]["used_entries"] = $this->getNumberUsedEntries($this->db->f("datafield_id"));
			}
		}
		
		return $datafields;
	}
	
	function getLocalFields($range_id = '', $object_class='', $object_type='') {
		global $SEM_TYPE;
		$local_datafields = array();
		
		if (!$range_id)
			$range_id = $this->range_id;
	
		if ((!$object_class) && ($range_id))
			$object_class = get_object_type($range_id);

		if ($object_class) {
			if (!$object_type) {
				switch ($object_class) {
					case "sem": 
						$query = sprintf ("SELECT status AS type FROM seminare WHERE seminar_id = '%s' ", $range_id);
					break;
					case "inst":
					case "fak":
						$query = sprintf ("SELECT type FROM Institute WHERE Institut_id = '%s' ", $range_id);
					break;
					case "user":
						$query = sprintf ("SELECT perms as type FROM auth_user_md5 WHERE user_id = '%s' ", $range_id);
					break;
				}

				$this->db->query($query);
				$this->db->next_record();
				
				$object_type = $this->db->f("type");
			}

			$object_type = ($object_class == 'sem') ? $SEM_TYPE[$object_type]['class'] : $object_type;
			
			switch ($object_class) {
				case "sem": 
				case "inst":
				case "fak":
					if ($object_type)
						$clause = "object_class = ".$object_type." OR object_class IS NULL";
					else
						$clause = "object_class IS NULL";
				break;
				case "user":
					$clause = "((object_class & ".$this->perms_mask[$object_type].") OR object_class IS NULL)";
				break;
			}


			if ($object_class == "fak")
				$object_class = "inst";
		
			$query2 = sprintf ("SELECT a.datafield_id, name, content, edit_perms, view_perms FROM datafields a LEFT JOIN datafields_entries b ON (a.datafield_id=b.datafield_id AND range_id = '%s') WHERE object_type ='%s' AND (%s) ORDER BY object_class, priority", $range_id, $object_class, $clause);

			$this->db2->query($query2);

			while ($this->db2->next_record()) {
				$local_datafields[$this->db2->f("datafield_id")] = $this->db2->Record;
			}
		}
		return $local_datafields;		
	}

	function &getLocalFieldsFormObject($form_name = 'datafield_form', $range_id = null, $object_class = null, $object_type = null, $user_id = null){
		@include "config_datafields.inc.php";
		if (!$range_id){
			$range_id = $this->range_id;
		}
		if (!$user_id){
			$user_id = $GLOBALS['auth']->auth['uid'];
		}
		if (!is_null($this->form_obj[$range_id])){
			return $this->form_obj[$range_id];
		}
		$form_fields = array();
		foreach ($this->getLocalFields($range_id, $object_class, $object_type) as $id => $value){
			if ($value['view_perms'] == 'all' || $GLOBALS['perm']->have_perm($value['view_perms']) || $range_id == $user_id){
				if ($DATAFIELDS[$id]){
					$form_fields[$id] = $DATAFIELDS[$id];
				} else {
					$form_fields[$id]['type'] = 'textarea';
					$form_fields[$id]['attributes']['rows'] = 3;
				}
				$form_fields[$id]['default_value'] = $value['content'];
				if (!$GLOBALS['perm']->have_perm($value['edit_perms'])){
					$form_fields[$id]['type'] = 'noform';
					if (!$value['content']){
						$form_fields[$id]['default_value'] = _("keine Inhalte vorhanden");
					}
					$form_fields[$id]['info'] = _("Das Feld ist für die Bearbeitung gesperrt und kann nur durch einen Administrator verändert werden.");
					$form_fields[$id]['attributes']['style'] = 'font-size:80%;font-weight:bold;font-style:italic;';
				}
				$form_fields[$id]['caption'] = $value['name'];
			} 
		}
		$this->form_obj[$range_id] =& new StudipForm($form_fields, array(), $form_name, false);
		return $this->form_obj[$range_id];
	}
	
	function storeContentFromForm($form_name = 'datafield_form', $range_id = null, $object_class = null, $object_type = null, $user_id = null){
		$form =& $this->getLocalFieldsFormObject($form_name, $range_id, $object_class, $object_type, $user_id);
		foreach ($form->getFormFieldsByName($only_editable = true) as $field_id){
			$ret += $this->storeContent(mysql_escape_string($form->getFormFieldValue($field_id)), $field_id, $range_id);
		}
		$form = null;
		return $ret;
	}
	
	function storeContent($content, $datafield_id, $range_id = '') {
		if (!$range_id)
			$range_id = $this->range_id;
		$query = sprintf ("SELECT mkdate FROM datafields_entries WHERE datafield_id ='%s' AND range_id = '%s'", $datafield_id, $range_id);
		$this->db->query($query);
		$this->db->next_record();
		if ($this->db->f("mkdate"))
			$mkdate = $this->db->f("mkdate");
		else
			$mkdate = time();
			
		$query = sprintf ("REPLACE INTO datafields_entries SET content='%s', datafield_id ='%s', range_id = '%s', mkdate = '%s', chdate ='%s' ", $content, $datafield_id, $range_id, $mkdate, time());
		$this->db->query($query);
		
		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;

	}
	
	function storeDataField($datafield_id='', $name='', $object_type='', $object_class='', $edit_perms='', $priority='', $view_perms='') {
		if ($datafield_id) {
			$query = sprintf ("SELECT * FROM datafields WHERE datafield_id = '%s' ", $datafield_id);

			$this->db->query($query);
			$this->db->next_record();
			
			if (!$name)
				$name = $this->db->f("name");

			if (!$object_type)
				$object_type = $this->db->f("object_type");
				
			if (!$object_class)
				$object_class = $this->db->f("object_class");
			
			if (!$edit_perms)
				$edit_perms = $this->db->f("edit_perms");

			if (!$view_perms)
				$view_perms = $this->db->f("view_perms");			
	
			if (!$priority)
				$priority = $this->db->f("priority");
		} else {
			$datafield_id = md5(uniqid("life42"));
		}
	
		if (!$object_class)
			$object_class = "NULL";

		if (!$view_perms)
			$view_perms = "NULL";

		$query = sprintf ("REPLACE INTO datafields SET datafield_id = '%s', name= '%s', object_type = '%s', object_class = %s, edit_perms = '%s', priority = '%s', view_perms = '%s'",
				$datafield_id, $name, $object_type, $object_class, $edit_perms, $priority, $view_perms);

		$this->db->query($query);

		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
	}
	
	function killDataField ($datafield_id) {
		$query = sprintf ("DELETE FROM datafields WHERE datafield_id = '%s' ", $datafield_id);
		
		$this->db->query($query);
		
		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
	}

	function killAllEntries ($range_id = '') {
		if (!$range_id)
			$range_id = $this->range_id;
		
		if ($range_id) {
			$query = sprintf ("DELETE FROM datafields_entries WHERE range_id = '%s'", $range_id);
		
		$this->db->query($query);
		
		if ($this->db->affected_rows())
			return TRUE;
		else
			return FALSE;
		}
	}
}
?>
