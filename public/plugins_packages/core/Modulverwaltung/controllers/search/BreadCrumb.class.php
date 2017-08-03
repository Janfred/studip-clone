<?php
/**
 * breadcrumb.php - Provides bread crumb navigation
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
class BreadCrumb
{

    private $trail = array();

    public function __construct()
    {
        $this->trail = isset($_SESSION['mvv_trail']) ? $_SESSION['mvv_trail'] : array();
    }
    
    public function append($object, $id = null)
    {
        $trail = $this->trail;
        if (is_object($object)) {
            $id = $object->id;
            $trail[$id] = [
                'type' => get_class($object),
                'uri' => $_SERVER['REQUEST_URI']
            ];
        } else if (is_array($object)) {
            $id = reset($object)->id;
            foreach ($object as $obj) {
                if ($obj->id != $id) {
                    $additional_objects[get_class($obj)] = $obj->id;
                }
            }
            $trail[$id] = [
                'type' => get_class($object[0]),
                'addition' => $additional_objects,
                'uri' => $_SERVER['REQUEST_URI']
            ];
        } else {
            $trail[$id] = [
                'name' => $object,
                'uri' => $_SERVER['REQUEST_URI']
            ];
        }
        $newTrail = [];
        $lastElement = false;
        foreach ($trail as $key => $trail_item) {
            if ($lastElement)
                break;
            $newTrail[$key] = $trail_item;
            if ($key === $id) {
                $lastElement = true;
            }
        }
        $this->trail = $newTrail;
        $this->store();
    }

    public function pop()
    {
        array_pop($this->trail);
    }

    public function getTrail()
    {
        return $this->trail;
    }

    public function init()
    {
        $_SESSION['mvv_trail'] = array();
        $this->trail = array();
    }

    private function store()
    {
        $_SESSION['mvv_trail'] = $this->trail;
    }

    public function __destruct()
    {
        $this->store();
    }

}