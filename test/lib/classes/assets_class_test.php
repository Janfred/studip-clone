<?php

/*
 * Copyright (C) 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


require_once dirname(__FILE__) . '/../../../lib/classes/Assets.class.php';

/**
 * Testcase for Assets class.
 *
 * @package    studip
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 * @version   $Id$
 */

class AssetsTestCase extends UnitTestCase {


  private $old_assets_url;


  function setUp() {
    $this->old_assets_url = @$GLOBALS['ASSETS_URL'];
    $GLOBALS['ASSETS_URL'] = 'http://www.example.com/public/';
    Assets::clear_cache();
  }


  function tearDown() {
    $GLOBALS['ASSETS_URL'] = $this->old_assets_url;
  }


  function test_class_should_exist() {
    $this->assertTrue(class_exists('Assets'));
  }


  function test_url_should_return_ASSETS_URL() {
    $this->assertEqual(Assets::url(), $GLOBALS['ASSETS_URL']);
  }


  function test_url_should_concats_argument() {
    $this->assertEqual(Assets::url('prototype.js'),
                       $GLOBALS['ASSETS_URL'] . 'prototype.js');
  }
}


class DynamicAssetsTestCase extends UnitTestCase {


  private $old_assets_url;


  function setUp() {
    $this->old_assets_url = @$GLOBALS['ASSETS_URL'];
    $GLOBALS['ASSETS_URL'] = 'http://www%d.example.com/public/';
    Assets::clear_cache();
  }


  function tearDown() {
    $GLOBALS['ASSETS_URL'] = $this->old_assets_url;
  }


  function test_url_without_arg_should_cycle() {
    $this->assertEqual(Assets::url(), sprintf($GLOBALS['ASSETS_URL'], 0));
    $this->assertEqual(Assets::url(), sprintf($GLOBALS['ASSETS_URL'], 1));
    $this->assertEqual(Assets::url(), sprintf($GLOBALS['ASSETS_URL'], 2));
    $this->assertEqual(Assets::url(), sprintf($GLOBALS['ASSETS_URL'], 3));
    $this->assertEqual(Assets::url(), sprintf($GLOBALS['ASSETS_URL'], 0));
  }


  function test_url_with_paramater_should_not_cycle() {
    $url = Assets::url('prototype.js');
    $url2 = Assets::url('prototype.js');
    $this->assertWantedPattern('@http://www[0-3].example.com/public/@', $url);
    $this->assertEqual($url, $url2);
  }
}


class AssetsHelpersTestCase extends UnitTestCase {


  private $old_assets_url;


  function setUp() {
    $this->old_assets_url = @$GLOBALS['ASSETS_URL'];
    $GLOBALS['ASSETS_URL'] = 'http://www.example.com/public/';
    Assets::clear_cache();
  }


  function tearDown() {
    $GLOBALS['ASSETS_URL'] = $this->old_assets_url;
  }


  function test_image_path_should_add_directory_before_image() {
    $expected = 'http://www.example.com/public/images/logo.png';
    $this->assertEqual(Assets::image_path('logo.png'), $expected);
  }


  function test_image_path_should_add_gif_if_no_extension_were_given() {
    $expected = 'http://www.example.com/public/images/logo.gif';
    $this->assertEqual(Assets::image_path('logo'), $expected);
  }


  function test_image_path_should_not_touch_absolute_paths() {
    $url = Assets::image_path('/some/logo.png');
    $this->assertEqual($GLOBALS['ASSETS_URL'].'some/logo.png', $url);
  }


  function test_javascript_path_should_add_directory_before_script() {
    $expected = 'http://www.example.com/public/javascripts/prototype.js';
    $this->assertEqual(Assets::javascript_path('prototype.js'), $expected);
  }


  function test_javascript_path_should_add_js_if_no_extension_were_given() {
    $expected = 'http://www.example.com/public/javascripts/prototype.js';
    $this->assertEqual(Assets::javascript_path('prototype'), $expected);
  }


