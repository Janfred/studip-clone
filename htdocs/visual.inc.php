<?

require_once($ABSOLUTE_PATH_STUDIP."config.inc.php");
require_once($ABSOLUTE_PATH_STUDIP."cssClassSwitcher.inc.php");


/*****************************************************************************
get_ampel_write, waehlt die geeignete Grafik in der Ampel Ansicht 
(fuer Berechtigungen) aus. Benoetigt den Status in der Veranstaltung
und auf der Anmeldeliste und den read_level der Veranstaltung
/*****************************************************************************/

function get_ampel_write ($mein_status, $admission_status, $write_level, $print="TRUE") {
	global $perm;
	
	if ($mein_status == "dozent" || $mein_status == "tutor" || $mein_status == "autor") { // in den Fällen darf ich auf jeden Fall schreiben
		$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
	} else {
		switch($write_level){
			case 0 : //Schreiben darf jeder
				$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
			break;
			case 1 : //Schreiben duerfen nur registrierte Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Registrierungsmail beachten!)") . "</font>";
			break;
			case 2 : //Schreiben nur mit Passwort
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(mit Passwort)") . "</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Registrierungsmail beachten!)") . "</font>";
			break;
			case 3 : //Schreiben nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Anmelde-/Warteliste)") . "</font>";
					else
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Anmeldeverfahren)") . "</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Registrierungsmail beachten!)") . "</font>";
			break;
		}
	}
	if ($print==TRUE) {
		echo $ampel_status;
	}
	return $ampel_status;
}

/*****************************************************************************
get_ampel_read, waehlt die geeignete Grafik in der Ampel Ansicht 
(fuer Berechtigungen) aus. Benoetigt den Status in der Veranstaltung
und auf der Anmeldeliste und den read_level der Veranstaltung
/*****************************************************************************/

function get_ampel_read ($mein_status, $admission_status, $read_level, $print="TRUE") {
	global $perm;

	if ($mein_status) { // wenn ich im Seminar schon drin bin, darf ich auf jeden Fall lesen
		$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
	} else {
		switch($read_level){
			case 0 : //Lesen darf jeder
				$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
			break;
			case 1 : //Lesen duerfen registrierte nur Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Registrierungsmail beachten!)") . "</font>";
			break; //Lesen nur mit Passwort
			case 2 :
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(mit Passwort)") . "</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Registrierungsmail beachten!)") . "</font>";
			break;
			case 3 : //Lesen nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Anmelde-/Warteliste)") . "</font>";
					else
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Anmeldeverfahren)") . "</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>" . _("(Registrierungsmail beachten!)") . "</font>";
			break;
		}
	}
	if ($print==TRUE) {
		echo $ampel_status;
	}
	return $ampel_status;
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

	case "contact" :
		$what = htmlentities($what,ENT_COMPAT);
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
		$what = symbol($what);		
		$what = str_replace("\n","<br /> ",$what);
		if (ereg("\[quote",$what) AND ereg("\[/quote\]",$what))
			$what = quotes_decode($what);
		$what = "<p width=\"100%\"class=\"printcontent\">" . $what . "</p>";
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
// Funktion um Quotings zu decoden
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
						$between_tags = "<b>".sprintf(_("%s hat geschrieben:"),$quote_name)."</b><hr>".$between_tags;
						}
					ELSE { // kein Name, also nur Zitat
						$nameend_pos = strpos($between_tags,"]");
						IF (substr($between_tags,$nameend_pos,5)=="]<br>") // ja, hier wurde anstaendig gequotet
							$between_tags = "<b>"._("Zitat:")."</b><hr>".substr($between_tags,$nameend_pos+6);
						ELSE // da wird gepfuscht, also mal besser Finger weg
							$between_tags = "<b>"._("Zitat:")."</b><hr>".substr($between_tags,$nameend_pos+1);
						}
					$description = $before_start_tag . "<blockquote class=\"quote\">";
					$description .= $between_tags . "</blockquote>";
					if (substr($after_end_tag,0,6)=="<br />") {
						$after_end_tag = substr($after_end_tag,6);
					}
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


