<?php

/**
 * Starting point for creating "normal" course or institute plugins.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPPortalPlugin extends AbstractStudIPLegacyPlugin {

	function AbstractStudIPPortalPlugin(){
		parent::AbstractStudIPLegacyPlugin();
	}

	/**
	 * Used to show an overview on the start page or portal page
	 * @param is the user already logged in?
	 */
	function showOverview($unauthorizedview=true){
		// has to be implemented
	}

	/**
	 * Does this plugin have an administration page, which should be shown?
	 * This default implementation only shows it for admin or root user.
	 */
	function hasAdministration(){
		$currentuser = $this->getUser();
		$currentperms = $currentuser->getPermission();
		if ($currentperms->hasAdminPermission()){
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Does the plugin have a view for a user not currently logged in.
	 *
	 */
	function hasUnauthorizedView(){
		return false;
	}

	/**
	 *	Does the plugin have a view for a currently logged in user.
	 *
	 * @return unknown
	 */
	function hasAuthorizedView(){
		return true;
	}


  /**
   * This abstract method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display_action($action) {

    $GLOBALS['CURRENT_PAGE'] = $this->getDisplayTitle();

    include 'lib/include/html_head.inc.php';
    include 'lib/include/header.php';

    $pluginparams = $_GET["plugin_subnavi_params"];

    if (in_array($action,
                 array('showConfigurationPage', 'showDescriptionalPage'))
        && $GLOBALS['perm']->have_perm("admin")) {
      include 'lib/include/links_admin.inc.php';
    }

    StudIPTemplateEngine::startContentTable();
    $this->$action($pluginparams);
    StudIPTemplateEngine::endContentTable();

    // close the page
    include 'lib/include/html_end.inc.php';
    page_close();
  }
}
?>
