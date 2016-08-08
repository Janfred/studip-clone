<?php
/**
 * index.php - Main index controller
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

require_once dirname(__FILE__) . '/MVV.class.php';

class IndexController extends MVVController
{
    
    public function index_action()
    {
        $this->redirect('studiengaenge/studiengaenge');
    }
    
    public function rescue($exception)
    {
        $this->redirect('studiengaenge/studiengaenge');
    }
    
}