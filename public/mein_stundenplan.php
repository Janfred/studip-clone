<?
# Lifter002: TODO
/**
* mein_stundenplan.php
*
* view of personal timetable
*
*
* @author		Cornelis Kater <ckater@gwdg.de> Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @package		studip_core
* @modulegroup	views
* @module		mein_stundenplan.php
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// mein_stundenplan.php - Persoenliche Stundenplanansicht in Stud.IP.
// Copyright (C) 2001-2002 Cornelis Kater <ckater@gwdg.de> Suchi & Berg GmbH <info@data-quest.de>
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


page_open(array("sess" => "Seminar_Session",
                "auth" => "Seminar_Auth",
                "perm" => "Seminar_Perm",
                "user" => "Seminar_User"));

ob_start(); //Outputbuffering for max performance

include('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
$view = 'standard';
if (($_REQUEST['view'] == 'print') || ($_REQUEST['view'] == 'edit')) {
	$view = $_REQUEST['view'];	
}

if ($view == 'print') {
	$_include_stylesheet = "style_print.css"; // use special stylesheet for printing
}

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head

require_once 'config.inc.php'; //Daten laden
require_once 'config_tools_semester.inc.php';
require_once 'lib/include/ms_stundenplan.inc.php';
require_once 'lib/visual.inc.php';
require_once 'lib/classes/SemesterData.class.php';
require_once 'lib/raumzeit/CycleDataDB.class.php';

if ($RESOURCES_ENABLE)
 	require_once ($RELATIVE_PATH_RESOURCES.'/resourcesFunc.inc.php');


//eingebundene Daten auf Konsitenz testen (Semesterwechsel? nicht mehr Admin im gespeicherten Institut?)
check_schedule_settings();

if (!$inst_id) {
	$HELP_KEYWORD="Basis.MyStudIPStundenplan";
	$CURRENT_PAGE = _("Mein Stundenplan");
} else {
	$HELP_KEYWORD="Basis.TerminkalenderStundenplan";
	$CURRENT_PAGE = $SessSemName["header_line"]." - "._("Veranstaltungs-Timetable");
}

if ($view != 'print') {
	include 'lib/include/header.php';   //hier wird der "Kopf" nachgeladen
	if ($inst_id) //Links if we show in the instiute-object-view
		include 'lib/include/links_openobject.inc.php';
	elseif (!$perm->have_perm("admin")) //if not in the adminview, it's the user view!
		include ('lib/include/links_sms.inc.php');
	else
		include ('lib/include/links_sms.inc.php');
}

$db = new DB_Seminar;
$db2 = new DB_Seminar;
$semester = new SemesterData;
$hash_secret = "machomania";

$all_semester = $semester->getAllSemesterData();
//Wert fuer colspan Ausrechnen
$glb_colspan = 0;

if($view != 'edit' && !$_REQUEST['inst_id']) {
	foreach($my_schedule_settings["glb_days"] as $tmp) {
		if ($tmp){
			$glb_colspan++;
		}
	}
}else {
	$glb_colspan = 7;
}

// Hat man sich inzwischen fest eingetragen, Eintrag aus dem virtuellen Stundenplan löschen
$db->query("SELECT * FROM seminar_user_schedule a, seminar_user b WHERE a.range_id = b.Seminar_id AND a.user_id = b.user_id AND a.user_id = '".$auth->auth['uid']."'");
while ($db->next_record()) {
	$db2->query("DELETE FROM seminar_user_schedule WHERE range_id = '".$db->f('Seminar_id')."' AND user_id = '".$db->f('user_id')."'");
}

// Virtuellen Stundenplaneintrag erstellen
if ($cmd == "add_entry") {
	$db->query("INSERT INTO seminar_user_schedule SET range_id = '$semid', user_id = '".$auth->auth['uid']."'");
}

// Virtuellen Stundenplaneintrag löschen
if ($cmd == "delete_entry") {
	$db->query("DELETE FROM seminar_user_schedule WHERE range_id = '$semid' AND user_id = '".$auth->auth['uid']."'");
}

//persoenlichen Eintrag wegloeschen
if ($cmd == "delete") {
	unset($my_personal_sems[$sem_id]);
}

// hide entry
if ($cmd == "hide") {
	if(!$my_schedule_settings['hidden']) {
		$my_schedule_settings['hidden'] = array();
	}
	
	$my_schedule_settings['hidden'][$sem_id] = True;
}

// show previously hidden entry
if ($cmd == "show") {
	if(!$my_schedule_settings['hidden']) {
		$my_schedule_settings['hidden'] = array();
	}
	
	// echo $my_schedule_settings['hidden'][$sem_id];
	if($my_schedule_settings['hidden'][$sem_id]){
		unset($my_schedule_settings['hidden'][$sem_id]);
	}
}
// echo _D($my_schedule_settings);

//ein weiterer persoenlicher Eintrag wurde uebermittelt
if ($cmd=="insert") {
	switch ($tag) {
		// nicht wundern, wir nehmen hier irgendwelche Tage, von denen wir 
		// wissen, was das fuer ein Wochentag war, um den Wochentag zu fixieren
		// (dieser Programmteil entstand 03/2001... *G)
		case 1: {
			$start_time = mktime($start_stunde,$start_minute,0,3,26,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,26,2001);
			break;
			}
		case 2: {
			$start_time = mktime($start_stunde,$start_minute,0,3,27,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,27,2001);
			break;
			}
		case 3: {
			$start_time = mktime($start_stunde,$start_minute,0,3,28,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,28,2001);
			break;
			}
		case 4: {
			$start_time = mktime($start_stunde,$start_minute,0,3,29,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,29,2001);
			break;
			}
		case 5: {
			$start_time = mktime($start_stunde,$start_minute,0,3,30,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,30,2001);
			break;
			}
		case 6: {
			$start_time = mktime($start_stunde,$start_minute,0,3,31,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,31,2001);
			break;
			}
		case 7: {
			$start_time = mktime($start_stunde,$start_minute,0,4,1,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,4,1,2001);
			break;
			}
		}

	$id=md5(uniqid($hash_secret));
	$my_personal_sems[$id]=array("start_time"=>$start_time, "ende_time"=>$ende_time, "beschreibung"=>$beschreibung, "room" =>$room, "doz" =>$dozent, "seminar_id"=>$id);
	//die;
}

// meine Seminare einlesen
if ($inst_id) {
	$db->query("SELECT seminare.Seminar_id, Name, VeranstaltungsNummer, start_time, duration_time,  metadata_dates FROM seminare WHERE Institut_id = '$inst_id' AND visible='1'");
	$view="inst";
} else {
	$user_id=$user->id;
	if ($perm->have_perm("admin")) {
		$db->query("SELECT seminare.Seminar_id, Name, VeranstaltungsNummer, start_time, duration_time,  metadata_dates FROM seminare WHERE Institut_id = '".$my_schedule_settings ["glb_inst_id"]."' ");
		$view="inst_admin";
	} else {
		$db->query("SELECT seminare.Seminar_id, Name, VeranstaltungsNummer, start_time, duration_time,  metadata_dates FROM  seminar_user LEFT JOIN seminare USING (seminar_id) WHERE user_id = '$user_id'");
	}
}

// select right semester
if ($_REQUEST['inst_id']) {
	$tmp_sem_nr = $_REQUEST['instview_sem'];
} else {
	$k = 0;
	foreach ($all_semester as $a) {
		if ($sem_name) {
			if (rawurldecode($sem_name) == $my_schedule_settings["glb_sem"])
				$tmp_sem_nr = $k;
		} else {
			if ($a["name"] == $my_schedule_settings["glb_sem"])
				$tmp_sem_nr = $k;
			$k++;
		}
	}
}

if (!$tmp_sem_nr) {
	if (time() < $VORLES_ENDE) {
		$tmp_sem_beginn = $SEM_BEGINN;
		$tmp_sem_ende = $SEM_ENDE;
		$tmp_sem_nr = $SEM_ID;
	} else {
		$tmp_sem_beginn=$SEM_BEGINN_NEXT;
		$tmp_sem_ende=$SEM_ENDE_NEXT;
		$tmp_sem_nr=$SEM_ID_NEXT;
	}
} else {
	$tmp_sem_beginn=$all_semester[$tmp_sem_nr]["beginn"];
	$tmp_sem_ende=$all_semester[$tmp_sem_nr]["ende"];
}

// Set the view (begin hour and and hour)
if ($_REQUEST['inst_id']) {
	$global_start_time=8;
	$global_end_time=20;
} else {
	$global_start_time=$my_schedule_settings["glb_start_time"];
	$global_end_time=$my_schedule_settings["glb_end_time"];
}

//Array der Seminare erzeugen
for ($seminar_user_schedule = 1; $seminar_user_schedule <= 2; $seminar_user_schedule++) {
	if ($seminar_user_schedule == 2) {
		if (!$inst_id) {
			// Das gleiche nochmal mit den virtuellen Veranstaltungseintragungen
			$db->query($query = "SELECT b.* FROM seminar_user_schedule a, seminare b WHERE a.range_id = b.Seminar_id AND a.user_id = '".$auth->auth['uid']."'");
		}
	}

	while ($db->next_record())
	{
	//Bestimmen, ob die Veranstaltung in dem Semester liegt, was angezeigt werden soll
	$use_this=FALSE;
	$term_data=unserialize($db->f("metadata_dates"));

	if (($db->f("start_time") <=$tmp_sem_beginn) && ($tmp_sem_beginn <= ($db->f("start_time") + $db->f("duration_time")))) {
		$use_this=TRUE;
	}
	if (($use_this) && (is_array($term_data["turnus_data"]) && count($term_data["turnus_data"])))
		{
		//Zusammenbasteln Dozentenfeld
		$db2->query("SELECT Nachname, username, position FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) WHERE status='dozent' AND Seminar_id ='".$db->f("Seminar_id")."' ORDER BY position ");
		$dozenten='';
		$i=1;
		while ($db2->next_record())
			{
			if ($i>1)
				$dozenten.=", ";
			if ($view != 'print')
				$dozenten.="<a href =\"about.php?username=".$db2->f("username")."\">".htmlReady($db2->f("Nachname"))."</a>";
			else
				$dozenten.= htmlReady($db2->f("Nachname"));
			$i++;
			}

		$i=0;
		foreach ($term_data["turnus_data"] as $data)
			if ($data["end_stunde"] >= $global_start_time) {
				//generate the room

				if ($RESOURCES_ENABLE) {
                    $roomIDsArray = CycleDataDB::getPredominantRoomDB($data["metadate_id"]);
                    if( $roomIDsArray) {
                        $tmp_room = getResourceObjectName($roomIDsArray[0]); 
                    } else {
                        $tmp_room = _("n. A.");
                    }
                } else {
                    $roomName = CycleDataDB::getFreeTextPredominantRoomDB($data["metadate_id"]);
                    if( $roomName) {
                        $tmp_room = $roomName; 
                    } else {
                        $tmp_room = _("n. A.");
                    }
                }

				//Patch fuer Problem mit alten Versionwn <=0.7 (Typ war falsch gesetzt), wird nur fuer rueckwaerts-Kompatibilitaet benoetigt
				settype ($data["start_stunde"], "integer");
				settype ($data["end_stunde"], "integer");
				settype ($data["start_minute"], "integer");
				settype ($data["end_minute"], "integer");

				//Check, ob die Endzeit ueber den sichtbaren Bereich des Stundenplans hinauslaeuft, wenn ja wird row_span entsprechend angepasst
				if ($data["end_stunde"] >$global_end_time) {
					$tmp_row_span = ((($global_end_time - $data["start_stunde"])+1) *4);
					$tmp_row_span = $tmp_row_span - (int)($data["start_minute"] / 15);
				} else
					$tmp_row_span = ceil((($data["end_stunde"] - $data["start_stunde"]) * 4) + (($data["end_minute"] - $data['start_minute'] ) / 15));

				//Check, ob die Startzeit ueber den Sichtbaren Bereich hinauslaeuft, wenn ja wird row_span und der index entsprechend frisiert
				if ($data["start_stunde"] < $global_start_time) {
					$tmp_row_span = $tmp_row_span - (($global_start_time - $data["start_stunde"]) *4);
					$tmp_row_span = $tmp_row_span + (int)($data["start_minute"] / 15);
					$idx_corr_h = $global_start_time - $data["start_stunde"];
					$idx_corr_m = (0 - $data["start_minute"]) ;
				} else {
					$idx_corr_h = 0;
					$idx_corr_m = 0;
				}

				//Dummy-Timestamps erzeugen. Der 5.8.2001 (ein Sonntag) wird als Grundlage verwendet.
				$start_time=mktime($data["start_stunde"], $data["start_minute"], 0, 8, (5+$data["day"]), 2001);
				$end_time=mktime($data["end_stunde"], $data["end_minute"], 0, 8, (5+$data["day"]), 2001);

				$i++; //<pfusch>$i (fuer alle einzelnen Objekte eines Seminars) wird hier zur Kennzeichnung der einzelen Termine eines Seminars untereinander verwendet. Unten wird die letzte Stelle jeweils weggelassen. </pfusch>

				$my_sems[$db->f("Seminar_id").$i]=array("start_time_idx"=>$data["start_stunde"]+$idx_corr_h.(int)(($data["start_minute"]+$idx_corr_m) / 15).$data["day"], "start_time"=>$start_time, "end_time"=>$end_time, "name"=>$db->f("Name"), "nummer"=>$db->f("VeranstaltungsNummer"), "seminar_id"=>$db->f("Seminar_id").$i,  "ort"=>$tmp_room, "row_span"=>$tmp_row_span, "dozenten"=>$dozenten, "personal_sem"=>FALSE, 'desc'=>$data['desc'], "virtual" => ($seminar_user_schedule == 2) ? true : false);
			}

		}
	}
}
//Daten aus der Sessionvariable hinzufuegen
if ((is_array($my_personal_sems)) && (!$inst_id)){
	foreach ($my_personal_sems as $key => $mps){
		if(!$mps["ende_time"] || !$mps['start_time']){
			unset($my_personal_sems[$key]);
			continue;
		}
		if (date("G", $mps["ende_time"]) >= $global_start_time) {
			//auch hier nochmal der Check
			if (date("G", $mps["ende_time"]) > $global_end_time) {
				$tmp_end_time = mktime($global_end_time+1, 00, 00, date ("n", $mps["start_time"]), date ("j", $mps["start_time"]), date ("Y", $mps["start_time"]));
				$tmp_row_span = (int)(($tmp_end_time - $mps["start_time"]) /15/60);
			} else
			$tmp_row_span = (int)(($mps["ende_time"] - $mps["start_time"])/15/60);
			
			//und der andere
			if (date("G", $mps["start_time"]) < $global_start_time) {
				$tmp_start_time = mktime($global_start_time, 00, 00, date ("n", $mps["start_time"]), date ("j", $mps["start_time"]), date ("Y", $mps["start_time"]));
				$tmp_row_span = (int)(($tmp_end_time - $tmp_start_time) /15/60);
				$idx_corr_h = $global_start_time - date("G", $mps["start_time"]);
				$idx_corr_m = (0 - date("i", $mps["start_time"]));
			} else {
				$idx_corr_h = 0;
				$idx_corr_m = 0;
			}
			
			//aus Sonntag=0 wird Sonntag=7, damit laesst's sich besser arbeiten *g
			$tmp_day=date("w", $mps["start_time"]);
			if ($tmp_day==0) $tmp_day=7;
			
			$my_sems[$mps["seminar_id"]]=array("start_time_idx"=>date("G", $mps["start_time"])+$idx_corr_h.(int)((date("i", $mps["start_time"])+$idx_corr_m) / 15).$tmp_day, "start_time"=>$mps["start_time"], "end_time"=>$mps["ende_time"], "name"=>$mps["beschreibung"], "seminar_id"=>$mps["seminar_id"],  "ort"=>$mps["room"], "row_span"=>$tmp_row_span, "dozenten"=>htmlReady($mps["doz"]), "personal_sem"=>TRUE);
		}
	}
}

//Array der Zellenbelegungen erzeugen
if (is_array($my_sems))
foreach ($my_sems as $ms)
	{
	$m=1;
	$idx_tmp=$ms["start_time_idx"];
	if ($ms["row_span"]>0)
		for ($m; $m<=$ms["row_span"]; $m++)
			{
			if ($m==1)  $start_cell=TRUE; else $start_cell=FALSE;
			$cell_sem[$idx_tmp][$ms["seminar_id"]] = $start_cell;
			if (($idx_tmp % 100) -date("w",$ms["start_time"]) == 30)
				$idx_tmp=$idx_tmp+70;
			else
				$idx_tmp=$idx_tmp+10;
			}
	else
		$cell_sem[$idx_tmp][$ms["seminar_id"]] = TRUE;
	}

//Alle Seminare, die sich ueberschneiden, zusammenfassen
$i=1;
for ($i; $i<7; $i++)
	{
	$n=$global_start_time;
	for ($n; $n<$global_end_time+1; $n++)
		{
		$l=0;
		for ($l; $l<4; $l++)
			{
			$idx=($n*100)+($l*10)+$i;
			if ($cell_sem[$idx])
				if (sizeof($cell_sem[$idx])>0)
					{
					$rows=0;
					$start_idx=$idx;
					while ($cs = each ($cell_sem [$idx]))
						if ($cs[1])
							if ($my_sems[$cs[0]]["row_span"]>$rows) $rows=$my_sems[$cs[0]]["row_span"];
					reset ($cell_sem[$idx]);
					if ($rows>1)
						{
						$s=2;
						for ($s; $s<=$rows; $s++)
							{
							$l++;
							if ($l>=4)
								{
								$l=0;
								$n++;
								}
							$idx=($n*100)+($l*10)+$i;
							while ($cs = each ($cell_sem [$idx]))
								if ($cs[1])
									{
									$cell_sem[$idx][$cs[0]]=FALSE;
									$cell_sem[$start_idx][$cs[0]]=TRUE;
									if ($my_sems[$cs[0]]["row_span"] > $rows -$s +1)
										$rows=$rows+($my_sems[$cs[0]]["row_span"]-($rows-$s +1));
									}
								reset ($cell_sem[$idx]);
							}
						}
					$cs = each (array_slice ($cell_sem[$start_idx], 0));
					reset ($cell_sem[$start_idx]);
					$my_sems[$cs[0]]["row_span"] = $rows;
					}
			}
		}
	}

?>
<table width ="100%" cellspacing=0 cellpadding=2 border=0>
<?

if (!$print_view) {
?>
<tr>
	<td class="blank" colspan=<? echo $glb_colspan+1?>>&nbsp;
		<form action="<? echo $PHP_SELF ?>" method="POST">
		<blockquote>
		<?
		if ($view=="user")  {
			echo _("Der Stundenplan zeigt Ihnen alle regelm&auml;&szlig;igen Veranstaltungen eines Semesters. Um den Stundenplan auszudrucken, nutzen Sie bitte die Druckfunktion ihres Browsers.") . "<br /><br />";
			echo "<font size=-1>";
			printf(_("Wenn Sie weitere Veranstaltungen aus Stud.IP in ihren Stundenplan aufnehmen m&ouml;chten, nutzen Sie bitte die %sVeranstaltungssuche%s."), "<a href = \"sem_portal.php\">", "</a>");
			echo "<br>";
			if ($CALENDAR_ENABLE)
				printf(_("Ihre pers&ouml;nlichen Termine finden Sie im %sTerminkalender%s."), "<a href=\"calendar.php\">", "</a>");
			echo "</font>";
		} elseif ($view == "inst") { ?>
		<?=_("Im Veranstaltungs-Timetable sehen Sie alle Veranstaltungen eines Semesters an der gew&auml;hlten Einrichtung.")?><br />
		<br /><font size=-1><?=_("Angezeigtes Semester:")?>&nbsp;
			<select name="instview_sem" style="vertical-align:middle">
			<?
				foreach ($all_semester as $key=>$val) {
					printf ("<option %s value=\"%s\">%s</option>\n", ($tmp_sem_nr == $key) ? "selected" : "", $key, $val["name"]);
				}
			?>
			</select>&nbsp;
			<input type="IMAGE" value="change_instview_sem" <? echo makeButton("uebernehmen", "src") ?> border=0 align="absmiddle" value="<?=_("&uuml;bernehmen")?>" />&nbsp;
			<input type="HIDDEN" name="inst_id" value="<? echo $inst_id ?>" /><br>
		<? } else { ?>
		<?=_("Im Veranstaltungs-Timetable sehen Sie alle Veranstaltungen eines Semesters an der gew&auml;hlten Einrichtung.")." <br /> "._("Sie k&ouml;nnen zus&auml;tzlich eigene Eintr&auml;ge anlegen.")?><br />
		<br />
			<?
		}
		if ($view !="user")
			printf ("<br><font size=-1><a target=\"_blank\" href=\"%s?print_view=TRUE%s\">"._("Druckansicht dieser Seite (wird in einem neuen Browserfenster ge&ouml;ffnet).")."</a></font>", $PHP_SELF, ($inst_id) ? "&inst_id=$inst_id&instview_sem=$instview_sem" : "");
		?>
		<br>
		</blockquote>
		</form>

	</td>
</tr>
<tr>
<td class="steel1" colspan=<? echo $glb_colspan+1?>>
<? }

ob_end_flush(); //Clear buffer for ouput the headers
ob_start();

?>
<table <? if ($print_view) { ?> bgcolor="#eeeeee" <? } ?> width ="99%" align="center" cellspacing=1 cellpadding=0 border=0>
<tr>
	<td width="10%" align="center" class="rahmen_steelgraulight" ><?=_("Zeit")?>
	</td>
	<? if ($my_schedule_settings["glb_days"]["mo"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight" ><?=_("Montag")?>
	</td><?}
	if ($my_schedule_settings["glb_days"]["di"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Dienstag")?>
	</td><?}
	if ($my_schedule_settings["glb_days"]["mi"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Mittwoch")?>
	</td><?}
	if ($my_schedule_settings["glb_days"]["do"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Donnerstag")?>
	</td><?}
	if ($my_schedule_settings["glb_days"]["fr"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Freitag")?>
	</td><?}
	if ($my_schedule_settings["glb_days"]["sa"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Samstag")?>
	</td><?}
	if ($my_schedule_settings["glb_days"]["so"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight"><?=_("Sonntag")?>
	</td><?}?>
</tr>
<?



//Aufbauen der eigentlichen Tabelle
$i=$global_start_time;

for ($i; $i<$global_end_time+1; $i++)
	{
	$k=0;
	for ($k; $k<4; $k++)
		{
		if ($k==0)
			{
			echo "<tr><td align=\"center\" class=\"rahmen_steelgraulight\" rowspan=4>";
			if ($i<10) echo "0";
			echo $i, ":00 "._("Uhr")."</td>";
			}
		else echo "<tr>";
		$l=1;
		for ($l; $l<8; $l++)
			{
			//ausgeblendete Tage skippen
			if (($l==1) && (!$my_schedule_settings["glb_days"]["mo"] )) continue;
			if (($l==2) && (!$my_schedule_settings["glb_days"]["di"] )) continue;
			if (($l==3) && (!$my_schedule_settings["glb_days"]["mi"] )) continue;
			if (($l==4) && (!$my_schedule_settings["glb_days"]["do"] )) continue;
			if (($l==5) && (!$my_schedule_settings["glb_days"]["fr"] )) continue;
			if (($l==6) && (!$my_schedule_settings["glb_days"]["sa"] )) continue;
			if (($l==7) && (!$my_schedule_settings["glb_days"]["so"] )) continue;
			//if ($l <>8)
			{
			$idx=($i*100)+($k*10)+$l;
			unset($cell_content);
			$m=0;
			if ($cell_sem[$idx])
				while ($cs = each ($cell_sem [$idx]))
					$cell_content[]=array("seminar_id"=>$cs[0], "start_cell"=>$cs[1]);
			if ((!$cell_sem[$idx]) || ($cell_content[0]["start_cell"]))	echo "<td ";
			$u=0;
			if (($cell_sem[$idx]) && ($cell_content[0]["start_cell"]))
				{
				$r=0;
				foreach ($cell_content as $cc)
					{
					if ($r==0) {
						echo "class=\"rahmen_white\" valign=\"top\" rowspan=",$my_sems[$cell_content[0]["seminar_id"]]["row_span"],">";
						echo "<table width=\"100%\" cellspacing=0 cellpadding=2 border=0><tr><td class=\"topic\">";
					} else
						echo "</td></tr><tr><td class=\"topic\">";
					if (($print_view) && ($r!=0))
						echo "<hr src=\"".$GLOBALS['ASSETS_URL']."images/border.jpg\" width=\"100%\">";
					$r++;
					echo "<font size=-1 ";
					if (!$print_view)
						echo "color=\"#FFFFFF\"";
					echo ">", date ("H:i",  $my_sems[$cc["seminar_id"]]["start_time"]);
					if  ($my_sems[$cc["seminar_id"]]["start_time"] <> $my_sems[$cc["seminar_id"]]["end_time"])
						echo " - ",  date ("H:i",  $my_sems[$cc["seminar_id"]]["end_time"]);
					if (!$my_sems[$cc['seminar_id']]['virtual']) {
						if ($my_sems[$cc["seminar_id"]]['desc']) echo ' ('.htmlReady($my_sems[$cc["seminar_id"]]['desc']).')';
					}
					if ($my_sems[$cc['seminar_id']]['ort']) echo ",  ", htmlReady($my_sems[$cc["seminar_id"]]["ort"]);
					echo '</font></td>';
					echo "<td class=\"topic\" align=\"right\">";
					if ($my_sems[$cc['seminar_id']]['virtual']) {
						echo "<a href=\"$PHP_SELF?cmd=delete_entry&semid=".substr($my_sems[$cc["seminar_id"]]["seminar_id"], 0, 32)."\">";
						echo "<img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" align=\"absmiddle\" border=\"0\"></a>";
					}

					echo "</td></tr><tr><td class=\"blank\">";
					if ((!$my_sems[$cc["seminar_id"]]["personal_sem"]) && (!$print_view)) {
						if ($my_sems[$cc['seminar_id']]['virtual']) {
							echo "<a href=\"details.php?sem_id=".substr($my_sems[$cc["seminar_id"]]["seminar_id"], 0, 32)."\">";
							echo "<FONT size=\"-1\" color=\"green\">";
						} else {
							if ($view=="inst")
								echo  "<a href=\"details.php?sem_id=";
							else
								echo  "<a href=\"seminar_main.php?auswahl=";
							echo substr($my_sems[$cc["seminar_id"]]["seminar_id"], 0, 32), "\"><font size=-1>";
						}
						if ($my_sems[$cc["seminar_id"]]["nummer"]) {
							echo htmlReady($my_sems[$cc["seminar_id"]]["nummer"]) . "&nbsp;";
						}
						echo htmlReady(substr($my_sems[$cc["seminar_id"]]["name"], 0,50));
						if (strlen($my_sems[$cc["seminar_id"]]["name"])>50)
							echo "...";
						echo"</font></a>";
						}
					else
						{
						echo "<font size=-1>";
						if ($my_sems[$cc["seminar_id"]]["nummer"]) {
							echo htmlReady($my_sems[$cc["seminar_id"]]["nummer"]) . "&nbsp;";
						}
						echo htmlReady(substr($my_sems[$cc["seminar_id"]]["name"], 0,50));
						if (strlen($my_sems[$cc["seminar_id"]]["name"])>50)
							echo "...";
						echo "</font>";
						}
					if ($my_sems[$cc["seminar_id"]]["dozenten"])
						echo "<br><div align=\"right\"><font size=-1>", $my_sems[$cc["seminar_id"]]["dozenten"], "</font></div>";
					if (($my_sems[$cc["seminar_id"]]["personal_sem"]) && (!$print_view))
						echo "<div align=\"right\"><a href=\"",$PHP_SELF, "?cmd=delete&d_sem_id=",$my_sems[$cc["seminar_id"]]["seminar_id"], "\"><img border=0 src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" ".tooltip(_("Diesen Termin löschen")).">&nbsp;</a></div>";
					}
				echo "</td></tr></table></td>";
				}
			if (!$cell_sem[$idx])  echo "class=\"steel1\"></td>";
			}
			}
			echo "</tr>\n";
		}
	}

	if ($print_view) {
		printf  ("<tr><td colspan=%s><i><font size=-1>&nbsp; "._("Erstellt am %s um %s  Uhr.")."</font></i></td><td align=\"right\"><font size=-2><img src=\"".$GLOBALS['ASSETS_URL']."images/logo2b.gif\"><br />&copy; %s v.%s&nbsp; &nbsp; </font></td></tr></tr>", $glb_colspan, date("d.m.y", time()), date("G:i", time()), date("Y", time()), $SOFTWARE_VERSION);
		}
	else {
		}

echo "</table></td></tr>";


if($view == 'edit') {
?>
<div class="steelgraulight" style="margin-top: 30px; padding: 10px;">
	<b>&nbsp;<?=_("Eigene Veranstaltung eintragen:")?></b><br>
	<div style="margin-left: 15px; padding: 10px">

		<font size=-1>&nbsp;(<?=_("Hier k&ouml;nnen sie Veranstaltungen, die nicht im Stud.IP System existieren oder andere, eigene Ereignisse eintragen")?>)</font><br>
		<form method="POST" action="<? echo $PHP_SELF ?>?cmd=insert">
			&nbsp;<?_("Wochentag:")?>
			<select name="tag">
				<option value="1"><?=_("Montag")?></option>
				<option value="2"><?=_("Dienstag")?></option>
				<option value="3"><?=_("Mittwoch")?></option>
				<option value="4"><?=_("Donnerstag")?></option>
				<option value="5"><?=_("Freitag")?></option>
				<option value="6"><?=_("Samstag")?></option>
				<option value="7"><?=_("Sonntag")?></option>
			</select>&nbsp; &nbsp;
			<?=_("Beginn:")?>
			<?
			echo"<select name=\"start_stunde\">";
			for ($i=$global_start_time; $i<=$global_end_time; $i++)
				{
				if ($i==9) echo "<option selected value=".$i.">".$i."</option>";
					else echo "<option value=".$i.">".$i."</option>";
				}
				echo"</select>";
				echo"<select name=\"start_minute\">";
				for ($i=0; $i<=45; $i=$i+15)
				{
				if ($i==0) echo "<option selected value=".$i.">0".$i."</option>";
					else echo "<option value=".$i.">".$i."</option>";
				}
				echo"</select> "._("Uhr")."&nbsp; &nbsp; ";
				?>
			<?=_("Ende:")?>
			<?
			echo"<select name=\"ende_stunde\">";
			for ($i=$global_start_time; $i<=$global_end_time; $i++)
				{
				if ($i==9) echo "<option selected value=".$i.">".$i."</option>";
					else echo "<option value=".$i.">".$i."</option>";
				}
				echo"</select>";
				echo"<select name=\"ende_minute\">";
				for ($i=0; $i<=45; $i=$i+15)
				{
				if ($i==0) echo "<option value=".$i.">0".$i."</option>";
				elseif ($i==45) echo "<option selected value=".$i.">".$i."</option>";
					else echo "<option value=".$i.">".$i."</option>";
				}
				echo"</select> "._("Uhr");
				echo "<br />&nbsp; "._("Beschreibung:");
				?>
				<input name="beschreibung" type="text" size=40 maxlength=255>&nbsp; &nbsp;
				<?=_("Raum:")?>
				<input name="room" type="text" size=20 maxlength=255>&nbsp; &nbsp;
				<?=_("DozentIn:")?>
				<input name="dozent" type="text" size=20 maxlength=255><br />&nbsp;
				<input name="send" type="IMAGE" <?=makeButton("eintragen", "src")?> value="<?=("Eintragen")?>">
				</form>
</div></div>

<?php

if(count($my_schedule_settings['hidden']) > 0) {
?>
	<div class="steelgraulight" style="margin: 0px; padding: 10px;">
	<b>&nbsp;<?=_("Ausgeblendete Termine:")?></b><br>
	<div style="margin-left: 15px; padding: 10px">

<?php
$first = True;
foreach($my_schedule_settings['hidden'] as $id => $value) {
	if(!$first){
		echo ', ';
	}
	$first = False;

	echo '<a href="'. $PHP_SELF .'?cmd=show&sem_id='. $id .'" title="'. _("Diesen Termin wieder einblenden") .'">';
	if($my_sems[$id]['desc']){
		echo htmlReady($my_sems[$id]['desc']);
	} else {
		echo "Seminar";
	}
	echo date(" (D H:i)", $my_sems[$id]["start_time"]);
	echo '</a>';
}
} // view == edit

echo '</div></div>';
}



echo '</td></tr></table>';

ob_end_flush(); //end outputbuffering
// Save data back to database.

include ('lib/include/html_end.inc.php');
page_close();
?>
