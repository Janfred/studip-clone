<?php

// require_once('vendor/adodb/adodb.inc.php');

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
