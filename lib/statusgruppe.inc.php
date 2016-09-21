<?
# Lifter001: DONE
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
* helper functions for handling statusgruppen
*
* helper functions for handling statusgruppen
*
* @author               Ralf Stockmann <rstockm@gwdg.de>
* @access               public
* @package          studip_core
* @modulegroup  library
* @module               statusgruppe.inc.php
*/
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// statusgruppe.inc.php
// Copyright (c) 2002 Ralf Stockmann <rstockm@gwdg.de>
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

/**
 * sets selfassign of a group to 0 or 1/2 dependend on the status of the other groups
 * @param statusgruppe_id:  id of statusgruppe in database
 * @param flag: 0 for users are not allowed to assign themselves to this group
 *                          or 1 / 2 to set selfassign to the value of the other statusgroups
 *                          of the same seminar for which selfassign is allowed. If no such
 *                          group exists, selfassign is set to the value of flag, 1 means
 *                          selfassigning is allowed and 2 it's only allowed for a maximum
 *                          of one group.
 */
function SetSelfAssign ($statusgruppe_id, $flag="0") {
    $db = DBManager::get();
    if ($flag != 0) {
        $query = "SELECT selfassign FROM statusgruppen WHERE selfassign = ? AND range_id = (
                      SELECT range_id
                      FROM statusgruppen
                      WHERE statusgruppe_id = ?
                  )";
        $statement = DBManager::get()->prepare($query);

        $statement->execute(array(2, $statusgruppe_id));
        if ($temp = $statement->fetchColumn()) {
            $flag = $temp;
        } else {
            $statement->execute(array(1, $statusgruppe_id));

            if ($temp = $statement->fetchColumn()) {
                $flag = $temp;
            }
        }
    }

    $query = "UPDATE statusgruppen SET selfassign = ? WHERE statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($flag, $statusgruppe_id));

    return $flag;
}


// find all "statusgruppen_ids" which are connected to a certain range_id
function getStatusgruppenIDS($range_id)
{
    $query = "SELECT statusgruppe_id FROM statusgruppen WHERE range_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($range_id));
    return $statement->fetchAll(PDO::FETCH_COLUMN);
}

// find the complete "statusgruppen_id-hierarchy" associated with an range_id
function getAllStatusgruppenIDS($range_id)
{
    $agenda =array($range_id);
    $result = array();
    while(sizeof($agenda)>0)
    {
        $current = array_pop($agenda);
        $result[] =  $current;
        $agenda = array_merge((array)getStatusgruppenIDS($current), (array)$agenda);
    }
    return $result;
}




/**
* Returns all statusgruppen for the given range.
*
* If there is no statusgruppe for the given range, it returns FALSE.
*
* @access   public
* @param    string  $range_id
* @param    string  $user_id
* @return   array   (structure statusgruppe_id => name)
*/
function GetAllStatusgruppen($parent, $check_user = null, $exclude = false)
{
    $query = "SELECT * FROM statusgruppen WHERE range_id = ? ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($parent));
    $groups = $statement->fetchAll(PDO::FETCH_ASSOC);

    if (empty($groups)) {
        return false;
    }

    $query = "SELECT visible FROM statusgruppe_user WHERE user_id = ? AND statusgruppe_id = ?";
    $presence = DBManager::get()->prepare($query);

    $childs = array();
    foreach ($groups as $group) {
        $user_there = $visible = $user_in_child = false;

        $kids = getAllStatusgruppen($group['statusgruppe_id'], $check_user, $exclude);

        if ($check_user) {
            $presence->execute(array($check_user, $group['statusgruppe_id']));
            $present = $presence->fetchColumn();
            $presence->closeCursor();

            if ($user_there = ($present !== false)) {
                $visible = $present;
            }

            if (is_array($kids)) {
                foreach ($kids as $kid) {
                    if ($kid['user_there'] || $kid['user_in_child']) {
                        $user_in_child = true;
                    }
                }
            }
        }

        if (!$check_user || !$exclude || $user_in_child || $user_there) {
            $childs[$group['statusgruppe_id']] = array(
                'role'          => Statusgruppe::getFromArray($group),
                'visible'       => $visible,
                'user_there'    => $user_there,
                'user_in_child' => $user_in_child,
                'child'         => $kids
            );
        }
    }

    return is_array($childs) ? $childs : false;
}


function isVatherDaughterRelation($vather, $daughter) {
    $children = getAllChildIDs($vather);
    return array_key_exists($daughter, $children);
}

function getAllChildIDs($range_id)
{
    $query = "SELECT statusgruppe_id, name FROM statusgruppen WHERE range_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($range_id));
    $zw = $statement->fetchGrouped(PDO::FETCH_COLUMN);

    $ids = array_keys($zw);
    foreach (array_keys($zw) as $id) {
        $zw = array_merge($zw, getAllChildIDs($id));
    }

    return $zw;
}

/**
 * 
 */
