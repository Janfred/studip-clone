<table width="100%" border="0" cellpadding="0" cellspacing="0">
	
<?
//echo '<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">';
		
if(!empty($err)){
	$error_sign = "<font color='FF0000' size='+2'><b>&nbsp;*&nbsp;</b></font>";
	$error_message = "Bitte korrigieren Sie die mit $error_sign gekennzeichneten Felder.".$err_message;
	my_info($error_message, "blank", 0);
}
?>

<tr>
	<td class="blank" width="100%">
		<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
			<tr><td width="100%" colspan="2" class="steel2">
<?

echo $edit_mode_out;

$css_switcher = new cssClassSwitcher();
$css_switcher->switchClass();

?>
			</td></tr>
			<form action="<? echo $PHP_SELF; ?>?cmd=edit" method=post>
			<tr>
				<td width="80%" valign="top">
				<table width="100%" cellpadding="4" cellspacing="0" border="0">
				<tr>
				 <td class="<? echo $css_switcher->getClass(); ?>">
					<p>
						<table border="0" cellspacing="2" cellpadding="2">
							<tr valign="bottom">
								<td><b>Beginn: </b></td>
								<td>am <input type="text" name="start_day" size="2" maxlength="2" value="<? echo $start_day; ?>">
								.&nbsp;<input type="text" name="start_month" size="2" maxlength="2" value="<? echo $start_month; ?>">
								.&nbsp;<input type="text" name="start_year" size="4" maxlength="4" value="<? echo $start_year; ?>">&nbsp;um&nbsp;<select name="start_h" size="1">
<?
		for($i = 0;$i <= 23;$i++){
			echo "<option";
			if($i == $start_h)
				echo " selected";
			echo ">$i";
		}
		
		echo "</select>&nbsp;:&nbsp;<select name=\"start_m\" size=\"1\">";
		
		for($i = 0;$i <= 55;$i += 5){
			echo "<option";
			if($i == $start_m)
				echo " selected";
			echo ">$i";
		}
?>	
								</select> Uhr<? echo $err["start_time"]?$error_sign:""; ?></td>
							</tr><tr valign="bottom">
								<td><b>Ende: </b></td>
								<td>am <input type="text" name="end_day" size="2" maxlength="2" value="<? echo $end_day; ?>">
								.&nbsp;<input type="text" name="end_month" size="2" maxlength="2" value="<? echo $end_month; ?>">
								.&nbsp;<input type="text" name="end_year" size="4" maxlength="4" value="<? echo $end_year; ?>">&nbsp;um&nbsp;<select name="end_h" size="1">
<?
		for($i = 0;$i <= 23;$i++){
			echo "<option";
			if($i == $end_h)
				echo " selected";
			echo ">$i";
		}
		
		echo "</select>&nbsp;:&nbsp;<select name=\"end_m\" size=\"1\">";
		
		for($i = 0;$i <= 55;$i += 5){
			echo "<option";
			if($i == $end_m)
				echo " selected";
			echo ">$i";
		}
