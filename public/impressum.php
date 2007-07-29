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
// $Id$

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$auth->login_if($again && ($auth->auth["uid"] == "nobody"));

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
$CURRENT_PAGE= _("Impressum");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

require_once('config.inc.php');
require_once('lib/visual.inc.php');
require_once('lib/user_visible.inc.php'); // user visibility
require_once 'lib/include/reiter.inc.php';

function write_toplist($rubrik,$query) {
	global $PHP_SELF;

	$db=new DB_Seminar;
	$db->query($query);
	$tmp_link="$PHP_SELF?view=statistik";
	if  ($db->affected_rows() > 0) {
		echo "<tr><td class=links1>&nbsp; $rubrik</td></tr><tr><td class=steel1><ol type='1' start='1'>";
		while ($db->next_record() ) {
			echo"<li><font size=2><a href='details.php?sem_id=".$db->f("seminar_id")."&send_from_search=true&send_from_search_page=$tmp_link'>";
			echo "".htmlReady($db->f("name"))."</a>";
			if ($rubrik== _("zuletzt angelegt") AND $db->f("count") >0) {
				$count =  date("d.m.Y H:i:s",$db->f("count"));
			} else
				$count = $db->f("count");
			if ($count>0) echo "&nbsp; (".$count.")";
			echo "</font></li>";
		}
		echo "</ol><br></td></tr>\n";
	}
}

function write_toplist_person($rubrik,$query) {
	global $PHP_SELF;

	$db=new DB_Seminar;
	$db->query($query);
	$tmp_link="$PHP_SELF?view=statistik";
	if  ($db->affected_rows() > 0) {
		echo "<tr><td class=links1>&nbsp; $rubrik</td></tr><tr><td class=steel1><ol type='1' start='1'>";
		while ($db->next_record() ) {
			echo"<li><font size=2><a href='about.php?username=".$db->f("username")."'>";
			echo "".htmlReady($db->f("full_name"))."</a>";
			if ($rubrik== _("zuletzt angelegt") AND $db->f("count") >0) {
				$count =  date("d.m.Y H:i:s",$db->f("count"));
			} else
				$count = $db->f("count");
			if ($count>0) echo "&nbsp; (".$count.")";
			echo "</font></li>";
		}
		echo "</ol><br></td></tr>\n";
	}
}

//Create Reitersystem
$reiter=new reiter;

//Topkats
$structure["kontakt"]=array ("topKat"=>"", "name"=>_("Kontakt"), "link"=>"impressum.php?view=ansprechpartner", "active"=>FALSE);
$structure["programm"]=array ("topKat"=>"", "name"=>_("&Uuml;ber Stud.IP"), "link"=>"impressum.php?view=technik", "active"=>FALSE);
//Bottomkats
$structure["ansprechpartner"]=array ("topKat"=>"kontakt", "name"=>_("Ansprechpartner"), "link"=>"impressum.php?view=ansprechpartner", "active"=>FALSE);
$structure["main"]=array ("topKat"=>"kontakt", "name"=>_("Entwickler"), "link"=>"impressum.php?view=main", "active"=>FALSE);
$structure["technik"]=array ("topKat"=>"programm", "name"=>_("Technik"), "link"=>"impressum.php?view=technik", "active"=>FALSE);
$structure["statistik"]=array ("topKat"=>"programm", "name"=>_("Statistik"), "link"=>"impressum.php?view=statistik", "active"=>FALSE);
$structure["history"]=array ("topKat"=>"programm", "name"=>_("History"), "link"=>"impressum.php?view=history", "active"=>FALSE);
$structure['blog']=array ('topKat'=>'programm', 'name'=>_("Stud.IP-Blog"), 'link'=>'http://blog.studip.de/', 'active'=>FALSE, 'target'=>'_blank');

$mailsub = '['.$_SERVER['SERVER_NAME'].']';

if (!$view)
	$view="ansprechpartner";

$reiter->create($structure, $view);

?>
<table width="100%" border="0" cellpadding="0" cellspacing="0">


