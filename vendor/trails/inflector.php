<?php

/*
 * inflector.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * <ClassDescription>
 *
 * @package   trails
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright (c) Authors
 * @version   $Id: inflector.php 4195 2006-10-25 09:46:28Z mlunzena $
 */

class Trails_Inflector {

  /**
   * Returns a camelized string from a lower case and underscored string by
   * replaceing slash with underscore and upper-casing each letter preceded
   * by an underscore.
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  function camelize($word) {
    return str_replace(' ', '', ucwords(str_replace('_', ' ',$word)));
  }

  /**
   * Returns an underscore-syntaxed version or the CamelCased string.
   *
   * @param string String to underscore.
   *
   * @return string Underscored string.
   */
  function underscore($word) {
    return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
  }

  /**
   * Returns classname in underscored form, with "_id" tacked on at the end.
   * This is for use in dealing with foreign keys in the database.
   *
   * @param string Class name.
   * @param boolean Seperate with underscore.
   *
   * @return strong Foreign key
   */
  function foreign_key($class_name, $separate = true) {
    return Trails_Inflector::underscore($class_name).($separate ? '_id' : 'id');
  }

  /**
   * Returns corresponding table name for given classname.
   *
   * @param string Name of class to get database table name for.
   *
   * @return string Name of the database table for given class.
   */
  function tableize($class_name) {
    return Trails_Inflector::underscore($class_name);
  }

  /**
   * Returns model class name for given database table.
   *
   * @param string Table name.
   *
   * @return string Classified table name.
   */
  function classify($table_name) {
    return Trails_Inflector::camelize($table_name);
  }

  /**
   * Returns a human-readable string from a lower case and underscored word by
   * replacing underscores with a space, and by upper-casing the initial
   * characters.
   *
   * @param string String to make more readable.
   *
   * @return string Human-readable string.
   */
  function humanize($word) {
    return ucwords(str_replace('_', ' ', $word));
  }
}
