<?php
/*
mein_stundenplan.php - Persoenliche Stundenplanansicht in Stud.IP.
Copyright (C) 2001-2002 Cornelis Kater <ckater@gwdg.de>

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

ob_start(); //Outputbuffering for max performance

?>

<html>
 <head>
<!--
// here i include my personal meta-tags; one of those might be useful:
// <META HTTP-EQUIV="REFRESH" CONTENT="<?php print $auth->lifetime*60;?>; URL=logout.php">
-->
  <title>Stud.IP</title>
        <?if (!$print_view) {?>
        <link rel="stylesheet" href="style.css" type="text/css">
        <?}
        else {?>
        <link rel="stylesheet" href="style_print	.css" type="text/css">
        <?}?>
 </head>
<body bgcolor="#ffffff">


<?php
require_once "$ABSOLUTE_PATH_STUDIP/seminar_open.php";
require_once "$ABSOLUTE_PATH_STUDIP/config.inc.php"; //Daten laden
require_once "$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php"; 
require_once "$ABSOLUTE_PATH_STUDIP/ms_stundenplan.inc.php";
require_once "$ABSOLUTE_PATH_STUDIP/visual.inc.php";

//eingebundene Daten auf Konsitenz testen (Semesterwechsel? nicht mehr Admin im gespeicherten Institut?)
check_schedule_settings();

if (!$print_view) {
	include "$ABSOLUTE_PATH_STUDIP/header.php";   //hier wird der "Kopf" nachgeladen
	if ($inst_id) //Links if we show in the instiute-object-view
		include "$ABSOLUTE_PATH_STUDIP/links1.php";
	elseif (!$perm->have_perm("admin")) //if not in the adminview, it's the user view!
		include "$RELATIVE_PATH_CALENDAR/calendar_links.inc.php";
	}

if ($change_view) {
	change_schedule_view();
	echo "</tr></td></table>";
	die;
	}


$db=new DB_Seminar;
$db2=new DB_Seminar;
$hash_secret="machomania";
setlocale ("LC_TIME","de_DE");

//laden der persoenlichen Eintraege/Einstellungen aus dem Sessionmanagement
//$user->register("my_personal_sems");

//temporaere Routine, die alle alten Eintraege in my_personal_sems in das neue (=assoziative) Format 
//konvertiert, die alten loescht bzw. deaktiviert und sie damit ueber das neue System loeschbar macht.
//Funktionweise kann allerdings leider schwer gestestet werden....... :(
//Diese Routine kann nach einiger Zeit auch wieder weg

/*for ($o=0; $o<1000; $o++)
	if (!$my_personal_sems[$my_personal_sems[$o]["seminar_id"]]) {
		$my_personal_sems[$my_personal_sems[$o]["seminar_id"]]=$my_personal_sems[$o];
		$my_personal_sems[$o]='';
		unset($my_personal_sems[$o]);
		}*/
	
//Wert fuer colspan Ausrechnen
$glb_colspan=0;
if ($my_schedule_settings["glb_days"]["mo"]) $glb_colspan++;
if ($my_schedule_settings["glb_days"]["di"]) $glb_colspan++;
if ($my_schedule_settings["glb_days"]["mi"]) $glb_colspan++;
if ($my_schedule_settings["glb_days"]["do"]) $glb_colspan++;
if ($my_schedule_settings["glb_days"]["fr"]) $glb_colspan++;
if ($my_schedule_settings["glb_days"]["sa"]) $glb_colspan++;
if ($my_schedule_settings["glb_days"]["so"]) $glb_colspan++;

//persoenlichen Eintrag wegloeschen
if ($cmd=="delete")
 {
    unset ($my_personal_sems[$d_sem_id]);
}


