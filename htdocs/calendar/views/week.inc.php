<?
/**
* week.inc.php
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
// week.inc.php
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


echo "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" align=\"center\">\n";
echo "<table border=\"0\" width=\"100%\" cellspacing=\"1\" cellpadding=\"2\">\n";
echo "<tr><th colspan=\"$colspan_2\">\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr>\n";
echo "<th width=\"15%\"><a href=\"$PHP_SELF?cmd=showweek&atime=";
echo $aweek->getStart() - 1 . "\">&nbsp;";
echo "<img border=\"0\" src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/forumrotlinks.gif\" ";
echo tooltip(_("zurück")) . ">&nbsp;</a></th>\n";
echo "<th width=\"70%\" class=\"cal\">";
printf(_("%s. Woche vom %s bis %s"), strftime("%V", $aweek->getStart()),
		strftime("%x", $aweek->getStart()), strftime("%x", $aweek->getEnd()));
echo "</th>\n";
echo "<th width=\"15%\"><a href=\"$PHP_SELF?cmd=showweek&atime=";
echo $aweek->getEnd() + 259201 . "\">&nbsp;";
echo "<img border=\"0\" src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/forumrot.gif\" ";
echo tooltip(_("vor")) . ">&nbsp;</a></th>\n";
echo "</tr></table>\n</th></tr>\n";

echo "<tr><th width=\"4%\"$colspan_1>";
if ($st > 0) {
	echo "<a href=\"calendar.php?cmd=showweek&atime=$atime&wtime=" . ($st - 1) . "\">";
	echo "<img border=\"0\" src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/forumgraurauf.gif\" ";
	echo tooltip(_("zeig davor")) . "></a>\n";
}
else
	echo "&nbsp";
echo "</th>".$tab["table"][0];

echo "<th width=\"4%\"$colspan_1>";
if ($st > 0) {
	echo "<a href=\"$PHP_SELF?cmd=showweek&atime=$atime&wtime=" . ($st - 1) . "\">";
	echo "<img border=\"0\" src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/forumgraurauf.gif\" ";
	echo tooltip(_("zeig davor")) . "></a>\n";
}
else
	echo "&nbsp;";
echo "</th></tr>\n";

// Zeile mit Tagesterminen ausgeben
echo "<tr><th$colspan_1>" . _("Tag") . "</th>{$tab['table'][1]}<th$colspan_1>";
echo _("Tag") . "</th></tr>\n";
		
		
$j = $st;
for ($i = 2; $i < sizeof($tab["table"]); $i++) {
	echo "<tr>";
	
	if ($i % $rowspan == 0) {
		if ($rowspan == 1)
			echo "<th$height>$j</th>";
		else
			echo "<th rowspan=\"$rowspan\">$j</th>";
	}
	if ($rowspan > 1) {
		$minutes = (60 / $rowspan) * ($i % $rowspan);
		if ($minutes == 0)
			$minutes = "00";
		echo "<th$height><font size=\"-2\">$minutes</font></th>\n";
	}
	
	echo $tab["table"][$i];
	
	if ($rowspan > 1)
		echo "<th><font size=\"-2\">$minutes</font></th>\n";
	if ($i % $rowspan == 0) {
		if($rowspan == 1)
			echo "<th>$j</th>";
		else
			echo "<th rowspan=\"$rowspan\">$j</th>";
		$j = $j + ceil($calendar_user_control_data["step_week"] / 3600);
	}
	
	echo "</tr>\n";
}

echo "<tr><th colspan=\"$colspan_2\">\n";
echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
echo "<tr><th width=\"4%\">";
if ($et < 23) {
	echo "<a href=\"$PHP_SELF?cmd=showweek&atime=$atime&wtime=" . ($et + 1) . "\">";
	echo "<img border=\"0\" src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/forumgraurunt.gif\" ";
	echo tooltip(_("zeig danach")) . "></a>";
}
else
	echo "&nbsp";
echo "</th><th width=\"92%\">&nbsp;</th>";
echo "<th width=\"4%\">";
if ($et < 23) {
	echo "<a href=\"$PHP_SELF?cmd=showweek&atime=$atime&wtime=" . ($et + 1) . "\">";
	echo "<img border=\"0\" src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/forumgraurunt.gif\" ";
	echo tooltip(_("zeig danach")) . "></a>";
}
else
	echo "&nbsp;";
echo "</th></tr>\n</table>\n";
echo "</th></tr>\n</table>\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";
jumpTo($jmp_m, $jmp_d, $jmp_y);
echo "</table>\n";
echo "<tr><td class=\"blank\">&nbsp;";
?>
