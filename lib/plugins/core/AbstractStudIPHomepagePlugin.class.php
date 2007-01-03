<?php

/**
 * Abstract plugin for plugins shown on the homepage of a user
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPHomepagePlugin extends AbstractStudIPPlugin {

	var $requesteduser; // StudIPUser for which user the homepage should be shown
	var $status_showOverview; // Uebersichtsseite unterdruecken

	function AbstractStudIPHomepagePlugin(){
		parent::AbstractStudIPPlugin();
		$this->requesteduser = null;
		$this->status_showOverview = 1;
	}

	/**
	 * Used to show an overview on the homepage of a user.
	 *
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
	 * @param unknown_type $newuser
	 */
	function setRequestedUser($newuser){
		if (is_a($newuser,"StudIPUser") || is_subclass_of($newuser,"StudIPUser")){
			$this->requesteduser = $newuser;
		}
	}

	function getRequestedUser(){
		return $this->requesteduser;
	}
}
?>
