<?php
/*
 * Copyright (c) 2011 mlunzena
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

namespace Test;

require_once dirname(__FILE__) . '/../../../bootstrap.php';
require_once 'lib/classes/squeeze/squeeze.php';


use \Studip\Squeeze\Configuration;
use \Studip\Squeeze\Compressor;

class SqueezeCompressorTest extends \PHPUnit_Framework_TestCase
{
    function skipTestWithoutCompressors()
    {
        if (in_array($this->getName(), ['testCompress', 'testCallCompressor'])) {
            $compressor = new Compressor(new Configuration());
            if (!$compressor->hasJsCompressor() && !$compressor->hasCssCompressor()) {
                $this->markTestSkipped('TODO Skip');
            }
        }
    }

    function setUp()
    {
        $this->skipTestWithoutCompressors();
        $GLOBALS['CACHING_ENABLE'] = false;

        $this->STUDIP_BASE_PATH = $GLOBALS['STUDIP_BASE_PATH'];
        $GLOBALS['STUDIP_BASE_PATH'] = realpath(dirname(__FILE__) . '/../../../../../');
    }

    function tearDown()
    {
        $GLOBALS['STUDIP_BASE_PATH'] = $this->STUDIP_BASE_PATH;
    }

    function testCallCompressor()
    {
        $compressor = new Compressor(new Configuration());
        $js = "function A() { this.stuff = 42; }";
        $expected = "function A(){this.stuff=42}\n";
        $this->assertEquals($expected, $compressor->callJsCompressor($js));
    }

    function testCallCompressorWithSyntaxError()
    {
        $this->setExpectedException('\Studip\Squeeze\Exception');
        $compressor = new Compressor(new Configuration());
        $js = "function A()";
        $compressor->callJsCompressor($js);
    }

}
