<tr id="date_<?= $date->id ?>" class="<?= $date instanceof CourseExDate ? 'ausfall' : '' ?><?= $is_next_date ? 'nextdate' : ''?>" <?= $is_next_date ? 'title="' . _('Der nächste Termin') . '"' : '' ?> data-termin-id="<?= htmlReady($date->id) ?>">

    <td data-sort-value="<?= htmlReady($date->date) ?>" class="date_name">
    <? $icon = 'date+' . ($date->chdate > $last_visitdate ? 'new' : '');?>
    <? if ($date instanceof CourseExDate): ?>
        <?= Icon::create($icon, 'info')->asImg(['class' => 'text-bottom']) ?>
        <?= htmlReady($date->getFullname()) ?>
        <?= tooltipIcon($date->content)?>
    <? else: ?>
        <? $dialog_url = $show_raumzeit
                       ? $controller->url_for('course/dates/details/' . $date->id)
                       : $controller->url_for('course/dates/singledate/' . $date->id); ?>
        <a href="<?= $dialog_url ?>" data-dialog>
            <?= Icon::create($icon, 'clickable')->asImg(['class' => 'text-bottom']) ?>
            <?= htmlReady($date->getFullname(CourseDate::FORMAT_VERBOSE)) ?>
        </a>
    <? endif ?>
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
<? if (!$date instanceof CourseExDate): ?>
    <td class="hidden-small-down">
        <div style="display: flex; flex-direction: row;">
            <ul class="themen_list clean">
            <? foreach ($date->topics as $topic): ?>
                <?= $this->render_partial('course/dates/_topic_li', compact('topic', 'date')) ?>
            <? endforeach; ?>
            </ul>
        <? if ($GLOBALS['perm']->have_studip_perm('tutor', $_SESSION['SessionSeminar'])): ?>
            <a href="<?= $controller->url_for('course/dates/new_topic?termin_id=' . $date->id) ?>" style="align-self: flex-end;" title="<?= _('Thema hinzufügen') ?>" data-dialog>
                <?= Icon::create('add', 'clickable')->asImg(12) ?>
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
<? else: ?>
    <td colspan="2"></td>
<? endif; ?>
</tr>