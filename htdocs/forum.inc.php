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

function editarea($write,$description,$nichtneu=FALSE,$zitat_id) {
	global $view,$topic_id,$user,$mehr;

	if ($nichtneu)
		$zusatz.= "<input type=hidden name=nichtneu value=TRUE><a href=\"forum.php?topic_id=$topic_id&view=$view&mehr=$mehr#anker\">" . makeButton("abbrechen", "img") . "</a>";
	else
		$zusatz = "<a href=\"forum.php?cmd=really_kill&topic_id=$write&view=$view&nurneu=1&mehr=$mehr#anker\">" . makeButton("abbrechen", "img") . "</a>";
	if ($write AND $write!=-1) {
		if ($description=="Dieser Beitrag wird gerade bearbeitet.")
			$description= _("Ihr Beitrag");
		if ($user->id == "nobody") {  // nicht angemeldete muessen Namen angeben
			if (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
				$postmp = strpos($description,"%%[editiert von");
				$description = substr_replace($description," ",$postmp);
			}
			if ($zitat_id!="") {
				$zitat = quote($zitat_id);
				$description="";
			}
			$description =	"<b>" . _("Ihr Name:") . "</b>&nbsp; <input type=text size=50 name=nobodysname onchange=\"pruefe_name()\" value=\"" . _("unbekannt") . "\"><br><br><input type=hidden name=update value='".$write."'>"
				."<div align=center><textarea name=description cols=80 rows=12>"
				.htmlReady($description)
				.htmlReady($zitat)
				."</textarea>";
		} else {
			if (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
				$postmp = strpos($description,"%%[editiert von");
				$description = substr_replace($description," ",$postmp);
			}
			if ($zitat_id!="") {
				$zitat = quote($zitat_id);
				$description="";
			}
			$description =	"<input type=hidden name=update value='".$write."'>"
				."<div align=center><textarea name=description cols=70 rows=12>"
				.htmlReady($description)
				.htmlReady($zitat)
				."</textarea>";
		}
		$description .= "<input type=hidden name=mehr value='".$mehr."'>";
		$description .= "<br><br><input type=image name=create value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0>&nbsp;"
			.$zusatz
			."</div>";		
		$edit = "";
	} else {
		// warum wir in die editarea kommen, wenn wir doch gar nicht editieren
		// wollen, weiss nur der liebe Ralf.
		// aber so ist hier der einzige mögliche Punkt, den formatReady einzuhängen..
		$description = formatReady($description); 
	}
	return $description;
}


//////////////////////////////////////////////////////////////////////////

