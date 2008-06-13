<?php
# Lifter002: TODO
/*
new_user_md5.php - die globale Benutzerverwaltung von Stud.IP.
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA	02111-1307, USA.
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("admin");

include ('lib/seminar_open.php'); 		// initialise Stud.IP-Session
require_once('lib/msg.inc.php'); 		// Funktionen fuer Nachrichtenmeldungen
require_once('config.inc.php'); 		// Wir brauchen den Namen der Uni
require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php');
require_once('lib/classes/UserManagement.class.php');

$cssSw = new cssClassSwitcher;

$CURRENT_PAGE = _("Benutzerverwaltung");

//-- hier muessen Seiten-Initialisierungen passieren --

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');	 //hier wird der "Kopf" nachgeladen
include ('lib/include/links_admin.inc.php');	//Linkleiste fuer admins


// Get a database connection
$db = new DB_Seminar;
$db2 = new DB_Seminar;

// Check if there was a submission
if (check_ticket($_REQUEST['studipticket'])){
	if ($_REQUEST['disable_mail_host_check']) $GLOBALS['MAIL_VALIDATE_BOX'] = false;
	while ( is_array($_POST)
			 && list($key, $val) = each($_POST)) {
		switch ($key) {

		// Create a new user
		case "create_x":

			$UserManagement = new UserManagement;

			if (!$title_front)
				$title_front = $title_front_chooser;
			if (!$title_rear)
				$title_rear = $title_rear_chooser;

			$newuser = array(	'auth_user_md5.username' => stripslashes(trim($username)),
												'auth_user_md5.Vorname' => stripslashes(trim($Vorname)),
												'auth_user_md5.Nachname' => stripslashes(trim($Nachname)),
												'auth_user_md5.Email' => stripslashes(trim($Email)),
												'auth_user_md5.perms' => implode($perms,","),
												'auth_user_md5.visible' => $visible,
												'user_info.title_front' => stripslashes(trim($title_front)),
												'user_info.title_rear' => stripslashes(trim($title_rear)),
												'user_info.geschlecht' => stripslashes(trim($geschlecht)),
											);

			if($UserManagement->createNewUser($newuser)){
				if ($_REQUEST['select_inst_id'] && $perm->have_studip_perm('admin', $_REQUEST['select_inst_id'])){
					$db = new DB_Seminar();
					$db->query(sprintf("SELECT Name, Institut_id FROM Institute WHERE Institut_id='%s'", $_REQUEST['select_inst_id']));
					if($db->next_record()){
						$inst_name = $db->f('Name');
						$db->query(sprintf("INSERT INTO user_inst (user_id,Institut_id,inst_perms) VALUES ('%s','%s','%s')",
						$UserManagement->user_data['auth_user_md5.user_id'], $_REQUEST['select_inst_id'], $UserManagement->user_data['auth_user_md5.perms']));
						if ($db->affected_rows()){
							$UserManagement->msg .= "msg§" . sprintf(_("Benutzer in Einrichtung \"%s\" mit dem Status \"%s\" eingetragen."), htmlReady($inst_name), $UserManagement->user_data['auth_user_md5.perms']) . "§";
						} else {
							$UserManagement->msg .= "error§" . sprintf(_("Benutzer konnte nicht in  Einrichtung \"%s\" eingetragen werden."), htmlReady($inst_name)) . "§";
						}
					}
				}
			}

			break;


		// Change user parameters
		case "u_edit_x":

			$UserManagement = new UserManagement($u_id);

			$newuser = array();
			if (isset($username))
				$newuser['auth_user_md5.username'] = stripslashes(trim($username));
			if (isset($Vorname))
				$newuser['auth_user_md5.Vorname'] = stripslashes(trim($Vorname));
			if (isset($Nachname))
				$newuser['auth_user_md5.Nachname'] = stripslashes(trim($Nachname));
			if (isset($Email))
				$newuser['auth_user_md5.Email'] = stripslashes(trim($Email));
			if (isset($perms))
				$newuser['auth_user_md5.perms'] = implode($perms,",");

			$newuser['auth_user_md5.locked']     = (isset($locked) ? $locked : 0);
			$newuser['auth_user_md5.lock_comment']    = (isset($lock_comment) ? stripslashes(trim($lock_comment)) : "");
			$newuser['auth_user_md5.locked_by'] = ($locked==1 ? $auth->auth["uid"] : "");

			if (isset($visible))
				$newuser['auth_user_md5.visible'] = $visible;
			if (isset($title_front) || isset($title_front_chooser)) {
				if (!$title_front)
					$title_front = $title_front_chooser;
				$newuser['user_info.title_front'] = stripslashes(trim($title_front));
			}
			if (isset($title_rear) || isset($title_rear_chooser)) {
				if (!$title_rear)
					$title_rear = $title_rear_chooser;
				$newuser['user_info.title_rear'] = stripslashes(trim($title_rear));
			}
			if (isset($geschlecht))
				$newuser['user_info.geschlecht'] = stripslashes(trim($geschlecht));

			$UserManagement->changeUser($newuser);

			if (is_array($_POST['datafield_id'])) {
				$ffCount = 0; // number of processed form fields
				$invalidEntries = null;
				foreach ($_POST['datafield_id'] as $i => $field_id) {
					$struct = new DataFieldStructure(array("datafield_id"=>$field_id, 'type'=>$_POST['datafield_type'][$i]));
					$entry  = DataFieldEntry::createDataFieldEntry($struct, $u_id);
					$numFields = $entry->numberOfHTMLFields(); // number of form fields used by this datafield
					if ($_POST['datafield_type'][$i] == 'bool' && $_POST['datafield_content'][$ffCount] != $field_id) { // unchecked checkbox?
						$entry->setValue('');
						$ffCount -= $numFields;  // unchecked checkboxes are not submitted by GET/POST
					}
					elseif ($numFields == 1)
						$entry->setValue($_POST['datafield_content'][$ffCount]);
					else
						$entry->setValue(array_slice($_POST['datafield_content'], $ffCount, $numFields));
					$ffCount += $numFields;
					$entry->structure->load();
					if ($entry->isValid()) {
						$entry->store();
					} else {
						$invalidEntries[$struct->getID()] = $entry;
					}
				}
				if (is_array($invalidEntries)) {
					foreach ($invalidEntries as $field) {
						$msg .= 'error§'. sprintf(_("Fehlerhafte Eingabe im Datenfeld %s (wurde nicht gespeichert)!"), "<b>".$field->structure->getName()."</b>") .'§';
					}
				}
			}

			break;


		// Change user password
		case "u_pass_x":

			$UserManagement = new UserManagement($u_id);

			$UserManagement->setPassword();

			break;


		// Delete the user
		case "u_kill_x":

			$UserManagement = new UserManagement($u_id);

			$UserManagement->deleteUser();

			break;


		default:
			break;
		}
	}
}

URLHelper::addLinkParam("studipticket", get_ticket());

// einzelnen Benutzer anzeigen
if (isset($_GET['details'])) {
	if ($details=="__" && in_array("Standard",$GLOBALS['STUDIP_AUTH_PLUGIN'])) { // neuen Benutzer anlegen
		?>
		<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		<tr><td class="blank" colspan=2>

			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?=URLHelper::getLink('')?>">
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Benutzername:")?></b></td>
					<td>&nbsp;<input type="text" name="username" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("globaler Status:")?>&nbsp;</b></td>
					<td>&nbsp;<? print $perm->perm_sel("perms", 'autor') ?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Sichtbarkeit")?>&nbsp;</b></td>
					<td>&nbsp;<?=vis_chooser('', TRUE) ?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Vorname:")?></b></td>
					<td>&nbsp;<input type="text" name="Vorname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("Nachname:")?></b></td>
					<td>&nbsp;<input type="text" name="Nachname" size=24 maxlength=63 value=""></td>
				</tr>
				<tr>
				<td><b>&nbsp;<?=_("Titel:")?></b>
				</td><td align="right"><select name="title_front_chooser" onChange="document.edit.title_front.value=document.edit.title_front_chooser.options[document.edit.title_front_chooser.selectedIndex].text;">
				<?
				for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i){
					echo "\n<option>$TITLE_FRONT_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td>&nbsp;<input type="text" name="title_front" value="" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td><b>&nbsp;<?=_("Titel nachgest.:")?></b>
				</td><td align="right"><select name="title_rear_chooser" onChange="document.edit.title_rear.value=document.edit.title_rear_chooser.options[document.edit.title_rear_chooser.selectedIndex].text;">
				<?
				for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i){
					echo "\n<option>$TITLE_REAR_TEMPLATE[$i]</option>";
				}
				?>
				</select></td>
				<td>&nbsp;<input type="text" name="title_rear" value="" size=24 maxlength=63></td>
				</tr>
				<tr>
				<td colspan="2"><b>&nbsp;<?=_("Geschlecht:")?></b></td>
				<td>&nbsp;<input type="RADIO" checked name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp;
				<input type="RADIO" name="geschlecht" value="1"><?=_("weiblich")?></td>
				</tr>
				<tr>
					<td colspan="2"><b>&nbsp;<?=_("E-Mail:")?></b></td>
					<td>&nbsp;<input type="text" name="Email" size=48 maxlength=63 value="">&nbsp;</td>
				</tr>
				<tr>
				<td colspan="2"><b>&nbsp;<?=_("Einrichtung:")?></b></td>
					<td>&nbsp;<select name="select_inst_id">
					<?
			if ($auth->auth['perm'] == "root"){
				$db->query("SELECT Institut_id, Name, 1 AS is_fak  FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
			} elseif ($auth->auth['perm'] == "admin") {
				$db->query("SELECT a.Institut_id,Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
				WHERE a.user_id='$user->id' AND a.inst_perms='admin' ORDER BY is_fak,Name");
			}
			printf ("<option value=\"0\">%s</option>\n", _("-- bitte Einrichtung ausw&auml;hlen (optional) --"));
			while ($db->next_record()){
				printf ("<option value=\"%s\" style=\"%s\">%s </option>\n", $db->f("Institut_id"),($db->f("is_fak") ? "font-weight:bold;" : ""), htmlReady(substr($db->f("Name"), 0, 70)));
				if ($db->f("is_fak")){
					$db2->query("SELECT Institut_id, Name FROM Institute WHERE fakultaets_id='" .$db->f("Institut_id") . "' AND institut_id!='" .$db->f("Institut_id") . "' ORDER BY Name");
					while ($db2->next_record()){
						printf("<option value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s </option>\n", $db2->f("Institut_id"), htmlReady(substr($db2->f("Name"), 0, 70)));
					}
				}
			}
			if($GLOBALS['MAIL_VALIDATE_BOX']){
				echo chr(10).'<tr><td colspan="3" align="right"><input type="checkbox" id="disable_mail_host_check" name="disable_mail_host_check" value="1"><label for="disable_mail_host_check" >'._("Mailboxüberprüfung deaktivieren").'</label></td></tr>';
			}
			?>
			</select>
					&nbsp;</td>
				</tr>
				<tr>
				<td colspan=3 align=center>&nbsp;
				<input type="IMAGE" name="create" <?=makeButton("anlegen", "src")?> value=" <?=_("Benutzer anlegen")?> ">&nbsp;
				<input type="IMAGE" name="nothing" <?=makeButton("abbrechen", "src")?> value=" <?=_("Abbrechen")?> ">
				&nbsp;</td></tr>
			</form></table>

		</td></tr>
		<tr><td class="blank" colspan=2>&nbsp;</td></tr>
		</table>
		<?

	} else { // alten Benutzer bearbeiten

		$db->query("SELECT auth_user_md5.*, (changed + 0) as changed_compat, mkdate, title_rear, title_front, geschlecht FROM auth_user_md5 LEFT JOIN ".$GLOBALS['user']->that->database_table." ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) WHERE username ='$details'");
		while ($db->next_record()) {
			if ($db->f("changed_compat") != "") {
				$stamp = mktime(substr($db->f("changed_compat"),8,2),substr($db->f("changed_compat"),10,2),substr($db->f("changed_compat"),12,2),substr($db->f("changed_compat"),4,2),substr($db->f("changed_compat"),6,2),substr($db->f("changed_compat"),0,4));
				$inactive = floor((time() - $stamp) / 3600 / 24)	." " . _("Tagen");
			} else {
				$inactive = _("nie benutzt");
			}
			?>

			<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>
			<tr><td class="blank" colspan=2>

			<table border=0 bgcolor="#eeeeee" align="center" cellspacing=0 cellpadding=2>
			<form name="edit" method="post" action="<?=URLHelper::getLink('')?>">
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Benutzername:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.username", $db->f('auth_plugin'))) {
						echo htmlReady($db->f("username"));
					} else {
					?><input type="text" name="username" size=24 maxlength=63 value="<?=htmlReady($db->f("username"))?>"><?
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("globaler Status:")?>&nbsp;</b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.perms", $db->f('auth_plugin'))) {
						echo $db->f("perms");
					} else {
						print $perm->perm_sel("perms", $db->f("perms"));
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Sichtbarkeit:")?>&nbsp;</b></td>
					<td class="steel1">&nbsp;&nbsp;<?=vis_chooser($db->f('visible'))?>&nbsp;<small>(<?=$db->f('visible')?>)</small></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Vorname:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.Vorname", $db->f('auth_plugin'))) {
						echo htmlReady($db->f("Vorname"));
					} else {
						?><input type="text" name="Vorname" size=24 maxlength=63 value="<?=htmlReady($db->f("Vorname"))?>"><?
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Nachname:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.Nachname", $db->f('auth_plugin'))) {
						echo htmlReady($db->f("Nachname"));
					} else {
						?><input type="text" name="Nachname" size=24 maxlength=63 value="<?=htmlReady($db->f("Nachname"))?>"><?
					}
					?>
					</td>
				</tr>
				<tr>
				<td class="steel1"><b>&nbsp;<?=_("Titel:")?></b>
				</td><td class="steel1" align="right">
				<?
				if (StudipAuthAbstract::CheckField("user_info.title_front", $db->f('auth_plugin'))) {
						echo "&nbsp;</td><td class=\"steel1\">&nbsp;" . htmlReady($db->f("title_front"));
				} else {
				?>
				<select name="title_front_chooser" onChange="document.edit.title_front.value=document.edit.title_front_chooser.options[document.edit.title_front_chooser.selectedIndex].text;">
				<?
				 for($i = 0; $i < count($TITLE_FRONT_TEMPLATE); ++$i){
					 echo "\n<option";
					 if($TITLE_FRONT_TEMPLATE[$i] == $db->f("title_front"))
					 	echo " selected ";
					 echo ">".htmlReady($TITLE_FRONT_TEMPLATE[$i])."</option>";
					}
				?>
				</select></td>
				<td class="steel1">&nbsp;<input type="text" name="title_front" value="<?=htmlReady($db->f("title_front"))?>" size=24 maxlength=63>
				<?
				}
				?>
				</td>
				</tr>
				<tr>
				<td class="steel1"><b>&nbsp;<?=_("Titel nachgest.:")?></b>
				</td><td class="steel1" align="right">
				<?
				if (StudipAuthAbstract::CheckField("user_info.title_rear", $db->f('auth_plugin'))) {
						echo "&nbsp;</td><td class=\"steel1\">&nbsp;" . htmlReady($db->f("title_rear"));
				} else {
				?>
				<select name="title_rear_chooser" onChange="document.edit.title_rear.value=document.edit.title_rear_chooser.options[document.edit.title_rear_chooser.selectedIndex].text;">
				<?
				 for($i = 0; $i < count($TITLE_REAR_TEMPLATE); ++$i){
					 echo "\n<option";
					 if($TITLE_REAR_TEMPLATE[$i] == $db->f("title_rear"))
					 	echo " selected ";
					 echo ">".htmlReady($TITLE_REAR_TEMPLATE[$i])."</option>";
					}
				?>
				</select></td>
				<td class="steel1">&nbsp;<input type="text" name="title_rear" value="<?=htmlReady($db->f("title_rear"))?>" size=24 maxlength=63>
				<?
				}
				?>
				</td>
				</tr>
				<tr>
				<td colspan="2" class="steel1"><b>&nbsp;<?=_("Geschlecht:")?></b></td>
				<td class="steel1">&nbsp;
				<?
				if (StudipAuthAbstract::CheckField("user_info.geschlecht", $db->f('auth_plugin'))) {
					echo "&nbsp;" . (!$db->f("geschlecht") ? _("m&auml;nnlich") : _("weiblich"));
				} else {
				?>
				<input type="RADIO" <? if (!$db->f("geschlecht")) echo "checked";?> name="geschlecht" value="0"><?=_("m&auml;nnlich")?>&nbsp;
				<input type="RADIO" <? if ($db->f("geschlecht")) echo "checked";?> name="geschlecht" value="1"><?=_("weiblich")?>
				<?
				}
				?>
				</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("E-Mail:")?></b></td>
					<td class="steel1">&nbsp;
					<?
					if (StudipAuthAbstract::CheckField("auth_user_md5.Email", $db->f('auth_plugin'))) {
						echo htmlReady($db->f("Email"));
					} else {
					?><input type="text" name="Email" size=48 maxlength=63 value="<?=htmlReady($db->f("Email"))?>">&nbsp;
					<?
					}
					?>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("inaktiv seit:")?></b></td>
					<td class="steel1">&nbsp;<? echo $inactive ?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("registriert seit:")?></b></td>
					<td class="steel1">&nbsp;<? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo _("unbekannt"); ?></td>
				</tr>
				<tr>
					<td colspan="2" class="steel1"><b>&nbsp;<?=_("Authentifizierung:")?></b></td>
					<td class="steel1">&nbsp;<?=($db->f("auth_plugin") ? $db->f("auth_plugin") : "Standard")?></td>
				</tr>

				<?
				$admin_ok = false;
				if ($perm->is_fak_admin() && $db->f('perms') == 'admin'){
					$db2->query("SELECT IF(count(a.Institut_id) - count(c.inst_perms),0,1) AS admin_ok FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id AND b.Institut_id!=b.fakultaets_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id AND c.user_id = '".$user->id."' AND c.inst_perms='admin')
							WHERE a.user_id ='".$db->f('user_id')."' AND a.inst_perms = 'admin'");
					$db2->next_record();
					$admin_ok = $db2->f('admin_ok');
				}

				if ($perm->have_perm('root') || ($db->f('perms') != 'admin' && $db->f('perms') != 'root') || $admin_ok) {

					echo "<tr>\n";
                                	echo "  <td class=\"steel1\"><b>&nbsp;"._("Benutzer sperren:")."</b></td>\n";
                                	echo "  <td class=\"steel1\">\n";
                                	echo "    <INPUT TYPE=\"checkbox\" NAME=\"locked\" VALUE=\"1\" ".($db->f("locked")==1 ? "CHECKED" : "").">"._("sperren")."\n";
                                	echo "  </td>\n";
                                	echo "  <td class=\"steel1\">\n";
                                	echo "    &nbsp;"._("Kommentar:")."&nbsp;\n";
                                	echo "    <INPUT TYPE=\"text\" NAME=\"lock_comment\" VALUE=\"".htmlReady($db->f("lock_comment"))."\" SIZE=\"24\" MAXLENGTH=\"255\">\n";
                                	echo "  </td>\n";
                               		echo "</tr>\n";
					if ($db->f("locked")==1)
                                        	echo "<TR><TD CLASS=\"steel1\" COLSPAN=\"3\" ALIGN=\"center\"><FONT SIZE=\"-2\">"._("Gesperrt von:")." ".htmlReady(get_fullname($db->f("locked_by")))." (<A HREF=\"about.php?username=".get_username($db->f("locked_by"))."\">".get_username($db->f("locked_by"))."</A>)</FONT></TD></TR>\n";
				}
				$userEntries = DataFieldEntry::getDataFieldEntries($db->f('user_id'));
				foreach ($userEntries as $entry) {
					$id = $entry->structure->getID();
					$color = '#000000';
					if ($invalidEntries[$id]) {
						$entry = $invalidEntries[$id];
						$entry->structure->load();
						$color = '#ff0000';
					}
					if ($entry->structure->accessAllowed($perm, $user->id, $db->f("user_id"))) {
						echo chr(10) . '<tr><td class="steel1" colspan="2">';
						echo chr(10) . '<span style="font-weight:bold;color:'.$color.'">&nbsp;' . htmlReady($entry->getName()).':</span></td>';
						echo chr(10) . '<td class="steel1">&nbsp;';
						if ($perm->have_perm($entry->structure->getEditPerms())) {
							echo chr(10).'<input type="HIDDEN" name="datafield_id[]" value="'.$entry->structure->getID().'">';
							echo chr(10).'<input type="HIDDEN" name="datafield_type[]" value="'.$entry->getType().'">';
							echo chr(10).$entry->getHTML('datafield_content[]', $entry->structure->getID());
						} else {
							echo chr(10).htmlReady($entry->getValue());
						}
						echo chr(10).'</td></tr>';
					}
				}

				if($GLOBALS['MAIL_VALIDATE_BOX'] && !StudipAuthAbstract::CheckField("auth_user_md5.password", $db->f('auth_plugin'))){
					echo chr(10).'<tr><td class="steel1" colspan="3" align="right"><input type="checkbox" id="disable_mail_host_check" name="disable_mail_host_check" value="1"><label for="disable_mail_host_check" >'._("Mailboxüberprüfung deaktivieren").'</label></td></tr>';
				}
				?>

				<td class="steel1" colspan=3 align=center>&nbsp;
				<input type="hidden" name="u_id" value="<?= $db->f("user_id") ?>">
				<?
				if ($perm->have_perm('root') || ($db->f('perms') != 'admin' && $db->f('perms') != 'root') || $admin_ok) {
					?>
					<input type="IMAGE" name="u_edit" <?=makeButton("uebernehmen", "src")?> value=" <?=_("Ver&auml;ndern")?> ">&nbsp;
					<?
					if (!StudipAuthAbstract::CheckField("auth_user_md5.password", $db->f('auth_plugin'))) {
						?>
						<input type="IMAGE" name="u_pass" <?=makeButton("neuespasswort", "src")?> value=" <?=_("Passwort neu setzen")?> ">&nbsp;
						<?
					}
					?>
					<input type="IMAGE" name="u_kill" <?=makeButton("loeschen", "src")?> value=" <?=_("L&ouml;schen")?> ">&nbsp;
					<?
		 		}
				?>
				<input type="IMAGE" name="nothing" <?=makeButton("abbrechen", "src")?> value=" <?=_("Abbrechen")?> ">
				&nbsp;</td></tr>
			</form>

			<tr><td colspan=3 class="blank">&nbsp;</td></tr>

			<? // links to everywhere
			echo "<tr><td class=\"steelgraulight\" colspan=3 align=\"center\">";
			echo "&nbsp;" . _("pers&ouml;nliche Homepage") . " <a href=\"".URLHelper::getLink('about.php?username=' . $db->f("username")) . "\"><img src=\"".$GLOBALS['ASSETS_URL']."images/einst.gif\" ".tooltip(_("Zur persönlichen Homepage des Benutzers"))." align=\"texttop\"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp";
			echo "&nbsp;" . _("Nachricht an BenutzerIn") . " <a href=\"".URLHelper::getLink('sms_send.php?rec_uname=' . $db->f("username")) . "\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" ".tooltip(_("Nachricht an den Benutzer verschicken")) . " align=\"texttop\"></a>";
			echo "</td></tr>";
			if ($perm->have_perm('root')){
				echo "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				echo "&nbsp;" . _("Datei- und Aktivitätenübersicht") . "&nbsp;";
				echo '<a href="' . URLHelper::getLink('user_activities.php?username=' . $db->f('username')) .'">
					<img src="'.$GLOBALS['ASSETS_URL'].'images/icon-disc.gif" align="absmiddle" border="0">
					</a>';
				echo "</td></tr>\n";
			}
			$temp_user_id = $db->f("user_id");
			if ($perm->have_perm("root"))
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (Institut_id) WHERE user_id ='$temp_user_id' AND inst_perms != 'user'");
			elseif ($perm->is_fak_admin())
				$db2->query("SELECT a.Institut_id,b.Name FROM user_inst AS a
							LEFT JOIN Institute b ON (a.Institut_id=b.Institut_id)
							LEFT JOIN user_inst AS c ON(b.fakultaets_id=c.Institut_id )
							WHERE a.user_id ='".$db->f("user_id")."' AND a.inst_perms <> 'user' AND c.user_id = '$user->id' AND c.inst_perms='admin'");
			else
				$db2->query("SELECT Institute.Institut_id, Name FROM user_inst AS x LEFT JOIN user_inst AS y USING (Institut_id) LEFT JOIN Institute USING (Institut_id) WHERE x.user_id ='$temp_user_id' AND x.inst_perms != 'user' AND y.user_id = '$user->id' AND y.inst_perms = 'admin'");
			if ($db2->num_rows()) {
				print "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				print "<b>&nbsp;" . _("Link zur MitarbeiterInnen-Verwaltung") . "&nbsp;</b>";
				print "</td></tr>\n";
			}
			while ($db2->next_record()) {
				echo "<tr><td class=\"steel2\" colspan=3 align=\"center\">";
				echo "&nbsp;" . htmlReady($db2->f("Name"));
				echo ' <a href="' . URLHelper::getLink(sprintf('inst_admin.php?details=%s&admin_inst_id=%s', $db->f("username"), $db2->f("Institut_id"))) . '">';
				echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/admin.gif\" align=\"texttop\" ".tooltip(_("Ändern der Einträge des Benutzers in der jeweiligen Einrichtung"))."\"></a>&nbsp;";
				echo "</td></tr>\n";
			}
			?>

			</table>

			</td></tr>
			<tr><td class="blank" colspan=2>&nbsp;</td></tr>

			</table>
			<?
		}
	}

} else {

	// Gesamtliste anzeigen

	?>

	<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr><td class="blank" colspan=2>&nbsp;</td></tr>

	<?
	parse_msg($UserManagement->msg);
	?>

	<tr><td class="blank" colspan=2>
	<?
	if (in_array("Standard",$GLOBALS['STUDIP_AUTH_PLUGIN'])){
		printf("&nbsp;&nbsp;"._("Neuen Benutzer-Account %s")."<br /><br />", "<a href=\"" . URLHelper::getLink("?details=__") . "\"><img ".makeButton("anlegen", "src")." align=\"absmiddle\"></a>");
	} else {
		echo "<p>&nbsp;" . _("Die Standard Authentifizierung ist ausgeschaltet. Das Anlegen von neuen Benutzern ist nicht möglich!") . "</p>";
	}

	include ('lib/include/pers_browse.inc.php');
	print "<br>\n";
	parse_msg($msg);


	if (isset($pers_browse_search_string)) { // Es wurde eine Suche initiert

		// nachsehen, ob wir ein Sortierkriterium haben, sonst nach username
		if (!isset($sortby) || $sortby=="") {
			if (!isset($new_user_md5_sortby) || $new_user_md5_sortby == "") {
				$new_user_md5_sortby = "username";
			}
		} else {
			$new_user_md5_sortby = $sortby;
			$sess->register("new_user_md5_sortby");
		}

		// Traverse the result set
		$db->query("SELECT auth_user_md5.*, (changed + 0) as changed_compat, mkdate FROM auth_user_md5 LEFT JOIN ".$GLOBALS['user']->that->database_table." ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) $pers_browse_search_string ORDER BY $new_user_md5_sortby");

		if ($db->num_rows() == 0) { // kein Suchergebnis
			print "<table border=0 bgcolor=\"#eeeeee\" align=\"center\" cellspacing=0 cellpadding=2 width=\"80%\">";
			print "<tr valign=\"top\" align=\"middle\">";
			print "<td>" . _("Es wurden keine Personen gefunden, auf die die obigen Kriterien zutreffen.") . "</td>";
			print "</tr><tr><td class=\"blank\">&nbsp;</td></tr></table>";

		} else { // wir haben ein Suchergebnis
			echo '<table border="0" bgcolor="#eeeeee" align="center" cellspacing="0" class="blank" cellpadding="2" width="100%">';

			if ($perm->have_perm('root')){
				echo '<tr valign="top"><td colspan="8"><a href="' . URLHelper::getLink('admin_user_kill.php?transfer_search=1') . '">'._("Suchergebnis in Löschformular übernehmen").'</a></td></tr>';
			}

			echo '<tr valign="top" align="middle">';
				if ($db->num_rows() == 1)
			 		echo '<td colspan="8">' . _("Suchergebnis: Es wurde <b>1</b> Person gefunden.") . "</td></tr>\n";
				else
			 		printf('<td colspan="8">' . _("Suchergebnis: Es wurden <b>%s</b> Personen gefunden.") . "</td></tr>\n", $db->num_rows());
			?>
			 <tr valign="top" align="middle">
				<th align="left"><a href="<?=URLHelper::getLink('?sortby=username')?>"><?=_("Benutzername")?></a>&nbsp;<span style="font-size:smaller;font-weight:normal;color:#f8f8f8;">(<?=_("Sichtbarkeit")?>)</span></th>
				<th align="left"><a href="<?=URLHelper::getLink('?sortby=perms')?>"><?=_("Status")?></a></th>
				<th align="left"><a href="<?=URLHelper::getLink('?sortby=Vorname')?>"><?=_("Vorname")?></a></th>
				<th align="left"><a href="<?=URLHelper::getLink('?sortby=Nachname')?>"><?=_("Nachname")?></a></th>
				<th align="left"><a href="<?=URLHelper::getLink('?sortby=Email')?>"><?=_("E-Mail")?></a></th>
				<th><a href="<?=URLHelper::getLink('?sortby=changed')?>"><?=_("inaktiv")?></a></th>
				<th><a href="<?=URLHelper::getLink('?sortby=mkdate')?>"><?=_("registriert seit")?></a></th>
				<th><a href="<?=URLHelper::getLink('?sortby=auth_plugin')?>"><?=_("Authentifizierung")?></a></th>
			 </tr>
			<?

			while ($db->next_record()):
				if ($db->f("changed_compat") != "") {
					$stamp = mktime(substr($db->f("changed_compat"),8,2),substr($db->f("changed_compat"),10,2),substr($db->f("changed_compat"),12,2),substr($db->f("changed_compat"),4,2),substr($db->f("changed_compat"),6,2),substr($db->f("changed_compat"),0,4));
					$inactive = floor((time() - $stamp) / 3600 / 24).'d';
					if($inactive == 0){
						$inactive = gmdate('H\hi\ms\s', (time() - $stamp));
					}
				} else {
					$inactive = _("nie benutzt");
				}
				?>
				<tr valign=middle align=left>
					<td class="<? $cssSw->switchClass(); echo $cssSw->getClass() ?>"><a href="<?=URLHelper::getLink('?details=' . $db->f("username"))?>"><?php $db->p("username") ?></a>&nbsp;<?
					if ($db->f('locked')=='1'){
						echo '<span style="font-size:smaller;color:red;font-weight:bold;">' . _("gesperrt!") .'</span>';
					} else {
						echo '<span style="font-size:smaller;color:#888;">('.$db->f('visible').')</span>';
					}
					?></TD>
					<td class="<? echo $cssSw->getClass() ?>"><?=$db->f("perms") ?></td>
					<td class="<? echo $cssSw->getClass() ?>"><?=htmlReady($db->f("Vorname")) ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?=htmlReady($db->f("Nachname")) ?>&nbsp;</td>
					<td class="<? echo $cssSw->getClass() ?>"><?=htmlReady($db->f("Email"))?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><?php echo $inactive ?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><? if ($db->f("mkdate")) echo date("d.m.y, G:i", $db->f("mkdate")); else echo _("unbekannt"); ?></td>
					<td class="<? echo $cssSw->getClass() ?>" align="center"><?=($db->f("auth_plugin") ? $db->f("auth_plugin") : "Standard")?></td>
				</tr>
				<?
			endwhile;
			print ("</table>");
		}
	}
	print ("</td></tr></table>");


}
include ('lib/include/html_end.inc.php');
page_close();
?>
