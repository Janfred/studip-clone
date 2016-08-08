<?php
/**
 * ModuleManagementModelTreeItem.php
 * Parent class of MVV-Objects used in tree and path views
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

require_once 'ModuleManagementModel.php';

abstract class ModuleManagementModelTreeItem extends ModuleManagementModel implements MvvTreeItem
{
    /**
     * The default route through the MVV object structure.
     * 
     * @var array
     */
    public static $TRAIL_DEFAULT = array(
        'MvvCourse',
        'Lvgruppe',
        'Modulteil',
        'Modul',
        'StgteilabschnittModul',
        'StgteilAbschnitt',
        'StgteilVersion',
        'StudiengangTeil',
        'Studiengang',
        'Abschluss',
        'AbschlussKategorie'
        );
    
    /**
     * An array of functions to filter mvv objects during path creation.
     * The class name is the key and the filter function the value.
     * 
     * @var array
     */
    protected static $object_filter = array(); 
    
    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/' . get_class() . '/trail_parent_id']);
    }
    
    public function getTrails($types = null, $mode = null, $path = null, $last = null)
    {
        $path = $path ?: self::$TRAIL_DEFAULT;
        $types = $types ?: $path;
        
        $trails = array();
        $class_name = get_class($this);
        
        if ($last) {
            $current = $path[array_search($last, $path) + 1];
        } else {
            $current = reset($path);
        }
        
        $parents = $this->getParents($current);
        if (!($mode & MvvTreeItem::TRAIL_SHOW_INCOMPLETE) && !count($parents)) {
            return array();
        }
        
        foreach ($parents as $parent) {
            if ($parent && $current != end($types)) {
                if ($this->checkFilter($parent)) {
                    foreach ($parent->getTrails($types, $mode, $path, $current) as $trail) {
                        if (in_array($class_name, $types)) {
                            $trail[$class_name] = $this;
                        }
                        $trails[] = $trail;
                    }
                }
            }
        }
        
        if (empty($trails) && in_array($class_name, $types)) {
            $trails = array(array($class_name => $this));
        }
        return $trails;
    }
    
    /**
     * Checks trails object filter.
     * 
     * @param MvvTreeItem $item The item to check.
     * @return boolean True if item has passed the check.
     */
    private function checkFilter(MvvTreeItem $item)
    {
        $filter = self::$object_filter[get_class($item)];
        if ($filter) {
            $checked = $filter['func']($item, $filter['params']);
            if (!$checked) {
                return false;
            }
        }
        return true;
    }
    
    protected static function sortTrails($trail_a, $trail_b)
    {
        
    }
    
    /**
     * Returns whether this object is assignable to courses.
     * 
     * @return boolean True if the object is assignable.
     */
    public function isAssignable()
    {
        return false;
    }
    
    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return count($this->getChildren()) > 0;
    }
    
    /**
     * Formats the trails to pathes. The path consists of alle names of the
     * objects of a trail glued together with the given delimiter.
     * 
     * @param type $trails
     * @param type $delimiter
     * @return type
     */
    public static function getPathes($trails, $delimiter = ' · ')
    {
        $pathes =  array();
        foreach ($trails as $trail) {
            $pathes[] = join($delimiter, array_map(
                    function($a) {
                        return $a->getDisplayName();
                    }, $trail));
        }
        sort($pathes, SORT_LOCALE_STRING);
        return $pathes;
    }
    
    /**
     * Filters trails by given object types.
     * 
     * @param array $trails An array of trails.
     * @param array $filter_objects An array of object class names.
     * @return array The filtered trails.
     */
    public static function filterTrails($trails, $filter_objects)
    {
        $filtered_trails = array();
        $trail_keys = array();
        foreach ($trails as $trail) {
            $temp_trail = array();
            $temp_keys = array();
            foreach ($trail as $trail_object) {
                if (in_array(get_class($trail_object), $filter_objects)) {
                    $temp_keys[] = $trail_object->getId();
                    $temp_trail[get_class($trail_object)] = $trail_object;
                }
            }
            // return only unique trails
            // (checked by the keys of the trails objects)
            if (!in_array($temp_keys, $trail_keys)) {
                $filtered_trails[] = $temp_trail;
            }
            $trail_keys[] = $temp_keys;
        }
        return $filtered_trails;
    }
    
    /**
     * Stores filter function to restrict pathes only to objects fulfilling
     * all conditions defined in this function.
     * 
     * @param string $class_name The name of the class.
     * @param Closure $filter_func The function defining the filter.
     * @param array $params Parameters used by filter function.
     * @throws InvalidArgumentException
     */
    public static function setObjectFilter($class_name, $filter_func, $params)
    {
        if (in_array('MvvTreeItem', class_implements($class_name))) {
            self::$object_filter[$class_name] =
                    ['func' => $filter_func, 'params' => $params];
        } else {
            throw new InvalidArgumentException();
        }
    }
}