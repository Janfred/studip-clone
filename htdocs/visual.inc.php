<?

require_once($ABSOLUTE_PATH_STUDIP."config.inc.php");

/*****************************************************************************
get_ampel_write, waehlt die geeignete Grafik in der Ampel Ansicht 
(fuer Berechtigungen) aus. Benoetigt den Status in der Veranstaltung
und auf der Anmeldeliste und den read_level der Veranstaltung
/*****************************************************************************/

function get_ampel_write ($mein_status, $admission_status, $write_level) {
	global $perm;
	
	if ($mein_status == "dozent" || $mein_status == "tutor" || $mein_status == "autor") { // in den Fällen darf ich auf jeden Fall schreiben
		echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
	} else {
		switch($write_level){
			case 0 : //Schreiben darf jeder
				echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
			break;
			case 1 : //Schreiben duerfen registrierte nur Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
				else
					echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
			break;
			case 2 : //Schreiben nur mit Passwort
				if ($perm->have_perm("autor"))
					echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(mit Passwort)</font>";
				else
					echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
			break;
			case 3 : //Schreiben nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmelde-/Warteliste)</font>";
					else
						echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmeldeverfahren)</font>";
				else
					echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
			break;
		}
	}
}

/*****************************************************************************
get_ampel_read, waehlt die geeignete Grafik in der Ampel Ansicht 
(fuer Berechtigungen) aus. Benoetigt den Status in der Veranstaltung
und auf der Anmeldeliste und den read_level der Veranstaltung
/*****************************************************************************/

function get_ampel_read ($mein_status, $admission_status, $read_level) {
	global $perm;

	if ($mein_status) { // wenn ich im Seminar schon drin bin, darf ich auf jeden Fall lesen
		echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
	} else {
		switch($read_level){
			case 0 : //Lesen darf jeder
				echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
			break;
			case 1 : //Lesen duerfen registrierte nur Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					echo"<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
				else
					echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten!)</font>";
			break; //Lesen nur mit Passwort
			case 2 :
				if ($perm->have_perm("autor"))
					echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(mit Passwort)</font>";
				else
					echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten!)</font>";
			break;
			case 3 : //Lesen nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmelde-/Warteliste)</font>";
					else
						echo"<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmeldeverfahren)</font>";
				else
					echo"<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
			break;
		}
	}
}

/*****************************************************************************
cssClassSwitcher, Klasse um cssClasses fuer Zebra auszuwaehlen
/*****************************************************************************/
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

function htmlReady($what, $trim = TRUE, $br = FALSE){
	if ($trim)
		$what = trim(htmlentities($what,ENT_QUOTES));
	else
		$what = htmlentities($what,ENT_QUOTES);
	if ($br)
		$what = preg_replace("/(\n\r|\r\n|\n|\r)/", "<br />", $what); // newline fixen
	return $what;
}

function JSReady ($what = "", $target = "overlib") {        
	switch ($target) {

	case "popup" :
		$what = addslashes(htmlentities($what,ENT_COMPAT));
		$what = str_replace("\n","<br />",$what);
		$what = str_replace("\r","",$what);
		return $what;
	break;
	
	case "alert" :
		$what = addslashes(htmlentities($what,ENT_COMPAT));
		$what = str_replace("\r","",$what);
		$what = str_replace("\n","\\n",$what); // alert boxen stellen keine html tags dar 
		return $what;
	break;

	case "forum" :

		$what = htmlentities($what,ENT_COMPAT);
		$what = format($what);
		$what = str_replace("\r","",$what);
		$what = smile($what);
		$what = str_replace("\n","<br /> ",$what);
		if (ereg("\[quote",$what) AND ereg("\[/quote\]",$what))
			$what = quotes_decode($what);
		$what = "<p width=\"100%\"class=\"printcontent\">" . $what . "</p>";
//                $what = "<table width=\"100%\"><tr><td class=\"printcontent\">" . $what . "</td></tr></table>";
		$what = addslashes(htmlentities($what,ENT_COMPAT));
		return $what;
		break;

	case "overlib" :
	default :
		$what = addslashes(htmlentities(htmlentities($what,ENT_COMPAT),ENT_COMPAT));
		$what = str_replace("\n","<br />",$what);
		$what = str_replace("\r","",$what);
		return $what;
		break;
	}
}

