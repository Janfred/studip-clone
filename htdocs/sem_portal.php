<?
/**
* sem_portal.php
* 
* the body for the serach engine
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	views
* @module		sem_portal.php
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sem_portal.php
// Rahmenseite der Suchfunktion
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once "$ABSOLUTE_PATH_STUDIP/functions.php";   //hier wird der "Kopf" nachgeladen
require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php"; 		//wir brauchen die Seminar-Typen
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php"; 		//wir brauchen die Seminar-Typen

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

$db=new DB_Seminar;

echo "\n".cssClassSwitcher::GetHoverJSFunction()."\n";

        
//Einstellungen fuer Reitersystem
$sess->register("sem_portal");

//got a fresh session?
if ((sizeof ($_REQUEST) == 1) && (!$view)) {
	$sem_portal='';
	$reset_all=TRUE;
}

//Standard herstellen
if (!$sem_portal["bereich"])
	$sem_portal["bereich"] = "all";

if ($view)
	$sem_portal["bereich"] = $view;

if ($choose_toplist)
	$sem_portal["toplist"] = $choose_toplist;
	
//function to display toplists
function getToplist($rubrik, $query, $type="count") {
	global $PHP_SELF;
	$result .= "<table cellpadding=\"0\" cellspacing=\"2\" border=\"0\">";
	$db=new DB_Seminar;
	$db->query($query);
	if  ($db->affected_rows() > 0) {
		$result .= "<tr><td colspan=\"2\"><font size=\"-1\"><b>$rubrik</b></font></td></tr>";
		$i=1;
		while ($db->next_record() ){
			$result .= "<tr><td width=\"1%\" valign=\"top\"><font size=\"-1\">$i.</font></td>";
			$result .= "<td width=\"99%\"><font size=\"-1\"><a href=\"details.php?sem_id=".$db->f("seminar_id")."&send_from_search=true&send_from_search_page=$PHP_SELF\">";
			$result .= htmlReady(substr($db->f("name"),0,45));
			if (strlen ($db->f("name")) > 45)
				$result .= "... ";
			$result .= "</a>";
			if ($type == "date" AND $db->f("count") >0) {
				$last =  date("YmdHis",$db->f("count"));
				$count = substr($last,6,2).".".substr($last,4,2).".". substr($last,0,4);
			}
			else 
				$count = $db->f("count");
			if ($count>0) 
				$result .= "&nbsp; (".$count.")";
			$result .= "</font></td></tr>";
			$i++;
		}
		$result .= "</tr>";
	}
	$result .= "</table>";
	return $result;
} 

?>
<body>
<?	

$view = $sem_portal["bereich"];
if (!$view) 
	$view="all";
	
if ($sem_portal["bereich"] != "all") {
	$sem_browse_data["s_sem"] ="all";
	if (!$show_class)
		$show_class = $view;

	foreach ($SEM_CLASS as $key => $value){
		if ($key == $show_class){
			foreach($SEM_TYPE as $type_key => $type_value){
				if($type_value['class'] == $key)
				$_sem_status[] = $type_key;
			}
		}
	}

	$query = "SELECT count(*) AS count FROM seminare WHERE seminare.status IN ('" . join("','", $_sem_status) . "')";
	$db->query($query);
	if ($db->next_record())
		$anzahl_seminare_class = $db->f("count");
}

	
if (!$perm->have_perm("root"))
	include ("$ABSOLUTE_PATH_STUDIP/links_seminare.inc.php");   	//hier wird die Navigation nachgeladen

?>
<table width="100%" border=0 cellpadding=0 cellspacing=0>
<tr>
	<td class="topic" colspan="2">
		<img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;<?=_("Anmeldung zu Veranstaltungen und Veranstaltungssuche")?></b>
	</td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
<tr>	
	<td class="blank" valign="top">
	<table cellpadding="5" border="0"><tr><td>
		<?
		if ($sem_portal["bereich"] == "all")
			print _("Sie k&ouml;nnen hier nach allen Veranstaltungen suchen, sich Informationen anzeigen lassen und Veranstaltungen abonnieren.<br /><br />");
		
		elseif ($anzahl_seminare_class > 0)
			print $SEM_CLASS[$sem_portal["bereich"]]["description"]."<br /><br />" ;

		 elseif ($sem_portal["bereich"] != "all") 
			print "<br>"._("In dieser Kategorie sind keine Veranstaltungen angelegt.<br>Bitte w&auml;hlen Sie einen andere Kategorie!");

		print "<font size=\"-1\">"._("Um eine Veranstaltung zu abonnieren, klicken Sie auf den Namen der Veranstaltung.")."</font><br />";

		if (($anzahl_seminare_class <= 20) && ($sem_portal["bereich"] != "all")) {
			print "<a href=\"$PHP_SELF?cmd=show_class\"><font size=\"-1\">";
			print _("Alle Veranstaltungen in dieser Kategorie anzeigen");
			print "</font></a><br />";
		}
		?>
	<br />
	</tr></td></table>
<?

//include the search engine

if ($view=="all") {
	$show_class=FALSE;
} else
	 $show_class=$view;

if ($SEM_CLASS[$view]["show_browse"] == FALSE AND $view!="all") {
	$hide_bereich=TRUE;
} else
	 $hide_bereich=FALSE;

$target_url="details.php";	//teilt der nachfolgenden Include mit, wo sie die Leute hinschicken soll
$target_id="sem_id"; 		//teilt der nachfolgenden Include mit, wie die id die &uuml;bergeben wird, bezeichnet werden soll

include "sem_browse.inc.php"; 		//der zentrale Seminarbrowser wird hier eingef&uuml;gt.

if (!count($_marked_sem)) {
	print "</td><td class=\"blank\" width=\"270\" align=\"right\" valign=\"top\">";

	//create TOP-lists
	if (!$mehr) {
		$count=5; // wieviel zeigen wir von den Listen?
		$mehr = 1;
	}
	else 
		$count = 5 * $mehr;
	
	if ($view !="all")
		$sql_where_query_seminare = "WHERE seminare.status IN ('" . join("','", $_sem_status) . "')";
	
	
	switch ($sem_portal["toplist"]) {
		case FALSE:
			$toplist =	getToplist(_("neueste Veranstaltungen"),"SELECT seminare.seminar_id, seminare.name, mkdate as count FROM seminare ".$sql_where_query_seminare." ORDER BY mkdate DESC LIMIT $count", "date");
		break;
		case 1:
			$toplist = getToplist(_("Teilnehmeranzahl"), "SELECT seminare.seminar_id, seminare.name, count(seminare.seminar_id) as count FROM seminar_user LEFT JOIN seminare USING(seminar_id) ".$sql_where_query_seminare." GROUP BY seminare.seminar_id ORDER BY count DESC LIMIT $count");
		break;
		case 2:
			$tmp_where = ($view != "all") ? $sql_where_query_seminare." AND NOT ISNULL(seminare.seminar_id) " : " WHERE NOT ISNULL(seminare.seminar_id) ";
			$toplist =	getToplist(_("die meisten Materialien"),"SELECT dokumente.seminar_id, seminare.name, count(dokumente.seminar_id) as count FROM dokumente LEFT JOIN seminare USING(seminar_id) ".$tmp_where." GROUP BY dokumente.seminar_id  ORDER BY count DESC LIMIT $count");
		break;
		case 3:
			$tmp_where = ($view != "all") ? $sql_where_query_seminare." AND NOT ISNULL(seminare.seminar_id) AND px_topics.mkdate > ".(time()-1209600) : " WHERE NOT ISNULL(seminare.seminar_id) AND px_topics.mkdate > ".(time()-1209600);
			$toplist =	getToplist(_("aktivste Veranstaltungen"),"SELECT px_topics.seminar_id, seminare.name, count(px_topics.seminar_id) as count FROM px_topics LEFT JOIN seminare USING(seminar_id) ".$tmp_where." GROUP BY px_topics.seminar_id  ORDER BY count DESC LIMIT $count");
		break;
	}
	
	//toplist link switcher
	if ($sem_portal["toplist"])
		$toplist_links .= "<a href=\"$PHP_SELF?choose_toplist=0\"><img src=\"pictures/forumrot.gif\" border=\"0\">&nbsp;"._("neueste Veranstaltungen")."</a><br />";
	if ($sem_portal["toplist"] != 1)
		$toplist_links .= "<a href=\"$PHP_SELF?choose_toplist=1\"><img src=\"pictures/forumrot.gif\" border=\"0\">&nbsp;"._("Teilnehmeranzahl")."</a><br />";
	if ($sem_portal["toplist"] != 2)
		$toplist_links .= "<a href=\"$PHP_SELF?choose_toplist=2\"><img src=\"pictures/forumrot.gif\" border=\"0\">&nbsp;"._("die meisten Materialien")."</a><br />";
	if ($sem_portal["toplist"] != 3)
		$toplist_links .= "<a href=\"$PHP_SELF?choose_toplist=3\"><img src=\"pictures/forumrot.gif\" border=\"0\">&nbsp;"._("aktivste Veranstaltungen")."</a><br />";
	
	$infobox = array (
		 ($view !="all") ? 
		 	array  ("kategorie"  => _("Information:"),
				"eintrag" => array	(	
					array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => sprintf (_("Gew&auml;hlte Kategorie: <b>%s</b>")."<br />"._("%s Veranstaltungen vorhanden"), $SEM_CLASS[$sem_portal["bereich"]]["name"], $anzahl_seminare_class)
					)
				)
			) : FALSE,
	
		array  ("kategorie" => _("Topliste:"),
			"eintrag" => array	(	
				array	 (	"icon" => "pictures/blank.gif",
									"text"  =>	$toplist
				)
			)
		),
		array  ("kategorie" => _("weitere Toplisten:"),
			"eintrag" => array	(	
				array	 (	"icon" => "pictures/blank.gif",
									"text"  =>	$toplist_links
				)
			)
		)
	);
	print_infobox ($infobox,"pictures/browse.jpg");
} else
	print "</td><td class=\"blank\" width=\"1\" align=\"right\" valign=\"top\">";
?>

	</td>
</tr>
<tr>
	<td class="blank" colspan="2">&nbsp;
	</td>
</tr>
</table>
<? page_close() ?>
</body>
</html>
