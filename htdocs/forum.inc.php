<?
/*
forum.php - Anzeige und Verwaltung des Ordnersystems
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


//////////////////////////////////////////////////////////////////////////


function editarea($forumposting) {
	global $forum, $view, $user, $PHP_SELF;
	
	if ($forumposting["writestatus"] == "new") // Abbrechen Button unterscheidet ob Anlegen abgebrochen oder Bearbeiten abgebrochen
		$zusatz = "<a href=\"".$PHP_SELF."?really_kill=".$forumposting["id"]."&nurneu=1#anker\">" . makeButton("abbrechen", "img") . "</a>";
	else
		$zusatz = "<a href=\"".$PHP_SELF."?open=".$forumposting["rootid"]."#anker\">" . makeButton("abbrechen", "img") . "</a>";
	
	$zusatz .= "&nbsp;&nbsp;<a href=\"show_smiley.php\" target=\"new\">Smileys</a>&nbsp;&nbsp;"."<a href=\"help/index.php?help_page=ix_forum6.htm\" target=\"new\">Formatierungshilfen</a>";
	if ($forumposting["writestatus"] == "new") { // es ist ein neuer Beitrag, der Autor sieht dann:
		$description = _("Ihr Beitrag");
	} else {
		$description = $forumposting["description"];  // bereits bestehender Text 
	}
	
	if (ereg("%%\[editiert von",$forumposting["description"])) { // wurde schon mal editiert
		$postmp = strpos($forumposting["description"],"%%[editiert von");
		$description = substr_replace($forumposting["description"]," ",$postmp);
	}
	if ($forum["zitat"]!="") {
		$zitat = quote($forum["zitat"]);
		$description="";
	}
	if ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben
		$description =	"<b>" . _("Ihr Name:") . "</b>&nbsp; <input type=text size=50 name=nobodysname onchange=\"pruefe_name()\" value=\"" . _("unbekannt") . "\"><br><br><input type=hidden name=update value='".$forumposting["id"]."'>"
				."<div align=center><textarea name=description cols=80 rows=12>"
				.htmlReady($description)
				.htmlReady($zitat)
				."</textarea>";
	} else {
		$description =	"<input type=hidden name=update value='".$forumposting["id"]."'>"
				."<div align=center><textarea name=description cols=70 rows=12>"
				.htmlReady($description)
				.htmlReady($zitat)
				."</textarea>";
		}
	$description .= "<br><br><img src=\"pictures/blank.gif\" width=\"160\" height=\"1\"><input type=image name=create value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0>&nbsp;"
		.$zusatz
		."</div>";	
	return $description;
}

//////////////////////////////////////////////////////////////////////////

function MakeUniqueID ()
{	// baut eine ID die es noch nicht gibt

	$hash_secret = "kertoiisdfgz";
	$db=new DB_Seminar;
	$tmp_id=md5(uniqid($hash_secret));

	$db->query ("SELECT topic_id FROM px_topics WHERE topic_id = '$tmp_id'");	
	IF ($db->next_record()) 	
		$tmp_id = MakeUniqueID(); //ID gibt es schon, also noch mal
	RETURN $tmp_id;
}

//////////////////////////////////////////////////////////////////////////

function move_topic($topic_id, $sem_id, $root, &$verschoben)  //rekursives Verschieben von topics, in anderes Seminar
{
	$db=new DB_Seminar;
	$db->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
	IF ($db->num_rows()) {
		while ($db->next_record()) {
			$next_topic=$db->f("topic_id");
			move_topic($next_topic,$sem_id,$root,$verschoben);
			}
		}
	IF ($root == $topic_id)
		$db->query("UPDATE px_topics SET parent_id=0, root_id='$topic_id', Seminar_id='$sem_id' WHERE topic_id='$topic_id'");
 	ELSE
 		$db->query("UPDATE px_topics SET root_id='$root', Seminar_id='$sem_id' WHERE topic_id='$topic_id'");
 	$verschoben++;
 	return $verschoben;
}

////////////////////////////////////////////////////////////////////////////

function move_topic2($topic_id, $root, &$verschoben,$thema)  //rekursives Verschieben von topics, diesmal in ein Thema
{
	$db=new DB_Seminar;
	$db->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
	IF ($db->num_rows()) {
		while ($db->next_record()) {
			$next_topic=$db->f("topic_id");
			move_topic2($next_topic,$root,$verschoben,$thema);
			}
		}
	IF ($root == $topic_id)
		$db->query("UPDATE px_topics SET parent_id='$thema', root_id='$thema' WHERE topic_id='$topic_id'");
 	ELSE
 		$db->query("UPDATE px_topics SET root_id='$thema' WHERE topic_id='$topic_id'");
 	$verschoben++;
 	return $verschoben;
}

//////////////////////////////////////////////////////////////////////////

function lonely($topic_id)  //Sucht alle aufgeklappten Beitraege raus
{	global $user,$auth,$rechte;
	$lonely=TRUE;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db2->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
		IF (!$db2->num_rows()) {
			$db->query("SELECT user_id FROM px_topics WHERE topic_id='$topic_id'");
			IF ($db->num_rows())
				while ($db->next_record())
					IF ($db->f("user_id")==$user->id OR $rechte) 
						$lonely=FALSE;
			}
				
 	return $lonely;
}

/////////////////////////////////////////////////////////////////////////

function suche_kinder($topic_id)  //Sucht alle aufgeklappten Beitraege raus
{	global $open,$view;
	$db=new DB_Seminar;
	$db->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
	if ($db->num_rows()) {
		while ($db->next_record()) {
			$next_topic=$db->f("topic_id");
			suche_kinder($next_topic);
			}
		}
	$open .= ";".$topic_id;
 	return $open;
}

//////////////////////////////////////////////////////////////////////////


function ForumOpenClose ($forumposting) {
	global $forum, $openall, $open;
	if (strstr($forum["openlist"],$forumposting["id"])!=TRUE
	AND !($openall == "TRUE" && $forumposting["rootid"] == $open)
	AND !(($forum["view"]=="flat" || $forum["view"]=="neue" || $forum["view"]=="flat" || $forum["view"]=="flatfolder" || $forum["view"]=="search") && $forum["flatallopen"]=="TRUE")
	AND !($forumposting["newold"]=="new" && $forum["neuauf"]==1) 
	AND ($forumposting["writestatus"]=="none")) {
		$forumposting["openclose"] = "close";
	} else {
		$forumposting["openclose"] = "open";
	}
	return $forumposting;
}

function ForumNewPosting ($forumposting) {
	global $loginfilelast,$SessSemName;
	$datumtmp = $loginfilelast[$SessSemName[1]];
	if ($datumtmp < $forumposting["mkdate"]) {
		$forumposting["newold"] = "new";  //Beitrag neu
	} else {
		$forumposting["newold"] = "old";  //Beitrag alt
	}
	return $forumposting;	
}

function forum_lonely($forumposting) {  //Sieht nach ob das Posting kinderlos ist
	
	$topic_id = $forumposting["id"];
	$db=new DB_Seminar;
	$db->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
		if (!$db->num_rows())
			$forumposting["lonely"]=TRUE;
		else
			$forumposting["lonely"]=FALSE;
 	return $forumposting;
}

function ForumGetRoot($id) {  //Holt die ID des Root-Postings
	
	$db=new DB_Seminar;
	$db->query("SELECT root_id FROM px_topics WHERE topic_id='$id'");
	if ($db->next_record())
		$root_id = $db->f("root_id");
 	return $root_id;
}

function ForumGetParent($id) {  //Holt die ID des Parent-Postings (wird für Schreibanzeige gebraucht)
	
	$db=new DB_Seminar;
	$db->query("SELECT parent_id FROM px_topics WHERE topic_id='$id'");
	if ($db->next_record())
		$parent_id = $db->f("parent_id");
 	return $parent_id;
}

function ForumFreshPosting($id) {  //Sieht nach ob das Posting frisch angelegt ist (mkdate ist gleich chdate)
	$db=new DB_Seminar;
	$db->query("SELECT chdate, mkdate FROM px_topics WHERE topic_id='$id' AND chdate = mkdate");
	IF ($db->num_rows()) {
		$fresh = TRUE;
	} else {
		$fesh = FALSE;
	}
 	return $fresh;
}

function ForumFolderOrPosting ($forumposting) {
	if ($forumposting["id"]==$forumposting["rootid"]) {
		$forumposting["type"] = "folder";  //Beitrag ist ein folder
	} else {
		$forumposting["type"] = "posting";  //Beitrag alt
	}
	return $forumposting;	
}

function ForumGetWriteStatus($forumposting) {
	global $forum;
	if ($forumposting["id"] == $forum["update"]) {  			// das Posting ist im Schreibmodus
		if ($forumposting["chdate"] == $forumposting["mkdate"]) { 	// das Posting ist frisch angelegt und noch nicht geschrieben
			$forumposting["writestatus"] = "new";		
		} else { 					// das Posting wird editiert
			$forumposting["writestatus"] = "update";	
		}
	} else {						// das Posting ist nicht im Schreibmodus
		$forumposting["writestatus"] = "none";	
	}
	return $forumposting;	
}

function ForumGetRights ($forumposting) {
	global $user,$auth,$rechte;
	if ($forumposting["userid"]==$user->id || $rechte)
		$forumposting["perms"] = "write";
	else
		$forumposting["perms"] = "none";
	return $forumposting;
}

function ForumIcon ($forumposting) {
	global $cmd, $rechte, $topic_id, $PHP_SELF, $forum, $auth;
	if ($forumposting["type"]=="folder") {
		if ($forumposting["lonely"]==FALSE)
			$bild = "pictures/cont_folder.gif";
		else
			$bild = "pictures/cont_folder2.gif";
	} else {
		$bild = "pictures/cont_blatt.gif";
	}
	
	if ($forum["jshover"]==1 AND $auth->auth["jscript"] AND $forumposting["description"]!="" && $forumposting["openclose"]=="close") {      
		if ($forum["view"]=="tree" && $forumposting["type"]=="folder") { // wir kommen aus der Themenansicht
			$hoverlink = "<a href=\"".$PHP_SELF."?open=".$forumposting["id"]."&openall=TRUE#anker\" ";
			$txt = "<i>" . _("Hier klicken um alle Postings im Ordner zu &ouml;ffnen") . "</i>";
		} else {
			$hoverlink = "<a href=\"javascript:void(0);\" ";
			$txt = "";
		}
		$forumposting["icon"] =	$hoverlink
			."onMouseOver=\"return overlib('"
			.JSReady($forumposting["description"],"forum").$txt
			."', CAPTION, '&nbsp;"
			.JSReady($forumposting["name"])
			."', NOCLOSE, CSSOFF)\" "
			." onMouseOut=\"nd();\"><img src=\"".$bild."\" border=0></a>";
	} else {
		if ($forum["view"]=="tree" && $forumposting["type"]=="folder")
			$forumposting["icon"] = "<a href=\"".$PHP_SELF."?open=".$forumposting["id"]."&openall=TRUE#anker\"><img src=\"".$bild."\" border=0 " . tooltip(_("Alle Postings im Ordner öffnen")) . "></a>";
		else
			$forumposting["icon"] =	"<img src=\"".$bild."\">";	
	}
	
	if ($cmd=="move" && $rechte)  // ein Beitrag wird verschoben, gelbe Pfeile davor
		$forumposting["icon"] =	 "<a href=\"".$PHP_SELF."?target=Thema&move_id=".$topic_id."&parent_id=".$forumposting["id"]."\">"
					."<img src=\"pictures/move.gif\" border=0 " . tooltip(_("Postings in diesen Ordner verschieben")) . "></a>"
					.$forumposting["icon"];
	return $forumposting;
}

function quote($zitat_id)  {
// Hilfsfunktion, die sich den zu quotenden Text holt, encodiert und zurueckgibt.
	$db=new DB_Seminar;
	$db->query("SELECT description, author FROM px_topics WHERE topic_id='$zitat_id'");
		while ($db->next_record()) {
			$description = $db->f("description");
			$author = $db->f("author");
			}
	$zitat = quotes_encode($description,$author);
	RETURN $zitat;
}

function ForumGetName($id)  {
// Hilfsfunktion, die sich den Titel eines Beitrags holt
	$db=new DB_Seminar;
	$db->query("SELECT name FROM px_topics WHERE topic_id='$id'");
		if ($db->next_record())
			$name = $db->f("name");
	RETURN $name;
}

function forum_get_buttons ($forumposting) {
	global $rechte, $forum, $PHP_SELF, $user, $SessionSeminar;	

	{ if (!(have_sem_write_perm())) { // nur mit Rechten...	
		$edit = "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&flatviewstartposting=0&sort=age#anker\">&nbsp;" . makeButton("antworten", "img") . "</a>";
		$edit .= "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&zitat=TRUE&flatviewstartposting=0&sort=age#anker\">&nbsp;" . makeButton("zitieren", "img") . "</a>";
		if ($forumposting["lonely"]==TRUE && ($rechte || $forumposting["perms"]=="write")) // ich darf bearbeiten
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?edit_id=".$forumposting["id"]."&view=".$forum["view"]."&flatviewstartposting=".$forum["flatviewstartposting"]."#anker\">"
			. makeButton("bearbeiten", "img") . "</a>";
		if ($rechte || ($forumposting["lonely"]==TRUE && $forumposting["perms"]=="write")) // ich darf löschen
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?delete_id=".$forumposting["id"]."&view=".$forum["view"]."&flatviewstartposting=".$forum["flatviewstartposting"]."\">"
			. makeButton("loeschen", "img") . "</a>";
		if ($rechte)  // ich darf verschieben
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?cmd=move&topic_id=".$forumposting["id"]."&view=tree\">"
			. makeButton("verschieben", "img") . "</a>";
	} elseif ($user->id == "nobody") { 	// darf Nobody hier schreiben?
		$db=new DB_Seminar;
		$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id='$SessionSeminar' AND Schreibzugriff=0");
		if ($db->num_rows())  {
			$edit = "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&flatviewstartposting=0#anker\">&nbsp;" . makeButton("antworten", "img") . "</a>";
			$edit .= "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&zitat=TRUE&flatviewstartposting=0#anker\">&nbsp;" . makeButton("zitieren", "img") . "</a>";
		} else
			$edit=""; // war kein nobody Seminar
	} else 	// nix mit Rechten
		$edit = ""; 
	}
	return $edit;
}

function DebugForum ($debugvar) {
	global $HTTP_POST_VARS;
	while(list($key,$value) = each($debugvar)) 
		$debug .= "$key: $value<br>";
	$debug .= "<hr>";
	while(list($key,$value) = each($HTTP_POST_VARS)) 
		$debug .= "$key: $value<br>";
	return $debug;
}

function ForumEmpty () {
	global $rechte, $SessSemName;
	IF ($rechte)
		$text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Sie k&ouml;nnen oben unter <b>neues Thema</b> einen Ordner anlegen.");
	ELSE {
		if ($SessSemName["class"]=="inst")
			 $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Kontaktieren Sie eine/n TutorIn oder eine/n DozentIn dieser Veranstaltung, um Ordner anlegen zu lassen.");
		else
			 $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Kontaktieren Sie den/die AdministratorIn der Einrichtung, um Ordner anlegen zu lassen.");
	}
	$empty = "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	$empty .= parse_msg("info§$text");
	$empty .= "</table>";	
	return $empty;
} 

function ForumNoPostings () {
	global $forum, $PHP_SELF;
	if ($forum["view"] != "search")
		$text = _("In dieser Ansicht gibt es derzeit keine Beiträge.");
	else
		$text = _("Zu Ihrem Suchbegriff gibt es keine Treffer.<br><a href=\"".$PHP_SELF."?view=search&reset=1\">Neue Suche</a>");
	$empty .= parse_msg("info§$text");
	return $empty;
} 

// Berechnung und Ausgabe der Blätternavigation

function forum_print_navi ($forum) {
	$i = 1;
	$maxpages = ceil($forum["forumsum"] / $forum["postingsperside"]);
	$ipage = ($forum["flatviewstartposting"] / $forum["postingsperside"])+1;
	if ($ipage != 1)
		$navi .= "<a href=\"$SELF_PHP?flatviewstartposting=".($ipage-2)*$forum["postingsperside"]."\"><font size=-1>zurück </a> | </font>";
	else
		$navi .= "<font size=\"-1\">Seite: </font>";
	while ($i <= $maxpages) {
		if ($i == 1 || $i+2 == $ipage || $i+1 == $ipage || $i == $ipage || $i-1 == $ipage || $i-2 == $ipage || $i == $maxpages) {
			if ($space == 1) {
				$navi .= "<font size=-1>... | </font>";
				$space = 0;
			}
			if ($i != $ipage)
				$navi .= "<a href=\"$SELF_PHP?flatviewstartposting=".($i-1)*$forum["postingsperside"]."\"><font size=-1>".$i."</a></font>";
			else
				$navi .= "<font size=\"-1\"><b>".$i."</b></font>";
			if ($maxpages != 1)
				$navi .= "<font size=\"-1\"> | </font>";
		} else {
			$space = 1;
		}
		$i++;	
	}
	if ($ipage != $maxpages)
		$navi .= "<a href=\"$SELF_PHP?flatviewstartposting=".($ipage)*$forum["postingsperside"]."\"><font size=-1> weiter</a></font>";
	return $navi;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function CreateTopic ($name="[no name]", $author="[no author]", $description="", $parent_id="0", $root_id="0", $tmpSessionSeminar=0, $user_id=FALSE)

{	global $SessionSeminar,$auth, $PHP_SELF, $writeextern;
	if (!$tmpSessionSeminar)
		$tmpSessionSeminar=$SessionSeminar;
	$db=new DB_Seminar;
	$mkdate = time();
	if ($writeextern != TRUE)
		$chdate = $mkdate;
	else
		$chdate = $mkdate+1;
	if (!$user_id) {
		$db->query ("SELECT user_id , username FROM auth_user_md5 WHERE username = '".$auth->auth["uname"]."' ");
		while ($db->next_record())
			$user_id = $db->f("user_id");
	}
	$topic_id = MakeUniqueID();
	IF ($root_id == "0")	{
		$root_id = $topic_id;
		}
	$query = "INSERT INTO px_topics (topic_id,name,description, parent_id, root_id , author, author_host, Seminar_id, user_id, mkdate, chdate) values ('$topic_id', '$name', '$description', '$parent_id', '$root_id', '$author', '".getenv("REMOTE_ADDR")."', '$tmpSessionSeminar', '$user_id', '$mkdate', '$chdate') ";
	$db=new DB_Seminar;
	$db->query ($query);
	IF  ($db->affected_rows() == 0) {
		print "<p>"._("Fehler beim Anlegen eines Postings.")."</p>\n";
		}
	return $topic_id;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function UpdateTopic ($name="[no name]", $topic_id, $description)
{	global $user, $nobodysname;
	$db=new DB_Seminar;
	$chdate = time();
	IF ($user->id == "nobody")  // bei nobodys wird mit Namen geschrieben, ist sonst schon da
		$query = "UPDATE px_topics SET name = '$name', description = '$description', chdate= '$chdate', author='$nobodysname' WHERE topic_id = '$topic_id'";
	ELSE
		$query = "UPDATE px_topics SET name = '$name', description = '$description', chdate= '$chdate' WHERE topic_id = '$topic_id'";
	$db->query ($query);
	IF  ($db->affected_rows() == 0) {
		print _("<p>Aktualisieren des Postings fehlgeschlagen</p>\n");
		}
}

function ForumParseZusatz($forumhead) {
	
	while(list($key,$value) = each($forumhead)) 
		$zusatz .= $value;
	return $zusatz;
}

function ForumStriche($forumposting) {
	$striche = "<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer.gif'><img src='pictures/forumleer.gif'></td>";
	for ($i=0;$i<$forumposting["level"];$i++) {
		if ($forumposting["lines"][$i+1]==0) 
			$striche .= "<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer.gif'></td>";
		else 
			$striche .= "<td class=\"blank\" nowrap background='pictures/forumstrich.gif'><img src='pictures/forumleer2.gif'></td>";
	}
	if ($forumposting["lonely"]==FALSE)
		$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/forumleer.gif\"></td>";
	else 
		$striche.= "<td class=\"blank\" nowrap background=\"pictures/steel1.jpg\"><img src=\"pictures/forumleer.gif\"></td>";
	return $striche;
}

function forum_print_toolbar ($id="") {
		global $user, $PHP_SELF, $forum, $open, $flatviewstartposting;
		$print = "<table class=\"blank\" width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr><td class=\"blank\">";
		if ($forum["toolbar"] == "open") {
			$print .= "<form name=\"sortierung\" method=\"post\" action=\"".$PHP_SELF."\">";
			$print .= "<table class=\"blank\" width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr><td class=\"blank\">&nbsp;</td></tr><tr>";
			$print .= "<td class=\"steelkante\"><img src=\"pictures/blank.gif\" height=\"22\" width=\"5\"></td>";
			$print .= "<td class=\"steelkante\"><font size=\"-1\">Indikator:&nbsp;";
			
			if ($forum["indikator"] == "age")
				$print .=  "</td><td nowrap class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forumrot_indikator.gif\" align=\"middle\"><font size=\"-1\">"._("Alter")." &nbsp;";
			else
				$print .=  "</td><td nowrap class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=age\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Alter")."</a> &nbsp;";
			if ($forum["indikator"] == "views")
				$print .=  "</td><td nowrap class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forum_indikator_gruen.gif\" align=\"middle\"><font size=\"-1\">"._("Views")." &nbsp;";
			else
				$print .=  "</td><td nowrap class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=views\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Views")."</a> &nbsp;";
			if ($forum["indikator"] == "rate")
				$print .=  "</td><td nowrap class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forum_indikator_gelb.gif\" align=\"middle\"><font size=\"-1\">"._("Bewertung")." &nbsp;";
			else
				$print .=  "</td><td nowrap class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=rate\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Bewertung")."</a> &nbsp;";
			if ($forum["indikator"] == "score")
				$print .=  "</td><td nowrap class=\"steelgraulight_shadow\" valign=\"middle\">&nbsp;<img src=\"pictures/forum_indikator_blau.gif\" align=\"middle\"><font size=\"-1\">"._("Relevanz")." &nbsp;";
			else
				$print .=  "</td><td nowrap class=\"steelkante\" valign=\"middle\">&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&open=$open&indikator=score\"><img src=\"pictures/forum_indikator_grau.gif\" border=\"0\" align=\"middle\"><font size=\"-1\" color=\"#555555\">"._("Relevanz")."</a> &nbsp;";
			
			if ($forum["view"] != "tree" && $forum["view"] != "mixed") { // Anzeige der Sortierung nicht in der Themenansicht
				$print .= "</td><td nowrap class=\"steelkante\" valign=\"bottom\">&nbsp;|&nbsp;&nbsp;<font size=\"-1\">Sortierung:&nbsp;&nbsp;</font>";
				$print .= "<select name=\"sort\" size=\"1\">";
				$tmp["age"] = "Alter";
				$tmp["viewcount"] = "Views";
				$tmp["rating"] = "Bewertung";
				$tmp["score"] = "Relevanz";
				$tmp["nachname"] = "Autor";
				$tmp["root_name"] = "Thema";
				$tmp["x.name"] = "Titel";
				while(list($key,$value) = each($tmp)) {
					$print .= "<option value=\"$key\"";
					if ($key == $forum["sort"]) $print.= " selected";
					$print .= ">$value";
				}
				$print .= "</select>&nbsp;&nbsp;";
				$print .= "<input type=hidden name=flatviewstartposting value='".$flatviewstartposting."'>";
				$print .= "<input type=image name=create value=\"abschicken\" src=\"pictures/haken_transparent.gif\" align=\"middle\" border=\"0\">";
			}
			$print .= "&nbsp;&nbsp;|&nbsp;&nbsp;<a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&toolbar=close&open=$open\" ".tooltip(_("Toolbar einfahren"))."><img src=\"pictures/forumgrau3.gif\" align=\"middle\" border=\"0\"></a>&nbsp;";
			$print .= "</td><td class=\"blank\" width=\"99%\"></td></tr><tr><td class=\"blank\" colspan=\"9\">&nbsp;</td></tr></table></form>";
		} else {
			$print .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" class=\"blank\"><tr><td class=\"blank\"><img src=\"pictures/blank.gif\" height=\"22\" width=\"5\"></td>";
			$print .= "<td class=\"blank\"><font size=\"-1\"><a href=\"$PHP_SELF?flatviewstartposting=$flatviewstartposting&toolbar=open&open=$open\"><img src=\"pictures/pfeillink.gif\" align=\"middle\" border=\"0\"".tooltip(_("Toolbar ausfahren"))."></a>";
			$print .= "</td></tr></table>";
		}
		if ($id) {  // Schreibmodus, also form einbauen
			if  ($user->id == "nobody") $print .= "<form name=forumwrite onsubmit=\"return pruefe_name()\" method=post action=\"".$PHP_SELF."#anker\">";
			else $print .= "<form name=forumwrite method=post action=\"".$PHP_SELF."#anker\">";
		}
		
		$print .= "</td></tr></table>";	
		return $print;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function printposting ($forumposting) {
	global $PHP_SELF,$forum,$view,$davor,$auth,$user, $SessSemName, $loginfilelast, $sidebar;

  // Status des Postings holen
 	// auf- zugeklappt
 	// schreibmodus
 	// Editiermodus
 	// neu / alt
 	// Ordner / Beitrag

	$forumposting = ForumGetWriteStatus($forumposting);
	$forumposting = ForumNewPosting($forumposting);
	$forumposting = ForumOpenClose($forumposting);
	$forumposting = ForumFolderOrPosting($forumposting);
	$forumposting = forum_lonely($forumposting);
	$forumposting = ForumIcon($forumposting);
					
 // Kopfzeile zusammenbauen
  		
  		if ($forum["view"] == "mixed") {		// etwas umständlich: Weg von der Themenansicht zum Folderflatview
  			$viewlink = "flatfolder";
  			$forum["flatviewstartposting"] = 0;
  		}
  			
  	 	else
  	 		$viewlink = "";
 		if ($forumposting["openclose"] == "close") {
  			$link =	$PHP_SELF."?open=".$forumposting["id"]."&flatviewstartposting=".$forum["flatviewstartposting"]."&view=".$viewlink."#anker";
  		} else {
  			if (get_username($user->id) != $forumposting["username"])  // eigene Postings werden beim view nicht gezählt
  				$objectviews = object_add_view($forumposting["id"]); // Anzahl der Views erhöhen
  			if ($forum["view"] == "tree" && $forumposting["type"] == "posting")
  				$link = $PHP_SELF."?open=".$forumposting["rootid"]."#anker"; 
  			else
  				$link = $PHP_SELF."?&flatviewstartposting=".$forum["flatviewstartposting"]."#anker"; 
			if ($forumposting["neuauf"]==1 AND $forumposting["newold"]=="new")
				$link = ""; // zuklappen nur m&ouml;glich wenn neueimmerauf nicht gesetzt	
  		}
  		
  		/// Indexe
  		
  		if (!$objectviews)
  			$objectviews = $forumposting["viewcount"];
  		if (($forumposting["rating"] == 99))
  			$forumposting["rating"] = "?";
  		
  		$forumposting["score"] = round($forumposting["score"],1);
  		
  		// $forumhead[] = "<font color=\"#007700\">".$objectviews."</font> / ";
  		// $forumhead[] = "<font color=\"#AA8800\">".$forumposting["rating"]."</font> / ";
  		// $forumhead[] = "<font color=\"#000077\">".round($relevanz,1)."</font> / ";
  		
  		/// Ende Indexe
  		
  		
  		if ($forumposting["foldercount"] && $forumposting["type"] == "folder" && $forumposting["openclose"] == "close")
  			$forumhead[] = "<b>".($forumposting["foldercount"]-1)."</b> / ";
  		
  		
  		if (!$auth->is_authenticated() || $user->id == "nobody" || $forumposting["author"]=="unbekannt" || $forumposting["username"]=="") // Nobody darf nicht auf die about...
			$forumhead[] = htmlReady($forumposting["author"]);
		else
			$forumhead[] = "<a class=\"printhead\" href=\"about.php?username=".$forumposting["username"]."\">". htmlReady($forumposting["author"]) ."&nbsp;</a>";
  		  		
  		$forumhead[] = 	"&nbsp;".date("d.m.Y - H:i", $forumposting["chdate"])."&nbsp;";
  		
  		if ($forum["view"] != "flatfolder")
  			$forumhead[] =	"<a href=\"".$PHP_SELF."?open=".$forumposting["id"]
					."&openall=TRUE&view=tree"
					."#anker\" class=\"printhead\">".htmlReady(mila($forumposting["rootname"],20))
					."</a>"
					."&nbsp; ";
  		
		$forumhead[] = "| ".$forumposting[$forum["sort"]]." | ";

		if (!(have_sem_write_perm())) // Antwort-Pfeil
			$forumhead[] = "<a href=\"write_topic.php?write=1&root_id=".$forumposting["rootid"]."&topic_id=".$forumposting["id"]."\" target=\"_new\"><img src=\"pictures/antwortnew.gif\" border=0 " . tooltip(_("Hier klicken um in einem neuen Fenster zu antworten")) . "></a>"; 
  		
  		$zusatz = ForumParseZusatz($forumhead);
  		
  		if ($forumposting["writestatus"]!="none") {    //wir sind im Schreibmodus
			echo "<input type=hidden name=topic_id value=$topic_id>";
			$name = "<input type=text size=50 style='font-size:8 pt;font-weight:normal;' name=titel value='".htmlReady($forumposting["name"])."'>";
			$zusatz = ""; // beim editieren brauchen wir den Kram nicht
		} else {
  			$name = "<a href=\"$link\" class=\"tree\" >".htmlReady(mila($forumposting["name"]))."</a>";
  		}
  		
  		if ($forumposting["newold"] == "new")
  			$new = TRUE;
  		  		
  		if (($forum["view"]=="tree" || $forum["view"]=="mixed") && $forumposting["type"] == "folder") {
  			if ($loginfilelast[$SessSemName[1]] < $forumposting["folderlast"])
			 	$new = TRUE;		
			
  			$forumposting["mkdate"] = $forumposting["folderlast"];
  		}
  		
  		// welcher Index liegt auf den Pfeilen?
  		
  		if ($forum["indikator"] == "views")
  			$index = $objectviews;
  		elseif ($forum["indikator"] == "rate")
  			$index = $forumposting["rating"];
  		elseif ($forum["indikator"] == "score")
  			$index = $forumposting["score"];
  		  	
  // Kopfzeile ausgeben 		
  		
  		if ($forumposting["intree"]!=TRUE)
  			echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
  		if ($forum["anchor"] == $forumposting["id"])
  			echo "<a name='anker'></a>";
		printhead ("100%","0",$link,$forumposting["openclose"],$new,$forumposting["icon"],$name,$zusatz,$forumposting["mkdate"],"TRUE",$index,$forum["indikator"]);
		if ($forumposting["intree"]==TRUE)
			echo "<td class=\"blank\">&nbsp;&nbsp;&nbsp;</td>";
		echo "</tr></table>\n";	

// Kontentzeile	zusammenbauen	
	
	if ($forumposting["openclose"] == "open") {
		$forumposting = ForumGetRights($forumposting);
		if ($forumposting["writestatus"] != "none") { // Posting wird geschrieben
			$description = editarea($forumposting);
		} else {
			$description = formatReady($forumposting["description"]);
			if ($forumposting["buttons"] == "no" || $forum["update"]) {
				$edit = "<br>";
			} else {
				$edit = forum_get_buttons($forumposting);
			}
		}
		if (ereg("\[quote",$description) AND ereg("\[/quote\]",$description) AND $forumposting["writestatus"] == "none")
			$description = quotes_decode($description);

		if ($sidebar==$forumposting["id"]) {
			$addon = "<font size=\"-1\" color=\"555555\"><br>&nbsp;&nbsp;Views: $objectviews<br>&nbsp;&nbsp;Relevanz: $relevanz<br>&nbsp;&nbsp;Bewertung: ".$forumposting["rate"]."<br>";
			$rate = object_print_rates_detail($forumposting["id"]);
			while(list($key,$value) = each($rate)) 
				$addon .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$key: $value<br>";
					
			if (get_username($user->id) == $forumposting["username"]) {
				$addon .= "<font size=\"-1\">&nbsp;&nbsp;Sie können sich nicht&nbsp;<br>&nbsp;&nbsp;selbst bewerten.";
			} else {
				if (object_check_user($forumposting["id"], "rate") == FALSE) {  // wenn er noch nicht bewertet hat
					$addon .= "<div align=\"center\"><font size=\"-1\">Dieser Beitrag war<br><font size=\"-2\">(Schulnote)</font><br><form method=post action=$PHP_SELF>";
					$addon .= "<b>&nbsp;<font size=\"2\" color=\"555555\">1<input type=radio name=rate value=1>";
					$addon .= "<input type=radio name=rate value=2>";
					$addon .= "<input type=radio name=rate value=3>";
					$addon .= "<input type=radio name=rate value=4>";
					$addon .= "<input type=radio name=rate value=5>5&nbsp;";
					$addon .= "<br><br>";
					$addon .= "<input type=hidden name=open value='".$forumposting["id"]."'><input type=hidden name=sidebar value='".$forumposting["id"]."'>";
					$addon .= "<input type=hidden name=flatviewstartposting value='".$forum["flatviewstartposting"]."'>";
					$addon .= "<input type=image name=create value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0>";
					$addon .= "</form>";
				} else {
					$addon .= "<font size=\"-1\">&nbsp;&nbsp;Sie haben diesen&nbsp;<br>&nbsp;&nbsp;Beitrag bewertet.";
				}
			}
		} else 
			$addon = "open:$PHP_SELF?open=".$forumposting["id"]."&flatviewstartposting=".$forum["flatviewstartposting"]."&sidebar=".$forumposting["id"]."#anker";		
  
  // Kontentzeile ausgeben
		
		echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		
		if ($forumposting["intree"]==TRUE) // etwas Schmuckwerk für die Strichlogik
			echo ForumStriche($forumposting);
		printcontent ("100%",$formposting,$description,$edit,TRUE,$addon);
		if ($forumposting["intree"]==TRUE)
			echo "<td class=\"blank\">&nbsp;&nbsp;&nbsp;</td>";
		echo "</tr></table>\n";	
	}
	return $forumposting;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function flatview ($open=0, $mehr=1, $show=0, $update="", $name="", $description="",$zitat="")

{	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow,$view,$rechte,$forum,$user,$flatviewstartposting,$PHP_SELF;

/////////////////////////////// Konstanten setzen bzw. zuweisen die für die ganze Seite gelten

$forum["openlist"] = $open;
$forum["zitat"] = $zitat;
$forum["update"] = $update;
$forum["postingsperside"] = 15;
$postingsperside = 15;
if (!$flatviewstartposting) {
	$forum["flatviewstartposting"] = 0;
	$flatviewstartposting = 0;
} else {
	$forum["flatviewstartposting"] = $flatviewstartposting;
}

/////////////////////////////// Abfrage der Postings

$db = new DB_Seminar;

if ($forum["view"]=="flatfolder") {
	$folder_id = $forum["flatfolder"];
	$addon = " AND x.root_id = '$folder_id'";
}
$order = "DESC";

if ($forum["sort"] == "rating" && ($forum["view"] != "tree" && $forum["view"] != "mixed"))
	$order = "ASC";

if ($forum["view"]=="search") {
	if ($forum["search"]!="") {
		$addon = " AND (".$forum["search"].")";
	} else {
		echo forum_search_field()."<br><br>";
		$nomsg="TRUE";
		die;
	}
} elseif ($forum["view"]=="neue") {
	$datumtmp = $loginfilelast[$SessSemName[1]];
	$addon = " AND x.chdate > '$datumtmp'";
}

$query = "SELECT x.topic_id FROM px_topics x, px_topics y WHERE x.root_id = y.topic_id AND x.Seminar_id = '$SessionSeminar'".$addon."";
$db->query($query);
if ($db->num_rows() > 0) {  // Forum ist nicht leer
	$forum["forumsum"] = $db->num_rows();
} else { // das Forum ist leer
	if ($nomsg!="TRUE") {
		echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
		echo ForumNoPostings();
		echo "</table>";
	}
	die;
}

$query = "SELECT x.topic_id, x.name , x.author , x.mkdate, x.chdate as age, y.name AS root_name"
	.", x.description, x.Seminar_id, y.topic_id AS root_id, username, x.user_id"
	.", IFNULL(views,0) as viewcount, nachname, IFNULL(ROUND(AVG(rate),1),99) as rating"
	.", ((6-(IFNULL(AVG(rate),3))-3)*5)+(IFNULL(views,0)/(((UNIX_TIMESTAMP()-x.mkdate)/604800)+1)) as score "
	."FROM px_topics x LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN object_views ON(object_views.object_id=x.topic_id) LEFT JOIN object_rate ON(object_rate.object_id=x.topic_id) , px_topics y "
	."WHERE x.root_id = y.topic_id AND x.seminar_id = '$SessionSeminar'".$addon." "
	."GROUP by x.topic_id ORDER BY ".$forum["sort"]." ".$order;

$query .= " LIMIT $flatviewstartposting,$postingsperside";
$db->query($query);



/////////////////////////////////////// HTML und Navigation

?>	
<table border=0 width="100%" cellspacing="0" cellpadding="0" align="center"><tr>
<td class="steelgraudunkel" align="left" width="45%">
<?

if ($forum["view"]=="flatfolder")
	echo "<img src=\"pictures/cont_folder.gif\" align=\"baseline\"><font size=\"-1\"><b> Thema:</b> ".mila(ForumGetName($forum["flatfolder"]),40)." / ";
if ($forum["search"]!="" && $forum["view"]=="search") {
	$searchname = explode("%",$forum["search"]);
	echo "<font size=\"-1\">&nbsp;Suchbegriff: '".$searchname["1"]."' / Treffer: ".$forum["forumsum"]."</font>";
} else
	echo "<font size=\"-1\">&nbsp;Postings: ".$forum["forumsum"]."</font>";
echo "</td><td class=\"steelgraudunkel\" align=\"center\" width=\"10%\">";
if ($forum["flatallopen"]=="TRUE")
	echo "<a href=\"".$PHP_SELF
		."?flatviewstartposting=".$forum["flatviewstartposting"]."&flatallopen=FALSE\"><img src='pictures/forumleer.gif' border=0 height='10' align=middle><img src='pictures/forumgraurauf.gif' border=0 " . tooltip(_("Alle zuklappen")) . " align=middle><img src='pictures/forumleer.gif' border=0></a>";
else
	echo "<a href=\"".$PHP_SELF
		."?flatviewstartposting=".$forum["flatviewstartposting"]."&flatallopen=TRUE\"><img src='pictures/forumleer.gif' border=0 height='10' align=middle><img src='pictures/forumgraurunt.gif' border=0 " . tooltip(_("Alle aufklappen")) . " align=middle><img src='pictures/forumleer.gif' border=0></a>";

echo "</td><td class=\"steelgraudunkel\" align=\"right\" width=\"45%\">";
echo forum_print_navi($forum)."&nbsp;&nbsp;&nbsp;";
echo "</td></tr></table>";

/////////////////// Konstanten für das gerade auszugebene Posting und Posting ausgeben

while($db->next_record()){
	$forumposting["id"] = $db->f("topic_id");
	$forumposting["name"] = $db->f("name");
	$forumposting["description"] = $db->f("description");
	$forumposting["author"] = $db->f("author");
	$forumposting["username"] = $db->f("username");
	$forumposting["userid"] = $db->f("user_id");
	$forumposting["rootid"] = $db->f("root_id");
	$forumposting["rootname"] = $db->f("root_name");
	$forumposting["mkdate"] = $db->f("mkdate");
	$forumposting["chdate"] = $db->f("age");
	$forumposting["viewcount"] = $db->f("viewcount");
	$forumposting["rating"] = $db->f("rating");
	$forumposting["score"] = $db->f("score");
	
	$forumposting = printposting($forumposting);
}

/////////// HTML für den Rest

echo "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" valign=\"top\" align=\"center\">";
echo "	<tr>";
echo "		<td class=\"blank\" valign=\"top\"><img src=\"pictures/forumleer.gif\" border=\"0\" height=\"4\">";
echo "</td>";
echo "	</tr>";
echo "	<tr>";
echo "		<td class=\"steelgraudunkel\" align=\"right\" ><img src=\"pictures/forumleer.gif\" border=\"0\" height=\"10\" align=\"middle\">";
echo forum_print_navi($forum);
echo "		&nbsp;&nbsp;</td>";
echo "	</tr>";
echo "	<tr>";
echo "		<td class=\"blank\">&nbsp;<br><br>";
echo "		</td>";
echo "	</tr>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "</table><br>";

/*
echo DebugForum($forum);
echo "<hr>";
echo DebugForum($forumposting);
*/


