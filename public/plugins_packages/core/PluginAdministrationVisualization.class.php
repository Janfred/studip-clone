<?php
// vim: noexpandtab
/**
 * The visualization of the plugin administration plugin
 * @author Dennis Reil <Dennis.Reil@offis.de>
 * @version $Revision$
 * $Id$
 */
require_once("PluginAdministration.class.php");
class PluginAdministrationVisualization extends AbstractStudIPPluginVisualization {

	// the constructor
	function PluginAdministrationVisualization($adminplugin){
		parent::AbstractStudIPPluginVisualization($adminplugin);
	}

	function showDeinstallQuestion($plugin) {
		if (!is_object($plugin)){
			return;
		}
		?>
		<table style="width: 100%;">
		<tr>
			<td valign="top"><img src="<?=$GLOBALS['ASSETS_URL']?>/images/ausruf.gif"></td>
			<td valign="top">
			<?= sprintf(_("Wollen Sie wirklich <b>%s</b> deinstallieren? <br>"), htmlReady($plugin->getPluginname()))?>
			<?= sprintf("<a href=\"%s\">" . makeButton("ja2") . "</a>&nbsp; \n",PluginEngine::getLink($this->pluginref,array("deinstall" => $plugin->getPluginid(),"forcedeinstall" => true)))?>
			<?= sprintf("<a href=\"%s\">" . makeButton("nein") . "</a>\n",PluginEngine::getLinkToAdministrationPlugin())?>
			</td>
		</tr>
		<tr>
			<td colspan="2" height="5">&nbsp;</td>
		</tr>
		</table>
		<?php
	}

	function showMessage($errorcode) {
		if ($errorcode) {
			$message = PluginAdministration::getErrorMessage($errorcode);

			if ($errorcode == PLUGIN_INSTALLATION_SUCCESSFUL) {
				StudIPTemplateEngine::showSuccessMessage($message);
			} else {
				StudIPTemplateEngine::showErrorMessage($message);
			}
		}
	}

	function showPluginPackageDownloadView($packagelink){
		 StudIPTemplateEngine::showSuccessMessage(sprintf(_("Das Plugin-Paket erfolgreich erzeugt.<br>Sie können das Pluginpaket <a href=\"%s\">hier herunterladen.</a>"),$packagelink));
	}

	function showPluginList($plugins){
		$relativepath = $this->pluginref->getPluginURL();
		?>
			<tr>
				<th align="left"><?= _("Name")?></th>
				<th align="left"><?= _("Typ") ?></th>
				<th align="left"><?= _("Verfügbarkeit") ?></th>
			</tr>
		<?php

		foreach($plugins as $plugin){
			if (($plugin->getPluginname() == "PluginAdministration") || ($plugin->getPluginid() == 1)){
				continue;
			}
		?>
			<tr>
				<td width="35%" align="left"><?= $plugin->getPluginname() ?></td>
				<td width="5%" align="left"><?= PluginEngine::getTypeOfPlugin($plugin) ?></td>
				<td align="left"><img src="<?= $relativepath?>/img/haken.gif" border="0" /><?= _("Aktiviert") ?></td>
			</tr>
		<?php
		}
	}

