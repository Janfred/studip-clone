<? use Studip\Button, Studip\LinkButton; ?>
<? $perm = new MvvPerm($zuordnung) ?>
<h3>
    <?= _('Modulzuordnung bearbeiten') ?>
</h3>
<form class="default" action="<?= $controller->url_for('/modul_zuordnung', $abschnitt->getId(), $modul->getId()) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Bezeichnung der Modulzuordnung') ?></legend>
        <label style="padding: 10px; display:block;">
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('Deutsch') ?>">
            <input <?= $perm->disable('bezeichnung') ?> type="text" name="bezeichnung" id="zuordnung_bezeichnung" size="60" maxlength="254" value="<?= htmlReady($zuordnung->bezeichnung) ?>">
        </label>
        <label style="padding: 10px; display:block;">
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('Englisch') ?>">
            <input <?= $perm->disable('bezeichnung_en') ?> type="text" name="bezeichnung_en" id="zuordnung_bezeichnung_en" size="60" maxlength="254" value="<?= htmlReady($zuordnung->bezeichnung_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Weitere Angaben') ?></legend>
        <label for="zuordnung_flexnow_modul"><?= _('ID der Zuordnung aus Fremdsystem') ?>
            <input <?= $perm->disable('flexnow_modul') ?> type="text" name="flexnow_modul" id="zuordnung_flexnow_modul" maxlength="254" value="<?= htmlReady($zuordnung->flexnow_modul) ?>">
        </label>
        <label for="zuordnung_modulcode"><?= _('Spezifischer Modulcode') ?>
            <input <?= $perm->disable('modulcode') ?> type="text" name="modulcode" id="zuordnung_modulcode" maxlength="254" value="<?= htmlReady($zuordnung->modulcode) ?>">
        </label>
    </fieldset>
    <footer data-dialog-button >
        <?= Button::createAccept(_('übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <?= LinkButton::createCancel(_('abbrechen'), $controller->url_for('/'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>