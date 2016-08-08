<?= $controller->jsUrl() ?>
<?= $controller->renderMessages() ?>
<table id="stgteilbezeichnungen" class="default sortable collapsable">
    <caption>
        <?= _('Studiengangteil-Bezeichnungen') ?>
        <span class="actions"><? printf(_('%s Bezeichnungen'), count($stgteilbezeichnungen)) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 10%;">
        <col style="width: 1%;">
        <col style="width: 10%;">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Name') ?></th>
            <th><?= _('Kurzname') ?></th>
            <th style="text-align: center;"><?= _('Studiengänge') ?></th>
            <th> </th>
        </tr>
    </thead>
    <? foreach ($stgteilbezeichnungen as $stgteilbezeichnung) : ?>
    <? $perm = MvvPerm::get($stgteilbezeichnung) ?>
    <tbody id="<?= $stgteilbezeichnung->id ?>" class="collapsed<?= $perm->haveFieldPerm('position') ? ' sort_items' : '' ?>">
    <tr class="header-row">
        <td class="toggle-indicator">
            <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $stgteilbezeichnung->id) ?>"><?= htmlReady($stgteilbezeichnung->name) ?> </a>
        </td>
        <td class="dont-hide">
            <?= htmlReady($stgteilbezeichnung->name_kurz) ?>
        </td>
        <td style="text-align: center;" class="dont-hide">
            <?= $stgteilbezeichnung->count_studiengaenge ?>
        </td>
        <td class="dont-hide actions">
        <? if ($perm->havePermWrite()) : ?>
            <a data-dialog href="<?= $controller->url_for('/stgteilbezeichnung/', $stgteilbezeichnung->id) ?>">
                <?= Icon::create('edit', 'clickable', array('title' => _('Studiengangteil-Bezeichnung bearbeiten')))->asImg(); ?>
            </a>
        <? endif; ?>
        <? if ($perm->havePermCreate()) : ?>
            <? if ($stgteilbezeichnung->count_studiengangteile < 1) : ?>
            <a href="<?= $controller->url_for('/delete', $stgteilbezeichnung->id) ?>">
                <?= Icon::create('trash', 'clickable', array('title' => _('Studiengangteil-Bezeichnung löschen')))->asImg(); ?>
            </a>
            <? endif; ?>
        <? endif; ?>
        </td>
    </tr>
   <? if ($bezeichnung_id == $stgteilbezeichnung->getId()) : ?>
        <?= $this->render_partial('studiengaenge/stgteilbezeichnungen/details', compact('stgteilbezeichnung')) ?>
    <? endif; ?>
    </tbody>
    <? endforeach; ?>
 </table>
