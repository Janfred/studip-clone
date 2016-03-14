<div style="white-space: nowrap;">
    <input type="text" name="<?= $name ?>[<?= $model->id ?>][]"
           class="no-hint"
           maxlength="2" size="1"
           value="<? if ($value) echo date('d', $timestamp); ?>"
           title="<?= _('Tag') ?>"
           style="display: inline-block; vertical-align: bottom; width: auto;"
           <? if ($model->is_required) echo 'required'; ?>>.

    <select name="<?= $name ?>[<?= $model->id ?>][]" title="<?= _('Monat') ?>"
            style="display: inline-block; vertical-align: bottom; width: auto;"
            <? if ($model->is_required) echo 'required'; ?>>
        <option value=""></option>
    <? for ($i = 0; $i < 12; $i += 1): ?>
        <option value="<?= $i + 1 ?>"
                <? if ($value && date('n', $timestamp) == $i + 1) echo 'selected'; ?>>
            <?= studip_utf8decode(strftime('%B', strtotime('January 1st +' . $i . ' months'))) ?>
        </option>
    <? endfor;?>
    </select>

    <input type="text" name="<?= $name ?>[<?= $model->id ?>][]"
           class="no-hint"
           maxlength="4" size="3"
           value="<? if ($value) echo date('Y', $timestamp); ?>"
           title="<?= _('Jahr') ?>"
           style="display: inline-block; vertical-align: bottom; width: auto;"
           <? if ($model->is_required) echo 'required'; ?>>
</div>
