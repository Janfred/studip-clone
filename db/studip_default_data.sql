# 
# create default config entries for resources-management
#

INSERT INTO `config` VALUES ('3d415eca600096df09e59407e4a7994d', 'RESOURCES_LOCKING_ACTIVE', '', '', 1074780851, '');
INSERT INTO `config` VALUES ('b7a2817d142ddd185df2f5ac587fe218', 'RESOURCES_ALLOW_ROOM_REQUESTS', '', '', 1074780851, '');
INSERT INTO `config` VALUES ('d821ffbff29ce636cef63ffe3fd8b427', 'RESOURCES_ALLOW_CREATE_ROOMS', '1', '', 1074780851, '');
INSERT INTO `config` VALUES ('e48dacf9158cd0b936144f0f4cf8dfa3', 'RESOURCES_INHERITANCE_PERMS_ROOMS', '1', '1', 1074780851, '');
INSERT INTO `config` VALUES ('45856b1e3407ceb37d87ec9b8fd32d7d', 'RESOURCES_INHERITANCE_PERMS', '1', '1', 1074780851, '');
INSERT INTO `config` VALUES ('c353c73d8f37e3c301ae34e99c837af4', 'RESOURCES_ENABLE_ORGA_CLASSIFY', '', '', 1074780851, '');
INSERT INTO `config` VALUES ('4ff6d5e7ef7ee66acefa5fcf8e7f2305', 'RESOURCES_ALLOW_SINGLE_ASSIGN_PERCENTAGE', '50', '', 1074780851, '');
INSERT INTO `config` VALUES ('5b4b20d9d2c1556ff1b42503d38a8bc6', 'RESOURCES_ALLOW_SINGLE_DATE_GROUPING', '5', '', 1074780851, '');


#
# defaults for evaluation
#

# Wertung 1-5

INSERT INTO `evalquestion` VALUES ('ef227e91618878835d52cfad3e6d816b', '0', 'polskala', 0, 'Wertung 1-5', 0);

