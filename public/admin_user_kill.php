<?php
# Lifter002: TODO
# Lifter003: TODO
/*
admin_user_kill.php
Copyright (C) 2005 André Noack <noack@data-quest.de>
Suchi & Berg GmbH <info@data-quest.de>
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
$Id: admin_extern.inc.php 6924 2006-11-09 14:32:56Z mlunzena $
*/


require_once('lib/functions.php');
require_once('lib/msg.inc.php');
require_once('lib/classes/UserManagement.class.php');

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("root");

$sess->register('_kill_user');
$db = new DB_Seminar();

include ('lib/seminar_open.php'); 		// initialise Stud.IP-Session

$msg = array();

//-- hier muessen Seiten-Initialisierungen passieren --
if (isset($_REQUEST['cancel_x'])){
	$_kill_user = array();
}

if (isset($_REQUEST['transfer_search']) && strlen($pers_browse_search_string)){
	$db->query("SELECT auth_user_md5.*, changed, mkdate FROM auth_user_md5 LEFT JOIN ".$GLOBALS['user']->that->database_table." ON auth_user_md5.user_id = sid LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) $pers_browse_search_string");
	$_kill_user = array();
	while($db->next_record()){
		$_kill_user[$db->f('username')] = $db->Record;
		$_kill_user[$db->f('username')]['selected'] = true;
	}
	$_kill_user = array_filter($_kill_user, 'is_array');
	$msg[] = array('msg', sprintf(_("%s Nutzer gefunden"), count($_kill_user)));
}

if (isset($_REQUEST['userlist_submit_x']) && trim($_REQUEST['kill_user_list'])){
	$_kill_user = preg_split("/[\s,;]+/", $_REQUEST['kill_user_list'], -1, PREG_SPLIT_NO_EMPTY);
	$_kill_user = array_flip($_kill_user);
	$db->query("SELECT * FROM auth_user_md5 WHERE username IN ('".join("','", array_keys($_kill_user))."')");
	while($db->next_record()){
		$_kill_user[$db->f('username')] = $db->Record;
		$_kill_user[$db->f('username')]['selected'] = true;
	}
	$_kill_user = array_filter($_kill_user, 'is_array');
	$msg[] = array('msg', sprintf(_("%s Nutzer gefunden"), count($_kill_user)));
}
if (isset($_REQUEST['kill_accounts_x']) && check_ticket($_POST['ticket'])){
	$umanager = new UserManagement();
	foreach($_kill_user as $uname => $udetail){
		if (isset($_REQUEST['selected_user'][$uname])){
			$umanager->user_data = array();
			$umanager->msg = '';
			$umanager->getFromDatabase($udetail['user_id']);
			//wenn keine Email gewünscht, Adresse aus den Daten löschen
			if (!$_REQUEST['send_email']) $umanager->user_data['auth_user_md5.Email'] = '';
			if ($umanager->deleteUser()){
				$msg[] = array('msg', sprintf(_("Nutzer %s wurde gelöscht"), $uname)
				.'<div style="margin-left:10px"><table border="0" cellpadding="0" cellspacing="0">'
				. parse_msg_to_string($umanager->msg,"§","blank",1,false,true)
				.'</table></div>');
				unset($_kill_user[$uname]);
			} else {
				$msg[] = array('error', sprintf(_("Nutzer %s konnte nicht gelöscht werden:"), $uname)
				.'<div style="margin-left:10px"><table border="0" cellpadding="0" cellspacing="0">'
				. parse_msg_to_string($umanager->msg,"§","blank",1,false,true)
				.'</table></div>');
			}
		} else {
			$_kill_user[$uname]['selected'] = false;
		}
	}
}

$CURRENT_PAGE = _("Löschen von Benutzer-Accounts");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');	 //hier wird der "Kopf" nachgeladen 
//fake
$i_page = 'new_user_md5.php';
include ('lib/include/links_admin.inc.php');	//Linkleiste fuer admins

echo "\n" . cssClassSwitcher::GetHoverJSFunction() . "\n";

?>
<script type="text/javascript">
			function invert_selection(){
				my_elements = document.forms['admin_user_kill'].elements;
				if(my_elements.length){
					for(i = 0; i < my_elements.length; ++i){
						if(my_elements[i].name.substring(0,13) == 'selected_user'){
							if(my_elements[i].checked) my_elements[i].checked = false;
							else my_elements[i].checked = true;
						}
					}
				}
			}
