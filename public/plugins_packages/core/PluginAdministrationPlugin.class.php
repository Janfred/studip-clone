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
		$this->template_factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
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

	function actionInstallPlugin(){
		$forceupdate = $_POST["update"];
		$pluginfilename = $_POST["pluginfilename"];
		$user = $this->getUser();
		$permission = $user->getPermission();
		$pluginengine = PluginEngine::getPluginPersistence();
		$roleplugin = $pluginengine->getPluginid('de_studip_core_RoleManagementPlugin');

		$template = $this->template_factory->open('plugin_administration');

		// check if user has the permission to check in / update plugins
		if (!$permission->hasRootPermission()) {
			// show nothing
			return;
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
			'plugins'       => $pluginengine->getAllInstalledPlugins(),
			'roleplugin'    => $pluginengine->getPlugin($roleplugin),
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
		$pluginengine = PluginEngine::getPluginPersistence();
		$adminpluginengine = PluginEngine::getPluginPersistence('Administration');
		$roleplugin = $pluginengine->getPluginid('de_studip_core_RoleManagementPlugin');

		$template = $this->template_factory->open('plugin_administration');

		// check if user has the permission to check in / update plugins
		if (!$permission->hasRootPermission() && $permission->hasAdminPermission()){
			$template = $this->template_factory->open('plugin_list');

			$template->set_attributes(array(
				'admin_plugin'  => $this,
				'plugins'       => $pluginengine->getAllEnabledPlugins()
			));

			echo $template->render();
			return;
		}

		$zip = $_GET['zip'];
		$deinstall = $_GET['deinstall'];
		$forceupdate = $_POST['update'];
		$forcedeinstall = $_REQUEST['forcedeinstall'];

		if (isset($deinstall)) {
			$plugin = $pluginengine->getPlugin($deinstall);

			if (is_object($plugin)){
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
		} else {
			// user changed the configuration of plugins
			$plugins = $pluginengine->getAllInstalledPlugins();

			foreach ($plugins as $plugin){
				$id = $plugin->getPluginid();
				if (!isset($_POST["available_" . $id]) &&
				    !isset($_POST["navposition_" . $id])) {
					continue;
				}

				if ($_POST["available_" . $id] == "1") {
					$plugin->setEnabled(true);
				} else {
					$plugin->setEnabled(false);
				}

				$navpos = $_POST["navposition_" . $id];
				if ($navpos <= 0){
					// minimaler Wert
					$navpos = 1;
				}

				$plugin->setNavigationPosition($navpos);

				if ($plugin instanceof AbstractStudIPAdministrationPlugin) {
					if ($_POST["available_" . $id] == "1"){
						$plugin->setActivated(true);
					} else {
						$plugin->setActivated(false);
					}
					$adminpluginengine->savePlugin($plugin);
				} else {
					// keine spezielle Behandlung nötig
					$pluginengine->savePlugin($plugin);
				}
			}
		}

		$template->set_attributes(array(
			'admin_plugin'  => $this,
			'plugins'       => $pluginengine->getAllInstalledPlugins(),
			'roleplugin'    => $pluginengine->getPlugin($roleplugin),
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

		# unconsumed_path contains the plugin's class name
		$plugin_class = current(explode('/', $this->unconsumed_path));
		if ($plugin_class === '') {
			throw new Studip_PluginNotFoundException(_("Kein Plugin angegeben."));
		}

		# retrieve corresponding plugin id
		$plugin_persistence = PluginEngine::getPluginPersistence();
		$plugin_id = $plugin_persistence->getPluginId($plugin_class);

		# create an instance of the queried plugin
		$plugin = $plugin_persistence->getPlugin($plugin_id);

		# retrieve manifest
		$pluginpath = $plugin->environment->getBasepath().$plugin->getPluginpath();
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

		# unconsumed_path contains the plugin's class name
		$plugin_class = current(explode('/', $this->unconsumed_path));
		if ($plugin_class === '') {
			throw new Studip_PluginNotFoundException(_("Kein Plugin angegeben."));
		}

		# retrieve corresponding plugin id
		$plugin_persistence = PluginEngine::getPluginPersistence();
		$plugin_id = $plugin_persistence->getPluginId($plugin_class);

		# create an instance of the queried plugin
		$plugin = $plugin_persistence->getPlugin($plugin_id);

		if (isset($_POST['selected'])) {
			$selected_inst = $_POST['selected_inst'];

			if (isset($_POST['nodefault'])) {
				if ($plugin->pluginengine->removeDefaultActivations($plugin)) {
					$message = array('msg' => _('Die Voreinstellungen wurden erfolgreich gelöscht.'));
					$selected_inst = array();
				} else {
					$message = array('err' => _('Die Voreinstellungen konnten nicht gelöscht werden.'));
				}
			} else {
				// save selected institutes
				if ($plugin->pluginengine->saveDefaultActivations($plugin, $selected_inst)) {
					$message = array('msg' => ngettext('Für das ausgewählte Institut wurde das Plugin standardmäßig aktiviert.',
									   'Für die ausgewählten Institute wurde das Plugin standardmäßig aktiviert.',
									   count($selected_inst)));
				} else {
					$message = array('err' => _('Das Abspeichern der Default-Einstellungen ist fehlgeschlagen.'));
				}
			}
		} else {
			// load old config
			$selected_inst = $plugin->pluginengine->getDefaultActivations($plugin);
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

		$pluginengine = PluginEngine::getPluginPersistence();
		$plugins = $pluginengine->getAllInstalledPlugins();
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

		$pluginengine = PluginEngine::getPluginPersistence();
		$plugins = $pluginengine->getAllInstalledPlugins();
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
