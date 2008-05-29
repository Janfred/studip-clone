<?
# Lifter002: 
/**
* Learning-modules that are connected to a lecture
*
* This file shows a list of ilias learning-modules that are connected to the chosen lecture.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		seminar_lermodule
* @package		ELearning
*/
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

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", 'user' => "Seminar_User"));
$perm->check("autor");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

//$i_page = "meine_seminare.php";
// -- here you have to put initialisations for the current page

require_once ('config.inc.php');
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('lib/msg.inc.php');

include ('lib/include/html_head.inc.php'); // Output of html head
include ('lib/include/header.php');   // Output of Stud.IP head

checkObject();
checkObjectModule("ilias_connect");
object_set_visit_module("ilias_connect");

if (isset($do_op) AND (($op_co_id == "") OR($op_co_inst == "") OR($seminar_id == "")))
{
	parse_window ("error§" . _("Die Seite wurde mit fehlerhaften Parametern aufgerufen. Bitte wenden Sie sich an den/die AdministratorIn."), "§",
				_("Fehlerhafte Parameter"));
	die();
}

if ($ILIAS_CONNECT_ENABLE)
{

	$db = New DB_Seminar;

	$db->query("SELECT preferred_language FROM user_info WHERE user_id='" . $auth->auth["uid"] . "'");
	if ($db->next_record())
		$preferred_language = $db->f("preferred_language");
	if ($preferred_language != "")
	{
		$language = explode("_", $preferred_language);
		$language = $language[0];
	}
	else
		$language = "de";
	$link1 = "<a href=\"".$ABSOLUTE_PATH_ILIAS . "help/$language/editor/index.html\">";

	if (($perm->have_studip_perm("autor",$seminar_id)) AND ($view=="edit"))
	{
		if (($do_op == "clear")  AND (($op_status == 2) OR ($perm->have_studip_perm("tutor",$seminar_id))))
		{
			$db->query("DELETE FROM seminar_lernmodul WHERE seminar_id = '$seminar_id' AND co_id = '$op_co_id' AND co_inst = '$op_co_inst' LIMIT 1");
			$op_string= _("Die Zuordnung wurde aufgehoben.");
			$print_open[$op_co_id . "@" . $op_co_inst . "@all"] = $print_open[$op_co_id . "@" . $op_co_inst . "@sem"];
			$print_open[$op_co_id . "@" . $op_co_inst . "@sem"] = false;
	     	}
	     	elseif ($do_op == "connect")
	     	{
			$op_string= _("Die Zuordnung wurde gespeichert.");
	     		$db->query("SELECT * FROM  seminar_lernmodul WHERE seminar_id = '$seminar_id' AND co_id = '$op_co_id' AND status = '$op_status' ");
	     		if ($db->next_record())
				$op_string= _("Dieses Lernmodul ist der Veranstaltung bereits zugeordnet.");
	     		else
	     		{
	     			$db->query("INSERT INTO seminar_lernmodul (seminar_id, co_id, co_inst, status) VALUES ('$seminar_id', '$op_co_id', '$op_co_inst', '$op_status')");
				$print_open[$op_co_id . "@" . $op_co_inst . "@sem"] = $print_open[$op_co_id . "@" . $op_co_inst . "@all"];
				$print_open[$op_co_id . "@" . $op_co_inst . "@all"] = false;
			}
     		}
	     	elseif (($do_op == "change") AND $perm->have_studip_perm("tutor",$seminar_id))
	     	{
			$op_string= _("Die Zuordnung wurde ge&auml;ndert.");
			$db->query("DELETE FROM seminar_lernmodul WHERE seminar_id = '$seminar_id' AND co_id = '$op_co_id' AND co_inst = '$op_co_inst' LIMIT 1");
     			$db->query("INSERT INTO seminar_lernmodul (seminar_id, co_id, co_inst, status) VALUES ('$seminar_id', '$op_co_id', '$op_co_inst', '$op_status')");
//			$print_open[$op_co_id . "@" . $op_co_inst . "@sem"] = false;
     		}
	}

	if ((!$perm->have_studip_perm("autor",$seminar_id)) AND ($view=="edit"))
	{
		if ($SessSemName["class"]=="inst")
			$msg = _("Sie haben keine Berechtigung, die Lernmodul-Zuordnungen dieser Einrichtung zu ver&auml;ndern.");
		else
			$msg = _("Sie haben keine Berechtigung, die Lernmodul-Zuordnungen dieser Veranstaltung zu ver&auml;ndern.");
		parse_window ("error§" . $msg, "§",
					_("Keine Berechtigung"));
		die();
	}

	include ('lib/include/links_openobject.inc.php');

	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_db_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_linking_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_view_functions.inc.php");


?><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="3">&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/icon-lern.gif" align="texttop">&nbsp;
		<b>
		<?   if ($view=="edit")
			{
				if ($SessSemName["class"]=="inst")
					echo _("Verbindung von Einrichtungen und Lernmodulen");
				else
					echo _("Verbindung von Veranstaltungen und Lernmodulen");
			}
			else
			{
				if ($SessSemName["class"]=="inst")
					echo _("Lernmodule f&uuml;r diese Einrichtung");
				else
					echo _("Lernmodule f&uuml;r diese Veranstaltung");
			}
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
echo $auswahl;
include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_user_functions.inc.php");

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

// Anzeige, wenn noch keine Account-Zuordnung besteht
	if (get_connected_user_id($auth->auth["uid"]) == false)
	{

		echo "<table><tr>";
		my_info(_("Sie m&uuml;ssen Ihren Account mit dem angebundenen ILIAS-System verbinden, bevor sie Lernmodule nutzen k&ouml;nnen."));
		echo "</tr></table>";
		echo _("F&uuml;r die Verwendung von Lernmodulen ist das Stud.IP mit einem ILIAS System verbunden. Damit Sie die Funktionen von ILIAS nutzen k&ouml;nnen, muss Ihrem Account in Stud.IP zun&auml;chst ein ILIAS-Account zugeordnet werden. Die Verwaltung des ILIAS-Accounts finden Sie auf ihrer Einstellungsseite (Werkzeugsymbol) unter \"My Stud.IP\". Dorthin gelangen Sie auch mit dem folgenden Link.");
		echo "<br><br>";
		echo "<a href=\"migration2studip.php?came_from=$seminar_id&came_from_view=$view\"><b>" . _("Mein ILIAS-Account") . "</b></a>";
		$infobox = array	(
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(
							array (	"icon" => "ausruf_small.gif",
									"text"  => _("Ihr Account wurde noch nicht mit dem angebundenen ILIAS-System verbunden.")
								 )
							)
			)
		);
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
										"text"  => sprintf(_("Hier k&ouml;nnen Sie Ihrem Stud.IP-Account einen %s ILIAS-Account zuweisen. %s"), "<a href=\"migration2studip.php\">", "</a>")
									);
		if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_studip_perm("autor",$seminar_id)))
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
										"text"  => sprintf(_("Hier k&ouml;nnen Sie ein %s neues Lernmodul anlegen%s. Das Modul muss anschlie&szlig;end noch zugewiesen werden."), "<a href=\"" . link_new_module() ."\" target=\"_blank\">", "</a>")
									);
	}
