<?php

/*
 * component.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * <ClassDescription>
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: component.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class Trails_Component {

  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param string <description>
   * @param array <description>
   *
   * @return string <description>
   */
  function render($classname, $action = 'show', $args = array()) {
    $ctrl =& Trails_Dispatcher::load_controller($classname);
    
    # send action to controller
    return $ctrl->perform($action, $args);
  }
}
