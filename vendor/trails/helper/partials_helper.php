<?php

/*
 * partials_helper.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * PartialsHelper.
 *
 * @package     trails
 * @subpackage  helper
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class PartialsHelper {


  function render($partial_path, $locals = array()) {

    list($path, $name) = PartialsHelper::partial_pieces($partial_path);

    if (!is_array($locals)) {
      trigger_error('Second argument has to be an array.', E_USER_WARNING);
      $locals = array();
    }

    if (isset($this->attributes[$name])) {
      $locals[$name] = $this->attributes[$name];
    } else if (!isset($locals[$name])) {
      $locals[$name] = NULL;
    }

    return Trails_Template::render_template($path . DIRECTORY_SEPARATOR . $name,
                                            $locals);
  }


  function render_collection($partial_path, $collection, $locals = array()) {

    list($path, $name) = PartialsHelper::partial_pieces($partial_path);
    $template =& Trails_Template::create_template($path . DIRECTORY_SEPARATOR . $name);

    $result = '';
    foreach ($collection as $value) {
      $template->clear_attributes();
      $locals[$name] = $value;
      $result .= $template->render($locals);
    }

    return $result;
  }


  function partial_pieces($partial_path) {
    if (strpos($partial_path, DIRECTORY_SEPARATOR) === FALSE)
      return array(dirname($this->template), $partial_path);

    return array(dirname($partial_path), basename($partial_path));
  }
}
