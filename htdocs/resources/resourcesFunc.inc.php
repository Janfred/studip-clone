<?

/*
ressourcesFunctions.php - 0.8
Hilfsfunktionen fuer Ressourcenverwaltung von Stud.IP.
Copyright (C) 2002 Cornelis Kater <ckater@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*****************************************************************************
a quick function to get the resource_id (only rooms!) for a assigned date
/*****************************************************************************/

function getDateAssigenedRoom($date_id){
	$db=new DB_Seminar;
	$query = sprintf ("SELECT resources_assign.resource_id FROM resources_assign LEFT JOIN resources_objects USING (resource_id) LEFT JOIN resources_categories USING (category_id) WHERE assign_user_id = '%s' AND resources_categories.name = 'Raum' ", $date_id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("resource_id");
	else
		return FALSE;
}

/*****************************************************************************
a quick function to get a name from a resources object
/*****************************************************************************/

function getResourceObjectName($id){
	$db=new DB_Seminar;
	$query = sprintf ("SELECT name FROM resources_objects WHERE resource_id = '%s'", $id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("name");
	else
		return FALSE;
}

/*****************************************************************************
a quick function to get a category from a resources object
/*****************************************************************************/

function getResourceObjectCategory($id){
	$db=new DB_Seminar;
	$query = sprintf ("SELECT category_id FROM resources_objects WHERE resource_id = '%s'", $id);
	$db->query($query);
	if ($db->next_record())
		return $db->f("category_id");
	else
		return FALSE;
}



/*****************************************************************************
sort function to sort the AssignEvents by date
/*****************************************************************************/

function cmp_assign_events($a, $b){
	$start_a = $a->getBegin();
	$start_b = $b->getBegin();
	if($start_a == $start_b)
		return 0;
	if($start_a < $start_b)
		return -1;
	return 1;
}

/*****************************************************************************
sort function to sort the ResourceObject by name
/*****************************************************************************/
function cmp_resources($a, $b){
	$name_a = $a->getName();
	$name_b = $b->getName();
	if($name_a == $name_b)
		return 0;
	if($name_a < $name_b)
		return -1;
	return 1;
}

/*****************************************************************************
checkAvaibleResources, a quick function to check if for a studip-object
or a user are resources avaiable
/*****************************************************************************/

function checkAvaiableResources($id) {
	$db = new DB_Seminar;
	
	//check if owner
	$db->query("SELECT owner_id FROM resources_objects WHERE owner_id='$id' ");
	if ($db->nf())
		return TRUE;
	
	//or additional perms avaiable
	$db->query("SELECT perms FROM resources_user_resources  WHERE user_id='$id' ");
	if ($db->nf())
		return TRUE;
	
	return FALSE;	
}


/*****************************************************************************
checkChangeOwnerOption checks, if I have the chance to change
the owner of the given object
/*****************************************************************************/

function checkChangeOwnerOption ($resource_object_owner_id, $user_id='') {
	global $user, $perm;
	if (!$user_id)
		$user_id = $user->id;
	
	//for root, it's quick!
	if ($perm->have_perm("root"))
		return TRUE;
	
	//load all my administrable objects
	$my_objects=search_administrable_objects ($search_string='_');
	
	//ok, we as a user aren't interesting...
	unset ($my_objects[$user_id]);
	
	if (sizeof ($my_objects)) {
		if (($my_objects[$resource_object_owner_id]) || ($resource_object_owner_id == $user_id))
			return TRUE;
		else
			return FALSE;
	} else
		return FALSE;
}


/*****************************************************************************
search_administrable_objects searches in all the (for me!) adminstrable objects
/*****************************************************************************/

function search_administrable_objects ($search_string='', $user_id='') {
	global $user, $perm, $auth;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	
	if (!$user_id)
		$user_id=$user->id;
		
	$user_global_perm=get_global_perm($this->user_id);
		switch ($user_global_perm) {
		case "root": 
			//Alle Personen...
			$db->query("SELECT user_id, Vorname, Nachname, username FROM auth_user_md5 WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR user_id = '$search_string' ORDER BY Nachname");
			while ($db->next_record())
					$my_objects[$db->f("user_id")]=array("name"=>$db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username").")", "art"=>"Personen");
			//Alle Seminare...
			$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>"Veranstaltungen");
			//Alle Institute...
			$db->query("SELECT Institut_id, Name FROM Institute WHERE Name LIKE '%$search_string%' OR Institut_id = '$search_string' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>"Institute");
		break;
		case "admin": 
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>"Personen");
			//Alle meine Institute (Suche)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms IN ('tutor', 'dozent', 'admin') AND user_inst.user_id='$user_id' ORDER BY Name");
			while ($db->next_record()) {
				if ($db->f("inst_perms") == "admin") {
					$my_objects_inst[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>"Institute");
				}
			}
			//Alle meine Institute (unabhaengig von Suche fuer Rechte)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE Inst_perms IN ('tutor', 'dozent', 'admin')");
			while ($db->next_record()) {
				if ($db->f("inst_perms") == "admin") {
					//...alle Mitarbeiter meiner Institute, in denen ich Admin bin....
					$db2->query ("SELECT auth_user_md5.user_id, Vorname, Nachname, username FROM auth_user_md5 LEFT JOIN user_inst USING (user_id) WHERE (username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR auth_user_md5.user_id = '$search_string') AND Institut_id = '".$db->f("Institut_id")."' AND inst_perms IN ('autor', 'tutor', 'dozent') ORDER BY Nachname");
					while ($db2->next_record()) {
						$my_objects_user[$db2->f("user_id")]=array("name"=>$db2->f("Nachname").", ".$db2->f("Vorname")." (".$db2->f("username").")", "art"=>"Personen");
					}
					//...alle Seminare meiner Institute, in denen ich Admin bin....
					$db2->query("SELECT seminare.Seminar_id, Name FROM seminare LEFT JOIN seminar_inst USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_inst.institut_id = '".$db->f("Institut_id")."' ORDER BY Name");
					while ($db2->next_record()) {
						$my_objects_sem[$db2->f("Seminar_id")]=array("name"=>$db2->f("Name"), "art"=>"Veranstaltungen");
					}
				}
			}
			if (is_array ($my_objects_user))
				foreach ($my_objects_user as $key=>$val) {
					$my_objects[$key]=$val;
			}
			if (is_array ($my_objects_sem))
				foreach ($my_objects_sem as $key=>$val) {
					$my_objects[$key]=$val;
			}
			if (is_array ($my_objects_inst))
				foreach ($my_objects_inst as $key=>$val) {
					$my_objects[$key]=$val;
			}
		break;
		case "dozent": 
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>"Person");
			//Alle meine Seminare
			$db->query("SELECT seminare.Seminar_id FROM seminare LEFT JOIN seminar_user USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') seminar_user.status IN ('tutor', 'dozent') ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>"Veranstaltungen");
			//Alle meine Institute...
			$db->query("SELECT Institute.Institut_id, Name FROM Institute LEFT JOIN user_inst USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms IN ('tutor', 'dozent') ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>"Institute");
		break;
		case "tutor": 
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>"Person");
			//Alle meine Seminare
			$db->query("SELECT seminare.Seminar_id, Name FROM seminare LEFT JOIN seminar_user USING (seminar_id) WHERE  (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status='tutor' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>"Veranstaltungen");
			//Alle meine Institute...
			$db->query("SELECT Institute.Institut_id, Name FROM Institute LEFT JOIN user_inst USING (institut_id)  WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms='tutor' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>"Institute");
		break;
		case "autor": 
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>"Personen");
		break;
	}
	return $my_objects;
}

/*****************************************************************************
search_admin_user searches in all the admins
/*****************************************************************************/

function search_admin_user ($search_string='') {

	$db=new DB_Seminar;

	//In allen Admins suchen...
	$db->query("SELECT user_id, Vorname, Nachname, username FROM auth_user_md5 WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR user_id = '$search_string' ORDER BY Nachname");
	while ($db->next_record())
			$my_objects[$db->f("user_id")]=array("name"=>$db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username").")", "art"=>"Personen");
	
	return $my_objects;
}


/*****************************************************************************
search_objects searches in all objects
/*****************************************************************************/

function search_objects ($search_string='', $user_id='') {
	global $user, $perm, $auth;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	
	if (!$user_id)
		$user_id=$user->id;
		
	//Alle Personen...
	$db->query("SELECT user_id, Vorname, Nachname, username FROM auth_user_md5 WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR user_id = '$search_string' ORDER BY Nachname");
	while ($db->next_record())
		$my_objects[$db->f("user_id")]=array("name"=>$db->f("Nachname").", ".$db->f("Vorname")." (".$db->f("username").")", "art"=>"Personen");
	//Alle Seminare...
	$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
	while ($db->next_record())
		$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>"Veranstaltungen");
	//Alle Institute...
	$db->query("SELECT Institut_id, Name FROM Institute WHERE Name LIKE '%$search_string%' OR Institut_id = '$search_string' ORDER BY Name");
	while ($db->next_record())
		$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>"Institute");

	return $my_objects;
}


