<?

function list_restore(&$this){
	$db = new DB_Seminar();
	$end = $this->end;
	$start = $this->start;
	$query = "SELECT * FROM termine WHERE range_id='" . $this->r_id . "' AND ";
	if(!$this->show_pr)
		$query .= "date_typ != -2 AND ";
	$query .= "(((date BETWEEN $start AND $end) OR (end_time BETWEEN $start AND $end)) "
					. "OR (date <= $end AND expire > $start AND repeat NOT LIKE '%%SINGLE%%')) "
					. "ORDER BY date ASC";

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
				new_event($this, $db, $real_date);
				break;
			
			// tägliche Wiederholung
			case "DAYLY" :
				if($rep["ts"] < $start)
					// brauche den ersten Tag nach $start an dem dieser Termin wiederholt wird
					$adate = $this->ts + (($rep["lintervall"]-(ceil(($start - $rep["ts"]) / 86400) % $rep["lintervall"]) - 1) * 86400);
				else
					$adate = $rep["ts"];
					
				while($adate <= $expire && $adate <= $end && $adate >= $start){
					$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
					new_event($this, $db, $real_date);
					$adate += 86400 * $rep["lintervall"];
				}
				break;
			
			// wöchentliche Wiederholung
			case "WEEKLY" :
				if($db->f("date") > $start){
					$adate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
					if($rep["ts"] != $adate){
						$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
						new_event($this, $db, $real_date);
					}
					$aday = strftime("%u", $adate) - 1;
					for($i = 0;$i < strlen($rep["wdays"]);$i++){
						$awday = (int) substr($rep["wdays"], $i, 1) - 1;
						if($awday > $aday){
							$wdate = $adate + ($awday - $aday) * 86400;
								if($wdate > $expire)
									break 2;
							$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
							new_event($this, $db, $real_date);
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
						new_event($this, $db, $real_date);
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
						new_event($this, $db, $real_date);
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
					// verhindert die Anzeige an Tagen, die außerhalb des Monats liegen (am 29. bis 31.)
					if($rep["wdays"] == "" ? date("j", $adate) == $rep["day"] : TRUE){
						$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
						new_event($this, $db, $real_date);
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
			
			// jährliche Wiederholung
			case "YEARLY" :
				if($db->f("date") > $start){
					$wdate = mktime(12,0,0,date("n",$db->f("date")),date("j",$db->f("date")),date("Y",$db->f("date")),0);
					if($rep["ts"] != $wdate){
						$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $wdate),date("j", $wdate),date("Y", $wdate));
						new_event($this, $db, $real_date);
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
						new_event($this, $db, $real_date);
					}
				}
				
				if($adate <= $end && $adate >= $start && $adate <= $expire){
					$real_date = mktime(date("G", $db->f("date")), date("i", $db->f("date")),0,date("n", $adate),date("j", $adate),date("Y", $adate));
					new_event($this, $db, $real_date);
				}
				break;
		}
	}
}

function new_event (&$this, $db, $real_date) {
	$event = new CalendarEvent($real_date,$db->f("end_time") - $db->f("date") + $real_date,$db->f("content"),
						                           $db->f("repeat"),$expire,$db->f("color"),
																			 $db->f("prority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
	$event->setDescription($db->f("description"));
	$event->setMakeDate($db->f("mkdate"));
	$event->setChangeDate($db->f("chdate"));
	$this->apps[] = $event;
}
	
?>
