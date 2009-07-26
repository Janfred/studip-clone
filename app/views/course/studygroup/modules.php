<table>

<? if ($GLOBALS['PLUGINS_ENABLE'])
{
    $cssSw=new cssClassSwitcher;
    $admin_modules_plugins = PluginEngine::getPlugins('Standard');
    
	foreach ($admin_modules_plugins as $plugin)
	{
		?>
		<tr><? $cssSw->switchClass() ?>
			<td class="<?= $cssSw->getClass() ?>"  width="15%" align="left">
				<font size=-1><b><?=$plugin->getPluginname()?></b><br /></font>
			</td>
			<td class="<?= $cssSw->getClass() ?>" width="15%">
				<input type="RADIO" name="plugin_<?=$plugin->getPluginid()?>" value="TRUE" <?= $plugin->isActivated() ? "checked" : "" ?>>
				<!-- mark old state -->
				<font size=-1><?=_("an")?></font>
				<input type="RADIO" name="plugin_<?=$plugin->getPluginid()?>" value="FALSE" <?= $plugin->isActivated() ? "" : "checked" ?>>
				<font size=-1><?=_("aus")?><br /></font>
			</td>
			<td class="<?= $cssSw->getClass() ?>" width="70%">
				<font size=-1><?
				$admininfo = $plugin->getPluginAdminInfo();
				if (!is_null($admininfo)){
					if ($plugin->isActivated()) {
						if (!method_exists($plugin, 'getPluginExistingItems') ||
						    $plugin->getPluginExistingItems($admin_modules_data['range_id'])) {
							print ('<font color="red">'.$admininfo->getMsg_pre_warning().'</font>');
						}
						else {
							print ($admininfo->getMsg_deactivate());
						}
					}
					else {
						print ($admininfo->getMsg_activate());
					}
				}
				else {
					// kein AdminInfo vorhanden, also nichts ausgeben
					print ("Dieses Plugin hat keinen Hilfetext bereitgestellt.");
				}
				?></font>
			</td>
		</tr>
		<?php
	}
}?>
</table>