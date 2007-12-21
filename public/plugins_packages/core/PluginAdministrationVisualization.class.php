<?php
/* vim: noexpandtab */
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
		switch ($errorcode) {
			case 0:
				break;
			case PLUGIN_INSTALLATION_SUCCESSFUL:
				StudIPTemplateEngine::showSuccessMessage(_("Die Installation des Plugins war erfolgreich"));
				break;
			case PLUGIN_UPLOAD_ERROR:
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Der Upload des Plugins ist fehlgeschlagen.")));
				break;
			case PLUGIN_MANIFEST_ERROR:
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Manifest des Plugins ist nicht korrekt.")));
				break;
			case PLUGIN_MISSING_MANIFEST_ERROR:
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Manifest des Plugins fehlt.")));
				break;
			case PLUGIN_ALLREADY_INSTALLED_ERROR:
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Plugin ist bereits installiert.")));
				break;
			case PLUGIN_ALREADY_REGISTERED_ERROR:
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Plugin ist bereits in der Datenbank registriert.")));
				break;
			default:
				StudIPTemplateEngine::showErrorMessage(sprintf(_("Bei der Installation des Plugins ist ein Fehler aufgetreten.")));
				break;
		}
	}

	function showPluginPackageDownloadView($packagelink){
		 StudIPTemplateEngine::showSuccessMessage(sprintf(_("Das Plugin-Paket erfolgreich erzeugt.<br>Sie können das Pluginpaket <a href=\"%s\">hier herunterladen.</a>"),$packagelink));
	}

	function showPluginList($plugins){
		$relativepath = $this->pluginref->getPluginpath();
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

		$relativepath = $this->pluginref->getPluginpath();
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
				<th alogn="center"><?= _("&nbsp;Zugriffsrechte") ?></th>
				<th align="right"><?= _("Package") ?></th>
			</tr>

		<?php

		$absenden = makeButton("speichern","input",_("Einstellungen speichern"));
		$lasttype = "";

		foreach($plugins as $plugin){
			$cssSw->switchClass();
			if (is_a($plugin,"PluginAdministrationPlugin") || is_subclass_of($plugin,"PluginAdministrationPlugin")){
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
			<tr <?=$cssSw->getHover()?>>
				<td align="left" class="<?=$cssSw->getClass()?>">
				<?
				 if (!$plugin->isDependentOnOtherPlugin()){
				 	?>
				 	<a href="<?= PluginEngine::getLink($this->pluginref,array("deinstall" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/trash.gif" border="0" alt="<?= _("Deinstallieren") ?>"/></a>&nbsp;
				 	<?
				 }
				?>
				</td>
				<td width="35%" align="left"class="<?=$cssSw->getClass()?>">
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
		  	 	 <td colspan="8" height="5"></td>
		  	 </tr>
		  	 <tr>
		  	 	 <td colspan="8" align="center"><?= $absenden ?></td>
		  	 </tr>
		  	 <tr>
		  	 	 <td colspan="8" height="10"></td>
		  	 </tr>
                         </table>
			 </form>
                         <?= $this->showInstallationForm($installableplugins);?>
		<?php

		StudIPTemplateEngine::createInfoBoxTableCell();

		$infobox = array	(
						array  ("kategorie"  => _("Hinweise:"),
								"eintrag" => array	(
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("Verfügbarkeit bedeutet bei Standard-Plugins, dass sie vom Dozenten in Veranstaltungen und Einrichtungen aktiviert werden können. Bei System- und Administrationsplugins wird zwischen Aktivierung und Verfügbarkeit nicht unterschieden.")
									),
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("Per Default-Aktivierung lassen sich Standard-Plugins automatisch in allen Veranstaltungen einer Einrichtung aktivieren.")
									),
									array (	"icon" => "ausruf_small.gif",
													"text"  => _("Position gibt die Reihenfolge des Plugins in der Navigation an. <b>Erlaubt sind nur Werte größer 0.</b>")
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
}
?>
