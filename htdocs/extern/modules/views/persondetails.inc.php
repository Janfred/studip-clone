<?

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "config.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_EXTERN"]
		. "/lib/extern_functions.inc.php");
if ($GLOBALS["CALENDAR_ENABLE"]) {
	require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . $GLOBALS["RELATIVE_PATH_CALENDAR"]
			. "/lib/DbCalendarEventList.class.php");
}
global $_fullname_sql;

$instituts_id = $this->config->range_id;
$username = $args["username"];

$db_inst =& new DB_Institut();
$db =& new DB_Institut();
$db_kategorien =& new DB_Institut();

$query_user_data = "SELECT i.Institut_id, i.Name, i.Strasse, i.Plz, i.url, ui.*, aum.*, "
						. $_fullname_sql[$this->config->getValue("Main", "nameformat")] . " AS fullname,"
						. "uin.user_id, uin.lebenslauf, uin.publi, uin.schwerp, uin.Lehre, uin.Home "
						. "FROM Institute i LEFT JOIN user_inst ui USING(Institut_id) "
	          . "LEFT JOIN auth_user_md5 aum USING(user_id) "
	          . "LEFT JOIN user_info uin USING (user_id) WHERE";

// Mitarbeiter/in am Institut
$db_inst->query("SELECT i.Institut_id FROM Institute i LEFT JOIN user_inst ui USING(Institut_id) "
	          ."LEFT JOIN auth_user_md5 aum USING(user_id) "
	          ."WHERE i.Institut_id = '$instituts_id' AND aum.username = '$username'");

// Mitarbeiter/in am Heimatinstitut des Seminars
if(!$db_inst->num_rows() && $sem_id){
	$db_inst->query("SELECT s.Institut_id FROM seminare s LEFT JOIN user_inst ui USING(Institut_id) "
	               ."LEFT JOIN auth_user_md5 aum USING(user_id) WHERE s.Seminar_id = '$sem_id' "
								 ."AND aum.username = '$username' AND ui.inst_perms = 'dozent'");
	if($db_inst->num_rows() && $db_inst->next_record())
		$instituts_id = $db_inst->f("Institut_id");
}

// an beteiligtem Institut Dozent(in)
if(!$db_inst->num_rows() && $sem_id){
	$db_inst->query("SELECT si.institut_id FROM seminare s LEFT JOIN seminar_inst si ON(s.Seminar_id = si.seminar_id) "
	               ."LEFT JOIN user_inst ui ON(si.institut_id = ui.Institut_id) LEFT JOIN auth_user_md5 aum "
								 ."USING(user_id) WHERE s.Seminar_id = '$sem_id' AND si.institut_id != '$instituts_id' "
								 ."AND ui.inst_perms = 'dozent' AND aum.username = '$username'");
	if($db_inst->num_rows() && $db_inst->next_record())
		$instituts_id = $db_inst->f("institut_id");
}

// ist zwar global Dozent, aber an keinem Institut eingetragen
if(!$db_inst->num_rows() && $sem_id){
	$query = "SELECT aum.*, ";
	$query .= $_fullname_sql[$this->config->getValue("Main", "nameformat")] . " AS fullname, ";
	$query .= " FROM auth_user_md5 aum LEFT JOIN user_info USING(user_id) ";
	$query .= "WHERE username = '$username' AND perms = 'dozent'";
	$db->query($query);
}
else
	$db->query($query_user_data . " aum.username = '$username' AND i.Institut_id = '$instituts_id'");

if(!$db->num_rows())
	die;

if($db->next_record())
	$db_kategorien->query("SELECT * FROM auth_user_md5 LEFT JOIN kategorien ON (range_id=user_id) "
	                     ."WHERE username='$username' AND hidden=0");
		
$attr_subheadline_td = preg_replace('/width\="[^"]+"/i',
		$this->config->getAttributes("TableParagraphSubHeadline", "td"),
		$this->config->getValue("TableParagraph", "margin"));


$aliases_content = $this->config->getValue("Main", "aliases");
$visible_content = $this->config->getValue("Main", "visible");

echo head($this, $db);

