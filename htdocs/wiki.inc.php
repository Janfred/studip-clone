<?

/**
* Retrieve a WikiPage version from current seminar's WikiWikiWeb.
*
* Returns raw text data from database if requested version is
* available. If not, an
*
* @param string WikiWiki keyword to be retrieved
* @param int    Version number. If empty, latest version is returned.
*
**/
function getWikiPage($keyword, $version) {
	global $db, $SessSemName;
	$q = "SELECT * FROM wiki WHERE ";
	$q .= "keyword = '$keyword' AND range_id='$SessSemName[1]' ";
	if (!$version) {
		$q .= "ORDER BY version DESC";
	} else {
		$q .= "AND version='$version'";
	}
	$db->query($q);
	$exists=$db->next_record();
	if (!$exists) {
		if ($keyword=="StartSeite") {
			$body=_("Dieses Wiki ist noch leer. Bearbeiten Sie es!\nNeue Seiten oder Links werden einfach durch Eingeben von WikiNamen angelegt.");
		$wikidata=array("body"=>$body, "user_id"=>"nobody",  "version"=>-1);
		} else {
      return NULL;
    }
	} else {
		$wikidata = $db->Record;
	}
	return $wikidata;
}

/**
* Retrieve latest version for a given keyword
*
* @param	string	keyword	WikiPage name
*
**/
function latestVersion($keyword) {
	global $SessSemName;
	$db=new DB_Seminar;
	$q = "SELECT * FROM wiki WHERE ";
	$q .= "keyword='$keyword' AND range_id='$SessSemName[1]' ";
	$q .= "ORDER BY version DESC";
	$db->query($q);
	$db->next_record();
	return $db->Record;
}

/**
* Create HTML code with version navigation
*
* Generate a link-list of recent versions of WikiPage $keyword,
* add links to diff-script between version links.
*
* @param string	Wiki keyword for currently selected seminar
*
**/ 
function getWikiPageVersions($keyword) {
	global $SessSemName;
	$db = new DB_Seminar;
	$db->query("SELECT version FROM wiki WHERE keyword = '$keyword' AND range_id='$SessSemName[1]' ORDER BY version DESC");
	if ($db->affected_rows() == 0) {
		return "";
	}
	$db->next_record();
	$latest=$db->f("version");
	$difflink="";
	$last=$latest;
	$count=0; 

	$str = "Versionen: <a href=\"wiki.php?keyword=$keyword&view=diff\">"._("Diffs")."</a>&nbsp;&nbsp;&nbsp; ";
	$str .= "<a href=\"wiki.php?keyword=$keyword\">"._("Aktuell")."</a> ";
	while ($db->next_record()) {
		if ($count++ >= 10) { // list at most 10 versions
			break;
		}
		$v = $db->f("version");
		$str .= " | <a href=\"wiki.php?keyword=$keyword&version=$v\">$v</a>";
		$last=$v;
	}
	return $str;
}


/**
* Check if given keyword exists in current WikiWikiWeb.
* 
* @param	string	WikiPage keyword
*
**/
function keywordExists($str) {
	global $SessSemName;
	$db = new DB_Seminar;
	$db->query("SELECT keyword FROM wiki WHERE keyword='$str' AND range_id='$SessSemName[1]' LIMIT 1");
	$result=$db->next_record();
	return $result;
}


/**
* Check if keyword already exists or links to new page. 
* Returns HTML-Link-Representation.
* 
* @param	string	WikiPage keyword
*
**/
function isKeyword($str, $page){
	if (keywordExists($str)==NULL) {
		return ' <a href="wiki.php?keyword='.$str.'&view=editnew&lastpage='.$page.'">'.$str.'(?)</a>';
	} else {
		return ' <a href="wiki.php?keyword='.$str.'">'.$str.'</a>';
	}
}


/**
* Get lock information about page/version
* Returns database record or NULL if no lock set.
&nbsp;*
* @param	string	WikiPage keyword
* @param	int	WikiPage version
*
**/
function getLock($keyword, $version) {
	global $SessSemName;
	$db=new DB_seminar;
	$db->query("SELECT * FROM wiki_locks WHERE range_id='$SessSemName[1]' AND keyword='$keyword' AND version='$version'");
	$db->next_record();
	return $db->Record;
}

