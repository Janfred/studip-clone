# phpMyAdmin MySQL-Dump
# version 2.3.3pl1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 29. Oktober 2003 um 12:12
# Server Version: 3.23.52
# PHP-Version: 4.2.2
# Datenbank: `studip`

#
# Daten für Tabelle `Institute`
#

INSERT INTO Institute VALUES ('d9a2cb67781cb478caef29fd14a0653a', 'Test-Fakultät', 'd9a2cb67781cb478caef29fd14a0653a', 'Geismar Landstr. 17b', '37083 Göttingen', 'http://www.studip.de', '0551 / 381 985 0', 'testfakultaet@studip.de', '0551 / 381 985 3', 7, 179, 1066997621, 1067423638);
INSERT INTO Institute VALUES ('6a1f27ed3c07b1cff22f467e8bd20868', 'Test-Einrichtung', 'd9a2cb67781cb478caef29fd14a0653a', 'Geismar Landstr. 17b', '37083 Göttingen', 'www.studip.de', '0551 / 381 985 0', 'testeinrichtung@studip.de', '0551 / 381 985 3', 1, 179, 1067423615, 1067423615);

#
# Daten für Tabelle `auth_user_md5`
#

INSERT IGNORE INTO auth_user_md5 VALUES ('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', 'ae2b1fca515949e5d54fb22b8ed95575', 'root', 'Root', 'Studip', 'root@localhost', NULL);
INSERT INTO auth_user_md5 VALUES ('205f3efb7997a0fc9755da2b535038da', 'test_dozent', 'ae2b1fca515949e5d54fb22b8ed95575', 'dozent', 'Testaccount', 'Dozent', 'dozent@studip.de', NULL);
INSERT INTO auth_user_md5 VALUES ('6235c46eb9e962866ebdceece739ace5', 'test_admin', 'ae2b1fca515949e5d54fb22b8ed95575', 'admin', 'Testaccount', 'Admin', 'admin@studip.de', NULL);
INSERT INTO auth_user_md5 VALUES ('7e81ec247c151c02ffd479511e24cc03', 'test_tutor', 'ae2b1fca515949e5d54fb22b8ed95575', 'tutor', 'Testaccount', 'Tutor', 'tutor@studip.de', NULL);
INSERT INTO auth_user_md5 VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', 'test_autor', 'ae2b1fca515949e5d54fb22b8ed95575', 'autor', 'Testaccount', 'Autor', 'autor@studip.de', NULL);

#
# Daten für Tabelle `folder`
#

