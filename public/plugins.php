<?php

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/exceptions/access_denied.php';

# initialize Stud.IP-Session
page_open(array('sess' => 'Seminar_Session',
                'auth' => 'Seminar_Default_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'));

require_once 'lib/seminar_open.php';
require_once 'lib/functions.php';

unregister_globals();

try {

  # get plugin class from request
  $dispatch_to = isset($_SERVER['PATH_INFO']) ?$_SERVER['PATH_INFO'] : '';
  list($plugin_class, $unconsumed) = PluginEngine::routeRequest($dispatch_to);

  # retrieve corresponding plugin id
  $plugin_persistence = PluginEngine::getPluginPersistence();
  $plugin_id = $plugin_persistence->getPluginId($plugin_class);

  PluginEngine::setCurrentPluginId($plugin_id);

  # create an instance of the queried plugin
  $plugin = $plugin_persistence->getPlugin($plugin_id);

  # user is not permitted, show login screen
  if (is_null($plugin)) {
    # TODO (mlunzena) should not getPlugin throw this exception?
    throw new Studip_AccessDeniedException();
  }

  if (is_callable(array($plugin, 'initialize'))) {
    $plugin->initialize();
  }

  # let the show begin
  $plugin->perform($unconsumed);

} catch (Studip_AccessDeniedException $ade) {

    $GLOBALS['auth']->login_if(TRUE);

} catch (Studip_PluginNotFoundException $pnfe) {

  include 'lib/include/html_head.inc.php';
  include 'lib/include/header.php';
  StudIPTemplateEngine::makeHeadline(_("Das angeforderte Plugin ist nicht vorhanden."));
  StudIPTemplateEngine::showErrorMessage($pnfe->getMessage());
  include 'lib/include/html_end.inc.php';
  exit;


} catch (Exception $e) {

  include 'lib/include/html_head.inc.php';
  include 'lib/include/header.php';
  StudIPTemplateEngine::makeHeadline(_("Fehler"));
  StudIPTemplateEngine::showErrorMessage($e->getMessage());
  include 'lib/include/html_end.inc.php';
  exit;

}

