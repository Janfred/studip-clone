<?
require_once($ABSOLUTE_PATH_STUDIP."visual.inc.php");
require_once($ABSOLUTE_PATH_EXTERN_MODULES."extern_functions.inc.php");
require_once("config.inc");

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

if(isset($show_group_names))
	$all_statusgruppen = GetStatusgruppenByName($instituts_id, $show_group_names);
else if(isset($show_group_ids))
	$all_statusgruppen = GetStatusgruppenByIds($instituts_id, $show_group_ids);
else if(isset($hidden_group_names))
	$all_statusgruppen = GetStatusgruppenByName($instituts_id, $hidden_group_names, TRUE);
else if(isset($hidden_group_ids))
	$all_statusgruppen = GetStatusgruppenByIds($instituts_id, $hidden_group_ids, TRUE);
else
	$all_statusgruppen = GetAllStatusgruppen($instituts_id);

if(!$all_statusgruppen)
	die();
	
// Standardfarbe für Zellenhintergrund
$bgcolor = $hgtabelle;

$db = new DB_Institut();

if(!isset($breite_daten))
		$breite_daten = array(
		       "Name"           =>  '30%',
	         "Telefon"        =>  '12%',
	         "Sprechstunde"   =>  '23%',
	         "Raum"           =>  '8%',
	         "e-mail"         =>  '27%');

$count = 0;

// Es koennen für den Link in der config.inc Parameter angegeben werden.
if(strstr($lnk_mdet, "?"))
	$lnk_mdet .= '&';
else
	$lnk_mdet .= '?';

if(!$gruppierung_mit){
	$statusgruppen_ids = array_keys($all_statusgruppen);
	$statusgruppen_ids = implode("','", $statusgruppen_ids);
	
	$query = "SELECT ui.raum, ui.sprechzeiten, ui.Telefon, aum.*
						FROM statusgruppen sg LEFT JOIN statusgruppe_user su USING(statusgruppe_id)
						LEFT JOIN user_inst ui USING(user_id) LEFT JOIN auth_user_md5 aum USING(user_id)
						WHERE sg.range_id = '$instituts_id' AND sg.statusgruppe_id 
						IN ('$statusgruppen_ids') AND ui.Institut_id = '$instituts_id' 
						GROUP BY user_id ORDER BY $mit_order ASC";
	$db->query($query);
  $all_statusgruppen = array($titel_al_liste);
}

