<?php
/*
 * plugin_admin.php - plugin administration controller
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'app/controllers/authenticated_controller.php';
require_once 'app/models/plugin_administration.php';

class PluginAdminController extends AuthenticatedController
{
    private $plugin_admin;

    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        // user must have root permission
        $perm->check('root');

        // set page title and navigation
        $GLOBALS['CURRENT_PAGE'] = _('Verwaltung von Plugins');
        Navigation::activateItem('/admin/tools/plugins');

        $this->plugin_admin = new PluginAdministration();
    }

    /**
     * Shows the plugins view and display all available plugin updates.
     */
    public function index_action()
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');
        $type = $plugin_filter != '' ? $plugin_filter : NULL;

        $this->plugins       = $plugin_manager->getPluginInfos($type);
        $this->plugin_types  = $this->plugin_admin->getPluginTypes();
        $this->plugin_filter = $plugin_filter;
        $this->update_info   = $this->plugin_admin->getUpdateInfo($this->plugins);

        foreach ($this->update_info as $id => $info) {
            if (isset($info['update']) && !$this->plugins[$id]['depends']) {
                ++$this->num_updates;
            }
        }
    }

    /**
     * User changed the configuration of plugins.
     */
    public function save_action()
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');
        $type = $plugin_filter != '' ? $plugin_filter : NULL;
        $plugins = $plugin_manager->getPluginInfos($type);

        foreach ($plugins as $plugin){
            $enabled = Request::int('enabled_' . $plugin['id'], 0);
            $navpos = Request::int('position_' . $plugin['id']);

            $plugin_manager->setPluginEnabled($plugin['id'], $enabled);

            if (isset($navpos)) {
                $plugin_manager->setPluginPosition($plugin['id'], max($navpos, 1));
            }
        }

        $this->flash['message'] = _('Die Änderungen wurden gespeichert.');
        $this->redirect('plugin_admin?plugin_filter='.$plugin_filter);
    }

    private function compare_score($plugin1, $plugin2)
    {
        return $plugin2['score'] - $plugin1['score'];
    }

    public function search_action()
    {
        $repository = new PluginRepository();
        $search = Request::get('search');

        // reset search if empty
        if ($search === '') {
            $search = NULL;
        }

        // search for plugins in all repositories
        $search_results = $repository->getPlugins($search);
        $plugins = PluginManager::getInstance()->getPluginInfos();

        // filter out already installed plugins
        foreach ($plugins as $plugin) {
            if (isset($search_results[$plugin['name']])) {
                unset($search_results[$plugin['name']]);
            }
        }

        if ($search === NULL) {
            // sort plugins by score
            uasort($search_results, array($this, 'compare_score'));
            $search_results = array_slice($search_results, 0, 6);
        } else {
            // sort plugins by name
            uksort($search_results, 'strnatcasecmp');
        }

        $this->search         = $search;
        $this->search_results = $search_results;
        $this->plugins        = $plugins;
    }

    public function install_action($pluginname = NULL)
    {
        try {
            if (isset($pluginname)) {
                $status = $this->plugin_admin->installPluginByName($pluginname);
            } else if (get_config('PLUGINS_UPLOAD_ENABLE')) {
                // process the upload and register plugin in the database
                $upload_file = $_FILES['upload_file']['tmp_name'];
                $status = $this->plugin_admin->installPlugin($upload_file);
            }

            $this->flash['message'] = _('Das Plugin wurde erfolgreich installiert.');
        } catch (PluginInstallationException $ex) {
            $this->flash['error'] = $ex->getMessage();
        }

        if (isset($upload_file)) {
            unlink($upload_file);
        }

        $this->redirect('plugin_admin');
    }

    public function ask_delete_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();

        $this->plugins       = $plugin_manager->getPluginInfos();
        $this->plugin_types  = $this->plugin_admin->getPluginTypes();
        $this->delete_plugin = $this->plugins[$plugin_id];
        $this->update_info   = $this->plugin_admin->getUpdateInfo($this->plugins);

        $this->render_action('index');
    }

    public function delete_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin_filter = Request::option('plugin_filter', '');
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);

        if (isset($plugin)) {
            $this->plugin_admin->uninstallPlugin($plugin);
        }

        $this->redirect('plugin_admin?plugin_filter='.$plugin_filter);
    }

    public function download_action($plugin_id)
    {
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);

        $pluginpath = get_config('PLUGINS_PATH').'/'.$plugin['path'];
        $manifest = $this->plugin_admin->getPluginManifest($pluginpath);
        $filename = $plugin['class'].'-'.$manifest['version'].'.zip';
        $filepath = get_config('TMP_PATH').'/'.$filename;

        create_zip_from_directory($pluginpath, $filepath);

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.filesize($filepath));

        $this->render_nothing();

        readfile($filepath);
        unlink($filepath);
    }

    /**
     * Install updates for all selected plugins.
     */
    public function install_updates_action()
    {
        $plugins = PluginManager::getInstance()->getPluginInfos();
        $plugin_filter = Request::option('plugin_filter', '');
        $update_info = $this->plugin_admin->getUpdateInfo($plugins);

        $update = Request::intArray('update');
        $update_status = array();

        foreach ($update as $id) {
            if (isset($update_info[$id]['update'])) {
                try {
                    $update_url = $update_info[$id]['update']['url'];
                    $status = $this->plugin_admin->installPluginFromURL($update_url);
                } catch (PluginInstallationException $ex) {
                    $update_errors[] = sprintf('%s: %s', $update_info[$id]['name'], $ex->getMessage());
                }
            }
        }

        if (isset($update_errors)) {
            $this->flash['error'] = ngettext('Beim Update ist ein Fehler auftretren:',
                                             'Beim Update sind Fehler aufgetreten:', count($update_errors));
            $this->flash['error_detail'] = $update_errors;
        } else {
            $this->flash['message'] = _('Update erfolgreich installiert.');
        }

        $this->redirect('plugin_admin?plugin_filter='.$plugin_filter);
    }

    /**
     * Shows a page describing the plugin's functionality,
     * dependence on other plugins, ...
     */
    public function manifest_action($plugin_id) {
        // get information about the plugin
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);

        // retrieve manifest
        $pluginpath = get_config('PLUGINS_PATH').'/'.$plugin['path'];
        $plugininfos = $this->plugin_admin->getPluginManifest($pluginpath);

        $this->plugin      = $plugin;
        $this->plugininfos = $plugininfos;
    }

    /**
     * Shows the standard configuration.
     */
    public function default_activation_action($plugin_id) {
        // get information about the plugin
        $plugin_manager = PluginManager::getInstance();
        $plugin = $plugin_manager->getPluginInfoById($plugin_id);

        if (Request::submitted('save')) {
            $selected_inst = Request::optionArray('selected_inst');

            if (Request::get('nodefault')) {
                $plugin_manager->setDefaultActivations($plugin_id, array());
                $message = _('Die Voreinstellungen wurden gelöscht.');
                $selected_inst = array();
            } else {
                // save selected institutes
                $plugin_manager->setDefaultActivations($plugin_id, $selected_inst);
                $message = ngettext(
                    'Für das ausgewählte Institut wurde das Plugin standardmäßig aktiviert.',
                    'Für die ausgewählten Institute wurde das Plugin standardmäßig aktiviert.',
                    count($selected_inst));
            }
        } else {
            // load old config
            $selected_inst = $plugin_manager->getDefaultActivations($plugin_id);
        }

        $this->message       = $message;
        $this->plugin_name   = $plugin['name'];
        $this->plugin_id     = $plugin_id;
        $this->selected_inst = $selected_inst;
        $this->institutes    = $this->plugin_admin->getInstitutes();
    }
}
