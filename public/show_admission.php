<?php
/*
show_admission.php - Instituts-Mitarbeiter-Verwaltung von Stud.IP
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>

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
$perm->check("admin");


// Set this to something, just something different...
$hash_secret = "trubatik";

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
$CURRENT_PAGE = _("Teilnehmerbeschränkte Veranstaltungen");

// Start of Output
include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head
include ('lib/include/links_admin.inc.php');  //Linkleiste fuer admins

require_once('config.inc.php'); //Grunddaten laden
require_once('lib/visual.inc.php'); //htmlReady
require_once('lib/classes/StudipAdmissionGroup.class.php'); //htmlReady

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;
$sem_condition = '';
$admission_condition = array();

if(isset($_REQUEST['choose_institut_x'])){
	if(isset($_REQUEST['select_sem'])){
		$_default_sem = $_REQUEST['select_sem'];
	}
	$_SESSION['show_admission']['check_admission'] = isset($_REQUEST['check_admission']);
	$_SESSION['show_admission']['check_prelim'] = isset($_REQUEST['check_prelim']);
}
/*
if(!$_SESSION['show_admission']['check_admission'] && !$_SESSION['show_admission']['check_prelim']){
	$_SESSION['show_admission']['check_admission'] = true;
}
*/
if ($_default_sem){
	$semester =& SemesterData::GetInstance();
	$one_semester = $semester->getSemesterData($_default_sem);
	if($one_semester["beginn"]){
		$sem_condition = "AND seminare.start_time <=".$one_semester["beginn"]." AND (".$one_semester["beginn"]." <= (seminare.start_time + seminare.duration_time) OR seminare.duration_time = -1) ";
	}
}
if($_SESSION['show_admission']['check_admission']) $admission_condition[] = "admission_type > 0";
//else $admission_condition[] = "admission_type = 0";
if($_SESSION['show_admission']['check_prelim']) $admission_condition[] = "admission_prelim = 1";
else $admission_condition[] = "admission_prelim <> 1";
$seminare_condition = "AND (" . join(" AND ", $admission_condition) . ") " .  $sem_condition;
if($perm->have_perm('root')){
	$db->query("SELECT COUNT(*) FROM seminare WHERE 1 $seminare_condition");
	$db->next_record();
	$_my_inst['all'] = array("name" => _("alle") , "num_sem" => $db->f(0));
	$db->query("SELECT a.Institut_id,a.Name, 1 AS is_fak, count(seminar_id) AS num_sem FROM Institute a
	LEFT JOIN seminare ON(seminare.Institut_id=a.Institut_id $seminare_condition  ) WHERE a.Institut_id=fakultaets_id GROUP BY a.Institut_id ORDER BY is_fak,Name,num_sem DESC");
} else {
	$db->query("SELECT a.Institut_id,b.Name, IF(b.Institut_id=b.fakultaets_id,1,0) AS is_fak,count(seminar_id) AS num_sem FROM user_inst a LEFT JOIN Institute b USING (Institut_id)
	LEFT JOIN seminare ON(seminare.Institut_id=b.Institut_id $seminare_condition  )	WHERE a.user_id='$user->id' AND a.inst_perms='admin' GROUP BY a.Institut_id ORDER BY is_fak,Name,num_sem DESC");
}
while($db->next_record()){
	$_my_inst[$db->f("Institut_id")] = array("name" => $db->f("Name"), "is_fak" => $db->f("is_fak"), "num_sem" => $db->f("num_sem"));
	if ($db->f("is_fak")){
		$db2->query("SELECT a.Institut_id, a.Name,count(seminar_id) AS num_sem FROM Institute a
		LEFT JOIN seminare ON(seminare.Institut_id=a.Institut_id $seminare_condition  ) WHERE fakultaets_id='" . $db->f("Institut_id") . "' AND a.Institut_id!='" .$db->f("Institut_id") . "'
		GROUP BY a.Institut_id ORDER BY a.Name,num_sem DESC");
		$num_inst = 0;
		while ($db2->next_record()){
			if(!$_my_inst[$db2->f("Institut_id")]){
				++$num_inst;
			}
			$_my_inst[$db2->f("Institut_id")] = array("name" => $db2->f("Name"), "is_fak" => 0 , "num_sem" => $db2->f("num_sem"));
		}
		$_my_inst[$db->f("Institut_id")]["num_inst"] = $num_inst;
	}
}

if (!is_array($_my_inst)){
	$_msg[] = array("info", sprintf(_("Sie wurden noch keinen Einrichtungen zugeordnet. Bitte wenden Sie sich an einen der zust&auml;ndigen %sAdministratoren%s."), "<a href=\"impressum.php?view=ansprechpartner\">", "</a>"));
} else {
	$_my_inst_arr = array_keys($_my_inst);
	if(!$_SESSION['show_admission']['institut_id']){
		$_SESSION['show_admission']['institut_id'] = $_my_inst_arr[0];
	}
	if($_REQUEST['institut_id']){
		$_SESSION['show_admission']['institut_id'] = ($_my_inst[$_REQUEST['institut_id']]) ? $_REQUEST['institut_id'] : $_my_inst_arr[0];
	}
}

if(isset($_REQUEST['group_sem_x']) && count($_REQUEST['gruppe']) > 1){
	$group_obj = new StudipAdmissionGroup();
	foreach($_REQUEST['gruppe'] as $sid){
		$group_obj->addMember($sid);
	}
	
}

?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
<?
if(is_object($group_obj)){
	?>
	<tr>
		<form action="<?=$PHP_SELF?>" name="Formular" method="post">
		<td class="blank" width="100%">
		<div class="steel1" style="margin:10px;padding:5px;border: 1px solid;">
		<div style="font-weight:bold;"><?=_("Gruppierte Veranstaltungen bearbeiten")?></div>
		<div style="font-size:10pt;">
		<?=_("Gruppierte Veranstaltungen müssen ein identisches Anmeldeverfahren benutzen.")?>
		<?=_("Alle Einstellungen die sie an dieser Stelle vornehmen können, werden in allen Veranstaltungen dieser Gruppe gesetzt, wenn sie die entsprechende Option auswählen.")?>
		<br><br>
		<b><?=_("Veranstaltungen in dieser Gruppe:")?></b>
		<ol>
		<?foreach($group_obj->members as $member){?>
			<li><?=htmlReady($member->getName())?></li>
		<?}?>
		</ol>
		<ul style="list-style: none; margin:0px;padding:0px;">
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("Name der Gruppe (optional):")?></span>
		<input type="text" name="admission_group_name" value="<?=htmlReady($group_obj->getValue('name'))?>" size="80" >
		</li>
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("Typ der Gruppe:")?></span>
		<input style="vertical-align:top" type="radio" name="admission_group_status" <?=($group_obj->getValue('status') == 0 ? 'checked' : '')?> value="0">
		&nbsp;
		<?=_("Eintrag in einer Veranstaltung und einer Warteliste")?>
		&nbsp;
		<input style="vertical-align:top" type="radio" name="admission_group_status" <?=($group_obj->getValue('status') == 1 ? 'checked' : '')?> value="1">
		&nbsp;
		<?=_("Eintrag nur in einer Veranstaltung")?>
		</li>
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("Anmeldeverfahren der Gruppe:")?></span>
		<input style="vertical-align:top" type="radio" name="admission_group_type" <?=($group_obj->getUniqueMemberValue('admission_type') == 2 ? 'checked' : '')?> value="2">
		&nbsp;
		<?=_("chronologische Anmeldung")?>
		&nbsp;
		<input style="vertical-align:top" type="radio" name="admission_group_type" <?=($group_obj->getUniqueMemberValue('admission_type') == 1 ? 'checked' : '')?> value="1">
		&nbsp;
		<?=_("Losverfahren")?>
		</li>
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("Startdatum für Anmeldungen:")?></span>
		<input style="vertical-align:top" type="checkbox" name="admission_change_starttime" value="1">
		&nbsp;
		<?=_("ändern")?>
		&nbsp;
		<?
		$group_admission_start_date = $group_obj->getUniqueMemberValue('admission_starttime');
		?>
		<input type="text" style="vertical-align:middle" name="adm_s_tag" size=2 maxlength=2 value="<? if (!is_null($group_admission_start_date)) echo date("d",$group_admission_start_date); else echo _("tt") ?>">.
		<input type="text" style="vertical-align:middle" name="adm_s_monat" size=2 maxlength=2 value="<? if (!is_null($group_admission_start_date)) echo date("m",$group_admission_start_date); else echo _("mm") ?>">.
		<input type="text" style="vertical-align:middle" name="adm_s_jahr" size=4 maxlength=4 value="<? if (!is_null($group_admission_start_date)) echo date("Y",$group_admission_start_date); else echo _("jjjj") ?>">
		<?=_("um");?>
		&nbsp;<input type="text" style="vertical-align:middle"  name="adm_s_stunde" size=2 maxlength=2 value="<? if (!is_null($group_admission_start_date)) echo date("H",$group_admission_start_date); else echo "00" ?>">:
		<input type="text" style="vertical-align:middle"  name="adm_s_minute" size=2 maxlength=2 value="<? if (!is_null($group_admission_start_date)) echo date("i",$group_admission_start_date); else  echo "00" ?>">&nbsp;<?=_("Uhr");?>
		<?=Termin_Eingabe_javascript(20,0,(!is_null($group_admission_start_date) ? $group_admission_start_date : 0));
		echo '&nbsp;(' . _("aktuelle Einstellung:") . '&nbsp;' . (!is_null($group_admission_start_date) ? _("identisches Datum in allen Veranstaltungen") : _("unterschiedliches Datum in allen Veranstaltungen") ) . ')';
		?>
		</li>
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("Enddatum für Anmeldungen:")?></span>
		<input style="vertical-align:top" type="checkbox" name="admission_change_endtime_sem" value="1">
		&nbsp;
		<?=_("ändern")?>
		&nbsp;
		<?
		$group_admission_end_date = $group_obj->getUniqueMemberValue('admission_endtime_sem');
		?>
		<input style="vertical-align:middle" type="text" name="adm_e_tag" size=2 maxlength=2 value="<? if (!is_null($group_admission_end_date)) echo date("d",$group_admission_end_date); else echo _("tt") ?>">.
		<input style="vertical-align:middle"  type="text" name="adm_e_monat" size=2 maxlength=2 value="<? if (!is_null($group_admission_end_date)) echo date("m",$group_admission_end_date); else echo _("mm") ?>">.
		<input style="vertical-align:middle" type="text" name="adm_e_jahr" size=4 maxlength=4 value="<? if (!is_null($group_admission_end_date)) echo date("Y",$group_admission_end_date); else echo _("jjjj") ?>">
		<?=_("um");?>
		&nbsp;<input style="vertical-align:middle" type="text" name="adm_e_stunde" size=2 maxlength=2 value="<? if (!is_null($group_admission_end_date)) echo date("H",$group_admission_end_date); else echo "00" ?>">:
		<input style="vertical-align:middle" type="text" name="adm_e_minute" size=2 maxlength=2 value="<? if (!is_null($group_admission_end_date)) echo date("i",$group_admission_end_date); else  echo "00" ?>">&nbsp;<?=_("Uhr");?>
		<?=Termin_Eingabe_javascript(21,0,(!is_null($group_admission_end_date) ? $group_admission_end_date : 0));
		echo '&nbsp;(' . _("aktuelle Einstellung:") . '&nbsp;' . (!is_null($group_admission_end_date) ? _("identisches Datum in allen Veranstaltungen") : _("unterschiedliches Datum in allen Veranstaltungen") ) . ')';
		?>
		</li>
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("Ende Kontingente / Losdatum:")?></span>
		<input style="vertical-align:top" type="checkbox" name="admission_change_endtime" value="1">
		&nbsp;
		<?=_("ändern")?>
		&nbsp;
		<?
		$group_admission_end = $group_obj->getUniqueMemberValue('admission_endtime');
		?>
		<input type="text" style="vertical-align:middle" name="adm_tag" size=2 maxlength=2 value="<? if (!is_null($group_admission_end)) echo date("d",$group_admission_end); else echo _("tt") ?>">.
		<input type="text" style="vertical-align:middle" name="adm_monat" size=2 maxlength=2 value="<? if (!is_null($group_admission_end)) echo date("m",$group_admission_end); else echo _("mm") ?>">.
		<input type="text" style="vertical-align:middle" name="adm_jahr" size=4 maxlength=4 value="<? if (!is_null($group_admission_end)) echo date("Y",$group_admission_end); else echo _("jjjj") ?>">
		<?=_("um");?>
		&nbsp;<input type="text" style="vertical-align:middle" name="adm_stunde" size=2 maxlength=2 value="<? if (!is_null($group_admission_end)) echo date("H",$group_admission_end); else echo "00" ?>">:
		<input type="text" style="vertical-align:middle" name="adm_minute" size=2 maxlength=2 value="<? if (!is_null($group_admission_end)) echo date("i",$group_admission_end); else  echo "00" ?>">&nbsp;<?=_("Uhr");?>
		<?=Termin_Eingabe_javascript(22,0,(!is_null($group_admission_end) ? $group_admission_end : 0));
		echo '&nbsp;(' . _("aktuelle Einstellung:") . '&nbsp;' . (!is_null($group_admission_end) ? _("identisches Datum in allen Veranstaltungen") : _("unterschiedliches Datum in allen Veranstaltungen") ) . ')';
		?></li>
		<li style="margin-top:5px;">
		<span style="display:block;float:left;width:200px;"><?=_("max. Teilnehmer:")?></span>
		<input style="vertical-align:top" type="checkbox" name="admission_change_turnout" value="1">
		&nbsp;
		<?=_("ändern")?>
		&nbsp;
		<input style="vertical-align:middle" type="text" size="3" value="<?=$group_obj->getUniqueMemberValue('admission_turnout')?>">
		<?
		echo '&nbsp;(' . _("aktuelle Einstellung:") . '&nbsp;' . (!is_null($group_obj->getUniqueMemberValue('admission_turnout')) ? _("identische Anzahl in allen Veranstaltungen") : _("unterschiedliche Anzahl in allen Veranstaltungen") ) . ')';
		?>
		</li>
		<li style="margin-top:5px;">
		<span style="padding-left:200px;">
		<?=makeButton('uebernehmen', 'input', _("Einstellungen übernehmen"), 'admissiongroupchange')?>
		&nbsp;
		<?=makeButton('abbrechen', 'input', _("Eingabe abbrechen"), 'admissiongroupcancel')?>
		</span>
		</li>
		</ul>
		</div>
		</div>
	<?
} else {
	if (is_array($_my_inst)) {
	?>
		<tr>
			<form action="<?=$PHP_SELF?>" method="post">
			<td class="blank" width="100%" >
				<div style="font-weight:bold;font-size:10pt;margin:10px;">
				<?=_("Bitte w&auml;hlen Sie eine Einrichtung aus:")?>
				</div>
				<div style="margin-left:10px;">
				<select name="institut_id" style="vertical-align:middle;">
					<?
					reset($_my_inst);
					while (list($key,$value) = each($_my_inst)){
						printf ("<option %s value=\"%s\" style=\"%s\">%s (%s)</option>\n",
								($key == $_SESSION['show_admission']['institut_id']) ? "selected" : "" , $key,($value["is_fak"] ? "font-weight:bold;" : ""),
								htmlReady($value["name"]), $value["num_sem"]);
						if ($value["is_fak"]){
							$num_inst = $value["num_inst"];
							for ($i = 0; $i < $num_inst; ++$i){
								list($key,$value) = each($_my_inst);
								printf("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s (%s)</option>\n",
									($key == $_SESSION['show_admission']['institut_id']) ? "selected" : "", $key,
									htmlReady($value["name"]), $value["num_sem"]);
							}
						}
					}
					?>
					</select>&nbsp;
					<?=SemesterData::GetSemesterSelector(array('name'=>'select_sem', 'style'=>'vertical-align:middle;'), $_default_sem)?>
					<?=makeButton("auswaehlen","input",_("Einrichtung auswählen"), "choose_institut")?>
				</div>
				<div style="font-size:10pt;margin:10px;">
				<b><?=_("Angezeigte Veranstaltungen einschränken:")?></b>
				<span style="margin-left:10px;font-size:10pt;">
				<input type="checkbox" name="check_admission" <?=$_SESSION['show_admission']['check_admission'] ? 'checked' : ''?> value="1" style="vertical-align:middle;">&nbsp;<?=_("Anmeldeverfahren")?>
				<input type="checkbox" name="check_prelim" <?=$_SESSION['show_admission']['check_prelim'] ? 'checked' : ''?> value="1" style="vertical-align:middle;">&nbsp;<?=_("vorläufige Teilnahme")?>
				</span>
				</div>
			</td>
			</form>
		</tr>
<?}?>
	<tr>
		<td class="blank">
<?
		//check if grouping / ungrouping
		//first show warning
		if (isset($group) && (!$real) && ($ALLOW_GROUPING_SEMINARS)) {
			printf("<form action=\"%s\" method=\"post\">",$PHP_SELF);
			printf("<table border=0 cellspacing=0 cellpadding=0 width=\"99%%\">");
			if ($group == "group") {
			  my_info(_("Beachten Sie, dass einE TeilnehmerIn bereits f&uuml;r mehrere der zu gruppierenden Veranstaltungen eingetragen sein kann. Das System nimmt daran keine &Auml;nderungen vor!"));
				my_info(_("Beachtem Sie au&szlig;erdem, dass nur Veranstaltungen mit dem chronologischen Anmeldeverfahren gruppiert werden k&ouml;nnen."));
			  my_info(_("Wollen Sie die ausgew&auml;hlten Veranstaltungen gruppieren?"));
			} else {
			  my_info(_("Beachten Sie, dass f&uuml;r bereits eingetragene / auf der Warteliste stehende TeilnehmerInnen keine &Auml;nderungen vorgenommen werden."));
			  my_info(_("Wollen Sie die Gruppierung f&uuml;r die ausgew&auml;hlte Gruppe aufl&ouml;sen?"));
			}
			echo "<tr><td>\n";
			printf("&nbsp;&nbsp;<input %s %s type=\"image\" border=\"0\" style=\"vertical-align:middle;\">\n",makeButton("ja2","src"),tooltip(_("&Auml;nderung durchf&uuml;hren")));
			print("<input type=\"hidden\" name=\"real\" value=\"1\">\n");
			printf("<input type=\"hidden\" name=\"group\" value=\"%s\">\n",$group);
			printf("<input type=\"hidden\" name=\"institut_id\" value=\"%s\">\n",$institut_id);
			printf("<input type=\"hidden\" name=\"seminarid\" value=\"%s\">\n",$seminarid);
			if (($group == "group") && ($_REQUEST['gruppe'])) {
				foreach ($_REQUEST['gruppe'] as $element) {
					printf("<input type=\"hidden\" name=\"gruppe[]\" value=\"%s\">\n",$element);
				}
			}
			printf("<a href=\"show_admission.php?institut_id=%s\"><img %s %s type=\"image\" border=\"0\" style=\"vertical-align:middle;\"></a>\n",$institut_id,makeButton("nein","src"),tooltip(_("Änderung NICHT durchführen")));
			print("</tr></td></table></form>");
		} elseif ($ALLOW_GROUPING_SEMINARS) {
			//execute order
			if ($group=="group") {
				if (isset($_REQUEST['gruppe'])) {
					if (sizeof($_REQUEST['gruppe']) <= 1) {
						printf("<table border=0 cellspacing=0 cellpadding=0 width=\"99%%\">");
						my_error(_("Sie m&uuml;ssen mindestens zwei Veranstaltungen auswählen, wenn Sie eine Gruppe erstellen wollen!"));
						printf("</table>");
					} else {
						$grouping = TRUE;
						foreach ($_REQUEST['gruppe'] as $element) {
							$db->query("SELECT admission_type, Name FROM seminare WHERE Seminar_id = '$element';");
							$db->next_record();
							if ($db->f("admission_type") != 2) {
								printf("<table border=0 cellspacing=0 cellpadding=0 width=\"99%%\">");
								my_error(sprintf(_("Die Veranstaltung *%s* muss auf chronologisches Anmeldeverfahren umgestellt werden, sonst ist keine Gruppierung möglich!"), $db->f("Name")));
								printf("</table>");
								$grouping = FALSE;
							}
						}
						if ($grouping) {
							//create group-id
							$n_group = md5(uniqid($hash_secret));
							foreach ($_REQUEST['gruppe'] as $element) {
								$query = "UPDATE seminare SET admission_group='$n_group' WHERE Seminar_id = '$element'";
								$db->query($query);
							}
						}
					}
				}
			} elseif ($group=="ungroup") {
				$query="UPDATE seminare SET admission_group=NULL WHERE admission_group='$seminarid'";
				$db->query($query);
			}
		}

		if ($_SESSION['show_admission']['institut_id'] == "all"  && $perm->have_perm("root"))
			$query = "SELECT * FROM seminare WHERE 1 $seminare_condition ORDER BY admission_group DESC, start_time DESC, Name";
		else
			$query = "SELECT * FROM seminare LEFT JOIN seminar_inst USING (Institut_id) WHERE seminar_inst.institut_id = '{$_SESSION['show_admission']['institut_id']}' $seminare_condition GROUP BY seminare.Seminar_id ORDER BY admission_group DESC, start_time DESC, Name";

		$db->query($query);
		$tag = 0;
		if ($db->nf()) {
			print ("<table width=\"99%\" border=0 cellspacing=2 cellpadding=2>");
			print ("<tr style=\"font-size:80%\">");
			if ($ALLOW_GROUPING_SEMINARS) {
				echo "<th width=\"1%\">". _("Gruppieren") ."</th>";
			}
			echo "<th width=\"25%\">". _("Veranstaltung") ."</th>";
			echo "<th width=\"10%\">". _("Status") ."</th>";
			echo "<th width=\"10%\">". _("Kontingent Teilnehmer") ."</th>";
			echo "<th width=\"10%\">". _("Max. Teilnehmer") ."</th>";
			echo "<th width=\"10%\">". _("Anmelde & Akzeptiertliste") ."</th>";
			echo "<th width=\"10%\">". _("Warteliste") ."</th>";
			echo "<th width=\"10%\">". _("Losdatum / Ende Kontingente") ."</th>";
			echo "<th width=\"20%\">". _("Anmeldezeitraum") ."</th>";
			echo "</tr>";
		} elseif ($institut_id) {
			print ("<table width=\"99%\" border=0 cellspacing=2 cellpadding=2>");
			parse_msg ("info§"._("Im gew&auml;hlten Bereich existieren keine teilnahmebeschr&auml;nkten Veranstaltungen")."§", "§", "steel1",2, FALSE);
		}

		if ($db->nf()) printf("<form action=\"%s\" method=\"post\">\n",$PHP_SELF);
	  	while ($db->next_record()) {
			$seminar_id = $db->f("Seminar_id");
	  		$query2 = "SELECT * FROM seminar_user WHERE seminar_id='$seminar_id' AND admission_studiengang_id != ''";
			$db2->query($query2);
			$teilnehmer = $db2->num_rows();
	  		$cssSw->switchClass();
			$quota = $db->f("admission_turnout");
			$count2 = 0;
			$count3 = 0;
			$query2 = "SELECT status, count(*) AS count2 FROM admission_seminar_user WHERE seminar_id='$seminar_id' AND (status='claiming' OR status='accepted') GROUP BY status";
			$db2->query($query2);
			if ($db2->next_record()) {
				$count2 = $db2->f("count2");
			}
			$query2 = "SELECT status, count(*) AS count3 FROM admission_seminar_user WHERE seminar_id='$seminar_id' AND status='awaiting' GROUP BY status";
			$db2->query($query2);
			if ($db2->next_record()) {
				$count3 = $db2->f("count3");
			}
			$datum = $db->f("admission_endtime");
			$status = array();
			if($db->f('admission_type') == 2) $status[] = _("Chronologisch");
			if($db->f('admission_type') == 1) $status[] = _("Losverfahren");
			if($db->f('admission_prelim'))  $status[] = _("vorläufig");
			echo "<tr>";
			if ($ALLOW_GROUPING_SEMINARS) {
					if (!$db->f("admission_group") ) { //wenn keiner Gruppe zugeordnet, dann cechkbox ausgeben
						printf("<td class=\"%s\" align=\"center\">",$cssSw->getClass());
						unset($last_group);
						printf("<input type=\"checkbox\" name=\"gruppe[]\" value=\"%s\">",$db->f("Seminar_id"));
						echo '</td>';
					} else {
						if($db->f("admission_group") != $last_group) {
							unset($last_group);
						}
						if (!isset($last_group)) { //Wenn erstes "Mitglied" einer Gruppe, dann Muelleimer ausgeben
							$last_group = $db->f("admission_group");
							$group_obj = new StudipAdmissionGroup($last_group);
							echo '<td class="'.$cssSw->getClass().'" style="border:1px solid;" align="center" valign="middle" rowspan="'.$group_obj->getNumMembers().'">';
							printf("<a title=\"%s\" href=\"show_admission.php?group_id=%s&cmd=edit\">
									<img src=\"".$GLOBALS['ASSETS_URL']."images/edit_transparent.gif\" border=\"0\"></a>"
									,_("Gruppe bearbeiten"), $db->f("admission_group"));
							echo '</td>';
						}
					}
				
			}
			printf ("<td class=\"%s\">
			<a title=\"%s\" href=\"seminar_main.php?auswahl=%s&redirect_to=teilnehmer.php\">
					<font size=\"-1\">%s%s</font>
					</a></td>
					<td class=\"%s\" align=\"center\">
					<a title=\"%s\" href=\"admin_admission.php?select_sem_id=%s\"><font size=\"-1\">%s</font></a></td>
					<td class=\"%s\" align=\"center\"><font size=\"-1\">%s</font></td>
					<td class=\"%s\" align=\"center\"><font size=\"-1\">%s</font></td>
					<td class=\"%s\" align=\"center\"><font size=\"-1\">%s</font></td>
					<td class=\"%s\" align=\"center\"><font size=\"-1\">%s</font></td>
					<td class=\"%s\" align=\"left\" nowrap><font size=\"-1\">%s</font></td>",
					$cssSw->getClass(),
					_("Teilnehmerliste aufrufen"),
					$db->f("Seminar_id"),
					htmlready(substr($db->f("Name"), 0, 50)), (strlen($db->f("Name"))>50) ? "..." : "",
					$cssSw->getClass(),
					_("Zugangsbeschränkungen aufrufen"),
					$db->f("Seminar_id"),
					join('/', $status),
					$cssSw->getClass(),
					$teilnehmer,
					$cssSw->getClass(),
					$quota,
					$cssSw->getClass(),
					$count2,
					$cssSw->getClass(),
					$count3,
					($datum != -1) ? ($datum < time() ? "steelgroup4" : "steelgroup1") : $cssSw->getClass(),
					($datum != -1) ? date("d.m.Y, G:i", $datum) : "");
			if (($db->f("admission_endtime_sem") != -1)  || ($db->f("admission_starttime") != -1)) {  // last tabel-data: "Anmeldeverfahren"
				$class = "";  //we have to parse the correct color for the background
				if ($db->f("admission_starttime") != -1) {
					if ($db->f("admission_starttime") > time()) $class = "steelgroup1";
						else $class="steelgroup4";
				}
				if ($db->f("admission_endtime_sem") != -1) {
					if ($db->f("admission_endtime_sem") < time()) $class = "steelgroup1";
						else if($class != "steelgroup1") $class = "steelgroup4";
				}
				// print out table-data
				printf ("<td class=\"%s\" align=\"left\" nowrap><font size=\"-1\">", $class);
				if ($db->f("admission_starttime") != -1) echo _("Start:")." ".date("d.m.Y, G:i", $db->f("admission_starttime"))." <br/>";
				if ($db->f("admission_endtime_sem") != -1) echo _("Ende:")." ".date("d.m.Y, G:i", $db->f("admission_endtime_sem"));
				echo "</font></td>";
			} else {
				printf("<td class=\"%s\" align=\"center\"></td>", $cssSw->getClass());
			}
			print ("</tr>");
		}

		if ($db->nf() && $ALLOW_GROUPING_SEMINARS) {
			echo '<tr><td align="left">'. "\n";
			echo makeButton("gruppieren", 'input', _("Markierte Veranstaltungen gruppieren"), 'group_sem');
			echo "</form>\n";
			echo "</td></tr>\n";
		}
		echo "</table>";
}
?>
<br>&nbsp;
</td>
</tr>
</table>
<?php
include ('lib/include/html_end.inc.php');
page_close();
 // <!-- $Id$ -->
 ?>