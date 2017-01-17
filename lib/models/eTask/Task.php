<?php
namespace eTask;

/**
 * eTask conforming task definition
 *
 * integer id database column
 * string type database column
 * string title database column
 * string description database column
 * string task database column
 * string user_id database column
 * string created database column
 * string changed database column
 * string options database column
 */
class Task extends \SimpleORMap
{
    use ConfigureTrait;
    use CreatedChangedTrait;

    /**
     * {@inheritdoc}
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->registerCallback('before_store', 'cbUpdateChanged');
    }

    /**
     * Updates the field `changed` on change
     * @return true
     */
    protected function cbUpdateChanged()
    {
        if ($this->isDirty()) {
            $this->changed = date('c');
        }
        return true;
    }

    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_tasks';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['owner'] = [
            'class_name' => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['additional_fields']['tests']['get'] = 'getTests';

        $config['has_many']['test_tasks'] = [
            'class_name' => $config['relationTypes']['TestTask'],
            'assoc_foreign_key' => 'task_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['responses'] = [
            'class_name' => $config['relationTypes']['Response'],
            'assoc_foreign_key' => 'task_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['task'] = 'JSONArrayObject';
        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    public function getTests()
    {
        $klass = $this->relationTypes['Test'];

        return \SimpleORMapCollection::createFromArray(
            $klass::findThru(
                $this->id,
                [
                    'thru_table' => 'etask_test_tasks',
                    'thru_key' => 'task_id',
                    'thru_assoc_key' => 'test_id',
                    'assoc_foreign_key' => 'id',
                    'order_by' => 'ORDER BY etask_tests.changed ASC'
                ]
            )
        );
    }
}
