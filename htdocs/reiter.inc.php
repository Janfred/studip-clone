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

require_once ("$ABSOLUTE_PATH_STUDIP/visual.inc.php");

class reiter {
	//Classes
	var $classActive = "links1b";				//Klasse fuer Zellen, die Aktiv (=im Vordergrund) sind
	var $classInactive="links1";				//Klasse fuer Zellen, die Inaktiv (=im Hintegrund) sind
	//Pics
	var $infoPic="pictures/info.gif";			//Bild das als Info Click/Alt-Text verwendet wird
	var $toActiveTopkatPic="pictures/reiter1.jpg";		//Trenner fuer Reiter
	var $toInactiveTopkatPic="pictures/reiter2.jpg";	//Trenner auf Inactive fuer Reiter
	var $closerTopkatPic="pictures/reiter4.jpg";		//Closer fuer Reiter
	var $closerInfo="";					//generic Closer for Info
	var $activeBottomkatPic="pictures/forumrot.gif";	//Aktiver Pfeil
	var $inactiveBottomkatPic="pictures/forumgrau.gif";	//Inaktiver Pfeil
	var $bottomPic="pictures/reiter3.jpg";			//Unterer Abschluss
	//Width's
	var $infoWidth="";					//Width of the Infoarea
	var $tableWidth="";					//Width of the whole table
	//Settings
	var $noAktiveBottomkat=FALSE;				//Wenn trotz bestimmtem View kein Unterktagorie markiert sein soll (wird durch "(view)" erreicht) 
	var $spacerInfoTopkat=FALSE;				//Should a spacer be used between Info and Topkat?
	var $textAdd="&nbsp; &nbsp; ";				//a addition, which will be added before and after every text
	var $katsAlign="left";					//how to align every kat
	var $topkatBreakLineLimit="auto";			//more than x topkats = arrange topkats in two lines, or auto for automatic arrangement
	
	function topkatStart() {
		printf ("<table %s cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>", ($this->tableWidth) ? "width=\"$this->tableWidth\"" : "");
		return;
	}
	
	function info($tooltip,$addText,$closeToActive=FALSE) {
		printf ("<td class=\"%s\" %s nowrap>&nbsp; ", $this->classActive, ($this->infoWidth) ? "width=\"$this->infoWidth\"" : "");
		if ($tooltip)
			printf ("<img align=\"absmiddle\" src=\"%s\" %s border=\"0\">&nbsp;", $this->infoPic, tooltip($tooltip, TRUE, TRUE));
		if ($addText)
			printf ("<font class=\"%s\">%s</font>", $this->classActive, $addText);
		if (($closeToActive) && ($this->toActiveTopkatPic))
			printf ("&nbsp; <img src=\"%s\" align=absmiddle>", $this->toActiveTopkatPic);
		elseif ($this->toInactiveTopkatPic)
			printf ("&nbsp; <img src=\"%s\" align=absmiddle>", $this->toInactiveTopkatPic);
		printf ("</td>\n");
		if ($this->spacerInfoTopkat)
			print ("<td> &nbsp; </td>");
		if ($this->closerInfo)
			printf ("<td><img src=\"%s\" align=absmiddle></td>", $this->closerInfo);
		return;
	}
	
