<?
# Lifter002: TODO
/**
* show_log.php
*
* Display event log
*
*
* @author               Tobias Thelen <tobias.thelen@uni-osnabrueck.de>
* @version              $Id$
* @access               public
* @package              studip_core
* @modulegroup          views
* @module               logging
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// functions.php
// Stud.IP Kernfunktionen
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>,
// Ralf Stockmann <rstockm@gwdg.de>, André Noack André Noack <andre.noack@gmx.net>
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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("root");

if (!$LOG_ENABLE) {
        print '<p>' . _("Log-Modul abgeschaltet."). '</p>';
	include ('lib/include/html_end.inc.php');
        page_close();
        die;
}

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ('lib/msg.inc.php'); //Funktionen fuer Nachrichtenmeldungen
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('config.inc.php');
require_once ('lib/log_events.inc.php');
require_once('lib/classes/Table.class.php');
require_once('lib/classes/ZebraTable.class.php');
require_once($RELATIVE_PATH_RESOURCES.'/lib/ResourceObject.class.php');
require_once ('lib/show_log.inc.php');

$CURRENT_PAGE = _("Log");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins

// main logic

$showentries=0;
if ($rewind_x) { // found, aber reset gedrückt
	$searchmode="search";
	$showentries=0;
} else if ($search_x || $searchmode=='found') { // suche oder gefunden
	if ($objecttype=='sem') {
		$objs=showlog_search_seminar($searchobject);
	} elseif ($objecttype=='res') {
		$objs=showlog_search_resource($searchobject);
	} elseif ($objecttype=='inst') {
		$objs=showlog_search_inst($searchobject);
	} elseif ($objecttype=='user') {
		$objs=showlog_search_user($searchobject);
	}
	$searchmode="found";
	if ($search_x) {
		$showentries=0;
	} else {
		$showentries=1;
	}
} else if ($searchmode=="search") { // alle anzeigen ohne Suche
	$showentries=1;
}

if (!$searchmode) $searchmode="search";

//  cleanup
$clensed=cleanup_log_events(); // returns # of deleted events

//
// Start output
//
$container=new ContainerTable();
start_form();
echo $container->headerRow("<b>&nbsp;"._("Anzeige der Log-Events")."</b>");
echo $container->openCell();

$content=new ContentTable();
echo $content->open();
if ($clensed) {
	$msg="info§"."$clensed "._("abgelaufene Einträge wurden gelöscht.");
	echo $content->openRow();
	echo $content->openCell();
	parse_msg($msg);
}
echo $content->openRow();
echo $content->openCell();
showlog_search_form($actionfilter, $searchmode, $objecttype, $objs, $searchobject, $object);
echo $content->openRow();
echo $content->openCell(array("colspan"=>"2"));

if (isset($weiter_x)) {
		$from=$from+50;
} else if (isset($zurueck_x)) {
		$from=max(0,$from-50);
} else {
	$from=0;
}

if ($showentries) {
	showlog_entries($from, $showlogmode, $actionfilter, $searchmode, $object);

}

echo $content->close();
echo $container->blankRow();
echo $container->close();
end_form($from);
include ('lib/include/html_end.inc.php');
page_close();
//<!-- $Id$ -->
