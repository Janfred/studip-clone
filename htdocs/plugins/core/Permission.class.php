<?php

/**
* The permission of an object, usually a user.
* @author Dennis Reil, <dennis.reil@offis.de>
* @version $Revision$
* @package pluginengine
* $Id$
*/

class Permission {
	var $permissionid;

    function Permission() {
	    $this->permissionid = "guest";
    }
    
    function hasRootPermission(){
    	$perm = $GLOBALS["perm"];
    	return $perm->have_perm("root");
	}    
	
	function hasAdminPermission(){
    	$perm = $GLOBALS["perm"];    	
    	return $perm->have_perm("admin");
	}   
	
	function hasTutorPermission(){
		$perm = $GLOBALS["perm"];
    	return $perm->have_perm("tutor");	 
	}
	
	function hasTeacherPermission(){
		$perm = $GLOBALS["perm"];
    	return $perm->have_perm("dozent");	 
	}	
	
	function hasStudentPermission(){
		$perm = $GLOBALS["perm"];
    	return $perm->have_perm("autor");	 
	}
	
	function isStudent(){
		$perm = $GLOBALS["perm"];
    	return $perm->have_perm("autor") && !$perm->have_perm("dozent");	 
	}
	
	function hasTeacherPermissionInPOI(){
		$perm = $GLOBALS["perm"];
		return $perm->have_studip_perm("dozent",$GLOBALS["SessSemName"][1]);
	}
	
	function hasTutorPermissionInPOI(){
		$perm = $GLOBALS["perm"];
		return $perm->have_studip_perm("tutor",$GLOBALS["SessSemName"][1]);
	}
	
	function hasStudentPermissionInPOI(){
		$perm = $GLOBALS["perm"];
		return $perm->have_studip_perm("autor",$GLOBALS["SessSemName"][1]);
	}
}
?>