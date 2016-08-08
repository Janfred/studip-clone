<form action="<?= $list['stop'] ? $controller->url_for('/index') : $controller->url_for('/chooser'); ?>" style="width: 100%;" id="<?= $name ?>">
    <? if (is_array($list['elements']) && sizeof($list['elements'])) : ?>
    <input type="hidden" name="step" value="<?= $name ?>">
    <? if ($list['stop']) : ?>
    <input type="hidden" name="stop" value="1">
    <? endif; ?>
    <label><?= $list['headline'] ?>
        <select name="id" style="width: 100%;">
            <option value=""><?= _('-- bitte wählen --') ?></option>
        <? foreach ($list['elements'] as $key => $element) : ?>
            <option value="<?= $key ?>"<?= $key == $list['selected'] ? ' selected' : '' ?>>
                <?= $element['name'] ?>
            </option>
        <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
</form>