/**
* universal an very usable functions to get all the special stud.ip formattings
*
* 
* @access       public        
* @param        string $what		what to format
* @param        string $trim		should the output trimmed?
* @return       string
*/
function formatReady($what, $trim = TRUE){
	return symbol(smile(FixLinks(format(latex(htmlReady($what, $trim, FALSE))), FALSE)));
}


/**
* the special version of formatReady for Wiki-Webs
*
* 
* @access       public        
* @param        string $what		what to format
* @param        string $trim		should the output trimmed?
* @return       string
*/
function wikiReady($what, $trim = TRUE){
	return symbol(smile(FixLinks(wiki_format(format(latex(htmlReady($what, $trim, FALSE))), FALSE))));
}


/**
* a special wiki formatting routine (unused the moment)
*
* 
* @access       public        
* @param        string $text		what to format
*/
function wiki_format($text) {
	return $text;
}

////////////////////////////////////////////////////////////////////////////////

function latex($text) {
	global $ABSOLUTE_PATH_STUDIP,$CANONICAL_RELATIVE_PATH_STUDIP,$TEXCACHE_PATH,$LATEXRENDER_ENABLE;
	global $LATEX_PATH,$DVIPS_PATH,$CONVERT_PATH,$IDENTIFY_PATH,$TMP_PATH;
	
	if ($LATEXRENDER_ENABLE) {
		include_once($ABSOLUTE_PATH_STUDIP."/lib/classes/latexrender.class.php");
		$latex = new LatexRender($ABSOLUTE_PATH_STUDIP.$TEXCACHE_PATH,$CANONICAL_RELATIVE_PATH_STUDIP.$TEXCACHE_PATH);
		$latex->_latex_path = $LATEX_PATH;
		$latex->_dvips_path = $DVIPS_PATH;
		$latex->_convert_path = $CONVERT_PATH;
		$latex->_identify_path = $IDENTIFY_PATH;
		$latex->_tmp_dir = $TMP_PATH;
		
		preg_match_all("#\[tex\](.*?)\[/tex\]#si",$text,$tex_matches);
		
		for ($i=0; $i < count($tex_matches[0]); $i++) {
			$pos = strpos($text, $tex_matches[0][$i]);
			$latex_formula = decodeHTML($tex_matches[1][$i]);
			
			$url = $latex->getFormulaURL($latex_formula);
			
			if ($url != false) {
				$text = substr_replace($text, "<img src='".$url."'>",$pos,strlen($tex_matches[0][$i]));
			} else {
				$text = substr_replace($text, "[unparseable or potentially dangerous latex formula]",$pos,strlen($tex_matches[0][$i]));
			}
		}	
	}
	return $text;
}

/**
* decodes html entities to normal characters
*
* @access	public
* @param	string
* @return	string
*/
function decodeHTML($string) {
	$string = strtr($string, array_flip(get_html_translation_table(HTML_ENTITIES,ENT_QUOTES)));
	$string = preg_replace("/&#([0-9]+);/me", "chr('\\1')", $string);
	return $string;
}

// ermöglicht einfache Formatierungen in Benutzereingaben

