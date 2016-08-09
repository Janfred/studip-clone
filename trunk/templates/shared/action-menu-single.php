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
    <? elseif ($action['type'] === 'multi-person-search'): ?>
        <?= $action['object']->render() ?>
    <? endif; ?>
<? endforeach; ?>
