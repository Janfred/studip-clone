<?

require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "config.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "/lib/classes/SemesterData.class.php");
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
$sem_id = $args["seminar_id"];

$db_inst =& new DB_Seminar();
$db =& new DB_Seminar();

if (!$nameformat = $this->config->getValue("Main", "nameformat"))
	$nameformat = "full";

$query_user_data = "SELECT i.Institut_id, i.Name, i.Strasse, i.Plz, i.url, ui.*, aum.*, "
						. $_fullname_sql[$nameformat] . " AS fullname,"
						. "uin.user_id, uin.lebenslauf, uin.publi, uin.schwerp, uin.Home "
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
	$query .= $_fullname_sql[$nameformat] . " AS fullname ";
	$query .= "FROM auth_user_md5 aum LEFT JOIN user_info USING(user_id) ";
	$query .= "WHERE username = '$username' AND perms = 'dozent'";
	$db->query($query);
}
else
	$db->query($query_user_data . " aum.username = '$username' AND i.Institut_id = '$instituts_id'");

if(!$db->next_record())
	die;

$aliases_content = $this->config->getValue("Main", "aliases");
$visible_content = $this->config->getValue("Main", "visible");

if ($margin = $this->config->getValue("TableParagraphText", "margin")) {
	$text_div = "<div style=\"margin-left:$margin;\">";
	$text_div_end = "</div>";
}
else {
	$text_div = "";
	$text_div_end = "";
}

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";

$studip_link = "http://{$GLOBALS['EXTERN_SERVER_NAME']}edit_about.php";
$studip_link .= "?login=yes&view=Daten&usr_name=$username";
if ($this->config->getValue("Main", "studiplink") == "top") {
	$args = array("width" => "100%", "height" => "40", "link" => $studip_link);
	echo "<tr><td width=\"100%\">\n";
	$this->elements["StudipLink"]->printout($args);
	echo "</td></tr>";
}

// generic data fields
if ($generic_datafields = $this->config->getValue("Main", "genericdatafields")) {
	$datafields_obj =& new DataFields($db->f("user_id"));
	$datafields = $datafields_obj->getLocalFields($db->f("user_id"));
}

$order = $this->config->getValue("Main", "order");
foreach ($order as $position) {

	$data_field = $this->data_fields["content"][$position];

	if ($visible_content[$position]) {
		switch ($data_field) {
			case "lebenslauf" :
			case "schwerp" :
			case "publi" :
				if ($db->f($data_field) != "") {
					echo "<tr><td width=\"100%\">\n";
					echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
					echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
					echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
					echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
					echo $aliases_content[$position] . "</font></td></tr>\n";
					echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
					echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
					echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
					echo formatReady($db->f($data_field), TRUE, TRUE, TRUE);
					echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
				}
				break;
			case "news" :
			case "termine" :
			case "kategorien" :
			case "lehre" :
			case "head" :
				$data_field($this, $db, $aliases_content[$position], $text_div, $text_div_end);
				break;
			// generic data fields
			default :
				// include generic datafields
				if ($datafields[$data_field]["content"]) {
					echo "<tr><td width=\"100%\">\n";
					echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
					echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr");
					echo "><td" . $this->config->getAttributes("TableParagraphHeadline", "td");
					echo "><font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">\n";
					echo $aliases_content[$position] . "</font></td></tr>\n";
					echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
					echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
					echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">\n";
					echo formatReady($datafields[$data_field]["content"], TRUE, TRUE, TRUE);
					echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
				}
		}
	}
}

if ($this->config->getValue("Main", "studiplink") == "bottom") {
	$args = array("width" => "100%", "height" => "40", "link" => $studip_link);
	echo "<tr><td width=\"100%\">\n";
	$this->elements["StudipLink"]->printout($args);
	echo "</td></tr>";
}

echo "</table>\n";