/**
* Release all locks for wiki page that are older than 30 minutes.
*
* @param	string	WikiPage keyword
*
**/
function releaseLocks($keyword) {
	global $SessSemName;
	$db=new DB_seminar;
	$db2=new DB_Seminar;
	$db->query("SELECT * FROM wiki_locks WHERE range_id='$SessSemName[1]' AND keyword='$keyword'");
	while ($db->next_record()) {
		if ((time() - $db->f("chdate")) > (30*60)) {
			$q="DELETE FROM wiki_locks WHERE range_id='".$db->f("range_id")."' AND keyword='".$db->f("keyword")."' AND chdate='".$db->f("chdate")."'";
			// print "<p>Query: $q</p>";
			$db2->query($q);
		}
	}
}

/**
* Release locks for current wiki page and current user
*
* @param	string	WikiPage keyword
*
**/
function releasePageLock($keyword) {
	global $SessSemName, $user_id;
	$db=new DB_seminar;
	$db->query("DELETE FROM wiki_locks WHERE range_id='$SessSemName[1]' AND keyword='$keyword' AND user_id='$user_id'");
}


/**
* Replace WikiWords with appropriate links in given string
*
* @param	string str	
*
**/
function wikiLinks($str, $page) { 
	return preg_replace("/(^|\s|\A|\>)([A-Z][a-z0-9]+[A-Z][a-zA-Z0-9]+)/e", "'\\1'.isKeyword('\\2', $page)", $str); 
}

/**
* Generate Meta-Information on Wiki-Page to display in top line
*
* @param	db-query result		all information about a wikiPage
*
**/
function getZusatz($wikiData) {
	if (!$wikiData || $wikiData["version"]<0) {
		return "";
	}
	$s = "<font size=-1>";
	$s .=  _("Version ") . $wikiData[version];
        $s .= sprintf(_(", ge&auml;ndert von %s am %s"), "</font><a href=\"about.php?username=".get_username ($wikiData[user_id])."\"><font size=-1 color=\"#333399\">".get_fullname ($wikiData[user_id])."</font></a><font size=-1>", date("d.m.Y, H:i",$wikiData[chdate])."<font size=-1>&nbsp;"."</font>");
	return $s;
}

