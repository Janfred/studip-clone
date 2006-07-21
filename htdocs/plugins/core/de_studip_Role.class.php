<?php

/**
 * Rolle
 * @author Dennis Reil, <Dennis.Reil@reil-online.de>
 *
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