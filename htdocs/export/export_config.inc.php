<?
$EXPORT_ENABLE = true;

$XSLT_ENABLE = true;
$skip_page_2 = false;
$PATH_XSLT_PROCESS = "./htdocs/studip/tmp/";

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