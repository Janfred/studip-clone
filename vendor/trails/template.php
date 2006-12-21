<?php

/*
 * template.php - Template abstraction for Stud.IP
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Abstract template class representing the presentation layer of an action.
 * Output can be customized by supplying attributes, which a template can
 * manipulate and display.
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: template.php 4747 2006-12-20 15:50:16Z mlunzena $
 */

class Trails_Template {

  /**
   * @ignore
   */
  var
    $attributes, $layout, $template;


  /**
   * Constructor
   *
   * @param string A name of a template.
   *
   * @return void
   */
  function Trails_Template($template) {

    # set template
    $this->template = $template;

    # init attributes
    $this->clear_attributes();
  }


  /**
   * Open a template of the given name using the factory method pattern.
   *
   * @deprecated
   *
   * @param string A name of a template.
   *
   * @return Template the factored object
   */
  function &create_template($template0) {

    # if it starts with a slash, it's an absolute path
    $template = $template0[0] != '/'
                ? Trails_Template::ROOT() . $template0
                : $template0;

    # no extension defined, find it
    if (!preg_match('/\w\.\w+$/', $template)) {

      # find templates matching pattern
      $files = glob($template . '.*');

      # no such template
      if (0 == sizeof($files)) {
        trigger_error(sprintf('Could not find template: "%s" (searching "%s").',
                              $template0, Trails_Template::ROOT()),
                      E_USER_ERROR);
        $null = NULL;
        return $null;
      }

      $template = current($files);
    }

    $ext = array_pop(explode('.', $template));
    switch ($ext) {

      case 'php':
        $class = 'Trails_PHPTemplate'; break;

      case 'pjs':
        $class = 'Trails_JSTemplate'; break;

      default:
        trigger_error(sprintf('Could not find class of "%s".', $template),
                      E_USER_ERROR);
        $null = NULL;
        return $null;
    }

    $template =& new $class($template);

    return $template;
  }


  /**
   * Clear all attributes associated with this template.
   *
   * @return void
   */
  function clear_attributes() {
    $this->attributes = array();
  }


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
    trigger_error('Trails_Template::render() must be overridden', E_USER_ERROR);
    exit;
  }


  /**
   * Class method to parse, render and return the presentation of a
   * template.
   *
   * @param string A name of a template.
   * @param array  An associative array of attributes and their associated
   *               values.
   * @param string A name of a layout template.
   *
   * @return string A string representing the rendered presentation.
   */
  function render_template($name, $attributes = null, $layout = null) {
    $component = Trails_Template::create_template($name);
    return $component->render($attributes, $layout);
  }


  /**
   * "Class variable"-like function to set the template root dir.
   *
   * @deprecated
   *
   * @param string the template_root to set.
   *
   * @return string returns the template_root.
   */
  function ROOT($new_root = NULL) {
    static $template_root;

    # getter functionality
    if (is_null($new_root)) {
      if (is_null($template_root))
        $template_root = 'templates/';
    }
    # setter functionality
    else
      $template_root = $new_root;

    return $template_root;
  }


  /**
   * Set an attribute.
   *
   * @param string An attribute name.
   * @param mixed  An attribute value.
   *
   * @return void
   */
  function set_attribute($name, $value) {
    $this->attributes[$name] = $value;
  }


  /**
   * Set an array of attributes.
   *
   * @param array An associative array of attributes and their associated
   *              values.
   *
   * @return void
   */
  function set_attributes($attributes) {
    $this->attributes = array_merge($this->attributes, $attributes);
  }


  /**
   * Set the template's layout.
   *
   * @param string A name of a layout template.
   *
   * @return void
   */
  function set_layout($layout) {
    if ($layout !== NULL)
      $layout =& Trails_Template::create_template('layouts/' . $layout);
    $this->layout =& $layout;
  }
}
