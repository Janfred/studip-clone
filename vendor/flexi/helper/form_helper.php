<?php

/*
 * form_helper.php - Helps with forms.
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
 * FormHelper.
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @author    Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author    David Heinemeier Hansson
 * @copyright (c) Authors
 * @version   $Id: form_helper.php 4710 2006-12-14 14:34:37Z mlunzena $
 */
class FormHelper {

  /**
   * Accepts a container (hash, array, enumerable, your type) and returns a
   * string of option tags. Given a container where the elements respond to
   * first and last (such as a two-element array), the "lasts" serve as option
   * values and the "firsts" as option text. Hashes are turned into this form
   * automatically, so the keys become "firsts" and values become lasts.
   * If +selected+ is specified, the matching "last" or element will get the
   * selected option-tag.  +Selected+ may also be an array of values to be
   * selected when using a multiple select.
   *
   * Examples (call, result):
   *   options_for_select([["Dollar", "$"], ["Kroner", "DKK"]])
   *     <option value="$">Dollar</option>\n<option value="DKK">Kroner</option>
   *
   *   options_for_select([ "VISA", "MasterCard" ], "MasterCard")
   *     <option>VISA</option>\n<option selected="selected">MasterCard</option>
   *
   *   options_for_select({ "Basic" => "$20", "Plus" => "$40" }, "$40")
   *     <option value="$20">Basic</option>\n
   *     <option value="$40" selected="selected">Plus</option>
   *
   *   options_for_select([ "VISA", "MasterCard", "Discover" ],
   *                      ["VISA", "Discover"])
   *     <option selected="selected">VISA</option>\n<option>MasterCard</option>
   *     <option selected="selected">Discover</option>
   *
   * NOTE: Only the option tags are returned, you have to wrap this call in a
   *       regular HTML select tag.
   */
  function options_for_select($opt = array(), $selected = '', $html_opt = array()) {
    $html_opt = TagHelper::_parse_attributes($html_opt);

    if (is_array($selected)) {
      $valid = array_map('strval', array_values($selected));
    }

    $html = '';

    if (isset($html_opt['include_custom']))
      $html .= content_tag('option', $html_opt['include_custom'],
                           array('value' => ''))."\n";
    else if (isset($html_opt['include_blank']))
      $html .= content_tag('option', '', array('value' => ''))."\n";

    foreach ($opt as $key => $value) {
      $option_options = array('value' => $key);
      if (
          isset($selected)
          &&
          (is_array($selected) && in_array(strval($key), $valid, true))
          ||
          (strval($key) == strval($selected))
         ) {
        $option_options['selected'] = 'selected';
      }

      $html .= content_tag('option', $value, $option_options)."\n";
    }

    return $html;
  }