if ($update)
	echo "</form>\n";
}

/////////////////////////////////////////////////////////////////////////

function DisplayFolders ($open=0, $update="", $zitat="") {
	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow,$rechte,$i_page,$view, $write,$all,$forum,$cmd,$move_id,$auth,$user, $PHP_SELF;

//Zeigt im Treeview die Themenordner an

	$forum["update"] = $update;
	$forum["zitat"] = $zitat;
		
	$fields = array("topic_id", "parent_id", "root_id", "name"
		, "description", "author", "author_host", "mkdate"
		, "chdate", "user_id");
	$query = "select distinct ";
	$comma = "";
	WHILE (list($key,$val)=each($fields)) {
		$query .= $comma."t.".$val;
		$comma = ", ";
	}
	
	$query .= ", count(*) as count, max(s.chdate) as last "
	.", IFNULL(views,0) as viewcount, IFNULL(ROUND(AVG(rate),1),99) as rating "
	.", ((6-(IFNULL(AVG(rate),3))-3)*5)+(IFNULL(views,0)/(((UNIX_TIMESTAMP()-t.mkdate)/604800)+1)) as score "
	."FROM px_topics t LEFT JOIN px_topics s USING(root_id) "
	."LEFT JOIN object_views ON(object_views.object_id=t.topic_id) LEFT JOIN object_rate ON(object_rate.object_id=t.topic_id)"
	."WHERE t.topic_id = t.root_id AND t.Seminar_id = '$SessionSeminar' GROUP BY t.root_id  ORDER BY t.mkdate";
	$db=new DB_Seminar;
	$db->query($query);
	if ($db->num_rows()==0) {  // Das Forum ist leer
		echo ForumEmpty();
		die;
	} else {
		
		// Berechnung was geöffnet ist
		
		$forum["openlist"] = "";
		$root_id = ForumGetRoot($open);
		if ($open != $root_id && !$update)
			$forum["openlist"] = suche_kinder($open);
		if ($update && ForumFreshPosting($update)==TRUE)
			$forum["openlist"] .= ForumGetParent($update);
		$forum["openlist"] .= ";".$open.";".$root_id;
		
		
		// HTML

		echo "<table class=\"blank\" width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr>";
		echo "<td class=\"steelgraudunkel\" width=\"33%\"><b><font size=\"-1\">&nbsp;" . _("Thema") . "</font></b></td>";
		echo "<td class=\"steelgraudunkel\" width=\"33%\" align=\"center\"><font size=\"-1\">&nbsp;&nbsp;";
		if ($forum["view"] == "tree")
			echo "<a href=\"".$PHP_SELF."?view=mixed\"><img src=\"pictures/forumtree.gif\" border=\"0\" align=\"top\"></a>";
		else
			echo "<a href=\"".$PHP_SELF."?view=tree\"><img src=\"pictures/forumflat.gif\" border=\"0\" align=\"top\"></a>";
		echo "</font><img src=\"pictures/forumleer.gif\" border=0 height=\"20\" align=\"middle\"></td>";
		echo "<td class=\"steelgraudunkel\" width=\"33%\"align=\"right\"><font size=\"-1\">" . _("<b>Postings</b> / letzter Eintrag") . "&nbsp;&nbsp;</font></td></tr></table>\n";
		while ($db->next_record()) {
			$forumposting["id"] = $db->f("topic_id");
			$forumposting["name"] = $db->f("name");
			$forumposting["description"] = $db->f("description");
			$forumposting["author"] = $db->f("author");
			$forumposting["username"] = get_username($db->f("user_id"));
			$forumposting["userid"] = $db->f("user_id");
			$forumposting["rootid"] = $db->f("root_id");
			$forumposting["rootname"] = $db->f("root_name");
			$forumposting["mkdate"] = $db->f("mkdate");
			$forumposting["chdate"] = $db->f("chdate");
			$forumposting["foldercount"] = $db->f("count");
			$forumposting["folderlast"] = $db->f("last");
			$forumposting["viewcount"] = $db->f("viewcount");
			$forumposting["rating"] = $db->f("rating");
			$forumposting["score"] = $db->f("score");
	
			$forumposting = printposting($forumposting);
		
			if ($forum["view"] == "tree" && $forumposting["openclose"]=="open") {
				DisplayKids ($forumposting);
			}
		}
	}
	echo "<table class=blank border=0 cellpadding=0 cellspacing=0 width=\"100%\"><tr><td class='blank'><img src='pictures/forumleer.gif' border=0 height='4'></td></tr><tr>";
	echo "<td align=center class=steelgraudunkel><img src='pictures/forumleer.gif' border=0 height='20' align=middle>";
	if ($rechte)
		echo "<a href='".$PHP_SELF."?neuesthema=TRUE#anker'><img src='pictures/forumgraurunt.gif' border=0 align=middle " . tooltip(_("Neues Thema anlegen")) . "><img src='pictures/cont_folder2.gif' " . tooltip(_("Neues Thema anlegen")) . " border=0 align=middle></a>";
	echo "</td></tr><tr><td class=blank>&nbsp; <br>&nbsp; <br></td></tr></table>\n";
	
	/*
	echo DebugForum($forum);
	echo "<hr>";
	echo DebugForum($forumposting);
	*/
	
	if ($update)
		echo "</form>\n";
}