$order = $this->config->getValue("Main", "order");
foreach ($order as $position) {

	$data_field = $this->data_fields["content"][$position];

	if ($visible_content[$position]) {
		if (($data_field == "lebenslauf" || $data_field == "schwerp"
				|| $data_field == "publi")) {
			if ($db->f($data_field) != "") {
				echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
				echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
				echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
				echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
				echo $aliases_content[$position] . "</font></td></tr>\n";
				echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
				echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
				echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
				echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
				echo FixLinks(format(htmlReady($db->f($data_field))));
				echo "</font></div></td></tr>\n</table>\n";
			}
		}
		else
			$data_field($this, $db, $aliases_content[$position]);
	}
}

function news (&$this, $db, $alias_content) {	
	$db_news = new DB_Institut();
	$query = "SELECT * FROM news_range nr LEFT JOIN news n USING(news_id) WHERE "
					. "nr.range_id = '" . $db->f("user_id") . "' AND user_id = '" . $db->f("user_id")
					. "' AND date <= " . time() . " AND (date + expire) >= " . time();
	$db_news->query($query);
	if ($db_news->num_rows()) {
		echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		echo "$alias_content</font></td></tr>\n";
	}
	while ($db_news->next_record()) {
		echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
		echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphSubHeadline", "margin") . ";\">";
		echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
		echo format(htmlReady($db_news->f("topic")));
		echo "</font></div></td></tr>\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		list ($content, $admin_msg) = explode("<admin_msg>", $db_news->f("body"));
		echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
		echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
		echo FixLinks(format(htmlReady($content)));
		echo "</font></div></td></tr>\n</table>";
	}
}

function termine (&$this, $db, $alias_content) {
	if ($GLOBALS["CALENDAR_ENABLE"]) {
		$event_list = new AppList($db->f("user_id"));
		if ($event_list->existEvent()) {
			echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
			echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
			echo "$alias_content</font></td></tr>\n";
			//$date_form = $this->config->getValue("
			
			while ($event = $event_list->nextEvent()) {
				echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
				echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
				echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphSubHeadline", "margin") . ";\">";
				echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
				echo strftime("%d.%m.%Y %H.%m", $event->getStart());
				if (date("dmY", $event->getStart()) == date("dmY", $event->getEnd()))
					echo strftime(" - %H.%m", $event->getEnd());
				else
					echo strftime(" - %d.%m.%Y %H.%m", $event->getEnd());
				echo " &nbsp;" . format(htmlReady($event->getTitle()));
				echo "</font></div></td></tr>\n";
				if ($event->getDescription()) {
					echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
					echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
					echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
					echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
					echo FixLinks(format(htmlReady($event->getDescription())));
					echo "</font></div></td></tr>\n";
				}
			} 
			echo "</table>\n";
		}
	}
}

function kategorien (&$this, $db, $alias_content) {
	$db_kategorien = new DB_Institut();
	$query = "SELECT * FROM auth_user_md5 LEFT JOIN kategorien ON (range_id=user_id) "
	       ."WHERE username='" . $db->f("username") . "' AND hidden=0";
	$db_kategorien->query($query);
	while ($db_kategorien->next_record()) {
		echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		echo htmlReady($db_kategorien->f("name"), TRUE);
		echo "</font></td></tr>\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		echo "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
		echo "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
		echo FixLinks(format(htmlReady($db_kategorien->f("content"))));
		echo "</font></div></td></tr>\n</table>\n";
	} 
}