</script>
<table border="0" bgcolor="#000000" align="center" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td class="blank">&nbsp;</td>
	</tr>
	<?
	if (count($msg)){
		echo "\n<tr><td class=\"blank\"><table width=\"99%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">";
		parse_msg_array($msg, "blank", 1 ,false);
		echo "\n</table></td></tr>";
	}
	?>
	<tr>
	<td class="blank">
	<div style="margin-left:20px;font-size:10pt;">
	<form action="<?=$PHP_SELF?>?userlist_submit=1" method="POST">
	<?=_("Geben sie eine Liste von Nutzernamen (username) ein, die zum Löschen vorgesehen sind. Die Namen können mit Komma, Semikolon, oder whitespaces getrennt sein.")?>
	<br><br>
	<textarea name="kill_user_list" rows="10" cols="80" wrap="virtual"><?=(is_array($_kill_user) ? join("\n", array_keys($_kill_user)): '')?></textarea>
	<br><br><br>
	<?=MakeButton('absenden', $mode = "input", $tooltip = _("Namen überprüfen"), 'userlist_submit')?>
	&nbsp;
	<?=MakeButton('zuruecksetzen', $mode = "input", $tooltip = _("Zurücksetzen"), 'cancel')?>
	<br>
	</div>
	</form>
	</td>
	</tr>
	<tr>
		<td class="blank">&nbsp;</td>
	</tr>
	
	<?
	if (count($_kill_user)){
		$cssSw = new cssClassSwitcher();
		$cssSw->enableHover();
		echo '<tr><td class="blank">';
		echo '<div style="margin-left:20px;font-size:10pt;">';
		echo '<form name="admin_user_kill" method="POST" action="'.$PHP_SELF.'?kill=1">';
		echo '<input type="hidden" name="ticket" value="'.get_ticket().'">';
		echo '<table cellpadding="2" cellspacing="0" bgcolor="#eeeeee"  width="85%">';
		echo chr(10).'<tr><td colspan="5" align="right" class="blank"><img '.makeButton('auswahlumkehr','src').' '.tooltip(_("Auswahl umkehren")) .' border="0" onClick="invert_selection();return false;"></td></tr>';
		echo chr(10).'<tr><td colspan="5" class="blank">&nbsp;</td></tr>';
		foreach($_kill_user as $username => $userdetail){
			echo chr(10).'<tr  ' . $cssSw->getHover().'><td ' . $cssSw->getFullClass() . '><b>'
				. '<a href="new_user_md5.php?details='.$username.'">'.$username . '</a></b></td>';
			echo chr(10).'<td ' . $cssSw->getFullClass() . '>' . htmlReady( 
						$userdetail['Vorname'] . ' ' . $userdetail['Nachname'] . ' ('.$userdetail['perms'].')').'</td>';
			echo chr(10).'<td ' . $cssSw->getFullClass() . '>' . htmlReady($userdetail['Email']) . '</td>';
			echo chr(10).'<td ' . $cssSw->getFullClass() . '>' . htmlReady(is_null($userdetail['auth_plugin']) ? 'standard' : $userdetail['auth_plugin']) . '</td>';
			echo chr(10).'<td ' . $cssSw->getFullClass() . '><input type="checkbox" value="1" name="selected_user['.$username.']" '
				.($userdetail['selected'] ? ' checked ' : '') .'></td>';
			echo chr(10).'</tr>'; 
			$cssSw->switchClass();
		}
		echo chr(10).'<tr><td colspan="5" class="blank">&nbsp;</td></tr>';
		?>
		<tr>
		<td class="blank" colspan="3" align="right">
		<?=_("Benachrichtigung per Email verschicken:")?>
		&nbsp;
		<input type="checkbox" name="send_email" value="1">
		</td>
		<td class="blank" colspan="2" align="right"> 
		<?=makeButton('loeschen', $mode = "input", $tooltip = _("Ausgewählte Nutzeraccounts löschen"), 'kill_accounts')?>
		</td>
		</tr>
		<?
		echo '</table></div></form></td></tr>';
	}
	?>
	<tr>
		<td class="blank">&nbsp;</td>
	</tr>
</table>
<?
include ('lib/include/html_end.inc.php'); // Output of html head
page_close();
?>
