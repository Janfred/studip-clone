<?php

/*
scm.php - Simple Content Module von Stud.IP
Copyright (C) 2000 André Noack <anoack@mcis.de>, Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>, 2003 Tobias Thelen <tthelen@uni-osnabrueck.de>

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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("lib/classes/Table.class.php");

checkObject(); // do we have an open object?
checkObjectModule("scm");

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
	

function scm_max_cols() 
{
	global $auth;
	//maximale spaltenzahl berechnen
	if ($auth->auth["jscript"]) {
		return round($auth->auth["xres"] / 12 );
	} else {
		return 64 ; //default für 640x480
	}
}

function scm_seminar_header($SessSemName) 
{
	$t=new Table();
	$t->setTableWidth("100%");
	echo $t->open();
	echo $t->openRow();
	echo $t->openCell(array("class"=>"topic", "width"=>"100%"));
	echo "<b>&nbsp;";
	echo "<img src=\"pictures/icon-cont.gif\" align=absmiddle>&nbsp; ";
	echo $SessSemName["header_line"] . "</b>";
	echo $t->closeCell();
	echo $t->closeRow();
	echo $t->openRow();
	echo $t->blankCell(array("class"=>"blank"));
	echo $t->openRow();
	echo $t->openCell(array("class"=>"blank"));
	return $t; // Cell is left open, content will be printed elsewhere
}

function scm_seminar_footer($table) {
	echo $table->close(); // close open cell, row and table
}

function scm_change_header($table, $user_id, $chdate) {
	$zusatz = "<font size=-1>";
	$zusatz .= sprintf(_("Zuletzt ge&auml;ndert von %s am %s"), "</font><a href=\"about.php?username=".get_username($username)."\"><font size=-1 color=\"#333399\">".get_fullname ($user_id)."</font></a><font size=-1>", date("d.m.Y, H:i",$chdate)."<font size=-1>&nbsp;"."</font>");
	$icon="&nbsp;<img src=\"pictures/cont_lit.gif\">";

	echo $table->openRow();
	echo $table->openCell(array("colspan"=>"2"));
	$head_table=new Table(array("width"=>"100%"));
	echo $head_table->openRow();
	printhead(0, 0, false, "open", FALSE, "", "", $zusatz);
	echo $head_table->close();
	echo $table->closeRow();
}

function scm_get_content($range_id) {
	$result=array();
	$db=new DB_Seminar;
	$db->query("SELECT * FROM scm WHERE range_id='$range_id'");
	if ($db->num_rows()) {
		$db->next_record();
		$result["content"]=$db->f("content");
		$result["user_id"]=$db->f("user_id");
		$result["chdate"]=$db->f("chdate");
		$result["tab_name"]=$db->f("tab_name");
	}
	return $result;
}

function scm_show_editbutton($table, $range_id) {
	global $perm, $PHP_SELF;
	if ($perm->have_studip_perm("tutor", $range_id)) {
		echo $table->openRow();
		echo $table->openCell(array("align"=>"center"));
		echo "&nbsp;<br><a href=\"$PHP_SELF?i_view=edit\">";
		echo "<img " . makeButton("bearbeiten","src") . " border=\"0\">";
		echo "</a>";
		echo $table->closeRow();
	}
}

function scm_show_content($SessSemName, $msg) {
	$range_id=$SessSemName[1];

	$header_table=scm_seminar_header($SessSemName);

	$frame_table=new Table();
	$frame_table->setTableWidth("100%");
	$frame_table->setCellClass("blank");
	echo $frame_table->openCell();

	$content_table=new Table();
	$content_table->setTableWidth("99%");
	$content_table->setTableAlign("center");
	$content_table->setCellClass("printcontent");
	echo $content_table->open();
	if ($msg) {
		parse_msg($msg);
	}
	$result=scm_get_content($range_id);
	if ($result) {
		scm_change_header($content_table, $result["user_id"], $result["chdate"]);
		scm_show_editbutton($content_table, $range_id);
		echo $content_table->openRow();
		echo $content_table->openCell();
		$printcontent_table=new Table(array("width"=>"100%"));
		echo $printcontent_table->open();
		printcontent(0,0, formatReady($result["content"]), FALSE);	
		echo $printcontent_table->close();
		echo $content_table->closeRow();
	} else {
		parse_msg("info§<font size=-1><b>" . sprintf(_("In dieser %s wurde noch kein Content angelegt."), $SessSemName["art_generic"]) . "</b></font>", "§", "steel1", 2, FALSE);
	}
	echo $content_table->close();
	echo $frame_table->row(array("&nbsp;"));
	echo $frame_table->close();
	echo $header_table->close();
}

function scm_edit_content($range_id) {
	global $perm, $PHP_SELF;
	$db=new DB_Seminar();
	$db2=new DB_Seminar();

	//Sicherheitscheck ob was zum Bearbeiten gewaehlt ist.
	if (!$perm->have_studip_perm("tutor",$range_id)) {
		echo "</tr></td></table>";
		return;
	}

	$max_col=scm_max_cols();

	$table=new Table(array("width"=>"100%"));
	$table->setCellClass("blank");
	$table->setCellColspan("2");
	echo $table->openCell(array("class"=>"topic"));
	echo "&nbsp;<b>";
	echo getHeaderLine($range_id) . " -  " . _("Frei gestaltbare Seite");
	echo "</b>";
	echo $table->closeRow();
	echo $table->row(array("&nbsp;"));

	parse_msg($result);

	echo $table->openCell();
	echo "<blockquote>";
	echo _("Hier k&ouml;nnen Sie eine Seite mit Zusatzinformationen zu Ihrer Veranstaltung gestalten. Sie können Links normal eingeben, diese werden anschlie&szlig;end automatisch als Hyperlinks dargestellt.");
	echo "</blockquote>";

	print("<form action=\"$PHP_SELF\" method=\"POST\">");

	$innerTable=new Table(array("width"=>"99%", "align"=>"center", "cellpadding"=>"2"));
	echo $innerTable->open();
	echo $innerTable->setCellColspan("2");
	echo $innerTable->setCellAlign("center");
	echo $innerTable->setCellWidth("100%");

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
	echo $innerTable->openRow(array("valign"=>"top", "align"=>"left"));
	echo $innerTable->openCell(array("align"=>"left"));
	echo _("Bezeichnung des Reiters:");
	echo " <input type=\"TEXT\" name=\"tab_name\" size=20 maxlength=20 value=\"". $db->f("tab_name") . "\"> <br>&nbsp;<br>";
	echo $innerTable->closeRow();
	echo $innerTable->openCell();
	echo"<textarea  name=\"content\"  style=\"width: 100%\" cols=$max_col rows=10 wrap=virtual >$content</textarea>";
	echo $innerTable->closeRow();
	echo $innerTable->openRow();
	echo $innerTable->openCell(array("width" => "50%", "align" => "left", "valign" => "bottom", "class" => "steel1", "colspan" => "1"));
	echo "&nbsp;<br>" . _("&Auml;nderungen");
	echo " <input type=\"IMAGE\" align=\"absmiddle\" name=\"send_button\" value=\"Änderungen vornehmen\" border=0 " . makeButton("uebernehmen", "src") . ">";
	echo "&nbsp; &nbsp; <a href=\"$PHP_SELF\"><img " . makeButton("abbrechen", "src") . " align=\"absmiddle\" border=\"0\"></a>";
	echo $innerTable->openCell(array("width" => "50%", "align" => "center", "class" => "steel1", "colspan" => "1"));
	echo"&nbsp;<br><font size=-1>" . _("Eingestellt von:") . " <b><a href=\"about.php?username=".$db2->f("username")."\">$autor</a></b>";
	if ($db->f("chdate"))
		print(", " . _("letzte &Auml;nderung am") . " ".date("d.m.y", $db->f("chdate"))." " . _("um") . " " . date("H:i", $db->f("chdate")));
	echo "</font>";
	echo $innerTable->close();
	echo "<input type=\"HIDDEN\" name=\"scm_id\" value=\"$scm_id\">";
	echo "<input type=\"HIDDEN\" name=\"new_entry\" value=\"$new_entry\">";
	echo "<input type=\"HIDDEN\" name=\"i_view\" value=\"change\">";
	print("</form>");
	echo "<br><br>";
	echo $table->close();
}

function scm_change_content($scm_id, $range_id, $user_id, $tab_name, $content, $new_entry) {
	global $perm;
	if (!$perm->have_studip_perm("tutor",$range_id)) {
		echo "</tr></td></table>";
		return;
	}
		
	$db=new DB_Seminar();
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

$msg=""; // Message to display 
switch ($i_view) {
	case "edit":
		scm_edit_content($SessSemName[1]);
		break;
	case "change":
		scm_change_content($scm_id, $SessSemName[1], $user->id, $tab_name, $content, $new_entry);
		$msg="msg§"._("Die Änderungen wurden übernommen.");
	default:
		scm_show_content($SessSemName, $msg);
		break;
}

echo "</td></tr></table>";

?>
</body>
</html>
<?php
  // Save data back to database.
  page_close();
?>
<!-- $Id$ -->

