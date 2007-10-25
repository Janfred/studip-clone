<?

/**
* displays tabs for messaging pages
*
* @author				Suchi & Berg GmbH <info@data-quest.de>, Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de>, Nils K. Windisch <studip@nkwindisch.de>
* @access				public
* @modulegroup	Messaging
* @module				links_sms.php
* @package			Stud.IP-Core
*/

/*
links_sms.inc.php - Navigation fuer die Uebersichtsseiten.
Copyright (C) 2002	Stefan Suchi <suchi@gmx.de>,
				Ralf Stockmann <rstockm@gwdg.de>,
				Cornelis Kater <ckater@gwdg.de,
				Suchi & Berg GmbH <info@data-quest.de>

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

require_once 'lib/include/reiter.inc.php';

$reiter=new reiter;

//Create Reitersystem
$reiter=new reiter;
//Topkats
if (!$perm->have_perm("admin")) {
	if ($GLOBALS['CALENDAR_ENABLE'])
		$structure["calendar"]=array ("topKat"=>"", "name"=>_("Terminkalender"), "link"=>"calendar.php", "active"=>FALSE);
	$structure["timetable"]=array ("topKat"=>"", "name"=>_("Stundenplan"), "link"=>"mein_stundenplan.php", "active"=>FALSE);
}
$structure["contact"]=array ("topKat"=>"", "name"=>_("Adressbuch"), "link"=>"contact.php", "active"=>FALSE);
$structure["post"]=array ("topKat"=>"", "name"=>_("Nachrichten"), "link"=>"sms_box.php", "active"=>FALSE);
$structure["chat"]=array ("topKat"=>"", "name"=>_("Chat"), "link"=>"chat_online.php", "active"=>FALSE);
$structure["online"]=array ("topKat"=>"", "name"=>_("Online"), "link"=>"online.php", "active"=>FALSE);

//Bottomkats

$structure["in"] = array ("topKat"=>"post", "name"=>_("empfangene"), "link"=>"sms_box.php?sms_inout=in", "active"=>FALSE);
$structure["out"] = array ("topKat"=>"post", "name"=>_("gesendete"), "link"=>"sms_box.php?sms_inout=out", "active"=>FALSE);
$structure["write"] = array ("topKat"=>"post", "name"=>_("Neue Nachricht schreiben"), "link"=>"sms_send.php", "active"=>FALSE);
$structure["adjust"] = array ("topKat"=>"post", "name"=>_("Messaging anpassen"), "link"=>"".$GLOBALS['PHP_SELF']."?change_view=TRUE", "active"=>FALSE);
$structure["online2"] = array ("topKat"=>"online", "name"=>_("Wer ist online?"), "link"=>"online.php", "active"=>FALSE);
$structure["adjust_online"] = array ("topKat"=>"online", "name"=>_("Messaging anpassen"), "link"=>"".$GLOBALS['PHP_SELF']."?change_view=TRUE", "active"=>FALSE);
if ($GLOBALS['CALENDAR_ENABLE']) {
	$structure["calendar_day"] = array ("topKat"=>"calendar", "name"=>_("Tag"), "link"=>"calendar.php?cmd=showday&atime=$atime", "active"=>FALSE);
	$structure["calendar_week"] = array ("topKat"=>"calendar", "name"=>_("Woche"), "link"=>"calendar.php?cmd=showweek&atime=$atime", "active"=>FALSE);
	$structure["calendar_month"] = array ("topKat"=>"calendar", "name"=>_("Monat"), "link"=>"calendar.php?cmd=showmonth&atime=$atime", "active"=>FALSE);
	$structure["calendar_year"] = array ("topKat"=>"calendar", "name"=>_("Jahr"), "link"=>"calendar.php?cmd=showyear&atime=$atime", "active"=>FALSE);
	$structure["calendar_edit"] = array ("topKat"=>"calendar", "name"=>_("Termin anlegen/bearbeiten"), "link"=>"calendar.php?cmd=edit&atime=$atime", "active"=>FALSE);
	$structure["calendar_bind"] = array ("topKat"=>"calendar", "name"=>_("Veranstaltungstermine"), "link"=>"calendar.php?cmd=bind&atime=$atime", "active"=>FALSE);
	$structure["calendar_export"] = array ("topKat"=>"calendar", "name"=>_("Export/Sync"), "link"=>"calendar.php?cmd=export&atime=$atime", "active"=>FALSE);
	$structure["calendar_changeview"] = array ("topKat"=>"calendar", "name"=>_("Ansicht anpassen"), "link"=>"calendar.php?cmd=changeview&atime=$atime", "active"=>FALSE);
}
$structure["timetable_timetable"] = array ("topKat"=>"timetable", "name"=>_("Stundenplan"), "link"=>"mein_stundenplan.php", "active"=>FALSE);
$structure["timetable_printview"] = array ("topKat"=>"timetable", "name"=>_("Druckansicht"), "link"=>"mein_stundenplan.php?print_view=TRUE", 'target'=>"_new", "active"=>FALSE);
$structure["timetable_changeview"] = array ("topKat"=>"timetable", "name"=>_("Ansicht anpassen"), "link"=>"mein_stundenplan.php?change_view=TRUE", "active"=>FALSE);

$structure["contact_viewalpha"] = array ("topKat"=>"contact", "name"=>_("Alphabetisch"), "link"=>"contact.php?view=alpha", "active"=>FALSE);
$structure["contact_viewgruppen"] = array ("topKat"=>"contact", "name"=>_("Gruppenansicht"), "link"=>"contact.php?view=gruppen", "active"=>FALSE);
$structure["contact_statusgruppen"] = array ("topKat"=>"contact", "name"=>_("Gruppenverwaltung"), "link"=>"contact_statusgruppen.php", "active"=>FALSE);
$structure["contact_export"] = array ("topKat"=>"contact", "name"=>_("VCF-Export"), "link"=>"contact_export.php", "active"=>FALSE);

//View festlegen
switch ($i_page) {
	case "sms_box.php" :
		if ($change_view == TRUE || $messaging_cmd == "change_view_insert") {
			$reiter_view = "adjust";
		} else {
			$reiter_view = $sms_data["view"];
		}
	break;
	case "sms_send.php" :
		if ($change_view == TRUE || $messaging_cmd == "change_view_insert") {
			$reiter_view = "adjust";
		} else {
			$reiter_view = "write";
		}
	break;
	case "online.php" :
		if ($change_view == TRUE || $messaging_cmd == "change_view_insert") {
			$reiter_view = "adjust_online";
		} else	{
			$reiter_view = "online";
		}
	break;
	case "chat_online.php" :
		$reiter_view = "chat";
	break;
	case "contact.php":
		if ($contact["view"] == "gruppen") {
			$reiter_view = "contact_viewgruppen";
		}
		if ($contact["view"] == "alpha"){
			$reiter_view = "contact_viewalpha";
		}
	break;
	case "calendar.php" :
		if (!$GLOBALS['CALENDAR_ENABLE'])
			break;
		switch($cmd) {
			case "showday":
				$reiter_view = "calendar_day";
			break;
			case "showweek":
				$reiter_view = "calendar_week";
			break;
			case "showmonth":
				$reiter_view = "calendar_month";
			break;
			case "showyear":
				$reiter_view = "calendar_year";
			break;
			case "edit":
				$reiter_view = "calendar_edit";
			break;
			case "bind":
				$reiter_view = "calendar_bind";
			break;
			case "export":
				$reiter_view = "calendar_export";
			break;
			case "changeview":
				$reiter_view = "calendar_changeview";
			break;
		}
	break;
	case "mein_stundenplan.php":
		if ($change_view) {
			$reiter_view = "timetable_changeview";
		} else {
			$reiter_view = "timetable_timetable";
		}
	break;
	case "contact_statusgruppen.php":
		$reiter_view = "contact_statusgruppen";
	break;
	case "contact_export.php":
		$reiter_view = "contact_export";
	break;
	default :
		$reiter_view="post";
	break;
}

$reiter->create($structure, $reiter_view);
?>
