<?
$perm->check("dozent");

require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_config.inc.php");   // Konfigurationsdatei
require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_xslt_vars.inc.php");   // Liste der XSLT-Skripts
require_once ("$ABSOLUTE_PATH_STUDIP/dates.inc.php");   // Datumsfunktionen

$cssSw = new cssClassSwitcher;									// Klasse für Zebra-Design
$cssSw->enableHover();


function CheckParamXSLT()
{
global $ex_type, $xml_file_id, $page, $o_mode, $format, $choose, $xslt_files, $export_o_modes, $export_ex_types, $export_error, $export_error_num;
	if ($page==1)
	{
		reset($xslt_files);
		while (list($key, $val) = each($xslt_files))
			if ($val[$ex_type] AND $val[$format])
				$mod_counter++;
		if ($mod_counter == 0)
		{	
			$export_error .= _("Für dieses Format sind keine Ausgabemodule installiert.<br>Bitte wählen Sie ein anderes Ausgabeformat.") . "<br>";
			$page = 0;
		}

		if ($format == "")
			$page = 0;
		reset($xslt_files);
	}

	if ( ($page==2) AND ($choose == "") )
		$page = 1;

	if (($xml_file_id != "")  AND (in_array($ex_type, $export_ex_types)) AND (in_array($o_mode, $export_o_modes)))
		return true;

	$export_error .= "<b>" . _("Unzulässiger Seitenaufruf!") . "</b><br>";
	$export_error_num++;
	return false;
}

$export_pagename = _("Konvertierung der Daten: ");

if (!CheckParamXSLT()) 
{
	$export_pagename .= _("Es ist ein Fehler aufgetreten ");
	$infobox = array(			
	array ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => _("Die Parameter, mit denen diese Seite aufgerufen wurde, sind fehlerhaft oder unvollständig.")
							 )
						)
		)
	);
}


elseif (!isset($page) or ($page == 0)) // Seite 1 : Auswahl des Dateiformats
{ 
	$export_pagename .= _("Auswahl des Dateiformats");
	
	$export_info = _("Bitte wählen Sie, in welchem Format die Daten ausgegeben werden sollen!") . "<br>";

	$export_pagecontent .= "<form method=\"POST\" action=\"" . $PHP_SELF . "\">";
	
	$export_pagecontent .= "<br><br><br>";
	$export_pagecontent .= _("Ausgabeformat:") .  "<select name=\"format\">";

	while (list($key, $val) = each($output_formats))
	{
		$export_pagecontent .= "<option value=\"" . $key . "\"";
		if ($format==$key) $export_pagecontent .= " selected";
		$export_pagecontent .= ">" . $val;
	}
	$export_pagecontent .= "</select><br>	<br>	<br>	<br>";
	
	$export_pagecontent .= _("Name der Datei (z.B. 'Test'):");
	
	$export_pagecontent .= "<input type=\"text\" name=\"xslt_filename\" value=\"" . $xslt_filename . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"page\" value=\"1\"><br><br><br>";
	$export_pagecontent .= "<input type=\"hidden\" name=\"o_mode\" value=\"" . $o_mode . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"ex_type\" value=\"" . $ex_type . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"xml_file_id\" value=\"" . $xml_file_id . "\">";

	$export_weiter_button = "<center><input type=\"IMAGE\"" . makeButton("weiter", "src") . " name=\"\"></center></form>";

	$infobox = array	(			
	array ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => sprintf(_("Diese Seite bereitet die Datenausgabe vor. %s Schritt 1/3 %s"), "<br><i>", "</i>")
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = _("Aktionen:");
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => _("Bitte wählen Sie das Dateiformat, in dem ihre Daten ausgegeben werden sollen.")
								);
}


