<?

require_once("$ABSOLUTE_PATH_STUDIP/lib/classes/SemesterData.class.php");

function xml_header()
{
global $UNI_NAME_CLEAN, $SEM_ID, $SOFTWARE_VERSION, $ABSOLUTE_PATH_STUDIP, $ex_type, $ex_sem, $range_name, $range_id;
	$semester = new SemesterData;
	$all_semester = $semester->getAllSemesterData();
	$xml_tag_string = "<" . "?xml version=\"1.0\"?>\n";
	//encoding=\"ISO-8859-1\" encoding=\"UTF-8\";
//	$xml_tag_string .= "<!DOCTYPE StudIP SYSTEM \"http://goettingen.studip.de/studip.dtd\">\n";
	$xml_tag_string .= "<studip version=\"" . htmlspecialchars ($SOFTWARE_VERSION) . "\" logo=\"". htmlspecialchars ($ABSOLUTE_PATH_STUDIP . "pictures/logo2b.gif") . "\"";
	if ($range_id == "root") $xml_tag_string .= " range=\"" . _("Alle Einrichtungen") . "\"";
	elseif ($range_name != "") $xml_tag_string .= " range=\"" . htmlspecialchars ($range_name) . "\"";
	if ($UNI_NAME_CLEAN != "") $xml_tag_string .= " uni=\"" . htmlspecialchars ($UNI_NAME_CLEAN) . "\"";
	if ($ex_type !="veranstaltung") 
		$xml_tag_string .= " zeitraum=\"" . htmlspecialchars ($all_semester[$SEM_ID]["name"]) . "\"";
	elseif ($all_semester[$ex_sem]["name"] != "") $xml_tag_string .= " zeitraum=\"" . htmlspecialchars ($all_semester[$ex_sem]["name"]) . "\"";
	$xml_tag_string .= ">\n";
	return $xml_tag_string;
}

function xml_open_tag($tag_name, $tag_key = "")
{
	if ($tag_key != "")  
		$xml_tag_string .= " key=\"" . htmlspecialchars ($tag_key ) ."\"" ;
	$xml_tag_string = "<" . $tag_name . $xml_tag_string .  ">\n";
	return $xml_tag_string;
}

function xml_close_tag($tag_name)
{
	$xml_tag_string = "</" . $tag_name .  ">\n";
	return $xml_tag_string;
}

function xml_tag($tag_name, $tag_content)
{
	$xml_tag_string = "<" . $tag_name . $xml_tag_string .  ">" 
		. htmlspecialchars ( $tag_content )
		. "</" . $tag_name .  ">\n";
	return $xml_tag_string;
}

function xml_footer()
{
	$xml_tag_string = "</studip>";
	return $xml_tag_string;
}

?>
