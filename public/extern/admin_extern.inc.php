<?
/**
* admin_extern.inc.php
*
*
*
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// admin_extern.inc.php
//
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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


include($ABSOLUTE_PATH_STUDIP. "seminar_open.php"); // initialise Stud.IP-Session
require_once($ABSOLUTE_PATH_STUDIP . "config.inc.php"); 		//wir brauchen die Seminar-Typen
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/extern_config.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/lib/extern_functions.inc.php");

// -- here you have to put initialisations for the current page

// Start of Output
include($ABSOLUTE_PATH_STUDIP . "html_head.inc.php"); // Output of html head
include($ABSOLUTE_PATH_STUDIP . "header.php");   // Output of Stud.IP head

require_once($ABSOLUTE_PATH_STUDIP . "msg.inc.php"); //Funktionen f&uuml;r Nachrichtenmeldungen
require_once($ABSOLUTE_PATH_STUDIP . "cssClassSwitcher.inc.php");
require_once($ABSOLUTE_PATH_STUDIP . "language.inc.php");

include($ABSOLUTE_PATH_STUDIP . "links_admin.inc.php");  //Linkleiste fuer admins

//get ID from an open Institute
if ($SessSemName[1])
	$range_id = $SessSemName[1];

echo "<table border=\"0\" class=\"blank\" align=\"center\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">\n";
echo "<tr valign=\"top\" align=\"center\">\n";
echo "<td class=\"topic\" align=\"left\" colspan=\"2\">&nbsp;<b>\n";
echo getHeaderLine($range_id) . " - ";
echo _("Verwaltung der externen Anzeigemodule");
foreach ($EXTERN_MODULE_TYPES as $key => $type) {
	if ($type["module"] == $mod) {
		echo " ({$EXTERN_MODULE_TYPES[$key]['name']})";
		break;
	}
}
echo "</b></td></tr>";
echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
if ($com != "info")
	echo "<tr><td class=\"blank\" align=\"center\" valign=\"top\" width=\"90%\">\n";
else
	echo "<tr><td class=\"blank\" align=\"center\" valign=\"top\" width=\"90%\" colspan=\"2\">\n";
echo "<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";


if ($com == "delete_sec") {
	$config = get_configuration($range_id, $config_id);

	$message = sprintf(_("Wollen Sie die Konfiguration <b>&quot;%s&quot;</b> des Moduls <b>%s</b> wirklich l&ouml;schen?"),
							$config["name"], $GLOBALS["EXTERN_MODULE_TYPES"][$config["type"]]["name"]);
	$message .= "<br><br><a href=\"$PHP_SELF?com=delete&config_id=$config_id\">";
	$message .= makeButton("ja2") . "</a>&nbsp; &nbsp;";
	$message .= "<a href=\"$PHP_SELF?list=TRUE&view=extern_inst\">";
	$message .= makeButton("nein") . "</a>";
	my_info($message, "blank", 1);
	print_footer();
	page_close();
	exit;
}

$css_switcher =& new cssClassSwitcher();

if ($com == "info") {
	include($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/views/extern_info_module.inc.php");
	print_footer();
	page_close();
	exit;
}

$element_command = FALSE;
if ($edit) {
	$element_commands = array('show', 'hide', 'move_left', 'move_right', 'show_group', 'hide_group', 'do_search_x');
	foreach ($element_commands as $element_command) {
		$element_command_form = $edit . "_" . $element_command;
		if ($_POST[$element_command_form]) {
			if (is_array($_POST[$element_command_form])) {
				$pos_tmp = array_keys($_POST[$element_command_form]);
				$pos = $pos_tmp[0];
			}
			$execute_command = $element_command;
			$com = "store";
		}
	}
}

if ($com == "new" || $com == "edit" || $com == "open" ||
		$com == "close" || $com == "store") {

	require_once($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_EXTERN . "/views/extern_edit_module.inc.php");
	print_footer();
	page_close();
	exit;
}

// Some browsers don't reload the site by clicking the same link twice again.
// So it's better to use different commands to do the same job.
if ($com == "set_default" || $com == "unset_default") {
	if (!set_default_config($range_id, $config_id)) {
		page_close();
		exit;
	}
}

if ($com == "delete") {
	if (!delete_config($range_id, $config_id)) {
		page_close();
		exit;
	}
}

if ($EXTERN_SRI_ENABLE_BY_ROOT && $_REQUEST['com'] == 'enable_sri'
		&& $perm->have_perm('root')) {
	enable_sri($SessSemName[1], $_REQUEST['sri_enable']);
}

echo "<table class=\"blank\" border=\"0\" width=\"95%\" ";
echo "align=\"left\" cellspacing=\"0\" cellpadding=\"0\">\n";
echo "<tr><td class=\"blank\" colspan=\"0\">\n<blockquote><b>";
echo _("Übersicht über alle angelegten Konfigurationen.");
echo "</b></blockquote>\n</td></tr>\n";
if ($EXTERN_SRI_ENABLE_BY_ROOT && $perm->have_perm('root')) {
	echo "<tr><td class=\"blank\">\n";
	echo "<form method=\"post\" action=\"$PHP_SELF?com=enable_sri\">\n";
	echo '<blockquote><font size="2">';
	echo _("SRI-Schnittstelle freigeben");
	echo ' <input type="checkbox" name="sri_enable" value="1"';
	if (sri_is_enabled($SessSemName[1])) {
		echo ' checked="checked"';
	}
	echo '/> &nbsp;<input type="image" border="0" align="absmiddle" ';
	echo makeButton('uebernehmen', 'src');
	echo "></font></blockquote></form>\n</td></tr>\n";
} else {
	echo "<tr><td class=\"blank\">&nbsp;</td></tr>\n";
}
echo "<tr><td class=\"blank\">\n";

$configurations = get_all_configurations($range_id);

$choose_module_form = "";
for ($i = 1; $i < sizeof($EXTERN_MODULE_TYPES); $i++) {
	if (sizeof($configurations[$EXTERN_MODULE_TYPES[$i]["module"]]) < $EXTERN_MAX_CONFIGURATIONS) {
		$choose_module_form .= "<option value=\"{$EXTERN_MODULE_TYPES[$i]['module']}\">"
				. $EXTERN_MODULE_TYPES[$i]['name'] . "</option>\n";
	}
	if ($configurations[$EXTERN_MODULE_TYPES[$i]["module"]])
		$have_config = TRUE;
}
// check for global configurations
if ($configurations[$EXTERN_MODULE_TYPES[0]["module"]])
	$have_config = TRUE;

echo "<blockquote><font size=\"2\">";
echo _("Neue globale Konfiguration anlegen.");
echo "&nbsp; <a href=\"$PHP_SELF?com=new&mod=Global\">" . makeButton("neuanlegen") . "</a>\n";
echo "</blockquote>";

if ($choose_module_form != "") {
	echo "<form method=\"post\" action=\"$PHP_SELF?com=new\">\n";
	echo "<blockquote><font size=\"2\">";
	$choose_module_form = "<select name=\"mod\">\n$choose_module_form</select>\n";
	printf(_("Neue Konfiguration f&uuml;r Modul %s anlegen."), $choose_module_form);
	echo "&nbsp; <input type=\"image\" " . makeButton("neuanlegen", "src") . " border=\"0\" align=\"absmiddle\">";
	echo "</font></blockquote>\n";
	echo "</form>\n";
}
else {
	echo "<blockquote><font size=\"2\">";
	echo _("Sie haben bereits für alle Module die maximale Anzahl von Konfigurationen angelegt. Um eine neue Konfiguration anzulegen, m&uuml;ssen Sie erst eine bestehende im gew&uuml;nschten Modul l&ouml;schen.");
	echo "</font></blockquote>\n";
}

echo "</td></tr>\n";

if (!$have_config) {
	echo "<tr><td class=\"blank\">\n<blockquote>\n<font size=\"2\">";
	echo _("Es wurden noch keine Konfigurationen angelegt.");
	echo "</font>\n</blockquote>\n</td></tr>\n";
}
else {
	echo "<tr><td height=\"20\" class=\"". $css_switcher->getHeaderClass() . "\" valign=\"bottom\">\n";
	echo "<font size=\"2\"><b>&nbsp;";
	echo _("Angelegte Konfigurationen");
	echo "</b></font>\n</td></tr>\n";
	$css_switcher->switchClass();
	echo "<tr><td" . $css_switcher->getFullClass() . ">&nbsp;</td></tr>\n";
	echo "<tr><td" . $css_switcher->getFullClass() . " valign=\"top\">\n";
	echo "<table width=\"90%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
	echo "<tr><td" . $css_switcher->getFullClass();
	echo ">&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
	echo "<td" . $css_switcher->getFullClass() . ">\n";

	$css_switcher_2 =& new CssClassSwitcher("", "topic");

	foreach ($EXTERN_MODULE_TYPES as $module_type) {
		if ($configurations[$module_type["module"]]) {
			$css_switcher_2->switchClass();
			echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
			echo "<tr>\n<td class=\"" . $css_switcher_2->getHeaderClass() . "\">";
			echo "<font size=\"2\"><b>&nbsp; ";

			if ($configurations[$module_type["module"]][$config_id])
				echo "<a name=\"anker\">\n";
			echo $module_type["name"];
			if ($configurations[$module_type["module"]][$config_id])
				echo "</a>\n";;

			echo "</b></font>\n</td></tr>\n";
			echo "<tr><td width=\"100%\" style=\"border-style:solid; border-width:1px; border-color:#000000;\">\n";

			echo "<table width=\"100%\" cellspacing=\"0\" cellpadding=\"2\" border=\"0\">\n";
			$css_switcher_2->resetClass();

			foreach ($configurations[$module_type["module"]] as $configuration) {
				$css_switcher_2->switchClass();
				echo "<tr><td" . $css_switcher_2->getFullClass() . " width=\"65%\"><font size=\"2\">";
				echo "&nbsp;" . $configuration["name"] . "</font></td>\n";
				echo "<td" . $css_switcher_2->getFullClass() . " width=\"5%\">";
				$tooltip = _("weitere Informationen anzeigen");
				echo "<a href=\"$PHP_SELF?com=info&config_id=" . $configuration["id"];
				echo '"><img src="';
				echo $GLOBALS['ASSETS_URL']."images/i.gif\" border=\"0\"" . tooltip($tooltip) . "></a>\n</td>\n";
				echo "<td" . $css_switcher_2->getFullClass() . " width=\"5%\">";

				// Switching for the is_default option. Read the comment above.
				if ($configuration["is_default"]) {
					echo "<a href=\"$PHP_SELF?list=TRUE&view=extern_inst&com=unset_default&config_id=";
					echo $configuration["id"] . "#anker\">";
					$tooltip = _("Standard entziehen");
					echo '<img src="';
					echo $GLOBALS['ASSETS_URL']."images/on_small.gif\" border=\"0\"" . tooltip($tooltip) . ">\n";
				}
				else {
					echo "<a href=\"$PHP_SELF?list=TRUE&view=extern_inst&com=set_default&config_id=";
					echo $configuration["id"] . "#anker\">";
					$tooltip = _("Standard zuweisen");
					echo '<img src="'.$GLOBALS['ASSETS_URL']."images/off_small_blank.gif\" border=\"0\"" . tooltip($tooltip) . ">";
				}

				echo "</a>\n</td>\n";
				echo "<td" . $css_switcher_2->getFullClass() . " align=\"center\" width=\"5%\">\n";
				echo "<a href=\"$PHP_SELF?com=delete_sec&config_id=" . $configuration["id"];
				echo '#anker"><img src="';
				$tooltip = _("Konfiguration löschen");
				echo $GLOBALS['ASSETS_URL']."images/trash.gif\" border=\"0\"" . tooltip($tooltip) . "></a>\n</td>\n";
				echo '<td' . $css_switcher_2->getFullClass() . " align=\"right\" width=\"20%\" ";
				echo ">\n";
				echo "<a href=\"$PHP_SELF?com=edit&mod=" . $module_type["module"];
				echo "&config_id=" . $configuration["id"] . "\"><img ";
				echo makeButton("bearbeiten", "src") . " border=\"0\"";
				$tooltip = _("Konfiguration bearbeiten");
				echo tooltip($tooltip) . "></a>&nbsp;\n</td></tr>\n";
			}

			$css_switcher_2->resetClass();
			echo "</table>\n";
			echo "</td></tr>\n";
			$css_switcher_2->switchClass();
			echo "<tr><td" . $css_switcher_2->getFullClass() . ">&nbsp;</td></tr>";
			echo "</table>\n";
		}

	}
	echo "</td></tr>\n";
	echo "</table>\n";
	echo "</td></tr><tr><td" . $css_switcher->getFullClass() . " colspan=\"2\">&nbsp;</td></tr>\n";
}
echo "</table></td></tr>\n";
echo "</table>\n</td>\n";
echo "<td class=\"blank\" width=\"10%\" valign=\"top\">\n";
echo "<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
$info_max_configs = sprintf(_("Sie können pro Modul maximal %s Konfigurationen anlegen."),
		$EXTERN_MAX_CONFIGURATIONS);

if ($configurations) {
	$info_set_default = _("Klicken Sie auf diesen Button, um eine Konfiguration zur Standard-Konfiguration zu erklären.");
	$info_no_default = _("Wenn Sie keine Konfiguration als Standard ausgew&auml;hlt haben, wird die Stud.IP-Konfiguration verwendet.");
	$info_is_default = _("Dieses Symbol kennzeichnet die Standard-Konfiguration, die zur Formatierung herangezogen wird, wenn sie beim Aufruf dieses Moduls keine Konfiguration angeben.");
	$info_further_info = _("Klicken Sie auf diesen Button um weitere Informationen über diese Konfiguration zu erhalten. Hier finden Sie auch die Links, über die Sie die Module in Ihrer Website einbinden können.");
	$info_content = array(
									array("kategorie" => "Information:",
												"eintrag" => array(
													array("icon" => "ausruf_small.gif",
																"text" => $info_max_configs
													),
													array("icon" => "on_small.gif",
																"text" => $info_is_default
													),
													array("icon" => "ausruf_small.gif",
																"text" => $info_no_default
													)
									)),
									array("kategorie" => "Aktion:",
		   									"eintrag" => array(
													array("icon" => "i.gif",
																"text" => $info_further_info,
													),
													array("icon" => "off_small_blank.gif",
																"text" => $info_set_default
													))
									));
}
else {
	$info_content = array(
									array("kategorie" => "Information:",
												"eintrag" => array(
													array("icon" => "ausruf_small.gif",
																"text" => $info_max_configs
													)
									)));
}

print_infobox($info_content, "einrichtungen.jpg");
print_footer();

?>
