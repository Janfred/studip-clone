<?

/*
calendarEvent.class.php - 0.8.20020409a
Klassen fuer Persoenlichen Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthien@gmx.de>

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

class CalendarEvent{

	// Datenfelder
	
	var $id;    	    // termin_id (String)
	var $txt;         // Terminkurzbeschreibung (String)
	var $start;       // Anfangszeit des Termins als Unix-Timestamp (int)
	var $end;         // Endzeit des des Termins als Unix-Timestamp (int)
	var $rep;         // Wiederholungsanweisung des Termins (String).
	var $exp;         // Wann verliert Termin Gueltigkeit? Unix-Timestamp (int)
	var $col = "";    // Farbe (STRING)
	var $cat = 1;     // Kategorie (int)
	var $dev = FALSE; // TRUE wenn Tagestermin (boolean)
	var $ts;          // der "genormte" Timestamp
	var $prio;        // Prioritaet (int)
	var $loc;         // Ort (String)
	var $desc;        // Terminbeschreibung (String)
	var $chng_flag;   // Termin geaendert ? (boolean)
	var $user_id;     // User-ID aus PphLib (String)
	var $type = -2;    // Termintyp (int) siehe config.inc.php (privater Termin als Standard)
	var $sem_id = ""; // Seminar-ID, wenn es sich um einen Seminartermin handelt (String)
	var $mkd = -1;    // Erstellungsdatum (int) wird ueberschrieben, falls Termin aus DB geholt wird
	
	// Konstruktor
	function CalendarEvent($start = "", $end = "", $txt = "", $exp = "", $cat = "", $prio = 1, $loc = "", $id = "", $type = -2){
		global $user, $PERS_TERMIN_KAT, $TERMIN_TYP, $sess;
		$this->user_id = $user->id;
		if(func_num_args() == 1){
			$id = func_get_arg(0);
			$this->restore($id);
		}
		else if(func_num_args() == 10){
			$this->id = func_get_arg(8);
			$this->start = func_get_arg(0);
			$this->end = func_get_arg(1);
			$this->txt = func_get_arg(2);
			$this->rep = func_get_arg(3);
			$this->exp = func_get_arg(4);
			$this->cat = func_get_arg(5);
			$this->prio = func_get_arg(6);
			$this->loc = func_get_arg(7);
			$this->type = func_get_arg(9);
			$this->chng_flag = FALSE;
		}
		else{
			if(empty($id))
				$id = md5(uniqid("bladidudel"));
			$this->id = $id;
			$this->start = $start;
			$this->setTitle($txt);
			$this->setEnd($end);
			$this->setExpire($exp);
			$this->setRepeat("SINGLE");
			$this->prio = $prio;
			$this->setLocation($loc);
			$this->desc = -1;
			if(empty($type))
				// privater Termin als Standard
				$type = -2; 
			$this->type = $type;
			
			// handelt es sich um einen Veranstaltungs-Termin ist die Kategorie gleich dem Typ
			if($this->type == -1 || $this->type == -2)
				$this->cat = $cat;
			else if($TERMIN_TYP[$this->type]["ebene"] == "sem")
				$this->cat = $this->type;
				
			$this->chng_flag = TRUE;
		}
		// nur persoenliche Termin haben per default eine Farbe
		// fuer Veranstaltungstermine muss eine Farbe explizit mit setColor() gesetzt werden
		if($this->type == -1 || $this->type == -2)
			$this->col = $PERS_TERMIN_KAT[$this->cat]["color"];
	}
		
	// public
	function getTitle(){
		return $this->txt;
	}
	
	// public
	function getStart(){
		return $this->start;
	}
	
	// public
	function getEnd(){
		return $this->end;
	}
	
	// public
	function getExpire(){
		return $this->exp;
	}
	
	function isDayEvent(){
		return $this->dev;
	}
	
	function setDayEvent($is_dev){
		$this->dev = $is_dev;
	}
	
	// public
	function getTs(){
		$repeat_data = explode(",", $this->rep);
		return $repeat_data[0];
	}
	
	// public
	function getDuration(){
		if(date("I", $this->start) > date("I", $this->end))
			return($this->end - $this->start - 3600);
		if(date("I", $this->start) < date("I", $this->end))
			return($this->end - $this->start + 3600);
		return($this->end - $this->start);
	}
	
	// public
	function getRepeat($index = ""){
		if($this->rep != ""){
			list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
			     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $this->rep);
			if($rep["duration"] == "#")
				$rep["duration"] = 1;
			return $index?$rep[$index]:$rep;
		}
		return FALSE;
	}
	
	// public
	function getColor(){
		if($this->col == "")
			return FALSE;
		return $this->col;
	}
	
	// public
	function getCategorie(){
		return $this->cat;
	}
	
	// public
	function getLocation(){
		if($this->loc == "")
			return FALSE;
		return $this->loc;
	}
	
	// public
	function getType(){
		return $this->type;
	}
	
	// public
	function getSeminarId(){
		if($this->sem_id != "")
			return $this->sem_id;
		return FALSE;
	}
	
	// public
	function setSeminarId($id){
		global $TERMIN_TYP;
		// Seminar-Typ muss vorher gesetzt werden
		if($TERMIN_TYP[$this->type]["ebene"] == "sem")
			$this->sem_id = $id;
		else
			$this->sem_id = "";
	}
	
	// public
	function setType($type){
		global $TERMIN_TYP;
		// nur fuer private Termine
		if($type == -2 || $type == -1){
			$this->type = $type;
			$this->chng_flag = TRUE;
		}
	}
	
	// public
	function setLocation($loc){
		$this->loc = $loc;
		$this->chng_flag = TRUE;
	}
	
	// public
	function getDescription(){
		if(is_int($this->desc)){
			$db = new DB_Seminar;
			$query = sprintf("SELECT termin_id, description FROM termine WHERE termin_id='%s'", $this->id);
			$db->query($query);
			if($db->next_record())
				return $db->f("description");
			return FALSE;
		}
		return $this->desc;
	}
	
	// public
	function setDescription($description){
		$this->desc = $description;
		$this->chng_flag = TRUE;
	}
	
	// public
	function getPriority(){
		return $this->prio;
	}
	
	function setPriority($prio){
		if($prio < 6 && $prio > 0){
			$this->prio = $prio;
			$this->chng_flag = TRUE;
		}
	}
	
	// public
	function setId($id){
		$this->id = $id;
		$this->chng_flag = TRUE;
	}
	
	// public
	function getId(){
		return $this->id;
	}
	
	// public
	function setTitle($txt = ""){
		if($txt)
			$this->txt = $txt;
		else
			$this->txt = "Kein Titel";
		$this->chng_flag = TRUE;
	}
	
	// public
	function setStart($start){
		if($start <= $this->end){
			$this->start = $start;
			$this->chng_flag = TRUE;
			return TRUE;
		}
		return FALSE;
	}
	
	// public
	function setEnd($end){
		if($end >= $this->start){
			$this->end = $end;
			$this->chng_flag = TRUE;
			return TRUE;
		}
		return FALSE;
	}
	
	// public
	function setRepeat(){
		$num = func_num_args();
		$type = func_get_arg(0);
		$duration = (int) ((mktime(12,0,0,date("n",$this->end),date("j",$this->end),date("Y",$this->end),0)
									- mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0))
									/ 86400) + 1;
		if($duration == 1)
			$duration = "#";
		// Hier wird auch der "genormte Timestamp" ts berechnet.
		switch($type){
			// ts ist hier der Tag des Termins 12:00:00 Uhr
			case "SINGLE":
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0);
				$this->rep = sprintf("%s,,,,,,SINGLE,%s", $this->ts, $duration);
				break;
			case "DAYLY":
				// ts ist hier der Tag des ersten Wiederholungstermins 12:00:00 Uhr
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0);
				if($num == 1)
					$this->rep = sprintf("%s,1,,,,,DAYLY,%s", $this->ts, $duration);
				elseif($num == 2)
					$this->rep = $this->ts.",".func_get_arg(1).",,,,,DAYLY,$duration";
				break;
			case "WEEKLY":
				// ts ist hier der Montag der ersten Wiederholungswoche 12:00:00 Uhr
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j", $this->start),date("Y",$this->start),0);
				switch($num){
					case 1:
						$this->ts += 604800 - (strftime("%u", $this->start) - 1) * 86400;
						$this->rep = sprintf("%s,1,,%s,,,WEEKLY,%s", $this->ts, strftime("%u", $this->start), $duration);
						break;
					case 2:
						$this->ts += func_get_arg(1) * 604800 - (strftime("%u", $this->start) - 1) * 86400;
						$this->rep = $this->ts.",".func_get_arg(1).",,".strftime("%u", $this->start).",,,WEEKLY,$duration";
						break;
					case 3:
						$this->ts += func_get_arg(1) * 604800 - (strftime("%u", $this->start) - 1) * 86400;
						$this->rep = $this->ts.",".func_get_arg(1).",,".func_get_arg(2).",,,WEEKLY,$duration";
						break;
				}
				break;
			case "MONTHLY":
				switch($num){
					case 1:
						$this->ts = mktime(12,0,0,date("n",$this->start) + 1,date("j",$this->start),date("Y",$this->start),0);
						$this->rep = sprintf("%s,1,,,,%s,MONTHLY,%s", $this->ts, date("j", $this->start), $duration);
						break;
					case 2:
						$amonth = date("n",$this->start) + func_get_arg(1);
						$this->ts = mktime(12,0,0,$amonth,date("j",$this->start),date("Y",$this->start),0);
						$this->rep = $this->ts.",".func_get_arg(1).",,,,".date("j", $this->start).",MONTHLY,$duration";
						break;
					case 3:
						$aday = func_get_arg(2);
						// Ist erste Wiederholung schon im gleichen Monat?
						if($aday < date("j", $this->start))
							$amonth = date("n",$this->start) + func_get_arg(1);
						else
							$amonth = date("n",$this->start);
						$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start),0);
						$this->rep = $this->ts.",".func_get_arg(1).",,,,".func_get_arg(2).",MONTHLY,$duration";
						break;
					case 4:
						// hier ist ts der erste Wiederholungstermin
						$amonth = date("n",$this->start) + func_get_arg(1);
						$adate = mktime(12,0,0,$amonth,1,date("Y",$this->start),0) + (func_get_arg(2) - 1) * 604800;
						$awday = strftime("%u",$adate);
						$adate -= ($awday - func_get_arg(3)) * 86400;
						if(func_get_arg(2) == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
						}
						else
							if($awday > func_get_arg(3))
								$adate += 604800;
						// Ist erste Wiederholung schon im gleichen Monat?
						if(date("j", $adate) > date("j", $this->start)){
							//Dann muss hier die Berechnung ohne Intervall wiederholt werden
							$amonth = date("n",$this->start);
							$adate = mktime(12,0,0,$amonth,1,date("Y",$this->start),0) + (func_get_arg(2) - 1) * 604800;
							$awday = strftime("%u",$adate);
							$adate -= ($awday - func_get_arg(3)) * 86400;
							if(func_get_arg(2) == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else
								if($awday > func_get_arg(3))
									$adate += 604800;
						}
						$this->ts = $adate;
						$this->rep = $this->ts.",".func_get_arg(1).",".func_get_arg(2).",".func_get_arg(3).",,,MONTHLY,$duration";
						break;
				}
				break;
			case "YEARLY":
				// ts ist hier der erste Wiederholungstermin 12:00:00 Uhr
				switch($num){
					case 1:
						$this->ts = mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y",$this->start) + 1,0);
						$this->rep = sprintf("%s,1,,,%s,%s,YEARLY,%s", $this->ts, date("n", $this->start), date("j", $this->start), $duration);
						break;
					case 3:
						$amonth = func_get_arg(1);
						$aday = func_get_arg(2);
						$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start),0);
						if($this->ts < mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y",$this->start),0))
							$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start) + 1,0);
						$this->rep = $this->ts.",1,,,".func_get_arg(1).",".func_get_arg(2).",YEARLY,$duration";
						break;
					case 4:
						$amonth = func_get_arg(3);
						$ayear = date("Y", $this->start);
						if($amonth < date("n", $this->start))
							$ayear++;
						$adate = mktime(12,0,0,$amonth,1,$ayear,0) + (func_get_arg(1) - 1) * 604800;
						$aday = strftime("%u",$adate);
						$adate -= ($aday - func_get_arg(2)) * 86400;
						if(func_get_arg(1) == 5){
							if(date("j",$adate) < 10)
								$adate -= 604800;
							if(date("n",$adate) == date("n",$adate + 604800))
								$adate += 604800;
						}
						else
							if($aday > func_get_arg(2))
								$adate += 604800;
						$this->ts = $adate;
						if($this->ts < mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y",$this->start),0))
							$this->ts = mktime(12,0,0,$amonth,$aday,date("Y",$this->start) + 1,0);
						$this->rep = $this->ts.",1,".func_get_arg(1).",".func_get_arg(2).",".func_get_arg(3).",,YEARLY,$duration";
						break;
				}
				break;
			default :
				$this->ts = mktime(12,0,0,date("n",$this->start),date("j",$this->start),date("Y",$this->start),0);
				$this->rep = sprintf("%s,,,,,,SINGLE,%s", $this->ts, $duration);
		}
		$this->chng_flag = TRUE;
	}
	
	// public
	function setExpire($exp = ""){
		if($exp == ""){
			$this->exp = 2114377200; //01.01.2037 00:00:00 Uhr
			$this->chng_flag = TRUE;
		}
		else if($exp < $this->end)
			return FALSE;
		else{
			$this->exp = $exp;
			$this->chng_flag = TRUE;
			return TRUE;
		}
	}
		
	// public
	function setColor($col){
		$this->col = $col;
		$this->chng_flag = TRUE;
	}
	
	// public
	function setCategorie($cat){
		$this->cat = $cat;
		$this->chng_flag = TRUE;
	}
	
	// Termin in Datenbank speichern
	// public
	function save(){
		global $TERMIN_TYP;
		// Natuerlich nur Speichern, wenn sich was geaendert hat
		// und es sich um einen persoenlichen Termin handelt
		if($this->chng_flag && ($this->type == -1 || $this->type == -2)){
			$db = new DB_Seminar;
			$chdate = time();
			if($this->mkd == -1)
				$mkdate = $chdate;
			else
				$mkdate = $this->mkd;
				
			if(is_int($this->desc))
				$query = sprintf("REPLACE termine (termin_id,range_id,autor_id,content,"
				       . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES"
				       . " ('%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
							 , $this->id, $this->user_id, $this->user_id, $this->txt, $this->start, $this->end
							 , $mkdate, $chdate, $this->type, $this->exp, $this->rep, $this->cat, $this->prio
							 , $this->loc);
			else
				$query = sprintf("REPLACE termine (termin_id,range_id,autor_id,content,description,"
			         . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES"
				       . " ('%s','%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
							 , $this->id, $this->user_id, $this->user_id, $this->txt, $this->desc, $this->start
							 , $this->end, $mkdate, $chdate, $this->type, $this->exp, $this->rep, $this->cat
							 , $this->prio, $this->loc);
			if($db->query($query))
				return TRUE;
			return FALSE;
		}
		return FALSE;
	}
	
	// Termin aus Datenbank loeschen
	// public
	function delete(){
		$db = new DB_Seminar;
		$query = sprintf("DELETE FROM termine WHERE termin_id='%s' AND autor_id='%s'", $this->id, $this->user_id);
		if($db->query($query))
			return TRUE;
		return FALSE;
	}
	
	// Termin aus Datenbank holen
	// public
	function restore($id){
		global $TERMIN_TYP, $PERS_TERMIN_KAT;
		$db = new DB_Seminar;
		if(func_num_args() == 1)
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON (range_id=Seminar_id) "
											. "WHERE (range_id='%s' OR user_id='%s') AND termin_id='%s'"
											, $this->user_id, $this->user_id, $id);
		else if(func_num_args() == 0)
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON (range_id=Seminar_id) "
											. "WHERE (range_id='%s' OR user_id='%s') AND termin_id='%s'"
											, $this->user_id, $this->user_id, $this->id);
		$db->query($query);
		
		if($db->next_record()){
			$this->id = $id;
			$this->txt = $db->f("content");
			$this->start = $db->f("date");
			$this->type = $db->f("date_typ");
			if(!$this->setEnd($db->f("end_time")))
				return FALSE;
				
			// bei Seminar-Terminen ist kein expire gesetzt
			if($TERMIN_TYP[$this->type]["ebene"] == "" && !$this->setExpire($db->f("expire")))
					return FALSE;
		
			$this->rep = $db->f("repeat");
			
			if($this->type == -1 || $this->type == -2)
				$this->cat = $db->f("color");
			else if($TERMIN_TYP[$this->type]["ebene"] == "sem"){
				$color = array("#000000","#FF0000","#FF9933","#FFCC66","#99FF99","#66CC66","#6699CC","#666699");
				$this->cat = $this->type;
				if($PERS_TERMIN_KAT[$this->type][color] == "")
					$this->col = $color[$db->f("gruppe")];
				else
					$this->col = $PERS_TERMIN_KAT[$this->type][color];
			}
			
			$this->desc = $db->f("description");
			$this->prio = $db->f("priority");
			$this->loc = $db->f("raum");
			$this->setSeminarId($db->f("Seminar_id"));
			$this->mkd = $db->f("mkdate");
			$this->chng_flag = FALSE;
			
			return TRUE;
		}
		return FALSE;
	}
	
	// public
	function serialisiere(){
		return serialize($this);
	}
	
	// public
	function clone(){
		$cloned = new CalendarEvent($this->start, $this->end, $this->txt, $this->rep,
		                     $this->exp, $this->cat, $this->prio, $this->loc, $this->id, $this->type);
		if(!is_int($this->descr))
			$cloned->setDescription($this->desc);
		// Das Erstellungsdatum wird hier erstmal ganz bewusst nicht uebernommen
		//if($this->mkd != -1)
			//$cloned->mkd = $this->mkd;
		$cloned->setColor($this->col);
		return $cloned;
	}
	
} // class Termin
