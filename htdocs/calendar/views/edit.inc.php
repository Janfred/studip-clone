
<table width="100%" class="blank" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="blank" width="100%" valign="top">
<?
		
if (!empty($err)) {
	$error_sign = "<font color=\"#FF0000\" size=\"+2\"><b>&nbsp;*&nbsp;</b></font>";
	$error_message = sprintf("Bitte korrigieren Sie die mit %s gekennzeichneten Felder.%s",
		$error_sign, $err_message);
	my_info($error_message, "blank", 0);
}

//echo "<tr>\n<td class=\"blank\" width=\"100%\" valign=\"top\">\n";
echo "<table class=\"blank\" width=\"100%\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\" align=\"center\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
echo "<table class=\"blank\" width=\"99%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";

if (isset($atermin) && $atermin->getSeminarId())
	echo "<tr><td width=\"100%\" class=\"steel2\">";
else
	echo "<tr><td width=\"100%\" colspan=\"2\" class=\"steel2\">";


echo $edit_mode_out;

$css_switcher = new cssClassSwitcher();
$css_switcher->switchClass();

echo "\n</td></tr>\n";
echo "<form action=\"$PHP_SELF?cmd=edit\" method=\"post\">";
echo "<tr>\n<td class=\"steel1\" width=\"80%\" valign=\"top\">\n";
echo "<table width=\"100%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
printf("<tr>\n<td class=\"%s\">\n", $css_switcher->getClass());

echo "<p>\n<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
echo "<tr valign=\"bottom\">\n";
printf("<td><b>%s </b></td>", "Beginn:");
echo "<td>am <input type=\"text\" name=\"start_day\" size=\"2\" maxlength=\"2\" value=\"$start_day\">";
echo ".&nbsp;<input type=\"text\" name=\"start_month\" size=\"2\" maxlength=\"2\" value=\"$start_month\">";
printf(".&nbsp;<input type=\"text\" name=\"start_year\" size=\"4\" maxlength=\"4\" value=\"%s\">&nbsp;%s&nbsp;<select name=\"start_h\" size=\"1\">",
	$start_year, "um");

