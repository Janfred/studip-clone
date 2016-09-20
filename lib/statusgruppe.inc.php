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
* built a not existing ID
*
* @access private
* @return   string
*/
function MakeUniqueStatusgruppeID ()
{
    $query = "SELECT 1 FROM statusgruppen WHERE statusgruppe_id = ?";
    $presence = DBManager::get()->prepare($query);

    do {
        $tmp_id = md5(uniqid('status_gruppe', true));

        $presence->execute(array($tmp_id));
        $present = $presence->fetchColumn();
        $presence->closeCursor();
    } while ($present);

    return $tmp_id;
}


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


function GetAllSelected ($range_id, $level = 0)
{
    $query = "SELECT user_id, statusgruppe_id
              FROM statusgruppen
              LEFT JOIN statusgruppe_user USING (statusgruppe_id)
              WHERE range_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($range_id));
    $temp = $statement->fetchAll(PDO::FETCH_ASSOC);

    // WTF???
    if (empty($temp)) {
        return $level == 0 ? array() : false;
    }

    $selected = array();
    $role_ids = array();

    foreach ($temp as $row) {
        if ($row['user_id'] != null) {
            $selected[$row['user_id']] = true;
        }

        if (!$role_ids[$row['statusgruppe_id']]) {
            $zw = GetAllSelected($row['statusgruppe_id'], $level + 1);
            if ($zw) {
                $selected += array_fill_keys($zw, true);
            }
            $role_ids[$row['statusgruppe_id']] = true;
        }
    }

    return array_keys($selected);
}

function InsertPersonStatusgruppe ($user_id, $statusgruppe_id, $is_institute_group = true)
{
    $query = "SELECT 1 FROM statusgruppe_user WHERE user_id = ? AND statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($user_id, $statusgruppe_id));
    $present = $statement->fetchColumn();

    if ($present) {
        return false;
    }

    $position = CountMembersPerStatusgruppe($statusgruppe_id);

    $query = "INSERT INTO statusgruppe_user (statusgruppe_id, user_id, position)
              VALUES (?, ?, ?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($statusgruppe_id, $user_id, $position));

    // Only make Datafields default if it is indeed an institute group. Ref.: #2207
    if ($is_institute_group) {
        MakeDatafieldsDefault($user_id, $statusgruppe_id);
    }

    return true;
}

function MakeDatafieldsDefault($user_id, $statusgruppe_id, $default = 'default_value')
{
    $query = "REPLACE INTO datafields_entries (datafield_id, range_id, content, sec_range_id, mkdate, chdate)
              VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
    $insert = DBManager::get()->prepare($query);

    $fields = DataField::getDataFields('userinstrole');
    foreach ($fields as $field) {
        if ($field->editAllowed($GLOBALS['auth']->auth['perm'])) {
            $insert->execute(array($field->id, $user_id, $default, $statusgruppe_id));
        }
    }
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

function RemovePersonStatusgruppe ($username, $statusgruppe_id)
{
    $user = User::findByUsername($username);

    // Get user's position for later resorting
    $query = "SELECT position FROM statusgruppe_user WHERE statusgruppe_id = ? AND user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($statusgruppe_id, $user->user_id));
    $position = $statement->fetchColumn() ?: 0;

    // Delete user from statusgruppe
    $query = "DELETE FROM statusgruppe_user WHERE statusgruppe_id = ? AND user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($statusgruppe_id, $user->user_id));

    // Resort members
    $query = "UPDATE statusgruppe_user SET position = position - 1 WHERE statusgruppe_id = ? AND position > ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($statusgruppe_id, $position));
}

function RemovePersonFromAllStatusgruppen ($username)
{
    $user = User::findByUsername($username);

    $query = "DELETE FROM statusgruppe_user WHERE user_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($user->user_id));
    return $statement->rowCount();
}

function RemovePersonStatusgruppeComplete ($username, $range_id) {

    $result = getAllStatusgruppenIDS($range_id);
    $user = User::findByUsername($username);

    $query = "SELECT DISTINCT statusgruppe_id
              FROM statusgruppe_user
              LEFT JOIN statusgruppen USING (statusgruppe_id)
              WHERE range_id = ? AND user_id = ?";
    $statement = DBManager::get()->prepare($query);

    foreach ($result as $range_id) {
        $statement->execute(array($range_id, $user->user_id));
        $statusgruppen = $statement->fetchAll(PDO::FETCH_COLUMN);
        $statement->closeCursor();

        foreach ($statusgruppen as $id) {
            RemovePersonStatusgruppe($username, $id);
        }
    }
}

function DeleteStatusgruppe ($statusgruppe_id)
{
    $query = "SELECT position, range_id FROM statusgruppen WHERE statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($statusgruppe_id));
    $temp = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$temp) {
        return;
    }

    // get all child-statusgroups and put them as a child of the father, so they don't hang around without a parent
    $childs = getAllChildIDs($statusgruppe_id);
    if (!empty($childs)) {
        $query = "UPDATE statusgruppen SET range_id = ? WHERE statusgruppe_id IN (?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute(array($temp['range_id'], $childs));
    }

    // Remove statusgruppe, assigned users and assigned datafields
    $query = "DELETE s, su, de
              FROM statusgruppen AS s
                LEFT JOIN statusgruppe_user AS su USING(statusgruppe_id)
                LEFT JOIN datafields_entries AS de ON (s.statusgruppe_id = de.range_id)
              WHERE s.statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($statusgruppe_id));

    // Resort
    $query = "UPDATE statusgruppen SET position = position - 1 WHERE range_id = ? AND position > ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($temp['range_id'], $temp['position']));
}