//ein weiterer persoenlicher Eintrag wurde uebermittelt
if ($cmd=="insert") {
	switch ($tag) {
		case "Montag": {//nicht wundern, wir nehmen hier irgendwelche Tage, von denen wir wissen, was das fuer ein Wochentag war, um den Wochentag zu fixieren (dieser Programmteil entstand 03/2001... *G)
			$start_time = mktime($start_stunde,$start_minute,0,3,26,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,26,2001);
			break;
			}
		case "Dienstag": {
			$start_time = mktime($start_stunde,$start_minute,0,3,27,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,27,2001);
			break;
			}
		case "Mittwoch": {
			$start_time = mktime($start_stunde,$start_minute,0,3,28,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,28,2001);
			break;
			}
		case "Donnerstag": {
			$start_time = mktime($start_stunde,$start_minute,0,3,29,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,29,2001);
			break;
			}
		case "Freitag": {
			$start_time = mktime($start_stunde,$start_minute,0,3,30,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,30,2001);
			break;
			}
		case "Samstag": {
			$start_time = mktime($start_stunde,$start_minute,0,3,31,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,3,31,2001);
			break;
			}
		case "Sonntag": {
			$start_time = mktime($start_stunde,$start_minute,0,4,1,2001);
			$ende_time = mktime($ende_stunde,$ende_minute,0,4,1,2001);
			break;
			}
		}

	$id=md5(uniqid($hash_secret));
	$my_personal_sems[$id]=array("start_time"=>$start_time, "ende_time"=>$ende_time, "beschreibung"=>$beschreibung, "seminar_id"=>$id);
	//die;
	}

//meine Seminare einlesen
if ($inst_id) {
	$db->query("SELECT seminare.Seminar_id, Name, Ort, start_time, duration_time,  metadata_dates FROM seminare WHERE Institut_id = '$inst_id' ");
	$view="inst";
} else {
	$user_id=$user->id;
	if ($perm->have_perm("admin")) {
		$db->query("SELECT seminare.Seminar_id, Name, Ort, start_time, duration_time,  metadata_dates FROM seminare WHERE Institut_id = '".$my_schedule_settings ["glb_inst_id"]."' ");
		$view="inst_admin";
	} else {
		$db->query("SELECT seminare.Seminar_id, Name, Ort, start_time, duration_time,  metadata_dates FROM seminare LEFT JOIN seminar_user USING (seminar_id) WHERE user_id = '$user_id'");
		$view="user";
	}
}
	
//richtiges Semester ausw&auml;hlen
if ($view=="inst") {
	if (!$instview_sem) {
	} else
		$tmp_sem_nr=$instview_sem;
} else {
	$k=1;
	foreach ($SEMESTER as $a) {
		if ($sem_name) {
			if (rawurldecode($sem_name) == $my_schedule_settings["glb_sem"])
				$tmp_sem_nr=$k;
		} else {
			if ($a["name"] == $my_schedule_settings["glb_sem"])
				$tmp_sem_nr=$k;
			$k++;
		}
	}
}

if (!$tmp_sem_nr) {
	if (time() < $VORLES_ENDE) {
		$tmp_sem_beginn=$SEM_BEGINN;
		$tmp_sem_ende=$SEM_ENDE;
		$tmp_sem_nr=$SEM_ID;
	} else {
		$tmp_sem_beginn=$SEM_BEGINN_NEXT;
		$tmp_sem_ende=$SEM_ENDE_NEXT;
		$tmp_sem_nr=$SEM_ID_NEXT;		
	}
} else {
	$tmp_sem_beginn=$SEMESTER[$tmp_sem_nr]["beginn"];
	$tmp_sem_ende=$SEMESTER[$tmp_sem_nr]["ende"];
}
	
