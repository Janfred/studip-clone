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
			case 1 : //Schreiben duerfen registrierte nur Stud.IP Teilnehmer
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gruen.gif\" width=\"11\" height=\"16\">";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
			break;
			case 2 : //Schreiben nur mit Passwort
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(mit Passwort)</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
			break;
			case 3 : //Schreiben nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmelde-/Warteliste)</font>";
					else
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmeldeverfahren)</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
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
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten!)</font>";
			break; //Lesen nur mit Passwort
			case 2 :
				if ($perm->have_perm("autor"))
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(mit Passwort)</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten!)</font>";
			break;
			case 3 : //Lesen nur nach Anmeldeverfaren
				if ($perm->have_perm("autor"))
					if ($admission_status)
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmelde-/Warteliste)</font>";
					else
						$ampel_status="<img border=\"0\" src=\"pictures/ampel_gelb.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Anmeldeverfahren)</font>";
				else
					$ampel_status="<img border=\"0\" src=\"pictures/ampel_rot.gif\" width=\"11\" height=\"16\">&nbsp;<font size=-1>(Registrierungsmail beachten)</font>";
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
		$what = symbol($what);		
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

////////////////////////////////////////////////////////////////////////////////

function formatReady($what, $trim = TRUE){
	return symbol(smile(FixLinks(format(htmlReady($what, $trim, FALSE)), FALSE)));
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
					"'\n\n  (((\n\n)  )*(.+?))(\Z|\n\n(?! ))'se",        // Absatz eingerueckt
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
	
	$pattern = array("'(\[([^\n\f\[]+)\])?(((https?://)|(ftp://([_a-z0-9-:]+@)?))[_a-z0-9-]+(\.[_a-z0-9-]+)+(/[^<\s]*[^\.\s])*)'ie",
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
* On every smiley a link to show_smiley.php overview is given. A tooltip which 
* shows the smiley code is given, too.
*
* @access	public        
* @param		string	the text to convert
* @return		string	convertet text
*/
function smile ($text= "") {
	global $SMILE_SHORT, $SMILE_PATH, $CANONICAL_RELATIVE_PATH_STUDIP;
	if(empty($text)) {
		return $text;
	}
	//smileys in the ":name:" notation
	$text=preg_replace("'(\>|^|\s):([_a-zA-Z][_a-z0-9A-Z-]*):($|\<|\s)'m","\\1<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\"><img alt=\"\\2\" title=\"\\2\" border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP$SMILE_PATH/\\2.gif\"></a>\\3",$text);
	
	//smileys in short notation
	reset($SMILE_SHORT);
	WHILE (list($key,$value) = each($SMILE_SHORT)) {
		$text=str_replace($key,"<a href=\"{$CANONICAL_RELATIVE_PATH_STUDIP}show_smiley.php\" target=\"_blank\"><img ".tooltip($value)." border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP$SMILE_PATH/$value.gif\"></a>",$text);
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
function symbol ($text= "") {
	global $SYMBOL_SHORT, $SYMBOL_PATH, $CANONICAL_RELATIVE_PATH_STUDIP;
	if(empty($text)) {
		return $text;
	}

	//symbols in short notation
	reset($SYMBOL_SHORT);
	WHILE (list($key,$value) = each($SYMBOL_SHORT)) {
		$text=str_replace($key,"<img ".tooltip($key)." border=\"0\" src=\"$CANONICAL_RELATIVE_PATH_STUDIP$SYMBOL_PATH/$value.gif\">",$text);
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
			$timediff = (int) log((time()-$timestmp)/86400 + 1) * 15;
			if ($timediff >= 68) {
				$timediff = 68;
			}
			$red = dechex(255-$timediff);
			$other = dechex(119+$timediff);
			$timecolor= "#".$red.$other.$other;
		}

	if ($open=="close") {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead2\" nowrap width=\"1%\" align=left valign=\"top\">";
	} else {
		$print = "<td bgcolor=\"".$timecolor."\" class=\"printhead3\" nowrap width=\"1%\" align=left valign=\"top\">";
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
	global $CANONICAL_RELATIVE_PATH_STUDIP;
$print = "<table align=\"center\" width=\"250\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">";
IF ($picture!="") {
	$print .= "<tr>
				<td class=\"blank\" width=\"100%\" align=\"right\">
					<img src=\"".$CANONICAL_RELATIVE_PATH_STUDIP.$picture."\">
				</td>
			</tr>";
		}
	$print .= "<tr>
				<td class=\"angemeldet\" width=\"100%\">
					<table background=\"".$CANONICAL_RELATIVE_PATH_STUDIP."pictures/white.gif\" align=\"center\" width=\"99%\" border=\"0\" cellpadding=\"4\" cellspacing=\"0\">";
for ($i = 0; $i < count($content); $i++) { $print .= "
						<tr>
							<td class=\"blank\" width=\"100%\" colspan=\"2\">
								<font size=\"-1\"><b>".$content[$i]["kategorie"]."</b></font>
								<br>
							</td>
						</tr>";
	for ($j = 0; $j < count($content[$i]["eintrag"]); $j++) { $print .= "
						<tr>
							<td class=\"blank\" width=\"1%\" align=\"center\" valign=\"top\">
								<img src=\"".$CANONICAL_RELATIVE_PATH_STUDIP.$content[$i]["eintrag"][$j]["icon"]."\">
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