<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP (calendar module)
// month.inc.php
// print out month view
// 
// Copyright (c) 2002 Peter Thienel <pthienel@web.de> 
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

echo "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"100%\">\n";
echo "<table width=\"98%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";
echo "<tr><td>&nbsp</td></tr>\n<tr><td>\n";
echo "<table class=\"blank\" border=\"0\" cellspacing=\"1\" cellpadding=\"0\" align=\"center\">\n";
echo "<tr><th>\n";

echo "<table width=\"100%\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";
echo "<tr>\n";
printf("<th>&nbsp;<a href=\"%s?cmd=showmonth&atime=%s\">",
	$PHP_SELF, $amonth->getStart() - 1);
echo "<img border=\"0\" src=\"./pictures/forumrotlinks.gif\" alt=\"zur&uuml;ck\"></a>&nbsp;</th>\n";
printf("<th colspan=%s class=\"cal\">\n", $mod == "nokw" ? "5" : "6");
printf("%s %s</th>\n", month($amonth->getStart()), $amonth->getYear());
printf("<th>&nbsp;<a href=\"%s?cmd=showmonth&atime=%s\">",
	$PHP_SELF, $amonth->getEnd() + 1);
echo "<img border=\"0\" src=\"./pictures/forumrot.gif\" alt=\"vor\"></a>&nbsp;</th>\n";
echo "</tr>\n<tr>\n";
printf("<th width=\"%s\">Mo</th><th width=\"%s\">Di</th><th width=\"%s\">Mi</th><th width=\"%s\">Do</th>\n",
	$width, $width, $width, $width);
printf("<th width=\"%s\">Fr</th><th width=\"%s\">Sa</th><th width=\"%s\">So</th>\n",
	$width, $width, $width);
if($mod != "nokw")
	printf("<th width=\"%s\">KW</th>\n", $width);
echo "</tr></table>\n</th></tr>\n";

echo "<tr><td class=\"blank\">\n";
echo "<table class=\"blank\" border=\"0\" cellspacing=\"1\" cellpadding=\"1\">\n";

// Im Kalenderblatt ist links oben immer Montag. Das muss natuerlich nicht der
// Monatserste sein. Es muessen evtl. noch Tage des vorangegangenen Monats
// am Anfang und des folgenden Monats am Ende angefuegt werden.

$adow = strftime("%u", $amonth->getStart()) - 1;

$first_day = $amonth->getStart() - $adow * 86400 + 43200;
// Ist erforderlich, um den Maerz richtig darzustellen
// Ursache ist die Sommer-/Winterzeit-Umstellung
$cor = 0;
if ($amonth->getMonth() == 3)
	$cor = 1;
	
$last_day = ((42 - ($adow + date("t",$amonth->getStart()))) % 7 + $cor) * 86400
 	        + $amonth->getEnd() - 43199;
					
for ($i = $first_day, $j = 0; $i <= $last_day; $i += 86400, $j++) {
	$aday = date("j", $i);
	// Tage des vorangehenden und des nachfolgenden Monats erhalten andere
	// style-sheets
	$style = "";
	if (($aday - $j - 1 > 0) || ($j - $aday  > 6))
		$style = "light";
	
	// Feiertagsueberpruefung
	if ($mod != "compact" && $mod != "nokw")
		$hday = holiday($i);
	
	// wenn Feiertag dann nur 4 Termine pro Tag ausgeben, sonst wirds zu eng
	if ($hday["col"] > 0)
		$max_apps = 4;
	else
		$max_apps = 5;
	
	if ($j % 7 == 0)
		echo "<tr>\n";
	printf("<td class=\"%smonth\" valign=\"top\" width=\"%s\" height=\"%s\">&nbsp;",
		$style, $width, $height);
	
	if (($j + 1) % 7 == 0) {
		printf("<a class=\"%ssday\" href=\"%s?cmd=showday&atime=%s\">%s</a>\n",
			$style, $PHP_SELF, $i, $aday);
		month_up_down($amonth, $i, $step, $max_apps);
		
		if ($hday["name"] != "")
			printf("<br><font class=\"inday\">%s</font>\n", $hday["name"]);
		
		print_month_events($amonth, $max_apps, $i);
		
		echo "</td>\n";
		
		if ($mod != "nokw") {
			printf("<td class=\"lightmonth\" align=\"center\" width=\"%s\" height=\"%s\">",
				$width, $height);
			printf("<a class=\"kw\" href=\"%s?cmd=showweek&atime=%s\">%s</a></td>\n",
				$PHP_SELF, $i, strftime("%V", $i));
		}
		echo "</tr>\n";
	}
	else{
		// unterschiedliche Darstellung je nach Art des Tages (Rang des Feiertages)
		switch ($hday["col"]) {
			case 1:
				printf("<a class=\"%sday\" href=\"%s?cmd=showday&atime=%s\">%s</a>\n",
					$style, $PHP_SELF, $i, $aday);
				month_up_down($amonth, $i, $step, $max_apps);
				printf("<br><font class=\"inday\">%s</font>", $hday["name"]);
				break;
			case 2:
				printf("<a class=\"%shday\" href=\"%s?cmd=showday&atime=%s\">%s</a>\n",
					$style, $PHP_SELF, $i, $aday);
				month_up_down($amonth, $i, $step, $max_apps);
				printf("<br><font class=\"inday\">%s</font>", $hday["name"]);
				break;
			case 3;
				printf("<a class=\"%shday\" href=\"%s?cmd=showday&atime=%s\">%s</a>\n",
					$style, $PHP_SELF, $i, $aday);
				month_up_down($amonth, $i, $step, $max_apps);
				printf("<br><font class=\"inday\">%s</font>", $hday["name"]);
				break;
			default:
				printf("<a class=\"%sday\" href=\"%s?cmd=showday&atime=%s\">%s</a>\n",
					$style, $PHP_SELF, $i, $aday);
				month_up_down($amonth, $i, $step, $max_apps);
		}
		
		print_month_events($amonth, $max_apps, $i);
		
		echo "</td>\n";
		
	}
}

