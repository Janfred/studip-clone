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

	include "links_openobject.inc.php";
	
////////////////////////////////////////////////////////////////////
/*
Variablen, die uebergeben werden:

$view (tree, letzte5, neue)
$mehr (nur in letzte 5)
$topic_id (ID des Bretts - nur im tree)
$open_id (welcher ist offen)
$write_id (welcher wird geschrieben)
#anker

*/
////////////////////////////////////////////////////////////////////


// Sind wir da wo wir hinwollen?

if ($topic_id AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo "<br><br>";
		parse_window ("error§" . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . "<br /><font size=-1 color=black>" . _("Um unerw&uuml;nschte Effekten wie falsch einsortierten Postings zu vermeiden,<br>empfehlen wir im System nur ein Browserfenster zu verwenden.") . "</font>", "§",
				_("zuviele Browserfenster im Forenbereich"), 
				"");
		die;
	}
} elseif ($open AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$open' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
		echo "<br><br>";
		parse_window ("error§" . _("Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.") . "<br /><font size=-1 color=black>" . _("Um unerw&uuml;nschte Effekten wie falsch einsortierten Postings zu vermeiden,<br>empfehlen wir im System nur ein Browserfenster zu verwenden.") . "</font>", "§",
				_("zuviele Browserfenster im Forenbereich"), 
				"");
		die;
	}
}

//Titel-Zeile
if (!$forumsend=="anpassen") {
	echo "\n<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
	echo "<tr><td class=\"topic\" width=\"95%\"><b>&nbsp;<img src='pictures/icon-posting.gif' align=absmiddle>&nbsp; ". $SessSemName["header_line"] ." - " . _("Forum") . "</b></td><td class=\"topic\" width=\"5%\" align=\"right\"><a href='forum.php?forumsend=anpassen'><img src='pictures/pfeillink.gif' border=0 " . tooltip(_("Look & Feel anpassen")) . ">&nbsp;</a></td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=2>&nbsp; </td></tr>\n";
	echo "</table>\n";
}


// Rekursives Löschen von Postings, Warnung
IF ($cmd == "kill" && $topic_id !="") {
	$db=new DB_Seminar;
	$mutter = suche_kinder($topic_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$root = $db->f("root_id");
			echo "\n\n<table class=\"blank\" cellspacing=0 cellpadding=5 border=0 width=\"100%\"><colgroup span=1></colgroup>\n";
			$msg="info§" . sprintf(_("Wollen Sie das untenstehende Posting %s von %s wirklich l&ouml;schen?"), "<b>".htmlReady($db->f("name"))."</b>", "<b>".$db->f("author")."</b>") . "<br>\n";
			if ($count)
				$msg.= sprintf(_("Alle %s Antworten darauf werden ebenfalls gel&ouml;scht!"), $count) . "<br />\n<br />\n";
			$msg.="<a href=\"".$PHP_SELF."?cmd=really_kill&topic_id=$topic_id&view=$view&mehr=$mehr#anker\">" . makeButton("ja2", "img") . "</a>&nbsp; \n";
			$msg.="<a href=\"".$PHP_SELF."?topic_id=$root&open=$topic_id&view=$view&mehr=$mehr#anker\">" . makeButton("nein", "img") . "</a>\n";
			parse_msg($msg, '§', 'blank', '1', FALSE);
			echo "</table>";

		// Darstellung des zu loeschenden Postings
	
			$parent_description = formatReady($db->f("description")); // erst mal die Anzeige des zu l&ouml;schenden Postings verschoenern...
		  	IF (ereg("\[quote",$parent_description) AND ereg("\[/quote\]",$parent_description))
				$parent_description = quotes_decode($parent_description);
			echo "<table width=\"100%\" class=blank border=0 cellpadding=0 cellspacing=0 align=center><tr><td class=blank><br><br>";	
			echo "<table width=\"80%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";	
			$icon = NTForum("topic",$topic_id,"","",$neuer_beitrag,$db->f("root_id"));			
			printhead ("100%","0","","close","",$icon,(mila(htmlReady($db->f("name")))),"");			
			echo "</tr></table>\n";	
			echo "<table width=\"80%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";	
			printcontent ("100%","",$parent_description,"");
			echo "</tr></table>\n<br><br></td></tr></table>";	
			page_close();
			die;
		}
	}
}

