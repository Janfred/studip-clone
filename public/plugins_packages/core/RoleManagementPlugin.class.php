<?php
/**
 * RoleManagementPlugin.class.php
 *
 * PHP version 5
 *
 * @author  	Dennis Reil
 * @author  	Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package 	core
 * @copyright 	2009 Stud.IP
 * @license 	http://www.gnu.org/licenses/gpl.html GPL Licence 3
 */

/**
 * Extends Stud.IP by role management, currently mainly used by the plugin engine.
 *
 */
class RoleManagementPlugin extends AbstractStudIPAdministrationPlugin
{
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	public $template_factory;
	/**
	 * Enter description here...
	 *
	 * @var unknown_type
	 */
	private $links;

	/**
	 * Konstruktor
	 *
	 */
	public function __construct()
	{
		parent::AbstractStudIPAdministrationPlugin();

		//Nur ROOT anzeigen
		if ($this->getUser()->getPermission()->hasRootPermission())
		{
			$nav = new PluginNavigation();
			$nav->setDisplayname(_("Verwaltung von Rollen"));
			$this->setTopnavigation($nav); // Eintrag auf der Startseite
			$this->setNavigation($nav); //Eintrag in die Reiter
			$this->setPluginiconname("img/einst.gif"); // ?!?
		}
		
		$this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
		
		$this->links = array(
			'createRole' => PluginEngine::getLink($this, array(), 'createRole'),
			'removeRole' => PluginEngine::getLink($this, array(), 'removeRole'),
			'doRoleAssignment' => PluginEngine::getLink($this, array(), 'doRoleAssignment'),
			'doPluginRoleAssignment' => PluginEngine::getLink($this, array(), 'doPluginRoleAssignment'),
			'showRoleAssignments' => PluginEngine::getLink($this, array(), 'showRoleAssignments')
		);
		PluginEngine::saveToSession($this,"roles",RolePersistence::getAllRoles());
	}

	/**
	 * Enter description here...
	 *
	 */
	private function deletePluginRoleAssignment()
	{
		print_r($_REQUEST);
	}

	/**
	 * Überprüft, ob der Benutzer ROOT ist.
	 *
	 */
	private function checkRootRights()
	{
		$user = $this->getUser();
		$perms = $user->getPermission();
		if (!$perms->hasRootPermission())
		{
			StudIPTemplateEngine::showErrorMessage("Fehler. Sie haben nicht die erforderlichen Rechte für diese Seite.");
			exit;
		}
	}

	/**
	 * Sucht nach Benutzern
	 *
	 * @param string $searchtxt
	 * @return array of StudIPUser
	 */
	private function searchUser($searchtxt)
	{
		$searchtxt = "%" . $searchtxt . "%";
		$stmt = DBManager::get()->prepare(
		  "SELECT user_id FROM auth_user_md5 ".
		  "WHERE username LIKE ? OR Vorname LIKE ? OR Nachname LIKE ? ".
		  "ORDER BY Vorname, Nachname, username");
		$stmt->execute(array($searchtxt, $searchtxt, $searchtxt));
		$users = array();
		while ($row = $stmt->fetch())
		{
			$user = new StudIPUser();
			$user->setUserid($row["user_id"]);
			$users[$row["user_id"]] = $user;
		}
		return $users;
	}

	/**
	 * Startfunktion
	 *
	 */
	public function actionShow()
	{
		$this->actionCreateRole();
	}

	/**
	 * Plugins Rollen zuweisen
	 *
	 */
	public function actionDoPluginRoleAssignment()
	{
		$pluginid = $_REQUEST["pluginid"];
		$assignbtn = $_REQUEST["assignrolebtn_x"];
		$delassignbtn = $_REQUEST["deleteroleassignmentbtn_x"];
		$selroles = $_REQUEST["rolesel"];
		$delassignedrols = $_REQUEST["assignedroles"];

		//action
		if (!empty($assignbtn))
		{
			// assign roles
			RolePersistence::assignPluginRoles($pluginid,$selroles);
			StudIPTemplateEngine::showSuccessMessage(_("Die Rechteeinstellungen wurden erfolgreich gespeichert."));
		}
		if (!empty($delassignbtn))
		{
			// delete role assignment
			RolePersistence::deleteAssignedPluginRoles($pluginid,$delassignedrols);
			StudIPTemplateEngine::showSuccessMessage(_("Die Rechteeinstellungen wurden erfolgreich gespeichert."));
		}

		//view
		$template = $this->template_factory->open('plugin_assignment');
		$template->set_attribute('plugins', PluginEngine::getPluginPersistence()->getAllInstalledPlugins());
		$template->set_attribute('assigned', RolePersistence::getAssignedPluginRoles($pluginid));
		$template->set_attribute('roles', RolePersistence::getAllRoles());
		$template->set_attribute('pluginid', $pluginid);
		$template->set_attribute('links', $this->links);
		echo $template->render();
	}

