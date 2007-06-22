<?php

/**
 * plugin id unknown
 */
define("UNKNOWN_PLUGIN_ID",-1);

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

	function &getConnection(){
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

    /**
    *
    */
    function &executePluginQuery($filter,$params=array(),$attendroles=true){
    	$user = $this->getUser();
		$userid = $user->getUserid();

    	if (!empty($filter) && $attendroles){
    		// look for where in filter
    		if (!(($pos = strpos($filter,"where")) === false)){
    			if (($pos == 0) || ($pos == 1)){
    				// where at the beginning
    				$filter = str_replace("where","",$filter);
    			}
    		}
    		$pos = strpos($filter,"order");
    		if (($pos === false) || ($pos > 1)){
    			$filter = "and " . $filter;
    		}
    		else {
    			$filter = " " . $filter;
    		}

    	}
    	if ($attendroles){
	    	// ok, filter should start with no where clause
	    	$params = array_merge(array($userid),(array)$params,array($userid),(array)$params);

	    	// $filter = "where p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?)) " . $filter;

	    	$newfilter = "join roles_plugins rp on p.pluginid=rp.pluginid join roles_user r on rp.roleid=r.roleid where r.userid=? " . $filter
	    			. " union select p.* from plugins p join roles_plugins rp on p.pluginid=rp.pluginid join roles_studipperms rps on rp.roleid=rps.roleid join auth_user_md5 au on  rps.permname=au.perms where au.user_id=? " . $filter;
	    	$filter = $newfilter;
    	}
    	// cache results for cache_time seconds
    	$plugins = array();
    	if ($GLOBALS["PLUGINS_CACHING"]){
    		$result = &$this->connection->CacheExecute($GLOBALS["PLUGINS_CACHE_TIME"],"Select p.* from plugins p " . $filter,$params);
    	}
    	else {
    		// select * from plugins p where p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid='76ed43ef286fb55cf9e41beadb484a9f' union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id='76ed43ef286fb55cf9e41beadb484a9f'))
    		//$this->connection->debug=true;
    	 	$result = &$this->connection->Execute("Select p.* from plugins p " . $filter,$params);
    	 	// $this->connection->debug=false;
    	}
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
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
    			if (!empty($rolerestriction)){


    			}
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);

				if ($plugin != null){
	            	$plugin->setPluginid($pluginid);
	        		$plugin->setPluginname($result->fields("pluginname"));
	        		$plugin->setNavigationPosition($result->fields("navigationpos"));
	        		if ($result->fields("enabled") == 'yes'){
	        			$plugin->setEnabled(true);
	        		}
	        		else {
	        			$plugin->setEnabled(false);
	        		}
	        		if (!is_null($result->fields("dependentonid"))){
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

    function getPlugins($enabled=false){
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
    function getAllEnabledPlugins(){
        return $this->getPlugins(true);
    }

    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
     */
    function getAllDisabledPlugins(){
    	return $this->getPlugins(false);
    }

    /*
    */
    function getAllActivatedPlugins(){
	    // TODO: Implementierung
	    return null;
    }

    /*
    */
    function getAllDeactivatedPlugins(){
	    // TODO: Implementierung
	    return null;
    }


    function getPlugin($id){
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	$plugins = $this->executePluginQuery("where p.pluginid=?",array($id));
    	if (count($plugins) == 1){
    		return $plugins[0];
    	}
    	else {
    		return null;
    	}
    }


		function pluginExists($id){
			$result = &$this->connection->execute("select * from plugins where pluginid=?", array($id));
			if (!$result) {
				return FALSE;
			}
			$return = !$result->EOF;
			$result->Close();
			return $return;
		}


    function deinstallPlugin($plugin){
    	// check, if there are dependent plugins
    	if ($plugin->isDependentOnOtherPlugin()){
    		// this plugin is not the main plugin
    		// don't search for other plugins.
    	}
    	else {
    		// this plugin is a plugin without dependencies
    		$dependentplugins = $this->executePluginQuery("where p.dependentonid=?",array($plugin->getPluginid()),false);
	    	if (is_array($dependentplugins)){
	    		foreach ($dependentplugins as $dependentplugin){
	    			// deinstall Plugin first
	    			$this->deinstallPlugin($dependentplugin);
	    		}
	    	}
    	}
		$this->connection->execute("Delete from plugins where pluginid=?", array($plugin->getPluginid()));
		$this->connection->execute("Delete from plugins_activated where pluginid=?", array($plugin->getPluginid()));
		$this->connection->execute("Delete from roles_plugins where pluginid=?",array($plugin->getPluginid()));
	}

	/**
	* Searches for $pluginclassname in the plugins database
	* @return true - plugin called $pluginclassname was found in the database
	*  		  false - plugin not found
	*/
	function isPluginRegistered($pluginclassname){
		$result = &$this->connection->execute("select * from plugins where pluginclassname=?", array($pluginclassname));

		if (!$result){
		   return false;
		}
		else {
			 $id = $result->fields("pluginid");
			 if (is_numeric($id)){
			 	$result->Close();
			 	return true;
			 }
			 else {
			 	return false;
			 }
		}
	}

	/**
	* Searches for $pluginname in the plugins database
	* @return the id of the plugin
	*/
	function getPluginId($pluginclassname){
		$result = &$this->connection->execute("select * from plugins where pluginclassname=?", array($pluginclassname));
		if (!$result){
		   return UNKNOWN_PLUGIN_ID;
		}
		else {
			if (!$result->EOF){
				return $result->fields("pluginid");
			}
			$result->Close();
			return UNKNOWN_PLUGIN_ID;
		}
	}
}
?>
