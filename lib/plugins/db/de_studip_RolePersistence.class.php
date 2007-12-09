<?

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

	function de_studip_RolePersistence(){

	}

	function getAllRoles(){
		$conn =& PluginEngine::getPluginDatabaseConnection();

		if ($GLOBALS["PLUGINS_CACHING"]){
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"select * from roles order by rolename");
		}
		else {
			$result = $conn->execute("select * from roles order by rolename");
		}
		$roles = array();
		if (!$result == null){
			while (!$result->EOF){
				$role = new de_studip_Role();
				$role->setRoleid($result->fields("roleid"));
				$role->setRolename($result->fields("rolename"));
				$roles[$result->fields("roleid")] = $role;
				$result->moveNext();
			}
			$result->Close();
		}
		return $roles;
	}

	/**
	 * Inserts the role into the database or does an update, if it's already there
	 *
	 * @param de_studip_Role $role
	 * @return the role id
	 */
	function saveRole($role){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		if ($role->getRoleid() == UNKNOWN_ROLE_ID){
			// role is not in database
			$result = $conn->execute("insert into roles (roleid,rolename) values (0,?)", array($role->getRolename()));
			$result =& $conn->execute("select last_insert_id() as roleid from roles");
			$roleid = $result->fields("roleid");
			if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
		}
		else {
			// role is already in database
			$result = $conn->execute("update roles set rolename=? where roleid=?",array($role->getRolename(),$role->getRoleid()));
			$roleid = $role->getRoleid();
			if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
		}
		return $roleid;
	}

	/**
	 * Delete role if not a permanent role. System roles cannot be deleted.
	 *
	 * @param unknown_type $role
	 */
	function deleteRole($role){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$conn->execute("delete from roles where roleid=? and system='n'",array($role->getRoleid()));
		if ($conn->Affected_Rows() > 0){
			$conn->execute("delete from roles_user where roleid=?",array($role->getRoleid()));
			$conn->execute("delete from roles_plugins where roleid=?",array($role->getRoleid()));
			$conn->execute("delete from roles_studipperms where roleid=?",array($role->getRoleid()));
		}
		if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
	}

	/**
	 * Saves a role assignment to the database
	 *
	 * @param StudIPUser $user
	 * @param de_studip_Role $role
	 */
	function assignRole($user,$role){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		if ($role->getRoleid() <> UNKNOWN_ROLE_ID){
			// role is not in database
			// save it to the database first
			$roleid = $this->saveRole($role);
		}
		else {
			$roleid = $role->getRoleid();
		}
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$conn->execute("replace into roles_user (roleid,userid) values (?,?)",array($roleid,$user->getUserid()));
		if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
	}

	function getAssignedRoles($userid,$implicit=false){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$roles = $this->getAllRoles();
		if ($implicit){
			$sqlstr = "SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?";
			if ($GLOBALS["PLUGINS_CACHING"]){
				$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],$sqlstr,array($userid,$userid));
			}
			else {
				$result = $conn->execute($sqlstr,array($userid,$userid));
			}
		}
		else {
			$sqlstr = "SELECT r.roleid FROM roles_user r where r.userid=?";
			if ($GLOBALS["PLUGINS_CACHING"]){
				$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],$sqlstr,array($userid));
			}
			else {
				$result = $conn->execute($sqlstr,array($userid));
			}
		}
		$assignedroles=array();
		if (!$result == null){
			while (!$result->EOF){
				$assignedroles[] = $roles[$result->fields("roleid")];
				$result->moveNext();
			}
			$result->Close();

		}
		return $assignedroles;
	}

	/**
	 * Deletes a role assignment from the database
	 *
	 * @param StudIPUser[] $users
	 * @param de_studip_Role $role
	 */
	function deleteRoleAssignment($user,$role){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$conn->execute("delete from roles_user where roleid=? and userid=?",array($role->getRoleid(),$user->getUserid()));
		if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
	}

	/**
	 * Get's all Role-Assignments for a certain user.
	 * If no user is set, all role assignments are returned.
	 *
	 * @param StudIPUser $user
	 * @return array with roleids and the assigned userids
	 */
	function getAllRoleAssignments($user=null){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		if ($user == null){
			$sqlstr = "select * from roles_user";
			if ($GLOBALS["PLUGINS_CACHING"]){
				$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],$sqlstr);
			}
			else {
				$result = $conn->execute($sqlstr);
			}
		}
		else {
			$sqlstr = "select * from roles_user where userid=?";
			if ($GLOBALS["PLUGINS_CACHING"]){
				$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],$sqlstr,array($user->getUserid()));
			}
			else {
				$result = $conn->execute($sqlstr,array($user->getUserid()));
			}
		}

		$roles_user = array();
		if (!$result == null){
			while (!$result->EOF){
				$roles_user[$result->fields("roleid")] = $result->fields("userid");
				$result->moveNext();
			}
			$result->Close();
		}
		return $roles_user;
	}

	function assignPluginRoles($pluginid,$roleids){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		foreach ($roleids as $roleid){
			$conn->Execute("replace into roles_plugins (roleid,pluginid) values (?,?)",array($roleid,$pluginid));
		}
		if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
	}

	function deleteAssignedPluginRoles($pluginid,$roleids){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		foreach ($roleids as $roleid){
			$conn->Execute("delete from roles_plugins where roleid=? and pluginid=?",array($roleid,$pluginid));
		}
		if ($GLOBALS["PLUGINS_CACHING"]) $conn->CacheFlush(); 
	}

	function getAssignedPluginRoles($pluginid=-1){
		$roles = $this->getAllRoles();
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$sqlstr = "select * from roles_plugins where pluginid=?";
		if ($GLOBALS["PLUGINS_CACHING"]){
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],$sqlstr,array($pluginid));
		}
		else {
			$result = $conn->execute($sqlstr,array($pluginid));
		}
		$assignedroles = array();
		if (!$result == null){
			while (!$result->EOF){
				$role = $roles[$result->fields("roleid")];
				if (!empty($role)){
					$assignedroles[] = $role;
				}
				$result->moveNext();
			}
			$result->Close();
		}
		return $assignedroles;
	}

	function getAllGroupRoleAssignments(){
		$roles = $this->getAllRoles();
		$studipperms = $GLOBALS["perm"]->permissions;
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$sqlstr = "select * from roles_studipperms";
		if ($GLOBALS["PLUGINS_CACHING"]){
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],$sqlstr);
		}
		else {
			$result = $conn->execute($sqlstr);
		}
		$assignedrolesperms = array();
		if (!$result == null){
			while (!$result->EOF){
				$assignedrolesperm["role"] = $roles[$result->fields("roleid")];
				$assignedrolesperm[$result->fields("permname")] = $studipperms[$result->fields("permname")];
				$assignedrolesperms[] = $assignedrolesperm;
				$result->moveNext();
			}
			$result->Close();
		}
		return $assignedrolesperms;
	}
}
