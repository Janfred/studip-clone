<?php

/**
* User-Object which should be used in plugins
* @author Dennis Reil, <dennis.reil@offis.de>
* @version $Revision$
* $Id$
* @package pluginengine
*/
class StudIPUser {
	var $userid;
	var $username;	
	var $permission;

	/**
	* Automatically reads in the uid of the current user
	*/
    function StudIPUser() {
    	$auth = $GLOBALS["auth"];
	    $this->userid=$auth->auth['uid'];
	    $this->username=$auth->auth['uname'];
	    $this->permission = new Permission();
    }
    
    function setUserid($newuserid){
	    $this->userid=$newuserid;
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
}
?>