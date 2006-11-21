<?php

/**
 * Starting point for creating "normal" course or institute plugins.
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractStudIPPortalPlugin extends AbstractStudIPPlugin {

	function AbstractStudIPPortalPlugin(){
		parent::AbstractStudIPPlugin();
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
}
?>
