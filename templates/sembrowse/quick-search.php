<?php
SkipLinks::addIndex(_('Suchformular'), 'search_sem_qs', 100);
?>

<?= $search_obj->getFormStart(URLHelper::getLink(), [ 'class' => 'default' ]) ?>

<fieldset>
    <legend class="hidden-small-down">
        <?= _("Filter") ?>
    </legend>

    <label>
        <?= _('Suchbegriff') ?>
        <?= $quicksearch->render() ?>
    </label>

    <label>
        <?= _('Schnellsuche') ?>
        <?= $search_obj->getSearchField('qs_choose', [ 'id' => 'search_sem_qs_choose' ]) ?>
    </label>

    <? if ($sem_browse_data['level'] === 'vv'): ?>
        <label>
            <?= _('in') ?>
            <?= $search_obj->getSearchField('scope_choose', [ 'id' => 'search_sem_scope_choose' ] ,$sem_tree->start_item_id) ?>
            <input type="hidden" name="level" value="vv">
        </label>
    <? endif; ?>

    <? if ($sem_browse_data['level'] === 'ev'): ?>
        <label>
            <?=  _('in') ?>:
            <?= $search_obj->getSearchField('range_choose', [ 'id' => 'search_sem_range_choose', ], $range_tree->start_item_id) ?>
            <input type="hidden" name="level" value="ev">
        </label>
    <? endif; ?>

    <label class="search_sem_label">
        <?= _('Semester') ?>

        <div class="hgroup-btn">
            <?= $search_obj->getSearchField(
                'sem',
                [ 'id' => 'search_sem_sem', 'class' => 'form-control' ],
                $sem_browse_data['default_sem']) ?>
        </div>
    </label>

</fieldset>

<footer>
    <span class="button-group">
        <?= $search_obj->getSearchButton(
            [
                'style' => 'vertical-align:middle',
                'class' => 'quicksearchbutton',
            ],
            true
        ) ?>
        <?= Studip\LinkButton::create(
            _('Zurücksetzen'),
            URLHelper::getURL('?reset_all=1'),
            ['title' => _('Zurücksetzen')]
        ) ?>
    </span>
</footer>

<?= $search_obj->getFormEnd() ?>
