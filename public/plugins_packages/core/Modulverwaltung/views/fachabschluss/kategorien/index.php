<?= $this->controller->jsUrl() ?>
<?= $this->controller->renderMessages() ?>
<table id="abschluss_kategorien" class="default sortable collapsable">
    <caption><?= _('Abschluss-Kategorien mit verwendeten Abschlüssen') ?></caption>
    <colgroup>
        <col>
        <col style="width: 20%;">
        <col style="width: 20%;">
        <col style="width: 1%;">
    <thead>
        <tr>
            <th>
                <?=  _('Name') ?>
            </th>
            <th style="text-align: center;">
                <?= _('Abschlüsse') ?>
            </th>
            <th style="text-align: center;">
                <?= _('Materialien') ?>
            </th>
            <th colspan="2"> </th>
        </tr>
    </thead>
    <? foreach ($abschluss_kategorien as $kategorie) : ?>
        <? $perm = MvvPerm::get($kategorie) ?>
        <? $abschluesse = $kategorie->abschluesse; ?>
        <tbody id="<?= $kategorie->id ?>" class="<?= count($abschluesse) ? '' : 'empty' ?> collapsed<?= $perm->haveFieldPerm('position') ? ' sort_items' : '' ?>">
        <tr class="header-row">
            <td class="toggle-indicator">
                <? if (count($abschluesse) < 1): ?>
                    <?= htmlReady($kategorie->name) ?>
                <? else: ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $kategorie->id) ?>"><?= htmlReady($kategorie->name) ?> </a>
                <? endif; ?>
            </td>
            <td class="dont-hide" style="text-align: center;">
                <?= $kategorie->count_abschluesse ?>
            </td>
            <td class="dont-hide" style="text-align: center;">
                <?= $kategorie->count_dokumente ?>
            </td>
            <td style="white-space: nowrap;" class="dont-hide actions">
            <? if ($perm->havePermWrite()) : ?>
                <a href="<?= $controller->url_for('/kategorie', $kategorie->id) ?>">
                    <?= Icon::create('edit', 'clickable', array('title' => _('Abschluss-Kategorie bearbeiten')))->asImg(); ?>
                </a>
            <? endif; ?>
            <? if ($perm->havePermCreate()) : ?>
                <? if (count($abschluesse) < 1) : ?>
                <a href="<?= $controller->url_for('/delete',  $kategorie->id) ?>">
                    <?= Icon::create('trash', 'clickable', array('title' => _('Abschluss-Kategorie löschen')))->asImg(); ?>
                </a>
                <? else : ?>
                    <?= Icon::create('trash', 'inactive', array('title' => _('Löschen nicht möglich')))->asImg(); ?>
                <? endif; ?>
            <? endif; ?>
            </td>
        </tr>
       <? if ($kategorie_id == $kategorie->id) : ?>
            <?= $this->render_partial('fachabschluss/kategorien/details', compact('kategorie')) ?>
        <? endif; ?>
        </tbody>
    <? endforeach; ?>
 </table>
