<?= $controller->jsUrl() ?>
<?= $controller->renderMessages() ?>
<table class="default collapsable">
    <caption>
        <?= _('Liste der Studieng�nge') ?>
        <span class="actions"><? printf(_('%s Studieng�nge'), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 15%;">
        <col style="width: 15%;">
        <col style="width: 8%; white-space: nowrap;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('studiengaenge/studiengaenge/index/', _('Studiengang'), 'name') ?>
            <?= $controller->renderSortLink('studiengaenge/studiengaenge/index/', _('Einrichtung'), 'institut_name') ?>
            <?= $controller->renderSortLink('studiengaenge/studiengaenge/index/', _('Kategorie'), 'kategorie_name') ?>
            <th> </th>
        </tr>
    </thead>
    <?= $this->render_partial('studiengaenge/studiengaenge/studiengaenge') ?>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align: right;">
            <? if ($count > MVVController::$items_per_page) : ?>
            <?
                $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                $pagination->clear_attributes();
                $pagination->set_attribute('perPage', MVVController::$items_per_page);
                $pagination->set_attribute('num_postings', $count);
                $pagination->set_attribute('page', $page);
                $page_link = reset(explode('?', $controller->url_for('/index'))) . '?page_studiengaenge=%s';
                $pagination->set_attribute('pagelink', $page_link);
                echo $pagination->render('shared/pagechooser');
            ?>
            <? endif; ?>
            </td>
        </tr>
    </tfoot>
</table>