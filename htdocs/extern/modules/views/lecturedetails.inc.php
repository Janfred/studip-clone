<?
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "dates.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "functions.php");

$seminar_id = $args["seminar_id"];
$db = new DB_Institut();
$query = "SELECT * FROM seminare WHERE Seminar_id='$seminar_id'";
$db->query($query);

$order = $this->config->getValue("Main", "order");
$visible = $this->config->getValue("Main", "visible");
$aliases = $this->config->getValue("Main", "aliases");
$j = -1;
if ($db->next_record()) {

	$data["name"] = htmlReady($db->f("Name"));
	
	if ($visible[++$j] && $db->f("Untertitel"))
		$data["subtitle"] = htmlReady($db->f("Untertitel"));
	
	if ($visible[++$j]) {
		$lecturer_link = $this->getModuleLink("Persondetails",
				$this->config->getValue("LecturerLink", "config"),
				$this->config->getValue("LecturerLink", "srilink"));
		$name_sql = $GLOBALS['_fullname_sql'][$this->config->getValue("Main", "nameformat")];
		$db_lecturer = new DB_Institut();
		$db_lecturer->query("SELECT $name_sql AS name, username FROM seminar_user su LEFT JOIN
				auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id)
				WHERE su.Seminar_id=\"$seminar_id\" AND su.status=\"dozent\"");
		while ($db_lecturer->next_record()) {
			$data["lecturer"][] = sprintf("<a href=\"%s&username=%s\"%s>%s</a>",
					$lecturer_link, $db_lecturer->f("username"),
					$this->config->getAttributes("LinkInternSimple", "a"),
					$db_lecturer->f("name"));
		}
		if (is_array($data["lecturer"]))
			$data["lecturer"] = implode(", ", $data["lecturer"]);
	}
	
	if ($visible[++$j] && $db->f("art"))
		$data["art"] = htmlReady($db->f("art"));
	
	if ($visible[++$j])
		$data["status"] = htmlReady($GLOBALS["SEM_TYPE"][$db->f("status")]["name"]);
	
	if ($visible[++$j] && $db->f("Beschreibung"))
		$data["description"] = htmlReady($db->f("Beschreibung"));
	
	if ($visible[++$j])
		$data["location"] = getRoom($seminar_id, FALSE);
	
	if ($visible[++$j]) {
		$data["time"] = view_turnus($seminar_id);
		if ($first_app = vorbesprechung($seminar_id))
			$data["time"] = "Vorbesprechung: $first_app, " . $data["time"];
	}
	
	if ($visible[++$j] && $db->f("teilnehmer"))
		$data["teilnehmer"] = htmlReady($db->f("teilnehmer"));
	
	if ($visible[++$j] && $db->f("voraussetzungen"))
		$data["requirements"] = htmlReady($db->f("voraussetzungen"));
	
	if ($visible[++$j] && $db->f("lernorga"))
		$data["lernorga"] = htmlReady($db->f("lernorga"));
	
	if ($visible[++$j] && $db->f("leistungsnachweis"))
		$data["leistung"] = htmlReady($db->f("leistungsnachweis"));
	
	if ($visible[++$j]) {
		$pathes = get_sem_tree_path($seminar_id, ">");
		if (is_array($pathes)) {
			$pathes_values = array_values($pathes);
			if ($this->config->getValue("Main", "range") == "long")
				$data["range_path"] = $pathes_values;
			else {
				foreach ($pathes_values as $path)
					$data["range_path"][] = array_pop(explode(">", $path));
			}
			$data["range_path"] = array_filter($data["range_path"], "htmlReady");
			$data["range_path"] = implode("<br>", $data["range_path"]);
		}
	}
	
	if ($visible[$i++] && $db->f("Sonstiges"))
		$data["misc"] = htmlReady($db->f("Sonstiges"));
	
	echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">";
	echo "<tr" . $this->config->getAttributes("SemName", "tr") . ">";
	echo "<td" . $this->config->getAttributes("SemName", "td") . ">";
	
	if ($this->config->getValue("Main", "studiplink")) {
		echo "<div" . $this->config->getAttributes("StudipLink", "div") . ">";
		echo "<font" . $this->config->getAttributes("StudipLink", "font") . ">";
		$lnk = "http://{$GLOBALS['EXTERN_SERVER_NAME']}seminar_main.php?&auswahl=" . $seminar_id;;
		$lnk .= "&redirect_to=admin_seminare1.php&login=true&new_sem=TRUE";
		printf("<a href=\"%s\"%s target=\"_blank\">%s</a>", $lnk,
				$this->config->getAttributes("StudipLink", "a"),
				$this->config->getValue("StudipLink", "linktext"));
		if ($this->config->getValue("StudipLink", "image")) {
			if ($image_url = $this->config->getValue("StudipLink", "imageurl"))
				$img = "<img border=\"0\" align=\"absmiddle\" src=\"$image_url\">";
			else {
				$img = "<img border=\"0\" src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}";
				$img .= "pictures/login.gif\" align=\"absmiddle\">";
			}
			printf("&nbsp;<a href=\"%s\"%s target=\"_blank\">%s</a>", $lnk,
				$this->config->getAttributes("StudipLink", "a"), $img);
		}
		echo "</font></div>";
	}
	
	echo "<div" . $this->config->getAttributes("SemName", "div") . ">";
	echo "<font" . $this->config->getAttributes("SemName", "font") . ">";
	echo $data["name"] . "</font></div></td></tr>\n";
	
	$headline_tr = $this->config->getAttributes("Headline", "tr");
	$headline_td = $this->config->getAttributes("Headline", "td");
	$headline_font = $this->config->getAttributes("Headline", "font");
	$headline_margin = $this->config->getValue("Headline", "margin");
	$content_tr =$this->config->getAttributes("Content", "tr");
	$content_td = $this->config->getAttributes("Content", "td");
	$content_font = $this->config->getAttributes("Content", "font");
	$content_margin = $this->config->getValue("Content", "margin");
	
	foreach ($order as $position) {
		if ($visible[$position] && $data[$this->data_fields[$position]]) {
			echo "<tr$headline_tr><td$headline_td><div style=\"margin-left:$headline_margin;\">";
			echo "<font$headline_font>{$aliases[$position]}</font></div></td></tr>\n";
			echo "<tr$content_tr><td$content_td><div style=\"margin-left:$content_margin;\">";
			echo "<font$content_font>" . $data[$this->data_fields[$position]];
			echo "</font></div></td></tr>\n";
		}
	}


	if ($this->config->getValue("Main", "studipinfo")) {
		echo "<tr$headline_tr><td$headline_td><div style=\"margin-left:$headline_margin;\">";
		echo "<font$headline_font>" . $this->config->getValue("StudipInfo", "headline");
		echo "<font></div></td></tr>\n";
	
		$db->query("SELECT i.Institut_id, i.Name, i.url FROM seminare LEFT JOIN Institute i
								USING(institut_id) WHERE Seminar_id='$seminar_id'");
		$db->next_record();
		$own_inst = $db->f("Institut_id");
		
		$pre_font = $this->config->getAttributes("StudipInfo", "font");
		echo "<tr$content_tr><td$content_td><div style=\"margin-left:$content_margin;\">";
		echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "homeinst");
		echo "&nbsp;</font><font$content_font>";
		if ($db->f("url")) {
			$link_inst = htmlReady($db->f("url"));
			if (!preg_match('{^https?://.+$}', $link_inst))
				$link_inst = "http://$link_inst";
			printf("<a href=\"%s\"%s>%s</a>", $link_inst,
					$this->config->getAttributes("LinkInternSimple", "a"),
					htmlReady($db->f("Name")));
		}
		else
			echo htmlReady($db->f("Name"));
		echo "<br></font>\n";
	
		$db->query("SELECT Name, url FROM seminar_inst LEFT JOIN Institute i USING(institut_id)
								WHERE seminar_id='$seminar_id' AND i.institut_id!='$own_inst'");
		$involved_insts = NULL;
		while ($db->next_record()) {
			if ($db->f("url")) {
				$link_inst = htmlReady($db->f("url"));
				if (!preg_match('{^https?://.+$}', $link_inst))
					$link_inst = "http://$link_inst";
				$involved_insts[] = sprintf("<a href=\"%s\"%s>%s</a>",
						$link_inst, $this->config->getAttributes("LinkInternSimple", "a"),
						htmlReady($db->f("Name")));
			}
			else
				$involved_insts[] = $db->f("Name");
		}
		
		if ($involved_insts) {
			$involved_insts = implode(", ", $involved_insts);
			echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "involvedinst");
			echo "&nbsp;</font><font$content_font>";
			echo $involved_insts . "<br></font>\n";
		}
		
		$db->query("SELECT count(*) as count_user FROM seminar_user WHERE Seminar_id='$seminar_id'");
		$db->next_record();
		
		if ($db->f("count_user")) {
			echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countuser");
			echo "&nbsp;</font><font$content_font>";
			echo $db->f("count_user") . "<br></font>\n";
		}
		
		$db->query("SELECT count(*) as count_postings FROM px_topics WHERE Seminar_id='$seminar_id'");
		$db->next_record();
		
	 if ($db->f("count_postings")) {
			echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countpostings");
			echo "&nbsp;</font><font$content_font>";
			echo $db->f("count_postings") . "<br></font>\n";
		}
	
		$db->query("SELECT count(*) as count_documents FROM dokumente WHERE seminar_id='$seminar_id'");
		$db->next_record();
		
		if ($db->f("count_documents")) {
			echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countdocuments");
			echo "&nbsp;</font><font$content_font>";
			echo $db->f("count_documents") . "<br></font>\n";
		}
		echo "</div></td></tr>";
	}
	
	echo "</table>\n";
}
?>

