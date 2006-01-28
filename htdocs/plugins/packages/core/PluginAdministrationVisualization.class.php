<?php
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
	
	function showPluginInstallationSuccess(){
		StudIPTemplateEngine::makeHeadline(_("Installation erfolgreich"));
		StudIPTemplateEngine::startContentTable();
		StudIPTemplateEngine::showSuccessMessage(_("Die Installation des Plugins war erfolgreich"));
		StudIPTemplateEngine::endContentTable();
	}
	
	function showPluginInstallationError($errorcode){
		StudIPTemplateEngine::makeHeadline(_("Installation fehlgeschlagen"));
		StudIPTemplateEngine::startContentTable();
		switch ($errorcode) {
			case PLUGIN_UPLOAD_ERROR: StudIPTemplateEngine::showErrorMessage(sprintf(_("Der Upload des Plugins ist fehlgeschlagen. <a href=\"%s\">%s</a>"),PluginEngine::getLinkToAdministrationPlugin(),makeButton("zurueck","img")));
								 break;
			case PLUGIN_MANIFEST_ERROR: StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Manifest des Plugins ist nicht korrekt. <a href=\"%s\">%s</a>"),PluginEngine::getLinkToAdministrationPlugin(),makeButton("zurueck","img")));
								break;			
			case PLUGIN_MISSING_MANIFEST_ERROR: StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Manifest des Plugins fehlt. <a href=\"%s\">%s</a>"),PluginEngine::getLinkToAdministrationPlugin(),makeButton("zurueck","img")));
								break;
			case PLUGIN_ALLREADY_INSTALLED_ERROR: StudIPTemplateEngine::showErrorMessage(sprintf(_("Das Plugin ist bereits installiert. <a href=\"%s\">%s</a>"),PluginEngine::getLinkToAdministrationPlugin(),makeButton("zurueck","img")));
								break;
			default: StudIPTemplateEngine::showErrorMessage(sprintf(_("Die Installation ist fehlgeschlagen. <a href=\"%s\">%s</a>"),PluginEngine::getLinkToAdministrationPlugin(),makeButton("zurueck","img")));
		}
		StudIPTemplateEngine::endContentTable();
	}
	
	function showPluginPackageDownloadView($packagelink){
		 StudIPTemplateEngine::makeHeadline(_("Plugin-Paket downloaden"));	 
		 StudIPTemplateEngine::startContentTable();
		 StudIPTemplateEngine::showSuccessMessage(_("Plugin-Paket erfolgreich erzeugt"));
		 ?>
		 <tr>
		 	 <td><?= sprintf(_("Sie können das Pluginpaket <a href=\"%s\">hier herunterladen.</a>"),$packagelink)?></td>
		 </tr>
		 <tr>
		 	 <td><a href="<?= PluginEngine::getLinkToAdministrationPlugin() ?>">zurück zur PluginAdministration</a></td>
		 </tr>
		 <?php
		 StudIPTemplateEngine::endContentTable();
	}
	
	function showPluginList($plugins){
		StudIPTemplateEngine::makeHeadline(_("Installierte Plugins"));

		StudIPTemplateEngine::startContentTable();
		
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
		StudIPTemplateEngine::endContentTable();
	}
	
	function showPluginAdministrationList($plugins){
		// default view
		StudIPTemplateEngine::makeHeadline(_("Plugin-Administration"),true);

		StudIPTemplateEngine::startContentTable(true);
		
		$relativepath = $this->pluginref->getPluginpath();
		?>
		  	<form action="<?= PluginEngine::getLink($this->pluginref) ?>" method="post">
		  	<input type="hidden" name="action" value="config" />
			<tr>
				<th align="left"><?= _("Name")?></th>
				<th align="left"><?= _("Typ") ?></th>
				<th align="center"><?= _("Verfügbarkeit") ?></th>
				<th align="left"><?= _("Entfernen") ?></th>
				<th align="right" width="5%"><?= _("Position") ?></th>
				<th align="right"><?= _("Package") ?></th>
			</tr>
		<?php
		
		$absenden = makeButton("absenden","input");
		$lasttype = "";
		foreach($plugins as $plugin){
			if (($plugin->getPluginname() == "PluginAdministration") || ($plugin->getPluginid() == 1)){
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
			<tr>
				<td width="35%" align="left"><a href="<?= PluginEngine::getLink($plugin,array(),"showDescriptionalPage") ?>"><?= $plugin->getPluginname() ?></a>&nbsp;
				<?php
				if (PluginEngine::getTypeOfPlugin($plugin) == "Standard"){
				?>
					<br>
					<a href="<?= PluginEngine::getLink($plugin,array(),"showConfigurationPage") ?>"><?= _("(Default-Aktivierung)")?></a></td>
				<?php
				}
				?>
				<td width="5%" align="left"><?= $type ?></td>
				<td align="center">
					<select name="available_<?= $pluginid?>"> 
						<option <? if ($plugin->isEnabled()) echo ("selected") ?>><?= _("an")?></option>
						<option <? if (!($plugin->isEnabled())) echo ("selected") ?>><?= _("aus")?></option>
					</select>
				</td>
				<td align="left"><a href="<?= PluginEngine::getLink($this->pluginref,array("deinstall" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/x2.gif" border="0" /><?= _("Deinstallieren") ?></a></td>
				<td align="right" width="5%"><input name="navposition_<?= $pluginid?>" type="text" size="5" value="<?= $plugin->getNavigationPosition()?>"></td>
				<td align="right"><a href="<?= PluginEngine::getLink($this->pluginref,array("zip" => $pluginid)) ?>"><img src="<?= $relativepath?>/img/icon-disc2.gif" border="0" /></a></td>
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
		  	 	 <td colspan="7" height="5"></td>
		  	 </tr>
			 </form>
		<?php
		
		StudIPTemplateEngine::createInfoBoxTableCell();
		
		$infobox = array	(	
						array  ("kategorie"  => _("Hinweise:"),
								"eintrag" => array	(	
									array (	"icon" => "pictures/ausruf_small.gif",
													"text"  => _("Verfügbarkeit bedeutet bei Standard-Plugins, dass sie vom Dozenten in Veranstaltungen und Einrichtungen aktiviert werden können. Bei System- und Administrationsplugins wird zwischen Aktivierung und Verfügbarkeit nicht unterschieden.")
									),
									array (	"icon" => "pictures/ausruf_small.gif",
													"text"  => _("Per Default-Aktivierung lassen sich Standard-Plugins automatisch in allen Veranstaltungen einer Einrichtung aktivieren.")
									),
									array (	"icon" => "pictures/ausruf_small.gif",
													"text"  => _("Position gibt die Reihenfolge des Plugins in der Navigation an. <b>Erlaubt sind nur Werte größer 0.</b>")
									)
								)
						)
				);
		print_infobox ($infobox,$relativepath . "/img/modules.jpg");
		StudIPTemplateEngine::endInfoBoxTableCell();
		StudIPTemplateEngine::endContentTable();
		?>
		<p>
		<?php
		StudIPTemplateEngine::makeHeadline(_("Installation neuer Plugins"),true);
		StudIPTemplateEngine::startContentTable(true);
		
		?>
		
		<form action="<?= PluginEngine::getLink($this->pluginref)?>" enctype="multipart/form-data" method="post">
		<input type="hidden" value="install" name="action">
		<input name="upload_file" type="file" size="50" maxlength="100000">
		<input type="image" <?= makeButton("hinzufuegen","image") ?>><br>
		<input type="checkbox" name="update" value="force"><?= _("Aktualisieren, falls Plugin schon vorhanden.")?>
		
		</form>
		<?php
		StudIPTemplateEngine::endContentTable();
	}
}
?>