<?
function get_module_info($co_inst, $co_id)
{
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT meta.id, meta.inst, meta.title, meta.description ".
			" FROM lerneinheit LEFT JOIN meta USING (id, inst)".
			" WHERE meta.status='final' ".
			" AND public = 'y' ".
			" AND meta.typ = 'le' ".
			" AND lerneinheit.id = '$co_id' ".
			" AND lerneinheit.inst = '$co_inst' ");
	if ($ilias_db->next_record())
	{
		$module_info["title"] = $ilias_db -> f("title");
		$module_info["description"] = $ilias_db -> f("description");
		$ilias_db -> query("SELECT DISTINCT id FROM page WHERE lerneinheit='co_id' AND le_inst='co_inst' AND pg_typ = 'le'");
		$module_info["pages"] = $ilias_db -> num_rows();
		$ilias_db -> query("SELECT DISTINCT id FROM page WHERE lerneinheit='co_id' AND le_inst='co_inst' AND pg_typ = 'mc'");
		$module_info["questions"] = $ilias_db -> num_rows();
		
		return $module_info;
	}
	return false;
}

function get_module_author($co_inst, $co_id)
{
	$module_author = false;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT benutzer.benutzername, benutzer.vorname, benutzer.nachname, benutzer.atitel ".
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
		$mcount++;
	}
	return $module_author;
}

function get_user_modules($benutzername)
{
	$mod_array = false;
	$module_count = 0;
	$db = New DB_Seminar;
	$db->query("SELECT * FROM auth_user_md5  WHERE username ='$benutzername'");
	if ($db->next_record())
	{
//		$firstname = $db->f("Vorname");
//		$surname = $db->f("Nachname");
		$ilias_user_id = get_ilias_user_id(get_ilias_user($benutzername));
	}
	else
	{		
		printf(_("Stud.IP-User '%s' wurde nicht gefunden.") . "<br>", $benutzername);
		return false;
	}
	if (get_ilias_user($benutzername) == false)
	{		
		echo _("Sie sind nicht als User im angebundenen ILIAS-System eingetragen. Wenden Sie sich bitte an den/die AdministratorIn.") . "<br>";
		return false;
	}
//	echo $firstname . $surname . $ilias_user_id;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT DISTINCT meta.id, meta.inst, meta.title, meta.description ".
			" FROM lerneinheit LEFT JOIN meta USING(id, inst) LEFT JOIN meta_author USING(id, inst, typ) LEFT JOIN meta_contrib USING(id, inst, typ)".
			" WHERE meta.status='final' ".
			" AND lerneinheit.public = 'y' ".
			" AND meta.typ = 'le' ".
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

function get_seminar_modules($seminar_id)
{
	$mod_array = false;
	$module_count = 0;
	$db = New DB_Seminar;
	$db -> query("SELECT co_inst, co_id FROM seminar_lernmodul WHERE seminar_id = '$seminar_id'");
	while ($db->next_record())
	{
		$mod_array[$module_count]["inst"] = $db -> f("co_inst");
		$mod_array[$module_count]["id"] = $db -> f("co_id");
		$module_count ++;
	}
	if ($module_count<1)
		return false;
	else
		return $mod_array;
}

function get_all_modules($hide_mod = false)
{
	$mod_array = false;
	$module_count = 0;
	$ilias_db = New DB_Ilias;
	$ilias_db -> query("SELECT lerneinheit.id, lerneinheit.inst, meta.title, meta.description".
			" FROM lerneinheit, meta ".
			" WHERE meta.status='final' ".
			" AND public = 'y' ".
			" AND meta.typ = 'le' " .
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