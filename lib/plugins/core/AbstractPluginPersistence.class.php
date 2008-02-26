<?php

/**
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 * @package pluginengine
 * @subpackage core
 */

class AbstractPluginPersistence {

	var $connection;

    function AbstractPluginPersistence($type, $host, $user, $password, $database){
    	$this->connection = &ADONewConnection($type);
    	// Verbindung zur Datenbank herstellen.
    	$this->connection->Connect($host,$user,$password,$database);
    }

    function getConnection(){
    	return $this->connection;
    }
}
?>
