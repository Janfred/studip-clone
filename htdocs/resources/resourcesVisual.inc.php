<?
/**
* resourcesVisual.php
* 
* classes for ouput, forms and other visuals
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	resources
* @module		resourcesVisual.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// resourcesVisual.php
// Klassen zur Darstellung aus Ausgabe innerhalb der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
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



/*****************************************************************************
ShowList, stellt Liste mit Hilfe von printThread dar
/*****************************************************************************/
require_once ($RELATIVE_PATH_RESOURCES."/lib/ShowTreeRow.class.php");

class ShowList extends ShowTreeRow{
	var $db;
	var $db2;
	var $recurse_levels;			//How much Levels should the List recurse
	var $supress_hierachy_levels;		//show only resources with a category or show also the hierarhy-levels (that are resources too)
	var $admin_buttons;			//show admin buttons or not

	function ShowList() {
		$this->recurse_levels=-1;
		$this->supress_hierachy_levels=FALSE;
	
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		
	}

	function setRecurseLevels($levels) {
		$this->recurse_levels=$levels;
	}

	function setAdminButtons($value) {
		$this->admin_buttons=$value;
	}
	
	function setViewHiearchyLevels($mode) {
		if ($mode)
			$this->supress_hierachy_levels=FALSE;
		else
			$this->supress_hierachy_levels=TRUE;
	}
	
	//private
	function showListObject ($resource_id, $admin_buttons=FALSE) {
		global $resources_data, $edit_structure_object, $RELATIVE_PATH_RESOURCES;
	
		//Object erstellen
		$resObject=new ResourceObject($resource_id);

		//Daten vorbereiten
		if (!$resObject->getCategoryIconnr())
			$icon="<img src=\"pictures/cont_folder2.gif\" />";
		else
			$icon="<img src=\"$RELATIVE_PATH_RESOURCES/pictures/cont_res".$resObject->getCategoryIconnr().".gif\" />";

		if ($resources_data["structure_opens"][$resObject->id]) {			
			$link=$PHP_SELF."?structure_close=".$resObject->id."#a";
			$open="open";
			if ($resources_data["actual_object"] == $resObject->id)
				echo "<a name=\"a\"></a>";
		} else {
			$link=$PHP_SELF."?structure_open=".$resObject->id."#a";
			$open="close";
		}
				//create a link onto the titel, too

		$titel='';
		if ($resObject->getCategoryName())
			$titel=$resObject->getCategoryName().": ";
		if ($edit_structure_object==$resObject->id) {
			echo "<a name=\"a\"></a>";
			$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
		} else {
			$titel.=htmlReady($resObject->getName());
		}

		//create a link on the titel, too
		if (($link) && ($edit_structure_object != $resObject->id))
			$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
		
		if ($resObject->getOwnerLink())
			$zusatz=sprintf (_("Besitzer:")." <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), $resObject->getOwnerName());
		else			
			$zusatz=sprintf (_("Besitzer:")." %s", $resObject->getOwnerName());
		$new=TRUE;
		if ($edit_structure_object==$resObject->id) {
			$content= "<br /><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br />";
			$content.= "<input type=\"image\" name=\"send\" align=\"absmiddle\" ".makeButton("uebernehmen", "src")." border=0 value=\""._("&Auml;nderungen speichern")."\" />";
			$content.= "&nbsp;<a href=\"$PHP_SELF?cancel_edit=$resObject->id\">".makeButton("abbrechen", "img")."</a>";						
			$content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\" />";
			$open="open";
		} else {
			$content=htmlReady($resObject->getDescription());
		}
		if ($admin_buttons) {
			if (!$weitere) {
				$edit= "<a href=\"$PHP_SELF?kill_object=$resObject->id\">".makeButton("loeschen")."</a>";
			} 
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_object=$resObject->id\">".makeButton("neuesobjekt")."</a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?edit_object=$resObject->id\">".makeButton("details")."</a>";
		} else {
			$edit.= "&nbsp;<a href=\"$PHP_SELF?show_object=$resObject->id&view=view_details\">".makeButton("details")."</a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?show_object=$resObject->id&view=view_schedule\">".makeButton("belegung")."</a>";
		}

		//Daten an Ausgabemodul senden (aus resourcesVisual)
		$this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
	}
	
	function showListObjects ($start_id='', $level=0, $result_count=0) {

		$db=new DB_Seminar;	
		$db2=new DB_Seminar;
		
		//Let's start and load all the threads
		$query = sprintf ("SELECT resource_id FROM resources_objects WHERE parent_id = '%s' %s", $start_id, ($this->supress_hierachy_levels) ? "AND category_id != ''" : "");
		$db->query($query);
		
		//if we have an empty result
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;
			
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"), $this->admin_buttons);
			//in weitere Ebene abtauchen
			if (($this->recurse_levels == -1) || ($level + 1 < $this->recurse_levels)) {
				//Untergeordnete Objekte laden
				$db2->query("SELECT resource_id FROM resources_objects WHERE parent_id = '".$db->f("resource_id")."' ");
				
				while ($db2->next_record())
					$this->showListObjects($db2->f("resource_id"), $level+1, $result_count);
			}
			$result_count++;
		}
	return $result_count;
	}
	
	function showRangeList($range_id) {
		$db=new DB_Seminar;	
		
		//create the query for all objects owned by the range
		$query = sprintf ("SELECT resource_id FROM resources_objects WHERE owner_id = '%s' ", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}

		//create the query for all additionale perms by the range to an object 
		$query = sprintf ("SELECT resource_id FROM  resources_user_resources WHERE user_id = '%s' ", $range_id);
		$db->query($query);
		
		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}
		
	return $result_count;		
	}
	
	function showSearchList($search_array) {

		$db=new DB_Seminar;	
		
		//create the query
		if (($search_array["search_exp"]) && (!$search_array["search_properties"]))
			$query = sprintf ("SELECT resource_id FROM resources_objects WHERE name LIKE '%%%s%%' ORDER BY name", $search_array["search_exp"]);

		if ($search_array["properties"]) {
			$query = sprintf ("SELECT DISTINCT resources_objects_properties.resource_id FROM resources_objects_properties %s WHERE ", ($search_array["search_exp"]) ? "LEFT JOIN resources_objects USING (resource_id)" : "");
			
			$i=0;
			foreach ($search_array["properties"] as $key => $val) {
				if ($val == "on")
					$val = 1;
				
				$query.= sprintf(" %s (property_id = '%s' AND state = '%s') ", ($i) ? "AND" : "", $key, $val);
				$i++;
			}
			
			if ($search_array["search_exp"]) 
				$query.= sprintf(" AND (name LIKE '%%%s%%' OR description LIKE '%%%s%%') ", $search_array["search_exp"], $search_array["search_exp"]);
		}
		
		$db->query($query);
		
		//if we have an empty result
		if ((!$db->num_rows()) && ($level==0))
			return FALSE;

		while ($db->next_record()) {
			$this->showListObject($db->f("resource_id"));
			$result_count++;
		}
	return $result_count;
	}
}

/*****************************************************************************
ShowThread, stellt Struktur mit Hilfe von printThread dar
/*****************************************************************************/
require_once ($RELATIVE_PATH_RESOURCES."/lib/ShowTreeRow.class.php");

class ShowThread extends ShowTreeRow {
	var $lines;		//Uebersichtsarray der Struktur;

	function ShowThread() {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}

