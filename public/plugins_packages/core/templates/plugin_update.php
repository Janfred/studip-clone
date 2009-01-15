<form action="<?= PluginEngine::getLink($admin_plugin, array(), 'installUpdates') ?>" method="post">
    <table style="width: 100%;" cellspacing="0">
        <tr>
            <th style="text-align: left; width: 25%;"><?= _('Name')?></th>
            <th style="text-align: left; width: 15%;"><?= _('Typ') ?></th>
            <th style="text-align: left; width: 15%;"><?= _('installierte Version') ?></th>
            <th style="text-align: left; width: 15%;"><?= _('verfügbare Version') ?></th>
            <th style="text-align: left; width: 30%;"><?= _('aktualisieren') ?></th>
        </tr>

        <? foreach ($plugins as $plugin): ?>
            <? $pluginid = $plugin->getPluginid() ?>
            <? if (!$plugin instanceof PluginAdministrationPlugin): ?>
                <? if (($type = PluginEngine::getTypeOfPlugin($plugin)) != $lasttype): ?>
                    <? $lasttype = $type ?>
                    <tr style="height: 10px;">
                        <td colspan="5"></td>
                    </tr>
                <? endif ?>
                <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>" style="height: 25px;">
                    <td style="padding-left: 1ex;">
                        <a href="<?= PluginEngine::getLink($admin_plugin, array(), 'manifest/'.$plugin->getPluginclassname()) ?>">
                            <?= htmlspecialchars($plugin->getPluginname()) ?>
                        </a>
                    <td>
                        <?= $type ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($update_info[$pluginid]['version']) ?>
                    </td>
                    <td>
                        <? if (isset($update_info[$pluginid]['update'])): ?>
                            <?= htmlspecialchars($update_info[$pluginid]['update']['version']) ?>
                        <? endif ?>
                    </td>
                    <td>
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
                        <? elseif (isset($update_info[$pluginid]['update']) && !$plugin->isDependentOnOtherPlugin()): ?>
                            <label>
                                <input type="checkbox" name="update[]" value="<?= $pluginid ?>" checked>
                                <?= _('Update installieren') ?>
                            </label>
                        <? endif ?>
                    </td>
                </tr>
            <? endif ?>
        <? endforeach ?>

        <tr style="height: 5px;">
            <td colspan="5"></td>
        </tr>
        <tr>
            <td style="text-align: center;" colspan="5">
                <?= makeButton('starten', 'input', _('Aktualisierung starten')) ?>
                &nbsp;
                <a href="<?= PluginEngine::getLink($admin_plugin) ?>">
                    <?= makeButton('abbrechen') ?>
                </a>
            </td>
        </tr>
        <tr style="height: 10px;">
            <td colspan="5"></td>
        </tr>
    </table>
</form>

<?= $this->render_partial('installation_form') ?>

<?
$infobox = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Wählen Sie in der Liste aus, welche Plugins aktualisiert werden sollen und klicken Sie auf "starten".')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Die automatische Aktualisierung wird nicht von allen Plugins unterstützt.')
            )
        )
    ), array(
        'kategorie' => _('Aktionen:'),
        'eintrag'   => array(
            array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.PluginEngine::getLink($admin_plugin).'">'._('Verwaltung von Plugins').'</a>'
            )
        )
    )
);

StudIPTemplateEngine::createInfoBoxTableCell();
print_infobox($infobox, 'modules.jpg');
StudIPTemplateEngine::endInfoBoxTableCell();
?>
