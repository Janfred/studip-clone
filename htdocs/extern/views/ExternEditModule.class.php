<?
/**
* ExternEditModule.class.php
* 
* basic functions for the extern interfaces
* 
*
* @author		Peter Thienel <pthienel@web.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version	$Id$
* @access		public
* @modulegroup	extern
* @module		ExternEditModule
* @package	studip_extern
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// ExternEditModule.class.php
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


require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"].$GLOBALS["RELATIVE_PATH_EXTERN"]."/views/ExternEditHtml.class.php");

class ExternEditModule extends ExternEditHtml {
	
	function ExternEditModule (&$config, $form_values = "", $faulty_values = "",
			 $edit_element = "") {
		ExternEdit::ExternEdit(&$config, $form_values, $faulty_values, $edit_element);
	}
	
	function editMainSettings ($field_names, $no_sort = "") {
		$order = $this->getValue("order");
		$aliases = $this->getValue("aliases");
		$visible = $this->getValue("visible");
		$widths = $this->getValue("width");
		$sort = $this->getValue("sort");
		if (!is_array($no_sort))
			$no_sort = array();
		
		$this->css->resetClass();
		$this->css->switchClass();
		
		$out = "<tr><td><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
		$out .= "<tr" . $this->css->getFullClass() . ">\n";
		$out .= "<td><font size=\"2\"><b>" . _("Spaltenname") . "</b></font></td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("&Uuml;berschrift") . "</b></font>$error_sign</td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("Breite") . "</b></font></td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("Sortierung") . "</b></font></td>\n";
		$out .= "<td><font size=\"2\"><b>" . _("Reihenfolge/<br>Sichtbarkeit") . "</b></font></td>\n";
		$out .= "</tr>\n";
		$this->css->switchClass();
		
		for ($i = 0; $i < sizeof($field_names); $i++) {
			
			// name of column
			$out .= "<tr" . $this->css->getFullClass() . ">\n";
			$out .= "<td><font size=\"2\">&nbsp;{$field_names[$order[$i]]}</font></td>";
			
			// column headline
			$out .= "<td><input type=\"text\" name=\"{$this->element_name}_aliases[$order[$i]]\"";
			$out .= "\" size=\"12\" maxlength=\"50\" value=\"";
			$out .= $aliases[$order[$i]] . "\"></td>\n";
			
			// width
			$width = str_replace("%", "", $widths[$order[$i]]);
			$out .= "<td><input type=\"text\" name=\"{$this->element_name}_width[$order[$i]]";
			$out .= "\" size=\"3\" maxlength=\"3\" value=\"$width\">\n</td>\n";
			
			// sort
			if (!in_array($order[$i], $no_sort)) {
				$out .= "<td><select name=\"{$this->element_name}_sort[$order[$i]]\" ";
				$out .= "size=\"1\">\n";
				for ($j = 0; $j <= (sizeof($order) - sizeof($no_sort)); $j++) {
					if ($sort[$order[$i]] == $j)
						$selected = " selected";
					else
						$selected = "";
					
					if ($j == 0)
						$out .= "<option value=\"0\"$selected>keine</option>";
					else
						$out .= "<option value=\"$j\"$selected>$j</option>";
				}
				$out .= "\n</select>\n</td>\n";
			}
			else
				$out .= "<td>&nbsp;</td>\n";
			
			// move left
			$out .= "<td valign=\"top\" nowrap=\"nowrap\">";
			$out .= "<input type=\"image\" name=\"move_left[$i]\" ";
			$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/move_up.gif\"";
			$out .= tooltip(_("nach links verschieben"));
			$out .= "border=\"0\" align=\"bottom\">\n";
			
			// move right
			$out .= "<input type=\"image\" name=\"move_right[$i]\" ";
			$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/move_down.gif\"";
			$out .= tooltip(_("nach rechts verschieben"));
			$out .= "border=\"0\" align=\"bottom\">\n&nbsp;";
			
			// visible
			if ($visible[$order[$i]]) {
				$out .= "<input type=\"image\" name=\"hide[{$order[$i]}]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/on_small.gif\"";
				$out .= tooltip(_("Spalte ausblenden"));
				$out .= "border=\"0\" align=\"middle\">\n";
			}
			else {
				$out .= "<input type=\"image\" name=\"show[{$order[$i]}]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/off_small.gif\"";
				$out .= tooltip(_("Spalte einblenden"));
				$out .= "border=\"0\" align=\"middle\">\n</td>\n";
			}
			
			$out .= "</tr>\n";
			$this->css->switchClass();
		}
		
		// width in pixels or percent
		$title = _("Breite in:");
		$info = _("Wählen Sie hier, ob die Breiten der Tabellenspalten als Prozentwerte oder Pixel interpretiert werden sollen.");
		$width_values = array("%", "");
		$width_names = array(_("Prozent"), _("Pixel"));
		$out .= "<tr" . $this->css->getFullClass() . ">\n";
		$out .= "<td><font size=\"2\">&nbsp;$title</font></td>";
		$out .= "<td colspan=\"4\"><input type=\"radio\" name=\"Main_widthpp\" value=\"%\"";
		if (substr($widths[0], -1) == "%")
			$out .= " checked=\"checked\"";
		$out .= " /><font size=\"2\">" . _("Prozent") . "&nbsp; &nbsp;</font><input type=\"radio\" name=\"";
		$out .= "Main_widthpp\" value=\"\"";
		if (substr($widths[0], -1) != "%")
			$out .= " checked=\"checked\"";
		$out .= " /><font size=\"2\">" . _("Pixel") . "&nbsp; &nbsp;</font>\n";
		$out .= "<img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/info.gif\"";
		$out .= tooltip($info, TRUE, TRUE) . ">$error_sign</td></tr>\n";
		$this->css->switchClass();
		
		$out .= "</table>\n</td></tr>\n";
		
		return $out;
	}
	
	function editName ($attribute) {
		$info = _("Geben Sie den Namen der Konfiguration an.");
		
		return $this->editTextfieldGeneric($attribute, "", $info, 40, 40);
	}
	
	function editGroups () {
		$groups_db = get_statusgruppen_by_name($this->config->range_id, "''", TRUE);
		
		if (!$groups_db)
			return FALSE;
		
		$title = _("Gruppen auswählen:");
		$info = _("Wählen sie die Statusgruppen aus, die ausgegeben werden sollen.");
		$groups_config = $this->getValue("groups");
		
		// initialize groups if this value isn't set in the config file
		if (!$groups_config)
			$groups_config = array_keys($groups_db);
			
		$groups_aliases = $this->getValue("groupsalias");
		$groups_visible = $this->getValue("groupsvisible");
		if (!$groups_visible)
			$groups_visible = array();
		
		for ($i = 0; $i < sizeof($groups_config); $i++)
			$groups[$groups_config[$i]] = $groups_aliases[$i];

		$this->css->resetClass();
		$this->css->switchClass();
		$out = "<tr><td><table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\">\n";
		$out .= "<tr" . $this->css->getFullClass() . ">\n";
		$out .= "<td width=\"42%\"><font size=\"2\"><b>" . _("Gruppenname") . "</b></font></td>\n";
		$out .= "<td width=\"48%\"><font size=\"2\"><b>" . _("alternativer Gruppenname") . "</b></font>$error_sign</td>\n";
		$out .= "<td width=\"1%\"><font size=\"2\"><b>" . _("Sichtbarkeit") . "</b></font></td>\n";
		$out .= "<td width=\"9%\"><font size=\"2\">&nbsp;</font></td>\n";
		$out .= "</tr>\n";
		$this->css->switchClass();
		
		reset($groups_db);
		foreach ($groups_db as $id => $name) {
		
			// name of group
			if (strlen($name) > 30)
				$name = substr($name, 0, 14) . "[...]" . substr($name, -10);
			$out .= "<tr" . $this->css->getFullClass() . ">\n";
			$out .= "<td nowrap=\"nowrap\"><font size=\"2\">&nbsp;" . htmlReady($name) . "</font></td>";
			
			// column headline
			$out .= "<td nowrap=\"nowrap\"><input type=\"text\" name=\"Main_groupsalias[]\"";
			$out .= "\" size=\"25\" maxlength=\"150\" value=\"";
			$out .= $groups[$id] . "\"></td>\n";
			
			// visible
			if (in_array($id, $groups_visible)) {
				$out .= "<td align=\"center\"><input type=\"image\" name=\"hide_group[$id]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/on_small.gif\"";
				$out .= tooltip(_("Spalte ausblenden"));
				$out .= "border=\"0\" align=\"middle\">\n</td>\n";
			}
			else {
				$out .= "<td align=\"center\"><input type=\"image\" name=\"show_group[$id]\" ";
				$out .= "img src=\"" . $GLOBALS["CANONICAL_RELATIVE_PATH_STUDIP"] . "pictures/off_small.gif\"";
				$out .= tooltip(_("Spalte einblenden"));
				$out .= "border=\"0\" align=\"middle\">\n</td>\n";
			}
			$out .= "<td>&nbsp;</td></tr>\n";
			$this->css->switchClass();
		}
		reset($groups_db);
		foreach ($groups_db as $id => $name)
			$out .= "<input type=\"hidden\" name=\"Main_groups[]\" value=\"$id\">\n";
			
		$out .= "</table>\n</td></tr>\n";
		
		return $out;
	}
	
}
	
?>
	