<?php
     if ($view=="main") {
	$developer[] = array('Marco Bohnsack', 'bohnsack@data-quest.de', _("(Projektmanagement, Hilfe)"));
	$developer[] = array('Torsten Heinrich', 'to.heinrich@fh-osnabrueck.de', _("(...)"));
	//$developer[] = array('Cornelius Hempel', 'cornelius.hempel@studip.uni-halle.de', _("(Fehlersuche)"));
	$developer[] = array('Cornelis Kater', 'info@ckater.de', _("(Ressourcenverwaltung, Terminverwaltung, Adminbereich, Design)"));
	$developer[] = array('Hartje Kriete', 'kriete@math.uni-goettingen.de', _("(&Uuml;bersetzung, Internationalisierung)"));
	$developer[] = array('Jan Kulmann', 'jankul@tzi.de', _("(Evaluationen)"));
	$developer[] = array('Marcus Lunzenauer', 'mlunzena@uos.de', _("(...)"));
	$developer[] = array('André Noack', 'noack@data-quest.de', _("(Chat, Literaturverwaltung, Authentifizierung, Verzeichnisse)"));
	$developer[] = array('Frank Ollermann', 'follerma@uni-osnabrueck.de', _("(Usability)"));
	$developer[] = array('Dennis Reil', 'studip@reil-online.de', _("(PlugIn-Schnittstelle)"));
	$developer[] = array('Jens Schmelzer', 'jens.schmelzer@fh-jena.de', _("(Security, Distribution)"));
	$developer[] = array('Ralf Stockmann', 'rstockm@gwdg.de', _("(Forensystem, Adressbuch, Design)"));
	$developer[] = array('Stefan Suchi', 'suchi@data-quest.de', _("(Projektmanagement, Datenbankstruktur, Rechtesystem, Internationalisierung)"));
	$developer[] = array('Tobias Thelen', 'tthelen@uni-osnabrueck.de', _("(WikiWeb, Hilfe)"));
	$developer[] = array('Peter Thienel', 'thienel@data-quest.de', _("(Externe Seiten, Terminkalender)"));
	$developer[] = array('Nils Kolja Windisch', 'info@nkwindisch.de', _("(Systeminterne Nachrichten)"));

?>

	<tr>
		<td valign="top" class="blank">
			<blockquote><br />
<?

printf(_("Stud.IP ist ein Open Source Projekt zur Unterst&uuml;tzung von Pr&auml;senzlehre an Universit&auml;ten, Hochschulen und anderen Bildungseinrichtungen. Das System entstand am %sZentrum f&uuml;r interdisziplin&auml;re Medienwissenschaft (ZiM)%s der %sGeorg-August-Universit&auml;t G&ouml;ttingen%s unter Mitwirkung der %sSuchi & Berg GmbH (data-quest)%s , G&ouml;ttingen. Heute erfolgt die Weiterentwicklung von Stud.IP verteilt an vielen Standorten (G&ouml;ttingen, Osnabr&uuml;ck, Oldenburg, Bremen, Hannover, Jena und weiteren). Die Koordination der Entwicklung erfolgt durch die %sStud.IP-CoreGroup.%s "), '<a href="http://zim.uni-goettingen.de/" target="_blank">', '</a>', '<a href="http://www.uni-goettingen.de/" target="_blank">', '</a>', '<a href="http://www.data-quest.de/" target="_blank">', '</a>', '<a href="http://blog.studip.de/entwickler/" target="_blank">', '</a>');

echo "\n<br />" . _("Stud.IP steht unter der GNU General Public License, Version 2.") . "\n<br />&nbsp;<br />\n";

printf(_("Weitere Informationen finden sie auf %s, %s und %s."), '<a target="_blank" href="http://www.studip.de" style="font-weight:bold;">www.studip.de</a>', '<a target="_blank" href="http://develop.studip.de" style="font-weight:bold;">develop.studip.de</a>', '<a target="_blank" href="http://blog.studip.de" style="font-weight:bold;">blog.studip.de</a>') . "<br />\n";

?>
		</td>
		<td class="blank" align="left" valign="top">
			<a target="_new" href="http://www.studip.de"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/studipanim.gif" border="0"></a>
		</td>
	</tr>
	<tr>
		<td valign="top" align="right" class="blank">
			&nbsp;
 		</td>
		<td class="blank" align="left" valign="top">
			&nbsp; &nbsp;<b><?=_("Version:")?> </b><? echo $SOFTWARE_VERSION?>
		</td>
	</tr>

	<tr>
		<td class="steel1" colspan="2">
			<br>&nbsp; &nbsp; <?=_("<b>Die folgenden Entwickler</b> sind mit der st&auml;ndigen Pflege und Weiterentwicklung des Systems befasst:")?><br>
			<blockquote><span style="font-size:smaller;">
<?php
			foreach($developer as $dev){
				echo '<b>'.$dev[0].'</b>, ';
				echo 'E-Mail: <a href="mailto:'.$dev[1].'?subject='.$mailsub.'">'.$dev[1].'</a> ';
				echo $dev[2].'<br />'."\n";
			}
?>
			</span></blockquote><br>
			&nbsp; &nbsp; <?=_("Sie erreichen uns auch &uuml;ber folgende <b>Mailinglisten:")?></b><br>
			<blockquote>
			<span style="font-size:smaller;"><b><?=_("Nutzer-Anfragen")?></b>, E-Mail: <a href="mailto:studip-users@lists.sourceforge.net?subject=<?=$mailsub ?>">studip-users@lists.sourceforge.net</a>: <?=_("Fragen, Anregungen und Vorschl&auml;ge an die Entwickler - bitte <u>keine</u> Passwort Anfragen!")?><br />
			<b><?=_("News-Mailingsliste")?></b>, E-Mail: <a target="new" href="http://lists.sourceforge.net/mailman/listinfo/studip-news">studip-news@lists.sourceforge.net</a>: <?=_("News rund um Stud.IP (Eintragung notwendig)")?></span><br />
			<br>
			<? printf(_("Wir laden alle Entwickler, Betreiber und Nutzer von Stud.IP ein, sich auf dem Developer-Server %s an den Diskussionen rund um die Weiterentwicklung und Nutzung der Plattform zu beteiligen."), '<a href="http://develop.studip.de" target="_blank">http://develop.studip.de</a>')?>
			</blockquote>
		</td>
	</tr>
	</table>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="blank" colspan="4">
			 <br />&nbsp; &nbsp; <font size=-1><b><?=_("Entwicklung und Support:")?> </b></font><br />&nbsp;
		</td>
		<td class="blank" colspan="3">
			 <br /><font size=-1><b><?=_("gef&ouml;rdert von:")?> </b></font><br />&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" width="4%">&nbsp;
		</td>
		<td align="left" class="blank" width="15%" align="center">
			<a target="_new" href="http://zim.uni-goettingen.de/"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/zim.gif" border="0" /></a>
		</td>
		<td align="left" class="blank" width="15%" align="center">
			<a target="_new" href="http://www.data-quest.de/"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/dataquest.gif" border="0" /></a>
		</td>
		<td align="left" class="blank" width="15%" align="center">
			&nbsp;
		</td>
		<td align="left" class="blank" width="15%" align="center">
			<a target="_new" href="http://www.bmbf.de/"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/bmbf.gif" border="0" /></a>
		</td>
		<td align="left" class="blank" width="15%" align="center">
			<a target="_new" href="http://www.campussource.de/"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/cslogotransparent.jpg" border="0" /></a>
		</td>
		<td align="center" class="blank" width="25%">
			&nbsp;
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="7">
			<br />
		</td>
	</tr>
	<?}