/////////////////////////////////

function DisplayKids ($forumposting, $level=0) {
	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow, $anfang, $forum,$rechte,$view,$write,$all,$davor,$auth,$user;

// stellt im Treeview alle Postings dar, die NICHT Thema sind

	$topic_id = $forumposting["id"];
	$forumposting["intree"]="TRUE";
	$query = "select topic_id, parent_id, name, author "
		.", px_topics.mkdate, px_topics.chdate, description, root_id, username, px_topics.user_id"
		.", IFNULL(views,0) as viewcount, IFNULL(ROUND(AVG(rate),1),99) as rating"
		.", ((6-(IFNULL(AVG(rate),3))-3)*5)+(IFNULL(views,0)/(((UNIX_TIMESTAMP()-px_topics.mkdate)/604800)+1)) as score "
		." FROM px_topics LEFT JOIN auth_user_md5 USING(user_id)"
		." LEFT JOIN object_views ON(object_views.object_id=topic_id) LEFT JOIN object_rate ON(object_rate.object_id=topic_id)"
		." WHERE"
		." parent_id = '$topic_id'"
		." GROUP BY topic_id ORDER by px_topics.mkdate";
	$db=new DB_Seminar;
	$db->query($query);
	$forumposting["lines"][$level] = $db->num_rows();
	WHILE ($db->next_record()) {
		
		$forumposting["id"] = $db->f("topic_id");
		$forumposting["name"] = $db->f("name");
		$forumposting["description"] = $db->f("description");
		$forumposting["author"] = $db->f("author");
		$forumposting["username"] = $db->f("username");
		$forumposting["userid"] = $db->f("user_id");
		$forumposting["rootid"] = $db->f("root_id");
		$forumposting["rootname"] = $db->f("root_name");
		$forumposting["mkdate"] = $db->f("mkdate");
		$forumposting["chdate"] = $db->f("chdate");
		$forumposting["level"] = $level;
		$forumposting["viewcount"] = $db->f("viewcount");
		$forumposting["rating"] = $db->f("rating");
		$forumposting["score"] = $db->f("score");
					
		echo "<table class=\"blank\" border=0 cellpadding=0 cellspacing=0 width=\"100%\"><tr><td class=\"blank\" nowrap valign=\"top\" ><img src='pictures/forumleer.gif'><img src='pictures/forumleer.gif'>";

	//Hier eine bezaubernde Routine um die Striche exakt wiederzugeben - keine Bange ich verstehe sie auch nicht mehr
		IF ($level){ 
			$striche = "";
			for ($i=0;$i<$level;$i++) {
				if ($i==($level-1)) {
					if ($forumposting["lines"][$i+1]>1) $striche.= "<img src='pictures/forumstrich3.gif' border=0>"; 		//Kreuzung
					else $striche.= "<img src='pictures/forumstrich2.gif' border=0>"; 				//abknickend
					$forumposting["lines"][$i+1] -= 1;
				} else {
					if ($forumposting["lines"][$i+1]==0) $striche .= "<img src='pictures/forumleer.gif' border=0>";		//Leerzelle
					else $striche .= "<img src='pictures/forumstrich.gif' border=0>";				//Strich
				}
			}
			echo $striche;
		}
		echo "</td>";
		$forumposting = printposting($forumposting);
		DisplayKids($forumposting, $level+1);
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function forum_search_field () {
	global $PHP_SELF;
$searchfield = "
<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\"><tr><td class=\"blank\">
<table border=\"0\" width=\"604\" cellspacing=\"5\" cellpadding=\"0\" align=\"center\">
<tr>
<td class=\"blank\">&nbsp;</td></tr>
<td class=\"blank\" width=\"302\" align=\"center\">
   <table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" valign=\"top\">
	<form  name=\"search\" method=\"post\"  action=".$PHP_SELF."
		<tr>
			<td class=\"steel1\">
				<b><font size=\"-1\">"._("Suchbegriff:")."</font></b>
			</td>
			<td class=\"steel1\">
				<input  type=\"TEXT\" name=\"suchbegriff\">
			</td>
		</tr>
	   	<tr>
	   		<td class=\"steelgraulight\">
	   			<b><font size=\"-1\">"._("Suchen in den Feldern:")."</font></b>
	   		</td>
	   		<td class=\"steelgraulight\">&nbsp;
	   		</td>
	   	</tr>
		<tr>
			<td class=\"steel1\">&nbsp;
			</td>
			<td class=\"steel1\">
				<input name=\"check_author\" type=\"CHECKBOX\" value=\"on\" checked><font size=\"-1\"> "._("Autor")."
			</td>
		</tr>
	     	<tr>
	     		<td class=\"steelgraulight\">&nbsp;
	     		</td>
	     		<td class=\"steelgraulight\">
	     			<input type=\"CHECKBOX\" name=\"check_name\" value=\"on\" checked><font size=\"-1\"> "._("Überschrift")." 
	     		</td>
	     	</tr>
	     	<tr>
	     		<td class=\"steel1\">&nbsp;
		     	</td>
		     	<td class=\"steel1\">
		     		<input type=\"CHECKBOX\" name=\"check_cont\" value=\"on\" checked><font size=\"-1\"> "._("Inhalt")."
		     	</td>
		</tr> 
		<tr>
			<td class=\"steelgraulight\" colspan=\"2\" align=\"center\">
				<input type=\"hidden\" name=\"view\" value=\"search\">
				<br><input type=\"IMAGE\" ".makeButton("suchestarten", "src")."border=\"0\" value=\""._("Suche starten")."\"><br><br>
			</td>
		</tr>
	</form>
   </table>
</td>
<td class=\"suche\"><img src=\"pictures/blank.gif\" height=\"10\" width=\"285\">
<tr>
</tr></table><br></td></tr></table>";
return $searchfield;
}

/////////////////////
	
function forum_move_navi ($topic_id) {
	global $perm, $user, $forum, $view, $PHP_SELF;
	
	$mutter = suche_kinder($topic_id);
	$mutter = explode (";",$mutter);
	$count = sizeof($mutter)-2;
	
	// wohin darf ich schieben? Abfragen je nach Rechten
	
	if ($perm->have_perm("tutor") OR $perm->have_perm("dozent"))
		$query = "SELECT DISTINCT seminare.Seminar_id, seminare.Name FROM seminar_user LEFT JOIN seminare USING(Seminar_id) WHERE user_id ='$user->id ' AND (seminar_user.status = 'tutor' OR seminar_user.status = 'dozent') ORDER BY Name";
	if ($perm->have_perm("admin"))
		$query = "SELECT seminare.* FROM user_inst LEFT JOIN Institute USING (Institut_id) LEFT JOIN seminare USING(Institut_id) LEFT OUTER JOIN seminar_user USING(Seminar_id) WHERE user_inst.inst_perms='admin' AND user_inst.user_id='$user->id' AND seminare.Institut_id is not NULL GROUP BY seminare.Seminar_id ORDER BY seminare.Name";
	if ($perm->have_perm("root"))
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

?>	
			<tr><td class="blank" colspan="2"><table border="0" cellpadding="0" cellspacing="0"><tr>
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
			  	<? echo "<a href=\"".$PHP_SELF."?view=$view\">".makeButton("abbrechen", "img")."</a>";?>
		  		</td>
  			</tr>
  		</table></td></tr>
<?		
}

?>