<?
/*
resourcesVisual.php - 0.8.20020401
Augaben der Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*****************************************************************************
printThread generische Darstellung einer Zeile des Threads
/*****************************************************************************/
class printThread {

	function printRow($icon, $link, $titel, $zusatz, $level='', $lines='', $weitere, $new=FALSE, $open="close", $content='Keine Beschreibung', $edit='', $breite="99%") {
		
		?><table border=0 cellpadding=0 cellspacing=0 width="100%">
			<tr>
				<td class="blank" valign="top" heigth=21 nowrap><img src="pictures/forumleer.gif"><img src="pictures/forumleer.gif"><?
	
		//Struktur darstellen
		$striche = "";
		for ($i=0;$i<$level;$i++) {
			if ($i==($level-1)) {
				if ($this->lines[$i+1]>1) 
					$striche.= "<img src=\"pictures/forumstrich3.gif\" border=0>"; 		//Kreuzung
				else
					$striche.= "<img src=\"pictures/forumstrich2.gif\" border=0>"; 		//abknickend
				$this->lines[$i+1] -= 1;
			} else {
				if ($this->lines[$i+1]==0) 
					$striche .= "<img src=\"pictures/forumleer.gif\" border=0>";			//Leerzelle
				else
					$striche .= "<img src=\"pictures/forumstrich.gif\" border=0>";		//Strich
			}
		}
	
		echo $striche;
					?></td>
					<?
	
		//Kofzeile ausgeben
		 printhead ($breite, 0, $link, $open, $new, $icon, $titel, $zusatz);
			?><td class="blank" width="*">&nbsp;</td>
			</tr>
		</table>
		<?	 
		 
		 //weiter zur Contentzeile
		 if ($open=="open") {
		?><table width="100%" cellpadding=0 cellspacing=0 border=0>
			<tr>
				<?
			 	//wiederum Striche fuer Struktur
				?><td class="blank" nowrap background="pictures/forumleer.gif"><img src="pictures/forumleer.gif"><img src="pictures/forumleer.gif"></td>
				<?
				$striche='';
				if ($level)
					for ($i=1;$i<=$level;$i++) {
						if ($this->lines[$i]==0) {
							$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumleer.gif\"><img src=\"pictures/forumleer.gif\"></td>";
							}
						else {
							$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumstrich.gif\"><img src=\"pictures/forumleer2.gif\"></td>";
							}
					}

				if ($weitere)
					$striche.= "<td class=\"blank\" nowrap background=\"pictures/forumstrichgrau.gif\"><img src=\"pictures/forumleer.gif\"></td>";
				else 
					$striche.= "<td class=\"blank\" nowrap background=\"pictures/steel1.jpg\"><img src=\"pictures/forumleer.gif\"></td>";

				echo $striche;
		
				//Contenzeile ausgeben
				printcontent ($breite, FALSE, $content, $edit);
				?><td class="blank" width="*">
					&nbsp;
				</td>
			</tr>	
		</table>
		<?
		}
	}
}

/*****************************************************************************
getList, stellt Liste mit Hilfe von printThread dar
/*****************************************************************************/

class getList extends printThread {

	function getList() {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
	}
	
