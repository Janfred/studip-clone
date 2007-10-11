<?php
/**
* Stud.IP authentication against CAS Server
*
* @access	public
* @author	Dennis Reil <dennis.reil@offis.de>
* @version	$Id$
* @package	
*/

require_once ('StudipAuthSSO.class.php');

// import phpCAS lib
include_once('CAS/CAS.php');
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
		parent::StudipAuthAbstract();
		$this->plugin_name = "cas";
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
		//echo ("Authenticated");
		return true;
	}
	
	function isUsedUsername($username){	
		// echo ("isUsedUsername");
		$db = new DB_Seminar();
		$db->query("select * from auth_user_md5 where username='$username'");
		if (!$db->next_record()){			
			return false;
		}
		else {			
			return true;
		}
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
	
	/**
	* initialize a new user
	*
	* this method is invoked for one time, if a new user logs in ($this->is_new_user is true)
	* place special treatment of new users here
	* @access	private
	* @param	string	the user id
	* @return	bool
	*/
	function doNewUserInit($uid){
		// auto insertion of new users, according to $AUTO_INSERT_SEM[] (defined in local.inc)
		$permlist = array('autor','tutor','dozent');
		$this->dbv->params[] = $uid;
		$db = $this->dbv->get_query("view:AUTH_USER_UID");
		$db->next_record();
		if (in_array($db->f("perms"), $permlist)){
			if (is_array($GLOBALS['AUTO_INSERT_SEM'])){
				foreach ($GLOBALS['AUTO_INSERT_SEM'] as $sem_id) {
					$this->dbv->params = array($sem_id, $uid, 'autor', 0);
					$db = $this->dbv->get_query("view:SEM_USER_INSERT");
				}
			}
			return true;
		}
		return false;
	}		
	
	function logout(){
		// do a global cas logout
		$this->cas->logout();
	}
	
	function getPluginclass(){
		return "cas";
	}
}

?>
