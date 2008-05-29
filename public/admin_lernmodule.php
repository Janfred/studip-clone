<?
# Lifter002: TODO
/**
* Administration of learning-modules in the connected ILIAS-Installation.
*
* This file includes administration tools for learning-modules.
* It allows to delete or edit learning-modules and to create new ones.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		admin_lernmodule
* @package		ELearning
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_lernmodule.php
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
$perm->check("tutor");

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

//$i_page = "meine_seminare.php";
//$ILIAS_CONNECT_ENABLE = true;
//$RELATIVE_PATH_LEARNINGMODULES = "lernmodule";
//$ABSOLUTE_PATH_ILIAS = "/ilias/";
// -- here you have to put initialisations for the current page

require_once ('config.inc.php');
require_once ('lib/visual.inc.php');
require_once 'lib/functions.php';
require_once ('lib/msg.inc.php');

$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design

if ($ILIAS_CONNECT_ENABLE)
{

	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head

	include ('lib/include/links_admin.inc.php');

	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_db_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_user_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_view_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_linking_functions.inc.php");

	if (isset($do_open))
		$print_open_admin[$do_open] = true;
	elseif (isset($do_close))
		$print_open_admin[$do_close] = false;
	$sess->register("print_open_admin");

?><table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="3">&nbsp;<b><?=_("Administration von Lernmodulen")?></b>
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
	//neuen ILIAS-User anlegen, wenn noch nicht vorhanden.
//	create_ilias_user($auth->auth["uname"]);

	if (get_connected_user_id($auth->auth["uid"]) == false)
	{
		echo "<table><tr>";
		my_info(_("Sie m&uuml;ssen Ihren Account mit dem angebundenen ILIAS-System verbinden, bevor sie Lernmodule nutzen k&ouml;nnen."));
		echo "</tr></table>";
		echo _("F&uuml;r die Verwendung von Lernmodulen ist das Stud.IP mit einem ILIAS System verbunden. Damit Sie die Funktionen von ILIAS nutzen k&ouml;nnen, muss Ihrem Account in Stud.IP zun&auml;chst ein ILIAS-Account zugeordnet werden. Die Verwaltung des ILIAS-Accounts finden Sie auf ihrer Einstellungsseite (Werkzeugsymbol) unter \"My Stud.IP\". Dorthin gelangen Sie auch mit dem folgenden Link.");
		echo "<br><br>";
		echo "<a href=\"migration2studip.php?came_from=admin\"><b>" . _("Mein ILIAS-Account") . "</b></a>";
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
	else
	{
		if (isset($delete))
		{
			echo "<table>";
			my_info(sprintf(_("Wenn Sie fortfahren, wird das Lernmodul mit dem Titel %s unwiderruflich gel&ouml;scht. Soll dieses Lernmodul wirklich gel&ouml;scht werden?"), "<b>" . $del_title . "</b>"));
			echo "</table>";
			?><br><center>
			<a href="<? echo link_delete_module($del_inst, $del_id); ?>" target="_blank"><? echo makeButton("ja", "img"); ?>&nbsp;
			<a href="<? echo $PHP_SELF; ?>"><? echo makeButton("nein", "img"); ?></center>
			<?
		}
		else
		{
			if (get_connected_user_id($auth->auth["uid"]) != false)
				echo "<a href=\"" . link_new_module() ."\" target=\"_blank\">". _("Neues Lernmodul anlegen") ."</a><br><br>";

			if ($perm->have_perm("admin"))
				show_admin_modules();
			else
				show_user_modules($auth->auth["uid"]);
		}

		?>
		<br>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
		<?
		$infobox = array	(
		array ("kategorie"  => _("Information:"),
			"eintrag" => array	(
							array (	"icon" => "ausruf_small.gif",
									"text"  => sprintf(_("Auf dieser Seite k&ouml;nnen Sie die Lernmodule administrieren."), "<br><i>", "</i>")
								 )
							)
			)
		);
		$link = "<a href=\"./test.xml"."\">";
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "icon-posting.gif" ,
										"text"  => _("Sie k&ouml;nnen ein Lernmodul bearbeiten, wenn Sie als AutorIn oder Co-AutorIn daf&uuml;r eingetragen sind.")
									);
			$infobox[1]["eintrag"][] = array (	"icon" => "trash.gif" ,
										"text"  => _("Sie k&ouml;nnen ein Lernmodul l&ouml;schen, indem Sie den Kasten des Moduls &ouml;ffnen und auf 'l&ouml;schen' klicken.")
									);
			if ((get_connected_user_id($auth->auth["uid"]) != false) AND ($perm->have_perm("autor")))
				$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
											"text"  => sprintf(_("Hier k&ouml;nnen Sie ein %s neues Lernmodul anlegen %s"), "<a href=\"" . link_new_module() ."\" target=\"_blank\">", "</a>")
										);
				print_infobox ($infobox,"lernmodule.jpg");
	}
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
	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head
	parse_window ("error§" . _("Das Verbindungsmodul f&uuml;r ILIAS-Lernmodule ist nicht eingebunden. Damit Lernmodule verwendet werden k&ouml;nnen, muss die Verbindung zu einer ILIAS-Installation in den Systemeinstellungen hergestellt werden. Wenden Sie sich bitte an den/die AdministratorIn."), "§",
				_("Lernmodule nicht eingebunden"));
}
include ('lib/include/html_end.inc.php');
page_close();
?>