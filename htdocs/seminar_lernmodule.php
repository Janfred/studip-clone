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
$perm->check("autor");

include ("$ABSOLUTE_PATH_STUDIP/seminar_open.php"); // initialise Stud.IP-Session

//$i_page = "meine_seminare.php";
// -- here you have to put initialisations for the current page

require_once ($ABSOLUTE_PATH_STUDIP."/visual.inc.php");
require_once ($ABSOLUTE_PATH_STUDIP."/functions.php");
require_once ($ABSOLUTE_PATH_STUDIP."/msg.inc.php");

if (isset($do_op) AND (($op_co_id == "") OR($op_co_inst == "") OR($seminar_id == "")))
{
	parse_window ("error§" . _("Die Seite wurde mit fehlerhaften Parametern aufgerufen. Bitte wenden Sie sich an den/die AdministratorIn."), "§",
				_("Fehlerhafte Parameter"));
	die();
}
if ($ILIAS_CONNECT_ENABLE)
{

	if (($perm->have_perm("dozent")) AND ($view=="edit"))
	{		
		$db = New DB_Seminar;
		if ($do_op == "clear")
		{
			$db->query("DELETE FROM seminar_lernmodul WHERE seminar_id = '$seminar_id' AND co_id = '$op_co_id' AND co_inst = '$op_co_inst' LIMIT 1");
			$op_string= _("Die Zuordnung wurde aufgehoben.");
	     	}
	     	elseif ($do_op == "connect")
	     	{
			$op_string= _("Die Zuordnung wurde gespeichert.");
	     		$db->query("SELECT * FROM  seminar_lernmodul WHERE seminar_id = '$seminar_id' AND co_id = '$op_co_id'");
	     		if ($db->next_record())
				$op_string= _("Dieses Lernmodul ist der Veranstaltung bereits zugeordnet.");
	     		else
	     			$db->query("INSERT INTO seminar_lernmodul (seminar_id, co_id, co_inst) VALUES ('$seminar_id', '$op_co_id', '$op_co_inst')");
     		}
	}

	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head

	include ("$ABSOLUTE_PATH_STUDIP/links_openobject.inc.php");

	include_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_config.inc.php");
	include_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_db_functions.inc.php");
	include_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_linking_functions.inc.php");
	include_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_view_functions.inc.php");


?><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="3">&nbsp;<b>
		<?   if ($view=="edit") 
				echo _("Verbindung von Veranstaltungen und Lernmodulen"); 
			else
				echo _("Lernmodule f&uuml;r diese Veranstaltung"); 
		?></b>
		</td>
	</tr>
	<tr>
		<td class="blank" colspan="3">&nbsp; 
		</td>
	</tr>
	<tr valign="top">
                <td width="1%" class="blank">
                	&nbsp; 
                </td>
		<td width="90%" class="blank">
<?     				
	if (($view == "edit") AND ($perm->have_perm("dozent")))
	{
		$infobox = array	(			
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(	
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => sprintf(_("Auf dieser Seite können Sie einer Veranstaltung Lernmodule zuordnen."), "<br><i>", "</i>")
								 )
							)
			)
		);
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/icon-posting.gif" ,
										"text"  => _("Sie können der Veranstaltung ein Lernmodul zuordnen...")
									);
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/trash.gif" ,
										"text"  => _("...oder eine bestehende Verknüpfung aufheben.")
									);

		if ($op_string != "") 
		{
			echo "<table><tr>";
			my_msg($op_string);
			echo "</tr></table>";
		}
		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
     		show_seminar_modules($seminar_id);
		
		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
		show_all_modules($seminar_id);
	}
	else
	{
		include_once ($ABSOLUTE_PATH_STUDIP. $RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_user_functions.inc.php");

		if ($seminar_id != $print_open["id"])
		{	
			$sess->unregister("print_open");
			unset($print_open);
		}/**/
		$print_open["id"] = $seminar_id;
		if (isset($do_open))
			$print_open[$do_open] = true;
		elseif (isset($do_close))
			$print_open[$do_close] = false;
		$sess->register("print_open");


		$le_modules = get_seminar_modules($seminar_id);
		if ($le_modules != false)
			$le_anzahl = sizeof($le_modules);
		else
			$le_anzahl = 0;
		if ($le_anzahl == 1)
			$info_text1 = _("Dieser Veranstaltung ist ein Lernmodul zugeordnet.");
		else
			$info_text1 = sprintf(_("Dieser Veranstaltung sind %s Lernmodule zugeordnet."), $le_anzahl);
		$infobox = array	(			
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(	
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => $info_text1
								 )
							)
			)
		);
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/forumgrau.gif" ,
										"text"  => _("Wenn Sie auf den Titel eines Lernmoduls klicken, &ouml;ffnet sich ein neues Fenster mit dem ILIAS-Lernmodul. Mit den Navigationspfeilen k&ouml;nnen Sie durch das Lernmodul bl&auml;ttern.")
									);

		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
		show_seminar_modules_links($seminar_id);
	}
	?>
	<br>
	</td>
	<td width="270" NOWRAP class="blank" align="center" valign="top">
	<? 
		print_infobox ($infobox,"pictures/lernmodule.jpg");
	?>		
	</td>		
</tr>
<tr>
	<td class="blank" colspan="3">&nbsp; 
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