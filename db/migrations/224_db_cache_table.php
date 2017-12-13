<?php

class DbCacheTable extends Migration
{
    function description()
    {
        return 'add database table for simple cache';
    }

    function up()
    {
        $db = DBManager::get();

        $db->exec('CREATE TABLE cache (
                   cache_key varchar(255) COLLATE latin1_bin NOT NULL,
                   content MEDIUMBLOB NOT NULL,
                   expires INT(11) NOT NULL,
                   PRIMARY KEY (cache_key)
                   ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC');

        StudipCacheFactory::getCache()->flush();
    }

    function down()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE cache');
    }
}
