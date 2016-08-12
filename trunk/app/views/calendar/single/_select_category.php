<form name="filter_categories" method="post" action="<?= $action_url ?>">
    <span style="font-size: small; color: #555555;"><?= _('Kategorie:') ?></span>
    <select class="sidebar-selectlist nested-select" style="width: 16em;" name="category" onChange="document.filter_categories.submit()">
        <option value=""><?= _('Alle Kategorien') ?></option>
    <? foreach (Config::get()->getValue('PERS_TERMIN_KAT') as $key => $cat) : ?>
        <option value="<?= $key ?>"<?= ($category == $key ? ' selected="selected"' : '') ?> data-text-color="<?= $cat['color'] ?>">
            <?= htmlReady($cat['name']) ?>
        </option>
    <? endforeach; ?>
    </select>
    <?= Icon::create('accept', 'clickable')->asInput(['class' => "text-top"]) ?>
</form>
