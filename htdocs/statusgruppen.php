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
require_once ("$ABSOLUTE_PATH_STUDIP/statusgruppe.inc.php");
require_once ("$ABSOLUTE_PATH_STUDIP/functions.php");

// Start of Output
include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

checkObject();

include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");

$cssSw=new cssClassSwitcher;

// Hilfsfunktionen

function groupmail($range_id) {
	$type = get_object_type($range_id);
	if ($type == "group") {
		$db=new DB_Seminar;
		$db->query ("SELECT Email FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) WHERE statusgruppe_id = '$range_id'");
		while ($db->next_record()) {
			$mailpersons .= ",".$db->f("Email");
		}
		$mailpersons = substr($mailpersons,1);
		return $mailpersons;
	}
	if ($type == "sem") {
		$db=new DB_Seminar;
		$db->query ("SELECT Email FROM seminar_user LEFT JOIN auth_user_md5 USING(user_id) WHERE Seminar_id = '$range_id'");
		while ($db->next_record()) {
			$mailpersons .= ",".$db->f("Email");
		}
		$mailpersons = substr($mailpersons,1);
		return $mailpersons;
	}
}


function PrintAktualStatusgruppen () {
	global $_fullname_sql,$SessSemName, $PHP_SELF, $rechte;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db->query ("SELECT name, statusgruppe_id, size FROM statusgruppen WHERE range_id = '$SessSemName[1]' ORDER BY position ASC");
	$AnzahlStatusgruppen = $db->num_rows();
	$i = 0;
	while ($db->next_record()) {
		$statusgruppe_id = $db->f("statusgruppe_id");
		$size = $db->f("size");
		$groupmails = groupmail($statusgruppe_id);
		echo "<table width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" border=\"0\"><tr>";
		printf ("<td width=\"90%%\" class=\"topic\"><font size=\"-1\"><b>&nbsp;%s</b></font></td>",htmlReady($db->f("name")));
		printf ("<td width=\"10%%\"class=\"topic\" align=\"right\" nowrap>");
		if ($rechte) {  // nicht alle duerfen Gruppenmails/Gruppensms verschicken
			printf ("<a href=\"mailto:%s?subject=%s \"><img src=\"pictures/mailnachricht.gif\" " . tooltip(_("E-Mail an alle Gruppenmitglieder verschicken")) . " border=\"0\"></a>", $groupmails,rawurlencode($SessSemName[0])); 
			printf ("&nbsp;<a href=\"sms.php?sms_source_page=statusgruppen.php&cmd=write&group_id=%s \"><img src=\"pictures/nachricht1.gif\" " . tooltip(_("Systemnachricht an alle Gruppenmitglieder verschicken")) . " border=\"0\"></a>&nbsp;", $statusgruppe_id); 
		} else {
			echo "&nbsp;";
		}
		printf ("</td>");
		echo 	"</tr>";

		$db2->query ("SELECT statusgruppe_user.user_id, " . $_fullname_sql['full'] ." AS fullname, username FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE statusgruppe_id = '$statusgruppe_id'");
		$k = 1;
		while ($db2->next_record()) {
			if ($k % 2) {
				$class="steel1";
			} else {
				$class="steelgraulight"; 
			}
			printf ("<tr>");
			printf ("<td width=\"90%%\" class=\"%s\"><font size=\"-1\"><a href = about.php?username=%s>&nbsp;%s</a></font></td>",$class, $db2->f("username"), htmlReady($db2->f("fullname")));
			printf ("<td width=\"10%%\"class=\"$class\" align=\"right\">");
			printf ("<a href=\"sms.php?sms_source_page=teilnehmer.php&cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" " . tooltip(_("Systemnachricht an User verschicken")) . " border=\"0\"></a>", $db2->f("username")); 
			printf ("&nbsp;</td>");
			echo "</tr>";
			$k++;
		}
		$i++;
		echo "</table><br><br>";
	}
}

