<?php
class AddSwitchForDefaultSemester extends Migration {
	function description() {
		return "adds a configuration switch to turn default values on/off into the config table";
	}

	function up() {
		 DBManager::get()->query("INSERT INTO config VALUES ('e5314dab1ae05360d3461841c9fed953', ''," .
		 		" 'WANTED_DEFAULT_VALUES', 'TRUE', 0, 'boolean', 'global', '', 0, ".time().",". time().
				", 'Schalter um default values zu erlauben.', '', '')");
	}

	function down() {
		 DBManager::get()->query("DELETE FROM `config` WHERE `config_id`='e5314dab1ae05360d3461841c9fed953' LIMIT 1 ");
	}
}

?>
