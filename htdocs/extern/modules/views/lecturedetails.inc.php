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
				$this->config->getValue("LinkInternSimple", "config"),
				$this->config->getValue("LinkInternSimple", "srilink"));
		$name_sql = $GLOBALS['_fullname_sql'][$this->config->getValue("Main", "nameformat")];
		$db_lecturer = new DB_Institut();
		$db_lecturer->query("SELECT $name_sql AS name, username FROM seminar_user su LEFT JOIN
				auth_user_md5 USING(user_id) LEFT JOIN user_info USING(user_id)
				WHERE su.Seminar_id=\"$seminar_id\" AND su.status=\"dozent\"");
		while ($db_lecturer->next_record()) {
			$data["lecturer"][] = sprintf("<a href=\"%s&username=%s&seminar_id=%s\"%s>%s</a>",
					$lecturer_link, $db_lecturer->f("username"), $seminar_id,
					$this->config->getAttributes("LinkInternSimple", "a"),
					$db_lecturer->f("name"));
		}
		if (is_array($data["lecturer"]))
			$data["lecturer"] = implode(", ", $data["lecturer"]);
	}
	
	if ($visible[++$j] && $db->f("art"))
		$data["art"] = htmlReady($db->f("art"));
	
	if ($visible[++$j]) {
		// reorganize the $SEM_TYPE-array
		foreach ($GLOBALS["SEM_CLASS"] as $key_class => $class) {
			$i = 0;
			foreach ($GLOBALS["SEM_TYPE"] as $key_type => $type) {
				if ($type["class"] == $key_class) {
					$i++;
					$sem_types_position[$key_type] = $i;
				}
			}
		}
		
		$aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
				"class_" . $GLOBALS["SEM_TYPE"][$db->f("status")]['class']);
		if ($aliases_sem_type[$sem_types_position[$db->f("status")] - 1])
			$data["status"] =  $aliases_sem_type[$sem_types_position[$db->f("status")] - 1];
		else
			$data["status"] = htmlReady($GLOBALS["SEM_TYPE"][$db->f("status")]["name"]);
	}
	
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
		$pathes = get_sem_tree_path($seminar_id, "^");
		if (is_array($pathes)) {
			$range_path_level = $this->config->getValue("Main", "rangepathlevel");
			$pathes_values = array_values($pathes);
			foreach ($pathes_values as $path) {
				$range_path_new = NULL;
				$path = explode("^", $path);
				if ($range_path_level > sizeof($path))
					$range_path_level = sizeof($path);
				for ($i = $range_path_level - 1; $i < sizeof($path); $i++)
					$range_path_new[] = $path[$i];
				$range_pathes[] = htmlReady(implode(" > ", $range_path_new));
				
			}
			$data["range_path"] = implode("<br>", $range_pathes);
		}
	}
	
	if ($visible[$i++] && $db->f("Sonstiges"))
		$data["misc"] = htmlReady($db->f("Sonstiges"));
	
	if ($this->config->getValue("Main", "studiplink")) {
		echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ";
		echo "width=\"" . $this->config->getValue("TableHeader", "table_width");
		echo " align=\"" . $this->config->getValue("TableHeader", "table_align") . "\">\n";

		$studip_link = "http://{$GLOBALS['EXTERN_SERVER_NAME']}seminar_main.php?&auswahl=";
		$studip_link .= $seminar_id . "&redirect_to=admin_seminare1.php&login=true&new_sem=TRUE";
		if ($this->config->getValue("Main", "studiplink") == "top") {
			$args = array("width" => "100%", "height" => "40", "link" => $studip_link);
			echo "<tr><td width=\"100%\">\n";
			$this->elements["StudipLink"]->printout($args);
			echo "</td></tr>";
		}
		$table_attr = $this->config->getAttributes("TableHeader", "table");
		$pattern = array("/width=\"[0-9%]+\"/", "/align=\"[a-z]+\"/");
		$replace = array("width=\"100%\"", "");
		$table_attr = preg_replace($pattern, $replace, $table_attr);
		echo "<tr><td width=\"100%\">\n<table$table_attr>\n";
	}
	else
		echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
		
	echo "<tr" . $this->config->getAttributes("SemName", "tr") . ">";
	echo "<td" . $this->config->getAttributes("SemName", "td") . ">";
	
	if ($margin = $this->config->getValue("SemName", "margin"))
		echo "<div style=\"margin-left:{$margin}px;\">";
	else
		echo "<div>";
	echo "<font" . $this->config->getAttributes("SemName", "font") . ">";
	echo $data["name"] . "</font></div></td></tr>\n";
	
	$headline_tr = $this->config->getAttributes("Headline", "tr");
	$headline_td = $this->config->getAttributes("Headline", "td");
	$headline_font = $this->config->getAttributes("Headline", "font");
	if ($headline_margin = $this->config->getValue("Headline", "margin")) {
		$headline_div = "<div style=\"margin-left:$headline_margin;\">";
		$headline_div_end = "</div>";
	}
	else {
		$headline_div = "";
		$headline_div_end = "";
	}
	$content_tr =$this->config->getAttributes("Content", "tr");
	$content_td = $this->config->getAttributes("Content", "td");
	$content_font = $this->config->getAttributes("Content", "font");
	if ($content_margin = $this->config->getValue("Content", "margin")) {
		$content_div = "<div style=\"margin-left:$content_margin;\">";
		$content_div_end = "</div>";
	}
	else {
		$content_div = "";
		$content_div_end = "";
	}
	
	foreach ($order as $position) {
		if ($visible[$position] && $data[$this->data_fields[$position]]) {
			echo "<tr$headline_tr><td$headline_td>$headline_div";
			echo "<font$headline_font>{$aliases[$position]}</font>$headline_div_end</td></tr>\n";
			echo "<tr$content_tr><td$content_td>$content_div";
			echo "<font$content_font>" . $data[$this->data_fields[$position]];
			echo "</font>$content_div_end</td></tr>\n";
		}
	}


	if ($this->config->getValue("Main", "studipinfo")) {
		echo "<tr$headline_tr><td$headline_td>$headline_div";
		echo "<font$headline_font>" . $this->config->getValue("StudipInfo", "headline");
		echo "<font>$headline_div_end</td></tr>\n";
	
		$db->query("SELECT i.Institut_id, i.Name, i.url FROM seminare LEFT JOIN Institute i
								USING(institut_id) WHERE Seminar_id='$seminar_id'");
		$db->next_record();
		$own_inst = $db->f("Institut_id");
		
		$pre_font = $this->config->getAttributes("StudipInfo", "font");
		echo "<tr$content_tr><td$content_td>$content_div";
		echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "homeinst");
		echo "&nbsp;</font><font$content_font>";
		if ($db->f("url")) {
			$link_inst = htmlReady($db->f("url"));
			if (!preg_match('{^https?://.+$}', $link_inst))
				$link_inst = "http://$link_inst";
			printf("<a href=\"%s\"%s target=\"_blank\">%s</a>", $link_inst,
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
				$involved_insts[] = sprintf("<a href=\"%s\"%s target=\"_blank\">%s</a>",
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
			echo $db->f("count_documents") . "</font>\n";
		}
		echo "$content_div_end</td></tr>";
	}
	
	echo "</table>\n";
	
	if ($this->config->getValue("Main", "studiplink")) {
		if ($this->config->getValue("Main", "studiplink") == "bottom") {
			$args = array("width" => "100%", "height" => "40", "link" => $studip_link);
			echo "</td></tr>\n<tr><td width=\"100%\">\n";
			$this->elements["StudipLink"]->printout($args);
		}
		echo "</td></tr></table>\n";
	}
}
?>