/**
* List all topics in this seminar's wiki
*
* @param  mode  string  Either "all" or "new", affects default sorting and page title.
* @param  sortby  string  Different sorting of entries.
**/
function listPages($mode, $sortby=NULL) {
	global $SessSemName, $user_id, $loginfilelast, $begin_blank_table, $end_blank_table;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;

  if ($mode=="all") {
    $selfurl = "wiki.php?view=listall";
    $sort = "ORDER by keyword"; // default sort order for "all pages"
    $nopages = _("In dieser Veranstaltung wurden noch keine WikiSeiten angelegt.");
  } else if ($mode=="new") {
    $selfurl = "wiki.php?view=listnew";
    $sort = "ORDER by lastchange"; // default sort order for "new pages"
    $nopages = _("Seit Ihrem letzten Login gibt es keinen neuen Seiten.");
  } else {
			parse_msg("info§" . _("ERROR: Falscher Anzeigemodus:") . $mode);
      return 0;
  }  

  $titlesortlink = "title";
  $changesortlink = "lastchange";
  $bysortlink = "by";
  if ($sortby == 'title') { // sort by keyword, prepare link for descending sorting
    $sort = " ORDER BY keyword";
    $titlesortlink = "titledesc";
  } else if ($sortby == 'titledesc') { // sort descending by keyword, prep link for asc. sort
    $sort = " ORDER BY keyword DESC";
    $titlesortlink = "title";
  } else if ($sortby == 'lastchange') {
    $sort = " ORDER BY lastchange DESC"; // default: Neuester zuerst
    $changesortlink = "lastchangedesc";
  } else if ($sortby == 'lastchangedesc') {
    $sort = " ORDER BY lastchange"; // aelteste zuerst
    $changesortlink = "lastchange";
  } 

  if ($mode=="all") {
    $q="SELECT keyword, MAX(chdate) AS lastchange FROM wiki WHERE range_id='$SessSemName[1]' GROUP BY keyword " . $sort;
  } else if ($mode=="new") {
    $datumtmp = $loginfilelast[$SessSemName[1]];
    $q="SELECT keyword, MAX(chdate) AS lastchange FROM wiki WHERE range_id='$SessSemName[1]' AND chdate > '$datumtmp' GROUP BY keyword " . $sort;
  }
  $result=$db->query($q);

  // quit if no pages found
  if  ($db->affected_rows() == 0){
    echo "<table width=\"100%\" border=0 cellpadding=0 cellspacing=0>";
    parse_msg ("info\xa7" . $nopages);
    echo "</table></td></tr></table></body></html>";
    die;
  }

  // show pages
  echo $begin_blank_table;
  echo "<tr><td class=\"blank\" colspan=\"2\">&nbsp;</td></tr>\n";
  echo "<tr><td class=\"blank\" colspan=\"2\">";
  echo "<table width=\"99%\" border=\"0\"  cellpadding=\"2\" cellspacing=\"0\" align=\"center\">";
  echo "<tr height=28>";
  $s = "<td class=\"steel\" width=\"%d%%\" align=\"left\"><img src=\"pictures/blank.gif\" width=\"1\" height=\"20\">%s</td>";
  printf($s, 3, "&nbsp;");
  printf($s, 49, "<font size=-1><b><a href=\"$selfurl&sortby=$titlesortlink\">"._("Titel")."</a></b></font>");
  printf($s, 24, "<font size=-1><b><a href=\"$selfurl&sortby=$changesortlink\">"._("Letzte Änderung")."</a></b></font>");
  printf($s, 24, "<font size=-1><b>"._("von")."</b></font>");
  echo "</tr>";

	$c=1;
	while ($db->next_record()) {

		if ($c++ % 2) {   // switcher fuer die Klassen
			$class="steel1";
			$class2="colorline";
		} else {
			$class="steelgraulight";
			$class2="colorline2";
		}

    $keyword=$db->f("keyword");
    $lastchange=$db->f("lastchange");
    $db2->query("SELECT user_id FROM wiki WHERE range_id='$SessSemName[1]' AND keyword='$keyword' AND chdate='$lastchange'");
    $db2->next_record();
    $userid=$db2->f("user_id");
    
		print("<tr><td class=\"$class\">&nbsp;</td>");
		printf("<td class=\"%s\"><font size=\"-1\"><a href = wiki.php?keyword=" . $keyword . ">", $class);
		print(htmlReady($keyword) ."</a>");
		print("</font></td>");
		print("<td class=\"$class\" align=\"left\"><font size=\"-1 \">");

		print(date("d.m.Y, H:i", $lastchange));
		print("</font></td>");
		print("<td class=\"$class\" align=\"left\"><font size=\"-1\">");
		print(get_fullname($userid));
		print("</font></td></tr>\n");
	}
	echo "</table><p>&nbsp;</p>";
  echo $end_blank_table;
}


function wikiSeminarHeader() {
	global $SessSemName;
	echo "\n<table width=\"100%\" class=\"blank\" border=0 cellpadding=0 cellspacing=0>\n";
	echo "<tr>";
	echo "<td class=\"topic\" width=\"95%\">";
	echo "<b>&nbsp;<img src=\"pictures/icon-wiki.gif\" align=absmiddle>&nbsp; ". $SessSemName["header_line"] ." - " .  _("Wiki") . "</b></td>";
	echo "<td class=\"topic\" width=\"5%\" align=\"right\">";
	echo "<a href =\"wiki.php?cmd=anpassen\">";
	echo "<img src=\"pictures/pfeillink.gif\" border=0 " . tooltip(_("Look & Feel anpassen")) . ">&nbsp;</a></td></tr>\n";
	echo "<tr><td class=\"blank\" colspan=2>&nbsp; </td></tr>\n";
	echo "</table>";
}

function wikiSinglePageHeader($wikiData, $keyword) {
	global $begin_blank_table, $end_blank_table;
	$zusatz=getZusatz($wikiData);

	echo $begin_blank_table;
	printhead(0, 0, FALSE, "icon-wiki", FALSE, "", "<b>$keyword</b>", $zusatz);
	echo $end_blank_table; 
}

