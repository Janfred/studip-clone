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

?>
<html>
<head>
<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
<body bgcolor=white>

<?
	
	
	include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren --

	require_once ("functions.php");
	require_once ("msg.inc.php");
	require_once ("visual.inc.php");
	require_once ("messagingSettings.inc.php");
	require_once ("messaging.inc.php");

	$msging=new messaging;
	$cssSw=new cssClassSwitcher;

	include "header.php";   //hier wird der "Kopf" nachgeladen
	
if ($sms_msg)
	$msg=rawurldecode($sms_msg);


if (($change_view) || ($add_user) || ($do_add_user) || ($delete_user)) {
	change_messaging_view();
	echo "</tr></td></table>";
	page_close();
	die;
	}

if ($cmd=="add_user")
	$msging->add_buddy ($add_uname, 0);

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
		<td class="blank"><br><blockquote>Hier k&ouml;nnen Sie sehen, wer ausser Ihnen im Moment online ist. <p>Sie k&ouml;nnen den Usern eine Nachricht schicken <img src="pictures/nachricht1.gif" width="24" height="21" alt="nachricht1.gif (1 kB)" border="0"><br>oder ihn zum Chatten <img src="pictures/chat1.gif" width="24" height="21" alt="chat1.gif (1 kB)" border="0"> einladen. <br>Wenn Sie auf den Namen klicken, kommen Sie zu seiner Homepage.
		<? if ($SessSemName[0]) echo "<br /><br /><a href=\"seminar_main.php\">Zur&uuml;ck zur Veranstaltung</a>"; ?></blockquote></td>
		<td class="blank" align = right><img src="pictures/online.jpg" border="0"></td>
	</tr>
	<tr>
		<td class="blank" colspan=2 width=100%">
	<?

if (is_array ($online)) {
	//Erzeugen der Liste aktiver Buddies
	reset($online);
	$different_groups=FALSE;
	while (list($index)=each($online)) 	{
		list($vor,$nach,$zeit,$tmp_online_uname)=$online[$index];
		if ($my_buddies) {
			foreach ($my_buddies as $a) {
				if ($tmp_online_uname == $a["username"]) {
					$active_buddies[]=array($a["group"], $vor,$nach,$zeit,$a["username"]);
					$tmp_online_userids[]=$a["username"];
					if ($a["group"])
						$different_groups=TRUE;
					}
				}
			}
		}
	
	if ($different_groups==TRUE)
		sort ($active_buddies);

	//Erzeugen der Buddyliste inaktiver Buddies
	if ($my_buddies) {	
		foreach ($my_buddies as $a) {
			if (is_array ($tmp_online_userids)) {
				if (!in_array($a["username"], $tmp_online_userids))
					$inactive_buddies[]=array(get_nachname(get_userid($a["username"])), $a["username"]);
				}
			else
				$inactive_buddies[]=array(get_nachname(get_userid($a["username"])), $a["username"]);
			}
		if (is_array($inactive_buddies))
			sort ($inactive_buddies);
		}
	
	//Erzeugen der Liste anderer Nutzer	
	if (!$my_messaging_settings["show_only_buddys"]) {
		reset($online);
		while (list($index)=each($online)) 	{
			list($vor,$nach,$zeit,$tmp_online_uname)=$online[$index];
			$is_buddy=FALSE;
			if ($my_buddies)
				foreach ($my_buddies as $a) {
					if ($tmp_online_uname == $a["username"]) 
						$is_buddy=TRUE;
					}
			if (!$is_buddy) 
				$n_buddies[]=array($vor,$nach,$zeit,$tmp_online_uname);
			}
		}
	}