function GetRoleNames($roles, $level = 0, $pred = '', $all = false) {
    $out = array();

    if (is_array($roles))
    foreach ($roles as $role_id => $role) {
        if (!$role['name']) $role['name'] = $role['role']->getName();

        if ($pred != '') {
            $new_pred = $pred.' > '.$role['name'];
        } else {
            $new_pred = $role['name'];
        }

        if ($role['user_there'] || $all) {
            $out[$role_id] = $new_pred;
        }

        if ($role['child']) {
            $out = array_merge((array)$out, (array)GetRoleNames($role['child'], $level+1, $new_pred, $all));
        }
    }

    return (sizeof($out) > 0 ? $out : null);
}

function get_role_data_recursive($roles, $user_id, &$default_entries, $filter = null, $level = 0, $pred = '') {
    global $auth, $user, $has_denoted_fields;

    $out = '';
    $out_table = array();

    if (is_array($roles))
    foreach ($roles as $role_id => $role) {

        $the_user = User::find($user_id);

        switch ($the_user->geschlecht) {
            case 2:
                $role['name'] = $role['role']->getName_w() ?: $role['role']->getName();
                break;
            case 1:
                $role['name'] = $role['role']->getName_m() ?: $role['role']->getName();
                break;
            default:
                $role['name'] = $role['role']->getName();
                break;
        }

        $out_zw = '';

        if ($pred != '') {
            $new_pred = $pred.' > '.$role['name'];
        } else {
            $new_pred = $role['name'];
        }

      $entries = DataFieldEntry::getDataFieldEntries(array($user_id, $role_id));

        if ($role['user_there']) {
            $out_zw .= '<tr><td>'
                    .  Assets::img('forumgrau2.png')
                    .  '</td><td colspan="2"><b>'. htmlReady($new_pred) .'</b></td></tr>';
            $zw = '<td %class%></td><td %class%><font size="-1">'. htmlReady($new_pred) .'</font></td>';
        }

        $zw2 = '';
        $has_value = false;

        if (is_array($entries))
        foreach ($entries as $id => $entry) {
            $default = false;
            if ($filter == null || in_array($id, $filter) === TRUE) {
                if ($entry->getValue() == 'default_value') {
                    $value = $default_entries[$id]->getDisplayValue();
                    $default = true;
                } else {
                    $value = $entry->getDisplayValue();
                }

                $name = $entry->getName();
                if ($role['user_there']) {
                    $view = $entry->isVisible();
                    $show_star = false;
                    if (!$view && ($user_id == $user->id)) {
                        $view = true;
                        $show_star = true;
                        $has_denoted_fields = true;
                    }

                    if ($view) { // Sichtbarkeitsberechtigung
                        $zw2 .= '<td %class%><font size="-1">'. trim($value);
                        if ($show_star) $zw2 .= ' *';
                        $zw2 .= '</font></td>';

                        if (trim($value)) {
                            $has_value = true;
                            if (!$default) {
                                $out_zw .= '<tr><td></td><td>'. htmlReady($name) .':&nbsp;&nbsp;</td><td>'.trim($value);
                                if ($show_star) $out_zw .= ' *';
                                $out_zw .= '</td></tr>';
                            }
                        }
                    }   // Ende Sichtbarkeitsberechtigung

                }
            }

        }

        if ($role['user_there'] && $role['visible']) {
            $out_table[] = $zw.$zw2;
            $out .= $out_zw;
        }

        if ($role['child']) {
            $back = get_role_data_recursive($role['child'], $user_id, $default_entries, $filter, $level+1, $new_pred);
            $out .= $back['standard'];
            $out_table = array_merge((array)$out_table, (array)$back['table']);
        }
    }

    return array('standard' => $out, 'table' => $out_table);
}

function getPersonsForRole($role_id)
{
    global $_fullname_sql;

    $query = "SELECT user_id, {$_fullname_sql['full_rev']} AS fullname, username, position
              FROM statusgruppe_user
              LEFT JOIN auth_user_md5 USING (user_id)
              LEFT JOIN user_info USING (user_id)
              WHERE statusgruppe_id = ?
              ORDER BY position";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($role_id));
    return $statement->fetchGrouped(PDO::FETCH_ASSOC);
}



/**
 * Ensure that a user has a valid default institute set if applicable,
 * i.e. he/she is member of at least one institute and has status 'autor'
 * or higher.
 *
 * @param string $user_id       user id
 */
function checkExternDefaultForUser($user_id) {
    if (!getExternDefaultForUser($user_id)) {
        $stmt = DBManager::get()->prepare("UPDATE user_inst SET externdefault = 1 WHERE user_id = ? AND inst_perms != 'user' ORDER BY priority LIMIT 1");
        $stmt->execute(array($user_id));
    }
}

/**
 * Return the id of the default institute for a user (if set).
 *
 * @param string $user_id       user id
 *
 * @return string  institute id or FALSE
 */
function getExternDefaultForUser($user_id) {
    $stmt = DBManager::get()->prepare("SELECT Institut_id FROM user_inst WHERE user_id = ? AND inst_perms != 'user' AND externdefault = 1");
    $stmt->execute(array($user_id));
    return $stmt->fetchColumn();
}
