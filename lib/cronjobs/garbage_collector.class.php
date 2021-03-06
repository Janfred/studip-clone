<?php
/**
* garbage_collector.class.php
*
* @author André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access public
* @since  2.4
*/
require_once 'lib/classes/CronJob.class.php';

class GarbageCollectorJob extends CronJob
{

    public static function getName()
    {
        return _('Datenbank bereinigen');
    }

    public static function getDescription()
    {
        return _('Entfernt endgültig gelöschte Nachrichten, nicht zugehörige Dateianhänge, abgelaufene Ankündigungen, alte Aktivitäten, veraltete Plugin-Assets sowie veraltete OAuth-Servernonces');
    }

    public static function getParameters()
    {
        return array(
            'verbose' => array(
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden (sind später im Log des Cronjobs sichtbar)'),
            ),
        );
    }

    public function setUp()
    {

    }

    public function execute($last_result, $parameters = array())
    {
        $db = DBManager::get();

        //abgelaufenen News löschen
        $deleted_news = StudipNews::DoGarbageCollect();
        //messages aufräumen
        $to_delete = $db->query("SELECT message_id, count( message_id ) AS gesamt, count(IF (deleted =0, NULL , 1) ) AS geloescht
                FROM message_user GROUP BY message_id HAVING gesamt = geloescht")->fetchAll(PDO::FETCH_COLUMN,0);
        if (count($to_delete)) {
            $db->exec("DELETE FROM message_user WHERE message_id IN(" . $db->quote($to_delete) . ")");
            $db->exec("DELETE FROM message WHERE message_id IN(" . $db->quote($to_delete) . ")");
        }

        //delete old attachments of non-sent and deleted messages:
        //A folder is old and not attached to a message when it has the
        //range type 'message', belongs to the folder type 'MessageFolder',
        //is older than 2 hours and has a range-ID that doesn't exist
        //in the "message" table.
        $unsent_attachment_folders = Folder::deleteBySql(
            "folder_type = 'MessageFolder'
            AND
            range_type = 'message'
            AND
            chdate < UNIX_TIMESTAMP(DATE_ADD(NOW(),INTERVAL -2 HOUR))
            AND
            range_id NOT IN (
                SELECT message_id FROM message
            )",
            [
                'user_id' => $GLOBALS['user']->id
            ]
        );


        if ($parameters['verbose']) {
            printf(_("Gelöschte Ankündigungen: %u") . "\n", (int)$deleted_news);
            printf(_("Gelöschte Nachrichten: %u") . "\n", count($to_delete));
            printf(_("Gelöschte Dateianhänge: %u") . "\n", count($unsent_attachment_folders));
        }

        PersonalNotifications::doGarbageCollect();

        \Studip\Activity\Activity::doGarbageCollect();

        // clean db cache
        $cache = new StudipDbCache();
        $cache->purge();

        // Remove old plugin assets
        PluginAsset::deleteBySQL('chdate < ?', array(time() - PluginAsset::CACHE_DURATION));

        // Remove expired oauth server nonces
        $query = "DELETE FROM `oauth_server_nonce`
                  WHERE `osn_timestamp` < UNIX_TIMESTAMP(NOW() - INTERVAL 6 HOUR)";
        $removed = DBManager::get()->exec($query);

        if ($removed > 0 && $parameters['verbose']) {
            printf(_('Gelöschte Server-Nonces: %u') . "\n", (int)$removed);
        }
    }
}