	/**
	 * Neue Rollen erstellen
	 *
	 */
	public function actionCreateRole()
	{
		$createrolebtn = $_POST["createrolebtn_x"];

		//action
		if (!empty($createrolebtn))
		{
			if(!empty($_POST["newrole"]))
			{
				$role = new Role();
				$role->setRolename($_POST["newrole"]);
				RolePersistence::saveRole($role);
				// create a new role
				PluginEngine::saveToSession($this,"roles",null);
				StudIPTemplateEngine::showSuccessMessage("Die Rolle <b>".$_POST["newrole"]."</b> wurde erfolgreich engelegt.");
			}
			else
			{
				StudIPTemplateEngine::showErrorMessage(_("Fehler. Sie haben keine Rolle eingegeben."));
			}
		}

		//view
		$template = $this->template_factory->open('role_create');
		$template->set_attribute('assigned', RolePersistence::getAssignedPluginRoles($pluginid));
		$template->set_attribute('roles', RolePersistence::getAllRoles());
		$template->set_attribute('links', $this->links);
		echo $template->render();

	}

	/**
	 * Löscht Rollen aus der Datenbank
	 *
	 */
	public function actionRemoveRole()
	{
		$removerolebtn = $_POST["removerolebtn_x"];

		$roles = PluginEngine::getValueFromSession($this,"roles");
		if (empty($roles))
		{
			$roles = RolePersistence::getAllRoles();
			PluginEngine::saveToSession($this,"roles",RolePersistence::getAllRoles());
		}

		//action
		if (!empty($removerolebtn))
		{
			if(!empty($_POST["rolesel"]))
			{
				$rolesel = $_POST["rolesel"];
				foreach ($rolesel as $roleid)
				{
					RolePersistence::deleteRole($roles[$roleid]);
				}
				PluginEngine::saveToSession($this,"roles",null);
				StudIPTemplateEngine::showSuccessMessage(_("Die Rolle(n) und alle dazugehörigen Zuweisungen wurden erfolgreich gelöscht."));
			}
			else 
			{
				StudIPTemplateEngine::showErrorMessage(_("Fehler. bitte wählen sie eine Rolle zum Löschen aus."));
			}
		}

		//view
		$template = $this->template_factory->open('role_create');
		$template->set_attribute('assigned', RolePersistence::getAssignedPluginRoles($pluginid));
		$template->set_attribute('roles', RolePersistence::getAllRoles());
		$template->set_attribute('links', $this->links);
		echo $template->render();
	}

