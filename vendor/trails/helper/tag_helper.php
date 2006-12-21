<?php

/*
 * tag_helper.php - TagHelper defines some base helpers to construct html tags.
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
 * TagHelper defines some base helpers to construct html tags.
 * This is poor man’s Builder for the rare cases where you need to
 * programmatically make tags but can’t use Builder.
 *
 * @package    trails
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: tag_helper.php 3437 2006-05-27 11:38:58Z mlunzena $
 */

class TagHelper {

  /**
   * Constructs an html tag.
   *
   * @param  $name    string  tag name
   * @param  $options array   tag options
   * @param  $open    boolean true to leave tag open
   *
   * @return string
   */
  function tag($name, $options = array(), $open = false) {
    if (!$name) return '';
    return '<'.$name.TagHelper::_tag_options($options).($open ? '>' : ' />');
  }

  /**
   * Helper function for content tags.
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return type <description>
   */
  function content_tag($name, $content = '', $options = array()) {
    if (!$name) return '';
    return '<'.$name.TagHelper::_tag_options($options).'>'.$content.'</'.$name.'>';
  }

  /**
   * Helper function for CDATA sections.
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function cdata_section($content) {
    return '<![CDATA['.$content.']]>';
  }

  /**
   * @ignore
   */
  function _tag_options($options = array()) {
    $options = TagHelper::_parse_attributes($options);
    $html = '';
    foreach ($options as $key => $value)
      $html .= ' '.$key.'="'.$value.'"';
    return $html;
  }

  /**
   * @ignore
   */
  function _parse_attributes($string) {
    return is_array($string) ? $string : Toolkit::string_to_array($string);
  }

  /**
   * @ignore
   */
  function _convert_options($opt) {
    $opt = TagHelper::_parse_attributes($opt);

    foreach (array('disabled', 'readonly', 'multiple') as $attribute)
      $opt = TagHelper::_boolean_attribute($opt, $attribute);

    return $opt;
  }

  /**
   * @ignore
   */
  function _boolean_attribute($opt, $attribute) {
    if (array_key_exists($attribute, $opt)) {
      if ($opt[$attribute])
        $opt[$attribute] = $attribute;
      else
        unset($opt[$attribute]);
    }

    return $opt;
  }

  /**
   * @ignore
   */
  function _get_option(&$options, $name, $default = NULL) {
    if (isset($options[$name])) {
      $value = $options[$name];
      unset($options[$name]);
    } else {
      $value = $default;
    }

    return $value;
  }
}
