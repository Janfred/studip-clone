<?
/**
* month.inc.php
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version		$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	calendar
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// month.inc.php
//
// Copyright (c) 2003 Peter Tienel <pthienel@web.de> 
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
$tooltip = tooltip(_("zurück"));
echo "<img border=\"0\" src=\"./pictures/forumrotlinks.gif\" $tooltip></a>&nbsp;</th>\n";
printf("<th colspan=%s class=\"cal\">\n", $mod == "nokw" ? "5" : "6");
echo htmlentities(strftime("%B ", $amonth->getStart()), ENT_QUOTES) . $amonth->getYear();
echo "</th>\n";
printf("<th>&nbsp;<a href=\"%s?cmd=showmonth&atime=%s\">",
	$PHP_SELF, $amonth->getEnd() + 1);
$tooltip = tooltip(_("vor"));
echo "<img border=\"0\" src=\"./pictures/forumrot.gif\" alt=\"$tooltip\"></a>&nbsp;</th>\n";
echo "</tr>\n<tr>\n";

$weekdays_german = array("MO", "DI", "MI", "DO", "FR", "SA", "SO");
foreach ($weekdays_german as $weekday_german)
	echo "<th width=\"$width\">" . wday("", "SHORT", $weekday_german) . "</th>";

if($mod != "nokw")
	echo "<th width=\"$width\">" . _("Woche") . "</th>\n";
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
	echo "<td class=\"{$style}month\" valign=\"top\" width=\"$width\" height=\"$height\">&nbsp;";
	
	if (($j + 1) % 7 == 0) {
		echo "<a class=\"{$style}sday\" href=\"$PHP_SELF?cmd=showday&atime=$i\">$aday</a>\n";
		month_up_down($amonth, $i, $step, $max_apps);
		
		if ($hday["name"] != "")
			echo "<br><font class=\"inday\">{$hday['name']}</font>\n";
		
		print_month_events($amonth, $max_apps, $i);
		
		echo "</td>\n";
		
		if ($mod != "nokw") {
			echo "<td class=\"lightmonth\" align=\"center\" width=\"$width\" height=\"$height\">";
			printf("<a class=\"kw\" href=\"%s?cmd=showweek&atime=%s\">%s</a></td>\n",
				$PHP_SELF, $i, strftime("%V", $i));
		}
		echo "</tr>\n";
	}
	else{
		// unterschiedliche Darstellung je nach Art des Tages (Rang des Feiertages)
		switch ($hday["col"]) {
			case 1:
				echo "<a class=\"{$style}day\" href=\"$PHP_SELF?cmd=showday&atime=$i\">$aday</a>\n";
				month_up_down($amonth, $i, $step, $max_apps);
				echo "<br><font class=\"inday\">{$hday['name']}</font>";
				break;
			case 2:
				echo "<a class=\{$style}shday\" href=\"$PHP_SELF?cmd=showday&atime=$i\">$aday</a>\n";
				month_up_down($amonth, $i, $step, $max_apps);
				echo "<br><font class=\"inday\">{$hday['name']}</font>";
				break;
			case 3;
				echo "<a class=\"{$style}hday\" href=\"$PHP_SELF?cmd=showday&atime=$i\">$aday</a>\n";
				month_up_down($amonth, $i, $step, $max_apps);
				echo "<br><font class=\"inday\">{$hday['name']}</font>";
				break;
			default:
				echo "<a class=\"{$style}day\" href=\"$PHP_SELF?cmd=showday&atime=$i\">$aday</a>\n";
				month_up_down($amonth, $i, $step, $max_apps);
		}
		
		print_month_events($amonth, $max_apps, $i);
		
		echo "</td>\n";
		
	}
}

echo "</td></tr></table>\n</td></tr>\n";
echo "<tr><th>&nbsp;</th></tr>\n";

echo "</table></td></table>\n";
echo "<table width=\"98%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
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
	global $PHP_SELF, $atime, $CANONICAL_RELATIVE_PATH_STUDIP;
	if($atime == $day_timestamp){
		$spacer = TRUE;
		$up = FALSE;
		$a = $month_obj->numberOfEvents($day_timestamp) - $step - $max_events;
		$up = ($month_obj->numberOfEvents($day_timestamp) > $max_events && $step >= $max_events);
		if($a + $max_events > $max_events){
			if($up)
				echo "&nbsp; &nbsp; &nbsp;";
			else
				echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
			$tooltip = sprintf(_("noch %s Termine danach"), $a);
			$tooltip = tooltip($tooltip);
			echo "<a href=\"$PHP_SELF?cmd=showmonth&atime=$day_timestamp&step=";
			echo ($step + $max_events) . "\">";
			echo "<img src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/forumrotrunt.gif\" ";
			echo $tooltip . " border=\"0\"></a>\n";
			$spacer = FALSE;
		}
		if($up){
			if($spacer)
				echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
			$tooltip = sprintf(_("noch %s Termine davor"), $step);
			$tooltip = tooltip($tooltip);
			echo "<a href=\"$PHP_SELF?cmd=showmonth&atime=$day_timestamp&step=";
			echo ($step - $max_events) . "\">";
			echo "<img src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/forumrotrauf.gif\" ";
			echo $tooltip . " border=\"0\"></a>\n";
			$month_obj->setPointer($atime, $step);
		}
	}
	else if($month_obj->numberOfEvents($day_timestamp) > $max_events){
		echo "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
		$tooltip = sprintf(_("noch %s Termine danach"),
				$month_obj->numberOfEvents($day_timestamp) - $max_events);
		$tooltip = tooltip($tooltip);
		echo "<a href=\"$PHP_SELF?cmd=showmonth&atime=$day_timestamp&step=";
		echo ($max_events) . "\"><img src=\"$CANONICAL_RELATIVE_PATH_STUDIP/pictures/forumrotrunt.gif\" ";
		echo $tooltip . " border=\"0\"></a>\n";
	}
}
?>