// Sicherheitsabfrage vor Loeschen eines Lernmoduls
	elseif (isset($delete))
	{
		echo "<table>";
		my_info(sprintf(_("Wenn Sie fortfahren, wird das Lernmodul mit dem Titel %s unwiderruflich gel&ouml;scht. Soll dieses Lernmodul wirklich gel&ouml;scht werden?"), "<b>" . $del_title . "</b>"));
		echo "</table>";
		?><br><center>
		<a href="<? echo link_delete_module($del_inst, $del_id); ?>" target="_blank"><? echo makeButton("ja", "img"); ?>&nbsp;
		<a href="<? echo $PHP_SELF; ?>"><? echo makeButton("nein", "img"); ?></center>
		<?
	}
// Lernmodule hinzufuegen / entfernen
	elseif (($perm->have_studip_perm("tutor",$seminar_id)) AND ($view=="edit"))
	{
		if ($SessSemName["class"]=="inst")
			$msg = _("Auf dieser Seite können Sie einer Einrichtung Lernmodule zuordnen.");
		else
			$msg = _("Auf dieser Seite können Sie einer Veranstaltung Lernmodule zuordnen.");
		$infobox = array	(
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(
							array (	"icon" => "ausruf_small.gif",
									"text"  => sprintf($msg, "<br><i>", "</i>")
								 )
							)
			)
		);
		if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_studip_perm("autor",$seminar_id)))
		$infobox[0]["eintrag"][] = array (	"icon" => "hilfe.gif",
									"text"  => $link1 . _("Hilfe zum Anlegen und Bearbeiten von ILIAS-Lernmodulen.") . "</a>"
		);
		if ($SessSemName["class"]=="inst")
			$msg = _("Sie können der Einrichtung ein Lernmodul zuordnen...");
		else
			$msg = _("Sie können der Veranstaltung ein Lernmodul zuordnen...");
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-posting.gif" ,
										"text"  => $msg
									);
			$infobox[1]["eintrag"][] = array (	"icon" => "trash.gif" ,
										"text"  => _("...oder eine bestehende Verknüpfung aufheben.")
									);
		if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_studip_perm("autor",$seminar_id)))
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
										"text"  => sprintf(_("Hier k&ouml;nnen Sie ein %s neues Lernmodul anlegen%s. Das Modul muss anschlie&szlig;end noch zugewiesen werden."), "<a href=\"" . link_new_module() ."\" target=\"_blank\">", "</a>")
									);

		if ($op_string != "")
		{
			echo "<table><tr>";
			my_msg($op_string);
			echo "</tr></table>";
		}
		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