	function showThreadLevel ($root_id, $level=0, $lines='') {
		global $resources_data, $edit_structure_object, $RELATIVE_PATH_RESOURCES;
		
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
			$resObject=new ResourceObject($db->f("resource_id"));

			//Daten vorbereiten
			if (!$resObject->getCategoryIconnr())
				$icon="<img src=\"pictures/cont_folder2.gif\" />";
			else
				$icon="<img src=\"$RELATIVE_PATH_RESOURCES/pictures/cont_res".$resObject->getCategoryIconnr().".gif\" />";

			if ($resources_data["move_object"])
				$icon="&nbsp;<a href=\"$PHP_SELF?target_object=".$resObject->id."#a\"><img src=\"pictures/move.gif\" border=0 alt=\""._("Objekt in diese Ebene verschieben")."\" /></a>".$icon;
			
			if ($resources_data["structure_opens"][$resObject->id]) {
				$link=$PHP_SELF."?structure_close=".$resObject->id."#a";
				$open="open";
				if ($resources_data["actual_object"] == $resObject->id)
					echo "<a name=\"a\"></a>";
			} else {
				$link=$PHP_SELF."?structure_open=".$resObject->id."#a";
				$open="close";
			}
			
			if ($resObject->getCategoryName())
				$titel=$resObject->getCategoryName().": ";
			if ($edit_structure_object==$resObject->id) {
				echo "<a name=\"a\"></a>";
				$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
			} else {
				$titel.=htmlReady($resObject->getName());
			}

			//create a link on the titel, too
			if (($link) && ($edit_structure_object != $resObject->id))
				$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
			
			if ($resObject->getOwnerLink())
				$zusatz=sprintf ("Besitzer: <a href=\"%s\"><font color=\"#333399\">%s</font></a>", $resObject->getOwnerLink(), $resObject->getOwnerName());
			else			
				$zusatz=sprintf ("Besitzer: %s", $resObject->getOwnerName());
			$new=TRUE;
			if ($edit_structure_object==$resObject->id) {
				$content.= "<br /><textarea name=\"change_description\" rows=3 cols=40>".htmlReady($resObject->getDescription())."</textarea><br />";
				$content.= "<input type=\"image\" name=\"send\" align=\"absmiddle\" ".makeButton("uebernehmen", "src")." border=0 value=\""._("&Auml;nderungen speichern")."\" />";
				$content.= "&nbsp;<a href=\"$PHP_SELF?cancel_edit=$resObject->id\">".makeButton("abbrechen", "img")."</a>";						
				$content.= "<input type=\"hidden\" name=\"change_structure_object\" value=\"".$resObject->getId()."\" />";
				$open="open";
			} else {
				$content=htmlReady($resObject->getDescription());
			}
			if ($resources_data["move_object"] == $resObject->id) 
				$content.= sprintf ("<br />"._("Dieses Objekt wurde zum Verschieben markiert. Bitte w&auml;hlen sie das Einf&uuml;gen-Symbol %s, um es in die gew&uuml;nschten Ebene zu verschieben."), "<img src=\"pictures/move.gif\" border=0 alt=\""._("Klicken Sie dieses Symbol, um dieses Objekt in eine andere Ebene zu verschieben")."\">");
			if (!$weitere) {
				$edit.= "<a href=\"$PHP_SELF?kill_object=$resObject->id\">".makeButton("loeschen", "img")."</a>";
			} else {
				$edit.= "&nbsp;<a href=\"$PHP_SELF?open_list=$resObject->id\">".makeButton("listeoeffnen", "img")."</a>";
			}
			if ($resources_data["move_object"] == $resObject->id) 
				$edit.= "&nbsp;<a href=\"$PHP_SELF?cancel_move=TRUE\">".makeButton("abbrechen", "img")."</a>";			
			else
				$edit.= "&nbsp;<a href=\"$PHP_SELF?pre_move_object=$resObject->id\">".makeButton("verschieben", "img")."</a>";			
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_object=$resObject->id\">".makeButton("neuesobjekt", "img")."</a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_hierachie_level=$resObject->id\">".makeButton("neueebene", "img")."</a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?edit_object=$resObject->id\">".makeButton("details", "img")."</a>";
			

			//Daten an Ausgabemodul senden (aus resourcesVisual)
			$this->showRow($icon, $link, $titel, $zusatz, $level, $lines, $weitere, $new, $open, $content, $edit);
			
			//in weitere Ebene abtauchen
			while ($db2->next_record()) {
				if ($resources_data["structure_opens"][$db->f("resource_id")])
					$this->showThreadLevel($db2->f("resource_id"), $level+1, $lines);
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

	function showPermsForms() {
		global $PHP_SELF, $search_string_search_root_user, $search_root_user;
		
		$resObject=new ResourceObject();
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<form method="POST" action="<?echo $PHP_SELF ?>?add_root_user=TRUE">
			<tr>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="42%" align="left">
					<font size=-1><b><?=_("Name")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="10%" align="center">
					<font size=-1><b><?=_("Berechtigungen")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="10%" align="center">
					<font size=-1><b><?=_("X")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="30%" align="center">
					<font size=-1><b><?=_("Suchen/hinzuf&uuml;gen")?></b></font>
				</td>
			</tr>
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="42%">
					<font size=-1><?=_("Diese Nutzer sind als Ressourcen-Root eingetragen, sie haben damit Zugriff auf alle globalen Ressourcen")?></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="30%" valign="top"><font size=-1><?=_("Nutzer hinzuf&uuml;gen")?></font><br />
				<? showSearchForm("search_root_user", $search_string_search_root_user, TRUE, FALSE, TRUE) ?>
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
						<font size=-1><option <? ($this->db->f("perms")=="autor") ? printf ("selected") : printf (""); ?>>autor</option></font>
						<font size=-1><option <? ($this->db->f("perms")=="admin") ? printf ("selected"): printf (""); ?>>admin</option></font>
					</select>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" valign="middle" align="right">
					<font size=-1>
						<?=_("Nutzer")?>&nbsp; 
						<a href="<? echo $PHP_SELF ?>?delete_root_user_id=<? echo $this->db->f("user_id") ?>">
						<?=makeButton("loeschen", "img") ?> 
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
				<td class="<? echo $this->getClass() ?>" colspan=5 align="center"><br />&nbsp; <input type="IMAGE" <?=makeButton("uebernehmen", "src") ?> border=0 name="submit" value="<?=_("Zuweisen") ?>"><br />&nbsp; 
				</td>
			</tr>
		</table>
		<br /><br />
		<?
	}

	function showTypesForms() {
		global $PHP_SELF, $RELATIVE_PATH_RESOURCES, $created_category_id;
		
		//the avaiable object-icons for every category
		$avaiableIcons = array (1=>"cont_res1.gif",2=> "cont_res2.gif",3=> "cont_res3.gif", 4=>"cont_res4.gif",5=> "cont_res5.gif");
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="25%" align="left">
					<font size=-1><b><?=_("Typ")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="65%" align="left">
					<font size=-1><b><?=_("zugeordnete Eigenschaften")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="6%" align="center">
					<font size=-1><b><?=_("X")?></b></font>
				</td>
			</tr>
			<form method="POST" action="<?echo $PHP_SELF ?>#a">
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" align="left">
					<font size=-1><?=_("neuer Typ:")?></font>
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="left">
					<font size=-1><input type="TEXT" name="add_type" size=50 maxlength=255 value="<<?=_("bitte geben Sie hier den Namen ein")?>>" /></font>
					&nbsp; <font size=-1><input type="IMAGE" name="_add_type" <?=makeButton("anlegen", "src")?> border=0 /></font>							
				</td>
			</tr>	
			</form>
			<form method="POST" action="<?echo $PHP_SELF ?>?change_categories=TRUE">
			<? 
			$this->selectTypes();
			while ($this->db->next_record()) {
				$depRes=$this->getDependingResources($this->db->f("category_id"));
				if ($created_category_id == $this->db->f("category_id"))
					print "<a name=\"a\"></a>";
				?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" valign="top">
					<font size=-1><input type="TEXT" name="change_category_name[<?=$this->db->f("category_id")?>]" value="<? echo $this->db->f("name") ?>" size="20" maxlength="255" /></font><br />
					<font size=-1><? printf(_("wird von <b>%s</b> Objekten verwendet")."</font><br />", $depRes); ?>
					<font size=-1><? ($this->db->f("system")) ? print( _("systemobjekt")."<br />") :print("") ?></font>
					
					<?
					foreach ($avaiableIcons as $key => $val) {
						printf ("<input type=\"RADIO\" name=\"change_category_iconnr[%s]\" %s value=\"%s\"><img src=\"%s/pictures/%s\" />&nbsp; ", $this->db->f("category_id"), ($this->db->f("iconnr") == $key) ? "checked" : "", $key, $RELATIVE_PATH_RESOURCES, $val);
					}
					?>
					<input type="HIDDEN" name="change_properties_id[]" value="<?=$this->db->f("category_id")?>" />
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
											echo _("Zustand Ja/Nein");
										break; 
										case "text":
											echo _("mehrzeiliges Textfeld");
										break; 
										case "num":
											echo _("einzeiliges Textfeld");
										break; 
										case "select":
											echo _("definiertes Auswahlfeld");
										break; 
									}
									?>
								</font><br />
							</td>
							<td class="<? echo $this->getClass() ?>" width="34%">
								<a href="<? echo $PHP_SELF ?>?delete_type_property_id=<? echo $this->db2->f("property_id") ?>&delete_type_category_id=<? echo $this->db2->f("category_id") ?>">
									<img src="pictures/trash.gif" <?=tooltip(_("Eigenschaft löschen"))?> 
								</a>
							</td>
						</tr>
						<? } ?>
						<tr>
							<td class="<? echo $this->getClass() ?>" width="33%">
								<select name="add_type_property_id[<?=$this->db->f("category_id")?>]">
								<?
								$this->selectProperties($this->db->f("category_id"), TRUE);
								//Noch nicht vergebene Properties zum Vergeben anbieten
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
									<option value="<? echo $this->db2->f("property_id") ?>"><? echo htmlReady($this->db2->f("name")) ?></option>
									</option>
										<?
											
									}
								}
								?>
								</tr>
								<td class="<? echo $this->getClass() ?>" width="67%" colspan=2>
									<input type="IMAGE" <?=makeButton("zuweisen", "src") ?> name="change_category_add_property<?=$this->db->f("category_id")?>" border=0 /> 
									&nbsp;<input type="IMAGE" name="change_types" <?=makeButton("uebernehmen", "src")?> border="0" /><br />
								</select>
							</td>
						</tr>
					</table>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" valign="bottom"align="center">
					<font size=-1>
						diesen Typ<br />
						<?
						if (($depRes==0) && (!$this->db->f("system"))) {
						?>
						<a href="<? echo $PHP_SELF ?>?delete_type=<? echo $this->db->f("category_id") ?>">
						<?=makeButton ("loeschen", "img");
						} else {
							print makeButton ("n_loeschen", "img");
						} ?>
					</font><br />
				</td>
			</tr>
			<? } ?>
			</form>
		</table>
		<br /><br />
		<?
	}
	
	function showPropertiesForms() {
		global $PHP_SELF;
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? echo $this->getHeaderClass() ?>" width="4%">
					<img src=\"pictures/blank.gif\" width=1 height=20>&nbsp; 
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="25%" align="left">
					<font size=-1><b><?=_("Eigenschaft")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="65%" align="left">
					<font size=-1><b><?=_("Art der Eigenschaft")?></b></font>
				</td>
				<td class="<? echo $this->getHeaderClass() ?>" width="6%" align="center">
					<font size=-1><b><?=_("X")?></b></font>
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
						<font size=-1><option value="bool"<?=_("Zustand")?></option></font>
						<font size=-1><option value="num"><?=_("einzeiligesTextfeld")?></option></font>
						<font size=-1><option value="text"><?=_("mehrzeiligesTextfeld")?></option></font>
						<font size=-1><option value="select"><?=_("Auswahlfeld")?></option></font>
					</select>
					&nbsp;<font size=-1><input type="IMAGE" name="_add_property" <?=makeButton("anlegen", "src")?> border=0 /></font>							
				</td>
			</tr>	
			</form>
			<form method="POST" action="<?echo $PHP_SELF ?>?change_properties=TRUE">
			<? 
			$this->selectProperties($dummy, TRUE);
			while ($this->db2->next_record()) {
				$depTyp=$this->getDependingTypes($this->db2->f("property_id"));
				?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" width="25%" valign="top">
					<font size=-1><input type="TEXT" name="change_property_name[<?=$this->db2->f("property_id")?>]" value="<? echo htmlReady($this->db2->f("name")) ?>" size="20" maxlength="255" /></font><br />
					<font size=-1>wird von <b><? echo  $depTyp ?></b> Typen verwendet</font><br />
					<font size=-1><? ($this->db2->f("system")) ? print( _("systemobjekt")) :print("") ?></font><br />
				</td>
				<td class="<? echo $this->getClass() ?>" width="65%" valign="top">
					<table border=0 celpadding=2 cellspacing=0 width="100%" align="center">
					<tr>
						<td class="<? echo $this->getClass() ?>" width="50%">
							<font size=-1><?=_("Art:")?></font>
							<br />
							<select name="send_property_type[<?=$this->db2->f("property_id")?>]">
								<font size=-1><option <? ($this->db2->f("type") == "bool") ? print "selected" : print "" ?> value="bool"><?=_("Zustand")?></option></font>
								<font size=-1><option <? ($this->db2->f("type") == "num") ? print "selected" : print "" ?> value="num"><?=_("einzeiliges Textfeld")?></option></font>
								<font size=-1><option <? ($this->db2->f("type") == "text") ? print "selected" : print "" ?> value="text"><?=_("mehrzeilges Textfeld")?></option></font>
								<font size=-1><option <? ($this->db2->f("type") == "select") ? print "selected" : print "" ?> value="select"><?=_("Auswahlfeld")?></option></font>
							</select>
							<br />
							<?
							if ($this->db2->f("type") == "bool") {
								printf ("<font size=-1>"._("Bezeichnung:")."</font><br />");
								printf ("<font size=-1><input type=\"TEXT\" name=\"send_property_bool_desc[%s]\" value=\"%s\" size=30 maxlength=255 /></font><br />", $this->db2->f("property_id"), htmlReady(($this->db2->f("options"))) ? $this->db2->f("options") : _("vorhanden"));
							}
							if ($this->db2->f("type") == "select") {
								printf ("<font size=-1>"._("Optionen:")."</font><br />");
								printf ("<font size=-1><input type=\"TEXT\" name=\"send_property_select_opt[%s]\" value=\"%s\" size=30 maxlength=255 /></font><br />", $this->db2->f("property_id"), htmlReady($this->db2->f("options")));
							}
							?>
							<font size=-1>Vorschau:</font>
							<br/>
							<?
							switch ($this->db2->f("type")) {
								case "bool":
									printf ("<input type=\"CHECKBOX\" name=\"dummy\" checked />&nbsp; <font size=-1>%s</font>", htmlReady($this->db2->f("options")));
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
						 	<input type="IMAGE" name="_send_property_type" <?=makeButton("uebernehmen", "src") ?> border=0 />
						 </td>
					</tr>
					</table>
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" valign="bottom"align="center">
					<font size=-1>
						<?=_("diese Eigenschaft")?><br />
						<?
						if (($depTyp==0) && (!$this->db->f("system"))) {
						?>
						<a href="<? echo $PHP_SELF ?>?delete_property=<? echo $this->db2->f("property_id") ?>">
						<?=makeButton("loeschen", "img");
						} else {
							print makeButton("n_loeschen", "img");
						} ?>
					</font><br />
				</td>
			</tr>
			<? } ?>
		</form>
		</table>
		<br /><br />
		<?
	}	
	
	function showPesonalSettingsForms() {
		global $PHP_SELF;
		
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>">
		</table>
		<br /><br />
		<?
	}	
}

