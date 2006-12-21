<?php

/*
 * asset_helper.php - Helps with assets.
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * AssetHelper.
 *
 * @package    trails
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id: asset_helper.php 3445 2006-05-30 14:27:52Z mlunzena $
 */

class AssetHelper {

  /**
   * Returns path to a javascript asset.
   *
   * Example:
   *
   *   javascript_path('ajax') => /javascripts/ajax.js
   */
  function javascript_path($source) {
    return AssetHelper::compute_public_path($source, 'javascripts', 'js');
  }

  /**
   * Returns a script include tag per source given as argument.
   *
   * Examples:
   *
   *   javascript_include_tag('xmlhr') =>
   *     <script language="JavaScript" type="text/javascript" src="/js/xmlhr.js"></script>
   *
   *   javascript_include_tag('common.javascript', '/elsewhere/cools') =>
   *     <script language="JavaScript" type="text/javascript" src="/js/common.javascript"></script>
   *     <script language="JavaScript" type="text/javascript" src="/elsewhere/cools.js"></script>
   */
  function javascript_include_tag() {
    $html = '';
    foreach (func_get_args() as $source) {
      $source = AssetHelper::javascript_path($source);
      $html .= TagHelper::content_tag('script', '',
                 array('type' => 'text/javascript', 'src' => $source));
      $html .= "\n";
    }

    return $html;
  }

  /**
   * Returns path to a stylesheet asset.
   *
   * Example:
   *
   *   stylesheet_path('style') => /stylesheets/style.css
   */
  function stylesheet_path($source) {
    return AssetHelper::compute_public_path($source, 'stylesheets', 'css');
  }

  /**
   * Returns a css link tag per source given as argument.
   *
   * Examples:
   *
   *   stylesheet_link_tag('style') =>
   *     <link href="/stylesheets/style.css" media="screen" rel="stylesheet" type="text/css" />
   *
   *   stylesheet_link_tag('style', array('media' => 'all'))  =>
   *     <link href="/stylesheets/style.css" media="all" rel="stylesheet" type="text/css" />
   *
   *   stylesheet_link_tag('random.styles', '/css/stylish') =>
   *     <link href="/stylesheets/random.styles" media="screen" rel="stylesheet" type="text/css" />
   *     <link href="/css/stylish.css" media="screen" rel="stylesheet" type="text/css" />
   */
  function stylesheet_link_tag() {
    $sources = func_get_args();
    $sourceOptions = (func_num_args() > 1 &&
                      is_array($sources[func_num_args() - 1]))
                      ? array_pop($sources)
                      : array();

    $html = '';
    foreach ($sources as $source) {
      $source = AssetHelper::stylesheet_path($source);
      $opt = array_merge(array('rel'   => 'stylesheet',
                               'type'  => 'text/css',
                               'media' => 'screen',
                               'href'  => $source),
                         $sourceOptions);
      $html .= TagHelper::tag('link', $opt) . "\n";
    }

    return $html;
  }

  /**
   * Returns path to an image asset.
   *
   * Example:
   *
   * The src can be supplied as a...
   *
   * full path,
   *   like "/my_images/image.gif"
   *
   * file name, 
   *   like "rss.gif", that gets expanded to "/images/rss.gif"
   *
   * file name without extension,
   *   like "logo", that gets expanded to "/images/logo.png"
   */
  function image_path($source) {
    return AssetHelper::compute_public_path($source, 'images', 'png');
  }

  /**
   * Returns an image tag converting the options instead html options on the
   * tag, but with these special cases:
   *
   * 'alt'  - If no alt text is given, the file name part of the +src+ is used
   *   (capitalized and without the extension)
   * * 'size' - Supplied as "XxY", so "30x45" becomes width="30" and height="45"
   *
   * The src can be supplied as a...
   * * full path, like "/my_images/image.gif"
   * * file name, like "rss.gif", that gets expanded to "/images/rss.gif"
   * * file name without extension, like "logo", that gets expanded to "/images/logo.png"
   */
  function image_tag($source, $opt = array()) {

    if (!$source) return '';

    $opt = TagHelper::_parse_attributes($opt);

    $opt['src'] = AssetHelper::image_path($source);

    if (!isset($opt['alt']))
      $opt['alt'] = ucfirst(current(explode('.', basename($opt['src']))));


    if (isset($opt['size'])) {
      list($opt['width'], $opt['height']) = split('x', $opt['size'], 2);
      unset($opt['size']);
    }

    return TagHelper::tag('img', $opt);
  }

  /**
   * @ignore
   */
  function compute_public_path($source, $dir, $ext) {

    # add extension if not present
    if (strpos(array_pop(explode('/', $source)), '.') === FALSE)
      $source = sprintf('%s.%s', $source, $ext);

    # url is not absolute
    if (strpos($source, ':') === FALSE) {

      # add dir if url does not contain a path 
      if ($source{0} != '/' && strpos($source, ':') === FALSE)
        $source = sprintf('/%s/%s', $dir, $source);

      # consider asset host
      $config =& Trails_Config::instance();
      if ($asset_host = $config->get('asset_host')) {
        $source = $asset_host . $source;
      }
      
      else {
        # add relative url
        $request = Trails_Request::instance();
        $source = $request->relative_url_root() . $source;
      }
    }

    return $source;
  }
}
