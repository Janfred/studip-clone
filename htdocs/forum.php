<?
/*
folder.php - Anzeige und Verwaltung des Ordnersystems
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
?>

<html>

<head>
<?IF (!isset($SessSemName[0]) || $SessSemName[0] == "") {
    echo "<META HTTP-EQUIV=\"refresh\" CONTENT=\"0; URL=index.php\">";
    echo "</head></html>";
    die;
}

IF  ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben, dazu auch JS Check auf Name
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
?>

<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
</head>
<? // <body bgcolor="#333366" background="pictures/bathtile.jpg">	

IF ($forum["jshover"]==1 AND $auth->auth["jscript"]) { // JS an und erwuenscht?
	echo "<script language=\"JavaScript\">";
	echo "var ol_textfont = \"Arial\"";
	echo "</script>";
	ECHO "<DIV ID=\"overDiv\" STYLE=\"position:absolute; visibility:hidden; z-index:1000;\"></DIV>";
	ECHO "<SCRIPT LANGUAGE=\"JavaScript\" SRC=\"overlib.js\"></SCRIPT>";
	}

	include "seminar_open.php"; //hier werden die sessions initialisiert

// -- hier muessen Seiten-Initialisierungen passieren

	include "header.php";   //hier wird der "Kopf" nachgeladen
	include "links1.php";
	require_once "functions.php";
	require_once "visual.inc.php";
	require_once "forum.inc.php";
	require_once "msg.inc.php";
	require_once "dates.inc.php"; 
	
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


//laden der persoenlichen Eintraege/Einstellungen aus dem Sessionmanagement
$user->register("writemode");

// Sind wir da wo wir hinwollen?

IF ($topic_id AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
	echo "<br><br>";
	parse_window ("error§Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.<br /><font size=-1 color=black>Um unerw&uuml;nschte Effekten wie falsch einsortierten Postings zu vermeiden,<br>empfehlen wir im System nur ein Browserfenster zu verwenden.</font>", "§",
				"zuviele Browserfenster im Forenbereich", 
				"");
	die;
		}
	}
ELSEIF ($open AND !$update) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$open' AND Seminar_id ='$SessSemName[1]'");
	if (!$db->num_rows()) { // wir sind NICHT im richtigen Seminar!
	echo "<br><br>";
	parse_window ("error§Sie versuchen, mit zwei Browserfenstern innerhalb verschiedener Foren zu navigieren.<br /><font size=-1 color=black>Um unerw&uuml;nschte Effekten wie falsch einsortierten Postings zu vermeiden,<br>empfehlen wir im System nur ein Browserfenster zu verwenden.</font>", "§",
				"zuviele Browserfenster im Forenbereich", 
				"");
	die;
		}
	}

//Titel-Zeile
IF (!$forumsend=="anpassen") {
	echo "\n<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
	echo "<tr><td class=\"topic\" width=\"99%\"><b>&nbsp;<img src='pictures/icon-posting.gif' align=absmiddle>&nbsp; ". htmlReady($SessSemName["art"]) .": ". htmlReady($SessSemName[0])." - Forum</b></td><td class=\"topic\" width=\"1%\" align=\"right\"><a href='forum.php?forumsend=anpassen'><img src='pictures/pfeillink.gif' border=0 alt='Look & Feel anpassen'>&nbsp;</a></td></tr>\n";
	echo "<tr><td class=\"blank\"  colspan=2>&nbsp; </td></tr>\n";
	echo "</table>\n";
	}

//Sind wir wirklich da, wo wir vogeben zu sein?
IF ($SessSemName[1] =="")
	{
	parse_window ("error§Sie haben kein Objekt gew&auml;hlt. <br /><font size=-1 color=black>Dieser Teil des Systems kann nur genutzt werden, wenn Sie vorher ein Objekt gew&auml;hlt haben.<br /><br /> Dieser Fehler tritt auch auf, wenn Ihre Session abgelaufen ist. Wenn sie sich länger als $AUTH_LIFETIME Minuten nicht im System bewegt haben, werden Sie automatisch abgemeldet. Bitte nutzen Sie in diesem Fall den untenstehenden Link, um zurück zur Anmeldung zu gelangen. </font>", "§",
				"Kein Objekt gew&auml;hlt", 
				"<a href=\"index.php\"><b>&nbsp;Hier</b></a> geht es wieder zur Anmeldung beziehungsweise Startseite.<br />&nbsp;");
	die;
	}

// Rekursives Löschen von Postings, Warnung
IF ($cmd == "kill" && $topic_id !="" && $rechte) {
	$db=new DB_Seminar;
	$mutter = suche_kinder($topic_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$db->next_record();
		$root = $db->f("root_id");
		echo "\n\n<table class=\"blank\" cellspacing=0 cellpadding=5 border=0 width=\"100%\"><colgroup span=1></colgroup>\n";
		$msg="info§Wollen Sie das untenstehende Posting <b>".htmlReady($db->f("name"))."</b> von <b>".$db->f("author")."</b> wirklich l&ouml;schen?<br>\n";
		if ($count)
			$msg.="Alle $count Antworten darauf werden ebenfalls gel&ouml;scht!<br />\n<br />\n";
		$msg.="<a href=\"".$PHP_SELF."?cmd=really_kill&topic_id=$topic_id&view=$view&mehr=$mehr#anker\"><img src=\"pictures/buttons/ja2-button.gif\" border=0 /></a>&nbsp; \n";
		$msg.="<a href=\"".$PHP_SELF."?topic_id=$root&open=$topic_id&view=$view&mehr=$mehr#anker\"><img src=\"pictures/buttons/nein-button.gif\" border=0 /></a>\n";
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
//		printf ("<tr><td class=blank><br><table width=80%% align=center border=0 cellpadding=5><tr><td class=\"steel1\">%s</td></tr></table><br />&nbsp;</td></tr></table>", $parent_description);
		page_close();
		die;
		}
	}

// loeschen von nicht zuende getippten Postings

IF ($writemode!="" AND !isset($update)) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$writemode' AND Seminar_id ='$SessSemName[1]' and (description = 'Dieser Beitrag wird gerade bearbeitet.' OR description = 'Beschreibung des Themas')");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$count = 0;
		delete_topic($writemode,$count);
		$writemode="";
		}
	}

// Rekursives Löschen von Postings, jetzt definitiv!

if ($cmd == "really_kill" && $topic_id !="" && $rechte) {
	$db=new DB_Seminar;
	$db->query("SELECT * FROM px_topics WHERE topic_id='$topic_id' AND Seminar_id ='$SessSemName[1]'");
	if ($db->num_rows()) { // wir sind im richtigen Seminar!
		$count = 0;
		delete_topic($topic_id, $count);
		$db->next_record();
		$topic_id = $db->f("root_id");
		IF ($nurneu!=1) { // es wurde wirklich was gel&ouml;scht und nicht nur ein Anlegen unterbrochen
			echo "<table class=\"blank\" cellspacing=0 cellpadding=0 border=0 width=\"100%\">";
			parse_msg("msg§$count Posting(s) gel&ouml;scht");
			echo "</table>";
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
?>		<table class=blank width="100%" cellpadding=0 cellspacing=0 border=0><tr><td class=blank>
		<form action="forum.php" method="POST">
		&nbsp;<b>Als Thema in anderes Forum verschieben (zusammen mit <?echo $count;?> Antworten):</b><br><br>&nbsp; 
  		<SELECT Name="sem_id" size="1">
<?		WHILE ($db->next_record()){
			$sem_name=htmlReady(substr($db->f("Name"), 0, 50));
			printf ("<option %s value=\"%s\">%s\n", $db->f("Seminar_id") == $SessSemName[1] ? "selected" : "", $db->f("Seminar_id"), $sem_name);
			}
?>		</select>
		<input type="HIDDEN" name="target" value="Seminar">
		<input type="HIDDEN" name="topic_id" value="<?echo $topic_id;?>">
		<input type="HIDDEN" name="view" value="<?echo $view;?>">
  		<input type=image name="SUBMIT" value="Verschieben" src="pictures/buttons/verschieben-button.gif" border=0>
  		<a href="forum.php?view=<?echo $view;?>"><img src="pictures/buttons/abbrechen-button.gif" border=0></a>
  		</form></td></tr></table>
<?		
	}
	
IF ($target =="Seminar"){ //Es soll in ein anderes Seminar verschoben werden 
	$verschoben = 0;
	move_topic($topic_id,$sem_id,$topic_id,$verschoben);
	echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	parse_msg("msg§$verschoben Posting(s) verschoben.");
	echo "</table>";
	}

IF ($target =="Thema"){ //Es soll in ein anderes Thema verschoben werden 
	$verschoben = 0;
	move_topic2($move_id,$move_id,$verschoben,$parent_id);
	echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	parse_msg("msg§$verschoben Posting(s) verschoben.");
	echo "</table>";
	}

// Einbau der Forumsetting-Ansicht

IF ($forumsend) {
	
	IF ($forumsend=="bla"){
		$forum=array(
			"jshover"=>$jshover, 
			"neuauf"=>$neuauf,
			"changed"=>"TRUE"			
			);
		}
	ELSE include("forumsettings.inc.php");
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
		$name = "Name des Themas";
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
</body></body>
</html>