class viewObject {
	var $resObject;		//Das Oject an dem gearbeitet wird
	
	//Konstruktor
	function viewObject($resource_id) {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		$this->resObject = new ResourceObject($resource_id);
		$this->cssSw = new cssClassSwitcher;
	}

	function selectProperties() {
		$this->db->query ("SELECT resources_properties.name, resources_properties.description, resources_properties.type, resources_properties.options, resources_properties.system, resources_properties.property_id  FROM resources_properties LEFT JOIN resources_categories_properties USING (property_id) LEFT JOIN resources_objects USING (category_id) WHERE resources_objects.resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}
	
	function view_properties() {
		global $PHP_SELF;

		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_properties=<? echo $this->resObject->getId() ?>">
			<input type="HIDDEN" name="view" value="edit_object_properties" />
			<tr>
				<td class="<? echo $this->cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->cssSw->getClass() ?>"><font size=-1><b><?=_("Name:")?></b></font><br />
				<font size=-1><? echo $this->resObject->getName()." (".(($this->resObject->getCategoryName()) ? $this->resObject->getCategoryName() : _("Hierachieebene")).")" ?>
				</td>
				<td class="<? echo $this->cssSw->getClass() ?>" width="60%" valign="top"><font size=-1><b><?=_("Besitzer:")?></b></font><br />
				<font size=-1><a href="<? echo $this->resObject->getOwnerLink()?>"><? echo $this->resObject->getOwnerName(TRUE);  ?></a></font>
				</td>
			</tr>
			<tr>
				<td class="<? $this->cssSw->switchClass(); echo $this->cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->cssSw->getClass() ?>" valign="top" colspan=2><font size=-1><b><?=_("Beschreibung:")?></b></font><br />
				<font size=-1><? echo $this->resObject->getDescription() ?></font>
				<cho
			</tr>
			<? 
			if ($this->resObject->getCategoryId()) {
			?>
			<tr>
				<td class="<? $this->cssSw->switchClass(); echo $this->cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->cssSw->getClass() ?>" colspan=2><font size=-1><b><?=_("Eigenschaften:")?></b></font>
				</td>
			</tr>
			<?
				$this->selectProperties();
				while ($this->db->next_record()) {
					?>
			<tr>
				<td class="<? 	$this->cssSw->switchClass(); echo $this->cssSw->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->cssSw->getClass() ?>">
					&nbsp; &nbsp; <font size=-1>&bull;&nbsp;<? echo htmlReady($this->db->f("name")); ?></font>
				</td>
				<td class="<? echo $this->cssSw->getClass() ?>" width="40%">
				<font size=-1>
				<?
					$this->db2->query("SELECT * FROM resources_objects_properties WHERE resource_id = '".$this->resObject->getId()."' AND property_id = '".$this->db->f("property_id")."' ");
					$this->db2->next_record();
					switch ($this->db->f("type")) {
						case "bool":
							printf ("%s", ($this->db2->f("state")) ?  htmlReady($this->db->f("options")) : " - ");
						break;
						case "num":
						case "text";
							print htmlReady($this->db2->f("state"));
						break;
						case "select";
							$options=explode (";",$this->db->f("options"));
							foreach ($options as $a) {
								if ($this->db2->f("state") == $a) 
									print htmlReady($a);
							}
						break;
					}
				?></td>
			</tr><?
				}
			}  ?>
		</table>
		<?
	}	
}


/*****************************************************************************
editObject, Darstellung der unterschiedlichen Forms zur 
Bearbeitung eines Objects
/*****************************************************************************/
class EditObject extends cssClasses {
	var $resObject;		//Das Oject an dem gearbeitet wird
	var $used_view;		//the used view
	
	//Konstruktor
	function EditObject($resource_id) {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
		$this->resObject=new ResourceObject($resource_id);
	}
	
	function setUsedView ($value) {
		$this->used_view = $value;
	}
	
	//private
	function selectCategories() {
		$this->db->query("SELECT * FROM resources_categories");
	}

	//private
	function selectProperties() {
		$this->db->query ("SELECT resources_properties.name, resources_properties.description, resources_properties.type, resources_properties.options, resources_properties.system, resources_properties.property_id  FROM resources_properties LEFT JOIN resources_categories_properties USING (property_id) LEFT JOIN resources_objects USING (category_id) WHERE resources_objects.resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	//private
	function selectPerms() {
		$this->db->query ("SELECT *  FROM resources_user_resources WHERE resource_id = '".$this->resObject->getId()."' ");
		if (!$this->db->affected_rows())
			return FALSE;
		else
			return TRUE;
	}

	function showScheduleForms($assign_id='') {
		global $PHP_SELF, $resources_data, $new_assign_object, $search_user, $search_string_search_user;

		if ($new_assign_object)
			$resAssign = unserialize($new_assign_object);
		else
			$resAssign=new AssignObject($assign_id);

		if (($resAssign->getOwnerType() == "sem") || ($resAssign->getOwnerType() == "date")) {
			$resObject=new ResourceObject ($resAssign->getResourceId());
			if ($resObject->getCategoryName() == "Raum")
				$lockedAssign=TRUE;
		}

		if  ($resAssign->getId() && !$resAssign->isNewObject) {
			$ObjectPerms = new AssignObjectPerms($resAssign->getId());
			if ($ObjectPerms->getUserPerm () == "admin")
				$killButton=FALSE;
			elseif ($ObjectPerms->getUserPerm () != "user")
				$lockedAssign=TRUE;
		}


		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_schedules=<? printf ("%s", ($resAssign->getId()) ?  $resAssign->getId() : "NEW"); ?>">
			<input type="HIDDEN" name="view" value="<?=$this->used_view ?>" />
			<input type="HIDDEN" name="change_schedule_resource_id" value="<? printf ("%s", ($assign_id) ? $resAssign->getResourceId() : $resources_data["actual_object"]); ?>" />			
			<input type="HIDDEN" name="change_schedule_repeat_month_of_year" value="<? echo $resAssign->getRepeatMonthOfYear() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_day_of_month" value="<? echo $resAssign->getRepeatDayOfMonth() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_month" value="<? echo $resAssign->getRepeatMonth() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_week_of_month" value="<? echo $resAssign->getRepeatWeekOfMonth() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_day_of_week" value="<? echo $resAssign->getRepeatDayOfWeek() ?>" />
			<input type="HIDDEN" name="change_schedule_repeat_week" value="<? echo $resAssign->getRepeatWeek() ?>" />
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="center"><br />&nbsp; 				
				<?
				if (!$lockedAssign) {
				?>
					<input type="IMAGE" <?=makeButton("uebernehmen", "src") ?> border=0 name="submit" value="&Uuml;bernehmen">
				<?
				}
				if ($killButton) {
					?><input type="IMAGE" <?=makeButton("loeschen", "src") ?> border=0 name="kill_assign" value="l&ouml;schen"><?
				}
				if  (!$resAssign->getId()) 
					 print "<br /><img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1>"._("Sie erstellen eine neue Belegung")."</font>";
				if ($lockedAssign) {
					if ($resAssign->getOwnerType() == "sem") {
						$query = sprintf("SELECT Name, Seminar_id FROM seminare WHERE Seminar_id='%s' ",$resAssign->getAssignUserId());
						$this->db->query($query);
						$this->db->next_record();
					} elseif ($resAssign->getOwnerType() == "date") {
						$query = sprintf("SELECT Name, Seminar_id FROM termine LEFT JOIN Seminare ON (termine.range_id = Seminare.Seminar_id) WHERE termin_id='%s' ",$resAssign->getAssignUserId());									
						$this->db->query($query);
						$this->db->next_record();
					}
					print "<img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1>";
					if ($resAssign->getOwnerType() == "sem")
						printf (_("Diese Belegung ist ein regelm&auml;&szlig;iger Veranstaltungstermin, der in diesem Raum staffindet.")."<br />"._("Die Zeiten dieser Belegung k&ouml;nnen sie nur innerhalb der Veranstaltung %s bearbeiten!")."</font>", "<a href=\"seminar_main?auswahl=".$this->db->f("Seminar_id")."\">".htmlReady($this->db->f("Name"))."</a>");
					elseif ($resAssign->getOwnerType() == "date")
						printf (_("Diese Belegung ist ein Einzeltermin einer Veranstaltung, der in diesem Raum stattfindet.")."<br />"._(" Die Zeiten dieser Belegung k&ouml;nnen sie nur innerhalb der Veranstaltung %s bearbeiten!")."</font>", "<a href=\"seminar_main?auswahl=".$this->db->f("Seminar_id")."\">".htmlReady($this->db->f("Name"))."</a>");
					else
						printf (_("Sie haben nicht die Berechtigung, diese Belegung zu bearbeiten."));
				}
				?>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" valign="top"><font size=-1><?=_("Datum/erster Termin:")?></font><br />
				<font size=-1>
				<?
				if ($lockedAssign) {
					echo "<b>".date("d.m.Y",$resAssign->getBegin())."</b>";
				} else {
				?>
					<input name="change_schedule_day" value="<? echo date("d",$resAssign->getBegin()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_month" value="<? echo date("m",$resAssign->getBegin()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_year" value="<? echo date("Y",$resAssign->getBegin()); ?>" size=4 maxlength="4" />
				<?
				}
				?>
				</font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%"><font size=-1><?=_("Art der Wiederholung:")?></font><br />
				<font size=-1>
				<?
				if ($lockedAssign) {
					if ($resAssign->getRepeatMode()=="w")
						if ($resAssign->getRepeatInterval() == 2)
							echo "<b>"._("zweiw&ouml;chentlich")."</b>";
						else
							echo "<b>"._("w&ouml;chentlich")."</b>";
					else
						echo "<b>"._("keine Wiederholung (Einzeltermin)")."</b>";
				} else {
				?>				
					<input type="IMAGE" name="change_schedule_repeat_none" <?=makeButton("keine".(($resAssign->getRepeatMode()=="na") ? "2" :""), "src") ?> border=0 />
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_day" <?=makeButton("taeglich".(($resAssign->getRepeatMode()=="d") ? "2" :""), "src") ?> border=0 />
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_week" <?=makeButton("woechentlich".(($resAssign->getRepeatMode()=="w") ? "2" :""), "src") ?> border=0 /><br />
					<input type="IMAGE" name="change_schedule_repeat_month" <?=makeButton("monatlich".(($resAssign->getRepeatMode()=="m") ? "2" :""), "src") ?> border=0 />
					&nbsp;<input type="IMAGE" name="change_schedule_repeat_year" <?=makeButton("jaehrlich".(($resAssign->getRepeatMode()=="y") ? "2" :""), "src") ?> border=0 />
				<?
				}
				?>
				</font>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" valign="top"><font size=-1><?=_("Beginn/Ende:")?></font><br />
				<font size=-1>
				<?
				if ($lockedAssign) {
					echo "<b>".date("G:i",$resAssign->getBegin())." - ".date("G:i",$resAssign->getEnd())." </b>";
				} else {
				?>
					<input name="change_schedule_start_hour" value="<? echo date("G",$resAssign->getBegin()); ?>" size=2 maxlength="2" />
					:<input name="change_schedule_start_minute" value="<? echo date("i",$resAssign->getBegin()); ?>" size=2 maxlength="2" /><?=_("Uhr")?>
					&nbsp; &nbsp; <input name="change_schedule_end_hour"  value="<? echo date("G",$resAssign->getEnd()); ?>" size=2 maxlength="2" />
					:<input name="change_schedule_end_minute" value="<? echo date("i",$resAssign->getEnd()); ?>" size=2 maxlength="2" /><?=_("Uhr")?>
				<?
				}
				?>
				</font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%" valign="top">
				<? if ($resAssign->getRepeatMode() != "na") { ?>
				<font size=-1><?=_("Wiederholung bis sp&auml;testens:")?></font><br />				
				<font size=-1>
				<?
				if ($lockedAssign) {
					echo "<b>".date("d.m.Y",$resAssign->getRepeatEnd())."</b>";
				} else {
				?>
					<input name="change_schedule_repeat_end_day" value="<? echo date("d",$resAssign->getRepeatEnd()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_repeat_end_month" value="<? echo date("m",$resAssign->getRepeatEnd()); ?>" size=2 maxlength="2" />
					.<input name="change_schedule_repeat_end_year" value="<? echo date("Y",$resAssign->getRepeatEnd()); ?>" size=4 maxlength="4" />
					<? if ($resAssign->getRepeatMode() != "y") { ?>
						<input type="CHECKBOX" <? printf ("%s", ($resAssign->isRepeatEndSemEnd()) ? "checked" : "") ?> name="change_schedule_repeat_sem_end" /> <?=_("Ende der Vorlesungszeit")?>
					<? }
				}
				?>
				</font>
				<? 
				} else { 
				?> &nbsp;  
				<? } ?>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" valign="top"><font size=-1><?=_("eingetragen f&uuml;r die Belegung:")?></font><br />
				<font size=-1>
					<? 
					$user_name=$resAssign->getUsername(FALSE);
					if ($user_name)
						echo "<b>$user_name&nbsp;</b></font>";
					else
						echo "<b>-- "._("kein Stud.IP Nutzer eingetragen")." -- &nbsp;</b></font>";
					if (!$lockedAssign) {
						?><br /><br /><font size=-1><? 
						 if ($user_name) 
						 	print _("einen anderen User (Nutzer oder Einrichtung) eintragen:");
						 else
							print _("einen User (Nutzer oder Einrichtung) eintragen:");						 
						?><br /></font><font size=-1>
						<? showSearchForm("search_user", $search_string_search_user, FALSE, TRUE, FALSE, FALSE, FALSE) ?> <br/>
						<?=_("freie Eingabe zur Belegung:")?><br /></font>
						<input name="change_schedule_user_free_name" value="<? echo $resAssign->getUserFreeName(); ?>" size=40 maxlength="255" />
						<br /><font size=-1><?=_("<b>Beachten Sie:</b> Wenn sie einen Nutzer oder eine Einrichtung eintragen, kann dieser Nutzer oder berechtigte Personen die Belegung selbstst&auml;ndig aufheben. Sie k&ouml;nnen die die Belegung aber auch frei eingeben.")?></font>
						<input type ="HIDDEN" name="change_schedule_assign_user_id" value="<? echo $resAssign->getAssignUserId(); ?>" />
					<?
					}
					?>
				</font>
				</td>
				<td class="<? echo $this->getClass() ?>" valign="top">
				<? if (($resAssign->getRepeatMode() != "na") && ($resAssign->getOwnerType() != "sem") && ($resAssign->getOwnerType() != "date")) {?>
				<font size=-1><?=_("Wiederholungsturnus:")?></font><br />				
				<font size=-1>
					<select name="change_schedule_repeat_interval"> value="<? echo $resAssign->getRepeatInterval(); ?>" size=2 maxlength="2" />
					<?
					switch ($resAssign->getRepeatMode()) {
						case "d": 
							$str[1]= _("jeden Tag");
							$str[2]= _("jeden zweiten Tag");
							$str[3]= _("jeden dritten Tag");
							$str[4]= _("jeden vierten Tag");
							$str[5]= _("jeden f&uuml;nften Tag");
							$str[6]= _("jeden sechsten Tag");
							$max=6;
						break;
						case "w": 
							$str[1]= _("jede Woche");
							$str[2]= _("jede zweite Woche");
							$str[3]= _("jede dritte Woche");
							$max=3;
						break;
						case "m": 
							$str[1]= _("jeden Monat");
							$str[2]= _("jeden zweiten Monat");
							$str[3]= _("jeden dritten Monat");
							$str[4]= _("jeden vierten Monat");
							$str[5]= _("jeden f&uuml;nften Monat");
							$str[6]= _("jeden sechsten Monat");
							$str[7]= _("jeden siebten Monat");
							$str[8]= _("jeden achten Monat");
							$str[9]= _("jeden neunten Monat");
							$str[10]= _("jeden zehnten Monat");
							$str[11]= _("jeden elften Monat");
							$max=11;
						break;
						case "y": 
							$str[1]= _("jedes Jahr");
							$str[2]= _("jedes zweite Jahr");
							$str[3]= _("jedes dritte Jahr");
							$str[4]= _("jedes vierte Jahr");
							$str[5]= _("jedes f&uuml;nfte Jahr");
							$max=5;
						break;
					}
					for ($i=1; $i<=$max; $i++) {
						if ($resAssign->getRepeatInterval() == $i)
							printf ("<option value=\"%s\" selected>%s</option>", $i, $str[$i]);
						else
							printf ("<option value=\"%s\">%s</option>", $i, $str[$i]);						
					}
					?>
					</select><br />
					</font>
					<font size=-1><?=_("begrenzte Anzahl der Wiederholungen:")?></font><br />
					<font size=-1>
					max.&nbsp;<input name="change_schedule_repeat_quantity" value="<?  if ($resAssign->getRepeatQuantity() != -1) echo $resAssign->getRepeatQuantity(); ?>" size=2 maxlength="2" />&nbsp; <?=_("Mal wiederholen")?>
					<?
					if ($resAssign->getRepeatQuantity() == -1) 
						{ ?> <input type="HIDDEN" name="change_schedule_repeat_quantity_infinity" value="TRUE" /> <? }
					?>
					</font>
				</font>
				<? 
				} else { 
				?> &nbsp;  
				<? } ?>
				</td>
			</tr>
			<?
			if (!$lockedAssign) {
			?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="center"><br />&nbsp; 
					<input type="IMAGE" <?=makeButton("uebernehmen", "src") ?> border=0 name="submit" value="<?=_("&Uuml;bernehmen")?>">
				<?
				if ($killButton) {
					?><input type="IMAGE" <?=makeButton("loeschen", "src") ?> border=0 name="kill_assign" value="<?=_("l&ouml;schen")?>"><?
				}
				?>
				<br />&nbsp; 
				</td>
			</tr>
			<?
			}
			?>
			</form>
		</table>
		<?
	}	


	function showPropertiesForms() {
		global $PHP_SELF;
			
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_properties=<? echo $this->resObject->getId() ?>">
			<input type="HIDDEN" name="view" value="edit_object_properties" />
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>"><font size=-1><?=_("Name:")?></font><br />
				<font size=-1><input name="change_name" value="<? echo htmlReady($this->resObject->getName()) ?>" size=60 maxlength="255" />
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%"><font size=-1><?=_("Typ des Objekts:")?></font><br />
				<font size=-1>
					<?
					if (!checkAssigns($this->resObject->getId())) {
						?>
						<select name="change_category_id">
						<?
						$this->selectCategories();
						if (!$this->resObject->getCategoryId())
							echo "<option select value=\"\">"._("nicht zugeordnet")."</option>";
						while ($this->db->next_record()) {
							if ($this->db->f("category_id")==$this->resObject->getCategoryId()) {
								echo "<option selected value=\"".$this->db->f("category_id")."\">".htmlReady($this->db->f("name"))."</option>";
							} else
								echo "<option value=\"".$this->db->f("category_id")."\">".htmlReady($this->db->f("name"))."</option>";
						}
						?>
						</select><img src="./pictures/pfeiltransparent.gif" border=0><input type="IMAGE" name="assign" <?=makeButton("zuweisen", "src")?> value="<?=_("Zuweisen")?>" border=0>
					<?
					} else {
						print "<b>".htmlReady($this->resObject->getCategoryName())."</b>";
						printf ("<input type=\"HIDDEN\" name=\"change_category_id\" value=\"%s\" />", $this->resObject->getCategoryId());
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>"><font size=-1><?=_("Beschreibung:")?></font><br />
			
				<font size=-1><textarea name="change_description" rows=3 cols=60><? echo htmlReady($this->resObject->getDescription()) ?></textarea>
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%" valign="top"><font size=-1><?=_("Besitzer:")?></font><br />
				<font size=-1><a href="<? echo $this->resObject->getOwnerLink()?>"><? echo $this->resObject->getOwnerName(TRUE) ?></a></font>
				</td>
			</tr>
			<?
			/*
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>">&nbsp; 
				<td class="<? echo $this->getClass() ?>" width="40%" valign="top"><font size=-1><?=_("Vererbte Belegung:")?></font><br />
				<font size=-1><input type="CHECKBOX" name="change_parent_bind" <? if ($this->resObject->getParentBind()) echo "checked" ?> >
				<?=_("Objekt &uuml;bernimmt Belegung von &uuml;bergeordnetem Objekt")?></font>
				</td>
			</tr>
			*/
			?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2><font size=-1><b><?=_("Eigenschaften")?></b></font><br />
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
					<font size=-1><? echo htmlReady($this->db->f("name")); ?></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="40%">
				<font size=-1>
				<?
					$this->db2->query("SELECT * FROM resources_objects_properties WHERE resource_id = '".$this->resObject->getId()."' AND property_id = '".$this->db->f("property_id")."' ");
					$this->db2->next_record();
					printf ("<input type=\"HIDDEN\" name=\"change_property_val[]\" value=\"%s\" />", "_id_".$this->db->f("property_id"));
					switch ($this->db->f("type")) {
						case "bool":
							printf ("<input type=\"CHECKBOX\" name=\"change_property_val[]\" %s /><font size=-1>&nbsp;%s</font>", ($this->db2->f("state")) ? "checked":"", htmlReady($this->db->f("options")));
						break;
						case "num":
							printf ("<input type=\"TEXT\" name=\"change_property_val[]\" value=\"%s\" size=30 maxlength=255 />", htmlReady($this->db2->f("state")));
						break;
						case "text";
							printf ("<textarea name=\"change_property_val[]\" cols=30 rows=2 >%s</textarea>", htmlReady($this->db2->f("state")));
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
				<font size=-1 color="red"><?=_("Das Objekt wurde noch keinem Typ zugewiesen. Um Eigenschaften bearbeiten zu k&ouml;nnen, m&uuml;ssen Sie vorher einen Typ festlegen!")?></font>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 align="center"><br />&nbsp; <input type="IMAGE" align="absmiddle" <?=makeButton("uebernehmen", "src")?> border=0 name="submit" value="<?=_("Zuweisen")?>">
			 	<?
				if ($this->resObject->isUnchanged())
					print "&nbsp;<a href=\"$PHP_SELF?cancel_edit=".$this->resObject->id."\">".makeButton("abbrechen", "img")."</a>";
			 	?>
				<br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
		<br /><br />
		<?
	}	

	function showPermsForms() {
		global $PHP_SELF, $search_owner, $search_perm_user, $search_string_search_perm_user, $search_string_search_owner;
		
		$ObjectPerms = new ResourcesObjectPerms($this->resObject->getId());
		
		$owner_perms = checkObjektAdminstrablePerms ($this->resObject->getOwnerId());
		
		if ($owner_perms)
			$admin_perms = TRUE;
		else
			$admin_perms = ($ObjectPerms->getUserPerm () == "admin") ? TRUE : FALSE;
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?change_object_perms=<? echo $this->resObject->getId() ?>">
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2><font size=-1><?=_("Besitzer:")?></font><br />
				<font size=-1><a href="<? echo $this->resObject->getOwnerLink()?>"><? echo $this->resObject->getOwnerName(TRUE) ?></a></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="60%">
				<?
				if ($owner_perms){
					?>
					<font size=-1>Besitzer &auml;ndern:</font><font size=-1 color="red"></font><br />
					<? showSearchForm("search_owner", $search_string_search_owner, FALSE,TRUE);
				} else
					print "<img src=\"pictures/ausruf_small2.gif\" align=\"absmiddle\" />&nbsp;<font size=-1><font size=\"-1\"> "._("Sie k&ouml;nnen den Besitzer nicht &auml;ndern.")."</font>";
				?>
				</td>
			</tr>
			<tr>

				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=2 valign="top"><font size=-1><?=_("Berechtigungen:")?></font><br />
				<td class="<? echo $this->getClass() ?>" width="60%" valign="top"><font size=-1<?=_("Berechtigung hinzuf&uuml;gen")?></font><br />
				<? showSearchForm("search_perm_user", $search_string_search_perm_user, FALSE, FALSE, FALSE, TRUE) ?>
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
					<font size=-1><a href="<? echo $this->resObject->getOwnerLink($this->db->f("user_id"))?>"><? echo htmlReady($this->resObject->getOwnerName(TRUE, $this->db->f("user_id"))) ?></a></font>
				</td>
				<td class="<? echo $this->getClass() ?>" width="*">
					<font size=-1>&nbsp; 
					<?
					if (($admin_perms) && ((($this->db->f("perms") == "autor") || ($owner_perms))))
						printf ("<input type=\"RADIO\" name=\"change_user_perms[%s]\" value=\"autor\" %s />autor", $i, ($this->db->f("perms") == "autor") ? "checked" : "");
					else
						printf ("<input type=\"RADIO\" disabled name=\"FALSE\" %s /><font color=\"#888888\">autor</font>", ($this->db->f("perms") == "autor") ? "checked" : "");
					
					if (($this->resObject->getOwnerType($this->db->f("user_id")) == "user") && ($owner_perms))
						printf ("<input type=\"RADIO\" name=\"change_user_perms[%s]\" value=\"admin\" %s />admin", $i, ($this->db->f("perms") == "admin") ? "checked" : "");
					else
						printf ("<input type=\"RADIO\" disabled name=\"FALSE\" %s /><font color=\"#888888\">admin</font>", ($this->db->f("perms") == "admin") ? "checked" : "");

					if (($owner_perms) || (($admin_perms) && ($this->db->f("perms") == "autor")))
						printf ("&nbsp; <a href=\"%s?change_object_perms=%s&delete_user_perms=%s\"><img src=\"pictures/trash.gif\" ".tooltip(_("Berechtigung löschen"))." border=0></a>", $PHP_SELF, $this->resObject->getId(), $this->db->f("user_id"));
					else
						print "&nbsp; <img src=\"pictures/lighttrash.gif\" ".tooltip(_("Sie d&uuml;rfen diese Berechtigung leider nicht löschen"))." border=0>";
					?>
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
					<font size=-1><img src="pictures/ausruf_small2.gif" align="absmiddle" />&nbsp;<font size=-1><font size="-1"><?=_("Es sind keine weiteren Berechtigungen eingetragen")?></font>
				</td>
			</tr>
			<? } ?>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp; 
				</td>
				<td class="<? echo $this->getClass() ?>" colspan=3 align="center"><br />&nbsp; <input type="IMAGE" <?=makeButton("uebernehmen", "src")?> border=0 name="submit" value="<?=_("Zuweisen")?>"><br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
		<?
	}	
}

/*****************************************************************************
ViewSchedules - graphical schedule view
/*****************************************************************************/

class ViewSchedules extends cssClasses {
	var $ressource_id;		//viewed ressource object
	var $user_id;			//viewed user
	var $range_id;			//viewed range
	var $start_time;		//time to start
	var $end_time;			//time to end
	var $length_factor;		//the used length factor for calculations, only used for viewing
	var $length_unit;		//the used length unit for calculations, only used for viewing
	var $week_offset;		//offset for the week view
	var $used_view;		//the used view, submitted to the sub classes
		
	//Konstruktor
	function ViewSchedules($resource_id='', $user_id='', $range_id='') {
		$this->db=new DB_Seminar;
		$this->db2=new DB_Seminar;
		$this->resource_id=$resource_id;
		$this->user_id=$user_id;
		$this->range_id=$range_id;

	}
	
	function setLengthFactor ($value) {
		$this->length_factor = $value;
	}	
	
	function setLengthUnit ($value) {
		$this->length_unit = $value;
	}
	
	function setStartTime ($value) {
		$this->start_time = $value;
	}
	
	function setEndTime ($value) {
		$this->end_time = $value;
	}
	
	function setWeekOffset ($value) {
		$this->week_offset = $value;
	}
	
	function setUsedView($value) {
		$this->used_view = $value;
	}
	
	function navigator () {
	 	//match start_time & end_time for a whole week
	 	$dow = date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) >1)
	 		$offset = 1 - date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) <1)
		 	$offset = -6;
	 
 		$start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
 		$end_time = mktime (23, 59, 0, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));
	
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
		<form method="POST" action="<?echo $PHP_SELF ?>?navigate=TRUE&view=view_schedule">
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $this->getClass() ?>" width="96%" colspan="2"><font size=-1><b><?=_("Zeitraum:")?></b></font>
				</td>
			</tr>
			<tr>
				<td class="<? echo $this->getClass() ?>" width="4%" rowspan="2">&nbsp;
				</td>
				<td class="<? echo $this->getClass() ?>" width="30%" rowspan="2" valign="top"><font size=-1>
					<font size=-1>Beginn:&nbsp; 
					<input type="text" name="schedule_begin_day" size=2 maxlength=2 value="<? if (!$start_time) echo date("d",time()); else echo date("d",$start_time); ?>">.
					<input type="text" name="schedule_begin_month" size=2 maxlength=2 value="<? if (!$start_time) echo date("m",time()); else echo date("m",$start_time); ?>">.
					<input type="text" name="schedule_begin_year" size=4 maxlength=4 value="<? if (!$start_time) echo date("Y",time()); else echo date("Y",$start_time); ?>"><br /> 
					&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; <input type="IMAGE" name="jump" border="0"<? echo makeButton("auswaehlen", "src") ?> /><br />
				</td>
				<td class="<? echo $this->getClass() ?>" width="66%" valign="top"><font size=-1>
					<input type="text" name="schedule_length_factor" size=2 maxlength=2 / value="<? if (!$this->length_factor) echo "1"; else echo $this->length_factor; ?>">
					&nbsp; <select name="schedule_length_unit">
						<option <? if ($this->length_unit  == "d") echo "selected" ?> value="d"><?=_("Tag(e)")?></option>
						<option <? if ($this->length_unit  == "w") echo "selected" ?> value="w"><?=_("Woche(n)")?></option>
						<option <? if ($this->length_unit  == "m") echo "selected" ?> value="m"><?=_("Monat(e)")?></option>
						<option <? if ($this->length_unit  == "y") echo "selected" ?> value="y"><?=_("Jahre(e)")?></option>
					</select>&nbsp; als Liste
					&nbsp; <input type="IMAGE" name="start_list" <?=makeButton("ausgeben", "src") ?> border=0 vallue="<?=_("ausgeben")?>" /><br />
				</td>
			</tr>
			<tr>
					<td class="<? echo $this->getClass() ?>" width="66%" valign="top"><font size=-1>
					<i>oder</i>&nbsp;  eine Woche grafisch
					&nbsp; <input type="IMAGE" name="start_graphical" <?=makeButton("ausgeben", "src") ?> border=0 vallue="<?=_("ausgeben")?>" /><br />&nbsp; 
				</td>
			</tr>
			</form>
		</table>
	<?
	}