  /**
   * Starts a form tag that points the action to an url configured with
   * <tt>url_for_options</tt> url_for. The method for the form defaults to POST.
   *
   * Options:
   * <tt>:multipart</tt> - If set to true, the enctype is set to
   *                       "multipart/form-data".
   */
  function form_tag($url_for_options = '', $opt = array()) {
    $opt = TagHelper::_parse_attributes($opt);

    $html_opt = $opt;
    if (!array_key_exists('method', $html_opt)) {
      $html_opt['method'] = 'post';
    }

    if (array_key_exists('multipart', $html_opt)) {
      $html_opt['enctype'] = 'multipart/form-data';
      unset($html_opt['multipart']);
    }

    $html_opt['action'] = UrlHelper::url_for($url_for_options);

    return TagHelper::tag('form', $html_opt, true);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function select_tag($name, $option_tags = NULL, $opt = array()) {
    return content_tag('select', $option_tags,
      array_merge(array('name' => $name, 'id' => $name),
                  TagHelper::_convert_options($opt)));
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function input_tag($name, $value = NULL, $opt = array()) {
    if ($value === NULL && isset($opt['type']) && $opt['type'] == 'password')
      $value = NULL;
    else if (($reqvalue = FormHelper::_get_request_value($name)) !== NULL)
      $value = $reqvalue;

    $std = array('type' => 'text', 'name' => $name, 'id' => $name,
                 'value' => $value);
    return tag('input', array_merge($std, TagHelper::_convert_options($opt)));
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function input_hidden_tag($name, $value = NULL, $opt = array()) {
    $opt = TagHelper::_parse_attributes($opt);

    $opt['type'] = 'hidden';
    return FormHelper::input_tag($name, $value, $opt);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function input_file_tag($name, $opt = array()) {
    $opt = TagHelper::_parse_attributes($opt);

    $opt['type'] = 'file';
    return input_tag($name, NULL, $opt);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function input_password_tag($name = 'password', $value = NULL,
                              $opt = array()) {
    $opt = TagHelper::_parse_attributes($opt);

    $opt['type'] = 'password';
    return input_tag($name, $value, $opt);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function text_area_tag($name, $content = NULL, $opt = array()) {
    if (($reqvalue = FormHelper::_get_request_value($name)) !== NULL)
      $content = $reqvalue;

    $opt = TagHelper::_parse_attributes($opt);

    if (array_key_exists('size', $opt)) {
      list($opt['cols'], $opt['rows']) = split('x', $opt['size'], 2);
      unset($opt['size']);
    }

    if (isset($opt['id']))  {
      $id = $opt['id'];
      unset($opt['id']);
    } else {
      $id = $name;
    }

    $std = array('name' => $name, 'id' => $id);
    return TagHelper::content_tag('textarea', htmlspecialchars($content),
                       array_merge($std, TagHelper::_convert_options($opt)));
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function checkbox_tag($name, $value = '1', $checked = false, $opt = array()) {

    #$request = sfContext::getInstance()->getRequest();
    if ($request->hasErrors())
      $checked = $request->getParameter($name, NULL);
    elseif (($reqvalue = FormHelper::_get_request_value($name)) !== NULL)
      $checked = $reqvalue;

    $std = array('type' => 'checkbox', 'name' => $name, 'id' => $name,
                 'value' => $value);
    $html_opt = array_merge($std, TagHelper::_convert_options($opt));
    if ($checked)
      $html_opt['checked'] = 'checked';

    return tag('input', $html_opt);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function radiobutton_tag($name, $value, $checked = false, $opt = array()) {
    if (($reqvalue = FormHelper::_get_request_value($name)) !== NULL)
      $checked = $reqvalue;

    $std = array('type' => 'radio', 'name' => $name, 'value' => $value);
    $html_opt = array_merge($std, TagHelper::_convert_options($opt));
    if ($checked)
      $html_opt['checked'] = 'checked';

    return TagHelper::tag('input', $html_opt);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function input_upload_tag($name, $opt = array()) {
    $opt = TagHelper::_parse_attributes($opt);

    $opt['type'] = 'file';

    return input_tag($name, '', $opt);
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function submit_tag($value = 'Save changes', $opt = array()) {
    $std = array('type' => 'submit', 'name' => 'commit', 'value' => $value);
    return TagHelper::tag('input', array_merge($std, TagHelper::_convert_options($opt)));
  }

  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function reset_tag($value = 'Reset', $opt = array()) {
    $std = array('type' => 'reset', 'name' => 'reset', 'value' => $value);
    return tag('input', array_merge($std, TagHelper::_convert_options($opt)));
  }

  /**
   * Inserts an <input> tag of type image.
   *
   * Example:
   *  <?= FormHelper::submit_image_tag('back.gif', array('name' => 'Back')) ?>
   *
   * @param string  the path to the image as specified by
   *                AssetHelper::image_path
   * @param array   <description>
   *
   * @return string  the <input> tag
   */
  function submit_image_tag($source, $opt = array()) {
    $std = array('type' => 'image',
                 'name' => 'commit',
                 'src' => AssetHelper::image_path($source));
    return tag('input', array_merge($std, TagHelper::_convert_options($opt)));
  }
}