// loeschen von nicht zuende getippten Postings

if ($writemode!="" AND !isset($update)) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$writemode' AND Seminar_id ='$SessSemName[1]' and (description = 'Dieser Beitrag wird gerade bearbeitet.' OR description = 'Beschreibung des Themas')");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$count = 0;
		delete_topic($writemode,$count);
		$writemode="";
	}
}

// Rekursives Löschen von Postings, jetzt definitiv!

if ($cmd == "really_kill" && $topic_id !="") {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		$mutter = suche_kinder($topic_id);
		$mutter = explode (";",$mutter);
		$count = sizeof($mutter)-2;
		if ($rechte || (($db->f("user_id") == $user->id) && ($count == 0))) {  // noch mal checken ob alles o.k.
			$count = 0;
			delete_topic($topic_id, $count);
			$db->next_record();
			$topic_id = $db->f("root_id");
			IF ($nurneu!=1) { // es wurde wirklich was gel&ouml;scht und nicht nur ein Anlegen unterbrochen
				echo "<table class=\"blank\" cellspacing=0 cellpadding=0 border=0 width=\"100%\">";
				parse_msg("msg§" . sprintf(_("%s Posting(s) gel&ouml;scht"), $count));
				echo "</table>";
			}
		}		
	}
}

// Verschieben von Postings

IF ($cmd == "move" && $topic_id !="" && $rechte) {
	$back = $open;
	$mutter = suche_kinder($topic_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	$open = $back;
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
					<form action="forum.php" method="POST">
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
					<form action="forum.php" method="POST">
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

			  		<a href="forum.php?view=<?echo $view;?>"><?=makeButton("abbrechen", "img")?></a>

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

// Einbau der Forumsetting-Ansicht

if ($forumsend) {
	
	if ($forumsend=="bla"){
		$forum=array(
			"jshover"=>$jshover, 
			"neuauf"=>$neuauf,
			"changed"=>"TRUE"			
			);
	} ELSE
		include("forumsettings.inc.php");
}

// Anzeige der Topics
if (!isset($topic_id) AND $view=="") {  // wir sind in der Themen-Auflistung
	$topic_id = "0";
	} 
	

// in $open wird alles reingepack, was aufgeklappt  ist:
if (isset($open) AND !$write AND $view=="" AND $all!=TRUE){
	$open=suche_kinder($open);
	} 
	

IF ($write){			// es wird ein neuer Beitrag gepostet
	$db=new DB_Seminar;
	$db->query("SELECT name, topic_id, root_id FROM px_topics WHERE topic_id = '$write'");
	while($db->next_record()){
		$name = $db->f("name");
		$author = get_fullname();
		$postinginhalt="Dieser Beitrag wird gerade bearbeitet.";
		$open = CreateTopic (addslashes($name), $author, $postinginhalt, $write, $db->f("root_id"));
		$write =$open;
		$writemode=$open;
		}
	}
	
IF ($neuesthema==TRUE){			// es wird ein neues Thema angelegt
		$name = _("Name des Themas");
		$author = get_fullname();
		$open = CreateTopic ($name, $author, "Beschreibung des Themas", "0", "0");
		$write =$open;
		$writemode=$open;
		$topic_id=$open;
		}
	

// Bearbeiten eines Beitrags

IF ($update) {
	IF ($nichtneu==TRUE) {
		$author = get_fullname();
		$now = date ("d.m.y - H:i", time());
		IF (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
			$postmp = strpos($description,"editiert von");
			$description = substr_replace($description,"editiert von ".$author." am ".$now."]%%",$postmp);
			}
		ELSE $description.="\n\n%%[editiert von ".$author." am ".$now."]%%";
		}
	UpdateTopic ($titel, $update, $description);
	$writemode="";
	}

// Verzweigung zu den drei Anzeigemodi 

IF ($view=="letzte" OR $view=="neue"){
	IF ($mehr<1) $mehr=1;
	letzte5($open, $mehr, $show, $write, $update, $name, $description,$zitat);
	}
ELSE
	DisplayTopic($datum,$topic_id,$open,0,0,$zitat);
	
  // Save data back to database.
  page_close()
 ?>
</body>
</html>
