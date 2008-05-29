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
* @modulegroup		support
* @module		Msg.class.php
* @package		support
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
	var $codes;
	
	//Konstruktor
	function Msg() {
		global $RELATIVE_PATH_SUPPORT;
		
	 	include ($RELATIVE_PATH_SUPPORT."/views/msgs_support.inc.php");
	}
				
	function addMsg($msg_code) {
		$this->codes[]=$msg_code;
	}
	
	function checkMsgs() {
		if ($this->codes)
			return TRUE;
		else 
			return FALSE;
	}
	
	function displayAllMsg($view_mode = "line") {
		if (is_array($this->codes)) {
			foreach ($this->codes as $val)
				$collected_msg.=($this->msg[$val]["mode"]."§".$this->msg[$val]["msg"]."§");
			if ($view_mode == "window")
				parse_window($collected_msg, "§", $this->msg[$this->codes[0]]["titel"], "<a href=\"support.php?view=overview\">"._("zur&uuml;ck")."</a>");
			else
				parse_msg($collected_msg, "§", "blank", 1, FALSE);
		}
	}
	
	function displayMsg($msg_code, $view_mode = "line") {
		if ($view_mode == "window")
			parse_window($this->msg[$msg_code]["mode"]."§".$this->msg[$msg_code]["msg"], "§", $this->msg[$msg_code]["titel"], "<a href=\"support.php?view=overview\">"._("zur&uuml;ck")."</a>");
		else
			parse_msg($this->msg[$msg_code]["mode"]."§".$this->msg[$msg_code]["msg"], "§", "blank", 1, FALSE);
	}
}