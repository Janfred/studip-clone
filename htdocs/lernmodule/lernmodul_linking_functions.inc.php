<?
function link_new_module()
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=new" . get_ilias_logindata();
}

function link_seminar_modules($seminar_id)
{
	global $auth, $perm;
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{
		for ($i=0; $i<sizeof($mod_array); $i ++)
		{
			$mod_info = get_module_info($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			$link_str[$i]["image"] .= "<a href=\"";
			$link_str[$i]["image"] .= link_use_module($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			$link_str[$i]["image"] .= "\" target=\"_blank\">";
			$link_str[$i]["image"] .= "<img src=\"./pictures/icon-lern.gif\" border=0>";
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
			if ($mod_info["pages"] != 1) 
				$link_str[$i]["content"] .= sprintf(_("Diese Lerneinheit enth&auml;lt %s Seiten. "), $mod_info["pages"]);
			else
				$link_str[$i]["content"] .= _("Diese Lerneinheit enth&auml;lt eine Seite. ");
			if ($mod_info["questions"] == 1) 
				$link_str[$i]["content"] .= _("Es gibt eine Testfrage zu der Lerneinheit.");
			elseif ($mod_info["questions"] > 1) 
				$link_str[$i]["content"] .= sprintf(_("Es gibt %s Testfragen zu der Lerneinheit."), $mod_info["questions"]);
			$link_str[$i]["content"] .= "<br>";
			$link_str[$i]["key"] .= $mod_array[$i]["id"] . "@" . $mod_array[$i]["inst"];
			$mod_author = get_module_author($mod_array[$i]["inst"], $mod_array[$i]["id"]);
			for ($i2=0; $i2<sizeof($mod_author); $i2 ++)
			{
				if (($auth->auth["uname"] == get_studip_user($mod_author[$i2]["id"])) OR ($perm->have_studip_perm("admin",$seminar_id)))
				{
					$link_str[$i]["button"] = "<br><center><a href=\"" . link_edit_module($mod_array[$i]["inst"], $mod_array[$i]["id"]) . "\" target=\"_blank\">"
					. makeButton("bearbeiten", "img")."</a>&nbsp";
					$delete_link = $PHP_SELF . "?delete=now&del_inst=".$mod_array[$i]["inst"]."&del_id=".$mod_array[$i]["id"]."&del_title=".$mod_info["title"];
					$link_str[$i]["button"] .= "<a href=\"" . $delete_link . "\">"
					. makeButton("bearbeiten", "img")."</a></center>";
				}
				if (get_studip_user($mod_author[$i2]["id"]) == false)
					$mod_desc[$i2] = $mod_author[$i2]["fullname"];
				else
					$mod_desc[$i2] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i2]["id"]). "\">" . $mod_author[$i2]["fullname"] . "</a>";
			}
			$link_str[$i]["desc"] .= implode($mod_desc, ", ");
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