  function test_javascript_path_should_not_touch_absolute_paths() {
    $url = Assets::javascript_path('/some/script.js');
    $this->assertEqual($GLOBALS['ASSETS_URL'].'some/script.js', $url);
  }


  function test_stylesheet_path_should_add_directory_before_script() {
    $expected = 'http://www.example.com/public/stylesheets/print.css';
    $this->assertEqual(Assets::stylesheet_path('print.css'), $expected);
  }


  function test_stylesheet_path_should_add_css_if_no_extension_were_given() {
    $expected = 'http://www.example.com/public/stylesheets/print.css';
    $this->assertEqual(Assets::stylesheet_path('print'), $expected);
  }


  function test_stylesheet_path_should_not_touch_absolute_paths() {
    $url = Assets::stylesheet_path('/some/style.css');
    $this->assertEqual($GLOBALS['ASSETS_URL'].'some/style.css', $url);
  }


  function test_img_should_return_img_tag_with_alt_attribute() {
    $expected = '<img alt="Logo" src="http://www.example.com/public/images/logo.png" />';
    $this->assertEqual(Assets::img('logo.png'), $expected);
  }


  function test_img_should_respect_alt_attribute() {
    $expected = '<img alt="logo" src="http://www.example.com/public/images/logo.png" />';
    $this->assertEqual(Assets::img('logo.png', array('alt' => 'logo')),
                       $expected);
  }


  function test_img_should_respect_size_attribute() {
    $expected = '<img alt="Logo" height="20" src="http://www.example.com/public/images/logo.png" width="10" />';
    $this->assertEqual(Assets::img('logo.png', array('size' => '10@20')),
                       $expected);
  }


  function test_img_should_respect_other_attributes() {
    $expected = '<img a="1" alt="Logo" b="2" src="http://www.example.com/public/images/logo.png" />';
    $this->assertEqual(Assets::img('logo.png', array('a' => '1', 'b' => 2)),
                       $expected);
  }


  function test_script_should_return_script_tag() {
    $expected = '<script type="text/javascript" src="http://www.example.com/public/javascripts/prototype.js"></script>' . "\n";
    $this->assertEqual(Assets::script('prototype'), $expected);
  }


  function test_script_should_return_multiple_script_tags() {
    $expected  = '<script type="text/javascript" src="http://www.example.com/public/javascripts/prototype.js"></script>' . "\n";
    $expected .= '<script type="text/javascript" src="http://www.example.com/public/javascripts/scriptaculous.js"></script>' . "\n";
    $this->assertEqual(Assets::script('prototype', 'scriptaculous'), $expected);
  }


  function test_stylesheet_should_return_link_tag() {
    $expected = '<link href="http://www.example.com/public/stylesheets/blue.css" media="screen" rel="stylesheet" type="text/css" />' . "\n";
    $this->assertEqual(Assets::stylesheet('blue'), $expected);
  }


  function test_stylesheet_should_return_multiple_link_tags() {
    $expected  = '<link href="http://www.example.com/public/stylesheets/blue.css" media="screen" rel="stylesheet" type="text/css" />' . "\n";
    $expected .= '<link href="http://www.example.com/public/stylesheets/green.css" media="screen" rel="stylesheet" type="text/css" />' . "\n";
    $expected .= '<link href="http://www.example.com/public/stylesheets/red.css" media="screen" rel="stylesheet" type="text/css" />' . "\n";
    $this->assertEqual(Assets::stylesheet('blue', 'green', 'red'), $expected);
  }


  function test_stylesheet_should_respect_options() {
    $expected  = '<link href="http://www.example.com/public/stylesheets/blue.css" media="all" rel="stylesheet" type="text/css" />' . "\n";
    $expected .= '<link href="http://www.example.com/public/stylesheets/green.css" media="all" rel="stylesheet" type="text/css" />' . "\n";
    $expected .= '<link href="http://www.example.com/public/stylesheets/red.css" media="all" rel="stylesheet" type="text/css" />' . "\n";
    $this->assertEqual(Assets::stylesheet('blue', 'green', 'red', array('media' => 'all')), $expected);
  }
}
