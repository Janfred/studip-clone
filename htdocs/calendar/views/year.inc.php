<table width="100%" border="0" cellpadding="5" cellspacing="0">
	<tr><td class="blank" width="100%">
		<table class="blank" border=0 width="98%" cellpadding="0" cellspacing="0" align="center">
		<tr><td class="blank"><table width="100%" border=0 cellpadding=0 cellspacing=0><tr>
			<th align="center" width="10%"><a href="<? echo $PHP_SELF; ?>?cmd=showyear&atime=<? echo $ayear->getStart() - 1; ?>"><img border="0" src="./pictures/forumrotlinks.gif" alt="zur&uuml;ck">&nbsp;</a></th>
			<th class="cal" align="center" width="80%"><font size="+2"><b><? echo $ayear->getYear(); ?></b></font></th>
			<th align="center" width="10%"><a href="<? echo $PHP_SELF; ?>?cmd=showyear&atime=<? echo $ayear->getEnd() + 1; ?>"><img border="0" src="./pictures/forumrot.gif" alt="vor">&nbsp;</a></th>
			</tr></table></td>
		</tr>
		<tr><td class="blank"><table width="100%" border=0 cellpadding=2 cellspacing=1>
<?
	
		$days_per_month = array(31,31,28,31,30,31,30,31,31,30,31,30,31);											
		if(date("L", $ayear->getStart()))
			$days_per_month[2] = 29;
		
		echo '<tr>';
		for($i = 1;$i < 13;$i++){
			$ts_month += ($days_per_month[$i] - 1) * 86400;
			printf("<th width=\"8%%\"><a class=\"precol1\" href=\"%s?cmd=showmonth&atime=%s\">%s</a></th>",
				$PHP_SELF, ($ayear->getStart() + $ts_month), month($ts_month));
		}
		echo '</tr>';
		
		for($i = 1;$i < 32;$i++){
			echo '<tr>';
			for($month = 1;$month < 13;$month++){
				$aday = mktime(12,0,0,$month,$i,$ayear->getYear());
				
				if($i <= $days_per_month[$month]){
					$wday = date("w", $aday);
					if($wday == 0 || $wday == 6)
						$weekend = " class=\"weekend\"";
					else
						$weekend = " class=\"weekday\"";
						
					if($month == 1)
						printf("<td%s height=\"25\">", $weekend);
					else
						printf("<td%s>", $weekend);
					
					if($apps = $ayear->numberOfEvents($aday))
						printf("<table width=\"100%%\" cellspacing=\"0\" cellpadding=\"0\"><tr><td%s>",
							$weekend);
						
					// noch wird nicht nach Wichtigkeit bestimmter Feiertage unterschieden
					$hday = holiday($aday);
					switch($hday["col"]){
					
						case "1":
							if(date("w", $aday) == "0"){
								echo '<a class="sday" href="'.$PHP_SELF.'?cmd=showday&atime='.$aday
								    .'"><b>'.$i.'</b></a> '.wday($aday, "SHORT");
								$count++;
								}
							else
								echo '<a class="day" href="'.$PHP_SELF.'?cmd=showday&atime='.$aday
								    .'"><b>'.$i.'</b></a> '.wday($aday, "SHORT");
							break;
						case "2":
						case "3":
							if(date("w", $aday) == "0"){
								echo '<a class="sday" href="'.$PHP_SELF.'?cmd=showday&atime='.$aday
								    .'"><b>'.$i.'</b></a> '.wday($aday, "SHORT");
								$count++;
							}
							else
								echo '<a class="hday" href="'.$PHP_SELF.'?cmd=showday&atime='.$aday
								    .'"><b>'.$i.'</b></a> '.wday($aday, "SHORT");
							break;
						default:
							if(date("w", $aday) == "0"){
								echo '<a class="sday" href="'.$PHP_SELF.'?cmd=showday&atime='.$aday
								    .'"><b>'.$i.'</b></a> '.wday($aday, "SHORT");
								$count++;
								}
							else
								echo '<a class="day" href="'.$PHP_SELF.'?cmd=showday&atime='.$aday
								    .'"><b>'.$i.'</b></a> '.wday($aday, "SHORT");
					}
					
					if	($apps) {
						if	($apps > 1) {
							printf("</td><td%s align=\"right\">", $weekend);
							printf("<img src=\"./pictures/icon-uhr.gif\" alt=\"%s Termine\" title=\"%s Termine\">",
								$apps, $apps);
							echo "</td></table>";
						}
						else {
							printf("</td><td%s align=\"right\">", $weekend);
							echo "<img src=\"pictures/icon-uhr.gif\" alt=\"1 Termin\" title=\"1 Termin\">";
							echo "</td></table>";
						}
					}
					echo "</td>";
				}
				else
					echo "<td class=\"weekday\">&nbsp;</td>";
			}
			echo "</tr>\n";
			
		}
		echo "<tr>";
		$ts_month = 0;
		for($i = 1;$i < 13;$i++){
			$ts_month += ($days_per_month[$i] - 1) * 86400;
			echo '<th width="8%"><a class="precol1" href="'.$PHP_SELF.'?cmd=showmonth&atime='.($ayear->getStart() + $ts_month).'">'.month($ts_month).'</a></th>';
		}
		echo '</tr></table></td></tr>';
		jumpTo($jmp_m, $jmp_d, $jmp_y);
		echo "</table>\n</td></tr>";
		echo "<tr><td class=\"blank\" width=\"100%\">&nbsp;";
?>