	function createList ($start_id, $level=0) {
		global $resources_data, $edit_structure_object;
		
		$db=new DB_Seminar;		
		$db2=new DB_Seminar;		
		
		//Daten des Objects holen
		$db->query("SELECT resource_id FROM resources_objects WHERE resource_id = '$start_id' ");
		
		//Wenn keine Liste ausgebene wird...
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;
			
		while ($db->next_record()) {
			//Untergeordnete Objekte laden
			$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$db->f("resource_id")."' ");
			
			//Object erstellen
			$resObject=new resourceObject($db->f("resource_id"));

			//Daten vorbereiten
			$icon="<img src=\"pictures/cont_folder2.gif\" />";
			if ($resources_data["structure_open"] == $resObject->id) {
				$link=$PHP_SELF."?structure_close=".$resObject->id."#a";
				$open="open";
				echo "<a name=\"a\"></a>";
			} else {
				$link=$PHP_SELF."?structure_open=".$resObject->id."#a";
				$open="close";
			}
			if ($resObject->getCategory())
				$titel=$resObject->getCategory().": ";
			if ($edit_structure_object==$resObject->id) {
				echo "<a name=\"a\"></a>";
				$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
			} else {
				$titel.=htmlReady($resObject->getName());
			}
			if ($resObject->getOwnerLink())
				$zusatz=sprintf ("Besitzer: <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), $resObject->getOwnerName());
			else			
				$zusatz=sprintf ("Besitzer: %s", $resObject->getOwnerName());
			$new=TRUE;
			if ($edit_structure_object==$resObject->id) {
				$content.= "<br /><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br />";
				$content.= "<input type=\"image\" src=\"./pictures/buttons/uebernehmen-button.gif\" border=0 value=\"&Auml;nderungen speichern\" />";
				$content.= "&nbsp;<input type=\"image\" src=\"./pictures/buttons/abbrechen-button.gif\" border=0 value=\"Abbrechen\" />";						
				$content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\" />";
				$open="open";
			} else {
				$content=htmlReady($resObject->getDescription());
			}
			if (!$weitere) {
				$edit.= "<a href=\"$PHP_SELF?kill_object=$resObject->id\"><img src=\"./pictures/buttons/loeschen-button.gif\" border=0></a>";
			} 
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_object=$resObject->id\"><img src=\"./pictures/buttons/neuesobjekt-button.gif\" border=0></a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?edit_object=$resObject->id\"><img src=\"./pictures/buttons/bearbeiten-button.gif\" border=0></a>";
			
			//Daten an Ausgabemodul senden (aus resourcesVisual)
			if ($level)
				$this->printRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
			
			//in weitere Ebene abtauchen
			while ($db2->next_record()) {
				$this->createList($db2->f("resource_id"), $level+1);
			}
		}
	}

	function createSearchList($search_string) {
	}
}

/*****************************************************************************
getThread, stellt Struktur mit Hilfe von printThread dar
/*****************************************************************************/
class getThread extends printThread {
	var $lines;		//Uebersichtsarray der Struktur;

	function getThread() {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
	}

	function createThread ($root_id, $level=0, $lines='') {
		global $resources_data, $edit_structure_object;
		
		$db=new DB_Seminar;		
		$db2=new DB_Seminar;		
		
		//Daten des Objects holen
		$db->query("SELECT resource_id FROM resources_objects WHERE resource_id = '$root_id' ");
		while ($db->next_record()) {
			//Untergeordnete Objekte laden
			$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$db->f("resource_id")."' ");
			
			//Struktur merken
			$weitere=$db2->affected_rows();
			$this->lines[$level+1] = $weitere;
	
			//Object erstellen
			$resObject=new resourceObject($db->f("resource_id"));

			//Daten vorbereiten
			$icon="<img src=\"pictures/cont_folder2.gif\" />";
			if ($resources_data["structure_open"] == $resObject->id) {
				$link=$PHP_SELF."?structure_close=".$resObject->id."#a";
				$open="open";
				echo "<a name=\"a\"></a>";
			} else {
				$link=$PHP_SELF."?structure_open=".$resObject->id."#a";
				$open="close";
			}
			if ($resObject->getCategory())
				$titel=$resObject->getCategory().": ";
			if ($edit_structure_object==$resObject->id) {
				echo "<a name=\"a\"></a>";
				$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
			} else {
				$titel.=htmlReady($resObject->getName());
			}
			if ($resObject->getOwnerLink())
				$zusatz=sprintf ("Besitzer: <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), $resObject->getOwnerName());
			else			
				$zusatz=sprintf ("Besitzer: %s", $resObject->getOwnerName());
			$new=TRUE;
			if ($edit_structure_object==$resObject->id) {
				$content.= "<br /><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br />";
				$content.= "<input type=\"image\" src=\"./pictures/buttons/uebernehmen-button.gif\" border=0 value=\"&Auml;nderungen speichern\" />";
				$content.= "&nbsp;<input type=\"image\" src=\"./pictures/buttons/abbrechen-button.gif\" border=0 value=\"Abbrechen\" />";						
				$content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\" />";
				$open="open";
			} else {
				$content=htmlReady($resObject->getDescription());
			}
			if (!$weitere) {
				$edit.= "<a href=\"$PHP_SELF?kill_object=$resObject->id\"><img src=\"./pictures/buttons/loeschen-button.gif\" border=0></a>";
			} else {
				$edit.= "&nbsp;<a href=\"$PHP_SELF?open_list=$resObject->id\"><img src=\"./pictures/buttons/listeoeffnen-button.gif\" border=0></a>";
			}
			$edit.= "&nbsp;<a href=\"$PHP_SELF?pre_move_object=$resObject->id\"><img src=\"./pictures/buttons/verschieben-button.gif\" border=0></a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_object=$resObject->id\"><img src=\"./pictures/buttons/neuesobjekt-button.gif\" border=0></a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_hierachie_level=$resObject->id\"><img src=\"./pictures/buttons/neueebene-button.gif\" border=0></a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?edit_object=$resObject->id\"><img src=\"./pictures/buttons/bearbeiten-button.gif\" border=0></a>";
			

			//Daten an Ausgabemodul senden (aus resourcesVisual)
			$this->printRow($icon, $link, $titel, $zusatz, $level, $lines, $weitere, $new, $open, $content, $edit);
			
			//in weitere Ebene abtauchen
			while ($db2->next_record()) {
				$this->createThread($db2->f("resource_id"), $level+1, $lines);
			}
		}
	}
}