	function showPluginAdministrationList($plugins,$msg="",$installableplugins=array(),$roleplugin=null){
		$cssSw = new cssClassSwitcher();									// Klasse für Zebra-Design
		$cssSw->enableHover();
		echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
		$cssSw->resetClass();
		// default view
		$nav = $this->pluginref->getTopNavigation();
		$this->showMessage($msg);

		$relativepath = $this->pluginref->getPluginURL();
		?>
		  	<form action="<?= PluginEngine::getLink($this->pluginref) ?>" method="post">
		  	<input type="hidden" name="action" value="config" />

			<table style="width: 100%;" cellspacing="0">
			<tr>
				<th align="left" width="2%"></th>
				<th align="left"><?= _("Name")?></th>
				<th align="left"><?= _("Typ") ?></th>
				<th align="center"><?= _("Verfügbarkeit") ?></th>
				<th align="right"><?= _("Position&nbsp;") ?></th>
				<th align="center"><?= _("&nbsp;Zugriffsrechte") ?></th>
				<th align="right"><?= _("Package") ?></th>
			</tr>

		<?php

		$update_link = PluginEngine::getLink($this->pluginref, array(), 'showUpdates');
		$absenden = makeButton("speichern","input",_("Einstellungen speichern"));
		$lasttype = "";

		foreach($plugins as $plugin){
			$cssSw->switchClass();
			if ($plugin instanceof PluginAdministrationPlugin) {
				continue;
			}
			$type = PluginEngine::getTypeOfPlugin($plugin);
			if ($type != $lasttype){
			   ?>
			<tr>
				<td colspan="7" height="10"></td>
			</tr>

			   <?php
			}
			$lasttype = $type;
			$pluginid = $plugin->getPluginid();
		?>
			<tr style="height: 25px;" <?=$cssSw->getHover()?>>
				<td align="left" class="<?=$cssSw->getClass()?>">
				<?
				 if (!$plugin->isDependentOnOtherPlugin()){
				 	?>
				 	<a href="<?= PluginEngine::getLink($this->pluginref,array("deinstall" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/trash.gif" border="0" alt="<?= _("Deinstallieren") ?>"/></a>&nbsp;
				 	<?
				 }
				?>
				</td>
				<td width="35%" align="left" class="<?=$cssSw->getClass()?>">
					<a href="<?= PluginEngine::getLinkToAdministrationPlugin(array(), 'manifest/'.$plugin->getPluginclassname()) ?>">
						<?= $plugin->getPluginname() ?>
					</a>
					&nbsp;
				<?php
				if (PluginEngine::getTypeOfPlugin($plugin) == "Standard"){
				?>
					<a href="<?= PluginEngine::getLinkToAdministrationPlugin(array(), "DefaultActivation/".$plugin->getPluginclassname()) ?>"><?= _("(Default-Aktivierung)")?></a></td>
				<?php
				}
				?>
				<td width="5%" align="left" class="<?=$cssSw->getClass()?>"><?= $type ?></td>
				<td align="center" class="<?=$cssSw->getClass()?>">
					<select name="available_<?= $pluginid?>">
						<option value="1" <? if ($plugin->isEnabled()) echo ("selected") ?>><?= _("an")?></option>
						<option value="0" <? if (!($plugin->isEnabled())) echo ("selected") ?>><?= _("aus")?></option>
					</select>
				</td>
				<td align="right" width="5%" class="<?=$cssSw->getClass()?>"><input name="navposition_<?= $pluginid?>" type="text" size="2" value="<?= $plugin->getNavigationPosition()?>"></td>
				<td align="center" class="<?=$cssSw->getClass()?>">&nbsp;<a href="<?= PluginEngine::getLink($roleplugin,array("pluginid"=>$pluginid),"doPluginRoleAssignment")?>"><?= makeButton("bearbeiten","img",_("Rollenberechtigungen bearbeiten")) ?></a></td>
				<td align="right" class="<?=$cssSw->getClass()?>">
				<?
					if (!$plugin->isDependentOnOtherPlugin()){
						?>
						<a href="<?= PluginEngine::getLink($this->pluginref,array("zip" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/icon-disc.gif" border="0" alt="<?= _("Plugin zippen")?>"/></a>
						<?
					}
				?>
				</td>
			</tr>
		<?php
		}
		?>
			<tr>
				<td colspan="7" height="5"></td>
			</tr>
			<tr>
				<td colspan="7" align="center"><?= $absenden ?></td>
			</tr>
			<tr>
				<td colspan="7" height="10"></td>
			</tr>
			</table>
			</form>
			<?= $this->showInstallationForm($installableplugins) ?>
		<?php

		StudIPTemplateEngine::createInfoBoxTableCell();

		$infobox = array(
			array(  'kategorie' => _('Hinweise:'),
				'eintrag'   => array(
					array(	'icon' => 'ausruf_small.gif',
						'text'  => _('Verfügbarkeit bedeutet bei Standard-Plugins, dass sie vom Dozenten in Veranstaltungen und Einrichtungen aktiviert werden können. Bei System- und Administrationsplugins wird zwischen Aktivierung und Verfügbarkeit nicht unterschieden.')
					),
					array(	'icon' => 'ausruf_small.gif',
						'text'  => _('Per Default-Aktivierung lassen sich Standard-Plugins automatisch in allen Veranstaltungen einer Einrichtung aktivieren.')
					),
					array(	'icon' => 'ausruf_small.gif',
						'text'  => _('Position gibt die Reihenfolge des Plugins in der Navigation an. Erlaubt sind nur Werte größer 0.')
					)
				)
			),
			array(  'kategorie' => _('Aktionen:'),
				'eintrag'   => array(
					array(	'icon' => 'link_intern.gif',
						'text'  => '<a href="'.$update_link.'">'._('Installierte Plugins aktualisieren').'</a>'
					)
				)
			)
		);
		print_infobox ($infobox,"modules.jpg");
		StudIPTemplateEngine::endInfoBoxTableCell();
	}

