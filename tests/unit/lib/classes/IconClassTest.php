<?php
/*
 * Copyright (C) 2015 <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once dirname(__FILE__) . '/../../bootstrap.php';
require_once 'lib/classes/Icon.class.php';
require_once 'lib/classes/Assets.class.php';

class IconClassTest extends PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $this->memo_assets_url = Assets::url();
        Assets::set_assets_url('');
    }

    function tearDown()
    {
        Assets::set_assets_url($this->memo_assets_url);
    }

    function testAssetsImgSVG()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/blue/vote.svg" alt="vote" class="icon-role-clickable icon-shape-vote">',
            Assets::img('icons/blue/vote.svg')
        );
    }

    function testAssetsImgSVGWithAddition()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/blue/add/vote.svg" alt="vote+add" class="icon-role-clickable icon-shape-vote+add">',
            Assets::img('icons/blue/add/vote.svg')
        );
    }

    function testAssetsImgWithDifferentSize()
    {
        $this->assertEquals(
            '<img width="32" height="32" src="images/icons/blue/vote.svg" alt="vote" class="icon-role-clickable icon-shape-vote">',
            Assets::img('icons/blue/vote.svg', array('size' => 32))
        );
    }

    function testAssetsImgPNG()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/grey/info-circle.svg" alt="info-circle" class="icon-role-inactive icon-shape-info-circle">',
            Assets::img('icons/16/grey/info-circle.png')
        );
    }

    function testAssetsImgPNGWithAddition()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/grey/add/info-circle.svg" alt="info-circle+add" class="icon-role-inactive icon-shape-info-circle+add">',
            Assets::img('icons/16/grey/add/info-circle.png')
        );
    }

    function testAssetsImgWithoutExtension()
    {
        $this->assertEquals(
            '<img width="20" height="20" src="images/icons/black/staple.svg" alt="staple" class="icon-role-info icon-shape-staple">',
            Assets::img('icons/20/black/staple')
        );
    }

    function testAssetsImgWithTitleAttribute()
    {
        $this->assertEquals(
            '<img title="Mit Anhang" width="20" height="20" src="images/icons/black/staple.svg" alt="Mit Anhang" class="icon-role-info icon-shape-staple">',
            Assets::img('icons/20/black/staple', array("title" => _("Mit Anhang")))
        );
    }

    function testAssetsImgWithHspaceAttribute()
    {
        $this->assertEquals(
            '<img hspace="3" width="16" height="16" src="images/icons/blue/arr_2left.svg" alt="arr_2left" class="icon-role-clickable icon-shape-arr_2left">',
            Assets::img('icons/16/blue/arr_2left.png', array('hspace' => 3))
        );
    }

    function testAssetsImgWithClassAttribute()
    {
        $this->assertEquals(
            '<img class="text-bottom icon-role-inactive icon-shape-staple" width="20" height="20" src="images/icons/grey/staple.svg" alt="staple">',
            Assets::img('icons/20/grey/staple', array('class' => 'text-bottom'))
        );
    }

    function testAssetsImgWithClassAndTitleAttribute()
    {
        $this->assertEquals(
            '<img title="Datei hochladen" class="text-bottom icon-role-clickable icon-shape-upload" width="20" height="20" src="images/icons/blue/upload.svg" alt="Datei hochladen">',
            Assets::img('icons/20/blue/upload', array('title' => _("Datei hochladen"), 'class' => "text-bottom"))
        );
    }

    function testAssetsInput()
    {
        $this->assertEquals(
            '<input type="image" title="Datei hochladen" class="text-bottom icon-role-clickable icon-shape-upload" width="20" height="20" src="images/icons/blue/upload.svg" alt="Datei hochladen">',
            Assets::input('icons/20/blue/upload', array('title' => _("Datei hochladen"), 'class' => "text-bottom"))
        );
    }

    function testIconCreateAsImg()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/blue/vote.svg" alt="vote" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable')->asImg()
        );
    }

    function testIconCreateAsImgWithAddition()
    {
        $this->assertEquals(
            '<img width="16" height="16" src="images/icons/blue/add/vote.svg" alt="vote+add" class="icon-role-clickable icon-shape-vote+add">',
            Icon::create('vote+add', 'clickable')->asImg()
        );
    }

    function testIconCreateAsImgWithSize()
    {
        $this->assertEquals(
            '<img width="20" height="20" src="images/icons/blue/add/vote.svg" alt="vote+add" class="icon-role-clickable icon-shape-vote+add">',
            Icon::create('vote+add', 'clickable')->asImg(20)
        );
    }

    function testIconCreateAsImgWithTitle()
    {
        $this->assertEquals(
            '<img title="Mit Anhang" width="20" height="20" src="images/icons/blue/vote.svg" alt="Mit Anhang" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable', ['title' => _("Mit Anhang")])->asImg(20)
        );
    }

    function testIconCreateAsImgWithHspace()
    {
        $this->assertEquals(
            '<img hspace="3" width="16" height="16" src="images/icons/blue/arr_2left.svg" alt="arr_2left" class="icon-role-clickable icon-shape-arr_2left">',
            Icon::create('arr_2left', 'clickable')->asImg(['hspace' => 3])
        );
    }

    function testIconCreateAsImgWithClass()
    {
        $this->assertEquals(
            '<img class="text-bottom icon-role-info icon-shape-staple" width="20" height="20" src="images/icons/black/staple.svg" alt="staple">',
            Icon::create('staple', 'info')->asImg(20, ['class' => 'text-bottom'])
        );
    }

    function testIconCreateAsImgWithClassAndTitle()
    {
        $this->assertEquals(
            '<img title="Datei hochladen" class="text-bottom icon-role-new icon-shape-upload" width="20" height="20" src="images/icons/red/upload.svg" alt="Datei hochladen">',
            Icon::create('upload', 'new', ['title' => _("Datei hochladen")])
                ->asImg(20, ['class' => 'text-bottom'])
        );
    }

    function testIconCreateAsInput()
    {
        $this->assertEquals(
            '<input type="image" class="text-bottom icon-role-clickable icon-shape-upload" width="20" height="20" src="images/icons/blue/upload.svg" alt="upload">',
            Icon::create('upload', 'clickable')->asInput(20, ['class' => 'text-bottom'])
        );
    }

    function testIconIsImmutable()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithRole('clickable');

        $this->assertNotSame($icon, $copy);
    }

    function testIconCopyWithRole()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithRole('info');

        $this->assertEquals($icon->getShape(),      $copy->getShape());
        $this->assertNotEquals($icon->getRole(),    $copy->getRole());
        $this->assertEquals($icon->getAttributes(), $copy->getAttributes());
    }

    function testIconCopyWithShape()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithShape('staple');

        $this->assertNotEquals($icon->getShape(),   $copy->getShape());
        $this->assertEquals($icon->getRole(),       $copy->getRole());
        $this->assertEquals($icon->getAttributes(), $copy->getAttributes());
    }

    function testIconCopyWithAttributes()
    {
        $icon = Icon::create('upload', 'clickable', ['title' => _('a title')]);
        $copy = $icon->copyWithAttributes(['title' => _('another title')]);

        $this->assertEquals($icon->getShape(),         $copy->getShape());
        $this->assertEquals($icon->getRole(),          $copy->getRole());
        $this->assertNotEquals($icon->getAttributes(), $copy->getAttributes());
    }

    function testStaticIcon()
    {
        $icon = Icon::create('https://i.imgur.com/kpTtTh.gif');
        $this->assertEquals($icon->asImagePath(), 'https://i.imgur.com/kpTtTh.gif');
    }

    function testIconCreateAsCSSWithSize()
    {
        $this->assertEquals(
            'background-image:url(images/icons/17/blue/add/vote.png);background-image:none,url(images/icons/blue/add/vote.svg);background-size:17px 17px;',
            Icon::create('vote+add', 'clickable')->asCSS(17)
        );
    }

    function testIconCreateAsImagePath()
    {
        $this->assertEquals(
            'images/icons/blue/add/vote.svg',
            Icon::create('vote+add', 'clickable')->asImagePath()
        );
    }

    function testIconCreateAsImgWithoutSize()
    {
        $this->assertEquals(
            '<img src="images/icons/blue/vote.svg" alt="vote" class="icon-role-clickable icon-shape-vote">',
            Icon::create('vote', 'clickable')->asImg(false)
        );
    }

    function testIconCreateAsInputWithoutSize()
    {
        $this->assertEquals(
            '<input type="image" src="images/icons/blue/upload.svg" alt="upload" class="icon-role-clickable icon-shape-upload">',
            Icon::create('upload', 'clickable')->asInput(false)
        );
    }
}
