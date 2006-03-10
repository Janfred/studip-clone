<?php

/**
 * Starting point for system plugins. System plugins can be integrated into the main menu for system wide 
 * functions or can do background tasks like logging without having a menu entry.
 * 
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 */

class AbstractStudIPSystemPlugin extends AbstractStudIPPlugin{
	
	function AbstractStudIPSystemPlugin(){
		parent::AbstractStudIPPlugin();
		$this->pluginengine = PluginEngine::getPluginPersistence("System");
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
}
?>