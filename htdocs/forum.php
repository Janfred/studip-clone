<?
/*
forum.php - Anzeige und Verwaltung des Forensystems
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	if ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben, dazu auch JS Check auf Name
?>
<SCRIPT language="JavaScript">
<!--
function pruefe_name(){
 var re_nachname = /^([a-zA-ZÄÖÜ][^0-9"´'`\/\\\(\)\[\]]+)$/;
 var checked = true;
 if (re_nachname.test(document.forumwrite.nobodysname.value)==false) {
 	alert("Bitte geben Sie Ihren tatsächlichen Namen an.");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
  if (document.forumwrite.nobodysname.value=="unbekannt") {
 	alert("Bitte geben Sie Ihren Namen an.");
 	document.forumwrite.nobodysname.focus();
 	checked = false;
 	}
 return checked;
}
// -->
</SCRIPT>
<?
}

	if ($forum["jshover"]==1 AND $auth->auth["jscript"]) { // JS an und erwuenscht?
		echo "<script language=\"JavaScript\">";
		echo "var ol_textfont = \"Arial\"";
		echo "</script>";
		echo "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
		echo "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"overlib.js\"></SCRIPT>";
	}

	require_once "functions.php";
	require_once "visual.inc.php";
	require_once "forum.inc.php";
	require_once "msg.inc.php";
	require_once "dates.inc.php"; 

	checkObject();


if ($view)
	$forum["view"] = $view;

if (!$forum["view"]) {
	$view = "tree";
	$forum["view"] = $view;
}

if ($forum["view"]=="flatfolder" && $view)
	$forum["flatfolder"] = $open;

$view = $forum["view"];



	include "links_openobject.inc.php";
	

// Sind wir da wo wir hinwollen?

if ($topic_id AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo "<br><br>";
		parse_window ("error§" . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . "<br /><font size=-1 color=black>" . _("Um unerw&uuml;nschte Effekte - wie falsch einsortierten Postings - zu vermeiden,<br>empfehlen wir, Stud.IP nur in einem Browserfenster zu verwenden.") . "</font>", "§",
				_("zuviele Browserfenster im Forenbereich!"), 
				"");
		die;
	}
} elseif ($open AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$open' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo "<br><br>";
		parse_window ("error§" . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . "<br /><font size=-1 color=black>" . _("Um unerw&uuml;nschte Effekte - wie falsch einsortierten Postings - zu vermeiden,<br>empfehlen wir, Stud.IP nur in einem Browserfenster zu verwenden.") . "</font>", "§",
				_("zuviele Browserfenster im Forenbereich!"), 
				"");
		die;
	}
}

// Rekursives Löschen von Postings, Warnung
IF ($delete_id) {
	$db=new DB_Seminar;
	$mutter = suche_kinder($delete_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$delete_id' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$root = $db->f("root_id");
			echo "\n\n<table class=\"blank\" cellspacing=0 cellpadding=5 border=0 width=\"100%\"><colgroup span=1></colgroup>\n";
			$msg="info§" . sprintf(_("Wollen Sie das untenstehende Posting %s von %s wirklich l&ouml;schen?"), "<b>".htmlReady($db->f("name"))."</b>", "<b>".$db->f("author")."</b>") . "<br>\n";
			if ($count)
				$msg.= sprintf(_("Alle %s Antworten auf diesen Beitrag werden ebenfalls gel&ouml;scht!"), $count) . "<br />\n<br />\n";
			$msg.="<a href=\"".$PHP_SELF."?really_kill=$delete_id&view=$view#anker\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
			$msg.="<a href=\"".$PHP_SELF."?topic_id=$root&open=$topic_id&view=$view&mehr=$mehr#anker\">" . makeButton("nein", "img") . "</a>\n";
			parse_msg($msg, '§', 'blank', '1', FALSE);
			echo "</table>";

		// Darstellung des zu loeschenden Postings
	
			echo "<table width=\"100%\" class=blank border=0 cellpadding=0 cellspacing=0 align=center><tr><td class=blank><br><br>";	
			echo "<table width=\"80%\" class=blank border=0 cellpadding=0 cellspacing=0 align=center><tr>";	
	
			$forumposting["id"] = $db->f("topic_id");
			$forumposting["name"] = $db->f("name");
			$forumposting["description"] = $db->f("description");
			$forumposting["author"] = $db->f("author");
			$forumposting["username"] = $db->f("username");
			$forumposting["rootid"] = $db->f("root_id");
			$forumposting["rootname"] = $db->f("root_name");
			$forumposting["mkdate"] = $db->f("mkdate");
			$forumposting["chdate"] = $db->f("chdate");
			$forumposting["buttons"] = "no";
			printposting($forumposting);
			echo "<br></td></tr></table>\n<br></td></tr></table>";	
			page_close();
			die;
		}
	}
} else {
	$forumposting["buttons"] = "yes";
}


// Verschieben von Postings

IF ($cmd == "move" && $topic_id !="" && $rechte) {
	$mutter = suche_kinder($topic_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	
	// wohin darf ich schieben? Abfragen je nach Rechten
	
	IF ($perm->have_perm("tutor") OR $perm->have_perm("dozent"))
		$query = "SELECT DISTINCT seminare.Seminar_id, seminare.Name FROM seminar_user LEFT JOIN seminare USING(Seminar_id) WHERE user_id ='$user->id ' AND (seminar_user.status = 'tutor' OR seminar_user.status = 'dozent') ORDER BY Name";
	IF ($perm->have_perm("admin"))
		$query = "SELECT seminare.* FROM user_inst LEFT JOIN Institute USING (Institut_id) LEFT JOIN seminare USING(Institut_id) LEFT OUTER JOIN seminar_user USING(Seminar_id) WHERE user_inst.inst_perms='admin' AND user_inst.user_id='$user->id' AND seminare.Institut_id is not NULL GROUP BY seminare.Seminar_id ORDER BY seminare.Name";
	IF ($perm->have_perm("root"))
		$query = "SELECT Seminar_id, Name FROM seminare ORDER BY Name";
	$db=new DB_Seminar;
	$db->query($query);

	if ($perm->have_perm("tutor") OR $perm->have_perm("dozent") OR $perm->have_perm("admin")) {
		$query2 = "SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING(Institut_id) WHERE user_id = '$user->id' AND (inst_perms = 'tutor' OR inst_perms = 'dozent' OR inst_perms = 'admin') ORDER BY Name";	
		$db2=new DB_Seminar;
		$db2->query($query2);
	}
	if ($perm->have_perm("root")) {
		$query2 = "SELECT Institut_id, Name FROM Institute ORDER BY Name";
		$db2=new DB_Seminar;
		$db2->query($query2);
	}


?>	<table class="steel1" width="100%" cellpadding="0" cellspacing="0" border="0">
			<tr>
				<td class="steel2" colspan="2">
					&nbsp; <img src="pictures/move.gif" border="0">&nbsp;<b><font size="-1"><?=sprintf(_("Als Thema verschieben (zusammen mit %s Antworten):"), $count)?></font></b>
				</td>
			</tr>
			<tr>
				<td class="steel1" colspan="2">
					&nbsp; 
				</td>
			</tr>
			<tr>
				<td class="steel1" align="right" nowrap width="20%" valign="baseline">
					<font size="-1"><?=_("in anderes Forum:")?></font>&nbsp; &nbsp; 
				</td>
				<td class="steel1" width="80%">
			<? 		echo "<form action=\"".$PHP_SELF."\" method=\"POST\">"; ?>
					<input type="image" name="SUBMIT" value="Verschieben" src="pictures/move.gif" border="0" <?=tooltip(_("dahin verschieben"))?>>&nbsp; 					
					<select Name="sem_id" size="1">
			<?		while ($db->next_record()) {
						$sem_name=htmlReady(substr($db->f("Name"), 0, 50));
						printf ("<option %s value=\"%s\">%s\n", $db->f("Seminar_id") == $SessSemName[1] ? "selected" : "", $db->f("Seminar_id"), $sem_name);
					}
			?>	</select>
					<input type="HIDDEN" name="target" value="Seminar">
					<input type="HIDDEN" name="topic_id" value="<?echo $topic_id;?>">
					<input type="HIDDEN" name="view" value="<?echo $view;?>">
		  		</form>
				</td>
			</tr>
			<?
		if ($db2->num_rows()) {   // Es kann auch in Institute verschoben werden
		?>
			<tr>
				<td class="steel1" align="right" nowrap width="20%" valign="baseline">
			  		<font size="-1"><?=_("in andere Einrichtung:")?></font>&nbsp; &nbsp; 
			  	</td>
				<td class="steel1" width="80%">
			<? 		echo "<form action=\"".$PHP_SELF."\" method=\"POST\">"; ?>
					<input type=image name="SUBMIT" value="Verschieben" src="pictures/move.gif" border=0 <?=tooltip(_("dahin verschieben"))?>>&nbsp; 						
			  	<select Name="inst_id" size="1">
			<?		while ($db2->next_record()) {
						$inst_name=htmlReady(substr($db2->f("Name"), 0, 50));
						printf ("<option value=\"%s\">%s\n", $db2->f("Institut_id"), $inst_name);
					}
			?>	</select>
					<input type="HIDDEN" name="target" value="Institut">
					<input type="HIDDEN" name="topic_id" value="<?echo $topic_id;?>">
					<input type="HIDDEN" name="view" value="<?echo $view;?>">
		  		</form>
				</td>
			</tr>
		<?
		}
		?>
			<tr valign="middle">
				<td class="steel1" align="right" nowrap width="20%">
					&nbsp; 
				</td>
				<td class="steel1" width="80%">					
					<br>

			  	<? echo "<a href=\"".$PHP_SELF."?view=".$view.">".makeButton("abbrechen", "img")."</a>";?>
		  		</td>
  			</tr>
  		</table>
<?		
	}
	

	
if ($target =="Seminar"){ //Es soll in ein anderes Seminar verschoben werden 
	$verschoben = 0;
	move_topic($topic_id,$sem_id,$topic_id,$verschoben);
	echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	parse_msg("msg§" . sprintf(_("%s Posting(s) verschoben."), $verschoben));
	echo "</table>";
	}
	
if ($target =="Institut"){ //Es soll in ein Institut verschoben werden 
	$verschoben = 0;
	move_topic($topic_id,$inst_id,$topic_id,$verschoben);
	echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	parse_msg("msg§" . sprintf(_("%s Posting(s) verschoben."), $verschoben));
	echo "</table>";
	}

if ($target =="Thema"){ //Es soll in ein anderes Thema verschoben werden 
	$verschoben = 0;
	move_topic2($move_id,$move_id,$verschoben,$parent_id);
	echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	parse_msg("msg§" . sprintf(_("%s Posting(s) verschoben."), $verschoben));
	echo "</table>";
	}












// Rekursives Löschen von Postings, jetzt definitiv!

if ($really_kill) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$really_kill' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		$mutter = suche_kinder($really_kill);
		$mutter = explode (";",$mutter);
		$count = sizeof($mutter)-2;
		$open = $db->f("root_id");
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$count = 0;
			delete_topic($really_kill, $count);
			$db->next_record();
			IF ($nurneu!=1) { // es wurde wirklich was gel&ouml;scht und nicht nur ein Anlegen unterbrochen
				echo "<table class=\"blank\" cellspacing=0 cellpadding=0 border=0 width=\"100%\">";
				parse_msg("msg§" . sprintf(_("%s Posting(s) gel&ouml;scht"), $count));
				echo "</table>";
			}
			
		}		
	}
}

// neuer Beitrag als Antwort wird eingeleitet

if ($answer_id) {
	$db=new DB_Seminar;
	$db->query("SELECT name, topic_id, root_id FROM px_topics WHERE topic_id = '$answer_id'");
	while($db->next_record()){
		$name = $db->f("name");
		if (substr($name,0,3)!="Re:")
			$name = "Re: ".$name; // Re: vor Überschriften bei Antworten
		$author = get_fullname();
		$postinginhalt = _("Dieser Beitrag wird gerade bearbeitet.");
		$edit_id = CreateTopic (addslashes($name), $author, $postinginhalt, $answer_id, $db->f("root_id"));
		$open = $edit_id;
	}
}

if ($zitat==TRUE)
	$zitat = $answer_id;
	
if ($edit_id) 
	$open = $edit_id;

IF ($update) {
	$author = get_fullname();
	$now = date ("d.m.y - H:i", time());
	IF (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
		$postmp = strpos($description,"editiert von");
		$description = substr_replace($description,"editiert von ".$author." am ".$now."]%%",$postmp);
	} ELSE {
		IF (ForumFreshPosting($update)==FALSE) // editiert von nur dranhängen wenn nicht frisch erstellt
			$description.="\n\n%%[editiert von ".$author." am ".$now."]%%";
	}
	UpdateTopic ($titel, $update, $description);
	$open = $update; //gerade bearbeiteten Beitrag aufklappen
}

IF ($neuesthema==TRUE && $rechte) {			// es wird ein neues Thema angelegt
		$name = _("Name des Themas");
		$author = get_fullname();
		$edit_id = CreateTopic ($name, $author, "Beschreibung des Themas", "0", "0");
		$open = $edit_id;
}

// Anker setzen

	$forum["anchor"] = $open;

//Titel-Zeile
if (!$forumsend=="anpassen") {
	echo "\n<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
	echo "<tr><td class=\"topic\" width=\"95%\"><b>&nbsp;<img src='pictures/icon-posting.gif' align=absmiddle>&nbsp; ". $SessSemName["header_line"] ." - " . _("Forum") . "</b></td><td class=\"topic\" width=\"5%\" align=\"right\"><a href='forum.php?forumsend=anpassen'><img src='pictures/pfeillink.gif' border=0 " . tooltip(_("Look & Feel anpassen")) . ">&nbsp;</a></td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=2>&nbsp;";
	echo " &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size=-1>Indikator: 
	<img src='pictures/forumrot.gif'> Alter 
	<img src='pictures/forumgrau.gif'> Views 
	<img src='pictures/forumgrau.gif'> Bewertung 
	<img src='pictures/forumgrau.gif'> Relevanz 
	</font></td></tr>\n";
	echo "</table>\n";
}

// Verzweigung zu den drei Anzeigemodi 

if ($flatallopen=="TRUE")
	$forum["flatallopen"] = "TRUE";
if ($flatallopen=="FALSE")
	$forum["flatallopen"] = "FALSE";

if ($forum["view"]=="flat" || $forum["view"]=="neue" || $forum["view"]=="flatfolder")
 	flatview ($open, $mehr, $show, $edit_id, $name, $description, $zitat);
else
	DisplayFolders ($open, $edit_id, $zitat);


  // Save data back to database.
  page_close()
 ?>
</body>
</html>