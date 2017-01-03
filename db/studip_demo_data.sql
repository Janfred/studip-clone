--
-- Daten für Tabelle `abschluss`
--

REPLACE INTO `abschluss` (`abschluss_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES('228234544820cdf75db55b42d1ea3ecc', 'Bachelor', '', 1311416359, 1311416359);
REPLACE INTO `abschluss` (`abschluss_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES('c7f569e815a35cf24a515a0e67928072', 'Master', '', 1311416385, 1311416385);

--
-- Daten für Tabelle `auth_user_md5`
--

REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', '$2a$08$SRoCYxAhWPFVF8V8CO15TOyzr.PpLRfVD9lVWVrmmBw4brkRTE/2G', 'root', 'Root', 'Studip', 'root@localhost', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('205f3efb7997a0fc9755da2b535038da', 'test_dozent', '$2a$08$ajIvgEjd17MiiDcFr6msc.xldknH/tTGajUXVhDxDKNJVX0H0iv0i', 'dozent', 'Testaccount', 'Dozent', 'dozent@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('6235c46eb9e962866ebdceece739ace5', 'test_admin', '$2a$08$svvSma20vIxIR4J5gc0jIu31gws1WibmiQ/HDhCTukFA5GqhscY1G', 'admin', 'Testaccount', 'Admin', 'admin@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('7e81ec247c151c02ffd479511e24cc03', 'test_tutor', '$2a$08$mGhBl85TPsiItumZ4xjbgOnQ1vqIhLAC9giCfWcFzpkE1jqe4lmby', 'tutor', 'Testaccount', 'Tutor', 'tutor@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');
REPLACE INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`, `validation_key`, `auth_plugin`, `locked`, `lock_comment`, `locked_by`, `visible`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 'test_autor', '$2a$08$xvbrvPhkcsvkzPZsNh.kceLw2IIwiNJ.1jGOwY3.H/dR2f8PG5X3O', 'autor', 'Testaccount', 'Autor', 'autor@studip.de', '', 'standard', 0, NULL, NULL, 'unknown');

--
-- Daten für Tabelle `config`
--

REPLACE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES('48f849a4927f8ac5231da5352076f16a', '', 'STUDYGROUPS_ENABLE', '1', 0, 'boolean', 'global', '', 0, 1268739461, 1268739461, 'Studiengruppen', 'Studiengruppen', '');
REPLACE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES('9f1c998d46f55ac38da3a53072a4086b', '', 'STUDYGROUP_DEFAULT_INST', 'ec2e364b28357106c0f8c282733dbe56', 0, 'string', 'global', '', 0, 1268739461, 1268739461, 'Studiengruppen', '', '');
REPLACE INTO `config` (`config_id`, `parent_id`, `field`, `value`, `is_default`, `type`, `range`, `section`, `position`, `mkdate`, `chdate`, `description`, `comment`, `message_template`) VALUES('bcd4820eebd8e027cef91bc761ab9a75', '', 'STUDYGROUP_TERMS', 'Mir ist bekannt, dass ich die Gruppe nicht zu rechtswidrigen Zwecken nutzen darf. Dazu zählen u.a. Urheberrechtsverletzungen, Beleidigungen und andere Persönlichkeitsdelikte.\r\n\r\nIch erkläre mich damit einverstanden, dass Admins die Inhalte der Gruppe zu Kontrollzwecken einsehen dürfen.', 0, 'string', 'global', '', 0, 1268739461, 1268739461, 'Studiengruppen', '', '');

--
-- Daten für Tabelle `datafields`
--

REPLACE INTO `datafields` (`datafield_id`, `name`, `object_type`, `object_class`, `edit_perms`, `view_perms`, `priority`, `mkdate`, `chdate`, `type`, `typeparam`, `is_required`, `description`) VALUES('ce73a10d07b3bb13c0132d363549efda', 'Matrikelnummer', 'user', '7', 'user', 'dozent', 0, NULL, NULL, 'textline', '', 0, '');

--
-- Daten für Tabelle `dokumente`
--

REPLACE INTO `dokumente` (`dokument_id`, `range_id`, `user_id`, `seminar_id`, `name`, `description`, `filename`, `mkdate`, `chdate`, `filesize`, `autor_host`, `downloads`, `url`, `protected`, `priority`, `author_name`) VALUES('6b606bd3d6d6cda829200385fa79fcbf', 'ca002fbae136b07e4df29e0136e3bd32', '76ed43ef286fb55cf9e41beadb484a9f', 'a07535cf2f8a72df33c12ddfa4b53dde', 'Stud.IP-Produktbroschüre im PDF-Format', '', 'mappe_studip-el.pdf', 1343924827, 1343924841, 314146, '127.0.0.1', 0, 'http://www.studip.de/download/mappe_studip-el.pdf', 0, 0, 'Root Studip');

--
-- Daten für Tabelle `folder`
--

REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('dad53cd0f0d9f36817c3c9c7c124bda3', 'ec2e364b28357106c0f8c282733dbe56', 'ec2e364b28357106c0f8c282733dbe56', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 7, 1156516698, 1156516698, 0);
REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('b58081c411c76814bc8f78425fb2ab81', '7a4f19a0a2c321ab2b8f7b798881af7c', '7a4f19a0a2c321ab2b8f7b798881af7c', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 7, 1156516698, 1156516698, 0);
REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('694cdcef09c2b8e70a7313b028e36fb6', '110ce78ffefaf1e5f167cd7019b728bf', '110ce78ffefaf1e5f167cd7019b728bf', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 7, 1156516698, 1156516698, 0);
REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('ad8dc6a6162fb0fe022af4a62a15e309', '373a72966cf45c484b4b0b07dba69a64', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', 'Hausaufgaben', '', 3, 1343924873, 1343924877, 0);
REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('df122112a21812ff4ffcf1965cb48fc3', '2f597139a049a768dbf8345a0a0af3de', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', 'Dateiordner der Gruppe: Studierende', 'Ablage für Ordner und Dokumente dieser Gruppe', 15, 1343924860, 1343924860, 0);
REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('1af61dbdcfca1b394290c5d4283371d7', '7cb72dab1bf896a0b55c6aa7a70a3a86', '7cb72dab1bf896a0b55c6aa7a70a3a86', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 7, 1343924088, 1343924088, 0);
REPLACE INTO `folder` (`folder_id`, `range_id`, `seminar_id`, `user_id`, `name`, `description`, `permission`, `mkdate`, `chdate`, `priority`) VALUES('ca002fbae136b07e4df29e0136e3bd32', 'a07535cf2f8a72df33c12ddfa4b53dde', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 5, 1343924407, 1343924894, 0);

--
-- Daten für Tabelle `Institute`
--

REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('1535795b0d6ddecac6813f5f6ac47ef2', 'Test Fakultät', '1535795b0d6ddecac6813f5f6ac47ef2', 'Geismar Landstr. 17b', '37083 Göttingen', 'http://www.studip.de', '0551 / 381 985 0', 'testfakultaet@studip.de', '0551 / 381 985 3', 1, 16, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('2560f7c7674942a7dce8eeb238e15d93', 'Test Einrichtung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 1, 16, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('536249daa596905f433e1f73578019db', 'Test Lehrstuhl', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 3, 16, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('f02e2b17bc0e99fc885da6ac4c2532dc', 'Test Abteilung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 4, 16, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('ec2e364b28357106c0f8c282733dbe56', 'externe Bildungseinrichtungen', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 16, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('7a4f19a0a2c321ab2b8f7b798881af7c', 'externe Einrichtung A', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 16, 1156516698, 1156516698, 'Studip', 0, '');
REPLACE INTO `Institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `modules`, `mkdate`, `chdate`, `lit_plugin_name`, `srienabled`, `lock_rule`) VALUES('110ce78ffefaf1e5f167cd7019b728bf', 'externe Einrichtung B', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 16, 1156516698, 1156516698, 'Studip', 0, '');

--
-- Daten für Tabelle `lit_catalog`
--

REPLACE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES('54181f281faa777941acc252aebaf26d', 'studip', 1156516698, 1156516698, 'Gvk', '387042768', 'Quickguide Strahlenschutz : [Aufgaben, Organisation, Schutzmaßnahmen].', 'Wolf, Heike', '', '', 'Kissing : WEKA Media', '', '2004-01-01', '', '74 S : Ill.', '', '', 'ger', '[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxislösungen', '', '');
REPLACE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES('d6623a3c2b8285fb472aa759150148ad', 'studip', 1156516698, 1156516698, 'Gvk', '387042253', 'Röntgenverordnung : (RÖV) ; Verordnung über den Schutz vor Schäden durch Röntgenstrahlen.', 'Wolf, Heike', '', '', 'Kissing : WEKA Media', '', '2004-01-01', '', '50 S.', '', '', 'ger', '[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxislösungen', '', '');
REPLACE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES('15074ad4f2bd2c57cbc9dfb343c1355b', 'studip', 1156516698, 1156516698, 'Gvk', '384065813', 'Der Kater mit Hut', 'Geisel, Theodor Seuss', '', '', 'München [u.a.] : Piper', '', '2004-01-01', '', '75 S : zahlr. Ill ; 19 cm.', 'ISBN: 349224078X (kart.)', '', 'ger', 'Serie Piper ;, 4078', '', '');
REPLACE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES('ce704bbc9453994daa05d76d2d04aba0', 'studip', 1156516698, 1156516698, 'Gvk', '379252104', 'Die volkswirtschaftliche Perspektive', 'Heise, Michael', '', '', 'In: Zeitschrift für das gesamte Kreditwesen, Vol. 57, No. 4 (2004), p. 211-217, Frankfurt, M. : Knapp', 'Kater, Ulrich;', '2004-01-01', '', 'graph. Darst.', '', '', 'ger', '', '', '');
REPLACE INTO `lit_catalog` (`catalog_id`, `user_id`, `mkdate`, `chdate`, `lit_plugin`, `accession_number`, `dc_title`, `dc_creator`, `dc_subject`, `dc_description`, `dc_publisher`, `dc_contributor`, `dc_date`, `dc_type`, `dc_format`, `dc_identifier`, `dc_source`, `dc_language`, `dc_relation`, `dc_coverage`, `dc_rights`) VALUES('b5d115a7f7cad02b4535fb3090bf18da', 'studip', 1156516698, 1156516698, 'Gvk', '386883831', 'E-Learning: Qualität und Nutzerakzeptanz sichern : Beiträge zur Planung, Umsetzung und Evaluation multimedialer und netzgestützter Anwendungen', 'Zinke, Gert', '', '', 'Bielefeld : Bertelsmann', 'Härtel, Michael; Bundesinstitut für Berufsbildung, ;', '2004-01-01', '', '159 S : graph. Darst ; 225 mm x 155 mm.', 'ISBN: 3763910204', '', 'ger', 'Berichte zur beruflichen Bildung ;, 265', '', '');

--
-- Daten für Tabelle `lit_list`
--

REPLACE INTO `lit_list` (`list_id`, `range_id`, `name`, `format`, `user_id`, `mkdate`, `chdate`, `priority`, `visibility`) VALUES('0b4d8c94244a1a571e3cc2afeeb15c5f', 'a07535cf2f8a72df33c12ddfa4b53dde', 'Basisliteratur der Veranstaltung', '**{dc_creator}** |({dc_contributor})||\r\n{dc_title}||\r\n{dc_identifier}||\r\n%%{published}%%||\r\n{note}||\r\n[{lit_plugin_display_name}]{external_link}|', '76ed43ef286fb55cf9e41beadb484a9f', 1343924971, 1343925058, 1, 1);

--
-- Daten für Tabelle `lit_list_content`
--

REPLACE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES('48acf3d39374f46876d46df0f56203cd', '0b4d8c94244a1a571e3cc2afeeb15c5f', 'b5d115a7f7cad02b4535fb3090bf18da', '76ed43ef286fb55cf9e41beadb484a9f', 1343925058, 1343925058, '', 5);
REPLACE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES('0cf7e4622ddbcc145b5792519979116f', '0b4d8c94244a1a571e3cc2afeeb15c5f', 'd6623a3c2b8285fb472aa759150148ad', '76ed43ef286fb55cf9e41beadb484a9f', 1343925058, 1343925058, '', 4);
REPLACE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES('28de3cab6e36758b96ba757b65512cd2', '0b4d8c94244a1a571e3cc2afeeb15c5f', '54181f281faa777941acc252aebaf26d', '76ed43ef286fb55cf9e41beadb484a9f', 1343925058, 1343925058, '', 3);
REPLACE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES('03e0d3910e15fd7ae2826ed6baf2b59d', '0b4d8c94244a1a571e3cc2afeeb15c5f', 'ce704bbc9453994daa05d76d2d04aba0', '76ed43ef286fb55cf9e41beadb484a9f', 1343925058, 1343925058, '', 2);
REPLACE INTO `lit_list_content` (`list_element_id`, `list_id`, `catalog_id`, `user_id`, `mkdate`, `chdate`, `note`, `priority`) VALUES('7e129b140176dfc1a4c53e065fa5e8b1', '0b4d8c94244a1a571e3cc2afeeb15c5f', '15074ad4f2bd2c57cbc9dfb343c1355b', '76ed43ef286fb55cf9e41beadb484a9f', 1343925058, 1343925058, '', 1);

--
-- Daten für Tabelle `news_rss_range`
--

REPLACE INTO `news_rss_range` (`range_id`, `rss_id`, `range_type`) VALUES('studip', '70cefd1e80398bb20ff599636546cdff', 'global');

--
-- Dumping data for table `plugins_activated`
--

REPLACE INTO `plugins_activated` (`pluginid`, `poiid`, `state`) VALUES(1, 'sema07535cf2f8a72df33c12ddfa4b53dde', 'on');
REPLACE INTO `plugins_activated` (`pluginid`, `poiid`, `state`) VALUES(2, 'sema07535cf2f8a72df33c12ddfa4b53dde', 'on');

--
-- Daten für Tabelle `range_tree`
--

REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('3f93863e3d37ba0df286a6e7e26974ef', 'root', 0, 0, 'Einrichtungen der Universität', '', '');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('1323254564871354786157481484621', '3f93863e3d37ba0df286a6e7e26974ef', 1, 0, '', 'inst', '1535795b0d6ddecac6813f5f6ac47ef2');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('ce6c87bbf759b4cfd6f92d0c5560da5c', '1323254564871354786157481484621', 0, 0, 'Test Einrichtung', 'inst', '2560f7c7674942a7dce8eeb238e15d93');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('2f4f90ac9d8d832cc8c8a95910fde4eb', '1323254564871354786157481484621', 0, 1, 'Test Lehrstuhl', 'inst', '536249daa596905f433e1f73578019db');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('5d032f70c255f3e57cf8aa85a429ad4e', '1323254564871354786157481484621', 0, 2, 'Test Abteilung', 'inst', 'f02e2b17bc0e99fc885da6ac4c2532dc');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('a3d977a66f0010fa8e15c27dd71aff63', 'root', 0, 1, 'externe Bildungseinrichtungen', 'fak', 'ec2e364b28357106c0f8c282733dbe56');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('e0ff0ead6a8c5191078ed787cd7c0c1f', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 0, 'externe Einrichtung A', 'inst', '7a4f19a0a2c321ab2b8f7b798881af7c');
REPLACE INTO `range_tree` (`item_id`, `parent_id`, `level`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES('105b70b72dc1908ce2925e057c4a8daa', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 1, 'externe Einrichtung B', 'inst', '110ce78ffefaf1e5f167cd7019b728bf');

--
-- Daten für Tabelle `scm`
--

REPLACE INTO `scm` (`scm_id`, `range_id`, `user_id`, `tab_name`, `content`, `mkdate`, `chdate`, `position`) VALUES('a07df31918cc8e5ca0597e959a4a5297', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', 'Informationen', '', 1343924407, 1343924407, 0);

--
-- Daten für Tabelle `seminare`
--

REPLACE INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `mkdate`, `chdate`, `ects`, `admission_turnout`, `admission_binding`, `admission_prelim`, `admission_prelim_txt`, `admission_disable_waitlist`, `visible`, `showscore`, `modules`, `aux_lock_rule`, `aux_lock_rule_forced`, `lock_rule`, `admission_waitlist_max`, `admission_disable_waitlist_move`, `is_complete`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', '', 'ec2e364b28357106c0f8c282733dbe56', 'Test Studiengruppe', '', 99, 'Studiengruppen sind eine einfache Möglichkeit, mit KommilitonInnen, KollegInnen und anderen zusammenzuarbeiten.', '', '', 1, 1, 1254348000, -1, '', '', '', '', '', 1268739824, 1343924088, '', 0, 0, 0, '', 0, 1, 0, 395, NULL, 0, NULL, 0, 0, 0);
REPLACE INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `mkdate`, `chdate`, `ects`, `admission_turnout`, `admission_binding`, `admission_prelim`, `admission_prelim_txt`, `admission_disable_waitlist`, `visible`, `showscore`, `modules`, `aux_lock_rule`, `aux_lock_rule_forced`, `lock_rule`, `admission_waitlist_max`, `admission_disable_waitlist_move`, `is_complete`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '12345', '2560f7c7674942a7dce8eeb238e15d93', 'Test Lehrveranstaltung', 'eine normale Lehrveranstaltung', 1, '', '', '', 1, 1, 1459461600, 0, '', 'für alle Studierenden', 'abgeschlossenes Grundstudium', 'Referate in Gruppenarbeit', 'Klausur', 1343924407, 1448562854, '4', 0, 0, 0, '', 0, 1, 0, 20911, NULL, 0, NULL, 0, 0, 0);

--
-- Daten für Tabelle `seminar_cycle_dates`
--

REPLACE INTO `seminar_cycle_dates` (`metadate_id`, `seminar_id`, `start_time`, `end_time`, `weekday`, `description`, `sws`, `cycle`, `week_offset`, `end_offset`, `sorter`, `mkdate`, `chdate`) VALUES('0309c794406b96bb01662e9e02593517', 'a07535cf2f8a72df33c12ddfa4b53dde', '09:00:00', '13:00:00', 4, '', '0.0', 1, 1, 14, 0, 1343924407, 1483466428);
REPLACE INTO `seminar_cycle_dates` (`metadate_id`, `seminar_id`, `start_time`, `end_time`, `weekday`, `description`, `sws`, `cycle`, `week_offset`, `end_offset`, `sorter`, `mkdate`, `chdate`) VALUES('d124b42deb48ac58adbd620b7ae6cc21', 'a07535cf2f8a72df33c12ddfa4b53dde', '09:00:00', '12:00:00', 1, '', '0.0', 1, 0, 14, 0, 1343924407, 1483466429);


--
-- Daten für Tabelle `seminar_inst`
--

REPLACE INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '2560f7c7674942a7dce8eeb238e15d93');

--
-- Daten für Tabelle `seminar_sem_tree`
--

REPLACE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '3d39528c1d560441fd4a8cb0b7717285');
REPLACE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '5c41d2b4a5a8338e069dda987a624b74');
REPLACE INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'dd7fff9151e85e7130cdb684edf0c370');

