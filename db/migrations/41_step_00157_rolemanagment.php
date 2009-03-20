<?
class Step00157RoleManagment extends Migration
{
    function description ()
    {
        return 'rename de_studip_core_RoleManagementPlugin to RoleManagementPlugin';
    }

    function up ()
    {
        DBManager::get()->exec("DELETE FROM `plugins` WHERE `pluginclassname`='de_studip_core_UserManagementPlugin'");
    	DBManager::get()->exec("UPDATE `plugins` SET `pluginid` = '2', `pluginclassname` = 'RoleManagementPlugin', `navigationpos` = '1' WHERE `pluginclassname`='de_studip_core_RoleManagementPlugin'");
    	DBManager::get()->exec("UPDATE `plugins_activated` SET `pluginid`='2' WHERE `pluginid`='3'");
        DBManager::get()->exec("DELETE FROM `roles_plugins` WHERE `pluginid`='3'");
	}

    function down ()
    {
     	DBManager::get()->exec("UPDATE `plugins` SET `pluginid` = '3', `pluginclassname` = 'de_studip_core_RoleManagementPlugin', `navigationpos` = '2' WHERE `pluginid`=2");
    	DBManager::get()->exec("INSERT INTO `plugins` (`pluginid`, `pluginclassname`, `pluginpath`, `pluginname`, `plugindesc`, `plugintype`, `enabled`, `navigationpos`, `dependentonid`) VALUES (2, 'de_studip_core_UserManagementPlugin', 'core', 'UserManagement', '', 'Core', 'yes', 1, 1)");
    	DBManager::get()->exec("UPDATE `plugins_activated` SET `pluginid`='3' WHERE `pluginid`='2'");
    }
}
?>
