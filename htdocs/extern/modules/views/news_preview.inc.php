<?
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");

$error_message = "";

$nameformat = $this->config->getValue("Main", "nameformat");

if ($nameformat == "last") {
	$query = "SELECT n.*, aum.Nachname AS name, aum.username FROM news_range nr LEFT JOIN ";
	$query .= "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) ";
	$query .= "WHERE range_id='{$this->config->range_id}'";
}
else {
	global $_fullname_sql;
	$query = "SELECT n.*, {$_fullname_sql[$nameformat]} AS name, ";
	$query .= "aum.username FROM news_range nr LEFT JOIN ";
	$query .= "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) ";
	$query .= "WHERE range_id='{$this->config->range_id}'";
}

$now = time();
$data = NULL;
for ($n = 0; $n < 3; $n++) {
	$content_data[$n]["date"] = $now - 600000 * ($n + 1);
	$content_data[$n]["topic"] = sprintf(_("Aktuelle Nachricht Nr. %s"), $n + 1);
	$content_data[$n]["body"] = str_repeat(sprintf(_("Beschreibung der Nachricht Nr. %s"), $n + 1) . " ", 10);
	switch ($nameformat) {
		case "no_title_short" :
			$content_data[$n]["fullname"] = _("Meyer, P.");
			break;
		case "no_title" :
			$content_data[$n]["fullname"] = _("Peter Meyer");
			break;
		case "no_title_rev" :
			$content_data[$n]["fullname"] = _("Meyer Peter");
			break;
		case "full" :
			$content_data[$n]["fullname"] = _("Dr. Peter Meyer");
			break;
		case "full_rev" :
			$content_data[$n]["fullname"] = _("Meyer, Peter, Dr.");
			break;
		case "last" :
			$content_data[$n]["fullname"] = _("Meyer");
			break;
	}
}

$sort = $this->config->getValue("Main", "sort");
sort($sort, SORT_NUMERIC);

echo "<table" . $this->config->getAttributes("TableHeader", "table") . ">\n";
echo "<tr" . $this->config->getAttributes("TableHeadRow", "tr") . ">\n";

$rf_news = $this->config->getValue("Main", "order");
$width = $this->config->getValue("Main", "width");
if ($this->config->getValue("TableHeader", "width_pp") == "PERCENT")
	$percent = "%";
$aliases = $this->config->getValue("Main", "aliases");
$visible = $this->config->getValue("Main", "visible");

$set_1 = $this->config->getAttributes("TableHeadrow", "th");
$set_2 = $this->config->getAttributes("TableHeadrow", "th", TRUE);
$zebra = $this->config->getValue("TableHeadrow", "th_zebrath_");

if ($this->config->getValue("Main", "studiplink")) {
	$colspan = array_count_values($visible);
	echo "<th$set_1 colspan=\"{$colspan['1']}\">";
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
	echo "</font></div></th></tr>\n";
	echo "<tr" . $this->config->getAttributes("TableHeadRow", "tr") . ">\n";
}

$i = 0;
foreach($rf_news as $spalte){
	if ($visible[$spalte]) {
	
		// "zebra-effect" in head-row
		if ($zebra) {
			if ($i % 2)
				$set = $set_2;
			else
				$set = $set_1;
		}
		else
			$set = $set_1;
		
		echo "<th$set width=\"" . $width[$spalte] . "$percent\">";
		
		if($aliases[$spalte] == "")
			echo "<b>&nbsp;</b>\n";
		else 
			echo "<font" . $this->config->getAttributes("TableHeadrow", "font") . ">" . $aliases[$spalte] . "</font>\n";
	
		echo "</th>\n";
		$i++;
	}
}
echo "</tr>\n";

$dateform = $this->config->getValue("Main", "dateformat");
$attr_a = $this->config->getAttributes("Link", "a");
$attr_font = $this->config->getAttributes("Link", "font");
$attr_div_topic = $this->config->getAttributes("ContentNews", "divtopic");
$attr_div_body = $this->config->getAttributes("ContentNews", "divbody");
$attr_font_topic = $this->config->getAttributes("ContentNews", "fonttopic");
$attr_font_body = $this->config->getAttributes("ContentNews", "fontbody");

$set_1 = $this->config->getAttributes("TableRow", "td");
$set_2 = $this->config->getAttributes("TableRow", "td", TRUE);
$zebra = $this->config->getValue("TableRow", "td_zebratd_");

foreach ($content_data as $dat) {
	list ($content,$admin_msg) = explode("<admin_msg>",$dat["body"]);
	if ($admin_msg) 
		$content.="\n--%%{$admin_msg}%%--";
		
	$data = array(
			"date" => sprintf("%s<br><a href=\"\"%s><font%s>(%s)</font></a>",
													strftime($dateform, $dat["date"]),
													$attr_a, $attr_font, $dat["fullname"]),
			"topic" => sprintf("<div%s><font%s>%s</font></div><div%s><font%s>%s</font></div>",
													$attr_div_topic, $attr_font_topic,
													$dat["topic"], $attr_div_body,
													$attr_font_body, $content)
	);
	
	// "horizontal zebra"
	if ($zebra == "HORIZONTAL") {
		if ($i % 2)
			$set = $set_2;
		else
			$set = $set_1;
	}
	else
		$set = $set_1;
	
	echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
	
	$j = 0;
	foreach($rf_news as $spalte){
		
		// "vertical zebra"
		if ($zebra == "VERTICAL") {
			if ($j % 2)
				$set = $set_2;
			else
				$set = $set_1;
		}
	
		if ($visible[$spalte]) {
			if($data[$this->data_fields[$spalte]] == "")
				echo "<td$set>&nbsp;</td>\n";
			else
				echo "<td$set>" . $data[$this->data_fields[$spalte]] . "</td>\n";
			$j++;
		}
	}
	
	echo "</tr>\n";
	$i++;
}

echo "\n</table>";


?>
