<?php

/*
 * router.php - Routing requests to appropriate controller
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Parses and maps requests to controllers and actions.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: router.php 5838 2007-05-31 09:07:03Z mlunzena $
 */

class Trails_Router {


  var $dispatcher;


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function Trails_Router(&$dispatcher) {
    $this->dispatcher =& $dispatcher;
  }


  /**
   * Parses given URL and returns an array of controllers, action and parameters
   * taken from that URL.
   *
   * @todo
   * @param string URL to be parsed
   * @return array
   */
  function parse($url) {

    $out = array();

    # remove everything after a '?'
    if (FALSE !== strpos($url, '?'))
      $url = substr($url, 0, strpos($url, '?'));

    # remove leading slash
    if ($url[0] == '/')
      $url = substr($url, 1);

    $request = explode('/', $url);

    ### get controller ###

    # load default controller
    if ($url == '') {
      $controller = $this->dispatcher->default_controller;
    }

    # check tokens
    else {

      $controller = '';
      $found = FALSE;

      do {
        # get next token
        $token = current($request);
        if (FALSE === $token) break;
        next($request);

        # check sanity
        if (!preg_match('/^[a-z0-9\-_]+$/', $token)) break;
        $controller .= ($controller == '' ? '' : '/') . $token;

        $found = is_readable($this->dispatcher->get_path($controller));

      } while (!$found);

      if (!$found)
        return NULL;
    }

    $out['controller'] = $controller;


    # ### get action ###
    $out['action'] = ($token = current($request))
                     ? $token : $this->dispatcher->default_action;


    # ### prepare parameters ###
    $out['args'] = array();
    while (FALSE !== next($request))
      $out['args'][] = current($request);

    return $out;
  }
}
