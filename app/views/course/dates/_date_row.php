<?php
$icon = $date->chdate > $last_visitdate ? 'date+new' : 'date';
$dialog_url = $show_raumzeit
            ? $controller->url_for('course/dates/details/' . $date->id)
            : $controller->url_for('course/dates/singledate/' . $date->id);
?>
<tr id="date_<?= $date->id ?>" <? if ($is_next_date) echo 'class="nextdate" title="' . _('Der nächste Termin') . '"'; ?> data-termin-id="<?= htmlReady($date->id) ?>">
    <td data-sort-value="<?= htmlReady($date->date) ?>" class="date_name">
        <a href="<?= $dialog_url ?>" data-dialog>
            <?= Icon::create($icon)->asImg(['class' => 'text-bottom']) ?>
            <?= htmlReady($date->getFullname(CourseDate::FORMAT_VERBOSE)) ?>
        </a>
    <? if (count($date->dozenten) > 0): ?>
        <br>
        (<?= htmlReady(implode(', ', $date->dozenten->getFullname())) ?>)
    <? endif; ?>
    </td>
    <td class="hidden-small-down"><?= htmlReady($date->getTypeName()) ?></td>
<? if (count($course->statusgruppen) > 0) : ?>
    <td class="hidden-small-down">
    <? if (count($date->statusgruppen) > 0) : ?>
        <ul class="clean">
        <? foreach ($date->statusgruppen as $statusgruppe) : ?>
            <li><?= htmlReady($statusgruppe->name) ?></li>
        <? endforeach ?>
        </ul>
    <? else : ?>
        <?= _('alle') ?>
    <? endif ?>
    </td>
<? endif ?>
    <td class="hidden-small-down">
        <div class="themen-list-container">
            <ul class="themen-list clean">
            <? foreach ($date->topics as $topic): ?>
                <?= $this->render_partial('course/dates/_topic_li', compact('topic', 'date')) ?>
            <? endforeach; ?>
            </ul>
        <? if ($has_access): ?>
            <a href="<?= $controller->url_for('course/dates/new_topic?termin_id=' . $date->id) ?>" title="<?= _('Thema hinzufügen') ?>" data-dialog="size=auto">
                <?= Icon::create('add') ?>
            </a>
        <? endif; ?>
        </div>
    </td>
    <td>
    <? if ($date->getRoom()): ?>
        <?= $date->getRoom()->getFormattedLink() ?>
    <? else: ?>
        <?= htmlReady($date->raum) ?>
    <? endif; ?>
    </td>
<? if ($has_access): ?>
    <td class="actions">
    <? if (!$dates_locked): ?>
        <a href="<?= $controller->url_for('course/timesrooms', ['raumzeitFilter' => 'all']) ?>">
            <?= Icon::create('edit')->asImg(tooltip2(_('Termin bearbeiten'))) ?>
        </a>
    <? endif; ?>
    <? if (!$cancelled_dates_locked): ?>
        <a href="<?= $controller->url_for('course/cancel_dates', ['termin_id' => $date->id]) ?>" data-dialog="size=auto">
            <?= Icon::create('trash')->asImg(tooltip2(_('Termin ausfallen lassen')) + [
                'data-confirm' => _('Wollen Sie diesen Termin wirklich ausfallen lassen?')
                                  . '<br>' . implode('<br>', $date->getDeletionWarnings()),
            ]) ?>
        </a>
    <? endif; ?>
    </td>
<? endif; ?>
</tr>
