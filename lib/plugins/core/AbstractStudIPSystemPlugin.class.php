<?php

  /*
   * AbstractStudIPSystemPlugin.class.php - abstract superclass for legacy
   *                                        system plugins
   *
   * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
   *
   * This program is free software; you can redistribute it and/or
   * modify it under the terms of the GNU General Public License as
   * published by the Free Software Foundation; either version 2 of
   * the License, or (at your option) any later version.
   */



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

class AbstractStudIPSystemPlugin extends AbstractStudIPLegacyPlugin{

        var $display_type;

	function AbstractStudIPSystemPlugin(){
		parent::AbstractStudIPLegacyPlugin();
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
    function getDisplayType ($filter = -1) {
        return $this->display_type & $filter;
    }


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display_action($action) {

    $GLOBALS['CURRENT_PAGE'] = $this->getDisplayTitle();

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET["plugin_subnavi_params"];

    // let the plugin show its view
    StudIPTemplateEngine::startContentTable();
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    // close the page
    include 'lib/include/html_end.inc.php';
    page_close();
  }
}