	function showScheduleList() {
		global $PHP_SELF;

		 //select view to jump from the schedule
		 if ($this->used_view == "openobject_schedule")
		 	$view = "openobject_assign";
		 else
			$view = "edit_object_assign";

		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $this->getClass() ?>" width="96%" align="center"><br />
					<? echo "<b>Anzeige vom ", date ("j.m.Y", $this->start_time), " bis ", date ("j.m.Y", $this->end_time)."</b><br />";?>
					<br />
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $this->getClass() ?>" width="96%">
					<?				
					$assign_events=new AssignEventList ($this->start_time, $this->end_time, $this->resource_id, '', '', TRUE);
					echo "<br /><font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font>";
					echo "<br /><br />";
					while ($event=$assign_events->nextEvent()) {
						echo "<a href=\"$PHP_SELF?view=".$view."&edit_assign_object=".$event->getAssignId()."\">".makeButton("details")."</a>";
						printf ("&nbsp; <font size=-1>"._("Belegung ist von <b>%s</b> bis <b>%s</b>, belegt von <b>%s</b>")."</font><br />", date("d.m.Y H:i", $event->getBegin()), date("d.m.Y H:i", $event->getEnd()), $event->getName());
					}
					?>
				</td>
			</tr>
		</table>
		<br /><br />
	<?
	}
	
