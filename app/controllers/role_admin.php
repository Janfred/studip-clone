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

    private function check_ticket()
    {
        if (!check_ticket(Request::option('ticket'))) {
            throw new InvalidArgumentException(_('Das Ticket für diese Aktion ist ungültig.'));
        }

    }

    /**
     * Sucht nach Benutzern
     *
     * @param string $searchtxt
     * @return array of StudIPUser
     */
    private function search_user($searchtxt)
    {
        $searchtxt = "%" . $searchtxt . "%";
        $stmt = DBManager::get()->prepare(
          "SELECT user_id FROM auth_user_md5 ".
          "WHERE username LIKE ? OR Vorname LIKE ? OR Nachname LIKE ? ".
          "ORDER BY Vorname, Nachname, username");
        $stmt->execute(array($searchtxt, $searchtxt, $searchtxt));
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $users = array();

        foreach ($result as $row) {
            $user = new StudIPUser($row["user_id"]);
            $users[$row["user_id"]] = $user;
        }

        return $users;
    }

    /**
     * Get role statistics
     */
    private function get_role_stats($roles)
    {
        $db = DBManager::get();

        foreach ($roles as $role) {
            $roleid = $role->getRoleid();

            $sql = "SELECT COUNT(*) FROM roles_user WHERE roleid = $roleid AND userid != 'nobody'";
            $stats[$roleid]['users'] = $db->query($sql)->fetchColumn();

            $sql = "SELECT COUNT(*) FROM roles_plugins WHERE roleid = $roleid";
            $stats[$roleid]['plugins'] = $db->query($sql)->fetchColumn();
        }

        return $stats;
    }

    /**
     * Startfunktion
     *
     */
    public function index_action()
    {
        $this->roles = RolePersistence::getAllRoles();
        $this->stats = $this->get_role_stats($this->roles);
    }

    /**
     * Plugins Rollen zuweisen
     *
     */
    public function assign_plugin_role_action($pluginid = NULL)
    {
        $pluginid = Request::int('pluginid', $pluginid);

        $this->plugins = PluginManager::getInstance()->getPluginInfos();
        $this->assigned = RolePersistence::getAssignedPluginRoles($pluginid);
        $this->roles = RolePersistence::getAllRoles();
        $this->pluginid = $pluginid;
    }

    /**
     * Plugins Rollen zuweisen
     *
     */
    public function save_plugin_role_action($pluginid)
    {
        $this->check_ticket();

        if (Request::submitted('assign_role')) {
            // assign roles
            $selroles = Request::intArray('rolesel');
            RolePersistence::assignPluginRoles($pluginid, $selroles);
        } else if (Request::submitted('remove_role')) {
            // delete role assignment
            $delassignedrols = Request::intArray('assignedroles');
            RolePersistence::deleteAssignedPluginRoles($pluginid, $delassignedrols);
        }

        $this->flash['success'] = _('Die Rechteeinstellungen wurden gespeichert.');
        $this->redirect('role_admin/assign_plugin_role/'.$pluginid);
    }

    /**
     * Neue Rollen erstellen
     *
     */
    public function create_role_action()
    {
        $this->check_ticket();

        $name = Request::get('name');

        if ($name != '') {
            // create a new role
            $role = new Role();
            $role->setRolename($name);
            RolePersistence::saveRole($role);

            $this->flash['success'] = sprintf(_('Die Rolle "%s" wurde angelegt.'), htmlReady($name));
        } else {
            $this->flash['error'] = _('Sie haben keinen Namen eingegeben.');
        }

        $this->redirect('role_admin');
    }

    /**
     * Löscht Rollen aus der Datenbank
     *
     */
    public function ask_remove_role_action($roleid)
    {
        $this->delete_role = $roleid;
        $this->roles = RolePersistence::getAllRoles();
        $this->stats = $this->get_role_stats($this->roles);

        $this->render_action('index');
    }

    /**
     * Löscht Rollen aus der Datenbank
     *
     */
    public function remove_role_action($roleid)
    {
        $this->check_ticket();

        $roles = RolePersistence::getAllRoles();
        RolePersistence::deleteRole($roles[$roleid]);

        $this->flash['success'] = _('Die Rolle und alle dazugehörigen Zuweisungen wurden gelöscht.');
        $this->redirect('role_admin');
    }

    /**
     * Enter description here...
     *
     */
    public function assign_role_action($userid = NULL)
    {
        // benutzer gesucht
        if (Request::submitted('search')) {
            $username = Request::get('username');

            if ($username == '') {
                $this->error = _('Es wurde kein Suchwort eingegeben.');
            } else {
                $users = $this->search_user($username);

                if (empty($users)) {
                    $this->error = _('Es wurde kein Benutzer gefunden.');
                }
            }
        }

        // benutzer ausgewählt
        if (!Request::submitted('reset')) {
            $usersel = Request::option('usersel', $userid);

            if (isset($usersel)) {
                $users[$usersel] = new StudIPUser($usersel);
                $this->currentuser = $users[$usersel];
                $this->assignedroles = $this->currentuser->getAssignedRoles();
                $this->all_userroles = $this->currentuser->getAssignedRoles(true);
            }
        }

        $this->users = $users;
        $this->username = $username;
        $this->roles = RolePersistence::getAllRoles();
    }

    /**
     * Enter description here...
     *
     */
    public function save_role_action()
    {
        $roles = RolePersistence::getAllRoles();
        $usersel = Request::option('usersel');
        $selecteduser = new StudIPUser($usersel);

        $this->check_ticket();

        // Rollen zuweisen
        if (Request::submitted('assign_role')) {
            foreach (Request::intArray('rolesel') as $selroleid) {
                $role = $roles[$selroleid];
                RolePersistence::assignRole($selecteduser, $role);
            }
        // Rollen löschen
        } else if (Request::submitted('remove_role')) {
            foreach (Request::intArray('assignedroles') as $roleid) {
                $role = $roles[$roleid];
                RolePersistence::deleteRoleAssignment($selecteduser, $role);
            }
        }

        $this->flash['success'] = _('Die Rollenzuweisungen wurden gespeichert.');
        $this->redirect('role_admin/assign_role?usersel='.$usersel);
    }

    /**
     * Check role access permission for the given plugin.
     *
     * @param $plugin   plugin meta data
     * @param $role_id  role id of role
     */
    private function check_role_access($plugin, $role_id)
    {
        $plugin_roles = RolePersistence::getAssignedPluginRoles($plugin['id']);

        foreach ($plugin_roles as $plugin_role) {
            if ($plugin_role->getRoleid() == $role_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Zeigt alle Benutzer mit bestimmten Rollen an
     *
     */
    public function show_role_action($roleid = NULL)
    {
        $db = DBManager::get();
        $roleid = Request::int('role', $roleid);

        if (isset($roleid)) {
            foreach (RolePersistence::getAllRoles() as $rolesel) {
                if ($roleid == $rolesel->getRoleid()) {
                    $role = $rolesel;
                    break;
                }
            }

            $sql = "SELECT * FROM auth_user_md5 JOIN roles_user ON userid = user_id
                    WHERE roleid = '".$roleid."' ORDER BY Nachname, Vorname";

            $users = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $plugins = PluginManager::getInstance()->getPluginInfos();

            foreach ($plugins as $id => $plugin) {
                if (!$this->check_role_access($plugin, $roleid)) {
                    unset($plugins[$id]);
                }
            }

            $this->users = $users;
            $this->plugins = $plugins;
            $this->role = $role;
            $this->roleid = $roleid;
        }

        $this->roles = RolePersistence::getAllRoles();
    }
}
?>