for($i = 0;$i <= 23;$i++){
	echo "<option";
	if($i == $start_h)
		echo " selected";
	if($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}
		
echo "</select>&nbsp;:&nbsp;<select name=\"start_m\" size=\"1\">";

for($i = 0;$i <= 55;$i += 5){
	echo "<option";
	if($i == $start_m)
		echo " selected";
	if($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}

printf("</select> %s%s</td>", "Uhr", $err["start_time"] ? $error_sign : "");
echo "</tr><tr valign=\"bottom\">";
printf("<td><b>%s </b></td>", "Ende:");
echo "<td>am <input type=\"text\" name=\"end_day\" size=\"2\" maxlength=\"2\" value=\"$end_day\">";
echo ".&nbsp;<input type=\"text\" name=\"end_month\" size=\"2\" maxlength=\"2\" value=\"$end_month\">";
printf(".&nbsp;<input type=\"text\" name=\"end_year\" size=\"4\" maxlength=\"4\" value=\"%s\">&nbsp;%s&nbsp;<select name=\"end_h\" size=\"1\">",
	$end_year, "um");

for($i = 0;$i <= 23;$i++){
	echo "<option";
	if($i == $end_h)
		echo " selected";
	if($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}

echo "</select>&nbsp;:&nbsp;<select name=\"end_m\" size=\"1\">";

for($i = 0;$i <= 55;$i += 5){
	echo "<option";
	if($i == $end_m)
		echo " selected";
	if($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}

printf("</select> %s%s</td>", "Uhr", $err["end_time"] ? $error_sign : "");
echo "\n</tr>\n</table>\n</p>\n</td>\n</tr>\n";

$css_switcher->switchClass();
printf("<tr><td class=\"%s\">", $css_switcher->getClass());
echo "<p>\n<table border=\"0\" width=\"100%%\" cellpadding=\"2\" cellspacing=\"2\">";
printf("<tr><td width=\"15%%\"><b>%s </b></td>", "Termin:");
printf("<td width=\"85%%\"><input type=\"text\" name=\"txt\" size=\"50\" maxlength=\"255\" value=\"%s\"></input>",
	$txt);
printf("%s</td>\n", $err["titel"] ? $error_sign : "");
echo"</tr><tr>\n";
printf("<td width=\"15%%\"><b>%s </b></td>", "Beschreibung:");
echo "<td width=\"85%\"><textarea name=\"content\" cols=\"48\" rows=\"5\" wrap=\"virtual\">";
echo $content;
echo "</textarea></td>\n";
echo "</tr>\n</table>\n</p>\n</td>\n</tr>\n<tr>";

$css_switcher->switchClass();
printf("<td class=\"%s\">", $css_switcher->getClass());
echo "\n<p>\n";
echo "<table border=\"0\" width=\"";
if (isset($atermin) && $atermin->getSeminarId())
	echo "<table border=\"0\" width=\"50%\" cellpadding=\"2\" cellspacing=\"2\">";
else
	echo "<table border=\"0\" width=\"80%\" cellpadding=\"2\" cellspacing=\"2\">";
echo "<tr>\n<td>\n";
printf("<b>%s </b>", "Kategorie:");
echo "\n</td><td>\n";
echo "<select name=\"cat\" size=\"1\">\n";

if (isset($atermin) && $atermin->getSeminarId()) {
	if (!isset($cat))
		$cat = 1;
	printf("<option value=\"%s\" selected>%s", $cat, $TERMIN_TYP[$cat]["name"]);
}
else {
	if (!isset($cat))
		$cat = 1;
	for ($i = 1;$i < sizeof($PERS_TERMIN_KAT);$i++) {
		printf("<option value=\"%s\"", $i);
		if($cat == $i)
			echo " selected";
		printf(">%s\n", $PERS_TERMIN_KAT[$i]["name"]);
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
								</select></td>
							<td>&nbsp;alle&nbsp;<input type="text" name="lintervall_m2" size="3" maxlength="3" value="<? echo $lintervall_m2?$lintervall_m2:1; ?>"><? echo $err["lintervall_m2"]?$error_sign:""; ?>&nbsp;Monate</td>
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
	
<?
	if(isset($atermin) && $atermin->getSeminarId()){
		$db = new DB_Seminar();
		$query = "SELECT name FROM seminare WHERE Seminar_id='".$atermin->getSeminarId()."'";
		$db->query($query);
		$db->next_record();
		$link_to_seminar = "<a href=\"" . $CANONICAL_RELATIVE_PATH_STUDIP
											. "seminar_main.php?auswahl=" . $atermin->getSeminarId()
											. "\">" . htmlReady($db->f("name")) . "</a>";
		$permission = get_perm($atermin->getSeminarId());
		if($permission == "tutor" || $permission == "dozent")
			$info_content = array(	
											array("kategorie" => "Information:",
														"eintrag" => array(	
														array("icon" => "pictures/ausruf_small.gif",
																	"text" => "Dieser Termin geh&ouml;rt zur Veranstaltung:"),
														array("text" => $link_to_seminar),
														array("text" => "Veranstaltungstermine k&ouml;nnen nicht im pers&ouml;nlichen Terminkalender bearbeitet werden."
																	)
														)
											),
											array("kategorie" => "Aktion:",
		   											"eintrag" => array(	
														array(	"icon" => "pictures/meinesem.gif",
																		"text" => "<a href=\"$PHP_SELF?cmd=bind\">W&auml;hlen</a> Sie aus, welche Veranstaltungstermine "
																							. "automatisch in Ihrem Terminkalender angezeigt werden sollen."
																	),
														array("icon" => "pictures/admin.gif",
																	"text" => "Um diesen Termin zu bearbeiten, wechseln Sie bitte "
																		. "in die <a href=\"./admin_dates.php?range_id="
																		. $atermin->getSeminarId() . "&show_id="
																		. $atermin->getId()."\">Terminverwaltung</a>."
																	)
														)
											)
										);
		else
			$info_content = array(	
											array("kategorie" => "Information:",
														"eintrag" => array(	
														array("icon" => "pictures/ausruf_small.gif",
																	"text" => "Dieser Termin geh&ouml;rt zur Veranstaltung:<br><br>"
																			. $link_to_seminar
																			. "<br><br>Veranstaltungstermine k&ouml;nnen nicht im pers&ouml;nlichen Terminkalender bearbeitet werden."
																	)
														)
											),
											array("kategorie" => "Aktion:",
		   											"eintrag" => array(	
														array (	"icon" => "pictures/meinesem.gif",
																		"text" => "<a href=\"$PHP_SELF?cmd=bind\">W&auml;hlen</a> Sie aus, welche Veranstaltungstermine "
																							. "automatisch in Ihrem Terminkalender angezeigt werden sollen."
																	)
														)
											)
										);
?>
	</td></tr></table></td>
			<td class="blank" align="center" rowspan="1" valign="top" width="20%">
				<table class="blank" cellspacing="0" cellpadding="0" border="0" valign="top">
					<tr><td class="blank" align="center" valign="top">
<?
						print_infobox($info_content, "pictures/dates.jpg");
?>
					</td></tr>
				</table>
			<tr><td class="blank">
<?
		echo "</td></tr>\n";
 	}
	else{
?>
	</td><td width="20%" valign="top" class="steel1">
		<table width="100%" border="0" cellspacing="2" cellpadding="2">
			<tr><td class="steel1" align="center"><b>Wiederholung</b></td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "SINGLE" || $mod == "SINGLE")
					echo '<input type="image" name="mod_s" src="./pictures/buttons/keine2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_s" src="./pictures/buttons/keine-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "DAYLY" || $mod == "DAYLY")
					echo '<input type="image" name="mod_d" src="./pictures/buttons/taeglich2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_d" src="./pictures/buttons/taeglich-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "WEEKLY" || $mod == "WEEKLY")
					echo '<input type="image" name="mod_w" src="./pictures/buttons/woechentlich2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_w" src="./pictures/buttons/woechentlich-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "MONTHLY" || $mod == "MONTHLY")
					echo '<input type="image" name="mod_m" src="./pictures/buttons/monatlich2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_m" src="./pictures/buttons/monatlich-button.gif" border="0">'; ?>
			</td></tr>
			<tr><td class="steel1" align="center">
			<? if($repeat["type"] == "YEARLY" || $mod == "YEARLY")
					echo '<input type="image" name="mod_y" src="./pictures/buttons/jaehrlich2-button.gif" border="0">';
				 else
					echo '<input type="image" name="mod_y" src="./pictures/buttons/jaehrlich-button.gif" border="0">'; ?>
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
		echo "</table></td></tr></table><br />\n";
		echo "</td></tr></table>\n";
?>