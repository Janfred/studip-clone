# phpMyAdmin MySQL-Dump
# version 2.3.3pl1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 27. März 2003 um 21:30
# Server Version: 3.23.52
# PHP-Version: 4.2.2
# Datenbank: `studip`

#
# Daten für Tabelle `Institute`
#

INSERT INTO Institute VALUES ('92b89ae00ae39d467c3cd5a1a9a53445', 'Demo Fakultät', '92b89ae00ae39d467c3cd5a1a9a53445', 'Georg-Müller-Str. 32', '37075 Göttingen', 'www.studip.de', '0551 / 9963325', 'test@studip.de', '0551 / 9963326', 7, 1048795298, 1048795298);
INSERT INTO Institute VALUES ('8eec88158b9742e868dd47104620f614', 'Test Einrichtung', '92b89ae00ae39d467c3cd5a1a9a53445', 'Albrecht-Thaer-Weg 72', '37075 Göttingen', 'www.studip.de', '0551 / 9963327', 'test@studip.de', '0551 / 9963328', 1, 1048795330, 1048795330);
INSERT INTO Institute VALUES ('feca0e3ccd285b1f414ddcde7299ba29', 'Test Abteilung', '92b89ae00ae39d467c3cd5a1a9a53445', 'Albrecht-Dürer-Weg 18', '37075 Göttingen', 'www.studip.de', '0551 / 99633223', 'info@ckater.de', '0551 / 99633222', 4, 1048795368, 1048795368);


#
# Daten für Tabelle `admission_seminar_studiengang`
#


#
# Daten für Tabelle `admission_seminar_user`
#


#
# Daten für Tabelle `archiv`
#


#
# Daten für Tabelle `archiv_user`
#


#
# Daten für Tabelle `auth_user_md5`
#

INSERT INTO auth_user_md5 VALUES ('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', 'ae2b1fca515949e5d54fb22b8ed95575', 'root', 'Root', 'Studip', 'root@localhost');
INSERT INTO auth_user_md5 VALUES ('f7fc4adacb450600ed22cb6abdaedd91', 'test_autor', 'ae2b1fca515949e5d54fb22b8ed95575', 'autor', 'Test', 'Autor', 'info@studip.de');
INSERT INTO auth_user_md5 VALUES ('12fd5b8766c19ef6ee50fb94231659d3', 'test_tutor', 'ae2b1fca515949e5d54fb22b8ed95575', 'tutor', 'Test', 'Tutor', 'info@studip.de');
INSERT INTO auth_user_md5 VALUES ('a25ec520443b6b2a7deb6688804e5b26', 'test_dozent', 'ae2b1fca515949e5d54fb22b8ed95575', 'dozent', 'Test', 'Dozent', 'info@studip.de');
INSERT INTO auth_user_md5 VALUES ('157ee45ad191f25b39a86664b036e5e3', 'test_admin', 'ae2b1fca515949e5d54fb22b8ed95575', 'admin', 'Test', 'Admin', 'info@studip.de');

#
# Daten für Tabelle `contact`
#


#
# Daten für Tabelle `contact_userinfo`
#


#
# Daten für Tabelle `dokumente`
#


#
# Daten für Tabelle `extern_config`
#


#
# Daten für Tabelle `folder`
#

INSERT INTO folder VALUES ('1544c80107894655e29b9f01062eae34', '92b89ae00ae39d467c3cd5a1a9a53445', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1048795298, 1048795298);
INSERT INTO folder VALUES ('2c914518cdb07d9552553fe702c1970c', '8eec88158b9742e868dd47104620f614', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1048795330, 1048795330);
INSERT INTO folder VALUES ('7421a65a3eb7687e90ce2e1139b221d7', 'feca0e3ccd285b1f414ddcde7299ba29', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1048795368, 1048795368);
INSERT INTO folder VALUES ('886c89cda4b4154d198aec24c90a28bf', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1048796294, 1048796294);
INSERT INTO folder VALUES ('cea739f8213c827a9143a1285e078011', 'c2aae30732fe32178f40e86ef130fd17', '157ee45ad191f25b39a86664b036e5e3', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1048796674, 1048796674);

#
# Daten für Tabelle `globalmessages`
#


#
# Daten für Tabelle `kategorien`
#

