<?
class AddChangeBasicDataLogAction extends DBMigration {
	function description () {
		return 'adds new log actions for changing basic data within lectures';
	}

	function up () {
		DBManager::get()->query("INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD5('CHANGE_BASIC_DATA'), 'CHANGE_BASIC_DATA', 'Basisdaten geändert', '%user hat in Veranstaltung %sem(%affected) die Daten %info geändert. ', 1, NULL)");
		DBManager::get()->query("INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD('CHANGE_INSTITUTE_DATA'), 'CHANGE_INSTITUTE_DATA', 'Institutdaten geändert', '%user hat in Veranstaltung %sem(%affected) die Daten %info. ', 1, NULL)");


	}

	function down () {
		DBManager::get()->query("DELETE FROM log_actions WHERE action_id = MD5 ('CHANGE_BASIC_DATA')");
		DBManager::get()->query("DELETE FROM log_actions WHERE action_id = MD5 ('CHANGE_INSTITUTE_DATA')");
	}


}
