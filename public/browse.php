<?php
# Lifter002: TEST (mriehe)
# Lifter005: TODO
/**
 * browse.php - Personen-Suche in Stud.IP
 *
 * PHP Version 5
 *
 * @author		Stefan Suchi <suchi@gmx.de>
 * @author		Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @version		$Revision$
 * @access		public
 * @copyright 	2000-2009 Stud.IP
 * @license 	http://www.gnu.org/licenses/gpl.html GPL Licence 3
*/

page_open(array("sess" => "Seminar_Session", "auth" => "Seminar_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"));
$perm->check('user');

//Imports
require_once 'lib/seminar_open.php'; // initialise Stud.IP-Session
require_once 'config.inc.php';   //wir brauchen die Auto-Eintrag-Seminare
require_once 'lib/visual.inc.php';
require_once 'lib/functions.php';
require_once 'lib/statusgruppe.inc.php';
require_once 'lib/user_visible.inc.php';

if ($GLOBALS['CHAT_ENABLE'])
{
	include_once $RELATIVE_PATH_CHAT."/chat_func_inc.php";
}

//Basics
$HELP_KEYWORD="Basis.SuchenPersonen";
$CURRENT_PAGE = _("Personensuche");

/* --- Actions -------------------------------------------------------------- */
//Eine Suche wurde abgeschickt
if ($_REQUEST['send'])
{
	$sess->register("browse_data"); //Daten in die Session
	$browse_data["Vorname"] = trim($_POST['Vorname']);
	$browse_data["Nachname"] = trim($_POST['Nachname']);
	$browse_data["inst_id"] = $_POST['inst_id'];
	$browse_data["sem_id"] = $_POST['sem_id'];
}

//Suchformular wurde zurückgesetzt
if ($_REQUEST['zuruecksetzen_x'])
{
	$sess->unregister("browse_data");
	unset($browse_data);
}

//wonach wurde gesucht?
if (!empty($browse_data["inst_id"]))
{
	$browse_data["group"]='Institut';
}
elseif (!empty($browse_data["sem_id"]))
{
	$browse_data["group"]='Seminar';
}
elseif($browse_data["Nachname"] || $browse_data["Vorname"])
{
	$browse_data["group"]='Search';
}

//Ergebnisse sollen sortiert werden
$sortby = array('Nachname', 'perms', 'status');
$browse_data['sortby'] = (in_array($_REQUEST['sortby'], $sortby))? trim($_REQUEST['sortby']):'Nachname';


/* --- Search --------------------------------------------------------------- */
$template = $GLOBALS['template_factory']->open('browse');

//TODO: (mriehe) welche aufgabe hat dieser teil?
if ($sms_msg)
{
	$template->set_attribute('sms_msg', $sms_msg);
	$sms_msg = '';
	$sess->unregister('sms_msg');
}

//List of Institutes
$db2=new DB_Seminar;
if ($perm->have_perm("admin"))
{
	$db2->query("SELECT * FROM Institute WHERE (Institute.modules & 16) ORDER BY name");
}
else
{
	$db2->query("SELECT * FROM user_inst LEFT JOIN Institute USING (institut_id) WHERE user_id = '$user->id' AND (Institute.modules & 16) ORDER BY name");
}
while ($db2->next_record())
{
	$institutes[] = array('id' => $db2->f("Institut_id"), 'name' => htmlReady(my_substr($db2->f("Name"), 0, 40)));
}

//List of Seminars
$db2=new DB_Seminar;
if (!$perm->have_perm("admin"))
{
	if ($AUTO_INSERT_SEM)
	{
		$templist = "'" . implode ("', '", $AUTO_INSERT_SEM) . "'";
		$db2->query("SELECT * FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE seminare.Seminar_id NOT IN ($templist) AND user_id = '$user->id' AND (seminare.modules & 8) ORDER BY Name");
	}
	else
	{
		$db2->query("SELECT * FROM seminar_user LEFT JOIN seminare USING (Seminar_id) WHERE user_id = '$user->id' AND (seminare.modules & 8) ORDER BY Name");
	}
	while ($db2->next_record())
	{
		$courses[] = array('id' => $db2->f("Seminar_id"), 'name' => htmlReady(my_substr($db2->f("Name"), 0, 40)));
	}
}

/* --- Results -------------------------------------------------------------- */

// nur global admin darf alle Benutzer sehen
if ($perm->have_perm("admin"))
{
 	$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname,username,perms,auth_user_md5.user_id FROM auth_user_md5 LEFT JOIN user_info USING (user_id) ORDER BY ".$browse_data["sortby"];
}

// nach instituten
if($browse_data["group"]=="Institut")
{
	$einrichtungssuche = true;
	$db2->query("SELECT Institut_id FROM user_inst WHERE Institut_id = '".$browse_data["inst_id"]."' AND user_id = '$user->id'");
	// entweder wir gehoeren auch zum Institut oder sind global admin
	if ($db2->num_rows() > 0 || $perm->have_perm("admin"))
	{
  		$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname ,username,user_inst.inst_perms,user_inst.user_id,user_inst.Institut_id FROM user_inst LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE Institut_id ='".$browse_data["inst_id"]."' ORDER BY ".$browse_data["sortby"];
	}
}