/*****************************************************************************
cssClasses, Klasse um cssClasses auszuwaehlen
/*****************************************************************************/
class cssClasses {
	var $db;				//Datenbankanbindung;
	var $db2;				//Datenbankanbindung;
	var $class=array
		(1=>"steelgraulight", 
		2=>"steel1"); 		//Klassen fuer Zebra
	var $headerClass="steel";
	var $classcnt=1;		//Counter fuer Zebra

	function getClass() {
		return $this->class[$this->classcnt];
	}

	function getHeaderClass() {
		return $this->headerClass;
	}

	function switchClass() {
		$this->classcnt++;
		if ($this->classcnt >sizeof($this->class))
			$this->classcnt=1;
	}
}

/*****************************************************************************
editSettings, Darstellung der unterschiedlichen Forms zur 
Bearbeitung der grundlegenden Einstellungen der Ressourcen-
verwaltung
/*****************************************************************************/

class editSettings extends cssClasses {
	var $db;
	var $db2;
	
	//Konstruktor
	function editSettings() {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
	}

	function getDependingResources($category_id)  {
		$db=new DB_Seminar;
		$db->query("SELECT count(resource_id) AS count FROM resources_objects WHERE category_id='$category_id' ");
		$db->next_record();
		return $db->f("count");
	}

	function getDependingTypes($property_id)  {
		$db=new DB_Seminar;
		$db->query("SELECT count(category_id) AS count FROM resources_categories_properties WHERE property_id='$property_id' ");
		$db->next_record();
		return $db->f("count");
	}

