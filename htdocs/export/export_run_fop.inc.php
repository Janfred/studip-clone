<?
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// export_run_fop.inc.php
// pages for choosing an xslt-script
// 
// Copyright (c) 2002 Arne Schroeder <schroeder@data-quest.de> 
// Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

$FOP_ENABLE = true;

if (($o_mode != "direct") AND ($o_mode != "passthrough")) 
	$perm->check("tutor");

require_once ("$ABSOLUTE_PATH_STUDIP$PATH_EXPORT/export_config.inc.php");   // Konfigurationsdatei

function CheckParamRUN_FOP()
{
global $XSLT_ENABLE, $ex_type, $o_mode, $xml_file_id, $page, $format, $output_formats, $choose, $xslt_files, $export_error, $export_error_num, $export_o_modes, $export_ex_types, $result_file;

	if ($result_file == "") 
	{
		$export_error .= "<b>" . _("Fehlende Parameter!") . "</b><br>";
		$export_error_num++;
		return false;
	}
	
		
	return true;
}


if (!CheckParamRUN_FOP()) 
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
elseif ($FOP_ENABLE != true)
{
	$infobox[1]["eintrag"][] = array (	"icon" => "pictures/einst.gif",
								"text"  => sprintf(_("Die Erweiterung zum Erzeugen von PDF-Dateien ist nicht aktiviert, es konnten daher nur Formatting Objects erzeugt werden."))
							);
}
else
{
	$export_pagename = _("Download der PDF-Datei");

	// Process the document
	escapeshellcmd ( $result_file );
	escapeshellcmd ( $TMP_PATH );
	$pdf_file = md5(uniqid(rand())) .".pdf";
	$str = "/usr/local/fop-0.20.5rc/fop.sh $TMP_PATH/$result_file $TMP_PATH/$pdf_file ";
//	$out = system( ( $str ) );
	$out = exec( ( ". /etc/profile.d/alljava.sh && " . $str ) );
//echo $str . "<br>" . $out . "<br>";
/*
	unlink( $TMP_PATH . "/" . $xml_file_id);
	unlink( $TMP_PATH . "/" . $result_file);
*/	
		if (file_exists($TMP_PATH."/".$pdf_file))
		{
			$link2 = "<a href=\"sendfile.php?type=2&file_id=" . $pdf_file  . "&file_name=" . $xslt_filename . ".pdf\">";
	
			$export_pagecontent = "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"30%\"><tr align=\"center\"><td>";
			$export_pagecontent .= "<b>" . _("Ausgabe-Datei: ") . "</b>";
			$export_pagecontent .= "</td><td>" . $link2 . $xslt_filename . ".pdf</a>";
//			$export_pagecontent .= "</td><td>" . $result_file . "</td></tr><tr><td colspan=\"2\">";
//			$export_pagecontent .= "&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;" . $link1 . _("Datei &ouml;ffnen") . "</a></td></tr><tr><td colspan=\"2\">";
//			$export_pagecontent .= "&nbsp;&nbsp;&nbsp;&nbsp; - &nbsp;&nbsp;" . $link2 . _("Datei herunterladen") . "</a></td></tr>";
			$export_pagecontent .= "</td></tr></table><br>";
	
			$result_printimage = "<a href=\"sendfile.php?type=2&file_id=" . $pdf_file . "&file_name=" . $xslt_filename . ".pdf" . "\"><img src=\"./pictures/" . $export_icon["pdf"] . "\" border=0></a>";
			$result_printlink = "<a href=\"sendfile.php?type=2&file_id=" . $pdf_file . "&file_name=" . $xslt_filename . ".pdf" . "\">" . $xslt_filename . ".pdf" . "</a>";
			$result_printdesc = _("PDF-Datei");
			$result_printcontent = _("Dies ist die fertige PDF-Datei.") . "<br>";	
		}	
		else
		{	
			unset($result_printimage);
			unset($result_printlink);
			unset($result_printdesc);
			unset($result_printcontent);
			unset($export_info);
			unset($export_msg);
			$export_pagecontent = "";
			$export_error = "<b>" . sprintf(_("Bei der Erzeugung der PDF-Datei ist ein Fehler aufgetreten. <br>Fehlermeldung: %s <br>Datei: %s"), $out, $pdf_file) . "</b><br>";
			$export_error_num ++;
		}
	
		$xml_printimage = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\"><img src=\"./pictures/" . $export_icon["xml"] . "\" border=0></a>";
		$xml_printlink = "<a href=\"sendfile.php?type=2&file_id=$xml_file_id&file_name=$xml_filename\">" . $xml_filename . "</a>";
		$xml_printdesc = _("XML-Daten");
		$xml_printcontent = _("In dieser Datei sind die Daten als XML-Tags gespeichert. Diese Tags k&ouml;nnen mit einem XSLT-Script verarbeitet werden.") . "<br>";	
	
		$xslt_printimage = "<a href=\"sendfile.php?type=2&file_id=" . $result_file . "&file_name=" . $xslt_filename . "." . $format . "\"><img src=\"./pictures/" . $export_icon[$format] . "\" border=0></a>";
		$xslt_printlink = "<a href=\"sendfile.php?type=2&file_id=" . $result_file . "&file_name=" . $xslt_filename . "." . $format . "\">" . $xslt_filename . "." . $format . "</a>";
		$xslt_printdesc = _("Formatting-Objects-Datei");
		$xslt_printcontent = _("In dieser Datei sind die Formatting Objects zur Erzeugung der PDF-Datei gespeichert.") . "<br>";	
	

		$infobox = array	(			
		array ("kategorie"  => "Information:",
			"eintrag" => array	(	
							array (	"icon" => "pictures/ausruf_small.gif",
									"text"  => $xslt_info
								 )
							)
			)
		);
		{
			$infobox[1]["kategorie"] = "Aktionen:";
				$infobox[1]["eintrag"][] = array (	"icon" => "pictures/nachricht1.gif" ,
											"text"  => sprintf(_("Um die Ausgabe-Datei herunterzuladen, klicken Sie %s hier %s."), $link2, "</a>")
										);
		}
	

}
?>