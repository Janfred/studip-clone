<?

// wikiMarkups are used by the wikiDirective function
// after all other conversions,
// wikiMarkup patterns are replaced
// args to wikiMarkup are passed to preg_replace
//
wikiMarkup('/\\(:stepform:\\)/e','wiki_stepform()');
wikiMarkup('/\\(:steplist\\s*(.*?):\\)/e',"wiki_steplist(array('q'=>'$1'))");

if ($_REQUEST['step_action']=='new_step') {
	// add new StEP-page to wiki pages
	wiki_newstep();
}

// create StEP form
//
function wiki_stepform() {
	global $PHP_SELF, $keyword;

	$form="<h2>"._("Neuer StEP-Eintrag")."</h2>";
	$form.="<form action=\"$PHP_SELF\" method=post>\n
		<input type=\"hidden\" name=\"step_action\" value=\"new_step\">
		<input type=\"hidden\" name=\"keyword\" value=\"$keyword\">
		<table>
		<tr><td>Zusammenfassung:</td>
		<td><input size=60 name=\"step_zusammenfassung\"></td></tr>
		<tr><td>Komplexität:</td>
		<td><select name=\"step_komplexitaet\"><option>gering</option><option>mittel</option><option>hoch</option></select></td></tr>
		<tr><td>Beschreibung:</td>
		<td><textarea name=\"step_beschreibung\" cols=60 rows=10></textarea></td></tr>
		<tr><td>&nbsp;</td><td><input type=image ".makeButton("eintragen","src")." border=0></td></tr>
		</table>
		</form>";
	return $form;
}

// get list of StEP entries
//
function wiki_get_steppagelist() {
	global $SessSemName;
	$list=array();
	$db=new DB_Seminar();
	$query="SELECT DISTINCT keyword FROM wiki WHERE range_id='$SessSemName[1]' AND keyword LIKE 'StEP%'";
	$db->query($query);
	while ($db->next_record()) {
		$list[]=$db->f('keyword');
	}
	return $list;
}
 
// create new StEP page
// data is passed from form defined in wiki_stepform()
//
function wiki_newstep() {
	global $step_zusammenfassung, $step_komplexitaet, $step_beschreibung;
	global $SessSemName, $auth;
	global $keyword, $view;
	$list=wiki_get_steppagelist();
	foreach ($list as $l) {
		$issue=max(@$issue, substr($l,4));
	}
	$pagename=sprintf("StEP%05d",@$issue+1);
	$createTime=date('Y-m-d H:i',time());
	$author=$auth->auth['uname'];
	$text="!!!!$pagename
Zusammenfassung: $step_zusammenfassung
Komplexitaet: $step_komplexitaet
Autor: $author
Erstellt: $createTime
Status: neu
Beschreibung: 

$step_beschreibung";
	$db=new DB_Seminar();
	$query="INSERT INTO wiki SET range_id='$SessSemName[1]', keyword='$pagename', body='".addslashes($text)."', chdate='".time()."', version='1'";
	$db->query($query);
	$view='show';
	$keyword=$pagename;
	return;
}

// wiki_steplist creates a table of StEP issues according to various
// criteria.  
function wiki_steplist($opt) {
  global $PHP_SELF;
  global $SessSemName;
  global $keyword;
  $pagename=$keyword;
  $opt = array_merge($opt,@$_REQUEST);
  $steplist = wiki_get_steppagelist();
  $out[] = "<table border='1' cellspacing='0' cellpadding='3'>
    <tr><th><a href='$PHP_SELF?keyword=$pagename&order=-name'>StEP#</a></th>
      <th><a href='$PHP_SELF?keyword=$pagename&order=status'>Status</a></th>
      <th><a href='$PHP_SELF?keyword=$pagename&order=erstellt'>Erstellt</a></th>
      <th><a href='$PHP_SELF?keyword=$pagename&order=autor'>Autor</a></th>
      <th><a href='$PHP_SELF?keyword=$pagename&order=komplexitaet'>Komplexitaet</a></th>
      <th><a href='$PHP_SELF?keyword=$pagename&order=zusammenfassung'>Zusammenfassung</a></th></tr>";
  $terms = preg_split('/((?<!\\S)[-+]?[\'"].*?[\'"](?!\\S)|\\S+)/',
    $opt['q'],-1,PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
  foreach($terms as $t) {
    if (trim($t)=='') continue;
    if (preg_match('/([^\'":=]*)[:=]([\'"]?)(.*?)\\2$/',$t,$match))
      $opt[strtolower($match[1])] = $match[3]; 
  }
  $n=0; $slist=array();
  foreach($steplist as $s) {
    $page = getLatestVersion($s,$SessSemName[1]);
    preg_match_all("/(^|\n)([A-Za-z][^:]*):([^\n]*)/",$page['body'],$match);
    $fields = array();
    for($i=0;$i<count($match[2]);$i++) 
      $fields[strtolower($match[2][$i])] = 
        htmlentities($match[3][$i],ENT_QUOTES);
    foreach(array('status','erstellt','autor','komplexitaet','zusammenfassung'
        ) as $h) {
      if (!@$opt[$h]) continue;
      foreach(preg_split('/[ ,]/',$opt[$h]) as $t) {
        if (substr($t,0,1)!='-' && substr($t,0,1)!='!') {
          if (strpos(strtolower(@$fields[$h]),strtolower($t))===false) 
            continue 3;
        } else if (strpos(strtolower(@$fields[$h]),
             strtolower(substr($t,1)))!==false) 
          continue 3;
      }
    }
    $slist[$n] = $fields;
    $slist[$n]['name'] = $s;
    $n++;
  }
  $cmp = CreateOrderFunction(@$opt['order'].',status,autor,-erstellt,zusammenfassung');
  usort($slist,$cmp);
  foreach($slist as $s) {
    $out[] = "<tr><td><a href='$PHP_SELF?keyword=$s[name]'>$s[name]</a></td>";
    foreach(array('status','erstellt','autor','komplexitaet','zusammenfassung'
        ) as $h) 
      $out[] = @"<td>{$s[$h]}</td>";
    $out[] = "</tr>";
  }
  $out[] = "</table>";
  return implode('',$out);
}

// This function creates specialized ordering functions needed to
// (more efficiently) perform sorts on arbitrary sets of criteria.
function CreateOrderFunction($order) { 
  $code = '';
  foreach(preg_split('/[\\s,|]+/',strtolower($order),-1,PREG_SPLIT_NO_EMPTY) 
      as $o) {
    if (substr($o,0,1)=='-') { $r='-'; $o=substr($o,1); }
    else $r='';
    if (preg_match('/\\W/',$o)) continue;
    $code .= "\$c=strcasecmp(@\$x['$o'],@\$y['$o']); if (\$c!=0) return $r\$c;\n";
  }
  $code .= "return 0;\n";
  return create_function('$x,$y',$code);
}


?>
