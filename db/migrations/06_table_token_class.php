<?php
class Step_0111_admission extends DBMigration {

	function description () {
		return 'creates table admission groups';
	}

	function up () {
		$this->announce(" creating table `admission_group`...");
		
		$this->db->query( "CREATE TABLE IF NOT EXISTS `admission_group` (
						  `group_id` varchar(32) NOT NULL,
						  `name` varchar(255) NOT NULL,
						  `status` tinyint(3) unsigned NOT NULL,
						  `chdate` int(10) unsigned NOT NULL,
						  `mkdate` int(10) unsigned NOT NULL,
						  PRIMARY KEY  (`group_id`)
						) TYPE=MyISAM");
		
		$this->announce("done.");
		
	}
	
	function down () {
		$this->announce(" removing table `admission_group`...");
		$this->db->query("DROP TABLE IF EXISTS `admission_group` ");
		$this->announce("done.");
	}
}
?>
