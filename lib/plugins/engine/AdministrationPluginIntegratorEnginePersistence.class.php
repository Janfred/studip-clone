<?php

/**
 * poiid of administrators
 */
define("PLUGIN_ADMINISTRATION_POIID","admin");

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class AdministrationPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

    function AdministrationPluginIntegratorEnginePersistence(){
    }

    /**
     * Liefert alle in der Datenbank bekannten Plugins zurück
     */
    function getAllInstalledPlugins(){

    	$plugins = array();
    	// nur Administrations-Plugins liefern
    	$plugins = parent::executePluginQuery("where plugintype='Administration' order by navigationpos, pluginname");


    	foreach ($plugins as $plugin){
    		$result = &$this->connection->execute("Select * from plugins_activated where pluginid=? and poiid=?",array($plugin->getPluginid(),PLUGIN_ADMINISTRATION_POIID));
    		if ($result){
    			//
    			if (!$result->EOF){
    				$plugin->setActivated(true);
    			}
    			else {
    				$plugin->setActivated(false);
    			}
    		}
    		else {
    			$plugin->setActivated(false);
    		}
    		$result->Close();
    		$extplugins[] = $plugin;
    	}
    	return $extplugins;

    }

    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
     */
    function getAllActivatedPlugins(){
    	if ($this->connection == null){
    		$this->connection = PluginEngine::getPluginDatabaseConnection();
    	}
    	$plugins = array();
    	//$result = &$this->connection->execute("SELECT p.* FROM plugins_activated a left join plugins p on p.pluginid=a.pluginid where a.poiid=? and p.plugintype='Administration' order by p.navigationpos",array(PLUGIN_ADMINISTRATION_POIID));
    	$user = $this->getUser();
    	$userid = $user->getUserid();
    	//$result = &$this->connection->execute("SELECT * FROM plugins p join plugins_activated a on p.pluginid=a.pluginid where a.poiid=? and p.plugintype='Administration' and p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?)) order by p.navigationpos",array(PLUGIN_ADMINISTRATION_POIID,$userid,$userid));

    	// $result = &$this->connection->execute("SELECT * FROM plugins p join plugins_activated a on p.pluginid=a.pluginid where a.poiid=? and p.plugintype='Administration' and p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?)) order by p.navigationpos",array(PLUGIN_ADMINISTRATION_POIID,$userid,$userid));

    	$result = &$this->connection->execute("SELECT p.* FROM plugins p join plugins_activated a on p.pluginid=a.pluginid join roles_plugins rp on p.pluginid=rp.pluginid
					join roles_user r on rp.roleid=r.roleid
					where a.poiid=? and p.plugintype='Administration'
					and r.userid=?

					union

					SELECT distinct p.* FROM plugins p join plugins_activated a on p.pluginid=a.pluginid join roles_plugins rp on p.pluginid=rp.pluginid
					join roles_studipperms rps on rps.roleid=rp.roleid
					join auth_user_md5 au on rps.permname = au.perms
					where
					au.user_id=? and a.poiid=? and p.plugintype='Administration'
					ORDER BY navigationpos, pluginname",
					array(PLUGIN_ADMINISTRATION_POIID,$userid,$userid,PLUGIN_ADMINISTRATION_POIID));


    	//$result = &$this->connection->execute("SELECT p.* FROM plugins_activated a left join plugins p on p.pluginid=a.pluginid where a.poiid=? and p.plugintype='Administration' order by p.navigationpos",array(PLUGIN_ADMINISTRATION_POIID));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		// echo ("keine aktivierten Plugins<br>");
    		return array();
    	}
    	else {
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname,$pluginpath);
            	if ($plugin != null){
            		$plugin->setPluginid($result->fields("pluginid"));
	        		$plugin->setPluginname($result->fields("pluginname"));
	        		$plugin->setActivated(true);
	        		$plugin->setUser($this->getUser());
	        		$plugins[] = $plugin;
            	}
            	$result->MoveNext();
        	}
        	$result->Close();
        	return $plugins;
    	}
    }

    /**
     * Liefert alle in der Datenbank bekannten und aktivierten Plugins zurück
     */
    function getAllDeActivatedPlugins(){
    	$plugins = array();
    	$result = &$this->connection->execute("SELECT p.* FROM plugins p left join plugins_activated a on p.pluginid=a.pluginid where a.poiid=? p.plugintype='Administration' and (a.pluginid is null)",array(PLUGIN_ADMINISTRATION_POIID));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return array();
    	}
    	else {
    		while (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname,$pluginpath);
            	if ($plugin != null){
	            	$plugin->setPluginid($result->fields("pluginid"));
	            	$plugin->setPluginname($result->fields("pluginname"));
	            	$plugin->setActivated(false);
	            	$plugin->setUser($this->getUser());
	            	$plugins[] = $plugin;
            	}
            	$result->MoveNext();
        	}
        	$result->Close();
        	return $plugins;
    	}
    }

    /**
     * Speichere ein Plugin
     */
    function savePlugin($plugin){
    	parent::savePlugin($plugin);
    	if (is_object($plugin) && is_subclass_of($plugin,'AbstractStudIPAdministrationPlugin')){
    		// Plugin speichern
    		if ($plugin->isActivated()){
    			//$this->connection->execute("replace into plugins_administration_activated (pluginid) values(?)", array($plugin->getPluginId()));
    			$this->connection->execute("replace into plugins_activated (pluginid,poiid) values(?,?)", array($plugin->getPluginId(),PLUGIN_ADMINISTRATION_POIID));
    		}
    		else {
    			// Plugin aus der aktiven Tabelle löschen
    			//$this->connection->execute("delete from plugins_administration_activated where pluginid=?", array($plugin->getPluginId()));
    			$this->connection->execute("delete from plugins_activated where pluginid=?", array($plugin->getPluginId()));
    		}
    	}
    	else {
    		// TODO: richtige Fehlerbehandlung
    		echo ("ERROR: kein gültiger Parameter<br>");
    		echo ("<pre>");
    		print_r($plugin);
    		echo ("</pre>");
    	}
    }

    function getPlugin($id){
    	$result = &$this->connection->execute("Select p.* from plugins p left join plugins_activated a on p.pluginid=a.pluginid where a.poiid=? and p.pluginid=? and p.plugintype='Administration' and (a.pluginid is null)",array(PLUGIN_ADMINISTRATION_POIID,$id));
    	if (!$result){
    		// TODO: Fehlermeldung ausgeben
    		return null;
    	}
    	else {
    		if (!$result->EOF) {
    			$pluginclassname = $result->fields("pluginclassname");
    			$pluginpath = $result->fields("pluginpath");
            	// Klasse instanziieren
            	$plugin = PluginEngine::instantiatePlugin($pluginclassname,$pluginpath);
            	if ($plugin != null){
	            	$plugin->setPluginid($result->fields("pluginid"));
	            	$plugin->setPluginname($result->fields("pluginname"));
	            	$plugin->setUser($this->getUser());
            	}
        	}
        	$result->Close();
        	return $plugin;
    	}
    }
}
?>
