<?php
/**
	Enthält für die Pluginschnittstelle wichtige Datenbank-Einstellungen
*/
class DBEnvironment extends Environment{
	var $dbtype;
	var $dbhost;
	var $dbuser;
	var $dbpassword;
	var $dbname;
	
	function DBEnvironment(){
		Environment::Environment();
		$this->dbtype = "mysql";
		$this->dbhost = "localhost";
		$this->dbuser = "root";
		$this->dbpassword = "";
		$this->dbname = "test";	
	}
	
	function setDbtype($type){
		$this->dbtype = $type;
	}
	
	function getDbtype(){
		return $this->dbtype;
	}
	
	function setDbhost($host){
		$this->dbhost = $host;
	}
	
	function getDbhost(){
		return $this->dbhost;
	}
	
	function setDbuser($user){
		$this->dbuser = $user;
	}
	
	function getDbuser(){
		return $this->dbuser;
	}
	
	function setDbpassword($password){
		$this->dbpassword = $password;
	}
	
	function getDbpassword(){
		return $this->dbpassword;
	}
	
	function setDbname($db){
		$this->dbname = $db;
	}
	
	function getDbname(){
		return $this->dbname;
	}
	
	
}

?>