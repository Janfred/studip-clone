<?php

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class HomepagePluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

    /**
     * Liefert alle in der Datenbank bekannten Plugins zurück
     */
    function getAllInstalledPlugins(){
    	// nur Standard-Plugins liefern
    	$plugins = parent::executePluginQuery("where plugintype='Homepage'");
    	return $plugins;
    }

    /**
     * Returns all activated system plugins
     * @return all activated system plugins
     */
    function getAllActivatedPlugins(){
    	// return all activated system plugins
    	$plugins = parent::executePluginQuery("where plugintype='Homepage' and enabled='yes'");
    	return $plugins;
    }

    function getPlugin($id){
    	$user = $this->getUser();
    	$userid = $user->getUserid();
    	$result = &$this->connection->execute("Select p.* from plugins p where p.pluginid=? and p.pluginid in (select rp.pluginid from roles_plugins rp where rp.roleid in (SELECT r.roleid FROM roles_user r where r.userid=? union select rp.roleid from roles_studipperms rp,auth_user_md5 a where rp.permname = a.perms and a.user_id=?))  and p.plugintype='Homepage'",array($id,$userid,$userid));
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
