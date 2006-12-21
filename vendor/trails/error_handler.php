<?php

/*
 * error_handler.php - Custom error handler for trails.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * <ClassDescription>
 *
 * @package     <package>
 * @subpackage  <package>
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class Trails_ErrorHandler {


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function errors($e = NULL) {

    static $error_types = array(
      E_ERROR           => "Error",
      E_WARNING         => "Warning",
      E_PARSE           => "Parsing Error",
      E_NOTICE          => "Notice",
      E_CORE_ERROR      => "Core Error",
      E_CORE_WARNING    => "Core Warning",
      E_COMPILE_ERROR   => "Compile Error",
      E_COMPILE_WARNING => "Compile Warning",
      E_USER_ERROR      => "User Error",
      E_USER_WARNING    => "User Warning",
      E_USER_NOTICE     => "User Notice",
      E_STRICT          => "Runtime Notice");

    static $errors;
    if (is_null($errors)) {
      $errors = array();
    }

    if (!is_null($e))
      # $errors[] = $e;
      $errors[] = sprintf("%s (%s:%d): %s\n",
                          $error_types[$e['no']], basename($e['file']),
                          $e['line'], $e['str']);
    else
      return $errors;
  }

  /**
   * Custom error handler.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function error_handler($no, $str, $file, $line, $context) {
    if ($no & error_reporting())
      Trails_ErrorHandler::errors(compact('no', 'str', 'file', 'line', 'context'));
  }
}
