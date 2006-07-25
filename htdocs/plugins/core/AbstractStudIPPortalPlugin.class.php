<?php
/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version 1.0
 *
 */
class AbstractStudIPPortalPlugin extends AbstractStudIPPlugin {
			
	function AbstractStudIPPortalPlugin(){
		parent::AbstractStudIPPlugin();		
	}
	
	/**
	 * Used to show an overview on the start page or portal page
	 *
	 */
	function showOverview(){		
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
}
?>