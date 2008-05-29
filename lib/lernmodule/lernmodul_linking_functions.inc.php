<?
# Lifter002: TODO
/**
* Linking-functions to conect Stud.IP-Pages to ILIAS-learning-modules.
*
* This file contains several functions to create links and forms that refer to ILIAS-learning-modules.
*
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		lernmodul_linking_functions
* @package		ELearning
*/

/**
* Return link to ILIAS to create a new learning-module
*
* Returns a link to ILIAS to create a new learning-module
*
* @access	public
* @return		string	returns link-string
*/
function link_new_module()
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=new" . get_ilias_logindata();
}

/**
* Return links to ILIAS to start, edit or delete learning-modules
*
* Returns an array of links to ILIAS to start, edit or delete learning-modules
*
* @access	public
* @param		array		$this_array	Array of learning-module-IDs
* @param		string	$perm_area	Permission-Range
* @return		array		Array of learning-module-data
*/
function get_module_linkdata($this_array, $perm_area = 0)
{
	global $auth, $perm;

	$mod_info = get_module_info($this_array["inst"], $this_array["id"]);
	$data_str["image"] .= "<img src=\"".$GLOBALS['ASSETS_URL']."images/icon-lern.gif\" border=0>";
	$data_str["link"] .= $mod_info["title"];
	if ($mod_info["pages"] != 1)
		$data_str["link"] .= sprintf(_(" (%s Seiten)"), $mod_info["pages"]);
	else
		$data_str["link"] .= _(" (1 Seite)");
	$data_str["content"] .= $mod_info["description"] . "<br>";
	if ($mod_info["pages"] != 1)
		$data_str["content"] .= sprintf(_("Diese Lerneinheit enth&auml;lt %s Seiten. "), $mod_info["pages"]);
	else
		$data_str["content"] .= _("Diese Lerneinheit enth&auml;lt eine Seite. ");
	if ($mod_info["questions"] == 1)
		$data_str["content"] .= _("Es gibt eine Testfrage zu der Lerneinheit.");
	elseif ($mod_info["questions"] > 1)
		$data_str["content"] .= sprintf(_("Es gibt %s Testfragen zu der Lerneinheit."), $mod_info["questions"]);
	$data_str["content"] .= "<br>";
	$data_str["key"] .= $this_array["id"] . "@" . $this_array["inst"];

	$data_str["button"] = "<br><center><a href=\"" . link_use_module($this_array["inst"], $this_array["id"]) . "\" class=\"tree\" target=\"_blank\">"
		. makeButton("starten", "img")."</a>&nbsp";

	$mod_author = get_module_author($this_array["inst"], $this_array["id"]);
	$mod_desc = "";
	for ($i=0; $i<sizeof($mod_author); $i ++)
	{
		if (($auth->auth["uname"] == get_studip_user($mod_author[$i]["id"])) OR ($perm->have_studip_perm("admin",$perm_area)))
		{
			$data_str["button"] .= "<a href=\"" . link_edit_module($this_array["inst"], $this_array["id"]) . "\" target=\"_blank\">"
			. makeButton("bearbeiten", "img")."</a>&nbsp";
			$delete_link = $PHP_SELF . "?delete=now&del_inst=".$this_array["inst"]."&del_id=".$this_array["id"]."&del_title=".$mod_info["title"];
			$data_str["button"] .= "<a href=\"" . $delete_link . "\">"
			. makeButton("loeschen", "img")."</a>";
		}
		if (get_studip_user($mod_author[$i]["id"]) == false)
			$mod_desc[$i] = $mod_author[$i]["fullname"];
		else
			$mod_desc[$i] = "<a href=\"about.php?username=" . get_studip_user($mod_author[$i]["id"]). "\">" . $mod_author[$i]["fullname"] . "</a>";
	}
	$data_str["button"] .= "</center>";
	$data_str["desc"] .= implode($mod_desc, ", ");

	return $data_str;
}

/**
* Return links to ILIAS to start, edit or delete learning-modules that belong to the given seminar
*
* Returns an array of links to ILIAS to start, edit or delete learning-modules that belong to the given seminar
*
* @access	public
* @param		string	$seminar_id	Seminar-ID
* @return		string	Link-String or false
*/
function link_seminar_modules($seminar_id)
{
	$mod_array = get_seminar_modules($seminar_id);
	if ($mod_array != false)
	{
		for ($i=0; $i<sizeof($mod_array); $i ++)
		{
			$link_str[$i] = get_module_linkdata($mod_array[$i], $seminar_id);
			$link_str[$i]["status"] = $mod_array[$i]["status"];
		}
		return $link_str;
	}
	else
		return false;
}

/**
* Return link to ILIAS to start learning-module
*
* Returns a link to ILIAS to start the learning-module specified by the given IDs
*
* @access	public
* @param		integer	$co_inst	Ilias Inst ID
* @param		integer	$co_id	Ilias learning module ID
* @return		string	Link-String
*/
function link_use_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=use&co_id=$co_id&co_inst=$co_inst" . get_ilias_logindata();
}

/**
* Return link to ILIAS to edit learning-module
*
* Returns a link to ILIAS to edit the learning-module specified by the given IDs
*
* @access	public
* @param		integer	$co_inst	Ilias Inst ID
* @param		integer	$co_id	Ilias learning module ID
* @return		string	Link-String
*/
function link_edit_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=edit&le=$co_id&le_inst=$co_inst" . get_ilias_logindata();
}

/**
* Return link to ILIAS to delete learning-module
*
* Returns a link to ILIAS to delete the learning-module specified by the given IDs
*
* @access	public
* @param		integer	$co_inst	Ilias Inst ID
* @param		integer	$co_id	Ilias learning module ID
* @return		string	Link-String
*/
function link_delete_module($co_inst, $co_id)
{
	global $ABSOLUTE_PATH_ILIAS;
	return $ABSOLUTE_PATH_ILIAS . "studip2ilias.php?rdmode=delete&le=$co_id" . get_ilias_logindata();
}


?>