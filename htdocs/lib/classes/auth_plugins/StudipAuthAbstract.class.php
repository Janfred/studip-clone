<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// StudipAuthAbstract.class.php
// Abstract class, used as a template for authentication plugins
// 
// Copyright (c) 2003 André Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/DbView.class.php");
require_once ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/DbSnapshot.class.php");

			

/**
* abstract base class for authentication plugins
*
* abstract base class for authentication plugins 
*
* @access	public
* @author	André Noack <noack@data-quest.de>
* @version	$Id$
* @package	
*/
class StudipAuthAbstract {
	
	/**
	* indicates whether login form should use md5 challenge response auth
	*
	* this should only be true, if password is stored and accessible as md5 hash !
	*
	* @access	public
	* @var		bool
	*/
	var $md5_challenge_response = false;
	
	/**
	*  md5 challenge sent by crcloginform
	*
	* 
	*
	* @access	private
	* @var		string
	*/
	var $challenge;
	
	
	/**
	* contains error message, if authentication fails
	*
	* 
	* @access	public
	* @var		string
	*/
	var $error_msg;
	
	/**
	* indicates whether the authenticated user logs in for the first time
	*
	* 
	* @access	public
	* @var		bool
	*/
	var $is_new_user = false;
	
	/**
	* associative array with mapping for database fields
	*
	* 
	* @access	public
	* @var		array $user_data_mapping
	*/
	var $user_data_mapping = null;
	
	/**
	* database connection
	*
	* 
	* @access	public
	* @var		object DbView
	*/
	var $dbv;
	
	/**
	* name of the plugin (last part of class name)
	*
	* 
	* @access	public
	* @var		string
	*/
	var $plugin_name;
	
	/**
	* 
	*
	* 
	* @access public
	* @static
	*/
	
	function &GetInstance( $plugin_name = false){
		static $plugin_instance;
		if (!is_array($plugin_instance)){
			foreach($GLOBALS['STUDIP_AUTH_PLUGIN'] as $plugin){
				$plugin = "StudipAuth" . $plugin;
				include_once $GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/lib/classes/auth_plugins/" . $plugin . ".class.php";
				$plugin_instance[strtoupper($plugin)] = new $plugin;
			}
		}
		return ($plugin_name) ? $plugin_instance[strtoupper("StudipAuth" . $plugin_name)] : $plugin_instance;
	}
	
	/**
	* 
	*
	* 
	* @access public
	* @static
	*/
	function CheckMD5(){
		$plugins =& StudipAuthAbstract::GetInstance();
		foreach($plugins as $object){
			if (!$object->md5_challenge_response){
				return false;
			}
		}
		return true;
	}
	
	/**
	* 
	*
	* 
	* @access public
	* @static
	*/
	function CheckAuthentication($username,$password,$jscript = false){
		$plugins =& StudipAuthAbstract::GetInstance();
		$error = false;
		$uid = false;
		foreach($plugins as $object){
			if ($uid = $object->authenticateUser($username,$password,$jscript)){
				return array('uid' => $uid,'error' => $error, 'is_new_user' => $object->is_new_user);
			} else {
				$error .= $object->plugin_name . ": " . $object->error_msg . "<br>";
			}
		}
		return array('uid' => $uid,'error' => $error);
	}
	