	function showScheduleGraphical() {
		global $RELATIVE_PATH_RESOURCES, $PHP_SELF;
	 	
	 	require_once ($RELATIVE_PATH_RESOURCES."/lib/ScheduleWeek.class.php");
	 	
	 	//match start_time & end_time for a whole week
	 	$dow = date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) >1)
	 		$offset = 1 - date ("w", $this->start_time);
	 	if (date ("w", $this->start_time) <1)
		 	$offset = -6;

		 //select view to jump from the schedule
		 if ($this->used_view == "openobject_schedule")
		 	$view = "openobject_assign";
		 else
			$view = "edit_object_assign";
		 
 		$start_time = mktime (0, 0, 0, date("n",$this->start_time), date("j", $this->start_time)+$offset+($this->week_offset*7), date("Y", $this->start_time));
 		$end_time = mktime (23, 59, 0, date("n",$start_time), date("j", $start_time)+6, date("Y", $start_time));

	 	$schedule=new ScheduleWeek(FALSE, FALSE, FALSE, TRUE, $start_time) ;
		
		?>
		<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $this->getClass() ?>"  width="10%" align="left">&nbsp;
					<a href="<? echo $PHP_SELF ?>?view=<?=$this->used_view?>&previous_week=TRUE"><img src="pictures/forumrotlinks.gif" <? echo tooltip (_("Vorherige Woche anzeigen")) ?>border="0" /></a>
				</td>
				<td class="<? echo $this->getClass() ?>" width="76%" align="center"><br />
					<? echo "<b>Anzeige der Woche vom ", date ("j.m.Y", $start_time), " bis ", date ("j.m.Y", $end_time)."</b> (".strftime("%V", $start_time).". "._("Woche").")";?>
					<br /><br />
				</td>
				<td class="<? echo $this->getClass() ?>" width="10%" align="center">&nbsp;
					<a href="<? echo $PHP_SELF ?>?view=<?=$this->used_view?>&next_week=TRUE"><img src="pictures/forumrot.gif" <? echo tooltip (_("Nächste Woche anzeigen")) ?>border="0" /></a>
				</td>
			</tr>
			<tr>
				<td class="<? $this->switchClass(); echo $this->getClass() ?>" width="4%">&nbsp;
				</td>
				<td class="<? echo $this->getClass() ?>" width="96%" colspan="3">
					<?						
					$assign_events=new AssignEventList ($start_time, $end_time, $this->resource_id, '', '', TRUE);
					echo "<br /><font size=-1>"._("Anzahl der Belegungen in diesem Zeitraum:")." ", $assign_events->numberOfEvents()."</font>";
					echo "<br />&nbsp; ";
					while ($event=$assign_events->nextEvent()) {
						$schedule->addEvent($event->getName(), $event->getBegin(), $event->getEnd(), 
											"$PHP_SELF?view=$view&edit_assign_object=".$event->getAssignId());
					}
					$schedule->showSchedule("html");
					echo "<br />&nbsp; ";
					?>
				</td>
			</tr>
		</table>
	<?
	}
}

