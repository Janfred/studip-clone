<?

/*
DbCalendarMonth.class.php - 0.7.5.20020312
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
require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarYear.class.php");

class DbCalendarMonth extends DbCalendarYear{

	var $month;        // Monat (Object)
	var $apps;         // Object[][]
	var $arr_pntr;     // Array-Pointer (int)
	
	// Konstruktor
	function DbCalendarMonth($tmstamp){
		$this->month = new Month($tmstamp);
		$this->apps = array();
		DbCalendarYear::DbCalendarYear($tmstamp);
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
	// zusätzlich Terminobjekte erzeugt, so dass in der Monatsansicht auf die
	// Termindaten zugegriffen werden kann
	function restore(){
		$db = new DB_Seminar();
		// 12 Tage zusätzlich (angezeigte Tage des vorigen und des nächsten Monats)
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
				
				// tägliche Wiederholung
				case "DAYLY" :
					if($rep["ts"] < $start){
						// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
						$adate = $start_ts + ($rep["lintervall"] - (($start_ts - $rep["ts"]) / 86400) % $rep["lintervall"]) * 86400;
						// Wie oft muss ein mehrtägiger Termin eingetragen werden, dessen
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
				
				// wöchentliche Wiederholung
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
					
					
					// Termine, die die Jahresgrenze überbrücken
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
							// verhindert die Anzeige an Tagen, die außerhalb des Monats liegen (am 29. bis 31.)
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
					
				// jährliche Wiederholung
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
		// 6 Tage zusätzlich (angezeigte Tage des vorigen und des nächsten Monats)
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

?>
