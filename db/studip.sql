# Erstellungszeit: 13. April 2004 um 17:10
# Server Version: 4.0.15
# PHP-Version: 4.3.3
# Datenbank: `studip`
# $Id$

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `Institute`
#

DROP TABLE IF EXISTS `Institute`;
CREATE TABLE `Institute` (
  `Institut_id` varchar(32) NOT NULL default '',
  `Name` varchar(255) NOT NULL default '',
  `fakultaets_id` varchar(32) NOT NULL default '',
  `Strasse` varchar(255) NOT NULL default '',
  `Plz` varchar(255) NOT NULL default '',
  `url` varchar(255) NOT NULL default 'http://www.studip.de',
  `telefon` varchar(32) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `fax` varchar(32) NOT NULL default '',
  `type` int(10) NOT NULL default '0',
  `modules` int(10) unsigned default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `lit_plugin_name` varchar(255) default NULL,
  PRIMARY KEY  (`Institut_id`),
  KEY `fakultaets_id` (`fakultaets_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `active_sessions`
#

DROP TABLE IF EXISTS `active_sessions`;
CREATE TABLE `active_sessions` (
  `sid` varchar(32) NOT NULL default '',
  `name` varchar(32) NOT NULL default '',
  `val` mediumtext,
  `changed` varchar(14) NOT NULL default '',
  PRIMARY KEY  (`name`,`sid`),
  KEY `changed` (`changed`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `admission_seminar_studiengang`
#

DROP TABLE IF EXISTS `admission_seminar_studiengang`;
CREATE TABLE `admission_seminar_studiengang` (
  `seminar_id` varchar(32) NOT NULL default '',
  `studiengang_id` varchar(32) NOT NULL default '',
  `quota` int(3) NOT NULL default '0',
  PRIMARY KEY  (`seminar_id`,`studiengang_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `admission_seminar_user`
#

DROP TABLE IF EXISTS `admission_seminar_user`;
CREATE TABLE `admission_seminar_user` (
  `user_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '',
  `studiengang_id` varchar(32) NOT NULL default '',
  `status` varchar(16) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `position` int(5) default NULL,
  `comment` tinytext,
  PRIMARY KEY  (`user_id`,`seminar_id`,`studiengang_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `archiv`
#

DROP TABLE IF EXISTS `archiv`;
CREATE TABLE `archiv` (
  `seminar_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `untertitel` varchar(255) NOT NULL default '',
  `beschreibung` text NOT NULL,
  `start_time` int(20) NOT NULL default '0',
  `semester` varchar(16) NOT NULL default '',
  `heimat_inst_id` varchar(32) NOT NULL default '',
  `institute` varchar(255) NOT NULL default '',
  `dozenten` varchar(255) NOT NULL default '',
  `fakultaet` varchar(255) NOT NULL default '',
  `dump` mediumtext NOT NULL,
  `archiv_file_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `forumdump` longtext NOT NULL,
  `studienbereiche` text NOT NULL,
  `VeranstaltungsNummer` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`),
  UNIQUE KEY `seminar_id` (`seminar_id`),
  KEY `heimat_inst_id` (`heimat_inst_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `archiv_user`
#

DROP TABLE IF EXISTS `archiv_user`;
CREATE TABLE `archiv_user` (
  `seminar_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `status` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`,`user_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `auth_user_md5`
#

DROP TABLE IF EXISTS `auth_user_md5`;
CREATE TABLE `auth_user_md5` (
  `user_id` varchar(32) NOT NULL default '',
  `username` varchar(64) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `perms` varchar(255) default NULL,
  `Vorname` varchar(64) default NULL,
  `Nachname` varchar(64) default NULL,
  `Email` varchar(64) default NULL,
  `auth_plugin` varchar(64) default NULL,
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `k_username` (`username`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `banner_ads`
#

DROP TABLE IF EXISTS `banner_ads`;
CREATE TABLE `banner_ads` (
  `ad_id` varchar(32) NOT NULL default '',
  `banner_path` varchar(255) NOT NULL default '',
  `description` varchar(255) default NULL,
  `alttext` varchar(255) default NULL,
  `target_type` enum('url','seminar','inst','user','none') NOT NULL default 'url',
  `target` varchar(255) NOT NULL default '',
  `startdate` int(20) NOT NULL default '0',
  `enddate` int(20) NOT NULL default '0',
  `priority` int(4) NOT NULL default '0',
  `views` int(11) NOT NULL default '0',
  `clicks` int(11) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`ad_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `calendar_events`
#

DROP TABLE IF EXISTS `calendar_events`;
CREATE TABLE `calendar_events` (
  `event_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `autor_id` varchar(32) NOT NULL default '',
  `uid` varchar(255) NOT NULL default '',
  `start` int(10) unsigned NOT NULL default '0',
  `end` int(10) unsigned NOT NULL default '0',
  `summary` varchar(255) NOT NULL default '',
  `description` text,
  `class` enum('PUBLIC','PRIVATE','CONFIDENTIAL') NOT NULL default 'PUBLIC',
  `categories` tinytext,
  `category_intern` tinyint(3) unsigned NOT NULL default '0',
  `priority` tinyint(3) unsigned NOT NULL default '0',
  `location` tinytext,
  `ts` int(10) unsigned NOT NULL default '0',
  `linterval` smallint(5) unsigned default NULL,
  `sinterval` smallint(5) unsigned default NULL,
  `wdays` varchar(7) default NULL,
  `month` tinyint(3) unsigned default NULL,
  `day` tinyint(3) unsigned default NULL,
  `rtype` enum('SINGLE','DAILY','WEEKLY','MONTHLY','YEARLY') NOT NULL default 'SINGLE',
  `duration` smallint(5) unsigned NOT NULL default '0',
  `count` smallint(5) default '0',
  `expire` int(10) unsigned NOT NULL default '0',
  `exceptions` text,
  `mkdate` int(10) unsigned NOT NULL default '0',
  `chdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`event_id`),
  UNIQUE KEY `uid_range` (`uid`,`range_id`),
  KEY `range_id` (`range_id`),
  KEY `autor_id` (`autor_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `config`
#

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `config_id` varchar(32) NOT NULL default '',
  `key` varchar(255) NOT NULL default '',
  `value` varchar(255) NOT NULL default '',
  `default_value` varchar(255) NOT NULL default '',
  `chdate` int(20) NOT NULL default '0',
  `comment` text NOT NULL,
  PRIMARY KEY  (`config_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `contact`
#

DROP TABLE IF EXISTS `contact`;
CREATE TABLE `contact` (
  `contact_id` varchar(32) NOT NULL default '',
  `owner_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `buddy` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`contact_id`),
  UNIQUE KEY `owner_user` (`owner_id`,`user_id`),
  KEY `owner_id` (`owner_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `contact_userinfo`
#

DROP TABLE IF EXISTS `contact_userinfo`;
CREATE TABLE `contact_userinfo` (
  `userinfo_id` varchar(32) NOT NULL default '',
  `contact_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `priority` int(11) NOT NULL default '0',
  PRIMARY KEY  (`userinfo_id`),
  KEY `contact_id` (`contact_id`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `datafields`
#

DROP TABLE IF EXISTS `datafields`;
CREATE TABLE `datafields` (
  `datafield_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `object_type` enum('sem','inst','user') default NULL,
  `object_class` varchar(10) default NULL,
  `edit_perms` enum('user','autor','tutor','dozent','admin','root') default NULL,
  `view_perms` varchar(10) default NULL,
  `priority` tinyint(3) unsigned NOT NULL default '0',
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`datafield_id`),
  KEY `object_type` (`object_type`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `datafields_entries`
#

DROP TABLE IF EXISTS `datafields_entries`;
CREATE TABLE `datafields_entries` (
  `datafield_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `content` text,
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`datafield_id`,`range_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `dokumente`
#

DROP TABLE IF EXISTS `dokumente`;
CREATE TABLE `dokumente` (
  `dokument_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '0',
  `name` varchar(255) default NULL,
  `description` text NOT NULL,
  `filename` varchar(255) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `filesize` int(20) NOT NULL default '0',
  `autor_host` varchar(20) NOT NULL default '',
  `downloads` int(20) NOT NULL default '0',
  `url` varchar(255) NOT NULL default '',
  `protected` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`dokument_id`),
  KEY `range_id` (`range_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`),
  KEY `mkdate` (`mkdate`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `eval`
#

DROP TABLE IF EXISTS `eval`;
CREATE TABLE `eval` (
  `eval_id` varchar(32) NOT NULL default '',
  `author_id` varchar(32) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `startdate` int(20) default NULL,
  `stopdate` int(20) default NULL,
  `timespan` int(20) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `anonymous` tinyint(1) NOT NULL default '1',
  `visible` tinyint(1) NOT NULL default '1',
  `shared` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`eval_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `eval_range`
#

DROP TABLE IF EXISTS `eval_range`;
CREATE TABLE `eval_range` (
  `eval_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`eval_id`,`range_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `eval_user`
#

DROP TABLE IF EXISTS `eval_user`;
CREATE TABLE `eval_user` (
  `eval_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`eval_id`,`user_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `evalanswer`
#

DROP TABLE IF EXISTS `evalanswer`;
CREATE TABLE `evalanswer` (
  `evalanswer_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `text` text NOT NULL,
  `value` int(11) NOT NULL default '0',
  `rows` tinyint(4) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  `residual` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`evalanswer_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `evalanswer_user`
#

DROP TABLE IF EXISTS `evalanswer_user`;
CREATE TABLE `evalanswer_user` (
  `evalanswer_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`evalanswer_id`,`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `evalgroup`
#

DROP TABLE IF EXISTS `evalgroup`;
CREATE TABLE `evalgroup` (
  `evalgroup_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `position` int(11) NOT NULL default '0',
  `child_type` enum('EvaluationGroup','EvaluationQuestion') NOT NULL default 'EvaluationGroup',
  `mandatory` tinyint(1) NOT NULL default '0',
  `template_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`evalgroup_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `evalquestion`
#

DROP TABLE IF EXISTS `evalquestion`;
CREATE TABLE `evalquestion` (
  `evalquestion_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `type` enum('likertskala','multiplechoice','polskala') NOT NULL default 'multiplechoice',
  `position` int(11) NOT NULL default '0',
  `text` text NOT NULL,
  `multiplechoice` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`evalquestion_id`),
  KEY `parent_id` (`parent_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `extern_config`
#

DROP TABLE IF EXISTS `extern_config`;
CREATE TABLE `extern_config` (
  `config_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `config_type` int(4) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `is_standard` int(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`config_id`,`range_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `folder`
#

DROP TABLE IF EXISTS `folder`;
CREATE TABLE `folder` (
  `folder_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`folder_id`),
  KEY `user_id` (`user_id`),
  KEY `range_id` (`range_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `guestbook`
#

DROP TABLE IF EXISTS `guestbook`;
CREATE TABLE `guestbook` (
  `post_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `content` text NOT NULL,
  PRIMARY KEY  (`post_id`),
  KEY `post_id` (`post_id`),
  KEY `range_id` (`range_id`),
  KEY `user_id` (`user_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `kategorien`
#

DROP TABLE IF EXISTS `kategorien`;
CREATE TABLE `kategorien` (
  `kategorie_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `content` text NOT NULL,
  `hidden` tinyint(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `priority` int(11) NOT NULL default '0',
  PRIMARY KEY  (`kategorie_id`),
  KEY `kategorie_id_2` (`kategorie_id`,`range_id`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lit_catalog`
#

DROP TABLE IF EXISTS `lit_catalog`;
CREATE TABLE `lit_catalog` (
  `catalog_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `lit_plugin` varchar(100) NOT NULL default 'Studip',
  `accession_number` varchar(100) default NULL,
  `dc_title` varchar(255) NOT NULL default '',
  `dc_creator` varchar(255) NOT NULL default '',
  `dc_subject` varchar(255) default NULL,
  `dc_description` text,
  `dc_publisher` varchar(255) default NULL,
  `dc_contributor` varchar(255) default NULL,
  `dc_date` date default NULL,
  `dc_type` varchar(100) default NULL,
  `dc_format` varchar(100) default NULL,
  `dc_identifier` varchar(255) default NULL,
  `dc_source` varchar(255) default NULL,
  `dc_language` varchar(10) default NULL,
  `dc_relation` varchar(255) default NULL,
  `dc_coverage` varchar(255) default NULL,
  `dc_rights` varchar(255) default NULL,
  PRIMARY KEY  (`catalog_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lit_list`
#

DROP TABLE IF EXISTS `lit_list`;
CREATE TABLE `lit_list` (
  `list_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `format` varchar(255) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `priority` smallint(6) NOT NULL default '0',
  `visibility` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`list_id`),
  KEY `range_id` (`range_id`),
  KEY `priority` (`priority`),
  KEY `visibility` (`visibility`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lit_list_content`
#

DROP TABLE IF EXISTS `lit_list_content`;
CREATE TABLE `lit_list_content` (
  `list_element_id` varchar(32) NOT NULL default '',
  `list_id` varchar(32) NOT NULL default '',
  `catalog_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `mkdate` int(11) NOT NULL default '0',
  `chdate` int(11) NOT NULL default '0',
  `note` varchar(255) default NULL,
  `priority` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`list_element_id`),
  KEY `list_id` (`list_id`),
  KEY `catalog_id` (`catalog_id`),
  KEY `priority` (`priority`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `lock_rules`
#

DROP TABLE IF EXISTS `lock_rules`;
CREATE TABLE `lock_rules` (
  `lock_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `attributes` text NOT NULL,
  PRIMARY KEY  (`lock_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `message`
#

DROP TABLE IF EXISTS `message`;
CREATE TABLE `message` (
  `message_id` varchar(32) NOT NULL default '',
  `chat_id` varchar(32) default NULL,
  `autor_id` varchar(32) NOT NULL default '',
  `message` text NOT NULL,
  `mkdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`message_id`),
  KEY `chat_id` (`chat_id`),
  KEY `autor_id` (`autor_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `message_user`
#

DROP TABLE IF EXISTS `message_user`;
CREATE TABLE `message_user` (
  `user_id` varchar(32) NOT NULL default '',
  `message_id` varchar(32) NOT NULL default '',
  `readed` tinyint(1) NOT NULL default '0',
  `deleted` tinyint(1) NOT NULL default '0',
  `snd_rec` char(3) NOT NULL default '',
  `dont_delete` tinyint(1) default '0',
  `folder` int(5) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`message_id`,`snd_rec`),
  KEY `message_id` (`message_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `news`
#

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `news_id` varchar(32) NOT NULL default '',
  `topic` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `author` varchar(255) NOT NULL default '',
  `date` int(11) NOT NULL default '0',
  `user_id` varchar(32) NOT NULL default '',
  `expire` int(11) NOT NULL default '0',
  PRIMARY KEY  (`news_id`),
  KEY `date` (`date`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `news_range`
#

DROP TABLE IF EXISTS `news_range`;
CREATE TABLE `news_range` (
  `news_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`news_id`,`range_id`),
  KEY `news_id` (`news_id`),
  KEY `range_id` (`range_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_rate`
#

DROP TABLE IF EXISTS `object_rate`;
CREATE TABLE `object_rate` (
  `object_id` varchar(32) NOT NULL default '',
  `rate` int(10) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  KEY `object_id` (`object_id`),
  KEY `rate` (`rate`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_user`
#

DROP TABLE IF EXISTS `object_user`;
CREATE TABLE `object_user` (
  `object_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `flag` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`object_id`,`user_id`,`flag`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `object_views`
#

DROP TABLE IF EXISTS `object_views`;
CREATE TABLE `object_views` (
  `object_id` varchar(32) NOT NULL default '',
  `views` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`object_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `px_topics`
#

DROP TABLE IF EXISTS `px_topics`;
CREATE TABLE `px_topics` (
  `topic_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `root_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `description` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `author` varchar(255) default NULL,
  `author_host` varchar(255) default NULL,
  `Seminar_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`topic_id`),
  KEY `root_id` (`root_id`),
  KEY `Seminar_id` (`Seminar_id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`),
  KEY `mkdate` (`mkdate`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `range_tree`
#

DROP TABLE IF EXISTS `range_tree`;
CREATE TABLE `range_tree` (
  `item_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `level` int(11) NOT NULL default '0',
  `priority` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `studip_object` varchar(10) default NULL,
  `studip_object_id` varchar(32) default NULL,
  PRIMARY KEY  (`item_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_assign`
#

DROP TABLE IF EXISTS `resources_assign`;
CREATE TABLE `resources_assign` (
  `assign_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `assign_user_id` varchar(32) default NULL,
  `user_free_name` varchar(255) default NULL,
  `begin` int(20) NOT NULL default '0',
  `end` int(20) NOT NULL default '0',
  `repeat_end` int(20) default NULL,
  `repeat_quantity` int(2) default NULL,
  `repeat_interval` int(2) default NULL,
  `repeat_month_of_year` int(2) default NULL,
  `repeat_day_of_month` int(2) default NULL,
  `repeat_week_of_month` int(2) default NULL,
  `repeat_day_of_week` int(2) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`assign_id`),
  KEY `resource_id` (`resource_id`),
  KEY `assign_user_id` (`assign_user_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_categories`
#

DROP TABLE IF EXISTS `resources_categories`;
CREATE TABLE `resources_categories` (
  `category_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `system` tinyint(4) NOT NULL default '0',
  `is_room` tinyint(4) NOT NULL default '0',
  `iconnr` int(3) default '1',
  PRIMARY KEY  (`category_id`),
  KEY `is_room` (`is_room`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_categories_properties`
#

DROP TABLE IF EXISTS `resources_categories_properties`;
CREATE TABLE `resources_categories_properties` (
  `category_id` varchar(32) NOT NULL default '',
  `property_id` varchar(32) NOT NULL default '',
  `requestable` tinyint(4) NOT NULL default '0',
  `system` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`property_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_locks`
#

DROP TABLE IF EXISTS `resources_locks`;
CREATE TABLE `resources_locks` (
  `lock_id` varchar(32) NOT NULL default '',
  `lock_begin` int(20) unsigned default NULL,
  `lock_end` int(20) unsigned default NULL,
  PRIMARY KEY  (`lock_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_objects`
#

DROP TABLE IF EXISTS `resources_objects`;
CREATE TABLE `resources_objects` (
  `resource_id` varchar(32) NOT NULL default '',
  `root_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `category_id` varchar(32) NOT NULL default '',
  `owner_id` varchar(32) NOT NULL default '',
  `institut_id` varchar(32) NOT NULL default '',
  `level` varchar(4) default NULL,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `lockable` tinyint(4) default NULL,
  `multiple_assign` tinyint(4) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`resource_id`),
  KEY `institut_id` (`institut_id`),
  KEY `root_id` (`root_id`),
  KEY `parent_id` (`parent_id`),
  KEY `category_id` (`category_id`),
  KEY `owner_id` (`owner_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_objects_properties`
#

DROP TABLE IF EXISTS `resources_objects_properties`;
CREATE TABLE `resources_objects_properties` (
  `resource_id` varchar(32) NOT NULL default '',
  `property_id` varchar(32) NOT NULL default '',
  `state` text NOT NULL,
  PRIMARY KEY  (`resource_id`,`property_id`),
  KEY `property_id` (`property_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_properties`
#

DROP TABLE IF EXISTS `resources_properties`;
CREATE TABLE `resources_properties` (
  `property_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `type` set('bool','text','num','select') NOT NULL default 'bool',
  `options` text NOT NULL,
  `system` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`property_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_requests`
#

DROP TABLE IF EXISTS `resources_requests`;
CREATE TABLE `resources_requests` (
  `request_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '',
  `termin_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `category_id` varchar(32) NOT NULL default '',
  `comment` text,
  `closed` tinyint(3) unsigned default NULL,
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`request_id`),
  KEY `termin_id` (`termin_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  KEY `category_id` (`category_id`),
  KEY `closed` (`closed`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_requests_properties`
#

DROP TABLE IF EXISTS `resources_requests_properties`;
CREATE TABLE `resources_requests_properties` (
  `request_id` varchar(32) NOT NULL default '',
  `property_id` varchar(32) NOT NULL default '',
  `state` text,
  `mkdate` int(20) unsigned default NULL,
  `chdate` int(20) unsigned default NULL,
  PRIMARY KEY  (`request_id`,`property_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_temporary_events`
#

DROP TABLE IF EXISTS `resources_temporary_events`;
CREATE TABLE `resources_temporary_events` (
  `event_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `assign_id` varchar(32) NOT NULL default '',
  `seminar_id` varchar(32) NOT NULL default '',
  `termin_id` varchar(32) NOT NULL default '',
  `begin` int(20) NOT NULL default '0',
  `end` int(20) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`event_id`),
  KEY `resource_id` (`resource_id`),
  KEY `assign_object_id` (`assign_id`)
) TYPE=HEAP;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `resources_user_resources`
#

DROP TABLE IF EXISTS `resources_user_resources`;
CREATE TABLE `resources_user_resources` (
  `user_id` varchar(32) NOT NULL default '',
  `resource_id` varchar(32) NOT NULL default '',
  `perms` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`resource_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `scm`
#

DROP TABLE IF EXISTS `scm`;
CREATE TABLE `scm` (
  `scm_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `tab_name` varchar(20) NOT NULL default 'Info',
  `content` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`scm_id`),
  UNIQUE KEY `range_id` (`range_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `sem_tree`
#

DROP TABLE IF EXISTS `sem_tree`;
CREATE TABLE `sem_tree` (
  `sem_tree_id` varchar(32) NOT NULL default '',
  `parent_id` varchar(32) NOT NULL default '',
  `priority` tinyint(4) NOT NULL default '0',
  `info` text NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `studip_object_id` varchar(32) default NULL,
  PRIMARY KEY  (`sem_tree_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `semester_data`
#

DROP TABLE IF EXISTS `semester_data`;
CREATE TABLE `semester_data` (
  `semester_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `semester_token` varchar(10) NOT NULL default '',
  `beginn` int(20) unsigned default NULL,
  `ende` int(20) unsigned default NULL,
  `vorles_beginn` int(20) unsigned default NULL,
  `vorles_ende` int(20) unsigned default NULL,
  PRIMARY KEY  (`semester_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `semester_holiday`
#

DROP TABLE IF EXISTS `semester_holiday`;
CREATE TABLE `semester_holiday` (
  `holiday_id` varchar(32) NOT NULL default '',
  `semester_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `beginn` int(20) unsigned default NULL,
  `ende` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`holiday_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_inst`
#

DROP TABLE IF EXISTS `seminar_inst`;
CREATE TABLE `seminar_inst` (
  `seminar_id` varchar(32) NOT NULL default '',
  `institut_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`,`institut_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_lernmodul`
#

DROP TABLE IF EXISTS `seminar_lernmodul`;
CREATE TABLE `seminar_lernmodul` (
  `seminar_id` varchar(32) NOT NULL default '',
  `co_inst` bigint(20) NOT NULL default '0',
  `co_id` bigint(20) NOT NULL default '0',
  `status` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`seminar_id`,`co_id`),
  KEY `seminar_id` (`seminar_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_sem_tree`
#

DROP TABLE IF EXISTS `seminar_sem_tree`;
CREATE TABLE `seminar_sem_tree` (
  `seminar_id` varchar(32) NOT NULL default '',
  `sem_tree_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`seminar_id`,`sem_tree_id`),
  KEY `seminar_id` (`seminar_id`),
  KEY `sem_tree_id` (`sem_tree_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_user`
#

DROP TABLE IF EXISTS `seminar_user`;
CREATE TABLE `seminar_user` (
  `Seminar_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `status` varchar(32) NOT NULL default '',
  `gruppe` tinyint(4) NOT NULL default '0',
  `admission_studiengang_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) NOT NULL default '0',
  `comment` tinytext,
  PRIMARY KEY  (`Seminar_id`,`user_id`),
  KEY `user_id` (`user_id`),
  KEY `Seminar_id` (`Seminar_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminar_user_number`
#

DROP TABLE IF EXISTS `seminar_user_number`;
CREATE TABLE `seminar_user_number` (
  `user_id` varchar(32) NOT NULL default '',
  `user_number` int(11) NOT NULL auto_increment,
  `seminar_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`user_number`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `seminare`
#

DROP TABLE IF EXISTS `seminare`;
CREATE TABLE `seminare` (
  `Seminar_id` varchar(32) NOT NULL default '0',
  `VeranstaltungsNummer` varchar(32) default NULL,
  `Institut_id` varchar(32) NOT NULL default '0',
  `Name` varchar(255) NOT NULL default '',
  `Untertitel` varchar(255) default NULL,
  `status` varchar(32) NOT NULL default '1',
  `Beschreibung` text NOT NULL,
  `Ort` varchar(255) default NULL,
  `Sonstiges` text,
  `Passwort` varchar(32) default NULL,
  `Lesezugriff` tinyint(4) NOT NULL default '0',
  `Schreibzugriff` tinyint(4) NOT NULL default '0',
  `start_time` int(20) default '0',
  `duration_time` int(20) default NULL,
  `art` varchar(255) default NULL,
  `teilnehmer` text,
  `vorrausetzungen` text,
  `lernorga` text,
  `leistungsnachweis` text,
  `metadata_dates` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `ects` varchar(32) default NULL,
  `admission_endtime` int(20) default NULL,
  `admission_turnout` int(5) default NULL,
  `admission_binding` tinyint(4) default NULL,
  `admission_type` int(3) NOT NULL default '0',
  `admission_selection_take_place` tinyint(4) default '0',
  `admission_group` varchar(32) default NULL,
  `admission_prelim` tinyint(4) unsigned NOT NULL default '0',
  `admission_prelim_txt` text,
  `admission_starttime` int(20) NOT NULL default '-1',
  `admission_endtime_sem` int(20) NOT NULL default '-1',
  `visible` tinyint(2) unsigned NOT NULL default '1',
  `showscore` tinyint(3) default '0',
  `modules` int(10) unsigned default NULL,
  `lock_rule` varchar(32) NOT NULL default '',
  `user_number` tinyint(4) default NULL,
  PRIMARY KEY  (`Seminar_id`),
  KEY `Institut_id` (`Institut_id`),
  KEY `chdate` (`chdate`),
  KEY `lock_rule` (`lock_rule`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `statusgruppe_user`
#

DROP TABLE IF EXISTS `statusgruppe_user`;
CREATE TABLE `statusgruppe_user` (
  `statusgruppe_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  PRIMARY KEY  (`statusgruppe_id`,`user_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `statusgruppen`
#

DROP TABLE IF EXISTS `statusgruppen`;
CREATE TABLE `statusgruppen` (
  `statusgruppe_id` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `position` int(20) NOT NULL default '0',
  `size` int(20) NOT NULL default '0',
  `selfassign` tinyint(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`statusgruppe_id`),
  KEY `range_id` (`range_id`),
  KEY `position` (`position`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `studiengaenge`
#

DROP TABLE IF EXISTS `studiengaenge`;
CREATE TABLE `studiengaenge` (
  `studiengang_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `beschreibung` text,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  PRIMARY KEY  (`studiengang_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `studip_ilias`
#

DROP TABLE IF EXISTS `studip_ilias`;
CREATE TABLE `studip_ilias` (
  `studip_user_id` varchar(32) NOT NULL default '',
  `ilias_user_id` bigint(20) NOT NULL default '0',
  `is_created` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`studip_user_id`,`ilias_user_id`),
  KEY `is_created` (`is_created`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `support_contract`
#

DROP TABLE IF EXISTS `support_contract`;
CREATE TABLE `support_contract` (
  `contract_id` varchar(32) NOT NULL default '',
  `institut_id` varchar(32) default NULL,
  `range_id` varchar(32) default NULL,
  `given_points` int(20) unsigned NOT NULL default '0',
  `contract_begin` int(20) unsigned NOT NULL default '0',
  `contract_end` int(20) unsigned NOT NULL default '0',
  `mkdate` int(20) unsigned NOT NULL default '0',
  `chdate` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`contract_id`),
  KEY `contract_id` (`contract_id`,`institut_id`,`range_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `support_event`
#

DROP TABLE IF EXISTS `support_event`;
CREATE TABLE `support_event` (
  `event_id` varchar(32) NOT NULL default '',
  `request_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `begin` int(20) unsigned NOT NULL default '0',
  `end` int(20) unsigned NOT NULL default '0',
  `used_points` int(4) unsigned NOT NULL default '0',
  `mkdate` int(20) unsigned NOT NULL default '0',
  `chdate` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`event_id`),
  KEY `event_id` (`event_id`,`user_id`,`mkdate`,`request_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `support_request`
#

DROP TABLE IF EXISTS `support_request`;
CREATE TABLE `support_request` (
  `request_id` varchar(32) NOT NULL default '',
  `contract_id` varchar(32) NOT NULL default '',
  `name` varchar(255) default NULL,
  `date` int(20) unsigned NOT NULL default '0',
  `user_id` varchar(32) NOT NULL default '0',
  `channel` tinyint(3) unsigned default '0',
  `topic_id` varchar(32) NOT NULL default '',
  `mkdate` int(20) unsigned NOT NULL default '0',
  `chdate` int(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`request_id`),
  KEY `contract_id` (`contract_id`,`topic_id`,`user_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `termine`
#

DROP TABLE IF EXISTS `termine`;
CREATE TABLE `termine` (
  `termin_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `autor_id` varchar(32) NOT NULL default '',
  `content` varchar(255) NOT NULL default '',
  `description` text,
  `date` int(20) NOT NULL default '0',
  `end_time` int(20) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `date_typ` tinyint(4) NOT NULL default '0',
  `topic_id` varchar(32) default NULL,
  `expire` int(20) default NULL,
  `repeat` varchar(128) default NULL,
  `color` varchar(20) default NULL,
  `priority` tinyint(4) default NULL,
  `raum` varchar(255) default NULL,
  PRIMARY KEY  (`termin_id`),
  KEY `chdate` (`chdate`),
  KEY `date_typ` (`date_typ`),
  KEY `range_id` (`range_id`),
  KEY `autor_id` (`autor_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `user_info`
#

DROP TABLE IF EXISTS `user_info`;
CREATE TABLE `user_info` (
  `user_id` varchar(32) NOT NULL default '',
  `hobby` varchar(255) NOT NULL default '',
  `lebenslauf` text,
  `publi` text NOT NULL,
  `schwerp` text NOT NULL,
  `Home` varchar(200) NOT NULL default '',
  `privatnr` varchar(32) NOT NULL default '',
  `privadr` varchar(64) NOT NULL default '',
  `score` bigint(20) NOT NULL default '0',
  `geschlecht` tinyint(4) NOT NULL default '0',
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `title_front` varchar(64) NOT NULL default '',
  `title_rear` varchar(64) NOT NULL default '',
  `preferred_language` varchar(6) default NULL,
  `smsforward_copy` tinyint(1) NOT NULL default '1',
  `smsforward_rec` varchar(32) NOT NULL default '',
  `guestbook` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  KEY `score` (`score`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `user_inst`
#

DROP TABLE IF EXISTS `user_inst`;
CREATE TABLE `user_inst` (
  `user_id` varchar(32) NOT NULL default '0',
  `Institut_id` varchar(32) NOT NULL default '0',
  `inst_perms` varchar(255) default '0',
  `sprechzeiten` varchar(200) NOT NULL default '',
  `raum` varchar(32) NOT NULL default '',
  `Telefon` varchar(32) NOT NULL default '',
  `Fax` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`Institut_id`),
  KEY `Institut_id` (`Institut_id`),
  KEY `user_id` (`user_id`),
  KEY `inst_perms` (`inst_perms`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `user_studiengang`
#

DROP TABLE IF EXISTS `user_studiengang`;
CREATE TABLE `user_studiengang` (
  `user_id` varchar(32) NOT NULL default '',
  `studiengang_id` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`user_id`,`studiengang_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `vote`
#

DROP TABLE IF EXISTS `vote`;
CREATE TABLE `vote` (
  `vote_id` varchar(32) NOT NULL default '',
  `author_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `type` enum('vote','test') NOT NULL default 'vote',
  `title` varchar(100) NOT NULL default '',
  `question` text NOT NULL,
  `state` enum('new','active','stopvis','stopinvis') NOT NULL default 'new',
  `startdate` int(20) default NULL,
  `stopdate` int(20) default NULL,
  `timespan` int(20) default NULL,
  `mkdate` int(20) NOT NULL default '0',
  `chdate` int(20) NOT NULL default '0',
  `resultvisibility` enum('ever','delivery','end','never') NOT NULL default 'ever',
  `multiplechoice` tinyint(1) NOT NULL default '0',
  `anonymous` tinyint(1) NOT NULL default '1',
  `changeable` tinyint(1) NOT NULL default '0',
  `co_visibility` tinyint(1) default NULL,
  `namesvisibility` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`vote_id`),
  KEY `range_id` (`range_id`),
  KEY `state` (`state`),
  KEY `startdate` (`startdate`),
  KEY `stopdate` (`stopdate`),
  KEY `resultvisibility` (`resultvisibility`),
  KEY `chdate` (`chdate`),
  KEY `author_id` (`author_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `vote_user`
#

DROP TABLE IF EXISTS `vote_user`;
CREATE TABLE `vote_user` (
  `vote_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `votedate` int(20) default NULL,
  PRIMARY KEY  (`vote_id`,`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `voteanswers`
#

DROP TABLE IF EXISTS `voteanswers`;
CREATE TABLE `voteanswers` (
  `answer_id` varchar(32) NOT NULL default '',
  `vote_id` varchar(32) NOT NULL default '',
  `answer` varchar(255) NOT NULL default '',
  `position` int(11) NOT NULL default '0',
  `counter` int(11) NOT NULL default '0',
  `correct` tinyint(1) default NULL,
  PRIMARY KEY  (`answer_id`),
  KEY `vote_id` (`vote_id`),
  KEY `position` (`position`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `voteanswers_user`
#

DROP TABLE IF EXISTS `voteanswers_user`;
CREATE TABLE `voteanswers_user` (
  `answer_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) NOT NULL default '',
  `votedate` int(20) default NULL,
  PRIMARY KEY  (`answer_id`,`user_id`)
) TYPE=MyISAM PACK_KEYS=1;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `wap_sessions`
#

DROP TABLE IF EXISTS `wap_sessions`;
CREATE TABLE `wap_sessions` (
  `user_id` char(32) NOT NULL default '',
  `session_id` char(32) NOT NULL default '',
  `creation_time` datetime default NULL
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `wiki`
#

DROP TABLE IF EXISTS `wiki`;
CREATE TABLE `wiki` (
  `range_id` varchar(32) NOT NULL default '',
  `user_id` varchar(32) default NULL,
  `keyword` varchar(128) NOT NULL default '',
  `body` text,
  `chdate` int(11) default NULL,
  `version` int(11) NOT NULL default '0',
  PRIMARY KEY  (`range_id`,`keyword`,`version`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `wiki_links`
#

DROP TABLE IF EXISTS `wiki_links`;
CREATE TABLE `wiki_links` (
  `range_id` char(32) NOT NULL default '',
  `from_keyword` char(128) NOT NULL default '',
  `to_keyword` char(128) NOT NULL default '',
  PRIMARY KEY  (`range_id`,`to_keyword`,`from_keyword`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Tabellenstruktur für Tabelle `wiki_locks`
#

DROP TABLE IF EXISTS `wiki_locks`;
CREATE TABLE `wiki_locks` (
  `user_id` varchar(32) NOT NULL default '',
  `range_id` varchar(32) NOT NULL default '',
  `keyword` varchar(128) NOT NULL default '',
  `chdate` int(11) NOT NULL default '0',
  PRIMARY KEY  (`range_id`,`user_id`,`keyword`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) TYPE=MyISAM;
