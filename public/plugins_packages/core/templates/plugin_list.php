<table style="width: 100%;" cellspacing="0">
    <tr>
        <th style="text-align: left; width: 35%;"><?= _('Name')?></th>
        <th style="text-align: left; width: 20%;"><?= _('Typ') ?></th>
        <th style="text-align: left; width: 20%;"><?= _('Verfügbarkeit') ?></th>
        <th style="text-align: left; width: 25%;"><?= _('Position') ?></th>
    </tr>

    <? foreach ($plugins as $plugin): ?>
        <? if ($plugin['class'] != 'PluginAdministrationPlugin'): ?>
            <tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>" style="height: 25px;">
                <td style="padding-left: 1ex;">
                    <?= htmlspecialchars($plugin['name']) ?>
                <td>
                    <?= join(', ', $plugin['type']) ?>
                </td>
                <td>
                    <? if ($plugin['enabled']): ?>
                      <?= Assets::img('haken_transparent.gif') ?><?= _('aktiviert') ?>
                    <? else: ?>
                      <?= Assets::img('x_transparent.gif') ?><?= _('deaktiviert') ?>
                    <? endif ?>
                </td>
                <td>
                    <?= $plugin['position'] ?>
                </td>
            </tr>
        <? endif ?>
    <? endforeach ?>
</table>

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
                'text' => _('Position gibt die Reihenfolge des Plugins in der Navigation an. Erlaubt sind nur Werte größer 0.')
            )
        )
    )
);

$infobox = array('picture' => 'modules.jpg', 'content' => $infobox_content);
?>
