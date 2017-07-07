<?php
$topics = CourseTopic::findBySeminar_id($folder->range_id);
?>
<label>
    <?= _('Wählen sie eine zugehöriges Thema aus') ?>
    <select name="topic_id">
    <? if (count($topics) === 0): ?>
        <option value="">
            <?= _('Es existiert kein Thema in dieser Veranstaltung') ?>
        </option>
    <? endif; ?>
    <? foreach ($topics as $topic): ?>
        <option value="<?= htmlReady($topic->id) ?>">
            <?= htmlReady($topic->title) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>
