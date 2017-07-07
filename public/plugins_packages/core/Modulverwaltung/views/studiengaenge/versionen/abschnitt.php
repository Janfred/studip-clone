<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = MvvPerm::get($abschnitt) ?>
<h3>
    <? if ($abschnitt->isNew()) : ?>
    <?= sprintf(_('Einen neuen Studiengangteil-Abschnitt für die Version "%s" anlegen.'),
            htmlReady($version->getDisplayName())) ?>
    <? else : ?>
    <?= sprintf(_('Studiengangteil-Abschnitt "%s" der Version "%s" bearbeiten.'),
            htmlReady($this->abschnitt->name),
            htmlReady($this->version->getDisplayName())) ?>
    <? endif; ?>
</h3>
<form class="default" action="<?= $controller->url_for('/abschnitt', $abschnitt->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Name des Studiengangteil-Abschnittes') ?></legend>
        <label style="padding: 10px; display:block;">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>">
            <input <?= $perm->disable('name') ?> type="text" name="name" id="abschluss_name" maxlength="254" value="<?= htmlReady($abschnitt->name) ?>" required>
        </label>
        <label style="padding: 10px; display:block;">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>">
            <input <?= $perm->disable('name_en') ?> type="text" name="name_en" id="abschluss_name_en" maxlength="254" value="<?= htmlReady($abschnitt->name_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kommentar') ?></legend>
        <label for="kommentar">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>">
            <textarea <?= $perm->disable('kommentar') ?> cols="60" rows="5" id="kommentar" name="kommentar" class="<?= $perm->haveFieldPerm('kommentar') ? 'add_toolbar' : '' ?> resizable ui-resizable"><?= htmlReady($abschnitt->kommentar) ?></textarea>
        </label>
        <label for="kommentar_en">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>">
            <textarea <?= $perm->disable('kommentar_en') ?> cols="60" rows="5" id="kommentar_en" name="kommentar_en" class="<?= $perm->haveFieldPerm('kommentar_en') ? 'add_toolbar' : '' ?> resizable ui-resizable"><?= htmlReady($abschnitt->kommentar_en) ?></textarea>
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Weitere Angaben') ?></legend>
        <label><?= _('Kreditpunkte') ?>
            <input <?= $perm->disable('kp') ?> type="text" name="kp" id="kp" value="<?= htmlReady($abschnitt->kp) ?>" size="3" maxlength="2">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Zwischenüberschrift') ?></legend>
        <label style="padding: 10px; display:block;">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>">
            <input <?= $perm->disable('ueberschrift') ?> type="text" name="ueberschrift" id="ueberschrift" size="60" maxlength="254" value="<?= htmlReady($abschnitt->ueberschrift) ?>">
        </label>
        <label style="padding: 10px; display:block;">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>">
            <input <?= $perm->disable('ueberschrift_en') ?> type="text" name="ueberschrift_en" id="ueberschrift_en" size="60" maxlength="254" value="<?= htmlReady($abschnitt->ueberschrift_en) ?>">
        </label>
    </fieldset>
    <input type="hidden" name="version_id" value="<?= $this->version->id ?>">
    <footer data-dialog-button >
        <? if ($abschnitt->isNew()) : ?>
        <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Abschnitt anlegen'))) ?>
        <? else : ?>
        <?= Button::createAccept(_('übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $cancel_url, array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>