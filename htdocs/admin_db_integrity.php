<?php
/**
* Frontend for the db integrity checks
* 
* 
*
* @author		André Noack <andre.noack@gmx.net>
* @version		$Id$
* @access		public
* @modulegroup	admin_modules
* @module		admin_db_integrity
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
// admin_db_integrity.php
// Integrity checks for the Stud.IP database
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

include $ABSOLUTE_PATH_STUDIP."seminar_open.php"; //hier werden die sessions initialisiert
include $ABSOLUTE_PATH_STUDIP."html_head.inc.php";
include $ABSOLUTE_PATH_STUDIP."header.php";   //hier wird der "Kopf" nachgeladen 
include $ABSOLUTE_PATH_STUDIP."links_admin.inc.php";  //Linkleiste fuer admins

//global variables
$_integrity_plugins = array("User","Seminar","Institut","Fakultaet","Archiv","Studiengang","Fach","Bereich","Termin");
$_csw = new cssClassSwitcher();

?>
<table class="blank" cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr><td class="topic" align="left">&nbsp; <b>Datenbank Integrit&auml;t pr&uuml;fen</b></td></tr>
	<tr><td  align="center">
		
<?
//check, if a plugin is activated
if($_REQUEST['plugin'] AND in_array($_REQUEST['plugin'],$_integrity_plugins)){
	
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES."/IntegrityCheck".$_REQUEST['plugin'].".class.php";
	$plugin_name = "IntegrityCheck".$_REQUEST['plugin'];
	$plugin_obj = new $plugin_name;
	
	//query the user, if he really wants to delete
	if($_REQUEST['cmd'] == "assure" AND isset($_REQUEST['checkid'])){
		$result = $plugin_obj->doCheck($_REQUEST['checkid']);
		$anzahl = $result->num_rows();
		$msg = "info§Sie beabsichtigen $anzahl Datens&auml;tze der Tabelle <b>".$plugin_obj->getCheckDetailTable($_REQUEST['checkid'])."</b> zu l&ouml;schen.<br>"
		."Dieser Schritt kann <u>nicht</u> r&uuml;ckg&auml;ngig gemacht werden! Sind sie sicher ? <br />\n"
		."<br><a href=\"$PHP_SELF?plugin={$_REQUEST['plugin']}&cmd=delete&checkid={$_REQUEST['checkid']}\"><img src=\"pictures/buttons/ja2-button.gif\" border=0></a>&nbsp;"
		."<a href=\"$PHP_SELF\"><img src=\"pictures/buttons/nein-button.gif\" border=0></a>\n";
		?><table border="0" width="80%" cellpadding="2" cellspacing="0" class="steel1">
		<tr><td class="blank">&nbsp; </td></tr>
		<?
		parse_msg($msg,"§","steel1",1,FALSE);
		?>
		<tr><td class="blank">&nbsp; </td></tr>
		</table><?
	
	//delete the rows in the according table
	} elseif($_REQUEST['cmd'] == "delete" AND isset($_REQUEST['checkid'])){
		$result = $plugin_obj->doCheckDelete($_REQUEST['checkid']);
		if ($result === false){
			$msg = "error§Beim L&ouml;schen der Datens&auml;tze trat ein Fehler auf!";
		} else {
			$msg = "msg§Es wurden $result Datens&auml;tze der Tabelle <b>".$plugin_obj->getCheckDetailTable($_REQUEST['checkid'])."</b> gelöscht!";
		}
		unset($_REQUEST['plugin']);
	
	//show the found rows in the according table
	} elseif($_REQUEST['cmd'] == "show" AND isset($_REQUEST['checkid'])){
		?>
		<table border="0" width="80%" cellpadding="2" cellspacing="2">
		<tr><td class="blank" colspan="2">&nbsp; </td></tr>
		<tr><td class="blank"><b>Bereich: <i><?=$_REQUEST['plugin']?></i> Datens&auml;tze der Tabelle <?=$plugin_obj->getCheckDetailTable($_REQUEST['checkid'])?></b></td>
		<td class="blank" align="center"><a href="<?="$PHP_SELF?plugin={$_REQUEST['plugin']}&cmd=assure&checkid={$_REQUEST['checkid']}"?>"><img src="pictures/buttons/loeschen-button.gif" border="0"></a>
		<a href="<?=$PHP_SELF?>"><img src="pictures/buttons/abbrechen-button.gif" border="0"></a></td></tr>
		<tr><td class="blank" colspan="2">&nbsp; </td></tr>
		<tr><td class="steel1" align="center" colspan="2">
		<?
		$db = $plugin_obj->getCheckDetailResult($_REQUEST['checkid']);
		?><table border=1 class="steelgraulight" style="font-size:smaller" align="center"><tr><?
		$meta = $db->metadata();
		for($i = 0;$i < count($meta);++$i){ 
			echo "<th>" . $meta[$i]['name'] . "</th>";
		}
		echo "</tr>";
		while ($db->next_record()){
			echo"<tr>";
			for($i = 0;$i < count($meta);++$i){ 
				echo "<td>&nbsp;".htmlReady(substr($db->f($i),0,50))."</td>";
				}
			echo"</tr>";
		}
		?></table></td></tr>
		<tr><td class="blank" colspan="2">&nbsp; </td></tr>
		</table><?
	
	//no command is given, do all checks of the activated plugin
	} else {
		?>
		<table border="0" width="80%" cellpadding="2" cellspacing="0">
		<tr><td class="blank" colspan="3">&nbsp; </td></tr>
		<tr><td class="blank" colspan="2"><b>Bereich: <i><?=$_REQUEST['plugin']?></i> der Datenbank wird gepr&uuml;ft!</b></td>
		<td class="blank" align="center"><a href="<?=$PHP_SELF?>"><img src="pictures/buttons/abbrechen-button.gif" border="0"></a></td> </tr>
		<tr><td class="blank" colspan="3">&nbsp; </td></tr>
		<tr><th width="20%">Tabelle</th><th width="60%">Ergebnis</th><th width="20%">Aktion</th></tr>
		<?
		for($i=0; $i < $plugin_obj->getCheckCount(); ++$i){
			echo "\n<tr><td ".$_csw->getFullClass().">".$plugin_obj->getCheckDetailTable($i)."</td>";
			echo "\n<td ".$_csw->getFullClass().">";
			$result = $plugin_obj->doCheck($i);
			$anzahl = $result->num_rows();
			echo "\n$anzahl Datensätze gefunden</td>";
			echo "\n<td ".$_csw->getFullClass().">";
			echo ($anzahl==0) ? "&nbsp;" : "<a href=\"{$PHP_SELF}?plugin={$_REQUEST['plugin']}&cmd=show&checkid={$i}\">"
				."<img src=\"pictures/buttons/anzeigen-button.gif\" border=\"0\" align=\"middle\"></a>&nbsp;"
				."<a href=\"{$PHP_SELF}?plugin={$_REQUEST['plugin']}&cmd=assure&checkid={$i}\">"
				."<img src=\"pictures/buttons/loeschen-button.gif\" border=\"0\" align=\"middle\"></a></td></tr>";
			$_csw->switchClass();
		}
		?><tr><td colspan="3">&nbsp;</td></tr></table><?
	}
}

//show all available plugins
if(!$_REQUEST['plugin']){
	for($i=0; $i < count($_integrity_plugins); ++$i){
		include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_ADMIN_MODULES."/IntegrityCheck".$_integrity_plugins[$i].".class.php";
	}
	?>
	<table border="0" width="80%" cellpadding="2" cellspacing="0">
	<?if ($msg) {
		echo "<tr><td class=\"blank\" colspan=\"4\">&nbsp; </td></tr>";
		parse_msg($msg,"§","blank", 4, FALSE);
	}
	?>
	<tr><td class="blank" colspan="4">&nbsp; </td></tr>
	<tr><td class="blank" colspan="4"><b>Folgende Bereiche der Datenbank k&ouml;nnen gepr&uuml;ft werden:</b><br />&nbsp; </td></tr>
	<tr><th width="20%">Bereich</th><th width="60%">Beschreibung</th><th width="10%">Anzahl</th><th width="10%">Aktion</th></tr>
	<?
	for($i=0; $i < count($_integrity_plugins); ++$i){
		$plugin_name = "IntegrityCheck".$_integrity_plugins[$i];
		$plugin_obj = new $plugin_name;
		echo "\n<tr><td ".$_csw->getFullClass().">&nbsp; ".$_integrity_plugins[$i]."</td>";
		echo "\n<td ".$_csw->getFullClass()." style=\"font-size:smaller\">Testet Tabelle: <b>".$plugin_obj->getCheckMasterTable()
			."</b> gegen <i>".join(", ",$plugin_obj->getCheckDetailList())."</i></td>";
		echo "\n<td align=\"center\" ".$_csw->getFullClass().">".$plugin_obj->getCheckCount()."</td>";
		echo "\n<td align=\"center\" ".$_csw->getFullClass()."><a href=$PHP_SELF?plugin=".$_integrity_plugins[$i]
			."><img src=\"pictures/buttons/jetzttesten-button.gif\" border=\"0\" align=\"middle\" hspace=\"10\" vspace=\"10\"></a></td></tr>";
		$_csw->switchClass();
	}
	?><tr><td colspan="3">&nbsp;</td></tr></table><?
}
page_close();
?>
</td></tr></table></body></html>
<!--$Id$-->
