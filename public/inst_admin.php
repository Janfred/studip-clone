<?php
# Lifter002: TODO
// vim: noexpandtab
/*
inst_admin.php - Instituts-Mitarbeiter-Verwaltung von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once("lib/msg.inc.php"); //Ausgaberoutinen an den User
require_once("config/config.inc.php"); //Grunddaten laden
require_once("lib/visual.inc.php"); //htmlReady
require_once ("lib/statusgruppe.inc.php");	//Funktionen der Statusgruppen
require_once ("lib/classes/DataFieldEntry.class.php");

// Start of Output
include ("lib/include/html_head.inc.php"); // Output of html head

// if we ar not in admin_view, we get the proper set variable from institut_members.php
if (!isset($admin_view)) {
	$admin_view = true;
}

$css_switcher = new CssClassSwitcher();
echo $css_switcher->GetHoverJSFunction();

// this page is used for administration (if the user has the proper rights)
// or for just displaying the workers and their roles
if ($admin_view) {
	$CURRENT_PAGE = _("Verwaltung der MitarbeiterInnen");

	$perm->check("admin");
	//prebuild navi and the object switcher (important to do already here and to use ob!)
	ob_start();
	include ("lib/include/links_admin.inc.php");  //Linkleiste fuer admins
	$links = ob_get_clean();

} else {
	$CURRENT_PAGE = _("Liste der MitarbeiterInnen");
	$perm->check("autor");

	//prebuild navi and the object switcher (important to do already here and to use ob!)
	ob_start();
	require("lib/include/links_openobject.inc.php");  //Linkleiste fuer Normalos
	$links = ob_get_clean();

}

//get ID from a open Institut. We have to wait until a links_*.inc.php has opened an institute (necessary if we jump directly to this page)
if ($SessSemName[1])
	$inst_id=$SessSemName[1];

if ($admin_view && !$perm->have_studip_perm('admin', $inst_id)) {
	$admin_view = false;
}

if (!$admin_view) {
	checkObject();
	checkObjectModule("personal");
}

//Change header_line if open object
$header_line = getHeaderLine($inst_id);
if ($header_line)
  $CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;


$db = new DB_Seminar();
$db2 = new DB_Seminar();
$db3 = new DB_Seminar();	
$db_institut_members = new DB_Seminar();
	
// Aus institut_members.php kopiert

// initialize session variable and store data given by URL
if(!isset($institut_members_data))
$sess->register("institut_members_data");

if (isset($sortby))
$institut_members_data["sortby"] = $sortby;
if (isset($direction))
$institut_members_data["direction"] = $direction;
if (isset($show))
$institut_members_data["show"] = $show;
if (isset($extend))
$institut_members_data["extend"] = $extend;

// The script remembers the users settings for the hole duration of the session,
// remove the comments if you don't like this behavior.
//if($i_query[0] == "" && sizeof($HTTP_POST_VARS) == 0) {
//  $sess->unregister($institut_members_data);
//  unset($institut_members_data);
//}


// check the given parameters or initialize them
if ($perm->have_studip_perm("admin", $inst_id)) {
  $accepted_columns = array("Nachname", "inst_perms");
} else {
  $accepted_columns = array("Nachname");
}

if(!in_array($institut_members_data["sortby"], $accepted_columns)) {
  $institut_members_data["sortby"] = "Nachname";
}

if($institut_members_data["direction"] == "ASC") {
  $new_direction = "DESC";
} else if($institut_members_data["direction"] == "DESC") {
  $new_direction = "ASC";
} else {
	$institut_members_data["direction"] = "ASC";
	$new_direction = "DESC";
}

$groups = GetAllStatusgruppen($inst_id);
$group_list = GetRoleNames($groups, 0, '', true);

if ($cmd == 'removeFromGroup' && $perm->have_studip_perm('admin', $inst_id)) {
	$db = new DB_Seminar();
	$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id = '$role_id' AND user_id = '".get_userid($username)."'");
}

if ($cmd == 'removeFromInstitute' && $perm->have_studip_perm('admin', $inst_id)) {
	$db = new DB_Seminar();

	$del_user_id = get_userid($username);
	$db->query("DELETE FROM statusgruppe_user WHERE statusgruppe_id IN ('".join("','",array_keys($group_list))."') AND user_id = '$del_user_id'");
	$db->query("DELETE FROM user_inst WHERE user_id = '$del_user_id' AND Institut_id = '$inst_id'");
}


function table_head ($structure, $css_switcher) {
	echo "<colgroup>\n";
	foreach ($structure as $key => $field) {
		if ($key != 'statusgruppe') {
			printf("<col width=\"%s\">", $field["width"]);
		}
	}
	echo "\n</colgroup>\n";
		
	echo "<tr>\n";
	
	$begin = TRUE;
	foreach ($structure as $key => $field) {
		if ($begin) {
			printf ("<td class=\"%s\" width=\"%s\" valign=\"baseline\">",
					$css_switcher->getHeaderClass(), $field["width"]);
			echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"25\" align=\"bottom\">&nbsp;";
			$begin = FALSE;
		}
		else
			printf ("<td class=\"%s\" width=\"%s\" align=\"left\" valign=\"bottom\" ".($key == 'nachricht' ? 'colspan="2"':'').">",
				$css_switcher->getHeaderClass(), $field["width"]);

		if ($field["link"]) {
			printf("<a href=\"%s\">", $field["link"]);
			printf("<font size=\"-1\"><b>%s&nbsp;</b></font>\n", $field["name"]);
			echo "</a>\n";
		}
		else
			printf("<font size=\"-1\"><b>%s&nbsp;</b></font>\n", $field["name"]);
		echo "</td>\n";
	}
	echo "</tr>\n";
}


function table_body ($db, $range_id, $structure, $css_switcher) {
	global $datafields_list, $group_list, $admin_view;

	$cells = sizeof($GLOBALS['dview']);
	
	$css_switcher->enableHover();

	while ($db->next_record()) {

		$pre_cells = 0;

		$default_entries = DataFieldEntry::getDataFieldEntries(array($db->f('user_id'), $range_id));

		if ($db->f('statusgruppe_id')) {
			$role_entries = DataFieldEntry::getDataFieldEntries(array($db->f('user_id'), $db->f('statusgruppe_id')));
		}

		$css_switcher->switchClass();
		printf("<tr%s>\n", $css_switcher->getHover());
		if($db->f("fullname")) {
			printf("<td%s>", $css_switcher->getFullClass());
			echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"2\" height=\"1\">";
			echo '<font size="-1">';
			if ($admin_view) {
				printf("<a href=\"edit_about.php?view=Karriere&open=%s&username=%s#%s\">%s</a>\n",
				$range_id, $db->f("username"), $range_id, htmlReady($db->f("fullname")));
			} else {
				echo '<a href="about.php?username='. $db->f('username') .'">'. $db->f('fullname') .'</a>';
			}
			echo '</font></td>';
		}
		else
			printf("<td%s>&nbsp;</td>", $css_switcher->getFullClass());
	
		if ($structure["status"]) {
			if ($db->f("inst_perms"))
				printf("<td%salign=\"left\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), htmlReady($db->f("inst_perms")));
			else // It is actually impossible !
				printf("<td%salign=\"left\"><font size=\"-1\">&nbsp;</font></td>\n",
					$css_switcher->getFullClass());
			$pre_cells++;
		}
		
		if ($structure["statusgruppe"]) {
			printf("<td%salign=\"left\"><font size=\"-1\">&nbsp;</font></td>\n",
				$css_switcher->getFullClass());
		}
		
		foreach ($datafields_list as $entry) {
			if ($structure[$entry->getId()]) {
				$value = '';
				if ($role_entries[$entry->getId()]) {
					if ($role_entries[$entry->getId()]->getValue() == 'default_value') {
						$value = $default_entries[$entry->getId()]->getValue();
					} else {
						$value = $role_entries[$entry->getId()]->getValue();
					}
				} else {
					if ($default_entries[$entry->getId()]) {
						$value = $default_entries[$entry->getId()]->getValue();
					}
				}

				if ($entry->getId() == '4aac305a882d62d4e56acadd47f262c7') {
					$value = my_substr($value, 0, 50);
				}

				printf("<td%salign=\"left\"><font size=\"-1\">%s</font></td>\n",
					$css_switcher->getFullClass(), $value);
			}
		}

		if ($structure["nachricht"]) {
			printf("<td%salign=\"left\" width=\"1%%\" nowrap>\n",$css_switcher->getFullClass());
			printf("<a href=\"sms_send.php?sms_source_page=inst_admin.php&rec_uname=%s\">",
				$db->f("username"));
			printf("<img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" alt=\"%s\" ", _("Nachricht an User verschicken"));
			printf("title=\"%s\" border=\"0\" valign=\"baseline\"></a>", _("Nachricht an User verschicken"));
			echo '</td>';			
			
			if ($admin_view) {				
				echo '<td '.$css_switcher->getFullClass().' width="1%" nowrap>';
				if ($db->f('statusgruppe_id')) {	// if we are in a view grouping by statusgroups
					echo '&nbsp;<a href="'. $GLOBALS['PHP_SELF'] .'?cmd=removeFromGroup&username='.$db->f('username').'&role_id='. $db->f('statusgruppe_id') .'">';
				} else {
					echo '&nbsp;<a href="'.$GLOBALS['PHP_SELF'].'?cmd=removeFromInstitute&username='.$db->f('username').'">';
				}
				echo '<img src="'.$GLOBALS['ASSETS_URL'].'/images/trash.gif" border="0"></a>&nbsp;';
				echo "\n</td>\n";
			}
		}

		echo "</tr>\n";	

		// Statusgruppen kommen in neue Zeilen
		if ($structure["statusgruppe"]) {
			$statusgruppen = GetStatusgruppenForUser($db->f('user_id'), array_keys($group_list));
			if (is_array($statusgruppen)) {
				foreach ($statusgruppen as $id) {
					$entries = DataFieldEntry::getDataFieldEntries(array($db->f('user_id'), $id));

					$css_switcher->switchClass();
					echo '<tr '.$css_switcher->getHover().'>';
					for ($i = 0; $i <= $pre_cells; $i++) {
						echo '<td '.$css_switcher->getFullClass().'></td>';
					}

					echo '<td '.$css_switcher->getFullClass().'><font size="-1">';

					if ($admin_view) {
						echo '<a href="admin_statusgruppe.php?role_id='.$id.'&cmd=displayRole">'.$group_list[$id].'</a>';
					} else {
						echo $group_list[$id];
					}

					echo '</font></td>';

					if (sizeof($entries) > 0) {
						foreach ($entries as $e_id => $entry) {
							if (in_array($e_id, $GLOBALS['dview']) === TRUE) {
								echo '<td '.$css_switcher->getFullClass().'><font size="-1">';
								if ($entry->getValue() == 'default_value') {
									echo $default_entries[$e_id]->getDisplayValue();
								} else {
									echo $entry->getDisplayValue();
								}
								echo '</font></td>';
							}
						}
					} else {
						for ($i = 0; $i < $cells; $i++) {
							echo '<td '.$css_switcher->getFullClass().'></td>';
						}
					}

					if ($admin_view) {
						echo '<td '.$css_switcher->getFullClass().'>';
						echo '<a href="edit_about.php?view=Karriere&username='.$db->f('username').'&switch='.$id.'"><font size="-1">';
						echo '<img src="'.$GLOBALS['ASSETS_URL'].'/images/edit_transparent.gif" border="0">';
						echo '</font></a></td>';

						echo '<td '.$css_switcher->getFullClass().'>';
						echo '&nbsp;<a href="'. $GLOBALS['PHP_SELF'] .'?cmd=removeFromGroup&username='.$db->f('username').'&role_id='.$id.'">';
						echo '<img src="'.$GLOBALS['ASSETS_URL'].'/images/trash.gif" border="0"></a>&nbsp;';
						echo '</td>';
						echo '</tr>', "\n";
					}
				}
			}
		}
	
	}
}

?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr>
		<td class="blank" colspan=2>&nbsp;
		</td>
	</tr>

<?
if (isset($nothing)) {  // abbrechen im Detailansicht angeklickt? => zu Namenübersicht wechseln
	unset($set);
	unset($details);     
}
	
if (!isset($details) || isset($set)) {
	// haben wir was uebergeben bekommen?

	if (is_array($HTTP_POST_VARS) && list($key, $val) = each($HTTP_POST_VARS)) {
    if ($perms!="") { //hoffentlich auch was Sinnvolles?
			$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = '$u_id'");
			while ($db->next_record()) {
				$scherge=$db->f("perms");
				$Fullname = $db->f("fullname");
			}

			// Hier darf fast keiner was:

			if (isset($u_kill_x)) {
				if (!($perm->have_perm("root") || (!$SessSemName["is_fak"] && $perm->have_studip_perm("admin",$SessSemName["fak"]))) && $scherge=='admin')
					my_error("<b>" . _("Sie haben keine Berechtigung Administrierende dieser Einrichtung zu l&ouml;schen.") . "</b>");
				else {
					$db2->query("DELETE from user_inst WHERE Institut_id = '$ins_id' AND user_id = '$u_id'");
					my_msg ("<b>" . sprintf(_("%s wurde aus der Einrichtung ausgetragen."), $Fullname) . "</b>");
					// remove from all Statusgruppen
					RemovePersonStatusgruppeComplete (get_username($u_id), $ins_id);
					unset($set);
					unset($details);  // wechle zur Namenübersicht
				}
			} 
			if (isset($inherit)) {
				$groupID = array_pop(array_keys($inherit)); // there is only 1 element in the array (and we get its key)
				setOptionsOfStGroup($groupID, $u_id, '', $inherit[$groupID]);
				$instID = GetRangeOfStatusgruppe($groupID);
				$entries = DataFieldEntry::getDataFieldEntriesBySecondRangeID($instID);			
				foreach ($entries as $rangeID=>$entry) {
					$entry->setSecondRangeID($groupID);  // content of institute fields is default for user role fields
					$entry->store();
				}	
			}
			if (isset($u_edit_x) || isset($inherit)) {
				if (!($perm->have_perm("root") || (!$SessSemName["is_fak"] && $perm->have_studip_perm("admin",$SessSemName["fak"]))) && $scherge=='admin' && $u_id != $auth->auth["uid"])
					my_error("<b>" . _("Sie haben keine Berechtigung andere Administrierende dieser Einrichtung zu ver&auml;ndern.") . "</b>");

				else {
					if ($perms=='autor' AND $scherge=='user') {
						my_error("<b>" . _("Sie k&ouml;nnen den User nicht auf AUTORiN hochstufen, da er oder sie im gesamten System nur den Status USER hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte eineN der Systemadministrierenden.") . "</b>");
					}
					elseif ($perms=='tutor' AND ($scherge=='user' OR $scherge=='autor')) {
						my_error("<b>" . sprintf(_("Sie k&ouml;nnen den User nicht auf TUTORiN hochstufen, da er oder sie im gesamten System nur den Status %s hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte eineN der Systemadministrierenden."), $scherge) . "</b>");
					}
					elseif ($perms=='dozent' AND ($scherge=='user' OR $scherge=='autor' OR $scherge=='tutor')) {
						my_error("<b>" . sprintf(_("Sie k&ouml;nnen den User nicht auf DOZENTiN hochstufen, da er oder sie im gesamten System nur den Status %s hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte eineN der Systemadministrierenden."), $scherge) . "</b>");
					}
					elseif ($perms=='admin' AND ($scherge=='user' OR $scherge=='autor' OR $scherge=='tutor' OR $scherge=='dozent')) {
						my_error("<b>" . sprintf(_("Sie k&ouml;nnen den User nicht auf ADMIN hochstufen, da er oder sie im gesamten System nur den Status %s hat. Wenn Sie dennoch an der Bef&ouml;rderung festhalten wollen, kontaktieren Sie bitte eineN der Systemadministrierenden."), $scherge) . "</b>");
					}
					elseif ($perms=='root') {
						my_error("<b>" . _("Sie k&ouml;nnen den User nicht auf ROOT hochstufen, dieser Status ist an einer Einrichtung nicht vorgesehen.") . "</b>");
					}
					elseif ($scherge == 'admin' && $perms != 'admin') {
						my_error("<b>" . _("Globale AdministratorInnen k&ouml;nnen auch an Einrichtung nur den Status \"admin\" haben.") . "</b>");
					}
					else { //na, dann muss es wohl sein (grummel)
						$query = "UPDATE user_inst SET inst_perms='$perms', raum='$raum', Telefon='$Telefon', Fax='$Fax', sprechzeiten='$sprechzeiten' WHERE Institut_id = '$ins_id' AND user_id = '$u_id'";
						$db2->query($query);
						my_msg("<b>" . sprintf(_("Status&auml;nderung f&uuml;r %s durchgef&uuml;hrt."), $Fullname) . "</b>");
					}
				}
				// process user role datafields 
				if (is_array($datafield_id)) {
					$ffCount = 0; // number of processed form fields 
					foreach ($datafield_id as $i=>$id) {
						$struct = new DataFieldStructure(array("datafield_id"=>$id, 'type'=>$datafield_type[$i]));				
						$entry  = DataFieldEntry::createDataFieldEntry($struct, array($u_id, $datafield_sec_range_id[$i]));
						$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
						if ($datafield_type[$i] == 'bool' && $datafield_content[$ffCount] != $id) { // unchecked checkbox?
							$entry->setValue('');
							$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
						}
						elseif ($numFields == 1)
							$entry->setValue($datafield_content[$ffCount]);
						else 
							$entry->setValue(array_slice($datafield_content, $ffCount, $numFields));
						$ffCount += $numFields;						
						
						$entry->structure->load();
						if ($entry->isValid()) 
							$entry->store();
						else 
							$invalidEntries[$struct->getID()] = $entry;
					}
					// change visibility of role data
					foreach ($group_id as $groupID) 
						setOptionsOfStGroup($groupID, $u_id, ($visible[$groupID] == '0') ? '0' : '1');
					if (is_array($invalidEntries))
						my_error('<b>Fehlerhafte Eingaben (s.u.)');
				}	
			}
			$inst_id=$ins_id;
		}
	} // Ende HTTP-POST-VARS

	// Jemand soll ans Institut...
	//if (isset($berufen_x) && $ins_id != "" && ($perm->have_perm("root") || (!$SessSemName["is_fak"] && $perm->have_studip_perm("admin",$SessSemName["fak"])))) {
	if (isset($berufen_x) && $ins_id != "") {
		if ($u_id == "0") {
			my_error("<b>" . _("Bitte eine Person ausw&auml;hlen!") . "</b>");
		} else {		
			$db->query("SELECT *  FROM user_inst WHERE Institut_id = '$ins_id' AND user_id = '$u_id'");
			if (($db->next_record()) && ($db->f("inst_perms") != "user")) {
				// der Admin hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Institut
				my_error("<b>" . _("Die Person ist bereits in der Einrichtung eingetragen. Um Rechte etc. zu &auml;ndern folgen Sie dem Link zu den Nutzerdaten der Person!") . "</b>");
			} else {  // mal nach dem globalen Status sehen
				$db3->query("SELECT " . $_fullname_sql['full'] . " AS fullname, perms FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE a.user_id = '$u_id'");
				$db3->next_record();
				$Fullname = $db3->f("fullname");
				if ($db3->f("perms") == "root")
					my_error("<b>" . _("ROOTs k&ouml;nnen nicht berufen werden!") . "</b>");
				elseif ($db3->f("perms") == "admin") {
					if ($perm->have_perm("root") || (!$SessSemName["is_fak"] && $perm->have_studip_perm("admin",$SessSemName["fak"]))) {
					    // als admin aufnehmen
					    $db2->query("INSERT into user_inst (user_id, Institut_id, inst_perms) values ('$u_id', '$ins_id', 'admin')");
					    my_msg("<b>" . sprintf(_("%s wurde als \"admin\" in die Einrichtung aufgenommen."), $Fullname) . "</b>");
					} else {
					    my_error("<b>" . _("Sie haben keine Berechtigung einen Admin zu berufen!") . "</b>");
					}
				} else {
					$insert_perms = $db3->f("perms");				
					//ok, aber nur hochstufen auf Maximal-Status (hat sich selbst schonmal gemeldet als Student an dem Inst)
					if ($db->f("inst_perms") == "user") {
						$db2->query("UPDATE user_inst SET inst_perms='$insert_perms' WHERE user_id='$u_id' AND Institut_id = '$ins_id' ");
					// ok, neu aufnehmen als das was er global ist
					} else {
						$db2->query("INSERT into user_inst (user_id, Institut_id, inst_perms) values ('$u_id', '$ins_id', '$insert_perms')");
					}
					if ($db2->affected_rows())
						my_msg("<b>" . sprintf(_("%s wurde als \"%s\" in die Einrichtung aufgenommen. Um Rechte etc. zu &auml;ndern folgen Sie dem Link zu den Nutzerdaten der Person!"), $Fullname, $insert_perms) . "</b>");
					else
						parse_msg ("error§<b>" . sprintf(_("%s konnte nicht in die Einrichtung aufgenommen werden!"), $Fullname) . "§");
				}
			}
			checkExternDefaultForUser($u_id);
		}
		$inst_id=$ins_id;
	}
}

?>
	<tr>
		<td class="blank" colspan=2>
<?


//Abschnitt zur Auswahl und Suche von neuen Personen
if ($inst_id != "" && $inst_id !="0") {

	$inst_name = $SessSemName[0];
	$auswahl = $inst_id;

	// Mitglieder zählen und E-Mail-Adressen zusammentstellen
	if ($perm->have_studip_perm("admin", $inst_id)) {
		$query = "SELECT auth_user_md5.Email FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) WHERE
							Institut_id = '$auswahl' AND inst_perms != 'user'";						

		$db_institut_members->query($query);
		$count = $db_institut_members->num_rows();

		$mail_list = array();
		while ($db_institut_members->next_record()) {
			if ($db_institut_members->f('Email')) {
				$mail_list[] = $db_institut_members->f('Email'); 
			}
		}
	}
	else
		$count = CountMembersStatusgruppen($auswahl);

	if ($admin_view) {
		printf("<blockquote>" . _("Auf dieser Seite k&ouml;nnen Sie Personen der Einrichtung %s zuordnen."), "<b>" . htmlReady($inst_name) . "</b>");
		echo "<br />" . _("Um weitere Personen als Mitarbeiter hinzuzuf&uuml;gen, benutzen Sie die Suche.");
		echo "<br /><br /></blockquote>";
		echo '<table width="100%" border="0" cellpadding="2" cellspacing="0">';
		echo '<tr>';
	} else {
		if ($count > 0) {
			printf("<blockquote>%s <b>%s</b></blockquote>", _("Alle MitarbeiterInnen der Einrichtung"), $SessSemName[0]);
		} else {
			printf(_("Der Einrichtung <b>%s</b> wurden noch keine MitarbeiterInnen zugeordnet!"), $SessSemName[0]);
			echo "\n<br /><br /></blockquote>\n";
			echo "</td></tr></table\n";
			echo "</body></html>";
			page_close();
			die;
		}
	}

	if ($admin_view) {
		if (isset($search_exp) && strlen($search_exp) > 2) {
			$search_exp = trim($search_exp);
			// Der Admin will neue Sklaven ins Institut berufen...
			$db->query ("SELECT DISTINCT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] . " AS fullname, username, perms  FROM auth_user_md5 LEFT JOIN user_info USING(user_id)LEFT JOIN user_inst ON user_inst.user_id=auth_user_md5.user_id AND Institut_id = '$inst_id' WHERE perms !='root' AND (user_inst.inst_perms = 'user' OR user_inst.inst_perms IS NULL) AND (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ");		
			?>
			<!-- Suche mit Ergebnissen -->
			<td class="blank" width="50%" valign="top" align="center">
				<form action="<? echo $PHP_SELF, "?inst_id=", $inst_id ?>" method="POST">
					<table width="90%" border="0" cellpadding="2" cellspacing="0">
						<tr>
							<td class="steelkante">
								<font size=-1>
									<b>&nbsp;<?=_("neue Person der Einrichtung zuordnen")?></b>
								</font>
						</tr>
						<tr>
							<td class="steel1">
								<font size=-1>
									<? printf(_("es wurden %s Personen gefunden") . "<br>", $db->num_rows());
									if ($db->num_rows()) {
									echo _("bitte w&auml;hlen Sie die zu berufende Person aus der Liste aus.");?>
								</font>
							</td>
						</tr>
						<tr>
							<td class="steel1"><select name="u_id" size="1">
							<?
							//Alle User auswaehlen, auf die der Suchausdruck passt und die im Institut nicht schon was sind. Selected werden hierdurch 
	//						printf ("<option value=\"0\">-- bitte ausw&auml;hlen --\n");
							while ($db->next_record())
								printf ("<option value=\"%s\">%s (%s) - %s\n", $db->f("user_id"), $db->f("fullname"), $db->f("username"), $db->f("perms"));
								?>
								</select>&nbsp;
							<input type="hidden" name="ins_id" value="<?echo $inst_id;?>"><br />
							<input type="IMAGE" name="berufen" <?=makeButton("hinzufuegen", "src")?> border=0 value="<?=_("berufen")?>">
						<? } ?>
							<input type="IMAGE" name="reset" <?=makeButton("neuesuche", "src")?> border=0 value="<?=_("Neue Suche")?>">
							</td>
						</tr>
					</table>
				</form>
			</td>
			<? // Ende der Berufung

		} elseif (!isset($set)) { ?>
			<!-- Suche -->
			<td class="blank" valign="top" width="50%" align="center">
				<form action="<? echo $PHP_SELF ?>" method="POST">
					<table width="90%" border="0" cellpadding="2" cellspacing="0">
						<tr>
							<td class="steelkante">
								<font size=-1>
									<b>&nbsp;<?=_("neue Person der Einrichtung zuordnen")?></b>
								</font>
							</td>
						</tr>
						<tr>
							<td class="steel1">
								<font size=-1>
									<?=_("bitte geben Sie Vornamen, Nachnamen oder den Usernamen ein:")?><br>
								</font>
							</td>
						</tr>
						<tr>
							<td class="steel1"><input type="TEXT" size=20 maxlength=255 name="search_exp"><br />
								<input type="IMAGE" name="search_user" <?=makeButton("suchestarten", "src")?> border=0 value="<?=_("Suche starten")?>">
								&nbsp;<input type="hidden" name="inst_id" value="<?echo $inst_id;?>">
							</td>
						</tr>
					</table>
				</form>
			</td>
			<?
			}
			?>

			<!-- Mail an alle MitarbeiterInnen -->
			<td class="blank" valign="top" width="50%" align="center">
				<table width="90%" border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td class="steelkante">
							<font size="-1">
								<b>&nbsp;<?=_("Nachricht an alle MitarbeiterInnen verschicken")?></b>
						</td>
					</tr>
					<tr>
						<td class="steel1">
							<font size="-1">
								<br/>
								<?=sprintf(_("Klicken Sie auf %s%s Rundmail an alle MitarbeiterInnen%s, um eine E-Mail an alle MitarbeiterInnen zu verschicken."), "<a href=\"mailto:" . join(",",$mail_list) . "?subject=" . urlencode(_("MitarbeiterInnen-Rundmail")) .  "\">",  '<img src="'.$GLOBALS['ASSETS_URL'].'/images/link_intern.gif" border="0">', "</a>");?>
							</font>
						</td>
					</tr>

					<tr>
						<td class="steel1">
							<font size="-1">
								<br/>
								<?=sprintf(_("Klicken Sie auf %s%s Stud.IP Nachricht an alle MitarbeiterInnen%s, um eine interne Nachricht an alle MitarbeiterInnen zu verschicken."),
									"<a href=\"sms_send.php?inst_id=$inst_id&subject=" . urlencode(_("MitarbeiterInnen-Rundmail - ". $SessSemName[0])) .  "\">", 
									'<img src="'.$GLOBALS['ASSETS_URL'].'/images/link_intern.gif" border="0">',
									"</a>"
								);?>
							</font>
						</td>
					</tr>

				</table>
			</td>
		</tr>
	</table>
	<br>
	<?
	}

// group by function as preset
switch ($institut_members_data["show"]) {
	case 'status' :
		if ($perm->have_perm("admin"))
			break;
	case 'liste' :
		break;
	default :
		$institut_members_data["show"] = "funktion";
}

$datafields_list = DataFieldStructure::getDataFieldStructures("userinstrole");
//$default_entries = DataFieldEntry::getDataFieldEntries(array($userID, $inst_id));

if ($institut_members_data['extend'] == 'yes') {
	$dview = $GLOBALS['INST_ADMIN_DATAFIELDS_VIEW']['extended'];
} else {
	$dview = $GLOBALS['INST_ADMIN_DATAFIELDS_VIEW']['default'];	
}

foreach ($datafields_list as $entry) {
	if (in_array($entry->getId(), $dview) === TRUE) {
		$struct[$entry->getId()] = array (
			'name' => $entry->getName(),
			'width' => '10%'
		);
	}
}

// this array contains the structure of the table for the different views
if ($institut_members_data["extend"] == "yes") {
	switch ($institut_members_data["show"]) {
		case 'liste' :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "10"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%")
											);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "10%")
												);
			}
			break;
		case 'status' :
			$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%")
												);
			break;
		default :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "10")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%")
												);
			}
	} // switch
}
else {
	switch ($institut_members_data["show"]) {
		case 'liste' :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "35%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "10"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "30%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "15%")
												);
			}
			break;
		case 'status' :
			$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "40%"),
												"statusgruppe" => array("name" => _("Funktion"),
														"width" => "20%")
												);
			break;
		default :
			if ($perm->have_perm("admin")) {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "40%"),
												"status" => array("name" => _("Status"),
														"link" => $PHP_SELF . "?sortby=inst_perms&direction=" . $new_direction,
														"width" => "15")
												);
			}
			else {
				$table_structure = array(
												"name" => array("name" => _("Name"),
														"link" => $PHP_SELF . "?sortby=Nachname&direction=" . $new_direction,
														"width" => "40%")
												);
			}
	} // switch
}

if ($admin_view) {
	$nachricht['nachricht'] = array(
		"name" => _("Aktionen") . "&nbsp;",
		"width" => "5%"
	);
}
	
$table_structure = array_merge((array)$table_structure, (array)$struct);
$table_structure = array_merge((array)$table_structure, (array)$nachricht);

$colspan = sizeof($table_structure)+1;

if ($sms_msg) {
	echo "<tr><td class=\"blank\">";
	echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"5\"></td></tr>\n";
	parse_msg($sms_msg, "§", "blank", 1, FALSE);
}
	
echo "<tr><td class=\"blank\">";
echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"1\" height=\"5\"></td></tr>\n";
echo "<tr><td class=\"blank\">\n";

if ($perm->have_perm("admin")) {
	echo '<form action="'.$PHP_SELF.'" method="post">', "\n";
}

echo "<table border=\"0\" width=\"99%\" cellpadding=\"4\" cellspacing=\"0\" align=\"center\">\n";
echo "<tr>\n";
echo "<td class=\"steel1\" width=\"60%\">\n";

// Admins can choose between different grouping functions
if ($perm->have_perm("admin")) {
	printf("<font size=\"-1\"><b>%s&nbsp;</b></font>\n", _("Gruppierung:"));
	printf("<select name=\"show\" style=vertical-align:middle><option %svalue=\"funktion\">%s</option>\n",
		($institut_members_data["show"] == "funktion" ? "selected " : ""), _("Funktion"));
	printf("<option %svalue=\"status\">%s</option>\n",
		($institut_members_data["show"] == "status" ? "selected " : ""), _("Status"));
	printf("<option %svalue=\"liste\">%s</option>\n",
		($institut_members_data["show"] == "liste" ? "selected " : ""), _("keine"));
	echo "</select>\n";
	echo "<input type=\"image\" border=\"0\" " . makeButton("uebernehmen", "src") . "style=vertical-align:middle />";
}
else {
	if ($institut_members_data["show"] == "funktion") {
		echo '&nbsp; &nbsp; &nbsp; <a href="'.$PHP_SELF.'?show=liste">';
		printf("<font size=\"-1\"><b>%s</b></font></a>\n", _("Alphabetische Liste anzeigen"));
	}
	else {
		echo '&nbsp; &nbsp; &nbsp; <a href="'.$PHP_SELF.'?show=funktion">';
		printf("<font size=\"-1\"><b>%s</b></font></a>\n", _("Nach Funktion gruppiert anzeigen"));
	}
}

echo "</td><td class=\"steel1\" width=\"30%\">\n";
printf("<font size=\"-1\">" . _("<b>%s</b> MitarbeiterInnen gefunden") . "</font>", $count);
echo "</td><td class=\"steel1\" width=\"10%\">\n";

if ($institut_members_data["extend"] == "yes") {
	echo '<a href="'.$PHP_SELF.'?extend=no">';
	echo makeButton("normaleansicht", "img");
}
else {
	echo '<a href="'.$PHP_SELF.'?extend=yes">';
	echo makeButton("erweiterteansicht", "img");
}

echo "</a>\n";
echo "</td></tr></table>\n";

if ($perm->have_perm("admin")) {
	echo "\n</form>\n";
}
echo "<table border=\"0\" width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\">\n";

table_head($table_structure, $css_switcher);

// if you have the right question you will get the right answer ;-)
if ($institut_members_data["show"] == "funktion") {
	$all_statusgruppen = $groups;
	if ($all_statusgruppen) {
		function display_recursive($roles, $level = 0, $title = '') {
			global $db_institut_members, $institut_members_data, $auswahl;
			global $_fullname_sql, $css_switcher, $table_structure, $colspan;
			foreach ($roles as $role_id => $role) {
				if ($title == '') {
					$zw_title = $role['role']->getName();
				} else {
					$zw_title = $title .' > '. $role['role']->getName();
				}
				if ($institut_members_data["extend"] == "yes")
					$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.inst_perms, ui.raum,
							ui.sprechzeiten, ui.Telefon, ui.inst_perms, aum.Email, aum.user_id,
							aum.username, info.Home, statusgruppe_id
							FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) LEFT JOIN
							user_info info USING(user_id) LEFT JOIN user_inst ui USING(user_id)
							WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
							AND statusgruppe_id = '%s' ORDER BY %s %s", $auswahl, $role_id,
							$institut_members_data["sortby"], $institut_members_data["direction"]);
				else
					$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, user_inst.raum, user_inst.sprechzeiten, user_inst.Telefon, inst_perms,
							Email, auth_user_md5.user_id, username, statusgruppe_id
							FROM statusgruppe_user  LEFT JOIN	auth_user_md5 USING(user_id)
							LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst USING(user_id)
							WHERE Institut_id = '%s' AND statusgruppe_id = '%s'
							AND inst_perms != 'user' ORDER BY %s %s", $auswahl, $role_id,
							$institut_members_data["sortby"], $institut_members_data["direction"]);

				$db_institut_members->query($query);
				if ($db_institut_members->num_rows() > 0) {
					echo "<tr><td class=\"steelkante\" colspan=\"$colspan\" height=\"20\">";
					echo "<font size=\"-1\"><b>&nbsp;";
					echo htmlReady($zw_title);
					echo "<b></font></td></tr>\n";
					table_body($db_institut_members, $auswahl, $table_structure, $css_switcher);
				}
				if ($role['child']) {
					display_recursive($role['child'], $level + 1, $zw_title);
				}
			}
		}
		display_recursive($all_statusgruppen);
	}
	if ($perm->have_perm("admin")) {
		$assigned = implode("','", GetAllSelected($auswahl));
		$db_residual = new DB_Seminar();
		if ($institut_members_data["extend"] == "yes")
			$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.inst_perms, ui.raum,
								ui.sprechzeiten, ui.Telefon, aum.Email, aum.user_id,
								aum.username
								FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id) LEFT JOIN user_info USING(user_id) 
								WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
								AND ui.user_id NOT IN('%s') ORDER BY %s %s",
								$auswahl, $assigned, $institut_members_data["sortby"],
								$institut_members_data["direction"]);
		else
			$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.inst_perms, ui.raum,
								ui.Telefon, aum.user_id, aum.username
								FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id) LEFT JOIN user_info USING(user_id) 
								WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
								AND ui.user_id NOT IN('%s')ORDER BY %s %s", $auswahl,
								$assigned,
								$institut_members_data["sortby"], $institut_members_data["direction"]);
										
		$db_residual->query($query);
		if ($db_residual->num_rows() > 0) {
			echo "<tr><td class=\"steelkante\" colspan=\"$colspan\" height=\"20\">";
			echo "<font size=\"-1\"><b>&nbsp;";
			echo _("keiner Funktion zugeordnet") . "<b></font></td></tr>\n";
			table_body($db_residual, $auswahl, $table_structure, $css_switcher);
		}
	}
}
elseif ($institut_members_data["show"] == "status") {
	$inst_permissions = array("admin" => _("Admin"), "dozent" => _("DozentIn"), "tutor" => _("TutorIn"),
														"autor" => _("AutorIn"));
	foreach ($inst_permissions as $key => $permission) {
		$query = sprintf("SELECT ". $_fullname_sql['full_rev'] ." AS fullname, ui.raum, ui.sprechzeiten, ui.Telefon,
											inst_perms, Email, auth_user_md5.user_id,
											username FROM user_inst ui LEFT JOIN	auth_user_md5 USING(user_id) 
											LEFT JOIN user_info USING(user_id)
											WHERE ui.Institut_id = '%s' AND inst_perms = '%s'
											ORDER BY %s %s", $auswahl, $key,
											$institut_members_data["sortby"], $institut_members_data["direction"]);
		$db_institut_members->query($query);
		if ($db_institut_members->num_rows() > 0) {
			echo "<tr><td class=\"steelkante\" colspan=\"$colspan\" height=\"20\">";
			echo "<font size=\"-1\"><b>&nbsp;$permission<b></font></td></tr>\n";
			table_body($db_institut_members, $auswahl, $table_structure, $css_switcher);
		}
	}
}
else {
	if ($institut_members_data["extend"] == "yes") {
		if($perm->have_perm("admin"))
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon, ui.inst_perms,
							aum.user_id, info.Home, ". $_fullname_sql['full_rev'] ." AS fullname,aum.Email, aum.username
							FROM user_inst ui LEFT JOIN	auth_user_md5 aum USING(user_id)
							LEFT JOIN user_info info USING(user_id)
							WHERE ui.Institut_id = '%s' AND ui.inst_perms != 'user'
							ORDER BY %s %s", $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
		else
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
							aum.user_id, info.Home, 
							". $_fullname_sql['full_rev'] ." AS fullname, aum.Email, aum.username, Institut_id
							FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id)
							LEFT JOIN user_inst ui USING(user_id) LEFT JOIN auth_user_md5 aum USING(user_id)
							LEFT JOIN user_info info USING(user_id)
							WHERE statusgruppen.statusgruppe_id IN ('%s') AND Institut_id = '%s' GROUP BY user_id
							ORDER BY %s %s",  implode("', '",  getAllStatusgruppenIDS($auswahl)), $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
	}
	else {
		if($perm->have_perm("admin"))
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon, ". $_fullname_sql['full_rev'] ." AS fullname,
							inst_perms, username, ui.user_id
							FROM user_inst ui LEFT JOIN	auth_user_md5 USING(user_id)
							LEFT JOIN user_info USING(user_id)
							WHERE ui.Institut_id = '%s' AND inst_perms != 'user'
							ORDER BY %s %s", $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
		else
			$query = sprintf("SELECT ui.raum, ui.sprechzeiten, ui.Telefon,
							aum.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, aum.username, Institut_id
							FROM statusgruppen LEFT JOIN statusgruppe_user su USING(statusgruppe_id)
							LEFT JOIN user_inst ui USING(user_id) LEFT JOIN auth_user_md5 aum USING(user_id)
							LEFT JOIN user_info USING(user_id)
							WHERE statusgruppen.statusgruppe_id IN ('%s') AND Institut_id = '%s' GROUP BY user_id
							ORDER BY %s %s", implode("', '",  getAllStatusgruppenIDS($auswahl)), $auswahl, $institut_members_data["sortby"],
							$institut_members_data["direction"]);
	}
	$db_institut_members->query($query);

	if ($db_institut_members->num_rows() != 0)
		table_body($db_institut_members, $auswahl, $table_structure, $css_switcher);
}

if (($EXPORT_ENABLE) AND ($db_institut_members->num_rows() > 0) AND ($perm->have_perm("tutor")))
{
	include_once($PATH_EXPORT . "/export_linking_func.inc.php");
	echo "<tr><td colspan=$colspan><br>" . export_form($auswahl, "person", $SessSemName[0]) . "</td></tr>";
}
echo "<tr><td class=\"blank\" colspan=\"$colspan\">&nbsp;</td></tr>\n";
echo "</table></td></tr></table>\n";
echo "</body></html>";

} // Ende Abfrageschleife, ob überhaupt eine Instituts_id gesetzt ist

include('lib/include/html_end.inc.php');
page_close();
