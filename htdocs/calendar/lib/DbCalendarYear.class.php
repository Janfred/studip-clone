<?

/*
DbCalendarYear.class.php - 0.8.20020520
Personal calendar for Stud.IP.
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

require_once("config.inc.php");
require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarYear.class.php";

class DbCalendarYear extends CalendarYear{

	var $apdays;          // timestamps der Tage, die Termine enthalten (int[])
	var $user_id;         // User-ID aus PhpLib (String)

  // Konstruktor
	function DbCalendarYear($tmstamp){
		global $user;
		$this->user_id = $user->id;
		CalendarYear::CalendarYear($tmstamp);
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
				
				// tägliche Wiederholung
				case "DAYLY" :
					if($rep["ts"] < $start){
						// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
						$adate = $this->ts + ($rep["lintervall"] - (($this->ts - $rep["ts"]) / 86400) % $rep["lintervall"]) * 86400;
						// Wie oft muss ein mehrtägiger Termin eingetragen werden, dessen
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
				
				// wöchentliche Wiederholung
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
					
					// Termine, die die Jahresgrenze überbrücken
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
							// verhindert die Anzeige an Tagen, die außerhalb des Monats liegen (am 29. bis 31.)
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
				
				// jährliche Wiederholung
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
					
					
					
					// Termine, die die Jahresgrenze überbrücken
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

?>
