<?
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");

$db = new DB_Institut();

$summenews = $db->num_rows();
$error_message = "";

// stimmt die übergebene range_id?
$query = "SELECT Name FROM Institute WHERE Institut_id=\"{$this->config->range_id}\"";
$db->query($query);
if(!$db->next_record())
	$error_message = $GLOBALS["EXTERN_ERROR_MESSAGE"];

$nameformat = $this->config->getValue("Main", "nameformat");

if ($nametitel == "last") {
	$query = "SELECT n.*, aum.Nachname AS name, aum.username FROM news_range nr LEFT JOIN ";
	$query .= "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) ";
	$query .= "WHERE range_id='{$this->config->range_id}'";
}
else {
	global $_fullname_sql;
	$query = "SELECT n.*, {$_fullname_sql[$nameformat]} AS name, ";
	$query .= "aum.username FROM news_range nr LEFT JOIN ";
	$query .= "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) ";
	$query .= "LEFT JOIN user_info USING(user_id) ";
	$query .= "WHERE range_id='{$this->config->range_id}'";
}

$sort = $this->config->getValue("Main", "sort");
sort($sort, SORT_NUMERIC);

$query_order = "";
foreach ($sort as $position) {
	if ($position > 0)
		$query_order .= " " . $this->data_fields[$position] . ",";
}

if ($query_order)
	$query .= " ORDER BY" . substr($query_order, 0, -1);

$db->query($query);

if (!$db->num_rows())
	$error_message = $this->config->getValue("Main", "nodatatext");

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
	$lnk = "http://{$GLOBALS['EXTERN_SERVER_NAME']}institut_main.php?auswahl=" . $this->config->range_id;
	$lnk .= "&redirect_to=admin_news.php&cmd=new_entry&view=inst&new_inst=TRUE&range_id=";
	$lnk .= $this->config->range_id;
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

// no data to print
if ($error_message) {
	echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
	echo "<td" . $this->config->getAttributes("TableRow", "td") . " colspan=\"$i\">\n";
	echo $error_message;
	echo "</td></tr>\n</table>\n";
}
else {
	$dateform = $this->config->getValue("Main", "dateformat");
	$attr_a = $this->config->getAttributes("LinkInternSimple", "a");
	$attr_font = $this->config->getAttributes("TableRow", "font");
	$attr_div_topic = $this->config->getAttributes("ContentNews", "divtopic");
	$attr_div_body = $this->config->getAttributes("ContentNews", "divbody");
	$attr_font_topic = $this->config->getAttributes("ContentNews", "fonttopic");
	$attr_font_body = $this->config->getAttributes("ContentNews", "fontbody");
	
	$link_persondetails = $this->getModuleLink("Persondetails",
			$this->config->getValue("LinkInternSimple", "config"), $this->config->getValue("LinkInternSimple", "srilink"));
	
	$set_1 = $this->config->getAttributes("TableRow", "td");
	$set_2 = $this->config->getAttributes("TableRow", "td", TRUE);
	$zebra = $this->config->getValue("TableRow", "td_zebratd_");
	
	while($db->next_record()){
		list ($content,$admin_msg) = explode("<admin_msg>",$db->f("body"));
		if ($admin_msg) 
			$content.="\n--%%{$admin_msg}%%--";
			
		$data = array(
				"date" => sprintf("<font%s>%s<br><a href=\"%s&username=%s\"%s>(%s)</a></font>",
														$attr_font, strftime($dateform, $db->f("date")), $link_persondetails,
														$db->f("username"),$attr_a, htmlReady($db->f("name"))),
				"topic" => sprintf("<div%s><font%s>%s</font></div><div%s><font%s>%s</font></div>",
														$attr_div_topic, $attr_font_topic,
														$db->f("topic"), $attr_div_body,
														$attr_font_body, formatReady($content))
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
}

?>
