<?
$skip_page_3 = true;
$PATH_XSLT_PROCESS = $TMP_PATH;

$export_o_modes = array("start","file","choose", "direct","processor","passthrough");
$export_ex_types = array("veranstaltung", "person", "forschung");

$export_icon["xml"] = "xls-icon.gif";
$export_icon["xslt"] = "xls-icon.gif";
$export_icon["xsl"] = "xls-icon.gif";
$export_icon["rtf"] = "rtf-icon.gif";
$export_icon["fo"] = "pdf-icon.gif";
$export_icon["pdf"] = "pdf-icon.gif";
$export_icon["html"] = "txt-icon.gif";
$export_icon["htm"] = "txt-icon.gif";
$export_icon["txt"] = "txt-icon.gif";

$xml_filename = "data.xml";
if ($xslt_filename == "") $xslt_filename = "studip";
?>