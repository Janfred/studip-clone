<?
/*
reiter.php - 0.8.20020327
Klasse zum Erstellen des Reitersystems
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

class reiter {
	var $classActive = "links1b";					//Klasse fuer Zellen, die Aktiv (=im Vordergrund) sind
	var $classInactive="links1";					//Klasse fuer Zellen, die Inaktiv (=im Hintegrund) sind
	var $infoPic="pictures/info.gif";				//Bild das als Info Click/Alt-Text verwendet wird
	var $toActiveTopkatPic="pictures/reiter1.jpg";	//Trenner fuer Reiter
	var $toInactiveTopkatPic="pictures/reiter2.jpg";	//Trenner auf Inactive fuer Reiter
	var $closerTopkatPic="pictures/reiter4.jpg";		//Closer fuer Reiter
	var $activeBottomkatPic="pictures/forumrot.gif";			//Aktiver Pfeil
	var $inactiveBottomkatPic="pictures/forumgrau.gif";		//Inaktiver Pfeil
	var $bottomPic="pictures/reiter3.jpg";			//Unterer Abschluss
	
	
	function topkatStart() {
		printf ("<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>");
		return;
	}
	
	function info($alt,$js,$closeToActive=FALSE) {
		global $auth;
		
		printf ("<td class=\"%s\" nowrap>&nbsp; <img align=\"absmiddle\" src=\"%s\" ", $this->classActive, $this->infoPic);
		 //JavaScript Infofenster aufbauen
		if ($auth->auth["jscript"])
			printf ("onClick=\"alert('%s');\"\n",$js);
		printf ("alt=\"%s\" border=\"0\">&nbsp;", $alt);
		if ($closeToActive)
			printf ("&nbsp; <img src=\"%s\" align=absmiddle>", $this->toActiveTopkatPic);
		else
			printf ("&nbsp; <img src=\"%s\" align=absmiddle>", $this->toInactiveTopkatPic);
		printf ("</td>\n");
		return;
	}
	
	function topkat($text,$link,$active=FALSE, $target="", $close=FALSE) {
		if (($active) && (!$close))
			printf("<td class=\"%s\" align=\"right\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">&nbsp; &nbsp; %s&nbsp; &nbsp; </a><img src=\"%s\" align=absmiddle></td>\n",
				$this->classActive, $this->classActive, $target, $link, $text, $this->toInactiveTopkatPic);
		if ((!$active) && (!$close))
			printf("<td class=\"%s\" align=\"right\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">&nbsp; &nbsp; %s&nbsp; &nbsp; </a><img src=\"%s\" align=absmiddle></td>\n",
				$this->classInactive, $this->classInactive, $target, $link, $text, $this->toActiveTopkatPic);
		if (($active) && ($close))
			printf("<td class=\"%s\" align=\"right\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">&nbsp; &nbsp; %s&nbsp; &nbsp; </a><img src=\"%s\" align=absmiddle></td>\n",
				$this->classActive, $this->classActive, $target, $link, $text, $this->closerTopkatPic);
		if ((!$active) && ($close))
			printf("<td class=\"%s\" align=\"right\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">&nbsp; &nbsp; %s&nbsp; &nbsp; </a><img src=\"%s\" align=absmiddle></td>\n",
				$this->classInactive, $this->classInactive, $target, $link, $text, $this->closerTopkatPic);
		return;
	}

	function topkatCloseRow() {
		printf ("</tr></table>\n");
		return;
	}
	
	function bottomkatStart() {
		printf ("<table width=\"%s\" cellspacing=0 cellpadding=4 border=0>\n<tr><td class=\"%s\">&nbsp; &nbsp; ", "100%", $this->classActive);
		return;
	}

	function bottomkat($text,$link,$active=FALSE, $target="") {
		if ($active)
			printf("<img src=\"%s\" border=\"0\"><a class=\"%s\" target=\"%s\" href=\"%s\">%s&nbsp; &nbsp; </a>\n",
				$this->activeBottomkatPic, $this->classActive, $target, $link, $text);
		else
			printf("<img src=\"%s\" border=\"0\"><a class=\"%s\" target=\"%s\" href=\"%s\">%s&nbsp; &nbsp; </a>\n",
				$this->inactiveBottomkatPic, $this->classActive, $target, $link, $text);
	}

	function bottomkatCloseRow() {
		printf ("</td></tr><tr><td background=\"%s\">&nbsp;</td></tr></table>\n", $this->bottomPic);
		return;
	}
	
	function activateStructure ($structure, $view) {
		if (!$view) {
			reset ($structure);
			list($index)=each($structure);
			$view=$index;
		}
		$structure[$view]["active"]=TRUE;
		if ($structure[$view]["topKat"])
			$structure[$structure[$view]["topKat"]]["active"]=TRUE;
		else {
			reset ($structure);
			while (list($loch)=each($structure)) {
				if ($structure[$loch]["topKat"] == $view) {
					$structure[$loch]["active"]=TRUE;
					break;
					}
			}
		}
		return $structure;
	}

	function printStructure ($structure, $alt, $js) {
		reset($structure);
		foreach ($structure as $key=>$val) {
			if (!$val["topKat"]) {
				$topKats++;
				if ($val["active"])
					$tmp_topKat=$key;
			}
		}
		$bottomKats=sizeof($structure)-$topKats;
		reset($structure);
		$this->topkatStart();
		$a=current($structure);
		if ($alt)
			$this->info($alt, $js, $a["active"]);
		for ($i=0; $i<$topKats; $i++) {
			if ($i+1==$topKats)
				$close=TRUE;
			$this->topkat($a["name"], $a["link"], $a["active"], $a["target"], $close);
			$a=next($structure);
			}
		$this->topkatCloseRow();
		$this->bottomkatStart();
		for ($i=0; $i<=$bottomKats; $i++) {
			if ($a["topKat"]==$tmp_topKat) {
				$this->bottomkat($a["name"], $a["link"], $a["active"], $a["target"]);
				}
			$a=next($structure);				
			}
		$this->bottomkatCloseRow();
	}
	
	function create($structure, $view, $alt='', $js='') {
		$structure=$this->activateStructure ($structure, $view);
		$this->printStructure($structure, $alt, $js);
	}
}
?>