<? use Studip\Button, Studip\LinkButton; ?>
<?= $controller->renderMessages() ?>
<? $perm = MvvPerm::get($stgteilbezeichnung) ?>
<h3>
    <? if ($stgteilbezeichnung->isNew()) : ?>
    <?= _('Neue Studiengangteil-Bezeichnung') ?>
    <? else : ?>
    <?= sprintf(_('Studiengangteil-Bezeichnung: %s'), htmlReady($stgteilbezeichnung->name)) ?>
    <? endif; ?>
</h3>
<form class="mvv-form default"
      action="<?= $controller->url_for('studiengaenge/stgteilbezeichnungen/store'. ($stgteilbezeichnung->getId() ? '/' . $stgteilbezeichnung->getId() : '')) ?>"
      method="post"<?= Request::isXhr() ? ' data-dialog' : '' ?>>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Studiengangteil-Bezeichnung') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name') ?> type="text" id="name" name="name" size="60" maxlength="100" value="<?= htmlReady($stgteilbezeichnung->name) ?>" required>
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_en') ?> type="text" id="name_en" name="name_en" size="60" maxlength="100" value="<?= htmlReady($stgteilbezeichnung->name_en) ?>">
        </label>
    </fieldset>
    <fieldset>
        <legend><?= _('Kurzname') ?></legend>
        <label>
            <img src="<?= Assets::image_path('languages/lang_de.gif') ?>" alt="<?= _('deutsch') ?>">
            <input <?= $perm->disable('name_kurz') ?> type="text" id="name_kurz" name="name_kurz" size="20" maxlength="20" value="<?= htmlReady($stgteilbezeichnung->name_kurz) ?>">
        </label>
        <label>
            <img src="<?= Assets::image_path('languages/lang_en.gif') ?>" alt="<?= _('englisch') ?>">
            <input <?= $perm->disable('name_kurz_en') ?> type="text" id="name_kurz_en" name="name_kurz_en" size="20" maxlength="20" value="<?= htmlReady($stgteilbezeichnung->name_kurz_en) ?>">
        </label>
    </fieldset>
    <footer data-dialog-button>
        <? if ($stgteilbezeichnung->isNew()) : ?>
        <?= Button::createAccept(_('anlegen'), 'store', array('title' => _('Studiengangteil-Bezeichnung anlegen'))) ?>
        <? else : ?>
        <?= Button::createAccept(_('übernehmen'), 'store', array('title' => _('Änderungen übernehmen'))) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('abbrechen'), $controller->url_for('studiengaenge/stgteilbezeichnungen/index'), array('title' => _('zurück zur Übersicht'))) ?>
    </footer>
</form>