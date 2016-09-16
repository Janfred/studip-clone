<?php
SkipLinks::addIndex(_('Suchformular'), 'search_sem_xts', 100);
// add skip link for simple search here
SkipLinks::addLink(
    _('Schnellsuche'),
    URLHelper::getURL('dispatch.php/search/courses', ['cmd' => 'qs', 'level' => 'f']),
    120
);
?>

<?= $search_obj->getFormStart(URLHelper::getLink('?send=yes')) ?>

<table id="search_sem_xts" border="0" align="center" cellspacing="0" cellpadding="2" width="99%">
    <colgroup>
        <col width="15%">
        <col width="35%">
        <col width="15%">
        <col width="35%">
    </colgroup>
    <tr>
        <td align="right">
            <label for="search_sem_title"><?= _('Titel') ?>:</label>
        </td>
        <td>
            <?= $search_obj->getSearchField('title', [
                    'id' => 'search_sem_title',
                    'style' => 'width:100%;',
            ]) ?>
        </td>
        <td align="right">
            <label for="search_sem_type"><?= _('Typ') ?>:</label>
        </td>
        <td>
            <?= $search_obj->getSearchField('type', ['id' => 'search_sem_type']) ?>
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="search_sem_sub_title"><?= _('Untertitel') ?>:</label>
        </td>
        <td>
            <?= $search_obj->getSearchField('sub_title', [
                    'id' => 'search_sem_sub_title',
                    'style' => 'width:100%',
            ]) ?>
        </td>
        <td align="right">
            <label for="search_sem_sem"><?= _('Semester') ?></label>
        </td>
        <td>
            <?= $search_obj->getSearchField(
                'sem',
                ['id' => 'search_sem_sem'],
                $sem_browse_data['default_sem']
            ) ?>
        </td>
    </tr>
    <tr>
        <td align="right">
            <label for="search_sem_number"><?= _('Nummer') ?>:</label>
        </td>
        <td>
            <?= $search_obj->getSearchField('number', [
                'id' => 'search_sem_number',
                'style' => 'width:100%',
            ]) ?>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td align="right">
            <label for="search_sem_comment"><?=  _('Kommentar') ?></label>
        </td>
        <td>
            <?= $search_obj->getSearchField('comment', [
                    'id' => 'search_sem_comment',
                    'style' => 'width:100%;',
            ]) ?>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
    <tr>
        <td align="right">
            <label for="search_sem_lecturer"><?= _('Lehrende') ?>:</label>
        </td>
        <td>
            <?= $search_obj->getSearchField('lecturer', [
                    'id' => 'search_sem_lecturer',
                    'style' => 'width:100%',
            ]) ?>
        </td>
        <td align="right">
            <label for="search_sem_combination"><?= _('Verknüpfung') ?>:</label>
        </td>
        <td>
            <?= $search_obj->getSearchField('combination', ['id' => 'search_sem_combination']) ?>
        </td>
    </tr>
<? if ($show_class): ?>
    <tr>
        <td align="right">
            <label for="search_sem_scope"><?= _('Bereich') ?>:</label>
        </td>
        <td align="left">
            <?= $search_obj->getSearchField('scope', [
                'id' => 'search_sem_scope',
                'style' => 'width:100%',
            ]) ?>
        </td>
        <td colspan="2">&nbsp;</td>
    </tr>
<? endif; ?>
    <tr>
        <td align="center" colspan="4">
            <?= $search_obj->getSearchButton() ?>
            <?= Studip\LinkButton::createCancel(
                _('Zurücksetzen'),
                URLHelper::getURL('?cmd=xts&level=f&reset_all=1'),
                ['title' => _('Zurücksetzen')]
            ) ?>
        </td>
    </tr>
</table>

<?= $search_obj->getFormEnd() ?>