elseif ($page == 1) // Seite 2 : Auswahl des XSLT-Scripts
{
	$export_pagename .= _("Auswahl des Ausgabemoduls");

	$export_info = _("Wählen Sie bitte eine der folgenden XSLT-Dateien und klicken Sie auf 'weiter'");

	$export_pagecontent .= "<form method=.\"POST\" action=\"" . $PHP_SELF . "\">";
	$export_pagecontent .= "<br><br>";
	$export_pagecontent .= "<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";
	$export_pagecontent .= "<tr align=\"center\" valign=\"top\">";
	$export_pagecontent .= "<th width=\"5%\"><b>&nbsp;</b></th>";
	$export_pagecontent .= "<th width=\"15%\" align=\"left\">" . _("Ausgabemodul") . "</th>";
	$export_pagecontent .= "<th width=\"80%\"><b>" . _("Beschreibung") . "</b></th>";
	$export_pagecontent .= "</tr>";
	
	$opt_num = 0;
	while (list($key, $val) = each($xslt_files))
	{
		if ($val[$ex_type] AND $val[$format])
		{
			$cssSw->switchClass();
			$export_pagecontent .= "<tr " . $cssSw->getHover() . ">";
			$export_pagecontent .= "<td class=\"" . $cssSw->getClass() . "\">&nbsp;<input type=\"radio\" name=\"choose\" value=\"" . $key . "\"";
			if ($opt_num == 0) $export_pagecontent .= " checked";
			$export_pagecontent .= ">&nbsp;</td>";
			$export_pagecontent .= "<td class=\"" . $cssSw->getClass() . "\">" . $val["name"] . "&nbsp;</td>";
			$export_pagecontent .= "<td class=\"" . $cssSw->getClass() . "\">" . $val["desc"] . "</td>";
			$export_pagecontent .= "</tr>";
			$opt_num++;
		}
	}
	
	$export_pagecontent .= "<br>";
	$export_pagecontent .= "</table>";
	$export_pagecontent .= "<input type=\"hidden\" name=\"page\" value=\"2\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"format\" value=\"" . $format . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"o_mode\" value=\"" . $o_mode . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"ex_type\" value=\"" . $ex_type . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"xml_file_id\" value=\"" . $xml_file_id . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"" . $xslt_filename . "\">";
	
	$export_weiter_button = "<center><input type=\"IMAGE\" " . makeButton("weiter", "src") . " name=\"\"></center></form>";

	
	$infobox = array	(			
	array ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => sprintf(_("Diese Seite bereitet die Datenausgabe vor. %s Schritt 2/3 %s"), "<br><i>", "</i>")
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = _("Aktionen:");
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => _("Wählen Sie bitte eins der zur Verfügung stehenden Ausgabemodule")
								);
}


elseif ($page == 2)  // Seite 3 : Download der Dateien
{
	$export_pagename .= _("Download der Dateien");

	$export_info = _("Die benötigten Dateien liegen nun zum Download bereit.");
	$export_pagecontent .= "<form method=\"POST\" action=\"" . $PHP_SELF . "\">";

	$xml_printimage = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>";
	$xml_printlink = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">" . $xml_filename . "</a>";
	$xml_printdesc = _("XML-Daten");
	$xml_printcontent = _("In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags können mit einem XSLT-Script verarbeitet werden.") . "<br>";	

	$xslt_printimage = "<a href=\"sendfile.php?type=2&file_id=" . $xslt_files[$choose]["file"] . "&file_name=" . $xslt_files[$choose]["name"] . ".xsl\"><img src=\"./pictures/" . $export_icon["xslt"] . "\" border=0></a>";
	$xslt_printlink = "<a href=\"sendfile.php?type=2&file_id=" . $xslt_files[$choose]["file"] . "&file_name=" . $xslt_files[$choose]["name"] . ".xsl\">" . $xslt_files[$choose]["name"] . ".xsl</a>";
	$xslt_printdesc = _("XSLT-Datei");
	$xslt_printcontent = _("Dies ist das XSLT-Script zur Konvertierung der Daten. Klicken Sie auf den Dateinamen, um die Datei zu öffnen.") . "<br>";	

	$export_pagecontent .= "";
	$export_pagecontent .= "<input type=\"hidden\" name=\"page\" value=\"3\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"choose\" value=\"" . $choose . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"format\" value=\"" . $format . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"o_mode\" value=\"" . $o_mode . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"ex_type\" value=\"" . $ex_type . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"xml_file_id\" value=\"" . $xml_file_id . "\">";
	$export_pagecontent .= "<input type=\"hidden\" name=\"xslt_filename\" value=\"" . $xslt_filename . "\">";

	if ($XSLT_ENABLE) 
	{
		$export_pagecontent .= _("Um die Daten mit dem installierten XSLT-Prozessor in das gewünschte Format zu bringen, klicken Sie bitte auf 'weiter'") . "<br><br>";
		$export_weiter_button .= "<center><input type=\"IMAGE\"" . makeButton("weiter", "src") . " name=\"\"></center>";
	}
	else
		$export_pagecontent .= "<br><br><br>";
		
	$export_weiter_button .= "</form>";

	$infobox = array	(			
	array ("kategorie"  => _("Information:"),
		"eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => sprintf(_("Diese Seite bereitet die Datenausgabe vor. %s Schritt 3/3 %s"), "<br><i>", "</i>")
							 )
						)
		)
	);
	$link = "<a href=\"./test.xml"."\">";
	$infobox[1]["kategorie"] = _("Aktionen:");
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => _("Sie können nun XML-Daten und  Ausgabemodul herunterladen.")
								);
	if ($XSLT_ENABLE) 
	{
		$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
									"text"  => _("Wenn Sie auf 'weiter' klicken, wird mit dem installierten XSLT-Prozessor die Ausgabedatei erzeugt.")
								);
	}
}
?>