<?

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "config.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
		. "/lib/extern_functions.inc.php");
global $_fullname_sql;

$attr_subheadline_td = preg_replace('/width\="[^"]+"/i',
		$this->config->getAttributes("TableParagraphSubHeadline", "td"),
		$this->config->getValue("TableParagraph", "margin"));


$aliases_content = $this->config->getValue("Main", "aliases");
$visible_content = $this->config->getValue("Main", "visible");

$order = $this->config->getValue("Main", "order");
foreach ($order as $position) {
	
	$data_field = $this->data_fields["content"][$position];

	if ($visible_content[$position]) {
		$data = NULL;
		switch ($data_field) {
			case "lebenslauf" :
				$data["content"] = str_repeat(_("Das ist mein Lebenslauf.") . " &nbsp;", 15);
				break;
			case "schwerp" :
				$data["content"] = str_repeat(_("Das sind meine Arbeitsschwerpunkte.") . " &nbsp;", 15);
				break;
			case "publi" :
				$data["content"] = str_repeat(_("Das sind meine Publikationen.") . " &nbsp;", 15);
				break;
			case "news" :
				$data[0]["topic"] = _("Das ist News Nr. 1");
				$data[0]["body"] = str_repeat(_("News Nr. 1") . " &nbsp;", 10);
				$data[1]["topic"] = _("Das ist News Nr. 2");
				$data[1]["body"] = str_repeat(_("News Nr. 2") . " &nbsp;", 10);
				$data[2]["topic"] = _("Das ist News Nr. 3");
				$data[2]["body"] = str_repeat(_("News Nr. 3") . " &nbsp;", 10);
				break;
			case "termine" :
				$now = time();
				for ($i = 0; $i < 3; $i++) {
					$data[$i]["start"] = $now + 19710329 * ($i + 1);
					$data[$i]["end"] = $data[$i]["start"] + 1000 * ($i + 1);
				}
				$data[0]["title"] = _("Das ist der erste Termin");
				$data[1]["title"] = _("Das ist der zweite Termin");
				$data[2]["title"] = _("Das ist der dritte Termin");
				$data[0]["content"] = str_repeat(_("Erster Termin") . " &nbsp;", 10);
				$data[1]["content"] = str_repeat(_("Zweiter Termin") . " &nbsp;", 10);
				$data[2]["content"] = str_repeat(_("dritter Termin ") . " &nbsp;", 10);
				break;
			case "kategorien" :
				$data["headline"] = _("Eigene Kategorie");
				$data["content"] = str_repeat(_("Eigene Kategorie") . " &nbsp;", 10);
				break;
			case "lehre" :
				$now = time();
				$data[0]["start_time"] = $now - 164160000;
				$data[1]["start_time"] = $now;
				$data[2]["start_time"] = $now + 164160000;
				$data[0]["name"] = _("Veranstaltung 1");
				$data[1]["name"] = _("Veranstaltung 2");
				$data[2]["name"] = _("Veranstaltung 3");
				$data[0]["untertitel"] = _("Untertitel der Veranstaltung 1");
				$data[1]["untertitel"] = _("Untertitel der Veranstaltung 2");
				$data[2]["untertitel"] = _("Untertitel der Veranstaltung 3");
				break;
			case "head" :
				$nameformat = $this->config->getValue("Main", "nameformat");
				switch ($nameformat) {
					case "no_title_short" :
						$data["fullname"] = _("Meyer, P.");
						break;
					case "no_title" :
						$data["fullname"] = _("Peter Meyer");
						break;
					case "no_title_rev" :
						$data["fullname"] = _("Meyer Peter");
						break;
					case "full" :
						$data["fullname"] = _("Dr. Peter Meyer");
						break;
					case "full_rev" :
						$data["fullname"] = _("Meyer, Peter, Dr.");
						break;
				}
				$data["Name"] = _("Mustereinrichtung");
				$data["Strasse"] = _("Musterstra&szlig;e");
				$data["Plz"] = _("12345 Musterstadt");
				$data["raum"] = "A 123";
				$data["Telefon"] = "213 - 237 192";
				$data["Fax"] = "213 - 237 191";
				$data["Email"] = "email@email.org";
				$data["Home"] = "http://www.studip.de";
				$data["sprechzeiten"] = _("Mo. und Do. 12.00 - 13.00");
				break;
		}
				
				
		if (($data_field == "lebenslauf" || $data_field == "schwerp"
				|| $data_field == "publi")) {
			echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
			echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
			echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
			echo $aliases_content[$position] . "</font></td></tr>\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
			if ($this->config->getValue("TableParagraphText", "margin"))
				echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
			else
				echo "<div>";
			echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
			echo $data["content"];
			echo "</font></div></td></tr>\n</table>\n";
		}
		else
			$data_field($this, $data, $aliases_content[$position]);
	}
}

