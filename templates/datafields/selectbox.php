<?php
$selected = function ($needle) use ($value) {
    if (is_array($value) && !in_array($needle, $value)) {
        return '';
    }
    if (!is_array($value) && $needle != $value) {
        return '';
    }
    return ' selected';
};
?>
<select name="<?= $name ?>[<?= $model->id ?>]<? if ($multiple) echo '[]'; ?>"
        id="<?= $name ?>_<?= $model->id ?>"
        <? if ($multiple) echo 'multiple'; ?>
        <? if ($model->is_required) echo 'required'; ?>>
<? foreach ($type_param as $pkey => $pval): ?>
    <option value="<?= $is_assoc ? (string)$pkey : $pval ?>"
            <?= $selected($is_assoc ? (string)$pkey : $pval) ?>>
        <?= htmlReady($pval) ?>
    </option>
<? endforeach; ?>
</select>