//////////////////////
// de- und encodieren der Quotings

function quotes_decode($description) 
{
// Funktion um Quotings zu encoden
// $description: der Text der gequotet werden soll, wird zurueckgegeben

	$description = " ".$description;
	$stack = Array();
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($description))) {        
		$curr_pos = strpos($description, "[", $curr_pos);
		if ($curr_pos) {
			$possible_start = substr($description, $curr_pos, 6);
			$possible_end = substr($description, $curr_pos, 8);
			if (strcasecmp("[quote", $possible_start) == 0) {
				array_push($stack, $curr_pos);
				++$curr_pos;
				}
			else if (strcasecmp("[/quote]", $possible_end) == 0) {
				if (sizeof($stack) > 0) {
					$start_index = array_pop($stack);
					$before_start_tag = substr($description, 0, $start_index);
					$between_tags = substr($description, $start_index+6, $curr_pos - $start_index-6);
//                                        echo $between_tags."<hr>";
					$after_end_tag = substr($description, $curr_pos + 8);
					IF (substr($between_tags,0,1)=="=") { //wir haben einen Namen angegeben
						$nameend_pos = strpos($between_tags,"]");
						$quote_name = substr($between_tags,1,$nameend_pos-1);
						IF (substr($between_tags,$nameend_pos,5)=="]<br>") // ja, hier wurde anstaendig gequotet
							$between_tags = substr($between_tags,$nameend_pos+6);
						ELSE // da wird gepfuscht, also mal besser Finger weg
							$between_tags = substr($between_tags,$nameend_pos+1);
						$between_tags = "<b>".$quote_name." hat geschrieben:</b><hr>".$between_tags;
						}
					ELSE { // kein Name, also nur Zitat
						$nameend_pos = strpos($between_tags,"]");
						IF (substr($between_tags,$nameend_pos,5)=="]<br>") // ja, hier wurde anstaendig gequotet
							$between_tags = "<b>Zitat:</b><hr>".substr($between_tags,$nameend_pos+6);
						ELSE // da wird gepfuscht, also mal besser Finger weg
							$between_tags = "<b>Zitat:</b><hr>".substr($between_tags,$nameend_pos+1);
						}
					$description = $before_start_tag . "<blockquote class=\"quote\">";
					$description .= $between_tags . "</blockquote>";
					$description .= $after_end_tag;
					if (sizeof($stack) > 0) {
						$curr_pos = array_pop($stack);
						array_push($stack, $curr_pos);
						++$curr_pos;
						}
					else $curr_pos = 1;
					}
				else ++$curr_pos;        
				}
			else ++$curr_pos;        
			}
		} 
	return $description;
}

///////////

function quotes_encode($description,$author)
{
// Funktion um Quotings zu encoden
// $description: der Text der gequotet werden soll, wird zurueckgegeben
// $author: Name des urspruenglichen Autors

	IF (ereg("%%\[editiert von",$description)) { // wurde schon mal editiert
		$postmp = strpos($description,"%%[editiert von");
		$description = substr_replace($description," ",$postmp);
		}
	WHILE (ereg("\[quote",$description) AND ereg("\[/quote\]",$description)){ // da wurde schon mal zitiert...
		$pos1 =         strpos($description, "[quote");
		$pos2 =         strpos($description, "[/quote]");
		IF ($pos1 < $pos2)
			$description = substr($description,0,$pos1)."[...]".substr($description,$pos2+8);
		ELSE break; // hier hat einer von Hand rumgepfuscht...
		}
	$description = "[quote=".$author."]\n".$description."\n[/quote]";
	RETURN $description;
}

////////////////////////////////////////////////////////////////////////////////

function formatReady($what, $trim = TRUE){
	return smile(FixLinks(format(htmlReady($what, $trim, FALSE)), FALSE, TRUE));
}

////////////////////////////////////////////////////////////////////////////////

// ermöglicht einfache Formatierungen in Benutzereingaben

