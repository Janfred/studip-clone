# phpMyAdmin MySQL-Dump
# version 2.3.0
# http://phpwizard.net/phpMyAdmin/
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 27. März 2003 um 16:36
# Server Version: 3.23.52
# PHP-Version: 4.2.2
# Datenbank: `Seminar`
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `Institute`
#

CREATE TABLE Institute (
  Institut_id varchar(32) NOT NULL default '',
  Name varchar(255) NOT NULL default '',
  fakultaets_id varchar(32) NOT NULL default '',
  Strasse varchar(255) NOT NULL default '',
  Plz varchar(255) NOT NULL default '',
  url varchar(255) NOT NULL default 'http://www.studip.de',
  telefon varchar(32) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  fax varchar(32) NOT NULL default '',
  type int(10) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (Institut_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `active_sessions`
#

CREATE TABLE active_sessions (
  sid varchar(32) NOT NULL default '',
  name varchar(32) NOT NULL default '',
  val mediumtext,
  changed varchar(14) NOT NULL default '',
  PRIMARY KEY  (name,sid),
  KEY changed (changed),
  KEY sid (sid)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `admission_seminar_studiengang`
#

CREATE TABLE admission_seminar_studiengang (
  seminar_id varchar(32) NOT NULL default '',
  studiengang_id varchar(32) NOT NULL default '',
  quota int(3) NOT NULL default '0',
  PRIMARY KEY  (seminar_id,studiengang_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `admission_seminar_user`
#

CREATE TABLE admission_seminar_user (
  user_id varchar(32) NOT NULL default '',
  seminar_id varchar(32) NOT NULL default '',
  studiengang_id varchar(32) NOT NULL default '',
  status varchar(16) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  position int(5) default NULL,
  PRIMARY KEY  (user_id,seminar_id,studiengang_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `archiv`
#

CREATE TABLE archiv (
  seminar_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  untertitel varchar(255) NOT NULL default '',
  beschreibung text NOT NULL,
  start_time int(20) NOT NULL default '0',
  semester varchar(16) NOT NULL default '',
  heimat_inst_id varchar(32) NOT NULL default '',
  fakultaet_id varchar(32) NOT NULL default '',
  institute varchar(255) NOT NULL default '',
  dozenten varchar(255) NOT NULL default '',
  fakultaet varchar(255) NOT NULL default '',
  dump mediumtext NOT NULL,
  archiv_file_id varchar(32) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  forumdump longtext NOT NULL,
  studienbereiche text NOT NULL,
  PRIMARY KEY  (seminar_id),
  KEY heimat_inst_id (heimat_inst_id),
  KEY fakultaet_id (fakultaet_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `archiv_user`
#

CREATE TABLE archiv_user (
  seminar_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  status varchar(32) NOT NULL default '',
  PRIMARY KEY  (seminar_id,user_id),
  KEY seminar_id (seminar_id),
  KEY user_id (user_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `auth_user_md5`
#

CREATE TABLE auth_user_md5 (
  user_id varchar(32) NOT NULL default '',
  username varchar(64) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  perms varchar(255) default NULL,
  Vorname varchar(64) default NULL,
  Nachname varchar(64) default NULL,
  Email varchar(64) default NULL,
  PRIMARY KEY  (user_id),
  UNIQUE KEY k_username (username)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `contact`
#

CREATE TABLE contact (
  contact_id varchar(32) NOT NULL default '',
  owner_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  buddy smallint(6) NOT NULL default '1',
  PRIMARY KEY  (contact_id),
  KEY owner_id (owner_id),
  KEY user_id (user_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `contact_userinfo`
#

CREATE TABLE contact_userinfo (
  userinfo_id varchar(32) NOT NULL default '',
  contact_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  content text NOT NULL,
  priority int(11) NOT NULL default '0',
  PRIMARY KEY  (userinfo_id),
  KEY contact_id (contact_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `dokumente`
#

CREATE TABLE dokumente (
  dokument_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  seminar_id varchar(32) NOT NULL default '',
  description text,
  filename varchar(255) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  filesize int(20) NOT NULL default '0',
  autor_host varchar(20) NOT NULL default '',
  downloads int(20) NOT NULL default '0',
  PRIMARY KEY  (dokument_id),
  KEY range_id (range_id),
  KEY user_id (user_id),
  KEY seminar_id (seminar_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `extern_config`
#

CREATE TABLE extern_config (
  config_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  config_type int(4) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  is_standard int(4) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (config_id,range_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `folder`
#

CREATE TABLE folder (
  folder_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  description text,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (folder_id),
  KEY user_id (user_id),
  KEY range_id (range_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `globalmessages`
#

CREATE TABLE globalmessages (
  user_id_rec varchar(32) NOT NULL default '',
  user_id_snd varchar(32) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  message text NOT NULL,
  message_id varchar(32) NOT NULL default '',
  chat_id varchar(32) default NULL,
  PRIMARY KEY  (message_id),
  KEY user_id_rec (user_id_rec),
  KEY user_id_snd (user_id_snd),
  KEY mkdate (mkdate),
  KEY chat_id (chat_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `kategorien`
#

CREATE TABLE kategorien (
  kategorie_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  content text NOT NULL,
  hidden tinyint(4) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  priority int(11) NOT NULL default '0',
  PRIMARY KEY  (kategorie_id),
  KEY kategorie_id_2 (kategorie_id,range_id),
  KEY priority (priority),
  KEY range_id (range_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `literatur`
#

CREATE TABLE literatur (
  literatur_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  literatur text,
  links text,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (literatur_id),
  KEY range_id (range_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `news`
#

CREATE TABLE news (
  news_id varchar(32) NOT NULL default '',
  topic varchar(70) NOT NULL default '',
  body text NOT NULL,
  author varchar(255) NOT NULL default '',
  date int(11) NOT NULL default '0',
  user_id varchar(32) NOT NULL default '',
  expire int(11) NOT NULL default '0',
  PRIMARY KEY  (news_id),
  KEY date (date),
  KEY user_id (user_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `news_range`
#

CREATE TABLE news_range (
  news_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (news_id,range_id),
  KEY range_id (range_id),
  KEY news_id (news_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `px_topics`
#

CREATE TABLE px_topics (
  topic_id varchar(32) NOT NULL default '',
  parent_id varchar(32) NOT NULL default '0',
  root_id varchar(32) NOT NULL default '0',
  name varchar(255) default NULL,
  description text,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  author varchar(255) default NULL,
  author_host varchar(255) default NULL,
  Seminar_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (topic_id),
  KEY root_id (root_id),
  KEY Seminar_id (Seminar_id),
  KEY parent_id (parent_id),
  KEY user_id (user_id),
  KEY mkdate (mkdate)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `range_tree`
#

CREATE TABLE range_tree (
  item_id varchar(32) NOT NULL default '',
  parent_id varchar(32) NOT NULL default '',
  priority int(11) NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  studip_object varchar(10) default NULL,
  studip_object_id varchar(32) default NULL,
  PRIMARY KEY  (item_id),
  KEY parent_id (parent_id),
  KEY priority (priority),
  KEY studip_object_id (studip_object_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_assign`
#

CREATE TABLE resources_assign (
  assign_id varchar(32) NOT NULL default '',
  resource_id varchar(32) NOT NULL default '',
  assign_user_id varchar(32) NOT NULL default '',
  user_free_name varchar(255) default NULL,
  begin int(20) NOT NULL default '0',
  end int(20) NOT NULL default '0',
  repeat_end int(20) default NULL,
  repeat_quantity int(2) default NULL,
  repeat_interval int(2) default NULL,
  repeat_month_of_year int(2) default NULL,
  repeat_day_of_month int(2) default NULL,
  repeat_month int(2) default NULL,
  repeat_week_of_month int(2) default NULL,
  repeat_day_of_week int(2) default NULL,
  repeat_week int(2) default NULL,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (assign_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_categories`
#

CREATE TABLE resources_categories (
  category_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  description text NOT NULL,
  system tinyint(4) NOT NULL default '0',
  iconnr int(3) unsigned default '1',
  PRIMARY KEY  (category_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_categories_properties`
#

CREATE TABLE resources_categories_properties (
  category_id varchar(32) NOT NULL default '',
  property_id varchar(32) NOT NULL default '',
  system tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (category_id,property_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_objects`
#

CREATE TABLE resources_objects (
  resource_id varchar(32) NOT NULL default '',
  root_id varchar(32) NOT NULL default '',
  parent_id varchar(32) NOT NULL default '',
  category_id varchar(32) NOT NULL default '',
  owner_id varchar(32) NOT NULL default '',
  level varchar(4) default NULL,
  name varchar(255) NOT NULL default '',
  description text NOT NULL,
  inventar_num varchar(255) NOT NULL default '',
  parent_bind tinyint(4) default NULL,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (resource_id),
  KEY categorie_id (category_id,owner_id),
  KEY root_id (root_id,parent_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_objects_properties`
#

CREATE TABLE resources_objects_properties (
  resource_id varchar(32) NOT NULL default '',
  property_id varchar(32) NOT NULL default '',
  state text NOT NULL,
  PRIMARY KEY  (resource_id,property_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_properties`
#

CREATE TABLE resources_properties (
  property_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  description text NOT NULL,
  type set('bool','text','num','select') NOT NULL default 'bool',
  options text NOT NULL,
  system tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (property_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_user_resources`
#

CREATE TABLE resources_user_resources (
  user_id varchar(32) NOT NULL default '',
  resource_id varchar(32) NOT NULL default '',
  perms varchar(10) NOT NULL default '',
  PRIMARY KEY  (user_id,resource_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sem_tree`
#

CREATE TABLE sem_tree (
  sem_tree_id varchar(32) NOT NULL default '',
  parent_id varchar(32) NOT NULL default '',
  priority tinyint(4) NOT NULL default '0',
  info text NOT NULL,
  name varchar(255) NOT NULL default '',
  studip_object_id varchar(32) default NULL,
  PRIMARY KEY  (sem_tree_id),
  KEY parent_id (parent_id),
  KEY priority (priority),
  KEY studip_object_id (studip_object_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_inst`
#

CREATE TABLE seminar_inst (
  seminar_id varchar(32) NOT NULL default '',
  institut_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (seminar_id,institut_id),
  KEY seminar_id (seminar_id),
  KEY institut_id (institut_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_lernmodul`
#

CREATE TABLE seminar_lernmodul (
  seminar_id varchar(32) NOT NULL default '',
  co_inst bigint(20) NOT NULL default '0',
  co_id bigint(20) NOT NULL default '0',
  PRIMARY KEY  (seminar_id,co_id),
  KEY seminar_id (seminar_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_sem_tree`
#

CREATE TABLE seminar_sem_tree (
  seminar_id varchar(32) NOT NULL default '',
  sem_tree_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (seminar_id,sem_tree_id),
  KEY seminar_id (seminar_id),
  KEY sem_tree_id (sem_tree_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_user`
#

CREATE TABLE seminar_user (
  Seminar_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  status varchar(32) NOT NULL default '',
  gruppe tinyint(4) NOT NULL default '0',
  admission_studiengang_id varchar(32) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  PRIMARY KEY  (Seminar_id,user_id),
  KEY user_id (user_id),
  KEY Seminar_id (Seminar_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminare`
#

CREATE TABLE seminare (
  Seminar_id varchar(32) NOT NULL default '0',
  VeranstaltungsNummer varchar(32) default NULL,
  Institut_id varchar(32) NOT NULL default '0',
  Name varchar(255) NOT NULL default '',
  Untertitel varchar(255) default NULL,
  status varchar(32) NOT NULL default '1',
  Beschreibung text NOT NULL,
  Ort varchar(255) default NULL,
  Sonstiges text,
  Passwort varchar(32) default NULL,
  Lesezugriff tinyint(4) NOT NULL default '0',
  Schreibzugriff tinyint(4) NOT NULL default '0',
  start_time int(20) default '0',
  duration_time int(20) default NULL,
  art varchar(255) default NULL,
  teilnehmer text,
  vorrausetzungen text,
  lernorga text,
  leistungsnachweis text,
  metadata_dates text,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  ects varchar(32) default NULL,
  admission_endtime int(20) default NULL,
  admission_turnout int(5) default NULL,
  admission_binding tinyint(4) default NULL,
  admission_type int(3) NOT NULL default '0',
  admission_selection_take_place tinyint(4) default NULL,
  showscore tinyint(3) default '0',
  PRIMARY KEY  (Seminar_id),
  KEY Institut_id (Institut_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `statusgruppe_user`
#

CREATE TABLE statusgruppe_user (
  statusgruppe_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (statusgruppe_id,user_id),
  KEY user_id (user_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `statusgruppen`
#

CREATE TABLE statusgruppen (
  statusgruppe_id varchar(32) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  position int(20) NOT NULL default '0',
  size int(20) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (statusgruppe_id),
  KEY range_id (range_id),
  KEY position (position)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `studiengaenge`
#

CREATE TABLE studiengaenge (
  studiengang_id varchar(32) NOT NULL default '',
  name varchar(255) default NULL,
  beschreibung text,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (studiengang_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `studip_ilias`
#

CREATE TABLE studip_ilias (
  studip_user_id varchar(32) NOT NULL default '',
  ilias_user_id bigint(20) NOT NULL default '0',
  is_created tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (studip_user_id,ilias_user_id),
  KEY is_created (is_created)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `termine`
#

CREATE TABLE termine (
  termin_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  autor_id varchar(32) NOT NULL default '',
  content varchar(255) NOT NULL default '',
  description text,
  date int(20) NOT NULL default '0',
  end_time int(20) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  date_typ tinyint(4) NOT NULL default '0',
  topic_id varchar(32) default NULL,
  expire int(20) default NULL,
  repeat varchar(128) default NULL,
  color varchar(20) default NULL,
  priority tinyint(4) default NULL,
  raum varchar(255) default NULL,
  PRIMARY KEY  (termin_id),
  KEY range_id (range_id),
  KEY autor_id (autor_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `user_info`
#

CREATE TABLE user_info (
  user_id varchar(32) NOT NULL default '',
  hobby varchar(255) NOT NULL default '',
  lebenslauf text,
  raum varchar(32) default NULL,
  sprechzeiten varchar(200) default NULL,
  publi text NOT NULL,
  schwerp text NOT NULL,
  Lehre text NOT NULL,
  Home varchar(200) NOT NULL default '',
  privatnr varchar(32) NOT NULL default '',
  privadr varchar(64) NOT NULL default '',
  score bigint(20) NOT NULL default '0',
  geschlecht tinyint(4) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  hide_studiengang tinyint(4) default NULL,
  preferred_language varchar(6) default NULL,
  title_front varchar(64) NOT NULL default '',
  title_rear varchar(64) NOT NULL default '',
  PRIMARY KEY  (user_id),
  KEY score (score)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `user_inst`
#

CREATE TABLE user_inst (
  user_id varchar(32) NOT NULL default '0',
  Institut_id varchar(32) NOT NULL default '0',
  inst_perms varchar(255) default '0',
  sprechzeiten varchar(200) NOT NULL default '',
  raum varchar(32) NOT NULL default '',
  Telefon varchar(32) NOT NULL default '',
  Fax varchar(32) NOT NULL default '',
  PRIMARY KEY  (user_id,Institut_id),
  KEY user_id (user_id),
  KEY Institut_id (Institut_id),
  KEY inst_perms (inst_perms)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `user_studiengang`
#

CREATE TABLE user_studiengang (
  user_id varchar(32) NOT NULL default '',
  studiengang_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (user_id,studiengang_id)
) TYPE=MyISAM;

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

# Anlegen des Benutzers root 

# Benutzer: root@studip ; Password: testing
INSERT INTO auth_user_md5 VALUES( '76ed43ef286fb55cf9e41beadb484a9f', 'root@studip', 'ae2b1fca515949e5d54fb22b8ed95575', 'root', 'Root', 'Studip', 'root@localhost');
INSERT INTO user_info SET user_id ='76ed43ef286fb55cf9e41beadb484a9f';