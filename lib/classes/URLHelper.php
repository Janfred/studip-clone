<?php
/*
 * URLHelper.php - utility functions for URL parameter handling
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/functions.php';

/**
 * The URLHelper class provides several utility functions (as class
 * methods) to ease the transition from using session data to URL
 * parameters.
 *
 * The most important method is URLHelper::getLink(), which appends
 * a number of additional parameters to a given URL. The parameters
 * can be set using the addLinkParam() or bindLinkParam() methods.
 */
class URLHelper
{
    /**
     * array of registered parameter values (initially empty)
     */
    static private $params = array();

    /**
     * Add a new link parameter. If a parameter with this name already
     * exists, its value will be replaced with the new one. All link
     * parameters will be included in the link returned by getLink().
     *
     * @param string $name  parameter name
     * @param string $value parameter value
     */
    static function addLinkParam ($name, $value)
    {
        self::$params[$name] = $value;
    }

    /**
     * Bind a new link parameter to a variable. If a parameter with this
     * name already exists, its value will re replaced with the binding.
     *
     * This method differs from addLinkParam() in two respects:
     *
     * - The bound variable is initialized with the parameter value in
     *   the current request.
     * - The parameter value is the value of the bound variable at the
     *   time getLink() is called.
     *
     * @param string $name  parameter name
     * @param string $var   variable to bind
     */
    static function bindLinkParam ($name, &$var)
    {
        if (isset($_REQUEST[$name])) {
            $var = $_REQUEST[$name];
        }

        self::$params[$name] = &$var;
    }

    /**
     * Remove a link parameter.
     *
     * @param string $name  parameter name
     */
    static function removeLinkParam ($name)
    {
        unset(self::$params[$name]);
    }

    /**
     * Augment the given URL by appending all registered link parameters.
     * Note that for each bound variable, its current value is used. You
     * can use the second parameter to add futher URL parameters to this
     * link without adding them globally. This method is identical to
     * getURL() except that it returns an entity encoded URL.
     *
     * @param string $url    relative or absolute URL
     * @param string $params array of additional link parameters to add
     *
     * @return string modified URL (entity encoded)
     */
    static function getLink ($url, $params = NULL)
    {
        return htmlspecialchars(self::getURL($url, $params));
    }

    /**
     * Augment the given URL by appending all registered link parameters.
     * Note that for each bound variable, its current value is used. You
     * can use the second parameter to add futher URL parameters to this
     * link without adding them globally.
     *
     * @param string $url    relative or absolute URL
     * @param string $params array of additional link parameters to add
     *
     * @return string modified URL
     */
    static function getURL ($url, $params = NULL)
    {
        $link_params = self::$params;
        $separator = strpos($url, '?') === false ? '?' : '&';

        if (isset($params)) {
            $link_params = array_merge($link_params, $params);
        }

        foreach ($link_params as $key => $value) {
            if (isset($value)) {
                $url .= $separator.urlencode($key).'='.urlencode($value);
                $separator = '&';
            }
        }

        return $url;
    }

    /**
     * Try to open the course or institute given by the parameter 'cid'
     * in the current request. This also binds the global $SessionSeminar
     * variable to the URL parameter 'cid' for links created by getLink().
     *
     * @return bool true if successful, false otherwise
     */
    static function setSeminarId ()
    {
        global $SessionSeminar;

        self::bindLinkParam('cid', $SessionSeminar);

        return openSem($SessionSeminar) || openInst($SessionSeminar);
    }
}
?>
