<?
# Lifter002: 
/**
* admin_log.php
*
* backend for administration of logging mechanism
*
*
* @author		Tobias Thelen <tthelen@uni-osnabrueck.de>
* @version		$Id$
* @access		public
* @module		admin_log.php
* @modulegroup		admin
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_log.php
//
// Copyright (C) 2006 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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

if (!$LOG_ENABLE) {
        print '<p>' . _("Log-Modul abgeschaltet."). '</p>';
	include ('lib/include/html_end.inc.php');
        page_close();
        die;
}

include ('lib/seminar_open.php'); // initialise Stud.IP-Session
require_once('lib/msg.inc.php');	//messages
require_once('config.inc.php');	//Settings....
require_once 'lib/functions.php';	//whatever ;)
require_once('lib/visual.inc.php');	//visuals
require_once('lib/classes/Config.class.php');	//Acces to config-values
require_once('lib/classes/UserConfig.class.php');	//Acces to userconfig-values

$cssSw=new cssClassSwitcher;
//$sess->register("admin_config_data");
//$admin_config_data["range_id"] = '';

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');	//hier wird das Reiter- und Suchsystem des Adminbereichs eingebunden
include ('lib/classes/Table.class.php');
include ('lib/classes/ZebraTable.class.php');

class LogAction {
	function LogAction($record) {
		$this->action_id=$record["action_id"];
		$this->name=$record["name"];
		$this->description=stripslashes($record["description"]);
		$this->info_template=stripslashes($record["info_template"]);
		$this->active=$record["active"];
		$this->expires=$record["expires"];
		$this->count=-1;
	}

	function count() {
		//if ($this->count!=-1) return $this->count;
		$db=new DB_Seminar();
		$sql="SELECT COUNT(*) as count FROM log_events WHERE action_id='$this->action_id'";
		$db->query($sql);
		$db->next_record();
		$this->count=$db->f("count");
		//if ($this->count==0) return "0";
		return $this->count;
	}

}

function change_action() {
	$msg="";
	if (!$_REQUEST['action_id']) {
		$msg.="error§"._("Keine ID übergegeben.")."§";
	}
	// names can't be changed
	//if (!$_REQUEST['name']) {
	//	$msg.="error§"._("Kein Name angegeben.")."§";
	//}
	if (!$_REQUEST['description']) {
		$msg.="error§"._("Keine Beschreibung angegeben.")."§";
	}
	if (!$_REQUEST['info_template']) {
			$msg.="error§"._("Kein Info-Template angegeben.")."§";
	}
	if ($_REQUEST['active']) {
		$active="1";
	} else {
		$active="0";
	}
	if ($msg) return $msg;
	$db=new DB_Seminar();
	$sql="UPDATE log_actions SET description='".addslashes($_REQUEST['description'])."', info_template='".addslashes($_REQUEST['info_template'])."', active='".$active."' WHERE action_id='".$_REQUEST['action_id']."'";
	$db->query($sql);
	$msg="msg§"._("Eintrag geändert")."§";
	return $msg;
}


function get_actions($order="name") {
	$db=new DB_Seminar();
	$sql="SELECT * FROM log_actions ORDER BY $order";
	$db->query($sql);
	$actions=array();
	while ($db->next_record()) {
		$actions[]=new LogAction($db->Record);
	}
	return $actions;
}

function show_list() {
	print "<form action=\"$PHP_SELF\" method=\"post\">";
	print "<input type=\"hidden\" name=\"action\" value=\"change\">";
	$actions=get_actions();
	$listtable=new ZebraTable(array("width"=>"99%","padding"=>"4", "align"=>"center"));
	print $listtable->openHeaderRow();
	print $listtable->cell("<font size=-1><b>"._("Name")."</b></font>");
	print $listtable->cell("<font size=-1><b>"._("Beschreibung")."</b></font>");
	print $listtable->cell("<font size=-1><b>"._("Template")."</b></font>");
	print $listtable->cell("<font size=-1><b>"._("Anzahl")."</b></font>");
	print $listtable->cell("<font size=-1><b>"._("Aktiv?")."</b></font>");
	// Ablaufzeit noch nicht implementiert
	// print $listtable->cell("<font size=-1><b>"._("Ablaufzeit")."</b></font>");
	print $listtable->cell("<font size=-1><b>"."&nbsp;"."</b></font>");
	print $listtable->closeRow();
	foreach ($actions as $a) {
		if ($_REQUEST['action']=="edit" && $_REQUEST['action_id']==$a->action_id) {
			print $listtable->openRow();
			print $listtable->cell("<a name=\"edit\"></a><input type=\"hidden\" name=\"action_id\" value=\"$a->action_id\"><font size=-1><b>".$a->name."</b></font>");
			print $listtable->cell("<font size=-1><input name=\"description\" value=\"$a->description\" size=35></font>");
			print $listtable->cell("<font size=-1><textarea rows=2 cols=30 name=\"info_template\">$a->info_template</textarea></font>");
			print $listtable->cell("<font size=-1>".$a->count()."</font>");
			print $listtable->cell("<input type=\"checkbox\" name=\"active\" ".($a->active ? "checked" : "").">");
			// Ablaufzeit noch nicht implementiert
			//print $listtable->cell("<input name=\"expires\" size=2 value=\"$a->expires\"><select name=\"expires_unit\"><option value=m>"._("Minuten")."</option><option value=h>"._("Stunden")."</option><option value=d>"._("Tage")."</option></select>");
			print $listtable->cell("<input type=image src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\" alt="._("Ändern").">");
			print $listtable->closeRow();
		} else {
			print $listtable->openRow();
			print $listtable->cell("<font size=-1>".$a->name."</font>");
			print $listtable->cell("<font size=-1>".$a->description."</font>");
			print $listtable->cell("<font size=-1>".$a->info_template."</font>");
			print $listtable->cell("<font size=-1>".$a->count()."</font>");
			if ($a->active) {
				print $listtable->cell("<img src=\"".$GLOBALS['ASSETS_URL']."images/haken_transparent.gif\">");
			} else {
				print $listtable->cell("<img src=\"".$GLOBALS['ASSETS_URL']."images/x_transparent.gif\">");
			}
			/*
			// Ablaufzeit noch nicht implementiert
			if ($a->expires) {
				print $listtable->cell("<font size=-1>".$a->expires." s"."</font>");
			} else {
				print $listtable->cell("<img src=\"".$GLOBALS['ASSETS_URL']."images/x_transparent.gif\">");
			}
			*/
			print $listtable->cell("<a href=\"$PHP_SELF?action=edit&action_id=".$a->action_id."#edit\"><img src=\"".$GLOBALS['ASSETS_URL']."images/edit_transparent.gif\" border=0></a>");
			print $listtable->closeRow();
		}
	}
	$listtable->close();
	print "</form>";
}



// handle action

if ($_REQUEST['action']=="change") {
	$msg=change_action();
}

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="topic" colspan=2>&nbsp; <b>
		<?=_("Konfiguration der Logging-Funktionen");
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
			<b><?=_("Logging") ?></b><br /><br />
			<?=_("Sie k&ouml;nnen hier einen Teil der Logging-Funktionen direkt ver&auml;ndern.")?> <br />
			</blockqoute>
		</td>
		<td class="blank" align="right" valign="top"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="10" width="5" /><br />
			<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/modules.jpg" border="0"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/blank.gif" height="10" width="10" />
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		<?
		show_list();
		?>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
			<table width="99%" border=0 cellpadding=0 cellspacing=3>
			</table>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		&nbsp;
		</td>
	</tr>
</table>
<?php
include ('lib/include/html_end.inc.php');
page_close();
?>