?>
								</select> Uhr<? echo $err["end_time"]?$error_sign:""; ?></td>
							</tr>
						</table>
					</p>
				</td>
			</tr>
			<tr><? $css_switcher->switchClass(); ?>
				<td class="<? echo $css_switcher->getClass(); ?>">
					<p>
						<table border="0" width="100%" cellpadding="2" cellspacing="2">
							<tr><td width="15%"><b>Termin: </b></td>
								<td width="85%"><input type="text" name="txt" size="50" maxlength="255" value="<? echo $txt; ?>"></input><? echo $err["titel"]?$error_sign:""; ?></td>
							</tr><tr>
								<td width="15%"><b>Beschreibung: </b></td>
								<td width="85%"><textarea name="content" cols="55" rows="5" wrap="virtual"><? echo $content; ?></textarea></td>
							</tr>
						</table>
					</p>
				</td>
			</tr>
			<tr><? $css_switcher->switchClass(); ?>
				<td class="<? echo $css_switcher->getClass(); ?>">
					<p>
						<table border="0" width="<? if(isset($atermin) && $atermin->getSeminarId()) echo "50%"; else echo "80%"; ?>" cellpadding="2" cellspacing="2">
							<tr>
								<td>
									<b>Kategorie: </b>
								</td><td>
									<select name="cat" size="1">
									<?
										if(isset($atermin) && $atermin->getSeminarId()){
											if(!isset($cat))
												$cat = 1;
											echo '<option value="'.$cat.'" selected>'.$TERMIN_TYP[$cat]["name"];
										}
										else{
											if(!isset($cat))
												$cat = 1;
											for($i = 1;$i < sizeof($PERS_TERMIN_KAT);$i++){
												echo '<option value="'.$i.'"';
												if($cat == $i)
													echo " selected";
												echo ">".$PERS_TERMIN_KAT[$i]["name"]."\n";
											}
										}
									?>
									</select>
								</td>
								<? if(isset($atermin) && $atermin->getSeminarId()) echo '<td>&nbsp</td>';
										else{?>
								<td>
									<b>Sichtbarkeit: </b>
								</td><td>
									<input type="radio" name="via" value="private"<? if($via == "private") echo " checked"; ?>>&nbsp;privat&nbsp;
									<input type="radio" name="via" value="public"<? if($via == "public") echo " checked"; ?>>&nbsp;&ouml;ffentlich
								</td>
								<? } ?>
							</tr>
								<td>
									<b>Raum: </b>
								</td><td>
									<input type="text" name="loc" size="30" maxlength="255" value="<? echo $loc; ?>">
								</td>
								<? if(isset($atermin) && $atermin->getSeminarId()) echo '<td>&nbsp</td>';
										else{?>
								<td>
									<b>Priorit&auml;t: </b>
								</td><td>
									<select name="priority" size="1">
										<option value="1"<? if($priority == 1) echo " selected"; ?>>1
										<option value="2"<? if($priority == 2) echo " selected"; ?>>2
										<option value="3"<? if($priority == 3) echo " selected"; ?>>3
										<option value="4"<? if($priority == 4) echo " selected"; ?>>4
										<option value="5"<? if($priority == 5) echo " selected"; ?>>5
									</select>
								</td>
								<? } ?>
							</tr>
						</table>
					</p>
				</td>
			</tr>
