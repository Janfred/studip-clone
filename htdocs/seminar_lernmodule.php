<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// seminar_lernmodul.php
//
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", user => "Seminar_User"));
$perm->check("dozent");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

//$i_page = "meine_seminare.php";
$ILIAS_CONNECT_ENABLE = true;
$RELATIVE_PATH_LERNMODUL = "lernmodule";
$ABSOLUTE_PATH_ILIAS = "/ilias/";
// -- here you have to put initialisations for the current page

require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/msg.inc.php");

if (isset($do_op) AND (($op_co_id == "") OR($op_co_inst == "") OR($op_seminar_id == "")))
{
	parse_window ("error§" . _("Die Seite wurde mit fehlerhaften Parametern aufgerufen. Bitte wenden Sie sich an den/die AdministratorIn."), "§",
				_("Fehlerhafte Parameter"));
	die();
}
if ($ILIAS_CONNECT_ENABLE)
{

	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");

	require_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LERNMODUL ."/lernmodul_config.inc.php");
	require_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LERNMODUL ."/lernmodul_db_functions.inc.php");
	require_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LERNMODUL ."/lernmodul_view_functions.inc.php");
	require_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LERNMODUL ."/lernmodul_linking_functions.inc.php");


?><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="2"><b><? echo _("Verbindung von Stud.IP-Veranstaltungen und Lernmodulen"); ?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
		</td>
	</tr>
	<tr valign="top">
		<td width="90%" class="blank"><?
     				
		$db = New DB_Seminar;
     		if ($do_op == "clear")
     		{
     			$db->query("DELETE FROM seminar_lernmodul WHERE seminar_id = '$op_seminar_id' AND co_id = '$op_co_id' AND co_inst = '$op_co_inst' LIMIT 1");
     			echo "<table><tr>";
     			my_msg("Die Zuordnung wurde aufgehoben.");
	     		echo "</tr></table>";
	     	}
	     	elseif ($do_op == "connect")
	     	{
	     		$db->query("INSERT INTO seminar_lernmodul (seminar_id, co_id, co_inst) VALUES ('$op_seminar_id', '$op_co_id', '$op_co_inst')");
	     		echo "<table><tr>";
	     		my_msg("Die Zuordnung wurde gespeichert.");
	     		echo "</tr></table>";
     		}
		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
     		show_seminar_modules("958125dbf62513c841bdebf7aef3e1ed");
		
		echo "<b>" . _("Folgende Lernmodule können eingebunden werden:") . "</b><br><br>";

		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
		show_all_modules("958125dbf62513c841bdebf7aef3e1ed");

		?>
		</table>
		<br>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
		<? 
	$infobox = array	(			
	array ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => sprintf(_("Auf dieser Seite können Sie einer Veranstaltung Lernmodule zuordnen."), "<br><i>", "</i>")
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = _("Aktionen:");
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/icon-posting.gif" ,
									"text"  => _("Sie können ein Lernmodul einer Veranstaltung zuordnen...")
								);
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/trash.gif" ,
									"text"  => _("...oder eine bestehende Verknüpfung aufheben.")
								);
			print_infobox ($infobox,"pictures/lernmodule.jpg");
		?>		
		</td>		
	</tr>
	</table>
<?
}
else 
{
	// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	parse_window ("error§" . _("Das Verbindungsmodul für ILIAS-Lernmodule ist nicht eingebunden. Damit Lernmodule verwendet werden können, muss die Verbindung zu einer ILIAS-Installation in den Systemeinstellungen hergestellt werden. Wenden Sie sich bitte an den/die AdministratorIn."), "§",
				_("Lernmodule nicht eingebunden"));
}
page_close();
?>
</body>
</html>