<?

function day_save($this){
	// Je nachdem, ob die Beschreibung eines Termins verändert wurde oder nicht,
	// ist es erforderlich das description Feld in der DB zu überschreiben.
	// Es werden also zwei unterschiedliche REPLACEs benötigt.
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

function day_restore(&$this){
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
					
					// täglich wiederholte Termine sind eh drin
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
						// für die anderen berechne erst mal den Montag in dieser Woche...
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
						// liegt dieser Tag nach der ersten Wiederholung und gehört der Monat zur Wiederholungsreihe?
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
							
						// liegt der Wiederholungstermin überhaupt in diesem Jahr?
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
			$termin = new CalendarEvent($start, $end, $db->f("content"), $db->f("repeat"), $db->f("expire"),
			                     $db->f("color"), $db->f("priority"), $db->f("raum"), $db->f("termin_id"), $db->f("date_typ"));
			if($time_range == 2)
				$termin->setDayEvent(TRUE);
			$termin->chng_flag = FALSE;
			$this->app[] = $termin;
		}
	}
}

?>
