<?

/*
kalenderFunc.inc.php - 0.7.5-20020309
Grundlegende Terminkalenderfunktionen fuer
persoenlichen Terminkalender in Stud.IP.
Copyright (C) 2001 Peter Thienel <pthien@gmx.de>

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


/****************************************************************/
/*                                                              */
/*        Grundlegende Funktionen für Terminkalender            */
/*                                                              */
/****************************************************************/


// Erzeugt aus einem Unix-Timestamp den Wochentag
// Name entweder in Lang- ("LONG") oder Kurzform ("SHORT")

function wday($tmstamp, $mode = "LONG"){
	$dayname_long = array("Sonntag", "Montag", "Dienstag", "Mittwoch",
												"Donnerstag", "Freitag", "Samstag");
	$dayname_short = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");
												
	$dow = date("w", $tmstamp);
	
	
	if($mode == "SHORT")
		return $dayname_short[$dow];
	return $dayname_long[$dow];
	
	// Das Gebietsschema liefert leider etwas merkwürdige Abkürzungen
	/*
	setlocale("LC_TIME", "");
		if($mode="SHORT")
			return strftime("%a", $tmstamp);
		return strftime("%A", $tmstamp);
	*/
}

// Gibt für einen Unix-Timestamp folgende Werte zurück:
//    "SS" : Timestamp liegt im Sommersemester
//    "WS" : Timestamp liegt im Wintersemester

function sem($tmstamp){
	
	$year = date("Y", $tmstamp);
	if(date("n", $tmstamp) < 4){
		$start = mktime(0,0,0,10,01, $year - 1);
		$ende = mktime(23,59,59,03,31, $year);
		if($tmstamp > $start && $tmstamp < $ende)
			return "WS";
	}
	elseif(date("n", $tmstamp) > 3 && date("n") < 10){
		$start = mktime(0,0,0,04,01, $year);
		$ende = mktime(23,59,59,9,30, $year);
		if($tmstamp > $start && $tmstamp < $ende)
			return "SS";
	}
	elseif(date("n", $tmstamp) > 9){
		$start = mktime(0,0,0,10,01, $year);
		$ende = mktime(23,59,59,03,31, $year + 1);
		if($tmstamp > $start && $tmstamp < $ende)
			return "WS";
	}
}
	

// Erzeugt aus einem Unix-Timestamp den Monatsnamen

function month($tmstamp){
	$monthname = array("", "Januar", "Februar", "M&auml;rz", "April", "Mai",
											"Juni", "Juli", "August", "September", "Oktober",
											"November", "Dezember");
											
	$dom = date("n", $tmstamp);
	return $monthname[$dom];
}

function ldate($tmstamp){
	return wday($tmstamp) . ", " . date("j. ",$tmstamp) . month($tmstamp) . date(" Y",$tmstamp);
}


// Hier jezt die ultimative Feiertags-"Berechnung"
// Zurückgegeben wird ein Array mit Namen des Feiertages ("name") und
// Färbungsgrad ("col", 0 bis 2), denn nicht jeder besondere Tag ist
// färbungswürdig.

