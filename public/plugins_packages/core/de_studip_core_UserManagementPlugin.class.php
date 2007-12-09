<?php

class de_studip_core_UserManagementPlugin extends AbstractStudIPCorePlugin{

	function de_studip_core_UserManagementPlugin(){
		parent::AbstractStudIPCorePlugin();
	}
	
	/**
	 * Sucht nach Benutzern
	 *
	 * @param array of StudIPUser $searchtxt
	 */
	function searchUser($searchtxt){
		$conn = PluginEngine::getPluginDatabaseConnection();
		$searchtxt = "%" . $searchtxt . "%";		
		$result = $conn->execute("select user_id from auth_user_md5 where username like ? or Vorname like ? or Nachname like ? order by Vorname,Nachname,username",array($searchtxt,$searchtxt,$searchtxt));		
		$users = array();
		if ($result != null){
			while (!$result->EOF){
				$user = new StudIPUser();
				$user->setUserid($result->fields("user_id"));
				$users[$result->fields("user_id")] = $user;
				$result->moveNext();
			}
		}
		return $users;
	}
}
?>