<?php

/*
 * dispatcher.php - <short-description>
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
 * @version   $Id: dispatcher.php 6141 2007-08-03 09:52:39Z mlunzena $
 */

class Trails_Dispatcher {


  var $trails_root, $trails_uri, $default_controller, $default_action;


  function Trails_Dispatcher($trails_root, $trails_uri,
                             $default_controller, $default_action) {
    $this->trails_root = $trails_root;
    $this->trails_uri = $trails_uri;
    $this->default_controller = $default_controller;
    $this->default_action = $default_action;
  }


  /**
   * <MethodDescription>
   *
   * @param string The requested URI.
   * @param string The requested URI.
   *
   * @return void
   */
  function dispatch($request_uri, $trails_root, $trails_uri, $default_controller, $default_action) {

    # instantiate dispatcher
    $dispatcher =&  new Trails_Dispatcher($trails_root, $trails_uri,
                                          $default_controller,
                                          $default_action);

    # get controller, action and args from router
    $router =& new Trails_Router($dispatcher);
    $route = $router->parse($request_uri);

    if (!$route)
      Trails_Dispatcher::controller_missing($request_uri);


    # load controller
    $controller =& $dispatcher->load_controller($route['controller']);
    if (is_null($controller))
      Trails_Dispatcher::controller_missing($request_uri);


    # send action
    echo $controller->perform($route['action'], $route['args']);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return string <description>
   */
  function get_path($controller_path) {
    return sprintf('%s/controllers/%s.php',
                   $this->trails_root, $controller_path);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return object <description>
   */
  function &load_controller($controller_path) {

    $obj = NULL;

    # load controller
    require_once $this->get_path($controller_path);
    $class = Trails_Dispatcher::camelize($controller_path) . 'Controller';

    # class found; instantiate
    if (class_exists($class))
      $obj =& new $class($this, $controller_path);

    return $obj;
  }


  /**
   * <MethodDescription>
   *
   * @param array <description>
   *
   * @return void
   */
  function controller_missing($request) {
    header('HTTP/1.0 404 Not Found');
    ?>
    <h1>Controller missing</h1>
    <pre><? var_dump($request) ?></pre>
    <pre><? var_dump(debug_backtrace()) ?></pre>
    <?
    exit;
  }


  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore.
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  function camelize($word) {
    return str_replace(' ', '', ucwords(str_replace(array('_', '/'),
                                                    array(' ', ' '),
                                                    $word)));
  }
}
