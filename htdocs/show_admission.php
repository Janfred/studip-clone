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
		echo "Teilnehmerbeschr&auml;nkte Veranstaltungen im System";
		?></b>
		</td>
	</tr>
	<tr>
		<td class="blank"align="center"><br><br>
<?

	  	$query = "SELECT Name, seminar_id, admission_turnout, admission_endtime FROM seminare WHERE admission_turnout > 0 ORDER BY Name";
		$db->query($query);
		print ("<table width=\"90%\" border=1 cellspacing=0 cellpadding=2>");
		print ("<tr>");
		if ($db->num_rows() > 0) {
			echo "<th width=\"40%\">Veranstaltung</th>";
			echo "<th width=\"10%\">Teilnehmer</th>";
			echo "<th width=\"10%\">Quota</th>";
			echo "<th width=\"10%\">claiming</th>";
			echo "<th width=\"10%\">awaiting</th>";
			echo "<th width=\"20%\">Datum</th>";
			echo "</tr>";
		}
	  	while ($db->next_record()) {
				$seminar_id = $db->f("seminar_id");
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
				printf ("<td class=\"%s\"><a href=\"seminar_main.php?auswahl=%s&redirect_to=teilnehmer.php\">%s</a></td><td class=\"%s\">%s</td><td class=\"%s\">%s</td><td class=\"%s\">%s</td><td class=\"%s\">%s</td><td class=\"%s\">%s</td>", $cssSw->getClass(), $db->f("seminar_id"), $db->f("Name"), $cssSw->getClass(), $teilnehmer, $cssSw->getClass(), $quota, $cssSw->getClass(), $count2, $cssSw->getClass(), $count3, $cssSw->getClass(), date("d.m.Y, G:i", $datum));	 
				print ("</tr>");
			}
		
		print("</table>");
?>
<br><br>&nbsp; 
</td>
</tr>
</table>
<?
	  page_close();
 ?>
</body>
</html>
<!-- $Id$ -->