<?
# Lifter002: TODO
// vim: noexpandtab
/**
 * role id unknown
 */
define("UNKNOWN_ROLE_ID",-1);

/**
 * Funktionen für das Rollenmanagement
 * @author Dennis Reil <dennis.reil@offis.de>
 * @package pluginengine
 * @subpackage db
 */

class de_studip_RolePersistence {

	function getAllRoles() {
		$roles = array();
		foreach (DBManager::get()->query("SELECT * FROM roles ORDER BY rolename")
		  as $row) {

			$role = new de_studip_Role();
			$role->setRoleid($row["roleid"]);
			$role->setRolename($row["rolename"]);
			$roles[$row["roleid"]] = $role;
		}
		return $roles;
	}

	/**
	 * Inserts the role into the database or does an update, if it's already there
	 *
	 * @param de_studip_Role $role
	 * @return the role id
	 */
	function saveRole($role) {
		$db = DBManager::get();

		// role is not in database
		if ($role->getRoleid() == UNKNOWN_ROLE_ID) {
			$stmt = $db->prepare("INSERT INTO roles (roleid, rolename) ".
			                     "values (0, ?)");
			$stmt->execute(array($role->getRolename()));
			$roleid = $db->lastInsertId();
		}
		// role is already in database
		else {
			$roleid = $role->getRoleid();
			$stmt = $db->prepare("UPDATE roles SET rolename=? WHERE roleid=?");
			$stmt->execute(array($role->getRolename(), $roleid));
		}
		return $roleid;
	}

	/**
	 * Delete role if not a permanent role. System roles cannot be deleted.
	 *
	 * @param unknown_type $role
	 */
	function deleteRole($role) {
		$id = $role->getRoleid();
		$db = DBManager::get();
		$stmt = $db->prepare("DELETE FROM roles WHERE roleid=? AND system='n'");
		$stmt->execute(array($id));
		if ($stmt->rowCount()) {
			$stmt = $db->prepare("DELETE FROM roles_user WHERE roleid=?");
			$stmt->execute(array($id));
			$stmt = $db->prepare("DELETE FROM roles_plugins WHERE roleid=?");
			$stmt->execute(array($id));
			$stmt = $db->prepare("DELETE FROM roles_studipperms WHERE roleid=?");
			$stmt->execute(array($id));
		}
	}

	/**
	 * Saves a role assignment to the database
	 *
	 * @param StudIPUser $user
	 * @param de_studip_Role $role
	 */
	function assignRole($user,$role) {
		// role is not in database
		// save it to the database first
		if ($role->getRoleid() <> UNKNOWN_ROLE_ID) {
			$roleid = $this->saveRole($role);
		}
		else {
			$roleid = $role->getRoleid();
		}
		$stmt = DBManager::get()->prepare("REPLACE INTO roles_user ".
		  "(roleid, userid) VALUES (?, ?)");
		$stmt->execute(array($roleid, $user->getUserid()));
	}

	function getAssignedRoles($userid, $implicit = false) {

		if ($implicit) {
			$stmt = DBManager::get()->prepare(
			  "SELECT r.roleid FROM roles_user r ".
			  "WHERE r.userid=? ".
			  "UNION ".
			  "SELECT rp.roleid FROM roles_studipperms rp, auth_user_md5 a ".
			  "WHERE rp.permname = a.perms and a.user_id=?");
			$stmt->execute(array($userid, $userid));
		}

		else {
			$stmt = DBManager::get()->prepare(
			  "SELECT r.roleid FROM roles_user r ".
			  "WHERE r.userid=?");
			$stmt->execute(array($userid));
		}

		$assignedroles = array();
		$roles = $this->getAllRoles();
		while ($row = $stmt->fetch()) {
			$assignedroles[] = $roles[$row["roleid"]];
		}
		return $assignedroles;
	}

	/**
	 * Deletes a role assignment from the database
	 *
	 * @param StudIPUser[] $users
	 * @param de_studip_Role $role
	 */
	function deleteRoleAssignment($user,$role) {
		$stmt = DBManager::get()->prepare("DELETE FROM roles_user ".
		  "WHERE roleid=? AND userid=?");
		$stmt->execute(array($role->getRoleid(),$user->getUserid()));
	}

	/**
	 * Get's all Role-Assignments for a certain user.
	 * If no user is set, all role assignments are returned.
	 *
	 * @param StudIPUser $user
	 * @return array with roleids and the assigned userids
	 */
	function getAllRoleAssignments($user=null) {

		if ($user == null) {
			$result = DBManager::get()->query("SELECT * FROM roles_user");
		}

		else {
			$result = DBManager::get()->prepare("SELECT * FROM roles_user ".
			  "WHERE userid=?");
			$result->execute(array($user->getUserid()));
		}

		$roles_user = array();
		while (!$result->EOF) {
			$roles_user[$row["roleid"]] = $row["userid"];
		}
		return $roles_user;
	}

	function assignPluginRoles($pluginid,$roleids) {
		$stmt = DBManager::get()->prepare("REPLACE INTO roles_plugins ".
		  "(roleid, pluginid) VALUES (?, ?)");
		foreach ($roleids as $roleid) {
			$stmt->execute(array($roleid, $pluginid));
		}
	}

	function deleteAssignedPluginRoles($pluginid,$roleids) {
		$stmt = DBManager::get()->prepare("DELETE FROM roles_plugins ".
		  "WHERE roleid=? AND pluginid=?");
		foreach ($roleids as $roleid) {
			$stmt->execute(array($roleid, $pluginid));
		}
	}

	function getAssignedPluginRoles($pluginid=-1) {
		$stmt = DBManager::get()->prepare("SELECT * FROM roles_plugins ".
		  "WHERE pluginid=?");
		$stmt->execute(array($pluginid));

		$assignedroles = array();
		$roles = $this->getAllRoles();
		while ($row = $stmt->fetch()) {
			$role = $roles[$row["roleid"]];
			if (!empty($role)) {
				$assignedroles[] = $role;
			}
		}
		return $assignedroles;
	}

	function getAllGroupRoleAssignments() {
		$roles = $this->getAllRoles();
		$studipperms = $GLOBALS["perm"]->permissions;

		$assignedrolesperms = array();
		foreach (DBManager::get()->query("SELECT * FROM roles_studipperms")
		  as $row) {
			$assignedrolesperm["role"] = $roles[$row["roleid"]];
			$assignedrolesperm[$row["permname"]] = $studipperms[$row["permname"]];
			$assignedrolesperms[] = $assignedrolesperm;
		}
		return $assignedrolesperms;
	}
}
