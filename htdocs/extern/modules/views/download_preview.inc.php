<?
/**
* extern_download_preview.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		extern_download_preview
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_download_preview.inc.php
// 
// Copyright (C) 2003 Peter Thienel <pthienel@web.de>,
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");

$time = time();

// preview data
$data[] = array("dokument_id" => 1, "description" => _("Das ist eine Text-Datei."),
	"filename" => "text_file.txt", "mkdate" => ($time - 100000), "chdate" => ($time - 50000),
	"filesize" => 263784, "Vorname" => "Julius", "Nachname" => "Rodman");
$data[] = array("dokument_id" => 2, "description" => _("Das ist eine Powerpoint-Datei."),
	"filename" => "powerpoint_file.ppt", "mkdate" => ($time - 200000), "chdate" => ($time - 150000),
	"filesize" => 263784, "Vorname" => "William", "Nachname" => "Wilson");
$data[] = array("dokument_id" => 3, "description" => _("Das ist eine ZIP-Datei."),
	"filename" => "zip_file.zip", "mkdate" => ($time - 300000), "chdate" => ($time - 250000),
	"filesize" => 263784, "Vorname" => "August", "Nachname" => "Bedloe");
$data[] = array("dokument_id" => 4, "description" => _("Das ist eine Excel-Datei."),
	"filename" => "excel_file.txt", "mkdate" => ($time - 400000), "chdate" => ($time - 350000),
	"filesize" => 263784, "Vorname" => "Ernst", "Nachname" => "Waldemar");
$data[] = array("dokument_id" => 5, "description" => _("Das ist eine Bild-Datei."),
	"filename" => "bild_jpeg_file.jpg", "mkdate" => ($time - 500000), "chdate" => ($time - 450000),
	"filesize" => 263784, "Vorname" => "Absalom", "Nachname" => "Hicks");
$data[] = array("dokument_id" => 6, "description" => _("Das ist ein Dokument im Microsoft Rich-Text-Format."),
	"filename" => "microsoft_rtf_file.rtf", "mkdate" => ($time - 600000), "chdate" => ($time - 550000),
	"filesize" => 263784, "Vorname" => "Dirk", "Nachname" => "Peters");
$data[] = array("dokument_id" => 7, "description" => _("Das ist ein Adobe PDF-Dokument."),
	"filename" => "adobe_pdf_file.pdf", "mkdate" => ($time - 700000), "chdate" => ($time - 650000),
	"filesize" => 263784, "Vorname" => "Augustus", "Nachname" => "Barnard");
$data[] = array("dokument_id" => 8, "description" => _("Und noch ein ZIP-Archiv."),
	"filename" => "gnu_zip_file.tar.gz", "mkdate" => ($time - 800000), "chdate" => ($time - 750000),
	"filesize" => 263784, "Vorname" => "Gordon", "Nachname" => "Pym");
$data[] = array("dokument_id" => 9, "description" => _("Eine weitere Text-Datei."),
	"filename" => "text2_file.txt", "mkdate" => ($time - 900000), "chdate" => ($time - 850000),
	"filesize" => 263784, "Vorname" => "Hans", "Nachname" => "Pfaal");
$data[] = array("dokument_id" => 10, "description" => _("Ein Bild im PNG-Format."),
	"filename" => "picture_png_file.png", "mkdate" => ($time - 1000000), "chdate" => ($time - 950000),
	"filesize" => 263784, "Vorname" => "John", "Nachname" => "Greely");

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
echo "\n<tr" . $this->config->getAttributes("TableHeadrow", "tr") . ">\n";

$rf_download = $this->config->getValue("Main", "order");
$breite_download = $this->config->getValue("Main", "width");
if ($this->config->getValue("TableHeader", "width_pp") == "PERCENT")
	$percent = "%";
$alias_download = $this->config->getValue("Main", "aliases");
$visible = $this->config->getValue("Main", "visible");

$set_1 = $this->config->getAttributes("TableHeadrow", "th");
$set_2 = $this->config->getAttributes("TableHeadrow", "th", TRUE);
$zebra = $this->config->getValue("TableHeadrow", "th_zebrath_");
$i = 0;
reset($rf_download);
foreach($rf_download as $spalte){
	if ($visible[$spalte] == "TRUE") {
		
		// "zebra-effect" in head-row
		if ($zebra) {
			if ($i % 2)
				$set = $set_2;
			else
				$set = $set_1;
		}
		else
			$set = $set_1;
			
		echo "<th$set width=\"" . $breite_download[$spalte] . "$percent\">";
		
		if($alias_download[$spalte] == "")
			echo "<b>&nbsp;</b>\n";
		else 
			echo "<font" . $this->config->getAttributes("TableHeadrow", "font") . "><b>" . $alias_download[$spalte] . "</b></font>\n";
	
		echo "</th>\n";
		$i++;
	}
}
echo "</tr>\n";

$set_1 = $this->config->getAttributes("TableRow", "td");
$set_2 = $this->config->getAttributes("TableRow", "td", TRUE);
$zebra = $this->config->getValue("TableRow", "td_zebratd_");

$i = 0;
reset($data);
foreach ($data as $db) {
	
	preg_match("/^.+\.([a-z1-9_-]+)$/i", $db["filename"], $file_suffix);
	
	// choose the icon for the given file format
	$icon = "";
	switch ($file_suffix[1]) {
		case "txt" :
			if (!$picture_file = $this->config->getValue("Main", "icontxt"))
				$icon = "txt-icon.gif";
			break;
		case "xls" :
			if (!$picture_file = $this->config->getValue("Main", "iconxls"))
				$icon = "xls-icon.gif";
			break;
		case "ppt" :
			if (!$picture_file = $this->config->getValue("Main", "iconppt"))
				$icon = "ppt-icon.gif";
			break;
		case "rtf" :
			if (!$picture_file = $this->config->getValue("Main", "iconrtf"))
				$icon = "rtf-icon.gif";
			break;
		case "zip" :
		case "tgz" :
		case "gz" :
			if (!$picture_file = $this->config->getValue("Main", "iconzip"))
				$icon = "zip-icon.gif";
			break;
		case "jpg" :
		case "png" :
		case "gif" :
		case "jpeg" :
		case "tif" :
			if (!$picture_file = $this->config->getValue("Main", "iconpic"))
				$icon = "pic-icon.gif";
			break;
		case "pdf" :
			if (!$picture_file = $this->config->getValue("Main", "iconpdf"))
				$icon = "pdf-icon.gif";
			break;
		default :
			if (!$picture_file = $this->config->getValue("Main", "icondefault"))
				$icon = "txt-icon.gif";
	}
	
	if ($icon)
		$picture_file = $CANONICAL_RELATIVE_PATH_STUDIP ."pictures/$icon";

	// Aufbereiten der Daten
	$daten = array(
		"icon"        => sprintf("<a href=\"\"><img border=\"0\" src=\"%s\"></a>"
											, $picture_file),
											 
		"filename"    => sprintf("<font%s><a href=\"\"%s>%s</a></font>"
											, $this->config->getAttributes("Link", "font")
											, $this->config->getAttributes("Link", "a") 
											, htmlReady($db["filename"])),
											 
		"description" => sprintf("<font%s>%s</font>"
											, $this->config->getAttributes("TableRow", "font")
											, htmlReady($db["description"])),
		
		"date"        => sprintf("<font%s>%s</font>"
											, $this->config->getAttributes("TableRow", "font")
											, date("d.m.Y", $db["mkdate"])),
		
		"size"        => sprintf("<font%s>%s</font>"
											, $this->config->getAttributes("TableRow", "font"),
											$db["filesize"] > 1048576 ? round($db["filesize"] / 1048576, 1) . " MB"
											: round($db["filesize"] / 1024, 1) . " kB"),
												
		"name"        => sprintf("<font%s>%s</font>"
											, $this->config->getAttributes("TableRow", "font")
											, htmlReady($db["Vorname"]." ".$db["Nachname"]))
	);

	// "horizontal zebra"
	if ($zebra == "HORIZONTAL") {
		if ($i % 2)
			$set = $set_2;
		else
			$set = $set_1;
	}
	else
		$set = $set_1;
	
	echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
	
	$j = 0;
	reset($rf_download);
	foreach($rf_download as $spalte){
		
		// "vertical zebra"
		if ($zebra == "VERTICAL") {
			if ($j % 2)
				$set = $set_2;
			else
				$set = $set_1;
		}
	
		if ($visible[$spalte] == "TRUE") {
			if($daten[$this->data_fields[$spalte]] == "")
				echo "<td$set>&nbsp;</td>\n";
			else
				echo "<td$set>" . $daten[$this->data_fields[$spalte]] . "</td>\n";
			$j++;
		}
	}
	
	echo "</tr>\n";
	$i++;
}

echo "\n</table>";

?>
