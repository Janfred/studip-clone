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

//Topkats
$structure["meine_veranstaltungen"]=array (topKat=>"", name=>"Meine Veranstaltungen", link=>"meine_seminare.php", active=>FALSE);
$structure["meine_einrichtungen"]=array (topKat=>"", name=>"Meine Einrichtungen", link=>"meine_einrichtungen.php", active=>FALSE);

//Bottomkats
$structure["_meine_veranstaltungen"]=array (topKat=>"meine_veranstaltungen", name=>"&Uuml;bersicht", link=>"meine_seminare.php", active=>FALSE);
$structure["veranstaltung_suche"]=array (topKat=>"meine_veranstaltungen", name=>"Veranstaltungen suchen", link=>"sem_portal.php?view=Alle", active=>FALSE);
if ($perm->have_perm("dozent")) {
	$structure["veranstaltung_admin"]=array (topKat=>"meine_veranstaltungen", name=>"Veranstaltungen verwalten", link=>"adminarea_start.php?list=TRUE", active=>FALSE);
	$structure["veranstaltung_neu"]=array (topKat=>"meine_veranstaltungen", name=>"neue Veranstaltungen anlegen", link=>"admin_seminare_assi.php?new_session=TRUE", active=>FALSE);
}

$structure["_meine_einrichtungen"]=array (topKat=>"meine_einrichtungen", name=>"&Uuml;bersicht", link=>"meine_einrichtungen.php", active=>FALSE);
$structure["einrichtung_suche"]=array (topKat=>"meine_einrichtungen", name=>"Einrichtungen suchen", link=>"institut_browse", active=>FALSE);

if ((!$perm->have_perm("dozent")) && (!$perm->have_perm("admin")) && (!$perm->have_perm("root"))) {
	$structure["einrichtung_personal"]=array (topKat=>"meine_einrichtungen", name=>"Zuordnung zu Einrichtungen", link=>"edit_about.php?view=Karriere#einrichtungen", active=>FALSE);
}

if ($perm->have_perm("admin")) {
	$structure["einrichtung_admin"]=array (topKat=>"meine_einrichtungen", name=>"Einrichtungen verwalten", link=>"admin_institut.php?list=TRUE", active=>FALSE);
}

//View festlegen
switch ($i_page) {
	case "meine_seminare.php" : 
		$reiter_view="meine_veranstaltungen"; 
	break;
	case "meine_einrichtungen.php" : 
		$reiter_view="meine_einrichtungen"; 
	break;
	default :
		$reiter_view="meine_seminare";
	break;
}

$reiter->create($structure, $reiter_view, $alt, $js);
?>