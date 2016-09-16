<?php
SkipLinks::addIndex(_('Suchformular'), 'search_sem_qs', 100);
?>

<?= $search_obj->getFormStart(URLHelper::getLink()) ?>

<table id="search_sem_qs" border="0" align="center" cellspacing="0" cellpadding="2" width="99%">
    <tr>
        <td align="center">
            <label for="search_sem_qs_choose"><?= _('Schnellsuche') ?>:</label>
            <?= $search_obj->getSearchField('qs_choose', [
                'id' => 'search_sem_qs_choose'
            ]) ?>

        <? if ($sem_browse_data['level'] === 'vv'): ?>
            <label for="search_sem_scope_choose"><?= _('in') ?>:</label>
            <?= $search_obj->getSearchField('scope_choose', [
                'id' => 'search_sem_scope_choose'
            ] ,$sem_tree->start_item_id) ?>
                <input type="hidden" name="level" value="vv">
        <? endif; ?>

        <? if ($sem_browse_data['level'] === 'ev'): ?>
            <label for="search_sem_range_choose"><?=  _('in') ?>:</label>
            <?= $search_obj->getSearchField('range_choose', [
                'id' => 'search_sem_range_choose',
            ], $range_tree->start_item_id) ?>
            <input type="hidden" name="level" value="ev">
        <? endif; ?>

            <label for="search_sem_sem"><?= _('Semester') ?>:</label>
            <?= $search_obj->getSearchField('sem', [
                'id' => 'search_sem_sem',
            ], $sem_browse_data['default_sem']) ?>

            <?= $search_obj->getSemChangeButton() ?>
        </td>
    </tr>
    <tr>
        <td align="center">
            <?= $quicksearch->setInputStyle('width: 50%')->render() ?>
            &nbsp;
            <?= $search_obj->getSearchButton([
                'style' => 'vertical-align:middle',
                'class' => 'quicksearchbutton',
            ], true) ?>
            <?= Studip\LinkButton::createCancel(
                    _('Zurücksetzen'),
                    URLHelper::getURL('?reset_all=1'),
                    ['title' => _('Zurücksetzen')]
            ) ?>
        </td>
    </tr>
</table>

<?= $search_obj->getFormEnd() ?>
