<?php
/*
admin_scm.php - Simple Content Module in Stud.IP
Copyright (C) 2000 André Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>, 2003 Tobias Thelen <tthelen@uos.de>

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
$perm->check("tutor");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins

require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

$db=new DB_Seminar;
$db2=new DB_Seminar;


//get ID 
if ($SessSemName[1])
	$range_id=$SessSemName[1];


//Sicherheitscheck ob was zum Bearbeiten gewaehlt ist.
if (!$perm->have_studip_perm("tutor",$range_id)) {
	echo "</tr></td></table>";
	die;
}

//maximale spaltenzahl berechnen
if ($auth->auth["jscript"])
	$max_col = round($auth->auth["xres"] / 12 );
else
	$max_col =  64 ; //default für 640x480


if($aendern && $range_id) {
	if ($new_entry) {
		$db->query("INSERT INTO scm VALUES ('$scm_id','$range_id','$user->id','$tab_name','$content', '".time()."', '".time()."')");
		if ($db->affected_rows())
			$result="msg§" . _("Inhalt ge&auml;ndert");
	} else {
		$db->query("UPDATE scm SET user_id='$user->id', tab_name='$tab_name', content='$content' WHERE scm_id='$scm_id'");
		if ($db->affected_rows()) {
			$result="msg§" . _("Inhalt ge&auml;ndert");
		 	$db->query("UPDATE scm SET chdate='".time()."' WHERE scm_id='$scm_id'");
		}
	}
}

?>
<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td class="topic" colspan=2>&nbsp;<b>
<?
echo getHeaderLine($range_id) . " -  " . _("Simple Content Modul");
?></b></td></tr>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>
<?

parse_msg($result);

print ("<tr><td class=\"blank\" colspan=2><blockquote>");
print(_("Hier k&ouml;nnen Sie eine Seite mit Zusatzinformationen zu Ihrer Veranstaltung gestalten. Sie können Links normal eingeben, diese werden anschlie&szlig;end automatisch als Hyperlinks dargestellt."));
print("</blockquote>");
print("<form action=\"$PHP_SELF\" method=\"POST\">");
?><table border="0" cellpadding="2" cellspacing="0" width="99%" align="center">
    <tr valign="top" align="center">
    <th width="100%" colspan=2 class="steelgraudunkel"><?=_("Content")?></th>
    </tr>
<?
$db->query("SELECT * FROM scm WHERE range_id='$range_id'");
if (!$db->num_rows()) {
	$new_entry=TRUE;
	$hash_secret = "blafasel28";
	$scm_id=md5(uniqid($hash_secret));
	$autor=get_fullname();
} else {
	$db->next_record();
	$db2->query("SELECT username FROM auth_user_md5 WHERE user_id = '".$db->f("user_id")."'");
	$db2->next_record();
	$new_entry=FALSE;
	$content=htmlReady($db->f("content"));
	$scm_id=$db->f("scm_id");
	$autor=get_fullname($db->f("user_id"));
}
echo"<tr><td width=\"100%\" align=\"center\" colspan=2>";
echo _("Bezeichnung des Reiters:") . " <input type=\"TEXT\" name=\"tab_name\" size=20 maxlength=20 value=\"". $db->f("tab_name") . "\">";
echo "</td></tr>";
echo"<tr><td width=\"100%\" align=\"center\" colspan=2>";
echo"<textarea  name=\"content\"  style=\"width: 100%\" cols=$max_col rows=10 wrap=virtual >$content</textarea></td></tr>";
echo"<tr><td width=\"50%\" align=\"left\" class=\"steel1\">&nbsp; ";
printf(_("&Auml;nderungen %s"), "<input type=\"IMAGE\" align=\"absmiddle\" name=\"send_button\" value=\"Änderungen vornehmen\" border=0 " . makeButton("uebernehmen", "src") . "></td>");
echo"<td width=\"50%\" align=\"center\" class=\"steel1\"><font size=-1>" . _("Eingestellt von:") . " <b><a href=\"about.php?username=".$db2->f("username")."\">$autor</a></b>";
if ($db->f("chdate"))
	print(", " . _("letzte &Auml;nderung am") . " ".date("d.m.y", $db->f("chdate"))." " . _("um") . " " . date("H:i", $db->f("chdate")));
echo "</font>";
echo"<input type=\"HIDDEN\" name=\"range_id\" value=\"$range_id\">";
echo"<input type=\"HIDDEN\" name=\"scm_id\" value=\"$scm_id\">";
echo"<input type=\"HIDDEN\" name=\"new_entry\" value=\"$new_entry\">";
echo"<input type=\"HIDDEN\" name=\"aendern\" value=\"TRUE\">";	  
echo"</td></tr>";
echo"</table><br><br>";

page_close();
 ?>
</body>
</html>
