<?
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"]."visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/lib/extern_functions.inc.php");

// An das Skript koennen verschiedene Variablen uebergeben werden,
// die festlegen, welche Statusgruppen angezeigt werden sollen:
//
// $show_gr_names enthält als kommaseparierte Liste die Namen der Statusgruppen,
// die angezeigt werden sollen.
// Syntax:
// $show_gr_names = "'Statusgruppe_1','Statusgruppe_2', ... ,'Statusgruppe_x'";
//
// Alternativ kann man in $hidden_gr_names angeben, welche Statusgruppen nicht
// angezeigt werden sollen. Alle nicht genannten werden angezeigt.
//
// Analog zu den oben genannten Variablen kann man in $show_gr_ids und
// $hidden_gr_ids statt der Namen die Statusgruppen-Ids an das Skript
// uebergeben.
//
// Ist keine der o.g. Variablen gesetzt, werden alle Statusgruppen ausgegeben.

$range_id = $this->config->range_id;

$all_groups = $this->config->getValue("Main", "groups");
$visible_groups = get_statusgruppen_by_id($range_id,
		$this->config->getValue("Main", "groupsvisible"));
$aliases_groups = $this->config->getValue("Main", "groupsalias");

$db = new DB_Institut();
$gruppierung_mit = TRUE;
if(!$gruppierung_mit){
	$groups_ids = array_keys($all_groups);
	$groups_ids = implode("','", $groups_ids);
	
	$query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon, aum.*
						FROM statusgruppen sg LEFT JOIN statusgruppe_user su USING(statusgruppe_id)
						LEFT JOIN user_inst ui USING(user_id) LEFT JOIN auth_user_md5 aum USING(user_id)
						WHERE sg.range_id = '$range_id' AND sg.statusgruppe_id 
						IN ('$groups_ids') AND ui.Institut_id = '$range_id' 
						GROUP BY user_id";// ORDER BY $mit_order ASC";
	$db->query($query);
  $all_groups = array($titel_al_liste);
}

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
// Wird eine alphabetische Liste ausgegeben, wird sie mit den Daten aus obiger
// Abfrage einmal durchlaufen.
reset($visible_groups);
foreach ($visible_groups as $group_id => $group) {
	global $_fullname_sql;
	if($gruppierung_mit){
		$query = "SELECT raum, sprechzeiten, Telefon, inst_perms,	Email, aum.user_id, username, ";
		$query .= $_fullname_sql[$this->config->getValue("Main", "nametitle")] . " AS name ";
		$query .= "FROM statusgruppe_user LEFT JOIN auth_user_md5 aum USING(user_id)";
		$query .= "LEFT JOIN user_inst USING(user_id) WHERE statusgruppe_id = '$group_id'";
		$query .= " AND Institut_id = '$range_id'";
						//	ORDER BY $mit_order ASC";
		$db->query($query);
		
		$position = array_search($group_id, $all_groups);
		if($aliases_groups[$position])
			$group = $aliases_groups[$position];
	}

	if ($db->num_rows()) {
	
		$name_sp_first = TRUE;
		$group_colspan = array_count_values($this->config->getValue("Main", "visible"));
		if (!$name_sp_frst && $group && !$no_group_headers) {
  		echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
			echo "<td colspan=\"{$group_colspan['1']}\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
  		echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
			echo htmlReady($group) . "</font>\n</td></tr>\n";
		}
		
		// Hier noch mal das gleiche für $rf_daten
//		if($sp_titel_wdhlg || $count == 1){
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
//		}
		
		
		if ($name_sp_frst && $group && !$no_group_headers) {
  		echo "<tr" . $this->config->getAttributes("TableGroup", "tr") . ">";
			echo "<td colspan=\"$anz_sp\"" . $this->config->getAttributes("TableGroup", "td") . ">\n";
  		echo "<font" . $this->config->getAttributes("TableGroup", "font") . ">";
			echo $group . "</font>\n</td></tr>\n";
		}
	
		$set_1 = $this->config->getAttributes("TableRow", "td");
		$set_2 = $this->config->getAttributes("TableRow", "td", TRUE);
		$zebra = $this->config->getValue("TableRow", "td_zebratd_");
	
		$i = 0;
		while($db->next_record()){
		
			$wert_daten = array(
			"Name"         => sprintf("<a href=\"\"%s>%s</a>",
												$this->config->getAttributes("Link", "a"),
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
			
			"Email"       => sprintf("<a href=\"mailto:%s\"%s>%s</a>",
												$db->f("Email"),
												$this->config->getAttributes("Link", "a"),
												$db->f("Email"))
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
}
	
echo "</table>\n";
/*

			
			while(list(,$name_sp) = each($rf_daten)){
			
				// Hintergrundfarbe umschalten (spaltenweise)
				if(isset($hgtabelle_2) && $hgschraffur == "SPALTE"){
					if($count_bgcolor % 2 == 1)
						$bgcolor = $hgtabelle_2;
					else
						$bgcolor = $hgtabelle;
					$count_bgcolor++;
				}
				
				if($wert_daten[$name_sp] || $link_daten[$name_sp]){
   					printf("<td%s%s%s width=\"%s\">\n",
						$bgcolor, $fborder, $td_height, $breite_daten[$name_sp]);
					if ($Schrift1 || $sgta || $tftabelle)
						printf("<font%s%s%s>%s%s</font>\n", $Schrift1, $sgtab, $tftabelle,
							$link_daten[$name_sp], $wert_daten[$name_sp]);
					else
						echo $link_daten[$name_sp] . $wert_daten[$name_sp] . "\n";
				}
				else{
					printf("<td%s%s%s width=\"%s\">\n",
						$bgcolor, $fborder, $td_height,  $breite_daten[$name_sp]);
					if ($sgtab)
						echo "<font$sgtab>&nbsp</font>\n";
					else
						echo "&nbsp\n";
				}

				if($link_daten[$name_sp])
					echo "</a>";

				echo "</td>\n";
			}

			reset($rf_daten);
			echo "</tr>\n";
		}
	

}
if($count > 0)
	echo "</table>\n";
*/
?>
