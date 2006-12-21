<?php

/*
 * initializer.php - Initialize Trails.
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
 * @version   $Id: initializer.php 4192 2006-10-24 10:57:01Z mlunzena $
 */

class Trails_Initializer {
  
  /**
   * <MethodDescription>simple/index
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function run() {

    # set include path
    $inc_path = ini_get('include_path');
    $inc_path .= PATH_SEPARATOR . TRAILS_ROOT;
    $inc_path .= PATH_SEPARATOR . TRAILS_ROOT . 'vendor';
    ini_set('include_path', $inc_path);

    # init configuration
    $config =& Trails_Config::instance(TRAILS_ROOT . 'config/config.ini.php');

    # set template root
    Trails_Template::ROOT(TRAILS_ROOT . $config->get('template_root'));

    # load adodb
    # TODO
    # require_once 'adodb/adodb.inc.php';
    # require_once 'adodb/adodb-active-record.inc.php';
    
    # init database
    # $db =& ADONewConnection($config->get('database.dsn'));
    # $config->set_by_ref('database.connection', $db);
    # ADOdb_Active_Record::SetDatabaseAdapter($db);
    
    # TODO session start
    # session_start();
    
    # init flash
    # Trails_Flash::fire();
  }
}
