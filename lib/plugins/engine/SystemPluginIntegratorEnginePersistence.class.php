<?php

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage engine
 */

class SystemPluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

    /**
     * Liefert alle in der Datenbank bekannten Plugins zurück
     */
    function getAllInstalledPlugins(){
      // nur Standard-Plugins liefern
      $plugins = parent::executePluginQuery("where plugintype='System'");
      return $plugins;
    }

    /**
     * Returns all activated system plugins
     * @return all activated system plugins
     */
    function getAllActivatedPlugins(){
      // return all activated system plugins
      $plugins = parent::executePluginQuery("where plugintype='System' and enabled='yes'");
      return $plugins;
    }

    function getPlugin($id) {
      $stmt = DBManager::get()->prepare("SELECT p.* FROM plugins p ".
        "WHERE p.pluginid=? AND p.plugintype='System'");
      $stmt->execute(array($id));
      $row = $stmt->fetch();
      if ($row !== FALSE) {
        $pluginclassname = $row["pluginclassname"];
        $pluginpath = $row["pluginpath"];
        // Klasse instanziieren
        $plugin = PluginEngine::instantiatePlugin($pluginclassname, $pluginpath);
        if ($plugin != null) {
          $plugin->setPluginid($row["pluginid"]);
          $plugin->setPluginname($row["pluginname"]);
          $plugin->setUser($this->getUser());
        }
      }
      return $plugin;
    }
}
