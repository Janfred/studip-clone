<?
/*
links_seminare.inc.php - Navigation fuer die Uebersichtsseiten.
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>, Cornelis Kater <ckater@gwdg.de

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

//oberen Reiter 
$structure["resources"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"resources.php?view=resources", active=>FALSE);
$structure["lists"]=array (topKat=>"", name=>"Listen", link=>"resources.php?view=lists", active=>FALSE);
$structure["objects"]=array (topKat=>"", name=>"Objekt", link=>"resources.php?view=objects", active=>FALSE);
$structure["settings"]=array (topKat=>"", name=>"Anpassen", link=>"resources.php?view=settings", active=>FALSE);

//Reiter "Uebersicht"
$structure["_resources"]=array (topKat=>"resources", name=>"Struktur", link=>"resources.php?view=_resources", active=>FALSE);
$structure["search"]=array (topKat=>"resources", name=>"Suchen", link=>"resources.php?view=search&new_search=TRUE", active=>FALSE);
$structure["create_hierarchie"]=array (topKat=>"resources", name=>"Neue Hierarchieebene erzeugen", link=>"resources.php?view=create_hierarchie#a", active=>FALSE);

//Reiter "Listen"
$structure["_lists"]=array (topKat=>"lists", name=>"Listenausgabe", link=>"resources.php?view=_lists", active=>FALSE);
$structure["search_lists"]=array (topKat=>"lists", name=>"Suchen", link=>"resources.php?view=search_lists", active=>FALSE);
$structure["export_lists"]=array (topKat=>"lists", name=>"Listen exportieren", link=>"resources.php?view=export_lists", active=>FALSE);

//Reiter "Objekt"
$structure["view_schedule"]=array (topKat=>"objects", name=>"Belegung ausgeben", link=>"resources.php?view=view_schedule", active=>FALSE);
$structure["edit_object_schedules"]=array (topKat=>"objects", name=>"Belegung bearbeiten", link=>"resources.php?view=edit_object_schedules", active=>FALSE);
$structure["edit_object_properties"]=array (topKat=>"objects", name=>"Eigenschaften bearbeiten", link=>"resources.php?view=edit_object_properties", active=>FALSE);
$structure["edit_object_perms"]=array (topKat=>"objects", name=>"Rechte bearbeiten", link=>"resources.php?view=edit_object_perms", active=>FALSE);

//Reiter "Anpassen"
if (($my_perms->getGlobalPerms() == "admin") || ($perm->have_perm("root"))){ //Grundlegende Einstellungen fuer alle Ressourcen Admins
	$structure["edit_types"]=array (topKat=>"settings", name=>"Typen verwalten", link=>"resources.php?view=edit_types", active=>FALSE);
	$structure["edit_properties"]=array (topKat=>"settings", name=>"Eigenschaften verwalten", link=>"resources.php?view=edit_properties", active=>FALSE);
}
if ($perm->have_perm("root")) { //Rechtezuweisungen nur fuer Root
	$structure["edit_perms"]=array (topKat=>"settings", name=>"globale Rechte verwalten", link=>"resources.php?view=edit_perms", active=>FALSE);
}
$structure["edit_personal_settings"]=array (topKat=>"settings", name=>"pers&ouml;nliche Einstellungen", link=>"resources.php?view=edit_personal_settings", active=>FALSE);
//

//View festlegen
 
$currentObject=new resourceObject($resources_data["structure_open"]);
$currentObjectTitelAdd=$currentObject->getCategory();
if ($currentObjectTitelAdd)
	$currentObjectTitelAdd=": ";
$currentObjectTitelAdd=$currentObject->getName()."&nbsp;<font size=-1>(".$currentObject->getOwnerName().")</font>";

switch ($resources_data["view"]) {
	//Reiter "Uebersicht"
	case "resources":
	case "_resources":
	case "create_hierachie":
	case "search":
		$page_intro="Auf dieser Seite k&ouml;nnen Sie Ressourcen, auf die Sie Zugriff haben, Ebenen zuordnen. ";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	
	//Reiter "Listen"
	case "lists":
	case "_lists":
	case "export_lists":
	case "search_list":
		$page_intro="Hier k&ouml;nnen Sie Listen verwalten. Angezeigt wird jeweils die Liste einer ausgew&auml;hlten Ebene oder alle Ressourcen, auf die sie Zugriff haben.";
		$title="Bearbeiten und Ausgeben von Listen";
	break;

	//Reiter "Objekt"
	case "objects":
	case "edit_object_perms":
	case "edit_object_properties":
	case "edit_object_schedules":
	case "view_schedule":
	case "search_object":
		$page_intro="Hier k&ouml;nnen Sie einzelen Objekte verwalten. Sie k&ouml;nnen Eigenschaften, Berechtigungen und Belegung verwalten.";
		$title="Objekt bearbeiten: ".$currentObjectTitelAdd;
	break;
	case "view_schedule":
		$page_intro="Hier k&ouml;nnen Sie sich den Belegungsplan des Objektes ausgeben lassen. Bitte w&auml;hlen Sie daf&uuml;r den Zeitraum aus.";
		$title="Belegung ausgeben - Objekt: ".$currentObjectTitelAdd;
	break;
	
	//Reiter "Anpassen"	
	case "settings":
	case "edit_types":
	case "edit_properties":
	case "edit_perms":
		$page_intro="Hier k&ouml;nnen Sie grundlegen Einstellungen der Ressourcenverwaltung vornehmen.";
		$title="Einstellungen bearbeiten";
	break;
	
	//default
	default:
		$resources_data["view"]="resources";
		$page_intro="Sie befinden sich in der Ressurcenverwaltung von Stud.IP. Sie k&ouml;nnen hier R&auml;ume, Geb&auml;ude, Ger&auml;te und andere Ressourcen verwalten.";
		$title="&Uuml;bersicht der Ressourcen";
	break;
	}

$reiter->create($structure, $resources_data["view"]);
?>