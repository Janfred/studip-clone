<?php
/**
* administer Fakultaeten
* 
* 
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup	admin_modules
* @module		admin_fakultaet
* @package		Admin
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_fakultaet.php
// 
// 
// Copyright (c) 2002 André Noack <noack@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("root");

require_once $ABSOLUTE_PATH_STUDIP."msg.inc.php"; 
require_once $ABSOLUTE_PATH_STUDIP."visual.inc.php";
require_once $ABSOLUTE_PATH_STUDIP."functions.php";
require_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES."/DbView.class.php";
require_once $ABSOLUTE_PATH_STUDIP."StudipRangeTree.class.php";

include $ABSOLUTE_PATH_STUDIP."seminar_open.php"; //initialize Stud.IP session
include $ABSOLUTE_PATH_STUDIP."html_head.inc.php"; //output of html head
include $ABSOLUTE_PATH_STUDIP."header.php";   // output of Stud.IP head
include $ABSOLUTE_PATH_STUDIP."links_admin.inc.php";  //links for admin area

//global variables
$_csw = new cssClassSwitcher();
$_view1 = new DbView();
$_view2 = new DbView();
$_msg = "";

//DB views
$_views["FAK_ALL_INFO"]= array("pk"=>"Fakultaets_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.*, count(b.Institut_id) AS anzahl FROM Fakultaeten a LEFT JOIN Institute b USING(fakultaets_id)
									GROUP BY a.Fakultaets_id ORDER BY ! !");
$_views["FAK_ONE_INFO"]= array("pk"=>"Fakultaets_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT a.*,count(b.Institut_id) AS anzahl FROM Fakultaeten a LEFT JOIN Institute b USING(fakultaets_id) 
									WHERE a.Fakultaets_id=? GROUP BY a.Fakultaets_id");
$_views["FAK_ADMINS"]= array("pk"=>"user_id","temp_table_type"=>"HEAP",
							"query"=>"SELECT " . $_fullname_sql['no_title'] . " AS fullname, c.username,a.user_id FROM fakultaet_user a LEFT JOIN 
									user_info b USING(user_id) LEFT JOIN auth_user_md5 c USING(user_id) WHERE a.status='admin' AND a.Fakultaets_id=?");
$_views["USER_SEARCH_ADMIN"] = array("query" => "SELECT " . $_fullname_sql['no_title'] . " AS fullname, a.username,a.user_id FROM auth_user_md5 a 
									LEFT JOIN user_info b USING(user_id) LEFT JOIN fakultaet_user c ON(a.user_id=c.user_id AND c.fakultaets_id=?) WHERE ISNULL(c.fakultaets_id) AND perms='admin' AND (Vorname LIKE ? OR Nachname LIKE ? OR username LIKE ?)");
$_views["FAK_INS"] = array("query" => "INSERT INTO Fakultaeten (Fakultaets_id,Name,mkdate,chdate) VALUES (?,?,UNIX_TIMESTAMP(),UNIX_TIMESTAMP())");
$_views["FAK_UPD"] = array("query" => "UPDATE Fakultaeten SET Name=?,chdate=UNIX_TIMESTAMP() WHERE Fakultaets_id=?");
$_views["FAK_DEL"] = array("query" => "DELETE FROM Fakultaeten WHERE Fakultaets_id=?");
$_views["FAK_USER_INS"] = array("query" => "INSERT INTO fakultaet_user (fakultaets_id,user_id,status) VALUES (?,?,'admin')");
$_views["FAK_USER_DEL"] = array("query" => "DELETE FROM fakultaet_user WHERE fakultaets_id LIKE ? AND user_id LIKE ?");

//handle submissions
if ($_REQUEST['cmd'] == "new" && $_REQUEST['new_fak'] != "" && $_REQUEST['new_fak'] != _("Bitte geben Sie hier einen Namen ein!")){
	$_view1->params = array($_view1->get_uniqid(),$_REQUEST['new_fak']);
	$rs = $_view1->get_query("view:FAK_INS");
	if ($rs->affected_rows()){
		$_msg = "msg§" . sprintf(_("Die Fakult&auml;t <b>%s</b> wurde angelegt!"),htmlReady(stripslashes($_REQUEST['new_fak'])));
	}
}
if ($_REQUEST['cmd'] == "search_admin" && isset($_REQUEST['do_add_admin_x']) && isset($_REQUEST['add_admin']) && isset($_REQUEST['fak_id'])){
	$_view1->params = array($_REQUEST['fak_id'],$_REQUEST['add_admin']);
	$rs = $_view1->get_query("view:FAK_USER_INS");
	if ($rs->affected_rows()){
		$_msg = "msg§" . sprintf(_("<b>%s</b> wurde zur Gruppe der Administratoren hinzugefügt!"),htmlReady(get_fullname($_REQUEST['add_admin'])));
	}
}
if ($_REQUEST['cmd'] == "alter_name" && isset($_REQUEST['do_alter_x']) && isset($_REQUEST['new_fak_name']) && isset($_REQUEST['fak_id'])){
	$_view1->params = array($_REQUEST['fak_id']);
	$rs = $_view1->get_query("view:FAK_ONE_INFO");
	$rs->next_record();
	$name = $rs->f("Name");
	if($name != $_REQUEST['new_fak_name']){
		$_view1->params = array($_REQUEST['new_fak_name'],$_REQUEST['fak_id'], );
		$rs = $_view1->get_query("view:FAK_UPD");
		$_msg = "msg§" . sprintf(_("<b>%s</b> in <b>%s</b> geändert!"),htmlReady($name),htmlReady(stripslashes($_REQUEST['new_fak_name'])));
	} else {
		$_REQUEST['cmd'] = "alter";
	}

}
if ($_REQUEST['cmd'] == "kill_admin" && isset($_REQUEST['admin_id']) && isset($_REQUEST['fak_id'])){
	$_view1->params = array($_REQUEST['fak_id'],$_REQUEST['admin_id']);
	$rs = $_view1->get_query("view:FAK_USER_DEL");
	if ($rs->affected_rows()){
		$_msg = "msg§" . sprintf(_("<b>%s</b> wurde aus der Gruppe der Administratoren entfernt!"),htmlReady(get_fullname($_REQUEST['admin_id'])));
	}
	$_REQUEST['cmd'] = "alter";
}
if ($_REQUEST['cmd'] == "kill_fak" && isset($_REQUEST['fak_id'])){
	$_view1->params = array($_REQUEST['fak_id']);
	$rs = $_view1->get_query("view:FAK_ONE_INFO");
	$rs->next_record();
	$name = $rs->f("Name");
	$_view1->params = array($_REQUEST['fak_id']);
	$rs = $_view1->get_query("view:FAK_DEL");
	if ($rs->affected_rows()){
		$_msg = "msg§" . sprintf(_("Die Fakultät <b>%s</b> wurde gelöscht§"),htmlReady($name));
	}
	$_view1->params = array($_REQUEST['fak_id'],"%%");
	$rs = $_view1->get_query("view:FAK_USER_DEL");
	if ($rs->affected_rows()){
		$_msg .= "msg§" . sprintf(_("<b>%s</b> Administrator(en) wurden gelöscht§"),$rs->affected_rows());
	}
	$_view1->params = array($_REQUEST['fak_id']);
	$rs = $_view1->get_query("view:TREE_ITEMS_OBJECT");
	while($rs->next_record()){
		$fak_items[] = $rs->f(0);
	}
	if (isset($_REQUEST['kill_tree_elements'])){
		$tree =& StudipRangeTree::GetInstance();
		$items_to_delete = $fak_items;
		for ($i = 0; $i < count($fak_items); ++$i){
			$items_to_delete = array_merge($items_to_delete,$tree->getKidsKids($fak_items[$i]));
		}
		if (count($items_to_delete)){
			$_view1->params[0] = $items_to_delete;
			$rs = $_view1->get_query("view:TREE_DEL_ITEM");
			if ($rs->affected_rows()){
				$_msg .= "msg§" . sprintf(_("<b>%s</b> Bereichsbaumelemente wurden gelöscht§"),$rs->affected_rows());
			}
			$_view1->params[0] = $items_to_delete;
			$rs = $_view1->get_query("view:CAT_DEL_RANGE");
			if ($rs->affected_rows()){
				$_msg .= "msg§" . sprintf(_("<b>%s</b> zugehörige Datenfelder wurden gelöscht§"),$rs->affected_rows());
			}
		}
	} else {
		for ($i = 0; $i < count($fak_items); ++$i){
			$_view1->params = array(mysql_escape_string($name . _(" (Element wurde in Stud.IP gelöscht)")),
									'','',$fak_items[$i]);
			$rs = $_view1->get_query("view:TREE_UPD_ITEM");
		}
		if ($i){
			$_msg .= "msg§" . sprintf(_("<b>%s</b> Bereichsbaumelemente wurden angepasst§"), $i);
		}
	}
}


?>
<table class="blank" cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr><td class="topic" align="left">
		&nbsp; <b>Verwaltung der Fakult&auml;ten</b>
	</td></tr>
<?
if ($_msg!=""){
	parse_msg($_msg,"§","blank",1,false);
}

if (($_REQUEST['cmd'] == "alter" || $_REQUEST['cmd'] == "search_admin") && isset($_REQUEST['fak_id'])){
?>
	<tr><td  align="center">
	<div align="left" style="width:90%">
	<p><b><u>
	Daten einer Fakult&auml;t &auml;ndern
	</u></b></p>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<form action="<?=$PHP_SELF?>?cmd=alter_name" method="post">
	<td align="left" class="steelgraulight" width="60%">
<?
	$_view1->params[0] = $_REQUEST['fak_id'];
	$rs = $_view1->get_query("view:FAK_ONE_INFO");
	$rs->next_record();
?>
	<p style="margin-left:10px"><br>
	<b><?=_("Name der Fakult&auml;t:")?></b><br>
	<input type="text" size="40" align="bottom" maxlength="255" style="width:100%" name="new_fak_name" value="<?=htmlReady($rs->f("Name"))?>">
	</p></td><td  align="left" valign="bottom" class="steelgraulight"><p>
	&nbsp;&nbsp;<input type="image" name="do_alter" <?=makeButton("uebernehmen","src") . tooltip(_("Neuen Namen übernehmen"))?> align="bottom" border="0">
	&nbsp;<input type="image" name="cancel_alter" <?=makeButton("abbrechen","src") . tooltip(_("Aktion abbrechen"))?> align="bottom" border="0">
	<input type="hidden" name="fak_id" value="<?=$_REQUEST['fak_id']?>">
	</p>
	</td></tr></form>

<?
	if (!$rs->f("anzahl")){
?>
	<form action="<?=$PHP_SELF?>?cmd=kill_fak" method="post">
	<tr><td  align="left" valign="top" class="steelgraulight" width="60%">
	<p style="margin-left:10px"><br>
	<b><?=_("Fakult&auml;t l&ouml;schen")?></b>
	&nbsp;&nbsp;&nbsp;<?=_("Zugeh&ouml;rige Eintr&auml;ge im Bereichsbaum l&ouml;schen")?>
	&nbsp;&nbsp;<input name="kill_tree_elements" type="checkbox">
	</p></td>
	<td  align="left" valign="bottom" class="steelgraulight">
	&nbsp;&nbsp;<input type="image" name="do_kill" <?=makeButton("loeschen","src") . tooltip(_("Fakultät löschen"))?> align="bottom" border="0">
	<input type="hidden" name="fak_id" value="<?=$_REQUEST['fak_id']?>">
	</td></tr>
	</form>
<?
	}
?>
	<tr><td  align="left" valign="top" class="steel1" width="60%">
	<p style="margin-left:10px"><br>
	<b><?=_("Administratoren:")?></b><br>
<?
	$_view1->params[0] = $_REQUEST['fak_id'];
	$rs = $_view1->get_query("view:FAK_ADMINS");
	while ($rs->next_record()){
		echo "\n<a href=\"$PHP_SELF?cmd=kill_admin&admin_id=" . $rs->f("user_id") . "&fak_id=" . $_REQUEST['fak_id'] ."\"><img src=\"pictures/trash.gif\" border=\"0\" "
			. tooltip(_("Zuordnung entfernen")) . " align=\"absbottom\" hspace=\"5\" ></a>"
			. htmlReady($rs->f("fullname") . " (" . $rs->f("username") . ")"). "<br>";
	}
	if (!$rs->num_rows()){
		echo "\nKeine Administratoren zugewiesen<br>";
	}
?>
	&nbsp;
	</p>
	</td><td align="left" valign="top" class="steel1">
	<form action="<?=$PHP_SELF?>?cmd=search_admin" method="post">
	<p style="margin-left:10px"><br>
	<b><?=_("Administrator hinzufügen:")?></b><br>
<?
	if (strlen($_REQUEST['search_name']) >= 2){
		$_view1->params = array($_REQUEST['fak_id'],"%".$_REQUEST['search_name']."%","%".$_REQUEST['search_name']."%","%".$_REQUEST['search_name']."%");
		$rs = $_view1->get_query("view:USER_SEARCH_ADMIN");
		if($treffer = $rs->num_rows()){
			echo "\n<input type=\"image\"  src=\"pictures/move_left.gif\" name=\"do_add_admin\" " . tooltip(_("Als Administrator hinzufügen")) . " align=\"baseline\" hspace=\"3\" border=\"0\">";
			echo "\n<select name=\"add_admin\" >";
			while ($rs->next_record()){
				echo "\n<option value=\"" . $rs->f("user_id"). "\">" . htmlReady($rs->f("fullname") . " (" . $rs->f("username") . ")"). "</option>";
			}
			echo "\n</select><input type=\"image\" src=\"pictures/rewind.gif\" name=\"search_reset\" " . tooltip(_("Neue Suche starten")) . " align=\"baseline\"  hspace=\"3\" border=\"0\">";
			echo "\n<br><span style=\"font-size:10pt\">" . $treffer . _(" Nutzer gefunden") . "</span>";
			echo "\n<input type=\"hidden\" name=\"fak_id\" value=\"" . $_REQUEST['fak_id'] . "\">";
	
		}
	}
	if (!$treffer || !$_REQUEST['search_name']) {
?>
	<input type="text" size="30" align="absmiddle" maxlength="255" style="width:60%" name="search_name" value="">
	&nbsp;<input type="image" src="pictures/suchen.gif"<?=tooltip(_("Administrator suchen"))?> align="absmiddle" border="0">
	<input type="hidden" name="fak_id" value="<?=$_REQUEST['fak_id']?>">
	<br><span style="font-size:10pt"><?=_("Geben Sie zur Suche den Vor-, Nach- oder Usernamen ein.")?>
	<?if($treffer === 0) echo "<br>" . _("Ihre Suche ergab keine Treffer");?>
	</span>
<?
	}
?>
	</p>
	</form>
	</td></tr>
	</table>
	</div>
	</td></tr>
<?
} else {
?>
	<tr><td  align="center">
	<div align="left" valign="center" style="width:90%">
	<p>
	<?=_("Auf dieser Seite k&ouml;nnen Sie die Fakult&auml;ten, die im System verwendet werden, verwalten. Sie m&uuml;ssen mindestens eine Fakult&auml;t angelegt haben, um Einrichtungen anlegen zu k&ouml;nnen.<br>
	<b>Achtung:</b> Das L&ouml;schen einer Fakult&auml;t ist nur m&ouml;glich, wenn keine Einrichtungen in dieser Fakult&auml;t existieren.")?>
	</p>
	<p>
	<form action="<?=$PHP_SELF?>?cmd=new" method="post">
	<b><?=_("Neue Fakult&auml;t anlegen:")?></b><br>
	<input type="text" align="absbottom" maxlength="255" style="width:75%" size="30" name="new_fak" value="<?=_("Bitte geben Sie hier einen Namen ein!")?>">
	&nbsp;<input type="image" <?=makeButton("anlegen","src") . tooltip(_("Neue Fakultät anlegen"))?> align="absbottom" border="0">
	</form>
	</p>
	<p>
	<b><?=_("Vorhandene Fakult&auml;ten:")?></b>
	</p>
	</div>
	</td></tr>
	<tr><td align="center">
	<table border="0" width="90%" cellpadding="2" cellspacing="0">
	<tr>
	<th width="60%">
	<a href="<?=$PHP_SELF?>?sortby=Name&dir=<?=(($_REQUEST['dir']=="ASC" || !$_REQUEST['dir']) ? "DESC" : "ASC")?>"<?=tooltip(_("Sortierreihenfolge ändern"),false)?>>
	<?=_("Name")?></a>
	</th>
	<th width="30%">
	<?=_("Admininstratoren")?>
	</th>
	<th width="10%">
	<a href="<?=$PHP_SELF?>?sortby=anzahl&dir=<?=(($_REQUEST['dir']=="ASC" || !$_REQUEST['dir']) ? "DESC" : "ASC")?>"<?=tooltip(_("Sortierreihenfolge ändern"),false)?>>
	<?=_("Einrichtungen")?></a>
	</th>
	</tr>
	<?
	$_view1->params = array("Name", "ASC");
	if ($_REQUEST['sortby']){
		$_view1->params[0] = $_REQUEST['sortby'];
	}
	if ($_REQUEST['dir']){
		$_view1->params[1] = $_REQUEST['dir'];
	}
	$rs1 = $_view1->get_query("view:FAK_ALL_INFO");
	while ($rs1->next_record()){
		echo "\n<tr><td " . $_csw->getFullClass() . " valign=\"center\"><a href=\"$PHP_SELF?cmd=alter&fak_id=" . $rs1->f("Fakultaets_id") .
		"\"" . tooltip(_("Hier klicken um Daten der Fakultät zu ändern"),true) . ">" . htmlReady($rs1->f("Name")) . "</a></td>";
		echo "\n<td " . $_csw->getFullClass() . " align=\"center\" valign=\"center\" style=\"font-size:10pt\">";
		$_view2->params[0] = $rs1->f("Fakultaets_id");
		$rs2 = $_view2->get_query("view:FAK_ADMINS");
		while ($rs2->next_record()){
			echo "<a style=\"font-size:10pt\" href=\"about.php?username=" . $rs2->f("username") . "\">" . htmlReady($rs2->f("fullname")) . 
			" (" . $rs2->f("username") . ")</a><br>";
		}
		if (!$rs2->num_rows()){
			echo "<span style=\"font-size:10pt\">" . _("kein Eintrag") . "</span>";
		}
		echo "</td>\n<td " . $_csw->getFullClass() . " align=\"center\" valign=\"center\">" . $rs1->f("anzahl") . "</td></tr>";
		$_csw->switchClass();
	}
	if (!$rs1->num_rows()){
		echo "\n<tr><td " . $_csw->getFullClass() . " colspan=\"3\" align=\"center\">" . _("Keine Fakult&auml;ten vorhanden") . "</td></tr>";
	}
?>
		</table>
	</td></tr>
<?
}
?>
<tr><td>&nbsp;</td></tr>
</table>
</body>
</html>
<?
page_close();
?>
