<?= $controller->renderMessages() ?>
<table class="default collapsable" style="width: 100%;">
    <caption><?= _('Studieng�nge gruppiert nach Abschl�ssen') ?></caption>
    <colgroup>
        <col>
        <col style="width: 10%;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Abschluss'), 'name') ?>
            <?= $controller->renderSortLink('/index', _('Studieng�nge'), 'count_studiengaenge', array('style' => 'text-align: center;')) ?>
        </tr>
    </thead>
    <? foreach ($abschluesse as $abschluss) : ?>
    <? // skip unknown Abschluesse ?>
    <? if (is_null($abschluss->name)) { continue; } ?>
    <tbody class="<?= ($abschluss->count_studiengaenge ? '' : 'empty') ?> <?= ($abschluss_id == $abschluss->id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row" id="abschluss_<?= $abschluss->id ?>">
            <td class="toggle-indicator">
                <? if (is_null($abschluss->name) && $abschluss->count_studiengaenge) : ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', ['parent_id' => $abschluss->id]) ?>"><?= _('Keinem Abschluss zugeordnet') ?></a>
                <? else : ?>
                    <? if ($abschluss->count_studiengaenge) : ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', ['parent_id' => $abschluss->id]) ?>"><?= htmlReady($abschluss->getDisplayName()) ?> </a>
                    <? else : ?>
                    <?= htmlReady($abschluss->getDisplayName()) ?>
                    <? endif; ?>
                <? endif; ?>
            </td>
            <td style="text-align: center;" class="dont-hide"><?= $abschluss->count_studiengaenge ?></td>
        </tr>
        <? if ($parent_id == $abschluss->id) : ?>
        <tr class="loaded-details nohover">
            <?= $this->render_partial('studiengaenge/studiengaenge/details', compact('parent_id', 'abschluss_id', 'abschluesse')) ?>
        </tr>
        <? endif; ?>
    </tbody>
    <? endforeach; ?>
 </table>