/*****************************************************************************
ResourcesBrowse, the search engine
/*****************************************************************************/

class ResourcesBrowse {
	var $start_object;		//where to start
	var $open_object;		//where we stay
	var $mode;			//the search mode
	var $searchArray;		//the array of search expressions (free search & properties)
	var $db;
	var $db2;
	var $cssSw;			//the cssClassSwitcher
	
	function ResourcesBrowse() {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
		$this->cssSw = new cssClassSwitcher;
		$this->list = new ShowList;
		
		$this->list->setRecurseLevels(0);
		$this->list->setViewHiearchyLevels(FALSE);
	}
	
	function setStartLevel($resource_id) {
		$this->start_object = $resource_id;
	}

	function setOpenLevel($resource_id) {
		$this->open_object = $resource_id;
	}

	function setMode($mode="browse") {
		$this->mode=$mode;
		if (!$this->mode)
			$this->mode="browse";
	}

	function setSearchArray($array) {
		$this->searchArray=$array;
	}
	
	//private
	function searchForm() {
		?>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> align="center">
				<font size=-1>freie Suche:&nbsp;
				<input name="search_exp"  type=textarea size=20 maxlength=255 value="<? echo $this->searchArray["search_exp"]; ?>" />
				<input type="IMAGE" <? echo makeButton ("suchestarten", "src") ?> name="start_search" border=0 value="<?=_("Suche starten")?>">
				&nbsp;<a href="<? echo $PHP_SELF?>?search=TRUE&reset=TRUE"><? echo makeButton ("neuesuche") ?></a>
				<?
				if ($this->mode == "browse")
					printf ("&nbsp;<a href=\"%s?view=search&mode=properties\">%s</a> "._("durchsuchen"), $PHP_SELF, makeButton ("eigenschaften"));
				else
					printf ("&nbsp;<a href=\"%s?view=search&mode=browse\">%s</a> "._("durchsuchen"), $PHP_SELF, makeButton ("ebenen"));				
				?>
			</td>
		</tr>
		<?
	}
	
