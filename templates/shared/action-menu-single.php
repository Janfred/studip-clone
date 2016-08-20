<?php
$attributes = function (array $attributes) {
    $result = [];
    foreach ($attributes as $key => $value) {
        if ($value === null) {
            $result[] = htmlReady($key);
        } else {
            $result[] = sprintf('%s="%s"', htmlReady($key), htmlReady($value));
        }
    }
    return implode(' ', $result);
};
?>

<? foreach ($actions as $action): ?>
    <? if ($action['type'] === 'link'): ?>
        <a href="<?= $action['link'] ?>" <?= $attributes($action['attributes'] + ['title' => $action['label']]) ?>>
        <? if ($action['icon']): ?>
            <?= $action['icon'] ?>
        <? else: ?>
            <?= htmlReady($action['label']) ?>
        <? endif; ?>
        </a>
    <? elseif ($action['type'] === 'button'): ?>
        <label>
        <? if ($action['icon']): ?>
            <?= $action['icon']->asInput(['name' => $action['name']]) ?>
        <? else: ?>
            <span class="action-menu-no-icon"></span>
            <button type="submit" name="<?= htmlReady($action['name']) ?>" style="display: none;"></button>
        <? endif; ?>
            <?= htmlReady($action['label']) ?>
        </label>
    <? elseif ($action['type'] === 'multi-person-search'): ?>
        <?= $action['object']->render() ?>
    <? endif; ?>
<? endforeach; ?>
