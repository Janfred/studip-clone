<?php
# Lifter007: TODO

/*
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * NOTE: This interface will change significantly in Stud.IP 1.11.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

interface SystemPlugin {

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
}
