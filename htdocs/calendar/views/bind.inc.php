<?
echo "<table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">\n";
echo "<tr><td class=\"blank\" width=\"90%\">\n";
echo "<table border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"1\" class=\"blank\">\n";

if (!empty($calendar_sess_control_data["view_prv"]))
	printf("<form action=\"%s?cmd=%s\" method=\"post\">", $PHP_SELF, $calendar_sess_control_data["view_prv"]);
else
	echo "<form action=\"$PHP_SELF?cmd=showweek\" method=\"post\">";
echo "\n<tr>\n";
echo "<th width=\"1%\" nowrap colspan=\"2\" align=\"center\">";
echo "&nbsp;<a href=\"gruppe.php\">";
echo "<img src=\"pictures/gruppe.gif\" alt=\"Gruppe &auml;ndern\" title=\"Gruppe &auml;ndern\" border=\"0\">";
echo "</a></th>\n";
echo "<th width=\"64%\" align=\"left\">";
printf("<a href=\"%s?cmd=bind&sortby=Name&order=%s\">%s</a></th>\n",
	$PHP_SELF, $order, "Name");
printf("<th width=\"7%%\"><a href=\"%s?cmd=bind&sortby=count&order=%s\">%s</a></th>\n",
	$PHP_SELF, $order, "Termine");
printf("<th width=\"13%%\"><b>%s</b></th>\n", "besucht");
printf("<th width=\"13%%\"><a href=\"%s?cmd=bind&sortby=status&order=%s\">%s</a></th>\n",
	$PHP_SELF,$order, "Status");
echo "<th width=\"2%\">&nbsp;</th>\n</tr>\n";

$css_switcher = new cssClassSwitcher();
echo $css_switcher->GetHoverJSFunction();
$css_switcher->enableHover();
$css_switcher->switchClass();

while($db->next_record()){
	$style = $css_switcher->getFullClass();
	echo "<tr" . $css_switcher->getHover() . "><td class=\"gruppe" . $db->f("gruppe") . "\">";
	echo "<img src=\"pictures/blank.gif\" alt=\"Gruppe\" border=\"0\" width=\"7\" height=\"12\"></td>\n";
	echo "<td$style>&nbsp; </td>";
	echo "<td$style><font size=\"-1\">";
	echo "<a href=\"" . $CANONICAL_RELATIVE_PATH_STUDIP;
	echo "seminar_main.php?auswahl=" . $db->f("Seminar_id") . "\">";
	echo format(htmlReady(mila($db->f("Name"))));
	echo "</a></font></td>\n";
	echo "<td$style align=\"center\"><font size=\"-1\">";
	echo $db->f("count");
	echo "</font></td>\n";
	if ($loginfilenow[$db->f("Seminar_id")] == 0) {
		echo "<td$style align=\"center\"><font size=\"-1\">";
		echo _("nicht besucht") . "</font></td>\n";
	}
	else{
		echo "<td$style align=\"center\"><font size=\"-1\">";
		echo date("d.m.Y", $loginfilenow[$db->f("Seminar_id")]);
		echo "</font></td>";
	}
	echo "<td$style align=\"center\"><font size=\"-1\">";
	echo $db->f("status");
	echo "</font></td>\n";
	if($calendar_user_control_data["bind_seminare"][$db->f("Seminar_id")])
		$is_checked = " checked";
	else
		$is_checked = "";
	echo "<td$style>";
	echo "<input type=\"checkbox\" name=\"sem[" . $db->f("Seminar_id")
		. "]\" value=\"TRUE\"$is_checked></td></tr>\n",
	$css_switcher->switchClass();
}

echo "<tr><td class=\"blank\">&nbsp;</td></tr>\n";
echo "<tr><td class=\"blank\" colspan=\"6\" align=\"center\">";
echo "&nbsp;<input type=\"image\" src=\"./pictures/buttons/auswaehlen-button.gif\" border=\"0\"></td></tr>\n";
// Dummy-Wert damit $sem auch ohne ausgewaehlte Seminare ausgewertet wird
echo "\n<input type=\"hidden\" name=\"sem[1]\" value=\"FALSE\">\n";
echo "<input type=\"hidden\" name=\"atime\" value=\"$atime\">";
echo "\n</form>\n";
echo "</table>";
echo "\n</td>\n";
echo "<td class=\"blank\" width=\"10%\" valign=\"top\">\n";
$info_content = array(array("kategorie" => _("Information:"),
											"eintrag" => array(	
												array("icon" => "pictures/ausruf_small.gif",
															"text" => _("Termine aus den ausgew&auml;hlten Veranstaltungen werden in Ihren Terminkalender &uuml;bernommen.")
											))));
										
print_infobox($info_content, "pictures/dates.jpg");
echo "</td></tr></table>\n";

echo "</tr><tr><td class=\"blank\">&nbsp;";
?>