function topic_liste ($eintrag, $root_id, $open, $name, $author, $create_dt, $root_name, $description, $username, $mehr, $show,$write,$modify_dt,$zitat) {
	global $PHP_SELF,$loginfilelast,$SessSemName,$forum,$view,$davor,$auth,$user;

// Funktion zeigt die Listen letzte 5 und neue an.

	$meineseite = $PHP_SELF;
	$datumtmp = $loginfilelast[$SessSemName[1]];
	if ($datumtmp < $modify_dt)
		$neuer_beitrag = TRUE;  //ist der Beitrag neu?

 // nicht aufgeklappt

	if (strstr($open,$eintrag)!=TRUE AND $show !=1 AND $show!=$eintrag AND !($neuer_beitrag==TRUE AND $forum["neuauf"]==1 AND !$write) AND ($davor!=$eintrag)) {
		$link =	$meineseite."?open=".$eintrag."&mehr=$mehr&view=$view#anker";
		$icon = NTForum("topic",$eintrag,$description,mila($name),$neuer_beitrag,$root_id);
		if (!$auth->is_authenticated() || $user->id == "nobody" || $author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
			$zusatz = htmlReady($author);
		else
			$zusatz = "<a class=\"printhead\" href=\"about.php?username=".$username."\">". htmlReady($author) ."&nbsp;</a>";
		$zusatz .="&nbsp;".date("d.m.Y - H:i", $create_dt)
			."&nbsp;<a href=\"".$PHP_SELF."?topic_id=".$root_id
			."&all=TRUE&open=$eintrag"
			."#anker\" class=\"printhead\">".htmlReady(mila($root_name,20))
			."</a>"
			."&nbsp; ";
		if (!(have_sem_write_perm()))  $zusatz .= "<a href=\"write_topic.php?write=1&root_id=".$root_id."&topic_id=".$eintrag."\" target=\"_new\"><img src=\"pictures/antwortnew.gif\" border=0 " . tooltip(_("Hier klicken um in einem neuen Fenster zu antworten")) . "></a>"; // Antwort-Pfeil
		//create a link onto the titel, too
		if ($link)
			$name = "<a href=\"$link\" class=\"tree\" >".htmlReady(mila($name))."</a>";
		else
			$name = htmlReady(mila($name));
			
		echo "<table width=\"90%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		printhead ("90%","0",$link,"close",$neuer_beitrag,$icon,$name,$zusatz,$create_dt);
		echo "</tr></table>\n";	

	} else { 
	
// aufgeklappt
		
		if ($root_id == $eintrag) {
			$leer = leer($eintrag);
			if ($leer==FALSE)
				$bild = "pictures/cont_folder.gif";
			else
				$bild = "pictures/cont_folder2.gif";
		} else
			$bild = "pictures/cont_blatt.gif";   //welches Symbol muss angezeigt werden?
		
		$edit = NTForum("reply",$eintrag,"0");
		$link = $meineseite."?mehr=$mehr&view=".$view."#anker"; 
		if ($forum["neuauf"]==1 AND $neuer_beitrag==TRUE)
			$link = ""; // zuklappen nur m&ouml;glich wenn neueimmerauf nicht gesetzt
		$icon = NTForum("topic",$eintrag,"","",$neuer_beitrag,$root_id);
		if (!$auth->is_authenticated() || $user->id == "nobody" || $author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
			$zusatz = htmlReady($author);
		else
			$zusatz = "<a class=\"printhead\" href=\"about.php?username=".$username."\">".htmlReady($author)."&nbsp;</a>";
		$zusatz .= "&nbsp;".date("d.m.Y - H:i", $create_dt)	
			."&nbsp;<a class=\"printhead\" href=\"".$PHP_SELF."?topic_id=".$root_id
			."&all=TRUE&open=$eintrag"
			."#anker\">".htmlReady(mila($root_name,20))
			."</a>"
			."&nbsp; ";
		if (!(have_sem_write_perm()))
			$zusatz .= "<a href=\"write_topic.php?write=1&root_id=".$root_id."&topic_id=".$eintrag."\" target=\"_new\"><img src=\"pictures/antwortnew.gif\" border=0 " . tooltip(_("Hier klicken um in einem neuen Fenster zu antworten")) . "></a>"; // antwort pfeil
		echo "<a name='anker'></a><table width=\"90%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		if ($write AND $eintrag!=$davor) {    //wir sind im Schreibmodus
			$form=TRUE;
			echo "<input type=hidden name=view value=$view>";
			echo "<input type=hidden name=open value=$topic_id>";
			echo "<input type=hidden name=topic_id value=$topic_id>";
			if (substr($name,0,3)!="Re:" AND $write!=-1)
				$name = "Re: ".$name; // RE: davor 
			$name = "<input type=text size=50 style='font-size:8 pt;font-weight:normal;' name=titel value='".htmlReady($name)."'>";
			$zusatz = ""; // beim editieren brauchen wir den Kram nicht
		} else {
			$name = htmlReady(mila($name));
		}
		//create a link onto the titel, too
		if ($link)
			$name = "<a href=\"$link\" class=\"tree\" >$name</a>";
		
		printhead ("90%","0",$link,"open",$neuer_beitrag,$icon,$name,$zusatz,$create_dt);
		echo "</tr></table>\n";	
		echo "<table width=\"90%\" border=0 cellpadding=0 cellspacing=0 align=center><tr>";
		if ($write==-1) {
			$write=$eintrag;
			$nichtneu=TRUE;
			$edit="";
		}
		if ($write AND $write!=-1)
			$edit="";	
		if ($eintrag!=$davor)
			$description = editarea($write,$description,$nichtneu,$zitat);
		else
			$description = formatReady($description);
		if (ereg("\[quote",$description) AND ereg("\[/quote\]",$description) AND (!$write OR $eintrag==$davor))
			$description = quotes_decode($description);
		printcontent ("100%",$form,$description,$edit);
		echo "</tr></table>\n";	
	}
	return;
}

//////////////////////////////////////////////////////////////////////////

function NTForum ($what, $r_topic_id, $description="", $name="", $neu="FALSE", $root_id="",$themenview=FALSE) {
	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow, $anfang, $forum, $open,$rechte,$view,$user,$cmd,$auth,$mehr;

	switch ($what) {

//es soll eine Ueberschriftenleiste im Forum angezeigt werden
		case "topic": 
			{
			if ($root_id == $r_topic_id) { //Wir sind bei einem Thema
				$leer = leer($r_topic_id);
				if ($leer==FALSE)
					$bild = "pictures/cont_folder.gif";
				else
					$bild = "pictures/cont_folder2.gif";
			} else
				$bild = "pictures/cont_blatt.gif";
	//JS Hovereffekt eingeschaltet
			if ($forum["jshover"]==1 AND $auth->auth["jscript"] AND $description!="") {      
				if ($themenview) { // wir kommen aus der Themenansicht
					$hoverlink = "<a href=\"forum.php?topic_id=".$r_topic_id."&all=TRUE#anker\" ";
					$txt = "<i>" . _("Hier klicken um alle Postings im Ordner zu &ouml;ffnen") . "</i>";
				} else {
					$hoverlink = "<a href=\"javascript:void(0);\" ";
					$txt = "";
				}
				$edit =	$hoverlink
					."onMouseOver=\"return overlib('"
					.JSReady($description,"forum").$txt
					."', CAPTION, '&nbsp;"
					.JSReady($name)
					."', NOCLOSE, CSSOFF)\" "
					." onMouseOut=\"nd();\"><img src=\"".$bild."\" border=0></a>";

			} else { 
	// ohne Hovern
				if ($themenview)
					$edit = "<a href=\"forum.php?topic_id=".$r_topic_id."&all=TRUE#anker\"><img src=\"".$bild."\" border=0 " . tooltip(_("Alle Postings im Ordner öffnen")) . "></a>";
				else
					$edit =	"<img src=\"".$bild."\">";
			}
			break;
		}
			
// es soll die Antwort-Zeile angehaengt werden

		case "reply": 
			{ if (!(have_sem_write_perm())) { // nur mit Rechten...		
					if (!$anfang)
						$anfang=$r_topic_id;

		// Die Buttonleiste:

				$edit = "<a href=\"forum.php?topic_id=".$anfang."&open=".$r_topic_id."&davor=".$r_topic_id."&view=".$view."&write=".$r_topic_id."&mehr=".$mehr."#anker\">&nbsp;" . makeButton("antworten", "img") . "</a>";
				$edit .= "<a href=\"forum.php?topic_id=".$anfang."&zitat=".$r_topic_id."&open=".$r_topic_id."&davor=".$r_topic_id."&view=".$view."&write=".$r_topic_id."&mehr=".$mehr."#anker\">&nbsp;" . makeButton("zitieren", "img") . "</a>";

				$lonely = lonely($r_topic_id);  //gibt es antworten? Wenn ja darf ich nicht bearbeiten
				if ($lonely==FALSE) // ich darf bearbeiten
					$edit .= "&nbsp;<a href=\"forum.php?topic_id=".$anfang."&open=".$r_topic_id."&write=-1&view=".$view."&mehr=".$mehr."#anker\">"
					. makeButton("bearbeiten", "img") . "</a>";

				if ($rechte || $lonely==FALSE)  // ich darf l&ouml;schen 
					$edit .= "&nbsp;<a href=\"forum.php?cmd=kill&topic_id=$r_topic_id&view=$view&mehr=$mehr\">"
					. makeButton("loeschen", "img") . "</a>";

				if ($rechte)  // ich darf verschieben
					$edit .= "&nbsp;<a href=\"forum.php?cmd=move&topic_id=$r_topic_id&view=$view\">"
					. makeButton("verschieben", "img") . "</a>";
				$lonely="";
				break;
			}

		// darf Nobody hier schreiben?

			elseif ($user->id == "nobody"){
				$db=new DB_Seminar;
				$db->query("SELECT Seminar_id FROM seminare WHERE Seminar_id='$SessionSeminar' AND Schreibzugriff=0");
				if ($db->num_rows())  {
					if (!$anfang)
						$anfang=$r_topic_id;
					$edit = "<a href=\"forum.php?topic_id=".$anfang."&open=".$r_topic_id."&davor=".$r_topic_id."&view=".$view."&write=".$r_topic_id."&mehr=".$mehr."#anker\">&nbsp;" . makeButton("antworten", "img") . "</a>";
					$edit .= "<a href=\"forum.php?topic_id=".$anfang."&zitat=".$r_topic_id."&open=".$r_topic_id."&davor=".$r_topic_id."&view=".$view."&write=".$r_topic_id."&mehr=".$mehr."#anker\">&nbsp;" . makeButton("zitieren", "img") . "</a>";
				} else
				$edit=""; // war kein nobody Seminar
			} 

		// nix mit Rechten

			else
				$edit = ""; 
		}
	}
	return $edit;
}

//////////////////////////////////////////////////////////////////////////

function DisplayKids ($topic_id=0, $level=0, $open=0, $lines="",$zitat="") {
	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow, $anfang, $forum,$rechte,$view,$write,$all,$davor,$auth,$user;

// stellt im Treeview alle Postings dar, die NICHT Thema sind

	if (!isset($anfang))
		$anfang = $topic_id;

	$query = "select topic_id, name, author "
		.", mkdate, chdate, description, root_id, username from px_topics LEFT JOIN auth_user_md5 USING(user_id) where "
		." parent_id = '$topic_id'"
		." order by mkdate $sort";
	$db=new DB_Seminar;
	$db->query($query);
	$lines[$level] = $db->num_rows();
	WHILE ($db->next_record()) {
		$r_topic_id = $db->f("topic_id");
		$r_name = $db->f("name");
		$r_author = $db->f("author");
		$r_mkdate = $db->f("mkdate");
		$r_chdate = $db->f("chdate");
		$r_description = $db->f("description");
		$root_id = $db->f("root_id");
		$username = $db->f("username");		
		echo "<table class=\"blank\" border=0 cellpadding=0 cellspacing=0 width=\"100%\"><tr><td class=\"blank\" nowrap valign=\"top\" ><img src='pictures/forumleer.gif'><img src='pictures/forumleer.gif'>";

	//Hier eine bezaubernde Routine um die Striche exakt wiederzugeben - keine Bange ich verstehe sie auch nicht mehr
		IF ($level){ 
			$striche = "";
			FOR ($i=0;$i<$level;$i++)	{
				IF ($i==($level-1)) {
					IF ($lines[$i+1]>1) $striche.= "<img src='pictures/forumstrich3.gif' border=0>"; 		//Kreuzung
					ELSE $striche.= "<img src='pictures/forumstrich2.gif' border=0>"; 				//abknickend
					$lines[$i+1] -= 1;
					}
				ELSE {
					IF ($lines[$i+1]==0) $striche .= "<img src='pictures/forumleer.gif' border=0>";		//Leerzelle
					ELSE $striche .= "<img src='pictures/forumstrich.gif' border=0>";				//Strich
					}
				}
			echo $striche;
			}
		echo "</td>";
		
		IF ($r_topic_id != $topic_id){
			$datumtmp = $loginfilelast[$SessSemName[1]];
			IF ($datumtmp < $r_chdate) $neuer_beitrag = TRUE;  //ist der Beitrag neu?
			ELSE $neuer_beitrag = FALSE;

//aufgeklappt und/oder neu:

			$openarray = explode(";",$open);
			IF (in_array($r_topic_id,$openarray) OR ($neuer_beitrag==TRUE AND $forum["neuauf"]==1 AND !$write) OR ($all==TRUE AND !$write) OR ($davor==$r_topic_id)) { 
			$db2=new DB_Seminar;
			$db2->query("SELECT *  from px_topics where parent_id = '$r_topic_id'");
			$letzter = $db2->num_rows();     // wenn $letzter = 0 ist gibt es keine Antwort auf das Posting mehr.
				IF  (!$auth->is_authenticated() || $user->id == "nobody" || $r_author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
					$zusatz = htmlReady($r_author);
				ELSE
					$zusatz = "<a class=\"printhead\" href=\"about.php?username=".$username."\">".htmlReady($r_author)."&nbsp;</a>";
				$zusatz .= "&nbsp;".date("d.m.Y - H:i", $r_mkdate)
					."&nbsp; ";
				IF (!(have_sem_write_perm()))
					$zusatz .= "<a class=\"printhead\" href=\"write_topic.php?write=1&root_id=".$root_id."&topic_id=".$r_topic_id."\" target=\"_new\"><img src=\"pictures/antwortnew.gif\" border=0 " . tooltip(_("Hier klicken um in einem neuen Fenster zu antworten")) . "></a>"; //user brauchen kein antworten
				IF ($write AND $davor!=$r_topic_id) {  // es wird geschrieben oder editiert
					$form = TRUE;
					echo "<input type=hidden name=view value=$view>";
					echo "<input type=hidden name=open value=$r_topic_id>";
					echo "<input type=hidden name=topic_id value=$anfang>";
					echo "<a name='anker'></a>";
					if (substr($r_name,0,3)!="Re:" AND $write!=-1) $r_name = "Re: ".$r_name;
					$r_name = "<input type=text size=50 style='font-size:8 pt;' name=titel value='".htmlReady($r_name)."'>";
					$zusatz = ""; //beim Editieren kein Zusatz
				} ELSE {
					$r_name = htmlReady(mila($r_name));
				}
				$link = "forum.php?topic_id=".$anfang."#anker";
				if ($forum["neuauf"]==1 AND $neuer_beitrag==TRUE)
					$link = ""; // zuklappen nur m&ouml;glich wenn neueimmerauf nicht gesetzt
				$icon = NTForum("topic",$r_topic_id,"","",$neuer_beitrag,$root_id);

	// Anker setzen
				if ($all==TRUE AND $open==$r_topic_id)
					echo "<a name='anker'></a>";  
				elseif (strpos($open,$r_topic_id)==0 AND !$write AND $all!=TRUE)
					echo "<a name='anker'></a>";  //es wird ein Anker gesetzt wenn der erste aufgeklapte Beitrag angespringen wird, etwa aus letzte5;		
				//create a link onto the titel, too
				if ($link)
					$r_name = "<a href=\"$link\" class=\"tree\" >$r_name</a>";

				printhead ("100%","0",$link,"open",$neuer_beitrag,$icon,$r_name,$zusatz,$r_mkdate);
// hier prozent
				echo "<td class=\"blank\">&nbsp;&nbsp;&nbsp;</td></tr></table>\n";		
				echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr>";
				$edit = NTForum("reply",$r_topic_id,"0");
				$striche = "<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer.gif'><img src='pictures/forumleer.gif'></td>";
				FOR ($i=0;$i<$level;$i++)	{
					IF ($lines[$i+1]==0) $striche .= "<td class=\"blank\" nowrap background='pictures/forumleer.gif'><img src='pictures/forumleer.gif'></td>";
					ELSE $striche .= "<td class=\"blank\" nowrap background='pictures/forumstrich.gif'><img src='pictures/forumleer2.gif'></td>";
					}
				IF ($letzter > 0) $striche.= "<td class=\"blank\" nowrap background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/forumleer.gif\"></td>";
				ELSE $striche.= "<td class=\"blank\" nowrap background=\"pictures/steel1.jpg\"><img src=\"pictures/forumleer.gif\"></td>";
				ECHO $striche;
				IF ($write==-1) {  // es wird ein bestehnder Beitrag editiert
					$write=$open;
					$nichtneu=TRUE;
					}
				IF ($write) $edit="";
				IF ($davor!=$r_topic_id AND $write)
					$r_description = editarea($write,$r_description,$nichtneu,$zitat);
			  	else
					$r_description = formatReady($r_description);
				IF(ereg("\[quote",$r_description) AND ereg("\[/quote\]",$r_description) AND (!$write OR $r_topic_id==$davor))  $r_description = quotes_decode($r_description); //it contains a quoting
				printcontent ("100%",$form,$r_description,$edit);
				echo "<td class=\"blank\">&nbsp;&nbsp;&nbsp;</td></tr></table>\n";	

//nicht aufgeklappt 

			}	else {
				$link = "forum.php?topic_id=".$anfang."&open=".$r_topic_id."#anker";
				$icon = NTForum("topic",$r_topic_id,$r_description,$r_name,$neuer_beitrag,$root_id);
				IF  (!$auth->is_authenticated() || $user->id == "nobody" || $r_author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
					$zusatz = htmlReady($r_author);
				ELSE $zusatz = "<a class=\"printhead\" href=\"about.php?username=".$username."\">".htmlReady($r_author)."&nbsp;</a>";
				$zusatz .= "&nbsp;".date("d.m.Y - H:i", $r_mkdate)
					."&nbsp; ";
				IF (!(have_sem_write_perm()))
					$zusatz .= "<a class=\"printhead\" href=\"write_topic.php?write=1&root_id=".$root_id."&topic_id=".$r_topic_id."\" target=\"_new\"><img src=\"pictures/antwortnew.gif\" border=0 " . tooltip(_("Hier klicken um in einem neuen Fenster zu antworten")) . "></a>"; // haben user Rechte? 
				//create a link onto the titel, too
				if ($link)
					$r_name = "<a href=\"$link\" class=\"tree\" >".htmlReady(mila($r_name))."</a>";
				else
					$r_name = htmlReady(mila($r_name));
				
				printhead ("100%","0",$link,"close",$neuer_beitrag,$icon,$r_name,$zusatz,$r_mkdate);
//zweiter Prozent
				echo "<td class=\"blank\">&nbsp;&nbsp;&nbsp;</td></tr></table>\n";			
				}	
			}
			DisplayKids($r_topic_id, $level+1, $open, $lines,$zitat);
		}
	}
	
/////////////////////////////////////////////////////////////////////////

function DisplayTopic ($datum=0, $topic_id=0, $open=0, $level=0, $nokids=0,$zitat="") {
	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow,$rechte,$i_page,$view, $write,$all,$forum,$cmd,$move_id,$auth,$user;

//Zeigt im Treeview die Themenordner an

	$fields = array("topic_id", "parent_id", "root_id", "name"
		, "description", "author", "author_host", "mkdate"
		, "chdate", "user_id");
	$query = "select distinct ";
	$comma = "";
	WHILE (list($key,$val)=each($fields)) {
		$query .= $comma."t.".$val;
		$comma = ", ";
	}
	$topicneu = $datum;
	$query .= ", count(*) as count, max(s.chdate) as last from px_topics t LEFT JOIN px_topics s USING(root_id) where t.topic_id = t.root_id AND t.Seminar_id = '$SessionSeminar' group by t.root_id  order by t.mkdate";
	$db=new DB_Seminar;
	$db->query($query);
	IF ($db->num_rows()==0) {  // Das Forum ist leer
		IF ($rechte)
			$text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Sie k&ouml;nnen oben unter <b>neues Thema</b> einen Ordner anlegen.");
		ELSE {
			if ($SessSemName["class"]=="inst")
				 $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Kontaktieren Sie eine/n TutorIn oder eine/n DozentIn dieser Veranstaltung, um Ordner anlegen zu lassen.");
			else
				 $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Kontaktieren Sie den/die AdministratorIn der Einrichtung, um Ordner anlegen zu lassen.");
		}
		echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
		parse_msg("info§$text");
		echo "</table>";
	} else {
		echo "<table class=\"blank\" width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr><td class=\"blank\" colspan=3>&nbsp;";
		IF ($write) {
			IF ($user->id == "nobody")
				echo "<form name=forumwrite onsubmit=\"return pruefe_name()\" method=post action=\"forum.php#anker\">\n";  // es wird geschrieben oder editiert
			ELSE
				echo "<form name=forumwrite method=post action=\"forum.php#anker\">\n";  // es wird geschrieben oder editiert
		}
		echo "</td></tr><tr>";
		echo "<td class=\"steelgraudunkel\"><b>&nbsp;" . _("Thema") . "</b></td><td class=\"steelgraudunkel\"><img src=\"pictures/forumleer.gif\" border=0 height=\"25\"></td><td class=\"steelgraudunkel\" align=\"right\">" . _("<b>Postings</b> / letzter Eintrag") . "&nbsp;</td></tr></table>\n";
		while ($db->next_record()) {
			$r_topic_id = $db->f("topic_id");
			$parent_id = $db->f("parent_id");
			$root_id = $db->f("root_id");
			$name = $db->f("name");
			$description = $db->f("description");
			$author = $db->f("author");
			$author_host = $db->f("author_host");
			$mkdate = $db->f("mkdate");
			$chdate = $db->f("chdate");
			$user_id = $db->f("user_id");
			$count = $db->f("count");
			$last = $db->f("last");
			
			$datumtmp = $loginfilelast[$SessSemName[1]];
			if ($datumtmp < $last)
				$neuer_beitrag = TRUE;
			$link = "forum.php?topic_id=".$r_topic_id."#anker";
			$icon = NTForum("topic",$r_topic_id,$description,"von ".$author.":",$neuer_beitrag,$root_id, TRUE);
			IF ($cmd=="move")  // ein Beitrag wird verschoben
				$icon =	 "<a href=\"forum.php?target=Thema&move_id=".$topic_id."&parent_id=".$r_topic_id."\">"
						."<img src=\"pictures/move.gif\" border=0 " . tooltip(_("Postings in diesen Ordner verschieben")) . "></a>"
						.$icon;
			$count -=1;
			$zusatz = "<b>".$count."</b> / ". date("d.m.Y - H:i", $last);
			IF (!(have_sem_write_perm()))
				$zusatz .=	"&nbsp; " // antwort-Pfeile
						."<a href=\"write_topic.php?write=1&root_id=".$root_id."&topic_id=".$root_id."\" target=\"_new\">"
						."<img src=\"pictures/antwortnew.gif\" border=0 " . tooltip(_("Hier klicken um in einem neuen Fenster zu antworten")) . "></a>";

// Aufgeklappt

					IF ($r_topic_id==$topic_id) {
						$db3= new DB_Seminar;
						$db3->query ("SELECT username FROM auth_user_md5 WHERE user_id='$user_id'");
						while($db3->next_record()) $username=$db3->f("username");
						IF  (!$auth->is_authenticated() || $user->id == "nobody" || $author=="unbekannt" || $username=="") // Nobody darf nicht auf die about...
							$zusatz = htmlReady($author)."&nbsp;/&nbsp; ".$zusatz;
						ELSE $zusatz = "<a class=\"printhead\" href=\"about.php?username=".$username."\">".htmlReady($author)."</a>&nbsp;/&nbsp; ".$zusatz;
						IF (!$open) $zusatz .= "<a name='anker'></a>";
						IF ($write AND $open==$topic_id) {  // es wird geschrieben oder editiert
							$form = TRUE;
							echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr>";
							echo "<input type=hidden name=view value=$view>";
							echo "<input type=hidden name=topic_id value=$r_topic_id>";
							echo "<a name='anker'></a></tr></table>\n";
							$name = "<input type=text size=50 style='font-size:8 pt;' name=titel value='".htmlReady($name)."'>";
							$zusatz = ""; // beim editieren kein Zusatz
						} else {
							$name = htmlReady(mila($name));
						}
						echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr>";
						IF (leer($r_topic_id)==FALSE)
							$icon ="<a href='forum.php?topic_id=".$r_topic_id."&all=TRUE#anker'><img src=\"pictures/cont_folder.gif\" border=0 " . tooltip(_("alle Postings im Ordner &ouml;ffnen")) . "></a>";
						ELSE
							$icon ="<a href='forum.php?topic_id=".$r_topic_id."&all=TRUE#anker'><img src=\"pictures/cont_folder2.gif\" border=0 " . tooltip(_("alle Postings im Ordner &ouml;ffnen")) . "></a>";
						//create a link onto the titel, too
						if (!$form)
							$name = "<a href=\"forum.php\" class=\"tree\" >$name</a>";

						printhead ("100%","0","forum.php","open",$neuer_beitrag,$icon,$name,$zusatz,$mkdate);

						echo "</tr></table>\n";
						$edit = NTForum("reply",$r_topic_id,"0");
						IF ($write==-1 AND $r_topic_id==$open) {
							$write=$r_topic_id;
							$nichtneu=TRUE;
							$edit="";
							}
						IF ($write AND $write!=-1) $edit="";
						IF ($write AND $r_topic_id==$open)
							$description = editarea($write,$description,$nichtneu,$zitat);
						ELSE
							$description = formatReady($description);
						echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr>";
					        IF(ereg("\[quote",$description) AND ereg("\[/quote\]",$description) AND (!$write OR $r_topic_id!=$open))  $description = quotes_decode($description);  //it contains a quoting
						printcontent ("99%",$form,$description,$edit);
						echo "</tr></table>\n";
						DisplayKids($topic_id, $level, $open,"",$zitat);

//nicht aufgeklappt

					} else {
						//create a link onto the titel, too
						if ($link)
							$name = "<a href=\"$link\" class=\"tree\" >".htmlReady(mila($name))."</a>";
						else
							$name = htmlReady(mila($name));
							
						echo "<table class=blank width=\"100%\" border=0 cellpadding=0 cellspacing=0><tr>";
						printhead ("100%","0",$link,"close",$neuer_beitrag,$icon,$name,$zusatz,$last);
						echo "</tr></table>\n";
						}
					$neuer_beitrag = FALSE;
				}
			echo "<table class=blank border=0 cellpadding=0 cellspacing=0 width=\"100%\"><tr><td class='blank'><img src='pictures/forumleer.gif' border=0 height='4'></td></tr><tr>";
			echo "<td align=center class=steelgraudunkel><img src='pictures/forumleer.gif' border=0 height='25' align=middle>";
			if ($rechte)
				echo "<a href='forum.php?neuesthema=TRUE#anker'><img src='pictures/forumgraurunt.gif' border=0 align=middle " . tooltip(_("Neues Thema anlegen")) . "><img src='pictures/cont_folder2.gif' " . tooltip(_("Neues Thema anlegen")) . " border=0 align=middle></a>";
			echo "</td></tr><tr><td class=blank>&nbsp; <br>&nbsp; <br></td></tr></table>\n";
			if ($write)
				echo "</form>\n";
			}
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
		print "<p>hey! insert failed, dammit.</p>\n";
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
		print "<p>hey! update failed, dammit.</p>\n";
		}
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function letzte5 ($open=0, $mehr=1, $show=0, $write, $update=0, $name="", $description="",$zitat)

{	global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow,$view,$rechte,$forum,$user;
if( !$mehr) $mehr ++;

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0 class="blank">
	<tr><td class="blank" colspan=2>&nbsp;
<?
	IF ($write) {
		IF  ($user->id == "nobody") echo "<form name=forumwrite onsubmit=\"return pruefe_name()\" method=post action=\"forum.php#anker\">\n";
		ELSE echo "<form name=forumwrite method=post action=\"forum.php#anker\">\n";
		}
?>	
</td></tr><tr><td colspan=2 class="blank">
<?
$limit = $mehr * 5;
$db = new DB_Seminar;
if ($view=="neue") {
	$db2=new DB_Seminar;
	$datumtmp = $loginfilelast[$SessSemName[1]];
	$db->query ("SELECT x.topic_id, x.name , x.author , x.mkdate, x.chdate , y.name AS root_name, x.description , x.Seminar_id, y.topic_id AS root_id, username FROM px_topics x LEFT JOIN auth_user_md5 USING(user_id), px_topics y WHERE x.root_id = y.topic_id AND x.chdate > '$datumtmp' AND x.Seminar_id = '$SessionSeminar' ORDER BY x.chdate DESC");
	$db2->query ("SELECT topic_id, name , author , mkdate , chdate , root_id, description , Seminar_id FROM px_topics WHERE chdate > '$datumtmp' AND Seminar_id = '$SessionSeminar' ORDER BY mkdate DESC");
	if  ($db2->affected_rows() == 0){
		echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
		parse_msg ("info§" . _("Seit Ihrem letzten Login gibt es keine neuen Beiträge."));
		echo "</table></td></tr></table></body></html>";
		die;
	}
} else {
	$db->query("SELECT x.topic_id, x.name , x.author , x.mkdate, x.chdate, y.name AS root_name, x.description, x.Seminar_id, y.topic_id AS root_id, username FROM px_topics x LEFT JOIN auth_user_md5 USING(user_id), px_topics y WHERE x.root_id = y.topic_id AND x.seminar_id = '$SessionSeminar' ORDER BY chdate DESC LIMIT $limit");
	IF ($db->num_rows()==0) {  // Das Forum ist leer
		IF ($rechte) $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Sie k&ouml;nnen oben unter <b>neues Thema</b> einen Ordner anlegen.");
		ELSE {
			if ($SessSemName["class"]=="inst")
				 $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Kontaktieren Sie eine/n TutorIn oder eine/n DozentIn dieser Veranstaltung, um Ordner anlegen zu lassen.");
			else
				 $text = _("In diesem Forum wurde noch kein Themenordner angelegt.<br>Kontaktieren Sie den/die AdministratorIn der Einrichtung, um Ordner anlegen zu lassen.");
		}
		echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
		parse_msg("info§$text");
		echo "</table></td></tr></table>";
		die;
		}
	}
	
?>	<table border=0 width="90%" cellspacing="0" cellpadding="0" align="center"><tr>
	<th width="100%">
	<?echo "<a href=\"forum.php?view=".$view."&mehr=".$mehr
		."&show=1\"><img src='pictures/forumleer.gif' border=0 height='25' align=middle><img src='pictures/forumgraurunt.gif' border=0 " . tooltip(_("Alle aufklappen")) . " align=middle><img src='pictures/forumleer.gif' border=0></a></th>";?>

	</tr></table>
<?
 while($db->next_record()){
	 topic_liste($db->f("topic_id"),$db->f("root_id"),$open,$db->f("name"),$db->f("author"),$db->f("mkdate"),$db->f("root_name"),$db->f("description"),$db->f("username"),$mehr,$show,$write,$db->f("chdate"),$zitat);
	}

$open = 0;
if ($view=="letzte") {
	echo "<table width=\"90%\" border=0 cellpadding=0 cellspacing=0 align='center'><tr><td class='blank'><img src='pictures/forumleer.gif' border=0 height='4'></td></tr><tr><td class=\"steelgraudunkel\" align=\"center\" ><a href=\"forum.php?view=".$view."&mehr=", $mehr+1, "\"><img src='pictures/forumgraurunt.gif' " . tooltip(_("zeig mir mehr")) . " border=0 align=middle></a><img src='pictures/forumleer.gif' height='23' border=0 align='middle'>";
	echo "<a href=\"forum.php?view=".$view."&mehr=", $mehr-1, "\"><img src='pictures/forumgraurauf.gif' " . tooltip(_("zeig mir weniger")) . " border=0 align=middle></a></td></tr>";
	echo "<tr><td class=\"blank\">&nbsp;<br><br></td></tr></table>";
} else {
	echo "<table width=\"90%\" border=0 cellpadding=0 cellspacing=0 align='center'><tr><td class='blank'><img src='pictures/forumleer.gif' border=0 height='4'></td></tr><tr><td class=\"steelgraudunkel\" align=\"center\" ><img src='pictures/forumleer.gif' height='23' border=0 valign='top' align='middle'>";
	echo "</td></tr>";
	echo "<tr><td class=\"blank\">&nbsp;<br><br></td></tr></table>";
	}
 echo "</td></tr>";
echo "</table><br>";
if ($write)
	echo "</form>\n";
}

//////////////////////////////////////////////////////////////////////////
?>
