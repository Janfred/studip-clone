<?
/**
* sync.inc.php
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version		$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	sync
*/
/**
* workaround for PHPDoc
*
* Use this if module contains no elements to document !
* @const PHPDOC_DUMMY
*/
define("PHPDOC_DUMMY",true);
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// sync.inc.php
//
// Copyright (c) 2003 Peter Tienel <pthienel@web.de> 
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


require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarImportFile.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarParserICalendar.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarExportFile.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarWriterICalendar.class.php");
require_once("$ABSOLUTE_PATH_STUDIP$RELATIVE_PATH_CALENDAR/lib/sync/CalendarSynchronizer.class.php");
require_once("$ABSOLUTE_PATH_STUDIP/msg.inc.php");
set_time_limit(0);
if ($experiod != 'period') {
	unset($exstartmonth);
	unset($exstartday);
	unset($exstartyear);
	unset($exendtmonth);
	unset($exendday);
	unset($exendyear);
}

$err = array();
if ($experiod == 'period') {
	if (!$exstart = check_date($exstartmonth, $exstartday, $exstartyear, 0, 0))
		$err['exstart'] = TRUE;
	if (!$exend = check_date($exendmonth, $exendday, $exendyear, 23, 59))
		$err['exend'] = TRUE;
	if ($exstart >= $exend)
		$err['exend'] = TRUE;
}

if (($expmod != 'exp' && $expmod != 'imp' && $expmod != 'sync') || ($expmod == 'exp' && !empty($err))) {
	require("$ABSOLUTE_PATH_STUDIP/html_head.inc.php");
	
	print_js_import();
	echo "\n<body onUnLoad=\"upload_end()\">"; 

	require("$ABSOLUTE_PATH_STUDIP/header.php");
	require($ABSOLUTE_PATH_STUDIP . $RELATIVE_PATH_CALENDAR . "/views/navigation.inc.php");
}