function format($text){
	$text = preg_replace("'\n?\r\n?'", "\n", $text);
	$pattern = array("'\n--+(\d?)(\n|$|(?=<))'m",              // Trennlinie
					"'(^|\s)%(?!%)(\S+%)+(?=(\s|$))'e",     // SL-kursiv
					"'(^|\s)\*(?!\*)(\S+\*)+(?=(\s|$))'e",  // SL-fett
					"'(^|\s)_(?!_)(\S+_)+(?=(\s|$))'e",     // SL-unterstrichen
					"'(^|\s)#(?!#)(\S+#)+(?=(\s|$))'e",     // SL-diktengleich
					"'(^|\s)\+(?!\+)(\S+\+)+(?=(\s|$))'e",  // SL-groesser
					"'(^|\s)-(?!-)(\S+-)+(?=(\s|$))'e",     // SL-kleiner
					"'(^|\s)&gt;(?!&gt;)(\S+&gt;)+(?=(\s|$))'ie",  // SL-hochgestellt
					"'(^|\s)&lt;(?!&lt;)(\S+&lt;)+(?=(\s|$))'ie",  // SL-tiefgestellt
					"'%%(\S|\S.*?\S)%%'s",               // ML-kursiv
					"'\*\*(\S|\S.*?\S)\*\*'s",           // ML-fett
					"'__(\S|\S.*?\S)__'s",                     // ML-unterstrichen
					"'##(\S|\S.*?\S)##'s",                     // ML-diktengleich
					"'\+\+(((\+\+)*)(\S|\S.*?\S)\\2)\+\+'se", // ML-groesser
					"'--(((--)*)(\S|\S.*?\S)\\2)--'se",       // ML-kleiner
					"'&gt;&gt;(\S|\S.*?\S)&gt;&gt;'is",  // ML-hochgestellt
					"'&lt;&lt;(\S|\S.*?\S)&lt;&lt;'is",        // ML-tiefgestellt
					"'\n\n\t(((\n\n)\t)*(.+?))(\Z|\n\n(?!\t))'se",        // Absatz eingerueckt
					"'(\n|\A)((-([^\-]|[^\-].+?)(\n|\Z))+?)(\n|\Z)'se",            // Aufzaehlungsliste
					"'\[pre\](.+?)\[/pre\]'is"           // praeformatierter Text 
					);
	$replace = array("<hr noshade=\"noshade\" width=\"98%\" size=\"\\1\" align=\"center\" />",
					"'\\1<i>'.substr(str_replace('%', ' ', '\\2'), 0, -1).'</i>'",
					"'\\1<b>'.substr(str_replace('*', ' ', '\\2'), 0, -1).'</b>'",
					"'\\1<u>'.substr(str_replace('_', ' ', '\\2'), 0, -1).'</u>'",
					"'\\1<tt>'.substr(str_replace('#', ' ', '\\2'), 0, -1).'</tt>'",
					"'\\1<big>'.substr(str_replace('+', ' ', '\\2'), 0, -1).'</big>'",
					"'\\1<small>'.substr(str_replace('-', ' ', '\\2'), 0, -1).'</small>'",
					"'\\1<sup>'.substr(str_replace('&gt;', ' ', '\\2'), 0, -1).'</sup>'",
					"'\\1<sub>'.substr(str_replace('&lt;', ' ', '\\2'), 0, -1).'</sub>'",
					"<i>\\1</i>",
					"<b>\\1</b>",
					"<u>\\1</u>",
					"<tt>\\1</tt>",
					"'<big>'.format('\\1').'</big>'",
					"'<small>'.format('\\1').'</small>'",
					"<sup>\\1</sup>",
					"<sub>\\1</sub>",
					"'<blockquote>'.format('\\1').'</blockquote>'",
					"'<ul>'.preg_call_format('\\2').'</ul>'",
					"<pre>\\1</pre>"
					);
	$text = preg_replace($pattern, $replace, $text);
	
	return $text;
}

// Hilfsfunktion für format()
function preg_call_format($tbr){
	return preg_replace("'-(.+?)(\n(?=-)|\Z)'se", "'<li>\\1</li>'", $tbr);
}