INSERT INTO `evalanswer` VALUES ('d67301d4f59aa35d1e3f12a9791b6885', 'ef227e91618878835d52cfad3e6d816b', 0, 'Sehr gut', 1, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('7052b76e616656e4b70f1c504c04ec81', 'ef227e91618878835d52cfad3e6d816b', 1, '', 2, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('64152ace8f2a74d0efb67c54eff64a2b', 'ef227e91618878835d52cfad3e6d816b', 2, '', 3, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('3a3ab5307f39ea039d41fb6f2683475e', 'ef227e91618878835d52cfad3e6d816b', 3, '', 4, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('6115b19f694ccd3d010a0047ff8f970a', 'ef227e91618878835d52cfad3e6d816b', 4, 'Sehr Schlecht', 5, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('be4c3e5fe0b2b735bb3b2712afa8c490', 'ef227e91618878835d52cfad3e6d816b', 5, 'Keine Meinung', 6, 0, 0, 1);

# Schulnoten

INSERT INTO `evalquestion` VALUES ('724244416b5d04a4d8f4eab8a86fdbf8', '0', 'likertskala', 0, 'Schulnoten', 0);

INSERT INTO `evalanswer` VALUES ('84be4c31449a9c1807bf2dea0dc869f1', '724244416b5d04a4d8f4eab8a86fdbf8', 0, 'Sehr gut', 1, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('c446970d2addd68e43c2a6cae6117bf7', '724244416b5d04a4d8f4eab8a86fdbf8', 1, 'Gut', 2, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('3d4dcedb714dfdcfbe65cd794b4d404b', '724244416b5d04a4d8f4eab8a86fdbf8', 2, 'Befriedigend', 3, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('fa2bf667ba73ae74794df35171c2ad2e', '724244416b5d04a4d8f4eab8a86fdbf8', 3, 'Ausreichend', 4, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('0be387b9379a05c5578afce64b0c688f', '724244416b5d04a4d8f4eab8a86fdbf8', 4, 'Mangelhaft', 5, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('aec07dd525f2610bdd10bf778aa1893b', '724244416b5d04a4d8f4eab8a86fdbf8', 5, 'Nicht erteilt', 6, 0, 0, 1);

# Wertung (trifft zu, ...)

INSERT INTO `evalquestion` VALUES ('95bbae27965d3404f7fa3af058850bd3', '0', 'likertskala', 0, 'Wertung (trifft zu, ...)', 0);

INSERT INTO `evalanswer` VALUES ('7080335582e2787a54f315ec8cef631e', '95bbae27965d3404f7fa3af058850bd3', 0, 'trifft völlig zu', 1, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('d68a74dc2c1f0ce226366da918dd161d', '95bbae27965d3404f7fa3af058850bd3', 1, 'trifft ziemlich zu', 2, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('641686e7c61899b303cda106f20064e7', '95bbae27965d3404f7fa3af058850bd3', 2, 'teilsteils', 3, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('7c36d074f2cc38765c982c9dfb769afc', '95bbae27965d3404f7fa3af058850bd3', 3, 'trifft wenig zu', 4, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('5c4827f903168ed4483db5386a9ad5b8', '95bbae27965d3404f7fa3af058850bd3', 4, 'trifft gar nicht zu', 5, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('c10a3f4e97f8badc5230a9900afde0c7', '95bbae27965d3404f7fa3af058850bd3', 5, 'kann ich nicht beurteilen', 6, 0, 0, 1);

# Werktage

INSERT INTO `evalquestion` VALUES ('6fddac14c1f2ac490b93681b3da5fc66', '0', 'multiplechoice', 0, 'Werktage', 0);

INSERT INTO `evalanswer` VALUES ('ced33706ca95aff2163c7d0381ef5717', '6fddac14c1f2ac490b93681b3da5fc66', 0, 'Montag', 1, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('087c734855c8a5b34d99c16ad09cd312', '6fddac14c1f2ac490b93681b3da5fc66', 1, 'Dienstag', 2, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('63f5011614f45329cc396b90d94a7096', '6fddac14c1f2ac490b93681b3da5fc66', 2, 'Mittwoch', 3, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('ccd1eaddccca993f6789659b36f40506', '6fddac14c1f2ac490b93681b3da5fc66', 3, 'Donnerstag', 4, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('48842cedeac739468741940982b5fe6d', '6fddac14c1f2ac490b93681b3da5fc66', 4, 'Freitag', 5, 0, 0, 0);

# Werktage-mehrfach

INSERT INTO `evalquestion` VALUES ('12e508079c4770fb13c9fce028f40cac', '0', 'multiplechoice', 0, 'Werktage-mehrfach', 1);

INSERT INTO `evalanswer` VALUES ('21b3f7cf2de5cbb098d800f344d399ee', '12e508079c4770fb13c9fce028f40cac', 0, 'Montag', 1, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('f0016e918b5bc5c4cf3cc62bf06fa2e9', '12e508079c4770fb13c9fce028f40cac', 1, 'Dienstag', 2, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('c88242b50ff0bb43df32c1e15bdaca22', '12e508079c4770fb13c9fce028f40cac', 2, 'Mittwoch', 3, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('b39860f6601899dcf87ba71944c57bc7', '12e508079c4770fb13c9fce028f40cac', 3, 'Donnerstag', 4, 0, 0, 0);
INSERT INTO `evalanswer` VALUES ('568d6fd620642cb7395c27d145a76734', '12e508079c4770fb13c9fce028f40cac', 4, 'Freitag', 5, 0, 0, 0);

# Freitext-Mehrzeilig

INSERT INTO `evalquestion` VALUES ('a68bd711902f23bd5c55a29f1ecaa095', '0', 'multiplechoice', 0, 'Freitext-Mehrzeilig', 0);

INSERT INTO `evalanswer` VALUES ('39b98a5560d5dabaf67227e2895db8da', 'a68bd711902f23bd5c55a29f1ecaa095', 0, '', 1, 5, 0, 0);

# Freitext-Einzeilig

INSERT INTO `evalquestion` VALUES ('442e1e464e12498bd238a7767215a5a2', '0', 'multiplechoice', 0, 'Freitext-Einzeilig', 0);

INSERT INTO `evalanswer` VALUES ('61ae27ab33c402316a3f1eb74e1c46ab', '442e1e464e12498bd238a7767215a5a2', 0, '', 1, 1, 0, 0);

#
# semester
#

INSERT INTO `semester_data` VALUES ('e106b4da1d587afabad769a46d013152', 'WS 2003/04', '', '', 1064959200, 1080770399, 1066600800, 1076281199);
INSERT INTO `semester_data` VALUES ('e7e8b2ef44499876f765250eea79ed0b', 'SS 2004', '', '', 1080770400, 1096581599, 1081807200, 1090015199);
INSERT INTO `semester_data` VALUES ('ec666c7cb2459aef4a3a51a997ca31c9', 'WS 2004/05', '', '', 1096581600, 1112306399, 1098050400, 1107557999);
INSERT INTO `semester_data` VALUES ('cc448ebc6edf11b180d288629f5acdc3', 'SS 2005', '', '', 1112306400, 1128117599, 1113170400, 1121464799);

#
# holidays
#

INSERT INTO `semester_holiday` VALUES ('909092b07339bb9faa19c69941044b3c', '1', 'Weihnachtsferien 2003/2004', '', 1071874800, 1073257199);
INSERT INTO `semester_holiday` VALUES ('c121c20d8bfb2381274fad682f401999', '1', 'Weihnachtsferien 2004/2005', '', 1103324400, 1104706799);

    