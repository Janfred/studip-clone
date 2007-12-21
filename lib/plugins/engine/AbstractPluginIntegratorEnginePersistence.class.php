<?php
/* vim: noexpandtab */
/**
 * plugin id unknown
 */
define("UNKNOWN_PLUGIN_ID", -1);

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class AbstractPluginIntegratorEnginePersistence {
	var $connection;
	var $environment;
	var $user;

	function AbstractPluginIntegratorEnginePersistence(){
		$this->connection = null;
		$this->user = null;
	}

	function setEnvironment($dbenvironment){
		if (is_a($dbenvironment,"DBEnvironment") || is_subclass_of($dbenvironment,"DBEnvironment")){
			$this->environment = $dbenvironment;
		}
	}

	function getEnvironment(){
		return $this->environment;
	}

	function setUser($newuser){
		if (is_a($newuser,"StudIPUser") || is_subclass_of($newuser,"StudIPUser")){
			$this->user = $newuser;
		}
		else {
			// Fehler, ungültiger Parameter
			$this->user = null;
		}
	}

	function getUser(){
		return $this->user;
	}

	function setConnection($newconnection){
		$this->connection = $newconnection;
	}

	function getConnection(){
		return $this->connection;
	}

    /**
		Registers a new plugin in the database
		@return the pluginid
	*/
	function registerPlugin($plugin, $pluginclassname, $pluginpath,$dependentonplugin=null){
		$type = PluginEngine::getTypeOfPlugin($plugin);
		if (strlen($type) > 0){
			// try to find an existing entry to update
			$result =& $this->connection->execute("select pluginid from plugins where pluginclassname=? and plugintype=?",array($pluginclassname,$type));
			if ($result && !$result->EOF){
				$pluginid = $result->fields("pluginid");
				// try to update this entry
				$result =& $this->connection->execute("update plugins set pluginpath=? where plugintype=? and pluginid=?", array($pluginpath,$type,$pluginid));
			}
			else {
				if (is_null($dependentonplugin)){
					$result =& $this->connection->execute("insert into plugins (pluginclassname,pluginname,pluginpath,plugintype,enabled,navigationpos) select ?,?,?,?,'no',max(navigationpos)+1 from plugins where plugintype=?", array($pluginclassname,$plugin->getPluginname(), $pluginpath,$type,$type));
				}
				else {
					$result =& $this->connection->execute("insert into plugins (pluginclassname,pluginname,pluginpath,plugintype,enabled,navigationpos,dependentonid) select ?,?,?,?,'no',max(navigationpos)+1,? from plugins where plugintype=?", array($pluginclassname,$plugin->getPluginname(), $pluginpath,$type,$dependentonplugin->getPluginid(),$type));
				}
				//$result =& $this->connection->execute("select last_insert_id() as pluginid from plugins");
				// $pluginid = $result->fields("pluginid");

				//$this->connection->debug=true;
				$pluginid = $this->connection->Insert_ID();
				// now register the system roles to this plugin.
				$this->connection->execute("insert into roles_plugins (roleid,pluginid) select roleid,? from roles where system='y'",array($pluginid));
				// $this->connection->debug=false;
			}
			if ($GLOBALS["PLUGINS_CACHING"]){
				$this->connection->CacheFlush();
			}
			return $pluginid;
		}
	}

    /**
    	updates plugin base data like position in the navigation
    */
    function savePlugin($plugin){
    	// keine Funktion
    	$enabled = "";
    	if ($plugin->isEnabled()){
	    	$enabled = "yes";
    	}
    	else{
	    	$enabled = "no";
    	}
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	$this->connection->execute("Update plugins set pluginname=?, enabled=?, navigationpos=? where pluginid=?", array($plugin->getPluginname(),$enabled,$plugin->getNavigationPosition(),$plugin->getPluginid()));
    	if ($GLOBALS["PLUGINS_CACHING"]){
    		$this->connection->CacheFlush();
    	}
    	return;
    }

	function executePluginQuery($filter, $params = array(), $attendroles = true) {
		$user = $this->getUser();
		$userid = $user->getUserid();

		if (!empty($filter) && $attendroles) {
			// look for where in filter
			if (!(($pos = strpos($filter,"where")) === false)) {
				if (($pos == 0) || ($pos == 1)) {
					// where at the beginning
					$filter = str_replace("where", "", $filter);
				}
			}
			$pos = strpos($filter, "order");
			if ($pos === false || $pos > 1) {
				$filter = "and " . $filter;
			} else {
				$filter = " " . $filter;
			}
		}

		if ($attendroles) {
			// ok, filter should start with no where clause
			$params = array_merge(array($userid), (array)$params,
			                      array($userid), (array)$params);
			$filter = "JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			          "JOIN roles_user r ON rp.roleid=r.roleid ".
			          "WHERE r.userid=? {$filter} ".
			          "UNION SELECT p.* FROM plugins p ".
			          "JOIN roles_plugins rp ON p.pluginid=rp.pluginid ".
			          "JOIN roles_studipperms rps ON rp.roleid=rps.roleid ".
			          "JOIN auth_user_md5 au ON rps.permname=au.perms ".
			          "WHERE au.user_id=? " . $filter;
		}

		// cache results for cache_time seconds
		$plugins = array();
		if ($GLOBALS["PLUGINS_CACHING"]) {
			$result = $this->connection->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],
			  "SELECT p.* FROM plugins p " . $filter, $params);
		} else {
			$result = $this->connection->Execute("SELECT p.* FROM plugins p " .
			                                     $filter,
			                                     $params);
		}

		// TODO: Fehlermeldung ausgeben
		if (!$result) {
			return array();
		}
		else {
			$rolemgmt = new de_studip_RolePersistence();
			$userroles = $user->getAssignedRoles(true);
			while (!$result->EOF) {
				$pluginclassname = $result->fields("pluginclassname");
				$pluginpath = $result->fields("pluginpath");
				$pluginid = $result->fields("pluginid");
				$rolerestriction = $rolemgmt->getAssignedPluginRoles($pluginid);
				if (!empty($rolerestriction)) {
				}
				$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
				if ($plugin != null) {
					$plugin->setPluginid($pluginid);
					$plugin->setPluginname($result->fields("pluginname"));
					$plugin->setNavigationPosition($result->fields("navigationpos"));
					if ($result->fields("enabled") == 'yes') {
						$plugin->setEnabled(true);
					}
					else {
						$plugin->setEnabled(false);
					}
					if (!is_null($result->fields("dependentonid"))) {
						$plugin->setDependentOnOtherPlugin(true);
					}
					$plugins[] = $plugin;
				}
				$result->MoveNext();
			}
			$result->Close();
			return $plugins;
		}
	}

	/**
	  * Liefert alle in der Datenbank bekannten Plugins zurück
	  */
	function getAllInstalledPlugins(){
		if ($this->connection == null){
			$this->connection = PluginEngine::getPluginDatabaseConnection();
		}
		return $this->executePluginQuery("order by plugintype, navigationpos, pluginname, enabled",array(),false);
	}

	function getPlugins($enabled = false){
		if ($enabled){
			$filter = 'yes';
		}
		else {
			$filter = 'no';
		}
		return $this->executePluginQuery("where enabled=? order by navigationpos, pluginname, plugintype", array($filter));
	}

	/**
	  * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
	  */
	function getAllEnabledPlugins() {
		return $this->getPlugins(true);
	}

	/**
	  * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
	  */
	function getAllDisabledPlugins() {
		return $this->getPlugins(false);
	}

	function getAllActivatedPlugins() {
		// TODO: Implementierung
		return null;
	}

	function getAllDeactivatedPlugins() {
		// TODO: Implementierung
		return null;
	}

	function getPlugin($id) {
		if ($this->connection === NULL) {
			$this->connection = PluginEngine::getPluginDatabaseConnection();
		}
		$plugins = $this->executePluginQuery("where p.pluginid=?", array($id));

		if (count($plugins) === 1) {
			return $plugins[0];
		}

		return null;
	}


	function pluginExists($id){
		$result = $this->connection->execute("select * from plugins where pluginid=?", array($id));
		if (!$result) {
			return FALSE;
		}
		$return = !$result->EOF;
		$result->Close();
		return $return;
	}


	function deinstallPlugin($plugin)	{
		// check, if there are dependent plugins
		if ($plugin->isDependentOnOtherPlugin()){
			// this plugin is not the main plugin
			// don't search for other plugins.
		} else {
			// this plugin is a plugin without dependencies
			$dependentplugins = $this->executePluginQuery("where p.dependentonid=?", array($plugin->getPluginid()), false);
			if (is_array($dependentplugins)) {
				foreach ($dependentplugins as $dependentplugin) {
					// deinstall Plugin first
					$this->deinstallPlugin($dependentplugin);
				}
			}
		}
		$this->connection->execute("Delete from plugins where pluginid=?", array($plugin->getPluginid()));
		$this->connection->execute("Delete from plugins_activated where pluginid=?", array($plugin->getPluginid()));
		$this->connection->execute("Delete from roles_plugins where pluginid=?", array($plugin->getPluginid()));
	}

	/**
	 * Searches for $pluginclassname in the plugins database
	 *
	 * @return true  - plugin called $pluginclassname was found in the database
	 *         false - plugin not found
	 */
	function isPluginRegistered($pluginclassname) {
		$result = $this->connection->execute("select * from plugins where pluginclassname=?", array($pluginclassname));

		if (!$result) {
		   return false;
		}

		$id = $result->fields("pluginid");

		if (is_numeric($id)) {
			$result->Close();
			return true;
		}

		return false;
	}

	/**
	 * Searches for $pluginname in the plugins database
	 *
	 * @param  string  class name
	 *
	 * @return int     the id of the plugin
	 */
	function getPluginId($pluginclassname) {
		$result = $this->connection->execute("select * from plugins where pluginclassname=?", array($pluginclassname));
		if (!$result || $result->EOF) {
			throw new Studip_PluginNotFoundException();
		}
		$id = $result->fields("pluginid");
		$result->Close();
		return $id;
	}
}
