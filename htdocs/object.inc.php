<?
/*
object.inc.php - Verwaltung des Objecttrackings
Copyright (C) 2003 Ralf Stockmann <rstockm@gwdg.de>

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

function object_add_view ($object_id) {
	$now = time();
	$db=new DB_Seminar;
	$db->query("SELECT * FROM object_views WHERE object_id = '$object_id'");
	if ($db->next_record()) { // wurde schon mal angeschaut, also hochzählen
		$views = $db->f("views")+1;
		$query = "UPDATE object_views SET chdate='$now', views='$views' WHERE object_id='$object_id'";		
	} else { // wird zum ersten mal angesehen, also counter anlegen
		$views = 1;
		$query = "INSERT INTO object_views (object_id,views,chdate) values ('$object_id', '$views', '$now')";	
	}
	$db->query($query);
	return $views;
}

function object_check_user ($object_id, $flag) {
	global $user;
	$db=new DB_Seminar;
	$db->query("SELECT * FROM object_user WHERE object_id = '$object_id' AND user_id = '$user->id' AND flag = '$flag'");
	if ($db->next_record())  // Der Nutzer hat hier einen Eintrag
		$tmp = TRUE;
	else
		$tmp = FALSE;
	return $tmp;
}

function object_add_rate ($object_id, $rate) {
	global $user;
	if (object_check_user($object_id, "rate") == FALSE) {
		$now = time();
		$db=new DB_Seminar;
		$db->query("INSERT INTO object_user (object_id, user_id, flag, mkdate) values ('$object_id', '$user->id', 'rate', '$now')");	
		$db->query("INSERT INTO object_rate (object_id, rate, mkdate) values ('$object_id', '$rate', '$now')");	
		$txt = _("Sie haben das Objekt mit \"$rate\"  bewertet.");
	} else {
		$txt = _("Sie haben dieses Objekt bereits bewertet.");
	}
	return $txt;
}

function object_print_rate ($object_id) {
	$db=new DB_Seminar;
	$db->query("SELECT avg(rate) as mittelwert FROM object_rate WHERE object_id = '$object_id'");
	if ($db->next_record()) {
		$tmp = round($db->f("mittelwert"),1);
		// $tmp = $db->f("mittelwert");
		if ($tmp == 0)
			$tmp = "?";
		}
	return $tmp;
}

function object_print_rates_detail ($object_id) {
	$db=new DB_Seminar;
	for ($i = 1;$i<6;$i++)
		$tmp[$i] = 0;
	$db->query("SELECT DISTINCT count(rate) as count, rate FROM object_rate WHERE object_id = '$object_id' GROUP BY rate");
	while ($db->next_record())
		$tmp[$db->f("rate")] = $db->f("count");
	return $tmp;
}

function object_return_views ($object_id) {
	$db=new DB_Seminar;
	$db->query("SELECT views FROM object_views WHERE object_id = '$object_id'");
	if ($db->next_record())
		$views = $db->f("views");
	else
		$views = 0;
	return $views;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>