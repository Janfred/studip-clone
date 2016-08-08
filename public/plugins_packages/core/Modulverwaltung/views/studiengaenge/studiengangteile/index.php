<?= $controller->renderMessages() ?>
<?= $controller->jsUrl() ?>
<table class="default collapsable">
    <caption>
        <?= _('Liste der Studiengangteile') ?>
        <span class="actions"><? printf(_('%s Studiengangteile'), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 40%;">
        <col span="3" style="width: 1%">
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Fach'), 'fach_name,zusatz,kp') ?>
            <?= $controller->renderSortLink('/index', _('Zweck'), 'zusatz,fach_name,kp') ?>
            <?= $controller->renderSortLink('/index', _('CP'), 'kp,fach_name,zusatz', ['style' => 'text-align: center;']) ?>
            <?= $controller->renderSortLink('/index', _('Versionen'), 'count_versionen,fach_name,kp', ['style' => 'text-align: center;']) ?>
            <th> </th>
        </tr>
    </thead>
    <? foreach ($stgteile as $stgteil): ?>
    <tbody class="<?= $stgteil->count_versionen ? '' : 'empty' ?>  <?= ($stgteil_id == $stgteil->getId() ? 'not-collapsed' : 'collapsed') ?>">
    <tr class="header-row <?= TextHelper::cycle('table_row_even', 'table_row_odd') ?>">
        <td class="toggle-indicator">
            <? if ($stgteil->count_versionen) : ?>
            <a class="mvv-load-in-new-row" href="<?= $controller->url_for('/details', $stgteil->getId()) ?>">
                <?= htmlReady($stgteil->fach_name) ?>
                <? if ($stgteil->count_fachberater) : ?>
                    <?= Icon::create('community', 'info', array('title' => sprintf(ngettext('%s Fachberater zugeordnet', '%s Fachberater zugeordnet', $stgteil->count_fachberater), $stgteil->count_fachberater)))->asImg(); ?>
                <? endif; ?>
            </a>
            <? else : ?>
            <?= htmlReady($stgteil->fach_name) ?>
            <? if ($stgteil->count_fachberater) : ?>
                <?= Icon::create('community', 'info', array('title' => sprintf(ngettext('%s Fachberater zugeordnet', '%s Fachberater zugeordnet', $stgteil->count_fachberater), $stgteil->count_fachberater)))->asImg(); ?>
            <? endif; ?>
            <? endif; ?>
        </td>
        <td class="dont-hide"><?= htmlReady($stgteil->zusatz) ?> </td>
        <td class="dont-hide" style="text-align: center;"><?= htmlReady($stgteil->kp) ?> </td>
        <td class="dont-hide" style="text-align: center;"><?= $stgteil->count_versionen ?> </td>
        <td class="dont-hide actions" style="white-space: nowrap;">
        <? if (MvvPerm::havePermCreate('StgteilVersion')) : ?>
            <a href="<?= $controller->url_for('/version', $stgteil->getId()) ?>">
                <?= Icon::create('file+add', 'clickable', array('title' => _('Neue Version anlegen')))->asImg(); ?>
            </a>
        <? endif; ?>
        <? if (MvvPerm::havePermWrite($stgteil)) : ?>
            <a href="<?= $controller->url_for('/stgteil', $stgteil->getId()) ?>">
                <?= Icon::create('edit', 'clickable', array('title' => _('Studiengangteil bearbeiten')))->asImg(); ?>
            </a>
        <? endif; ?>
        <? if (MvvPerm::havePermCreate('StudiengangTeil')) : ?>
            <a href="<?= $controller->url_for('/copy', $stgteil->getId()) ?>">
                <?= Icon::create('files', 'clickable', array('title' => _('Studiengangteil kopieren')))->asImg(); ?>
            </a>
        <? endif; ?>
        <? if (MvvPerm::havePermCreate($stgteil)) : ?>
            <a href="<?= $controller->url_for('/delete', $stgteil->getId()) ?>">
                <?= Icon::create('trash', 'clickable', array('title' => _('Studiengangteil löschen')))->asImg(); ?>
            </a>
        <? endif; ?>
        </td>
    </tr>
    <? if ($stgteil_id == $stgteil->getId()) : ?>
    <? $versionen = StgteilVersion::findByStgteil($stgteil->getId()); ?>
    <tr class="loaded-details nohover">
        <?= $this->render_partial('studiengaenge/studiengangteile/details', compact('stgteil_id', 'versionen')) ?>
    </tr>
    <? endif; ?>
    </tbody>
    <? endforeach ?>
    <? if ($count > MVVController::$items_per_page) : ?>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align: right;">
            <?
                $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                $pagination->clear_attributes();
                $pagination->set_attribute('perPage', MVVController::$items_per_page);
                $pagination->set_attribute('num_postings', $count);
                $pagination->set_attribute('page', $page);
                $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_studiengangteile=%s';
                $pagination->set_attribute('pagelink', $page_link);
                echo $pagination->render("shared/pagechooser");
            ?>
            </td>
        </tr>
    </tfoot>
    <? endif; ?>
</table>
