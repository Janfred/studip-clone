
#
# Dumping data for table `admission_seminar_studiengang`
#
INSERT INTO `admission_seminar_studiengang` (`seminar_id`, `studiengang_id`, `quota`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', '4780c15be9f63594440dd48fca054d06', 50);
INSERT INTO `admission_seminar_studiengang` (`seminar_id`, `studiengang_id`, `quota`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', 'a4f2fd9ba41c3433c3fbbd87f74eabd2', 40);
INSERT INTO `admission_seminar_studiengang` (`seminar_id`, `studiengang_id`, `quota`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', 'all', 10);
#
# Dumping data for table `admission_seminar_user`
#
#
# Dumping data for table `archiv`
#
#
# Dumping data for table `archiv_user`
#
#
# Dumping data for table `auth_user_md5`
#
INSERT INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`) VALUES ('f7fc4adacb450600ed22cb6abdaedd91', 'test_autor', 'ae2b1fca515949e5d54fb22b8ed95575', 'autor', 'Test', 'Autor', 'info@studip.de');
INSERT INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`) VALUES ('12fd5b8766c19ef6ee50fb94231659d3', 'test_tutor', 'ae2b1fca515949e5d54fb22b8ed95575', 'tutor', 'Test', 'Tutor', 'info@studip.de');
INSERT INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`) VALUES ('a25ec520443b6b2a7deb6688804e5b26', 'test_dozent', 'ae2b1fca515949e5d54fb22b8ed95575', 'dozent', 'Test', 'Dozent', 'info@studip.de');
INSERT INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`) VALUES ('157ee45ad191f25b39a86664b036e5e3', 'test_admin', 'ae2b1fca515949e5d54fb22b8ed95575', 'admin', 'Test', 'Admin', 'info@studip.de');
INSERT INTO `auth_user_md5` (`user_id`, `username`, `password`, `perms`, `Vorname`, `Nachname`, `Email`) VALUES ('d2efba7cfecb87a86ba11d225848e9f9', 'test_fak_admin', 'ae2b1fca515949e5d54fb22b8ed95575', 'admin', 'Test', 'Admin (Fakultät)', 'info@studip.de');
#
# Dumping data for table `contact`
#
INSERT INTO `contact` (`contact_id`, `owner_id`, `user_id`, `buddy`) VALUES ('0979087506eb1e4afdfa130ef0a165d4', 'f7fc4adacb450600ed22cb6abdaedd91', 'a25ec520443b6b2a7deb6688804e5b26', 1);
INSERT INTO `contact` (`contact_id`, `owner_id`, `user_id`, `buddy`) VALUES ('b648621a313cb4ee711830a086fe7ab5', 'f7fc4adacb450600ed22cb6abdaedd91', '12fd5b8766c19ef6ee50fb94231659d3', 1);
#
# Dumping data for table `contact_userinfo`
#
#
# Dumping data for table `dokumente`
#
#
# Dumping data for table `extern_config`
#
#
# Dumping data for table `folder`
#
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('1544c80107894655e29b9f01062eae34', '92b89ae00ae39d467c3cd5a1a9a53445', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1048795298, 1048795298);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('2c914518cdb07d9552553fe702c1970c', '8eec88158b9742e868dd47104620f614', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1048795330, 1048795330);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('7421a65a3eb7687e90ce2e1139b221d7', 'feca0e3ccd285b1f414ddcde7299ba29', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1048795368, 1048795368);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('886c89cda4b4154d198aec24c90a28bf', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1048796294, 1048796294);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('cea739f8213c827a9143a1285e078011', 'c2aae30732fe32178f40e86ef130fd17', '157ee45ad191f25b39a86664b036e5e3', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1048796674, 1048796674);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('6255ca4b4f117b2a104602ed9168613d', 'fb41520a81db27b6881b0e40b813627b', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1051714664, 1051714664);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('ad3a0a7009bd994e109cb2f4f38d615f', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1051715127, 1051715127);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('8b9b8e380c0f41049409cc109d2f6270', '18d08887fdb7fa3ced99195986ad78f4', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 17.06.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1051715233, 1051715463);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('6222eb4d5765c517a9ed725ef173d149', 'd1718fcb6e0fc19e1fd1e0d4b3790c15', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 01.07.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1051715233, 1051715463);
INSERT INTO `folder` (`folder_id`, `range_id`, `user_id`, `name`, `description`, `mkdate`, `chdate`) VALUES ('158045383644e5649bd5e3bae6d50fec', '0577536619fe3d6c4a8536e23954df83', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 15.07.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1051715234, 1051715463);
#
# Dumping data for table `globalmessages`
#
INSERT INTO `globalmessages` (`user_id_rec`, `user_id_snd`, `mkdate`, `message`, `message_id`, `chat_id`) VALUES ('test_dozent', 'root@studip', 1051715772, 'Ihre persönliche Seite wurde von einer Administratorin oder einem Administrator verändert.\n Folgende Veränderungen wurden vorgenommen:\n \nIhre persönlichen Daten wurden geändert.\n', 'ca8f3f5bf5a1e0d56dec8e14d8324020', NULL);
INSERT INTO `globalmessages` (`user_id_rec`, `user_id_snd`, `mkdate`, `message`, `message_id`, `chat_id`) VALUES ('test_tutor', 'root@studip', 1051715824, 'Ihre persönliche Seite wurde von einer Administratorin oder einem Administrator verändert.\n Folgende Veränderungen wurden vorgenommen:\n \nIhre persönlichen Daten wurden geändert.\n', '0aa795c59e2c29c435b8ffdb963001a0', NULL);
#
# Dumping data for table `institute`
#
INSERT INTO `institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`) VALUES ('92b89ae00ae39d467c3cd5a1a9a53445', 'Demo Fakultät', '92b89ae00ae39d467c3cd5a1a9a53445', 'Georg-Müller-Str. 32', '37075 Göttingen', 'www.studip.de', '0551 / 9963325', 'test@studip.de', '0551 / 9963326', 7, 1048795298, 1048795298);
INSERT INTO `institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`) VALUES ('8eec88158b9742e868dd47104620f614', 'Test Einrichtung', '92b89ae00ae39d467c3cd5a1a9a53445', 'Albrecht-Thaer-Weg 72', '37075 Göttingen', 'www.studip.de', '0551 / 9963327', 'test@studip.de', '0551 / 9963328', 1, 1048795330, 1048795330);
INSERT INTO `institute` (`Institut_id`, `Name`, `fakultaets_id`, `Strasse`, `Plz`, `url`, `telefon`, `email`, `fax`, `type`, `mkdate`, `chdate`) VALUES ('feca0e3ccd285b1f414ddcde7299ba29', 'Test Abteilung', '92b89ae00ae39d467c3cd5a1a9a53445', 'Albrecht-Dürer-Weg 18', '37075 Göttingen', 'www.studip.de', '0551 / 99633223', 'info@ckater.de', '0551 / 99633222', 4, 1048795368, 1048795368);
#
# Dumping data for table `kategorien`
#
INSERT INTO `kategorien` (`kategorie_id`, `range_id`, `name`, `content`, `hidden`, `mkdate`, `chdate`, `priority`) VALUES ('c1a2679038f846c37e87e8da517f5a0e', '446fcff18c676a7ad05848c5b611e1cb', 'Erklärung', 'Diese Einrichtung dient nur als Test.', 0, 1048795457, 1048795457, 0);
INSERT INTO `kategorien` (`kategorie_id`, `range_id`, `name`, `content`, `hidden`, `mkdate`, `chdate`, `priority`) VALUES ('9141904ef341f634bf1af24000829791', '4711264a82cab32b9fb13d6ddf8c67c8', 'Erklärung', 'Dies ist eine übergeordnete Einrichtung ("Fakultät").', 0, 1051714085, 1051714085, 0);
INSERT INTO `kategorien` (`kategorie_id`, `range_id`, `name`, `content`, `hidden`, `mkdate`, `chdate`, `priority`) VALUES ('0af17ba3d13475591056e26573a6baef', 'bcf67d880ae408f78322ac42ba78703a', 'Erklärung', 'Diese Einrichtung dient nur als Test.', 0, 1051714186, 1051714186, 0);
#
# Dumping data for table `literatur`
#
INSERT INTO `literatur` (`literatur_id`, `range_id`, `user_id`, `literatur`, `links`, `mkdate`, `chdate`) VALUES ('e53a9559f67b2f784f96e8cc260bc078', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', '', '', 1048796300, 1048796300);
#
# Dumping data for table `news`
#
#
# Dumping data for table `news_range`
#
#
# Dumping data for table `px_topics`
#
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('c54f1a605ac5d04e01c58d2a01c10a0f', '0', 'c54f1a605ac5d04e01c58d2a01c10a0f', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048795298, 1048795298, '', '134.76.62.67', '92b89ae00ae39d467c3cd5a1a9a53445', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('e8567d6e1f66a6d5e6a23b3b7f0f8295', '0', 'e8567d6e1f66a6d5e6a23b3b7f0f8295', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048795330, 1048795330, '', '134.76.62.67', '8eec88158b9742e868dd47104620f614', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('9237e9cce296698f951efb27fb8c7bf4', '0', '9237e9cce296698f951efb27fb8c7bf4', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048795368, 1048795368, '', '134.76.62.67', 'feca0e3ccd285b1f414ddcde7299ba29', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('deb1f92bd68daad159d1801a573f19ed', '0', 'deb1f92bd68daad159d1801a573f19ed', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048796294, 1048796294, 'Root Studip', '134.76.62.67', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('abc38321da4e016543243b8764cc3e62', '0', 'abc38321da4e016543243b8764cc3e62', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048796674, 1048796674, 'Test Admin', '134.76.62.67', 'c2aae30732fe32178f40e86ef130fd17', '157ee45ad191f25b39a86664b036e5e3');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('a71028b7f6f44978ae57cd46f7a25c84', '0', 'a71028b7f6f44978ae57cd46f7a25c84', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1051714664, 1051714664, 'Root Studip', '127.0.0.1', 'fb41520a81db27b6881b0e40b813627b', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('081b170c5fb81a2d57b05177fc686885', '0', '081b170c5fb81a2d57b05177fc686885', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1051715127, 1051715127, 'Root Studip', '127.0.0.1', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('5f67bfa2966ee30e2b848aa17d01f43c', '0', '5f67bfa2966ee30e2b848aa17d01f43c', 'Sitzung: Kein Titel am 17.06.2003', 'Hier kann zu diesem Termin diskutiert werden', 1051715233, 1051715463, 'Root Studip', '127.0.0.1', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('fc2b73a717fe2daa69fc58ab46944899', '0', 'fc2b73a717fe2daa69fc58ab46944899', 'Sitzung: Kein Titel am 01.07.2003', 'Hier kann zu diesem Termin diskutiert werden', 1051715233, 1051715463, 'Root Studip', '127.0.0.1', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` (`topic_id`, `parent_id`, `root_id`, `name`, `description`, `mkdate`, `chdate`, `author`, `author_host`, `Seminar_id`, `user_id`) VALUES ('5aa43cf59095ca6557bfec4abfa04062', '0', '5aa43cf59095ca6557bfec4abfa04062', 'Sitzung: Kein Titel am 15.07.2003', 'Hier kann zu diesem Termin diskutiert werden', 1051715234, 1051715463, 'Root Studip', '127.0.0.1', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f');
#
# Dumping data for table `range_tree`
#
INSERT INTO `range_tree` (`item_id`, `parent_id`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('446fcff18c676a7ad05848c5b611e1cb', '4711264a82cab32b9fb13d6ddf8c67c8', 0, 'Test Einrichtung', 'inst', '8eec88158b9742e868dd47104620f614');
INSERT INTO `range_tree` (`item_id`, `parent_id`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('bcf67d880ae408f78322ac42ba78703a', '446fcff18c676a7ad05848c5b611e1cb', 0, 'Test Abteilung', 'inst', 'feca0e3ccd285b1f414ddcde7299ba29');
INSERT INTO `range_tree` (`item_id`, `parent_id`, `priority`, `name`, `studip_object`, `studip_object_id`) VALUES ('4711264a82cab32b9fb13d6ddf8c67c8', 'root', 2, 'Demo Fakultät', 'fak', '92b89ae00ae39d467c3cd5a1a9a53445');
#
# Dumping data for table `resources_assign`
#
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('f1828d744cb5d621bac2d4f84254d328', '47107f2140bdfa0ba0352c32af45535f', '091735e55ed66c375d0b369b66247086', '', 1047898800, 1047906000, 1047906000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796294, 1048796313);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('0a9dcdd68b62b9328a5b6b6cf20b8dff', '47107f2140bdfa0ba0352c32af45535f', '7eb4623c4618389be9dfecc66386ac6d', '', 1051516800, 1051524000, 1051524000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('1f4454ee0b7724ec41cb4976c1435d19', '47107f2140bdfa0ba0352c32af45535f', '300f929e10a4ebbd6b417ac0f3d33f13', '', 1052121600, 1052128800, 1052128800, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('6af15fee2610ada87ec76e7300d4a0d4', '47107f2140bdfa0ba0352c32af45535f', '00d59caf9666c18b464bef87c7a12a7c', '', 1052726400, 1052733600, 1052733600, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('691648b092d4fe2dbd9c6253f354d5f7', '47107f2140bdfa0ba0352c32af45535f', 'a3e24c56c402036ff4b5bd5cf2d4821c', '', 1053331200, 1053338400, 1053338400, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('10daed68583885910b49a0564a76035f', '47107f2140bdfa0ba0352c32af45535f', '9b0a28629d70f0994d15e061604cf292', '', 1053936000, 1053943200, 1053943200, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('cdfd85427fa46b1aeebb3e2abc20625c', '47107f2140bdfa0ba0352c32af45535f', '44718d51d8947804012de6433e2b8c8d', '', 1054540800, 1054548000, 1054548000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('4b31ec57566e35b05b4ddd62d35c4449', '47107f2140bdfa0ba0352c32af45535f', '14a86f4956c30a259f8154c4ff1c85eb', '', 1055750400, 1055757600, 1055757600, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('7f9015f5a0085fa4d2e2b5def40f0cdb', '47107f2140bdfa0ba0352c32af45535f', 'f38f179eb5bef72c5f70ca5547ab1360', '', 1056355200, 1056362400, 1056362400, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('320229f11e0e94ab59bec04ce0139c9c', '47107f2140bdfa0ba0352c32af45535f', '89eae36de6acda54b7605d770ca6de8e', '', 1056960000, 1056967200, 1056967200, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('281087ead645fe9ad5aa042b2f17d78b', '47107f2140bdfa0ba0352c32af45535f', 'aa404410a199644bca1bfae410f31e39', '', 1057564800, 1057572000, 1057572000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796304, 1048796304);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('693365b754724e55c5a924cf7dc7fa24', '47107f2140bdfa0ba0352c32af45535f', 'effcf72aff71bb7dd61dff7eef7bd4bb', '', 1058169600, 1058176800, 1058176800, 0, 0, 0, 0, 0, 0, 0, 0, 1048796304, 1048796304);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('e5e138062ed039bd070a249f1d9d4974', '47107f2140bdfa0ba0352c32af45535f', '18d08887fdb7fa3ced99195986ad78f4', '', 1055837700, 1055843100, 1055843100, 0, 0, 0, 0, 0, 0, 0, 0, 1051715233, 1051715233);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('19d02a3ff03a68b1be353f64303fd7c5', '47107f2140bdfa0ba0352c32af45535f', 'd1718fcb6e0fc19e1fd1e0d4b3790c15', '', 1057047300, 1057052700, 1057052700, 0, 0, 0, 0, 0, 0, 0, 0, 1051715234, 1051715234);
INSERT INTO `resources_assign` (`assign_id`, `resource_id`, `assign_user_id`, `user_free_name`, `begin`, `end`, `repeat_end`, `repeat_quantity`, `repeat_interval`, `repeat_month_of_year`, `repeat_day_of_month`, `repeat_month`, `repeat_week_of_month`, `repeat_day_of_week`, `repeat_week`, `mkdate`, `chdate`) VALUES ('727de649fde6eb97af39e10a9d9e4823', '47107f2140bdfa0ba0352c32af45535f', '0577536619fe3d6c4a8536e23954df83', '', 1058256900, 1058262300, 1058262300, 0, 0, 0, 0, 0, 0, 0, 0, 1051715234, 1051715234);
#
# Dumping data for table `resources_categories`
#
INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `iconnr`) VALUES ('1cf2a34de92c06137ecdfcef4a29e4bc', 'Raum', '', 1, 3);
INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `iconnr`) VALUES ('82bdd20907e914de72bbfc8043dd3a46', 'Gebäude', '', 0, 1);
INSERT INTO `resources_categories` (`category_id`, `name`, `description`, `system`, `iconnr`) VALUES ('891662c701078186c857fca25d34ade6', 'Gerät', '', 0, 2);
#
# Dumping data for table `resources_categories_properties`
#
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('82bdd20907e914de72bbfc8043dd3a46', '8772d6757457c8b4a05b180e1c2eba9c', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('82bdd20907e914de72bbfc8043dd3a46', '5753ab43945ae787f983f5c8a036712d', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('891662c701078186c857fca25d34ade6', '1b86b5026052fd3d8624fead31204cba', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('891662c701078186c857fca25d34ade6', '9c0658891b95fe962d013f1308feb80d', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('891662c701078186c857fca25d34ade6', '7bff1a7d45bc37280e988f6e8d007bad', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('1cf2a34de92c06137ecdfcef4a29e4bc', '0ef8a73d95f335cdfbaec50cae92762a', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('1cf2a34de92c06137ecdfcef4a29e4bc', '5753ab43945ae787f983f5c8a036712d', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('1cf2a34de92c06137ecdfcef4a29e4bc', '31abad810703df361d793361bf6b16e5', 0);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('1cf2a34de92c06137ecdfcef4a29e4bc', 'ef4ba565e635b45c3f43ecdc69fb4aca', 1);
INSERT INTO `resources_categories_properties` (`category_id`, `property_id`, `system`) VALUES ('1cf2a34de92c06137ecdfcef4a29e4bc', '648b8579ffca64a565459fd6ea0313c5', 0);
#
# Dumping data for table `resources_objects`
#
INSERT INTO `resources_objects` (`resource_id`, `root_id`, `parent_id`, `category_id`, `owner_id`, `level`, `name`, `description`, `inventar_num`, `parent_bind`, `mkdate`, `chdate`) VALUES ('6e06400579d08e356608972fae08206d', '6e06400579d08e356608972fae08206d', '0', '', '8eec88158b9742e868dd47104620f614', '0', 'Veranstaltungsräume', '', '', 0, 1048795905, 1048795966);
INSERT INTO `resources_objects` (`resource_id`, `root_id`, `parent_id`, `category_id`, `owner_id`, `level`, `name`, `description`, `inventar_num`, `parent_bind`, `mkdate`, `chdate`) VALUES ('47107f2140bdfa0ba0352c32af45535f', '6e06400579d08e356608972fae08206d', '6e06400579d08e356608972fae08206d', '1cf2a34de92c06137ecdfcef4a29e4bc', '76ed43ef286fb55cf9e41beadb484a9f', '1', 'Testraum', 'Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen.', '', 0, 1048795977, 1048795985);
INSERT INTO `resources_objects` (`resource_id`, `root_id`, `parent_id`, `category_id`, `owner_id`, `level`, `name`, `description`, `inventar_num`, `parent_bind`, `mkdate`, `chdate`) VALUES ('5faaee03ea6215e12a2bd345e6f8398d', '6e06400579d08e356608972fae08206d', '6e06400579d08e356608972fae08206d', '82bdd20907e914de72bbfc8043dd3a46', '8eec88158b9742e868dd47104620f614', '1', 'Test Gebäude', '', '', 0, 1048796002, 1048796030);
#
# Dumping data for table `resources_objects_properties`
#
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('5faaee03ea6215e12a2bd345e6f8398d', '8772d6757457c8b4a05b180e1c2eba9c', '');
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('5faaee03ea6215e12a2bd345e6f8398d', '5753ab43945ae787f983f5c8a036712d', 'on');
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('47107f2140bdfa0ba0352c32af45535f', 'ef4ba565e635b45c3f43ecdc69fb4aca', '25');
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('47107f2140bdfa0ba0352c32af45535f', '0ef8a73d95f335cdfbaec50cae92762a', '');
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('47107f2140bdfa0ba0352c32af45535f', '31abad810703df361d793361bf6b16e5', 'Übungsraum');
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('47107f2140bdfa0ba0352c32af45535f', '5753ab43945ae787f983f5c8a036712d', 'on');
INSERT INTO `resources_objects_properties` (`resource_id`, `property_id`, `state`) VALUES ('47107f2140bdfa0ba0352c32af45535f', '648b8579ffca64a565459fd6ea0313c5', 'on');
#
# Dumping data for table `resources_properties`
#
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('ef4ba565e635b45c3f43ecdc69fb4aca', 'Sitzplätze', '', 'num', '', 1);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('8772d6757457c8b4a05b180e1c2eba9c', 'Adresse', '', 'text', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('0ef8a73d95f335cdfbaec50cae92762a', 'Ausstattung', '', 'text', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('7bff1a7d45bc37280e988f6e8d007bad', 'Seriennummer', '', 'num', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('31abad810703df361d793361bf6b16e5', 'Raumtyp', '', 'select', 'Hörsaal;Übungsraum;Sitzungszimmer', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('5753ab43945ae787f983f5c8a036712d', 'behindertengerecht', '', 'bool', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('648b8579ffca64a565459fd6ea0313c5', 'Verdunklung', '', 'bool', 'vorhanden', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('9c0658891b95fe962d013f1308feb80d', 'Hersteller', '', 'num', '', 0);
INSERT INTO `resources_properties` (`property_id`, `name`, `description`, `type`, `options`, `system`) VALUES ('1b86b5026052fd3d8624fead31204cba', 'Kaufdatum', '', 'num', '', 0);
#
# Dumping data for table `resources_user_resources`
#
#
# Dumping data for table `sem_tree`
#
INSERT INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('e5462fe499926db698c4e0ab6b263774', 'root', 1, '', '', '92b89ae00ae39d467c3cd5a1a9a53445');
INSERT INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('2c97382a307c8b78a791c18be83ed4ce', 'e5462fe499926db698c4e0ab6b263774', 0, 'Ein Demonstrationsbereich', 'Studienbereich A - Virtuelle Lehre', NULL);
INSERT INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('b455161f3770b6624407d534ac3037c6', 'e5462fe499926db698c4e0ab6b263774', 1, '', 'Studienbereich B - Präsenzlehre', NULL);
INSERT INTO `sem_tree` (`sem_tree_id`, `parent_id`, `priority`, `info`, `name`, `studip_object_id`) VALUES ('ee3c16ca7364cb96c3949a148c0295e5', 'e5462fe499926db698c4e0ab6b263774', 2, '', 'Studienbereich C - sonstiges', NULL);
#
# Dumping data for table `seminar_inst`
#
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', 'feca0e3ccd285b1f414ddcde7299ba29');
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('7009adabf5107440876e2b971bd3a888', '8eec88158b9742e868dd47104620f614');
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('c2aae30732fe32178f40e86ef130fd17', '8eec88158b9742e868dd47104620f614');
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('c2aae30732fe32178f40e86ef130fd17', '92b89ae00ae39d467c3cd5a1a9a53445');
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('fb41520a81db27b6881b0e40b813627b', '8eec88158b9742e868dd47104620f614');
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('fb41520a81db27b6881b0e40b813627b', '92b89ae00ae39d467c3cd5a1a9a53445');
INSERT INTO `seminar_inst` (`seminar_id`, `institut_id`) VALUES ('fb41520a81db27b6881b0e40b813627b', 'feca0e3ccd285b1f414ddcde7299ba29');
#
# Dumping data for table `seminar_lernmodul`
#
#
# Dumping data for table `seminar_sem_tree`
#
INSERT INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', 'ee3c16ca7364cb96c3949a148c0295e5');
INSERT INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES ('7009adabf5107440876e2b971bd3a888', '2c97382a307c8b78a791c18be83ed4ce');
INSERT INTO `seminar_sem_tree` (`seminar_id`, `sem_tree_id`) VALUES ('7009adabf5107440876e2b971bd3a888', 'ee3c16ca7364cb96c3949a148c0295e5');
#
# Dumping data for table `seminar_user`
#
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('7009adabf5107440876e2b971bd3a888', 'a25ec520443b6b2a7deb6688804e5b26', 'dozent', 2, '', 1048796294);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('7009adabf5107440876e2b971bd3a888', '12fd5b8766c19ef6ee50fb94231659d3', 'tutor', 2, '', 1048796294);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('7009adabf5107440876e2b971bd3a888', 'f7fc4adacb450600ed22cb6abdaedd91', 'autor', 7, '', 1048796470);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('c2aae30732fe32178f40e86ef130fd17', 'a25ec520443b6b2a7deb6688804e5b26', 'dozent', 2, '', 1048796674);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('c2aae30732fe32178f40e86ef130fd17', 'f7fc4adacb450600ed22cb6abdaedd91', 'autor', 7, '', 1048796733);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('c2aae30732fe32178f40e86ef130fd17', '12fd5b8766c19ef6ee50fb94231659d3', 'autor', 7, '', 1048796799);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('fb41520a81db27b6881b0e40b813627b', 'a25ec520443b6b2a7deb6688804e5b26', 'dozent', 2, '', 1051714664);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('fb41520a81db27b6881b0e40b813627b', '12fd5b8766c19ef6ee50fb94231659d3', 'tutor', 2, '', 1051714664);
INSERT INTO `seminar_user` (`Seminar_id`, `user_id`, `status`, `gruppe`, `admission_studiengang_id`, `mkdate`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', 'a25ec520443b6b2a7deb6688804e5b26', 'dozent', 2, '', 1051715127);
#
# Dumping data for table `seminare`
#
INSERT INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Passwort`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `metadata_dates`, `mkdate`, `chdate`, `ects`, `admission_endtime`, `admission_turnout`, `admission_binding`, `admission_type`, `admission_selection_take_place`, `showscore`) VALUES ('7009adabf5107440876e2b971bd3a888', '0', '8eec88158b9742e868dd47104620f614', 'Test Lehrveranstaltung', '', '2', '', '', '', '', 1, 1, 1049148000, 0, '', '', 'Interesse', 'Kleingruppen', 'Klausur', 'a:5:{s:3:"art";s:1:"0";s:12:"start_termin";s:2:"-1";s:11:"start_woche";s:1:"0";s:6:"turnus";s:1:"0";s:11:"turnus_data";a:1:{i:0;a:8:{s:3:"idx";s:5:"11000";s:3:"day";s:1:"1";s:12:"start_stunde";s:2:"10";s:12:"start_minute";s:1:"0";s:10:"end_stunde";s:2:"12";s:10:"end_minute";s:1:"0";s:4:"room";s:8:"Testraum";s:11:"resource_id";s:32:"47107f2140bdfa0ba0352c32af45535f";}}}', 1048796294, 1048796294, '', -1, 0, 0, 0, 0, 0);
INSERT INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Passwort`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `metadata_dates`, `mkdate`, `chdate`, `ects`, `admission_endtime`, `admission_turnout`, `admission_binding`, `admission_type`, `admission_selection_take_place`, `showscore`) VALUES ('c2aae30732fe32178f40e86ef130fd17', '0', '8eec88158b9742e868dd47104620f614', 'Feedbackforum', 'Kommentare und Fragen zum System', '13', '', '', '', '', 0, 0, 1049148000, -1, '', '', '', '', '', '', 1048796674, 1048796674, '', -1, 0, 0, 0, 0, 0);
INSERT INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Passwort`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `metadata_dates`, `mkdate`, `chdate`, `ects`, `admission_endtime`, `admission_turnout`, `admission_binding`, `admission_type`, `admission_selection_take_place`, `showscore`) VALUES ('fb41520a81db27b6881b0e40b813627b', '', '92b89ae00ae39d467c3cd5a1a9a53445', 'Fakultätsrat', '', '8', 'Hier werden wichtige Dinge besprochen.', '', '', 'ae2b1fca515949e5d54fb22b8ed95575', 2, 2, 1049148000, -1, '', '', '', '', '', '', 1051714664, 1051714664, '', -1, 0, 0, 0, 0, 0);
INSERT INTO `seminare` (`Seminar_id`, `VeranstaltungsNummer`, `Institut_id`, `Name`, `Untertitel`, `status`, `Beschreibung`, `Ort`, `Sonstiges`, `Passwort`, `Lesezugriff`, `Schreibzugriff`, `start_time`, `duration_time`, `art`, `teilnehmer`, `vorrausetzungen`, `lernorga`, `leistungsnachweis`, `metadata_dates`, `mkdate`, `chdate`, `ects`, `admission_endtime`, `admission_turnout`, `admission_binding`, `admission_type`, `admission_selection_take_place`, `showscore`) VALUES ('35f0ab24761e9e426e1e3dbe5e46a0fa', '123456', 'feca0e3ccd285b1f414ddcde7299ba29', 'Test Lehrveranstaltung 2 (zugangsbeschränkt)', '', '1', 'Zugangsbeschränkte Veranstaltung', '', '', '', 3, 3, 1049148000, 0, '', '', '', '', '', 'a:5:{s:3:"art";s:1:"0";s:12:"start_termin";i:1055800800;s:11:"start_woche";s:2:"-1";s:6:"turnus";s:1:"1";s:11:"turnus_data";a:1:{i:0;a:8:{s:3:"idx";s:5:"21015";s:3:"day";s:1:"2";s:12:"start_stunde";i:10;s:12:"start_minute";i:15;s:10:"end_stunde";i:11;s:10:"end_minute";i:45;s:4:"room";s:8:"Testraum";s:11:"resource_id";s:32:"47107f2140bdfa0ba0352c32af45535f";}}}', 1051715125, 1051715463, '', 1055627999, 15, 0, 2, 0, 0);
#
# Dumping data for table `statusgruppe_user`
#
INSERT INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`) VALUES ('178fa6ee83e8312c636845d865c071b3', '12fd5b8766c19ef6ee50fb94231659d3');
INSERT INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`) VALUES ('178fa6ee83e8312c636845d865c071b3', 'a25ec520443b6b2a7deb6688804e5b26');
INSERT INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`) VALUES ('237c1b79c14e10d8060f461427d80a02', 'a25ec520443b6b2a7deb6688804e5b26');
INSERT INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`) VALUES ('6ecccc7b87655d8cd783658b2924ad02', '12fd5b8766c19ef6ee50fb94231659d3');
INSERT INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`) VALUES ('6ecccc7b87655d8cd783658b2924ad02', 'a25ec520443b6b2a7deb6688804e5b26');
INSERT INTO `statusgruppe_user` (`statusgruppe_id`, `user_id`) VALUES ('d326a1313836259b713e25ddac93b299', '12fd5b8766c19ef6ee50fb94231659d3');
#
# Dumping data for table `statusgruppen`
#
INSERT INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `mkdate`, `chdate`) VALUES ('178fa6ee83e8312c636845d865c071b3', 'Lehrende', '8eec88158b9742e868dd47104620f614', 1, 5, 1048796125, 1048796125);
INSERT INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `mkdate`, `chdate`) VALUES ('237c1b79c14e10d8060f461427d80a02', 'HochschullehrerIn', 'feca0e3ccd285b1f414ddcde7299ba29', 1, 0, 1051714449, 1051714449);
INSERT INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `mkdate`, `chdate`) VALUES ('d326a1313836259b713e25ddac93b299', 'stud. Hilfskraft', 'feca0e3ccd285b1f414ddcde7299ba29', 2, 0, 1051714462, 1051714462);
INSERT INTO `statusgruppen` (`statusgruppe_id`, `name`, `range_id`, `position`, `size`, `mkdate`, `chdate`) VALUES ('6ecccc7b87655d8cd783658b2924ad02', 'Meine liebsten Lehrenden', 'f7fc4adacb450600ed22cb6abdaedd91', 1, 0, 1051716073, 1051716073);
#
# Dumping data for table `studiengaenge`
#
INSERT INTO `studiengaenge` (`studiengang_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES ('9442ab7d2a41e2fca158f507202fdbcd', 'Virtuelle Lehrsysteme', '', 1048795730, 1048795730);
INSERT INTO `studiengaenge` (`studiengang_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES ('4780c15be9f63594440dd48fca054d06', 'Jura', '', 1048795747, 1048795747);
INSERT INTO `studiengaenge` (`studiengang_id`, `name`, `beschreibung`, `mkdate`, `chdate`) VALUES ('a4f2fd9ba41c3433c3fbbd87f74eabd2', 'Soziologie', '', 1048795755, 1048795755);
#
# Dumping data for table `studip_ilias`
#
#
# Dumping data for table `termine`
#
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('091735e55ed66c375d0b369b66247086', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Vorbesprechung', '', 1047898800, 1047906000, 1048796294, 1048796313, 2, '0', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('7eb4623c4618389be9dfecc66386ac6d', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1051516800, 1051524000, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('300f929e10a4ebbd6b417ac0f3d33f13', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1052121600, 1052128800, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('00d59caf9666c18b464bef87c7a12a7c', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1052726400, 1052733600, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('a3e24c56c402036ff4b5bd5cf2d4821c', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1053331200, 1053338400, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('9b0a28629d70f0994d15e061604cf292', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1053936000, 1053943200, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('44718d51d8947804012de6433e2b8c8d', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1054540800, 1054548000, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('14a86f4956c30a259f8154c4ff1c85eb', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1055750400, 1055757600, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('f38f179eb5bef72c5f70ca5547ab1360', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1056355200, 1056362400, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('89eae36de6acda54b7605d770ca6de8e', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1056960000, 1056967200, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('aa404410a199644bca1bfae410f31e39', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1057564800, 1057572000, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('effcf72aff71bb7dd61dff7eef7bd4bb', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1058169600, 1058176800, 1048796304, 1048796304, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('18d08887fdb7fa3ced99195986ad78f4', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1055837700, 1055843100, 1051715233, 1051715463, 1, '5f67bfa2966ee30e2b848aa17d01f43c', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('d1718fcb6e0fc19e1fd1e0d4b3790c15', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1057047300, 1057052700, 1051715233, 1051715463, 1, 'fc2b73a717fe2daa69fc58ab46944899', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO `termine` (`termin_id`, `range_id`, `autor_id`, `content`, `description`, `date`, `end_time`, `mkdate`, `chdate`, `date_typ`, `topic_id`, `expire`, `repeat`, `color`, `priority`, `raum`) VALUES ('0577536619fe3d6c4a8536e23954df83', '35f0ab24761e9e426e1e3dbe5e46a0fa', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1058256900, 1058262300, 1051715234, 1051715463, 1, '5aa43cf59095ca6557bfec4abfa04062', NULL, NULL, NULL, NULL, 'Testraum');
#
# Dumping data for table `user_info`
#
INSERT INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `raum`, `sprechzeiten`, `publi`, `schwerp`, `Lehre`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `hide_studiengang`, `preferred_language`, `title_front`, `title_rear`) VALUES ('f7fc4adacb450600ed22cb6abdaedd91', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795803, 1048795803, NULL, NULL, '', '');
INSERT INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `raum`, `sprechzeiten`, `publi`, `schwerp`, `Lehre`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `hide_studiengang`, `preferred_language`, `title_front`, `title_rear`) VALUES ('12fd5b8766c19ef6ee50fb94231659d3', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795821, 1051715824, NULL, NULL, '', 'MBA');
INSERT INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `raum`, `sprechzeiten`, `publi`, `schwerp`, `Lehre`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `hide_studiengang`, `preferred_language`, `title_front`, `title_rear`) VALUES ('a25ec520443b6b2a7deb6688804e5b26', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795849, 1051715772, NULL, NULL, 'Prof. Dr.', '');
INSERT INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `raum`, `sprechzeiten`, `publi`, `schwerp`, `Lehre`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `hide_studiengang`, `preferred_language`, `title_front`, `title_rear`) VALUES ('157ee45ad191f25b39a86664b036e5e3', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795876, 1048795876, NULL, NULL, '', '');
INSERT INTO `user_info` (`user_id`, `hobby`, `lebenslauf`, `raum`, `sprechzeiten`, `publi`, `schwerp`, `Lehre`, `Home`, `privatnr`, `privadr`, `score`, `geschlecht`, `mkdate`, `chdate`, `hide_studiengang`, `preferred_language`, `title_front`, `title_rear`) VALUES ('d2efba7cfecb87a86ba11d225848e9f9', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795876, 1048795876, NULL, NULL, '', '');
#
# Dumping data for table `user_inst`
#
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('a25ec520443b6b2a7deb6688804e5b26', '8eec88158b9742e868dd47104620f614', 'dozent', '', '', '', '');
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('157ee45ad191f25b39a86664b036e5e3', '8eec88158b9742e868dd47104620f614', 'admin', '', '', '', '');
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('12fd5b8766c19ef6ee50fb94231659d3', '8eec88158b9742e868dd47104620f614', 'tutor', '', '', '', '');
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('d2efba7cfecb87a86ba11d225848e9f9', '92b89ae00ae39d467c3cd5a1a9a53445', 'admin', '', '', '', '');
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('a25ec520443b6b2a7deb6688804e5b26', 'feca0e3ccd285b1f414ddcde7299ba29', 'dozent', '', '', '', '');
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('12fd5b8766c19ef6ee50fb94231659d3', 'feca0e3ccd285b1f414ddcde7299ba29', 'tutor', '', '', '', '');
INSERT INTO `user_inst` (`user_id`, `Institut_id`, `inst_perms`, `sprechzeiten`, `raum`, `Telefon`, `Fax`) VALUES ('f7fc4adacb450600ed22cb6abdaedd91', 'feca0e3ccd285b1f414ddcde7299ba29', 'user', '', '', '', '');
#
# Dumping data for table `user_studiengang`
#
INSERT INTO `user_studiengang` (`user_id`, `studiengang_id`) VALUES ('f7fc4adacb450600ed22cb6abdaedd91', '9442ab7d2a41e2fca158f507202fdbcd');
    
