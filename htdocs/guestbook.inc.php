<?
/*
object.inc.php - Verwaltung des Objecttrackings
Copyright (C) 2003 Ralf Stockmann <rstockm@gwdg.de>

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
function print_guestbook ($range_id) {
	global $user;
	if ($range_id == $user->id)
		if (check_guestbook($range_id)==TRUE)
			$active = " ("._("aktiviert").")";
		else
			$active = " ("._("deaktiviert").")";
			
	echo "<table class=\"blank\" width=\"100%%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td class=\"topic\"><img src=\"pictures/nutzer.gif\" valign=\"absmiddle\"><b>&nbsp;" . _("Gästebuch").$active." </b></td></tr></table>";
	echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
	printhead ("100%","0",$link,$forumposting["openclose"],$new,$forumposting["icon"],getnum_guestbook($range_id)."&nbsp;"._(" Einträge"),$zusatz,$forumposting["chdate"],"TRUE",$index,$forum["indikator"]);	
	echo "</tr></table>";
	echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr><td>";
	if (check_guestbook($range_id)==TRUE) {
		$content = show_posts_guestbook($range_id);
		$content .= form_guestbook($range_id);
	}
	if ($range_id == $user->id)
		$buttons = buttons_guestbook($range_id);
	else
		$buttons = "";
	printcontent ("100%",$formposting,$content,$buttons,TRUE,"");
	echo "</td></tr></table>";
}

function show_posts_guestbook ($range_id) {
	global $PHP_SELF, $user, $username;
	$db=new DB_Seminar;
	$output = "<table class=\"blank\" width=\"98%%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">";
	$db->query("SELECT * FROM guestbook WHERE range_id = '$range_id' ORDER BY mkdate DESC");
	while ($db->next_record()) {  
		$output .= "<tr><td class=\"steel2\"><b><font size=\"-1\"><a href=\"$PHP_SELF?username=".get_username($db->f("user_id"))."\">".get_fullname($db->f("user_id"))."</a>&nbsp;"._("hat am")."&nbsp;".date("d.m.Y - H:i", $db->f("mkdate"))."&nbsp;"._("geschrieben:")."</font></b></td></tr>"
			. "<tr><td class=\"steelgraulight\"><font size=\"-1\">".formatready($db->f("content"))."</font><p align=\"right\">";
		if ($range_id == $user->id)
			$addon = "<a href=\"".$PHP_SELF."?deletepost=".$db->f("post_id")."&username=$username#anker\">" . makeButton("loeschen", "img") . "</a>";
		else
			$addon = "&nbsp;";
			
		$output .= $addon
			."</p></td></tr>"
			. "<tr><td class=\"steel1\">&nbsp;</td></tr>";
	}
	$output .= "</table>";
	return $output;	
}

function form_guestbook($range_id) {
	global $auth, $PHP_SELF, $username;
	if ($auth->auth["jscript"]) {
		$max_col = round($auth->auth["xres"] / 12 );
	} else 
		$max_col =  64 ; //default für 640x480
	$cols = round($max_col*0.45);
	if ($cols < 28) $cols = 28;
	$text = "<p align=\"center\">"._("Geben Sie hier Ihren Gästebuchbeitrag ein!")."</p>";
	
	$form =	"<form name=\"guestbook\" method=\"post\" action=\"".$PHP_SELF."#anker\">"
		."<input type=hidden name=guestbook value='$range_id'>"
		."<input type=hidden name=username value='$username'>"
		.$text
		."<div align=center><textarea name=post style=\"width:70%\" cols=\"". $cols."\"  rows=8 wrap=virtual>"
		."</textarea>"
		."<br><br><input type=image name=create value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0>&nbsp;<br>";
	return $form;
}

function delete_post_guestbook ($range_id, $post_id) {
	global $user;
	$db=new DB_Seminar;
	if ($range_id == $user->id) {
		$db->query("DELETE FROM guestbook WHERE post_id = '$post_id'");	
	}	
}

function erase_guestbook ($range_id) {
	global $user;
	$db=new DB_Seminar;
	if ($range_id == $user->id) {
		$db->query("DELETE FROM guestbook WHERE range_id = '$range_id'");	
	}	
}


function buttons_guestbook ($range_id) {
	global $PHP_SELF, $username;
	$buttons = "";
	if (check_guestbook($range_id) == "TRUE") {
		$buttons .= "&nbsp;&nbsp;<a href=\"".$PHP_SELF."?guestbook=switch&username=$username&rnd=".rand()."#anker\">" . makeButton("entfernen", "img") . "</a>";
	} else {
		$buttons .= "<a href=\"".$PHP_SELF."?guestbook=switch&username=$username#anker\">" . makeButton("anlegen", "img") . "</a>";
	}
	$buttons .= "&nbsp;&nbsp;<a href=\"".$PHP_SELF."?guestbook=erase&username=$username#anker\">" . makeButton("alleloeschen", "img") . "</a>";
	return $buttons;	
}

function actions_guestbook($guestbook) {
	global $user, $post, $create;
	if ($guestbook=="switch")
		$msg = switch_guestbook($user->id);
	if ($guestbook=="erase")
		erase_guestbook ($user->id);
	if ($post) {
		$msg = add_post_guestbook($guestbook,$post);
	}
}

function add_post_guestbook($range_id,$content) {
	global $user;
	$now = time();
	$post_id = makeunique_guestbook();
	$user_id = $user->id;
	$db=new DB_Seminar;
	$db->query("INSERT INTO guestbook (post_id,range_id,user_id,mkdate,content) values ('$post_id', '$range_id', '$user_id', '$now', '$content')");	
	return $post_id;
}

function check_guestbook ($range_id) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM user_info WHERE user_id = '$range_id' AND guestbook = '1'");
	if ($db->next_record())  // Guestbook is aktivatet
		$tmp = TRUE;
	else
		$tmp = FALSE;
	return $tmp;	
}

function getnum_guestbook ($range_id) {
	$db=new DB_Seminar;
	$db->query("SELECT count(*) as count FROM guestbook WHERE range_id = '$range_id'");
	if ($db->next_record())  
		$count = $db->f("count");
	else
		$count = 0;
	return $count;	
}

function makeunique_guestbook ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kershfshsshdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));
	$db->query ("SELECT post_id FROM guestbook WHERE post_id = '$tmp_id'");	
	if ($db->next_record()) 	
		$tmp_id = MakeUniqueID(); //ID gibt es schon, also noch mal
	return $tmp_id;
}

function switch_guestbook ($range_id) {
	$db=new DB_Seminar;
	if (check_guestbook($range_id) == "TRUE") { // Guestbook is activated
		$db->query("UPDATE user_info SET guestbook='0' WHERE user_id='$range_id'");
		$tmp = _("Sie haben Ihr Gästebuch deaktiviert. Es ist nun nicht mehr sichtbar.");
	} else { 
		$db->query("UPDATE user_info SET guestbook='1' WHERE user_id='$range_id'");
		$tmp = _("Sie haben Ihr Gästebuch aktiviert: Besucher können nun schreiben!");
	}
	return $tmp;
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>