function PrintNonMembers ($range_id)
{	
	global $_fullname_sql;
	$bereitszugeordnet = GetAllSelected($range_id);
	$db=new DB_Seminar;
	$query = "SELECT seminar_user.user_id, username, " . $_fullname_sql['full'] ." AS fullname, perms FROM seminar_user  LEFT JOIN auth_user_md5 USING(user_id) LEFT JOIN user_info USING (user_id) WHERE Seminar_id = '$range_id' ORDER BY Nachname ASC";
	$db->query ($query);
	if ($db->num_rows() >sizeof($bereitszugeordnet)-1) { // there are non-grouped members
		echo "<table width=\"99%\" cellpadding=\"0\" cellspacing=\"0\" align=\"center\" border=\"0\"><tr>";
		print ("<td width=\"100%%\" colspan=\"2\" class=\"steel\"><font size=\"-1\"><b>&nbsp;" . _("keiner Funktion oder Gruppe zugeordnet") . "</b></font> <img src=\"pictures/blank.gif\" height=\"25\"></td>");
		echo 	"</tr>";
		$k = 1;
		while ($db->next_record()) {
			if (!in_array($db->f("user_id"), $bereitszugeordnet)) {
				if ($k % 2) {
					$class="steel1";
				} else {
					$class="steelgraulight"; 
				}
				printf ("<tr>");
				printf ("<td width=\"90%%\" class=\"%s\"><font size=\"-1\"><a href = about.php?username=%s>&nbsp;%s</a></font></td>",$class, $db->f("username"), htmlReady($db->f("fullname")));
				printf ("<td width=\"10%%\"class=\"$class\" align=\"right\">");
				printf ("<a href=\"sms.php?sms_source_page=teilnehmer.php&cmd=write&rec_uname=%s\"><img src=\"pictures/nachricht1.gif\" " . tooltip(_("Systemnachricht an User verschicken")) . " border=\"0\"></a>", $db->f("username")); 
				printf ("&nbsp;</td>");
				echo "	</tr>";
				$k++;
			}
		}
	echo "</table><br><br>";
	}
	if ($k > 1) {
		$Memberstatus = 1;
	} else {
		$Memberstatus = 2;
	}
	if (sizeof($bereitszugeordnet) < 2) {
		$Memberstatus = 0;
	}
	return $Memberstatus;
}

// Beginn Darstellungsteil

?>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="2"><b>&nbsp;<?=$SessSemName["header_line"]; ?> - <?=_("Funktionen / Gruppen")?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
		</td>
	</tr>
	<tr>
		<td class="blank">
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<?
	if ($sms_msg)
		parse_msg (rawurldecode($sms_msg));
	?>	
	<tr valign="top">
     <td width="90%" NOWRAP class="blank">
			<?
			PrintAktualStatusgruppen();
			$anzahltext = PrintNonMembers($SessSemName[1]); 
			
			if ($anzahltext == 1) {
				$Memberstatus = _("Nicht alle Personen sind einer Funktion / Gruppe zugeordnet.");
			}
			if ($anzahltext == 2) {
				$Memberstatus = _("Alle Personen sind mindestens einer Funktion / Gruppe zugeordnet.");
			}
			if ($anzahltext == 0) {
				$Memberstatus = _("Niemand ist einer Funktion / Gruppe zugeordnet.");
			}
			?>
		</td>
	</tr>
	</table>
	</td>
	<td width="270" NOWRAP class="blank" align="center" valign="top">
	
	<?
	$infobox = array	(			
		array  ("kategorie"  => _("Information:"),
			"eintrag" => array	(	
				array (	"icon" => "pictures/ausruf_small.gif",
											"text"  => $Memberstatus
				)
			)
		)
	);
	$link = "<a href=\"mailto:".groupmail($SessSemName[1])."?subject=".rawurlencode($SessSemName[0])."\">";
	$infobox[1]["kategorie"] = _("Aktionen:");
		$infobox[1]["eintrag"][] = array (	"icon" => "./pictures/nachricht1.gif" ,
									"text"  => _("Um Personen eine systeminterne Kurznachricht zu senden, benutzen Sie bitte das normale Briefsymbol.")
								);
	if ($rechte) {
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/einst.gif",
								"text"  => sprintf(_("Um Gruppen anzulegen und ihnen Personen zuzuordnen nutzen Sie %sFunktionen / Gruppen verwalten%s."), "<a href=\"admin_statusgruppe.php?view=statusgruppe_sem&new_sem=TRUE&range_id=$SessSemName[1]\">", "</a>")
								);
	if ($anzahltext > 0) {
		$infobox[1]["eintrag"][] = array (	"icon" => "./pictures/mailnachricht.gif" ,
									"text"  => _("Mit dem erweiterten Briefsymbol k&ouml;nnen Sie eine E-Mail an alle Gruppenmitglieder verschicken.")
								);
	}
		$infobox[1]["eintrag"][] = array (	"icon" => "./pictures/ausruf_small.gif" ,
									"text"  => sprintf(_("Um eine E-Mail an alle TeilnehmerInnen der Veranstaltung zu versenden, klicken Sie %shier%s."), $link, "</a>")
								);
	}

	print_infobox ($infobox,"pictures/groups.jpg");

	?>
	</td>		
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
		</td>
	</tr>
</table>
<p>
<?

// Ende Darstellungsteil

page_close();
?>
</body>
</html>
