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
$j = 0;
if ($db->next_record()) {

	$data["name"] = htmlReady($db->f("Name"));
	
	if ($visible[$j++] && $db->f("Untertitel"))
		$data["subtitle"] = htmlReady($db->f("Untertitel"));
	
	if ($visible[$j++]) {
		$lecturer_link = $this->getModuleLink("Persondetails",
				$this->config->getValue("LecturerLink", "config"),
				$this->config->getValue("LecturerLink", "srilink"));
		$name_sql = $GLOBALS['_fullname_sql'][$this->config->getValue("Main", "nameformat")];
		$db_lecturer = new DB_Institut();
		$db_lecturer->query("SELECT $name_sql AS name, username FROM seminar_user su LEFT JOIN
				auth_user_md5 USING(user_id) WHERE su.Seminar_id=\"$seminar_id\" AND su.status=\"dozent\"");
		while ($db_lecturer->next_record()) {
			$data["lecturer"][] = sprintf("<a href=\"%s&username=%s\"%s>%s</a>",
					$lecturer_link, $db_lecturer->f("username"),
					$this->config->getAttributes("LinkInternSimple", "a"),
					$db_lecturer->f("name"));
		}
		$data["lecturer"] = implode(", ", $data["lecturer"]);
	}
	
	if ($visible[$j++] && $db->f("art"))
		$data["art"] = htmlReady($db->f("art"));
	
	if ($visible[$j++])
		$data["status"] = htmlReady($GLOBALS["SEM_TYPE"][$db->f("status")]["name"]);
	
	if ($visible[$j++] && $db->f("Beschreibung"))
		$data["description"] = htmlReady($db->f("Beschreibung"));
	
	if ($visible[$i++])
		$data["location"] = getRoom($seminar_id, FALSE);
	
	if ($visible[$i++]) {
		$data["time"] = view_turnus($seminar_id);
		if ($first_app = vorbesprechung($seminar_id))
			$data["time"] = "Vorbesprechung: $first_app, " . $data["time"];
	}
	
	if ($visible[$i++] && $db->f("teilnehmer"))
		$data["teilnehmer"] = htmlReady($db->f("teilnehmer"));
	
	if ($visible[$i++] && $db->f("voraussetzungen"))
		$data["requirements"] = htmlReady($db->f("teilnehmer"));
	
	if ($visible[$i++] && $db->f("lernorga"))
		$data["lernorga"] = htmlReady($db->f("lernorga"));
	
	if ($visible[$i++] && $db->f("leistungsnachweis"))
		$data["leistung"] = htmlReady($db->f("leistungsnachweis"));
	
	if ($visible[$i++]) {
		$pathes = get_sem_tree_path($seminar_id);
		if (is_array($pathes))
			$data["range_path"] = implode("<br>", array_values($pathes));
	}
	
	if ($visible[$i++] && $db->f("Sonstiges"))
		$data["misc"] = htmlReady($db->f("Sonstiges"));
	
	echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">";
	echo "<tr" . $this->config->getAttributes("SemName", "tr") . ">";
	echo "<td" . $this->config->getAttributes("SemName", "td") . ">";
	echo "<div" . $this->config->getAttributes("SemName", "div") . ">";
	echo "<font" . $this->config->getAttributes("SemName", "font") . ">";
	echo $data["name"] . "</font></div></td></tr>\n";
/*	if ($change_link) {
		echo "<td align=\"right\">";//$hg_leinzel_titel>";
		echo "<a href=\"".$CANONICAL_RELATIVE_PATH_STUDIP."seminar_main?auswahl=$ID&redirect_to=admin_seminare1.php&login=true&new_sem=TRUE\"><font color=\"#$link_color\">$change_link</font></a>&nbsp";
		echo "<a href=\"".$CANONICAL_RELATIVE_PATH_STUDIP."seminar_main?auswahl=$ID&redirect_to=admin_seminare1.php&login=true&new_sem=TRUE\"><img src=\"pfeillink.gif\" border=\"0\" alt=\"$change_link\"></a>\n";
	}*/
	
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
			echo "<tr$headline_tr>";
			echo "<td$headline_td>";
			echo "<div style=\"margin-left:$headline_margin;\">";
			echo "<font$headline_font>";
			echo $aliases[$position];
			echo "</font></div></td></tr>\n";
			echo "<tr$content_tr>";
			echo "<td$content_td>";
			echo "<div style=\"margin-left:$content_margin;\">";
			echo "<font$content_font>";
			echo $data[$this->data_fields[$position]];
			echo "</font></div></td></tr>\n";
		}
	}
	
	echo "</table>\n";
}

if ($studipinfo == true) {

	echo "<br>";
	
	include($ABSOLUTE_PATH_EXTERN_MODULES . "studipinfo.inc.php");	
	
	if ($studiplogo) {
		if ($Schrift2 || $tfeinzel || $sgeinzel)
			printf("<font%s%s%s>", $Schrift2, $tfeinzel, $sgeinzel);
		echo "<br><br>\n";
		echo "<center>";
		echo "&nbsp;Anmeldung zur Veranstaltung sowie weitere Informationen wie<br>\n";
		echo "<b>&nbsp;Ablaufpl&auml;ne, Literaturlisten, Linksammlungen, Hausarbeiten und Diskussionsforen</b>\n";
		echo "<br>\n&nbsp;im Stud.IP System!\n";
		echo "<p><a href=\"http://134.76.148.29/seminar/sem_verify.php?id=$ID\">";
		
		if($studip_pic)
			echo "<img border=\"0\" src=\"$studip_pic\"></a></p>";
		else
			echo "<img border=\"0\" src=\"http://134.76.148.29/seminar/pictures/studipanim.gif\" width=\"414\" height=\"155\"></a></p>";
		
		echo "</center><br>\n";
		if ($Schrift2 || $tfeinzel || $sgeinzel)
			echo "</font>\n";
	}
}

?>