function news (&$this, $db, $alias_content, $text_div, $text_div_end) {
	if ($margin = $this->config->getValue("TableParagraphSubHeadline", "margin")) {
		$subheadline_div = "<div style=\"margin-left:$margin;\">";
		$subheadline_div_end = "</div>";
	}
	else {
		$subheadline_div = "";
		$subheadline_div_end = "";
	}
	
	$db_news = new DB_Seminar();
	$query = "SELECT * FROM news_range nr LEFT JOIN news n USING(news_id) WHERE "
					. "nr.range_id = '" . $db->f("user_id") . "' AND user_id = '" . $db->f("user_id")
					. "' AND date <= " . time() . " AND (date + expire) >= " . time();
	$db_news->query($query);
	if ($db_news->num_rows()) {
		echo "<tr><td width=\"100%\">\n";
		echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		echo "$alias_content</font></td></tr>\n";
	
		while ($db_news->next_record()) {
			echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
			echo $subheadline_div;
			echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
			echo htmlReady($db_news->f("topic"));
			echo "</font>$subheadline_div_end</td></tr>\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			list ($content, $admin_msg) = explode("<admin_msg>", $db_news->f("body"));
			echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
			echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
			echo formatReady($content, TRUE, TRUE, TRUE);
			echo "</font>$text_div_end</td></tr>\n";
		}
		echo "</table>\n</td></tr>\n";
	}
}

function termine (&$this, $db, $alias_content, $text_div, $text_div_end) {
	if ($GLOBALS["CALENDAR_ENABLE"]) {
		if ($margin = $this->config->getValue("TableParagraphSubHeadline", "margin")) {
			$subheadline_div = "<div style=\"margin-left:$margin;\">";
			$subheadline_div_end = "</div>";
		}
		else {
			$subheadline_div = "";
			$subheadline_div_end = "";
		}
	
		$event_list = new DbCalendarEventList($db->f("user_id"));
		if ($event_list->existEvent()) {
			echo "<tr><td width=\"100%\">\n";
			echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
			echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
			echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
			echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
			echo "$alias_content</font></td></tr>\n";
			
			while ($event = $event_list->nextEvent()) {
				echo "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
				echo "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
				echo $subheadline_div;
				echo "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
				echo strftime($this->config->getValue("Main", "dateformat") . " %H.%m", $event->getStart());
				if (date("dmY", $event->getStart()) == date("dmY", $event->getEnd()))
					echo strftime(" - %H.%m", $event->getEnd());
				else
					echo strftime(" - " . $this->config->getValue("Main", "dateformat") . " %H.%m", $event->getEnd());
				echo " &nbsp;" . htmlReady($event->getTitle());
				echo "</font>$subheadline_div_end</td></tr>\n";
				if ($event->getDescription()) {
					echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
					echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
					echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
					echo htmlReady($event->getDescription());
					echo "</font>$text_div_end</td></tr>\n";
				}
			} 
			echo "</table>\n</td></tr>\n";
		}
	}
}

function kategorien (&$this, $db, $alias_content, $text_div, $text_div_end) {
	$db_kategorien = new DB_Seminar();
	$query = "SELECT * FROM auth_user_md5 LEFT JOIN kategorien ON (range_id=user_id) "
	       ."WHERE username='" . $db->f("username") . "' AND hidden=0";
	$db_kategorien->query($query);
	while ($db_kategorien->next_record()) {
		echo "<tr><td width=\"100%\">\n";
		echo "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		echo "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		echo htmlReady($db_kategorien->f("name"), TRUE);
		echo "</font></td></tr>\n";
		echo "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
		echo "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";
		echo "$text_div<font" . $this->config->getAttributes("TableParagraphText", "font") . ">";
		echo formatReady($db_kategorien->f("content"), TRUE, TRUE, TRUE);
		echo "</font>$text_div_end</td></tr>\n</table>\n</td></tr>\n";
	} 
}

