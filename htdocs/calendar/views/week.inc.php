<table width="100%" border="0" cellpadding="5" cellspacing="0" align="center">
	<tr><td class="blank" width="100%" align="center">
	<table border="0" width="100%" cellspacing="1" cellpadding="2">
		<tr><th colspan="<? echo $colspan_2; ?>"><table width="100%" border="0" cellpadding="2" cellspacing="0" align="center"><tr>
			<th width="15%"><a href="<? echo $PHP_SELF; ?>?cmd=showweek&atime=<? echo $aweek->getStart() - 1; ?>">&nbsp;<img border="0" src="./pictures/forumrotlinks.gif" alt="zur&uuml;ck">&nbsp;</a></th>
			<th width="70%" class="cal"><? echo strftime("%V. Woche vom ", $aweek->getStart()).date("d.m.Y", $aweek->getStart()); ?> bis <? echo date("d.m.Y", $aweek->getEnd()); ?></th>
			<th width="15%"><a href="<? echo $PHP_SELF; ?>?cmd=showweek&atime=<? echo $aweek->getEnd() + 259201; ?>">&nbsp;<img border="0" src="./pictures/forumrot.gif" alt="vor">&nbsp;</a></th>
			</tr></table></th>
		</tr>
		
<?
		printf('<tr><th width="4%%"%s>', $colspan_1);
		if($st > 0){
			echo '<a href="calendar.php?cmd=showweek&atime='.$atime.'&wtime='.($st - 1).'">';
			echo '<img border="0" src="./pictures/forumgraurauf.gif" alt="zeig davor"></a>';
		}
		else
			echo "&nbsp";
		echo '</th>'.$tab["table"][0];
		printf('<th width="4%%"%s>', $colspan_1);
		if($st > 0){
			echo '<a href="calendar.php?cmd=showweek&atime='.$atime.'&wtime='.($st - 1).'">';
			echo '<img border="0" src="./pictures/forumgraurauf.gif" alt="zeig davor"></a>';
		}
		else
			echo "&nbsp;";
		echo '</th></tr>';
		
		// Zeile mit Tagesterminen ausgeben
		printf('</tr><th%s>Tag</th>%s<th%s>Tag</th></tr>', $colspan_1, $tab["table"][1], $colspan_1);
		
		
		$j = $st;
		for($i = 2;$i < sizeof($tab["table"]);$i++){
			echo "<tr>";
			
			if($i % $rowspan == 0){
				if($rowspan == 1)
					echo "<th".$height.">".$j."</th>";
				else
					echo "<th rowspan=\"$rowspan\">".$j."</th>";
			}
			if($rowspan > 1){
				$minutes = (60 / $rowspan) * ($i % $rowspan);
				if($minutes == 0)
					$minutes = "00";
				echo "<th".$height."><font size=\"-2\">".$minutes."</font></th>";
			}
			
			echo $tab["table"][$i];
			
			if($rowspan > 1)
				echo '<th><font size="-2">'.$minutes.'</font></th>';
			if($i % $rowspan == 0){
				if($rowspan == 1)
					echo "<th>".$j."</th>";
				else
					echo "<th rowspan=\"$rowspan\">".$j."</th>";
				$j = $j + ceil($calendar_user_control_data["step_week"] / 3600);
			}
			
			echo "</tr>\n";
		}
		echo '<tr><th colspan="'.$colspan_2."\">\n";
		echo '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
		echo '<tr><th width="4%">';
		if($et < 23){
			echo '<a href="calendar.php?cmd=showweek&atime='.$atime.'&wtime='.($et + 1).'">';
			echo '<img border="0" src="./pictures/forumgraurunt.gif" alt="zeig danach"></a>';
		}
		else
			echo "&nbsp";
		echo '</th><th width="92%">&nbsp;</th>';
		echo '<th width="4%">';
		if($et < 23){
			echo '<a href="calendar.php?cmd=showweek&atime='.$atime.'&wtime='.($et + 1).'">';
			echo '<img border="0" src="./pictures/forumgraurunt.gif" alt="zeig danach"></a>';
		}
		else
			echo "&nbsp;";
		echo "</th></tr></table>\n";
		echo "</th></tr></table>\n";
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" align="center">';
		jumpTo($jmp_m, $jmp_d, $jmp_y);
		echo "</table>\n";
		echo "<tr><td class=\"blank\">&nbsp;";
?>
