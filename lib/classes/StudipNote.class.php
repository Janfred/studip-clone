<?php
/**
* StudipNote.class.php
*
*
*
*
* @author	jens.schmelzer@fh-jena.de
* @version	$Id:  $
* @access	public
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
//
// Copyright (C) 2005 André Noack <noack@data-quest>,
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

require_once 'lib/visual.inc.php';


class StudipNote {
	var $db;
	var $title = 'Ajax-Notepad';
	var $ok = false;
	var $content = '';
	var $kid;
	
	function StudipNote(){
		if (!$this->check()) return;
		$this->db = new DB_Seminar;			
		$this->db->query('SELECT * FROM kategorien WHERE range_id = \''.mysql_real_escape_string($GLOBALS['user']->id).'\' AND name LIKE \''. mysql_real_escape_string($this->title). '\' ORDER BY priority LIMIT 1;');
		if ($this->db->next_Record()){
			$name = $this->db->f('name');
			$this->content = $this->db->f('content');
			$this->kid = $this->db->f('kategorie_id');
		} else {
			$name = $this->title;
			$this->content = _("Keine Notizen vorhanden :-)");
			$this->kid = md5(uniqid("blablubburegds4"));
			$this->db->query("UPDATE kategorien SET priority=priority+1 WHERE range_id='".mysql_real_escape_string($GLOBALS['user']->id)."'");
			$this->db->query('INSERT INTO `kategorien` (`kategorie_id`, `range_id`, `name`, `content`, `hidden`, `mkdate`, `chdate`, `priority`) VALUES (\''.mysql_real_escape_string($this->kid) .'\', \''.mysql_real_escape_string($GLOBALS['user']->id). '\', \''.mysql_real_escape_string($name) .'\', \''.mysql_real_escape_string($this->content) .'\', 1, \''.time().'\', \''.time().'\', 0);');
		}

	}
	
	function check(){
		if (!$GLOBALS['auth']->auth['jscript']) return false;
		if (!$GLOBALS['perm']->have_perm('autor')) return false;
		$this->ok = true;
		return true;
	}
	
	function updateNote(){
		if (!$this->ok) return;
		if (array_key_exists('value', $_POST)) {
			$value = utf8_decode($_POST['value']);
			if (get_magic_quotes_gpc()) $value = stripslashes($value); 
			if ($this->content != $value) {
				$this->db->query('UPDATE kategorien SET content=\''.mysql_real_escape_string($value).'\', chdate=\''.time().'\' WHERE kategorie_id=\''.mysql_real_escape_string($this->kid).'\';');
			}
			echo formatReady($value);
			return;
		}
		echo 'Fehler beim speichern!';
	}
	
	function getNoteForForm(){
		if (!$this->ok) return;
		echo utf8_encode($this->content);
	}
	
	function getNote(){
		if (!$this->ok) return;
		echo formatReady($this->content);
	}
	
	function writeForm($infotext){
	$noitzen = _("Notizen");
	$ausblenden = _("ausblenden");
	$abbrechen = ' ['._("abbrechen").']';
	$klicken = _("Zum Bearbeiten hier klicken!");
	$speichern = _("speichern");
	$verschieben = _("verschieben");
	$out = <<<ENDHTML
	<div id="studipNote" style="position:absolute;top:50px;right:8px;width:256px;z-index:99;display:none;opacity:0.9;"><div style="background-color:#dde;border:2px ridge darkred;padding:10px;text-align:left;"><span style="font-size:x-small;">$infotext
	</span><hr><span style="font-size:smaller;"><b>$noitzen:</b>
	<div id="studipNote_txt" style="background-color:#dde;">&nbsp;</div>
	<hr>
	<span onclick="Effect.Fade('studipNote');" style="cursor:pointer;">[$ausblenden]</span>&nbsp;
	<span id="studipNote_move" style="cursor:move;">[$verschieben]</span>
	</span>
	</div></div>
	<script type="text/javascript">
	// <![CDATA[
	new Ajax.InPlaceEditor(\$('studipNote_txt'), 'ajaxserver.php?ajax_cmd=studipNote&ajax_cmd2=update', { rows:8, cols:25, okText:'$speichern', cancelText:'$abbrechen', clickToEditText: '$klicken', highlightendcolor:'#ddddee', loadTextURL: 'ajaxserver.php?ajax_cmd=studipNote&ajax_cmd2=form'});
	new Draggable('studipNote',{gohsting: true, scroll:window, handle:'studipNote_move'});
	// ]]>
	</script>
ENDHTML;
	echo $out;
	}
	
}



?>