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

require_once ("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/datei.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once ("$ABSOLUTE_PATH_STUDIP/language.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/DataFields.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/Modules.class.php");
require_once($ABSOLUTE_PATH_STUDIP . "/lib/classes/StudipLitList.class.php");
require_once ("$ABSOLUTE_PATH_STUDIP/lib/classes/SemesterData.class.php");



// Liefert den dump des Seminars
function dump_sem($sem_id) {
	global $TERMIN_TYP, $SEM_TYPE, $SEM_CLASS,$_fullname_sql,$AUTO_INSERT_SEM;
	
	$dump = "";
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	$DataFields = new DataFields($sem_id);
	$Modules = new Modules;	
	$Modules = $Modules->getLocalModules($sem_id);	

	$db2->query ("SELECT * FROM seminare WHERE Seminar_id='$sem_id'");
	$db2->next_record();

	$dump.="\n<table width=100% border=1 cellpadding=2 cellspacing=0>";
	$dump .= " <tr><td colspan=2 align=left class=\"topic\">";
	$dump .= "<H1 class=\"topic\">&nbsp;".htmlReady($db2->f('Name'),1,1)."</H1>";
	$dump.= "</td></tr>\n";

	//Grunddaten des Seminars, wie in den seminar_main

	if ($db2->f('Untertitel')!="")  
		$dump.="<tr><td width=\"15%\"><b>" . _("Untertitel:") . " </b></td><td>".htmlReady($db2->f('Untertitel'),1,1)."</td></tr>\n";
	
	if (view_turnus($sem_id, FALSE))
		$dump.="<tr><td width=\"15%\"><b>" . _("Zeit:") . " </b></td><td>".view_turnus($sem_id, FALSE)."</td></tr>\n";
	
	if (get_semester($sem_id))
		$dump.="<tr><td width=\"15%\"><b>" . _("Semester:") . " </b></td><td>".get_semester($sem_id)."</td></tr>\n";

	if (veranstaltung_beginn($sem_id))
		$dump.="<tr><td width=\"15%\"><b>" . _("Erster Termin:") . " </b></td><td>".veranstaltung_beginn($sem_id)."</td></tr>\n";
		
	if (vorbesprechung($sem_id))
		$dump.="<tr><td width=\"15%\"><b>" . _("Vorbesprechung:") . " </b></td><td>".vorbesprechung($sem_id)."</td></tr>\n";
		
	if (getRoom($sem_id, FALSE))  
		$dump.="<tr><td width=\"15%\"><b>" . _("Ort:") . " </b></td><td>".getRoom($sem_id, FALSE)."</td></tr>\n";

	//wer macht den Dozenten?
	$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'dozent' ORDER BY Nachname");

	if ($db->affected_rows() > 1)
		$dump.= "<tr><td width=\"15%\"><b>" . _("DozentInnen:") . " </b></td><td>";
	else
		$dump.= "<tr><td width=\"15%\"><b>" . _("DozentIn:") . " </b></td><td>";
	while ($db->next_record()) 
		$dump.= $db->f("fullname") ."<br>  ";
	$dump.="</td></tr>\n";
		
	//und wer ist Tutor?
	$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = 'tutor' ORDER BY Nachname");

	if ($db->affected_rows() > 1)
		$dump.="<tr><td width=\"15%\"><b>" . _("TutorInnen:") . " </b></td><td>";
	elseif ($db->affected_rows() == 1)
		$dump.="<tr><td width=\"15%\"><b>" . _("TutorIn:") . " </b></td><td>";
	while ($db->next_record()) 
		$dump.= $db->f("fullname")."<br>";
	if ($db->affected_rows())
		$dump.="</td></tr>\n";
		
	if ($db2->f("status")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Typ der Veranstaltung:") . "&nbsp;</b></td><td align=left>";
		$dump.= $SEM_TYPE[$db2->f("status")]["name"]." " . _("in der Kategorie") . " <b>".$SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["name"]."</b></td></tr>\n";
		}
	if ($db2->f("art")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Art der Veranstaltung:") . "&nbsp;</b></td><td align=left>";
		$dump .= htmlReady($db2->f("art"),1,1)."</td></tr>\n";
		}
	if ($db2->f("VeranstaltungsNummer"))
		{
		$dump .="<tr><td width=\"15%\">";
		$dump .="<b>" . _("Veranstaltungsnummer:") . "&nbsp;</b></td><td width=75% align=left>";
		$dump.= $db2->f("VeranstaltungsNummer")."</td></tr>\n";
		}
	if ($db2->f("ects")!="")
		{
		$dump .="<tr><td width=\"15%\">";
		$dump .="<b>" . _("ECTS-Punkte:") . "&nbsp;</b></td><td width=75% align=left>";
		$dump.= $db2->f("ects")."</td></tr>\n";
		}
	if ($db2->f("Beschreibung")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Beschreibung:") . "&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("Beschreibung"),1,1)."</td></tr>\n";
		}
	if ($db2->f("teilnehmer")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("TeilnehmerInnen:") . "&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("teilnehmer"),1,1)."</td></tr>\n";
		}
	if ($db2->f("vorrausetzungen")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Voraussetzungen:") . "&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("vorrausetzungen"),1,1)."</td></tr>\n";
		}
	if ($db2->f("lernorga")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Lernorganisation:") . "&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("lernorga"),1,1)."</td></tr>\n";
		}
	if ($db2->f("leistungsnachweis")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Leistungsnachweis:") . "&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("leistungsnachweis"),1,1)."</td></tr>\n";
		}
	
	//add the free adminstrable datafields
	$localFields = $DataFields->getLocalFields();
	
	foreach ($localFields as $val) {
		if ($val["content"]) {
			$dump.="<tr><td width=\"15%\"><b>" . htmlReady($val["name"]) . ":&nbsp;</b></td><td align=left>";
			$dump.= htmlReady($val["content"],1,1)."</td></tr>\n";
		}
	}
	
	if ($db2->f("Sonstiges")!="")
		{
		$dump.="<tr><td width=\"15%\"><b>" . _("Sonstiges:") . "&nbsp;</b></td><td align=left>";
		$dump.= htmlReady($db2->f("Sonstiges"),1,1)."</td></tr>\n";
		}

	// Fakultaeten...
	$db3->query("SELECT DISTINCT c.Name FROM seminar_inst a LEFT JOIN  Institute b USING(Institut_id) LEFT JOIN Institute c ON(c.Institut_id=b.fakultaets_id)  WHERE a.seminar_id = '$sem_id'");
	IF ($db3->affected_rows() > 0)
		{
		$dump.= "<tr><td width=\"15%\"><b>" . _("Fakult&auml;t(en):") . "&nbsp;</b></td><td>";
		WHILE ($db3->next_record())
			$dump.= htmlReady($db3->f("Name"))."<br>";
		$dump.= "</td></tr>\n";
		}	
	
	//Studienbereiche 
	if ($SEM_CLASS[$SEM_TYPE[$db2->f("status")]["class"]]["bereiche"]) {
		$sem_path = get_sem_tree_path($sem_id);
		$dump .= "<tr><td width=\"15%\"><b>" . _("Studienbereich(e):") . "&nbsp;</b></td><td>";
		if (is_array($sem_path)){
			foreach ($sem_path as $sem_tree_id => $path_name) {
				$dump.= htmlReady($path_name)."<br>";
			}
		}
		$dump.= "</td></tr>\n";
	}

			
		
	$iid=$db2->f("Institut_id");
	$db3->query("SELECT Name, url FROM Institute WHERE Institut_id = '$iid'");
	$db3->next_record();
	$dump.="<tr><td width=\"15%\"><b>" . _("Heimat-Einrichtung:") . "&nbsp;</b></td><td>".$db3->f("Name")."</td></tr>\n";
	$db3->query("SELECT Name, url FROM seminar_inst LEFT JOIN Institute USING (institut_id) WHERE seminar_id = '$sem_id' AND Institute.institut_id != '$iid'");
	$cd=$db3->affected_rows();
	if ($db3->affected_rows() == 1)
		$dump.="<tr><td width=\"15%\"><b>" . _("beteiligte Einrichtung:") . "&nbsp;</b></td><td>";
	else if ($db3->affected_rows() >= 2)
		$dump.="<tr><td width=\"15%\"><b>" . _("beteiligte Einrichtungen:") . "&nbsp;</b></td><td>";
	
	while ($db3->next_record()) {
		$cd--;
		$dump.= htmlReady($db3->f("Name"));
		if ($cd >= 1) $dump.=",&nbsp;";
	}
	if ($db3->affected_rows())
		$dump.="</td></tr>\n";

	//Teilnehmeranzahl
	$dump.= "<tr><td width=\"15%\"><b>" . _("max. TeilnehmerInnenanzahl:") . "&nbsp;</b></td><td>".$db2->f("admission_turnout")."&nbsp;</td></tr>\n";

	//Statistikfunktionen

	$db3->query("SELECT count(*) as anzahl FROM seminar_user WHERE Seminar_id = '$sem_id'");
	$db3->next_record();
	$dump.= "<tr><td width=\"15%\"><b>" . _("Anzahl der angemeldeten TeilnehmerInnen:") . "&nbsp;</b></td><td>".$db3->f("anzahl")."</td></tr>\n";

	$db3->query("SELECT count(*) as anzahl FROM px_topics WHERE Seminar_id = '$sem_id'");
	$db3->next_record();
	$dump.= "<tr><td width=\"15%\"><b>" . _("Postings:") . "&nbsp;</b></td><td>".$db3->f("anzahl")."</td></tr>\n";

	$db3->query("SELECT count(*) as anzahl FROM dokumente WHERE Seminar_id='$sem_id'");
	$db3->next_record();
	$docs=$db3->f("anzahl");
	$dump.= "<tr><td width=\"15%\"><b>" . _("Dokumente:") . "&nbsp;</b></td><td>".$docs."</td></tr>\n";

	$dump.= "</table>\n";

	// Ablaufplan
	if ($Modules["schedule"]) {
		$db->query("SELECT *  FROM termine WHERE (range_id='$sem_id' AND date_typ ='1') ORDER BY date");
		if ($db->num_rows()) {
			$dump.="<br>";	  
			$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td colspan=2 align=left class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;" . _("Ablaufplan") . "</H2>";
			$dump.= "</td></tr>\n";
		
			while ($db->next_record()) {
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
		if ($db->num_rows()) {
			$dump.="<br>";	  
			$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td colspan=2 align=left class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;" . _("zus&auml;tzliche Termine") . "</H2>";
			$dump.= "</td></tr>\n";

			while ($db->next_record()) {
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
	}
	
	//SCM
	if ($Modules["scm"]) {	
		$db->query("SELECT * FROM scm WHERE range_id='$sem_id'");
		if ($db->num_rows()) {
		  	$db->next_record();
		  	$content = $db->f("content");
		  	$content = FixLinks(format($content)); // /newline fixen
		  	$tab_name = $db->f("tab_name");
		  	$tab_name = htmlReady($tab_name); // /newline fixen
		     
		  	if(!empty($content)) {
		 		$dump.="<br>";	  
		  		$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
				$dump .= " <tr><td align=left class=\"topic\">";
				$dump .= "<H2 class=\"topic\">&nbsp;" . $tab_name . "</H2>";
				$dump.= "</td></tr>\n";
				$dump.="<tr><td align=\"left\" width=\"100%\"><br>". $content ."<br></td></tr>\n";
				$dump .= "</table>\n";
			}
		}
	}
	
	if ($Modules['literature']){
		$lit = StudipLitList::GetFormattedListsByRange($sem_id, false);
		if ($lit){
			$dump.="<br>";	  
		  	$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td align=left class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;" . _("Literaturlisten") . "</H2>";
			$dump.= "</td></tr>\n";
			$dump.="<tr><td align=\"left\" width=\"100%\"><br>". format($lit) ."<br></td></tr>\n";
			$dump .= "</table>\n";
		}
	}
	
	// Dateien anzeigen
	if ($Modules["documents"]) {
		$i=0;
	
		//Auslesen aller allgemeine Dokumente zum Seminar
		$doc_ids=doc_challenge($sem_id);
		if (count($doc_ids))
			foreach ($doc_ids as $a) {
				$db->query ("SELECT dokument_id, dokumente.description, filename, dokumente.mkdate, filesize, dokumente.user_id, username, Nachname, dokumente.url  FROM dokumente LEFT JOIN auth_user_md5 ON auth_user_md5.user_id = dokumente.user_id WHERE dokument_id = '$a'");
				$db->next_record();
				if ($db->f("url")!="")
					$linktxt = _("Hinweis: Diese Datei wurde nicht archiviert, da sie lediglich verlinkt wurde.<br>");
				else
					$linktxt = "";	
				$dbresult[$i]=array("mkdate"=>$db->f("mkdate"), "dokument_id"=>$db->f("dokument_id"), "description"=>$linktxt.$db->f("description"), "filename"=>$db->f("filename"), "filesize"=>$db->f("filesize"),"user_id"=> $db->f("user_id"), "username"=>$db->f("username"), "nachname"=>$db->f("Nachname"));
				$i++;
			}
			
		//Auslesen der Dokumente zu Terminen
		$db->query ("SELECT termin_id FROM termine WHERE range_id ='$sem_id'");
		while ($db->next_record()) {
			$doc_ids=doc_challenge($db->f("termin_id"));
			if (count($doc_ids))
				foreach ($doc_ids as $a) {
					$db2->query ("SELECT dokument_id, dokumente.description, filename, dokumente.mkdate, filesize, dokumente.user_id, username, Nachname, dokumente.url  FROM dokumente LEFT JOIN auth_user_md5 ON auth_user_md5.user_id = dokumente.user_id WHERE dokument_id = '$a'");
					$db2->next_record();
					if ($db2->f("url")!="")
						$linktxt = _("Hinweis: Diese Datei wurde nicht archiviert, da sie lediglich verlinkt wurde.<br>");
					else
						$linktxt = "";	
					$dbresult[$i]=array("mkdate"=>$db2->f("mkdate"), "dokument_id"=>$db2->f("dokument_id"), "description"=>$linktxt.$db2->f("description"), "filename"=>$db2->f("filename"), "filesize"=>$db2->f("filesize"),"user_id"=> $db2->f("user_id"), "username"=>$db2->f("username"), "nachname"=>$db2->f("Nachname"));	
					$i++;
				}
		}
		
		if (!sizeof($dbresult)==0) {
			$dump.="<br>";	  
  			$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
			$dump .= " <tr><td align=left colspan=3 class=\"topic\">";
			$dump .= "<H2 class=\"topic\">&nbsp;" . _("Dateien:") . "</H2>";
			$dump.= "</td></tr>\n";
	
			rsort ($dbresult);
			
			for ($i=0; $i<sizeof($dbresult); $i++) {
				$doc_id = $dbresult[$i]["dokument_id"];
				$sizetmp = $dbresult[$i]["filesize"];
				$sizetmp = ROUND($sizetmp / 1024);
				$size = "(".$sizetmp." KB)";
	
     				$dump.="<tr><td width='100%'><b>".htmlReady($dbresult[$i]["filename"])."</b><br>".htmlReady($dbresult[$i]["description"])."&nbsp;".$size."</td><td>".
					$dbresult[$i]["nachname"] . "&nbsp;</td><td>&nbsp;".date("d.m.Y", $dbresult[$i]["mkdate"])."</td></tr>\n";
			}
			$dump.="</table>\n";
		}
	}


	// Teilnehmer
	if ($Modules["participants"]) {
		if (is_array($AUTO_INSERT_SEM) && !in_array($sem_id, $AUTO_INSERT_SEM)) {
			$gruppe = array ("dozent" => _("DozentInnen"),
				"tutor" => _("TutorInnen"),
				"autor" => _("AutorInnen"),
				"user" => _("LeserInnen"));
			$dump.="<br>";	  
			while (list ($key, $val) = each ($gruppe)) {	  
	
			// die eigentliche Teil-Tabelle
	
				$sortby = "doll DESC";
				$db=new DB_Seminar;
				$db2=new DB_Seminar;
				$db->query ("SELECT seminar_user.user_id, " . $_fullname_sql['full'] . " AS fullname, username, status, count(topic_id) AS doll FROM seminar_user LEFT JOIN px_topics USING (user_id,Seminar_id) LEFT JOIN auth_user_md5 ON (seminar_user.user_id=auth_user_md5.user_id) LEFT JOIN user_info USING (user_id) WHERE seminar_user.Seminar_id = '$sem_id' AND status = '$key'  GROUP by seminar_user.user_id ORDER BY $sortby");
	
				if (!$db->affected_rows() == 0) {//haben wir in der Personengattung ueberhaupt einen Eintrag?
		  			$dump.="<table width=100% border=1 cellpadding=2 cellspacing=0>";
					$dump .= " <tr><td align=left colspan=4 class=\"topic\">";
					$dump .= "<H2 class=\"topic\">&nbsp;".$val."</H2>";
					$dump.= "</td></tr>\n";
					$dump.="<th width=\"30%%\">" . _("Name") . "</th>";
					$dump.="<th width=\"10%%\">" . _("Postings") . "</th>";
					$dump.="<th width=10%><b>" . _("Dokumente") . "</b></th></tr>\n";
				
					while ($db->next_record()) {
						$dump.="<tr><td>";
						$dump.= $db->f("fullname");
						$dump.="</td><td align=center>";
						$dump.= $db->f("doll");
						$dump.="</td><td align=center>";
	
						$Dokumente = 0;
						$UID = $db->f("user_id");
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
		}
	}

 	return $dump;
	
} // end function dump_sem($sem_id)


/////// die beiden Funktionen um das Forum zu exportieren

function Export_Kids ($topic_id=0, $level=0) {
// stellt im Treeview alle Postings dar, die NICHT Thema sind

	if (!isset($anfang))
		$anfang = $topic_id;
	$query = "select topic_id, name, author "
		.", mkdate, chdate, description, root_id, username from px_topics LEFT JOIN auth_user_md5 USING(user_id) where "
		." parent_id = '$topic_id'"
		." order by mkdate";
	$db=new DB_Seminar;
	$db->query($query);
	$lines[$level] = $db->num_rows();
	while ($db->next_record()) {
		$r_topic_id = $db->f("topic_id");
		$r_name = $db->f("name");
		$r_author = $db->f("author");
		$r_mkdate = $db->f("mkdate");
		$r_chdate = $db->f("chdate");
		$r_description = $db->f("description");
		$root_id = $db->f("root_id");
		$username = $db->f("username");		

		if ($r_topic_id != $topic_id) {
			$r_name = htmlReady($r_name);
			$zusatz = htmlReady($r_author)." " . _("am") . " ";
			$zusatz .= date("d.m.Y - H:i", $r_mkdate);
			$r_description = FixLinks(format(htmlReady($r_description, $trim, FALSE)));
			if (ereg("\[quote",$r_description) AND ereg("\[/quote\]",$r_description) AND !$write)  {
      				$r_description = quotes_decode($r_description);
			}
			$forum_dumbkid.="<tr><td class=blank><hr><b>".$r_name."</b> " . _("von") . " ".$zusatz."</td></tr><tr><td class=blank>".$r_description."</td></tr>\n";	
		}
		$forum_dumbkid.=Export_Kids($r_topic_id, $level+1);
	}
	return $forum_dumbkid;
}
	
function Export_Topic ($sem_id) {
	global $SessionSeminar,$SessSemName;

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
		$text = _("Das Forum ist leer");
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
			$zusatz = "<b>".$count."</b> / ".date("d.m.Y - H:i", $last);
			$zusatz = htmlReady($author)."&nbsp;/&nbsp; ".$zusatz;
			$name = htmlReady($name);
			$description = FixLinks(format(htmlReady($description, $trim, FALSE)));
		        IF(ereg("\[quote\]",$description) AND ereg("\[/quote\]",$description) AND !$write)  $description = quotes_decode($description);
			$forum_dumb.="<table class=blank width=\"100%\" border=0 cellpadding=5 cellspacing=0><tr><td><h3>".$name."</h3> " . _("von") . " ".$zusatz."</td></tr><tr><td class=blank>".$description. "</td></tr>";
			$forum_dumb.=Export_Kids($r_topic_id, $level);
			$forum_dumb.="</table><br><br>";
			$neuer_beitrag = FALSE;
		}
	}
	return $forum_dumb;
}



//Funktion zum archivieren eines Seminars, sollte in der Regel vor dem Loeschen ausgfuehrt werden.
function in_archiv ($sem_id) {

	global $SEM_CLASS,$SEM_TYPE,$ABSOLUTE_PATH_STUDIP, $UPLOAD_PATH, $ARCHIV_PATH, $TMP_PATH, $ZIP_PATH, $_fullname_sql;
	
	$hash_secret="frauen";

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$semester = new SemesterData;


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
	
	$all_semester = $semester->getAllSemesterData();
	for ($i=0; $i<sizeof($all_semester); $i++)
		{
		if (($start_time >= $all_semester[$i]["beginn"]) && ($start_time <= $all_semester[$i]["ende"])) $semester_tmp=$all_semester[$i]["name"];
		}
	
	//Studienbereiche 
	if ($SEM_CLASS[$SEM_TYPE[$db->f("status")]["class"]]["bereiche"]) {
		$sem_path = get_sem_tree_path($seminar_id);
		if (is_array($sem_path)){
			$studienbereiche = join(", ",array_values($sem_path));
		}
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

	$db2->query("SELECT fakultaets_id FROM seminare LEFT JOIN Institute USING (Institut_id) WHERE seminare.Seminar_id = '$seminar_id'");
	$db2->next_record();
	$fakultaet_id=$db2->f("fakultaets_id");

	$db2->query("SELECT DISTINCT c.Name FROM seminar_inst a LEFT JOIN  Institute b USING(Institut_id) LEFT JOIN Institute c ON(c.Institut_id=b.fakultaets_id)  WHERE a.seminar_id = '$seminar_id'");
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
	$studienbereiche = addslashes($studienbereiche);		
	$dozenten = addslashes($dozenten);		
	$fakultaet = addslashes($fakultaet);	

	setTempLanguage();  // use $DEFAULT_LANGUAGE for archiv-dumps
	include ("$ABSOLUTE_PATH_STUDIP/config.inc.php");

	//Dump holen

	$dump = addslashes(dump_sem($sem_id));

	//Forumdump holen

	$forumdump = addslashes(export_topic($sem_id));
	
	restoreLanguage();
	include ("$ABSOLUTE_PATH_STUDIP/config.inc.php");

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

	$query = sprintf ("SELECT dokument_id FROM dokumente WHERE seminar_id = '%s' AND url = ''", $seminar_id);
	$db->query ($query);
	if ($db->affected_rows()) {
		$archiv_file_id=md5(uniqid($hash_secret));
		$docs=0;	
		
		//temporaeres Verzeichnis anlegen
		exec ("mkdir $TMP_PATH/$archiv_file_id");
		$tmp_full_path="$TMP_PATH/$archiv_file_id";
		
		$query = sprintf ("SELECT termin_id FROM termine WHERE range_id = '%s'", $seminar_id);
		$db->query ($query);
		$list = "('".$seminar_id."'";
		while ($db->next_record()) {
			$list .= ", '".$db->f("termin_id")."' ";
		}
		$list.= ")";
		
		//copy documents in the temporary folder-system
		$query = sprintf ("SELECT folder_id, name FROM folder WHERE range_id IN %s ORDER BY name", $list);
		$db->query ($query);
		$folder = 0;
		while ($db->next_record()) {
			$folder++;
			exec ("mkdir '$tmp_full_path/[$folder] ".prepareFilename($db->f("name"), FALSE)."' ");
			createTempFolder ($db->f("folder_id"), $tmp_full_path."/[$folder] ".prepareFilename($db->f("name"), FALSE), FALSE);
		}
		
		//zip all the stuff
	 	exec ("cd $tmp_full_path && ".$ZIP_PATH." -9 -r ".$ARCHIV_PATH."/".$archiv_file_id." * ");
	 	exec ("mv ".$ARCHIV_PATH."/".$archiv_file_id.".zip ".$ARCHIV_PATH."/".$archiv_file_id);
		exec ("rm -r $tmp_full_path");	 	
	} else
		$archiv_file_id="";
	
	//Reinschreiben von diversem Klumpatsch in die Datenbank
	$db->query("INSERT INTO archiv (seminar_id,name,untertitel,beschreibung,start_time,semester,heimat_inst_id,
				institute,dozenten,fakultaet,dump,archiv_file_id,mkdate,forumdump,studienbereiche) VALUES 
				('$seminar_id', '$name', '$untertitel', '$beschreibung', '$start_time', '$semester_tmp', '$heimat_inst_id', 
				'$institute', '$dozenten', '$fakultaet', '$dump', '$archiv_file_id', '".time()."','$forumdump',
				'$studienbereiche')");
}


?>
