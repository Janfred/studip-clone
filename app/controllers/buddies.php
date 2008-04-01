<?php

/*
 * buddies.php - Buddies controller
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once 'lib/contact.inc.php';


class BuddiesController extends Trails_Controller {


  function before_filter($action, &$args) {
    # open session
    page_open(array('sess' => 'Seminar_Session',
                    'auth' => 'Seminar_Auth',
                    'perm' => 'Seminar_Perm',
                    'user' => 'Seminar_User'));
    require_once 'lib/seminar_open.php';
    # user must be logged in
    $GLOBALS['auth']->login_if($_REQUEST['again']
                               && ($GLOBALS['auth']->auth['uid'] == 'nobody'));
  }


  function after_filter($action, &$args) {
    page_close();
  }


  function toggle_publish_action() {
    TogglePublishBuddies($GLOBALS['auth']->auth['uid']);
    $this->redirect($GLOBALS['ABSOLUTE_URI_STUDIP'].'about.php');
  }
}
