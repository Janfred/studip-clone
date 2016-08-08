<?php
/**
 * MvvTreeRoot.php
 * The root element of a path or tree of MVV objects. This is the root of the
 * tree or the end of a path.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * 
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 */

class MvvTreeRoot implements MvvTreeItem
{
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return null;
    }
    
    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return null;
    }
    
    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        $institute = array();
        $stmt = DBManager::get()->prepare('SELECT DISTINCT fakultaets_id '
                . 'FROM mvv_modul_inst mmi '
                . 'INNER JOIN mvv_modul USING(modul_id) '
                . 'INNER JOIN mvv_modulteil USING(modul_id) '
                . 'INNER JOIN mvv_lvgruppe_modulteil USING(modulteil_id) '
                . 'LEFT JOIN Institute USING(institut_id) '
                . 'WHERE mmi.gruppe = ?'
                . 'ORDER BY Institute.name ASC');
        
        $stmt->execute(array('hauptverantwortlich'));
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $institut) {
            $institute[$institut['fakultaets_id']] =
                    new Fachbereich($institut['fakultaets_id']);
        }
        return $institute;
    }
    
    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }
    
    public function getDisplayName()
    {
        return $GLOBALS['UNI_NAME_CLEAN'];
    }
    
    public function getId()
    {
        return 'root';
    }
    
    /**
     * @see MvvTreeItem::isAssignable()
     */
    public function isAssignable()
    {
        return false;
    }
    
    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
        return array();
    }
    
    /**
     * @see MvvTreeItem::getTrails()
     */
    public function getTrails($types = null, $mode = null, $path = null, $last = null)
    {
        $path = $path ?: ModuleManagementModelTreeItem::$TRAIL_DEFAULT;
        $types = $types ?: $path;
        
        $trails = array();
        $class_name = get_class($this);
        
        if ($last) {
            $current = $path[array_search($last, $path) + 1];
        } else {
            $current = reset($path);
        }
        $parents = $this->getParents($current);
        foreach ($parents as $parent) {
            if ($current != end($types)) {
                foreach ($parent->getTrails($types, $mode, $path, $current) as $trail) {
                    if (in_array($class_name, $types)) {
                        $trail[$class_name] = $this;
                    }
                    $trails[] = $trail;
                }
            }
        }
        if (empty($trails) && in_array($class_name, $types)) {
            $trails = array(array($class_name => $this));
        }
        return $trails;
    }
    
    
}