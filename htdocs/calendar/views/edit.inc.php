<?
/**
* edit.inc.php
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>
* @version		$Id$
* @access		public
* @modulegroup	calendar
* @module		calendar
* @package	calendar
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
// edit.inc.php
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


echo "<table width=\"100%\" class=\"blank\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		
if (!empty($err)) {
	$error_sign = "<font color=\"#FF0000\" size=\"+2\"><b>&nbsp;*&nbsp;</b></font>";
	$error_message = sprintf(_("Bitte korrigieren Sie die mit %s gekennzeichneten Felder.%s"),
		$error_sign, $err_message);
	my_info($error_message, "blank", 2);
}

echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
echo "<table class=\"blank\" width=\"100%\" border=\"0\" cellspacing=\"5\" cellpadding=\"0\" align=\"center\">\n";
echo "<tr><td class=\"blank\" width=\"100%\" valign=\"top\">\n";
echo "<table class=\"blank\" width=\"99%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\">\n";

if (isset($atermin) && $atermin->getSeminarId())
	echo "<tr><td width=\"100%\" class=\"steel2\">";
else
	echo "<tr><td width=\"100%\" colspan=\"2\" class=\"steel2\">";


echo $edit_mode_out;

$css_switcher = new cssClassSwitcher();
$css_switcher->switchClass();

echo "\n</td></tr>\n";
echo "<form action=\"$PHP_SELF?cmd=edit\" method=\"post\">";
echo "<tr>\n<td class=\"steel1\" width=\"80%\" valign=\"top\">\n";
echo "<table width=\"100%\" cellpadding=\"4\" cellspacing=\"0\" border=\"0\">\n";
printf("<tr>\n<td class=\"%s\">\n", $css_switcher->getClass());

echo "<p>\n<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
echo "<tr valign=\"center\">\n<td><b>";
echo _("Beginn:") . " </b></td>\n<td> &nbsp; &nbsp;";
echo _("Tag");
echo "&nbsp; </td><td><input type=\"text\" name=\"start_day\" size=\"2\" maxlength=\"2\" value=\"$start_day\">\n";
echo "&nbsp;.&nbsp;<input type=\"text\" name=\"start_month\" size=\"2\" maxlength=\"2\" value=\"$start_month\">\n";
echo "&nbsp;.&nbsp;<input type=\"text\" name=\"start_year\" size=\"4\" maxlength=\"4\" value=\"$start_year\">\n";
echo "</td><td>&nbsp; &nbsp; &nbsp;";
echo _("Uhrzeit");
echo "&nbsp; </td><td><select name=\"start_h\" size=\"1\">\n";

for ($i = 0;$i < 24;$i++) {
	echo "<option";
	if ($i == $start_h)
		echo " selected";
	if ($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}
		
echo "</select>&nbsp;:&nbsp;<select name=\"start_m\" size=\"1\">\n";

for ($i = 0;$i < 60;$i += 5) {
	echo "<option";
	if ($i == $start_m)
		echo " selected";
	if ($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}

echo "</select>";
echo ($err["start_time"] ? $error_sign : "");
echo "</td>\n</tr><tr valign=\"center\"><td><b>";
echo _("Ende:") . " </b></td>\n<td> &nbsp; &nbsp;";
echo _("Tag");
echo "&nbsp; </td><td><input type=\"text\" name=\"end_day\" size=\"2\" maxlength=\"2\" value=\"$end_day\">\n";
echo "&nbsp;.&nbsp;<input type=\"text\" name=\"end_month\" size=\"2\" maxlength=\"2\" value=\"$end_month\">\n";
echo "&nbsp;.&nbsp;<input type=\"text\" name=\"end_year\" size=\"4\" maxlength=\"4\" value=\"$end_year\">\n";
echo "</td><td>&nbsp; &nbsp; &nbsp;";
echo _("Uhrzeit");
echo "&nbsp; </td><td><select name=\"end_h\" size=\"1\">\n";

for ($i = 0;$i < 24;$i++) {
	echo "<option";
	if ($i == $end_h)
		echo " selected";
	if ($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}

echo "</select>&nbsp;:&nbsp;<select name=\"end_m\" size=\"1\">\n";

for ($i = 0;$i < 60;$i += 5) {
	echo "<option";
	if ($i == $end_m)
		echo " selected";
	if ($i < 10)
		echo ">0$i";
	else
		echo ">$i";
}

echo "</select>";
echo ($err["end_time"] ? $error_sign : "");
echo "</td>\n</tr>\n</table>\n</p>\n</td>\n</tr>\n";

$css_switcher->switchClass();
echo "<tr><td class=\"" . $css_switcher->getClass() . "\">";
echo "<p>\n<table border=\"0\" width=\"100%%\" cellpadding=\"2\" cellspacing=\"2\">";
echo "<tr><td width=\"15%\"><b>";
echo _("Termin:") . " </b></td>";
echo "<td width=\"85%\">";
echo "<input type=\"text\" name=\"txt\" size=\"50\" maxlength=\"255\" value=\"$txt\"></input>";
printf("%s</td>\n", ($err["titel"] ? $error_sign : ""));
echo"</tr><tr>\n";
echo "<td width=\"15%%\"><b>";
echo _("Beschreibung:") . " </b></td>";
echo "<td width=\"85%\"><textarea name=\"content\" cols=\"48\" rows=\"5\" wrap=\"virtual\">";
echo $content;
echo "</textarea></td>\n";
echo "</tr>\n</table>\n</p>\n</td>\n</tr>\n<tr>";

$css_switcher->switchClass();
echo "<td class=\"" . $css_switcher->getClass() . "\">";
echo "\n<p>\n";
echo "<table border=\"0\" width=\"";
if (isset($atermin) && $atermin->getSeminarId())
	echo "<table border=\"0\" width=\"50%\" cellpadding=\"2\" cellspacing=\"2\">";
else
	echo "<table border=\"0\" width=\"80%\" cellpadding=\"2\" cellspacing=\"2\">";
echo "<tr>\n<td>\n<b>";
echo _("Kategorie:") . " </b>";
echo "\n</td><td>\n";
echo "<select name=\"cat\" size=\"1\">\n";

if (isset($atermin) && $atermin->getSeminarId()) {
	if (!isset($cat))
		$cat = 1;
	printf("<option value=\"%s\" selected>%s", $cat, $TERMIN_TYP[$cat]["name"]);
}
else {
	if (!isset($cat))
		$cat = 1;
	for ($i = 1;$i < sizeof($PERS_TERMIN_KAT);$i++) {
		printf("<option value=\"%s\"", $i);
		if ($cat == $i)
			echo " selected";
		printf(">%s\n", $PERS_TERMIN_KAT[$i]["name"]);
	}
}
		
echo "</select>\n</td>\n";

if (isset($atermin) && $atermin->getSeminarId())
	echo "<td>&nbsp</td>";
else {
	$info = _("Private Termine sind nur für Sie sichtbar. Öffentliche Termine werden auf ihrer internen Homepage auch anderen Nutzern bekanntgegeben.");
	echo "<td>&nbsp; &nbsp;<b>";
	echo _("Sichtbarkeit:") . "</b></td>\n";
	echo "<td nowrap=\"nowrap\"><input type=\"radio\" name=\"via\" value=\"private\"";
	if ($via == "private")
		echo " checked";
	echo ">&nbsp;" . _("privat") . "&nbsp;";
	echo "<input type=\"radio\" name=\"via\" value=\"public\"";
	if ($via == "public")
		echo " checked";
	echo ">&nbsp;" . _("&ouml;ffentlich");
	echo "&nbsp; &nbsp;<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\"";
	echo tooltip($info, TRUE, TRUE) . "></td>\n";
}

echo "</tr><tr>\n";

echo "<td><b>";
echo _("Raum:") . "</b></td>\n";
echo "<td><input type=\"text\" name=\"loc\" size=\"30\" maxlength=\"255\" value=\"$loc\"></td>\n";
if (isset($atermin) && $atermin->getSeminarId())
	echo "<td>&nbsp</td>";
else {
	echo "<td>&nbsp; &nbsp;<b>";
	echo _("Priorit&auml;t:") . "</b>\n</td><td>\n";
	echo "<select name=\"priority\" size=\"1\">\n";
	for ($i = 1; $i < 6; $i++) {
		echo "<option value=\"$i\"";
		if ($priority == $i)
			echo " selected";
		echo " />$i\n";
	}
	echo "</select></td>\n";
}

echo "</tr>\n</table>\n</p></td></tr>\n";

if ($mod == "MONTHLY" || $mod == "YEARLY") {
	$form_week_arr = array(
			"1" => _("ersten"),
			"2" => _("zweiten"),
			"3" => _("dritten"),
			"4" => _("vierten"),
			"5" => _("letzten")
	);
	
	$form_day_arr = array(
			"1" => _("Montag"),
			"2" => _("Dienstag"),
			"3" => _("Mittwoch"),
			"4" => _("Donnerstag"),
			"5" => _("Freitag"),
			"6" => _("Samstag"),
			"7" => _("Sonntag")
	);
	
	$form_month_arr = array(
			"1" => _("Januar"),
			"2" => _("Februar"),
			"3" => _("M&auml;rz"),
			"4" => _("April"),
			"5" => _("Mai"),
			"6" => _("Juni"),
			"7" => _("Juli"),
			"8" => _("August"),
			"9" => _("September"),
			"10" => _("Oktober"),
			"11" => _("November"),
			"12" => _("Dezember")
	);
}

switch ($mod) {
	case "DAYLY":
		$css_switcher->switchClass();
		echo "<tr>\n<td class=\"" . $css_switcher->getClass() . "\">\n";
		echo "<p><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">\n";
		echo "<tr><td width=\"30%\"><input type=\"radio\" name=\"type_d\" value=\"dayly\"";
		if ($type_d == "dayly" || $type_d == "")
			echo " checked";
		echo ">&nbsp;<b>" . _("Alle") . "</b> &nbsp;";
		echo "<input type=\"text\" name=\"lintervall_d\" size=\"3\" maxlength=\"3\" value=\"";
		echo ($lintervall_d ? $lintervall_d : "1");
		echo "\">&nbsp;" . _("Tage");
		echo ($err["lintervall_d"] ? $error_sign : "");
		echo "</td>\n";
		echo "<td width=\"70%\"><input type=\"radio\" name=\"type_d\" value=\"wdayly\"";
		if ($type_d == "wdayly")
			echo " checked";
		echo ">&nbsp;<b>" . _("Jeden Werktag") ."</b></td>";
		echo "</tr>\n</table></p>\n</td></tr>\n";
		break;
		
	case "WEEKLY":
		if (!$wdays)
			$wdays = array();
		$css_switcher->switchClass();
		echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
		echo "<p><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">\n";
		echo "<tr><td colspan=\"5\" valign=\"center\">\n";
		$out_1 = '<input type="text" name="lintervall_w" size="3" maxlength="3" value="';
		$out_1 .= ($lintervall_w ? $lintervall_w : "1");
		$out_1 .= '">';
		$out_2 = ($err["lintervall_w"] ? $error_sign : "");
		$out_2 .= "</td>\n</tr><tr>\n";
		$out_2 .= '<td rowspan="2" width="20%" align="center">';
		printf(_("<b>Alle &nbsp;</b>%s<b>&nbsp; Wochen</b>%s<b>am:&nbsp;</b>"), $out_1, $out_2);
		echo "</td>\n<td width=\"20%\">\n";
		echo "<input type=\"checkbox\" name=\"wdays[]\" value=\"1\"";
		if(in_array(1, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Montag") . "</td>\n";
		echo "<td width=\"20%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"2\"";
		if(in_array(2, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Dienstag") . "</td>\n";
		echo "<td width=\"20%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"3\"";
		if(in_array(3, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Mittwoch") . "</td>\n";
		echo "<td width=\"20%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"4\"";
		if(in_array(4, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Donnerstag") . "</td>\n";
		echo "</tr><tr>\n";
		echo "<td width=\"20%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"5\"";
		if(in_array(5, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Freitag") . "</td>\n";
		echo "<td width=\"20%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"6\"";
		if(in_array(6, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Samstag") . "</td>\n";
		echo "<td colspan=\"2\" width=\"40%\"><input type=\"checkbox\" name=\"wdays[]\" value=\"7\"";
		if(in_array(7, $wdays)) echo " checked";
		echo ">&nbsp;" . _("Sonntag") . "</td>\n";
		echo "</tr>\n</table></p>\n</td></tr>\n";
		break;
		
	case "MONTHLY":
		$css_switcher->switchClass();
		echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
		echo "<p><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">\n";
		echo "<tr><td width=\"15%\" nowrap=\"nowrap\"><input type=\"radio\" name=\"type_m\" value=\"day\"";
		if ($type_m == "day" || $type_m == "") echo " checked";
		echo ">&nbsp;<b>";
		$out_1 = "&nbsp;</td>\n<td width=\"10%\">";
		$out_1 .= "<input type=\"text\" name=\"day_m\" size=\"2\" maxlength=\"2\" value=\"";
		$out_1 .= ($day_m ? "$day_m" : "$start_day");
		$out_1 .= "\">" . ($err["day_m"] ? $error_sign : "") . "&nbsp;.&nbsp; <b>";
		$out_2 = "&nbsp;</td>\n<td width=\"10%\">";
		$out_2 .= "<input type=\"text\" name=\"lintervall_m1\" size=\"3\" maxlength=\"3\" value=\"";
		$out_2 .= ($lintervall_m1 ? "$lintervall_m1" : "1");
		$out_2 .= "\">" . ($err["lintervall_m1"] ? $error_sign : "") . "&nbsp;";
		printf(_("<b>Wiederholt am</b>%s<b>alle</b>%sMonate"), $out_1, $out_2);
		echo "</td>\n<td width=\"65%\">&nbsp;</td>\n";
		echo "</tr><tr>\n";
		echo "<td nowrap=\"nowrap\"><input type=\"radio\" name=\"type_m\" value=\"wday\"";
		if ($type_m == "wday") echo " checked";
		echo ">&nbsp;<b>" . _("Jeden") . "</b>&nbsp;</td>\n";
		echo "<td><select name=\"sintervall_m\" size=\"1\">\n";
		
		reset($form_week_arr);
		foreach ($form_week_arr as $key => $value) {
			echo "<option value=\"$key\"";
			if($sintervall_m == $key)
				echo " selected";
			echo ">$value\n";
		}
		
		echo "</select>\n</td><td>\n";
		echo "<select name=\"wday_m\" size=\"1\">\n";
		
		reset($form_day_arr);
		foreach ($form_day_arr as $key => $value) {
			echo "<option value=\"$key\"";
			if($wday_m == $key)
				echo " selected";
			echo ">$value\n";
		}
		
		echo "</select></td>\n";
		echo "<td>&nbsp;<b>" . _("alle");
		echo "</b> &nbsp;<input type=\"text\" name=\"lintervall_m2\" size=\"3\" maxlength=\"3\" value=\"";
		echo ($lintervall_m2 ? $lintervall_m2 : "1");
		echo "\">" . ($err["lintervall_m2"] ? $error_sign : "");
		echo "&nbsp;" . _("Monate") . "</td></tr>\n</table></p>\n</td></tr>\n";
		break;
		
	case "YEARLY":
		if(!$month_y1)
			$month_y1 = $start_month;
		if(!$month_y2)
			$month_y2 = $start_month;
			
		$css_switcher->switchClass();
		echo "<tr><td class=\"" . $css_switcher->getClass() . "\">\n";
		echo "<p><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\">\n";
		echo "<tr><td width=\"10%\" nowrap=\"nowrap\"><input type=\"radio\" name=\"type_y\" value=\"day\"";
		if ($type_y == "day" || $type_y == "") echo " checked";
		echo ">&nbsp;<b>" . _("Jeden") . "</b>&nbsp; </td>\n";
		echo "<td nowrap=\"nowrap\"><input type=\"text\" name=\"day_y\" size=\"2\" maxlength=\"2\" value=\"";
		echo ($day_y ? $day_y : $start_day);
		echo "\">" . ($err["day_y"] ? $error_sign : "");
		echo "&nbsp;.&nbsp;\n";
		echo "<select name=\"month_y1\" size=\"1\">\n";
		
		reset($form_month_arr);
		foreach ($form_month_arr as $key => $value) {
			echo "<option value=\"$key\"";
			if($month_y1 == $key)
				echo " selected";
			echo ">$value\n";
		}
		
		echo "</select></td></tr>\n";
		echo "<tr><td width=\"10%\" nowrap=\"nowrap\"><input type=\"radio\" name=\"type_y\" value=\"wday\"";
		if ($type_y == "wday") echo " checked";
		echo ">&nbsp;<b>";
		$out_1 = "</b>&nbsp; </td>\n";
		$out_1 .= "<td nowrap=\"nowrap\"><select name=\"sintervall_y\" size=\"1\">\n";
		
		reset($form_week_arr);
		foreach ($form_week_arr as $key => $value) {
			$out_1 .= "<option value=\"$key\"";
			if($sintervall_y == $key)
				$out_1 .= " selected";
			$out_1 .= ">$value\n";
		}
		
		$out_1 .= "</select>\n<select name=\"wday_y\" size=\"1\">\n";
		
		reset($form_day_arr);
		foreach ($form_day_arr as $key => $value) {
			$out_1 .= "<option value=\"$key\"";
			if ($wday_y == $key)
				$out_1 .= " selected";
			$out_1 .= ">$value\n";
		}
		
		$out_1 .= "</select>&nbsp;<b>";
		printf(_("Jeden%sim"), $out_1);
		echo "</b>&nbsp;\n<select name=\"month_y2\" size=\"1\">\n";
		
		reset($form_month_arr);
		foreach ($form_month_arr as $key => $value) {
			echo "<option value=\"$key\"";
			if ($month_y2 == $key)
				echo " selected";
			echo ">$value\n";
		}
		echo "</select></td></tr>\n</table></p>\n</td></tr>\n";
		break;
		
}
	
if ($mod != "SINGLE") {
	$css_switcher->switchClass();
	echo "<tr><td class=\"" . $css_switcher->getClass() . "\">";
	echo "<p><table>\n<tr><td><b>";
	echo _("Wiederholung endet:") . "</b>&nbsp; </td>\n";
	echo "<td><select name=\"exp_c\" size=\"1\">\n";
	echo "<option value=\"never\"";
	if ($exp_c == "never") echo " selected";
	echo ">" . _("nie");
	echo "\n<option value=\"date\"";
	if ($exp_c == "date") echo " selected";
	echo ">" . _("am rechts anzugebenden Datum");
	echo "\n</select>&nbsp; &nbsp;</td>\n";
	echo "<td><input type=\"text\" size=\"2\" maxlength=\"2\" name=\"exp_day\" value=\"";
	echo (($exp_day && $exp_c == "date") ? $exp_day : "TT");
	echo "\">&nbsp;.&nbsp;</td>\n";
	echo "<td><input type=\"text\" size=\"2\" maxlength=\"2\" name=\"exp_month\" value=\"";
	echo (($exp_month && $exp_c == "date") ? $exp_month : "MM");
	echo "\">&nbsp;.&nbsp;</td>\n";
	echo "<td><input type=\"text\" size=\"4\" maxlength=\"4\" name=\"exp_year\" value=\"";
	echo (($exp_year && $exp_c == "date") ? $exp_year : "JJJJ");
	echo "\">" . ($err["exp_time"] ? $error_sign : "") .  "</td>\n";
	echo "</tr>\n</table></p>\n</td></tr>\n";
}

echo "</table>\n";
	
if (isset($atermin) && $atermin->getSeminarId()) {
	$db = new DB_Seminar();
	$query = "SELECT name FROM seminare WHERE Seminar_id='".$atermin->getSeminarId()."'";
	$db->query($query);
	$db->next_record();
	$link_to_seminar = "<a href=\"" . $CANONICAL_RELATIVE_PATH_STUDIP
										. "seminar_main.php?auswahl=" . $atermin->getSeminarId()
										. "\">" . htmlReady($db->f("name")) . "</a>";
	$permission = get_perm($atermin->getSeminarId());
	
	$info_text_1 = sprintf(_("Dieser Termin geh&ouml;rt zur Veranstaltung:<p>%s</p>Veranstaltungstermine k&ouml;nnen nicht im pers&ouml;nlichen Terminkalender bearbeitet werden.")
			, $link_to_seminar);
	$info_text_2 = sprintf(_("<a href=\"%s?cmd=bind\">W&auml;hlen</a> Sie aus, welche Veranstaltungstermine automatisch in Ihrem Terminkalender angezeigt werden sollen.")
			, $PHP_SELF);
	if ($permission == "tutor" || $permission == "dozent") {
		$link_to_seminar = sprintf("<a href=\"%sadmin_dates.php?range_id=%s&show_id=%s\">"
				, $CANONICAL_RELATIVE_PATH_STUDIP, $atermin->getSeminarId(), $atermin->getId());
		$info_text_3 = sprintf(_("Um diesen Termin zu bearbeiten, wechseln Sie bitte in die %sTerminverwaltung</a>.")
				, $link_to_seminar);
		$info_content = array(	
										array("kategorie" => _("Information:"),
													"eintrag" => array(	
													array("icon" => "/pictures/ausruf_small.gif",
																"text" => $info_text_1
																)
													)
										),
										array("kategorie" => _("Aktion:"),
		   										"eintrag" => array(	
													array("icon" => "/pictures/meinesem.gif",
																"text" => $info_text_2
																),
													array("icon" => "/pictures/admin.gif",
																"text" => $info_text_3
																)
													)
										)
									);
	}
	else {
		$info_content = array(	
										array("kategorie" => "Information:",
													"eintrag" => array(	
													array("icon" => "/pictures/ausruf_small.gif",
																"text" => $info_text_1
																)
													)
										),
										array("kategorie" => "Aktion:",
		  											"eintrag" => array(	
													array (	"icon" => "/pictures/meinesem.gif",
																	"text" => $info_text_2
																)
													)
										)
									);
	}

	echo "</td></tr></table>\n</td>\n";
	echo "<td class=\"blank\" align=\"center\" rowspan=\"1\" valign=\"top\" width=\"20%\">\n";
	echo "<table class=\"blank\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" valign=\"top\">\n";
	echo "<tr><td class=\"blank\" align=\"center\" valign=\"top\">\n";
	print_infobox($info_content, "/pictures/dates.jpg");
	echo "</td></tr>\n</table>\n<tr><td class=\"blank\">\n";
	echo "</td></tr>\n";
}
else {
	echo "</td><td width=\"20%\" valign=\"top\" class=\"steel1\">\n";
	echo "<table width=\"100%\" border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
	echo "<tr><td class=\"steel1\" align=\"center\"><b>";
	echo _("Wiederholung") . "</b></td></tr>\n";
	echo "<tr><td class=\"steel1\" align=\"center\">\n";
	if ($repeat["type"] == "SINGLE" || $mod == "SINGLE")
		echo "<input type=\"image\" name=\"mod_s\" " . makeButton("keine2", "src") . " border=\"0\">\n";
	else
		echo "<input type=\"image\" name=\"mod_s\" " . makeButton("keine", "src") . " border=\"0\">\n";
	echo "</td></tr>\n";
	echo "<tr><td class=\"steel1\" align=\"center\">\n";
	if ($repeat["type"] == "DAYLY" || $mod == "DAYLY")
		echo "<input type=\"image\" name=\"mod_d\" " . makeButton("taeglich2", "src") . " border=\"0\">\n";
	else
		echo "<input type=\"image\" name=\"mod_d\" " . makeButton("taeglich", "src") . " border=\"0\">\n";
	echo "</td></tr>\n";
	echo "<tr><td class=\"steel1\" align=\"center\">\n";
	if ($repeat["type"] == "WEEKLY" || $mod == "WEEKLY")
		echo "<input type=\"image\" name=\"mod_w\" " . makeButton("woechentlich2", "src") . " border=\"0\">\n";
	else
		echo "<input type=\"image\" name=\"mod_w\" " . makeButton("woechentlich", "src") . " border=\"0\">\n";
	echo "</td></tr>\n";
	echo "<tr><td class=\"steel1\" align=\"center\">\n";
	if ($repeat["type"] == "MONTHLY" || $mod == "MONTHLY")
		echo "<input type=\"image\" name=\"mod_m\" " . makeButton("monatlich2", "src") . " border=\"0\">\n";
	else
		echo "<input type=\"image\" name=\"mod_m\" " . makeButton("monatlich", "src") . " border=\"0\">\n";
	echo "</td></tr>\n";
	echo "<tr><td class=\"steel1\" align=\"center\">\n";
	if($repeat["type"] == "YEARLY" || $mod == "YEARLY")
		echo "<input type=\"image\" name=\"mod_y\" " . makeButton("jaehrlich2", "src") . " border=\"0\">\n";
	else
		echo "<input type=\"image\" name=\"mod_y\" " . makeButton("jaehrlich", "src") . " border=\"0\">\n";
	echo "</td></tr>\n";
	echo "<tr><td class=\"steel1\"><br>&nbsp;<br></td></tr>\n";

	if ($atime && !$termin_id) {
		echo "<tr><td class=\"steel1\" align=\"center\">\n";
		echo "<input type=\"hidden\" name=\"atime\" value=\"$atime\">\n";
		echo "<input type=\"hidden\" name=\"mod_err\" value=\"$mod_err\">\n";
		echo "<input type=\"hidden\" name=\"mod_prv\" value=\"$mod\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"add\">\n";
		echo "<input type=\"image\" " . makeButton("terminspeichern", "src"). " border=\"0\"></td></tr>\n";
		echo "</form>\n";
	}
	else {
		echo "<tr><td class=\"steel1\" align=\"center\">\n";
		echo "<input type=\"hidden\" name=\"termin_id\" value=\"$termin_id\">\n";
		echo "<input type=\"hidden\" name=\"atime\" value=\"$atime\">\n";
		echo "<input type=\"hidden\" name=\"mod_err\" value=\"$mod_err\">\n";
		echo "<input type=\"hidden\" name=\"mod_prv\" value=\"$mod\">\n";
		echo "<input type=\"hidden\" name=\"cmd\" value=\"add\">\n";
		echo "<input type=\"image\" " . makeButton("terminaendern", "src"). " border=\"0\"></td></tr>\n";
		echo "<tr><td class=\"steel1\">&nbsp;</td></tr>\n";
		echo "</form>\n";
	
		echo "<tr><td class=\"steel1\" align=\"center\"><form action=\"$PHP_SELF?cmd=del\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"termin_id\" value=\"$termin_id\">\n";
		echo "<input type=\"hidden\" name=\"atime\" value=\"$atime\">\n";
		echo "<input type=\"image\" " . makeButton("loeschen", "src"). " border=\"0\"></form>\n</td></tr>\n";
	}
}
echo "</table></td></tr></table><br />\n";
echo "</td></tr></table>\n";

?>
