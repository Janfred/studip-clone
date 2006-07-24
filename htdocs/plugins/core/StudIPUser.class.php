<?php

/**
* User-Object which should be used in plugins
* @author Dennis Reil, <dennis.reil@offis.de>
* @version $Revision$
* $Id$
* @package pluginengine
*/

require_once("lib/classes/UserManagement.class.php");

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
	    $this->permission = new Permission($this->userid);   	    	   	  
    }
    
    function getSurname(){
    	return $this->surname;
    }
    
    function getGivenname(){
    	return $this->givenname;
    }
    
    function setUserid($newuserid){
	    $this->userid=$newuserid;
	    $usermgmt = new UserManagement($this->userid);
	    $this->givenname = $usermgmt->user_data["auth_user_md5.Vorname"];
	    $this->surname = $usermgmt->user_data["auth_user_md5.Nachname"];
	    $this->username = $usermgmt->user_data["auth_user_md5.username"];	 
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