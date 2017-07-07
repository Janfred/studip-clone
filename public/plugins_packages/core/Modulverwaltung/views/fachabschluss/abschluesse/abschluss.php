<? use Studip\Button, Studip\LinkButton; ?>
<?= $this->controller->renderMessages() ?>
<h1>
    <? if ($abschluss->isNew()) : ?>
    <?= _('Neuer Abschluss') ?>
    <? else : ?>
    <?= sprintf(_('Abschluss: %s'), htmlReady($abschluss->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($abschluss) ?>
<form class="default" action="<?= $controller->url_for('/abschluss', $abschluss->getId()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Name des Abschlusses') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name') ?>type="text" name="name" id="abschluss_name" size="60" maxlength="254" value="<?= htmlReady($abschluss->name) ?>">
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_en') ?>type="text" name="name_en" id="abschluss_name_en" size="60" maxlength="254" value="<?= htmlReady($abschluss->name_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kurzname') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name_kurz') ?>type="text" name="name_kurz" id="abschluss_name_kurz" size="60" maxlength="254" value="<?= htmlReady($abschluss->name_kurz) ?>">
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_kurz_en') ?>type="text" name="name_kurz_en" id="abschluss_name_kurz_en" size="60" maxlength="254" value="<?= htmlReady($abschluss->name_kurz_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Abschluss-Kategorie wählen') ?></legend>
        <? if ($perm->haveFieldPerm('category_assignment')) : ?>
        <label><?= _('Abschluss-Kategorie') ?></label>
            <select id="abschluss_kategorie" name="kategorie_id" size="1">
                <option value=""><?= _('-- bitte wählen --') ?></option>
                <? foreach ($abschluss_kategorien as $kategorie) : ?>
                <option <?= ($kategorie->getId() == $abschluss->kategorie_id ? 'selected ' : '') ?>value="<?= $kategorie->getId() ?>"><?= htmlReady($kategorie->name) ?></option>
                <? endforeach; ?>
            </select>
        </label>
        <? else : ?>
            <?= htmlReady(AbschlussKategorie::get($abschluss->kategorie_id)->getDisplayName()) ?>
        <? endif; ?>
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label for="abschluss_beschreibung">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPerm('beschreibung')) : ?>
            <textarea cols="60" rows="5" id="abschluss_beschreibung" name="beschreibung" class="add_toolbar resizable ui-resizable"><?= htmlReady($abschluss->beschreibung) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" id="abschluss_beschreibung" name="beschreibung" class="resizable ui-resizable"><?= htmlReady($abschluss->beschreibung) ?></textarea>
            <? endif; ?>
        </label>
        <br>
        <label for="abschluss_beschreibung_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPerm('beschreibung_en')) : ?>
            <textarea cols="60" rows="5" id="abschluss_beschreibung_en" name="beschreibung_en" class="add_toolbar resizable ui-resizable"><?= htmlReady($abschluss->beschreibung_en) ?></textarea>
            <? else : ?>
            <textarea readonly cols="60" rows="5" id="abschluss_beschreibung_en" name="beschreibung_en" class="resizable ui-resizable"><?= htmlReady($abschluss->beschreibung_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <footer data-dialog-button>
    <? if ($abschluss->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
        <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Abschluss anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
        <?= Button::createAccept(_('übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $controller->url_for('/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>