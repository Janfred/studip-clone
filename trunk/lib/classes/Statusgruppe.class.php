<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/*
Statusgruppe.class.php - Statusgruppen-Klasse
Copyright (C) 2008 Till Glöggler <tgloeggl@uos.de>

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

/**
 * This class represents a single Statusgroup and additionally has some helper-functions
 * for working with multiple / structured groups
 *
 * @author tgloeggl
 */
class Statusgruppe
{
    var $new;
    var $messages = [];
    
    var $statusgruppe_id;
    var $name       = '';
    var $name_m     = '';
    var $name_w     = '';
    var $range_id   = '';
    var $position   = 0;
    var $size       = 0;
    var $selfassign = 0;
    var $mkdate     = 0;
    var $chdate     = 0;
    
    private $has_folder;
    private $is_sem;
    
    function __construct($statusgruppe_id = '')
    {
        if ($statusgruppe_id == '') {
            $this->new             = true;
            $this->statusgruppe_id = md5(uniqid(rand()));
        } else {
            $this->new             = false;
            $this->statusgruppe_id = $statusgruppe_id;
            $this->restore();
        }
    }
    
    /* * * * * * * * * * * * * * * * * * * *
     * * G E T T E R   /   S E T T E R * * *
     * * * * * * * * * * * * * * * * * * * */
    public function __call($method, $args)
    {
        if (mb_substr($method, 0, 3) == 'get') {
            $variable = mb_strtolower(mb_substr($method, 3, mb_strlen($method) - 3));
            if (property_exists($this, $variable)) {
                return $this->$variable;
            } else {
                throw new Exception(__CLASS__ . "::$method() does not exist!");
            }
        } else if (mb_substr($method, 0, 3) == 'set') {
            $variable = mb_strtolower(mb_substr($method, 3, mb_strlen($method) - 3));
            if (sizeof($args) != 1) {
                throw new Exception("wrong parameter count: " . __CLASS__ . "::$method() expects 1 parameter!");
            }
            $this->$variable = $args[0];
        }
    }
    
    function getId()
    {
        return $this->statusgruppe_id;
    }
    
    /* * * * * * * * * * * * * * * * * * * *
     * * * * * * D A T A B A S E * * * * * * 
     * * * * * * * * * * * * * * * * * * * */
    function restore()
    {
        if (!$this->statusgruppe_id) return;
        
        try {
            $stmt = DBManager::get()->prepare("SELECT * FROM statusgruppen WHERE statusgruppe_id = ?");
            if ($stmt->execute([$this->statusgruppe_id])) {
                $statusgruppe = $stmt->fetch();
                foreach ($statusgruppe as $key => $val) {
                    $this->$key = $val;
                }
            } else {
                throw new Exception(__CLASS__ . '::' . __FUNCTION__ . '() , line ' . __LINE__ . ': Error while querying statusgroup!');
            }
        } catch (PDOException $e) {
            echo $e->getMessage();
            die;
        }
    }
    
