<?
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
	require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");

	IF (isset($SessSemName) && ($SessSemName[1] != "")) {
		$db=new DB_Seminar;
		
		$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id = '$SessSemName[1]' ");
		if ($db->next_record())
			$entry_level="sem";

		if (!$entry_level) {
			$db->query("SELECT Institut_id FROM Institute WHERE Institut_id = '$SessSemName[1]' ");
			if ($db->next_record())
				$entry_level="inst";
		}

		if (!$entry_level) {
			$db->query("SELECT Fakultaets_id FROM Fakultaeten WHERE Fakultaets_id = '$SessSemName[1]' ");
			if ($db->next_record())
				$entry_level="fak";
		}
		

		if ($entry_level=="sem") {
		$db->query("SELECT Lesezugriff, Schreibzugriff, Passwort FROM seminare WHERE Seminar_id LIKE '$SessSemName[1]'");
		while ($db->next_record()) {
		$SemSecLevelRead=$db->f("Lesezugriff");
		$SemSecLevelWrite=$db->f("Schreibzugriff");
		$SemSecPass=$db->f("Passwort");}
		//root darf alles, keine Abfrage, admins duerfen jetzt auch :(
		if (!$perm->have_perm("admin")){
			$db->query("SELECT status FROM seminar_user WHERE Seminar_id LIKE '$SessSemName[1]' AND user_id LIKE '$user->id'"); //status des Users in diesem Seminar auslesen
			$db->next_record();
			$SemUserStatus=$db->f("status");
			if (!$SemUserStatus) {//wenn kein Status gesetzt ist (=keine Eintrag fuer das Seminar) gucken, ob man trotzdem rein darf (vielleicht als Nobody?)
				$SemUserStatus="nobody";
				if ($SemSecLevelRead>0){
					echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
					echo "<tr><td class=\"topic\" colspan=2>&nbsp;<b>Rechtecheck</b></td></tr>";
					echo "<tr><td class=\"blank\" colspan=2>&nbsp<br></td></tr>";
					parse_msg ("error§Sie haben keine Berechtigung, diese Veranstaltung zu betreten!");
					echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
					if ($send_from_search)
						echo " | <a href=\"sem_portal.php\">zur&uuml;ck zur Suche</a>";
					echo "<br /><br /></td></tr></table>";
					die;
				}
			} else { // wir haben einen Status, dann mal sehen...
				if ($SemUserStatus=="dozent" || $SemUserStatus=="tutor") $rechte=TRUE;
				else $rechte=FALSE;
			}
		}

		elseif ($auth->auth["perm"]=="admin") {    // evtl ein Instadmin ?
			$db->query("SELECT user_inst.Institut_id, seminare.Seminar_id FROM user_inst LEFT JOIN seminare USING (Institut_id) WHERE inst_perms='admin' AND user_id='$user->id' AND seminare.Seminar_id='$SessSemName[1]'");
				if ($db->num_rows()) {
					  // Eintrag gefunden, also ein zum Instadmin gehöriges Seminar
					$rechte=TRUE;
				} else { // kein Eintrag gefunden, wir müssen draußen bleiben...
					echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
					echo "<tr><td class=\"topic\" colspan=2>&nbsp;<b>Rechtecheck</b></td></tr>";
					echo "<tr><td class=\"blank\" colspan=2>&nbsp<br></td></tr>";
					parse_msg ("error§Sie haben keine Berechtigung, diese Veranstaltung zu betreten!");
					echo"<tr><td class=\"blank\" colspan=2><a href=\"index.php\">&nbsp;&nbsp; zur&uuml;ck zur Startseite</a>";
					if ($send_from_search)
						echo" | <a href=\"sem_portal.php\">zur&uuml;ck zur Suche</a><br /><br /></td></tr></table>";
					die;
				}
		}

		elseif ($perm->have_perm("root")) {   // hier kommt root
			$rechte=TRUE;
		}
		}

		elseif($entry_level=="inst") {
			$db->query ("SELECT user_id, inst_perms FROM user_inst WHERE Institut_id = '$SessSemName[1]' AND user_inst.user_id LIKE '$user->id' ");
			
			if ($db->next_record()) {
				$SemUserStatus=$db->f("inst_perms");
				if (($SemUserStatus =="user") || ($SemUserStatus =="autor")) { //eingetragen im Institut aber nur user oder autor = keine Rechte
					$rechte=FALSE;
				}
				else { // ein ordentlicher Mitarbeiter
					$rechte=TRUE;
				}
			}
			elseif ($perm->have_perm("root"))  // hier kommt root
				$rechte=TRUE;
			else
				$SemUserStatus="user"; //wenn gar nichts wird man hier autor
		}

		elseif($entry_level=="fak") { //Fakultaetsbereich, mehr als Platzhalter, daher nur rudimentaer ausgebaut.
			if (!$perm->have_perm("admin")){
			}
			elseif ($auth->auth["perm"]=="admin") {    // evtl ein Instadmin ?
			}
			elseif ($perm->have_perm("root")) {   // hier kommt root
				$rechte=TRUE;
			}
		}
	}
 ?>
