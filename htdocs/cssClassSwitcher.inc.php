<?
/**
* cssClassSwitcher.inc.php
* 
* class for handling zebra-tables
*
* @author		Andre Noack <noack@data-quest.de>
* @version		$Id$
* @access		public
* @package		studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// html_head.inc.php
// Copyright (c) 2002 Andre Noack <noack@data-quest.de>
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

class cssClassSwitcher {
	var $class = array("steelgraulight", "steel1");                 //Klassen
	var $headerClass = "steel";
	var $classcnt = 0;                //Counter
	var $hovercolor = array("#B7C2E2","#CED8F2");
	var $nohovercolor = array("#E2E2E2","#F2F2F2");
	var $JSenabled = FALSE;
	var $hoverenabled = FALSE;
	
	function cssClassSwitcher($class = "",$headerClass = "",$hovercolor = "",$nohovercolor = ""){
		if ($GLOBALS["auth"]->auth["jscript"]) $this->JSenabled = TRUE;
		if (is_array($class)) $this->class = $class;
		if ($headerClass) $this->headerClass = $headerClass;
		if (is_array($hovercolor)) $this->hovercolor = $hovercolor;
		if (is_array($nohovercolor)) $this->nohovercolor = $nohovercolor;
	}
	
	function enableHover($hovercolor = "",$nohovercolor = ""){
		if (is_array($hovercolor)) $this->hovercolor = $hovercolor;
		if (is_array($nohovercolor)) $this->nohovercolor = $nohovercolor;	
		if ($this->JSenabled)
			$this->hoverenabled = TRUE;
	}
	
	function disableHover(){
		$this->hoverenabled = FALSE;
	}
	
	function getHover(){
		if($this->hoverenabled && $this->JSenabled){
			$ret = $this->getFullClass();
			$ret .= " onMouseOver='doHover(this,\"".$this->nohovercolor[$this->classcnt]."\",\"".$this->hovercolor[$this->classcnt]."\")'".
				" onMouseOut='doHover(this,\"".$this->hovercolor[$this->classcnt]."\",\"".$this->nohovercolor[$this->classcnt]."\")' ";
		}
		return $ret;
	}
	
	function getFullClass(){
		$ret = ($this->hoverenabled) ?  " style=\"background-color:".$this->nohovercolor[$this->classcnt]."\" " : " class=\"" . $this->class[$this->classcnt] . "\" ";
		return $ret;
	}
	
	function getClass() {
		return ($this->hoverenabled) ? "\"  style=\"background-color:".$this->nohovercolor[$this->classcnt]." " : $this->class[$this->classcnt];
	}

	function getHeaderClass() {
		return $this->headerClass;
	}

	function resetClass() {
		return $this->classcnt = 0;
	}

	function switchClass() {
		$this->classcnt++;
		if ($this->classcnt >= sizeof($this->class))
			$this->classcnt = 0;
	}
	
	function GetHoverJSFunction(){
		static $is_called = FALSE;
		$ret = "";
		if($GLOBALS["auth"]->auth["jscript"] && !$is_called) {
			$ret = "<script type=\"text/javascript\">
					function hexToRgb(hexcolor){
						var rgb = 'rgb(' + parseInt(hexcolor.substr(1,2),16) + ',' + parseInt(hexcolor.substr(3,2),16) + ','
									+ parseInt(hexcolor.substr(5,2),16) +')';
						return rgb;
					}
					function doHover(theRow, theFromColor, theToColor){
						if (theFromColor == '' || theToColor == '') {
							return false;
						}
						if (document.getElementsByTagName) {
							var theCells = theRow.getElementsByTagName('td');
						}
						else if (theRow.cells) {
							var theCells = theRow.cells;
						} else {
							return false;
						}
						hexToRgb(theToColor);
						if (theRow.tagName.toLowerCase() != 'tr'){
							if ((theRow.style.backgroundColor.toLowerCase() == theFromColor.toLowerCase()) || (theRow.style.backgroundColor == hexToRgb(theFromColor))) {
								theRow.style.backgroundColor = theToColor;
							}
						} else {
							var rowCellsCnt  = theCells.length;
							for (var c = 0; c < rowCellsCnt; c++) {
								if ((theCells[c].style.backgroundColor == theFromColor.toLowerCase()) || (theCells[c].style.backgroundColor == hexToRgb(theFromColor))) {
									theCells[c].style.backgroundColor = theToColor;
								}
							}
						}
						return true;
					}
					</script>";
		}
		$is_called = TRUE;
		return $ret;
	}
}
?>
