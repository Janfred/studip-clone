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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ("functions.php");
require_once ("msg.inc.php");
require_once ("visual.inc.php");
require_once ("messagingSettings.inc.php");
require_once ("messaging.inc.php");
require_once ("contact.inc.php");

$msging=new messaging;
$cssSw=new cssClassSwitcher;

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

ob_start();

if ($sms_msg)
	$msg=rawurldecode($sms_msg);

if (($change_view) || ($delete_user) || ($view=="Messaging")) {
	change_messaging_view();
	echo "</tr></td></table>";
	page_close();
	die;
	}

if ($cmd=="add_user") {
	$msging->add_buddy ($add_uname, 0);
}

if ($cmd=="delete_user")
	$msging->delete_buddy ($delete_uname);

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic"><img src="pictures/nutzer.gif" border="0" align="texttop"><b>&nbsp;Wer ist Online?</b></td>
	<td nowrap class="topic" align="right">Einstellungen &auml;ndern&nbsp; <a href="<? echo $PHP_SELF ?>?change_view=TRUE"><img src="pictures/pfeillink.gif" border=0></a>
	
</tr>
<?
if ($msg)
	{
	echo"<tr><td class=\"blank\"colspan=2><br>";
	parse_msg ($msg);
	echo"</td></tr>";
	}

	?>
	<tr>
		<td class="blank"><br><blockquote>Hier k&ouml;nnen Sie sehen, wer ausser Ihnen im Moment online ist. <p>Sie k&ouml;nnen den Usern eine Nachricht schicken <img src="pictures/nachricht1.gif" width="24" height="21" <?=tooltip("Nachricht an User verschicken")?> border="0"><br>oder ihn zum Chatten <img src="pictures/chat1.gif" width="24" height="21" <?=tooltip("zum Chatten einladen")?> border="0"> einladen. <br>Wenn Sie auf den Namen klicken, kommen Sie zu seiner Homepage.
		<?
		if ($SessSemName[0] && $SessSemName["class"] == "inst")
			echo "<br /><br /><a href=\"institut_main.php\">Zur&uuml;ck zur ausgew&auml;hlten Einrichtung</a>";
		elseif ($SessSemName[0])
			echo "<br /><br /><a href=\"seminar_main.php\">Zur&uuml;ck zur ausgew&auml;hlten Veranstaltung</a>";
		?>
		<td class="blank" align = right><img src="pictures/online.jpg" border="0"></td>
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

	if (is_array ($online)) { // wenn jemand online ist
		if (!$my_messaging_settings["show_only_buddys"]) {
			foreach($online as $username=>$value) { //ale durchgehen die online sind
				$user_id = get_userid($username);
				$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND user_id = '$user_id' AND buddy = '1'");	
				if ($db->next_record()) { // er ist auf jeden Fall als Buddy eingetragen
					$db2->query ("SELECT name, statusgruppen.statusgruppe_id FROM statusgruppen LEFT JOIN statusgruppe_user USING(statusgruppe_id) WHERE range_id = '$owner_id' AND user_id = '$user_id'");	
					if ($db2->next_record()) { // er ist auch einer Gruppe zugeordnet
						$group_buddies[]=array($db2->f("name"), $online[$username]["name"],$online[$username]["last_action"],$username,$db2->f("statusgruppe_id"));
					} else {	// buddy, aber keine Gruppe
						$non_group_buddies[]=array($online[$username]["name"],$online[$username]["last_action"],$username);
					}
				} else { // online, aber kein buddy
					$n_buddies[]=array($online[$username]["name"],$online[$username]["last_action"],$username);
				}
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
	echo "<table width=\"99%\" align=\"center\"cellspacing=0 border=0 cellpadding=2>\n";

	//Kopfzeile
	if ($my_messaging_settings["show_only_buddys"]) 
		echo "\n<tr><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>Buddies</b></font></td></tr>\n";
	else
		echo "\n<tr><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>Buddies</b></font></td><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>andere  Nutzer</b></font></td></tr>\n";
	echo "<tr>";

	//Buddiespalte
	$db->query ("SELECT contact_id FROM contact WHERE owner_id = '$owner_id' AND buddy = '1'");	
	if (!$db->next_record()) { // Nutzer hat gar keine buddies buddies
		echo "\n<td width=\"50%\" valign=\"top\">";
		echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";
		echo "\n<td class=\"steel1\" width=\"50%\" align=\"center\" colspan=5><font size=-1>Sie haben keine Buddies ausgew&auml;hlt. <br />Zum Addressbuch (".GetSizeofBook()." Eintr&auml;ge) klicken Sie <a href=\"contact.php\">hier</a></font></td>";
		echo "\n</tr></table></td>";
		}
	else { // nutzer hat pronzipiell buddies
		echo "\n<td width=\"50%\" valign=\"top\">";
		echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0>\n";
		if (sizeof($group_buddies)) {
			echo "\n<tr><td class=\"steelgraudunkel\" colspan=2 width=\"65%\"><font size=-1 color=\"white\"><b>Name</b></font></td><td class=\"steelgraudunkel\"  width=\"20%\" colspan=4><font size=-1 color=\"white\"><b>letztes Lebenszeichen</b></font></td></tr>"; 
			reset ($group_buddies);
			$lastgroup = "";
			$groupcount = 0;
			while (list($index)=each($group_buddies)) {
				list($gruppe,$fullname,$zeit,$tmp_online_uname,$statusgruppe_id)=$group_buddies[$index];
				if ($gruppe != $lastgroup) {// Ueberschrift fuer andere Gruppe
					printf("\n<tr><td colspan=\"4\" align=\"middle\"><font size=\"2\"><a href=\"contact.php?view=gruppen&filter=%s\">%s</a></font></td></tr>",$statusgruppe_id,$gruppe);
					$groupcount++;
					if ($groupcount > 10) //irgendwann gehen uns die Farben aus
						$groupcount = 1;  
				}
				$lastgroup = $gruppe;
				printf("\n<tr><td  width=\"1%%\" class=\"gruppe%s\">&nbsp; </td><td class=\"".$cssSw->getClass()."\" width=\"64%%\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s </font></a></td><td class=\"".$cssSw->getClass()."\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $groupcount, $tmp_online_uname, htmlReady($fullname), date("i",$zeit), date("s",$zeit));
				echo "\n<td class=\"".$cssSw->getClass()."\" width=\"5%\" align=center>";
				if ($CHAT_ENABLE) {
					if ($chatServer->isActiveUser($chatServer->getIdFromNick("studip",$tmp_online_uname),"studip")) {
						echo "<img src=\"pictures/chat2.gif\"".tooltip("Dieser User befindet sich im Chat")." border=\"0\"></td>";
					} else {
						echo "<a href=\"sms.php?sms_source_page=online.php&cmd=chatinsert&rec_uname=$tmp_online_uname\"><img src=\"pictures/chat1.gif\" ".tooltip("zum Chatten einladen")." border=\"0\"></a>";
					}
				} else {
					echo "&nbsp;";
				}
				echo "\n</td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=center><a href=\"sms.php?sms_source_page=online.php&cmd=write&rec_uname=$tmp_online_uname\"><img src=\"pictures/nachricht1.gif\" ".tooltip("Nachricht an User verschicken")." border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=\"center\"><a href=\"$PHP_SELF?cmd=delete_user&delete_uname=$tmp_online_uname\"><img src=\"pictures/trash.gif\" ".tooltip("aus der Buddylist entfernen")." border=\"0\"></a></td></tr>";
				$cssSw->switchClass();					
			}
		}

		if (sizeof($non_group_buddies)) {
			echo "\n<tr><td colspan=6 class=\"steelgraudunkel\" align=\"center\"><font size=-1 color=\"white\"><b>Buddies ohne Gruppenzuordnung:</b></font></td></tr>";
			reset ($non_group_buddies);
			while (list($index)=each($non_group_buddies)) {
				list($fullname,$zeit,$tmp_online_uname)=$non_group_buddies[$index];
				printf("\n<tr><td  width=\"1%%\" class=\"gruppe%s\">&nbsp; </td><td class=\"".$cssSw->getClass()."\" width=\"64%%\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s </font></a></td><td class=\"".$cssSw->getClass()."\" width=\"20%%\"><font size=-1> %s:%s</font></td>", 0, $tmp_online_uname, htmlReady($fullname), date("i",$zeit), date("s",$zeit));
				echo "\n<td class=\"".$cssSw->getClass()."\" width=\"5%\" align=center>";
				if ($CHAT_ENABLE) {
					if ($chatServer->isActiveUser($chatServer->getIdFromNick("studip",$tmp_online_uname),"studip"))
						echo "<img src=\"pictures/chat2.gif\"".tooltip("Dieser User befindet sich im Chat")." border=\"0\"></td>";
					else    
						echo "<a href=\"sms.php?sms_source_page=online.php&cmd=chatinsert&rec_uname=$tmp_online_uname\"><img src=\"pictures/chat1.gif\" ".tooltip("zum Chatten einladen")." border=\"0\"></a>";
				}
				else echo "&nbsp;";
				echo "\n</td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=center><a href=\"sms.php?sms_source_page=online.php&cmd=write&rec_uname=$tmp_online_uname\"><img src=\"pictures/nachricht1.gif\" ".tooltip("Nachricht an User verschicken")." border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=\"center\"><a href=\"$PHP_SELF?cmd=delete_user&delete_uname=$tmp_online_uname\"><img src=\"pictures/trash.gif\" ".tooltip("aus der Buddylist entfernen")." border=\"0\"></a></td></tr>";
				$cssSw->switchClass();					
				}
			$cssSw->switchClass();
			}
		if (!sizeof($non_group_buddies) &&!sizeof($group_buddies) ) { // gar keine Buddies online
			echo "\n<td class=\"".$cssSw->getClass()."\" width=\"50%\" align=\"center\" colspan=6><font size=-1>Es sind keine Ihrer Buddies online.</font></td></tr><tr>";		
		}
		echo "\n<td class=\"".$cssSw->getClass()."\" width=\"50%\" align=\"center\" colspan=6><font size=-1>Zum Addressbuch (".GetSizeofBook()." Eintr&auml;ge) klicken Sie <a href=\"contact.php\">hier</a></font></td>";
		echo "\n</tr></table></td>";
		}

ob_end_flush();
ob_start();

	//Spalte anderer Benutzer
	if (!$my_messaging_settings["show_only_buddys"]) {	
		echo "\n<td width=\"50%\" valign=\"top\">";
		echo "\n<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";
	
		if (is_array($n_buddies)) {
			echo "\n<td class=\"steelgraudunkel\"  colspan=2><font size=-1 color=\"white\"><b>Name</b></font></td><td class=\"steelgraudunkel\" colspan=3 ><font size=-1 color=\"white\"><b>letztes Lebenszeichen</b></font></td></tr>\n";
			reset($n_buddies);
			while (list($index)=each($n_buddies)) {
				list($fullname,$zeit,$tmp_online_uname)=$n_buddies[$index];
				printf("\n<tr><td class=\"".$cssSw->getClass()."\" width=\"1%%\"><a href=\"$PHP_SELF?cmd=add_user&add_uname=$tmp_online_uname\"><img src=\"pictures/add_buddy.gif\" ".tooltip("zu den Buddies hinzufügen")." border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"67%%\" align=\"left\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s </font></a></td><td class=\"".$cssSw->getClass()."\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $tmp_online_uname, htmlReady($fullname), date("i",$zeit), date("s",$zeit));
				echo "\n<td class=\"".$cssSw->getClass()."\" width=\"6%\"align=center>";
				if ($CHAT_ENABLE){
					if ($chatServer->isActiveUser($chatServer->getIdFromNick("studip",$tmp_online_uname),"studip"))
							echo "<img src=\"pictures/chat2.gif\" ".tooltip("Dieser User befindet sich im Chat")." border=\"0\">";
					else    
							echo "<a href=\"sms.php?sms_source_page=online.php&cmd=chatinsert&rec_uname=$tmp_online_uname\"><img src=\"pictures/chat1.gif\" ".tooltip("zum Chatten einladen")." border=\"0\"></a>";
					}
				else echo "&nbsp;";
				echo "\n</td><td class=\"".$cssSw->getClass()."\" align=center width=\"6%\"><a href=\"sms.php?sms_source_page=online.php&cmd=write&rec_uname=$tmp_online_uname\"><img src=\"pictures/nachricht1.gif\" ".tooltip("Nachricht an User verschicken")." border=\"0\"></a></td></tr>";
				$cssSw->switchClass();					
				}
			
			}
		else {
			echo "\n<td class=\"steelgraudunkel\" width=\"50%\" align=\"center\" colspan=4><font size=-1 color=\"white\"><b>Kein anderer Nutzer ist online.</b></font></td>";
			echo "\n</tr></table></td>";
			}
		}
	echo "\n</tr></table>";
?>
</tr></table></td></tr></table>
</body>
</html>
<?
ob_end_flush();
  // Save data back to database.
page_close();
?>