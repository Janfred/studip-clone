<?php

/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * Autoloads Stud.IP classes.
 *
 * @package    studip
 * @author     Marcus Lunzenauer
 */
class StudipAutoloader {

   /**
    * Registers Autoloader as an SPL autoloader.
    */
   static public function register()
   {
       spl_autoload_register(array(new self, 'autoload'));
   }

    /**
     * Handles autoloading of classes.
     *
     * @param  string  a class name.
     *
     * @return boolean returns true if the class has been loaded
     */
    public function autoload($class)
    {
        $formats = array(
            '%s/lib/classes/%s.class.php',
            '%s/lib/classes/%s.php'
        );
        foreach ($formats as $format) {
            $file = sprintf($format, $GLOBALS['STUDIP_BASE_PATH'], $class);
            if (file_exists($file)) {
                require $file;
                return TRUE;
            }
        }
        return FALSE;
  }
}
