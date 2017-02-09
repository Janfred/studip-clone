<?php
class ConvertCronjobLogs extends Migration
{
    public function up()
    {
        CronjobLog::findAndMapBySQL(function ($entry) {
            $entry->exception = unserialize($entry->exception) ?: null;
            $entry->store();
        }, "exception RLIKE '^(N;|O:)'");
    }

    public function down()
    {
        // Not neccessary
    }
}
