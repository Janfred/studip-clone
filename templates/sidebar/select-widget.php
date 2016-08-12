<form action="<?= $url ?>" method="<?= $method ?>">
<? foreach ($params as $key => $value): ?>
    <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>">
<? endforeach; ?>
    <select class="sidebar-selectlist <?= $class ?> <? if ($__is_nested): ?>nested-select<? endif; ?>" name="<?= htmlReady($name) ?>" <? if ($size) printf('size="%u"', $size); ?> <?= $attributes ?>>
    <? foreach ($elements as $element): ?>
        <? if ($element instanceof SelectGroupElement && count($element->getElements()) > 0): ?>
            <optgroup label="<?= htmlReady($element->getLabel() ) ?>">
            <? foreach ($element->getElements() as $option): ?>
                <option value="<?= htmlReady($option->getId()) ?>" <? if ($option->isActive()) echo 'selected'; ?> class="<? if ($element->getIndentLevel()): ?>nested-item nested-item-level-<?= $element->getIndentLevel() + 1 ?><? endif; ?>  <? if ($element->isHeader()): ?>nested-item-header<? endif; ?>">
                    <?= htmlReady(my_substr($option->getLabel(), 0, $max_length)) ?>
                </option>
            <? endforeach; ?>
            </optgroup>
        <? elseif (!($element instanceof SelectGroupElement)): ?>
            <option value="<?= htmlReady($element->getId()) ?>" <? if ($element->isActive()) echo 'selected'; ?> class="<? if ($element->getIndentLevel()): ?>nested-item nested-item-level-<?= $element->getIndentLevel() + 1 ?><? endif; ?> <? if ($element->isHeader()): ?>nested-item-header<? endif; ?>">
                <?= htmlReady(my_substr($element->getLabel(), 0, $max_length)) ?>
            </option>
        <? endif; ?>
    <? endforeach; ?>
    </select>
    <noscript>
        <?= Studip\Button::create(_('Zuweisen')) ?>
    </noscript>
</form>