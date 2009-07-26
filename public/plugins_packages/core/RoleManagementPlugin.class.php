<?php
# Lifter002: TEST
# Lifter003: TEST
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
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$users = array();
		foreach ($result as $row)
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
		$selroles = $_REQUEST["rolesel"];
		$delassignedrols = $_REQUEST["assignedroles"];

		//action
		if (isset($_REQUEST["assignrolebtn_x"]) || isset($_REQUEST["assignrolebtn"]))
		{
			// assign roles
			RolePersistence::assignPluginRoles($pluginid,$selroles);
			StudIPTemplateEngine::showSuccessMessage(_("Die Rechteeinstellungen wurden erfolgreich gespeichert."));
		}
		if (isset($_REQUEST["deleteroleassignmentbtn_x"]) || isset($_REQUEST["deleteroleassignmentbtn"]))
		{
			// delete role assignment
			RolePersistence::deleteAssignedPluginRoles($pluginid,$delassignedrols);
			StudIPTemplateEngine::showSuccessMessage(_("Die Rechteeinstellungen wurden erfolgreich gespeichert."));
		}

		//view
		$template = $this->template_factory->open('plugin_assignment');
		$template->set_attribute('plugins', PluginManager::getInstance()->getPluginInfos());
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
		//action
		if (isset($_POST["createrolebtn_x"]) || isset($_POST["createrolebtn"]))
		{
			if(!empty($_POST["newrole"]))
			{
				$role = new Role();
				$role->setRolename($_POST["newrole"]);
				RolePersistence::saveRole($role);
				// create a new role
				StudIPTemplateEngine::showSuccessMessage("Die Rolle <b>".$_POST["newrole"]."</b> wurde erfolgreich engelegt.");
			}
			else
			{
				StudIPTemplateEngine::showErrorMessage(_("Fehler. Sie haben keine Rolle eingegeben."));
			}
		}

		//view
		$template = $this->template_factory->open('role_create');
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
		//action
		if (isset($_POST["removerolebtn_x"]) || isset($_POST["removerolebtn"]))
		{
			if(!empty($_POST["rolesel"]))
			{
				$roles = RolePersistence::getAllRoles();
				$rolesel = $_POST["rolesel"];
				foreach ($rolesel as $roleid)
				{
					RolePersistence::deleteRole($roles[$roleid]);
				}
				StudIPTemplateEngine::showSuccessMessage(_("Die Rolle(n) und alle dazugehörigen Zuweisungen wurden erfolgreich gelöscht."));
			}
			else
			{
				StudIPTemplateEngine::showErrorMessage(_("Fehler. Bitte wählen sie eine Rolle zum Löschen aus."));
			}
		}

		//view
		$template = $this->template_factory->open('role_create');
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
		$roles = RolePersistence::getAllRoles();
		$selecteduser = PluginEngine::getValueFromSession($this,"selecteduser");
		$founduser = PluginEngine::getValueFromSession($this,"searcheduser");

		// zurücksetzen
		if(isset($_POST['resetseluser_x']) || isset($_POST['resetseluser']))
		{
			PluginEngine::saveToSession($this,"searcheduser",null);
			PluginEngine::saveToSession($this,"selecteduser",null);
		}

		// benutzer ausgewählt
		if(isset($_POST["seluserbtn_x"]) || isset($_POST["seluserbtn"]))
		{
			$founduser = PluginEngine::getValueFromSession($this,"searcheduser");
			$selecteduser = $founduser[$_POST["usersel"]];
			PluginEngine::saveToSession($this,"selecteduser",$selecteduser);
		}

		// benutzer gesucht
		if((isset($_POST["searchuserbtn_x"]) || isset($_POST["searchuserbtn"])) && !empty($_POST["usersearchtxt"]))
		{
			$founduser = $this->searchUser(trim($_POST["usersearchtxt"]));
			PluginEngine::saveToSession($this,"searcheduser",$founduser);
			PluginEngine::saveToSession($this,"selecteduser",null);
			if(empty($founduser))
			{
				StudIPTemplateEngine::showErrorMessage(_("Es wurde kein Benutzer gefunden."));
			}
		}
		elseif((isset($_POST["searchuserbtn_x"]) || isset($_POST["searchuserbtn"])) && empty($_POST["usersearchtxt"]))
		{
			StudIPTemplateEngine::showErrorMessage(_("Fehler. Es wurde kein Suchwort eingegeben."));
		}

		// Rollen zuweisen
		if (!empty($_POST["assignrolebtn_x"]))
		{
			foreach ($_POST["rolesel"] as $selroleid)
			{
				$role = $roles[$selroleid];
				RolePersistence::assignRole($selecteduser,$role);
			}
			StudIPTemplateEngine::showSuccessMessage(_("Zuweisungen erfolgreich durchgeführt."));
		}

		// Rollen löschen
		if (!empty($_POST["deleteroleassignmentbtn_x"]))
		{
			foreach ($_POST["assignedroles"] as $roleid)
			{
				$role = $roles[$roleid];
				RolePersistence::deleteRoleAssignment($selecteduser,$role);
			}
			StudIPTemplateEngine::showSuccessMessage(_("Rollenzuweisung erfolgreich gelöscht."));
		}

		if(!empty($selecteduser))
		{
			$implicidroles = array();
			foreach ($selecteduser->getAssignedRoles(true) as $assignedrole)
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

		//view
		$template = $this->template_factory->open('user_assignment');
		$template->set_attribute('users', $founduser);
		$template->set_attribute('currentuser', $selecteduser);
		$template->set_attribute('implicidroles', $implicidroles);
		$template->set_attribute('usersearchtxt', $_POST["usersearchtxt"]);
		$template->set_attribute('roles', $roles);
		$template->set_attribute('links', $this->links);
		echo $template->render();
	}

	/**
	 * Check role access permission for the given plugin.
	 *
	 * @param $plugin   plugin meta data
	 * @param $role_id  role id of role
	 */
	protected function checkRoleAccess($plugin, $role_id)
	{
		$plugin_roles = RolePersistence::getAssignedPluginRoles($plugin['id']);

		foreach ($plugin_roles as $plugin_role) {
			if ($plugin_role->getRoleid() === $role_id) {
				return true;
			}
		}

		return $false;
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
			//users
			$users = DBManager::get()->query("SELECT a.user_id AS userid, a.username, a.Vorname, a.Nachname FROM roles_user  AS u LEFT JOIN auth_user_md5 AS a ON u.userid=a.user_id WHERE u.roleid = '".$roleid."' ORDER BY a.Nachname;")->fetchAll(PDO::FETCH_ASSOC);

			//plugins
			$plugins = PluginManager::getInstance()->getPluginInfos();

			foreach ($plugins as $id => $plugin)
			{
				if (!$this->checkRoleAccess($plugin, $roleid)) {
					unset($plugins[$id]);
				}
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
