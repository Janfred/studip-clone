<?
/**
* admin_modules.php
* 
* switch the modules (defines in Modules.class.php) on/off for Institutes or Veranstaltungen
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @module		admin_modules.php
* @modulegroup		admin
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_modules.php
// Module fuer Veranstaltungen und Einrichtungen (definiert in Modules.class.php) an/abschalten
// Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

$perm->check("tutor");

include ($ABSOLUTE_PATH_STUDIP."seminar_open.php"); // initialise Stud.IP-Session
require_once($ABSOLUTE_PATH_STUDIP."msg.inc.php");	//Ausgaben
require_once($ABSOLUTE_PATH_STUDIP."config.inc.php");	//Settings....
require_once($ABSOLUTE_PATH_STUDIP."functions.php");	//basale Funktionen
require_once($ABSOLUTE_PATH_STUDIP."visual.inc.php");	//Darstellungsfunktionen
require_once($ABSOLUTE_PATH_STUDIP."messaging.inc.php");	//Nachrichtenfunktionen
require_once($ABSOLUTE_PATH_STUDIP."lib/classes/AdminModules.class.php");	//Nachrichtenfunktionen

// Start of Output
include ($ABSOLUTE_PATH_STUDIP."html_head.inc.php"); // Output of html head
include ($ABSOLUTE_PATH_STUDIP."header.php");   // Output of Stud.IP head
include ($ABSOLUTE_PATH_STUDIP."links_admin.inc.php");	//hier wird das Reiter- und Suchsystem des Adminbereichs eingebunden


$db=new DB_Seminar;
$db2=new DB_Seminar;
$cssSw=new cssClassSwitcher;
$sess->register("admin_modules_data");
$messaging=new messaging;
$amodules=new AdminModules;

//get ID
if ($SessSemName[1])
	$range_id=$SessSemName[1]; 
	
//wenn wir frisch reinkommen, werden benoetigte Daten eingelesen
if (($range_id) && (!$uebernehmen_x) && (!$delete_forum) && (!$delete_documents)) {
	$admin_modules_data["modules_list"] = $amodules->getLocalModules($range_id);
	$admin_modules_data["orig_bin"] = $amodules->getBin($range_id);
	$admin_modules_data["changed_bin"] = $amodules->getBin($range_id);
	$admin_modules_data["range_id"] = $range_id;
} else {
	//Sicherheitscheck ob &uuml;berhaupt was zum Bearbeiten gewaehlt ist.
	if (!$admin_modules_data["range_id"]) {
		echo "</tr></td></table>";
		die;
	}
}

if ($perm->have_studip_perm("tutor", $admin_modules_data["range_id"])) {
	//Sicherheitscheck ob ueberhaupt was zum Bearbeiten gewaehlt ist.

	if (!$admin_modules_data["range_id"]){
		echo "</tr></td></table>";
		die;
	}
	
	if ($uebernehmen_x) {
		foreach ($amodules->registered_modules as $key => $val) {
			$tmp_key = $key."_value";
			if ($$tmp_key == "TRUE")
				$$tmp_key = TRUE;
			else
				$$tmp_key = FALSE;
	
			if ($$tmp_key)
				$amodules->setBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
			else
				$amodules->clearBit($admin_modules_data["changed_bin"], $amodules->registered_modules[$key]["id"]);
			
		}
		//checks
		$msg='';
		//check for forum
		if (($amodules->isBit($admin_modules_data["orig_bin"],  $amodules->registered_modules["forum"]["id"])) &&
			(!$amodules->isBit($admin_modules_data["changed_bin"],  $amodules->registered_modules["forum"]["id"])) &&
			($amodules->getModuleForumExistingItems($admin_modules_data["range_id"]))) {
			$msg.="info§"._("Wollen Sie wirklich das Forum deaktivieren und damit alle Diskussionbeitr&auml;ge l&ouml;schen?");
			$msg.="<br /><a href=\"".$PHP_SELF."?delete_forum=TRUE\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
			$msg.="<a href=\"".$PHP_SELF."?cancel=TRUE\">" . makeButton("nein", "img") . "</a>\n§";
			$dont_save = TRUE;
		}
		
		//check for documents
		if (($amodules->isBit($admin_modules_data["orig_bin"],  $amodules->registered_modules["documents"]["id"])) &&
			(!$amodules->isBit($admin_modules_data["changed_bin"],  $amodules->registered_modules["documents"]["id"])) &&
			($amodules->getModuleDocumentsExistingItems($range_id))) {
			$msg.="info§"._("Wollen Sie wirklich den Dateiordner deaktivieren und damit alle Dateien l&ouml;schen?");
			$msg.="<br /><a href=\"".$PHP_SELF."?delete_documents=TRUE\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
			$msg.="<a href=\"".$PHP_SELF."?cancel=TRUE\">" . makeButton("nein", "img") . "</a>\n§";
			$dont_save = TRUE;
		}

		if (!$dont_save) {
			$amodules->writeBin($admin_modules_data["range_id"], $admin_modules_data["changed_bin"]);
			$admin_modules_data["orig_bin"] = $admin_modules_data["changed_bin"];
			$admin_modules_data["modules_list"] = $amodules->getLocalModules($admin_modules_data["range_id"]);
			$msg.= "msg§Die ver&auml;nderte Modulkonfiguration wurde &uuml;bernommen";
		}
	}
	
	if ($delete_forum) {
		$amodules->moduleForumDeactivate($admin_modules_data["range_id"]);
		$amodules->writeStatus("forum", $admin_modules_data["range_id"], FALSE);
		$admin_modules_data["modules_list"] = $amodules->getLocalModules($admin_modules_data["range_id"]);
		$admin_modules_data["orig_bin"] = $amodules->getBin($admin_modules_data["range_id"]);
	}

	if ($delete_documents) {
		$amodules->moduleDocumentsDeactivate($admin_modules_data["range_id"]);
		$amodules->writeStatus("documents", $admin_modules_data["range_id"], FALSE);
		$admin_modules_data["modules_list"] = $amodules->getLocalModules($admin_modules_data["range_id"]);
		$admin_modules_data["orig_bin"] = $amodules->getBin($admin_modules_data["range_id"]);
	}
}

if ($admin_modules_data["range_id"]) {

?>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp; <b>
		<?
		echo getHeaderLine($admin_modules_data["range_id"])." -  "._("Module konfigurieren");
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
				<?parse_msg($msg);?>
				</table>
			<? } ?>
			<br />
			<blockquote>
			<b><?=_("Module konfigurieren") ?></b><br /><br />
			<?=_("Sie k&ouml;nnen hier einzelne Module nachtr&auml;glich aktivieren oder deaktivieren.")?> <br /><br />
			</blockqoute>
		</td>
		<td class="blank" align="right" valign="top"><img src="pictures/blank.gif" height="10" width="5" /><br />
			<img src="pictures/modules.jpg" border="0"><img src="pictures/blank.gif" height="10" width="10" />
		</td>		
	</tr>
	<tr>
	<td class="blank" colspan=2>
	<form method="POST" name="modules" action="<? echo $PHP_SELF ?>">
		<table width="99%" border=0 cellpadding=2 cellspacing=0 align="center">
		<tr <? $cssSw->switchClass() ?>>
			<td class="<? echo $cssSw->getClass() ?>" align="center" colspan="4">		
				<input type="IMAGE" name="uebernehmen" <?=makeButton("uebernehmen", "src")?> border=0 value="uebernehmen">
				<? if ($admin_modules_data["orig_bin"] != $admin_modules_data["changed_bin"]) {
					?> <br /><img src="pictures/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><?=_("Diese Daten sind noch nicht gespeichert.")?></font><br /> <?
					}
				?>
			</td>
		</tr>
		<? if ($amodules->isEnableable("forum", $admin_modules_data["range_id"])) { ?>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  width="10%" align="left">
				<font size=-1><b><?=_("Forum:")?></b><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="16%">
				<input type="RADIO" name="forum_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 0)) ? "checked" : "" ?>>
				<font size=-1><?=_("an")?></font>
				<input type="RADIO" name="forum_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 0)) ? "" : "checked" ?>>
				<font size=-1><?=_("aus")?><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="70%">
				<font size=-1><?
				if ($amodules->getModuleForumExistingItems($admin_modules_data["range_id"]))
					print ($admin_modules_data["modules_list"]["forum"]) ? sprintf("<font color=\"red\">"._("Achtung: Beim Deaktivieren des Forums werden <b>%s</b> Postings ebenfalls gel&ouml;scht!")."</font>", $amodules->getModuleForumExistingItems($admin_modules_data["range_id"])) : _("Das Forum kann jederzeit aktiviert werden.");
				else
					print ($admin_modules_data["modules_list"]["forum"]) ? _("Das Forum kann jederzeit deaktiviert werden.") : _("Das Forum kann jederzeit aktiviert werden.");
				?></font>
			</td>
		</tr>
		<? }
		if ($amodules->isEnableable("documents", $admin_modules_data["range_id"])) { ?>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  align="left">
				<font size=-1><b><?=_("Dateien:")?></b><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="16%">
				<input type="RADIO" name="documents_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 1)) ? "checked" : "" ?>>
				<font size=-1><?=_("an")?></font>
				<input type="RADIO" name="documents_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 1)) ? "" : "checked" ?>>
				<font size=-1><?=_("aus")?><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="70%">
				<font size=-1><?
				if ($amodules->getModuleDocumentsExistingItems($admin_modules_data["range_id"]))
					print ($admin_modules_data["modules_list"]["documents"]) ? sprintf("<font color=\"red\">"._("Achtung: Beim Deaktivieren des Dateiordners werden <b>%s</b> Dateien ebenfalls gel&ouml;scht!")."</font>", $amodules->getModuleDocumentsExistingItems($admin_modules_data["range_id"])) : _("Das Forum jederzeit aktiviert werden.");
				else
					print ($admin_modules_data["modules_list"]["documents"]) ? _("Der Dateiordner kann jederzeit deaktiviert werden.") : _("Der Dateiordner kann jederzeit aktiviert werden.");
				?></font>
			</td>
		</tr>
		<? }
		if ($amodules->isEnableable("ilias_connect", $admin_modules_data["range_id"])) { ?>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>"  align="left">
				<font size=-1><b><?=_("Lernmodule:")?></b><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="16%">
				<input type="RADIO" name="ilias-connect_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 2)) ? "checked" : "" ?>>
				<font size=-1><?=_("an")?></font>
				<input type="RADIO" name="ilias-connect_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 2)) ? "" : "checked" ?>>
				<font size=-1><?=_("aus")?><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="70%">
				<font size=-1><?=($admin_modules_data["modules_list"]) ? _("Die Anbindung zu Ilias Lernmodulen kann jederzeit deaktiviert werden.") : _("Die Anbindung zu Ilias Lernmodulen  kann jederzeit aktiviert werden.")?></font>
			</td>
		</tr>
		<? }
		if ($amodules->isEnableable("chat", $admin_modules_data["range_id"])) { ?>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>" align="left">
				<font size=-1><b><?=_("Chat:")?></b><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="16%">
				<input type="RADIO" name="chat_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 3)) ? "checked" : "" ?>>
				<font size=-1><?=_("an")?></font>
				<input type="RADIO" name="chat_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 3)) ? "" : "checked" ?>>
				<font size=-1><?=_("aus")?><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="70%">
				<font size=-1><?=($admin_modules_data["modules_list"]) ? _("Der Chat kann jederzeit deaktiviert werden.") : _("Der Chat kann jederzeit aktiviert werden.")?></font>
			</td>
		</tr>
		<? }
		if ($amodules->isEnableable("support", $admin_modules_data["range_id"])) { ?>
		<tr <? $cssSw->switchClass() ?> rowspan=2>
			<td class="<? echo $cssSw->getClass() ?>" width="4%" align="right">
				&nbsp;
			</td>
			<td class="<? echo $cssSw->getClass() ?>" align="left">
				<font size=-1><b><?=_("SupportDB:")?></b><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="16%">
				<input type="RADIO" name="support_value" value="TRUE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 4)) ? "checked" : "" ?>>
				<font size=-1><?=_("an")?></font>
				<input type="RADIO" name="support_value" value="FALSE" <?=($amodules->isBit($admin_modules_data["changed_bin"], 4)) ? "" : "checked" ?>>
				<font size=-1><?=_("aus")?><br /></font>
			</td>
			<td class="<? echo $cssSw->getClass() ?>" width="70%">
				<font size=-1><?=($admin_modules_data["modules_list"]) ? _("Die SupportDB kann jederzeit deaktiviert werden.") : _("Die SupportDB kann jederzeit aktiviert werden.")?></font>
			</td>
		</tr>
		<? } ?>
		<tr>
			<td class="blank" colspan=3>&nbsp; 
			</td>
		</tr>
		<?
	page_close();		
	}
else
	die;

?>
	</table>
</td>
</tr>
</table>
</body>
</html>
