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
		$searchtxt = "%" . $searchtxt . "%";
		$stmt = DBManager::get()->prepare(
		  "SELECT user_id FROM auth_user_md5 ".
		  "WHERE username LIKE ? OR Vorname LIKE ? OR Nachname LIKE ? ".
		  "ORDER BY Vorname, Nachname, username");
		$stmt->execute(array($searchtxt, $searchtxt, $searchtxt));
		$users = array();
		while ($row = $stmt->fetch()){
			$user = new StudIPUser();
			$user->setUserid($row["user_id"]);
			$users[$row["user_id"]] = $user;
		}
		return $users;
	}
}