function wikiEdit($keyword, $wikiData, $backpage=NULL) {
    global $begin_blank_table, $end_blank_table, $user_id;
    
    if (!$wikiData) {
      $body = "";
      $version = 0;
      $lastpage="&lastpage=$backpage";
    } else {
      $body = $wikiData["body"];
      $version = $wikiData["version"];
      $lastpage = "";
    }
  	releaseLocks($keyword); // kill old locks 
    $lock=getLock($keyword,$wikiData["version"]);
		if ($lock && $lock["user_id"]!=$user_id) { //XXX TODO
		  $locktime=ceil((time()-$lock["chdate"])/60);
		  $lockuser=get_fullname($lock["user_id"]);
			echo $begin_blank_table;
      echo "<tr><td class=blank>&nbsp;</td></tr>";
      parse_msg("info§" . sprintf(_("Die Seite wird evtl. von %s bearbeitet. (Seit %s Minuten)"), $lockuser, $locktime) . "<br>" . _("Wenn Sie die Seite trotzdem &auml;ndern, kann ein Versionskonflikt entstehen.") . "<br>" . _("Es werden dann beide Versionen eingetragen und m&uuml;ssen von Hand zusammengef&uuml;hrt werden."));
      echo $end_blank_table;
		}

		echo "\n<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" width=\"100%\">";
    echo "<tr><td>";
    $cont = "<font size=-2><p>" . _("Sie k&ouml;nnen beliebigen Text einf&uuml;gen und vorhandenen Text &auml;ndern.") . " ";
    $cont .= _("Beachten Sie dabei die <a href=\"help/index.php?help_page=ix_forum6.htm\">Formatierungsm&ouml;glichkeiten</a>.") . "<br>";
    $cont .= _("Links entstehen automatisch aus W&ouml;rtern, die mit Gro&szlig;buchstaben beginnen und einen Gro&szlig;buchstaben in der Wortmitte enthalten.") . "</p></font>";
    
		$cont .= "<p><form method=\"post\" action=\"?keyword=$keyword&cmd=edit\">";
		$cont .= "<textarea name=\"body\" cols=\"80\" rows=\"15\">$body</textarea>\n";
		$cont .= "<input type=\"hidden\" name=\"wiki\" value=\"$keyword\">";
		$cont .= "<input type=\"hidden\" name=\"version\" value=\"$version\">";
		$cont .= "<input type=\"hidden\" name=\"submit\" value=\"true\">";
		$cont .= "<input type=\"hidden\" name=\"cmd\" value=\"show\">";
		$cont .= "<br><br><input type=image name=\"submit\" value=\"abschicken\" " . makeButton("abschicken", "src") . " align=\"absmiddle\" border=0 >&nbsp;<a href=\"wiki.php?cmd=abortedit&keyword=$keyword$lastpage\"><img " . makeButton("abbrechen", "src") . " align=\"absmiddle\" border=0></a>";
		$cont .= "</form>\n";
		printcontent(0,0,$cont,"");
		echo "</tr></table>     ";
		echo "</td></tr></table>";
}

/////////////////////////////////////////////////
// DIFF funcitons adapted from:
// PukiWiki - Yet another WikiWikiWeb clone.
// http://www.pukiwiki.org (GPL'd)
//
//
// 
function do_diff($strlines1,$strlines2)
{
	$plus="<td width=\"3\" bgcolor=\"green\">&nbsp;</td>";
	$minus="<td width=\"3\" bgcolor=\"red\">&nbsp;</td>";
	$equal="<td width=\"3\" bgcolor=\"grey\">&nbsp;</td>";
	$obj = new line_diff($plus, $minus, $equal);
	$str = $obj->str_compare($strlines1,$strlines2);
	return $str;
}

/*
line_diff

S. Wu, <A HREF="http://www.cs.arizona.edu/people/gene/vita.html">
E. Myers,</A> U. Manber, and W. Miller,
<A HREF="http://www.cs.arizona.edu/people/gene/PAPERS/np_diff.ps">
"An O(NP) Sequence Comparison Algorithm,"</A>
Information Processing Letters 35, 6 (1990), 317-323.
*/

class line_diff
{
	var $arr1,$arr2,$m,$n,$pos,$key,$plus,$minus,$equal,$reverse;
	
