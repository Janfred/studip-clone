<?php

class QuestionnaireAssignment extends SimpleORMap
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'questionnaire_assignments';
        $config['belongs_to']['questionnaire'] = array(
            'class_name'        => 'Questionnaire',
            'foreign_key'       => 'questionnaire_id',
            'assoc_foreign_key' => 'questionnaire_id',
        );

        parent::configure($config);
    }

    public static function findBySeminarAndQuestionnaire($seminar_id, $questionnaire_id)
    {
        return self::findOneBySQL("questionnaire_id = ? AND range_id = ? AND range_type = 'course'", array($questionnaire_id, $seminar_id));
    }
}