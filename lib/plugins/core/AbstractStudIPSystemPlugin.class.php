<?php

/**
 * Starting point for system plugins. System plugins can be integrated into the main menu for system wide
 * functions or can do background tasks like logging without having a menu entry.
 *
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

define('SYSTEM_PLUGIN_TOOLBAR',   1);
define('SYSTEM_PLUGIN_STARTPAGE', 2);

class AbstractStudIPSystemPlugin extends AbstractStudIPPlugin{

        var $display_type;

	function AbstractStudIPSystemPlugin(){
		parent::AbstractStudIPPlugin();
		$this->pluginengine = PluginEngine::getPluginPersistence("System");
                $this->display_type = SYSTEM_PLUGIN_TOOLBAR;
	}

	/**
	 * A system plugin can do system tasks like logging in the background.
	 * This function
	 *
	 * @return true - plugin should be called for background task
	 * 		   false - plugin has no background task
	 */
	function hasBackgroundTasks(){
		return false;
	}

	/**
	 * abstract function for doing all background tasks
	 *
	 */
	function doBackgroundTasks(){

	}

	 /**
     * returns the score which the current user get's for activities in this plugin
     *
     */
    function getScore(){
    	return 0;
    }

    /**
     * define where the plugin will be visible (toolbar and/or start page)
     */
    function setDisplayType ($display_type) {
        $this->display_type = $display_type;
    }

    /**
     * returns where the plugin will be visible (toolbar and/or start page)
     */
    function getDisplayType ($filter = ~0) {
        return $this->display_type & $filter;
    }
}
?>
