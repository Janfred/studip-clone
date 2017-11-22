<?php
/*
 *
 * Copyright (c) 2011  <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

namespace Studip\Squeeze;


class Compressor
{
    const JS_COMPRESSOR = 'uglifyjs';
    const JS_OPTIONS    = '--compress --mangle --';

    const CSS_COMPRESSOR = 'cleancss';
    const CSS_OPTIONS    = '--skip-rebase -O 2';

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    public function compress($paths)
    {
        $js = $this->concatenateAssets($paths);
        if ($this->shouldCompress() && $this->hasJsCompressor()) {
            $js = $this->callJsCompressor($js);
        }

        return $js;
    }

    private function concatenateAssets($paths)
    {
        $files = array_map(array($this, "getFileAsUTF8"), $paths);
        return join("\n", $files);
    }

    private function getFileAsUTF8($path)
    {
        $content = file_get_contents(
            $this->configuration['assets_root'] . "/$path");

        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'WINDOWS-1252');
        }

        return $content;
    }

    public function shouldCompress()
    {
        return $this->configuration['compress'];
    }

    public function hasJsCompressor()
    {
        exec("which " . self::JS_COMPRESSOR, $result);
        return (bool)$result;
    }

    public function callJsCompressor($js)
    {
        $command = self::JS_COMPRESSOR . ' ' . self::JS_OPTIONS;
        return $this->procOpen($command, $js);
    }

    public function hasCssCompressor()
    {
        exec("which " . self::CSS_COMPRESSOR, $result);
        return (bool)$result;
    }

    public function callCssCompressor($css)
    {
        $command = self::CSS_COMPRESSOR . ' ' . self::CSS_OPTIONS;
        return $this->procOpen($command, $css);
    }

    private function procOpen($command, $stdin)
    {
        $cwd = $GLOBALS['TMP_PATH'];

        $err = tempnam($cwd, 'squeeze');
        $descriptorspec = array(
            array("pipe", "r"),
            array("pipe", "w"),
            array("file", $err, "a")
        );

        $process = proc_open($command, $descriptorspec, $pipes);

        if (is_resource($process)) {

            fwrite($pipes[0], $stdin);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $return_value = proc_close($process);
            $error_msg = file_get_contents($err);
            unlink($err);

            # an error happened
            if ($return_value) {
                throw new Exception("Compression Error: " . $error_msg);
            }

            return $output;
        }

        throw new Exception("Compression failed");
    }
}
