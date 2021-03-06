<?php

class ShowAdressees extends Migration
{
    private $options = array(
        array(
            'name'        => 'SHOW_ADRESSEES_LIMIT',
            'description' => 'Ab wievielen Adressaten dürfen diese aus datenschutzgründen nicht mehr angezeigt werden in einer empfangenen Nachricht?',
            'section'     => 'global',
            'type'        => 'string',
            'value'       => '20'
        )
    );

    public function description()
    {
        return 'Lets Stud.IP display the adressees of a Stud.IP-message.';
    }

    public function up()
    {
        foreach ($this->options as $option) {
            Config::get()->create($option['name'], $option);
        }

        DBManager::get()->exec("
            ALTER TABLE message
            ADD COLUMN `show_adressees` tinyint(4) NOT NULL DEFAULT '0' AFTER `message`
        ");
    }

    public function down()
    {
        $db = DBManager::get();
        $stmt = $db->prepare("DELETE FROM config WHERE field = :name");

        foreach ($this->options as $option) {
            $stmt->execute(array('name' => $option['name']));
        }
        DBManager::get()->exec("
            ALTER TABLE message
            DROP COLUMN `show_adressees`
        ");
    }
}