if (($expmod != 'exp' && $expmod != 'imp' && $expmod != 'sync') || ($expmod == 'exp' && !empty($err))) {

	echo "<table border=\"0\" class=\"blank\" cellspacing=\"0\" cellpadding=\"5\" width=\"100%\">\n";
	
	if (!empty($err)) {
		$error_sign = "<font color=\"#FF0000\" size=\"+2\"><b>&nbsp;*&nbsp;</b></font>";
		$error_message = sprintf(_("Bitte korrigieren Sie die mit %s gekennzeichneten Felder.%s"),
			$error_sign, $err_message);
		my_info($error_message, "blank", 2);
	}
	
	$info = array (	
		array ("kategorie"  => "Information:",
			"eintrag" => array	(	
				array (	"icon" => "pictures/ausruf_small.gif",
					"text"  => _("Sie k&ouml;nnen Termindaten importieren, exportieren und synchronisieren.")
				),
			)
		),
	);
	
	echo "<tr valign=\"top\">\n";
	echo "<td width=\"99%\" nowrap class=\"blank\">\n";
	echo "<table align=\"center\" width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"5\" cellspacing=0>\n";
	
	if ($expmod == 'syncrec') {
		
		$info = array (	
			array ("kategorie"  => "Information:",
				"eintrag" => array	(	
					array (	"icon" => "pictures/ausruf_small.gif",
						"text"  => _("Sie k&ouml;nnen Termindaten importieren, exportieren und synchronisieren.")
					),
				)
			),
		);
		
		$send_sync = "{$CANONICAL_RELATIVE_PATH_STUDIP}sendfile.php"
				. "?type=2&file_id=$tmpfile&file_name=$file&force_download=1";
		
		echo "<tr><th align=\"left\" width=\"100%\">\n<font size=\"-1\">";
		echo _("Herunterladen der synchronisierten Kalenderdaten")."</font>\n</th></tr>\n";
		$send_file = "";
		$params['form'] = "<form action=\"$send_sync\" method=\"post\">\n";
		$params['content'] = _("Klicken Sie auf den Button, um die Datei mit den synchronisierten Kalenderdaten herunterzuladen.")
				. _("Die Daten liegen ebenfalls in einer iCalendar-Datei vor, die Sie in Ihren lokalen Terminkalender (z.B. MS Outlook) importieren können.");
		$params['button'] = "<input type=\"image\" " . makeButton("herunterladen", "src"). " border=\"0\">";
		print_cell($params);
		echo "</table\n</td>\n";
		
		echo "<td class=\"blank\" align=\"right\" valign=\"top\" width=\"1%\" valign=\"top\">\n";
		print_infobox($info, "pictures/dates.jpg");
	}
	else {
	
		echo "<tr><th align=\"left\" width=\"100%\">\n<font size=\"-1\">";
		echo _("Exportieren Ihrer Kalenderdaten")."</font>\n</th></tr>\n";
		
		$params['form'] = "<form action=\"$PHP_SELF?cmd=export&atime=$atime\" method=\"post\">\n";
		$params['content'] = _("Bitte w&auml;hlen Sie, welche Termine exportiert werden sollen:") . "</font></div>\n"
				. "<br>&nbsp; &nbsp; <select name=\"extype\" size=\"1\">\n"
				. "<option value=\"PERS\"" . ($extype == 'PERS' ? 'selected="selected"' : '')
				. ">" . _("Nur meine pers&ouml;nlichen Termine") . "</option>\n"
				. "<option value=\"SEM\"" . ($extype == 'SEM' ? 'selected="selected"' : '')
				. ">" . _("Nur meine Veranstaltungstermine") . "</option>\n"
				. "<option value=\"ALL\"" . ($extype == 'ALL' ? 'selected="selected"' : '')
				. ">" . _("Alle Termine") . "</option>\n"
				. "</select><br>&nbsp;\n<div><font size=\"-1\">"
				. _("Geben Sie an, aus welchem Zeitbereich Termine exportiert werden sollen:")
				. "</div><br>\n&nbsp; &nbsp; <input type=\"radio\" name=\"experiod\" value=\"all\" ";
		if ($experiod != 'period')
			$params['content'] .= "checked=\"checked\"";
		$params['content'] .= ">\n"
				. "&nbsp;" . _("Alle Termine") . "<br>\n"
				. "&nbsp; &nbsp; <input type=\"radio\" name=\"experiod\" value=\"period\" ";
		if ($experiod == 'period')
			$params['content'] .= "checked=\"checked\"";
		$params['content'] .= ">\n"
				. "&nbsp;"
				. sprintf(_("Nur Termine vom:%sbis zum:"), 
							" &nbsp <input type=\"text\" name=\"exstartday\" size=\"2\" maxlength=\"2\" value=\""
						. ($exstartday ? $exstartday : date("d", time())) . "\">.&nbsp;\n"
						. "<input type=\"text\" name=\"exstartmonth\" size=\"2\" maxlength=\"2\" value=\""
						. ($exstartmonth ? $exstartmonth : date("m", time())) . "\">.&nbsp;\n"
						. "<input type=\"text\" name=\"exstartyear\" size=\"4\" maxlength=\"4\" value=\""
						. ($exstartyear ? $exstartyear : date("Y", time()) - 1) . "\">"
						. ($err['exstart'] ? $error_sign : '')
						. "&nbsp &nbsp; \n")
				. " &nbsp; <input type=\"text\" name=\"exendday\" size=\"2\" maxlength=\"2\" value=\""
				. ($exendday ? $exendday : date("d", time())) . "\">.&nbsp;\n"
				. "<input type=\"text\" name=\"exendmonth\" size=\"2\" maxlength=\"2\" value=\""
				. ($exendmonth ? $exendmonth : date("m", time())) . "\">.&nbsp;\n"
				. "<input type=\"text\" name=\"exendyear\" size=\"4\" maxlength=\"4\" value=\""
				. ($exendyear ? $exendyear : date("Y", time()) + 1) . "\">\n"
				. ($err['exend'] ? $error_sign : '');
		$params['button'] = "<input type=\"image\" " . makeButton("export", "src"). " border=\"0\">";
		$params['expmod'] = "exp";
		print_cell($params);
		
		echo "<tr><th colspan=\"2\" align=\"left\" width=\"100%\">\n<font size=\"-1\">";
		echo _("Importieren Ihrer Kalenderdaten")."</font>\n</th></tr>\n";
		
		$params['form'] = "<form action=\"$PHP_SELF?cmd=export&atime=$atime\" method=\"post\" "
				. "enctype=\"multipart/form-data\" name=\"import_form\">\n";
		$params['content'] = _("Sie k&ouml;nnen Termine importieren, die sich in einer iCalendar-Datei befinden.")
				. "<br><br>" . _("Klicken Sie auf \"Durchsuchen\", um eine Datei auszuwählen.")
				. "</div>\n<br>&nbsp; &nbsp; <input type=\"file\" name=\"importfile\" size=\"40\">\n";
		$params['button'] = "<input value=\"Senden\" type=\"image\" " . makeButton("dateihochladen", "src"). " onClick=\"return upload_start(document.import_form);\" "
				. "name=\"create\" border=\"0\">\n";
		$params['expmod'] = 'imp';
		print_cell($params);
		
		echo "<tr><th colspan=\"2\" align=\"left\" width=\"100%\">\n<font size=\"-1\">";
		echo _("Synchronisieren Ihrer Kalenderdaten")."</font>\n</th></tr>\n";
		
		$params['form'] = "<form action=\"$PHP_SELF?cmd=export&atime=$atime\" method=\"post\" "
				. "enctype=\"multipart/form-data\" name=\"sync_form\">\n";
		$params['content'] = _("Sie k&ouml;nnen Termine synchronisieren, die sich in einer iCalendar-Datei befinden.")
				. "<br><br>" . _("Klicken Sie auf \"Durchsuchen\", um eine Datei auszuwählen.")
				. "</div>\n<br>&nbsp; &nbsp; <input type=\"file\" name=\"importfile\" size=\"40\">\n";
		$params['button'] = "<input value=\"Senden\" type=\"image\" " . makeButton("dateihochladen", "src"). " onClick=\"return upload_start(document.sync_form);\" "
				. "name=\"create\" border=\"0\">\n";
		$params['expmod'] = 'sync';
		print_cell($params);
		
		/*
		$info_content = array(
											array("kategorie" => _("Information:"),
														"eintrag" => array(	
														array("icon" => "pictures/ausruf_small.gif",
																	"text" => $info_text_1
																	)
														)
											),
											array("kategorie" => _("Aktion:"),
			   										"eintrag" => array(	
														array("icon" => "pictures/meinesem.gif",
																	"text" => $info_text_2
																	),
														array("icon" => "pictures/admin.gif",
																	"text" => $info_text_3
																	)
														)
											)
										);
		
		*/
		echo "</table\n</td>\n";
		
		echo "<td class=\"blank\" align=\"right\" valign=\"top\" width=\"1%\" valign=\"top\">\n";
		print_infobox($info, "pictures/dates.jpg");
	}
	
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
	echo "</table>\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td></tr></table>\n";
}
elseif ($expmod == 'exp' && empty($err)) {
	
	if ($experiod != 'period') {
		$exstart = 0;
		$exend = 2114377200;
	}
	
	switch ($extype) {
		case 'ALL':
			$extype = 'ALL_EVENTS';
			break;
		case 'SEM':
			$extype = 'SEMINAR_EVENTS';
			break;
		default:
			$extype = 'CALENDAR_EVENTS';
	}
	
	$export = new CalendarExportFile(new CalendarWriterICalendar());
	$export->exportFromDatabase($user->id, $exstart, $exend, $extype);
	$export->sendFile();

}
elseif ($expmod == 'imp') {

	$import =& new CalendarImportFile(new CalendarParserICalendar(),
			$HTTP_POST_FILES["importfile"]);
	
	$import->importIntoDatabase();
	
	header("Location: $PHP_SELF?cmd=export&atime=$atime&");
	
}
elseif ($expmod == 'sync') {

	$import =& new CalendarImportFile(new CalendarParserICalendar(),
			$HTTP_POST_FILES["importfile"]);
	
	$export =& new CalendarExportFile(new CalendarWriterICalendar());
	
	$synchronizer =& new CalendarSynchronizer($import, $export);
	$synchronizer->synchronize();
	$location = "Location: $PHP_SELF?cmd=export&expmod=syncrec&tmpfile="
			. $export->getTempFileName() . "&file=" . $export->getFileName() . "&atime=$atime";
	header($location);

}

function print_cell ($params) {
	
	echo "<tr><td width=\"100%\" class=\"steel1\">\n";
	echo $params['form'];
	echo "<div><font size=\"-1\">";
	echo $params['content'];
	echo "<div style=\"text-align:center; vertical-align:center;\">\n";
	echo "&nbsp;\n";
	echo "<div style=\"text-align:center; vertical-align:center;\">\n";
	echo $params['button'];
	echo "<input type=\"hidden\" name=\"expmod\" value=\"{$params['expmod']}\">\n";
	echo "</div>\n</form>\n</td></tr>\n";
	
}

?>
