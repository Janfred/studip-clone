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
    	$stmt = DBManager::get()->prepare(
    	  "SELECT p.* FROM plugins p WHERE p.pluginid=? AND p.pluginid IN ".
    	  "(SELECT rp.pluginid FROM roles_plugins rp WHERE rp.roleid IN (".
    	  "SELECT r.roleid FROM roles_user r WHERE r.userid=? ".
    	  "UNION ".
    	  "SELECT rp.roleid FROM roles_studipperms rp, auth_user_md5 a ".
    	  "WHERE rp.permname = a.perms AND a.user_id=?)) ".
    	  "AND p.plugintype='Homepage'");
    	$stmt->execute(array($id, $userid, $userid));
  		$row = $stmt->fetch();
  		if ($row === FALSE) {
  			$pluginclassname = $row["pluginclassname"];
  			$pluginpath = $row["pluginpath"];
  			// Klasse instanziieren
  			$plugin = PluginEngine::instantiatePlugin($pluginclassname,
  			                                          $pluginpath);
  			if ($plugin != null) {
  			  $plugin->setPluginid($result->fields("pluginid"));
  			  $plugin->setPluginname($result->fields("pluginname"));
  			  $plugin->setUser($this->getUser());
  			}
     }
     return $plugin;
   }
}