<?
	switch($mod){
		case "DAYLY":
			?>
			<tr><? $css_switcher->switchClass(); ?>
				<td class="<? echo $css_switcher->getClass(); ?>">
				<p>
					<table width="100%" border="0" cellpadding="2" cellspacing="2">
						<tr><td width="30%"><input type="radio" name="type_d" value="dayly"<?if($type_d == "dayly" || $type_d == "") echo " checked"; ?>>&nbsp;<b>Alle</b>&nbsp;
						<input type="text" name="lintervall_d" size="3" maxlength="3" value="<? echo $lintervall_d?$lintervall_d:1; ?>">&nbsp;Tage<? echo $err["lintervall_d"]?$error_sign:""; ?></td>
						<td width="70%"><input type="radio" name="type_d" value="wdayly"<?if($type_d == "wdayly") echo " checked"; ?>>&nbsp;<b>Jeden Werktag</b></td>
						</tr>
					</table>
				</p>
				</td>
			</tr>
			<?
			break;
		case "WEEKLY":
			if(!$wdays)
				$wdays = array();
			?>
			<tr><? $css_switcher->switchClass(); ?>
				<td class="<? echo $css_switcher->getClass(); ?>">
				<p>
					<table width="100%" border="0" cellpadding="2" cellspacing="2">
						<tr><td colspan="5"><b>Alle </b><input type="text" name="lintervall_w" size="3" maxlength="3" value="<? echo $lintervall_w?$lintervall_w:1; ?>"><b> Wochen</b><? echo $err["lintervall_w"]?$error_sign:""; ?></td>
						</tr><tr>
							<td rowspan="2" width="20%" align="center"><b>am:&nbsp;</b></td>
							<td width="20%"><input type="checkbox" name="wdays[]" value="1"<? if(in_array(1, $wdays)) echo " checked"; ?>><b>&nbsp;Montag</b></td>
							<td width="20%"><input type="checkbox" name="wdays[]" value="2"<? if(in_array(2, $wdays)) echo " checked"; ?>><b>&nbsp;Dienstag</b></td>
							<td width="20%"><input type="checkbox" name="wdays[]" value="3"<? if(in_array(3, $wdays)) echo " checked"; ?>><b>&nbsp;Mittwoch</b></td>
							<td width="20%"><input type="checkbox" name="wdays[]" value="4"<? if(in_array(4, $wdays)) echo " checked"; ?>><b>&nbsp;Donnerstag</b></td>
						</tr><tr>
							<td width="20%"><input type="checkbox" name="wdays[]" value="5"<? if(in_array(5, $wdays)) echo " checked"; ?>><b>&nbsp;Freitag</b></td>
							<td width="20%"><input type="checkbox" name="wdays[]" value="6"<? if(in_array(6, $wdays)) echo " checked"; ?>><b>&nbsp;Samstag</b></td>
							<td colspan="2" width="40%"><input type="checkbox" name="wdays[]" value="7"<? if(in_array(7, $wdays)) echo " checked"; ?>><b>&nbsp;Sonntag</b></td>
						</tr>
					</table>
				</p>
				</td>
			</tr>
			<?
			break;
		case "MONTHLY":
			?>
			<tr><? $css_switcher->switchClass(); ?>
				<td class="<? echo $css_switcher->getClass(); ?>">
				<p>
					<table width="100%" border="0" cellpadding="2" cellspacing="2">
						<tr><td width="15%"><input type="radio" name="type_m" value="day"<? if($type_m == "day" || $type_m == "") echo " checked"; ?>>&nbsp;<b>An jedem</b>&nbsp;</td>
							<td width="10%"><input type="text" name="day_m" size="2" maxlength="2" value="<? echo $day_m?$day_m:$start_day; ?>"><? echo $err["day_m"]?$error_sign:""; ?>&nbsp;.&nbsp;&nbsp;alle&nbsp;</td>
							<td width="10%"><input type="text" name="lintervall_m1" size="3" maxlength="3" value="<? echo $lintervall_m1?$lintervall_m1:1; ?>"><? echo $err["lintervall_m1"]?$error_sign:""; ?>&nbsp;Monate</td>
							<td width="65%">&nbsp;</td>
						</tr><tr>
							<td><input type="radio" name="type_m" value="wday"<? if($type_m == "wday") echo " checked"; ?>>&nbsp;<b>Jeden</b>&nbsp;</td>
							<td>
								<select name="sintervall_m" size="1">
									<option value="1"<? if($sintervall_m == 1) echo " selected"; ?>>ersten
									<option value="2"<? if($sintervall_m == 2) echo " selected"; ?>>zweiten
									<option value="3"<? if($sintervall_m == 3) echo " selected"; ?>>dritten
									<option value="4"<? if($sintervall_m == 4) echo " selected"; ?>>vierten
									<option value="5"<? if($sintervall_m == 5) echo " selected"; ?>>letzten
								</select>
							</td><td>
								<select name="wday_m" size="1">
									<option value="1"<? if($wday_m == 1) echo " selected"; ?>>Montag
									<option value="2"<? if($wday_m == 2) echo " selected"; ?>>Dienstag
									<option value="3"<? if($wday_m == 3) echo " selected"; ?>>Mittwoch
									<option value="4"<? if($wday_m == 4) echo " selected"; ?>>Donnerstag
									<option value="5"<? if($wday_m == 5) echo " selected"; ?>>Freitag
									<option value="6"<? if($wday_m == 6) echo " selected"; ?>>Samstag
									<option value="7"<? if($wday_m == 7) echo " selected"; ?>>Sonntag
								</select>&nbsp;alle&nbsp;</td>
							<td><input type="text" name="lintervall_m2" size="3" maxlength="3" value="<? echo $lintervall_m2?$lintervall_m2:1; ?>"><? echo $err["lintervall_m2"]?$error_sign:""; ?>&nbsp;Monate</td>
						</tr>
					</table>
				</p>
				</td>
			</tr>
			<?
			break;
		case "YEARLY":
			if(!$month_y1)
				$month_y1 = $start_month;
			if(!$month_y2)
				$month_y2 = $start_month;
			
			?>
			<tr><? $css_switcher->switchClass(); ?>
				<td class="<? echo $css_switcher->getClass(); ?>">
				<p>
					<table width="100%" border="0" cellpadding="2" cellspacing="2">
						<tr><td width="100%" colspan="4">
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr><td width="15%"><input type="radio" name="type_y" value="day"<? if($type_y == "day" || $type_y == "") echo " checked"; ?>>&nbsp;<b>Jeden</b>&nbsp;</td>
									<td width="5%"><input type="text" name="day_y" size="2" maxlength="2" value="<? echo $day_y?$day_y:$start_day; ?>"><? echo $err["day_y"]?$error_sign:""; ?>&nbsp;.&nbsp;</td>
									<td width="85%">
										<select name="month_y1" size="1">
											<option value="1"<? if($month_y1 == 1) echo " selected"; ?>>Januar
											<option value="2"<? if($month_y1 == 2) echo " selected"; ?>>Februar
											<option value="3"<? if($month_y1 == 3) echo " selected"; ?>>M&auml;rz
											<option value="4"<? if($month_y1 == 4) echo " selected"; ?>>April
											<option value="5"<? if($month_y1 == 5) echo " selected"; ?>>Mai
											<option value="6"<? if($month_y1 == 6) echo " selected"; ?>>Juni
											<option value="7"<? if($month_y1 == 7) echo " selected"; ?>>Juli
											<option value="8"<? if($month_y1 == 8) echo " selected"; ?>>August
											<option value="9"<? if($month_y1 == 9) echo " selected"; ?>>September
											<option value="10"<? if($month_y1 == 10) echo " selected"; ?>>Oktober
											<option value="11"<? if($month_y1 == 11) echo " selected"; ?>>November
											<option value="12"<? if($month_y1 == 12) echo " selected"; ?>>Dezember
										</select>
									</td>
								</tr>
							</table></td>
						</tr><tr>
							<td width="15%"><input type="radio" name="type_y" value="wday"<? if($type_y == "wday") echo " checked"; ?>>&nbsp;<b>Jeden</b>&nbsp;</td>
							<td width="10%">
								<select name="sintervall_y" size="1">
									<option value="1"<? if($sintervall_y == 1) echo " selected"; ?>>ersten
									<option value="2"<? if($sintervall_y == 2) echo " selected"; ?>>zweiten
									<option value="3"<? if($sintervall_y == 3) echo " selected"; ?>>dritten
									<option value="4"<? if($sintervall_y == 4) echo " selected"; ?>>vierten
									<option value="5"<? if($sintervall_y == 5) echo " selected"; ?>>letzten
								</select>
							</td><td width="10%">
								<select name="wday_y" size="1">
									<option value="1"<? if($wday_y == 1) echo " selected"; ?>>Montag
									<option value="2"<? if($wday_y == 2) echo " selected"; ?>>Dienstag
									<option value="3"<? if($wday_y == 3) echo " selected"; ?>>Mittwoch
									<option value="4"<? if($wday_y == 4) echo " selected"; ?>>Donnerstag
									<option value="5"<? if($wday_y == 5) echo " selected"; ?>>Freitag
									<option value="6"<? if($wday_y == 6) echo " selected"; ?>>Samstag
									<option value="7"<? if($wday_y == 7) echo " selected"; ?>>Sonntag
								</select>&nbsp;im&nbsp;</td>
							<td width="65%">
								<select name="month_y2" size="1">
									<option value="1"<? if($month_y2 == 1) echo " selected"; ?>>Januar
									<option value="2"<? if($month_y2 == 2) echo " selected"; ?>>Februar
									<option value="3"<? if($month_y2 == 3) echo " selected"; ?>>M&auml;rz
									<option value="4"<? if($month_y2 == 4) echo " selected"; ?>>April
									<option value="5"<? if($month_y2 == 5) echo " selected"; ?>>Mai
									<option value="6"<? if($month_y2 == 6) echo " selected"; ?>>Juni
									<option value="7"<? if($month_y2 == 7) echo " selected"; ?>>Juli
									<option value="8"<? if($month_y2 == 8) echo " selected"; ?>>August
									<option value="9"<? if($month_y2 == 9) echo " selected"; ?>>September
									<option value="10"<? if($month_y2 == 10) echo " selected"; ?>>Oktober
									<option value="11"<? if($month_y2 == 11) echo " selected"; ?>>November
									<option value="12"<? if($month_y2 == 12) echo " selected"; ?>>Dezember
								</select>
							</td>
						</tr>
					</table>
				</p>
				</td>
			</tr>
			<?
			break;
	}
	if($mod != "SINGLE"){
?>
<tr><? $css_switcher->switchClass(); ?>
<td class="<? echo $css_switcher->getClass(); ?>">
	<p>
	<table>
		<tr>
			<td><b>Verliert G&uuml;ltigkeit: </b></td>
			<td>
				<select name="exp_c" size=1>
					<option value="never"<? if($exp_c == "never") echo " selected"; ?>>Nie
					<option value="date"<? if($exp_c == "date") echo " selected"; ?>>am rechts anzugebenden Datum
				</select>
			</td>
			<td><input type="text" size="2" maxlength="2" name="exp_day" value="<? echo ($exp_day && $exp_c == "date")?$exp_day:"TT"; ?>">&nbsp;.&nbsp;</td>
			<td><input type="text" size="2" maxlength="2" name="exp_month" value="<? echo ($exp_month && $exp_c == "date")?$exp_month:"MM"; ?>">&nbsp;.&nbsp;</td>
			<td><input type="text" size="4" maxlength="4" name="exp_year" value="<? echo ($exp_year && $exp_c == "date")?$exp_year:"JJJJ"; ?>"><? echo $err["exp_time"]?$error_sign:""; ?></td>
		</tr>
	</table>
	</p>
</td>
</tr>
<?
	}
