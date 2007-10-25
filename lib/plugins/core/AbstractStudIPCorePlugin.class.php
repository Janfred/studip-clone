<?php

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPCorePlugin extends AbstractStudIPLegacyPlugin {


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display($action) {

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET["plugin_subnavi_params"];

    StudIPTemplateEngine::makeHeadline($this->getDisplaytitle(), true,
                                       $this->getPluginiconname());

    StudIPTemplateEngine::startContentTable();
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    // close the page
    include 'lib/include/html_end.inc.php';
    page_close();
	}
}
