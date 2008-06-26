<?
# Lifter002: TODO
# Lifter005: TODO
/**
* day.inc.php
*
* Shows the day calender
*
* @author		Peter Thienel <pthienel@web.de>
* @author 		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
* @version		$Id$
* @access		public
* @package		calendar
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// day.inc.php
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

/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);

// Begin of output
//TODO: templates
include('lib/include/html_head.inc.php');

if ($forum["jshover"] == 1 AND $auth->auth["jscript"]) { // JS an und erwuenscht?
	echo "<script language=\"JavaScript\">";
	echo "var ol_textfont = \"Arial\"";
	echo "</script>";
	echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
	echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"".$GLOBALS['ASSETS_URL']."javascripts/overlib.js\"></SCRIPT>";
}

include('lib/include/header.php');
include('lib/include/links_sms.inc.php');

echo "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"60%\"><br/>\n";
echo "<table width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"100%\">\n";
echo "<table class=\"steelgroup0\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr>\n";
echo "<td align=\"center\" width=\"10%\" height=\"40\"><a href=\"$PHP_SELF?cmd=showday&atime=";
echo $atime - 86400 . "\">\n";
$tooltip = tooltip(_("zurück"));
echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_previous.gif\"$tooltip></a></td>\n";
echo "<td class=\"calhead\" width=\"80%\" class=\"cal\"><b>\n";

echo $aday->toString("LONG") . ", " . $aday->getDate();
// event. Feiertagsnamen ausgeben
if ($hday = holiday($atime))
	echo "<br>" . $hday["name"];

echo "</b></td>\n";
echo "<td align=\"center\" width=\"10%\"><a href=\"$PHP_SELF?cmd=showday&atime=";
echo $atime + 86400 . "\">\n";
$tooltip = tooltip(_("vor"));
echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_next.gif\"$tooltip></a></td>\n";
echo "</tr>\n";

if ($st > 0) {
	echo "<tr><td align=\"center\" colspan=\"3\"><a href=\"$PHP_SELF?cmd=showday&atime=";
	echo ($atime - ($at - $st + 1) * 3600) . "\">";
	$tooltip = tooltip(_("zeig davor"));
	echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_up.gif\"$tooltip></a></td></tr>\n";
}
echo "</table>\n</td></tr>\n<tr><td class=\"blank\">\n";
echo "<table class=\"steelgroup0\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"1\">";

echo $tab["table"];

if ($et < 23) {
	echo "<tr><td align=\"center\" colspan=\"" . $tab["max_columns"] . "\">";
	echo "<a href=\"$PHP_SELF?cmd=showday&atime=";
	echo ($atime + ($et - $at + 1) * 3600) . "\">";
	$tooltip = tooltip(_("zeig danach"));
	echo "<img border=\"0\" src=\"".$GLOBALS['ASSETS_URL']."images/calendar_down.gif\"$tooltip></a></td></tr>\n";
}
else
	echo "<tr><td colspan=\"" . $tab["max_columns"] . "\">&nbsp;</td></tr>\n";

echo "</table>\n</td></tr>\n</table>\n<td width=\"40%\" valign=\"top\" class=\"blank\"><br/>\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
echo "<tr><td>\n";
echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
jumpTo($jmp_m, $jmp_d, $jmp_y);
echo "</table></td></tr>\n";
$link = "$PHP_SELF?cmd=showday&atime=";
echo "<tr><td align=\"center\">".includeMonth($atime, $link)."</td></tr>\n";
echo "</table>\n";
?>
