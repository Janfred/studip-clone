<?
/**
* ExternModuleDownload.class.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternModuleDownload
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternModuleDownload.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/ExternModule.class.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/views/extern_html_templates.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");

class ExternModuleDownload extends ExternModule {

	var $field_names = array();
	var $data_fields = array("icon", "filename", "description", "mkdate", "filesize", "fullname");
	var $registered_elements = array("Body", "TableHeader", "TableHeadrow",
																	 "TableRow", "Link", "LinkIntern", "TableFooter");

	/**
	*
	*/
	function ExternModuleDownload () {
		$this->field_names = array
		(
				_("Icon"),
				_("Dateiname"),
				_("Beschreibung"),
				_("Datum"),
				_("Gr&ouml;&szlig;e"),
				_("Upload durch")
		);
		
	}
	
	function setup () {
		$this->elements["LinkIntern"]->link_module_type = 2;
		$this->elements["LinkIntern"]->real_name = _("Link zum Modul MitarbeiterInnendetails");
		$this->elements["Link"]->real_name = _("Link zum Dateidownload");
	}
	
	function checkRangeId ($range_id) {
		$range = get_object_type($range_id);
		
		if ($range == "inst" || $range == "fak")
			return TRUE;
			
		return FALSE;
	}
	
	function printout ($args) {
		if ($this->config->getValue("Main", "wholesite")) {				
			echo html_header($this->config->getValue("Main", "title"),
					$this->config->getValue("Main", "urlcss"),
					$this->config->getAttributes("Body", "body"),
					$this->config->getValue("Main", "copyright"),
					$this->config->getValue("Main", "author"));
		}
		
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		echo $this->toString();
		
		if ($this->config->getValue("Main", "wholesite"))
			echo html_footer();
	}
	
	function printoutPreview () {
		echo html_header($this->config->getValue("Main", "title"),
				$this->config->getValue("Main", "urlcss"),
				$this->config->getAttributes("Body", "body"),
				$this->config->getValue("Main", "copyright"),
				$this->config->getValue("Main", "author"));
		
		if (!$language = $this->config->getValue("Main", "language"))
			$language = "de_DE";
		init_i18n($language);
		
		echo $this->toStringPreview();
		
		echo html_footer();
	}
	
	function toString ($args = NULL) {
		$db = new DB_Seminar();
		$error_message = "";
		
		// test for valid range_id
		$query = "SELECT Name FROM Institute WHERE Institut_id='{$this->config->range_id}'";
		$db->query($query);
		if(!$db->next_record())
			$error_message = $GLOBALS["EXTERN_ERROR_MESSAGE"];
		
		$sort = $this->config->getValue("Main", "sort");
		$query_order = "";
		foreach ($sort as $key => $position) {
			if ($position > 0)
				$query_order[$position] = $this->data_fields[$key];
		}
		if ($query_order) {
			ksort($query_order, SORT_NUMERIC);
			$query_order = " ORDER BY " . implode(",", $query_order) . " DESC";
		}
		
		if (!$nameformat = $this->config->getValue("Main", "nameformat"))
			$nameformat = "no_title_short";
		$query = "SELECT dokument_id, description, filename, d.mkdate, d.chdate, filesize, ";
		$query .= $GLOBALS["_fullname_sql"][$nameformat];
		$query .= "AS fullname, username FROM dokumente d LEFT JOIN user_info USING (user_id) ";
		$query .= "LEFT JOIN auth_user_md5 USING (user_id) WHERE ";
		$query .= "Seminar_id='{$this->config->range_id}'$query_order";
		
		$db->query($query);
		
		if (!$db->num_rows())
			$error_message = $this->config->getValue("Main", "nodatatext");
		
		$out = $this->elements["TableHeadrow"]->toString();
		
		// if there are no files present or other shit has happened
		if ($error_message)
			$out = $this->elements["TableRow"]->toString(array("content" => $error_message));
		else {
			$table_row_data["data_fields"] = $this->data_fields;
			while($db->next_record()){
			
				preg_match("/^.+\.([a-z1-9_-]+)$/i", $db->f("filename"), $file_suffix);
				
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
					$picture_file = "http://{$GLOBALS['EXTERN_SERVER_NAME']}pictures/$icon";
						
				$download_link = $CANONICAL_RELATIV_PATH_STUDIP;
				$download_link .= sprintf("sendfile.php?type=0&file_id=%s&file_name=%s\"",
						$db->f("dokument_id"), $db->f("filename"));
			
				// Aufbereiten der Daten
				$table_row_data["content"] = array(
					"icon"        => sprintf("<a href=\"%s\"><img border=\"0\" src=\"%s\"></a>"
														, $download_link, $picture_file),
														 
					"filename"    => $this->elements["Link"]->toString(array("content" =>
														htmlReady($db->f("filename")), "link" => $download_link)),
														 
					"description" => htmlReady(mila_extern($db->f("description"),
													 $this->config->getValue("Main", "lengthdesc"))),
					
					"mkdate"      => strftime($this->config->getValue("Main", "dateformat"), $db->f("mkdate")),
					
					"filesize"    => $db->f("filesize") > 1048576 ? round($db->f("filesize") / 1048576, 1) . " MB"
														: round($db->f("filesize") / 1024, 1) . " kB",
															
					"fullname"    => $this->elements["LinkIntern"]->toString(array("content" =>
														htmlReady($db->f("fullname")), "module" => "Persondetails",
														"link_args" => "username=" . $db->f("username")))
				);
				$out .= $this->elements["TableRow"]->toString($table_row_data);
			}
			
			return $this->elements["TableHeader"]->toString(array("content" => $out));
		}
	}
	
	function toStringPreview () {
		$time = time();
		// preview data
		$data[] = array("dokument_id" => 1, "description" => _("Das ist eine Text-Datei."),
			"filename" => "text_file.txt", "mkdate" => ($time - 100000), "chdate" => ($time - 50000),
			"filesize" => 26378, "Vorname" => "Julius", "Nachname" => "Rodman");
		$data[] = array("dokument_id" => 2, "description" => _("Das ist eine Powerpoint-Datei."),
			"filename" => "powerpoint_file.ppt", "mkdate" => ($time - 200000), "chdate" => ($time - 150000),
			"filesize" => 263784, "Vorname" => "William", "Nachname" => "Wilson");
		$data[] = array("dokument_id" => 3, "description" => _("Das ist eine ZIP-Datei."),
			"filename" => "zip_file.zip", "mkdate" => ($time - 300000), "chdate" => ($time - 250000),
			"filesize" => 63784, "Vorname" => "August", "Nachname" => "Bedloe");
		$data[] = array("dokument_id" => 4, "description" => _("Das ist eine Excel-Datei."),
			"filename" => "excel_file.txt", "mkdate" => ($time - 400000), "chdate" => ($time - 350000),
			"filesize" => 23784, "Vorname" => "Ernst", "Nachname" => "Waldemar");
		$data[] = array("dokument_id" => 5, "description" => _("Das ist eine Bild-Datei."),
			"filename" => "bild_jpeg_file.jpg", "mkdate" => ($time - 500000), "chdate" => ($time - 450000),
			"filesize" => 53784, "Vorname" => "Absalom", "Nachname" => "Hicks");
		$data[] = array("dokument_id" => 6, "description" => _("Das ist ein Dokument im Microsoft Rich-Text-Format."),
			"filename" => "microsoft_rtf_file.rtf", "mkdate" => ($time - 600000), "chdate" => ($time - 550000),
			"filesize" => 563784, "Vorname" => "Dirk", "Nachname" => "Peters");
		$data[] = array("dokument_id" => 7, "description" => _("Das ist ein Adobe PDF-Dokument."),
			"filename" => "adobe_pdf_file.pdf", "mkdate" => ($time - 700000), "chdate" => ($time - 650000),
			"filesize" => 13784, "Vorname" => "Augustus", "Nachname" => "Barnard");
		$data[] = array("dokument_id" => 8, "description" => _("Und noch ein ZIP-Archiv."),
			"filename" => "gnu_zip_file.tar.gz", "mkdate" => ($time - 800000), "chdate" => ($time - 750000),
			"filesize" => 2684, "Vorname" => "Gordon", "Nachname" => "Pym");
		$data[] = array("dokument_id" => 9, "description" => _("Eine weitere Text-Datei."),
			"filename" => "text2_file.txt", "mkdate" => ($time - 900000), "chdate" => ($time - 850000),
			"filesize" => 123784, "Vorname" => "Hans", "Nachname" => "Pfaal");
		$data[] = array("dokument_id" => 10, "description" => _("Ein Bild im PNG-Format."),
			"filename" => "picture_png_file.png", "mkdate" => ($time - 1000000), "chdate" => ($time - 950000),
			"filesize" => 813784, "Vorname" => "John", "Nachname" => "Greely");
		$data[] = array("dokument_id" => 11, "description" => _("Eine anderes Format."),
			"filename" => "good_music.mp3", "mkdate" => ($time - 1150000), "chdate" => ($time - 653900),
			"filesize" => 934651, "Vorname" => "Augustus", "Nachname" => "Barnard");
		
		$table_row_data["data_fields"] = $this->data_fields;
		$out = $this->elements["TableHeadrow"]->toString();
		
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
			$table_row_data["content"] = array(
				"icon"        => $this->elements["Link"]->toString(array("content" =>
													"<img border=\"0\" src=\"$picture_file\">", "link" => "")),
													 
				"filename"    => $this->elements["Link"]->toString(array("content" =>
													htmlReady($db["filename"]), "link" => "")),
													 
				"description" => htmlReady(mila_extern($db["description"],
													$this->config->getValue("Main", "lengthdesc"))),
				
				"mkdate"      => strftime($this->config->getValue("Main", "dateformat"), $db["mkdate"]),
				
				"filesize"    => $db["filesize"] > 1048576 ? round($db["filesize"] / 1048576, 1) . " MB"
													: round($db["filesize"] / 1024, 1) . " kB",
														
				"fullname"    => $this->elements["LinkIntern"]->toString(
													array("content" => htmlReady($db["Vorname"]." ".$db["Nachname"])))
				
			);
			$out .= $this->elements["TableRow"]->toString($table_row_data);
		}
		
		return $this->elements["TableHeader"]->toString(array("content" => $out));
	}

	
}
?> 