	//private
	function getHistory($id) {
		global $PHP_SELF, $UNI_URL, $UNI_NAME;
		$top=FALSE;
		$k=0;
		while ((!$top) && ($id)) {
			$k++;
			$query = sprintf ("SELECT name, parent_id, resource_id FROM resources_objects WHERE resource_id = '%s' ", $id);
			$this->db2->query($query);
			$this->db2->next_record();

			$result_arr[] = array("id" => $this->db2->f("resource_id"), "name" => htmlReady($this->db2->f("name")));
			$id=$this->db2->f("parent_id");

			if ($this->db2->f("parent_id") == "0") {
				$top = TRUE;
			}
		}

		$result = printf (" <font size = -1>Ressourcen der<a href=\"%s?view=search&reset=TRUE\"> %s</a></font>", $PHP_SELF, $UNI_NAME);
		if (is_array($result_arr))
			for ($i = sizeof($result_arr)-1; $i>=0; $i--) {
				$result.= sprintf (" > <a href=\"%s?view=search&open_level=%s\"><font size = -1>%s</font></a>", $PHP_SELF, $result_arr[$i]["id"], htmlReady($result_arr[$i]["name"]));
			}
		return $result;
	}
	
	//private
	function showProperties() {
		global $PHP_SELF;

		?>	
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> >
				<font size="-1"><?=_("folgende Eigenschaften soll die Ressource besitzen (leer bedeutet egal):")?></font>
			<br />
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> >
				<table width="90%" cellpadding=5 cellspacing=0 border=0 align="center">			
					<?
					$query = sprintf("SELECT category_id, name FROM resources_categories ORDER BY name");
					$this->db->query($query);
					$k=0;
					while ($this->db->next_record()) {
						print "<tr>\n";
						print "<td colspan=\"2\"> \n";
						if ($k)
							print "<hr /><br />";
						printf ("<font size=-1><b>%s:</b></font>", htmlReady($this->db->f("name")));
						print "</td>\n";
						print "</tr> \n";
						print "<tr>\n";
						print "<td width=\"50%\" valign=\"top\">";
						$query = sprintf("SELECT resources_properties.property_id, name, type, options FROM resources_categories_properties LEFT JOIN resources_properties USING (property_id) WHERE category_id = '%s' ORDER BY name ", $this->db->f("category_id"));
						$this->db2->query($query);
						if ($this->db2->num_rows() % 2 == 1)
							$i=0;
						else
							$i=1;
						$switched = FALSE;
						while ($this->db2->next_record()) {
							if (($i > ($this->db2->num_rows() /2 )) && (!$switched)) {
								print "</td><td width=\"50%\" valign=\"top\">";
								$switched = TRUE;
							}
							print "<table width=\"100%\" border=\"0\"><tr>";
							printf ("<td width=\"50%%\">%s</td>", htmlReady($this->db2->f("name")));
							print "<td width=\"50%\">";
							printf ("<input type=\"HIDDEN\" name=\"search_property_val[]\" value=\"%s\" />", "_id_".$this->db2->f("property_id"));
							switch ($this->db2->f("type")) {
								case "bool":
									printf ("<input type=\"CHECKBOX\" name=\"search_property_val[]\" %s /><font size=-1>&nbsp;%s</font>", ($value) ? "checked":"", htmlReady($this->db2->f("options")));
								break;
								case "num":
									printf ("<input type=\"TEXT\" name=\"search_property_val[]\" value=\"%s\" size=20 maxlength=255 />", htmlReady($value));
								break;
								case "text";
									printf ("<textarea name=\"search_property_val[]\" cols=20 rows=2 >%s</textarea>", htmlReady($value));
								break;
								case "select";
									$options=explode (";",$this->db2->f("options"));
									print "<select name=\"search_property_val[]\">";
									print	"<option value=\"\">--</option>";
									foreach ($options as $a) {
										printf ("<option %s value=\"%s\">%s</option>", ($value == $a) ? "selected":"", $a, htmlReady($a));
									}
									printf ("</select>");
								break;
							}
							print "</td></tr></table>";
							$i++;
						}
						$k++;
					}
					?>
				</table>
			</td>
		</tr>
		<?	
	}
	
