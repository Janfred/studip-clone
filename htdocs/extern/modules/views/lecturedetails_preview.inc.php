<?
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "visual.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "dates.inc.php");
require_once($GLOBALS["ABSOLUTE_PATH_STUDIP"] . "functions.php");

global $SEM_CLASS, $SEM_TYPE;

// reorganize the $SEM_TYPE-array
foreach ($SEM_CLASS as $key_class => $class) {
	$i = 0;
	foreach ($SEM_TYPE as $key_type => $type) {
		if ($type["class"] == $key_class) {
			$i++;
			$sem_types_position[$key_type] = $i;
		}
	}
}

$data_sem["name"] = _("Name der Veranstaltung");
$data_sem["subtitle"] = _("Untertitel der Veranstaltung");
switch ($this->config->getValue("Main", "nameformat")) {
	case "no_title_short" :
		$data_sem["lecturer"] = _("Meyer, P.");
		break;
	case "no_title" :
		$data_sem["lecturer"] = _("Peter Meyer");
		break;
	case "no_title_rev" :
		$data_sem["lecturer"] = _("Meyer Peter");
		break;
	case "full" :
		$data_sem["lecturer"] = _("Dr. Peter Meyer");
		break;
	case "full_rev" :
		$data_sem["lecturer"] = _("Meyer, Peter, Dr.");
		break;
}
$data_sem["art"] = _("Testveranstaltung");
$data_sem["semtype"] = 1;
$data_sem["description"] = str_repeat(_("Beschreibung") . " ", 10);
$data_sem["location"] = _("A 123, 1. Stock");
$data_sem["time"] = _("Di. 8:30 - 13:30, Mi. 8:30 - 13:30, Do. 8:30 - 13:30");
$data_sem["teilnehmer"] = str_repeat(_("Teilnehmer") . " ", 6);
$data_sem["requirements"] = str_repeat(_("Voraussetzungen") . " ", 6);
$data_sem["lernorga"] = str_repeat(_("Lernorganisation") . " ", 6);
$data_sem["leistung"] = str_repeat(_("Leistungsnachweis") . " ", 6);
$data_sem["range_path"] = _("Fakult&auml;t &gt; Studiengang &gt; Bereich");
$data_sem["misc"] = str_repeat(_("Sonstiges") . " ", 6);


setlocale(LC_TIME, $this->config->getValue("Main", "timelocale"));
$order = $this->config->getValue("Main", "order");
$visible = $this->config->getValue("Main", "visible");
$aliases = $this->config->getValue("Main", "aliases");
$j = -1;

$data["name"] = $data_sem["name"];

if ($visible[++$j])
	$data["subtitle"] = $data_sem["subtitle"];

if ($visible[++$j]) {
	$data["lecturer"][] = sprintf("<a href=\"\"%s>%s</a>",
			$this->config->getAttributes("LinkInternSimple", "a"),
			$data_sem["lecturer"]);
	if (is_array($data["lecturer"]))
		$data["lecturer"] = implode(", ", $data["lecturer"]);
}

if ($visible[++$j])
	$data["art"] = $data_sem["art"];

if ($visible[++$j]) {
	$aliases_sem_type = $this->config->getValue("ReplaceTextSemType",
			"class_{$SEM_TYPE[$data_sem['semtype']]['class']}");
	if ($aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1])
		$data["status"] = $aliases_sem_type[$sem_types_position[$data_sem['semtype']] - 1];
	else {
		$data["status"] = htmlReady($SEM_TYPE[$data_sem['semtype']]["name"]
				." (". $SEM_CLASS[$SEM_TYPE[$data_sem['semtype']]["class"]]["name"].")");
	}
}

if ($visible[++$j])
	$data["description"] = $data_sem["description"];

if ($visible[++$j])
	$data["location"] = $data_sem["location"];

if ($visible[++$j]) {
	$data["time"] = $data_sem["time"];
}

if ($visible[++$j])
	$data["teilnehmer"] = $data_sem["teilnehmer"];

