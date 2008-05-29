<?
# Lifter002: TODO
/**
* DB-Functions for ILIAS-Connection.
* 
* In this file there are functions to fetch learning-module data from the database.
* 
* @author		Arne Schroeder <schroeder@data.quest.de>
* @version		$Id$
* @access		public
* @modulegroup		elearning_modules
* @module		lernmodul_db_functions
* @package		ELearning
*/

/**
* Search for learning modules
*
* This function searches for learning modules according to the given keyword
*
* @access	public        
* @param		string	$key	keyword
* @param		integer	$area	where to search
* @return		array		returns array of string or false
*/
function search_modules($key, $area = 4)
{
	switch($area)
	{
		case "1": $add_query = " AND meta.title LIKE '%" . $key . "%' "; break;
		case "2": $add_query = " AND meta.description LIKE '%" . $key . "%' "; break;
		case "3": $add_query = " AND (meta_author.author_firstname LIKE '%" . $key . "%' OR meta_author.author_surname LIKE '%" . $key . "%') "; break;
		default: $add_query = " AND (meta.title LIKE '%" . $key . "%' OR meta.description LIKE '%" . $key . "%' OR (meta_author.author_firstname LIKE '%" . $key . "%' OR meta_author.author_surname LIKE '%" . $key . "%')) ";
	}
	$mod_array = false;
	$ilias_db = New DB_Ilias;
	$module_count = 0;
	if (trim($key) <>"")
	{
		$ilias_db -> query("SELECT DISTINCT lerneinheit.id, lerneinheit.inst, meta.title, meta.description".
			" FROM lerneinheit LEFT JOIN meta USING(inst, id)  LEFT JOIN meta_author USING(inst, id, typ) ".
			" WHERE meta.status='final' ".
			" AND public IN('y','f') ".
			" AND meta.typ = 'le' " .
			" AND lerneinheit.deleted='0000-00-00 00:00:00'". $add_query);
		while ($ilias_db->next_record())
		{
			$mod_array[$module_count]["inst"] = $ilias_db -> f("inst");
			$mod_array[$module_count]["id"] = $ilias_db -> f("id");
			$mod_array[$module_count]["title"] = $ilias_db -> f("title");
			$mod_array[$module_count]["description"] = $ilias_db -> f("description");
			$module_count ++;
		}
	}
	if ($module_count<1)
		return false;
	else
		return $mod_array;
}


/**
* Get learning module data
*
* This function returns information about the learning module specified by the given IDs
*
* @access	public        
* @param		integer	$co_inst	Ilias Inst ID
* @param		integer	$co_id	Ilias learning module ID
* @return		array		returns array of string or false
*/
function get_module_info($co_inst, $co_id)
{
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT meta.id, meta.inst, meta.title, meta.description ".
			" FROM lerneinheit LEFT JOIN meta USING (id, inst)".
			" WHERE meta.status='final' ".
			" AND public IN('y','f') ".
			" AND meta.typ = 'le' ".
			" AND lerneinheit.deleted='0000-00-00 00:00:00'".
			" AND lerneinheit.id = '$co_id' ".
			" AND lerneinheit.inst = '$co_inst' ");
	if ($ilias_db->next_record())
	{
		$module_info["title"] = htmlReady($ilias_db -> f("title"));
		$module_info["description"] = htmlReady($ilias_db -> f("description"));
		$ilias_db -> query("SELECT id FROM page WHERE lerneinheit='$co_id' AND le_inst='$co_inst' AND pg_typ = 'le'");
		$module_info["pages"] = $ilias_db -> num_rows();
		$ilias_db -> query("SELECT id FROM page WHERE lerneinheit='$co_id' AND le_inst='$co_inst' AND pg_typ = 'mc'");
		$module_info["questions"] = $ilias_db -> num_rows();
		
		return $module_info;
	}
	return false;
}

/**
* Get learning module author
*
* This function returns an array of the authors of the learning module specified by the given IDs
*
* @access	public        
* @param		integer	$co_inst	Ilias Inst ID
* @param		integer	$co_id	Ilias learning-module ID
* @return		array		returns array of string or false
*/
function get_module_author($co_inst, $co_id)
{
	$module_author = false;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT benutzer.id, benutzer.benutzername, benutzer.vorname, benutzer.nachname, benutzer.atitel ".
			" FROM meta_author, benutzer".
			" WHERE meta_author.typ = 'le' ".
			" AND meta_author.id = '$co_id' ".
			" AND meta_author.inst = '$co_inst' ".
			" AND meta_author.author_local_id = benutzer.id ");
	$mcount = 0;
	while ($ilias_db->next_record())
	{
		$module_author[$mcount]["fullname"] .= $ilias_db -> f("atitel") . " " . $ilias_db -> f("vorname") . " " . $ilias_db -> f("nachname");
		$module_author[$mcount]["username"] .= $ilias_db -> f("benutzername");
		$module_author[$mcount]["id"] .= $ilias_db -> f("id");
		$mcount++;
	}
	return $module_author;
}

