<?php
/*
online.php - Anzeigemodul fuer Personen die Online sind
Copyright (C) 2002 André Noack <andre.noack@gmx.net>, Cornelis Kater <ckater@gwdg.de>

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
$perm->check("user");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once 'lib/functions.php';
require_once ('lib/msg.inc.php');
require_once ('lib/visual.inc.php');
require_once ('lib/include/messagingSettings.inc.php');
require_once ('lib/messaging.inc.php');
require_once ('lib/contact.inc.php');
require_once ('lib/user_visible.inc.php');
if ($GLOBALS['CHAT_ENABLE']){
	include_once $RELATIVE_PATH_CHAT.'/chat_func_inc.php';
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
}
$msging=new messaging;
$cssSw=new cssClassSwitcher;

$HELP_KEYWORD="Basis.InteraktionWhosOnline";

$CURRENT_PAGE = _("Wer ist online?");
// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_sms.inc.php');

ob_start();

$online = get_users_online($my_messaging_settings['active_time'], $user->cfg->getValue($user->id, "ONLINE_NAME_FORMAT"));

if ($sms_msg) {
	$msg = $sms_msg;
	$sms_msg = '';
	$sess->unregister('sms_msg');
}

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</tr></td></table>";
	page_close();
	die;
	}

if ($cmd=="add_user") {
	$msging->add_buddy ($add_uname);
	$online[$add_uname]['is_buddy'] = true;
}

if ($cmd=="delete_user"){
	$msging->delete_buddy ($delete_uname);
	$online[$delete_uname]['is_buddy'] = false;
}
?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<?
if ($msg)
	{
	echo"<tr><td class=\"blank\"colspan=2><br>";
	parse_msg ($msg);
	echo"</td></tr>";
	}

	?>
	<tr>
		<td class="onlineinfo"><blockquote>
		<?
		print(_("Hier k&ouml;nnen Sie sehen, wer au&szlig;er Ihnen im Moment online ist.") . "<p>");
		printf(_("Sie k&ouml;nnen diesen Usern eine Nachricht schicken %s oder sie zum Chatten %s einladen."), sprintf("<img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" width=\"24\" height=\"21\" %s border=\"0\"><br>", tooltip(_("Nachricht an User verschicken"))), sprintf("<img src=\"".$GLOBALS['ASSETS_URL']."images/chat1.gif\" width=\"24\" height=\"21\" %s border=\"0\">", tooltip(_("zum Chatten einladen"))));
		print("\n<br>" . _("Wenn Sie auf den Namen klicken, kommen Sie zur Homepage des Users."));

		if ($SessSemName[0] && $SessSemName["class"] == "inst")
			echo "<br /><br /><a href=\"institut_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Einrichtung") . "</a>";
		elseif ($SessSemName[0])
			echo "<br /><br /><a href=\"seminar_main.php\">" . _("Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung") . "</a>";
		?>
		<td class="blank" align = right><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/online.jpg" border="0"></td>
	</tr>
	<tr>
		<td class="blank" colspan=2 width="100%">
	<?
ob_end_flush();
ob_start();
	//Erzeugen der Liste aktiver und inaktiver Buddies
	$different_groups=FALSE;


	$owner_id = $user->id;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	$weitere = 0;

	if (is_array ($online)) { // wenn jemand online ist
		foreach($online as $username=>$value) { //alle durchgehen die online sind
			$user_id = $value["userid"];
			if ($value['is_visible']) {
				if ($value['is_buddy']) { // er ist auf jeden Fall als Buddy eingetragen
					$db2->query ("SELECT statusgruppen.position, name, statusgruppen.statusgruppe_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id = '$owner_id' AND user_id = '$user_id' ORDER BY statusgruppen.position ASC LIMIT 1");
					if ($db2->next_record()) { // er ist auch einer Gruppe zugeordnet
						$group_buddies[]=array($db2->f("position"), $db2->f("name"), $online[$username]["name"],$online[$username]["last_action"],$username,$db2->f("statusgruppe_id"),$user_id);
					} else {	// buddy, aber keine Gruppe
						$non_group_buddies[]=array($online[$username]["name"],$online[$username]["last_action"],$username,$user_id);
					}
				} else { // online, aber kein buddy
					$n_buddies[]=array($online[$username]["name"],$online[$username]["last_action"],$username,$user_id);
				}
			} else {
				$weitere++;
			}
		}
	}


 if (is_array($group_buddies))
	sort ($group_buddies);

if (is_array($non_group_buddies))
	sort ($non_group_buddies);

if (is_array($n_buddies))
	sort ($n_buddies);

	$cssSw->switchClass();
	//Anzeige
	echo "<table width=\"100%\" align=\"center\"cellspacing=0 border=0 cellpadding=2>\n";

	//Kopfzeile
	if ($my_messaging_settings["show_only_buddys"])
		echo "\n<tr><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20><font size=-1><b>" . _("Buddies") . "</b></font></td></tr>\n";
	else
		echo "\n<tr><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20><font size=-1><b>" . _("Buddies") . "</b></font></td><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=1 height=20><font size=-1><b>" . _("andere Nutzer") . "</b></font></td></tr>\n";
	echo "<tr>";

	//Buddiespalte

	if (!GetNumberOfBuddies()) { // Nutzer hat gar keine buddies
		echo "\n<td width=\"50%\" valign=\"top\">";
		echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";
		echo "\n<td class=\"steel1\" width=\"50%\" align=\"center\" colspan=5><font size=-1>";
		echo _("Sie haben keine Buddies ausgew&auml;hlt.") . "<br />";
		printf(_("Zum Adressbuch (%d Eintr&auml;ge) klicken Sie %shier%s"), GetSizeofBook(), "<a href=\"contact.php\">", "</a>");
		echo "</font></td>";
		echo "\n</tr></table></td>";

	} else { // nutzer hat prinzipiell buddies

		echo "\n<td width=\"50%\" valign=\"top\">";
		echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0>\n";
		if (($group_buddies) || ($non_group_buddies)) {
			echo "\n<tr><td class=\"steelgraudunkel\" colspan=2 width=\"65%\"><font size=-1 color=\"white\">&nbsp;<b>" . _("Name") . "</b></font></td><td class=\"steelgraudunkel\"  width=\"20%\" colspan=4><font size=-1 color=\"white\"><b>" . _("letztes Lebenszeichen") . "</b></font></td></tr>";
		} else { // gar keine Buddies online
			echo "\n<tr><td class=\"steelgraudunkel\" width=\"50%\" align=\"center\" colspan=6><font size=-1 color=\"white\"><b>" . _("Es sind keine Ihrer Buddies online.") ."</b></font></td></tr>";
		}
		if (sizeof($group_buddies)) {
			reset ($group_buddies);
			$lastgroup = "";
			$groupcount = 0;
			while (list($index)=each($group_buddies)) {
				list($position,$gruppe,$fullname,$zeit,$tmp_online_uname,$statusgruppe_id,$tmp_user_id)=$group_buddies[$index];
				if ($gruppe != $lastgroup) {// Ueberschrift fuer andere Gruppe
					printf("\n<tr><td colspan=\"6\" align=\"middle\" class=\"steelkante\"><a href=\"contact.php?view=gruppen&filter=%s\"><font size=\"2\" color=\"#555555\">%s</font></a></td></tr>",$statusgruppe_id, htmlready($gruppe));
					$groupcount++;
					if ($groupcount > 10) //irgendwann gehen uns die Farben aus
						$groupcount = 1;
				}
				$lastgroup = $gruppe;
				printf("\n<tr><td  width=\"1%%\" class=\"gruppe%s\">&nbsp; </td><td class=\"steel1\" width=\"64%%\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s </font></a></td><td class=\"steel1\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $groupcount, $tmp_online_uname, htmlReady($fullname), date("i",$zeit), date("s",$zeit));
				echo "\n<td class=\"steel1\" width=\"5%\" align=center>";
				if ($CHAT_ENABLE) {
					echo chat_get_online_icon($tmp_user_id,$tmp_online_uname);
				} else {
					echo "&nbsp;";
				}
				echo "\n</td><td class=\"steel1\" width=\"5%\" align=center><a href=\"sms_send.php?sms_source_page=online.php&rec_uname=$tmp_online_uname\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" ".tooltip(_("Nachricht an User verschicken"))." border=\"0\"></a></td><td class=\"steel1\" width=\"5%\" align=\"center\"><a href=\"$PHP_SELF?cmd=delete_user&delete_uname=$tmp_online_uname\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("aus der Buddy-Liste entfernen"))." border=\"0\"></a></td></tr>";
				$cssSw->switchClass();
			}
		}

		if (sizeof($non_group_buddies)) {
			echo "\n<tr><td colspan=6 class=\"steelkante\" align=\"center\"><font size=-1 color=\"#555555\"><a href=\"contact.php?view=gruppen&filter=all\"><font size=-1 color=\"#555555\">"._("Buddies ohne Gruppenzuordnung").":</font></a></font></td></tr>";
			reset ($non_group_buddies);
			while (list($index)=each($non_group_buddies)) {
				list($fullname,$zeit,$tmp_online_uname,$tmp_user_id)=$non_group_buddies[$index];
				printf("\n<tr><td  width=\"1%%\" class=\"steel1\">&nbsp; </td><td class=\"steel1\" width=\"64%%\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s </font></a></td><td class=\"steel1\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $tmp_online_uname, htmlReady($fullname), date("i",$zeit), date("s",$zeit));
				echo "\n<td class=\"steel1\" width=\"5%\" align=center>";
				if ($CHAT_ENABLE) {
					echo chat_get_online_icon($tmp_user_id,$tmp_online_uname);
				} else {
					echo "&nbsp;";
				}
				echo "\n</td><td class=\"steel1\" width=\"5%\" align=center><a href=\"sms_send.php?sms_source_page=online.php&rec_uname=$tmp_online_uname\"><img src=\"".$GLOBALS['ASSETS_URL']."images/nachricht1.gif\" ".tooltip(_("Nachricht an User verschicken"))." border=\"0\"></a></td><td class=\"steel1\" width=\"5%\" align=\"center\"><a href=\"$PHP_SELF?cmd=delete_user&delete_uname=$tmp_online_uname\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("aus der Buddy-Liste entfernen"))." border=\"0\"></a></td></tr>";
			}
		}
		echo "\n<tr><td class=\"blank\" width=\"50%\" align=\"center\" colspan=6><font size=-1><br>Zum Adressbuch (".GetSizeofBook()." Eintr&auml;ge) klicken Sie <a href=\"contact.php\">hier</a></font></td>";
		echo "\n</tr></table></td>";
	}

ob_end_flush();
ob_start();

	//Spalte anderer Benutzer
	if (!$my_messaging_settings["show_only_buddys"]) {
		echo "\n<td width=\"50%\" valign=\"top\">";
		echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";

		if (is_array($n_buddies)) {
			echo "\n<td class=\"steelgraudunkel\"  colspan=2><font size=-1 color=\"white\"><b>&nbsp;" . _("Name") . "</b></font></td><td class=\"steelgraudunkel\" colspan=3 ><font size=-1 color=\"white\"><b>" . _("letztes Lebenszeichen") . "</b></font></td></tr>\n";
			reset($n_buddies);
			while (list($index)=each($n_buddies)) {
				list($fullname,$zeit,$tmp_online_uname,$tmp_user_id)=$n_buddies[$index];
				printf("\n<tr><td class=\"".$cssSw->getClass()."\" width=\"1%%\"><a href=\"$PHP_SELF?cmd=add_user&add_uname=$tmp_online_uname\"><img src=\"".$GLOBALS['ASSETS_URL']."images/add_buddy.gif\" ".tooltip(_("zu den Buddies hinzufügen"))." border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"67%%\" align=\"left\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s </font></a></td><td class=\"".$cssSw->getClass()."\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $tmp_online_uname, htmlReady($fullname), date("i",$zeit), date("s",$zeit));
				echo "\n<td class=\"".$cssSw->getClass()."\" width=\"6%\"align=center>";
				if ($CHAT_ENABLE) {
					echo chat_get_online_icon($tmp_user_id,$tmp_online_uname);
				} else {
					echo "&nbsp;";
				}
			?>
			</td>
			<td class="<?=$cssSw->getClass()?>" align=center width="6%">
				<a href="sms_send.php?sms_source_page=online.php&rec_uname=<?=$tmp_online_uname?>">
					<img src="<?=$GLOBALS['ASSETS_URL']?>images/nachricht1.gif" <?=tooltip(_("Nachricht an User verschicken"))?> border="0">
				</a>
			</td>
		</tr>
			<?
				$cssSw->switchClass();
			}
			if ($weitere > 0) {
		?>
			<tr>
				<td colspan="5" align="center">
					<br/>
					<font size="-1"><?=sprintf(_("+ %s unsichtbare NutzerInnen"), $weitere)?></font>
				</td>
			</tr>
		<?
			}
		} else {
			// if we previously found unvisible users who are online
			if ($weitere > 0) {
			?>
				<td class="steelgraudunkel" colspan="5" align="center">
					<font size="-1" color="white">
						<b>&nbsp;<?=_("Keine sichtbaren Nutzer online.")?></b>
					</font>
				</td>
			</tr>
			<tr>
				<td colspan="5" align="center">
					<br/>
					<font size="-1"><?=sprintf(_("+ %s unsichtbare NutzerInnen"), $weitere)?></font>
				</td>
			</tr>
			<?
			} else {
			?>
			<td class="steelgraudunkel" width="50%" align="center" colspan="4">
				<font size="-1" color="white">
					<b><?=_("Kein anderer Nutzer ist online.")?></b>
				</font>
			</td>
			</tr>
			</table>
			</td>
			<?
			}
		}
	}
	echo "\n</tr></table>";
?>
</tr></table></td></tr></table>
<?
include ('lib/include/html_end.inc.php');
ob_end_flush();
  // Save data back to database.
page_close();
?>