	function selectTypes() {
		$this->db->query("SELECT *  FROM resources_categories ORDER BY name");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	function selectRootUser() {
		$this->db->query("SELECT *  FROM resources_user_resources WHERE resource_id ='all' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}
	
	function selectProperties($category_id='', $all=FALSE) {
		if (!$all)
			$this->db2->query ("SELECT *  FROM resources_properties LEFT JOIN resources_categories_properties USING (property_id) WHERE category_id = '$category_id' ORDER BY name");
		else
			$this->db2->query ("SELECT *  FROM resources_properties ORDER BY name");		
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	function create_perms_forms() {
		global $PHP_SELF, $search_string_search_root_user, $search_root_user;
		
		$resObject=new resourceObject();
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<form method="POST" action="<?echo $PHP_SELF ?>?add_root_user=TRUE">
			<tr>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="42%" align="left">
					<font size=-1><b>Name</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="10%" align="center">
					<font size=-1><b>Berechtigungen</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="10%" align="center">
					<font size=-1><b>X</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="30%" align="center">
					<font size=-1><b>Suchen/hinzuf&uuml;gen</b></font>
				</td>
			</tr>
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="42%">
					<font size=-1>Diese Nutzer sind als Ressourcen-Root eingetragen, sie haben damit Zugriff auf alle globalen Ressourcen</font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="30%" valign="top"><font size=-1>Nutzer hinzuf&uuml;gen</font><br />
				<?create_search_form("search_root_user", $search_string_search_root_user, TRUE, TRUE) ?>
				</td>
			</tr>
			<?
			$this->selectRootUser();
			while ($this->db->next_record()) {
			?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="42%" valign="top">
					<font size=-1><a href="<? echo $resObject->getOwnerLink($this->db->f("user_id"))."\">".$resObject->getOwnerName(TRUE, $this->db->f("user_id")) ?></a></font><br />
					<font size=-1>(<? echo get_username($this->db->f("user_id")); ?>)</font><br />
				</td>
				<input type="HIDDEN" name="change_root_user_id[]" value="<? echo $this->db->f("user_id") ?>" />
				<td class="<? echo $this->getClass() ?>" width="10%" valign="middle">
					<select name="change_root_user_perms[]">
						<font size=-1><option <? ($this->db->f("perms")=="user") ? printf ("selected") : printf (""); ?>>user</option></font>
						<font size=-1><option <? ($this->db->f("perms")=="admin") ? printf ("selected"): printf (""); ?>>admin</option></font>
					</select>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" valign="middle" align="right">
					<font size=-1>
						Nutzer&nbsp; 
						<a href="<? echo $PHP_SELF ?>?delete_root_user_id=<? echo $this->db->f("user_id") ?>">
						<img src="pictures/buttons/loeschen-button.gif" alt="Typ l&ouml;schen" border=0>
					</font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="30%" align="center">&nbsp; 
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=5 align="center"><br />&nbsp; <input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 name="submit" value="Zuweisen"><br />&nbsp; 
				</td>
			</tr>
		</table>
		<br /><br />
		<?
	}

	function create_types_forms() {
		global $PHP_SELF;
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="25%" align="left">
					<font size=-1><b>Typ</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="65%" align="left">
					<font size=-1><b>zugeordnete Eigenschaften</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="6%" align="center">
					<font size=-1><b>X</b></font>
				</td>
			</tr>
			<form method="POST" action="<?echo $PHP_SELF ?>?add_type_category_id=<? echo $this->db2->f("category_id")?>">
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" align="left">
					<font size=-1>neuer Typ:</font>
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="left">
					<font size=-1><input type="TEXT" name="add_type" size=50 maxlength=255 value="<bitte geben Sie hier den Namen ein>" /></font>
					&nbsp; <font size=-1><input type="IMAGE" name="_add_type" src="pictures/buttons/anlegen-button.gif" border=0 /></font>							
				</td>
			</tr>	
			</form>
			<? 
			$this->selectTypes();
			while ($this->db->next_record()) {
				$depRes=$this->getDependingResources($this->db->f("category_id"));
				?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" valign="top">
					<font size=-1><? echo $this->db->f("name") ?></font><br />
					<font size=-1>wird von <b><? echo  $depRes ?></b> Objekten verwendet</font><br />
					<font size=-1><? ($this->db->f("system")) ? print( "systemobjekt") :print("") ?></font><br />
				</td>
				<td class="<? echo $this->getClass() ?>" width="65%" valign="top">
					<table border=0 celpadding=2 cellspacing=0 width="100%" align="center">
						<?
						$tmp_resvis='';
						$this->selectProperties($this->db->f("category_id"));
						while ($this->db2->next_record()) {
							//schon zugewiesene Properties merken
							$tmp_resvis[]=$this->db2->f("property_id");
						?>
						<tr>
							<td class="<? echo $this->getClass() ?>" width="33%">
								<font size=-1><? echo $this->db2->f("name") ?></font><br />
							</td>
							<td class="<? echo $this->getClass() ?>" width="33%">
								<font size=-1><? 						
									switch ($this->db2->f("type")) {
										case "bool":
											echo "Zustand Ja/Nein";
										break; 
										case "text":
											echo "mehrzeiliges Textfeld";
										break; 
										case "num":
											echo "einzeiliges Textfeld";
										break; 
										case "select":
											echo "definiertes Auswahlfeld";
										break; 
									}
									?>
								</font><br />
							</td>
							<td class="<? echo $this->getClass() ?>" width="34%">
								<a href="<? echo $PHP_SELF ?>?delete_type_property_id=<? echo $this->db2->f("property_id") ?>&delete_type_category_id=<? echo $this->db2->f("category_id") ?>">
									<img src="pictures/trash.gif" alt="Eigenschaft l&ouml;schen" border=0>
								</a>
							</td>
						</tr>
						<? } ?>
						<form method="POST" action="<?echo $PHP_SELF ?>?add_type_category_id=<? echo $this->db->f("category_id")?>">
						<tr>
							<td class="<? echo $this->getClass() ?>" width="33%">
								<select name="add_type_property_id">
								<?
								$this->selectProperties($this->db->f("category_id"), TRUE);
								//Noch nicht vergebene Propertys zum Vergeben anbieten
								while ($this->db2->next_record()) {
									if (is_array($tmp_resvis))
										if (!in_array($this->db2->f("property_id"), $tmp_resvis)) 
											$give_it=TRUE;
										else
											$give_it=FALSE;
									else
										$give_it=TRUE;
									if ($give_it) {
										?>
									<option value="<? echo $this->db2->f("property_id") ?>"><? echo $this->db2->f("name") ?></option>
									</option>
										<?
											
									}
								}
								?>
								</tr>
								<td class="<? echo $this->getClass() ?>" width="67%" colspan=2>
									<input type="IMAGE" src="pictures/buttons/zuweisen-button.gif" border=0 /> 
								</select>
							</td>
						</tr>
						</form>
					</table>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" valign="bottom"align="center">
					<font size=-1>
						diesen Typ<br />
						<?
						if (($depRes==0) && (!$this->db->f("system"))) {
						?>
						<a href="<? echo $PHP_SELF ?>?delete_type=<? echo $this->db->f("category_id") ?>">
						<img src="pictures/buttons/loeschen-button.gif" alt="Typ l&ouml;schen" border=0>
						<?} else {?>
						<img src="pictures/buttons/n_loeschen2-button.gif" border=0 /> 
						<?} ?>
					</font><br />
				</td>
			</tr>
			<? } ?>
		</table>
		<br /><br />
		<?
	}
	
	function create_properties_forms() {
		global $PHP_SELF;
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="25%" align="left">
					<font size=-1><b>Eigenschaft</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="65%" align="left">
					<font size=-1><b>Art der Eigenschaft</b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="6%" align="center">
					<font size=-1><b>X</b></font>
				</td>
			</tr>
			<form method="POST" action="<?echo $PHP_SELF ?>?add_type_category_id=<? echo $this->db2->f("category_id")?>">
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" align="left">
					<font size=-1>neue Eigenschaft:</font>
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="left" valign="bottom">
					<font size=-1><input type="TEXT" name="add_property" size=50 maxlength=255 value="<bitte geben Sie hier den Namen ein>" /></font>
					<select name="add_property_type">
						<font size=-1><option value="bool">Zustand</option></font>
						<font size=-1><option value="num">einzeiligesTextfeld</option></font>
						<font size=-1><option value="text">mehrzeiligesTextfeld</option></font>
						<font size=-1><option value="select">Auswahlfeld</option></font>
					</select>
					&nbsp;<font size=-1><input type="IMAGE" name="_add_property" src="pictures/buttons/anlegen-button.gif" border=0 /></font>							
				</td>
			</tr>	
			</form>
			<? 
			$this->selectProperties($dummy, TRUE);
			while ($this->db2->next_record()) {
				$depTyp=$this->getDependingTypes($this->db2->f("property_id"));
				?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" valign="top">
					<font size=-1><? echo $this->db2->f("name") ?></font><br />
					<font size=-1>wird von <b><? echo  $depTyp ?></b> Typen verwendet</font><br />
					<font size=-1><? ($this->db2->f("system")) ? print( "systemobjekt") :print("") ?></font><br />
				</td>
				<td class="<? echo $this->getClass() ?>" width="65%" valign="top">
					<table border=0 celpadding=2 cellspacing=0 width="100%" align="center">
					<tr>
					<form method="POST" action="<?echo $PHP_SELF ?>?send_property_type_id=<? echo $this->db2->f("property_id")?>">
						<td class="<? echo $this->getClass() ?>" width="50%">
							<font size=-1>Art:</font>
							<br />
							<select name="send_property_type">
								<font size=-1><option <? ($this->db2->f("type") == "bool") ? print "selected" : print "" ?> value="bool">Zustand</option></font>
								<font size=-1><option <? ($this->db2->f("type") == "num") ? print "selected" : print "" ?> value="num">einzeiliges Textfeld</option></font>
								<font size=-1><option <? ($this->db2->f("type") == "text") ? print "selected" : print "" ?> value="text">mehrzeilges Textfeld</option></font>
								<font size=-1><option <? ($this->db2->f("type") == "select") ? print "selected" : print "" ?> value="select">Auswahlfeld</option></font>
							</select>
							<br />
							<?
							if ($this->db2->f("type") == "bool") {
								printf ("<font size=-1>Bezeichnung:</font><br />");
								printf ("<font size=-1><input type=\"TEXT\" name=\"send_property_bool_desc\" value=\"%s\" size=30 maxlength=255 /></font><br />", ($this->db2->f("options")) ? $this->db2->f("options") : "vorhanden");
							}
							if ($this->db2->f("type") == "select") {
								printf ("<font size=-1>Optionen:</font><br />");
								printf ("<font size=-1><input type=\"TEXT\" name=\"send_property_select_opt\" value=\"%s\" size=30 maxlength=255 /></font><br />", $this->db2->f("options"));
							}
							?>
							<font size=-1>Vorschau:</font>
							<br/>
							<?
							switch ($this->db2->f("type")) {
								case "bool":
									printf ("<input type=\"CHECKBOX\" name=\"dummy\" checked />&nbsp; <font size=-1>%s</font>", $this->db2->f("options"));
								break;
								case "num":
									printf ("<input type=\"TEXT\" name=\"dummy\" size=30 maxlength=255 />");
								break;
								case "text";
									printf ("<textarea name=\"dummy\" cols=30 rows=2 ></textarea>");
								break;
								case "select";
									$options=explode (";",$this->db2->f("options"));
									printf ("<select name=\"dummy\">");
									foreach ($options as $a) {
										printf ("<option value=\"%s\">%s</option>", $a, htmlReady($a));
									}
									printf ("</select>");
								break;
							}
							?>
						</td>
						<td class="<? echo $this->getClass() ?>" width="50%" valign="bottom">&nbsp; 
						 	<input type="IMAGE" name="_send_property_type" src="./pictures/buttons/uebernehmen-button.gif" border=0 />
						 </td>
					</tr>
					</form>
					</table>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" valign="bottom"align="center">
					<font size=-1>
						diese Eigenschaft<br />
						<?
						if (($depTyp==0) && (!$this->db->f("system"))) {
						?>
						<a href="<? echo $PHP_SELF ?>?delete_property=<? echo $this->db2->f("property_id") ?>">
						<img src="pictures/buttons/loeschen-button.gif" alt="Eigenschaft l&ouml;schen" border=0>
						<?} else {?>
						<img src="pictures/buttons/n_loeschen2-button.gif" border=0 /> 
						<?} ?>
					</font><br />
				</td>
			</tr>
			<? } ?>
		</table>
		<br /><br />
		<?
	}	
	
	
}

/*****************************************************************************
editObject, Darstellung der unterschiedlichen Forms zur 
Bearbeitung eines Objects
/*****************************************************************************/
class editObject extends cssClasses {
	var $resObject;		//Das Oject an dem gearbeitet wird
	
	//Konstruktor
	function editObject($resource_id) {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
		$this->resObject=new resourceObject($resource_id);
	}
	
	function selectCategories() {
		$this->db->query("SELECT * FROM resources_categories");
	}

	function selectProperties() {
		$this->db->query ("SELECT resources_properties.name, resources_properties.description, resources_properties.type, resources_properties.options, resources_properties.system, resources_properties.property_id  FROM resources_properties LEFT JOIN resources_categories_properties USING (property_id) LEFT JOIN resources_objects USING (category_id) WHERE resources_objects.resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	function selectPerms() {
		$this->db->query ("SELECT *  FROM resources_user_resources WHERE resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	function create_propertie_forms() {
		global $PHP_SELF;
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_properties=<? echo $this->resObject->getId() ?>">
			<input type="HIDDEN" name="view" value="edit_object_properties" />
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>"><font size=-1>Name:</font><br />
				<font size=-1><input name="change_name" value="<? echo $this->resObject->getName() ?>" size=60 maxlength="255" />
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%"><font size=-1>Typ des Objekts:</font><br />
				<font size=-1>
					<select name="change_category_id">
					<?
					$this->selectCategories();
					if (!$this->resObject->getCategoryId())
						echo "<option select value=\"\">nicht zugeordnet</option>";
					while ($this->db->next_record()) {
						if ($this->db->f("category_id")==$this->resObject->getCategoryId()) {
							echo "<option selected value=\"".$this->db->f("category_id")."\">".$this->db->f("name")."</option>";
							}
						else
							echo "<option value=\"".$this->db->f("category_id")."\">".$this->db->f("name")."</option>";
					}
					?>
					</select><img src="./pictures/pfeiltransparent.gif" border=0><input type="IMAGE" name="assign" value="Zuweisen"src="./pictures/buttons/zuweisen-button.gif" border=0>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>"><font size=-1>Beschreibung:</font><br />
			
				<font size=-1><textarea name="change_description" rows=3 cols=60><? echo $this->resObject->getDescription() ?></textarea>
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%" valign="top"><font size=-1>Besitzer:</font><br />
				<font size=-1><? echo $this->resObject->getOwnerName() ?></font>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>"><font size=-1>Inventarnummer:</font><br />
				<font size=-1><input name="change_inventar_num" value="<? echo $this->resObject->getInventarNum() ?>" size=60 maxlength="255" />
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%" valign="top"><font size=-1>Vererbte Belegung:</font><br />
				<font size=-1><input type="CHECKBOX" name="change_parent_bind" <? if ($this->resObject->getParentBind()) echo "checked" ?> >
				Objekt &uuml;bernimmt Belegung von &uuml;bergeordnetem Objekt</font>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2><font size=-1><b>Eigenschaften</b></font><br />
				</td>
			</tr>
			<? if ($this->resObject->getCategoryId()) {?>
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="center">
				</td>
			</tr>
				<?
				$this->selectProperties();
				while ($this->db->next_record()) {
					?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>">
					<font size=-1><? echo $this->db->f("name"); ?></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%">
				<font size=-1>
				<?
					$this->db2->query("SELECT * FROM resources_objects_properties WHERE resource_id = '".$this->resObject->getId()."' AND property_id = '".$this->db->f("property_id")."' ");
					$this->db2->next_record();
					printf ("<input type=\"HIDDEN\" name=\"change_property_val[]\" value=\"%s\" />", "_id_".$this->db->f("property_id"));
					switch ($this->db->f("type")) {
						case "bool":
							printf ("<input type=\"CHECKBOX\" name=\"change_property_val[]\" %s /><font size=-1>&nbsp;%s</font>", ($this->db2->f("state")) ? "checked":"", $this->db->f("options"));
						break;
						case "num":
							printf ("<input type=\"TEXT\" name=\"change_property_val[]\" value=\"%s\" size=30 maxlength=255 />", $this->db2->f("state"));
						break;
						case "text";
							printf ("<textarea name=\"change_property_val[]\" cols=30 rows=2 >%s</textarea>", $this->db2->f("state"));
						break;
						case "select";
							$options=explode (";",$this->db->f("options"));
							printf ("<select name=\"change_property_val[]\">");
							foreach ($options as $a) {
								printf ("<option %s value=\"%s\">%s</option>", ($this->db2->f("state") == $a) ? "selected":"", $a, htmlReady($a));
							}
							printf ("</select>");
						break;
					}
				?></td>
			</tr><?
				}
			} else { ?>
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2>
				<font size=-1 color="red">Das Objekt wurde noch keinem Typ zugewiesen. Um Eigenschaften bearbeiten zu k&ouml;nnen, m&uuml;ssen Sie vorher einen Typ festlegen!</font>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="center"><br />&nbsp; <input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 name="submit" value="Zuweisen"><br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
		<br /><br />
		<?
	}	

	function create_perm_forms() {
		global $PHP_SELF, $search_owner, $search_perm_user, $search_string_search_perm_user, $search_string_search_owner;
		
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_perms=<? echo $this->resObject->getId() ?>">
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2><font size=-1>Besitzer:</font><br />
				<font size=-1><a href="<? echo $this->resObject->getOwnerLink()?>"><? echo $this->resObject->getOwnerName(TRUE) ?></a></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="60%"><font size=-1>Besitzer &auml;ndern:</font><font size=-1 color="red"></font><br />
				<? create_search_form("search_owner", $search_string_search_owner) ?>
				</td>
			</tr>
			<tr>

				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 valign="bottom"><font size=-1>Berechtigungen:</font><br />
				<td class="<? echo $this->getClass() ?>" width="60%" valign="top"><font size=-1>Berechtigungen hinzuf&uuml;gen</font><br />
				<?create_search_form("search_perm_user", $search_string_search_perm_user) ?>
				</td>
			</tr>
			<?
			$i=0;
			if ($this->selectPerms())
				while ($this->db->next_record()) {
				?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				<td class="<? echo $this->getClass() ?>" width="20%">
					<input type="HIDDEN" name="change_user_id[]" value="<? echo $this->db->f("user_id")?>" />
					<font size=-1><a href="<? echo $this->resObject->getOwnerLink($this->db->f("user_id"))?>"><? echo $this->resObject->getOwnerName(TRUE, $this->db->f("user_id")) ?></a></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="*">
					<font size=-1>&nbsp; 
					<input type="RADIO" name="change_user_perms[<?echo $i ?>]" value="user"<? if ($this->db->f("perms") == "user") echo "checked" ?>  />user
					<input type="RADIO" name="change_user_perms[<?echo $i ?>]" value="admin"<? if ($this->db->f("perms") == "admin") echo "checked" ?>  />admin
					&nbsp; <a href="<? echo $PHP_SELF ?>?change_object_perms=<? echo $this->resObject->getId() ?>&delete_user_perms=<? echo $this->db->f("user_id") ?>"><img src="pictures/trash.gif" alt="Berechtigung l&ouml;schen" border=0></a>
				</td>
				<td class="<? echo $this->getClass() ?>" width="60%" valign="top">&nbsp; 
				</td>
			</tr>
				
				<?
				$i++;  
				}
			else {
				?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				<td class="<? echo $this->getClass() ?>" colspan=3>
					<font size=-1>Es sind keine weiteren Berechtigungen eingetragen</font>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=3 align="center"><br />&nbsp; <input type="IMAGE" src="pictures/buttons/uebernehmen-button.gif" border=0 name="submit" value="Zuweisen"><br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
		<br /><br />
		<?
	}	
}
?>