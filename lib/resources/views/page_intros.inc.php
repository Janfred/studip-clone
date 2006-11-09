<?
/**
* page_intros.inc.php
*
* library for the messages on the pages, contents of the infoboxes and stuff to display
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		page_intros.inc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// page_intros.inc.php
// Nachrichten, Inhalt der Infokaesten und andere Inhalte der Seiten der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

require_once ($GLOBALS['RELATIVE_PATH_RESOURCES']."/lib/ResourceObject.class.php");
require_once ($GLOBALS['RELATIVE_PATH_RESOURCES']."/lib/RoomGroups.class.php");


if ($resources_data["actual_object"]) {
	$currentObject =& ResourceObject::Factory($resources_data["actual_object"]);
	$currentObjectTitelAdd=": ".(($currentObject->getCategoryName()) ? $currentObject->getCategoryName() : _("Hierachieebene"));
	if ($currentObjectTitelAdd)
		$currentObjectTitelAdd=": ";
	$currentObjectTitelAdd=": ".$currentObject->getName()."&nbsp;<font size=-1>(".$currentObject->getOwnerName().")</font>";
}


switch ($view) {
	//Reiter "Uebersicht"
	case "plan":
		$page_intro=_("Auf dieser Seite k&ouml;nnen Sie sich einen Wochenplan als CSV-Datei ausgeben lassen.");
		$title=_("Spezielle Funktionen");
	break;
	case "regular":
		$page_intro=_("Auf dieser Seite k&ouml;nnen Sie sich einen Semesterplan als CSV-Datei ausgeben lassen.");
		$title=_("Spezielle Funktionen");
	break;
	case "diff":
		$page_intro=_("Auf dieser Seite k&ouml;nnen Sie sich die w&ouml;chentliche Differenzliste der Belegung aller R&auml;ume als CSV-Datei ausgeben lassen.");
		$title=_("Spezielle Funktionen");
	break;

	case "resources":
	case "_resources":
		$page_intro=_("Auf dieser Seite k&ouml;nnen Sie durch alle Ressourcen bzw. Ebenen, auf die Sie Zugriff haben, navigieren und Ressourcen verwalten.");
		$title=_("&Uuml;bersicht der Ressourcen");
	break;
	case "search":
		$page_intro=_("Sie k&ouml;nnen hier nach Ressourcen suchen. Sie haben die M&ouml;glichkeit, &uuml;ber ein Stichwort oder bestimmte Eigenschaften Ressourcen zu suchen oder sich durch die Ebenen zu navigieren.");
		$title=_("Suche nach Ressourcen");
		$infobox = array(
					array  ("kategorie" => _("Aktionen:"),
							"eintrag" => array (
								array	("icon" => "suchen.gif",
									"text"  => (($resources_data["search_mode"] == "browse") || (!$resources_data["search_mode"]))? sprintf(_("Gew&uuml;nschte Eigenschaften <br />%sangeben%s"), "<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."&mode=properties\">", "</a>") :  sprintf(_("Gew&uuml;nschte Eigenschaften <br />%snicht angeben%s"), "<a href=\"$PHP_SELF?view=search&quick_view_mode=".$view_mode."&mode=browse\">", "</a>")),
								array	("icon" => "meinetermine.gif",
									"text"  => (!$resources_data["check_assigns"])? sprintf(_("Gew&uuml;nschte Belegungszeit %sber&uuml;cksichtigen%s"), "<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."&check_assigns=TRUE\">", "</a>") :  sprintf(_("Gew&uuml;nschte Belegungszeit <br />%snicht ber&uuml;cksichtigen%s"), "<a href=\"$PHP_SELF?view=search&quick_view_mode=".$view_mode."&check_assigns=FALSE\">", "</a>")),
								array	("icon" => "cont_res5.gif",
									"text"  => ($resources_data["search_only_rooms"])? sprintf(_("Nur R&auml;ume %sanzeigen%s"), "<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."&search_only_rooms=0\">", "</a>") :  sprintf(_("Alle Ressourcen %sanzeigen%s"), "<a href=\"$PHP_SELF?view=search&quick_view_mode=".$view_mode."&search_only_rooms=1\">", "</a>")),
								array("icon" => "blank.gif",
									"text"  => "<br /><a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."&reset=TRUE\">".makeButton("neuesuche")."</a>"))));
		$infopic = "rooms.jpg";
		$clipboard = TRUE;
	break;
	//Reiter "Listen"
	case "lists":
	case "_lists":
	case "export_lists":
	case "search_list":
		if ($resources_data["list_open"])
			$page_intro= sprintf(_("Sie sehen alle Eintr&auml;ge in der Ebene <b>%s</b>"), getResourceObjectName($resources_data["list_open"]));
		$title=_("Bearbeiten und ausgeben von Listen");
		if ($resources_data["list_open"])
			$title.=" - "._("Ebene").": ".getResourceObjectName($resources_data["list_open"]);
		$infobox = array(
					array  ("kategorie"  => _("Information:"),
							"eintrag" => array (
								array ("icon" => "ausruf_small.gif",
									"text"  => ($resources_data["list_recurse"]) ? _("Untergeordnete Ebenen werden ausgegeben.") : _("Untergeordnete Ebenen werden <u>nicht</u> ausgegeben.")))),
					array  ("kategorie" => _("Aktionen:"),
							"eintrag" => array (
								array	("icon" =>  (!$resources_data["list_recurse"]) ? "on_small.gif" : "off_small.gif",
									"text"  => ($resources_data["list_recurse"]) ? sprintf(_("Ressourcen in untergeordneten Ebenen %snicht ausgeben%s."), "<a href=\"$PHP_SELF?nrecurse_list=TRUE\">", "</a>") :  sprintf(_("Ressourcen in untergeordneten Ebenen %s(mit) ausgeben%s"), "<a href=\"$PHP_SELF?recurse_list=TRUE\">", "</a>")))));
		$infopic = "rooms.jpg";
	break;

	//Reiter "Objekt"
	case "objects":
	case "edit_object_assign":
		$page_intro=_("Sie sehen hier die Einzelheiten der Belegung. Falls Sie &uuml;ber entsprechende Rechte verf&uuml;gen, k&ouml;nnen Sie sie bearbeiten oder eine neue Belegung erstellen.");
		$title=_("Belegungen anzeigen/bearbeiten").$currentObjectTitelAdd;
		if (($view_mode == "no_nav") || ($view_mode == "search")) {
			$infobox = array(
						array  ("kategorie" => _("Aktionen:"),
								"eintrag" => array (
									array	("icon" => "link_intern.gif",
										"text"  => "<a href=\"$PHP_SELF?quick_view=view_schedule&quick_view_mode=".$view_mode."\">"._("zur&uuml;ck zum Belegungsplan")."</a>"))));
		}
	break;
	case "edit_object_properties":
		$page_intro=_("Hier k&ouml;nnen Sie Ressourcen-Eigenschaften bearbeiten.");
		$title=_("Eigenschaften bearbeiten").$currentObjectTitelAdd;
	break;
	case "edit_object_perms":
		$page_intro=_("Hier k&ouml;nnen Sie Berechtigungen f&uuml;r den Zugriff auf die Ressource vergeben.")." <br /><font size=\"-1\">"._("<b>Achtung:</b> Alle hier erteilten Berechtigungen gelten ebenfalls f&uuml;r die Ressourcen, die der gew&auml;hlten Ressource untergeordnet sind!")."</font>";
		$title=_("Eigenschaften bearbeiten").$currentObjectTitelAdd;
	break;
	case "view_schedule":
		$page_intro=_("Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource anzeigen  und auf unterschiedliche Art darstellen lassen.");
		$title=_("Belegungszeiten ausgeben").$currentObjectTitelAdd;

		$infobox[0]["kategorie"] = _("Aktionen:");
		$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
								"text"  => sprintf (_("%sEigenschaften%s anzeigen"), "<a href=\"$PHP_SELF?quick_view=view_details&quick_view_mode=".$view_mode."\">", "</a>"));
		if (($ActualObjectPerms->havePerm("autor")) && ($currentObject->getCategoryId()))
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>sprintf (_("Eine neue Belegung %serstellen%s"), ($view_mode == "oobj") ? "<a href=\"$PHP_SELF?cancel_edit_assign=1&quick_view=openobject_assign&quick_view_mode=".$view_mode."\">" : "<a href=\"$PHP_SELF?cancel_edit_assign=1&quick_view=edit_object_assign&quick_view_mode=".$view_mode."\">", "</a>"));

		if ($view_mode == "search")
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>"<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."\">"._("zur&uuml;ck zur Suche")."</a>");

			if ($view_mode == "no_nav"){
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>"<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."\">"._("zur Ressourcensuche")."</a>");
				if (get_config('RESOURCES_ENABLE_SEM_SCHEDULE')){
					$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
								"text"  => sprintf (_("%sSemesterplan%s anzeigen"), "<a href=\"$PHP_SELF?quick_view=view_sem_schedule&quick_view_mode=".$view_mode."\">", "</a>"));
				}
			}
		if ($view_mode != "search" && $view_mode != "no_nav") {
			if ($SessSemName["class"] == "sem")
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
										"text"  => "<a href=\"seminar_main.php\">"._("zur&uuml;ck zur Veranstaltung")."</a>");
			if ($SessSemName["class"] == "inst")
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
										"text"  => "<a href=\"institut_main.php\">"._("zur&uuml;ck zur Einrichtung")."</a>");
		}

		//$infopic = "schedule.jpg";
	break;
	case "view_sem_schedule":
		$page_intro=_("Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource anzeigen  und auf unterschiedliche Art darstellen lassen.");
		$title=_("Belegungszeiten pro Semester ausgeben").$currentObjectTitelAdd;

		$infobox[0]["kategorie"] = _("Aktionen:");

		$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
								"text"  => sprintf (_("%sEigenschaften%s anzeigen"), "<a href=\"$PHP_SELF?quick_view=view_details&quick_view_mode=".$view_mode."\">", "</a>"));
		if (($ActualObjectPerms->havePerm("autor")) && ($currentObject->getCategoryId()))
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>sprintf (_("Eine neue Belegung %serstellen%s"), ($view_mode == "oobj") ? "<a href=\"$PHP_SELF?cancel_edit_assign=1&quick_view=openobject_assign&quick_view_mode=".$view_mode."\">" : "<a href=\"$PHP_SELF?cancel_edit_assign=1&quick_view=edit_object_assign&quick_view_mode=".$view_mode."\">", "</a>"));

		if ($view_mode == "search")
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>"<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."\">"._("zur&uuml;ck zur Suche")."</a>");

		if ($view_mode == "no_nav"){
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>"<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."\">"._("zur Ressourcensuche")."</a>");
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>sprintf (_("%sBelegungsplan%s anzeigen"), ($view_mode == "oobj") ? "<a href=\"$PHP_SELF?quick_view=openobject_schedule&quick_view_mode=".$view_mode."\">" : "<a href=\"$PHP_SELF?quick_view=view_schedule".(($view_mode == "no_nav") ? "&quick_view_mode=no_nav" : "")."\">", "</a>"));

		}
		if ($view_mode != "search" && $view_mode != "no_nav") {
			if ($SessSemName["class"] == "sem")
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
										"text"  => "<a href=\"seminar_main.php\">"._("zur&uuml;ck zur Veranstaltung")."</a>");
			if ($SessSemName["class"] == "inst")
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
										"text"  => "<a href=\"institut_main.php\">"._("zur&uuml;ck zur Einrichtung")."</a>");
			$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
								"text"  => "<a href=\"$PHP_SELF?view=view_sem_schedule&print_view=1\" target=\"_blank\">"
											. _("Druckansicht")
											. "</a>");
		}

		//$infopic = "schedule.jpg";
	break;
	case "view_group_schedule":
		$room_groups =& RoomGroups::GetInstance();
		$page_intro=_("Hier k&ouml;nnen Sie sich die Belegungszeiten einer Raumgruppe anzeigen lassen.");
		$title=_("Belegungszeiten einer Raumgruppe pro Semester ausgeben:") . '&nbsp;' . htmlReady($room_groups->getGroupName($resources_data['actual_room_group']));

		$infobox[0]["kategorie"] = _("Aktionen:");
		$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
								"text"  => "<a href=\"$PHP_SELF?view=view_group_schedule&print_view=1\" target=\"_blank\">"
											. _("Druckansicht")
											. "</a>");
	break;
	//Reiter "Anpassen"
	case "settings":
	case "edit_types":
		$page_intro=_("Verwalten Sie auf dieser Seite die Ressourcen-Typen, wie etwa R&auml;ume, Ger&auml;te oder Geb&auml;ude. Sie k&ouml;nnen jedem Typ beliebig viele Eigenschaften zuordnen.");
		$title=_("Typen bearbeiten");
	break;
	case "edit_properties":
		$page_intro=_("Verwalten Sie auf dieser Seite die einzelnen Eigenschaften. Diese Eigenschaften k&ouml;nnen Sie beliebigen Ressourcen-Typen zuweisen.");
		$title=_("Eigenschaften bearbeiten");
	break;
	case "edit_perms":
		$page_intro=_("Verwalten Sie hier AdministratorInnen des Systems, die Rechte &uuml;ber alle Ressourcen erhalten.");
		$title=_("Ressourcen-AdministratorInnen bearbeiten");
	break;
	case "edit_settings":
		$page_intro=_("Verwalten Sie hier grundlegende Einstellungen der Ressourcenverwaltung.");
		$title=_("Einstellungen der Ressourcenverwaltung");
	break;

	//Reiter Raumplanung
	case "requests_start":
		$page_intro=_("Auf dieser Seite wird Ihnen der Status der Anfragen aus Ihren Bereichen angezeigt. Sie k&ouml;nnen das Bearbeiten der Anfragen von hier aus starten.");
		$title=_("&Uuml;bersicht des Raumplanungs-Status");
	break;
	case "edit_request":
		$page_intro=_("Sie k&ouml;nnen hier die einzelnen Anfragen einsehen und passenden R&auml;ume ausw&auml;hlen sowie zuweisen.");
		$title=_("Bearbeiten der Anfragen");
		$infobox = array(
					array  ("kategorie"  => _("Information:"),
							"eintrag" => array (
								array ("icon" => "ausruf_small.gif",
									"text"  => ($resources_data["skip_closed_requests"]) ? _("Bereits bearbeitete Anfragen werden <u>nicht</u> angezeigt.") : _("Bereits bearbeitete Anfragen werden weiterhin angezeigt.")))),
					array  ("kategorie" => _("Aktionen:"),
							"eintrag" => array (
								array	("icon" =>  "link_intern.gif" ,
									"text"  =>  "<a href=\"javascript:void(null)\" onClick=\"window.open('resources.php?quick_view=search&quick_view_mode=no_nav','','scrollbars=yes,left=10,top=10,width=1000,height=680,resizable=yes')\" >"._("Ressourcen suchen")."</a>"),
								array	("icon" =>  (!$resources_data["skip_closed_requests"]) ? "off_small.gif" : "on_small.gif",
									"text"  => ($resources_data["skip_closed_requests"]) ? sprintf(_("Bearbeitete Anfragen %sanzeigen%s."), "<a href=\"$PHP_SELF?skip_closed_requests=FALSE\">", "</a>") :  sprintf(_("Bearbeitete Anfragen %snicht anzeigen%s"), "<a href=\"$PHP_SELF?skip_closed_requests=TRUE\">", "</a>")),
								array	("icon" =>  "nachricht1.gif",
									"text"  => sprintf(_("Nachrichten zu zugewiesenen Anfragen %sversenden%s."), "<a href=\"$PHP_SELF?snd_closed_request_sms=TRUE\">", "</a>")))));
		$infopic = "rooms.jpg";
		$clipboard = TRUE;
	break;
	case 'list_requests':
		$page_intro = sprintf(_("Sie sehen hier eine Liste aller offenen Anfragen, die Sortierung folgt der Einstellung unter %s&Uuml;bersicht%s."), '<a href="resources.php?view=requests_start&cancel_edit_request_x=1">', '</a>'). '<br/>'._("Ein Klick auf das Symbol nebem dem Z&auml;hler erlaubt es Ihnen, direkt zu der Anfrage zu springen.");
		$title = _("Anfragenliste");
	break;
	//all the intros in an open object (Veranstaltung, Einrichtung)
	case "openobject_main":
		$page_intro=sprintf(_("Auf dieser Seite sehen sie alle der %s zugeordneten Ressourcen."), $SessSemName["art_generic"]);
		$title=$SessSemName["header_line"]." - "._("Ressourcen&uuml;bersicht");
		$infobox = array(
					array  ("kategorie"  => _("Information:"),
							"eintrag" => array (
								array ("icon" => "ausruf_small.gif",
									"text"  => ($perm->have_studip_perm("autor", $SessSemName[1]) ?
												(($SessSemName["class"] == "sem") ? _("Als Teilnehmer der Veranstaltung haben Sie die M&ouml;glichkeit, diese Ressourcen frei zu belegen oder den Belegungsplan einzusehen.") :
																				_("Als Mitarbeiter der Einrichtung haben Sie die M&ouml;glichkeit, diese Ressourcen frei zu belegen oder den Belegungsplan einzusehen.")) :
												(($SessSemName["class"] == "sem") ? _("Sie k&ouml;nnen hier die Details und den Belegungsplan der dieser Veranstaltung zugeordneten Ressourcen einsehen.") :
																				_("Sie k&ouml;nnen hier den Details und Belegungsplan der dieser Einrichtung zugeordneten Ressourcen einsehen.")))))));
		$infopic = "schedule.jpg";
	break;
	case "openobject_details":
	case "view_details":
		if ($resources_data["actual_object"])
			$page_intro= sprintf(_("Hier sehen Sie detaillierte Informationen der Ressource %s"), "<b>".$currentObject->getName()."</b> (".(($currentObject->getCategoryName()) ? $currentObject->getCategoryName() : _("Hierachieebene")).").");
		if ($view_mode == "oobj")
			$title=$SessSemName["header_line"]." - "._("Ressourcendetails");
		else
			$title=_("Anzeige der Ressourceneigenschaften");

		if (($view_mode == "no_nav") || ($view_mode == "search")) {
			$infobox[0]["kategorie"] = _("Aktionen:");

			if (is_object($currentObject)) {
				if ($currentObject->getCategoryId())
					$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
											"text"  =>sprintf (_("%sBelegungsplan%s anzeigen"), ($view_mode == "oobj") ? "<a href=\"$PHP_SELF?quick_view=openobject_schedule&quick_view_mode=".$view_mode."\">" : "<a href=\"$PHP_SELF?quick_view=view_schedule".(($view_mode == "no_nav") ? "&quick_view_mode=no_nav" : "")."\">", "</a>"));
				if (($ActualObjectPerms->havePerm("autor")) && ($currentObject->getCategoryId()))
					$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
											"text"  =>sprintf (_("Eine neue Belegung %serstellen%s"), ($view_mode == "oobj") ? "<a href=\"$PHP_SELF?cancel_edit_assign=1&quick_view=openobject_assign&quick_view_mode=".$view_mode."\">" : "<a href=\"$PHP_SELF?cancel_edit_assign=1&quick_view=edit_object_assign&quick_view_mode=".$view_mode."\">", "</a>"));
			}

			if ($view_mode == "no_nav")
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
									"text"  =>"<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."\">"._("zur Ressourcensuche")."</a>");

			if ($view_mode != "search" && $view_mode != "no_nav") {
				if ($SessSemName["class"] == "sem")
					$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
											"text"  => "<a href=\"seminar_main.php\">"._("zur&uuml;ck zur Veranstaltung")."</a>");
				if ($SessSemName["class"] == "inst")
					$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
											"text"  => "<a href=\"institut_main.php\">"._("zur&uuml;ck zur Einrichtung")."</a>");
			}

			if ($view_mode == "search")
				$infobox[0]["eintrag"][] = array ("icon" => "link_intern.gif",
										"text"  =>"<a href=\"$PHP_SELF?quick_view=search&quick_view_mode=".$view_mode."\">"._("zur&uuml;ck zur Suche")."</a>");
		$infopic = "schedule.jpg";
		}
	break;
	case "openobject_schedule":
		if ($resources_data["actual_object"])
			$page_intro=sprintf(_("Hier k&ouml;nnen Sie sich die Belegungszeiten der Ressource %s ausgeben lassen"), "<b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().")");
		$title=$SessSemName["header_line"]." - "._("Ressourcenbelegung");
	break;
	case "openobject_assign":
		if ($resources_data["actual_object"])
			$page_intro=sprintf(_("Anzeigen der der Belegung der Ressource %s. Sie k&ouml;nnen die Belegung auch bearbeiten, falls Sie entsprechende Rechte besitzen, oder eine neue Belegung erstellen."), "<b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").");
		$title=$SessSemName["header_line"]." - ".("Belegung anzeigen/bearbeiten");
	break;
	case "edit_object_perms":
		if ($resources_data["actual_object"])
			$page_intro=sprintf(_("Vergeben von Rechten auf die Ressource %s"), "<b>".$currentObject->getName()."</b> (".$currentObject->getCategoryName().").");
		$title=sprintf(_("Vergeben von Berechtigungen - Objekt%s"), $currentObjectTitelAdd);
	break;
	//default
	default:
		$page_intro=_("Sie befinden sich in der Ressourcenverwaltung von Stud.IP. Sie k&ouml;nnen hier R&auml;ume, Geb&auml;ude, Ger&auml;te und andere Ressourcen verwalten.");
		$title=_("&Uuml;bersicht der Ressourcen");
	break;
	}
?>
