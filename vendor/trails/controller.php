<?php

/*
 * controller.php - <short-description>
 *
 * Copyright (C) 2006 - Marcus Lunzenauer <mlunzena@uos.de>
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
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: controller.php 6021 2007-06-28 08:52:03Z mlunzena $
 */

class Trails_Controller {


  /**
   * @ignore
   */
  var
    $dispatcher,
    $controller_name,

    $response,
    $assigns,

    $performed_render,
    $performed_redirect,

    $template_factory,
    $layout;


  /**
   * Constructor.
   *
   * @param string the controllers path name
   *
   * @return void
   */
  function Trails_Controller(&$dispatcher, $controller_name) {

    $this->dispatcher =& $dispatcher;
    $this->controller_name = $controller_name;

    $this->response = ' ';
    $this->assigns = array();

    $this->performed_render = FALSE;
    $this->performed_redirect = FALSE;

    $this->template_factory =&
      new Flexi_TemplateFactory($dispatcher->trails_root . DIRECTORY_SEPARATOR .
                               'views' . DIRECTORY_SEPARATOR);
    $this->set_layout(NULL);
  }


  /**
   * Callback function being called before an action is executed. If this
   * function returns TRUE, the action will actually be called, otherwise
   * an error will be generated and processing will be aborted.
   *
   * @param string Name of the action to perform.
   * @param array  An array of arguments to the action.
   *
   * @return bool <description>
   */
  function before_filter(&$action, &$args) {
    return TRUE;
  }


  /**
   * Callback function being called after an action is executed.
   *
   * @param string Name of the action to perform.
   * @param array  An array of arguments to the action.
   *
   * @return void
   */
  function after_filter($action, $args) {
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   * @param array  <description>
   *
   * @return string <description>
   */
  function perform($action, $args) {

    # call before filter
    $before_filter_result = $this->before_filter($action, $args);

    # send action to controller
    if ($before_filter_result !== FALSE && !$this->has_performed()) {

      $mapped_action = $this->map_action($action);

      # is action callable?
      if (!method_exists($this, $mapped_action))
        # TODO (mlunzena) should not the user provide a method missing action?
        Trails_Dispatcher::method_missing($this, $action, $args);


      call_user_func_array(array(&$this, $mapped_action), $args);

      if (!$this->has_performed())
        $this->render_action($action);

      # call after filter
      $this->after_filter($action, $args);
    }

    return $this->response;
  }


  function map_action($action) {
    return $action . '_action';
  }


  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  function has_performed() {
    return $this->performed_render || $this->performed_redirect;
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function redirect($to) {

    if ($this->has_performed())
      trigger_error('Double_Render_Error');

    $this->performed_redirect = TRUE;

    # get uri
    $url = $this->url_for($to);

    # redirect
    header('Location: '.$url);
    printf('<html><head><meta http-equiv="refresh" content="0;url=%s"/>'.
           '</head></html>', $url);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function render_text($text = ' ') {

    if ($this->has_performed())
      trigger_error('Double_Render_Error');

    $this->performed_render = TRUE;

    $this->response = $text;
  }


  /**
   * <MethodDescription>
   *
   * @return void
   */
  function render_nothing() {
    $this->render_text(' ');
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function render_action($action) {
    $this->render_template($this->controller_name.DIRECTORY_SEPARATOR.$action,
                           $this->layout);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function render_template($template_name, $layout = NULL) {

    # open template
    $template =& $this->template_factory->open($template_name);
    if (is_null($template)) {
      trigger_error(sprintf('No such template: "%s"', $template_name),
                    E_USER_ERROR);
      return;
    }

    # template requires setup ?
    if ($this->does_template_require_setup($template)) {
      switch (get_class($template)) {
        case 'Flexi_JsTemplate':
          header('Content-Type: text/javascript');
          break;
      }
    }

    # set attributes
    $attributes =& $this->get_assigned_variables();
    $template->set_attributes($attributes);

    # set layout
    if (isset($layout))
      $template->set_layout('layouts' . DIRECTORY_SEPARATOR . $layout);

    $this->render_text($template->render());
  }


  /**
   * <MethodDescription>
   *
   * @return void
   */
  function &get_assigned_variables() {

    $assigns = array();
    $protected = get_class_vars(get_class($this));

    foreach (get_object_vars($this) as $var => $value)
      if (!array_key_exists($var, $protected))
        $assigns[$var] =& $this->$var;

    $assigns['controller'] = $this;

    return $assigns;
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function set_layout($layout) {
    $this->layout = $layout;
  }


  function url_for($to) {

    $base = $this->dispatcher->trails_uri;

    # absolute URL?
    return preg_match('#^[a-z]+://#', $to)
           ? $to
           : $base . '/' . $to;
  }
}
