# phpMyAdmin MySQL-Dump
# version 2.3.3pl1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Erstellungszeit: 17. September 2003 um 10:29
# Server Version: 3.23.52
# PHP-Version: 4.2.2
# Datenbank: `studip`
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
  modules int(10) unsigned default NULL,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (Institut_id),
  KEY fakultaets_id (fakultaets_id),
  KEY chdate (chdate)
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
  KEY changed (changed)
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
  comment tinytext,
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
  institute varchar(255) NOT NULL default '',
  dozenten varchar(255) NOT NULL default '',
  fakultaet varchar(255) NOT NULL default '',
  dump mediumtext NOT NULL,
  archiv_file_id varchar(32) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  forumdump longtext NOT NULL,
  studienbereiche text NOT NULL,
  PRIMARY KEY  (seminar_id),
  UNIQUE KEY seminar_id (seminar_id),
  KEY heimat_inst_id (heimat_inst_id)
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
  auth_plugin varchar(64) default NULL,
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
  buddy tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (contact_id),
  UNIQUE KEY owner_user (owner_id,user_id),
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
  KEY contact_id (contact_id),
  KEY priority (priority)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `datafields`
#

CREATE TABLE datafields (
  datafield_id varchar(32) NOT NULL default '',
  name varchar(255) default NULL,
  object_type enum('sem','inst','user') default NULL,
  object_class varchar(10) default NULL,
  edit_perms enum('user','autor','tutor','dozent','admin','root') default NULL,
  view_perms varchar(10) default NULL,
  priority tinyint(3) unsigned NOT NULL default '0',
  mkdate int(20) unsigned default NULL,
  chdate int(20) unsigned default NULL,
  PRIMARY KEY  (datafield_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `datafields_entries`
#

CREATE TABLE datafields_entries (
  datafield_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  content text,
  mkdate int(20) unsigned default NULL,
  chdate int(20) unsigned default NULL,
  PRIMARY KEY  (datafield_id,range_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `dokumente`
#

CREATE TABLE dokumente (
  dokument_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  seminar_id varchar(32) NOT NULL default '0',
  name varchar(255) default NULL,
  description text NOT NULL,
  filename varchar(255) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  filesize int(20) NOT NULL default '0',
  autor_host varchar(20) NOT NULL default '',
  downloads int(20) NOT NULL default '0',
  PRIMARY KEY  (dokument_id),
  KEY range_id (range_id),
  KEY seminar_id (seminar_id),
  KEY user_id (user_id),
  KEY chdate (chdate)
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
  KEY range_id (range_id),
  KEY chdate (chdate)
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
  KEY priority (priority)
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
  KEY range_id (range_id),
  KEY mkdate (mkdate),
  KEY chdate (chdate)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `message`
#

CREATE TABLE message (
  message_id varchar(32) NOT NULL default '',
  chat_id varchar(32) default NULL,
  autor_id varchar(32) NOT NULL default '',
  message text NOT NULL,
  mkdate int(20) NOT NULL default '0',
  PRIMARY KEY  (message_id),
  KEY chat_id (chat_id),
  KEY autor_id (autor_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `message_user`
#

CREATE TABLE message_user (
  user_id varchar(32) NOT NULL default '',
  message_id varchar(32) NOT NULL default '',
  readed tinyint(1) NOT NULL default '0',
  deleted tinyint(1) NOT NULL default '0',
  snd_rec char(3) NOT NULL default '',
  PRIMARY KEY  (user_id,message_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `news`
#

CREATE TABLE news (
  news_id varchar(32) NOT NULL default '',
  topic varchar(255) NOT NULL default '',
  body text NOT NULL,
  author varchar(255) NOT NULL default '',
  date int(11) NOT NULL default '0',
  user_id varchar(32) NOT NULL default '',
  expire int(11) NOT NULL default '0',
  PRIMARY KEY  (news_id),
  KEY date (date)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `news_range`
#

CREATE TABLE news_range (
  news_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (news_id,range_id),
  KEY news_id (news_id),
  KEY range_id (range_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_rate`
#

CREATE TABLE object_rate (
  object_id varchar(32) NOT NULL default '',
  rate int(10) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  KEY object_id (object_id),
  KEY rate (rate)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_user`
#

CREATE TABLE object_user (
  object_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  flag varchar(32) NOT NULL default '',
  mkdate int(20) NOT NULL default '0',
  PRIMARY KEY  (object_id,user_id,flag)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_views`
#

CREATE TABLE object_views (
  object_id varchar(32) NOT NULL default '',
  views int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  PRIMARY KEY  (object_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `px_topics`
#

CREATE TABLE px_topics (
  topic_id varchar(32) NOT NULL default '',
  parent_id varchar(32) NOT NULL default '',
  root_id varchar(32) NOT NULL default '',
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
  KEY chdate (chdate),
  KEY mkdate (mkdate)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `range_tree`
#

CREATE TABLE range_tree (
  item_id varchar(32) NOT NULL default '',
  parent_id varchar(32) NOT NULL default '',
  level int(11) NOT NULL default '0',
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
  iconnr int(3) default '1',
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
  level int(4) default NULL,
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
  PRIMARY KEY  (seminar_id,institut_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_lernmodul`
#

CREATE TABLE seminar_lernmodul (
  seminar_id varchar(32) NOT NULL default '',
  co_inst bigint(20) NOT NULL default '0',
  co_id bigint(20) NOT NULL default '0',
  status tinyint(4) NOT NULL default '0',
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
  comment tinytext,
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
  admission_selection_take_place tinyint(4) default '0',
  admission_group varchar(32) default NULL,
  admission_prelim tinyint(4) unsigned NOT NULL default '0',
  admission_prelim_txt text,
  admission_starttime int(20) NOT NULL default '-1',
  admission_endtime_sem int(20) NOT NULL default '-1',
  visible tinyint(2) unsigned NOT NULL default '1',
  showscore tinyint(3) default '0',
  modules int(10) unsigned default NULL,
  PRIMARY KEY  (Seminar_id),
  KEY Institut_id (Institut_id),
  KEY chdate (chdate)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `statusgruppe_user`
#

CREATE TABLE statusgruppe_user (
  statusgruppe_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  PRIMARY KEY  (statusgruppe_id,user_id)
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
# Tabellenstruktur für Tabelle `support_contract`
#

CREATE TABLE support_contract (
  contract_id varchar(32) NOT NULL default '',
  institut_id varchar(32) default NULL,
  range_id varchar(32) default NULL,
  given_points int(20) unsigned NOT NULL default '0',
  contract_begin int(20) unsigned NOT NULL default '0',
  contract_end int(20) unsigned NOT NULL default '0',
  mkdate int(20) unsigned NOT NULL default '0',
  chdate int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (contract_id),
  KEY contract_id (contract_id,institut_id,range_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `support_event`
#

CREATE TABLE support_event (
  event_id varchar(32) NOT NULL default '',
  request_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  begin int(20) unsigned NOT NULL default '0',
  end int(20) unsigned NOT NULL default '0',
  used_points int(4) unsigned NOT NULL default '0',
  mkdate int(20) unsigned NOT NULL default '0',
  chdate int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (event_id),
  KEY event_id (event_id,user_id,mkdate,request_id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `support_request`
#

CREATE TABLE support_request (
  request_id varchar(32) NOT NULL default '',
  contract_id varchar(32) NOT NULL default '',
  name varchar(255) default NULL,
  date int(20) unsigned NOT NULL default '0',
  user_id varchar(32) NOT NULL default '0',
  channel tinyint(3) unsigned default '0',
  topic_id varchar(32) NOT NULL default '',
  mkdate int(20) unsigned NOT NULL default '0',
  chdate int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (request_id),
  KEY contract_id (contract_id,topic_id,user_id)
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
  KEY chdate (chdate),
  KEY date_typ (date_typ),
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
  publi text NOT NULL,
  schwerp text NOT NULL,
  Home varchar(200) NOT NULL default '',
  privatnr varchar(32) NOT NULL default '',
  privadr varchar(64) NOT NULL default '',
  score bigint(20) NOT NULL default '0',
  geschlecht tinyint(4) NOT NULL default '0',
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  title_front varchar(64) NOT NULL default '',
  title_rear varchar(64) NOT NULL default '',
  preferred_language varchar(6) default NULL,
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
  KEY Institut_id (Institut_id),
  KEY user_id (user_id),
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
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `vote`
#

CREATE TABLE vote (
  vote_id varchar(32) NOT NULL default '',
  author_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  type enum('vote','test') NOT NULL default 'vote',
  title varchar(100) NOT NULL default '',
  question varchar(255) NOT NULL default '',
  state enum('new','active','stopvis','stopinvis') NOT NULL default 'new',
  startdate int(20) default NULL,
  stopdate int(20) default NULL,
  timespan int(20) default NULL,
  mkdate int(20) NOT NULL default '0',
  chdate int(20) NOT NULL default '0',
  resultvisibility enum('ever','delivery','end','never') NOT NULL default 'ever',
  multiplechoice tinyint(1) NOT NULL default '0',
  anonymous tinyint(1) NOT NULL default '1',
  changeable tinyint(1) NOT NULL default '0',
  co_visibility tinyint(1) default NULL,
  namesvisibility tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (vote_id),
  KEY range_id (range_id),
  KEY state (state),
  KEY startdate (startdate),
  KEY stopdate (stopdate),
  KEY resultvisibility (resultvisibility),
  KEY chdate (chdate),
  KEY author_id (author_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `vote_user`
#

CREATE TABLE vote_user (
  vote_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  votedate int(20) default NULL,
  PRIMARY KEY  (vote_id,user_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `voteanswers`
#

CREATE TABLE voteanswers (
  answer_id varchar(32) NOT NULL default '',
  vote_id varchar(32) NOT NULL default '',
  answer varchar(255) NOT NULL default '',
  position int(11) NOT NULL default '0',
  counter int(11) NOT NULL default '0',
  correct tinyint(1) default NULL,
  PRIMARY KEY  (answer_id),
  KEY vote_id (vote_id),
  KEY position (position)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `voteanswers_user`
#

CREATE TABLE voteanswers_user (
  answer_id varchar(32) NOT NULL default '',
  user_id varchar(32) NOT NULL default '',
  votedate int(20) default NULL,
  PRIMARY KEY  (answer_id,user_id)
) TYPE=MyISAM PACK_KEYS=1;
# --------------------------------------------------------


#
# Tabellenstruktur für Tabelle `wiki`
#

CREATE TABLE wiki (
  range_id varchar(32) NOT NULL default '',
  user_id varchar(32) default NULL,
  keyword varchar(128) NOT NULL default '',
  body text,
  chdate int(11) default NULL,
  version int(11) NOT NULL default '0',
  PRIMARY KEY  (range_id,keyword,version),
  KEY user_id (user_id),
  KEY chdate (chdate)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `wiki_links`
#

CREATE TABLE wiki_links (
  range_id char(32) NOT NULL default '',
  from_keyword char(128) NOT NULL default '',
  to_keyword char(128) NOT NULL default '',
  PRIMARY KEY  (range_id,to_keyword,from_keyword)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `wiki_locks`
#

CREATE TABLE wiki_locks (
  user_id varchar(32) NOT NULL default '',
  range_id varchar(32) NOT NULL default '',
  keyword varchar(128) NOT NULL default '',
  chdate int(11) NOT NULL default '0',
  PRIMARY KEY  (range_id,user_id,keyword),
  KEY user_id (user_id),
  KEY chdate (chdate)
) TYPE=MyISAM;