	function showInstallationForm($installableplugins=array()){
		StudIPTemplateEngine::makeContentHeadline(_("Installation neuer Plugins"));
		if (isset($GLOBALS["PLUGINS_UPLOAD_ENABLE"]) && $GLOBALS["PLUGINS_UPLOAD_ENABLE"]){
		?>
		<form action="<?= PluginEngine::getLink($this->pluginref,array(),"installPlugin")?>" enctype="multipart/form-data" method="post">
			<input type="hidden" value="install" name="action">
			<input name="upload_file" type="file" size="50" maxlength="100000">
			<?= makeButton("hinzufuegen","input",_("neues Plugin installieren")) ?><br>
                        <label>
			<input type="checkbox" name="update" value="force"><?= _("Aktualisieren, falls Plugin schon vorhanden.")?>
                        </label>
		</form>
		<?php
		}
		else {
			// Upload von Plugins ist nicht erlaubt
		?>
		<form action="<?= PluginEngine::getLink($this->pluginref,array(),"installPlugin")?>" enctype="multipart/form-data" method="post">
			<input type="hidden" value="install" name="action">
			<table>

			<?php
			foreach ($installableplugins as $pluginfilename){
				?>
				<tr>
					<td>
						<input type="radio" name="pluginfilename" value="<?= $pluginfilename?>">
					</td>
					<td>
						<?= $pluginfilename?>
					</td>
				</tr>
				<?php
			}
			?>
				<tr>
					<td>
						<input type="radio" name="pluginfilename" value="kein">
					</td>
					<td>
						<?= _("Kein Plugin wählen")?>
					</td>
				</tr>
			</table>
			<?= makeButton("hinzufuegen","input",_("neues Plugin installieren")) ?><br>
                        <label>
			<input type="checkbox" name="update" value="force"><?= _("Aktualisieren, falls Plugin schon vorhanden.")?>
                        </label>
		</form>
		<?php
		}
	}

