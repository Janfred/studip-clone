<?

/*
ressources.php - 0.8 
Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

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

require_once "config.inc.php";

class Termin{

	// Datenfelder
	
	var $id;    	    // termin_id (String)
	var $txt;         // Terminkurzbeschreibung (String)
	var $start;       // Anfangszeit des Termins als Unix-Timestamp (int)
	var $end;         // Endzeit des des Termins als Unix-Timestamp (int)
	var $rep;         // Wiederholungsanweisung des Termins (String).
	var $exp;         // Wann verliert Termin GÅltigkeit? Unix-Timestamp (int)
	var $col = "";    // Farbe (STRING)
	var $cat = 1;     // Kategorie (int)
	var $dev = FALSE; // TRUE wenn Tagestermin (boolean)
	var $ts;          // der "genormte" Timestamp
	var $prio;        // PrioritÑt (int)
	var $loc;         // Ort (String)
	var $desc;        // Terminbeschreibung (String)
	var $chng_flag;   // Termin geÑndert ? (boolean)
	var $user_id;     // User-ID aus PphLib (String)
	var $type = -2;    // Termintyp (int) siehe config.inc.php (privater Termin als Standard)
	var $sem_id = ""; // Seminar-ID, wenn es sich um einen Seminartermin handelt (String)
	var $mkd = -1;    // Erstellungsdatum (int) wird Åberschrieben, falls Termin aus DB geholt wird
	
	// Konstruktor
	function Termin($start = "", $end = "", $txt = "", $exp = "", $cat = "", $prio = 1, $loc = "", $id = "", $type = -2){
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
		// nur pers˜nliche Termin haben per default eine Farbe
		// fÅr Veranstaltungstermine muss eine Farbe explizit mit setColor() gesetzt werden
		if($this->type == -1 || $this->type == -2)
			$this->col = $PERS_TERMIN_KAT[$this->cat]["color"];
	}
		
	// public
	function getTitel(){
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
	function getKategorie(){
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
		// nur fÅr private Termine
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
	function setKategorie($cat){
		$this->cat = $cat;
		$this->chng_flag = TRUE;
	}
	
	// Termin in Datenbank speichern
	// public
	function save(){
		global $TERMIN_TYP;
		// NatÅrlich nur Speichern, wenn sich was geÑndert hat
		// und es sich um einen pers˜nlichen Termin handelt
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
	
	// Termin aus Datenbank l˜schen
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
		$cloned = new Termin($this->start, $this->end, $this->txt, $this->rep,
		                     $this->exp, $this->cat, $this->prio, $this->loc, $this->id, $this->type);
		if(!is_int($this->descr))
			$cloned->setDescription($this->desc);
		// Das Erstellungsdatum wird hier erstmal ganz bewusst nicht Åbernommen
		//if($this->mkd != -1)
			//$cloned->mkd = $this->mkd;
		$cloned->setColor($this->col);
		return $cloned;
	}
	
} // class Termin

//******************************************************************************


class Year{

	var $year;            // Jahr (int)
	var $ts;              // "genormter" timestamp (s.o.)
	
	// Konstruktor
	function Year($tmstamp){
		$this->year = date("Y", $tmstamp);
		$this->ts = mktime(12,0,0,1,1,$this->year,0);
	}
	
	// public
	function getYear(){
		return $this->year;
	}
	
	// public
	function getStart(){
		return mktime(0,0,0,1,1,$this->year);
	}
	
		// public
	function getEnd(){
		$end = mktime(0,0,0,1,1,$this->year + 1) - 1;
		return $end;
	}
	
	function getTs(){
		return $this->ts;
	}
	
	// public
	function serialisiere(){
		return serialize($this);
	}
	
} // class Year

//******************************************************************************

class Month extends Year{

	var $mon;      // Monat (int)
	
	// Konstruktor
	function Month($tmstamp){
		$this->year = date("Y", $tmstamp);
		$this->mon = date("n", $tmstamp);
		$this->ts = mktime(12,0,0,$this->mon,1,$this->year,0);
	}
	
	// public
	function getMonth(){
		return $this->mon;
	}
	
	// public
	function getNameOfMonth(){
		return month($this->ts);
	}
	
	// public
	function getStart(){
		return mktime(0,0,0,$this->mon,1,$this->year);
	}
	
	// public
	function getEnd(){
		$next_mon = $this->mon + 1;
		return mktime(0,0,0,$next_mon,1,$this->year) - 1;
	}
	
	
} // class Month

//******************************************************************************

class Day extends Month{

	var $dow;       		// Wochentag (int)
	var $dom;       		// Tag des Monats (int)

	// Konstruktor
	function Day($tmstamp){
		$date = getdate($tmstamp);
		$this->dow = strftime("%u", $tmstamp);
		$this->dom = $date["mday"];
		$this->year = $date["year"];
		$this->mon = $date["mon"];
		$this->ts = mktime(12,0,0,$this->mon,$this->dom,$this->year,0);
	}
	
	// public
	function getStart(){
		return mktime(0,0,0,$this->mon,$this->dom,$this->year);
	}
	
	function getEnd(){
		return mktime(23,59,59,$this->mon,$this->dom,$this->year);
	}
	
	// public
	function getName($mod = "SHORT"){
		return wday($this->ts, $mod);
	}
	
	// public
	function getDayOfMonth(){
		return $this->dom;
	}
	
	// public
	function getDate($mod = "LONG"){
		if($mod == "SHORT"){
			if(strlen($this->dom) == 1)
				$date = "0" . $this->dom . ".";
			else
				$date = $this->dom . ".";
			if(strlen($this->mon) == 1)
				$date .= "0" . $this->mon . ".";
			else
				$date .= $this->mon . ".";
			return $date . $this->year;
		}
		else
			return $this->dom . ". " . month($this->ts) . " " . $this->year;
	}
	
	// public
	function isHoliday(){
		return holiday($this->ts);
	}
	
} // class Day

//******************************************************************************

class DB_Day extends Day{

	var $app;         	// Termine (Object[])
	var $app_del;       // Termine, die gel˜scht werden (Object[])
	var $arr_pntr;    	// "private" function getTermin
	var $maxColumns;    // maximale Anzahl der parallel liegenden Termine
	var $user_id;       // User-ID aus PphLib (String)
	
	// Konstruktor
	function DB_Day($tmstamp){
		global $user;
		$this->user_id = $user->id;
		Day::Day($tmstamp);
		$this->restore();
		$this->sort();
		$this->arr_pntr = 0;
	}
	
	// Anzahl von Terminen innerhalb eines bestimmten Zeitabschnitts
	// default one day
	// public
	function numberOfApps($start = 0, $end = 86400){
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
	
	// Termin hinzufÅgen
	// Der Termin wird gleich richtig einsortiert
	// public
	function addTermin($term){
		$this->app[] = $term;
		$this->sort();
	//	return TRUE;
	}
	
	// Termin l˜schen
	// public
	function delTermin($id){
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
	
	// ersetzt vorhandenen Termin mit Åbergebenen Termin, wenn ID gleich
	// public
	function replaceTermin($term){
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
	function nextTermin($start = -1, $step = 3600){
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
		// Je nachdem, ob die Beschreibung eines Termins verÑndert wurde oder nicht,
		// ist es erforderlich das description Feld in der DB zu Åberschreiben.
		// Es werden also zwei unterschiedliche REPLACEs ben˜tigt.
		$db = new DB_Seminar();
		if($size = sizeof($this->app)){
			$query1 = "REPLACE termine (termin_id,range_id,autor_id,content,description,"
			        . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES";
			$query2 = "REPLACE termine (termin_id,range_id,autor_id,content,"
				      . "date,end_time,mkdate,chdate,date_typ,expire,repeat,color,priority,raum) VALUES";
			$sep1 = FALSE;
			$sep2 = FALSE;
			$chdate = time();
			if($this->mkd == -1)
				$mkdate = $chdate;
			else
				$mkdate = $this->mkd;
			
			for($i = 0;$i < $size;$i++){
				if($this->app[$i]->type == -1 || $this->app[$i]->type == -2){
					if(is_string($this->app[$i]->desc)){
						if($sep1)
							$values1 .= ",";
						$values1 .= sprintf("('%s','%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
										 , $this->app[$i]->id, $this->user_id, $this->user_id, $this->app[$i]->txt
										 , $this->app[$i]->desc, $this->app[$i]->start, $this->app[$i]->end, $mkdate
										 , $chdate, $this->app[$i]->type, $this->app[$i]->exp, $this->app[$i]->rep
										 , $this->app[$i]->cat, $this->app[$i]->prio, $this->app[$i]->loc);
						$sep1 = TRUE;
					}
					else if($this->app[$i]->chng_flag){
						if($sep2)
							$values2 .= ",";
						$values2 .= sprintf("('%s','%s','%s','%s',%s,%s,%s,%s,%s,%s,'%s','%s',%s,'%s')"
										 , $this->app[$i]->id, $this->user_id, $this->user_id, $this->app[$i]->txt
										 , $this->app[$i]->start, $this->app[$i]->end, $mkdate, $chdate, $this->app[$i]->type
										 , $this->app[$i]->exp, $this->app[$i]->rep, $this->app[$i]->cat, $this->app[$i]->prio
										 , $this->app[$i]->loc);
						$sep2 = TRUE;
					}
				}
			}
			if($values1){
				$query1 .= $values1;
				$db->query($query1);
			}
			if($values2){
				$query2 .= $values2;
				$db->query($query2);
			}
		}
		if($size = sizeof($this->app_del)){
			$query = sprintf("DELETE FROM termine WHERE range_id = '%s' AND autor_id = '%s' AND termin_id IN ("
											, $this->user_id, $this->user_id);
			for($i = 0;$i < $size;$i++){
				if($this->app[$i]->type == -1 || $this->app[$i]->type == -2){
					if($i > 0)
						$values .= ",";
					$values .= "'" . $this->app_del[$i]->getId() . "'";
				}
				$query .= $values . ")";
				$db->query($query);
			}
		}
	}
	
	// public
	function existTermin(){
		if(sizeof($this->app) > 0)
			return TRUE;
		return FALSE;
	}

	// Wiederholungstermine, die in der Vergangenheit angelegt wurden belegen in
	// app[] die ersten Positionen und werden hier in den "Tagesablauf" einsortiert
	// Termine, die sich Åber die Tagesgrenzen erstrecken, muessen anhand ihrer
	// "absoluten" Anfangszeit einsortiert werden.
	// private
	function sort(){
		if(sizeof($this->app))
			usort($this->app, "cmp_list");
	}					

	// Termine aus Datenbank holen
	// private
	function restore(){
		$db = new DB_Seminar;
		// die Abfrage grenzt das Trefferset weitgehend ein
		$query = sprintf("SELECT termin_id,content,date,end_time,date_typ,expire,repeat,color,priority,raum"
		       . " FROM termine WHERE range_id='%s' AND autor_id='%s' AND ((date BETWEEN %s AND %s OR "
					 . "end_time BETWEEN %s AND %s) OR (%s BETWEEN date AND end_time) OR (date <= %s AND expire > %s AND"
					 . " repeat REGEXP '(.+,,,.*%s.*,,,DAYLY)|(.+,.+,,,,,DAYLY)|"
					 . "(.+,.+,,.*%s.*,,,WEEKLY)|(.+,.+,,,,%s,MONTHLY)|"
					 . "(.+,.+,.+,%s,,,MONTHLY)|(.+,1,,,%s,%s,YEARLY)|"
					 . "(.+,1,.+,%s,%s,,YEARLY)|(^.*,[^#]+$)'))"
					 . " ORDER BY date ASC"
					 , $this->user_id, $this->user_id, $this->getStart(), $this->getEnd(), $this->getStart()
					 , $this->getEnd(), $this->getStart(), $this->getEnd(), $this->getStart(), $this->dow, $this->dow
					 , $this->dom, $this->dow, $this->mon, $this->dom, $this->dow, $this->mon);
		$db->query($query);
		
		while($db->next_record()){
			$time_range = 0;
			$is_in_day = FALSE;
			list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
			     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $db->f("repeat"));
			
			// der "Ursprungstermin"
			if($db->f("date") >= $this->getStart() && $db->f("end_time") <= $this->getEnd()){
					$is_in_day = TRUE;
			}
			elseif($db->f("date") >= $this->getStart() && $db->f("date") <= $this->getEnd()){
				$is_in_day = TRUE;
				$time_range = 1;
			}
			elseif($db->f("date") < $this->getStart() && $db->f("end_time") > $this->getEnd()){
				$is_in_day = TRUE;
				$time_range = 2;
			}
			elseif($db->f("end_time") >= $this->getStart() && $db->f("end_time") <= $this->getEnd()){
				$is_in_day = TRUE;
				$time_range = 3;
			}
			else{
				
				switch($rep["type"]){
					case "DAYLY":
						
						// tÑglich wiederholte Termine sind eh drin
						if($rep["lintervall"] == 1){
							$is_in_day = TRUE;
							break;
						}
						
						$pos = (($this->ts - $rep["ts"]) / 86400) % $rep["lintervall"];
						if($pos == 0){
							$is_in_day = TRUE;
							$time_range = 1;
							break;
						}
						if($pos < $rep["duration"]){
							$is_in_day = TRUE;
							if($pos == $rep["duration"] - 1)
								$time_range = 3;
							else
								$time_range = 2;
						}
						break;
						
					case "WEEKLY":
						if($rep["duration"] == "#"){
							// fÅr die anderen berechne erst mal den Montag in dieser Woche...
							$adate = $this->ts - ($this->dow - 1) * 86400;
							if(ceil(($adate - $rep["ts"]) / 604800) % $rep["lintervall"] == 0){
								$is_in_day = TRUE;
								break;
							}
						}
						else{
							$adate = $this->ts - ($this->dow - 1) * 86400;
							if($adate + 1 > $rep["ts"] - ($this->dow - 1) * 86400){
								for($i = 0;$i < strlen($rep["wdays"]);$i++){
									$pos = (($adate - $rep["ts"]) / 86400 - $rep["wdays"][$i] + $this->dow) % ($rep["lintervall"] * 7);
									if($pos == 0){
										$is_in_day = TRUE;
										$time_range = 1;
										break;
									}
									if($pos < $rep["duration"]){
										$is_in_day = TRUE;
										if($pos == $rep["duration"] - 1)
											$time_range = 3;
										else
											$time_range = 2;
										break 2;
									}
								}
							}
						}
						break;
					case "MONTHLY":
						if($rep["duration"] == "#"){
							// liegt dieser Tag nach der ersten Wiederholung und geh˜rt der Monat zur Wiederholungsreihe?
							if($rep["ts"] < $this->ts + 1 && abs(date("n", $rep["ts"]) - $this->mon) % $rep["lintervall"] == 0){
								// es ist ein Termin am X. Tag des Monats, den hat die Datenbankabfrage schon richtig erkannt
								if($rep["sintervall"] == ""){
									$is_in_day = TRUE;
									break;
								}
								// Termine an einem bestimmten Wochentag in der X. Woche
								if(ceil($this->dom / 7) == $rep["sintervall"]){
									$is_in_day = TRUE;
									break;
								}
								if($rep["sintervall"] == 5 && (($this->dom / 7) > 3))
									$is_in_day = TRUE;
							}
						}
						else{
							$amonth = ($rep["lintervall"] - ((($this->year - date("Y",$rep["ts"])) * 12) - (date("n",$rep["ts"]))) % $rep["lintervall"]) % $rep["lintervall"];
							if($rep["day"]){
								$lwst = mktime(12,0,0,$amonth,$rep["day"],$this->year,0);
								$hgst = $lwst + ($rep["duration"] - 1) * 86400;

								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
						
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
						
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range = 3;
									break;
								}
								
								$lwst = mktime(12,0,0,$amonth - $rep["lintervall"],$rep["day"],$this->year,0);
								$hgst = $lwst + $rep["duration"] * 86400;
								
								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
						
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
						
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range = 3;
									break;
								}
								
							}
							if($rep["sintervall"]){
							
								if($rep["sintervall"] == 5)
									$cor = 0;
								else
									$cor = 1;
								
								$lwst = mktime(12,0,0,$amonth,1,$this->year,0) + ($rep["sintervall"] - $cor) * 604800;
								$aday = strftime("%u",$lwst);
								$lwst -= ($aday - $rep["wdays"]) * 86400;
								if($rep["sintervall"] == 5){
									if(date("j",$lwst) < 10)
										$lwst -= 604800;
									if(date("n",$lwst) == date("n",$lwst + 604800))
										$lwst += 604800;
								}
								else{
									if($aday > $rep["wdays"])
										$lwst += 604800;
								}
								
								$hgst = $lwst + ($rep["duration"] - 1) * 86400;
								
								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
								
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
								
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range =3;
									break;
								}
								
								$lwst = mktime(12,0,0,$amonth - $rep["lintervall"],1,$this->year,0) + ($rep["sintervall"] - $cor) * 604800;;
								$aday = strftime("%u",$lwst);
								$lwst -= ($aday - $rep["wdays"]) * 86400;
								if($rep["sintervall"] == 5){
									if(date("j",$lwst) < 10)
										$lwst -= 604800;
									if(date("n",$lwst) == date("n",$lwst + 604800))
										$lwst += 604800;
								}
								else{
									if($aday > $rep["wdays"])
										$lwst += 604800;
								}
								
								$hgst = $lwst + $rep["duration"] * 86400;
								$lwst += 86400;
								
								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
								
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
								
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range =3;
									break;
								}
							}
							
						}
							
						break;
					case "YEARLY":
					
						if($rep["duration"] == "#"){
							if($rep["ts"] > $this->getStart() && $rep["ts"] < $this->getEnd()){
								$is_in_day = TRUE;
								break;
							}
								
							// liegt der Wiederholungstermin Åberhaupt in diesem Jahr?
							if($this->year == date("Y", $rep["ts"]) || ($this->year - date("Y", $rep["ts"])) % $rep["lintervall"] == 0){
								// siehe "MONTHLY"
								if($rep["sintervall"] == ""){
									$is_in_day = TRUE;
									break;
								}
								if(ceil($this->dom / 7) == $rep["sintervall"]){
									$is_in_day = TRUE;
									break;
								}
								if($rep["sintervall"] == 5 && (($this->dom / 7) > 3)){
									$is_in_day = TRUE;
									break;
								}
							}
						}
						else{
						
							// der erste Wiederholungstermin
							$lwst = $rep["ts"];
							$hgst = $rep["ts"] + $rep["duration"] * 86400;
							if($lwst == $this->ts){
								$is_in_day = TRUE;
								$time_range = 1;
								break;
							}
							
							if($this->ts > $lwst && $this->ts < $hgst){
								$is_in_day = TRUE;
								$time_range = 2;
								break;
							}
						
							if($this->ts == $hgst){
								$is_in_day = TRUE;
								$time_range = 3;
								break;
							}
							
							if($rep["day"]){
								$lwst = mktime(12,0,0,$rep["month"],$rep["day"],$this->year,0);
								$hgst = $lwst + ($rep["duration"] - 1) * 86400;

								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
						
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
						
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range = 3;
									break;
								}
								
								$lwst = mktime(12,0,0,$rep["month"],$rep["day"] - 1,$this->year - 1,0);
								$hgst = $lwst + $rep["duration"] * 86400;
								
								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
						
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
						
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range = 3;
									break;
								}
								
							}
							
							if($rep["sintervall"]){
								$lwst = mktime(12,0,0,$rep["month"],1,$this->year,0) + ($rep["sintervall"] - $cor) * 604800;
								$aday = strftime("%u",$lwst);
								$lwst -= ($aday - $rep["wdays"]) * 86400;
								if($rep["sintervall"] == 5){
									if(date("j",$lwst) < 10)
										$lwst -= 604800;
									if(date("n",$lwst) == date("n",$lwst + 604800))
										$lwst += 604800;
								}
								else
									if($aday > $rep["wdays"])
										$lwst += 604800;
						
								$hgst = $lwst + ($rep["duration"] - 1) * 86400;
						
								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
								
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
								
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range = 3;
									break;
								}
								
								$lwst = mktime(12,0,0,$rep["$month"],1,$this->year - 1,0) + ($rep["sintervall"] - $cor) * 604800;;
								$aday = strftime("%u",$lwst);
								$lwst -= ($aday - $rep["wdays"]) * 86400;
								if($rep["sintervall"] == 5){
									if(date("j",$lwst) < 10)
										$lwst -= 604800;
									if(date("n",$lwst) == date("n",$lwst + 604800))
										$lwst += 604800;
								}
								else{
									if($aday > $rep["wdays"])
										$lwst += 604800;
								}
								
								$hgst = $lwst + $rep["duration"] * 86400;
								$lwst += 86400;
								
								if($this->ts == $lwst){
									$is_in_day = TRUE;
									$time_range = 1;
									break;
								}
								
								if($this->ts > $lwst && $this->ts < $hgst){
									$is_in_day = TRUE;
									$time_range = 2;
									break;
								}
								
								if($this->ts == $hgst){
									$is_in_day = TRUE;
									$time_range = 3;
									break;
								}
								
							}
						}
				}
			}
			
			if($is_in_day==TRUE){		
				switch($time_range){
					case 0: // Einzeltermin
						$start = mktime(date("G",$db->f("date")),date("i",$db->f("date")),0,$this->mon,$this->dom,$this->year);
						$end = mktime(date("G",$db->f("end_time")),date("i",$db->f("end_time")),0,$this->mon,$this->dom,$this->year);
						break;
					case 1: // Start
						$start = mktime(date("G",$db->f("date")),date("i",$db->f("date")),0,$this->mon,$this->dom,$this->year);
						$end = $this->getEnd();
						break;
					case 2: // Mitte
						$start = $this->getStart();
						$end = $this->getEnd();
						break;
					case 3: // Ende
						$start = $this->getStart();
						$end = mktime(date("G",$db->f("end_time")),date("i",$db->f("end_time")),0,$this->mon,$this->dom,$this->year);
				}
				$termin = new Termin($start, $end, $db->f("content"), $db->f("repeat"), $db->f("expire"),
				                     $db->f("color"), $db->f("priority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
				if($time_range == 2)
					$termin->setDayEvent(TRUE);
				$termin->chng_flag = FALSE;
				$this->app[] = $termin;
			}
		}
	}
	
	// public
	function bindSeminarTermine(){
		if(func_num_args() == 0)
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user s ON Seminar_id=range_id WHERE "
			       . "user_id = '%s' AND date BETWEEN %s AND %s"
						 , $this->user_id, $this->getStart(), $this->getEnd());
		else if(func_num_args() == 1 && $seminar_ids = func_get_arg(0)){
			if(is_array($seminar_ids))
				$seminar_ids = implode("','", $seminar_ids);
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user s ON Seminar_id=range_id WHERE "
			       . "user_id = '%s' AND Seminar_id IN ('%s') AND date_typ!=6"
						 . " AND date_typ!=7 AND date BETWEEN %s AND %s"
						 , $this->user_id, $seminar_ids, $this->getStart(), $this->getEnd());
		}
		else
			return FALSE;
			
		$db = new DB_Seminar;	
		$db->query($query);
		$color = array("#000000","#FF0000","#FF9933","#FFCC66","#99FF99","#66CC66","#6699CC","#666699");
		
		if($db->num_rows() != 0){
			while($db->next_record()){
				$repeat = $db->f("date").",,,,,,SINGLE,#";
				$expire = 2114377200; //01.01.2037 00:00:00 Uhr
				$app = new Termin($db->f("date"), $db->f("end_time"), $db->f("content"), $repeat, $expire,
				                  $db->f("date_typ"), $db->f("priority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
				$app->setSeminarId($db->f("Seminar_id"));
				$app->setColor($color[$db->f("gruppe")]);
				$app->setKategorie($db->f("date_typ"));
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

//******************************************************************************

class DB_Year extends Year{

	var $apdays;          // timestamps der Tage, die Termine enthalten (int[])
	var $user_id;         // User-ID aus PhpLib (String)

  // Konstruktor
	function DB_Year($tmstamp){
		global $user;
		$this->user_id = $user->id;
		Year::Year($tmstamp);
		$this->restore();
	}
	
	// public
	function restore(){
		$db = new DB_Seminar();
		$end = $this->getEnd();
		$start = $this->getStart();

		$query = sprintf("SELECT termin_id,content,date,end_time,date_typ,expire,repeat,color,priority,raum"
		       . " FROM termine WHERE range_id='%s' AND autor_id='%s' AND (date BETWEEN %s AND %s"
					 . " OR (date <= %s AND expire > %s AND repeat NOT LIKE '%%SINGLE%%') OR (%s BETWEEN date AND end_time))"
					 . " ORDER BY date ASC"
					 , $this->user_id, $this->user_id, $start, $end, $end, $start, $start);
		$db->query($query);
		
		$year = $this->year;
		$month = 1;
		
		while($db->next_record()){
			list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
		     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $db->f("repeat"));
			if($rep["duration"] == "#")
				$duration = 1;
			else
				$duration = $rep["duration"];
			
			switch($rep["type"]){
				// Einzeltermin
				case "SINGLE" :
					$adate = $rep["ts"];
					while($duration-- && $adate <= $end){
						$this->apdays["$adate"]++;
						$adate += 86400;
					}
					break;
				
				// tÑgliche Wiederholung
				case "DAYLY" :
					if($rep["ts"] < $start){
						// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
						$adate = $this->ts + ($rep["lintervall"] - (($this->ts - $rep["ts"]) / 86400) % $rep["lintervall"]) * 86400;
						// Wie oft muss ein mehrtÑgiger Termin eingetragen werden, dessen
						// Startzeit vor Jahresbeginn liegt?
						if(($xdate = $adate - ($rep["lintervall"] - $duration + 1) * 86400) > $start){
							$duration_first = ($xdate - $this->ts) / 86400 + 1;
							$md_date = $this->ts;
							while($duration_first-- && $md_date <= $end && $md_date <= $db->f("expire")){
								$this->apdays["$md_date"]++;
								$md_date += 86400;
							}
						}
					}
					else
						$adate = $rep["ts"];
					
					while($duration--){
						$md_date = $adate;
						while($md_date <= $db->f("expire") && $md_date <= $end){
							$this->apdays["$md_date"]++;
							$md_date += 86400 * $rep["lintervall"];
						}
						$adate += 86400;
					}
					break;
				
				// w˜chentliche Wiederholung
				case "WEEKLY" :
					if($db->f("date") > $start){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						if($rep["ts"] != $adate){
							$md_date = $adate;
							$count = $duration;
							while($count-- && $md_date <= $end && $md_date <= $db->f("expire")){
								$this->apdays["$md_date"]++;
								$md_date += 86400;
							}
						}
							
						
						$aday = strftime("%u", $adate) - 1;
						for($i = 0;$i < strlen($rep["wdays"]);$i++){
							$awday = (int) substr($rep["wdays"], $i, 1) - 1;
							if($awday > $aday){
								$wdate = $adate + ($awday - $aday) * 86400;
								$count = $duration;
								while($count--){
									if($wdate > $end || $wdate > $db->f("expire"))
										break 2;
									$this->apdays["$wdate"]++;
									$wdate += 86400;
								}
							}
						}
					}
					
					if($rep["ts"] < $start){
						// Brauche Montag der "angefangenen" Woche
						$adate = $this->ts - (strftime("%u",$start) - 1) * 86400;
						$adate += (($rep["lintervall"] - (($adate - $rep["ts"]) / 604800) % $rep["lintervall"]) % $rep["lintervall"]) * 604800;
					}
					else
						$adate = $rep["ts"];
						
					while($adate <= $db->f("expire") && $adate <= $end){
						// Termin kann innerhalb der Woche an verschiedenen Wochentagen wiederholt werden
						for($i = 0;$i < strlen($rep["wdays"]);$i++){
							$awday = (int) substr($rep["wdays"], $i, 1) - 1;
							$wdate = $adate + $awday * 86400;
							$count = $duration;
							while($count--){
								if($wdate > $end || $wdate > $db->f("expire"))
									break 3;
								$this->apdays["$wdate"]++;
								$wdate += 86400;
							}
						}
						$adate += 604800 * $rep["lintervall"];
					}
					break;
				
				// monatliche Wiederholung
				case "MONTHLY" :
					if($db->f("date") > $start){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						$count = $duration;
						while($count-- && $adate <= $end && $adate <= $db->f("expire")){
							$this->apdays["$adate"]++;
							$adate += 86400;
						}
					}
					
					if($rep["sintervall"] == 5)
						$cor = 0;
					else
						$cor = 1;
					
					if($rep["ts"] < $start){
						// brauche ersten Monat in dem der Termin wiederholt wird
				  	$amonth = ($rep["lintervall"] - ((($year - date("Y",$rep["ts"])) * 12) - date("n",$rep["ts"])) % $rep["lintervall"]) % $rep["lintervall"];
						// ist Wiederholung am X. Wochentag des X. Monats...
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else{
								if($aday > $rep["wdays"])
									$adate += 604800;
							}
						}
						else
							// oder am X. Tag des Monats ?
							$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
					}
					else{
						// handelt es sich um "X. Wochentag des X. Monats" kommt nichts hinzu
						$adate = $rep["ts"];// + ($rep["day"]?($rep["day"] - 1) * 86400:0);
						$amonth = date("n", $rep["ts"]);
					}
					
					// Termine, die die Jahresgrenze ÅberbrÅcken
					if($duration > 1 && $rep["ts"] < $this->ts){
						if($rep["day"] == ""){
							$xdate = mktime(12,0,0,$amonth - $rep["lintervall"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$xdate);
							$xdate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$xdate) < 10)
									$xdate -= 604800;
								if(date("n",$xdate) == date("n",$xdate + 604800))
									$xdate += 604800;
							}
							else
								if($aday > $rep["wdays"])
									$xdate += 604800;
							$xdate += $duration * 86400;
						}
						else
							$xdate = mktime(12,0,0,date("n",$adate) - $rep["lintervall"],date("j",$adate) + $duration,date("Y",$adate),0);
						
						$xdate++;
						$md_date = $this->ts;
						while($md_date < $xdate && $md_date <= $db->f("expire")){
							$this->apdays["$md_date"]++;
							$md_date += 86400;
						}
					}
					
					while($adate <= $db->f("expire") && $adate <= $end){
						$md_date = $adate;
						$count = $duration;
						while($count--){
							// verhindert die Anzeige an Tagen, die auòerhalb des Monats liegen (am 29. bis 31.)
							if($rep["wdays"] == ""?date("j", $adate) == $rep["day"]:TRUE
								&& $md_date <= $db->f("expire") && $md_date <= $end)
									$this->apdays["$md_date"]++;
							$md_date += 86400;
						}
						$amonth += $rep["lintervall"];
						// wenn Termin am X. Wochentag des X. Monats, dann Berechnung hier wiederholen
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else
								if($aday > $rep["wdays"])
									$adate += 604800;
						}
						else
							$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
					}
					break;
				
				// jÑhrliche Wiederholung
				case "YEARLY" :
					if($db->f("date") > $start -1 && $db->f("start") < $end + 1){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						if($rep["ts"] != $adate){
							$count = $duration;
							while($count-- && $adate <= $end && $adate <= $db->f("expire")){
								$this->apdays["$adate"]++;
								$adate += 86400;
							}
						}
					}
					
					if($rep["sintervall"] == 5)
						$cor = 0;
					else
						$cor = 1;
					
					if($rep["ts"] < $start){
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$rep["month"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else
								if($aday > $rep["wdays"])
									$adate += 604800;
						}
						else
							$adate = mktime(12,0,0,$rep["month"],$rep["day"],$year,0);
					}
					else
						$adate = $rep["ts"];
					
					
					
					// Termine, die die Jahresgrenze ÅberbrÅcken
					if($duration > 1 && $rep["ts"] < $this->ts){
						if($rep["day"] == ""){
							$xdate = mktime(12,0,0,$rep["month"],1,$year - 1,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$xdate);
							$xdate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$xdate) < 10)
									$xdate -= 604800;
								if(date("n",$xdate) == date("n",$xdate + 604800))
									$xdate += 604800;
							}
							else
								if($aday > $rep["wdays"])
									$xdate += 604800;
							$xdate += $duration * 86400;
						}
						else
							$xdate = mktime(12,0,0,date("n",$adate),date("j",$adate) + $duration - 1,date("Y",$adate) - 1,0);
						
						$xdate++;
						$md_date = $this->ts;
						while($md_date < $xdate && $md_date <= $db->f("expire")){
							$this->apdays["$md_date"]++;
							$md_date += 86400;
						}
					}
					
					if($adate > $db->f("date"))
						while($duration-- && $adate <= $db->f("expire") && $adate <= $end){
							$this->apdays["$adate"]++;
							$adate += 86400;
						}
					break;
					
			}
		}
	}
	
	function bindSeminarTermine(){
		// zeigt alle abonnierten Seminare an
		if(func_num_args() == 0)
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON Seminar_id=range_id WHERE "
			       . "user_id = '%s' AND date BETWEEN %s AND %s"
						 , $this->user_id, $this->getStart(), $this->getEnd());
		else if(func_num_args() == 1 && $seminar_ids = func_get_arg(0)){
			if(is_array($seminar_ids))
				$seminar_ids = implode("','", $seminar_ids);
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON Seminar_id=range_id WHERE "
			       . "user_id = '%s' AND Seminar_id IN ('%s')"
						 . " AND date BETWEEN %s AND %s"
						 , $this->user_id, $seminar_ids, $this->getStart(), $this->getEnd());
		}
		else
			return FALSE;
		
		$db = new DB_Seminar;
		$db->query($query);
		
		if($db->num_rows() > 0){
			while($db->next_record()){
				$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),$this->year,0);
				$this->apdays["$adate"]++;
			}
			return TRUE;
		}
		return FALSE;
	}
	
	// public
	function existTermin($tmstamp){
		$adate = mktime(12,0,0,date("n", $tmstamp), date("j", $tmstamp), date("Y", $tmstamp),0);
		if(empty($this->apdays["$adate"]))
			return FALSE;
		return TRUE;
	}
	
	// Anzahl von Terminen an einem bestimmten Tag
	// public
	function numberOfApps($tmstamp){
		$adate = mktime(12,0,0,date("n", $tmstamp),date("j", $tmstamp),date("Y", $tmstamp),0);
		return $this->apdays[$adate];
	}
	
	// public
	function serialisiere(){
		return serialize($this);
	}
	
} // class DB_Year
		
//******************************************************************************

class DB_Month extends DB_Year{

	var $month;        // Monat (Object)
	var $apps;         // Object[][]
	var $arr_pntr;     // Array-Pointer (int)
	
	// Konstruktor
	function DB_Month($tmstamp){
		$this->month = new Month($tmstamp);
		$this->apps = array();
		DB_Year::DB_Year($tmstamp);
	}
	
	// public
	function getMonth(){
		return $this->month->getMonth();
	}
	
	// public
	function getNameOfMonth(){
		return $this->month->getNameOfMonth();
	}
	
	// public
	function getStart(){
		return $this->month->getStart();
	}
	
	// public
	function getEnd(){
		return $this->month->getEnd();
	}
	
	function getTs(){
		return $this->month->getTs();
	}
	
	// public
	function sort(){
			while(list($key, $val)=each($this->apps)){
				usort($val,"cmp");
				$this->apps[$key] = $val;
			}
	}
	
	// public
	// ist im Prinzip die gleiche Methode, die auch Jahr benutzt, nur werden hier
	// zusÑtzlich Terminobjekte erzeugt, so dass in der Monatsansicht auf die
	// Termindaten zugegriffen werden kann
	function restore(){
		$db = new DB_Seminar();
		// 12 Tage zusÑtzlich (angezeigte Tage des vorigen und des nÑchsten Monats)
		$end = $this->getEnd() + 518400;
		$start = $this->getStart() - 518400;
		$start_ts = $this->month->ts - 518400;
		$end_ts = $start_ts + date("t",$this->month->ts) * 86400 + 518400;
		$query = sprintf("SELECT termin_id,content,date,end_time,date_typ,expire,repeat,color,priority,raum"
		       . " FROM termine WHERE range_id='%s' AND autor_id='%s' AND (date BETWEEN %s AND %s OR "
					 . "(date <= %s AND expire > %s AND (repeat NOT LIKE '%%SINGLE%%' OR repeat REGEXP '^.*,[^#]+$')))"
					 . " ORDER BY date ASC"
					 , $this->user_id, $this->user_id, $start, $end, $end, $start);
		$db->query($query);
		
		$year = $this->year;
		$month = $this->getMonth() - 1;
		
		while($db->next_record()){
			list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
		     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $db->f("repeat"));
			
			if($rep["duration"] == "#")
				$duration = 1;
			else
				$duration = $rep["duration"];
				
			$expire = $db->f("expire");
			switch($rep["type"]){
				// Einzeltermin (die hat die Datenbank schon gefiltert)
				case "SINGLE" :
					$adate = $rep["ts"];
					while($duration-- && $adate <= $end){
						if($adate > $start){
							$this->apdays["$adate"]++;
							$this->apps["$adate"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
						}
						$adate += 86400;
					}
					break;
				
				// tÑgliche Wiederholung
				case "DAYLY" :
					if($rep["ts"] < $start){
						// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
						$adate = $start_ts + ($rep["lintervall"] - (($start_ts - $rep["ts"]) / 86400) % $rep["lintervall"]) * 86400;
						// Wie oft muss ein mehrtÑgiger Termin eingetragen werden, dessen
						// Startzeit vor Jahresbeginn liegt?
						if(($xdate = $adate - ($rep["lintervall"] - $duration + 1) * 86400) > $start_ts){
							$duration_first = ($xdate - $start_ts) / 86400 + 1;
							$md_date = $start_ts;
							while($duration_first-- && $md_date <= $end && $md_date <= $expire){
								$this->apdays["$md_date"]++;
								$this->apps["$md_date"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                             $db->f("repeat"),$expire,$db->f("color"),
																				 $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
								$md_date += 86400;
							}
						}
					}
					else
						$adate = $rep["ts"];
					
					while($duration--){
						$md_date = $adate;
						while($md_date <= $db->f("expire") && $md_date <= $end){
							$this->apdays["$md_date"]++;
							$this->apps["$md_date"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                             $db->f("repeat"),$expire,$db->f("color"),
																				 $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$md_date += 86400 * $rep["lintervall"];
						}
						$adate += 86400;
					}
					break;
				
				// w˜chentliche Wiederholung
				case "WEEKLY" :
					if($db->f("date") > $start - 1 && $db->f("date") < $end + 1){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						if($rep["ts"] != $adate){
							$md_date = $adate;
							$count = $duration;
							while($count-- && $md_date <= $end && $md_date <= $expire){
								$this->apdays["$md_date"]++;
								$this->apps["$md_date"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                            $db->f("repeat"),$expire,$db->f("color"),
																			  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
								$md_date += 86400;
							}
						}
						$aday = strftime("%u", $adate) - 1;
						for($i = 0;$i < strlen($rep["wdays"]);$i++){
							$awday = (int) substr($rep["wdays"], $i, 1) - 1;
							if($awday > $aday){
								$wdate = $adate + ($awday - $aday) * 86400;
								$count = $duration;
								while($count--){
									if($wdate > $end || $wdate > $expire)
										break 2;
									$this->apdays["$wdate"]++;
									$this->apps["$wdate"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
									$wdate += 86400;
								}
							}
						}
					}
					if($rep["ts"] < $start){
						// Brauche den Montag der angefangenen Woche
						$start_ts = $this->ts - 518400;
						$adate = $start_ts - (strftime("%u",$start_ts) - 1) * 86400;
						$adate += (($rep["lintervall"] - (($adate - $rep["ts"]) / 604800) % $rep["lintervall"]) % $rep["lintervall"]) * 604800;
					}
					else
						$adate = $rep["ts"];
						
					while($adate <= $expire && $adate <= $end){
						// Termin kann innerhalb der Woche an verschiedenen Wochentagen wiederholt werden
						for($i = 0;$i < strlen($rep["wdays"]);$i++){
							$awday = (int) substr($rep["wdays"], $i, 1) - 1;
							$wdate = $adate + $awday * 86400;
							$count = $duration;
							while($count--){
								if($wdate > $end || $wdate > $db->f("expire"))
									break 3;
								$this->apdays["$wdate"]++;
								$this->apps["$wdate"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
								$wdate += 86400;
							}
						}
						$adate += 604800 * $rep["lintervall"];
					}
					break;
				
				// monatliche Wiederholung
				case "MONTHLY" :
					
					if($db->f("date") > $start){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						$count = $duration;
						while($count-- && $adate <= $end && $adate <= $db->f("expire")){
							$this->apdays["$adate"]++;
							$this->apps["$adate"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$adate += 86400;
						}
					}
					
					if($rep["sintervall"] == 5)
						$cor = 0;
					else
						$cor = 1;
					
					if($rep["ts"] < $start){
						// brauche ersten Monat in dem der Termin wiederholt wird
				  	$amonth = ($rep["lintervall"] - ((($year - date("Y",$rep["ts"])) * 12) - (date("n",$rep["ts"]))) % $rep["lintervall"]) % $rep["lintervall"];
						// ist Wiederholung am X. Wochentag des X. Monats...
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else{
								if($aday > $rep["wdays"])
									$adate += 604800;
							}
						}
						else
							// oder am X. Tag des Monats ?
							$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
					}
					else{
						// handelt es sich um "X. Wochentag des X. Monats" kommt nichts hinzu
						$adate = $rep["ts"];// + ($rep["day"]?($rep["day"] - 1) * 86400:0);
						$amonth = date("n", $rep["ts"]);
					}
					
					
					// Termine, die die Jahresgrenze ÅberbrÅcken
					if($duration > 1 && $rep["ts"] < $start_ts){
						if($rep["day"] == ""){
							$xdate = mktime(12,0,0,$amonth - $rep["lintervall"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$xdate);
							$xdate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$xdate) < 10)
									$xdate -= 604800;
								if(date("n",$xdate) == date("n",$xdate + 604800))
									$xdate += 604800;
							}
							else
								if($aday > $rep["wdays"])
									$xdate += 604800;
							$xdate += $duration * 86400;
						}
						else
							$xdate = mktime(12,0,0,date("n",$adate) - $rep["lintervall"],date("j",$adate) + $duration,date("Y",$adate),0);
						
						$xdate++;
						$md_date = $start_ts;
						while($md_date < $xdate && $md_date <= $db->f("expire")){
							$this->apdays["$md_date"]++;
							$this->apps["$md_date"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$md_date += 86400;
						}
					}
					
					while($rep["ts"] < $start && $adate <= $db->f("expire") && $adate <= $end){
						$md_date = $adate;
						$count = $duration;
						while($count--){
							// verhindert die Anzeige an Tagen, die auòerhalb des Monats liegen (am 29. bis 31.)
							if($rep["wdays"] == ""?date("j", $adate) == $rep["day"]:TRUE
								&& $md_date <= $db->f("expire") && $md_date <= $end)
									$this->apdays["$md_date"]++;
									$this->apps["$md_date"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$md_date += 86400;
						}
						$amonth += $rep["lintervall"];
						// wenn Termin am X. Wochentag des X. Monats, dann Berechnung hier wiederholen
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else
								if($aday > $rep["wdays"])
									$adate += 604800;
						}
						else
							$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
					}
					break;
					
				// jÑhrliche Wiederholung
				case "YEARLY" :
					if($db->f("date") > $start + 1 && $db->f("date") < $end + 1){
						$wdate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),$year,0);
						if($rep["ts"] != $wdate){
							if($db->f("end_date") < $end)
								$event_end = mktime(0,0,0,date("n",$db->f("end_time")),date("j",$db->f("end_time")) + 1,date("Y",$db->f("end_time")),0);
							else
								$event_end = $end;
							$count = $duration;
							while($wdate < $event_end && $wdate < $expire + 1){
								$this->apdays["$wdate"]++;
								$this->apps["$wdate"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
								$wdate += 86400;
							}
						}
						break;
					}
					
					if($rep["sintervall"] == 5)
						$cor = 0;
					else
						$cor = 1;
					
					if($rep["ts"] < $start){
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$rep["month"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
							}
							else
								if($aday > $rep["wdays"])
									$adate += 604800;
						}
						else
							$adate = mktime(12,0,0,$rep["month"],$rep["day"],$year,0);
					}
					else
						$adate = $rep["ts"];
						
					if($duration > 1){// && $rep["ts"] < $start){
						if($rep["day"] == ""){
							$xdate = mktime(12,0,0,$rep["month"],1,$year - 1,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$xdate);
							$xdate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$xdate) < 10)
									$xdate -= 604800;
							}
							else
								if($aday > $rep["wdays"])
									$xdate += 604800;
							$duration_first = $duration - (date("z", $this->ts - 86400) - date("z",$xdate)) + 5;
						}
						else{
							$xdate = mktime(12,0,0,date("n",$adate),date("j",$adate),date("Y",$adate) - 1,0)
											+ ($duration - 1) * 86400;
							$duration_first = ($xdate - $this->ts) / 86400 + 7;
						}
						$md_date = $this->month->ts - 518400;
						$duration_first -= date("z", $this->month->ts);
						if($xdate + $duration * 86400 > $start){
							while($duration_first-- > 0 && $md_date <= $end && $md_date <= $expire){
								$this->apdays["$md_date"]++;
								$this->apps["$md_date"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                             $db->f("repeat"),$expire,$db->f("color"),
																				 $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
								$md_date += 86400;
							}
						}
					}
					
					while($duration-- && $adate <= $expire && $adate <= $end){
						$this->apdays["$adate"]++;
						$this->apps["$adate"][] = new Termin($db->f("date"),$db->f("end_time"),$db->f("content"),
						                              $db->f("repeat"),$expire,$db->f("color"),
																				  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
						$adate += 86400;
					}
					break;
			}
		}
	}
	
	// public
	function nextTermin($tmstamp){
		$adate = mktime(12,0,0,date("n", $tmstamp), date("j", $tmstamp), date("Y", $tmstamp),0);
		if($this->apps["$adate"]){
			if(!isset($this->arr_pntr["$adate"]))
				$this->arr_pntr["$adate"] = 0;
			if($this->arr_pntr["$adate"] < $this->apdays["$adate"])
				return $this->apps["$adate"][$this->arr_pntr["$adate"]++];
			$this->arr_pntr["$adate"] = 0;
		}
		return FALSE;
	}
	
	// public
	function setPointer($tmstamp, $pos){
		$adate = mktime(12,0,0,date("n", $tmstamp),date("j", $tmstamp),date("Y", $tmstamp),0);
		$this->arr_pntr["$adate"] = $pos;
	}

	function bindSeminarTermine(){
		// 6 Tage zusÑtzlich (angezeigte Tage des vorigen und des nÑchsten Monats)
		$end = $this->getEnd() + 518400;
		$start = $this->getStart() - 518400;
		$db = new DB_Seminar;
		$color = array("#000000","#FF0000","#FF9933","#FFCC66","#99FF99","#66CC66","#6699CC","#666699");
		
		if(func_num_args() == 0)
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON Seminar_id=range_id WHERE "
			       . "user_id = '%s' AND date BETWEEN %s AND %s"
						 , $this->user_id, $start, $end);
		else if(func_num_args() == 1 && $seminar_ids = func_get_arg(0)){
			if(is_array($seminar_ids))
				$seminar_ids = implode("','", $seminar_ids);
			$query = sprintf("SELECT * FROM termine LEFT JOIN seminar_user ON Seminar_id=range_id WHERE "
			       . "user_id = '%s' AND Seminar_id IN ('%s') AND date BETWEEN %s AND %s"
						 , $this->user_id, $seminar_ids, $start, $end);
		}
		else
			return FALSE;
			
		$db->query($query);
		
		while($db->next_record()){
			$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),$this->year,0);
			$repeat = $db->f("date").",,,,,,SINGLE,#";
			$expire = 2114377200; //01.01.2037 00:00:00 Uhr
			$this->apdays["$adate"]++;
			$app = new Termin($db->f("date"), $db->f("end_time"),
			                          $db->f("content"), $repeat, $expire, $db->f("date_typ"),
															  $db->f("priority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
			$app->setSeminarId($db->f("Seminar_id"));
			$app->setColor($color[$db->f("gruppe")]);
			$app->setKategorie($db->f("date_typ"));
			$this->apps["$adate"][] = $app;
		}
	}
	
} // class DB_Month

//******************************************************************************

class DB_Week{

	var $wdays;     // Object[]
	var $kw;        // Kalenderwoche (String)
	var $ts;        // Timestamp bezogen auf Montag 12:00:00 Uhr (int)
	var $type;      // 5 fÅr 5-Tage-Woche, 7 fÅr gesamte Woche (int)
	
	// Konstruktor
	function DB_Week($tmstamp, $type = "LONG"){
		if($type == "SHORT")
			$this->type = 5;
		else
			$this->type = 7;
			
		// Berechnung des Timestamps fÅr Montag 12:00:00 Uhr
		$timestamp = mktime(12,0,0,date("n",$tmstamp),date("j",$tmstamp),date("Y",$tmstamp),0);
		$this->ts = $timestamp - 86400 * (strftime("%u", $timestamp) - 1);
		
		$this->kw = strftime("%W", $this->ts);
		
		for($i = 0;$i < $this->type;$i++)
			$this->wdays[$i] = new DB_Day($this->ts + $i * 86400);
	}
	
	// public
	function getStart(){
		return mktime(0,0,0,date("n", $this->ts),date("j", $this->ts),date("Y", $this->ts));
	}
	
	// public
	function getEnd(){
		return mktime(0,0,0,date("n", $this->ts),date("j", $this->ts) + $this->type,date("Y", $this->ts)) - 1;
	}
	
	// private
	function getTs(){
		return $this->ts;
	}
	
	function getType(){
		return $this->type;
	}
	
	// public
	function serialisiere(){
		$size = sizeof($this->wdays);
		for($i = 0;$i < $size;$i++)
			$ser .= 'i:' . $i . ';' . $this->wdays[$i]->serialisiere();
		
		// Achtung: kw ist hier ein String mit fester LÑnge 2!	
		$serialized = 'O:7:"db_week":4:{s:4:"type";i:' . $this->type . ';s:2:"ts";i:'
		            . $this->ts . ';s:2:"kw";s:2:"' . $this->kw . '";s:5:"wdays";a:'
								. $size . ':{' . $ser . '}}';
		return $serialized;
	}
	
	function bindSeminarTermine(){
		if(func_num_args() == 1){
			$arg = func_get_arg(0);
			for($i = 0;$i < $this->type;$i++)
				$ret = $this->wdays[$i]->bindSeminarTermine($arg);
		}
		else
			for($i = 0;$i < $this->type;$i++)
				$ret = $this->wdays[$i]->bindSeminarTermine();
		return $ret;
	}
	
} // class Week

//******************************************************************************

class AppList{

	var $start;      // Startzeit als Unix-Timestamp (int)
	var $end;        // Endzeit als Unix-Timestamp (int)
	var $ts;         // der "genormte" Timestamp s.o. (int)
	var $apps;       // Termine (Object[])
	var $r_id;       // range_id (String)
	var $show_pr;    // Private Termine anzeigen ? (boolean)
	var $user_id;    // User-ID aus PhpLib (String)
	
	// Konstruktor
	// bei Aufruf ohne Parameter: Termine von jetzt bis jetzt + 14 Tage
	function AppList($range_id, $show_private = FALSE, $start = -1, $end = -1, $sort = TRUE){
		global $user;
		if($start == -1)
			$start = time();
		if($end == -1)
			$end = mktime(23,59,59,date("n", $start),date("j", $start) + 7,date("Y", $start));
		
		$this->start = $start;
		$this->end = $end;
		$this->ts = mktime(12,0,0,date("n", $this->start),date("j", $this->start),date("Y", $this->start),0);
		$this->r_id = $range_id;
		$this->show_pr = $show_private;
		$this->user_id = $user->id;
		$this->restore();
		if($sort)
			$this->sort();
	}
	
	// public
	function getStart(){
		return $this->start;
	}
	
	// public
	function getEnd(){
		return $this->end;
	}
	
	// Pers˜nliche Termine und Seminartermine werden gemischt. Es muss also
	// nicht mehr nachtraeglich sortiert werden.
	// private
	function restore(){
		$db = new DB_Seminar();
		//$db2 = new DB_Seminar();
		$end = $this->end;
		$start = $this->start;
		$query = sprintf("SELECT termin_id,content, description, date,end_time,date_typ,expire,repeat,color,priority,raum"
		       . " FROM termine WHERE range_id='%s' AND ", $this->r_id);
		if(!$this->show_pr)
			$query .= "date_typ != -2 AND ";
		$query .= sprintf("(date BETWEEN %s AND %s OR (date <= %s AND expire > %s AND repeat NOT LIKE '%%SINGLE%%'))"
					 . " ORDER BY date ASC", $start, $end, $end, $start);

		$db->query($query);
		
		$year = date("Y", $this->start);
		$month = date("n", $this->start);
		
		while($db->next_record()){
			list($rep["ts"], $rep["lintervall"], $rep["sintervall"], $rep["wdays"],
		     $rep["month"], $rep["day"], $rep["type"], $rep["duration"]) = explode(",", $db->f("repeat"));
			if($rep["duration"] == "#")
				$duration = 1;
			else
				$duration = $rep["duration"];
			$expire = $db->f("expire");
			
			switch($rep["type"]){
				// Einzeltermin (die hat die Datenbank schon gefiltert)
				case "SINGLE" :
					$adate = $rep["ts"];
					$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
					$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
					                   $db->f("repeat"),$expire,$db->f("color"),
					                   $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
					$event->setDescription($db->f("description"));
					$this->apps[] = $event;
					break;
				
				// tÑgliche Wiederholung
				case "DAYLY" :
					if($rep["ts"] < $start)
						// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
						$adate = $this->ts + (($rep["lintervall"]-(ceil(($start - $rep["ts"]) / 86400) % $rep["lintervall"]) - 1) * 86400);
					else
						$adate = $rep["ts"];
						
					while($adate <= $expire && $adate <= $end && $adate >= $start){
						$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
						$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                    $db->f("repeat"),$expire,$db->f("color"),
						                    $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
						$event->setDescription($db->f("description"));
						$this->apps[] = $event;
						$adate += 86400 * $rep["lintervall"];
					}
					break;
				
				// w˜chentliche Wiederholung
				case "WEEKLY" :
					if($db->f("date") > $start){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						if($rep["ts"] != $adate){
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
							$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                      $db->f("repeat"),$expire,$db->f("color"),
							                    $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$event->setDescription($db->f("description"));
							$this->apps[] = $event;
						}
						$aday = strftime("%u", $adate) - 1;
						for($i = 0;$i < strlen($rep["wdays"]);$i++){
							$awday = (int) substr($rep["wdays"], $i, 1) - 1;
							if($awday > $aday){
								$wdate = $adate + ($awday - $aday) * 86400;
									if($wdate > $expire)
										break 2;
								$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
								$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                        $db->f("repeat"),$expire,$db->f("color"),
								                    $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
								$event->setDescription($db->f("description"));
								$this->apps[] = $event;
							}
						}
					}
					if($rep["ts"] < $start){
						// Brauche den Montag der angefangenen Woche
						$start_ts = $this->ts;
						$adate = $start_ts - (strftime("%u",$start_ts) - 1) * 86400;
						$adate += (($rep["lintervall"] - (($adate - $rep["ts"]) / 604800) % $rep["lintervall"]) % $rep["lintervall"]) * 604800;
					}
					else
						$adate = $rep["ts"];
						
					while($adate <= $expire && $adate <= $end && $adate >= $start){
						// Termin kann innerhalb der Woche an verschiedenen Wochentagen wiederholt werden
						for($i = 0;$i < strlen($rep["wdays"]);$i++){
							$awday = (int) substr($rep["wdays"], $i, 1) - 1;
							$wdate = $adate + $awday * 86400;
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
							if($real_date > $end || $wdate > $expire)
								break 2;
							if($real_date < $start)
								continue;
							$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                     $db->f("repeat"),$expire,$db->f("color"),
							                   $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$event->setDescription($db->f("description"));
							$this->apps[] = $event;
						}
						$adate += 604800 * $rep["lintervall"];
					}
					break;
				
				// monatliche Wiederholung
				case "MONTHLY" :
					if($db->f("date") > $start){
						$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						if($rep["ts"] != $adate){
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
							$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                     $db->f("repeat"),$db->f("expire"),$db->f("color"),
																$db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$event->setDescription($db->f("description"));
							$this->apps[] = $event;
						}
					}
					
					if($rep["sintervall"] == 5)
						$cor = 0;
					else
						$cor = 1;
					
					if($rep["ts"] < $start){
						// brauche ersten Monat nach $start in dem der Termin wiederholt wird
						$amonth = $month + (abs($month - date("n", $rep["ts"])) % $rep["lintervall"]);
						// ist Wiederholung am X. Wochentag des X. Monats...
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else{
								if($aday > $rep["wdays"])
									$adate += 604800;
							}
						}
						else
							// oder am X. Tag des Monats ?
							$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
					}
					else{
						// handelt es sich um "X. Wochentag des X. Monats" kommt nichts hinzu
						$adate = $rep["ts"] + ($rep["day"] ? ($rep["day"] - 1) * 86400 : 0);
						$amonth = date("n", $rep["ts"]);
					}
						
					while($adate <= $expire && $adate <= $end && $adate >= $start){
						// verhindert die Anzeige an Tagen, die auòerhalb des Monats liegen (am 29. bis 31.)
						if($rep["wdays"] == "" ? date("j", $adate) == $rep["day"] : TRUE){
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
							$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                    $db->f("repeat"),$expire,$db->f("color"),
							                  $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$event->setDescription($db->f("description"));
							$this->apps[] = $event;
						}
						
						$amonth += $rep["lintervall"];
						// wenn Termin am X. Wochentag des X. Monats, dann Berechnung hier wiederholen
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$amonth,1,$year,0) + ($rep["sintervall"] - 1) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
								if(date("n",$adate) == date("n",$adate + 604800))
									$adate += 604800;
							}
							else{
								if($aday > $rep["wdays"])
									$adate += 604800;
							}
						}
						else
							$adate = mktime(12,0,0,$amonth,$rep["day"],$year,0);
					}
					break;
				
				// jÑhrliche Wiederholung
				case "YEARLY" :
					if($db->f("date") > $start){
						$wdate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
						if($rep["ts"] != $wdate){
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
							$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                     $db->f("repeat"),$db->f("expire"),$db->f("color"),
							                   $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$event->setDescription($db->f("description"));
							$this->apps[] = $event;
						}
					}
					
					if($rep["sintervall"] == 5)
						$cor = 0;
					else
						$cor = 1;
					
					if($rep["ts"] < $start){
						if($rep["day"] == ""){
							$adate = mktime(12,0,0,$rep["month"],1,$year,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$adate);
							$adate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$adate) < 10)
									$adate -= 604800;
							}
							else
								if($aday > $rep["wdays"])
									$adate += 604800;
						}
						else
							$adate = mktime(12,0,0,$rep["month"],$rep["day"],$year,0);
					}
					else
						$adate = $rep["ts"];
					
					if($duration > 1){
						if($rep["day"] == ""){
							$xdate = mktime(12,0,0,$rep["month"],1,$year - 1,0) + ($rep["sintervall"] - $cor) * 604800;
							$aday = strftime("%u",$xdate);
							$xdate -= ($aday - $rep["wdays"]) * 86400;
							if($rep["sintervall"] == 5){
								if(date("j",$xdate) < 10)
									$xdate -= 604800;
							}
							else
								if($aday > $rep["wdays"])
									$xdate += 604800;
							$duration_first = $duration - (date("z",mktime(12,0,0,12,31,$year-1,0)) - date("z",$xdate)) - 1;
						}
						else{
							$xdate = mktime(12,0,0,date("n",$adate),date("j",$adate),date("Y",$adate) - 1,0)
											+ ($duration - 1) * 86400;
							$duration_first = ($xdate - $this->ts) / 86400 + 1;
						}
						if($xdate <= $end && $xdate >= $start && $xdate <= $expire){
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $md_date),date("j", $md_date),date("Y", $md_date));
							$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
							                           $db->f("repeat"),$expire,$db->f("color"),
																				 $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
							$event->setDescription($db->f("description"));
							$this->apps[] = $event;
						}
					}
					
					if($adate <= $end && $adate >= $start && $adate <= $expire){
						$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
						$event = new Termin($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                    $db->f("repeat"),$expire,$db->f("color"),
						                    $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
						$event->setDescription($db->f("description"));
						$this->apps[] = $event;
					}
					break;
			}
		}
	}
	
	// public
	function numberOfApps(){
		return sizeof($this->apps);
	}
	
	function existTermin(){
		return sizeof($this->apps) > 0 ? TRUE : FALSE;
	}
	
	// public
	function nextTermin(){
		if(list(,$ret) = each($this->apps));
			return $ret;
		return FALSE;
	}
	
	function sort(){
		if($this->apps)
			usort($this->apps,"cmp_list");
	}
	
} // class AppList

//******************************************************************************

//*************** Hilfsfunktionen *********************

function cmp($a, $b){
	$start_a = date("Gi", $a->getStart());
	$start_b = date("Gi", $b->getStart());
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

function cmp_list($a, $b){
	$start_a = $a->getStart();
	$start_b = $b->getStart();
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

?>