	function line_diff($plus='+',$minus='-',$equal='=')
	{
		$this->plus = $plus;
		$this->minus = $minus;
		$this->equal = $equal;
	}
	function arr_compare($key,$arr1,$arr2)
	{
		$this->key = $key;
		$this->arr1 = $arr1;
		$this->arr2 = $arr2;
		$this->compare();
		$arr = $this->toArray();
		return $arr;
	}
	function set_str($key,$str1,$str2)
	{
		$this->key = $key;
		$this->arr1 = array();
		$this->arr2 = array();
		$str1 = preg_replace("/\r/",'',$str1);
		$str2 = preg_replace("/\r/",'',$str2);
		foreach (explode("\n",$str1) as $line)
		{
			$this->arr1[] = new DiffLine($line);
		}
		foreach (explode("\n",$str2) as $line)
		{
			$this->arr2[] = new DiffLine($line);
		}
	}
	function str_compare($str1,$str2)
	{
		$this->set_str('diff',$str1,$str2);
		$this->compare();
		
		$str = '';
		foreach ($this->toArray() as $obj)
		{
			$str .= "<tr>".$obj->get('diff')."<td width=\"10\">&nbsp;</td><td>".$obj->text()."</td></tr>";
		}
		return $str;
	}
	function compare()
	{
		$this->m = count($this->arr1);
		$this->n = count($this->arr2);
		
		if ($this->m == 0 or $this->n == 0) // no need compare.
		{
			$this->result = array(array('x'=>0,'y'=>0));
			return;
		}
		
		// sentinel
		array_unshift($this->arr1,new DiffLine(''));
		$this->m++;
		array_unshift($this->arr2,new DiffLine(''));
		$this->n++;
		
		$this->reverse = ($this->n < $this->m);
		if ($this->reverse) // swap
		{
			$tmp = $this->m; $this->m = $this->n; $this->n = $tmp;
			$tmp = $this->arr1; $this->arr1 = $this->arr2; $this->arr2 = $tmp;
			unset($tmp);
		}
		
		$delta = $this->n - $this->m; // must be >=0;
		
		$fp = array();
		$this->path = array();
		
		for ($p = -($this->m + 1); $p <= ($this->n + 1); $p++)
		{
			$fp[$p] = -1;
			$this->path[$p] = array();
		}
		
		for ($p = 0;; $p++)
		{
			for ($k = -$p; $k <= $delta - 1; $k++)
			{
				$fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
			}
			for ($k = $delta + $p; $k >= $delta + 1; $k--)
			{
				$fp[$k] = $this->snake($k, $fp[$k - 1], $fp[$k + 1]);
			}
			$fp[$delta] = $this->snake($delta, $fp[$delta - 1], $fp[$delta + 1]);
			if ($fp[$delta] >= $this->n)
			{
				$this->pos = $this->path[$delta]; // 
				return;
			}
		}
	}
	function snake($k, $y1, $y2)
	{
		if ($y1 >= $y2)
		{
			$_k = $k - 1;
			$y = $y1 + 1;
		}
		else
		{
			$_k = $k + 1;
			$y = $y2;
		}
		$this->path[$k] = $this->path[$_k];// 
		$x = $y - $k;
		while ((($x + 1) < $this->m) and (($y + 1) < $this->n)
			and $this->arr1[$x + 1]->compare($this->arr2[$y + 1]))
		{
			$x++; $y++;
			$this->path[$k][] = array('x'=>$x,'y'=>$y); // 
		}
		return $y;
	}
	function toArray()
	{
		$arr = array();
		if ($this->reverse) //
		{
			$_x = 'y'; $_y = 'x'; $_m = $this->n; $arr1 =& $this->arr2; $arr2 =& $this->arr1;
		}
		else
		{
			$_x = 'x'; $_y = 'y'; $_m = $this->m; $arr1 =& $this->arr1; $arr2 =& $this->arr2;
		}
		
		$x = $y = 1;
		$this->add_count = $this->delete_count = 0;
		$this->pos[] = array('x'=>$this->m,'y'=>$this->n); // sentinel
		foreach ($this->pos as $pos)
		{
			$this->delete_count += ($pos[$_x] - $x);
			$this->add_count += ($pos[$_y] - $y);
			
			while ($pos[$_x] > $x)
			{
				$arr1[$x]->set($this->key,$this->minus);
				$arr[] = $arr1[$x++];
			}
			
			while ($pos[$_y] > $y)
			{
				$arr2[$y]->set($this->key,$this->plus);
				$arr[] =  $arr2[$y++];
			}
			
			if ($x < $_m)
			{
				$arr1[$x]->merge($arr2[$y]);
				$arr1[$x]->set($this->key,$this->equal);
				// $arr[] = $arr1[$x];
			}
			$x++; $y++;
		}
		return $arr;
	}
}

class DiffLine
{
	var $text;
	var $status;
	
	function DiffLine($text)
	{
		$this->text = "$text\n";
		$this->status = array();
	}
	function compare($obj)
	{
		return $this->text == $obj->text;
	}
	function set($key,$status)
	{
		$this->status[$key] = $status;
	}
	function get($key)
	{
		return array_key_exists($key,$this->status) ? $this->status[$key] : '';
	}
	function merge($obj)
	{
		$this->status += $obj->status;
	}
	function text()
	{
		return $this->text;
	}
}

?>

