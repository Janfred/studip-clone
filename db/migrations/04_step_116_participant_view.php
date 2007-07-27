<?
class Step116ParticipantView extends DBMigration {

	function description () {
		return 'creates table necessary for StEP116';
	}

	function up () {
		set_time_limit(0);
		$this->announce(" creating table...");
		$this->db->query("
      CREATE TABLE IF NOT EXISTS `teilnehmer_view` (
        `datafield_id` varchar(40) NOT NULL default '',
        `seminar_id` varchar(40) NOT NULL default '',
        `active` tinyint(4) default NULL,
        PRIMARY KEY  (`datafield_id`,`seminar_id`)
      ) ENGINE=MyISAM
		");
		
		$this->announce("done.");
		
	}
	
	function down () {
		set_time_limit(0);
		$this->announce(" removing table...");
		$this->db->query("
      DROP TABLE `teilnehmer_view` 
		");
		
		$this->announce("done.");
		
	}
}
?>
