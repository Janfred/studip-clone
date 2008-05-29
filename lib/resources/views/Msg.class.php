<?
# Lifter002: TODO
/**
* Msg.class.php
* 
* creates messages
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup		resources
* @module		Msg.class.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Msg.class.php
// erzeugt Fehlermeldungen und andere Ausgaben
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
Msg, class for all the msg stuff
/*****************************************************************************/

class Msg {
	var $msg;
	var $codes=array();
	var $params;
	
	//Konstruktor
	function Msg() {
		global $RELATIVE_PATH_RESOURCES;

	 	include ($RELATIVE_PATH_RESOURCES."/views/msgs_resources.inc.php");
	}
				
	function addMsg($msg_code, $params='') {
		$this->codes[]=$msg_code;
		if (is_array($params)) {
			$this->params[] = $params;
		} else
			$this->params[] = array();
			
	}
	
	function checkMsgs() {
		if ($this->codes)
			return TRUE;
		else 
			return FALSE;
	}
	
	function displayAllMsg($view_mode = "line") {
		if (is_array($this->codes)) {
			foreach ($this->codes as $key=>$val)
				$collected_msg.=($this->msg[$val]["mode"]."§".vsprintf($this->msg[$val]["msg"],$this->params[$key])."§");
			if ($view_mode == "window")
				parse_window($collected_msg, "§", $this->msg[$this->codes[0]]["titel"], "<a href=\"resources.php?view=resources\">"._("zur&uuml;ck")."</a>");
			else
				parse_msg($collected_msg, "§", "blank", 1, FALSE);
		}
	}
	
	function displayMsg($msg_code, $view_mode = "line", $params=array()) {
		if ($view_mode == "window")
			parse_window($this->msg[$msg_code]["mode"]."§".vsprintf($this->msg[$msg_code]["msg"], $params), "§", $this->msg[$msg_code]["titel"], "<a href=\"resources.php?view=resources\">"._("zur&uuml;ck")."</a>");
		else
			parse_msg($this->msg[$msg_code]["mode"]."§".vsprintf($this->msg[$msg_code]["msg"], $params), "§", "blank", 1, FALSE);
	}
}