// entfernt alle Schnellformatierungszeichen aus $text
// zurückgegeben wird reiner Text (für HTML-Ausgabe (Druckansicht)
// muss dieser noch durch nl2br() laufen
function kill_format($text){
	$text = preg_replace("'\n?\r\n?'", "\n", $text);
	$pattern = array("'(^|\s)%(?!%)(\S+%)+'e",     // SL-kursiv
					"'(^|\s)\*(?!\*)(\S+\*)+'e",  // SL-fett
					"'(^|\s)_(?!_)(\S+_)+'e",     // SL-unterstrichen
					"'(^|\s)#(?!#)(\S+#)+'e",     // SL-diktengleich
					"'(^|\s)\+(?!\+)(\S+\+)+'e",  // SL-groesser
					"'(^|\s)-(?!-)(\S+-)+'e",     // SL-kleiner
					"'(^|\s)>(?!>)(\S+>)+'e",  // SL-hochgestellt
					"'(^|\s)<(?!<)(\S+<)+'e",  // SL-tiefgestellt
					"'%%(\S|\S.*?\S)%%'s",              // ML-kursiv
					"'\*\*(\S|\S.*?\S)\*\*'s",          // ML-fett
					"'__(\S|\S.*?\S)__'s",              // ML-unterstrichen
					"'##(\S|\S.*?\S)##'s",              // ML-diktengleich
					"'\+\+(((\+\+)*)(\S|\S.*?\S)?\\2)\+\+'s",  // ML-groesser
					"'--(((--)*)(\S|\S.*?\S)?\\2)--'s",        // ML-kleiner
					"'>>(\S|\S.*?\S)>>'is",  // ML-hochgestellt
					"'<<(\S|\S.*?\S)<<'is",        // ML-tiefgestellt
					"'\n\n\t(((\n\n)\t)*(.+?))(\Z|\n\n(?!\t))'s",        // Absatz eingerueckt
					"'(?<=\n|^)--+(\d?)(\n|$|(?=<))'m",                                                                          // Trennlinie
					"'\n((-(.+?)(\n|\Z))+?)(\n|\Z)'s",            // Aufzaehlungsliste
					"'\[pre\](.+?)\[/pre\]'is" ,                                          // praeformatierter Text
					"'\[.+?\](((http://|https://|ftp://)?([^/\s]+)(.[^/\s]+){2,})|([-a-z0-9_]+(\.[_a-z0-9-]+)*@([a-z0-9-]+(\.[a-z0-9-]+)+)))'i",
					"'\[quote=.+?quote\]'is",
					"':[^\s]+?:'s"
					);
	$replace = array("'\\1'.substr(str_replace('%', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('*', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('_', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('#', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('+', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('-', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('&gt;', ' ', '\\2'), 0, -1)",
					"'\\1'.substr(str_replace('&lt;', ' ', '\\2'), 0, -1)",
					"\\1", "\\1", "\\1", "\\1", "\\1", "\\1",
					"\\1", "\\1", "\n\\1\n", "", "\n\\1\n", "\\1", "\\1", "", "");
	
	$text = preg_replace($pattern, $replace, $text);
	return $text;
}

//////////////////////////////////////////////////////////////////////////

function FixLinks($data = "", $fix_nl = TRUE, $nl_to_br = FALSE){
	if(empty($data)){
		return $data;
	}
	if($fix_nl === TRUE)
		$data = preg_replace("/\n?\r\n?/", "\n", $data); // newline fixen
	//$lines = explode("\n", $data);
	
	$pattern = array("/([ \t\]\n]|^)www\./i", "/([ \t\]\n]|^)ftp\./i");
	$replace = array("\\1http://www.", "\\1ftp://ftp.");
	$fixed_text = preg_replace($pattern, $replace, $data);
	
	$pattern = array("'(\[([^\n\f\]]+)\])?(((http://)|(https://)|(ftp://([_a-z0-9-]+@)?))[_a-z0-9-]+(\.[_a-z0-9-]+)+(/[_a-z0-9-~]*)*\.?[_a-z0-9-\?\&\=\;]*)'ie",
					"'(?<=\s)(\[([^\n\f\]]+)\])?([-a-z0-9_]+(\.[_a-z0-9-]+)*@([_a-z0-9-]+(\.[_a-z0-9-]+)+))'ie");
	$replace = array("preg_call_link('\\2', '\\3', 'LINK')", "preg_call_link('\\2', '\\3', 'MAIL')");
	$fixed_text = preg_replace($pattern, $replace, $fixed_text);
	
	if($nl_to_br === TRUE)
		$fixed_text = str_replace("\n", "<br />", $fixed_text);
	
	return $fixed_text;
}