INSERT INTO folder VALUES ('60dab5a0f4bc9da27759119ca678523e', 'd9a2cb67781cb478caef29fd14a0653a', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1066997621, 1066997621);
INSERT INTO folder VALUES ('121c23103f4006f48d1900d14b8456bb', '6a1f27ed3c07b1cff22f467e8bd20868', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1067423615, 1067423615);
INSERT INTO folder VALUES ('4cb68ee5e7f65059aa26327b4ea93d7c', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1067424154, 1067424154);
INSERT INTO folder VALUES ('b2eb66de1e60d7b505270a307dd5d206', '049616112f21acf8567013820e3878ce', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 20.10.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424271, 1067425166);
INSERT INTO folder VALUES ('bb39059956122c909cd041e27cfdb530', '7e55c42bfa96389c1f601f93f5f26db0', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 23.10.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425166);
INSERT INTO folder VALUES ('698e1327adfb63e6e2cd8aa8cc71deed', 'c187467f7d49cb3824428a0ca3fed1e2', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 27.10.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425166);
INSERT INTO folder VALUES ('5852c7d13aa61e8628a5b04052856ab4', '75d8545383cfe668ec3d346b05afe2a4', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 30.10.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425166);
INSERT INTO folder VALUES ('78471ee028a012a414787e1bb4ce0bef', '6426bd7e032e2e21d27eb45f79ea1bc8', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 03.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425166);
INSERT INTO folder VALUES ('bb4f31eb95d71bc24bbfb3142b64336d', '930b0f91e7ba58d6a56d18dee783482d', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 06.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('e51e4b690b9e2ffd5263576264a0b9ba', 'e880531505932cb1af91b4aedb68560f', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 10.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('66ecf6789cf7eecf255138faba9fb14a', 'ef6de39cd7b74f57dc431ca6d43b1364', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 13.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('23ddf22259b94fa99455c20084c44787', '0627fae9f6fcee584db9b2478b98d128', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 17.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('a84da90bb2a7d8cc803f05f0e9f8005a', 'e02bcc95b7b5a89314b15fea1077b415', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 20.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('86f1dc5446fb26bf457947081f8f1f75', '27a53e480be39615139e25cca31ec832', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 24.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('f8a447ba4e29164ea1a948c28ae51315', '57b0cf0e704407b1791592bba21ca22f', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 27.11.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('b466946f90cb8a08d0f36c8730ee4730', '58c11e07905705a5e4b55483db66f84e', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 01.12.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('470f5986e630eff8e61da9f8ed570f8a', '59af1de2f72f0d4f01e174773916af41', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 04.12.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('1e2206d6452ea967dd5ffe1d79811e18', '3762407314151dd0674e61c0c4234d38', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 08.12.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('be85964b497df03c8899875dafb62479', 'a746e21ece7630b2e6e2a7f354e67fd4', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 11.12.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('288988b95afa33b8383ccae19977f6d2', 'af259534e1d569f6ef563ffd3e3c0bbc', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 15.12.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('e0d2d6a4f6846023c1fcc3d6bafe022f', '939a229c1e5462bf69bd67eabf6938c6', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 18.12.2003', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424272, 1067425167);
INSERT INTO folder VALUES ('c54bc2bba23988b15b40005ffb272eb4', '581a6065357f2a2b25e2aed40e1797c9', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 05.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425167);
INSERT INTO folder VALUES ('738c98a9e0d4386257ce633425325453', '81825595bec9d6097d44223f8770c59b', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 08.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('e1f1154076c19d02b1741261edd20f7c', 'b456e5c0985c24b49f08fac99f768f5e', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 12.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('47a1cf460a55b80f32e11baf8d39f833', '8ecc2035ecf5378773086e126a951832', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 15.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('a1805baaf098dc5137d65695a0c8299c', '68fd6ec6efba9c46023f1b85dff80974', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 19.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('e220c3ac66e769bff95a73d6797f5b04', '01b76210274246a06ff80f2b3874344c', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 22.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('24d1dad9fe0838684d5aa32eff9e9df1', 'd10f4f1ab03a747217365e05f9072027', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 26.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('abf1228c6b2aa780108d8d2fb0901fdd', 'f9dbc6deddbae235ca713b7346f6355b', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 29.01.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('f1859de72dd1c0978d2f60c22008ff44', '4895b056cb06379c3a2193661a5419b7', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 02.02.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('7426e2ce8fb9fff852062710d923841f', '71d22e1dd7e9dbe188e1884aebbad8a6', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 05.02.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1067424273, 1067425168);
INSERT INTO folder VALUES ('4d927a73f23c71af7360ad1d07dfd5b9', 'b4b9f08454b3871e697caea84f8f22c2', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1067425274, 1067425274);

#
# Daten für Tabelle `news`
#

INSERT IGNORE INTO news VALUES ('29f2932ce32be989022c6f43b866e744', 'Herzlich Willkommen!', 'Das Stud.IP-Team heisst sie herzlich willkommen. \r\nBitte schauen Sie sich ruhig um!\r\n\r\nWenn Sie das System selbst installiert haben und diese News sehen, haben Sie die Demonstrationsdaten in die Datenbank eingefügt. Wenn Sie produktiv mit dem System arbeiten wollen, sollten Sie diese Daten später wieder löschen, da die Passwörter der Accounts (vor allem des root-Accounts) öffentlich bekannt sind.', 'Root Studip', UNIX_TIMESTAMP(NOW()), '76ed43ef286fb55cf9e41beadb484a9f', 7343999);

#
# Daten für Tabelle `news_range`
#

INSERT INTO news_range VALUES ('29f2932ce32be989022c6f43b866e744', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO news_range VALUES ('29f2932ce32be989022c6f43b866e744', 'studip');

#
# Daten für Tabelle `px_topics`
#

INSERT INTO px_topics VALUES ('27e0b6806c65f5d8e69497582eb2efb3', '0', '27e0b6806c65f5d8e69497582eb2efb3', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1066997621, 1066997621, '', '82.82.159.129', 'd9a2cb67781cb478caef29fd14a0653a', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('c195a93402bba46d424bd07cc8f9651a', '0', 'c195a93402bba46d424bd07cc8f9651a', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1067423615, 1067423615, '', '213.23.233.209', '6a1f27ed3c07b1cff22f467e8bd20868', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('e16a753465395b7cb04fed6d1d27e858', '0', 'e16a753465395b7cb04fed6d1d27e858', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1067424154, 1067424154, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('f24255f809a8dbfd5063fa9ec8508f13', '0', 'f24255f809a8dbfd5063fa9ec8508f13', 'Sitzung: Kein Titel am 20.10.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424271, 1067425166, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('b5c64a3ab8ce81c019d719ebf0cfd612', '0', 'b5c64a3ab8ce81c019d719ebf0cfd612', 'Sitzung: Kein Titel am 23.10.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425166, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('7080e92cf5880ca11e6554014c95f7f4', '0', '7080e92cf5880ca11e6554014c95f7f4', 'Sitzung: Kein Titel am 27.10.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425166, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('2aa3c87a1bf554130e77e9e5e6cfea1a', '0', '2aa3c87a1bf554130e77e9e5e6cfea1a', 'Sitzung: Kein Titel am 30.10.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425166, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('07e712098fa59d116c1a0b3c7feb3b25', '0', '07e712098fa59d116c1a0b3c7feb3b25', 'Sitzung: Kein Titel am 03.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425166, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('ff0a8ee14ba6239a7111a33323668696', '0', 'ff0a8ee14ba6239a7111a33323668696', 'Sitzung: Kein Titel am 06.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('bc6694d534b53ee7d3e85825f19e583f', '0', 'bc6694d534b53ee7d3e85825f19e583f', 'Sitzung: Kein Titel am 10.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('e56cf52ec332ba0e7ea88ca13237d112', '0', 'e56cf52ec332ba0e7ea88ca13237d112', 'Sitzung: Kein Titel am 13.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('3fceaa866d0daaf2c1319ac3ac99c8df', '0', '3fceaa866d0daaf2c1319ac3ac99c8df', 'Sitzung: Kein Titel am 17.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('89ba1903d0a32967ece574be9e82853e', '0', '89ba1903d0a32967ece574be9e82853e', 'Sitzung: Kein Titel am 20.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('6eb809e3df5608e56ce8def424227982', '0', '6eb809e3df5608e56ce8def424227982', 'Sitzung: Kein Titel am 24.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('2abe059b3aa9021e2badbf8807616f14', '0', '2abe059b3aa9021e2badbf8807616f14', 'Sitzung: Kein Titel am 27.11.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('0ae7abae49ed09b2b6b8905a6b820ee8', '0', '0ae7abae49ed09b2b6b8905a6b820ee8', 'Sitzung: Kein Titel am 01.12.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('f635f82740928e193572b36ca5db76e2', '0', 'f635f82740928e193572b36ca5db76e2', 'Sitzung: Kein Titel am 04.12.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('f831e0b0ff790f5226f3bca9fc31f969', '0', 'f831e0b0ff790f5226f3bca9fc31f969', 'Sitzung: Kein Titel am 08.12.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('db0ba0822fca309fbbf7a8bdeecb5e09', '0', 'db0ba0822fca309fbbf7a8bdeecb5e09', 'Sitzung: Kein Titel am 11.12.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('dff914ffe337d6b59a403589f1672ff4', '0', 'dff914ffe337d6b59a403589f1672ff4', 'Sitzung: Kein Titel am 15.12.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('f7a6cdf74fba7a29238d7568b6f80e09', '0', 'f7a6cdf74fba7a29238d7568b6f80e09', 'Sitzung: Kein Titel am 18.12.2003', 'Hier kann zu diesem Termin diskutiert werden', 1067424272, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('f89b9b75d019073c3910361623b9974c', '0', 'f89b9b75d019073c3910361623b9974c', 'Sitzung: Kein Titel am 05.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425167, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('24717f7883b5c2cbd59533606f6b1c6f', '0', '24717f7883b5c2cbd59533606f6b1c6f', 'Sitzung: Kein Titel am 08.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('bfc05a7bf3b61b019bc4d9c9ef6c9ef8', '0', 'bfc05a7bf3b61b019bc4d9c9ef6c9ef8', 'Sitzung: Kein Titel am 12.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('e2eb162aa327c9f448816a2ce3d02164', '0', 'e2eb162aa327c9f448816a2ce3d02164', 'Sitzung: Kein Titel am 15.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('08d6ebb84ed75cc8afbd50d23dcb81a1', '0', '08d6ebb84ed75cc8afbd50d23dcb81a1', 'Sitzung: Kein Titel am 19.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('62970222190f348078180194e0926f76', '0', '62970222190f348078180194e0926f76', 'Sitzung: Kein Titel am 22.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('dfd9d2b50483054e1fcc521bf82f5602', '0', 'dfd9d2b50483054e1fcc521bf82f5602', 'Sitzung: Kein Titel am 26.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('bc4fa1b44ba30d8735d2ed39ee86143d', '0', 'bc4fa1b44ba30d8735d2ed39ee86143d', 'Sitzung: Kein Titel am 29.01.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('9a1e70b55b2cd12184c61758a01c9a70', '0', '9a1e70b55b2cd12184c61758a01c9a70', 'Sitzung: Kein Titel am 02.02.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('86792cfad094dbcd2d7caf155eea661d', '0', '86792cfad094dbcd2d7caf155eea661d', 'Sitzung: Kein Titel am 05.02.2004', 'Hier kann zu diesem Termin diskutiert werden', 1067424273, 1067425168, 'Root Studip', '213.23.233.209', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO px_topics VALUES ('2a192ab371437c021752fb6b8a032db4', '0', '2a192ab371437c021752fb6b8a032db4', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1067425274, 1067425274, 'Root Studip', '213.23.233.209', 'b4b9f08454b3871e697caea84f8f22c2', '76ed43ef286fb55cf9e41beadb484a9f');

#
# Daten für Tabelle `range_tree`
#

INSERT INTO range_tree VALUES ('85db2baab403372b121b189eebe050ee', 'root', 0, 0, 'Test-Fakultät', 'fak', 'd9a2cb67781cb478caef29fd14a0653a');
INSERT INTO range_tree VALUES ('59c37d2e638d827cf3b72f435688b4cd', '85db2baab403372b121b189eebe050ee', 0, 0, 'Test-Einrichtung', 'inst', '6a1f27ed3c07b1cff22f467e8bd20868');

#
# Daten für Tabelle `sem_tree`
#

INSERT INTO sem_tree VALUES ('6d4782f8bb17dadf53e3bf8a9cfb919a', 'root', 1, '', '', 'd9a2cb67781cb478caef29fd14a0653a');
INSERT INTO sem_tree VALUES ('e29e0dfff182b5915c421b65c34264df', '6d4782f8bb17dadf53e3bf8a9cfb919a', 0, '', 'Test-Studienfach 1', NULL);
INSERT INTO sem_tree VALUES ('5b6ad76729028bde2fed8ebb6bf0323a', '6d4782f8bb17dadf53e3bf8a9cfb919a', 1, '', 'Test Studienfach 2', NULL);

#
# Daten für Tabelle `seminar_inst`
#

INSERT INTO seminar_inst VALUES ('0df1d0586ad3a160dd00d4e2789cf8e8', '6a1f27ed3c07b1cff22f467e8bd20868');
INSERT INTO seminar_inst VALUES ('b4b9f08454b3871e697caea84f8f22c2', 'd9a2cb67781cb478caef29fd14a0653a');

#
# Daten für Tabelle `seminar_sem_tree`
#

INSERT INTO seminar_sem_tree VALUES ('0df1d0586ad3a160dd00d4e2789cf8e8', '5b6ad76729028bde2fed8ebb6bf0323a');
INSERT INTO seminar_sem_tree VALUES ('0df1d0586ad3a160dd00d4e2789cf8e8', 'e29e0dfff182b5915c421b65c34264df');

#
# Daten für Tabelle `seminar_user`
#

INSERT INTO seminar_user VALUES ('0df1d0586ad3a160dd00d4e2789cf8e8', '205f3efb7997a0fc9755da2b535038da', 'dozent', 2, '', 1067424154, NULL);
INSERT INTO seminar_user VALUES ('0df1d0586ad3a160dd00d4e2789cf8e8', '7e81ec247c151c02ffd479511e24cc03', 'tutor', 2, '', 1067424154, NULL);
INSERT INTO seminar_user VALUES ('b4b9f08454b3871e697caea84f8f22c2', '205f3efb7997a0fc9755da2b535038da', 'dozent', 2, '', 1067425274, NULL);
INSERT INTO seminar_user VALUES ('b4b9f08454b3871e697caea84f8f22c2', '7e81ec247c151c02ffd479511e24cc03', 'tutor', 2, '', 1067425274, NULL);

#
# Daten für Tabelle `seminare`
#

INSERT INTO seminare VALUES ('0df1d0586ad3a160dd00d4e2789cf8e8', '', '6a1f27ed3c07b1cff22f467e8bd20868', 'Test-Lehrveranstaltung', '', '2', '', '', '', '', 1, 1, 1064959200, 0, '', '', '', '', '', 'a:5:{s:3:"art";s:1:"0";s:12:"start_termin";i:-1;s:11:"start_woche";s:1:"0";s:6:"turnus";s:1:"0";s:11:"turnus_data";a:2:{i:0;a:8:{s:3:"idx";s:5:"11000";s:3:"day";s:1:"1";s:12:"start_stunde";i:10;s:12:"start_minute";i:0;s:10:"end_stunde";i:12;s:10:"end_minute";i:0;s:4:"room";s:9:"Hörsaal 1";s:11:"resource_id";s:32:"6f3e26a53a4c1f40501217c281d4969a";}i:1;a:8:{s:3:"idx";s:5:"41400";s:3:"day";s:1:"4";s:12:"start_stunde";i:14;s:12:"start_minute";i:0;s:10:"end_stunde";i:16;s:10:"end_minute";i:0;s:4:"room";s:9:"Hörsaal 2";s:11:"resource_id";s:32:"d6a41a3a473de72a87ef5fff0c73c510";}}}', 1067424154, 1067425166, '', -1, 0, NULL, 0, 0, NULL, 0, '', 1067424179, -1, 1, 0, 431);
INSERT INTO seminare VALUES ('b4b9f08454b3871e697caea84f8f22c2', '', 'd9a2cb67781cb478caef29fd14a0653a', 'Testveranstaltung Community', '', '11', 'Diese Veranstaltung ist rein virtuell. Hier kann zum Beispiel über Filme oder Bücher dikutiert werden.', '', '', '', 0, 0, 1064959200, -1, '', '', '', '', '', '', 1067425274, 1067425642, '', -1, 0, 0, 0, 0, NULL, 0, '', 1067425319, -1, 1, 0, 387);

#
# Daten für Tabelle `statusgruppe_user`
#

INSERT INTO statusgruppe_user VALUES ('345b175099d24bcd080b8bfc1b0b4512', '205f3efb7997a0fc9755da2b535038da', 0);
INSERT INTO statusgruppe_user VALUES ('9522b28753171e55b86f0dbe4b642678', '7e81ec247c151c02ffd479511e24cc03', 0);
INSERT INTO statusgruppe_user VALUES ('9ce36c46083003cd7cbec361c7cd6e51', '205f3efb7997a0fc9755da2b535038da', 0);

#
# Daten für Tabelle `statusgruppen`
#

INSERT INTO statusgruppen VALUES ('f5f06f1a820d875dbd1d4b19346dab1a', 'unbenannt', 'd9a2cb67781cb478caef29fd14a0653a', 1, 0, 0, 1067423651, 1067423651);
INSERT INTO statusgruppen VALUES ('74619a019a5f6bc3c4cf1ef0abf62b9c', 'unbenannt', 'd9a2cb67781cb478caef29fd14a0653a', 2, 0, 0, 1067423656, 1067423656);
INSERT INTO statusgruppen VALUES ('345b175099d24bcd080b8bfc1b0b4512', 'DirektorIn', '6a1f27ed3c07b1cff22f467e8bd20868', 1, 0, 0, 1067423716, 1067423740);
INSERT INTO statusgruppen VALUES ('9ce36c46083003cd7cbec361c7cd6e51', 'HochschullehrerIn', '6a1f27ed3c07b1cff22f467e8bd20868', 2, 0, 0, 1067423719, 1067423754);
INSERT INTO statusgruppen VALUES ('9522b28753171e55b86f0dbe4b642678', 'stud. Hilfskraft', '6a1f27ed3c07b1cff22f467e8bd20868', 3, 0, 0, 1067423787, 1067423787);

#
# Daten für Tabelle `studiengaenge`
#

INSERT INTO studiengaenge VALUES ('63b13b29db6adcf0e2814a6388d4583c', 'Test Studiengang 1', '', 1067423985, 1067423985);
INSERT INTO studiengaenge VALUES ('4a55e9df07a18e76ebb84e27ae212b30', 'Test Studiengang 2', '', 1067423997, 1067423997);

#
# Daten für Tabelle `termine`
#

INSERT INTO termine VALUES ('049616112f21acf8567013820e3878ce', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1066636800, 1066644000, 1067424271, 1067425166, 1, 'f24255f809a8dbfd5063fa9ec8508f13', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('7e55c42bfa96389c1f601f93f5f26db0', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1066910400, 1066917600, 1067424272, 1067425166, 1, 'b5c64a3ab8ce81c019d719ebf0cfd612', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('c187467f7d49cb3824428a0ca3fed1e2', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1067245200, 1067252400, 1067424272, 1067425166, 1, '7080e92cf5880ca11e6554014c95f7f4', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('75d8545383cfe668ec3d346b05afe2a4', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1067518800, 1067526000, 1067424272, 1067425166, 1, '2aa3c87a1bf554130e77e9e5e6cfea1a', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('6426bd7e032e2e21d27eb45f79ea1bc8', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1067850000, 1067857200, 1067424272, 1067425166, 1, '07e712098fa59d116c1a0b3c7feb3b25', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('930b0f91e7ba58d6a56d18dee783482d', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1068123600, 1068130800, 1067424272, 1067425167, 1, 'ff0a8ee14ba6239a7111a33323668696', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('e880531505932cb1af91b4aedb68560f', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1068454800, 1068462000, 1067424272, 1067425167, 1, 'bc6694d534b53ee7d3e85825f19e583f', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('ef6de39cd7b74f57dc431ca6d43b1364', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1068728400, 1068735600, 1067424272, 1067425167, 1, 'e56cf52ec332ba0e7ea88ca13237d112', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('0627fae9f6fcee584db9b2478b98d128', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1069059600, 1069066800, 1067424272, 1067425167, 1, '3fceaa866d0daaf2c1319ac3ac99c8df', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('e02bcc95b7b5a89314b15fea1077b415', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1069333200, 1069340400, 1067424272, 1067425167, 1, '89ba1903d0a32967ece574be9e82853e', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('27a53e480be39615139e25cca31ec832', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1069664400, 1069671600, 1067424272, 1067425167, 1, '6eb809e3df5608e56ce8def424227982', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('57b0cf0e704407b1791592bba21ca22f', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1069938000, 1069945200, 1067424272, 1067425167, 1, '2abe059b3aa9021e2badbf8807616f14', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('58c11e07905705a5e4b55483db66f84e', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1070269200, 1070276400, 1067424272, 1067425167, 1, '0ae7abae49ed09b2b6b8905a6b820ee8', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('59af1de2f72f0d4f01e174773916af41', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1070542800, 1070550000, 1067424272, 1067425167, 1, 'f635f82740928e193572b36ca5db76e2', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('3762407314151dd0674e61c0c4234d38', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1070874000, 1070881200, 1067424272, 1067425167, 1, 'f831e0b0ff790f5226f3bca9fc31f969', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('a746e21ece7630b2e6e2a7f354e67fd4', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1071147600, 1071154800, 1067424272, 1067425167, 1, 'db0ba0822fca309fbbf7a8bdeecb5e09', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('af259534e1d569f6ef563ffd3e3c0bbc', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1071478800, 1071486000, 1067424272, 1067425167, 1, 'dff914ffe337d6b59a403589f1672ff4', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('939a229c1e5462bf69bd67eabf6938c6', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1071752400, 1071759600, 1067424272, 1067425167, 1, 'f7a6cdf74fba7a29238d7568b6f80e09', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('581a6065357f2a2b25e2aed40e1797c9', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1073293200, 1073300400, 1067424273, 1067425167, 1, 'f89b9b75d019073c3910361623b9974c', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('81825595bec9d6097d44223f8770c59b', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1073566800, 1073574000, 1067424273, 1067425168, 1, '24717f7883b5c2cbd59533606f6b1c6f', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('b456e5c0985c24b49f08fac99f768f5e', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1073898000, 1073905200, 1067424273, 1067425168, 1, 'bfc05a7bf3b61b019bc4d9c9ef6c9ef8', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('8ecc2035ecf5378773086e126a951832', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1074171600, 1074178800, 1067424273, 1067425168, 1, 'e2eb162aa327c9f448816a2ce3d02164', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('68fd6ec6efba9c46023f1b85dff80974', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1074502800, 1074510000, 1067424273, 1067425168, 1, '08d6ebb84ed75cc8afbd50d23dcb81a1', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('01b76210274246a06ff80f2b3874344c', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1074776400, 1074783600, 1067424273, 1067425168, 1, '62970222190f348078180194e0926f76', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('d10f4f1ab03a747217365e05f9072027', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1075107600, 1075114800, 1067424273, 1067425168, 1, 'dfd9d2b50483054e1fcc521bf82f5602', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('f9dbc6deddbae235ca713b7346f6355b', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1075381200, 1075388400, 1067424273, 1067425168, 1, 'bc4fa1b44ba30d8735d2ed39ee86143d', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO termine VALUES ('4895b056cb06379c3a2193661a5419b7', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1075712400, 1075719600, 1067424273, 1067425168, 1, '9a1e70b55b2cd12184c61758a01c9a70', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO termine VALUES ('71d22e1dd7e9dbe188e1884aebbad8a6', '0df1d0586ad3a160dd00d4e2789cf8e8', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1075986000, 1075993200, 1067424273, 1067425168, 1, '86792cfad094dbcd2d7caf155eea661d', NULL, NULL, NULL, NULL, 'Hörsaal 2');

#
# Daten für Tabelle `user_info`
#

INSERT IGNORE INTO user_info VALUES ('76ed43ef286fb55cf9e41beadb484a9f', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 1);
INSERT INTO user_info VALUES ('a4ab271addc1d902e2f2d6e03d747632', '', NULL, '', '', '', '', '', 0, 0, 1066887991, 0, '', 'M.A.', NULL, 1, '', 1);
INSERT INTO user_info VALUES ('205f3efb7997a0fc9755da2b535038da', '', NULL, '', '', '', '', '', 0, 0, 1066998899, 1067423409, '', '', NULL, 1, '', 1);
INSERT INTO user_info VALUES ('6235c46eb9e962866ebdceece739ace5', '', NULL, '', '', '', '', '', 0, 0, 1067423390, 1067423390, '', '', NULL, 1, '', 1);
INSERT INTO user_info VALUES ('7e81ec247c151c02ffd479511e24cc03', '', NULL, '', '', '', '', '', 0, 0, 1067423435, 1067423470, '', '', NULL, 1, '', 1);
INSERT INTO user_info VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', '', NULL, '', '', '', '', '', 0, 0, 1067423459, 1067423459, '', '', NULL, 1, '', 1);

#
# Daten für Tabelle `user_inst`
#

INSERT INTO user_inst VALUES ('7e81ec247c151c02ffd479511e24cc03', '6a1f27ed3c07b1cff22f467e8bd20868', 'tutor', '', '', '', '');
INSERT INTO user_inst VALUES ('205f3efb7997a0fc9755da2b535038da', '6a1f27ed3c07b1cff22f467e8bd20868', 'dozent', '', '', '', '');

#
# Daten für Tabelle `vote`
#

INSERT INTO vote VALUES ('b5329b23b7f865c62028e226715e1914', '76ed43ef286fb55cf9e41beadb484a9f', 'studip', 'vote', 'Nutzen Sie bereits Stud.IP?', 'Haben Sie Stud.IP bereits im Einsatz oder planen Sie, es einzusetzen?', 'active', 1076337204, NULL, NULL, 1076337205, 1076337205, 'delivery', 1, 0, 1, NULL, 0);

#
# Daten für Tabelle `voteanswers`
#

INSERT INTO voteanswers VALUES ('112f7c8f52b0a2a6eff9cddf93b419c7', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.7.5', 0, 0, 0);
INSERT INTO voteanswers VALUES ('c8ade4c7f3bbe027f6c19016dd3e001c', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.8.0', 1, 0, 0);
INSERT INTO voteanswers VALUES ('58281eda805a0fe5741c74a2c612cb05', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.8.15', 2, 0, 0);
INSERT INTO voteanswers VALUES ('ddfd889094a6cea75703728ee7b48806', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.9.0', 3, 0, 0);
INSERT INTO voteanswers VALUES ('dc1b49bf35e9cfbfcece807b21cec0ef', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 0.9.5', 4, 0, 0);
INSERT INTO voteanswers VALUES ('8502e4b4600a12b2d5d43aecf2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.0', 5, 0, 0);
INSERT INTO voteanswers VALUES ('dddf684fbcac58f7ffd0804b7095c71b', 'b5329b23b7f865c62028e226715e1914', 'Ich plane, es demnächst einzusetzen', 6, 0, 0);
INSERT INTO voteanswers VALUES ('03bce9c940fc76f5eb90ab7b151cf34d', 'b5329b23b7f865c62028e226715e1914', 'Ich schaue mich nur mal um', 7, 0, 0);
INSERT INTO voteanswers VALUES ('5fb01b6623c848c3bf33cce70675b91a', 'b5329b23b7f865c62028e226715e1914', 'Ich bin nicht interessiert', 8, 0, 0);

#
# Daten für Tabelle `resources_assign`
#

INSERT INTO resources_assign VALUES ('b1c6c1787e60a61ffe405157c8813434', '6f3e26a53a4c1f40501217c281d4969a', '049616112f21acf8567013820e3878ce', '', 1066636800, 1066644000, 1066644000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425166, 1067425166);
INSERT INTO resources_assign VALUES ('03d27333a8171969eb18ba4be2b64f66', 'd6a41a3a473de72a87ef5fff0c73c510', '7e55c42bfa96389c1f601f93f5f26db0', '', 1066910400, 1066917600, 1066917600, 0, 0, 0, 0, 0, 0, 0, 0, 1067425166, 1067425166);
INSERT INTO resources_assign VALUES ('da0f294b09a372c33bae908cf1ac51fb', '6f3e26a53a4c1f40501217c281d4969a', 'c187467f7d49cb3824428a0ca3fed1e2', '', 1067245200, 1067252400, 1067252400, 0, 0, 0, 0, 0, 0, 0, 0, 1067425166, 1067425166);
INSERT INTO resources_assign VALUES ('61937315605477abbb71b01fabe56eb5', 'd6a41a3a473de72a87ef5fff0c73c510', '75d8545383cfe668ec3d346b05afe2a4', '', 1067518800, 1067526000, 1067526000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425166, 1067425166);
INSERT INTO resources_assign VALUES ('d3c4c1b107a34f48c95792af362cffc8', '6f3e26a53a4c1f40501217c281d4969a', '6426bd7e032e2e21d27eb45f79ea1bc8', '', 1067850000, 1067857200, 1067857200, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('5ff56f691ece2319df8251f07646c544', 'd6a41a3a473de72a87ef5fff0c73c510', '930b0f91e7ba58d6a56d18dee783482d', '', 1068123600, 1068130800, 1068130800, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('c8e1b0b343ff79b6dcc8725be16e76c6', '6f3e26a53a4c1f40501217c281d4969a', 'e880531505932cb1af91b4aedb68560f', '', 1068454800, 1068462000, 1068462000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('55d8680e85008090e265ed10dc4b9223', 'd6a41a3a473de72a87ef5fff0c73c510', 'ef6de39cd7b74f57dc431ca6d43b1364', '', 1068728400, 1068735600, 1068735600, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('f432973a5225a0505a253b76b916d640', '6f3e26a53a4c1f40501217c281d4969a', '0627fae9f6fcee584db9b2478b98d128', '', 1069059600, 1069066800, 1069066800, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('a7cef7ec82b33696a1080fb49b890345', 'd6a41a3a473de72a87ef5fff0c73c510', 'e02bcc95b7b5a89314b15fea1077b415', '', 1069333200, 1069340400, 1069340400, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('a0e26262ca0f4bf9f29266fe2ed2f386', '6f3e26a53a4c1f40501217c281d4969a', '27a53e480be39615139e25cca31ec832', '', 1069664400, 1069671600, 1069671600, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('65679ab96fdd6e14c26d966ac3c262a8', 'd6a41a3a473de72a87ef5fff0c73c510', '57b0cf0e704407b1791592bba21ca22f', '', 1069938000, 1069945200, 1069945200, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('d6b796b405add1656f7e9e709a1a2a25', '6f3e26a53a4c1f40501217c281d4969a', '58c11e07905705a5e4b55483db66f84e', '', 1070269200, 1070276400, 1070276400, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('6bc8063f8c97688b07ba67570d7c6ff6', 'd6a41a3a473de72a87ef5fff0c73c510', '59af1de2f72f0d4f01e174773916af41', '', 1070542800, 1070550000, 1070550000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('69f21120999ef425ad4180c86d785221', '6f3e26a53a4c1f40501217c281d4969a', '3762407314151dd0674e61c0c4234d38', '', 1070874000, 1070881200, 1070881200, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('9615d24d427dcb72fd5c7aa542e80174', 'd6a41a3a473de72a87ef5fff0c73c510', 'a746e21ece7630b2e6e2a7f354e67fd4', '', 1071147600, 1071154800, 1071154800, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('e37fde81ba46de8068f2e7be7e0c0884', '6f3e26a53a4c1f40501217c281d4969a', 'af259534e1d569f6ef563ffd3e3c0bbc', '', 1071478800, 1071486000, 1071486000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('739df99327db7cde8c5f09deab05f64c', 'd6a41a3a473de72a87ef5fff0c73c510', '939a229c1e5462bf69bd67eabf6938c6', '', 1071752400, 1071759600, 1071759600, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('5a3e1ef27dfbba24bdec376e52a4d057', '6f3e26a53a4c1f40501217c281d4969a', '581a6065357f2a2b25e2aed40e1797c9', '', 1073293200, 1073300400, 1073300400, 0, 0, 0, 0, 0, 0, 0, 0, 1067425167, 1067425167);
INSERT INTO resources_assign VALUES ('c392db69567de4310805776f3a7d8939', 'd6a41a3a473de72a87ef5fff0c73c510', '81825595bec9d6097d44223f8770c59b', '', 1073566800, 1073574000, 1073574000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('0c079d8eb12ab5ef8cdd6d6f2bf4704b', '6f3e26a53a4c1f40501217c281d4969a', 'b456e5c0985c24b49f08fac99f768f5e', '', 1073898000, 1073905200, 1073905200, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('a6321eac550afbc81e84584e16b1a902', 'd6a41a3a473de72a87ef5fff0c73c510', '8ecc2035ecf5378773086e126a951832', '', 1074171600, 1074178800, 1074178800, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('6380805b1aeced2e79f88eb126a20eb5', '6f3e26a53a4c1f40501217c281d4969a', '68fd6ec6efba9c46023f1b85dff80974', '', 1074502800, 1074510000, 1074510000, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('9cd401098be91bf638f0e4fe4607c3be', 'd6a41a3a473de72a87ef5fff0c73c510', '01b76210274246a06ff80f2b3874344c', '', 1074776400, 1074783600, 1074783600, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('0c2df2d19ae30732ae420a3b851a7a3d', '6f3e26a53a4c1f40501217c281d4969a', 'd10f4f1ab03a747217365e05f9072027', '', 1075107600, 1075114800, 1075114800, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('1ea354b6841e71d15f28034fa956196a', 'd6a41a3a473de72a87ef5fff0c73c510', 'f9dbc6deddbae235ca713b7346f6355b', '', 1075381200, 1075388400, 1075388400, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('5927da39a093b646a031bf48d37bfd5d', '6f3e26a53a4c1f40501217c281d4969a', '4895b056cb06379c3a2193661a5419b7', '', 1075712400, 1075719600, 1075719600, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);
INSERT INTO resources_assign VALUES ('a174389172f3937c16bfe95af4fe25ab', 'd6a41a3a473de72a87ef5fff0c73c510', '71d22e1dd7e9dbe188e1884aebbad8a6', '', 1075986000, 1075993200, 1075993200, 0, 0, 0, 0, 0, 0, 0, 0, 1067425168, 1067425168);

#
# Daten für Tabelle `resources_objects`
#

INSERT INTO resources_objects VALUES ('30d2887b82d3f184251273a7c5902f98', '30d2887b82d3f184251273a7c5902f98', '0', '', '76ed43ef286fb55cf9e41beadb484a9f', 0, 'Ressourcen der Testinstallation', '', '', 0, 1067425065, 1067425090);
INSERT INTO resources_objects VALUES ('6f3e26a53a4c1f40501217c281d4969a', '30d2887b82d3f184251273a7c5902f98', '30d2887b82d3f184251273a7c5902f98', '1cf2a34de92c06137ecdfcef4a29e4bc', '76ed43ef286fb55cf9e41beadb484a9f', 1, 'Hörsaal 1', '', '', 0, 1067425093, 1067425104);
INSERT INTO resources_objects VALUES ('d6a41a3a473de72a87ef5fff0c73c510', '30d2887b82d3f184251273a7c5902f98', '30d2887b82d3f184251273a7c5902f98', '1cf2a34de92c06137ecdfcef4a29e4bc', '76ed43ef286fb55cf9e41beadb484a9f', 1, 'Hörsaal 2', '', '', 0, 1067425131, 1067425141);

#
# Daten für Tabelle `resources_objects_properties`
#

INSERT INTO resources_objects_properties VALUES ('6f3e26a53a4c1f40501217c281d4969a', 'ef4ba565e635b45c3f43ecdc69fb4aca', '25');
INSERT INTO resources_objects_properties VALUES ('6f3e26a53a4c1f40501217c281d4969a', '0ef8a73d95f335cdfbaec50cae92762a', 'alles was man so braucht');
INSERT INTO resources_objects_properties VALUES ('6f3e26a53a4c1f40501217c281d4969a', '31abad810703df361d793361bf6b16e5', 'Hörsaal');
INSERT INTO resources_objects_properties VALUES ('6f3e26a53a4c1f40501217c281d4969a', '5753ab43945ae787f983f5c8a036712d', 'on');
INSERT INTO resources_objects_properties VALUES ('6f3e26a53a4c1f40501217c281d4969a', '648b8579ffca64a565459fd6ea0313c5', 'on');
INSERT INTO resources_objects_properties VALUES ('d6a41a3a473de72a87ef5fff0c73c510', 'ef4ba565e635b45c3f43ecdc69fb4aca', '500');
INSERT INTO resources_objects_properties VALUES ('d6a41a3a473de72a87ef5fff0c73c510', '0ef8a73d95f335cdfbaec50cae92762a', 'eher mäßig');
INSERT INTO resources_objects_properties VALUES ('d6a41a3a473de72a87ef5fff0c73c510', '31abad810703df361d793361bf6b16e5', 'Hörsaal');
INSERT INTO resources_objects_properties VALUES ('d6a41a3a473de72a87ef5fff0c73c510', '648b8579ffca64a565459fd6ea0313c5', '');