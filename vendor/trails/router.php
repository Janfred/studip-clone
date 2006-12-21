<?php

/*
 * router.php - Routing requests to appropriate controller
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 * Copyright (c) 2006, Cake Software Foundation, Inc.
 *                     1785 E. Sahara Avenue, Suite 490-204
 *                     Las Vegas, Nevada 89104
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
 * @author    Cake Software Foundation, Inc.
 * @copyright (c) Authors
 * @version   $Id: router.php 3410 2006-05-23 17:00:30Z mlunzena $
 */

class Trails_Router {

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

    # load config
    $config =& Trails_Config::instance();

    # remove everything after a '?'
    if (FALSE !== strpos($url, '?'))
      $url = substr($url, 0, strpos($url, '?'));

    # remove leading slash
    if ($url{0} == '/')
      $url = substr($url, 1);
    
    $request = explode('/', $url);

    ### get controller ###

    # load default controller
    if ($url == '') {
      $controller = $config->get('default_controller');
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
        
        $found = is_readable(Trails_Dispatcher::get_path($controller));

      } while (!$found);

      if (!$found)
        return NULL;
    }

    $out['controller'] = $controller;


    # ### get action ###
    $out['action'] = !($token = current($request))
                     ? $config->get('default_action') : $token;


    # ### prepare parameters ###
    $out['args'] = array();
    while (FALSE !== next($request))
      $out['args'][] = current($request);

    return $out;
  }
}
