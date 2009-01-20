<?
class AddAdditionalLogActions extends Migration {
	function description () {
		return 'adds new log actions for changing basic data within lectures and enabling and disabling plugins';
	}

	function up () {
		DBManager::get()->query("INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD5('CHANGE_BASIC_DATA'), 'CHANGE_BASIC_DATA', 'Basisdaten geändert', '%user hat in Veranstaltung %sem(%affected) die Daten %info geändert.', 0, NULL)");
		DBManager::get()->query("INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD5('CHANGE_INSTITUTE_DATA'), 'CHANGE_INSTITUTE_DATA', 'Institutdaten geändert', '%user hat in Veranstaltung %sem(%affected) die Daten %info geändert.', 0, NULL)");
		DBManager::get()->query("INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD5('PLUGIN_ENABLE'), 'PLUGIN_ENABLE', 'Plugin einschalten', '%user hat in Veranstaltung %sem(%affected) das Plugin %plugin(%coaffected) aktiviert.', '1', NULL);");
		DBManager::get()->query("INSERT IGNORE INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES( MD5('PLUGIN_DISABLE'), 'PLUGIN_DISABLE', 'Plugin ausschalten', '%user hat in Veranstaltung %sem(%affected) das Plugin %plugin(%coaffected) deaktiviert.', '1', NULL);");
	}

	function down () {
		DBManager::get()->query("DELETE FROM log_actions WHERE action_id = MD5('CHANGE_BASIC_DATA')");
		DBManager::get()->query("DELETE FROM log_actions WHERE action_id = MD5('CHANGE_INSTITUTE_DATA')");
		DBManager::get()->query("DELETE FROM log_actions WHERE action_id = MD5('PLUGIN_ENABLE')");
		DBManager::get()->query("DELETE FROM log_actions WHERE action_id = MD5('PLUGIN_DISABLE')");
	}
}