	/**
	 * Enter description here...
	 *
	 */
	public function actionDoRoleAssignment()
	{
		$usersearchtxt = $_POST["usersearchtxt"];
		$userselid = $_POST["usersel"];
		$rolselids = $_POST["rolesel"];
		$searchuserbtn = $_POST["searchuserbtn_x"];
		$assignrolebtn = $_POST["assignrolebtn_x"];
		$deleteassignedrolebtn = $_POST["deleteroleassignmentbtn_x"];
		$delassignedroles = $_POST["assignedroles"];
		$seluserbtn = $_POST["seluserbtn_x"];
		$resetseluser = $_POST['resetseluser_x'];

		if(!empty($resetseluser))
		{
			PluginEngine::saveToSession($this,"searcheduser",null);
			PluginEngine::saveToSession($this,"selecteduser",null);
		}

		$roles = PluginEngine::getValueFromSession($this,"roles");
		if (empty($roles))
		{
			$roles = RolePersistence::getAllRoles();
			PluginEngine::saveToSession($this,"roles",$roles);
		}

		$founduser = PluginEngine::getValueFromSession($this,"searcheduser");
		$selecteduser = PluginEngine::getValueFromSession($this,"selecteduser");
		if(!empty($seluserbtn))
		{
			$founduser = PluginEngine::getValueFromSession($this,"searcheduser");
			$selecteduser = $founduser[$userselid];
			PluginEngine::saveToSession($this,"selecteduser",$selecteduser);
		}
		elseif(!empty($searchuserbtn) && !empty($usersearchtxt))
		{
			// Suche wurde angefordert
			$founduser = $this->searchUser($usersearchtxt);
			PluginEngine::saveToSession($this,"searcheduser",$founduser);
			PluginEngine::saveToSession($this,"selecteduser",null);
		}

		if(!empty($selecteduser))
		{
			$implicidroles = array();
			$assigned = $selecteduser->getAssignedRoles(true);
			foreach ($assigned as $assignedrole)
			{
				$found = false;
				foreach ($selecteduser->getAssignedRoles() as $explassignedrole)
				{
					if ($explassignedrole->getRoleid() == $assignedrole->getRoleid())
					{
						$found = true;
					}
				}
				if (!$found)
				{
					$implicidroles[] = $assignedrole->getRolename();
				}
			}
		}


		//action add
		if (!empty($assignrolebtn))
		{
			foreach ($rolselids as $selroleid)
			{
				// for all selected roles
				$role = $roles[$selroleid];
				RolePersistence::assignRole($selecteduser,$role);
			}
			StudIPTemplateEngine::showSuccessMessage(_("Zuweisungen erfolgreich durchgeführt."));
		}

		//action delete
		if (!empty($deleteassignedrolebtn))
		{
			//ini_set("display_errors","on");
			foreach ($delassignedroles as $roleid)
			{
				$role = $roles[$roleid];
				RolePersistence::deleteRoleAssignment($selecteduser,$role);
			}
			StudIPTemplateEngine::showSuccessMessage(_("Rollenzuweisung erfolgreich gelöscht."));
		}

		//view
		$template = $this->template_factory->open('user_assignment');
		$template->set_attribute('users', $founduser);
		$template->set_attribute('currentuser', $selecteduser);
		$template->set_attribute('implicidroles', $implicidroles);
		$template->set_attribute('usersearchtxt', $usersearchtxt);
		$template->set_attribute('roles', $roles);
		$template->set_attribute('links', $this->links);
		echo $template->render();
	}

	/**
	 * Zeigt alle Benutzer mit bestimmten Rollen an
	 *
	 */
	public function actionShowRoleAssignments()
	{
		$roleid = $_REQUEST['role'];

		//action
		if(!empty($roleid))
		{
			foreach (RolePersistence::getAllRoles() as $rolesel)
			{
				if($roleid == $rolesel->getRoleid())
				{
					$role = $rolesel;
					break;
				}
			}
			$users = array();
			$db = new DB_Seminar;
			//users
		    $sql =  "SELECT a.user_id, a.username, a.Vorname, a.Nachname FROM roles_user  AS u LEFT JOIN auth_user_md5 AS a ON u.userid=a.user_id WHERE u.roleid = '".$roleid."' ORDER BY a.Nachname;";
		    $db->query($sql);
		    while($db->next_record())
		    {
		    	$user = array(
		    		'userid' => $db->f('user_id'),
		    		'username' => $db->f('username'),
		    		'nachname' => $db->f('Nachname'),
		    		'vorname' => $db->f('Vorname')
		    	);
		    	$users[] = $user;
		    }
			//plugins
			$sql =  "SELECT a.pluginname, a.plugintype FROM roles_plugins AS u LEFT JOIN plugins AS a ON u.pluginid=a.pluginid WHERE u.roleid = '".$roleid."' ORDER BY a.pluginname;";
		    $db->query($sql);
		    while($db->next_record())
		    {
		    	$plugin = array(
		    		'plugintype' => $db->f('plugintype'),
		    		'pluginname' => $db->f('pluginname'),
		    	);
		    	$plugins[] = $plugin;
		    }

		}

		//view
		$template = $this->template_factory->open('assignments_list');
		$template->set_attribute('users', $users);
		$template->set_attribute('plugins', $plugins);
		$template->set_attribute('role', $role);
		$template->set_attribute('roleid', $roleid);
		$template->set_attribute('roles', RolePersistence::getAllRoles());
		$template->set_attribute('links', $this->links);
		echo $template->render();

	}
}
?>