	function showPluginUpdateList ($plugins, $update_info, $update_status = array()) {
		$cssSw = new cssClassSwitcher();									// Klasse für Zebra-Design
		$cssSw->enableHover();
		echo "\n" . $cssSw->GetHoverJSFunction() . "\n";
		$cssSw->resetClass();

		$relativepath = $this->pluginref->getPluginURL();
		?>
		  	<form action="<?= PluginEngine::getLink($this->pluginref, array(), 'installUpdates') ?>" method="post">

			<table style="width: 100%;" cellspacing="0">
			<tr>
				<th align="left" width="2%"></th>
				<th align="left"><?= _("Name")?></th>
				<th align="left"><?= _("Typ") ?></th>
				<th align="left"><?= _("installierte Version") ?></th>
				<th align="left"><?= _("verfügbare Version") ?></th>
				<th align="left"><?= _("aktualisieren") ?></th>
			</tr>

		<?php

		$absenden = makeButton("starten", "input", _("Aktualisierung starten"));
		$abbrechen = '<a href="'.PluginEngine::getLink($this->pluginref).'">'.makeButton("abbrechen").'</a>';
		$lasttype = "";

		foreach ($plugins as $plugin) {
			$cssSw->switchClass();
			if ($plugin instanceof PluginAdministrationPlugin) {
				continue;
			}
			$type = PluginEngine::getTypeOfPlugin($plugin);
			if ($type != $lasttype){
			   ?>
			<tr>
				<td colspan="6" height="10"></td>
			</tr>

			   <?php
			}
			$lasttype = $type;
			$pluginid = $plugin->getPluginid();
		?>
			<tr style="height: 25px;" <?=$cssSw->getHover()?>>
				<td align="left" class="<?=$cssSw->getClass()?>">
				</td>
				<td width="25%" align="left" class="<?=$cssSw->getClass()?>">
					<a href="<?= PluginEngine::getLinkToAdministrationPlugin(array(), 'manifest/'.$plugin->getPluginclassname()) ?>">
						<?= $plugin->getPluginname() ?>
					</a>
				<td width="10%" align="left" class="<?=$cssSw->getClass()?>"><?= $type ?></td>
				<td width="15%" align="left" class="<?=$cssSw->getClass()?>">
					<?= htmlentities($update_info[$pluginid]['version']) ?>
				</td>
				<td width="15%" align="left" class="<?=$cssSw->getClass()?>">
					<? if (isset($update_info[$pluginid]['update'])): ?>
						<?= htmlentities($update_info[$pluginid]['update']['version']) ?>
					<? endif ?>
				</td>
				<td width="33%" align="left" class="<?=$cssSw->getClass()?>">
					<? if (isset($update_status[$pluginid])): ?>
						<? if ($update_status[$pluginid] == PLUGIN_INSTALLATION_SUCCESSFUL): ?>
							<span style="color: green;">
								<?= _('Update erfolgreich installiert') ?>
							</span>
						<? else: ?>
							<span style="color: red;">
								<?= PluginAdministration::getErrorMessage($update_status[$pluginid]) ?>
							</span>
						<? endif ?>
					<? elseif (!$plugin->isDependentOnOtherPlugin() && isset($update_info[$pluginid]['update'])): ?>
						<label>
							<input type="checkbox" name="update[]" value="<?= $pluginid ?>" checked>
							<?= _('Update installieren') ?>
						</label>
					<? endif ?>
				</td>
			</tr>
		<?php
		}
		?>
			<tr>
				<td colspan="6" height="5"></td>
			</tr>
			<tr>
				<td colspan="6" align="center"><?= $absenden ?>&nbsp;<?= $abbrechen ?></td>
			</tr>
			<tr>
				<td colspan="6" height="10"></td>
			</tr>
			</table>
			</form>
			<?= $this->showInstallationForm(PluginEngine::getInstallablePlugins()) ?>
		<?php

		StudIPTemplateEngine::createInfoBoxTableCell();

		$infobox = array(
			array(  'kategorie' => _('Hinweise:'),
				'eintrag'   => array(
					array(  'icon' => 'ausruf_small.gif',
						'text'  => _('Wählen Sie in der Liste aus, welche Plugins aktualisiert werden sollen und klicken Sie auf "starten".')
					),
					array(	'icon' => 'ausruf_small.gif',
						'text'  => _('Die automatische Aktualisierung wird nicht von allen Plugins unterstützt.')
					)
				)
			),
			array(  'kategorie' => _('Aktionen:'),
				'eintrag'   => array(
					array(	'icon' => 'link_intern.gif',
						'text'  => '<a href="'.PluginEngine::getLink($this->pluginref).'">'._('Verwaltung von Plugins').'</a>'
					)
				)
			)
		);
		print_infobox ($infobox,"modules.jpg");
		StudIPTemplateEngine::endInfoBoxTableCell();
	}
}
?>
