<?php

/*
 * text_helper.php - Help with texts.
 *
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * TextHelper.
 *
 *
 * @package    flexi
 * @subpackage helper
 *
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright (c) Authors
 * @version   $Id$
 */

class TextHelper {

  /**
   * @ignore
   */
  private static $cycles = array();

  /**
   * Returns a camelized string from a lower case and underscored string by
   * replacing slash with underscore and upper-casing each letter preceded
   * by an underscore.
   *
   * @param string String to camelize.
   *
   * @return string Camelized string.
   */
  static function camelize($word) {
    return str_replace(' ', '',
                       ucwords(str_replace(array('_', '/'),
                                           array(' ', ' '), $word)));
  }


  # Creates a Cycle object whose _to_s_ method cycles through elements of an
  # array every time it is called. This can be used for example, to alternate
  # classes for table rows:
  #
  #   <% @items.each do |item| %>
  #     <tr class="<%= cycle("even", "odd") -%>">
  #       <td>item</td>
  #     </tr>
  #   <% end %>
  #
  # You can use named cycles to allow nesting in loops.  Passing a Hash as
  # the last parameter with a <tt>:name</tt> key will create a named cycle.
  # You can manually reset a cycle by calling reset_cycle and passing the
  # name of the cycle.
  #
  #   <% @items.each do |item| %>
  #     <tr class="<%= cycle("even", "odd", :name => "row_class")
  #       <td>
  #         <% item.values.each do |value| %>
  #           <span style="color:<%= cycle("red", "green", "blue", :name => "colors") -%>">
  #             value
  #           </span>
  #         <% end %>
  #         <% reset_cycle("colors") %>
  #       </td>
  #    </tr>
  #  <% end %>
  static function cycle($first_value) {

    $values = func_get_args();


    if (is_array($values[func_num_args() - 1])) {
      $params = array_pop($values);
      $name = $params['name'];
    }
    else {
      $name = 'default';
    }

    $cycle = self::get_cycle($name);
    if (is_null($cycle) || $cycle->values !== $values)
      $cycle = self::set_cycle($name, new TextHelperCycle($values));

    return (string)$cycle;
  }

  # Resets a cycle so that it starts from the first element the next time
  # it is called. Pass in +name+ to reset a named cycle.
  function reset_cycle($name = 'default') {
    $cycle = self::get_cycle($name);
    if (isset($cycle))
      $cycle->reset();
  }


  /**
   * @ignore
   */
  private static function get_cycle($name) {
    return isset(self::$cycles[$name]) ? self::$cycles[$name] : NULL;
  }

  /**
   * @ignore
   */
  private static function set_cycle($name, $cycle) {
    return self::$cycles[$name] = $cycle;
  }
}


class TextHelperCycle {


  public $values;


  function __construct($values) {
    $this->values = (array) $values;
  }


  function reset() {
    reset($this->values);
  }


  function __toString() {
    $result = current($this->values);
    if (next($this->values) === FALSE)
      $this->reset();
    return $result;
  }
}

