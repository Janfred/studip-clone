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
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once("lib/classes/Table.class.php");

checkObject(); // do we have an open object?
checkObjectModule("scm");
object_set_visit_module("scm");

$msg=""; // Message to display 


switch ($i_view) {
	case "edit":
		include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
		scm_edit_content($SessSemName[1]);
		break;
	case "change":
		scm_change_content($scm_id, $SessSemName[1], $scm_name, $scm_preset, $content, $new_entry);
		$msg="msg§"._("Die Änderungen wurden übernommen.");
	default:
		include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");
		scm_show_content($SessSemName[1], $msg);
		break;
}


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

function scm_seminar_header($range_id, $site_name) 
{
	$t=new Table();
	$t->setTableWidth("100%");
	echo $t->open();
	echo $t->openRow();
	echo $t->openCell(array("class"=>"topic", "width"=>"100%"));
	echo "<b>&nbsp;";
	echo "<img src=\"pictures/icon-cont.gif\" align=absmiddle>&nbsp; ";
	echo getHeaderLine($range_id) . " - $site_name</b>";
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

function scm_change_header($table, $titel, $user_id, $chdate) {
	$titel;
	$zusatz = "<font size=-1>";
	$zusatz .= sprintf(_("Zuletzt ge&auml;ndert von %s am %s"), "</font><a href=\"about.php?username=".get_username($user_id)."\"><font size=-1 color=\"#333399\">".get_fullname ($user_id)."</font></a><font size=-1>", date("d.m.Y, H:i",$chdate)."<font size=-1>&nbsp;"."</font>");
	$icon="&nbsp;<img src=\"pictures/cont_cont.gif\">";

	echo $table->openRow();
	echo $table->openCell(array("colspan"=>"2"));
	$head_table=new Table(array("width"=>"100%"));
	echo $head_table->openRow();
	printhead(0, 0, false, "open", FALSE, $icon, $titel, $zusatz);
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

function scm_show_content($range_id, $msg) {
	global $rechte, $PHP_SELF;
	$result=scm_get_content($range_id);
	
	$header_table=scm_seminar_header($range_id, $result["tab_name"]);

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

	if (($result["content"]) || ($rechte)) {
		scm_change_header($content_table, $result["tab_name"], $result["user_id"], $result["chdate"]);
		echo $content_table->openRow();
		echo $content_table->openCell();
		$printcontent_table=new Table(array("width"=>"100%"));
		echo $printcontent_table->open();
		if ($rechte) {
			$edit = "<a href=\"$PHP_SELF?i_view=edit\">".makeButton("bearbeiten")."</a>";
		} else {
			$edit = "";
		}
		printcontent(0,0, formatReady($result["content"]), $edit);	
		echo $printcontent_table->close();
		echo $content_table->closeRow();
	} else {
		parse_msg("info§<font size=-1><b>". _("In diesem Bereich wurden noch keine Inhalte erstellt.") . "</b></font>", "§", "steel1", 2, FALSE);
	}
	echo $content_table->close();
	echo $frame_table->row(array("&nbsp;"));
	echo $frame_table->close();
	echo $header_table->close();
}

function scm_edit_content($range_id) {
	global $perm, $PHP_SELF, $SCM_PRESET;
	$result=scm_get_content($range_id);
	$max_col=scm_max_cols();
	
	$header_table=scm_seminar_header($range_id, $result["tab_name"]);
	
	print("<form action=\"$PHP_SELF\" method=\"POST\">");

	$frame_table=new Table();
	$frame_table->setTableWidth("100%");
	$frame_table->setCellClass("blank");
	echo $frame_table->openCell();
	
	print("<blockquote>");
	print(_("Hier k&ouml;nnen Sie eine Seite mit Zusatzinformationen zu Ihrer Veranstaltung gestalten. Sie können Links normal eingeben, diese werden anschlie&szlig;end automatisch als Hyperlinks dargestellt."));
	print("</blockquote>");

	$content_table=new Table();
	$content_table->setTableWidth("99%");
	$content_table->setTableAlign("center");
	$content_table->setCellClass("printcontent");
	echo $content_table->open();
	if ($msg) {
		parse_msg($msg);
	}
	$titel="</b><input style=\"font-size:8 pt;\" type=\"TEXT\" name=\"scm_name\" value=\"".$result["tab_name"]."\" maxlength=\"20\" size=\"20\" />";
	$titel.="</font size=\"-1\">&nbsp;"._("oder w&auml;hlen Sie hier einen Namen aus:")."&nbsp;\n";
	$titel.="<select style=\"font-size:8 pt;\" name=\"scm_preset\">";
	$titel.="<option value=\"0\">- "._("Vorlagen")." -</option>\n";
	foreach ($SCM_PRESET as $key=>$val) 
		$titel.=sprintf("<option value=\"%s\">%s</option>\n", $key, $val["name"]);
	$titel.="</select>";

	scm_change_header($content_table, $titel, $result["user_id"], $result["chdate"]);
	
	$content="<textarea name=\"content\" style=\"width: 90%\" cols=$max_col rows=10 wrap=virtual >".htmlReady($result["content"])."</textarea>\n";
	if (!$result)
		$content.="<input type=\"HIDDEN\" name=\"new_entry\" value=\"1\"><b>\n";
	$content.= "<input type=\"HIDDEN\" name=\"scm_id\" value=\"$scm_id\">";
	$content.= "<input type=\"HIDDEN\" name=\"i_view\" value=\"change\">";
	
	$edit="<input style=\"vertical-align: middle;\" type=\"IMAGE\" name=\"send_scm\" value=\"&auml;nderungen vornehmen\" border=0 " . makeButton("uebernehmen", "src") . ">";
	$edit.="&nbsp;<a href=\"$PHP_SELF\">". makeButton("abbrechen") . "</a>";
	$edit .= "<font size=\"-1\">&nbsp;&nbsp;<a href=\"show_smiley.php\" target=\"new\">";
	$edit .= "Smileys</a>&nbsp;&nbsp;<a href=\"help/index.php?help_page=ix_forum6.htm\" ";
	$edit .= "target=\"new\">Formatierungshilfen</a></font>\n";
	
	echo $content_table->openRow();
	echo $content_table->openCell();
	$printcontent_table=new Table(array("width"=>"100%"));
	echo $printcontent_table->open();
	printcontent(0,0, $content, $edit);	
	echo $printcontent_table->close();
	echo $content_table->closeRow();
	echo $content_table->close();
	echo $frame_table->row(array("&nbsp;"));
	echo $frame_table->close();
	echo $header_table->close();
	
	print("</form>");
}

function scm_change_content($scm_id, $range_id, $name, $preset, $content, $new_entry) {
	global $user, $perm, $SCM_PRESET;
	$db = new DB_Seminar;
	if (!$perm->have_studip_perm("tutor",$range_id)) {
		echo "</tr></td></table>";
		return;
	}
	
	if ($preset)
		$tab_name = $SCM_PRESET[$preset]["name"];
	else
		$tab_name = $name;
	
	if ($new_entry) {
		$scm_id=md5(uniqid("simplecontent"));
		$db->query("INSERT INTO scm SET scm_id = '$scm_id', range_id='$range_id', user_id='$user->id', tab_name='$tab_name', content='$content', mkdate='".time()."', chdate='".time()."'");
		if ($db->affected_rows())
			$result="msg§" . _("Inhalt ge&auml;ndert");
	} else {
		$db->query("UPDATE scm SET user_id='$user->id', tab_name='$tab_name', content='$content' WHERE range_id='$range_id'");
		if ($db->affected_rows()) {
			$result="msg§" . _("Inhalt ge&auml;ndert");
		 	$db->query("UPDATE scm SET chdate='".time()."' WHERE range_id='$range_id'");
		}
	}
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

