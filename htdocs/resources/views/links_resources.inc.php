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
$structure["resources"]=array (topKat=>"", name=>"&Uuml;bersicht", link=>"resources.php?view=resources#a", active=>FALSE);
if ($resources_data["list_open"])
	$structure["lists"]=array (topKat=>"", name=>"Liste", link=>"resources.php?view=lists#a", active=>FALSE);
if ($resources_data["actual_object"])
	$structure["objects"]=array (topKat=>"", name=>"Ressource", link=>"resources.php?view=objects", active=>FALSE);
if (($my_perms->getGlobalPerms() == "admin") || ($perm->have_perm("root")))
	$structure["settings"]=array (topKat=>"", name=>"Anpassen", link=>"resources.php?view=settings", active=>FALSE);

//Reiter "Uebersicht"
$structure["_resources"]=array (topKat=>"resources", name=>"Struktur", link=>"resources.php?view=_resources#a", active=>FALSE);
$structure["search"]=array (topKat=>"resources", name=>"Suchen", link=>"resources.php?view=search&new_search=TRUE", active=>FALSE);
$structure["create_hierarchie"]=array (topKat=>"resources", name=>"Neue Hierarchieebene erzeugen", link=>"resources.php?view=create_hierarchie#a", active=>FALSE);

//Reiter "Listen"
if ($resources_data["list_open"]) {
	$structure["_lists"]=array (topKat=>"lists", name=>"Listenausgabe", link=>"resources.php?view=_lists#a", active=>FALSE);
	//$structure["search_lists"]=array (topKat=>"lists", name=>"Suchen", link=>"resources.php?view=search_lists", active=>FALSE);
	//$structure["export_lists"]=array (topKat=>"lists", name=>"Listen exportieren", link=>"resources.php?view=export_lists", active=>FALSE);
}

//Reiter "Objekt"
if ($resources_data["actual_object"]) {
	if ($ActualObjectPerms ->havePerm ("user")) {
		if ($ActualObjectPerms ->havePerm ("admin")) {
			$structure["edit_object_properties"]=array (topKat=>"objects", name=>"Eigenschaften bearbeiten", link=>"resources.php?view=edit_object_properties", active=>FALSE);
			$structure["edit_object_perms"]=array (topKat=>"objects", name=>"Rechte bearbeiten", link=>"resources.php?view=edit_object_perms", active=>FALSE);
		} else {
			$structure["edit_object_properties"]=array (topKat=>"objects", name=>"Details", link=>"resources.php?view=openobject_details", active=>FALSE);
		}
		if (getResourceObjectCategory($resources_data["actual_object"])) {
			$structure["view_schedule"]=array (topKat=>"objects", name=>"Belegungsplan", link=>"resources.php?view=view_schedule", active=>FALSE);
			$structure["edit_object_assign"]=array (topKat=>"objects", name=>"Belegung bearbeiten", link=>"resources.php?view=edit_object_assign", active=>FALSE);
		}
	}
 }

//Reiter "Anpassen"
if (($my_perms->getGlobalPerms() == "admin") || ($perm->have_perm("root"))){ //Grundlegende Einstellungen fuer alle Ressourcen Admins
	$structure["edit_types"]=array (topKat=>"settings", name=>"Typen verwalten", link=>"resources.php?view=edit_types", active=>FALSE);
	$structure["edit_properties"]=array (topKat=>"settings", name=>"Eigenschaften verwalten", link=>"resources.php?view=edit_properties", active=>FALSE);
}
if ($perm->have_perm("root")) { //Rechtezuweisungen nur fuer Root
	$structure["edit_perms"]=array (topKat=>"settings", name=>"globale Rechte verwalten", link=>"resources.php?view=edit_perms", active=>FALSE);
}
//$structure["edit_personal_settings"]=array (topKat=>"settings", name=>"pers&ouml;nliche Einstellungen", link=>"resources.php?view=edit_personal_settings", active=>FALSE);
//

$reiter->create($structure, $resources_data["view"]);

?>