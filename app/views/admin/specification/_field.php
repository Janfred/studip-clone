<?php
$fields = Request::getArray('fields');
$order  = Request::getArray('order');
?>

<label class="col-2">
<? if ($required) : ?>
<span class="required">
<? endif ?>
    <?= htmlReady($name) ?>
<? if ($required) : ?>
    </span>
<? endif ?>
</label>
<label class="col-2">
    <?= _('Sortierung') ?>
    <input id="order_<?= $id ?>" min="0" type="number" size="3" name="order[<?= $id ?>]"
           value="<?= (int)(($order && isset($order[$id])) ? $order[$id] : @$rule['order'][$id]) ?>">
    <input type="hidden" name="fields[<?= $id ?>]" value="0">
</label>
<label class="col-2">
    <input type="checkbox"
           name="fields[<?= $id ?>]"
           value="1"
            <?= (($fields && isset($fields[$id])) ? $fields[$id] : @$rule['attributes'][$id]) ? 'checked="checked"' : '' ?>>
    <?= _('Aktivieren') ?>
</label>
