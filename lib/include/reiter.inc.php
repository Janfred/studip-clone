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

require_once ('lib/visual.inc.php');

class reiter {
	//Classes
	var $classActive = "links1c";				//Klasse fuer Zellen, die Aktiv (=im Vordergrund) sind
	var $classInactive="links1a";				//Klasse fuer Zellen, die Inaktiv (=im Hintegrund) sind
	var $class2nd = "links1b";				//Klasse fuer Zellen in der zweiten Reiterebene
	var $classInfo = "linksinfo";				//Klasse fuer Zellen in der zweiten Reiterebene
	var $classDisabled="linksdisabled";				//Klasse fuer Zellen, die Disabled (=nicht klickbar) sind
	//Pics
	var $infoPic              = "info.gif";        //Bild das als Info Click/Alt-Text verwendet wird
	var $toActiveTopkatPic    = "reiter1.jpg";     //Trenner fuer Reiter
	var $toInactiveTopkatPic  = "reiter2.jpg";     //Trenner auf Inactive fuer Reiter
	var $closerTopkatPic      = "reiter4.jpg";     //Closer fuer Reiter
	var $closerInfo           = "";                //generic Closer for Info
	var $activeBottomkatPic   = "forumrot3.gif";   //Aktiver Pfeil
	var $inactiveBottomkatPic = "pfeilweiss2.gif"; //Inaktiver Pfeil
	var $bottomPic            = "reiter3.jpg";     //Unterer Abschluss
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
		printf ("<td class=\"%s\" %s nowrap>&nbsp; ", $this->classInfo, ($this->infoWidth) ? "width=\"$this->infoWidth\"" : "");
		if ($tooltip)
			printf ("<img align=\"absmiddle\" src=\"".$GLOBALS['ASSETS_URL']."images/%s\" %s border=\"0\">&nbsp;", $this->infoPic, tooltip($tooltip, TRUE, TRUE));
		if ($addText)
			printf ("<font class=\"%s\">%s</font>", $this->classInfo, $addText);
		if (($closeToActive) && ($this->toActiveTopkatPic))
			printf ("&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/%s\" align=absmiddle>", $this->toActiveTopkatPic);
		elseif ($this->toInactiveTopkatPic)
			printf ("&nbsp; <img src=\"".$GLOBALS['ASSETS_URL']."images/%s\" align=absmiddle>", $this->toInactiveTopkatPic);
		printf ("</td>\n");
		if ($this->spacerInfoTopkat)
			print ("<td> &nbsp; </td>");
		if ($this->closerInfo)
			printf ("<td><img src=\"%s\" align=absmiddle></td>", $this->closerInfo);
		return;
	}

	function topkat($text,$link,$width,$active=FALSE, $target="", $close=FALSE, $disabled=FALSE) {
		$link = self::absolutizeLink($link);
		if ($disabled) {
			if ($close) {
				printf("<td class=\"%s\" %s align=\"%s\" nowrap><font class=\"%s\">%s%s%s</font>%s</td>\n",
					$this->classInactive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classDisabled, $this->textAdd, $text, $this->textAdd,
					($this->closerTopkatPic) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/$this->closerTopkatPic\" align=absmiddle>" : "");
			} else {
				printf("<td class=\"%s\" %s align=\"%s\" nowrap><font class=\"%s\">%s%s%s</font>%s</td>\n",
					$this->classInactive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classDisabled, $this->textAdd, $text, $this->textAdd,
					($this->toInactiveTopkatPic) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/$this->toActiveTopkatPic\" align=absmiddle>" : "");
			}
		} else {
			if (($active) && (!$close))
				printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
					$this->classActive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classActive, $target, $link, $this->textAdd, $text, $this->textAdd,
					($this->toInactiveTopkatPic) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/$this->toInactiveTopkatPic\" align=absmiddle>" : "");
			if ((!$active) && (!$close))
				printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
					$this->classInactive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classInactive, $target, $link, $this->textAdd, $text, $this->textAdd,
					($this->toActiveTopkatPic) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/$this->toActiveTopkatPic\" align=absmiddle>" : "");
			if (($active) && ($close))
				printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
					$this->classActive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classActive, $target, $link, $this->textAdd, $text, $this->textAdd,
					($this->closerTopkatPic) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/$this->closerTopkatPic\" align=absmiddle>" : "");
			if ((!$active) && ($close))
				printf("<td class=\"%s\" %s align=\"%s\" nowrap><a class=\"%s\" target=\"%s\" href=\"%s\">%s%s%s</a>%s</td>\n",
					$this->classInactive, ($width) ? "width=\"$width\"" : "", $this->katsAlign, $this->classInactive, $target, $link, $this->textAdd, $text, $this->textAdd,
					($this->closerTopkatPic) ? "<img src=\"".$GLOBALS['ASSETS_URL']."images/$this->closerTopkatPic\" align=absmiddle>" : "");
			return;
		}
	}

	function topkatCloseRow($addLine = FALSE, $cols = '') {
		if ($addLine) {
			printf ("</tr><td colspan=\"%s\" style=\"background-image: url('".$GLOBALS['ASSETS_URL']."images/line.gif')\"><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"10\" height=\"1\" /></td>", $cols);
		}
		printf ("</tr></table>\n");
		return;
	}

	function bottomkatStart() {
		printf ("<table width=\"100%%\" cellspacing=0 cellpadding=4 border=0>\n<tr><td class=\"%s\" width=\"2%%\">&nbsp; </td><td class=\"%s\">",  $this->class2nd, $this->class2nd);
		return;
	}

	function bottomkat($text,$link,$active=FALSE, $target="", $disabled=FALSE) {
		$link = self::absolutizeLink($link);
		if ($disabled) {
			printf("<span style=\"white-space:nowrap;\"><img src=\"".$GLOBALS['ASSETS_URL']."images/%s\" border=\"0\"><font class=\"%s\">%s</font><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"15\"></span>\n",
				$this->inactiveBottomkatPic, $this->classDisabled, $text);
		} else {
			if (($active) && (!$this->noAktiveBottomkat))
				printf("<span style=\"white-space:nowrap;\"><img src=\"".$GLOBALS['ASSETS_URL']."images/%s\" border=\"0\"><a class=\"%s\" target=\"%s\" href=\"%s\">%s</a><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"15\"></span>\n",
					$this->activeBottomkatPic, $this->class2nd, $target, $link, $text);
			else
				printf("<span style=\"white-space:nowrap;\"><img src=\"".$GLOBALS['ASSETS_URL']."images/%s\" border=\"0\"><a class=\"%s\" target=\"%s\" href=\"%s\">%s</a><img src=\"".$GLOBALS['ASSETS_URL']."images/blank.gif\" width=\"15\"></span>\n",
					$this->inactiveBottomkatPic, $this->class2nd, $target, $link, $text);
		}
	}

	function bottomkatIsolator () {
		print "<span style=\"white-space:nowrap;\"><img valign=\"bottom\" src=\"".$GLOBALS['ASSETS_URL']."images/isolator.gif\" border=\"0\">&nbsp;&nbsp;&nbsp;</span>\n";
	}

	function bottomkatCloseRow() {
		printf ("</td></tr></table>\n");
		return;
	}

	function activateStructure ($structure, $view) {
		if (!$view) {
			reset ($structure);
			list($index)=each($structure);
			$view=$index;
		}
		$structure[$view]["active"]=TRUE;
		if ($structure[$view]["topKat"]){
			$structure[$structure[$view]["topKat"]]["active"]=TRUE;
		} else {
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

		//for nobodys, they dont have xres (or if we use this class in a non phpLib context)
		if (!$auth->auth["xres"])
			$xres = 800;
		else
			$xres = $auth->auth["xres"];

		if (strtolower($this->topkatBreakLineLimit) == "auto")
			$topkatLetterBreakLineLimit = round($xres / 7.1);

		foreach ($structure as $key=>$val) {
			if (!$val["topKat"]) {
				$i++;
				$counter++;
				$lettercounter = $lettercounter + strlen($val["name"]) + 6;
				if (strtolower($this->topkatBreakLineLimit) == "auto") {
					if ($lettercounter > $topkatLetterBreakLineLimit) {
						$segment++;
						$lettercounter=0;
					}
				} else {
					if ($counter > $this->topkatBreakLineLimit) {
						$segment++;
						$counter=1;
					}
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
		$rows=0;

		for ($s=1; $s<=$segments; $s++) {
			reset($structure);
			$a=current($structure);
				$cols=0;
			$topkatOpened = FALSE;

			for ($i=0; $i<$topKats; $i++) {
				$b=next($structure);
				$close=FALSE;
				if (($a["topKatSegment"] == $s) && (($a["topKatSegment"] != $activeSegment) || ($activeSegment == $segments))) {
					if (!$topkatOpened) {
						$rows++;
						$this->topkatStart();
						$topkatOpened = TRUE;
					}
					if ((($tooltip) || ($addText)) && ($s == $segments) && ($activeSegment == $segments) && (!$tooltipCreated)) {
						$this->info($tooltip, $addText, $a["active"]);
						$tooltipCreated=TRUE;
					}
					if ($i+1 == $topKats)
						$close=TRUE;
					if ($a["topKatSegment"] <> $b["topKatSegment"])
						$close=TRUE;
					$this->topkat($a["name"], $a["link"], $a["width"],$a["active"], $a["target"], $close, $a["disabled"]);
					$cols++;
					}

				$a=$b;
				}
			if ($topkatOpened) {
				$this->topkatCloseRow(($rows < $segments) ? TRUE : FALSE, $cols);
			}
		}

		if ($activeSegment != $segments) {
			$this->topkatStart();
			reset($structure);
			$a=current($structure);
			$cols=0;

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
					$this->topkat($a["name"], $a["link"], $a["width"],$a["active"], $a["target"], $close, $a["disabled"]);
					$cols++;
				}
				$a=$b;
			}
			$this->topkatCloseRow(FALSE);
		}
		//BottomKats
		if ($bottomKats) {
			$this->bottomkatStart();
			for ($i=0; $i<=$bottomKats; $i++) {
				if ($a["topKat"]==$tmp_topKat) {
					if ($a["newline"])
						print "<br />";
					if ($a["isolator"])
						$this->bottomkatIsolator();
					$this->bottomkat($a["name"], $a["link"], $a["active"], $a["target"], $a["disabled"]);
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

	function absolutizeLink($link) {
		if (!(preg_match('#^[a-z]+://#', $link) || $link[0] === '/')) {
			$link = $GLOBALS['ABSOLUTE_URI_STUDIP'].$link;
		}
		return $link;
	}
}
?>
