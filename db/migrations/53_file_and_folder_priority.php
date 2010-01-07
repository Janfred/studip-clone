<?
class FileAndFolderPriority extends Migration {
    function description() {
        return 'adding a column in table dokumente and folder so that they can be sorted (StEP00175 / TIC #851)';
    }

    function up() {
        $db = DBManager::get();
        $db->exec("UPDATE dokumente ADD priority INT(11) NOT NULL DEFAULT 0");
        $db->exec("UPDATE folder ADD priority INT(11) NOT NULL DEFAULT 0");
    }

    function down() {
        $db = DBManager::get();
        $db->exec("ALTER TABLE `dokumente` DROP `priority`");
        $db->exec("ALTER TABLE `folder` DROP `priority`");
    }
}
?>