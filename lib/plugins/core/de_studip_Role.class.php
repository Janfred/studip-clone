<?php
# Lifter002: TODO

/**
 * Rolle
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class de_studip_Role {
	var $roleid;
	var $rolename;

	function de_studip_Role(){
		$this->roleid = UNKNOWN_ROLE_ID;
		$this->rolename = "";
	}

	function getRoleid(){
		return $this->roleid;
	}

	function setRoleid($newid){
		$this->roleid = $newid;
	}

	function getRolename(){
		return $this->rolename;
	}

	function setRolename($newrole){
		$this->rolename = $newrole;
	}
}

?>
