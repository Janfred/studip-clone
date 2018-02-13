<form action="<?= URLHelper::getLink($url, array(), true) ?>" method="<?= $method?>">
    <?= ($method == 'post' ? CSRFProtection::tokenTag() : '') ?>
    <select name="<?=htmlReady($name)?>" class="sidebar-selectlist submit-upon-select text-top" size="<?= (int) $size ?: 8 ?>" style="max-width: 200px;cursor:pointer" aria-label="<?= _("Wählen Sie ein Objekt aus. Sie gelangen dann zur neuen Seite.") ?>">
    <? foreach ($elements as $element): ?>
        <option <?= $value == $element->getid() ? 'selected' : ''?>
                value="<?= htmlReady($element->getid()) ?>"
                title="<?= htmlReady($element->getTooltip() !== null ? $element->getTooltip() : $element->getLabel()) ?>">
            <?= htmlReady(my_substr($element->getLabel(), 0, 30)) ?>
        </option>
    <? endforeach; ?>
    </select>
</form>