<?php
namespace eTask;

/**
 * eTask conforming test task relation
 *
 * integer test_id database column
 * integer task_id database column
 * integer position database column
 * float points database column
 * string options database column
 */
class TestTask extends \SimpleORMap
{
    use ConfigureTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_test_tasks';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['test'] = array(
            'class_name' => $config['relationTypes']['Test'],
            'foreign_key' => 'test_id');

        $config['belongs_to']['task'] = array(
            'class_name' => $config['relationTypes']['Task'],
            'foreign_key' => 'task_id');

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }
}
