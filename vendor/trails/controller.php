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
 * @version   $Id: controller.php 4192 2006-10-24 10:57:01Z mlunzena $
 */

class Trails_Controller {

  /**
   * @ignore
   */
  var
    $controller_name,
    $response,
    $assigns,
    $performed_render,
    $performed_redirect,
    $request;

  /**
   * <VariableDescription>
   */
  var
    $flash;

  /**
   * Constructor.
   *
   * @param string the controllers path name
   *
   * @return void
   */
  function Trails_Controller($controller_name) {
    $this->assigns         = array();
    $this->controller_name = $controller_name;
    $this->flash           =& Trails_Flash::flash();
    $this->request         =& Trails_Request::instance();
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
  function before_filter($action, &$args) {
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
  function after_filter($action, &$args) {
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

    $action_name = $action . '_action';

    # is action callable?
    if (!method_exists($this, $action_name))
      # TODO
      Trails_Dispatcher::method_missing($this);

    # mute controller - begin
    set_error_handler(array('Trails_ErrorHandler', 'error_handler'));
    ob_start();

    # call before filter
    $before_filter_result = $this->before_filter($action, $args);

    # send action to controller
    if ($before_filter_result !== FALSE && !$this->has_performed()) {
      call_user_func_array(array(&$this, $action_name), $args);

      # call after filter
      $this->after_filter($action, $args);

      if (!$this->has_performed())
        $this->render_action($action);
    }

    # mute controller - end
    ob_end_clean();
    restore_error_handler();
    # TODO
    if (sizeof($errors = Trails_ErrorHandler::errors()))
      var_dump(join("\n", $errors));

    return $this->response;
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

    # get host
    $host = $_SERVER['HTTP_HOST'];

    # get uri
    $uri = UrlHelper::url_for($to);

    $url = 'http://'.$host.$uri;
    #die($url);

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
    $this->render_template($this->controller_name.'/'.$action,
                           $this->controller_name);
  }

  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  function render_template($template, $layout = NULL) {

    # open template
    $template = Trails_Template::create_template($template);

    # set attributes
    $attributes =& $this->get_assigned_variables();
    $template->set_attributes($attributes);

    # set layout
    if (isset($layout))
      $template->set_layout($layout);

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

    return $assigns;
  }
}
