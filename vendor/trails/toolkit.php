<?php

/*
 * toolkit.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Toolkit provides basic utility methods.
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author    Sean Kerr
 * @copyright (c) Authors
 * @version   $Id: toolkit.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class Trails_Toolkit {
  /**
   * Returns subject replaced with regular expression matchs
   *
   * @param mixed subject to search
   * @param array array of search => replace pairs
   */
  function pregtr($search, $replacePairs) {
    return preg_replace(array_keys($replacePairs),
                        array_values($replacePairs),
                        $search);
  }

  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return array <description>
   */
  function string_to_array($string) {
    preg_match_all('/
      \s*(\w+)              # key                               \\1
      \s*=\s*               # =
      (\'|")?               # values may be included in \' or " \\2
      (.*?)                 # value                             \\3
      (?(2) \\2)            # matching \' or " if needed        \\4
      \s*(?:
        (?=\w+\s*=) | \s*$  # followed by another key= or the end of the string
      )
    /x', $string, $matches, PREG_SET_ORDER);

    $attributes = array();
    foreach ($matches as $val)
      $attributes[$val[1]] = $val[3];

    return $attributes;
  }
  
  /**
   * Encodes argument to json.
   *
   * @param mixed argument to convert to json
   *
   * @return string the jsonified argument
   */
  function to_json($arg) {
    require_once 'json.php';
    return Trails_Json::encode($arg);
  }
}
