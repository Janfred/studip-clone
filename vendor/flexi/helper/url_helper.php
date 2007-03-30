<?php

/*
 * url_helper.php - Help with javascripts.
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
 * UrlHelper.
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @copyright (c) Authors
 * @version   $Id: url_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */
class UrlHelper {

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function url_for($parameters = '') {

    $base = dirname($_SERVER['PHP_SELF']);

    if (is_string($parameters)) {
      // absolute URL?
      if (preg_match('#^[a-z]+\://#', $parameters))
        return $parameters;
      // relative URL?
      else
        return $base . '/' . $parameters;
    }

    return $base . '/' .implode('/', $parameters);
  }

  /**
   * Creates a link tag of the given 'name' using an URL created by the set of
   * 'options'. See the valid options in classes/ActionController/Base.html.
   * It's also possible to pass a string instead of an options hash to get a link
   * tag that just points without consideration. If nil is passed as a name, the
   * link itself will become the name.
   * The html_options have a special feature for creating javascript confirm
   * alerts where if you pass :confirm => 'Are you sure?',
   * the link will be guarded with a JS popup asking that question. If the user
   * accepts, the link is processed, otherwise not.
   *
   * Example:
   *   UrlHelper::link_to("Delete this page",
   *                      array('action' => "destroy", 'id' => $page->id),
   *                      array('confirm' => "Are you sure?"))
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function link_to($name = '', $options = '', $html_options = array()) {
    $html_options = TagHelper::_parse_attributes($html_options);
  
    $html_options = UrlHelper::_convert_options_to_javascript($html_options);
  
    $absolute = false;
    if (isset($html_options['absolute_url'])) {
      unset($html_options['absolute_url']);
      $absolute = true;
    }
  
    $html_options['href'] = UrlHelper::url_for($options, $absolute);
  
    if (isset($html_options['query_string'])) {
      $html_options['href'] .= '?'.$html_options['query_string'];
      unset($html_options['query_string']);
    }
  
    if (!strlen($name)) {
      $name = $html_options['href'];
    }
  
    return TagHelper::content_tag('a', $name, $html_options);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function button_to($name, $target, $options = array()) {
    $html_options = TagHelper::_convert_options($options);
    $html_options['value'] = $name;
 
    if (isset($html_options['post']) && $html_options['post']) {
      if (isset($html_options['popup'])) {
        trigger_error('You can\'t use "popup" and "post" together',
                      E_USER_ERROR);      
      }
      $html_options['type'] = 'submit';
      unset($html_options['post']);
      $html_options = UrlHelper::_convert_options_to_javascript($html_options);
 
      return form_tag($target, array('method' => 'post',
                                     'class' => 'button_to')).
             tag('input', $html_options).
             '</form>';
    } else if (isset($html_options['popup'])) {
      $html_options['type']    = 'button';
      $html_options = UrlHelper::_convert_options_to_javascript($html_options, $target);
 
      return tag('input', $html_options);
    } else {
      $html_options['type']    = 'button';
      $html_options['onclick'] = "document.location.href='".url_for($target)."';";
      $html_options = UrlHelper::_convert_options_to_javascript($html_options);
 
      return TagHelper::tag('input', $html_options);
    }
  }

  /**
   * @ignore
   */
  function _convert_options_to_javascript($html_options, $target = '') {

    // confirm
    $confirm = isset($html_options['confirm']) ? $html_options['confirm'] : '';
    unset($html_options['confirm']);

    // popup
    $popup = isset($html_options['popup']) ? $html_options['popup'] : '';
    unset($html_options['popup']);

    // post
    $post = isset($html_options['post']) ? $html_options['post'] : '';
    unset($html_options['post']);

    $onclick = isset($html_options['onclick']) ? $html_options['onclick'] : '';

    if ($popup && $post) {
      trigger_error('You can\'t use "popup" and "post" in the same link',
                    E_USER_ERROR);      
    } else if ($confirm && $popup) {
      $html_options['onclick'] = sprintf("%sif (%s) {%s};return false;",
        $onclick,
        UrlHelper::_confirm_js_func($confirm),
        UrlHelper::_popup_js_func($popup, $target));
    } else if ($confirm && $post) {
      $html_options['onclick'] = sprintf("%sif (%s) {%s};return false;",
      $onclick,
      UrlHelper::_confirm_js_func($confirm),
      UrlHelper::_post_js_func());
    } else if ($confirm) {
      if ($onclick) {
        $html_options['onclick'] = 'if ('.UrlHelper::_confirm_js_func($confirm).') {'.$onclick.'}';
      } else {
        $html_options['onclick'] = 'return '.UrlHelper::_confirm_js_func($confirm).';';
      }
    } else if ($post) {
      $html_options['onclick'] = $onclick.UrlHelper::_post_js_func().'return false;';
    } else if ($popup) {
      $html_options['onclick'] = $onclick.UrlHelper::_popup_js_func($popup, $target).'return false;';
    }

    return $html_options;
  }

  /**
   * @ignore
   */
  function _confirm_js_func($confirm) {
    return "confirm('".JsHelper::escape_javascript($confirm)."')";
  }

  /**
   * @ignore
   */
  function _popup_js_func($popup, $target = '') {
    $url = $target == '' ? 'this.href' : "'".UrlHelper::url_for($target)."'";

    if (is_array($popup)) {
      if (isset($popup[1])) {
        return "window.open(".$url.",'".$popup[0]."','".$popup[1]."');";
      } else {
        return "window.open(".$url.",'".$popup[0]."');";
      }
    } else {
      return "window.open(".$url.");";
    }
  }

  /**
   * @ignore
   */
  function _post_js_func() {
    return "f = document.createElement('form'); ".
           "document.body.appendChild(f); ".
           "f.method = 'POST'; ".
           "f.action = this.href; ".
           "f.submit();";
  }

  /**
   * @ignore
   */
  function _encodeText($text) {
    $encoded_text = '';

    for ($i = 0; $i < strlen($text); $i++) {
      $char = $text{$i};
      $r = rand(0, 100);

      # roughly 10% raw, 45% hex, 45% dec
      # '@' *must* be encoded. I insist.
      if ($r > 90 && $char != '@') {
        $encoded_text .= $char;
      } else if ($r < 45) {
        $encoded_text .= '&#x'.dechex(ord($char)).';';
      } else {
        $encoded_text .= '&#'.ord($char).';';
      }
    }

    return $encoded_text;
  }
}
