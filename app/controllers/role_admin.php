<?php
/**
 * RoleManagementPlugin.class.php
 *
 * @author      Dennis Reil
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @copyright   2009 Stud.IP
 */

require_once 'app/controllers/authenticated_controller.php';

/**
 * Extends Stud.IP by role management, currently mainly used by the plugin engine.
 */
class RoleAdminController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        // user must have root permission
        $perm->check('root');

        // set page title and navigation
        $GLOBALS['CURRENT_PAGE'] = _('Verwaltung von Rollen');
        Navigation::activateItem('/admin/tools/roles');
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
            $user = new StudIPUser($row["user_id"]);
            $users[$row["user_id"]] = $user;
        }
        return $users;
    }

    /**
     * Startfunktion
     *
     */
    public function index_action()
    {
        $this->roles = RolePersistence::getAllRoles();
        $this->render_action('create_role');
    }

    /**
     * Plugins Rollen zuweisen
     *
     */
    public function assign_plugin_role_action($pluginid = NULL)
    {
        if (!isset($pluginid)) {
            $pluginid = Request::int('pluginid');
        }

        $selroles = Request::intArray('rolesel');
        $delassignedrols = Request::intArray('assignedroles');

        //action
        if (Request::submitted('assignrolebtn'))
        {
            // assign roles
            RolePersistence::assignPluginRoles($pluginid,$selroles);
            $this->success = _('Die Rechteeinstellungen wurden erfolgreich gespeichert.');
        }
        if (Request::submitted('deleteroleassignmentbtn'))
        {
            // delete role assignment
            RolePersistence::deleteAssignedPluginRoles($pluginid,$delassignedrols);
            $this->success = _('Die Rechteeinstellungen wurden erfolgreich gespeichert.');
        }

        //view
        $this->plugins = PluginManager::getInstance()->getPluginInfos();
        $this->assigned = RolePersistence::getAssignedPluginRoles($pluginid);
        $this->roles = RolePersistence::getAllRoles();
        $this->pluginid = $pluginid;
    }

    /**
     * Neue Rollen erstellen
     *
     */
    public function create_role_action()
    {
        //action
        if (Request::submitted('createrolebtn'))
        {
            $newrole = Request::get('newrole');

            if ($newrole != '')
            {
                $role = new Role();
                $role->setRolename($newrole);
                RolePersistence::saveRole($role);
                // create a new role
                $this->success = sprintf(_('Die Rolle "%s" wurde erfolgreich angelegt.'), $newrole);
            }
            else
            {
                $this->error = _('Sie haben keine Rolle eingegeben.');
            }
        }

        //view
        $this->roles = RolePersistence::getAllRoles();
    }

    /**
     * Löscht Rollen aus der Datenbank
     *
     */
    public function remove_role_action()
    {
        //action
        if (Request::submitted('removerolebtn'))
        {
            if (count(Request::intArray('rolesel')))
            {
                $roles = RolePersistence::getAllRoles();
                $rolesel = Request::intArray('rolesel');
                foreach ($rolesel as $roleid)
                {
                    RolePersistence::deleteRole($roles[$roleid]);
                }
                $this->success = _('Die Rolle(n) und alle dazugehörigen Zuweisungen wurden erfolgreich gelöscht.');
            }
            else
            {
                $this->error = _('Bitte wählen sie eine Rolle zum Löschen aus.');
            }
        }

        //view
        $this->roles = RolePersistence::getAllRoles();
        $this->render_action('create_role');
    }

    /**
     * Enter description here...
     *
     */
    public function assign_role_action()
    {
        $roles = RolePersistence::getAllRoles();
        $usersearchtxt = Request::get('usersearchtxt');
        $usersel = Request::option('usersel');

        // zurücksetzen
        if (Request::submitted('resetseluser'))
        {
            unset($usersearchtxt);
        }

        // benutzer gesucht
        if (isset($usersearchtxt))
        {
            if ($usersearchtxt != '')
            {
                $founduser = $this->searchUser($usersearchtxt);
                if (empty($founduser))
                {
                    $this->error = _('Es wurde kein Benutzer gefunden.');
                }
            }
            else
            {
                $this->error = _('Es wurde kein Suchwort eingegeben.');
            }
        }

        // benutzer ausgewählt
        if (isset($usersel) && isset($founduser[$usersel]))
        {
            $selecteduser = $founduser[$usersel];

            // Rollen zuweisen
            if (Request::submitted('assignrolebtn'))
            {
                foreach (Request::intArray('rolesel') as $selroleid)
                {
                    $role = $roles[$selroleid];
                    RolePersistence::assignRole($selecteduser,$role);
                }
                $this->success = _('Zuweisungen erfolgreich durchgeführt.');
            }

            // Rollen löschen
            if (Request::submitted('deleteroleassignmentbtn'))
            {
                foreach (Request::intArray('assignedroles') as $roleid)
                {
                    $role = $roles[$roleid];
                    RolePersistence::deleteRoleAssignment($selecteduser,$role);
                }
                $this->success = _('Rollenzuweisung erfolgreich gelöscht.');
            }

            $implicitroles = array();
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
                    $implicitroles[] = $assignedrole->getRolename();
                }
            }
        }

        //view
        $this->users = $founduser;
        $this->currentuser = $selecteduser;
        $this->implicitroles = $implicitroles;
        $this->usersearchtxt = $usersearchtxt;
        $this->roles = $roles;
    }

    /**
     * Check role access permission for the given plugin.
     *
     * @param $plugin   plugin meta data
     * @param $role_id  role id of role
     */
    private function checkRoleAccess($plugin, $role_id)
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
    public function show_role_action()
    {
        $roleid = Request::int('role');

        //action
        if (!empty($roleid))
        {
            foreach (RolePersistence::getAllRoles() as $rolesel)
            {
                if ($roleid == $rolesel->getRoleid())
                {
                    $role = $rolesel;
                    break;
                }
            }
            //users
            $users = DBManager::get()->query("SELECT a.user_id AS userid, a.username, a.Vorname, a.Nachname
                                                FROM roles_user AS u LEFT JOIN auth_user_md5 AS a ON u.userid=a.user_id
                                                WHERE u.roleid = '".$roleid."' ORDER BY a.Nachname")->fetchAll(PDO::FETCH_ASSOC);

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
        $this->users = $users;
        $this->plugins = $plugins;
        $this->role = $role;
        $this->roleid = $roleid;
        $this->roles = RolePersistence::getAllRoles();
    }
}
?>