//Array der Seminare erzeugen 
while ($db->next_record())
	{
	//Bestimmen, ob die Veranstaltung in dem Semester liegt, was angezeigt werden soll
	$use_this=FALSE;

	if (($db->f("start_time") <=$tmp_sem_beginn) &&(($tmp_sem_beginn <= ($db->f("start_time") + $db->f("duration_time"))) || ($db->f("duration_time") == -1)))
		$use_this=TRUE;
	
	
	$term_data=unserialize($db->f("metadata_dates"));

	if (($use_this) && (!$term_data["art"]) && (is_array($term_data["turnus_data"])))
		{
		//Zusammenbasteln Dozentenfeld
		$db2->query("SELECT Vorname, Nachname, username FROM auth_user_md5 LEFT JOIN seminar_user USING (user_id) WHERE status='dozent' AND Seminar_id ='".$db->f("Seminar_id")."'");
		$dozenten='';
		$i=1;
		while ($db2->next_record())
			{
			if ($i>1) $dozenten.=", ";
			$dozenten.="<a href =\"about.php?username=".$db2->f("username")."\">".$db2->f("Nachname")."</a>";
			$i++;
			}
		
		$i=0;
		foreach 	($term_data["turnus_data"] as $data)
			{
			//Patch fuer Problem mit alten Versionwn <=0.7 (Typ war falsch gesetzt), wird nur fuer rueckwaerts-Kompatibilitaet benoetigt
			settype ($data["start_stunde"], "integer");
			settype ($data["end_stunde"], "integer");
			settype ($data["start_minute"], "integer");
			settype ($data["start_minute"], "integer");
					
			//wichtiger Check, ob die Endzeit ueber den sichtbaren Bereich des Stundenplans hinauslaeuft, wenn ja wird row_span entsprechend angepasst
			if ($data["end_stunde"] >=$my_schedule_settings["glb_end_time"])
				{
				$tmp_row_span = ((($my_schedule_settings["glb_end_time"] - $data["start_stunde"])+1) *4)-1;
				$tmp_row_span = $tmp_row_span - ($data["start_minute"] / 15);
				}
			else 
				$tmp_row_span = (($data["end_stunde"] - $data["start_stunde"]) * 4) + ($data["end_minute"] / 15);
			
			
			//Dummy-Timestamps erzeugen. Der 5.8.2001 (ein Sonntag) wird als Grundlage verwendet.
			$start_time=mktime($data["start_stunde"], $data["start_minute"], 0, 8, (5+$data["day"]), 2001);
			$end_time=mktime($data["end_stunde"], $data["end_minute"], 0, 8, (5+$data["day"]), 2001);			

			$i++; //<pfusch>$i (fuer alle einzelnen Objekte eines Seminars) wird hier zur Kennzeichnung der einzelen Termine eines Seminars untereinander verwendet. Unten wird die letzte Stelle jeweils weggelassen. </pfusch>

			$my_sems[$db->f("Seminar_id").$i]=array("start_time_idx"=>$data["start_stunde"].($data["start_minute"] / 15).$data["day"], "start_time"=>$start_time, "end_time"=>$end_time, "name"=>htmlReady($db->f("Name")), "seminar_id"=>$db->f("Seminar_id").$i,  "ort"=>$db->f("Ort"), "row_span"=>$tmp_row_span, "dozenten"=>$dozenten, "personal_sem"=>FALSE);
			}
		}
	}

	
