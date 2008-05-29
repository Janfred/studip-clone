<?
# Lifter002: TODO
/**
* Browse learning-modules in the connected ILIAS-Installation.
*
* This file allows to search for ILIAS-learning modules. The search-results are shown in a result-table.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		browse_lernmodule
* @package		ELearning
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// browse_lernmodule.php
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
$perm->check("user");


include ('lib/seminar_open.php'); // initialise Stud.IP-Session

require_once ('config.inc.php');
include_once ('lib/visual.inc.php');
include_once 'lib/functions.php';
include_once ('lib/msg.inc.php');

if (isset($do_open))
	$print_open_search[$do_open] = true;
elseif (isset($do_close))
	$print_open_search[$do_close] = false;
$sess->register("print_open_search");

if ($ILIAS_CONNECT_ENABLE)
{

	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_db_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_user_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_linking_functions.inc.php");
	include_once ($RELATIVE_PATH_LEARNINGMODULES ."/lernmodul_view_functions.inc.php");

	include ('lib/include/html_head.inc.php'); // Output of html head
	include ('lib/include/header.php');   // Output of Stud.IP head

	checkObjectModule("ilias_connect");

	$infobox = array	(array ("kategorie"  => _("Information:"),
			"eintrag" => array	(array (	"icon" => "ausruf_small.gif",
									"text"  => sprintf(_("Auf dieser Seite k&ouml;nnen Sie nach Lernmodulen im angebundenen ILIAS-System suchen.")) ) ) ) );

	$infobox[1]["kategorie"] = _("Aktionen:");
	$infobox[1]["eintrag"][] = array (	"icon" => "icon-lern.gif" ,
									"text"  => sprintf(_("Geben Sie einen Suchbegriff ein und klicken Sie auf 'Suche starten'. Die Suche bezieht sich auf den ausgew&auml;hlten Suchbereich.")));
//	$infobox[1]["eintrag"][] = array (	"icon" => "forumgrau.gif" ,
//									"text"  => sprintf(_("Gefundene Lernmodule k&ouml;nnen von hier aus gestartet werden. ")));

?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td class="topic" colspan="3">&nbsp;<img src="<?= $GLOBALS['ASSETS_URL'] ?>images/suchen.gif" border="0" align="texttop">&nbsp;<b><? echo _("Suche nach Lernmodulen");?></b></td>
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
	<form method="POST" action="<? echo $PHP_SELF; ?>">

	<table cellpadding="10" cellspacing="01" border="0" width="100%"><tr><td>
	<br>
	<? echo _("Hier k&ouml;nnen Sie nach Lernmodulen suchen."); ?>
	<br>
	<br>
	<br>
	<br>
	</td></tr>
	<tr valign="middle"><td class="steel1" align="center"><font size="-1"><? echo _("Suchbereich:");?>&nbsp;</font>
	<select name="search_area">
	<option value="1"<? if ($search_area == "1") echo " selected";?>><? echo _("Titel");?>
	<option value="2"<? if ($search_area == "2") echo " selected";?>><? echo _("Beschreibung");?>
	<option value="3"<? if ($search_area == "3") echo " selected";?>><? echo _("AutorIn");?>
	<option value="4"<? if (($search_area == "4") OR ($search_area == "")) echo " selected";?>><? echo _("Alle Felder");?>
	</select>&nbsp;
	</td><td class="steel1" align="center">
	<font size="-1"><? echo _("Schl&uuml;sselwort:");?>&nbsp;</font>
	<input type="TEXT" name="search_key" size="30" value="<? echo $search_key?>">&nbsp;
	</td><td class="steel1" align="center">
	<input type="IMAGE" <? echo makeButton("suchestarten", "src");?>>
	</td></tr>
	<tr><td colspan="3">
	<?
	if (isset($search_key) AND (strlen(trim($search_key)) < 2))
		echo "<b>" . _("Der Suchbegriff ist zu kurz") . "</b><br><br>";
	elseif (isset($search_key) AND (trim($search_key) != ""))
	{
		$erg_list = search_modules($search_key, $search_area);

		show_these_modules( $erg_list );
	}
	?>
	</td></tr></table>

	</form>
		</td>
		<td width="270" NOWRAP class="blank" align="center" valign="top">
		<? print_infobox ($infobox,"lernmodule.jpg");?>
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