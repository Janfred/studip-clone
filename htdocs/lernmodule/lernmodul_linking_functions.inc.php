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
			$link_str .= "<a href=\"";
			$link_str .= link_use_module($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			$link_str .= "\" target=\"_blank\">";
			$link_str .= "<b>" . $mod_info["title"] . "</b> - " . $mod_info["description"] . "<br>";
			$link_str .= "</a>";
		}
		return $link_str;
	}
	else
		return false;
}

function link_use_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "course.php?co_id=$co_id&co_inst=$co_inst" . get_ilias_logindata();
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