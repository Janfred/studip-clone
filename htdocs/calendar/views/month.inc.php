<table width="100%" border="0" cellpadding="5" cellspacing="0">
	<tr><td class="blank" width="100%">
	<table width="98%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr><td>&nbsp</td></tr><tr><td>
	<table class="blank" border="0" cellspacing="1" cellpadding="0" align="center">
	<tr><th>
	<table width="100%" border="0" cellspacing="1" cellpadding="1">
		<tr>
			<th>&nbsp;<a href="<? echo $PHP_SELF; ?>?cmd=showmonth&atime=<? echo $amonth->getStart()-1; ?>"><img border="0" src="./pictures/forumrotlinks.gif" alt="zur&uuml;ck"></a>&nbsp;</th>
			<th colspan=<? if($mod == "nokw") echo "5"; else echo "6"; ?> class="cal">
			<? echo month($amonth->getStart())." ".$amonth->getYear(); ?></th>
			<th>&nbsp;<a href="<? echo $PHP_SELF; ?>?cmd=showmonth&atime=<? echo $amonth->getEnd()+1; ?>"><img border="0" src="./pictures/forumrot.gif" alt="vor"></a>&nbsp;</th>
		</tr>
		<tr>
		<? echo "<th width=$width>Mo</th><th width=$width>Di</th><th width=$width>Mi</th><th width=$width>Do</th>
						<th width=$width>Fr</th><th width=$width>Sa</th><th width=$width>So</th>";
			 if($mod != "nokw")
			  echo "<th width=$width>KW</th>";
		?>
		</tr>
	</table></th></tr>
		<tr><td class="blank">
		<table class="blank" border="0" cellspacing="1" cellpadding="1">
		
<?
		// Im Kalenderblatt ist links oben immer Montag. Das muss natuerlich nicht der
		// Monatserste sein. Es muessen evtl. noch Tage des vorangegangenen Monats
		// am Anfang und des folgenden Monats am Ende angefuegt werden.
		
		$adow = strftime("%u", $amonth->getStart()) - 1;
		
		$first_day = $amonth->getStart() - $adow * 86400 + 43200;
		// Ist erforderlich, um den Maerz richtig darzustellen
		// Ursache ist die Sommer-/Winterzeit-Umstellung
		$cor = 0;
		if($amonth->getMonth() == 3)
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
			if($mod != "compact" && $mod != "nokw")
				$hday = holiday($i);
			
			// wenn Feiertag dann nur 4 Termine pro Tag ausgeben, sonst wird zu eng
			if($hday["col"] > 0)
				$max_apps = 4;
			else
				$max_apps = 5;
				
			if($j % 7 == 0)
				echo '<tr>';
			echo '<td class="'.$style.'month" valign=top width='.$width.' height='.$height.'>&nbsp;';
			
			if(($j + 1) % 7 == 0){
				echo '<a class="' . $style . 'sday" href="'.$PHP_SELF.'?cmd=showday&atime=' . $i . '">'
					  	   . $aday . "</a>";
				monthUpDown($amonth, $i, $step, $max_apps);
				if($hday["name"] != "")
					echo '<br><font class="inday">' . $hday["name"] . '</font>';
				$count = 0;
				while(($aterm = $amonth->nextTermin($i)) && $count < $max_apps){
					$html_txt = fit_title($aterm->getTitle(),1,1,15);
					$jscript_txt = "'',CAPTION,'".JSReady($aterm->getTitle())."',NOCLOSE,CSSOFF";
					echo '<br><a class="inday" href="'.$PHP_SELF.'?cmd=edit&termin_id='.$aterm->getId().'" onmouseover="return overlib('.$jscript_txt
					     .');" onmouseout="nd();"><font color="'.$aterm->getColor().'">'
							 .$html_txt."</font><a>\n";
					$count++;
				}
				echo "</td>";
				if($mod != "nokw")
					echo '<td align=center width='.$width.' height='.$height.'><a class="kw" href="'.$PHP_SELF.'?cmd=showweek&atime=' . $i . '">'
							 	 . strftime("%V", $i)."</a></td>";
				echo "</tr>\n";
			}
			else{
				// unterschiedliche Darstellung je nach Art des Tages (Rang des Feiertages)
				switch($hday["col"]){
					case 1:
						echo '<a class="'.$style.'day" href="'.$PHP_SELF.'?cmd=showday&atime='.$i.'">'.$aday."</a>\n";
						monthUpDown($amonth, $i, $step, $max_apps);
						echo '<br><font class="inday">'.$hday["name"].'</font>';
						break;
					case 2:
						echo '<a class="'.$style.'hday" href="'.$PHP_SELF.'?cmd=showday&atime='.$i.'">'.$aday."</a>\n";
						monthUpDown($amonth, $i, $step, $max_apps);
						echo '<br><font class="inday">'.$hday["name"].'</font>';
						break;
					case 3;
						echo '<a class="'.$style.'hday" href="'.$PHP_SELF.'?cmd=showday&atime='.$i.'">'.$aday."</a>\n";
						monthUpDown($amonth, $i, $step, $max_apps);
						echo '<br><font class="inday">' . $hday["name"] . '</font>';
						break;
					default:
						echo '<a class="'.$style.'day" href="'.$PHP_SELF.'?cmd=showday&atime='.$i.'">'.$aday."</a>\n";
						monthUpDown($amonth, $i, $step, $max_apps);
				}
				
				$count = 0;
				while(($aterm = $amonth->nextTermin($i)) && $count < $max_apps){
					$html_txt = fit_title($aterm->getTitle(),1,1,15);
					$jscript_txt = "'',CAPTION,'".JSReady($aterm->getTitle()).'&nbsp;&nbsp;&nbsp;&nbsp;'.strftime("%H:%M-",$aterm->getStart()).strftime("%H:%M",$aterm->getEnd())."',NOCLOSE,CSSOFF";
					echo '<br><a class="inday" href="'.$PHP_SELF.'?cmd=edit&termin_id='.$aterm->getId().'&atime='.$i.'" onmouseover="return overlib('.$jscript_txt
					     .');" onmouseout="return nd();"><font color="'.$aterm->getColor().'">'
							 .$html_txt."</font></a>";
					$count++;
				}
				
				echo "</td>";
				
			}
		}
?>
		</td></tr></table></td></tr>
		<tr><th>&nbsp;</th></tr>
<?
		echo '</table></td></table><table width="98%" border="0" cellpadding="0" cellspacing="0" align="center">';
		jumpTo($jmp_m, $jmp_d, $jmp_y);
		echo "</table>\n";
		echo "<tr><td class=\"blank\">&nbsp;";
?>
