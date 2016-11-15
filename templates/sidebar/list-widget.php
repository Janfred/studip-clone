<ul class="<?= implode(' ', $css_classes) ?>">
<? foreach ($elements as $index => $element): ?>
    <? $icon = $element->icon; ?>
    <li id="<?= htmlReady($index) ?>"
        <?= $icon ? 'style="' . $icon->asCSS() .'"' : '' ?>
        <?= $element->active ? 'class="active"' : '' ?>>
        <?= $element->render() ?>
    </li>
<? endforeach; ?>
</ul>