// Hilfsfunktion für FixLinks()
function preg_call_link($name, $link, $mod){
	if($mod == "LINK"){
		if($name == "")
			$name = $link;
		$link = str_replace("&amp;", "&", $link);
		$tbr = "<a href=\"$link\" target=\"_blank\">$name</a>";
	}
	else{
		if($name != "")
			$tbr = "<a href=\"mailto:$link\">$name</a>";
		else
			$tbr = "<a href=\"mailto:$link\">$link</a>";
	}
	return $tbr;
}

//////////////////////////////////////////////////////////////////////////////////////////

function smile ($text= "") {
	global $SMILE_SHORT, $SMILE_PATH, $CANONICAL_RELATIVE_PATH_STUDIP;
	if(empty($text)) {
		return $text;
	}
	$text=preg_replace("'(\>|^|\s):([_a-zA-Z][_a-z0-9A-Z-]*):($|\<|\s)'m","\\1<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\"><img alt=\"\\2\" title=\"\\2\" border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP$SMILE_PATH/\\2.gif\"></a>\\3",$text);
	reset($SMILE_SHORT);
	WHILE (list($key,$value) = each($SMILE_SHORT)) {
		$text=str_replace($key,"<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\"><img ".tooltip($value)." border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP$SMILE_PATH/$value.gif\"></a>",$text);
	}
	return $text;
}


//Beschneidungsfunktion fuer alle printhead Ausgaben
function mila ($titel,$size=60){
	global $auth;

	if ($auth->auth["jscript"] AND $size==60) {
		if (strlen ($titel) >$auth->auth["xres"] / 13)        //hier wird die maximale Laenge berechnet, nach der Abgeschnitten wird (JS dynamisch)
			$titel=substr($titel, 0, $auth->auth["xres"] / 13)."... ";
		}
	else {
		if (strlen ($titel) >$size) 
			$titel=substr($titel, 0, $size)."... ";
	}
	return $titel;
}

//Ausgabe der Aufklapp-Kopfzeile
function printhead($breite,$left,$link,$open,$new,$icon,$titel,$zusatz,$timestmp=0) {

		if ($timestmp==0) {
			$timecolor = "#BBBBBB";
		} else {
			$timediff = log((time()-$timestmp)/86400 + 1) * 15;
			if ($timediff >= 68) {
				$timediff = 68;
			}
			$red = dechex(255-$timediff);
			$other = dechex(119+$timediff);
			$timecolor= "#".$red.$other.$other;
		}

	if ($open=="close") {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead2\" nowrap width=\"1%\" align=left valign=\"bottom\">";
	} else {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead3\" nowrap width=\"1%\" align=left valign=\"bottom\">";
	}

	if ($link) {
		$print.= "<a href=\"".$link."\">";
	}
	$print.="&nbsp;<img src=\"";
	if ($open=="open") {
		$titel = "<b>".$titel."</b>";
	}
	if ($link) {
		if ($open=="close" AND $new!=TRUE) {
			$print.="pictures/forumgrau2.gif\" alt=\"Objekt aufklappen\"";
		}
		if ($open=="open" AND $new!=TRUE) {
			$print.="pictures/forumgraurunt2.gif\" alt=\"Objekt zuklappen\"";
		}
		if ($open=="close" AND $new==TRUE) {
			$print.="pictures/forumrot.gif\" alt=\"Objekt aufklappen\"";
		}
		if ($open=="open" AND $new==TRUE) {
			$print.="pictures/forumrotrunt.gif\" alt=\"Objekt zuklappen\"";
		}
	} else {
		if ($open=="close") {
			if (!$new) {
				$print.="pictures/forumgrau2.gif\"";
			}
			if ($new) { 
				$print.="pictures/forumrot.gif\"";
			}
		} else {
			if (!$new) {
				$print.="pictures/forumgraurunt2.gif\"";
			}
			if ($new) {
				$print.="pictures/forumrotrunt.gif\"";
			}
		}
	}
	
	$print .=" border=0>";
	if ($link) {
		$print.= "</a>";
	}
	$print.="</td><td class=\"printhead\" nowrap width=\"1%\" valign=\"middle\">".$icon."</td>"."<td class=\"printhead\" align=\"left\" width=\"20%\" nowrap valign=\"bottom\">&nbsp;".$titel."</td>"."<td align=\"right\" class=\"printhead\" width=\"99%\" valign='bottom'>".$zusatz."&nbsp;</td>";
	echo $print;
}

