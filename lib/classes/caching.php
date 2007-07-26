<?php

/*
 * caching.php - all necessary classes to enable caching.
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * TODO
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marco Diedrich (mdiedric@uos)
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @since     1.6
 */

class StudipCacheFactory {


  /**
   * singleton instance
   *
   * @var StudipCache
   */
  static private $cache;


  /**
   * config instance
   *
   * @var Config
   */
  static private $config;


  function getConfig() {
    return is_null(self::$config) ? Config::getInstance() : self::$config;
  }


  function setConfig($config) {
    self::$config = $config;
  }


  function configure($file, $class, $arguments) {

    # TODO encoding for strings...
    $arguments = json_encode($arguments);

    $cfg = self::getConfig();

    $cfg->setValue($file,      'cache_class_file', '');
    $cfg->setValue($class,     'cache_class',      '');
    $cfg->setValue($arguments, 'cache_init_args',  '');
  }


  /**
   * Returns a cache instance.
   *
   * @return CacheFactory  the cache instance
   */
  function getCache() {

    if (is_null(StudipCacheFactory::$cache)) {

      $cfg = self::getConfig();

      $cache_class_file = $cfg->getValue('cache_class_file');
      $cache_class      = $cfg->getValue('cache_class');
      $cache_init_args  = $cfg->getValue('cache_init_args');

      # default class
      if (is_null($cache_class)) {
        $cache_class = 'StudipArrayCache';
      }

      # load class file before
      else {

        if (isset($cache_class_file))
          require_once $cache_class_file;

        if (!class_exists($cache_class))
          throw new Exception("Could not find class: '$cache_class'");
      }

      # decode argumentss
      $arguments = isset($cache_init_args)
                   ? json_decode($cache_init_args, TRUE)
                   : array();

      $reflection_class = new ReflectionClass($cache_class);
      StudipCacheFactory::$cache = $reflection_class->newInstanceArgs($arguments);
    }

    return StudipCacheFactory::$cache;
  }
}


/**
 * Interface for all caches.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marco Diedrich (mdiedric@uos)
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @since     1.6
 */

interface StudipCache {


  /**
   * Expire item from the cache.
   *
   * Example:
   *
   *   # expires foo
   *   $cache->expire('foo');
   *
   * @param   string  a single key.
   *
   * @returns TODO
   *
   */
  function expire($arg);


  /**
   * Retrieve item from the server.
   *
   * Example:
   *
   *   # reads foo
   *   $foo = $cache->reads('foo');
   *
   * @param   string  a single key
   *
   * @returns mixed  the previously stored data if an item with such a key
   *                 exists on the server or FALSE on failure.
   */
  function read($arg);


  /**
   * Store data at the server.
   *
   * @param string   the item's key.
   * @param string   the item's content.
   * @param int      the item's expiry time in seconds. Defaults to 12h.
   *
   * @returns mixed  returns TRUE on success or FALSE on failure.
   *
   */
  function write($name, $content, $expire = 43200);
}


/**
 *
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marco Diedrich (mdiedric@uos)
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @since     1.6
 */

class StudipArrayCache implements StudipCache {


  private $cache;


  function __construct($array = array()) {
    $this->cache = (array) $array;
  }


  /**
   * Expires just a single key.
   *
   * @param  string  the key
   *
   * @return void
   */
  function expire($key) {
    unset($this->cache[$key]);
  }


  /**
   * Reads just a single key from the cache.
   *
   * @param  string  the key
   *
   * @return mixed   the corresponding value
   */
  function read($key) {
    return isset($this->cache[$key]) ? $this->cache[$key] : NULL;
  }


  /**
   * Store data at the server.
   *
   * @param string   the item's key.
   * @param string   the item's content.
   * @param int      the item's expiry time in seconds. Defaults to 12h.
   *
   * @returns mixed  returns TRUE on success or FALSE on failure.
   *
   */
  function write($name, $content, $expire = 43200) {
    $this->cache[$name] = $content;
    return TRUE;
  }
}

