#
# Daten für Tabelle `Institute`
#

INSERT INTO `Institute` VALUES ('1535795b0d6ddecac6813f5f6ac47ef2', 'Test Fakultät', '1535795b0d6ddecac6813f5f6ac47ef2', 'Geismar Landstr. 17b', '37083 Göttingen', 'http://www.studip.de', '0551 / 381 985 0', 'testfakultaet@studip.de', '0551 / 381 985 3', 1, 243, 1084638861, 1084638861, 'Studip');
INSERT INTO `Institute` VALUES ('2560f7c7674942a7dce8eeb238e15d93', 'Test Einrichtung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 1, 243, 1084638891, 1084638891, 'Studip');
INSERT INTO `Institute` VALUES ('536249daa596905f433e1f73578019db', 'Test Lehrstuhl', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 3, 243, 1084638912, 1084638912, 'Studip');
INSERT INTO `Institute` VALUES ('f02e2b17bc0e99fc885da6ac4c2532dc', 'Test Abteilung', '1535795b0d6ddecac6813f5f6ac47ef2', '', '', '', '', '', '', 4, 243, 1084638945, 1084638945, 'Studip');
INSERT INTO `Institute` VALUES ('ec2e364b28357106c0f8c282733dbe56', 'externe Bildungseinrichtungen', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 243, 1084723039, 1084723679, 'Studip');
INSERT INTO `Institute` VALUES ('7a4f19a0a2c321ab2b8f7b798881af7c', 'externe Einrichtung A', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 243, 1084723053, 1084723651, 'Studip');
INSERT INTO `Institute` VALUES ('110ce78ffefaf1e5f167cd7019b728bf', 'externe Einrichtung B', 'ec2e364b28357106c0f8c282733dbe56', '', '', '', '', '', '', 1, 243, 1084723061, 1084723661, 'Studip');


#
# Daten für Tabelle `auth_user_md5`
#

INSERT IGNORE INTO `auth_user_md5` VALUES ('76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', 'ae2b1fca515949e5d54fb22b8ed95575', 'root', 'Root', 'Studip', 'root@localhost', NULL);
INSERT INTO `auth_user_md5` VALUES ('205f3efb7997a0fc9755da2b535038da', 'test_dozent', 'ae2b1fca515949e5d54fb22b8ed95575', 'dozent', 'Testaccount', 'Dozent', 'dozent@studip.de', NULL);
INSERT INTO `auth_user_md5` VALUES ('6235c46eb9e962866ebdceece739ace5', 'test_admin', 'ae2b1fca515949e5d54fb22b8ed95575', 'admin', 'Testaccount', 'Admin', 'admin@studip.de', NULL);
INSERT INTO `auth_user_md5` VALUES ('7e81ec247c151c02ffd479511e24cc03', 'test_tutor', 'ae2b1fca515949e5d54fb22b8ed95575', 'tutor', 'Testaccount', 'Tutor', 'tutor@studip.de', NULL);
INSERT INTO `auth_user_md5` VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', 'test_autor', 'ae2b1fca515949e5d54fb22b8ed95575', 'autor', 'Testaccount', 'Autor', 'autor@studip.de', NULL);

#
# Daten für Tabelle `dokumente`
#

INSERT INTO `dokumente` VALUES ('c51a12e44c667b370fe2c497fbfc3c21', '823b5c771f17d4103b1828251c29a7cb', '76ed43ef286fb55cf9e41beadb484a9f', '834499e2b8a2cd71637890e5de31cba3', 'Stud.IP-Produktbroschüre im PDF-Format', '', 'studip_broschuere.pdf', 1084782809, 1084782809, 295294, '217.94.188.5', 2, 'http://www.studip.de/download/studip_broschuere.pdf', 0);

#
# Daten für Tabelle `folder`
#

INSERT INTO `folder` VALUES ('dad53cd0f0d9f36817c3c9c7c124bda3', 'ec2e364b28357106c0f8c282733dbe56', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1084723039, 1084723039);
INSERT INTO `folder` VALUES ('b58081c411c76814bc8f78425fb2ab81', '7a4f19a0a2c321ab2b8f7b798881af7c', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1084723053, 1084723053);
INSERT INTO `folder` VALUES ('694cdcef09c2b8e70a7313b028e36fb6', '110ce78ffefaf1e5f167cd7019b728bf', '', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Einrichtung', 1084723061, 1084723061);
INSERT INTO `folder` VALUES ('823b5c771f17d4103b1828251c29a7cb', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Allgemeiner Dateiordner', 'Ablage für allgemeine Ordner und Dokumente der Veranstaltung', 1084723361, 1084723361);
INSERT INTO `folder` VALUES ('c10cb219dd0a6c922e6a65ed163a9225', '3785036f5b7bfd53fede7266c70b5108', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 18.10.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723383, 1102345281);
INSERT INTO `folder` VALUES ('268084969e1cbdc3a63dcb41d0964864', 'c439597f2142105681238f31492c8e42', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 20.10.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723383, 1102345281);
INSERT INTO `folder` VALUES ('28488039e8d3b63c1b84308c3681ad06', '3903df558848989b25cf495a45f0b2ec', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 25.10.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345281);
INSERT INTO `folder` VALUES ('afe24e488792a6a33b3c32e888cee1e2', 'f991bb073aa9d5c70e7f53c62b95781a', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 27.10.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345281);
INSERT INTO `folder` VALUES ('08a0b3f60a54a46a1a38365b11214f91', '2c61b4c0fb0b996bd8e0be447567cf83', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 01.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345281);
INSERT INTO `folder` VALUES ('9615a795c9034959f088d7a2c15f34f5', '5686564230a1afccf6eaffbc0d5cc53c', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 03.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345281);
INSERT INTO `folder` VALUES ('4073a56953327869b7a768d218a2b18b', '88201e74cee038a024e1a47f68270a28', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 08.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345281);
INSERT INTO `folder` VALUES ('795e69df0f7ade4b98f6417b773e6ac5', '0c8e5d6b586407668dc79396319e6252', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 10.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345281);
INSERT INTO `folder` VALUES ('8f881a98a34350d089d7c9398502cf3d', '069252169d8134b113f0861f9bcfb220', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 15.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('a79b78a052275077e4b29bae33be17fb', '1566febc6a5f0d20f7f9d8b28100b672', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 17.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('6795c417fe03b221c21760e5db70f508', '56e71e09825424cb2f1a720a0f5fc58e', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 22.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('eaf1ec298e6841ac17de40972240a509', 'a16416f5e57442cf6bf4456c81fd95b2', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 24.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('54e4dc8a9a5a359b39b56d9f5a40a95e', '124d1283cf148c3be8670c1ce50dadeb', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 29.11.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('3833f210646f55dccbf9087c0ba4c892', '7dbc70074dc47d42f2bcb5d336572df7', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 01.12.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('24b6c2e2651b6385e110f9f8a110dde5', '1850529ad8409e6bbe76e9b237e8336e', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 06.12.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('91a0e709d4da233bd366323ae971d6d9', '7be27773cb28cf96d0dac62ef3dcf02f', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 08.12.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('04be2962b46adf72c9b6951271fc51b5', '42c1fac59bcf048f9e0736d41f76cbfd', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 13.12.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('cf72fb1a829e1a9bd7644e040502948b', 'e4a7ae88e5259aecf44abe0f50f33356', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 15.12.2004', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('1effc1746c46c1bc430a2bb0e36aa970', 'a38c1b0876addbe32b32c02000c18760', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 03.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723384, 1102345282);
INSERT INTO `folder` VALUES ('b486ce92e8524f713431d67e3def8f38', '5645b8a1161c24f576add46f9bc3e29b', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 05.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);
INSERT INTO `folder` VALUES ('bb2ffff1d578b1726de0e36c6f3fad06', '4d7e54a00376b9f7f0555411929e1669', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 10.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);
INSERT INTO `folder` VALUES ('671e814a0cbcfd0715ece6b3fa92f10a', '65a19db66d9df28a2852c177b402753b', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 12.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);
INSERT INTO `folder` VALUES ('d1559ca7501c25e6a8623427d1831c50', '968c816f9c045039658c424d5b631b33', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 17.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);
INSERT INTO `folder` VALUES ('9efdc8406781ed889d2f26995bfd4bd6', '001579a6edb2f566da6ff24f58dc1852', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 19.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);
INSERT INTO `folder` VALUES ('2357eb801a0c2dfece8799aa4c9eabcb', '8ce42aef2c480eb37a48dfa05e045a97', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 24.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);
INSERT INTO `folder` VALUES ('a71b0e3d3f63623e52ab022b6d5b3fbd', 'bc98b3dd0ac1be1a9c7ab918cd322806', '76ed43ef286fb55cf9e41beadb484a9f', 'Sitzung: Kein Titel am 26.01.2005', 'Ablage für Ordner und Dokumente zu diesem Termin', 1084723385, 1102345282);

#
# Daten für Tabelle `lit_catalog`
#

INSERT INTO `lit_catalog` VALUES ('54181f281faa777941acc252aebaf26d', 'studip', 1084723443, 1084723443, 'Gvk', '387042768', 'Quickguide Strahlenschutz : [Aufgaben, Organisation, Schutzmaßnahmen].', 'Wolf, Heike', '', '', 'Kissing : WEKA Media', '', '2004-01-01', '', '74 S : Ill.', '', '', 'ger', '[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxislösungen', '', '');
INSERT INTO `lit_catalog` VALUES ('d6623a3c2b8285fb472aa759150148ad', 'studip', 1084723444, 1084723444, 'Gvk', '387042253', 'Röntgenverordnung : (RÖV) ; Verordnung über den Schutz vor Schäden durch Röntgenstrahlen.', 'Wolf, Heike', '', '', 'Kissing : WEKA Media', '', '2004-01-01', '', '50 S.', '', '', 'ger', '[Der Strahlenschutzbeauftragte in Medizin und Technik / Heike Wolf] Praxislösungen', '', '');
INSERT INTO `lit_catalog` VALUES ('15074ad4f2bd2c57cbc9dfb343c1355b', 'studip', 1084723457, 1084723457, 'Gvk', '384065813', 'Der Kater mit Hut', 'Geisel, Theodor Seuss', '', '', 'München [u.a.] : Piper', '', '2004-01-01', '', '75 S : zahlr. Ill ; 19 cm.', 'ISBN: 349224078X (kart.)', '', 'ger', 'Serie Piper ;, 4078', '', '');
INSERT INTO `lit_catalog` VALUES ('ce704bbc9453994daa05d76d2d04aba0', 'studip', 1084723459, 1084723459, 'Gvk', '379252104', 'Die volkswirtschaftliche Perspektive', 'Heise, Michael', '', '', 'In: Zeitschrift für das gesamte Kreditwesen, Vol. 57, No. 4 (2004), p. 211-217, Frankfurt, M. : Knapp', 'Kater, Ulrich;', '2004-01-01', '', 'graph. Darst.', '', '', 'ger', '', '', '');
INSERT INTO `lit_catalog` VALUES ('b5d115a7f7cad02b4535fb3090bf18da', 'studip', 1084723476, 1084723476, 'Gvk', '386883831', 'E-Learning: Qualität und Nutzerakzeptanz sichern : Beiträge zur Planung, Umsetzung und Evaluation multimedialer und netzgestützter Anwendungen', 'Zinke, Gert', '', '', 'Bielefeld : Bertelsmann', 'Härtel, Michael; Bundesinstitut für Berufsbildung, ;', '2004-01-01', '', '159 S : graph. Darst ; 225 mm x 155 mm.', 'ISBN: 3763910204', '', 'ger', 'Berichte zur beruflichen Bildung ;, 265', '', '');

#
# Daten für Tabelle `lit_list`
#

INSERT INTO `lit_list` VALUES ('3332f270b96fb23cdd2463cef8220b29', '834499e2b8a2cd71637890e5de31cba3', 'Basisliteratur der Veranstaltung', '**{dc_creator}** |({dc_contributor})||\r\n{dc_title}||\r\n{dc_identifier}||\r\n%%{published}%%||\r\n{note}||\r\n[{lit_plugin}]{external_link}|\r\n', '76ed43ef286fb55cf9e41beadb484a9f', 1084723414, 1084723488, 1, 1);

#
# Daten für Tabelle `lit_list_content`
#

INSERT INTO `lit_list_content` VALUES ('1e6d6e6f179986f8c2be5b1c2ed37631', '3332f270b96fb23cdd2463cef8220b29', '15074ad4f2bd2c57cbc9dfb343c1355b', '76ed43ef286fb55cf9e41beadb484a9f', 1084723488, 1084723488, '', 1);
INSERT INTO `lit_list_content` VALUES ('4bd3001d8260001914e9ab8716a4fe70', '3332f270b96fb23cdd2463cef8220b29', 'ce704bbc9453994daa05d76d2d04aba0', '76ed43ef286fb55cf9e41beadb484a9f', 1084723488, 1084723488, '', 2);
INSERT INTO `lit_list_content` VALUES ('ce226125c3cf579cf28e5c96a8dea7a9', '3332f270b96fb23cdd2463cef8220b29', '54181f281faa777941acc252aebaf26d', '76ed43ef286fb55cf9e41beadb484a9f', 1084723488, 1084723488, '', 3);
INSERT INTO `lit_list_content` VALUES ('1d4ff2d55489dd9284f6a83dfc69149e', '3332f270b96fb23cdd2463cef8220b29', 'd6623a3c2b8285fb472aa759150148ad', '76ed43ef286fb55cf9e41beadb484a9f', 1084723488, 1084723488, '', 4);
INSERT INTO `lit_list_content` VALUES ('293e90c3c6511d2c8e1d4ba7b51daa98', '3332f270b96fb23cdd2463cef8220b29', 'b5d115a7f7cad02b4535fb3090bf18da', '76ed43ef286fb55cf9e41beadb484a9f', 1084723488, 1084723488, '', 5);

#
# Daten für Tabelle `news`
#

INSERT IGNORE INTO news VALUES ('29f2932ce32be989022c6f43b866e744', 'Herzlich Willkommen!', 'Das Stud.IP-Team heisst sie herzlich willkommen. \r\nBitte schauen Sie sich ruhig um!\r\n\r\nWenn Sie das System selbst installiert haben und diese News sehen, haben Sie die Demonstrationsdaten in die Datenbank eingefügt. Wenn Sie produktiv mit dem System arbeiten wollen, sollten Sie diese Daten später wieder löschen, da die Passwörter der Accounts (vor allem des root-Accounts) öffentlich bekannt sind.', 'Root Studip', UNIX_TIMESTAMP(NOW()), '76ed43ef286fb55cf9e41beadb484a9f', 7343999);

#
# Daten für Tabelle `news_range`
#

INSERT IGNORE INTO news_range VALUES ('29f2932ce32be989022c6f43b866e744', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT IGNORE INTO news_range VALUES ('29f2932ce32be989022c6f43b866e744', 'studip');

#
# Daten für Tabelle `px_topics`
#

INSERT INTO `px_topics` VALUES ('5260172c3d6f9d56d21b06bf4c278b52', '0', '5260172c3d6f9d56d21b06bf4c278b52', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1084723039, 1084723039, '', '134.76.62.67', 'ec2e364b28357106c0f8c282733dbe56', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('b30ec732ee1c69a275b2d6adaae49cdc', '0', 'b30ec732ee1c69a275b2d6adaae49cdc', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1084723053, 1084723053, '', '134.76.62.67', '7a4f19a0a2c321ab2b8f7b798881af7c', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('9f394dffd08043f13cc65ffff65bfa05', '0', '9f394dffd08043f13cc65ffff65bfa05', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1084723061, 1084723061, '', '134.76.62.67', '110ce78ffefaf1e5f167cd7019b728bf', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('49271c51527a89c794332f737eda652c', '0', '49271c51527a89c794332f737eda652c', 'Allgemeine Diskussionen', 'Hier ist Raum für allgemeine Diskussionen', 1084723361, 1084723361, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('74f969ac0234f8a43cf8a643912e082d', '0', '74f969ac0234f8a43cf8a643912e082d', 'Sitzung: Kein Titel am 18.10.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723383, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('0710db8fe7a5e79d3689d51ca5bb99cd', '0', '0710db8fe7a5e79d3689d51ca5bb99cd', 'Sitzung: Kein Titel am 20.10.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723383, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('2acac279068bd557a0acfc39ac40b245', '0', '2acac279068bd557a0acfc39ac40b245', 'Sitzung: Kein Titel am 25.10.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('2a0eb4e82b88c46b4bd23739596a43b8', '0', '2a0eb4e82b88c46b4bd23739596a43b8', 'Sitzung: Kein Titel am 27.10.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('04568021ca8d57264482bc597a489e47', '0', '04568021ca8d57264482bc597a489e47', 'Sitzung: Kein Titel am 01.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('779d1073be9ced6ef99831697bd659bc', '0', '779d1073be9ced6ef99831697bd659bc', 'Sitzung: Kein Titel am 03.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('9f0148635181691624de8d23b1a82e77', '0', '9f0148635181691624de8d23b1a82e77', 'Sitzung: Kein Titel am 08.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('a9bb4b8c998f8d79bcf40a58f27c7aba', '0', 'a9bb4b8c998f8d79bcf40a58f27c7aba', 'Sitzung: Kein Titel am 10.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345281, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('be2897fece10d886290d922bbf3c846f', '0', 'be2897fece10d886290d922bbf3c846f', 'Sitzung: Kein Titel am 15.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('76a680eda707cb07ca9336f432144830', '0', '76a680eda707cb07ca9336f432144830', 'Sitzung: Kein Titel am 17.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('37bee45c87e208f68fa54daec5831606', '0', '37bee45c87e208f68fa54daec5831606', 'Sitzung: Kein Titel am 22.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('e35ca61dce76a555bf9790afd2684346', '0', 'e35ca61dce76a555bf9790afd2684346', 'Sitzung: Kein Titel am 24.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('893480ddf4197dc4608e0e2ee10d9920', '0', '893480ddf4197dc4608e0e2ee10d9920', 'Sitzung: Kein Titel am 29.11.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('a9cfaefe0a0a51981ba523ff9c6bf078', '0', 'a9cfaefe0a0a51981ba523ff9c6bf078', 'Sitzung: Kein Titel am 01.12.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('e2be37daf11c62b5a6f6d20042b1afd9', '0', 'e2be37daf11c62b5a6f6d20042b1afd9', 'Sitzung: Kein Titel am 06.12.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('c9eac9721b8788f5edfe4ca35d974fe9', '0', 'c9eac9721b8788f5edfe4ca35d974fe9', 'Sitzung: Kein Titel am 08.12.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('6b5142f6e3b424bcd6fba078777a6787', '0', '6b5142f6e3b424bcd6fba078777a6787', 'Sitzung: Kein Titel am 13.12.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('ed2716f3a3fdd6d41c01fd1e18fb76ee', '0', 'ed2716f3a3fdd6d41c01fd1e18fb76ee', 'Sitzung: Kein Titel am 15.12.2004', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('059bebbddc40bd796fc61495a0c67a8e', '0', '059bebbddc40bd796fc61495a0c67a8e', 'Sitzung: Kein Titel am 03.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723384, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('8aa91b80b762c1fa246b8d08282f637b', '0', '8aa91b80b762c1fa246b8d08282f637b', 'Sitzung: Kein Titel am 05.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('91102549c75a543c56761ab762741914', '0', '91102549c75a543c56761ab762741914', 'Sitzung: Kein Titel am 10.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('b05a5be76c8e1c53f61df2c3a9c5ce6a', '0', 'b05a5be76c8e1c53f61df2c3a9c5ce6a', 'Sitzung: Kein Titel am 12.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('39963bfd55c4cdcfd27680c1e1f8df86', '0', '39963bfd55c4cdcfd27680c1e1f8df86', 'Sitzung: Kein Titel am 17.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('167f5d09921de150a242d2e9d16e482b', '0', '167f5d09921de150a242d2e9d16e482b', 'Sitzung: Kein Titel am 19.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('b403b80337b85f5df3f7048b07e397c4', '0', 'b403b80337b85f5df3f7048b07e397c4', 'Sitzung: Kein Titel am 24.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');
INSERT INTO `px_topics` VALUES ('e065dd2e11879a26703081587f6f7367', '0', 'e065dd2e11879a26703081587f6f7367', 'Sitzung: Kein Titel am 26.01.2005', 'Hier kann zu diesem Termin diskutiert werden', 1084723385, 1102345282, 'Root Studip', '134.76.62.67', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f');

#
# Daten für Tabelle `range_tree`
#

INSERT INTO `range_tree` VALUES ('3f93863e3d37ba0df286a6e7e26974ef', 'root', 0, 0, 'Einrichtungen der Universität', '', '');
INSERT INTO `range_tree` VALUES ('1323254564871354786157481484621', '3f93863e3d37ba0df286a6e7e26974ef', 1, 0, '', 'inst', '1535795b0d6ddecac6813f5f6ac47ef2');
INSERT INTO `range_tree` VALUES ('ce6c87bbf759b4cfd6f92d0c5560da5c', '1323254564871354786157481484621', 0, 0, 'Test Einrichtung', 'inst', '2560f7c7674942a7dce8eeb238e15d93');
INSERT INTO `range_tree` VALUES ('2f4f90ac9d8d832cc8c8a95910fde4eb', '1323254564871354786157481484621', 0, 1, 'Test Lehrstuhl', 'inst', '536249daa596905f433e1f73578019db');
INSERT INTO `range_tree` VALUES ('5d032f70c255f3e57cf8aa85a429ad4e', '1323254564871354786157481484621', 0, 2, 'Test Abteilung', 'inst', 'f02e2b17bc0e99fc885da6ac4c2532dc');
INSERT INTO `range_tree` VALUES ('a3d977a66f0010fa8e15c27dd71aff63', 'root', 0, 1, 'externe Bildungseinrichtungen', 'fak', 'ec2e364b28357106c0f8c282733dbe56');
INSERT INTO `range_tree` VALUES ('e0ff0ead6a8c5191078ed787cd7c0c1f', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 0, 'externe Einrichtung A', 'inst', '7a4f19a0a2c321ab2b8f7b798881af7c');
INSERT INTO `range_tree` VALUES ('105b70b72dc1908ce2925e057c4a8daa', 'a3d977a66f0010fa8e15c27dd71aff63', 0, 1, 'externe Einrichtung B', 'inst', '110ce78ffefaf1e5f167cd7019b728bf');

#
# Daten für Tabelle `sem_tree`
#

INSERT INTO `sem_tree` VALUES ('5b73e28644a3e259a6e0bc1e1499773c', 'root', 1, '', '', '1535795b0d6ddecac6813f5f6ac47ef2');
INSERT INTO `sem_tree` VALUES ('439618ae57d8c10dcaabcf7e21bcc1d9', '5b73e28644a3e259a6e0bc1e1499773c', 0, '', 'Test Studienbereich A', NULL);
INSERT INTO `sem_tree` VALUES ('5c41d2b4a5a8338e069dda987a624b74', '5b73e28644a3e259a6e0bc1e1499773c', 1, '', 'Test Studienbereich B', NULL);
INSERT INTO `sem_tree` VALUES ('3d39528c1d560441fd4a8cb0b7717285', '439618ae57d8c10dcaabcf7e21bcc1d9', 0, '', 'Test Studienbereich A-1', NULL);
INSERT INTO `sem_tree` VALUES ('dd7fff9151e85e7130cdb684edf0c370', '439618ae57d8c10dcaabcf7e21bcc1d9', 1, '', 'Test Studienbereich A-2', NULL);
INSERT INTO `sem_tree` VALUES ('01c8b1d188be40c5ac64b54a01aae294', '5b73e28644a3e259a6e0bc1e1499773c', 2, '', 'Test Studienbereich C', NULL);

#
# Daten für Tabelle `seminar_inst`
#

INSERT INTO `seminar_inst` VALUES ('834499e2b8a2cd71637890e5de31cba3', '2560f7c7674942a7dce8eeb238e15d93');

#
# Daten für Tabelle `seminar_sem_tree`
#

INSERT INTO `seminar_sem_tree` VALUES ('834499e2b8a2cd71637890e5de31cba3', '3d39528c1d560441fd4a8cb0b7717285');
INSERT INTO `seminar_sem_tree` VALUES ('834499e2b8a2cd71637890e5de31cba3', '5c41d2b4a5a8338e069dda987a624b74');
INSERT INTO `seminar_sem_tree` VALUES ('834499e2b8a2cd71637890e5de31cba3', 'dd7fff9151e85e7130cdb684edf0c370');

#
# Daten für Tabelle `seminar_user`
#

INSERT INTO `seminar_user` VALUES ('834499e2b8a2cd71637890e5de31cba3', '205f3efb7997a0fc9755da2b535038da', 'dozent', 2, '', 1084723360, NULL);
INSERT INTO `seminar_user` VALUES ('834499e2b8a2cd71637890e5de31cba3', '7e81ec247c151c02ffd479511e24cc03', 'tutor', 2, '', 1084723360, NULL);
INSERT INTO `seminar_user` VALUES ('834499e2b8a2cd71637890e5de31cba3', 'e7a0a84b161f3e8c09b4a0a2e8a58147', 'autor', 2, '', 1084723360, NULL);

#
# Daten für Tabelle `seminare`
#

INSERT INTO `seminare` VALUES ('834499e2b8a2cd71637890e5de31cba3', '1234', '2560f7c7674942a7dce8eeb238e15d93', 'Test Lehrveranstaltung', 'eine normale Lehrveranstaltung', 1, '', '', '', '', 1, 1, 1096581600, 0, '', 'für alle Studierenden', 'abgeschlossenes Grundstudium', 'Referate in Gruppenarbeit', 'Klausur', 'a:5:{s:3:"art";s:1:"0";s:12:"start_termin";i:-1;s:11:"start_woche";s:1:"0";s:6:"turnus";s:1:"0";s:11:"turnus_data";a:2:{i:0;a:8:{s:3:"idx";s:5:"11000";s:3:"day";s:1:"1";s:12:"start_stunde";i:10;s:12:"start_minute";i:0;s:10:"end_stunde";i:12;s:10:"end_minute";i:0;s:4:"room";s:9:"Hörsaal 1";s:11:"resource_id";s:32:"728f1578de643fb08b32b4b8afb2db77";}i:1;a:8:{s:3:"idx";s:5:"31100";s:3:"day";s:1:"3";s:12:"start_stunde";i:11;s:12:"start_minute";i:0;s:10:"end_stunde";i:12;s:10:"end_minute";i:0;s:4:"room";s:9:"Hörsaal 2";s:11:"resource_id";s:32:"b17c4ea6e053f2fffba8a5517fc277b3";}}}', 1084723360, 1102345281, '4', -1, 0, NULL, 0, 0, NULL, 0, '', -1, -1, 1, 0, 495);

#
# Daten für Tabelle `statusgruppe_user`
#

INSERT INTO `statusgruppe_user` VALUES ('efb56e092f33cb78a8766676042dc1c5', '7e81ec247c151c02ffd479511e24cc03', 1);
INSERT INTO `statusgruppe_user` VALUES ('5d40b1fc0434e6589d7341a3ee742baf', '205f3efb7997a0fc9755da2b535038da', 1);

#
# Daten für Tabelle `statusgruppen`
#

INSERT INTO `statusgruppen` VALUES ('86498c641ccf4f4d4e02f4961ccc3829', 'Lehrbeauftragte', '2560f7c7674942a7dce8eeb238e15d93', 3, 0, 0, 1084722322, 1084722322);
INSERT INTO `statusgruppen` VALUES ('600403561c21a50ae8b4d41655bd2191', 'HochschullehrerIn', '2560f7c7674942a7dce8eeb238e15d93', 4, 0, 0, 1084722327, 1084722327);
INSERT INTO `statusgruppen` VALUES ('efb56e092f33cb78a8766676042dc1c5', 'wiss. MitarbeiterIn', '2560f7c7674942a7dce8eeb238e15d93', 2, 0, 0, 1084722305, 1084722305);
INSERT INTO `statusgruppen` VALUES ('5d40b1fc0434e6589d7341a3ee742baf', 'DirektorIn', '2560f7c7674942a7dce8eeb238e15d93', 1, 0, 0, 1084639925, 1084639925);

#
# Daten für Tabelle `studiengaenge`
#

INSERT INTO studiengaenge VALUES ('63b13b29db6adcf0e2814a6388d4583c', 'Test Studiengang 1', '', 1067423985, 1067423985);
INSERT INTO studiengaenge VALUES ('4a55e9df07a18e76ebb84e27ae212b30', 'Test Studiengang 2', '', 1067423997, 1067423997);

#
# Daten für Tabelle `termine`
#

INSERT INTO `termine` VALUES ('08baa8b86742b780ef35aa896b74aa88', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Vorbesprechung', '', 1096984800, 1096992000, 1084723361, 1102345347, 2, '0', NULL, NULL, NULL, NULL, '');
INSERT INTO `termine` VALUES ('e4a7ae88e5259aecf44abe0f50f33356', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1103104800, 1103108400, 1084723384, 1102345282, 1, 'ed2716f3a3fdd6d41c01fd1e18fb76ee', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('42c1fac59bcf048f9e0736d41f76cbfd', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1102928400, 1102935600, 1084723384, 1102345282, 1, '6b5142f6e3b424bcd6fba078777a6787', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('7be27773cb28cf96d0dac62ef3dcf02f', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1102500000, 1102503600, 1084723384, 1102345282, 1, 'c9eac9721b8788f5edfe4ca35d974fe9', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('1850529ad8409e6bbe76e9b237e8336e', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1102323600, 1102330800, 1084723384, 1102345282, 1, 'e2be37daf11c62b5a6f6d20042b1afd9', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('7dbc70074dc47d42f2bcb5d336572df7', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1101895200, 1101898800, 1084723384, 1102345282, 1, 'a9cfaefe0a0a51981ba523ff9c6bf078', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('124d1283cf148c3be8670c1ce50dadeb', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1101718800, 1101726000, 1084723384, 1102345282, 1, '893480ddf4197dc4608e0e2ee10d9920', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('a16416f5e57442cf6bf4456c81fd95b2', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1101290400, 1101294000, 1084723384, 1102345282, 1, 'e35ca61dce76a555bf9790afd2684346', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('56e71e09825424cb2f1a720a0f5fc58e', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1101114000, 1101121200, 1084723384, 1102345282, 1, '37bee45c87e208f68fa54daec5831606', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('1566febc6a5f0d20f7f9d8b28100b672', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1100685600, 1100689200, 1084723384, 1102345282, 1, '76a680eda707cb07ca9336f432144830', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('069252169d8134b113f0861f9bcfb220', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1100509200, 1100516400, 1084723384, 1102345282, 1, 'be2897fece10d886290d922bbf3c846f', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('0c8e5d6b586407668dc79396319e6252', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1100080800, 1100084400, 1084723384, 1102345281, 1, 'a9bb4b8c998f8d79bcf40a58f27c7aba', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('88201e74cee038a024e1a47f68270a28', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1099904400, 1099911600, 1084723384, 1102345281, 1, '9f0148635181691624de8d23b1a82e77', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('5686564230a1afccf6eaffbc0d5cc53c', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1099476000, 1099479600, 1084723384, 1102345281, 1, '779d1073be9ced6ef99831697bd659bc', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('2c61b4c0fb0b996bd8e0be447567cf83', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1099299600, 1099306800, 1084723384, 1102345281, 1, '04568021ca8d57264482bc597a489e47', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('f991bb073aa9d5c70e7f53c62b95781a', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1098867600, 1098871200, 1084723384, 1102345281, 1, '2a0eb4e82b88c46b4bd23739596a43b8', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('3903df558848989b25cf495a45f0b2ec', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1098691200, 1098698400, 1084723384, 1102345281, 1, '2acac279068bd557a0acfc39ac40b245', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('c439597f2142105681238f31492c8e42', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1098262800, 1098266400, 1084723383, 1102345281, 1, '0710db8fe7a5e79d3689d51ca5bb99cd', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('3785036f5b7bfd53fede7266c70b5108', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1098086400, 1098093600, 1084723383, 1102345281, 1, '74f969ac0234f8a43cf8a643912e082d', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('a38c1b0876addbe32b32c02000c18760', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1104742800, 1104750000, 1084723384, 1102345282, 1, '059bebbddc40bd796fc61495a0c67a8e', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('5645b8a1161c24f576add46f9bc3e29b', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1104919200, 1104922800, 1084723385, 1102345282, 1, '8aa91b80b762c1fa246b8d08282f637b', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('4d7e54a00376b9f7f0555411929e1669', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1105347600, 1105354800, 1084723385, 1102345282, 1, '91102549c75a543c56761ab762741914', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('65a19db66d9df28a2852c177b402753b', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1105524000, 1105527600, 1084723385, 1102345282, 1, 'b05a5be76c8e1c53f61df2c3a9c5ce6a', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('968c816f9c045039658c424d5b631b33', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1105952400, 1105959600, 1084723385, 1102345282, 1, '39963bfd55c4cdcfd27680c1e1f8df86', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('001579a6edb2f566da6ff24f58dc1852', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1106128800, 1106132400, 1084723385, 1102345282, 1, '167f5d09921de150a242d2e9d16e482b', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('8ce42aef2c480eb37a48dfa05e045a97', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1106557200, 1106564400, 1084723385, 1102345282, 1, 'b403b80337b85f5df3f7048b07e397c4', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('bc98b3dd0ac1be1a9c7ab918cd322806', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1106733600, 1106737200, 1084723385, 1102345282, 1, 'e065dd2e11879a26703081587f6f7367', NULL, NULL, NULL, NULL, 'Hörsaal 2');
INSERT INTO `termine` VALUES ('b46d522fc65824ccab8e09f27705b414', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1107162000, 1107169200, 1102345282, 1102345282, 1, '', NULL, NULL, NULL, NULL, 'Hörsaal 1');
INSERT INTO `termine` VALUES ('f09ba58bf95bd6e20077091b705e315d', '834499e2b8a2cd71637890e5de31cba3', '76ed43ef286fb55cf9e41beadb484a9f', 'Kein Titel', NULL, 1107338400, 1107342000, 1102345282, 1102345282, 1, '', NULL, NULL, NULL, NULL, 'Hörsaal 2');


#
# Daten für Tabelle `user_info`
#

INSERT IGNORE INTO `user_info` VALUES ('76ed43ef286fb55cf9e41beadb484a9f', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT INTO `user_info` VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT INTO `user_info` VALUES ('205f3efb7997a0fc9755da2b535038da', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT INTO `user_info` VALUES ('6235c46eb9e962866ebdceece739ace5', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);
INSERT INTO `user_info` VALUES ('7e81ec247c151c02ffd479511e24cc03', '', NULL, '', '', '', '', '', 0, 0, 0, 0, '', '', NULL, 1, '', 0, 0, '', 0);

#
# Daten für Tabelle `user_inst`
#

INSERT INTO `user_inst` VALUES ('205f3efb7997a0fc9755da2b535038da', '2560f7c7674942a7dce8eeb238e15d93', 'dozent', '', '', '', '', '0', '0', '1');
INSERT INTO `user_inst` VALUES ('7e81ec247c151c02ffd479511e24cc03', '2560f7c7674942a7dce8eeb238e15d93', 'tutor', '', '', '', '', '0', '0', '1');
INSERT INTO `user_inst` VALUES ('6235c46eb9e962866ebdceece739ace5', '2560f7c7674942a7dce8eeb238e15d93', 'admin', '', '', '', '', '0', '0', '1');
INSERT INTO `user_inst` VALUES ('e7a0a84b161f3e8c09b4a0a2e8a58147', '2560f7c7674942a7dce8eeb238e15d93', 'user', '', '', '', '', '0', '0', '1');

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
INSERT INTO voteanswers VALUES ('8342e4b4600a12b2d5d43aecf2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.0', 5, 0, 0);
INSERT INTO voteanswers VALUES ('8112e4b4600a12b2d5d43aecf2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.1.0', 5, 0, 0);
INSERT INTO voteanswers VALUES ('8502e4b4600a12b2d5d43aefe2930be4', 'b5329b23b7f865c62028e226715e1914', 'Ich nutze die Version 1.1.5', 5, 0, 0);
INSERT INTO voteanswers VALUES ('dddf684fbcac58f7ffd0804b7095c71b', 'b5329b23b7f865c62028e226715e1914', 'Ich plane, es demnächst einzusetzen', 6, 0, 0);
INSERT INTO voteanswers VALUES ('03bce9c940fc76f5eb90ab7b151cf34d', 'b5329b23b7f865c62028e226715e1914', 'Ich schaue mich nur mal um', 7, 0, 0);
INSERT INTO voteanswers VALUES ('5fb01b6623c848c3bf33cce70675b91a', 'b5329b23b7f865c62028e226715e1914', 'Ich bin nicht interessiert', 8, 0, 0);
