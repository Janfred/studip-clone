<?
/**
* extern_download.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	extern
* @module		Extern
* @package		studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// extern_download.inc.php
// This is a wrapper class for configuration files.
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


require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");

$db = new DB_Institut();
$error_message = "";

// stimmt die übergebene range_id?
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
	$query_order = " ORDER BY " . implode(",", $query_order);
}

// Daten holen
global $_fullname_sql;
$query = "SELECT dokument_id, description, filename, d.mkdate, d.chdate, filesize, ";
$query .= $_fullname_sql[$this->config->getValue("Main", "nameformat")];
$query .= "AS fullname, username FROM dokumente d LEFT JOIN user_info USING (user_id) ";
$query .= "LEFT JOIN auth_user_md5 USING (user_id) WHERE ";
$query .= "Seminar_id='{$this->config->range_id}'$query_order";

$db->query($query);

if (!$db->num_rows())
	$error_message = $this->config->getValue("Main", "nodatatext");

// Titelzeile bauen
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

$set_td_1 = $this->config->getAttributes("TableRow", "td");
$set_td_2 = $this->config->getAttributes("TableRow", "td", TRUE);
$zebra_td = $this->config->getValue("TableRow", "td_zebratd_");

$i = 0;
reset($rf_download);
foreach($rf_download as $spalte){
	if ($visible[$spalte]) {
	
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
			echo "<font" . $this->config->getAttributes("TableHeadrow", "font") . ">" . $alias_download[$spalte] . "</font>\n";
	
		echo "</th>\n";
		$i++;
	}
}
echo "</tr>\n";

// no data to print
if ($error_message) {
	echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
	echo "<td" . $this->config->getAttributes("TableRow", "td") . " colspan=\"$i\">\n";
	echo $error_message;
	echo "</td></tr>\n</table>\n";
}
else {
	$link_persondetails = $this->getModuleLink("Persondetails",
			$this->config->getValue("LinkIntern", "config"), $this->config->getValue("LinkIntern", "srilink"));
	
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
			$picture_file = $GLOBALS["EXTERN_SERVER_NAME"] ."pictures/$icon";
	
		$download_link = $CANONICAL_RELATIV_PATH_STUDIP;
		$download_link .= sprintf("sendfile.php?type=0&file_id=%s&file_name=%s\"",
				$db->f("dokument_id"), $db->f("filename"));
	
		// Aufbereiten der Daten
		$daten = array(
			"icon"        => sprintf("<a href=\"%s\"><img border=\"0\" src=\"%s\"></a>"
												, $download_link, $picture_file),
												 
			"filename"    => sprintf("<font%s><a%s href=\"%s\">%s</a></font>"
												, $this->config->getAttributes("Link", "font")
												, $this->config->getAttributes("Link", "a")
												, $download_link
												, htmlReady($db->f("filename"))),
												 
			"description" => sprintf("<font%s>%s</font>"
												, $this->config->getAttributes("TableRow", "font")
												, htmlReady(mila_extern($db->f("description"),
													$this->config->getValue("Main", "lengthdesc")))),
			
			"mkdate"      => sprintf("<font%s>%s</font>"
												, $this->config->getAttributes("TableRow", "font")
												, strftime($this->config->getValue("Main", "dateformat"), $db->f("mkdate"))),
			
			"size"        => sprintf("<font%s>%s</font>"
												, $this->config->getAttributes("TableRow", "font"),
												$db->f("filesize") > 1048576 ? round($db->f("filesize") / 1048576, 1) . " MB"
												: round($db->f("filesize") / 1024, 1) . " kB"),
													
			"fullname"    => sprintf("<font%s><a href=\"%s&username=%s\"%s>%s</a></font>"
												, $this->config->getAttributes("LinkIntern", "font")
												, $link_persondetails, $db->f("username")
												, $this->config->getAttributes("LinkIntern", "a")
												, htmlReady($db->f("fullname")))
		);
		
		// "horizontal zebra"
		if ($zebra_td == "HORIZONTAL") {
			if ($i % 2)
				$set = $set_td_2;
			else
				$set = $set_td_1;
		}
		else
			$set = $set_td_1;
		
		echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
		
		$j = 0;
		reset($rf_download);
		foreach($rf_download as $spalte){
			
			// "vertical zebra"
			if ($zebra == "VERTICAL") {
				if ($j % 2)
					$set = $set_td_2;
				else
					$set = $set_td_1;
			}
		
			if ($visible[$spalte]) {
				if($db->f($this->data_fields[$spalte]) || $spalte == "icon")
					echo "<td$set>" . $daten[$this->data_fields[$spalte]] . "</td>\n";
				else
					echo "<td$set>&nbsp;</td>\n";
				$j++;
			}
		}
		
		echo "</tr>\n";
		$i++;
	}
	
	echo "\n</table>";
}

?>