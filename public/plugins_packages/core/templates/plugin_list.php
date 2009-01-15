<table style="width: 100%;" cellspacing="0">
    <tr>
        <th style="text-align: left; width: 35%;"><?= _('Name')?></th>
        <th style="text-align: left; width: 20%;"><?= _('Typ') ?></th>
        <th style="text-align: left; width: 20%;"><?= _('Verfügbarkeit') ?></th>
        <th style="text-align: left; width: 25%;"><?= _('Position') ?></th>
    </tr>

    <? foreach ($plugins as $plugin): ?>
        <? if (!$plugin instanceof PluginAdministrationPlugin): ?>
            <? if (($type = PluginEngine::getTypeOfPlugin($plugin)) != $lasttype): ?>
                <? $lasttype = $type ?>
                <tr style="height: 10px;">
                    <td colspan="4"></td>
                </tr>
            <? endif ?>
            <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>" style="height: 25px;">
                <td style="padding-left: 1ex;">
                    <?= htmlspecialchars($plugin->getPluginname()) ?>
                <td>
                    <?= $type ?>
                </td>
                <td>
                    <img src="<?= $admin_plugin->getPluginURL() ?>/img/haken.gif"><?= _('Aktiviert') ?>
                </td>
                <td>
                    <?= $plugin->getNavigationPosition() ?>
                </td>
            </tr>
        <? endif ?>
    <? endforeach ?>
</table>

<?
$infobox = array(
    array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(
            array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Verfügbarkeit bedeutet bei Standard-Plugins, dass sie vom Dozenten in Veranstaltungen und Einrichtungen aktiviert werden können. Bei System- und Administrationsplugins wird zwischen Aktivierung und Verfügbarkeit nicht unterschieden.')
            ), array(
                'icon' => 'ausruf_small.gif',
                'text' => _('Position gibt die Reihenfolge des Plugins in der Navigation an. Erlaubt sind nur Werte größer 0.')
            )
        )
    )
);

StudIPTemplateEngine::createInfoBoxTableCell();
print_infobox($infobox, 'modules.jpg');
StudIPTemplateEngine::endInfoBoxTableCell();
?>
