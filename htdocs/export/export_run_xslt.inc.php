<?

require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_config.inc.php");   // Konfigurationsdatei
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xslt_vars.inc.php");   // Liste der XSLT-Skripts

if ($o_mode != "passthrough")
{
	// Start of Output
		include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
		include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
		include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");	
	
 	?>
	
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<td class="topic" colspan="2"><b>Ausgabe der Daten</b>
			</td>
		</tr>
		<tr>
			<td class="blank" colspan="2">&nbsp; 
			</td>
		</tr>
	<tr valign="top">
		<td width="90%" class="blank">
		<table>
	<?
}
// Allocate a new XSLT processor
$xh = xslt_create();

// Process the document
$result_file = $xslt_filename . "." . $format;
$result = $PATH_XSLT_PROCESS . $result_file;
$xml_process_file = $PATH_XSLT_PROCESS . $xml_file_id;
$xslt_process_file = "./htdocs/studip/export/" . $xslt_files[$choose]["file"];
if (xslt_process($xh, $xml_process_file , $xslt_process_file, $result) AND ($o_mode != "passthrough")) 
{
	my_msg("Die Daten wurden erfolgreich konvertiert. <br> Sie k&ouml;nnen die Ausgabedatei jetzt ansehen oder herunterladen.<br>");
	$xslt_info = "Die Daten sind nun im gew&auml;hlten Format verf&uuml;gbar.";
	$xslt_process = true;
	$link1 = "<a href=\"" . $TMP_PATH . "/" . $result_file . "\">";
	$link2 = "<a href=\"sendfile.php?type=2&file_id=" . $result_file . "&file_name=" . $xslt_filename . "." . $format . "\">";
}
elseif ($o_mode != "passthrough") 
{
	my_error(sprintf("Bei der Konvertierung ist ein Fehler aufgetreten. <br>Der XSLT-Prozessor meldet den Fehler Nr. %s: %s <br>", xslt_error($xh), xslt_errno($xh)));
	$xslt_info = "Bei der Konvertierung ist ein Fehler aufgetreten.";
	$xslt_process = false;
}
/*
if ($o_mode == "processor")
	unlink($xml_file);
*/
xslt_free($xh);

if ($o_mode == "passthrough")
	readfile("./tmp/" . $result_file);
else
{
	?>
		</table>
	<?
	if ($xslt_process)
	{
		?><center><table cellspacing="0" cellpadding="0" border="0" width="30%"><tr align="center"><td>
		<b>Ausgabe-Datei: </b></td><td><? echo $result_file; ?></td></tr><tr><td colspan="2">
		&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;<? echo $link1; ?> Datei &ouml;ffnen</a></td></tr><tr><td colspan="2">
		&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;<? echo $link2; ?> Datei herunterladen</a></td></tr>
		</td></tr></table></center><br><br><?
	}
	?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><?
	printhead ("99%", 0, "", "close", true, "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>", "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">" . $xml_filename . "</a>", "XML-Daten");
	?></tr></table><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
	<?
	printcontent("99%", FALSE, "In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags k&ouml;nnen mit einem XSLT-Script verarbeitet werden.<br>", "");
	?>
	</tr></table>
	<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><?
	printhead ("99%", 0, "", "close", true, "<a href=\"sendfile.php?type=2&file_id=" . $xslt_files[$choose]["file"] . "&file_name=" . $xslt_files[$choose]["name"] . ".xsl\"><img src=\"./pictures/" . $export_icon["xslt"] . "\" border=0></a>", "<a href=\"sendfile.php?type=2&file_id=" . $xslt_files[$choose]["file"] . "&file_name=" . $xslt_files[$choose]["name"] . ".xsl\">" . $xslt_files[$choose]["name"] . ".xsl</a>", "XSLT-Datei");
	?></tr></table><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
	<?
	printcontent("99%", FALSE, "Dies ist das XSLT-Script zur Konvertierung der Daten.<br>", "");
	?>
	</tr></table>
	<?
	if ($xslt_process)
	{
		?><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><?
		printhead ("99%", 0, "", "close", true, "<a href=\"sendfile.php?type=2&file_id=" . $result_file . "&file_name=" . $xslt_filename . "." . $format . "\"><img src=\"./pictures/" . $export_icon[$format] . "\" border=0></a>", "<a href=\"sendfile.php?type=2&file_id=" . $result_file . "&file_name=" . $xslt_filename . "." . $format . "\">" . $xslt_filename . "." . $format . "</a>", "Ausgabe-Datei");
		?></tr></table><?
		?><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
		<?
		printcontent("99%", FALSE, "Dies ist die fertige Ausgabedatei.<br>", "");
		?>
		</tr></table><?
	}

	?>
	</td>
	<td width="270" NOWRAP class="blank" align="center" valign="top">
	<?

	$infobox = array	(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => $xslt_info
							 )
						)
		)
	);
	if ($xslt_process)
	{
		$infobox[1]["kategorie"] = "Aktionen:";
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/einst.gif",
										"text"  => "Um die Ausgabe-Datei in ihrem Browser anzusehen, klicken Sie $link1 hier </a>."
									);
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
										"text"  => "Um die Ausgabe-Datei herunterzuladen, klicken Sie $link2 hier </a>."
									);
	}
		
	print_infobox ($infobox,"pictures/export.jpg");
	
	?>		
			</td>		
		</tr>
		<tr>
			<td class="blank" colspan="2">&nbsp; 
			</td>
		</tr>
	</table>
	<p>&nbsp;</p>
	<?
}
?>