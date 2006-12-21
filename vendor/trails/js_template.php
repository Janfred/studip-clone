<?php

/*
 * js_template.php - Template engine generating Javascript
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'helper/prototype_helper.php';

/**
 * A template engine that renders Javascript templates.
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: js_template.php 4189 2006-10-24 10:42:50Z mlunzena $
 */

class Trails_JSTemplate extends Trails_Template {

  /**
   * Parse, render and return the presentation.
   *
   * @param array  An optional associative array of attributes and their
   *               associated values.
   * @param string A name of a layout template.
   *
   * @return string A string representing the rendered presentation.
   */
  function render($attributes = null, $layout = null) {

    # TODO ?
    header('Content-Type: text/javascript');

    $this->set_layout(null);

    # put attributes into scope
    if (!is_null($attributes))
      $this->attributes = array_merge($this->attributes, $attributes);
    extract($this->attributes, EXTR_REFS);

    # get generator object
    $update_page =& new Trails_JavascriptGenerator();

    # include template, parse it and get output
    ob_start();
    require $this->template;
    ob_end_clean();

    return $update_page->to_s();
  }
}
