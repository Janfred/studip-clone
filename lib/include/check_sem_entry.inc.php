<?
# Lifter002: TODO
/*
check_sem_entry.inc.php - Script zur Ueberpruefung der Zugangsberechtigung zu einem Seminar,
wird nur augefuert, wenn ein Seminar gewaehlt wurde.
Copyright (C) 2000 André Noack <andre.noack@gmx.net>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

  global $perm, $SessSemName, $send_from_search, $rechte;

	require_once ('lib/msg.inc.php');

	if (isset($SessSemName) && ($SessSemName[1] != "")) {
		$db=new DB_Seminar;

		$entry_level = ($SessSemName["is_fak"]) ? "fak" : $SessSemName["class"];

		if ($entry_level=="sem") {
			$db->query("SELECT Lesezugriff, Schreibzugriff, Passwort FROM seminare WHERE Seminar_id LIKE '$SessSemName[1]'");
			while ($db->next_record()) {
				$SemSecLevelRead=$db->f("Lesezugriff");
				$SemSecLevelWrite=$db->f("Schreibzugriff");
			$SemSecPass=$db->f("Passwort");}
			$SemUserStatus = $perm->get_studip_perm($SessSemName[1]);

			//root darf alles, keine Abfrage, admins duerfen jetzt auch :(
			if (!$SemUserStatus) {//wenn kein Status gesetzt ist (=keine Eintrag fuer das Seminar) gucken, ob man trotzdem rein darf (vielleicht als Nobody?)
				$SemUserStatus="nobody";
				if ($SemSecLevelRead>0){
					echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
					echo "<tr><td class=\"topic\" colspan=2>&nbsp;<b>" . _("&Uuml;berpr&uuml;fung der Rechte") . "</b></td></tr>";
					echo "<tr><td class=\"blank\" colspan=2>&nbsp<br></td></tr>";
					parse_msg ("error§" . _("Sie haben keine Berechtigung diese Veranstaltung zu betreten!"));
					echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; " . _("zur&uuml;ck zur Startseite") . "</a>";
					if ($send_from_search)
					echo " | <a href=\"sem_portal.php\">" . _("zur&uuml;ck zur Suche") . "</a>";
					echo "<br /><br /></td></tr></table>";
					closeObject();
					page_close();
					die;
				}
			} else { // wir haben einen Status, dann mal sehen...
				if ($perm->have_studip_perm("tutor", $SessSemName[1]))
					$rechte=TRUE;
				else
					$rechte=FALSE;
			}
		}

		elseif($entry_level=="inst" || $entry_level=="fak") {
			$SemUserStatus = $perm->get_studip_perm($SessSemName[1]);
			if ($perm->have_studip_perm("tutor", $SessSemName[1]))
				$rechte=TRUE;
			else
				$rechte=FALSE;
		}

	}
 ?>
