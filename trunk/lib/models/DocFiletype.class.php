<?php
class DocFiletype extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'doc_filetype';

        $config['has_many']['forbiddenTypes'] = [
            'class_name' => 'DocFileTypeForbidden',
            'on_delete'  => 'delete',
            'on_store'   => 'store',
        ];

        parent::configure($config);
    }
}