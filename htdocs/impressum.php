<?php
/*
impressum.php - Impressum von Stud.IP.
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@uni-goettingen.de>

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

?>

<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
        <link rel="stylesheet" href="style.css" type="text/css">
 </head>
<body bgcolor="#ffffff">


<?php
        include "seminar_open.php"; //hier werden die sessions initialisiert
        include "header.php";   //hier wird der "Kopf" nachgeladen
	
	require_once "config.inc.php";
	require_once("visual.inc.php");

function write_toplist($rubrik,$query)
{
	$db=new DB_Seminar;
	$db->query($query);
	$tmp_link="$PHP_SELF?view=statistik";
	IF  ($db->affected_rows() > 0) {
		echo "<tr><td class=links1>&nbsp; $rubrik</td></tr><tr><td class=steel1><ol type='1' start='1'>";
		while ($db->next_record() ){
			echo"<font size=2><li><a href='details.php?sem_id=".$db->f("seminar_id")."&send_from_search=true&send_from_search_page=$tmp_link'>";
			echo "".htmlReady($db->f("name"))."</a>";
			IF ($rubrik=="zuletzt angelegt" AND $db->f("count") >0) {
				$last =  date("YmdHis",$db->f("count"));
				$count = substr($last,6,2).".".substr($last,4,2).".". substr($last,0,4);
				}
			ELSE $count = $db->f("count");
			IF ($count>0) echo "&nbsp; (".$count.")";
			echo "</li></font>";
			}
		echo "</ol><br></td></tr>";
		}
} 

?>
<body>

<table cellpadding="0" cellspacing="0" border="0">
	<tr>
<?	
	IF (!$view) $view="main";
	
	if ($view == "main") {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="impressum.php?view=main"><font color="#000000" size=2><b>&nbsp; &nbsp; Entwickler&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="impressum.php?view=main"><font color="#000000" size=2><b>&nbsp; &nbsp; Entwickler&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}

	if ($view == "ansprechpartner") {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="impressum.php?view=ansprechpartner"><font color="#000000" size=2><b>&nbsp; &nbsp; Ansprechpartner&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="impressum.php?view=ansprechpartner"><font color="#000000" size=2><b>&nbsp; &nbsp; Ansprechpartner&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}

	if ($view == "statistik") {?>  <td class=links1b align=right nowrap><a  class="links1b" href="impressum.php?view=statistik"><font color="#000000" size=2><b>&nbsp; &nbsp; Statistik&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="impressum.php?view=statistik"><font color="#000000" size=2><b>&nbsp; &nbsp; Statistik&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}

	if ($view == "history") {?>  <td class="links1b" align=right nowrap><a  class="links1b" href="impressum.php?view=history"><font color="#000000" size=2><b>&nbsp; &nbsp; History&nbsp; &nbsp; </b></font></a><img src="pictures/reiter2.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="impressum.php?view=history"><font color="#000000" size=2><b>&nbsp; &nbsp; History&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}
 
	if ($view == "technik") {?>  <td class=links1b align=right nowrap><a  class="links1b" href="impressum.php?view=technik"><font color="#000000" size=2><b>&nbsp; &nbsp; Technik&nbsp; &nbsp; </b></font></a><img src="pictures/reiter4.jpg" align=absmiddle></td><?}
	ELSE {?>  <td class="links1" align=right nowrap><a  class="links1" href="impressum.php?view=technik"><font color="#000000" size=2><b>&nbsp; &nbsp; Technik&nbsp; &nbsp; </b></font></a><img src="pictures/reiter1.jpg" align=absmiddle></td><?}



	echo "</tr></table>\n<table cellspacing=0 cellpadding=4 border=0 width=100%><tr><td class=\"steel1\">&nbsp; &nbsp; ";
	echo"<br></td></tr><tr><td class=\"reiterunten\">&nbsp; </td></tr></table>\n";
?>

<table width="100%" border=0 cellpadding=0 cellspacing=0>


<? IF ($view=="main") {?>
	
	<tr>
		<td valign="top" class="blank">
			<blockquote><br />
			Stud.IP ist ein Open Source Projekt zur Unterst&uuml;tzung von Pr&auml;senzlehre an der Universit&auml;t G&ouml;ttingen. <br>
			Das System wird federf&uuml;hrend entwickelt vom Zentrum f&uuml;r interdisziplin&auml;re Medienwissenschaft (ZiM) G&ouml;ttingen.<br>
			Stud.IP steht unter der GNU General Public License, Version 2 oder neuer.<br /><br />
			Weitere Informationen finden sie auf <a target="_new" href="http://www.studip.de">www.studip.de</a><br />
		</td>
		<td class="blank" align="right">
			<a target="_new" href="http://www.studip.de"><img src="pictures/studipanim.gif" border="0"></a><br /><b>Version: </b><? echo $SOFTWARE_VERSION?>
			&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
			<font size=-1>gef&ouml;rdert von </font>&nbsp; &nbsp; &nbsp; &nbsp; <br />
			<a target="_new" href="http://www.bmbf.de/"><img src="pictures/bmbf.gif" border="0" /></a>&nbsp; &nbsp; &nbsp; &nbsp; 
			
		</td>
	<tr>
		<td class="steel1"colspan=2>
			<br>&nbsp; &nbsp; <b>Die folgenden Entwickler</b> sind mit der st&auml;ndigen Pflege und Weiterentwicklung des Systems befasst:<br><br><blockquote>
			<b>Marco Bohnsack</b>, eMail: <a href="mailto:mbohnsa@stud.uni-goettingen.de">mbohnsa@stud.uni-goettingen.de</a> (Hilfe)
			<br><b>Oliver Brakel</b>, eMail: <a href="mailto:obrakel@gwdg.de">obrakel@gwdg.de</a> (Distribution)
			<br><b>Cornelis Kater</b>, eMail: <a href="mailto:ckater@gwdg.de">ckater@gwdg.de</a> (Kernentwicklung, Terminverwaltung, Adminbereich, Design)
			<br><b>André Noack</b>, eMail: <a href="mailto:andre.noack@gmx.net">andre.noack@gmx.net</a> (Kernentwicklung, Newsverwaltung, Chat)
			<br><b>Arne Schröder</b>, eMail: <a href="mailto:23arne@web.de">23arne@web.de</a> (Externe Seiten)
			<br><b>Ralf Stockmann</b>, eMail: <a href="mailto:rstockm@gwdg.de">rstockm@gwdg.de</a> (Kernentwicklung, Forensystem, pers&ouml;nliche Seiten, Design)
			<br><b>Stefan Suchi</b>, eMail: <a href="mailto:suchi@data-quest.de">suchi@data-quest.de</a> (Kernentwicklung, Datenbankstruktur, Rechtesystem, Adminbereich)
			<br><b>Peter Thienel</b>, eMail: <a href="mailto:rabeiri@gmx.de">rabeiri@gmx.de</a> (Externe Seiten, Terminplaner)
			<br></blockquote>
		</td>
	</tr>
	<?}
	
IF ($view=="statistik") {?>
	
	<tr>
		<td width="70%"  valign="top" class="blank">
		<blockquote>
			<b>Top-Listen aller Veranstaltungen</b><br /><br />
			<table  cellpadding=0 cellspacing=0 class=blank>	
			<?
			//Toplists
			$count = 10;
			write_toplist("die meisten Teilnehmer","SELECT seminare.seminar_id, seminare.name, count(seminare.seminar_id) as count FROM seminar_user LEFT JOIN seminare USING(seminar_id) ".$sql_where_query_seminare." GROUP BY seminare.seminar_id ORDER BY count DESC LIMIT $count");
			write_toplist("zuletzt angelegt","SELECT seminare.seminar_id, seminare.name, mkdate as count FROM seminare ".$sql_where_query_seminare." ORDER BY mkdate DESC LIMIT $count");
			write_toplist("die meisten Materialien (Dokumente)","SELECT seminare.seminar_id, seminare.name, count(seminare.seminar_id) as count FROM dokumente LEFT JOIN seminare USING(seminar_id) ".$sql_where_query_seminare." GROUP BY seminar_id  ORDER BY count DESC LIMIT $count");
			$week = time()-1209600;
			IF ($view!="Alle") $tmp = ereg_replace("WHERE","AND",$sql_where_query_seminare);
				write_toplist("die aktivsten Seminare (Postings der letzten zwei Wochen)","SELECT seminare.seminar_id, seminare.name, count(*) as count FROM px_topics LEFT JOIN seminare USING(seminar_id) WHERE px_topics.mkdate > $week ".$tmp." GROUP BY seminar_id  ORDER BY count DESC LIMIT $count");
			?>	
			</table>
		</blockquote>
		</td>
		<td width="30%" valign="top" class="blank">
			<table  align=middle cellpadding=2 cellspacing=0 border=0 >
			<b>Statistik</b><br /><br />
			<?
			//Statistics
			$db=new DB_Seminar;
			$cssSw=new cssClassSwitcher;
	
			$db->query("SELECT * from seminare");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">Aktive Veranstaltungen:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();
			$db->query("SELECT * from archiv");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" nowrap>Archivierte Veranstaltungen:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">beteiligte Institute:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * FROM Fakultaeten");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">beteiligte Fakult&auml;ten:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from auth_user_md5 WHERE perms='admin'");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Administratoren:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();	
			$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from auth_user_md5 WHERE perms='dozent'");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Dozenten:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from auth_user_md5 WHERE perms='tutor'");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Tutoren:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from auth_user_md5 WHERE perms='autor'");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Studierende:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from px_topics");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" >Postings:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from dokumente");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">Dateien:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	

			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from literatur");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" >Literaturlisten:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from termine");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">Termine:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	
	
			$cssSw->switchClass();	$db->query("SELECT * FROM Institute");
			$db->query("SELECT * from news");
			$anzahl = $db->num_rows();
			echo "<tr><td class=\"".$cssSw->getClass() ."\">News:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr></blockquote></table></td></tr>"; 	
	}
	
IF ($view=="history") {?>
	
	<tr>
		<td valign="center" class="blank">
		<blockquote>
		<b>Stud.IP history.txt</b><br /><br />		
		<? 
		
		$history = file("history.txt");
		WHILE ($i <sizeof($history)){
			echo $history[$i]."<br>";
			$i++;
			}
		?>
		</td>
	</tr>
	<?}
	
IF ($view=="ansprechpartner") {?>	
	
	
	<tr>
		<td class="blank" valign="top" colspan=2><br><br>
		<blockquote><b>F&uuml;r diese Stud.IP-Installation sind folgende Administratoren zust&auml;ndig:</b><br>
		<blockquote><blockquote>
<?
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	
	$db->query("SELECT * FROM auth_user_md5 WHERE perms='root' ORDER BY Nachname");
	if ($db->affected_rows() ==0) { echo"keine. Na sowas. Das kann ja eigentlich gar nicht sein..."; }
	while ($db->next_record())
		{
		echo "<a href=\"about.php?username=".$db->f("username")."\">".$db->f("Vorname")." ".$db->f("Nachname")."</a>, eMail: <a href=\"mailto:".$db->f("Email")."\">".$db->f("Email")."</a><br>";
		}
	
?>
		<br>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		<blockquote></blockquote>
		</td>
	<tr>
		<td class="steel1" colspan=2>
		<blockquote><br><b>Folgende Einrichtungen sind beteiligt:</b><br><font size=-1>(Genannt werden die jeweiligen Administratoren der Einrichtungen f&uuml;r entsprechende Anfragen)</font>
		</td>
	</table><table width="100%" border=0 cellpadding=0 cellspacing=0>
	</tr>
	<tr>
		<td class="steel1" valign="top"width="55%">
		<blockquote>
		
<?

	$db->query("SELECT auth_user_md5.*, Institute.Institut_id, Institute.Name FROM auth_user_md5 LEFT OUTER JOIN user_inst USING (user_id) LEFT JOIN Institute ON (user_inst.institut_id = Institute.Institut_id) WHERE user_inst.inst_perms='admin' AND Institute.Name NOT LIKE '%- - -%' ORDER BY Institute.Name, Nachname");
	$count=$db->affected_rows()-1;
	$half=$db->affected_rows()/2;
	$change=0;
	while ($db->next_record())
		{
		if (($count<$half) && ($change==0) && ($last_inst<>$db->f("Institut_id")))
			{
			echo"<br><br></td><td class=\"steel1\" valign=\"top\" width=\"45%\">";
			$change=1;
			}
		if ($last_inst<>$db->f("Institut_id"))
			{
			$inst_id=$db->f("Institut_id");
			echo "<br><br><b><a href=\"institut_main.php?auswahl=".$db->f("Institut_id")."\">".htmlReady($db->f("Name"))."</a>:</b><br>";
			}
		$last_inst=$inst_id;
		echo "<a href=\"about.php?username=".$db->f("username")."\">".$db->f("Vorname")." ".$db->f("Nachname")."</a>, eMail: <a href=\"mailto:".$db->f("Email")."\">".$db->f("Email")."</a><br>";
		$count=$count-1;
		}


?>
		<br>&nbsp; <br></td>
	</tr>
<?	}

IF ($view == "technik") {

?>
	<tr>
		<td colspan=6 class="blank"><blockquote><blockquote>
<?

echo "Stud IP ist ein Open-Source Projekt und steht unter der GNU General Public License. Sämtliche zum Betrieb notwendigen Dateien können unter <a href='http://sourceforge.net/projects/studip/'>http://sourceforge.net/projects/studip/</a> heruntergeladen werden. ";
echo "<br><br>Die technische Grundlage bietet ein LINUX-System mit Apache Webserver sowie eine MySQL Datenbank, die über PHP gesteuert wird.";
echo "<br><br>Im System findet ein 6-stufiges Rechtesystem Verwendung, das individuell auf verschiedenen Ebenen wirkt - etwa in Veranstaltungen, Einrichtungen, Fakultäten oder systemweit.";
echo "<br><br>Seminare oder Arbeitsgruppen können mit Passwörtern geschützt werden - die Verschlüsselung erfolgt mit einem MD5 one-way-hash.";
echo "<br><br>Das System ist zu 100% über das Internet administrierbar, es sind keine zusätzlichen Werkzeuge nötig. Ein Webbrowser ab Version 4 wird empfohlen.";
echo "<br><br>Das System wird ständig weiterentwickelt und an die Wünsche unserer Nutzer angepasst - <a href='mailto:crew@studip.de'>sagen Sie uns Ihre Meinung!</a>";

?>

	</blockquote></blockquote><br><br></td></tr>
	<tr>
		<td align="center" class="blank" width="15%">
			<a href="http://www.suse.de" target="_new"><img src="./pictures/penguin.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://www.apache.org" target="_new"><img src="./pictures/apache.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://www.mysql.org" target="_new"><img src="./pictures/powered-by-mysql-transparent1.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://www.php.com" target="_new"><img src="./pictures/php4.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://phplib.netuse.de" target="_new"><img src="./pictures/phplib_sm.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://sourceforge.net" target="_new"> <img src="http://sourceforge.net/sflogo.php?group_id=16662" width="88" height="31" border="0" alt="SourceForge Logo"></a>
		</td>
	</tr>
	
<?}

?>
</table>

<?php

// Save data back to database.
page_close();
 ?>
<!-- $Id$ -->