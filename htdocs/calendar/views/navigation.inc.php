<?
/**
* navigation.inc.php
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
// navigation.inc.php
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


require($RELATIVE_PATH_CALENDAR . "/calendar_links.inc.php");

if ($cmd != "changeview") {
	echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
	echo "<tr>\n";
	echo "<td class=\"topic\">&nbsp;<img src=\"{$CANONICAL_RELATIVE_PATH_STUDIP}pictures/meinetermine.gif\" ";
	$tooltip = tooltip(_("Termine"));
	echo "border=\"0\" align=\"absmiddle\" $tooltip><b>&nbsp;";
	echo $title . "</b></td></tr>\n";

	if ($intro) {
	echo "<tr><td class=\"blank\"><font size=\"2\">&nbsp;</font>\n";
	echo "<blockquote>\n";
	echo _("Dieser Terminkalender verwaltet Ihre Termine. Sie k&ouml;nnen Termine eintragen, &auml;ndern, gruppieren und sich &uuml;bersichtlich anzeigen lassen.");
	echo "</blockquote>\n<font size=\"2\">&nbsp;</font></td></tr>\n";
	}
	else
		echo "<tr><td class=\"blank\" width=\"100%\">&nbsp;</td></tr>\n";
		
	echo "</table>\n";
}

?>
