<?php
# Lifter010: TODO
$is_locked = $input['locked'] ? 'disabled readonly' : '';
$is_locked_array = $input['locked'] ? array('disabled' => true, 'readonly' => true) : array();
$is_required_array = $input['must'] ? array('required' => true) : array();
if ($input['type'] === "text") : ?>
    <? if ($input['i18n']) : ?>
        <?= I18N::input($input['name'], $input['value'], $is_locked_array + $is_required_array) ?>
    <? else : ?>
        <input <?=$is_locked ?> type="text" name="<?= $input['name'] ?>" value="<?= htmlReady($input['value']) ?>" <? if ($input['must']) echo 'required'; ?>>
    <? endif ?>
<? endif;

if ($input['type'] === "number") : ?>
    <input <?=$is_locked ?> type="number" name="<?= $input['name'] ?>" value="<?= htmlReady($input['value']) ?>" min="<?= $input['min'] ?>" <? if ($input['must']) echo 'required'; ?>>
<? endif; 

if ($input['type'] === "textarea") : ?>
    <? if ($input['i18n']) : ?>
        <?= I18N::textarea($input['name'], $input['value'], $is_required_array) ?>
    <? else : ?>
        <textarea <?=$is_locked ?> name="<?= $input['name'] ?>" <? if ($input['must']) echo 'required'; ?>><?=
            htmlReady($input['value'])
            ?></textarea>
    <? endif ?>
<? endif;

if ($input['type'] === "select") : ?>
    <? if (!$input['choices'][$input['value']]) : ?>
        <?= _("Keine Änderung möglich") ?>
    <? else : ?>
    <select <?=$is_locked ?> name="<?= $input['name'] ?>" <? if ($input['must']) echo 'required'; ?>>
    <? if ($input['choices']) : foreach ($input['choices'] as $choice_value => $choice_name) : ?>
        <option value="<?= htmlReady($choice_value) ?>"<?
            if ($choice_value == $input['value']) print " selected"
            ?>><?= htmlReady($choice_name) ?></option>
    <? endforeach; endif; ?>
    </select>
    <? endif ?>
<? endif;

if ($input['type'] === "multiselect") : ?>
    <select <?=$is_locked ?> name="<?= $input['name'] ?>" multiple size="8" <? if ($input['must']) echo 'required'; ?>>
    <? if ($input['choices']) : foreach ($input['choices'] as $choice_value => $choice_name) : ?>
        <option value="<?= htmlReady($choice_value) ?>"<?=
            in_array($choice_value, is_array($input['value']) ? $input['value'] : array($input['value']))
            ? " selected"
            : "" ?>><?= htmlReady($choice_name) ?></option>
    <? endforeach; endif; ?>
    </select>
<? endif;

if ($input['type'] === "datafield"):?>
    <div style="padding-right:0.5em;">
        <?=$input['locked'] ? $input['display_value'] : $input['html_value'];?>
    </div>
    <?if($input['description']):?>
        <?=tooltipIcon($input['description'])?>
    <?endif?>
<?endif?>
