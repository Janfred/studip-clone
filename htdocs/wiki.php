<?php

/*
wiki.php - Einfaches WikiWikiWeb in Stud.IP

@module wiki
@author Tobias Thelen <tthelen@uos.de>

Copyright (C) 2003 Tobias Thelen <tthelen@uni-osnabrueck.de>
Contains code (regex for WikiWord detection) from Blast Wiki http://www.roboticboy.com/wiki/ (GPL'd)
Contains code (diff routine) from PukiWiki http://www.pukiwiki.org (GPL'd)

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
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

checkObject(); // do we have an open object?
checkObjectModule("wiki"); //are we allowed to use this module here?

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");

require_once("$ABSOLUTE_PATH_STUDIP/wiki.inc.php");

echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>\n";

$db=new DB_Seminar;
$username=$auth->auth['uname'];
$db->query("SELECT * FROM auth_user_md5  WHERE username ='$username'");
$db->next_record();
$user_id=$db->f("user_id");

$begin_blank_table="<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
$end_blank_table="</tr></table>";

wikiSeminarHeader();

// ---------- Start of main WikiLogic

if ($view=="listall") {
	listPages("all", $sortby);
} else if ($view=="listnew") {
	listPages("new", $sortby);
} else if ($view=="diff") {
		$db = new DB_Seminar;
		$q = "SELECT * FROM wiki WHERE ";
		$q .= "keyword = '$keyword' AND range_id='$SessSemName[1]' ";
		$q .= "ORDER BY version DESC";
		$result = $db->query($q);
		if ($db->affected_rows() == 0) {
        echo $begin_blank_table;
		    parse_msg ("info\xa7" . _("Es gibt keine zu vergleichenden Versionen."));
        echo $end_blank_table;
		    echo "</td></tr></table></body></html>";
		    die;
		}
		wikiSinglePageHeader($wikiData, $keyword);

		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
		$db->next_record();
		$last = $db->f("body");
		$lastversion = $db->f("version");
		$zusatz=getZusatz($db->Record);
		while ($db->next_record()) {
			echo "<tr>";
			$current = $db->f("body");
			$currentversion = $db->f("version");
			$diffarray = "<b><font size=-1>Änderungen zu </font> $zusatz</b><p>";
			$diffarray .= "<table cellpadding=0 cellspacing=0 border=0 width=\"100%\">\n";
			$diffarray .= do_diff($current, $last);
			$diffarray .= "</table>\n";
			printcontent(0,0, $diffarray, "");
			echo "</tr>";
			$last = $current;
			$lastversion = $currentversion;
			$zusatz=getZusatz($db->Record);
		}
		echo "</table>     ";
		echo "</td></tr></table>";
		echo "<p><font size=-2>".getWikiPageVersions($keyword)."</font></p>";
   
} else if ($view=="edit") {
    if (!$perm->have_perm("autor")) {
 		    echo $begin_blank_table;
		    parse_msg("error§" . _("Sie haben keine Berechtigung, Seiten zu editieren!"));
 		    echo $end_blank_table;
		    echo "</td></tr></table></body></html>";
		    die;
    }
		//show form
		$wikiData=getWikiPage($keyword,$version);

		// set lock
		$db=new DB_Seminar;
		$result=$db->query("REPLACE INTO wiki_locks (user_id, range_id, keyword, version, chdate) VALUES ('$user->id', '$SessSemName[1]', '$keyword', '$wikiData[version]', '".time()."')");

		wikiSinglePageHeader($wikiData, $keyword);
    wikiEdit($keyword, $wikiData);

} else if ($view=='editnew') { // edit a new page
    if (!$perm->have_perm("autor")) {
 		    echo $begin_blank_table;
		    parse_msg("error§" . _("Sie haben keine Berechtigung, Seiten zu editieren!"));
 		    echo $end_blank_table;
		    echo "</td></tr></table></body></html>";
		    die;
    }
		// set lock
		$db=new DB_Seminar;
		$result=$db->query("INSERT INTO wiki_locks (user_id, range_id, keyword, version, chdate) VALUES ('$user->id', '$SessSemName[1]', '$keyword', '0', '".time()."')");
		wikiSinglePageHeader($wikiData, $keyword);
    wikiEdit($keyword, NULL, $lastpage);

} else {
	if (empty($keyword)) { $keyword='StartSeite'; } // display Start page as default FIX: I18n?
	releaseLocks($keyword); // kill old locks 
	$show_page=TRUE;
	
	if ($submit) { 
		// write changes to db, show new page
		$latestVersion=getWikiPage($keyword,"");
    if ($latestVersion) {
  		$date=time();
  		$lastchange = $date - $latestVersion[chdate];
    }
		// print "<p>version=$version, lastchange=$lastchange, user=$user->id, changeuser=$latestVersion[user_id]</p>";
		if ($latestVersion && ($latestVersion['body'] == $body)) {
      echo $begin_blank_table;
			parse_msg("info§" . _("Keine Änderung vorgenommen."));
      echo $end_blank_table;
		} else if ($latestVersion && ($version!="") && ($lastchange < 30*60) && ($user->id == $latestVersion[user_id])) {
			// if same author changes again within 30 minutes,
			// no new verison is created 
			$db=new DB_Seminar;
			$result=$db->query("UPDATE wiki SET body='$body', chdate='$date' WHERE keyword='$keyword' AND range_id='$SessSemName[1]' AND version='$version'");
      echo $begin_blank_table;
			parse_msg("info§" . _("Update ok, keine neue Version, da erneute Änderung innerhalb 30 Minuten."));
      echo $end_blank_table;
		} else {
			if ($version=="") {
				$version=0;
			} else {
				$version=$version+1;
			}
			$date=time();
			$db=new DB_Seminar;
			$result=$db->query("INSERT INTO wiki (range_id, user_id, keyword, body, chdate, version) VALUES ('$SessSemName[1]', '$user->id', '$keyword','$body','$date','$version')");
			echo $begin_blank_table;
			parse_msg("info§" . _("Update ok, neue Version angelegt."));
      echo $end_blank_table; 
		}
		$show_page=TRUE;
		$version=""; // $version="" means: get latest and display edit button

  // -----------------------------------
  // Editing page was aborted
  //
  } else if ($cmd=="abortedit") { // Editieren abgebrochen
    releasePageLock($keyword); // kill lock that was set when starting to edit
    if ($lastpage) { // if editing new page was aborted, display last page again
      $keyword=$lastpage;
    }
    $showpage=true;

  // -----------------------------------
  // Delete request was sent -> display confirmation dialog and current page
  //
  } else if ($cmd=="delete") {
    if (!$perm->have_perm("dozent")) {
 		    echo $begin_blank_table;
		    parse_msg("error§" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
 		    echo $end_blank_table;
		    echo "</td></tr></table></body></html>";
		    die;
    }
    if ($version=="latest") {
      $lv=latestVersion($keyword);
      $version=$lv["version"];
      $islatest=true;
    }
    echo $begin_blank_table;
		$msg="info§" . sprintf(_("Wollen Sie die untenstehende Version %s der Seite %s wirklich l&ouml;schen?"), "<b>".$version."</b>", "<b>".$keyword."</b>") . "<br>\n";
    if ($islatest) {
      $msg .= _("Diese Version ist die derzeit aktuelle. Nach dem L&ouml;schen wird die n&auml;chst&auml;ltere Version aktuell.") . "<br>";
    } else {
      $msg .= _("Diese Version ist nicht die aktuellste. Das L&ouml;schen wirkt sich daher nicht auf die aktuelle Version aus.") . "<br>";
    }    
		$msg.="<a href=\"".$PHP_SELF."?cmd=really_delete&keyword=$keyword&version=$version\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
    $lnk = "?keyword=$keyword"; // what to do when delete is aborted
    if (!$islatest) $lnk .= "&version=$version"; 
    $msg.="<a href=\"".$PHP_SELF."$lnk\">" . makeButton("nein", "img") . "</a>\n";
    parse_msg($msg, '§', 'blank', '1', FALSE);
    echo $end_blank_table;

  // -------------------------------
  // Delete was confirmed -> really delete
  //
  } else if ($cmd=="really_delete") {

    if (!$perm->have_perm("dozent")) {
 		    echo $begin_blank_table;
		    parse_msg("error§" . _("Sie haben keine Berechtigung, Seiten zu l&ouml;schen."));
 		    echo $end_blank_table;
		    echo "</td></tr></table></body></html>";
		    die;
    }
    $q="DELETE FROM wiki WHERE keyword='$keyword' AND version='$version' AND range_id='$SessSemName[1]'";
    $db->query($q);
    if (!keywordExists($keyword)) { // all versions have gone
      $addmsg = sprintf(_("<br>Damit ist die Seite mit allen Versionen gel&ouml;scht."));
      $newkeyword = "StartSeite";
    } else {
      $newkeyword = $keyword;
      $addmsg = "";
    }
    echo $begin_blank_table;
		parse_msg("info§" . sprintf(_("Version %s der Seite <b>%s</b> gel&ouml;scht."), $version, $keyword) . $addmsg);
    echo $end_blank_table;
    $version=""; // show latest version
    $keyword=$newkeyword;
    $show_page=true;    
  }
  
	/****************************************************************
	 Show Page
	 ****************************************************************/
	if ($show_page) {
		$wikiData = getWikiPage($keyword, $version);
		wikiSinglePageHeader($wikiData, $keyword);

		if ($perm->have_perm("autor")) { 
			if ($version!="") {
				if ($perm->have_perm("dozent")) {
					$edit="<a href=\"?keyword=$keyword&cmd=delete&version=$version\"><img ".makeButton("loeschen","src")." border=\"0\"></a>";
				} else {
					$edit="Ältere Version, nicht bearbeitbar!";
				}
			} else {
				$edit="";
        if ($perm->have_perm("autor")) {
  				$edit.="<a href=\"?keyword=$keyword&view=edit\"><img ".makeButton("bearbeiten","src")." border=\"0\"></a>";
        }
				if ($perm->have_perm("dozent")) {
					$edit.="&nbsp;<a href=\"?keyword=$keyword&cmd=delete&version=latest\"><img ".makeButton("loeschen","src")." border=\"0\"></a>";
				}
			}
			$edit .= "<br>&nbsp;";
		} else {
			$edit="";
		}

		echo $begin_blank_table;
		echo "<tr class=\"printcontent\">";
		echo "<td class=\"printcontent\" width=\"22\">&nbsp;&nbsp;</td>";
		echo "<td class=\"printcontent\" align=\"center\">&nbsp;<br>\n";
		echo $edit;
		echo $end_blank_table; 

		echo $begin_blank_table;
		echo "<tr>\n";
		$cont = wikiLinks(wikiReady($wikiData["body"]), $keyword);
		printcontent(0,0, $cont, $edit);
		echo $end_blank_table; 

		echo "</td></tr></table>";
		echo "<p>&nbsp;<font size=-2>".getWikiPageVersions($keyword)."</font></p>";
	}
}

// Save data back to database.
page_close()
?>
<!-- $Id$ -->
