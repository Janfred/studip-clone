<?php

class TextmarkupDatafield extends Migration
{
    function description()
    {
        return 'add textmarkup type to datafields';
    }

    function up()
    {
        DBManager::get()->exec("ALTER TABLE datafields CHANGE type
                                type ENUM('bool','textline','textarea','textmarkup','selectbox','date','time','email','phone','radio','combo','link','selectboxmultiple')
                                NOT NULL DEFAULT 'textline'");
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("ALTER TABLE datafields CHANGE type
                                type ENUM('bool','textline','textarea','selectbox','date','time','email','phone','radio','combo','link','selectboxmultiple')
                                NOT NULL DEFAULT 'textline'");
        SimpleORMap::expireTableScheme();
    }
}