    function store()
    {
        try {
            
            if ($this->new) {
                $this->position = 0;
                
                // get the last position to insert the new group after
                $stmt = DBManager::get()->prepare("SELECT position FROM statusgruppen WHERE range_id = ? ORDER BY position DESC");
                $stmt->execute([$this->range_id]);
                if ($data = $stmt->fetch()) {
                    $this->position = $data['position'] + 1;
                }
                
                $query
                    = "INSERT INTO statusgruppen
                    (statusgruppe_id, name, range_id, position, size, selfassign, mkdate, chdate) VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)";
                
                $data = [$this->statusgruppe_id, $this->name, $this->range_id, $this->position, $this->size,
                    $this->selfassign, time(), time()];
            } else {
                $query
                    = "UPDATE statusgruppen SET
                    name = ?, range_id = ?, position = ?,
                    size = ?, selfassign = ?, chdate = ?
                    WHERE statusgruppe_id = ?";
                
                $data = [$this->name, $this->range_id, $this->position, $this->size,
                    $this->selfassign, time(), $this->statusgruppe_id];
            }
            
            $stmt   = DBManager::get()->prepare($query);
            $result = $stmt->execute($data);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die;
        }
        
    }
    
    function delete()
    {
        Statusgruppen::find($this->statusgruppe_id)->remove();
    }
    
    /* * * * * * * * * * * * * * * * * * * *
     * * H E L P E R   F U N C T I O N S * *
     * * * * * * * * * * * * * * * * * * * */
    
    function hasFolder()
    {
        // check, if we have a group-folder
        if ($this->isSeminar()) {
            if (!isset($this->has_folder)) {
                $stmt = DBManager::get()->prepare("SELECT COUNT(*) as c FROM folder WHERE range_id = ?");
                $stmt->execute([$this->statusgruppe_id]);
                
                $folder           = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->has_folder = ($folder['c'] == 1) ? true : false;
            }
            
            return $this->has_folder;
        }
        
        return false;
    }
    
    function isSeminar()
    {
        if (!isset($this->is_sem)) {
            $stmt = DBManager::get()->prepare("SELECT * FROM seminare WHERE Seminar_id = ?");
            $stmt->execute([$this->range_id]);
            
            if ($seminar = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->is_sem = true;
            } else {
                $this->is_sem = false;
            }
        }
        
        return $this->is_sem;
    }
    
    function getData()
    {
        global $invalidEntries;
        
        $role = [
            'id'         => $this->statusgruppe_id,
            'name'       => $this->name,
            'size'       => $this->size,
            'selfassign' => $this->selfassign,
            'folder'     => $this->hasFolder(),
        ];
        
        // we fetch the generic datafields for roles if this is an institute
        if (!$this->isSeminar()) {
            $datafields = DataFieldEntry::getDataFieldEntries([$this->range_id, $this->statusgruppe_id], 'roleinstdata');
            
            foreach ($datafields as $id => $field) {
                
                if (isset($invalidEntries[$id])) {
                    $invalid = true;
                } else {
                    $invalid = false;
                }
                
                $df[] = [
                    'name'           => $field->getName(),
                    'value'          => $field->getValue(),
                    'html'           => $field->getHTML('datafields'),
                    'datafield_id'   => $field->getID(),
                    'datafield_type' => $field->getType(),
                    'invalid'        => $invalid,
                ];
            }
            
            $role['datafields'] = $df;
        }
        
        return $role;
    }
    
    /**
     * add the classes messages to the submitted msg-array
     *
     * @param mixed $msgs the already present messages
     *
     * @return mixed the original+class messages
     */
    function getMessages($msgs)
    {
        foreach (['error', 'info', 'msg'] as $type) {
            if (is_array($this->messages[$type])) {
                foreach ($this->messages[$type] as $msg) {
                    if ($type === 'msg') {
                        $message = MessageBox::success($msg);
                    } else {
                        $message = MessageBox::$type($msg);
                    }
                    PageLayout::postMessage($message);
                }
            }
        }
        
        return $msgs;
    }
    
    /* * * * * * * * * * * * * * * * * * * *
     * * * S T A T I C   M E T H O D S * * *
     * * * * * * * * * * * * * * * * * * * */
    
    static function displayOptionsForRoles($roles, $omit_role = false, $level = 0)
    {
        if (is_array($roles)) foreach ($roles as $role_id => $role) {
            if ($omit_role != $role_id) {
                echo '<option value="' . $role_id . '">';
                for ($i = 1; $i <= $level; $i++) echo '&nbsp; &nbsp;';
                echo mb_substr($role['role']->getName(), 0, 70) . '</option>';
            }
            if ($role['child']) Statusgruppe::displayOptionsForRoles($role['child'], $omit_role, $level + 1);
        }
    }
    
    static function getFlattenedRoles($roles, $level = 0, $parent_name = false)
    {
        if (!is_array($roles)) return [];
        
        $ret = [];
        
        //var_dump($roles);
        foreach ($roles as $id => $role) {
            if (!isset($role['name'])) $role['name'] = $role['role']->getName();
            $spaces = '';
            for ($i = 0; $i < $level; $i++) $spaces .= '&nbsp;&nbsp;';
            
            // generate an indented version of the role-name
            $role['name'] = $spaces . $role['name'];
            
            // generate a name with all parent-roles in the name
            if ($parent_name) {
                $role['name_long'] = $parent_name . ' > ' . $role['role']->getName();
            } else {
                $role['name_long'] = $role['role']->getName();
            }
            
            $ret[$id] = $role;
            
            if ($role['child']) {
                $ret = array_merge($ret, Statusgruppe::getFlattenedRoles($role['child'], $level + 1, $role['name_long']));
            }
            
        }
        
        return $ret;
    }
    
    static function getFromArray($data)
    {
        $statusgruppe      = new Statusgruppe();
        $statusgruppe->new = false;
        
        $statusgruppe->statusgruppe_id = $data['statusgruppe_id'];
        $statusgruppe->name            = $data['name'];
        $statusgruppe->name_m          = $data['name_m'];
        $statusgruppe->name_w          = $data['name_w'];
        $statusgruppe->range_id        = $data['range_id'];
        $statusgruppe->position        = $data['position'];
        $statusgruppe->size            = $data['size'];
        $statusgruppe->selfassign      = $data['selfassign'];
        $statusgruppe->mkdate          = $data['mkdata'];
        $statusgruppe->chdate          = $data['chdate'];
        
        return $statusgruppe;
    }
}
