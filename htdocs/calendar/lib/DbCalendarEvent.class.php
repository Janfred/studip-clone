<?

require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/driver/$CALENDAR_DRIVER/event_driver.inc.php");

class DbCalendarEvent extends CalendarEvent{

	function DbCalendarEvent($start = "", $end = "", $txt = "", $exp = "", $cat = "", $prio = 1, $loc = "", $id = "", $type = -2){
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
		else
			CalendarEvent::CalendarEvent($start, $end, $txt, $exp, $cat, $prio, $loc, $id, $type);
	}
	
	// public
	function getDescription(){
		if(is_int($this->desc) && $description = event_get_description($this->id))
			$this->desc = $description;
		return $this->desc;
	}
	
	// Termin in Datenbank speichern
	// public
	function save(){
		event_save($this);
	}
	
	// Termin aus Datenbank loeschen
	// public
	function delete(){
		return event_delete($this->id, $this->user_id);
	}
	
	// Termin aus Datenbank holen
	// public
	function restore($id){
		return event_restore($id, $this);
	}
	
}

?>