?>
</table>
	</td><td width="20%" valign="top" class="steel1">
		<table width="100%" border="0" cellspacing="2" cellpadding="2">
<?
	if(isset($atermin) && $atermin->getSeminarId()){
		$db = new DB_Seminar;
		$query = "SELECT name FROM seminare WHERE Seminar_id=\"".$atermin->getSeminarId()."\"";
		$db->query($query);
		$db->next_record();
?>
			<tr><td class="steel1" align="center"><b>Veranstaltungstermin<br>&nbsp;<b></td></tr>
			<tr><td class="steel1">
				Dieser Termin geh&ouml;rt zur Veranstaltung:
				<blockquote>
					<a href="./seminar_main.php?auswahl=<? echo $atermin->getSeminarId().'">'.fit_title($db->f("name"), 1, 1, 120, "...", FALSE); ?></a>
				</blockquote>
				<p>Veranstaltungstermine k&ouml;nnen nicht im pers&ouml;nlichen Terminkalender bearbeitet werden.</p>
<?
		$permission = get_perm($atermin->getSeminarId());
		if($permission == "tutor" || $permission == "dozent")
			echo 'Um diesen Termin zu bearbeiten, wechseln Sie bitte in die <a href="./admin_dates.php?range_id='.$atermin->getSeminarId().'&ebene=sem">Terminverwaltung</a>.';
		echo "</td></tr>\n";
 	}
	else{
?>
			<tr><td class="steel1" align="center"><b>Wiederholung</b></td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "SINGLE" || $mod == "SINGLE")
					echo '<input type="image" name="mod_s" src="./pictures/buttons/keine2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_s" src="./pictures/buttons/keine-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "DAYLY" || $mod == "DAYLY")
					echo '<input type="image" name="mod_d" src="./pictures/buttons/jedentag2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_d" src="./pictures/buttons/jedentag-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "WEEKLY" || $mod == "WEEKLY")
					echo '<input type="image" name="mod_w" src="./pictures/buttons/jedewoche2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_w" src="./pictures/buttons/jedewoche-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "MONTHLY" || $mod == "MONTHLY")
					echo '<input type="image" name="mod_m" src="./pictures/buttons/jedenmonat2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_m" src="./pictures/buttons/jedenmonat-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "YEARLY" || $mod == "YEARLY")
					echo '<input type="image" name="mod_y" src="./pictures/buttons/jedesjahr2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_y" src="./pictures/buttons/jedesjahr-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1"><br>&nbsp;<br></td></tr>
