<?
$perm->check("dozent");

$export_pagename = _("Datenexport");
require_once ("$ABSOLUTE_PATH_STUDIP/config_tools_semester.inc.php");   // Aktuelles Semester
require_once ("$ABSOLUTE_PATH_STUDIP/RangeTreeObject.class.php");   // Uni-Baum-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xml_vars.inc.php");   // XML-Variablen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xml_func.inc.php");   // XML-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_studipdata_func.inc.php");   // Studip-Export-Funktionen
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_config.inc.php");   // Konfigurationsdatei
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");   // Datumsfunktionen

function CheckParamXML()
{
global $range_id, $ex_type, $xml_file_id, $o_mode, $export_error, $export_error_num, $export_o_modes, $export_ex_types;

	if ((($range_id != "") OR ($xml_file_id != "")) AND (in_array($o_mode, $export_o_modes) AND (in_array($ex_type, $export_ex_types))))
		return true;

	$export_error .= "<b>" . _("Unzulässiger Seitenaufruf!") . "</b><br>";
	$export_error_num++;
	return false;
}


if (!CheckParamXML()) 
{
	$infobox = array(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => _("Die Parametern, mit denen diese Seite aufgerufen wurde, sind fehlerhaft.")
							 )
						)
		)
	);
}

$db=new DB_Seminar;
$db2=new DB_Seminar;

if ($o_mode != "direct")
{
	$xml_file_id = md5(uniqid(rand())) . ".xml";
	$xml_file = fopen($TMP_PATH."/" . $xml_file_id, "w");
}

$tree_object = new RangeTreeObject($range_id);
$range_name = $tree_object->item_data["name"];

output_data ( xml_header(), $o_mode);

export_range( $range_id );

output_data ( xml_footer(), $o_mode);

if ($o_mode != "direct")
{
	fclose($xml_file);
}

if ($o_mode == "file")
{

	if ($object_counter<1)
	{
		$xml_export_text = _("Es wurden keine Daten gefunden!");
		$export_error = _("Es wurden keine Daten gefunden! Die übergebene ID verweist auf keine Veranstaltungs- / Personendaten.");
		$export_error_num++;
//		echo "</td></tr>";
//		die("</table></td></tr></table></body>");

	}
	else
	{
		$xml_export_text = _("Die Daten wurden erfolgreich exportiert.");
		$export_msg = sprintf(_("%s Objekte wurden verarbeitet."), $object_counter);

		$export_info = _("Die Daten wurden in eine XML-Datei exportiert. <br>Wenn Sie die Datei in ein anderes Format konvertieren wollen, klicken Sie auf weiter.<br>Um die Datei herunterzuladen, klicken Sie auf den Dateinamen.");

		$export_pagecontent .= "<br><br>";
		
		$export_weiter_button = "<center><a href=\"" . $PHP_SELF . "?xml_file_id=" . $xml_file_id . "&ex_type=" . $ex_type . "&o_mode=choose\">" . makeButton("weiter") . "</a></center>";

		$xml_printimage = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>";
		$xml_printlink = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">" . $xml_filename . "</a>";
		$xml_printdesc = _("XML-Daten");
		$xml_printcontent = _("In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags können mit einem XSLT-Script verarbeitet werden.") . "<br>";	
	}
	
	$infobox = array	(			
	array ("kategorie"  => _("Information:"),
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
		$infobox[1]["kategorie"] = _("Aktionen:");
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/" . $export_icon["xml"] ,
										"text"  => sprintf(_("Um die XML-Datei jetzt herunterzuladen klicken Sie %s hier %s."), $link, "</a>")
									);
			$infobox[1]["eintrag"][] = array (	"icon" => "pictures/file.gif" ,
										"text"  => _("Wenn Sie die Daten konvertieren wollen, klicken Sie auf 'weiter'.")
									);
	}
	
}
?>