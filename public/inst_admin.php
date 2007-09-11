<?php
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
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("admin");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Verwaltung der Mitarbeiter");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();

//get ID from a open Institut
if ($SessSemName[1])
	$inst_id = $SessSemName[1];

	//Change header_line if open object
$header_line = getHeaderLine($inst_id);
if ($header_line)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;

require_once('lib/msg.inc.php'); //Ausgaberoutinen an den User
require_once('config.inc.php'); //Grunddaten laden
require_once('lib/visual.inc.php'); //htmlReady
require_once ('lib/statusgruppe.inc.php');	//Funktionen der Statusgruppen
require_once ("lib/classes/DataFieldEntry.class.php");
require_once ('lib/log_events.inc.php');	// Logging

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;


function perm_select($name,$global_perm,$default) {
	$possible_perms=array("user","autor","tutor","dozent");
	$counter=0;
	echo "<select name=\"$name\">";
	if ($global_perm == "admin")
		echo "<option selected>admin</option>";  // einmal admin, immer admin...
	else {
		while ($counter <= 4 ) {
			echo "<option";
			if ($default==$possible_perms[$counter])
				echo" selected";
			echo ">$possible_perms[$counter]</option>";
			if ($possible_perms[$counter]==$global_perm)
				break;
			$counter++;
		}
	}
	echo "</select>";
	return;
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

	if ( is_array($_POST) && list($key, $val) = each($_POST)) {
    if ($perms!="") { //hoffentlich auch was Sinnvolles?
			$db->query("SELECT " . $_fullname_sql['full'] . " AS fullname, perms FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id = '$u_id'");
			while ($db->next_record()) {
				$scherge=$db->f("perms");
				$Fullname = htmlReady($db->f("fullname"));
			}

			// Hier darf fast keiner was:

			if (isset($u_kill_x)) {
				if (!($perm->have_perm("root") || (!$SessSemName["is_fak"] && $perm->have_studip_perm("admin",$SessSemName["fak"]))) && $scherge=='admin')
					my_error("<b>" . _("Sie haben keine Berechtigung Administrierende dieser Einrichtung zu l&ouml;schen.") . "</b>");
				else {
					$db2->query("DELETE from user_inst WHERE Institut_id = '$ins_id' AND user_id = '$u_id'");
					my_msg ("<b>" . sprintf(_("%s wurde aus der Einrichtung ausgetragen."), $Fullname) . "</b>");
					log_event("INST_USER_DEL",$ins_id,$u_id); // logging
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
				foreach ((array)$entries as $rangeID=>$entry) {
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
						if ($scherge!=$perms) { // log status change
							log_event("INST_USER_STATUS",$ins_id,$u_id,"$scherge -> $perms");
						}
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
					if (is_array($group_id)) {
						foreach ($group_id as $groupID)
							setOptionsOfStGroup($groupID, $u_id, ($visible[$groupID] == '0') ? '0' : '1');
					}
					if (is_array($invalidEntries))
						my_error('<b>Fehlerhafte Eingaben (s.u.)');
				}
			}
			$inst_id=$ins_id;
		}
	} // Ende HTTP-POST-VARS

	// Jemand soll ans Institut...
	if (isset($berufen_x) && $ins_id != "") {
		if ($u_id == "0") {
			my_error("<b>" . _("Bitte eine Person ausw&auml;hlen!") . "</b>");
		} else {

			$db->query("SELECT *  FROM user_inst WHERE Institut_id = '$ins_id' AND user_id = '$u_id'");
			if (($db->next_record()) && ($db->f("inst_perms") != "user")) {
				// der Admin hat Tomaten auf den Augen, der Mitarbeiter sitzt schon im Institut
				my_error("<b>" . _("Die Person ist bereits in der Einrichtung eingetragen. Bitte verwenden Sie die untere Tabelle, um Rechte etc. zu &auml;ndern!") . "</b>");
			} else {  // mal nach dem globalen Status sehen
				$db3->query("SELECT " . $_fullname_sql['full'] . " AS fullname, perms FROM auth_user_md5 a LEFT JOIN user_info USING(user_id) WHERE a.user_id = '$u_id'");
				$db3->next_record();
				$Fullname = htmlReady($db3->f("fullname"));
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
					if ($db2->affected_rows()) {
						my_msg("<b>" . sprintf(_("%s wurde als \"%s\" in die Einrichtung aufgenommen. Bitte verwenden Sie die untere Tabelle, um Rechte etc. zu &auml;ndern!"), $Fullname, $insert_perms) . "</b>");
						log_event("INST_USER_ADD",$ins_id,$u_id,$insert_perms); // logging
					} else {
						parse_msg ("error§<b>" . sprintf(_("%s konnte nicht in die Einrichtung aufgenommen werden!"), $Fullname) . "§");
					}
				}
			}
		}
		$inst_id=$ins_id;
	}


?>
	<tr>
		<td class="blank" colspan=2>
<?


//Abschnitt zur Auswahl und Suche von neuen Personen
if ($inst_id != "" && $inst_id !="0") {

	$inst_name = $SessSemName[0];
	if (isset($search_exp) && strlen($search_exp) > 2) {
		$search_exp = trim($search_exp);
		// Der Admin will neue Sklaven ins Institut berufen...
		$db->query ("SELECT DISTINCT auth_user_md5.user_id, " . $_fullname_sql['full_rev'] . " AS fullname, username, perms  FROM auth_user_md5 LEFT JOIN user_info USING(user_id)LEFT JOIN user_inst ON user_inst.user_id=auth_user_md5.user_id AND Institut_id = '$inst_id' WHERE perms !='root' AND (user_inst.inst_perms = 'user' OR user_inst.inst_perms IS NULL) AND (Vorname LIKE '%$search_exp%' OR Nachname LIKE '%$search_exp%' OR username LIKE '%$search_exp%') ORDER BY Nachname ");
		printf("<blockquote>" . _("Auf dieser Seite k&ouml;nnen Sie Personen der Einrichtung %s zuordnen, Daten ver&auml;ndern und Berechtigungen vergeben."), "<b>" . htmlReady($inst_name) . "</b>");
		echo "<br /><br /></blockquote>";
		?>
		<table width="100%" border="0" bgcolor="#C0C0C0" bordercolor="#FFFFFF" cellpadding="2" cellspacing="0">
			<form action="<? echo $PHP_SELF, "?inst_id=", $inst_id ?>" method="POST">
			<tr>
				<td class="blank" colspan=2>
				<blockquote>
					<table width="50%" border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td class="steel1">
						<font size=-1><b><?=_("neue Person der Einrichtung zuordnen")?></b><br>
						<? printf(_("es wurden %s Personen gefunden") . "<br>", $db->num_rows());
						if ($db->num_rows()) {
						echo _("bitte w&auml;hlen Sie die zu berufende Person aus der Liste aus.");
						?></font>
						</td>
					</tr>
					<tr>
						<td class="steel1"><select name="u_id" size="1">
						<?
						//Alle User auswaehlen, auf die der Suchausdruck passt und die im Institut nicht schon was sind. Selected werden hierdurch
//						printf ("<option value=\"0\">-- bitte ausw&auml;hlen --\n");
						while ($db->next_record())
							printf ("<option value=\"%s\">%s (%s) - %s\n", $db->f("user_id"), htmlReady($db->f("fullname")), $db->f("username"), $db->f("perms"));
							?>
							</select>&nbsp;
						<input type="hidden" name="ins_id" value="<?echo $inst_id;?>"><br />
						<input type="IMAGE" name="berufen" <?=makeButton("hinzufuegen", "src")?> border=0 value="<?=_("berufen")?>">
					<? } ?>
						<input type="IMAGE" name="reset" <?=makeButton("neuesuche", "src")?> border=0 value="<?=_("Neue Suche")?>">
						</td>
					</tr>
					</table>
				</blockquote>
				</td>
			</tr>
			</form>
		</table>
		<br>
		<? // Ende der Berufung

	} elseif (!isset($set)) {

		// Der Admin will neue Sklaven ins Institut berufen... aber erst mal suchen
		printf("<blockquote>" . _("Auf dieser Seite k&ouml;nnen Sie Personen der Einrichtung %s zuordnen, Daten ver&auml;ndern und Berechtigungen vergeben."), "<b>" . htmlReady($inst_name) . "</b>");
		echo "<br />" . _("Um weitere Personen als Mitarbeiter hinzuzuf&uuml;gen, benutzen Sie die Suche.");
		echo "<br /><br /></blockquote>";
		?>
			<table width="100%" border="0" cellpadding="2" cellspacing="0">
			<form action="<? echo $PHP_SELF ?>" method="POST">
			<tr>
				<td class="blank" colspan=2>
				<blockquote>
					<table width="50%" border="0" cellpadding="2" cellspacing="0">
					<tr>
						<td class="steel1">
						<font size=-1><b><?=_("neue Person der Einrichtung zuordnen")?></b><br>
						<?=_("bitte geben Sie Vornamen, Nachnamen oder den Usernamen ein:")?><br></font>
						</td>
					</tr>
					<tr>
						<td class="steel1"><input type="TEXT" size=20 maxlength=255 name="search_exp"><br />
						<input type="IMAGE" name="search_user" <?=makeButton("suchestarten", "src")?> border=0 value="<?=_("Suche starten")?>">
						&nbsp;<input type="hidden" name="inst_id" value="<?echo $inst_id;?>">
						</td>
					</tr>
					</table>
				</blockquote>
				</td>
			</tr>
			</form>
		</table>
		<br>
		<?
		}

	//nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
	if (!isset($sortby) || $sortby=="")
		$sortby = "Nachname";

	//entweder wir gehoeren auch zum Institut oder sind global root und es ist ein Institut ausgewählt
	if ($perm->have_studip_perm("admin",$inst_id) && !isset($set)) {
		$query = "SELECT user_inst.*, " . $_fullname_sql['full_rev'] . " AS fullname,Email,username FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE Institut_id ='$inst_id' AND inst_perms !='user' ORDER BY $sortby";
		$db->query($query);

		//Ausgabe der Tabellenueberschrift
		print ("<tr><td class=\"blank\" colspan=2><blockquote>");
		print ("<b>" . _("Bereits der Einrichtung zugeordnet:") . "</b><br><br />");
		print ("<table width=\"90%\" border=0 cellspacing=0 cellpadding=2>");
		print ("<tr>");

		if ($db->num_rows() > 0) {
			// wir haben ein Ergebnis
			echo "<th width=\"30%\"><a href=\"inst_admin.php?sortby=Nachname&inst_id=$inst_id\">" . _("Name") . "</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=inst_perms&inst_id=$inst_id\">" . _("Status") . "</a></th>";
			echo "<th width=\"15%\">Gruppe / Funktion</th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=raum&inst_id=$inst_id\">" . _("Raum Nr.") . "</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=sprechzeiten&inst_id=$inst_id\">" . _("Sprechzeit") . "</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=Telefon&inst_id=$inst_id\">" . _("Telefon") . "</a></th>";
			echo "<th width=\"10%\"><a href=\"inst_admin.php?sortby=Fax&inst_id=$inst_id\">" . _("Fax") . "</a></th>";
			echo "</tr>";

			//anfuegen der daten an tabelle in schleife...

	  	while ($db->next_record()) {
				$user_id = $db->f("user_id");
				$mail_list[] = $db->f("Email");
				$cssSw->switchClass();
				echo "<tr valign=middle align=left>";

				if ((!$SessSemName["is_fak"] && $perm->have_studip_perm("admin",$SessSemName["fak"])) || $perm->have_perm("root") || $db->f("inst_perms") != "admin" || $db->f("username") == $auth->auth["uname"])
					printf ("<td class=\"%s\"><a href=\"%s?details=%s&inst_id=%s\">%s</a></td>", $cssSw->getClass(), $PHP_SELF, $db->f("username"), $db->f("Institut_id"), htmlReady($db->f("fullname")));
				else
					printf ("<td class=\"%s\">&nbsp;%s</td>", $cssSw->getClass(), htmlReady($db->f("fullname")));	 ?>

				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo $db->f("inst_perms"); ?></td>
				<td class="<? echo $cssSw->getClass() ?>"  align="left"><?

				if ($gruppen = GetStatusgruppen($inst_id, $db->f("user_id"))){
					echo htmlReady(join(", ", array_values($gruppen)));
				} else
					echo "&nbsp;";
				?>


				</td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("raum")); ?></td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("sprechzeiten")); ?></td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("Telefon")); ?></td>
				<td class="<? echo $cssSw->getClass() ?>" >&nbsp;<?php echo htmlReady($db->f("Fax")); ?></td>
				</tr>
				<?php
//	endif;
				print ("</tr>");
			}

			//Link fuer tolle Rundmailfunktion wird hier gebastelt

			echo "</table><br><b>" . _("Rundmail an alle MitarbeiterInnen verschicken") . "</b><br><br>&nbsp;";
			printf(_("Bitte hier %sklicken%s"), "<a href=\"mailto:" . join(",",$mail_list) . "?subject=" . urlencode(_("MitarbeiterInnen-Rundmail")) .  "\">", "</a>");
			echo "<br /><br /></blockquote>";
			echo "<br><blockquote><b>" . _("Stud.IP Nachricht an alle MitarbeiterInnen verschicken") . "</b><br><br>&nbsp;";
			printf(_("Bitte hier %sklicken%s"), "<a href=\"sms_send.php?inst_id=$inst_id&emailrequest=1&subject=".urlencode(_("MitarbeiterInnen-Rundmail:") . ' ' . $inst_name)."\">", "</a>");
			echo "<br /><br /></blockquote></td></tr>";
			print("</table>");
		} else { // wir haben kein Ergebnis
			print("</table>" . _("Es wurde niemand gefunden! Bevor Sie die MitarbeiterInnenliste dieser Einrichtung bearbeiten k&ouml;nnen, m&uuml;ssen Sie der Einrichtung zuerst MitarbeiterInnen zuordnen.") . "<br /><br />");
		}
	}
}
}

