<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'StudipCache.class.php';
require_once 'StudipNullCache.class.php';

/**
 * This factory retrieves the instance of StudipCache configured for use in
 * this Stud.IP installation.
 *
 * @package        studip
 * @subpackage lib
 *
 * @author        Marco Diedrich (mdiedric@uos)
 * @author        Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @since         1.6
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


    /**
     * Returns the currently used config instance
     *
     * @return Config        an instance of class Config used by this factory to
     *                       determine the class of the actual implementation of
     *                       the StudipCache interface; if no config was set, it
     *                       returns the instance returned by Config#getInstance
     * @see Config
     */
    static function getConfig()
    {
        return is_null(self::$config) ? Config::getInstance() : self::$config;
    }


    /**
     * @param    Config       an instance of class Config which will be used to
     *                        determine the class of the implementation of interface
     *                        StudipCache
     *
     * @return void
     */
    static function setConfig($config)
    {
        self::$config = $config;
    }


    /**
     * Configure the file, class and arguments used for instantiation of the the
     * StudipCache instance. After sending this method, the previously used cache
     * instance is voided and a new instance will be created on demand.
     *
     * @param    string             the absolute path to the implementing class
     * @param    string             the name of the class
     * @param    array              an array of custom arguments
     *
     * @return void
     */
    static function configure($file, $class, $arguments)
    {

        # TODO encoding for strings... but probably the caller should care..
        $arguments = json_encode($arguments);

        $cfg = self::getConfig();

        $cfg->setValue($file,
                       'cache_class_file',
                       'Absoluter Pfad der Datei, die die StudipCache-Klasse '.
                       'enthält');
        $cfg->setValue($class,
                       'cache_class',
                       'Klassenname des zu verwendenden StudipCaches');
        $cfg->setValue($arguments,
                       'cache_init_args',
                       'JSON-kodiertes Array von Argumenten für die '.
                       'Instanziierung der StudipCache-Klasse');

        self::$cache = NULL;
    }


    /**
     * Resets the configuration and voids the cache instance.
     *
     * @return void
     */
    static function unconfigure()
    {

        $cfg = self::getConfig();

        $cfg->unsetValue('cache_class_file');
        $cfg->unsetValue('cache_class');
        $cfg->unsetValue('cache_init_args');

        self::$cache = NULL;
    }


    /**
     * Returns a cache instance.
     *
     * @return StudipCache    the cache instance
     */
    static function getCache()
    {

        if (is_null(self::$cache)) {

            $cfg = self::getConfig();

            $cache_class_file = $cfg->getValue('cache_class_file');
            $cache_class      = $cfg->getValue('cache_class');
            $cache_init_args  = $cfg->getValue('cache_init_args');

            # default class
            if (is_null($cache_class)) {
                $cache_class = 'StudipNullCache';
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
            self::$cache = sizeof($arguments)
                            ? $reflection_class->newInstanceArgs($arguments)
                            : $reflection_class->newInstance();
        }

        return self::$cache;
    }
}

