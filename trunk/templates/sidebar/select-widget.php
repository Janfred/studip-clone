<form action="<?= $url ?>" method="<?= $method ?>">
<? foreach ($params as $key => $value): ?>
    <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>">
<? endforeach; ?>
    <select class="sidebar-selectlist" name="<?= htmlReady($name) ?>" <? if ($size) printf('size="%u"', $size); ?> <?= $attributes ?>>
    <? foreach ($elements as $element): ?>
        <? if ($element instanceof SelectElement): ?>
            <option value="<?= htmlReady($element->getId()) ?>" <? if ($element->isActive()) echo 'selected'; ?> style="text-indent: <?= $element->getIndentLevel() ?>ex;">
                <?= htmlReady(my_substr($element->getLabel(), 0, $max_length)) ?>
            </option>
        <? elseif(count($element->getElements()) > 0): ?>
            <optgroup label="<?= htmlReady($element->getLabel() ) ?>">
            <? foreach ($element->getElements() as $option): ?>
                <option value="<?= htmlReady($option->getId()) ?>" <? if ($option->isActive()) echo 'selected'; ?> style="text-indent: <?= $option->getIndentLevel() ?>ex;">
                    <?= htmlReady(my_substr($option->getLabel(), 0, $max_length)) ?>
                </option>
            <? endforeach; ?>
            </optgroup>
        <? endif; ?>
    <? endforeach; ?>
    </select>
    <noscript>
        <?= Studip\Button::create(_('Zuweisen')) ?>
    </noscript>
</form>