function format ($text) {
	$text = preg_replace("'\n?\r\n?'", "\n", $text);
	
	$pattern = array("'(^|\n)\!([^!].*)$'m",     // Überschrift 4. Stufe
					"'(^|\n)\!{2}([^!].*)$'m",           // Überschrift 3. Stufe
					"'(^|\n)\!{3}([^!].*)$'m",           // Überschrift 2. Stufe
					"'(^|\n)\!{4}([^!].*)$'m",           // Überschrift 1. Stufe
					"'(^|\n)--+(\d?)(\n|$)'m",           // Trennlinie
					"'(\n|\A)(((-|=)+.*(\n|\Z))+)'e"
					);
	$replace = array("<h4>\\2 </h4>",
					"<h3> \\2 </h3>",
					"<h2> \\2 </h2>",
					"<h1> \\2 </h1>",
					"<hr noshade=\"noshade\" width=\"98%\" size=\"\\1\" align=\"center\" />",
					"preg_call_format_list('\\2')"
					);
	$text = preg_replace($pattern, $replace, $text);
	$text = preg_replace("'(\</h.\>)\n'", "\\1", $text);
	
	$pattern = array("'\[pre\](.+?)\[/pre\]'is",    // praeformatierter Text
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
					"'\+\+(((\+\+)*)(\S|\S.*?\S)\\2)\+\+'se",  // ML-groesser
					"'--(((--)*)(\S|\S.*?\S)\\2)--'se",        // ML-kleiner
					"'&gt;&gt;(\S|\S.*?\S)&gt;&gt;'is",     // ML-hochgestellt
					"'&lt;&lt;(\S|\S.*?\S)&lt;&lt;'is",     // ML-tiefgestellt
					"'\n\n  (((\n\n)  )*(.+?))(\Z|\n\n(?! ))'se"        // Absatz eingerueckt
					);
	$replace = array("<pre>\\1</pre>",
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
					"'<blockquote>'.format('\\1').'</blockquote>'"
					);
	$text = preg_replace($pattern, $replace, $text);
	
	return $text;
}

// Hilfsfunktion für format()
function preg_call_format_list ($content_str) {
	$current_level = 0;
	$closed_level[0] = TRUE;
	
	$lines = explode("\n", $content_str);
	foreach ($lines as $line) {
		if (preg_match("'^((-|=)+)\s*(.*)$'", $line, $matches)) {
			$level = strlen($matches[1]);
			if ($matches[1]{0} == "-")
				$tag = "ul";
			else
				$tag = "ol";
		
			if ($level > $current_level) {
				while ($level > $current_level) {
					$ret .= "<$tag><li>";
					$closed_level[$current_level++] = FALSE;
				}
				$ret .= $matches[3];
				$closed_level[$current_level] = FALSE;
			}
			else if ($level == $current_level) {
				if (!$closed_level[$current_level]) {
					$ret .= "</li>";
					$closed_level[$current_level] = TRUE;
				}
				$ret .= "<li>{$matches[3]}</li>";
			}
			else if ($level < $current_level) {
				while ($level < $current_level) {
					$ret .= "</li></$tag>";
					$closed_level[$current_level--] = TRUE;
				}
				$ret .= "<li>$matches[3]";
				$closed_level[$current_level] = FALSE;
			}
		}
	}
	
	foreach ($closed_level as $closed) {
		if (!$closed)
			$ret .= "</$tag>";
	}
	
	return $ret;
}

