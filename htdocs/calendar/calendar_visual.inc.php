<?
/*
kalenderVisual.inc 0.8-20020701
Persoenlicher Terminkalender in Stud.IP.
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

// Tabellenansicht der Termine eines Tages erzeugen
function createDayTable($day_obj, $start = 6, $end = 19, $step = 900, $precol = TRUE,
                        $compact = TRUE, $link_edit = FALSE, $title_length = 70, $height = 20, $padding = 6, $spacing = 1){
	
	global $atime;
	$term = array();    // Array mit eingeordneten Terminen und Platzhaltern (mixed[])
	$colsp = array();   // Breite der Spalten in den einzelnen Zeilen (int[])
	$tab = array();     // html-Ausgabe der Tabelle zeilenweise (String[])
	$max_spalte = 0;    // maximale Spaltenzahl der Tabelle
	$height_event = $height;
	$width_precol_1 = 5;
	$width_precol_2 = 4;
	$day_event_row = "";
	
	if($precol){
		if($step >= 3600){
			$height_precol_1 = ' height="' . ($step / 3600) * $height . '"';
			$height_precol_2 = "";
			$rowspan_precol = "";
			$width_precol_1_txt = "";
			$width_precol_2_txt = "";
		}
		else{
			$height_precol_1 = "";
			$height_precol_2 = ' height="' . $height . '"';
			$rowspan_precol = ' rowspan="' . 3600 / $step . '"';
			$width_precol_1_txt = " width=\"$width_precol_1%\" nowrap ";
			$width_precol_2_txt = " width=\"$width_precol_2%\" nowrap ";
		}
	}
	
	$start *= 3600;
	$end *= 3600;
	
	// Die Generierung der Tabellenansicht erfolgt mit Hilfe "geklonter" Termine,
	// da die Anfangs- und Endzeiten zur korrekten Darstellung evtl. angepasst
	// werden muessen
	for($i = 0;$i < sizeof($day_obj->app);$i++){
		if(($day_obj->app[$i]->getEnd() > $day_obj->getStart() + $start)
				&& ($day_obj->app[$i]->getStart() < $day_obj->getStart() + $end + 3600)){
			
			$cloned_event = $day_obj->app[$i]->clone();
			$end_corr = $cloned_event->getEnd() % $step;
			if($end_corr > 0){
				$end_corr = $cloned_event->getEnd() + ($step - $end_corr);
				$cloned_event->setEnd($end_corr);
			}
			if($cloned_event->getStart() < ($day_obj->getStart() + $start))
				$cloned_event->setStart($day_obj->getStart() + $start);
			if($cloned_event->getEnd() > ($day_obj->getStart() + $end + 3600))
				$cloned_event->setEnd($day_obj->getStart() + $end + 3600);
				
			if($day_obj->app[$i]->isDayEvent())
				$tmp_day_event[] = $cloned_event;
			else
				$tmp_event[] = $cloned_event;
		}
	}
	
	// calculate maximum number of columns
	$w = 0;
	for($i = $start / $step;$i < $end / $step + 3600 / $step;$i++){
		$spalte = 0;
		$zeile = $i - $start / $step;
		while($w < sizeof($tmp_event) && $tmp_event[$w]->getStart() >= $day_obj->getStart() + $i * $step
				&& $tmp_event[$w]->getStart() < $day_obj->getStart() + ($i + 1) * $step){
				
			$event = $tmp_event[$w];
			$rows = ceil($event->getDuration() / $step);
			
			while($term[$zeile][$spalte] != "" && $term[$zeile][$spalte] != "#")
				$spalte++;
		
			$term[$zeile][$spalte] = $event;
			
			$count = $rows - 1;
			for($x = $zeile + 1;$x < $zeile + $rows;$x++){
				for($y = 0;$y <= $spalte;$y++){
					if($y == $spalte){
						$term[$x][$y] = $count--;
					}
					elseif($term[$x][$y] == "")
						$term[$x][$y] = "#";
				}
			}
			if($max_spalte < sizeof($term[$zeile]))
				$max_spalte = sizeof($term[$zeile]);
			$w++;
			
		}
	}
	
	$zeile_min = 0;
		
	for($i = $start / $step;$i < $end / $step + 3600 / $step;$i++){
		$zeile = $i - $start / $step;
		$zeile_min = $zeile;
		
		while(maxValue($term[$zeile], $step) > 1)
			$zeile += maxValue($term[$zeile], $step) - 1;
		
		$size = 0;
		for($j = $zeile_min;$j <= $zeile;$j++)
			if(sizeof($term[$j]) > $size)
					$size = sizeof($term[$j]);
					
		for($j = $zeile_min;$j <= $zeile;$j++)
			$colsp[$j] = $size;
			
		$i = $zeile + $start / $step;
	}
	
	// Zeile fuer Tagestermine
	if($precol){
		if($step >= 3600){
			$day_event_row[0] = "<th class=\"steel1\" width=\"$width_precol_1%\">&nbsp;</th>";
			$day_event_row[0] .= "<td class=\"steel1\" width=\"".(100 - $width_precol_1)."%\"";
		}
		else{
			$day_event_row[0] = "<th width=\"".($width_precol_1 + $width_precol_2)."\" colspan=\"2\">Tag</th>";
			$day_event_row[0] .= "<td class=\"steel1\" width=\"".(100 - $width_precol_1 - $width_precol_2)."%\"";
	  }
	}
	else
		$day_event_row[0] = "<td class=\"steel1\"";
	
	if($link_edit){
		if($max_spalte  > 0)
			$day_event_row[0] .= " colspan=\"".($max_spalte + 1)."\"";
	}
	else{
		if($max_spalte > 1)
			$day_event_row[0] .= " colspan=\"".$max_spalte."\"";
	}
	
	if($tmp_day_event){
		if($link_edit)
			$link_edit_str = "<td class=\"steel1\" align=\"right\"><a href=\"calendar.php?cmd=edit&atime=".$day_obj->getTs()
											."\"><img src=\"./pictures/cal-link.gif\" border=\"0\" alt=\"neuer Tagestermin\"></a></td>\n";
			
		$day_event_row[0] .= '><table width="100%" border="0" cellpadding="'.($padding / 2).'" cellspacing="1">';
		reset($tmp_day_event);
		foreach($tmp_day_event as $day_event){
			$title = fit_title($day_event->getTitle(),1,1,$title_length);
			$title_str = sprintf('<a href="calendar.php?cmd=edit&termin_id=%s&atime=%s"><font class="inday">%s'
													. '</font></a>', $day_event->getId(), $day_event->getStart(), $title);
			$day_event_row[0] .= sprintf('<tr><td bgcolor="%s" style="background-color:%s">'
																.'<table width="100%%" border="0" cellpadding="1" cellspacing="0"><tr>'
																, $day_event->getColor(), $day_event->getColor());
			$day_event_row[0] .= "<td class=\"steel1\" width=\"100%\">$title_str</td>";
			$day_event_row[0] .= "\n</tr></table></td>$link_edit_str</tr>\n";
		}
		$day_event_row[0] .= "</table></td>";
	}
	else
		if($link_edit)
			$day_event_row[0] .= " align=\"right\"><a href=\"calendar.php?cmd=edit&atime=".$day_obj->getTs()
												. "\"><img src=\"./pictures/cal-link.gif\" border=\"0\" alt=\"neuer Tagestermin\"></a></td>\n";
		else
			$day_event_row[0] .= ">&nbsp;</td>\n";
	
	if($compact)
		$day_event_row[0] = "<tr>$day_event_row[0]</tr>\n";
	
	for($i = $start / $step;$i < $end / $step + 3600 / $step;$i++){
		$cspan_str = "";
		$zeile = $i - $start / $step;
		
		if($link_edit){
			$link_edit_time = $zeile * $step + $start - 3600;
			$link_edit_alt = strftime("neuer Termin um %R Uhr", $link_edit_time);
		}
		
		if($compact)
			$tab[$zeile] .= "<tr>\n";
		
		// Vorspalte mit Uhrzeiten zusammenbauen
		if($precol){
			if(($i * $step) % 3600 == 0){
				$tab[$zeile] .= sprintf('<th%s%s%s><a class="precol1" href="calendar.php'
												, $width_precol_1_txt, $height_precol_1, $rowspan_precol);
				$tab[$zeile] .= sprintf('?cmd=edit&atime=%s">%s</a></th>'
												, $day_obj->getStart() + $i * $step, $i / (3600 / $step));
				$width_precol_1_txt = "";
			}
			// bei Intervallen mit vollen Stunden Minuten ausblenden
			if($step % 3600 != 0){
				$tab[$zeile] .= sprintf('<th%s%s><a class="precol2" href="calendar.php?cmd=edit&atime=%s">'
												, $width_precol_2_txt, $height_precol_2, ($day_obj->getStart() + $i * $step));
				$minute = ($zeile % (3600 / $step)) * ($step / 60);
				if($minute == 0)
					$tab[$zeile] .= "00</a></th>";
				else
					$tab[$zeile] .= $minute."</a></th>";
				$width_precol_2_txt = "";
			}
		}
		
		$link_notset = TRUE;				 
		if(!$term[$zeile]){
			if($link_edit){
				if($max_spalte > 0)
					$tab[$zeile] .= '<td class="steel1" align="right" colspan="'.($max_spalte + 1)
												.'"><a href="calendar.php?cmd=edit&atime='.($day_obj->getStart() + $i * $step)
												.'"><img src="./pictures/cal-link.gif" border="0" alt="'.$link_edit_alt.'"></a>'."</td>\n";
				else
					$tab[$zeile] .= "<td class=\"steel1\" align=\"right\"><a href=\"calendar.php?cmd=edit&atime="
												.($day_obj->getStart() + $i * $step)
												."\"><img src=\"./pictures/cal-link.gif\" border=\"0\" alt=\"".$link_edit_alt."\"></a></td>\n";
			}
			else{
				if($max_spalte > 1)
					$tab[$zeile] .= '<td class="steel1" colspan="'.$max_spalte.'"><font class="inday">&nbsp;</font>'."</td>\n";
				else
					$tab[$zeile] .= "<td class=\"steel1\"><font class=\"inday\">&nbsp;</font></td>\n";
			}
			
			$height = "";
			// Wenn bereits hier ein Link eingefuegt wurde braucht weiter unten keine
			// zusaetliche Spalte ausgegeben werden
			$link_notset = FALSE;
		}
		else{
			if($colsp[$zeile] > 0)
				$cspan = (int) ($max_spalte / $colsp[$zeile]);
			else
				$cspan = 0;
				
			$cspan_new = 0;
			for($j = 0;$j < $colsp[$zeile];$j++){
				$sp = 0;
				$n = 0;
				if($j + 1 == $colsp[$zeile])
					$cspan += $max_spalte % $colsp[$zeile];
					
				if(is_object($term[$zeile][$j])){
					
					// Wieviele Termine sind zum aktuellen Termin zeitgleich?
					$p = 0;
					$count = 0;
					while($aterm = $tmp_event[$p]){
						if($aterm->getStart() >= $term[$zeile][$j]->getStart() && $aterm->getStart() < $term[$zeile][$j]->getEnd())
							$count++;
						$p++;
					}
					
					if($count == 0){
						for($n = $j + 1;$n < $colsp[$zeile];$n++){
							if(!is_int($term[$zeile][$n])){
								$sp++;
							}
							else
								break;
						}
						$cspan += $sp;
					}
					
					$rows = ceil($term[$zeile][$j]->getDuration() / $step);
					$tab[$zeile] .= '<td';
					
					if($cspan > 1)
						$tab[$zeile] .= ' colspan="'.$cspan.'"';
					if($rows > 1)
						$tab[$zeile] .= ' rowspan="'.$rows.'"';
					
					$tab[$zeile] .= sprintf(' style="background-color:%s">', $term[$zeile][$j]->getColor());
					$tab[$zeile] .= "\n<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"1\">\n";
										
					if ($term[$zeile][$j]->getType() == 1
							&& $term[$zeile][$j]->getTitle() == "Kein Titel") {
						$title_out = $term[$zeile][$j]->getSemName();
					}
					else
						$title_out = $term[$zeile][$j]->getTitle();
					
					if($rows == 1){
						$title = fit_title($title_out,$colsp[$zeile],$rows,$title_length - 6);
						// calculating the correct height of the event-cell if the cell has 1 row
						$tab[$zeile] .= sprintf("<tr><td class=\"steel1\" height=\"%s\">\n"
													, $height_event - $padding);
						$tab[$zeile] .= sprintf('<a href="calendar.php?cmd=edit&termin_id=%s&atime=%d">'
													, $term[$zeile][$j]->getId()
													, ($day_obj->getStart() + $term[$zeile][$j]->getStart() % 86400));
						$tab[$zeile] .= sprintf("<font class=\"inday\">%s</font></a></td>\n", $title);
						$tab[$zeile] .= "<td class=\"steel1\" align=\"right\">&nbsp;</td></tr>\n";
					}
					else{
						$title = fit_title($title_out,$colsp[$zeile],$rows - 1,$title_length);
						// calculating the correct height of the event-cell if the cell has _more_ than 1 row
						$tab[$zeile] .= sprintf("<tr><td class=\"steel1\" height=\"%s\">\n"
													, ($height_event * ($rows - 1) + $spacing * ($rows - 1) - (2 * $padding)));
						$tab[$zeile] .= sprintf('<a href="calendar.php?cmd=edit&termin_id=%s&atime=%d">'
													, $term[$zeile][$j]->getId()
													, ($day_obj->getStart() + $term[$zeile][$j]->getStart() % 86400));
						$tab[$zeile] .= sprintf("<font class=\"inday\">%s</font></a></td></tr>\n", $title);
						$tab[$zeile] .= sprintf("<tr><td height=\"%s\" class=\"steel1\" align=\"right\">&nbsp;</td></tr>\n"
													, $height_event);
					}
					
					
					$tab[$zeile] .= "</table></td>\n";

					if($sp > 0){
						for($m = $zeile;$m < $rows + $zeile;$m++){
							$colsp[$m] = $colsp[$m] - $sp-1;
							$v = $j;
							while($term[$m][$v] == "#")
								$term[$m][$v] = 1;
						}
						$j = $n;
					}
				}
				
				elseif($term[$zeile][$j] == "#"){
					$csp = 0;
					while($term[$zeile][$j] == "#"){
						$csp += $cspan;
						$j++;
					}
					if($csp > 1)
						$colspan_attr = " colspan=\"$csp\"";
					elseif($csp == 1)
						$colspan_attr = "";
						
					if($link_edit)
						$tab[$zeile] .= sprintf('<td class="steel1"%s align="right"><a href="calendar.php?cmd=edit&atime=%s">'
																."<img src=\"./pictures/cal-link.gif\" border=\"0\" alt=\"".$link_edit_alt."\"></a></td>\n"
																, $colspan_attr, $day_obj->getStart() + $i * $step);
					else
						$tab[$zeile] .= "<td class=\"steel1\"$colspan_attr><font class=\"inday\">&nbsp;</font></td>\n";
						
					$height = "";
				}
				
				elseif($term[$zeile][$j] == ""){
					$csp = $max_spalte - $j;
					if($link_edit)
						$csp++;
					if($csp > 1)
						$colspan_attr = " colspan=\"$csp\"";
					elseif($csp == 1)
						$colspan_attr = "";
					
					if($link_edit)
						$tab[$zeile] .= sprintf('<td class="steel1"%s align="right"><a href="calendar.php?cmd=edit&atime=%s">'
															."<img src=\"./pictures/cal-link.gif\" border=\"0\" alt=\"".$link_edit_alt."\"></a></td>\n"
															, $colspan_attr, $day_obj->getStart() + $i * $step);
					else
						$tab[$zeile] .= "<td class=\"steel1\"$colspan_attr><font class=\"inday\">&nbsp;</font></td>\n";
					
					$link_notset = FALSE;
					$height = "";
					break;
				}
				
			}
	
		}
		
		if($link_edit && $link_notset)
			$tab[$zeile] .= "<td class=\"steel1\" width=\"1\" align=\"right\"><a href=\"calendar.php?cmd=edit&atime="
										. ($day_obj->getStart() + $i * $step)
										. "\"><img src=\"./pictures/cal-link.gif\" border=\"0\" alt=\"".$link_edit_alt."\"></a></td>\n";	
		
		if($compact)
			$tab[$zeile] .= "</tr>\n";
		
		// sonst zerlegt array_merge die Tabelle
		if(!isset($tab[$zeile]))
			$tab[$zeile] = "";
	
	}
		
	if($max_spalte == 0)
		$max_spalte = 1;
		
	if($link_edit && sizeof($tmp_event) > 0)
		$max_spalte++;
		
	if($precol){
		if($step >= 3600)
			$max_spalte++;
		else
			$max_spalte += 2;
	}
	
	$tab = array_merge($day_event_row, $tab);
	
	if($compact)
		$tab = implode("", $tab);
	
	return array("table" => $tab, "max_columns" => $max_spalte);
	
}

function maxValue($term, $st){
	$max_value = 0;
	for($i = 0;$i < sizeof($term);$i++){
		if(is_object($term[$i]))
			$max = ceil($term[$i]->getDuration() / $st);
		elseif($term[$i] == "#")
			continue;
		elseif($term[$i] > $max_value)
			$max = $term[$i];
		if($max > $max_value)
			$max_value = $max;
	}
	return $max_value;
}

// Tabellenansicht der Termine fuer eine Woche
function createWeekTable($week_obj, $start = 6, $end = 21, $step = 3600, $compact = TRUE, $link_edit = FALSE){
	$tab_arr = "";
	$tab = "";
	$max_columns = 0;
	$rows = ($end - $start + 1) * 3600 / $step;
	// calculating the maximum title length
	$length = ceil(125 / $week_obj->getType());
	
	for($i = 0;$i < $week_obj->type;$i++)
		$tab_arr[$i] = createDayTable($week_obj->wdays[$i],$start,$end,$step,FALSE,FALSE,$link_edit,$length,20,4,1);
		
	// weekday and date as title for each column
	for($i = 0;$i < $week_obj->getType();$i++){
		// add up all colums of each day
		$max_columns += $tab_arr[$i]["max_columns"];
		$dtime = $week_obj->wdays[$i]->getTs();
		if($week_obj->getType() == 5)
			$tab[0] .= '<th width="18%"';
		else
			$tab[0] .= '<th width="13%"';
			
		if($tab_arr[$i]["max_columns"] > 1)
			$tab[0] .= ' colspan="'.$tab_arr[$i]["max_columns"].'"';
		$tab[0] .= '><a href="calendar.php?cmd=showday&atime='.$dtime.'">';
		$tab[0] .= wday($dtime, "SHORT") . " " . date("d", $dtime) . "</a></th>\n";
	}
	if($compact)
		$tab[0] = '<tr>'.$tab[0].'</tr>';
		
	// put the table together
	for($i = 1;$i < $rows + 2;$i++){
		if($compact)
			$tab[$i] .= '<tr>';
		for($j = 0;$j < $week_obj->type;$j++){
			$tab[$i] .= $tab_arr[$j]["table"][$i - 1];
		}
		if($compact)
			$tab[$i] .= '</tr>';
	}
	
	if($compact)
		$tab = implode("", $tab);
			
	return array("table" => $tab, "max_columns" => $max_columns);

}

function jumpTo($month, $day, $year, $colsp = 1){
	global $atime, $cmd;
	
	?>
		<tr><td<? if($colsp > 1) echo " colspan=\"$colsp\""; ?>>&nbsp;</td></tr>
		<tr><td width="100%" align="center"<? if($colsp > 1) echo " colspan=\"$colsp\""; ?>>
			<blockquote>
				<form action="./calendar.php?cmd=<? echo $cmd; ?>" method="post">
	  			<b>Gehe zu:</b>&nbsp;&nbsp;<input type="text" name="jmp_d" size=2 maxlength="2" value="<? echo $day; ?>">
					&nbsp;.&nbsp;<input type="text" name="jmp_m" size=2 maxlength="2" value="<? echo $month; ?>">
					&nbsp;.&nbsp;<input type="text" name="jmp_y" size=4 maxlength="4" value="<? echo $year; ?>">
					&nbsp;<input type="image" src="./pictures/buttons/absenden-button.gif" border="0" align="absmiddle">
					<input type="hidden" name="atime" value="<? echo $atime; ?>">
				</form>
			</blockquote>
		</td></tr>
		<tr><td<? if($colsp > 1) echo " colspan=\"$colsp\""; ?>>&nbsp;</td></tr>
	<?
}

// verwendet variable Parameterliste
function includeMonth(){
	global $RELATIVE_PATH_CALENDAR;
	require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarMonth.class.php");
	global $imt;
	$ptime = func_get_arg(0);
	if($imt)
		$atime = $imt;
	else
		$atime = $ptime;
	switch(func_num_args()){
		case 4 :
			$js_include = " " . func_get_arg(3);
		case 3 :
			$arg = func_get_arg(2);
			if($arg == "NOKW")
				$mod = "NOKW";
			else
				$js_include = $arg;
		case 2 :
			$href = func_get_arg(1);
	}
		
	$amonth = new CalendarMonth($atime);
	$now = mktime(12,0,0,date("n", time()), date("j", time()), date("Y", time()));
	$width = "25";
	$height = "25";

	$ret = "<table class=\"blank\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\">";
	$ret .= "<tr><th>";
	$ret .= "<table border=\"0\" cellspacing=\"1\" cellpadding=\"1\">";
	$ret .= "<tr>";
	$ret .= sprintf("<th valign=\"top\"><a href=\"%s%s&imt=%s\">",
						$href, $ptime, ($amonth->getStart()-1));
	$ret .= "<img border=\"0\" src=\"./pictures/forumrotlinks.gif\" alt=\"zur&uuml;ck\"></a></th>";
	$ret .= "<th class=\"cal\" colspan=\"";
	if($mod == "nokw")
		$ret .= "5";
	else
		$ret .= "6";
	$ret .= "\" align=\"center\">";
	$ret .= sprintf("%s %s</th>\n", month($amonth->getStart()), $amonth->getYear());
	$ret .= sprintf("<th valign=\"top\"><a href=\"%s%s&imt=%s\">",
						$href, $ptime, ($amonth->getEnd()+1));
	$ret .= "<img border=\"0\" src=\"./pictures/forumrot.gif\" alt=\"vor\"></a></th>";
	$ret .= "</tr>\n";
	$ret .= "<tr>\n";
	$ret .= "<th class=\"inccal\" width=\"$width\">Mo</th>";
	$ret .= "<th class=\"inccal\" width=\"$width\">Di</th>";
	$ret .= "<th class=\"inccal\" width=\"$width\">Mi</th>";
	$ret .= "<th class=\"inccal\" width=\"$width\">Do</th>\n";
	$ret .= "<th class=\"inccal\" width=\"$width\">Fr</th>";
	$ret .= "<th class=\"inccal\" width=\"$width\">Sa</th>";
	$ret .= "<th class=\"inccal\" width=\"$width\">So</th>";
	if($mod != "nokw")
		$ret .= "<th class=\"inccal\" width=\"$width\">KW</th>";
	$ret .= "</tr>\n</table></th></tr>\n<tr><td class=\"blank\">";
	$ret .= "<table class=\"blank\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">";

	// Im Kalenderblatt ist links oben immer Montag. Das muss natuerlich nicht der
	// Monatserste sein. Es muessen evtl. noch Tage des vorangegangenen Monats
	// am Anfang und des folgenden Monats am Ende angefuegt werden.
	$adow = date("w", $amonth->getStart());
	if($adow == 0)
		$adow = 6;
	else
		$adow--;
	$first_day = $amonth->getStart() - $adow * 86400 + 43200;
	// Ist erforderlich, um den Maerz richtig darzustellen
	// Ursache ist die Sommer-/Winterzeit-Umstellung
	$cor = 0;
	if($amonth->getValue() == 3)
		$cor = 1;
		
	$last_day = ((42 - ($adow + date("t",$amonth->getStart()))) % 7 + $cor) * 86400
	 	        + $amonth->getEnd() - 43199;
						
	for($i = $first_day, $j = 0;$i <= $last_day;$i += 86400, $j++){
		$aday = date("j", $i);
		// Tage des vorangehenden und des nachfolgenden Monats erhalten andere
		// style-sheets
		$style = "";
		if(($aday - $j - 1 > 0) || ($j - $aday  > 6))
			$style = "light";
		
		// Feiertagsueberpruefung
		$hday = holiday($i);
		
		if($j % 7 == 0)
			$ret .= "<tr>";
		if($now == $i)
			$ret .= "<td class=\"current\" ";
		elseif (abs($atime - $i) < 3601)
			$ret .= "<td class=\"month\" ";
		else
			$ret .= "<td class=\"steel1\"";
		$ret .= sprintf("align=\"center\" width=\"%s\" height=\"%s\">", $width, $height);
		
		if(($j + 1) % 7 == 0){
			$ret .= sprintf("<a class=\"%ssdaymin\" href=\"%s%s\"%s>%s</a>",
								$style, $href, $i, $js_include, $aday);
			$ret .= "</td>";
			if ($mod != "nokw") {
				$ret .= sprintf("<td class=\"steel1\" align=\"center\" width=\"%s\" height=\"%s\">",
									$width, $height);
				$ret .= "<a href=\"./calendar.php?cmd=showweek&atime=$i\">";
				$ret .= sprintf("<font class=\"kwmin\">%s</font></a></td>", strftime("%V", $i));
			}
			$ret .= "</tr>\n";
		}
		else{
			// unterschiedliche Darstellung je nach Art des Tages (Rang des Feiertages)
			switch($hday["col"]){
				case 1:
				case 2:
					$ret .= sprintf('<a class="%shdaymin" href="%s%s"%s>%s</a>', $style, $href, $i, $js_include, $aday);
					break;
				case 3;
					$ret .= sprintf('<a class="%shdaymin" href="%s%s"%s>%s</a>', $style, $href, $i, $js_include, $aday);
					break;
				default:
					$ret .= sprintf('<a class="%sdaymin" href="%s%s"%s>%s</a>', $style, $href, $i, $js_include, $aday);
			}
			$ret .= "</td>";	
		}
	}
	$ret .= "</table></td></tr>\n";
	$ret .= "</table>\n";
	return $ret;
}

function fit_title($title, $cols, $rows, $max_length, $end_str = "...", $pad = TRUE){
	global $auth;
	if($auth->auth["jscript"])
		$max_length = $max_length * ($auth->auth["xres"] / 1024);
		
	$title_length = strlen($title);
	$length = ceil($max_length / $cols);
	$new_title = substr($title, 0, $length * $rows);
	
	if(strlen($new_title) < $title_length)
		$new_title = substr($new_title, 0, - (strlen($end_str))) . $end_str;
		
	$new_title = htmlentities(chunk_split($new_title, $length, "\n"),ENT_QUOTES);
	$new_title = substr(str_replace("\n","<br>",$new_title), 0, -4);
	
	if($pad && $title_length < $length)
		$new_title .= str_repeat("&nbsp;", $length - $title_length);
		
	return $new_title;
}	

?>
