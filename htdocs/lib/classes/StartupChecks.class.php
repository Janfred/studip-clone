<?
/**
* StartupChecks.class.php
* 
* checks to determine if the system is ready to create Veranstaltungen
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
// StartupChecks.class.php
// Checks zum ersten Anlegen einer Veranstaltung
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

class StartupChecks {
	var $registered_checks = array (
		"institutes" => array("perm" => "root"),
		"institutesRange" => array("perm" => "root"),
		"myInstituteRange" => array("perm" => "dozent"),
		"dozent" => array("perm" => "admin"),
		"institutesDozent" => array("perm" => "admin"),
		"myInstitutesDozent" => array("perm" => "dozent")
	);
	var $db;
	
	function StartupChecks() {
		$this->registered_checks["institutes"]["msg"] = _("Sie ben&ouml;tigen mindestens eine Einrichtung, an der Veranstaltungen angeboten werden k&ouml;nnen. Legen Sie bitte zun&auml;chst eine Einrichtung an.");
		$this->registered_checks["institutes"]["link"] = "admin_institut.php?i_view=new";
		$this->registered_checks["institutes"]["link_name"] = _("neue Einrichtung anlegen");
	
		$this->registered_checks["institutesRange"]["msg"] = _("Sie m&uuml;ssen der Einrichtung, f&uuml;r die sie eine Veranstaltung anlegen wollen, mindestens einen Studienbereich zuordnen. Nutzen sie daf&uuml;r die Veranstaltunghierarchie, um Studienbereiche f&uuml;r die entsprechende Einrichtung anzulegen.");
		$this->registered_checks["institutesRange"]["link"] = "admin_sem_tree.php";
		$this->registered_checks["institutesRange"]["link_name"] = _("Studienbereiche zuordnen");

		$this->registered_checks["myInstituteRange"]["msg"] = _("Um Veranstaltungen anlegen zu k&ouml;nnen, muss der Einrichtung, f&uuml;r die Sie eine Veranstaltung anlegen m&ouml;chten, mindestens ein Studienbereich zugeordnet werden. Bitte wenden Sie sich an einen der Administratoren des Systems.");
		$this->registered_checks["myInstituteRange"]["link"] = "impressum.php?view=ansprechpartner";
		$this->registered_checks["myInstituteRange"]["link_name"] = _("Kontakt zu den Administratoren");

		$this->registered_checks["dozent"]["msg"] = _("Sie m&uuml;ssen der Einrichtung, f&uuml;r die sie eine Veranstaltung anlegen wollen, mindestens einen Dozenten zuordnen. Nutzen sie daf&uuml;r die globale Nutzerverwaltung, um einen neuen Nutzer mit dem Status Dozent anzulegen oder den Satus eines bestehenden Nutzers auf &raquo;dozent&laquo; zu setzen.");
		$this->registered_checks["dozent"]["link"] = "new_user_md5";
		$this->registered_checks["dozent"]["link_name"] = _("Dozentenaccount anlegen oder anderen Account hochstufen");

		$this->registered_checks["institutesDozent"]["msg"] = _("Um Veranstaltungen anlegen zu k&ouml;nnen, muss der Einrichtung, f&uuml;r die Sie eine Veranstaltung anlegen m&ouml;chten, mindestens ein Dozentenaccount zugeordnet werden. Nutzen Sie daf&uuml;r die Mitarbeiterverwaltung f&uuml;r Einrichtungen.");
		$this->registered_checks["institutesDozent"]["link"] = "inst_admin.php?list=TRUE";
		$this->registered_checks["institutesDozent"]["link_name"] = _("Dozent der Einrichtung zuordnen");
	
		$this->registered_checks["myInstitutesDozent"]["msg"] = _("Um Veranstaltungen anlegen zu k&ouml;nnen, muss ihr Account der Einrichtung, f&uuml;r die Sie eine Veranstaltung anlegen m&ouml;chten, zugeordnet werden. Bitte wenden Sie sich an einen der Administratoren des Systems.");
		$this->registered_checks["myInstitutesDozent"]["link"] = "impressum.php?view=ansprechpartner";
		$this->registered_checks["myInstitutesDozent"]["link_name"] = _("Kontakt zu den Administratoren");

		$this->db = new DB_Seminar;
	}
	
	function institutes() {
		$query = "SELECT Institut_id FROM Institute";
		$this->db->query ($query);
		
		if ($this->db->nf()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function institutesRange() {
		$query = "SELECT studip_object_id FROM sem_tree LEFT JOIN Institute ON (Institute.Institut_id = sem_tree.studip_object_id)";
		$this->db->query ($query);
		
		if ($this->db->nf()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function myInstituteRange() {
		global $user;

		$query = sprintf ("SELECT studip_object_id FROM user_inst LEFT JOIN Institute USING (Institut_id) LEFT JOIN sem_tree ON (Institute.Institut_id = sem_tree.studip_object_id) WHERE user_id = '%s' ", $user->id);
		$this->db->query ($query);
		
		if ($this->db->nf()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function dozent() {
		$query = "SELECT user_id FROM auth_user_md5 WHERE perms = 'dozent'";
		$this->db->query ($query);
		
		if ($this->db->nf()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function institutesDozent() {
		$query = "SELECT user_id FROM user_inst WHERE inst_perms = 'dozent'";
		$this->db->query ($query);
		
		if ($this->db->nf()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	function myInstitutesDozent() {
		global $user;
		
		$query = sprintf ("SELECT user_id FROM user_inst WHERE inst_perms = 'dozent' AND user_id = '%s'", $user->id);
		$this->db->query ($query);
		
		if ($this->db->nf()) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	function getCheckList() {
		global $perm;
		$list = array();
		
		foreach ($this->registered_checks as $key=>$val) {
			if ((($this->registered_checks[$key]["perm"] == "root") && ($perm->have_perm("root"))) ||
				 (($this->registered_checks[$key]["perm"] == "admin") && ($perm->have_perm("admin"))) ||
				 (($this->registered_checks[$key]["perm"] == "dozent") && ($perm->have_perm("dozent")) && (!$perm->have_perm("root")) && (!$perm->have_perm("admin")))) {

				if (method_exists($this,$key)) {
					$list[$key] = $this->$key();
				}
			}
		}
		return $list;
	}
}