function lehre (&$this, $db, $alias_content, $text_div, $text_div_end) {
	global $attr_text_td, $end, $start; 
	$db1 = new DB_Seminar();
	$semester = new SemesterData();
	$all_semester = $semester->getAllSemesterData();
	// old hard coded $SEMESTER-array starts with index 1
	array_unshift($all_semester, 0);
	
	if ($margin = $this->config->getValue("TableParagraphSubHeadline", "margin")) {
		$subheadline_div = "<div style=\"margin-left:$margin;\">";
		$subheadline_div_end = "</div>";
	}
	else {
		$subheadline_div = "";
		$subheadline_div_end = "";
	}
	if ($margin = $this->config->getValue("List", "margin")) {
		$list_div = "<div style=\"margin-left:$margin;\">";
		$list_div_end = "</div>";
	}
	else {
		$list_div = "";
		$list_div_end = "";
	}
	
	// sem-types in class 1 (Lehre)
	foreach ($GLOBALS["SEM_TYPE"] as $key => $type) {
		if ($type["class"] == 1)
			$types[] = $key;
	}
	$types = implode("','", $types);
	
	$switch_time = mktime(0, 0, 0, date("m"),
			date("d") + 7 * $this->config->getValue("PersondetailsLectures", "semswitch"), date("Y"));
	// get current semester
	$current_sem = get_sem_num($switch_time) + 1;
	
	switch ($this->config->getValue("PersondetailsLectures", "semstart")) {
		case "previous" :
			if (isset($all_semester[$current_sem - 1]))
				$current_sem--;
			break;
		case "next" :
			if (isset($all_semester[$current_sem + 1]))
				$current_sem++;
			break;
		case "current" :
			break;
		default :
			if (isset($all_semester[$this->config->getValue("PersondetailsLectures", "semstart")]))
				$current_sem = $this->config->getValue("PersondetailsLectures", "semstart");
	}
	
	$last_sem = $current_sem + $this->config->getValue("PersondetailsLectures", "semrange") - 1;
	if ($last_sem < $current_sem)
		$last_sem = $current_sem;
	if (!isset($all_semester[$last_sem]))
		$last_sem = sizeof($all_semester) - 1;
	
	$out = "";
	for (;$current_sem <= $last_sem; $last_sem--) {
		$query = "SELECT * FROM seminar_user su LEFT JOIN seminare s USING(seminar_id) "
	           ."WHERE user_id='".$db->f("user_id")."' AND "
			       ."su.status LIKE 'dozent' AND ((start_time >= {$all_semester[$last_sem]['beginn']} "
			       ."AND start_time <= {$all_semester[$last_sem]['beginn']}) OR (start_time <= {$all_semester[$last_sem]['ende']} "
						 ."AND duration_time = -1)) AND s.status IN ('$types') AND s.visible = 1 "
						 ."ORDER BY s.mkdate DESC";
			
		$db1->query($query);
			
		if ($db1->num_rows()) {
			if (!($this->config->getValue("PersondetailsLectures", "semstart") == "current"
					&& $this->config->getValue("PersondetailsLectures", "semrange") == 1)) {
				$out .= "<tr" . $this->config->getAttributes("TableParagraphSubHeadline", "tr") . ">";
				$out .= "<td" . $this->config->getAttributes("TableParagraphSubHeadline", "td") . ">";
				$out .= $subheadline_div;
				$out .= "<font" . $this->config->getAttributes("TableParagraphSubHeadline", "font") . ">";
				$month = date("n", $all_semester[$last_sem]['beginn']);
				if($month > 9) {
					$out .= $this->config->getValue("PersondetailsLectures", "aliaswise");
					$out .= date(" Y/", $all_semester[$last_sem]['beginn']) . date("y", $all_semester[$last_sem]['ende']);
				}
				else if($month > 3 && $month < 10) {
					$out .= $this->config->getValue("PersondetailsLectures", "aliassose");
					$out .= date(" Y", $all_semester[$last_sem]['beginn']);
				}
				$out .= "</font>$subheadline_div_end</td></tr>\n";
			}
			
			$out .= "<tr" . $this->config->getAttributes("TableParagraphText", "tr") . ">";
			$out .= "<td" . $this->config->getAttributes("TableParagraphText", "td") . ">";

			if ($this->config->getValue("PersondetailsLectures", "aslist")) {
				$out .= "$list_div<ul" . $this->config->getAttributes("List", "ul") . ">\n";
				while ($db1->next_record()) {
					$out .= "<li" . $this->config->getAttributes("List", "li") . ">";
					$out .= $this->elements["LinkIntern"]->toString(array("module" => "Lecturedetails",
							"link_args" => "seminar_id=" . $db1->f("Seminar_id"),
							"content" => htmlReady($db1->f("Name"), TRUE)));
					if ($db1->f("Untertitel") != "") {
						$out .= "<font" . $this->config->getAttributes("TableParagraphText", "font") . "><br>";
						$out .= htmlReady($db1->f("Untertitel"), TRUE) . "</font>\n";
					}
				}
				$out .= "</ul>$list_div_end";
			}
			else {
				$out .= $text_div;
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
				$out .= $text_div_end;
			}
			$out .= "</td></tr>\n";
		}
	}
	
	if ($out) {
		$out_title = "<tr><td width=\"100%\">\n";
		$out_title .= "<table" . $this->config->getAttributes("TableParagraph", "table") . ">\n";
		$out_title .= "<tr" . $this->config->getAttributes("TableParagraphHeadline", "tr") . ">";
		$out_title .= "<td" . $this->config->getAttributes("TableParagraphHeadline", "td") . ">";
		$out_title .= "<font" . $this->config->getAttributes("TableParagraphHeadline", "font") . ">";
		$out_title .= $alias_content . "</font></td></tr>\n";
		echo $out_title . $out . "</table>\n</td></tr>\n";
	}
}

