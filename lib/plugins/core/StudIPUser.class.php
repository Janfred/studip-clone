<?php

/**
* User-Object which should be used in plugins
* @author Dennis Reil, <dennis.reil@offis.de>
* @version $Revision$
* $Id$
* @package pluginengine
* @subpackage core
*/

class StudIPUser {
	var $userid;
	var $username;
	var $permission;
	var $surname;
	var $givenname;
	var $assignedroles;

	/**
	* Automatically reads in the uid of the current user
	*/
    function StudIPUser() {
    	$auth = $GLOBALS["auth"];
	    $this->setUserid($auth->auth['uid']);
    }

    function getSurname(){
    	return $this->surname;
    }

    function getGivenname(){
    	return $this->givenname;
    }

    function setUserid($newuserid){
	    $this->userid = $newuserid;
		$dbconn = PluginEngine::getPluginDatabaseConnection();
		if ($GLOBALS['PLUGINS_CACHING']){
    		$result =& $dbconn->CacheExecute($GLOBALS['PLUGINS_CACHE_TIME'],"SELECT * FROM auth_user_md5 WHERE user_id=?",array($newuserid));
		}
		else {
			$result =& $dbconn->Execute("SELECT * FROM auth_user_md5 WHERE user_id=?",array($newuserid));
		}
		if (!$result->EOF){
			$this->givenname = $result->fields("Vorname");
			$this->surname = $result->fields("Nachname");
			$this->username = $result->fields("username");
		}
    	$result->Close();
	   	$this->permission = new Permission($this->userid);
    }

    function getUserid(){
	    return $this->userid;
    }

    function getPermission(){
    	return $this->permission;
    }

    function getUsername(){
    	return $this->username;
    }
    /*
    function setUsername($newusername){
    	$this->username = $newusername;
    }
    */

    /**
     * checks, if this user is identical to the otheruser
     *
     * @param StudIPUser $otheruser
     * @return false - other user is not the same as this user
     * 		   true - both user are the same
     */
    function isSameUser($otheruser){
    	if (is_a($otheruser,"StudIPUser") || is_subclass_of($otheruser,"StudIPUser")){
    		if ($otheruser->getUserid() == $this->getUserid()){
    			return true;
    		}
    		else {
    			return false;
    		}
    	}
    	else {
    		return false;
    	}
    }

    function getAssignedRoles($withimplicit=false){
    	$rolemgmt = new de_studip_RolePersistence();
	    $this->assignedroles = $rolemgmt->getAssignedRoles($this->userid,$withimplicit);
    	return $this->assignedroles;
    }

}
?>
