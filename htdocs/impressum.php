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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	
require_once("$ABSOLUTE_PATH_STUDIP/config.inc.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");
require_once "$ABSOLUTE_PATH_STUDIP/reiter.inc.php";

function write_toplist($rubrik,$query) {
	global $PHP_SELF;
	
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

//Create Reitersystem
$reiter=new reiter;

//Topkats
$structure["kontakt"]=array (topKat=>"", name=>"Kontakt", link=>"impressum.php?view=main", active=>FALSE);
$structure["programm"]=array (topKat=>"", name=>"&Uuml;ber Stud.IP", link=>"impressum.php?view=technik", active=>FALSE);
//Bottomkats
$structure["main"]=array (topKat=>"kontakt", name=>"Entwickler", link=>"impressum.php?view=main", active=>FALSE);
$structure["ansprechpartner"]=array (topKat=>"kontakt", name=>"Ansprechpartner", link=>"impressum.php?view=ansprechpartner", active=>FALSE);
$structure["technik"]=array (topKat=>"programm", name=>"Technik", link=>"impressum.php?view=technik", active=>FALSE);
$structure["statistik"]=array (topKat=>"programm", name=>"Statistik", link=>"impressum.php?view=statistik", active=>FALSE);
$structure["history"]=array (topKat=>"programm", name=>"History", link=>"impressum.php?view=history", active=>FALSE);

if (!$view)
	$view="main";

$reiter->create($structure, $view);

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>


<? IF ($view=="main") {?>
	
	<tr>
		<td valign="top" class="blank">
			<blockquote><br />
			Stud.IP ist ein Open Source Projekt zur Unterst&uuml;tzung von Pr&auml;senzlehre an der Universit&auml;t G&ouml;ttingen.<br>
			Das System wird entwickelt vom Zentrum f&uuml;r interdisziplin&auml;re Medienwissenschaft (ZiM),
			Universit&auml;t G&ouml;ttingen und der Suchi &amp; Berg GmbH (data-quest), G&ouml;ttingen.<br>
			Stud.IP steht unter der GNU General Public License, Version 2 oder neuer.<br /><br />
			Weitere Informationen finden sie auf <a target="_new" href="http://www.studip.de">www.studip.de</a><br />
		</td>
		<td class="blank" align="left" valign="top">
			<a target="_new" href="http://www.studip.de"><img src="pictures/studipanim.gif" border="0"></a>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="blank">
			&nbsp; 
 		</td>
		<td class="blank" align="left" valign="top">
			&nbsp; &nbsp;<b>Version: </b><? echo $SOFTWARE_VERSION?>		
		</td>
	</tr>
	
	<tr>
		<td class="steel1"colspan=2>
			<br>&nbsp; &nbsp; <b>Die folgenden Entwickler</b> sind mit der st&auml;ndigen Pflege und Weiterentwicklung des Systems befasst:<br>
			<blockquote>
			<b>Marco Bohnsack</b>, eMail: <a href="mailto:mbohnsa@stud.uni-goettingen.de">mbohnsa@stud.uni-goettingen.de</a> (Hilfe)
			<br><b>Oliver Brakel</b>, eMail: <a href="mailto:obrakel@gwdg.de">obrakel@gwdg.de</a> (Distribution)
			<br><b>Cornelis Kater</b>, eMail: <a href="mailto:kater@data-quest.de">kater@data-quest.de</a> (Kernentwicklung, Terminverwaltung, Adminbereich, Design)
			<br><b>André Noack</b>, eMail: <a href="mailto:noack@data-quest.de">noack@data-quest.de</a> (Kernentwicklung, Newsverwaltung, Chat)
			<br><b>Arne Schröder</b>, eMail: <a href="mailto:23arne@web.de">23arne@web.de</a> (Externe Seiten)
			<br><b>Ralf Stockmann</b>, eMail: <a href="mailto:rstockm@gwdg.de">rstockm@gwdg.de</a> (Kernentwicklung, Forensystem, pers&ouml;nliche Seiten, Design)
			<br><b>Stefan Suchi</b>, eMail: <a href="mailto:suchi@data-quest.de">suchi@data-quest.de</a> (Kernentwicklung, Datenbankstruktur, Rechtesystem, Adminbereich)
			<br><b>Peter Thienel</b>, eMail: <a href="mailto:thienel@data-quest.de">thienel@data-quest.de</a> (Externe Seiten, Terminplaner)
			<br></blockquote>
		</td>
	</tr>
	</table>
	<table width="100%" border=0 cellpadding=0 cellspacing=0>
	<tr>
		<td class="blank" colspan=3>
			<blockquote>
				 <br>&nbsp;<font size=-1>gef&ouml;rdert von </font><br>
			</blockquote>
		</td>
	</tr>
	<tr>
		<td align="left" class="blank" width="15%">
			<blockquote>
				<a target="_new" href="http://www.bmbf.de/"><img src="pictures/bmbf.gif" border="0" /></a>
			</blockquote>
		</td>
		<td align="left" class="blank" width="15%">
			<blockquote>
				<a target="_new" href="http://www.campussource.de/"><img src="pictures/cslogotransparent.jpg" border="0" /></a>
			</blockquote>
		</td>
		<td align="center" class="blank" width="70%">
			&nbsp; 
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
			write_toplist("die meisten Teilnehmer","SELECT seminare.seminar_id, seminare.name, count(seminare.seminar_id) as count FROM seminar_user LEFT JOIN seminare USING(seminar_id) GROUP BY seminare.seminar_id ORDER BY count DESC LIMIT $count");
			write_toplist("zuletzt angelegt","SELECT seminare.seminar_id, seminare.name, mkdate as count FROM seminare ORDER BY mkdate DESC LIMIT $count");
			write_toplist("die meisten Materialien (Dokumente)","SELECT a.seminar_id, b.name, count(a.seminar_id) as count FROM dokumente a LEFT JOIN seminare b USING(seminar_id) WHERE NOT ISNULL(b.seminar_id) GROUP BY a.seminar_id  ORDER BY count DESC LIMIT $count");
			$week = time()-1209600;
			write_toplist("die aktivsten Veranstaltungen (Postings der letzten zwei Wochen)","SELECT a.seminar_id, b.name, count(a.seminar_id) as count FROM px_topics a LEFT JOIN seminare b USING(seminar_id) WHERE NOT ISNULL(b.seminar_id) AND a.mkdate > $week GROUP BY a.seminar_id  ORDER BY count DESC LIMIT $count");
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
	
			$db->query("SELECT count(*) from seminare");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">Aktive Veranstaltungen:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();
			$db->query("SELECT count(*) from archiv");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\" nowrap>Archivierte Veranstaltungen:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();	
			$db->query("SELECT count(*) FROM Institute");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">beteiligte Einrichtungen:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();
			$db->query("SELECT count(*) FROM Fakultaeten");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">beteiligte Fakult&auml;ten:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 

			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='admin'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Administratoren:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();	
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='dozent'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Dozenten:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='tutor'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Tutoren:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='autor'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">registrierte Studierende:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 
	
			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";
	
			$cssSw->switchClass();
			$db->query("SELECT count(*) from px_topics");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\" >Postings:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	
	
			$cssSw->switchClass();
			$db->query("SELECT count(*) from dokumente");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">Dateien:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	

			$cssSw->switchClass();
			$db->query("SELECT count(*) from literatur");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\" >Literaturlisten:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	
	
			$cssSw->switchClass();
			$db->query("SELECT count(*) from termine");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">Termine:</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>"; 	
	
			$cssSw->switchClass();
			$db->query("SELECT count(*) from news");
			$db->next_record();
			$anzahl = $db->f(0);
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
		<td class="blank" valign="top" colspan=2>
		<blockquote><b>F&uuml;r diese Stud.IP-Installation sind folgende Administratoren zust&auml;ndig:</b><br><br />
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
