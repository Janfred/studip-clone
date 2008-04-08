<?
class DbOptimierungKontingentierung extends DBMigration {

	function description () {
		return 'adds keys in admission_seminar_studiengang, admission_seminar_user and seminar_user';
	}

	function up () {
		set_time_limit(0);
		$this->announce("add keys...");
		$this->db->query("ALTER TABLE `admission_seminar_studiengang` ADD INDEX `studiengang_id` ( `studiengang_id` )");
		$this->db->query("ALTER TABLE `admission_seminar_user` ADD INDEX `seminar_id` ( `seminar_id`, `studiengang_id`, `status` )");
		$this->db->query("ALTER TABLE `seminar_user` ADD INDEX `Seminar_id` ( `Seminar_id`, `admission_studiengang_id` )");

		$this->announce("done.");
		
	}
	
	function down () {
		$this->announce("delete keys...");
		$this->db->query("ALTER TABLE `admission_seminar_studiengang` DROP INDEX `studiengang_id`");
		$this->db->query("ALTER TABLE `admission_seminar_user` DROP INDEX `seminar_id`");
		$this->db->query("ALTER TABLE `seminar_user` DROP INDEX `Seminar_id`");
		
		$this->announce("done.");
	}
}
?>