<?
	if($atime && !$termin_id){?>
		<tr><td class="steel1" align="center">
			<input type="hidden" name="atime" value="<? echo $atime; ?>">
			<input type="hidden" name="mod_err" value="<? echo $mod_err; ?>">
			<input type="hidden" name="mod_prv" value="<? echo $mod; ?>">
			<input type="hidden" name="cmd" value="add">
			<input type="image" src="./pictures/buttons/terminspeichern-button.gif" border="0"></td>
		</tr>
	</form>
	<?}
	else{?>
		<tr><td class="steel1" align="center">
			<input type="hidden" name="termin_id" value="<? echo $termin_id; ?>">
			<input type="hidden" name="atime" value="<? echo $atime; ?>">
			<input type="hidden" name="mod_err" value="<? echo $mod_err; ?>">
			<input type="hidden" name="mod_prv" value="<? echo $mod; ?>">
			<input type="hidden" name="cmd" value="add">
			<input type="image" src="./pictures/buttons/terminaendern-button.gif" border="0"></td>
		</tr>
		<tr><td class="steel1">&nbsp;</td></tr>
	</form>
	<?
			echo '<tr><td class="steel1" align="center"><form action="'.$PHP_SELF.'?cmd=del" method="post">'."\n";
			echo '<input type="hidden" name="termin_id" value="'.$termin_id."\">\n";
			echo '<input type="hidden" name="atime" value="'.$atime."\">\n";
			echo '<input type="image" src="./pictures/buttons/loeschen-button.gif" border="0"></form></td></tr>';
		}
	}
		echo '</table></td></tr></table><br />';
?>
