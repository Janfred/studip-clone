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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");  //Linkleiste fuer admins
		
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
		echo "Teilnehmerbeschr&auml;nkte Veranstaltungen";
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
				<input <?=makeButton("auswaehlen","src")?> <?=tooltip(_("Einrichtung auswählen"))?> type="image" border="0" style="vertical-align:middle;">
				<br>&nbsp;
			</div>
		</td>
		</form>
	</tr>	
	<tr>
		<td class="blank"align="center">
<?
		if ((($institut_id == "all") || (!$institut_id)) && ($perm->have_perm("root")))
		  	$query = "SELECT Name, Seminar_id, admission_turnout, admission_endtime FROM seminare WHERE admission_type > 0 ORDER BY Name";
		 else
			$query = "SELECT Name, seminare.Seminar_id, admission_turnout, admission_endtime FROM seminare LEFT JOIN seminar_inst USING (Institut_id) WHERE admission_type > 0 AND seminar_inst.institut_id = '$institut_id' GROUP BY seminare.Seminar_id ORDER BY Name";		  
		$db->query($query);
		if ($db->nf()) {
			print ("<table width=\"90%\" border=0 cellspacing=0 cellpadding=2>");
			print ("<tr>");		
			echo "<th width=\"40%\">Veranstaltung</th>";
			echo "<th width=\"10%\">Teilnehmer</th>";
			echo "<th width=\"10%\">Quota</th>";
			echo "<th width=\"10%\">Anmeldeliste</th>";
			echo "<th width=\"10%\">Warteliste</th>";
			echo "<th width=\"20%\">Datum</th>";
			echo "</tr>";
		} elseif ($institut_id) {
			print ("<table width=\"99%\" border=0 cellspacing=0 cellpadding=2>");
			parse_msg ("info§Im gew&auml;hlten Bereich existieren keine teilnahmebeschr&auml;nkten Veranstaltungen§", "§", "steel1",2, FALSE);
		}
		
	  	while ($db->next_record()) {
				$seminar_id = $db->f("Seminar_id");
	  			$query2 = "SELECT * FROM seminar_user WHERE seminar_id='$seminar_id'";
				$db2->query($query2);
				$teilnehmer = $db2->num_rows();
	  			$cssSw->switchClass();
				$quota = $db->f("admission_turnout");
				$count2 = 0;
				$count3 = 0;
			  	$query2 = "SELECT status, count(*) AS count2 FROM admission_seminar_user WHERE seminar_id='$seminar_id' AND status='claiming' GROUP BY status";
				$db2->query($query2);
				if ($db2->next_record()) {
					$count2 = $db2->f("count2"); 				
				}
				$query2 = "SELECT status, count(*) AS count2 FROM admission_seminar_user WHERE seminar_id='$seminar_id' AND status='awaiting' GROUP BY status";
				$db2->query($query2);
				if ($db2->next_record()) {
					$count3 = $db2->f("count2"); 				
				}
				$datum = $db->f("admission_endtime");
				if ($datum <1)
					$datum = 1;
				ECHO "<tr>";
				printf ("<td class=\"%s\"><a href=\"seminar_main.php?auswahl=%s&redirect_to=teilnehmer.php\"><font size=\"-1\">%s%s</font></a></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td><td class=\"%s\"><font size=\"-1\">%s</font></td>", 
					$cssSw->getClass(), $db->f("Seminar_id"), htmlready(substr($db->f("Name"), 0, 50)), (strlen($db->f("Name"))>50) ? "..." : "", $cssSw->getClass(), $teilnehmer, $cssSw->getClass(), $quota, $cssSw->getClass(), $count2, $cssSw->getClass(), $count3, $datum < time() ? "steelgroup4" : "steelgroup1", date("d.m.Y, G:i", $datum));	 
				print ("</tr>");
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