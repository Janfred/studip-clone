<?php
//try {
    $output .= "";
    
    // run until really everything is done...
    set_time_limit(0);
    
    // we need enough memory
    ini_set( "memory_limit", "256M");
       
    // include business logic classes
    require_once('lib/classes/Seminar.class.php');
    require_once('lib/resources/lib/VeranstaltungResourcesAssign.class.php');
    
    $db = new DB_Seminar();
    $db2 = new DB_Seminar();
    

    // check call permission -> compare "secret" parameter with temporary entry in the DB

    if($db->query("SELECT value FROM config WHERE config_id = 'migration5'"))
        $db->next_record();
    else
        die("Error accessing database in raumzeit_conversion_subroutine script.");
    
    // get parameters:

   // if (!$_REQUEST['secret'] || $_REQUEST['secret'] != $db->f("value")) {
    //    die("Invalid access to raumzeit_conversion_subroutine script.");
    //}    
    
    // number of records to be converted this time
    if (!$_REQUEST['step_size']) {
        // default ste size
        $step_size = 1;
    } else {
        $step_size = $_REQUEST['step_size'];
    }
    
    // set record number where to start/continue the conversion
    if (!$_REQUEST['start_at']) {
        $start_at = 0;
    } else {
        $start_at = $_REQUEST['start_at'];    
    }
    $output .= "(". date("Y-m-d H:i:s T") .") Converting up to $step_size seminars beninning at record $start_at in this substep.\n";
                   
    
    // set counter for this round...
    $seminar_counter = 0;
    
    // prevents the caching of assign objects in AssignObject.class.php (?)
    $GLOBALS['FORCE_THROW_AWAY'] = TRUE;
    
    // enable 
    // - raumbuchungen, die auf ein metadate gebucht sind werden auf einzeltermine verschoben
    // - ressources assign: termine mit raum verknüpft
    // creates 
    $GLOBALS['CONVERT_SINGLE_DATES'] = TRUE;
        
    
    //if($convert_all_data)
        // read a bunch of seminares
     //   $db->query("SELECT Seminar_id, Name FROM seminare LIMIT $start_at, $step_size");
    //else    
    //    // read a bunch of seminares where the change date is zero (chdate funtions as a marker)
        $db->query("SELECT Seminar_id, Name FROM seminare WHERE chdate = 0 LIMIT 0, $step_size");
        
    
    // get number of rows
    $number_of_rows = $db->num_rows();
    
    // initialize counter
    $seminar_counter = 0;
    
    // loop through all found seminars
    while ($db->next_record()) {
    
            // get seminar ID
            $seminar_id = $db->f('Seminar_id');
            
            $output .= "(". date("Y-m-d H:i:s T") .") Converting Seminar ID='$seminar_id', Name '".$db->f('Name')."'\n";
            unset($sem);
            
            // create new seminar object
            $sem = new Seminar( $seminar_id);
            
            // loop through every regular date
            foreach ($sem->metadate->cycles as $key => $val) {
    
                // assign ressources, if ressources are used
                if ($val->resource_id) {
                    $veranst_assign = new VeranstaltungResourcesAssign($sem->getId());
                    $veranst_assign->deleteAssignedRooms();
                }
                
                // this method creates corresponding single dates for regular dates, if they are not present 
                $sem->getSingleDatesForCycle($key);
                
                $val->resource_id = '';
            }
            
            // update the seminar object (modifies the chdate)
            $sem->store();
            
            $seminar_counter++;        
    }
    
    
    $output .= "Number of converted seminars in this substep: $seminar_counter\n"; // return the number of convertet dates (last output is return value)
    $output .= "$seminar_counter"; // return the number of convertet dates (last output is return value)

    echo $output;
/*
} catch (Exception $e) {
    echo "ERROR: ". $e->__toString();
}
*/
?>