echo "</td></tr></table>\n</td></tr>\n";
echo "<tr><th>&nbsp;</th></tr>\n";

echo "</table></td></table><table width=\"98%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
jumpTo($jmp_m, $jmp_d, $jmp_y);
echo "</table>\n";
echo "<tr><td class=\"blank\">&nbsp;";

/**
* Print a list of events for each day of month
*
* @access public
* @param object $month_obj instance of DbCalendarMonth
* @param int $max_events the number of events to print
* @param int $day_timestamp unix timestamp of the day
*/
function print_month_events ($month_obj, $max_events, $day_timestamp) {
	global $PHP_SELF;
	$count = 0;
	while (($aterm = $month_obj->nextEvent($day_timestamp)) && $count < $max_events) {
		if ($aterm->getType() == 1 && $aterm->getTitle() == "Kein Titel") {
			$html_title = fit_title($aterm->getSemName(),1,1,15);
			$jscript_title = JSReady($aterm->getSemName());
		}
		else {
			$html_title = fit_title($aterm->getTitle(),1,1,15);
			$jscript_title = JSReady($aterm->getTitle());
		}
		
		$jscript_text = "'"
									. ($aterm->getDescription() ? JSReady($aterm->getDescription()) : _("Keine Beschreibung"))
									. "',CAPTION,'"
									. strftime("%H:%M-",$aterm->getStart())
									. strftime("%H:%M",$aterm->getEnd())
									. "&nbsp; &nbsp; " . $jscript_title
									. "',NOCLOSE,CSSOFF";
			
		printf("<br><a class=\"inday\" href=\"%s?cmd=edit&termin_id=%s&atime=%s\" ",
			$PHP_SELF, $aterm->getId(), $day_timestamp);
		echo "onmouseover=\"return overlib($jscript_text);\" ";
		echo "onmouseout=\"return nd();\">";
		printf("<font color=\"%s\">%s</font></a>\n", $aterm->getColor(), $html_title);
		$count++;
	}
}

/**
* Up-/down-navigation if there are more events per day than the given number
*
* @access private
* @param object &$month_obj instance of DbCalendarMonth
* @param int $day_timestamp unix timestamp of this day
* @param int $step the current step
* @param int $max_events the number of events per step
*/
function month_up_down (&$month_obj, $day_timestamp, $step, $max_events) {
	global $PHP_SELF, $atime;
	if($atime == $day_timestamp){
		$spacer = TRUE;
		$up = FALSE;
		$a = $month_obj->numberOfEvents($day_timestamp) - $step - $max_events;
		$up = ($month_obj->numberOfEvents($day_timestamp) > $max_events && $step >= $max_events);
		if($a + $max_events > $max_events){
			if($up)
				echo '&nbsp; &nbsp; &nbsp;';
			else
				echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
			echo '<a href="'.$PHP_SELF.'?cmd=showmonth&atime='.$day_timestamp.'&step='.($step + $max_events).'"><img src="./pictures/forumrotrunt.gif" alt="noch '.$a.' Termine danach" border="0"></a>';
			$spacer = FALSE;
		}
		if($up){
			if($spacer)
				echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
			echo '<a href="'.$PHP_SELF.'?cmd=showmonth&atime='.$day_timestamp.'&step='.($step - $max_events).'"><img src="./pictures/forumrotrauf.gif" alt="noch '.$step.' Termine davor" border="0"></a>';
			$month_obj->setPointer($atime, $step);
		}
	}
	else if($month_obj->numberOfEvents($day_timestamp) > $max_events){
		echo '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;';
		echo '<a href="'.$PHP_SELF.'?cmd=showmonth&atime='.$day_timestamp.'&step='.($max_events).'"><img src="./pictures/forumrotrunt.gif" alt="noch '.($month_obj->numberOfEvents($day_timestamp) - $max_events).' Termine danach" border="0"></a>';
	}
}
?>
