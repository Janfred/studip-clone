<?
/**
* resourcesFunc.php
* 
* functions for resources
* 
*
* @author		Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
* @version		$Id$
* @access		public
* @modulegroup	resources
* @module		resourcesFunc.php
* @package		resources
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// resourcesFunc.php
// Funktionen der Ressourcenverwaltung
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+


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
checkAssigns, a quick function to check if for a ressource
exists assigns
/*****************************************************************************/

function checkAssigns($id) {
	$db = new DB_Seminar;
	
	$db->query("SELECT assign_id FROM resources_assign WHERE resource_id='$id' ");
	if ($db->nf())
		return TRUE;
	return FALSE;	
}

/*****************************************************************************
checkObjektAdminstrablePerms checks, if I have the chance to change
the owner of the given object
/*****************************************************************************/

function checkObjektAdminstrablePerms ($resource_object_owner_id, $user_id='') {
	global $user, $perm, $my_perms;
	if (!$user_id)
		$user_id = $user->id;
	
	//for root, it's quick!
	if ($perm->have_perm("root"))
		return TRUE;
	
	//for the resources admin too
	if ($my_perms ->getGlobalPerms() == "admin")
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

function search_administrable_objects ($search_string='', $user_id='', $sem=TRUE) {
	global $user, $perm, $auth, $_fullname_sql;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	
	if (!$user_id)
		$user_id = $user->id;
		
	if (!$search_string)
		$search_string = "_";
		
	$user_global_perm=get_global_perm($this->user_id);
		switch ($user_global_perm) {
		case "root": 
			//Alle Personen...
			$db->query("SELECT a.user_id,". $_fullname_sql['full_rev'] ." AS fullname , username FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR a.user_id = '$search_string' ORDER BY Nachname");
			while ($db->next_record())
					$my_objects[$db->f("user_id")]=array("name"=>$db->f("fullname")." (".$db->f("username").")", "art"=>_("Personen"), "perms" => "admin");
			//Alle Seminare...
			if ($sem) {
				$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
				while ($db->next_record())
					$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
			}
			//Alle Institute...
			$db->query("SELECT Institut_id, Name FROM Institute WHERE Name LIKE '%$search_string%' OR Institut_id = '$search_string' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "admin");
		break;
		case "admin": 
			//Alle meine Institute (Suche)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms = 'admin' AND user_inst.user_id='$user_id' ORDER BY Name");
			while ($db->next_record()) {
				$my_objects_inst[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "admin");
			}
			//Alle meine Institute (unabhaengig von Suche fuer Rechte)...
			$db->query("SELECT Institute.Institut_id, Name, inst_perms FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE inst_perms = 'admin' AND user_inst.user_id='$user_id' ");
			while ($db->next_record()) {
				//...alle Mitarbeiter meiner Institute, in denen ich Admin bin....
				$db2->query ("SELECT auth_user_md5.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, username FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE (username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR auth_user_md5.user_id = '$search_string') AND Institut_id = '".$db->f("Institut_id")."' AND inst_perms IN ('autor', 'tutor', 'dozent') ORDER BY Nachname");
				while ($db2->next_record()) {
					$my_objects_user[$db2->f("user_id")]=array("name"=>$db2->f("fullname")." (".$db2->f("username").")", "art"=>_("Personen"), "perms" => "admin");
				}
				//...alle Seminare meiner Institute, in denen ich Admin bin....
				if ($sem) {
					$db2->query("SELECT seminare.Seminar_id, Name FROM seminar_inst LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_inst.institut_id = '".$db->f("Institut_id")."' ORDER BY Name");
					while ($db2->next_record()) {
						$my_objects_sem[$db2->f("Seminar_id")]=array("name"=>$db2->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
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
			//Alle meine Seminare
			if ($sem) {
				$db->query("SELECT seminare.Seminar_id, Name FROM seminar_user LEFT JOIN seminare USING (seminar_id) WHERE (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status IN ('tutor', 'dozent')  AND seminar_user.user_id='$user_id' ORDER BY Name");
				while ($db->next_record())
					$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"), "perms" => "admin");
			}
			//Alle meine Institute...
			$db->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms IN ('tutor', 'dozent')  AND user_inst.user_id='$user_id'  ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => $db->f("inst_perms"));
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>_("Personen"),  "perms" => "admin");
		break;
		case "tutor": 
			//Alle meine Seminare
			if ($sem) {
				$db->query("SELECT seminare.Seminar_id, Name FROM seminar_user LEFT JOIN seminare USING (seminar_id) WHERE  (Name LIKE '%$search_string%' OR Untertitel LIKE '%$search_string%' OR seminare.Seminar_id = '$search_string') AND seminar_user.status='tutor' AND seminar_user.user_id='$user_id' ORDER BY Name");
				while ($db->next_record())
					$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"),  "perms" => "tutor");
			}
			//Alle meine Institute...
			$db->query("SELECT Institute.Institut_id, Name FROM user_inst LEFT JOIN Institute USING (institut_id)  WHERE (Name LIKE '%$search_string%' OR Institute.Institut_id = '$search_string') AND inst_perms='tutor' AND user_inst.user_id='$user_id' ORDER BY Name");
			while ($db->next_record())
				$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"), "perms" => "tutor");
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>_("Personen"),  "perms" => "admin");
		break;
		case "autor": 
			$my_objects[$user_id]=array("name"=>"aktueller Account"." (".get_username($user_id).")", "art"=>_("Personen"),  "perms" => "admin");
		break;
	}
	return $my_objects;
}

/*****************************************************************************
search_admin_user searches in all the admins
/*****************************************************************************/

function search_admin_user ($search_string='') {
	global $_fullname_sql;
	$db=new DB_Seminar;

	//In allen Admins suchen...
	$db->query("SELECT a.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, username FROM auth_user_md5  a LEFT JOIN user_info USING (user_id) WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR a.user_id = '$search_string' ORDER BY Nachname");
	while ($db->next_record())
			$my_objects[$db->f("user_id")]=array("name"=>$db->f("fullname")." (".$db->f("username").")", "art"=>_("Personen"));
	
	return $my_objects;
}


/*****************************************************************************
search_objects searches in all objects
/*****************************************************************************/

function search_objects ($search_string='', $user_id='', $sem=TRUE) {
	global $user, $perm, $auth, $_fullname_sql;

	$db=new DB_Seminar;
	$db2=new DB_Seminar;
	$db3=new DB_Seminar;
	
	if (!$user_id)
		$user_id=$user->id;
		
	//Alle Personen...
	$db->query("SELECT a.user_id, ". $_fullname_sql['full_rev'] ." AS fullname, username FROM auth_user_md5 a LEFT JOIN user_info USING (user_id) WHERE username LIKE '%$search_string%' OR Vorname LIKE '%$search_string%' OR Nachname LIKE '%$search_string%' OR a.user_id = '$search_string' ORDER BY Nachname");
	while ($db->next_record())
		$my_objects[$db->f("user_id")]=array("name"=>$db->f("fullname")." (".$db->f("username").")", "art"=>_("Personen"));
	//Alle Seminare...
	if ($sem) {
		$db->query("SELECT Seminar_id, Name FROM seminare WHERE Name LIKE '%$search_string%' OR Untertitel = '%$search_string%' OR Seminar_id = '$search_string' ORDER BY Name");
		while ($db->next_record())
			$my_objects[$db->f("Seminar_id")]=array("name"=>$db->f("Name"), "art"=>_("Veranstaltungen"));
	}
	//Alle Institute...
	$db->query("SELECT Institut_id, Name FROM Institute WHERE Name LIKE '%$search_string%' OR Institut_id = '$search_string' ORDER BY Name");
	while ($db->next_record())
		$my_objects[$db->f("Institut_id")]=array("name"=>$db->f("Name"), "art"=>_("Einrichtungen"));

	return $my_objects;
}


/*****************************************************************************
Searchform, zur Erzeugung der oft gebrauchten Personen-Auswahl
u.a. Felder
/*****************************************************************************/

function showSearchForm($name, $search_string='', $user_only=FALSE, $administrable_objects_only=FALSE, $admins=FALSE, $allow_all=FALSE, $sem=TRUE) {

	if ($search_string) {
		if ($user_only) //Nur in Personen suchen
			if ($admins) //nur admins anzeigen
				$my_objects=search_admin_user($search_string);
			else //auch andere...
				;
		elseif ($administrable_objects_only)
			$my_objects=search_administrable_objects($search_string, FALSE, $sem);
		else //komplett in allen Objekten suchen
			$my_objects=search_objects($search_string, FALSE, $sem);
			
		?>
		<input type="HIDDEN" name="<? echo "search_string_".$name ?>" value="<? echo $search_string ?>" />
		<select name="<? echo "submit_".$name ?>">
		<?
		if ($allow_all)
			print "<option value=\"all\">"._("jeder")."</option>";

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
			<font size=-1><input type="IMAGE" name="<? echo "send_".$name ?>" <?=makeButton("uebernehmen", "src") ?> value="<?=_("&uuml;bernehmen")?>"  /></font>
			<font size=-1><input type="IMAGE" name="<? echo "reset_".$name ?>" <?=makeButton("neuesuche", "src") ?> border=0 value="<?=_("neue Suche")?>" /></font>
		<?
	} else {
		?>
		<font size=-1><input type="TEXT" name="<? echo "search_string_".$name ?>" size=30 maxlength=255 /></font>
		<font size=-1><input type="IMAGE" align="absmiddle" name="<? echo "do_".$name ?>" <?=makeButton("suchestarten", "src") ?> border=0 value="<?=_("suchen")?>" /></font>
		<?
	}
}
?>