<?php

# Copyright (c)  2007 - Marcus Lunzenauer <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.


define('TRAILS_VERSION', '0.4.0');


/**
 * TODO
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 6361 2007-10-19 12:29:29Z mlunzena $
 */

class Trails_Dispatcher {

  # TODO (mlunzena) Konfiguration muss anders geschehen
  public
    $trails_root,
    $trails_uri,
    $default_controller,
    $default_action;


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  function __construct($trails_root,
                              $trails_uri,
                              $default_controller,
                              $default_action) {

    $this->trails_root        = $trails_root;
    $this->trails_uri         = $trails_uri;
    $this->default_controller = $default_controller;
    $this->default_action     = $default_action;
  }


  /**
   * <MethodDescription>
   *
   * @param string The requested URI.
   *
   * @return void
   */
  function dispatch($request_uri) {

    $old_handler = set_error_handler(array('Trails_Exception',
                                           'errorHandlerCallback'),
                                     E_ALL);

    ob_start();
    $level = ob_get_level();

    try {

      $clean_uri = $this->clean_uri((string) $request_uri);
      $response = $this->choose_controller($clean_uri)->perform();

    } catch (Trails_Exception $e) {

      ob_clean();

      $response = array('status'  => array($e->getCode(), $e->getMessage()),
                        'headers' => $e->headers);

      $response['body'] =
        sprintf('<html><head><title>Trails Error</title></head>'.
                '<body><h1>%s</h1><pre>%s</pre></body></html>',
                htmlentities($e),
                htmlentities($e->getTraceAsString()));
    }

    # output of response
    # TODO (mlunzena) response should be an object
    # TODO (mlunzena) should not we use HTTP/1.1 ?
    if (sizeof($response['status'])) {
      header(sprintf('HTTP/1.0 %d %s',
                     $response['status'][0],
                     $response['status'][1]),
             TRUE, $response['status'][0]);
    }

    foreach ($response['headers'] as $k => $v) {
      header("$k: $v");
    }

    echo $response['body'];


    while (ob_get_level() >= $level) {
      ob_end_flush();
    }

    if (isset($old_handler)) {
      set_error_handler($old_handler);
    }
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  private function clean_uri($uri) {

    # remove "query" part
    if (FALSE !== ($pos = strpos($uri, '?'))) {
      $uri = substr($uri, 0, $pos);
    }

    return ltrim($uri, '/');
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  protected function choose_controller($uri) {

    # default controller
    if ('' === $uri) {
      $controller_path = $this->default_controller;
      $unconsumed = $uri;
    }

    else {
      list($controller_path, $unconsumed) = $this->parse($uri);
    }

    return $this->load_controller($controller_path, $unconsumed);
  }


  /**
   * Parses given URL and returns an array of controllers, action and parameters
   * taken from that URL.
   *
   * @param string  <description>
   *
   * @return type   <description>
   */
  protected function parse($uri) {

    $accumulated = array();
    foreach (explode('/', $uri) as $part) {

      # sanity check
      if (!preg_match('/^[a-z0-9\-_]+$/', $part)) {
        break;
      }

      $accumulated[] = $part;
      $exploded = join('/', $accumulated);

      if (is_readable($this->get_path($exploded))) {

        # TODO (mlunzena) check this!!!
        $unconsumed = substr($uri, strlen($exploded) + 1);
        if (FALSE === $unconsumed) {
          $unconsumed = '';
        }

        return array($exploded, $unconsumed);
      }
    }

    throw new Trails_Exception(404, 'Not found: ' . $uri);
  }


  /**
   * <MethodDescription>
   *
   * @param string   <description>
   *
   * @return string  <description>
   */
  protected function get_path($controller_path) {
    return
      sprintf('%s/controllers/%s.php', $this->trails_root, $controller_path);
  }


  /**
   * <MethodDescription>
   *
   * @param string   <description>
   * @param string   <description>
   *
   * @return object  <description>
   */
  protected function load_controller($controller_path, $unconsumed) {

    require_once $this->get_path($controller_path);
    $class = Trails_Inflector::camelize($controller_path) . 'Controller';
    if (!class_exists($class)) {
      throw new Trails_Exception(501, 'Controller missing: ' . $class);
    }

    return new $class($this, $unconsumed);
  }
}


/**
 * TODO
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 6361 2007-10-19 12:29:29Z mlunzena $
 */

class Trails_Controller {


  /**
   * @ignore
   */
  protected
    $dispatcher,
    $unconsumed,

    $response = array('body' => ' ', 'status' => array(), 'headers' => array()),

    $performed_render = FALSE,
    $performed_redirect = FALSE,

    $template_factory,
    $layout = NULL;


  /**
   * Constructor.
   *
   * @param  mixed  the dispatcher who creates this instance
   *
   * @return void
   */
  function __construct($dispatcher, $unconsumed) {

    $this->dispatcher = $dispatcher;
    $this->unconsumed = $unconsumed;

    $this->template_factory =
      new Flexi_TemplateFactory($dispatcher->trails_root . '/views/');
  }


  /**
   * <MethodDescription>
   *
   * @param array  <description>
   *
   * @return string <description>
   */
  function perform() {

    # TODO (mlunzena) das muss geändert werden
    if ('' === $this->unconsumed) {
      $args = array();
      $action = $this->dispatcher->default_action;
    }
    else {
      $args = explode('/', $this->unconsumed);
      $action = array_shift($args);
    }

    # call before filter
    $before_filter_result = $this->before_filter($action, $args);

    # send action to controller
    if (!(FALSE === $before_filter_result || $this->has_performed())) {

      $mapped_action = $this->map_action($action);

      # is action callable?
      if (!method_exists($this, $mapped_action)) {
        $this->does_not_understand($action, $args);
      }
      else {
        call_user_func_array(array(&$this, $mapped_action), $args);
      }

      if (!$this->has_performed()) {
        $this->render_action($action);
      }

      # call after filter
      $this->after_filter($action, $args);
    }

    return $this->response;
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  protected function map_action($action) {
    return $action . '_action';
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
  protected function before_filter(&$action, &$args) {
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
  protected function after_filter($action, $args) {
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   * @param type <description>
   *
   * @return void
   */
  protected function does_not_understand($action, $args) {
    throw new Trails_Exception(404, 'Action missing: ' . $action);
  }


  /**
   * <MethodDescription>
   *
   * @return bool <description>
   */
  protected function has_performed() {
    return $this->performed_render || $this->performed_redirect;
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  protected function redirect($to) {

    if ($this->has_performed()) {
      throw new Trails_Exception(500, 'Double Render Error');
    }

    $this->performed_redirect = TRUE;

    # get uri
    $url = $this->url_for($to);

    # redirect
    $this->response['headers']['Location'] = $url;
    $this->response['body'] =
      sprintf('<html><head><meta http-equiv="refresh" content="0;url=%s"/>'.
              '</head></html>', $url);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  protected function render_text($text = ' ') {

    if ($this->has_performed()) {
      throw new Trails_Exception(500, 'Double Render Error');
    }

    $this->performed_render = TRUE;

    $this->response['body'] = $text;
  }


  /**
   * <MethodDescription>
   *
   * @return void
   */
  protected function render_nothing() {
    $this->render_text(' ');
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  protected function render_action($action) {
    $class = get_class($this);
    $controller_name =
      Trails_Inflector::underscore(substr($class, 0, strlen($class) - 10));

    $this->render_template($controller_name.'/'.$action, $this->layout);
  }


  /**
   * <MethodDescription>
   *
   * @param string <description>
   *
   * @return void
   */
  protected function render_template($template_name, $layout = NULL) {

    # open template
    $template = $this->template_factory->open($template_name);
    if (is_null($template)) {
      throw new Trails_Exception(500, sprintf('No such template: "%s"',
                                              $template_name));
    }

    # template requires setup ?
    switch (get_class($template)) {
      case 'Flexi_JsTemplate':
        $this->set_content_type('text/javascript');
        break;
    }

    $template->set_attributes($this->get_assigned_variables());

    if (isset($layout)) {
      $template->set_layout($layout);
    }

    $this->render_text($template->render());
  }


  /**
   * <MethodDescription>
   *
   * @return void
   */
  protected function get_assigned_variables() {

    $assigns = array();
    $protected = get_class_vars(get_class($this));

    foreach (get_object_vars($this) as $var => $value) {
      if (!array_key_exists($var, $protected)) {
        $assigns[$var] =& $this->$var;
      }
    }

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
  protected function set_layout($layout) {
    $this->layout = $layout;
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  function url_for($to) {

    $base = $this->dispatcher->trails_uri;

    # absolute URL?
    return preg_match('#^[a-z]+://#', $to)
           ? $to
           : $base . '/' . $to;
  }


  function set_status($status, $reason_phrase = NULL) {
    $reason_phrase = isset($reason_phrase)
                     ? $reason_phrase
                     : $this->get_reason_phrase($status);
    $this->response['status'] = array($status => $reason_phrase);
  }


  /**
   * Returns the reason phrase of this response according to RFC2616.
   *
   * @param int      the response's status
   *
   * @return string  the reason phrase for this response's status
   */
  protected function get_reason_phrase($status) {
    $reason = array(
      100 => 'Continue', 'Switching Protocols',
      200 => 'OK', 'Created', 'Accepted', 'Non-Authoritative Information',
             'No Content', 'Reset Content', 'Partial Content',
      300 => 'Multiple Choices', 'Moved Permanently', 'Found', 'See Other',
             'Not Modified', 'Use Proxy', '(Unused)', 'Temporary Redirect',
      400 => 'Bad Request', 'Unauthorized', 'Payment Required','Forbidden',
             'Not Found', 'Method Not Allowed', 'Not Acceptable',
             'Proxy Authentication Required', 'Request Timeout', 'Conflict',
             'Gone', 'Length Required', 'Precondition Failed',
             'Request Entity Too Large', 'Request-URI Too Long',
             'Unsupported Media Type', 'Requested Range Not Satisfiable',
             'Expectation Failed',
      500 => 'Internal Server Error', 'Not Implemented', 'Bad Gateway',
             'Service Unavailable', 'Gateway Timeout',
             'HTTP Version Not Supported');

    return isset($reason[$status]) ? $reason[$status] : '';
  }


  /**
   * @param  string  the content type
   *
   * @return void
   */
  protected function set_content_type($type) {
    $this->response['headers']['Content-Type'] = $type;
  }
}


/**
 * TODO
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 6361 2007-10-19 12:29:29Z mlunzena $
 */

class Trails_Inflector {


  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore. TODO
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  static function camelize($word) {
    $parts = explode('/', $word);
    foreach ($parts as $key => $part) {
      $parts[$key] = str_replace(' ', '',
                                 ucwords(str_replace('_', ' ', $part)));
    }
    return join('_', $parts);
  }


  /**
   * <MethodDescription>
   *
   * @param type <description>
   *
   * @return type <description>
   */
  static function underscore($word) {
    $parts = explode('_', $word);
    foreach ($parts as $key => $part) {
      $parts[$key] = preg_replace('/(?<=\w)([A-Z])/', '_\\1', $part);
    }
    return strtolower(join('/', $parts));
  }
}


/**
 * The flash provides a way to pass temporary objects between actions.
 * Anything you place in the flash will be exposed to the very next action and
 * then cleared out. This is a great way of doing notices and alerts, such as
 * a create action that sets
 * <tt>$flash->set('notice', "Successfully created")</tt>
 * before redirecting to a display action that can then expose the flash to its
 * template.
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 6361 2007-10-19 12:29:29Z mlunzena $
 */

class Trails_Flash {


  /**
   * @ignore
   */
  private
    $flash, $used;


  /**
   * Constructor
   *
   * @return void
   */
  private function __construct($flash = array(), $used = array()) {
    $this->flash = $flash;
    $this->used  = $used;
  }


  /**
   * Class field replacement.
   *
   * @param object  the flash to set.
   *
   * @return object the stored flash.
   */
  static function &flash($set = FALSE) {
    static $flash;

    if ($set !== FALSE) {
      $flash = $set;
    }

    return $flash;
  }


  /**
   * Used internally by the <tt>keep</tt> and <tt>discard</tt> methods
   *     use()               # marks the entire flash as used
   *     use('msg')          # marks the "msg" entry as used
   *     use(null, false)    # marks the entire flash as unused
   *                         # (keeps it around for one more action)
   *     use('msg', false)   # marks the "msg" entry as unused
   *                         # (keeps it around for one more action)
   *
   * @param mixed  a key.
   * @param bool   used flag.
   *
   * @return void
   */
  private function _use($k = NULL, $v = TRUE) {
    if ($k) {
      $this->used[$k] = $v;
    }
    else {
      foreach ($this->used as $k => $value) {
        $this->_use($k, $v);
      }
    }
  }


  /**
   * Marks the entire flash or a single flash entry to be discarded by the end
   * of the current action.
   *
   *     $flash->discard()             # discards entire flash
   *                                   # (it'll still be available for the
   *                                   # current action)
   *     $flash->discard('warning')    # discard the "warning" entry
   *                                   # (it'll still be available for the
   *                                   # current action)
   *
   * @param mixed  a key.
   *
   * @return void
   */
  function discard($k = NULL) {
    $this->_use($k);
  }


  /**
   * Marks flash entries as used and expose the flash to the view.
   *
   * @return void
   */
  static function fire() {
    if (!isset($_SESSION['trails_flash'])) {
      $flash =& Trails_Flash::flash(new Trails_Flash());
      $_SESSION['trails_flash'] = array($flash->flash, $flash->used);
    }
    else {
      list($_flash, $_used) = $_SESSION['trails_flash'];
      $flash =& Trails_Flash::flash(new Trails_Flash($_flash, $_used));
    }

    $flash->discard();
  }


  /**
   * Returns the value to the specified key.
   *
   * @param mixed  a key.
   *
   * @return mixed the key's value.
   */
  function &get($k) {
    $return = NULL;
    if (isset($this->flash[$k])) {
      $return =& $this->flash[$k];
    }
    return $return;
  }


  /**
   * Keeps either the entire current flash or a specific flash entry available
   * for the next action:
   *
   *    $flash->keep()           # keeps the entire flash
   *    $flash->keep('notice')   # keeps only the "notice" entry, the rest of
   *                             # the flash is discarded
   *
   * @param mixed  a key.
   *
   * @return void
   */
  function keep($k = NULL) {
    $this->_use($k, FALSE);
  }


  /**
   * Sets a flash that will not be available to the next action, only to the
   * current.
   *
   *    $flash->now('message') = "Hello current action";
   *
   * This method enables you to use the flash as a central messaging system in
   * your app. When you need to pass an object to the next action, you use the
   * standard flash assign (<tt>set</tt>). When you need to pass an object to
   * the current action, you use <tt>now</tt>, and your object will vanish when
   * the current action is done.
   *
   * Entries set via <tt>now</tt> are accessed the same way as standard entries:
   * <tt>$flash->get('my-key')</tt>.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function now($k, $v) {
    $this->discard($k);
    $this->flash[$k] = $v;
  }


  /**
   * Sets a key's value.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function set($k, $v) {
    $this->keep($k);
    $this->flash[$k] = $v;
  }


  /**
   * Sets a key's value by reference.
   *
   * @param mixed  a key.
   * @param mixed  its value.
   *
   * @return void
   */
  function set_ref($k, &$v) {
    $this->keep($k);
    $this->flash[$k] =& $v;
  }


  /**
   * Deletes the flash entries that were not marked for keeping.
   *
   * @return void
   */
  function sweep(){

    # no flash, no sweep
    if (!isset($_SESSION['trails_flash'])) {
      return;
    }

    # get flash
    $flash =& Trails_Flash::flash();

    // actually sweep
    $keys = array_keys($flash->flash);
    foreach ($keys as $k) {
      if (!$flash->used[$k]) {
        $flash->_use($k);
      } else {
        unset($flash->flash[$k], $flash->used[$k]);
      }
    }

    // cleanup if someone meddled with flash or used
    $fkeys = array_keys($flash->flash);
    $ukeys = array_keys($flash->used);
    foreach (array_diff($fkeys, $ukeys) as $k => $v) {
      unset($flash->used[$k]);
    }

    // serialize it
    $_SESSION['trails_flash'] = array($flash->flash, $flash->used);
  }
}


/**
 * TODO
 *
 * @package   trails
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id: trails.php 6361 2007-10-19 12:29:29Z mlunzena $
 */

class Trails_Exception extends Exception {


  public $headers;


  function __construct($status, $reason, $headers = array()) {
    parent::__construct($reason, $status);
    $this->headers = $headers;
  }


  function __toString() {
    return "{$this->code} {$this->message}";
  }


  static function errorHandlerCallback($errno, $string, $file, $line, $context) {

    if (!($errno & error_reporting())) {
      return;
    }

    if ($errno == E_NOTICE || $errno == E_WARNING || $errno == E_STRICT) {
      return FALSE;
    }

    $e = new Trails_Exception(500, $string);
    $e->line = $line;
    $e->file = $file;
    throw $e;
  }
}

