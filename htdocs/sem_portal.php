<?php
/*
sem_portal.php - Portal fuer Seminarfreischaltung von Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>

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


        page_open(array("sess" => "Seminar_Session", "auth" => "Seminar", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
				$perm->check("user");
?>
<html>
<head>
	<title>Stud.IP</title>
	<link rel="stylesheet" href="style.css" type="text/css">
	<META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
</head>


<?php
        include "seminar_open.php"; //hier werden die sessions initialisiert
?>

<!-- hier muessen Seiten-Initialisierungen passieren -->

<?php
        include "header.php";   //hier wird der "Kopf" nachgeladen
        require_once "functions.php";   //hier wird der "Kopf" nachgeladen
        require_once "config.inc.php"; 		//wir brauchen die Seminar-Typen
        require_once "visual.inc.php"; 		//wir brauchen die Seminar-Typen
        
//Einstellungen fuer Reitersystem
$sess->register("sem_portal");

//Standard herstellen
if (!$sem_portal["bereich"])
	{
	$sem_portal=array(
		"bereich"=>"Alle", 
		);
	}

if (isset($view)){
	$sem_portal=array(
		"bereich"=>$view, 
		);
	}
	
function write_toplist($rubrik,$query) {
	global $PHP_SELF;
	
	$db=new DB_Seminar;
	$db->query($query);
	IF  ($db->affected_rows() > 0) {
		echo "<tr><td class=links1>&nbsp; $rubrik</td></tr><tr><td class=steel1><ol type='1' start='1'>";
		while ($db->next_record() ){
			echo"<font size=2><li><a href='details.php?sem_id=".$db->f("seminar_id")."&send_from_search=true&send_from_search_page=$PHP_SELF'>";
			echo "".htmlReady($db->f("name"))."</a>";
			IF ($rubrik=="zuletzt angelegt" AND $db->f("count") >0) {
				$last =  date("YmdHis",$db->f("count"));
				$count = substr($last,6,2).".".substr($last,4,2).".". substr($last,0,4);
				}
			ELSE $count = $db->f("count");
			IF ($count>0) echo "&nbsp; (".$count.")";
			echo "</li></font>";
			}
		echo "</ol></td></tr>";
		}
} 



?>
<body>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
<?	
$view = $sem_portal["bereich"];
	IF (!$view) $view="Alle";
	
	if ($view == "Alle") {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="sem_portal.php?cmd=qs&view=Alle&reset_all=true"><font color="#000000" size=2><b>&nbsp; &nbsp; Alle&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="sem_portal.php?cmd=qs&view=Alle&reset_all=true"><font color="#000000" size=2><b>&nbsp; &nbsp; Alle&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}

	$counter=1;

foreach ($SEM_CLASS as $a) {
	if ($view == $counter) {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="sem_portal.php?cmd=xts&view=<?echo $counter;?>&reset_all=true"><font color="#000000" size=2><b>&nbsp; &nbsp; <?echo $a["name"];?>&nbsp; &nbsp; </b></font></a><? if ($counter == sizeof($SEM_CLASS)) echo "<img src=\"pictures/reiter4.jpg\" align=absmiddle></td>"; else echo "<img src=\"pictures/reiter2.jpg\" align=absmiddle></td>"; $beschreibung = $a["description"];}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="sem_portal.php?cmd=xts&view=<?echo $counter;?>&reset_all=true"><font color="#000000" size=2><b>&nbsp; &nbsp; <?echo $a["name"];?>&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}
	$counter++;
	}


	echo "</tr></table>\n<table cellspacing=0 cellpadding=4 border=0 width=100%><tr><td class=\"steel1\">&nbsp; &nbsp; ";
	echo"<br></td></tr><tr><td class=\"reiterunten\">&nbsp; </td></tr></table>\n";
//	echo "<table class=blank width=100% border=0 cellspacing=0 cellpadding=0><tr><td class=\"blank\">&nbsp; </td></tr></table>";

?>

<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan=2><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;Anmeldung zu Veranstaltungen</b></td>
</tr>
<tr>
<td class="blank" align = left width="90%"><blockquote>
<? 
IF ($sem_portal["bereich"]=="Alle") {
	echo"<br>Um an einer Veranstaltung teilnehmen zu k&ouml;nnen und damit die dort zur Verf&uuml;gung gestellten Materialien  zu nutzen, w&auml;hlen Sie die Veranstaltung &uuml;ber diese Suchfunktion aus.<p>Bitte klicken Sie zur Anmeldung auf den Namen der Veranstaltung.<br>Wenn Sie eine Veranstaltung aus einem bestimmten Bereich suchen, w&auml;hlen Sie oben den entsprechenden Reiter.";
	}
ELSE {

	$sem_browse_data["s_sem"] ="alle";
	$db=new DB_Seminar;
	if (!$show_class)
		$show_class = $view;
	$tmp_classes=''; $query="";
	$i=1;
	foreach ($SEM_TYPE as $a) {
		if ($a["class"] == $show_class)
			$tmp_classes[]=$i;
		$i++;
		}
	$query.="SELECT count(*) AS count FROM seminare WHERE seminare.status in (";
	$i=0;
	foreach ($tmp_classes as $a) {
		if ($i)
			$query.=", ";
		$query.="'$a'";
		$i++;
		}
	$query.=" )";
	$db->query($query);
	IF ($db->next_record() ){
		$Anzahl = $db->f("count");
		IF ($Anzahl > 0) {
			echo $beschreibung;
			echo "&nbsp; <br>(<a href='sem_portal.php?send=yes&s_sem=alle&sset=xts&s_bool=1&show_class=$view'>alle ".$db->f("count")."&nbsp;Veranstaltungen anzeigen</a>)";
			}
		ELSE {
			echo "<br>In diesem Bereich sind noch keine Veranstaltungen angelegt.<br>Bitte w&auml;hlen Sie mit den Reitern einen anderen Bereich!<br><br></td></tr></table>\n";
			die;
			}
		}

	}
?>
</blockquote></td>
<td class="blank" align = right><img src="pictures/board2.jpg" border="0"></td>
</tr>
<tr><td class="blank" colspan=2>&nbsp;</td></tr>

<tr><td class="blank" colspan=2>
<?

IF ($view=="Alle") $show_class=FALSE;
ELSE $show_class=$view;
IF ($SEM_CLASS[$view]["show_browse"]==FALSE AND $view!="Alle") {
	$hide_bereich=TRUE;
	}
ELSE $hide_bereich=FALSE;

	if ($perm->have_perm("admin")) {
		$target_url="seminar_main.php";	//teilt der nachfolgenden Include mit, wo sie die Leute hinschicken soll
		$target_id="auswahl"; 		//teilt der nachfolgenden Include mit, wie die id die &uuml;bergeben wird, bezeichnet werden soll
		}
	else {
		$target_url="details.php";	//teilt der nachfolgenden Include mit, wo sie die Leute hinschicken soll
		$target_id="sem_id"; 		//teilt der nachfolgenden Include mit, wie die id die &uuml;bergeben wird, bezeichnet werden soll
		}
	include "sem_browse.inc.php"; 		//der zentrale Seminarbrowser wird hier eingef&uuml;gt.
	
	$sql_where_query_seminare="";

echo "</td></tr><tr><td class=\"blank\" colspan=2>&nbsp; </td></tr>";
echo "</table><br>";

IF ($sem_browse_data["level"]!="s" AND $sem_browse_data["level"]!="sbb" AND !$level) { // Wir sind auf einer Uebersichtsseite, also her mit den TOP-Listen

   IF ($Anzahl > 0 OR $view=="Alle") { //Wenn was da ist TOP-Listren ausgeben
	echo "<table width=100% border=0 cellspacing=0 cellpadding=1><tr><td class=topic><b>TOP-Listen";
	IF ($view!="Alle") ECHO " im Bereich ".$SEM_CLASS[$view]["name"];
	IF ($mehr) echo "<a name='anker'>";
	?>
	</b></td></tr>
	<?

	//Erweierung des query um Klasseneingrenzung
	IF ($view!="Alle") {
		$show_class = $view;
		$tmp_classes='';
		$i=1;
		foreach ($SEM_TYPE as $a) {
			if ($a["class"] == $show_class)
				$tmp_classes[]=$i;
			$i++;
			}
		$sql_where_query_seminare.="WHERE seminare.status in (";
		$i=0;
		foreach ($tmp_classes as $a) {
			if ($i)
				$sql_where_query_seminare.=", ";
			$sql_where_query_seminare.="'$a'";
			$i++;
			}
		$sql_where_query_seminare.=" )";
		}

	IF (!$mehr) {
		$count=5; // wieviel zeigen wir von den Listen?
		$mehr = 1;
		}
	ELSE $count = 5 * $mehr;

	write_toplist("die meisten Teilnehmer","SELECT seminare.seminar_id, seminare.name, count(seminare.seminar_id) as count FROM seminar_user LEFT JOIN seminare USING(seminar_id) ".$sql_where_query_seminare." GROUP BY seminare.seminar_id ORDER BY count DESC LIMIT $count");
	write_toplist("zuletzt angelegt","SELECT seminare.seminar_id, seminare.name, mkdate as count FROM seminare ".$sql_where_query_seminare." ORDER BY mkdate DESC LIMIT $count");
	write_toplist("die meisten Materialien (Dokumente)","SELECT seminare.seminar_id, seminare.name, count(seminare.seminar_id) as count FROM dokumente LEFT JOIN seminare USING(seminar_id) ".$sql_where_query_seminare." GROUP BY seminar_id  ORDER BY count DESC LIMIT $count");
	$week = time()-1209600;
	IF ($view!="Alle") $tmp = ereg_replace("WHERE","AND",$sql_where_query_seminare);
	write_toplist("die aktivsten Seminare (Postings der letzten zwei Wochen)","SELECT seminare.seminar_id, seminare.name, count(*) as count FROM px_topics LEFT JOIN seminare USING(seminar_id) WHERE px_topics.mkdate > $week ".$tmp." GROUP BY seminar_id  ORDER BY count DESC LIMIT $count");
	}
	echo "<tr><td class=\"steelgraudunkel\" align=\"center\" ><a href=\"$PHP_SELF?view=".$view."&mehr=", $mehr+1, "#anker\"><font size=2 color='#333399'><img src='pictures/forumgraurunt.gif' alt='zeig mir mehr' border=0 align=middle></font></a><img src='pictures/forumleer.gif' height='23' border=0 valign='top' align='middle'>";
	IF ($mehr > 1) echo "<a href=\"$PHP_SELF?view=".$view."&mehr=", $mehr-1, "#anker\"><font size=2 color='#333399'><img src='pictures/forumgraurauf.gif' alt ='zeig mir weniger' border=0 align=middle></font></a>";
	echo "</td></tr></td></tr>";
	echo "</table>\n</td></tr>";
}
	
?>
</table></td></tr>
</table>
<?
     page_close()
 ?>
</body>
</html>