function lehre (&$this, $db, $alias_content) {
	global $attr_text_td, $end, $start;
	$db1 = new DB_Institut;
	
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
	
	$lnk_sdet = $this->getModuleLink("Lecturedetails",
			$this->config->getValue("LinkIntern", "config"), $this->config->getValue("LinkIntern", "srilink"));
	$lnk_sdet .= "&seminar_id=";
	
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
			$query = "SELECT * FROM seminar_user su LEFT JOIN seminare s USING(seminar_id) "
		           ."WHERE user_id='".$db->f("user_id")."' AND "
				       ."su.status LIKE 'dozent' AND ((start_time >= $start "
				       ."AND start_time <= $end) OR (start_time <= $end "
							 ."AND duration_time = -1)) AND s.status IN ('$types') ORDER BY s.mkdate DESC";
				
			$db1->query($query);
				
			if ($db1->num_rows()) {
				$out .= "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
				$out .= "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
				$out .= "<div style=\"margin-left:" . $this->config->getValue("TableParagraphSubHeadline", "margin") . ";\">";
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
					$out .= "<div style=\"margin-left:" . $this->config->getValue("List", "margin") . ";\">";
					$out .= "<ul" . $this->config->getAttributes("List", "ul") . ">\n";
					while ($db1->next_record()) {
						$lnk = $lnk_sdet . $db1->f("Seminar_id");
						$out .= "<li" . $this->config->getAttributes("List", "li") . ">";
						$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
						$out .= "<a href=\"$lnk\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
						$out .= htmlReady($db1->f("Name"), TRUE) . "</a></font>\n";
						if ($db1->f("Untertitel") != "") {
							$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . "><br>";
							$out .= htmlReady($db1->f("Untertitel"), TRUE) . "</font>\n";
						}
					}
					$out .= "</ul>";
				}
				else {
					$out .= "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
					$j = 0;
					while ($db1->next_record()) {
						if ($j) $out .= "<br><br>";
						$lnk = $lnk_sdet . $db1->f("Seminar_id");
						$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
						$out .= "<a href=\"$lnk\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
						$out .= htmlReady($db1->f("Name"), TRUE) . "</a></font>\n";
						if($db1->f("Untertitel") != "") {
							$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
							$out .= "<br>" . htmlReady($db1->f("Untertitel"), TRUE) . "</font>\n";
						}
						$j = 1;
					}
				}
				$out .= "</div></td></tr>\n";
			}
		}
	}
	else{
		$start = $GLOBALS["SEMESTER"][$key]["beginn"];
		$end = $GLOBALS["SEMESTER"][$key]["ende"];
		$out = "";
		$db1->query("SELECT * FROM seminar_user su LEFT JOIN seminare s USING(seminar_id) "
		  	        ."WHERE user_id LIKE '".$db->f("user_id")."' AND "
					      ."su.status LIKE 'dozent' AND ((start_time >= $start "
				       	."AND start_time <= $end) OR (start_time <= $end "
							 	."AND duration_time = -1)) AND s.status IN ('$types') ORDER BY s.mkdate DESC");

		if ($db1->num_rows()) {
			$out .= "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			$out .= "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
			
			if ($this->config->getValue("PersondetailsLectures", "aslist")) {
				$out .= "<div style=\"margin-left:" . $this->config->getValue("List", "margin") . ";\">";
				$out .= "<ul" . $this->config->getAttributes("List", "ul") . ">";
				while ($db1->next_record()) {
					$lnk = $lnk_sdet . $db1->f("Seminar_id");
					$out .= "<li" . $this->config->getAttributes("List", "li") . ">";
					$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
					$out .= "<a href=\"$lnk\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
					$out .= htmlReady($db1->f("Name"), TRUE) . "</a></font>\n";
					if($db1->f("Untertitel") != "") {
						$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
						$out .= "<br>" . htmlReady($db1->f("Untertitel"), TRUE) . "</font>\n";
					}
				}
				$out .= "</ul>";
			}
			else {
				$out .= "<div style=\"margin-left:" . $this->config->getValue("TableParagraphText", "margin") . ";\">";
				$j = 0;
				while ($db1->next_record()) {
					if ($j) $out .= "<br><br>";
					$lnk = $lnk_sdet . $db1->f("Seminar_id");
					$out .= "<font" . $this->config->getAttributes("LinkIntern", "font") . ">";
					$out .= "<a href=\"$lnk\"" . $this->config->getAttributes("LinkIntern", "a") . ">";
					$out .= htmlReady($db1->f("Name"), TRUE) . "</a></font><br>\n";
					if($db1->f("Untertitel") != "") {
						$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
						$out .= htmlReady($db1->f("Untertitel"), TRUE) . "</font>\n";
					}
					$j = 1;
				}
			}
			$out .= "</div></td></tr>\n";
		}
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

function head (&$this, $db) {
	$out = "";
	$out .= "<table" . $this->config->getAttributes("PersondetailsHeader", "table") . ">\n";
	$out .= "<tr" . $this->config->getAttributes("PersondetailsHeader", "tr") . ">";
	$out .= "<td colspan=\"2\" width=\"100%\"";
	$out .= $this->config->getAttributes("PersondetailsHeader", "headlinetd") . ">";
  if ($this->config->getValue("Main", "studiplink")) {
		$out .= "<div" . $this->config->getAttributes("StudipLink", "div") . ">";
		$out .= "<font" . $this->config->getAttributes("StudipLink", "font") . ">";
		$lnk = "http://{$GLOBALS['EXTERN_SERVER_NAME']}edit_about.php?login=yes&view=Daten";
		$lnk .= "&usr_name=" . $db->f("username");
		$out .= sprintf("<a href=\"%s\"%s target=\"_blank\">%s</a>", $lnk,
				$this->config->getAttributes("StudipLink", "a"),
				$this->config->getValue("StudipLink", "linktext"));
		if ($this->config->getValue("StudipLink", "image")) {
			if ($image_url = $this->config->getValue("StudipLink", "imageurl"))
				$img = "&nbsp;<img border=\"0\" align=\"absmiddle\" src=\"$image_url\">";
			else {
				$img = "&nbsp;<img border=\"0\" src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}";
				$img .= "pictures/login.gif\" align=\"absmiddle\">";
			}
			$out .= sprintf("<a href=\"%s\"%s target=\"_blank\">%s</a>", $lnk,
				$this->config->getAttributes("StudipLink", "a"), $img);
		}
		$out .= "</font></div>";
	}
	$out .= "<font" . $this->config->getAttributes("PersondetailsHeader", "font") . ">";
	$out .= htmlReady($db->f("fullname"), TRUE);
	$out .= "</font></td></tr>\n";
	
	$out .= "<tr><td" . $this->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
	$out .= kontakt($this, $db) . "</td>\n";
	
	$out .=  "<td" . $this->config->getAttributes("PersondetailsHeader", "picturetd") . ">";
	if (file_exists("{$GLOBALS['ABSOLUTE_PATH_STUDIP']}/user/" . $db->f("user_id").".jpg")) {
		$out .=  "<img src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}user/";
		$out .=  $db->f("user_id") . ".jpg\" alt=\"Foto " . htmlReady(trim($db->f("fullname"))) . "\"";
		$out .=  $this->config->getAttributes("PersondetailsHeader", "img") . ">";
	}
	else
		$out .=  "&nbsp;";
		
	$out .=  "</td></tr>\n</table>\n";
	
	return $out;
}

function kontakt ($this, $db) {
//	global $db;
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
	$out .= htmlReady($db->f("fullname"), TRUE) . "<br>\n";
	
	if ($db->f("Name")) {
		$out .= "<br>";
		$url = trim($db->f("url"));
		if (!stristr($url, "http://"))
			$url = "http://$url";
		$out .= "<a href=\"$url\" target=\"_top\">";
		$out .= htmlReady($db->f("Name"), TRUE) . "</a><br>";
		if ($this->config->getValue("Contact", "adradd"))
			$out .= $this->config->getValue("Contact", "adradd");
	}
	
	if ($db->f("Strasse")) {
		$out .= "<br><br>" . htmlReady($db->f("Strasse"), TRUE);
		if($db->f("Plz")!="")
  		$out .= "<br>" . htmlReady($db->f("Plz"), TRUE);
	}
  $out .= "<br><br></font></td></tr>\n";
	
	$order = $this->config->getValue("Contact", "order");
	$visible = $this->config->getValue("Contact", "visible");
	$alias_contact = $this->config->getValue("Contact", "aliases");
	foreach ($order as $position) {
		if (!$visible[$position])
			continue;
		$data_field = $this->data_fields["contact"][$position];
	  if($data_field == "raum" && $db->f("raum")){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
	    $out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
   	  $out .= htmlReady($db->f("raum"), TRUE) . "</font></td></tr>\n";
    }

	  if($data_field == "Telefon" && $db->f("Telefon")){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
   	  $out .= htmlReady($db->f("Telefon"), TRUE) . "</font></td></tr>\n";
	  }
    
   	if($data_field == "Fax" && $db->f("Fax")){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= htmlReady($db->f("Fax"), TRUE) . "</font></td></tr>\n";
	  }
           	
	  if($data_field == "Email" && $db->f("Email")){
			$mail = trim($db->f("Email"));
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= "<a href=\"mailto:$mail\">$mail</a></font></td></tr>\n";
		}
        	
		if($data_field == "Home" && $db->f("Home")){
			$home = trim(formatReady($db->f("Home")));
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= "$home</font></td></tr>\n";
		}
			
		if($data_field == "sprechzeiten" && $db->f("sprechzeiten")){
			$out .= "<tr$attr_tr>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fonttitle>";
			$out .= $alias_contact[$position] . "</font></td>";
			$out .= "<td$attr_td>";
			$out .= "<font$attr_fontcontent>";
			$out .= formatReady($db->f("sprechzeiten"), TRUE) . "</font></td></tr>\n";
		}
	}
	$out .= "</table>\n";
	
	return $out;
}				

?>