/*****************************************************************************
Searchform, zur Erzeugung der oft gebrauchten Personen-Auswahl
u.a. Felder
/*****************************************************************************/

function create_search_form($name, $search_string='', $user_only=FALSE, $administrable_objects_only=FALSE, $admins=FALSE, $allow_all=FALSE) {

	if ($search_string) {
		if ($user_only) //Nur in Personen suchen
			if ($admins) //nur admins anzeigen
				$my_objects=search_admin_user($search_string);
			else //auch andere...
				;
		elseif ($administrable_objects_only)
			$my_objects=search_administrable_objects($search_string);
		else //komplett in allen Objekten suchen
			$my_objects=search_objects($search_string);
			
		?>
		<input type="HIDDEN" name="<? echo "search_string_".$name ?>" value="<? echo $search_string ?>" />
		<select name="<? echo "submit_".$name ?>">
		<?
		if ($allow_all)
			print "<option value=\"all\">Jeder</option>";

		foreach ($my_objects as $key=>$val) {
			if ($val["art"] != $old_art) {
				?>			
			<font size=-1><option value="FALSE"><? echo "-- ".$val["art"]." --"; ?></option></font>
				<?
			}
			?>
			<font size=-1><option value="<? echo $key ?>"><? echo my_substr($val["name"],0,30); ?></option></font>
			<?

			$old_art=$val["art"];
		}
		?></select>
			<font size=-1><input type="SUBMIT" name="<? echo $name ?>" value="&uuml;bernehmen" size=30 maxlength=255 /></font>
			&nbsp; <font size=-1><input type="SUBMIT" name="<? echo "reset_".$name ?>" value="neue Suche" /></font>
		<?
	} else {
		?>
		<font size=-1><input type="TEXT" name="<? echo "search_string_".$name ?>" size=30 maxlength=255 /></font>
		<font size=-1><input type="SUBMIT" name="<? echo "do_".$name ?>" value="suchen" /></font>
		<?
	}
}
?>