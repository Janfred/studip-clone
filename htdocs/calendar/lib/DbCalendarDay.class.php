<?

/*
DbCalendarDay.class.php - 0.8.20020709
Klassen fuer Persoenlichen Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthienel@web.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

//****************************************************************************

require_once($ABSOLUTE_PATH_STUDIP . "config.inc.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarDay.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/SeminarEvent.class.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/calendar_misc_func.inc.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/driver/$CALENDAR_DRIVER/day_driver.inc.php");

class DbCalendarDay extends CalendarDay{

	var $app;         	// Termine (Object[])
	var $app_del;       // Termine, die geloescht werden (Object[])
	var $arr_pntr;    	// "private" function getTermin
	var $user_id;       // User-ID aus PphLib (String)
	
	// Konstruktor
	function DbCalendarDay($tmstamp){
		global $user;
		$this->user_id = $user->id;
		CalendarDay::CalendarDay($tmstamp);
		$this->restore();
		$this->sort();
		$this->arr_pntr = 0;
	}
	
	// Anzahl von Terminen innerhalb eines bestimmten Zeitabschnitts
	// default one day
	// public
	function numberOfEvents($start = 0, $end = 86400){
		$i = 0;
		$count = 0;
		while($aterm = $this->app[$i]){
			if($aterm->getStart() >= $this->getStart() + $start && $aterm->getStart() <= $this->getStart() + $end)
				$count++;
			$i++;
		}
		return $count - 1;
	}
	
	// public
	function numberOfSimultaneousApps($term){
		$i = 0;
		$count = 0;
		while($aterm = $this->app[$i]){
			if($aterm->getStart() >= $term->getStart() && $aterm->getStart() < $term->getEnd())
				$count++;
			$i++;
		}
		return ($count);
	}
	
	// Termin hinzufuegen
	// Der Termin wird gleich richtig einsortiert
	// public
	function addEvent($term){
		$this->app[] = $term;
		$this->sort();
	//	return TRUE;
	}
	
	// Termin loeschen
	// public
	function delEvent($id){
		for($i = 0;$i < sizeof($this->app);$i++)
			if($id != $this->app[$i]->getId())
				$app_bck[] = $this->app[$i];
			else
				$this->app_del[] = $this->app[$i];
				
		if(sizeof($app_bck) == sizeof($this->app))
			return FALSE;
		
		$this->app = $app_bck;
		return TRUE;
	}
	
	// ersetzt vorhandenen Termin mit uebergebenen Termin, wenn ID gleich
	// public
	function replaceEvent($term){
		for($i = 0;$i < sizeof($this->app);$i++)
			if($this->app[$i]->getId() == $term->getId()){
				$this->app[$i] = $term;
				$this->sort();
				return TRUE;
			}
		return FALSE;
	}
	
	// Abrufen der Termine innerhalb eines best. Zeitraums
	// default 1 hour
	// public
	function nextEvent($start = -1, $step = 3600){
		if($start < 0)
			$start = $this->start;
		while($this->arr_pntr < sizeof($this->app)){
			if($this->app[$this->arr_pntr]->getStart() >= $start && $this->app[$this->arr_pntr]->getStart() < $start + $step)
				return $this->app[$this->arr_pntr++];
			$this->arr_pntr++;
		}
		$this->arr_pntr = 0;
		return FALSE;
	}
	
	// Termine in Datenbank speichern.
	// public
	function save(){
		
		day_save($this);
		
	}
	
	// public
	function existEvent(){
		if(sizeof($this->app) > 0)
			return TRUE;
		return FALSE;
	}

	// Wiederholungstermine, die in der Vergangenheit angelegt wurden belegen in
	// app[] die ersten Positionen und werden hier in den "Tagesablauf" einsortiert
	// Termine, die sich ueber die Tagesgrenzen erstrecken, muessen anhand ihrer
	// "absoluten" Anfangszeit einsortiert werden.
	// private
	function sort(){
		if(sizeof($this->app))
			usort($this->app, "cmp_list");
	}					

	// Termine aus Datenbank holen
	// private
	function restore(){
		day_restore($this);
	}
	
	// public
	function bindSeminarEvents($sem_id = ""){
		if($sem_id == "")
			$query = sprintf("SELECT t.*, s.Name "
						 . "FROM termine t LEFT JOIN seminar_user su ON su.Seminar_id=t.range_id "
						 . "LEFT JOIN seminare s USING(Seminar_id) WHERE "
			       . "user_id = '%s' AND date_typ!=-1 AND date_typ!=-2 AND date BETWEEN %s AND %s"
						 , $this->user_id, $this->getStart(), $this->getEnd());
		else if($sem_id != ""){
			if(is_array($sem_id))
				$sem_id = implode("','", $sem_id);
			$query = sprintf("SELECT t.*, s.Name "
						 . "FROM termine t LEFT JOIN seminar_user su ON su.Seminar_id=t.range_id "
						 . "LEFT JOIN seminare s USING(Seminar_id) WHERE "
			       . "user_id = '%s' AND range_id IN ('%s') AND date_typ!=-1 "
						 . "AND date_typ!=-2 AND date BETWEEN %s AND %s"
						 , $this->user_id, $sem_id, $this->getStart(), $this->getEnd());
		}
		else
			return FALSE;
			
		$db =& new DB_Seminar;	
		$db->query($query);
		
		if($db->num_rows() != 0){
			while($db->next_record()){
				$app =& new SeminarEvent($db->f("date"), $db->f("end_time"), $db->f("content"),
				              $db->f("date_typ"), $db->f("raum"), $db->f("termin_id"), $db->f("range_id"),
											$db->f("mkdate"), $db->f("chdate"));
				$app->setSemName($db->f("Name"));
				$this->app[] = $app;
			}
			$this->sort();
			return TRUE;
		}
		return FALSE;
	}
	
	// public
	function serialisiere(){
		$size_app = sizeof($this->app);
		$size_app_del = sizeof($this->app_del);
		
		for($i = 0;$i < $size_app;$i++)
			$ser_app .= 'i:'.$i.';'.$this->app[$i]->serialisiere();
		for($i = 0;$i < $size_app_del;$i++)
			$ser_app_del .= 'i:'.$i.';'.$this->app_del[$i]->serialisiere();
		
		$pattern[0] = "/s:3:\"app\";a:".$size_app.":\{\}/";
		$pattern[1] = "/s:7:\"app_del\";a:".$size_app_del.":\{\}/";
		
		$replace[0] = "s:3:\"app\";a:".$size_app.":{".$ser_app."}";
		$replace[1] = "s:7:\"app_del\";a:".$size_app_del.":{".$ser_app_del."}";
		
		$serialized = preg_replace($pattern, $replace, serialize($this));
		
		return $serialized;
	}

}

// class Day

?>
