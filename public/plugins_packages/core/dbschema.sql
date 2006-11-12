CREATE TABLE `roles` (
  `roleid` int(10) unsigned NOT NULL auto_increment,
  `rolename` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`roleid`)
);
CREATE TABLE `roles_user` (
  `roleid` int(10) unsigned NOT NULL default '0',
  `userid` char(32) NOT NULL default '',
  PRIMARY KEY  (`roleid`,`userid`)
);