// nach seminaren
if($browse_data["group"]=="Seminar")
{
	if ($AUTO_INSERT_SEM)
	{
		$templist = "'" . implode ("', '", $AUTO_INSERT_SEM) . "'";
		$db2->query("SELECT Seminar_id FROM seminar_user WHERE Seminar_id NOT IN ($templist) AND Seminar_id = '".$browse_data["sem_id"]."' AND user_id = '$user->id'");
	}
	else
	{
		$db2->query("SELECT Seminar_id FROM seminar_user WHERE Seminar_id = '".$browse_data["sem_id"]."' AND user_id = '$user->id'");
	}
	// entweder wir gehoeren auch zum Seminar oder sind global admin
	if ($db2->num_rows() > 0 || $perm->have_perm("admin"))
	{
 		$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname ,username,seminar_user.status,auth_user_md5.user_id FROM seminar_user LEFT JOIN auth_user_md5 USING (user_id) LEFT JOIN user_info USING (user_id) WHERE Seminar_id ='".$browse_data["sem_id"]."' ORDER BY ".$browse_data["sortby"];
	}
}

// freie Suche
if($browse_data["group"]=="Search")
{
 	$query = "SELECT " . $_fullname_sql['full_rev'] ." AS fullname,username,perms,auth_user_md5.user_id FROM auth_user_md5 LEFT JOIN user_info USING (user_id)";
	//TODO: (mriehe) das geht auch einfacher und schöner...
 	$browse_data["Vorname"] = str_replace("%", "\%", $browse_data["Vorname"]);	// tss, tss, tss
	$browse_data["Vorname"] = str_replace("_", "\_", $browse_data["Vorname"]);
	$browse_data["Nachname"] = str_replace("%", "\%", $browse_data["Nachname"]);
	$browse_data["Nachname"] = str_replace("_", "\_", $browse_data["Nachname"]);
	if ($browse_data["Vorname"] != "" && strlen($browse_data["Vorname"]) > 2)
	{
		// wir haben Vornamen und Nachnamen zum Suchen
		if ($browse_data["Nachname"] != "" && strlen($browse_data["Nachname"]) > 2)
		{
	 		$query .= " WHERE Vorname LIKE '%".$browse_data["Vorname"]."%' AND Nachname LIKE '%".$browse_data["Nachname"]."%' ";
		}
		else
		{
			// wir haben einen Vornamen zum Suchen
	 		$query .= " WHERE Vorname LIKE '%".$browse_data["Vorname"]."%' ";
		}
	}
	else
	{
		// wir haben einen Nachnamen zum Suchen
		if ($browse_data["Nachname"] != "" && strlen($browse_data["Nachname"]) > 2)
		{
	 		$query .= " WHERE Nachname LIKE '%".$browse_data["Nachname"]."%' ";
		}
		// wir haben nix oder Muell zum Suchen. PFUI!
		//TODO: (mriehe) das macht so absolut keinen Sinn...
		else
		{
	 		$query .= " WHERE Vorname ='- - -' AND Nachname = '- - -' ";
		}
	}
	$query .= " ORDER BY ".$browse_data["sortby"];
}

//Befinden sich daten in der Session?
if ($browse_data["group"])
{
	$db = new DB_Seminar;
    $db->query($query);

	// ausgabe der tabellenueberschrift
	if ($db->num_rows() > 0)
	{
		$visible = 0; //VIS: first, we have to save all data, that needs to be displayed
		$data = array();
		while ($db->next_record())
		{
			if (get_visibility_by_id($db->f("user_id")) || ($einrichtungssuche && $db->f("perms") != "autor" && $db->f("perms") != "user"))
			{
				$visible++;
				$data[] = $db->Record;
			}
		}
		// wir haben ein Ergebnis
		if ($visible > 0)
		{

			//anfuegen der daten an tabelle in schleife...
		  	foreach ($data as $val)
		  	{
		  		// now iterate trough data-array instead of database-array
				switch ($browse_data["group"])
				{
					case "Seminar":
						$result = array('username' => $val["username"], 'fullname' => htmlReady($val["fullname"]), 'status' => htmlReady($val["status"]));
						break;
					case "Institut":
						$result = array('username' => $val["username"], 'fullname' => htmlReady($val["fullname"]));
						if ($val["inst_perms"] == "user")
						{
							$result['perms'] = _('Studierender');
						}
						else
						{
							//statusgruppen
							$gruppen = GetRoleNames(GetAllStatusgruppen($val["Institut_id"],$val["user_id"]));
							(is_array($gruppen)) ? $result['perms'] = htmlReady(join(", ", array_values($gruppen))) : $result['perms']= _("keiner Funktion zugeordnet");
						}
						break;
					default:
						$result = array('username' => $val["username"], 'fullname' => htmlReady($val["fullname"]), 'perms' => $val["perms"]);
						break;
				}

				if ($GLOBALS['CHAT_ENABLE'])
				{
					$result['chat'] = chat_get_online_icon($val["user_id"],$val["username"]);
				}
				$resultset[] = $result;
			}
			$template->set_attribute('results', $resultset);
		}
	}
}

/* --- View ----------------------------------------------------------------- */
$template->set_attribute('browse_data', $browse_data);
$template->set_attribute('institutes', $institutes);
$template->set_attribute('courses', $courses);
$template->set_layout("layouts/base");
echo $template->render();
page_close();
?>
