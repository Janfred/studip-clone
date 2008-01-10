<?
class LockRules extends DBMigration {

	function description () {
		return 'creates table for lock rules';
	}

	function up () {
		set_time_limit(0);
		$this->announce(" creating table...");
		
		$this->db->query( "	
			CREATE TABLE `lock_rules` (
				`lock_id` varchar(32) NOT NULL default '',
				`name` varchar(255) NOT NULL default '',
				`description` text NOT NULL,
				`attributes` text NOT NULL,
				PRIMARY KEY  (`lock_id`)
			)");
		
		$this->announce("done.");
		
	}
	
	function down () {
		set_time_limit(0);
		$this->announce(" removing table...");
		$this->db->query("
      DROP TABLE `lock_rules` 
		");
		
		$this->announce("done.");
		
	}
}
?>