if ($visible[++$j])
	$data["requirements"] = $data_sem["requirements"];

if ($visible[++$j])
	$data["lernorga"] = $data_sem["lernorga"];

if ($visible[++$j])
	$data["leistung"] = $data_sem["leistung"];

if ($visible[++$j]) {
	$pathes = array($data_sem["range_path"]);
	if (is_array($pathes)) {
		$pathes_values = array_values($pathes);
		if ($this->config->getValue("Main", "range") == "long")
			$data["range_path"] = $pathes_values;
		else {
			foreach ($pathes_values as $path)
				$data["range_path"][] = array_pop(explode("&gt;", $path));
		}
		$data["range_path"] = array_filter($data["range_path"], "htmlReady");
		$data["range_path"] = implode("<br>", $data["range_path"]);
	}
}

if ($visible[$i++])
	$data["misc"] = $data_sem["misc"];

if ($this->config->getValue("Main", "studiplink") == "top") {
	$args = array("width" => $this->config->getValue("TableHeader", "table_width"),
			"align" => $this->config->getValue("TableHeader", "table_align"), "valign" => "top",
	"height" => "40", "link" => "");
	$this->elements["StudipLink"]->printout($args);
	echo "<br>";
}

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">";
echo "<tr" . $this->config->getAttributes("SemName", "tr") . ">";
echo "<td" . $this->config->getAttributes("SemName", "td") . ">";

if ($this->config->getValue("Main", "studiplink")) {
	echo "<div" . $this->config->getAttributes("StudipLink", "div") . ">";
	echo "<font" . $this->config->getAttributes("StudipLink", "font") . ">";
	printf("<a href=\"\"%s>%s</a>",
			$this->config->getAttributes("StudipLink", "a"),
			$this->config->getValue("StudipLink", "linktext"));
	if ($this->config->getValue("StudipLink", "image")) {
		if ($image_url = $this->config->getValue("StudipLink", "imageurl"))
			$img = "<img border=\"0\" align=\"absmiddle\" src=\"$image_url\">";
		else {
			$img = "<img border=\"0\" src=\"{$GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']}";
			$img .= "pictures/login.gif\" align=\"absmiddle\">";
		}
		printf("&nbsp;<a href=\"\"%s>%s</a>",
			$this->config->getAttributes("StudipLink", "a"), $img);
	}
	echo "</font></div>";
}

if ($margin = $this->config->getValue("SemName", "margin"))
	echo "<div style=\"margin-left:{$margin}px;\">";
else
	echo "<div>";
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
	
	$pre_font = $this->config->getAttributes("StudipInfo", "font");
	echo "<tr$content_tr><td$content_td><div style=\"margin-left:$content_margin;\">";
	echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "homeinst");
	echo "&nbsp;</font><font$content_font>";
	printf("<a href=\"\"%s>%s</a>",
			$this->config->getAttributes("LinkInternSimple", "a"),
			_("Heimatinstitut"));
	echo "<br></font>\n";
	
	echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "involvedinst");
	echo "&nbsp;</font><font$content_font>";
	echo str_repeat(_("Beteiligte Institute") . " ", 5) . "<br></font>\n";
	
	echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countuser");
	echo "&nbsp;</font><font$content_font>";
	echo "23<br></font>\n";
	
	echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countpostings");
	echo "&nbsp;</font><font$content_font>";
	echo "42<br></font>\n";

	echo "<font$pre_font>" . $this->config->getValue("StudipInfo", "countdocuments");
	echo "&nbsp;</font><font$content_font>";
	echo "7<br></font>\n";
	echo "</div></td></tr>";
}

echo "</table>\n";

if ($this->config->getValue("Main", "studiplink") == "bottom") {
	echo "<br>";
	$args = array("width" => $this->config->getValue("TableHeader", "table_width"),
			"align" => $this->config->getValue("TableHeader", "table_align"), "valign" => "bottom",
	"height" => "40", "link" => "");
	$this->elements["StudipLink"]->printout($args);
}
?>

