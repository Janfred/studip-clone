<?

function export_form($range_id, $ex_type = "", $filename = "", $format = "")
{
	global $output_formats, $ABSOLUTE_PATH_STUDIP , $PATH_EXPORT;
	require_once ($ABSOLUTE_PATH_STUDIP . $PATH_EXPORT . "/export_xslt_vars.inc.php");
	$export_string .= "<form action=\"" . "export.php\" method=\"post\">";
	$export_string .= "<table width=\"100%\" cellpadding=4><tr><td class=\"steel1\">&nbsp;&nbsp;&nbsp;";

	$export_string .= "<font size=\"-1\">"._("Diese Daten exportieren: ") .  "</font>";
	$export_string .= "</td><td align=\"center\" class=\"steel1\">";
	$export_string .= "<select name=\"format\">";
	while (list($key, $val) = each($output_formats))
	{
		$export_string .= "<option value=\"" . $key . "\"";
		if ($format==$key) $export_string .= " selected";
		$export_string .= ">" . $val;
	}
	$export_string .= "</select>";

	$export_string .= "</td><td align=\"right\" class=\"steel1\">";
	$export_string .= "<input type=\"IMAGE\" " . makeButton("export", "src") . " value=\"" . _("Diese Daten Exportieren") . "\" name=\"export\">&nbsp;";

	$export_string .= "<input type=\"hidden\" name=\"range_id\" value=\"$range_id\">";
	$export_string .= "<input type=\"hidden\" name=\"o_mode\" value=\"choose\">";
	$export_string .= "<input type=\"hidden\" name=\"page\" value=\"1\">";
	$export_string .= "<input type=\"hidden\" name=\"ex_type\" value=\"$ex_type\">";
	$export_string .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"$filename\">";
	$export_string .= "</td></tr></table>";
	$export_string .= "</form>";
	return $export_string;
}
	
function export_link($range_id, $ex_type = "", $filename = "", $format = "", $choose = "")
{
	global $ABSOLUTE_PATH_STUDIP, $PATH_EXPORT;

	$export_string .= "";
	if ($choose != "")
		$export_string .= "<a href=\"" . "export.php?range_id=$range_id&ex_type=$ex_type&xslt_filename=$filename&format=$format&choose=$choose&o_mode=processor\">";
	elseif ($ex_type != "")
		$export_string .= "<a href=\"" . "export.php?range_id=$range_id&ex_type=$ex_type&xslt_filename=$filename&o_mode=choose\">";
	else
		$export_string .= "<a href=\"" . "export.php?range_id=$range_id&o_mode=start\">";
	$export_string .= _("Diese Daten exportieren");
	$export_string .= "</a>";
	return $export_string;
}
	
function export_button($range_id, $ex_type = "", $filename = "", $format = "", $choose = "")
{
	global $ABSOLUTE_PATH_STUDIP, $PATH_EXPORT;
	if ($choose != "")
		$export_string .= "<a href=\"" . "export.php?range_id=$range_id&ex_type=$ex_type&xslt_filename=$filename&format=$format&choose=$choose&o_mode=processor\">";
	elseif ($ex_type != "")
		$export_string .= "<a href=\"" . "export.php?range_id=$range_id&ex_type=$ex_type&xslt_filename=$filename&o_mode=choose\">";
	else
		$export_string .= "<a href=\"" . "export.php?range_id=$range_id&o_mode=start\">";
	$export_string .= makeButton("export", "img");
	$export_string .= "</a>";
	return $export_string;
}
	
?>