else
	if ($my_buddies) {
		foreach ($my_buddies as $a) 
			$inactive_buddies[]=array(get_nachname(get_userid($a["username"])), $a["username"]);
	sort ($inactive_buddies);
	}
	
	$cssSw->switchClass();
	
	//Anzeige
	echo "<table width=\"99%\" align=\"center\"cellspacing=0 border=0 cellpadding=2>\n";

	//Kopfzeile
	if ($my_messaging_settings["show_only_buddys"]) 
		echo "<tr><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>Buddies</b></font></td></tr>\n";
	else
		echo "<tr><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>Buddies</b></font></td><td class=\"".$cssSw->getHeaderClass()."\" width=\"50%\" align=\"center\"><img src=\"pictures/blank.gif\" width=1 height=20><font size=-1><b>andere  Nutzer</b></font></td></tr>\n";
	echo "<tr>";

	//Buddiespalte
	if (!is_array($my_buddies)) {
		echo "<td width=\"50%\" valign=\"top\">";
		echo "<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";
		echo "<td class=\"steel1\" width=\"50%\" align=\"center\" colspan=5><font size=-1>Sie haben keine Buddies ausgew&auml;hlt. <br />Um neue Buddies aufzunehmen, klicken sie <a href=\"$PHP_SELF?change_view=TRUE#buddy_anker\">hier</a></font></td>";
		echo "</tr></table></td>";
		}
	else {
		echo "<td width=\"50%\" valign=\"top\">";
		echo "<table width=\"100%\" cellspacing=0 cellpadding=1 border=0>\n";
		if (sizeof($active_buddies)) {
			echo "<tr><td class=\"steelgraudunkel\" colspan=2 width=\"65%\"><font size=-1 color=\"white\"><b>Name</b></font></td><td class=\"steelgraudunkel\"  with=\"20%\" colspan=4><font size=-1 color=\"white\"><b>letztes Lebenszeichen</b></font></td></tr>"; 
			reset ($active_buddies);
			while (list($index)=each($active_buddies)) {
				list($gruppe,$vor,$nach,$zeit,$tmp_online_uname)=$active_buddies[$index];
				printf("<tr><td class=\"".$cssSw->getClass()."\" width=\"1%%\" class=\"gruppe%s\">&nbsp; </td><td class=\"".$cssSw->getClass()."\" width=\"64%%\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s %s </font></a></td><td class=\"".$cssSw->getClass()."\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $gruppe, $tmp_online_uname, htmlReady($vor), htmlReady($nach), date("i",$zeit), date("s",$zeit));
				echo "<td class=\"".$cssSw->getClass()."\" width=\"5%\" align=center>";
				if ($CHAT_ENABLE) {
					if ($chatServer->isActiveUser($chatServer->getIdFromNick("studip",$tmp_online_uname),"studip"))
				    		echo "<img src=\"pictures/chat2.gif\" alt=\"Dieser User befindet sich im Chat\" border=\"0\"></td>";
					else    
				    		echo "<a href=\"sms.php?sms_source_page=online.php&cmd=chatinsert&rec_uname=$tmp_online_uname\"><img src=\"pictures/chat1.gif\" alt=\"zum Chatten einladen\" border=\"0\"></a>";
				}
				else echo "&nbsp;";
				echo "</td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=center><a href=\"sms.php?sms_source_page=online.php&cmd=write&rec_uname=$tmp_online_uname\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=\"center\"><a href=\"$PHP_SELF?cmd=delete_user&delete_uname=$tmp_online_uname\"><img src=\"pictures/trash.gif\" alt=\"aus der Buddylist entfernen\" border=\"0\"></a></td></tr>";
				$cssSw->switchClass();					
				}
			}
		else
			echo "<td class=\"steel1\" align=\"center\" colspan=6><font size=-1>Im Augenblick ist keiner ihrer Buddies online.</font></td></tr>";
		if (sizeof($inactive_buddies)) {
			echo "<tr><td colspan=6 class=\"steelgraudunkel\" align=\"center\"><font size=-1 color=\"white\"><b>Diese Buddies sind zur Zeit offline:</b></font></td></tr>";
			reset ($inactive_buddies);
			while (list($index)=each($inactive_buddies)) {
				list($nachname, $tmp_online_uname)=$inactive_buddies[$index];
				$cssSw->switchClass();				
				echo "<tr><td class=\"".$cssSw->getClass()."\" colspan=3 width=\"85%\"><a href=\"about.php?username=$tmp_online_uname\"><font color=\"#666666\" size=-1>&nbsp; ".htmlReady(get_fullname_from_uname($tmp_online_uname))."</font></a></td><td class=\"".$cssSw->getClass()."\" width=\"5%\"align=center>&nbsp; </td><td class=\"".$cssSw->getClass()."\" width=\"5%\"align=center><a href=\"sms.php?sms_source_page=online.php&cmd=write&rec_uname=$tmp_online_uname\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"5%\" align=\"center\"><a href=\"$PHP_SELF?cmd=delete_user&delete_uname=$tmp_online_uname\"><img src=\"pictures/trash.gif\" alt=\"aus der Buddylist entfernen\" border=\"0\"></a></td></tr>";
				}
			$cssSw->switchClass();
			}
			echo "<td class=\"".$cssSw->getClass()."\" width=\"50%\" align=\"center\" colspan=6><font size=-1>Um weitere Buddies aufzunehmen, klicken sie <a href=\"$PHP_SELF?change_view=TRUE#buddy_anker\">hier</a></font></td>";
		echo "</tr></table></td>";
		}

	//Spalte anderer Benutzer
	if (!$my_messaging_settings["show_only_buddys"]) {	
		echo "<td width=\"50%\" valign=\"top\">";
		echo "<table width=\"100%\" cellspacing=0 cellpadding=1 border=0><tr>\n";
	
		if (is_array($n_buddies)) {
			echo "<td class=\"steelgraudunkel\" width=\"69%\"  colspan=2><font size=-1 color=\"white\">Name</font></td><td class=\"steelgraudunkel\" colspan=3 width=\"20%\"><font size=-1 color=\"white\"><b>letztes Lebenszeichen</b></font></td></tr>\n";
			reset($n_buddies);
			while (list($index)=each($n_buddies)) {
				list($vor,$nach,$zeit,$tmp_online_uname)=$n_buddies[$index];
				printf("<tr><td class=\"".$cssSw->getClass()."\" width=\"1%%\"><a href=\"$PHP_SELF?cmd=add_user&add_uname=$tmp_online_uname\"><img src=\"pictures/add_buddy.gif\" alt=\"zu den Buddies hinzuf&uuml;gen\" border=\"0\"></a></td><td class=\"".$cssSw->getClass()."\" width=\"69%%\" align=\"left\"><a href=\"about.php?username=%s\"><font size=-1>&nbsp; %s %s </font></a></td><td class=\"".$cssSw->getClass()."\" width=\"20%%\"><font size=-1> %s:%s</font></td>", $tmp_online_uname, htmlReady($vor), htmlReady($nach), date("i",$zeit), date("s",$zeit));
				echo "<td width=\"5%\"align=center>";
				if ($CHAT_ENABLE){
					if ($chatServer->isActiveUser($chatServer->getIdFromNick("studip",$tmp_online_uname),"studip"))
				    		echo "<img src=\"pictures/chat2.gif\" alt=\"Dieser User befindet sich im Chat\" border=\"0\">";
					else    
				    		echo "<a href=\"sms.php?sms_source_page=online.php&cmd=chatinsert&rec_uname=$tmp_online_uname\"><img src=\"pictures/chat1.gif\" alt=\"zum Chatten einladen\" border=\"0\"></a>";
					}
				else echo "&nbsp;";
				echo "</td><td class=\"".$cssSw->getClass()."\" align=center width=\"5%\"><a href=\"sms.php?sms_source_page=online.php&cmd=write&rec_uname=$tmp_online_uname\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\"></a></td></tr>";
				$cssSw->switchClass();					
				}
			}
		else {
			echo "<td class=\"steel1\" width=\"50%\" align=\"center\" colspan=4><font size=-1>Kein anderer Nutzer ist online.</font></td>";
			echo "</tr></table></td>";
			}
		}
	echo "</tr></table>";
?>
</tr></table></td></tr></table>
<?
  // Save data back to database.
  page_close()
 ?>
</body>
</html>