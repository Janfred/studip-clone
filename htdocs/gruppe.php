<?php
/*
gruppe.php - Zuordnung der abonierten Seminare zu Gruppen
Copyright (C) 2000 Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

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

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page
require_once("$ABSOLUTE_PATH_STUDIP/functions.php");
require_once("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

$db=new DB_Seminar;
      

//es wird eine Tabelle aufgebaut, in der die Gruppenzugehoerigkeit festgelegt wird.

IF ($auth->is_authenticated() && $user->id != "nobody" && !$perm->have_perm("admin")){
	 ?>
	<table width="75%" border=0 cellpadding=0 cellspacing=0 align=center>
	<tr>
		<td class="topic">&nbsp;&nbsp;<img src="pictures/gruppe.gif" alt="Gruppe &auml;ndern" border=0>&nbsp;&nbsp;<b><?=_("Gruppenzuordnung")?></></td>
	</tr>
	<tr><td class="blank"><br><blockquote><?=_("Hier k&ouml;nnen Sie Ihre Veranstaltungen in Gruppen einordnen. Die Gruppen werden farbig gegliedert - die Darstellung unter <b>meine Veranstaltungen</b> wird entsprechend den Gruppen sortiert.")?></blockquote><br>
	<FORM method=post action="meine_seminare.php">
	<table border="0" cellpadding="0" cellspacing="0" width="90%" align="center">
	<tr valign"top" align="center">
	<th width="90%"><?=_("Veranstaltung")?></th>

<? FOR ($i=0; $i<8; $i++)
	ECHO "<th class=\"gruppe".$i."\" width=\"10px\"><b>&nbsp;</b></th>";
	ECHO "</tr>";
	$db->query ("SELECT seminare.Name,seminare.visible, seminare.Seminar_id, seminar_user.status, seminar_user.gruppe FROM seminare LEFT JOIN seminar_user USING (Seminar_id) WHERE seminar_user.user_id = '$user->id' GROUP BY Seminar_id ORDER BY gruppe,Name");
	$c=0;
	while ($db->next_record())
		{
 	  	if ($c % 2)
			$class="steel1";
		else
			$class="steelgraulight"; 

		printf("<tr><td class=\"$class\">&nbsp;<a href=\"seminar_main.php?auswahl=%s\">%s</a>%s</td>",
		$db->f("Seminar_id"),htmlReady(my_substr($db->f("Name"),0,50)),
		(!$db->f('visible') ? "&nbsp;<font size=\"-1\">" . _("(versteckt)") . "</font>" : ""));
		FOR ($i=0; $i<8; $i++)
			{
			ECHO "<td class=\"$class\"><INPUT type=radio name=gruppe[".$db->f('Seminar_id')."] value=".$i;
			IF ($db->f("gruppe")==$i) ECHO " checked";
			ECHO "></td>";
			}
		ECHO "</tr>";	
		$c++;
		}
		ECHO "<tr><td class=\"blank\">&nbsp; </td><td class=\"blank\" align=center colspan=8><br><INPUT type=\"IMAGE\" " . makeButton("absenden", "src") . " border=0 value=absenden><INPUT type=hidden name=gruppesent value=1><br />&nbsp; </td></tr></form>";
	echo "</table></td></tr>";
}

?>
</table>
</body>
</html>
<?php
  // Save data back to database.
  page_close()
 ?>
<!-- $Id$ -->

