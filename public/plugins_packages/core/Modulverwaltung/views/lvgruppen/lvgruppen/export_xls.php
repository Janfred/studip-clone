<table>
    <tr>
        <th><?= _('LV-Gruppe') ?></th>
        <th><?= _('Verantw. Einrichtung Modul') ?></th>
        <th><?= _('Abschluss') ?></th>
        <th><?= _('Studiengang') ?></th>
        <th><?= _('Version des Studiengangteils') ?></th>
        <th><?= _('Modul') ?></th>
        <th><?= _('Modulteil') ?></th>
    </tr>
    <? $trail_object_classes = array('Abschluss', 'Studiengang', 'StgteilVersion',
                    'StgteilabschnittModul', 'Modulteil'); ?>
    <? foreach ($lvgruppen as $lvgruppe) : ?>
        <? $trails = $lvgruppe->getTrails(array_reverse($trail_object_classes)); ?>
        <? if (count($trails)) : ?>
            <? foreach ($trails as $trail) : ?>
                <tr>
                    <td><?= htmlReady($lvgruppe->getDisplayName()) ?></td>
                    <? if ($trail['Modul']->responsible_institute) : ?>
                        <td>
                        <?= htmlReady($trail['Modul']->responsible_institute->institute->getDisplayName()) ?>
                        </td>
                    <? else : ?>
                        <td></td>
                    <? endif; ?>
                <? foreach ($trail_object_classes as $trail_object_class) : ?>
                    <? if ($trail[$trail_object_class]) : ?>
                        <td><?= htmlReady($trail[$trail_object_class]->getDisplayName()) ?></td>
                    <? else : ?>
                        <td></td>
                    <? endif; ?>
                <? endforeach; ?>
                </tr>
            <? endforeach; ?>
        <? else : ?>
        <tr>
            <td><?= htmlReady($lvgruppe->getDisplayName()) ?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <? endif; ?>
    <? endforeach; ?>
</table>
