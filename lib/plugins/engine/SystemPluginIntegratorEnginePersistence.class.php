<?php
# Lifter002: TODO

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
      $plugins = parent::executePluginQuery("plugintype='System'");
      return $plugins;
    }

    /**
     * Returns all activated system plugins
     * @return all activated system plugins
     */
    function getAllActivatedPlugins(){
      // return all activated system plugins
      $plugins = parent::executePluginQuery("plugintype='System' and enabled='yes'");
      return $plugins;
    }
}
