<?
/*

Copyright (C) 2002 

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
page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("dozent");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

$i_page = "meine_seminare.php";
$EXPORT_ENABLE = true;
$XSLT_ENABLE = false;
$PATH_EXPORT = "export";
// -- here you have to put initialisations for the current page

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");

require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT" . "/export_config.inc.php");

if ($EXPORT_ENABLE)
{
	if (($page==2) AND $XSLT_ENABLE AND $skip_page_2) $page=3;
	//Exportmodul einbinden
	if ($range_id != "")	
		include($ABSOLUTE_PATH_STUDIP ."" . $PATH_EXPORT . "/export_xml.inc.php");
	elseif (($xml_file_id != "") AND ($page != 3))
		include($ABSOLUTE_PATH_STUDIP ."" . $PATH_EXPORT . "/export_choose_xslt.inc.php");

	if ( (isset($choose)) AND (isset($format)) AND ($XSLT_ENABLE) AND 
		(((($o_mode == "processor") OR ($o_mode == "passthrough")) AND ($object_counter > 0)) OR ($page == 3)))
		include($ABSOLUTE_PATH_STUDIP ."" . $PATH_EXPORT . "/export_run_xslt.inc.php");
	
}
else 
{
	require_once ($ABSOLUTE_PATH_STUDIP."msg.inc.php");
	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	parse_window ("error§Das Exportmodul ist nicht eingebunden. Damit Daten im XML-Format exportiert werden k&ouml;nnen, muss das Exportmodul in den Systemeinstellungen freigeschaltet werden. Wenden Sie sich bitte an den Administrator.", "§",
				"Exportmodul nicht eingebunden");
}
page_close();
?>