<?
/*
archiv.inc.php - Funktionen zur Archivierung in Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

require_once "config.inc.php";
require_once "dates.inc.php";
require_once "datei.inc.php";


// Liefert den dump des Seminars


function dump_sem($sem_id)  
{
	global $TERMIN_TYP, $SEM_TYPE, $SEM_CLASS,$_fullname_sql;
	
	require_once("visual.inc.php");

	$dump = "";
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$db2->query ("SELECT * FROM seminare WHERE Seminar_id='$sem_id'");
	$db2->next_record();

		
//////////////////		

		$dump.="\n<table width=100% border=1 cellpadding=2 cellspacing=0>";
		$dump .= " <tr><td colspan=2 align=left class=\"topic\">";
		$dump .= "<H1 class=\"topic\">&nbsp;".htmlReady($db2->f('Name'),1,1)."</H1>";
		$dump.= "</td></tr>\n";

	//Grunddaten des Seminars, wie in den seminar_main

	if ($db2->f('Untertitel')!="")  
		$dump.="<tr><td width=\"15%\"><b>Untertitel: </b></td><td>".htmlReady($db2->f('Untertitel'),1,1)."</td></tr>\n";
	
	if (view_turnus($sem_id, FALSE))
		$dump.="<tr><td width=\"15%\"><b>Zeit: </b></td><td>".view_turnus($sem_id, FALSE)."</td></tr>\n";
	
	if (get_semester($sem_id))
		$dump.="<tr><td width=\"15%\"><b>Semester: </b></td><td>".get_semester($sem_id)."</td></tr>\n";

	if (veranstaltung_beginn($sem_id))
		$dump.="<tr><td width=\"15%\"><b>Erster Termin: </b></td><td>".veranstaltung_beginn($sem_id)."</td></tr>\n";
		
	if (vorbesprechung($sem_id))
		$dump.="<tr><td width=\"15%\"><b>Vorbesprechung: </b></td><td>".vorbesprechung($sem_id)."</td></tr>\n";
		
	if ($db2->f('Ort')!="")  
		$dump.="<tr><td width=\"15%\"><b>Ort: </b></td><td>".htmlReady($db2->f('Ort'),1,1)."</td></tr>\n";

	//wer macht den Dozenten?
	$db=new DB_Seminar;
	$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'dozent' ORDER BY Nachname");

	if ($db->affected_rows() > 1)
		$dump.= "<tr><td width=\"15%\"><b>DozentInnen: </b></td><td>";
	else
		$dump.= "<tr><td width=\"15%\"><b>DozentIn: </b></td><td>";
	while ($db->next_record()) 
		$dump.= $db->f("fullname") ."<br>  ";
	$dump.="</td></tr>\n";
		
	//und wer ist Tutor?
	$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'tutor' ORDER BY Nachname");

	if ($db->affected_rows() > 1)
		$dump.="<tr><td width=\"15%\"><b>TutorInnen: </b></td><td>";
	elseif ($db->affected_rows() == 1)
		$dump.="<tr><td width=\"15%\"><b>TutorIn: </b></td><td>";
	while ($db->next_record()) 
		$dump.= $db->f("fullname")."<br>";
	if ($db->affected_rows())
		$dump.="</td></tr>\n";
		
	if ($db2->f("status")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Typ der Veranstaltung:&nbsp;</b></td><td align=left>";
		$dump.= $SEM_TYPE[$db2->f("status")]["name"]." in der Kategorie <b>".$SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["name"]."</b></td></tr>\n";
		}
	if ($db2->f("art")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Art der Veranstaltung:&nbsp;</b></td><td align=left>";
		$dump .= htmlReady($db2->f("art"),1,1)."</td></tr>\n";
		}
	if ($db2->f("VeranstaltungsNummer"))
		{
		$dump .="<tr><td width=\"15%\">";
		$dump .="<b>Veranstaltungsnummer:&nbsp;</b></td><td width=75% align=left>";
		$dump.= $db2->f("VeranstaltungsNummer")."</td></tr>\n";
		}
	if ($db2->f("ects")!="")
		{
		$dump .="<tr><td width=\"15%\">";
		$dump .="<b>ECTS-Punkte:&nbsp;</b></td><td width=75% align=left>";
		$dump.= $db2->f("ects")."</td></tr>\n";
		}
	if ($db2->f("Beschreibung")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Beschreibung:&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("Beschreibung"),1,1)."</td></tr>\n";
		}
	if ($db2->f("teilnehmer")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Teilnehmer:&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("teilnehmer"),1,1)."</td></tr>\n";
		}
	if ($db2->f("vorrausetzungen")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Voraussetzungen:&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("vorrausetzungen"),1,1)."</td></tr>\n";
		}
	if ($db2->f("lernorga")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Lernorganisation:&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("lernorga"),1,1)."</td></tr>\n";
		}
	if ($db2->f("leistungsnachweis")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Leistungsnachweis:&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("leistungsnachweis"),1,1)."</td></tr>\n";
		}
	if ($db2->f("Sonstiges")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>Sonstiges:&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("Sonstiges"),1,1)."</td></tr>\n";
		}

// Fakultaeten...

	$db3->query("SELECT DISTINCT Fakultaeten.Name FROM Fakultaeten LEFT JOIN Institute USING (Fakultaets_id) LEFT JOIN seminar_inst USING (institut_id) LEFT JOIN seminare USING (Institut_id) WHERE seminare.Seminar_id = '$sem_id' OR seminar_inst.seminar_id = '$sem_id'");
	IF ($db3->affected_rows() > 0)
		{
		$dump.= "<tr><td width=\"15%\"><b>Fakult&auml;t:&nbsp;</b></td><td>";
		WHILE ($db3->next_record())
			$dump.= htmlReady($db3->f("Name"))."<br>";
		$dump.= "</td></tr>\n";
		}	
	
// Faecher...

	$db3->query("SELECT DISTINCT faecher.name FROM faecher LEFT JOIN bereich_fach USING (fach_id) LEFT JOIN seminar_bereich USING (bereich_id) WHERE seminar_id = '$sem_id'");
	IF ($db3->affected_rows() > 0)
		{
		$dump.= "<tr><td width=\"15%\"><b>F&auml;cher:&nbsp;</b></td><td>";
		WHILE ($db3->next_record())
			$dump.= htmlReady($db3->f("name"))."<br>";
		$dump.= "</td></tr>\n";
		}	

// Anzeige der Bereiche  
		
	$db3->query("SELECT bereiche.* FROM bereiche LEFT JOIN seminar_bereich USING(bereich_id) WHERE seminar_id = '$sem_id'");
	IF ($db3->affected_rows() > 0)
		{
		$dump.= "<tr><td width=\"15%\"><b>Studienbereiche:&nbsp;</b></td><td>";
		WHILE ($db3->next_record())
			$dump.= htmlReady($db3->f("name"))."<br>";
		$dump.= "</td></tr>\n";
		}	
		
	$iid=$db2->f("Institut_id");
	$db3->query("SELECT Name, url FROM Institute WHERE Institut_id = '$iid'");
	$db3->next_record();
	$dump.="<tr><td width=\"15%\"><b>Heimat-Einrichtung:&nbsp;</b></td><td>".$db3->f("Name")."</td></tr>\n";
	$db3->query("SELECT Name, url FROM seminar_inst LEFT JOIN Institute USING (institut_id) WHERE seminar_id = '$sem_id' AND Institute.institut_id != '$iid'");
	$cd=$db3->affected_rows();
	if ($db3->affected_rows() == 1)
		$dump.="<tr><td width=\"15%\"><b>beteiligte Einrichtung:&nbsp;</b></td><td>";
	else if ($db3->affected_rows() >= 2)
		$dump.="<tr><td width=\"15%\"><b>beteiligte Einrichtungen:&nbsp;</b></td><td>";
	
	while ($db3->next_record()) {
		$cd--;
		$dump.= htmlReady($db3->f("Name"));
		if ($cd >= 1) $dump.=",&nbsp;";
	}
	if ($db3->affected_rows())
		$dump.="</td></tr>\n";

	//Teilnehmeranzahl
	$dump.= "<tr><td width=\"15%\"><b>max. Teilnehmeranzahl:&nbsp;</b></td><td>".$db2->f("admission_turnout")."</td></tr>\n";

	//Statistikfunktionen

	$db3->query("SELECT count(*) as anzahl FROM seminar_user WHERE Seminar_id = '$sem_id'");
	$db3->next_record();
	$dump.= "<tr><td width=\"15%\"><b>Anzahl der angemeldeten Teilnehmer:&nbsp;</b></td><td>".$db3->f("anzahl")."</td></tr>\n";

	$db3->query("SELECT count(*) as anzahl FROM px_topics WHERE Seminar_id = '$sem_id'");
	$db3->next_record();
	$dump.= "<tr><td width=\"15%\"><b>Postings:&nbsp;</b></td><td>".$db3->f("anzahl")."</td></tr>\n";

	$db3->query("SELECT count(*) as anzahl FROM dokumente WHERE Seminar_id='$sem_id'");
	$db3->next_record();
	$docs=$db3->f("anzahl");
	$dump.= "<tr><td width=\"15%\"><b>Dokumente:&nbsp;</b></td><td>".$docs."</td></tr>\n";

	$dump.= "</table>\n";

// Ablaufplan

    $db->query("SELECT *  FROM termine WHERE (range_id='$sem_id' AND date_typ ='1') ORDER BY date");
    if ($db->num_rows())
	{
	$dump.="<br>";	  
	$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
	$dump .= " <tr><td colspan=2 align=left class=\"topic\">";
	$dump .= "<H2 class=\"topic\">&nbsp;Ablaufplan</H2>";
	$dump.= "</td></tr>\n";

	while ($db->next_record())
		   {
		   $dump.="<tr align=\"center\"> ";
		   $dump.= "<td width=\"25%\" align=\"left\" >";
		   $dump.= strftime("%d. %b. %Y, %H:%M", $db->f("date"));
		   $dump.= "</td>";
		   $dump.= "<td width=\"75%\" align=\"left\"> ";
		   $dump.= $TERMIN_TYP[$db->f("date_typ")]["name"].": ".htmlReady($db->f("content"),1,1);
		   $dump.= "&nbsp;</td></tr>\n";
		   if ($db->f("description")) {
		   	$dump.="<tr><td width=\"25%\">&nbsp;</td>";
		   	$dump.= "<td width=\"75%\">".htmlReady($db->f("description"),1,1)."</td></tr>\n";
		   	}
		   }
	$dump .= "</table>\n";
	}
	
// zusaetzliche Termine

    $db->query("SELECT *  FROM termine WHERE (range_id='$sem_id' AND date_typ!='1') ORDER BY date");
    if ($db->num_rows())
	{
	$dump.="<br>";	  
	$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
	$dump .= " <tr><td colspan=2 align=left class=\"topic\">";
	$dump .= "<H2 class=\"topic\">&nbsp;zus&auml;tzliche Termine</H2>";
	$dump.= "</td></tr>\n";

	while ($db->next_record())
		   {
		   $dump.="<tr align=\"center\"> ";
		   $dump.= "<td width=\"25%\" align=\"left\" >";
		   $dump.= strftime("%d. %b. %Y, %H:%M", $db->f("date"));
		   $dump.= "</td>";
		   $dump.= "<td width=\"75%\" align=\"left\"> ";
		   $dump.= $TERMIN_TYP[$db->f("date_typ")]["name"].": ".htmlReady($db->f("content"),1,1);
		   $dump.= "&nbsp;</td></tr>\n";
		   if ($db->f("description")) {
		   	$dump.="<tr><td width=\"25%\">&nbsp;</td>";
		   	$dump.= "<td width=\"75%\">".htmlReady($db->f("description"),1,1)."</td></tr>\n";
		   	}
		   }
	$dump .= "</table>\n";
	}
	
// Literatur & Links

	$db->query("SELECT * FROM literatur WHERE range_id='$sem_id'");
	if ($db->num_rows()) {
  	$db->next_record();
  	$literatur=$db->f("literatur");
  	$literatur=FixLinks(htmlReady($literatur)); // /newline fixen
  	$links=$db->f("links");
  	$links=FixLinks(htmlReady($links)); // /newline fixen
     
  	IF(!empty($literatur)) {
 			$dump.="<br>";	  
  		$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td align=left class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;Literatur:</H2>";
			$dump.= "</td></tr>\n";
			$dump.="<tr><td align=\"left\" width=\"100%\"><br>".$literatur."<br></td></tr>\n";
			$dump .= "</table>\n";
		}
    
		IF(!empty($links)) {
  		$dump.="<br>";	  
    	$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td align=left class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;Links:</H2>";
			$dump.= "</td></tr>\n";
			$dump.="<tr><td align=\"left\" width=\"100%\"><br>".$links."<br></td></tr>\n";
			$dump .= "</table>\n";
		}
	}

// Dateien anzeigen


	$db=new DB_Seminar;
	$i=0;
	
//Auslesen aller allgemeine Dokumente zum Seminar
	$doc_ids=doc_challenge($sem_id);
	if (count($doc_ids))
		foreach ($doc_ids as $a) {
			$db->query ("SELECT dokument_id, dokumente.description, filename, dokumente.mkdate, filesize, dokumente.user_id, username, Nachname  FROM dokumente LEFT JOIN auth_user_md5 ON auth_user_md5.user_id = dokumente.user_id WHERE dokument_id = '$a'");
			$db->next_record();
			$dbresult[$i]=array("mkdate"=>$db->f("mkdate"), "dokument_id"=>$db->f("dokument_id"), "description"=>$db->f("description"), "filename"=>$db->f("filename"), "filesize"=>$db->f("filesize"),"user_id"=> $db->f("user_id"), "username"=>$db->f("username"), "nachname"=>$db->f("Nachname"));
			$i++;
		}
		
//Auslesen der Dokumente zu Terminen
	$db->query ("SELECT termin_id FROM termine WHERE range_id ='$sem_id'");
	while ($db->next_record()) {
		$doc_ids=doc_challenge($db->f("termin_id"));
		if (count($doc_ids))
			foreach ($doc_ids as $a) {
				$db2->query ("SELECT dokument_id, dokumente.description, filename, dokumente.mkdate, filesize, dokumente.user_id, username, Nachname  FROM dokumente LEFT JOIN auth_user_md5 ON auth_user_md5.user_id = dokumente.user_id WHERE dokument_id = '$a'");
				$db2->next_record();
				$dbresult[$i]=array("mkdate"=>$db2->f("mkdate"), "dokument_id"=>$db2->f("dokument_id"), "description"=>$db2->f("description"), "filename"=>$db2->f("filename"), "filesize"=>$db2->f("filesize"),"user_id"=> $db2->f("user_id"), "username"=>$db2->f("username"), "nachname"=>$db2->f("Nachname"));	
				$i++;
			}
	}
	
	if (!sizeof($dbresult)==0) {
		$dump.="<br>";	  
  	$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
		$dump .= " <tr><td align=left colspan=3 class=\"topic\">";
		$dump .= "<H2 class=\"topic\">&nbsp;Dateien:</H2>";
		$dump.= "</td></tr>\n";

		rsort ($dbresult);
		
		for ($i=0; $i<sizeof($dbresult); $i++) {
			$doc_id = $dbresult[$i]["dokument_id"];
			$sizetmp = $dbresult[$i]["filesize"];
			$sizetmp = ROUND($sizetmp / 1024);
			$size = "(".$sizetmp." KB)";

      $dump.="<tr><td width='100%'><b>".htmlReady($dbresult[$i]["filename"])."</b><br>".htmlReady($dbresult[$i]["description"])."&nbsp;".$size."</td><td>".
	    $dbresult[$i]["nachname"] .
	    "&nbsp;</td><td>&nbsp;".date("d.m.Y", $dbresult[$i]["mkdate"])."</td></tr>\n";
		}
		$dump.="</table>\n";
	}


// Teilnehmer

	$gruppe = array ("dozent" => "Dozenten",
		  "tutor" => "Tutoren",
		  "autor" => "Autoren",
		  "user" => "Leser");
	$dump.="<br>";	  
	while (list ($key, $val) = each ($gruppe)) {	  

// die eigentliche Teil-Tabelle

		$sortby = "doll DESC";
		$db=new DB_Seminar;
		$db2=new DB_Seminar;
		$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status, count(topic_id) AS doll FROM seminar_user LEFT JOIN px_topics USING (user_id,Seminar_id) LEFT JOIN auth_user_md5 ON (seminar_user.user_id=auth_user_md5.user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = '$key'  GROUP by seminar_user.user_id ORDER BY $sortby");

		IF (!$db->affected_rows() == 0) {//haben wir in der Personengattung ueberhaupt einen Eintrag?
	  	$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td align=left colspan=4 class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;".$val."</H2>";
			$dump.= "</td></tr>\n";
			$dump.="<th width=\"30%%\">Name</th>";
			$dump.="<th width=\"10%%\">Postings</th>";
			$dump.="<th width=10%><b>Dokumente</b></th></tr>\n";
			
			while ($db->next_record()) {
				$dump.="<tr><td>";
				$dump.= $db->f("fullname");
				$dump.="</td><td align=center>";
				$dump.= $db->f("doll");
				$dump.="</td><td align=center>";

				$Dokumente = 0;
				$UID = $db->f("user_id");
				//???!!!
				//$db2->query ("SELECT count(dokument_id) AS doll FROM termine LEFT JOIN dokumente ON termine.termin_id = dokumente.range_id WHERE termine.range_id = '$sem_id' AND dokumente.user_id = '$UID' GROUP by termine.range_id");
				//while ($db2->next_record()) {
				//	$Dokumente += $db2->f("doll");
				//}
				$db2->query ("SELECT count(dokument_id) AS doll FROM dokumente WHERE dokumente.Seminar_id = '$sem_id' AND dokumente.user_id = '$UID'");
				while ($db2->next_record()) {
					$Dokumente += $db2->f("doll");
				}
				$dump.= $Dokumente;
				$dump.="</td>";
				$dump.="</tr>\n";
	
			} // eine Zeile zuende

			$dump.= "</table>\n";
		}
	} // eine Gruppe zuende

 	return $dump;
	
} // end function dump_sem($sem_id)


/////// die beiden Funktionen um das Forum zu exportieren

function Export_Kids ($topic_id=0, $level=0)
{
// global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow, $anfang, $forum,$rechte,$view,$write,$all,$davor,$auth,$user;
// stellt im Treeview alle Postings dar, die NICHT Thema sind

	IF (!isset($anfang)) $anfang = $topic_id;
	$query = "select topic_id, name, author "
		.", mkdate, chdate, description, root_id, username from px_topics LEFT JOIN auth_user_md5 USING(user_id) where "
		." parent_id = '$topic_id'"
		." order by mkdate";
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

		IF ($r_topic_id != $topic_id) {
			$r_name = htmlReady($r_name);
			$zusatz = htmlReady($r_author)." am ";
			$zusatz .= date("d.m.Y - H:i", $r_mkdate);
			$r_description = FixLinks(format(htmlReady($r_description, $trim, FALSE)));
			IF(ereg("\[quote",$r_description) AND ereg("\[/quote\]",$r_description) AND !$write)  {
      				$r_description = quotes_decode($r_description);
				}
			$forum_dumbkid.="<tr><td class=blank><hr><b>".$r_name."</b> von ".$zusatz."</td></tr><tr><td class=blank>".$r_description."</td></tr>\n";	
			}
		$forum_dumbkid.=Export_Kids($r_topic_id, $level+1);
		}
	RETURN $forum_dumbkid;
	}
	
function Export_Topic ($sem_id)
{
 global $SessionSeminar,$SessSemName;
// global $SessionSeminar,$SessSemName,$loginfilelast,$loginfilenow,$rechte,$i_page,$view, $write,$all,$forum,$cmd,$move_id,$auth,$user;
	$datum=0;
	$topic_id=0;
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
	$query .= ", count(*) as count, max(s.chdate) as last from px_topics t LEFT JOIN px_topics s USING(root_id) where t.topic_id = t.root_id AND t.Seminar_id = '$sem_id' group by t.root_id  order by t.mkdate";
	$db=new DB_Seminar;
	$db->query($query);
	IF ($db->num_rows()==0) {  // Das Forum ist leer
		$text = "Das Forum ist leer";
		$forum_dumb="<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>".$text."</table>";
		}
	ELSE {
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
			$count -=1;
			$last = date("YmdHis", $last);
			$zusatz = "<b>".$count."</b> / ".substr($last,6,2).".".substr($last,4,2).".". substr($last,0,4)." - ".substr($last,8,2).":". substr($last,10,2);
			$zusatz = htmlReady($author)."&nbsp;/&nbsp; ".$zusatz;
			$name = htmlReady($name);
			$description = FixLinks(format(htmlReady($r_description, $trim, FALSE)));
		        IF(ereg("\[quote\]",$description) AND ereg("\[/quote\]",$description) AND !$write)  $description = quotes_decode($description);
			$forum_dumb.="<table class=blank width=\"100%\" border=0 cellpadding=5 cellspacing=0><tr><td><h3>".$name."</h3> von ".$zusatz."</td></tr><tr><td class=blank>".$description. "</td></tr>";
			$forum_dumb.=Export_Kids($r_topic_id, $level);
			$forum_dumb.="</table><br><br>";
			$neuer_beitrag = FALSE;
			}
		}
RETURN $forum_dumb;
}



//Funktion zum archivieren eines Seminars, sollte in der Regel vor dem Loeschen ausgfuehrt werden.
function in_archiv ($sem_id)
{
	global $SEMESTER, $ABSOLUTE_PATH_STUDIP, $UPLOAD_PATH, $ARCHIV_PATH, $TMP_PATH, $ZIP_PATH, $_fullname_sql;
	
	$hash_secret="frauen";

	$db=new DB_Seminar;
	$db2=new DB_Seminar;


	//Besorgen der Grunddaten des Seminars
	
	$db->query("SELECT * FROM seminare WHERE Seminar_id = '$sem_id'");

	$db->next_record();
	$seminar_id = $db->f("Seminar_id");
	$name = $db->f("Name");
	$untertitel = $db->f("Untertitel");
	$beschreibung = $db->f("Beschreibung");
	$start_time = $db->f("start_time");
	$heimat_inst_id = $db->f("Institut_id");
	
	//Besorgen von einzelnen Daten zu dem Seminar
	
	$i=0;
	for ($i; $i<=sizeof($SEMESTER); $i++)
		{
		if (($start_time >= $SEMESTER[$i]["beginn"]) && ($start_time <= $SEMESTER[$i]["ende"])) $semester_tmp=$SEMESTER[$i]["name"];
		}
	
	$db2->query("SELECT faecher.name FROM faecher LEFT JOIN bereich_fach USING (fach_id) LEFT JOIN seminar_bereich USING (bereich_id) WHERE seminar_id = '$seminar_id'");
	$db2->next_record();
	$fach=$db2->f("name");
	while ($db2->next_record())
		{
		$fach=$fach.", ".$db2->f("name");
		}	

	$db2->query("SELECT bereiche.name FROM bereiche LEFT JOIN seminar_bereich USING (bereich_id) WHERE seminar_id = '$seminar_id'");
	$db2->next_record();
	$bereich=$db2->f("name");
	while ($db2->next_record())
		{
		$bereich=$bereich.", ".$db2->f("name");
		}
	
	// das Heimatinstitut als erstes
	$db2->query("SELECT Name FROM Institute WHERE Institut_id = '$heimat_inst_id'");
	$db2->next_record();
	$institute = $db2->f("Name");

	// jetzt den Rest
	$db2->query("SELECT Name FROM Institute LEFT JOIN seminar_inst USING (institut_id) WHERE seminar_id = '$seminar_id' AND Institute.Institut_id != '$heimat_inst_id'");
	while ($db2->next_record())
		{
		$institute=$institute.", ".$db2->f("Name");
		}
	
	$db2->query("SELECT " . $_fullname_sql['full'] . " AS fullname FROM seminar_user  LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_id = '$seminar_id' AND seminar_user.status='dozent'");
	$db2->next_record();
	$dozenten=$db2->f("fullname");
	while ($db2->next_record())
		{
		$dozenten=$dozenten.", ".$db2->f("fullname");
		}

	$db2->query("SELECT Fakultaeten.Fakultaets_id FROM Fakultaeten LEFT JOIN Institute USING (Fakultaets_id)  LEFT JOIN seminare USING (Institut_id) WHERE seminare.Seminar_id = '$seminar_id'");
	$db2->next_record();
	$fakultaet_id=$db2->f("Fakultaets_id");

	$db2->query("SELECT DISTINCT Fakultaeten.Name FROM Fakultaeten LEFT JOIN Institute USING (Fakultaets_id) LEFT JOIN seminar_inst USING (institut_id) WHERE seminar_inst.seminar_id = '$seminar_id'");
	$db2->next_record();
	$fakultaet=$db2->f("Name");
	while ($db2->next_record())
		{
		$fakultaet=$fakultaet.", ".$db2->f("Name");
		}

	// Schreiben Datenbank -> Datenbank
	
	$name = addslashes($name);
	$untertitel = addslashes($untertitel);
	$beschreibung = addslashes($beschreibung);
	$institute = addslashes($institute);
	$bereich = addslashes($bereich);		
	$dozenten = addslashes($dozenten);		
	$fakultaet = addslashes($fakultaet);	

	//Dump holen

	$dump = addslashes(dump_sem($sem_id));

	//Forumdump holen

	$forumdump = addslashes(export_topic($sem_id));
	
	//OK, naechster Schritt: Kopieren der Personendaten aus seminar_user in archiv_user

	$db->query("SELECT * FROM seminar_user WHERE Seminar_id = '$seminar_id'");
	while ($db->next_record())
		{
		$seminar_id=$db->f("Seminar_id");
		$user_id=$db->f("user_id");
		$status=$db->f("status");
		$db2->query("INSERT INTO archiv_user SET seminar_id='$seminar_id', user_id='$user_id', status='$status' ");
		}
	
	//OK, letzter Schritt: ZIPpen der Dateien des Seminars und Verschieben in eigenes Verzeichnis

	
	$archiv_file_id=md5(uniqid($hash_secret));
	$docs=0;	
	
	//temporaeres Verzeichnis anlegen
	exec ("mkdir $TMP_PATH/$archiv_file_id");
	$tmp_full_path="$TMP_PATH/$archiv_file_id";
	
	//globale Seminardokumente unter richtigem Namen ins temporaere Verzeichnis kopieren
	$doc_ids=doc_challenge ($seminar_id);
	if (is_array($doc_ids))
		foreach ($doc_ids as $a) {
			$db->query("SELECT dokument_id, filename FROM dokumente WHERE dokument_id = '$a'");
			if ($db->next_record()) {
				$docs++;
				exec ("cp '$UPLOAD_PATH/".$a."' '$tmp_full_path/[".($docs)."] ".$db->f("filename") ."'");
				}
			}

	//Dokumente zu Terminen unter richtigem Namen ins temporaere Verzeichnis kopieren
	$db->query ("SELECT termin_id FROM termine WHERE range_id='$seminar_id'");
	while ($db->next_record()) {
		$doc_ids=doc_challenge ($db->f("termin_id"));
		if (is_array($doc_ids))
			foreach ($doc_ids as $a) {
				$db2->query("SELECT dokument_id, filename FROM dokumente WHERE dokument_id = '$a'");
				if ($db2->next_record()) {
					$docs++;
					exec ("cp '$UPLOAD_PATH/".$a."' '$tmp_full_path/[".($docs)."] ".$db2->f("filename") ."'");
					}
				}
			}

	//Alles zippen
	if ($docs) {
	 	exec ($ZIP_PATH." -9 -j ".$ARCHIV_PATH."/".$archiv_file_id." ".$tmp_full_path."/*.* ");
	 	exec ("mv ".$ARCHIV_PATH."/".$archiv_file_id.".zip ".$ARCHIV_PATH."/".$archiv_file_id);
	 	exec ("rm $tmp_full_path/*.*");
	 	exec ("rmdir $TMP_PATH/$archiv_file_id");
	 	}
	else
		$archiv_file_id="";
	
	//Reinschreiben von diversem Klumpatsch in die Datenbank
	$db->query("INSERT INTO archiv VALUES ('$seminar_id', '$name', '$untertitel', '$beschreibung', '$start_time', '$semester_tmp', '$fach', '$bereich', '$heimat_inst_id', '$fakultaet_id', '$institute', '$dozenten', '$fakultaet', '$dump', '$archiv_file_id', '".time()."','$forumdump')");
}


?>
