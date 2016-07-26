<?php

class AddActivities extends Migration
{

    public function description()
    {
        return 'add table for activities';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec("CREATE TABLE `activities` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `object_id` varchar(255) NOT NULL,
            `context` enum('system','course','institute','user') NOT NULL,
            `context_id` varchar(32) NOT NULL,
            `provider` varchar(255) NOT NULL,
            `actor_type` varchar(255) NOT NULL,
            `actor_id` varchar(255) NOT NULL,
            `verb` enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','sent','voided') NOT NULL DEFAULT 'experienced',
            `content` text NULL,
            `object_type` varchar(255) NOT NULL,
            `mkdate` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `context_id` (`context_id`),
            KEY `mkdate` (`mkdate`)
        ) ENGINE = InnoDB ROW_FORMAT=DYNAMIC");
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("DROP TABLE `activities`");
    }
}