--
-- Daten für Tabelle `seminar_user`
--

REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `notification`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 'autor', 0, 5, 0, 1343924589, '', 'unknown', '', 1);
REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `notification`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('7cb72dab1bf896a0b55c6aa7a70a3a86', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 'dozent', 0, 8, 0, 0, '', 'unknown', '', 1);
REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `notification`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '205f3efb7997a0fc9755da2b535038da', 'dozent', 0, 5, 0, 1343924407, '', 'yes', '', 1);
REPLACE INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `position`, `gruppe`, `notification`, `mkdate`, `comment`, `visible`, `label`, `bind_calendar`) VALUES('a07535cf2f8a72df33c12ddfa4b53dde', '7e81ec247c151c02ffd479511e24cc03', 'tutor', 0, 5, 0, 1343924407, '', 'yes', '', 1);

--
-- Daten für Tabelle `sem_tree`
--

REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('5b73e28644a3e259a6e0bc1e1499773c', 'root', 1, '', '', '1535795b0d6ddecac6813f5f6ac47ef2', 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('439618ae57d8c10dcaabcf7e21bcc1d9', '5b73e28644a3e259a6e0bc1e1499773c', 0, '', 'Test Studienbereich A', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('5c41d2b4a5a8338e069dda987a624b74', '5b73e28644a3e259a6e0bc1e1499773c', 1, '', 'Test Studienbereich B', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('3d39528c1d560441fd4a8cb0b7717285', '439618ae57d8c10dcaabcf7e21bcc1d9', 0, '', 'Test Studienbereich A-1', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('dd7fff9151e85e7130cdb684edf0c370', '439618ae57d8c10dcaabcf7e21bcc1d9', 1, '', 'Test Studienbereich A-2', NULL, 0);
REPLACE INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`, `type`) VALUES('01c8b1d188be40c5ac64b54a01aae294', '5b73e28644a3e259a6e0bc1e1499773c', 2, '', 'Test Studienbereich C', NULL, 0);

--
-- Daten für Tabelle `statusgruppen`
--

REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('86498c641ccf4f4d4e02f4961ccc3829', 'Lehrbeauftragte', '2560f7c7674942a7dce8eeb238e15d93', 3, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('600403561c21a50ae8b4d41655bd2191', 'Hochschullehrer/-in', '2560f7c7674942a7dce8eeb238e15d93', 4, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('efb56e092f33cb78a8766676042dc1c5', 'wiss. Mitarbeiter/-in', '2560f7c7674942a7dce8eeb238e15d93', 2, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('5d40b1fc0434e6589d7341a3ee742baf', 'Direktor/-in', '2560f7c7674942a7dce8eeb238e15d93', 1, 0, 0, 1156516698, 1156516698, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('2f597139a049a768dbf8345a0a0af3de', 'Studierende', 'a07535cf2f8a72df33c12ddfa4b53dde', 1, 0, 0, 1343924562, 1343924562, 0);
REPLACE INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `selfassign`, `mkdate`, `chdate`, `calendar_group`) VALUES('f4319d9909e9f7cb4692c16771887f22', 'Lehrende', 'a07535cf2f8a72df33c12ddfa4b53dde', 0, 0, 0, 1343924551, 1343924551, 0);

--
-- Daten für Tabelle `statusgruppe_user`
--

REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('efb56e092f33cb78a8766676042dc1c5', '7e81ec247c151c02ffd479511e24cc03', 1, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('5d40b1fc0434e6589d7341a3ee742baf', '205f3efb7997a0fc9755da2b535038da', 1, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('f4319d9909e9f7cb4692c16771887f22', '205f3efb7997a0fc9755da2b535038da', 1, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('f4319d9909e9f7cb4692c16771887f22', '7e81ec247c151c02ffd479511e24cc03', 2, 1, 1);
REPLACE INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`, `position`, `visible`, `inherit`) VALUES('2f597139a049a768dbf8345a0a0af3de', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 1, 1, 1);

--
-- Daten für Tabelle `studiengaenge`
--

REPLACE INTO `fach` (`fach_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES('f981c9b42ca72788a09da4a45794a737', 'Informatik', '', 1311416397, 1311416397);
REPLACE INTO `fach` (`fach_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES('6b9ac09535885ca55e29dd011e377c0a', 'Geschichte', '', 1311416418, 1311416418);

--
-- Daten für Tabelle `termine`
--

REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('1305342795b6b4e46f17a613f87d3243', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1498719600, 1498734000, 1483466428, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('3c724ac77da7d64c6ed1232f49d3ab11', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1495436400, 1495447200, 1483466429, 1483466511, 1, NULL, '', 'd124b42deb48ac58adbd620b7ae6cc21');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('53e93647eccea41d49821f2801d9cb68', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1494226800, 1494237600, 1483466429, 1483466511, 1, NULL, '', 'd124b42deb48ac58adbd620b7ae6cc21');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('542a7476c70ec8b75e4b4d723c55679d', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1496300400, 1496314800, 1483466428, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('5e57916bddf35f88c4c796b4ed4072a8', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1491807600, 1491818400, 1483466429, 1483466511, 1, NULL, '', 'd124b42deb48ac58adbd620b7ae6cc21');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('9abee8056d3ee8729fe2bf833444fff3', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1493881200, 1493895600, 1483466428, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('9c7f55e8affbf378b99a447c183db6e5', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1495090800, 1495105200, 1483466428, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('a36724249d72e74e4f4b8f9bfbe076f2', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1497855600, 1497866400, 1483466429, 1483466511, 1, NULL, '', 'd124b42deb48ac58adbd620b7ae6cc21');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('a3b75aed9da78077809dd803205e5c33', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1497510000, 1497524400, 1483466428, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('a4402a5d4136e8dec6345fd83a4c2a78', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1492671600, 1492686000, 1483466428, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('b224cdbf0337a7f4d877d12286085579', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1499065200, 1499076000, 1483466429, 1483466511, 1, NULL, '', 'd124b42deb48ac58adbd620b7ae6cc21');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('ee65b84e2dec84fafa4fcf5032b49804', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1493017200, 1493028000, 1483466429, 1483466511, 1, NULL, '', 'd124b42deb48ac58adbd620b7ae6cc21');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('f1bb9e79a37833d8cc401b7bcef290df', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1499929200, 1499943600, 1483466429, 1483466532, 1, NULL, '', '0309c794406b96bb01662e9e02593517');
REPLACE INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`) VALUES('c5675fd9f6284c2219afe744dda43850', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1497520800, 1497528000, 1483467038, 1483467038, 4, NULL, '', NULL);


--
-- Dumping data for table `ex_termine`
--

REPLACE INTO `ex_termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `raum`, `metadate_id`, `resource_id`) VALUES
  ('4bbea42f1e6943b2c2a4d2737d29401a', 'a07535cf2f8a72df33c12ddfa4b53dde', '76ed43ef286fb55cf9e41beadb484a9f', '', NULL, 1496646000, 1496656800, 1483466429, 1483466429, 1, NULL, NULL, 'd124b42deb48ac58adbd620b7ae6cc21', '');


--
-- Daten für Tabelle `user_visibility_settings`
--

REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 1, 0, '0', 'Allgemeine Daten', 4, NULL, 'commondata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 2, 0, '0', 'Private Daten', 4, NULL, 'privatedata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 3, 0, '0', 'Studien-/Einrichtungsdaten', 4, NULL, 'studdata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 4, 0, '0', 'Zusätzliche Datenfelder', 4, NULL, 'additionaldata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 5, 0, '0', 'Eigene Kategorien', 4, NULL, 'owncategory');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 6, 0, '0', 'Plugins', 4, NULL, 'plugins');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', 7, 1, '1', 'Ankündigungen', 4, NULL, 'news');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('6235c46eb9e962866ebdceece739ace5', 8, 0, '0', 'Allgemeine Daten', 4, NULL, 'commondata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('6235c46eb9e962866ebdceece739ace5', 9, 0, '0', 'Private Daten', 4, NULL, 'privatedata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('6235c46eb9e962866ebdceece739ace5', 10, 0, '0', 'Studien-/Einrichtungsdaten', 4, NULL, 'studdata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('6235c46eb9e962866ebdceece739ace5', 11, 0, '0', 'Zusätzliche Datenfelder', 4, NULL, 'additionaldata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('6235c46eb9e962866ebdceece739ace5', 12, 0, '0', 'Eigene Kategorien', 4, NULL, 'owncategory');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('6235c46eb9e962866ebdceece739ace5', 13, 0, '0', 'Plugins', 4, NULL, 'plugins');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 14, 0, '0', 'Allgemeine Daten', 4, NULL, 'commondata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 15, 0, '0', 'Private Daten', 4, NULL, 'privatedata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 16, 0, '0', 'Studien-/Einrichtungsdaten', 4, NULL, 'studdata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 17, 0, '0', 'Zusätzliche Datenfelder', 4, NULL, 'additionaldata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 18, 0, '0', 'Eigene Kategorien', 4, NULL, 'owncategory');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 19, 0, '0', 'Plugins', 4, NULL, 'plugins');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 20, 16, '1', 'Wo ich studiere', 4, NULL, 'studying');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', 21, 17, '1', 'Matrikelnummer', 4, NULL, 'ce73a10d07b3bb13c0132d363549efda');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('205f3efb7997a0fc9755da2b535038da', 22, 0, '0', 'Allgemeine Daten', 4, NULL, 'commondata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('205f3efb7997a0fc9755da2b535038da', 23, 0, '0', 'Private Daten', 4, NULL, 'privatedata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('205f3efb7997a0fc9755da2b535038da', 24, 0, '0', 'Studien-/Einrichtungsdaten', 4, NULL, 'studdata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('205f3efb7997a0fc9755da2b535038da', 25, 0, '0', 'Zusätzliche Datenfelder', 4, NULL, 'additionaldata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('205f3efb7997a0fc9755da2b535038da', 26, 0, '0', 'Eigene Kategorien', 4, NULL, 'owncategory');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('205f3efb7997a0fc9755da2b535038da', 27, 0, '0', 'Plugins', 4, NULL, 'plugins');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 28, 0, '0', 'Allgemeine Daten', 4, NULL, 'commondata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 29, 0, '0', 'Private Daten', 4, NULL, 'privatedata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 30, 0, '0', 'Studien-/Einrichtungsdaten', 4, NULL, 'studdata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 31, 0, '0', 'Zusätzliche Datenfelder', 4, NULL, 'additionaldata');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 32, 0, '0', 'Eigene Kategorien', 4, NULL, 'owncategory');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 33, 0, '0', 'Plugins', 4, NULL, 'plugins');
REPLACE INTO `user_visibility_settings` (`user_id`, `visibilityid`, `parent_id`, `category`, `name`, `state`, `plugin`, `identifier`) VALUES('7e81ec247c151c02ffd479511e24cc03', 34, 31, '1', 'Matrikelnummer', 4, NULL, 'ce73a10d07b3bb13c0132d363549efda');

--
-- Daten für Tabelle `user_info`
--

REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES('76ed43ef286fb55cf9e41beadb484a9f', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES('205f3efb7997a0fc9755da2b535038da', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES('6235c46eb9e962866ebdceece739ace5', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');
REPLACE INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `publi`, `schwerp`, `Home`, `privatnr`, `privatcell`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `title_front`, `title_rear`, `preferred_language`, `smsforward_copy`, `smsforward_rec`, `email_forward`, `smiley_favorite`, `motto`, `lock_rule`) VALUES('7e81ec247c151c02ffd479511e24cc03', '', '', '', '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, '', '', '');

--
-- Daten für Tabelle `user_inst`
--

REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('205f3efb7997a0fc9755da2b535038da', '2560f7c7674942a7dce8eeb238e15d93', 'dozent', '', '', '', '', 0, 0, 1);
REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('6235c46eb9e962866ebdceece739ace5', '2560f7c7674942a7dce8eeb238e15d93', 'admin', '', '', '', '', 0, 0, 1);
REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('7e81ec247c151c02ffd479511e24cc03', '2560f7c7674942a7dce8eeb238e15d93', 'tutor', '', '', '', '', 0, 0, 1);
REPLACE INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`, `externdefault`, `priority`, `visible`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', '2560f7c7674942a7dce8eeb238e15d93', 'user', '', '', '', '', 1, 0, 1);

--
-- Daten für Tabelle `user_studiengang`
--

REPLACE INTO `user_studiengang` (`user_id`, `fach_id`, `semester`, `abschluss_id`) VALUES('e7a0a84b161f3e8c09b4a0a2e8a58147', '6b9ac09535885ca55e29dd011e377c0a', 2, '228234544820cdf75db55b42d1ea3ecc');
REPLACE INTO `user_studiengang` (`user_id`, `fach_id`, `semester`, `abschluss_id`) VALUES('7e81ec247c151c02ffd479511e24cc03', 'f981c9b42ca72788a09da4a45794a737', 1, '228234544820cdf75db55b42d1ea3ecc');