function news (&$this, $data, $alias_content) {	
	echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
	echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
	echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
	echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
	echo "$alias_content</font></td></tr>\n";
	foreach ($data as $dat) {
		echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
		if ($this->config->getValue("TableParagraphSubHeadline", "margin"))
			echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphSubHeadline", "margin") . ";\">";
		else
			echo "<div>";
		echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
		echo $dat["topic"];
		echo "</font></div></td></tr>\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		list ($content, $admin_msg) = explode("<admin_msg>", $dat["body"]);
		echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		if ($this->config->getValue("TableParagraphText", "margin"))
			echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
		else
			echo "<div>";
		echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
		echo $content;
		echo "</font></div></td></tr>\n";
	}
	echo "</table>\n";
}

function termine (&$this, $data, $alias_content) {
	if ($GLOBALS["CALENDAR_ENABLE"]) {
		echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		echo "$alias_content</font></td></tr>\n";
		
		foreach ($data as $dat) {
			echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
			if ($this->config->getValue("TableParagraphSubHeadline", "margin"))
				echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphSubHeadline", "margin") . ";\">";
			else
				echo "<div>";
			echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
			echo strftime($this->config->getValue("Main", "dateformat") . " %H.%m", $dat["start"]);
			if (date("dmY", $dat["start"]) == date("dmY", $dat["end"]))
				echo strftime(" - %H.%m", $dat["end"]);
			else
				echo strftime(" - " . $this->config->getValue("Main", "dateformat") . " %H.%m", $dat["end"]);
			echo " &nbsp;" . $dat["title"];
			echo "</font></div></td></tr>\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
			if ($this->config->getValue("TableParagraphText", "margin"))
				echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
			else
				echo "<div>";
			echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
			echo $dat["content"];
			echo "</font></div></td></tr>\n";
		} 
		echo "</table>\n";
	}
}

function kategorien (&$this, $data, $alias_content) {
	echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
	echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
	echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
	echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
	echo $data["headline"];
	echo "</font></td></tr>\n";
	echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
	echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
	if ($this->config->getValue("TableParagraphText", "margin"))
		echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
	else
		echo "<div>";
	echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
	echo $data["content"];
	echo "</font></div></td></tr>\n</table>\n";
}

