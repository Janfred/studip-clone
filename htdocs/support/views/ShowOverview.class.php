<?
/**
* EditResourceData.class.php
* 
* shows the forms to edit the object
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		EditResourceData.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// EditResourceData.class.php
// stellt die forms zur Bearbeitung eines Ressourcen-Objekts zur Verfuegung
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
require_once ($RELATIVE_PATH_SUPPORT."/views/ShowTreeRow.class.php");
require_once ($RELATIVE_PATH_SUPPORT."/lib/ContractObject.class.php");

class ShowOverview extends ShowTreeList
	var $db;
	var $db2;

	function ShowOverview ($range_id) {
		$query ("SELECT contract_id FROM support_contract WHERE range_id = '%s'", $range_id);
		$this->db->query($query);
		
		while ($this->db->next_record()) {
			$this->showListObject($this->db->f("contract_id"))
		}
	}

	//private
	function showListObject ($contract_id) {
		global $supportdb_data, $edit_con_object, $RELATIVE_PATH_SUPPORT, $PHP_SELF;
	
		//Object erstellen
		$conObject=new ContractObject($contract_id);

		//Daten vorbereiten
		$icon="<img src=\"pictures/cont_folder2.gif\" />";

		if ($supportdb_data["con_opens"][$conObject->getId]) {			
			$link=$PHP_SELF."?con_close=".$conObject->getId()."#a";
			$open="open";
			if ($supportdb_data["actual_object"] == $conObject->getId())
				echo "<a name=\"a\"></a>";
		} else {
			$link=$PHP_SELF."?con_open=".$conObject->getId()."#a";
			$open="close";
		}

		if ($edit_con_object == $conObject->id) {
			echo "<a name=\"a\"></a>";
			$titel.="<input style=\"{font-size:8 pt; width: 100%;}\" type=\"text\" size=20 maxlength=255 name=\"change_name\" value=\"".htmlReady($resObject->getName())."\" />";
		} else {
			$titel=sprintf(_("Supportvertrag vom %s bis %s"), date("d.m.Y", $conObject->getContractBegin()), date("d.m.Y", $conObject->getContractEnd()));
		}

		//create a link on the titel, too
		if (($link) && ($edit_con_object != $conObject->id))
			$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";
		
		//contract partner (an institute)
		$query = sprintf ("SELECT name FROM Institute WHERE Institut_id = '%s'", $conObject->getInstitutId());
		$this->db->query($query);
		$this->db->next_record();
				
		$zusatz="<a href=\institut_main.php?auswahl=%s\"><font color=\"#333399\">%s</font></a>", $conObject->getInstitutId(), $this->db->f("Name"));

		$new=TRUE;
		if ($open == "open") {
			if (($edit_con_object == $conObject->id) && ($rechte)) {
				$content.= "<input type=\"image\" name=\"send\" align=\"absmiddle\" ".makeButton("uebernehmen", "src")." border=0 value=\""._("&Auml;nderungen speichern")."\" />";
				$content.= "&nbsp;<a href=\"$PHP_SELF?cancel_edit=$conObject->id\">".makeButton("abbrechen", "img")."</a>";						
				$content.= "<input type=\"hidden\" name=\"change_con_object\" value=\"".$conObject->getId()."\" />";
				$open="open";
			} else {
				//hier Details
			}
			if ($rechte) {
				if ($conObject->isDeletable()) {
					$edit= "<a href=\"$PHP_SELF?kill_object=$conObject->id\">".makeButton("loeschen")."</a>";
				} 
				$edit.= "&nbsp;<a href=\"$PHP_SELF?create_object=$conObject->id\">".makeButton("neuesobjekt")."</a>";
				$edit.= "&nbsp;&nbsp;&nbsp;&nbsp;";
			} 
			
			$edit.= "<a href=\"$PHP_SELF?show_requests=$conObject->id&view=requests\">".makeButton("requests")."</a>&nbsp;";
		}

		//Daten an Ausgabemodul senden
		$this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
	}
}