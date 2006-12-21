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
 * @version   $Id: dispatcher.php 4191 2006-10-24 10:43:31Z mlunzena $
 */

class Trails_Dispatcher {

  /**
   * <MethodDescription>
   *
   * @param string The requested URI.
   *
   * @return void
   */
  function dispatch() {

    $request_uri = substr($_SERVER['REQUEST_URI'],
                          strlen(dirname($_SERVER['PHP_SELF'])));

    # instantiate dispatcher
    $dispatcher =&  new Trails_Dispatcher();

    # get controller, action and args from router
    $router =&  new Trails_Router();
    # list($controller_path, $action, $args) = $router->parse($request_uri);
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
    return TRAILS_ROOT . sprintf('app/controllers/%s.php', $controller_path);
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
    require_once Trails_Dispatcher::get_path($controller_path);
    $class = Trails_Inflector::camelize($controller_path) . 'Controller';

    # class found; instantiate
    if (class_exists($class))
      $obj =&  new $class($controller_path);

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
   * <MethodDescription>
   *
   * @param array ToDo
   *
   * @return void
   */
  function method_missing($request) {
    header('HTTP/1.0 404 Not Found');
    ?>
    <h1>Method missing</h1>
    <pre><? var_dump($request) ?></pre>
    <?
    exit;
  }
}