INSERT INTO kategorien VALUES ('c1a2679038f846c37e87e8da517f5a0e', '446fcff18c676a7ad05848c5b611e1cb', 'Erklärung', 'Diese Einrichtung dient nur als Test.', 0, 1048795457, 1048795457, 0);

#
# Daten für Tabelle `literatur`
#

INSERT INTO literatur VALUES ('e53a9559f67b2f784f96e8cc260bc078', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', '', '', 1048796300, 1048796300);

#
# Daten für Tabelle `news`
#


#
# Daten für Tabelle `news_range`
#


#
# Daten für Tabelle `px_topics`
#

INSERT INTO px_topics VALUES ('c54f1a605ac5d04e01c58d2a01c10a0f', '0', 'c54f1a605ac5d04e01c58d2a01c10a0f', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048795298, 1048795298, '', '134.76.62.67', '92b89ae00ae39d467c3cd5a1a9a53445', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('e8567d6e1f66a6d5e6a23b3b7f0f8295', '0', 'e8567d6e1f66a6d5e6a23b3b7f0f8295', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048795330, 1048795330, '', '134.76.62.67', '8eec88158b9742e868dd47104620f614', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('9237e9cce296698f951efb27fb8c7bf4', '0', '9237e9cce296698f951efb27fb8c7bf4', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048795368, 1048795368, '', '134.76.62.67', 'feca0e3ccd285b1f414ddcde7299ba29', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('deb1f92bd68daad159d1801a573f19ed', '0', 'deb1f92bd68daad159d1801a573f19ed', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048796294, 1048796294, 'Root Studip', '134.76.62.67', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('abc38321da4e016543243b8764cc3e62', '0', 'abc38321da4e016543243b8764cc3e62', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1048796674, 1048796674, 'Test Admin', '134.76.62.67', 'c2aae30732fe32178f40e86ef130fd17', '157ee45ad191f25b39a86664b036e5e3');

#
# Daten für Tabelle `range_tree`
#

INSERT INTO range_tree VALUES ('446fcff18c676a7ad05848c5b611e1cb', 'root', 0, 'Test Einrichtung', 'inst', '8eec88158b9742e868dd47104620f614');
INSERT INTO range_tree VALUES ('bcf67d880ae408f78322ac42ba78703a', 'root', 1, 'Test Abteilung', 'inst', 'feca0e3ccd285b1f414ddcde7299ba29');

#
# Daten für Tabelle `resources_assign`
#

INSERT INTO resources_assign VALUES ('f1828d744cb5d621bac2d4f84254d328', '47107f2140bdfa0ba0352c32af45535f', '091735e55ed66c375d0b369b66247086', '', 1047898800, 1047906000, 1047906000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796294, 1048796313);
INSERT INTO resources_assign VALUES ('0a9dcdd68b62b9328a5b6b6cf20b8dff', '47107f2140bdfa0ba0352c32af45535f', '7eb4623c4618389be9dfecc66386ac6d', '', 1051516800, 1051524000, 1051524000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('1f4454ee0b7724ec41cb4976c1435d19', '47107f2140bdfa0ba0352c32af45535f', '300f929e10a4ebbd6b417ac0f3d33f13', '', 1052121600, 1052128800, 1052128800, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('6af15fee2610ada87ec76e7300d4a0d4', '47107f2140bdfa0ba0352c32af45535f', '00d59caf9666c18b464bef87c7a12a7c', '', 1052726400, 1052733600, 1052733600, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('691648b092d4fe2dbd9c6253f354d5f7', '47107f2140bdfa0ba0352c32af45535f', 'a3e24c56c402036ff4b5bd5cf2d4821c', '', 1053331200, 1053338400, 1053338400, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('10daed68583885910b49a0564a76035f', '47107f2140bdfa0ba0352c32af45535f', '9b0a28629d70f0994d15e061604cf292', '', 1053936000, 1053943200, 1053943200, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('cdfd85427fa46b1aeebb3e2abc20625c', '47107f2140bdfa0ba0352c32af45535f', '44718d51d8947804012de6433e2b8c8d', '', 1054540800, 1054548000, 1054548000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('4b31ec57566e35b05b4ddd62d35c4449', '47107f2140bdfa0ba0352c32af45535f', '14a86f4956c30a259f8154c4ff1c85eb', '', 1055750400, 1055757600, 1055757600, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('7f9015f5a0085fa4d2e2b5def40f0cdb', '47107f2140bdfa0ba0352c32af45535f', 'f38f179eb5bef72c5f70ca5547ab1360', '', 1056355200, 1056362400, 1056362400, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('320229f11e0e94ab59bec04ce0139c9c', '47107f2140bdfa0ba0352c32af45535f', '89eae36de6acda54b7605d770ca6de8e', '', 1056960000, 1056967200, 1056967200, 0, 0, 0, 0, 0, 0, 0, 0, 1048796303, 1048796303);
INSERT INTO resources_assign VALUES ('281087ead645fe9ad5aa042b2f17d78b', '47107f2140bdfa0ba0352c32af45535f', 'aa404410a199644bca1bfae410f31e39', '', 1057564800, 1057572000, 1057572000, 0, 0, 0, 0, 0, 0, 0, 0, 1048796304, 1048796304);
INSERT INTO resources_assign VALUES ('693365b754724e55c5a924cf7dc7fa24', '47107f2140bdfa0ba0352c32af45535f', 'effcf72aff71bb7dd61dff7eef7bd4bb', '', 1058169600, 1058176800, 1058176800, 0, 0, 0, 0, 0, 0, 0, 0, 1048796304, 1048796304);

#
# Daten für Tabelle `resources_objects`
#

INSERT INTO resources_objects VALUES ('6e06400579d08e356608972fae08206d', '6e06400579d08e356608972fae08206d', '0', '', '8eec88158b9742e868dd47104620f614', '0', 'Veranstaltungsräume', '', '', 0, 1048795905, 1048795966);
INSERT INTO resources_objects VALUES ('47107f2140bdfa0ba0352c32af45535f', '6e06400579d08e356608972fae08206d', '6e06400579d08e356608972fae08206d', '1cf2a34de92c06137ecdfcef4a29e4bc', '76ed43ef286fb55cf9e41beadb484a9f', '1', 'Testraum', 'Dieses Objekt wurde neu erstellt. Es wurden noch keine Eigenschaften zugewiesen.', '', 0, 1048795977, 1048795985);
INSERT INTO resources_objects VALUES ('5faaee03ea6215e12a2bd345e6f8398d', '6e06400579d08e356608972fae08206d', '6e06400579d08e356608972fae08206d', '82bdd20907e914de72bbfc8043dd3a46', '8eec88158b9742e868dd47104620f614', '1', 'Test Gebäude', '', '', 0, 1048796002, 1048796030);

#
# Daten für Tabelle `resources_objects_properties`
#

INSERT INTO resources_objects_properties VALUES ('5faaee03ea6215e12a2bd345e6f8398d', '8772d6757457c8b4a05b180e1c2eba9c', '');
INSERT INTO resources_objects_properties VALUES ('5faaee03ea6215e12a2bd345e6f8398d', '5753ab43945ae787f983f5c8a036712d', 'on');
INSERT INTO resources_objects_properties VALUES ('47107f2140bdfa0ba0352c32af45535f', 'ef4ba565e635b45c3f43ecdc69fb4aca', '25');
INSERT INTO resources_objects_properties VALUES ('47107f2140bdfa0ba0352c32af45535f', '0ef8a73d95f335cdfbaec50cae92762a', '');
INSERT INTO resources_objects_properties VALUES ('47107f2140bdfa0ba0352c32af45535f', '31abad810703df361d793361bf6b16e5', 'Übungsraum');
INSERT INTO resources_objects_properties VALUES ('47107f2140bdfa0ba0352c32af45535f', '5753ab43945ae787f983f5c8a036712d', 'on');
INSERT INTO resources_objects_properties VALUES ('47107f2140bdfa0ba0352c32af45535f', '648b8579ffca64a565459fd6ea0313c5', 'on');


#
# Daten für Tabelle `resources_user_resources`
#


#
# Daten für Tabelle `sem_tree`
#

INSERT INTO sem_tree VALUES ('e5462fe499926db698c4e0ab6b263774', 'root', 1, '', '', '92b89ae00ae39d467c3cd5a1a9a53445');
INSERT INTO sem_tree VALUES ('2c97382a307c8b78a791c18be83ed4ce', 'e5462fe499926db698c4e0ab6b263774', 0, 'Ein Demonstrationsbereich', 'Studienbereich A - Virtuelle Lehre', NULL);
INSERT INTO sem_tree VALUES ('b455161f3770b6624407d534ac3037c6', 'e5462fe499926db698c4e0ab6b263774', 1, '', 'Studienbereich B - Präsenzlehre', NULL);
INSERT INTO sem_tree VALUES ('ee3c16ca7364cb96c3949a148c0295e5', 'e5462fe499926db698c4e0ab6b263774', 2, '', 'Studienbereich C - sonstiges', NULL);

#
# Daten für Tabelle `seminar_inst`
#

INSERT INTO seminar_inst VALUES ('7009adabf5107440876e2b971bd3a888', '8eec88158b9742e868dd47104620f614');
INSERT INTO seminar_inst VALUES ('c2aae30732fe32178f40e86ef130fd17', '8eec88158b9742e868dd47104620f614');
INSERT INTO seminar_inst VALUES ('c2aae30732fe32178f40e86ef130fd17', '92b89ae00ae39d467c3cd5a1a9a53445');

#
# Daten für Tabelle `seminar_lernmodul`
#


#
# Daten für Tabelle `seminar_sem_tree`
#

INSERT INTO seminar_sem_tree VALUES ('7009adabf5107440876e2b971bd3a888', '2c97382a307c8b78a791c18be83ed4ce');
INSERT INTO seminar_sem_tree VALUES ('7009adabf5107440876e2b971bd3a888', 'ee3c16ca7364cb96c3949a148c0295e5');

#
# Daten für Tabelle `seminar_user`
#

INSERT INTO seminar_user VALUES ('7009adabf5107440876e2b971bd3a888', 'a25ec520443b6b2a7deb6688804e5b26', 'dozent', 2, '', 1048796294);
INSERT INTO seminar_user VALUES ('7009adabf5107440876e2b971bd3a888', '12fd5b8766c19ef6ee50fb94231659d3', 'tutor', 2, '', 1048796294);
INSERT INTO seminar_user VALUES ('7009adabf5107440876e2b971bd3a888', 'f7fc4adacb450600ed22cb6abdaedd91', 'autor', 7, '', 1048796470);
INSERT INTO seminar_user VALUES ('c2aae30732fe32178f40e86ef130fd17', 'a25ec520443b6b2a7deb6688804e5b26', 'dozent', 2, '', 1048796674);
INSERT INTO seminar_user VALUES ('c2aae30732fe32178f40e86ef130fd17', 'f7fc4adacb450600ed22cb6abdaedd91', 'autor', 7, '', 1048796733);
INSERT INTO seminar_user VALUES ('c2aae30732fe32178f40e86ef130fd17', '12fd5b8766c19ef6ee50fb94231659d3', 'autor', 7, '', 1048796799);

#
# Daten für Tabelle `seminare`
#

INSERT INTO seminare VALUES ('7009adabf5107440876e2b971bd3a888', 0, '8eec88158b9742e868dd47104620f614', 'Test Lehrveranstaltung', '', '2', '', '', '', '', 1, 1, 1049148000, 0, '', '', 'Interesse', 'Kleingruppen', 'Klausur', 'a:5:{s:3:"art";s:1:"0";s:12:"start_termin";s:2:"-1";s:11:"start_woche";s:1:"0";s:6:"turnus";s:1:"0";s:11:"turnus_data";a:1:{i:0;a:8:{s:3:"idx";s:5:"11000";s:3:"day";s:1:"1";s:12:"start_stunde";s:2:"10";s:12:"start_minute";s:1:"0";s:10:"end_stunde";s:2:"12";s:10:"end_minute";s:1:"0";s:4:"room";s:8:"Testraum";s:11:"resource_id";s:32:"47107f2140bdfa0ba0352c32af45535f";}}}', 1048796294, 1048796294, '', -1, 0, 0, 0, 0, 0);
INSERT INTO seminare VALUES ('c2aae30732fe32178f40e86ef130fd17', 0, '8eec88158b9742e868dd47104620f614', 'Feedbackforum', 'Kommentare und Fragen zum System', '13', '', '', '', '', 0, 0, 1049148000, -1, '', '', '', '', '', '', 1048796674, 1048796674, '', -1, 0, 0, 0, 0, 0);

#
# Daten für Tabelle `statusgruppe_user`
#

INSERT INTO statusgruppe_user VALUES ('178fa6ee83e8312c636845d865c071b3', '12fd5b8766c19ef6ee50fb94231659d3');
INSERT INTO statusgruppe_user VALUES ('178fa6ee83e8312c636845d865c071b3', 'a25ec520443b6b2a7deb6688804e5b26');

#
# Daten für Tabelle `statusgruppen`
#

INSERT INTO statusgruppen VALUES ('178fa6ee83e8312c636845d865c071b3', 'Lehrende', '8eec88158b9742e868dd47104620f614', 1, 5, 1048796125, 1048796125);

#
# Daten für Tabelle `studiengaenge`
#

INSERT INTO studiengaenge VALUES ('9442ab7d2a41e2fca158f507202fdbcd', 'Virtuelle Lehrsysteme', '', 1048795730, 1048795730);
INSERT INTO studiengaenge VALUES ('4780c15be9f63594440dd48fca054d06', 'Jura', '', 1048795747, 1048795747);
INSERT INTO studiengaenge VALUES ('a4f2fd9ba41c3433c3fbbd87f74eabd2', 'Soziologie', '', 1048795755, 1048795755);

#
# Daten für Tabelle `studip_ilias`
#


#
# Daten für Tabelle `termine`
#

INSERT INTO termine VALUES ('091735e55ed66c375d0b369b66247086', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Vorbesprechung', '', 1047898800, 1047906000, 1048796294, 1048796313, 2, '0', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('7eb4623c4618389be9dfecc66386ac6d', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1051516800, 1051524000, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('300f929e10a4ebbd6b417ac0f3d33f13', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1052121600, 1052128800, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('00d59caf9666c18b464bef87c7a12a7c', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1052726400, 1052733600, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('a3e24c56c402036ff4b5bd5cf2d4821c', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1053331200, 1053338400, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('9b0a28629d70f0994d15e061604cf292', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1053936000, 1053943200, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('44718d51d8947804012de6433e2b8c8d', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1054540800, 1054548000, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('14a86f4956c30a259f8154c4ff1c85eb', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1055750400, 1055757600, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('f38f179eb5bef72c5f70ca5547ab1360', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1056355200, 1056362400, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('89eae36de6acda54b7605d770ca6de8e', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1056960000, 1056967200, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('aa404410a199644bca1bfae410f31e39', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1057564800, 1057572000, 1048796303, 1048796303, 1, '', NULL, NULL, NULL, NULL, 'Testraum');
INSERT INTO termine VALUES ('effcf72aff71bb7dd61dff7eef7bd4bb', '7009adabf5107440876e2b971bd3a888', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1058169600, 1058176800, 1048796304, 1048796304, 1, '', NULL, NULL, NULL, NULL, 'Testraum');

#
# Daten für Tabelle `user_info`
#

INSERT INTO user_info VALUES ('76ed43ef286fb55cf9e41beadb484a9f', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 0, 0, NULL, NULL, '', '');
INSERT INTO user_info VALUES ('f7fc4adacb450600ed22cb6abdaedd91', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795803, 1048795803, NULL, NULL, '', '');
INSERT INTO user_info VALUES ('12fd5b8766c19ef6ee50fb94231659d3', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795821, 1048795821, NULL, NULL, '', '');
INSERT INTO user_info VALUES ('a25ec520443b6b2a7deb6688804e5b26', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795849, 1048795849, NULL, NULL, '', '');
INSERT INTO user_info VALUES ('157ee45ad191f25b39a86664b036e5e3', '', NULL, NULL, NULL, '', '', '', '', '', '', 0, 0, 1048795876, 1048795876, NULL, NULL, '', '');

#
# Daten für Tabelle `user_inst`
#

INSERT INTO user_inst VALUES ('a25ec520443b6b2a7deb6688804e5b26', '8eec88158b9742e868dd47104620f614', 'dozent', '', '', '', '');
INSERT INTO user_inst VALUES ('157ee45ad191f25b39a86664b036e5e3', '8eec88158b9742e868dd47104620f614', 'admin', '', '', '', '');
INSERT INTO user_inst VALUES ('12fd5b8766c19ef6ee50fb94231659d3', '8eec88158b9742e868dd47104620f614', 'tutor', '', '', '', '');

#
# Daten für Tabelle `user_studiengang`
#

