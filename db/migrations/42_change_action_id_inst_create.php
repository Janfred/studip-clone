<?
class ChangeActionIdInstCreate extends Migration
{
    function description ()
    {
        return 'Corrects action_id for INST_CREATE log action and log events';
    }

    function up ()
    {
        $db = DBManager::get();

	// fixes #448, cf. http://develop.studip.de/trac/ticket/448
        $db->exec("UPDATE log_actions SET action_id='0d87c25b624b16fb9b8cdaf9f4e96e53' 
                                    WHERE action_id='59f3f38c905fded82bbfdf4f04c16729'");
        $db->exec("UPDATE log_events SET action_id='0d87c25b624b16fb9b8cdaf9f4e96e53' 
                                   WHERE action_id='59f3f38c905fded82bbfdf4f04c16729'");
    }

}
?>
