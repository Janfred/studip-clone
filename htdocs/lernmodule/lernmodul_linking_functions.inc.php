<?
function link_new_module()
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=new" . get_ilias_logindata();
}

function link_seminar_modules($seminar_id)
{
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{
		for ($i=0; $i<sizeof($mod_array); $i ++)
		{
			$mod_info = get_module_info($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			$link_str[$i]["image"] .= "<a href=\"";
			$link_str[$i]["image"] .= link_use_module($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			$link_str[$i]["image"] .= "\" target=\"_blank\">";
			$link_str[$i]["image"] .= "<img src=\"./pictures/cont_blatt.gif\" border=0>";
			$link_str[$i]["image"] .= "</a>";
			$link_str[$i]["link"] .= "<a href=\"";
			$link_str[$i]["link"] .= link_use_module($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			$link_str[$i]["link"] .= "\" class=\"tree\" target=\"_blank\">";
			$link_str[$i]["link"] .= $mod_info["title"];
			$link_str[$i]["link"] .= " (" . $mod_info["pages"] . " Seite";
			if ($mod_info["pages"] != 1) 
				$link_str[$i]["link"] .= "n";
			$link_str[$i]["link"] .= ")";
			$link_str[$i]["link"] .= "</a>";
			$link_str[$i]["content"] .= $mod_info["description"] . "<br>";
			$link_str[$i]["content"] .= "Diese Lerneinheit enth&auml;lt " . $mod_info["pages"] . " Seite";
			if ($mod_info["pages"] != 1) 
				$link_str[$i]["content"] .= "n";
			if ($mod_info["questions"] != 0) 
				$link_str[$i]["content"] .= " und " . $mod_info["questions"] . " Frage";
			if ($mod_info["questions"] > 1) 
				$link_str[$i]["content"] .= "n";
			$link_str[$i]["content"] .= ".";
			$link_str[$i]["key"] .= $mod_array[$i]["id"] . "@" . $mod_array[$i]["inst"];
			$mod_author = get_module_author($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			for ($i2=0; $i2<sizeof($mod_author); $i2 ++)
			{
				$mod_author[$i2] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i2]["id"]). "\">" . $mod_author[$i2]["fullname"] . "</a>";
			}
			$link_str[$i]["desc"] .= implode($mod_author, ", ");
		}
		return $link_str;
	}
	else
		return false;
}

function link_use_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=use&co_id=$co_id&co_inst=$co_inst" . get_ilias_logindata();
}

function link_edit_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=edit&le=$co_id&le_inst=$co_inst" . get_ilias_logindata();
}

function link_delete_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=delete&le=$co_id" . get_ilias_logindata();
}


?>