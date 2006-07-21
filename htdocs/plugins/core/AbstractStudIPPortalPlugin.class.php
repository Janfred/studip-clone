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
}
?>