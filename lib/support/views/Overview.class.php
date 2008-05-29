<?
# Lifter002: TODO
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
Overview, Forms der Ueberblickdarstellung (Vertraege)
/*****************************************************************************/
require_once ($RELATIVE_PATH_SUPPORT."/views/ShowTreeRow.class.php");
require_once ($RELATIVE_PATH_SUPPORT."/lib/ContractObject.class.php");

class Overview extends ShowTreeRow {
	var $db;
	var $db2;

	function Overview() {
		$this->db = new DB_Seminar;
		$this->db2 = new DB_Seminar;
	}

	function ShowOverview ($range_id) {
		$db = new DB_Seminar;
		$query = sprintf("SELECT contract_id FROM support_contract WHERE range_id = '%s' ORDER BY contract_begin ASC", $range_id);
		$db->query($query);
		while ($db->next_record()) {
			$this->showListObject($db->f("contract_id"));
		}
	}

	//private
	function showListObject ($contract_id) {
		global $supportdb_data, $edit_con_object, $RELATIVE_PATH_SUPPORT, $PHP_SELF, $supporter, $perm, $user;

		//Object erstellen
		$conObject=new ContractObject($contract_id);

		//Daten vorbereiten
		$icon="<img src=\"".$GLOBALS['ASSETS_URL']."images/cont_folder2.gif\" />";

		if ((!$supportdb_data["con_opens"]) && (!$supportdb_data["user_action_con"]) && ($conObject->isOldestActive()) && ($conObject->getRemainingPoints() > 0)) {
			$supportdb_data["con_opens"][$conObject->getId()] = TRUE;
			$supportdb_data["actual_con"] = $conObject->getId();
		}

		if ($supportdb_data["con_opens"][$conObject->getId()]) {
			$link=$PHP_SELF."?con_close=".$conObject->getId();
			$open="open";
			if ($supportdb_data["actual_con"] == $conObject->getId())
				echo "<a name=\"a\"></a>";
		} else {
			$link=$PHP_SELF."?con_open=".$conObject->getId()."#a";
			$open="close";
		}

		if (($edit_con_object == $conObject->id) && ($supporter)){
			echo "<a name=\"a\"></a>";
			$titel = _("Laufzeit: ");
			$titel .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"2\" maxlength=\"2\" name=\"con_begin_day\" value=\"".date("d", $conObject->getContractBegin())."\" />.";
			$titel .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"2\" maxlength=\"2\" name=\"con_begin_month\" value=\"".date("m", $conObject->getContractBegin())."\" />.";
			$titel .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"4\" maxlength=\"4\" name=\"con_begin_year\" value=\"".date("Y", $conObject->getContractBegin())."\" />";
			$titel .= "&nbsp;bis&nbsp;<input style=\"{font-size:8pt;}\" type=\"text\"size=2 maxlength=\"2\" name=\"con_end_day\" value=\"".date("d", $conObject->getContractEnd())."\" />.";
			$titel .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"2\" maxlength=\"2\" name=\"con_end_month\" value=\"".date("m", $conObject->getContractEnd())."\" />.";
			$titel .= "<input style=\"{font-size:8pt;}\" type=\"text\"size=\"4\" maxlength=\"4\" name=\"con_end_year\" value=\"".date("Y", $conObject->getContractEnd())."\" />";
		} else {
			$titel = sprintf(_("Supportvertrag vom %s bis %s"), date("d.m.Y", $conObject->getContractBegin()), date("d.m.Y", $conObject->getContractEnd()));
		}

		//create a link on the titel, too
		if (($link) && ($edit_con_object != $conObject->id))
			$titel = "<a href=\"$link\" class=\"tree\" >$titel</a>";

		//contract partner (an institute)
		if ($edit_con_object == $conObject->id) {
			$zusatz =  "<select name=\"con_institut_id\" style=\"{font-size:8 pt;};\">\n";
			if ($perm->have_perm("root"))
				$query = "SELECT Name,Institut_id,1 AS is_fak,'admin' AS inst_perms FROM Institute WHERE Institut_id=fakultaets_id ORDER BY Name";
			elseif ($perm->have_perm("admin"))
				$query = "SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user->id' AND inst_perms = 'admin') ORDER BY is_fak,Name";
			else
				$query = "SELECT Name,a.Institut_id,IF(a.Institut_id=fakultaets_id,1,0) AS is_fak,inst_perms FROM user_inst  a LEFT JOIN Institute USING (institut_id) WHERE (user_id = '$user->id' AND inst_perms = 'dozent') ORDER BY is_fak,Name";

			$this->db->query($query);

			while ($this->db->next_record()) {
				$zusatz .= sprintf ("<option style=\"{font-size:8pt;}\" %s style=\"%s\" value=\"%s\"> %s</option>\n", $this->db->f("Institut_id") == $conObject->getInstitutId() ? "selected" : "",
					($this->db->f("is_fak")) ? "font-weight:bold;" : "", $this->db->f("Institut_id"), htmlReady(my_substr($this->db->f("Name"),0,50)));
				if ($this->db->f("is_fak") && $this->db->f("inst_perms") == "admin") {
					$this->db2->query("SELECT a.Institut_id, a.Name FROM Institute a WHERE fakultaets_id='" . $this->db->f("Institut_id") . "' AND a.Institut_id!='" .$this->db->f("Institut_id") . "' ORDER BY Name");
					while($this->db2->next_record()) {
						$zusatz .= sprintf ("<option %s value=\"%s\">&nbsp;&nbsp;&nbsp;&nbsp;%s</option>\n", $this->db2->f("Institut_id") == $conObject->getInstitutId() ? "selected" : "",
							$this->db2->f("Institut_id"), htmlReady(my_substr($this->db2->f("Name"),0,50)));
					}
				}
			}

			$zusatz .= "</select>\n";
		} else {
			$query = sprintf ("SELECT Name FROM Institute WHERE Institut_id = '%s'", $conObject->getInstitutId());
			$this->db->query($query);
			$this->db->next_record();

			$zusatz = sprintf("<a href=\"institut_main.php?auswahl=%s\"><font color=\"#333399\">%s</font></a>", $conObject->getInstitutId(), htmlReady($this->db->f("Name")));
			$zusatz .= sprintf("&nbsp;(%s / %s)", $conObject->getRemainingPoints(), $conObject->getGivenPoints());
		}

		$new=TRUE;
		if ($open == "open") {
			$content = "<table border=\"0\" cellspacing=\"2\" cellpadding=\"0\" width=\"100%\">\n";
			$content .= sprintf ("<tr><td width=\"20%%\"><b><font size=\"-1\">"._("Punkte in diesem Vertrag:")."</font></b></td><td width=\"10%%\" align=\"center\" valign=\"top\"><font size=\"-1\">%s</font></td><td width=\"20%%\">&nbsp;</td>\n",
				(($edit_con_object == $conObject->id) && ($supporter)) ? "<input style=\"{font-size:8pt;}\" type=\"TEXT\" name=\"con_given_points\" size=\"4\" maxlength=\"4\" value=\"".$conObject->getGivenPoints()."\" />" : $conObject->getGivenPoints());
			$content .= sprintf ("<td width=\"20%%\"><b><font size=\"-1\">"._("Anfragen:")."</font></b></td><td width=\"10%%\" align=\"center\"><font size=\"-1\">%s</font></td><td width=\"20%%\">&nbsp;</td></tr>\n",  $conObject->getRequests());
			$content .= sprintf ("<tr><td><b><font size=\"-1\">"._("verbrauchte Punkte:")."</font></b></td><td align=\"center\"><font size=\"-1\">%s</font></td><td>&nbsp;</td>\n",  $conObject->getUsedPoints());
			$content .= sprintf ("<td width=\"20%%\"><b><font size=\"-1\">"._("Bearbeitungen:")."</font></b></td><td width=\"10%%\" align=\"center\"><font size=\"-1\">%s</font></td><td width=\"20%%\">&nbsp;</td></tr>\n",  $conObject->getEvents());
			$content .= "<tr><td colspan=\"2\" style=\"{background-image: url('".$GLOBALS['ASSETS_URL']."images/line.gif')};\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /><td><td><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width =\"10\" height=\"1\"  /></td></tr>\n";
			$content .= sprintf ("<tr><td><b><font size=\"-1\">"._("verbleibende Punkte:")."</font></b></td><td align=\"center\"><font size=\"-1\">%s</font></td><td colspan=\"3\">&nbsp;</td></tr>\n",  $conObject->getRemainingPoints());
			$content .= "</table>";
			$content .= sprintf ("<input type=\"HIDDEN\" name=\"sent_con_id\" value=\"%s\" />", $conObject->id);
		}
		if ($supporter) {
			if ($edit_con_object == $conObject->id) {
				$edit = "<br />&nbsp;<input align=\"absmiddle\" type=\"IMAGE\" ".makeButton("uebernehmen", "src")." />";
				$edit .= "&nbsp;<a href=\"$PHP_SELF?cancel_edit_con=$conObject->id\">".makeButton("abbrechen")."</a>&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			if ($conObject->isDeleteable()) {
				$edit .= "<a href=\"$PHP_SELF?kill_con=$conObject->id\">".makeButton("loeschen")."</a>";
			}
			if ($edit_con_object != $conObject->id)
				$edit .= "&nbsp;<a href=\"$PHP_SELF?edit_con=$conObject->id\">".makeButton("bearbeiten")."</a>";
			$edit.= "&nbsp;<a href=\"$PHP_SELF?create_req=$conObject->id&view=requests\">".makeButton("neueanfrage")."</a>";
		}

		$edit.= "&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$PHP_SELF?show_con_req=$conObject->id&view=requests\">".makeButton("anfragen")."</a>&nbsp;";


		//Daten an Ausgabemodul senden
		$this->showRow($icon, $link, $titel, $zusatz, 0, 0, 0, $new, $open, $content, $edit);
	}
}