if ($view=="statistik") {?>

	<tr>
		<td width="70%"  valign="top" class="blank">
		<blockquote>
			<b><?=_("Top-Listen aller Veranstaltungen")?></b><br /><br />
			<table  cellpadding=0 cellspacing=0 class=blank>
			<?
			//Toplists
			$count = 10;
			write_toplist(_("die meisten Teilnehmer"),"SELECT seminar_user.seminar_id, seminare.name, count(seminar_user.seminar_id) as count FROM seminar_user INNER JOIN seminare USING(seminar_id) WHERE seminare.visible=1 GROUP BY seminar_user.seminar_id ORDER BY count DESC LIMIT $count");
			write_toplist(_("zuletzt angelegt"),"SELECT seminare.seminar_id, seminare.name, mkdate as count FROM seminare WHERE visible = 1 ORDER BY mkdate DESC LIMIT $count");
			write_toplist(_("die meisten Materialien (Dokumente)"),"SELECT a.seminar_id, b.name, count(a.seminar_id) as count FROM seminare b  INNER JOIN dokumente a USING(seminar_id) WHERE b.visible=1 GROUP BY a.seminar_id  ORDER BY count DESC LIMIT $count");
			$week = time()-1209600;
			write_toplist(_("die aktivsten Veranstaltungen (Postings der letzten zwei Wochen)"),"SELECT a.seminar_id, b.name, count(a.seminar_id) as count FROM px_topics a INNER JOIN seminare b USING(seminar_id) WHERE b.visible=1 AND a.mkdate > $week GROUP BY a.seminar_id  ORDER BY count DESC LIMIT $count");
			write_toplist_person(_("die beliebtesten Homepages (Besucher)"),"SELECT auth_user_md5.user_id, username, views as count, " . $_fullname_sql['full'] . " AS full_name FROM object_views LEFT JOIN auth_user_md5 ON(object_id=auth_user_md5.user_id) LEFT JOIN user_info USING (user_id) WHERE auth_user_md5.user_id IS NOT NULL  ORDER BY count DESC LIMIT $count");
			?>
			</table>
		</blockquote>
		</td>
		<td width="30%" valign="top" class="blank">
			<table  align=middle cellpadding=2 cellspacing=0 border=0 >
			<b><?=_("Statistik")?></b><br /><br />
			<?
			//Statistics
			$db=new DB_Seminar;
			$cssSw=new cssClassSwitcher;

			$db->query("SELECT count(*) from seminare");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Aktive Veranstaltungen:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from archiv");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\" nowrap>" . _("Archivierte Veranstaltungen:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) FROM Institute WHERE Institut_id != fakultaets_id");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("beteiligte Einrichtungen:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) FROM Institute WHERE Institut_id = fakultaets_id");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("beteiligte Fakult&auml;ten:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='admin'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("registrierte Administratoren:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='dozent'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("registrierte Dozenten:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='tutor'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("registrierte Tutoren:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from auth_user_md5 WHERE perms='autor'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("registrierte Studierende:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			echo "<tr><td class=\"".$cssSw->getClass() ."\" colspan=2>&nbsp; </td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from px_topics");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\" >" . _("Postings:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from dokumente WHERE url = ''");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Dateien:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from dokumente WHERE url != ''");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("verlinkte Dateien:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from lit_list");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\" >" . _("Literaturlisten:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from termine");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Termine:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from news");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("News:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			$cssSw->switchClass();
			$db->query("SELECT count(*) from user_info WHERE guestbook='1'");
			$db->next_record();
			$anzahl = $db->f(0);
			echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Gästebücher:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

			if ($GLOBALS['VOTE_ENABLE']) {
				$cssSw->switchClass();
				$db->query("SELECT count(*) from vote WHERE type='vote'");
				$db->next_record();
				$anzahl = $db->f(0);
				echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Umfragen:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

				$cssSw->switchClass();
				$db->query("SELECT count(*) from vote WHERE type='test'");
				$db->next_record();
				$anzahl = $db->f(0);
				echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Tests:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";

				$cssSw->switchClass();
				$db->query("SELECT count(*) from eval");
				$db->next_record();
				$anzahl = $db->f(0);
				echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Evaluationen:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";
			}

			if ($GLOBALS['WIKI_ENABLE']) {
				$cssSw->switchClass();
				$db->query("SELECT COUNT(DISTINCT keyword) as count from wiki");
				$db->next_record();
				$anzahl = $db->f(0);
				echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("WikiWeb Seiten:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";
			}

			if ($GLOBALS['ILIAS_CONNECT_ENABLE']){
				$cssSw->switchClass();
				$db->query("SELECT COUNT(DISTINCT co_id) as count from seminar_lernmodul");
				$db->next_record();
				$anzahl = $db->f(0);
				echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("ILIAS-Lernmodule:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";
			}
			if ($RESOURCES_ENABLE) {
				$cssSw->switchClass();
				$db->query("SELECT COUNT(*) from resources_objects");
				$db->next_record();
				$anzahl = $db->f(0);
				echo "<tr><td class=\"".$cssSw->getClass() ."\">" . _("Ressourcen-Objekte:") . "</td><td class=\"".$cssSw->getClass() ."\" align=right>$anzahl</td></tr>";
			}

			echo "</blockquote></table></td></tr>";
	}
	
if ($view == 'history') {?>
	
	<tr>
		<td valign="middle" class="blank">
		<blockquote>
		<b>Stud.IP history.txt</b><br /><br />		
		<? 
		$history = file('history.txt');
		echo formatReady(implode('',$history));
		?>
		</blockquote>
		</td>
	</tr>
	<?}

if ($view=="ansprechpartner") {?>


	<tr>
		<td class="blank" valign="top">
		<blockquote><b>
<?
	printf ("<font size=\"-1\">"._("F&uuml;r diese Stud.IP-Installation (%s) sind folgende Administratoren zust&auml;ndig:") . "</font></b><br><br />", $UNI_NAME);

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

	$db->query("SELECT " . $_fullname_sql['full'] ." AS fullname, Email, username FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE perms='root' AND ".get_vis_query()." ORDER BY Nachname");
	if ($db->affected_rows() ==0) { echo _("keine. Na sowas. Das kann ja eigentlich gar nicht sein..."); }
	while ($db->next_record())
		{
		echo "<font size=\"-1\"><a href=\"about.php?username=".$db->f("username")."\">".htmlReady($db->f("fullname"))."</a>, E-Mail: <a href=\"mailto:".$db->f("Email")."\">".$db->f("Email")."</a></font ><br>";
		}

?>
		<br><font size="-1">
		<?=_("<b>allgemeine Anfragen</b> wie Passwort-Anforderungen u.a. richten Sie bitte an:")?><br>
		</font>
		<font size="-1"><a href="mailto:<?=$UNI_CONTACT?>"><?=$UNI_CONTACT?>	</a></font ><br /><br /></blockquote>
		</td>
		<td class="blank" align="center" valign="middle">
			<a target="_new" href="http://www.studip.de"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/studipanim.gif" border="0"></a>
			<div align="left"><br>&nbsp; &nbsp;<b><?=_("Version:")?> </b><? echo $SOFTWARE_VERSION?></div>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan=2>
		<blockquote></blockquote>
		</td>
	<tr>
		<td class="steel1" colspan=2>
		<blockquote><br><b><font size="-1"><?=_("Folgende Einrichtungen sind beteiligt:")?></font></b><br><font size=-1><?=_("(Genannt werden die jeweiligen Administratoren der Einrichtungen f&uuml;r entsprechende Anfragen)")?></font>
		</blockquote>
		</td>
	</table><table width="100%" border=0 cellpadding=0 cellspacing=0>
	</tr>
	<tr>
		<td class="steel1" valign="top" width="55%">
		<blockquote>

<?

	$db->query("SELECT " . $_fullname_sql['full'] ." AS fullname,auth_user_md5.Email,username, Institute.Institut_id, Institute.Name FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) LEFT JOIN Institute ON (user_inst.institut_id = Institute.Institut_id) WHERE user_inst.inst_perms='admin' AND Institute.Name NOT LIKE '%- - -%' AND ".get_vis_query()." ORDER BY Institute.Name, Nachname");
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
			echo "<br><br><b><font size=\"-1\"><a href=\"institut_main.php?auswahl=".$db->f("Institut_id")."\">".htmlReady($db->f("Name"))."</a>:</font></b><br>";
			}
		$last_inst=$inst_id;
		echo "<font size=\"-1\"><a href=\"about.php?username=".$db->f("username")."\">".htmlReady($db->f("fullname"))."</a>, E-Mail: <a href=\"mailto:".$db->f("Email")."\">".$db->f("Email")."</a></font><br>";
		$count=$count-1;
		}


?>
		<br>&nbsp; <br></td>
	</tr>
<?	}

if ($view == "technik") {

?>
	<tr>
		<td colspan=6 class="blank"><blockquote><blockquote>
<?

printf(_("Stud IP ist ein Open-Source Projekt und steht unter der GNU General Public License. Sämtliche zum Betrieb notwendigen Dateien können unter %shttp://sourceforge.net/projects/studip/%s heruntergeladen werden."), "<a href=\"http://sourceforge.net/projects/studip/\">", "</a>");
echo "<br><br>";
echo _("Die technische Grundlage bietet ein LINUX-System mit Apache Webserver sowie eine MySQL Datenbank, die über PHP gesteuert wird.");
echo "<br><br>";
echo _("Im System findet ein 6-stufiges Rechtesystem Verwendung, das individuell auf verschiedenen Ebenen wirkt - etwa in Veranstaltungen, Einrichtungen, Fakultäten oder systemweit.");
echo "<br><br>";
echo _("Seminare oder Arbeitsgruppen können mit Passwörtern geschützt werden - die Verschlüsselung erfolgt mit einem MD5 one-way-hash.");
echo "<br><br>";
echo _("Das System ist zu 100% über das Internet administrierbar, es sind keine zusätzlichen Werkzeuge nötig. Ein Webbrowser der 5. Generation wird empfohlen.");
echo "<br><br>";
printf(_("Das System wird ständig weiterentwickelt und an die Wünsche unserer Nutzer angepasst - %ssagen Sie uns Ihre Meinung!%s"), '<a href="mailto:studip-users@lists.sourceforge.net?subject='.$mailsub.'">', '</a>');

?>

	</blockquote></blockquote><br><br></td></tr>
	<tr>
		<td align="center" class="blank" width="15%">
			<a href="http://www.suse.de" target="_new"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/penguin.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://www.apache.org" target="_new"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/apache.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://www.mysql.org" target="_new"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/powered-by-mysql-transparent1.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://www.php.net" target="_new"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/php4.gif" border=0></a>
		</td>
		<td align="center" class="blank" width="15%">
			<a href="http://sourceforge.net/projects/phplib" target="_new"><img src="<?= $GLOBALS['ASSETS_URL'] ?>images/phplib_sm.gif" border=0></a>
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
