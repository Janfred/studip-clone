<?
/**
 * Funktionen für das Rollemanagement
 * @author Dennis Reil, <Dennis.Reil@offis.de>
 */
require_once("plugins/core/de_studip_Role.class.php");
define("UNKNOWN_ROLE_ID",-1);

class de_studip_RolePersistence {

	function de_studip_RolePersistence(){
		
	}

	function getAllRoles(){
		$conn =& PluginEngine::getPluginDatabaseConnection();		
		$conn->CacheFlush();
//		$conn->debug=true;
		$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"select * from roles order by rolename");
		$roles = array();
		if (!$result == null){
			while (!$result->EOF){
				$role = new de_studip_Role();
				$role->setRoleid($result->fields("roleid"));
				$role->setRolename($result->fields("rolename"));
				$roles[$result->fields("roleid")] = $role;
//				echo("DEBUG: ROLE <br>");
//				print_r($role);
				$result->moveNext();
			}
			$result->Close();
		}
//		$conn->debug=false;
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
			$conn->CacheFlush();
		}
		else {
			// role is already in database			
			$result = $conn->execute("update roles set rolename=? where roleid=?",array($role->getRolename(),$role->getRoleid()));
			$roleid = $role->getRoleid();
			$conn->CacheFlush();
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
		$conn->CacheFlush();				
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
		$conn->CacheFlush();
	}
	
	function getAssignedRoles($userid,$implicit=false){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$roles = $this->getAllRoles();
		if ($implicit){
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?",array($userid,$userid));		
		}
		else {
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"SELECT r.roleid FROM roles_user r where r.userid=?",array($userid));		
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
		$conn->CacheFlush();
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
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"select * from roles_user");
		}
		else {
			$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"select * from roles_user where userid=?",array($user->getUserid()));			
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
		$conn->CacheFlush();
	}
	
	function deleteAssignedPluginRoles($pluginid,$roleids){
		$conn =& PluginEngine::getPluginDatabaseConnection();
		foreach ($roleids as $roleid){
			$conn->Execute("delete from roles_plugins where roleid=? and pluginid=?",array($roleid,$pluginid));							
		}
		$conn->CacheFlush();
	}
	
	function getAssignedPluginRoles($pluginid=-1){		
		$roles = $this->getAllRoles();		
		$conn =& PluginEngine::getPluginDatabaseConnection();
		$conn->CacheFlush();
		$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"select * from roles_plugins where pluginid=?",array($pluginid));
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
		$conn->CacheFlush();
		$result = $conn->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"select * from roles_studipperms");
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
?>
