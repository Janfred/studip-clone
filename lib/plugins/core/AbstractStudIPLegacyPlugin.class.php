<?php

/*
 * AbstractStudIPLegacyPlugin.class.php - bridge for legacy plugins
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Abstract class for old style plugins in Stud.IP. It implements
 * method #perform() using the template method design pattern. Just implement
 * the #route or #display method in your actual old style plugin to change
 * the plugin's behaviour.
 *
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @version     $Id$
 * @package     pluginengine
 * @subpackage  core
 */

abstract class AbstractStudIPLegacyPlugin extends AbstractStudIPPlugin {


  function AbstractStudIPLegacyPlugin() {
    parent::AbstractStudIPPlugin();
  }


  function actionShow($param = null) {
    return $this->show($param);
  }


  /**
   * @param $subnavigationparam - set if a subnavigation item was clicked.
   *                              The value is plugin dependent and specified
   *                              by the plugins subnavigation link params.
   */
  function show($subnavigationparam = null) {}


  /**
   * This method dispatches and displays all actions. It uses the template
   * method design pattern, so you may want to implement the methods #route
   * and/or #display to adapt to your needs.
   *
   * @param  string  the part of the dispatch path, that were not consumed yet
   *
   * @return void
   */
  function perform($unconsumed_path) {

    # get action
    list($action, $this->unconsumed_path) = $this->route($unconsumed_path);

    # it's action time
    try {

      ob_start();

      $this->display_action($action);
      ob_end_flush();

    } catch (Exception $e) {

      # disable output buffering
      while (ob_get_level()) {
        ob_end_clean();
      }

      # defer exception handling
      throw $e;
    }
  }


  /**
   * Called by #perform to detect the action to be accomplished.
   *
   * @param  string  the part of the dispatch path, that were not consumed yet
   *
   * @return string  the name of the instance method to be called
   */
  function route($unconsumed_path) {

    $tokens = preg_split('@/@', $unconsumed_path, -1, PREG_SPLIT_NO_EMPTY);
    $action = 'action' . array_shift($tokens);

    $class_methods = array_map('strtolower', get_class_methods($this));
    if (!in_array(strtolower($action), $class_methods)) {
      throw new Exception(_("Das Plugin verfügt nicht über die gewünschte Operation"));
    }

    return array($action, join('/', $tokens));
  }


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string  the name of the action to accomplish
   * @param  string  the unconsumed rest
   *
   * @return void
   */
  abstract function display_action($action);
}

