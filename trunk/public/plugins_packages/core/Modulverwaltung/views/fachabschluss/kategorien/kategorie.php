<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->jsUrl() ?>
<?= $controller->renderMessages() ?>
<h1>
    <? if ($abschluss_kategorie->isNew()) : ?>
    <?= _('Neue Abschluss-Kategorie') ?>
    <? else : ?>
    <?= sprintf(_('Abschluss-Kategorie: %s'), htmlReady($abschluss_kategorie->getDisplayName())) ?>
    <? endif; ?>
</h1>
<? $perm = MvvPerm::get($abschluss_kategorie) ?>
<form class="default" action="<?= $controller->url_for('fachabschluss/kategorien/kategorie/' . $abschluss_kategorie->getId()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Name') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name') ?>type="text" id="name" name="name" size="60" maxlength="254" value="<?= htmlReady($abschluss_kategorie->name) ?>" required>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_en') ?>type="text" id="name_en" name="name_en" size="60" maxlength="254" value="<?= htmlReady($abschluss_kategorie->name_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kurzname') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name_kurz') ?>type="text" id="name_kurz" name="name_kurz" size="60" maxlength="254" value="<?= htmlReady($abschluss_kategorie->name_kurz) ?>">
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_kurz_en') ?>type="text" id="name_kurz_en" name="name_kurz_en" size="60" maxlength="254" value="<?= htmlReady($abschluss_kategorie->name_kurz_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Beschreibung') ?></legend>
        <label for="beschreibung">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPermBeschreibung()) : ?>
                <textarea cols="60" rows="5" id="beschreibung" name="beschreibung" class="add_toolbar resizable ui-resizable"><?= htmlReady($abschluss_kategorie->beschreibung) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" id="beschreibung" name="beschreibung" class="resizable ui-resizable"><?= htmlReady($abschluss_kategorie->beschreibung) ?></textarea>
            <? endif; ?>
        </label>
        <label for="beschreibung_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>" style="vertical-align: top;">
            <? if ($perm->haveFieldPermBeschreibung_en()) : ?>
                <textarea cols="60" rows="5" id="beschreibung_en" name="beschreibung_en" class="add_toolbar resizable ui-resizable">
                <?= htmlReady($abschluss_kategorie->beschreibung_en) ?></textarea>
            <? else : ?>
                <textarea readonly cols="60" rows="5" id="beschreibung_en" name="beschreibung_en" class=" resizable ui-resizable">
                <?= htmlReady($abschluss_kategorie->beschreibung_en) ?></textarea>
            <? endif; ?>
        </label>
    </fieldset>
    <?= $this->render_partial('shared/form_dokumente', array('perm_dokumente' => $perm->haveFieldPerm('document_assignments', MvvPerm::PERM_CREATE))) ?>
    <footer data-dialog-buttons>
    <? if ($abschluss_kategorie->isNew()) : ?>
        <? if ($perm->havePermCreate()) : ?>
        <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Abschluss-Kategorie anlegen'))) ?>
        <? endif; ?>
    <? else : ?>
        <? if ($perm->havePermWrite()) : ?>
        <?= Button::createAccept(_('übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
    <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $controller->url_for('fachabschluss/kategorien'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>