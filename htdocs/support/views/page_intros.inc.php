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
* @modulegroup	resources
* @module		page_intros.inc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// page_intros.inc.php
// Nachrichten, Inhalt der Infokaesten und andere Inhalte der Seiten der Supportdatenbank
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


switch ($supportdb_data["view"]) {
	//Reiter "Uebersicht"
	case "overview":
		$page_intro=_("Hier k&ouml;nnen Sie Einzelheiten zu ihrem abgeschlossenen Supportvertrag wie den Punktestand oder die Laufzeit einsehen.");
		$title=_("&Uuml;berblick &uuml;ber ihre Supportvertr&auml;ge");
		$infobox[0]["kategorie"]=_("Information:");
		$infobox[0]["eintrag"][] = array ("icon" => "pictures/ausruf_small.gif", "text"  =>sprintf (_("Insgesamt <b>%s</b> Punkte, davon noch <b>%s</b> verf&uuml;gbar"), calculateGlobalPoints($SessSemName[1]), calculateGlobalRemainingPoints ($SessSemName[1])));
		if ($rechte) {
			$infobox[1]["kategorie"]=_("Aktionen:");
			$infobox[1]["eintrag"][] = array ("icon" => "pictures/forumrot.gif", "text"  =>sprintf (_("Einen neuen Vertrag %sanlegen%s"), "<a href=\"$PHP_SELF?view=overview&create_con=TRUE#a\">", "</a>"));
		}							
		$infopic = "pictures/rooms.jpg";
	break;
	case "requests":
		$conObject = new ContractObject ($supportdb_data["actual_con"]);
		$page_intro=sprintf (_("Sie sehen hier die letzten 10 Anfragen, die im Rahmen des Supportvertrages vom <b>%s</b> bis <b>%s</b> gestellt wurden. Nutzen Sie f&uuml;r &auml;ltere Anfragen die Suchfunktion."), date("d.m.Y", $conObject->getContractBegin()), date("d.m.Y", $conObject->getContractEnd()));
		$title=_("Anfragen");
		$infobox[0]["kategorie"]=_("Information:");
		$infobox[0]["eintrag"][] = array ("icon" => "pictures/ausruf_small.gif", "text"  =>(countUnassignedTopics ($SessSemName[1]) ? sprintf(_("Es liegen noch <b>%s</b> unbeantwortete Themen im Forum vor"), countUnassignedTopics ($SessSemName[1])) : _("Im Augenblick sind alle Themen im Forum mit Anfragen verkn&uuml;pft oder es liegen keine Themen vor.")));
		if ($rechte) {
			$infobox[1]["kategorie"]=_("Aktionen:");
			$infobox[1]["eintrag"][] = array ("icon" => "pictures/forumrot.gif", "text"  =>sprintf (_("Eine neue Anfrage %sanlegen%s"), "<a href=\"$PHP_SELF?view=requests&create_req=TRUE#a\">", "</a>"));
		}							
		$infopic = "pictures/rooms.jpg";
	break;
	case "events":
		$page_intro=_("Sie k&ouml;nnen hier BEarbeitungszeiten zu einem Request anlegen oder bearbeiten");
		$title=_("Bearbeitungszeiten");
		$infobox = array(
/*					array  ("kategorie"  => _("Information:"), 
							"eintrag" => array (
								array ("icon" => "pictures/ausruf_small.gif", 	
									"text"  => ($resources_data["list_recurse"]) ? _("Untergeordnete Ebenen werden ausgegeben.") : _("Untergeordnete Ebenen werden <u>nicht</u> ausgegeben.")))),*/
					array  ("kategorie" => "Aktionen:", 
							"eintrag" => array (
								array	("icon" => "pictures/suchen.gif",
									"text"  => (($resources_data["search_mode"] == "browse") || (!$resources_data["search_mode"]))? sprintf(_("Ressourcen &uuml;ber ihre %sEigenschaften%s suchen"), "<a href=\"$PHPSELF?view=search&mode=properties\">", "</a>") :  sprintf(_("%sEbenen%s durchsuchen"), "<a href=\"$PHP_SELF?view=search&mode=browse\">", "</a>")),
								array("icon" => "pictures/blank.gif",
									"text"  => "<br /><a href=\"$PHP_SELF?view=search&reset=TRUE\">".makeButton("neuesuche")."</a>"))));
		$infopic = "pictures/rooms.jpg";
	break;
	}
?>