<?php
namespace eTask;

/**
 * eTask SORM class relationship configuration trait
 */
trait ConfigureTrait
{
    protected $relationTypes = [];

    private static function configureClassNames($config = [])
    {
        $defaultTypes = [
            'Assignment' => '\\eTask\\Assignment',
            'AssignmentRange' => '\\eTask\\AssignmentRange',
            'Attempt' => '\\eTask\\Attempt',
            'Response' => '\\eTask\\Response',
            'Task' => '\\eTask\\Task',
            'Test' => '\\eTask\\Test',
            'TestTask' => '\\eTask\\TestTask'
        ];

        $types = [];

        if (!isset($config['relationTypes'])) {
            $types = $defaultTypes;
        } else {
            foreach ($defaultTypes as $key => $classname) {
                $types[$key] = $config['relationTypes'][$key] ?: $classname;
            }
        }

        return $types;
    }
}
