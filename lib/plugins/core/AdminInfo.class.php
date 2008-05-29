<?php
# Lifter002: TODO

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AdminInfo {

	var $desc;
	var $msg_pre_warning;
	var $msg_warning;
	var $msg_activate;
	var $msg_deactivate;

    function AdminInfo() {
    	$this->desc = "";
    	$this->msg_pre_warning = _("Achtung: Beim Deaktivieren dieses Plugins gehen möglicherweise Einstellungen verloren.");
    	$this->msg_warning = _("");
    	$this->msg_activate = _("Dieses Plugin kann jederzeit aktiviert werden.");
    	$this->msg_deactivate = _("Dieses Plugin kann jederzeit deaktiviert werden");
    }

    function getDesc(){
    	return $this->desc;
    }

    function setDesc($newdesc){
    	$this->desc = $newdesc;
    }

    function getMsg_pre_warning(){
    	return $this->msg_pre_warning;
    }

    function setMsg_pre_warning($newmsg){
    	$this->msg_pre_warning = $newmsg;
    }

    function getMsg_warning(){
    	return $this->msg_warning;
    }

    function setMsg_warning($newmsg){
    	$this->msg_warning = $newmsg;
    }

    function getMsg_activate(){
    	return $this->msg_activate;
    }

    function setMsg_activate($newmsg){
    	$this->msg_activate = $newmsg;
    }

    function getMsg_deactivate(){
    	return $this->msg_deactivate;
    }

    function setMsg_deactivate($newmsg){
    	$this->msg_deactivate = $newmsg;
    }

    function getWarningBeforeDeactivation(){
    	return $this->getMsg_deactivate();
    }

    function getWarningBeforeActivation(){
    	return $this->getMsg_activate();
    }
}
?>
