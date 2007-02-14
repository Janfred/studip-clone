# mysql migration script
# base version: studip-1.4
# update version: studip-1.5

PLEASE NOTE: Since there exists no migration-tool, please use this script to convert your old database to
a newer version.
Don`t paste this script directly into your SQL-client, because you may have to excute some convert scripts and/or
delete-queries at specified points!

# For detailed informations, please take a look at the update protocol from our installation in Goettingen!
# (Should be located in the same folder)

#
# StEP00069: Nicht abonnieren, sondern nur in Stundenplan eintragen
#
CREATE TABLE `seminar_user_schedule` (
	`range_id` varchar(32) NOT NULL default '',
	`user_id` varchar(32) NOT NULL default '',
	PRIMARY KEY  (`range_id`,`user_id`)
) TYPE=MyISAM;

#
# StEP00075: Dozentenreihenfolge und -bezeichnung
#

ALTER TABLE `seminar_user` ADD `position` INT NOT NULL AFTER `status`;
