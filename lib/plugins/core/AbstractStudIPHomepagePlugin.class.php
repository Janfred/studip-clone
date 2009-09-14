<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/**
 * Abstract plugin for plugins shown on the homepage of a user
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPHomepagePlugin extends AbstractStudIPLegacyPlugin
  implements HomepagePlugin {

	var $requesteduser; // StudIPUser for which user the homepage should be shown
	var $status_showOverview; // Uebersichtsseite unterdruecken

	function AbstractStudIPHomepagePlugin(){
		parent::AbstractStudIPLegacyPlugin();
		$this->status_showOverview = 1;
	}

	/**
	 * Sets the navigation of this plugin.
	 *
	 * @deprecated
	 */
	function setNavigation(StudipPluginNavigation $navigation) {
		// prepend copy of navigation to its sub navigation
		$first_item_name = key($navigation->getSubNavigation());
		$navigation_copy = clone $navigation;
		$navigation_copy->clearSubmenu();
		$navigation->insertSubNavigation('self', $first_item_name, $navigation_copy);
		$navigation->setTitle($this->getDisplayTitle());

		parent::setNavigation($navigation);

		if (Navigation::hasItem('/homepage')) {
			Navigation::addItem('/homepage/' . $this->getPluginclassname(), $navigation);
		}
	}

	/**
	 * Used to show an overview on the homepage of a user.
	 */
	function showOverview(){
		// has to be implemented
	}

	/**
	 * true:  overviewpage is enabled
	 * false: overviewpage is disabled
	 */
	function getStatusShowOverviewPage(){
		return $this->status_showOverview;
	}

	function setStatusShowOverviewPage($status){
		$oldstatus = $this->status_showOverview;
		$this->status_showOverview = $status;
		return $oldstatus;
	}


	/**
	 * Set the user for which the homepage is rendered
	 *
	 * @param unknown_type $user
	 */
	function setRequestedUser($user){
		if ($user instanceof StudIPUser) {
			$this->requesteduser = $user;
		}
	}

	function getRequestedUser(){
		return $this->requesteduser;
	}


  /**
   * This method sets everything up to perform the given action and
   * displays the results or anything you want to.
   *
   * @param  string the name of the action to accomplish
   *
   * @return void
   */
  function display_action($action) {
    $username = Request::quoted('username', $GLOBALS['auth']->auth['uname']);
    $user_id = get_userid($username);

    if ($user_id == '') {
      throw new Exception(_('Es wurde kein Nutzer unter dem angegebenen Nutzernamen gefunden!').
                          _('Wenn Sie auf einen Link geklickt haben, kann es sein, dass sich der Username des gesuchten Nutzers geändert hat oder der Nutzer gelöscht wurde.'));
    }

    $requser = new StudIPUser($user_id);
    $this->setRequestedUser($requser);

    parent::display_action($action);
  }
}
?>
