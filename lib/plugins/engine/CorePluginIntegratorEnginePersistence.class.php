<?php

/**
 * @author André Noack <noack@data-quest.de>
 * @version $Id: $
 * @package pluginengine
 * @subpackage engine
 */

class CorePluginIntegratorEnginePersistence extends AbstractPluginIntegratorEnginePersistence {

	private static $plugins = array();

	function CorePluginIntegratorEnginePersistence() {
		parent::AbstractPluginIntegratorEnginePersistence();
	}

	function getPluginByNameIfAvailable($pluginclassname){
		if (isset(self::$plugins[$pluginclassname])) {
			return self::$plugins[$pluginclassname];
		}
		try {
			$plugin = $this->getPlugin($this->getPluginId($pluginclassname));
			if($plugin->isEnabled()){
				return self::$plugins[$pluginclassname] = $plugin;
			} else {
				return null;
			}
				
		} catch (Studip_PluginNotFoundException $e) {
			return null;
		}
	}
	
	function getPlugin($id) {
		$plugins = $this->executePluginQuery("p.pluginid=?", array($id), false);
		return count($plugins) === 1 ? $plugins[0] : null;
	}
}
