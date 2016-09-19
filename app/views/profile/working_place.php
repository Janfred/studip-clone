<br><b><?= _('Wo ich arbeite:') ?></b><br>

<ul>
    <? foreach ($institutes as $institute): ?>
        <li>
            <a href="<?= URLHelper::getLink('dispatch.php/institute/overview', ['auswahl' => $institute['institut_id']]) ?>">
                <?= htmlReady($institute['institute_name']) ?>
            </a>
            <? if ($institute['raum'] != ''): ?>
                <br>
                <b><?= _('Raum:') ?></b>
                <?= htmlReady($institute['raum']) ?>
            <? endif; ?>

            <? if ($institute['sprechzeiten'] != ''): ?>
                <br>
                <b><?= _('Sprechzeit:') ?></b>
                <?= htmlReady($institute['sprechzeiten']) ?>
            <? endif; ?>

            <? if ($institute['Telefon'] != ''): ?>
                <br>
                <b><?= _('Telefon:') ?></b>
                <?= htmlReady($institute['Telefon']) ?>
            <? endif; ?>

            <? if ($institute['Fax'] != ''): ?>
                <br>
                <b><?= _('Fax:') ?></b>
                <?= htmlReady($institute['Fax']) ?>
            <? endif; ?>

            <? if (!empty($institute['datafield'])): ?>
                <table cellspacing="0" cellpadding="0" border="0">
                    <? foreach ($institute['datafield'] as $datafield): ?>
                        <tr>
                            <td style="padding-right: 5px"><?= htmlReady($datafield['name']) ?>:</td>
                            <td>
                                <?= $datafield['value'] ?>
                                <? if ($datafield['show_star']) echo '*'; ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                </table>
            <? endif; ?>

            <? if (!empty($institute['role'])): ?>
                <table cellpadding="0" cellspacing="0" border="0">
                    <?= $institute['role'] ?>
                </table>
            <? else: ?>
                <br>
            <? endif; ?>
        </li>
    <? endforeach; ?>
</ul>
