<?
// Wrapper class for driver functions in calendar/lib/driver/

require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/driver/$CALENDAR_DRIVER/event_driver.inc.php");

class DbCalendarEvent extends CalendarEvent {

	// ($start = "", $end = "", $txt = "", $exp = "", $cat = "", $prio = 1, $loc = "", $id = "", $type = -2)
	
	function DbCalendarEvent () {
		
		switch(func_num_args()){
			// get event out of database...
			case 1:
				global $user, $PERS_TERMIN_KAT, $TERMIN_TYP;
				$this->user_id = $user->id;
			
				$id = func_get_arg(0);
				$this->restore($id);
			
				// nur persoenliche Termin haben per default eine Farbe
				// fuer Veranstaltungstermine muss eine Farbe explizit mit setColor() gesetzt werden
				if($this->type == -1 || $this->type == -2)
					$this->col = $PERS_TERMIN_KAT[$this->cat]["color"];
				break;
			case 3:
			case 6:
				$param_array = func_get_args();
				call_user_func_array(array(&$this, "Event"), $param_array);
				break;
			case 8:
			case 9:
				$param_array = func_get_args();
				call_user_func_array(array(&$this, "CalendarEvent"), $param_array);
				break;
			default:
				die("Wrong parameter (".func_num_args().") count for DbCalendarEvent()");
		}
				
		/*
		// get event out of database...
		if(func_num_args() == 1){
			
			global $user, $PERS_TERMIN_KAT, $TERMIN_TYP;
			$this->user_id = $user->id;
			
			$id = func_get_arg(0);
			$this->restore($id);
			
			// nur persoenliche Termin haben per default eine Farbe
			// fuer Veranstaltungstermine muss eine Farbe explizit mit setColor() gesetzt werden
			if($this->type == -1 || $this->type == -2)
				$this->col = $PERS_TERMIN_KAT[$this->cat]["color"];
		}
		else{
			// ...or it is a new event
			CalendarEvent::CalendarEvent($start, $end, $txt, $exp, $cat, $prio, $loc, $id, $type);
			$mkdate = time();
			$chdate = $mkdate;
		}*/
	}
	
	// public
	function getDescription (){
		if($this->desc == null && $description = event_get_description($this->id))
			$this->desc = $description;
		return $this->desc;
	}
	
	// Store event in database
	// public
	function save (){
		event_save($this);
	}
	
	// delete event in database
	// public
	function delete (){
		return event_delete($this->id, $this->user_id);
	}
	
	// get event out of database
	// public
	function restore ($id){
		if(! event_restore($id, $this))
			die("This event (ID='$id') can not be restored!");
	}
	
}

?>