// Die "große" Schleife. Sie wird für jede Statusgruppe einmal durchlaufen.
// Fuer jede Statusgruppe wird eine Abfrage abgesetzt.
// Wird eine alphabetische Liste ausgegeben, wird sie mit den Daten aus obiger
// Abfrage einmal durchlaufen.
reset($all_statusgruppen);
while(list($statusgruppe_id, $statusgruppe) = each($all_statusgruppen)){
	if($gruppierung_mit){
		$query = "SELECT Nachname, Vorname, raum, sprechzeiten, Telefon, inst_perms,
							Email, auth_user_md5.user_id, username
							FROM statusgruppe_user LEFT JOIN auth_user_md5 USING(user_id)
							LEFT JOIN user_inst USING(user_id)
							WHERE statusgruppe_id = '$statusgruppe_id' 
							AND Institut_id = '$instituts_id'
							ORDER BY $mit_order ASC";
		$db->query($query);
	
		if($alias_gruppierung[$statusgruppe] != "")
			$statusgruppe = $alias_gruppierung[$statusgruppe];
		else
			$statusgruppe = htmlReady($statusgruppe);
	}

	if($db->num_rows()){
	
		if($count == 0)
			printf("<table%s%s%s%s%s>\n", $border, $fborder, $padding, $spacing, $b_mit);
		$count++;
		if($style == "Liste"){
			printf("<tr><td%s%s height=\"19\" width=\"30%%\">", $hgtitel, $fborder);
			if ($tftitel || $Schrift1 || $sgtitel)
				printf("<font%s%s%s><strong>%s</strong></font></td>\n",
					$tftitel, $Schrift1, $sgtitel, $statusgruppe);
			else
				echo "<b>$statusgruppe</b>";
			
			// Zähler zur Steuerung der Hintergrundfarbe
			$count_bgcolor = 0;
			
			while($db->next_record()){
			
				// Hintergrundfarbe umschalten
				if(isset($hgtabelle_2)){
					if($count_bgcolor % 2 == 1)
						$bgcolor = $hgtabelle_2;
					else
						$bgcolor = $hgtabelle;
					$count_bgcolor++;
				}
						
				$link_daten = array(
				"Name" => sprintf("<a href=\"%susername=%s&instituts_id=%s\"%s>",
					$lnk_mdet, $db->f("username"), $instituts_id, $linkstyle),
				"e-mail" => sprintf("<a href=\"mailto:%s\"%s>", $db->f("Email"), $linkstyle));
							
				$wert_daten = array(
					"Name"         => htmlReady($db->f("Vorname") . " " . $db->f("Nachname"), TRUE),
					"Telefon"      => htmlReady($db->f("Telefon"), TRUE),
					"Sprechstunde" => htmlReady($db->f("sprechzeiten"), TRUE),
					"Raum"         => htmlReady($db->f("raum"), TRUE),
					"e-mail"       => $db->f("Email"));
					
				printf("<tr><td%s%s height=\"16\" width=\"100%%\">",
					$bgcolor, $fborder);

				reset($rf_daten);
				while(list(,$name_sp) = each($rf_daten)){
					if($alias_daten[$name_sp])
						$alias_name_sp = $alias_daten[$name_sp].": ";
					else
						$alias_name_sp = $name_sp.": ";
						
					if($name_sp == "Name"){
						$sgtab_name = " size=\"10\"";
						$alias_name_sp = "";
					}
					else
						$sgtab_name = $sgtab;
					
					if($link_daten[$name_sp] || $wert_daten[$name_sp])
						printf("<font%s%s><b>%s</b></font>%s<font%s%s%s>%s</font><br>",
							$Schrift1, $sgtab_name, $alias_name_sp, $link_daten[$name_sp], $Schrift1,
							$sgtab, $tftabelle, $wert_daten[$name_sp]);

					if($link_daten[$name_sp])
						echo "</a>";
				}
			reset($rf_daten);
			echo "</td></tr>\n";
			}
		}
		else{
			$anz_sp = sizeof($rf_daten);
			if(!$name_sp_frst && $statusgruppe && !$no_group_headers){
  			echo "<tr><td colspan=\"$anz_sp\"$hg_ms_gruppe$fborder height=\"19\">\n";
				if ($a_ms_gruppe || $g_ms_gruppe || $f_ms_gruppe) {
	  			echo "<font$a_ms_gruppe$g_ms_gruppe$f_ms_gruppe><b>";
					echo $statusgruppe . "</b></font>\n</td></tr>\n";
				}
				else
					echo "<b>$statusgruppe</b>\n</td></tr>\n";
			}
			
			// Hier noch mal das gleiche für $rf_daten
			if($sp_titel_wdhlg || $count == 1){
				reset($rf_daten);
				echo "<tr>\n";
				foreach($rf_daten as $name_sp){
  				printf("<td%s%s width=\"%s\">\n",
						$hgtitel, $fborder, $breite_daten[$name_sp]);
					if($alias_daten[$name_sp])
						$name_sp = $alias_daten[$name_sp];
					if ($Schrift1 || $sgtitel || $tftitel) {
						echo "<font$Schrift1$sgtitel$tftitel>";
						echo "<b>$name_sp</b></font>\n</td>\n";
					}
					else
						echo "<b>$name_sp</b>\n</td>\n";
				}
				echo "</tr>\n";
			}
			
			if($name_sp_frst && $statusgruppe && !$no_group_headers){
	  		echo "<tr><td colspan=\"$anz_sp\"$hg_ms_gruppe$fborder>\n";
				if ($a_ms_gruppe || $g_ms_gruppe || $f_ms_gruppe) {
	  			echo "<font$a_ms_gruppe$g_ms_gruppe$f_ms_gruppe>";
					echo "<b>$statusgruppe</b></font>\n</td></tr>\n";
				}
				else
					echo "<b>$statusgruppe</b>\n</td></tr>\n";
			}
			
			reset($rf_daten);
			echo "</tr>\n";
			
			// Zähler zur Steuerung der Hintergrundfarbe
			$count_bgcolor = 0;
			while($db->next_record()){
			
				// Hintergrundfarbe umschalten (zeilenweise)
				if($hgschraffur == "ZEILE"){
					if(isset($hgtabelle_2)){
						if($count_bgcolor % 2 == 1)
							$bgcolor = $hgtabelle_2;
						else
							$bgcolor = $hgtabelle;
						$count_bgcolor++;
					}
				}
				else
					$count_bgcolor = 0; 

				$link_daten = array(
					"Name" => sprintf("<a href=\"%susername=%s&instituts_id=%s\"%s>",
						$lnk_mdet, $db->f("username"), $instituts_id, $linkstyle),
					"e-mail" => sprintf("<a href=\"mailto:%s\"%s>", $db->f("Email"), $linkstyle));
							
				$wert_daten = array(
					"Name"         => htmlReady($db->f("Vorname") . " " . $db->f("Nachname"), TRUE),
					"Telefon"      => htmlReady($db->f("Telefon"), TRUE),
					"Sprechstunde" => htmlReady($db->f("sprechzeiten"), TRUE),
					"Raum"         => htmlReady($db->f("raum"), TRUE),
					"e-mail"       => $db->f("Email"));
		
				echo "<tr>";

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
	}
}
if($count > 0)
	echo "</table>\n";
?>
