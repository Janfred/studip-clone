<?php

/*
 * request.php - <short-description>
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
 * @version   $Id: request.php 4534 2006-11-28 15:25:13Z mlunzena $
 */

class Trails_Request {

  /**
   * @ignore
   */
  var $is_cli, $method;

  /**
   * @ignore
   */
  function Trails_Request() {
    $this->is_cli = !isset($_SERVER['REQUEST_METHOD']);
    $this->method = $this->is_cli ? '' : strtolower($_SERVER['REQUEST_METHOD']);
  }

  /**
   * <MethodDescription>
   *
   * @return object <description>
   */
  function &instance() {
    static $request;
    if (!$request)
      $request = new Trails_Request();
    return $request;
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_cli() {
    return $this->is_cli;
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function set_cli($is_cli) {
    $this->is_cli = $is_cli;
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */

  function is_delete() {
    return $this->method == 'delete';
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_get() {
    return $this->method == 'get';
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_head() {
    return $this->method == 'head';
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_post() {
    return $this->method == 'post';
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_put() {
    return $this->method == 'put';
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_xhr() {
    return $this->is_xml_http_request();
  }

  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function is_xml_http_request() {
    return strstr($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') !== FALSE;
  }

  /**
   * <MethodDescription>
   *
   * @return string <description>
   */
  function relative_url_root() {
    return $this->is_cli() ? '' : dirname($_SERVER['SCRIPT_NAME']);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return mixed <description>
   */
  function clean($arg) {

    $args = func_get_args();

    if (sizeof($args) == 1) {
      if (!is_string($arg))
        trigger_error(sprintf('Argument "%s" is not a string.',
                              var_export($arg, TRUE)),
                      E_USER_ERROR);
      return $this->get_request_var($arg);
    }

    $result = array();

    foreach ($args as $name) {
      if (!is_string($name))
        trigger_error(sprintf('Argument "%s" is not a string.',
                              var_export($name, TRUE)),
                      E_USER_ERROR);
      else
        $result[$name] = $this->get_request_var($name);
    }

    return $result;
  }

  /**
   * @param mixed the name of a request variable.
   *
   * @return mixed the value of the named request variable.
   */
  function get_request_var($var) {
    return is_string($var) && isset($_REQUEST[$var]) ? $_REQUEST[$var] : NULL;
  }
}
