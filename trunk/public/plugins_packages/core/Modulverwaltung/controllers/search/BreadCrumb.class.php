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

    public function append($name)
    {
        if ($init) {
            $_SESSION['mvv_trail'] = array();
            $this->trail = array();
        }
        
        $trail = $this->trail;
        $trail[] = array('name' => $name, 'uri' => $_SERVER['REQUEST_URI']);
        $newTrail = array();
        $lastElement = false;
        foreach ($trail as $order => $trail) {
            if ($lastElement)
                break;
            $newTrail[$order] = $trail;
            if ($trail['name'] === $name) {
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