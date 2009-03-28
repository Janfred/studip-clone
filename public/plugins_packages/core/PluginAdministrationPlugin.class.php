<?php
// vim: noexpandtab
/*
 * Plugin for the administration of plugins and a good example for an Administration-Plugin
 * @author Dennis Reil <dennis.reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once 'PluginAdministration.class.php';

class PluginAdministrationPlugin extends AbstractStudIPAdministrationPlugin{
	// management
	var $pluginmgmt;
	// template factory
	var $template_factory;
	// layout template
	var $layout;
	// environment
	var $environment;

	/**
	 *
	 */
	function PluginAdministrationPlugin(){
		AbstractStudIPAdministrationPlugin::AbstractStudIPAdministrationPlugin();
		$tab = new PluginNavigation();
		$tab->setDisplayname(_("Verwaltung von Plugins"));
		$this->setNavigation($tab);
		$this->setTopNavigation($tab);
		$this->setPluginiconname("img/einst.gif");
	}

	/**
	 * Initializes basic functions like the PluginVisualization and the PluginAdministration
	 *
	 */
	function initialize(){
		if ($this->pluginmgmt == null){
			$this->pluginmgmt = new PluginAdministration($this->environment);
		}
	}

	/**
	 * Common code for all actions: set up template factory and layout template.
	 */
	function display_action($action) {
		$this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');

		$this->layout = $GLOBALS['template_factory']->open('layouts/base');
		$this->layout->set_attribute('tabs', 'links_admin');
		$GLOBALS['CURRENT_PAGE'] = $this->getDisplayTitle();

		$this->$action();
		page_close();
	}

	function actionInstallPlugin(){
		$forceupdate = $_POST["update"];
		$pluginfilename = $_POST["pluginfilename"];
		$user = $this->getUser();
		$permission = $user->getPermission();

		$template = $this->template_factory->open('plugin_administration');
		$template->set_layout($this->layout);

		// check if user has the permission to check in / update plugins
		if (!$permission->hasRootPermission()) {
			throw new Studip_AccessDeniedException();
		}

		if ($GLOBALS['PLUGINS_UPLOAD_ENABLE']){
			$upload_file = $_FILES["upload_file"]["tmp_name"];
			// process the upload and register plugin in the database
			$result = $this->pluginmgmt->installPlugin($upload_file,$forceupdate);
		} else if (isset($pluginfilename) && isset($GLOBALS['NEW_PLUGINS_PATH'])){
			$newpluginfilename = $GLOBALS['NEW_PLUGINS_PATH'] . "/" . $pluginfilename;
			$result = $this->pluginmgmt->installPlugin($newpluginfilename,$forceupdate);
		} else {
			// nothing to do
		}

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'errorcode'     => $result,
			'plugins'       => PluginManager::getInstance()->getPluginInfos(),
			'roleplugin'    => PluginEngine::getPlugin('RoleManagementPlugin'),
			'installable'   => PluginEngine::getInstallablePlugins()
		));

		echo $template->render();
	}

	/**
	 * Shows the plugins view
	 *
	 */
	function actionShow(){
		$user = $this->getUser();
		$permission = $user->getPermission();
		$plugin_manager = PluginManager::getInstance();

		$template = $this->template_factory->open('plugin_administration');
		$template->set_layout($this->layout);

		// check if user has the permission to view / edit plugins
		if (!$permission->hasAdminPermission()) {
			throw new Studip_AccessDeniedException();
		} else if (!$permission->hasRootPermission()) {
			$template = $this->template_factory->open('plugin_list');
			$template->set_layout($this->layout);

			$template->set_attributes(array(
				'admin_plugin'  => $this,
				'plugins'       => $plugin_manager->getPluginInfos()
			));

			echo $template->render();
			return;
		}

		$zip = $_GET['zip'];
		$deinstall = $_GET['deinstall'];
		$forceupdate = $_POST['update'];
		$forcedeinstall = $_REQUEST['forcedeinstall'];

		if (isset($deinstall)) {
			$plugin = $plugin_manager->getPluginInfoById($deinstall);

			if (isset($plugin)) {
				if (isset($forcedeinstall)){
					// Plugin notwendige Änderungen vor der deinstallation durchführen lassen
					$this->pluginmgmt->deinstallPlugin($plugin);
				}
				else {
					// ask, if it should really be deleted
					$template->set_attribute('delete_plugin', $plugin);
				}
			}
		} else if (isset($zip)) {
			$link = $this->pluginmgmt->zipPluginPackage($zip);
			$template->set_attribute('packagelink', $link);
		} else if (isset($_REQUEST['save_x'])) {
			// user changed the configuration of plugins
			$plugins = $plugin_manager->getPluginInfos();

			foreach ($plugins as $plugin){
				$id = $plugin['id'];
				if (!isset($_POST["available_" . $id]) &&
				    !isset($_POST["navposition_" . $id])) {
					continue;
				}

				$enabled = $_POST["available_" . $id] == "1";
				$plugin_manager->setPluginEnabled($id, $enabled);

				// minimaler Wert
				$navpos = max($_POST["navposition_" . $id], 1);
				$plugin_manager->setPluginPosition($id, $navpos);
			}
		}

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'plugins'       => $plugin_manager->getPluginInfos(),
			'roleplugin'    => PluginEngine::getPlugin('RoleManagementPlugin'),
			'installable'   => PluginEngine::getInstallablePlugins()
		));

		echo $template->render();
	}

	/**
	 * Shows a page describing the plugin's functionality,
	 * dependence on other plugins, ...
	 */
	function actionManifest() {

		$permission = $this->user->getPermission();
		if (!$permission->hasAdminPermission()) {
			throw new Studip_AccessDeniedException();
		}

		$template = $this->template_factory->open('plugin_manifest');
		$template->set_layout($this->layout);

		# unconsumed_path contains the plugin's class name
		$plugin_class = current(explode('/', $this->unconsumed_path));
		if ($plugin_class === '') {
			throw new Studip_PluginNotFoundException(_("Kein Plugin angegeben."));
		}

		# get information about the queried plugin
		$plugin = PluginManager::getInstance()->getPluginInfo($plugin_class);

		# retrieve manifest
		$pluginpath = $this->environment->getPackagebasepath().'/'.$plugin['path'];
		$plugininfos = PluginEngine::getPluginManifest($pluginpath);

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'plugin'        => $plugin,
			'plugininfos'   => $plugininfos
		));

		echo $template->render();
	}

	/**
	 * Shows the standard configuration.
	 */
	function actionDefaultActivation() {

		$permission = $this->user->getPermission();
		if (!$permission->hasAdminPermission()) {
			throw new Studip_AccessDeniedException();
		}

		$template = $this->template_factory->open('plugin_default_activation');
		$template->set_layout($this->layout);

		# unconsumed_path contains the plugin's class name
		$plugin_class = current(explode('/', $this->unconsumed_path));
		if ($plugin_class === '') {
			throw new Studip_PluginNotFoundException(_("Kein Plugin angegeben."));
		}

		$plugin_manager = PluginManager::getInstance();
		$plugin_info = $plugin_manager->getPluginInfo($plugin_class);

		if (isset($_POST['selected'])) {
			$selected_inst = isset($_POST['selected_inst']) ? $_POST['selected_inst'] : array();

			if (isset($_POST['nodefault'])) {
				$plugin_manager->setDefaultActivations($plugin_info['id'], array());
				$message = array('msg' => _('Die Voreinstellungen wurden erfolgreich gelöscht.'));
				$selected_inst = array();
			} else {
				// save selected institutes
				$plugin_manager->setDefaultActivations($plugin_info['id'], $selected_inst);
				$message = array('msg' => ngettext(
					'Für das ausgewählte Institut wurde das Plugin standardmäßig aktiviert.',
					'Für die ausgewählten Institute wurde das Plugin standardmäßig aktiviert.',
					count($selected_inst)));
			}
		} else {
			// load old config
			$selected_inst = $plugin_manager->getDefaultActivations($plugin_info['id']);
		}

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'message'       => $message,
			'selected_inst' => $selected_inst,
			'institutes'    => StudIPCore::getInstitutes()
		));

		echo $template->render();
	}

	/**
	 * Display all available plugin updates.
	 */
	function actionShowUpdates ()
	{
		$permission = $this->user->getPermission();
		if (!$permission->hasRootPermission()) {
			throw new Studip_AccessDeniedException();
		}

		$template = $this->template_factory->open('plugin_update');
		$template->set_layout($this->layout);

		$plugins = PluginManager::getInstance()->getPluginInfos();
		$update_info = $this->pluginmgmt->getUpdateInfo($plugins);

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'plugins'       => $plugins,
			'update_info'   => $update_info,
			'installable'   => PluginEngine::getInstallablePlugins()
		));

		echo $template->render();
	}

	/**
	 * Install updates for all selected plugins.
	 */
	function actionInstallUpdates ()
	{
		$permission = $this->user->getPermission();
		if (!$permission->hasRootPermission()) {
			throw new Studip_AccessDeniedException();
		}

		$template = $this->template_factory->open('plugin_update');
		$template->set_layout($this->layout);

		$plugins = PluginManager::getInstance()->getPluginInfos();
		$update_info = $this->pluginmgmt->getUpdateInfo($plugins);

		$update = isset($_POST['update']) ? $_POST['update'] : array();
		$update_status = array();

		foreach ($update as $id) {
			if (isset($update_info[$id]['update'])) {
				$update_url = $update_info[$id]['update']['url'];
				$update_status[$id] =
					$this->pluginmgmt->installPluginFromURL($update_url);
			}
		}

		$update_info = $this->pluginmgmt->getUpdateInfo($plugins);

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'plugins'       => $plugins,
			'update_info'   => $update_info,
			'update_status' => $update_status,
			'installable'   => PluginEngine::getInstallablePlugins()
		));

		echo $template->render();
	}
}
