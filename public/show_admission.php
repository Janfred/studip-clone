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

include ("seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("html_head.inc.php"); // Output of html head
include ("header.php");   // Output of Stud.IP head
include ("links_admin.inc.php");  //Linkleiste fuer admins

require_once("config.inc.php"); //Grunddaten laden
require_once("visual.inc.php"); //htmlReady

$db=new DB_Seminar;
$db2=new DB_Seminar;
$db3=new DB_Seminar;

?>
<table border=0 bgcolor="#000000" align="center" cellspacing=0 cellpadding=0 width=100%>
	<tr>
		<td class="topic">&nbsp;<b>
		<?
		echo _("Teilnehmerbeschr&auml;nkte Veranstaltungen");
		?></b>
		</td>
	</tr>
	<tr>
		<form action="<?=$PHP_SELF?>" method="post">
		<td class="blank" width="100%" >
			<br />
			<div style="font-weight:bold;font-size:10pt;margin-left:10px;">
			<?=_("Bitte w&auml;hlen Sie eine Einrichtung aus:")?>
			</div>
			<div style="margin-left:10px;">
			<select name="institut_id" style="vertical-align:middle;">
				<?
				if ($perm->have_perm("root"))
					printf ("<option %s value=\"%s\"> %s</option>", (($institut_id == "all") || (!$institut_id)) ? "selected" :"", "all", "alle");

				if ($perm->have_perm("root"))
					$db3->query("SELECT Name,Institut_id,1 AS is_fak,'admin' AS inst_perms FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name");
				elseif ($perm->have_perm("admin"))
					$db3->query("SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user->id' AND inst_perms = 'admin') ORDER BY is_fak,Name");

				while ($db3->next_record()) {
					printf ("<option %s style=\"%s\" value=\"%s\"> %s</option>", $db3->f("Institut_id") == $institut_id ? "selected" : "",
						($db3->f("is_fak")) ? "font-weight:bold;" : "", $db3->f("Institut_id"), htmlReady(my_substr($db3->f("Name"),0,60)));
					if ($db3->f("is_fak") && $db3->f("inst_perms") == "admin"){
						$db2->query("SELECT a.Institut_id, a.Name FROM Institute a
									 WHERE fakultaets_id='" . $db3->f("Institut_id") . "' AND a.Institut_id!='" .$db3->f("Institut_id") . "' ORDER BY Name");
						while($db2->next_record()){
							printf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>", $db2->f("Institut_id") == $institut_id ? "selected" : "",
								$db2->f("Institut_id"), htmlReady(my_substr($db2->f("Name"),0,60)));
						}
					}
				}
				?>
				</select>&nbsp;
				<input <?=makeButton("auswaehlen","src")?> <?=tooltip(_("Einrichtung auswÃ¤hlen"))?> type="image" border="0" style="vertical-align:middle;">
				<br>&nbsp;
			</div>
		</td>
		</form>
	</tr>
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
			printf("<a href=\"show_admission.php?institut_id=%s\"><img %s %s type=\"image\" border=\"0\" style=\"vertical-align:middle;\"></a>\n",$institut_id,makeButton("nein","src"),tooltip(_("Ã„nderung NICHT durchfuehren")));
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

		if ((($institut_id == "all") || (!$institut_id)) && ($perm->have_perm("root")))
		$query = "SELECT * FROM seminare WHERE admission_type > 0 OR admission_starttime > ". time() ."  OR admission_endtime_sem > -1 OR (admission_starttime <= ". time(). " AND admission_starttime > 0) OR (admission_prelim = 1) ORDER BY admission_group DESC, start_time DESC, Name";
      else
	$query = "SELECT * FROM seminare LEFT JOIN seminar_inst USING (Institut_id) WHERE (admission_type > 0 OR admission_starttime > ".time()." OR admission_endtime_sem > -1 OR (admission_starttime <= ".time()." AND admission_starttime > 1) OR (admission_prelim = 1)) AND seminar_inst.institut_id = '$institut_id' GROUP BY seminare.Seminar_id ORDER BY admission_group DESC, start_time DESC, Name";

		$db->query($query);
		$tag = 0;
		if ($db->nf()) {
			print ("<table width=\"99%\" border=0 cellspacing=0 cellpadding=2>");
			print ("<tr>");
			echo "<td width=\"3%\"></td>";
			if ($ALLOW_GROUPING_SEMINARS) {
				echo "<th width=\"5%\">". _("Gruppieren") ."</th>";
				echo "<th width=\"1%\"></th>";
			}
			echo "<th width=\"25%\">". _("Veranstaltung") ."</th>";
			echo "<th width=\"8%\">". _("Teilnehmer") ."</th>";
			echo "<th width=\"8%\">". _("Max. Teilnehmer") ."</th>";
			echo "<th width=\"8%\">". _("Anmelde & Akzeptiertliste") ."</th>";
			echo "<th width=\"8%\">". _("Warteliste") ."</th>";
			echo "<th width=\"15%\">". _("Enddatum Kontingente") ."</th>";
			echo "<th width=\"20%\">". _("Anmeldezeitraum") ."</th>";
			echo "</tr>";
		} elseif ($institut_id) {
			print ("<table width=\"99%\" border=0 cellspacing=0 cellpadding=2>");
			parse_msg ("info§"._("Im gew&auml;hlten Bereich existieren keine teilnahmebeschr&auml;nkten Veranstaltungen")."§", "§", "steel1",2, FALSE);
		}

		if ($db->nf()) printf("<form action=\"%s\" method=\"post\">\n",$PHP_SELF);
	  	while ($db->next_record()) {
			$seminar_id = $db->f("Seminar_id");
	  		$query2 = "SELECT * FROM seminar_user WHERE seminar_id='$seminar_id'";
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
			/*if ($datum <1)
				$datum = 1;*/
			echo "<tr><td></td>";
			if ($ALLOW_GROUPING_SEMINARS) {
				printf("<td class=\"%s\" align=\"center\">",$cssSw->getClass());
				if (!$db->f("admission_group")) { //wenn keiner Gruppe zugeordnet, dann cechkbox ausgeben
					unset($last_group);
					printf("<input type=\"checkbox\" name=\"gruppe[]\" value=\"%s\">",$db->f("Seminar_id"));
				} else {
					if($db->f("admission_group") != $last_group) {
						unset($last_group);
						$tag = 1 - $tag;
					}
						if (!isset($last_group)) { //Wenn erstes "Mitglied" einer Gruppe, dann Muelleimer ausgeben
						$last_group = $db->f("admission_group");
						printf("<a href=\"show_admission.php?seminarid=%s&institut_id=%s&group=ungroup\"><img src=\"".$GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\"></a>",$db->f("admission_group"),$institut_id);
					}
				}
				print("</td>");
			}
			if ($ALLOW_GROUPING_SEMINARS) {
				if(($db->f("admission_group")) && ($db->f("admission_group") == $last_group)) {
					print ("<td bgcolor=\"");
					if ($tag == 0) print ("#CC0000"); else print ("#00CC00");
					print ("\"></td>");
				} else {
					 printf("<td class=\"%s\"></td>",$cssSw->getClass());
				}
			}
			printf ("<td class=\"%s\"><a href=\"seminar_main.php?auswahl=%s&redirect_to=teilnehmer.php\"><font size=\"-1\">%s%s</font></a></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\" align=\"center\"><font size=\"-1\">%s</font></td>",
			$cssSw->getClass(), $db->f("Seminar_id"), htmlready(substr($db->f("Name"), 0, 50)), (strlen($db->f("Name"))>50) ? "..." : "", $cssSw->getClass(), $teilnehmer, $cssSw->getClass(), $quota, $cssSw->getClass(), $count2, $cssSw->getClass(), $count3, ($datum != -1) ? ($datum < time() ? "steelgroup4" : "steelgroup1") : $cssSw->getClass(), ($datum != -1) ? date("d.m.Y, G:i", $datum) : "");
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
				printf ("<td class=\"%s\" align=\"center\"><font size=\"-1\">", $class);
				if ($db->f("admission_starttime") != -1) echo _("Start:")." ".date("d.m.Y, G:i", $db->f("admission_starttime"))." <br/>";
				if ($db->f("admission_endtime_sem") != -1) echo _("Ende:")." ".date("d.m.Y, G:i", $db->f("admission_endtime_sem"));
				echo "</font></td>";
			} else {
				printf("<td class=\"%s\" align=\"center\"></td>", $cssSw->getClass());
			}
			print ("</tr>");
		}

		if ($db->nf() && $ALLOW_GROUPING_SEMINARS) {
			print ("<tr><td></td><td>\n");
			echo "<input ".makeButton("gruppieren","src")." ".tooltip(_("Markierte Veranstaltungen gruppieren"))." type=\"image\" border=\"0\" style=\"vertical-align:left;\" align=\"left\">";
			print("<input type=\"hidden\" name=\"group\" value=\"group\">\n");
			printf("<input type=\"hidden\" name=\"institut_id\" value=\"%s\">\n",$institut_id);
			print("</form>\n");
			print("</td></tr>\n");
		}

		print("</table>");

?>
<br>&nbsp;
</td>
</tr>
</table>
<?
page_close();
 ?>
</body>
</html>
<!-- $Id$ -->
