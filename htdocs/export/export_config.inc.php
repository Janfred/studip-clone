<?
$EXPORT_ENABLE = true;

$XSLT_ENABLE = false;
$skip_page_2 = false;
$PATH_XSLT_PROCESS = $TMP_PATH;

$export_icon["xml"] = "xls-icon.gif";
$export_icon["xslt"] = "xls-icon.gif";
$export_icon["xsl"] = "xls-icon.gif";
$export_icon["rtf"] = "rtf-icon.gif";
$export_icon["pdf"] = "pdf-icon.gif";
$export_icon["html"] = "txt-icon.gif";
$export_icon["htm"] = "txt-icon.gif";
$export_icon["txt"] = "txt-icon.gif";

$xml_filename = "data.xml";
if (!isset($xslt_filename)) $xslt_filename = "studip";
?>