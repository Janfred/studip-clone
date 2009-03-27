<?
class Tic473Expirationdate extends Migration
{
    function description () {
        return 'adds "expiration" to auth_user_md5';
    }

    function up () {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `auth_user_md5` ADD `expiration` INT( 11 ) NOT NULL ;");
    }

    function down () {
        $db = DBManager::get();

        $db->exec("ALTER TABLE `auth_user_md5` DROP `expiration`");
    }
}
?>
