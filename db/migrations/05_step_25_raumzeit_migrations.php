<?
class Step25RaumzeitMigrations extends DBMigration
{
    function description ()
    {
        return 'modify db schema and convert dates for StEP00025; see logfile in $TMP_PATH';
    }

    function up ()
    {
        // open log file
        $logfile_handle = fopen( $GLOBALS["TMP_PATH"] ."/Stud.IP_date_conversion.log", "ab");
        if(!$logfile_handle) {
            throw new Exception ("Can't open logfile ".$GLOBALS["TMP_PATH"]."/Stud.IP_date_conversion.log");
        }
        
        $this->write( get_class($this)." - Creating db schema...");
/*        
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `themen` (
              `issue_id` varchar(32) NOT NULL default '',
              `seminar_id` varchar(32) NOT NULL default '',
              `author_id` varchar(32) NOT NULL default '',
              `title` varchar(255) NOT NULL default '',
              `description` mediumtext NOT NULL,
              `priority` int(11) NOT NULL default '0',
              `mkdate` int(11) NOT NULL default '0',
              `chdate` int(11) NOT NULL default '0',
              PRIMARY KEY (`issue_id`)
            );
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `ex_termine` (
              `termin_id` varchar(32) NOT NULL default '',
              `range_id` varchar(32) NOT NULL default '',
              `autor_id` varchar(32) NOT NULL default '',
              `content` varchar(255) NOT NULL default '',
              `description` text,
              `date` int(20) NOT NULL default '0',
              `end_time` int(20) NOT NULL default '0',
              `mkdate` int(20) NOT NULL default '0',
              `chdate` int(20) NOT NULL default '0',
              `date_typ` tinyint(4) NOT NULL default '0',
              `topic_id` varchar(32) default NULL,
              `expire` int(20) default NULL,
              `repeat` varchar(128) default NULL,
              `color` varchar(20) default NULL,
              `priority` tinyint(4) default NULL,
              `raum` varchar(255) default NULL,
              `metadate_id` varchar(32) default NULL,
              `resource_id` varchar(32) NOT NULL default '',
              PRIMARY KEY  (`termin_id`),
              KEY `range_id` (`range_id`),
              KEY `autor_id` (`autor_id`)
            ) TYPE=MyISAM PACK_KEYS=1;
        ");
            
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `themen_termine` (
              `issue_id` varchar(32) NOT NULL default '',
              `termin_id` varchar(32) NOT NULL default '',
              PRIMARY KEY  (`issue_id`,`termin_id`)
            ) TYPE=MyISAM;
        ");
            
        $this->db->query("
            ALTER TABLE `termine` ADD `metadate_id` VARCHAR( 32 );
        ");

        $this->db->query("
            ALTER TABLE `termine` ADD INDEX ( `metadate_id` );
        ");
            
        $this->db->query("
            ALTER TABLE `resources_requests` ADD `reply_comment` TEXT AFTER `comment`;        
        ");
        
        $this->db->query("
            INSERT INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES (
            '93da66ca9e2d17df5bc61bd56406add7' , '', 'RESOURCES_ROOM_REQUEST_DEFAULT_ACTION', 'NO_ROOM_INFO_ACTION', '1', 'string', 'global', '', '0', '0', '0', 'Designates the pre-selected action for the room request dialog', 'Valid values are: NO_ROOM_INFO_ACTION, ROOM_REQUEST_ACTION, BOOKING_OF_ROOM_ACTION, FREETEXT_ROOM_ACTION', ''
            );
        ");
*/        
        $this->write( get_class($this)."Finished with creating db schema.");
        $this->write( get_class($this)."Starting data conversion... - this may take a very long time");

        // create secret password for subroutine authentication
        $secret_password = md5(uniqid("ditnuc6532ktn"));

        // signal start of conversion and set 'secret' 
        $this->db->query("
            REPLACE INTO `config` ( `config_id` , `parent_id` , `field` , `value` , `is_default` , `type` , `range` , `section` , `position` , `mkdate` , `chdate` , `description` , `comment` , `message_template` )
            VALUES (
            'migration5' , '', 'MIGRATION_5_TEMPORARY_SECRET', '".$secret_password."', '0', 'string', 'global', '', '0', '0', '0', 'Temporary secret string for migragtion 5', 'Temporary entry of migration 5', ''
            );
        ");
        
        $this->convert_data( $logfile_handle, $secret_password);       

        // remove signal
        $this->db->query("
            DELETE FROM `config` WHERE config_id = 'migration5';
        ");

        $this->write( get_class($this)."Finished with data conversion...");
    }
    
    
    function convert_data( $logfile_handle, $secret_password){
        
        // data conversion code:
                
        // run until really everything is done...
        set_time_limit(0); 
        
        // we need enough memory
        ini_set( "memory_limit", "256M");
               
        // set URL of subroutine file
        // (needed because of PHP memory problems, if the conversion would be done in one step)
        $CONVERSION_SUBROUTINE_URL = $GLOBALS["ABSOLUTE_URI_STUDIP"] ."raumzeit_conversion_subroutine.php";
        
        // define step size (number of rows) for subroutine proccessing
        $STEP_SIZE= 300;
        
                
        // include business logic
        require_once('lib/classes/Seminar.class.php');
        require_once('lib/resources/lib/VeranstaltungResourcesAssign.class.php');

        
        
        // lets go...
        fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") Starting conversion of imported seminar dates.\n");
        
        
        // STEP 1:
        //      convert the title of dates (="content") to real themes
        //      converts all dates, that don't have content==''
        fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") Step 1: Converting the title of dates to real themes:\n");
        
        // create database connectors
        $db  = new DB_Seminar();
        $db2 = new DB_Seminar();
        
        // get all dates (=termine) with content!=''
        $db->query("SELECT termine.* FROM seminare LEFT JOIN termine ON (seminare.Seminar_id = termine.range_id) WHERE (content != '' OR description != '')");
        
        $counter = 0;
        
        // create new theme for each date
        while ($db->next_record()) {
            $counter++;
            $new_issue_id = md5(uniqid("Issue"));
                $db2->query("INSERT INTO themen_termine (issue_id, termin_id) VALUES ('$new_issue_id', '".$db->f('termin_id')."')");
                $db2->query("INSERT INTO themen (issue_id, seminar_id, author_id, title, description, mkdate, chdate) VALUES ('$new_issue_id', '".$db->f('range_id')."', '".$db->f('author_id')."', '".mysql_escape_string($db->f('content'))."', '".mysql_escape_string($db->f('description'))."', '".$db->f('mkdate')."', '".$db->f('chdate')."')");
                $db2->query("UPDATE termine SET content = '', description = '' WHERE termin_id = '".$db->f('termin_id')."'");
                $db2->query("UPDATE folder SET range_id = '$new_issue_id' WHERE range_id = '".$db->f('termin_id')."'"); 
                if($db->f('topic_id')){ 
                    $db2->query("UPDATE px_topics SET topic_id = '$new_issue_id' WHERE topic_id = '".$db->f('topic_id')."'"); 
                    $db2->query("UPDATE px_topics SET root_id = '$new_issue_id'  WHERE root_id = '".$db->f('topic_id')."'"); 
                    $db2->query("UPDATE px_topics SET parent_id = '$new_issue_id'  WHERE parent_id = '".$db->f('topic_id')."'"); 
                } 
            fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") converting termin_id='".$db->f('termin_id')."', added theme_id='".$new_issue_id."'\n");
            flush();
        }
        
        fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") Finished Step 1. Converted $counter dates.\n");
        
        // END OF STEP 1
        
        
        // STEP 2:
        //      create single dates for all regular dates (turnus_data in metadata_dates)
        fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") Step 2: Creating single dates for all regular dates:\n");
        
        // reset counter
        $counter = 0;
        
        // set number of record to start with
        $start_at = 0;
        
        do {
            // call the conversion subroutine with number of rows that should get processed           

            // create cURL-Handle
            $ch = curl_init();

            // set url and other option
            curl_setopt($ch, CURLOPT_URL, $CONVERSION_SUBROUTINE_URL ."?step_size=".$STEP_SIZE."&start_at=".$start_at."&secret=".$secret_password);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

            // make the call to the url
            $response = curl_exec ($ch);

            // close cURL-Handle und gebe die Systemresourcen frei
            curl_close($ch);


            // success ?
            if( $response == FALSE ){
                fwrite($logfile_handle, "Error while executing subroutine. Stopping.\n");
                throw new Exception("Error while executing subroutine.");
            }
            // some not quite nice error handling:
            if( substr($response,0,5) == "ERROR" ){
                // write output to logfile
                fwrite( $logfile_handle, $response);
                fwrite($logfile_handle, "Error while executing subroutine. Stopping.\n");
                throw new Exception("Error while executing subroutine.". $response);
            }

            // get last line (holds the number of converted rows)
            $begin_of_last_line = strrpos( $response, "\n")+1;
            $numberOfConvertedRows = substr($response, $begin_of_last_line, strlen($response)-$begin_of_last_line);

            // cutoff last line
            $response = substr($response, 0, $begin_of_last_line);
            
            // write output to logfile
            fwrite( $logfile_handle, $response);
        
            // count total amount of converted seminars
            $counter += $numberOfConvertedRows;

            // step to next record package            
            $start_at += $STEP_SIZE;
            
        } while( $numberOfConvertedRows != 0);
        
        fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") Finished Step 2. Converted $counter seminars.\n");
        
        fwrite($logfile_handle, "(". date("Y-m-d H:i:s T") .") Conversion finished.");
        
        // close logfile
        fclose($logfile_handle);        
    }
}
?>
