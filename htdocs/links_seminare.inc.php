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
$structure["meine_veranstaltungen"]=array (topKat=>"", name=>_("Meine Veranstaltungen"), link=>"meine_seminare.php", active=>FALSE);
if (!$perm->have_perm("admin"))
	$structure["veranstaltungen_suche"]=array (topKat=>"", name=>_("Veranstaltungen suchen / hinzuf&uuml;gen"), link=>"sem_portal.php?view=Alle", active=>FALSE);
else
	$structure["veranstaltungen_suche"]=array (topKat=>"", name=>_("Veranstaltungen suchen"), link=>"sem_portal.php?view=Alle", active=>FALSE);

$structure["meine_einrichtungen"]=array (topKat=>"", name=>_("Meine Einrichtungen"), link=>"meine_einrichtungen.php", active=>FALSE);

//Bottomkats
$structure["_meine_veranstaltungen"]=array (topKat=>"meine_veranstaltungen", name=>_("&Uuml;bersicht"), link=>"meine_seminare.php", active=>FALSE);
if (!$perm->have_perm("admin"))
	$structure["meine_veranstaltungen_extendet"]=array (topKat=>"meine_veranstaltungen", name=>_("erweiterte &Uuml;bersicht"), link=>"meine_seminare.php?view=ext", active=>FALSE);
if ($perm->have_perm("admin"))
	$structure["veranstaltungs_timetable"]=array (topKat=>"meine_veranstaltungen", name=>_("Veranstaltungs Timetable"), link=>"mein_stundenplan.php", active=>FALSE);
//
$structure["Alle"]=array (topKat=>"veranstaltungen_suche", name=>_("Alle"), link=>"sem_portal.php?view=Alle", active=>FALSE);
foreach ($SEM_CLASS as $key=>$val)  {
	$structure["class_".$key]=array (topKat=>"veranstaltungen_suche", name=>$val["name"], link=>"sem_portal.php?view=$key&reset_all=TRUE&cmd=qs", active=>FALSE);
}
//
$structure["_meine_einrichtungen"]=array (topKat=>"meine_einrichtungen", name=>_("&Uuml;bersicht"), link=>"meine_einrichtungen.php", active=>FALSE);
$structure["meine_einrichtungen_extendet"]=array (topKat=>"meine_einrichtungen", name=>_("erweiterte &Uuml;bersicht"), link=>"meine_einrichtungen.php?view=ext", active=>FALSE);

if ($perm->have_perm("admin")) {
	$structure["einrichtung_admin"]=array (topKat=>"meine_einrichtungen", name=>_("Einrichtungen verwalten"), link=>"admin_institut.php?list=TRUE", active=>FALSE);
}

//View festlegen
switch ($i_page) {
	case "meine_seminare.php" : 
		if ($view=="ext") 
			$reiter_view="meine_veranstaltungen_extendet"; 
		else
			$reiter_view="meine_veranstaltungen"; 
	break;
	case "meine_einrichtungen.php" : 
		if ($view=="ext") 
			$reiter_view="meine_einrichtungen_extendet"; 
		else
			$reiter_view="meine_einrichtungen"; 
	break;
	case "sem_portal.php" : 
		if ($view=="Alle")
			$reiter_view="Alle";
		else
			$reiter_view="class_".$view;
	break;
	case "mein_stundenplan.php" : 
		$reiter_view="veranstaltungs_timetable";
	break;
	default :
		$reiter_view="meine_seminare";
	break;
}

$reiter->create($structure, $reiter_view, $alt, $js);
?>
