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
	$description .= "<br><br><input type=image name=create value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0>&nbsp;"
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

function leer($topic_id)  //schaut nach ob ein Ordner leer ist
{	global $user,$auth,$rechte;
	$leer=TRUE;
	$db2=new DB_Seminar;
	$db2->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
		IF ($db2->num_rows()) $leer=FALSE;
 	return $leer;
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
	AND !(($forum["view"]=="flat" || $forum["view"]=="neue" || $forum["view"]=="flat" || $forum["view"]=="flatfolder") && $forum["flatallopen"]=="TRUE")
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

function ForumLonely($forumposting) {  //Sieht nach ob das Posting kinderlos ist
	
	$topic_id = $forumposting["id"];
	$db2=new DB_Seminar;
	$db2->query("SELECT topic_id FROM px_topics WHERE parent_id='$topic_id'");
		if (!$db2->num_rows())
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
	$db=new DB_Seminar;
	$topic_id = $forumposting["id"];
	$db->query("SELECT user_id FROM px_topics WHERE topic_id='$topic_id'");
	if ($db->next_record())
		if ($db->f("user_id")==$user->id || $rechte)
			$forumposting["perms"] = "write";
		else
			$forumposting["perms"] = "none";
	return $forumposting;
}

function ForumIcon ($forumposting) {
	global $cmd, $rechte, $topic_id, $PHP_SELF, $forum, $auth;
	if ($forumposting["type"]=="folder") {
		if (leer($forumposting["id"])==FALSE)
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


function quote($zitat_id)  
{
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

function ForumGetName($id)  
{
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
		
		if ($forum["view"] == "flatfolder")
			$page = (ceil($forum["forumsum"] / $forum["postingsperside"])-1)*$forum["postingsperside"];
		else 	$page = "0";
		$edit = "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&flatviewstartposting=$page#anker\">&nbsp;" . makeButton("antworten", "img") . "</a>";
		$edit .= "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&zitat=TRUE&flatviewstartposting=$page#anker\">&nbsp;" . makeButton("zitieren", "img") . "</a>";
		if ($forumposting["lonely"]==TRUE && ($rechte || $forumposting["perms"]=="write")) // ich darf bearbeiten
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?edit_id=".$forumposting["id"]."&view=".$forum["view"]."&flatviewstartposting=".$forum["flatviewstartposting"]."#anker\">"
			. makeButton("bearbeiten", "img") . "</a>";
		if ($rechte || ($forumposting["lonely"]==TRUE && $forumposting["perms"]=="write")) // ich darf löschen
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?delete_id=".$forumposting["id"]."&view=".$forum["view"]."&flatviewstartposting=".$forum["flatviewstartposting"]."\">"
			. makeButton("loeschen", "img") . "</a>";
		if ($rechte)  // ich darf verschieben
			$edit .= "&nbsp;<a href=\"".$PHP_SELF."?cmd=move&topic_id=".$forumposting["id"]."&view=".$forum["view"]."\">"
			. makeButton("verschieben", "img") . "</a>";
	} elseif ($user->id == "nobody") { 	// darf Nobody hier schreiben?
		$db=new DB_Seminar;
		$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id='$SessionSeminar' AND Schreibzugriff=0");
		if ($db->num_rows())  {
			$edit = "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&flatviewstartposting=$page#anker\">&nbsp;" . makeButton("antworten", "img") . "</a>";
			$edit .= "<a href=\"".$PHP_SELF."?answer_id=".$forumposting["id"]."&zitat=TRUE&flatviewstartposting=$page#anker\">&nbsp;" . makeButton("zitieren", "img") . "</a>";
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
	$text = _("In dieser Ansicht gibt es derzeit keine Beiträge.");
	$empty = "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
	$empty .= parse_msg("info§$text");
	$empty .= "</table>";	
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

{	global $SessionSeminar,$auth;
	if (!$tmpSessionSeminar)
		$tmpSessionSeminar=$SessionSeminar;
	$db=new DB_Seminar;
	$mkdate = time();
	if (!$user_id) {
		$db->query ("SELECT user_id , username FROM auth_user_md5 WHERE username = '".$auth->auth["uname"]."' ");
		while ($db->next_record())
			$user_id = $db->f("user_id");
	}
	$topic_id = MakeUniqueID();
	IF ($root_id == "0")	{
		$root_id = $topic_id;
		}
	$query = "INSERT INTO px_topics (topic_id,name,description, parent_id, root_id , author, author_host, Seminar_id, user_id, mkdate, chdate) values ('$topic_id', '$name', '$description', '$parent_id', '$root_id', '$author', '".getenv("REMOTE_ADDR")."', '$tmpSessionSeminar', '$user_id', '$mkdate', '$mkdate') ";
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


////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function printposting ($forumposting) {
	global $PHP_SELF,$forum,$view,$davor,$auth,$user, $SessSemName, $loginfilelast;

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
	$forumposting = ForumIcon($forumposting);
	$forumposting = ForumLonely($forumposting);
					
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
  			if ($forum["view"] == "tree")
  				$link = $PHP_SELF."?open=".$forumposting["rootid"]."#anker"; 
  			else
  				$link = $PHP_SELF."?&flatviewstartposting=".$forum["flatviewstartposting"]."#anker"; 
			if ($forumposting["neuauf"]==1 AND $forumposting["newold"]=="new")
				$link = ""; // zuklappen nur m&ouml;glich wenn neueimmerauf nicht gesetzt	
  		}
  		
  		if (!$objectviews)
  			$objectviews = object_return_views($forumposting["id"]);
  		$forumhead[] = "<font color=\"#007700\">".$objectviews."</font> / ";
  		
  		if ($forumposting["foldercount"] && $forumposting["type"] == "folder" && $forumposting["openclose"] == "close")
  			$forumhead[] = "<b>".($forumposting["foldercount"]-1)."</b> / ";
  		
  		
  		if (!($forum["view"] == "tree" && $forumposting["type"] == "folder" && $forumposting["openclose"] == "close")) {
	  		if (!$auth->is_authenticated() || $user->id == "nobody" || $forumposting["author"]=="unbekannt" || $forumposting["username"]=="") // Nobody darf nicht auf die about...
				$forumhead[] = htmlReady($forumposting["author"]);
			else
				$forumhead[] = "<a class=\"printhead\" href=\"about.php?username=".$forumposting["username"]."\">". htmlReady($forumposting["author"]) ."&nbsp;</a>";
  		}
  		
  		$forumhead[] = 	"&nbsp;".date("d.m.Y - H:i", $forumposting["chdate"])."&nbsp;";
  		
  		if ($forum["view"] != "flatfolder")
  			$forumhead[] =	"<a href=\"".$PHP_SELF."?open=".$forumposting["id"]
					."&openall=TRUE&view=tree"
					."#anker\" class=\"printhead\">".htmlReady(mila($forumposting["rootname"],20))
					."</a>"
					."&nbsp; ";
  		
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
  		
  // Kopfzeile ausgeben 		
  		
  		if ($forumposting["intree"]!=TRUE)
  			echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
  		if ($forum["anchor"] == $forumposting["id"])
  			echo "<a name='anker'></a>";
		printhead ("100%","0",$link,$forumposting["openclose"],$new,$forumposting["icon"],$name,$zusatz,$forumposting["mkdate"]);
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
		if (ereg("\[quote",$description) AND ereg("\[/quote\]",$description) AND (!$forum["zitat"]) AND $forumposting["writestatus"] == "none")
			$description = quotes_decode($description);
		
  // Kontentzeile ausgeben
		
		echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		
		if ($forumposting["intree"]==TRUE) // etwas Schmuckwerk für die Strichlogik
			echo ForumStriche($forumposting);
		
		printcontent ("100%",$formposting,$description,$edit);
		if ($forumposting["intree"]==TRUE)
			echo "<td class=\"blank\">&nbsp;&nbsp;&nbsp;</td>";
		echo "</tr></table>\n";	
	}
	return $forumposting;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function flatview ($open=0, $mehr=1, $show=0, $update="", $name="", $description="",$zitat="")

{	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow,$view,$rechte,$forum,$user,$flatviewstartposting,$PHP_SELF;

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0 class="blank">
	<tr>
		<td colspan=2 class="blank"><br>
<?
IF ($update) {  // Schreibmodus, also form einbauen
	IF  ($user->id == "nobody") echo "<form name=forumwrite onsubmit=\"return pruefe_name()\" method=post action=\"".$PHP_SELF."#anker\">\n";
	ELSE echo "<form name=forumwrite method=post action=\"".$PHP_SELF."#anker\">\n";
}

///////// Konstanten setzen bzw. zuweisen die für die ganze Seite gelten

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

///////// Ausgabe für leeren Forenbereich



$db = new DB_Seminar;

///////// Abfrage der Postings

if ($forum["view"]=="flatfolder") {
	$folder_id = $forum["flatfolder"];
	$addon = " AND x.root_id = '$folder_id'";
	$order = "ASC";
} else {
	$order = "DESC";
}

if ($forum["view"]=="neue") {
	$datumtmp = $loginfilelast[$SessSemName[1]];
	$db->query ("SELECT x.topic_id, x.name , x.author , x.mkdate, x.chdate , y.name AS root_name, x.description , x.Seminar_id, y.topic_id AS root_id, username FROM px_topics x LEFT JOIN auth_user_md5 USING(user_id), px_topics y WHERE x.root_id = y.topic_id AND x.chdate > '$datumtmp' AND x.Seminar_id = '$SessionSeminar' ORDER BY x.chdate ".$order);	
} else 
	$db->query("SELECT x.topic_id, x.name , x.author , x.mkdate, x.chdate, y.name AS root_name, x.description, x.Seminar_id, y.topic_id AS root_id, username FROM px_topics x LEFT JOIN auth_user_md5 USING(user_id), px_topics y WHERE x.root_id = y.topic_id AND x.seminar_id = '$SessionSeminar'".$addon." ORDER BY chdate ".$order);

if ($db->num_rows() > 0) {  // Forum ist nicht leer
	$forum["forumsum"] = $db->num_rows();
} else { // das Forum ist leer
	echo ForumNoPostings();
	die;
}
if ($forum["view"]!="neue")
	$db->query("SELECT x.topic_id, x.name , x.author , x.mkdate, x.chdate, y.name AS root_name, x.description, x.Seminar_id, y.topic_id AS root_id, username FROM px_topics x LEFT JOIN auth_user_md5 USING(user_id), px_topics y WHERE x.root_id = y.topic_id AND x.seminar_id = '$SessionSeminar'".$addon." ORDER BY chdate ".$order." LIMIT $flatviewstartposting,$postingsperside");

///////// HTML und Navigation

?>	
<table border=0 width="100%" cellspacing="0" cellpadding="0" align="center"><tr>
<td class="steelgraudunkel" valign= "top" align="left" width="33%">
<?
if ($forum["view"]=="flatfolder")
	echo "<img src=\"pictures/cont_folder.gif\" align=\"baseline\"><font size=-1><b> Thema:</b> ".ForumGetName($forum["flatfolder"]);
echo "</td><td class=\"steelgraudunkel\" align=\"center\" width=\"33%\">";
if ($forum["flatallopen"]=="TRUE")
	echo "<a href=\"".$PHP_SELF
		."?flatviewstartposting=".$forum["flatviewstartposting"]."&flatallopen=FALSE\"><img src='pictures/forumleer.gif' border=0 height='10' align=middle><img src='pictures/forumgraurauf.gif' border=0 " . tooltip(_("Alle zuklappen")) . " align=middle><img src='pictures/forumleer.gif' border=0></a>";
else
	echo "<a href=\"".$PHP_SELF
		."?flatviewstartposting=".$forum["flatviewstartposting"]."&flatallopen=TRUE\"><img src='pictures/forumleer.gif' border=0 height='10' align=middle><img src='pictures/forumgraurunt.gif' border=0 " . tooltip(_("Alle aufklappen")) . " align=middle><img src='pictures/forumleer.gif' border=0></a>";

echo "</td><td class=\"steelgraudunkel\" align=\"right\" width=\"33%\">";
echo forum_print_navi($forum)."&nbsp;&nbsp;&nbsp;";
echo "</td></tr></table>";

////////// Ausgabe der Postings

while($db->next_record()){

// Konstanten für das gerade auszugebene Posting setzen

	$forumposting["id"] = $db->f("topic_id");
	$forumposting["name"] = $db->f("name");
	$forumposting["description"] = $db->f("description");
	$forumposting["author"] = $db->f("author");
	$forumposting["username"] = $db->f("username");
	$forumposting["rootid"] = $db->f("root_id");
	$forumposting["rootname"] = $db->f("root_name");
	$forumposting["mkdate"] = $db->f("mkdate");
	$forumposting["chdate"] = $db->f("chdate");

	
	$forumposting = printposting($forumposting);
	
	// printposting($mehr,$show,$write,$db->f("chdate"),$zitat);
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

//Stelle Form zu
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
	$query .= ", count(*) as count, max(s.chdate) as last from px_topics t LEFT JOIN px_topics s USING(root_id) where t.topic_id = t.root_id AND t.Seminar_id = '$SessionSeminar' group by t.root_id  order by t.mkdate";
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

		echo "<table class=\"blank\" width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr><td class=\"blank\" colspan=3>&nbsp;";
		if ($update) {  // Schreibmodus, also form einbauen
			if  ($user->id == "nobody") echo "<form name=forumwrite onsubmit=\"return pruefe_name()\" method=post action=\"".$PHP_SELF."#anker\">\n";
			else echo "<form name=forumwrite method=post action=\"".$PHP_SELF."#anker\">\n";
		}
		echo "</td></tr><tr>";
		echo "<td class=\"steelgraudunkel\"><b><font size=\"-1\">&nbsp;" . _("Thema") . "</font></b></td><td class=\"steelgraudunkel\"><img src=\"pictures/forumleer.gif\" border=0 height=\"20\"></td><td class=\"steelgraudunkel\" align=\"right\"><font size=\"-1\">" . _("<b>Postings</b> / letzter Eintrag") . "&nbsp;&nbsp;</font></td></tr></table>\n";
		while ($db->next_record()) {
			$forumposting["id"] = $db->f("topic_id");
			$forumposting["name"] = $db->f("name");
			$forumposting["description"] = $db->f("description");
			$forumposting["author"] = $db->f("author");
			$forumposting["username"] = get_username($db->f("user_id"));
			$forumposting["rootid"] = $db->f("root_id");
			$forumposting["rootname"] = $db->f("root_name");
			$forumposting["mkdate"] = $db->f("mkdate");
			$forumposting["chdate"] = $db->f("chdate");
			$forumposting["foldercount"] = $db->f("count");
			$forumposting["folderlast"] = $db->f("last");
	
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
		.", mkdate, chdate, description, root_id, username from px_topics LEFT JOIN auth_user_md5 USING(user_id) where "
		." parent_id = '$topic_id'"
		." order by mkdate $sort";
	$db=new DB_Seminar;
	$db->query($query);
	$forumposting["lines"][$level] = $db->num_rows();
	WHILE ($db->next_record()) {
		
		$forumposting["id"] = $db->f("topic_id");
		$forumposting["name"] = $db->f("name");
		$forumposting["description"] = $db->f("description");
		$forumposting["author"] = $db->f("author");
		$forumposting["username"] = $db->f("username");
		$forumposting["rootid"] = $db->f("root_id");
		$forumposting["rootname"] = $db->f("root_name");
		$forumposting["mkdate"] = $db->f("mkdate");
		$forumposting["chdate"] = $db->f("chdate");
		$forumposting["level"] = $level;
					
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
?>