<?
# Lifter002: TODO
/*
pers_browse.inc.php - Universeller Personenbrowser zum Includen, Stud.IP
Copyright (C) 2001 Stefan Suchi <suchi@gmx.de>

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
//$Id$

function perm_select($name,$default) {
	$possible_perms=array(_("alle"),"user","autor","tutor","dozent","admin","root");
	$counter=0;
	echo '<select name="'.$name.'">';
	while ($counter <= 6 ) {
		echo '<option';
		if ($default == $possible_perms[$counter])
			echo ' selected';
		echo '>'.$possible_perms[$counter].'</option>';
		$counter++;
	}
	echo '</select>';
	return;
}

function crit_select($name,$default) {
	$possible_crits=array(">=","=","<",_("nie"));
	$counter=0;
	echo '<select name="'.$name.'">';
	while ($counter <= 3 ) {
		echo "<option";
		if ($default==$possible_crits[$counter])
			echo ' selected';
		echo '>'.$possible_crits[$counter].'</option>';
		$counter++;
	}
	echo "</select>";
	return;
}


// Check auf neue Übergabe und Variablen-Registrierung
if (isset($pers_browse_search)) {
	$pers_browse_old['username'] = $pers_browse_username;
	$pers_browse_old['Vorname'] = $pers_browse_Vorname;
	$pers_browse_old['Email'] = $pers_browse_Email;
	$pers_browse_old['Nachname'] = $pers_browse_Nachname;
	$pers_browse_old['perms'] = $pers_browse_perms;
	$pers_browse_old['crit'] = $pers_browse_crit;
	$pers_browse_old['changed'] = $pers_browse_changed;
	$pers_browse_old['locked'] = $pers_browse_locked;
	$sess->register("pers_browse_old");

	// Parser
	$pers_browse_search_string = "";
	if (isset($pers_browse_username) && $pers_browse_username != "")
		$pers_browse_search_string .= "username LIKE '%" . $pers_browse_username ."%' AND ";
	if (isset($pers_browse_Vorname) && $pers_browse_Vorname != "")
		$pers_browse_search_string .= "Vorname LIKE '%" . $pers_browse_Vorname ."%' AND ";
	if (isset($pers_browse_Email) && $pers_browse_Email != "")
		$pers_browse_search_string .= "Email LIKE '%" . $pers_browse_Email ."%' AND ";
	if (isset($pers_browse_Nachname) && $pers_browse_Nachname != "")
		$pers_browse_search_string .= "Nachname LIKE '%" . $pers_browse_Nachname ."%' AND ";
	if (isset($pers_browse_locked) && $pers_browse_locked != "")
		$pers_browse_search_string .= "locked = 1 AND ";
	if (isset($pers_browse_perms) && $pers_browse_perms != _("alle"))
		$pers_browse_search_string .= "perms = '$pers_browse_perms' AND ";
	if (isset($pers_browse_changed) && $pers_browse_changed != "" && $pers_browse_changed >=0) {
		$searchdate = date("YmdHis",  time()-$pers_browse_changed*3600*24);
		$searchdate2 = date("YmdHis",  time()-($pers_browse_changed+1)*3600*24);
			if ($pers_browse_crit == "<") {
				$searchcrit = ">";
				$pers_browse_search_string .= "changed $searchcrit '$searchdate' AND ";
			}
			if ($pers_browse_crit == ">=") {
				$searchcrit = "<";
				$pers_browse_search_string .= "changed $searchcrit '$searchdate' AND ";
			}
			if ($pers_browse_crit == "=") {
				$pers_browse_search_string .= "changed < '$searchdate' AND changed > '$searchdate2' AND ";
			}
		}
	if (isset($pers_browse_crit) && $pers_browse_crit == _("nie"))
		$pers_browse_search_string .= "changed IS NULL AND ";
	
	if ($pers_browse_search_string != "") {
		$pers_browse_search_string = " WHERE " . $pers_browse_search_string;	
		$pers_browse_search_string = substr($pers_browse_search_string,0,-4);
		if ($pers_browse_crit == _("nie") || ($pers_browse_changed != "" && $pers_browse_changed >=0))
			$pers_browse_search_string .= $GLOBALS['user']->that->get_where_clause($GLOBALS['user']->name);
		$sess->register("pers_browse_search_string");
	} else {
		$sess->unregister("pers_browse_search_string");
		unset($pers_browse_search_string);
		$msg = "error§" . _("Bitte geben Sie einen Suchbegriff ein.") . "§";
	}
}


// Formular zuruecksetzen
if (isset($pers_browse_clear)) {
	$sess->unregister("pers_browse_old");
	unset($pers_browse_old);
	$sess->unregister("pers_browse_search_string");
	unset($pers_browse_search_string);
}


// Suchformular
print "<form action=\"$PHP_SELF\" method=\"post\">\n";
print "<table border=0 align=\"center\" cellspacing=0 cellpadding=2 width = \"80%\">\n";
print "<tr><th colspan=5>" . _("Suchformular") . "</th></tr>";
print "\n<tr><td class=steel1 align=\"right\" width=\"15%\">" . _("Benutzername:") . " </td>";
print "\n<td class=steel1 align=\"left\" width=\"35%\"><input name=\"pers_browse_username\" type=\"text\" value=\"$pers_browse_old[username]\" size=30 maxlength=255></td>\n";
print "\n<td class=steel1 align=\"right\" width=\"15%\">" . _("Vorname:") . " </td>";
print "\n<td class=steel1 colspan=2 align=\"left\" width=\"35%\"><input name=\"pers_browse_Vorname\" type=\"text\" value=\"$pers_browse_old[Vorname]\" size=30 maxlength=255></td></tr>\n";
print "\n<tr><td class=steel1 align=\"right\" width=\"15%\">" . _("E-Mail:") . " </td>";
print "\n<td class=steel1 align=\"left\" width=\"35%\"><input name=\"pers_browse_Email\" type=\"text\" value=\"$pers_browse_old[Email]\" size=30 maxlength=255></td>\n";
print "\n<td class=steel1 align=\"right\" width=\"15%\">" . _("Nachname:") . " </td>";
print "\n<td class=steel1 colspan=2 align=\"left\" width=\"35%\"><input name=\"pers_browse_Nachname\" type=\"text\" value=\"$pers_browse_old[Nachname]\" size=30 maxlength=255></td></tr>\n";
print "\n<tr><td class=steel1 align=\"right\" width=\"15%\">" . _("Status:") . " </td>";
print "\n<td class=steel1 align=\"left\" width=\"35%\">";
perm_select("pers_browse_perms",$pers_browse_old['perms']);
echo "&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" name=\"pers_browse_locked\" value=\"1\" " . ($pers_browse_old['locked'] ? "checked" : "" ) . ">&nbsp;"._("gesperrt");
print "</td>\n";
print "\n<td class=steel1 align=\"right\" width=\"15%\">" . _("inaktiv:") . " </td>";
print "\n<td class=steel1 align=\"left\" width=\"10%\">";
	crit_select("pers_browse_crit",$pers_browse_old['crit']);
print "</td>";
print "\n<td class=steel1 align=\"left\" width=\"25%\"><input name=\"pers_browse_changed\" type=\"text\" value=\"$pers_browse_old[changed]\" size=10 maxlength=50> Tage</td></tr>\n";
print "\n<tr><td class=steel1>&nbsp</td><td class=steel1 align=\"left\"><input type=\"IMAGE\" " . makeButton("suchestarten", "src") .  tooltip(_("Suche starten")) . " border=0><input type=\"HIDDEN\" name=\"pers_browse_search\" value=\"TRUE\"></td>\n";
print "\n<td class=steel1>&nbsp</td><td class=steel1 colspan=2 align=\"left\"><a href=\"$PHP_SELF?pers_browse_clear=TRUE\"" . tooltip(_("Formular zurücksetzen")) . ">" . makeButton("zuruecksetzen", "img") . "</a></td></tr>\n";

print "\n</table></form>\n";

?>