/**
* Get learning modules belonging to user
*
* This function returns an array of the learning module that belong to the given Stud.IP-user-ID
*
* @access	public        
* @param		string	$studip_id		StudIP User ID
* @return		array		returns array of string or false
*/
function get_user_modules($studip_id)
{
	$mod_array = false;
	$module_count = 0;
	$db = New DB_Seminar;
	$db->query("SELECT * FROM auth_user_md5  WHERE user_id ='$studip_id'");
	if ($db->next_record())
	{
//		$firstname = $db->f("Vorname");
//		$surname = $db->f("Nachname");
		$ilias_user_id = get_connected_user_id($studip_id);
	}
	else
	{		
		printf(_("Stud.IP-User wurde nicht gefunden.") . "<br>");
		return false;
	}
	if ($ilias_user_id == false)
	{		
		echo _("Sie sind nicht als User im angebundenen ILIAS-System eingetragen.") . "<br>";
		return false;
	}
//	echo $firstname . $surname . $ilias_user_id;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT meta.id, meta.inst, meta.title, meta.description ".
			" FROM lerneinheit LEFT JOIN meta USING(id, inst) LEFT JOIN meta_author USING(id, inst, typ) LEFT JOIN meta_contrib USING(id, inst, typ)".
			" WHERE meta.status='final' ".
			" AND lerneinheit.public = 'y' ".
			" AND meta.typ = 'le' ".
			" AND lerneinheit.deleted='0000-00-00 00:00:00'".
			" AND ((meta_author.author_local_id = '$ilias_user_id') ".
			" OR (meta_contrib.contrib_local_id = '$ilias_user_id'))"
			);
	while ($ilias_db->next_record())
	{
		$mod_array[$module_count]["inst"] = $ilias_db -> f("inst");
		$mod_array[$module_count]["id"] = $ilias_db -> f("id");
		$mod_array[$module_count]["title"] = $ilias_db -> f("title");
		$mod_array[$module_count]["description"] = $ilias_db -> f("description");
		$module_count ++;
	}
	if ($module_count<1)
		return false;
	else
		return $mod_array;
}

/**
* Get learning modules belonging to seminar
*
* This function returns an array of the learning module that belong to the given Stud.IP-Range-ID
*
* @access	public        
* @param		string	$seminar_id	StudIP Seminar ID
* @return		array		returns array of string or false
*/
function get_seminar_modules($seminar_id)
{
	$mod_array = false;
	$module_count = 0;
	$db = New DB_Seminar;
	$db -> query("SELECT co_inst, co_id, status FROM seminar_lernmodul WHERE seminar_id = '$seminar_id'");
	while ($db->next_record() AND (get_module_info($db -> f("co_inst"), $db -> f("co_id")) != false))
	{
		$mod_array[$module_count]["inst"] = $db -> f("co_inst");
		$mod_array[$module_count]["id"] = $db -> f("co_id");
		$mod_array[$module_count]["status"] = $db -> f("status");
		$module_count ++;
	}
	if ($module_count<1)
		return false;
	else
		return $mod_array;
}

/**
* Get all learning modules
*
* This function returns an array of all learning modules
*
* @access	public        
* @param		array		$hide mod	optional array of learning-modules that will not be shown
* @return		array 	returns array of string or false
*/
function get_all_modules($hide_mod = false)
{
	$mod_array = false;
	$module_count = 0;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT lerneinheit.id, lerneinheit.inst, meta.title, meta.description".
			" FROM lerneinheit, meta ".
			" WHERE meta.status='final' ".
			" AND public IN('y','f') ".
			" AND meta.typ = 'le' " .
			" AND lerneinheit.deleted='0000-00-00 00:00:00'".
			" AND lerneinheit.id = meta.id ".
			" AND lerneinheit.inst = meta.inst ");
	while ($ilias_db->next_record())
	{
		$mod_array[$module_count]["inst"] = $ilias_db -> f("inst");
		$mod_array[$module_count]["id"] = $ilias_db -> f("id");
		$mod_array[$module_count]["title"] = $ilias_db -> f("title");
		$mod_array[$module_count]["description"] = $ilias_db -> f("description");
		if ($hide_mod != false)
			for ($i=0; $i<sizeof($hide_mod); $i++)
				if (($hide_mod[$i]["id"] == $mod_array[$module_count]["id"]) AND ($hide_mod[$i]["inst"] == $mod_array[$module_count]["inst"]))
					{
						unset($mod_array[$module_count]);
						$module_count--;
					}
		
		$module_count ++;
	}
	if ($module_count<1)
		return false;
	else
		return $mod_array;
}
	
?>
