<?
/*
links_about.inc.php - Navigation fuer die Uebersichtsseiten.
Copyright (C) 2002	Stefan Suchi <suchi@gmx.de>, 
				Ralf Stockmann <rstockm@gwdg.de>, 
				Cornelis Kater <ckater@gwdg.de
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

require_once "$ABSOLUTE_PATH_STUDIP/reiter.inc.php";

$reiter=new reiter;

//Create Reitersystem
$reiter=new reiter;
//Topkats
if (!$perm->have_perm("admin")) {
$structure["calender"]=array ("topKat"=>"", "name"=>_("Terminkalender"), "link"=>"calendar.php", "active"=>FALSE);
$structure["timetable"]=array ("topKat"=>"", "name"=>_("Stundenplan"), "link"=>"mein_stundenplan.php", "active"=>FALSE);
}
$structure["contact"]=array ("topKat"=>"", "name"=>_("Adressbuch"), "link"=>"contact.php", "active"=>FALSE);
$structure["post"]=array ("topKat"=>"", "name"=>_("Nachrichten"), "link"=>"sms_box.php", "active"=>FALSE);
$structure["chat"]=array ("topKat"=>"", "name"=>_("Chat"), "link"=>"chat_online.php", "active"=>FALSE);
$structure["online"]=array ("topKat"=>"", "name"=>_("Wer ist online?"), "link"=>"online.php", "active"=>FALSE);






//Bottomkats

if ($atime) {
	$xx = $atime;
} else {
	$xx = "";
}
$structure["in"] = array ("topKat"=>"post", "name"=>_("empfangene"), "link"=>"sms_box.php?sms_inout=in", "active"=>FALSE);
$structure["out"] = array ("topKat"=>"post", "name"=>_("gesendete"), "link"=>"sms_box.php?sms_inout=out", "active"=>FALSE);
$structure["write"] = array ("topKat"=>"post", "name"=>_("Neue Nachricht schreiben"), "link"=>"sms_send.php", "active"=>FALSE);
$structure["calendar_day"] = array ("topKat"=>"calender", "name"=>_("Tag"), "link"=>"calendar.php?cmd=showday&atime=$atime", "active"=>FALSE);
$structure["calendar_week"] = array ("topKat"=>"calender", "name"=>_("Woche"), "link"=>"calendar.php?cmd=showweek&atime=$atime", "active"=>FALSE);
$structure["calendar_month"] = array ("topKat"=>"calender", "name"=>_("Monat"), "link"=>"calendar.php?cmd=showmonth&atime=$atime", "active"=>FALSE);
$structure["calendar_year"] = array ("topKat"=>"calender", "name"=>_("Jahr"), "link"=>"calendar.php?cmd=showyear&atime=$atime", "active"=>FALSE);
$structure["calendar_edit"] = array ("topKat"=>"calender", "name"=>_("Bearbeiten"), "link"=>"calendar.php?cmd=edit&atime=$atime", "active"=>FALSE);
$structure["calendar_bind"] = array ("topKat"=>"calender", "name"=>_("Veranstaltungstermine"), "link"=>"calendar.php?cmd=bind&atime=$atime", "active"=>FALSE);
$structure["calendar_changeview"] = array ("topKat"=>"calender", "name"=>_("Ansicht anpassen"), "link"=>"calendar.php?cmd=changeview&atime=$atime", "active"=>FALSE);
$structure["timetable_timetable"] = array ("topKat"=>"timetable", "name"=>_("Stundenplan"), "link"=>"mein_stundenplan.php", "active"=>FALSE);
$structure["timetable_printview"] = array ("topKat"=>"timetable", "name"=>_("Druckansicht"), "link"=>"mein_stundenplan.php?print_view=TRUE", target=>"_new", "active"=>FALSE);
$structure["timetable_changeview"] = array ("topKat"=>"timetable", "name"=>_("Ansicht anpassen"), "link"=>"mein_stundenplan.php?change_view=TRUE", "active"=>FALSE);

$structure["contact_viewalpha"] = array ("topKat"=>"contact", "name"=>_("Alphabetisch"), "link"=>"contact.php?view=alpha", "active"=>FALSE);
$structure["contact_viewgruppen"] = array ("topKat"=>"contact", "name"=>_("Gruppenansicht"), "link"=>"contact.php?view=gruppen", "active"=>FALSE);
$structure["contact_statusgruppen"] = array ("topKat"=>"contact", "name"=>_("Gruppenverwaltung"), "link"=>"contact_statusgruppen.php", "active"=>FALSE);

//View festlegen
switch ($i_page) {
	case "sms_box.php" : 
		$reiter_view = $sms_data["view"]; 
	break;
	case "sms_send.php" : 
		$reiter_view = "write"; 
	break;
	case "online.php" : 
		$reiter_view = "online"; 
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
	default :
		$reiter_view="post";
	break;
}

$reiter->create($structure, $reiter_view);
?>