// entfernt alle Schnellformatierungszeichen aus $text
// zurückgegeben wird reiner Text (für HTML-Ausgabe (Druckansicht)
// muss dieser noch durch nl2br() laufen
function kill_format ($text) {
	$text = preg_replace("'\n?\r\n?'", "\n", $text);
	$pattern = array("'(^|\s)%(?!%)(\S+%)+'e",     // SL-kursiv
					"'(^|\s)\*(?!\*)(\S+\*)+'e",  // SL-fett
					"'(^|\s)_(?!_)(\S+_)+'e",     // SL-unterstrichen
					"'(^|\s)#(?!#)(\S+#)+'e",     // SL-diktengleich
					"'(^|\s)\+(?!\+)(\S+\+)+'e",  // SL-groesser
					"'(^|\s)-(?!-)(\S+-)+'e",     // SL-kleiner
					"'(^|\s)>(?!>)(\S+>)+'e",     // SL-hochgestellt
					"'(^|\s)<(?!<)(\S+<)+'e",     // SL-tiefgestellt
					"'%%(\S|\S.*?\S)%%'s",        // ML-kursiv
					"'\*\*(\S|\S.*?\S)\*\*'s",    // ML-fett
					"'__(\S|\S.*?\S)__'s",        // ML-unterstrichen
					"'##(\S|\S.*?\S)##'s",        // ML-diktengleich
					"'\+\+(((\+\+)*)(\S|\S.*?\S)?\\2)\+\+'s",  // ML-groesser
					"'--(((--)*)(\S|\S.*?\S)?\\2)--'s",        // ML-kleiner
					"'>>(\S|\S.*?\S)>>'is",  // ML-hochgestellt
					"'<<(\S|\S.*?\S)<<'is",  // ML-tiefgestellt
					"'\n\n\t(((\n\n)\t)*(.+?))(\Z|\n\n(?!\t))'s",  // Absatz eingerueckt
					"'(?<=\n|^)--+(\d?)(\n|$|(?=<))'m",                                                                          // Trennlinie
					"'\n((-(.+?)(\n|\Z))+?)(\n|\Z)'s",  // Aufzaehlungsliste
					"'\[pre\](.+?)\[/pre\]'is" ,        // praeformatierter Text
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

function FixLinks($data = "", $fix_nl = TRUE, $nl_to_br = TRUE) {
	if (empty($data)) {
		return $data;
	}
	if ($fix_nl)
		$data = preg_replace("/\n?\r\n?/", "\n", $data); // newline fixen
	
	$pattern = array("/([ \t\]\n]|^)www\./i", "/([ \t\]\n]|^)ftp\./i");
	$replace = array("\\1http://www.", "\\1ftp://ftp.");
	$fixed_text = preg_replace($pattern, $replace, $data);
	
	$pattern = array("'(\[([^\n\f\[]+)\])?(((https?://)|(ftp://([_a-z0-9-:]+@)?))[_a-z0-9-]+(\.[_a-z0-9-]+)+(/[^<\s]*[^\.\s<])*)'ie",
					"'(?<=\s|^)(\[([^\n\f\[]+)\])?([-a-z0-9_]+(\.[_a-z0-9-]+)*@([_a-z0-9-]+(\.[_a-z0-9-]+)+))'ie");
	$replace = array("preg_call_link('\\2', '\\3', 'LINK')", "preg_call_link('\\2', '\\3', 'MAIL')");
	$fixed_text = preg_replace($pattern, $replace, $fixed_text);
	
	if ($nl_to_br)
		$fixed_text = str_replace("\n", "<br />", $fixed_text);
	
	return $fixed_text;
}

// Hilfsfunktion für FixLinks()
function preg_call_link($name, $link, $mod) {
	if ($mod == "LINK") {
		if ($name == "")
			$name = $link;
		$link = str_replace("&amp;", "&", $link);
		$tbr = "<a href=\"$link\" target=\"_blank\">$name</a>";
	}
	else {
		if ($name != "")
			$tbr = "<a href=\"mailto:$link\">$name</a>";
		else
			$tbr = "<a href=\"mailto:$link\">$link</a>";
	}
	return $tbr;
}

/**
* create smileys
*
* This functions converts the smileys codes (":name:") notation an the shorts, 
* located in the config.inc into the assigned pictures. 
* On every smiley a link to show_smiley.php overview is given (only if $extern
* is FALSE). A tooltip which shows the smiley code is given, too.
*
* @access	public        
* @param		string	the text to convert
* @param		boolean	TRUE if function is called from extern pages
* @return		string	convertet text
*/
function smile ($text = "", $extern = FALSE) {
	global $SMILE_SHORT, $SMILE_PATH, $CANONICAL_RELATIVE_PATH_STUDIP;
	
	if(empty($text))
		return $text;
	
	//smileys in the ":name:" notation
	$pattern = "'(\>|^|\s):([_a-zA-Z][_a-z0-9A-Z-]*):($|\<|\s)'m";
	$replace = "\\1";
	if (!$extern) {
		$replace .= "<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\">";
		$replace .= "<img alt=\"\\2\" title=\"\\2\" border=\"0\" src=\"";
		$replace .= $CANONICAL_RELATIVE_PATH_STUDIP . $SMILE_PATH . "/\\2.gif\"></a>\\3";
	}
	else {
		$replace .= "<img alt=\"\\2\" title=\"\\2\" border=\"0\" src=\"";
		$replace .= "http://$SERVER_NAME/$SMILE_PATH/\\2.gif\"></a>\\3";
	}
	$text = preg_replace($pattern, $replace, $text);
	
	//smileys in short notation
	reset($SMILE_SHORT);
	while (list($key,$value) = each($SMILE_SHORT)) {
		$text = str_replace($key,"<a href=\""
				. $CANONICAL_RELATIVE_PATH_STUDIP . "show_smiley.php\" target=\"_blank\">"
				. "<img ".tooltip($value)." border=\"0\" src=\""
				. $CANONICAL_RELATIVE_PATH_STUDIP . $SMILE_PATH . "/$value.gif\"></a>",$text);
	}
	return $text;
}


/**
* create symbols from the shorts
*
* This functions converts the short, locatet in the config.inc
* into the assigned pictures. It uses a different directory
* as the smile-function, becauso symbols should not be shown in
* the smiley and so, no link is given onto the picture. A tooltip which 
* shows the symbol code is given, too.
*
* @access	public        
* @param		string	the text to convert
* @return		string	convertet text
*/
function symbol ($text = "") {
	global $SYMBOL_SHORT, $SYMBOL_PATH, $CANONICAL_RELATIVE_PATH_STUDIP;
	
	if(empty($text))
		return $text;

	//symbols in short notation
	reset($SYMBOL_SHORT);
	while (list($key, $value) = each($SYMBOL_SHORT)) {
		$text=str_replace($key,"<img ".tooltip($key)." border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP$SYMBOL_PATH/$value.gif\">",$text);
	}
	
	return $text;
}

//Beschneidungsfunktion fuer alle printhead Ausgaben
function mila ($titel, $size = 60) {
	global $auth;

	if ($auth->auth["jscript"] AND $size == 60) {
		//hier wird die maximale Laenge berechnet, nach der Abgeschnitten wird (JS dynamisch)
		if (strlen ($titel) >$auth->auth["xres"] / 13)
			$titel=substr($titel, 0, $auth->auth["xres"] / 13)."... ";
	}
	else {
		if (strlen ($titel) >$size) 
			$titel=substr($titel, 0, $size)."... ";
	}
	return $titel;
}

//Ausgabe der Aufklapp-Kopfzeile
function printhead ($breite, $left, $link, $open, $new, $icon,
		$titel, $zusatz, $timestmp = 0, $printout = TRUE) {

		if ($timestmp == 0)
			$timecolor = "#BBBBBB";
		else {
			$timediff = (int) log((time() - $timestmp) / 86400 + 1) * 15;
			if ($timediff >= 68)
				$timediff = 68;
			
			$red = dechex(255 - $timediff);
			$other = dechex(119 + $timediff);
			$timecolor= "#" . $red . $other . $other;
		}

	if ($open == "close") {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead2\" nowrap width=\"1%\"";
		$print .= "align=left valign=\"top\">";
	}
	else {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead3\" nowrap width=\"1%\"";
		$print .= " align=left valign=\"top\">";
	}

	if ($link)
		$print .= "<a href=\"".$link."\">";
	
	$print .= "&nbsp;<img src=\"";
	if ($open == "open")
		$titel = "<b>" . $titel . "</b>";
	
	if ($link) {
		if ($open == "close" AND $new != TRUE)
			$print .= "pictures/forumgrau2.gif\"" . tooltip(_("Objekt aufklappen"));
	
		if ($open == "open" AND $new != TRUE)
			$print .= "pictures/forumgraurunt2.gif\"" . tooltip(_("Objekt zuklappen"));
		
		if ($open == "close" AND $new == TRUE)
			$print .= "pictures/forumrot.gif\"" . tooltip(_("Objekt aufklappen"));
		
		if ($open == "open" AND $new == TRUE)
			$print .= "pictures/forumrotrunt.gif\"" . tooltip(_("Objekt zuklappen"));
		
	}
	else {
		if ($open == "close") {
			if (!$new)
				$print .= "pictures/forumgrau2.gif\"";
			
			if ($new)
				$print .= "pictures/forumrot.gif\"";
		}
		else {
			if (!$new)
				$print .= "pictures/forumgraurunt2.gif\"";
			
			if ($new)
				$print .= "pictures/forumrotrunt.gif\"";
		}
	}
	
	$print .= " border=0>";
	if ($link) {
		$print .= "</a>";
	}
	$print .= "</td><td class=\"printhead\" nowrap width=\"1%\" valign=\"middle\">$icon</td>";
	$print .= "<td class=\"printhead\" align=\"left\" width=\"20%\" nowrap valign=\"bottom\">&nbsp;";
	$print .= $titel."</td>"."<td align=\"right\" class=\"printhead\" width=\"99%\" valign='bottom'>";
	$print .= $zusatz."&nbsp;</td>";
	
	if ($printout)
		echo $print;
	else
		return $print;
}

//Ausgabe des Contents einer aufgeklappten Kopfzeile
function printcontent ($breite, $write = FALSE, $inhalt, $edit, $printout = TRUE) {

	$print = "<td class=\"printcontent\" width=\"22\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	$print .= "</td><td class=\"printcontent\" width=\"$breite\"><br>";
	$print .= $inhalt;
	
	if ($edit)
		$print .= "<br><br><div align=\"center\">$edit</div><img src=\"pictures/blank.gif\" height=\"6\" border=\"0\">";
	else
		$print .= "<br>";
	
	$print .= "</td>";
	
	if ($printout)
		echo $print;
	else
		return $print;
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
	global $CANONICAL_RELATIVE_PATH_STUDIP;
	
	$print = "<table align=\"center\" width=\"250\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
	
	if ($picture) {
		$print .= "<tr>
					<td class=\"blank\" width=\"100%\" align=\"right\">
						<img src=\"".$CANONICAL_RELATIVE_PATH_STUDIP . $picture."\">
					</td>
				</tr>";
	}
	
	$print .= "<tr>
					<td class=\"angemeldet\" width=\"100%\">
						<table background=\"".$CANONICAL_RELATIVE_PATH_STUDIP."pictures/white.gif\" align=\"center\" width=\"99%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\">";
						
	for ($i = 0; $i < count($content); $i++)
		if ($content[$i]) { 
			$print .= "
							<tr>
								<td class=\"blank\" width=\"100%\" colspan=\"2\">
									<font size=\"-1\"><b>".$content[$i]["kategorie"]."</b></font>
									<br>
								</td>
							</tr>";
			for ($j = 0; $j < count($content[$i]["eintrag"]); $j++)  {
				$print .= "
							<tr>
								<td class=\"blank\" width=\"1%\" align=\"center\" valign=\"top\">
									<img src=\"".$CANONICAL_RELATIVE_PATH_STUDIP . $content[$i]["eintrag"][$j]["icon"]."\">
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


/**
* Returns a an entry in the top navigation bar
*
* 
* @access        public        
* @param        string $icon       	 Path to the icon
* @param        string $URL		URL on button
* @param        string $text		Hovertext under the Button
* @param        string $tooltip		for Tooltip Window
* @param        integer $size		Width of the Element
* @param        string $target		same or new window...
* @param        string $align		
* @param        string $toolwindow	For a special Toolwindow
* @return        string
*/
function MakeToolbar($icon,$URL,$text,$tooltip,$size,$target="_top",$align="center",$toolwindow="FALSE")
{
	if ($toolwindow == "FALSE") {
		$tool = tooltip($tooltip);
	} else {
		$tool = tooltip($tooltip,TRUE,TRUE);
	}
	$toolbar = "<td class=\"toolbar\" align=\"$align\">";

	$toolbar .= "<img border=\"0\" src=\"pictures/blank.gif\" height=\"1\" width=\"45\"><br>"
			  ."<a class=\"toolbar\" href=\"$URL\" target=\"$target\"><img border=\"0\" src=\"$icon\" ".$tool."><br>"
			  ."<img border=\"0\" src=\"pictures/blank.gif\" height=\"6\" width=\"$size\"><br>"
			  ."<b><font size=\"2\">".$text."</font></b></a><br>"
			  ."<img border=\"0\" src=\"pictures/blank.gif\" height=\"4\" width=\"30\">";
	$toolbar .= "</td>\n";
	return $toolbar;
}
?>
