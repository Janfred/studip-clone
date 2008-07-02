<?php
/**
* Stud.IP authentication against CAS Server
*
* @access	public
* @author	Dennis Reil <dennis.reil@offis.de>
* @version	$Id$
* @package	
*/

require_once 'StudipAuthSSO.class.php';
// import phpCAS lib
require_once 'CAS/CAS.php';

class StudipAuthCAS extends StudipAuthSSO {
	
	var $host;
	var $port;
	var $uri;
	
	var $cas;
	var $userdata;
	
	/**
	* Constructor
	*
	* 
	* @access public
	* 
	*/
	function StudipAuthCAS() {
		//calling the baseclass constructor
		parent::StudipAuthSSO();
		if ($this->cas == null){
			$this->cas = new CASClient(CAS_VERSION_2_0,false,$this->host,$this->port,$this->uri,false);
		}
	}
	
	function getUser(){
		return $this->cas->getUser();
	}
	
	function isAuthenticated($username, $password, $jscript){
		// do CASAuthentication
		$this->cas->forceAuthentication();
		return true;
	}
	
	/**
	* authentication method
	*
	* this method authenticates the passed username, it is used by StudipAuthAbstract::CheckAuthentication()
	* if authentication succeeds it calls StudipAuthAbstract::doDataMapping() to map data fields
	* if the authenticated user logs in for the first time it calls StudipAuthAbstract::doNewUserInit() to
	* initialize the new user
	* @access private
	* @param	string	the username to check
	* @param	string	the password to check
	* @param	bool	indicates if javascript was enabled/disabled during the login process
	* @return	string	if authentication succeeds the Stud.IP user id, else false
	*/
	function authenticateUser($username, $password, $jscript = false){
		if ($this->isAuthenticated($username, $password, $jscript)){			
			if ($uid = $this->getStudipUserid($this->getUser())){						
				$this->doDataMapping($uid);
				if ($this->is_new_user){
					$this->doNewUserInit($uid);
				}
			}
			return $uid;
		} else {
			return false;
		}
	}	
	
	function getUserData($key){
		$userdataclassname = $GLOBALS["STUDIP_AUTH_CONFIG_CAS"]["user_data_mapping_class"];
		if (empty($userdataclassname)){
			echo ("ERROR: no userdataclassname specified.");
			return;
		}
		require_once($userdataclassname . ".class.php");
		// get the userdata
		if (empty($this->userdata)){
			$this->userdata = new $userdataclassname();		
		}
		$result = $this->userdata->getUserData($key, $this->cas->getUser());		
		return $result;
	}
	
	function logout(){
		// do a global cas logout
		$this->cas->logout();
	}
}

?>
