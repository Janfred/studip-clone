<?php

/*
 * helper.php - Help with other Helpers.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Helper.
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright (c) Authors
 * @version   $Id: helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class Helper {

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return void
   */
  static function use_helper() {
    foreach (func_get_args() as $helper) {
      require_once $helper.'_helper.php';
    }
  }
}
