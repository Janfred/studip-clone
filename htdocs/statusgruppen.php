<?
/*
statusgruppen.php - Statusgruppen-Anzeige von Stud.IP.
Copyright (C) 2002 Ralf Stockmann <rstockm@gwdg.de>

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

  page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));

	include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	include ("$ABSOLUTE_PATH_STUDIP/links1.php");

	$cssSw=new cssClassSwitcher;

// Hilfsfunktionen

function PrintAktualStatusgruppen ()
{	global $SessSemName, $PHP_SELF;
	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT name, statusgruppe_id, size FROM statusgruppen WHERE range_id = '$SessSemName[1]' ORDER BY position ASC");
	$AnzahlStatusgruppen = $db->num_rows();
	$tmptxt ="Es sind noch keine Statusgruppen angelegt.";
	if ($rechte) {
		$tmptxt .= "Nutzen Sie oben den Link 'Statusgruppen verwalten' wenn Sie welche anlegen m&ouml;chten!";
	} 
	if ($AnzahlStatusgruppen == 0) {
		$infobox = array	(			
		array  ("kategorie"  => "Information:",
			"eintrag" => array	(	
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => $tmptxt
									)
			)
		    )
		);
		echo "<br>";
		// print the info_box
		print_infobox ($infobox,"pictures/seminare.jpg");
	}
	$i = 0;
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		$size = $db->f("size");
		echo "<table width=\"99%\" cellpadding=\"1\" cellspacing=\"0\" align=\"center\" border=\"0\">
			        <tr height=28> ";
		printf ("	          <td width=\"100%%\" colspan=\"2\" class=\"steel\"><font size=\"-1\"><b>%s</b></font><img src=\"pictures/blank.gif\"height=\"22\"></td>",htmlReady($db->f("name")));
		echo 	"</tr>";

		$db2->query ("SELECT statusgruppe_user.user_id, Vorname, Nachname, username FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) WHERE statusgruppe_id = '$statusgruppe_id'");
		$k = 1;
		while ($db2->next_record()) {
			if ($k % 2) {
				$class="steel1";
			} else {
				$class="steelgraulight"; 
			}
			printf ("     <tr>");
			printf ("       <td width=\"95%%\" class=\"%s\"><font size=\"-1\"><a href = about.php?username=%s>%s&nbsp; %s</a></font></td>",$class, $db2->f("username"), htmlReady($db2->f("Vorname")), htmlReady($db2->f("Nachname")));
			printf ("	   <td width=\"5%%\"class=\"$class\" align=\"center\">");
			printf ("		<a href=\"sms.php?sms_source_page=teilnehmer.php&cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" alt=\"Nachricht an User verschicken\" border=\"0\"></a>", $db2->f("username")); 
			printf ("	   </td>");
			echo "	</tr>";
			$k++;
		}
		$i++;
		echo "</table><br><br>";
	}
}


// Beginn Darstellungsteil

 ?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic"><b>&nbsp;<? echo $SessSemName["art"],": ",htmlReady($SessSemName[0]); ?> - Statusgruppen</b>
		</td>
	</tr>
	<tr>
		<td class="blank">&nbsp; 
		</td>
	</tr>
	<tr>
     		<td width="100%" NOWRAP class="blank">
			<?PrintAktualStatusgruppen (); ?>
			<br>&nbsp; 
		</td>
	</tr>
</table>
<p>&nbsp;</p>
<?

// Ende Darstellungsteil

page_close();
?>
</body>
</html>
