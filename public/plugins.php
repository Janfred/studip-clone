<?php
/*
 * Central point of access to plugins. Builds the top navigation and shows
 * the result of a plugins show implementation in the middle
 *
 *
 * @author Dennis Reil, CELab <dennis.reil@offis.de>
 * @date 04.07.2005
 * @version $Revision$
 * @package pluginengine
 * $HeadURL$
 * $Revision$
 * $Author$
 */


# initialize Stud.IP-Session
page_open(array('sess' => 'Seminar_Session',
                'auth' => 'Seminar_Default_Auth',
                'perm' => 'Seminar_Perm',
                'user' => 'Seminar_User'));
include 'lib/seminar_open.php';
include 'lib/functions.php';


try {

  # retrieve requestes plugin id and unconsumed path
  # TODO (mlunzena) unconsumed ist so doch nicht gut..
  $plugin_id = PluginEngine::getPluginIdFromRequest($unconsumed);
  if (UNKNOWN_PLUGIN_ID === $plugin_id) {
    throw new Exception(_("Das angeforderte Plugin ist nicht vorhanden."));
  }

  # create an instance of the queried plugin
  $plugin_persistence = PluginEngine::getPluginPersistence();
  $plugin = $plugin_persistence->getPlugin($plugin_id);

  # user is not permitted, show login screen
  if (is_null($plugin)) {
    $GLOBALS['auth']->login_if(TRUE);
  }

  if (is_callable(array($plugin, 'initialize'))) {
    $plugin->initialize();
  }


  # let the show begin
  $plugin->perform($unconsumed);

} catch (Exception $e) {

  include 'lib/include/html_head.inc.php';
  include 'lib/include/header.php';
  StudIPTemplateEngine::makeHeadline(_("Fehler"));
  StudIPTemplateEngine::showErrorMessage($e->getMessage());
  include 'lib/include/html_end.inc.php';
  exit;

}

