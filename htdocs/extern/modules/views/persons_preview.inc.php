<?
/**
* persons_preview.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		persons_preview
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// persons_preview.inc.php
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


$group_data[] = array("group_name" => "Gruppe A", "persons" => array(
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com"),
array("name" => "Vorname Nachname", "raum" => "M 13", "sprechzeiten" => "Mi. und Fr., 12.00 - 13.00",
		"telefon" => "38-895638", "email" => "bla@bla.com"),
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com")));
$group_data[] =  array("group_name" => "Gruppe B", "persons" => array(
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com"),
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com"),
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com")));
$group_data[] =  array("group_name" => "Gruppe C", "persons" => array(
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com"),
array("name" => "Vorname Nachname", "raum" => "M 23", "sprechzeiten" => "Donnerstags, 12.00 - 13.00",
		"telefon" => "38-374982", "email" => "bla@bla.com")));

$count = 0;

$order = $this->config->getValue("Main", "order");
$width = $this->config->getValue("Main", "width");
$alias = $this->config->getValue("Main", "aliases");
$visible = $this->config->getValue("Main", "visible");
if ($this->config->getValue("TableHeader", "width_pp") == "PERCENT")
	$percent = "%";
else
	$percent = "";
	
$set_1 = $this->config->getAttributes("TableHeadrow", "th");
$set_2 = $this->config->getAttributes("TableHeadrow", "th", TRUE);
$zebra = $this->config->getValue("TableHeadrow", "th_zebrath_");

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";

// Die "große" Schleife. Sie wird für jede Statusgruppe einmal durchlaufen.
// Fuer jede Statusgruppe wird eine Abfrage abgesetzt.
reset($group_data);
foreach ($group_data as $groups) {
	$statusgruppe = $groups["group_name"];
	
	$name_sp_first = TRUE;
	$group_colspan = sizeof($this->config->getValue("Main", "order"));
	if(!$name_sp_frst && $statusgruppe && !$no_group_headers){
  	echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
		echo "<td colspan=\"$group_colspan\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
  	echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
		echo $statusgruppe . "</font>\n</td></tr>\n";
	}
	
	// Hier noch mal das gleiche für $rf_daten
//	if($sp_titel_wdhlg || $count == 1){
		echo "<tr" . $this->config->getAttributes("TableHeadRow", "tr") . ">\n";
		$i = 0;
		reset($order);
		foreach ($order as $column) {
		
			// "zebra-effect" in head-row
			if ($zebra) {
				if ($i % 2)
					$set = $set_2;
				else
					$set = $set_1;
			}
			else
				$set = $set_1;
			
			if ($visible[$column]) {
  			echo "<th$set width=\"" . $width[$column] . $percent . "\">\n";
				echo "<font" . $this->config->getAttributes("TableHeadRow", "font") . ">";
				if ($alias[$column])
					echo $alias[$column];
				else
					echo "&nbsp;";
				echo "</font>\n</th>\n";
			}
			$i++;
		}
		echo "</tr>\n";
//	}
	
	if($name_sp_frst && $statusgruppe && !$no_group_headers){
  	echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
		echo "<td colspan=\"$anz_sp\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
  	echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
		echo $statusgruppe . "</font>\n</td></tr>\n";
	}
	
//	reset($rf_daten);
//	echo "</tr>\n";
	$set_1 = $this->config->getAttributes("TableRow", "td");
	$set_2 = $this->config->getAttributes("TableRow", "td", TRUE);
	$zebra = $this->config->getValue("TableRow", "td_zebratd_");
	
	$i = 0;
	reset($groups["persons"]);
	foreach ($groups["persons"] as $data) {
					
		$wert_daten = array(
			"Name"         => sprintf("<a href=\"\"%s>%s</a>",
												$this->config->getAttributes("Link", "a"),
												htmlReady($data["name"], TRUE)),
												
			"Telefon"      => sprintf("<font%s>%s</font>",
												$this->config->getAttributes("TableRow", "font"),
												htmlReady($data["telefon"], TRUE)),
			
			"Sprechzeiten" => sprintf("<font%s>%s</font>",
												$this->config->getAttributes("TableRow", "font"),
												htmlReady($data["sprechzeiten"], TRUE)),
			
			"Raum"         => sprintf("<font%s>%s</font>",
												$this->config->getAttributes("TableRow", "font"),
												htmlReady($data["raum"], TRUE)),
			
			"Email"       => sprintf("<a href=\"mailto:%s\"%s>%s</a>",
												$data["email"],
												$this->config->getAttributes("Link", "a"),
												$data["email"])
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
		
		echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">";
		
		$j = 0;
		reset($order);
		foreach ($order as $column) {
			if ($visible[$column]) {
				
				// "vertical zebra"
				if ($zebra == "VERTICAL") {
					if ($j % 2)
						$set = $set_2;
					else
						$set = $set_1;
				}
				else
					$set = $set_1;
			
				echo "<td$set>";
				if ($wert_daten[$this->data_fields[$column]])
   				echo $wert_daten[$this->data_fields[$column]];
				else
					echo "&nbsp";
				echo "</td>\n";
				$j++;
			}
		}

		echo "</tr>\n";
	}
}
	
echo "</table>\n";
?>
