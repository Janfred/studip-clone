<?

function xml_header()
{
global $UNI_NAME_CLEAN, $SEMESTER, $SEM_ID, $ex_type, $ex_sem;
	$xml_tag_string = "<" . "?xml version=\"1.0\"?>\n";
	//encoding=\"ISO-8859-1\" encoding=\"UTF-8\";
//	$xml_tag_string .= "<!DOCTYPE StudIP SYSTEM \"http://goettingen.studip.de/studip.dtd\">\n";
	$xml_tag_string .= "<studip";
	if ($UNI_NAME_CLEAN != "") $xml_tag_string .= " uni=\"" . $UNI_NAME_CLEAN . "\"";
	if ($ex_type !="veranstaltung") 
		$xml_tag_string .= " zeitraum=\"" . $SEMESTER[$SEM_ID]["name"] . "\"";
	elseif ($SEMESTER[$ex_sem]["name"] != "") $xml_tag_string .= " zeitraum=\"" . $SEMESTER[$ex_sem]["name"] . "\"";
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
		. htmlspecialchars (kill_format( $tag_content ) )
		. "</" . $tag_name .  ">\n";
	return $xml_tag_string;
}

function xml_footer()
{
	$xml_tag_string = "</studip>";
	return $xml_tag_string;
}

?>