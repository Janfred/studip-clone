<?
/*
calendar.inc.php 0.8-20020628
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

?>
<html>
	<head>
		<title>Stud.IP</title>
		<link rel="stylesheet" href="style.css" type="text/css">
	</head>
	<body bgcolor="#FFFFFF">
<?
	if($cmd == "showmonth"){
		echo '<div ID="overDiv" STYLE="position:absolute; visibility:hidden;z-index:1000;"></div>';
		echo "<script language=\"JavaScript\" src=\"overlib.js\"></script>\n";
	}
	
	// -- hier muessen Seiten-Initialisierungen passieren --
	// -- wir sind jetzt definitiv in keinem Seminar, also... --
	$SessSemName[0] = "";
	$SessSemName[1] = "";

	include("header.php");   //hier wird der "Kopf" nachgeladen
	require_once("config.inc.php"); //Daten laden
	require_once("visual.inc.php");
	require_once("functions.php");
	include($RELATIVE_PATH_CALENDAR . "/calendar_func.inc.php");
//	include($RELATIVE_PATH_CALENDAR . "/lib/kalenderClass.inc.php");
	include($RELATIVE_PATH_CALENDAR . "/calendar_visual.inc.php");
		
	// bei Einsprung ohne $cmd wird weiter unten eine Erlaeuterung ausgegeben
	if(!$cmd && !$atime)
		$intro = TRUE;
	
	// Wird kein timestamp an das Skript uebergeben, benutze aktuellen
	if(!$atime && !$termin_id)
		$atime = time();
		
	if($mod)
		$cmd = "edit";
		
	// Zeitbereich eingrenzen
	if(isset($atime) && ($atime < 0 || $atime > 2114377200))
		$atime = time();
	
	// Datum fuer "Gehe-zu-Funktion" checken
	if(check_date($jmp_m, $jmp_d, $jmp_y))
		$atime = mktime(12,0,0,$jmp_m,$jmp_d,$jmp_y);
	else{
		$jmp_d = date("j", $atime);
		$jmp_m = date("n", $atime);
		$jmp_y = date("Y", $atime);
	}
	
	// User-Variablen initialisieren, updaten oder Standardwerte setzen
	$user->register("calendar_user_control_data");
	// Benutzereinstellungen uebernehmen
	if($cmd_cal == "chng_cal_settings"){
		$calendar_user_control_data = array(
			"view"           => $cal_view,
			"start"          => $cal_start,
			"end"            => $cal_end,
			"step_day"       => $cal_step_day,
			"step_week"      => $cal_step_week,
			"type_week"      => $cal_type_week,
			"holidays"       => $cal_holidays,
			"sem_data"       => $cal_sem_data,
			"link_edit"      => $cal_link_edit,
			"bind_seminare"  => $calendar_user_control_data["bind_seminare"]
		);
	}
	// Default-Werte speichern
	if(empty($calendar_user_control_data["view"])){
		$calendar_user_control_data = array(
			"view"           => "showweek",
			"start"          => 9,
			"end"            => 20,
			"step_day"       => 900,
			"step_week"      => 3600,
			"type_week"      => "LONG",
			"holidays"       => TRUE,
			"sem_data"       => TRUE,
			"link_edit"      => FALSE,
			"bind_seminare"  => ""
		);
	}
	
	// Wenn "Einbinden-Formular" abgeschickt wurde, dann ...["bind_seminare"] erneuern
	if($sem)
		$calendar_user_control_data["bind_seminare"] = $sem;
	if(is_array($calendar_user_control_data["bind_seminare"]))
		$bind_seminare = array_keys($calendar_user_control_data["bind_seminare"], "TRUE");
	else
		$bind_seminare = "";
	
	// Wenn Termin-Anlegen oder -Bearbeiten beendet ist, vergiss die Formulardaten
	if(isset($calendar_sess_forms_data) && $cmd != "edit"){
		$sess->unregister("calendar_sess_forms_data");
		unset($calendar_sess_forms_data);
	}
	
	if($cmd == ""){
		if($termin_id)
			// wird eine termin_id uebergeben immer in den Bearbeiten-Modus
			$cmd = "edit";
		else
			$cmd = $calendar_user_control_data["view"];
	}
	
	$sess->register("calendar_sess_control_data");
	
	if($cmd == "add" || $cmd == "edit"){
		if(!isset($calendar_sess_forms_data))
			$sess->register("calendar_sess_forms_data");
		if(!empty($HTTP_POST_VARS)){
			if($calendar_sess_control_data["mod"])
				$mod_prv = $calendar_sess_control_data["mod"];
			else
				$mod_prv = "keine";
				
			if($mod)
				$calendar_sess_control_data["mod"] = $mod;
				
			// Formulardaten uebernehmen
			$accepted_vars = array("start_m", "start_h", "start_day", "start_month", "start_year", "end_m",
														"end_h",	"end_day", "end_month", "end_year",	"exp_day", "exp_month",
														"exp_year", "cat", "priority", "txt", "content", "loc", "lintervall_d",
														"lintervall_w", "wdays", "type_m", "lintervall_m2", "sintervall_m",
														"lintervall_m1", "wday_m", "day_m", "type_y", "sintervall_y", "wday_y",
														"day_y", "month_y1", "month_y2", "atime", "termin_id", "exp_c", "mod"
														);
			reset($HTTP_POST_VARS);
			while(list($key, $value) = each($HTTP_POST_VARS)){
				if(in_array($key, $accepted_vars))
					$calendar_sess_forms_data[$key] = $value;
			}
			extract($calendar_sess_forms_data, EXTR_OVERWRITE);
		}
		else
			$calendar_sess_control_data["mod"] = "";
	}
	
	// Seitensteuerung
	switch($cmd){
		case "showday":
			$calendar_sess_control_data["view_prv"] = $cmd;
			$title = "Mein pers&ouml;nlicher Terminkalender - Tagesansicht";
			break;
		case "add":
			switch($calendar_sess_control_data["view_prv"]){
				case "showday":
					$title = "Mein pers&ouml;nlicher Terminkalender - Tagesansicht";
					break;
				case "showweek":
					$title = "Mein pers&ouml;nlicher Terminkalender - Wochenansicht";
					break;
				case "showmonth":
					$title = "Mein pers&ouml;nlicher Terminkalender - Monatsansicht";
					break;
				case "showyear":
					$title = "Mein pers&ouml;nlicher Terminkalender - Jahresansicht";
			}
			break;
		case "del":
			require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
			$title = "Mein pers&ouml;nlicher Terminkalender - Tagesansicht";
			$atermin = new CalendarEvent($termin_id);
			$atermin->delete();
			if(!empty($calendar_sess_control_data["view_prv"]))
				$cmd = $calendar_sess_control_data["view_prv"];
			else
				$cmd = "showday";
			break;
		case "showweek":
			$title = "Mein pers&ouml;nlicher Terminkalender - Wochenansicht";
			$calendar_sess_control_data["view_prv"] = $cmd;
			break;
		case "showmonth":
			$title = "Mein pers&ouml;nlicher Terminkalender - Monatsansicht";
			$calendar_sess_control_data["view_prv"] = $cmd;
			break;
		case "showyear":
			$title = "Mein pers&ouml;nlicher Terminkalender - Jahresansicht";
			$calendar_sess_control_data["view_prv"] = $cmd;
			break;
		case "bind":
			$title = "Mein pers&ouml;nlicher Terminkalender - Seminartermine einbinden";
			break;
/*		case "import":
			$title = "Mein pers&ouml;nlicher Terminkalender - Termine importieren";
			break; */
		case "edit":
			if($termin_id && !$mod){
				require_once($RELATIVE_PATH_CALENDAR . "/lib/CalendarEvent.class.php");
				$atermin = new CalendarEvent($termin_id);
				$repeat = $atermin->getRepeat();
				$translate = array("SINGLE"=>"keine", "DAYLY"=>"t&auml;glich", "WEEKLY"=>"w&ouml;chentlich",
					                 "MONTHLY"=>"monatlich", "YEARLY"=>"j&auml;hrlich");
				$mod = $translate[$repeat["type"]];
				if(empty($HTTP_POST_VARS))
					$calendar_sess_control_data["mod"] = $mod;
			}
			if($termin_id)
				$title = "Mein pers&ouml;nlicher Terminkalender - Termin bearbeiten";
			else
				$title = "Mein pers&ouml;nlicher Terminkalender - Neuer Termin";
				
			switch($mod){
				case "keine":
					break;
				case "t&auml;glich":
					if($type == "wdayly")
						$lintervall_d = "";
					break;
				case "w&ouml;chentlich":
				case "monatlich":
				case "j&auml;hrlich":
					break;
			}
			break;
		default:
			
	}

	// Termin hinzufuegen *********************************************************
	
	if($cmd == "add"){
		// Ueberpruefung der Formulareingaben
		$err = "";
		if(!check_date($start_month, $start_day, $start_year))
			$err["start_time"] = TRUE;
		if(!check_date($end_month, $end_day, $end_year))
			$err["end_time"] = TRUE;
		
		if(!$err["start_time"] && !$err["end_time"]){
			$start = mktime($start_h,$start_m,0,$start_month,$start_day,$start_year);
			$end = mktime($end_h,$end_m,0,$end_month,$end_day,$end_year);
			if($start > $end)
				$err["end_time"] = TRUE;
		}
		/*
		if(ceil((mktime(12,0,0,$end_month,$end_day,$end_year) - mktime(12,0,0,$start_month,$start_day,$start_year)) / 86400) > 2){
			$err["end_time"] = TRUE;
			$err_message = "<br>Ein Termin darf sich &uuml;ber max. zwei Tage erstrecken!";
		}*/
		
		if(!preg_match('/^.*\S+.*$/', $txt))
			$err["titel"] = TRUE;
		
		switch($mod_prv){
			case "t&auml;glich":
				if(!preg_match("/^\d{1,3}$/", $lintervall_d))
					$err["lintervall_d"] = TRUE;
				break;
			case "w&ouml;chentlich":
				if(!preg_match("/^\d{1,3}$/", $lintervall_w))
					$err["lintervall_w"] = TRUE;
				break;
			case "monatlich":
				if($type_m == "day"){
					if(!preg_match("/^\d{1,2}$/", $day_m) && $day_m < 32)
						$err["sintervall_m"] = TRUE;
					if(!preg_match("/^\d{1,3}$/", $lintervall_m1))
						$err["lintervall_m1"] = TRUE;
				}
				else
					if(!preg_match("/^\d{1,3}$/", $lintervall_m2))
						$err["lintervall_m2"] = TRUE;
				break;
			case "j&auml;hrlich":
				// Jahr 2000 als Schaltjahr
				if(!check_date($month_y1, $day_y, 2000))
					$err["day_y"] = TRUE;
		}
		
		if($exp_c == "date")
			if(!check_date($exp_month, $exp_day, $exp_year))
				$err["exp_time"] = TRUE;
			else{
				$exp = mktime(23,59,59,$exp_month,$exp_day,$exp_year);
				if(!$err["end_time"] && $exp < $end)
					$err["exp_time"] = TRUE;
			}
		else
			$exp = "";
		
		// wenn alle Daten OK, dann Termin anlegen		
		if(empty($err)){
			require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarEvent.class.php");
			$atermin = new CalendarEvent($start,$end,$txt,$exp,$cat,$priority,$loc);		
			$atermin->setDescription($content);
			if($vue == "public")
				$atermin->setType(-1);
			else
				$atermin->setType(-2);
				
			switch($mod_prv){
				case "einzel":
					$atermin->setRepeat("SINGLE");
					break;
				case "t&auml;glich":
					if($type_d == "dayly")
						$atermin->setRepeat("DAYLY", $lintervall_d);
					else if($type_d == "wdayly")
						$atermin->setRepeat("WEEKLY", 1, "12345");
					break;
				case "w&ouml;chentlich":
					if(empty($wdays))
						$atermin->setRepeat("WEEKLY", $lintervall_w);
					else{
						$weekdays = implode("", $wdays);
						$atermin->setRepeat("WEEKLY", $lintervall_w, $weekdays);
					}
					break;
				case "monatlich":
					if($type_m == "day")
						$atermin->setRepeat("MONTHLY", $lintervall_m1, $day_m);
					else
						$atermin->setRepeat("MONTHLY", $lintervall_m2, $sintervall_m, $wday_m);
					break;
				case "j&auml;hrlich":
					if($type_y == "day")
						$atermin->setRepeat("YEARLY", $month_y1, $day_y);
					else
						$atermin->setRepeat("YEARLY", $sintervall_y, $wday_y, $month_y2);
					break;
			}
			// wird eine termin_id uebergeben, werden nur die Daten des Termins geaendert
			if($termin_id)
				$atermin->setId($termin_id);
			$atermin->save();
			
			if(!empty($calendar_sess_control_data["view_prv"]))
				$cmd = $calendar_sess_control_data["view_prv"];
			else
				$cmd = "showday";
				
		}
		else{
			$cmd = "edit";
			if(!$mod_err)
				$mod_err = $mod_prv;
			$mod = $mod_err;
		}
	}
	
	include($RELATIVE_PATH_CALENDAR . "/calendar_links.inc.php");
	
	if($cmd != "changeview"){
	?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
	<tr>
		<td class="topic">&nbsp;<img src="pictures/meinetermine.gif" border="0" align="absmiddle" alt="Termine"><b>&nbsp;<? echo $title; ?></b></td>
	</tr>
	<? if($intro){ ?>
	<tr><td class="blank">&nbsp;
		<blockquote>
			Dieser Terminkalender verwaltet Ihre Termine. Sie k&ouml;nnen Termine eintragen, &auml;ndern, 
			gruppieren und sich &uuml;bersichtlich anzeigen lassen.
		</blockquote>
	</td></tr>
		<? }
		else
			echo '<tr><td class="blank" height="15" width="100%">&nbsp;</td></tr>';
		echo "</table>";
	}
	
	// Tagesuebersicht anzeigen ***************************************************
	
	if($cmd == "showday"){
		
		$d_start = $calendar_user_control_data["start"];
		$d_end = $calendar_user_control_data["end"];
	
		$at = date("G", $atime);
		if($at >=  $d_start && $at <= $d_end || !$atime){
			$st = $d_start;
			$et = $d_end;
		}
		elseif($at < $d_start){
			$st = 0;
			$et = $d_start + 2;
		}
		else{
			$st = $d_end - 2;
			$et = 23;
		}
		
		require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarDay.class.php");
		$aday = new DbCalendarDay($atime);
		$aday->bindSeminarTermine($bind_seminare);
		$tab = createDayTable($aday, $st, $et, $calendar_user_control_data["step_day"], TRUE, TRUE);
		
?>
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
		$link = "./$PHP_SELF?cmd=showday&atime=";
		echo "<tr><td align=\"center\">".includeMonth($atime, $link)."</td></tr>\n";
		echo "<tr><td>&nbsp;</td></tr>\n";
		echo "</table>\n";
		echo "</td></tr><tr><td class=\"blank\" width=\"100%\" colspan=\"2\">&nbsp;";
	}

	// Wochenuebersicht anzeigen **************************************************
	
	if($cmd == "showweek"){
	
		$w_start = $calendar_user_control_data["start"];
		$w_end = $calendar_user_control_data["end"];
		
		if(isset($wtime))
			$at = (int) $wtime;
		if(!($at > 0 && $at < 24))
			$at = $w_start;
		if($at >=  $w_start && $at <= $w_end){
			$st = $w_start;
			$et = $w_end;
		}
		else if($at < $w_start){
			$st = 0;
			$et = $w_start + 2;
		}
		else{
			$st = $w_end - 2;
			$et = 23;
		}
		
		require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarWeek.class.php");
		$aweek = new DbCalendarWeek($atime, $calendar_user_control_data["type_week"]);
		$aweek->bindSeminarTermine($bind_seminare);
		$tab = createWeekTable($aweek, $st, $et, $calendar_user_control_data["step_week"]
													, FALSE, $calendar_user_control_data["link_edit"]);
		$rowspan = ceil(3600 / $calendar_user_control_data["step_week"]);
		$height = ' height="20"';
		if($aweek->getType() == 5)
			$width = "98";
		else
			$width = "99";
		if($rowspan > 1){
			$colspan_1 = ' colspan="2"';
			$colspan_2 = $tab["max_columns"] + 4;
		}
		else{
			$colspan_1 = "";
			$colspan_2 = $tab["max_columns"] + 2;
		}
		
?>
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
	}

	// Monatsuebersicht anzeigen **************************************************

	if($cmd == "showmonth"){
	
		require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarMonth.class.php");
		
		$amonth = new DbCalendarMonth($atime);
		$calendar_sess_forms_data["bind_seminare"] = "";
		$amonth->bindSeminarTermine($bind_seminare);
		$amonth->sort();
		
		if($mod == "compact" || $mod == "nokw"){
			$hday["name"] = "";
			$hday["col"] = "";
			$width = "20";
			$height = "20";
		}
		else{
			$width = "90";
			$height = "80";
		}
		
?>
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
	}
	
	// Jahresuebersicht ***********************************************************
	
	if($cmd == "showyear"){
	
		require_once($RELATIVE_PATH_CALENDAR . "/lib/DbCalendarYear.class.php");
		
		$ayear = new DbCalendarYear($atime);
		$ayear->bindSeminarTermine($bind_seminare);
		
?>
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
			echo '<th width="8%"><a class="precol1" href="'.$PHP_SELF.'?cmd=showmonth&atime='.($ayear->getStart() + $ts_month).'">'.month($ts_month).'</a></th>';
		}
		echo '</tr>';
		
		for($i = 1;$i < 32;$i++){
			echo '<tr>';
			for($month = 1;$month < 13;$month++){
				$aday = mktime(12,0,0,$month,$i,$ayear->getYear());
				
				if($i <= $days_per_month[$month]){
					$wday = date("w", $aday);
					if($wday == 0 || $wday == 6)
						$weekend = ' class="weekend"';
					else
						$weekend = "";
						
					if($month == 1)
						echo "<td" . $weekend . ' height="25">';
					else
						echo "<td" . $weekend . ">";
					
					if($apps = $ayear->numberOfApps($aday))
						echo '<table width="100%" cellspacing=0 cellpadding=0><tr><td' . $weekend . '>';
						
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
					
					if($apps){
						if($apps > 1)
							echo '</td><td' . $weekend . ' align="right"><img src="pictures/icon-uhr.gif" alt="'.$apps.' Termine"></td></table>';
						else
							echo '</td><td' . $weekend . ' align="right"><img src="pictures/icon-uhr.gif" alt="1 Termin"></td></table>';
					}
					
					echo '</td>';
				}
				else
					echo '<td>&nbsp;</td>';
			}
			echo "</tr>\n";
			
		}
		echo '<tr>';
		$ts_month = 0;
		for($i = 1;$i < 13;$i++){
			$ts_month += ($days_per_month[$i] - 1) * 86400;
			echo '<th width="8%"><a class="precol1" href="'.$PHP_SELF.'?cmd=showmonth&atime='.($ayear->getStart() + $ts_month).'">'.month($ts_month).'</a></th>';
		}
		echo '</tr></table></td></tr>';
		jumpTo($jmp_m, $jmp_d, $jmp_y);
		echo "</table>\n</td></tr>";
		echo "<tr><td class=\"blank\" width=\"100%\">&nbsp;";
		
	}
	
	// Termine editieren *********************************************************

	// ist $termin_id an das Skript uebergeben worden, dann bearbeite diesen Termin
	// ist $atime an das Skript uebergeben worden, dann erzeuge neuen Termin (s.o.)
	if($cmd == "edit"){
		?>	
			<table width="100%" border="0" cellpadding="5" cellspacing="0">
				<tr><td class="blank" width="100%">
			<?
		echo '<table width="98%" border="0" cellspacing="0" cellpadding="4" align="center">';
		
		if(!empty($err)){
			$error_sign = "<font color='FF0000' size='+2'><b>&nbsp;*&nbsp;</b></font>";
			$error_message = "Bitte korrigieren Sie die mit $error_sign gekennzeichneten Felder.".$err_message;
			my_info($error_message);
		}
		
		echo '<tr><td width="100%" colspan="2" class="steel2">';
		// Aufruf aus Tagesansicht fuer neuen Termin
		if($atime && !$termin_id && !$mod){
			$start_h = date("G", $atime);
			$start_m = date("i", $atime);
			$start_day = date("j", $atime);
			$start_month = date("n", $atime);
			$start_year = date("Y", $atime);
			$end_h = $start_h + 1;
			$end_m = 0;
			$end_day = $start_day;
			$end_month = $start_month;
			$end_year = $start_year;
			$expire = 2114377200;
			$cat = 1;
			$vue = "private";
			echo "<b>Termin erstellen f&uuml;r " . ldate($atime) . "</b></td></tr>\n";
		}
		// Aufruf aus Ansichten bestehenden Termin bearbeiten
		else if($atermin && !$mod_prv){
			$start_h = date("G", $atermin->getStart());
			$start_m = date("i", $atermin->getStart());
			$start_day = date("j", $atermin->getStart());
			$start_month = date("n", $atermin->getStart());
			$start_year = date("Y", $atermin->getStart());
			$end_h = date("G", $atermin->getEnd());
			$end_m = date("i", $atermin->getEnd());
			$end_day = date("j", $atermin->getEnd());
			$end_month = date("n", $atermin->getEnd());
			$end_year = date("Y", $atermin->getEnd());
			$expire = $atermin->getExpire();
			if($expire == mktime(0,0,0,1,1,2037))
				$exp_c = "never";
			else
				$exp_c = "date";
			$exp_day = date("j", $expire);
			$exp_month = date("n", $expire);
			$exp_year = date("Y", $expire);
			if($atermin->getType() == -1)
				$vue = "public";
			else
				$vue = "private";
			$cat = $atermin->getCategory();
			$priority = $atermin->getPriority();
			$txt = htmlReady($atermin->getTitle());
			$content = htmlReady($atermin->getDescription());
			$loc = htmlReady($atermin->getLocation());
			switch($repeat["type"]){
				case "SINGLE":
					break;
				case "DAYLY":
					$lintervall_d = $repeat["lintervall"];
					break;
				case "WEEKLY":
					$lintervall_w = $repeat["lintervall"];
					for($i = 0;$i < strlen($repeat["wdays"]);$i++)
						$wdays[$repeat["wdays"][$i]] = $repeat["wdays"][$i];
					break;
				case "MONTHLY":
					if($repeat["day"] == ""){
						$type_m = "wday";
						$lintervall_m2 = $repeat["lintervall"];
						$sintervall_m = $repeat["sintervall"];
						for($i = 0;$i < strlen($repeat["wdays"]);$i++)
							$wday_m = $repeat["wdays"];
					}
					else{
						$type_m = "day";
						$lintervall_m1 = $repeat["lintervall"];
						$day_m = $repeat["day"];
					}
					break;
				case "YEARLY":
					if($repeat["day"] == ""){
						$type_y = "wday";
						$sintervall_y = $repeat["sintervall"];
						$wday_y = $repeat["wdays"];
						$month_y2 = $repeat["month"];
					}
					else{
						$type_y = "day";
						$day_y = $repeat["day"];
						$month_y1 = $repeat["month"];
					}
			}
			if($atermin->getSeminarId())
				echo "<b>Termin am " . ldate($atermin->getStart()) . "</b></td></tr>\n";
			else
				echo "<b>Termin am " . ldate($atermin->getStart()) . " bearbeiten</b></td></tr>\n";
		}
		else if($mod_prv && $termin_id){
			echo "<b>Termin am " . ldate($atime) . " bearbeiten</b></td></tr>\n";
		}
		else if($mod && $atime)
			if(check_date($start_month, $start_day, $start_year))
				echo "<b>Termin erstellen f&uuml;r " . ldate(mktime(0,0,0,$start_month,$start_day,$start_year)) . "</b></td></tr>\n";
		else{
			page_close();
			die;
		}
		
		if(!$mod)
			$mod = "keine";
		
		// Uebertragung Formular->Formular
		if($mod_prv){
			$txt = htmlentities(stripslashes($txt), ENT_QUOTES);
			$content = htmlentities(stripslashes($content), ENT_QUOTES);
			$loc = htmlentities(stripslashes($loc), ENT_QUOTES);
		}
		
		// Start- und Endzeit nur auf fuenf Minuten genau einstellbar	
		$start_m = $start_m - ($start_m % 5);
		$end_m = $end_m - ($end_m % 5);
?>
			<form action="<? echo $PHP_SELF; ?>?cmd=edit" method=post>
			<tr>
				<td width="80%" valign="top">
					<p>
						<table border="0">
							<tr valign="baseline">
								<td><b>Beginn: </b></td>
								<td>am <input type="text" name="start_day" size=2 maxlength="2" value="<? echo $start_day; ?>"></td>
								<td>.&nbsp;<input type="text" name="start_month" size=2 maxlength="2" value="<? echo $start_month; ?>"></td>
								<td>.&nbsp;<input type="text" name="start_year" size=4 maxlength="4" value="<? echo $start_year; ?>"> um </td>
								<td><select name="start_h" size=1>
<?
		for($i = 0;$i <= 23;$i++){
			echo "<option";
			if($i == $start_h)
				echo " selected";
			echo ">$i";
		}
		
		echo "</select>&nbsp;:&nbsp;</td><td><select name=\"start_m\" size=1>";
		
		for($i = 0;$i <= 55;$i += 5){
			echo "<option";
			if($i == $start_m)
				echo " selected";
			echo ">$i";
		}
?>	
								</select> Uhr<? echo $err["start_time"]?$error_sign:""; ?></td>
							</tr><tr valign="baseline">
								<td><b>Ende: </b></td>
								<td>am <input name="end_day" size=2 value="<? echo $end_day; ?>"></td>
								<td>.&nbsp;<input name="end_month" size=2 value="<? echo $end_month; ?>"></td>
								<td>.&nbsp;<input name="end_year" size=4 value="<? echo $end_year; ?>"> um </td>
								<td><select name="end_h" size=1>
<?
		for($i = 0;$i <= 23;$i++){
			echo "<option";
			if($i == $end_h)
				echo " selected";
			echo ">$i";
		}
		
		echo "</select>&nbsp;:&nbsp;</td><td><select name=\"end_m\" size=1>";
		
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
											for($i = 0;$i < sizeof($PERS_TERMIN_KAT);$i++){
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
									<input type="radio" name="vue" value="private"<? if($vue == "private") echo " checked"; ?>>&nbsp;privat&nbsp;
									<input type="radio" name="vue" value="public"<? if($vue == "public") echo " checked"; ?>>&nbsp;&ouml;ffentlich
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
<?
	switch($mod){
		case "t&auml;glich":
			?>
			<table width="100%" border="0" cellpadding="2" cellspacing="2">
				<tr><td width="30%"><input type="radio" name="type_d" value="dayly"<?if($type_d == "dayly" || $type_d == "") echo " checked"; ?>>&nbsp;<b>Alle</b>&nbsp;
						<input type="text" name="lintervall_d" size="3" maxlength="3" value="<? echo $lintervall_d?$lintervall_d:1; ?>">&nbsp;Tage<? echo $err["lintervall_d"]?$error_sign:""; ?></td>
					<td width="70%"><input type="radio" name="type_d" value="wdayly"<?if($type_d == "wdayly") echo " checked"; ?>>&nbsp;<b>Jeden Werktag</b></td>
				</tr>
			</table>
			<?
			break;
		case "w&ouml;chentlich":
			if(!$wdays)
				$wdays = array();
			?>
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
			<?
			break;
		case "monatlich":
			?>
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
			<?
			break;
		case "j&auml;hrlich":
			if(!$month_y1)
				$month_y1 = $start_month;
			if(!$month_y2)
				$month_y2 = $start_month;
			
			?>
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
			<?
			break;
	}
	if($mod != "keine"){
?>
	</p>
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
<?
	}
?>
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
		$perm = get_perm($atermin->getSeminarId());
		if($perm == "tutor" || $perm == "dozent")
			echo 'Um diesen Termin zu bearbeiten, wechseln Sie bitte in die <a href="./admin_dates.php?range_id='.$atermin->getSeminarId().'&ebene=sem">Terminverwaltung</a>.';
		echo "</td></tr>\n";
 	}
	else{
?>
			<tr><td class="steel1" align="center"><b>Wiederholung</b></td></tr>
			<tr><td class="steel1" valign="middle">
			<? if($repeat["type"] == "SINGLE" || $mod == "keine")
					echo '<img src="./pictures/forumgrau.gif" border="0" alt="Dieser Termin ist ein Einzeltermin">';
				 else
					echo '<img src="./pictures/forumleer.gif" border="0" alt="">'; ?>
				<input type="submit" name="mod" value="keine" border="0"></td></tr>
			<tr><td class="steel1" valign="middle">
			<? if($repeat["type"] == "DAYLY" || $mod == "t&auml;glich")
					echo '<img src="./pictures/forumgrau.gif" border="0" alt="Dieser Termin wird t&auml;glich wiederholt">';
				 else
					echo '<img src="./pictures/forumleer.gif" border="0" align="absmiddle" alt="">'; ?>
				<input type="submit" name="mod" value="t&auml;glich" border="0"></td></tr>
			<tr><td class="steel1" valign="middle">
			<? if($repeat["type"] == "WEEKLY" || $mod == "w&ouml;chentlich")
					echo '<img src="./pictures/forumgrau.gif" border="0" alt="Dieser Termin wird w&ouml;chentlich wiederholt">';
				 else
					echo '<img src="./pictures/forumleer.gif" border="0" alt="">'; ?>
				<input type="submit" name="mod" value="w&ouml;chentlich" border="0"></td></tr>
			<tr><td class="steel1" valign="middle">
			<? if($repeat["type"] == "MONTHLY" || $mod == "monatlich")
					echo '<img src="./pictures/forumgrau.gif" border="0" alt="Dieser Termin wird monatlich wiederholt">';
				 else
					echo '<img src="./pictures/forumleer.gif" border="0" alt="">'; ?>
				<input type="submit" name="mod" value="monatlich" border="0"></td></tr>
			<tr><td class="steel1" valign="middle">
			<? if($repeat["type"] == "YEARLY" || $mod == "j&auml;hrlich")
					echo '<img src="./pictures/forumgrau.gif" border="0" alt="Dieser Termin wird j&auml;hrlich wiederholt">';
				 else
					echo '<img src="./pictures/forumleer.gif" border="0" alt="">'; ?>
				<input type="submit" name="mod" value="j&auml;hrlich" border="0"></td></tr>
			<tr><td class="steel1"><br>&nbsp;<br></td></tr>
<?
	if($atime && !$termin_id){?>
		<tr><td class="steel1" align="center">
			<input type="hidden" name="atime" value="<? echo $atime; ?>">
			<input type="hidden" name="mod_err" value="<? echo $mod_err; ?>">
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
		echo '</table></td></tr></table>';
	}
	
	// Seminartermine einbinden **************************************************
	if($cmd == "bind"){
		// alle vom user abonnierten Seminare
		$db = new DB_Seminar;
		if(!isset($sortby))
			$sortby = "seminar_user.gruppe, seminare.Name";
	//	if($sortby == "count")
		//	$sortby = "count DESC";
		if(!isset($order))
			$order = "ASC";
		$query = "SELECT seminare.Name, seminare.Seminar_id, seminar_user.status, seminar_user.gruppe, count(termin_id) as count "
					 . "FROM seminare LEFT JOIN seminar_user USING (Seminar_id) LEFT JOIN termine ON range_id=seminare.Seminar_id WHERE seminar_user.user_id = '"
					 . $user->id."' GROUP BY Seminar_id ORDER BY $sortby $order";
		$db->query($query);
		if($order == "ASC")
			$order = "DESC";
		else
			$order = "ASC";
		
	?>
	<table width="100%" border="0" cellpadding="5" cellspacing="0">
		<tr><td class="blank" width="100%">
			<table border="0" width="100%" cellspacing="0" cellpadding="0" class="blank">
				<form action="<? echo $PHP_SELF; ?>?cmd=<? if(!empty($calendar_sess_control_data["view_prv"])) echo $calendar_sess_control_data["view_prv"]; else echo "showweek"; ?>" method="post">
				<tr>
					<th width="2%"><a href="gruppe.php"><img src='pictures/gruppe.gif' alt='Gruppe &auml;ndern' border=0></a></th>
					<th width="63%"><a href="<? echo $PHP_SELF ?>?cmd=bind&sortby=Name&order=<? echo $order; ?>">Name</a></th>
					<th width="7%"><a href="<? echo $PHP_SELF ?>?cmd=bind&sortby=count&order=<? echo $order; ?>">Termine</a></th>
					<th width="13%"><b>besucht</b></th>
					<th width="13%"><a href="<? echo $PHP_SELF ?>?cmd=bind&sortby=status&order=<? echo $order; ?>">Status</a></th>
					<th width="2%">&nbsp;</th>
				</tr>
	<?
		$style_switch = 1;
		while($db->next_record()){
			if($style_switch % 2)
				$style = "steel1";
			else
				$style = "steelgraulight";
			$style_switch++;
			printf("<tr><td class=\"gruppe%s\"><img src=\"pictures/blank.gif\" alt=\"Gruppe\" border=\"0\" width=\"15\" height=\"12\"></td>\n", $db->f("gruppe"));
			printf("<td class=\"%s\">&nbsp;&nbsp;%s</td>\n", $style, format(htmlReady(mila($db->f("Name")))));
			printf("<td class=\"%s\" align=\"center\">%s</td>\n", $style, $db->f("count"));
			if($loginfilenow[$db->f("Seminar_id")] == 0)
				printf("<td class=\"%s\" align=\"center\">nicht besucht</td>\n", $style);
			else
				printf("<td class=\"%s\" align=\"center\">%s</td>", $style, date("d.m.Y", $loginfilenow[$db->f("Seminar_id")]));
			printf("<td class=\"%s\" align=\"center\">%s</td>\n", $style, $db->f("status"));
			if($calendar_user_control_data["bind_seminare"][$db->f("Seminar_id")])
				$is_checked = " checked";
			else
				$is_checked = "";
			printf("<td class=\"%s\"><input type=\"checkbox\" name=\"sem[%s]\" value=\"TRUE\"%s></tr>\n", $style, $db->f("Seminar_id"), $is_checked);
		}
		echo "<tr><td class=\"blank\">&nbsp;</td></tr>\n";
		echo '<tr><td class="blank" colspan="6" align="center">&nbsp;<input type="image" src="./pictures/buttons/auswaehlen-button.gif" border="0"></td></tr>';
		// Dummy-Wert damit $sem auch ohne ausgewaehlte Seminare ausgewertet wird
		echo "\n<input type=\"hidden\" name=\"sem[1]\" value=\"FALSE\">\n";
		printf('<input type="hidden" name="atime" value="%s">', $atime);
		echo "\n</form>\n";
		echo "</table>";
		echo "\n</td></tr><tr><td class=\"blank\">&nbsp;";
	}
	
	// Termine importieren *******************************************************
/*	if($cmd == "import"){
		
	} */
	
	// Ansicht anpassen **********************************************************
	if($cmd == "changeview"){
		include($RELATIVE_PATH_CALENDAR . "/calendar_settings.inc.php");
	}
	
	// Save data back to database.
	page_close();
?>
			</td></tr>
		</table>
	</body>
</html>
