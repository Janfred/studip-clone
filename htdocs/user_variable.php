<?php
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));

include "functions.php";
include "statusgruppe.inc.php";

class FakeUser{
	var $user_variables;
	var $user_id = "nobody";
	var $name = "Seminar_User";
	var $session;

	function FakeUser(){
		$this->session = $GLOBALS['sess'];
		if (!is_object($this->session)){
			die("No Session Object found");
		}
	}
	
	function microwaveIt(){
		$this->session->get_lock();
		$vals = $this->session->that->ac_get_value($this->user_id, $this->name);
		$vals = str_replace("\$GLOBALS", "\$this->user_variables", $vals);
		eval(sprintf(";%s",$vals));
	}
	
	function getVariable($name){
		if(!isset($this->user_variables[$name]))
			return false;
		return $this->user_variables[$name];
	}
}

$db=new DB_Seminar;
$db->query("select * from active_sessions WHERE name = 'Seminar_user' AND sid != 'nobody'");
while ($db->next_record()) {

	$test = new FakeUser();
	$test->user_id = $db->f("sid");
	$test->microwaveIt();
	echo "<pre>";
//  print_r($test->getVariable("my_buddies"));

	$my_array = $test->getVariable("my_buddies");
	if (key($my_array)) {
		for (reset ($my_array);
			$mykey = key($my_array);
			next($my_array))
		{
		$user_id = get_userid($mykey);
		if ($user_id != "") {
			$owner_id = $db->f("sid");
			$hash_secret = "kdfhfdfdfgz";
			$contact_id=md5(uniqid($hash_secret));
			$query = "INSERT INTO contact (contact_id,owner_id,user_id) values ('$contact_id', '$owner_id', '$user_id')";
			$db2=new DB_Seminar;
			$db2->query ($query);
			IF  ($db2->affected_rows() > 0) {
				$gruppenname = "Kontaktgruppe ".$my_array[$mykey][group];
				$gruppenid = CheckStatusgruppe ($owner_id, $gruppenname);
				if ($gruppenid == false) {  // die Gruppe gibt es noch nicht, also anlegen
					$gruppenid = AddNewStatusgruppe ($gruppenname, $owner_id,0);
				}
				$writedone = InsertPersonStatusgruppe ($user_id, $gruppenid);  //Person wird zugeordnet
				if ($writedone == true) { // hat alles funktioniert
					echo "<br>Eintrag: ".$mykey;
					echo $gruppenname;
				}
			}
		}
		}
	echo "<hr>";
	}
}

page_close();
?>