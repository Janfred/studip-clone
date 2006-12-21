<?php

/*
 * json.php - Provides basic JSON functionality
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Basic JSON functionality.
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: json.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class Trails_Json {

  /**
   * <MethodDescription>
   *
   * @param mixed <description>
   *
   * @return string <description>
   */
  function encode($value) {
    $enc =& new Trails_Json();
    return $enc->encode_value($value);
  }

  /**
   * @ignore
   */
  function encode_value(&$value) {

    if (is_object($value))
      return $this->encode_object($value);

    else if (is_array($value))
      return $this->encode_array($value);

    return $this->encode_datum($value);
  }
    
  /**
   * @ignore
   */
  function encode_object(&$value) {

    $props = array();
    foreach (get_object_vars($value) as $name => $propValue)
      if (isset($propValue))
        $props[] = sprintf('%s : %s', $this->encode_value($name),
                                      $this->encode_value($propValue));
    $props = implode(', ', $props);
    return sprintf('{%s}', $props);
  }

  /**
   * @ignore
   */
  function encode_array(&$array) {
    $tmpArray = array();
    $assoc = FALSE;

    foreach (array_keys($array) as $key) {
      if (!is_int($key)) {
        $assoc = TRUE;
        break;
      }
    }

    if ($assoc) {
      foreach ($array as $key => $value)
        $tmpArray[] = sprintf('%s : %s', $this->encode_string($key),
                                         $this->encode_value($value));
      $result = sprintf('{%s}', implode(', ', $tmpArray));
    }
    
    else {
      $result = '[';
      $length = count($array);
      for ($i = 0; $i < $length; $i++) {
      $tmpArray[] = $this->encode_value($array[$i]);
      }
      $result .= implode(', ', $tmpArray);
      $result .= ']';
    }

    return $result;
  }

  /**
   * @ignore
   */
  function encode_datum(&$value) {
    if (is_numeric($value)) return (string)$value;
    if (is_string($value))  return $this->encode_string($value);
    if (is_bool($value))    return $value ? 'true' : 'false';
    return 'null';
  }

  /**
   * @ignore
   */
  function encode_string(&$string) {
    $search  = array('\\', "\n", "\t", "\r", "\b", "\f", '"');
    $replace = array('\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\"');
    $string  = str_replace($search, $replace, $string);
    $string  = str_replace(array(chr(0x08), chr(0x0C)),
                           array('\b', '\f'), $string);
    return '"' . $string . '"';
  }
}