	/**
	* 
	*
	* 
	* @access public
	* @static
	*/
	function CheckUsername($username){
		$plugins =& StudipAuthAbstract::GetInstance();
		$error = false;
		$found = false;
		foreach($plugins as $object){
			if ($found = $object->isUsedUsername($username)){
				return array('found' => $found,'error' => $error);
			} else {
				$error .= "<b>" . $object->plugin_name . "</b>: " . $object->error_msg . "<br>";
			}
		}
		return array('found' => $found,'error' => $error);
	}
	/**
	* 
	*
	* 
	* @access public
	* @static
	*/
	function CheckField($field_name,$plugin_name){
		if (!$plugin_name){
			return false;
		}
		$plugin =& StudipAuthAbstract::GetInstance($plugin_name);
		return (is_object($plugin) ? $plugin->isMappedField($field_name) : false);
	}
	
	
	/**
	* Constructor
	*
	* 
	* @access private
	* 
	*/
	function StudipAuthAbstract() {
		$this->plugin_name = substr(get_class($this),10);
		//hier auslesen von konfigurationsoptionen
		$config_var = $GLOBALS["STUDIP_AUTH_CONFIG_" . strtoupper($this->plugin_name)];
		if (isset($config_var)){
			foreach ($config_var as $key => $value){
				$this->$key = $value;
			}
		}
		$this->dbv = new DbView();
		$this->challenge = $GLOBALS['challenge'];
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function authenticateUser($username, $password, $jscript = false){
		if ($this->isAuthenticated($username, $password, $jscript)){
			$uid = $this->getStudipUserid($username);
			$this->doDataMapping($uid);
			if ($this->is_new_user){
				$this->doNewUserInit($uid);
			}
			return $uid;
		} else {
			return false;
		}
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function getStudipUserid($username){
		$this->dbv->params[] = $username;
		$db = $this->dbv->get_query("view:AUTH_USER_UNAME");
		if ($db->next_record()){
			$auth_plugin = is_null($db->f("auth_plugin")) ? "standard" : $db->f("auth_plugin");
			if ($auth_plugin != $this->plugin_name){
				$this->error_msg = sprintf(_("Dieser Username wird bereits über %s authentifiziert!"),$auth_plugin) . "<br>";
				return false;
			}
			$uid = $db->f("user_id");
			return $uid;
		}
		$uid = md5(uniqid($username,1));
		$this->dbv->params = array($uid,mysql_escape_string($username),"autor","","","","",$this->plugin_name);
		$db = $this->dbv->get_query("view:AUTH_USER_INSERT");
		$this->dbv->params = array($uid,time(),time(),$GLOBALS['_language']);
		$db = $this->dbv->get_query("view:USER_INFO_INSERT");
		$this->is_new_user = true;
		return $uid;
	}
	
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function doNewUserInit($uid){
		include ($GLOBALS['ABSOLUTE_PATH_STUDIP'] . "/config.inc.php");
		$permlist = array('autor','tutor','dozent');
		$this->dbv->params[] = $uid;
		$db = $this->dbv->get_query("view:AUTH_USER_UID");
		$db->next_record();
		if (in_array($db->f("perms"), $permlist)){
			if (is_array($AUTO_INSERT_SEM)){
				foreach ($AUTO_INSERT_SEM as $sem_id) {
					$this->dbv->params = array($sem_id, $uid, 'autor', 0);
					$db = $this->dbv->get_query("view:SEM_USER_INSERT");
				}
			}
		return true;
		}
		return false;
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function doDataMapping($uid){
		if (is_array($this->user_data_mapping)){
			foreach($this->user_data_mapping as $key => $value){
				if (method_exists($this, $value['callback'])){
					$split = explode(".",$key);
					$table = $split[0];
					$field = $split[1];
					$mapped_value = call_user_method($value['callback'],$this,$value['map_args']);
					$this->dbv->params = array($table,$field,mysql_escape_string($mapped_value),$uid);
					$db = $this->dbv->get_query("view:GENERIC_UPDATE");
				}
			}
			return true;
		}
		return false;
	}
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function isMappedField($name){
		return isset($this->user_data_mapping[$name]);
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function isUsedUsername($username){
		$this->error = sprintf(_("Methode %s nicht implementiert!"),get_class($this) . "::isUsedUsername()");
		return false;
	}
	
	/**
	* 
	*
	* 
	* @access private
	* 
	*/
	function isAuthenticated($username, $password, $jscript){
		$this->error = sprintf(_("Methode %s nicht implementiert!"),get_class($this) . "::isAuthenticated()");
		return false;
	}
	
	
}
?>
