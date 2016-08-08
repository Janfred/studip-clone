<?= $this->controller->renderMessages() ?>
<table class="default collapsable">
    <caption>
        <?= _('Lehrveranstaltungsgruppen'); ?>
        <span class="actions"><? printf(ngettext('%s LV-Gruppe', '%s LV-Gruppen', $count), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col span="4" style="width: 1%;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Name'), 'name') ?>
            <?= $controller->renderSortLink('/index', _('Veranstaltungen'), 'count_seminare', array('style' => 'text-align: center;')) ?>
            <?= $controller->renderSortLink('/index', _('Archiv'), 'count_archiv', array('style' => 'text-align: center;')) ?>
            <?= $controller->renderSortLink('/index', _('Modulteile'), 'count_modulteile', array('style' => 'text-align: center;')) ?>
            <th> </th>
        </tr>
    </thead>
    <? foreach ($lvgruppen as $lvgruppe): ?>
    <tbody class="<?= $lvgruppe->count_seminare || true ? '' : 'empty' ?>  <?= ($lvgruppe_id ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="header-row <?= TextHelper::cycle('table_row_even', 'table_row_odd') ?>">
        <td class="toggle-indicator">
            <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $lvgruppe->id) ?>">
                <?= htmlReady($lvgruppe->getDisplayName()) ?>
            </a>
        </td>
        <td style="text-align: center;" class="dont-hide"><?= $lvgruppe->count_seminare ?> </td>
        <td style="text-align: center;" class="dont-hide"><?= $lvgruppe->count_archiv ?> </td>
        <td style="text-align: center;" class="dont-hide"><?= $lvgruppe->count_modulteile ?> </td>
        <td class="dont-hide actions" style="white-space: nowrap;">
            <? if (MvvPerm::get($lvgruppe)->havePermWrite()) : ?>
            <a data-dialog href="<?= $controller->url_for('/lvgruppe', $lvgruppe->id) ?>">
                <?= Icon::create('edit', 'clickable', array('title' => _('Lehrveranstaltungsgruppe bearbeiten')))->asImg(); ?>
            </a>
            <a data-dialog href="<?= $controller->url_for('shared/log_event/show/', $lvgruppe->id) ?>">
                <?= Icon::create('log', 'clickable', array('title' => _('Log-Einträge dieser Lehrveranstaltungsgruppe')))->asImg(); ?>
            </a>
            <? endif; ?>
            <? if (MvvPerm::get($lvgruppe)->havePermCreate()) : ?>
                <? if ($lvgruppe->count_semester == 0 && $lvgruppe->count_modulteile == 0): ?>
                <a href="<?= $controller->url_for('/delete', $lvgruppe->id) ?>">
                    <?= Icon::create('trash', 'clickable', array('title' => _('Lehrveranstaltungsgruppe löschen')))->asImg(); ?>
                </a>
                <? else: ?>
                    <?= Icon::create('trash', 'inactive', array('title' => _('Löschen nicht möglich')))->asImg(); ?>
                <? endif; ?>
            <? endif; ?>
        </td>
    </tr>
    <? if ($lvgruppe_id == $lvgruppe->id) : ?>
    <tr class="loaded-details nohover">
        <?= $this->render_partial('lvgruppen/lvgruppen/details', compact('lvgruppe')) ?>
    </tr>
    <? endif; ?>
    </tbody>
    <? endforeach ?>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: right;">
            <? if ($count > MVVController::$items_per_page) : ?>
            <?
                $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                $pagination->clear_attributes();
                $pagination->set_attribute('perPage', MVVController::$items_per_page);
                $pagination->set_attribute('num_postings', $count);
                $pagination->set_attribute('page', $page);
                $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_lvgruppen=%s';
                $pagination->set_attribute('pagelink', $page_link);
                echo $pagination->render("shared/pagechooser");
            ?>
            <? endif; ?>
            </td>
        </tr>
    <tfoot>
</table>