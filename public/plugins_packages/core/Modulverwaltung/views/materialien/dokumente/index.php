<?= $this->controller->renderMessages() ?>
<? if(sizeof($dokumente)) : ?>
<table class="default collapsable">
    <caption>
        <?= _('Verlinkte Materialien/Dokumente') ?>
        <span class="actions"><? printf(ngettext('%s Dokument', '%s Dokumente', $count), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 40%">
        <col style="width: 5%">
        <col span="2" style="width: 1%">
    </colgroup>
    <thead>
        <tr>
            <?= $controller->renderSortLink('materialien/dokumente/', _('Name'), 'name') ?>
            <?= $controller->renderSortLink('materialien/dokumente/', _('Linktext'), 'linktext') ?>
            <?= $controller->renderSortLInk('materialien/dokumente/', _('Geändert am'), 'chdate', ['style' => 'white-space: nowrap;']) ?>
            <?= $controller->renderSortLink('materialien/dokumente/', _('Referenzierungen'), 'count_zuordnungen', ['style' => 'text-align: center;']) ?>
        <th> </th>
        </tr>
    </thead>
    <? foreach ($dokumente as $dokument): ?>
    <? $perm = MvvPerm::get($dokument) ?>
    <tbody class="<?= ($dokument_id == $dokument->id ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="header-row">
        <td class="toggle-indicator">
            <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $dokument->id) ?>"><?= htmlReady($dokument->name) ?></a>
        </td>
        <td class="dont-hide">
            <?= htmlReady($dokument->linktext) ?>
        </td>
        <td class="dont-hide">
            <?= strftime('%x, %X', $dokument->chdate) ?>
        </td>
        <td style="text-align: center;" class="dont-hide">
            <?= $dokument->count_zuordnungen ?>
        </td>
        <td style="white-space: nowrap;" class="dont-hide actions">
            <? if ($perm->havePermWrite()) : ?>
            <a href="<?= $controller->url_for('/dokument', $dokument->id) ?>">
                <?= Icon::create('edit', 'clickable', ['title' => _('Dokument bearbeiten')])->asImg() ?>
            </a>
            <? endif; ?>
            <? if ($perm->havePermCreate()) : ?>
            <a href="<?= $controller->url_for('/delete', $dokument->id) ?>">
                <?= Icon::create('trash', 'clickable', ['title' => _('Dokument löschen')])->asImg(); ?>
            </a>
            <? endif; ?>
        </td>
    </tr>
    <? if ($dokument_id == $dokument->getId()) : ?>
    <tr class="loaded-details nohover">
        <?= $this->render_partial('materialien/dokumente/details', compact('dokument')) ?>
    </tr>
    <? endif; ?>
    </tbody>
    <? endforeach ?>
</table>
<? if ($count > MVVController::$items_per_page) : ?>
<div style="width: 100%; text-align: center; margin-top: 15px;">
<?
    $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
    $pagination->clear_attributes();
    $pagination->set_attribute('perPage', MVVController::$items_per_page);
    $pagination->set_attribute('num_postings', $count);
    $pagination->set_attribute('page', $page);
    // ARGH!
    $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_dokumente=%s';
    $pagination->set_attribute('pagelink', $page_link);
    echo $pagination->render("shared/pagechooser");
?>
<? endif; ?>
<? endif; ?>
