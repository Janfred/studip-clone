#
# Dumping data for table 'resources_categories'
#

INSERT INTO resources_categories VALUES("1cf2a34de92c06137ecdfcef4a29e4bc", "Raum", "", "1", "3");
INSERT INTO resources_categories VALUES("82bdd20907e914de72bbfc8043dd3a46", "Gebäude", "", "0", "1");
INSERT INTO resources_categories VALUES("891662c701078186c857fca25d34ade6", "Gerät", "", "0", "2");


#
# Dumping data for table 'resources_categories_properties'
#

INSERT INTO resources_categories_properties VALUES("82bdd20907e914de72bbfc8043dd3a46", "8772d6757457c8b4a05b180e1c2eba9c", "0");
INSERT INTO resources_categories_properties VALUES("82bdd20907e914de72bbfc8043dd3a46", "5753ab43945ae787f983f5c8a036712d", "0");
INSERT INTO resources_categories_properties VALUES("891662c701078186c857fca25d34ade6", "1b86b5026052fd3d8624fead31204cba", "0");
INSERT INTO resources_categories_properties VALUES("891662c701078186c857fca25d34ade6", "9c0658891b95fe962d013f1308feb80d", "0");
INSERT INTO resources_categories_properties VALUES("891662c701078186c857fca25d34ade6", "7bff1a7d45bc37280e988f6e8d007bad", "0");
INSERT INTO resources_categories_properties VALUES("1cf2a34de92c06137ecdfcef4a29e4bc", "0ef8a73d95f335cdfbaec50cae92762a", "0");
INSERT INTO resources_categories_properties VALUES("1cf2a34de92c06137ecdfcef4a29e4bc", "5753ab43945ae787f983f5c8a036712d", "0");
INSERT INTO resources_categories_properties VALUES("1cf2a34de92c06137ecdfcef4a29e4bc", "31abad810703df361d793361bf6b16e5", "0");
INSERT INTO resources_categories_properties VALUES("1cf2a34de92c06137ecdfcef4a29e4bc", "ef4ba565e635b45c3f43ecdc69fb4aca", "1");
INSERT INTO resources_categories_properties VALUES("1cf2a34de92c06137ecdfcef4a29e4bc", "648b8579ffca64a565459fd6ea0313c5", "0");

#
# Dumping data for table 'resources_properties'
#

INSERT INTO resources_properties VALUES("ef4ba565e635b45c3f43ecdc69fb4aca", "Sitzplätze", "", "num", "", "1");
INSERT INTO resources_properties VALUES("8772d6757457c8b4a05b180e1c2eba9c", "Adresse", "", "text", "", "0");
INSERT INTO resources_properties VALUES("0ef8a73d95f335cdfbaec50cae92762a", "Ausstattung", "", "text", "", "0");
INSERT INTO resources_properties VALUES("7bff1a7d45bc37280e988f6e8d007bad", "Seriennummer", "", "num", "", "0");
INSERT INTO resources_properties VALUES("31abad810703df361d793361bf6b16e5", "Raumtyp", "", "select", "Hörsaal;Übungsraum;Sitzungszimmer", "0");
INSERT INTO resources_properties VALUES("5753ab43945ae787f983f5c8a036712d", "behindertengerecht", "", "bool", "", "0");
INSERT INTO resources_properties VALUES("648b8579ffca64a565459fd6ea0313c5", "Verdunklung", "", "bool", "vorhanden", "0");
INSERT INTO resources_properties VALUES("9c0658891b95fe962d013f1308feb80d", "Hersteller", "", "num", "", "0");
INSERT INTO resources_properties VALUES("1b86b5026052fd3d8624fead31204cba", "Kaufdatum", "", "num", "", "0");

