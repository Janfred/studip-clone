<?php
/*
meine_einrichtungen.php - Anzeige der besuchten/benutzten Einrichtungen
Copyright (C) 2002 	Stefan Suchi <suchi@gmx.de>,
				Ralf Stockmann <rstockm@gwdg.de>,
				Cornelis Kater <ckater@gwdg.de>
				Suchi & Berg GmbH <info@data-quest.de>

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


page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check("user");

ob_start(); //Outputbuffering für maximal Performance

function get_my_inst_values(&$my_inst) {
	global $user;
	
	$db2 = new DB_seminar();
	
	// Postings
	$db2->query("SELECT b.Seminar_id,count(topic_id) as count, count(if((chdate > b.loginfilenow AND user_id !='".$user->id."'),a.topic_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN px_topics a USING (Seminar_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		if ($my_inst[$db2->f("Seminar_id")]["modules"]["forum"]) {
			$my_inst[$db2->f("Seminar_id")]["neuepostings"]=$db2->f("neue");
			$my_inst[$db2->f("Seminar_id")]["postings"]=$db2->f("count");
		}
	}

	//dokumente
	$db2->query("SELECT b.Seminar_id,count(dokument_id) as count, count(if((chdate > b.loginfilenow AND user_id !='".$user->id."'),a.dokument_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN dokumente a USING (Seminar_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		if ($my_inst[$db2->f("Seminar_id")]["modules"]["documents"]) {
			$my_inst[$db2->f("Seminar_id")]["neuedokumente"]=$db2->f("neue");
			$my_inst[$db2->f("Seminar_id")]["dokumente"]=$db2->f("count");
		}
	}

	//News
	$db2->query("SELECT b.Seminar_id,count(IF(date < UNIX_TIMESTAMP(),range_id,NULL)) as count, count(IF((date < UNIX_TIMESTAMP() AND date > b.loginfilenow AND user_id !='".$user->id."'),range_id,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN news_range ON (b.Seminar_id=range_id) LEFT JOIN news  USING(news_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		$my_inst[$db2->f("Seminar_id")]["neuenews"]=$db2->f("neue");
		$my_inst[$db2->f("Seminar_id")]["news"]=$db2->f("count");
	}
	
	// Literatur?
	$db2->query("SELECT b.Seminar_id,if(literatur !='' OR links != '',1,0) AS literatur,
			if((chdate > b.loginfilenow AND user_id !='".$user->id."' AND (literatur !='' OR links != '')),1,0) AS neue 
			FROM loginfilenow_".$user->id." b  LEFT JOIN literatur ON (range_id = b.Seminar_id)");
	while($db2->next_record()) {
		if ($my_inst[$db2->f("Seminar_id")]["modules"]["literature"]) {			
			$my_inst[$db2->f("Seminar_id")]["neueliteratur"]=$db2->f("neue");
			$my_inst[$db2->f("Seminar_id")]["literatur"]=$db2->f("literatur");
		}
	}
	
	//Wiki-Eintraege?
	$db2->query("SELECT b.Seminar_id, COUNT(DISTINCT keyword) as count, count(IF((chdate > b.loginfilenow AND user_id !='".$user->id."'),a.keyword,NULL)) AS neue 
				FROM loginfilenow_".$user->id." b  LEFT JOIN wiki a ON (b.Seminar_id=range_id) GROUP BY b.Seminar_id");
	while($db2->next_record()) {
		if ($my_sem[$db2->f("Seminar_id")]["modules"]["wiki"]) {	
			$my_sem[$db2->f("Seminar_id")]["neuewikiseiten"]=$db2->f("neue");
			$my_sem[$db2->f("Seminar_id")]["wikiseiten"]=$db2->f("count");
		}
	}

	return;
}


function print_institut_content($instid,$my_inst_values) {
  // Postings
  if ($my_inst_values["neuepostings"])
		echo "<a href=\"institut_main.php?auswahl=$instid&redirect_to=forum.php&view=neue\">&nbsp; <img src='pictures/icon-posting2.gif' border=0 ".tooltip(sprintf(_("%s Postings, %s neue"),$my_inst_values["postings"], $my_inst_values["neuepostings"]))."></a>";
  elseif ($my_inst_values["postings"])
		echo "<a href=\"institut_main.php?auswahl=$instid&redirect_to=forum.php\">&nbsp; <img src='pictures/icon-posting.gif' border=0 ".tooltip(sprintf(_("%s Postings"), $my_inst_values["postings"]))."></a>";
  else
		echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
  //Dokumente
  if ($my_inst_values["neuedokumente"])
		echo "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=folder.php&cmd=all\"><img src='pictures/icon-disc2.gif' border=0 ".tooltip(sprintf(_("%s Dokumente, %s neue"), $my_inst_values["dokumente"], $my_inst_values["neuedokumente"]))."></a>";
  elseif ($my_inst_values["dokumente"])
		echo "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=folder.php&cmd=tree\"><img src='pictures/icon-disc.gif' border=0 ".tooltip(sprintf(_("%s Dokumente"), $my_inst_values["dokumente"]))."></a>";
  else
		echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //News
  if ($my_inst_values["neuenews"])
		echo "&nbsp; <a href=\"institut_main.php?auswahl=$instid\"><img src='pictures/icon-news2.gif' border=0 ".tooltip(sprintf(_("%s News, %s neue"), $my_inst_values["news"], $my_inst_values["neuenews"]))."></a>";
  elseif ($my_inst_values["news"])
		echo "&nbsp; <a href=\"institut_main.php?auswahl=$instid\"><img src='pictures/icon-news.gif' border=0 ".tooltip(sprintf(_("%s News"), $my_inst_values["news"]))."></a>";
  else
		echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";

  //Literatur
	if ($my_inst_values["literatur"]) {
		echo "<a href=\"institut_main.php?auswahl=$instid&redirect_to=literatur.php\">";
		if ($my_inst_values["neueliteratur"])
	  	echo "&nbsp; <img src=\"pictures/icon-lit2.gif\" border=0 ".tooltip(_("Zur Literatur- und Linkliste (geändert)"))."></a>";
		else
		  echo "&nbsp; <img src=\"pictures/icon-lit.gif\" border=0 ".tooltip(_("Zur Literatur- und Linkliste"))."></a>";
  } else {
		echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";
	}

  // Termine
  if ($my_inst_values["neuetermine"])
		echo "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=dates.php\"><img src='pictures/icon-uhr2.gif' border=0 ".tooltip(sprintf(_("%s Termine, %s neue"), $my_inst_values["termine"], $my_inst_values["neuetermine"]))."></a>";
  elseif ($my_inst_values["termine"])
		echo "&nbsp; <a href=\"institut_main.php?auswahl=$instid&redirect_to=dates.php\"><img src='pictures/icon-uhr.gif' border=0 ".tooltip(sprintf(_("%s Termine"), $my_inst_values["termine"]))."></a>";
  else
		echo "&nbsp; <img src='pictures/icon-leer.gif' border=0>";


  // Wikiseiten
  if ($my_sem_values["neuewikiseiten"])
		echo "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=wiki.php&view=listnew\"><img src='pictures/icon-wiki2.gif' border=0 ".tooltip(sprintf(_("%s WikiSeiten, %s Änderungen"), $my_sem_values["wikiseiten"], $my_sem_values["neuewikiseiten"]))."></a>";
  elseif ($my_sem_values["wikiseiten"])
		echo "&nbsp; <a href=\"seminar_main.php?auswahl=$semid&redirect_to=wiki.php\"><img src='pictures/icon-wiki.gif' border=0 ".tooltip(sprintf(_("%s WikiSeiten"), $my_sem_values["wikiseiten"]))."></a>";
  else
		echo "&nbsp; <img src='pictures/icon-leer.gif' width=\"20\" height=\"20\" border=\"0\">";

  echo "&nbsp;&nbsp;";
  

} // Ende function print_institut_content


include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once ($ABSOLUTE_PATH_STUDIP."config.inc.php");			// Klarnamen fuer den Veranstaltungsstatus
require_once ($ABSOLUTE_PATH_STUDIP."visual.inc.php");			// htmlReady fuer die Veranstaltungsnamen
require_once ($ABSOLUTE_PATH_STUDIP."dates.inc.php");			// Semester-Namen fuer Admins
require_once ($ABSOLUTE_PATH_STUDIP."/lib/classes/Modules.class.php");	// modul-config class

require_once $ABSOLUTE_PATH_STUDIP."messaging.inc.php";

if ($GLOBALS['CHAT_ENABLE']){
	include_once $ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_CHAT."/chat_func_inc.php"; 
	$chatServer =& ChatServer::GetInstance($GLOBALS['CHAT_SERVER_NAME']);
	$chatServer->caching = true;
	$sms = new messaging();
}
if ($GLOBALS['ILIAS_CONNECT_ENABLE']){
	include_once ($ABSOLUTE_PATH_STUDIP.$RELATIVE_PATH_LEARNINGMODULES."/lernmodul_db_functions.inc.php"); 
}
$cssSw=new cssClassSwitcher;                          					// Klasse für Zebra-Design
$cssSw->enableHover();
$Modules = new Modules;
$db=new DB_Seminar;
$db2=new DB_Seminar;

// we are defintely not in an lexture or institute
closeObject();
$links_admin_data =''; 	//Auch im Adminbereich gesetzte Veranstaltungen muessen geloescht werden.

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

echo "\n".cssClassSwitcher::GetHoverJSFunction()."\n";

include ("$ABSOLUTE_PATH_STUDIP/links_seminare.inc.php");   	//hier wird die Navigation nachgeladen

//bei Bedarf aus seminar_user austragen
if ($cmd=="kill") {
	$db->query("DELETE FROM user_inst WHERE user_id='$user->id' AND Institut_id='$auswahl'");
	if ($db->affected_rows() == 0)
		$meldung="error§" . _("Datenbankfehler!");
	else {
	  
	  $db->query("SELECT Name FROM Institute WHERE Institut_id = '$auswahl'");
	  $db->next_record();
	  $meldung="msg§" . sprintf(_("Die Zuordnung zur Einrichtung %s wurde aufgehoben."), "<b>".$db->f("Name")."</b>");
	}
}


//This view is only for users up to admin
if ( !$perm->have_perm("root")) {

	if (!isset($sortby))
		$sortby="Name";
	if ($sortby == "count")
		$sortby = "count DESC";
	$db->query ("SELECT b.Name, b.Institut_id,b.type, user_inst.inst_perms,if(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,modules FROM user_inst LEFT JOIN Institute b USING (Institut_id) WHERE user_inst.user_id = '$user->id' GROUP BY Institut_id ORDER BY $sortby");
	$num_my_inst=$db->num_rows();
	if (!$num_my_inst)
		if ($perm->have_perm("dozent"))
			$meldung="info§" . sprintf(_("Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen %sAdministratoren%s."), "<a href=\"impressum.php?view=ansprechpartner\">", "</a>") . "§".$meldung;
		else
			$meldung="info§" . sprintf(_("Sie haben sich noch keinen Einrichtungen zugeordnet. Um sich Einrichtungen zuzuordnen, nutzen Sie bitte die entsprechende %sOption%s unter \"universit&auml;re Daten\" auf Ihrer pers&ouml;nlichen Einstellungsseite."), "<a href=\"edit_about.php?view=Karriere#einrichtungen\">", "</a>") . "§".$meldung;
	?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td class="topic" colspan="3">
			<img src="pictures/meinesem.gif" border="0" align="texttop">&nbsp;<b><?=_("Meine Einrichtungen")?></b>
		</td>
	</tr>
	<?
	if ($num_my_inst) {
	 ?>
	 	<tr>
	 		<td valign="top" class="blank">
				<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
					<tr align="center">
						<td class="blank" colspan="3">&nbsp;
						</td>
					</tr>
					<tr align="center">
						<td valign="top" align="center">
							<table border="0" cellpadding="1" cellspacing="0" width="98%" align="center" class="blank">
								<? if ($meldung) {
									echo "<tr><td>&nbsp; </td></tr>";
									parse_msg($meldung);
								}
								?>
								<tr valign="top" align="center">
									<th width="1%">&nbsp; </th>
									<th width="86%" align="left"><a href="<?=$PHP_SELF?>?sortby=Name&view=<?=$view?>"><?=_("Name")?></a></th>
									<th width="10%"><b><?=_("Inhalt")?></b></th>
									<?
									if ($view=="ext") { 
									?>
										<th width="10%"><b>&nbsp;<?=_("besucht")?>&nbsp;</b></th>
										<th width="10%"><a href="<?=$PHP_SELF?>?sortby=inst_perms&view=<?=$view?>">&nbsp;<?=_("Status")?>&nbsp;</a></th>
									<? }?>
									<th width="3%"><b>&nbsp;X&nbsp;</b></th>
								</tr>
		<?
		ob_end_flush(); //Buffer leeren, damit der Header zu sehen ist
		ob_start();
		while ($db->next_record()) {
			$my_inst[$db->f("Institut_id")]=array(name=>$db->f("Name"),status=>$db->f("inst_perms"),type=>($db->f("type")) ? $db->f("type") : 1, modules =>$Modules->getLocalModules($db->f("Institut_id"), "inst",$db->f("modules")));
			$value_list.="('".$db->f("Institut_id")."',0".$loginfilenow[$db->f("Institut_id")]."),";
			if (($GLOBALS['CHAT_ENABLE']) && ($my_inst[$db->f("Institut_id")]["modules"]["chat"])) {
				$chatter = $chatServer->isActiveChat($db->f("Institut_id"));
				$chat_info[$db->f("Institut_id")] = array("chatter" => $chatter, "chatuniqid" => $chatServer->chatDetail[$db->f("Institut_id")]["id"],
												"is_active" => $chatServer->isActiveUser($user->id,$db->f("Institut_id")));
				if ($chatter){
					$active_chats[$chatServer->chatDetail[$db->f("Institut_id")]["id"]] = $db->f("Institut_id");
				}
			}
			if ($db->f("is_fak") && $db->f("inst_perms") == "admin") {
				$db2->query("SELECT a.Institut_id, a.Name, a.type FROM Institute a 
					 WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND a.Institut_id!='" .$db->f("Institut_id") . "' 
					 ORDER BY $sortby");
				while($db2->next_record()) {
					$my_inst[$db2->f("Institut_id")]=array(name=>$db2->f("Name"),status=>"admin",type=>($db2->f("type")) ? $db2->f("type") : 1,modules =>$Modules->getLocalModules($db->f("Institut_id"), "inst",$db->f("modules")));
					$value_list.="('".$db2->f("Institut_id")."',0".$loginfilenow[$db2->f("Institut_id")]."),";
					if ($GLOBALS['CHAT_ENABLE'] && ($my_inst[$db->f("Institut_id")]["modules"]["chat"])){
						$chatter = $chatServer->isActiveChat($db2->f("Institut_id"));
						$chat_info[$db2->f("Institut_id")] = array("chatter" => $chatter, "chatuniqid" => $chatServer->chatDetail[$db2->f("Institut_id")]["id"],
						"is_active" => $chatServer->isActiveUser($user->id,$db2->f("Institut_id")));
						if ($chatter){
							$active_chats[$chatServer->chatDetail[$db2->f("Institut_id")]["id"]] = $db2->f("Institut_id");
						}
					}
				}
			}
		}
		$value_list=substr($value_list,0,-1);
		$db->query("CREATE  TEMPORARY TABLE if NOT EXISTS loginfilenow_".$user->id." ( Seminar_id varchar(32) NOT NULL PRIMARY KEY, loginfilenow int(11) NOT NULL DEFAULT 0, INDEX(loginfilenow) ) TYPE=HEAP");
		$ins_query="REPLACE INTO loginfilenow_".$user->id." (Seminar_id, loginfilenow) VALUES ".$value_list;
		$db->query($ins_query);
		get_my_inst_values($my_inst);
		$db->query("DROP TABLE loginfilenow_".$user->id);
		if ($GLOBALS['CHAT_ENABLE']){
			if (is_array($active_chats)){
				$chat_invs = $sms->check_list_of_chatinv(array_keys($active_chats));
			}
		}
		foreach ($my_inst as $instid=>$values) {

			$cssSw->switchClass();
			$lastVisit = $loginfilenow[$instid];
			echo "<tr ".$cssSw->getHover().">";
			echo "<td class=\"".$cssSw->getClass()."\">&nbsp; </td>";
			// Name-field		
			echo "<td class=\"".$cssSw->getClass()."\"><a href=\"institut_main.php?auswahl=$instid\">";
			echo "<font size=-1>".htmlReady($INST_TYPE[$values["type"]]["name"] . ": " . $values["name"])."</font>";
			print ("</a></td>");
			// Content-field
			echo "<td class=\"".$cssSw->getClass()."\"  align=\"left\" nowrap>";
			print_institut_content($instid, $values);
			if (($GLOBALS['CHAT_ENABLE']) && ($values["modules"]["chat"])) {
				echo "<a href=\"".((!$auth->auth["jscript"]) ? "chat_online.php" : "#")."\" onClick=\"return open_chat(" . (($chat_info[$instid]['is_active']) ? "false" : "'$instid'") . ");\">&nbsp;";
				echo chat_get_chat_icon($chat_info[$instid]['chatter'], $chat_invs[$chat_info[$instid]['chatuniqid']], $chat_info[$instid]['is_active'],true);
				echo "</a>&nbsp;";
			} else
				echo "&nbsp; <img src='pictures/icon-leer.gif' width=\"15\" height=\"17\" border=0>";
				
			if (($GLOBALS['ILIAS_CONNECT_ENABLE']) && ($values["modules"]["ilias_connect"])) {
				$mod_count = get_seminar_modules($instid);
				if ($mod_count) {
					echo "<a href=\"institut_main.php?view=show&auswahl=$instid&redirect_to=seminar_lernmodule.php\">&nbsp;";
					echo "<img src=\"pictures/icon-lern.gif\" ";
					if (sizeof($mod_count) == 1)
						echo tooltip(sprintf(_("Die Einrichtung ist mit %s ILIAS-Lernmodul verbunden."), sizeof($mod_count)))."border=\"0\">";
					else
						echo tooltip(sprintf(_("Die Einrichtung ist mit %s ILIAS-Lernmodulen verbunden."), sizeof($mod_count)))."border=\"0\">";
					echo "</a>&nbsp;";
				}
				else
					echo "&nbsp;<img src=\"pictures/icon-leer.gif\" width=\"18\" height=\"20\" border=\"0\">";
			}
			echo "</td>";

			// Extendet views:

			// last visited-field
			if ($view=="ext") {
				if ($loginfilenow[$instid]==0) {
					echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>n.b.</font></td>";
				} else  {
				 	echo "<td class=\"".$cssSw->getClass()."\"align=\"center\" nowrap><font size=-1>", date("d.m.", $loginfilenow[$instid]),"</font></td>";
				}
			// Status-field
			echo "<td class=\"".$cssSw->getClass()."\" align=\"center\" nowrap><font size=-1>". $values["status"]."&nbsp;</font></td>";
			}

			// delete Entry from List:
			if (($values["status"]=="dozent") || ($values["status"]=="tutor") || ($values["status"]=="admin") || ($values["status"]=="autor"))
				echo "<td class=\"".$cssSw->getClass()."\" align=center>&nbsp;</td>";
			else
				printf("<td class=\"".$cssSw->getClass()."\" align=center align=center><a href=\"$PHP_SELF?auswahl=%s&cmd=kill\"><img src=\"pictures/trash.gif\" ".tooltip(_("aus der Einrichtung austragen"))." border=\"0\"></a></td>", $instid);
		 	echo "</tr>\n";
		}
	} else {
	 ?>
	 <tr>
	 	<td class="blank" colspan="2">&nbsp; 
	 	</td>
	 </tr>
	 <tr>
		 <td valign="top" class="blank">
			<table border="0" cellpadding="0" cellspacing="0" width="100%" align="center" class="blank">
				<?
				if ($meldung)	{
					parse_msg($meldung);
				}
	}

//Info-field on the right side
?>
	</table>
		</td>
			<td class="blank" width="270" valign="top">


<?


	$db->query("SELECT count(*) as count  FROM Institute");
	$db->next_record();
	$anzahltext = sprintf(_("Es sind %s weitere Einrichtungen vorhanden."), ($db->f("count")-$num_my_inst));
	
	if (!$perm->have_perm("dozent")) {

		$infobox = array	(			
			array  ("kategorie"  => _("Information:"),
				"eintrag" => array	(	
					array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => $anzahltext
					)
				)
			),
			array  ("kategorie" => _("Aktionen:"),
				"eintrag" => array	(	
					array	 (	"icon" => "pictures/suchen.gif",
										"text"  => sprintf(_("Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die %sSuchfunktion%s."), "<a href=\"institut_browse.php\">", "</a>")
					),
					array	 (	"icon" => "pictures/meinesem.gif",
										"text"  => sprintf(_("Wenn Sie weitere Einrichtungen in ihre pers&ouml;nliche Auswahl aufnehmen m&ouml;chten, k&ouml;nnen sie sich hier %szuordnen%s."), "<a href=\"edit_about.php?view=Karriere#einrichtungen\">", "</a>")
					)
				)
			)
		);

	} else {

		$infobox = array	(			
			array  ("kategorie"  => _("Information:"),
				"eintrag" => array	(	
					array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => $anzahltext
					)
				)
			),
			array  ("kategorie" => _("Aktionen:"),
				"eintrag" => array	(	
					array	 (	"icon" => "pictures/suchen.gif",
										"text"  => sprintf(_("Um Einrichtungen zu suchen und sich Informationen anzeigen zu lassen, nutzen Sie die %sSuchfunktion%s."), "<a href=\"institut_browse.php\">", "</a>")
					)
				)
			)
		);
	}


// print the info_box

print_infobox ($infobox,"pictures/einrichtungen.jpg");

?>				
				<br />
			</td>
		</tr>
	  </table>
     </td>
    </tr>

<?
}
// Save data back to database.
ob_end_flush(); //Outputbuffering beenden
page_close();
 ?>
</table>
<!-- $Id$ -->
