<?
// FORMATIERUNGSSTRINGS FUER DIE AUSGABE IM


// ---------------------------
// - RTF-FORMAT -
// ---------------------------


$export_name["rtf"] = "Rich Text Format";
$export_endung["rtf"] = ".rtf";
$export_icon["rtf"] = "rtf-icon.gif";
$export_header["rtf"] = "{\\rtf1\\ansi\\ansicpg1252\\deff0\\deflang1031{\\fonttbl{\\f0\\fnil\\fcharset0 Times New Roman;}}
\\viewkind4\\uc1\\pard\\par\\qc\\fs56 Sozialwissenschaftliche Fakult\\'e4t
\\par\\fs36 der Georg-August-Universit\\'e4t G\\'f6ttingen
\\par\\pard\\fs44 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par 
\\par\\qc\\fs96 Vorlesungskommentar
\\par\\fs56 SOMMERSEMESTER 2002\\par\\pard";

$export_filter["rtf"] = array(
		"neuezeile" => "\n\\par",
		"neueseite" => "\\page\n",
		"font1" => "\\f0",
		"/font" => "",
		"size10" => "\\fs20",
		"size12" => "\\fs24",
		"size14" => "\\fs28",
		"size16" => "\\fs32",
		"size18" => "\\fs36",
		"size20" => "\\fs40",
		"fett" => "\\b",
		"/fett" => "\\b0",
		"zentriert" => "\\qc",
		"/zentriert" => "\\pard",
		"content" => " ",
		"/content" => "",
		"tab4" => "\\trowd \\trgaph70\\trleft-70\\trbrdrt\\brdrs\\brdrw10 \\trbrdrl\\brdrs\\brdrw10 \\trbrdrb\\brdrs\\brdrw10 
\\trbrdrr\\brdrs\\brdrw10 \\trbrdrh\\brdrs\\brdrw10 \\trbrdrv\\brdrs\\brdrw10 \\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx2316\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb
\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx4702\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx7088\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb
\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx9474\\pard\\plain \\nowidctlpar\\widctlpar\\intbl\\adjustright \\fs20\\lang1031\\cgrid {%s\\cell %s\\cell %s\\cell %s\\cell }\\pard \\nowidctlpar\\widctlpar\\intbl\\adjustright {\\row }\\pard 
\\nowidctlpar\\widctlpar\\adjustright",
		"tab5" => "\\trowd \\trgaph70\\trleft-70\\trbrdrt\\brdrs\\brdrw10 \\trbrdrl\\brdrs\\brdrw10 \\trbrdrb\\brdrs\\brdrw10 
\\trbrdrr\\brdrs\\brdrw10 \\trbrdrh\\brdrs\\brdrw10 \\trbrdrv\\brdrs\\brdrw10 \\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx1839\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb
\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx3748\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx5657\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb
\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx7566\\clvertalt\\clbrdrt\\brdrs\\brdrw10 \\clbrdrl\\brdrs\\brdrw10 \\clbrdrb\\brdrs\\brdrw10 \\clbrdrr\\brdrs\\brdrw10 \\cltxlrtb \\cellx9475\\pard\\plain \\nowidctlpar\\intbl\\adjustright \\lang1031\\cgrid 
{\\fs24 %s\\cell %s\\cell %s\\cell %s\\cell %s\\cell }\\pard \\nowidctlpar\\widctlpar\\intbl\\adjustright {\\row }\\pard ",
		"rahmen" => "\\brdrt\\brdrs\\brdrw10\\brsp20 \\brdrl\\brdrs\\brdrw10\\brsp80 \\brdrb
\\brdrs\\brdrw10\\brsp20 \\brdrr\\brdrs\\brdrw10\\brsp80 \\adjustright \\fs20\\lang1031\\cgrid {%s\\par }\\pard",
		);

$export_footer["rtf"] = "}";


// ---------------------------
// - HTML-FORMAT -
// ---------------------------


$export_name["html"] = "HTML-Seite";
$export_endung["html"] = ".htm";
$export_icon["html"] = "xls-icon.gif";
$export_header["html"] = "<html>\n
<head>\n
	<title>Vorlesungskommentar</title>\n
</head>\n
<body>\n
<center>\n
<font size=6>Sozialwissenschaftliche Fakult&auml;t</font><br>\n
<font size=5>der Georg-August-Universit&auml;t G&ouml;ttingen</font><br>\n
<br><br><br><br><br><br><br><br><br><br><br><br><br>\n
<font size=7>Vorlesungskommentar</font><br>\n
<font size=6>SOMMERSEMESTER 2002</font><br>\n
</center>\n";

$export_filter["html"] = array(
		"neuezeile" => "<br>\n",
		"neueseite" => "<hr>\n",
		"font1" => "<font ",
		"/font" => "</font>",
		"size10" => "size=2>",
		"size12" => "size=3>",
		"size14" => "size=4>",
		"size16" => "size=5>",
		"size18" => "size=6>",
		"size20" => "size=7>",
		"fett" => "<b>",
		"/fett" => "</b>",
		"zentriert" => "<center>",
		"/zentriert" => "</center>",
		"content" => "",
		"/content" => "",
		"tab" => "<table border=1 bordercolor='#000000' cellspacing=0 width='100%'>",
		"/tab" => "</table>",
		"tab4" => "<tr><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td></tr>",
		"tab5" => "<tr><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td><td>&nbsp;%s</td></tr>",
		"rahmen" => "<table border=1 bordercolor='#000000' cellspacing=0 width='100%%'><tr><td>%s</td></tr></table>",
		);

$export_footer["html"] = "</body></html>\n";


// ---------------------------
// - TXT-FORMAT -
// ---------------------------


$export_name["txt"] = "Text (ohne Formatierungen)";
$export_endung["txt"] = ".txt";
$export_icon["txt"] = "txt-icon.gif";
$export_header["txt"] = "		    Sozialwissenschaftliche Fakultaet\n		der Georg-August-Universitaet Goettingen\n\n\n\n\n\n			Vorlesungskommentar\n			SOMMERSEMESTER 2002\n\n\n";

$export_filter["txt"] = array(
		"neuezeile" => "\n",
		"neueseite" => "_________________________________________________________\n",
		"font1" => "",
		"size10" => "",
		"size12" => "",
		"size14" => "	",
		"size16" => "	",
		"size18" => "		",
		"size20" => "		",
		"fett" => "",
		"/fett" => "",
		"zentriert" => "",
		"/zentriert" => "",
		"content" => "",
		"/content" => "",
		"tab" => "",
		"/tab" => "",
		"tab4" => "		%s		%s		%s		%s\n",
		"tab5" => "		%s		%s		%s		%s		%s\n",
		"rahmen" => "___________________________________________________________\n%s\n___________________________________________________________\n"
		);

$export_footer["txt"] = "";
?>