	//private
	function browseLevels() {
		global $PHP_SELF;
		
		if ($this->open_object) {
			$query = sprintf ("SELECT resource_id, name, description FROM resources_objects WHERE parent_id = '%s' ORDER BY name", $this->open_object);
			$query2 = sprintf ("SELECT parent_id FROM resources_objects WHERE resource_id = '%s' ", $this->open_object);
			
			$this->db2->query($query2);			
			$this->db2->next_record();
			if ($this->db2->f("parent_id") != "0")
				$way_back=$this->db2->f("parent_id");
		} else {
			$query = sprintf ("SELECT resource_id, name, description FROM resources_objects WHERE resource_id = root_id ORDER BY name");
			$way_back=-1;
		}

		$this->db->query($query);
		
		//check for sublevels in current level
		$sublevels = FALSE;
		while ($this->db->next_record()) {
			$query2 = sprintf ("SELECT resource_id, name, description FROM resources_objects WHERE parent_id = '%s' ORDER BY name", $this->db->f("resource_id"));
			$this->db2->query($query2);
			if ($this->db2->nf() >0)
				$sublevels = TRUE;
		}
		if ($sublevels)
			$this->db->seek(0);
		
		?>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?>>
				<?
				echo $this->getHistory($this->open_object)." > <font size=-1><b>".$this->db->num_rows()."</b> "._("Unterebenen vorhanden")."</font>";
				?>
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?> align="right">
				<table width="70%" cellpadding=5 cellspacing=0 border=0 align="center">
					<?
					if ((!$this->db->num_rows()) || (!$sublevels)) {
						echo " <font size=-1><br /><b>"._("Auf dieser Ebene existieren keine weiteren Unterebenen")."</b><br /></font>";
					} else {
						if ($this->db->num_rows() % 2 == 1)
							$i=0;
						else
							$i=1;
						print "<td width=\"60%\" valign=\"top\">";
						while ($this->db->next_record()) {
							if (($i > ($this->db->num_rows() /2 )) && (!$switched)) {
								print "</td><td width=\"40%\" valign=\"top\">";
								$switched = TRUE;
							}
							printf ("<a href=\"$PHP_SELF?view=search&open_level=%s\"><b>%s</b></a><br />", $this->db->f("resource_id"), htmlReady($this->db->f("name")));
							$i++;
						}
					}
					?>
				</table>
				<?
				if ($way_back>=0) {
					printf ("<a href = \"%s?view=search&%s\">", $PHP_SELF, (!$way_back) ? "reset=TRUE" : "open_level=$way_back"); 
					print ("<img src=\"./pictures/move_left.gif\" border=\"0\" />&nbsp; <font size=\"-1\">"._("eine Ebene zur&uuml;ck")."</font></a>");
			
				}
				?>
			</td>
		</tr>
		<tr>
			<td <? $this->cssSw->switchClass(); echo $this->cssSw->getFullClass() ?>>
				<font size=-1><?=_("Eintr&auml;ge auf dieser Ebene:")?></font>
			</td>
		</tr>		
		<? 
	}
	
	//private
	function showList() {
		$result_count=$this->list->showList($this->open_object);
		if (!$result_count) {
			?>
		<tr>
			<td <? echo $this->cssSw->getFullClass() ?>>
				<font size=-1><b><?=_("Es existieren keine Eintr&auml;ge auf dieser Ebene.")?></b></font>
			</td>
		</tr>		
			<?
		}
	}
	
	//private
	function showSearchList() {
		$result_count=$this->list->showSearchList($this->searchArray);
		if (!$result_count) {
			?>
		<tr>
			<td <? echo $this->cssSw->getFullClass() ?>>
				<font size=-1><b><?=_("Es wurden keine Eintr&auml;ge zu ihren Suchkriterien gefunden.")?></b></font>
			</td>
		</tr>		
			<?
		}
	}
	
	//private
	function showSearch() {
		?>
			<table border=0 celpadding=2 cellspacing=0 width="99%" align="center">
				<form method="POST" action="<?echo $PHP_SELF ?>?view=search">
				<?
				$this->searchForm();
				if (!$this->searchArray) {
					if ($this->mode == "browse")
						$this->browseLevels();
					if ($this->mode == "properties")
						$this->showProperties();
					if ($this->mode == "browse")
						$this->showList();
				} else {
					$this->showSearchList();
				}
				?>
				</form>
			</table>
			<br />
		<?
	}	
}
?>