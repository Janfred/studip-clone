<?
/**
* admin_datafields.php
*
* administrate the generic datafields
*
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		admin_datafields.php
* @modulegroup		admin
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_datafields.php
// Administration fuer generische Datenfelder
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));

$perm->check("root");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once('lib/msg.inc.php');	//Ausgaben
require_once('config.inc.php');	//Settings....
require_once 'lib/functions.php';	//basale Funktionen
require_once('lib/visual.inc.php');	//Darstellungsfunktionen
require_once('lib/classes/DataFields.class.php');	//class DataFields

$db=new DB_Seminar;
$db2=new DB_Seminar;
$cssSw=new cssClassSwitcher;
$sess->register("admin_datafields_data");
$DataFields=new DataFields;


// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');	//hier wird das Reiter- und Suchsystem des Adminbereichs eingebunden


if ($change_datafield) {
	$admin_datafields_data["change_datafield"] = $change_datafield;
	$admin_datafields_data["create_datafield"] = FALSE;
}

if ($create_new) {
	$admin_datafields_data["create_datafield"] = $create_new;
	$admin_datafields_data["change_datafield"] = FALSE;
}

if ($cancel) {
	$admin_datafields_data["create_datafield"] = FALSE;
	$admin_datafields_data["change_datafield"] = FALSE;
}

if (($send) && (($admin_datafields_data["change_datafield"]) || ($admin_datafields_data["create_datafield"]))) {

	function datafield_check_array($datafield) {	// we do not want duplicated code. Used for datafield_class & datafield_view_perms

		global $DataFields;

		if (is_array($datafield)) {
			$tmp_datafield = $datafield;
			$datafield = 0;
			foreach ($tmp_datafield as $val) {
				if ($val == "FALSE")
					$datafield = "NULL";
				else {
					$datafield+= $DataFields->perms_mask[$val];
				}
			}
		} elseif ($datafield == "FALSE")
			$datafield = "NULL";

		return $datafield;
	}

	$datafield_class = datafield_check_array($datafield_class);

	$DataFields->storeDataField($admin_datafields_data["change_datafield"], $datafield_name, $admin_datafields_data["create_datafield"], $datafield_class, $datafield_edit_perms, $datafield_priority, $datafield_view_perms);
	if ($admin_datafields_data["change_datafield"]) {
		$admin_datafields_data["change_datafield"] = FALSE;
		$msg = "msg§"._("Die &Auml;nderungen am Datenfeld wurden &uuml;bernommen.");
	} else {
		$admin_datafields_data["create_datafield"] = FALSE;
		$msg = "msg§"._("Das Datenfeld wurde angelegt.");
	}
}

if ($kill_datafield) {
	$DataFields->killDataField($kill_datafield);
	$msg = "msg§"._("Das Datenfeld wurde gel&ouml;scht.");
}

?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp; <b>
		<?
		print _("generische Datenfelder konfigurieren");
		?>
		</td>
	</tr>
 	<tr>
		<td class="blank" valign="top">
			<?
			if (isset($msg)) {
			?>
				<table border="0">
				<tr><td>&nbsp;</td></tr>
				<?parse_msg($msg, "§", "blank", 1, FALSE);?>
				</table>
			<? } ?>
			&nbsp;
		</td>
	</tr>
	<form method="POST" name="modules" action="<? echo $PHP_SELF ?>?send=TRUE">
	<tr>
		<td class="blank" colspan=2>
		&nbsp;<b><font size="-1"><?=_("Datenfelder f&uuml;r Veranstaltungen")?></font></b>
		<table width = "99%" border="0" cellpadding="2" cellspacing="0" align="center">
			<tr>
				<td class="steel" width="20%" align="left" valign="bottom">
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="1" height="18" border="0" />
					<font size="-1">
					<b><?=_("Name")?></b>
					</font>
				</td>
				<td class="steel" width="20%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Veranstaltungs-Kategorie")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("ben&ouml;tigter Status")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Sichtbarkeit")?></b>
					</font>
				</td>
				<td class="steel" width="8%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Reihenfolge")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Anzahl der Eintr&auml;ge")?></b>
					</font>
				</td>
				<td class="steel" width="7%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Aktion")?></b>
					</font>
				</td>
			</tr>
			<?
			$datafields_list = $DataFields->getFields("sem");
			foreach ($datafields_list as $key=>$val) {	//** start of loop **
				$cssSw->switchClass()
				?>
			<tr>
				<td class="<?=$cssSw->getClass()?>" align="left">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<a name=\"a\"></a>";
						printf ("<input type=\"TEXT\" maxlength=\"255\" size=\"30\" style=\"{font-size:8 pt; width: 90%%;}\" value=\"%s\" name=\"datafield_name\" />", $val["name"]);
					} else
						print $val["name"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if (($admin_datafields_data["change_datafield"] == $val["datafield_id"]) && (!$val["used_entries"])) {
						print "<select name=\"datafield_class\" style=\"{font-size:8 pt;}\">";
						echo "<option value=\"FALSE\">". _("alle") ."</option>";
						foreach ($SEM_CLASS as $key2=>$val2) {
							printf ("<option %s value=\"%s\">%s</option>", ($val["object_class"] == $key2) ? "selected" : "", $key2, $val2["name"]);
						}
						print "</select>";
					} else
						print ($val["object_class"]) ? $SEM_CLASS[$val["object_class"]]["name"] : _("alle")
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<select name=\"datafield_edit_perms\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"user\">user</option>", ($val["edit_perms"] == "user") ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["edit_perms"] == "autor") ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["edit_perms"] == "tutor") ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["edit_perms"] == "dozent") ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["edit_perms"] == "admin") ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["edit_perms"] == "root") ? "selected" : "");
						print "</select>";
					} else
						print $val["edit_perms"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<select name=\"datafield_view_perms\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"all\">%s</option>", ($val["view_perms"] == "all") ? "selected" : "", _("alle"));
						printf ("<option %s value=\"user\">user</option>", ($val["view_perms"] == "user") ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["view_perms"] == "autor") ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["view_perms"] == "tutor") ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["view_perms"] == "dozent") ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["view_perms"] == "admin") ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["view_perms"] == "root") ? "selected" : "");
						print "</select>";
					} else
						if ($val["view_perms"] == "all") {
							print _("alle");
						}
						else {
							print $val["view_perms"];
						}
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						printf ("<input type=\"TEXT\" maxlength=\"10\" size=\"5\" style=\"{font-size:8 pt; width: 30%%; text-align: center;}\" value=\"%s\" name=\"datafield_priority\" />", $val["priority"]);
					} else
						print $val["priority"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?=$val["used_entries"]?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"])
						printf ("&nbsp;&nbsp;<input type=\"IMAGE\" name=\"send_datafield\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" %s />", tooltip(_("Änderungen übernehmen")));
					else
						printf ("&nbsp;&nbsp;<a href=\"%s?change_datafield=%s#a\"><img src=\"".$GLOBALS['ASSETS_URL']."images/edit_transparent.gif\" border=\"0\" %s /></a>", $PHP_SELF, $val["datafield_id"], tooltip(_("Datenfeld verändern")));
					if (!$val["used_entries"])
						printf ("&nbsp;&nbsp;<a href=\"%s?kill_datafield=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\" %s /></a>", $PHP_SELF, $val["datafield_id"], tooltip(_("Diese Kategorie löschen (wird von keiner Veranstaltung verwendet)")));
					?>
				</td>
			</tr>
			<?
			}
			if ($admin_datafields_data["create_datafield"] == "sem") {
				$cssSw->switchClass()
			?>
			<tr>
				<td class="<?=$cssSw->getClass()?>" align="left">
					<a name="a"></a>
					<font size="-1">
						<input type="TEXT" maxlength="255" size="30" style="{font-size:8 pt; width: 90%;}" name="datafield_name" />
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_class" style="{font-size:8 pt;}">";
						<option value="FALSE"><?=_("alle")?></option>
						<?
						foreach ($SEM_CLASS as $key=>$val) {
							printf ("<option value=\"%s\">%s</option>", $key, $val["name"]);
						}
						?>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_edit_perms" style="{font-size:8 pt;}">
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
				<? //New possibility: Set rights for visibility ?>
					<font size="-1">
						<select name="datafield_view_perms" style="{font-size:8 pt;}">
						<option value="all"><?=_("alle")?></option>
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<input type="TEXT" maxlength="10" size="5" style="{font-size:8 pt; width: 30%; text-align: center;}" name="datafield_priority" />
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					&nbsp;
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center" nowrap>
					&nbsp;&nbsp;<input type="IMAGE" name="send" src="<?= $GLOBALS['ASSETS_URL'] ?>images/haken_transparent.gif" border="0" <?=tooltip(_("Datenfeld speichern"))?> />
					&nbsp;<a href="<?=$PHP_SELF?>?cancel=TRUE"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/x_transparent.gif" border="0" <?=tooltip(_("Anlegen abbrechen"))?> />
				</td>
			</tr>
			<?
			}
			?>
		</table>
		<?
		if ($admin_datafields_data["create_datafield"] != "sem") {
			?>&nbsp;<a href="<?=$PHP_SELF?>?create_new=sem#a"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/add_right.gif" border="0" <?=tooltip(_("Neues Datenfeld für Veranstaltungen anlegen"))?> /></a><?
		}
		?>
		<br />&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		&nbsp;<b><font size="-1"><?=_("Datenfelder f&uuml;r Einrichtungen")?></font></b>
		<table width = "99%" border="0" cellpadding="2" cellspacing="0" align="center">
			<tr>
				<td class="steel" width="20%" align="left" valign="bottom">
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="1" height="18" border="0" />
					<font size="-1">
					<b><?=_("Name")?></b>
					</font>
				</td>
				<td class="steel" width="20%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Einrichtungs-Typ")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("ben&ouml;tigter Status")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Sichtbarkeit")?></b>
					</font>
				</td>
				<td class="steel" width="8%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Reihenfolge")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Anzahl der Eintr&auml;ge")?></b>
					</font>
				</td>
				<td class="steel" width="7%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Aktion")?></b>
					</font>
				</td>
			</tr>
			<?
			$datafields_list = $DataFields->getFields("inst");
			$cssSw->resetClass();
			foreach ($datafields_list as $key=>$val) {
				$cssSw->switchClass()
				?>
			<tr>
				<td class="<?=$cssSw->getClass()?>" align="left">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<a name=\"a\"></a>";
						printf ("<input type=\"TEXT\" maxlength=\"255\" size=\"30\" style=\"{font-size:8 pt; width: 90%%;}\" value=\"%s\" name=\"datafield_name\" />", $val["name"]);
					} else
						print $val["name"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if (($admin_datafields_data["change_datafield"] == $val["datafield_id"]) && (!$val["used_entries"])) {
						print "<select name=\"datafield_class\" style=\"{font-size:8 pt}\">";
						echo "<option value=\"FALSE\">". _("alle") ."</option>";
						foreach ($INST_TYPE as $key2=>$val2) {
							printf ("<option %s value=\"%s\">%s</option>", ($val["object_class"] == $key2) ? "selected" : "", $key2, $val2["name"]);
						}
						print "</select>";
					} else
						print ($val["object_class"]) ? $INST_TYPE[$val["object_class"]]["name"] : _("alle")
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<select name=\"datafield_edit_perms\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"user\">user</option>", ($val["edit_perms"] == "user") ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["edit_perms"] == "autor") ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["edit_perms"] == "tutor") ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["edit_perms"] == "dozent") ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["edit_perms"] == "admin") ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["edit_perms"] == "root") ? "selected" : "");
						print "</select>";
					} else
						print $val["edit_perms"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<select name=\"datafield_view_perms\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"all\">%s</option>", ($val["view_perms"] == "all") ? "selected" : "", _("alle"));
						printf ("<option %s value=\"user\">user</option>", ($val["view_perms"] == "user") ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["view_perms"] == "autor") ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["view_perms"] == "tutor") ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["view_perms"] == "dozent") ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["view_perms"] == "admin") ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["view_perms"] == "root") ? "selected" : "");
						print "</select>";
					} else
						if ($val["view_perms"] == "all") {
							print _("alle");
						}
						else {
							print $val["view_perms"];
						}
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						printf ("<input type=\"TEXT\" maxlength=\"10\" size=\"5\" style=\"{font-size:8 pt; width: 30%%; text-align: center;}\" value=\"%s\" name=\"datafield_priority\" />", $val["priority"]);
					} else
						print $val["priority"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?=$val["used_entries"]?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"])
						printf ("&nbsp;&nbsp;<input type=\"IMAGE\" name=\"send_datafield\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" %s />", tooltip(_("Änderungen übernehmen")));
					else
						printf ("&nbsp;&nbsp;<a href=\"%s?change_datafield=%s#a\"><img src=\"".$GLOBALS['ASSETS_URL']."images/edit_transparent.gif\" border=\"0\" %s /></a>", $PHP_SELF, $val["datafield_id"], tooltip(_("Datenfeld verändern")));
					if (!$val["used_entries"])
						printf ("&nbsp;&nbsp;<a href=\"%s?kill_datafield=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\" %s /></a>", $PHP_SELF, $val["datafield_id"], tooltip(_("Diese Kategorie löschen (wird von keiner Veranstaltung verwendet)")));
					?>
				</td>
			</tr>
				<?
			}
			if ($admin_datafields_data["create_datafield"] == "inst") {
				$cssSw->switchClass()
			?>
			<tr>
				<td class="<?=$cssSw->getClass()?>" align="left">
					<a name="a"></a>
					<font size="-1">
						<input type="TEXT" maxlength="255" size="30" style="{font-size:8 pt; width: 90%}" name="datafield_name" />
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_class" style="{font-size:8 pt;}">";
						<option value="FALSE"><?=_("alle")?></option>
						<?
						foreach ($INST_TYPE as $key=>$val) {
							printf ("<option value=\"%s\">%s</option>", $key, $val["name"]);
						}
						?>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_edit_perms" style="{font-size:8 pt;}">";
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_view_perms" style="{font-size:8 pt;}">";
						<option value="all"><?=_("alle")?></option>
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<input type="TEXT" maxlength="10" size="5" style="{font-size:8 pt; width: 30%; text-align: center;}" name="datafield_priority" />
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					&nbsp;
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center" nowrap>
					&nbsp;&nbsp;<input type="IMAGE" name="send" src="<?= $GLOBALS['ASSETS_URL'] ?>images/haken_transparent.gif" border="0" <?=tooltip(_("Datenfeld speichern"))?> />
					&nbsp;<a href="<?=$PHP_SELF?>?cancel=TRUE"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/x_transparent.gif" border="0" <?=tooltip(_("Anlegen abbrechen"))?> />
				</td>
			</tr>
			<?
			}
			?>
		</table>
		<?
		if ($admin_datafields_data["create_datafield"] != "inst") {
			?>&nbsp;<a href="<?=$PHP_SELF?>?create_new=inst#a"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/add_right.gif" border="0" <?=tooltip(_("Neues Datenfeld für Einrichtungen anlegen"))?> /></a><?
		}
		?>
		<br />&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		&nbsp;<b><font size="-1"><?=_("Datenfelder f&uuml;r Nutzer")?></font></b>
		<table width = "99%" border="0" cellpadding="2" cellspacing="0" align="center">
			<tr>
				<td class="steel" width="20%" align="left" valign="bottom">
					<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" width="1" height="18" border="0" />
					<font size="-1">
					<b><?=_("Name")?></b>
					</font>
				</td>
				<td class="steel" width="20%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Nutzer-Status")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("ben&ouml;tigter Status")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Sichtbarkeit")?></b>
					</font>
				</td>
				<td class="steel" width="8%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Reihenfolge")?></b>
					</font>
				</td>
				<td class="steel" width="15%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Anzahl der Eintr&auml;ge")?></b>
					</font>
				</td>
				<td class="steel" width="7%" align="center" valign="bottom">
					<font size="-1">
					<b><?=_("Aktion")?></b>
					</font>
				</td>
			</tr>
			<?
			$datafields_list = $DataFields->getFields("user");
			$cssSw->resetClass();
			foreach ($datafields_list as $key=>$val) {
				$cssSw->switchClass()
				?>
			<tr>
				<td class="<?=$cssSw->getClass()?>" align="left">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<a name=\"a\"></a>";
						printf ("<input type=\"TEXT\" maxlength=\"255\" size=\"30\" style=\"{font-size:8 pt; width: 90%%;}\" value=\"%s\" name=\"datafield_name\" />", $val["name"]);
					} else
						print $val["name"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if (($admin_datafields_data["change_datafield"] == $val["datafield_id"]) && (!$val["used_entries"])) {
						print "<select name=\"datafield_class[]\"  multiple size=\"7\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"FALSE\">"._("alle")."</option>", (!$val["object_class"]) ? "selected" : "");
						printf ("<option %s value=\"user\">user</option>", ($val["object_class"] & $DataFields->perms_mask["user"]) ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["object_class"] & $DataFields->perms_mask["autor"]) ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["object_class"] & $DataFields->perms_mask["tutor"]) ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["object_class"] & $DataFields->perms_mask["dozent"]) ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["object_class"] & $DataFields->perms_mask["admin"]) ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["object_class"] & $DataFields->perms_mask["root"]) ? "selected" : "");
						print "</select>";
					} else {
						print ($val["object_class"]) ? $DataFields->getReadableUserClass($val["object_class"]) : _("alle");
					}
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<select name=\"datafield_edit_perms\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"user\">user</option>", ($val["edit_perms"] == "user") ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["edit_perms"] == "autor") ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["edit_perms"] == "tutor") ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["edit_perms"] == "dozent") ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["edit_perms"] == "admin") ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["edit_perms"] == "root") ? "selected" : "");
						print "</select>";
					} else
						print $val["edit_perms"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						print "<select name=\"datafield_view_perms\" style=\"{font-size:8 pt;}\">";
						printf ("<option %s value=\"all\">%s</option>", ($val["view_perms"] == "user") ? "selected" : "", _("alle"));
						printf ("<option %s value=\"user\">user</option>", ($val["view_perms"] == "user") ? "selected" : "");
						printf ("<option %s value=\"autor\">autor</option>", ($val["view_perms"] == "autor") ? "selected" : "");
						printf ("<option %s value=\"tutor\">tutor</option>", ($val["view_perms"] == "tutor") ? "selected" : "");
						printf ("<option %s value=\"dozent\">dozent</option>", ($val["view_perms"] == "dozent") ? "selected" : "");
						printf ("<option %s value=\"admin\">admin</option>", ($val["view_perms"] == "admin") ? "selected" : "");
						printf ("<option %s value=\"root\">root</option>", ($val["view_perms"] == "root") ? "selected" : "");
						print "</select>";
					} else
						if ($val["view_perms"] == "all") {
							print _("alle");
						}
						else {
							print $val["view_perms"];
						}
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"]) {
						printf ("<input type=\"TEXT\" maxlength=\"10\" size=\"5\" style=\"{font-size:8 pt; width: 30%%; text-align: center;}\" value=\"%s\" name=\"datafield_priority\" />", $val["priority"]);
					} else
						print $val["priority"]
					?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
					<?=$val["used_entries"]?>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<?
					if ($admin_datafields_data["change_datafield"] == $val["datafield_id"])
						printf ("&nbsp;&nbsp;<input type=\"IMAGE\" name=\"send_datafield\" src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" border=\"0\" %s />", tooltip(_("Änderungen übernehmen")));
					else
						printf ("&nbsp;&nbsp;<a href=\"%s?change_datafield=%s#a\"><img src=\"".$GLOBALS['ASSETS_URL']."images/edit_transparent.gif\" border=\"0\" %s /></a>", $PHP_SELF, $val["datafield_id"], tooltip(_("Datenfeld verändern")));
					if (!$val["used_entries"])
						printf ("&nbsp;&nbsp;<a href=\"%s?kill_datafield=%s\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\" %s /></a>", $PHP_SELF, $val["datafield_id"], tooltip(_("Diese Kategorie löschen (wird von keiner Veranstaltung verwendet)")));
					?>
				</td>
			</tr>
				<?
			}
			if ($admin_datafields_data["create_datafield"] == "user") {
				$cssSw->switchClass()
			?>
			<tr>
				<td class="<?=$cssSw->getClass()?>" align="left">
					<a name="a"></a>
					<font size="-1">
						<input type="TEXT" maxlength="255" size="30" style="{font-size:8 pt; width: 90%;}" name="datafield_name" />
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_class[]"  multiple size="7" style="{font-size:8 pt;}">";
						<option value="FALSE"><?=_("alle")?></option>
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_edit_perms" style="{font-size:8 pt;}">";
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<select name="datafield_view_perms" style="{font-size:8 pt;}">";
						<option value="all"><?=_("alle")?></option>
						<option value="user">user</option>
						<option value="autor">autor</option>
						<option value="tutor">tutor</option>
						<option value="dozent">dozent</option>
						<option value="admin">admin</option>
						<option value="root">root</option>
						</select>
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					<font size="-1">
						<input type="TEXT" maxlength="10" size="5" style="{font-size:8 pt; width: 30%; text-align: center;}" name="datafield_priority" />
					</font>
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center">
					&nbsp;
				</td>
				<td class="<?=$cssSw->getClass()?>" align="center" nowrap>
					&nbsp;&nbsp;<input type="IMAGE" name="send" src="<?= $GLOBALS['ASSETS_URL'] ?>images/haken_transparent.gif" border="0" <?=tooltip(_("Datenfeld speichern"))?> />
					&nbsp;<a href="<?=$PHP_SELF?>?cancel=TRUE"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/x_transparent.gif" border="0" <?=tooltip(_("Anlegen abbrechen"))?> />
				</td>
			</tr>
			<?
			}
			?>
		</table>
		<?
		if ($admin_datafields_data["create_datafield"] != "user") {
			?>&nbsp;<a href="<?=$PHP_SELF?>?create_new=user#a"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/add_right.gif" border="0" <?=tooltip(_("Neues Datenfeld für Nutzer anlegen"))?> /></a><?
		}
		?>
		<br />&nbsp;
		</td>
	</tr>
	</form>
</table>
<?
include ('lib/include/html_end.inc.php');
page_close();
?>