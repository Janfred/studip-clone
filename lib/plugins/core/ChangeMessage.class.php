<?php
# Lifter002: TODO
/**
 * Änderungstext zu einem Plugin. (z.B. 13 neue Forumseinträge)
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class ChangeMessage {

	var $id; // Id der Veranstaltung / der Institution zu der diese Nachricht gehört.
	var $message; // Nachricht

    function ChangeMessage($newid,$newmessage) {
    	$this->id = $newid;
    	$this->message = $newmessage;
    }

    function getId(){
    	return $this->id;
    }

    function setId($newid){
    	$this->id = $newid;
    }

    function getMessage(){
    	return $this->message;
    }

    function setMessage($newmessage){
    	$this->message = $newmessage;
    }
}
?>
