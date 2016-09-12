<?php
# Lifter010: TODO
/**
 * user.php - model class for the useradministration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright   2010 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.1
 */

/**
 *
 *
 */
class UserModel
{
    
    /**
     * Merge an user ($old_id) to another user ($new_id).  This is a part of the
     * old numit-plugin.
     *
     * @param string $old_user
     * @param string $new_user
     * @param boolean $identity merge identity (if true)
     *
     * @return array() messages to display after migration
     */
    public static function convert($old_id, $new_id, $identity = false)
    {
        NotificationCenter::postNotification('UserWillMigrate', $old_id, $new_id);

        $messages = array();

        //Identitätsrelevante Daten migrieren
        if ($identity) {
            // Veranstaltungseintragungen
            self::removeDoubles('seminar_user', 'Seminar_id', $new_id, $old_id);
            $query = "UPDATE IGNORE seminar_user SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            self::removeDoubles('admission_seminar_user', 'seminar_id', $new_id, $old_id);
            $query = "UPDATE IGNORE admission_seminar_user SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Persönliche Infos
            $query = "DELETE FROM user_info WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id));

            $query = "UPDATE IGNORE user_info SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Studiengänge
            self::removeDoubles('user_studiengang', 'fach_id', $new_id, $old_id);
            $query = "UPDATE IGNORE user_studiengang SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Eigene Kategorien
            $query = "UPDATE IGNORE kategorien SET range_id = ? WHERE range_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Institute
            self::removeDoubles('user_inst', 'Institut_id', $new_id, $old_id);
            $query = "UPDATE IGNORE user_inst SET user_id = ? WHERE user_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Generische Datenfelder zusammenführen (bestehende Einträge des
            // "neuen" Nutzers werden dabei nicht überschrieben)
            $old_user = User::find($old_id);

            $query = "INSERT INTO datafields_entries
                        (datafield_id, range_id, sec_range_id, content, mkdate, chdate)
                      VALUES (:datafield_id, :range_id, :sec_range_id, :content,
                              UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
                      ON DUPLICATE KEY
                        UPDATE content = IF(content IN ('', 'default_value'), VALUES(content), content),
                               chdate = UNIX_TIMESTAMP()";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':range_id', $new_id);

            $old_user->datafields->each(function ($field) use ($new_id, $statement) {
                $statement->bindValue(':datafield_id', $field->datafield_id);
                $statement->bindValue(':sec_range_id', $field->sec_range_id);
                $statement->bindValue(':content', $field->content);
                $statement->execute();
            });

            # Datenfelder des alten Nutzers leeren
            $old_user->datafields = array();
            $old_user->store();

            //Buddys
            $query = "UPDATE IGNORE contact SET owner_id = ? WHERE owner_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute(array($new_id, $old_id));

            // Avatar
            $old_avatar = Avatar::getAvatar($old_id);
            $new_avatar = Avatar::getAvatar($new_id);
            if ($old_avatar->is_customized()) {
                if (!$new_avatar->is_customized()) {
                    $avatar_file = $old_avatar->getFilename(AVATAR::ORIGINAL);
                    if (!file_exists($avatar_file)) {
                        $avatar_file = $old_avatar->getFilename(AVATAR::NORMAL);
                    }
                    $new_avatar->createFrom($avatar_file);
                }
                $old_avatar->reset();
            }

            $messages[] = _('Identitätsrelevante Daten wurden migriert.');
        }

        // Restliche Daten übertragen

        // ForumsModule migrieren
        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $plugin->migrateUser($old_id, $new_id);
        }

        // Dateieintragungen und Ordner
        // TODO (mlunzena) should post a notification
        $query = "UPDATE IGNORE dokumente SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE folder SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Kalender
        $query = "UPDATE IGNORE calendar_event SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE calendar_user SET owner_id = ? WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE calendar_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE event_data SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE event_data SET editor_id = ? WHERE editor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Archiv
        self::removeDoubles('archiv_user', 'seminar_id', $new_id, $old_id);
        $query = "UPDATE IGNORE archiv_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Evaluationen
        $query = "UPDATE IGNORE eval SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('eval_user', 'eval_id', $new_id, $old_id);
        $query = "UPDATE IGNORE eval_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE evalanswer_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Kategorien
        $query = "UPDATE IGNORE kategorien SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Literatur
        $query = "UPDATE IGNORE lit_catalog SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE lit_list SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Nachrichten (Interne)
        $query = "UPDATE IGNORE message SET autor_id = ? WHERE autor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('message_user', 'message_id', $new_id, $old_id);
        $query = "UPDATE IGNORE message_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // News
        $query = "UPDATE IGNORE news SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE news_range SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Informationsseiten
        $query = "UPDATE IGNORE scm SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Statusgruppeneinträge
        self::removeDoubles('statusgruppe_user', 'statusgruppe_id', $new_id, $old_id);
        $query = "UPDATE IGNORE statusgruppe_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        // Termine
        $query = "UPDATE IGNORE termine SET autor_id = ? WHERE autor_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Votings
        $query = "UPDATE IGNORE vote SET author_id = ? WHERE author_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE vote SET range_id = ? WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('vote_user', 'vote_id', $new_id, $old_id);
        $query = "UPDATE IGNORE vote_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        self::removeDoubles('voteanswers_user', 'answer_id', $new_id, $old_id);
        $query = "UPDATE IGNORE voteanswers_user SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Wiki
        $query = "UPDATE IGNORE wiki SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        $query = "UPDATE IGNORE wiki_locks SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Adressbucheinträge
        $query = "UPDATE IGNORE contact SET owner_id = ? WHERE owner_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        //Blubber
        $query = "UPDATE IGNORE blubber SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_follower SET studip_user_id = ? WHERE studip_user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_mentions SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_reshares SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $query = "UPDATE IGNORE blubber_streams SET user_id = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));

        NotificationCenter::postNotification('UserDidMigrate', $old_id, $new_id);

        $messages[] = _('Dateien, Termine, Adressbuch, Nachrichten und weitere Daten wurden migriert.');
        return $messages;
    }


    /**
     * Delete double entries of the old and new user. This is a part of the old
     * numit-plugin.
     *
     * @param string $table
     * @param string $field
     * @param md5 $new_id
     * @param md5 $old_id
     */
    private static function removeDoubles($table, $field, $new_id, $old_id)
    {
        $items = array();

        $query = "SELECT a.{$field} AS field_item
                  FROM {$table} AS a, {$table} AS b
                  WHERE a.user_id = ? AND b.user_id = ? AND a.{$field} = b.{$field}";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($new_id, $old_id));
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $value) {
            array_push($items, $value['field_item']);
        }

        if (!empty($items)) {
            $query = "DELETE FROM `{$table}`
                  WHERE user_id = :user_id AND `{$field}` IN (:items)";

            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $new_id);
            $statement->bindValue(':items', $items, StudipPDO::PARAM_ARRAY);
            $statement->execute();
        }
    }
}
