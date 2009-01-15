<?php
/*
 * PluginRepository.class.php - query plugin meta data
 *
 * Copyright (c) 2008  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * Class used to locate plugins available from a plugin repository.
 */
class PluginRepository
{
    /**
     * list and meta data of available plugins
     */
    private $plugins = array();

    /**
     * Initialize a new PluginRepository and read meta data from
     * the given URL (optional).
     */
    public function __construct ($url = NULL)
    {
        if (isset($url)) {
            $this->readMetadata($url);
        }
    }

    /**
     * Read plugin meta data from the given URL (using XML format).
     * The structure of the XML is:
     *
     * <plugins>
     *   <plugin name="DummyPlugin"
     *           version="2.0"
     *           url="http://plugins.example.com/DummyPlugin-2.0.zip"
     *           studipMinVersion="1.4"
     *           studipMaxVersion="1.9" />
     *   [...]
     * </plugins>
     */
    public function readMetadata ($url)
    {
        global $SOFTWARE_VERSION;

        if (($metadata = file_get_contents($url)) === false) {
            throw new Exception("Error reading URL: $url");
        }

        $xml = new SimpleXMLElement($metadata);

        if (isset($xml->plugin)) {
            foreach ($xml->plugin as $xml_plugin) {
                $min_version = $xml_plugin['studipMinVersion'];
                $max_version = $xml_plugin['studipMaxVersion'];

                if (isset($min_version) &&
                      version_compare($min_version, $SOFTWARE_VERSION) > 0 ||
                    isset($max_version) &&
                      version_compare($max_version, $SOFTWARE_VERSION) < 0) {
                    // plugin is not compatible, so skip it
                    continue;
                }

                $this->registerPlugin(utf8_decode($xml_plugin['name']),
                                      utf8_decode($xml_plugin['version']),
                                      utf8_decode($xml_plugin['url']));
            }
        }
    }

    /**
     * Register a new plugin in this repository.
     *
     * @param $name     string plugin name
     * @param $version  string plugin version
     * @param $name     string plugin URL
     */
    protected function registerPlugin ($name, $version, $url)
    {
        if (!isset($this->plugins[$name]) ||
            version_compare($version, $this->plugins[$name]['version']) > 0) {
            $this->plugins[$name] = array('version' => $version, 'url' => $url);
        }
    }

    /**
     * Get meta data for the plugin with the given name (if available).
     * Always chooses the newest compatible version of the plugin.
     *
     * @return array meta data for plugin (or NULL)
     */
    public function getPlugin ($name)
    {
        return $this->plugins[$name];
    }
}
?>
