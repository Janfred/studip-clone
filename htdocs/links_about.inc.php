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

//Topkats
$structure["alle"]=array (topKat=>"", name=>"Alle", link=>"about.php?username=$username", active=>FALSE);
$structure["bild"]=array (topKat=>"", name=>"Bild", link=>"edit_about.php?view=Bild&username=$username", active=>FALSE);
$structure["daten"]=array (topKat=>"", name=>"Nutzerdaten", link=>"edit_about.php?view=Daten&username=$username", active=>FALSE);
$structure["karriere"]=array (topKat=>"", name=>"universit&auml;re Daten", link=>"edit_about.php?view=Karriere&username=$username", active=>FALSE);
$structure["lebenslauf"]=array (topKat=>"", name=>"Lebenslauf", link=>"edit_about.php?view=Lebenslauf&username=$username", active=>FALSE);
$structure["sonstiges"]=array (topKat=>"", name=>"Sonstiges", link=>"edit_about.php?view=Sonstiges&username=$username", active=>FALSE);
if (!$perm->have_perm("admin"))
	$structure["mystudip"]=array (topKat=>"", name=>"My Stud.IP", link=>"edit_about.php?view=allgemein&username=$username", active=>FALSE);

//Bottomkats
$structure["_alle"]=array (topKat=>"alle", name=>"Pers&ouml;nliche Homepage", link=>"about.php?username=$username", active=>FALSE);
$structure["_bild"]=array (topKat=>"bild", name=>"Hochladen des pers&ouml;nlichen Bildes", link=>"edit_about.php?view=Bild&username=$username", active=>FALSE);
$structure["_daten"]=array (topKat=>"daten", name=>"Nutzerdaten bearbeiten", link=>"edit_about.php?view=Daten&username=$username", active=>FALSE);
$structure["_karriere"]=array (topKat=>"karriere", name=>"universit&auml;re Daten", link=>"edit_about.php?view=Karriere&username=$username", active=>FALSE);
if (!$perm->have_perm ("dozent")) {
	$structure["studiengaenge"]=array (topKat=>"karriere", name=>"Zuordnung zu Studieng&auml;ngen", link=>"edit_about.php?view=Karriere&username=$username#studiengaenge", active=>FALSE);
	$structure["einrichtungen"]=array (topKat=>"karriere", name=>"Zuordnung zu Einrichtungen", link=>"edit_about.php?view=Karriere&username=$username#einrichtungen", active=>FALSE);
}
$structure["_lebenslauf"]=array (topKat=>"lebenslauf", name=>"Lebenslauf", link=>"edit_about.php?view=Lebenslauf&username=$username", active=>FALSE);
if ($perm->have_perm ("dozent")) {
	$structure["schwerpunkte"]=array (topKat=>"lebenslauf", name=>"Schwerpunkte", link=>"edit_about.php?view=Lebenslauf&username=$username#schwerpunkte", active=>FALSE);
	$structure["publikationen"]=array (topKat=>"lebenslauf", name=>"Publikationen", link=>"edit_about.php?view=Lebenslauf&username=$username#publikationen", active=>FALSE);
}
$structure["_sonstiges"]=array (topKat=>"sonstiges", name=>"eigene Kategorien bearbeiten", link=>"edit_about.php?view=Sonstiges&username=$username", active=>FALSE);
$structure["allgemein"]=array (topKat=>"mystudip", name=>"Allgemeines", link=>"edit_about.php?view=allgemein&username=$username", active=>FALSE);
$structure["forum"]=array (topKat=>"mystudip", name=>"Forum", link=>"edit_about.php?view=Forum&username=$username", active=>FALSE);
$structure["calendar"]=array (topKat=>"mystudip", name=>"Terminkalender", link=>"edit_about.php?view=calendar&username=$username", active=>FALSE);
$structure["stundenplan"]=array (topKat=>"mystudip", name=>"Stundenplan", link=>"edit_about.php?view=Stundenplan&username=$username", active=>FALSE);
$structure["messaging"]=array (topKat=>"mystudip", name=>"Messaging", link=>"edit_about.php?view=Messaging&username=$username", active=>FALSE);
$structure["login"]=array (topKat=>"mystudip", name=>"Login", link=>"edit_about.php?view=Login&username=$username", active=>FALSE);


//View festlegen
switch ($i_page) {
	case "about.php" : 
		$reiter_view="alle"; 
	break;
	case "edit_about.php" : 
		switch ($view) {
			case "Bild":
				$reiter_view="bild"; 
			break;
			case "Daten":
				$reiter_view="daten"; 
			break;
			case "Karriere":
				$reiter_view="karriere"; 
			break;
			case "Lebenslauf":
				$reiter_view="lebenslauf"; 
			break;
			case "Sonstiges":
				$reiter_view="sonstiges"; 
			break;
			case "Login":
				$reiter_view="login"; 
			break;
			case "allgemein":
				$reiter_view="allgemein"; 
			break;
			case "Forum":
				$reiter_view="forum"; 
			break;
			case "calendar":
				$reiter_view="calendar"; 
			break;
			case "Stundenplan":
				$reiter_view="stundenplan"; 
			break;
			case "Messaging":
				$reiter_view="messaging"; 
		}
	break;
	default :
		$reiter_view="alle";
	break;
}

$reiter->create($structure, $reiter_view, $alt, $js);
?>