//zeigen wir eine einzelne Person an?

if (isset($details)) {
	$db->query("SELECT  " . $_fullname_sql['full'] . " AS fullname,user_inst.*,auth_user_md5.*, Institute.Name FROM auth_user_md5 LEFT JOIN user_info USING (user_id) LEFT JOIN user_inst USING (user_id) LEFT JOIN Institute USING (Institut_id) WHERE username = '$details' AND user_inst.Institut_id = '$inst_id'");
	while ($db->next_record()) {
		?>
		<tr>
		<td class="blank" colspan=2>
		<table border=0 align="center" cellspacing=0 cellpadding=2>
			<form method="POST" name="edit" action="inst_admin.php?details=<?=$details?>&inst_id=<?=$inst_id?>&set=1">
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" height="30"><b>&nbsp;<?=_("Einrichtung:")?></b></td>
				<td class="<? echo $cssSw->getClass() ?>" ><?php  echo htmlReady($db->f("Name")) ?></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" height="30"><b>&nbsp;<?=_("Name:")?></b></td>
				<td class="<? echo $cssSw->getClass() ?>" ><?php  echo htmlReady($db->f("fullname")) ?></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;<?=_("Status in der Einrichtung:")?>&nbsp;</b></td>
				<td class="<? echo $cssSw->getClass() ?>" >
				<?
				perm_select("perms",$db->f("perms"),$db->f("inst_perms"));
				?>
				</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;<?=_("Gruppe/Funktion in der Einrichtung:")?>&nbsp;</b></td>
				<td class="<? echo $cssSw->getClass() ?>" >
			<?
			$user_id = $db->f("user_id")	;

			if ($gruppen = GetStatusgruppen($inst_id, $user_id)) {
					echo "<a href=\"admin_statusgruppe.php?list=TRUE&view=statusgruppe_inst\">";
					echo htmlReady(join(", ", array_values($gruppen)));
					echo "</a>";
			} else
					echo "<a href=\"admin_statusgruppe.php?list=TRUE&view=statusgruppe_inst\">" . _("bisher keiner zugeordnet") . "</a>";
			?>
			</td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;<?=_("Raum:")?></b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="raum" size=24 maxlength=31 value="<?php echo htmlReady($db->f("raum")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;<?=_("Sprechstunde:")?></b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="sprechzeiten" size=24 maxlength=63 value="<?php echo htmlReady($db->f("sprechzeiten")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;<?=_("Telefon:")?></b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="Telefon" size=24 maxlength=31 value="<?php echo htmlReady($db->f("Telefon")) ?>"></td>
			</tr>
			<tr <?$cssSw->switchClass() ?> >
				<td class="<? echo $cssSw->getClass() ?>" ><b>&nbsp;<?=_("Fax:")?></b></td>
			  	<td class="<? echo $cssSw->getClass() ?>" ><input type="text" name="Fax" size=24 maxlength=31 value="<?php echo htmlReady($db->f("Fax")) ?>"></td>
			</tr>
			<?
			$entries = DataFieldEntry::getDataFieldEntries(array($user_id, $inst_id));	// Default-Daten der Einrichtung
			foreach ($entries as $id=>$entry) {
				$cssSw->switchClass();
				$color = 'black';
				if (isset($invalidEntries[$id])) {
					$color = 'red';
					$entry = $invalidEntries[$id];  // keep wrong entry to show it in corresponding form field
				}
				echo '<tr><td class="' . $cssSw->getClass() . '" align="left"><b>';
				echo "<font color='$color'>&nbsp;" . $entry->getName() . "</font></b></td>";
				echo '<td colspan="2" class="' . $cssSw->getClass() . '">' . $entry->getHTML('datafield_content[]', $entry->structure->getID());
				echo '<input type="HIDDEN" name="datafield_id[]" value="'.$entry->structure->getID().'">';
				echo '<input type="HIDDEN" name="datafield_type[]" value="'.$entry->getType().'">';
				echo '<input type="HIDDEN" name="datafield_sec_range_id[]" value="'.$inst_id.'">';
				echo '</td></tr>';
			}

			// Rollendaten anzeigen
			if ($gruppen) {
				global $auth;
				$groupOptions = getOptionsOfStGroups($user_id);
				$perms = $auth->auth['perm'];
				foreach ($gruppen as $groupID=>$group) {
					echo '<tr><td align="left" colspan="2"><br>';
					echo '<font size="+1"><b>' . _('Funktion:') . " " . $group . '</b></font>';
//							echo '<td colspan="2" valign="middle">';
					echo '<font size="-1"><br>' . _('Daten der Einrichtung') .' </font>';
					$button = makeButton('uebernehmen' . ($groupOptions[$groupID]['inherit'] ? '2' : ''), 'src');
					if ($perms == 'root' || $perms == 'admin')
						printf('<input type="image" name="inherit[%s]" value="1" align="center" %s>', $groupID, $button);
					else
						print("<img align='center' $button>");
					echo '<font size="-1"> ' . _('oder') . ' </font>';
					$button = makeButton('abweichend' . ($groupOptions[$groupID]['inherit'] ? '' : '2'), 'src');
					if ($perms == 'root' || $perms == 'admin')
						printf('<input type="image" name="inherit[%s]" value="0" align="center" %s>', $groupID, $button);
					else
						print("<img align='center' $button>");
					echo '<font size="-1"> ' . _('eingeben') . ', ' . _('diese Funktion ausblenden:') . '</font>';
					printf('<input type="checkbox" name="visible[%s]" %s value="0">', $groupID, !$groupOptions[$groupID]['visible'] ? 'checked="checked"' : '');
					echo "<input type=\"hidden\" name=\"group_id[]\" value=\"$groupID\">";
					echo "</td></tr>\n";
					$cssSw->resetClass();
					if (!$groupOptions[$groupID]['inherit']) {
						$entries = DataFieldEntry::getDataFieldEntries(array($user_id, $groupID));
						foreach ($entries as $id=>$entry) {
							$cssSw->switchClass();
							$td = '<td class="'.$cssSw->getClass().'" align="left">';
							echo "<tr>$td&nbsp;" . $entry->getName() . ':</td>';
							echo '<td colspan="1" class="' . $cssSw->getClass() . '">&nbsp; ';
							if ($entry->structure->editAllowed($perms)) {
								echo $entry->getHTML('datafield_content[]', $entry->structure->getID());
								echo '<input type="HIDDEN" name="datafield_id[]" value="'.$entry->structure->getID().'">';
								echo '<input type="HIDDEN" name="datafield_type[]" value="'.$entry->getType().'">';
								echo '<input type="HIDDEN" name="datafield_sec_range_id[]" value="'.$groupID.'">';
							}
							else
								echo $entry->getDisplayValue();
							echo '</td></tr>';
						}
					}
				}
			}

			?>
			<tr><td class="blank">&nbsp;</td></tr>
			<?$cssSw->resetClass();?>
			<tr <?$cssSw->switchClass() ?>>
				<td class="<? echo $cssSw->getClass() ?>"  colspan=2 align=center>&nbsp;
				<input type="hidden" name="u_id"  value="<?php $db->p("user_id") ?>">
				<input type="hidden" name="ins_id"  value="<?php $db->p("Institut_id") ?>">
				<input type="IMAGE" name="u_edit" <?=makeButton("uebernehmen", "src")?> border=0 value="<?=_("ver&auml;ndern")?>">&nbsp;
				<?
				if ($db->f("user_id") != $user->id) {
					?>
					<input type="IMAGE" name="u_kill"  <?=makeButton("loeschen", "src")?> border=0  value="<?=_("l&ouml;schen")?>">&nbsp;
					<?
				}?>
				<input type="IMAGE" name="nothing"  <?=makeButton("abbrechen", "src")?> border=0  value="<?=_("abbrechen")?>">
				</td>
			</tr>
			<tr>
				<td class="blank"  colspan=2 class="blank">&nbsp;</td></tr>

			<? // links to everywhere
			print "<tr><td  class=\"steel1\" colspan=2 align=\"center\">";
				printf("&nbsp;" . _("pers&ouml;nliche Homepage") . " <a href=\"about.php?username=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/einst.gif\" border=0 alt=\"Zur pers&ouml;nlichen Homepage des Benutzers\" align=\"texttop\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", $db->f("username"));
				printf("&nbsp;" . _("Nachricht an BenutzerIn") . " <a href=\"sms_send.php?rec_uname=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" alt=\"Nachricht an den Benutzer verschicken\" border=0 align=\"texttop\"></a>", $db->f("username"));
			print "</td></tr>";
			?>
			</form>
		</table>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<?
	} // end while ($db->next_record())
} // end if (isset($details))

?>

</table>
<?php
include ('lib/include/html_end.inc.php');
page_close();
?>