function head (&$this, $db, $a) {
	$pic_max_width = $this->config->getValue("PersondetailsHeader", "img_width");
	$pic_max_height = $this->config->getValue("PersondetailsHeader", "img_height");

	// fit size of image
	if ($pic_max_width && $pic_max_height) {
		$pic_size = @getimagesize($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "user/"
				. $db->f("user_id") . ".jpg");
	
		if ($pic_size[0] > $pic_max_width || $pic_size[1] > $pic_max_height) {
			$fak_width = $pic_size[0] / $pic_max_width;
			$fak_height = $pic_size[1] / $pic_max_height;
			if ($fak_width > $fak_height) {
				$pic_width = (int) ($pic_size[0] / $fak_width);
				$pic_height = (int) ($pic_size[1] / $fak_width);
			}
			else {
				$pic_height = (int) ($pic_size[1] / $fak_height);
				$pic_width = (int) ($pic_size[0] / $fak_height);
			}
		}
		else {
			$pic_width = $pic_size[0];
			$pic_height = $pic_size[1];
		}
		$pic_max_width = $pic_width;
		$pic_max_height = $pic_height;
	}

	$this->config->config["PersondetailsHeader"]["img_width"] = $pic_max_width;
	$this->config->config["PersondetailsHeader"]["img_height"] = $pic_max_height;
	
	if ($this->config->getValue("Main", "showcontact")
			&& $this->config->getValue("Main", "showimage"))
		$colspan = " colspan=\"2\"";
	else
		$colspan = "";
	
	echo "<tr><td width=\"100%\">\n";
	echo "<table" . $this->config->getAttributes("PersondetailsHeader", "table") . ">\n";
	echo "<tr" . $this->config->getAttributes("PersondetailsHeader", "tr") . ">";
	echo "<td$colspan width=\"100%\"";
	echo $this->config->getAttributes("PersondetailsHeader", "headlinetd") . ">";
	echo "<font" . $this->config->getAttributes("PersondetailsHeader", "font") . ">";
	echo htmlReady($db->f("fullname"), TRUE);
	echo "</font></td></tr>\n";
	
	if ($this->config->getValue("Main", "showimage")
			|| $this->config->getValue("Main", "showcontact")) {
		echo "<tr>";
		if ($this->config->getValue("Main", "showcontact")
				&& ($this->config->getValue("Main", "showimage") == "right"
				|| !$this->config->getValue("Main", "showimage"))) {
				echo "<td" . $this->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
				echo kontakt($this, $db) . "</td>\n";
		}
		
		if ($this->config->getValue("Main", "showimage")) {
			echo "<td" . $this->config->getAttributes("PersondetailsHeader", "picturetd") . ">";
			if (file_exists("{$GLOBALS['ABSOLUTE_PATH_STUDIP']}/user/" . $db->f("user_id").".jpg")) {
				echo "<img src=\"http://{$GLOBALS['EXTERN_SERVER_NAME']}user/";
				echo $db->f("user_id") . ".jpg\" alt=\"Foto " . htmlReady(trim($db->f("fullname"))) . "\"";
				echo $this->config->getAttributes("PersondetailsHeader", "img") . "></td>";
			}
			else
				echo "&nbsp;</td>";
		}
		
		if ($this->config->getValue("Main", "showcontact")
				&& $this->config->getValue("Main", "showimage") == "left") {
			echo "<td" . $this->config->getAttributes("PersondetailsHeader", "contacttd") . ">";
			echo kontakt($this, $db) . "</td>\n";
		}
		
		echo "</tr>\n";
	}
	
	echo  "</table>\n</td></tr>\n";
}

function kontakt ($this, $db) {
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
		$out .= "<a href=\"$url\" target=\"_blank\">";
		$out .= htmlReady($db->f("Name"), TRUE) . "</a>";
		if ($this->config->getValue("Contact", "adradd"))
			$out .= "<br>" . $this->config->getValue("Contact", "adradd");
	}
	
	if ($db->f("Strasse")) {
		$out .= "<br><br>" . htmlReady($db->f("Strasse"), TRUE);
		if($db->f("Plz"))
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
			$home = trim(FixLinks(htmlReady($db->f("Home")), TRUE, TRUE, FALSE, TRUE));
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
			$out .= htmlReady($db->f("sprechzeiten")) . "</font></td></tr>\n";
		}
	}
	$out .= "</table>\n";
	
	return $out;
}				

?>
