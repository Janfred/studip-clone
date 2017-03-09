<?php
namespace eTask;

/**
 * eTask conforming assignment attempt definition
 *
 * @property int id database column
 * @property int assignment_id database column
 * @property string user_id database column
 * @property string start database column
 * @property string end database column
 * @property string options database column
 */
class Attempt extends \SimpleORMap
{
    use ConfigureTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_assignment_attempts';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['assignment'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'foreign_key' => 'assignment_id'
        ];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }
}
