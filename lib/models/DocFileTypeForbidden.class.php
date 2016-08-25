<?php
class DocFileTypeForbidden extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'doc_filetype_forbidden';
        
        $config['belongs_to']['userConfig'] = [
            'class_name'  => 'DocUsergroupConfig',
            'foreign_key' => 'usergroup',
        ];
        $config['belongs_to']['filetype'] = [
            'class_name'  => 'DocFiletype',
            'foreign_key' => 'dateityp_id',
        ];

        parent::configure($config);
    }
}
