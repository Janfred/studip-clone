<?php
# Lifter007: TODO
# Lifter003: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface StudIPSystemPlugin {

  /**
   * A system plugin can do system tasks like logging in the background.
   * This function
   *
   * @return boolean    true - plugin should be called for background task
   *                    false - plugin has no background task
   */
  function hasBackgroundTasks();

  /**
   * abstract function for doing all background tasks
   *
   * @return void
   */
  function doBackgroundTasks();

  /**
   * returns the score which the current user get's for activities in this
   * plugin
   *
   * @return integer    <description>
   */
  function getScore();

  /**
   * define where the plugin will be visible (toolbar and/or start page)
   *
   * @param  type       <description>
   *
   * @return void
   */
  function setDisplayType($display_type);


  /**
   * returns where the plugin will be visible (toolbar and/or start page)
   *
   * @param  type       optional, default: -1
   *
   * @return type       <description>
   */
  function getDisplayType($filter = -1);
}