//Ausgabe des Contents einer aufgeklappten Kopfzeile
function printcontent ($breite,$write=FALSE,$inhalt,$edit) {

	$print.= "<td class=\"printcontent\" width=22>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td class=\"printcontent\" width=\"$breite\"><br>";
	$print .= $inhalt;
	if ($edit) {
		$print.= "<br><br><div align=\"center\">".$edit."</div>";
	} else {
		$print.= "<br>";
	}
	$print.="</td>";
	echo $print;
}


/*****************************************************************************
print_infobox, baut einen Info-Kasten aus folgenden Elementen zusammen: Bild (separat uebergeben), Ueberschriften, Icons, Inhalt (in Array).
Der Aufruf des Bildes ist optional.
Beispielaufbau f&uuml;r das Array:

$infobox = array	(	
array  ("kategorie"  => "Information:",
		"eintrag" => array	(	
						array	 (	"icon" => "pictures/suchen.gif",
								"text"  => "Um weitere Veranstaltungen bitte Blabla"
								),
						array	 (	"icon" => "pictures/admin.gif",
								"text"  => "um Verwaltung  Veranstaltungen bitte Blabla"
								)
		)
	),
array  ("kategorie" => "Aktionen:",
		   "eintrag" => array	(	
						array (	"icon" => "pictures/ausruf_small.gif",
								"text"  => "es sind noch 19 Veranstaltungen vorhanden."
								)
		)
	)
);
/*****************************************************************************/

function print_infobox ($content, $picture="") {

$print = "<table align=\"center\" width=\"250\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
IF ($picture!="") {
	$print .= "<tr>
				<td class=\"blank\" width=\"100%\" align=\"right\">
					<img src=\"".$picture."\">
				</td>
			</tr>";
		}
	$print .= "<tr>
				<td class=\"angemeldet\" width=\"100%\">
					<table align=\"center\" width=\"99%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\">";
for ($i = 0; $i < count($content); $i++) { $print .= "
						<tr>
							<td class=\"blank\" width=\"100%\" colspan=\"2\">
								<font size=\"-1\"><b>".$content[$i]["kategorie"]."</b></font>
								<br>
							</td>
						</tr>";
	for ($j = 0; $j < count($content[$i]["eintrag"]); $j++) { $print .= "
						<tr>
							<td class=\"blank\" width=\"1%\" valign=\"top\">
								<img src=\"".$content[$i]["eintrag"][$j]["icon"]."\">
							</td>
							<td class=\"blank\" width=\"99%\">
								<font size=\"-1\">".$content[$i]["eintrag"][$j]["text"]."</font><br>
							</td>
						</tr>";
	}
}
$print .= "
					</table>
				</td>
			</tr>
		</table>";

echo $print;
}





/**
* Returns a given text as html tooltip
*
* title and alt attribute is default, with_popup means a JS alert box activated on click
* @access        public        
* @param        string $text        
* @param        boolean        $with_alt        return text with alt attribute
* @param        boolean $with_popup        return text with JS alert box on click
* @return        string
*/
function tooltip($text,$with_alt = TRUE,$with_popup = FALSE){
	$ret = "";
	if ($with_popup)
		$ret = " onClick=\"alert('".JSReady($text,"alert")."');\"";
	$text = htmlReady($text);
	if ($with_alt)
		$ret .= " alt=\"$text\"";
	$ret .= " title=\"$text\" ";
	return $ret;
}
?>