	function topkat($text,$link,$width,$active=FALSE, $target="", $close=FALSE) {
		if (($active) && (!$close))
			printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
				$this->classActive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classActive, $target, $link, $this->textAdd, $text, $this->textAdd,  
				($this->toInactiveTopkatPic) ? "<img src=\"$this->toInactiveTopkatPic\" align=absmiddle>" : "");
		if ((!$active) && (!$close))
			printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
				$this->classInactive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classInactive, $target, $link, $this->textAdd, $text, $this->textAdd, 
				($this->toActiveTopkatPic) ? "<img src=\"$this->toActiveTopkatPic\" align=absmiddle>" : "");
		if (($active) && ($close))
			printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
				$this->classActive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classActive, $target, $link, $this->textAdd, $text, $this->textAdd, 
				($this->closerTopkatPic) ? "<img src=\"$this->closerTopkatPic\" align=absmiddle>" : "");
		if ((!$active) && ($close))
			printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
				$this->classInactive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classInactive, $target, $link, $this->textAdd, $text, $this->textAdd, 
				($this->closerTopkatPic) ? "<img src=\"$this->closerTopkatPic\" align=absmiddle>" : "");
		return;
	}

	function topkatCloseRow() {
		printf ("</tr></table>\n");
		return;
	}
	
	function bottomkatStart() {
		printf ("<table width=\"100%%\" cellspacing=0 cellpadding=4 border=0>\n<tr><td class=\"%s\" width=\"2%%\">&nbsp; </td><td class=\"%s\">",  $this->classActive, $this->classActive);
		return;
	}

	function bottomkat($text,$link,$active=FALSE, $target="") {
		if (($active) && (!$this->noAktiveBottomkat))
			printf("<span style=\"white-space:nowrap;\"><img src=\"%s\" border=\"0\"><a class=\"%s\" target=\"%s\" href=\"%s\">%s</a><img src=\"pictures/blank.gif\" width=\"15\"></span>\n",
				$this->activeBottomkatPic, $this->classActive, $target, $link, $text);
		else
			printf("<span style=\"white-space:nowrap;\"><img src=\"%s\" border=\"0\"><a class=\"%s\" target=\"%s\" href=\"%s\">%s</a><img src=\"pictures/blank.gif\" width=\"15\"></span>\n",
				$this->inactiveBottomkatPic, $this->classActive, $target, $link, $text);
	}

	function bottomkatCloseRow() {
		printf ("</td></tr><tr><td colspan=\"2\" background=\"%s\">&nbsp;</td></tr></table>\n", $this->bottomPic);
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

	function segmentTopKats(&$structure) {
		global $auth;
		
		$segment = 1;
		$counter = 0;
		$lettercounter = 0;
			
		if (strtolower($this->topkatBreakLineLimit) == "auto")
			$topkatLetterBreakLineLimit = round($auth->auth["xres"] / 8 );
			
		foreach ($structure as $key=>$val) {
			if (!$val["topKat"]) {
				$i++;
				$counter++;
				$lettercounter = $lettercounter + strlen($val["name"]) + 6;
				if (strtolower($this->topkatBreakLineLimit) == "auto")
					if ($lettercounter > $topkatLetterBreakLineLimit) {
						$segment++;
						$lettercounter=0;
					}
				elseif (strtolower($this->topkatBreakLineLimit) > 0)
					if ($counter > $this->topkatBreakLineLimit) {
						$segment++;
						$counter=1;
					}
				
				$structure[$key]["topKatSegment"] = $segment;
			}
		}
		return $segment;
	}

	function printStructure ($structure, $tooltip, $addText) {
		//TopKats
		if ($this->topkatBreakLineLimit)
			$segments = $this->segmentTopKats($structure);
		else
			$segments = 1;

		reset($structure);
		foreach ($structure as $key=>$val) {
			if (!$val["topKat"]) {
				$topKats++;
				if ($val["active"]) {
					$tmp_topKat=$key;
					$activeSegment = $val["topKatSegment"];
				}
			}
		}
		$bottomKats=sizeof($structure)-$topKats;
		
		$tooltipCreated=FALSE;
		for ($s=1; $s<=$segments; $s++) {
			$this->topkatStart();
			reset($structure);
			$a=current($structure);

			for ($i=0; $i<$topKats; $i++) {
				$b=next($structure);
				$close=FALSE;		
				if (($a["topKatSegment"] == $s) && (($a["topKatSegment"] != $activeSegment) || ($activeSegment == $segments))) {
					if ((($tooltip) || ($addText)) && ($s == $segments) && ($activeSegment == $segments) && (!$tooltipCreated)) {
						$this->info($tooltip, $addText, $a["active"]);
						$tooltipCreated=TRUE;
					}
					if ($i+1 == $topKats)
						$close=TRUE;
					if ($a["topKatSegment"] <> $b["topKatSegment"])
						$close=TRUE;
					$this->topkat($a["name"], $a["link"], $a["width"],$a["active"], $a["target"], $close);
					}
				
				$a=$b;
				}
			$this->topkatCloseRow();
		}
		
		if ($activeSegment != $segments) {
			$this->topkatStart();
			reset($structure);
			$a=current($structure);
			for ($i=0; $i<$topKats; $i++) {
				$b=next($structure);
				$close=FALSE;		
				if ($a["topKatSegment"] == $activeSegment) {
					if ((($tooltip) || ($addText)) && (!$tooltipCreated)) {
						$this->info($tooltip, $addText, $a["active"]);
						$tooltipCreated=TRUE;
					}
					if ($i+1 == $topKats)
						$close=TRUE;
					if ($a["topKatSegment"] <> $b["topKatSegment"])
						$close=TRUE;
					$this->topkat($a["name"], $a["link"], $a["width"],$a["active"], $a["target"], $close);
				}
				$a=$b;
			}
			$this->topkatCloseRow();
		}
		//BottomKats
		if ($bottomKats) {
			$this->bottomkatStart();
			for ($i=0; $i<=$bottomKats; $i++) {
				if ($a["topKat"]==$tmp_topKat) {
					$this->bottomkat($a["name"], $a["link"], $a["active"], $a["target"]);
					}
				$a=next($structure);				
				}
			$this->bottomkatCloseRow();
		}
	}
	
	function setNoAktiveBottomkat($view) {
		if ((substr ($view, 0, 1) == "(") && (substr ($view,strlen($view)-1, strlen($view)) == ")")) {
			$this->noAktiveBottomkat=TRUE;
			$view=substr($view, 1, strlen($view)-2);
		}
		return $view;
	}
	
	function create($structure, $view, $tooltip='', $addText='') {
		$view=$this->setNoAktiveBottomkat($view);
		$structure=$this->activateStructure ($structure, $view);
		$this->printStructure($structure, $tooltip, $addText);
	}
}
?>