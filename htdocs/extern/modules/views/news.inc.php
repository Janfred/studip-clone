<?
require_once($ABSOLUTE_PATH_STUDIP . "visual.inc.php");

$db =& new DB_Seminar();
$error_message = "";

// stimmt die übergebene range_id?
$query = "SELECT Name FROM Institute WHERE Institut_id=\"{$this->config->range_id}\"";
$db->query($query);
if(!$db->next_record())
	$error_message = $GLOBALS["EXTERN_ERROR_MESSAGE"];

$sort = $this->config->getValue("Main", "sort");

$query_order = "";
foreach ($sort as $key => $position) {
	if ($position > 0)
		$query_order[$position] = $this->data_fields[$key];
}
if ($query_order) {
	ksort($query_order, SORT_NUMERIC);
	$query_order = " ORDER BY " . implode(",", $query_order) . " DESC";
}

if (!$nameformat = $this->config->getValue("Main", "nameformat"))
	$nameformat = "no_title";
$now = time();
if ($nametitel == "last") {
	$query = "SELECT n.*, aum.Nachname AS name, aum.username FROM news_range nr LEFT JOIN "
			. "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) "
			. "WHERE range_id='{$this->config->range_id}' AND n.date <= $now AND "
			. "n.date + n.expire >= $now" . $query_order;
}
else {
	global $_fullname_sql;
	$query = "SELECT n.*, {$_fullname_sql[$nameformat]} AS name, "
			. "aum.username FROM news_range nr LEFT JOIN "
			. "news n USING(news_id) LEFT JOIN auth_user_md5 aum USING(user_id) "
			. "LEFT JOIN user_info USING(user_id) "
			. "WHERE range_id='{$this->config->range_id}' AND n.date <= $now AND "
			. "n.date + n.expire >= $now" . $query_order;
}

$db->query($query);

if (!$db->num_rows())
	$error_message = $this->config->getValue("Main", "nodatatext");

if ($this->config->getValue("Main", "studiplink")) {
	echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" ";
	echo "width=\"" . $this->config->getValue("TableHeader", "table_width");
	echo "\" align=\"" . $this->config->getValue("TableHeader", "table_align") . "\">\n";

	$studip_link = "http://{$GLOBALS['EXTERN_SERVER_NAME']}institut_main.php?auswahl=" . $this->config->range_id;
	$studip_link .= "&redirect_to=admin_news.php&cmd=new_entry&view=inst&new_inst=TRUE&range_id=";
	$studip_link .= $this->config->range_id;
	if ($this->config->getValue("Main", "studiplink") == "top") {
		$args = array("width" => "100%",
		"height" => "40", "link" => $studip_link);
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

$i = 0;
$this->elements["TableHeadrow"]->printout();

// no data to print
if ($error_message) {
	echo "<tr" . $this->config->getAttributes("TableRow", "tr") . ">\n";
	echo "<td" . $this->config->getAttributes("TableRow", "td") . " colspan=\"$i\">\n";
	echo $error_message;
	echo "</td></tr>\n</table>\n";
}
else {
	$data["data_fields"] = $this->data_fields;
	$dateform = $this->config->getValue("Main", "dateformat");
	$show_date_author = $this->config->getValue("Main", "showdateauthor");
	$not_author_link = $this->config->getValue("Main", "notauthorlink");
	
	while($db->next_record()){
		list ($content,$admin_msg) = explode("<admin_msg>",$db->f("body"));
		if ($admin_msg) 
			$content.="\n--%%{$admin_msg}%%--";
		
		// !!! LinkInternSimple is not the type of this element,
		// the type of this element is LinkIntern !!!
		// this is for compatibiliy reasons only
		if ($show_date_author != 'date') {
			if ($not_author_link)
				$author_name = htmlReady($db->f("name"));
			else
				$author_name = $this->elements["LinkInternSimple"]->toString(array(
										"content" => htmlReady($db->f("name")),
										"link_args" => "username=" . $db->f("username"),
										"module" => "Persondetails"));
		}
		
		switch ($show_date_author) {
			case 'date' :
				$data["content"]["date"] = strftime($dateform, $db->f("date"));
				break;
			case 'author' :
				$data["content"]["date"] = $author_name;
				break;
			default :
				$data["content"]["date"] = strftime($dateform, $db->f("date")) . "<br>" . $author_name;
		}
				
		$data["content"]["topic"] = $this->elements["ContentNews"]->toString(array("content" =>
									array("topic" => htmlReady($db->f("topic")),
									"body" => formatReady($content, TRUE, TRUE, TRUE))));
		
		$this->elements["TableRow"]->printout($data);
	}
	
	echo "\n</table>";
}
if ($this->config->getValue("Main", "studiplink")) {
	if ($this->config->getValue("Main", "studiplink") == "bottom") {
		$args = array("width" => "100%",
		"height" => "40", "link" => $studip_link);
		echo "</td></tr>\n<tr><td width=\"100%\">\n";
		$this->elements["StudipLink"]->printout($args);
	}
	echo "</td></tr></table>\n";
}

?>
