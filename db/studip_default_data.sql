
-- 
-- Daten für Tabelle `config`
-- 

INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('7291d64d9cc4ea43ee9e8260f05a4111', '', 'MAIL_NOTIFICATION_ENABLE', '0', 1, 'boolean', 'global', '', 0, 1122996278, 1122996278, 'Informationen über neue Inhalte per email verschicken', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('9f6d7e248f58d1b211314dfb26c77d63', '', 'RESOURCES_ALLOW_DELETE_REQUESTS', '0', 1, 'boolean', 'global', '', 0, 1136826903, 1136826903, 'Erlaubt das Löschen von Raumanfragen für globale Ressourcenadmins', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('25bdaf939c88ee79bf3da54165d61a48', '', 'MAINTENANCE_MODE_ENABLE', '0', 1, 'boolean', 'global', '', 0, 1130840930, 1130840930, 'Schaltet das System in den Wartungsmodus, so dass nur noch Administratoren Zugriff haben', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('88c038ca4fb36764ff6486d72379e1ae', '', 'ZIP_UPLOAD_MAX_FILES', '100', 1, 'integer', 'global', '', 0, 1130840930, 1130840930, 'Die maximale Anzahl an Dateien, die bei einem Zipupload automatisch entpackt werden', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('c1f9ef95f501893c73e2654296c425f2', '', 'ZIP_UPLOAD_ENABLE', '1', 1, 'boolean', 'global', '', 0, 1130840930, 1130840930, 'Ermöglicht es, ein Zip Archiv hochzuladen, welches automatisch entpackt wird', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('d733eb0f9ef6db9fb3b461dd4df22376', '', 'ZIP_UPLOAD_MAX_DIRS', '10', 1, 'integer', 'global', '', 0, 1130840962, 1130840962, 'Die maximale Anzahl an Verzeichnissen, die bei einem Zipupload automatisch entpackt werden', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('1c07aa46c6fe6fea26d9b0cfd8fbcd19', '', 'SENDFILE_LINK_MODE', 'normal', 1, 'string', 'global', '', 0, 1141212096, 1141212096, 'Format der Downloadlinks: normal=sendfile.php?parameter=x, old=sendfile.php?/parameter=x, rewrite=download/parameter/file.txt', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('9d4956b4eac20f03b60b17d7ac30b40a', '', 'SEMESTER_TIME_SWITCH', '0', 1, 'integer', 'global', '', 0, 1140013696, 1140013696, 'Anzahl der Wochen vor Semesterende zu dem das vorgewählte Semester umspringt', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('06cdb765fb8f0853e3ebe08f51c3596e', '', 'RESOURCES_ENABLE', '0', 1, 'boolean', 'global', '', 0, 0, 0, 'Enable the Stud.IP resource management module', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('3d415eca6003321f09e59407e4a7994d', '', 'RESOURCES_LOCKING_ACTIVE', '', 1, 'boolean', 'global', 'resources', 0, 0, 1100709567, 'Schaltet in der Ressourcenverwaltung das Blockieren der Bearbeitung für einen Zeitraum aus (nur Admins dürfen in dieser Zeit auf die Belegung zugreifen)', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('b7a2817d142443245df2f5ac587fe218', '', 'RESOURCES_ALLOW_ROOM_REQUESTS', '', 1, 'boolean', 'global', '', 0, 0, 1100709567, 'Schaltet in der Ressourcenverwaltung das System zum Stellen und Bearbeiten von Raumanfragen ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('d821ffbff29ce636c6763ffe3fd8b427', '', 'RESOURCES_ALLOW_CREATE_ROOMS', '2', 1, 'integer', 'global', '', 0, 0, 1100709567, 'Welche Rechstufe darf  Räume anlegen? 1 = Nutzer ab Status tutor, 2 = Nutzer ab Status admin, 3 = nur Ressourcenadministratoren', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('5a6e2342b90530ed50ad8497054420c0', '', 'RESOURCES_ALLOW_ROOM_PROPERTY_REQUESTS', '1', 1, 'boolean', 'global', '', 0, 0, 1074780851, 'Schaltet in der Ressourcenverwaltung die Möglichkeit, im Rahmen einer Anfrage Raumeigenschaften zu wünschen, ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('e4123cf9158cd0b936144f0f4cf8dfa3', '', 'RESOURCES_INHERITANCE_PERMS_ROOMS', '1', 1, 'integer', 'global', '', 0, 0, 1100709567, 'Art der Rechtevererbung in der Ressourcenverwaltung für Räume: 1 = lokale Rechte der Einrichtung und Veranstaltung werden übertragen, 2 = nur Autorenrechte werden vergeben, 3 = es werden keine Rechte vergeben', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('45856b1e3407ce565d87ec9b8fd32d7d', '', 'RESOURCES_INHERITANCE_PERMS', '1', 1, 'integer', 'global', '', 0, 0, 1100709567, 'Art der Rechtevererbung in der Ressourcenverwaltung für Ressourcen (nicht Räume): 1 = lokale Rechte der Einrichtung und Veranstaltung werden übertragen, 2 = nur Autorenrechte werden vergeben, 3 = es werden keine Rechte vergeben', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('c353c73d8f37e3c301ae34898c837af4', '', 'RESOURCES_ENABLE_ORGA_CLASSIFY', '1', 1, 'boolean', 'global', '', 0, 0, 1100709567, 'Schaltet in der Ressourcenverwaltung das Einordnen von Ressourcen in Orga-Struktur (ohne Rechtevergabe) ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('0821671742242add144595b1112399fb', '', 'RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE', '50', 1, 'integer', 'global', '', 0, 0, 1100709567, 'Wert (in Prozent), ab dem ein Raum mit Einzelbelegungen (statt Serienbelegungen) gefüllt wird, wenn dieser Anteil an möglichen Belegungen bereits durch andere Belegungen zu Überschneidungen führt', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('94d1643209a8f404dfe71228aad5345d', '', 'RESOURCES_ALLOW_SINGLE_DATE_GROUPING', '5', 1, 'integer', 'global', '', 0, 0, 1100709567, 'Anzahl an Einzeltermine, ab der diese als Gruppe zusammengefasst bearbeitet werden', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('074ccc86f0313dd695dc8e3ec3cebe73', '', 'HTML_HEAD_TITLE', 'Stud.IP', 1, 'string', 'global', '', 0, 0, 0, 'Angezeigter Titel in der Kopfzeile des Browsers', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('f2f8a47ea69ed9ccba5573e85a15662c', '', 'ACCESSKEY_ENABLE', '', 1, 'boolean', 'user', '', 0, 0, 0, 'Schaltet die Nutzung von Shortcuts für einen User ein oder aus, Systemdefault', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('0b00c75bc76abe0dd132570403b38e5c', '', 'NEWS_RSS_EXPORT_ENABLE', '1', 1, 'boolean', 'global', '', 0, 0, 0, 'Schaltet die Möglichkeit des rss-Export von privaten News global ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('42d237f9dfd852318cdc66319043536d', '', 'FOAF_SHOW_IDENTITY', '', 1, 'boolean', 'user', '', 0, 0, 0, 'Schaltet für einen User ein oder aus, ob dieser in FOAS-Ketten angezeigt wird, Systemdefault', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('6ae7aecf299930cbb8a5e89bbab4da55', '', 'FOAF_ENABLE', '1', 1, 'boolean', 'global', '', 0, 0, 0, 'FOAF Feature benutzen?', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('a52e3b62ac0bee819b782d8979960b7b', '', 'RESOURCES_ENABLE_GROUPING', '1', 1, 'boolean', 'global', '', 0, 0, 1121861801, 'Schaltet in der Ressourcenverwaltung die Funktion zur Verwaltung von Raumgruppen ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('76cac679fa57fdbb3f9d6cee20bf8c6f', '', 'RESOURCES_ENABLE_SEM_SCHEDULE', '1', 1, 'boolean', 'global', '', 0, 0, 0, 'Schaltet in der Ressourcenverwaltung ein, ob ein Semesterbelegungsplan erstellt werden kann', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('3af783748f92cdf99b066d4227f8dffc', '', 'RESOURCES_SEARCH_ONLY_REQUESTABLE_PROPERTY', '1', 1, 'boolean', 'global', '', 0, 0, 0, 'Schaltet in der Suche der Ressourcenverwaltun das Durchsuchen von nicht wünschbaren Eigenschaften ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('fe498bb91a4cbfdfd5078915e979153c', '', 'RESOURCES_ENABLE_VIRTUAL_ROOM_GROUPS', '1', 1, 'boolean', 'global', '', 0, 0, 0, 'Schaltet in der Ressourcenverwaltung automatische gebildete Raumgruppen neben per Konfigurationsdatei definierten Gruppen ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('68b127dde744085637d221e11d4e8cf2', '', 'RESOURCES_ALLOW_CREATE_TOP_LEVEL', '', 1, 'boolean', 'global', '', 0, 0, 0, 'Schaltet für die Ressourcenverwaltung ein, ob neue Hierachieebenen von anderen Nutzern als Admins angelegt werden können oder nicht', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('b16359d5514b13794689eab669124c69', '', 'ALLOW_DOZENT_VISIBILITY', '', 1, 'boolean', 'global', '', 0, 0, 0, 'Schaltet ein oder aus, ob ein Dozent eigene Veranstaltungen selbst verstecken darf oder nicht', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('e8cd96580149cde65ad69b6cf18d5c39', '', 'ALLOW_DOZENT_ARCHIV', '', 1, 'boolean', 'global', '', 0, 0, 1109946684, 'Schaltet ein oder aus, ob ein Dozent eigene Veranstaltungen selbst archivieren darf oder nicht', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('24ecbeb431826c61fd8b53b3aa41bfa6', '', 'SHOWSEM_ENABLE', '1', 1, 'boolean', 'user', '', 0, 1122461027, 1122461027, 'Einstellung für Nutzer, ob Semesterangaben in der Übersicht "Meine Veranstaltung" nach dem Titel der Veranstaltung gemacht werden; Systemdefault', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('91e6e53b3748a53c42440453e8045be3', '', 'RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST', '1', 1, 'boolean', 'global', '', 0, 1122565305, 1122565305, 'Schaltet das Pflicht, eine Raumanfrage beim Anlegen einer Veranstaltung machen zu müssen, ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('f32367b1542a1d513ecee8a26e26d239', '', 'RESOURCES_SCHEDULE_EXPLAIN_USER_NAME', '1', 1, 'boolean', 'global', '', 0, 1123516671, 1123516671, 'Schaltet in der Ressourcenverwaltung die Anzeige der Namen des Belegers in der Ausgabe von Belegungsplänen ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('4c52bfa598daa03944a401b66c53d828', '', 'NEWS_DISABLE_GARBAGE_COLLECT', '0', 1, 'boolean', 'global', '', 0, 1123751948, 1123751948, 'Schaltet den Garbage-Collect für News ein oder aus', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('9e0579653e585a688665a6ea2e2d7c90', '', 'EVAL_AUSWERTUNG_CONFIG_ENABLE', '1', 1, 'boolean', 'global', '', 0, 1141225624, 1141225624, 'Ermöglicht es dem Nutzer, die grafische Darstellung der Evaluationsauswertung zu konfigurieren', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('0ad11a4cafa548d3c72a3dc1776568d8', '', 'EVAL_AUSWERTUNG_GRAPH_FORMAT', 'jpg', 1, 'string', 'global', '', 0, 1141225624, 1141225624, 'Das Format, in dem die Diagramme der grafischen Evaluationsauswertung erstellt werden (jpg, png, gif).', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('781e0998a1b5c998ebbc02a4f0d907ac', '', 'USER_VISIBILITY_UNKNOWN', '1', 1, 'boolean', 'global', '', 0, 1153815901, 1153815901, 'Sollen Nutzer mit Sichtbarkeit "unknown" wie sichtbare behandelt werden?', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('3ca9d678f11a73917420161180838205', '', 'CHAT_USE_AJAX_CLIENT', '0', 1, 'boolean', 'user', '', 0, 1153815830, 1153815830, 'Einstellung für Nutzer, ob der AJAX chatclient benutzt werden soll (experimental); Systemdefault', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('54ad03142e6704434976c9a0df8329c8', '', 'ONLINE_NAME_FORMAT', 'full_rev', 1, 'string', 'user', '', 0, 1153814980, 1153814980, 'Default-Wert für wer-ist-online Namensformatierung', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('8a147b2d487d7ae91264f03cab5d8c07', '', 'ADMISSION_PRELIM_COMMENT_ENABLE', '0', 1, 'boolean', 'global', '', 0, 1153814966, 1153814966, 'Schaltet ein oder aus, ob ein Nutzer im Modus "Vorläufiger Eintrag" eine Bemerkung hinterlegen kann', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('a93eb21bb08719b3a522b7e238bd8b7e', '', 'EXTERNAL_HELP', '1', 1, 'boolean', 'global', '', 0, 1155128579, 1155128579, 'Schaltet das externe Hilfesystem ein', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('10367c279370c7f78552d2747c2b169c', '', 'EXTERNAL_HELP_LOCATIONID', 'default', 1, 'string', 'global', '', 0, 1155128579, 1155128579, 'Eine eindeutige ID zur Identifikation der gewünschten Hilfeseiten, leer bedeutet Standardhilfe', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('6679a9cf02e56c0fce92e91b8f696005', '', 'EXTERNAL_HELP_URL', 'http://hilfe.studip.de/index.php/%s', 1, 'string', 'global', '', 0, 1155128579, 1155128579, 'URL Template für das externe Hilfesystem', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('4cd2cd3cc207ffc0ae92721c291cd906', '', 'RESOURCES_SHOW_ROOM_NOT_BOOKED_HINT', '0', 1, 'boolean', 'global', '', 0, 1168444600, 1168444600, 'Einstellung, ob bei aktivierter Raumverwaltung Raumangaben die nicht gebucht sind gekennzeichnet werden', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('3b6a1623b8e0913430d6a27bfda976fd', '', 'ADMISSION_ALLOW_DISABLE_WAITLIST', '1', 1, 'boolean', 'global', '', 0, 1170242650, 1170242650, 'Schaltet ein oder aus, ob die Warteliste in Zugangsbeschränkten Veranstaltungen deaktiviert werden kann', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('08f085d9ef2ee7d8b355dcc35282ab8c', '', 'ENABLE_SKYPE_INFO', '1', 1, 'boolean', 'global', '', 0, 1170242666, 1170242666, 'Ermöglicht die Eingabe / Anzeige eines Skype Namens ', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('615e92cdf78c1436c3fc1f60a8cd944e', '', 'SEM_VISIBILITY_PERM', 'root', 1, 'string', 'global', '', 0, 1170242706, 1170242706, 'Bestimmt den globalen Nutzerstatus, ab dem versteckte Veranstaltungen in der Suche gefunden werden (root,admin,dozent)', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('4158d433b57052b20fd66d84b71c7324', '', 'SEM_CREATE_PERM', 'dozent', 1, 'string', 'global', '', 0, 1170242930, 1170242930, 'Bestimmt den globalen Nutzerstatus, ab dem Veranstaltungen angelegt werden dürfen (root,admin,dozent)', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('93da66ca9e2d17df5bc61bd56406add7', '', 'RESOURCES_ROOM_REQUEST_DEFAULT_ACTION', 'NO_ROOM_INFO_ACTION', 1, 'string', 'global', '', 0, 0, 0, 'Designates the pre-selected action for the room request dialog', 'Valid values are: NO_ROOM_INFO_ACTION, ROOM_REQUEST_ACTION, BOOKING_OF_ROOM_ACTION, FREETEXT_ROOM_ACTION', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('0d3f84ed4dd6b7147b504ffb5b6fbc2c', '', 'RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW', '0', 1, 'boolean', 'global', '', 0, 12, 12, 'Enables the expert view of the course schedules', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('bc3004618b17b29dc65e10e89be9a7a0', '', 'RESOURCES_ENABLE_BOOKINGSTATUS_COLORING', '1', 1, 'boolean', 'global', '', 0, 0, 0, 'Enable the colored presentation of the room booking status of a date', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('0c81083086adc66714864b1abcff650a', '', 'EXTERNAL_IMAGE_EMBEDDING', 'deny', 1, 'string', 'global', '', 0, 0, 0, 'Sollen externe Bilder über [img] eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=image proxy benutzen', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('cb92d5bb08f346567dbd394d0d553454', '', 'EMAIL_DOMAIN_RESTRICTION', '', 1, 'string', 'global', '', 0, 1157107088, 1157107088, 'Beschränkt die gültigkeit von Email-Adressen bei freier Registrierung auf die angegebenen Domains. Komma-separierte Liste von Domains ohne vorangestelltes @.', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('9b313fe9ae6184b39c2545999ccce8ab', '', 'EXTERNAL_FLASH_MOVIE_EMBEDDING', 'deny', 1, 'string', 'global', '', 0, 1157107088, 1157107088, 'Sollen externe Flash-Filme mit Hilfe des [flash]-Tags der Schnellformatierung eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=image proxy benutzen', '', '');
INSERT INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES ('3acf297f781b0c0aefd551ec304b902d', '', 'DOCUMENTS_EMBEDD_FLASH_MOVIES', 'deny', 1, 'string', 'global', '', 0, 1157107088, 1157107088, 'Sollen im Dateibereich Flash-Filme direkt in einem Player angezeigt werden? deny=nicht erlaubt, allow=erlaubt, autoload=Film wird beim aufklappen geladen (incrementiert Downloads), autoplay=Film wird sofort abgespielt', '', '');

-- 
-- Daten für Tabelle `evalanswer`
-- 

INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('d67301d4f59aa35d1e3f12a9791b6885', 'ef227e91618878835d52cfad3e6d816b', 0, 'Sehr gut', 1, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('7052b76e616656e4b70f1c504c04ec81', 'ef227e91618878835d52cfad3e6d816b', 1, '', 2, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('64152ace8f2a74d0efb67c54eff64a2b', 'ef227e91618878835d52cfad3e6d816b', 2, '', 3, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('3a3ab5307f39ea039d41fb6f2683475e', 'ef227e91618878835d52cfad3e6d816b', 3, '', 4, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('6115b19f694ccd3d010a0047ff8f970a', 'ef227e91618878835d52cfad3e6d816b', 4, 'Sehr Schlecht', 5, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('be4c3e5fe0b2b735bb3b2712afa8c490', 'ef227e91618878835d52cfad3e6d816b', 5, 'Keine Meinung', 6, 0, 0, 1);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('84be4c31449a9c1807bf2dea0dc869f1', '724244416b5d04a4d8f4eab8a86fdbf8', 0, 'Sehr gut', 1, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('c446970d2addd68e43c2a6cae6117bf7', '724244416b5d04a4d8f4eab8a86fdbf8', 1, 'Gut', 2, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('3d4dcedb714dfdcfbe65cd794b4d404b', '724244416b5d04a4d8f4eab8a86fdbf8', 2, 'Befriedigend', 3, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('fa2bf667ba73ae74794df35171c2ad2e', '724244416b5d04a4d8f4eab8a86fdbf8', 3, 'Ausreichend', 4, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('0be387b9379a05c5578afce64b0c688f', '724244416b5d04a4d8f4eab8a86fdbf8', 4, 'Mangelhaft', 5, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('aec07dd525f2610bdd10bf778aa1893b', '724244416b5d04a4d8f4eab8a86fdbf8', 5, 'Nicht erteilt', 6, 0, 0, 1);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('7080335582e2787a54f315ec8cef631e', '95bbae27965d3404f7fa3af058850bd3', 0, 'trifft völlig zu', 1, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('d68a74dc2c1f0ce226366da918dd161d', '95bbae27965d3404f7fa3af058850bd3', 1, 'trifft ziemlich zu', 2, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('641686e7c61899b303cda106f20064e7', '95bbae27965d3404f7fa3af058850bd3', 2, 'teilsteils', 3, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('7c36d074f2cc38765c982c9dfb769afc', '95bbae27965d3404f7fa3af058850bd3', 3, 'trifft wenig zu', 4, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('5c4827f903168ed4483db5386a9ad5b8', '95bbae27965d3404f7fa3af058850bd3', 4, 'trifft gar nicht zu', 5, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('c10a3f4e97f8badc5230a9900afde0c7', '95bbae27965d3404f7fa3af058850bd3', 5, 'kann ich nicht beurteilen', 6, 0, 0, 1);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('ced33706ca95aff2163c7d0381ef5717', '6fddac14c1f2ac490b93681b3da5fc66', 0, 'Montag', 1, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('087c734855c8a5b34d99c16ad09cd312', '6fddac14c1f2ac490b93681b3da5fc66', 1, 'Dienstag', 2, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('63f5011614f45329cc396b90d94a7096', '6fddac14c1f2ac490b93681b3da5fc66', 2, 'Mittwoch', 3, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('ccd1eaddccca993f6789659b36f40506', '6fddac14c1f2ac490b93681b3da5fc66', 3, 'Donnerstag', 4, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('48842cedeac739468741940982b5fe6d', '6fddac14c1f2ac490b93681b3da5fc66', 4, 'Freitag', 5, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('21b3f7cf2de5cbb098d800f344d399ee', '12e508079c4770fb13c9fce028f40cac', 0, 'Montag', 1, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('f0016e918b5bc5c4cf3cc62bf06fa2e9', '12e508079c4770fb13c9fce028f40cac', 1, 'Dienstag', 2, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('c88242b50ff0bb43df32c1e15bdaca22', '12e508079c4770fb13c9fce028f40cac', 2, 'Mittwoch', 3, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('b39860f6601899dcf87ba71944c57bc7', '12e508079c4770fb13c9fce028f40cac', 3, 'Donnerstag', 4, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('568d6fd620642cb7395c27d145a76734', '12e508079c4770fb13c9fce028f40cac', 4, 'Freitag', 5, 0, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('39b98a5560d5dabaf67227e2895db8da', 'a68bd711902f23bd5c55a29f1ecaa095', 0, '', 1, 5, 0, 0);
INSERT INTO `evalanswer` (`evalanswer_id`, `parent_id`, `position`, `text`, `value`, `rows`, `counter`, `residual`) VALUES ('61ae27ab33c402316a3f1eb74e1c46ab', '442e1e464e12498bd238a7767215a5a2', 0, '', 1, 1, 0, 0);

-- 
-- Daten für Tabelle `evalquestion`
-- 

INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('ef227e91618878835d52cfad3e6d816b', '0', 'polskala', 0, 'Wertung 1-5', 0);
INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('724244416b5d04a4d8f4eab8a86fdbf8', '0', 'likertskala', 0, 'Schulnoten', 0);
INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('95bbae27965d3404f7fa3af058850bd3', '0', 'likertskala', 0, 'Wertung (trifft zu, ...)', 0);
INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('6fddac14c1f2ac490b93681b3da5fc66', '0', 'multiplechoice', 0, 'Werktage', 0);
INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('12e508079c4770fb13c9fce028f40cac', '0', 'multiplechoice', 0, 'Werktage-mehrfach', 1);
INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('a68bd711902f23bd5c55a29f1ecaa095', '0', 'multiplechoice', 0, 'Freitext-Mehrzeilig', 0);
INSERT INTO `evalquestion` (`evalquestion_id`, `parent_id`, `type`, `position`, `text`, `multiplechoice`) VALUES ('442e1e464e12498bd238a7767215a5a2', '0', 'multiplechoice', 0, 'Freitext-Einzeilig', 0);

-- 
-- Daten für Tabelle `semester_data`
-- 

INSERT INTO `semester_data` (`semester_id`, `name`, `description`, `semester_token`, `beginn`, `ende`, `vorles_beginn`, `vorles_ende`) VALUES ('f2b4fdf5ac59a9cb57dd73c4d3bbb651', 'SS 2008', '', '', 1207000800, 1222811999, 1208124000, 1215813599);
-- 
-- Daten für Tabelle `log_actions`
-- 

INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('0ee290df95f0547caafa163c4d533991', 'SEM_VISIBLE', 'Veranstaltung sichtbar schalten', '%user schaltet %sem(%affected) sichtbar.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('a94706b41493e32f8336194262418c01', 'SEM_INVISIBLE', 'Veranstaltung unsichtbar schalten', '%user versteckt %sem(%affected).', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('bd2103035a8021942390a78a431ba0c4', 'DUMMY', 'Dummy-Aktion', '%user tut etwas.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('4490aa3d29644e716440fada68f54032', 'LOG_ERROR', 'Allgemeiner Log-Fehler', 'Allgemeiner Logging-Fehler, Details siehe Debug-Info.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('f858b05c11f5faa2198a109a783087a8', 'SEM_CREATE', 'Veranstaltung anlegen', '%user legt %sem(%affected) an.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('5b96f2fe994637253ba0fe4a94ad1b98', 'SEM_ARCHIVE', 'Veranstaltung archivieren', '%user archiviert %info (ID: %affected).', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('bf192518a9c3587129ed2fdb9ea56f73', 'SEM_DELETE_FROM_ARCHIVE', 'Veranstaltung aus Archiv löschen', '%user löscht %info aus dem Archiv (ID: %affected).', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('4869cd69f20d4d7ed4207e027d763a73', 'INST_USER_STATUS', 'Einrichtungsnutzerstatus ändern', '%user ändert Status für %user(%coaffected) in Einrichtung %inst(%affected): %info.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('6be59dcd70197c59d7bf3bcd3fec616f', 'INST_USER_DEL', 'Benutzer aus Einrichtung löschen', '%user löscht %user(%coaffected) aus Einrichtung %inst(%affected).', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('cf8986a67e67ca273e15fd9230f6e872', 'USER_CHANGE_TITLE', 'Akademische Titel ändern', '%user ändert/setzt akademischen Titel für %user(%affected) - %info.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('ca216ccdf753f59ba7fd621f7b22f7bd', 'USER_CHANGE_NAME', 'Personennamen ändern', '%user ändert/setzt Name für %user(%affected) - %info.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('8aad296e52423452fc75cabaf2bee384', 'USER_CHANGE_USERNAME', 'Benutzernamen ändern', '%user ändert/setzt Benutzernamen für %user(%affected): %info.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('59f3f38c905fded82bbfdf4f04c16729', 'INST_CREATE', 'Einrichtung anlegen', '%user legt Einrichtung %inst(%affected) an.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('1a1e8c9c3125ea8d2c58c875a41226d6', 'INST_DEL', 'Einrichtung löschen', '%user löscht Einrichtung %info (%affected).', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('d18d750fb2c166e1c425976e8bca96e7', 'USER_CHANGE_EMAIL', 'E-Mail-Adresse ändern', '%user ändert/setzt E-Mail-Adresse für %user(%affected): %info.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('a92afa63584cc2a62d2dd2996727b2c5', 'USER_CREATE', 'Nutzer anlegen', '%user legt Nutzer %user(%affected) an.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('e406e407501c8418f752e977182cd782', 'USER_CHANGE_PERMS', 'Globalen Nutzerstatus ändern', '%user ändert/setzt globalen Status von %user(%affected): %info', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('63042706e5cd50924987b9515e1e6cae', 'INST_USER_ADD', 'Benutzer zu Einrichtung hinzufügen', '%user fügt %user(%coaffected) zu Einrichtung %inst(%affected) mit Status %info hinzu.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('4dd6b4101f7bf3bd7fe8374042da95e9', 'USER_NEWPWD', 'Neues Passwort', '%user generiert neues Passwort für %user(%affected)', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('e8646729e5e04970954c8b9679af389b', 'USER_DEL', 'Benutzer löschen', '%user löscht %user(%affected) (%info)', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('2e816bfd792e4a99913f11c04ad49198', 'SEM_UNDELETE_SINGLEDATE', 'Einzeltermin wiederherstellen', '%user stellt Einzeltermin %singledate(%affected) in %sem(%coaffected) wieder her.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('997cf01328d4d9f36b9f50ac9b6ace47', 'SEM_DELETE_SINGLEDATE', 'Einzeltermin löschen', '%user löscht Einzeltermin %singledate(%affected) in %sem(%coaffected).', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('b205bde204b5607e036c10557a6ce149', 'SEM_SET_STARTSEMESTER', 'Startsemester ändern', '%user hat in %sem(%affected) das Startsemester auf %semester(%coaffected) geändert.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('9d13643a1833c061dc3d10b4fb227f12', 'SEM_SET_ENDSEMESTER', 'Semesterlaufzeit ändern', '%user hat in %sem(%affected) die Laufzeit auf %semester(%coaffected) geändert', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('5f8fda12a4c0bd6eadbb94861de83696', 'SEM_ADD_CYCLE', 'Regelmäßige Zeit hinzugefügt', '%user hat in %sem(%affected) die regelmäßige Zeit <em>%coaffected</em> hinzugefügt.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('6f4bb66c1caf89879d89f3b1921a93dd', 'SEM_DELETE_CYCLE', 'Regelmäßige Zeit gelöscht', '%user hat in %sem(%affected) die regelmäßige Zeit <em>%coaffected</em> gelöscht.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('3f7dcf6cc85d6fba1281d18c4d9aba6f', 'SEM_ADD_SINGLEDATE', 'Einzeltermin hinzufügen', '%user hat in %sem(%affected) den Einzeltermin <em>%coaffected</em> hinzugefügt', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('c36fa0f804cde78a6dcb1c30c2ee47ba', 'SEM_DELETE_REQUEST', 'Raumanfrage gelöscht', '%user hat in %sem(%affected) die Raumanfrage für die gesamte Veranstaltung gelöscht.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('370db4eb0e38051dd3c5d7c52717215a', 'SEM_DELETE_SINGLEDATE_REQUEST', 'Einzeltermin, Raumanfrage gelöscht', '%user hat in %sem(%affected) die Raumanfrage für den Termin <em>%coaffected</em> gelöscht.', 1, NULL);
INSERT INTO `log_actions` (`action_id`, `name`, `description`, `info_template`, `active`, `expires`) VALUES ('9d642dc93540580d42ba2ea502c3fbf6', 'SINGLEDATE_CHANGE_TIME', 'Einzeltermin bearbeiten', '%user hat in %sem(%affected) den Einzeltermin %coaffected geändert.', 1, NULL);

-- 
-- Daten für Tabelle `plugins`
-- 

INSERT INTO `plugins` (`pluginid`, `pluginclassname`, `pluginpath`, `pluginname`, `plugindesc`, `plugintype`, `enabled`, `navigationpos`) VALUES (1, 'PluginAdministrationPlugin', 'core', 'Plugin-Administration', 'Administrationsoberfläche für Plugins', 'Administration', 'yes', 0);
INSERT INTO `plugins` (`pluginid`, `pluginclassname`, `pluginpath`, `pluginname`, `plugindesc`, `plugintype`, `enabled`, `navigationpos`, `dependentonid`) VALUES (2, 'de_studip_core_UserManagementPlugin', 'core', 'UserManagement', '', 'Core', 'yes', 1, 1);
INSERT INTO `plugins` (`pluginid`, `pluginclassname`, `pluginpath`, `pluginname`, `plugindesc`, `plugintype`, `enabled`, `navigationpos`, `dependentonid`) VALUES (3, 'de_studip_core_RoleManagementPlugin', 'core', 'RollenManagement', 'Administration der Rollen', 'Administration', 'yes', 2, 1);

-- 
-- Daten für Tabelle `plugins_activated`
-- 

INSERT INTO `plugins_activated` (`pluginid`, `poiid`, `state`) VALUES (1, 'admin', 'on');
INSERT INTO `plugins_activated` (`pluginid`, `poiid`, `state`) VALUES (3, 'admin', 'on');

-- 
-- Daten für Tabelle `roles`
-- 

INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (1, 'Root-Administrator(in)', 'y');
INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (2, 'Administrator(in)', 'y');
INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (3, 'Mitarbeiter(in)', 'y');
INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (4, 'Lehrende(r)', 'y');
INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (5, 'Studierende(r)', 'y');
INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (6, 'Tutor(in)', 'y');
INSERT INTO `roles` (`roleid`, `rolename`, `system`) VALUES (7, 'Nobody', 'y');

-- 
-- Daten für Tabelle `roles_studipperms`
-- 

INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (1, 'root');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (2, 'admin');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (3, 'admin');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (3, 'root');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (4, 'dozent');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (5, 'autor');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (5, 'tutor');
INSERT INTO `roles_studipperms` (`roleid`, `permname`) VALUES (6, 'tutor');

-- 
-- Daten für Tabelle `roles_plugins`
-- 

INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (1, 1);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (1, 2);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (1, 3);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (2, 1);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (2, 2);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (2, 3);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (3, 1);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (3, 2);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (3, 3);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (4, 1);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (4, 2);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (4, 3);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (5, 1);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (5, 2);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (5, 3);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (6, 1);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (6, 2);
INSERT INTO `roles_plugins` (`roleid`, `pluginid`) VALUES (6, 3);

-- 
-- Daten für Tabelle `roles_user`
-- 

INSERT INTO `roles_user` (`roleid`, `userid`) VALUES (7, 'nobody');

-- 
-- Daten für Tabelle `schema_version`
-- 

INSERT INTO `schema_version` (`domain`, `version`) VALUES ('studip', 17);