// Offizielle Lernmodule der Veranstaltung anzeigen
     		$erg1 = show_seminar_modules($seminar_id, 1);
// Nicht-offizielle Lernmodule der Veranstaltung anzeigen
     		$erg2 = show_seminar_modules($seminar_id, 2);
		if (($erg1 == false) AND ($erg2 == false))
		{
			if ($SessSemName["class"]=="inst")
				$msg = _("Mit dieser Einrichtung sind keine ILIAS-Lernmodule verknüpft.");
			else
				$msg = _("Mit dieser Veranstaltung sind keine ILIAS-Lernmodule verknüpft.");
			echo "<b>" . $msg . "</b><br><br>";
		}
		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
		show_all_modules($seminar_id);
	}
// Lernmodule anzeigen und benutzen
	else
	{
		$le_modules = get_seminar_modules($seminar_id);
		if ($le_modules != false)
			$le_anzahl = sizeof($le_modules);
		else
			$le_anzahl = 0;
		if ($le_anzahl == 1)
		{
			if ($SessSemName["class"]=="inst")
				$info_text1 = _("Dieser Einrichtung ist ein Lernmodul zugeordnet.");
			else
				$info_text1 = _("Dieser Veranstaltung ist ein Lernmodul zugeordnet.");
		}
		else
		{
			if ($SessSemName["class"]=="inst")
				$info_text1 = sprintf(_("Dieser Einrichtung sind %s Lernmodule zugeordnet."), $le_anzahl);
			else
				$info_text1 = sprintf(_("Dieser Veranstaltung sind %s Lernmodule zugeordnet."), $le_anzahl);
		}
		$infobox = array	(
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(
							array (	"icon" => "ausruf_small.gif",
									"text"  => $info_text1
								 )
							)
			)
		);
		if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_studip_perm("autor",$seminar_id)))
		$infobox[0]["eintrag"][] = array (	"icon" => "hilfe.gif",
									"text"  => $link1 . _("Hilfe zum Anlegen und Bearbeiten von ILIAS-Lernmodulen.") . "</a>"
		);
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
										"text"  => _("Wenn Sie in einem Lernmodul auf 'Starten' klicken, &ouml;ffnet sich ein neues Fenster mit dem ILIAS-Lernmodul. Mit den Navigationspfeilen k&ouml;nnen Sie durch das Lernmodul bl&auml;ttern.")
									);

		if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_studip_perm("autor",$seminar_id)))
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
										"text"  => sprintf(_("Hier k&ouml;nnen Sie ein %s neues Lernmodul anlegen%s. Das Modul muss anschlie&szlig;end noch zugewiesen werden."), "<a href=\"" . link_new_module() ."\" target=\"_blank\">", "</a>")
									);

		$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
		show_seminar_modules_links($seminar_id);

//		if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_studip_perm("autor",$seminar_id)))
//			echo "<br><br><a href=\"" . link_new_module() ."\" target=\"_blank\">". _("Neues Lernmodul anlegen") ."</a><br>";
	}
	?>
	<br>
	</td>
	<td width="270" NOWRAP class="blank" align="center" valign="top">
	<?
		print_infobox ($infobox,"lernmodule.jpg");
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
	parse_window ("error§" . _("Das Verbindungsmodul für ILIAS-Lernmodule ist nicht eingebunden. Damit Lernmodule verwendet werden können, muss die Verbindung zu einer ILIAS-Installation in den Systemeinstellungen hergestellt werden. Wenden Sie sich bitte an den/die AdministratorIn."), "§",
				_("Lernmodule nicht eingebunden"));
}
include ('lib/include/html_end.inc.php');
page_close();
?>