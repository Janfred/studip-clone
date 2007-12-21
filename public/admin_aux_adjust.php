<?
/**
* admin_aux_lock.php - Sichtbarkeits-Administration von Stud.IP.
* Copyright (C) 2006 Till Glöggler <tgloeggl@inspace.de>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/
define ('CHECKED', ' checked="checked"');

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$auth->login_if($auth->auth["uid"] == "nobody");
$perm->check("admin");
include ("lib/seminar_open.php"); // initialise Stud.IP-Session

require_once('lib/classes/Table.class.php');
require_once('lib/classes/ZebraTable.class.php');
require_once('lib/classes/AuxLockRules.class.php');
require_once('lib/classes/DataFieldEntry.class.php');

//Output starts here

include ('lib/include/html_head.inc.php'); // Output of html head
$CURRENT_PAGE = _("Verwaltung der Regeln für Zusatzangaben");

//prebuild navi and the object switcher (important to do already here and to use ob!)
ob_start();
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins
$links = ob_get_clean();

//get ID from a open Seminar
if ($SessSemName[1])
	$header_object_id = $SessSemName[1];

//Change header_line if open object
$header_line = getHeaderLine($header_object_id);
if ($header_object_id)
	$CURRENT_PAGE = $header_line." - ".$CURRENT_PAGE;

include ('lib/include/header.php');   //hier wird der "Kopf" nachgeladen
echo $links;

$sem_id = $SessionSemName[1];

function mainView() {
	global $zt;

  $link = htmlspecialchars($GLOBALS['PHP_SELF']);

	echo $zt->openRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('<a href="'.$link.'?cmd=new_rule">'. _("Neue Regel anlegen") .'</a><br/><br/>', array('colspan' => '20', 'class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openHeaderRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('&nbsp;<b>Name</b>');
	echo $zt->cell('&nbsp;<b>Beschreibung</b>');
	echo $zt->cell('&nbsp;');
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->closeRow();

	$rules = AuxLockRules::getAllLockRules();

	foreach ((array)$rules as $id => $data) {
		echo $zt->openRow();
		echo $zt->cell('&nbsp;', array('class' => 'blank'));
		echo $zt->cell('&nbsp;'.$data['name']);
		echo $zt->cell('&nbsp;'.$data['description']);
		echo $zt->cell('<a href="'.$link.'?cmd=edit&id='.$id.'">'. makebutton('bearbeiten').'</a>&nbsp;&nbsp;&nbsp;&nbsp;'.
		               '<a href="'.$link.'?cmd=delete&id='.$id.'">'. makebutton('loeschen').'</a>', array('width' => '30%', 'align' => 'center'));
		echo $zt->cell('&nbsp;', array('class' => 'blank'));
		echo $zt->closeRow();
	}

	echo $zt->openRow();
	echo $zt->cell('<br/>', array('colspan' => '20', 'class' => 'blank'));
	echo $zt->close();
}

function ruleView($state = 'change', $id = '') {
	global $_REQUEST, $zt, $sem_id, $user;
	if ($state == 'edit') {
		$rule = AuxLockRules::getLockRuleByID($id);
		$title = $rule['name']. _("ändern");
	} else {
		$title = _("Neue Regel definieren");
	}

	echo '<form action="'.$PHP_SELF.'" method="post">';

	echo $zt->openRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('<b>'.$title.'</b>', array('colspan' => '20', 'class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('&nbsp;'. _("Name:"), array('width' => '80%'));
	if ($state == 'edit') {
		echo $zt->cell('<input type="text" name="name" value="'.$rule['name'].'">', array('colspan' => '3'));
	} else {
		echo $zt->cell('<input type="text" name="name">', array('colspan' => '3'));
	}
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('&nbsp;'. _("Beschreibung:"));
	if ($state == 'edit') {
		echo $zt->cell('<textarea name="description" cols="40" rows="4">'.$rule['description'].'</textarea>', array('colspan' => '3'));
	} else {
		echo $zt->cell('<textarea name="description" cols="40" rows="4"></textarea>', array('colspan' => '3'));
	}
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openHeaderRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('&nbsp;<b>'. _("Feld") .'</b>');
	echo $zt->cell('<b>'. _("Sortierung") .'</b>', array('width' => '10%'));
	echo $zt->cell('&nbsp;&nbsp;<b>'. _("nicht aktivieren") .'</b>', array('width' => '10%'));
	echo $zt->cell('&nbsp;&nbsp;<b>'. _("aktivieren") .'</b>', array('width' => '10%'));
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openHeaderRow();
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->cell('&nbsp;<b>'. _("Veranstaltungsinformationen") .'</b>', array('colspan' => '4', 'class' => 'steelgraudunkel'));
	echo $zt->cell('&nbsp;', array('class' => 'blank'));
	echo $zt->closeRow();

	$semFields = AuxLockRules::getSemFields();
	$center = array('align' => 'center');

	foreach ($semFields as $id => $name) {
		echo $zt->openRow();
		echo $zt->cell('&nbsp;', array('class' => 'blank'));
		echo $zt->cell('&nbsp;'. $name);
		$checked = '';
		echo $zt->cell('<input type="text" max="3" size="3" name="order['.$id.']" value="'.(($z = $rule['order'][$id]) ? $z : '0').'">', $center);
		if ($state == 'edit') {
			echo $zt->cell('<input type="radio" name="fields['.$id.']" value="0"'.(($rule['attributes'][$id])?'':CHECKED).'>', $center);
			echo $zt->cell('<input type="radio" name="fields['.$id.']" value="1"'.(($rule['attributes'][$id])?CHECKED:'').'>', $center);
		} else {
			echo $zt->cell('<input type="radio" name="fields['.$id.']" value="0"'.CHECKED.'>', $center);
			echo $zt->cell('<input type="radio" name="fields['.$id.']" value="1">', $center);
		}
		echo $zt->cell('&nbsp;', array('class' => 'blank'));
		echo $zt->closeRow();
	}

	$fset = array(
		'user' => _("Personenbezogene Informationen"),
		'usersemdata' => _("Zusatzinformationen")
	);

	foreach ($fset as $field => $title) {
		echo $zt->openHeaderRow();
		echo $zt->cell('&nbsp;', array('class' => 'blank'));
		echo $zt->cell('&nbsp;<b>'.$title.'</b>', array('colspan' => '4', 'class' => 'steelgraudunkel'));
		echo $zt->cell('&nbsp;', array('class' => 'blank'));
		echo $zt->closeRow();

		$entries = DataFieldStructure::getDataFieldStructures($field);

		foreach ($entries as $id => $entry) {
			echo $zt->openRow();
			echo $zt->cell('&nbsp;', array('class' => 'blank'));
			echo $zt->cell('&nbsp;'. $entry->getName());
			$checked = '';
			echo $zt->cell('<input type="text" max="3" size="3" name="order['.$id.']" value="'.(($z = $rule['order'][$id]) ? $z : '0').'">', $center);
			if ($state == 'edit') {
				echo $zt->cell('<input type="radio" name="fields['.$id.']" value="0"'.(($rule['attributes'][$id])?'':CHECKED).'>', $center);
				echo $zt->cell('<input type="radio" name="fields['.$id.']" value="1"'.(($rule['attributes'][$id])?CHECKED:'').'>', $center);
			} else {
				echo $zt->cell('<input type="radio" name="fields['.$id.']" value="0"'.CHECKED.'>', $center);
				echo $zt->cell('<input type="radio" name="fields['.$id.']" value="1">', $center);
			}
			echo $zt->cell('&nbsp;', array('class' => 'blank'));
			echo $zt->closeRow();
		}
	}

	echo $zt->openRow();
	echo $zt->cell('<br/>', array('colspan' => '20', 'align' => 'center', 'class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openRow();
	echo $zt->cell('<input type="image" '.makebutton('uebernehmen', 'src').'>', array('colspan' => '20', 'align' => 'center', 'class' => 'blank'));
	echo $zt->closeRow();

	echo $zt->openRow();
	echo $zt->cell('<br/>', array('colspan' => '20', 'class' => 'blank'));
	echo $zt->close();

	if ($state == 'edit') {
		echo '<input type="hidden" name="id" value="'.$rule['lock_id'].'">', "\n";
		echo '<input type="hidden" name="cmd" value="doEdit">', "\n";
	} else {
		echo '<input type="hidden" name="cmd" value="doAdd">', "\n";
	}

	echo '</form>';
}

switch ($_REQUEST['cmd']) {
	case 'new_rule':
		$view = 'add';
		break;

	case 'doAdd':
		AuxLockRules::createLockRule($_REQUEST['name'], $_REQUEST['description'], $_REQUEST['fields'], $_REQUEST['order']);
		$msg[] = 'msg§'. sprintf(_("Die Regel %s wurde angelegt!"), $_REQUEST['name']);
		$view = 'main';
		break;

	case 'edit':
		$edit_id = $_REQUEST['id'];
		$view = 'edit';
		break;

	case 'doEdit':
		AuxLockRules::updateLockRule($_REQUEST['id'], $_REQUEST['name'], $_REQUEST['description'], $_REQUEST['fields'], $_REQUEST['order']);
		$msg[] = 'msg§'. sprintf(_("Die Regel %s wurde geändert!"), $_REQUEST['name']);
		$view = 'main';
		break;

	case 'delete':
		if (AuxLockRules::deleteLockRule($_REQUEST['id'])) {
			$msg[] = 'msg§'. _("Die Regel wurde gelöscht!");
		} else {
			$msg[] = 'error§'. _("Es können nur nicht verwendete Regeln gelöscht werden!");
		}
		break;

	default:
		$view = 'main';
		break;
}

$containerTable = new ContainerTable();
echo $containerTable->openRow();
echo $containerTable->openCell(array("colspan"=>"2"));
if (is_array($msg)) {
	foreach ($msg as $message) {
		parse_msg($message);
	}
}

// do stuff

echo $containerTable->close();

$zt = new ZebraTable(array('width' => '100%'));

switch ($view) {
	case 'add':
		ruleView('add');
		break;

	case 'edit':
		ruleView('edit', $edit_id);
		break;

	default:
		mainView();
		break;
}

echo $containerTable->close();
page_close();
?>