function lehre (&$this, $data, $alias_content) {
	global $attr_text_td;
	
	// sem-types in class 1 (Lehre)
	foreach ($GLOBALS["SEM_TYPE"] as $key => $type) {
		if ($type["class"] == 1)
			$types[] = $key;
	}
	$types = implode("','", $types);
	
	// current semester
	$now = time();
	foreach ($GLOBALS["SEMESTER"] as $key => $sem) {
		if ($sem["beginn"] >= $now)
			break;
	}
	
	$sem_range = $this->config->getValue("PersondetailsLectures", "semrange");
	if ($sem_range != "current") {
		if ($sem_range == "three") {
			if ($key > 1)
				$i = -1;
			else
				$i = 0;
			if ((sizeof($GLOBALS["SEMESTER"]) - $key) > 0)
				$max = 2;
			else
				$max = 1;
		}
		else {
			$i = 1 - $key;
			$max = sizeof($GLOBALS["SEMESTER"]) - $key + 1;
		}
		
		$out = "";
		for (;$i < $max; $i++) {
			$start = $GLOBALS["SEMESTER"][$key + $i]["beginn"];
			$end = $GLOBALS["SEMESTER"][$key + $i]["ende"];
				
			$out .= "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
			$out .= "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
			if ($this->config->getValue("TableParagraphSubHeadline", "margin"))
				$out .= "<div style=\"margin-left:" . $this->config->getValue("TableParagraphSubHeadline", "margin") . ";\">";
			else
				$out .= "<div>";
			$out .= "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
			$month = date("n", $start);
			if($month > 9) {
				$out .= $this->config->getValue("PersondetailsLectures", "aliaswise");
				$out .= date(" Y/", $start) . date("y",$end);
			}
			else if($month > 3 && $month < 10) {
				$out .= $this->config->getValue("PersondetailsLectures", "aliassose");
				$out .= date(" Y", $start);
			}
			$out .= "</font></div></td></tr>\n";
			$out .= "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			$out .= "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
			
			if ($this->config->getValue("PersondetailsLectures", "aslist")) {
				if ($this->config->getValue("List", "margin"))
					$out .= "<div style=\"margin-left:" . $this->config->getValue("List", "margin") . ";\">";
				else
					$out .= "<div>";
				$out .= "<ul" . $this->config->getAttributes("List", "ul") . ">\n";
				foreach ($data as $dat) {
					$out .= "<li" . $this->config->getAttributes("List", "li") . ">";
					$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
					$out .= "<a href=\"\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
					$out .= $dat["name"] . "</a></font>\n";
					$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . "><br>";
					$out .= $dat["untertitel"] . "</font>\n";
				}
				$out .= "</ul>";
			}
			else {
				if ($this->config->getValue("TableParagraphText", "margin"))
					$out .= "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
				else
					$out .= "<div>";
				$j = 0;
				foreach ($data as $dat) {
					if ($j) $out .= "<br><br>";
					$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
					$out .= "<a href=\"$lnk\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
					$out .= $dat["name"] . "</a></font>\n";
					$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
					$out .= "<br>" . $dat["untertitel"] . "</font>\n";
					$j = 1;
				}
			}
			$out .= "</div></td></tr>\n";
		}
	}
	else{
		$start = $GLOBALS["SEMESTER"][$key]["beginn"];
		$end = $GLOBALS["SEMESTER"][$key]["ende"];
		
		$out .= "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		$out .= "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		
		if ($this->config->getValue("PersondetailsLectures", "aslist")) {
			if ($this->config->getValue("List", "margin"))
				$out .= "<div style=\"margin-left:" . $this->config->getValue("List", "margin") . ";\">";
			else
				$out .= "<div>";
			$out .= "<ul" . $this->config->getAttributes("List", "ul") . ">";
			foreach ($data as $dat) {
				$out .= "<li" . $this->config->getAttributes("List", "li") . ">";
				$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
				$out .= "<a href=\"\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
				$out .= $dat["name"] . "</a></font>\n";
				$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
				$out .= "<br>" . $dat["untertitel"] . "</font>\n";
			}
			$out .= "</ul>";
		}
		else {
			if ($this->config->getValue("TableParagraphText", "margin"))
				$out .= "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
			else
				$out .= "<div>";
			$j = 0;
			foreach ($data as $dat) {
				if ($j) $out .= "<br><br>";
				$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
				$out .= "<a href=\"\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
				$out .= $dat["name"] . "</a></font><br>\n";
				$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
				$out .= $dat["untertitel"] . "</font>\n";
				$j = 1;
			}
		}
		$out .= "</div></td></tr>\n";
	}
	
	if ($out) {
		$out_title = "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		$out_title .= "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		$out_title .= "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		$out_title .= "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		$out_title .= $alias_content . "</font></td></tr>\n";
		echo $out_title . $out . "</table>\n";
	}
}

