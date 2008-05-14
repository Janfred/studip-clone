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
     * @param mixed  $value parameter value
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
     * @param mixed  $var   variable to bind
     */
    static function bindLinkParam ($name, &$var)
    {
        if (isset($_REQUEST[$name])) {
            $var = $_REQUEST[$name];
        }

        self::$params[$name] = &$var;
    }

    /**
     * Get the list of currently registered link parameters.
     *
     * @return array list of registered link parameters
     */
    static function getLinkParams ()
    {
        return self::$params;
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
     * link without adding them globally. Any parameters included in the
     * argument list take precedence over registered link parameters of
     * the same name. This method is identical to getURL() except that it
     * returns an entity encoded URL suitable for use in HTML attributes.
     *
     * @param string $url    relative or absolute URL
     * @param array  $params array of additional link parameters to add
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
     * link without adding them globally. Any parameters included in the
     * argument list take precedence over registered link parameters of
     * the same name.
     *
     * @param string $url    relative or absolute URL
     * @param array  $params array of additional link parameters to add
     *
     * @return string modified URL
     */
    static function getURL ($url, $params = NULL)
    {
        $link_params = self::$params;

        list($url, $fragment) = explode('#', $url);
        list($url, $query)    = explode('?', $url);

        if (isset($query)) {
            parse_str($query, $query_params);
            $link_params = array_merge($link_params, $query_params);
        }

        if (isset($params)) {
            $link_params = array_merge($link_params, $params);
        }

        if (count($link_params)) {
            $url .= '?'.http_build_query($link_params);
        }

        if (isset($fragment)) {
            $url .= '#'.$fragment;
        }

        return $url;
    }
}
?>
