<?
/**
* ZusatzLockRules.class.php - Sichtbarkeits-Administration fuer Zusatzangaben bei Teilnehmerlisten
*
* Copyright (C) 2006 Till Glöggler <tgloeggl@inspace.de>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

class AuxLockRules {
	function _toArray($db) {
		return array(
			'lock_id' => $db->f('lock_id'),
			'name' => $db->f('name'),
			'description' => $db->f('description'),
			'attributes' => unserialize($db->f('attributes')),
			'order' => unserialize($db->f('sorting'))
		);
	}

	function getAllLockRules() {
		$db = new DB_Seminar("SELECT * FROM aux_lock_rules");
		while ($db->next_record()) {
			$data[$db->f('lock_id')] = AuxLockRules::_toArray($db);
		}

		return $data;
	}

	function getLockRuleById($id) {
		$db = new DB_Seminar("SELECT * FROM aux_lock_rules WHERE lock_id = '$id'");
		$db->next_record();
		return AuxLockRules::_toArray($db);
	}

	function getLockRuleBySemId($sem_id) {
		$db = new DB_Seminar("SELECT aux_lock_rule FROM seminare WHERE Seminar_id = '$sem_id'");
		$db->next_record();
		return AuxLockRules::getLockRuleById($db->f('aux_lock_rule'));
	}

	function createLockRule($name, $description, $fields, $order) {
		$id = md5(uniqid(rand()));
		$attributes = serialize($fields);
		$sorting = serialize($order);
		$db = new DB_Seminar();
		$query = "INSERT INTO aux_lock_rules (lock_id, name, description, attributes, sorting) VALUES ('$id', '$name', '$description', '$attributes', '$sorting')";
		//echo $query;die;
		$db->query($query);
		return $id;
	}

	function updateLockRule($id, $name, $description, $fields, $order) {
		$attributes = serialize($fields);
		$sorting = serialize($order);
		$db = new DB_Seminar("UPDATE aux_lock_rules SET name = '$name', description = '$description', attributes = '$attributes', sorting = '$sorting' WHERE lock_id = '$id'");
		return $db->affected_rows();
	}

	function deleteLockRule($id) {
		$db = new DB_Seminar();
		$db->query("SELECT COUNT(*) as c FROM seminare WHERE aux_lock_rule = '$id'");
		$db->next_record();
		if ($db->f('c') > 0) return FALSE;
		$db->query("DELETE FROM aux_lock_rules WHERE lock_id = '$id'");
		return TRUE;
	}

	function getSemFields() {
		return array(
			'vasemester' => 'Semester',
			'vanr' => 'Veranstaltungsnummer',
			'vatitle' => 'Veranstaltungstitel',
			'vadozent' => 'Dozent'
		);
	}
}
