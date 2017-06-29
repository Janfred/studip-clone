<?php
$groups = Statusgruppen::findBySeminar_id(Request::get('cid'));
?>
<label>
    <?= _('Wählen sie eine zugehörige Gruppe aus') ?>
    <select name="group">
    <? if (count($groups) === 0): ?>
        <option value="">
            <?= _('Es existiert keine Gruppe in dieser Veranstaltung') ?>
        </option>
    <? endif; ?>
    <? foreach ($groups as $group): ?>
        <option value="<?= htmlReady($group->id) ?>">
            <?= htmlReady($group->name) ?>
        </option>
    <? endforeach; ?>
    </select>
</label>
