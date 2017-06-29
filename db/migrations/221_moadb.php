<?php
/**
 * @author  André Noack <noack@data-quest.de>
 * @license GPL2 or any later version
 *
*/

class Moadb extends Migration
{
    public function description()
    {
        return 'migrates documents to moadb';
    }

    private function insert52aTermsOfUse($db)
    {
        // Copied and modified from cli script add_52a_tables from Stud.IP 3.5:
        $db->exec("INSERT INTO `content_terms_of_use_entries` (`id`, `name`, `position`, `description`, `student_description`, `download_condition`, `icon`, `is_default`, `mkdate`, `chdate`) VALUES
('3RD_PARTY_FALSE', 'Dokument ist frei von Rechten Dritter', 0, '', '', 0, 'check-circle', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('3RD_PARTY_TRUE', 'Dokument ist nicht frei von Rechten Dritter', 1, '', '', 1, 'decline-circle', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('FREE_LICENSE', 'Werk mit freier Lizenz', 3, 'Werke, die unter einer freien Lizenz veröffentlich wurden, d.h. deren Weitergabe und zumeist auch Veränderung ohne Lizenzkosten gestattet ist, dürfen Sie ohne Einschränkungen für den Unterricht zugänglich machen. \n\nTypische Beispiele sind:\n- Open-Access-Publikationen \n- Open Educational Ressources (OER) \n- Werke unter Creative-Commons-Lizenzen (z.B. Wikipedia-Inhalte) \n\nAchtung: Vergewissern Sie sich im Einzelfall, welche Einschränkungen für die Verbreitung und Veränderung die jeweilige Lizenz ggf. enthält.', 'Das Dokument unterliegt einer freien Lizenz. Sie dürfen es weitergeben und unter Beachtung der Details der Lizenz (s. Angaben im Dokument) verändern und in eigene Werke übernehmen.', 0, 'cc', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('NON_TEXTUAL', 'Abbildungen, Fotos, Filme, Musikstücke, Partituren', 5, 'Urheberrechtlich geschützte Werke, die keine Texte sind, dürfen Sie auch ohne gesonderte Lizenz oder Erlaubnis für die Lehre nutzen, wenn folgende Kriterien erfüllt sind:\n\n- Es handelt sich um kleine Teile des Gesamtwerkes (z.B. max. 10% eines Bildbandes, 5 Minuten bei Filmen, Kinofilme erst nach 2 Jahren) \n- Der Teilnehmerkreis ist abgeschlossen (die Datei kann erst heruntergeladen werden, wenn der Zugang zur Veranstaltung geschlossen ist) \n- Das Werk dient der Veranschaulichung im Rahmen der Lehrveranstaltung\n\nZum Hintergrund: Die Bereitstellung erfolgt unter den Erlaubnissen des § 52a UrhG, die notwendige Vergütung erfolgt pauschal über die Länder.', 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', 0, '52a', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('SELFMADE_NONPUB', 'Selbst verfasstes, nicht publiziertes Werk', 2, 'Selbst verfasste Werke dürfen Sie ohne Einschränkungen zugänglich machen, wenn Sie die Verwertungsrechte nicht an einen Verlag abgetreten haben. \nTypische Beispiele sind selbst verfasste:\n - Präsentationsfolien, auch mit Text- und Bildzitaten aus fremden Quellen \n- Übungsaufgaben, Musterlösungen \n- Computer-Programme \n- Literaturlisten, Seminarpläne\n - Vorlesungsskripte \n\nWichtig ist die Beachtung des Zitatrechtes: \nWenn Sie Teile fremder Quellen übernehmen, ist das zulässig, solange diese Teile mit Quelle gekennzeicht werden und Gegenstand einer wissenschaftlichen Auseinandersetzung sind.', 'Das Dokument wird von den Autor/-innen zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben. Für darüber hinaus gehende Erlaubnisse (Weitergabe, Veränderung) wenden Sie sich an die Autor/-innen oder beachten Sie die Hinweise im Dokument.', 0, 'own-license', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('TEXT_NO_LICENSE', 'Publizierte Texte ohne erworbene Lizenz oder gesonderte Erlaubnis', 6, 'Veröffentlichte Texte, für die keine Lizenz erworben wurde und für die keine gesonderte Erlaubnis vorliegt, dürfen unter den Erlaubnissen des § 52a UrhG für Unterrichtsteilnehmer/-innen zugänglich gemacht werden. \nTypische Beispiele für Texte, die Sie nicht mehr ohne Lizenz oder Erlaubnis zum Download anbieten dürfen, sind: \n- deutsche und ausländische Verlagsprodukte (Buchauszüge, Zeitungs- oder Zeitschriftenartikel) \n- Auszüge aus vergriffenen Büchern \n- auf Internetseiten frei zugängliche Texte \n\nZum Hintergrund: Die Bereitstellung erfolgt unter den Erlaubnissen des § 52a UrhG, die notwendige Vergütung erfolgt bis 30. September 2017 pauschal über die Länder.', 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', 1, '52a', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('UNDEF_LICENSE', 'Ungeklärte Lizenz', 7, 'Bitte geben Sie an, welcher Lizenz das hochgeladene Material unterliegt bzw. auf welcher Grundlage Sie es zugänglich machen. Unterbleibt diese Angabe, wird beim Herunterladen auf den ungeklärten Lizenzstatus hingewiesen.', 'Diese Datei enthält Material mit einer ungeklärten Lizenz. Zu Fragen der Nutzung und Weitergabe wenden Sie sich an die Person, die diese Datei hochgeladen hat.', 2, 'question-circle', 1, UNIX_TIMESTAMP(),UNIX_TIMESTAMP()),
('WITH_LICENSE', 'Nutzungserlaubnis oder Lizenz liegt vor', 4, 'Wenn Sie urheberrechtlich geschützte Werke zugänglich machen wollen und keine der anderen Kategorien passt, benötigen Sie eine Erlaubnis oder kostenpflichtige Lizenz des Inhabers der Verwertungsrechte. Das ist bei publizierten Werken der Verlag, bei nicht publizierten Werken der Autor. \n\nTypische Beispiele sind: \n- Zustimmung von Kollegen oder Studierenden zur Weitergabe von Skripten, Seminararbeiten, Referatsfolien \n- Zustimmung eines Verlages zur Nutzung von Werkteilen für die Lehre \n- Verlags-Erlaubnis zur Nutzung eigener publizierter Werke für die Lehre \n- Erworbene Lizenz für die Weitergabe in Lehrveranstaltung (eine einzelne erworbene Kopie reicht nicht aus!) \n\nAchtung: Campus- oder Nationallizenzen erlauben es nicht, dass Sie ein Werk erneut hochladen und somit selbst verbreiten. Verlinken Sie in diesem Fall direkt auf das Angebot Ihrer Bibliothek o.ä.', 'Das Dokument wird zur Nutzung im Rahmen dieser Veranstaltung bereitgestellt. Sie dürfen es für private Zwecke herunterladen und archivieren, nicht jedoch ohne Erlaubnis weitergeben.', 0, 'license', 0, UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");

    }

    private function updateLicenseIds($db)
    {
        //We must convert the old IDs from the document_licenses table
        //to the new IDs from the content_terms_of_use_entries table:
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = '3RD_PARTY_TRUE' WHERE content_terms_of_use_id = '0';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = '3RD_PARTY_FALSE' WHERE content_terms_of_use_id = '1';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'UNDEF_LICENSE' WHERE content_terms_of_use_id = '2';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'SELFMADE_NONPUB' WHERE content_terms_of_use_id = '3';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'FREE_LICENSE' WHERE content_terms_of_use_id = '4';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'WITH_LICENSE' WHERE content_terms_of_use_id = '5';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'NON_TEXTUAL' WHERE content_terms_of_use_id = '6';");
        $db->exec("UPDATE `file_refs` SET content_terms_of_use_id = 'TEXT_NO_LICENSE' WHERE content_terms_of_use_id = '7';");
    }

    public function up()
    {
        $db = DBManager::get();

        try {
            $db->exec("RENAME TABLE files TO _files");
            $db->exec("RENAME TABLE file_refs TO _file_refs");
        } catch (PDOException $e)
        {

        }

        $db->exec("CREATE TABLE IF NOT EXISTS `files` (
                 `id` varchar(32) NOT NULL,
                 `user_id` varchar(32) NOT NULL,
                 `mime_type` varchar(255) NOT NULL DEFAULT '',
                 `name` varchar(255)  NOT NULL,
                 `size` int(10) unsigned NOT NULL,
                 `storage` enum('disk','url')  NOT NULL DEFAULT 'disk',
                 `author_name` varchar(100) NOT NULL DEFAULT '',
                 `mkdate` int(10) unsigned NOT NULL,
                 `chdate` int(10) unsigned NOT NULL,
                 PRIMARY KEY (`id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_refs` (
                 `id` varchar(32) NOT NULL,
                 `file_id` varchar(32) NOT NULL,
                 `folder_id` varchar(32) NOT NULL,
                 `downloads` int(10) unsigned NOT NULL DEFAULT 0,
                 `description` text NOT NULL,
                 `content_terms_of_use_id` varchar(32) NOT NULL,
                 `user_id` varchar(32) NOT NULL DEFAULT '',
                 `name` varchar(255) NOT NULL DEFAULT '',
                 `mkdate` int(10) UNSIGNED NOT NULL DEFAULT 0,
                 `chdate` int(10) UNSIGNED NOT NULL DEFAULT 0,
                 PRIMARY KEY (`id`),
                 KEY `file_id` (`file_id`),
                 KEY `folder_id` (`folder_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `file_urls` (
                 `file_id` varchar(32)  NOT NULL,
                 `url` varchar(4096)  NOT NULL,
                 `access_type` enum('proxy','redirect') NOT NULL DEFAULT 'proxy',
                 PRIMARY KEY (`file_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");
        $db->exec("CREATE TABLE IF NOT EXISTS `folders` (
                 `id` varchar(32)  NOT NULL,
                 `user_id` varchar(32)  NOT NULL,
                 `parent_id` varchar(32)  NOT NULL,
                 `range_id` varchar(32)  NOT NULL,
                 `range_type` varchar(32)  NOT NULL,
                 `folder_type` varchar(255)  NOT NULL,
                 `name` varchar(255)  NOT NULL,
                 `data_content` text  NOT NULL,
                 `description` text  NOT NULL,
                 `mkdate` int(10) unsigned NOT NULL,
                 `chdate` int(10) unsigned NOT NULL,
                 PRIMARY KEY (`id`),
                 KEY `range_id` (`range_id`),
                 KEY `parent_id` (`parent_id`)
                ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC");

        //table for SORM class ContentTermsOfUse:
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `content_terms_of_use_entries` (
            `id` VARCHAR(32) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `position` int(10) unsigned NOT NULL,
            `description` TEXT NOT NULL,
            `student_description` TEXT NOT NULL,
            `download_condition` TINYINT(2) NOT NULL,
            `icon` VARCHAR(128) NOT NULL DEFAULT '',
            `is_default` tinyint(2) unsigned NOT NULL DEFAULT 0,
            `mkdate` int(10) unsigned NOT NULL,
            `chdate` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC"
        );


        //default terms of use entries:
        $this->insert52aTermsOfUse($db);

        //map old 52 license IDs to new terms of use entries:
        $this->updateLicenseIds($db);



        //top folder courses
        $institute_folders = array();
        foreach ($db->query("SELECT i.institut_id as new_range_id,i.name FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.range_id OR MD5(CONCAT(i.institut_id, 'top_folder')) = f.range_id group by i.institut_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = '';
            $folder['mkdate'] = $folder['chdate'] = time();
            $this->migrateFolder($folder, $folder['new_range_id'], 'institute', 'RootFolder');
            $institute_folders[$folder['new_range_id']] = $folder['folder_id'];
        }
        //aka Allgemeiner Dateiordner
        foreach ($db->query("SELECT f.*, i.institut_id as seminar_id FROM `folder` f INNER JOIN `Institute` i ON i.institut_id = f.range_id") as $folder) {
            $folder['range_id'] = $institute_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'institute', 'StandardFolder');
        }
        //other top folders
        foreach ($db->query("SELECT f.*, i.institut_id as seminar_id FROM `folder` f INNER JOIN `Institute` i ON MD5(CONCAT(i.institut_id, 'top_folder')) = f.range_id") as $folder) {
            $folder['range_id'] = $institute_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'institute', 'StandardFolder');
        }
        unset($institute_folders);
        //Blubber folders
        foreach ($db->query("SELECT f.*, a.user_id AS seminar_id, CONCAT_WS(' ', vorname,nachname) as name
                            FROM `folder` f
                            INNER JOIN `auth_user_md5` a ON a.user_id = f.range_id") as $folder) {
            $folder['range_id'] = '';
            $folder['description'] = '';
            $folder['name'] = '';
            $this->migrateFolder($folder, $folder['seminar_id'], 'user', 'StandardFolder');
        }
        $seminar_folders = array();
        foreach ($db->query("SELECT s.seminar_id as new_range_id,s.name FROM `folder` f INNER JOIN `seminare` s ON s.Seminar_id = f.range_id OR MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id group by s.Seminar_id") as $folder) {
            $folder['folder_id'] = md5(uniqid('folders', true));
            $folder['range_id'] = '';
            $folder['user_id'] = $GLOBALS['user']->id;
            $folder['description'] = '';
            $folder['mkdate'] = $folder['chdate'] = time();
            $this->migrateFolder($folder, $folder['new_range_id'], 'course', 'RootFolder');
            $seminar_folders[$folder['new_range_id']] = $folder['folder_id'];
        }
        //aka Allgemeiner Dateiordner
        foreach ($db->query("SELECT f.*, s.Seminar_id as seminar_id FROM `folder` f INNER JOIN `seminare` s ON s.Seminar_id = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'StandardFolder');
        }
        //other top folders
        foreach ($db->query("SELECT f.*, s.Seminar_id as seminar_id FROM `folder` f INNER JOIN `seminare` s ON MD5(CONCAT(s.Seminar_id, 'top_folder')) = f.range_id") as $folder) {
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            if (!$folder['range_id'] ) throw new Exception($folder['seminar_id']);
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'StandardFolder');
        }

        //group folder
        foreach ($db->query("SELECT f.*, s.range_id AS seminar_id FROM `folder` f INNER JOIN `statusgruppen` s ON s.statusgruppe_id = f.range_id") as $folder) {
            $data_content = json_encode(['group' => $folder['range_id']]);
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'CourseGroupFolder', $data_content);
        }
        //issue folder
        foreach ($db->query("SELECT f.*, t.seminar_id AS seminar_id FROM `folder` f INNER JOIN `themen` t ON t.issue_id = f.range_id") as $folder) {
            $data_content = json_encode(['topic_id' => $folder['range_id']]);
            $folder['range_id'] = $seminar_folders[$folder['seminar_id']];
            $this->migrateFolder($folder, $folder['seminar_id'], 'course', 'CourseTopicFolder', $data_content);
        }

        //personal documents
        foreach ($db->fetchFirst("SELECT distinct parent_id FROM `_file_refs` inner join auth_user_md5 where parent_id=user_id") as $user_id) {
            $top_folder_id = $db->fetchColumn("SELECT id FROM folders WHERE range_type = 'user' AND range_id=?", [$user_id]);
            if (!$top_folder_id) {
                $folder = [
                    'folder_id' => md5(uniqid($user_id)),
                    'range_id' => '',
                    'range_type' => 'user',
                    'parent_id' => '',
                    'user_id' => $user_id,
                    'description' => '',
                    'name' => '',
                    'mkdate' => time(),
                    'chdate' => time()
                ];
                $this->migratePersonalFolder($folder, $user_id);
                $top_folder_id = $folder['folder_id'];
            }
            $this->migratePersonalFiles($db->fetchAll("SELECT file_id,id,storage_id,mime_type,user_id,filename,description,mkdate,chdate,downloads,size FROM `_files` inner join _file_refs using(file_id) WHERE parent_id = ? and storage_id<>''", array($user_id)), $top_folder_id);
            $subfolders = $db->fetchAll("SELECT file_id as folder_id,user_id,'{$top_folder_id}' as range_id,name,description,mkdate,chdate FROM _file_refs INNER JOIN _files USING(file_id) WHERE storage_id='' AND parent_id = ?", array($user_id));
            foreach ($subfolders as $one) {
                $this->migratePersonalFolder($one, $user_id);
            }
        }


        //migrate message attachments:
        $this->migrateMessageAttachments();





        //delete configuration variables designed for the old file area:
        /*
        $db->exec(
            "DELETE FROM `config`
            WHERE
            `field` IN
            ('PERSONALDOCUMENT_OPEN_ACCESS',
            'PERSONALDOCUMENT_OPEN_ACCESS_ROOT_PRIVILEDGED',
            'PERSONALDOCUMENT_ENABLE',
            'FILESYSTEM_MULTICOPY_ENABLE',
            'DOCUMENTS_EMBEDD_FLASH_MOVIES')"
        );
        $db->exec("DROP TABLE `doc_filetype`, `doc_filetype_forbidden`, `doc_usergroup_config`, `dokumente`, `files_backend_studip`, `files_backend_url`, `files_share`, `folder`, `_files`, `_file_refs`");
        */
        SimpleORMap::expireTableScheme();
    }




    public function migrateMessageAttachments()
    {
        //First we wipe out all documents with range-ID = 'provisional'.
        //Such documents were meant to be attached to mails but were left
        //unattached... to remain lonely in the database...
        //So it's time to end this misery and delete them!
        /*
        $unattached_documents = StudipDocument::deleteBySql(
            "range_id = 'provisional'"
        );
        */

        $db = DBManager::get();

        //then we retrieve all message-IDs:
        $message_rows = $db->query("SELECT DISTINCT message_id,autor_id,subject,message.mkdate FROM message INNER JOIN dokumente ON range_id = message_id");

        foreach($message_rows as $message_row) {
            //now we loop through each message ID and check if there are
            //files in the dokumente table with that range-ID:
            $message_id = $message_row['message_id'];

                //we found at least one attachment: create a top folder for this message:
                $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $folder_id = md5($message_id . '_attachments');

                $insert_folder->execute([
                    $folder_id,
                    $message_row['autor_id'],
                    '',
                    $message_id,
                    'message',
                    'MessageFolder',
                    $message_row['subject'],
                    '',
                    '',
                    $message_row['mkdate'],
                    $message_row['mkdate']
                ]);

                $this->migrateFiles($db->fetchAll(
                    "SELECT * FROM dokumente WHERE range_id = :range_id",
                    [
                        'range_id' => $message_id
                    ]
                ), $folder_id);

        }
    }


    public function migrateFolder($folder, $range_id, $range_type, $folder_type, $data_content = '')
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $folder_type = $folder_type == 'StandardFolder' && isset($folder['permission']) && $folder['permission'] != 7 ? 'PermissionEnabledFolder' : $folder_type;
        $data_content = $data_content == '' && isset($folder['permission']) && $folder['permission'] != 7 ? json_encode(['permission' => $folder['permission']]): $data_content;
        $insert_folder->execute(array($folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $range_type, $folder_type, $folder['name'], $data_content, (string)$folder['description'], $folder['mkdate'], $folder['chdate']));
        $subfolders = $db->fetchAll("SELECT * FROM folder WHERE range_id = ?", array($folder['folder_id']));
        foreach ($subfolders as $one) {
            $this->migrateFolder($one, $range_id, $range_type, 'StandardFolder');
        }
        $this->migrateFiles($db->fetchAll("SELECT * FROM dokumente WHERE range_id = ?", array($folder['folder_id'])), $folder['folder_id']);



    }

    public function migrateFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file_url = $db->prepare("INSERT INTO `file_urls` (`file_id`, `url`) VALUES (?, ?)");
        $filenames = array();
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute(array(
                $one['dokument_id'],
                $one['dokument_id'],
                $folder_id,
                $one['downloads'],
                $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string) $one['description'],
                $one['protected'],
                $one['user_id'],
                $filename,
                $one['mkdate'],
                $one['chdate']
            ));
            $insert_file->execute(array($one['dokument_id'], $one['user_id'], get_mime_type($one['filename']), $filename, $one['filesize'], $one['url'] ? 'url' : 'disk', $one['author_name'], $one['mkdate'], $one['chdate']));
            if ($one['url']) {
                $insert_file_url->execute(array($one['dokument_id'], $one['url']));
            }
        }
    }

    public function migratePersonalFolder($folder, $range_id)
    {
        $db = DBManager::get();
        $insert_folder = $db->prepare("INSERT INTO `folders` (`id`, `user_id`, `parent_id`, `range_id`, `range_type`, `folder_type`, `name`, `data_content`, `description`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, 'user', 'StandardFolder', ?, '', ?, ?, ?)");

        $insert_folder->execute(array($folder['folder_id'], $folder['user_id'], $folder['range_id'], $range_id, $folder['name'], (string)$folder['description'], $folder['mkdate'], $folder['chdate']));
        $subfolders = $db->fetchAll("SELECT file_id as folder_id,user_id,parent_id as range_id,name,description,mkdate,chdate FROM _file_refs INNER JOIN _files USING(file_id) WHERE storage_id='' AND parent_id = ?", array($folder['folder_id']));
        foreach ($subfolders as $one) {
            $this->migratePersonalFolder($one, $range_id);
        }
        $this->migratePersonalFiles($db->fetchAll("SELECT file_id,id,storage_id,mime_type,user_id,filename,description,mkdate,chdate,downloads,size FROM `_files` inner join _file_refs using(file_id) WHERE parent_id = ? and storage_id<>''", array($folder['folder_id'])), $folder['folder_id']);


    }

    public function migratePersonalFiles($files, $folder_id)
    {
        $db = DBManager::get();
        $insert_file_ref = $db->prepare("INSERT INTO `file_refs` (`id`, `file_id`, `folder_id`, `downloads`, `description`, `content_terms_of_use_id`, `user_id`, `name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_file = $db->prepare("INSERT INTO `files` (`id`, `user_id`, `mime_type`, `name`, `size`, `storage`, `author_name`, `mkdate`, `chdate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $filenames = array();
        foreach ($files as $one) {
            $c = 0;
            $filename = $one['filename'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext) {
                $name = substr($filename, 0, strrpos($filename, $ext) - 1);
            } else {
                $name = $filename;
            }
            while (in_array($filename, $filenames)) {
                $filename = $name . '['.++$c.']' . ($ext ? '.' . $ext : '');
            }
            $filenames[] = $filename;
            $insert_file_ref->execute(array(
                $one['id'],
                $one['storage_id'],
                $folder_id,
                $one['downloads'],
                $one['name'] != $one['filename'] ? trim($one['name'] . "\n" . $one['description']) : (string) $one['description'],
                0,
                $one['user_id'],
                $filename,
                $one['mkdate'],
                $one['chdate']
            ));
            $insert_file->execute(array($one['storage_id'], $one['user_id'], $one['mime_type'], $filename, $one['size'], 'disk', '', $one['mkdate'], $one['chdate']));
            $new_path = $GLOBALS['UPLOAD_PATH'] . '/' . substr($one['storage_id'], 0, 2) . '/' . $one['storage_id'];
            $old_path = $GLOBALS['USER_DOC_PATH'] . '/' . $one['user_id'] . '/' . $one['storage_id'];
            @rename($old_path, $new_path);
        }
    }

    public function down()
    {
        /*
         * I'M SORRY DAVE, I'M AFRAID I CAN'T DO THAT
         */
    }
}
