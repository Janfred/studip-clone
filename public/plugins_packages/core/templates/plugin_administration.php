<? if ($errorcode): ?>
    <? $message = PluginAdministration::getErrorMessage($errorcode) ?>

    <? if ($errorcode == PLUGIN_INSTALLATION_SUCCESSFUL): ?>
        <? StudIPTemplateEngine::showSuccessMessage($message) ?>
    <? else: ?>
        <? StudIPTemplateEngine::showErrorMessage($message) ?>
    <? endif ?>
<? endif ?>

<? if ($packagelink): ?>
    <? $message = sprintf(_('Das Plugin-Paket wurde erfolgreich erzeugt. Sie können es <a href="%s">hier herunterladen</a>.'), htmlspecialchars($packagelink)) ?>
    <? StudIPTemplateEngine::showSuccessMessage($message) ?>
<? endif ?>

<? if ($delete_plugin): ?>
    <table style="margin-bottom: 5px; width: 100%;">
        <tr>
            <td style="width: 40px;">
                <?= Assets::img('ausruf.gif') ?>
            <td>
                <?= sprintf(_('Wollen Sie wirklich <b>%s</b> deinstallieren?'), htmlspecialchars($delete_plugin['name'])) ?>
                <br>
                <a href="<?= PluginEngine::getLink($admin_plugin, array('deinstall' => $delete_plugin['id'], 'forcedeinstall' => true)) ?>">
                    <?= makeButton('ja2') ?>
                </a>
                &nbsp;
                <a href="<?= PluginEngine::getLink($admin_plugin) ?>">
                    <?= makeButton('nein') ?>
                </a>
            </td>
        </tr>
    </table>
<? endif ?>

<form action="<?= PluginEngine::getLink($admin_plugin) ?>" method="post">
    <table style="width: 100%;" cellspacing="0">
        <tr>
            <th style="text-align: left; width: 35%;"><?= _('Name')?></th>
            <th style="text-align: left; width: 15%;"><?= _('Typ') ?></th>
            <th style="text-align: left; width: 15%;"><?= _('Verfügbarkeit') ?></th>
            <th style="text-align: left; width: 15%;"><?= _('Position') ?></th>
            <th style="text-align: left; width: 15%;"><?= _('Zugriffsrechte') ?></th>
            <th style="text-align: left; width:  5%;"><?= _('Aktionen') ?></th>
        </tr>

        <? foreach ($plugins as $plugin): ?>
            <? $pluginid = $plugin['id'] ?>
            <? if ($plugin['class'] != 'PluginAdministrationPlugin'): ?>
                <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>" style="height: 25px;">
                    <td style="padding-left: 1ex;">
                        <a href="<?= PluginEngine::getLink($admin_plugin, array(), 'manifest/'.$plugin['class']) ?>">
                            <?= htmlspecialchars($plugin['name']) ?>
                        </a>
                        <? if (in_array('StandardPlugin', $plugin['type'])): ?>
                            <a href="<?= PluginEngine::getLink($admin_plugin, array(), 'defaultActivation/'.$plugin['class']) ?>">
                                <?= _('(Default-Aktivierung)') ?>
                            </a>
                        <? endif ?>
                    <td>
                        <?= join(', ', $plugin['type']) ?>
                    </td>
                    <td>
                        <select name="available_<?= $pluginid ?>">
                            <option value="0" <?= $plugin['enabled'] ? '' : 'selected' ?>><?= _('aus') ?></option>
                            <option value="1" <?= $plugin['enabled'] ? 'selected' : '' ?>><?= _('an') ?></option>
                        </select>
                    </td>
                    <td>
                        <input name="navposition_<?= $pluginid ?>" type="text" size="2" value="<?= $plugin['position'] ?>">
                    </td>
                    <td>
                        <? if ($roleplugin): ?>
                            <a href="<?= PluginEngine::getLink($roleplugin, array('pluginid' => $pluginid), 'doPluginRoleAssignment') ?>">
                                <?= makeButton('bearbeiten', 'img' , _('Rollenberechtigungen bearbeiten')) ?>
                            </a>
                        <? endif ?>
                    </td>
                    <td style="text-align: center;">
                        <? if (!$plugin['depends']): ?>
                            <a href="<?= PluginEngine::getLink($admin_plugin, array('zip' => $pluginid)) ?>">
                                <img src="<?= $admin_plugin->getPluginURL() ?>/img/icon-disc.gif" title="<?= _('Herunterladen') ?>">
                            </a>
                        <? endif ?>
                        &nbsp;
                        <? if (!$plugin['depends']): ?>
                            <a href="<?= PluginEngine::getLink($admin_plugin, array('deinstall' => $pluginid)) ?>">
                                <img src="<?= $admin_plugin->getPluginURL() ?>/img/trash.gif" title="<?= _('Deinstallieren') ?>">
                            </a>
                        <? endif ?>
                    </td>
                </tr>
            <? endif ?>
        <? endforeach ?>

        <tr style="height: 5px;">
            <td colspan="6"></td>
        </tr>
        <tr>
            <td style="text-align: center;" colspan="6">
                <?= makeButton('speichern', 'input', _('Einstellungen speichern'), 'save') ?>
            </td>
        </tr>
        <tr style="height: 10px;">
            <td colspan="6"></td>
        </tr>
    </table>
</form>

<?= $this->render_partial('installation_form') ?>

<?
$infobox_content = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Verfügbarkeit bedeutet bei Standard-Plugins, dass sie vom Dozenten in Veranstaltungen und Einrichtungen aktiviert werden können. Bei System- und Administrationsplugins wird zwischen Aktivierung und Verfügbarkeit nicht unterschieden.')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Per Default-Aktivierung lassen sich Standard-Plugins automatisch in allen Veranstaltungen einer Einrichtung aktivieren.')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Position gibt die Reihenfolge des Plugins in der Navigation an. Erlaubt sind nur Werte größer 0.')
            )
        )
    ), array(
        'kategorie' => _('Aktionen:'),
        'eintrag'   => array(
            array(
                'icon' => 'link_intern.gif',
                'text' => '<a href="'.PluginEngine::getLink($admin_plugin, array(), 'showUpdates').'">'._('Installierte Plugins aktualisieren').'</a>'
            )
        )
    )
);

$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
