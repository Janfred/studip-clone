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

			// show nothing
			// echo $template->render();
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

		if (is_object($this->user)) {
			$permission = $this->user->getPermission();
			if (!$permission->hasAdminPermission()) {
				throw new Studip_AccessDeniedException();
			}
		}

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
		$plugininfos =
			PluginEngine::getPluginManifest($plugin->environment->getBasepath() .
			                                $plugin->pluginpath . '/');
		StudIPTemplateEngine::makeContentHeadline(_("Plugin-Details"), 2);
		?>
			<table>
				<tr>
					<td>Name:</td>
					<td align="left">&nbsp;<?= $plugin->pluginname ?></td>
				</tr>
				<tr>
					<td>Name (original):</td>
					<td align="left">&nbsp;<?= $plugininfos["pluginname"] ?></td>
				</tr>
				<tr>
					<td>Klasse:</td>
					<td align="left">&nbsp;<?= $plugin->getPluginclassname() ?></td>
				</tr>
				<tr>
					<td>Origin:</td>
					<td align="left">&nbsp;<?= $plugininfos["origin"] ?></td>
				</tr>
				<tr>
					<td>Version:</td>
					<td align="left">&nbsp;<?= $plugininfos["version"] ?></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><a href="<?= PluginEngine::getLinkToAdministrationPlugin() ?>"><?= makeButton("zurueck","img",_("zurück zur Plugin-Verwaltung")) ?></a></td>
				</tr>
			</table>
		<?
	}

	/**
	 * Shows the standard configuration.
	 */
	function actionDefaultActivation() {

		if (is_object($this->user)) {
			$permission = $this->user->getPermission();
			if (!$permission->hasAdminPermission()) {
				throw new Studip_AccessDeniedException();
			}
		}

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

		$user = $plugin->getUser();
		$permission = $user->getPermission();
		if (!$permission->hasAdminPermission()) {
			throw new Studip_AccessDeniedException(_("Sie besitzen keine Berechtigung, um dieses Plugin zu konfigurieren."));
		}
		else {
			StudIPTemplateEngine::makeContentHeadline(_("Default-Aktivierung"));
			$sel_institutes = $_POST["sel_institutes"];
			if ($_GET["selected"]){
				if ($_POST["nodefault"] == true) {
					if ($plugin->pluginengine->removeDefaultActivations($plugin)) {
						StudIPTemplateEngine::showSuccessMessage(_("Die Voreinstellungen wurden erfolgreich gelöscht."));
						$sel_institutes = array();
					}
					else {
						StudIPTemplateEngine::showErrorMessage(_("Die Voreinstellungen konnten nicht gelöscht werden"));
					}
				}
				else {
					// save selected institutes
					if ($plugin->pluginengine->saveDefaultActivations($plugin, $sel_institutes)) {
						// show info
						if (count($sel_institutes) > 1) {
							StudIPTemplateEngine::showSuccessMessage(_("Für die ausgewählten Institute wurde das Plugin standardmäßig aktiviert!"));
						}
						else {
							StudIPTemplateEngine::showSuccessMessage(_("Für das ausgewählte Institut wurde das Plugin standardmäßig aktiviert!"));
						}
					}
					else {
						StudIPTemplateEngine::showErrorMessage(_("Das Abspeichern der Default-Einstellungen ist fehlgeschlagen"));
					}
				}
			}
			else {
				// load old config
				$sel_institutes = $plugin->pluginengine->getDefaultActivations($plugin);
			}

			?>
			<tr>
				<td>
					<?
					echo _("Wählen Sie die Einrichtungen, in deren Veranstaltungen das Plugin automatisch aktiviert sein soll.<p>");
					$institutes = StudIPCore::getInstitutes();
					?>
					<form action="<?= PluginEngine::getLinkToAdministrationPlugin(array("selected" => true), "DefaultActivation/".$plugin->getPluginclassname()) ?>" method="POST">
					<select name="sel_institutes[]" multiple size="20">
					<?

					foreach ($institutes as $institute) {
						// if id is in selected institutes, the mark it as selected

						if (array_search($institute->getId(),  $sel_institutes) !== false){
							$selected = "selected";
						}
						else {
							$selected = "";
						}
						echo(sprintf("<option value=\"%s\" %s> %s </option>", $institute->getId(), $selected, $institute->getName()));
						$childs = $institute->getAllChildInstitutes();
						foreach ($childs as $child) {
							if (array_search($child->getId(), $sel_institutes) !== false) {
								$selected = "selected";
							}
							else {
								$selected = "";
							}
							echo(sprintf("<option value=\"%s\" %s>&nbsp;&nbsp;&nbsp;&nbsp; %s </option>",$child->getId(),$selected, $child->getName()));
						}
					}

					?>
					</select><br>
					<input type="checkbox" name="nodefault"><?= _("keine Voreinstellung wählen") ?>
					<p>

					<?= makeButton("uebernehmen", "input", _("Einstellungen speichern")) ?>
					<a href="<?= PluginEngine::getLinkToAdministrationPlugin() ?>"><?= makeButton("zurueck", "img",  _("Zurück zur Plugin-Verwaltung")) ?></a>
					</form>
				</td>
			</tr>

			<?php

			StudIPTemplateEngine::createInfoBoxTableCell();
			$infobox = array(array(
				"kategorie" => _("Hinweise:"),
				"eintrag" => array(array("icon" => "ausruf_small.gif",
				                         "text" => _("Wählen Sie die Institute, in deren Veranstaltungen das Plugin standardmäßig eingeschaltet werden soll.")),
				                   array("icon" => "ausruf_small.gif",
				                         "text" => _("Eine Mehrfachauswahl ist durch Drücken der Strg-Taste möglich.")))));
			print_infobox($infobox, 'modules.jpg');
			StudIPTemplateEngine::endInfoBoxTableCell();
		}
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
