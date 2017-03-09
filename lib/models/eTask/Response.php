<?php
namespace eTask;

/**
 * eTask conforming assignment definition
 *
 *  integer id database column
 *  integer assignment_id database column
 *  integer task_id database column
 *  string user_id database column
 *  string response database column
 *  integer state database column
 *  float points database column
 *  string feedback database column
 *  string grader_id database column
 *  string created database column
 *  string changed database column
 *  string options database column
 */
class Response extends \SimpleORMap
{
    use ConfigureTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_responses';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['assignment'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'foreign_key' => 'assignment_id'
        ];

        $config['belongs_to']['task'] = [
            'class_name' => $config['relationTypes']['Task'],
            'foreign_key' => 'task_id'
        ];

        $config['belongs_to']['user'] = [
            'class_name' => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['belongs_to']['grader'] = [
            'class_name' => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['serialized_fields']['response'] = 'JSONArrayObject';
        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }
}
