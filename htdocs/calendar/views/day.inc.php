<table width="100%" border="0" cellpadding="5" cellspacing="0">
	<tr><td class="blank" width="50%">
	<table width="100%" class="blank" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="blank" width="100%">
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
			<th width="10%" height="40"><a href="<? echo $PHP_SELF; ?>?cmd=showday&atime=<? echo $atime - 86400 ?>"><img border="0" src="./pictures/forumrotlinks.gif" alt="zur&uuml;ck"></a></th>
			<th width="80%" class="cal"><b>
		<?
			echo $aday->toString("LONG") . ", " . $aday->getDate();
			// event. Feiertagsnamen ausgeben
			if($hday = holiday($atime))
				echo '<br>' . $hday["name"];
		?>
			</b></th>
			<th width="10%"><a href="<? echo $PHP_SELF; ?>?cmd=showday&atime=<? echo $atime + 86400 ?>"><img border="0" src="./pictures/forumrot.gif" alt="vor"></a></th>
			</tr>
		<?
			if($st > 0)
				echo '<tr><th colspan="3"><a href="'.$PHP_SELF.'?cmd=showday&atime='.($atime - ($at - $st + 1) * 3600).'"><img border="0" src="./pictures/forumgraurauf.gif" alt="zeig davor"></a></th></tr>';
		?>
		</table>
	</td></tr>
	<tr><td class="blank">
		<table width="100%" border="0" cellpadding="3" cellspacing="1">
<?
		echo $tab["table"];
		if($et < 23)
			echo '<tr><th colspan="'.$tab["max_columns"].'"><a href="'.$PHP_SELF.'?cmd=showday&atime='.($atime + ($et - $at + 1) * 3600).'"><img border="0" src="./pictures/forumgraurunt.gif" alt="zeig danach"></a></th></tr>';
		else
			echo '<tr><th colspan="'.$tab["max_columns"].'">&nbsp;</th></tr>';
		echo '</table></td></tr></table><td width="50%" valign="top" class="blank">';
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		echo "<tr><td>\n";
		echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
		jumpTo($jmp_m, $jmp_d, $jmp_y);
		echo "</table></td></tr>\n";
		$link = "$PHP_SELF?cmd=showday&atime=";
		echo "<tr><td align=\"center\">".includeMonth($atime, $link)."</td></tr>\n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "</td></tr><tr><td class=\"blank\" width=\"100%\" colspan=\"2\">&nbsp;";
?>
