<?
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xml_vars.inc.php");   // XML-Variablen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xml_func.inc.php");   // XML-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_studipdata_func.inc.php");   // Studip-Export-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_config.inc.php");   // Konfigurationsdatei
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");   // Datumsfunktionen

if ($o_mode == "file")
{
// Start of Output
	include ("$ABSOLUTE_PATH_STUDIP/html_head.inc.php"); // Output of html head
	include ("$ABSOLUTE_PATH_STUDIP/header.php");   // Output of Stud.IP head
	include ("$ABSOLUTE_PATH_STUDIP/links_admin.inc.php");
}

function CheckParam()
{
global $range_id, $ex_type, $xml_file_id, $o_mode;
if ((($range_id != "") OR ($xml_file_id != "")) AND ($o_mode != "") AND (in_array($ex_type, array("veranstaltung", "person", "forschung"))))
	return true;
return false;
}

$db=new DB_Seminar;
$db2=new DB_Seminar;

if ($o_mode == "file")
{
	 ?>
	<table cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td class="topic" colspan="2"><b>Datenexport</b>
		</td>
	</tr>
	<? if (!CheckParam()) my_error("<b>Unzul&auml;ssiger Seitenaufruf!</b>");
	?>
	<tr>
		<td class="blank" colspan="2">&nbsp; 
		</td>
	</tr>
	<tr valign="top">
     		<td width="90%" class="blank">
	<table>		
	<?

}

if ($o_mode != "direct")
{
	$xml_file_id = md5(uniqid(rand())) . ".xml";
	$xml_file = fopen($TMP_PATH."/" . $xml_file_id, "w");
}

output_data ( xml_header() );

export_inst( $range_id );

output_data ( xml_footer() );

if ($o_mode != "direct")
	fclose($xml_file);

if ($o_mode == "file")
{

	if ($object_counter<1)
	{
		$xml_export_text = "Es wurden keine Daten gefunden!";
//		echo "</td>";
		my_error("Es wurden keine Daten gefunden! Die &uuml;bergebene ID verweist auf keine Veranstaltungs- / Personendaten.");
//		die("</table></body>");

	}
	else
	{
		$xml_export_text = "Die Daten wurden erfolgreich exportiert.";
		my_msg(sprintf("%s Objekte wurden verarbeitet.", $object_counter));

		my_info("Die Daten wurden in eine XML-Datei exportiert. <br>Wenn Sie die Datei in ein anderes Format konvertieren wollen, klicken Sie auf weiter.<br>Um die Datei herunterzuladen, klicken Sie auf den Dateinamen.");
	}
	?>
	</table>
	<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr><?
	printhead ("99%", 0, "", "close", true, "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>", "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">" . $xml_filename . "</a>", "XML-Daten");
	?></tr></table><table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>
	<?
	printcontent("99%", FALSE, "In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags k&ouml;nnen mit einem XSLT-Script verarbeitet werden.<br>", "");
	?>
	</tr></table>
	<br><br><br><?
	
	if ($object_counter>0) 
	{
		?>
		<center><a href="<? echo $PHP_SELF . "?xml_file_id=" . $xml_file_id . "&ex_type=" . $ex_type;?>"><? echo makeButton("weiter"); ?></a></center>
		<?
	}
	?>
	</td>
	<td width="270" NOWRAP class="blank" align="center" valign="top">
	<?

	$infobox = array	(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => $xml_export_text
							 )
						)
		)
	);
	if ($object_counter > 0)
	{
		$link = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">";
		$infobox[1]["kategorie"] = "Aktionen:";
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/" . $export_icon["xml"] ,
										"text"  => "Um die XML-Datei jetzt herunterzuladen klicken Sie $link hier </a>."
									);
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/file.gif" ,
										"text"  => "Wenn Sie die Daten konvertieren wollen, klicken Sie auf 'weiter'."
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
	</body>
	</html>
	<?
}
?>