function holiday($tmstamp, $mod = ""){
	// erstmal brauchen wir den Ostersonntag für die meisten kirchlichen Feiertage
//	$easterday = easter_date(date("Y", $tmstamp)); // geht leider nicht
	// Berechnung nach Carters Algorithmus (gültig von 1900 - 2099)
	$tmstamp = mktime(0,0,0,date("n",$tmstamp),date("j",$tmstamp),date("Y",$tmstamp));
	$year = date("Y", $tmstamp);
	$b = 225 - 11 * ($year % 19);
	$d = (($b - 21) % 30) + 21;
	if($d > 48)
		$d--;
	$e = ($year + abs($year / 4) + $d + 1) % 7;
	$q = $d + 7 - $e;
	if($q < 32)
		$easterday = date("z",mktime(0,0,0,3,$q,$year)) + 1;
	else
		$easterday = date("z",mktime(0,0,0,4,$q - 31,$year)) + 1;

	// Differenz in Tagen zu Ostertag berechnen
	$doy = date("z", $tmstamp) + 1;
	$dif = $doy - $easterday;
	switch($dif){
		case -48: $name = "Rosenmontag"; $col = 1; break;
		case -47: $name = "Fastnacht"; $col = 1; break;
		case -46: $name = "Ascher-mittwoch"; $col = 1; break;
	//	case -8: $name = "Palmsonntag"; $col = 1; break;
		case  -2: $name = "Karfreitag"; $col = 3; break;
		case   0: $name = "Ostersonntag"; $col = 3; break;
		case   1: $name = "Ostermontag"; $col = 3; break;
		case  39: $name = "Christi Himmelfahrt"; $col = 3; break;
		case  49: $name = "Pfingstsonntag"; $col = 3; break;
		case  50: $name = "Pfingstmontag"; $col = 3; break;
		case  60: $name = "Fronleichnam"; $col = 1; break;
	}
	
	// die unveränderlichen Feiertage
	switch($doy){
		case   1: $name = "Neujahr"; $col = 3; break;
		case   6: $name = "Hl. Drei K&ouml;nige"; $col = 1; break;
	}
	
	// Schaltjahre nicht vergessen
	if(date("L", $tmstamp))
		$doy--;
	switch($doy){
		case  79: $name = "Fr&uuml;lings-anfang"; $col = 1; break;
		case 121: $name = "Maifeiertag"; $col = 3; break;
//		case 125: $name = "Europatag"; $col = 1; break;
		case 172: $name = "Sommeranfang"; $col = 1; break;
		case 266: $name = "Herbstanfang"; $col = 1; break;
		case 276: $name = "Tag der deutschen Einheit"; $col = 3; break;
		case 304: $name = "Reformations-tag"; $col = 2; break;
		case 305: $name = "Allerheiligen"; $col = 1; break;
		case 315: $name = "Martinstag"; $col = 1; break;
		case 340: $name = "Nikolaus"; $col = 1; break;
		case 355: $name = "Winteranfang"; $col = 1; break;
		case 358: $name = "Hl. Abend"; $col = 1; break;
		case 359: $name = "1. Weihnachtstag"; $col = 3; break;
		case 360: $name = "2. Weihnachtstag"; $col = 3; break;
		case 365: $name = "Sylvester"; $col = 1; break;
	}
	
	// Die Sonntagsfeiertage
	if(date("w", $tmstamp) == 0){
		if($doy > 127 && $doy < 135){
			$name = "Muttertag";
			$col = 1;
		}
		else if($doy > 266 && $doy < 274){
			$name = "Erntedank";
			$col = 1;
		}
		else if($doy > 319 && $doy < 327){
			$name = "Volks-trauertag";
			$col = 2;
		}
		else if($doy > 326 && $doy < 334){
			$name = "Toten-sonntag";
			$col = 1;
		}
		else if($doy > 330 && $doy < 338){
			$name = "1. Advent";
			$col = 2;
		}
		else if($doy > 337 && $doy < 345){
			$name = "2. Advent";
			$col = 2;
		}
		else if($doy > 344 && $doy < 352){
			$name = "3. Advent";
			$col = 2;
		}
		else if($doy > 351 && $doy < 359){
			$name = "4. Advent";
			$col = 2;
		}
	}
	
	if($name){
		if($mod == "WITHBR")
			$name = preg_replace("/-/", "-<br>", $name);
		else
			$name = preg_replace("/-/", "", $name);
		return array("name"=>$name, "col"=>$col);
	}
	return FALSE;
}

// überprüft eine Datumsangabe, die in einen Timestamp gewandelt werden soll
function check_date($month, $day, $year){
	if(!preg_match("/^\d{1,2}$/", $day) || !preg_match("/^\d{1,2}$/", $month) ||!preg_match("/^\d{4}$/", $year))
		return FALSE;
	if($year < 1970 || $year > 2036)
		return FALSE;
	if(!checkdate($month, $day, $year))
		return FALSE;
	return TRUE;
}

// ermittelt die Anzahl von Tagen zwischen zwei timestamps (plus Schalttage)
function day_diff($ts_1, $ts_2){
	$days = (int)(abs($ts_1 - $ts_2) / 86400);
	$days_1 = (int)(date("Y", $ts_1) / 4);
	$days_2 = (int)(date("Y", $ts_2) / 4);
	if(date("n", $ts_1) > 3 && date("L", $ts_1))
		$days_1--;
	if(date("n", $ts_2) > 3 && date("L", $ts_2))
		$days_2--;
		
	return $days - abs($days_1 - $days_2);
}
	
	

?>