function head (&$this, $data) {
	$out = "";
	$out .= "<table" . $this->config->getAttributes("PersondetailsHeader", "table") . ">\n";
	$out .= "<tr" . $this->config->getAttributes("PersondetailsHeader", "tr") . ">";
	$out .= "<td colspan=\"2\" width=\"100%\"";
	$out .= $this->config->getAttributes("PersondetailsHeader", "headlinetd") . ">";
  if ($this->config->getValue("Main", "studiplink")) {
		$out .= "<div" . $this->config->getAttributes("StudipLink", "div") . ">";
		$out .= "<font" . $this->config->getAttributes("StudipLink", "font") . ">";
		$out .= sprintf("<a href=\"\"%s>%s</a>",
				$this->config->getAttributes("StudipLink", "a"),
				$this->config->getValue("StudipLink", "linktext"));
		if ($this->config->getValue("StudipLink", "image")) {
			if ($image_url = $this->config->getValue("StudipLink", "imageurl"))
				$img = "&nbsp;<img border=\"0\" align=\"absmiddle\" src=\"$image_url\">";
			else {
				$img = "&nbsp;<img border=\"0\" src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}";
				$img .= "pictures/login.gif\" align=\"absmiddle\">";
			}
			$out .= sprintf("<a href=\"\"%s>%s</a>", $this->config->getAttributes("StudipLink", "a"), $img);
		}
		$out .= "</font></div>";
	}
	$out .= "<font" . $this->config->getAttributes("PersondetailsHeader", "font") . ">";
	$out .= $data["fullname"];
	$out .= "</font></td></tr>\n";
	
	$out .= "<tr><td" . $this->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
	$out .= kontakt($this, $data) . "</td>\n";
	
	$out .=  "<td" . $this->config->getAttributes("PersondetailsHeader", "picturetd") . ">";
	if (file_exists("{$GLOBALS['ABSOLUTE_PATH_STUDIP']}/user/nobody.jpg")) {
		$out .=  "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}user/";
		$out .=  "nobody.jpg\" alt=\"Foto " . $data["fullname"] . "\"";
		$out .=  $this->config->getAttributes("PersondetailsHeader", "img") . ">";
	}
	else
		$out .=  "&nbsp;";
		
	$out .=  "</td></tr>\n</table>\n";
	
	echo $out;
}

function kontakt (&$this, $data) {
	$attr_table = $this->config->getAttributes("Contact", "table");
	$attr_tr = $this->config->getAttributes("Contact", "table");
	$attr_td = $this->config->getAttributes("Contact", "td");
	$attr_fonttitle = $this->config->getAttributes("Contact", "fonttitle");
	$attr_fontcontent = $this->config->getAttributes("Contact", "fontcontent");
	
	$out = "<table$attr_table>\n";
	$out .= "<tr$attr_tr>";
	$out .= "<td colspan=\"2\"$attr_td>";
	$out .= "<font$attr_fonttitle>";
	if ($headline = $this->config->getValue("Contact", "headline"))
		$out .= "$headline<br><br></font>\n";
	else
		$out .= "<br></font>\n";
	
	$out .= "<font$attr_fontcontent>";
	$out .= $data["fullname"] . "<br>\n";
	
	$out .= "<br>";
	$out .= "<a href=\"\">";
	$out .= $data["Name"] . "</a><br>";
	if ($this->config->getValue("Contact", "adradd"))
		$out .= $this->config->getValue("Contact", "adradd");
	
	$out .= "<br><br>" . $data["Strasse"];
	$out .= "<br>" . $data["Plz"];
	
  $out .= "<br><br></font></td></tr>\n";
	
	$order = $this->config->getValue("Contact", "order");
	$visible = $this->config->getValue("Contact", "visible");
	$alias_contact = $this->config->getValue("Contact", "aliases");
	foreach ($order as $position) {
		if (!$visible[$position])
			continue;
		$data_field = $this->data_fields["contact"][$position];
	  if($data_field == "raum"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
	    $out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
   	  $out .= $data["raum"] . "</font></td></tr>\n";
    }

	  if($data_field == "Telefon"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
   	  $out .= $data["Telefon"] . "</font></td></tr>\n";
	  }
    
   	if($data_field == "Fax"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= $data["Fax"] . "</font></td></tr>\n";
	  }
           	
	  if($data_field == "Email"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= "<a href=\"mailto:$mail\">{$data['Email']}</a></font></td></tr>\n";
		}
        	
		if($data_field == "Home"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= $data["home"] . "</font></td></tr>\n";
		}
			
		if($data_field == "sprechzeiten"){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= $data["sprechzeiten"] . "</font></td></tr>\n";
		}
	}
	$out .= "</table>\n";
	
	return $out;
}				

?>