//Daten aus der Sessionvariable hinzufuegen
if ((is_array($my_personal_sems)) && (!$inst_id))
	foreach ($my_personal_sems as $mps)
	{
	//auch hier nochmal der Check
	if (date("G", $mps["ende_time"]) >=$my_schedule_settings["glb_end_time"])
		{
		$tmp_end_time = mktime($my_schedule_settings["glb_end_time"]+1, 00, 00, date ("n", $mps["start_time"]), date ("j", $mps["start_time"]), date ("Y", $mps["start_time"]));
		$tmp_row_span = ($tmp_end_time - $mps["start_time"]) /15/60;
		}
	else $tmp_row_span = ($mps["ende_time"] - $mps["start_time"])/15/60;

	//aus Sonntag=0 wird Sonntag=7, damit laesst's sich besser arbeiten *g
	$tmp_day=date("w", $mps["start_time"]);
	if ($tmp_day==0) $tmp_day=7;
	
	$my_sems[$mps["seminar_id"]]=array("start_time_idx"=>date("G", $mps["start_time"]).(date("i", $mps["start_time"]) / 15).$tmp_day, "start_time"=>$mps["start_time"], "end_time"=>$mps["ende_time"], "name"=>$mps["beschreibung"], "seminar_id"=>$mps["seminar_id"],  "ort"=>"", "row_span"=>$tmp_row_span, "dozenten"=>"", "personal_sem"=>TRUE);
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
	$n=$my_schedule_settings["glb_start_time"];
	for ($n; $n<$my_schedule_settings["glb_end_time"]+1; $n++)
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
if (!$print_view)
if ($perm->have_perm("admin")) {
?>
<tr>
	<td class="topic" width = "99%"colspan=<? echo $glb_colspan?>><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;<? if ($view=="user")  echo "Mein Stundenplan"; else echo "Veranstaltungs-Timetable" ?></b>
	</td>
	<td nowrap class="topic" align="right">Ansicht anpassen&nbsp; <a href="<? echo $PHP_SELF ?>?change_view=TRUE"><img src="pictures/pfeillink.gif" border=0></a>
	</td>
</tr>
<?
	}
else
	{
?>
<tr>
	<td class="topic" width = "99%"colspan=<? echo $glb_colspan+1?>><img src="pictures/meinesem.gif" border="0" align="texttop"><b>&nbsp;<? if ($view=="user")  echo "Mein Stundenplan"; else echo "Veranstaltungs-Timetable" ?></b>
	</td>
</tr>
<?
	}

if (!$print_view) {
?>
<tr>
	<td class="blank" colspan=<? echo $glb_colspan+1?>>&nbsp;
		<form action="<? echo $PHP_SELF ?>" method="POST">
		<blockquote>
		<?  if ($view=="user")  { ?>
		Der Stundenplan zeigt Ihnen alle regelm&auml;&szlig;igen Veranstaltungen eines Semesters. Um den Stundenplan auszudrucken, nutzen sie bitte die Druckfunktion ihres Browsers.<br /><br />
		<font size=-1>Wenn Sie weitere Veranstaltungen aus Stud.IP in ihren Stundenplan aufnehmen m&ouml;chten, nutzen Sie bitte die <a href = "sem_portal.php?view=Alle">Veranstaltungssuche</a>. <br>
		Ihre pers&ouml;nlichen Termine finden sie auf der <a href="kalender.php">Termin&uuml;bersicht</a>.</font>
		<?} else { ?>
		In der Veranstaltungs-Timetable sehen Sie alle Veranstaltungen der Einrichtung eines Semesters.<br />
		<br /><font size=-1>Angezeigtes Semester:&nbsp; 
			<select name="instview_sem">
			<?
				foreach ($SEMESTER as $key=>$val) {
					printf ("<option %s value=\"%s\">%s</option>\n", ($tmp_sem_nr == $key) ? "selected" : "", $key, $val["name"]);
				}
			?>
			</select>&nbsp; 
			<input type="IMAGE" value="change_instview_sem" src="pictures/buttons/uebernehmen-button.gif" border=0 value="&uuml;bernehmen" />
			<input type="HIDDEN" name="inst_id" value="<? echo $inst_id ?>" />
		<? } 
		if ($view !="user")
			printf ("&nbsp; <font size=-1><a target=\"_new\" href=\"%s?print_view=TRUE%s\">Druckansicht dieser Seite</a></font>", $PHP_SELF, ($inst_id) ? "&inst_id=".$inst_id : "");
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
	<td width="10%" align="center" class="rahmen_steelgraulight" >Zeit
	</td>
	<? if ($my_schedule_settings["glb_days"]["mo"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight" >Montag
	</td><?}
	if ($my_schedule_settings["glb_days"]["di"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight">Dienstag
	</td><?}
	if ($my_schedule_settings["glb_days"]["mi"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight">Mittwoch
	</td><?}
	if ($my_schedule_settings["glb_days"]["do"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight">Donnerstag
	</td><?}
	if ($my_schedule_settings["glb_days"]["fr"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight">Freitag
	</td><?}
	if ($my_schedule_settings["glb_days"]["sa"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight">Samstag
	</td><?}
	if ($my_schedule_settings["glb_days"]["so"]) {?>
	<td width="<?echo round (90/$glb_colspan)."%"?>" align="center" class="rahmen_steelgraulight">Sonntag
	</td><?}?>
</tr>
<?



//Aufbauen der eigentlichen Tabelle
$i=$my_schedule_settings["glb_start_time"];

for ($i; $i<$my_schedule_settings["glb_end_time"]+1; $i++)
	{
	$k=0;
	for ($k; $k<4; $k++)
		{
		if ($k==0) 
			{
			echo "<tr><td align=\"center\" class=\"rahmen_steelgraulight\" rowspan=4>"; 
			if ($i<10) echo "0";
			echo $i, ".00 Uhr</td>";
			}
		else echo "<tr>";
		$l=1;
		for ($l; $l<8; $l++)
			{
			//ausgeblendete Tage skippen
			if (($l==1) && (!$my_schedule_settings["glb_days"]["mo"] )) $l=2;
			if (($l==2) && (!$my_schedule_settings["glb_days"]["di"] )) $l=3;
			if (($l==3) && (!$my_schedule_settings["glb_days"]["mi"] )) $l=4;
			if (($l==4) && (!$my_schedule_settings["glb_days"]["do"] )) $l=5;
			if (($l==5) && (!$my_schedule_settings["glb_days"]["fr"] )) $l=6;
			if (($l==6) && (!$my_schedule_settings["glb_days"]["sa"] )) $l=7;
			if (($l==7) && (!$my_schedule_settings["glb_days"]["so"] )) $l=8;
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
						echo "<hr width=\"100%\">";
					$r++;
					echo "<font size=-1 ";
					if (!$print_view)
						echo "color=\"FFFFFF\"";
					echo ">", date ("H:i",  $my_sems[$cc["seminar_id"]]["start_time"]);
					if  ($my_sems[$cc["seminar_id"]]["start_time"] <> $my_sems[$cc["seminar_id"]]["end_time"]) 
						echo " - ",  date ("H:i",  $my_sems[$cc["seminar_id"]]["end_time"]);
					if ($my_sems[$cc["seminar_id"]]["ort"]) echo ",  ", $my_sems[$cc["seminar_id"]]["ort"];
					echo "</font></td></tr><tr><td class=\"blank\">";
					if (!$my_sems[$cc["seminar_id"]]["personal_sem"]) 
						{
						if ($view=="inst")
							echo  "<a href=\"details.php?sem_id=";						
						else
							echo  "<a href=\"seminar_main.php?auswahl=";
						echo substr($my_sems[$cc["seminar_id"]]["seminar_id"], 0, 32), "\"><font size=-1>";
						echo substr($my_sems[$cc["seminar_id"]]["name"], 0,50);
						if (strlen($my_sems[$cc["seminar_id"]]["name"])>50)
							echo "..."; 
						echo"</font></a>";
						}
					else
						{
						echo "<font size=-1>";					
						echo substr($my_sems[$cc["seminar_id"]]["name"], 0,50);
						if (strlen($my_sems[$cc["seminar_id"]]["name"])>50)
							echo "...";
						echo "</font>";
						}
					if ($my_sems[$cc["seminar_id"]]["dozenten"]) echo "<br><div align=\"right\"><font size=-1>", $my_sems[$cc["seminar_id"]]["dozenten"], "</font></div>";
					if ($my_sems[$cc["seminar_id"]]["personal_sem"]) echo "<div align=\"right\"><a href=\"",$PHP_SELF, "?cmd=delete&d_sem_id=",$my_sems[$cc["seminar_id"]]["seminar_id"], "\"><img border=0 src=\"./pictures/trash.gif\" alt=\"Dieses Feld aus der Auswahl l&ouml;schen\">&nbsp;</a></div>";
					}
				echo "</td></tr></table>";
				}
			if (!$cell_sem[$idx])  echo "class=\"steel1\"></td>"; 
			}
			}
			echo "</tr>\n";
		}
	}

	if ($print_view) {
		echo "<tr><td colspan=$glb_colspan><i><font size=-1>&nbsp; Erstellt am ",date("d.m.y", time())," um ", date("G:i", time())," Uhr.</font></i></td><td align=\"right\"><font size=-2><img src=\"pictures/logo2b.gif\"><br />&copy; ", date("Y", time())," v.$SOFTWARE_VERSION&nbsp; &nbsp; </font></td></tr></tr>";
		}
	else {
		}
	?>
	</td>
</tr>
<?
echo "</table></td></tr>";
?>
<tr>
	<td colspan=<? echo $glb_colspan+1?> class="blank">
		&nbsp; 
	</td>
</tr>
<?
if ((!$print_view) && (!$inst_id)) {
?>
<tr>
	<td colspan=<? echo $glb_colspan+1?> class="blank">
		&nbsp; 
	</td>
</tr>
<tr>
	<td colspan=<? echo $glb_colspan+1?> class="steelgraulight">
		<b>&nbsp;Eigene Veranstaltung eintragen:</b><br>
		<font size=-1>&nbsp;(Hier k&ouml;nnen sie Veranstaltungen, die nicht im Stud.IP System existieren oder andere eigene Ereignisse eintragen)</font><br>
		<form method="POST" action="<? echo $PHP_SELF ?>?cmd=insert">
			&nbsp;Wochentag:
			<select name="tag">
				<option>Montag</option>
				<option>Dienstag</option>
				<option>Mittwoch</option>
				<option>Donnerstag</option>
				<option>Freitag</option>
				<option>Samstag</option>
				<option>Sonntag</option>				
			</select>
			Beginn: 
			<?	    
	   		echo"<select name=\"start_stunde\">";
	   		for ($i=$my_schedule_settings["glb_start_time"]; $i<=$my_schedule_settings["glb_end_time"]; $i++)
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
	    		echo"</select> Uhr";
	    		?>
			Ende:
			<?	    
	   		echo"<select name=\"ende_stunde\">";
	   		for ($i=$my_schedule_settings["glb_start_time"]; $i<=$my_schedule_settings["glb_end_time"]; $i++)
		  		{
		  		if ($i==9) echo "<option selected value=".$i.">".$i."</option>";
		       		else echo "<option value=".$i.">".$i."</option>";
		  		}
	    		echo"</select>";
	    		echo"<select name=\"ende_minute\">";
	     		for ($i=0; $i<=45; $i=$i+15)
		  		{
		  		if ($i==0) echo "<option selected value=".$i.">0".$i."</option>";
		  		elseif ($i==45) echo "<option selected value=".$i.">".$i."</option>";
		       		else echo "<option value=".$i.">".$i."</option>";
		  		}
	    		echo"</select> Uhr";
	    		?>
	    		Beschreibung:
	    		<input name="beschreibung" type="text" size=40 maxlength=255>
	    		<input name="submit" type="submit" value="Eintragen">
		</form>
	</td>
</tr>
<tr>
	<td colspan=<? echo $glb_colspan+1?> class="blank">
		&nbsp; 
	</td>
</tr>

<?
}
ob_end_flush(); //end outputbuffering 

// Save data back to database.
page_close();
if (!$print_view) {
?>
</td></tr></table>
<? } ?>
</body>
</html>
<!-- $Id$ -->