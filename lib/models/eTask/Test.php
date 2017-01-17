<?php
namespace eTask;

/**
 * eTask conforming test definition
 * integer id database column
 * string title database column
 * string description database column
 * string user_id database column
 * string created database column
 * string changed database column
 * string options database column
 */
class Test extends \SimpleORMap
{
    use ConfigureTrait;
    use CreatedChangedTrait;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_tests';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['additional_fields']['tasks']['get'] = 'getTasks';

        $config['has_many']['testtasks'] = [
            'class_name' => $config['relationTypes']['TestTask'],
            'assoc_foreign_key' => 'test_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['belongs_to']['owner'] = [
            'class_name'  => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['has_many']['assignments'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'assoc_foreign_key' => 'test_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    public function getTasks()
    {
        $klass = $this->relationTypes['Task'];

        return \SimpleORMapCollection::createFromArray(
            $klass::findThru(
                $this->id,
                [
                    'thru_table' => 'etask_test_tasks',
                    'thru_key' => 'test_id',
                    'thru_assoc_key' => 'task_id',
                    'assoc_foreign_key' => 'id',
                    'order_by' => 'ORDER BY etask_test_tasks.position ASC'
                ]
            )
        );
    }

    public function countTasks()
    {
        $klass = $this->relationTypes['TestTask'];

        return $klass::countBySql('test_id = ?', [ $this->id ]);
    }

    public function addTask($task)
    {
        $klass = $this->relationTypes['TestTask'];

        $testTask = $klass::create(
            [
                'test_id' => $this->id,
                'task_id' => $task->id,
                'position' => $this->countTasks() + 1,
                'options' => []
            ]
        );

        $this->resetRelation('testtasks');
        return $testTask;
    }
}
