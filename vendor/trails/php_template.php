<?php

/*
 * php_template.php - Template engine using PHP
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * A template engine that uses PHP to render templates.
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: php_template.php 4189 2006-10-24 10:42:50Z mlunzena $
 */

class Trails_PHPTemplate extends Trails_Template {

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

    if ($layout) $this->set_layout($layout);

    # put attributes into scope
    if (!is_null($attributes))
      $this->attributes = array_merge($this->attributes, $attributes);
    extract($this->attributes, EXTR_REFS);

    # include template, parse it and get output
    ob_start();
    require $this->template;
    $content_for_layout = ob_get_contents();
    ob_end_clean();

    # include layout, parse it and get output
    if (isset($this->layout)) {
      $defined = get_defined_vars();
      unset($defined['this'], $defined['attributes'], $defined['layout']);
      $content_for_layout = $this->layout->render($defined);
    }

    return $content_for_layout;
  }
}
