<?
/**
* persons.inc.php
* 
* 
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		persons
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// persons.inc.php
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

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]."visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/extern_functions.inc.php");
global $_fullname_sql;

$range_id = $this->config->range_id;

$all_groups = $this->config->getValue("Main", "groups");
$visible_groups = get_statusgruppen_by_id($range_id,
		$this->config->getValue("Main", "groupsvisible"));
$aliases_groups = $this->config->getValue("Main", "groupsalias");

$sort = $this->config->getValue("Main", "sort");
sort($sort, SORT_NUMERIC);

$query_order = "";
reset($sort);
foreach ($sort as $position) {
	if ($position > 0)
		$query_order .= " " . $this->data_fields[$position - 1] . ",";
}

$db = new DB_Institut();
$grouping = $this->config->getValue("Main", "grouping");
if(!$grouping){
	$groups_ids = implode("','", $this->config->getValue("Main", "groupsvisible"));
	
	$query = "SELECT DISTINCT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,	Email, aum.user_id, username, ";
	$query .= $_fullname_sql[$this->config->getValue("Main", "nameformat")] . " AS fullname, ";
	$query .= "FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) ";
	$query .= "LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ";
	$query .= "WHERE statusgruppe_id IN ('$groups_ids') AND Institut_id = '$range_id'";
	
	if ($query_order)
		$query .= " ORDER BY" . substr($query_order, 0, -1);
	$db->query($query);
	$visible_groups = array("");
}

$repeat_headrow = $this->config->getValue("Main", "repeatheadrow");
$order = $this->config->getValue("Main", "order");
$width = $this->config->getValue("Main", "width");
$alias = $this->config->getValue("Main", "aliases");
$visible = $this->config->getValue("Main", "visible");
if ($this->config->getValue("TableHeader", "width_pp") == "PERCENT")
	$percent = "%";
else
	$percent = "";
$group_colspan = array_count_values($visible);

$set_1 = $this->config->getAttributes("TableHeadrow", "th");
$set_2 = $this->config->getAttributes("TableHeadrow", "th", TRUE);
$zebra = $this->config->getValue("TableHeadrow", "th_zebrath_");

$set_td_1 = $this->config->getAttributes("TableRow", "td");
$set_td_2 = $this->config->getAttributes("TableRow", "td", TRUE);
$zebra_td = $this->config->getValue("TableRow", "td_zebratd_");

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";

$first_loop = TRUE;
reset($visible_groups);
foreach ($visible_groups as $group_id => $group) {
	if($grouping){
		$query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon, inst_perms,	Email, aum.user_id, username, ";
		$query .= $_fullname_sql[$this->config->getValue("Main", "nameformat")] . " AS fullname, ";
		$query .= "FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id) ";
		$query .= "LEFT JOIN user_info USING(user_id) LEFT JOIN user_inst ui USING(user_id) ";
		$query .= "WHERE statusgruppe_id='$group_id' AND Institut_id = '$range_id'";
		if ($query_order)
			$query .= " ORDER BY" . substr($query_order, 0, -1);
		$db->query($query);
		
		$position = array_search($group_id, $all_groups);
		if($aliases_groups[$position])
			$group = $aliases_groups[$position];
	}

	if ($db->num_rows()) {
	
		if ($grouping && $repeat_headrow == "above") {
  		echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
			echo "<td colspan=\"{$group_colspan['1']}\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
  		echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
			echo htmlReady($group) . "</font>\n</td></tr>\n";
		}
		
		if($repeat_headrow || $first_loop){
			echo "<tr" . $this->config->getAttributes("TableHeadrow", "tr") . ">\n";
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
					echo "<font" . $this->config->getAttributes("TableHeadrow", "font") . ">";
					if ($alias[$column])
						echo $alias[$column];
					else
						echo "&nbsp;";
					echo "</font>\n</th>\n";
				}
				$i++;
			}
			echo "</tr>\n";
		}
		
		
		if ($grouping && $repeat_headrow != "above") {
  		echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
			echo "<td colspan=\"{$group_colspan['1']}\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
  		echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
			echo $group . "</font>\n</td></tr>\n";
		}

		$i = 0;
		while($db->next_record()){
		
			$data = array(
			"fullname"         => sprintf("<a href=\"\"%s><font%s>%s</font></a>",
												$this->config->getAttributes("Link", "a"),
												$this->config->getAttributes("Link", "font"),
												htmlReady($db->f("name"), TRUE)),
												
			"Telefon"      => sprintf("<font%s>%s</font>",
												$this->config->getAttributes("TableRow", "font"),
												htmlReady($db->f("Telefon"), TRUE)),
			
			"Sprechzeiten" => sprintf("<font%s>%s</font>",
												$this->config->getAttributes("TableRow", "font"),
												htmlReady($db->f("sprechzeiten"), TRUE)),
			
			"Raum"         => sprintf("<font%s>%s</font>",
												$this->config->getAttributes("TableRow", "font"),
												htmlReady($db->f("raum"), TRUE)),
			
			"Email"       => sprintf("<a href=\"mailto:%s\"%s><font%s>%s</font></a>",
												$db->f("Email"),
												$this->config->getAttributes("Link", "a"),
												$this->config->getAttributes("Link", "font"),
												$db->f("Email"))
			);

			// "horizontal zebra"
			if ($zebra_td == "HORIZONTAL") {
				if ($i % 2)
					$set_td = $set_td_2;
				else
					$set_td = $set_td_1;
			}
			else
				$set_td = $set_td_1;
		
			echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">";
			
			
			$j = 0;
			reset($order);
			foreach ($order as $column) {
				if ($visible[$column]) {
				
					// "vertical zebra"
					if ($zebra_td == "VERTICAL") {
						if ($j % 2)
							$set_td = $set_td_2;
						else
							$set_td = $set_td_1;
					}
			
					echo "<td$set_td>";
					if ($db->f($this->data_fields[$column]))
   					echo $data[$this->data_fields[$column]];
					else
						echo "&nbsp";
					echo "</td>\n";
					$j++;
				}
			}
			$i++;
			echo "</tr>\n";
		}
		$first_loop = FALSE;
	}
}
	
echo "</table>\n";

?>
