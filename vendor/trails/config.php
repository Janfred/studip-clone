<?php

/*
 * config.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Config file parsing and handling, acts as a registry for config data.
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: config.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class Trails_Config {

  /**
   * @ignore
   */
  var $properties, $file_name;

  /**
   * Constructor
   *
   * @param string <description>
   *
   * @return void
   */
  function Trails_Config($file_name = '') {
    
    # set file_name
    $this->file_name = $file_name;
    
    # set properties
    $this->properties = array();
    
    # load config
    if ($file_name !== '') {
      $config =& $this->load($file_name);
      $this->replace($config);
    }
  }

  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return object <description>
   */
  function &instance($file_name = '') {
    static $instance;
    if (!isset($instance))
      $instance = new Trails_Config($file_name);
    return $instance;
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function &get($key) {
    return $this->properties[$key];
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return void
   */
  function set($key, $value) {
    $this->properties[$key] = $value;
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return void
   */
  function set_by_ref($key, &$value) {
    $this->properties[$key] =& $value;
  }
  
  /**
   * Return an array of all Config properties.
   *
   * @return array <description>
   */
  function &get_all() {
    return $this->properties;
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function merge(&$values) {
    $this->properties = array_merge($this->properties, $values);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function replace(&$values) {
    $this->properties =& $values;
  }
  
  /**
   * <MethodDescription>
   *
   * @return string <description>
   */
  function get_file_name() {
    return $this->file_name;
  }
  
  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return array <description>
   */
  function &load($file_name = '') {

    # init result
    $config = array();

    # load config file
    $file_name = $file_name == '' ? $this->file_name : $file_name;
    if (is_readable($file_name))
      include $file_name;
    else
      trigger_error('No such file: "' . $file_name . '".');

    return $config;
  }

  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return int returns the amount of bytes that were written to the file.
   */
  function save($file_name = '') {
    
    if ($file_name == '')
      $file_name = $this->file_name;

    $content = sprintf("<?php\n\$config = %s;\n?>",
                       var_export($this->properties, TRUE));

    $file = fopen($file_name, 'w+');
    
    if (!$file)
      return 0;

    $written = fwrite($file, $content);
    if (FALSE === $written)
      return 0;

    fclose($file);

    return $written;
  }
}