function DeleteAllStatusgruppen ($range_id)
{
    $query = "SELECT statusgruppe_id FROM statusgruppen WHERE range_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($range_id));
    $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

    foreach ($ids as $id) {
        DeleteStatusgruppe($id);
    }

    return count($ids);
}

function moveStatusgruppe($role_id, $up_down = 'up')
{
    $query = "SELECT range_id, position FROM statusgruppen WHERE statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($role_id));
    $temp = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$temp) {
        return;
    }

    $position = $temp['position'];
    if ($up_down == 'up') {
        $other_position = $position - 1;
    } else {
        $other_position = $position + 1;
    }

    $query = "UPDATE statusgruppen SET position = ? WHERE range_id = ? AND position = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($position, $temp['range_id'], $other_position));

    $query = "UPDATE statusgruppen SET position = ? WHERE statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($other_position, $role_id));
}


function CheckUserStatusgruppe ($group_id, $object_id)
{
    static $groups = null;
    if ($groups === null) {
        $query = "SELECT statusgruppe_id FROM statusgruppe_user WHERE user_id = :id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':id', $object_id);
        $statement->execute();
        $groups = $statement->fetchAll(PDO::FETCH_COLUMN);
    }
    return in_array($group_id, $groups);
}



/**
* Returns the number of persons who are grouped in Statusgruppen for one range.
*
* Persons who are members in more than one Statusgruppe will be count only once
*
* @access public
* @param string $range_id The ID of a range with Statusgruppen
* @return int The number of members
*/
function CountMembersStatusgruppen ($range_id)
{
    $ids = getAllStatusgruppenIDS($range_id);
    if (empty($ids)) {
        return 0;
    }

    $query = "SELECT COUNT(DISTINCT user_id)
              FROM statusgruppen
              JOIN statusgruppe_user USING (statusgruppe_id)
              WHERE statusgruppe_id IN (?)";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($ids));
    return $statement->fetchColumn();
}

function CountMembersPerStatusgruppe ($group_id)
{
    $query = "SELECT COUNT(user_id)
              FROM statusgruppen
              JOIN statusgruppe_user USING (statusgruppe_id)
              WHERE statusgruppe_id = ?";
    $statement = DBManager::get()->prepare($query);
    $statement->execute(array($group_id));
    return $statement->fetchColumn();
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

function display_roles_recursive($roles, $level = 0, $pred = '') {
    if (is_array($roles))
    foreach ($roles as $role_id => $data) {
        if ($level > 0) {
            $title = $pred.' > '. $data['name'];
        } else {
            $title = $data['name'];
        }
        echo '<tr><td colspan="2" class="content_seperator"><b>'.$title.'</b></td></tr>';
        if ($persons = getPersonsForRole($role_id)) {
            $z = 1;
            if (is_array($persons))
            foreach ($persons as $p) {
                //echo '<tr><td '.$class.' width="20" align="center">'.$p['position'].'</td>';
                echo '<tr><td width="20" align="center">'.$z.'&nbsp;</td>';
                echo '<td><a href="'.URLHelper::getLink('dispatch.php/profile?username='.$p['username']).'">'.$p['fullname'].'</a></td>';
                $z++;
            }
        }
        echo '<tr><td colspan="2" class="blank">&nbsp;</td></tr>';
        echo '</tr>';
        if ($data['child']) {
            if ($level > 0) {
                $zw = $pred . ' > '.$data['name'];
            } else {
                $pred = $data['name'];
                $zw = $pred;
            }
            display_roles_recursive($data['child'], $level+1, $zw);
        }
    }
}

function GetRoleNames($roles, $level = 0, $pred = '', $all = false) {
    $out = array();

    if (is_array($roles))
    foreach ($roles as $role_id => $role) {
        if ($level == 0) $inst_id = $role_id;
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
